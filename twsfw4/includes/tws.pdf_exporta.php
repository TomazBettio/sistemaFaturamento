<?php
/*
 * Data Criacao 16/04/2019
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Realiza a exportação de uma tabela para PDF
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

//include_once($config['include'].'html2pdf/html2pdf.class.php');
include_once($config['include'].'vendor/autoload.php');
use Spipu\Html2Pdf\Html2Pdf;

class pdf_exporta{
	
	//Orienteção Portrait (P) or Lanscape (L)
	private $_orientação = 'P';
	
	//Formato da página (A4/A3/A2....)
	private $_paginaFormato = 'A4';
	
	//Tipo de saída (I: Abre o pdf gerado no navegador. D: Abre a janela para você realizar o download do pdf. F: Salva o pdf em alguma pasta do servidor.)
	private $_tipoSaida;
	
	//HTML a ser convertido
	private $_html;
	
	//Titulos cabeçalho
	private $_cab;
	
	//Dados
	private $_dados;
	
	//Tipo dos campos
	private $_tipos;
	
	//Campos
	private $_campos;
	
	// Conteudos a adicionar
	private $_conteudo;
	
	//Inclui paginação
	private $_paginacao = true;
	
	//Header
	private $_header = '';
	
	//Altura do Header
	private $_headerAlt = 7;
	
	function __construct($param = array()){
		$this->_orientação = verificaParametro($param, 'orientacao', 'P');
		$this->_paginaFormato = verificaParametro($param, 'tamanhoPagina', 'A4');
		$this->_tipoSaida = verificaParametro($param, 'tipoSaida', 'F');
		$this->_paginacao = verificaParametro($param, 'paginacao', true);
		$this->_conteudo = array();
	}
	
	function grava($destino){
		$html = '';
//echo $this->abre();
//echo $this->_html;
//echo $this->fecha();
		if(!empty(trim($this->_html))){
			ob_start();
				echo $this->abre();
				echo $this->_html;
				echo $this->fecha();
				
				$html = ob_get_contents();
			ob_end_clean();
		}
//echo "---------------------------------------------------------------\n\n\n\n\n\n$html\n---------------------------------------------------------------\n\n\n\n\n\n ";
		if(count($this->_conteudo) > 0){
			foreach ($this->_conteudo as $conteudo){
				$html .= $conteudo.'<br>';
			}
		}
		
		if(!empty($destino)){
			$this->gerar_pdf2($html, $destino);
		}else{
			return $html;
		}
	}
	
	function gerar_pdf2($html, $destino){
		try
		{
			if(file_exists($destino)){
				unlink($destino);
			}
			//$html2pdf = new HTML2PDF($this->_orientação,$this->_paginaFormato,'pt', true, 'UTF-8', array(0, 0, 0, 0));
			$html2pdf = new Html2Pdf($this->_orientação,$this->_paginaFormato,'pt', true, 'UTF-8', array(0, 0, 0, 0));
			/* Abre a tela de impressão */
			//$html2pdf->pdf->IncludeJS("print(true);");
			
			//$html2pdf->pdf->SetDisplayMode('real');
			$html2pdf->pdf->SetDisplayMode('fullpage');
			
			/* Parametro vuehtml = true desabilita o pdf para desenvolvimento do layout */
			$html2pdf->writeHTML($html, false);
			
			/* Abrir no navegador */
			$html2pdf->Output($destino, $this->_tipoSaida);
			
			/* Salva o PDF no servidor para enviar por email */
			//$html2pdf->Output('caminho/boleto.pdf', 'F');
			
			/* Força o download no browser */
			//$html2pdf->Output('boleto.pdf', 'D');
		}catch(HTML2PDF_exception $e) {
			echo $e;
			exit;
		}
	}
	
	//--------------------------------------------------------- SET -------------------------------------------
	
	function setHTML($html){
		if(!empty(trim($html))){
			$this->_html = $html;
		}
	}
	
	function setConteudo($conteudo){
		if(!empty(trim($conteudo))){
			$this->_conteudo[] = $conteudo;
		}
	}
	
	public function setHeader($html, $altura = 20){
		$this->_header = $html;
		$this->_headerAlt = $altura;
	}
	
	//-------------------------------------------------------- GET --------------------------------------------
	
	private function abre(){
		global $nl;
		$pagincao = '';
		$header = '';
		if(!empty($this->_header)){
			$header .= '<page_header>'.$nl;
			$header .= $this->_header;
            $header .= '</page_header>'.$nl;
		}
		if($this->_paginacao){
			$pagincao = '
				<page_footer>
					<div align="right"><b>Página: [[page_cu]]/[[page_nb]]</b></div>
				</page_footer>
					';
		}
		
		$ret = '
		<style type="text/css">
		<!--
		table
		{
			width:100%;
		}
		.cp {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
			color: #000;
			font-weight:normal;
		};
		
		table, td, th
		{
			padding: 1;
			border: 1px solid;
		}
		table.page_header {width: 100%; border: none; padding: 2mm }
		.classe_header{padding: 0; border: none;}
		-->
		</style>
<page backtop="'.$this->_headerAlt.'mm" backbottom="7mm" backleft="11mm" backright="10mm" style="font-size: 9px; font-weight: normal;">
'.$header.'
'.$pagincao.'
		';
//<page backtop="7mm" backbottom="7mm" backleft="11mm" backright="10mm" style="font-size: 9px; font-weight: normal;">
		return $ret;
	}
	
	private function fecha(){
		$ret = '';
		$ret .= '</page>';
		
		return $ret;
	}

}