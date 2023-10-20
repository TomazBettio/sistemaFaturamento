<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
//$config['site']['debug'] = true;

class crm_kanban{
    var $funcoes_publicas = array(
        'index'     => true,
        'ajax'      => true,
    );
    
    public function index(){
        $kanban = new kanban_lite(2);
        return $kanban . '';
    }
    
    public function ajax(){
        $ret = kanban_lite::ajax();
        return $ret;
    }
}