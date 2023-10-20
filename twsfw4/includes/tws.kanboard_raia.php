<?php 
class kanboard_raia{
    private $_num_colunas;
    private $_id;
    private $_editavel;
    private $_etiqueta;
    private $_num_raias;
    private $_colunas;
    private $_descricao;
    private $_num_tarefas;
    private $_tarefas_vencidas = 0;
    private $_limite_tarefas;
    private $_mostrar_totalizador;
    
    public function __construct($param){
        $this->_colunas = $param['colunas'];
        $this->_num_colunas = count($this->_colunas);
        $this->_id = $param['id'];
        $this->_editavel = true;
        $this->_etiqueta = $param['etiqueta'];
        $this->_num_raias = $param['num_raias'];
        $this->_descricao = $param['descricao'] ?? '';
        $this->_num_tarefas = $param['num_tarefas'];
        $this->_limite_tarefas = 0;
        $this->_mostrar_totalizador = $param['mostrar_totalizador'] ?? true;
    }
    
    public function getNumColunas(){
        return count($this->_colunas);
    }
    
    static function getTituloRaia($id){
        $ret = '';
        $sql = "select etiqueta from kanboard_raia where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['etiqueta'];
        }
        return $ret;
    }
    
    static function getCampoRaia($id, $campo){
        $ret = '';
        $sql = "select $campo from kanboard_raia where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0][$campo];
        }
        return $ret;
    }
    
    public function __toString(){
        $ret = '';
        $html_colunas = $this->criarColunas();
        
        if($this->_num_raias > 1){
            $ret = $this->abrirTagTr();
            $ret .= $this->abrirTagTh();
            $ret .= $this->montarBarraExpandir();
            $ret .= $this->_etiqueta;
            $ret .= $this->montarToolTip();
            $ret .= $this->montarContadorTarefas();
            $ret .= $this->montarContadorTarefasVencidas();
            $ret .= '</th></tr>';
            //$ret .= '</td></tr>';
        }
        
        $ret .= $html_colunas;
        
        return $ret;
    }
    
    private function criarColunas(){
        $ret = '';
        $cabs = array();
        $tarefas = array();
        
        //cria os objetos dos cabeçalhos e colunas de tarefas
        foreach ($this->_colunas as $param_coluna_atual){
            $cab_novo = new kanboard_cabecalho_coluna($param_coluna_atual);
            $pos = $cab_novo->getPosicaoColuna();
            $id_coluna = $cab_novo->getIdColuna();
            $tarefas[$pos] = new kanboard_coluna_tarefas($param_coluna_atual, $id_coluna, $this->_id, $this->_num_colunas);
            $num_tarefas_vencidas = $tarefas[$pos]->getNumTarefasVencidas();
            $cab_novo->setTarefasVencidas($num_tarefas_vencidas);
            $this->_tarefas_vencidas += $num_tarefas_vencidas;
            //aqui
            $cabs[$pos] = $cab_novo;
            unset($cab_novo);
            
        }
        
        //transforma os objetos criados em string
        if(count($cabs) > 0){
            $ret .= '<tr class="board-swimlane-columns-' . $this->_id . '"' . $this->montarStyleTrCabecalho() . '>';
            foreach ($cabs as $cab){
                $ret .= $cab;
            }
            $ret .= '</tr>';
        }
        
        if(count($tarefas) > 0){
            $ret .= '<tr class="board-swimlane board-swimlane-tasks-' . $this->_id . '"' . $this->montarStyleTrTarefas() . '>';
            foreach ($tarefas as $tarefa){
                $ret .= $tarefa;
            }
            $ret .= '</tr>';
        }
        
        return $ret;
    }
    
    private function abrirTagTr(){
        $ret = '<tr id="swimlane-' . $this->_id . '">';
        return $ret;
    }
    
    private function abrirTagTh(){
        $ret = '<th class="board-swimlane-header" colspan="' . $this->_num_colunas . '">';
        //$ret = '<td class="board-swimlane-header" colspan="' . $this->_num_colunas . '">';
        return $ret;
    }
    
    private function montarBarraExpandir(){
        $ret = '<a href="#" class="board-swimlane-toggle" data-swimlane-id="' . $this->_id . '">
                    <i class="fa fa-chevron-circle-up hide-icon-swimlane-' . $this->_id . '" title="Collapse swimlane" role="button" aria-label="Collapse swimlane"></i>
                    <i class="fa fa-chevron-circle-down show-icon-swimlane-' . $this->_id . '" title="Expand swimlane" role="button" aria-label="Expand swimlane" style="display: none"></i>
                </a>';
        return $ret;
    }
    
    private function montarToolTip(){
        $ret = '<span class="tooltip" data-href="/kanboard/?controller=BoardTooltipController&amp;action=swimlane&amp;swimlane_id=1&amp;project_id=1">
                    <i class="fa fa-info-circle"></i>
                </span>';
        return $ret;
    }
    
    private function montarContadorTarefas(){
        $ret = '';
        if($this->_mostrar_totalizador){
            $ret .= '<span title="Task count" class="board-column-header-task-count swimlane-task-count-' . $this->_id . '" style="margin-left: 3px">';
            $ret .= '<span><span class="ui-helper-hidden-accessible">Task count</span>' . badge(array('numeral' => $this->_num_tarefas, 'texto' => 'tarefa(s) aberta(s)'));
            if($this->_limite_tarefas > 0){
                $ret .= '/' . $this->_limite_tarefas;
            }
            $ret .= '</span></span>';
        }
        return $ret;
    }
    
    private function montarContadorTarefasVencidas(){
        $ret = '';
        if($this->_mostrar_totalizador && $this->_tarefas_vencidas > 0){
            $ret .= '<span title="Task count" class="board-column-header-task-count swimlane-task-count-' . $this->_id . '" style="margin-left: 3px">';
            $ret .= '<span><span class="ui-helper-hidden-accessible">Task count</span>' . badge(array('numeral' => $this->_tarefas_vencidas, 'cor' => 'danger', 'texto' => 'tarefa(s) vencida(s)'));
            $ret .= '</span></span>';
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
?>