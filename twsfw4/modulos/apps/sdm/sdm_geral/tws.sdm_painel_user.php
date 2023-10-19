<?php
/*
 * Data Criacao: 12/01/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Painel
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class sdm_painel_user{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	//Titulo
	private $_titulo;
	
	//Programa
	private $_programa;
	
	//Clientes não contabilizados
	private $_clientesNao = [];
	
	function __construct(){
		$this->_titulo ='Painel';
		$this->_programa = get_class($this);
		
		$this->getClientesNao();
		
	}
	
	function index(){
		$ret = '';
		
		$ret .= $this->mlinhaAgendas();
		
		return $ret;
	}
	
	
	//------------------------------------------------------------------------- UI ----------------------------
	
	private function mlinhaAgendas(){
		global $nl;
		$ret = '';
		$falta = $this->getAgendasSemOS();
		
		$ret .= '<div class="row">'.$nl;
		
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = $falta[1];
		$param['valor'] = $falta[0];
		$param['medida'] = '';
		$param['texto'] = 'Agenda sem OS';
		$param['icone'] = 'fa-plus-square';
		$param['footer'] = 'Detalhes';
		$param['link'] = 'index.php?menu=sdm_agenda.sdm_minhaagenda.index.semOS';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$agendasMarcadas = app_sdm::getAgendasMarcadas(['somenteQuantidade' => true]);
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = 'bg-fuchsia';
		$param['valor'] = $agendasMarcadas;
		$param['medida'] = '';
		$param['texto'] = 'Agendas Marcadas';
		$param['icone'] = 'fa-calendar-check-o';
		$param['footer'] = 'Detalhes';
		$param['link'] = 'index.php?menu=sdm_agenda.sdm_minhaagenda.index';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$horasNoMes = $this->getHorasMes();
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = 'bg-purple';
		$param['valor'] = $horasNoMes;
		$param['medida'] = '';
		$param['texto'] = 'Horas no Mês';
		$param['icone'] = 'fa-clock-o';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'index.php?menu=sdm_os.listar_os.index.horasMes';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = 'bg-orange';
		$param['valor'] = 5;
		$param['medida'] = '';
		$param['texto'] = 'Aguarda Terceiros';
		$param['icone'] = 'fa-wrench';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'detalhes.000001';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		
		$param = [];
		$param['titulo'] = 'Agendas';
		$param['conteudo'] = $ret;
		
		$ret = addCard($param);
		return $ret;
	}
	
	//-------------------------------------------------------------------------------------- GET -------------------------------------
	private function getAgendasSemOS(){
		$ret = [];

		$ret[0] = app_sdm::getAgendasSemOS(['clientesFora' => true]);
		$ret[1] = 'bg-green';
		
		if($ret[0] > 0 && $ret[0] <= 3){
			$ret[1] = 'bg-yellow';
		}elseif($ret > 3){
			$ret[1] = 'bg-red';
		}
		
		return $ret;
	}
	
	private function getHorasMes(){
		$ret = '0/0';
		$horaUsuario = '00';
		$horaTotal = '00';
		$usuario = getUsuario();
		
		$dtIni = date('Ym').'01';
		$dtFim = date('Ymt',mktime(0,0,0,date('m'),15,date('Y')));
		
		$sql = "SELECT user, hora_total FROM sdm_os WHERE data >= '$dtIni' AND data <= '$dtFim' AND IFNULL(del,'') <> 'S' ";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$horaTotal = datas::somaTempo($horaTotal, $row['hora_total']);
				if($row['user'] == $usuario){
					$horaUsuario = datas::somaTempo($horaUsuario, $row['hora_total']);
				}
			}
		}
		
		if(!empty($horaTotal)){
			$horaUsuario = substr($horaUsuario, 0, 2).'hs';
			$horaTotal = substr($horaTotal, 0, 2).'hs';
			$ret = $horaUsuario.' / '.$horaTotal;
		}
		
		return $ret;
	}
	
	//-------------------------------------------------------------------------------------- SET -------------------------------------
	
	//-------------------------------------------------------------------------------------- ADD -------------------------------------
	
	//-------------------------------------------------------------------------------------- VO  -------------------------------------
	
	//-------------------------------------------------------------------------------------- UTEIS -----------------------------------
	
	/**
	 * Clientes que não devem ser contados para verificar agendas abertas
	 */
	private function getClientesNao(){
		$this->_clientesNao[] = '900001'; // 
		$this->_clientesNao[] = '900002'; //
		$this->_clientesNao[] = '900003'; //
	}
	
	
}

