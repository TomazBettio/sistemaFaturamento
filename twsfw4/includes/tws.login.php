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
	}
	
	private function loginIntranet($username, $password){
		global $app;
		$sql = "SELECT * FROM sys001 WHERE user = '$username' AND ativo='S'";
		//echo "SQL: $sql <br>\n";die();
		$info = query($sql);
		
		if ((count($info)==1) AND ($info[0]['id'] > 0) AND ($info[0]['senha'] != "")) {
			$dados = $info[0];
			$dbsenha = $dados['senha'];
			//print_r($dados);die();
			if ( $dbsenha == $password){
				$app->setaDadosUsuario($dados);
				log::logAcesso("$username acessou com sucesso", 1);
				return true;
			}else{
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
	}
}