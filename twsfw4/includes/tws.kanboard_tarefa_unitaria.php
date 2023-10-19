<?php
class kanboard_tarefa_unitaria{
    private $_arrastavel;
    private $_ativa;
    private $_cor;
    private $_id;
    private $_id_coluna;
    private $_id_projeto;
    private $_posicao;
    private $_id_dono;
    private $_id_caterogira;
    private $_dt_vencimento;
    private $_url;
    private $_responsavel;
    private $_etiqueta;
    private $_nome_categoria;
    private $_cor_categoria;
    private $_categoria_descricao;
    private $_tags;
    
    public function __construct($param){
        $this->_arrastavel = $param['arrastavel'];//$param['is_draggable'];
        $this->_ativa = $param['ativo'];//($param['is_active'] === '1');
        $this->_cor = $param['cor'];
        $this->_id = $param['id'];
        $this->_id_coluna = $param['coluna'];
        $this->_id_projeto = $param['projeto'];
        $this->_posicao = $param['posicao'];
        $this->_id_dono = $param['dono'];
        $this->_id_caterogira = $param['categoria'];
        $this->_dt_vencimento = $param['data_limite'];
        $this->_url = 'www.google.com';
        $this->_responsavel = $this->montarResponsavel($param);
        $this->_etiqueta = $param['etiqueta'];
        $this->_nome_categoria = $param['categoria'];
        $this->_cor_categoria = $param['cor'];//$param['category_color_id'];
        $this->_categoria_descricao = '';//$param['category_description'];
        $this->_tags = $param['tags'];
    }
        
    private function montarResponsavel($param){
        return !empty($param['assignee_name']) ? $param['assignee_name'] : (!empty($param['assignee_username']) ? $param['assignee_username'] : '');
    }

    
    public function __toString(){
        //return $this->retornarHtmlTeste();
        $ret = '<div class="
        task-board
        ' . ($this->_arrastavel ? 'draggable-item ' : '') . ($this->_ativa ? 'task-board-status-open ' : 'task-board-status-closed ') . '
        color-' . $this->_cor . '"
     data-task-id="' . $this->_id . '"
     data-column-id="' . $this->_id_coluna . '"
     data-swimlane-id="' . $this->_id_projeto . '"
     data-position="' . $this->_posicao . '"
     data-owner-id="' . $this->_id_dono . '"
     data-category-id="' . $this->_id_caterogira . '"
     data-due-date="' . $this->_dt_vencimento . '"
     data-task-url="' . $this->_url . '"
        ' . $this->gerarStyleDiv1() . '>';
        $ret .= '<div class="task-board-sort-handle" style="display: none;"' . $this->gerarStyleDiv2() . '><i class="fa fa-arrows-alt"' . $this->gerarStyleI() . '></i></div>';
        $ret .= $this->gerarHtmlExpandido();
        $ret .= '</div>';
        return $ret;
    }
    
    private function gerarStyleI(){
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
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    box-sizing: initial;"';
    }
    
    private function gerarStyleDiv2(){
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
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    float: left;
    padding-right: 5px;
    display: none;
    box-sizing: initial;"';
    }
    
    private function gerarStyleDiv1(){
        return ' style="     --color-primary: #333;
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
    cursor: pointer;
    -webkit-user-select: none;
    border: 1px solid #000;
    padding: 2px;
    word-wrap: break-word;
    font-size: .9em;
    border-radius: 6px;
    page-break-inside: avoid;
    touch-action: none;
    border-width: 2px;
    margin-bottom: 0;
    position: relative;
    left: 0px;
    top: 0px;
    box-sizing: initial;"';
    }
    
    public function getPosicao(){
        return $this->_posicao;
    }
    
