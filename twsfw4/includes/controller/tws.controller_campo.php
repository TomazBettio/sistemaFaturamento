<?php
class controller_campo{
    private $_nulo;
    private $_tamanho;
    private $_campo;
    private $_valor;
    private $_tipo;
    private $_valor_padrao;
    private $_valores_permitidos;
    
    public function __construct($campo, $nulo, $tipo, $tamanho = 99, $valor_padrao = null){
        $this->_campo = $campo;
        $this->_nulo = $nulo;
        $this->_tamanho = $tamanho;
        $this->_valor_padrao = $valor_padrao;
        $this->_tipo = $tipo;
        $this->_valor = null;
        $this->_valores_permitidos = array();
    }
    
    public function __get($campo){
        switch ($campo){
            case '_campo':
                return $this->_campo;
                break;
            default:
                return '';
                break;
        }
    }
    
    public function setValoresPermitidos($lista){
        if(is_array($lista)){
            $this->_valores_permitidos = $lista;
        }
    }
    
    public function setValor($valor){
        if((count($this->_valores_permitidos) == 0 || in_array($valor, $this->_valores_permitidos)) && strlen(strval($valor)) <= $this->_tamanho){
            $this->_valor = $valor;
        }
        else{
            addPortalMensagem('não foi possivel');
        }
    }
    
    public function validarValor(){
        $ret = true;
        if(!$this->_nulo && is_null($this->_valor) && is_null($this->_valor_padrao)){
            $ret = false;
        }
        return $ret;
    }
    
    public function getValor(){
        $ret = null;
        
        //pegando o valor
        $ret = $this->_valor ?? $this->_valor_padrao;
        
        //formatando o valor
        if($ret != null){
            /*
            if($this->_tipo == 'string'){
                
            }
            */
        }
        else{
            $ret = 'NULL';
        }
        return $ret;
    }
}