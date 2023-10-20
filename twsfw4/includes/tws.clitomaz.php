<?php

if(true){
	$config['db_bancoIECLB'] 	= 'pdo';
	$config['db_serverIECLB'] 	= 'host=localhost';
	$config['db_databaseIECLB'] = 'ieclb';
	$config['db_usuarioIECLB'] 	= 'root';
	$config['db_senhaIECLB'] 	= '';
	
	$dbIECLB = ADOnewConnection($config['db_bancoIECLB']);
	
	$dsnString= $config['db_serverIECLB'].';dbname='.$config['db_databaseIECLB'].';charset=utf8';
	$dbIECLB->connect('mysql:' . $dsnString,$config['db_usuarioIECLB'],$config['db_senhaIECLB']);
	//$dbIECLB->debug = true;
}