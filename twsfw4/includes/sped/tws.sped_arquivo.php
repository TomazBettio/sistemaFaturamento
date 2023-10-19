<?php

/*
 * Data Criacao: 31/08/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Classe com funcoes comuns ao SPED
 *
 * Alteracoes;
 *
 *
 */

include_once 'tws.elementos.php';

class sped_arquivo{
	
	//Nome do arquivo SPED
	protected $_arquivo;
	
	//Tipo de operacao (L -> leitura | G -> gravacao)
	protected $_operacao;
	
	//Diretorio include dos elementos
	protected $_dirElementos;
	
	//Handle do arquivo
	private $_handle;
	
	//Registros encontrados no arquivo
	protected $_registros = [];
	
	//Elementos que devem ser lidos
	protected $_elementosLidos = [];
	
	protected $_classesElementos = [];
	
	public function __construct($arquivo, $dirIncludes, $operacao = 'L'){
		$this->_operacao = $operacao == 'L' ? 'L' : 'G';
		$this->_arquivo = $arquivo;
		$this->_dirElementos = $dirIncludes;
	}
	
	protected function setElementoLeitura($elemento){
		if(!empty($elemento)){
			if(array_search($elemento, $this->_elementosLidos) === false){
				$this->_elementosLidos[] = strpos($elemento, 'Z') === false ? $elemento : substr($elemento, 1);
				
				include_once($this->_dirElementos.DIRECTORY_SEPARATOR.$elemento.'.php');
				$this->_classesElementos[$elemento] = new $elemento();
			}
		}
	}
	
	protected function setBlocoLeitura($elementos){
		if(is_array($elementos) && count($elementos) > 0){
			foreach ($elementos as $elemento){
				$this->setElementoLeitura($elemento);
			}
		}
	}
	
	protected function leitura(){
		$this->_registros = [];
		$this->abreArquivo();
		
		if($this->_handle){
			while (!feof($this->_handle)) {
				$linha = $this->leituraLinha();
				if (count($linha) > 1) {
					$bloco = $linha[1];
					if(array_search($bloco, $this->_elementosLidos) !== false){
						unset($linha[0]);
						$this->_registros[] = $linha;
					}
				}
			}
		
		
		
			$this->fechaArquivo();
		}else{
			echo "Não foi possível abrir o arquivo ".$this->_arquivo." para leitura!<br>\n";
		}
	}
	
	
	
	//------------------------------------------------------------------------
	
	private function abreArquivo(){
		$this->_handle = fopen($this->_arquivo, "r");
		
		if(!$this->_handle){
			echo "Não foi possível abrir o arquivo ".$this->_arquivo." para leitura!<br>\n";
		}
	}
	
	private function fechaArquivo(){
		fclose($this->_handle);
	}
	
	private function leituraLinha(){
		$ret = [];

		$linha = fgets($this->_handle);
		$ret = explode("|", $linha);

		return $ret;
	}
	
}