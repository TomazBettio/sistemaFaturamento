<?php
/*
 * Data Criacao: 23/10/2013 - 16:37:59
 * Autor: Thiel
 *
 * Arquivo: class.000002.inc.php
 *
 * Modificações:
 *				 Emanuel - 31/10/2018 - a função vendas1464Campo foi trocada pela da intranet 1
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

if(true){
	$config["db4_banco"] 	= "oci8";
	$config["db4_server"] 	= "10.0.0.92";
	$config["db4_database"] = "WINT";
	$config["db4_usuario"] 	= "gauchafarma";
	$config["db4_senha"] 	= "ga1ch4fa3m9";
	
	
	$db4 = ADONewConnection($config["db4_banco"]);
	$db4->Connect($config["db4_server"], $config["db4_usuario"], $config["db4_senha"], $config["db4_database"]);
	//$db4->debug = true;

	// Conectar base teste
	$config["db5_banco"] 	= "oci8";
	$config["db5_server"] 	= "10.0.0.93";
	$config["db5_database"] = "TESTE";
	$config["db5_usuario"] 	= "GAUCHATESTE";
	$config["db5_senha"] 	= "GAUCHATESTE";
	
	$db5 = null;
	
	$config['db_f1_banco'] 	    = 'pdo';
	$config['db_f1_server'] 	= 'host=10.0.0.122';
	$config['db_f1_database'] 	= 'f1';
	$config['db_f1_usuario'] 	= 'f1';
	$config['db_f1_senha'] 	    = 'f1senha123';
	
	$db_f1 = null;
}

function conectaBaseF1($debug = false){
	global $config, $db_f1;

	$db_f1 = ADOnewConnection($config['db_f1_banco']);
	$dsnString= $config['db_f1_server'].';dbname='.$config['db_f1_database'].';charset=utf8';
	$db_f1->connect('mysql:' . $dsnString,$config['db_f1_usuario'],$config['db_f1_senha']);
	$db_f1->setCharset('utf8');
	
	if($debug){
		$db_f1->debug = true;
	}
	
}


function conectaBaseTeste($debug = true){
	global $config, $db5;
	
	$db5 = ADONewConnection($config["db5_banco"]);
	$db5->Connect($config["db5_server"], $config["db5_usuario"], $config["db5_senha"], $config["db5_database"]);
	if($debug){
		$db5->debug = true;
	}
	
}

function getCampoSupervisor($super, $campo = 'nome'){
	$ret = '';
	if($campo == 'email'){
		$campo = "(select us2.email from pcusuari us2 where us2.codusur = pcsuperv.cod_cadrca) email";
	}
	$sql = "select $campo from pcsuperv where codsupervisor = $super";
	//echo "SQL: $sql <br> \n";
	$rows = query4($sql);
	if(count($rows) > 0){
		$ret = $rows[0][0];
	}
	
	return $ret;
}

function getCampoERC($erc, $campo = 'nome'){
	$ret = '';
	$sql = "select $campo from pcusuari where codusur = $erc";
	//echo "SQL: $sql <br> \n";
	$rows = query4($sql);
	if(count($rows) > 0){
		$ret = $rows[0][0];
	}
	
	return $ret;
}

function query4($sql, $traceSQL = false, $traceRES = false){
	global $db4;
//echo $sql."\n";
	$ret = array();
	if($traceSQL){
		echo "\nSQL: $sql <br>\n";
	}
	$res = $db4->Execute($sql);
	if($traceRES){
		print_r($res);
	}
	
	if (!$res ){
		echo "<br>\nSQL: $sql<br>\n";
		print "<br><br>Erro SQL: ".$db4->ErrorMsg()."<br><br>Erro";
		return false;
	}else{
		if(strpos(strtoupper($sql), "SELECT") === false){
			$ret = $res;
		}else{
			while ($arr = $res->FetchRow()) {
				$ret[] = $arr;
			}
		}
		return $ret;
	}
}

function query4C($sql, $traceSQL = false, $traceRES = false){
	global $db4;
	//echo $sql."\n";
	$ret = array();
	if($traceSQL){
		echo "\nSQL: $sql <br>\n";
	}
	$db4->debug = true;
	$res = $db4->Execute($sql);
	if  ($db4->hasFailedTrans()){
		echo "<br>\n----------------- ERRO -> Fazendo rollback ------------ <br>\n";
	}else{
		echo "<br>\n----------------- Commit ------------ <br>\n";
	}
	$db4->completeTrans();
	$db4->debug = false;
	if($traceRES){
		print_r($res);
	}
	
	if (!$res ){
		echo "<br>\nSQL: $sql<br>\n";
		print "<br><br>Erro SQL: ".$db4->ErrorMsg()."<br><br>Erro";
		return false;
	}else{
		if(strpos(strtoupper($sql), "SELECT") === false){
			$ret = $res;
		}else{
			while ($arr = $res->FetchRow()) {
				$ret[] = $arr;
			}
		}
		return $ret;
	}
}

function query5($sql){
	global $db5;
	//echo $sql."<br>";
	$ret = array();
	$res = $db5->Execute($sql);
	if (!$res ){
		print "<br><br>Erro SQL: ".$db5->ErrorMsg()."<br><br>Erro";
		return false;
	}else{
		if(strpos(strtoupper($sql), "SELECT") === false){
			$ret = $res;
		}else{
			while ($arr = $res->FetchRow()) {
				$ret[] = $arr;
			}
		}
		return $ret;
	}
}

	function getDadosRCA($rca){
		$ret = array();
		$sql = "select pcusuari.codusur,
						       pcusuari.nome,
						       pcusuari.email,
						       pcusuari.codsupervisor,
						       pcsuperv.nome,
						       (select us2.email from pcusuari us2 where us2.codusur = pcsuperv.cod_cadrca) email_super
						from pcusuari,
						     pcsuperv
						where pcusuari.codusur = $rca
						    and pcusuari.codsupervisor = pcsuperv.codsupervisor (+)";
		$rows = query4($sql);
		foreach ($rows as $row){
			$ret['rca'] = $row[0];
			$ret['nome'] = $row[1];
			$ret['email'] = $row[2];
			$ret['super'] = $row[3];
			$ret['super_nome'] = $row[4];
			$ret['super_email'] = $row[5];
		}
		return $ret;
	}
	
	function getEmailCoordenador($super){
		$ret = '';
		$sql = "select pcsuperv.codsupervisor,
						       pcsuperv.nome,
						       (select us2.email from pcusuari us2 where us2.codusur = pcsuperv.cod_cadrca) email
						from pcsuperv
						where pcsuperv.codsupervisor = $super";
//echo "SQL: $sql <br> \n";
		$rows = query4($sql);	
		if(count($rows) > 0){
			$ret = $rows[0][2];
		}
		
		return $ret;
	}
	
	function getEmailERC($erc){
		$ret = '';
				$sql = "select pcusuari.email from pcusuari where pcusuari.codusur = $erc";
	//echo "SQL: $sql <br> \n";
			$rows = query4($sql);	
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	function getListaEmailGF($tipo,$ativo = true,$ordem = '', $filtrar = true){
		$ret = array();
		switch ($tipo) {
			case 'rca':
				$naoEnviarEmail = array(11,268,600);
				$sql = "SELECT PCUSUARI.CODUSUR, 
						       PCUSUARI.NOME, 
						       PCUSUARI.EMAIL,
						       PCUSUARI.CODSUPERVISOR, 
						       PCSUPERV.NOME,
						       (SELECT US2.EMAIL FROM PCUSUARI US2 WHERE US2.CODUSUR = PCSUPERV.COD_CADRCA) EMAIL_SUPER,
								PCUSUARI.DTTERMINO,
								BLOQUEIO
						FROM PCUSUARI,
						     PCSUPERV
						WHERE PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
						";
				if($ativo){
					$sql .= "AND pcusuari.dttermino is null and bloqueio = 'N'";
				}
				if($ordem == 'nome'){
					$sql .= " ORDER BY PCUSUARI.NOME";
				}else{
					$sql .= " ORDER BY PCUSUARI.CODUSUR";
				}
				$rows = query4($sql);

				$i = 0;
				foreach ($rows as $row){
					$ret[$i]['rca'] = $row[0];
					$ret[$i]['nome'] = $row[1];
					if($row['DTTERMINO'] != '' || $row['BLOQUEIO'] == 'S'){
						$ret[$i]['ativo'] = 'N';
					}else{
						$ret[$i]['ativo'] = 'S';
					}
					
					$direto = false;
					if(strpos(strtoupper($ret[$i]['nome']), "DIRETO") !== false){
						$direto = true;
					}
					
					
					if(array_search($row[0], $naoEnviarEmail) === false && $ret[$i]['ativo'] == 'S' && !$direto){
						$ret[$i]['email'] = $row[2];
					}else{
						$ret[$i]['email'] = '';
					}
					$ret[$i]['super'] = $row[3];
					$ret[$i]['super_nome'] = $row[4];
					$ret[$i]['super_email'] = $row[5];
					$i++;
				}
				break;
			case 'supervisor':
				$naoEnviarEmail = array(14,15);
				$sql = "select pcsuperv.codsupervisor,
						       pcsuperv.nome,
						       (select us2.email from pcusuari us2 where us2.codusur = pcsuperv.cod_cadrca) email
						from pcsuperv
						where pcsuperv.dtdemissao is null";
				$rows = query4($sql);
				$i = 0;
				foreach ($rows as $row){
					$ret[$i]['super'] = $row[0];
					$ret[$i]['nome'] = $row[1];
					if(array_search($row[0], $naoEnviarEmail) === false){
						$ret[$i]['email'] = $row[2];
					}else{
						$ret[$i]['email'] = '';
					}
					if($row[0] == 19){
						$ret[$i]['email'] .= ';tele11@gauchafarma.com';
					}
					$i++;
				}
				break;
		}
		
		return $ret;
	}
	
	function getListaOperadores($inativos = false){
		$ret = [];
		$sql = "
			SELECT
			    MATRICULA,
			    NOME,
				EMAIL
			FROM
			    PCEMPR
			WHERE
			    CODPERFILTELEVMED IS NOT NULL
			";
		
		if(!$inativos){
			$sql .= "AND SITUACAO = 'A' AND DTDEMISSAO IS NULL";
		}
		//echo "SQL: $sql<br>\n";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$operador = $row['MATRICULA'];
				$ret[$operador]['nome'] = $row['NOME'];
				$ret[$operador]['email'] = $row['EMAIL'];
			}
		}
		
		return $ret;
	}
	
	function getDeptoGaucha(){
		$ret = array();
		$ret[0][0] = '';
		$ret[0][1] = '';
		$sql = "SELECT * FROM depto ORDER BY depto";
		$rows = query($sql);
		$i=1;
		foreach ($rows as $row){
			$ret[$i][0] = $row[0];
			$ret[$i][1] = $row[1];
			$i++;
		}
		return $ret;
	}
	
	
	function query3($sql){
		global $db3, $config;
		$ret = array();
		//echo "\nSQL: $sql <br>\n";
		//print_r($db);
		$res = $db3->Execute($sql);
		if (!$res ){
			if($config["site"]["debug"])
				print $db3->ErrorMsg();
		}else{
			if(strpos(strtoupper(substr($sql,0,10)), "SELECT") === false){
				//			$ret = $db3->GenID;
			}else{
				$ret = $res->GetRows();
			}
			return $ret;
		}
	}
	
	/*
	 * Fun��o para ser utilizada no campo sys4_funcaodados 
	 */
	
	function sys044_getERC($branco = true){
		$tabela = array();
		if($branco){
			$tabela[0][0] = "";
			$tabela[0][1] = "&nbsp;";
		}
	
			
		$sql = "SELECT codusur, codusur || ' - ' || nome
				FROM  pcusuari
				ORDER BY nome";
		$rows = query4($sql);
	
		$i = count($tabela);
		foreach ($rows as $row) {
			$tabela[$i][0] = $row[0];
			$tabela[$i][1] = $row[1];
			$i++;
		}
		return $tabela;
	}
	
	/*
	 * Fun��o para ser utilizada no campo sys4_funcaodados
	 */
	
	function sys044_getSupervisor($branco = true){
		$tabela = array();
		if($branco){
			$tabela[0][0] = "";
			$tabela[0][1] = "&nbsp;";
		}
	
	
		$sql = "SELECT codsupervisor, codsupervisor || ' - ' || nome
				FROM  pcsuperv
				ORDER BY nome";
		$rows = query4($sql);
	
		$i = count($tabela);
		foreach ($rows as $row) {
			$tabela[$i][0] = $row[0];
			$tabela[$i][1] = $row[1];
			$i++;
		}
		return $tabela;
	}
	
	/*
	 * Retorna as redes
	 */
	function sys044_redes($branco = true){
		$tabela = array();
		if($branco){
			$tabela[0][0] = "";
			$tabela[0][1] = "&nbsp;";
		}
	
	
		$sql = "select codrede, descricao from pcredecliente order by descricao";
		$rows = query4($sql);
	
		$i = count($tabela);
		foreach ($rows as $row) {
			$tabela[$i][0] = $row[0];
			$tabela[$i][1] = $row[1];
			$i++;
		}
		return $tabela;
	}
	
	function vendas1464($dataIni, $dataFim, $param, $trace = false){
		$whereFornecedor = '';
		$whereDepto = '';
		$whereOrigem = '';
		$whereSecao = '';
		$whereSuper = '';
		$whereERC = '';
		$whereCli = '';
		$whereCliDev = '';
		$whereProduto = '';
		$whereMarca = '';
		$wherePedidoFora = '';
		$wherePedidoForaDev = '';
		
		if(isset($param['pedidoFora']) && !empty($param['pedidoFora'])){
			$wherePedidoFora = " AND PCNFSAID.NUMPED NOT IN ( ".$param['pedidoFora']." )";
			$wherePedidoForaDev = " AND PCNFSAID.NUMPED NOT IN ( ".$param['pedidoFora']." )";
		}
		
		if(isset($param['marca']) && !empty($param['marca'])){
			$whereMarca= " AND (PCPRODUT.CODMARCA IN ( ".$param['marca']." ))";
		}
		if(isset($param['produto']) && !empty($param['produto'])){
			$whereProduto = " AND (PCPRODUT.CODPROD IN ( ".$param['produto']." ))";
		}
		if(isset($param['secao']) && !empty($param['secao'])){
			$whereSecao = " AND (PCPRODUT.CODSEC IN ( ".$param['secao']." ))";
		}
		if(isset($param['super']) && !empty($param['super'])){
			$whereSuper = " AND (PCNFSAID.CODSUPERVISOR IN ( ".$param['super']." ))";
		}
		if(isset($param['ERC']) && !empty($param['ERC'])){
			$whereERC = " AND (PCNFSAID.CODUSUR IN ( ".$param['ERC']." ))";
		}
		if(isset($param['erc']) && !empty($param['erc'])){
			$whereERC = " AND (PCNFSAID.CODUSUR IN ( ".$param['erc']." ))";
		}
		if(isset($param['cliente']) && !empty($param['cliente'])){
			$whereCli = " AND (PCNFSAID.CODCLI IN ( ".$param['cliente']." ))";
			$whereCliDev =" AND (PCNFENT.CODFORNEC IN ( ".$param['cliente']." ))";
		}
		if(isset($param['fornecedor']) && !empty($param['fornecedor'])){
			$whereFornecedor = " AND (PCPRODUT.CODFORNEC IN ( ".$param['fornecedor']." ))";
		}
		if(isset($param['depto']) && !empty($param['depto'])){
			$whereDepto = " AND PCPRODUT.CODEPTO IN (".$param['depto'].")";
		}else{
			$whereDepto = " AND PCPRODUT.CODEPTO IN (1,12)";
		}
		if(isset($param['origem']) && !empty($param['origem'])){
			switch ($param['origem']) {
				case 'OL':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					break;
				case 'NOL':
					$whereOrigem = " AND NVL(PCNFSAID.NUMPED,0) NOT IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					break;
					case 'PE':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'PE') ";
					break;
				case 'T':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'T') ";
					break;
				case 'PDA':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV IS NULL) ";
					break;
				default:
					$whereOrigem = "";
					break;
			}
				
		}

		
		$sql = "
