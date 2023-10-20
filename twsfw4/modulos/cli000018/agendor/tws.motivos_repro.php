<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class motivos_repro extends cad01{
    public function __construct(){
        parent::__construct('bs_motivos_reprovacao', ['btEditar' => false]);
    }
}