<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class importar_metas{
    var $funcoes_publicas = array(
        'index' 		        => true,
        'ajax'                  => true,
    );
    
    public function __construct(){
        
    }
    
    public function index(){
        //verificacao_pmc
        $arquivo = "";
        $input_file_type = PHPExcel_IOFactory::identify($arquivo);
        $obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
        $obj_reader->setReadDataOnly(true);
        
        $objPHPExcel = $obj_reader->load($arquivo);
        $sheetCount = $obj_reader->getSheetCount();
        var_dump($sheetCount);
        die();
        
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $max_lin = $objWorksheet->getHighestRow();
        $max_col = $objWorksheet->getHighestColumn();
        
        return '';
    }
}