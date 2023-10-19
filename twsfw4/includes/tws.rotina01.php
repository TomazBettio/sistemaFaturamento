<?php
/*
 * Data Criação: 22/08/16
 * Autor: Thiel
 *
 * Versão: 1
 * 
 * Alterações:
 * 				09/10/19 - Thiel - Inclusão de TextoFial, a ser incluído após o conteudo
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class rotina01{
	
	//Titulo
	private $_titulo = '';
	
	//Botões do título
	private $_botoesTitulo = array();
	
	//Link
	private $_link;
	
	//Programa
	private $_programa;
	
	//Indica se utiliza a classe de filtro
	private $_usaFiltro;
	
	//Classe Filtro
	private $_filtro;
	
	//sys020
	private $_sys020;
	
	private $_textoFinal = '';
	
	function __construct($parametros){
		$this->_sys020 = new sys020();
		
		$botaoConfirmacao 		= $parametros['botaoConfiguracao']	?? false;
		$this->_titulo 			= $parametros['titulo']				?? '';
		$this->_botoesTitulo 	= $parametros['botoesTitulo']		?? [];
		$this->_link			= $parametros['link']				?? getLink();
		$this->_conteudo 		= $parametros['conteudo']			?? '';
		$this->_programa		= $parametros['programa']			?? getModulo().'.'.getClasse();
		$this->_usaFiltro		= $parametros['filtro']				?? false;
		
		if($this->_usaFiltro === true){
			$paramFiltro = array();
			$paramFiltro['layout'] = 'basico';
			$paramFiltro['tamanho'] = 12;
			$paramFiltro['posicaoBotoes'] = FORMFILTRO_BT_POS;
			$paramFiltro['titulo'] = 'Filtro';
			$this->_filtro = new formfiltro01($this->_programa, $paramFiltro);
			
			$botao = array();
			$botao['onclick']= "$('#formFiltro').toggle();";
			$botao['texto']	= 'Filtro';
			$botao['id'] = 'bt_form';
			$botao['icone'] = 'fa-filter';
			$this->addBotaoTitulo($botao);
			
		}
		
		if($botaoConfirmacao === true){
			$botao = array();
			$ponto = $this->verificaPontoFinal($this->_link);
			$botao['onclick']= 'setLocation(\''.$this->_link.$ponto.'index.sysParametros\')';
			$botao['texto']	= 'Configurações';
			$botao['id'] = 'btConfigurar';
			$botao['icone'] = 'fa-cog';
			
			$this->addBotaoTitulo($botao);
		}
	}
	
	function __toString(){
		$ret = '';
		$operacao = getOperacao();
		
		
		if($operacao == 'sysParametros'){
			$ret .= $this->_sys020->formulario($this->_programa, $this->_titulo);
		}elseif($operacao == 'sysParametrosGravar'){
			$ret .= $this->_sys020->gravaFormulario($this->_programa);
			addPortalMensagem('Configurações alteradas com sucesso!');
			$ret .= $this->index();
		}else{
			$ret .= $this->index();
		}
		
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------------------------ Form -----------------------------
	
	private function index(){
		$ret = '';
		$titulo = $this->_titulo;
		$param = array();
		if(count($this->_botoesTitulo) > 0){
			foreach ($this->_botoesTitulo as $botao){
				$param['botoesTitulo'][] = $botao;
			}
		}
		
		if($this->_usaFiltro === true){
			$ret .= $this->_filtro;
		}
		
		$ret .= $this->_conteudo;
		
		if(!empty($this->_textoFinal)){
			$ret .= $this->_textoFinal;
		}
		
		$param['titulo'] = $titulo;
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------------------------ GETs ------------------------------
	
	function isPrimeiro(){
		return $this->_filtro->isPrimeiro();
	}
	
	function getFiltro(){
		return $this->_filtro->getFiltro();
	}
	
	function getParametros(){
		$ret = array();
		
		$parametros = $this->_sys020->getParametros($this->_programa);
		if(count($parametros) > 0){
			foreach ($parametros as $parametro){
				$ret[$parametro['parametro']] = $parametro['valor'];
			}
		}
		
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------------------------ SETs ------------------------------
	
	function setConteudo($conteudo){
		$this->_conteudo .= $conteudo;
	}
	
	function setTitulo($titulo){
		$this->_titulo = $titulo;
	}
	
	function addBotaoTitulo($botao, $link = ''){
		if(is_array($botao)){
			$this->_botoesTitulo[] = $botao;
		}elseif(strtoupper($botao) == 'CANCELAR'){
			if(empty(trim($link))){
				$link = $this->_link.'index';
			}
			//Inclui o botão cancelar
			$p = array();
			$p['onclick'] = "setLocation('".$link."')";
			$p['tamanho'] = 'pequeno';
			$p['cor'] = 'danger';
			$p['texto'] = 'Cancelar';
			$this->_botoesTitulo[] = $p;
		}
	}
	
	public function setTextoFinal($texto){
		if(!empty(trim($texto))){
			$this->_textoFinal = $texto;
		}
	}
	
	//------------------------------------------------------------------------------------------------------------ UTEIS ------------------------------
	private function verificaPontoFinal($string){
		$ret = '';
		if(substr($string, -1, 1) != '.'){
			$ret = '.';
		}
		
		return $ret;
	}
	
	public function escondeFiltro(){
		addPortalJquery("$('#formFiltro').hide();");
	}
}