SELECT NVL(VENDAS.CODUSUR, DEVOLUCAO.CODUSURDEVOL) CODUSUR, 
        NVL(VENDAS.NOME, DEVOLUCAO.NOME) NOME,
       (NVL(VLMETA, 0)) VLMETA,
       (NVL(QTMETA, 0)) QTMETA,
       SUM(NVL(VENDAS.VLREPASSE,0)) VLREPASSE,
       (NVL(QTPESOMETA, 0)) QTPESOMETA,
       (NVL(MIXPREV, 0)) MIXPREV,
       (NVL(CLIPOSPREV, 0)) CLIPOSPREV,
       SUM(NVL(VENDAS.QTVENDA,0) - NVL(DEVOLUCAO.QTDEVOLUCAO,0)) QTVENDA,
       SUM(NVL(VENDAS.VLVENDA,0) - NVL(DEVOLUCAO.VLDEVOLUCAO,0)) VLVENDA,
       SUM(NVL(VENDAS.VLVENDA_SEMST,0) - NVL(DEVOLUCAO.VLDEVOLUCAO_SEMST,0)) VLVENDA_SEMST,
       SUM(NVL(DEVOLUCAO.VLDEVOLUCAO,0)) VLDEVOLUCAO,
       SUM(NVL(DEVOLUCAO.VLDEVOLUCAO_SEMST,0)) VLDEVOLUCAO_SEMST,
       SUM(NVL(DEVOLUCAO.QTDEVOLUCAO, 0)) QTDEVOLUCAO,
       SUM(NVL(VENDAS.TOTPESO,0) - NVL(DEVOLUCAO.TOTPESO,0) ) TOTPESO,
       SUM(NVL(VENDAS.VLCUSTOFIN,0) - 0) VLCUSTOFIN, 
       SUM(NVL(VENDAS.VLBONIFIC,0)) VLBONIFIC,
       MAX ( DISTINCT(NVL(VENDAS.QTCLIPOS,0))) QTCLIPOS,
       SUM( NVL(VENDAS.QTCLIPOS, 0) - NVL(DEVOLUCAO.QTCLIPOSNEG, 0) ) QTCLIPOSMENOSDEVOL,
       max(DISTINCT(VENDAS.QTMIX)) QTMIX,
       SUM(NVL(VENDAS.VLVENDA,0) - NVL(DEVOLUCAO.VLDEVOLUCAO,0) - (NVL(VENDAS.VLCUSTOFIN,0)))  VLLUCRO,
       SUM(NVL(VENDAS.VLVENDA_SEMST,0) - NVL(DEVOLUCAO.VLDEVOLUCAO_SEMST,0) - (NVL(VENDAS.VLCUSTOFIN,0)))  VLLUCRO_SEMST,
       SUM(NVL(VENDAS.VOLUME,0) - NVL(DEVOLUCAO.VOLUME,0)) VOLUME,
       SUM(NVL(VENDAS.LITRAGEM,0) - NVL(DEVOLUCAO.LITRAGEM,0)) LITRAGEM
FROM  
		
-------------------------------------------------------------------------------------------------------------------------------------
		
		(SELECT CODUSUR,
              NOME,
              SUM(NVL(QTVENDA,0)) QTVENDA,
              SUM(NVL(VLVENDA,0) + NVL(VALORST,0) + NVL(VALORIPI,0)) VLVENDA,
              SUM(NVL(VLVENDA_SEMST,0)) VLVENDA_SEMST,
              SUM(NVL(TOTPESO,0)) TOTPESO,
              SUM(NVL(VLCUSTOFIN,0) - 0) VLCUSTOFIN, 
              SUM(NVL(VLBONIFIC,0)) VLBONIFIC,
              COUNT(DISTINCT(QTCLIPOS)) QTCLIPOS,
              COUNT(DISTINCT(QTMIX)) QTMIX,
              SUM(NVL(VOLUME,0)) VOLUME,
              SUM(NVL(VLREPASSE,0)) VLREPASSE,
              SUM(NVL(LITRAGEM,0)) LITRAGEM FROM (  SELECT PCNFSAID.NUMTRANSVENDA, PCMOV.CODCLI,
			   PCCLIENT.CLIENTE,
			   PCFORNEC.CODFORNECPRINC,
			 PCFORNEC.FORNECEDOR,
			   PCFORNEC.CODFORNEC,
			   PCUSUARI.CODUSUR, 
			   PCUSUARI.NOME, 
			   PCNFSAID.CODSUPERVISOR, 
			   PCSUPERV.NOME SUPERV, 
			   PCPRODUT.CODEPTO, 
			   PCPRODUT.CODSEC, 
			   PCDEPTO.DESCRICAO DEPARTAMENTO, 
			   PCSECAO.DESCRICAO SECAO, 
			   PCNFSAID.CODPRACA, 
			   PCPRACA.PRACA, 
			   PCPRODUT.CODMARCA, 
			   PCMARCA.MARCA, 
			   PCCLIENT.ESTENT, 
			   PCCLIENT.MUNICENT,
			   PCCLIENT.CODCIDADE,
			   PCCIDADE.NOMECIDADE,
			   NVL(PCCLIENT.CODCLIPRINC, PCCLIENT.CODCLI) CODCLIPRINC, 
			   (SELECT X.CLIENTE 
				  FROM PCCLIENT X 
				 WHERE X.CODCLI = NVL(PCCLIENT.CODCLIPRINC, PCCLIENT.CODCLI)) CLIENTEPRINC, 
			   ROUND( (NVL(PCPRODUT.VOLUME, 0) * NVL(PCMOV.QT, 0)),2)  VOLUME, 
			  (NVL(PCPRODUT.LITRAGEM, 0) * NVL(PCMOV.QT, 0))  LITRAGEM, 
			   PCATIVI.RAMO,
			   PCATIVI.CODATIV,
			   PCMOV.CODPROD,
			   PCPRODUT.DESCRICAO,
			   PCPRODUT.EMBALAGEM,
			   PCPRODUT.UNIDADE,
			   PCPRODUT.CODFAB,
			   PCNFSAID.CODPLPAG,
			   PCNFSAID.NUMPED,
			   PCNFSAID.CODCOB,
			   PCCLIENT.CODPLPAG CODPLANOCLI,
			   PCPLPAG.DESCRICAO DESCRICAOPCPLPAG,
			   PCPLPAG.NUMDIAS, 
			   0 QTMETA,
			   0 QTPESOMETA,
			   0 MIXPREV,
			   0 CLIPOSPREV,
			   ROUND((NVL(PCMOV.QT, 0) * 
				 DECODE(PCNFSAID.CONDVENDA,
						 5,
						 0,
						 6,
						 0,
						 11,
						 0,
						 12,
						 0,
						 DECODE(PCMOV.CODOPER,'SB',0,nvl(pcmov.VLIPI,0)))),2) VALORIPI,
			   ROUND((NVL(PCMOV.QT, 0) * 
				 DECODE(PCNFSAID.CONDVENDA,
						 5,
						 0,
						 6,
						 0,
						 11,
						 0,
						 12,
						 0,
						 DECODE(PCMOV.CODOPER,'SB',0,nvl(pcmov.st,0)))),2) VALORST,
			  (SELECT PCCLIENT.CODPLPAG || ' - ' || PCPLPAG.DESCRICAO  FROM PCPLPAG WHERE PCCLIENT.CODPLPAG = PCPLPAG.CODPLPAG) DESCRICAOPLANOCLI,
			   ((DECODE(PCMOV.CODOPER,  
								   'S', 
								   (NVL(DECODE(PCNFSAID.CONDVENDA, 
											   7, 
											   PCMOV.QTCONT, 
											   PCMOV.QT), 
										0)), 
								   'SM', 
								   (NVL(DECODE(PCNFSAID.CONDVENDA, 
											   7, 
											   PCMOV.QTCONT, 
											   PCMOV.QT), 
										0)), 
								   'ST', 
								   (NVL(DECODE(PCNFSAID.CONDVENDA, 
											   7, 
											   PCMOV.QTCONT, 
											   PCMOV.QT), 
										0)), 
								   'SB', 
								   (NVL(DECODE(PCNFSAID.CONDVENDA, 
											   7, 
											   PCMOV.QTCONT, 
											   PCMOV.QT), 
										0)), 
								   0))) QTVENDA, 
						  ((DECODE(PCMOV.CODOPER                                
								  ,'S'                                        
								  ,(NVL(DECODE(PCNFSAID.CONDVENDA,              
											   7,                               
											   PCMOV.QTCONT,                    
											   PCMOV.QT),                       
										0))                                     
								  ,'ST'                                       
								  ,(NVL(DECODE(PCNFSAID.CONDVENDA,              
											   7,                               
											   PCMOV.QTCONT,                    
											   PCMOV.QT),                       
										0))                                     
								  ,'SM'                                       
								  ,(NVL(DECODE(PCNFSAID.CONDVENDA,              
											   7,                               
											   PCMOV.QTCONT,                    
											   PCMOV.QT),                       
										0))                                     
								  ,'SB'                                       
								  ,(NVL(DECODE(PCNFSAID.CONDVENDA,              
											   7,                               
											   PCMOV.QTCONT,                    
											   PCMOV.QT),                       
										0))                                     
								  ,0)) * (NVL(PCMOV.CUSTOFIN, 0))) VLCUSTOFIN,  
			   ROUND((((DECODE(PCMOV.CODOPER,                                           
							   'S',                                                   
							   (NVL(DECODE(PCNFSAID.CONDVENDA,                          
										   7,                                           
										   PCMOV.QTCONT,                                
										   PCMOV.QT),                                   
									0)),                                                
							   'ST',                                                  
							   (NVL(DECODE(PCNFSAID.CONDVENDA,                          
										   7,                                           
										   PCMOV.QTCONT,                                
										   PCMOV.QT),                                   
									0)),                                                
							   'SM',                                                  
							   (NVL(DECODE(PCNFSAID.CONDVENDA,                          
										   7,                                           
										   PCMOV.QTCONT,                                
										   PCMOV.QT),                                   
									0)),                                                
							   0)) *                                                    
					 (NVL(DECODE(PCNFSAID.CONDVENDA,                                    
								   7,                                                   
								   (NVL(PUNITCONT, 0) - NVL(PCMOV.VLIPI, 0) -           
								   NVL(PCMOV.ST, 0)) + NVL(PCMOV.VLFRETE, 0) +          
								   NVL(PCMOV.VLOUTRASDESP, 0) +                         
								   NVL(PCMOV.VLFRETE_RATEIO, 0) +                       
								   DECODE(PCMOV.TIPOITEM,                               
										  'C',                                        
										  (SELECT NVL((SUM(M.QTCONT *                   
														   NVL(M.VLOUTROS, 0)) /        
												  PCMOV.QT), 0) VLOUTROS                
											 FROM PCMOV M                               
											WHERE M.NUMTRANSVENDA =                     
												  PCMOV.NUMTRANSVENDA                   
											  AND M.TIPOITEM = 'I'                    
											  AND CODPRODPRINC = PCMOV.CODPROD),        
										  NVL(PCMOV.VLOUTROS, 0)) -                     
								   NVL(PCMOV.VLREPASSE, 0),                             
								   (NVL(PCMOV.PUNIT, 0) - NVL(PCMOV.VLIPI, 0) -         
								   NVL(PCMOV.ST, 0)) + NVL(PCMOV.VLFRETE, 0) +          
								   NVL(PCMOV.VLOUTRASDESP, 0) +                         
								   NVL(PCMOV.VLFRETE_RATEIO, 0) +                       
								   DECODE(PCMOV.TIPOITEM,                               
										  'C',                                        
										  (SELECT NVL((SUM(M.QTCONT *                   
														   NVL(M.VLOUTROS, 0)) /        
												  PCMOV.QT), 0) VLOUTROS                
											 FROM PCMOV M                               
											WHERE M.NUMTRANSVENDA =                     
												  PCMOV.NUMTRANSVENDA                   
											  AND M.TIPOITEM = 'I'                    
											  AND CODPRODPRINC = PCMOV.CODPROD),        
										  NVL(PCMOV.VLOUTROS, 0)) -                     
								   NVL(PCMOV.VLREPASSE, 0)),                            
							0)))),                                                      
					 2) VLVENDA,                                                        
																						
			   (((DECODE(PCMOV.CODOPER,                                                 
						 'S',                                                         
						 (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),    
							  0)),                                                      
						 'ST',                                                        
						 (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),    
							  0)),                                                      
						 'SM',                                                        
						 (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),    
							  0)),                                                      
						 0)) *                                                          
			   (NVL(DECODE(PCNFSAID.CONDVENDA,                                          
							 7,                                                         
							 PCMOV.PUNITCONT,                                           
							 NVL(PCMOV.PUNIT, 0) + NVL(PCMOV.VLFRETE, 0) +              
							 NVL(PCMOV.VLOUTRASDESP, 0) +                               
							 NVL(PCMOV.VLFRETE_RATEIO, 0) +                             
							 DECODE(PCMOV.TIPOITEM,                                     
									'C',                                              
									(SELECT (SUM(M.QTCONT * NVL(M.VLOUTROS, 0)) /       
											PCMOV.QT) VLOUTROS                          
									   FROM PCMOV M                                     
									  WHERE M.NUMTRANSVENDA = PCMOV.NUMTRANSVENDA       
										AND M.TIPOITEM = 'I'                          
										AND CODPRODPRINC = PCMOV.CODPROD),              
									NVL(PCMOV.VLOUTROS, 0)) -                           
							 NVL(PCMOV.VLREPASSE, 0) - NVL(PCMOV.ST, 0)),               
					  0)))) VLVENDA_SEMST,                                              
			  ROUND(    (NVL(PCMOV.QT, 0) *
			   DECODE(PCNFSAID.CONDVENDA,
					   5,
					   DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
					   6,
					   DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
					   11,
					   DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
					   1,
					   NVL(PCMOV.PBONIFIC,0),                                      
					   12,
					   DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
					   0)),2) VLBONIFIC,
					   ((DECODE(PCMOV.CODOPER,
								   'S',
								   (NVL(DECODE(PCNFSAID.CONDVENDA,
											   7,
											   PCMOV.QTCONT,
											   PCMOV.QT),
										0)),
								   'ST',
								   (NVL(DECODE(PCNFSAID.CONDVENDA,
											   7,
											   PCMOV.QTCONT,
											   PCMOV.QT),
										0)),
								   'SM',
								   (NVL(DECODE(PCNFSAID.CONDVENDA,
											   7,
											   PCMOV.QTCONT,
											   PCMOV.QT),
										0)),
								   0))) QTVENDIDA,
			   ROUND( (NVL(PCPRODUT.PESOBRUTO,0) * NVL(PCMOV.QT, 0)),2) AS TOTPESO,
			   ROUND(PCMOV.QT * (PCMOV.PTABELA
							   + NVL (pcmov.vlfrete, 0) + NVL (pcmov.vloutrasdesp, 0) + NVL (pcmov.vlfrete_rateio, 0) + NVL (pcmov.vloutros, 0) - NVL (pcmov.vlrepasse, 0)),2) VLTABELA,
			   PCMOV.CODCLI QTCLIPOS,
			   PCNFSAID.NUMTRANSVENDA QTNUMTRANSVENDA, 
			   PCNFSAID.CODFILIAL, 
			  (SELECT PCFILIAL.FANTASIA 
					  FROM PCNFSAID P, PCFILIAL  
					 WHERE P.CODFILIAL = PCFILIAL.CODIGO 
					   AND P.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA  AND ROWNUM = 1) FILIAL,
			   PCPRODUT.CODPROD AS QTMIXCAD,
			   PCMOV.CODPROD AS QTMIX, 
		   (SELECT COUNT(*) FROM PCPRODUT P WHERE P.CODFORNEC = PCFORNEC.CODFORNEC) QTMIXCADNOVO,
 PCGERENTE.NOMEGERENTE,
 DECODE(PCNFSAID.NUMTRANSVENDA,NULL,PCSUPERV.CODGERENTE,PCNFSAID.CODGERENTE) CODGERENTE, 
 PCPRACA.ROTA,
 PCROTAEXP.DESCRICAO DESCROTA,
 NVL(PCMOV.QT,0) * NVL(PCMOV.VLREPASSE,0) VLREPASSE, 
 PCPRODUT.CODAUXILIAR
  FROM PCNFSAID,
       PCPRODUT,
       PCMOV,
       PCCLIENT,
       PCUSUARI,
       PCSUPERV,
       PCPLPAG,
       PCFORNEC,
       PCATIVI, 
       PCPRACA,
       PCDEPTO,
       PCSECAO,
       PCPEDC,
       PCGERENTE,
       PCCIDADE,
       PCMARCA,
       PCROTAEXP
 WHERE PCMOV.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA
   AND PCMOV.DTMOV BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD') 
   AND PCMOV.CODPROD = PCPRODUT.CODPROD
   AND PCNFSAID.CODPRACA = PCPRACA.CODPRACA(+)
   AND PCATIVI.CODATIV(+) = PCCLIENT.CODATV1
   AND PCMOV.CODCLI = PCCLIENT.CODCLI
   AND PCFORNEC.CODFORNEC = PCPRODUT.CODFORNEC
   AND PCNFSAID.CODUSUR = PCUSUARI.CODUSUR
   AND PCPRACA.ROTA = PCROTAEXP.CODROTA(+)
   AND PCPRODUT.CODMARCA = PCMARCA.CODMARCA(+)
   AND PCCLIENT.CODCIDADE = PCCIDADE.CODCIDADE(+)
  AND PCMOV.CODOPER <> 'SR' 
  AND PCMOV.CODOPER <> 'SO' 
   AND PCNFSAID.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR(+)
   AND PCNFSAID.CODPLPAG = PCPLPAG.CODPLPAG
   AND PCNFSAID.NUMPED = PCPEDC.NUMPED(+)
   AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO(+)
   AND PCPRODUT.CODSEC = PCSECAO.CODSEC(+)
   AND PCNFSAID.CODGERENTE = PCGERENTE.CODGERENTE(+) 
   AND PCMOV.CODOPER NOT IN ('SI')
   AND PCNFSAID.CODFISCAL NOT IN (522, 622, 722, 532, 632, 732)
   AND PCNFSAID.CONDVENDA NOT IN (4, 8, 10, 13, 20, 98, 99)
   AND (PCNFSAID.DTCANCEL IS NULL)
   	$whereFornecedor
   	$whereDepto
	$whereOrigem
	$whereSecao
	$whereSuper
	$whereERC
	$whereCli
	$whereProduto
	$whereMarca
	$wherePedidoFora
   AND PCNFSAID.DTSAIDA BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD') 
           AND PCMOV.CODFILIAL IN('1')
           AND PCNFSAID.CODFILIAL IN('1')
)
        GROUP BY CODUSUR,
              NOME
              ) VENDAS, 
			  
			  