    private function gerarHtmlExpandido(){
        $ret = '<div class="task-board-expanded"' . $this->gerarStyleDiv1HtmlExpandido() . '>
                    <div class="task-board-saving-icon"' . $this->gerarStyleDiv2HtmlExpandido() . '>
                        <i class="fa fa-spinner fa-pulse fa-2x"' . $this->gerarStyleIHtmlExpandido() . '></i>
                    </div>
                    <div class="task-board-header"' . $this->gerarStyleDiv3HtmlExpandido() . '>
                        ' . $this->gerarDropdown() .
                        $this->gerarHtmlPrivilegioEditarTarefa() . 
    
                        (!empty($this->_id_dono) ? '<span class="task-board-assignee">' . $this->_responsavel . '</span>' : '') .
                        $this->avatarDaTarefa() . ' 
                    </div>
            
                    <div class="task-board-title"' . $this->gerarStyleDiv4HtmlExpandido() . '>
                        <a href="/kanboard/task/2" class="" title=""' . $this->gerarStyleAHtmlExpandido() . '>' . $this->_etiqueta . '</a>
                    </div>
                ' . $this->footer() . '
                </div>';
        
        return $ret;
    }
    
    private function gerarStyleAHtmlExpandido(){
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
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    border: none;
    color: #000;
    text-decoration: none;
    background-color: initial;"
    box-sizing: initial;';
    }
    
    private function gerarStyleDiv2HtmlExpandido(){
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
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    position: absolute;
    margin: auto;
    width: 100%;
    text-align: center;
    color: #000;
    display: none;
    box-sizing: initial;"';
    }
    
    private function gerarStyleIHtmlExpandido(){
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
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    text-align: center;
    color: #000;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    font-size: 2em;
    animation: fa-spin 1s infinite steps(8);
    box-sizing: initial;"';
    }
    
    private function gerarStyleDiv3HtmlExpandido(){
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
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    box-sizing: initial;"';
    }
    
    private function gerarStyleDiv4HtmlExpandido(){
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
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    margin-top: 5px;
    margin-bottom: 8px;
    box-sizing: initial;"';
    }
    
    private function gerarStyleDiv1HtmlExpandido(){
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
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    box-sizing: initial;"';
    }
    
