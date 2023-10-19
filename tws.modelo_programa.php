<?php
/*
 * Data Criacao: 27/01/2022
 * Autor: 
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class modelo_programa{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	//Titulo
	private $_titulo;
	
	//Programa
	private $_programa;
	
	function __construct(){
		$this->_titulo ='Modelo de Programa';
		$this->_programa = get_class($this);
	}
	
	public function index(){
		$ret = '';
		
		return $ret;
	}
}