-------------------------------------------------------------------------------------------------------------------------------------
			  
     (SELECT CODUSUR, 
             SUM(NVL(VLMETA, 0)) VLMETA,
             SUM(NVL(QTMETA, 0)) QTMETA,
             SUM(NVL(QTPESOMETA, 0)) QTPESOMETA,
             SUM(NVL(MIXPREV, 0)) MIXPREV,
             SUM(NVL(CLIPOSPREV, 0)) CLIPOSPREV
       FROM (SELECT 
PCMETA.CODIGO,
       PCUSUARI.CODUSUR,
       PCUSUARI.CODSUPERVISOR,
       PCSUPERV.NOME SUPERV,
       PCUSUARI.NOME,
       0 NUMTRANSVENDA,
       0 QTDEVOLUCAO,
       0 VLDEVOLUCAO,
       0 TOTPESO,
       0 QTCLIPOS,
       0 QTMIXCAD,
       0 QTMIX,
       0 QTVENDA,
       0 VLVENDA,
       0 LITRAGEM,
       0 VLCUSTOFIN,
       PCMETA.CODFILIAL,
       (SELECT PCFILIAL.FANTASIA FROM PCFILIAL WHERE PCMETA.CODFILIAL = PCFILIAL.CODIGO) FILIAL,
       NVL(PCMETA.VLVENDAPREV, 0) VLMETA,
       NVL(PCMETA.QTVENDAPREV, 0) QTMETA,
       NVL(PCMETA.QTPESOPREV, 0) QTPESOMETA,
       NVL(PCMETA.MIXPREV, 0) MIXPREV,
       NVL(PCMETA.CLIPOSPREV, 0) CLIPOSPREV,
       NVL(PCMETA.VOLUMEPREV,0) VOLUMEPREV,
 PCSECAO.CODSEC, PCSECAO.DESCRICAO SECAO, PCDEPTO.CODEPTO, PCDEPTO.DESCRICAO DEPARTAMENTO, PCPRODUT.CODFAB,
 PCPRODUT.DESCRICAO, PCPRODUT.EMBALAGEM, PCPRODUT.UNIDADE, PCPRODUT.CODPROD,
   PCFORNEC.CODFORNEC, PCFORNEC.FORNECEDOR
  FROM PCMETA, PCUSUARI, PCSUPERV  
   , PCPRODUT, PCDEPTO, PCSECAO, PCFORNEC 
 WHERE PCMETA.CODUSUR = PCUSUARI.CODUSUR
   AND   PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR
   AND PCUSUARI.CODSUPERVISOR NOT IN ('9999')
   AND PCMETA.TIPOMETA = 'P'
   AND PCPRODUT.CODPROD = PCMETA.CODIGO
   AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO(+)
   AND PCPRODUT.CODSEC = PCSECAO.CODSEC(+)
   AND PCPRODUT.CODFORNEC = PCFORNEC.CODFORNEC(+)
   AND PCMETA.DATA BETWEEN TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
   AND NVL(PCMETA.CODFILIAL, ' ') IN('1')
   $whereFornecedor
   $whereDepto
	$whereSecao
   )
   GROUP BY CODUSUR) META,
   
