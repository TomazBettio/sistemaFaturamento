<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class vavrcrud extends cad02
{
  function __construct()
  {
    $param = [];
    parent::__construct('marpa_vavrvales', 'Crud VA/VR', $param);
  }
  
}