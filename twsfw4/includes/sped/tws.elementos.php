<?php
/*
 * Data Criacao: 01/09/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: 
 *
 * Alteracoes;
 *
 *
 */

class Elementos{
	
	protected $_parametros = [];
	
	public function __construct() {
		
	}
	
	public function lerLinha($linha){
		$ret = [];
		
		$i = 1;
		$ret['BLOCO'] = $linha[$i];
		
		foreach ($this->_parametros as $key => $param){
			$i++;
			if($param['type'] == 'numeric'){
				$dado = str_replace(',', '.', $linha[$i]);
			}else{
				$dado = $linha[$i];
			}
			$ret[$key] = $dado;
		}
		
		
		return $ret;
	}
}