-------------------------------------------------------------------------------------------------------------------------------------   
   
      
      ( SELECT CODUSURDEVOL, NOME,
               SUM(QTDEVOLUCAO) QTDEVOLUCAO,
               SUM(VLDEVOLUCAO) VLDEVOLUCAO,
               SUM(VLDEVOLUCAO_SEMST) VLDEVOLUCAO_SEMST,
               SUM(TOTPESO) TOTPESO,
               SUM(VOLUME) VOLUME,
               COUNT(DISTINCT(DEVOLVIDO)) QTCLIPOSNEG,
               SUM(NVL(VLBONIFIC,0)) VLBONIFIC, 
   				SUM(NVL(LITRAGEM,0)) LITRAGEM 
   FROM  (SELECT PCFORNEC.CODFORNEC, 
       PCFORNEC.FORNECEDOR, 
       PCFORNEC.CODFORNECPRINC,
      (SELECT A.FORNECEDOR FROM PCFORNEC A WHERE A.CODFORNEC = PCFORNEC.CODFORNECPRINC) FORNECEDORPRINC,
       PCNFENT.CODFORNEC CODCLI,
       PCCLIENT.CODATV1,
       DECODE(NVL(PCPEDC.NUMCAIXA,0),0,0,NVL(PCPEDC.NUMCAIXA,0)) CAIXA,
       PCNFENT.NUMNOTA ,
       PCNFENT.CODDEVOL,
       NVL(PCNFENT.VLOUTRAS,0) VLOUTRAS,
       NVL(PCNFENT.VLFRETE,0) VLFRETE,
       PCNFENT.CODFILIAL ,
       PCNFENT.CODMOTORISTADEVOL,
    (SELECT X.CLIENTE 
       FROM PCCLIENT X
      WHERE X.CODCLI = NVL(PCCLIENT.CODCLIPRINC, PCCLIENT.CODCLI)) CLIENTEPRINC,
       (SELECT DISTINCT PCEMPR.NOME  
          FROM PCEMPR                
         WHERE PCEMPR.MATRICULA = PCNFENT.CODMOTORISTADEVOL) NOMEMOTORISTA,
       PCNFENT.DTENT,
       PCNFENT.NUMTRANSENT,
       PCESTCOM.NUMTRANSVENDA, 
       PCEMPR.NOME NOMEFUNC,
       PCTABDEV.MOTIVO,
       PCCLIENT.CLIENTE,
       PCCLIENT.CODCIDADE,
       PCCIDADE.NOMECIDADE,
       PCMOV.CUSTOFIN,
       PCMOV.CODDEVOL DEVOLITEM,
       PCTABDEV2.MOTIVO MOTIVO2,
       PCCLIENT.ESTENT,
       PCCLIENT.MUNICENT,
       PCCLIENT.VIP,
       PCESTCOM.VLESTORNO,
       PCNFENT.OBS,
       PCMOV.CODOPER,
       PCMOV.ST,
       (DECODE(PCNFSAID.CONDVENDA,7,NVL(PCMOV.PUNITCONT,0),NVL(PCMOV.PUNIT,0))) PUNIT,
       PCPRODUT.DESCRICAO,
       PCPRODUT.CODAUXILIAR,
       PCPRODUT.EMBALAGEM,
       PCPRODUT.UNIDADE,
       PCMOV.CODPROD,
       PCPRODUT.CODEPTO, 
       PCPRODUT.CODSEC, 
       PCPRODUT.CODFAB, 
       PCUSUARI.PERCENT, 
       PCUSUARI.PERCENT2, 
       PCDEPTO.DESCRICAO DEPARTAMENTO, 
       PCSECAO.DESCRICAO SECAO, 
       DECODE(PCNFSAID.NUMTRANSVENDA,NULL,PCUSUARI.CODSUPERVISOR,PCNFSAID.CODSUPERVISOR) CODSUPERVISOR,
       PCMARCA.MARCA,
       PCATIVI.CODATIV,
       PCATIVI.RAMO,
       PCPRACA.CODPRACA, 
       PCPRACA.NUMREGIAO, 
       PCPRACA.ROTA, 
       PCPRACA.PRACA, 
       0 QTMETA,
       0 QTPESOMETA,
       0 MIXPREV,
       0 CLIPOSPREV,
       NVL(PCNFSAID.CODPLPAG,PCCLIENT.CODPLPAG) CODPLPAG, 
       PCNFSAID.NUMPED,
       PCNFSAID.CODCOB,
       PCNFSAID.CONDVENDA,
       PCNFSAID.PRAZOMEDIO,
       PCNFSAID.CODEMITENTE,
       PCPLPAG.DESCRICAO DESCRICAOPCPLPAG,
       NVL(PCPLPAG.NUMDIAS,0) NUMDIAS,
       PCUSUARI.NOME,
       PCNFENT.VLST,
       PCSUPERV.NOME AS SUPERV,
       NVL(PCMOV.QT,0) * NVL(PCMOV.VLREPASSE,0) VLREPASSE,
       0 VLVENDA, 
       0 QTBONIFIC,
      (SELECT PCFILIAL.FANTASIA 
              FROM PCNFENT P, PCFILIAL  
             WHERE P.CODFILIAL = PCFILIAL.CODIGO 
               AND P.NUMTRANSENT = PCNFENT.NUMTRANSENT  AND ROWNUM = 1) FILIAL,
 (NVL(PCMOV.QT,0)) QT,        
     (NVL(PCMOV.QT,0)) QTDEVOLUCAO,
  (DECODE(PCNFSAID.CONDVENDA, 5, 0, DECODE(NVL(PCMOVCOMPLE.BONIFIC, 'N'), 'N', NVL(PCMOV.QT, 0), 0)) * 
  DECODE(PCNFSAID.CONDVENDA,                                                    
          5,                                                                    
          0,                                                                    
          6,                                                                    
          0,                                                                    
          11,                                                                   
          0,                                                                    
          (DECODE(PCMOV.PUNIT,                                                  
                  0,                                                            
                  PCMOV.PUNITCONT,                                              
                  NULL,                                                         
                  PCMOV.PUNITCONT,                                              
                  PCMOV.PUNIT) + NVL(PCMOV.VLFRETE, 0) +                        
          NVL(PCMOV.VLOUTRASDESP, 0) + NVL(PCMOV.VLFRETE_RATEIO, 0)             
          + NVL(PCMOV.VLOUTROS, 0)))) VLDEVOLUCAO,                              
  (NVL(PCMOV.QT, 0) *                                                           
  DECODE(PCNFSAID.CONDVENDA,                                                    
          5,                                                                    
          0,                                                                    
          6,                                                                    
          0,                                                                    
          11,                                                                   
          0,                                                                    
          (DECODE(PCMOV.PUNIT,                                                  
                  0,                                                            
                  PCMOV.PUNITCONT,                                              
                  NULL,                                                         
                  PCMOV.PUNITCONT,                                              
                  PCMOV.PUNIT) + NVL(PCMOV.VLOUTROS, 0) -                       
          NVL(PCMOV.ST, 0) + NVL(PCMOV.VLFRETE, 0)))) VLDEVOLUCAO_SEMST,        
  (NVL(PCMOV.QT, 0) *                                                           
  (DECODE(PCNFSAID.CONDVENDA,                                                   
          5,                                                                    
          NVL(PCMOV.PUNITCONT, 0),                                              
          0) + NVL(PCMOV.VLOUTROS, 0) +                                         
               NVL(PCMOV.VLFRETE, 0))) VLDEVOLUCAOBNF,                          
  (NVL(PCMOV.QT, 0) *                                                           
  (DECODE(PCNFSAID.CONDVENDA,                                                   
           5,                                                                   
           NVL(PCMOV.PUNITCONT, 0),                                             
           6,                                                                   
           NVL(PCMOV.PUNITCONT, 0),                                             
           11,                                                                  
           NVL(PCMOV.PUNITCONT, 0),                                             
           12,                                                                  
           NVL(PCMOV.PUNITCONT, 0),                                             
           0) + DECODE(PCNFSAID.CONDVENDA,                                      
                         5,                                                     
                         NVL(PCMOV.VLOUTROS, 0),                                
                         6,                                                     
                         NVL(PCMOV.VLOUTROS, 0),                                
                         11,                                                    
                         NVL(PCMOV.VLOUTROS, 0),                                
                         12,                                                    
                         NVL(PCMOV.VLOUTROS, 0)) +                              
  DECODE(PCNFSAID.CONDVENDA,                                                    
           5,                                                                   
           NVL(PCMOV.VLFRETE, 0),                                               
           6,                                                                   
           NVL(PCMOV.VLFRETE, 0),                                               
           11,                                                                  
           NVL(PCMOV.VLFRETE, 0),                                               
           12,                                                                  
           NVL(PCMOV.VLFRETE, 0)))) VLDEVOLUCAOBONI,                            
  (NVL(PCMOV.QT, 0) * NVL(PCMOV.CUSTOFIN, 0)) VLCMVDEVOL,                       
  (NVL(PCMOV.QT, 0) * NVL(PCMOV.CUSTOFIN, 0)) VLCUSTOFIN,                       
  (NVL(PCPRODUT.LITRAGEM, 0) * NVL(PCMOV.QT, 0)) LITRAGEM,                      
  (NVL(PCPRODUT.VOLUME, 0) * NVL(PCMOV.QT, 0)) VOLUME,                          
  (DECODE(PCMOV.PBASERCA,                                                       
          NULL,                                                                 
          NVL(PCMOV.PBASERCA, NVL(PCMOV.PTABELA, 0)),                           
          NVL(PCMOV.PTABELA, 0)) * NVL(PCMOV.QT, 0)) DEVOLTAB,                  
  (NVL(PCMOV.PESOBRUTO, PCPRODUT.PESOBRUTO) * NVL(PCMOV.QT, 0)) AS TOTPESO,     
  
  ROUND((NVL(PCMOV.QT, 0) *                                                     
        DECODE(PCNFSAID.CONDVENDA,                                              
                5,                                                              
                DECODE(PCMOV.PBONIFIC,                                          
                       NULL,                                                    
                       PCMOV.PTABELA,                                           
                       PCMOV.PBONIFIC) + NVL(PCMOV.VLFRETE, 0) +                
                NVL(PCMOV.VLOUTRASDESP, 0) +                                    
                NVL(PCMOV.VLFRETE_RATEIO, 0) + NVL(PCMOV.VLOUTROS, 0),          
                6,                                                              
                DECODE(PCMOV.PBONIFIC,                                          
                       NULL,                                                    
                       PCMOV.PTABELA,                                           
                       PCMOV.PBONIFIC),                                         
                11,                                                             
                DECODE(PCMOV.PBONIFIC,                                          
                       NULL,                                                    
                       PCMOV.PTABELA,                                           
                       PCMOV.PBONIFIC),                                         
                12,                                                             
                DECODE(PCMOV.PBONIFIC,                                          
                       NULL,                                                    
                       PCMOV.PTABELA,                                           
                       PCMOV.PBONIFIC),                                         
                0)),                                                            
        2) VLBONIFIC,                                                           

       NVL(PCCLIENT.CODCLIPRINC,PCCLIENT.CODCLI) CODCLIPRINC,  
       PCNFENT.CODUSURDEVOL,      
       PCNFENT.CODUSURDEVOL CODUSUR,  
       CASE WHEN  (  SELECT SUM ( NVL(PCMOV.QT, 0) * (NVL(PCMOV.PUNIT, 0) + NVL(PCMOV.VLOUTROS, 0)) ) FROM PCMOV M, PCESTCOM E, PCNFENT  F
         WHERE E.NUMTRANSENT = F.NUMTRANSENT AND M.NUMTRANSENT = F.NUMTRANSENT
         AND M.CODOPER = 'ED' AND M.DTCANCEL IS NULL
         AND PCNFSAID.NUMTRANSVENDA = E.NUMTRANSVENDA )  >= NVL(PCNFSAID.VLTOTAL,0) THEN
            PCFORNEC.CODFORNEC 
            ELSE
            0 END DEVOLVIDO, 
      (SELECT PCCLIENT.CODPLPAG || ' - ' || PCPLPAG.DESCRICAO  FROM PCPLPAG WHERE PCCLIENT.CODPLPAG = PCPLPAG.CODPLPAG) DESCRICAOPLANOCLI,
      PCGERENTE.NOMEGERENTE,
      DECODE(PCNFSAID.NUMTRANSVENDA,NULL,PCSUPERV.CODGERENTE,PCNFSAID.CODGERENTE) CODGERENTE 
  FROM PCNFENT, PCESTCOM, PCEMPR, PCNFSAID, PCMOV, PCPRODUT, PCCLIENT, PCFORNEC, PCPRACA, PCTABDEV, PCTABDEV PCTABDEV2, 
       PCDEPTO, PCSECAO, PCUSUARI, PCPLPAG, PCSUPERV, PCATIVI, PCPEDC, PCCIDADE, PCMARCA, PCGERENTE, PCMOVCOMPLE 
 ,(SELECT DISTINCT CASE                                          
            WHEN PED.CONDVENDA = 7 THEN                          
             (SELECT DISTINCT P1.NUMPED                          
                FROM PCPEDC P1, PCESTCOM E1                      
               WHERE E1.NUMTRANSENT = ESTC.NUMTRANSENT           
                 AND P1.NUMTRANSVENDA = E1.NUMTRANSVENDA         
                 AND P1.NUMPEDENTFUT = PED.NUMPED                
                 AND P1.CONDVENDA = 8)                           
            WHEN PED.CONDVENDA = 8 THEN                          
             (SELECT DISTINCT P2.NUMPED                          
                FROM PCPEDC P2, PCESTCOM E2                      
               WHERE E2.NUMTRANSENT = ESTC.NUMTRANSENT           
                 AND P2.NUMTRANSVENDA = E2.NUMTRANSVENDA         
                 AND P2.NUMPED = PED.NUMPEDENTFUT                
                 AND P2.CONDVENDA = 7)                           
          END TEMVENDATV8,                                       
          PED.NUMTRANSVENDA,                                     
          ESTC.NUMTRANSENT                                       
     FROM PCPEDC PED, PCESTCOM ESTC                              
    WHERE PED.NUMTRANSVENDA(+) = ESTC.NUMTRANSVENDA) TEMVENDATV8 
 WHERE PCNFENT.NUMTRANSENT = PCESTCOM.NUMTRANSENT
   AND PCCLIENT.CODPRACA = PCPRACA.CODPRACA
   AND PCESTCOM.NUMTRANSENT = PCMOV.NUMTRANSENT
   AND PCFORNEC.CODFORNEC = PCPRODUT.CODFORNEC
   AND PCNFSAID.NUMPED  = PCPEDC.NUMPED(+)
   AND PCNFENT.CODDEVOL = PCTABDEV.CODDEVOL(+)
   AND PCMOV.CODDEVOL = PCTABDEV2.CODDEVOL(+)
   AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO(+)
   AND PCNFENT.CODUSURDEVOL = PCUSUARI.CODUSUR(+) 
   and nvl(PCNFSAID.CODSUPERVISOR,PCUSUARI.CODSUPERVISOR) = PCSUPERV.CODSUPERVISOR
   AND PCPRODUT.CODSEC = PCSECAO.CODSEC(+)
   AND PCCLIENT.CODATV1 = PCATIVI.CODATIV(+)
   AND PCNFENT.CODFUNCLANC  = PCEMPR.MATRICULA(+)
   AND PCESTCOM.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA(+)
   AND PCCLIENT.CODCIDADE = PCCIDADE.CODCIDADE(+)
   AND NVL(PCNFSAID.CODPLPAG,PCCLIENT.CODPLPAG) = PCPLPAG.CODPLPAG
   AND PCPRODUT.CODMARCA = PCMARCA.CODMARCA(+)
   AND PCMOV.NUMTRANSITEM = PCMOVCOMPLE.NUMTRANSITEM(+)
   AND PCNFSAID.CODGERENTE = PCGERENTE.CODGERENTE(+)
      -- numtransvenda = 0 refere-se a devolucoes avulsas que nao
      -- devem ser incluidas no resumo de faturamento
   AND PCMOV.CODPROD = PCPRODUT.CODPROD
   AND PCNFENT.CODFORNEC = PCCLIENT.CODCLI 
   AND PCNFENT.TIPODESCARGA IN ('6', '7', 'T')
   AND NVL(PCNFENT.CODFISCAL,0) IN (131, 132, 231, 232, 199, 299)
   AND PCMOV.DTCANCEL IS NULL
   AND PCMOV.CODOPER = 'ED' 
   AND NVL(PCNFENT.OBS, 'X') <> 'NF CANCELADA'
    AND TEMVENDATV8.NUMTRANSENT = PCNFENT.NUMTRANSENT       
          AND NVL(PCNFSAID.CONDVENDA, 0) NOT IN (4, 8, 10, 13, 20, 98, 99)
   	$whereFornecedor
   	$whereDepto
	$whereOrigem
	$whereSecao
	$whereSuper
	$whereERC
--	$whereCli
	$whereCliDev
	$whereProduto
	$whereMarca
	$wherePedidoForaDev
          AND PCMOV.CODFILIAL IN('1')
           AND PCNFENT.CODFILIAL IN('1')
   AND PCNFENT.DTENT BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
)

        GROUP BY CODUSURDEVOL, NOME) DEVOLUCAO,
		
		
     PCUSUARI
WHERE PCUSUARI.CODUSUR = VENDAS.CODUSUR(+)
  AND PCUSUARI.CODUSUR = DEVOLUCAO.CODUSURDEVOL(+)
  AND PCUSUARI.CODUSUR = META.CODUSUR(+)
  AND ( ( NVL(VENDAS.QTVENDA,0) <> 0) or  ( NVL(DEVOLUCAO.QTDEVOLUCAO,0) <> 0) or  ( NVL(DEVOLUCAO.VLDEVOLUCAO,0) <> 0) OR (NVL(VENDAS.TOTPESO,0) <> 0) OR (NVL(VENDAS.VLVENDA,0) <> 0) OR  (NVL(VENDAS.CODUSUR,0) <> 0) OR (NVL(VENDAS.VLBONIFIC,0) <> 0) ) 
GROUP BY VENDAS.CODUSUR,
       VENDAS.NOME,
       DEVOLUCAO.CODUSURDEVOL,
       DEVOLUCAO.NOME,
       (NVL(VLMETA, 0)),
       (NVL(QTMETA, 0)),
       (NVL(QTPESOMETA, 0)),
       (NVL(MIXPREV, 0)),
       (NVL(CLIPOSPREV, 0))
