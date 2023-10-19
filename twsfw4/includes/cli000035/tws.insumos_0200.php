<?php
/*
 * Data Criacao: 07/07/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Resgata dados 0200 - produtos
 * 
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);




class insumos_0200{
	
	//Anos
	private $_anos = [];
	
	//Path
	private $_path;
	
	//Dados
	private $_dados = [];
	
	//Indica se controla resumo (TXT com as informações já extraidas)
	private $_resumo;
	
	//Nome do arquivo resumo
	private $_arqResumo;
	
	//Tipo de arquivo (ECD/EFD)
	private $_tipoSPED;
	
	//Bloco a ser pesquisado
	private $_bloco;
	
	//Blocos ativos (devem ser pesquisados)
	private $_blocosAtivos;
	
	private $_trace;
	
	public function __construct($path, $anos, $tipo, $trace = false){
		$this->_path = $path;
		$this->_anos = $anos;
		$this->_tipoSPED = $tipo;
		$this->_trace = $trace;
		
		$this->_bloco = '0200';
		$this->_blocosAtivos = ['0200'];
		
		$this->_resumo = true;
		$this->_arqResumo = '0200_resumo';
		
		$this->getInformacoes();
		
		return;
	}
	
	public function getDados(){
		return $this->_dados;
	}
	
	private function getInformacoes(){
		$existe_resumo = $this->_resumo ? $this->verificaResumo($this->_arqResumo) : true;
		
		if($existe_resumo){
			foreach ($this->_anos as $ano){
				$dir = $this->_path.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.strtolower($this->_tipoSPED);
				if(!is_dir($dir)){
					$dir = $this->_path.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.strtoupper($this->_tipoSPED);
					if(!is_dir($dir)){
						die("'Diretorio $dir não encontrado, favor revisar!");
					}
				}
				
				$arquivos = $this->getArquivos($dir);
				if($this->_trace){
					print_r($arquivos);
				}
				
				if(count($arquivos) == 0){
					die("Não constam arquivo de ".$this->_tipoSPED." no diretorio $dir, favor verificar!");
				}
				
				foreach ($arquivos as $arquivo){
					$linhas = $this->leituraArquivo($dir.DIRECTORY_SEPARATOR.$arquivo, $this->_bloco);
					if(count($linhas) > 0){
// ----------------------------------------------------------------------------------------------------------
						foreach ($linhas as $linha){
							$this->_dados[$linha['cod']] = $linha;
						}
// ----------------------------------------------------------------------------------------------------------
					}
				}
			}
			
			if($this->_resumo){
				$this->gravaResumo($this->_arqResumo);
			}
		}else{
			$this->recuperaResumo($this->_arqResumo);
		}
		//print_r($this->_planoContas);
	}
	
	
	//------------------------------------------------------------------- UTEIS ---------------------------
	private function getArquivos($dir){
		$ret = [];
		$diretorio = dir($dir);
		
		while($arquivo = $diretorio->read()){
			if($arquivo != '.' && $arquivo != '..'){
				$ret[] = $arquivo;
			}
		}
		
		return $ret;
	}
	
	
	private function leituraArquivo($arq, $bloco = ''){
		$ret = [];
		$temp_I050 = '';
		$temp_I050_red = '';
		$temp_I050_desc = '';
		
		$handle = fopen($arq, "r");
		if ($handle) {
			while (!feof($handle)) {
				$linha = fgets($handle);
				if (strlen(trim($linha)) > 0) {
					$processa = false;
					if(count($this->_blocosAtivos) == 0){
						$processa = true;
					}else{
						foreach ($this->_blocosAtivos as $ativo){
							if(strpos($linha, $ativo) === 1){
								$processa = true;
							}
						}
					}
					if($processa){
						$sep = $this->separaLinha($linha);
// ----------------------------------------INI Processo especifico ------------------------------------------------------------------
						$ret[] = ['cod'=> $sep[2], 
								  'desc'=> $sep[3], 
								  'NCM'=>$sep[8]
								];
// ----------------------------------------INI Processo especifico ------------------------------------------------------------------
					}
				}
			}
			fclose($handle);
		}else{
			echo "Erro ao abrir o arquivo $arq <br>\n";
			return false;
		}
		return $ret;
	}
	
	private function separaLinha($linha){
		return explode('|', $linha);
	}
	
	//--------------------------------------------------------------- RESUMO ----------------------------------
	
	/**
	 * Verifica se o arquivo de resumo existe
	 *
	 * @param string $nome - Nome do arquivo (sem extensão)
	 * @return boolean
	 */
	private function verificaResumo($nome){
		$ret = true;
		$arq = $this->_path.DIRECTORY_SEPARATOR.$nome.'.txt';
		
		if(file_exists($arq)){
			$ret = false;
		}
		
		return $ret;
	}
	
	private function gravaResumo($arquivo){
		if(empty($arquivo)){
			return;
		}
		
		ksort($this->_dados);
		
		$file = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.txt', "w");
		if(count($this->_dados ) == 0){
			echo "Arquivo $arquivo sem dados!<br>\n";
			return;
		}
		
		foreach ($this->_dados as $dado){
			fwrite($file, implode('|', $dado)."\n");
		}
		
		fclose($file);
	}
	
	
	private function recuperaResumo($arquivo){
		$dados = [];
		if(empty($arquivo)){
			return;
		}

		$handle = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.txt', "r");
		if ($handle) {
			while (!feof($handle)) {
				$linha = fgets($handle);
				if (strlen(trim($linha)) > 0) {
					$dados[] = str_replace("\n", '', $linha);
				}
				
			}
			fclose($handle);
		}else{
			addPortalMensagem("Arquivo $arquivo - recuperaArquivo - não encontrado", 'danger');
		}
		
		foreach ($dados as $dado){
			$dado = explode('|', $dado);
			$cod = $dado[0];
			
			$temp = [];
			$temp['cod'] 	= $cod;
			$temp['desc'] 	= $dado[1];
			$temp['NCM'] 	= $dado[2];
		
			$this->_dados[$cod] = $temp;
		}
		
	}
	
}