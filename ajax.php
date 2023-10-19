<?php
/*
 * Data Criacao: 27/01/2022
 * Autor: Alexandre Thiel
 *
 * Descricao: 	Executa chamadas AJAX do programa.
 *              Como não realiza todas os processos do index é mais rápido
 *
 * 				Chama o método ajax de qualquer classe (programa)
 *              - pode controlar internamente mais de uma função utilizando a OPERACAO
 *              - precisa ser declarado como público
 *              - não precisa estar no array de funções liberadas
 */

if(!defined('TWSiNet'))define('TWSiNet', true);

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0
header('Content-type: text/html; charset=utf-8');

include("./config/config.php");

if($config['error_reporting']){
	ini_set('display_errors',1);
	ini_set('display_startup_erros',1);
	error_reporting(E_ALL);
}

$pagina = new ajax();
if($pagina->logado()){
	echo $pagina;
}
die();