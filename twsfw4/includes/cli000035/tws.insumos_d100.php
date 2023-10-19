<?php
/*
 * Data Criacao: 07/07/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Resgata dados A100
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);




class insumos_d100{
	
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
	
	public function __construct($path, $anos, $tipo){
		$this->_path = $path;
		$this->_anos = $anos;
		$this->_tipoSPED = $tipo;
		
		$this->_bloco = 'D100';
		$this->_blocosAtivos = ['D100','D101','D105'];
		
		$this->_resumo = true;
		$this->_arqResumo = 'D100_resumo';
		
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
				
				if(count($arquivos) == 0){
					die("Não constam arquivo de ".$this->_tipoSPED." no diretorio $dir, favor verificar!");
				}
				
				foreach ($arquivos as $arquivo){
					$linhas = $this->leituraArquivo($dir.DIRECTORY_SEPARATOR.$arquivo, $this->_bloco);
					if(count($linhas) > 0){
// ----------------------------------------------------------------------------------------------------------
						foreach ($linhas as $linha){
							$this->_dados[] = $linha;
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
		$ret_temp = [];
		
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
						if($sep[1] == 'D100'){
							if(count($ret_temp) > 0){
								$ret[] = ['data'=>$ret_temp['data'], 'nota'=>$ret_temp['nota'], 'vl'=>$ret_temp['valor'], 'pis'=>$ret_temp['pis'], 'cof'=>$ret_temp['cofins'], 'bloco' =>'D100'];
								$ret_temp = [];
							}
							$ret_temp['nota'] = $sep[9];
							$ret_temp['data'] = $sep[12];
							$ret_temp['valor'] = str_replace(',', '.', $sep[15]);
							$ret_temp['pis'] = 0;
							$ret_temp['cofins'] = 0;
						}elseif($sep[1] == 'D101'){
							$ret_temp['pis'] = str_replace(',', '.', $sep[8]);
						}else{
							$ret_temp['cofins'] = str_replace(',', '.', $sep[8]);
						}
						
						
// ----------------------------------------INI Processo especifico ------------------------------------------------------------------
					}
				}
			}
			if(count($ret_temp) > 0 ){
				$ret[] = ['data'=>$ret_temp['data'], 'nota'=>$ret_temp['nota'], 'vl'=>$ret_temp['valor'], 'pis'=>$ret_temp['pis'], 'cof'=>$ret_temp['cofins'], 'bloco' =>$bloco];
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
		
		if(count($this->_dados ) == 0){
			return;
		}
		
		$file = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.txt', "w");
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
			
			$temp = [];
			$temp['data'] 	= $dado[0];
			$temp['nota']	= $dado[1];
			$temp['vl']		= $dado[2];
			$temp['pis']	= $dado[3];
			$temp['cof']	= $dado[4];
			$temp['bloco'] 	= $dado[5];
			$temp['seq']	= isset($dado[6]) ? $dado[6] : '';
			$temp['prod']	= isset($dado[7]) ? $dado[7] : '';
			
			$this->_dados[] = $temp;
		}
		
	}
}