<?php
/*
 * Data Criacao 04/01/2022
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 * 
 * Alterações:
 * 
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

function TWS_autoload($class){
	if(class_exists($class)){
		return;
	}
	
	global $config;
	
	$cliente = $config['app'];
	$classe = strtolower($class);
	
	//echo "Classe: $class <br>";
	$components = explode('_',$class);
	$_app = array_shift($components);
	
	$file = $config["include"]."tws.".strtolower($class).".php";
	//echo "arquivo1: ".$file."<br>";
	if (file_exists($file)){
		include_once($file);
	}else{
	    $file = $config["modulos"]."core" . DIRECTORY_SEPARATOR .$_app. DIRECTORY_SEPARATOR . "inc". DIRECTORY_SEPARATOR . "tws.".$classe.".php";
		//echo $file."<br>\n";
		if (file_exists($file)){
			include_once($file);
		}else{
		    $file = $config["modulos"]."apps" . DIRECTORY_SEPARATOR .getAplicacao(). DIRECTORY_SEPARATOR . "tws.".$classe.".php";
			//echo $file."<br>\n";
			if (file_exists($file)){
				include_once($file);
			}else{
			    $file = $config["modulos"]."cli".$cliente. DIRECTORY_SEPARATOR .$_app. DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "tws.".$classe.".php";
				//echo $file."<br>\n";
				if (file_exists($file)){
					include_once($file);
				}else{
					//Includes pespecíficos do cliente
					$file = $config["include"]."cli".$cliente.DIRECTORY_SEPARATOR."tws.".strtolower($class).".php";
					//echo $file."<br>\n";
					if (file_exists($file)){
						include_once($file);
					}
				}
			}
		}
	}
}

spl_autoload_register("TWS_autoload");

/**
 * Load a class and include the class file if not done so already.
 *
 * This function is used to create an instance of a class, and if the class file has not been included it will do so.
 * $GLOBALS['egw']->acl =& CreateObject('phpgwapi.acl');
 *
 * @author RalfBecker@outdoor-training.de
 * @param $classname string name of class
 * @param $p1,$p2,... string class parameters (all optional)
 * @return object reference to an object
 */
function &CreateObject($class){
	global $config, $nl;
	$erro = '';
	
	@list($appname,$classname) = explode('.',$class);
	$appname   = strtolower($appname);
	$classname = strtolower($classname);
	
	if (!class_exists($classname)){
	    $inc = $config["modulos"].'core' . DIRECTORY_SEPARATOR .$appname. DIRECTORY_SEPARATOR . 'tws.'.$classname.'.php';
		$erro .= $inc.'<br>'.$nl;
		if (!file_exists($inc)){
		    $inc = $config["modulos"].'apps' . DIRECTORY_SEPARATOR .getAplicacao(). DIRECTORY_SEPARATOR .$appname . DIRECTORY_SEPARATOR . 'tws.'.$classname.'.php';
			$erro .= $inc.'<br>'.$nl;
			if (!file_exists($inc)){
			    $inc = $config["modulos"]."cli".getCliente().DIRECTORY_SEPARATOR.$appname. DIRECTORY_SEPARATOR . "tws.".$classname.".php";
				$erro .= $inc.'<br>'.$nl;
				if (!file_exists($inc)){
					echo "(CreateObject) Include não existe: $inc <br>".$nl;
					echo $erro;
					die();
				}
			}
		}
		
		require_once($inc);
	}
	$args = func_get_args();
	switch(count($args)){
		case 1:
			$obj = new $classname;
			break;
		case 2:
			$obj = new $classname($args[1]);
			break;
		case 3:
			$obj = new $classname($args[1],$args[2]);
			break;
		case 4:
			$obj = new $classname($args[1],$args[2],$args[3]);
			break;
		default:
			$code = '$obj = new ' . $classname . '(';
			foreach($args as $n => $arg){
				if ($n){
					$code .= ($n > 1 ? ',' : '') . '$args[' . $n . ']';
				}
			}
			$code .= ');';
			eval($code);
			break;
	}
	if (!is_object($obj)){
		echo "<p>CreateObject('$class'): ão foi possável instanciar a classe!!!<br />\n".function_backtrace(1)."</p>\n";
	}
	return $obj;
}

