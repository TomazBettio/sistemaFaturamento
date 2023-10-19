<?php
/*
 * Data Criacao: 30/08/18
 * Autor: Thiel
 *
 * Descricao: CRUD na tabela sys020 - Parãmetros de programas
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class sys020{
	
	//Campos do SYS020
	private $_camposSYS020;
	
	//Campos do SYS021
	private $_camposSYS021;
	
	function __construct(){
		$this->_camposSYS020 = ['id','programa','parametro','seq','tipo','config','descricao','linhas','opcoes','help','ativo'];
		
		$this->_camposSYS021 = ['id','programa','parametro','versao','valor'];
	}
	
	//---------------------------------------------------------------------------------- GET -------------------------------------------------
	function getParametros($programa, $parametro = ''){
		$ret = [];
		$whereParametro = '';
		if(!empty($parametro)){
			$whereParametro = " AND parametro = '$parametro'";
		}
		
		$sql = "SELECT
					sys020.*,
					sys021.*	
				FROM 
					sys020 
						JOIN sys021 using (id,programa,parametro) 
				WHERE
					sys020.programa = '$programa' 
					AND sys021.ativo = 'S' 
					$whereParametro 
				ORDER BY 
					sys020.seq, 
					sys021.versao";
		$rows = query($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				foreach ($this->_camposSYS020 as $campo){
					$temp[$campo] = $row[$campo];
				}
				foreach ($this->_camposSYS021 as $campo){
					$temp[$campo] = $row[$campo];
				}
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
}