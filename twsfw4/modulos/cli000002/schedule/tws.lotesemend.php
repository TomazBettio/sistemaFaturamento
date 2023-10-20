<?php
/*
 * Data Criacao 15 de set de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: tws.loteSemEnd.inc.php
 * 
 * Descricao:
 * 
 * Alterções:
 *            19/11/2018 - Emanuel - Migração para intranet2
 *            30/01/2023 - Emanuel - Migração para intranet4
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class lotesemend{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Indica se é teste
	var $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = '000002.lotesemend';
		$this->_relatorio = new relatorio01($this->_programa,"");
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data At�'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){
	}
	
	private function montarColunasRelatorio($shedule = false){
	    $this->_relatorio->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'Cod'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'prod'	, 'etiqueta' => 'Produto'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'qt'		, 'etiqueta' => 'Quant.'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'lote'	, 'etiqueta' => 'Lote'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	}

	function schedule($param){
		$emails = str_replace(',', ';', $param);
		
		$this->montarColunasRelatorio(true);
		
		$titulo = 'Lotes sem Endereco. Data: '.date('d/m/Y');
		
		$this->_relatorio->setTitulo($titulo);
		$this->_dados = $this->getDados();
		
		$this->_relatorio->setDados($this->_dados);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true,'produtosSemEnderecos'.date('d.m.Y'));
		
		
		if(!$this->_teste){
		    $this->_relatorio->enviaEmail($emails,$titulo);
		    log::gravaLog('lotes_sem_endereco', "Email enviado para: $emails");
		}
		else{
		    //$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
		    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
		}
	}

	function getDados(){
	    $ret = array();
		$sql = "
				select 
				    pclote.codprod,
				    pcprodut.descricao,
				    pclote.qt,
				    pclote.numlote
				from 
				    pclote,
				    pcprodut
				where 
				    pclote.codprod = pcprodut.codprod (+)
				    and pclote.qt <> 0
				    and pclote.numlote not in (select numlote from PCESTENDERECO where PCESTENDERECO.codprod = pclote.codprod)
				    --and pclote.codprod in (:PRODUTOS)
				order by 
				    pclote.codprod,
				    pclote.qt
				";
		$rows = query4($sql);
//echo "$sql \n";
		
		if(is_array($rows) && count($rows) > 0){
			//$i = 0;
			foreach($rows as $row){
			    $temp = array();
			    $temp['cod']  = $row[0];
			    $temp['prod'] = $row[1];
			    $temp['qt']   = $row[2];
			    $temp['lote'] = $row[3];
			    $ret[] = $temp;
			    /*
				$this->_dados[$i]['cod'] 	= $row[0];
				$this->_dados[$i]['prod'] 	= $row[1];
				$this->_dados[$i]['qt'] 	= $row[2];
				$this->_dados[$i]['lote'] 	= $row[3];
				$i++;
				*/
			}
		}
		
		return $ret;
	}
	
}