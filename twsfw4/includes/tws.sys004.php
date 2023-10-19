<?php
/*
* Data Criacao: 26/03/2013 - 15:55:13
* Autor: Thiel
*
* Descricao: 
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class sys004{

	static function inclui($dados){
		global $config;
		
		$campos = [];
		$campos['programa'] 	= verificaParametro($dados, 'programa','');
		$campos['emp'] 			= verificaParametro($dados, 'emp','');
		$campos['fil'] 			= verificaParametro($dados, 'fil','');
		$campos['ordem'] 		= verificaParametro($dados, 'ordem', 1);
		$campos['variavel'] 	= verificaParametro($dados, 'variavel', '');
		
		if(!empty($campos['programa'])){
			$sql = "SELECT * FROM sys004 WHERE emp = '".$campos['emp']."' AND fil = '".$campos['fil']."' AND programa = '".$campos['programa']."' AND (ordem = '".$campos['ordem']."' OR variavel = '".$campos['variavel']."')";
//echo "$sql <br>\n";
			$rows = query($sql);
			if(count($rows) == 0){
			
				$campos['pergunta'] 	= verificaParametro($dados, 'pergunta'		, '');
				$campos['tipo'] 		= verificaParametro($dados, 'tipo'			, '');
				$campos['tamanho'] 		= verificaParametro($dados, 'tamanho'		, 0);
				$campos['casadec'] 		= verificaParametro($dados, 'casadec'		, 0);
				$campos['validador'] 	= verificaParametro($dados, 'validador'		, '');
				$campos['tabela'] 		= verificaParametro($dados, 'tabela'		, '');
				$campos['funcaodados'] 	= verificaParametro($dados, 'funcaodados'	, '');
				$campos['help'] 		= verificaParametro($dados, 'help'			, '');
				$campos['inicializador']= verificaParametro($dados, 'inicializador'	, '');
				$campos['inicFunc'] 	= verificaParametro($dados, 'inicfunc'		, '');
				$campos['opcoes'] 		= verificaParametro($dados, 'opcoes'		, '');
				
				$sql = montaSQL($campos, 'sys004');
//echo "$sql <br>\n";
				query($sql);
			}
		}
		return "";
	}
	
	static function deletaTodos($programa){
		global $config;
		$sql = "DELETE FROM sys004 WHERE programa = '$programa' ";
		query($sql);
	}
}