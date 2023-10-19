<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class mgt_cfop extends cad01
{
  function __construct()
  {
    $param = [];
    parent::__construct('mgt_cfop', 'Cadastro CFOP', $param);
  }
}
