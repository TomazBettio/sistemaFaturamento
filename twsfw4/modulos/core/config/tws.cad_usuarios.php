<?php
/*
 * Data Criacao 28/02/2022
 * Autor: TWS - Emanuel Thiel
 *
 * Descricao:
 *
 * Alterações:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class cad_usuarios extends cad01{
    function __construct(){
        $param = array(
            'filtraCliente' => false,
        );
        parent::__construct('sys001', 'Titulo Teste', $param);
    }
}