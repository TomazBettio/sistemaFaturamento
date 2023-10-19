<?php
/*
 * Data Criacao 02/01/2019
 * Autor: TWS - Alexandre
 *
 * Descricao: Cria uma tela para fazer a manutenção de texto com ou sem parãmetros
 * 
 * Alterações:
 * 				19/01/2021 - Thiel - Ajustado para utilizar o AdminLTE 4
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class editor01{

	//Indica se deve mostar a aba de varáveis/explicação
	private $_mostraVariaveis;
	
	//Titulo do processo
	private $_titulo;
	
	//Titulo aba variaveis
	private $_tituloVariaveis;
	
	//ID
	private $_editorID;
	
	//Nome do form
	private $_editorNome;
	
	//Conteudo (texto) sem substituição de valores
	private $_conteudo;
	
	//Variaveis (nome - sem @@ - , e descrição)
	private $_variaveis;
	
	//Link se o botão cancelar for pressionado 
	private $_linkCancela;
	
	//IDs dos editores
	private $_editores;
	
	//Títulos dos editores
	private $_editoresTitulo;
	
	// Indica se as variáveis devem aparecer tem todos os editores (true) ou só no promeiro (false)
	private $_variaveisIndividuais;
	
	function __construct($param){
		
		$this->_editores 		= verificaParametro($param, 'editores', array('editorID'));
		$this->_mostraVariaveis = verificaParametro($param, 'mostraVariaveis', true); 
		$this->_titulo 			= verificaParametro($param, 'titulo', '');
		$this->_tituloVariaveis = verificaParametro($param, 'tituloVariaveis', 'Variáveis');
		$this->_editorID 		= verificaParametro($param, 'editorID', 'editorID');
		$this->_editorNome 		= verificaParametro($param, 'editorNome', 'editorNome');
		$this->_variaveis 		= verificaParametro($param, 'variaveis', array());
		$this->_linkCancela 	= verificaParametro($param, 'linkCancela', getLink().'index');
		
		$this->_variaveisIndividuais = verificaParametro($param, 'variaveisIndividuais', true);
	}

	function __toString(){
		global $nl;
		$ret = '';
		if(empty($this->_titulo)){
			$this->_titulo = 'Editor';
		}

		if($this->_variaveisIndividuais === true){
			foreach ($this->_editores as $editor){
				$ret .= $this->getEditor($editor);
			}
		}else{
			$editores = '';
			$variaveis = '';
			foreach ($this->_editores as $editor){
				$editores .= $this->getFormEditor($editor);
				if(empty($variaveis)){
					$variaveis = $this->getFormVariaveis($editor);
				}
			}
			
			$ret .= '<div class="row">'.$nl;
			$ret .= '	<div  class="col-md-9">'.$editores.'</div>'.$nl;
			$ret .= '	<div  class="col-md-3">'.$variaveis.'</div>'.$nl;
			$ret .= '</div>'.$nl;
		}
		
		
		$param = array();
		$param['acao'] = getLink().'gravarEditor';
		$param['nome'] = 'formEditor';
		//$param['onsubmit'] = 'verificaForm';
		$ret = formbase01::form($param, $ret);
		
		$param = [];
		$param['titulo'] = $this->_titulo;
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	function getEditor($id){
		global $nl;
		$ret = '';
		$editor = '';
		$variaveis = '';
		
		
		$colunas1 = $this->_mostraVariaveis ? 9 : 12;
		$colunas2 = $this->_mostraVariaveis ? 3 : 0;
		
		$editor = $this->getFormEditor($id);
		$variaveis = $this->_mostraVariaveis ? $this->getFormVariaveis($id) : '';
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-'.$colunas1.'">'.$editor.'</div>'.$nl;
		if($this->_mostraVariaveis){
			$ret .= '	<div  class="col-md-'.$colunas2.'">'.$variaveis.'</div>'.$nl;
		}
		$ret .= '</div>'.$nl;
		
			
		
		return $ret;
	}
	
	private function getFormEditor($id){
		$ret = '';
		
		$param = array();
		$param['nome'] 	= $id;
		$param['id'] 	= $id;
		$param['valor'] = $this->_conteudo[$id];
		$param['linhas']= 15;
		//$param[''] = '';
		
		$ret .= formbase01::formEditor($param);
		
		$param = array();
		$p = [];
		$p['onclick'] = "setLocation('".$this->_linkCancela."')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Cancelar';
		$param['botoesTitulo'][] = $p;
		
		$p = [];
//		$p['icone'] 	= 'fa-arrow-right';
		$p['cor'] 		= 'primary';
		$p['texto'] 	= 'Salvar';
		$p['tamanho'] = 'pequeno';
		$p['id'] 		= 'bt_salvar';
		$p["onclick"]	= "$('#formEditor').submit()";
		$param['botoesTitulo'][] = $p;
		
		$param['class'] = 'card card-danger card-outline';
		
		$ret = addCard($this->_editoresTitulo[$id], $ret, $param);
		
		return $ret;
	}
	
	//--------------------------------------------------------------------------------- GET ---------------------------------------------
	
	private function getFormVariaveis(){
		global $nl;
		$ret = '';
		
		$ret .= '<table width="100%" border="0" cellpadding="4" cellspacing="4">'.$nl;
		foreach ($this->_variaveis as $variavel => $descricao){
			$ret .= '	<tr>'.$nl;
			$ret .= '		<td valign="top"><b>@@'.$variavel.'</b>&nbsp;&nbsp;</td>'.$nl;
			$ret .= '		<td valign="top">'.$descricao.'</td>'.$nl;
			$ret .= '	</tr>'.$nl;
		}
		$ret .= '</table>'.$nl;

		$param = [];
		$param['titulo'] = $this->_tituloVariaveis;
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	//--------------------------------------------------------------------------------- SET ---------------------------------------------
	
	function setConteudo($conteudo, $id = 'editorID'){
		$this->_conteudo[$id] = $conteudo;
	}
	
	function setLinkCancela($link){
		if(!empty($link)){
			$this->_linkCancela = $link;
		}
	}
	
	function setTituloEditor($titulo, $id = 'editorID'){
		$this->_editoresTitulo[$id] = $titulo;
	}
	
	//--------------------------------------------------------------------------------- PROCESSOS ----------------------------------------
	
	function mescla($valores){
		
	}
	
	//------------------------------------------------------------------------------------ UTEIS ------------------------------------
	
}