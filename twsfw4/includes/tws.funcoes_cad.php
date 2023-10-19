<?php
/*
 * Data Criacao 26/01/2022
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class funcoes_cad{
	
	//------------------------------------------------------------------------------------------ Clientes ---------------------
	
	static function getListaClientes($campo = 'nreduz',$branco = true){
		$ret = array();
		
		if($branco){
			$ret[0][0] = "";
			$ret[0][1] = "&nbsp;";
		}
		
		$sql = "SELECT cod, $campo FROM cad_clientes WHERE ativo = 'S' ORDER BY $campo";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$temp[0] = $row['cod'];
				$temp[1] = $row[$campo];
				
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	static function getClienteCampo($id, $campo = 'nome'){
		$ret = '';
		
		$sql = "SELECT $campo FROM cad_clientes WHERE id = $id";
		$rows = query($sql);
		
		if(isset($rows[0][$campo])){
			$ret = $rows[0][$campo];
		}
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------ Recursos ---------------------
	
	static function getListaRecursos($campo = 'apelido',$branco = true){
		$ret = array();
		
		if($branco){
			$ret[0][0] = "";
			$ret[0][1] = "&nbsp;";
		}
		
		$sql = "SELECT usuario, $campo FROM cad_recursos WHERE agenda = 'S'  ORDER BY $campo";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$temp[0] = $row['usuario'];
				$temp[1] = $row[$campo];
				
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	static function getRecursoCampo($usuario, $campo = 'nome'){
		$ret = '';
		
		$sql = "SELECT $campo FROM cad_recursos WHERE usuario = '$usuario'";
		$rows = query($sql);
		
		if(isset($rows[0][$campo])){
			$ret = $rows[0][$campo];
		}
		return $ret;
	}

}