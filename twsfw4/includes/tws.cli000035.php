<?php

$dbMF = '';
$dbERP = '';
$dbMRP = '';


/**
 * O BD do monofasico é o mesmo da intranet, então não precisa conectar novamente
 */
function conectaMF()
{
	// Mantido para compatibilidade
}

function queryMF($sql, $debugQuery = false, $debugRet = false)
{
	return query($sql, $debugQuery, $debugRet);
	// Mantido para compatibilidade
}

function conectaMRP()
{
	global $config, $dbMRP;

	if ($config['producao']) {
		$config['db_bancoMRP'] 	= 'postgres';
		$config['db_serverMRP'] 	= '192.168.2.12';
		$config['db_databaseMRP'] = 'marpa_new';
		$config['db_usuarioMRP'] 	= 'postgres';
		$config['db_senhaMRP'] 	= 'e0w2c0i8u2n3';
	} else {
		$config['db_bancoMRP'] 	= 'postgres';
		$config['db_serverMRP'] 	= '192.168.1.14';
		$config['db_databaseMRP'] = 'marpa_new';
		$config['db_usuarioMRP'] 	= 'postgres';
		$config['db_senhaMRP'] 	= 'e0w2c0i8u2n3';
	}
	$db2 = ADONewConnection($config['db_bancoMRP']);
	//$db2->debug = true;
	$db2->Connect($config['db_serverMRP'], $config['db_usuarioMRP'], $config['db_senhaMRP'], $config['db_databaseMRP']);
}

function queryMRP($sql, $debugQuery = false, $debugRet = false)
{
	global $dbMRP, $config;
	$ret = array();
	if ($debugQuery) {
		echo "\nSQL: $sql <br>\n";
	}
	if (isset($config['site']['logQuery']) && $config['site']['logQuery']) {
		log::gravaLog('logQuery', getModulo() . ' - ' . getClasse() . ' - ' . getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $dbMRP->Execute($sql);
	if ($res === false) {
		if ($config['debug'] || $debugQuery) {
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $dbMRP->ErrorMsg();
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

	if ($config['producao']) {
		$config['db_bancoERP'] 		= 'pdo';
		$config['db_serverERP'] 	= 'host=192.168.1.252';
		$config['db_databaseERP'] 	= 'marpatributario';
		$config['db_usuarioERP'] 	= 'marpatributario';
		$config['db_senhaERP'] 		= '3F6jOdYh';
	} else {
		$config['db_bancoERP'] 		= 'pdo';
		$config['db_serverERP'] 	= 'host=192.168.1.16';
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

function criaLoginPortalArquivos($param)
{
	$sql = "SELECT user FROM sys001 WHERE user = '" . $param['cnpj'] . "'";
	$sys001 = query($sql);

	$sql = "SELECT cnpj FROM painel_arquivos WHERE cnpj = '" . $param['cnpj'] . "'";
	$painel = query($sql);

	if (count($sys001) == 0 && count($painel) == 0) {

		// INSERT NA TABELA sys001
		$temp = [];
		$temp['user'] = $param['cnpj'];
		$temp['nome'] = $param['nome'];
		$temp['apelido'] = $param['apelido'] ?? '';
		$temp['origem'] = $param['origem'] ?? 'I';
		$temp['nivel'] = $param['nivel'] ?? 500;
		$temp['cod_recurso'] = $param['cod_recurso'] ?? '';
		$temp['cod_vendedor'] = $param['cod_vendedor'] ?? '';
		$temp['senha'] = uniqid();

		$sql = montaSQL($temp, 'sys001');
		query($sql);


		// INSERT NA TABELA painel_arquivos
		$temp = [];
		$temp['usuario'] = $param['nome'];
		$temp['cnpj'] = $param['cnpj'];
		$temp['contrato'] = $param['contrato'];

		$sql = montaSQL($temp, 'painel_arquivos');
		query($sql);


		// INSERT NA TABELA sys115
		$temp = [];
		$temp['user'] = $param['cnpj'];
		$temp['programa'] = 'painel_arquivos.portal_arquivos.index';
		$temp['perm'] = 'S';

		$sql = montaSQL($temp, 'sys115');
		query($sql);
	}
}

/**
 * 
 * @param string $aruivo - caminho do arquivo xml a ser lido
 */

function carregaXMLnota($arquivo, $log = '')
{
	$ret = '';

	putAppVar('erro_imp_xml_monofasico', '');

	if (!is_file($arquivo)) {
		if (!empty($log)) {
			log::gravaLog($log, "Não foi possível abrir o arquivo $arquivo");
		}
		putAppVar('erro_imp_xml_monofasico', "Não existe: $arquivo");
		return false;
	}

	$conteudo = file_get_contents($arquivo);
	if ($conteudo === false) {
		if (!empty($log)) {
			log::gravaLog($log, "Erro ao ler o arquivo - $arquivo");
		}
		putAppVar('erro_imp_xml_monofasico', "Erro ao ler o arquivo - $arquivo");
		return false;
	}

	@$ret = simplexml_load_string($conteudo);

	if ($ret === false) {
		$conteudo = utf8_encode($conteudo);

		@$ret = simplexml_load_string($conteudo);
	}


	if ($ret === false) {
		$conteudo = str_replace('&', '-', $conteudo);
		$conteudo = str_replace('<<', '-', $conteudo);
		$conteudo = str_replace('>>', '-', $conteudo);
		$conteudo = str_replace('ï»¿', '', $conteudo);

		@$ret = simplexml_load_string($conteudo);
	}


	if ($ret === false) {
		//echo $conteudo;
		if (!empty($log)) {
			log::gravaLog($log, "Erro simplexml_load_string - $arquivo");
		}
		putAppVar('erro_imp_xml_monofasico', "Erro - XML inválido - $arquivo");
		return false;
	}

	return $ret;
}

/**
 * Grava na talema mgt_monofasico_log_xml a movimentação do arquivo
 * 
 * Autor: Alexandre Thiel
 * @param string $cnpj - CNPJ do cliente
 * @param string $contrato - Contrado
 * @param string $arquivo - arquivo que foi processado
 * @param string $obs - observação 
 */
function gravaLogLeituraXMLmonofasico($cnpj, $contrato, $arquivo, $obs, $log_destinatario, $log_emitente, $log_operacao, $erro = '')
{
	if (!empty($contrato) && !empty($cnpj) && !empty($arquivo) && !empty($obs)) {
		$campos = [];
		$campos['cnpj'] 		= $cnpj;
		$campos['contrato'] 	= $contrato;
		$campos['arquivo'] 		= $arquivo;
		$campos['obs'] 			= $obs;
		$campos['destinatario']	= $log_destinatario;
		$campos['emitente']		= $log_emitente;
		$campos['operacao']		= $log_operacao;
		$campos['usuario'] 		= getUsuario();
		$campos['erro']			= $erro;

		$sql = montaSQL($campos, 'mgt_monofasico_log_xml');
		query($sql);
	}
}

function verifica_quant_arquivos($dir)
{
	$ret = 0;
	if (substr($dir, -1) !== DIRECTORY_SEPARATOR) {
		$dir .= DIRECTORY_SEPARATOR;
	}
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $file) {
			if (is_file($dir . $file)) {
				$ret++;
			}
		}
	}

	return $ret;
}
