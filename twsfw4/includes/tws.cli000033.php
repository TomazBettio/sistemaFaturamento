<?php

if($config['producao'] === true){
	$config['db_banco2'] 	= 'postgres';
	$config['db_server2'] 	= '192.168.1.12';
	$config['db_database2'] = 'marpa_new';
	$config['db_usuario2'] 	= 'postgres';
	$config['db_senha2'] 	= 'e0w2c0i8u2n3';
}else{
	$config['db_banco2'] 	= 'postgres';
	$config['db_server2'] 	= '192.168.1.14';
	$config['db_database2'] = 'marpa_new';
	$config['db_usuario2'] 	= 'postgres';
	$config['db_senha2'] 	= 'e0w2c0i8u2n3';
}

$db2 = ADONewConnection($config['db_banco2']);
//$db2->debug = true;
$db2->Connect($config['db_server2'], $config['db_usuario2'], $config['db_senha2'],$config['db_database2']);

function query2($sql, $debugQuery = false, $debugRet = false){
	global $db2, $config;
	$ret = array();
	if($debugQuery){
		echo "\nSQL: $sql <br>\n";
	}
	if(isset($config['site']['logQuery']) && $config['site']['logQuery']){
		log::gravaLog('logQuery', getModulo().' - '.getClasse().' - '.getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $db2->Execute($sql);
	if ($res === false){
		if($config['debug'] || $debugQuery){
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $db->ErrorMsg();
			echo "\n<br>------------------------------<br>\n";
		}
		return false;
	}else{
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		if(($pos1 === false || $pos1 > 5) && $pos2 === false){
			//			$ret = $db->GenID;
			return true;
		}else{
			$ret = $res->GetRows();
		}
		if($debugRet){
			print_r($ret);
		}
		return $ret;
	}
}


function conectaTRIB() {
	global $config, $dbTRIB;
	
	if($config['producao'] === true){
		$config['db_bancoTRIB'] 	= 'pdo';
		$config['db_serverTRIB'] 	= 'host=192.168.1.252';
		$config['db_databaseTRIB'] 	= 'marpatributario';
		$config['db_usuarioTRIB'] 	= 'marpatributario';
		$config['db_senhaTRIB'] 	= '3F6jOdYh';
	}else{
		$config['db_bancoTRIB'] 	= 'pdo';
		$config['db_serverTRIB'] 	= 'host=192.168.1.16';
		$config['db_databaseTRIB'] 	= 'marpatributario';
		$config['db_usuarioTRIB'] 	= 'marpatributario';
		$config['db_senhaTRIB'] 	= '3F6jOdYh';
	}
	
	$dbTRIB = ADOnewConnection($config['db_bancoTRIB']);
	$dsnString= $config['db_serverTRIB'].';dbname='.$config['db_databaseTRIB'].';charset=utf8';
	$dbTRIB->connect('mysql:' . $dsnString,$config['db_usuarioTRIB'],$config['db_senhaTRIB']);
	//$dbTRIB->debug = true;
}

function queryTRIB($sql, $debugQuery = false, $debugRet = false){
	global $dbTRIB, $config;
	$ret = array();
	if($debugQuery){
		echo "\nSQL: $sql <br>\n";
	}
	if(isset($config['site']['logQuery']) && $config['site']['logQuery']){
		log::gravaLog('logQuery', getModulo().' - '.getClasse().' - '.getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $dbTRIB->Execute($sql);
	if ($res === false){
		if($config['debug'] || $debugQuery){
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $dbTRIB->ErrorMsg();
			echo "\n<br>------------------------------<br>\n";
		}
		return false;
	}else{
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		if(($pos1 === false || $pos1 > 5) && $pos2 === false){
			//			$ret = $db->GenID;
			return true;
		}else{
			$ret = $res->GetRows();
		}
		if($debugRet){
			print_r($ret);
		}
		return $ret;
	}
}

function conectaCONSULT() {
	global $config, $dbTRIB;
	
	if($config['producao'] === true){
		$config['db_bancoCONSULT'] 	= 'pdo';
		$config['db_serverCONSULT'] 	= 'host=192.168.1.252';
		$config['db_databaseCONSULT'] 	= 'marpaconsultoria';
		$config['db_usuarioCONSULT'] 	= 'marpatributario';
		$config['db_senhaCONSULT'] 	= '3F6jOdYh';
	}else{
		$config['db_bancoCONSULT'] 	= 'pdo';
		$config['db_serverCONSULT'] 	= 'host=192.168.1.16';
		$config['db_databaseCONSULT'] 	= 'marpaconsultoria';
		$config['db_usuarioCONSULT'] 	= 'marpatributario';
		$config['db_senhaCONSULT'] 	= '3F6jOdYh';
	}
	
	$dbCONSULT = ADOnewConnection($config['db_bancoCONSULT']);
	$dsnString= $config['db_serverCONSULT'].';dbname='.$config['db_databaseCONSULT'].';charset=utf8';
	$dbCONSULT->connect('mysql:' . $dsnString,$config['db_usuarioCONSULT'],$config['db_senhaCONSULT']);
	//$dbCONSULT->debug = true;
}

function queryCONSULT($sql, $debugQuery = false, $debugRet = false){
	global $dbCONSULT, $config;
	$ret = array();
	if($debugQuery){
		echo "\nSQL: $sql <br>\n";
	}
	if(isset($config['site']['logQuery']) && $config['site']['logQuery']){
		log::gravaLog('logQuery', getModulo().' - '.getClasse().' - '.getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $dbCONSULT->Execute($sql);
	if ($res === false){
		if($config['debug'] || $debugQuery){
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $dbCONSULT->ErrorMsg();
			echo "\n<br>------------------------------<br>\n";
		}
		return false;
	}else{
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		if(($pos1 === false || $pos1 > 5) && $pos2 === false){
			//			$ret = $db->GenID;
			return true;
		}else{
			$ret = $res->GetRows();
		}
		if($debugRet){
			print_r($ret);
		}
		return $ret;
	}
}


function conectaRH() {
	global $config, $dbRH;
	
	if($config['producao'] === true){
		$config['db_bancoRH'] 		= 'pdo';
		$config['db_serverRH'] 		= 'host=192.168.1.252';
		$config['db_databaseRH'] 	= 'marpa';
		$config['db_usuarioRH'] 	= 'marpa2';
		$config['db_senhaRH'] 		= 'mF01lkwvXbpd7w88';
	}else{
		$config['db_bancoRH'] 		= 'pdo';
		$config['db_serverRH'] 		= 'host=192.168.1.16';
		$config['db_databaseRH'] 	= 'marpa';
		$config['db_usuarioRH'] 	= 'marpa2';
		$config['db_senhaRH'] 		= 'mF01lkwvXbpd7w88';
	}
	
	$dbRH = ADOnewConnection($config['db_bancoRH']);
	$dsnString= $config['db_serverRH'].';dbname='.$config['db_databaseRH'].';charset=utf8';
	$dbRH->connect('mysql:' . $dsnString,$config['db_usuarioRH'],$config['db_senhaRH']);
	//$db->debug = true;
}

function queryRH($sql, $debugQuery = false, $debugRet = false){
	global $dbRH, $config;
	$ret = array();
	if($debugQuery){
		echo "\nSQL: $sql <br>\n";
	}
	if(isset($config['site']['logQuery']) && $config['site']['logQuery']){
		log::gravaLog('logQuery', getModulo().' - '.getClasse().' - '.getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $dbRH->Execute($sql);
	if ($res === false){
		if($config['debug'] || $debugQuery){
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $dbRH->ErrorMsg();
			echo "\n<br>------------------------------<br>\n";
		}
		return false;
	}else{
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		if(($pos1 === false || $pos1 > 5) && $pos2 === false){
			//			$ret = $db->GenID;
			return true;
		}else{
			$ret = $res->GetRows();
		}
		if($debugRet){
			print_r($ret);
		}
		return $ret;
	}
}
