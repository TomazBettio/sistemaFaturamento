<?php 
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class graficos_chart{
    protected $_colunas = array();
    protected $_cabecalho_colunas = array();
    protected $_dataSet = array();
    protected $_id;
    protected $_camposDataSet;
    protected $_formatacaoCampos;
    protected $_tipo;
    protected $_callback = array();
    protected $_formatacoesLabel = array();
    protected $_inteiros = false;
    
    function __construct($tipo, $id = ''){
        $this->_id = !empty($id) ? $id : geraStringAleatoria(10, false, true, true);
        
        if(in_array($tipo, array('bar', 'bubble', 'doughnut', 'pie', 'line', 'polarArea', 'radar', 'scatter'))){
            $this->_tipo = $tipo;
        }
        
        $this->_camposDataSet = array(
            'label' => 'label',
            'backgroundColor' => 'backgroundColor',
            'borderColor' => 'borderColor',
            'pointRadius' => 'pointRadius',
            'pointColor' => 'pointColor',
            'pointStrokeColor' => 'pointStrokeColor',
            'pointHighlightFill' => 'pointHighlightFill',
            'pointHighlightStroke' => 'pointHighlightStroke',
            'data' => 'data',
            'labels' => 'array'
        );
        
        $this->_formatacaoCampos = array(
            'label' => 'string',
            'backgroundColor' => 'string',
            'borderColor' => 'string',
            'pointRadius' => 'bool',
            'pointColor' => 'string',
            'pointStrokeColor' => 'string',
            'pointHighlightFill' => 'string',
            'pointHighlightStroke' => 'string',
            'data' => 'data',
        );
        
        $this->adicionarFormatacoesLabel();
        
        $callbackTitle = '
                        let title = context[0].dataset.label || \'\';
                        if(title){
                            title += \' - \';
                        }
                        title += context[0].chart.data.labels[context[0].dataIndex];
                        return title;
                    ';
        $this->addCallback('title', $callbackTitle);
        
        
        addPortalJS('plugin', 'chart/chart.js', 'I');
    }
    
    private function adicionarFormatacoesLabel(){
        $this->_formatacoesLabel['formatacao_presuf'] = "
    let label = 'prefixo' + context.formattedValue + 'sufixo';
    return label;
";
    }
    
    function __get($campo){
        return $this->_formatacoesLabel[$campo] ?? '';
    }
    
    function addColuna($param){
        if(isset($param['campo']) && isset($param['etiqueta'])){
            $this->_colunas[] = $param['campo'];
            $this->_cabecalho_colunas[$param['campo']] = $param['etiqueta'];
        }
    }
    
    function addDataSet($param){
        $dataSet = array();
        foreach ($this->_camposDataSet as $chave => $valor){
            if(isset($param[$valor])){
                $dataSet[$chave] = $param[$valor];
            }
        }
        $this->_dataSet[] = $dataSet;
    }
    
    protected function getProximoDataSet(){
        
    }
    
    protected function montarListaLabels(){
        $ret = '[dados]';
        $temp = array();
        foreach ($this->_colunas as $coluna){
            $temp[] = "'{$this->_cabecalho_colunas[$coluna]}'";
        }
        if(count($temp) > 0){
            $temp = implode(', ', $temp);
        }
        else{
            $temp = '';
        }
        $ret = str_replace('dados', $temp, $ret);
        return $ret;
    }
    
    protected function montarDataSetIndividual($dataSet){
        /*
         * {
          label               : 'Digital Goods',
          backgroundColor     : 'rgba(60,141,188,0.9)',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [28, 48, 40, 19, 86, 27, 90]
        },
         */
        $ret = '';
        $campos = array();
        foreach($dataSet as $campo => $chave){
            $campos[] = "$campo : " . $this->formatarValor($campo, $chave);
        }
        if(!isset($dataSet['label']) && !isset($dataSet['labels'])){
            $campos[] = 'labels : ' . $this->montarListaLabels();
        }
        $ret = '{' . implode(', ', $campos) . '},';
        return $ret;
    }
    
    protected function formatarValor($campo, $valor, $tipo = ''){
        $ret = '';
        if(isset($this->_formatacaoCampos[$campo]) || !empty($tipo)){
            $tipo = empty($tipo) ? $this->_formatacaoCampos[$campo] : $tipo;
            switch ($tipo) {
                case 'string':
                    $ret = "'$valor'";
                    break;
                case 'bool':
                    $ret = $valor ? 'true' : 'false';
                    break;
                case 'array':
                    $ret = '[' . implode(', ', $valor) . ']';
                    break;
                case 'data':
                    if(in_array($this->_tipo, array('pie', 'doughnut')) && false){
                        $ret = $this->formatarValor($campo, $valor, 'array');
                    }
                    else{
                        $dados_temp = array();
                        foreach ($this->_colunas as $coluna){
                            $dados_temp[] = $valor[$coluna] ?? 0;
                        }
                        $ret = $this->formatarValor($campo, $dados_temp, 'array');
                    }
                default:
                break;
            }
        }
        else{
            $ret = $valor;
        }
        return $ret;
    }
    
    protected function montarJsDataSet(){
        $ret = "
var dados_grafico_{$this->_id} = {
      labels  : " . $this->montarListaLabels() . ",
      datasets: [
        ";
        if(in_array($this->_tipo, array('pie', 'doughnut')) && false){
            $ret .= $this->montarDataSetIndividual(array_pop($this->_dataSet));
        }
        else{
            foreach ($this->_dataSet as $ds){
                $ret .= $this->montarDataSetIndividual($ds);
            }
        }
        $ret .= "
      ]
    }
";
        return $ret;
    }
    
    protected function montarJsGrafico(){
        $ret = "
    var op_grafico_{$this->_id} = {
        responsive              : true,
        maintainAspectRatio     : false,
        datasetFill             : false,
        plugins: {
            tooltip: {
                callbacks: {
                    " . $this->montarJsCallback() . "
                }
            }
        },
        ";
        if($this->_inteiros){
            $ret .= "        scales: {
            y: {
                ticks: {
                    // Include a dollar sign in the ticks
                    callback: function(value, index, ticks) {
                        if (value % 1 == 0) {
                            return '' + value;
                        }
                        else{
                            return '';
                        }
                    }
                }
            },
        }";
        }
        $ret .= "
    }
    var {$this->_id} = document.getElementById('{$this->_id}')
    new Chart({$this->_id}, {
        type: '{$this->_tipo}',
        data:  dados_grafico_{$this->_id},
        options: op_grafico_{$this->_id},
    })
";
        /*
         * 
         * plugins: [
            autocolors,
      ]
         * */
        return $ret;
    }
    
    private function montarJsCallback(){
        $ret = '';
        if(count($this->_callback) > 0){
            foreach ($this->_callback as $campo => $funcao){
                $temp = $campo . ': function(context) {
' . $funcao . '
        },';
                $ret .= $temp;
            }
        }
        return $ret;
    }
    
    private function addCallback($campo, $valor){
        $this->_callback[$campo] = $valor;
    }
    
    function setFormatacaoLabelDireto($formatacao){
        $this->addCallback('label', $formatacao);
    }
    
    function setFormatacaoLabelPreSuf($prefixo = '', $sufixo = ''){
        if(!empty($prefixo) || !empty($sufixo)){
            $funcao = $this->_formatacoesLabel['formatacao_presuf'];
            $funcao = str_replace(array('prefixo', 'sufixo'), array($prefixo, $sufixo), $funcao);
            $this->setFormatacaoLabelDireto($funcao);
        }
    }
    
    function setEscalaInteiros($inteiros = true){
        $this->_inteiros = $inteiros;
    }
    
    function __toString(){
        $js = $this->montarJsDataSet() . '
' . $this->montarJsGrafico();
        if($this->_tipo === 'doughnut'){
            //echo $js;
        }
        //addPortaljavaScript($this->montarJsDataSet());
        //addPortaljavaScript($this->montarJsGrafico());
        addPortaljavaScript($js, 'F');
        return '<canvas id="' . $this->_id . '" style="height:400px;"></canvas>';
    }
}