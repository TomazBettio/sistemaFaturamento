<?php
/*
 * Data Criação: 25/09/2023
 * Autor: Alex Cesar
 *
 * Descricao: 	schedule para atualizar o status de fechamento de requisições, a partir dos valores de seus itens
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class fechamento_req{
    function __construct(){
        set_time_limit(0);
    }
    
    function index(){
    }
    
    function schedule($parametro)
    {
        $requisicoes = $this->todasRequisicoes();
        foreach($requisicoes as $req)
        {
            //pega status de todos os itens
            $itens_req = $this->itensRequisicao($req);
            //se todos os itens fechados, atualiza status
            if(!in_array('A', $itens_req)){
                $hoje = date('Ymd');
                if(in_array('R', $itens_req)){
                    //atualiza status para R
                    $status = 'R';
                } else {
                    //atualiza status para F
                    $status = 'F';
                }
                $sql = "UPDATE pmp_requisicao SET data_fecha = '$hoje', status = '$status' WHERE id = $req";
                query($sql);
            }
        }
    }
    
    private function itensRequisicao($req)
    {
        $ret = [];
        $sql = "SELECT status FROM pmp_item_requisicao WHERE ativo = 'S' AND requisicao = $req";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $ret[]= $row['status'];
            }
        }
        return $ret;
    }
    
    private function todasRequisicoes()
    {
        $ret = [];
        $sql = "SELECT id FROM pmp_requisicao WHERE ativo = 'S' AND status = 'A'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $ret[] = $row['id'];
            }
        }
        return $ret;
    }
}