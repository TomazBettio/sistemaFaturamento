<?php
/*
 * Data Criação: 16/10/2023
 * Autor: Alex Cesar
 *
 * Descricao: 	schedule para atualizar o status das campanhas
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class fechamento_campanha{
    function __construct(){
        set_time_limit(0);
    }
    
    function index(){
    }
    
    function schedule($parametro)
    {
        $hoje = date('Ymd');
        $campanhas = $this->todasCampanhas();
        if(!empty($campanhas)){
            foreach($campanhas as $cam){
                if($cam['fim'] <= $hoje)
                {
                    $sql = "UPDATE road_campanha_usuario SET ativo='N', dt_fim = '$hoje' WHERE ativo = 'S' AND campanha={$cam['id']}";
                    query($sql);
                    $sql = "UPDATE road_campanhas SET ativo='N' WHERE id = {$cam['id']}";
                    query($sql);
                }
            }
        }
    }
    
    private function todasCampanhas()
    {
        $ret = [];
        $campos = ['id','fim'];
        $sql = "SELECT id, fim FROM road_campanhas WHERE ativo = 'S' AND excluida = 'N'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            foreach($rows as $row){
                foreach($campos as $cam){
                    $temp[$cam] = $row[$cam];
                }
                $ret[]= $temp;
            }
        }
        return $ret;
    }
    
}