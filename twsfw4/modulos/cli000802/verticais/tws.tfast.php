<?php
/*
 * Data Criação: 09/08/2023
 * Autor: BCS
 *
 * Teste FastAPI pegar headers
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class tfast{
    
    private $_rest;
    
    var $funcoes_publicas = [
        'index' => true,
    ];
    
    public function __construct()
    {
        $this->_rest = new rest_fast('http://127.0.0.1:7000/headers');
    }
    
    public function index()
    {
        $ret = '';
        print_r($this->_rest->mandaReq());
        return $ret;
    }
}
    