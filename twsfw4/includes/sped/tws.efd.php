<?php

/*
 * Data Criacao: 31/08/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: SPED fiscal
 *
 * Alteracoes;
 *
 *
 */

include_once 'tws.sped_arquivo.php';

class efd extends sped_arquivo{
	
	public function __construct($arquivo, $operacao = 'L'){
		
		parent::__construct($arquivo,'efd', $operacao);
		$this->setElementoLeitura('Z0000');
		
	}
	
	public  function setBlocoLeitura($bloco){
		$elementos = $this->getElementosBloco($bloco);
		
		parent::setBlocoLeitura($elementos);
	}
	
	public function leitura(){
		parent::leitura();
		
//print_r($this->_registros);
	}
	
	public function processaBlocoH()
	{
		$ret = [];
		$elementos = $this->getElementosBloco('H');
		$nivel1 = -1;
		$nivel2 = -1;
		$nivel3 = -1;
		foreach ($this->_registros as $reg){
			$elemento = $reg[1];
			
			if(array_search($elemento, $elementos) !== false){
				$dados = $this->_classesElementos[$elemento]->lerLinha($reg);
				
				if($elemento == 'H020' || $elemento == 'H030'){
					$ret[$nivel1][$nivel2][$nivel3][] = $dados;
				}elseif($elemento == 'H010'){
					$nivel3++;
					$ret[$nivel1][$nivel2][$nivel3] = $dados;
				}elseif($elemento == 'H005'){
					$nivel2++;
					$ret[$nivel1][$nivel2] = $dados;
				}elseif($elemento == 'H001'){
					$nivel1++;
					$ret[$nivel1] = $dados;
				}
			}
		}
		
		return $ret;
	}
	
	//----------------------------------------------------------------- Uteis --------------------------------
	private function getElementosBloco($bloco){
		$ret = [];
		
		switch ($bloco) {
			case 'H':
				$ret[] = 'H001';
				$ret[] = 'H005';
				$ret[] = 'H010';
				$ret[] = 'H020';
				$ret[] = 'H030';
				break;
		}
		
		return $ret;
	}
}

