<?php
/*
* Data Criação: 03/09/2013 - 15:30:12
* Autor: Thiel
*
* Arquivo: schedule.php
* 
* Versao: 0.05
* 
* Descricao: 	Responsável pela execução de todos os schedules.
* 				Pode ser executado de 5/5 minutos
* 
* 				Chama o método schedule de qualquer classe (programa)
*               - precisa ser declarado como público
*               - não precisa estar no array de funções liberadas 
* 
* Alteracoes:
* 26/12/16 - Alterado para atender a execução de hora/hora
* 22/05/18 - Alterado para utilizar try/catch
* 25/04/22 - migrado para o sdm/crm - Emanuel
*/

if(!defined('TWSiNet'))define('TWSiNet', true);
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

include("/var/www/sdm/config/config.php");
$config['site']['debug'] = true;

set_time_limit(0);
date_default_timezone_set ( "America/Sao_Paulo" );

$email_erro = '';

/*
 * Verifica se foi passado por GET o schedule a ser executado (testes)
 */

echo "Executando schedule Intranet2";
log::gravaLog("schedule", "-----------------------------------------------------> Executando Schedule");

if(isset($_GET['schedule'])){
	$programas = getProgramaSchedule($_GET['schedule']);
}else{
	$programas = getProgramaSchedule();
}



//print_r($programas);
if(count($programas) > 0){
	foreach ($programas as $programa){
		$ret = '';
		//$app->cliente = $programa['cliente'];
		//$app->user->user = 'schedule';
		
		if(isset($_GET['parametros'])){
			$parametros = $_GET['parametros'];
		}else{
			$parametros = $programa['parametros'];
		}
		
		log::gravaLog("schedule", "Inicio execucao: ".$programa['programa'].".schedule - Parametros: $parametros");
		try {
			$config['site']['debug'] = true;
			$ret = ExecMethod($programa['programa'].".schedule",$parametros);
		} catch (Exception $e) {
			log::gravaLog("schedule_erro", print_r($e,true),null, true);
			$email_erro .= print_r($e,true);
			enviaEmail('suporte@thielws.com.br', 'Schedule Erro', $email_erro);
			$email_erro = '';
		}
		
		log::gravaLog("schedule", "Fim execucao: ".$programa['programa'].".schedule");
		
		echo $ret;
	}
}

//Envia emails agendados
//enviaEmalAgenda();

/**
* getProgramaSchedule - Retorna os programas que dem ser executados em um determinado dia/hora
*
* @author	Alexandre Thiel
* @access	public
* @param	string	$schedule
* @return	array	programas a serem executados
*
* @version 0.01
*/
function getProgramaSchedule($schedule = ''){
	$dia = array();
	$dia['dia'] 	= date("d");	// 01 - 31
	$dia['mes']		= date("m");	// 01 - 12
	$dia['ano']		= date("Y");	// 2013
	$dia['semana'] 	= date("N");	// 1-Segunda, 2-Ter�a,.... 7-Domingo
	$dia['hora']	= date("H");	// 01 - 24
	$dia['minuto']	= date("i");	// 00 - 59

	// Períodos: D - Di�rio, M - Mensal, A - Anual, S - Semanal, M - Mensal, H - Hora (1 x por hora cfme minuto), N - Minuto (15/15 minutos)
	$ret = array();
	$zonaMinuto = (int)($dia['minuto'] / 15);
	if(strpos($dia['minuto'], '0') === 0){
	    $minuto = $dia['minuto'][1];
	    $dia['minuto'] = $minuto;
	}

//echo "Zona: ".$zonaMinuto."<br>";
//print_r($dia);
//echo "<br>";	
	//Seleciona na tabela schedule quais os programas que devem ser executados
	if($schedule == ''){
		$sql = "SELECT * FROM (SELECT schedule.*, case minuto when '0' then -1 when '1' then 13 when '2' then 28 when '3' then 43 end as minuto_minimo, case minuto when '0' then 2 when '1' then 17 when '2' then 32 when '3' then 47 end as minuto_maximo from schedule) tmp1 
				WHERE 
					(
						(periodo = 'D' AND (semana LIKE '%".$dia['semana']."%' OR semana = '') AND hora = ".$dia['hora']." AND minuto_minimo <= " . $dia['minuto'] . " and minuto_maximo >= " . $dia['minuto'] . ")
						OR
						(periodo = 'N' AND (semana LIKE '%".$dia['semana']."%' OR semana = '') AND " . montarRestricaoScheduleN(intval($dia['minuto'])) . ")
						OR
						(periodo = 'M' AND dia = '".$dia['dia']."' AND hora = ".$dia['hora']." AND minuto_minimo <= " . $dia['minuto'] . " and minuto_maximo >= " . $dia['minuto'] . ")
						OR
						(periodo = 'H' AND (semana LIKE '%".$dia['semana']."%' OR semana = '')  AND minuto_minimo <= " . $dia['minuto'] . " and minuto_maximo >= " . $dia['minuto'] . ")
                        OR
                        (periodo = '5')
					) AND ativo = 'S'
					";
	}else{
		$sql = "SELECT * FROM schedule WHERE programa = '$schedule' or nome = '$schedule'";
	}
//echo "SQL: $sql <br>";
	log::gravaLog("schedule_query", $sql);
	$rows = query($sql);
	
	if(count($rows) > 0){
		$i = 0;
		foreach($rows as $row){
			log::gravaLog("schedule", "Leitura: ".$row['programa']);
			$ret[$i]['id'] 			= $row['id'];
			$ret[$i]['cliente'] 	= $row['cliente'];
			$ret[$i]['fil'] 		= $row['fil'];
			$ret[$i]['emp'] 		= $row['emp'];
			$ret[$i]['nome'] 		= $row['nome'];
			$ret[$i]['desc'] 		= $row['descricao'];
			$ret[$i]['periodo'] 	= $row['periodo'];
			$ret[$i]['programa']	= $row['programa'];
			$ret[$i]['parametros']	= $row['parametros'];
			$ret[$i]['ano'] 		= $row['ano'];
			$ret[$i]['mes'] 		= $row['mes'];
			$ret[$i]['dia'] 		= $row['dia'];
			$ret[$i]['hora'] 		= $row['hora'];
			$ret[$i]['minuto'] 		= $row['minuto'];
			$ret[$i]['semana'] 		= $row['semana'];
			$i++;
		}
	}
	return $ret;
}

function montarRestricaoScheduleN($minuto){
    $ret = 'false';
    $pares_minutos = array();
    $pares_minutos[] = array('min' => 0, 'max' => 2);
    $pares_minutos[] = array('min' => 13, 'max' => 17);
    $pares_minutos[] = array('min' => 28, 'max' => 32);
    $pares_minutos[] = array('min' => 43, 'max' => 47);
    foreach ($pares_minutos as $par){
        if($minuto >= $par['min'] && $minuto <= $par['max']){
            $ret = 'true';
        }
    }
    return $ret;
}
