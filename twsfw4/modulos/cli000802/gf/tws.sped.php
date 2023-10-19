<?php

/*
 * Data Criacao: 31/08/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Realiza o ajuste do sped fiscal da Gauchafarma 
 *
 * Alteracoes;
 *
 *
 */

include($config['include'].DIRECTORY_SEPARATOR.'sped'.DIRECTORY_SEPARATOR.'tws.efd.php');

class sped{
	
	var $funcoes_publicas = array(
			'index' 			=> true,
	);
	
	private $_efd;
	
	//Array do bloco H
	private $_bloco;
	
	private $_
	
	public function __construct(){
		$this->_efd = new efd("C:\Temp\GF\sped_fiscal_teste-27.07.txt");
	}
	
	public function index(){
		$this->_efd->setBlocoLeitura('H');
		
		$this->_efd->leitura();
		
		$this->_bloco = $this->_efd->processaBlocoH();
		
		
	}
	
	private function processaH030() {
		
	}
}