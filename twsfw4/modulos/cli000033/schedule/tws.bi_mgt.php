<?php
/*
 * Data Criação: 30/06/2022
 * Autor: Thiel
 *
 * Descrição: Atualização do MSSQL do BI da MGT
 *
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class bi_mgt{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	function __construct(){
		set_time_limit(0);
	}
	
	function index(){
		
	}
	
	function schedule($param){
		echo " ----------------------------------- INICIO --------------------------------------------------<br>\n";
		log::gravaLog('atualizacao_bi_mgt', 'Iniciando atualização');
		$url = file_get_contents('http://192.168.1.251/BI/atualiza_dados_bi.php');
		log::gravaLog('atualizacao_bi_mgt', 'Atualização realizada');
		echo " ----------------------------------- FIM ------------------------------------------------------<br>\n";
		
	}

	
}


