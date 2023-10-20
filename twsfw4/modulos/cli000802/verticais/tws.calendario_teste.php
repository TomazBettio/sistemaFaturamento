<?php

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);


class calendario_teste {
    
    var $funcoes_publicas = array(
        'index'             => true,
    );
    
    
    public function __construct()
    {
        //
    }
    public function index(){
        $calendario = new calendario02();
        $tarefas = array(
            array(
                'titulo' => 'TAREFA TESTE 2',
                'data'   => '20230513'
            ),
            array(
                'titulo' => 'TAREFA TESTE 3',
                'data'   => '20230523'
            ),
        );
        $tarefa_unica = array(
                'titulo' => 'TAREFA TESTE 1',
                'data'   => '20230509'
                );
      //  $calendario->addConjuntoTarefas($tarefas);
      //  $calendario->addTarefa($tarefa_unica);
        return $calendario . '';
    }

}