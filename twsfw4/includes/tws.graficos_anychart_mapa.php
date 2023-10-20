<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class graficos_anychart_mapa{
    private $_dados;
    private $_base_log;
    
    function __construct($dados = array(), $base_log = M_E){
        $this->_dados = $dados;
        $this->_base_log = $base_log;
    }
    
    function gerarJsData(){
        $ret = '';
        if(is_array($this->_dados) && count($this->_dados) > 0){
            $dados = array();
            foreach ($this->_dados as $estado => $valor){
                $dados[] = "{'id': 'BR." . strtoupper($estado) . "', 'value': $valor, 'tamanho' : " . log($valor, $this->_base_log) . "}";
            }
            if(is_array($dados)){
                $ret = "var data = [" . implode(',', $dados) . "];";
            }
        }
        return $ret;
    }
    
    function __toString(){
        $jquery = '
anychart.onDocumentReady(function () {
        ' . $this->gerarJsData() . '

		//
		var map = anychart.map();
		map.geoData(anychart.maps.brazil);

		// set the series
		//var series = map.choropleth(data);
		
		let series = map.bubble(
			anychart.data.set(data).mapAs({
                size: "tamanho"
            })
        );
		
		  // disable labels
		series.labels(false);
		
		series.tooltip().format(function(e){
		   return e.getData("value") + " Processos";
		});

		// set the container
		map.container(\'container\');
		map.draw();
      });
';
        //addPortalJS('link', 'https://cdn.anychart.com/releases/8.11.0/js/anychart-core.min.js');
        addPortalJS('plugin', 'anychart/anychart-core.min.js');
        //addPortalJS('link', 'https://cdn.anychart.com/releases/8.11.0/js/anychart-map.min.js');
        addPortalJS('plugin', 'anychart/anychart-map.min.js');
        //addPortalJS('link', 'https://cdn.anychart.com/releases/8.11.0/geodata/countries/brazil/brazil.js');
        addPortalJS('plugin', 'anychart/brazil.js');
        
        
        //addPortalJS('link', 'https://cdn.anychart.com/releases/8.11.0/js/anychart-data-adapter.min.js');
        //addPortalJS('link', 'https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.3.15/proj4.js');
        
        
        addPortalJquery($jquery);
        return '<div id="container" style="width: 400px; height: 400px; margin: 0; padding: 0; "></div>';
    }
}