<?php
class controller_base{
    protected $_tabela;
    protected $_json;
    protected $_campos;
    protected $_lista_campos;
    protected $_campos_chave;
    
    function __construct($tabela, $campos_chave, $json = false){
        $this->_tabela = $tabela;
        $this->_json = $json;
        $this->_campos_chave = $campos_chave;
        $this->_valores_permitidos = array();
        $this->configurarCampos();
    }
    
    protected function configurarCampos(){
        
    }
    
    public function getTodosRegistros(){
        $ret = '';
        $sql = "select * from {$this->_tabela}";
        $rows = query($sql);
        $ret = $this->tranformarSaida($rows);
        return $ret;
    }
    
    public function getRegistroEspecifico($id){
        $ret = '';
        if(!is_array($this->_campos_chave)){
            $dados = $this->pesquisaSimples(array($this->_campos_chave => $id));
        }
        else{
            $where = array();
            foreach ($this->_campos_chave as $campo){
                $where[$campo] = $id[$campo];
            }
            $dados = $this->pesquisaSimples($where);
        }
        $ret = $this->tranformarSaida($dados, true);
        return $ret;
    }
    
    public function pesquisaSimples($param_where, $campos_retorno = array()){
        $ret = '';
        if(count($campos_retorno) === 0){
            $campos_retorno = '*';
        }
        echo 'p4';
        var_dump($param_where);
        echo 'p5';
        $sql = montaSQL($param_where, $this->_tabela, 'SELECT', '', $campos_retorno);
        echo $sql;
        $rows = query($sql);
        $ret = $this->tranformarSaida($rows);
        return $ret;
    }
    
    protected function validarValores(){
        $ret = true;
        foreach ($this->_campos as $campo){
            $ret = $ret && $campo->validarValor();
            /*
            if(!$ret){
                return $ret;
            }
            */
        }
        return $ret;
    }
    
    public function incluir(){
        $ret = false;
        if($this->validarValores()){
            $campos_insert = array();
            $campos_chave = array();
            if(is_array($this->_campos_chave)){
                $campos_chave = $this->_campos_chave;
            }
            else{
                $campos_chave[] = $this->_campos_chave;
            }
            foreach ($this->_campos as $campo){
                if(!in_array($campo->_campo, $campos_chave)){
                    $campos_insert[$campo->_campo] = $campo->getValor();
                }
                
            }
            $sql = montaSQL($campos_insert, $this->_tabela);
            $ret = query($sql);
        }
        return $ret;
    }
    
    public function carregarRegistro($id){
        $dados = $this->getRegistroEspecifico($id);
        foreach ($dados as $campo => $valor){
            $this->_campos[$campo]->setValor($valor);
        }
    }
    
    public function setValorCampo($campo, $valor){
        if(isset($this->_campos[$campo])){
            $this->_campos[$campo]->setValor($valor);
        }
    }
    
    public function editar(){
        $where = array();
        $campos_update = array();
        if(is_array($this->_campos_chave)){
            foreach ($this->_campos_chave as $campo){
                $where[] = "$campo = " . $this->_campos[$campo]->getValor();
            }
            
            foreach ($this->_campos as $campo){
                if(!in_array($campo->_campo, $this->_campos_chave)){
                    $campos_update[$campo->_campo] = $campo->getValor();
                }
            }
        }
        else{
            $where[] = $this->_campos_chave . ' = ' . $this->_campos[$this->_campos_chave]->getValor();
            
            foreach ($this->_campos as $campo){
                if($campo->_campo != $this->_campos_chave){
                    $campos_update[$campo->_campo] = $campo->getValor();
                }
            }
        }
        
        $sql = montaSQL($campos_update, $this->_tabela, 'UPDATE', implode(' and ', $where));
        query($sql);
    }
    
    
    protected function tranformarSaida($dados, $direto = false){
        if($this->_json){
            $ret = '';
        }
        else{
            $ret = array();
        }
        if(is_array($dados) && count($dados) > 0){
            $dados_sem_duplicatas = array();
            foreach ($dados as $d){
                $temp = array();
                foreach ($this->_campos as $c){
                    $temp[$c->_campo] = $d[$c->_campo];
                }
                $dados_sem_duplicatas[] = $temp;
            }
            if($direto && count($dados) === 1){
                $dados_sem_duplicatas = $temp;
            }
            if($this->_json){
                $ret = json_encode($dados_sem_duplicatas);
            }
            else{
                $ret = $dados_sem_duplicatas;
            }
        }
        return $ret;
    }
}