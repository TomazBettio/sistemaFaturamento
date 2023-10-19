<?php
/*
 * Data Criação: 15/09/2020
 * Autor: BCS
 *
 * 
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class cad_projetos{
    var $funcoes_publicas = array(
        'index' 	    => true,
        'excluir' 		=> true,
        'editar'        => true,
        'salvar'        => true,
    );
    
    //Programa
    private $_programa = '';
    
    public function __construct(){
    	$this->_programa = get_class($this);
    }

    function index(){
    	$param = [];
        $bw = new tabela01($param);
        
        $bw->addColuna(array('campo' => 'id'			, 'etiqueta' => 'ID'				,'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
        $bw->addColuna(array('campo' => 'clienteDesc' 	, 'etiqueta' => 'Cliente'			,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $bw->addColuna(array('campo' => 'titulo' 		, 'etiqueta' => 'Título'			,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        //$bw->addColuna(array('campo' => 'descricao' 	, 'etiqueta' => 'Descrição'			,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $bw->addColuna(array('campo' => 'respDesc' 		, 'etiqueta' => 'Responsável'		,'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $bw->addColuna(array('campo' => 'tipoDesc'		, 'etiqueta' => 'Tipo'				,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $bw->addColuna(array('campo' => 'inicio' 		, 'etiqueta' => 'Início'			,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $bw->addColuna(array('campo' => 'fim' 			, 'etiqueta' => 'Fim'				,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        
        $param = array(
            'texto' => 'Editar',
            'link' => getLink() . 'editar&id=',
            'coluna' => 'id64',
            'width' => 10,
            'flag' => '',
            'cor' => 'success'
        );
        $bw->addAcao($param);
        
        $param2 = array(
            'texto' => 'Excluir',
            'link' => getLink() . 'excluir&id=',
            'coluna' => 'id64',
            'width' => 10,
            'flag' => '',
            'cor' => 'danger'
        );
        $bw->addAcao($param2);
        
        $dados = $this->getDados();
        $bw->setDados($dados);
        
        $param = [];
        
        $param = [];
        $param['titulo'] = 'Cadastro de Projetos';
        $param['conteudo'] = $bw;
        $p = array('onclick' => "setLocation('" . getLink() . "editar&id=0')",'texto' => 'Incluir', 'cor' => COR_PADRAO_BOTOES);
        $param['botoesTitulo'][] = $p;
        $ret = addCard($param);
        
        return $ret;
    }
    
    
    function editar(){
    	$ret = '';
    	
    	$id = getParam($_GET, 'id', 0);
      	if($id != '0'){
    		$id = base64_decode($id);
    	}
    	$dados = $this->getDados($id);
    	$form = new form01(array());
    	$form->addCampo(array('id' => '','campo' => 'formPrograma[cliente]'		,'etiqueta' => 'Cliente'	,'tipo' => 'A' ,'tamanho' => '15','linha' => '1', 'largura' => 5,'valor' => $dados['cliente']		, 'lista' => funcoes_cad::getListaClientes('nome')	,'validacao' => '','obrigatorio' => true));
    	$form->addCampo(array('id' => '','campo' => 'formPrograma[titulo]'		,'etiqueta' => 'Título'		,'tipo' => 'T' ,'tamanho' => '15','linha' => '1', 'largura' => 7,'valor' => $dados['titulo']		, 'lista' => ''										,'validacao' => '','obrigatorio' => true));
    	$form->addCampo(array('id' => '','campo' => 'formPrograma[tipo]'		,'etiqueta' => 'Tipo'		,'tipo' => 'A' ,'tamanho' => '06','linha' => '2', 'largura' => 2,'valor' => $dados['tipo']			, 'lista' => tabela('000005','',true)				,'validacao' => '','obrigatorio' => true));
    	$form->addCampo(array('id' => '','campo' => 'formPrograma[inicio]'		,'etiqueta' => 'Data Início','tipo' => 'D' ,'tamanho' => '10','linha' => '2', 'largura' => 3,'valor' => $dados['inicio']		, 'lista' => ''										,'validacao' => '','obrigatorio' => true));
    	$form->addCampo(array('id' => '','campo' => 'formPrograma[fim]'			,'etiqueta' => 'Data Fim'	,'tipo' => 'D' ,'tamanho' => '10','linha' => '2', 'largura' => 3,'valor' => $dados['fim']			, 'lista' => ''										,'validacao' => '','obrigatorio' => false));
    	$form->addCampo(array('id' => '','campo' => 'formPrograma[responsavel]'	,'etiqueta' => 'Responsavel','tipo' => 'A' ,'tamanho' => '15','linha' => '2', 'largura' => 4,'valor' => $dados['responsavel']	, 'lista' => funcoes_cad::getListaRecursos()		,'validacao' => '','obrigatorio' => true));
    	$form->addCampo(array('id' => '','campo' => 'formPrograma[descricao]'	,'etiqueta' => 'Descrição'	,'tipo' => 'TA','tamanho' => '10','linha' => '2', 'largura' =>12,'valor' => $dados['descricao']		, 'lista' => ''										,'validacao' => '','obrigatorio' => true, 'linhasTA' => 10));
    	
    	$form->setEnvio(getLink() . 'salvar&id='.base64_encode($id), 'formPrograma', 'formPrograma');
    	
    	$titulo = $id == 0 ? 'NOVO Projeto' : 'EDITAR Projeto';
    	
    	$param = [];
    	$param['titulo'] = $titulo;
    	$param['conteudo'] = $form;
    	$ret = addCard($param);
    	
    	putAppVar('sdm_projeto_form', 'ok');
    	
    	return $ret;
    }
    
    
    //----------------------------------------------------------------------------------------------------------------------- GET ------------------------
    private function getDados($id = null){
        $ret = [];
        $sql = '';
        $colunas = array('id','cliente','titulo','descricao','tipo','inicio','fim','responsavel');
        if(is_null($id)){
        	$sql = "SELECT * FROM sdm_projetos WHERE del <> '*' ORDER BY id";
        }elseif($id > 0){
        	$sql = "SELECT * FROM sdm_projetos WHERE del <> '*' AND id = $id";
        }else{
        	foreach ($colunas as $c) {
        		$ret[$c] = '';
        	}
        }
        if(!empty($sql)){
//echo "$sql <br>\n";
	        $rows = query($sql);
	        if(is_array($rows)&&count($rows)>0){
	            foreach ($rows as $r) {
	                foreach ($colunas as $c) {
	                    $temp[$c] = $r[$c];
	                }
	                $temp['clienteDesc']= funcoes_cad::getClienteCampo($temp['cliente']);
	                $temp['inicio'] 	= datas::dataS2D($temp['inicio']);
	                $temp['fim'] 		= datas::dataS2D($temp['fim']);
	                $temp['tipoDesc'] 	= getTabelaDesc('000005',$temp['tipo']);
	                $temp['id64'] 		= base64_encode($temp['id']);
	                $temp['respDesc']	= funcoes_cad::getRecursoCampo($temp['responsavel'],'apelido');
	
	                if(is_null($id)){
	                	$ret[] = $temp;
	                }else{
	                	$ret = $temp;
	                }
	            }
	        }
        }
        return $ret;
    }
    
    function salvar(){
    	if(getAppVar('sdm_projeto_form') == 'ok'){
	        $id = getParam($_GET, 'id', 0);
	        if($id != '0'){
	        	$id = base64_decode($id);
	        }
	        if(isset($_POST['formPrograma']) && count($_POST['formPrograma'])>0){
	            $dados = $_POST['formPrograma'];
	            $campos = [];
//print_r($dados);
	            $campos['cliente'] 		= $dados['cliente'];
	            $campos['titulo']		= $dados['titulo'];
	            $campos['inicio'] 		= datas::dataD2S($dados['inicio']);
	            $campos['fim'] 			= datas::dataD2S($dados['fim']);
	            $campos['descricao']	= $dados['descricao'];
	            $campos['tipo']			= $dados['tipo'];
	            $campos['responsavel']	= $dados['responsavel'];
	            
	            if($id == 0){
	            	$campos['inc_user']	= getUsuario();
	                $sql = montaSQL($campos, 'sdm_projetos');
	            }else {
	            	$sql = montaSQL($campos, 'sdm_projetos','UPDATE', "id = $id");
	            }
//echo "$sql <br>\n";
	            query($sql);
	            
	        }
    	}
    	
        putAppVar('sdm_projeto_form', '');
        
        return $this->index();
    }
    
    function excluir(){
        $id = getParam($_GET, 'id', 0);
        if($id != '0'){
        	$id = base64_decode($id);
        	
        	$campos = [];
        	$campos['del'] = '*';
        	$campos['del_user'] = getUsuario();
        	$campos['del_data'] = date("Y-m-d H:i:s");
        	
        	$sql = montaSQL($campos, 'sdm_projetos','UPDATE', "id = $id");
        	query($sql);
        }

        return $this->index();
    }
}