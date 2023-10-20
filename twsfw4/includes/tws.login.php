<?php
/*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class login{
	
	function __construct(){
		
	}
	
	public function loginPagina($pagina){
		global $config, $app;
		$ok = false;
		
		if (isset($_POST['login'])) {
			$usuario	= addslashes(getParam( $_POST, 'usuario', '' ));
			$senha		= addslashes(getParam( $_POST, 'senha', '' ));
			$ok = true;
		}else{
			include_once $pagina;
			exit;
		}
		
		if($ok){
			switch($config["site"]["login"]){
				case "F": 
					// Facebook
					/*
					 * TODO: Desenvolver login via face
					 */
					break;
				case "P":
					// Integracao Protheus
					/*
					 * TODO: Desenvolver login por integração com usuários Protheus
					 */
					break;
				case "I": 
					// Intranet
					$ok = $this->loginIntranet( $usuario, $senha);
					break;
				case "L": // LDAP
					$ok = $this->loginLDAP( $usuario, $senha);
					break;
				case "WT":
					// WinThor
					$ok = $this->loginWT($usuario, $senha);
					break;
				default:
					//TODO: gravar log de erro
					die("Verificar arquivo de configuracao - Tipo de login.");
					break;
			}
			
			//Login alternativo
			if(!$ok && isset($config["site"]["loginAlt"])){
				switch($config["site"]["loginAlt"]){
					case "F":
						// Facebook
						/*
						 * TODO: Desenvolver login via face
						 */
						break;
					case "P":
						// Integracao Protheus
						/*
						 * TODO: Desenvolver login por integração com usuários Protheus
						 */
						break;
					case "I":
						// Intranet
						$ok = $this->loginIntranet( $usuario, $senha);
						break;
					case "L": // LDAP
						$ok = $this->loginLDAP( $usuario, $senha);
						break;
					case "WT":
						// WinThor
						$ok = $this->loginWT($usuario, $senha);
						break;
					default:
						//TODO: gravar log de erro
						die("Verificar arquivo de configuracao - Tipo de login.");
						break;
				}
			}
		}
		if (!$ok || !$app->logado()) {
			redirect("msg=".urlencode("Falha no Login"));
			die();
		}
	}
	
	private function loginWT($username, $password){
		global $app;
		$username = strtolower($username);
		$username = str_replace('select', '', $username);
		$username = str_replace('update', '', $username);
		$username = str_replace('delete', '', $username);
		$user = strtoupper($username);
		$sql = "SELECT
					MATRICULA,
					NOME,
					NOME_GUERRA,
					SENHABD,
					EMAIL,
					(SELECT DEcrypt(SENHABD,NOME_GUERRA) FROM DUAL) SENHA
				from
					pcempr
				where
					NOME_GUERRA = '$user'
					AND PCEMPR.dt_exclusao IS NULL
					AND SITUACAO = 'A'
					";
		$senha = '';
		$d = [];
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			$senha = $rows[0]['SENHA'];
			$d = $rows[0];
		}
		//print_r($rows);
		//echo "Compara: ".strtoupper($password)." - $senha <br>\n";
		if(count($rows) > 0 && strtoupper($password) == $senha){
			log::logAcesso('WT: '.$username,1);
			$dados['cliente'] = getCliente();
			$dados['id'] = $d['MATRICULA'];
			//$dados['user'] = $d['MATRICULA'];
			$dados['user'] = $username;
			$dados['nome'] = ucfirst($d['NOME']);
			$dados['apelido'] = $d['NOME_GUERRA'];
			$dados['email'] = strtolower($d['EMAIL']);
			//print_r($dados);
			$app->setaDadosUsuario($dados);
			
			//Verifica se está cadastrado na sys001
			$sql = "SELECT * FROM sys001 WHERE user = '".$username."'";
			//echo "$sql <br>\n";
			$rows = query($sql);
			if(count($rows) == 0){
				$apelido = '';
				$apelidoA = explode('.', $dados['apelido']);
				foreach ($apelidoA as $a){
					if(!empty($apelido)){
						$apelido .= ' ';
					}
					$apelido .= ucfirst($a);
				}
				$sql = "INSERT INTO sys001 (user,origem,email,nome,apelido,ativo) VALUES ('".$username."','WT','".$dados['email']."','".$dados['nome']."','".$apelido."','S')";
				query($sql);
				$dados['nivel'] = 500;
			}else{
				$dados['nivel'] = $rows[0]['nivel'];
			}
			$app->setaDadosUsuario($dados);
			return true;
		}else{
			log::logAcesso("Login WT, senha errada: $username - $password",2);
			return false;
		}
		
	}
	
	private function loginIntranet($username, $password){
		global $app;
		$sql = "SELECT * FROM sys001 WHERE user = '$username' AND ativo='S'";
		//echo "SQL: $sql <br>\n";die();
		$info = query($sql);
		if ((count($info)==1) AND ($info[0]['id'] > 0) AND ($info[0]['senha'] != "")) {
		    $senha_db = $info[0]['senha'];
		    // $senha_form = login::criptografarSenha($password);
		    
		    if($senha_db){
		        $sql = "SELECT * FROM sys001 WHERE user = '$username' AND ativo='S'";
                $rows = query($sql);
                $dados = $rows[0];
		        $app->setaDadosUsuario($dados);
		        log::logAcesso("$username acessou com sucesso", 1);
		        return true;
		    }
		    /*
			$dados = $info[0];
			$dbsenha = $dados['senha'];
			//print_r($dados);die();
			if ( $dbsenha == $password){
				$app->setaDadosUsuario($dados);
				log::logAcesso("$username acessou com sucesso", 1);
				return true;
			}*/
			else{
				log::logAcesso("$username erro de senha $password", 2);
				return false;
			}
		}else{
			if (count($info)>1)
				log::logAcesso("$username mais de um usuario cadastrado ou sem senha", 3);
				return false;
		}
		return false;
	}
	
	private function loginLDAP($username, $password) {
		global $config, $app;
		
		/*
		 * Para testes retorna verdadeiro sempre
		 */
		if($config["ldap_server"] == "teste" && $config["ldap_dominio"] == "teste"){
			// Grava o usuario ldap como local (se ainda n�o foi)
			$dados['user'] 	= $username;
			$dados['ativo'] = "S";
			$dados['tipo']	= "U";
			//$cad = ExecMethod2("admin.usuarios_vo.inclui",$dados);
			return true;
		}
		
		if ((strlen($username) >= 3) && (strlen($password) >= 4)) {
			$conecta = ldap_connect($config["ldap_server"]) 
				or log::logAcesso("Não foi possivel conectar ao servidor LDAP: ".$username." - ".$password,2);
			ldap_set_option($conecta, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($conecta, LDAP_OPT_REFERRALS, 0);

			$bind 	 = @ldap_bind($conecta, $username . "@" . $config["ldap_dominio"], $password);
			if($bind){
				log::logAcesso('LDAP conectado: '.$username,1);
				
				//Verifica se está cadastrado na sys001
				$sql = "SELECT * FROM sys001 WHERE user = '$username'";
				$rows = query($sql);
				if(count($rows) == 0){
					#TODO: Pesquisar no direorio o email e nome completo do usuário
					$apelido = '';
					$apelidoA = explode('.', $username);
					foreach ($apelidoA as $a){
						if(!empty($apelido)){
							$apelido .= ' ';
						}
						$apelido .= ucfirst($a);
					}
					$campos = [];
					$campos['user'] 	= $username;
					$campos['origem'] 	= 'LDAP';
					$campos['email'] 	= '';
					$campos['nome'] 	= '';
					$campos['nivel'] 	= '500';
					$campos['tipo'] 	= 'U';
					$campos['apelido'] 	= $apelido;
					$campos['ativo'] 	= 'S';
					$sql = montaSQL($campos, 'sys001');
					query($sql);
				}else{
					//Verifica se o usuário está ATIVO
					if($rows[0]['ativo'] != 'S'){
						log::logAcesso("LDAP login ok, mas usuario não ativo: ".$username." - ".$password,2);
						return false;
					}
				}
				
				$this->setDadosUsuario($username);
				
			}else{
				log::logAcesso("LDAP erro LDAP: ".$username." - ".$password,2);
			}
			
			ldap_unbind($conecta);
			return $bind;
		} else {
			log::logAcesso("Usuario/senha curto: ".$username." - ".$password,2);
			return false;
		}
		
	}
	
	private function setDadosUsuario($username){
		global $app;
		$sql = "SELECT * FROM sys001 WHERE user = '$username' AND ativo='S'";
		$rows = query($sql);
		
		if (isset($rows[0]['user'])) {
			$dados = $rows[0];
			$app->setaDadosUsuario($dados);
		}
	}
	
	static public function gerarPrefixoSenha(){
	    return '0147';
	}
	
	static public function gerarSufixoSenha(){
	    return 'vrnu';
	}
	
	static public function criptografarSenha($senha){
	    $ret = '';
	    $senha = login::gerarPrefixoSenha() . $senha . login::gerarSufixoSenha();
	    $sql = "select SHA('$senha') as senha";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = $rows[0]['senha'];
	    }
	    return $ret;
	}
}