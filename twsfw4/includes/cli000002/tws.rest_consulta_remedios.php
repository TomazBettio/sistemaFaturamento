<?php
class rest_consulta_remedios{
    //URL Path
    private $_path = [];
    
    //REST
    private $_rest;
    
    public function __construct($url, $token, $versao = 1){
        $this->_rest = new rest_cliente01($url);
        $this->_rest->setHeader("Authorization", "" . $token);
        
        $this->getPath($versao);
    }
    
    // PRODUTOS ================================================================================================================================
    //==========================================================================================================================================
    public function cadastrarPrecoEstoque($dados, $sku){
        $ret = '';
        $path = $this->_path['produtos']['PATCH']['preco_estoque'];
        $path = str_replace('{sku}', $sku, $path);
        $param = array();
        $this->_rest->setPostData($dados);
        $this->_rest->setHeader('Content-Type', 'application/json');
        $resultado = $this->_rest->executa($path, 'PATCH', $param, false, true);
        $ret = $this->montaMensagemErro($resultado);
        
        return $ret;
    }
    
    public function cadastrarDimensoes($dados, $sku){
        $ret = '';
        $path = $this->_path['produtos']['PATCH']['dimenssoes'];
        $path = str_replace('{sku}', $sku, $path);
        $param = array();
        $this->_rest->setPostData($dados);
        $this->_rest->setHeader('Content-Type', 'application/json');
        $resultado = $this->_rest->executa($path, 'PATCH', $param, false, true);
        $ret = $this->montaMensagemErro($resultado);
        
        return $ret;
    }
    
    private function montaMensagemErro($resultado){
        $ret = '';
        
        if(is_array($resultado)){
            //deu tudo certo
            $ret = true;
        }
        elseif(strpos($resultado, 'product') !== false){
            //deu errado e não existe produto
            $ret = 'produto';
        }
        else{
            //algum outro erro
            $ret = false;
        }
        
        return $ret;
    }
    
    public function pesquisarProduto($sku){
        $ret = '';
        $path = $this->_path['produtos']['GET']['especifico'];
        $path = str_replace('{sku}', $sku, $path);
        $param = array();
        $ret = $this->_rest->executa($path, 'GET', $param);
        
        return $ret;
    }
    
    
    //------------------------------------------------------------------------------------- UTEIS ---------------------------------------------
    
    private function getPath($versao){
        switch ($versao) {
            case 1.3:
                break;
                
            default:
                $this->_path['produtos']['PATCH']['preco_estoque']	     = '/api/v1/store/products/{sku}/price_stock';
                $this->_path['produtos']['PATCH']['preco_estoque_massa'] = '/api​/v1​/store​/products';
                $this->_path['produtos']['PATCH']['dimenssoes']	         = '/api/v1/store/products/{sku}/dimensions_and_weight';
                
                $this->_path['produtos']['GET']['especifico']	         = '/api/v1/store/products/{sku}';
                $this->_path['produtos']['GET']['todos']	             = '/api​/v1​/store​/products';
                $this->_path['produtos']['GET']['dimensoes']	         = '​/api​/v1​/store​/products​/{sku}​/dimensions_and_weight';
                break;
        }
    }
    
    
}