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
		
		$sql = "SELECT cod, $campo FROM cad_organizacoes WHERE ativo = 'S' ORDER BY $campo";
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
		
		$sql = "SELECT $campo FROM cad_organizacoes WHERE id = $id";
		$rows = query($sql);
		
		if(isset($rows[0][$campo])){
			$ret = $rows[0][$campo];
		}
		return $ret;
	}
	
	static function getClienteCampoByCodigo($codigo, $campo = 'nome'){
	    $ret = '';
	    
	    $sql = "SELECT $campo FROM cad_organizacoes WHERE cod = '$codigo'";
	    $rows = query($sql);
	    
	    if(isset($rows[0][$campo])){
	        $ret = $rows[0][$campo];
	    }
	    return $ret;
	}
	
	//------------------------------------------------------------------------------------------ Recursos ---------------------
	
	static function getListaRecursos($param = []){
		$ret = array();
		
		$campo = $param['campo'] ?? 'apelido';
		$branco = $param['branco'] ?? true;
		$agenda = $param['agenda'] ?? 'S';
		$ativo = $param['ativo'] ?? 'S';
		
		if($branco){
			$ret[0][0] = "";
			$ret[0][1] = "&nbsp;";
		}
		
		$where = '';
		if($agenda == 'S' || $agenda == 'N'){
			$where .= "agenda = '$agenda'";
		}
		
		if($ativo == 'S' || $ativo == 'N'){
			if(!empty($where)){
				$where .= " AND ";
			}
			$where .= "ativo = '$ativo'";
		}
		
		
		if(empty($where)){
			$where = '1=1';
		}
		
		$sql = "SELECT usuario, $campo FROM sdm_recursos WHERE $where AND IFNULL(del, ' ') <> '*' ORDER BY $campo";
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
		
		$sql = "SELECT $campo FROM sdm_recursos WHERE usuario = '$usuario'";
		$rows = query($sql);
		
		if(isset($rows[0][$campo])){
			$ret = $rows[0][$campo];
		}
		return $ret;
	}

}