ORDER BY VLVENDA DESC
				
				";
		if($trace){
			echo "============================================================================================================================================\n";
			echo "\n\n $sql \n\n";
			echo "============================================================================================================================================\n";
		}
		$rows = query4($sql);
		
		return $rows;
	}
	
	/*
	 * Função utilizada para pegar só a última parte da string, depois do espaço
	 * no caso de o campo ser: to_char(VENDAS.DTSAIDA,'YYYYMM') MESANO
	 * retorna: MESANO
	 */
	function preparaCampoGroupBy($campo, $nomeCampo = false){
		$pos = strpos($campo, ' ');
		if($pos !== false){
			if($nomeCampo){
				$campo = substr($campo, $pos + 1);
			}else{
				$campo = substr($campo, 0,$pos);
			}
		}
		
		return $campo;
	}
	
	function vendas1464Campo($campos,$dataIni, $dataFim, $param, $trace = false){
		$ret = array();
		$arrCampos = array();
		$whereFornecedor = '';
		$whereFornecedorFora = '';
		$whereDepto = '';
		$whereOrigem = '';
		$whereSecao = '';
		$whereSuper = '';
		$whereERC = '';
		$whereERCdev = '';
		$whereCli = '';
		$whereCliFora = '';
		$whereCliDev = '';
		$whereCliDevFora = '';
		$whereProduto = '';
		$whereProdutoFora = '';
		$whereMarca = '';
		$whereMarcaFora = '';
		$whereERCcli = '';
		$whereERCcliDev = '';
		$whereBonificacao = '';
		$whereBonificacaoDev = '';
		$whereBonificacaoFiltro = '';
		$whereBonificacaoDevFiltro = '';
		$whereIntegradora = '';
		$wherePedido = '';
		$wherePedidoDev = '';
		$whereForaCampanha = '';
		$whereCampanha = '';
		$whereAtividadeFora = '';
		$whereAtividadeForaDev = '';
		$whereOperacao = '';
		$whereOperador = '';
		$wherePedidoAte = '';

		$wherePedidoFora = '';
		$wherePedidoForaDev = '';
		
		//Filtros Diversos
		$whereVenda = '';
		$whereDevolucao = '';
		
		$tabelaAdicional = ''; 
		
		if(is_array($campos)){
			$arrGroup = array();
			foreach ($campos as $campo){
				$arrGroup[] = preparaCampoGroupBy($campo);
				$arrCampos[] = preparaCampoGroupBy($campo,true);
			}
			$campo = implode(',', $campos);
			$campoGroup= implode(',', $arrGroup);
		}else{
			$campo = $campos;
			$campoGroup = preparaCampoGroupBy($campo);
		}
	
		if(isset($param['campanha']) && !empty($param['campanha'])){
			$whereCampanha = " AND PCNFSAID.CODPROMOCAOMED IN (".$param['campanha'].")";
		}
		if(isset($param['foraCampanha']) && !empty($param['foraCampanha'])){
			$whereForaCampanha = " AND PCNFSAID.CODPROMOCAOMED NOT IN (".$param['foraCampanha'].")";
		}
		
		if(isset($param['operacao']) && !empty($param['operacao'])){
			$whereOperacao = " AND PCNFSAID.CONDVENDA IN  ( ".$param['operacao']." ) ";
		}
		
		if(isset($param['marca']) && !empty($param['marca'])){
			$whereMarca= " AND (PCPRODUT.CODMARCA IN ( ".$param['marca']." ))";
		}
		if(isset($param['marcaFora']) && !empty($param['marcaFora'])){
			$whereMarcaFora= " AND (PCPRODUT.CODMARCA NOT IN ( ".$param['marcaFora']." ))";
		}
		if(isset($param['produto']) && !empty($param['produto'])){
			$whereProduto = " AND (PCPRODUT.CODPROD IN ( ".$param['produto']." ))";
		}
		if(isset($param['produtoFora']) && !empty($param['produtoFora'])){
			$whereProdutoFora = " AND (PCPRODUT.CODPROD NOT IN ( ".$param['produtoFora']." ))";
		}
		
		if(isset($param['secao']) && !empty($param['secao'])){
			$whereSecao = " AND (PCPRODUT.CODSEC IN ( ".$param['secao']." ))";
		}
		if(isset($param['super']) && !empty($param['super'])){
			$whereSuper = " AND (PCNFSAID.CODSUPERVISOR IN ( ".$param['super']." ))";
		}
		if(isset($param['ERC']) && !empty($param['ERC'])){
			$whereERC = " AND (PCNFSAID.CODUSUR IN ( ".$param['ERC']." ))";
			$whereERCdev = " AND (PCNFENT.CODUSURDEVOL IN ( ".$param['ERC']." ))";
		}
		if(isset($param['erc']) && !empty($param['erc'])){
			$whereERC = " AND (PCNFSAID.CODUSUR IN ( ".$param['erc']." ))";
			$whereERCdev = " AND (PCNFENT.CODUSURDEVOL IN ( ".$param['erc']." ))";
		}
		
		if(isset($param['integradora']) && !empty($param['integradora'])){
			$whereIntegradora = " AND (PCPEDRETORNO.INTEGRADORA IN ( ".$param['integradora']." ))";
		}
		if(isset($param['pedido']) && !empty($param['pedido'])){
			$wherePedido = " AND PCNFSAID.NUMPED IN ( ".$param['pedido']." )";
			$wherePedidoDev = " AND PCNFSAID.NUMPED IN ( ".$param['pedido']." )";
		}
		if(isset($param['pedidoFora']) && !empty($param['pedidoFora'])){
			$wherePedidoFora = " AND PCNFSAID.NUMPED NOT IN ( ".$param['pedidoFora']." )";
			$wherePedidoForaDev = " AND PCNFSAID.NUMPED NOT IN ( ".$param['pedidoFora']." )";
		}
		
		
		if(isset($param['erccli']) && !empty($param['erccli'])){
			$whereERCcli = " AND (PCNFSAID.CODCLI IN ( select codcli from pcclient where codusur1 in (".$param['erccli'].")))";
			$whereERCcliDev =" AND (PCNFENT.CODFORNEC IN (  select codcli from pcclient where codusur1 in (".$param['erccli'].")))";
		}
		if(isset($param['cliente']) && !empty($param['cliente'])){
			$whereCli = " AND (PCNFSAID.CODCLI IN ( ".$param['cliente']." ))";
			$whereCliDev =" AND (PCNFENT.CODFORNEC IN ( ".$param['cliente']." ))";
		}
		if(isset($param['clienteFora']) && !empty($param['clienteFora'])){
			$whereCliFora = " AND (PCNFSAID.CODCLI NOT IN ( ".$param['clienteFora']." ))";
			$whereCliDevFora =" AND (PCNFENT.CODFORNEC NOT IN ( ".$param['clienteFora']." ))";
		}
		//Menos os ramos de atividades
		if(isset($param['atividadeFora']) && !empty($param['atividadeFora'])){
			$whereAtividadeFora = " AND (PCNFSAID.CODCLI IN ( SELECT CODCLI FROM PCCLIENT WHERE CODATV1 NOT IN (".$param['atividadeFora']." )))";
			$whereAtividadeForaDev =" AND (PCNFENT.CODFORNEC IN (  SELECT CODCLI FROM PCCLIENT WHERE CODATV1 NOT IN (".$param['atividadeFora']." ) ))";
		}
		if(isset($param['clientePrincipal']) && !empty($param['clientePrincipal'])){
			$whereCli = " AND (PCNFSAID.CODCLI IN ( SELECT CODCLI FROM PCCLIENT WHERE CODCLIPRINC IN (".$param['clientePrincipal']." )))";
			$whereCliDev =" AND (PCNFENT.CODFORNEC IN ( SELECT CODCLI FROM PCCLIENT WHERE CODCLIPRINC IN (".$param['clientePrincipal']." )))";
		}
		if(isset($param['fornecedor']) && !empty($param['fornecedor'])){
			$whereFornecedor = " AND (PCPRODUT.CODFORNEC IN ( ".$param['fornecedor']." ))";
		}
		if(isset($param['fornecedorFora']) && !empty($param['fornecedorFora'])){
			$whereFornecedorFora = " AND (PCPRODUT.CODFORNEC NOT IN ( ".$param['fornecedorFora']." ))";
		}
		if(isset($param['depto']) && !empty($param['depto'])){
			$whereDepto = " AND PCPRODUT.CODEPTO IN (".$param['depto'].")";
		}else{
			$whereDepto = " AND PCPRODUT.CODEPTO IN (1,12)";
		}
		if(isset($param['origem']) && !empty($param['origem'])){
			switch ($param['origem']) {
				case 'OL':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					//Alterado pois Sesi e Angeloni estavam entrando como PE também
					//$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC, PCPEDRETORNO WHERE PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+) AND PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV IN ('OL','PE') AND PCPEDRETORNO.INTEGRADORA <> 20 )";
					break;
				case 'OL2':
					//$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					//Alterado pois Sesi e Angeloni estavam entrando como PE também
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC, PCPEDRETORNO WHERE PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+) AND PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV IN ('OL','PE') AND PCPEDRETORNO.INTEGRADORA <> 20 )";
					break;
				case 'NOL':
					$whereOrigem = " AND NVL(PCNFSAID.NUMPED,0) NOT IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					//Alterado pois Sesi e Angeloni estavam entrando como PE também
					//$whereOrigem = " AND NVL(PCNFSAID.NUMPED,0) NOT IN ( SELECT PCPEDC.NUMPED FROM PCPEDC, PCPEDRETORNO WHERE PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+) AND PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV IN ('OL','PE') AND PCPEDRETORNO.INTEGRADORA <> 20 )";
					break;
				case 'NOL2':
					//$whereOrigem = " AND NVL(PCNFSAID.NUMPED,0) NOT IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					//Alterado pois Sesi e Angeloni estavam entrando como PE também
					$whereOrigem = " AND NVL(PCNFSAID.NUMPED,0) NOT IN ( SELECT PCPEDC.NUMPED FROM PCPEDC, PCPEDRETORNO WHERE PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+) AND PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV IN ('OL','PE') AND PCPEDRETORNO.INTEGRADORA <> 20 )";
					break;
				case 'PE':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'PE') ";
					//Alterado pois Sesi e Angeloni estavam entrando como PE também
					//$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC, PCPEDRETORNO WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'PE' AND PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+) AND PCPEDRETORNO.INTEGRADORA = 20) ";
					break;
				case 'PE2':
					//$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'PE') ";
					//Alterado pois Sesi e Angeloni estavam entrando como PE também
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC, PCPEDRETORNO WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'PE' AND PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+) AND PCPEDRETORNO.INTEGRADORA = 20) ";
					break;
				case 'T':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'T') ";
					break;
				case 'W':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'W') ";
					break;
				case 'NT':
					$whereOrigem = " AND PCNFSAID.NUMPED NOT IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'T') ";
					break;
				case 'PDA':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV IS NULL) ";
					break;
				default:
					$whereOrigem = "";
					break;
			}
	
		}
		
		if(isset($param['bonificacao']) && $param['bonificacao'] === true){
			$whereBonificacao = "
                           'SB', 
                           (NVL(DECODE(PCNFSAID.CONDVENDA, 
                                       7, 
                                       PCMOV.QTCONT, 
                                       PCMOV.QT), 
                                0)), 
							";
			$whereBonificacaoDev = "
									DECODE(PCNFSAID.CONDVENDA, 5, 0, DECODE(NVL(PCMOVCOMPLE.BONIFIC, 'N'), 'N', NVL(PCMOV.QT, 0), 0)) QT,
									DECODE(PCNFSAID.CONDVENDA, 5, 0, DECODE(NVL(PCMOVCOMPLE.BONIFIC, 'N'), 'N', NVL(PCMOV.QT, 0), 0)) QTDEVOLUCAO, 
									";
		}elseif(isset($param['bonificacao']) && $param['bonificacao'] === false){
			$whereBonificacaoFiltro = " AND PCMOV.CODOPER <> 'SB'";
			//$whereBonificacaoDevFiltro = " AND PCMOV.CODOPER <> 'EB'";
			$whereBonificacaoDev = "
									(NVL(PCMOV.QT,0)) QT,
     								(NVL(PCMOV.QT,0)) QTDEVOLUCAO,
 									";
		}else{
			$whereBonificacaoDev = "
									(NVL(PCMOV.QT,0)) QT,        
     								(NVL(PCMOV.QT,0)) QTDEVOLUCAO,
 									";
		}
		
		if(isset($param['tabelaAdicional']) && !empty($param['tabelaAdicional'])){
			$tabelaAdicional = ','.$param['tabelaAdicional'];
		}
		
		//Filtro diverso
		if(isset($param['whereVenda']) && !empty($param['whereVenda'])){
			$whereVenda = $param['whereVenda'];
		}
		if(isset($param['whereDevolucao']) && !empty($param['whereDevolucao'])){
			$whereDevolucao = $param['whereDevolucao'];
		}
		if(isset($param['operador']) && !empty($param['operador'])){
			$whereOperador = " AND PCNFSAID.CODEMITENTEPEDIDO IN ( ".$param['operador']." ) ";
		}
		//Data limite dos pedidos
		if(isset($param['pedidoAte']) && !empty($param['pedidoAte'])){
			$wherePedidoAte = " AND PCPEDC.DATA < TO_DATE('".$param['pedidoAte']."', 'YYYYMMDD') ";
		}
		//Vendas
		$sql = "
	SELECT
		$campo,
		SUM(NVL(VLVENDA_SEMST,0)) VLVENDA_SEMST,
		COUNT(DISTINCT(QTMIX)) QTMIX,
		SUM(NVL(VLCUSTOFIN,0)) VLCUSTOFIN,
		count(distinct NUMPED) PEDIDOS,
		SUM(QTCONT) QUANTIDADE,
		SUM(QTVENDA) QUANTIDADE_VENDIDA,
		SUM(NVL(VLBONIFIC,0)) VLBONIFIC,
		COUNT(DISTINCT(CODCLI)) POSITIVACAO
	FROM
		(SELECT
			PCNFSAID.NUMTRANSVENDA,
			PCCLIENT.codusur1 ERCCLI,
			PCMOV.CODCLI,
			pcclient.CODCLIPRINC,
			PCNFSAID.CODUSUR,
			PCNFSAID.CODSUPERVISOR,
			PCNFSAID.CODEMITENTEPEDIDO,
			PCMOV.CODPROD,
			PCNFSAID.NUMPED,
			PCNFSAID.NUMNOTA,
			PCPRODUT.CODFORNEC,
			NVL(PCPLPAG.NUMDIAS,0) NUMDIAS,
			PCPRODUT.CODSEC,
			PCPRODUT.CODEPTO,
			PCPRODUT.CODLINHAPROD,
			PCPRODUT.CODMARCA,
			PCNFSAID.DTSAIDA DATA,
			PCMOV.QTCONT,
		    CASE 
		        WHEN PCPEDC.origemped = 'B' THEN 'BALCAO'
		        WHEN PCPEDC.origemped = 'C' THEN 'CALL CENTER'
		        WHEN PCPEDC.origemped = 'T' THEN 'TMKT'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv IS NULL THEN 'PDA'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'OL' THEN 'OL'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'PE' THEN 'PE'
				WHEN PCPEDC.origemped = 'W' THEN 'WEB'
		    END ORIGEM,
			
			ROUND((NVL(PCMOV.QT, 0) * nvl(pcmov.st,0)),2) VALORST,
			((DECODE(PCMOV.CODOPER,
				'S',
				(NVL(DECODE(PCNFSAID.CONDVENDA,
				7,
				PCMOV.QTCONT,
				PCMOV.QT),
				0)),
				'SM',
				(NVL(DECODE(PCNFSAID.CONDVENDA,
				7,
				PCMOV.QTCONT,
				PCMOV.QT),
				0)),
				'ST',
				(NVL(DECODE(PCNFSAID.CONDVENDA,
				7,
				PCMOV.QTCONT,
				PCMOV.QT),
				0)),
				$whereBonificacao
				0))) QTVENDA,
			((DECODE(PCMOV.CODOPER
				,'S'
				,(NVL(DECODE(PCNFSAID.CONDVENDA,
				7,
				PCMOV.QTCONT,
				PCMOV.QT),
				0))
				,'ST'
				,(NVL(DECODE(PCNFSAID.CONDVENDA,
				7,
				PCMOV.QTCONT,
				PCMOV.QT),
				0))
				,'SM'
				,(NVL(DECODE(PCNFSAID.CONDVENDA,
				7,
				PCMOV.QTCONT,
				PCMOV.QT),
				0))
				,'SB'
				,(NVL(DECODE(PCNFSAID.CONDVENDA,
				7,
				PCMOV.QTCONT,
				PCMOV.QT),
				0))
				,0)) * (NVL(PCMOV.CUSTOFIN, 0))) VLCUSTOFIN,
			ROUND((((DECODE(PCMOV.CODOPER,
			'S',
			(NVL(DECODE(PCNFSAID.CONDVENDA,
			7,
			PCMOV.QTCONT,
			PCMOV.QT),
			0)),
			'ST',
			(NVL(DECODE(PCNFSAID.CONDVENDA,
			7,
			PCMOV.QTCONT,
			PCMOV.QT),
			0)),
			'SM',
			(NVL(DECODE(PCNFSAID.CONDVENDA,
			7,
			PCMOV.QTCONT,
			PCMOV.QT),
			0)),
			0)) *
			(NVL(DECODE(PCNFSAID.CONDVENDA,
			7,
			(NVL(PUNITCONT, 0) - NVL(PCMOV.VLIPI, 0) -
			NVL(PCMOV.ST, 0)) + NVL(PCMOV.VLFRETE, 0) +
			NVL(PCMOV.VLOUTRASDESP, 0) +
			NVL(PCMOV.VLFRETE_RATEIO, 0) +
			DECODE(PCMOV.TIPOITEM,
			'C',
			(SELECT NVL((SUM(M.QTCONT * NVL(M.VLOUTROS, 0)) / PCMOV.QT), 0) VLOUTROS FROM PCMOV M WHERE M.NUMTRANSVENDA = PCMOV.NUMTRANSVENDA
			AND M.TIPOITEM = 'I'
			AND CODPRODPRINC = PCMOV.CODPROD),
			NVL(PCMOV.VLOUTROS, 0)) -
			NVL(PCMOV.VLREPASSE, 0),
			(NVL(PCMOV.PUNIT, 0) - NVL(PCMOV.VLIPI, 0) -
			NVL(PCMOV.ST, 0)) + NVL(PCMOV.VLFRETE, 0) +
			NVL(PCMOV.VLOUTRASDESP, 0) +
			NVL(PCMOV.VLFRETE_RATEIO, 0) +
			DECODE(PCMOV.TIPOITEM,
			'C',
			(SELECT NVL((SUM(M.QTCONT *
			NVL(M.VLOUTROS, 0)) /
			PCMOV.QT), 0) VLOUTROS
			FROM PCMOV M
			WHERE M.NUMTRANSVENDA =
			PCMOV.NUMTRANSVENDA
			AND M.TIPOITEM = 'I'
			AND CODPRODPRINC = PCMOV.CODPROD),
			NVL(PCMOV.VLOUTROS, 0)) -
			NVL(PCMOV.VLREPASSE, 0)),
			0)))),
			2) VLVENDA,
			


       (((DECODE(PCMOV.CODOPER,                                                 
                 'S',                                                         
                 (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),    
                      0)),                                                      
                 'ST',                                                        
                 (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),    
                      0)),                                                      
                 'SM',                                                        
                 (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),    
                      0)),                                                      
                 0)) *                                                          
       (NVL(DECODE(PCNFSAID.CONDVENDA,                                          
                     7,                                                         
                     PCMOV.PUNITCONT,                                           
                     NVL(PCMOV.PUNIT, 0) + NVL(PCMOV.VLFRETE, 0) +              
                     NVL(PCMOV.VLOUTRASDESP, 0) +                               
                     NVL(PCMOV.VLFRETE_RATEIO, 0) +                             
                     DECODE(PCMOV.TIPOITEM,                                     
                            'C',                                              
                            (SELECT (SUM(M.QTCONT * NVL(M.VLOUTROS, 0)) /       
                                    PCMOV.QT) VLOUTROS                          
                               FROM PCMOV M                                     
                              WHERE M.NUMTRANSVENDA = PCMOV.NUMTRANSVENDA       
                                AND M.TIPOITEM = 'I'                          
                                AND CODPRODPRINC = PCMOV.CODPROD),              
 'I', NVL(PCMOV.VLOUTROS, 0), DECODE(NVL(PCNFSAID.SOMAREPASSEOUTRASDESPNF,'N'),'N',NVL((PCMOV.VLOUTROS), 0),'S',NVL((NVL(PCMOV.VLOUTROS,0)-NVL(PCMOV.VLREPASSE,0)), 0)))
                      - (nvl(pcmov.ST,0)+NVL(PCMOVCOMPLE.VLSTTRANSFCD,0))),               
              0)))) VLVENDA_SEMST,


			ROUND(    
                (NVL(PCMOV.QT, 0) *
					DECODE(PCNFSAID.CONDVENDA,
    					5, (DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC) - NVL(PCMOV.ST, 0)),
    					--6, (DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC) - NVL(PCMOV.ST, 0)),
    					--11,(DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC) - NVL(PCMOV.ST, 0)),
    					--1, (NVL(PCMOV.PBONIFIC,0) - NVL(PCMOV.ST, 0)),
    					--12,(DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC) - NVL(PCMOV.ST, 0)),
    				0)
                    ),2) VLBONIFIC,
			((DECODE(PCMOV.CODOPER,
					'S',
					(NVL(DECODE(PCNFSAID.CONDVENDA,
					7,
					PCMOV.QTCONT,
					PCMOV.QT),
					0)),
					'ST',
					(NVL(DECODE(PCNFSAID.CONDVENDA,
					7,
					PCMOV.QTCONT,
					PCMOV.QT),
					0)),
					'SM',
					(NVL(DECODE(PCNFSAID.CONDVENDA,
					7,
					PCMOV.QTCONT,
					PCMOV.QT),
					0)),
					0))) QTVENDIDA,
			ROUND(PCMOV.QT * (PCMOV.PTABELA	+ NVL (pcmov.vlfrete, 0) + NVL (pcmov.vloutrasdesp, 0) + NVL (pcmov.vlfrete_rateio, 0) + NVL (pcmov.vloutros, 0) - NVL (pcmov.vlrepasse, 0)),2) VLTABELA,
			PCMOV.CODCLI QTCLIPOS,
			PCNFSAID.NUMTRANSVENDA QTNUMTRANSVENDA,
			PCNFSAID.CODFILIAL,
			PCMOV.CODPROD AS QTMIX,
			NVL(PCMOV.QT,0) * NVL(PCMOV.VLREPASSE,0) VLREPASSE,
			PCPEDRETORNO.INTEGRADORA,
			PCMOV.CODOPER
		FROM
			PCNFSAID,
			PCPRODUT,
			PCMOV,
			PCCLIENT,
			PCPLPAG,
			PCPEDC,
			PCMOVCOMPLE,
			PCPEDRETORNO
			$tabelaAdicional
		WHERE
			PCMOV.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA
			AND PCMOV.NUMTRANSITEM = PCMOVCOMPLE.NUMTRANSITEM(+)
			AND PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+)
			AND PCNFSAID.DTSAIDA BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
			AND PCMOV.DTMOV BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
			AND PCMOV.CODCLI = PCCLIENT.CODCLI (+)
			AND PCMOV.codprod = pcprodut.codprod (+)
			AND PCMOV.CODOPER <> 'SR'
			AND PCMOV.CODOPER <> 'SO'
			AND PCNFSAID.NUMPED = PCPEDC.NUMPED(+)
			AND PCMOV.CODOPER NOT IN ('SI')
			AND PCNFSAID.CODFISCAL NOT IN (522, 622, 722, 532, 632, 732)
			AND PCNFSAID.CONDVENDA NOT IN (4, 8, 10, 13, 20, 98, 99)
			AND (PCNFSAID.DTCANCEL IS NULL)
			AND PCMOV.CODFILIAL IN('1')
			AND PCNFSAID.CODFILIAL IN('1')
			AND PCNFSAID.CODPLPAG = PCPLPAG.CODPLPAG
			--and PCNFSAID.codcli = 16982
			$whereFornecedor
			$whereFornecedorFora
			$whereDepto
			$whereOrigem
			$whereSecao
			$whereSuper
			$whereERC
			$whereCli
			$whereCliFora
			$whereProduto
			$whereProdutoFora
			$whereMarca
			$whereMarcaFora
			$whereERCcli
			$wherePedido
			$wherePedidoFora
			$whereCampanha
			$whereForaCampanha
			$whereAtividadeFora
			$whereVenda
			$whereIntegradora
			$whereOperacao
			$whereBonificacaoFiltro
			$whereOperador
			$wherePedidoAte
		)
		group by
			$campoGroup
		order by
			$campoGroup
		";
		$rows = query4($sql);
		if($trace){
			echo "\n\n".$sql."\n\n";
//			print_r($rows);	
		}
	
		if(count($rows) > 0){
			if(is_array($campos)){
				foreach ($rows as $row){
					$comando = array();
					$campoC = '$ret[$row[\''. implode('\']][$row[\'', $arrCampos) .'\']]';//['venda'] = $row['VLVENDA_SEMST'];";
					$comando[] = $campoC."['venda'] 	= ".$row['VLVENDA_SEMST'].";";
					$comando[] = $campoC."['mix'] 		= ".$row['QTMIX'].";";
					$comando[] = $campoC."['devol'] 	= 0;";
					$comando[] = $campoC."['quantDevol']= 0;";
					$comando[] = $campoC."['cmv'] 		= ".$row['VLCUSTOFIN'].";";
					$comando[] = $campoC."['pedidos']	= ".$row['PEDIDOS'].";";
					$comando[] = $campoC."['quant'] 	= ".$row['QUANTIDADE'].";";
					$comando[] = $campoC."['quantVend']	= ".$row['QUANTIDADE_VENDIDA'].";";
					$comando[] = $campoC."['bonific']	= ".$row['VLBONIFIC'].";";
					$comando[] = $campoC."['positivacao']= ".$row['POSITIVACAO'].";";
					foreach ($comando as $c){
//echo "$c <br>\n";
						eval($c);
					}
//print_r($ret);
				}
			}else{
				foreach ($rows as $row){
					$cod = $row[0];
					$ret[$cod]['cod'] 		= $row[0];
					$ret[$cod]['venda'] 	= $row[1];
					$ret[$cod]['mix'] 		= $row[2];
					$ret[$cod]['devol'] 	= 0;
					$ret[$cod]['quantDevol']= 0;
					$ret[$cod]['cmv'] 		= $row[3];
					$ret[$cod]['pedidos'] 	= $row['PEDIDOS'];
					$ret[$cod]['quant'] 	= $row['QUANTIDADE'];
					$ret[$cod]['quantVend']	= $row['QUANTIDADE_VENDIDA'];
					$ret[$cod]['bonific'] 	= $row['VLBONIFIC'];
					$ret[$cod]['positivacao']= $row['POSITIVACAO'];
				}
			}
		}
		if($trace){
//			print_r($ret);
		}
		
		if(!isset($param['devolucoes']) || $param['devolucoes'] != false){
			// Devoluções
			$sql = "
	SELECT 
	    $campo,
	    SUM(QTDEVOLUCAO) QTDEVOLUCAO,
	    --SUM(VLDEVOLUCAO) VLDEVOLUCAO,
	    SUM(VLDEVOLUCAO_SEMST) VLDEVOLUCAO_SEMST,
	    --COUNT(DISTINCT(DEVOLVIDO)) QTCLIPOSNEG,
	    SUM(NVL(VLBONIFIC,0)) VLBONIFIC,
	    SUM(NVL(VLCUSTOFIN,0)) VLCUSTOFIN,
	    SUM(NVL(VLDEVOLUCAOBNF,0)) VLDEVOLUCAOBNF
	   FROM  (
	
	SELECT 
	       PCNFENT.CODFORNEC CODCLI,
			PCPRODUT.CODSEC,
			PCPRODUT.CODEPTO,
			PCPRODUT.CODLINHAPROD,
			PCPRODUT.CODMARCA,
			PCNFENT.DTENT DATA,
	       PCNFENT.NUMNOTA ,
	       PCNFENT.CODDEVOL,
	       NVL(PCNFENT.VLOUTRAS,0) VLOUTRAS,
	       NVL(PCNFENT.VLFRETE,0) VLFRETE,
	--       PCNFENT.CODFILIAL ,
			PCCLIENT.codusur1 ERCCLI,
	       PCNFENT.DTENT,
	       PCNFENT.NUMTRANSENT,
	       PCESTCOM.NUMTRANSVENDA, 
	       PCMOV.CUSTOFIN,
	       PCMOV.CODDEVOL DEVOLITEM,
	       PCESTCOM.VLESTORNO,
	      -- PCNFENT.OBS,
	       --PCMOV.CODOPER,
		    CASE 
		        WHEN PCPEDC.origemped = 'B' THEN 'BALCAO'
		        WHEN PCPEDC.origemped = 'C' THEN 'CALL CENTER'
		        WHEN PCPEDC.origemped = 'T' THEN 'TMKT'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv IS NULL THEN 'PDA'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'OL' THEN 'OL'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'PE' THEN 'PE'
		    END ORIGEM,
	       
	       PCMOV.ST,
	       (DECODE(PCNFSAID.CONDVENDA,7,NVL(PCMOV.PUNITCONT,0),NVL(PCMOV.PUNIT,0))) PUNIT,
	       PCMOV.CODPROD,
	       DECODE(PCNFSAID.NUMTRANSVENDA,NULL,PCUSUARI.CODSUPERVISOR,PCNFSAID.CODSUPERVISOR) CODSUPERVISOR,
	       PCNFSAID.NUMPED,
	       PCNFSAID.CODEMITENTEPEDIDO,
	       PCNFENT.VLST,
	       NVL(PCMOV.QT,0) * NVL(PCMOV.VLREPASSE,0) VLREPASSE,
	 		$whereBonificacaoDev
	  (DECODE(PCNFSAID.CONDVENDA, 5, 0, DECODE(NVL(PCMOVCOMPLE.BONIFIC, 'N'), 'N', NVL(PCMOV.QT, 0), 0)) * 
	  DECODE(PCNFSAID.CONDVENDA,                                                    
	          5,                                                                    
	          0,                                                                    
	          6,                                                                    
	          0,                                                                    
	          11,                                                                   
	          0,                                                                    
	          (DECODE(PCMOV.PUNIT,                                                  
	                  0,                                                            
	                  PCMOV.PUNITCONT,                                              
	                  NULL,                                                         
	                  PCMOV.PUNITCONT,                                              
	                  PCMOV.PUNIT) + NVL(PCMOV.VLFRETE, 0) +                        
	          NVL(PCMOV.VLOUTRASDESP, 0) + NVL(PCMOV.VLFRETE_RATEIO, 0)             
	          + NVL(PCMOV.VLOUTROS, 0)))) VLDEVOLUCAO,                              
	  (NVL(PCMOV.QT, 0) *                                                           
	  DECODE(PCNFSAID.CONDVENDA,                                                    
	          5,                                                                    
	          0,                                                                    
	          6,                                                                    
	          0,                                                                    
	          11,                                                                   
	          0,                                                                    
	          (DECODE(PCMOV.PUNIT,                                                  
	                  0,                                                            
	                  PCMOV.PUNITCONT,                                              
	                  NULL,                                                         
	                  PCMOV.PUNITCONT,                                              
	                  PCMOV.PUNIT) + NVL(PCMOV.VLOUTROS, 0) -                       
	          NVL(PCMOV.ST, 0) + NVL(PCMOV.VLFRETE, 0)))) VLDEVOLUCAO_SEMST,        
	  (NVL(PCMOV.QT, 0) *                                                           
	  (DECODE(PCNFSAID.CONDVENDA,                                                   
	          5,                                                                    
	          NVL(PCMOV.PUNITCONT, 0),                                              
	          0) + NVL(PCMOV.VLOUTROS, 0) +                                         
	               NVL(PCMOV.VLFRETE, 0))) VLDEVOLUCAOBNF,                          
	  (NVL(PCMOV.QT, 0) *                                                           
	  (DECODE(PCNFSAID.CONDVENDA,                                                   
	           5,                                                                   
	           NVL(PCMOV.PUNITCONT, 0),                                             
	           6,                                                                   
	           NVL(PCMOV.PUNITCONT, 0),                                             
	           11,                                                                  
	           NVL(PCMOV.PUNITCONT, 0),                                             
	           12,                                                                  
	           NVL(PCMOV.PUNITCONT, 0),                                             
	           0) + DECODE(PCNFSAID.CONDVENDA,                                      
	                         5,                                                     
	                         NVL(PCMOV.VLOUTROS, 0),                                
	                         6,                                                     
	                         NVL(PCMOV.VLOUTROS, 0),                                
	                         11,                                                    
	                         NVL(PCMOV.VLOUTROS, 0),                                
	                         12,                                                    
	                         NVL(PCMOV.VLOUTROS, 0)) +                              
	  DECODE(PCNFSAID.CONDVENDA,                                                    
	           5,                                                                   
	           NVL(PCMOV.VLFRETE, 0),                                               
	           6,                                                                   
	           NVL(PCMOV.VLFRETE, 0),                                               
	           11,                                                                  
	           NVL(PCMOV.VLFRETE, 0),                                               
	           12,                                                                  
	           NVL(PCMOV.VLFRETE, 0)))) VLDEVOLUCAOBONI,                            
	  (NVL(PCMOV.QT, 0) * NVL(PCMOV.CUSTOFIN, 0)) VLCMVDEVOL,                       
	  (NVL(PCMOV.QT, 0) * NVL(PCMOV.CUSTOFIN, 0)) VLCUSTOFIN,                       
	  (DECODE(PCMOV.PBASERCA,                                                       
	          NULL,                                                                 
	          NVL(PCMOV.PBASERCA, NVL(PCMOV.PTABELA, 0)),                           
	          NVL(PCMOV.PTABELA, 0)) * NVL(PCMOV.QT, 0)) DEVOLTAB,                  
	  ROUND((NVL(PCMOV.QT, 0) *                                                     
	        DECODE(PCNFSAID.CONDVENDA,                                              
	                5, DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC) + NVL(PCMOV.VLFRETE, 0) +  NVL(PCMOV.VLOUTRASDESP, 0) +  NVL(PCMOV.VLFRETE_RATEIO, 0) + NVL(PCMOV.VLOUTROS, 0),          
	             --   6, DECODE(PCMOV.PBONIFIC,  NULL, PCMOV.PTABELA,  PCMOV.PBONIFIC),                                         
	             --  11, DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),                                         
	             --   12, DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),                                         
	                0)),                                                            
	        2) VLBONIFIC,                                                           
	
	       NVL(PCCLIENT.CODCLIPRINC,PCCLIENT.CODCLI) CODCLIPRINC,  
	       PCNFENT.CODUSURDEVOL,      
	       PCNFENT.CODUSURDEVOL CODUSUR,  
	       CASE WHEN  (  SELECT SUM ( NVL(PCMOV.QT, 0) * (NVL(PCMOV.PUNIT, 0) + NVL(PCMOV.VLOUTROS, 0)) ) FROM PCMOV M, PCESTCOM E, PCNFENT  F
	         WHERE E.NUMTRANSENT = F.NUMTRANSENT AND M.NUMTRANSENT = F.NUMTRANSENT
	         AND M.CODOPER = 'ED' AND M.DTCANCEL IS NULL
	         AND PCNFSAID.NUMTRANSVENDA = E.NUMTRANSVENDA )  >= NVL(PCNFSAID.VLTOTAL,0) 
				THEN
	            	PCFORNEC.CODFORNEC 
	            ELSE
	            	0 
			END DEVOLVIDO,
	            pcprodut.codfornec,
				PCPEDRETORNO.INTEGRADORA,
				PCMOV.CODOPER
			  FROM 
			    PCNFENT, 
			    PCESTCOM, 
			    PCNFSAID, 
			    PCMOV, 
			    PCPRODUT, 
			    PCCLIENT, 
			    PCFORNEC, 
			    PCUSUARI, 
			    PCPEDC, 
			    PCMOVCOMPLE,
				PCPEDRETORNO
				$tabelaAdicional
			 WHERE 
			   PCNFENT.NUMTRANSENT = PCESTCOM.NUMTRANSENT
			   AND PCESTCOM.NUMTRANSENT = PCMOV.NUMTRANSENT
			   AND PCFORNEC.CODFORNEC = PCPRODUT.CODFORNEC
			   AND PCNFSAID.NUMPED  = PCPEDC.NUMPED(+)
					AND PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+)
			   AND PCNFENT.CODUSURDEVOL = PCUSUARI.CODUSUR(+) 
			   AND PCESTCOM.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA(+)
			   AND PCMOV.NUMTRANSITEM = PCMOVCOMPLE.NUMTRANSITEM(+)
			   AND PCMOV.CODPROD = PCPRODUT.CODPROD
			   AND PCNFENT.CODFORNEC = PCCLIENT.CODCLI 
			   AND PCNFENT.TIPODESCARGA IN ('6', '7', 'T')
			   AND NVL(PCNFENT.CODFISCAL,0) IN (131, 132, 231, 232, 199, 299)
			   AND PCMOV.DTCANCEL IS NULL
			   AND PCMOV.CODOPER = 'ED' 
			   AND NVL(PCNFENT.OBS, 'X') <> 'NF CANCELADA'
			   AND NVL(PCNFSAID.CONDVENDA, 0) NOT IN (4, 8, 10, 13, 20, 98, 99)
			   AND PCMOV.CODFILIAL IN('1')
			   AND PCNFENT.CODFILIAL IN('1')
			   AND PCNFENT.DTENT BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
			   --and PCNFENT.codfornec = 10449
				$whereFornecedor
				$whereFornecedorFora
			   	$whereDepto
				$whereOrigem
				$whereSecao
				$whereSuper
				$whereERCdev
			--	$whereCli
				$whereCliFora
				$whereCliDevFora
				$whereCliDev
				$whereProduto
				$whereProdutoFora
				$whereMarca
				$whereMarcaFora
				$whereERCcliDev
				$wherePedidoDev
				$wherePedidoForaDev
				$whereCampanha
				$whereForaCampanha
				$whereAtividadeForaDev
				$whereDevolucao
				$whereIntegradora
				$whereOperacao
				$whereBonificacaoDevFiltro
				$whereOperador
				$wherePedidoAte
			)
			group by 
				$campoGroup
			order by
				$campoGroup
					";
				$rows = query4($sql);
				if($trace){
					echo "\n\n".$sql."\n\n";
//print_r($rows);
				}
				
				if(count($rows) > 0){
					if(is_array($campos)){
						foreach ($rows as $row){
							$comando = array();
							$campoC = '$ret[$row[\''. implode('\']][$row[\'', $arrCampos) .'\']]';
//echo "campo: $campoC \n";
							eval('if(isset('.$campoC.'))$existe=1; else $existe = 0;');
//echo 'if(isset('.$campo.'))$existe=1; else $existe = 0;'."\n";
//echo "Existe: $existe ".$row[0]." - ".$row[1]."<br> \n";
							if($existe == 0){
								$comando[] = $campoC."['venda'] 	= ".$row['VLDEVOLUCAO_SEMST']." * -1;";
								$comando[] = $campoC."['mix'] 		= 0;";
								$comando[] = $campoC."['devol'] 	= ".$row['VLDEVOLUCAO_SEMST'].";";
								$comando[] = $campoC."['cmv'] 		= ".$row['VLCUSTOFIN']." * -1;";
								$comando[] = $campoC."['pedidos']	= 0;";
								$comando[] = $campoC."['quant'] 	= ".$row['QTDEVOLUCAO']." * -1;";
								$comando[] = $campoC."['quantDevol']= ".$row['QTDEVOLUCAO'].";";
								$comando[] = $campoC."['quantVend'] = ".$row['QTDEVOLUCAO']." * -1;";
								$comando[] = $campoC."['bonific']	= ".$row['VLDEVOLUCAOBNF']." * -1;";
								$comando[] = $campoC."['positivacao']= 0;";
							}else{
								$comando[] = $campoC."['venda'] 	-= ".$row['VLDEVOLUCAO_SEMST'].";";
								$comando[] = $campoC."['mix'] 		+= 0;";
								$comando[] = $campoC."['devol'] 	+= ".$row['VLDEVOLUCAO_SEMST'].";";
								$comando[] = $campoC."['cmv'] 		-= ".$row['VLCUSTOFIN'].";";
								$comando[] = $campoC."['pedidos']	+= 0;";
								$comando[] = $campoC."['quant'] 	-= ".$row['QTDEVOLUCAO'].";";
								$comando[] = $campoC."['quantDevol']+= ".$row['QTDEVOLUCAO'].";";
								$comando[] = $campoC."['quantVend'] -= ".$row['QTDEVOLUCAO'].";";
								$comando[] = $campoC."['bonific']	-= ".$row['VLDEVOLUCAOBNF']." * -1;";
							}
							foreach ($comando as $c){
//echo "$c <br>\n";
								eval($c);
							}
//print_r($ret);die();
						}
					}else{
						foreach ($rows as $row){
							$cod = $row[0];
							if(!isset($ret[$cod])){
								$ret[$cod]['cod'] 			= $row[0];
								$ret[$cod]['venda'] 		= $row['VLDEVOLUCAO_SEMST'] * -1;
								$ret[$cod]['mix'] 			= 0;
								$ret[$cod]['devol'] 		= $row['VLDEVOLUCAO_SEMST'];
								$ret[$cod]['cmv'] 			= $row['VLCUSTOFIN'] * -1;
								$ret[$cod]['pedidos']		= 0;
								$ret[$cod]['quant'] 		= $row['QTDEVOLUCAO'] * -1;
								$ret[$cod]['quantDevol'] 	= $row['QTDEVOLUCAO'];
								$ret[$cod]['quantVend']		= $row['QTDEVOLUCAO'] * -1;
								$ret[$cod]['bonific'] 		= $row['VLDEVOLUCAOBNF'] * -1;
								$ret[$cod]['positivacao']	= 0;
							}else{
								$ret[$cod]['cod'] 		= $row[0];
								$ret[$cod]['venda'] 	-= $row['VLDEVOLUCAO_SEMST'];
								$ret[$cod]['mix'] 		+= 0;
								$ret[$cod]['devol'] 	+= $row['VLDEVOLUCAO_SEMST'];
								$ret[$cod]['cmv'] 		+= $row['VLCUSTOFIN'] * -1;
								$ret[$cod]['pedidos']	+= 0;
								$ret[$cod]['quant'] 	+= $row['QTDEVOLUCAO'] * -1;
								$ret[$cod]['quantDevol']+= $row['QTDEVOLUCAO'];
								$ret[$cod]['quantVend']	+= $row['QTDEVOLUCAO'] * -1;
								$ret[$cod]['bonific'] 	+= $row['VLDEVOLUCAOBNF'] * -1;
							}
						}
					}
				}
		}
		if($trace){
//			print_r($ret);
		}
		
