<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_tarefa extends cad01{
    function __construct(){
        $param = array();
        parent::__construct('crm_tarefa', $param);
    }
}