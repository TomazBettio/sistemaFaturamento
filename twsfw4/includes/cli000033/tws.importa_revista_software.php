<?php
/*
 * Data Criacao 07/06/2022
 * 
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Funcoes utilizadas para a importacao da revista de software
 *
 * Alteracoes:
 *
 */

class importa_revista_software{
	
	//Numero da revista
	private $_numero;
	
	//URL da revista/INPI
	private $_urlRevista;
	
	//Caminho do arquivo
	private $_caminho;
	
	//Campos existentes no arquivo
	private $_campos;
	
	public function __construct($numero = 0){
		global $config;
		
		$this->_urlRevista = $config['URLrevistaINPI'];
		$this->_caminho = $config['caminhoArquivosINPI'].'software'.DIRECTORY_SEPARATOR;
		
		$this->_campos = [ 
				"(Cd) ",
				"(Np) ",
				"(54) ",
				"(73) ",
				"(Cr) ",
				"(Lg) ",
				"(Cp) ",
				"(Tp) ",
				"(Dl) ",
				"(Rg) ",
				"(74) "
		];
		
		$this->_numero = $numero;
	}
	
	//-------------------------------------------------------------------------- GET -----------------------------
	##### VERIFICA SE EXISTE ALGUMA REVISTA BLOQUEADA
	public function getRevistaBloqueada() {
		$ret = false;
		
		$sql = "SELECT * FROM MarpaMovRevistaSoft WHERE status = 'B' ";
		$rows = query2($sql);
		
		if(count($rows) > 0){
			$ret = true;
		}
		
		return $ret;
	}
}