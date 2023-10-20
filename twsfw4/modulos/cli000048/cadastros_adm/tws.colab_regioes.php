<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class colab_regioes extends cad01{
	function __construct(){
		$param = [];
		parent::__construct('pmp_colab_regiao', $param);
		
		$this->_query_dados = "SELECT 
		                          pmp_colab_regiao.id as id, 
		                          sys001.nome as colaborador, 
		                          pmp_colab_regiao.regiao as regiao
                                FROM 
                                    pmp_colab_regiao 
                                    JOIN 
                                        (sys001 
                                        JOIN pmp_colaborador 
                                        ON sys001.user = pmp_colaborador.user) 
                                    ON pmp_colab_regiao.colaborador = pmp_colaborador.id
                                WHERE pmp_colab_regiao.ativo = 'S'";
                                
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

function getListaColaboradores(){
    $ret = [['','']];
    $sql = "select pmp_colaborador.id as id, nome from sys001 join pmp_colaborador using (user) where pmp_colaborador.ativo = 'S' ORDER BY nome";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        foreach ($rows as $row){
            $ret[] = [$row['id'], $row['nome']];
        }
    }
    return $ret;
}