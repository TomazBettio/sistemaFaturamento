<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class programa1
{
    var $funcoes_publicas = array(
		'index' 	=> true,
	);
    public function index(){
        return 'funcionou';

    }
}
