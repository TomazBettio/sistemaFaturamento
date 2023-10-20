<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class graficos_jqvmap{
    private $_dados;
    
    function __construct($dados = array()){
        $this->_dados = $dados;
    }
    
    function gerarJsLabels(){
        $ret = '';
        if(is_array($this->_dados) && count($this->_dados) > 0){
            foreach ($this->_dados as $estado => $valor){
                $ret .= "if (code == \"$estado\") {
				label[0].innerHTML = label[0].innerHTML + \"<br>----------<br>$valor Processos\";
			}";
            }
            $ret = 'onLabelShow: function(event, label, code) {' . $ret . '},';
        }
        return $ret;
    }
    
    function __toString(){
        $jquery = '
jQuery(\'#vmap\').vectorMap({
		map: \'brazil_br\', 
		enableZoom: false, 
		showTooltip: true,
		' . $this->gerarJsLabels() . '
	});
';
        addPortalJS('plugin' , 'jqvmap/jquery.vmap.js');
        addPortalJS('plugin' , 'jqvmap/maps/jquery.vmap.brazil.js');
        addPortalCSS('plugin', 'jqvmap/jqvmap.css');
        addPortalJquery($jquery);
        return '<div id="vmap" style="width: 800px; height: 600px;"></div>';
    }
}