/**
 * Execute a function with multiple arguments
 * We take object $GLOBALS[classname] from class if exists
 *
 * @param string app.class.method method to execute
 * @example ExecObject('etemplates.so_sql.search',$criteria,$key_only,...);
 * @return mixed reference to returnvalue of the method
 */
function &ExecMethod($acm){
	global $config;
	$ret = '';
	if(empty($acm)){
		return $ret;
	}
	if (strpos($acm,'::') !== false){
		list($class,$method) = explode('::',$acm);
		$acm = array($class,$method);
	}
	if (!is_callable($acm)){
		if(count(explode('.',$acm)) == 3){
			list($app,$class,$method) = explode('.',$acm);
		}else{
			list($app,$class) = explode('.',$acm);
		}
		//echo "APP: $app - CLASS: $class - METODO: $method <br>\n";
		if (!is_object($obj =& $GLOBALS[$class])){
			$obj =& CreateObject($acm);
		}
		
		if(!isset($method) || !isset($obj)){
			if(!isset($method)){
				echo "<p><b>"."</b>: não encontrado o método. ACM: '$acm'</p>\n";
				return false;
			}
		}
		
		if (!method_exists($obj,$method)){
			echo "<p><b>"."</b>: não encontrado o método '$method' na classe '$class'</p>\n";
			$ret = false;
			return $ret;
		}
		$acm = array($obj,$method);
	}
	$args = func_get_args();
	unset($args[0]);
	//print_r($acm);
	//TODO: verificar pq as fun�oes abaixo geram 'Notice'
	if(count($args) > 0){
		if($config['debug'] === true){
			$ret = call_user_func_array($acm,$args);
		}else{
			$ret = @call_user_func_array($acm,$args);
		}
	}else{
		if($config['debug'] === true){
			$ret = call_user_func($acm);
		}else{
			$ret = @call_user_func($acm);
		}
	}
	return $ret;
}


/*
 * Redireciona para uma nova pagina sem apagar a sessao
 */
function redireciona( $link) {
	if(!empty($link)){
		$url = "Location: $link";
		header( $url );
		exit();
	}
}

/**
 * Grava uma variavel na sessao
 */
function putAppVar($variavel, $valor){
	global $app;
	$app->_variaveis[$variavel] = $valor;
}

/*
 * Retorna o valor gravado em uma sessao
 */

function getAppVar($variavel){
	global $app;
	if(isset($app->_variaveis[$variavel])){
		return $app->_variaveis[$variavel];
	}
	return null;
}

/*
 * Esclui variavel gravado em uma sessao
 */

function unsetAppVar($variavel){
	global $app;
	unset($app->_variaveis[$variavel]);
}

/*
 * Verifica se existe a variavel
 */
function issetAppVar($variavel){
	global $app;
	return isset($app->_variaveis[$variavel]);
}

/**
 * Retorna um parametro
 *
 * @author	Alexandre Thiel
 * @access	public
 * @param	array	$arr	Indica a origem ($_GET, $_POST ou $_REQUEST)
 * @param	string	$name	Nome do parametro a procurar
 * @param	string	$def	Valor que deve retornar se o parametro n�o existir
 * @return	mixed	Valor do parametro
 */
function getParam( $arr, $name, $def = null ) {
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}


