<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class bhio_metas{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editarLinha'   => true,
        'salvarMetaLinha' => true,
        'editarVendedor' => true,
        'salvarMetaVendedor' => true,
    );
    
    private $_empresas;
    private $_linhas;
    private $_programa;
    private $_filtro;
    private $_docs;
    private $_rest;
    private $_metas;
    
    public function __construct(){
        $this->_empresas = array('990');
        $this->_programa = get_class($this);
        
        
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Código'		, 'variavel' => 'COD', 'tipo' => 'T', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        
        $this->_rest = new rest_protheus('http://192.168.1.125:8888/rest', 'admin', 'q1w2e3');
        
        $this->_linhas = $this->montarListaLinhas();
        
        if(getAppVar('listaVendedoresBhio') === null){
            $lista_temporaria = array();
            $vendedores = $this->_rest->getAllVendedores();
            foreach ($vendedores as $vendedor){
                $empresa = $vendedor['companyId'];
                while (strlen($empresa) < 3){
                    $empresa .= '0';
                }
                $nome = $vendedor['name']; //ou shortName
                $id = $vendedor['code'];
                $lista_temporaria[] = array(
                    'id' => $id,
                    'nome' => $nome,
                    'empresa' => $empresa
                );
            }
            putAppVar('listaVendedoresBhio', $lista_temporaria);
        }
    }
    
    private function montarListaLinhas(){
        $ret = array();
        $linhas = $this->_rest->getAllLinhas();
        foreach ($linhas as $linha){
            $ret[trim($linha['Code'])] = $linha['Name'];
        }
        
        return $ret;
    }
    
    public function index(){
        $ret = '';
        $this->_filtro = new formfiltro01($this->_programa, array());
        $aba_ativa = $this->escolherAbaAtiva();
        $tabs = array(
            array('titulo' => 'Linhas', 'conteudo' => $this->montarAbaLinhas()),
            array('titulo' => 'Vendedores', 'conteudo' => $this->montarAbaVendedores()),
        );
        $ret = formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tabs, 'ativo' => $aba_ativa));
        $ret = addCard(array('titulo' => 'Metas', 'conteudo' => $ret));
        return $ret;
    }
    
    private function escolherAbaAtiva(){
        $ret = 0;
        if(!$this->_filtro->getPrimeira()){
            $ret = 1;
        }
        return $ret;
    }
    
    private function montarAbaLinhas(){
        $ret = '';
        $temp = array();
        $linhas_bk = $this->_linhas;
        while (count($this->_linhas) > 0) {
            $temp_linha = array();
            while(count($temp_linha) < 3 && count($this->_linhas) > 0){
                $codigo_temp = array_key_first($this->_linhas);
                $param['texto'] 	= $this->_linhas[$codigo_temp];
                $param['width'] 	= 30;
                $param['flag'] 		= '';
                $param['onclick'] 		= "setLocation('" . getLink() . "editarLinha&id=$codigo_temp')";
                $param['cor'] 		= 'success';
                $param['bloco'] 		= true;
                $temp_linha[] = formbase01::formBotao($param);
                unset($this->_linhas[$codigo_temp]);
            }
            while(count($temp_linha) < 3){
                $temp_linha[] = '';
            }
            
            $param = array();
            $param['tamanhos'] = array(4, 4, 4);
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
        }

        $this->_linhas = $linhas_bk ;
        
        $ret = implode('<br>', $temp);
        return $ret;
    }
    
    private function montarAbaVendedores(){
        $ret = '';
        if(!$this->_filtro->getPrimeira()){
            $dados_filtro = $this->_filtro->getFiltro();
            $dados = $this->getDadosAbaVendedores($dados_filtro['COD']);
        }
        else{
            $dados = array();
        }
        $ret .= $this->_filtro;
        $temp = array();
        while (count($dados) > 0) {
            $temp_linha = array();
            while(count($temp_linha) < 3 && count($dados) > 0){
                $codigo_temp = array_key_first($dados);
                $param['texto'] 	= $dados[$codigo_temp]['nome'];
                $param['width'] 	= 30;
                $param['flag'] 		= '';
                $param['onclick'] 		= "setLocation('" . getLink() . "editarVendedor&id={$dados[$codigo_temp]['id']}&empresa={$dados[$codigo_temp]['empresa']}')";
                $param['cor'] 		= 'success';
                $param['bloco'] 		= true;
                $temp_linha[] = formbase01::formBotao($param);
                unset($dados[$codigo_temp]);
            }
            while(count($temp_linha) < 3){
                $temp_linha[] = '';
            }
            
            $param = array();
            $param['tamanhos'] = array(4, 4, 4);
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
        }
        if(is_array($temp) && count($temp) > 0){
            $ret .= '<br><br>';
            $ret .= implode('<br>', $temp);
        }
        
        return $ret;
    }
    
    private function getDadosAbaVendedores($codigo = ''){
        $ret = array();
        if(empty($codigo)){
            $ret = getAppVar('listaVendedoresBhio');
        }
        else{
            $lista_temporaria = getAppVar('listaVendedoresBhio');
            foreach ($lista_temporaria as $vendedor){
                if(strpos($vendedor['nome'], $codigo) !== false || strpos($vendedor['id'], $codigo) !== false){
                    $ret[] = $vendedor;
                }
            }
        }
        return $ret;
    }
    
    public function editarLinha(){
        $ret = '';
        $vendedores = $this->getDadosAbaVendedores();
        $linha = trim($_GET['id']);
        
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'INI', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'FIM', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        
        
        $filtro = new formfiltro01('bhioMetasLinha', array());
        
        $anoMesAtual = date('Ym');
        
        $dataIni = '';
        $dataFim = '';
        
        if(!$filtro->getPrimeira()){
            $dados_filtro = $filtro->getFiltro();
            $dataIni = $dados_filtro['INI'] ?? '';
            $dataFim = $dados_filtro['FIM'] ?? '';
            
            if(empty($dataIni) && empty($dataFim)){
                $dataIni = date('Y') . '0101';
                $dataFim = date('Y') . '1231';
            }
            elseif(!empty($dataIni) && empty($dataFim)){
                $dataFim = date('Ymd', strtotime('+1 years', strtotime($dataIni)));
            }
            elseif(empty($dataIni) && !empty($dataFim)){
                $dataIni = date('Ymd', strtotime('-1 years', strtotime($dataFim)));
            }
        }
        else{
            $dataIni = date('Y') . '0101';
            $dataFim = date('Y') . '1231';

        }
        
        $meses_prontos = datas::getMeses($dataIni, $dataFim);
        foreach ($meses_prontos as $m){
            $anoMes[$m['anomes']] = $m['mes'] . ' ' . $m['ano'];
        }
        $metas_existentes = $this->getMetas($linha, '', $dataIni, $dataFim);
        
        $tabela = new tabela01();
        $tabela->addColuna(array('campo' => 'nome'	, 'etiqueta' => 'Vendedor'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        foreach ($anoMes as $chave => $valor){
            $tabela->addColuna(array('campo' => $chave	, 'etiqueta' => $valor		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        }
        
        $dados = array();
        foreach ($vendedores as $v){
            $temp = array(
                'nome' => $v['nome'],
            );
            foreach ($anoMes as $chave => $valor){
                $valor = $metas_existentes[$v['empresa']][$linha][$v['id']][$chave] ?? 0;
                if($chave < $anoMesAtual){
                    $temp[$chave] = formataReais($valor);
                }
                else{
                    $temp[$chave] = formbase01::formTexto(array('nome' => 'camposMetas[' . $v['empresa'] . $chave . $v['id'] . ']', 'mascara' => 'V', 'valor' => $valor));
                }
            }
            
            $dados[] = $temp;
        }
        
        $tabela->setDados($dados);
        
        
        $datas_hidden = formbase01::formHidden(array('nome' => 'datas[ini]', 'valor' => $dataIni)) . formbase01::formHidden(array('nome' => 'datas[fim]', 'valor' => $dataFim));
        
        $ret .= $filtro;
        $ret .= '<form id="formMetas" method="post" action="' . getLink() . 'salvarMetaLinha&id=' . $linha . '">' . $datas_hidden . $tabela . '</form>';
        
        $bt = array('texto' => 'Salvar Metas', 'onclick' => 'document.getElementById(\'formMetas\').submit();');
        $ret = addCard(array('titulo' => 'Metas ' . $this->_linhas[$_GET['id']], 'conteudo' => $ret, 'botoesTitulo' => array($bt)));
        return $ret;
    }
    
    public function salvarMetaLinha(){
        $dados = $_POST['camposMetas'];
        $datas = $_POST['datas'];
        $linha = $_GET['id'];
        if(is_array($dados) && count($dados) > 0){
            $this->_metas = $this->getMetas($linha, '', $datas['ini'], $datas['fim']);
            $this->getDocsMetas($linha, '', $datas['ini'], $datas['fim']);
            foreach ($dados as $chave => $valor){
                $ano = substr($chave, 3, 4);
                $mes = substr($chave, 7, 2);
                $empresa = substr($chave, 0, 3);
                $vendedor = substr($chave, 9);
                
                $this->integrarMeta($empresa, $linha, $vendedor, $mes, $ano, $valor);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    private function integrarMeta($empresa, $linha, $vendedor, $mes, $ano, $valor){
        $valor_formatado = str_replace(array('.', ','), array('', '.'), $valor);
        if(!isset($this->_metas[$empresa][$linha][$vendedor]) && !empty(str_replace(array('0', ',', '.'), '', $valor))){
            //cria a meta completa
            $doc = $this->getDocEspecifico($empresa, $linha, $vendedor, $ano);
            $dado_meta_nova = array(
                'CompanyId' => $empresa,
                'InternalId' => "01$doc",
                'BranchId' => '01',
                'CompanyInternalId' => $empresa . "01" . $doc,
                'Code' => $doc,
                'SalesTargetDescription' => 'temp 2023 2',
                
                'ListOfSalesTargets' => array(
                    array(
                        'SalesTargetItem' => '0'  .$mes,
                        'BillingDate' => $ano . $mes . '01',
                        'SellerCode' => $vendedor,
                        'Quantity' => 1,
                        'Price' => floatval($valor_formatado),
                        'Currency' => 1,
                        'BlockedRecord' => '2',
                    ),
                ),
                
                //'ListOfSalesTargets' => array(),
            );
            $this->_rest->incluirMetaNova($dado_meta_nova);
        }
        elseif(!isset($this->_metas[$empresa][$linha][$vendedor][$ano . $mes]) && !empty(str_replace(array('0', ',', '.'), '', $valor))){
            //adiciona alvo
            $doc = $this->getDocEspecifico($empresa, $linha, $vendedor, $ano);
            $dado_meta_nova = array(
                //'CompanyId' => '990',
                'InternalId' => '01' . $doc,
                'BillingDate' => $ano . $mes . '01',
                'SalesTargetItem' => '0' . $mes,
                'SellerCode' => $vendedor,
                'Quantity' => 1,
                'Price' => floatval($valor_formatado),
                'Currency' => 1,
                'BlockedRecord' => '2',
                
                //'ListOfSalesTargets' => array(),
            );
            $this->_rest->incluirSubMeta('01' . $doc, $dado_meta_nova);
        }
        elseif(isset($this->_metas[$empresa][$linha][$vendedor][$ano . $mes]) && strval($this->_metas[$empresa][$linha][$vendedor][$ano . $mes]) === $valor_formatado){
            $doc = $this->getDocEspecifico($empresa, $linha, $vendedor, $ano);
            $this->_rest->atualizarSubMeta('01' . $doc, '0' . $mes, array('Price' => floatval($valor_formatado)));
        }
        $this->_metas[$empresa][$linha][$vendedor][$ano . $mes] = $valor_formatado;
    }
    
    private function getDocsMetas($linha = '', $vendedor = '', $dataIni = '', $dataFim = ''){
        $ret = array();
        $sql = "select * from bs_metas_doc";
        $where = array();
        if(!empty($linha)){
            $where[] = "linha = '$linha'";
        }
        if(!empty($vendedor)){
            $where[] = "vendedor = '$vendedor'";
        }
        if(!empty($dataIni) && !empty($dataFim)){
            $anoIni = substr($dataIni, 0, 4);
            $anoFim = substr($dataFim, 0, 4);
            $where[] = "ano BETWEEN  '$anoIni' AND '$anoFim'";
        }
        if(count($where) > 0){
            $sql .= ' where ' . implode(' and ', $where);
        }
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['empresa']][$row['linha']][$row['vendedor']][$row['ano']] = $row['doc'];
            }
        }
        $this->_docs = $ret;
        //return $ret;
    }
    
    private function getDocEspecifico($empresa, $linha, $vendedor, $ano){
        $ret = $this->_docs[$empresa][$linha][$vendedor][$ano] ?? $this->inserirDocMeta($empresa, $linha, $vendedor, $ano);
        return $ret;
    }
    
    private function inserirDocMeta($empresa, $linha, $vendedor, $ano){
        $ret = '';
        $sql = "select max(id) as id from bs_metas_doc";
        $rows = query($sql);
        if(is_array($rows)){
            $id = '1';
            if(count($rows) > 0){
                $id = $rows[0]['id'];
            }
            $id_novo = $id + 1;
            $doc = $id_novo;
            while (strlen($doc) < 8) {
                $doc = '0' . $doc;
            }
            $doc = 'V' . $doc;
            $sql = "insert into bs_metas_doc values ($id_novo, '$empresa', '$linha', '$vendedor', '$ano', '$doc')";
            query($sql);
            $ret = $doc;
            $this->_docs[$empresa][$linha][$vendedor][$ano] = $doc;
        }
        return $ret;
    }
    
    private function getMetas($linha_filtro = '', $vendedor_filtro = '',  $dataIni = '', $dataFim = ''){
        $ret = array();
        $doc_reverso = array();
        $sql = "select * from bs_metas_doc";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $doc_reverso[$row['doc']] = array(
                    'linha' => $row['linha'],
                    'vendedor' => $row['vendedor'],
                    'ano' => $row['ano'],
                );
            }
        }
        $metas = $this->_rest->getAllMetas();
        foreach ($metas as $meta){
            if(isset($doc_reverso[$meta['Code']])){
                $empresa = $meta['CompanyId'];
                while(strlen($empresa) < 3){
                    $empresa .= '0';
                }
                $doc = $meta['Code'];
                if(is_array($meta['ListOfsalestargets']) && count($meta['ListOfsalestargets']) > 0){
                    foreach ($meta['ListOfsalestargets'] as $alvo){
                        $data = substr(datas::dataD2S($alvo['BillingDate']), 0, 6);
                        $vendedor = $doc_reverso[$doc]['vendedor'];
                        $linha = $doc_reverso[$doc]['linha'];
                        $ret[$empresa][$linha][$vendedor][$data] = $alvo['Price'];
                    }
                }
            }
        }
        return $ret;
    }
    
    private function getUltimoRecNo(){
        $ret = array();
        foreach ($this->_empresas as $empresa){
            $ret[$empresa] = 1;
            $sql = "select max(R_E_C_N_O_) as ID from SCT$empresa";
            $rows = query2($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret[$empresa] += intval($rows[0]['ID']);
            }
        }
        return $ret;
    }
    
    public function editarVendedor(){
        $ret = '';
        $vendedor = $_GET['id'];
        $empresa = $_GET['empresa'];
        
        
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'INI', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'FIM', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        
        
        $filtro = new formfiltro01('bhioMetasLinha', array());
        
        $anoMesAtual = date('Ym');
        
        $dataIni = '';
        $dataFim = '';
        
        if(!$filtro->getPrimeira()){
            $dados_filtro = $filtro->getFiltro();
            $dataIni = $dados_filtro['INI'] ?? '';
            $dataFim = $dados_filtro['FIM'] ?? '';
            
            if(empty($dataIni) && empty($dataFim)){
                $dataIni = date('Y') . '0101';
                $dataFim = date('Y') . '1231';
            }
            elseif(!empty($dataIni) && empty($dataFim)){
                $dataFim = date('Ymd', strtotime('+1 years', strtotime($dataIni)));
            }
            elseif(empty($dataIni) && !empty($dataFim)){
                $dataIni = date('Ymd', strtotime('-1 years', strtotime($dataFim)));
            }
        }
        else{
            $dataIni = date('Y') . '0101';
            $dataFim = date('Y') . '1231';
            
        }
        
        $meses_prontos = datas::getMeses($dataIni, $dataFim);
        foreach ($meses_prontos as $m){
            $anoMes[$m['anomes']] = $m['mes'] . ' ' . $m['ano'];
        }
        $metas_existentes = $this->getMetas('', $vendedor, $dataIni, $dataFim);
        
        $tabela = new tabela01();
        $tabela->addColuna(array('campo' => 'nome'	, 'etiqueta' => 'Vendedor'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        foreach ($anoMes as $chave => $valor){
            $tabela->addColuna(array('campo' => $chave	, 'etiqueta' => $valor		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        }
        
        $dados = array();
        
        foreach ($this->_linhas as $cod_linha => $linha){
            $temp = array(
                'nome' => $linha,
            );
            foreach ($anoMes as $chave => $valor){
                $valor = $metas_existentes[$empresa][$cod_linha][$vendedor][$chave] ?? 0;
                if($chave < $anoMesAtual){
                    $temp[$chave] = formataReais($valor);
                }
                else{
                    $temp[$chave] = formbase01::formTexto(array('nome' => 'camposMetas[' . $empresa . $chave . $cod_linha . ']', 'mascara' => 'V', 'valor' => $valor));
                }
            }
            
            $dados[] = $temp;
        }
        
        $tabela->setDados($dados);
        
        
        $datas_hidden = formbase01::formHidden(array('nome' => 'datas[ini]', 'valor' => $dataIni)) . formbase01::formHidden(array('nome' => 'datas[fim]', 'valor' => $dataFim));
        
        $ret .= $filtro;
        $ret .= '<form id="formMetas" method="post" action="' . getLink() . 'salvarMetaVendedor&id=' . $vendedor . '&empresa=' . $empresa . '">' . $datas_hidden . $tabela . '</form>';
        
        $bt = array('texto' => 'Salvar Metas', 'onclick' => 'document.getElementById(\'formMetas\').submit();');
        $ret = addCard(array('titulo' => 'Metas ' . 'nome do vendedor', 'conteudo' => $ret, 'botoesTitulo' => array($bt)));
        return $ret;
    }
    
    public function salvarMetaVendedor(){
        $dados = $_POST['camposMetas'];
        $datas = $_POST['datas'];
        $vendedor = $_GET['id'];
        if(is_array($dados) && count($dados) > 0){
            $this->getDocsMetas('', $vendedor, $datas['ini'], $datas['fim']);
            $this->_metas = $this->getMetas('', $vendedor, $datas['ini'], $datas['fim']);
            foreach ($dados as $chave => $valor){
                $empresa = substr($chave, 0, 3);
                $ano = substr($chave, 3, 4);
                $mes = substr($chave, 7, 2);
                $linha = substr($chave, 9);
                $this->integrarMeta($empresa, $linha, $vendedor, $mes, $ano, $valor);
            }
        }
        redireciona(getLink() . 'index');
    }
    
}