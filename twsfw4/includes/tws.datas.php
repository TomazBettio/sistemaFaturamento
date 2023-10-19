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
		if(strlen($data) < 8) return "";
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
			$dia_semana["Mon"] = "Seg";
			$dia_semana["Tue"] = "Ter";
			$dia_semana["Wed"] = "Qua";
			$dia_semana["Thu"] = "Qui";
			$dia_semana["Fri"] = "Sex";
			$dia_semana["Sat"] = "Sab";
			$dia_semana["Sun"] = "Dom";
		}else{
			$dia_semana["Mon"] = "1";
			$dia_semana["Tue"] = "2";
			$dia_semana["Wed"] = "3";
			$dia_semana["Thu"] = "4";
			$dia_semana["Fri"] = "5";
			$dia_semana["Sat"] = "6";
			$dia_semana["Sun"] = "7";
			
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
				$datas[1][] = $dia_semana[strftime("%a", mktime(0,0,0,$ini_mes, $ini_dia, $ini_ano))];
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
		$ret = datas::dataS2D(substr(str_replace("-","",$data),0,8),$ano);
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
}

