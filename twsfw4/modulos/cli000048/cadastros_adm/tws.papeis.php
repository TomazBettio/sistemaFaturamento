<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class papeis extends cad01{
	function __construct(){
		$param = [];
		parent::__construct('pmp_papel',$param);
	}
	
	public function salvar($id = 0, $dados = array(), $acao = '', $redireciona = true){
	    parent::salvar($id, $dados, $acao, false);
	    
	    $acao = !empty($acao) ? $acao : getParam($_GET, 'acao', $acao);
	    if($acao == 'I'){
	        gravarAtualizacao($this->_tabela, $this->_ultimoIdIncluido, $acao);
	    }elseif($acao == 'E'){
	        $id = $id !== 0 ? $id : base64_decode(getParam($_GET, 'id', 0));
	        gravarAtualizacao($this->_tabela, $id, $acao);
	    }
	    redireciona();
	    
	}
	
	public function excluir($redireciona = true){
	    parent::excluir(false);
	    $id = base64_decode(getParam($_GET, 'id', 0));
	    gravarAtualizacao($this->_tabela, $id, 'E');
	    redireciona();
	}
}