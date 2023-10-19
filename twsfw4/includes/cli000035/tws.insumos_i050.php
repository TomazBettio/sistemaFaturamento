<?php
/*
 * Data Criacao: 07/07/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Resgata dados I050
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);




class insumos_i050{
	
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
		
		$this->_bloco = 'I050';
		$this->_blocosAtivos = ['I050', 'I051'];
		
		$this->_resumo = true;
		$this->_arqResumo = 'I050_resumo';
		
		$this->getInformacoes(false);
		
		return;
	}
	
	public function getDados(){
		return $this->_dados;
	}
	
	private function getInformacoes($debug = false){
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
				if($debug){
					print_r($arquivos);
				}
				
				if(count($arquivos) == 0){
					die("Não constam arquivo de ".$this->_tipoSPED." no diretorio $dir, favor verificar!");
				}
				
				foreach ($arquivos as $arquivo){
					$linhas = $this->leituraArquivo($dir.DIRECTORY_SEPARATOR.$arquivo, $this->_bloco, $ano);
					if(count($linhas) > 0){
// ----------------------------------------------------------------------------------------------------------
						foreach ($linhas as $linha){
							$this->_dados[] = $linha;
						}
// ----------------------------------------------------------------------------------------------------------
					}
				}
			}
			
			if($debug){
				print_r($this->_dados);
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
	
	
	private function leituraArquivo($arq, $bloco, $ano){
		$ret = [];
		$temp_I050 = '';
		$temp_I050_red = '';
		$temp_I050_desc = '';
		$conta_pos = 4;
		if($ano >= 2019){
			$conta_pos = 3;
		}
		//Indica se existe as linhas I051
		$existe051 = false;
//echo "Arquivo: $arq <br>\n";
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
//echo "$linha";
						$sep = $this->separaLinha($linha);
// ----------------------------------------INI Processo especifico ------------------------------------------------------------------
						if($sep[1] == 'I050'){
							if(!empty($temp_I050)){
								$t = [];
								$t['reduzida'] = $temp_I050_red;
								$t['desc'] = utf8_encode($temp_I050_desc);
								$t['cod'] = $temp_I050;
								$t['conta'] = '';
								//print_r($t);
								if(strpos($temp_I050,'1') !== 0 && strpos($temp_I050,'2') !== 0 && strpos($temp_I050,'01') !== 0 && strpos($temp_I050,'02') !== 0){
									$ret[] = $t;
								}
								
								$temp_I050 = '';
								$temp_I050_red = '';
							}
							$temp_I050 = $sep[6];
							$temp_I050_red = $sep[7];
							$temp_I050_desc = $sep[8];
						}else{
							$existe051 = true;
							if(!empty($temp_I050) && !empty(trim($sep[$conta_pos]))){
								$t = [];
								$t['reduzida'] = $temp_I050_red;
								$t['desc'] = utf8_encode($temp_I050_desc);
								$t['cod'] = $temp_I050;
								$t['conta'] = $sep[$conta_pos];
//print_r($t);
								if(strpos($sep[$conta_pos],'1') !== 0 && strpos($sep[$conta_pos],'2') !== 0 && strpos($sep[$conta_pos],'01') !== 0 && strpos($sep[$conta_pos],'02') !== 0){
									$ret[] = $t;
								}
								
								$temp_I050 = '';
								$temp_I050_red = '';
							}
						}
						
						/*
						 * Se não existe a conta reduzida, grava o ultimo 050 lido
						 */
						if(!$existe051){
							$t = [];
							$t['reduzida'] = $temp_I050_red;
							$t['desc'] = utf8_encode($temp_I050_desc);
							$t['cod'] = $temp_I050;
							$t['conta'] = '';
							//print_r($t);
							if(strpos($temp_I050,'1') !== 0 && strpos($temp_I050,'2') !== 0 && strpos($temp_I050,'01') !== 0 && strpos($temp_I050,'02') !== 0){
								$ret[] = $t;
							}
						}
// ----------------------------------------INI Processo especifico ------------------------------------------------------------------
					}
				}
			}
			fclose($handle);
		}else{
			echo "Erro ao abrir o arquivo $arq <br>\n";
			return false;
		}
//print_r($ret);	
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
		$arq = $this->_path.DIRECTORY_SEPARATOR.$nome.'.csv';
		
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
		
		$file = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.csv', "w");
		foreach ($this->_dados as $dado){
			fwrite($file, implode(';', $dado)."\n");
		}
		
		fclose($file);
	}
	
	
	private function recuperaResumo($arquivo){
		$dados = [];
		if(empty($arquivo)){
			return;
		}
		
		$handle = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.csv', "r");
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
			$dado = explode(';', $dado);
			
			$temp = [];
			$temp['reduzida'] 	= $dado[0];
			$temp['desc'] 		= $dado[1];
			$temp['cod'] 		= $dado[2];
			$temp['conta'] 		= $dado[3];
			
			$this->_dados[] = $temp;
		}
	}
}