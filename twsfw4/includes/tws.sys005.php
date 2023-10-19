<?php
/*
 * Data Criacao: 07/03/22
 * Autor: Thiel
 *
 * Descricao: funções para manipulação da SYS005 - Tabelas auxiliares
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class sys005{
	
	//-------------------------------------------------------------------------------------- UI --------------------------------------
	
	//-------------------------------------------------------------------------------------- GET -------------------------------------
	
	static function getGrupos($semGrupo = false, $base64 = false){
		$ret = [];
		
		$param = [];
		$param['base64'] = $base64;
		$ret = tabela('SYS5GR', $param);		
		
		if($semGrupo){
			$temp[0] = 'SEMGRP';
			if($base64){
				$temp[0] = base64_encode($temp[0]);
			}
			$temp[1] = 'Sem grupo';
			$ret[] = $temp;
		}
		
		return $ret;
	}
	
	static  function getTabelasGrupo($grupo, $branco = true){
		$ret = [];
		
		if($branco){
			$ret[0][] = '';
			$ret[0][] = '';
		}
		
		if(!empty($grupo)){
			$grupo = $grupo == 'SEMGRP' ? '' : $grupo;
			
			$sql = "SELECT chave, descricao FROM sys005 WHERE tabela = '000000' AND grupo = '$grupo' AND ativo = 'S' ";
			$rows = query($sql);
			
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$temp = [];
					$temp[] = $row['chave'];
					$temp[] = $row['descricao'];
					
					$ret[] = $temp;
				}
			}
		}
		
		return $ret;
	}
	
	static function getGrupoNome($grupo){
		$ret = '';
		
		if(!empty($grupo)){
			$ret = getTabelaDesc('SYS5GR', $grupo);
		}
		
		return $ret;
	}
	
	static function getTabelaNome($tabela){
		$ret = '';
		
		if(!empty($tabela)){
			$sql = "SELECT descricao FROM sys005 WHERE tabela = '000000' AND chave = '$tabela' ";
			$rows = query($sql);
			
			if(isset($rows[0]['descricao'])){
				$ret = $rows[0]['descricao'];
			}
		}
		
		return $ret;
	}
	//-------------------------------------------------------------------------------------- SET -------------------------------------
	
	//-------------------------------------------------------------------------------------- ADD -------------------------------------
	
	//-------------------------------------------------------------------------------------- UTEIS -----------------------------------
}