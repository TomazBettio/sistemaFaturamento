<?php
//arrumar quantidades para diferentes tipos de pacotess
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class integracao_martins{
    var $funcoes_publicas = array(
        'index'                 => true,
        'editarConfig'          => true,
        'salvarConfig'          => true,
        'integrarProdutos'      => true,
        'integrarPrecos'        => true,
        'integrarEstoque'       => true,
        'integrarFotos'         => true,
        'gerarRelatorio'        => true,
        'editarCategorias'      => true,
        'salvarCategorias'      => true,
        'excluirCategorias'     => true,
        'integrarPendentes'     => true,
        'schedule'              => true,
    );
    
    private $_programa;
    private $_marcas;
    private $_unidades;
    private $_estoque_maximo;
    private $_estoque_minimo;
    private $_categorias_gf;
    private $_categorias_martins;
    private $_medidas_caixa;
    private $_medidas_multi;
    
    private $_produtos_integrar;
    private $_flag_produtos_integrar;
    private $_produtos_zerar;
    private $_flag_produtos_zerar;
    
    function __construct(){
        set_time_limit(0);
        $this->_marcas = array();
        $this->_unidades = array();
        $this->_estoque_maximo = 1000;
        $this->_estoque_minimo = $this->getConfig('estoque_min') == '' ? 0 : intval($this->getConfig('estoque_min'));
        $this->_programa = 'integracao_martins';
        
        $this->_categorias_gf = array();
        $this->_categorias_martins = array();
        
        $this->_medidas_caixa = array();
        $this->_medidas_multi = array();
        
        $this->_produtos_integrar = array();
        $this->_flag_produtos_integrar = false;
        $this->_produtos_zerar = array();
        $this->_flag_produtos_zerar = false;
    }
    
    function index(){
        $ret = '';
        $titulo = 'Integração Martins';
        
        //print_r($this->getCategoriasMartinsLista());
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."integrarProdutos')",
            'tamanho' => 'grande',
            'cor' => 'danger',
            'texto' => 'Reintegrar Tudo',
        	'ativo' => false,
            'bloco' => true,
        );
        $bt_produtos = formbase01::formBotao($param);
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."integrarPendentes')",
            'tamanho' => 'grande',
            'cor' => 'success',
            'texto' => 'Integrar Produtos Pendentes',
            'bloco' => true,
        );
        $bt_pendentes = formbase01::formBotao($param);
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."integrarPrecos')",
            'tamanho' => 'grande',
            'cor' => 'success',
            'texto' => 'Integrar Preços',
            'bloco' => true,
        );
        $bt_precos = formbase01::formBotao($param);
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."integrarEstoque')",
            'tamanho' => 'grande',
            'cor' => 'success',
            'texto' => 'Integrar Estoque',
            'bloco' => true,
        );
        $bt_estoque = formbase01::formBotao($param);
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."integrarFotos')",
            'tamanho' => 'grande',
            'cor' => 'success',
            'texto' => 'Integrar Fotos',
            'bloco' => true,
        );
        $bt_fotos = formbase01::formBotao($param);
        
        $param = array(
            'onclick' => "setLocation('" . getLink() ."gerarRelatorio')",
            'tamanho' => 'grande',
            'cor' => 'primary',
            'texto' => 'Gerar Relatório',
            'bloco' => true,
        );
        $bt_relatorio = formbase01::formBotao($param);
        
        
        $param = array();
        $param['tamanhos'] = array(3, 3, 3, 3);
        $param['conteudos'] = array('', $bt_pendentes, $bt_precos, '');
        $ret .= addLinha($param) . '<br>';
        
        $param = array();
        $param['tamanhos'] = array(3, 3, 3, 3);
        $param['conteudos'] = array('', $bt_estoque, $bt_fotos, '');
        $ret .= addLinha($param) . '<br>';
        
        $param = array();
        $param['tamanhos'] = array(3, 3, 3, 3);
        $param['conteudos'] = array('', $bt_produtos, $bt_relatorio, '');
        $ret .= addLinha($param);
        
        
        $param = array();
        $bt = array();
        $bt['onclick'] = "setLocation('".getLink()."editarConfig')";
        $bt['tamanho'] = 'padrao';
        $bt['cor'] = 'sucess';
        $bt['texto'] = 'Configurar';
        $param['botoesTitulo'][] = $bt;
        
        $bt = array();
        $bt['onclick'] = "setLocation('".getLink()."editarCategorias')";
        $bt['tamanho'] = 'padrao';
        $bt['cor'] = 'sucess';
        $bt['texto'] = 'Editar Categorias';
        $param['botoesTitulo'][] = $bt;
        $param['titulo'] = $titulo;
        $param['conteudo'] = $ret;
        $ret = addCard($param);
        return $ret;
    }
    
    public function schedule($param){
        $opcoes = explode(';', $param);
        if(in_array('estoque', $opcoes)){
            $this->integrarEstoque(false);
        }
        if(in_array('precos', $opcoes)){
            $this->integrarPrecos(false);
        }
    }
    
    public function editarConfig(){
        $param = array(
            'geraScriptValidacaoObrigatorios' => false,
        );
        $form = new form01($param);
        //$this->addJsCategorias();
        $dados = $this->getConfig();
        //$form->setPastas(array('Produtos', 'Preços', 'Estoque', 'Categorias'));
        //$form->setPastas(array('Produtos', 'Preços', 'Estoque'));
        $form->setPastas(array('Produtos', 'Preços', 'Estoque'));
        
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[produtos_somente]'         , 'etiqueta' => 'Somente Produtos'      , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['produtos_somente'] , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[produtos_excluidos]'       , 'etiqueta' => 'Produtos Excluídos'    , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['produtos_excluidos'] , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[marcas_somente]'           , 'etiqueta' => 'Somente Marcas'        , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['marcas_somente'] , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[marcas_excluidos]'         , 'etiqueta' => 'Marcas Excluídas'      , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['marcas_excluidos'] , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[fornecedores_somente]'     , 'etiqueta' => 'Somente Fornecedores'  , 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['fornecedores_somente'] , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        //$form->addCampo(array('id' => '', 'campo' => 'formconfig[fornecedores_excluidos]'   , 'etiqueta' => 'Fornecedores Excluídos', 'tipo' => 'TA' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['fornecedores_excluidos'] , 'pasta'	=> 0 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        $form = $this->addCamposEstados($form, $dados);
        
        $form->addCampo(array('id' => '', 'campo' => 'formconfig[estoque_min]'   , 'etiqueta' => 'Estoque Minimo', 'tipo' => 'N' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['estoque_min'] , 'pasta'	=> 2 , 'lista' => '', 'validacao' => '', 'largura' => 12, 'obrigatorio' => false));
        
        //$form->addConteudoPastas(3, $this->criarFormCategorias());
        

        
        $form->setEnvio(getLink() . 'salvarConfig', 'formconfig', 'formconfig');
        
        /*
        $form_teste = $form . formbase01::formSend();
        $form_teste = formbase01::form(array(
            'acao' => getLink() . 'salvarConfig',
            'id'   => 'formconfig',
            'nome' => 'formconfig',
        ), $form_teste);
        */
        
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
        //return addBoxInfo('Configurar Parâmetros da Integração', $form_teste . '', $param);
    }
    
    private function addCamposEstados($form, $dados){
        $estados = array('AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO');
        $lista = $this->montaListaPrecos();
        foreach ($estados as $e){
            $form->addCampo(array('id' => '', 'campo' => 'formconfig[' . $e . ']'   , 'etiqueta' => 'Lista de preços ' . $e, 'tipo' => 'A' , 'tamanho' => '35', 'linhas' => '', 'valor' => $dados[$e] , 'pasta'	=> 1 , 'lista' => $lista, 'validacao' => '', 'largura' => 6, 'obrigatorio' => false));
            $vl = isset($dados['cliente_'.$e]) ? $dados['cliente_'.$e] : '';
            $form->addCampo(array('id' => '', 'campo' => 'formconfig[cliente_' . $e . ']'   , 'etiqueta' => 'Cliente ' . $e, 'tipo' => 'T' , 'tamanho' => '35', 'linhas' => '', 'valor' => $vl , 'pasta'	=> 1 , 'lista' => '', 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
        }
        return $form;
    }
    
    public function salvarConfig(){
        $dados = getParam($_POST, 'formconfig');
//print_r($dados);die();
        foreach ($dados as $parametro => $valor){
            if($parametro != 'selectCategoriaGF' && $parametro != 'selectCategoriaMA' && $parametro != 'tabelacategorias'){
            	if(strpos($parametro, 'cliente_') === false){
                	$sql = "update gf_martins_config set valor = '$valor' where parametro = '$parametro'";
            	}else{
            		$uf = str_replace('cliente_', '', $parametro);
            		$valor = empty($valor) ? 0 : $valor;
            		$sql = "update gf_martins_config set cliente = $valor where parametro = '$uf'";
            		
            	}
//echo "$sql <br>\n";
                query($sql);
            }
        }
        return $this->index();
    }
    
    private function getConfig($config = ''){
        $ret = '';
        if($config == ''){
            $ret = array();
            $parametro = array();
            foreach ($parametro as $p){
                $ret[$p] = '';
            }
            
            $sql = "select * from gf_martins_config";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[$row['parametro']] = $row['valor'];
                    if(strlen($row['parametro']) == 2){
                    	$ret['cliente_'.$row['parametro']] = $row['cliente'];
                    }
                }
            }
        }
        else{
            $ret = '';
            $sql = "select * from gf_martins_config where parametro = '$config'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0]['valor'];
            }
        }
