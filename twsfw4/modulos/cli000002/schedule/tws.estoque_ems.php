<?php
/*
* Data Cria��o: 30/10/2013 - 15:54:20
* Autor: Thiel
*
* Arquivo: tws.estoque_ems.inc.php
* 
* Alterções:
*           09/01/2019 - Emanuel - Migração para intranet2
*           30/01/2023 - Emanuel - Migração para intranet4
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class estoque_ems{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	private $_teste = false;

	function __construct(){
		set_time_limit(0);
	}			
	
	function index(){
	}

	function schedule($param){
		global $config;
echo "Estoque EMS <br>";	
		$linhas = $this->getDados();
//print_r($linhas);
		$arquivo = 'estems 89735070000100.txt';
		echo $config['temp'].$arquivo;
		$file = fopen($config['temp'].$arquivo, "w");
		if(!$file){
		    die("não foi possivel editar o arquivo");
		}
		
		if(count($linhas) > 0){
			foreach ($linhas as $linha){
				fwrite($file, $linha."\n");
			}
		}
		fclose($file);
		
		if(!$this->_teste){
    		$servidor = "10.0.0.107";
    		$diretorio = "estoque";
    		$usuario = "EMS";
    		$senha = "g0gJ9CpA";
    
    		$conn_id = ftp_connect($servidor);
    		if(!$conn_id){
    			log::gravaLog("estoqueEMS", "Nao foi possivel conectar ao servidor FTP ".$servidor);
    			die("Nao foi possivel conectar a $servidor"); 
    		}
    	
    		$fp = fopen($config['temp'].$arquivo, 'r');
    		if(!$fp){
    		    ftp_close($conn_id);
    		    die("não foi possivel abrir o arquivo");
    		}
    		$login_result = ftp_login($conn_id, $usuario, $senha);
            
    		if (ftp_fput($conn_id, 'estoque/'.$arquivo, $fp, FTP_ASCII)) {
    		    log::gravaLog("estoqueEMS", "Enviado com sucesso arquivo para o FTP");
    		} else {
    		    log::gravaLog("estoqueEMS", "Erro ao enviar arquivo para o FTP");
    		}
    		
    		ftp_close($conn_id);
    		fclose($fp);
		}
	}

	private function getDados(){
		$ret = array();
		$var = 0;
		$sql = "select '80', p.codauxiliar, (e.qtestger - e.qtbloqueada) ESTOQUE
				from pcprodut p
				    left outer join pcest e
				        on p.codprod = e.codprod and e.codfilial = 1
				where codfornec in (1086,949,1132,1099, 15287, 18809)
					and dtexclusao is NULL
							";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach($rows as $row){
			    if($row[1] > '7000000000000'){
			         if($row[2]<0){
			             $row[2] = 0;
			         }
			         $row[2] = (int)$row[2];
			         $ret[] = $row[0].formataNum($row[1],13).formataNum((int)$row[2],9);
			    }
			}
		}
		return $ret;	
	}
}
