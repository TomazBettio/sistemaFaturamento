<?php
/*
 * Data Criacao: 28/12/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Agenda Pessoal
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class sdm_minhaagenda{
	var $funcoes_publicas = array(
			'index' 	=> true,
	);
	
	//Titulo
	private $_titulo;
	
	private $_programa;
	
	//Agendas Marcadas
	private $_agendas = [];
	
	//Nome clientes
	private $_clientes = [];
	
	//Dados recurso
	private $_recurso = [];
	
	private $_filtro;
	
	
	function __construct(){
		$this->_titulo ='Minha Agenda';
		$this->_programa = 'minhaagenda';
		
		
		$this->getRecurso();
		$this->getClientesNome();
		
		$paramFiltro = array();
		$paramFiltro['titulo'] = 'Filtros';
		$paramFiltro['tamanho'] = 12;
		$this->_filtro = new formFiltro01($this->_programa, $paramFiltro);
		
		if(true){
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente'	, 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sdm_agenda_clientes()'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
	}
	
	function index(){
		$ret = '';
		$operacao = getOperacao();
		
		$filtro  = $this->_filtro->getFiltro();
		addPortalJquery("$('#formFiltro').hide();");
		
		$diaIni = !empty($filtro['DATAINI']) ? $filtro['DATAINI'] : datas::getDataDias();
		$diaFim = !empty($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		$cliente = !empty($filtro['CLIENTE']) ? $filtro['CLIENTE'] : '';
		
		$this->getAgendas($diaIni, $diaFim, $cliente, $operacao);
		
		$ret .= $this->_filtro;
		$ret .= $this->montaTabela();
		
		$param = array();
		
		$param = [];
		$param['titulo'] = $this->_titulo;
		$param['conteudo'] = $ret;
		$botao = [];
		$botao['onclick']= "$('#formFiltro').toggle();";
		$botao['texto']	= 'Filtros';
		$botao['id'] 	= 'bt_form';
		$param['botoesTitulo'][] = $botao;
		$ret = addCard($param);
		
		$param = [];
		$param['clientesFora'] = true;
		$agendaSemOS = app_sdm::getAgendasSemOS(['clientesFora' => true]);
		
		return $ret;
	}
	
	
	//----------------------------------------------------------------- UI   ------------
	
	private function montaTabela(){
		$ret = '';
		
		$param = [];
		$tab = new tabela01($param, '');
		
		$tab->addColuna(array('campo' => 'bt'	, 'etiqueta' => 'OS'	,'tipo' => 'T', 'width' => 70, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'data'		, 'etiqueta' => 'Data'		,'tipo' => 'D', 'width' =>  70, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'turno'	, 'etiqueta' => 'Turno'		,'tipo' => 'T', 'width' =>  50, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'	,'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'projeto'	, 'etiqueta' => 'Projeto'	,'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'tarefa'	, 'etiqueta' => 'tarefa'	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		
		$tab->setCorLinha('conflito');
		$tab->setDados($this->_agendas);
		
		$ret .= $tab;
		
		return $ret;
	}
	
	//----------------------------------------------------------------- GETs ------------
	
	private function getRecurso(){
		$sql = "SELECT * FROM cad_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' AND usuario = '".getUsuario()."'";
		$rows = query($sql);
		
		if(isset($rows[0]['nome'])){
			$this->_recurso['nome'] 	= $rows[0]['nome'];
			$this->_recurso['apelido']	= $rows[0]['apelido'];
			$this->_recurso['tipo'] 	= $rows[0]['tipo'];
			$this->_recurso['recurso']	= $rows[0]['usuario'];
		}
	}
	
	private function getAgendas($diaIni, $diaFim, $cliente, $operacao){
		$this->_agendas = [];
		
		if($operacao == 'semOS'){
			$sql = "SELECT * FROM sdm_agenda WHERE TRIM(os) = '' AND recurso = '".getUsuario()."' AND IFNULL(del,'') = ''  AND cliente_agenda NOT IN (".getParametroSistema('sdm_clientes_sem_os').")";
		}else{
			$sql = "SELECT * FROM sdm_agenda WHERE data >= '$diaIni' AND recurso = '".getUsuario()."' AND IFNULL(del,'') = '' ";
			if(!empty($diaFim)){
			    $sql .= "AND data <= '$diaFim' ";
			}
			if(!empty($cliente)){
			    $sql .= "AND cliente_agenda = '$cliente' ";
			}
		}
		$sql .= " ORDER BY data";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = $row;
				$temp['tarefa'] 	= nl2br($temp['tarefa']);
				$temp['cliente'] 	= $this->_clientes[$row['cliente_agenda']];
				if($row['os'] == ''){
					$temp['bt'] 	= '<a href="index.php?menu=sdm_os.listar_os.editar&agenda='.base64_encode($row['id']).'" class="btn btn-xs btn-success" role="button" id="CRIAR">Criar OS</a>';
				}else{
					$temp['bt'] 	= '<button type="button" class="btn btn-xs btn-primary"  onclick="op2(' . "'index.php?menu=sdm_os.listar_os.geraPDF&agenda="  . base64_encode($row['id']) . "')" . '" id="VISUALIZAR">Visualizar OS</button>';
				}
				$temp['projeto'] 	= $this->getNomeProjeto($temp['projeto']);
				$temp['conflito'] 	= '';
				$temp['id']			= $row['id'];
				$this->_agendas[] 	= $temp;
			}
		}
		
		$this->verificaConflitos();
	}
	
	private function verificaConflitos(){
		if(count($this->_agendas) > 0){
			$marcadas = [];
			foreach ($this->_agendas as $key => $agenda){
				//$this->_agendas[$key]['conflito'] = $this->verificaConflitoDia($agenda['data'], $this->_recurso['recurso'], $agenda['turno']);
				$marcadas[$agenda['data']][$agenda['turno']][] = $key;
			}
			
			$datasConflito = [];
			foreach ($marcadas as $dia => $turno){
				if(count($turno) > 1){
					//Verifica se tem mais de uma agenda no mesmo turno
//echo "Dia: $dia - ".count($turno)."<br>\n";
					foreach ($turno as $turno => $t){
						if(count($t) > 1){
//echo "Turno: $turno - ".count($t)."<br>\n";
							foreach ($t as $id){
//echo "ID: $id <br>\n";	
								$datasConflito[$dia] = true;
//print_r($this->_agendas[$id]);
							}
						}
					}
				}
			}
			
			foreach ($marcadas as $dia => $turno){
				if(count($turno) > 1){
					$chaves = implode('', array_keys($turno));
					$I = strpos($chaves, 'I');
					$M = strpos($chaves, 'M');
					$T = strpos($chaves, 'T');
					$N = strpos($chaves, 'N');
					if(($I !== false && $M !== false) || ($I !== false && $T !== false)){
						$datasConflito[$dia] = true;
					}
				}
			}
			
			
			//Marca as datas com conflitos
			foreach ($marcadas as $dia => $turno){
				foreach ($turno as $turno => $t){
					foreach ($t as $id){
						$this->_agendas[$id]['conflito'] = isset($datasConflito[$dia]) ? 'danger' : '';
					}
				}
			}
//			print_r($marcadas);
		}
	}
	
	private function getClientesNome(){
		$sql = "SELECT cod, nreduz FROM cad_clientes ";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$this->_clientes[$row['cod']] = $row['nreduz'];
			}
		}
	}
	
	private function getNomeProjeto($id = ''){
	    $ret = '';
	    if($id != ''){
	        $sql = "select titulo from sdm_projetos where id = '$id'";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            $ret = $rows[0]['titulo'];
	        }
	    }
	    return $ret;
	}
	
	//----------------------------------------------------------------------------- UTEIS ------------------------------------------------------------
	
}

function sdm_agenda_clientes(){
    $ret = array();
    
    $ret[0][0] = "";
    $ret[0][1] = "&nbsp;";
    
    $sql = "SELECT cod, nreduz FROM cad_clientes WHERE ativo = 'S' ORDER BY nreduz";
    $rows = query($sql);
    
    if(is_array($rows) && count($rows) > 0){
        foreach ($rows as $row) {
            $temp[0] = $row['cod'];
            $temp[1] = $row['nreduz'];
            
            $ret[] = $temp;
        }
    }
    //print_r($tabela);
    return $ret;
}