    private function footer(){
        $ret = '';
        if(!empty($this->_id_caterogira)){
            $ret .= '<div class="task-board-category-container task-board-category-container-color">
                        <span class="task-board-category category-' . $this->_nome_categoria . ($this->_cor_categoria ? "color-" . $this->_cor_categoria : '') . '">';
            $ret .= $this->_nome_categoria;
            /*
             * a linha de cima deveria ser isso aqui
             * <?= $this->url->link(
             $this->text->e($task['category_name']),
             'TaskModificationController',
             'edit',
             array('task_id' => $task['id']),
             false,
             'js-modal-large' . (! empty($task['category_description']) ? ' tooltip' : ''),
             t('Change category')
             ) ?>*/
            if(!empty($this->_categoria_descricao)){
                /*<?= $this->app->tooltipMarkdown($task['category_description']) ?>*/
            }
            $ret .= '   </span>
                    </div>';
        }
        if(!empty($this->_tags)){
            $ret .= '<div class="task-tags">
                        <ul>';
            foreach($this->_tags as $tag){
               $ret .= '<li class="task-tag '. ($tag['color_id'] ? "color-" . $tag['color_id'] : '') . '" ' . $tag['name'] . '</li>';
            }
        }
        /*
    
        <?php foreach ($task['tags'] as $tag): ?>
            <li class="task-tag <?= $tag['color_id'] ? "color-{$tag['color_id']}" : '' ?>"><?= $this->text->e($tag['name']) ?></li>
        <?php endforeach ?>
        </ul>
    </div>

<div class="task-board-icons">
    <div class="task-board-icons-row">
        <?php if ($task['reference']): ?>
            <span class="task-board-reference" title="<?= t('Reference') ?>">
                <span class="ui-helper-hidden-accessible"><?= t('Reference') ?> </span><?= $this->task->renderReference($task) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="task-board-icons-row">
        <?php if ($task['is_milestone'] == 1): ?>
            <span title="<?= t('Milestone') ?>">
                <i class="fa fa-flag flag-milestone" role="img" aria-label="<?= t('Milestone') ?>"></i>
            </span>
        <?php endif ?>

        <?php if ($task['score']): ?>
            <span class="task-score" title="<?= t('Complexity') ?>">
                <i class="fa fa-trophy" role="img" aria-label="<?= t('Complexity') ?>"></i>
                <?= $this->text->e($task['score']) ?>
            </span>
        <?php endif ?>

        <?php if (! empty($task['time_estimated']) || ! empty($task['time_spent'])): ?>
            <span class="task-time-estimated" title="<?= t('Time spent and estimated') ?>">
                <span class="ui-helper-hidden-accessible"><?= t('Time spent and estimated') ?> </span><?= $this->text->e($task['time_spent']) ?>/<?= $this->text->e($task['time_estimated']) ?>h
            </span>
        <?php endif ?>

        <?php if (! empty($task['date_due'])): ?>
            <span class="task-date
                <?php if (time() > $task['date_due']): ?>
                     task-date-overdue
                <?php elseif (date('Y-m-d') == date('Y-m-d', $task['date_due'])): ?>
                     task-date-today
                <?php endif ?>
                ">
                <i class="fa fa-calendar"></i>
                <?php if (date('Hi', $task['date_due']) === '0000' ): ?>
                    <?= $this->dt->date($task['date_due']) ?>
                <?php else: ?>
                    <?= $this->dt->datetime($task['date_due']) ?>
                <?php endif ?>
            </span>
        <?php endif ?>
    </div>
    <div class="task-board-icons-row">

        <?php if ($task['recurrence_status'] == \Kanboard\Model\TaskModel::RECURRING_STATUS_PENDING): ?>
            <?= $this->app->tooltipLink('<i class="fa fa-refresh fa-rotate-90"></i>', $this->url->href('BoardTooltipController', 'recurrence', array('task_id' => $task['id']))) ?>
        <?php endif ?>

        <?php if ($task['recurrence_status'] == \Kanboard\Model\TaskModel::RECURRING_STATUS_PROCESSED): ?>
            <?= $this->app->tooltipLink('<i class="fa fa-refresh fa-rotate-90 fa-inverse"></i>', $this->url->href('BoardTooltipController', 'recurrence', array('task_id' => $task['id']))) ?>
        <?php endif ?>

        <?php if (! empty($task['nb_links'])): ?>
            <?= $this->app->tooltipLink('<i class="fa fa-code-fork fa-fw"></i>'.$task['nb_links'], $this->url->href('BoardTooltipController', 'tasklinks', array('task_id' => $task['id']))) ?>
        <?php endif ?>

        <?php if (! empty($task['nb_external_links'])): ?>
            <?= $this->app->tooltipLink('<i class="fa fa-external-link fa-fw"></i>'.$task['nb_external_links'], $this->url->href('BoardTooltipController', 'externallinks', array('task_id' => $task['id']))) ?>
        <?php endif ?>

        <?php if (! empty($task['nb_subtasks'])): ?>
            <?= $this->app->tooltipLink('<i class="fa fa-bars fa-fw"></i>'.round($task['nb_completed_subtasks'] / $task['nb_subtasks'] * 100, 0).'%', $this->url->href('BoardTooltipController', 'subtasks', array('task_id' => $task['id']))) ?>
        <?php endif ?>

        <?php if (! empty($task['nb_files'])): ?>
            <?= $this->app->tooltipLink('<i class="fa fa-paperclip fa-fw"></i>'.$task['nb_files'], $this->url->href('BoardTooltipController', 'attachments', array('task_id' => $task['id']))) ?>
        <?php endif ?>

        <?php if ($task['nb_comments'] > 0): ?>
            <?php if ($not_editable): ?>
                <?php $aria_label = $task['nb_comments'] == 1 ? t('%d comment', $task['nb_comments']) : t('%d comments', $task['nb_comments']); ?>
                <span title="<?= $aria_label ?>" role="img" aria-label="<?= $aria_label ?>"><i class="fa fa-comments-o"></i>&nbsp;<?= $task['nb_comments'] ?></span>
            <?php else: ?>
                <?= $this->modal->medium(
                    'comments-o',
                    $task['nb_comments'],
                    'CommentListController',
                    'show',
                    array('task_id' => $task['id']),
                    $task['nb_comments'] == 1 ? t('%d comment', $task['nb_comments']) : t('%d comments', $task['nb_comments'])
                ) ?>
            <?php endif ?>
        <?php endif ?>

        <?php if (! empty($task['description'])): ?>
            <?= $this->app->tooltipLink('<i class="fa fa-file-text-o"></i>', $this->url->href('BoardTooltipController', 'description', array('task_id' => $task['id']))) ?>
        <?php endif ?>

        <?php if ($task['is_active'] == 1): ?>
            <div class="task-icon-age">
                <span title="<?= t('Task age in days')?>" class="task-icon-age-total"><span class="ui-helper-hidden-accessible"><?= t('Task age in days') ?> </span><?= $this->dt->age($task['date_creation']) ?></span>
                <span title="<?= t('Days in this column')?>" class="task-icon-age-column"><span class="ui-helper-hidden-accessible"><?= t('Days in this column') ?> </span><?= $this->dt->age($task['date_moved']) ?></span>
            </div>
        <?php else: ?>
            <span class="task-board-closed"><i class="fa fa-ban fa-fw"></i><?= t('Closed') ?></span>
        <?php endif ?>

        <?= $this->task->renderPriority($task['priority']) ?>

        <?= $this->hook->render('template:board:task:icons', array('task' => $task)) ?>
    </div>
</div>

<?= $this->hook->render('template:board:task:footer', array('task' => $task)) ?>*/
        return '';
    }
    
