<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


class sincronizar_dados{
    var $funcoes_publicas = array(
        'index'             => true,
    );
    
    private $_rest;
    private $_negocios_velhos = [];
    private $_codigosVendedoresProtheus = [];
    private $_emailVendedores = [];
    
    public function __construct(){
        $this->_rest = criarRestAgendor();
        $this->montarListaNegociosVelhos();
        $this->montarListaCodigosVendedorProtheus();
    }
    
    public function index(){
        //$this->criarOrcamentos();
    }
    
    public function schedule($param){
        if($param === 'produtos'){
            $this->renovarProdutosProtheus();
            $this->atualizarProdutosAgendor();
        }
        elseif($param === 'orcamentos'){
            $this->criarOrcamentos();
            //$this->getPropostasProtheus();
            //$this->moverNegociosAgendorOrcamento();
        }
        elseif($param === 'aprovados'){
            $this->gravarNegociosAprovadosCliente();
        }
        /*
         elseif($param === 'aprovados'){
         $this->moverNegociosAprovadosAgendor();
         }
         */
    }
    
    private function recuperarNegociosAprovados(){
        $ret = [];
        $etapas = [2804448];
        foreach ($etapas as $etapa){
            $temp = $this->_rest->getAllNegocios(1, $etapa);
            $ret = array_merge($ret, $temp);
        }
        return $ret;
    }
    
    private function gravarNegociosAprovadosCliente(){
        $negocios = $this->recuperarNegociosAprovados();
        foreach ($negocios as $negocio_atual){
            $codigo_negocio = $negocio_atual['id'] ?? '';
            $ordem_etapa = $negocio_atual['dealStage']['sequence'];
            $sql = "select * from bs_agendor_negocios where id = '$codigo_negocio'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $sql = "update bs_agendor_negocios set etapa = '$ordem_etapa', tipo = 'P' where id = '$codigo_negocio' and tipo = 'AC'";
                query($sql);
            }
        }
    }
    
    private function montarListaNegociosVelhos(){
        $sql = "select id, tipo from bs_agendor_negocios";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $this->_negocios_velhos[$row['tipo']][] = $row['id'];
            }
        }
    }
    
    private function montarListaCodigosVendedorProtheus(){
        $sql = "select * from bs_agendor_protheus_vendedores where id_agendor in (select id_agendor from bs_agendor_protheus_vendedores group by id_agendor having count(*) = 1)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $this->_codigosVendedoresProtheus[$row['id_agendor']] = $row['cod_protheus'];
            }
        }
        
        $sql = "select id_agendor, cod_protheus, coalesce(regiao, 'BASE') as regiao from bs_agendor_protheus_vendedores left join bs_regiao_vendedor on (bs_agendor_protheus_vendedores.cod_protheus = bs_regiao_vendedor.vendedor) where id_agendor not in (select id_agendor from bs_agendor_protheus_vendedores group by id_agendor having count(*) = 1)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $this->_codigosVendedoresProtheus[$row['id_agendor']][$row['regiao']] = $row['cod_protheus'];
            }
        }
    }
    
    private function renovarProdutosProtheus(){
        $sql = "
            
SELECT LTRIM(RTRIM(B1_COD)) AS B1_COD
	,LTRIM(RTRIM(B1_DESC)) AS B1_DESC
	,COALESCE(B1_IPI, 0) AS B1_IPI
	,COALESCE(VALOR, 0) AS VALOR
FROM (
	SELECT *
		,LEN(CASE
				WHEN B1_REGANVI IS NOT NULL
					AND LEN(LTRIM(RTRIM(B1_REGANVI))) >= 1
					THEN B1_REGANVI
				WHEN B1_REGANV2 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV2))) >= 1
					THEN B1_REGANV2
				WHEN B1_REGANV3 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV3))) >= 1
					THEN B1_REGANV3
				WHEN B1_REGANV4 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV4))) >= 1
					THEN B1_REGANV4
				WHEN B1_REGANV5 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV5))) >= 1
					THEN B1_REGANV5
				ELSE ''
				END) AS COD_ANVISA
	FROM SB1040
	WHERE B1_TIPO IN (
			'PA'
			,'PI'
			,'MR'
			)
		AND D_E_L_E_T_ != '*'
	) PRODUTOS
LEFT JOIN (
	SELECT DA1_CODPRO
		,MAX(DA1_PRCVEN) AS VALOR
	FROM DA1040
	WHERE DA1_CODTAB = '001'
		AND D_E_L_E_T_ != '*'
        AND DA1_PRCVEN > 0
	GROUP BY DA1_CODPRO
	) AS PRECOS ON (PRODUTOS.B1_COD = PRECOS.DA1_CODPRO)
