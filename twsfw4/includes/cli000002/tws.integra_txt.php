<?php
/*
* Data Criação: 24/03/2015 - 17:12:35
* Autor: Thiel
*
* Descrição: Realiza a importação ou exportação de arquivos TXT
* 
* 
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class integra_txt{
	/*
	 * Caracter separador de campos, se vazio = posicao fixa
	 */
	private $_separador;
	
	/*
	 * Diretorio do arquivo 
	 */
	
	private $_dir;
	/*
	 * Estrutura do arquivo
	 */
	private $_estrutura;

	/*
	 * Nome do arquivo
	 */
	private $_arquivo;

	/*
	 * Tipo de estrutura de arquivo
	 * 0 => Separador
	 * 1 => Posicao fixa
	 */
	private $_tipo;	
	
	/*
	 * Ponteiro do arquivo
	 */
	private $_handle;
	
	/*
	 * Tipo de registro padrao (quando o mesmo não for informado - geralmente arquivos simples de um tipo só)
	 */
	private $_tipoPadrao;
	
	/*
	 * Indica que, quando utilizar separador, a linha começa ou não com separador
	 */
	private $_sepIni;
	
	//Indica se deve fazer debug
	private $_trace;
	
	function __construct($separador, $tipo, $dir = '', $arq = '', $trace = false){
		$this->_trace = $trace;
		$this->_handle = null;
		$this->_sepIni = false;
		$this->_tipoPadrao = 'UNICO';
		if($tipo == 0 && $separador == ''){
			die('Quando o tipo for 0 o parametro separador nao pode ficar em branco');
		}
		$this->_separador = $separador;
		$this->_tipo = $tipo;
		
		if($dir != ''){
			$this->_dir = $dir;
		}
		
		if($arq != ''){
			$this->_arquivo = $arq;
		}
	}
	
	function __destruct(){
		fclose($this->_handle);
	}

	function setDiretorio($dir){
		$this->_dir = $dir;
	}	
	
	function setArquivo($arq){
		$this->_arquivo = $arq;
	}
	
	function setSeparadorIni($sep){
		if($sep)
			$this->_sepIni = true;
		else
			$this->_sepIni = false;
	}
	
	function gravaArquivo($dados, $tipoRegistro = ''){
		if($tipoRegistro == ''){
			$tipoRegistro = $this->_tipoPadrao;
		}
		if($this->_handle == null){
			$this->_handle = fopen($this->_dir.$this->_arquivo, "w");
			if($this->_trace){
				echo "Arquivo Criado: ".$this->_dir.$this->_arquivo." <br>\n";
			}
		}
//print_r($this->_estrutura[$tipoRegistro]);
//print_r($dados);
		$c = count($this->_estrutura[$tipoRegistro]);
		$q = count($dados);
		if($q==0 || $c==0){
			die("(integratxt)(gravaArquivo)(tipo: $tipo)(c: $c)(q: $q) Sem estrutura ou sem dados");
		}
		for($i=0;$i<$q;$i++){
			$linha = '';
			if($this->_separador != '' && $this->_sepIni = true){
				$linha .= $this->_separador;
			}
			if($tipoRegistro != $this->_tipoPadrao){
				$linha .= "$tipoRegistro";
				if($this->_separador != ''){
					$linha .= $this->_separador;
				}
			}
			for($e=0;$e<$c;$e++){
				$variavel = $this->_estrutura[$tipoRegistro][$e]['var'];
				$conteudo = trim($dados[$i][$variavel]);
//echo "Variavel: $variavel \n Conteudo: $conteudo \n";
				if($this->_estrutura[$tipoRegistro][$e]['preencher'] !== ''){
					if($this->_estrutura[$tipoRegistro][$e]['alin'] == 'D'){
						$alinhamento = 'I';
					}else{
						$alinhamento = 'F';
					}
					$conteudo = preenche($conteudo, $this->_estrutura[$tipoRegistro][$e]['tam'], $this->_estrutura[$tipoRegistro][$e]['preencher'],$alinhamento);
				}
				$linha .= $conteudo.$this->_separador;
			}
			fwrite($this->_handle, $linha."\n");
		}		
		
	}

	/**
	* Indica qual a estrutura da linha do arquivo
	* (pode ser indicada mais de uma estrutura para arquivos complexos)
	*
	* @author	Alexandre Thiel
	* @access	public
	* @param	array	$param	Parametros da estrutura
	* 							$param['var'] 		-> nome da variavel
	* 							$param['pos'] 		-> posicao inicial do campo
	* 							$param['tam'] 		-> Tamanho do campo
	* 							$param['alin'] 		-> alinhamento (E-esquerda (preenche a direita) D - direita (preenche a esquerda))
	* 							$param['preencher'] -> caracter a ser utilizado para preencher e completar o tamanho
	* @return	void
	*/
	
	function setEstrutura($param, $tipoRegistro = ''){
		if($tipoRegistro == ''){
			$tipoRegistro = $this->_tipoPadrao;
		}
		$tipoReg = $tipoRegistro;
		$quant = count($param['var']);
		if(count($param['var']) != count($param['pos']) || count($param['var']) != count($param['tam'])){
			die("Quantidade de itens diferentes na estrutura RETORNO tipo $tipoRegistro, por favor verifique!");
		}
	
		for($i=0;$i<$quant;$i++){
			$this->_estrutura[$tipoReg][$i]['var'] 		= isset($param['var'][$i]) 			? $param['var'][$i] 		: '';
			$this->_estrutura[$tipoReg][$i]['pos'] 		= isset($param['pos'][$i]) 			? $param['pos'][$i] 		: '';
			$this->_estrutura[$tipoReg][$i]['tam'] 		= isset($param['tam'][$i]) 			? $param['tam'][$i] 		: '';
			$this->_estrutura[$tipoReg][$i]['alin']  	= isset($param['alin'][$i]) 		? $param['alin'][$i] 		: '';
			$this->_estrutura[$tipoReg][$i]['preencher'] = isset($param['preencher'][$i]) 	? $param['preencher'][$i] 	: '';
		}
	}
}