<?php
//arrumar quantidades para diferentes tipos de pacotes
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class integracao_consulta_remedios{
    var $funcoes_publicas = array(
        'index' => true,
        'integracaoCompleta' => true,
        'gerarRelatorio' => true,
        'editarConfig' => true,
        'salvarConfig' => true,
        'atualizarIntermedario' => true,
        'schedule' => true,
    );
    
    private $_programa;   
    private $_campos_intermediario;
    private $_campos_config;
    
    private $_estoque_max;
    private $_estoque_min;
    
    private $_teste;
    
    private $_produtos_integrar;
    private $_flag_produtos_integrar;
    private $_produtos_zerar;
    private $_flag_produtos_zerar;
    private $_produtos_importados;
    private $_flag_produtos_importados;
    private $_produtos_estoque;
    
    function __construct(){
        set_time_limit(0);
        $this->_campos_config = array('estoque_max', 'estoque_min', 'produtos_somente', 'produtos_excluidos', 'marcas_somente', 'marcas_excluidos', 'fornecedores_somente', 'fornecedores_excluidos', 'lista_preco');
        $this->_campos_intermediario = array('sku', 'preco', 'estoque', 'peso', 'altura', 'largura', 'comprimento');
        $this->_programa = 'integracao_consulta_remedios';
        $estoque_temp = $this->getConfig('estoque_max');
        $this->_estoque_max = $estoque_temp === '' ? 2000 : $estoque_temp;
        $estoque_temp = $this->getConfig('estoque_min');
        $this->_estoque_min = $estoque_temp === '' ? 0 : $estoque_temp;
        
        $this->_teste = true;
        
        $this->_produtos_integrar = array();
        $this->_flag_produtos_integrar = false;
        $this->_produtos_zerar = array();
        $this->_flag_produtos_zerar = false;
        $this->_produtos_importados = array();
        $this->_flag_produtos_importados = false;
        $this->_produtos_estoque = array();
    }
    
    function index(){
        $ret = '';
        $titulo = 'Integração Consulta Remédios';
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."integracaoCompleta')",
            'tamanho' => 'grande',
            'cor' => 'success',
            'texto' => 'Integrar Produtos',
            'bloco' => true,
        );
        $bt_integracao = formbase01::formBotao($param);
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."gerarRelatorio')",
            'tamanho' => 'grande',
            'cor' => 'success',
            'texto' => 'Gerar Relatório',
            'bloco' => true,
        );
        $bt_relatorio = formbase01::formBotao($param);
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."atualizarIntermedario')",
            'tamanho' => 'grande',
            'cor' => 'success',
            'texto' => 'Atualizar Preço e Estoque',
            'bloco' => true,
        );
        $bt_atualizar = formbase01::formBotao($param);
        
        $param = array();
        $param['tamanhos'] = array(3, 3, 3, 3);
        $param['conteudos'] = array('', $bt_integracao, $bt_atualizar, '');
        $ret .= addLinha($param) . '<br>';
        
        $param = array();
        $param['tamanhos'] = array(3, 6, 3);
        $param['conteudos'] = array('', $bt_relatorio, '');
        $ret .= addLinha($param) . '<br>';
        
        $param = array();
        $bt = array();
        $bt['onclick'] = "setLocation('".getLink()."editarConfig')";
        $bt['tamanho'] = 'padrao';
        $bt['cor'] = 'sucess';
        $bt['texto'] = 'Configurar';
        $param['botoesTitulo'][] = $bt;
        $param['titulo'] = $titulo;
        $param['conteudo'] = $ret;
        
        $ret = addCard($param);
        $ret .= $this->montarCardPesquisa();
        
        if(isset($_POST['formPesquisa'])){
            $ret .= $this->montarCardResultadoPesquisa();
        }
        
        return $ret;
    }
    
    public function schedule(){
        $this->integrarWinthorIntermediario();
        $this->integrarIntermediarioApi(true, false);
    }
    
    public function integracaoCompleta(){
        $this->integrarWinthorIntermediario();
        $this->integrarIntermediarioApi();
        return $this->index();
    }
    
    private function integrarWinthorIntermediario(){
        $sql = $this->montaSqlWinthorIntermediario();
        $this->popularProdutosIntegrar();
        $this->popularProdutosZerar();
        $this->popularProdutosImportados();
        $rows = query4($sql);
        foreach ($rows as $row){
            if($this->verificarProdutoDeveSerIntegrado($row['CODPROD']) || $this->verificarProdutoDeveSerZerado($row['CODPROD'])){
                $dados = $this->montarPacoteIntermediario($row);
                $status = $this->verticarModificacoes($dados);
                if($status == 'novo'){
                    $sql = montaSQL($dados, 'consulta_remedios');
                }
                elseif($status == 'modificado' || ($status == 'igual' && ($dados['estoque'] == $this->_estoque_max || $dados['estoque'] == strval($this->_estoque_max)))){
                    $sql = montaSQL(['estoque' => 0], 'consulta_remedios', 'UPDATE', "sku = '" . $dados['sku'] . "'");
                    query($sql);
                    $sql = montaSQL($dados, 'consulta_remedios', 'UPDATE', "sku = '" . $dados['sku'] . "'");
                }
                elseif($status == 'igual'){
                    $sql = '';
                }
                elseif($status == 'incompleto'){
                    if(count($this->getDadosIntermediario($dados['sku'])) === 0){
                        //não existe e tem problemas
                        $sql = montaSQL($dados, 'consulta_remedios');
                        query($sql);
                    }
                    $sql = '';
                    $this->atualizarIntermediario($dados['sku'], false, 'dados do winthor incompletos');
                }
                if(!empty($sql)){
                    query($sql);
                }
            }
        }
    }
    
    private function montaSqlWinthorIntermediario($limitado = true){
        $ret = '';
        if($limitado){
            $base = "SELECT
    					PCPRODUT.*,
    					PCPRODFILIAL.ORIGMERCTRIB
    				FROM
    					PCPRODUT,
    					PCPRODFILIAL
    				WHERE
    					PCPRODUT.CODPROD = PCPRODFILIAL.CODPROD (+)
    					AND PCPRODFILIAL.CODFILIAL = 1
    					AND PCPRODUT.OBS2 <> 'FL'
    					AND PCPRODUT.DTEXCLUSAO IS NULL
    					AND PCPRODUT.REVENDA = 'S'
    					AND CODEPTO IN (1,12)
    			    ";
        }
        else{
            $base = "select 
                        CODPROD,
                        NOMEECOMMERCE,
                        DESCRICAO,
                        CODAUXILIAR
                     from 
                        pcprodut 
                    where 
                        dtexclusao is null 
                        and revenda = 'S' 
                    ";
        }
        //AND PCPRODUT.CODAUXILIAR = '7896004754154'
        
       $where = $this->montarWhereWinthorIntermediario();
       
       $order_by = ' ORDER BY
                    PCPRODUT.CODPROD';
       
        $ret .= $base . $where . $order_by;
        
        return $ret;
    }
    
    private function montarWhereWinthorIntermediario(){
        $parametros = $this->getConfig();
        
        $ret = '';
        /*
        if($parametros['produtos_somente'] != ''){
            $string_produtos_somente = $this->montarIn($parametros['produtos_somente']);
            if($string_produtos_somente != ''){
                $ret .=" AND PCPRODUT.CODPROD IN $string_produtos_somente ";
            }
        }
        
        if($parametros['produtos_excluidos'] != ''){
            $string_produtos_excluidos = $this->montarIn($parametros['produtos_excluidos']);
            if($string_produtos_excluidos != ''){
                $ret .= " AND PCPRODUT.CODPROD NOT IN $string_produtos_excluidos ";
            }
        }
        
        if($parametros['marcas_somente'] != ''){
            $string_marcas_somente = $this->montarIn($parametros['marcas_somente']);
            if($string_marcas_somente != ''){
                $ret .= " AND PCPRODUT.CODMARCA IN $string_marcas_somente ";
            }
        }
        
        if($parametros['marcas_excluidos'] != ''){
            $string_marcas_excluidos = $this->montarIn($parametros['marcas_excluidos']);
            if($string_marcas_excluidos != ''){
                $ret .= " AND PCPRODUT.CODMARCA NOT IN $string_marcas_excluidos ";
            }
        }
        
        if($parametros['fornecedores_somente'] != ''){
            $string_fornecedores_somente = $this->montarIn($parametros['fornecedores_somente']);
            if($string_fornecedores_somente != ''){
                $ret .= " AND PCPRODUT.CODFORNEC IN $string_fornecedores_somente ";
            }
        }
        
        if($parametros['fornecedores_excluidos'] != ''){
            $string_fornecedores_excluidos = $this->montarIn($parametros['fornecedores_excluidos']);
            if($string_fornecedores_excluidos != ''){
                $ret .= " AND PCPRODUT.CODFORNEC NOT IN $string_fornecedores_excluidos ";
            }
        }
        */
        
        
        return $ret;
    }
    
    private function montarIn($valores){
        $ret = '';
        $array_dados = explode(',', $valores);
        $temp = array();
        foreach ($array_dados as $d){
            $temp[] = "'$d'";
        }
        if(count($temp) > 0){
            $ret = '(' . implode(', ', $temp) . ')';
        }
        return $ret;
    }
    
    private function montarPacoteIntermediario($row){
        $ret = array();
        $ret['sku']         = $row['CODAUXILIAR'];
        $ret['preco']       = $this->formatarValor($this->getPrecoProduto($row['CODPROD']));
        $ret['estoque']     = ($this->verificarProdutoDeveSerZerado($row['CODPROD']) ? '0' : $this->getEstoqueProduto($row['CODPROD']));
        $ret['peso']        = $this->formatarValor($row['PESOBRUTO']);
        $ret['altura']      = $this->formatarValor($row['ALTURAARM']);
        $ret['largura']     = $this->formatarValor($row['LARGURAARM']);
        $ret['comprimento'] = $this->formatarValor($row['COMPRIMENTOARM']);
        $ret['mensagem_erro'] = '';
        return $ret;
    }
    
    private function getPrecoProduto($codprod){
        $ret = 'sem valor';
        $regiao = $this->getConfig('lista_preco');
        $sql = "select NUMREGIAO, PTABELA1 from pctabpr where codprod = '$codprod' and NUMREGIAO = '$regiao'";
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['PTABELA1'];
        }
        return $ret;
    }
    
    private function getEstoqueProduto($codprod){
        $ret = 'sem estoque';
        
        $sql = "SELECT
                    PCEST.CODPROD,
                    PCPRODUT.CODEPTO,
                    (NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0) - NVL(PCEST.QTBLOQUEADA, 0)) QTESTDISP
                FROM
                    PCEST,
                    PCPRODUT
                WHERE
                    PCEST.CODFILIAL = 1
                    AND PCEST.CODPROD = PCPRODUT.CODPROD (+)
                    AND PCPRODUT.CODEPTO IN (1,12)
                    AND PCEST.CODPROD = '$codprod'";
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $this->resolverEstoque($rows[0]['QTESTDISP']);
        }
        return $ret;
    }
    
    private function resolverEstoque($estoque){
        $ret = $estoque;
        if($estoque > $this->_estoque_max){
            $ret = $this->_estoque_max;
        }
        elseif($estoque < $this->_estoque_min){
            $ret = 0;
        }
        return $ret;
    }
    
    private function formatarValor($valor){
        $pos_ponto = strpos(strval($valor), '.');
        if($pos_ponto === 0){
            $valor = '0' . $valor;
        }
        return strval(round(floatval($valor), 2));
    }
    
    private function verticarModificacoes($dados){
        $ret = '';
        if($dados['preco'] === 'sem valor' || $dados['estoque'] === 'sem estoque'){
            $ret = 'incompleto';
        }
        else{
            $ret = 'novo';
            $dados_atuais = $this->getDadosIntermediario($dados['sku']);
            if(count($dados_atuais) > 0){
                $ret = 'igual';
                foreach ($this->_campos_intermediario as $campo){
                    if($dados[$campo] != $dados_atuais[$campo]){
                        $ret = 'modificado';
                    }
                }
            }
        }
        return $ret;
    }
    
    private function getDadosIntermediario($sku, $bruto = false){
        $ret = array();
        $sql = "select * from consulta_remedios where sku = '$sku'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            if($bruto){
                $ret = $rows[0];
            }
            else{
                foreach ($this->_campos_intermediario as $campo){
                    $ret[$campo] = $rows[0][$campo];
                }
            }
        }
        return $ret;
    }
    
    private function integrarIntermediarioApi($sincronizar_preco_estoque = true, $sincronizar_dimensoes = true){
        if($sincronizar_preco_estoque || $sincronizar_preco_estoque){
            $sql = "select * from consulta_remedios where (cadastro > sincro and cadastro > erro) or (erro >= cadastro and erro >= sincro)";
            $rows = query($sql);
            //$rest = new rest_consulta_remedios('https://loja.stagingcr.com.br', '851cdaff-43ca-4d52-95d9-1c8efce5e0c0');
            $rest = new rest_consulta_remedios('https://loja.consultaremedios.com.br', 'af79459f-d325-4865-8ecc-117f63d722c0');
            
            foreach ($rows as $row){
                $dados = $this->montarPacoteApi($row);
                if($sincronizar_preco_estoque){
                    $status_preco = $rest->cadastrarPrecoEstoque($dados['preco_status'], $dados['sku']);
                }
                else{
                    $status_preco = true;
                }
                if($sincronizar_dimensoes){
                    $status_dimensoes = $rest->cadastrarDimensoes($dados['dimensoes'], $dados['sku']);
                }
                else{
                    $status_dimensoes = true;
                }
                $status = (($status_preco === $status_dimensoes) &&  ($status_preco === true) ? true : false);
                $mensagem_erro = $this->montaMensagemErroApi($status_preco, $status_dimensoes);
                $this->atualizarIntermediario($dados['sku'], $status, $mensagem_erro);
            }
        }
    }
    
    private function montaMensagemErroApi($status_preco, $status_dimensoes){
        $ret = 'erro desconhecido';
        if($status_preco === $status_dimensoes){
            if($status_preco === true){
                $ret =  '';
            }
            elseif($status_preco === 'produto'){
                $ret = 'o produto não existe';
            }
            elseif($status_preco === false){
                $ret = 'erro desconhecido';
            }
        }
        else{
            if($status_preco === 'produto' || $status_dimensoes === 'produto'){
                $ret = 'o produto não existe';
            }
            elseif($status_preco === false || $status_dimensoes === false){
                $ret = 'erro desconhecido';
            }
        }
        return $ret;
    }
    
    private function montarPacoteApi($row){
        $ret = array();
        $dados_preco_estoque = array(
            'price'  => $row['preco'],
            'stock'  => $row['estoque'],
        );
        $dados_dimensoes = array(
            'weight' => $row['peso'],
            'height' => $row['altura'],
            'width'  => $row['largura'],
            'depth'  => $row['comprimento'],
        );
        $ret['preco_status'] = $dados_preco_estoque;
        $ret['dimensoes'] = $dados_dimensoes;
        $ret['sku'] = $row['sku'];
        return $ret;
    }
    
    private function atualizarIntermediario($sku, $status, $mensagem_erro){
        $timestamp = time() + 5;
        $data = date('Y-m-d H:i:s', $timestamp);
        if($status){
            $sql = "update consulta_remedios set sincro = '$data', mensagem_erro = '$mensagem_erro' where sku = '$sku'";
        }
        else{
            $sql = "update consulta_remedios set erro   = '$data', mensagem_erro = '$mensagem_erro' where sku = '$sku'";
        }
        query($sql);
    }
    
    public function gerarRelatorio(){
        global $config;
        
        $dados = $this->getDados();
        
        $param = array(
            'programa' => $this->_programa,
            'titulo' => 'Integração Consulta Remédios',
        );
        $relatorio = new relatorio01($param);
        $relatorio->setToExcel(true, 'integracao_consulta_remedios');
        $arquivo = 'integracao_consulta_remedios.xlsx';
        
        $relatorio->addColuna(array('campo' => 'codigo'			, 'etiqueta' => 'Código'			         , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'nome' 		, 'etiqueta' => 'Nome eCommerce' 	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'nomemkp' 	, 'etiqueta' => 'Nome MktPlace' 	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'status'			, 'etiqueta' => 'Status'			         , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'mensagem'		, 'etiqueta' => 'Mensagem de Erro'			 , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'codigo_cr'		, 'etiqueta' => 'Codigo Consulta Remédios'	 , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'preco'			, 'etiqueta' => 'Preço'			             , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'estoque'		, 'etiqueta' => 'Estoque'			         , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
//        $relatorio->addColuna(array('campo' => 'altura'			, 'etiqueta' => 'Altura'			         , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
       // $relatorio->addColuna(array('campo' => 'largura'		, 'etiqueta' => 'Largura'			         , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        //$relatorio->addColuna(array('campo' => 'comprimento'	, 'etiqueta' => 'Comprimento'			     , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
       // $relatorio->addColuna(array('campo' => 'peso'			, 'etiqueta' => 'Peso'			             , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        
        $relatorio->addColuna(array('campo' => 'detalhe' 		, 'etiqueta' => 'FL/Exluido' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'ean' 			, 'etiqueta' => 'EAN' 			,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $relatorio->addColuna(array('campo' => 'dtcadastro' 	, 'etiqueta' => 'DT Cadastro' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'codfornec' 	, 'etiqueta' => 'Cod.Fornec' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'fornecedor' 	, 'etiqueta' => 'Fornecedor' 	,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'depto' 		, 'etiqueta' => 'Depto' 		,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'marca' 		, 'etiqueta' => 'Marca' 		,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'psico' 		, 'etiqueta' => 'Psicotropico' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'retinoico' 	, 'etiqueta' => 'Retinoico' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'desc2' 		, 'etiqueta' => 'Descrição 2' 	,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'estoque' 		, 'etiqueta' => 'Estoque' 		,'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
        
        $relatorio->setDados($dados);
        $relatorio . '';
        
        addPortalJquery("setLocation('" . $config['tempURL'] . $arquivo . "');");
        
        return $this->index();
    }
    
    private function getDados(){
        $ret = array();
        $produtos = $this->getListaProdutos(false);
        $dados_ok              = $this->getDadosStatus('OK'             , $produtos);
        $dados_dessincronizado = $this->getDadosStatus('DESSINCRONIZADO', $produtos);
        $dados_erro            = $this->getDadosStatus('ERRO'           , $produtos);
        $ret = array_merge($dados_ok, $dados_dessincronizado, $dados_erro);
        
        foreach ($ret as $i => $r){
        	$sql = "
				SELECT
					CODPROD,
					DESCRICAO,
					NOMEECOMMERCE,
					NOMEECOMMERCE_MARKETPLACE,
					CODAUXILIAR,
					DTCADASTRO,
					CODFORNEC,
					DTEXCLUSAO,
					(SELECT PCMARCA.MARCA FROM PCMARCA WHERE PCMARCA.CODMARCA = PCPRODUT.CODMARCA) MARCA,
					(SELECT FORNECEDOR FROM PCFORNEC WHERE CODFORNEC = PCPRODUT.CODFORNEC) AS FORNEC,
					(SELECT DESCRICAO FROM PCDEPTO WHERE CODEPTO = PCPRODUT.CODEPTO) AS DEPARTAMENTO,
					PCPRODUT.OBS2 AS FORA_LINHA,
					PSICOTROPICO,
					RETINOICO,
					DESCRICAO2,
					OBS2,
					(SELECT SUM((NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0))) FROM PCEST WHERE CODPROD = PCPRODUT.CODPROD) AS ESTOQUE
				FROM
					PCPRODUT
				WHERE
					CODEPTO <> 4
					AND CODPROD = ".$r['codigo'];
					
					$rows = query4($sql);
					if(isset($rows[0])){
					
			        	$ret[$i]['nome' 		] = $rows[0]['NOMEECOMMERCE'];
			        	$ret[$i]['nomemkp' 		] = $rows[0]['NOMEECOMMERCE_MARKETPLACE'];
			        	$ret[$i]['detalhe' 		] = !empty($rows[0]['DTEXCLUSAO']) ? $rows[0]['DTEXCLUSAO'] :(!empty($rows[0]['OBS2']) ? $rows[0]['OBS2'] : '');
			        	$ret[$i]['ean' ] 		  = $rows[0]['CODAUXILIAR'];
			        	$ret[$i]['dtcadastro' 	] = $rows[0]['DTCADASTRO'];
			        	$ret[$i]['codfornec' 	] = $rows[0]['CODFORNEC'];
			        	$ret[$i]['fornecedor' 	] = $rows[0]['FORNEC'];
			        	$ret[$i]['depto' 		] = $rows[0]['DEPARTAMENTO'];
			        	$ret[$i]['psico' 		] = $rows[0]['PSICOTROPICO'];
			        	$ret[$i]['retinoico' 	] = $rows[0]['RETINOICO'];
			        	$ret[$i]['desc2' 		] = $rows[0]['DESCRICAO2'];
			        	$ret[$i]['estoque' 		] = $rows[0]['ESTOQUE'];
			        	$ret[$i]['marca' 		] = $rows[0]['MARCA'];
					}
        }
        
        return $ret;
    }
    
    private function getDadosStatus($status, $produtos){
        $ret = array();
        $sql = $this->montaQueryRelatorio($status);
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                
                $temp['codigo'] = isset($produtos[$row['sku']]) ? $produtos[$row['sku']]['codigo'] : $row['sku'];
                $temp['nome'] = isset($produtos[$row['sku']]) ? $produtos[$row['sku']]['nome'] : '';
                $temp['codigo_cr'] = $row['sku'];
                $temp['preco'] = $row['preco'];
                $temp['estoque'] = $row['estoque'];
                $temp['altura'] = $row['altura'];
                $temp['largura'] = $row['largura'];
                $temp['comprimento'] = $row['comprimento'];
                $temp['peso'] = $row['peso'];
                $temp['mensagem'] = $row['mensagem_erro'];
                $temp['status'] = $status;
                
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function montaQueryRelatorio($status){
        $ret = 'select * from consulta_remedios where ';
        switch ($status){
            case 'OK':
                $ret .= 'sincro >= cadastro and sincro > erro';
                break;
            case 'DESSINCRONIZADO':
                $ret .= 'cadastro > sincro and cadastro > erro';
                break;
            case 'ERRO':
                $ret .= 'erro >= cadastro and erro > sincro';
                break;
            default:
                break;
        }
        return $ret;
    }
    
    private function getListaProdutos($limitado = true){
        $ret = array();
        $sql = $this->montaSqlWinthorIntermediario($limitado);
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['codigo'] = $row['CODPROD'];
                $temp['nome'] = !empty($row['NOMEECOMMERCE']) ? str_replace("'", '´', $row['NOMEECOMMERCE']) : str_replace("'", '´', $row['DESCRICAO']);
                $ret[$row['CODAUXILIAR']] = $temp;
            }
        }
        return $ret;
    }
    
    public function editarConfig(){
        $dados = $this->getConfig();
        
        $param = array(
            'geraScriptValidacaoObrigatorios' => false,
        );
        $form = new form01($param);
        $form->setPastas(array('Produtos', 'Preços', 'Estoque'));
        
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[produtos_somente]'         , 'etiqueta' => 'Somente Produtos'      , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['produtos_somente']       , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[produtos_excluidos]'       , 'etiqueta' => 'Produtos Excluídos'    , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['produtos_excluidos']     , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[marcas_somente]'           , 'etiqueta' => 'Somente Marcas'        , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['marcas_somente']         , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[marcas_excluidos]'         , 'etiqueta' => 'Marcas Excluídas'      , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['marcas_excluidos']       , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[fornecedores_somente]'     , 'etiqueta' => 'Somente Fornecedores'  , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['fornecedores_somente']   , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[fornecedores_excluidos]'   , 'etiqueta' => 'Fornecedores Excluídos', 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['fornecedores_excluidos'] , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        $form->addCampo(array('id' => '', 'campo' => 'formconfig[lista_preco]'              , 'etiqueta' => 'Lista de Preços'       , 'tipo' => 'A'  , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['lista_preco']            , 'pasta'	=> 1 , 'lista' => $this->getListasPrecos(), 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        $form->addCampo(array('id' => '', 'campo' => 'formconfig[estoque_max]'              , 'etiqueta' => 'Estoque Máximo'        , 'tipo' => 'N'  , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['estoque_max']            , 'pasta'	=> 2 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        $form->addCampo(array('id' => '', 'campo' => 'formconfig[estoque_min]'              , 'etiqueta' => 'Estoque Minimo'        , 'tipo' => 'N'  , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['estoque_min']            , 'pasta'	=> 2 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        $form->setEnvio(getLink() . 'salvarConfig', 'formconfig', 'formconfig');
        
        $param = array();
        $bt = array();
        $bt['onclick'] = "setLocation('".getLink()."index')";
        $bt['tamanho'] = 'pequeno';
        $bt['cor'] = 'danger';
        $bt['texto'] = 'Cancelar';
        $param['botoesTitulo'][] = $bt;
        $param['titulo'] = 'Configurar Parâmetros da Integração';
        $param['conteudo'] = $form . '';
        
        return addCard($param);
    }
    
    public function salvarConfig(){
        $dados = getParam($_POST, 'formconfig');
        foreach ($this->_campos_config as $campo){
            if(isset($dados[$campo])){
                $sql = $this->montaQuerySalvarConfig($campo, $dados[$campo]);
                log::gravaLog('consulta_remedios_modificacoes_config', $sql);
                query($sql);
            }
        }
        return $this->index();
    }
    
    private function montaQuerySalvarConfig($campo, $valor){
        $ret = '';
        $sql = "select * from gf_cr_config where campo = '$campo'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = array(
                'valor' => $valor,
            );
            $where = "campo = '$campo'";
            $ret = montaSQL($campos, 'gf_cr_config', 'UPDATE', $where);
        }
        else{
            $campos = array(
                'campo' => $campo,
                'valor' => $valor,
            );
            $ret = montaSQL($campos, 'gf_cr_config');
        }
        return $ret;
    }
    
    private function getConfig($campo = ''){
        if($campo == ''){
            $ret = array();
            //todos os configs
            foreach ($this->_campos_config as $campo_atual){
                $ret[$campo_atual] = '';
            }
            $sql = 'select * from gf_cr_config';
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[$row['campo']] = $row['valor'];
                }
            }
        }
        else{
            $ret = '';
            //somente um
            $sql = "select * from gf_cr_config where campo = '$campo'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0]['valor'];
            }
        }
        return $ret;
    }
    
    private function getListasPrecos(){
        $ret = array();
        $sql = "select NUMREGIAO, REGIAO from pcregiao order by NUMREGIAO";
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret[] = array('', '');
            foreach ($rows as $row){
                $temp = array();
                $temp[0] = $row['NUMREGIAO'];
                $temp[1] = $row['REGIAO'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function atualizarIntermedario(){
        $sql = $this->montaSqlWinthorIntermediario();
        $this->popularProdutosIntegrar();
        $this->popularProdutosZerar();
        $this->popularProdutosImportados();
        $rows = query4($sql);
        foreach ($rows as $row){
            if($this->verificarProdutoJaFoiImportado($row['CODPROD'])){
                $dados = $this->montarPacoteIntermediario($row);
                $status = $this->verticarModificacoes($dados);
                if($status == 'modificado' || ($status == 'igual' && ($dados['estoque'] == $this->_estoque_max || $dados['estoque'] == strval($this->_estoque_max)))){
                    $sql = montaSQL(['estoque' => 0], 'consulta_remedios', 'UPDATE', "sku = '" . $dados['sku'] . "'");
                    query($sql);
                    $sql = montaSQL($dados, 'consulta_remedios', 'UPDATE', "sku = '" . $dados['sku'] . "'");
                    query($sql);
                }
                elseif($status == 'incompleto'){
                    $this->atualizarIntermediario($dados['sku'], false, 'dados do winthor incompletos');
                }
            }
        }
        $this->integrarIntermediarioApi(true, false);
        return $this->index();
    }
    
    private function getProdutosAtualizar($flag = true){
        $ret = array();
        if($flag){
            $sql = "select * from consulta_remedios";
            
        }
        else{
           $sql = "select * from consulta_remedios where cadastro > sincro and cadastro > erro";
        }
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['sku'];
            }
        }
        
        return $ret;
    }
    
    private function montaSqlAtualizacaoWinthor($produto){
        $ret = "SELECT
					PCPRODUT.*,
					PCPRODFILIAL.ORIGMERCTRIB
				FROM
					PCPRODUT,
					PCPRODFILIAL
				WHERE
					PCPRODUT.CODPROD = PCPRODFILIAL.CODPROD (+)
					AND PCPRODFILIAL.CODFILIAL = 1
					AND PCPRODUT.OBS2 <> 'FL'
					AND PCPRODUT.DTEXCLUSAO IS NULL
					AND PCPRODUT.REVENDA = 'S'
					AND CODEPTO IN (1,12)
                    AND PCPRODUT.CODAUXILIAR = '$produto'";
        return $ret;
    }
    
    private function verificarProdutoDeveSerIntegrado($cod_prod){
        return isset($this->_produtos_integrar[trim(strval($cod_prod))]);
    }
    
    private function popularProdutosIntegrar(){
        if($this->_flag_produtos_integrar === false){
            $this->_flag_produtos_integrar = true;
            $sql = "select cod_prod from gf_produtos_marketplace where integracao = 'CR'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $temp = trim(strval($row['cod_prod']));
                    $this->_produtos_integrar[$temp] = $temp;
                }
            }
        }
    }
    
    
    
    private function verificarProdutoDeveSerZerado($cod_prod){
        return isset($this->_produtos_zerar[trim(strval($cod_prod))]);
    }
    
    private function popularProdutosZerar(){
        if($this->_flag_produtos_zerar === false){
            $this->_flag_produtos_zerar = true;
            $sql = "select sku from consulta_remedios";
            $todos_os_sku_integrados = query($sql);
            $dados_produtos_winthor = $this->getListaProdutos(false);
            foreach ($todos_os_sku_integrados as $temp){
                if(isset($dados_produtos_winthor[$temp['sku']]['codigo'])){
                    $codigo = trim(strval($dados_produtos_winthor[$temp['sku']]['codigo']));
                    if(!$this->verificarProdutoDeveSerIntegrado($codigo)){
                        $this->_produtos_zerar[$codigo] = $codigo;
                    }
                }
            }
        }
    }
    
    private function verificarProdutoJaFoiImportado($cod_prod){
        return isset($this->_produtos_importados[trim(strval($cod_prod))]);
    }
    
    private function popularProdutosImportados(){
        if($this->_flag_produtos_importados === false){
            $this->_flag_produtos_importados = true;
            $sql = "select sku from consulta_remedios";
            $todos_os_sku_integrados = query($sql);
            $dados_produtos_winthor = $this->getListaProdutos(false);
            foreach ($todos_os_sku_integrados as $temp){
                if(isset($dados_produtos_winthor[$temp['sku']]['codigo'])){
                    $codigo = trim(strval($dados_produtos_winthor[$temp['sku']]['codigo']));
                    $this->_produtos_importados[$codigo] = $codigo;
                }
            }
        }
    }
    
    private function montarCardPesquisa(){
        $ret = '';
        formbase01::setLayout('basico');
        
        $param = array(
            'nome' => 'formPesquisa[sku]',
            'etiqueta' => 'Cod. de barra',
            'mascara' => 'I',
            'valor' => '',
        );
        $campo_sku = formbase01::formTexto($param);
        
        $param = array(
            'nome' => 'formPesquisa[codprod]',
            'etiqueta' => 'Cod. do produto',
            'mascara' => 'I',
            'valor' => '',
        );
        $campo_codprod = formbase01::formTexto($param);
        
        $param = array(
            'posicao' => 'C'
        );
        $bt_enviar = formbase01::formSend($param);
        
        $param = array();
        $param['tamanhos'] = array(4, 4, 4);
        $param['conteudos'] = array($bt_enviar, $campo_sku, $campo_codprod);
        $linha = addLinha($param);
        
        $param = array(
            'acao' =>  getLink().'index',
            'nome' => 'formPesquisa',
            'id' => 'formPesquisa',
        );
        
        $ret = formbase01::form($param, $linha);
        
        $param = array(
            'titulo' => 'Pesquisar',
            'conteudo' => $ret,
        );
        return addCard($param);
    }
    
    private function montarCardResultadoPesquisa(){
        $ret = '';
        $pesquisar_por = $_POST['formPesquisa'];
        $dados = array();
        if(!empty($pesquisar_por['sku']) || !empty($pesquisar_por['codprod'])){
            $dados_produtos = $this->getListaProdutos(false);
            //$rest = new rest_consulta_remedios('https://loja.stagingcr.com.br', '851cdaff-43ca-4d52-95d9-1c8efce5e0c0');
            $rest = new rest_consulta_remedios('https://loja.consultaremedios.com.br', 'af79459f-d325-4865-8ecc-117f63d722c0');
            if(!empty($pesquisar_por['sku'])){
                if(strval($pesquisar_por['sku']) !== '0'){
                    $retorno = $rest->pesquisarProduto($pesquisar_por['sku']);
                    if(is_array($retorno) && count($retorno) > 0){
                        $dados = $this->montaLinhasPesquisa($retorno, $dados_produtos, '', $pesquisar_por['sku']);
                    }
                    elseif($retorno === false){
                        addPortalMensagem('Erro', 'Não existe nenhum produto com esse código de barra','erro');
                    }
                }
            }
            if(!empty($pesquisar_por['codprod'])){
                if(strval($pesquisar_por['codprod']) !== '0'){
                    $codigo = $pesquisar_por['codprod'];
                    $sql = "select 
                            CODPROD,
                            NOMEECOMMERCE,
                            DESCRICAO,
                            CODAUXILIAR
                         from 
                            pcprodut 
                        where 
                            CODPROD = '$codigo'";
                    $rows = query4($sql);
                    if(is_array($rows) && count($rows) > 0){
                        $sku = $rows[0]['CODAUXILIAR'];
                        $retorno = $rest->pesquisarProduto($sku);
                        if(is_array($retorno) && count($retorno) > 0){
                            $dados = $this->montaLinhasPesquisa($retorno, $dados_produtos, $codigo);
                        }
                        elseif($retorno === false){
                            addPortalMensagem('Erro', 'Esse produto não ainda não existe no Consulta Remédios','erro');
                        }
                    }
                    else{
                        addPortalMensagem('Erro', 'Não existe nenhum produto com esse código','erro');
                    }
                }
            }
        }
        if(count($dados) > 0){
            $tabela = new tabela01(array());
            $tabela->addColuna(array('campo' => 'campo' , 'etiqueta' => 'Campo' ,'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
            $tabela->addColuna(array('campo' => 'gf' , 'etiqueta' => 'Gaucha Farma' ,'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
            $tabela->addColuna(array('campo' => 'cr' , 'etiqueta' => 'Consulta Remédios' ,'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
            
            $tabela->setDados($dados);
            $ret .= $tabela;
            
            $param = array(
                'titulo' => 'Resultado da Pesquisa',
                'conteudo' => $ret,
            );
            $ret = addCard($param);
        }
        return $ret;
    }
    
    private function montaLinhasPesquisa($resposta_rest, $dados_produtos, $codprod = '', $sku = ''){
        $ret = array();
        $sku_atual = $resposta_rest['sku'];
        
        $dados_intermediario = $this->getDadosIntermediario($resposta_rest['sku'], true);
        if(count($dados_intermediario) < 1){
            foreach ($resposta_rest['secondary_skus'] as $sku_secundario){
                if(count($dados_intermediario) < 1){
                    $dados_intermediario = $this->getDadosIntermediario($sku_secundario, true);
                }
            }
        }
        
        if(count($dados_intermediario) > 1){
            //caso o produto esteja cadastrado na tabela intermediaria
            if(!isset($dados_produtos[$sku_atual])){
                foreach ($resposta_rest['secondary_skus'] as $sku_secundario){
                    if(!isset($dados_produtos[$sku_atual])){
                        $sku_atual = $sku_secundario;
                    }
                }
            }
            
            if($codprod === ''){
                $codprod = $dados_produtos[$sku_atual]['codigo'];
            }
            $temp = array(
                'campo' => 'Nome',
                'gf' => $dados_produtos[$sku_atual]['nome'],
                'cr' => $resposta_rest['product'],
            );
            $ret[] = $temp;
            
            $temp = array(
                'campo' => 'Codigo do Produto',
                'gf' => $codprod,
                'cr' => '',
            );
            $ret[] = $temp;
            
            $temp = array(
                'campo' => 'Código de Barras',
                'gf' => !empty($sku) ? $sku : $sku_atual,
                'cr' => $sku_atual,
            );
            $ret[] = $temp;
            
            
            
            $temp = array(
                'campo' => 'Status',
                'gf' => $dados_intermediario['mensagem_erro'] === '' ? 'OK' : 'ERRO(' . $dados_intermediario['mensagem_erro'] . ')',
                'cr' => $this->montarStatusRespostaApi($resposta_rest),
            );
            $ret[] = $temp;
            
            $temp = array(
                'campo' => 'Estoque',
                'gf' => $dados_intermediario['estoque'],
                'cr' => $resposta_rest['stock'],
            );
            $ret[] = $temp;
            
            $temp = array(
                'campo' => 'Preço',
                'gf' => str_replace('.', ',', $dados_intermediario['preco']),
                'cr' => str_replace('.', ',', $resposta_rest['price'])
            );
            $ret[] = $temp;
        }
        else{
            //caso o produto não esteja cadastrado na tabela intermediaria
            addPortalMensagem('Erro', 'Nenhum produto com esse código ou código de barra foi importado do Winthor','erro');
        }
        
        return $ret;
    }
    
    private function montarStatusRespostaApi($resposta_rest){
        $status_raw = $resposta_rest['status'];
        $ret = 'sem status';
        switch ($status_raw) {
            case 'ready':
            $ret = 'OK';
            break;
            default:
            break;
        }
        
        return $ret;
    }
}