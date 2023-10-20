<?php
/*
* Data Criação: 26/09/2013 - 09:35:35
* Autor: Thiel
*
* Arquivo: class.integratxt.inc.php
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class integratxt01{
	
	/*
	 * Caracter separador de campos, se vazio = posi��o fixa
	 */
	var $_separador;
	
	/*
	 * Tipo de estrutura de arquivo
	 * 0 => Arquivo simples
	 * 1 => Header, Detalhe, Trailer 
	 * 2 => SPED
	 * 3 => Posição fixa - Header, Detalhe, Trailer
	 */
	var $_tipo;
	
	/*
	 * Diret�rio arquivos novos
	 */
	var $_dirNovos;
	/*
	 * Diret�rio arquivos retorno
	 */
	var $_dirRet;
	/*
	 * Diret�rio arquivos com erros
	 */
	var $_dirErro;
	/*
	 * Diret�rio arquivos processados (c�pia do retorno)
	 */
	var $_dirProc;
	/*
	 * Estrutura do arquivo
	 */
	var $_estrutura;
	/*
	 * Tamanho do registro de identifica��o da linha
	 */
	var $_tamIdLinha;
	/*
	 * Estrutura do arquivo retorno
	 */
	var $_estruturaRet;
	/*
	 * Tamanho do registro de identifica��o da linha de retorno
	 */
	var $_tamIdLinhaRet;
	/*
	 * Nome dos arquivos lidos
	 */
	var $_nomeArq;
	/*
	 * Retorno da leitura dos arquivos
	 */
	var $_resLeitura;

	/*
	 * Arquivo tipo 3
	 */
	var $_arqTipo3;
	
	function __construct($separador, $tipo){
		$this->_separador = $separador;
		$this->_tipo = $tipo;
		$this->_arqTipo3 = null;
	}
	
	function __destruct(){
		if($this->_arqTipo3 != null){
			fclose($this->_arqTipo3);
			$this->_arqTipo3 = null;
		}
		
	}
	
	function setDiretorios($param){
		$this->_dirNovos = isset($param['novos']) 		? $param['novos'] 		: "";
		$this->_dirRet 	 = isset($param['retorno']) 	? $param['retorno'] 	: "";
		$this->_dirErro  = isset($param['erro']) 		? $param['erro'] 		: "";
		$this->_dirProc  = isset($param['processados']) ? $param['processados'] : "";
		$this->_dirRet 	 = isset($param['saida'])	 	? $param['saida'] 		: "";
	}
	
	function leEntradas(){
		$dir = scandir($this->_dirNovos);
print_r($dir);
		if(count($dir) > 2){
			$i = 0;
			foreach ($dir as $arq){
				if($arq != "." && $arq != ".."){
					$this->_nomeArq[$i]['arq'] 	 = $this->_dirNovos."/".$arq;
					$this->_nomeArq[$i]['chave'] = str_replace(".","",$arq);

					$conteudo = $this->leituraArquivo($this->_nomeArq[$i]['arq']);
					if(!is_array($conteudo)){
						$this->_nomeArq[$i]['leitura'] = false;
					}else{
						$this->_nomeArq[$i]['leitura'] = true;
						switch ($this->_tipo) {
							case 0:
								echo "Nao implementado";
								break;
							case 1: // Header...
								$this->separaItensFixo($this->_nomeArq[$i]['chave'], $conteudo);
								break;
							case 2: // SPED
								$this->separaItensSepSPED($this->_nomeArq[$i]['chave'], $conteudo);
								break;
						}
						
					}
					$i++;
				}
			}
		}
	}
	
	
	function getArquivos(){
		return $this->_nomeArq;
	}
	
	function getResultLeitura(){
		return $this->_resLeitura;
	}
	
	/**
	* Separa os dados das linhas conforme o separador
	*
	* @author	Alexandre Thiel
	* @access	public
	* @param	string	$chave		Chave do array (geralmente nome do arquivo)
	* @param	array	$conteudo	Array a ser separada, cada elemento � uma linha
	* @param	boolean	$igPrimeira Indica se deve ser ignorado o primeiro caracter da linha
	* @return	void
	*
	* @version 0.01
	*/
	function separaItensSepSPED($chave, $conteudo){
		$quant = count($conteudo);
		if($quant > 0){
			for($i=0;$i<$quant;$i++){
				$cont = explode("|", substr($conteudo[$i], 1));
				$tipo = $cont[0];
				$estr = isset($this->_estrutura[$tipo]) ? $this->_estrutura[$tipo] : "";
				if(is_array($estr) > 0){
					$temp = array();
					$e = 0;
					foreach ($estr as $e => $item){
						$temp[$item['var']] = $cont[$e];
						$e++;
					}
					$ind = count($this->_resLeitura[$chave]);
					$this->_resLeitura[$chave][$ind] = $temp;
				}
			}
		}
	}
	
	function separaItensFixo($chave, $conteudo){
		$quant = count($conteudo);
		if($quant > 0){
			for($i=0;$i<$quant;$i++){
				$tipo = substr($conteudo[$i], 0,$this->_tamIdLinha);
				$estr = $this->_estrutura[$tipo];
				$temp = array();
				foreach ($estr as $e => $item){
					$temp[$item['var']] = substr($conteudo[$i], $item['pos'] -1, $item['tam']);
				}
				$ind = count($this->_resLeitura[$chave]);
				$this->_resLeitura[$chave][$ind] = $temp;
			}
		}
		
		
		
		
	}
			
	function leituraArquivo($arq){
		$ret = array();
		$handle = fopen($arq, "r");
		if ($handle) {
		    while (!feof($handle)) {
		    	$linha = fgets($handle);
  				if (strlen(trim($linha)) > 0) {
    				$ret[] = $linha;
    			}
	    	}
	    	fclose($handle);
		}else{
	    	return false;
	    }
	    return $ret;
	}
	
	function setEstutInc($param){
		$tipoReg = $param['fixo'][0];
		$this->_tamIdLinha = strlen("$tipoReg");
		$quant = count($param['var']);
		if((count($param['var']) != count($param['pos']) || count($param['var']) != count($param['tam'])) && $this->_tipo == 1){
			die("Quantidade de itens diferentes na estrutura NOVO tipo $tipoReg, por favor verifique!");
		}

		for($i=0;$i<$quant;$i++){
			$this->_estrutura[$tipoReg][$i]['var']  = $param['var'][$i];
			$this->_estrutura[$tipoReg][$i]['pos']  = isset($param['pos'][$i]) ? $param['pos'][$i] : "";
			$this->_estrutura[$tipoReg][$i]['tam']  = isset($param['tam'][$i]) ? $param['tam'][$i] : "";
			$this->_estrutura[$tipoReg][$i]['fixo'] = isset($param['fixo'][$i]) ? $param['fixo'][$i] : "";
		}
	}

	function setEstrutSimplesExprta($param){
		$q = count($param);
		if($q >0){
			$this->_estruturaRet = array();
			for($i=0;$i<$q;$i++){
				$this->_estruturaRet[$i]['var']  = $param[$i];
			}
		}
	}
	
	function gravaArq($arq,$dados){
		$file = fopen($this->_dirRet.$arq, "w");
		$c = count($this->_estruturaRet);
		$q = count($dados);
		if($q==0 || $c==0){
			return "erro";
		}
		for($i=0;$i<$q;$i++){
			$linha = "";
			for($e=0;$e<$c;$e++){
				if($this->_tipo == 0){
					$cont = trim($dados[$i][$this->_estruturaRet[$e]['var']]);
				}else{
					$cont = $dados[$i][$this->_estruturaRet[$e]['var']];
				}
				$linha .= $cont.$this->_separador;
			}
			fwrite($file, $linha."\n");
		}
		fclose($file);
		
	}
	
	function gravaArquivoTipo3($tipo,$arq,$dados){
		if($this->_arqTipo3 == null){
//log::gravaLog("integra_txt",'integratxt: '.$this->_dirRet.$arq);
			$this->_arqTipo3 = fopen($this->_dirRet.$arq, "w");
		}
		
		$c = count($this->_estruturaRet[$tipo]);
		$q = count($dados);
		if($q==0 || $c==0){
			die("(integratxt)(gravaArquivoTipo3)(tipo: $tipo)(c: $c)(q: $q) Sem estrutura ou sem dados");
		}
//log::gravaLog("integra_txt", "Classe integratxt, tipo: $tipo, grava arquivo ".$this->_dirRet.$arq." Quant dados: $q");
		for($i=0;$i<$q;$i++){
			//$linha = $fixo;
			$linha = '';
			for($e=0;$e<$c;$e++){
				$cont = trim($dados[$i][$this->_estruturaRet[$tipo][$e]['var']]);
				if($this->_estruturaRet[$tipo][$e]['preencher'] != ''){
					if($this->_estruturaRet[$tipo][$e]['alin'] == 'E' || $this->_estruturaRet[$tipo][$e]['alin'] == 'I'){
						$alinhamento = 'I';
					}else{
						$alinhamento = 'F';
					}
					//$alinhamento = $this->_estruturaRet[$tipo][$e]['alin'] == 'D' ? 'I' : 'F';
//echo "Alin: ".$this->_estruturaRet[$tipo][$e]['alin']." $alinhamento <br>";
//echo "VAR: ".$this->_estruturaRet[$tipo][$e]['var']."<br>";
					$cont = preenche($cont, $this->_estruturaRet[$tipo][$e]['tam'], $this->_estruturaRet[$tipo][$e]['preencher'],$alinhamento);
				}
				$linha .= $cont.$this->_separador;
			}
			fwrite($this->_arqTipo3, $tipo.$linha."\n");
		}
		//fclose($file);	
	}
	
	function fechaArquivoTipo3(){
		fclose($this->_arqTipo3);
		$this->_arqTipo3 = null;
	}
	
	function setEstrutRet($param){
		$tipoReg = $param['fixo'][0];
		$this->_tamIdLinhaRet = strlen("$tipoReg");
		$quant = count($param['var']);
		if(count($param['var']) != count($param['pos']) || count($param['var']) != count($param['tam'])){
			die("Quantidade de itens diferentes na estrutura RETORNO tipo $tipoReg, por favor verifique!");
		}

		for($i=0;$i<$quant;$i++){
			$this->_estruturaRet[$tipoReg][$i]['var'] 		= isset($param['var'][$i]) 			? $param['var'][$i] 		: '';
			$this->_estruturaRet[$tipoReg][$i]['pos'] 		= isset($param['pos'][$i]) 			? $param['pos'][$i] 		: '';
			$this->_estruturaRet[$tipoReg][$i]['tam'] 		= isset($param['tam'][$i]) 			? $param['tam'][$i] 		: '';
			$this->_estruturaRet[$tipoReg][$i]['alin']  	= isset($param['alin'][$i]) 		? $param['alin'][$i] 		: '';
			$this->_estruturaRet[$tipoReg][$i]['preencher'] = isset($param['preencher'][$i]) 	? $param['preencher'][$i] 	: '';
			$this->_estruturaRet[$tipoReg][$i]['fixo'] 		= isset($param['fixo'][$i]) 		? $param['fixo'][$i] 		: '';
		}
	}
}