<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class crm_categorias extends cad01{
    function __construct(){
        $param = array();
        parent::__construct('crm_categorias', $param);
    }
}