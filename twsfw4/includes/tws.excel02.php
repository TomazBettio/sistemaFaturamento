<?php
/*
 * Data Criacao 22/01/2018
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Classe para utilizar a biblioteca PhpSpreadsheet
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

//include_once($config["include"].'PhpSpreadsheet/autoload.php');
include_once($config["include"].'phpExcel/PHPExcel.php');

class excel02{
	
	// Objeto excel
	private $_excel;
	
	// Arquivo a ser gravado
	private $_arquivo;
	
	// Colunas
	private $_colunas = array();
	
	//worksheet
	private $_worksheet = array();
	
	//Worksheet de trabalho
	private $_wsSetada;
	
	//dados cabeçalho
	private $_dadosCabecalho = [];
	
	function __construct($arquivo = ''){
		$this->_excel = new PHPExcel();
		$this->_excel->getProperties()->setCreator("www.thielws.com.br");
		$this->_arquivo = $arquivo;
		//$this->_worksheet[0] = 0;
		$this->_wsSetada = 0;

	}			

	function setArquivo($arquivo){
		$this->_arquivo = $arquivo;
	}
	
	public function setCabecalho($dados){
	    $this->_dadosCabecalho = $dados;
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
			//$this->_excel->getActiveSheet()->setTitle('ok '.$index);
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
	
	function setDados($cab,$dados,$campos, $tipo = array()){
	    $linha = 1;
	    $this->setColunas($cab);
	    if(count($this->_dadosCabecalho) > 0){
	        foreach ($this->_dadosCabecalho as $cabecalho){
	            $coluna = 0;
	            foreach ($cabecalho as $col){
	                //$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$this->_wsSetada][$coluna].$linha, $col);
	                //$this->_excel->getActiveSheet()->getStyle($this->_colunas[$this->_wsSetada][$coluna].$linha)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $this->_excel->getActiveSheet()->setCellValueExplicit($this->_colunas[$this->_wsSetada][$coluna].$linha, $col,PHPExcel_Cell_DataType::TYPE_STRING);
	                
	                $coluna++;
	            }
	            $linha++;
	        }
	    }
	    $linha_cab = $linha;
	    $linha++;
	    
		if(count($dados) > 0 && is_array($dados)){
			foreach ($dados as $dado){
				$coluna = 0;
				foreach ($campos as $c){
	//echo "Coluna: ".$this->_colunas[$this->_wsSetada][$coluna].$linha." - ".$dado[$c]."<br>\n";
					$valor = '';
					if(isset($dado[$c])){
						if(isset($tipo[$coluna]) && $tipo[$coluna] == 'D'){
							$valor = datas::dataS2D($dado[$c]);
						}else{
							$valor = strip_tags($dado[$c]);
						}
					}
					$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$this->_wsSetada][$coluna].$linha, $valor);
					if(isset($tipo[$coluna]) && $tipo[$coluna] == 'V'){
						$this->_excel->getActiveSheet()->getStyle($this->_colunas[$this->_wsSetada][$coluna].$linha)->getNumberFormat()->setFormatCode('###,###,##0.00');
					}
					if(isset($tipo[$coluna]) && $tipo[$coluna] == 'T'){
					    $this->_excel->getActiveSheet()->getStyle($this->_colunas[$this->_wsSetada][$coluna].$linha)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					}
					$coluna++;
				}
				$linha++;
			}
		}
		foreach ($tipo as $coluna => $tipo_atual){
		    //esconde as colunas do tipo HD
		    if($tipo_atual == 'HD'){
		        $this->_excel->getActiveSheet()->getColumnDimension($this->_colunas[$this->_wsSetada][$coluna])->setVisible(false);
		    }
		}
		$this->setCabColunas($cab, $linha_cab);
		$this->formataTitulo($linha_cab);
	}
	
	function setCabColunas($cab, $linha = 1){
	    $i = 0;
	    foreach ($cab as $c){
	        $c = str_replace('<br>', ' ', $c);
	        $this->_excel->getActiveSheet()->setCellValue($this->_colunas[$this->_wsSetada][$i].$linha, $c);
	        $this->_excel->getActiveSheet()->getColumnDimension($this->_colunas[$this->_wsSetada][$i])->setAutoSize(true);
	        $i++;
	    }
	}
	
	function setColunas($cab){
		$this->_colunas[$this->_wsSetada]= array();
		$col = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$q = strlen($col);
		$add = '';
		$colAdd = 0;
		$indCol = 0;
		for($i=0;$i<count($cab);$i++){
			if($indCol > ($q-1)){
				$indCol = 0;
				$add = substr($col, $colAdd,1);
				$colAdd++;
			}
			$this->_colunas[$this->_wsSetada][] = $add.substr($col, $indCol,1);
			$indCol++;
		}
		//print_r($this->_colunas[$this->_wsSetada]);
	}

	
	function grava($compactar = false){
		$objWriter = PHPExcel_IOFactory::createWriter($this->_excel, 'Excel2007');
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
	
	function formataTitulo($linha){
	    $ini = $this->_colunas[$this->_wsSetada][0].$linha;
	    $fim = $this->_colunas[$this->_wsSetada][count($this->_colunas[$this->_wsSetada])-1].$linha;
		$this->_excel->getActiveSheet()->getStyle($ini.':'.$fim)->applyFromArray(
				array(
						'font'    => array(
								'bold'      => true
						),
						'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						),
						'borders' => array(
								'top'     => array(
										'style' => PHPExcel_Style_Border::BORDER_THIN
								)
						),
						'fill' => array(
								'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
			  			'rotation'   => 90,
								'startcolor' => array(
										'argb' => 'FFA0A0A0'
								),
								'endcolor'   => array(
										'argb' => 'FFFFFFFF'
								)
			 		)
				)
				);
	}
	
	function setWidthCouluna($i, $tamanho){
	   $col = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	   $this->_excel->getActiveSheet()->getColumnDimension($col[$i])->setAutoSize(false);
	   $this->_excel->getActiveSheet()->getColumnDimension($col[$i])->setWidth($tamanho);
	}
	
	function copiarPaginaExcelExterior($arquivo, $index_pagina = false, $nome = ''){
	    $objPHPExcel2 = PHPExcel_IOFactory::load($arquivo);
	    
	    if($index_pagina !== false || !empty($nome)){
	        if($index_pagina !== false){
	            $pagina = $objPHPExcel2->getSheet($index_pagina);
	        }
	        else{
	            $pagina = $objPHPExcel2->getSheetByName($nome);
	        }
	        $this->_excel->addExternalSheet($pagina);
	    }
	    
	}
}