    private function gerarHtmlPrivilegioEditarTarefa(){
        return '<a href="/kanboard/?controller=TaskModificationController&amp;action=edit&amp;task_id=' . $this->_id . '" class="js-modal-large" title="" ' . $this->gerarStyleAPrivilegio() . '>
                    <i class="fa fa-edit fa-fw js-modal-large" aria-hidden="true"' . $this->gerarStyleIPrivilegio() . '></i>
                </a>';
    }
    
    private function gerarStyleAPrivilegio(){
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
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    border: none;
    color: #000;
    text-decoration: none;
    background-color: initial;
    box-sizing: initial;"';
    }
    
    private function gerarStyleIPrivilegio(){
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
    -webkit-user-select: none;
    word-wrap: break-word;
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
    
    private function gerarHtmlColapsado(){
        return '';
    }
    
    private function avatarDaTarefa(){
        /*
    }
        <?php if (! empty($task['owner_id'])): ?>
<div class="task-board-avatars">
    <span
        <?php if ($this->user->hasProjectAccess('TaskModificationController', 'edit', $task['project_id'])): ?>
        class="task-board-assignee task-board-change-assignee"
        data-url="<?= $this->url->href('TaskModificationController', 'edit', array('task_id' => $task['id'])) ?>">
    <?php else: ?>
        class="task-board-assignee">
    <?php endif ?>
        <?= $this->avatar->small(
            $task['owner_id'],
            $task['assignee_username'],
            $task['assignee_name'],
            $task['assignee_email'],
            $task['assignee_avatar_path'],
            'avatar-inline'
        ) ?>
    </span>
</div>
<?php endif ?>
    }
    */
        return '';
    }
    
    private function gerarDropdown(){
        
        $ret = '<div class="dropdown"' . $this->gerarStyleDropDiv() . '>
                    <a href="#" class="dropdown-menu dropdown-menu-link-icon" ' . $this->gerarStyleDropA() . '>
                        <strong ' . $this->gerarStyleStrongDrop() . '>#' . $this->_id . '<i class="fa fa-caret-down"' . $this->gerarStyleIDrop() . '></i></strong>
                    </a>';
        $menu_real = '
                    <ul ' . $this->gerarStyleUlDrop() . '>
                        <li>
                            <a href="/kanboard/?controller=TaskModificationController&amp;action=assignToMe&amp;task_id=2&amp;csrf_token=f0014379618d8ddfad3f8a9719d3330b0cb6bcd9bcfa3bb48405f68eca4ed155&amp;redirect=board" class="" title=""><i class="fa fa-fw fa-hand-o-right" aria-hidden="true"></i>Assign to me</a>            </li>
                        <li>
                            <a href="/kanboard/?controller=TaskModificationController&amp;action=start&amp;task_id=2&amp;csrf_token=32374ee456a786edf89e5311f4775afddbe8c2739e240829614b42b280243b9f&amp;redirect=board" class="" title=""><i class="fa fa-fw fa-play" aria-hidden="true"></i>Set the start date automatically</a>            </li>
                        <li>
                            <a href="/kanboard/?controller=TaskModificationController&amp;action=edit&amp;task_id=2" class="js-modal-large" title=""><i class="fa fa-edit fa-fw js-modal-large" aria-hidden="true"></i>Edit the task</a>            </li>
                        <li>
                            <a href="/kanboard/?controller=SubtaskController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-plus fa-fw js-modal-medium" aria-hidden="true"></i>Add a sub-task</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskInternalLinkController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-code-fork fa-fw js-modal-medium" aria-hidden="true"></i>Add internal link</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskExternalLinkController&amp;action=find&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-external-link fa-fw js-modal-medium" aria-hidden="true"></i>Add external link</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=CommentController&amp;action=create&amp;task_id=2" class="js-modal-small" title=""><i class="fa fa-comment-o fa-fw js-modal-small" aria-hidden="true"></i>Add a comment</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskFileController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-file fa-fw js-modal-medium" aria-hidden="true"></i>Attach a document</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskPopoverController&amp;action=screenshot&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-camera fa-fw js-modal-medium" aria-hidden="true"></i>Add a screenshot</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskDuplicationController&amp;action=duplicate&amp;task_id=2" class="js-modal-small" title=""><i class="fa fa-files-o fa-fw js-modal-small" aria-hidden="true"></i>Duplicate</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskDuplicationController&amp;action=copy&amp;task_id=2&amp;project_id=1" class="js-modal-small" title=""><i class="fa fa-clipboard fa-fw js-modal-small" aria-hidden="true"></i>Duplicate to project</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskDuplicationController&amp;action=move&amp;task_id=2&amp;project_id=1" class="js-modal-small" title=""><i class="fa fa-clone fa-fw js-modal-small" aria-hidden="true"></i>Move to project</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskMailController&amp;action=create&amp;task_id=2" class="js-modal-small" title=""><i class="fa fa-paper-plane fa-fw js-modal-small" aria-hidden="true"></i>Send by email</a>        </li>
                        <li>
                            <a href="/kanboard/?controller=TaskSuppressionController&amp;action=confirm&amp;task_id=2" class="js-modal-confirm" title=""><i class="fa fa-trash-o fa-fw js-modal-confirm" aria-hidden="true"></i>Remove</a>            </li>
                        <li>
                            <a href="/kanboard/?controller=TaskStatusController&amp;action=close&amp;task_id=2" class="js-modal-confirm" title=""><i class="fa fa-times fa-fw js-modal-confirm" aria-hidden="true"></i>Close this task</a>                    </li>
                    </ul>';
        $ret .= $menu_real;
        $ret .= '
                </div>';
        return $ret;
    }
    
    private function gerarStyleLiDrop(){
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
    word-wrap: break-word;
    font-size: .9em;
    margin: 0;
    padding: 0;
    margin-left: 20px;
    box-sizing: initial;"';
        return $ret;
    }
    
    private function gerarStyleUlDrop(){
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
    word-wrap: break-word;
    font-size: .9em;
    margin: 0;
    padding: 0;
    display: none;
    box-sizing: initial;
    position: absolute;"';
        return $ret;
    }
    
    private function gerarStyleIDrop(){
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
    border-collapse: collapse;
    border-spacing: 0;
    -webkit-user-select: none;
    word-wrap: break-word;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    padding-right: 3px;
    text-decoration: none;
    color: #333;
    box-sizing: initial;"';
        return $ret;
    }
    
    private function gerarStyleStrongDrop(){
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
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    border-collapse: collapse;
    border-spacing: 0;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    color: #333;
    box-sizing: initial;
    font-weight: initial;
    box-sizing: initial;
    line-height: initial;
    -webkit-user-select: none;
    text-align: initial;
    list-style: initial;"';
        return $ret;
    }
    
    private function gerarStyleDropA(){
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
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    border: none;
    color: #000;
    text-decoration: none;
    
    position: initial;
    top: initial;
    left: initial;
    z-index: initial;
    display: initial;
    float: initial;
    min-width: initial;
    padding: initial;
    margin: initial;
    text-align: initial;
    list-style: initial;
    background-color: initial;
    background-clip: initial;
    border-radius: initial;
    box-shadow: initial;"';
    }
    
    private function gerarStyleDropDiv(){
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
    color: var(--color-primary);
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    border-collapse: collapse;
    border-spacing: 0;
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    display: inline;
    position: relative;
    box-sizing: initial;"';
    }
    
    function retornarHtmlTeste(){
        return '<div class="task-board draggable-item task-board-status-open task-board-recent color-yellow ui-sortable-handle" data-task-id="2" data-column-id="5" data-swimlane-id="2" data-position="1" data-owner-id="0" data-category-id="0" data-due-date="0" data-task-url="/kanboard/task/2">

    <div class="task-board-sort-handle" style="display: none;"><i class="fa fa-arrows-alt"></i></div>

            <div class="task-board-expanded">
            <div class="task-board-saving-icon" style="display: none;"><i class="fa fa-spinner fa-pulse fa-2x"></i></div>
            <div class="task-board-header">
                                    <div class="dropdown">
    <a href="#" class="dropdown-menu dropdown-menu-link-icon"><strong>#2 <i class="fa fa-caret-down"></i></strong></a>
    <ul>
        
                                <li>
                <a href="/kanboard/?controller=TaskModificationController&amp;action=assignToMe&amp;task_id=2&amp;csrf_token=27f429291c8f8d0e64c0609ca7470096dd6a6cbee2a742f913bd95cb35c36f08&amp;redirect=board" class="" title=""><i class="fa fa-fw fa-hand-o-right" aria-hidden="true"></i>Assign to me</a>            </li>
                                    <li>
                <a href="/kanboard/?controller=TaskModificationController&amp;action=start&amp;task_id=2&amp;csrf_token=e4fd27be740b24f0d4be7eeeca07ca1e2143d4e92fd4cfb5a4518f491f42c440&amp;redirect=board" class="" title=""><i class="fa fa-fw fa-play" aria-hidden="true"></i>Set the start date automatically</a>            </li>
                        <li>
                <a href="/kanboard/?controller=TaskModificationController&amp;action=edit&amp;task_id=2" class="js-modal-large" title=""><i class="fa fa-edit fa-fw js-modal-large" aria-hidden="true"></i>Edit the task</a>            </li>
                <li>
            <a href="/kanboard/?controller=SubtaskController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-plus fa-fw js-modal-medium" aria-hidden="true"></i>Add a sub-task</a>        </li>
        
        <li>
            <a href="/kanboard/?controller=TaskInternalLinkController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-code-fork fa-fw js-modal-medium" aria-hidden="true"></i>Add internal link</a>        </li>
        <li>
            <a href="/kanboard/?controller=TaskExternalLinkController&amp;action=find&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-external-link fa-fw js-modal-medium" aria-hidden="true"></i>Add external link</a>        </li>
        
        <li>
            <a href="/kanboard/?controller=CommentController&amp;action=create&amp;task_id=2" class="js-modal-small" title=""><i class="fa fa-comment-o fa-fw js-modal-small" aria-hidden="true"></i>Add a comment</a>        </li>
        
        <li>
            <a href="/kanboard/?controller=TaskFileController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-file fa-fw js-modal-medium" aria-hidden="true"></i>Attach a document</a>        </li>
        <li>
            <a href="/kanboard/?controller=TaskPopoverController&amp;action=screenshot&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-camera fa-fw js-modal-medium" aria-hidden="true"></i>Add a screenshot</a>        </li>
        
        <li>
            <a href="/kanboard/?controller=TaskDuplicationController&amp;action=duplicate&amp;task_id=2" class="js-modal-small" title=""><i class="fa fa-files-o fa-fw js-modal-small" aria-hidden="true"></i>Duplicate</a>        </li>
        <li>
            <a href="/kanboard/?controller=TaskDuplicationController&amp;action=copy&amp;task_id=2&amp;project_id=2" class="js-modal-small" title=""><i class="fa fa-clipboard fa-fw js-modal-small" aria-hidden="true"></i>Duplicate to project</a>        </li>
        
        <li>
            <a href="/kanboard/?controller=TaskDuplicationController&amp;action=move&amp;task_id=2&amp;project_id=2" class="js-modal-small" title=""><i class="fa fa-clone fa-fw js-modal-small" aria-hidden="true"></i>Move to project</a>        </li>
        <li>
            <a href="/kanboard/?controller=TaskMailController&amp;action=create&amp;task_id=2" class="js-modal-small" title=""><i class="fa fa-paper-plane fa-fw js-modal-small" aria-hidden="true"></i>Send by email</a>        </li>
        
                    <li>
                <a href="/kanboard/?controller=TaskSuppressionController&amp;action=confirm&amp;task_id=2" class="js-modal-confirm" title=""><i class="fa fa-trash-o fa-fw js-modal-confirm" aria-hidden="true"></i>Remove</a>            </li>
                        <li>
                            <a href="/kanboard/?controller=TaskStatusController&amp;action=close&amp;task_id=2" class="js-modal-confirm" title=""><i class="fa fa-times fa-fw js-modal-confirm" aria-hidden="true"></i>Close this task</a>                    </li>
        
            </ul>
</div>
                                            <a href="/kanboard/?controller=TaskModificationController&amp;action=edit&amp;task_id=2" class="js-modal-large" title=""><i class="fa fa-edit fa-fw js-modal-large" aria-hidden="true"></i></a>                                    
                
                            </div>

                        <div class="task-board-title">
                <a href="/kanboard/task/2" class="" title="">fsdfsdf</a>            </div>
            
            

<div class="task-board-icons">
    <div class="task-board-icons-row">
            </div>
    <div class="task-board-icons-row">
        
        
        
            </div>
    <div class="task-board-icons-row">

        
        
        
        
        
        
        
        
                    <div class="task-icon-age">
                <span title="Task age in days" class="task-icon-age-total"><span class="ui-helper-hidden-accessible">Task age in days </span>5d</span>
                <span title="Days in this column" class="task-icon-age-column"><span class="ui-helper-hidden-accessible">Days in this column </span>&lt;30m</span>
            </div>
        
        <span class="task-priority" title="Task priority"><span class="ui-helper-hidden-accessible">Task priority </span>P0</span>
            </div>
</div>

        </div>
    </div>';
    }
}