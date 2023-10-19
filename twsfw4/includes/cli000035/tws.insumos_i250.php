<?php
/*
 * Data Criacao: 07/07/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Resgata dados I075
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);




class insumos_i250{
	
	//Anos
	private $_anos = [];
	
	//Path
	private $_path;
	
	//Dados
	private $_dados = [];
	
	//Indica se controla resumo (CSV com as informações já extraidas)
	private $_resumo;
	
	//Nome do arquivo resumo
	private $_arqResumo;
	
	//Tipo de arquivo (ECD/EFD)
	private $_tipoSPED;
	
	//Bloco a ser pesquisado
	private $_bloco;
	
	//Blocos ativos (devem ser pesquisados)
	private $_blocosAtivos;
	
	private $_letras = [];
	
	public function __construct($path, $anos, $tipo){
		$this->_path = $path;
		$this->_anos = $anos;
		$this->_tipoSPED = $tipo;
		
		$this->_bloco = 'I250';
		$this->_blocosAtivos = ['I200', 'I250'];
		
		$this->_resumo = true;
		$this->_arqResumo = 'I250_resumo';
		
		$this->getInformacoes();
		
		$this->_letras = range('A', 'Z');
		$this->_letras[] = '&';
		$this->_letras[] = 'º';
		$this->_letras[] = '-';
		$this->_letras[] = ':';
		$this->_letras[] = '/';
		$this->_letras[] = '\\';
		$this->_letras[] = '.';
		$this->_letras[] = ')';
		$this->_letras[] = '(';
		$this->_letras[] = 'Ã';
		$this->_letras[] = 'é';
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
						foreach ($linhas as $l){
							//$conta = $l['CONTA'];
							//$conta = isset($this->_planoContas[$contaI250]) ? $this->_planoContas[$contaI250][2] : 'SEM_CONTA';
							
							$data = $l['I200_DT'];
							$data = substr($data, 4,4).substr($data, 2,2).substr($data, 0,2);
							
							$valor		= empty($l['VALOR']) ? 0 : str_replace(',', '.', $l['VALOR']);
							
							$this->_dados[$data][] = [
									'conta'	=>$l['CONTA'],
									'data'	=>$data,
									'lanc'	=>$l['I200_LANC'],
									'vl'	=>$valor,
									'DC'	=>$l['DC'],
									'hist'	=>strtoupper($l['HISTORICO']),
									'I075'	=>$l['I075'],
									'nota'	=> $this->pesquisaNrNota(strtoupper($l['HISTORICO']))
							];
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
	
	private function pesquisaNrNota($historico){
		//$novo = trim(str_replace($this->_letras, '', $historico));
		$novo = preg_replace("/[^0-9]/", "", $historico);
		//echo "novo: $novo - $historico<br>\n";
		
		return $novo;
	}
	
	private function leituraArquivo($arq, $bloco = ''){
		$ret = [];
		
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
						if($sep[1] == 'I200'){
							$temp_I200_LANC = $sep[2];
							$temp_I200_DT = $sep[3];
						}else{
							if(strpos($sep[2],'1') !== 0 && strpos($sep[2],'2') !== 0 && strpos($sep[2],'01') !== 0 && strpos($sep[2],'02') !== 0){
								$temp = [];
								$temp['I200_LANC']  = $temp_I200_LANC;
								$temp['I200_DT']	= $temp_I200_DT;
								
								$temp['CONTA']		= $sep[2];
								$temp['VALOR']		= str_replace(',', '.', $sep[4]);
								$temp['DC']			= $sep[5];
								$temp['HISTORICO']	= $sep[8];
								$temp['I075']		= $sep[7];
								
								$ret[] = $temp;
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
			echo "Arquivo $arquivo sem dados!<br>\n";
			return;
		}
		
		$file = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.csv', "w");
		foreach ($this->_dados as $dados){
			foreach ($dados as $dado){
				fwrite($file, implode(';', $dado)."\n");
			}
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
			$data = $dado[1];
			
			$this->_dados[$data][] = [
					'conta'	=> $dado[0],
					'data'	=> $dado[1],
					'lanc'	=> $dado[2],
					'vl'	=> $dado[3],
					'DC'	=> $dado[4],
					'hist'	=> $dado[5],
					'I075'	=> $dado[6],
					'nota'	=> isset($dado[7]) ? $dado[7] : ''
			];
		}
	}
}