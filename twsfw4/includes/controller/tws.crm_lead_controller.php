<?php

class crm_lead_controller extends controller_base{
    function __construct($json = false){
        $cad = new cad01('crm_lead');
        $sys002 = $cad->getSys002();
        $campo_chave = $sys002['chave'];
        if(strpos($campo_chave, ',') !== false){
            $campo_chave = explode(',', $campo_chave);
        }
        parent::__construct('crm_lead', $campo_chave, $json);
    }
    
    protected function configurarCampos(){
        $this->_campos[''] = new controller_campo('', false, 'string');
    }
}