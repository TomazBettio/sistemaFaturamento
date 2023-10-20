<?php
/*
 * Data Criacao: 26/06/2018
 * Autor: Thiel
 *
 * Descricao: Utilizado para manter a sessão ativa (para processos que são muito demorados)
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class manter_conectado{
	var $funcoes_publicas = array(
			'ajax' 	=> true,
	);
	
	function ajax(){
		return 'ok';
	}
	
}