<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class gestao extends kanboard{
    public function __construct($projeto = ''){
        parent::__construct(10);
    }
}