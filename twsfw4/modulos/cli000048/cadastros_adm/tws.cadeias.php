<?php
class cadeias extends cad01{
    public function __construct(){
        parent::__construct('pmp_cadeia', []);
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