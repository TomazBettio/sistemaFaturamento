<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


class compras_limitado{
    var $funcoes_publicas = array(
        'index'     => true,
        'ajax'      => true,
    );
    
    public function __construct(){
    }
    
    public function index(){
        //clip-path: polygon(95% 0,100% 50%,95% 100%,0 100%,5% 50%,0 0);
        //return $botao1 . $botao2 . $botao3;
        /*
         $kanban = new kanban_lite(4);
         $ret = addCard(['titulo' => $this->getTitulo(), 'conteudo' => $kanban . '']);
         return $ret;
         */
        $kanban = new kanban_lite(4);
        $colunas = $this->getColunas();
        foreach ($colunas as $c){
            $kanban->addColuna($c);
        }
        return $kanban . '';
        /*
         $kanban->addColuna(array(
         'etiqueta' => 'coluna1',
         'tarefas' => array(
         array('titulo' => 'v1', 'id' => 1, 'conteudo' => 'tarefa com conteudo'),
         array('titulo' => 'tarefa sem conteudo', 'id' => 2)
         )
         ));
         $kanban->addColuna(array('etiqueta' => 'coluna2'));
         $kanban->addColuna(array('etiqueta' => 'coluna3'));
         */
        
        //return '';
    }
    
    private function getColunas(){
        $ret = [];
        $sql = "select * from kl_etapas where board = 4 and id = 51 order by ordem";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['etiqueta', 'id', 'cor'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $temp['tarefas'] = $this->getTarefas($temp['id']);
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getTarefas($etapa){
        $ret = [];
        $data = date('Y m d');
        $sql = "select * from kl_cards where etapa = '$etapa' and dt_insert >= STR_TO_DATE('$data', '%Y %m %d') order by dt_insert desc";
        $rows = query($sql);
        $primeira = true;
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $tarefa_atual = $this->montarTarefa($row);
                if(!$primeira){
                    $tarefa_atual['editar'] = false;
                }
                else{
                    $primeira = false;
                }
                $ret[] = $tarefa_atual;
            }
        }
        return $ret;
    }
    
    private function montarTarefa($row){
        $temp = array(
            'etiqueta' => $row['etiqueta'],
            'id' => $row['id'],
            'cor' => $row['cor'],
            'score' => floatval($row['score']),
        );
        /*
         if(!empty(str_replace(['0', ',', '.'], '', $row['score']))){
         $temp['etiqueta'] = $row['etiqueta'] . '  ' . badge(['numeral' => formataReais($row['score']), 'texto' => 'Valor do Card', 'concatenarNumeral' => false, 'cor' => 'warning']);
         }
         */
        $resumo = nl2br($row['resumo']);
        if(!empty($row['tags'])){
            $array_badges = array();
            $tags = $row['tags'];
            $temp_explode = explode(';', $tags);
            
            if(is_array($temp_explode) && count($temp_explode) > 0){
                foreach ($temp_explode as $te){
                    $array_badges[] =  badge(['numeral' => $te]);
                }
                $resumo = implode(' ', $array_badges) . '<br>' . $resumo;
            }
        }
        $temp['conteudo'] = $resumo;
        
        return $temp;
    }
    
    public function ajax(){
        $ret = kanban_lite::ajax();
        return $ret;
    }
}