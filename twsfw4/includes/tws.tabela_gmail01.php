<?php
/*
* Data Criacao: 17/07/2019
* Autor: Thiel
*
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

/*
 * Baseado nas 12 colunas do bootstrap
 */

class tabela_gmail01{
	
	private $_retorno;
	
	//Quantidade total de colunas
	private $_quantColunas;
	
	private $_corBorda;
	private $_corFundoTitulo;
	private $_corFonteTitulo;
	
	//Alinhamento do título
	private $_alinhamentoTitulo;
	
	//Controle de fechamento <TD>
	private $_controleTD = false;
	
	//Controle de fechamento <TH>
	private $_controleTH = false;
	
	//fonte
	private $_fonte = '';
	
	//Imprime o tamanho da TD?
	private $_tamanhoTD = false;
	
	//Tamanho da tabela
	private $_tamanho;

	function __construct($param = array()){
		$this->_quantColunas 		= verificaParametro($param, 'colunas', 12);
		$this->_corBorda 			= verificaParametro($param, 'corBorda', '#000000');
		$this->_corFundoTitulo 		= verificaParametro($param, 'corFundoTitulo', '#000000');
		$this->_corFonteTitulo 		= verificaParametro($param, 'corFonteTitulo', '#FFF');
		$this->_alinhamentoTitulo 	= verificaParametro($param, 'alinhamentoTitulo', 'center');
		$this->_fonte 				= verificaParametro($param, 'fonte','font-family: Verdana, Geneva, sans-serif;');
		$this->_tamanhoTD 			= verificaParametro($param, 'tamanhoTD', false);
	}
	
	function __toString(){
		return $this->_retorno;
	}
	
	function abreTabela($tam){
		global $nl;
		$tam = $tam > 0 ? $tam : 800;
		$this->_tamanho = $tam;
		$this->_retorno .= '<table width="'.$tam.'" border="0" align="center" cellpadding="5" cellspacing="0" rules="all" style="border: 1px solid '.$this->_corBorda.'; border-collapse: collapse;">'.$nl;
	}
	
	function fechaTabela(){
		global $nl;
		$this->_retorno .= '</table>'.$nl;
	}
	
	function addBR(){
		global $nl;
		$this->_retorno .= '<br>'.$nl;
	}
	
	function abreTR($titulo = false){
		global $nl;
		if($titulo){
			$ret = '	<tr style="'.$this->_fonte.' font-size: 14px; font-weight: bold;	color: '.$this->_corFonteTitulo.'; background-color: '.$this->_corFundoTitulo.'; text-align: '.$this->_alinhamentoTitulo.';	border: 1px solid '.$this->_corBorda.';">'.$nl;
		}else{
			$ret = '	<tr>'.$nl;
		}
		
		$this->_retorno .= $ret;
	}

	function fechaTR(){
		global $nl;
		//Se <TD> aberta, fecha
		if($this->_controleTD){
			$this->fechaTD();
		}
		if($this->_controleTH){
			$this->fechaTH();
		}
		$this->_retorno .= '	</tr>'.$nl;
	}
	
	function setColunasTotais($quant = 0){
		$this->_quantColunas = $quant;
	}
	function addTitulo($titulo, $colunas = 0){
		global $nl;
		
		$colunas = $colunas == 0 ? $this->_quantColunas : $colunas;
		
		$this->abreTR(true);
		$this->_retorno .= '		<th colspan="'.$colunas.'" scope="col" style="text-align: '.$this->_alinhamentoTitulo.'; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 1px; border-left-width: 0px; border-top-style: none; border-right-style: none; border-bottom-style: solid; border-left-style: none; border-top-color: '.$this->_corBorda.'; border-right-color: '.$this->_corBorda.'; border-bottom-color: #FFF; border-left-color: '.$this->_corBorda.';">'.$titulo.'</th>'.$nl;
		$this->fechaTR(true);
	}
	
