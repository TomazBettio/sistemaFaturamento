<?php
/*
 * Data Criacao: 26/06/2018
 * Autor: Thiel
 *
 * Descricao: Utilizado para controlar se o menu vai estar recolhido ou não
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class menu_colapse{
	var $funcoes_publicas = array(
			'ajax' 	=> true,
	);
	
	function ajax(){
		global $config;

		$config['sidebar_collapse'] = $config['sidebar_collapse'] ? false : true;
		putAppVar('sidebar_collapse', $config['sidebar_collapse']);
		
		return 'ok';
	}
	
}