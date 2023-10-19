<?php

$dbMF = '';
$dbERP = '';

function conectaMF()
{
	global $config, $dbMF;

	if ($config['producao'] === true) {
		$config['db_bancoMF'] 		= 'pdo';
		$config['db_serverMF'] 		= 'host=192.168.1.164';
		$config['db_databaseMF'] 	= 'intranet_mgt';
		$config['db_usuarioMF'] 	= 'intranet_mgt';
		$config['db_senhaMF'] 		= 'aIe71sUYqaV1FO94';
	} else {
		$config['db_bancoMF'] 		= 'pdo';
		$config['db_serverMF'] 		= 'host=192.168.1.16';
		$config['db_databaseMF'] 	= 'mgt_dev';
		$config['db_usuarioMF'] 	= 'marpatributario';
		$config['db_senhaMF'] 		= '3F6jOdYh';
	}
	$dbMF = ADOnewConnection($config['db_bancoERP']);
	$dsnString = $config['db_serverMF'] . ';dbname=' . $config['db_databaseMF'] . ';charset=utf8';
	$dbMF->connect('mysql:' . $dsnString, $config['db_usuarioMF'], $config['db_senhaMF']);
	//$db->debug = true;
}

function queryMF($sql, $debugQuery = false, $debugRet = false)
{
	global $dbMF, $config;
	$ret = array();
	if ($debugQuery) {
		echo "\nSQL: $sql <br>\n";
	}
	if (isset($config['site']['logQuery']) && $config['site']['logQuery']) {
		log::gravaLog('logQuery', getModulo() . ' - ' . getClasse() . ' - ' . getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $dbMF->Execute($sql);
	if ($res === false) {
		if ($config['debug'] || $debugQuery) {
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $dbMF->ErrorMsg();
			echo "\n<br>------------------------------<br>\n";
		}
		return false;
	} else {
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		if (($pos1 === false || $pos1 > 5) && $pos2 === false) {
			//			$ret = $db->GenID;
			return true;
		} else {
			$ret = $res->GetRows();
		}
		if ($debugRet) {
			print_r($ret);
		}
		return $ret;
	}
}

function conectaERP()
{
	global $config, $dbERP;

	if (true) {
		$config['db_bancoERP'] 		= 'pdo';
		$config['db_serverERP'] 		= 'host=192.168.1.252';
		$config['db_databaseERP'] 	= 'marpatributario';
		$config['db_usuarioERP'] 	= 'marpatributario';
		$config['db_senhaERP'] 		= '3F6jOdYh';
	} else {
		$config['db_bancoERP'] 		= 'pdo';
		$config['db_serverERP'] 		= 'host=192.168.1.16';
		$config['db_databaseERP'] 	= 'marpatributario';
		$config['db_usuarioERP'] 	= 'marpatributario';
		$config['db_senhaERP'] 		= '3F6jOdYh';
	}
	$dbERP = ADOnewConnection($config['db_bancoERP']);
	$dsnString = $config['db_serverERP'] . ';dbname=' . $config['db_databaseERP'] . ';charset=utf8';
	$dbERP->connect('mysql:' . $dsnString, $config['db_usuarioERP'], $config['db_senhaERP']);
	//$db->debug = true;
}

function queryERP($sql, $debugQuery = false, $debugRet = false)
{
	global $dbERP, $config;
	$ret = array();
	if ($debugQuery) {
		echo "\nSQL: $sql <br>\n";
	}
	if (isset($config['site']['logQuery']) && $config['site']['logQuery']) {
		log::gravaLog('logQuery', getModulo() . ' - ' . getClasse() . ' - ' . getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $dbERP->Execute($sql);
	if ($res === false) {
		if ($config['debug'] || $debugQuery) {
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $dbERP->ErrorMsg();
			echo "\n<br>------------------------------<br>\n";
		}
		return false;
	} else {
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		if (($pos1 === false || $pos1 > 5) && $pos2 === false) {
			//			$ret = $db->GenID;
			return true;
		} else {
			$ret = $res->GetRows();
		}
		if ($debugRet) {
			print_r($ret);
		}
		return $ret;
	}
}
