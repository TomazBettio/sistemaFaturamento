<?php
/*
 * Data Criacao: 07/07/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Grava CSV
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class insumos_excel{
	
	private $_path;
	private $_dados;
	private $_campos;
	private $_cab;
	private $_tipos;
	
	
	public function __construct($path, $dados, $campos, $cab, $tipos){
		$this->_path = $path;
		$this->_dados = $dados;
		$this->_campos = $campos;
		$this->_cab = $cab;
		$this->_tipos = $tipos;
	}
	
	
	public function grava($arquivo){
		$file = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo, "w");
		if(count($this->_cab) > 0){
			$linha = implode(';', $this->_cab);
			fwrite($file, $linha."\n");
		}
		$q = count($this->_dados);
		if($q == 0){
			fclose($file);
			return;
		}
		
		foreach ($this->_dados as $dado){
			$temp = [];
			foreach ($this->_campos as $campo){
				$temp[] = isset($dado[$campo]) ? $dado[$campo] : '';
			}
			$linha = implode(';', $temp);
			fwrite($file, $linha."\n");
		}
		
		fclose($file);
	}
}