	function abreTD($texto, $colunas, $alinhamento = 'esquerda', $titulo = false, $corFundo = ''){
		global $nl;
		
		//Se <TD> aberta, fecha
		if($this->_controleTD){
			$this->fechaTD();
		}
		
		switch ($alinhamento) {
			case 'D':
				$alin = 'right';
				break;
			case 'C':
				$alin = 'center';
				break;
			case 'E':
				$alin = 'left';
				break;
			case 'direita':
				$alin = 'right';
				break;
			case 'centro':
				$alin = 'center';
				break;
			default:
				$alin = 'left';
				break;
		}
		$cor = '';
		if($corFundo != ''){
			$cor =  ' bgcolor="'.$corFundo.'" ';
		}
		
		$tamanho = '';
		if($this->_tamanhoTD){
			$tamanho = 'width: 25%; ';
		}
		
		if($titulo){
			$ret = '<td colspan="'.$colunas.'" '.$cor.' scope="col" style="'.$tamanho.'border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: '.$this->_corBorda.';">'.$nl;
		}else{
			$ret = '<td colspan="'.$colunas.'" '.$cor.' style="'.$tamanho.$this->_fonte.'	font-size: 12px; text-align: '.$alin.'; border: 1px solid '.$this->_corBorda.';">'.$nl;
		}
		$this->_retorno .= $ret;
		$this->_retorno .= "	".$texto.$nl;
		$this->_controleTD = true;
	}

	function fechaTD(){
		global $nl;
		$this->_retorno .= '</td>'.$nl;
		$this->_controleTD = false;
	}
		
	function abreTH($texto,$colunas){
		global $nl;
		$this->_retorno .= '<th colspan="'.$colunas.'" align="center" scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: '.$this->_corBorda.';">'.$nl;
		$this->_retorno .= '	<div align="center">'.$texto.'</div>'.$nl;
		$this->_controleTH = true;
	}

	function fechaTH(){
		global $nl;
		$this->_retorno .= '</th>'.$nl;
		$this->_controleTH = false;
	}
	
	function termos(){
		$ret = '<br><hr />';
		$ret .= '<table width="800" border="0" align="center" cellpadding="5" cellspacing="0">';
		$ret .= '  <tr>';
		$ret .= '    <td align="center" style="'.$this->_fonte.' font-size: 9px; color: #999;">ATEN&Ccedil;&Atilde;O: </td>';
		$ret .= '  </tr>';
		$ret .= '  <tr>';
		$ret .= '    <td style="'.$this->_fonte.' font-size: 9px; color: #999;"> Esta &eacute; uma mensagem autom&aacute;tica. Ela e qualquer anexo existente s&atilde;o confidenciais para o destinat&aacute;rio descrito. Se voc&ecirc; n&atilde;o &eacute; o destinat&aacute;rio, &eacute; ilegal copiar, editar, transmitir, divulgar ou usar qualquer parte desta mensagem ou de seus arquivos anexos. O conte&uacute;do desta mensagem pode n&atilde;o refletir a opini&atilde;o da companhia, haja vista ser poss&iacute;vel sua altera&ccedil;&atilde;o pelo pr&oacute;prio destinat&aacute;rio ou terceiros. E ainda, n&atilde;o poder&aacute; ser respondida ao remetente, pois trata-se de um caminho inv&aacute;lido para contato.</td>';
		$ret .= '  </tr>';
		$ret .= '  <tr>';
		$ret .= '    <td style="'.$this->_fonte.' font-size: 9px; color: #999;">Todos os nossos e-mails tem link direto para o nosso site, e em nenhuma hip&oacute;tese trazem arquivos anexos execut&aacute;veis ou links para download. Pedimos que desconsidere qualquer e-mail enviado com estas caracter&iacute;sticas.</td>';
		$ret .= '  </tr>';
		$ret .= '</table>';
		
		$this->_retorno .= $ret;
	}
}