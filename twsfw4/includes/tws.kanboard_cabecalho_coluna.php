<?php
class kanboard_cabecalho_coluna{
    private $_id;
    private $_etiqueta;
    private $_posicao;
    private $_limiteTarefas;
    private $_descricao;
    private $_hide;
    private $_idProjeto;
    private $_tarefasAbertas = 0;
    private $_tarefasFechadas = 0;
    private $_numTarefas = 0;
    private $_tarefasVencidas = 0;
    private $_tarefas = array();
    private $_linkFecharTodasTarefas;
    private $_link_novs_tarefa;
    private $_tabelaEntidades;
    private $_btFecharTarefas;
    private $_totalizarScore;
    private $_scoreTotal;
    private $_prefixoScore;
    private $_iconeScore;
    private $_mostrar_totalizador;
    
    public function __construct($param){
        $this->_id = $param['id'];
        $this->_etiqueta = $param['etiqueta'];
        $this->_posicao = $param['posicao'];
        $this->_limiteTarefas = $param['limite'];
        $this->_descricao = $param['descricao'];
        $this->_hide = $param['esconder'];
        $this->_idProjeto = $param['projeto'];
        
        $this->_tarefasAbertas = $param['num_abertas'];
        $this->_tarefasFechadas = $param['num_fechadas'];
        $this->_numTarefas = $param['num_tarefas'];
        $this->_tarefas = $param['tarefas'];
        
        $this->_link_novs_tarefa = $param['link_nova'];
        $this->_linkFecharTodasTarefas = $param['link_fechar'] ?? '';
        
        $this->_btFecharTarefas = $param['bt_fechar_tarefas'] ?? true;
        
        $this->_totalizarScore = $param['totalizar'] ?? false;
        $this->_scoreTotal = $param['score_total'] ?? '';
        $this->_prefixoScore = $param['prefixo_score'] ?? '';
        $this->_iconeScore = $param['icone_score'] ?? '';
        
        $this->_mostrar_totalizador = $param['mostrar_totalizador'] ?? true;
    }
    
    public function setTarefasVencidas($num_tarefas_vencidas){
        $this->_tarefasVencidas = $num_tarefas_vencidas;
    }
    
    public function getTarefasVencidas(){
        return $this->_tarefasVencidas;
    }
    
