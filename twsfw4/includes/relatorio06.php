<?php
/*
* Data Criacao: 02/05/2019
* Autor: Thiel
*
* Classe para gerar relatorios com múltiplas seções
* 
* Alterções:
* 			18/09/19 - Thiel - Possibilidade de impressão de logo/titulo/parametros/data emissão
* 			20/01/22 - Thiel - Melhoria no relatorio05 para incluir o botão de 'CONFIGURACAO'
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class relatorio06{
	
	var $_dados;
	private $_dadosTfoot = array();
	
	// Arquivo excel a ser criado
	private $_arqExcel = '';
	
	// Link para o arquivo excel
	private $_linkExcel = '';
	
	// CABs para exportar para excel/PDF
	private $_cab;
	
	// Tipo de conteudo (exportar excel/PDF) T - Texto, N - Numero
	private $_tipo;
	
	// Tamanho da coluna para exportar excel/PDF
	private $_width;
	
	// Posição do texto para exportar excel/PDF
	private $_posicao;
	
	//---------------------------------------------------------------------------------------------
	
	// Indica se o relatório vai ser impresso (browser)
	private $_print = true;

	// Indice dos campos
	private $_campo;
	
	//Colunas para setar secções não informadas (repetir extrutura)
	private $_colunas;
	
	//-------------------------------------------------------------------------- Cores para as linhas
	private $_cores = [];
	private $_colunaCor = [];
	
	//-------------------------------------------------------------------------- Cores para as colunas
	private $_coresColunas = [];
	
	//-------------------------------------------------------------------------- Cores para as colunas (cores condicionais)
	private $_coresColunasIf = [];
	private $_coresColunasCondicional = [];
	
	
	//-------------------------------------------------------------------------- Ajustes
	function __toString(){
	}
	
	
	//------------------------------------------------------------------------------------------------------- SET -------------------
	
	function setPrint($print){
		if($print){
			$this->_print = true;
		}else{
			$this->_print = false;
		}
		$this->_browser->setPrint($print);
	}
	

	
	function setDadosTfoot($dados, $secao = 0){
		$this->_dadosTfoot[$secao] = $dados;
		$this->_browser[$secao]->setDadosTfoot($dados);
	}
	
	public function setCabecalhoPDF($param = array()){
		global $config;
		$this->_cabPDF['titulo'] 		= verificaParametro($param, 'titulo','');
		$this->_cabPDF['logo'] 			= isset($config['relatorios']['logoArquivo']) ? $config['relatorios']['logoArquivo'] : '';
		$this->_cabPDF['larguraLogo']	= isset($config['relatorios']['logoLargura']) ? $config['relatorios']['logoLargura'] : '';
		$this->_cabPDF['alturaLogo'] 	= isset($config['relatorios']['logoAltura']) ? $config['relatorios']['logoAltura'] : '';
		$this->_cabPDF['filtros'] 		= verificaParametro($param, 'filtros',array());
		$this->_cabPDF['emissao'] 		= verificaParametro($param, 'emissao', true);
		$this->_cabPDF['altura'] 		= verificaParametro($param, 'altura', 20);
	}
	
	
	
	//------------------------------------------------------------------------------------------------------- GET -------------------
	
	private function getHeaderPDF(){
		global $nl;
		$ret = '';
		$titulo = '<h2>'.$this->_cabPDF['titulo'].'</h2>'.$nl;
		$largura = !empty($this->_cabPDF['larguraLogo']) ? 'width="'.$this->_cabPDF['larguraLogo'].'"' : '';
		$altura = !empty($this->_cabPDF['larguraLogo']) ? 'height="'.$this->_cabPDF['larguraLogo'].'"' : '';
		
		if(count($this->_cabPDF['filtros']) > 0){
			$titulo .= $this->_filtro->parametrosString($this->_cabPDF['filtros']);
		}
		
		$ret .= '<table align="center" cellspacing="0" style="width: 100%; border: 0px; border-collapse: collapse;">'.$nl;
		$ret .= '	<tr>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 10%;border: 0px;"><img src="'.$this->_cabPDF['logo'].'" '.$largura.' '.$altura.'></td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 80%;border: 0px;">'.$titulo.'</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 10%;border: 0px;"><b>Emissão</b><br> '.date('d/m/Y').'</td>'.$nl;
		$ret .= '	</tr>'.$nl;
		$ret .= '</table>'.$nl;
//echo $ret;		
		return $ret;
	}
	
	function getParametros(){
		$ret = array();
		
		$parametros = $this->_sys020->getParametros($this->_programa);
		if(count($parametros) > 0){
			foreach ($parametros as $parametro){
				$ret[$parametro['parametro']] = $parametro['valor'];
			}
		}
		
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------------------- ADD -------------------
	

	
	
	function setDetalhes($link,$coluna,$funcao = 1){
		$this->_browser->setDetalhes($link,$coluna,$funcao);
	}
	

	
	function setSeleciona($VARIAVEL, $COLUNA, $LINK, $NAME = "browseForm"){
		$this->_browser->setSeleciona($VARIAVEL, $COLUNA, $LINK, $NAME);
	}
	
	
	public function addCorLinha($key, $cor, $secao = 0){
		if(!empty($key) && !empty($cor)){
			$this->_cores[$secao][$key] = $cor;
		}
	}
	
	
	//------------------------------------------------------------------------------------------- GET -------------------------------------------------
	
	function getProximaSessao($ant = false){
		$ret = 0;
		if($ant === false){
			$ret = count($this->_browser);
			if(isset($this->_browser[$ret])){
				$ret = $this->getProximaSessao($ret);
			}
		}else{
			$ret = $ant + 1;
			if(isset($this->_browser[$ret])){
				$ret = $this->getProximaSessao($ret);
			}
		}
		
		return $ret;
	}
			
	

	function copiaExcel($dir, $nomeArquivo){
		$ret = false;
		if(!empty(trim($this->_arqExcel)) && !empty(trim($dir)) && !empty(trim($nomeArquivo))){
			if(substr($dir, -1) != '\\' && substr($dir, -1) != '/'){
				$dir .= '/';
			}
			$ret = copy($this->_arqExcel, $dir.$nomeArquivo);
		}
		
		return $ret;
	}
	
	/**
	 * Indica se sera gerado arquivo PDF deste relatorio.
	 * No caso de shedule pode ser indicado o nome do arquivo.
	 * Se nao for indicado o nome sera cliente + usuario + programa
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	boolean	$excel	Indica se deve gerar excel
	 * @param	string	$nome	Nome do arquivo
	 * @return	void
	 *
	 * @version 0.01
	 */
	function toPDF($pdf,$nome = ''){
		global $config;
		
		if($pdf){
			$this->_toPDF = true;
		}else{
			$this->_toPDF = false;
		}
		
		if($nome == ''){
			$arquivo = funcoesusuario::getUsuario().".".$this->_programa.".pdf";
		}else{
			$arquivo = $nome.".pdf";
		}
		$this->_arqPDF = $config['temp'].$arquivo;
//echo "Arquivo1 : ".$this->_arqPDF." <br>\n";
		$this->_linkPDF = $config["tempURL"].$arquivo;
//echo "Arquivo2 : ".$this->_linkPDF." <br>\n";
	}
	
	/**
	 * 
	 * @param string $html
	 * @param number $altura - Altura em mm do header
	 */
	public function setHeaderPdf($html, $altura = 7){
		$this->_headerPDF = $html;
		$this->_headerAltPDF = $altura;
	}
	

	
	function agendaEmail($dia, $hora, $programa, $para,$titulo = '', $de = '', $msgAntes = '', $copiaOculta = '', $emailsender = array(),$embeddedImage = array(), $responderPara=array(), $teste = false){
		$anexos = array();
		$msg = "";
		if($this->_toExcel && $this->_quantDados[0] > 0){
			$excel = new excel($this->_arqExcel);
			$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
			$excel->grava();
			$anexos[] = $this->_arqExcel;
			unset($excel);
		}
		
		if($titulo == ""){
			$titulo = $this->_titulo;
		}
		
		if($msgAntes != ''){
			$msg .= $msgAntes;
		}
		
		if($this->_quantDados[0] > 0){
			if(strlen($msg) > 2000000 || count($this->_campo) > 56 || $this->_enviaTabelaCorpoEmail == false){
				$msg = "Segue anexo o relatório ".$titulo.".";
			}else{
				
				$msg .= $this->_browser;
			}
		}else{
			if($this->_textoSemDados != ""){
				$msg = $this->_textoSemDados;
			}else{
				$msg = "Não existem dados!";
			}
		}
		
		
		agendaEmail($dia, $hora, $programa, $para, $titulo, $msg, $anexos, $emailsender,$embeddedImage, $responderPara, $copiaOculta, $teste);
	}
	

	
	/*
	 * Retorna os campos utilizados no relatório
	 */
	function getCampos($secao = 0){
		return $this->_campo[$secao];
	}
	
	function getPathArquivoExcel(){
	    return $this->_arqExcel;
	}
}