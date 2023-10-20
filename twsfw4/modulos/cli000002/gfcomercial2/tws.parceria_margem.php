<?php
/*
 * Data Criação: 16/01/2015 - 12:13:57
 * Autor: Thiel
 *
 * Descrição: Enviado diariamente para os gestores. 
 * 			  As informações foram previamente calculadas pela rotina ora_parcerias2
 *            São as mesmas informações enviadas aos supervisores + ERCs + venda no período passado + margem
 *            
 * Modificações:
 *            24/07/19 - Emanuel - migração para intranet2
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class parceria_margem{
	var $funcoes_publicas = array(
//			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Campos tabela banco
	var $_campos;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = '000002.parceria';
		$this->_relatorio = new relatorio01($this->_programa,"");
		$this->_relatorio->addColuna(array('campo' => 'super'    , 'etiqueta' => 'Regiao'						, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'regiao'   , 'etiqueta' => 'Regiao Nome'					, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vend'     , 'etiqueta' => 'ERC'							, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc'      , 'etiqueta' => 'ERC Nome'						, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'acordo' 	 , 'etiqueta' => 'Acordo'						, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descricao'					, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'	 , 'etiqueta' => 'Clientes'						, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'dataini'  , 'etiqueta' => 'Data Ini'						, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'datafim'  , 'etiqueta' => 'Data Fim'						, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'tipo'     , 'etiqueta' => 'Tipo'							, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'objetivo' , 'etiqueta' => 'Objetivo'						, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'premio'   , 'etiqueta' => 'Premio'						, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'venda1'   , 'etiqueta' => 'Venda<br>Medicamento'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'venda12'  , 'etiqueta' => 'Venda<br>Nao Medic.'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaOL'  , 'etiqueta' => 'Venda<br>OL'					, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'venda'    , 'etiqueta' => 'Venda<br>Total'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'mediaR'   , 'etiqueta' => 'Media Real'					, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'mediaF'   , 'etiqueta' => 'Media Futur.'					, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'bonusG'   , 'etiqueta' => 'Bonus Gerado' 				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'bonusU'   , 'etiqueta' => 'Bonus Usado'					, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'obs'			, 'etiqueta' => 'OBS'						, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vendaAnt'	, 'etiqueta' => 'Venda Periodo<br>Anterior'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'margem'   	, 'etiqueta' => 'Margem'					, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
	}
	
	function index(){
	}
	
	function schedule($param){
		$emails = str_replace(',', ';', $param);
		$titulo = 'Contratos de Parceria Margem. Data: '.date('d/m/Y');
		
		$this->_relatorio->setTitulo($titulo);
		log::gravaLog("parcerias_margem", "$titulo, Selecionando registros...");
		$this->_campos = $this->_relatorio->getCampos();

		$dados = $this->getDados();
		
		$this->_relatorio->setDados($dados);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true,'Contratos_de_Parceria_Margem_'.date('d.m.Y'));
		if(!$this->_teste){
			$this->_relatorio->enviaEmail($emails,$titulo);
			log::gravaLog("parcerias", "Enviado email: ".$emails);
		}else{
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
		}
	}
	
	function getDados(){
		$ret = array();
		$hoje = datas::getDataDias();
		
		$sql = "SELECT * FROM gf_parcerias WHERE dataini <= '$hoje' AND datafim >= '$hoje' ORDER BY super, vend";
		$rows = query($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$acordo = $row['acordo'];
				$super = $row['super'];
				$erc = $row['vend'];
				
				$temp = array();
				foreach ($this->_campos as $campo){
					$key = $campo;
					if(array_search($key, array('dataini','datafim','premio')) === false){
						$temp[$key] = $row[$campo];
					}
					if($key == 'dataini' || $key == 'datafim'){
						$temp[$key] = datas::dataS2D($row[$campo]);
					}
					if($key == 'premio'){
						$premio = $row[$campo];
						if($premio < 100 && $premio > 0){
							$temp[$key] 	= $premio." %";
						}else{
							$temp[$key] 	= number_format($premio, 2, ',', '.');
						}
					}
				}
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
}