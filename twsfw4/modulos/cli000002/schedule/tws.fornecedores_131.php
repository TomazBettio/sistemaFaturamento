<?php
/*
* Data Criação: 28/10/2015 - 17:44:10
* Autor: Thiel
*
* Arquivo: tws.fornecedores_131.inc.php
* 
* Quando eh cadastrado um novo fornecedor no WinThor eh necessario entrar na rotina 131 e
* liberar o acesso dos usuarios do credito para ele (caso contrario nao se consegue ver as
* movimentacoes financeiras deste fornecedor)
* Esta rotina verifica os fornecedores cadastrados no dia e inclui a liberacao para os usuarios
* passados nos parametros
* 
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
class fornecedores_131{
	var $funcoes_publicas = array();
	
	function __construct(){
		set_time_limit(0);
	}
			
	function index(){
		
	}
	
	function schedule($param){
		$usuarios = $this->getUsuarios();
		$fornededores = $this->getForncedores();
		if(count($usuarios) > 0 && count($fornededores) > 0){
			foreach ($fornededores as $f){
				foreach ($usuarios as $u){
					$sql = "SELECT * FROM PCLIB WHERE CODTABELA = 3 AND CODFUNC = $u AND CODIGON = $f";
					$rows = query4($sql);
					if(count($rows) == 0){
						$sql = "INSERT INTO PCLIB (CODFUNC,CODTABELA,CODIGON,CODFUNC_LIB,DATA_LIB,CODIGOA) VALUES ($u,3,$f,68,SYSDATE,'  ')";
						query4($sql);
						//echo "$sql<br><br>\n";
						log::gravaLog("fornecedor_131", "Fornecedor: $f Usuario: $u");
					}
				}
			}
		}
	}
	
	function getForncedores(){
		$ret = array();
		$sql = "SELECT codfornec FROM pcfornec where dtcadastro > SYSDATE -15";
		$rows = query4($sql);
		if(count($rows) >0){
			foreach ($rows as $row){
				$ret[] = $row[0];
			}
		}
		return $ret;
	}
	
	function getUsuarios(){
		$ret = array();
		$sql = "select matricula from pcempr where situacao = 'A'";
		$rows = query4($sql);
		if(count($rows) >0){
			foreach ($rows as $row){
				$ret[] = $row[0];
			}
		}
		return $ret;
	}
}