<?php
/*
 * Data Criacao 14/02/2022
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Manipulação de datas
 *
 * Alteracoes:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class datas {
    
    
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
    
    /**
     * Pega data 2013-05-27 00:00:00 (retorno data MSSQL) e retorna hh:mm:ss
     *
     * @author	Emanuel Thiel
     * @access	public
     * @param	string	$data	2013-05-27 00:00:00 (retorno data MSSQL)
     * @return	string	horario no formato hh:mm:ss
     */
    // Pega aaaammdd e retorna aaaa.mm.dd (padr�o MSSQL data)
    static function dataMS2H($data, $sep = ' ',$ano = 4){
        $ret = substr($data, 11,8);
        return $ret;
    }

	/**
	 * Pega data aaaammdd e retorna dd/mm/aaaa
	 *
	 * @param	string	$data	data no formato aaaammdd
	 * @param	string	$ano 	4 ou 2 digitos para ano (P: 4)
	 * @param	string	$sep	Separador (P: /)
	 * @return	string	data no formato dd/mm/aaaa
	 */
	static function dataS2D($data,$ano = 4,$sep = "/"){
		if(empty($data)){
			return '';
		}
		if(preg_match("/([a-zA-Z])/", $data ) ){
			return $data;
		}
		if (strlen($data) == 8){
			if($ano == 4){
				$dataN = substr($data,6,2).$sep.substr($data,4,2).$sep.substr($data,0,4);
			}else{
				$dataN = substr($data,6,2).$sep.substr($data,4,2).$sep.substr($data,2,2);
			}
		}else{
			if($ano == 4){
				$dataN = substr($data,4,2).$sep.substr($data,2,2).$sep."20".substr($data,0,2);
			}else{
				$dataN = substr($data,4,2).$sep.substr($data,2,2).$sep.substr($data,0,2);
			}
		}
		return $dataN;
	}
	
	/**
	 * Pega data dd/mm/aaaa e retorna aaaammdd
	 *
	 * @param	string	$data	data no formato dd/mm/aaaa, dd/mm/aa, ddmmaa ou ddmmaaaa
	 * @param	string	$sep	separador utilizado na saída- padrão ''
	 *
	 * @return	string	data no formato aaaammdd
	 */
	static function dataD2S($data, $sepSaida = '', $sepEntrada = '/'){
		if(strlen($data) < 8) {
			return '';
		}
		if(!empty($sepEntrada) && strpos($data, $sepEntrada) === false){
			return '';
		}
		if(!empty($sepEntrada)){
			list($dia,$mes,$ano) = explode($sepEntrada, $data);
		}else{
			$tamAno = 4;
			if(strlen($data) == 6){
				$tamAno = 2;
			}
			$dia = substr($data, 0, 2);
			$mes = substr($data, 2, 2);
			$ano = substr($data, 4, $tamAno);
		}
		if(strlen($ano) == 2) $ano = "20".$ano;
		$data = $ano.$sepSaida.$mes.$sepSaida.$dia;
		return $data;
	}
	
	/**
	 * Retorna a data no formato aaaammdd da data informada (se nao informada a atual) mais os $dias
	 *
	 * @param	int		$dias	Quantidade de dias a somar (ou diminuir) da data
	 * @param	string	$data	Data no formato aaaammdd a ser considerada como base (se vazio considera a data de hoje)
	 * @return	string	Datata no formato aaaammdd
	 */
	static function getDataDias($dias=0,$data = ''){
		
		if ($data != ''){
			$dia = substr($data,6,2);
			$mes = substr($data,4,2);
			$ano = substr($data,0,4);
			$dt = getdate( mktime(0,0,0,$mes, $dia, $ano) + 86400 * $dias );
		}else{
			$dt = getdate( time() + 86400 * $dias );
		}
		
		$dt = $dt['year'].($dt['mon']>'9'?$dt['mon']:'0'.$dt['mon']).($dt['mday']>'9'?$dt['mday']:'0'.$dt['mday']);
		
		return $dt;
	}
	
	/**
	 * Retorna um array com os dias existentes no intervalo de datas
	 *
	 * @param string $data_inicio no formato AAAAMMDD
	 * @param string $data_fim no formato AAAAMMDD
	 * @param string $semana - Padrão 'ext' para escrito ou outro valor para numérico (1-segunda... 7-domingo)
	 * @return array	[0][datas entre as duas datas informadas]
	 * 					[1][dia da semana das datas]
	 */
	static function calendario($data_inicio,$data_fim, $semana = "ext"){
		$dia_semana = [];
		if ($semana == "ext"){
			$dia_semana[1] = "Seg";
			$dia_semana[2] = "Ter";
			$dia_semana[3] = "Qua";
			$dia_semana[4] = "Qui";
			$dia_semana[5] = "Sex";
			$dia_semana[6] = "Sab";
			$dia_semana[7] = "Dom";
		}else{
			$dia_semana[1] = "1";
			$dia_semana[2] = "2";
			$dia_semana[3] = "3";
			$dia_semana[4] = "4";
			$dia_semana[5] = "5";
			$dia_semana[6] = "6";
			$dia_semana[7] = "7";
			
		}
		$datas = array();
		$ini_dia = strval(substr($data_inicio,6,2));
		$ini_mes = strval(substr($data_inicio,4,2));
		$ini_ano = strval(substr($data_inicio,0,4));
		
		$ini = strval($data_inicio);
		$fim = strval($data_fim);
		
		for ($i = $ini; $i <= $fim; $i++){
			if (intval($ini_dia) < 10) {$str_dia = "0".intval($ini_dia);}else{$str_dia = $ini_dia;}
			if (intval($ini_mes) < 10) {$str_mes = "0".intval($ini_mes);}else{$str_mes = $ini_mes;}
			
			if (checkdate($ini_mes, $ini_dia, $ini_ano)){
				$datas[0][] = $ini_ano.$str_mes.$str_dia;
				$datas[1][] = $dia_semana[date("N", mktime(0,0,0,$ini_mes, $ini_dia, $ini_ano))];
			}else{
				$ini_dia = 0;
				$ini_mes++;
				if ($ini_mes == 13){
					$ini_mes = 1;
					$ini_ano++;
				}
				if (intval($ini_dia) < 10) {$str_dia = "0".intval($ini_dia);}else{$str_dia = $ini_dia;}
				if (intval($ini_mes) < 10) {$str_mes = "0".intval($ini_mes);}else{$str_mes = $ini_mes;}
			}
			$ini_dia++;
			$i = strval($ini_ano.$str_mes.$str_dia);
		}
		return $datas;
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
	 * Retorna a soma de dois tempos HH:MM:SS

	 * @param		$temp1 - string - formato HH:MM:SS
	 * @param		$temp2 - string - formato HH:MM:SS
	 * @param		$ret - 1= HH:MM 2=HH:MM:SS 3=HH
	 * @return		string - formato HH:MM:SS
	 */
	
	static function somaTempo($temp1, $temp2,$ret = 1, $soma = true){
		$times[] = $temp1;
		$times[] = $temp2;
		
		$seconds = 0;
		$mult = 1;
		
		foreach ( $times as $time ){
			@list( $g, $i, $s ) = explode( ':', $time );
			$g = intval($g);
			$i = intval($i);
			$s = intval($s);
			$seconds += $g * 3600 * $mult;
			$seconds += $i * 60 * $mult;
			$seconds += $s * $mult;
			
			if(!$soma){
				$mult = -1;
			}
		}
		
		$hours = floor( $seconds / 3600 );
		$seconds -= $hours * 3600;
		$minutes = floor( $seconds / 60 );
		$seconds -= $minutes * 60;
		
		if(strlen("$hours") < 2){
			$hours = '0'.$hours;
		}
		if(strlen("$minutes") < 2){
			$minutes = '0'.$minutes;
		}
		if(strlen("$seconds") < 2){
			$seconds = '0'.$seconds;
		}
		
		if($ret == 1){
			return "$hours:$minutes";
		}elseif($ret == 2){
			return "$hours:$minutes:$seconds";
		}else{
			return "$hours";
		}
		
	}
	
	static function somaTempoTimestamp($timestamp, $minutos = 0, $horas = 0, $dias = ''){
		$ret = false;
		
		$inicio = new DateTime($timestamp);
		if($inicio !== false){ 
			if(!empty($dias)){
				$horas += ($dias * 24);
			}
			$fim = $inicio->add(new DateInterval('PT'.$horas.'H'.$minutos.'M'));
			$ret = $fim->format('Y-m-d H:i:s');
		}
		
		return $ret;
	}
	
	static public function getTimeStampMysql(){
		return date("Y-m-d H:i:s");
	}
	
	/**
	 * Pega data aaaammdd e retorna aaaa.mm.dd (padrão MSSQL data)
	 *
	 * @param	string	$data	data no formato aaaammdd
	 * @param	string	$sep	separador (padr�o '.')
	 * @return	string	data no formato aaaa.mm.dd
	 */
	static function dataMSSQL($data,$sep = '.'){
		$ret = '';
		if(!empty($data)){
			$ret = substr($data,0,4).$sep.substr($data,4,2).$sep.substr($data,6,2);
		}
		return $ret;
	}
	
	static function data_hoje($formato = "DMA",$separador = "/"){
		if($formato == "DMA"){
			$data = date("d").$separador.date("m").$separador.date("Y");
		}elseif($formato == "AMD"){
			$data = date("Y").$separador.date("m").$separador.date("d");
		}
		return $data;
	}
	
	static function hora_hoje(){
		return date("H").":".date("i");
	}
	
	/**
	 * Pega data 2013-05-27 00:00:00 (retorno data MSSQL) e retorna dd/mm/aaaa
	 *
	 * @param	string	$data	2013-05-27 00:00:00 (retorno data MSSQL)
	 * @return	string	data no formato dd/mm/aaaa
	 */
	// Pega aaaammdd e retorna aaaa.mm.dd (padr�o MSSQL data)
	static function dataMS2D($data, $ano = 4){
	    $ret = '';
	    if(!empty($data)){
	        $ret = datas::dataS2D(substr(str_replace("-","",$data),0,8),$ano);
	    }
		return $ret;
	}
	
	/**
	 * Pega data 2013-05-27 00:00:00 (retorno data MSSQL) e retorna aaaammdd
	 *
	 * @param	string	$data	2013-05-27 00:00:00 (retorno data MSSQL)
	 * @return	string	data no formato aaaammdd
	 */
	static function dataMS2S($data, $ano = 4){
		$ret = '';
		if(!empty($data)){
			$ret = substr(str_replace("-","",$data),0,8);
		}
		return $ret;
	}
	
	/**
	 * Retona o último dia de um $ano/$mes
	 * 
	 * @param int $ano
	 * @param int $mes
	 * @return int - último dia do mes
	 */
	static function ultimoDiaMes($ano, $mes){
		$ret = '';
		
		if(empty($ano) || empty($mes)){
			return $ret;
		}
		
		$ret = date('t',mktime(0,0,0,$mes,15,$ano));
		
		return $ret;
	}
	
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
	 * Retorna o dia da semana de uma data AAAAMMDD
	 * 
	 * @param string $data
	 * @return number - 0(para domingo) a 6(para sábado)
	 */
	static function getDiaSemana($data){
		$ret = false;
		
		if(strlen($data) == 8){
			$ret = date('w', mktime(0, 0, 0, substr($data,4,2), substr($data,6,2), substr($data,0,4)));
		}
		
		return $ret;
	}
	
	/**
	 * Retorna a direfença de horas entre duas datas timestamp
	 * 
	 * @param string $time1 - ex: '2023-03-08 12:00:00'
	 * @param string $time2 - ex: '2023-03-08 12:00:00'
	 * @return string diferença em horas:minutos entre as duas datas
	 */
	static function getDifHoras($time1, $time2){
		$ret = false;
		
		$inicio = new DateTime($time1);
		$fim = new DateTime($time2);
		
		if($inicio !== false && $fim !== false){
			$intervalo = $fim->diff($inicio);
			$horas = $intervalo->h;
			$horas = $horas < 10 ? '0'.$horas : $horas;
			$minutos = $intervalo->i;
			$minutos = $minutos < 10 ? '0'.$minutos : $minutos;
			
			$ret = $horas.":".$minutos;
		}
		
		return $ret;
	}
}

