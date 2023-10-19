<?php
/*
* Data Criacao: 21/08/2014 - 18:34:09
* Autor: Thiel
*
* Descricao:
* 
* Alter��o:
*           17/10/2018 - Emanuel - foi copiado o m�todo getPeriodos da classe data da intranet 1 usada na classe depto12meses
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class datas{


	/**
	 * Pega data 2013-05-27 00:00:00 (retorno data MSSQL) e retorna dd/mm/aaaa hh:mm:ss
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	string	$data	2013-05-27 00:00:00 (retorno data MSSQL)
	 * @return	string	data no formato dd/mm/aaaa
	 */
	// Pega aaaammdd e retorna aaaa.mm.dd (padr�o MSSQL data)
	static function dataMS2DH($data, $sep = ' ',$ano = 4){
		$ret = datas::dataS2D(substr(str_replace("-","",$data),0,8),$ano).$sep.substr($data, 11,8);
		return $ret;
	}
		

	

	
	
	/*
	 * data de 31122013 para 31/12/2013
	 * data de 311213 para 31/12/2013
	 */
	static function formataData2($data){
		if (strlen($data) == 6){
			$tamano = 2;
		}else{
			$tamano = 4;
		}
	
		$dia = substr($data,0,2);
		$mes = substr($data,2,2);
		$ano = substr($data,4,$tamano);
	
		return($dia."/".$mes."/".$ano);
	}

	static function getMesesExtenso($mes = false){
		$ret = array();
		if($mes !== false){
			$mes = intval($mes);
		}
		$ret[1] = 'Janeiro';
		$ret[2] = 'Fevereiro';
		$ret[3] = 'Mar&ccedil;o';
		$ret[4] = 'Abril';
		$ret[5] = 'Maio';
		$ret[6] = 'Junho';
		$ret[7] = 'Julho';
		$ret[8] = 'Agosto';
		$ret[9] = 'Setembro';
		$ret[10] = 'Outubro';
		$ret[11] = 'Novembro';
		$ret[12] = 'Dezembro';
		
		if($mes === false)
			return $ret;
		else
			return $ret[$mes];
	}
	

	
	
	/**
	 * Calcula o numero de dias entre duas datas
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	string $dataIni Data inicio formato AAAAMMDD
	 * @param	string $dataFim Data fim formato AAAAMMDD
	 * @return	int quantidade de dias
	 *
	 * @version 0.01
	 */
	static function getEspacoDias($diaIni,$diaFim){
	    if($diaIni != '' && $diaFim != '') {
	        $dataIni = mktime(0, 0, 0, substr($diaIni,4,2), substr($diaIni,6,2), substr($diaIni,0,4));
	        $dataFim = mktime(0, 0, 0, substr($diaFim,4,2), substr($diaFim,6,2), substr($diaFim,0,4));
	        $diferenca = $dataFim - $dataIni;
	        $dias = (int)floor($diferenca / (60 * 60 * 24));
	    }else {
	        $dias = 0;
	    }
	    return $dias;
	}
	
	/**
	 * Retorna as datas referentes a $quant meses (anterior ou posterior) a data informada no formato AAAAMMDD
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	string	$quant 		quantidade de meses
	 * @param	string	$data		data no formato aaaammdd (padr�o - hoje)
	 * @param	string	$sentido	Datas 'A'nteriores ou 'P'osteriores
	 * @param	string	$atual		Indica se a data atual deve entrar no retorno
	 *
	 * @return	array	$ret		Array contendo as datas no formato $ret[n]['ini'] = data inicial  $ret[n]['fim'] = data final
	 */
	
	static function getPeriodos($quant,$data='',$sentido = 'A', $atual = false){
	    $ret = array();
	    if($data == ''){
	        $data = date("Ymd");
	    }
	    $dia = strval(substr($data,6,2));
	    $mes = strval(substr($data,4,2));
	    $ano = strval(substr($data,0,4));
	    $anoCurto = strval(substr($data,2,2));
	    
	    $key = 0;
	    if($atual){
	        $ret[$key]['ini'] = date('Ym',mktime(0,0,0,$mes, $dia, $ano)).'01';
	        $ret[$key]['fim'] = date('Ymt',mktime(0,0,0,$mes, $dia, $ano));
	        $ret[$key]['anomes'] = $ano.$mes;
	        $ret[$key]['desc'] = $mes."/".$ano;
	        $ret[$key]['desc'] = $mes."/".$ano;
	        $ret[$key]['descCurta'] = $mes."/".$anoCurto;
	        $key++;
	    }
	    
	    if($sentido <> 'A' && $sentido <> 'P'){
	        $sentido = 'A';
	    }
	    
	    $soma = $sentido == 'A' ? -1 : 1;
	    
	    if($quant > 0){
	        for($i=0;$i<$quant;$i++){
	            $mes = $mes + $soma;
	            if($mes < 1){
	                $mes = 12;
	                $ano--;
	            }
	            if($mes > 12){
	                $mes = 1;
	                $ano++;
	            }
	            $mes = $mes < 10 ? '0'.$mes : $mes;
	            $anoCurto = strval(substr($ano,2,2));
	            $ret[$key]['ini'] = date('Ym',mktime(0,0,0,$mes, 15, $ano)).'01';
	            $ret[$key]['fim'] = date('Ymt',mktime(0,0,0,$mes, 15, $ano));
	            $ret[$key]['anomes'] = $ano.$mes;
	            $ret[$key]['desc'] = $mes."/".$ano;
	            $ret[$key]['desc'] = $mes."/".$ano;
	            $ret[$key]['descCurta'] = $mes."/".$anoCurto;
	            
	            $key++;
	        }
	    }
	    
	    return $ret;
	}

	/**
	 * Retorna os periodos de ANO/MES entre duas datas
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	string	$dataini 	Data inicial AAAAMMDD
	 * @param	string	$datafim	Data final AAAAMMDD
	 * @param	string	$extenso	Indica se deve retornar o mes/ano por 'E'xtenso, 'R'esumido ou 'N'umerico
	 * @return	array	$ret		Array contendo as datas no formato $ret[n]['ini'] = data inicial  $ret[n]['fim'] = data final
	 */
	
	static function getMeses($dataini, $datafim=''){
		$ret = array();
		$meses = array('Janeiro','Fevereiro','Marco','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
		$mesCurto = array('Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez');
		$mesNr = array('01','02','03','04','05','06','07','08','09','10','11','12');
		if(empty($dataini)){
			$dataini = date("Ymd");
		}
		if($datafim == ''){
			$datafim = date("Ymd");
		}
		
		$diaini = strval(substr($dataini,6,2));
		$mesini = strval(substr($dataini,4,2));
		$anoini = strval(substr($dataini,0,4));
		
		$diafim = strval(substr($datafim,6,2));
		$mesfim = strval(substr($datafim,4,2));
		$anofim = strval(substr($datafim,0,4));
		
		$soma = $dataini > $datafim ? -1 : 1;
		
		$ano = $anoini;
		$mes = $mesini;
		
		do{
			$fim = false;
			$quant = count($ret);
			$ret[$quant]['anomes'] 		= $ano.$mesNr[$mes-1];
			$ret[$quant]['mes'] 		= $meses[$mes-1];
			$ret[$quant]['mesCurto'] 	= $mesCurto[$mes-1];
			$ret[$quant]['mesNr']	 	= $mesNr[$mes-1];
			$ret[$quant]['ano'] 		= $ano;
			$ret[$quant]['anoCurto'] 	= substr($ano, 2,2);
			$ret[$quant]['mesano'] 		= $meses[$mes-1].'/'.$ano;
			$ret[$quant]['mesanoCurto'] = $mesCurto[$mes-1].'/'.$ret[$quant]['anoCurto'];
			$ret[$quant]['mesanoNr'] 	= $mesNr[$mes-1].'/'.$ano;
			$ret[$quant]['mesanoNrCurto']= $mesNr[$mes-1].'/'.$ret[$quant]['anoCurto'];
			$ret[$quant]['diaini'] 		= '01';
			$ret[$quant]['diafim'] 		= date('t',mktime(0,0,0,$mes, 15, $ano));
			
			$mes += $soma;
			if($mes > 12){
				$mes = 1;
				$ano++;
			}elseif($mes < 1){
				$mes = 12;
				$ano--;
			}
			if($dataini > $datafim && $ano.$mesNr[$mes-1] < $anofim.$mesfim){
				$fim = true;
			}elseif($dataini < $datafim && $ano.$mesNr[$mes-1] > $anofim.$mesfim){
				$fim = true;
			}elseif($dataini == $datafim){
				$fim = true;
			}
			
		}while(!$fim);
		
		return $ret;
	}
	
	/**
	 * Retorna a diferença em dias de duas datas AAAAMMDD ou DD/MM/AAAA
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	string	$data1	Data início
	 * @param	string	$data2	Data fim
	 * @return	int		Dias de diferen�a entre as datas
	 *
	 * @version 0.01
	 */
	static function getDiffDias($data1, $data2 = ''){
		if($data2 == ''){
			$data2 = date('Ymd');
		}
		$date1 = strtotime($data1);
		$date2 = strtotime($data2);
		$subTime = $date1 - $date2;
		
		//$y = ($subTime/(60*60*24*365));
		$d = (int)floor( $subTime / (60 * 60 * 24));
		
		if($d < 1){
			$d *= -1;
		}
		
		return $d;
	}
	
	
	/**
	 * Calcula o numero de meses entra a data1 e data2
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	array	$data1	Data inicio (DD/MM/AAAA)
	 * @param	array	$data2	Data fim (DD/MM/AAAA)
	 * @return	int		Quantidade de meses
	 */
	static function calculaDifMeses($data1,$data2){
		list($dia1,$mes1,$ano1) = explode('/', $data1);
		list($dia2,$mes2,$ano2) = explode('/', $data2);
		
		if($ano2.$mes2 <= $ano1.$mes1){
			return 0;
		}
		
		$anos = ($ano2 - $ano1) > 1 ? 12 * ($ano2 - $ano1 -1) : 0;
		
		if($ano2 > $ano1){
			$meses = (13 - $mes1) + $mes2 + $anos;
		}else{
			$meses = $mes2 - $mes1 + 1 ;
		}
		return $meses;
	}
	
	/**
	 * Calcula o numero de meses entra a data1 e data2
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	array	$data1	Data inicio (AAAAMMDD)
	 * @param	array	$data2	Data fim (AAAAMMDD)
	 * @return	int		Quantidade de meses
	 */
	static function calculaDifMesesS($data1,$data2){
		$meses = 0;
		$data1 = datas::dataS2D($data1);
		$data2 = datas::dataS2D($data2);
		
		$meses = datas::calculaDifMeses($data1,$data2);
		
		return $meses;
	}
	
	
	/**
	 * Retorna um timestamp unix de um timestamp mysql (2019-05-28 17:38:48)
	 * 
	 * @param string $time
	 */
	static function gettimestamp($time){
		$ano = substr($time, 0, 4);
		$mes = substr($time, 5, 2);
		$dia = substr($time, 8, 2);
		
		$hora = substr($time, 11, 2);
		$min = substr($time, 14, 2);
		$seg = substr($time, 17, 2);
		
		return mktime($hora, $min, $seg, $mes, $dia, $ano);
	}
}