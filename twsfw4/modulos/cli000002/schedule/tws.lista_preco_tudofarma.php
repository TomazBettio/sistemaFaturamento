<?php
/*
 * Data Criacao 02/10/2023
 * Autor: Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class lista_preco_tudofarma{

    private $_colunas=[];
    private $_caminho = "/mnt/pedtemp/tudofarma-kimberly/preco/";
    //"\\modenav2\edi\in_out\tudofarma-essity\preco";
    
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	function __construct(){
	    set_time_limit(0);
	    
	    $this->_colunas = ['UF','CNPJ_DIST','EAN','PF','ESTOQUE','DESCONTO_PERC','PRECO_SEM_IMPOSTO'];
	}
	
	function index(){
	}
	
	private function getDados()
	{
	    $ret = [];
	    $sql = "
                select
				    'RS'                            as UF,
				    89735070000100                  as CNPJ_DIST,
				    pcprodut.codauxiliar            as EAN,
				    pcdesconto.precofixopromocaomed as PF,
				    pcest.qtest                     as ESTOQUE,
				    0                               as DESCONTO_PERC,
				    pcdesconto.precofixopromocaomed as PRECO_SEM_IMPOSTO
				
				from
				    pcprodut,
				    pcest,
				    pcdesconto
				
				where
				        codpromocaomed = 68529
				    and pcprodut.codprod = pcdesconto.codprod
				    and pcprodut.codprod = pcest.codprod
				    and pcest.codfilial = 1
				
				union all
				
				select
				    'SC'                            as UF,
				    89735070000100                  as CNPJ_DIST,
				    pcprodut.codauxiliar            as EAN,
				    pcdesconto.precofixopromocaomed as PF,
				    pcest.qtest                     as ESTOQUE,
				    0                               as DESCONTO_PERC,
				    pcdesconto.precofixopromocaomed as PRECO_SEM_IMPOSTO
				
				from
				    pcprodut,
				    pcest,
				    pcdesconto
				
				where
				        codpromocaomed = 68530
				    and pcprodut.codprod = pcdesconto.codprod
				    and pcprodut.codprod = pcest.codprod
				    and pcest.codfilial = 1
";
	    $rows = query4($sql);
	    if(is_array($rows) && count($rows)>0){
	        foreach($rows as $row){
	            $temp = [];
	            foreach($row as $campo=>$value){
	                if(!is_int($campo)){
	                    $temp[$campo] = $value;
	                }
	            }
	            $ret[]=$temp;
	        }
	    }
	    return $ret;
	}
	
	//função criar arquivo csv
	private function geraCSV()
	{
	    $ret = '';
	    $dados = $this->getDados();
	    $ret.=implode(',',$this->_colunas) . "\r\n";
	    foreach ($dados as $dado){
	        foreach($this->_colunas as $coluna){
	            if($coluna == 'PF' || $coluna == 'PRECO_SEM_IMPOSTO')
	            {
	                $preco = explode('.', $dado[$coluna]);
	                $decimal = $preco[1] ?? 0;
	                $inteiro = $preco[0];
	                if(strlen($decimal)==1){
	                    $decimal = $decimal.'0';
	                }
	                if(strlen($inteiro)==1){
	                    $inteiro = '0'.$inteiro;
	                }
	                $ret .= "\"$inteiro,$decimal\",";
	            } else {
	                $ret .= "\"".$dado[$coluna]."\",";
	            }
	        }
	        $ret.="\r\n";
	    }
	    return $ret;
	}
	
	function schedule($parametro)
	{
	    //global $config;
	    echo "Criando arquivo csv - ";
	    $ano = date('Y');
	    $mes = date('m');
	    $dia = date('d');
	    $hora = date('H');
	    $minuto = date('i');
	    
	    $nome_arquivo = "tudofarma_gauchafarma_$dia$mes$ano$hora$minuto.csv";
	    echo "Nome do arquivo: $nome_arquivo - ";
	   // $caminho_arquivo = $config['base'] . "arquivos/$nome_arquivo";
	    $caminho_arquivo = $this->_caminho . "$nome_arquivo";
	    echo "local do arquivo: $caminho_arquivo - ";
	    
	    $fp = fopen($caminho_arquivo,'w');
	    fwrite($fp,$this->geraCSV());
	    fclose($fp);
	    echo "Escrevi arquivo.";
	}
}