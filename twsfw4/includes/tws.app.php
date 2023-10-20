<?php
/*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

#[\AllowDynamicProperties]
class app{
	
	//ID do usuário na SYS001
	var $_userID;
	
	//Variaveis
	var $_variaveis;
	
	//Dados de usuario
	var $_usuario = [];
	
	//Avatar do usuario
	var $_avatar = '';
	
	//Linguagem do usuário
	var $_userLingua;
	
	//HTML do Menu
	private $_menu = '';
	
	function __construct(){
		$this->_userID = 0;
	}
	
	public function logado(){
		return $this->_userID <= 0 ? false : true;
	}
	
	public function setaDadosUsuario($dados){
		global $config;
		
		$this->_userID	= $dados['id'];
		$this->_user	= $dados['user'];
		$this->_userLingua = $dados['lingua'] ?? '';
		foreach ($dados as $chave => $valor) {
			if ($chave != "senha"){
				$this->_usuario[$chave] = $valor;
			}
		}
		
		$avatar = $config['baseS3'].'imagens/avatares/'.$dados['id'].'.jpg';
		$avatarLink = $config['imagens'].'avatares/'.$dados['id'].'.jpg';
		if(!file_exists($avatar)){
			$avatar = $config['baseS3'].'imagens/avatares/'.$dados['id'].'.png';
			$avatarLink = $config['imagens'].'avatares/'.$dados['id'].'.png';
			if(!file_exists($avatar)){
				$avatar = $config['baseS3'].'imagens/avatares/'.$dados['id'].'.gif';
				$avatarLink = $config['imagens'].'avatares/'.$dados['id'].'.gif';
				if(!file_exists($avatar)){
					$avatarLink = $config['imagens'].'avatares/avatarGenerico.jpg';
				}
			}
		}
		
		$this->_avatar = $avatarLink;
	}
	
	public function getMenu(){
		global $config;
		if(empty($this->_menu) || !$config['sidebar_cache']){
			
			$menu = new menu();
			$this->_menu = $menu;
		}
		
		return $this->_menu;
	}
}