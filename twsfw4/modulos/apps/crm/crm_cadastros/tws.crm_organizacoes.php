<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_organizacoes extends cad01{
    function __construct(){
        $param = array();
        parent::__construct('crm_organizacoes', $param);
    }
}