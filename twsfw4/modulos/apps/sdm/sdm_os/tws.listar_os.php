<?php
/*
 * Data Criação: 12/08/2020
 * Autor: bcs, Emanuel Thiel
 * 
 * Cliente: TWS
 * 
 * Descrição: Listagem/Cadastro de OSs
 * 
 * Alterações: União da Listagem e do Cadastro em um único arquivo 27/08/2020
 * 
 */
if (! defined('TWSiNet') || ! TWSiNet)
    die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

include_once($config['include'].'dompdf/autoload.inc.php');
// reference the Dompdf namespace
use Dompdf\Frame\FrameTree;
use Dompdf\Options;
use Dompdf\Dompdf;
use Dompdf\Css\Stylesheet;

class listar_os{
    var $funcoes_publicas = array(
        'index'			=> true,
        'excluir'       => true,
        'salvar'	    => true,
        'editar'		=> true,
        'editaModelo'   => true,
        'gravarEditor'  => true,  
        'geraPDF'       => true,
        'addJqueryAjax' => true,
        'ajax'          => true,
    );
    
    private $_programa;
    private $_titulo = 'Lista OS';
    private $_variaveis = [];
    
    //Tabela principal
    private $_tabela;
    
	public function __construct(){
        $this->_variaveis = array(
            'os_cliente'=> 'Cliente',
            'cliente_nome'=> 'Nome Cliente',
            'user'=> 'Usuário criador da OS',
            'usuario_nome'=> 'Nome completo do usuário criador da OS',
            'modulo'    => 'Módulo',
            'projeto'   => 'Projeto',
            'data'      => 'Data',
            'restricao' => 'Restrição',
            'pessoa'    => 'Pessoa',
            'hora_ini'  => 'Hora Início',
            'hora_fim'  => 'Hora Fim',
            'hora_add'  => 'Hora (+)',
            'hora_sub'  => 'Hora (-)',
            'hora_trans'=> 'Hora Translado',
            'hora_total'=> 'Hora Toral',
            'observacao'=> 'Observação',
			'id'		=> 'Id da OS',
			'lista_tarefas' => 'Lista de Tarefas'
        );
        
        $param = [];
        $param['titulo'] = "Ordens de Serviço";
        $param['mostraFiltro'] = true;
        $param['filtroTipo'] = 2;
        $this->_tabela = new tabela02($param);
        $this->_programa = $this->_tabela->getPrograma();

        if(true){
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Analista'	, 'variavel' => 'user'		, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'listar_os::listaAnalista()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Cliente'	, 'variavel' => 'os_cliente', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'listar_os::listaCliente()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Dt Início'	, 'variavel' => 'data_ini'	, 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''							, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Dt Fim'	, 'variavel' => 'data_fim'	, 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''							, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
        }
        
        addJS_ListaOS();
   }
	
	public function index(){
 		$ret = '';
 		
 		$this->montaColunas();
 		$this->adicionaBotoes();
	    
 		$filtro = $this->_tabela->getFiltro();
        
 		if(!$this->_tabela->getPrimeira()){
 		    $dados = $this->getDados($filtro);
 		    $this->_tabela->setDados($dados);
 		    $this->_tabela->setFooter("TOTAL: ". $this->totHoras($dados). " Horas");
 		}
 		
 		$ret .= $this->_tabela;

 		return $ret;
	}
	
	public function excluir()
	{
		$id = intval(getParam($_GET, 'id', 0));
		if(!$this->validaEdita($id)&&$id!=0){
			addPortalMensagem("", "Você não tem permissão para alterar essa OS", 'erro');
		}
		else{
			query("UPDATE sdm_os SET del = 'S' WHERE id = $id ");
			//Verifica se existe tarefas associadas (e libera a tarefa para ser refaturada)
			$this->verificaTarefasExclusao($id);
			query("UPDATE sdm_tarefas SET os = 0 WHERE os = $id ");
		}
		redireciona(getLink().'index');
	}
	
