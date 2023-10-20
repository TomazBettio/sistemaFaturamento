<?php

#[\AllowDynamicProperties]
class kanboard_tarefas_controller extends controller_base{
    protected $_projeto;
    
    function __construct($projeto = '', $json = false){
        $this->_projeto = $projeto;
        $tabela = 'kanboard_tarefas';
        $campo_chave = 'id';
        parent::__construct($tabela, $campo_chave, $json);
    }
    
    protected function configurarCampos(){
        $this->_campos['id'] = new controller_campo('id', true, 'chave');
        $this->_campos['raia'] = new controller_campo('raia', false, 'int');
        $this->_campos['raia']->setValoresPermitidos($this->getRaias());
        $this->_campos['coluna'] = new controller_campo('coluna', false, 'int');
        $this->_campos['posicao'] = new controller_campo('posicao', false, 'int');
        $this->_campos['etiqueta'] = new controller_campo('etiqueta', false, 'string');
        $this->_campos['conteudo'] = new controller_campo('conteudo', false, 'string');
        $this->_campos['cor'] = new controller_campo('cor', false, 'string');
        $this->_campos['dono'] = new controller_campo('dono', false, 'int');
        $this->_campos['categoria'] = new controller_campo('categoria', false, 'string');
        $this->_campos['data_limite'] = new controller_campo('data_limite', false, 'data');
        $this->_campos['responsavel'] = new controller_campo('responsavel', false, 'int');
        $this->_campos['tags'] = new controller_campo('tags', false, 'string');
        $this->_campos['arrastavel'] = new controller_campo('arrastavel', false, 'string');
        $this->_campos['score'] = new controller_campo('score', false, 'float');
        $this->_campos['status'] = new controller_campo('status', false, 'string');
    }
    
    public function getRaias(){
        $ret = array();
        $sql = "select id from kanboard_raia where projeto = $this->_projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = strval($row['id']);
            }
        }
        return $ret;
    }
}