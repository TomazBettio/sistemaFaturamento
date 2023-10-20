<?php
/*
* Data Cria��o: 07/12/2020
* Autor: Thiel
*
* Arquivo: tws.est_hypera.inc.php
* 
* Alterções:
*           
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class est_hypera{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);


	private $_relatorio;
	
	//Codigo dos fornecedores
	private $_fornecedores;
	
	// Classe de integração
	private $_integra;
	
	//Nome arquivo
	private $_arquivo;
	
	
	function __construct(){
		set_time_limit(0);
		
		$this->_fornecedores = '1123';
		$this->_arquivo = 'ESTOQUE_GAUCHAFARMA.txt';
	}			
	
	function index(){
		$this->schedule();
	}

	function schedule($param = []){
		global $config;
		global $config;
		
		$this->_integra = new integra_txt('',1,'','', true);
		$this->_integra->setArquivo($this->_arquivo);
		$this->_integra->setDiretorio($config['tempUPD']);
		
		$this->setaEstrutura();

		$linhas = $this->getDados();
		
		$this->setRegistro1();
		$this->setRegistro2($linhas);
		
		$this->transfereArquivo();

	}
	
	private function transfereArquivo(){
		global $config;
		
		$orig = $config['tempUPD'].$this->_arquivo;
		$dest1 = "/mnt/pedtemp/CLOSEUP-HYPERA/ESTOQUE/".$this->_arquivo;
		$dest2 = "/mnt/pedtemp/CLOSEUP-HYPERA/BACKUP/".$this->_arquivo.'.'.date('Ymd');
		
		if(!copy($orig, $dest1)){
			log::gravaLog("estoque_Hypera", "Erro transferido: ".$dest1);
		}else{
			log::gravaLog("estoque_Hypera", "Tranferido arquivo para: ".$dest1);
		}

// 07.02.22 - O diretório de bkp foi excluído 
//		if(!copy($orig, $dest2)){
//			log::gravaLog("estoque_Hypera", "Erro transferido: ".$dest2);
//		}else{
//			log::gravaLog("estoque_Hypera", "Tranferido arquivo para: ".$dest2);
//		}
	}
	
	private function setRegistro1(){
		$reg = array();
		$reg[0]['tipo'] = '1';
		$reg[0]['cnpj'] = '89735070000100';
		$reg[0]['data'] = date('dmY');
		$reg[0]['hora'] = date('Hi');
		
		$this->_integra->gravaArquivo($reg,'1');
		log::gravaLog("estoque_Hypera", "Gerado arquivo Registro 1 - ".$this->_arquivo);
	}
	
	private function setRegistro2($estoque){
		$this->_integra->gravaArquivo($estoque,'2');
		log::gravaLog("estoque_Hypera", "Gerado arquivo Registro 2 - ".$this->_arquivo);
	}

	private function getDados(){
		$ret = array();
		$sql = "SELECT P.CODAUXILIAR, (E.QTESTGER - E.QTBLOQUEADA) ESTOQUE
				FROM PCPRODUT P
				    LEFT OUTER JOIN PCEST E
				        ON P.CODPROD = E.CODPROD AND E.CODFILIAL = 1
				WHERE CODFORNEC IN (".$this->_fornecedores.")
					AND DTEXCLUSAO IS NULL
					AND (E.QTESTGER - E.QTBLOQUEADA) > 0
							";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach($rows as $row){
			    if($row['CODAUXILIAR'] > '7000000000000'){
			         if($row['ESTOQUE']<0){
			         	$row['ESTOQUE'] = 0;
			         }
			         $temp = [];
			         $temp['tipo'] = '2';
			         $temp['ean'] = $row['CODAUXILIAR'];
			         $temp['quant'] = $row['ESTOQUE'];
			         $ret[] = $temp;
			    }
			}
		}
		return $ret;	
	}
	
	private function setaEstrutura(){
		$param = array();
		$param['var'] = array("cnpj"	,"data"	,"hora");
		$param['pos'] = array(2	,16	,24);
		$param['tam'] = array(14	,8	,4);
		$param['preencher'] = array(' ',' ',' ');
		$param['alin'] = array('E','E','E');
		
		$this->_integra->setEstrutura($param,'1');
		
		$param = array();
		$param['var'] = array('ean'	,'quant');
		$param['pos'] = array(2	,15);
		$param['tam'] = array(13,10);
		$param['preencher'] = array(' ',' ');
		$param['alin'] = array('E','D');
		
		$this->_integra->setEstrutura($param,'2');
	}
}