//print_r($ret);
			return $ret;	
	}
	
	function prazoMedioVenda($campo,$dataIni, $dataFim, $param, $trace = false){
		$ret = array();
		$whereFornecedor = '';
		$whereDepto = '';
		$whereOrigem = '';
		$whereSecao = '';
		$whereSuper = '';
		$whereERC = '';
		$whereCli = '';
		$whereCliDev = '';
		$whereProduto = '';
		$whereRede = '';
		$where = isset($param['where']) ? $param['where'] : '';
		$quebraData = '';
		$selectData = '';

		/*
		 * Quebra saida por data de faturamento
		 */
		if(isset($param['quebraData']) && $param['quebraData']){
			$quebraData = 'DTSAIDA,';
			$selectData = "to_char(DTSAIDA,'YYYYMMDD') DTSAIDA,";
		}
		

		if(isset($param['rede']) && !empty($param['rede'])){
			$whereRede = " AND PCCLIENT.codrede IN ( ".$param['rede']." )";
		}
		if(isset($param['produto']) && !empty($param['produto'])){
			$whereProduto = " AND (PCPRODUT.CODPROD IN ( ".$param['produto']." ))";
		}
		if(isset($param['secao']) && !empty($param['secao'])){
			$whereSecao = " AND (PCPRODUT.CODSEC IN ( ".$param['secao']." ))";
		}
		if(isset($param['super']) && !empty($param['super'])){
			$whereSuper = " AND (PCNFSAID.CODSUPERVISOR IN ( ".$param['super']." ))";
		}
		if(isset($param['ERC']) && !empty($param['ERC'])){
			$whereERC = " AND (PCNFSAID.CODUSUR IN ( ".$param['ERC']." ))";
		}
		if(isset($param['erc']) && !empty($param['erc'])){
			$whereERC = " AND (PCNFSAID.CODUSUR IN ( ".$param['ERC']." ))";
		}
		if(isset($param['cliente']) && !empty($param['cliente'])){
			$whereCli = " AND (PCNFSAID.CODCLI IN ( ".$param['cliente']." ))";
			$whereCliDev =" AND (PCNFENT.CODFORNEC IN ( ".$param['cliente']." ))";
		}
		if(isset($param['fornecedor']) && !empty($param['fornecedor'])){
			$whereFornecedor = " AND (PCPRODUT.CODFORNEC IN ( ".$param['fornecedor']." ))";
		}
		if(isset($param['depto']) && !empty($param['depto'])){
			$whereDepto = " AND PCPRODUT.CODEPTO IN (".$param['depto'].")";
		}
		if(isset($param['origem']) && !empty($param['origem'])){
			switch ($param['origem']) {
				case 'OL':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					break;
				case 'NOL':
					$whereOrigem = " AND NVL(PCNFSAID.NUMPED,0) NOT IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
					break;
				case 'PE':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'PE') ";
					break;
				case 'T':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'T') ";
					break;
				case 'PDA':
					$whereOrigem = " AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV IS NULL) ";
					break;
				default:
					$whereOrigem = "";
					break;
			}
	
		}
	
		
		$sql = "
