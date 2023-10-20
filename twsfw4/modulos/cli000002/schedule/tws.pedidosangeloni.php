<?php
/*
* Data Criação: 19/11/2014 - 15:32:22
* Autor: Thiel
*
* Arquivo: tws.pedidosAngeloni.inc.php
* 
* 
* Alterções:
*           02/01/2019 - Emanuel - Migração para intranet2
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class pedidosAngeloni{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	var $_cnpjAngeloni;
	var $_cnpjWM;
	var $_cnpjCooper;
	
	var $_arquivosTemp = [];

	function __construct(){
		set_time_limit(0);
		
		$limpa = array('.','-','/');
		
		$sql = 'select pcclient.cgcent from pcclient where codrede = 51';
		$rows = query4($sql);
		foreach ($rows as $row){
			$this->_cnpjAngeloni[] = substr(str_replace($limpa, '', $row[0]),0,8);
		}
		$sql = 'select pcclient.cgcent from pcclient where codrede = 72';
		$rows = query4($sql);
		foreach ($rows as $row){
			$this->_cnpjWM[] = substr(str_replace($limpa, '', $row[0]),0,8);
		}
		$sql = 'select pcclient.cgcent from pcclient where codrede = 96';
		$rows = query4($sql);
		foreach ($rows as $row){
			$this->_cnpjCooper[] = substr(str_replace($limpa, '', $row[0]),0,8);
		}
		
	}			
	
	function index(){
	}

	function schedule($param){
		$cnpj = $this->getCNPJsAngeloni();
		$dir_pedidos 	= "/mnt/pedtemp/neogrid/neogridmercador/pedidos";
		$dir_angeloni 	= "/mnt/pedtemp/neogrid/angeloni/pedido";
		$dir_walmart 	= "/mnt/pedtemp/neogrid/walmart/pedido";
		$dir_cooper 	= "/mnt/pedtemp/neogrid/cooper/pedido";

	    if ($dh = opendir($dir_pedidos)) {
	        while (($file = readdir($dh)) !== false) {
	        	if($file != '.' && $file != '..'){
	        		$this->_arquivosTemp[] = $file;
	        		log::gravaLog('pedidosAngeloni', 'Arquivo: '.$file);
	        	}
	        }
	        closedir($dh);
	    }else{
	    	log::gravaLog('pedidosAngeloni', 'Não conseguiu abrir o diretorio');
	    }
	    	
	    
//$fp = fopen($dir_walmart.'data.123', 'w');
//fwrite($fp, '1safsdf dsfa sdf asd');
//fwrite($fp, '23 sdfsdf dsf sdf sdf sdf sdf sfd ');
//fclose($fp);
	    
	    
	    if(count($this->_arquivosTemp) > 0){
	    	foreach ($this->_arquivosTemp as $arquivo){
	    		$cnpj = substr($this->getCNPJarquivo($dir_pedidos,$arquivo),0,8);
	    		
	    		$pos = array_search($cnpj, $this->_cnpjAngeloni);
	    		if($pos !== false){
	    			echo "<br>Arquivo: $arquivo Angeloni - $cnpj <br>\n";
	    			$this->moveArquivo($dir_pedidos.'/'.$arquivo, $dir_angeloni.'/'.$arquivo);
	    		}
	    		$pos = array_search($cnpj, $this->_cnpjWM);
	    		if($pos !== false){
	    			echo "<br>Arquivo: $arquivo Walmart - $cnpj <br>\n";
	    			$this->moveArquivo($dir_pedidos.'/'.$arquivo, $dir_walmart.'/'.$arquivo);
	    		}
	    		$pos = array_search($cnpj, $this->_cnpjCooper);
	    		if($pos !== false){
	    			echo "<br>Arquivo: $arquivo Cooper - $cnpj <br>\n";
	    			$this->moveArquivo($dir_pedidos.'/'.$arquivo, $dir_cooper.'/'.$arquivo);
	    		}
	    	}
	    }
	}
	
	function moveArquivo($de,$para){
//		exec("cp $de $para");
		
//		rename($de,$para);
		if(copy($de,$para)){
			unlink($de);
		}
		log::gravaLog('pedidosAngeloni', "Arquivo movido: $de  -  $para");
	}
	
	function getCNPJarquivo($dir,$arquivo){
		$arq = file ($dir.'/'.$arquivo);
		$cnpj = $pedido = substr($arq[0], 180,14);
		
		return $cnpj;
	}

	function getCNPJsAngeloni(){
		$ret = array();
		
		$sql = "";
		$rows = query4($sql);
		
		foreach ($rows as $row){
			$ret[] = $row[0];
		}
		
		return $ret;
	}
}