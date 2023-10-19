<?php
/*
 * Data Criacao: 04/10/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Separa o arquivo de resultado do insumos e separa, e depois cria um zip
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);




class insumos_separa{
	
	private $_path;
	
	private $_arquivo;
	
	private $_separaPor;
	
	private $_id;
	
	private $_arquivoZIP;
	
	private $_arquivoOrig;
	
	private $_arquivosGerados = [];
	
	private $_cab;
	
	public function __construct($path, $arquivo, $separaPor, $id){
		$this->_path = $path;
		$this->_arquivo = $arquivo;
		$this->_separaPor = $separaPor;
		$this->_id = $id;
		
		$this->_arquivoZIP = $path.$id.DIRECTORY_SEPARATOR.'I250_'.$this->_arquivo.'.zip';
		$this->_arquivoOrig = $path.$id.DIRECTORY_SEPARATOR.'I250_'.$this->_arquivo.'.csv';
	}
	
	public function separa(){
		$ret = [];
		if(!is_file($this->_arquivoOrig)){
			addPortalMensagem('Não encontrado o arquivo I250_'.$this->_arquivo.'.csv', 'danger');
			return;
		}
		
		$handle = fopen($this->_arquivoOrig, "r");
		if ($handle) {
			$linha = fgets($handle); // ignora a primeira linha
			$this->_cab = $linha;
			while (!feof($handle)) {
				$linha = fgets($handle);
				if (strlen(trim($linha)) > 0) {
					$sep = explode(';', $linha);
					$conta = $sep[1];
					$ano = substr($sep[9], 6, 4);
					
					switch ($this->_separaPor) {
						case 'A':
							$ret[$ano][] = $linha;
							break;
						case 'C':
							$ret[$conta][] = $linha;
							break;
						case 'AC':
							$ret[$ano][$conta][] = $linha;
							break;
					}
				}
			}
			fclose($handle);
			
			switch ($this->_separaPor) {
				case 'A':
					foreach ($ret as $ano => $linhas){
						$this->gravaArquivo($ano,'',$linhas);
					}
					break;
				case 'C':
					foreach ($ret as $conta => $linhas){
						$this->gravaArquivo('',$conta,$linhas);
					}
					break;
				case 'AC':
					foreach ($ret as $ano => $contas){
						foreach ($contas as $conta => $linhas){
							$this->gravaArquivo($ano,$conta,$linhas);
						}
					}
					break;
			}
			clearstatcache();
			
			$this->gerarZIP();
			
			addPortalMensagem('Arquivo I250_'.$this->_arquivo.'.zip gerado com sucesso!');
		}else{
			addPortalMensagem('Não foi possível abrir o arquivo I250_'.$this->_arquivo.'.csv', 'danger');
		}
		
	}
	
	public function exluiZIP(){
		if(is_file($this->_arquivoZIP)){
			unlink($this->_arquivoZIP);
		}
	}
	
	private function gerarZIP(){
		$zip = new ZipArchive(); 
		$zip->open($this->_arquivoZIP, ZipArchive::CREATE); 
		foreach($this->_arquivosGerados as $arquivo){ 
			$zip->addFile($arquivo[0],$arquivo[1]); 
		} 
		$zip->close();
		
		foreach($this->_arquivosGerados as $arquivo){
			unlink($arquivo[0]);
		}
		
	}
	

	private function gravaArquivo($ano,$conta,$linhas){
		$nome = 'I250_'.$this->_arquivo;
		if(!empty($ano)){
			$nome .= '_'.$ano;
		}
		if(!empty($conta)){
			$nome .= '_'.$conta;
		}
		$nome .= '.csv';
		
		$file = fopen($this->_path.$this->_id.DIRECTORY_SEPARATOR.$nome, "w");
		fwrite($file, $this->_cab);
		foreach ($linhas as $linha){
			fwrite($file, $linha);
		}
		
		fclose($file);
		$temp = [];
		$temp[0] = $this->_path.$this->_id.DIRECTORY_SEPARATOR.$nome;
		$temp[1] = $nome;
		$this->_arquivosGerados[] = $temp;
	}

}