    static function getTituloColuna($id){
        $ret = '';
        $sql = "select etiqueta from kanboard_colunas where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['etiqueta'];
        }
        return $ret;
    }
    
    static function getCampoColuna($id, $campo){
        $ret = '';
        $sql = "select $campo from kanboard_colunas where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0][$campo];
        }
        return $ret;
    }
    
    public function __toString(){
        $ret = '<th nowrap class="board-column-header board-column-header-' . $this->_id . '"data-column-id="' . $this->_id . '"' . $this->montarStyleTh() . '>';
        $ret .= $this->gerarHtmlColapsado();
        $ret .= $this->geraHtmlExpandido();
        $ret .= '</th>';
        return $ret;
    }
    
    private function montarStyleTh(){
        return 'style="
            --color-primary: #333;
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
    text-align: left;
    padding: .5em 3px;
    border: 1px solid #eee;
    background: #fbfbfb;
    box-sizing: initial;"';
    }
    
    public function getIdColuna(){
        return $this->_id;
    }
    
    public function getPosicaoColuna(){
        return $this->_posicao;
    }
    
    private function gerarHtmlColapsado(){
        $ret = '<div class="board-column-collapsed" ' . $this->gerarStyleDivHtmlColapsado() . '>
                    <small class="board-column-header-task-count" title="' . $this->_tarefasAbertas . '"' . $this->gerarStyleSmallHtmlColapsado() . '>
                        <span id="task-number-column-' . $this->_id . '"' . $this->gerarStyleSpan1HtmlColapsado() . '>
                            <span class="ui-helper-hidden-accessible" ' . $this->gerarStyleSpan2HtmlColapsado() . '>'. $this->_tarefasAbertas . ' </span>'
                                . '<div style="text-align: center;">'   . badge(array('numeral' => $this->_tarefasAbertas, 'texto' => 'tarefa(s) aberta(s)')) . ($this->_tarefasVencidas > 0 ? badge(array('numeral' => $this->_tarefasVencidas, 'cor' => 'danger', 'texto' => 'tarefa(s) vencida(s)')) : '') . '</div>' . '
                        </span>
                    </small>
                </div>';
        /*
        $ret = '<div class="board-column-collapsed">
            <small class="board-column-header-task-count" title="<?= t('Task count') ?>">
                <span id="task-number-column-<?= $column['id'] ?>"><span class="ui-helper-hidden-accessible"><?= t('Task count') ?> </span><?= $column['nb_tasks'] ?></span>
            </small>
        </div>';
		*/
        return $ret;
    }
    
    private function gerarStyleSpan2HtmlColapsado(){
        return ' style="--color-primary: #333;
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
    text-align: left;
    font-size: .8em;
    color: var(--color-light);
    font-weight: 400;
    border: 0;
    clip: rect(0 0 0 0);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute;
    width: 1px;
    box-sizing: initial;"';
    }
    
    private function gerarStyleSpan1HtmlColapsado(){
            return ' style="--color-primary: #333;
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
    text-align: left;
    font-size: initial;
    color: var(--color-light);
    font-weight: initial;
    box-sizing: initial;"';
    }
    
    private function gerarStyleSmallHtmlColapsado(){
        return ' style="    --color-primary: #333;
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
    text-align: left;
    font-size: .8em;
    color: var(--color-light);
    font-weight: 400;
    box-sizing: initial;"';
    }
    
    private function gerarStyleDivHtmlColapsado(){
        return ' style="--color-primary: #333;
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
    text-align: left;
    display: none;
    box-sizing: initial;"';
    }

    private function geraHtmlExpandido(){
        $ret = '<div class="board-column-expanded"' . $this->gerarStyleDivHtmlExpandido() . '>';
        $ret .= $this->geraBotaoNovaTarefa();
        $ret .= '<div nowrap>';
        $ret .= $this->criarCore();
        $ret .= $this->montarTotalizadorTarefas();
        $ret .= $this->montarTotalizadorTarefasVencidas();
        $ret .= '</div>';
        $ret .= '</div>';
        $ret .= $this->montarTotalizadorScore();
        return $ret;
    }
    
    private function montarTotalizadorScore(){
        $ret = '';
        if($this->_totalizarScore){
            $ret = '<div style="font-size: initial;font-family:Helvetica,Arial,sans-serif,\'FontAwesome\';">' . $this->_iconeScore . $this->_prefixoScore . strval($this->_scoreTotal) . '</div>';
        }
        return $ret;
    }
    
    private function gerarStyleDivHtmlExpandido(){
        return ' style="    --color-primary: #333;
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
    text-align: left;
    display: flex;
    align-items: center;
    box-sizing: initial;"';
    }

    private function geraBotaoNovaTarefa(){
        $ret = '';
        if(!is_array($this->_link_novs_tarefa)){
            if(!empty($this->_link_novs_tarefa)){
                $ret = '    <div class="board-add-icon"' . $this->gerarStyleDivBotaoNovaTarefa() . '>';
                $ret .= '       <a href="' . $this->_link_novs_tarefa . '" title="Add a new task" ' . $this->gerarStyleABotaoNovaTarefa() . '>
                                    <i class="fa fa-plus fa-fw js-modal-medium" aria-hidden="true"' . $this->gerarStyleIBotaoNovaTarefa() . '></i>
                                </a>
                            </div>';
            }
        }
        else{
            $ret = '<span class="dropdown"' . $this->gerarStyleSpan2Core() . '>
                        <a href="#" class="dropdown-menu"' . $this->gerarStyleACore() . '>
                                <i class="fa fa-plus fa-fw js-modal-medium"' .$this->gerarStyleIBotaoNovaTarefa() . '></i>
                        </a>
                        ' . $this->criarMenuDropNovaTarefa() . '
                    </span>';
        }
        return $ret;
    }
    
    private function criarMenuDropNovaTarefa(){
        $ret = '<ul ' . $this->gerarStyleUlMenuDrop() . '>';
        foreach ($this->_link_novs_tarefa as $info_bt){
            $param = array(
                'link' => $info_bt[1],
                'classe_a' => 'js-modal-medium',
                'classe_i' => 'fa fa-plus fa-fw js-modal-confirm',
                'etiqueta' => $info_bt[0],
            );
            $ret .= $this->criarBotaoMenuDrop($param);
        }
        $ret .= '</ul>';
        return $ret;
    }
    
    private function gerarStyleDivBotaoNovaTarefa(){
        return ' style="    --color-primary: #333;
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
    text-align: left;
    float: left;
    padding: 0 5px;
    box-sizing: initial;"';
    }
    
    private function gerarStyleABotaoNovaTarefa(){
        return ' style="    --color-primary: #333;
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
    text-align: left;
    border: none;
    text-decoration: none;
    color: var(--color-primary);
    background-color: initial;
    box-sizing: initial;"';
    }
    
    private function gerarStyleIBotaoNovaTarefa(){
        return ' style="    --color-primary: #333;
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
    border-collapse: collapse;
    border-spacing: 0;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    width: 1.28571429em;
    text-align: center;
    padding-right: 3px;
    text-decoration: none;
    color: var(--link-color-primary);
    font-size: 1.4em;
    box-sizing: initial;"';
    }
    
    
    private function montarTotalizadorTarefas(){
        //$ret = '<span><span class="ui-helper-hidden-accessible">Task count</span>' . $this->_numTarefas . '</span>';
        $ret = '';
        if($this->_mostrar_totalizador){
            $ret .= '<span nowarp><span nowarp class="ui-helper-hidden-accessible">Task count</span>' . badge(array('numeral' => $this->_tarefasAbertas, 'texto' => 'tarefa(s) aberta(s)')) . '</span>';
        }
        return $ret;
    }
    
    private function montarTotalizadorTarefasVencidas(){
        //$ret = '<span><span class="ui-helper-hidden-accessible">Task count</span>' . $this->_numTarefas . '</span>';
        $ret = '';
        if($this->_tarefasVencidas > 0){
            $ret .= '<span nowarp style="margin-left: 2px;"><span nowarp class="ui-helper-hidden-accessible">Task count</span>' . badge(array('numeral' => $this->_tarefasVencidas, 'cor' => 'danger', 'texto' => 'tarefa(s) vencida(s)')) . '</span>';
        }
        return $ret;
    }
    

    private function criarCore(){
        return '<span class="board-column-title"' . $this->gerarStyleSpan1Core() .'>
                    <span class="dropdown"' . $this->gerarStyleSpan2Core() . '>
                        <a href="#" class="dropdown-menu"' . $this->gerarStyleACore() . '>' . $this->_etiqueta . '
                                <i class="fa fa-caret-down"' . $this->gerarStyleICore() . '></i>
                        </a>
                        ' . $this->criarMenuDrop() . '
                    </span>
               </span>';
    }
    
    private function gerarStyleSpan1Core(){
        return ' style="    --color-primary: #333;
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
    text-align: left;
    box-sizing: initial;"';
    }
    
    private function gerarStyleSpan2Core(){
        return ' style="    --color-primary: #333;
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
    text-align: left;
    display: inline;
    position: relative;
    box-sizing: initial;"';
    }
    
    private function gerarStyleACore(){
        return ' style="    --color-primary: #333;
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
    text-align: left;
    border: none;
    color: var(--color-primary);
    text-decoration: none;
    box-shadow: initial;
    line-height: initial;
    box-sizing: initial;
    position: initial;
    top: initial;
    left: initial;
    z-index: initial;
    display: initial;
    float: initial;
    min-width: initial;
    padding: initial;
    margin: initial;
    list-style: initial;
    background-color: initial;
    background-clip: initial;
    border-radius: initial;
    font-size: initial;"';
    }
    
    private function gerarStyleICore(){
        return ' style="    --color-primary: #333;
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
    border-collapse: collapse;
    border-spacing: 0;
    text-align: left;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    color: var(--color-primary);
    padding-right: 3px;
    text-decoration: none;
    box-sizing: initial;"';
    }
    
    
    private function criarMenuDrop(){
        $ret = '<ul ' . $this->gerarStyleUlMenuDrop() . '>';
        $ret .= $this->criarBotaoMenuDropMinimizar();
        if($this->_btFecharTarefas){
            $param = array(
                'link' => $this->_linkFecharTodasTarefas,
                'classe_a' => 'js-modal-confirm',
                'classe_i' => 'fa fa-close fa-fw js-modal-confirm',
                'etiqueta' => 'Fechar todas as tarefas desta coluna',
            );
            $ret .= $this->criarBotaoMenuDrop($param);
        }
        $ret .= '</ul>';
        return $ret;
    }
    
    private function gerarStyleUlMenuDrop(){
        $ret = ' style="    --color-primary: #333;
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
    color: var(--color-primary);
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    border-collapse: collapse;
    border-spacing: 0;
    cursor: pointer;
    -webkit-user-select: none;
    font-size: .9em;
    margin: 0;
    padding: 0;
    display: none;
    box-sizing: initial;
    position: absolute;
    background-color: #fff;
    list-style-type: none;"';
        return $ret;
    }
    private function gerarStyleLiMenuDrop(){
        return ' style="    --color-primary: #333;
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
    box-sizing: initial;"';
    }
    private function gerarStyleIMenuDrop(){
        return ' style="    --color-primary: #333;
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
    border-collapse: collapse;
    border-spacing: 0;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    width: 1.28571429em;
    text-align: center;
    color: var(--color-primary);
    padding-right: 3px;
    text-decoration: none;
    box-sizing: initial;"';
    }
    private function gerarStyleAMenuDrop(){
        return ' style="    --color-primary: #333;
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
    border: none;
    text-decoration: none;
    color: var(--color-primary);
    box-sizing: initial;
    background-color: initial;"';
    }
    
    private function criarBotaoMenuDropMinimizar(){
        return '<li' . $this->gerarStyleLiMenuDrop() .'>
                    <i class="fa fa-minus-square fa-fw"' . $this->gerarStyleIMinimizar() . '></i>
                    <a href="' . getLink() .'index" class="board-toggle-column-view" data-column-id="' . $this->_id . '"' . $this->gerarStyleAMenuDrop() . '>Esconder esta coluna</a>
                </li>';
    }
    private function gerarStyleIMinimizar(){
        return ' style="    --color-primary: #333;
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
    border-collapse: collapse;
    border-spacing: 0;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    width: 1.28571429em;
    text-align: center;
    box-sizing: initial;"';
    }
    
    private function criarBotaoMenuDrop($param){
        $ret = '<li ' . $this->gerarStyleLiMenuDrop() . '>
                    <a href="' . $param['link'] . '" ';
        if($param['classe_a'] != ''){
            $ret .= 'class="' . $param['classe_a'] . '" ';
        }
        $ret .= 'title=""' . $this->gerarStyleAMenuDrop() . '>
                        <i class="' . $param['classe_i'] . '" aria-hidden="true"' . $this->gerarStyleIMenuDrop() . '></i>
                        ' . $param['etiqueta'] . '
                    </a>                                
                </li>';
        return $ret;
    }
}