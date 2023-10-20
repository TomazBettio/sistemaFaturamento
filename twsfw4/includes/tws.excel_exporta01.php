<?php
/*
 * Data Criacao 24/07/2023
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Exporta de maneira flexivel, sem se basear em uma tabela
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

include_once($config["include"].'PhpSpreadsheet/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class excel_exporta01{
	
	// Objeto excel
	private $_excel;
	
	// Arquivo a ser gravado
	private $_arquivo;
	
	// Colunas
	private $_colunas = [];
	
	//worksheet
	private $_worksheet = [];
	
	//Worksheet de trabalho
	private $_wsSetada;
	
	//Linha a ser impressa
	private $_linha = 0;
	
	public function __construct($param = []){
		$this->_excel = new Spreadsheet();
		
		$this->geraNomeArquivo($param);

		$criador = $param['criador'] ?? 'www.verticais.com.br';
		$this->_excel->getProperties()->setCreator($criador);
		
		$modificadoPor = $param['modificadoPor'] ?? 'www.verticais.com.br';
		$this->_excel->getProperties()->setLastModifiedBy($modificadoPor);
		
		$titulo = $param['titulo'] ?? 'www.verticais.com.br';
		$this->_excel->getProperties()->setTitle($titulo);
		
		$propriedades = $param['propriedades'] ?? 'www.verticais.com.br';
		$this->_excel->getProperties()->setSubject($propriedades);
		
		$descricao = $param['descricao'] ?? 'www.verticais.com.br';
		$this->_excel->getProperties()->setDescription($descricao);
		
		$keyWords = $param['kewWords'] ?? 'www.verticais.com.br';
		$this->_excel->getProperties()->setKeywords($keyWords);
		
		$categoria = $param['categoria'] ?? 'www.verticais.com.br';
		$this->_excel->getProperties()->setCategory($categoria);
		
		$this->_wsSetada = 0;
		$this->setColunas(200);
	}			

	public function setArquivo($arquivo){
		$this->_arquivo = $arquivo;
	}
	
	public function addWorksheet($index = NULL, $titulo = ''){
		$titulo = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $titulo);
		$titulo = tirarAcentos($titulo);
		$titulo = substr($titulo, 0, 30);
		if($index != 0 && $index != null){
			$this->_excel->createSheet($index);
		}
		$kws = count($this->_worksheet);
		if($index == NULL){
			$index = $kws;
		}
		
		$this->_worksheet[$kws] = $index;
		$this->_excel->setActiveSheetIndex($kws);
		if($titulo != ''){
			$this->_excel->getActiveSheet()->setTitle($titulo);
		}
	}
	
	public function setWSAtiva($index){
		if(isset($this->_worksheet[$index])){
			$this->_excel->setActiveSheetIndex($index);
			$this->_wsSetada = $index;
		}else{
			$key = array_search($index, $this->_worksheet);
			if($key !== false){
				$this->_excel->setActiveSheetIndex($key);
				$this->_wsSetada = $key;
			}
		}
	}

	public function setTituloWS($titulo){
		if($titulo != ''){
			$this->_excel->getActiveSheet()->setTitle($titulo);
		}
		
	}
	
	public function gravaLinha($linha, $tipo = ''){
		switch ($tipo) {
			case 'cab':
			case 'cabecalho':
				$this->setLinhaCab($linha);
				break;
			default:
				$this->setLinha($linha);
				break;
		}
	}
	
	public function grava($compactar = false){
		$objWriter = new Xlsx($this->_excel);
		$objWriter->save($this->_arquivo);
		
		if($compactar){
			$zip = new ZipArchive();
			$arquivo = str_replace('xlsx', 'zip', $this->_arquivo);
			if ($zip->open($arquivo, ZipArchive::CREATE) !== true) {
				echo 'Falha ao criar o arquivo ' . $arquivo ."<br>\n" ;
				return false;
			} else {
				$zip->addFile($this->_arquivo, basename($this->_arquivo) );
				$zip->close();
				return true;
			}
		}
	}
	
	public function setFormatacao($tipos){
		if(count($tipos) > 0){
			foreach ($tipos as $i => $tipo){
				switch ($tipo) {
					case 'T':
						$this->_excel->getActiveSheet()->getStyle($this->_colunas[$i])->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
						break;
					case 'V':
						$this->_excel->getActiveSheet()->getStyle($this->_colunas[$i])->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
						break;
					case 'N':
						$this->_excel->getActiveSheet()->getStyle($this->_colunas[$i])->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
						break;
					case 'V4':
						$this->_excel->getActiveSheet()->getStyle($this->_colunas[$i])->getNumberFormat()->setFormatCode('#,##0.0000');
						break;
					case 'D':
						$this->_excel->getActiveSheet()->getStyle($this->_colunas[$i])->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
						break;
					case 'P':
						$this->_excel->getActiveSheet()->getStyle($this->_colunas[$i])->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
						break;
					case 'RS':
					    $this->_excel->getActiveSheet()->getStyle($this->_colunas[$i])->getNumberFormat()->setFormatCode('R$ #,##0.00_-');
					    break;
				}
			}
		}
	}
	
	public function aplicaCorLinha($linha,$cor, $colunas = 0){
		$cor = $this->getCor($cor);
		/*/
		 const COLOR_BLACK = 'FF000000';
		 const COLOR_WHITE = 'FFFFFFFF';
		 const COLOR_RED = 'FFFF0000';
		 const COLOR_DARKRED = 'FF800000';
		 const COLOR_DARKBLUE = 'FF000080';
		 const COLOR_GREEN = 'FF00FF00';
		 const COLOR_DARKGREEN = 'FF008000';
		 const COLOR_DARKYELLOW = 'FF808000';
		 const COLOR_MAGENTA = 'FFFF00FF';
		 const COLOR_CYAN = 'FF00FFFF';
		 /*/
		
		if($colunas > 0){
			$linha = $this->_colunas[0].$linha.':'.$this->_colunas[$colunas-1].$linha;
		}
		
		if(!empty($cor)){
			$this->_excel->getActiveSheet()->getStyle($linha)->applyFromArray(
				[
					'font'    => [
						'bold'      => true
					],
					'borders' => [
						'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
						'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
					],
					'fill' => [
						'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
						'color'   => ['argb' => $cor]
					]
				]
				);
		}
	}
	
	public function aplicaCorCelula($linha,$cor, $coluna){
	    $cor = $this->getCor($cor);
	    if(!empty($cor)){
	        $cor_borda = $this->getCor('preto');
	        $this->_excel->getActiveSheet()->getStyle( $this->_colunas[$coluna] . $linha)->applyFromArray(
	            [
	                'borders' => [
	                    'outline' => [
	                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR, 
                            'color' =>  [
                                'argb' => $cor_borda
                            ]
	                    ],
	                ],
	                'fill' => [
	                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
	                    'color'   => ['argb' => $cor]
	                ]
	            ]
	            );
	    }
	}
	    
	private function setLinhaCab($cab, $linha = 1){
	    $i = 0;
	    $this->_linha = $linha;
	    foreach ($cab as $c){
	        $c = str_replace('<br>', ' ', $c);
	        $this->_excel->getActiveSheet()->setCellValue($this->_colunas[$i].$linha, $c);
	        $this->_excel->getActiveSheet()->getColumnDimension($this->_colunas[$i])->setAutoSize(true);
	        $i++;
	    }
	    $this->formataTitulo($linha, $cab);
	}
	

	private function setLinha($linha){
		$this->_linha++;
		$i = 0;
		foreach ($linha as $c){
			$c = str_replace('<br>', ' ', $c);
			$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$i].$this->_linha, $c);
			$i++;
		}
	}
	

	
	private function formataTitulo($linha, $itens){
	    $ini = $this->_colunas[0].$linha;
	    $fim = $this->_colunas[count($itens)-1].$linha;
		$this->_excel->getActiveSheet()->getStyle($ini.':'.$fim)->applyFromArray(
				[
						'font'    => [
							'bold'      => true
						],
						'alignment' => [
							'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
						],
						'borders' => [
							'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
							'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
						],
						'fill' => [
							'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
			  				'rotation'   => 90,
							'startColor' => ['argb' => 'FFA0A0A0'],
							'endColor'   => ['argb' => 'FFFFFFFF']
			 			]
				]
				);
	}
	
	
	//------------------------------------------------------- UTEIS ----------------------
	private function setColunas($quant = 200){
		$this->_colunas = [];
		$col = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$q = strlen($col);
		$add = '';
		$colAdd = 0;
		$indCol = 0;
		for($i=0;$i<$quant;$i++){
			if($indCol > ($q-1)){
				$indCol = 0;
				$add = substr($col, $colAdd,1);
				$colAdd++;
			}
			$this->_colunas[] = $add.substr($col, $indCol,1);
			$indCol++;
		}
		//print_r($this->_colunas[$this->_wsSetada]);
	}
	
	private function geraNomeArquivo($param){
		global $config;
		$arquivo = 'excel.xlsx';
		if(isset($param['nomeArquivo'])){
			if(strpos($param['nomeArquivo'], '.xlsx') === false){
				$param['nomeArquivo'] .= '.xlsx';
			}
			$arquivo = $param['nomeArquivo'];
		}
		
		$caminho = '';
		if(isset($param['arquivo'])){
			if(strpos($param['arquivo'], '.xlsx') === false){
				$param['arquivo'] .= $arquivo;
			}
			$caminho = $param['arquivo'];
		}
		
		if(empty($caminho)){
			$this->_arquivo = $config['tempPach'].$arquivo;
		}else{
			$this->_arquivo = $caminho;
		}
	}
	
	private function getCor($cor){
	    $ret = $cor;
	    switch ($cor) {
	        case 'amarelo':
	            $ret = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_YELLOW;
	            break;
	        case 'azul':
	            $ret = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE;
	            break;
	        case 'verde':
	            $ret = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN;
	            break;
	        case 'preto':
	            $ret = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK;
	            break;
	    }
	    return $ret;
	}
}