SELECT 
	$selectData
    $campo,
    (SUM(VLVENDA_SEMST * PRAZOMEDIO)/SUM(VLVENDA_SEMST)) PMEDIO
FROM
    (
    SELECT
    	$quebraData
        $campo,
        SUM(NVL(VLVENDA_SEMST,0)) VLVENDA_SEMST,
        PRAZOMEDIO
    FROM
        (
        SELECT
        	PCNFSAID.DTSAIDA,
            PCCLIENT.codusur1 ERCCLI,
            PCMOV.CODCLI,
            pcclient.codcliprinc,
            PCNFSAID.codusur,
            PCNFSAID.CODSUPERVISOR,
            PCMOV.CODPROD,
            PCNFSAID.NUMPED,
            pcprodut.codfornec,
            pcnfsaid.prazomedio,
            pcprodut.codepto,
            PCCLIENT.codrede,
		    CASE 
		        WHEN PCPEDC.origemped = 'B' THEN 'BALCAO'
		        WHEN PCPEDC.origemped = 'C' THEN 'CALL CENTER'
		        WHEN PCPEDC.origemped = 'T' THEN 'TMKT'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv IS NULL THEN 'PDA'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'OL' THEN 'OL'
		        WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'PE' THEN 'PE'
		    END ORIGEM,
            1 FILIAL,
            (((DECODE(PCMOV.CODOPER,
                    'S',
                    (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),
                    0)),
                    'ST',
                    (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),
                    0)),
                    'SM',
                    (NVL(DECODE(PCNFSAID.CONDVENDA, 7, PCMOV.QTCONT, PCMOV.QT),
                    0)),
                    0)) *
                    (NVL(DECODE(PCNFSAID.CONDVENDA,
                    7,
                    PCMOV.PUNITCONT,
                    NVL(PCMOV.PUNIT, 0) + NVL(PCMOV.VLFRETE, 0) +
                    NVL(PCMOV.VLOUTRASDESP, 0) +
                    NVL(PCMOV.VLFRETE_RATEIO, 0) +
                    DECODE(PCMOV.TIPOITEM,
                    'C',
                    (SELECT (SUM(M.QTCONT * NVL(M.VLOUTROS, 0)) /
                    PCMOV.QT) VLOUTROS
                    FROM PCMOV M
                    WHERE M.NUMTRANSVENDA = PCMOV.NUMTRANSVENDA
                    AND M.TIPOITEM = 'I'
                    AND CODPRODPRINC = PCMOV.CODPROD),
                    NVL(PCMOV.VLOUTROS, 0)) -
                    NVL(PCMOV.VLREPASSE, 0) - NVL(PCMOV.ST, 0)),
                    0)))) VLVENDA_SEMST

        FROM
            PCNFSAID,
            PCPRODUT,
            PCMOV,
            PCCLIENT,
            PCPEDC
        WHERE
            PCMOV.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA
            AND PCNFSAID.DTSAIDA BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
            AND PCMOV.DTMOV BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
            AND PCMOV.CODCLI = PCCLIENT.CODCLI (+)
            AND PCMOV.codprod = pcprodut.codprod (+)
            AND PCMOV.CODOPER <> 'SR'
            AND PCMOV.CODOPER <> 'SO'
            AND PCNFSAID.NUMPED = PCPEDC.NUMPED(+)
            AND PCMOV.CODOPER NOT IN ('SI')
            AND PCNFSAID.CODFISCAL NOT IN (522, 622, 722, 532, 632, 732)
            AND PCNFSAID.CONDVENDA NOT IN (4, 8, 10, 13, 20, 98, 99)
            AND (PCNFSAID.DTCANCEL IS NULL)
            AND PCMOV.CODFILIAL IN('1')
            AND PCNFSAID.CODFILIAL IN('1')
            AND PCMOV.CODCLI > 0
			$whereFornecedor
			$whereDepto
			$whereOrigem
			$whereSecao
			$whereSuper
			$whereERC
			$whereCli
			$whereRede
			$whereProduto
			$where
        )
        WHERE 
            VLVENDA_SEMST > 0
        group by
        	$quebraData
            $campo,
            PRAZOMEDIO
    )