	public function ajax(){
		$ret = [];
		
		
		$ret[] = array('valor' => '', 'etiqueta' => '');
		$cliente = getParam($_GET, 'cliente', '');
		
		if($cliente != ''){
			$sql = "select * from sdm_projetos where cliente = '$cliente'";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$temp = array(
						'valor' => $row['id'],
						'etiqueta' => $row['titulo'],
					);
					$ret[] = $temp;
				}
			}
		}
		return json_encode($ret);
	}
	
	public function editar($id=0, $agenda='0'){
		$this->addJqueryAjax();
		if($id==0){
			$id = intval(getParam($_GET, 'id', 0));
		}
		if($agenda == '0'){
			$agenda = getParam($_GET, 'agenda', '');
		}
		$lis = array();
		if(isset($_POST['exportar']) && count($_POST['exportar']) > 0)
		{
			$id = 0;
			$lis = $_POST['exportar'];
		}
		$dados = $this->getDadosOS($id,$lis,$agenda);
		$ret = '';
		
		$param = [];
		$param['geraScriptValidacaoObrigatorios'] = true;
		$form = new form01($param);
		$alteraData = true;
		if($id == 0 && empty($agenda)){
			$form->addCampo(array('id' => 'cliente'	    , 'campo' => 'formOS[os][os_cliente]'   ,'valor'=> $dados['os']['os_cliente']	, 'etiqueta' => 'Cliente'		                         , 'tipo' => 'A'  , 'tamanho' => '10', 'linha' => 1, 'largura' => 6	, 'lista' => $this->listaClienteTab()								, 'validacao' => '', 'obrigatorio' => true, 'onchange' => 'callAjax();'));
			$form->addCampo(array('id' => 'projeto'	    , 'campo' => 'formOS[os][projeto]'      ,'valor'=> $dados['os']['projeto']		, 'etiqueta' => 'Projeto'	                             , 'tipo' => 'A'  , 'tamanho' => '10', 'linha' => 1, 'largura' => 4	, 'lista' => array(array('', ''))		             				, 'validacao' => ''));
		}else{
			$nomeCli = $this->getNomeCliente($dados['os']['os_cliente']);
			$form->addCampo(array('id' => 'cliente'	    , 'campo' => 'formOS[os][os_cliente]'   ,'valor'=> $nomeCli  				 	, 'etiqueta' => 'Cliente'		   						, 'tipo' => 'I'  , 'tamanho' => '10', 'linha' => 1, 'largura' => 6	, 'lista' => ''														, 'validacao' => ''	, 'obrigatorio' => true));
			$form->addCampo(array('id' => 'projeto'	    , 'campo' => 'formOS[os][projeto]'      ,'valor'=> $dados['os']['projeto']		, 'etiqueta' => 'Projeto'	                            , 'tipo' => 'A'	 , 'tamanho' => '10', 'linha' => 1, 'largura' => 4	, 'lista' => $this->criarListaProjetos($dados['os']['os_cliente'])	, 'validacao' => ''));
			if(!empty($agenda)){
				$alteraData = false;
				$form->addHidden('formOS[os][os_cliente]', $dados['os']['os_cliente']);
			}
		}
		$form->addCampo(array('id' => 'data'	    , 'campo' => 'formOS[os][data]'             ,'valor'=> $dados['os']['data']			, 'etiqueta' => 'Data'	                                , 'tipo' => 'D'	 , 'tamanho' => '10', 'linha' => 1, 'largura' => 2	, 'lista' => ''			             								, 'validacao' => '', 'readonly' => !$alteraData, 'obrigatorio' => true));
		
		$form->addCampo(array('id' => 'modulo'	    , 'campo' => 'formOS[os][modulo]'	        ,'valor'=> $dados['os']['modulo']		, 'etiqueta' => 'Módulo'	                                , 'tipo' => 'T'  , 'tamanho' => '10', 'linha' => 2, 'largura' => 6	, 'lista' => ''		                 								, 'validacao' => ''));
		$form->addCampo(array('id' => 'restricao'	, 'campo' => 'formOS[os][restricao]'        ,'valor'=> $dados['os']['restricao']	, 'etiqueta' => 'Restrição'	                            , 'tipo' => 'A'	 , 'tamanho' => '10', 'linha' => 2, 'largura' => 3	, 'lista' => tabela('000001')										, 'validacao' => '',                                                  'obrigatorio' => true));
		$form->addCampo(array('id' => 'usuario'	    , 'campo' => 'formOS[os][pessoa]'           ,'valor'=> $dados['os']['pessoa']		, 'etiqueta' => 'Usuário'	                            , 'tipo' => 'T'	 , 'tamanho' => '10', 'linha' => 2, 'largura' => 3	, 'lista' => ''			             								, 'validacao' => ''));
		
		$form->addCampo(array('id' => 'hora_ini'	, 'campo' => 'formOS[os][hora_ini]'         ,'valor'=> $dados['os']['hora_ini']		, 'etiqueta' => 'Hora Início'	                        , 'tipo' => 'T'	 , 'tamanho' => '10', 'linha' => 3, 'largura' => 2	, 'lista' => ''		                 								, 'validacao' => '', 'mascara' => 'H', 'onchange' => 'calculaHora();','obrigatorio' => true));
		$form->addCampo(array('id' => 'hora_fim'	, 'campo' => 'formOS[os][hora_fim]'         ,'valor'=> $dados['os']['hora_fim']		, 'etiqueta' => 'Hora Fim'	                            , 'tipo' => 'T'	 , 'tamanho' => '10', 'linha' => 3, 'largura' => 2	, 'lista' => ''			             								, 'validacao' => '', 'mascara' => 'H', 'onchange' => 'calculaHora();','obrigatorio' => true));
		$form->addCampo(array('id' => 'hora_add'	, 'campo' => 'formOS[os][hora_add]'         ,'valor'=> $dados['os']['hora_add']		, 'etiqueta' => 'Outros (+)'	                            , 'tipo' => 'T'	 , 'tamanho' => '10', 'linha' => 3, 'largura' => 2	, 'lista' => ''			             								, 'validacao' => '', 'mascara' => 'H', 'onchange' => 'calculaHora();'));
		$form->addCampo(array('id' => 'hora_sub'	, 'campo' => 'formOS[os][hora_sub]'         ,'valor'=> $dados['os']['hora_sub']		, 'etiqueta' => 'Outros (-)'	                            , 'tipo' => 'T'	 , 'tamanho' => '10', 'linha' => 3, 'largura' => 2	, 'lista' => ''			             								, 'validacao' => '', 'mascara' => 'H', 'onchange' => 'calculaHora();'));
		$form->addCampo(array('id' => 'hora_trans'	, 'campo' => 'formOS[os][hora_trans]'       ,'valor'=> $dados['os']['hora_trans']	, 'etiqueta' => 'Transladado'	                        , 'tipo' => 'T'	 , 'tamanho' => '10', 'linha' => 3, 'largura' => 2	, 'lista' => ''			             								, 'validacao' => '', 'mascara' => 'H', 'onchange' => 'calculaHora();'));
		$form->addCampo(array('id' => 'hora_total_f'	, 'campo' => 'formOS[os][hora_total]'   ,'valor'=> $dados['os']['hora_total'] 	, 'etiqueta' => 'Total'	                                , 'tipo' => 'I'	 , 'tamanho' => '10', 'linha' => 3, 'largura' => 2	, 'lista' => ''			             								, 'validacao' => '', 'mascara' => 'H'));
		$form->addHidden('formOS[os][hora_total]',''.$dados['os']['hora_total'],'hora_total');
		
		$form->addCampo(array('id' => 'observacao'	, 'campo' => 'formOS[os][observacao]'       ,'valor'=> $dados['os']['observacao']	, 'etiqueta' => 'Observação (vai ser impressa na OS):'	, 'tipo' => 'TA' , 'tamanho' => '10', 'linha' => 5, 'largura' => 6	,  'lista' => ''			, 'validacao' => ''));
		$form->addCampo(array('id' => 'observacao'	, 'campo' => 'formOS[os][obs_int]'			,'valor'=> $dados['os']['obs_int']  	, 'etiqueta' => 'Obs.Interna (não impressa na OS):'	    , 'tipo' => 'TA' , 'tamanho' => '10', 'linha' => 5, 'largura' => 6	,  'lista' => ''			, 'validacao' => ''));
		$ret .= $form;
		$ret .= $this->getTabelaTarefas($id,$lis);
		
		$link = getLink() . "salvar&id=$id&agenda=$agenda";
		if(count($lis) > 0){
			$link .= '&lis=' . implode('|', $lis);
		}
		
		$param = [];
		$param['sendFooter'] = true;
		$param['acao'] = $link;
		$param['id'] = 'formContrato';
		$param['nome'] = 'formContrato';
		$param['onsubmit'] = 'verificaObrigatorios';
		$ret = formbase01::form($param, $ret);
		
		$param = [];
		$param['conteudo'] = $ret;
		$param['titulo'] = 'Edição OS - '.formataNum($id,6);
		if($id==0)	    {
			$param['titulo'] = 'Nova OS';
		}
		$ret = addCard($param);
		
		return $ret;
	}
	
	public function salvar() {
		$dados = getParam($_POST, 'formOS', array());
		$id = intval(getParam($_GET, 'id', 0));
		$agenda = getParam($_GET, 'agenda', '0');
		$lista_sdm_os_tarefas = getParam($_GET, 'lis', '');
		
		if(is_array($dados) && count($dados) > 0){
			$dados_os 				= $dados['os'];
			$dados_tarefas 			= $dados['tarefas'];
			$dados_os['data'] 		= datas::dataD2S($dados_os['data']);
			$dados_os['user']		= getUsuario();
			if($id==0){
				$dados_os['id'] = $this->getId();
				$id = $dados_os['id'];
				$sql = montaSQL($dados_os, 'sdm_os');
			}
			else{
				$sql = montaSQL($dados_os, 'sdm_os', 'UPDATE'," id = $id  ");
			}
//echo $sql;
			query($sql);
			if($agenda != '0'){
				$sql = "select * from sdm_os where id = '" . $id . "'";
				$rows = query($sql);
				if(is_array($rows) && count($rows) > 0){
					$sql = "update sdm_agenda set os = '$id' where id = '" . base64_decode($agenda) . "'";
					query($sql);
				}
			}
			//	$id_os = $id != 0 ? $id : $this->recuperarOS($dados_os);
			$this->gravarTarefas($dados_tarefas, $id, $lista_sdm_os_tarefas);
		}
		redireciona();
	}
	
	public function editaModelo(){
		$param = array();
		$param['titulo'] = 'Ordem de Serviço';
		$param['editores'] = array( 'Modelo_OS');
		$param['variaveis'] = $this->_variaveis;
		$param['variaveisIndividuais'] = false;
		
		$editor = new editor01($param);
		$editor->setTituloEditor('Corpo da OS', 'Modelo_OS');
		
		$textoCorpo = $this->carregaModelo();
		
		$editor->setConteudo( $textoCorpo[0]['valor'], 'Modelo_OS');
		
		return '' . $editor;
	}
	
	public function gravarEditor(){
		$cliente 	= getCliente();
		$textoCorpo = getParam($_POST, 'Modelo_OS');
		//print_r($textoCorpo);
		$sys = new sys020();
		$rows = query("SELECT parametro FROM sys020 WHERE parametro = 'Modelo_OS_$cliente'");
		if(is_array($rows) && count($rows)>0)
		{
			$sys->atualiza($this->_programa, 'Modelo_OS_' . $cliente ,    $textoCorpo);
		}
		else{
			$dados = array(
				'programa'=>$this->_programa,
				'parametro'=>'Modelo_OS_' . $cliente,
				'tipo'=>'ED',
				'config'=>'',
				'descricao'=>'Modelo da Ordem de Serviço',
				'valor'=>$textoCorpo,
				
			);
			$sys->inclui($dados);
		}
		return ''.$this->index();
	}
	
	public function geraPDF(){
		$id = intval(getParam($_GET, 'id', 0));
		if($id == 0){
			$agenda = getParam($_GET, 'agenda', '0');
			if($agenda != '0'){
				$sql = "select os from sdm_agenda where id = '" . base64_decode($agenda) . "'";
				$rows = query($sql);
				if(is_array($rows) && count($rows) > 0){
					$id = $rows[0]['os'];
				}
			}
		}
		//    global $config;
		
		$options = new Options();
		$options->set('isPhpEnabled', TRUE);
		$options->set('isRemoteEnabled', true);
		
		$dados = $this->getDadosPDF($id);
		$texto = $this->carregaModelo();
		$texto = $this->setTextoOS($dados, $texto[0]['valor']);
		
		$texto_estilo = "<style>
td {font-size:13px;}
</style>";
		
		
		$texto = $texto_estilo . $texto;
//echo($texto);
		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($texto);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		
		//$dompdf->stream('document.pdf', array('Attachment'=>false));
		$pdf = $dompdf->output();
		if (headers_sent()) {
		    die("Unable to stream pdf: headers already sent");
		}
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Content-Type: application/pdf");
		header("Content-Length: " . mb_strlen($pdf, "8bit"));
		header(\Dompdf\Helpers::buildContentDispositionHeader("inline", "output.pdf"));
		echo $pdf;
		flush();
	}
	
	private function montaColunas(){
		$this->_tabela->addColuna(array('campo' => 'id'           , 'etiqueta' => 'ID'	       	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
		$this->_tabela->addColuna(array('campo' => 'user'         , 'etiqueta' => 'Analista'  	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'esquerda'));
		$this->_tabela->addColuna(array('campo' => 'os_cliente'   , 'etiqueta' => 'Cliente'   	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'esquerda'));
		$this->_tabela->addColuna(array('campo' => 'data'         , 'etiqueta' => 'Data'	   	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
		$this->_tabela->addColuna(array('campo' => 'hora_ini'     , 'etiqueta' => 'Início'	   	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
		$this->_tabela->addColuna(array('campo' => 'hora_fim'     , 'etiqueta' => 'Fim'	   		, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
		$this->_tabela->addColuna(array('campo' => 'hora_trans'   , 'etiqueta' => 'Translado' 	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
		$this->_tabela->addColuna(array('campo' => 'hora_total'   , 'etiqueta' => 'Total'	   	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));	
	}
	
	private function adicionaBotoes(){
		// Botão Editar
		$param = [];
		$param['texto'] = 'Editar';
		$param['link'] 	= getLink()."editar&id=";
		$param['coluna']= 'id';
		$param['width'] = 10;
		$param['flag'] 	= 'editar';
		$param['cor'] 	= 'success';
		$this->_tabela->addAcao($param);
		
		// Botão Excluir
		$this->jsConfirmaExclusao('"Confirme a exclusão da OS"');
		$param = [];
		$param['texto'] = 'Excluir';
		$param['link'] 	= "javascript:confirmaExclusao('" . getLink() .'excluir&id=' . "','{ID}')";
		$param['coluna']= 'id';
		$param['width'] = 10;
		$param['flag'] 	= 'editar';
		$param['cor'] 	= 'danger';
		$this->_tabela->addAcao($param);
		
		$param = [];
		$param['texto'] = 'Vizualizar';
		$param['onclick'] 	= "op2('" . getLink()."geraPDF&id={COLUNA:id}')";
		$param['coluna']	= 'id';
		$param['width'] 	= 10;
		//$param['flag'] 		= 'editar';
		//$param['cor'] 		= 'success';
		$this->_tabela->addAcao($param);
		
		$p = [];
		$p['onclick'] = "setLocation('".getLink()."editar')";
		$p['texto'] = 'Incluir';
		$p['cor'] = 'success';
		$this->_tabela->addBotaoTitulo($p);
		
		$p['onclick'] = "setLocation('".getLink()."editaModelo')";
		$p['texto'] = 'Editar Modelo PDF';
		//$p['cor'] = 'success';
		$this->_tabela->addBotaoTitulo($p);
	}
	
	private function totHoras($dados){
	    $ret = "00:00";
	    foreach($dados as $d)
	        if(isset($d['hora_total'])&&!empty($d['hora_total'])){
    	        $um = explode(":",$ret);
    	        $dois = explode(":",$d['hora_total']);
    	        
    	        $min = $um[1] + $dois[1];
    	        $hora = $um[0] + $dois[0] + floor($min/60);
    	        if($min>=60){
    	            $min = $min%60;
    	        }
    	        if($min<10){
    	            $min = '0'.$min;
    	        }
    	        
    	        $ret = $hora.":".$min;
	        }
	    return $ret;
	}
	
	//Pega os dados da tabela 
	private function getDados($filtro = array()){
	    $ret = array();
	    $texto = '';
        if (isset($filtro['user']) && $filtro['user'] != '')
        {
            $texto .= " AND user = '" . $filtro['user'] . "'";
        }
        if (isset($filtro['pessoa']) && $filtro['pessoa'] != '')
        {
            $texto .= " AND user = '" . $filtro['pessoa'] . "'";
        }
        if (isset($filtro['os_cliente']) && $filtro['os_cliente'] != '')
        {
            $texto .= " AND os_cliente = '" . $filtro['os_cliente'] . "'";
        }
        if (isset($filtro['data_ini']) && $filtro['data_ini'] != '')
        {
            $texto .= " AND data >= " . $filtro['data_ini'] . "";
        }
        if (isset($filtro['data_fim']) && $filtro['data_fim'] != '')
        {
            $texto .= " AND data <= " . $filtro['data_fim'] . "";
        }
        
            
        $col = array('id','user','cliente','os_cliente','data','hora_ini','hora_fim','hora_total','hora_trans');
            
        $rows = query("SELECT * FROM sdm_os WHERE 1=1 ".$texto." AND del != 'S' ");
        $usuario = getUsuario();
        if (is_array($rows) && count($rows) > 0)
            foreach ($rows as $r) {
                foreach ($col as $c)
                {
                    $temp[$c] = $r[$c];
                }
                
                $temp['editar']		= $r['user'] == $usuario ? true : false;
                
                $rows1 = query("SELECT nreduz FROM cad_organizacoes WHERE cod = '" . $temp['os_cliente'] . "' and ativo != 'N' ");
                if (is_array($rows1) && count($rows1) > 0) {
                    $temp['os_cliente'] = $rows1[0]['nreduz'];

                    $rows2 = query("SELECT apelido FROM sys001 WHERE user = '" . $temp['user'] . "'");
                    if (is_array($rows2) && count($rows2) > 0)
                    {
                        $temp['user'] = $rows2[0]['apelido'];
                    }

                    $temp['id'] = str_pad($temp['id'], 6, "0", STR_PAD_LEFT);
                    if (isset($temp['data']))
                    {
                        $temp['data'] = datas::dataS2D($temp['data']);
                    }

                    $ret[] = $temp;
                }
            }
        return $ret;
	}
	
	private function validaEdita($id){
	    $ret = false;
	    $row = query("SELECT user FROM sdm_os WHERE id = $id");
	    if(is_array($row) && count($row)>0){
	        if(getUsuario()==$row[0]['user'])
	        {
	            $ret = true;
	        }
	    }
        return $ret;
	}
		
	static function listaAnalista(){
	    $ret = array(array('',''));
	    $row = query("SELECT DISTINCT usuario, apelido, nome FROM sdm_recursos WHERE 1=1 ORDER BY nome");
	    if(is_array($row) && count($row) > 0)
	    {
	        foreach($row as $r){
	            if(!empty($r['apelido'])){
    	            $temp = array($r['usuario'],$r['apelido']);
    	            $ret[]=$temp;
	            }
	        }
	    }
	    return $ret;
	}
	
	static function listaCliente(){
	    $ret = array(array('',''));
	    $row = query("SELECT DISTINCT cod,nreduz FROM cad_organizacoes WHERE  ativo = 'S' ORDER BY nreduz");
	    if(is_array($row)&&count($row)>0){
    	        foreach($row as $r){
    	            $temp = array($r['cod'],$r['nreduz']);
    	            $ret[]=$temp;
    	    }
	    }
	    return $ret;
	}
	
	private function listaClienteTab(){
	    $ret = array(array('',''));
	    $row = query("SELECT DISTINCT cod,nreduz FROM cad_organizacoes WHERE  ativo = 'S' ORDER BY nreduz");
	    if(is_array($row)&&count($row)>0){
	        foreach($row as $r){
	            $temp = array($r['cod'],$r['nreduz']);
	            $ret[]=$temp;
	        }
	    }
	    return $ret;
	}
	
	private function listaProjetos(){
	    $ret = array(array('',''));
	    $row = query("SELECT titulo FROM os_osoffice_projeto WHERE del = 'N' ORDER BY titulo");
	    if(is_array($row) && count($row)>0){
	        foreach ($row as $r){
	            $temp = array();
	            $temp[] = $r['titulo'];
	            $temp[] = $r['titulo'];
	            $ret[] = $temp;
	        }
	    }
	    return $ret;
	}
	
	private function criarListaProjetos($cliente = ''){
	    $ret = [];
	    
	    $ret[] = ['', ''];
	    if(!empty($cliente)){
	        $sql = "select * from sdm_projetos where cliente = '$cliente'";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            foreach ($rows as $row){
	                $ret[] = [$row['id'], $row['titulo']];
	            }
	        }
	    }
	    return $ret;
	}
		
	private function array_key_first(array $arr) {
	    foreach($arr as $key => $unused) {
	        return $key;
	    }
	    return NULL;
	}
	
	private function getNomeCliente($cliente){
	    $ret = '';
	    $sql = "SELECT DISTINCT nreduz FROM cad_organizacoes WHERE ativo = 'S' and cod = '$cliente'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = $rows[0]['nreduz'];
	    }
	    return $ret;
	}
	
	private function getDadosOS($id, $lis= [], $agenda = 0){ 
	    $ret = ['os' => [], 'tarefa' => []];
	    $col = ['id', 'os_cliente','modulo','projeto','data','restricao','pessoa','hora_ini','hora_fim','hora_add','hora_sub','hora_trans','hora_total','observacao','obs_int'];
	    if($id!=0){
	        $rows = query("SELECT * FROM sdm_os WHERE id = $id ");
	        if (is_array($rows) && count($rows) > 0) {
	            foreach ($col as $c){
	                $ret['os'][$c] = $rows[0][$c];
	            }
	            
	            $rows = query("SELECT * FROM sdm_os_itens WHERE os = $id AND excluida != 'S'");
	            if (is_array($rows) && count($rows) > 0)
	            {
	                $ret['tarefa'] = $rows;
	            }
	        }
	    }else{
	        foreach ($col as $c){
	            $ret['os'][$c] = '';
	        }
	        
	        if(!empty($lis)){
	            $rows = query("SELECT cliente FROM sdm_tarefas WHERE id = ".$this->array_key_first($lis));
	            if (is_array($rows) && count($rows) > 0){
	                $ret['os']['os_cliente'] = $rows[0]['cliente'];
	            }	            
	        }
	        
	        if($agenda != '0'){
	            $sql = "select * from sdm_agenda where id = '" . base64_decode($agenda) . "'";
	            $rows = query($sql);
	            if(is_array($rows) && count($rows) > 0){
	                $ret['os']['data'] = $rows[0]['data'];
	                $ret['os']['os_cliente'] = $rows[0]['cliente_agenda'];
	            }
	        }
	    }
	    $ret['os']['data']=datas::dataS2D($ret['os']['data']);
	    return $ret;
	}
	
	private function getId(){
	    $ret = 1;
	    $rows = query("SELECT MAX(id) FROM sdm_os "); //where cliente = '".getCliente()."'");
	    if(is_array($rows)&&count($rows)>0){
	        $ret = $ret + $rows[0][0];
	    }
	    return $ret;
	}
	
	private function recuperarOS($dados){
	    $ret = '';
	    $where = array();
	    foreach ($dados as $campo => $valor){
	        $where[] = $campo . " = '$valor'";
	    }
	    if(count($where) > 0){
	    	$sql = "select id from sdm_os where 1=1 AND " . implode(' and ', $where);
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            $ret = $rows[0]['id'];
	        }
	    }
	    
	    return $ret;
	}
	
	private function gravarTarefas($tarefas, $id_os, $lista_sdm_os_tarefas){
	    if(is_array($tarefas) && count($tarefas) > 0){
	        $tarefas_texto = $tarefas['descricao'];
	        $tarefas_horas = $tarefas['horas'];
	        
	        $sql = "DELETE FROM sdm_os_itens WHERE os = $id_os ";
	        	query($sql);
	        
	        foreach ($tarefas_texto as $chave => $texto){
	            if($texto != '' ){
	            	$sql = "insert into sdm_os_itens (os, descricao, horas,cliente) values ($id_os, '$texto', '" . $tarefas_horas[$chave] . "', '".getCliente()."')";
					log::gravaLog('listar_os', $sql);
	                query($sql);
					if($lista_sdm_os_tarefas != ''){
						//$lista_sdm_os_tarefas = explode('|', $lista_sdm_os_tarefas);
					    $sql = "UPDATE sdm_tarefas SET os = $id_os WHERE id in (". str_replace('|', ', ', $lista_sdm_os_tarefas) . ')';
						log::gravaLog('listar_os', $sql);
						query($sql);
					}   
	            }
	        }
	    }
	}
	
	private function getTabelaTarefas($id_os,$lis_ids = array()){
	    $ret = '';
	    
	    $num_tarefas = 1;
	    
	    if($id_os != '0'){
	    	$sql = "SELECT * FROM sdm_os_itens WHERE os = $id_os AND excluida='N'";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            $num_tarefas += count($rows);
	        }
	    }
	    else if(!empty($lis_ids)){
	        $num_tarefas = count($lis_ids);
	    }
	    
	    $param = [];
	    $param['texto'] = 'Incluir Tarefa';
	    $param['onclick'] = "incluiRat($num_tarefas);";
	    $param['id'] = 'myInput';
	    $ret .= formbase01::formBotao($param);
	    
	    $param = [];
	    $param['paginacao'] = false;
	    $param['scroll'] 	= false;
	    $param['scrollX'] 	= false;
	    $param['scrollY'] 	= false;
	    $param['ordenacao'] = false;
	    $param['filtro']	= false;
	    $param['info']		= false;
	    $param['id']		= 'tabRatID';
	    $param['width']		= '100%';
	    $tab = new tabela01($param);
	    
	    
	    $tab->addColuna(array('campo' => 'horas'	    , 'etiqueta' => 'Tempo'		            , 'tipo' => 'V', 'width' => '5'  , 'posicao' => 'C'));
	    $tab->addColuna(array('campo' => 'descricao'	, 'etiqueta' => 'Descrição da Tarefa'	, 'tipo' => 'V', 'width' => '170', 'posicao' => 'E'));
	    $tab->addColuna(array('campo' => 'bt'			, 'etiqueta' => ''						, 'tipo' => 'V', 'width' => ' 50', 'posicao' => 'D'));
	    
	    
	    $dados = array();
	    if($id_os != 0){
	    	$sql = "SELECT * FROM sdm_os_itens WHERE os = $id_os  AND excluida='N'";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            foreach ($rows as $num => $row){
	                $temp = array();
	                $hora = $row['horas'];
	                $texto = $row['descricao'];
	                $temp['horas'] = "<input  type='text' name='formOS[tarefas][horas][]' value='$hora' style='width:100%;text-align: right;' id='" . ($num+1) . "tabelacampohora' class='form-control  form-control-sm'          >";
	                $temp['descricao'] = "<textarea name='formOS[tarefas][descricao][]' rows='' id='" . ($num+1) . "tabelacampotexto' class='form-control  form-control-sm' style='width:100%;'>$texto</textarea>";
	                
	                $temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>";
	                
	                $dados[] = $temp;
	                $js = "$('#" . ($num+1) . "tabelacampohora')." . "mask('99:99',{reverse: true});";
	                addPortaljavaScript($js);
	            }
	        }
	    }else if(!empty($lis_ids)){
	        foreach($lis_ids as $i){
	            $sql = "SELECT * FROM sdm_tarefas WHERE id = $i ";
	            $rows = query($sql);
	            if(is_array($rows) && count($rows) > 0){
	                foreach ($rows as $num => $row){
	                    $temp = array();
	                    $hora = $row['tempo'];
	                    $texto = $row['descricao'];
	                    $temp['horas'] = "<input type='text' name='formOS[tarefas][horas][]' value='$hora' style='width:100%;text-align: right;' id='" . ($num+1) . "tabelacampohora' class='form-control  form-control-sm'          >";
	                    $temp['descricao'] = "<input type='text' name='formOS[tarefas][descricao][]' value='$texto' style='width:100%;' id='" . ($num+1) . "tabelacampotexto' class='form-control  form-control-sm'          >";
	                    
	                    $temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>";
	                    
	                    $dados[] = $temp;
	                }
	            }
	        }
	    }
	    $tab->setDados($dados);
	    $ret .= $tab;
	    
	    return $ret;
	}
	
	private function setTextoOS($dados,$modelo){
	    if(is_array($dados) && count($dados) > 0){
	        foreach ($dados as $campo => $valor){
	            $modelo = str_replace('@@' . $campo, $valor, $modelo);
	        }
	    }
	    return $modelo;
	}
	
	private function getTabelaTarefasPDF($id = '', $cliente = ''){
		global $config;
		$ret = '';
		if($id != '' && $cliente != ''){
			$sql = "select * from sdm_os_itens where os = $id ";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				$ret = '<table border="0" cellpadding="0" cellspacing="0" style="width:100%">
	<tbody>
		<tr>
			<td colspan="4"><img src="'.$config['raizS3'].'imagens/preto.gif" style="height:2px; width:700px" /></td>
		</tr>
		<tr>
			<td style="text-align:center;">Tarefa</td>
			<td colspan="3" style="text-align:center;">Descri&ccedil;&atilde;o Atividade</td>
		</tr>
		<tr>
			<td colspan="4" style="width:100%"><img src="'.$config['raizS3'].'imagens/preto.gif" style="height:1px; width:700px" /></td>
		</tr>';
				foreach($rows as $num => $row){
					$ret .= '<tr>';
					$ret .= '<td style="text-align:center;">' . ($num + 1) . '</td>';
					$ret .= '<td colspan="3" style="text-align:left;">' . nl2br($row['descricao']) . "</td>";
					$ret .= '</tr>';
					$ret .= '		<tr>
			<td colspan="4"><img src="'.$config['raizS3'].'imagens/preto.gif" style="height:1px; width:700px" /></td>
		</tr>';
				}
				$ret .= '<tr>
			<td colspan="4"><img src="'.$config['raizS3'].'imagens/preto.gif" style="height:1px; width:700px" /></td>
		</tr>
	</tbody>
</table>';
			}
		}
		return $ret;
	}
	
	private function getDadosPDF($id){
		$ret = array();
	//	$dados = $this->getDadosOS($id);
	//	$ret = $dados['os'];
		$dados = $this->getDadosOSModelo($id);
		$ret = $dados;
		$ret['lista_tarefas'] = $this->getTabelaTarefasPDF($id, getCliente());
		$ret['restricao'] = getTabelaDesc('000001',$ret['restricao']);
		return $ret; 
	}
	
	private function getDadosOSModelo($id){
	    $ret = array();
	    $col = array('id', 'user','os_cliente','modulo','projeto','data','restricao','pessoa','hora_ini','hora_fim','hora_add','hora_sub','hora_trans','hora_total','observacao','os_cliente');
	    if($id!=0){
	    	$rows = query("SELECT * FROM sdm_os WHERE id = $id ");
	        if (is_array($rows) && count($rows) > 0) {
	            foreach ($col as $c)
	            {
	                $ret[$c] = $rows[0][$c];
	            }
	        }
	            
	        $rows1 = query("SELECT nreduz,nome FROM cad_organizacoes WHERE cod = '" . $ret['os_cliente'] . "' and ativo != 'N' ");
            if (is_array($rows1) && count($rows1) > 0){
                $ret['cliente_nome'] = $rows1[0]['nome'];
            }            

            $rows2 = query("SELECT nome FROM sys001 WHERE user = '" . $ret['user'] . "'");
            if (is_array($rows2) && count($rows2) > 0){
                $ret['usuario_nome'] = $rows2[0]['nome'];
            }
            
            $ret['data']=datas::dataS2D($ret['data']);
            $ret['id'] = str_pad($ret['id'], 6, "0", STR_PAD_LEFT);
            
                
	    }
	    return $ret;
	}
	
	/**
	 * Verifica se existe tarefas associadas a esta OS e libera as mesmas para novo faturamento se existir
	 * 
	 * @param int $os - Nr da OS
	 */
	private function verificaTarefasExclusao($os){
		$tarefas = [];
	
		$sql = "SELECT tarefa FROM sdm_os_itens WHERE os = $os AND IFNULL(tarefa,'') <> '' ";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$tarefas[] = $row['tarefa'];
			}
		}
		
		if(count($tarefas) > 0){
			$sql = "UPDATE sdm_tarefas SET os = 0 WHERE id IN (".implode(',', $tarefas).")";
			query($sql);
		}
	}
	
	function jsConfirmaExclusao($titulo){
	    addPortaljavaScript('function confirmaExclusao(link,id){');
	    addPortaljavaScript('	if (confirm('.$titulo.')){');
	    addPortaljavaScript('		setLocation(link+id);');
	    addPortaljavaScript('	}');
	    addPortaljavaScript('}');
	}
	
	private function carregaModelo(){
		$ret = array();
		$cliente = getCliente();
		$sys = new sys020();
	    $ret = $sys->getParametros($this->_programa, 'Modelo_OS_' . $cliente);
		if(count($ret) == 0){
			$ret = $sys->getParametros($this->_programa, 'Modelo_OS');
		}
	//	var_dump($ret);
		//die();
		return $ret;
	}
	
	function addJqueryAjax(){
	    addPortaljavaScript("function callAjax(){");
	    
	    addPortaljavaScript("  var cliente = document.getElementById('cliente').value;");
	    addPortaljavaScript("  var option = '';
    $.getJSON('" . getLinkAjax('projeto', true) . "ajax&cliente=' + cliente, function (dados){
        if (dados.length > 0){
            $.each(dados, function(i, obj){
		      option += '<option value=" . '"' . "'+obj.valor+'" . '"' . ">'+obj.etiqueta+'</option>';
                $('#projeto').html(option).show();
            });
        }
    })
}");
	}
}

function addJS_ListaOS(){
    $ret = '';
    
    $ret .= "
function excluirRat(e){
				var t = $('#tabRatID').DataTable();
				t.row( $(e).parents('tr') ).remove().draw();
    }
function incluiRat(valor){
				var t = $('#tabRatID').DataTable();
        
				var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";
        
				var hora = \"<input  type='text' name='formOS[tarefas][horas][]' value='' style='width:100%;text-align: right;' id='\"+valor+\"tabelacampohora' class='form-control  form-control-sm'          >\";
                var texto = \"<input  type='text' name='formOS[tarefas][descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
        
					t.row.add( [hora, texto, bt] ).draw( false );
                    $('#'+valor+'tabelacampohora').mask('99:99',{reverse: true});
        
                    valor = valor + 1;
                    $('#myInput').attr('onclick', 'incluiRat('+valor+');' );
}
function calculaHora(){
	hora1 = getHora(document.getElementById('hora_ini').value);
	hora2 = getHora(document.getElementById('hora_fim').value);
	hora3 = getHora(document.getElementById('hora_add').value);
	hora4 = getHora(document.getElementById('hora_sub').value);
	hora5 = getHora(document.getElementById('hora_trans').value);
        
	minuto1 = getMinuto(document.getElementById('hora_ini').value);
	minuto1 = 60 - minuto1;
	minuto2 = getMinuto(document.getElementById('hora_fim').value);
	minuto3 = getMinuto(document.getElementById('hora_add').value);
	minuto4 = getMinuto(document.getElementById('hora_sub').value);
	minuto5 = getMinuto(document.getElementById('hora_trans').value);
        
        
	minuto = minuto1+minuto2+minuto3 - minuto4+minuto5;
	hrmin = Math.floor(minuto / 60);
	minuto = minuto - hrmin * 60;
        
	hora = hora2 + hora3 - hora4 + hora5 + hrmin - hora1 -1;
        
	if (minuto == 0){ minuto = '00';}
	if (minuto < 10 && minuto != 0){ minuto = '0'+minuto;}
	if (hora < 1){ hora = '00';}
	if (hora < 10 && hora != 0){ hora = '0'+hora;}
        
//	if (hora1 == 0 || hora2 == 0){hora = '00'; minuto = '00';}
	if (hora2 == 0){hora = '00'; minuto = '00';}
	document.getElementById('hora_total').value = hora+':'+minuto;
	document.getElementById('hora_total_f').value = hora+':'+minuto;
        
        
	// Calcula total sem a hora de translado
	minutoT = minuto1+minuto2+minuto3 - minuto4;
	hrminT = Math.floor(minutoT / 60);
	minutoT = minutoT - hrminT * 60;
        
	horaT = hora2 + hora3 - hora4 + hrminT - hora1 -1;
        
	if (minutoT == 0){ minutoT = '00';}
	if (minutoT < 10 && minutoT != 0){ minutoT = '0'+minutoT;}
	if (horaT < 1){ horaT = '00';}
	if (horaT < 10 && horaT != 0){ horaT = '0'+horaT;}
	if (hora2 == 0){horaT = '00'; minutoT = '00';}
}
        
function getHora(horas){
 	dp = horas.indexOf(':');
	hora = horas.substr(0,dp);
	if (hora.length == 0)
		hora = '0';
	return(parseFloat(hora));
}
        
function getMinuto(horas){
 	dp = horas.indexOf(':');
	minuto = horas.substr(dp+1,horas.length-1);
	if (minuto.length == 0)
		minuto = '0';
	return(parseFloat(minuto));
}
        
function FormataHora(campo,teclapres) {
	var tecla = (window.Event) ? teclapres.which : teclapres.keyCode;
	vr = document.form[campo].value;
	vr = vr.replace( '.', '' );
	vr = vr.replace( ':', '' );
	vr = vr.replace( '/', '' );
	tam = vr.length + 1;
	if (tecla == 8 || tecla == 0 ){
		return true;
	}
    if (tam == 1){
		if (tecla < 48 || tecla > 50){
			return false;
		}
	}
	if (tam == 2){
		ant = vr.substr( 0, 1 );
		if (ant == '2' && (tecla < 48 || tecla > 51)){
			return false;
		}
	}
	if (tam == 3){
		if (tecla < 48 || tecla > 53){
			return false;
		}
	}
	if ( tecla != 9 && tecla != 8 ){
		if ( tam > 2 && tam < 5 )
			document.form[campo].value = vr.substr( 0, tam - 2  ) + ':' + vr.substr( tam - 2, tam );
			return true;
	}
	return false;
}

		";
    
    addPortaljavaScript($ret);
    
    return $ret;
}

