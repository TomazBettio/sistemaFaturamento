<?php
class colab extends cad01{
    public function __construct(){
        parent::__construct('pmp_colaborador', []);
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

function getListaUsuarios(){
    $ret = [];
    $sql = "SELECT user, nome FROM sys001 WHERE ativo = 'S' 
            AND user NOT IN (SELECT user FROM pmp_colaborador WHERE ativo = 'S') 
            ORDER BY nome";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        foreach ($rows as $row){
            $ret[] = [$row['user'], $row['nome']];
        }
    }
    return $ret;
}