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

    ini_set('display_errors',1);
    ini_set('display_startup_erros',1);
    error_reporting(E_ALL);
    
    
function TWS_autoload($class){
	if(class_exists($class)){
		return;
	}
	
	global $config;
	
	$cliente = $config['app'];
	$classe = strtolower($class);
	
	if(strpos($classe, 'controller') !== false){
		$file = $config["include"]."controller".DIRECTORY_SEPARATOR."tws.".strtolower($class).".php";
		//echo $file."<br>\n";
		if (file_exists($file)){
			include_once($file);
		}else{
			addPortalMensagem("Controler ".$config["include"]."controller".DIRECTORY_SEPARATOR."tws.".strtolower($class).".php não encontrado",'error');
		}
	}
	
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
function redireciona( $link = '') {
	if($link == ''){
		$link = getLink().'index';
	}
	$url = "Location: $link";
	header( $url );
	exit();
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


function query($sql, $debugQuery = false, $debugRet = false, $echo = true){
	global $db, $config;
	
	$ret = array();
	if($debugQuery){
		if($echo){
			echo "\nSQL: $sql <br>\n";
		}
		log::gravaLog('sql_debug', $sql);
	}
	if(isset($config['site']['logQuery']) && $config['site']['logQuery']){
		log::gravaLog('logQuery', getModulo().' - '.getClasse().' - '.getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	
	
	//$res = $db->Execute($sql);
	try {
	   $res = $db->Execute($sql);
	//} catch(PDOException $e){
	} catch (\Throwable $th) {
	    echo "ERRO SQL";
	    $res = false;
	    log::gravaLogErroDB('logQueryErro', "Erro no sql = $sql");
	}
	
	if ($res === false){
		if($config['debug'] || $debugQuery){
			if($echo){
				echo "<br>\nErro no SQL: $sql \n<br>";
				print $db->ErrorMsg();
				echo "\n<br>------------------------------<br>\n";
			}
			log::gravaLog('sql_debug', 'ERRO');
			log::gravaLog('sql_debug', $db->ErrorMsg());
		}
		return false;
	}else{
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		$pos3 = strpos($sql, "UPDATE");
		if(($pos1 === false || $pos1 > 5) && $pos2 === false){
			$ret = $db->insert_Id();
		}elseif($pos3 === false || $pos3 > 5){
			$ret = $res->GetRows();
		}else{
			$ret = $db->affected_rows();
		}
		if($debugRet){
			if($echo){
				print_r($ret);
			}
			log::gravaLog('sql_debug', $ret, true);
		}
		return $ret;
	}
}


function montaSQL($campos, $tabela, $tipo = 'INSERT', $where = '', $campos_select = ''){
	global $config;
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
		elseif(strtoupper($tipo) == 'SELECT'){
			$sql = "select ";
			if(strpos($campos_select, '|') === false){
				$sql .= $campos_select;
			}
			else{
				$sql .= implode(', ', explode('|', $campos_select));
			}
			
			$sql .= " from $tabela ";
			$campos_where = array();
			foreach ($campos as $chave => $valor){
				if($valor != ''){
					$campos_where[] = "$chave = '$valor'";
				}
			}
			if(count($campos_where) > 0){
				$sql .= 'WHERE ' . implode(' and ', $campos_where);
			}
		}
	}
	
	if($config['debug']){
		log::gravaLog('montaSQL', $sql);
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

function getAvatarUsuario($usuario = ''){
    $ret = '';
    if(empty($usuario)){
        global $app;
        $ret = isset($app->_avatar) ? $app->_avatar : '';
    }
    else{
        global $config;
        $id = getUsuario('id', $usuario);
        $avatar = $config['baseS3'].'imagens/avatares/'.$id.'.jpg';
        $avatarLink = $config['imagens'].'avatares/'.$id.'.jpg';
        if(!file_exists($avatar)){
            $avatar = $config['baseS3'].'imagens/avatares/'.$id.'.png';
            $avatarLink = $config['imagens'].'avatares/'.$id.'.png';
            if(!file_exists($avatar)){
                $avatar = $config['baseS3'].'imagens/avatares/'.$id.'.gif';
                $avatarLink = $config['imagens'].'avatares/'.$id.'.gif';
                if(!file_exists($avatar)){
                    $avatarLink = $config['imagens'].'avatares/avatarGenerico.jpg';
                }
            }
        }
        $ret = $avatarLink;
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

/**
 * Retorna o link para chamada de AJAX
 *
 * @param string $operacao operação a ser realizada
 * @param boolean $full Indica se deve retornar o caminha completo - com url/....)
 * @param string $classe Indica a classe (por padrão a classe que está sendo executada)
 * @param string $modulo Indica o modulo (por padrão o modulo que está sendo executada)
 * @return string link completo
 */

function getLinkAjax($operacao, $full = false, $classe = '', $modulo = ''){
	global $config;
	$classe = empty($classe) ? getClasse() : $classe;
	$modulo = empty($modulo) ? getModulo() : $modulo;
	$operacao =  empty($modulo) ? '' : '.'.$operacao;
	$ret = 'ajax.php?menu='.$modulo.'.'.$classe.'.ajax'.$operacao;
	
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
	
	return enviaEmail($param);
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
	
	if(isset($param['programa'])){
	    $programa = $param['programa'];
	}
	else{
	    $stack = debug_backtrace();
	    $programa = $stack[1]['class'] ?? $stack[2]['class'] ?? $stack[3]['class'] ?? $stack[4]['class'] ?? $stack[5]['class'] ?? $stack[6]['class'] ?? $stack[7]['class'] ?? $stack[8]['class'] ?? 'sem classe';
	}
	
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
				if($ret){
				    gravarLogSys205($destinatario, $assunto, $programa);
				}
			}
			else{
			    gravarLogSys205($destinatario, $assunto, $programa);
			}
			return true;
			break;
		case 'SMTP':
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->CharSet = 'UTF-8';
			//$mail->SMTPDebug  = 1;
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
			    gravarLogSys205($destinatario, $assunto, $programa);
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

function gravarLogSys205($destinatario, $assunto, $programa = '', $data = '', $hora = ''){
    if($destinatario != '' && $assunto != '' && $programa != ''){
        if($programa === ''){
            $stack = debug_backtrace();
            $programa = $stack[1]['class'] ?? $stack[2]['class'] ?? $stack[3]['class'] ?? $stack[4]['class'] ?? $stack[5]['class'] ?? $stack[6]['class'] ?? $stack[7]['class'] ?? $stack[8]['class'] ?? 'sem classe';
        }
        if($data === ''){
            $data = date('Ymd');
        }
        if($hora === ''){
            $hora = date('H:i');
        }
        
        $dados = array(
            'programa' => $programa,
            'destinatario' => $destinatario,
            'assunto' => $assunto,
            'data' => $data,
            'hora' => $hora,
        );
        $sql = montaSQL($dados, 'sys205');
        query($sql);
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
	$programa = $param['programa'];
	
	if(!empty(trim($destinatario))){
		
		$teste 			= $param['teste'] ?? false;
		$anexos 		= $param['anexos'] ?? [];
		$embeddedImage 	= $param['embeddedImage'] ?? [];
		
		$campos = array();
		$campos['id'] 			= geraID('sys200');
		$campos['dia'] 			= $param['dia'] ?? '';
		$campos['hora'] 		= $param['hora'] ?? '';
		$campos['programa']		= $param['programa'] ?? 'nap_informado';
		$campos['mensagem'] 	= str_replace("'", "´", $param['mensagem']);
		$campos['para'] 		= strtolower($param['destinatario']);
		$campos['de'] 			= serialize($param['emailsender'] ?? '');
		$campos['assunto'] 		= str_replace("'", "´", $param['assunto']);
		$campos['responderpara']= serialize($param['responderPara'] ?? '');
		$campos['bcc'] 			= $param['bcc'] ?? '';
		$campos['status'] 		= '0';
		
		$debug = $param['debug'] ?? false;
		
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
		
		if($debug){
			print_r($campos);
		}
		
		$tabela = 'sys200';
		if($teste === true){
			$tabela = 'sys200_teste';
		}
		$sql = montaSQL($campos, $tabela);
		query($sql);

		if($debug){
			echo "$sql <br>\n";
		}
		
		if($teste === false){
			if($campos['anexo'] == 'S' || $campos['imagem'] == 'S'){
				$diretorio = $config['tempEmailDir'].$campos['id'];
				mkdir($diretorio, '0777');
				chmod ($diretorio, 0777);
			}
			
			if($campos['anexo'] == 'S'){
				$dir = $diretorio.'/anexos';
				mkdir($dir, '0777');
				chmod ($dir, 0777);
				foreach ($anexos as $anexo){
					$nome = basename($anexo);
					copy($anexo, $dir.'/'.$nome);
				}
			}
			if($campos['imagem'] == 'S'){
				$dir = $diretorio.'/imagens';
				mkdir($dir, '0777');
				chmod ($dir, 0777);
				foreach ($embeddedImage as $imagem){
					$nome = basename($imagem);
					copy($imagem, $dir.'/'.$nome);
				}
			}
		}
		return true;
	}else{
		log::gravaLog('email_agenda_erro', 'Tentativa de gerar email sem destinatário '.$programa );
	}
	
	return false;
}

function enviaEmailAgenda(){
    global $config;
    
    $hora = date('H:i');
    $dia = date('Ymd');
    
    $sql = "SELECT id FROM sys200 WHERE status = '0' AND (dia = '' OR dia = '$dia') AND hora <= '$hora' AND tentativas < 3";
    $ids = query($sql);
    if(is_array($ids) && count($ids) > 0){
        foreach ($ids as $idz){
            $id = $idz['id'];
            $sql = "SELECT * FROM sys200 WHERE id = '$id' AND status = '0' AND tentativas < 3";
            $rows = query($sql);
            if(isset($rows[0]['status'])){
                
                //Muda o status para T - Tentando enviar
                $tentativas = $rows[0]['tentativas'] + 1;
                $sql = "UPDATE sys200 SET status = 'T', tentativas = $tentativas WHERE id = '$id'";
                query($sql);
                
                $email = $rows[0];
                $emailsender 	= unserialize($email['de']);
                $destinatario 	= $email['para'];
                $assunto		= $email['assunto'];
                $mensagem		= $email['mensagem'];
                $responderPara	= unserialize($email['responderpara']);
                $bcc			= $email['bcc'];
                
                $anexos = array();
                $embeddedImage = array();
                if($email['anexo'] == 'S' || $email['imagem'] == 'S'){
                    $diretorio = $config['tempEmailDir'].$id;
                    if(is_dir($diretorio)){
	                    if($email['anexo'] == 'S'){
	                        $dir = scandir($diretorio.'/anexos');
	                        if(count($dir) > 2){
	                            foreach ($dir as $arq){
	                                if($arq != "." && $arq != ".."){
	                                    $anexos[] = $diretorio.'/anexos/'.$arq;
	                                }
	                            }
	                        }
	                    }
	                    if($email['imagem'] == 'S'){
	                        
	                    }
                    }
                }
                
                $param_email = array(
                    'mensagem' => $mensagem,
                    'destinatario' => $destinatario,
                    'emailsender' => $emailsender,
                    'assunto' => $assunto,
                    'anexos' => $anexos,
                    'embeddedImage' => $embeddedImage,
                    'responderPara' => $responderPara,
                    'bcc' => $bcc,
                    'programa' => $email['programa'],
                );
                //$enviado = enviaEmailAntigo($destinatario, $assunto, $mensagem, $anexos, $emailsender, $embeddedImage, $responderPara, $bcc);
                $enviado = enviaEmail($param_email);
                
                //$enviado = enviaEmail('suporte@thielws.com.br', $assunto, $mensagem, $anexos, $emailsender, $embeddedImage, $responderPara, $bcc);
                if($enviado === true){
                    //Marca como enviado e apaga o anexo
                    $dataEnvio = date('Y-m-d H:i');
                    $sql = "UPDATE sys200 SET status = 'E', envio = '$dataEnvio'  WHERE id = '$id'";
                    query($sql);
                    //gravarLogSys205($destinatario, $assunto, $email['programa']);
                    if($email['anexo'] == 'S' || $email['imagem'] == 'S'){
                        if(apagaDiretorio($diretorio)){
                            //log::gravaLog('email_agenda', 'Excluido o diretório '.$diretorio);
                        }else{
                            log::gravaLog('email_agenda_erro', 'Não foi possível excluir o diretório '.$diretorio);
                        }
                        log::gravaLog('email_agenda', 'Enviado email '.$id);
                    }
                    
                }else{
                    //Retorna para não enviado
                    log::gravaLog('email_agenda_erro', 'Não foi possível enviar o email '.$id);
                    $sql = "UPDATE sys200 SET status = '0' WHERE id = '$id'";
                    query($sql);
                }
            }
        }
    }
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

/*
 * Grava o valor do paramentro do sistema (SYS006)
 */

function gravarParametroSistema($parametro, $valor){
    $valor_antigo = getParametroSistema($parametro);
    if(is_null($valor_antigo)){
        //caso o parâmetro não exista
        $sql = "insert into sys006 values (null, '$parametro', '', '', '$valor', 'T', '200', '', 'S')";
    }
    else{
        //caso o parâmetro exista
        $sql = "update sys006 set valor = '$valor' where parametro = '$parametro'";
    }
    query($sql);
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

function salvarArquivoUpload($caminho_origem, $caminho_destino = ''){
	global $config;
	$ret = false;
	if($caminho_destino == ''){
		$caminho_destino = $config['arquivosDir'];
		$temp = explode(DIRECTORY_SEPARATOR, $caminho_origem);
		$nome_arquivo = end($temp);
		$caminho_destino .= $nome_arquivo;
	}
	else{
		$temp = explode(DIRECTORY_SEPARATOR, $caminho_destino);
		$nome_arquivo = end($temp);
	}
	if(move_uploaded_file($caminho_origem, $caminho_destino)){
		$param = array(
			'caminho' => $caminho_destino,
			'nome' => $nome_arquivo,
			'usuario' => getUsuario(),
			'dt_inclusao' => date('YmdHis'),
		);
		$sql = montaSQL($param, 'sys007');
		//$sql = "insert into sys007 (id, caminho, nome, usuario, dt_inclusao) values (null, '$caminho_destino', '$nome_arquivo', '" . getUsuario() . "', '" . date('YmdHis') . "')";
		query($sql);
		$sql = montaSQL($param, 'sys007', 'SELECT', '', 'id');
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			$ret = $rows[0]['id'];
		}
	}
	return $ret;
}

function getDadosArquivoDownload($id){
	$ret = false;
	$sql = "select * from sys007 where id = $id";
	$rows = query($sql);
	if(is_array($rows) && count($rows) > 0){
		$ret = array(
			'nome' => $rows[0]['nome'],
			'caminho' => $rows[0]['caminho'],
		);
	}
	return $ret;
}

function montarDicionarioSys005($tabela){
	$ret = array();
	if(!empty($tabela)){
		$sql = "select chave, descricao from sys005 where tabela = '$tabela'";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row['chave']] = $row['descricao'];
			}
		}
	}
	return $ret;
}

function getIdUsuario($usuario){
	$ret = 0;
	$sql = "select id from sys001 where user = '$usuario'";
	$rows = query($sql);
	if(is_array($rows) && count($rows) > 0){
		$ret = $rows[0]['id'];
	}
	return $ret;
}

//------------------------ SCHEDULE -----------------------
/** Verifica se já foi executado determinado relatório em uma data
 * 	Retorna FALSE se ainda não foi executado e TRUE caso já tenha sido executado
 *
 * @param string $programa - Nome do Programa
 * @param string $dia - data
 * @return boolean
 */
function verificaExecucaoSchedule($programa,$dia){
	$ret = false;
	$sql = "SELECT * FROM  schedule_execucoes WHERE dia = '$dia' AND relatorio = '$programa'";
	$rows = query($sql);
	if(count($rows) > 0){
		$ret = true;
	}
	
	return $ret;
}

function gravaExecucaoSchedule($programa,$dia){
	if($programa != '' && $dia != ''){
		$sql = "INSERT INTO schedule_execucoes (dia,relatorio,usuario) VALUES ('$dia',  '$programa','".getUsuario()."')";
		query($sql);
	}
}

/**
 * Apaga um diretório e seu conteudo
 *
 * @param string $dir
 * @return boolean
 */
function apagaDiretorio($dir) {
	if(is_dir($dir)){
		$iterator     = new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS);
		$rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
		
		foreach($rec_iterator as $file){
			$file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
		}
		
		rmdir($dir);
		
		if(is_dir($dir)){
			log::gravaLog('exclusao_direotrio','Erro ao excluir os arquivos! '.$dir);
			return false;
		}else{
			return true;
		}
	}else{
		log::gravaLog('exclusao_direotrio', 'Não foi possível excluir o diretorio, pois não é um direorio: '.$dir);
		return false;
	}
}

/**
 * Mescla os parametros
 *
 * @param array $chaves - Chaves/indices
 * @param array $padrao	- Valores padrões
 * @param array $param	- Parametros enviados
 */
function mesclaParametros($padrao, $param){
	$ret = array();
	if(is_array($padrao) && !empty($padrao)){
		$ret = $padrao;
		foreach ($padrao as $chave => $valor){
			$ret[$chave] = $valor;
			if(isset($param[$chave])){
				$ret[$chave] = $param[$chave];
			}
		}
	}
	//Para parametros que não existem no padrão
	foreach ($param as $chave => $valor){
		if(!isset($ret[$chave])){
			$ret[$chave] = $param[$chave];
		}
	}
	
	return $ret;
}

/**
 * Ajusta um valor 999.999,99 para 999999.99
 *
 * @param string $valor
 * @return mixed
 */
function ajustaValor($valor){
	$valor = str_replace('.', '', $valor);
	$valor = str_replace(',', '.', $valor);
	return $valor;
}

/*
 * Formata o nr enviado para reais (1.000,00)
 */
function formataReais($nr,$casas = 2, $simbolo = false){
	$nr = empty($nr) ? 0 : $nr;
	$ret = number_format($nr, $casas, ',', '.');
	if($simbolo)
		$ret = "R$ ".$ret;
		return $ret;
}

/**
 * Preenche uma string com um determinado caracter até um tamanha determinado
 *
 * @author	Alexandre Thiel
 * @access	public
 * @param	string	$str	String a ser preenchida
 * @param	int		$tam	Tamnho que a string deve ter
 * @param	string	$caract	Caracter usado para preencher a string
 * @param	string	$pos	Posição da inclusão dos caracteres I-inicio F-fim
 * @return	mixed	Valor do parametro
 *
 * @version 0.01
 */
function preenche($str, $tam, $caract, $pos = 'I'){
	while(strlen($str) < $tam){
		if(strtoupper($pos) == 'I' || strtoupper($pos) == 'E' || strtoupper($pos) == 'ESQUERDA' || strtoupper($pos) == 'INICIO'){
			//echo "$tam-$caract-$pos - Ini $str<br>\n";
			$str = "$caract".$str;
		}else{
			//echo "$tam-$caract-$pos - Fim $str<br>\n";
			$str = $str."$caract";
		}
	}
	
	if(strlen($str) > $tam){
		$str = substr($str, 0, $tam);
	}
	return $str;
}

function verificarAcaoSys016($user, $acao){
    $ret = false;
    $sql = "select * from sys016 where usuario = '$user' and item = '$acao'";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        $ret = true;
    }
    return $ret;
}

/**
 * limpa uma string deixando somente os números
 *
 * @param string $numero
 * @return string
 */
function limparNumero($numero){
    return preg_replace('/[^0-9]/', '', $numero);
}
