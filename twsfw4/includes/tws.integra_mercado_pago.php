<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class integra_mercado_pago{
    var $_path = [];
    var $_url = '';
    var $_chave = '';
    var $_rest;
    
    function __construct($chave, $versao = 1){
        $this->_url = 'https://api.mercadopago.com';
        "";
        
        $this->getPath($versao);
        $this->_rest = new rest_cliente01($this->_url);
        $this->_chave = $chave;
        $this->_rest->setHeader('accept', 'application/json');
    }
    
    public function gerarRelatorio($filtro){
        //funciona mas 202 pra ele é erro
        $ret = '';
        $path = $this->_path['relatorio_lib']['criar'];
        //$path = str_replace('{sku}', $sku, $path);
        $param = array();
        $this->_rest->setPostData($filtro);
        $this->_rest->setHeader('content-type', 'application/json');
        $this->_rest->setHeader('accept', 'application/json');
        $this->setHeaderChave();
        $resposta = $this->_rest->executa($path, 'POST', $param);
        $ret = $resposta['id'] ?? false;
        //$this->_rest->unSetAllHeaders();
        
        return $ret;
    }
    
    public function listarTodosRelatorios($listarPorId = false){
        //funciona mas 202 pra ele é erro
        $ret = '';
        $path = $this->_path['relatorio_lib']['listar'];
        $path = str_replace('{chave}', $this->_chave, $path);
        $param = array();
        $this->_rest->setHeader('accept', 'application/json');
        $resposta = $this->_rest->executa($path, 'GET', $param);
        if($listarPorId && is_array($resposta)){
            $ret = array();
            foreach ($resposta as $r){
                $ret[$r['id']] = $r['file_name'];
            }
        }
        else{
            $ret = $resposta;
        }
        
        $this->_rest->unSetAllHeaders();
        
        return $ret;
    }
    
    public function pesquisarRelatorioID($id){
        $ret = '';
        $relatorios = $this->listarTodosRelatorios(true);
        if(is_array($relatorios) && isset($relatorios[$id])){
            $ret =  $relatorios[$id];
        }
        return $ret;
    }
    
    public function pesquisarUltimoRelatorio(){
        $ret = '';
        $relatorios = $this->listarTodosRelatorios();
        if(is_array($relatorios) && isset($relatorios[0])){
            $ret = $relatorios[0]['file_name'];
        }
        return $ret;
    }
    
    public function baixarRelatorio($arquivoMP, $arquivoLocal){
        $ret = false;
        $path = $this->_path['relatorio_lib']['download'];
        $path = str_replace('{arquivo}', $arquivoMP, $path);
        $path = str_replace('{chave}', $this->_chave, $path);
        $url = $this->_url . $path;
        if (file_put_contents($arquivoLocal, file_get_contents($url)))
        {
            $ret = true;
        }
        return $ret;
    }
    
    private function setHeaderChave(){
        $this->_rest->setHeader('Authorization', "Bearer $this->_chave");
    }
    
    private function unSetHeader($header){
        $this->_rest->unsetHeader($header);
    }
    
    private function getPath($versao = 1){
        switch ($versao) {
            case 1:
            default:
                $this->_path['relatorio_lib']['criar']	  = '/v1/account/release_report/';
                $this->_path['relatorio_lib']['listar']   = '/v1/account/release_report/list?access_token={chave}';
                $this->_path['relatorio_lib']['download'] = '/v1/account/release_report/{arquivo}?access_token={chave}';
                break;
        }
    }
}