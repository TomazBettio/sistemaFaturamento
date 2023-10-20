<?php
/*
* Data Cria��o: 30/10/2013 - 15:54:20
* Autor: Thiel
*
* Arquivo: class.estoqueEMS.inc.php
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class est_teuto{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);

	function __construct(){
		set_time_limit(0);
	}			
	
	function index(){
	}

	function schedule($param){
		global $config;
		$emails = $param;
		
echo "Estoque TEUTO <br>";
		$linhas = $this->getDados();
//print_r($linhas);
		$data = date('Ymd');
		$dataCont = date('dmY');
		$hora = date('His');
		$arquivo = 'EST'.$data.$hora.'.txt';
		$arqivoTemp = $config['temp'].$arquivo;
		$file = fopen($arqivoTemp, "w");
		if ($file == false){
		    die("erro ao abrir o arquivo");
		}
		fwrite($file, '00;89735070000100;'.$dataCont.';'.$hora."\n");
		
		if(count($linhas) > 0){
			foreach ($linhas as $linha){
				fwrite($file, $linha."\n");
			}
		}
		fclose($file);
		
		$dir 	= "/mnt/pedoltemp/teuto/estoque/";
		$dir2   = "/mnt/pedoltemp/focopdv/estoque/"; 
		$dir3   = "/mnt/pedtemp/FOCOPDV/ESTOQUE/"; //modificado por Emanuel a pedido do Gustavo em 28/10/22
		
	    log::gravaLog("estoqueTeuto", "Gerado arquivo $arquivo");
	    
	    if(!copy($arqivoTemp, $dir.$arquivo)){
	    	log::gravaLog("estoqueTeuto", "Erro transferido1: $dir $arquivo");
	    }
	    if(!copy($arqivoTemp, $dir2.$arquivo)){
	    	log::gravaLog("estoqueTeuto", "Erro transferido2: $dir2 $arquivo");
	    }
	    if(!copy($arqivoTemp, $dir3.$arquivo)){
	        log::gravaLog("estoqueTeuto", "Erro transferido3: $dir3 $arquivo");
	    }
	    
	    log::gravaLog("estoqueTeuto", "Arquivo transferido: $dir $arquivo");
	    log::gravaLog("estoqueTeuto", "Arquivo transferido: $dir2 $arquivo");
	    log::gravaLog("estoqueTeuto", "Arquivo transferido: $dir3 $arquivo");
	    
	    
	    //Envia email para o Gabriel
	    $param = [];
	    $param['destinatario'] = $emails.';suporte@thielws.com.br';
	    $param['mensagem'] = 'Arquivo anexo foi enviado para a Teuto';
	    $param['assunto'] = 'Estoque Teuto';
	    $param['anexos'][] = $arqivoTemp;
	    $param['programa'] = 'est_teuto';
	    enviaEmail($param);
	    
	    unset($arqivoTemp);

	}

	function getDados(){
		$ret = array();
		$sql = "select '09',p.codauxiliar, (e.qtestger - e.qtbloqueada) ESTOQUE
				from pcprodut p
				    left outer join pcest e
				        on p.codprod = e.codprod and e.codfilial = 1
				where codfornec in (119,16939,16944)
					and dtexclusao is NULL
					and p.codauxiliar > 7000000000000
							";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach($rows as $row){
					
				if($row[1] > '7000000000000'){
					if($row[2]<0){
						$row[2] = 0;
					}elseif($row[2] > 5000){
						$row[2] = 5000;
					}
					$ret[] = $row[0].';'.formataNum($row[1],13).';'.formataNum((int)$row[2],6);
				}
			}
		}
		
		return $ret;	
	}
}

