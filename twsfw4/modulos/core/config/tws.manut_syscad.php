<?php
/*
 * Data Criação: 11/05/2020
 * Autor: Thiel
 *
 * Descricao: Realiza a manutenção nas tabelas sys002, sys003 e sys008
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class manut_syscad{
	var $funcoes_publicas = array(
			'index'			=> true,
	);
	
	//Nome do programa
	private $_programa;
	
	public function __construct(){
		$this->_programa = get_class($this);
	}
	
	public function index(){
		$ret = '';
		
		$param = [];
		$param['colunas'] = 3;
		$param['cards'][0] = $this->card('1');
		$param['cards'][1] = $this->card('2');
		$param['cards'][2] = $this->card('3');
		$ret .= addCardsMoveis($param);
		
		$param = [];
		$param['colunas'] = 3;
		$param['cards'][0] = $this->card('4');
		$param['cards'][1] = $this->card('5');
		$param['cards'][2] = $this->card('6');
		$ret .= addCardsMoveis($param);
		
		return $ret;
	}
	
	private function card($titulo) {
		$param = [];
		$param['titulo'] = $titulo;
		$param['conteudo'] = 'jjjjjjj';
		$param['header-class'] = 'ui-sortable-handle';
		$param['header-style'] = 'cursor: move;';
		
		return addCard($param);
	}
}