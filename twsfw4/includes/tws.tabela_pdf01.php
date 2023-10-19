<?php
/*
* Data Criacao: 02/05/2019
* Autor: Thiel
*
* Descrição: Gera tabela genérica para utilização em PDFs
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class tabela_pdf01{
	
	//Quantidade total de colunas
	protected $_quantColunas = 0;
	
	//Tamanho total da tabela
	protected $_width = 0;
	
	//Dados das colunas
	protected $_colunas = array();
	
	//Dados a serem apresentados
	protected $_dados = array();
	
	//Totais da tabela
	protected $_dadosTotais = array();
	
	//Titulo tabela
	protected $_titulo = '';
	
	//Sub titulo tabela
	protected $_subTitulo;
	
	//ID da tabela
	protected $_id;
	
	//Footer a ser impresso
	protected $_footer = '';
	
	//Indica se deve imprimir o cabecalho da tabela
	protected $_mostraCab = true;
	
	//Indica se deve mostrar borda na tabela
	protected $_border = true;
	
	//Indica se a tabeça terá stripe (listras)
	protected $_stripe = false;
	
	protected $_stripeCor1 = '#FFFFFF';
	protected $_stripeCor2 = '#efefef';
	
	//modo de conversão do pdf
	protected $_modoConversaoPdf = '';

	function __construct($param = []){
		$quantID = getAppVar('tabelaTWSquant') === false ? 1 : getAppVar('tabelaTWSquant');
		$this->_id = 'tabelaTWS'.$quantID;
		putAppVar('tabelaTWSquant', $quantID + 1);
		
		$this->_mostraCab = verificaParametro($param, 'mostraCab', true);
		$this->_border = verificaParametro($param, 'border', true);
		$this->_stripe = $param['stripe'] ?? false;
	}
	
	function __toString(){
		global $nl;
		$ret = '';
		
		if(isset($this->_titulo) && !empty($this->_titulo)){
			$ret .= '<h3>'.$this->_titulo;
			if(isset($this->_subTitulo)){
				$ret .= '<small>'.$this->_subTitulo.'</small>';
			}
			$ret .= '</h3>'.$nl;
		}
		
		//$ret .= $this->style();
		
		$border = ' border: 0px solid;';
		if($this->_border){
			$border = ' border: 1px solid;';
		}
		
		$ret .= '<table id= "'.$this->_id.'" align="center" cellspacing="0" style="width: 100%;'.$border.' border-collapse: collapse;">'.$nl;
		if($this->_mostraCab){
			$ret .= $this->getCabecalho();
		}
		$ret .= $this->getlinhas();
		if(count($this->_dadosTotais) > 0){
			$ret .= $this->getlinhas(true);
		}
		$ret .= '</table>'.$nl;
		
		if(!empty($this->_footer)){
			$ret .= '<br>'.$nl;
			$ret .= $this->_footer;
		}
		
		return $ret;
	}
	
	//------------------------------------------ SET -------------------------------------------------------------
	
	function setTabela($campos, $cab, $width = array(), $posicao = array(), $tipo = array()){
		$this->_quantColunas = 0;
		$this->_width = 0;
		if(!is_array($cab) || !is_array($campos) || count($cab) <= 0 || count($cab) <> count($campos)){
			die('Problemas na determinação da tabela PDF, favor entrar em contato com o desenvolvedor.');
		}
		
		foreach ($campos as $i => $campo){
			$this->_quantColunas++;
			$temp = array();
			$temp['campo'] 	= $campo;
			$temp['cab'] 	= $cab[$i];
			if($width[$i]){
				$temp['width'] 	= $width[$i];
				$this->_width += $width[$i];
			}
			$temp['posicao'] 	= isset($posicao[$i]) ? $posicao[$i] : 'E';
			$temp['tipo']	= isset($tipo[$i]) ? $tipo[$i] : 'T';
			
			$this->_colunas[$i] = $temp;
		}
		
		$this->ajustaTamanhoPercentual();
//print_r($cab);
//print_r($campo);
//print_r($width);
//print_r($posicao);
//print_r($tipo);

//print_r($this->_colunas);
	}
	
	function setDados($dadosTabela){
		if(is_array($dadosTabela)){
			$this->_dados = $dadosTabela;
		}
	}
	
	function setDadosTotais($dados){
		if(is_array($dados)){
			$this->_dadosTotais = $dados;
		}
	}
	
	function setTitulo($titulo, $sub = ''){
		$this->_titulo = $titulo;
		$this->_subTitulo = $sub;
	}
	
	public function setFooter($texto){
		if(!empty(trim($texto))){
			$this->_footer = $texto;
		}
	}
	
	public function setMostraCabecalho($mostra = true){
		if($mostra){
			$this->_mostraCab = true;
		}else{
			$this->_mostraCab = false;
		}
	}
	
	public function setStripe($stripe){
		$this->_stripe = $stripe === true ? true : false;
	}
	
	public function setCorStripe($cor1, $cor2){
		if(!empty($cor1)){
			$this->_stripeCor1 = $cor1;
		}
		if(!empty($cor2)){
			$this->_stripeCor2 = $cor2;
		}
	}
	//------------------------------------------ UTEIS -----------------------------------------------------------
	
	public function addColuna($param){
		$this->_quantColunas++;
		$temp = [];
		$temp['campo'] 	= $param['campo'];
		$temp['cab'] 	= $param['etiqueta'];
		if(isset($param['width']) && !empty($param['width'])){
			$temp['width'] 	= $param['width'];
			$this->_width += $param['width'];
		}
		$temp['posicao'] 	= isset($param['posicao']) && !empty($param['posicao']) ? $param['posicao'] : 'E';
		$temp['tipo']	= $param['tipo'];
		
		$this->_colunas[] = $temp;
		
		$this->ajustaTamanhoPercentual();
	}
	
	protected function ajustaTamanhoPercentual(){
		
		foreach ($this->_colunas as $i => $coluna){
			if($this->_width > 0){
				$tam = round(($coluna['width'] * 100) / $this->_width, 2);
			}else{
				$tam = round((100/$this->_quantColunas), 2);
			}
			$this->_colunas[$i]['wPercent'] = $tam;
		}
	}
	
	//------------------------------------------ TABELA ----------------------------------------------------------
	
	protected function getCabecalho(){
		global $nl;
		$ret = '';
		
		$ret .= '	<tr>'.$nl;
		foreach ($this->_colunas as $coluna){
			$ret .= '		<th scope="col" style="width:'.$coluna['wPercent'].'%; text-align: center;">';
			$ret .= '			<b>'.$coluna['cab'].'</b>';
			$ret .= '		</th>'.$nl;
		}
		$ret .= '	</tr>'.$nl;
		
		return $ret;
	}
	
	protected function getLinhas($total = false){
		global $nl;
		$ret = '';
		$dados = array();
		
		if($total){
			$dados[] = $this->_dadosTotais;
		}else{
			$dados = $this->_dados;
		}
		
		$stripe = false;
		
		if(count($dados) > 0){
			foreach ($dados as $dado){
				if($this->_stripe){
					$cor = $stripe ? $this->_stripeCor1 : $this->_stripeCor2;
					$stripe = $stripe ? false : true;
					$tr = '	<tr bgcolor="'.$cor.'">';
				}else{
					$tr = '	<tr>';
				}
				$ret .= $tr.$nl;
				foreach ($this->_colunas as $coluna){		
					$valorCampo = isset($dado[$coluna['campo']]) ? $dado[$coluna['campo']] : '';
					$neg = false;
					switch ($coluna['tipo']){
						case "V":
							//Valor (duas casas decimais)
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							$neg = $valorCampo < 0 ? true : false;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 2, ',', '.');
							}
							break;
						case "V4":
							//Valor (quatro casas decimais)
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							$neg = $valorCampo < 0 ? true : false;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 4, ',', '.');
							}
							break;
						case "N":
							//Valor inteiro
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							$neg = $valorCampo < 0 ? true : false;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 0, ',', '.');
							}
							break;
						case "D":
							//Data
							if($valorCampo != '' && $valorCampo != 0){
								$valorCampo = datas::dataS2D($valorCampo);
							}
							break;
						case "T":
						default:
							//Texto
							$valorCampo = ajustaCaractHTML($valorCampo);
							break;
					}
					//Se for negativo coloca o menos na frente
					if($neg){
						$valorCampo = '-'.$valorCampo;
					}
					switch ($coluna['posicao']) {
					case 'D':
						$alin = 'right';
						break;
					case 'C':
						$alin = 'center';
						break;
					case 'E':
						$alin = 'left';
						break;
					case 'J':
						$alin = 'justify';
						break;
					default:
						$alin = 'left';
						break;
				}
				
					$ret .= '		<td scope="col" style="width:'.$coluna['wPercent'].'%; text-align: '.$alin.';">';
					if(!empty($valorCampo)){
						if($total){
							$ret .= '			<b>'.$valorCampo.'</b>';
						}else{
							$ret .= '			'.$valorCampo;
						}
					}else{
						$ret .= '&nbsp;';
					}
					$ret .= '		</td>'.$nl;
				}
				$ret .= '	</tr>'.$nl;
			}
		}
		
		return $ret;}
	
	function addBR(){
		global $nl;
		$this->_retorno .= '<br>'.$nl;
	}
	
	function abreTR($titulo = false){
		global $nl;
		if($titulo){
			$ret = '	<tr style="font-family: Verdana, Geneva, sans-serif;	font-size: 14px; font-weight: bold;	color: #FFF; background-color: #063; text-align: center;	border: 1px solid #063;">'.$nl;	
		}else{
			$ret = '	<tr>'.$nl;
		}
		
		$this->_retorno .= $ret;
	}

	function fechaTR(){
		global $nl;
		$this->_retorno .= '	</tr>'.$nl;
	}
	
	function setColunasTotais($quant = 0){
		$this->_quantColunas = $quant;
	}
	function addTitulo($titulo){
		global $nl;
		$ret = '';
		
		$this->abreTR(true);
		$this->_retorno .= '		<th colspan="'.$this->_quantColunas.'" scope="col" style="border-top-width: 0px; border-right-width: 0px; border-bottom-width: 1px; border-left-width: 0px; border-top-style: none; border-right-style: none; border-bottom-style: solid; border-left-style: none; border-top-color: #063; border-right-color: #063; border-bottom-color: #FFF; border-left-color: #063;">'.$titulo.'</th>'.$nl;
		$this->fechaTR(true);
	}
	
	function abreTD($texto, $colunas, $alinhamento = 'esquerda', $titulo = false, $corFundo = ''){
		global $nl;
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
			case 'J':
				$alin = 'justify';
				break;
			default:
				$alin = 'left';
				break;
		}
		$cor = '';
		if($corFundo != ''){
			$cor =  ' bgcolor="'.$corFundo.'" ';
		}
		if($titulo){
			$ret = '<td colspan="'.$colunas.'" '.$cor.' scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">'.$nl;
		}else{
			$ret = '<td colspan="'.$colunas.'" '.$cor.' style="font-family: Verdana, Geneva, sans-serif;	font-size: 12px; text-align: '.$alin.'; border: 1px solid #063;">'.$nl;
		}
		$this->_retorno .= $ret;
		$this->_retorno .= $texto.$nl;
	}

	function fechaTD(){
		global $nl;
		$this->_retorno .= '</td>'.$nl;
	}
		
	function abreTH($texto,$colunas){
		global $nl;
		$this->_retorno .= '<th colspan="'.$colunas.'" align="center" scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">'.$nl;
		$this->_retorno .= '<div align="center">'.$texto.'</div>'.$nl;
	}

	function fechaTH(){
		global $nl;
		$this->_retorno .= '</th>'.$nl;
	}
	
	protected function style(){
		$ret = '
		<style type="text/css">
		<!--
		table
		{
			width:100%;
			border: 1px solid;
		}
		.cp {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
			color: #000;
			font-weight:normal;
		};
		
		table, td
		{
			padding:0;
		}
		-->
		</style>
		';
		
		return $ret;
	}
	
	public function setModoConversaoPdf($modo){
	    $modo = strtoupper($modo);
	    if(in_array($modo, ['HTML2PDF','WKHTMLTOPDF'])){
	        $this->_modoConversaoPdf = $modo;
	    }
	    else{
	    }
	}
	
	public function gerarPdf($arquivo, $cabecalho){
	    $htmlPDF = $this->__toString();
	    $paramPDF = [];
	    $paramPDF['orientacao'] = 'L';
	    $PDF = new pdf_exporta02($paramPDF);
	    $PDF->setModoConversao($this->_modoConversaoPdf);
	    if($cabecalho != ''){
	        $htmlPDF = $cabecalho . $htmlPDF;
	    }
	    $PDF->setHTML($htmlPDF);
	    $PDF->grava($arquivo);
	    unset($PDF);
	}
}