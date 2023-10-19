<?php
/*
 * Data Criacao 07/06/2022
 *
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Realiza a importacao da revista de software
 *
 * Alteracoes:
 *
 */

class importa_revista_software{
	
	//Revista
	private $_revista;
	
	//Nome do arquivo de LOG
	private $_log;
	
	public function schedule($param = ''){
		$this->_log = 'importacao_revista_software';
		$this->_revista = new importa_revista_software();
		log::gravaLog($this->_log, 'Iniciando schedule Importa Revista Software');
		
		if($this->_revista->getRevistaBloqueada()){
			$this->enviaEmail($param, 'revistaBloqueada');
		}
		
		
	}
	
	
	private function enviaEmail($param, $tipo){
		$msg = '';
		$log = '';
		
		switch ($tipo) {
			case 'revistaBloqueada':
				$msg = 'Existe um bloqueio de revista - software';
				$log = 'Existe um bloqueio de revista - software';
				break;
			default:
				$msg = 'Problema não identificado!';
				$log = 'Problema não identificado!';
				break;
		}
		
		if(!empty($log)){
			log::gravaLog($this->_log, $log);
		}
	}
}