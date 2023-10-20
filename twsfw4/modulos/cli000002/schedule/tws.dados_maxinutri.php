<?php
/*
 * Data Criacao: 21/07/2023
 * Autor: Alex Cesar
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class dados_maxinutri{
    
    private $_caminho = "/mnt/pedtemp/MAXINUTRI/";
    //'\\modenav2\edi\in_out\MAXINUTRI';
    
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	
	function __construct(){
	    set_time_limit(0);	    
	}
	
	function index(){
	}
	
	function schedule($parametro)
	{
	    //global $config;
	    echo "Criando arquivo csv - ";
	  //  for($i = 7; $i--;$i>0){
	        $data = datas::getDataDias(-1);
    	   // $data = date('Ymd')-1;
    	   
    	    $nome_arquivo = "MAXINUTRI $data - $data.csv";
    	    //$nome_arquivo = "teste.csv";
    	    echo "Nome do arquivo: $nome_arquivo - ";
    	    
    	    // $caminho_arquivo = $config['base'] . "arquivos/$nome_arquivo";
    	    $caminho_arquivo = $this->_caminho . "$nome_arquivo";
    	    echo "local do arquivo: $caminho_arquivo - ";
    	    
    	    $fp = fopen($caminho_arquivo,'w');
    	    fwrite($fp,$this->geraCSV(datas::dataS2D($data)));
    	    fclose($fp);
    	    
    	    echo "Escrevi arquivo.";
	//    }
	    
	}
	
	private function getDados($data)
	{
	    $ret = [];
	    
	    $sql = "
                select
                to_char(dtsaida,'YYYY-MM-DD')   as DHEMI,
                cgcfilial                       as EMIT_CNPJ,
                'GAUCHAFARMA MEDICAMENTOS LTDA' as EMIT_NOME,
                'PORTO ALEGRE'                  as EMIT_CIDADE,
                uffilial                        as EMIT_UF,
                lpad(replace(replace(replace(replace(cgc, '.', ''), '-', ''), '/', ''), ' ', ''),14,0) as DEST_CNPJ, 
                cep                             as DEST_CEP,
                numendereco                     as DEST_NRO,
                uf                              as DEST_UF,
                bairro                          as DEST_XBAIRRO,
                endereco                        as DEST_XLGR,
                municipio                       as DEST_XMUN,
                ie                              as DEST_IE,
                cliente                         as DEST_XNOME,
                ''                              as DEST_XFANT,
                pcmov.numseq                    as PROD_ITEM,
                codauxiliar                     as PROD_CEAN,
                pcmov.codfiscal                 as CFOP,
                codprod                         as PROD_CPROD,
                qt                              as PROD_QCOM,
                unidade                         as PROD_UCOM,
                descricao                       as PROD_XPROD,
                pcnfsaid.numnota                as NNF,
                serie                           as SERIE,
                chavenfe                        as NFE_ID
                
                from pcnfsaid
                       left outer join pcmov on (pcnfsaid.numtransvenda = pcmov.numtransvenda)
                where pcnfsaid.dtsaida between to_date('$data','DD/MM/YYYY') and to_date('$data','DD/MM/YYYY')
                and pcnfsaid.codfilial = 1
                and pcnfsaid.condvenda = 1
                and pcnfsaid.codfilialnf = 1
                and pcmov.codprod in (select codprod from pcprodut where codfornec = 18790 and codepto <> 4 /* BRINDES */)
                and pcnfsaid.dtcancel IS NULL
                and pcmov.codoper = 'S'
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
	private function geraCSV($data)
	{
	    $ret = '';
	    $dados = $this->getDados($data);
	    $ret.=implode(',',$this->_colunas) . "\r\n";
	    foreach ($dados as $dado){
	        foreach($this->_colunas as $coluna){
	            $ret .= "\"".$dado[$coluna]."\",";
	        }
	        $ret.="\r\n";
	    }
	    return $ret;
	}
	
}