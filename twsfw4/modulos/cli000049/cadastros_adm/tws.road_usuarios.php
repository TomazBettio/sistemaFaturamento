<?php

/*
 * Data Criacao: 02/10/2023
 * 
 * Autor: Gilson Britto
 *
 * Descricao: cad dos usuários do Road.APP
 *
 *
 *TODO:
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


ini_set('display_errors', 1);

ini_set('display_startup_erros', 1);

error_reporting(E_ALL);



class road_usuarios extends cad01{

    public function __construct()
    {
        parent::__construct('road_usuarios');
    }

}