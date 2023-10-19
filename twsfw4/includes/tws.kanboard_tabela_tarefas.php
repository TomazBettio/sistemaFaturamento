<?php
//include 'kanboard_cabecalho_coluna.php';
//include 'kanboard_coluna_tarefas.php';

class kanboard_tabela_tarefas{
    private $_cabs = array();
    private $_tarefas = array();
    
    private $_id;
    private $_linkSalvar;
    private $_linkReload;
    private $_linkCheck;
    private $_linkCriacaoTarefas;
    
    public function __construct($projeto, $dados, $param = array()){
        $this->_linkSalvar = $param['link_salvar'] ?? '/intranet4/salvar_kanboard.php';
        $this->_linkReload = $param['link_reload'] ?? '/intranet4/index.php?menu=testes.mercado_pago.index';
        $this->_linkCheck  = ($param['link_check']  ?? '/intranet4/check.php?projeto=' .  $projeto) . '&estampa_intranet=' . time();
        $this->_linkCriacaoTarefas = $param['link_criacao'] ?? '/criar.php';
        
        $this->_id = $projeto;
        $cabs_param = $dados['colunas'];
        foreach ($cabs_param as $param_atual){
            $cab_novo = new kanboard_cabecalho_coluna($param_atual);
            $pos = $cab_novo->getPosicaoColuna();
            $id_coluna = $cab_novo->getIdColuna();
            $this->_cabs[$pos] = $cab_novo;
            unset($cab_novo);
            $this->_tarefas[$pos] = new kanboard_coluna_tarefas($param_atual, $id_coluna, $this->_id);
        }
        ksort($this->_cabs);
        ksort($this->_tarefas);
    }
    
    public function __toString(){
        $ret = '<div id="board-container" class>';
        $ret .= $this->montarTagTable();
        
        $ret .= $this->renderizarCabecalho();
        $ret .= $this->renderizarTarefas();
        
        $ret .= '</table>';
        $ret .= '</div>';
        return $ret;
    }
    
    private function montarTagTable(){
        //return '<table id="board" class="board-project-' . $this->_id . '">';
        
        $ret = '<table id="board"
                   class="board-project-' . $this->_id . '"
                   data-project-id="' . $this->_id . '"
                   data-check-interval="10"
                   data-save-url="' . $this->_linkSalvar . '"
                   data-reload-url="' . $this->_linkReload . '"
                   data-check-url="' . $this->_linkCheck . '"
                   data-task-creation-url="' . $this->_linkCriacaoTarefas . '"
'. $this->montaStyle() . '
            >';
        
        return $ret;
    }
    
    private function montaStyle(){
        return '"--color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    margin: 0;
    padding: 0;
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: fixed;
    margin-bottom: 0;
    font-weight: initial;
    line-height: initial;
    text-align: initial;
    color: initial;
    font-size: initial;
    box-sizing: initial;"';
    }
    
    private function renderizarCabecalho(){
        $ret = '';
        if(count($this->_cabs) > 0){
            $ret .= '<tr class="board-swimlane-columns-' . $this->_id . '"' . $this->montarStyleTrCabecalho() . '>';
            foreach ($this->_cabs as $cab){
                $ret .= $cab;
            }
            $ret .= '</tr>';
        }
        return $ret;
    }
    
    private function montarStyleTrCabecalho(){
        return 'style="    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    border-collapse: collapse;
    border-spacing: 0;
    margin: 0;
    padding: 0;
    font-weight: initial;
    line-height: initial;
    text-align: initial;
    color: initial;
    font-size: initial;
    box-sizing: initial;"';
    }
    
    private function renderizarTarefas(){
        $ret = '';
        if(count($this->_tarefas) > 0){
            $ret .= '<tr class="board-swimlane board-swimlane-tasks-' . $this->_id . '"' . $this->montarStyleTrTarefas() . '>';
            foreach ($this->_tarefas as $tarefa){
                $ret .= $tarefa;
            }
            $ret .= '</tr>';
        }
        return $ret;
    }
    
    private function montarStyleTrTarefas(){
        return 'style="    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    border-collapse: collapse;
    border-spacing: 0;
    margin: 0;
    padding: 0;
    font-weight: initial;
    line-height: initial;
    text-align: initial;
    color: initial;
    text-rendering: initial;
    font-size: initial;
    box-sizing: initial;"';
    }
}