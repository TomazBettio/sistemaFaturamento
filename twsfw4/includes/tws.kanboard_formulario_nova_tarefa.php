<?php
class kanboard_formulario_nova_tarefa{
    private $_form;
    private $_link;
    
    function __construct($param = array(), $link = ''){
        $this->_form = new form01($param);
        $this->_link = $link;
    }
    
    function addCampo($param){
        $this->_form->addCampo($param);
    }
    
    function addHidden($param){
        $this->_form->addHidden($param);
    }
    
    function __toString(){
        $ret = '';
        $ret .= $this->_form;
        
        $bt_enviar = '<div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Save","orLabel":"or","cancelLabel":"cancel","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>';
        
        $ret = '<div class="page-header">
    <h2>teasdddd &gt; New task</h2>
</div>
<form method="post" action="' . $this->_link . '" autocomplete="off">
    <div class="task-form-container">
        ' . $ret . '
        <div class="task-form-bottom">
            ' . $bt_enviar . '
        </div>
    </div>
</form>';
        
        return $ret;
    }
    
    private function criarBotaoEnviar(){
        return '<div class="task-form-bottom">

            
                                        
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save","orLabel":"or","cancelLabel":"cancel","color":"blue","tabindex":null,"disabled":false}' . "'" . '></div>        </div>';
    }
}