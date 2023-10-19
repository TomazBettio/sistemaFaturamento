<?php
/*
 * Data Criacao: 12/04/2022
 * Autor: Thiel
 *
 * Descricao: Funções comuns no SDM
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class app_sdm{
	
	//-------------------------------------------------------------------------------------- UI --------------------------------------
	
	//-------------------------------------------------------------------------------------- GET -------------------------------------
	
	public static function getAgendasSemOS($param = []){
		$ret = 0;
		$where = '';

		$clienteFora = $param['clientesFora'] ?? true;
		if($clienteFora !== false){
			$where .= " AND cliente_agenda NOT IN (".getParametroSistema('sdm_clientes_sem_os').") ";
		}
		
		if(isset($param['usuario'])){
			$where .= " AND recurso = '".$param['usuario']."' ";
		}else{
			$where .= " AND recurso = '".getUsuario()."' ";
		}
		
		if(isset($param['data'])){
			$where .= " AND data < '".$param['data']."'";
		}else{
			$where .= " AND data < '".date('Ymd')."'";
		}
		
		$sql = "SELECT COUNT(*) FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' ".$where;
//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	public static function getAgendasMarcadas($param = []){
		$ret = [];
		$where = '';
		$somenteQuantidade = $param['somenteQuantidade'] ?? false;
		
		$clienteFora = $param['clientesFora'] ?? true;
		if($clienteFora !== false){
			$where .= " AND cliente_agenda NOT IN (".getParametroSistema('sdm_clientes_sem_os').") ";
		}
		
		if(isset($param['usuario'])){
			$where .= " AND recurso = '".$param['usuario']."' ";
		}else{
			$where .= " AND recurso = '".getUsuario()."' ";
		}
		
		if(isset($param['data'])){
			$where .= " AND data < '".$param['data']."'";
		}else{
			$where .= " AND data > '".date('Ymd')."'";
		}
		
		
		if($somenteQuantidade){
			$sql = "SELECT count(*) FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' ".$where;
		}else{
			$campos = ['recurso','data','turno','contato','cliente_agenda'];
			$sql = "SELECT * FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' ".$where;
		}
		
		
		//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			if($somenteQuantidade){
				$ret = $rows[0][0];
			}else{
				foreach ($rows as $row){
					$temp = [];
					foreach ($campos as $c){
						$temp[$c] = $row[$c];
					}
			
					$ret[] = $temp;
				}
			}
		}		
		
		return $ret;
	}
	
	public static function getRecursos($recurso = ''){
		$ret = [];
		
		$where = '';
		if(!empty($recurso)){
			$where = " AND recurso = '$recurso'";
		}
		$sql = "SELECT * FROM cad_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' $where ORDER BY apelido";
		//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['nome'] 	= $row['nome'];
				$temp['apelido']= $row['apelido'];
				$temp['tipo'] 	= $row['tipo'];
				$temp['recurso']= $row['usuario'];
				
				$ret[$row['usuario']] = $temp;
			}
		}
		
		return $ret;
	}
	
	public static function getRecursosLista($apelido = true, $ativos = true){
		$ret = [];
		
		$ret[0][0] = '';
		$ret[0][1] = '';
		
		$where = '';
		if($ativos){
			$where = " AND ativo = 'S'";
		}
		$sql = "SELECT nome, apelido, usuario FROM cad_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' $where ORDER BY apelido";
//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp[0] = $row['usuario'];
				$temp[1] = $apelido ? $row['apelido'] : $row['nome'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	public static function getClienteCampo($cliente, $campo = 'nreduz'){
		$ret = '';
		
		$sql = "SELECT $campo FROM cad_clientes WHERE cod = '$cliente'";
//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	//-------------------------------------------------------------------------------------- SET -------------------------------------
	
	//-------------------------------------------------------------------------------------- ADD -------------------------------------
	
	//-------------------------------------------------------------------------------------- VO  -------------------------------------
	
	//-------------------------------------------------------------------------------------- UTEIS -----------------------------------
	
}