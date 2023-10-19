<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class cad_recursos extends cad01{
	function __construct(){
		$param = [];
		parent::__construct('cad_recursos', 'Cadastro de Recursos', $param);
	}
}