WHERE COD_ANVISA >= 1
            
            
";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            //esvaziar tabela
            $sql = "insert into bs_produtos values ";
            $dados = [];
            foreach ($rows as $row){
                $etiqueta = str_replace("'", "''", $row['B1_DESC']);
                $dados[] = "('{$row['B1_COD']}', '$etiqueta', {$row['B1_IPI']}, {$row['VALOR']})";
            }
            if(count($dados) > 0){
                query("truncate bs_produtos");
                while(count($dados) > 0){
                    $dados_temp = [];
                    $sql = "insert into bs_produtos values ";
                    while (count($dados) > 0 && count($dados_temp) < 200) {
                        $dados_temp[] = array_shift($dados);
                    }
                    $sql .= implode(', ', $dados_temp);
                    query($sql);
                }
            }
        }
    }
    
    private function atualizarProdutosAgendor(){
        $produtos_protheus = $this->getProdutosProtheus();
        $produtos_agendor = $this->_rest->getAllProdutos(); //pega todos os produtos do agendor
        if(is_array($produtos_protheus) && count($produtos_protheus) > 0 && is_array($produtos_agendor) && count($produtos_agendor) > 0){
            $codigos_protheus = array_column($produtos_protheus, 'cod');
            $codigos_agendor = array_column($produtos_agendor, 'code');
            foreach ($codigos_protheus as $cod){
                if(!in_array($cod, $codigos_agendor)){
                    $etiqueta = $produtos_protheus[$cod]['etiqueta'];
                    $dados = ['name' => $etiqueta, 'active' => true, 'code' => $cod];
                    $valor = $produtos_protheus[$cod]['valor'];
                    if($valor > 0){
                        $dados['price'] = $valor;
                    }
                    do{
                        $resposta = $this->_rest->cadastrarProduto($dados);
                        sleep(3);
                    }
                    while($resposta === false);
                }
            }
            foreach ($produtos_agendor as $produto){
                $cod = $produto['code'];
                $temp = [];
                if(!in_array($cod, $codigos_protheus)){
                    if($produto['active']){
                        $temp = $produto;
                        $temp['active'] = false;
                        unset($temp['createdAt']);
                        unset($temp['id']);
                    }
                }
                else{
                    //verifica se precisa atualizar
                    if(!$this->compararProdutos($produto, $produtos_protheus[$cod])){
                        $temp = $produto;
                        $temp['active'] = true;
                        unset($temp['createdAt']);
                        unset($temp['id']);
                        $temp['name'] =  $produtos_protheus[$cod]['etiqueta'];
                        $temp['price'] = $produtos_protheus[$cod]['valor'];
                    }
                }
                if(count($temp) > 0){
                    do{
                        $resposta = $this->_rest->atualizarProduto($produto['id'], $temp);
                        sleep(3);
                    }
                    while($resposta === false);
                }
            }
        }
    }
    
    private function criarOrcamentos(){
        global $config;
        global $nl;
        set_time_limit(0);
        $rest_protheus = new rest_protheus($config['protheus']['link'], $config['protheus']['user'], $config['protheus']['senha']);
        $negocios_salvar = [];
        $negocios = $this->getNegociosRelevantes();
        if(is_array($negocios) && count($negocios) > 0){
            foreach ($negocios as $negocio_atual){
                $codigo_negocio = $negocio_atual['id'] ?? '';
                $ordem_etapa = $negocio_atual['dealStage']['sequence'];
                if(!empty($codigo_negocio)){
                    $codigo_unico = $this->gerarCodigoUnico();
                    $negocio_completo = $this->_rest->getNegocio($codigo_negocio);
                    if($this->verificarNegocioEhNovo($codigo_negocio, 'N')){
                        $dados = $this->processarNegocio($negocio_completo['data'], $codigo_unico);
                        if($this->validarDados($dados)){
                            $json = $this->montarJsonFinal($dados);
                            $resposta = $rest_protheus->criarOrcamento($json);
                            if(is_array($resposta) && isset($resposta['errorCode']) && $resposta['errorCode'] == 0){
                                $this->gravarOrcamentoGoflow($codigo_unico, $codigo_negocio);
                                criarTarefaOrcamentoCriado($codigo_negocio, $codigo_unico);
                                //$this->bloquearOrcamento($codigo_unico, $rest_protheus);
                                bloquearOrcamento($codigo_unico);
                                $titulo_card = $negocio_completo['data']['title'];
                                $responsavel = $negocio_completo['data']['owner']['name'];
                                $valor = $negocio_completo['data']['value'];
                                $negocios_salvar[] = [$codigo_negocio, $ordem_etapa, $titulo_card, $responsavel, $valor, 'N'];
                            }
                            else{
                                //deu errado
                                log::gravaLog('erros_orcamento_agendor', "ocorreu algum erro com o negócio $codigo_negocio $nl retorno: " . json_encode($resposta) . "$nl dados: $json");
                            }
                        }
                        else{
                            log::gravaLog('erros_orcamento_agendor', "o dados do negócio $codigo_negocio não passaram pelo teste de validação $nl dados: " . json_encode($dados));
                        }
                    }
                    else{
                        $titulo_card = $negocio_completo['data']['title'];
                        $responsavel = $negocio_completo['data']['owner']['name'];
                        $valor = $negocio_completo['data']['value'];
                        //$negocios_salvar[] = [$codigo_negocio, $ordem_etapa];
                        $negocios_salvar[] = [$codigo_negocio, $ordem_etapa, $titulo_card, $responsavel, $valor, 'N'];
                    }
                }
            }
        }
        $this->renovarTabelaNegocios($negocios_salvar, 'N');
    }
    
    private function renovarTabelaNegocios($negocios_atuais, $tipo){
        //mudar função para
        $sql = "delete from bs_agendor_negocios where tipo = '$tipo'";
        if(count($negocios_atuais) > 0){
            $codigos = [];
            foreach ($negocios_atuais as $negocio){
                $codigos[] = "'{$negocio[0]}'";
            }
            $sql .= " or id in (" . implode(', ', $codigos) . ")";
        }
        query($sql);
        if(count($negocios_atuais) > 0){
            $dados = [];
            foreach ($negocios_atuais as $negocio){
                $temp = [];
                foreach ($negocio as $valor){
                    $temp[] = "'$valor'";
                }
                $dados[] = '(' . implode(', ', $temp) . ')';
            }
            $sql = "insert into bs_agendor_negocios values " . implode(', ', $dados);
            query($sql);
        }
    }
    
    private function gravarOrcamentoGoflow($codigo_unico, $codigo_negocio){
        $sql = "insert into bs_orcamentos values ('$codigo_unico', '$codigo_negocio', current_timestamp(), null, null, null, null, null, null, null, null, null)";
        query($sql);
    }
    
    private function montarJsonFinal($dados){
        $ret = json_encode($dados);
        $ret = str_replace('\\' . '/', '/', $ret);
        return $ret;
    }
    
    private function validarDados($dados){
        $ret = true;
        foreach ($dados as $valor){
            $ret = (!is_null($valor)) && $ret;
        }
        foreach ($dados['client'] as $valor){
            $ret = (!is_null($valor)) && $ret;
        }
        $ret = $ret && (count($dados['products']) > 0);
        return $ret;
    }
    
    private function getNegociosRelevantes(){
        $ret = [];
        $etapas = [2804447];
        foreach ($etapas as $etapa){
            $temp = $this->_rest->getAllNegocios(1, $etapa);
            $ret = array_merge($ret, $temp);
        }
        
        $temp = [];
        foreach($ret as $negocio){
            if($negocio['id'] == '19187952' || $negocio['id'] == 19187952){
                $temp[] = $negocio;
            }
        }
        
        $ret = $temp;
        
        return $ret;
    }
    
    private function verificarNegocioEhNovo($codigo_negocio, $tipo){
        return !in_array($codigo_negocio, $this->_negocios_velhos[$tipo] ?? []);
    }
    
    private function gerarCodigoUnico(){
        return $this->gerarBlocoCodigoUnico(8) . '-' . $this->gerarBlocoCodigoUnico(4) . '-' . $this->gerarBlocoCodigoUnico(4) . '-' . $this->gerarBlocoCodigoUnico(4) . '-' . $this->gerarBlocoCodigoUnico(12);
    }
    
    private function gerarBlocoCodigoUnico($tam){
        $ret = '';
        $string = "0123456789abcdef";
        $quant= strlen($string);
        for($i=0;$i<$tam;$i++){
            $ret .= $string[rand(0, $quant-1)];
        }
        return $ret;
    }
    
    private function processarNegocio($negocio, $codigo_aleatorio){
        $ret = array();
        
        $id_empresa = $negocio['organization']['id'];
        //$cnpj = $negocio['organization']['cnpj'];
        $ret['appEmail'] = $this->getEmailVendedor($negocio);
        $ret['client'] = $this->getDadosEmpresaAgendor($id_empresa);
        $ret['delivery'] = $ret['client']['delivery'];
        $ret['file'] = '';
        $ret['obs'] = '';
        $ret['paymentCondition'] = '007 - 30 DIAS';
        $ret['products'] = $this->getProdutos($negocio);
        $ret['saleDate'] = datas::data_hoje();
        $ret['sellerCode'] = $this->getCodigoVendedor($negocio, $ret['client']['state']);
        $ret['type'] = 'O';
        $ret['uid'] = $codigo_aleatorio;
        return $ret;
    }
    
    private function getEmailVendedor($negocio){
        $ret = '';
        $codigo_vendedor = $negocio['owner']['id'] ?? '';
        if(!empty($codigo_vendedor)){
            if(isset($this->_emailVendedores[$codigo_vendedor])){
                $ret = $this->_emailVendedores[$codigo_vendedor];
            }
            else{
                $dados_usuario = $this->_rest->getUser($codigo_vendedor);
                if(is_array($dados_usuario) && count($dados_usuario) > 0){
                    $ret = strtolower($dados_usuario['data'][0]['contact']['email']);
                    $this->_emailVendedores[$codigo_vendedor] = $ret;
                }
            }
        }
        return $ret;
    }
    
    private function getDadosEmpresaAgendor($id_empresa){
        $ret = array();
        $temp = $this->_rest->getEmpresa($id_empresa);
        if(is_array($temp) && count($temp) > 0){
            $dados_empresa = $temp['data'];
            $rua = $dados_empresa['address']['streetName'] . ' ' . $dados_empresa['address']['streetNumber'];
            $telefone = !empty($dados_empresa['contact']['work']) ? $dados_empresa['contact']['work'] : $dados_empresa['contact']['mobile'];
            $telefone = str_replace(array('(', ')', '-', '/', '\\', ' '), '', $telefone);
            $ret = array(
                'cep' => $dados_empresa['address']['postalCode'] ?? '',
                'city' => $dados_empresa['address']['city'] ?? '',
                'delivery' => $rua,
                'document' => $dados_empresa['cnpj'] ?? '',
                'email' => $dados_empresa['email'] ?? '',
                'name' => $dados_empresa['legalName'] ?? '',
                'neighborhood' => $dados_empresa['address']['district'] ?? '',
                'phone' => $telefone,
                'state' => $dados_empresa['address']['state'] ?? '',
                'stateInscription' => '',
                'street' => $rua,
            );
        }
        return $ret;
    }
    
    private function getProdutos($negocio){
        $ret = array();
        $entidades = $negocio['products_entities'];
        if(is_array($entidades) && count($entidades) > 0){
            foreach ($entidades as $entidade){
                $codigo = $entidade['code'];
                $quantidade = $entidade['quantity'] > 0 ? $entidade['quantity'] : 1;
                $temp = array(
                    'code' => $codigo,
                    'ipi' => $this->getIpiProduto($codigo),
                    'obs' => '',
                    'operation' => '01',
                    'price' => $entidade['unitValue'],
                    'quantity' => $quantidade,
                );
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getIpiProduto($codigo){
        $ret = 1;
        $codigo = trim($codigo);
        $sql = "select ipi from bs_produtos where cod = '$codigo'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['ipi'];
        }
        
        return $ret;
    }
    
    private function getCodigoVendedor($negocio, $estado){
        $ret = '';
        $codigo_vendedor = $negocio['owner']['id'];
        if(isset($this->_codigosVendedoresProtheus[$codigo_vendedor])){
            if(!is_array($this->_codigosVendedoresProtheus[$codigo_vendedor])){
                $ret = $this->_codigosVendedoresProtheus[$codigo_vendedor];
            }
            else{
                if(isset($this->_codigosVendedoresProtheus[$codigo_vendedor][$estado]) || isset($this->_codigosVendedoresProtheus[$codigo_vendedor]['BASE'])){
                    $ret = $this->_codigosVendedoresProtheus[$codigo_vendedor][$estado] ?? $this->_codigosVendedoresProtheus[$codigo_vendedor]['BASE'];
                }
                else{
                    $estado = array_key_first($this->_codigosVendedoresProtheus[$codigo_vendedor]);
                    $ret = $this->_codigosVendedoresProtheus[$codigo_vendedor][$estado];
                }
            }
        }
        return $ret;
    }
}