//print_r($ret);
        return $ret;
    }
    
    public function integrarPendentes($mensagem = true){
        $this->gerarLogExecucoes('PRODUTOS_PENDENTES');
        $this->popularProdutosIntegrar();
        $this->popularProdutosZerar();
        $data = date('Y-m-d H:i:s');
        
        $this->carregaMarcas();
        
        $sql = $this->montarSqlProdutos();
        
        $produtos_ignorar = $this->getProdutosIntegrados();
        
        $rows = query4($sql);
        $contadores = array(
            'iguais' => 0,
            'modificados' => 0,
            'novos' => 0,
        );
        $produtos_sem_categoria = array();
        
        $temp = array();
        
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if($this->verificarProdutoDeveSerIntegrado($row['CODPROD']) && !in_array(trim($row['CODPROD']), $produtos_ignorar)){
                    $temp = array();
                    //////////////////////////////////////////////////////////////////////////////////////
                    //copiado diretamente do f1
                    $temp['codigo_produto'] = $row['CODPROD'];
                    $codfabricante			= $row['CODFAB'];
                    //$temp['descricao'] = strtolower(str_replace("'", '´',$row['DESCRICAO']));
                    $temp['descricao'] = strtolower(str_replace("'", '´',$row['NOMEECOMMERCE_MARKETPLACE']));
                    $temp['cod_barra'] = $row['CODAUXILIAR'];
                    $temp['peso']				= $this->formatarValor($row['PESOBRUTO']);
                    $temp['altura']				=  $this->tirarZerosFinal(floatval($this->formatarValor($row['ALTURAARM'])) / 100);
                    $temp['largura']			= $this->tirarZerosFinal(floatval($this->formatarValor($row['LARGURAARM'])) / 100);
                    $temp['comprimento']		=  $this->tirarZerosFinal(floatval($this->formatarValor($row['COMPRIMENTOARM'])) / 100);
                    $temp['ncm'] = $row['NBM'];
                    
                    $temp['manifaturante_codigo'] = strtolower($row['CODMARCA']);
                    $temp['manifaturante_nome'] = strtolower($this->_marcas[$row['CODMARCA']]);
                    
                    ;
                    $temp['unidade_de_medida'] = strtolower($this->getUnidade($row['UNIDADE']));
                    $temp['unidade_de_medida_sigla'] = strtolower($row['UNIDADE']);
                    /////////////////////////////////////////////////////////////////////////////////////
                    //combinados com o Alexandre
                    if(in_array(strtolower($row['UNIDADE']), $this->_medidas_caixa)){
                        $temp['venda_multipla'] = 0; //nenhum produto é vendido em multiplos
                        $temp['quantidade_minima'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_pacote'] = 10; //nenhum produto é vendido em multiplos
                        $temp['quantidade_multipla'] = 0; //nenhum produto é vendido em multiplos
                    }
                    elseif(in_array(strtolower($row['UNIDADE']), $this->_medidas_multi)){
                        $temp['venda_multipla'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_minima'] = 10; //nenhum produto é vendido em multiplos
                        $temp['quantidade_pacote'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_multipla'] = 10; //nenhum produto é vendido em multiplos
                    }
                    else{
                        $temp['venda_multipla'] = 0; //nenhum produto é vendido em multiplos
                        $temp['quantidade_minima'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_pacote'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_multipla'] = 0; //nenhum produto é vendido em multiplos
                    }
                    $temp['quantidade_manufaturante'] = 1;
                    $temp['modelo'] = $this->extrairModeloProduto('',$codfabricante); //não existem modelos diferentes
                    /////////////////////////////////////////////////////////////////////////////////////
                    //não sei se estão corretos
                    $temp['garantia_meses'] = $row['PRAZOGARANTIA'] == '' ? 0 : $row['PRAZOGARANTIA'];
                    $temp['tempo_entrega_adicional'] = $row['PRAZOENTR'] == '' ? 0 : $row['PRAZOENTR'];
                    
                    $categoria_gf = $row['CODSEC'] . '-' . $row['CODCATEGORIA'] . '-' . $row['CODSUBCATEGORIA'];
                    $temp['categoria'] = $this->getConfig($categoria_gf);
                    //$temp['nome_produto'] = $this->montarNomeProduto($row, $temp['categoria'], $codfabricante);
                    //modificado em 10/06/22 por Emanuel Thiel, o nome do produto passou a ser NOMEECOMMERCE_MARKETPLACE
                    //$temp['nome_produto'] = $row['NOMEECOMMERCE_MARKETPLACE'];
                    $temp['nome_produto'] = $this->corrigirNomeEcommerceNovo($row['NOMEECOMMERCE_MARKETPLACE']);
                    
                    
                    $temp['descricao'] = $temp['nome_produto'];
                    if(!empty($row['DESCRICAO1'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO1']));
                    }
                    if(!empty($row['DESCRICAO2'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO2']));
                    }
                    if(!empty($row['DESCRICAO3'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO3']));
                    }
                    if(!empty($row['DESCRICAO4'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO4']));
                    }
                    if(!empty($row['DESCRICAO5'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO5']));
                    }
                    if(!empty($row['DESCRICAO6'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO6']));
                    }
                    if(!empty($row['DESCRICAO7'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO7']));
                    }
                    
                    $temp['descricao'] = str_replace("'", "''", $temp['descricao']);
                    ///////////////////////////////////////////////////////////////////////////////////////
                    //não precisam ser preenchidos
                    if($temp['categoria'] != ''){
                        $verificar_linha = $this->verificarUpdate($temp, 'martins_produtos', 'codigo_produto', 'codigo_produto');
                        if($verificar_linha === false){
                            //a entrada já existe e não foi modificada
                            $contadores['iguais'] += 1;
                        }
                        elseif($verificar_linha === 1){
                            //a entrada foi modificada
                            $temp['sincro'] = '0000-00-00 00:00:00';
                            $temp['erro'] = '0000-00-00 00:00:00';
                            $contadores['modificados'] += 1;
                            $sql = montaSQL($temp, 'martins_produtos', 'UPDATE', "codigo_produto = '" . $temp['codigo_produto'] . "'");
                            query($sql);
                        }
                        elseif($verificar_linha === 2){
                            //a linha é nova
                            $contadores['novos'] += 1;
                            $sql = montaSQL($temp, 'martins_produtos');
                            query($sql);
                        }
                    }
                    else{
                        $produtos_sem_categoria[] = $temp['codigo_produto'];
                    }
                }
            }
        }
        
        
        $obj = new integra_martins();
        $obj->migracaoProdutosCompleta();
        
        /*
         $this->integrarPrecos(false);
         $this->integrarEstoque(false);
         $this->integrarFotos(false);
         */
        if($mensagem){
            $mensagem_portal = "Produtos recuperados do winthor com sucesso<br>&nbsp;" . $contadores['novos'] . " Produtos Novos<br>&nbsp;" . $contadores['modificados'] . " Produtos Modificados<br>&nbsp;" . $contadores['iguais'] . " Produtos Iguais";
            addPortalMensagem($mensagem_portal);
            if(count($produtos_sem_categoria) > 0){
                $mensagem_portal = "Os seguintes produtos estão sem categoria e não foram recuperados<br>" . implode(', ', $produtos_sem_categoria);
                addPortalMensagem($mensagem_portal, 'info');
            }
            $this->mensagemPortalIntegracao('martins_produtos', $data);
            return $this->index();
        }
        else{
            return $contadores;
        }
    }
    
    public function integrarProdutos($mensagem = true){
        $this->gerarLogExecucoes('PRODUTOS');
        $this->popularProdutosIntegrar();
        $this->popularProdutosZerar();
        $data = date('Y-m-d H:i:s');
        
        $this->carregaMarcas();
        
        $sql = $this->montarSqlProdutos();   
        
        $rows = query4($sql);
        $contadores = array(
            'iguais' => 0,
            'modificados' => 0,
            'novos' => 0,
        );
        $produtos_sem_categoria = array();
        
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if($this->verificarProdutoDeveSerIntegrado($row['CODPROD']) || $this->verificarProdutoDeveSerZerado($row['CODPROD'])){
                    $temp = array();
                    //////////////////////////////////////////////////////////////////////////////////////
                    //copiado diretamente do f1
                    $temp['codigo_produto'] = $row['CODPROD'];
                    $codfabricante			= $row['CODFAB'];
                    //$temp['descricao'] = strtolower(str_replace("'", '´',$row['DESCRICAO']));
                    $temp['descricao'] = strtolower(str_replace("'", '´',$row['NOMEECOMMERCE_MARKETPLACE']));
                    $temp['cod_barra'] = $row['CODAUXILIAR'];
                    $temp['peso']				= $this->formatarValor($row['PESOBRUTO']);
                    $temp['altura']				=  $this->tirarZerosFinal(floatval($this->formatarValor($row['ALTURAARM'])) / 100);
                    $temp['largura']			= $this->tirarZerosFinal(floatval($this->formatarValor($row['LARGURAARM'])) / 100);
                    $temp['comprimento']		=  $this->tirarZerosFinal(floatval($this->formatarValor($row['COMPRIMENTOARM'])) / 100);
                    $temp['ncm'] = $row['NBM'];
                    
                    $temp['manifaturante_codigo'] = strtolower($row['CODMARCA']);
                    $temp['manifaturante_nome'] = strtolower($this->_marcas[$row['CODMARCA']]);
                    
                    ;
                    $temp['unidade_de_medida'] = strtolower($this->getUnidade($row['UNIDADE']));
                    $temp['unidade_de_medida_sigla'] = strtolower($row['UNIDADE']);
                    /////////////////////////////////////////////////////////////////////////////////////
                    //combinados com o Alexandre
                    if(in_array(strtolower($row['UNIDADE']), $this->_medidas_caixa)){
                        $temp['venda_multipla'] = 0; //nenhum produto é vendido em multiplos
                        $temp['quantidade_minima'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_pacote'] = 10; //nenhum produto é vendido em multiplos
                        $temp['quantidade_multipla'] = 0; //nenhum produto é vendido em multiplos
                    }
                    elseif(in_array(strtolower($row['UNIDADE']), $this->_medidas_multi)){
                        $temp['venda_multipla'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_minima'] = 10; //nenhum produto é vendido em multiplos
                        $temp['quantidade_pacote'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_multipla'] = 10; //nenhum produto é vendido em multiplos
                    }
                    else{
                        $temp['venda_multipla'] = 0; //nenhum produto é vendido em multiplos
                        $temp['quantidade_minima'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_pacote'] = 1; //nenhum produto é vendido em multiplos
                        $temp['quantidade_multipla'] = 0; //nenhum produto é vendido em multiplos
                    }
                    $temp['quantidade_manufaturante'] = 1;
                    $temp['modelo'] = $this->extrairModeloProduto('',$codfabricante); //não existem modelos diferentes
                    /////////////////////////////////////////////////////////////////////////////////////
                    //não sei se estão corretos
                    $temp['garantia_meses'] = $row['PRAZOGARANTIA'] == '' ? 0 : $row['PRAZOGARANTIA'];
                    $temp['tempo_entrega_adicional'] = $row['PRAZOENTR'] == '' ? 0 : $row['PRAZOENTR'];
                    
                    $categoria_gf = $row['CODSEC'] . '-' . $row['CODCATEGORIA'] . '-' . $row['CODSUBCATEGORIA'];
                    $temp['categoria'] = $this->getConfig($categoria_gf);
                    //$temp['nome_produto'] = $this->montarNomeProduto($row, $temp['categoria'], $codfabricante);
                    //modificado em 10/06/22 por Emanuel Thiel, o nome do produto passou a ser NOMEECOMMERCE_MARKETPLACE
                    //$temp['nome_produto'] = $row['NOMEECOMMERCE_MARKETPLACE'];
                    $temp['nome_produto'] = $this->corrigirNomeEcommerceNovo($row['NOMEECOMMERCE_MARKETPLACE']);
                    
                    
                    $temp['descricao'] = $temp['nome_produto'];
                    if(!empty($row['DESCRICAO1'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO1']));
                    }
                    if(!empty($row['DESCRICAO2'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO2']));
                    }
                    if(!empty($row['DESCRICAO3'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO3']));
                    }
                    if(!empty($row['DESCRICAO4'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO4']));
                    }
                    if(!empty($row['DESCRICAO5'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO5']));
                    }
                    if(!empty($row['DESCRICAO6'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO6']));
                    }
                    if(!empty($row['DESCRICAO7'])){
                        $temp['descricao'] .= "\n";
                        $temp['descricao'] .= ucwords(strtolower($row['DESCRICAO7']));
                    }
                    $temp['descricao'] = str_replace("'", "''", $temp['descricao']);
                    ///////////////////////////////////////////////////////////////////////////////////////
                    //não precisam ser preenchidos
                    if($temp['categoria'] != ''){
                        $verificar_linha = $this->verificarUpdate($temp, 'martins_produtos', 'codigo_produto', 'codigo_produto');
                        if($verificar_linha === false){
                            //a entrada já existe e não foi modificada
                            $contadores['iguais'] += 1;
                        }
                        elseif($verificar_linha === 1){
                            //a entrada foi modificada
                            $temp['sincro'] = '0000-00-00 00:00:00';
                            $temp['erro'] = '0000-00-00 00:00:00';
                            $contadores['modificados'] += 1;
                            $sql = montaSQL($temp, 'martins_produtos', 'UPDATE', "codigo_produto = '" . $temp['codigo_produto'] . "'");
                            query($sql);
                        }
                        elseif($verificar_linha === 2){
                            //a linha é nova
                            $contadores['novos'] += 1;
                            $sql = montaSQL($temp, 'martins_produtos');
                            query($sql);
                        }
                    }
                    else{
                        $produtos_sem_categoria[] = $temp['codigo_produto'];
                    }
                }
            }
        }
        
        
        $obj = new integra_martins();
        $obj->migracaoProdutosCompleta();
        
        $this->integrarPrecos(false);
        $this->integrarEstoque(false);
        $this->integrarFotos(false);
        if($mensagem){
            $mensagem_portal = "Produtos recuperados do winthor com sucesso<br>&nbsp;" . $contadores['novos'] . " Produtos Novos<br>&nbsp;" . $contadores['modificados'] . " Produtos Modificados<br>&nbsp;" . $contadores['iguais'] . " Produtos Iguais";
            addPortalMensagem($mensagem_portal);
            if(count($produtos_sem_categoria) > 0){
                $mensagem_portal = "Os seguintes produtos estão sem categoria e não foram recuperados<br>" . implode(', ', $produtos_sem_categoria);
                addPortalMensagem($mensagem_portal, 'info');
            }
            $this->mensagemPortalIntegracao('martins_produtos', $data);
            return $this->index();
        }
        else{
            return $contadores;
        }
    }
    
    private function verificarUpdate($linha, $tabela, $indice_entrada, $coluna_entrada){
        //false -> já existe e não foi modificado 1 -> entrada modificada 2 -> nova entrada
        $ret = false;
        $sql = "select * from $tabela where $coluna_entrada = '" . $linha[$indice_entrada] . "'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $row = $rows[0];
            foreach ($linha as $campo => $valor){
                if($row[$campo] != $valor){
                    $ret = 1;
                }
            }
            if(strcmp($row['cadastro'], $row['erro']) < 0){
                $ret = 1;
            }
            if($ret === false && $tabela == 'martins_estoque'){
                //modificado em 20/10/22 por Emanuel
                //força o programa a refazer a sincronização de produtos com o estoque máximo
                //separei em 2 ifs pois não lembro se o php avalia todas as expressões de um if caso as primeiras sejam falsas
                if($linha['estoque'] == $this->_estoque_maximo){
                    $ret = 1;
                    log::gravaLog('martins_teste_estoque_max', $linha['produto']);
                }
            }
        }
        else{
            $ret = 2;
        }
        return $ret;
    }
    
    private function montarIn($string_dados){
        $ret = '';
        $array_dados = explode(',', $string_dados);
        $temp = array();
        foreach ($array_dados as $d){
            $temp[] = "'$d'";
        }
        if(count($temp) > 0){
            $ret = '(' . implode(', ', $temp) . ')';
        }
        return $ret;
    }
    
    private function formatarValor($valor){
        $pos_ponto = strpos($valor, '.');
        if($pos_ponto === 0){
            $valor = '0' . $valor;
        }
        return $valor . '';
    }
    
    private function carregaMarcas(){
        $sql = "SELECT * FROM PCMARCA";
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $this->_marcas[$row['CODMARCA']] = $row['MARCA'];
            }
        }
    }
    
    private function getUnidade($unidade){
        $sql = "SELECT * FROM pcunidade";
        $rows = query4($sql);
        if(!isset($this->_unidades[$unidade])){
            $sql = "SELECT descricao FROM pcunidade WHERE unidade =  '$unidade'";
            $rows = query4($sql);
            if(isset($rows[0]['DESCRICAO'])){
                $this->_unidades[$unidade] = $rows[0]['DESCRICAO'];
            }else{
                $this->_unidades[$unidade] = '';
            }
        }
        
        return $this->_unidades[$unidade];
    }
    
    private function montarSqlProdutos(){
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
					";
        $order_by = " ORDER BY
PCPRODUT.CODPROD";
        /*
        $parametros = $this->getConfig();
        
        
        if($parametros['produtos_somente'] != ''){
            $string_produtos_somente = $this->montarIn($parametros['produtos_somente']);
            if($string_produtos_somente != ''){
                $ret .="AND PCPRODUT.CODPROD IN $string_produtos_somente";
            }
        }
        
        if($parametros['produtos_excluidos'] != ''){
            $string_produtos_excluidos = $this->montarIn($parametros['produtos_excluidos']);
            if($string_produtos_excluidos != ''){
                $ret .= "AND PCPRODUT.CODPROD NOT IN $string_produtos_excluidos";
            }
        }
        
        if($parametros['marcas_somente'] != ''){
            $string_marcas_somente = $this->montarIn($parametros['marcas_somente']);
            if($string_marcas_somente != ''){
                $ret .= "AND PCPRODUT.CODMARCA IN $string_marcas_somente";
            }
        }
        
        if($parametros['marcas_excluidos'] != ''){
            $string_marcas_excluidos = $this->montarIn($parametros['marcas_excluidos']);
            if($string_marcas_excluidos != ''){
                $ret .= "AND PCPRODUT.CODMARCA NOT IN $string_marcas_excluidos";
            }
        }
        
        if($parametros['fornecedores_somente'] != ''){
            $string_fornecedores_somente = $this->montarIn($parametros['fornecedores_somente']);
            if($string_fornecedores_somente != ''){
                $ret .= "AND PCPRODUT.CODFORNEC IN $string_fornecedores_somente";
            }
        }
        
        if($parametros['fornecedores_excluidos'] != ''){
            $string_fornecedores_excluidos = $this->montarIn($parametros['fornecedores_excluidos']);
            if($string_fornecedores_excluidos != ''){
                $ret .= "AND PCPRODUT.CODFORNEC NOT IN $string_fornecedores_excluidos";
            }
        }
        */
        
        
        $ret .= '   ' . $order_by;
        
        return $ret;
    }
    
    private function verficarTodosOsProdutosPossuemPrecoWintor($produtos, $listasPrecos){
        $ret = array();
        foreach ($produtos as $p){
            $sql = "select NUMREGIAO, PTABELA1 from pctabpr where codprod = '$p'";
            $temp = array();
            foreach ($listasPrecos as $l){
                $temp[] = "'" . $l . "'";
            }
            $sql .= " and NUMREGIAO in (" . implode(', ', $temp) . ')';
            $rows = query4($sql);
            if(is_array($rows) && count($rows) > 0){
                $erro = false;
                foreach ($rows as $row){
                    if(empty($row['PTABELA1'])){
                        $erro = true;
                    }
                }
                if($erro){
                    $ret[] = $p;
                }
            }
            else{
                $ret[] = $p;
            }
        }
        return $ret;
        
    }
    
    public function integrarPrecos($mensagem = true){
        $this->gerarLogExecucoes('PRECOS');
        if($mensagem){
            $this->popularProdutosIntegrar();
            $this->popularProdutosZerar();
        }
        $data = date('Y-m-d H:i:s');
        
        $produtos = $this->getProdutosIntegrados();
        $listasPrecos = $this->getListasPrecos();
        $clientesPadrao = $this->getClientesPadrao();
        $contadores = array(
            'iguais' => 0,
            'modificados' => 0,
            'novos' => 0,
        );
        
        $produtos_com_problema_preco = $this->verficarTodosOsProdutosPossuemPrecoWintor($produtos, $listasPrecos);
        if(count($produtos_com_problema_preco) == 0 || true){
            foreach ($produtos as $p){
                if(!in_array($p, $produtos_com_problema_preco)){
                    $listaPrecosProduto = $this->getTodosPrecosProduto($p, $listasPrecos, $clientesPadrao);
                    foreach ($listasPrecos as $estado => $regiao){
                        if(isset($listaPrecosProduto[$regiao])){
                            $valor = round(floatval($listaPrecosProduto[$regiao]['preco']), 2);
                            $st = round(floatval($listaPrecosProduto[$regiao]['st']), 2);
                            $temp = array(
                                'produto' => $p,
                                'estado' => $estado,
                                'preco_venda' => $valor,
                                'preco_listado' => $valor,
                                'st' => $st,
                                'ipi' => 0,
                            );
                            
                            $update = $this->verificarUpdatePrecos($temp);
                            if($update === false){
                                //a entrada já existe e não foi modificada
                                $contadores['iguais'] += 1;
                            }
                            elseif($update === 1){
                                //a entrada foi modificada
                                $contadores['modificados'] += 1;
                                $sql = montaSQL($temp, 'martins_precos', 'UPDATE', "produto = '$p' and estado = '$estado'");
                                query($sql);
                            }
                            elseif($update === 2){
                                //a linha é nova
                                $contadores['novos'] += 1;
                                $temp['sincro'] = '0000-00-00 00:00:00';
                                $temp['erro'] = '0000-00-00 00:00:00';
                                $sql = montaSQL($temp, 'martins_precos');
                                query($sql);
                            }
                        }
                    }
                }
            }
            
            $obj = new integra_martins();
            $obj->migracaoPrecosCompleta();
            
            if($produtos_com_problema_preco){
                $mensagem_portal = "Não foi possível integrar os preços dos seguintes produtos:<br>" . implode(', ', $produtos_com_problema_preco);
                addPortalMensagem($mensagem_portal, 'error');
            }
            
            if($mensagem){
                $mensagem_portal = "Preços recuperados do winthor com sucesso<br>&nbsp;" . $contadores['novos'] . " Preoços Novos<br>&nbsp;" . $contadores['modificados'] . " Preços Modificados<br>&nbsp;" . $contadores['iguais'] . " Preços Iguais";
                addPortalMensagem($mensagem_portal);
                $this->mensagemPortalIntegracao('martins_precos', $data);
                return $this->index();
            }
            else{
                return $contadores;
            }
        }
        else{
            $mensagem_portal = "Não foi possível integrar os preços pois os seguintes protudos não tem preço cadastrado em uma ou mais listas<br>" . implode(', ', $produtos_com_problema_preco);
            addPortalMensagem($mensagem_portal, 'error');
            if($mensagem){
                return $this->index();
            }
            else{
                return $contadores;
            }
        }
    }
    
    private function verificarUpdatePrecos($linha){
        $ret = false;
        $sql = "select * from martins_precos where produto = '" . $linha['produto'] . "' and estado = '" . $linha['estado'] . "'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $row = $rows[0];
            foreach ($linha as $campo => $valor){
                if($row[$campo] != $valor){
                    $ret = 1;
                }
            }
            if(strcmp($row['cadastro'], $row['erro']) < 0){
                $ret = 1;
            }
        }
        else{
            $ret = 2;
        }
        return $ret;
    }
    
    private function montaListaPrecos(){
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
    
    private function getProdutosIntegrados(){
        $ret = array();
        $sql = "select codigo_produto from martins_produtos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['codigo_produto'];
            }
        }
        return $ret;
    }
    
    private function getListasPrecos(){
        $ret = array();
        $sql = "select * from gf_martins_config where parametro in ('RS', 'SC', 'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RO', 'RR', 'SP', 'SE', 'TO')";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if($row["valor"] != ''){
                    $ret[$row['parametro']] = $row['valor'];
                }
            }
        }
        return $ret;
    }
    
    private function getClientesPadrao(){
    	$ret = array();
    	$sql = "select * from gf_martins_config where parametro in ('RS', 'SC', 'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RO', 'RR', 'SP', 'SE', 'TO')";
    	$rows = query($sql);
    	if(is_array($rows) && count($rows) > 0){
    		foreach ($rows as $row){
    			if($row["valor"] != ''){
    				$ret[$row['parametro']] = $row['cliente'];
    			}
    		}
    	}
    	return $ret;
    	
    }
    
    private function getTodosPrecosProduto($produto, $listas, $clientesPadrao){
        $ret = array();
        $sql = "select NUMREGIAO, PTABELA1 from pctabpr where codprod = '$produto'";
        $temp = array();
        $cliPadrao = [];
        foreach ($listas as $uf => $l){
            $temp[] = "'" . $l . "'";
            $cliPadrao[$l] = $clientesPadrao[$uf];
        }
        $sql .= " and NUMREGIAO in (" . implode(', ', $temp) . ')';
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
            	$regiao = $row['NUMREGIAO'];
            	$preco = $row['PTABELA1'];
            	if($cliPadrao[$regiao] > 0){
            		$st = $this->calculaST($cliPadrao[$regiao], $produto, $preco);
            		//$ret[$regiao] = round($preco + $st, 2);
            		$ret[$regiao]['preco'] = round($preco,2) + round($st,2);
            		$ret[$regiao]['st'] = round($st,2);
if($produto == 31809  ){
//	echo "Preco: $preco<br>\n";
//	echo "ST: $st<br>\n";
//	echo "Total: ".$ret[$regiao]['preco']."<br>\n";
}
            		log::gravaLog('martins_preco', "Regiao: $regiao Produto: $produto Preço: $preco ST: $st Cliente: ".$cliPadrao[$regiao]);
            	}else{
            		$ret[$regiao]['preco'] = round($preco,2);
            		$ret[$regiao]['st'] = 0;
            	}
            }
        }
        return $ret;
    }
    
    private function  calculaST($cliente, $prod, $preco){
    	/*/
    	 * SELECT FNC_OBTER_STFONTE_COTACAO('1',
    	 19942, -->> produto
    	 4787, -->> cliente
    	 3.6286) -->> preço
    	 from dual
    	 
    	 
    	 */
    	$st = 0;
    	$sql = "SELECT FNC_OBTER_STFONTE_COTACAO('1', $prod, ".$cliente.",$preco) from dual";
   		$rows = query4($sql);
   		/*
   		if(is_array($rows) && count($rows) > 0){
   		    $valor = $rows[0][0] ?? null;
   		    if(!empty($valor)){
   		        $st = round($valor, 2);
   		    }
   		}
   		*/
   		if(count($rows) > 0){
   		    $st = round($rows[0][0], 2);
   		}
    	return $st;
    }
    
    private function getValorEstoque($estoque){
        $ret = $estoque;
        if($estoque < $this->_estoque_minimo && $this->_estoque_minimo != ''){
            $ret = 0;
        }
        elseif($estoque > $this->_estoque_maximo){
            $ret = $this->_estoque_maximo;
        }
        return $ret;
    }
    
    public function integrarEstoque($mensagem = true){
        $this->gerarLogExecucoes('ESTOQUE');
        if($mensagem){
            $this->popularProdutosIntegrar();
            $this->popularProdutosZerar();
        }
        $data = date('Y-m-d H:i:s');
        
        $produtos = $this->getProdutosIntegrados();
        $contadores = array(
            'iguais' => 0,
            'modificados' => 0,
            'novos' => 0,
        );
        
        foreach($produtos as $p){
            $sql = "SELECT
                    PCEST.CODPROD,
                    PCPRODUT.CODEPTO,
                    (NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0) - NVL(pcest.qtbloqueada, 0)) QTESTDISP
                FROM
                    PCEST,
                    PCPRODUT
                WHERE
                    PCEST.CODFILIAL = 1
                    AND PCEST.CODPROD = PCPRODUT.CODPROD (+)
                    AND PCPRODUT.CODEPTO IN (1,12)
                    AND PCEST.CODPROD = '$p'";
            $rows = query4($sql);
            if(is_array($rows) && count($rows) > 0){
                $row = $rows[0];
                if($this->verificarProdutoDeveSerZerado($p)){
                    $temp = array(
                        'produto' => $p,
                        'centro' => '722_UNICO',
                        'estoque' => 0,
                    );
                }
                else{
                    $temp = array(
                        'produto' => $p,
                        'centro' => '722_UNICO',
                        'estoque' => $this->getValorEstoque($row['QTESTDISP']),
                    );
                }
                
                
                
                
                $update = $this->verificarUpdate($temp, 'martins_estoque', 'produto', 'produto');
                if($update === false){
                    //a entrada já existe e não foi modificada
                    $contadores['iguais'] += 1;
                }
                elseif($update === 1){
                    //a entrada foi modificada
                    $contadores['modificados'] += 1;
                    $temp['sincro'] = '0000-00-00 00:00:00';
                    $sql = montaSQL($temp, 'martins_estoque', 'UPDATE', "produto = '$p'");
                    query($sql);
                }
                elseif($update === 2){
                    //a linha é nova
                    $contadores['novos'] += 1;
                    $temp['sincro'] = '0000-00-00 00:00:00';
                    $temp['erro'] = '0000-00-00 00:00:00';
                    $sql = montaSQL($temp, 'martins_estoque');
                    query($sql);
                }
            }
        }
        
        $obj = new integra_martins();
        $obj->migracaoEstoqueCompleta();
        
        if($mensagem){
            $mensagem_portal = "Estoques recuperados do winthor com sucesso<br>&nbsp;" . $contadores['novos'] . " Estoques Novos<br>&nbsp;" . $contadores['modificados'] . " Estoques Modificados<br>&nbsp;" . $contadores['iguais'] . " Estoques Iguais";
            addPortalMensagem($mensagem_portal);
            $this->mensagemPortalIntegracao('martins_estoque', $data);
            return $this->index();
        }
        else{
            return $contadores;
        }
    }
    
    public function integrarFotos($mensagem = true){
        $this->gerarLogExecucoes('FOTOS');
        if($mensagem){
            $this->popularProdutosIntegrar();
            $this->popularProdutosZerar();
        }
        $data = date('Y-m-d H:i:s');
        
        $produtos = $this->getProdutosIntegrados();
        $contadores = array(
            'iguais' => 0,
            'modificados' => 0,
            'novos' => 0,
        );
        foreach($produtos as $p){
            $sql = "";
            $rows = $this->recuperarFotos($p);
            
            
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $row = $rows[0];
                    $temp = array(
                        'produto' => $p,
                        'codigo' => $row['codigo'],
                        'link' => $row['link'],
                    );
                    
                    
                    $update = $this->verificarUpdateFotos($temp);
                    if($update === false){
                        //a entrada já existe e não foi modificada
                        $contadores['iguais'] += 1;
                    }
                    elseif($update === 1){
                        //a entrada foi modificada
                        $contadores['modificados'] += 1;
                        $sql = montaSQL($temp, 'martins_fotos', 'UPDATE', "produto = '$p' and codigo = '" . $temp['codigo'] . "'");
                        query($sql);
                    }
                    elseif($update === 2){
                        //a linha é nova
                        $contadores['novos'] += 1;
                        $temp['sincro'] = '0000-00-00 00:00:00';
                        $temp['erro'] = '0000-00-00 00:00:00';
                        $sql = montaSQL($temp, 'martins_fotos');
                        query($sql);
                    }
                }
            }
        }
        
        $obj = new integra_martins();
        $obj->migracaoFotosCompleta();
        
        if($mensagem){
            $mensagem_portal = "Fotos recuperadas do winthor com sucesso<br>&nbsp;" . $contadores['novos'] . " Fotos Novas<br>&nbsp;" . $contadores['modificados'] . " Fotos Modificadas<br>&nbsp;" . $contadores['iguais'] . " Fotos Iguais";
            addPortalMensagem($mensagem_portal);
            $this->mensagemPortalIntegracao('martins_fotos', $data);
            return $this->index();
        }
        else{
            return $contadores;
        }
    }
    
    private function verificarUpdateFotos($linha){
        $ret = false;
        $sql = "select * from martins_fotos where produto = '" . $linha['produto'] . "' and codigo = '" . $linha['codigo'] . "'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $row = $rows[0];
            foreach ($linha as $campo => $valor){
                if($row[$campo] != $valor){
                    $ret = 1;
                }
            }
        }
        else{
            $ret = 2;
        }
        return $ret;
    }
    
    private function recuperarFotos($produto){
        $ret = array();
        if($this->checarExistePasta($produto)){
            $ret = $this->getFotosPasta($produto);
        }
        else{
            $ret[] = array(
                'codigo' => '0',
                'link' => 'https://athenas.gauchafarma.com/imagens/imagensProd/' . $produto . '.jpg',
            );
        }
        return $ret;
    }
    
    private function getFotosPasta($produto){
        $ret = array();
        $pasta_base = "/mnt/winthor/IMG/Produtos/$produto/";
        $arquivos = array_diff(scandir($pasta_base), array('..', '.'));
        foreach ($arquivos as $arq){
            $temp = array();
            if(strpos($arq, '_') === false){
                //primeira foto
                $temp = array(
                    'codigo' => 0,
                    'link' => 'https://athenas.gauchafarma.com/imagens/imagensProd/' . $produto . '/' . $arq,
                );
            }
            else{
                //outras fotos
                $id = substr($arq, strpos($arq, '_') + 1);
                $id = substr($id, 0, strpos($id, '.'));
                $id = intval($id) - 1;
                
                $temp = array(
                    'codigo' => $id,
                    'link' => 'https://athenas.gauchafarma.com/imagens/imagensProd/' . $produto . '/' . $arq,
                );
            }
            $ret[] = $temp;
        }
        return $ret;
    }
    
    function checarExistePasta($produto){
        $pasta_base = "/mnt/winthor/IMG/Produtos/$produto/";
        return is_dir($pasta_base);
    }
    
    public function gerarRelatorio(){
        global $config;
        
        $this->integrarTudo();
        
        
        
        /*
        if($this->existeDadosDessincronizados()){
            addPortalMensagem('', 'Existem produtos que não foram integrados, por favor integre eles antes de gerar o relatório', 'erro');
            return $this->index();
        }
        */
        $dados = $this->getDados();
        $relatorio = new relatorio01($this->_programa, array(), 'Integração Winthor');
        $relatorio->setToExcel(true, 'integracao_winthor');
        $arquivo = 'integracao_winthor.xlsx'; 
        $relatorio->addColuna(array('campo' => 'cod'			, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'status'		    , 'etiqueta' => 'Status'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'nome' 		, 'etiqueta' => 'Nome eCommerce' 	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'nomemkp' 	, 'etiqueta' => 'Nome MktPlace' 	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'estoque'		, 'etiqueta' => 'Estoque'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'foto'			, 'etiqueta' => 'Foto'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        
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
        
        $estados = $this->getEstadosRelevantes();
        foreach ($estados as $estado){
            $relatorio->addColuna(array('campo' => $estado . "_P"			, 'etiqueta' => $estado . "_P"			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
            $relatorio->addColuna(array('campo' => $estado . "_ST"			, 'etiqueta' => $estado . "_ST"			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        }
        $relatorio->setDados($dados);
        $relatorio . '';
        addPortalJquery("setLocation('" . $config['tempURL'] . $arquivo . "');");
        //echo $config['tempURL'] . $arquivo;
        return $this->index();
    }
    
    private function getDados(){
        $ret = array();
        $estados = $this->getEstadosRelevantes();
        $estados_campos = array();
        foreach ($estados as $estado){
            $estados_campos[] = $estado . "_P";
            $estados_campos[] = $estado . "_ST";
        }
        //////////////////////////////////////////////////////////////////
        $sql = $this->montaQueryRelatorio($estados, 'complestos');
        //echo $sql . '<br>';
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['cod'] 		= $row['cod'];
                $temp['status'] 	= 'Produto Cadastrado';
                $temp['descricao'] 	= $row['descricao'];
                $temp['nome'] 		= $row['nome'];
                $temp['foto'] 		= $row['foto'];
                $temp['estoque'] 	= $row['estoque'];
                foreach ($estados_campos as $campo){
                    $temp[$campo]  = $row[$campo];
                }
                $ret[] = $temp;
            }
        }
        
        if(getUsuario() == 'thiel' && false){
   	print_r($ret);
   	die('consulta 1');
}
        
        ////////////////////////////////////////////////////////////////
        $sql = $this->montaQueryRelatorio($estados, 'dessincronizados');
        //echo $sql . '<br>';
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['cod'] = $row['cod'];
                $temp['status'] = 'Produto Dessincronizado';
                $temp['descricao'] = $row['descricao'];
                $temp['nome'] 		= $row['nome'];
                $temp['foto'] = $row['foto'];
                $temp['estoque'] = $row['estoque'];
                foreach ($estados_campos as $campo){
                    $temp[$campo]  = $row[$campo];
                }
                $ret[] = $temp;
            }
        }
        
        if(getUsuario() == 'thiel' && false){
   	print_r($ret);
   	die('consulta 2');
}
        
        ////////////////////////////////////////////////////////////////
        $sql = $this->montaQueryRelatorio($estados, 'erro');
        //echo $sql . '<br>';
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['cod'] = $row['cod'];
                $temp['status'] = 'Produto com Erro';
                $temp['descricao'] = $row['descricao'];
                $temp['nome'] 		= $row['nome'];
                $temp['foto'] = $row['foto'];
                $temp['estoque'] = $row['estoque'];
                foreach ($estados_campos as $campo){
                    $temp[$campo]  = $row[$campo];
                }
                $ret[] = $temp;
            }
        }
        ////////////////////////////////////////////////////////////////
        if(getUsuario() == 'thiel' && false){
        	print_r($ret);
        	die('consulta 3');
        }
        
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
					OBS2
			--		(SELECT SUM((NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0))) FROM PCEST WHERE CODPROD = PCPRODUT.CODPROD) AS ESTOQUE
				FROM
					PCPRODUT
				WHERE
					CODEPTO <> 4
					AND CODPROD = ".$r['cod'];
        	
        	$rows = query4($sql);
        	
        	if(isset($rows[0])){
	        	$ret[$i]['nome' 		] = $rows[0]['NOMEECOMMERCE'];
	        	$ret[$i]['nomemkp' 		] = $rows[0]['NOMEECOMMERCE_MARKETPLACE'];
	        	$ret[$i]['detalhe' 		] = !empty($rows[0]['DTEXCLUSAO']) ? $rows[0]['DTEXCLUSAO'] : (!empty($rows[0]['OBS2']) ? $rows[0]['OBS2'] : '');
	        	$ret[$i]['ean' ] 		  = $rows[0]['CODAUXILIAR'];
	        	$ret[$i]['dtcadastro' 	] = $rows[0]['DTCADASTRO'];
	        	$ret[$i]['codfornec' 	] = $rows[0]['CODFORNEC'];
	        	$ret[$i]['fornecedor' 	] = $rows[0]['FORNEC'];
	        	$ret[$i]['depto' 		] = $rows[0]['DEPARTAMENTO'];
	        	$ret[$i]['psico' 		] = $rows[0]['PSICOTROPICO'];
	        	$ret[$i]['retinoico' 	] = $rows[0]['RETINOICO'];
	        	$ret[$i]['desc2' 		] = $rows[0]['DESCRICAO2'];
	        	//	$ret[$i]['estoque' 		] = $rows[0]['ESTOQUE'];
	        	$ret[$i]['marca' 		] = $rows[0]['MARCA'];
        	}
        }
        return $ret;
    }
    
    private function getEstadosRelevantes(){
        $ret = array();
        $sql = "select parametro from gf_martins_config where valor != '' and parametro in ('RS', 'SC', 'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RO', 'RR', 'SP', 'SE', 'TO') and parametro in (select estado from martins_regiao)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['parametro'];
            }
        }
        return $ret;
    }
    
    private function montaQueryRelatorio($estados, $tipo){
        $sql = "select martins_produtos.codigo_produto as cod, martins_produtos.nome_produto as nome, martins_produtos.descricao as descricao, martins_fotos.link as foto, martins_estoque.estoque as estoque";
        foreach ($estados as $estado){
            $tabela = "preco_$estado";
            $temp = ", $tabela.preco_venda as $estado" . "_P, $tabela.st as $estado" . "_ST";
            $sql .= $temp;
        }
        $sql .= " from martins_produtos left join martins_fotos on (martins_produtos.codigo_produto = martins_fotos.produto) left join martins_estoque on (martins_produtos.codigo_produto = martins_estoque.produto) ";
        foreach ($estados as $estado){
            $tabela = "preco_$estado";
            $temp = "left join martins_precos as $tabela on (martins_produtos.codigo_produto = $tabela.produto and $tabela.estado = '$estado') ";
            $sql .= $temp;
        }
        switch ($tipo){
            case 'complestos':
                $sql .= "where martins_fotos.codigo = '0'";
                $sql .= "
 and martins_produtos.sincro >= martins_produtos.cadastro and martins_produtos.sincro > martins_produtos.erro
 and martins_fotos.sincro    >= martins_fotos.cadastro    and martins_fotos.sincro    > martins_fotos.erro
 and martins_estoque.sincro  >= martins_estoque.cadastro  and martins_estoque.sincro  > martins_estoque.erro";
                break;
            case 'dessincronizados':
                //$sql .= " and cadastro > sincro and sincro > erro and cadastro > erro";
                $sql .= "where martins_fotos.codigo = '0'";
                $sql .= " and (
 (martins_produtos.cadastro > martins_produtos.sincro and martins_produtos.cadastro > martins_produtos.erro)
 or (martins_fotos.cadastro    > martins_fotos.sincro    and martins_fotos.cadastro    > martins_fotos.erro)
 or (martins_estoque.cadastro  > martins_estoque.sincro  and martins_estoque.cadastro  > martins_estoque.erro)
)";
                break;
            case 'erro':
                //$sql .= " and erro >= cadastro and erro > sincro";
                $sql .= "where (martins_fotos.codigo = '0' or martins_fotos.codigo is null)";
                $sql .= " and (
 (martins_produtos.erro >= martins_produtos.cadastro and martins_produtos.erro > martins_produtos.sincro)
 or (martins_fotos.erro    >= martins_fotos.cadastro    and martins_fotos.erro    > martins_fotos.sincro)
 or (martins_estoque.erro  >= martins_estoque.cadastro  and martins_estoque.erro  > martins_estoque.sincro)
)";
                break;
            default:
                break;
        }
        $sql .= ' order by martins_produtos.codigo_produto';
        return $sql;
    }
    
    private function integrarTudo(){
        $ret = array();
        
        $contador_produtos = $this->integrarProdutos(false);
        $contador_estoque = $this->integrarEstoque(false);
        $contador_precos = $this->integrarPrecos(false);
        $contador_fotos = $this->integrarFotos(false);
        
        $ret = array(
            'iguais' => $contador_produtos['iguais'] + $contador_estoque['iguais'] + $contador_precos['iguais'] + $contador_fotos['iguais'],
            'modificados' => $contador_produtos['modificados'] + $contador_estoque['modificados'] + $contador_precos['modificados'] + $contador_fotos['modificados'],
            'novos' => $contador_produtos['novos'] + $contador_estoque['novos'] + $contador_precos['novos'] + $contador_fotos['novos'],
        );
        
        return $ret;
    }
    
    private function existeDadosDessincronizados(){
        $ret = true;
        $tabelas = array('martins_estoque', 'martins_fotos', 'martins_precos', 'martins_produtos');
        $sql_raw = array();
        foreach ($tabelas as $tabela){
            $sql_raw[] = "select id from $tabela where cadastro > sincro or erro > sincro";
        }
        $sql = implode(' union ', $sql_raw);
        $rows = query($sql);
        if(is_array($rows) && count($rows) == 0){
            $ret = false;
        }
        return $ret;
    }
    
    private function getCateogriaProdutoMartins($produto){
        $ret = '';
        $sql = "select * from gf_martins_categorias where produto = '$produto'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['categoria'];
            $data = date('Y-m-d H:i:s');
            $sql = "update gf_martins_categorias set sincro = '$data' where id = '" . $rows[0]['id'] . "'";
            query($sql);
        }
        return $ret;
    }
    
    private function integrarCategoriasMartins(){
        $ret = array();
        $sql = 'select gf_martins_categorias.categoria as categoria, gf_martins_categorias.produto as produto from gf_martins_categorias join martins_produtos on (gf_martins_categorias.produto = martins_produtos.codigo_produto) where  gf_martins_categorias.cadastro > gf_martins_categorias.sincro"';
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $categoria = $row['categoria'];
                $produto = $row['produto'];
                $sql = "update martins_produtos set categoria = '$categoria' where codigo_produto = '$produto'";
                query($sql);
                $data = date('Y-m-d H:i:s');
                $sql = "update gf_martins_categorias set sincro = '$data' where categoria = '$categoria' and produto = '$produto'";
                query($sql);
            }
        }
        return $ret;   
    }
    
    private function getNumEntradasIntegradas($tabela, $data){
        $ret = array();
        
        $sql = "select * from $tabela where cadastro >= '$data' and erro >= cadastro";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret['erros'] = count($rows);
        }
        else{
            $ret['erros'] = 0;
        }
        
        $sql = "select * from $tabela where cadastro >= '$data' and sincro >= cadastro";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret['acertos'] = count($rows);
        }
        else{
            $ret['acertos'] = 0;
        }
        return $ret;
    }
    
    private function mensagemPortalIntegracao($tabela, $data){
        $variaveis = array(
            'martins_produtos' => array('objetos' => 'produtos', 'genero' => 'os'),
            'martins_precos'   => array('objetos' => 'preços'  , 'genero' => 'os'),
            'martins_fotos'    => array('objetos' => 'fotos'   , 'genero' => 'as'),
            'martins_estoque'  => array('objetos' => 'estoques' , 'genero' => 'os'),
        );
        
        $numeros_integracao = $this->getNumEntradasIntegradas($tabela, $data);
        
        $tipo_mensagem = $numeros_integracao['erros'] == 0 ? '' : 'error';
        $mensagem_portal = $numeros_integracao['acertos'] . " " . $variaveis[$tabela]['objetos'] . " integrad" . $variaveis[$tabela]['genero'] . " ao Martins corretamente<br>&nbsp;" . $numeros_integracao['erros'] . " " . $variaveis[$tabela]['objetos'] . " não foram integrad" . $variaveis[$tabela]['genero'] . "  corretamente";
        addPortalMensagem($mensagem_portal, $tipo_mensagem);
    }
    /*
    private function getTodasCategoriasMartins(){
        echo '';
        $ret = array();
        $obj = new integra_martins();
        $lista_raw = $obj->getCategorias();
        foreach ($lista_raw as $primeiro_nivel){
            $sufixo = '';
            if(is_array($primeiro_nivel['categories']) && count($primeiro_nivel['categories']) > 0){
                //se o primeiro nivel não é o ultimo
                foreach ($primeiro_nivel['categories'] as $segundo_nivel){
                    if(is_array($segundo_nivel['categories']) && count($segundo_nivel['categories']) > 0){
                        //se o segundo nivel não é o ultimo
                        $sufixo = $primeiro_nivel['description'] . "#" . $segundo_nivel['description'] . '#';
                        foreach ($segundo_nivel['categories'] as $terceiro_nivel){
                            //$ret[] = array($terceiro_nivel['category_id'], $sufixo . $terceiro_nivel['description']);
                            $ret[$terceiro_nivel['category_id']] = $sufixo . $terceiro_nivel['description'];
                        }
                    }
                    else{
                        $sufixo = $primeiro_nivel['description'] . "#";
                        //$ret[] = array($segundo_nivel['category_id'], $sufixo . $segundo_nivel['description']);
                        $ret[$segundo_nivel['category_id']] = $sufixo . $segundo_nivel['description'];
                    }
                }
            }
            else{
                //se o primeiro nível é o ultimo
                //$ret[] = array($primeiro_nivel['category_id'], $primeiro_nivel['description']);
                $ret[$primeiro_nivel['category_id']] = $primeiro_nivel['description'];
            }
        }
        return $ret;
    }
    
    private function getTodasCategoriasGF(){
        $ret = array();
        $sql = "SELECT
				    PCPRODUT.CODSEC,
				    PCSECAO.DESCRICAO SECAO,
				    PCPRODUT.CODCATEGORIA,
				    PCCATEGORIA.CATEGORIA CATEGORIA,
				    PCPRODUT.CODSUBCATEGORIA,
				    PCSUBCATEGORIA.SUBCATEGORIA SUB
				FROM
				    PCPRODUT,
				    PCSECAO,
				    PCCATEGORIA,
				    PCSUBCATEGORIA
				WHERE
				    PCPRODUT.CODSEC = PCSECAO.CODSEC
				    AND PCPRODUT.CODCATEGORIA = PCCATEGORIA.CODCATEGORIA AND PCSECAO.CODSEC = PCCATEGORIA.CODSEC
				    AND PCPRODUT.CODSUBCATEGORIA = PCSUBCATEGORIA.CODSUBCATEGORIA AND PCSUBCATEGORIA.CODCATEGORIA = PCCATEGORIA.CODCATEGORIA
				        AND PCSUBCATEGORIA.CODSEC = PCCATEGORIA.CODSEC
            
				GROUP BY
				    PCPRODUT.CODSEC,
				    PCSECAO.DESCRICAO ,
				    PCPRODUT.CODCATEGORIA,
				    PCCATEGORIA.CATEGORIA ,
				    PCPRODUT.CODSUBCATEGORIA,
				    PCSUBCATEGORIA.SUBCATEGORIA
				ORDER BY
				    PCSUBCATEGORIA.SUBCATEGORIA";
        $rows = query4($sql);
        $temp = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['CODSEC'] . '-' .  $row['CODCATEGORIA'] . '-' .  $row['CODSUBCATEGORIA']] = $row['SECAO'] . '#' .  $row['CATEGORIA'] . '#' .  $row['SUB'];
            }
        }
        return $ret;
    }
    
    private function montaListaCateogriasGF(){
        $ret = array();
        foreach ($this->_categorias_gf as $codigo => $nome){
            if($this->getConfig($codigo) == ''){
                //só entra na lista se não estiver no config
                $ret[] = array(
                    0 => $codigo,
                    1 => $nome,
                );
            }
        }
        return $ret;
    }
    
    private function montaListaCateogriasMartins(){
        $ret = array();
        foreach ($this->_categorias_martins as $codigo => $nome){
            $ret[] = array(
                0 => $codigo,
                1 => $nome,
            );
        }
        return $ret;
    }
    
    private function criarFormCategorias(){
        $ret = '';
        $this->carregarCategorias();
        formbase01::setLayout('basico');
        $campo = array(
            'id' => 'selectCategoriaGF',
            'campo' => 'formconfig[selectCategoriaGF]',
            'etiqueta' => 'Categoria GF',
            'tipo' => 'A',
            'tamanho' => '100',
            'width' => true,
            'lista' => $this->montaListaCateogriasGF(),
            'largura' => 5,
            'obrigatorio' => false
        ); 
        $select_gf = formbase01::formSelect($campo);
        
        $campo = array(
            'id' => 'selectCategoriaMA',
            'campo' => 'formconfig[selectCategoriaMA]',
            'etiqueta' => 'Categoria Martins',
            'tipo' => 'A',
            'tamanho' => '100',
            'width' => true,
            'lista' => $this->montaListaCateogriasMartins(),
            'largura' => 5,
            'obrigatorio' => false
        ); 
        $select_martins = formbase01::formSelect($campo);
        
        $param = [];
        $param['texto'] = 'Incluir Relação';
        $param['onclick'] = "incluiRat();";
        $param['id'] = 'myInput';
        $param['tamanho'] = 'grande';
        $botao = formbase01::formBotao($param);
        
        $ret .= '<div class="row">';
        $ret .=  '	<div  class="col-md-5">'. $select_gf .'</div>';
        $ret .= '	<div  class="col-md-5">'.$select_martins.'</div>';
        $ret .= '	<div  class="col-md-2">'.$botao.'</div>';
        $ret .= '</div>';
        $ret .= $this->criarTabelaCategorias();
        return $ret;
        //<input type="text" id="country" name="country" value="Norway" readonly>
        //$ret =  '<input type="hidden" name="'.$nome.'" value="'.$valor.'" id="'.$id.'">'.$nl;
    }
    
    private function carregarCategorias(){
        if(count($this->_categorias_gf) == 0){
            $this->_categorias_gf = $this->getTodasCategoriasGF();
        }
        if(count($this->_categorias_martins) == 0){
            $this->_categorias_martins = $this->getTodasCategoriasMartins();
        }
    }
    
    private function criarTabelaCategorias(){
        $ret = '';
        $sql = "select * from gf_martins_config where parametro like '%-%'";
        $rows = query($sql);
        $dados = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if(substr_count($row['parametro'], '-') >= 2){
                    $temp = array();
                    $temp['gf'] = '<input  type="text" name="formconfig[tabelacategorias][' . count($dados) . '][gf]" value="' . $this->_categorias_gf[$row['parametro']] .  '" id="categoriaGF' . count($dados) . '" class="form-control  form-control-sm" readonly >';
                    //$temp['ma'] = $this->_categorias_martins[$row['valor']];
                    $temp['ma'] = '<input  type="text" name="formconfig[tabelacategorias][' . count($dados) . '][ma]" value="' . $this->_categorias_martins[$row['valor']] .  '" id="categoriaMA' . count($dados) . '" class="form-control  form-control-sm" readonly >';
                    $temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRelacao(this);'>Excluir</button>";
                    $dados[] = $temp;
                }
            }
        }
        
        $param = [];
        $param['paginacao'] = false;
        $param['scroll'] 	= false;
        $param['scrollX'] 	= false;
        $param['scrollY'] 	= false;
        $param['ordenacao'] = false;
        $param['filtro']	= false;
        $param['info']		= false;
        $param['id']		= 'tabRelacaoID';
        $tab = new tabela03($param);
        
        $tab->addColuna(array('campo' => 'gf'	, 'etiqueta' => 'Categoria GF'		 , 'tipo' => 'T', 'width' => '150'  , 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'ma'	, 'etiqueta' => 'Categoria Martins'	 , 'tipo' => 'T', 'width' => '150', 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'bt'	, 'etiqueta' => ''				     , 'tipo' => 'T', 'width' => ' 50', 'posicao' => 'C'));
        
        $tab->setDados($dados);
        
        $ret .= $tab;
        
        return $ret;
    }
    
    private function addJsCategorias(){
        $js = "
function incluiRat(valor){
    var t = $('#tabRatID').DataTable();
            
    var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";
            
    var hora = \"<input  type='text' name='formOS[tarefas][horas][]' value='' style='width:100%;text-align: right;' id='\"+valor+\"tabelacampohora' class='form-control  form-control-sm'          >\";
    var texto = \"<input  type='text' name='formOS[tarefas][descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
            
	t.row.add( [hora, texto, bt] ).draw( false );
    $('#'+valor+'tabelacampohora').mask('99:99',{reverse: true});
            
    valor = valor + 1;
    $('#myInput').attr('onclick', 'incluiRat('+valor+');' );
}";
        //addPortaljavaScript($js);
        $js = "
function excluirRelacao(e){
    var t = $('#tabRelacaoID').DataTable();
	t.row( $(e).parents('tr') ).remove().draw();
}";
        addPortaljavaScript($js);
    }
    */
    
    private function getTodasCategoriasMartins(){
        $ret = array();
        $obj = new integra_martins();
        $lista_raw = $obj->getCategorias();
        foreach ($lista_raw as $primeiro_nivel){
            $sufixo = '';
            if(is_array($primeiro_nivel['categories']) && count($primeiro_nivel['categories']) > 0){
                //se o primeiro nivel não é o ultimo
                foreach ($primeiro_nivel['categories'] as $segundo_nivel){
                    if(is_array($segundo_nivel['categories']) && count($segundo_nivel['categories']) > 0){
                        //se o segundo nivel não é o ultimo
                        $sufixo = $primeiro_nivel['description'] . " # " . $segundo_nivel['description'] . ' # ';
                        foreach ($segundo_nivel['categories'] as $terceiro_nivel){
                            //$ret[] = array($terceiro_nivel['category_id'], $sufixo . $terceiro_nivel['description']);
                            $ret[$terceiro_nivel['category_id']] = $sufixo . $terceiro_nivel['description'];
                        }
                    }
                    else{
                        $sufixo = $primeiro_nivel['description'] . " # ";
                        //$ret[] = array($segundo_nivel['category_id'], $sufixo . $segundo_nivel['description']);
                        $ret[$segundo_nivel['category_id']] = $sufixo . $segundo_nivel['description'];
                    }
                }
            }
            else{
                //se o primeiro nível é o ultimo
                //$ret[] = array($primeiro_nivel['category_id'], $primeiro_nivel['description']);
                $ret[$primeiro_nivel['category_id']] = $primeiro_nivel['description'];
            }
        }
        $this->_categorias_martins = $ret;
    }
    
    private function getTodasCategoriasGF(){
        $ret = array();
        $sql = "SELECT
				    PCPRODUT.CODSEC,
				    PCSECAO.DESCRICAO SECAO,
				    PCPRODUT.CODCATEGORIA,
				    PCCATEGORIA.CATEGORIA CATEGORIA,
				    PCPRODUT.CODSUBCATEGORIA,
				    PCSUBCATEGORIA.SUBCATEGORIA SUB
				FROM
				    PCPRODUT,
				    PCSECAO,
				    PCCATEGORIA,
				    PCSUBCATEGORIA
				WHERE
				    PCPRODUT.CODSEC = PCSECAO.CODSEC
				    AND PCPRODUT.CODCATEGORIA = PCCATEGORIA.CODCATEGORIA AND PCSECAO.CODSEC = PCCATEGORIA.CODSEC
				    AND PCPRODUT.CODSUBCATEGORIA = PCSUBCATEGORIA.CODSUBCATEGORIA AND PCSUBCATEGORIA.CODCATEGORIA = PCCATEGORIA.CODCATEGORIA
				        AND PCSUBCATEGORIA.CODSEC = PCCATEGORIA.CODSEC
            
				GROUP BY
				    PCPRODUT.CODSEC,
				    PCSECAO.DESCRICAO ,
				    PCPRODUT.CODCATEGORIA,
				    PCCATEGORIA.CATEGORIA ,
				    PCPRODUT.CODSUBCATEGORIA,
				    PCSUBCATEGORIA.SUBCATEGORIA
				ORDER BY
				    PCPRODUT.CODSEC,
                    PCPRODUT.CODCATEGORIA,
                    PCPRODUT.CODSUBCATEGORIA";
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['CODSEC'] . '-' .  $row['CODCATEGORIA'] . '-' .  $row['CODSUBCATEGORIA']] = $row['SECAO'] . ' # ' .  $row['CATEGORIA'] . ' # ' .  $row['SUB'];
            }
        }
        $this->_categorias_gf = $ret;
    }
    
    private function montaListaCateogriasGF(){
        $ret = array();
        foreach ($this->_categorias_gf as $codigo => $nome){
            if($this->getConfig($codigo) == ''){
                //só entra na lista se não estiver no config
                $ret[] = array(
                    0 => $codigo,
                    1 => $nome,
                );
            }
        }
        return $ret;
    }
    
    private function montaListaCateogriasMartins(){
        $ret = array();
        foreach ($this->_categorias_martins as $codigo => $nome){
            $ret[] = array(
                0 => $codigo,
                1 => $nome,
            );
        }
        return $ret;
    }
    
    public function editarCategorias(){
        $this->getTodasCategoriasGF();
        $this->getTodasCategoriasMartins();
        
        $param = array();
        $form = new form01($param);
        
        $form->addCampo(array('id' => '', 'campo' => 'formcategoria[gf]'   , 'etiqueta' => 'Categoria GF', 'tipo' => 'A' , 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0 , 'lista' => $this->montaListaCateogriasGF(), 'validacao' => '', 'largura' => 6, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formcategoria[ma]'   , 'etiqueta' => 'Categoria Martins', 'tipo' => 'A' , 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0 , 'lista' => $this->montaListaCateogriasMartins(), 'validacao' => '', 'largura' => 6, 'obrigatorio' => true));
        
        $form->setEnvio(getLink() . 'salvarCategorias', 'formcategoria', 'formcategoria');
        
        $param = [];
        $param['paginacao'] = false;
        $param['scroll'] 	= false;
        $param['scrollX'] 	= false;
        $param['scrollY'] 	= false;
        $param['ordenacao'] = false;
        $param['filtro']	= false;
        $param['info']		= false;
        $tab = new tabela01($param);
        
        $tab->addColuna(array('campo' => 'gf'	    , 'etiqueta' => 'Categoria GF'		, 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'ma'	    , 'etiqueta' => 'Categoria Martins'	, 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'bt'	    , 'etiqueta' => ''		            , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        
        $dados = $this->getDadosCategorias();
        $tab->setDados($dados);
        
        
        $param = array();
        $bt = array();
        $bt['onclick'] = "setLocation('".getLink()."index')";
        $bt['tamanho'] = 'pequeno';
        $bt['cor'] = 'danger';
        $bt['texto'] = 'Voltar';
        $param['botoesTitulo'][] = $bt;
        $param['titulo'] = 'Configurar Parâmetros da Integração';
        $param['conteudo'] = $form . $tab;
        
        return addCard($param);
    }
    
    private function getDadosCategorias(){
        $ret = array();
        $sql = "select * from gf_martins_config where parametro like '%-%'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['gf'] = $this->_categorias_gf[$row['parametro']];
                $temp['ma'] = $this->_categorias_martins[$row['valor']];
                $temp['bt'] = '<button type="button" class="btn btn-xs btn-danger"  onclick="setLocation(' . "'". getLink() . "excluirCategorias&parametro=" . $row['parametro'] . "')" . '">Excluir</button>';
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function salvarCategorias(){
        $dados = getParam($_POST, 'formcategoria');
        $categoria_gf = $dados['gf'];
        $categoria_ma = $dados['ma'];
        $sql = "insert into gf_martins_config  values (null, '$categoria_gf', '$categoria_ma', NULL)";
        log::gravaLog('martins_categorias', $sql);
        query($sql);
        return $this->editarCategorias();
    }
    
    public function excluirCategorias(){
        $parametro = getParam($_GET, 'parametro');
        $sql = "DELETE FROM gf_martins_config WHERE parametro = '$parametro'";
        query($sql);
        return $this->editarCategorias();
    }
    
    private function dessincronizar($produto){
        $sql = "update martins_estoque set sincro = '0000-00-00 00:00:00' where produto = '$produto'";
        query($sql);
        $sql = "update martins_fotos   set sincro = '0000-00-00 00:00:00' where produto = '$produto'";
        query($sql);
        $sql = "update martins_precos  set sincro = '0000-00-00 00:00:00' where produto = '$produto'";
        query($sql);
    }
    
    private function montarNomeProduto($dados, $categoria, $modelo){
        $ret = array();
        $marca = ucwords(strtolower($this->_marcas[$dados['CODMARCA']]),"-/");
        //parte para colocar letra maiuscula no inicio de cada palavra
        $nome = strtolower(!empty($dados['NOMEECOMMERCE']) ? str_replace("'", '´', $dados['NOMEECOMMERCE']) : str_replace("'", '´', $dados['DESCRICAO']));
        $nome = $this->tirar_duplicatas($nome, $marca); //ET 25/02/22 para tirar a marca
        $temp = explode(' ', $nome);
        //ET Neto reclamou que parte do nome de alguns produtos estava sem letra maiuscula no começo, suspeitamos que o problema fosse com o modelo entre ()
        $temp = str_replace('(', '', $temp);
        $temp = str_replace(')', '', $temp);
        
        /////////////////////////////////////////
        $palavras_sem_maiuscula  = array('do', 'de', 'da', 'o', 'a', 'e', 'no', 'na');
        foreach ($temp as $palavra){
            if(!in_array($palavra, $palavras_sem_maiuscula)){
                $ret[] = ucfirst($palavra);
            }
            else{
                $ret[] = $palavra;
            }
        }
        $ret = implode(' ', $ret);
        ////////////////////////////////////////////////////////////////
        //parte para colocar a marca no final do produto
        
        
        //AT - Necessário pois tem produtos que já tem a marca na descrição
        if(strpos($ret, $marca) === false){
        	$ret .= ', ' . $marca;
        }
        /////////////////////////////////////////////////////////////////
        //parte para colocar o modelo no final do produto
        $modelo = ucfirst($this->extrairModeloProduto($dados['NOMEECOMMERCE'], $modelo));
        //$ret = substr($ret, 0, strpos($ret, '(')) . substr($ret, strpos($ret, ')') + 1); //arranca o modelo

        
        //AT
        //$ret = str_replace('(', '', $ret);
        //$ret = str_replace(')', '', $ret);      
        //ET
        $categorias_com_modelo = $this->montaListaCategoriasComModelo(); //carrega a lista de categorias que dem ter o modelo
        if(in_array(strval($categoria), $categorias_com_modelo)){
            $ret .= !empty($modelo) ? ", $modelo" : '';
        }
        
        
        
        /////////////////////////////////////////////////////////////////
        //Não deveser enviado virgula
        $ret = str_replace(',', '', $ret);
        //Tira espaço duplo
        $ret = str_replace('  ', ' ', $ret);
        //$ret = str_replace('/', ' ', $ret);
        return $ret;
    }
    
    private function corrigirNomeEcommerceNovo($nome){
        $nome = strtolower($nome);
        $temp = explode(' ', $nome);
        $palavras_sem_maiuscula  = array('do', 'de', 'da', 'o', 'a', 'e', 'no', 'na');
        foreach ($temp as $palavra){
            if(!in_array($palavra, $palavras_sem_maiuscula)){
                $ret[] = ucfirst(strtolower($palavra));
            }
            else{
                $ret[] = $palavra;
            }
        }
        $ret = implode(' ', $ret);
        return $ret;
    }
    
    private function extrairModeloProduto($descricao, $modelo){
        $ret = '';
       // if(strpos($produto, '(') !== false){
       //     $ret = substr($produto, strpos($produto, '(') + 1);
       //     $ret = substr($ret,  0, strpos($ret, ')'));
       // }

		//Pode acontecer que o modelo já esteja na descrição do produto, neste caso ignora
        if($modelo != '0' && strpos($descricao, $modelo) === false){
        	//Pega somente os numeros
        	$ret = trim($modelo);
        	$temp = filter_var($ret, FILTER_SANITIZE_NUMBER_INT);
        	if($temp == $ret){
        		$ret = ''; // Não deve ser enviados números
        	}
        	
        }
        
        return $ret;
    }
    
    private function montaListaCategoriasComModelo(){
        $ret = array();
        $ret[] = '2000';
        $ret[] = '2001';
        $ret[] = '2002';
        $ret[] = '2003';
        $ret[] = '2004';
        $ret[] = '2005';
        $ret[] = '2006';
        $ret[] = '2007';
        $ret[] = '2008';
        $ret[] = '2009';
        $ret[] = '2010';
        $ret[] = '2011';
        $ret[] = '2012';
        $ret[] = '2013';
        $ret[] = '2014';
        $ret[] = '2015';
        $ret[] = '2016';
        $ret[] = '2017';
        $ret[] = '2018';
        $ret[] = '2019';
        $ret[] = '2020';
        $ret[] = '2021';
        $ret[] = '2022';
        $ret[] = '2023';
        $ret[] = '2024';
        $ret[] = '2025';
        $ret[] = '2026';
        $ret[] = '2027';
        $ret[] = '2028';
        $ret[] = '2029';
        $ret[] = '2030';
        $ret[] = '2031';
        $ret[] = '2032';
        $ret[] = '2033';
        $ret[] = '2034';
        $ret[] = '2035';
        $ret[] = '2036';
        $ret[] = '2037';
        $ret[] = '2038';
        $ret[] = '2039';
        $ret[] = '2040';
        $ret[] = '2041';
        $ret[] = '2042';
        $ret[] = '2043';
        $ret[] = '2044';
        $ret[] = '2045';
        $ret[] = '2046';
        $ret[] = '2047';
        $ret[] = '2048';
        $ret[] = '2049';
        $ret[] = '2050';
        $ret[] = '2051';
        $ret[] = '2052';
        $ret[] = '2053';
        $ret[] = '2054';
        $ret[] = '2055';
        $ret[] = '2056';
        $ret[] = '2057';
        $ret[] = '2058';
        $ret[] = '2059';
        $ret[] = '2060';
        $ret[] = '2061';
        $ret[] = '2062';
        $ret[] = '2063';
        $ret[] = '2064';
        $ret[] = '2065';
        $ret[] = '2096';
        $ret[] = '2100';
        $ret[] = '2110';
        $ret[] = '2118';
        $ret[] = '2146';
        $ret[] = '2148';
        $ret[] = '1633';
        $ret[] = '1997';
        $ret[] = '1775';
        $ret[] = '1776';
        $ret[] = '1777';
        $ret[] = '1955';
        $ret[] = '1956';
        $ret[] = '1957';
        $ret[] = '1958';
        $ret[] = '1959';
        $ret[] = '2092';
        $ret[] = '2123';
        $ret[] = '1481';
        $ret[] = '1482';
        $ret[] = '2119';
        $ret[] = '2140';
        $ret[] = '2164';
        $ret[] = '2165';
        $ret[] = '231';
        $ret[] = '1998';
        $ret[] = '1999';
        $ret[] = '2078';
        $ret[] = '443';
        $ret[] = '1489';
        $ret[] = '1490';
        $ret[] = '1492';
        $ret[] = '1960';
        $ret[] = '1961';
        $ret[] = '1962';
        $ret[] = '1963';
        $ret[] = '1964';
        $ret[] = '1965';
        $ret[] = '2138';
        $ret[] = '1483';
        $ret[] = '1485';
        $ret[] = '1486';
        $ret[] = '1487';
        $ret[] = '1751';
        $ret[] = '2172';
        $ret[] = '2173';
        $ret[] = '2248';
        $ret[] = '2249';
        $ret[] = '2250';
        $ret[] = '2251';
        $ret[] = '224';
        $ret[] = '245';
        $ret[] = '304';
        $ret[] = '1474';
        $ret[] = '1475';
        $ret[] = '1476';
        $ret[] = '1477';
        $ret[] = '1478';
        $ret[] = '1780';
        $ret[] = '1781';
        $ret[] = '1782';
        $ret[] = '1783';
        $ret[] = '1784';
        $ret[] = '1785';
        $ret[] = '2175';
        $ret[] = '2260';
        $ret[] = '2289';
        $ret[] = '306';
        $ret[] = '1772';
        $ret[] = '1773';
        $ret[] = '2134';
        $ret[] = '1472';
        $ret[] = '2136';
        $ret[] = '1479';
        $ret[] = '1792';
        $ret[] = '2259';
        $ret[] = '1681';
        $ret[] = '2262';
        $ret[] = '672';
        $ret[] = '1593';
        $ret[] = '1658';
        $ret[] = '1669';
        $ret[] = '1991';
        $ret[] = '1992';
        $ret[] = '1503';
        $ret[] = '1504';
        $ret[] = '1505';
        $ret[] = '1506';
        $ret[] = '1507';
        $ret[] = '1763';
        $ret[] = '1764';
        $ret[] = '1765';
        $ret[] = '1766';
        $ret[] = '2124';
        $ret[] = '2125';
        $ret[] = '2211';
        $ret[] = '2212';
        $ret[] = '2213';
        $ret[] = '2214';
        $ret[] = '2217';
        $ret[] = '2224';
        $ret[] = '2274';
        $ret[] = '2275';
        $ret[] = '1436';
        $ret[] = '1495';
        $ret[] = '1497';
        $ret[] = '1498';
        $ret[] = '1499';
        $ret[] = '1500';
        $ret[] = '1501';
        $ret[] = '1502';
        $ret[] = '1758';
        $ret[] = '2122';
        $ret[] = '2150';
        $ret[] = '2155';
        $ret[] = '2158';
        $ret[] = '2209';
        $ret[] = '2263';
        $ret[] = '2272';
        $ret[] = '1494';
        $ret[] = '2218';
        $ret[] = '2219';
        $ret[] = '1754';
        $ret[] = '1755';
        $ret[] = '1756';
        $ret[] = '1757';
        $ret[] = '1767';
        $ret[] = '1790';
        $ret[] = '2130';
        $ret[] = '2143';
        $ret[] = '2176';
        $ret[] = '2178';
        $ret[] = '2179';
        $ret[] = '2180';
        $ret[] = '2181';
        $ret[] = '2182';
        $ret[] = '2183';
        $ret[] = '2184';
        $ret[] = '2185';
        $ret[] = '2186';
        $ret[] = '2187';
        $ret[] = '2194';
        $ret[] = '2210';
        $ret[] = '2243';
        $ret[] = '2244';
        $ret[] = '2201';
        $ret[] = '2221';
        $ret[] = '2222';
        $ret[] = '2223';
        $ret[] = '1444';
        $ret[] = '1730';
        $ret[] = '1966';
        $ret[] = '2145';
        $ret[] = '2151';
        $ret[] = '1741';
        $ret[] = '2203';
        $ret[] = '217';
        $ret[] = '1059';
        $ret[] = '1061';
        $ret[] = '1453';
        $ret[] = '1454';
        $ret[] = '1728';
        $ret[] = '1460';
        $ret[] = '1461';
        $ret[] = '1462';
        $ret[] = '1463';
        $ret[] = '1464';
        $ret[] = '1465';
        $ret[] = '1466';
        $ret[] = '1468';
        $ret[] = '1469';
        $ret[] = '2114';
        $ret[] = '1509';
        $ret[] = '1510';
        $ret[] = '1511';
        $ret[] = '1513';
        $ret[] = '1514';
        $ret[] = '1515';
        $ret[] = '1517';
        $ret[] = '1516';
        $ret[] = '1893';
        $ret[] = '2174';
        $ret[] = '2282';
        return $ret;
    }
    
    private function tirarZerosFinal($valor){
        $ret = strval($valor);
        if(strpos($ret, '.') !== false){
            while(strlen(substr($ret, strpos($ret, '.') + 1)) < 4){
                $ret .= '0';
            }
        }
        return $ret;
    }
    
    private function tirar_duplicatas($nome, $marca){
        $ret = '';
        $nome = strtolower($nome);
        $marca = strtolower($marca);
        $caracteres_divisores = array('/', '\\', ";", ':', '_', '#');
        $caracteres_divisores_presentes = array();
        foreach ($caracteres_divisores as $c){
            if(strpos($marca, $c)){
                $caracteres_divisores_presentes[] = $c;
            }
        }
        foreach ($caracteres_divisores_presentes as $c){
            $marca = str_replace($c, '++', $marca);
        }
        $marca = explode('++', $marca);
        $palavras_ignoradas = array('do', 'de', 'da', 'o', 'a', 'e', 'no', 'na');
        foreach ($marca as $m){
            if(!in_array($m, $palavras_ignoradas)){
                if(strpos($nome, $m) !== false){
                    $nome = str_replace($m, '', $nome);
                }
            }
        }
        $ret = $nome;
        return $ret;
        /*
        $nome = ' ' . strtolower($nome);
        $i = 0;
        while ($i < strlen($nome)){
            while($nome[$i] == ' '){
                $i++;
            }
            $primeira_letra = $i;
            while($i != ' '){
                $i++;
            }
            $ultima_letra = $i - 1;
            while(strpos($nome, substr($nome, $primeira_letra, $ultima_letra - $primeira_letra), $ultima_letra) !== false){
                
            }
            $i++;
        }
        */
    }
    
    private function verificarProdutoDeveSerIntegrado($cod_prod){
        return isset($this->_produtos_integrar[trim(strval($cod_prod))]);
    }
    
    private function popularProdutosIntegrar(){
        if($this->_flag_produtos_integrar === false){
            $this->_flag_produtos_integrar = true;
            $sql = "select cod_prod from gf_produtos_marketplace where integracao = 'MA'";
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
            $produtos = $this->getProdutosIntegrados();
            foreach ($produtos as $p){
                $codigo = trim(strval($p));
                if(!$this->verificarProdutoDeveSerIntegrado($codigo)){
                    $this->_produtos_zerar[$codigo] = $codigo;
                }
            }
        }
    }
    
    private function gerarLogExecucoes($integracao){
        $texto = 'o usuário ' . getUsuario() . " executou a integracao $integracao";
        log::gravaLog('martins_log_execucao_' . $integracao, $texto);
    }
}