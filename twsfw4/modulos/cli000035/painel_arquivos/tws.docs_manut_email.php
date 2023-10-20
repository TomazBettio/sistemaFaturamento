<?php
/*
 * Data Criacao: 22/08/18
 * Autor: TWS
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class docs_manut_email{
	var $funcoes_publicas = array(
		'index' 		=> true,
		'gravarEditor'	=> true,
	);
	
	//Programa
	private $_programa;
	
	//sys020
	private $_sys020;
	
	//Parametro SYS020
	private $_parametro;
	
	//Variáveis possíveis
	private $_variaveis;
	
	//Editor
	private $_editor;
	
	function __construct(){
		$this->_programa = get_class($this);
		$this->_sys020 = new sys020();
		$this->_parametro1 = 'DOCS_EMAIL';
		$this->_parametro2 = 'DOCS_EMAIL_REAB';
		
		$this->_variaveis = array(
			'razao' => 'Razão Social do Cliente',
			'contrato' => 'Nr. do contrato',
			'login' => 'Usuário para acessar o portal',
			'senha' => 'Senha inicial',
		);
		
		$param = array();
		$param['titulo'] = 'Manutenção de Emails';
		$param['editores'] = ['email', 'email_reab'];
		$param['variaveis'] = $this->_variaveis;
		$param['variaveisIndividuais'] = false;
		
		$this->_editor = new editor01($param);
		
		$this->_editor->setTituloEditor('Manutenção Email Inicial', 'email');
		$this->_editor->setTituloEditor('Manutenção Email Reabertura', 'email_reab');
		
		if(false){
			$this->adicionaParametros();
		}
	}
	
	
	public function index(){
		$ret = '';
		
		$conteudo1 = $this->_sys020->getParametroValor($this->_programa, $this->_parametro1);
		$conteudo2 = $this->_sys020->getParametroValor($this->_programa, $this->_parametro2);
		
		$this->_editor->setConteudo($conteudo1, 'email');
		$this->_editor->setConteudo($conteudo2, 'email_reab');
		
		$ret .= $this->_editor;
		
		return $ret;
	}
	
	public function gravarEditor(){
		$texto1 = getParam($_POST, 'email');
		$texto2 = getParam($_POST, 'email_reab');
		
		$this->_sys020->atualiza($this->_programa, $this->_parametro1, $texto1);
		$this->_sys020->atualiza($this->_programa, $this->_parametro2, $texto2);
		
		addPortalMensagem('Texto alterado com sucesso!');
		
		redireciona();
	}
	
	private function adicionaParametros(){
		$param = [];
		$param['programa'] = $this->_programa;
		$param['parametro'] = 'DOCS_EMAIL';
		$param['tipo'] = 'TA';
		$param['linhas'] = 3;
		$param['config'] = '';
		$param['descricao'] = 'Modelo email';
		$param['valor'] = '';
		$param['help'] = 'Modelo email enviado clientes Monofásico Portal DOCs, enviado ao gerar uma folha rosto.';
		
		$this->_sys020->inclui($param);
		
		
		$param = [];
		$param['programa'] = $this->_programa;
		$param['parametro'] = 'DOCS_EMAIL_REAB';
		$param['tipo'] = 'TA';
		$param['linhas'] = 3;
		$param['config'] = '';
		$param['descricao'] = 'Modelo email reabertura';
		$param['valor'] = '';
		$param['help'] = 'Modelo email enviado clientes Monofásico Portal DOCs, enviado reabrir o envio de arquivos.';
		
		$this->_sys020->inclui($param);
	}
	
}