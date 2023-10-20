<?php
class rest_protheus{
    private $_link;
    private $_path = array();
    private $_rest;
    
    public function __construct($link, $usuario = '', $senha = ''){
        $this->_link = $link;
        $this->getPath();
        $this->_rest = new rest_cliente01($this->_link);
        if(!empty($usuario) && !empty($senha)){
            $this->_rest->setHeader('Authorization', 'Basic ' . base64_encode($usuario . ':' . $senha));
        }
    }
    
    public function getMetas($pagina = 1){
        $ret = '';
        $path = $this->_path['metas']['GET']['all'] . "?page=$pagina";
        $param = array();
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getAllMetas(){
        $ret = array();
        $i = 0;
        $ret_rest = '';
        do{
            $i++;
            $ret_rest = $this->getMetas($i);
            if($ret_rest !== false){
                $ret = array_merge($ret, $ret_rest['items'] ?? array());
            }
        }
        while($ret_rest !== false);
        return $ret;
    }
    
    public function incluirMetaNova($dados){
        $ret = '';
        $path = $this->_path['metas']['POST']['incluir'];
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', $param);
        return $ret;
    }
    
    public function incluirSubMeta($id, $dados){
        $ret = '';
        $path = $this->_path['metas']['POST']['incluirSub'];
        $path = str_replace('{id}', $id, $path);
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', $param);
        return $ret;
    }
    
    public function getLinhas($pagina = 1){
        $ret = '';
        $path = $this->_path['linhas']['GET']['all'] . "?page=$pagina";
        $param = array();
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getAllLinhas(){
        $ret = array();
        $i = 0;
        $ret_rest = '';
        do{
            $i++;
            $ret_rest = $this->getLinhas($i);
            if($ret_rest !== false){
                $ret = array_merge($ret, $ret_rest['items'] ?? array());
            }
        }
        while($ret_rest !== false);
        return $ret;
    }
    
    public function getVendedores($pagina = 1){
        $ret = '';
        $path = $this->_path['vendedores']['GET']['all'] . "?page=$pagina";
        $param = array();
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getAllVendedores(){
        $ret = array();
        $i = 0;
        $ret_rest = '';
        do{
            $i++;
            $ret_rest = $this->getVendedores($i);
            if($ret_rest !== false){
                $ret = array_merge($ret, $ret_rest['items'] ?? array());
            }
        }
        while($ret_rest !== false);
        return $ret;
    }
    
    public function atualizarSubMeta($id, $sub, $dados){
        $ret = '';
        $path = $this->_path['metas']['PUT']['alterarSub'];
        $path = str_replace('{id}', $id, $path);
        $path = str_replace('{sub}', $sub, $path);
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'PUT', $param);
        return $ret;
    }
    
    public function getSubMeta($id, $sub){
        $ret = '';
        $path = $this->_path['metas']['GET']['sub'];
        $path = str_replace('{id}', $id, $path);
        $path = str_replace('{sub}', $sub, $path);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getClientes($pagina = 1){
        $ret = '';
        $path = $this->_path['clientes']['GET']['all'] . "/1?page=$pagina&fields=code";
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getAllClientes(){
        $ret = array();
        $i = 0;
        $ret_rest = '';
        set_time_limit(0);
        do{
            $i++;
            $ret_rest = $this->getClientes($i);
            if($ret_rest !== false){
                $ret = array_merge($ret, $ret_rest['items'] ?? array());
            }
        }
        while($ret_rest !== false);
        return $ret;
    }
    
    public function getListaPrecos($lista){
        $ret = '';
        $path = $this->_path['precos']['GET']['lista'];
        $path = str_replace('{lista}', $lista, $path);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getProdutos($pagina = ''){
        $ret = '';
        $path = $this->_path['produtos']['GET']['all'];
        if(!empty($pagina)){
            $path .= "?page=$pagina";
        }
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getAllProdutos(){
        $ret = array();
        $i = 0;
        $ret_rest = '';
        set_time_limit(0);
        do{
            $i++;
            $ret_rest = $this->getProdutos($i);
            if($ret_rest !== false){
                $ret = array_merge($ret, $ret_rest['items'] ?? array());
            }
        }
        while($ret_rest !== false);
        return $ret;
    }
    
    public function criarOrcamento($dados){
        $ret = '';
        $temp = json_decode($dados, true);
        if(!empty($temp['sellerCode'])){
            $path = $this->_path['orcamento']['POST']['criar'];
            $this->_rest->setPostData($dados);
            $param = array();
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'POST', $param, false, true);
        }
        return $ret;
    }
    
    public function alterarStatusOrcamento($dados){
        $ret = '';
        $status_validos = ['A', 'B', 'C', 'D', 'F'];
        if(isset($dados['orcamento']) && isset($dados['status']) && in_array($dados['status'], $status_validos)){
            $path = $this->_path['gf']['POST']['alterarStatusOrcamento'];
            $param = array();
            $this->_rest->setHeader('Content-Type', 'application/json');
            $this->_rest->setPostData($dados);
            $ret = $this->_rest->executa($path, 'POST', $param, true, true);
        }
        return $ret;
    }
    
    public function confirmarOrcamento($dados){
        $ret = '';
        if(isset($dados['orcamento'])){
            $path = $this->_path['gf']['POST']['confirmarOrcamento'];
            $param = array();
            $this->_rest->setHeader('Content-Type', 'application/json');
            $this->_rest->setPostData($dados);
            $ret = $this->_rest->executa($path, 'POST', $param, true, true);
        }
        return $ret;
    }
    
    public function alterarOrcamento($dados){
        $ret = '';
        $path = $this->_path['gf']['POST']['alterarOrcamento'];
        $param = array();
        $this->_rest->setPostData($dados);
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', $param, true, true);
        return $ret;
    }
    
    public function excluirOrcamento($id){
        $ret = '';
        $path = $this->_path['gf']['DELETE']['excluirOrcamento'];
        $path = str_replace('{id}', $id, $path);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'DELETE', $param, true, true);
        return $ret;
    }
    
    private function getPath($versao = ''){
        switch ($versao) {
            case 1.3:
                break;
                
            default:
                $this->_path['metas']['GET']['all']	             = '/api/crm/v1/salestargets';
                $this->_path['metas']['POST']['incluir']	     = '/api/crm/v1/salestargets';
                $this->_path['metas']['POST']['incluirSub']	     = '/api/crm/v1/salestargets/{id}/ListOfsalestargets';
                
                $this->_path['metas']['GET']['sub']	             = '/api/crm/v1/salestargets/{id}/ListOfsalestargets' . '/{sub}';
                
                $this->_path['metas']['PUT']['alterarSub']	     = '/api/crm/v1/salestargets/{id}/ListOfsalestargets/{sub}';
                
                $this->_path['linhas']['GET']['all']	         = '/api/ctb/v1/classvalue';
                $this->_path['vendedores']['GET']['all']	     = '/api/crm/v2/seller';
                
                $this->_path['clientes']['GET']['all']           = '/api/crm/v1/customerVendor';
                
                $this->_path['precos']['GET']['lista'] = '/api/supply/v2/PriceListHeaderItems/{lista}';
                
                $this->_path['produtos']['GET']['all'] = '/api/retail/v1/RetailItem';
                
                $this->_path['orcamento']['POST']['criar'] = '/mpfw010';
                
                /*
                $this->_path['gf']['POST']['alterarStatusOrcamento'] = '/api/gf/v1/gforcamento/alterarstatus';
                $this->_path['gf']['POST']['confirmarOrcamento'] = '/api/gf/v1/gforcamento/confirmarorcamento';
                $this->_path['gf']['DELETE']['excluirOrcamento'] =   '/api/gf/v1/gforcamento/deletarorcamento?ID={id}';
                */
                
                $this->_path['gf']['POST']['alterarStatusOrcamento'] = '/api/v1/gforcamento/alterarstatus';
                $this->_path['gf']['POST']['confirmarOrcamento'] = '/api/v1/gforcamento/confirmarorcamento';
                $this->_path['gf']['POST']['alterarOrcamento'] = '/api/v1/gforcamento/alterarorcamento';
                
                $this->_path['gf']['DELETE']['excluirOrcamento'] =   '/api/v1/gforcamento/deletarorcamento?ID={id}';
                break;
        }
    }
}