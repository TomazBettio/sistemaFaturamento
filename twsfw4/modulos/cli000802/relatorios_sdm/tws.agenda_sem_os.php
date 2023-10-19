<?php
/*
 * Data Criacao: 13/04/2022
 * Autor: Alexandre Thiel
 *
 * Descricao: Relatório de agendas que não possuem OS
 *
 * Alterações:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',0);
ini_set('display_startup_erros',0);
//error_reporting(E_ALL);

class agenda_sem_os{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	//Classe relatorio
	private $_relatorio;
	
	//Nome do programa
	private $_programa;
	
	//Titulo do relatorio
	private $_titulo;
	
	//Indica que se é teste (não envia email se for)
	private $_teste;
	
	//Dados
	private $_dados;
	
	public function __construct(){
		$this->_programa = get_class($this);
		$this->_titulo = 'Agendas sem OS';
		
		$this->_teste = false;
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->setPdfStripe(true);
		
		 ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Recurso'		    , 'variavel' => 'RECURSO'   ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'app_sdm::getRecursosLista()'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		 ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Tipo'		   		, 'variavel' => 'TIPO'    	,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'A=Analítico;S=Sintético'));
	}
	
	public function index(){
		
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$recurso 	= $filtro['RECURSO'] ?? '';
		$tipo 		= $filtro['TIPO'] ?? 'S';
		
		//$this->_relatorio->setTitulo("");
		
		if(!$this->_relatorio->getPrimeira()){
			$this->getDados($tipo, $recurso);
			$this->montaColunas($tipo);
			
			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
			$this->_relatorio->setToPDF(true);
		}else{
			$this->montaColunas();
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	public function schedule($param = ''){
		$this->ajustaOSs();
		$dados = [];
		$recursos = [];
		$this->montaColunas('A', true);
		$this->getDados('A');
		$this->_relatorio->setToExcel(false);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setTitulo('Agendas sem OS. '.date('d.m.Y'));
		
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $d){
				$rec = $d['recurso'];
				
				$dados[$rec][] = $d;
				if(!isset($recursos[$rec])){
					$recursos[$rec] = $rec;
				}
			}
			
			foreach ($dados as $rec => $d){
				$this->_relatorio->setDados($d);
				$this->_relatorio->setFooter('Voce tem '.count($d).' agendas sem OS. Por favor inclua as mesmas ou solicite o cancelamento da agenda a quem a marcou.<br>');
				if($this->_teste){
					$this->_relatorio->enviaEmail('suporte@thielws.com.br');
					log::gravaLog('agenda_sem_os', 'Email teste enviado');
				}else{
					$email = getUsuario('email', $rec);
					if(!empty($email)){
						$this->_relatorio->enviaEmail($email);
						log::gravaLog('agenda_sem_os', 'Email enviado: '.$rec.' - '.$email);
					}else{
						log::gravaLog('agenda_sem_os', 'Recurso sem email: '.$rec);
					}
				}
				
			}
		}
//print_r($this->_dados);
	}
	
	private function montaColunas($tipo = 'S', $schedule = false){
		
		if($tipo == 'S'){
			$this->_relatorio->addColuna(array('campo' => 'recurso'	, 'etiqueta' => 'Recurso'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'agendas'	, 'etiqueta' => 'Qt Agendas'	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'C'));
			//$this->_relatorio->addColuna(array('campo' => ''	, 'etiqueta' => ''					, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			//$this->_relatorio->addColuna(array('campo' => ''	, 'etiqueta' => ''					, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
			//$this->_relatorio->addColuna(array('campo' => ''	, 'etiqueta' => ''					, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			//$this->_relatorio->addColuna(array('campo' => ''	, 'etiqueta' => ''					, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		}elseif($tipo == 'S2'){
			$this->_relatorio->addColuna(array('campo' => 'recurso'	, 'etiqueta' => 'Recurso'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'agenda1'	, 'etiqueta' => 'QT Outros'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'C'));
			$this->_relatorio->addColuna(array('campo' => 'agenda2'	, 'etiqueta' => 'QT Minha'		, 'tipo' => 'N', 'width' => 250, 'posicao' => 'C'));
			$this->_relatorio->addColuna(array('campo' => 'agendas'	, 'etiqueta' => 'Qt Total'		, 'tipo' => 'N', 'width' => 100, 'posicao' => 'C'));
		}else{
			if(!$schedule){
				$this->_relatorio->addColuna(array('campo' => 'recurso'	, 'etiqueta' => 'Recurso'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			}
			$this->_relatorio->addColuna(array('campo' => 'data'	, 'etiqueta' => 'Data'			, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'C'));
			$this->_relatorio->addColuna(array('campo' => 'turno'	, 'etiqueta' => 'Turno'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
			$this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'coord'	, 'etiqueta' => 'Marcado por'	, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
			
		}
	}
	
	private function getDados($tipo = 'S', $recurso = ''){
		$this->_dados = [];
		
		
		$where = "";
		if($recurso !== ''){
			$where = "and recurso = '$recurso'";
		}
		if($tipo == 'S'){
			$sql = "SELECT recurso, COUNT(*) agendas FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' AND data < '".date('Ymd')."'  AND cliente_agenda NOT IN (".getParametroSistema('sdm_clientes_sem_os').") $where GROUP BY recurso";
		}elseif($tipo == 'S2'){
			
		}else{
			$sql = "SELECT recurso, data, turno, cliente_agenda, marcado_por FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' AND data < '".date('Ymd')."'  AND cliente_agenda NOT IN (".getParametroSistema('sdm_clientes_sem_os').") $where";
		}
		
		
		$rows = query($sql);

		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['recurso'] = getUsuario('nome',$row['recurso']);
				if($tipo == 'S'){
					$temp['agendas'] = $row['agendas'];
				}elseif($tipo == 'S2'){
					
				}else{
					$temp['data'] 	= $row['data'];
					$temp['turno'] 	= $row['turno'];
					$temp['cliente']= app_sdm::getClienteCampo($row['cliente_agenda']);
					$temp['coord'] 	= getUsuario('nome',$row['marcado_por']);
					$temp['marcado']= $row['marcado_por'];
					$temp['recurso']= $row['recurso'];
				}
		
				$this->_dados[] = $temp;
			}
		}
	}
	
	/**
	 * Realiza a amarração de Agendas x OS
	 */
	private function ajustaOSs(){
		$agendas = $this->getAgendasSemOS();
		
		if(count($agendas) > 0){
			foreach ($agendas as $agenda){
				$sql = "SELECT id FROM sdm_os WHERE IFNULL(del,'N') = 'N' AND user = '".$agenda['recurso']."' AND data = '".$agenda['data']."' AND os_cliente = '".$agenda['cliente']."'";
				$rows = query($sql);
				if(isset($rows[0][0])){
					$this->amarraAgenda($agenda['id'], $rows[0][0]);
				}
				
			}
		}
	}
	
	private function amarraAgenda($agenda, $os){
		$sql = "UPDATE sdm_agenda SET os = $os WHERE id = $agenda";
		query($sql);
	}
	
	private function getAgendasSemOS(){
		$ret = [];
		
		$sql = "SELECT id, cliente_agenda, recurso, data FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = ''  AND data < '".date('Ymd')."'";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			foreach ($rows as $row){
				$temp = [];
				$temp['id'] 	 = $row['id'];
				$temp['data'] 	 = $row['data'];
				$temp['recurso'] = $row['recurso'];
				$temp['cliente'] = $row['cliente_agenda'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
}