GROUP BY
	$quebraData
    $campo
order by
	$quebraData
    $campo
		";
		
	    $rows = query4($sql);
	    if($trace){
	    	echo "\n\n".$sql."\n\n";
	    }
	    if(count($rows) > 0){
	    	foreach ($rows as $row){
	    		$cod = $row[strtoupper($campo)];
	    		if($quebraData == ''){
	   				$ret[$cod] = $row['PMEDIO'];
	    		}else{
	    			$data = $row['DTSAIDA'];
	    			$ret[$cod][$data] = $row['PMEDIO'];
	    		}
	    	}
	    }
	    return $ret;
    
	}

	/*
	 * Retorna o período de inicio e fim dos relatórios que são enviados automaticamente
	 * Se o dia 1 é dia da semana executa mês passado
	 * Se é dia 2 e segunda, executa mês passado
	 * Se é dia 3 e segunda, executa mês passado
	 */
	function getDatasRelAut($menos = 0){
		$dia = date('d');
		$semana = date('N');
		$ano = date('Y');
		$mes = date('m');
		if($dia == 1 || ($dia == 2 && $semana == 1) || ($dia == 3 && $semana == 1)){
			$mes--;
			if($mes == 0){
				$mes = 12;
				$ano--;
			}
			$dia = date("t", mktime(0,0,0,$mes,'15',$ano));
		}else{
			$dia -= $menos;
		}
		$dia = (int)$dia;
		if($dia < 10){
			$dia = '0'.$dia;
		}
		$mes = (int)$mes;
		if($mes< 10){
			$mes= '0'.$mes;
		}
		$dataIni = $ano.$mes.'01';
		$dataFim = $ano.$mes.$dia;
		
		return array($dataIni, $dataFim);
	}
	
	function verificaIntegraEZ($tabela,$codWT,$trace=false){
		$ret = false;
		if($codWT != ''){
			$sql = "SELECT * FROM integraEZ WHERE tabela = '$tabela' AND wt_char = '$codWT'";
			if($trace){
				echo "$sql \n\n<br>";
			}
			
			$rows = query($sql);
			if(count($rows) > 0){
				$ret = $rows[0]['ez'];
			}
		}
		return $ret;
	}
	
	function verificaSeEnviaEmail($cod,$tipo = 'erc'){
		$ret = false;
		if($tipo == 'erc'){
			$erc = array(268,11);
			if(array_search($cod, $erc) === false){
				$ret = true;
			}
		}elseif($tipo == 'super'){
			$super = array(11,14,15);
			if(array_search($cod, $super) === false){
				$ret = true;
			}
			
		}
		
		return $ret;
	}
	
	function getMargemTWS($dataIni, $dataFim, $param, $campos = array()){
		$sql = '';
		$sqlSelect = '';
		$sqlGroup = '';
		
		$whereCli = '';
		$whereDepto = '';
		$whereProduto = '';
		
		if(is_array($campos) && count($campos) == 0){
			$campos= array('RCA','CLIENTE');
		}
		if(is_array($campos)){
			$campo = implode(',', $campos);
		}else{
			$campo = $campos;
		}
		
		if(isset($param['cliente']) && !empty($param['cliente'])){
			$whereCli = " AND CLIENTE IN ( ".$param['cliente']." )";
		}
		if(isset($param['depto']) && !empty($param['depto'])){
			$whereDepto = " AND PRODUTO IN (SELECT codprod FROM PCPRODUT WHERE codepto IN ( ".$param['depto']." ))";
		}
		if(isset($param['produto']) && !empty($param['produto'])){
			$whereProduto = " AND PRODUTO IN (".$param['produto']." )";
		}
		
		$ret = array();
		if($campo != ''){
			$sqlSelect = "$campo,";
			$sqlGroup = "group by $campo";
		}
		
		$sql = "
			select
				$sqlSelect
				((1-(SUM(tws_margem.VLCUSTOREAL) + SUM(BCONSUMIDO) + SUM(BGERADO) - SUM(RESSARCST) - SUM(CREDITOICMS))/SUM(VLLIQUIDO))*100) MARGEM
			from
				tws_margem
			where
				data >= '$dataIni' AND data <= '$dataFim'
				AND VLLIQUIDO > 0
				$whereCli
				$whereDepto
				$whereProduto
			$sqlGroup
		";
		

//echo "$sql \n";
		$rows = query4($sql);
//print_r($rows);
		if(count($rows) > 0){
			foreach ($rows as $row){
				if($campo != ''){
					$campoTemp = '$ret[$row[\''. implode('\']][$row[\'', $campos) .'\']]';
					$comando = $campoTemp."['margem']	= ".$row['MARGEM'].";";
	//echo "$comando \n";
					eval($comando);
				}else{
					$ret = $row['MARGEM'];
				}
			}
		}
		
		return $ret;
	}

	/** Verifica se já foi executado determinado relatório em uma data
	 * 	Retorna FALSE se ainda não foi executado e TRUE caso já tenha sido executado
	 * 
	 * @param string $programa - Nome do Programa
	 * @param string $dia - data 
	 * @return boolean
	 */
	function verificaExecucaoSchedule($programa,$dia){
		$ret = false;
		$sql = "SELECT * FROM  `schedule_execucoes`WHERE dia = '$dia' AND relatorio = '$programa'";
		$rows = query($sql);
		if(count($rows) > 0){
			$ret = true;
		}
		
		return $ret;
	}
	
	function gravaExecucaoSchedule($programa,$dia){
		if($programa != '' && $dia != ''){
			$sql = "INSERT INTO schedule_execucoes (dia,relatorio,usuario) VALUES ('$dia',  '$programa','".funcoesusuario::getUsuario()."')";
			query($sql);
		}
	}
//}

	/**
	 * Utilizado na integração com o easy - envio de documentos vencidos
	 * 
	 * @param string $prStr
	 * @return number
	 */
	function CalcDigitMod9($prStr) {
		$numArray  = str_split($prStr);
		$arraySum  = array_sum($numArray);
		$remainder = $arraySum % 9;
		$DIGIT     = 9 - $remainder;
		return $DIGIT;
	}
	
	/**
	 * Retorna o preço médio dos itens vendidos por um ERC
	 * @param array $campos
	 * @param array $dataIni
	 * @param array $dataFim
	 * @param array $param
	 * @param boolean $trace
	 * @return array
	 */
	function calculaPrecoMedio($campo, $dataIni, $dataFim, $param = [], $trace = false){
		$ret = [];
		if($campo == 'CODEMITENTEPEDIDO'){
			// Operador Tele
			$campos = array('CODEMITENTEPEDIDO', 'CODCLI');
		}elseif($campo == '' || $campo == 'CODUSUR'){
			//ERC do Pedido
			$campos = array('CODUSUR', 'CODCLI');
		}else{
			//ERC do cadastro de cliente
			$campos = array('ERCCLI', 'CODCLI');
		}$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, $trace);
		
		foreach ($vendas as $erc => $v2){
			$quant_cli = count($v2);
			$preço = 0;
			foreach ($v2 as $v3){
				$preço += $v3['quantVend'] > 0 && $v3['venda'] > 0 ? $v3['venda'] / $v3['quantVend'] : 0;
			}
			
			$ret[$erc] = $preço / $quant_cli;
		}
		
		return $ret;
	}
	
	/**
	 * Retorna o MIX (Produto) médio do ERC
	 * @param array $campos
	 * @param array $dataIni
	 * @param array $dataFim
	 * @param array $param
	 * @param boolean $trace
	 * @return array
	 */
	function calculaMIX($campo, $dataIni, $dataFim, $param = [], $tipo = 'GD', $trace = false){
		$ret = [];
		if($campo == 'CODEMITENTEPEDIDO'){
			// Operador Tele
			$campos = array('CODEMITENTEPEDIDO', 'CODCLI');
		}elseif($campo == '' || $campo == 'CODUSUR'){
			//ERC do Pedido
			$campos = array('CODUSUR', 'CODCLI');
		}else{
			//ERC do cadastro de cliente
			$campos = array('ERCCLI', 'CODCLI');
		}
		$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, $trace);
		
		if($tipo == 'GD'){
			foreach ($vendas as $erc => $v2){
				$quant_cli = count($v2);
				$mix = 0;
				foreach ($v2 as $v3){
					$mix += $v3['mix'];
				}
				
				$ret[$erc] = $mix / $quant_cli;
			}
		}elseif($tipo == 'ERC'){
			foreach ($vendas as $erc => $v2){
				foreach ($v2 as $cliente => $v3){
					$ret[$erc][$cliente] = $v3['mix'];
				}
			}
		}
		
		return $ret;
	}
	
	function getOperadores(){
		$ret = [];
		
		$sql = "SELECT MATRICULA, NOME, EMAIL FROM PCEMPR WHERE AREAATUACAO LIKE '%TELEVENDAS%' AND SITUACAO = 'A'ORDER BY NOME";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = array();
				$temp['matricula'] 	= $row['MATRICULA'];
				$temp['nome'] 		= $row['NOME'];
				$temp['email'] 		= $row['EMAIL'];
				
				$ret[$row['MATRICULA']] = $temp;
			}
		}
		
		return $ret;
	}
	
	function query_f1($sql){
	    global $db_f1, $config;

	    $ret = array();
	    $res = $db_f1->Execute($sql);
	    if (!$res ){
	        if($config["site"]["debug"]){
	            echo "<br>\nErro no SQL: $sql \n<br>";
	            print $db_f1->ErrorMsg();
	            echo "\n<br>------------------------------<br>\n";
	        }
	        return false;
	    }else{
	        $sql = trim($sql);
	        $pos = strpos(strtoupper($sql), "SELECT");
	        if($pos === false || $pos > 5){
	            //			$ret = $db->GenID;
	            return true;
	        }else{
	            $ret = $res->GetRows();
	        }
	        return $ret;
	    }
	}