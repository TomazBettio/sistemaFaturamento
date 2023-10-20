<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class rest_translog{
    private $_url = '';
    private $_token = '';
    private $_rest;
    
    public function __construct($token){
        $this->_url = 'https://sistemas.translogtransportes.com.br/gerenciar/api/rest';
        $this->_token = $token;
        $this->getPath();       
        $this->_rest = new rest_cliente01($this->_url);
    }
    
    public function consultarNota($nota, $cnpj, $chave){
        $path = $this->_path['consulta']['POST']['nota'];
        $dados = [
            'token' => $this->_token,
            'numero_documento' => $nota,
            'cnpj_distribuidora' => $cnpj,
            'chave_acesso' => $chave,
        ];
        $this->_rest->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        //$this->_rest->setPostData($dados);
        $param = [];
        foreach ($dados as $chave => $valor){
            $param[] = "$chave=$valor";
        }
        $ret = $this->_rest->executa($path, 'POST', $param, true, true);
        return $ret;
    }
    
    private function getPath($versao = 1){
        switch ($versao) {
            default:
                $this->_path['consulta']['POST']['nota']	    = '/consulta/consultar_nota';
                break;
        }
    }
}