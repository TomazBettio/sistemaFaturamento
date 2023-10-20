<?php
/*
* Data Cria��o: 01/09/2014 - 14:24:22
* Autor: Thiel
*
* Arquivo: schedule.faturamento.php
* 
* Relat�rio desenvolvido para ser o 8075 enviado diariamente para o M�rcio e Ivair - solicitante: Marcos
* 
* Atualizado: 24/05/2023 - Alex
* 
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class faturamento{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);

	// Nome do Programa
	var $_programa = '';
	// Teste do Schedule
	var $_teste = false;

	function __construct(){
		$this->_programa = '000002.faturamento';
	}			
	
	function index(){
	    set_time_limit(120);
	    $ret='';
	    
	    $relatorio = $this->criaRelatorio();
	    
	    $ret.=$relatorio;
	    
	    return $ret;
	}
	
	function schedule($param){
	    
	    $relatorio = $this->criaRelatorio();
	   // $relatorio->setToPDF(true);
	    $relatorio->setToExcel(true,'Resumo_Vendas');
	    $relatorio->setAuto(true);
	    
	    //emails
	    $param = str_replace(',', ';', $param);
	   // $param = 'alex.cesar@verticais.com.br';
	    if(!$this->_teste)
	    {
	        $relatorio->enviaEmail($param);
	        log::gravaLog('Resumo_OL_PE', 'Enviado email para: '.$param);
	        
	    } else {
	        $relatorio->enviaEmail('alex.cesar@verticais.com.br');
	        log::gravaLog('Resumo_OL_PE', 'Enviado email teste ');
	        
	    }
	}
	
	private function criaRelatorio()
	{
	    //Cria relatório
	    $relatorio = new relatorio01(['programa' => $this->_programa]);
	    
	    //Pega prazo
	    $dia = date('d');
	    $mes = date('m');
	    $ano = date('Y');
	    //teste início mês/ano
	    //    $dia = '01';
	    //    $mes = '01';
	    if($dia!='01')
	    {
	        $dataIni = $ano.$mes.'01';
	        $dataFim = date('Ymd');
	    } else{
	        $mes--;
	        if($mes == 0){
	            $mes = 12;
	            $ano--;
	        }
	        $mes = $mes < 10 ? '0'.$mes : $mes;
	        $dataIni = $ano.$mes.'01';
	        $dataFim = date('Ymt',mktime(0,0,0,$mes,15,$ano));
	    }
//$mes = '09';
//$ano = '2023';
//$dataIni = '20230901';
//$dataFim = '20230930';
    
	    $titulo = "Resumo do Faturamento Diário: ".$mes.'/'.$ano;
	    $relatorio->setTitulo($titulo);
	    
	    // $data = date('Ymd');
	    //$campos = ['cod','venda','mix','devol','quantDevol','cmv','pedidos','quant','quantVend','bonific','positivacao'];
	    
	    //DIAS
	    $relatorio->addColuna(array('campo' => 'dia'	, 'etiqueta' => 'Dia'			, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'E'));
	    
	    //MEDICAMENTOS
	    $relatorio->addColuna(array('campo' => 'venda'	  , 'etiqueta' => 'Medicamentos'      , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
	    //  $relatorio->addColuna(array('campo' => 'quant'	, 'etiqueta' => 'Quantidade'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'quantVend', 'etiqueta' => 'Quantidade Vendida', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'p1'	      , 'etiqueta' => 'Prazo'			  , 'tipo' => 'N', 'width' =>  80, 'posicao' => 'E'));
	    
	    //NÃO MEDICAMENTOS
	    $relatorio->addColuna(array('campo' => 'venda_n'	, 'etiqueta' => 'Não-Medicamentos'  , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
	    //  $relatorio->addColuna(array('campo' => 'quant_n'	, 'etiqueta' => 'Quantidade'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'quantVend_n', 'etiqueta' => 'Quantidade Vendida', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'p2'	        , 'etiqueta' => 'Prazo'			    , 'tipo' => 'N', 'width' =>  80, 'posicao' => 'E'));
	    
	    //TOTAIS
	    $relatorio->addColuna(array('campo' => 'tot_venda'	, 'etiqueta' => 'Total'  , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'tot_quantVend', 'etiqueta' => 'Total Vendidos', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'tot_p'	        , 'etiqueta' => 'Prazo Médio'			    , 'tipo' => 'N', 'width' =>  80, 'posicao' => 'E'));
	    
	    $dados = $this->getDados($dataIni, $dataFim);
	    //   $dados=$this->getDados($data-5, $data);
	    $relatorio->setDados($dados);
	    
	    return $relatorio;
	}
	
	private function getDados($dataIni, $dataFim)
	{
	    $ret = [];
	    $prazos = $this->prazoMedio($dataIni, $dataFim);
	    $campos = ['cod','venda','mix','devol','quantDevol','cmv','pedidos','quant','quantVend','bonific','positivacao'];
	    
	     if(is_array($prazos) && count($prazos)>0)
	     {
	         //POR DIA
	         for($data=$dataIni;$data<$dataFim+1;$data++)
    	    {
    	        $dados = vendas1464Campo('CODEPTO', $data, $data, ['bonificacao'=>false]);
    	        
    	        if(is_array($dados) && count($dados)>0)
    	        {
        	        $temp = [];
        	        $temp['dia'] = $data;
        	        foreach($campos as $campo)
        	        {
        	            $temp[$campo] = $dados[1][$campo];
        	            $temp[$campo.'_n'] = $dados[12][$campo];
        	            $temp['tot_'.$campo] = $dados[1][$campo] + $dados[12][$campo];
        	        }
        	        if(array_key_exists($data,$prazos)){
        	            $temp['p1'] = $prazos[$data]['p1'];
        	            $temp['p2'] = $prazos[$data]['p2'];
        	            $temp['tot_p'] = ($temp['p1']+$temp['p2'])/2;        	            
        	        }
        	        $ret[]=$temp;	        
    	        }
    	    }
    	    //TOTAIS
    	    $dados = vendas1464Campo('CODEPTO', $dataIni, $dataFim, ['bonificacao'=>false]);
    	    if(is_array($dados) && count($dados)>0)
    	    {
    	        $temp = ['dia' => 'TOTAL', 'p1' => 0, 'p2' => 0, 'tot_p' => 0];
    	        foreach($campos as $campo)
    	        {
    	            $temp[$campo] = $dados[1][$campo];
    	            $temp[$campo.'_n'] = $dados[12][$campo];
    	            $temp['tot_'.$campo] = $dados[1][$campo] + $dados[12][$campo];
    	        }
    	        foreach($ret as $re)
    	        {
    	            $temp['p1'] += $re['p1'];
    	            $temp['p2'] += $re['p2'];    	            
    	            $temp['tot_p'] += $re['tot_p'];    	            
    	        }
    	        $temp['p1'] = $temp['p1']/count($ret);
    	        $temp['p2'] = $temp['p2']/count($ret);
    	        $temp['tot_p'] = $temp['tot_p']/count($ret);
    	        
    	        $ret[]=$temp;
    	    }
	     }
	    return $ret;
	}
	
	function prazoMedio($dtDe,$dtAte){
	    $ret = [];
		$param = array();
		$param['quebraData'] = true;
		$param['depto'] = '1,12';
		
	//	$dtDe = datas::dataD2S($dtDe);
	//	$dtAte = datas::dataD2S($dtAte);
		
		//Medicamentos / Não medicamentos
		$medias = prazoMedioVenda('CODEPTO',$dtDe, $dtAte, $param, false);
//print_r($medias);
		foreach ($medias as $depto => $media){
			foreach ($media as $dia => $prazo){
				if($depto == 1){
				    $ret[$dia]['p1'] = $prazo;
				}else{
				    $ret[$dia]['p2'] = $prazo;
				}
			}
		}

		//TOTAL Medicamentos / Não medicamentos
		$medias = prazoMedioVenda('FILIAL',$dtDe, $dtAte, $param, false);
		//print_r($medias);
		foreach ($medias as $filial => $media){
			foreach ($media as $dia => $prazo){
			    $ret[$dia]['p3'] = $prazo;
			}
		}
		
		
		//PE
		$param['origem'] = 'PE';
		$medias = prazoMedioVenda('origem',$dtDe, $dtAte, $param, false);
//print_r($medias);
		foreach ($medias as $origem => $media){
			foreach ($media as $dia => $prazo){
				if($origem == 'PE'){
				    $ret[$dia]['p4'] = $prazo;
				}
			}
		}
		
		//Fornecedores
		$param['origem'] = 'OL';
		$param['fornecedor'] = '1118,101,1123,1139,119';
		$medias = prazoMedioVenda('codfornec',$dtDe, $dtAte, $param, false);
//print_r($medias);
		foreach ($medias as $fornec => $media){
			foreach ($media as $dia => $prazo){
				switch ($fornec) {
					case 1118:
					    $ret[$dia]['p5'] = $prazo;;
						break;
					case 101:
					    $ret[$dia]['p6'] = $prazo;;
						break;
					case 1123:
					    $ret[$dia]['p7'] = $prazo;;
						break;
					case 1139:
					    $ret[$dia]['p8'] = $prazo;;
						break;
					case 119:
					    $ret[$dia]['p9'] = $prazo;;
						break;
					default:
						break;
				}
			}
		}
		
		//Redes
		$param['fornecedor'] = '';
		$param['origem'] = 'T';
		$param['rede'] = '73,43';
		$medias = prazoMedioVenda('codrede',$dtDe, $dtAte, $param, false);
//print_r($medias);
		foreach ($medias as $rede => $media){
			foreach ($media as $dia => $prazo){
				switch ($rede) {
					case 56:
					    $ret[$dia]['p10'] = $prazo;;
						break;
					case 73:
					    $ret[$dia]['p11'] = $prazo;;
						break;
					case 43:
					    $ret[$dia]['p12'] = $prazo;;
						break;
					default:
						break;
				}
			}
		}
		
		//total
		$param['rede'] = '';
		$param['origem'] = '';
		$param['quebraData'] = false;
		$param['where'] = " AND (PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'PE')";
		$param['where'] .= " OR (PCPRODUT.codfornec IN (1123,1118,101,1139,119) 
AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL'))";
		$param['where'] .= " OR (PCCLIENT.codrede IN (73,43) AND PCNFSAID.NUMPED IN 
( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'T'))";
		$param['where'] .= ") ";
		$medias = prazoMedioVenda('DTSAIDA',$dtDe, $dtAte, $param, false);
//print_r($medias);
		foreach ($medias as $data => $prazo){
			$dia = str_replace('-', '', $data);
			$ret[$dia]['p13'] = $prazo;;
		}
		return $ret;
	}
}