function query($sql, $debugQuery = false, $debugRet = false){
	global $db, $config;
	$ret = array();
	if($debugQuery){
		echo "\nSQL: $sql <br>\n";
	}
	if(isset($config['site']['logQuery']) && $config['site']['logQuery']){
		log::gravaLog('logQuery', getModulo().' - '.getClasse().' - '.getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $db->Execute($sql);
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


function montaSQL($campos, $tabela, $tipo = 'INSERT', $where = ''){
	$sql = '';
	if(count($campos) > 0 && !empty($tabela)){
		if(strtoupper($tipo) == 'INSERT'){
			$camposTemp = array();
			$valoresTemp = array();
			foreach ($campos as $campo => $valor){
				$camposTemp[] = $campo;
				if(substr($valor, 0, 1) == '"'){
					//se começar com aspas duplas quer diquer que é um comando SQL
					$valor = str_replace('"', '', $valor);
				}elseif(substr($valor, 0, 1) != "'" && $valor != 'NULL' && strpos($valor, 'DATETIME,CONVERT') === false){
					$valor = "'".$valor."'";
				}
				$valoresTemp[] = $valor;
			}
			$sql = "INSERT INTO $tabela 
					(".implode(",\n", $camposTemp).")
					VALUES 
					(".implode(",\n", $valoresTemp).")";
		}elseif(strtoupper($tipo) == 'UPDATE' && !empty($where)){
			$sqlTemp = array();
			$sql = "UPDATE $tabela SET \n";
			foreach ($campos as $campo => $valor){
				if(substr($valor, 0, 1) == '"'){
					//se começar com aspas duplas quer dizer que é um comando SQL
					$valor = str_replace('"', '', $valor);
				}elseif(substr($valor, 0, 1) != "'" && $valor != 'NULL'){
					$valor = "'".$valor."'";
				}
				$sqlTemp[] = $campo.' = '.$valor."\n";
			}
			$sql .= implode(',', $sqlTemp);
			$sql .= "WHERE $where";
		}
	}
	
	return $sql;
}

//--------------------------------------------------------- Tradução --------------------------
function traducao($objeto, $id, $lingua, $padrao_pt_br, $reescrever = false){
	
	return $padrao_pt_br;
	//TODO: terminar processo
	$ret = $padrao_pt_br;
	$sql = "select texto, lingua from sys500 where objeto = '$objeto' and id = '$id' and lingua in ($lingua)";
	$rows = query($sql);
	if(is_array($rows) && count($rows) == 2){
		//tem em pt-br e na língua do usuário
		//pegar a da lingua do usuário
		foreach ($rows as $row){
			if($row['lingua'] != 'pt-br'){
				$ret = $row['texto'];
			}
		}
	}
	elseif(is_array($rows) && count($rows) == 1){
		//só tem em pt-br
		//retornar o em pt-br
		$ret = isset($rows[0]['texto']) ? $rows[0]['texto'] : $padrao_pt_br;
	}
	elseif(is_array($rows) && count($rows) == 0){
		//n tem em nenhum dos 2
		//incluir na tabela
		//retornar o padrao
		$temp = array(
				'objeto' => $objeto,
				'id' => $id,
				'lingua' => 'pt-br',
				'texto' => $padrao_pt_br,
		);
		$sql = montaSQL($temp, 'sys500');
		query($sql);
	}
	return $ret;
}

function getLingua(){
	global $app;
	return $app->_userLingua;
}

//--------------------------------------------------------- GETs ------------------------------

function getUsuario($campo = 'usuario', $usuario = ''){
	$ret = '';
	
	if(empty($usuario)){
		global $app;
		
		switch (strtolower($campo)) {
			case 'id':
				$ret = isset($app->_userID) ? $app->_userID : 'schedule';
				break;
			case 'usuario':
				$ret = isset($app->_user) ? $app->_user : 'schedule';
				break;
			case 'avatar':
				$ret = isset($app->_avatar) ? $app->_avatar : '';
				break;
			default:
				$ret = isset($app->_usuario[$campo]) ? $app->_usuario[$campo] : '';
				break;
		}
	}else{
		$sql = "SELECT * FROM sys001 WHERE user = '$usuario'";
		$rows = query($sql);
		
		if(isset($rows[0][$campo])){
			$ret = $rows[0][$campo];
		}
	}
	
	return $ret;
}

function getCliente(){
	global $config;
	
	return $config['app'];
}

function getAplicacao(){
	global $config;
	
	return $config['appNome'];
}

function getApp(){
	global $config;
	
	return $config['app'];
}


/**
 * Retorna a empresa (ou filial)
 *
 */
function getEmp(){
	global $config;
	
	return '01';
}

function getLink($full = false){
	global $config;
	$ret = 'index.php?menu='.getModulo().'.'.getClasse().'.';
	
	if($full){
		$ret = $config['raiz'].$ret;
	}
	
	return $ret;
}

function getLinkAjax($operacao, $full = false, $classe = '', $modulo = ''){
	global $config;
	$classe = empty($classe) ? getClasse() : $classe;
	$modulo = empty($modulo) ? getModulo() : $modulo;
	$ret = 'ajax.php?menu='.$modulo.'.'.$classe.'.ajax.'.$operacao;
	
	if($full){
		$ret = $config['raiz'].$ret;
	}
	
	return $ret;
}

function getLinkArquivos($operacao, $full = false, $classe = '', $modulo = ''){
	global $config;
	$classe = empty($classe) ? getClasse() : $classe;
	$modulo = empty($modulo) ? getModulo() : $modulo;
	$ret = 'arquivos.php?menu='.$modulo.'.'.$classe.'.arquivos.'.$operacao;
	
	if($full){
		$ret = $config['raiz'].$ret;
	}
	
	return $ret;
}
	/**
 * Retorna o módulo
 *
 * @return string	Modulo
 */
function getModulo(){
	global $pagina;
	$ret = isset($pagina->_modulo) ? $pagina->_modulo : '';
	return $ret;
}

/**
 * Retorna a classe
 *
 * @return string	Modulo
 */
function getClasse(){
	global $pagina;
	$ret = isset($pagina->_classe) ? $pagina->_classe: '';
	return $ret;
}

/**
 * Retorna o metodo
 *
 * @return string	Metodo
 */
function getMetodo(){
	global $pagina;
	$ret = '';
	if(isset($pagina->_metodo)){
		$ret = $pagina->_metodo;
	}
	return $ret;
}


/**
 * Retorna a operação
 */
function getOperacao(){
	global $pagina;
	$ret = '';
	if(isset($pagina->_operacao)){
		$ret = $pagina->_operacao;
	}
	return $ret;
}

/**
 * Limpa o nome do campo para ser utilizado como ID
 */
function ajustaID($campo){
	$id = str_replace("[","",$campo);
	$id = str_replace("]","",$id);
	$id = str_replace(" ","_",$id);
	$id = str_replace("/","_",$id);
	return $id;
}

/**
 * Verifica se existe a chave, se não retorna o padrao
 * SE $empty for false, retorna o padrão se o parametro estiver vazio
 */
function verificaParametro($param, $chave, $padrao = '', $empty = true){
	$ret = $padrao;
	
	if(isset($param[$chave])){
		if(empty($param[$chave]) && !$empty){
			$ret = $padrao;
		}else{
			$ret = $param[$chave];
		}
	}
	
	return $ret;
}

/**
 *
 * @param string $tabela nome da tabela as ser pesquisada
 * 
 * $param['mostraID'] 		- boolean 	- Indica se deve incluir o ID antes da descrição 
 * $param['filtro'] 		- array		- Condições que devem ser aplicados no select
 * $param['camposChave']	- string	- campos que são chave
 * $param['campoDescricao'] - string	- campo de descrição
 * $param['chaveAlternativa']- string	- Chave alternativa
 * 
 * @return array
 */
function listaTabela($tabela, $param = []){
	$ret = [];
	$id = verificaParametro($param, 'mostraID', false, false);
	$filtro = verificaParametro($param, 'filtro', [], false);
	$branco = verificaParametro($param, 'branco', true);
	
	$campos_chave = verificaParametro($param, 'camposChave', '');
	$campo_desc = verificaParametro($param, 'campoDescricao', '');
	
	if(empty($campos_chave) || empty($campo_desc)){
		$sql = "SELECT chave, campo_desc FROM sys002 where tabela = '$tabela'";
		$rows = query($sql);
		if(isset($rows[0]['chave'])){
			$campos_chave = $rows[0]['chave'];
			$campo_desc   = $rows[0]['campo_desc'];
		}
	}
	
	if(!empty($campos_chave) && !empty($campo_desc)){
		$where = '';
		
		if(count($filtro) != 0){
			foreach($filtro as $campo => $valor){
				if(!empty($where)){
					$where .= ' AND ';
				}
				$where .= $campo . ' = ' . $valor;
			}
		}
		
		if(isset($param['chaveAlternativa']) && !empty($param['chaveAlternativa'])){
			$campos_chave = $param['chaveAlternativa'];
		}
		
		$sql = 'SELECT ' . $campos_chave . ', ' . $campo_desc . ' FROM ' . $tabela;
		if(!empty($where)){
			$sql .= ' WHERE '.$where;
		}
		
		$rows = query($sql);
//echo "$sql <br>\n";
		$lista_campos = array('chaves' => explode(', ', $campos_chave), 'desc' => $campo_desc);
		
		if(is_array($rows) && count($rows) > 0){
			
			if($branco){
				$ret[] = ['', ''];
			}
			
			foreach($rows as $valor){
				$indice = '';
				foreach ($lista_campos['chaves'] as $campo_atual){
					if($indice != ''){
						$indice .= '|';
					}
					$indice .= $valor[$campo_atual];
				}
				
				if($id){
					$ret[] = array($indice, $indice . ' - ' . $valor[$lista_campos['desc']]);
				}else{
					$ret[] = array($indice, $valor[$lista_campos['desc']]);
				}
			}
		}
	}else{
		addPortalMensagem('Erro!!', 'Tabela '.$tabela.' não encontrada na sys002 ou não indicado campos chave e descrição', 'erro');
	}
	return $ret;
}

/*
 * Retorna os valores disponiveis em uma tabela
 */

function tabela($tab, $param = []){
	$tabela = array();
	$branco = verificaParametro($param, 'branco', true);	
	$ordem = verificaParametro($param, 'ordem', 'descricao');
	$id = verificaParametro($param, 'mostraID', false, false);
	$base64 = $param['base64'] ?? false;
	
	if($branco){
		$tabela[0][0] = "";
		$tabela[0][1] = "&nbsp;";
	}
	
	$sql = "SELECT chave, descricao FROM sys005 WHERE tabela = '$tab' AND ativo = 'S' ORDER BY ";
	$ordem = "descricao";
	if ($ordem != "descricao"){
		$ordem = "chave";
	}
	$sql .= $ordem;
//echo "SQL: $sql <br>\n";
	$rows = query($sql);
	$rows = traducoes::traduzirSys005($tab, $rows);
	if(count($rows) > 0){
		$i = count($tabela);
		foreach ($rows as $row) {
			$tabela[$i][0] = $base64 ? base64_decode($row[0]) : $row[0];
			if($id){
				$tabela[$i][1] = $row[0].'-'.$row[1];
			}else{
				$tabela[$i][1] = $row[1];
			}
			$i++;
		}
	}
//print_r($tabela);
	return $tabela;
}

/*
 * Retorna a descrição de um valor em uma tabela
 *
 * Se "|" for encontrado em $tabela, quer dizer que foi enviado tabela e chave na mesma variavel
 */

function getTabelaDesc($tabela,$chave = ""){
	$ret = '';
	if(strpos($tabela, "|") !== false){
		$temp = explode("|",$tabela);
		$tabela = $temp[0];
		$chave	= $temp[1];
	}
	$traducao = traducoes::traduzir("sys005-$tabela-$chave"); //descomentar quando passar a usar as traduções
	if($traducao === ''){
	    $sql = "SELECT descricao FROM sys005 WHERE tabela = '$tabela' AND chave = '$chave' ";
	    //echo "$sql <br>\n";
	    $rows = query($sql);
	    if(count($rows) > 0){
	        $ret = $rows[0][0];
	    }
	}
	else{
	    $ret = $traducao;
	}
	
	return $ret;
	
}

/**
 * Gera uma string aleatoria
 *
 * @param	int		$tam	Quantidade de caracteres
 * @param	boolean	$num	Indica se deve constar numeros
 * @param	boolean	$min	Indica se deve constar letras minusculas
 * @param	boolean	$mai	Indica se deve constar letras maiusculas
 * @return	string
 */

function geraStringAleatoria($tam, $num = true, $min = true, $mai = true){
	$ret = '';
	$string = "";
	if($num){
		$string .= "0123456789";
	}
	if($min){
		$string .= "abcdefghijklmnopqrstuvwxyz";
	}
	if($mai){
		$string .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	}
	$quant= strlen($string);
	for($i=0;$i<$tam;$i++){
		$ret .= $string[rand(0, $quant-1)];
	}
	
	return $ret;
}

/*
 * Redireciona para uma nova pagina apagando a sessao
 */
function redirect( $params = '', $index = true) {
	session_write_close();
	$url = "Location: ";
	if(empty($params)){
		$url .= 'index.php';
	}elseif($index === true){
		$url .= "index.php?$params";
	}else{
		$url .= $params;
	}
	header( $url );
	exit();
}

function formataNum($valor, $tam, $caracter = "0"){
	
	while(strlen($valor) < $tam){
		$valor = $caracter.$valor;
	}
	return $valor;
}

function escapeQuery($str) {
	$str = strtr($str, array(
			"\0" => "",
			"'"  => "&#39;",
			//	"\"" => "&#34;",
			//"\"" => '\"',
			"\\" => "&#92;",
			// more secure
			"<"  => "&lt;",
			">"  => "&gt;",
			"SELECT" => "",
			"select" => "",
			"DELETE" => "",
			"delete" => "",
			"UPDATE" => "",
			"update" => "",
	));
	
	// Se for <b> ou </b> volta ao normal
	$str = str_replace("&lt;b&gt;", "<b>", $str);
	$str = str_replace("&lt;/b&gt;", "</b>", $str);
	
	return $str;
}

/**
 * Retorna o próximo número de um campo em uma tabela
 *
 * @param	array	$arr	Indica a origem ($_GET, $_POST ou $_REQUEST)
 * @param	string	$name	Nome do parametro a procurar
 * @param	string	$def	Valor que deve retornar se o parametro n�o existir
 * @return	mixed	Valor do parametro
 */
function getProximoNumero($tabela, $campo, $where = "", $tam = 0,$empfil = true, $cliente = true){
	//@TODO: Fazer o controle dos numeros utilizados (para quando varias pessoas incluirem ao mesmo tempo)
	
	$sql = "SELECT max($campo) FROM $tabela";
	$whereSQL = "";
	if(!empty($where)){
		$whereSQL .= " $where";
	}
	if($empfil){
		if($whereSQL != ""){
			$whereSQL .= " AND ";
		}
		$whereSQL .= " emp = '".getEmp()."' AND fil = '".getFil($tabela)."' ";
	}
	if($cliente){
		if($whereSQL != ""){
			$whereSQL .= " AND ";
		}
		$whereSQL .= " cliente = '".getCliente()."' ";
	}
	//echo "SQL: $sql <br>";
	if(!empty($whereSQL)){
		$sql .= " WHERE ".$whereSQL;
	}
	
	$rows = query($sql);
	if(count($rows) > 0){
		$atual = $rows[0][0];
	}else{
		$atual = 0;
	}
	if($tam == 0){
		$tam = strlen(("$atual"));
	}
	$proximo = $atual + 1;
	while (strlen("$proximo") < $tam){
		$proximo = "0".$proximo;
	}
	return $proximo;
}

/**
 * Mantido para mantar a compatibilidade 
 */
function enviaEmailAntigo($destinatario, $assunto, $mensagem, $anexos = array(), $emailsender = array(),$embeddedImage = array(), $responderPara=array(),$bcc = ''){
	$param = [];
	$param['mensagem']		= $mensagem;
	$param['destinatario'] 	= $destinatario;
	$param['emailsender'] 	= $emailsender;
	$param['assunto'] 		= $assunto;
	$param['anexos'] 		= $anexos;
	$param['embeddedImage'] = $embeddedImage;
	$param['responderPara'] = $responderPara;
	$param['bcc'] 			= $bcc;
	
	enviaEmail($param);
}

/**
 * enviaEmail
 *
 * @param	array	$param Parametros de envio
 * @return	boolean	Sucesso ou não do envio
 */
function enviaEmail($param){
	global $config;
	$quebra_linha = "\n";
	//	$assunto = utf8_decode($assunto);
	$emailsender 	= $param['emailsender'] ?? [];
	$destinatario 	= $param['destinatario'] ?? '';
	$mensagem		= $param['mensagem'] ?? '';
	$assunto		= $param['assunto'] ?? '';
	$anexos			= $param['anexos'] ?? [];
	$embeddedImage	= $param['embeddedImage'] ?? [];
	$responderPara	= $param['responderPara'] ?? [];
	$mensagem 		= str_replace("'", "´", $mensagem);
	$bcc			= $param['bcc'] ?? '';
	$cc				= $param['cc'] ?? '';
	
	if(empty(trim($destinatario))){
		return false;
	}
	
	if(!is_array($emailsender) || count($emailsender) == 0){
		$emailsender[0] = $config['smtp']['emissorPadrao'];
		$emailsender[1] = $config['smtp']['nomeEmissorPadrao'];
	}
	$emailsender[1] = utf8_decode($emailsender[1]);
	
	
	$destinatario = str_replace(',', ';', $destinatario);
	$dests = explode(";", $destinatario);
	
	switch ($config['email']) {
		case 'MAIL':
			$headers = "Content-type: text/html; charset=iso-8859-1\n";
			if(!mail($destinatario[0], $assunto, $mensagem, $headers ,"-r".$emailsender)){ // Se for Postfix
				$headers .= "Return-Path: " . $emailsender . $quebra_linha; // Se "nao for Postfix"
				if(!$ret = mail($emaildestinatario[0], $assunto, $mensagemHTML, $headers )){
					logAcesso("Email erro: ", 3);
				}
			}
			return true;
			break;
		case 'SMTP':
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->CharSet = 'UTF-8';
			$mail->SMTPDebug  = 1;
			//$mail->Debugoutput = 'html';
			$mail->Debugoutput = 'error_log';
			$mail->Host       = $config['smtp']['servidor'];
			//Set the SMTP port number - likely to be 25, 465 or 587
			$mail->Port       = $config['smtp']['porta'];
			$mail->SMTPAuth   = $config['smtp']['SMTPAuth'];
			$mail->Username   = $config['smtp']['usuario'];
			$mail->Password   = $config['smtp']['senha'];
			$mail->SMTPOptions = array(
					'ssl' => array(
							'verify_peer' => false,
							'verify_peer_name' => false,
							'allow_self_signed' => true
					)
			);
			if(isset($config['smtp']['secure']) && !empty($config['smtp']['secure'])){
				$mail->SMTPSecure = $config['smtp']['secure'];
			}
			if(count($responderPara) > 0){
				$mail->addReplyTo($responderPara[0], $responderPara[1]);
			}
			foreach ($emailsender as $sender){
				$mail->SetFrom($emailsender[0], $emailsender[1]);
			}
			foreach ($dests as $dest) {
				$mail->AddAddress($dest);
			}
			if($bcc != ''){
				//echo "Adicionando cópia oculta: $bcc <br>\n";
				$mail->addBCC($bcc); // CÓPIA OCULTA
			}
			if(!empty($cc)){
				//echo "Adicionando cópia oculta: $bcc <br>\n";
				$mail->addCC($cc); // CÓPIA OCULTA
			}
			$mail->Subject = $assunto;
			$mail->MsgHTML($mensagem);
			if(count($anexos) > 0){
				foreach ($anexos as $anexo){
					$mail->AddAttachment($anexo);
				}
			}
			if(count($embeddedImage) > 0){
				$embedded = 1;
				foreach ($embeddedImage as $imagem){
					$mail->AddEmbeddedImage($imagem['caminho'].$imagem['nome'], "embedded".$embedded, $imagem['nome']);
					//echo "Imagem: ".$imagem['caminho'].$imagem['nome']."<br>\n";
					$embedded++;
				}
			}
			if(!$mail->Send()) {
				if($mail->SMTPDebug){
					echo "Servidor: ".$config['smtp']['servidor']."<br>\n";
					echo "Usuario: ".$config['smtp']['usuario']."<br>\n";
					echo "Senha: ".$config['smtp']['senha']."<br>\n";
					echo "Erro: ".$mail->ErrorInfo."<br>\n";
				}
				log::logAcesso("Email erro: " . $mail->ErrorInfo, 3);
				log::logAcesso("Servidor: ".$config['smtp']['servidor']." Usuario: ".$config['smtp']['usuario']." Senha: ".$config['smtp']['senha'], 3);
				return false;
			} else {
				return true;
			}
			break;
		case 'SES':
			break;
		default:
			die("Necess�rio indicar uma forma de envio de email");
			break;
	}
}

/**
 * Mantido para mantar a compatibilidade
 */
function agendaEmailAntigo($dia, $hora, $programa, $destinatario, $assunto, $mensagem, $anexos = array(), $emailsender = array(),$embeddedImage = array(), $responderPara=array(),$bcc = '', $teste = false){
	$param = [];
	$param['dia']			= $dia;
	$param['hora']			= $hora;
	$param['programa']		= $programa;
	$param['mensagem']		= $mensagem;
	$param['destinatario'] 	= $destinatario;
	$param['emailsender'] 	= $emailsender;
	$param['assunto'] 		= $assunto;
	$param['anexos'] 		= $anexos;
	$param['embeddedImage'] = $embeddedImage;
	$param['responderPara'] = $responderPara;
	$param['bcc'] 			= $bcc;
	
	agendaEmail($param);
	
}

function agendaEmail($param){
	global $config;
	
	$destinatario = $param['destinatario'] ?? '';
	
	if(!empty(trim($destinatario))){
		
		$teste 			= $param['teste'] ?? false;
		$anexos 		= $param['anexos'] ?? [];
		$embeddedImage 	= $param['embeddedImage'] ?? [];
		
		$campos = array();
		$campos['id'] 			= geraID('sys200');
		$campos['dia'] 			= $param['dia']			;
		$campos['hora'] 		= $param['hora']			;
		$campos['programa']		= $param['programa']		;
		$campos['mensagem'] 	= str_replace("'", "´", $param['mensagem']);
		$campos['para'] 		= strtolower($param['destinatario']);
		$campos['de'] 			= serialize($param['emailsender']);
		$campos['assunto'] 		= $param['assunto'];
		$campos['responderpara']= serialize($param['responderPara']);
		$campos['bcc'] 			= $param['bcc'];
		$campos['status'] 		= '0';
		
		if(is_array($anexos) && count($anexos) > 0){
			$campos['anexo'] = 'S';
		}else{
			$campos['anexo'] = 'N';
		}
		if(is_array($embeddedImage) && count($embeddedImage) > 0){
			$campos['imagem'] = 'S';
		}else{
			$campos['imagem'] = 'N';
		}
		
		//print_r($campos);
		$tabela = 'sys200';
		if($teste === true){
			$tabela = 'sys200_teste';
		}
		$sql = montaSQL($campos, $tabela);
		query($sql);
//echo "$sql <br>\n";
		
		if($teste === false){
			if($campos['anexo'] == 'S' || $campos['imagem']){
				$diretorio = $config['tempEmailDir'].$campos['id'];
				mkdir($diretorio);
			}
			
			if($campos['anexo'] == 'S'){
				$dir = $diretorio.'/anexos';
				mkdir($dir);
				foreach ($anexos as $anexo){
					$nome = basename($anexo);
					copy($anexo, $dir.'/'.$nome);
				}
			}
			if($campos['imagem'] == 'S'){
				$dir = $diretorio.'/imagens';
				mkdir($dir);
				foreach ($embeddedImage as $imagem){
					$nome = basename($imagem);
					copy($imagem, $dir.'/'.$nome);
				}
			}
		}
		return true;
	}else{
		log::gravaLog('email_agenda_erro', 'Tentativa de gerar email sem destinatário '.$campos['programa'].' - '.$campos['para'] );
	}
	
	return false;
}

/**
 * Gera um ID único na $tabela
 *
 * @param	string	$tabela		Nome da tabela
 * @param	string	$campo		Campo da tabela
 * @param	int		$tam		Quantidade de caracteres
 * @return	string
 */
function geraID($tabela, $campo = 'id', $tam = 15){
	if($tabela == '' || $campo == '' || $tam == '' || $tam < 1){
		return '';
	}
	$ret = geraStringAleatoria($tam, true, false, true);
	
	$sql = "SELECT $campo FROM $tabela WHERE $campo = '$ret'";
	$rows = query($sql);
	if(count($rows) > 0){
		$ret = geraID($tabela, $campo, $tam);
	}
	
	return $ret;
}

/*
 * Retorna o valor do parametro do sistema (SYS006)
 */

function getParametroSistema($parametro){
	$ret = NULL;
	
	$sql = "SELECT valor FROM sys006 WHERE parametro = '$parametro' ";
	$rows = query($sql);
	
	if(isset($rows[0][0])){
		$ret = $rows[0][0];
	}
	return $ret;
}

function mascara($val, $mask){
    $maskared = '';
    $k = 0;
    for($i = 0; $i<=strlen($mask)-1; $i++){
        if($mask[$i] == '#'){
            if(isset($val[$k])){
                $maskared .= $val[$k++];
            }
        }else{
            if(isset($mask[$i])){
                $maskared .= $mask[$i];
            }
        }
    }
    return $maskared;
}
