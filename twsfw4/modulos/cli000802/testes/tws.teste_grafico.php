<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class teste_grafico{
    var $funcoes_publicas = array(
        'index' 	=> true,
    );
    private $_programa = 'teste_grafico';
    
    public function __construct(){
        sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Ano', 'variavel' => 'ANO'		, 'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getAnosGrafico();'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Mês', 'variavel' => 'MES'		, 'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getMesesGrafico();'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        //sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Log bolhas', 'variavel' => 'LOG'		, 'tipo' => 'N', 'tamanho' => '8'));
    }
    
    public function index(){
        //addPortalJS('link', 'https://cdn.jsdelivr.net/npm/chartjs-plugin-autocolors');
        //addPortaljavaScript('const autocolors = window[\'chartjs-plugin-autocolors\'];', 'F');
        $ret = '';
        
       /* if(getAppVar('cliente_teste') === null){
            return addCard(array('conteudo' => 'Sem Dados'));
        }*/
        
        $filtro = new formfiltro01($this->_programa, array('tamanho' => 12));
        
        if(!$filtro->getPrimeira()){
            $dados_filtro = $filtro->getFiltro();
            $mes_filtro = $dados_filtro['MES'];
            $ano_filtro = $dados_filtro['ANO'];
            $meses = empty($mes_filtro) ? array_keys($this->getListaMeses()) : array($mes_filtro);
            $todos_meses = $this->getListaMeses();
            $anos  = empty($ano_filtro) ? $this->getAnos()       : array($ano_filtro);
            
            
            $dados = $this->getDadosTabela1($anos);
            foreach ($anos as $ano){
                $grafico = new graficos_chart('bar');
                foreach ($todos_meses as $codigo => $nome){
                    $grafico->addColuna(array('campo' => $codigo, 'etiqueta' => $nome));
                }
                $grafico->setFormatacaoLabelPreSuf('R$ ');
                $data_set = array(
                    'label' => 'Vl. Condenação',
                    'backgroundColor' => 'rgba(60,141,188,0.9)',
                    'borderColor' => 'rgba(60,141,188,0.8)',
                    'pointRadius' => false,
                    'pointColor' => '#3b8bba',
                    'pointStrokeColor' => 'rgba(60,141,188,1)',
                    'pointHighlightFill' => '#fff',
                    'pointHighlightStroke' => 'rgba(60,141,188,1)',
                    'data' => $dados[$ano]['condenacao'],
                );
                $grafico->addDataSet($data_set);
                
                $data_set = array(
                    'label' => 'Vl. Depositado',
                    'backgroundColor' => 'rgba(210, 214, 222, 1)',
                    'borderColor' => 'rgba(210, 214, 222, 1)',
                    'pointRadius' => false,
                    'pointColor' => 'rgba(210, 214, 222, 1)',
                    'pointStrokeColor' => '#c1c7d1',
                    'pointHighlightFill' => '#fff',
                    'pointHighlightStroke' => 'rgba(220,220,220,1)',
                    'data' => $dados[$ano]['deposito'],
                );
                $grafico->addDataSet($data_set);
                $ret .= addCard(array('titulo' => "Condenação e Depositos $ano", 'conteudo' => $grafico . ''));
            }
            
            $dados_tabela_2e3 = $this->getDadosTabela2e3($meses, $anos);
            $dados_tabela_2 = $dados_tabela_2e3[0];
            $dados_tabela_3 = $dados_tabela_2e3[1];
            
            $lista_name_phase = $this->getAllNamePhase();
            
            $grafico = new graficos_chart('doughnut');
            foreach ($lista_name_phase as $codigo => $nome){
                $grafico->addColuna(array('campo' => $codigo, 'etiqueta' => $nome));
            }
            $grafico->setFormatacaoLabelPreSuf('R$ ');
            $grafico->addDataSet(array(
                'data' => $dados_tabela_2,
            ));
            $ret .= addCard(array('titulo' => "Valor Condenado por Fase", 'conteudo' => $grafico . ''));
            
            $grafico = new graficos_chart('doughnut');
            foreach ($lista_name_phase as $codigo => $nome){
                $grafico->addColuna(array('campo' => $codigo, 'etiqueta' => $nome));
            }
            
            $grafico->addDataSet(array(
                'data' => $dados_tabela_3,
            ));
            $ret .= addCard(array('titulo' => "Num. Processos por Fase", 'conteudo' => $grafico . ''));
            
            
            $dados = $this->getDadosTabela4();
            foreach ($anos as $ano){
                $grafico = new graficos_chart('line');
                $grafico->setEscalaInteiros(true);
                foreach ($todos_meses as $codigo => $nome){
                    $grafico->addColuna(array('campo' => $codigo, 'etiqueta' => $nome));
                }
                foreach ($lista_name_phase as $codigo => $nome){
                    $data_set = array(
                        'label' => $nome,
                        'data' => $dados[$ano][$codigo],
                    );
                    $grafico->addDataSet($data_set);
                }
                $ret .= addCard(array('titulo' => "Num. Processos por Fase $ano", 'conteudo' => $grafico . ''));
            }
            
            $dados_mapa = $this->getDadosMapaQuantidade($anos, $meses);
            $mapa = new graficos_jqvmap($dados_mapa);
            $ret .= addCard(array('titulo' => 'Mapa','conteudo' => $mapa . ''));
            
            $mapa = new graficos_anychart_mapa($dados_mapa);
            $ret .= addCard(array('titulo' => 'Mapa2','conteudo' =>  $mapa . ''));
            
            /*
             $total_mapa = 0;
             $num = count($dados_mapa);
             foreach ($dados_mapa as $d){
             $total_mapa += $d;
             }
             echo '------------------------------------------------------media mapa = ' . ($total_mapa / $num);
             $total_mapa -= $dados_mapa['rs'];
             echo '------------------------------------------------------media mapa sem RS= ' . ($total_mapa / $num);
             */
            
            //$grupos_teste = teste_cores($dados_mapa, 4);
            //var_dump($grupos_teste);
        }
        
        /*
        else{
            $meses = array_keys($this->getListaMeses());
            $todos_meses = $this->getListaMeses();
            $anos = $this->getAnos();
        }
        */
        
        
        
        $ret = $filtro . $ret;
        
        
        
        
        $ret = addCard(array('conteudo' => $ret));
        
        return $ret;
    }
    
    private function getListaMeses(){
        $ret = array(
            '01'   =>   'Janeiro'   ,
            '02'   =>   'Fevereiro' ,
            '03'   =>   'Março'     ,
            '04'   =>   'Abril'     ,
            '05'   =>   'Maio'      ,
            '06'   =>   'Junho'     ,
            '07'   =>   'Julho'     ,
            '08'   =>   'Agosto'    ,
            '09'   =>   'Setembro'  ,
            '10'   =>   'Outubro'   ,
            '11'   =>   'Novembro'  ,
            '12'   =>   'Dezembro'  ,
        );
        return $ret;
    }
    
    private function getDadosTabela1($anos){
        $ret = array();
        $sql_base = '
            SELECT mes
            	,FLOOR(sum(condenacao)) AS total_condenacao
            	,FLOOR(sum(depositado)) AS total_deposito
            FROM (
            	SELECT SUBSTRING(data_last_updated_of_probability, 6, 2) AS mes
            		,COALESCE(valorcondenacao, 0) AS condenacao
            		,COALESCE(total_deposit_value, 0) AS depositado
            	FROM processos
            	WHERE data_last_updated_of_probability LIKE \'parametro/%\'
            	) tmp1
            GROUP BY mes;';
        //$meses = $this->montaListaMeses();
        foreach ($anos as $ano){
            $sql = str_replace('parametro', $ano, $sql_base);
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $valor_condenacao = $row['total_condenacao'];
                    $valor_deposito = $row['total_deposito'];
                    $mes = $row['mes'];
                    $ret[$ano]['condenacao'][$mes] = intval($valor_condenacao);
                    $ret[$ano]['deposito'][$mes] = intval($valor_deposito);
                    
                    //$ret[$ano]['condenacao'][$meses[$mes]] = $valor_condenacao;
                    //$ret[$ano]['deposito'][$meses[$mes]] = $valor_deposito;
                }
            }
        }
        return $ret;
    }
    
    private function getAnos(){
        $ret = array();
        $sql = "select distinct SUBSTRING(data_last_updated_of_probability, 1, 4) as ano from processos WHERE data_last_updated_of_probability NOT LIKE '%2014%'
            		AND data_last_updated_of_probability NOT LIKE '2015/%' and data_last_updated_of_probability NOT LIKE '2014/%' order by ano";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['ano'];
            }
        }
        return $ret;
    }
    
    private function getDadosTabela2e3($meses, $anos){
        $ret = array();
        $sql = "SELECT namephase as nome, count(*) as contador, coalesce(FLOOR(sum(valorcondenacao)), 0) as valor FROM processos";
        $where = array();
        $where[] = 'namephase is not null';
        
        $where_meses = array();
        foreach ($meses as $mes){
            $where_meses[] = "data_last_updated_of_probability like '%/$mes/%'";
        }
        if(count($where_meses) > 0){
            $where[] = '(' . implode(' OR ', $where_meses) . ')';
        }
        
        $where_ano = array();
        foreach ($anos as $ano){
            $where_ano[] = "data_last_updated_of_probability like '$ano/%'";
        }
        if(count($where_ano) > 0){
            $where[] = '(' . implode(' OR ', $where_ano) . ')';
        }
        
        if(count($where) > 0){
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $sql .= ' group by namephase';
        
        $rows = query($sql);
        $lista_codigos = $this->getAllNamePhase(true);
        foreach ($lista_codigos as $codigo){
            $ret[0][$codigo] = 0;
            $ret[1][$codigo] = 0;
        }
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $codigo = $lista_codigos[$row['nome']];
                $ret[0][$codigo] = $row['valor'];
                $ret[1][$codigo] = $row['contador'];
            }
        }
        return $ret;
    }
    
    private function getDadosTabela4(){
        $ret = array();
        $sql_base = "
SELECT mes
	,nome
	,count(*) AS total
FROM (
	SELECT SUBSTRING(data_last_updated_of_probability, 6, 2) AS mes
		,processos.namephase AS nome
	FROM `processos`
	WHERE namephase IS NOT NULL
		AND data_last_updated_of_probability LIKE 'parametro/%'
	) tmp1
GROUP BY mes
	,nome;
";
        $anos = $this->getAnos();
        $etapas = $this->getAllNamePhase(true);
        foreach ($anos as $ano){
            foreach ($etapas as $etapa_atual){
                $ret[$ano][$etapa_atual]['01'] = 0;
                $ret[$ano][$etapa_atual]['02'] = 0;
                $ret[$ano][$etapa_atual]['03'] = 0;
                $ret[$ano][$etapa_atual]['04'] = 0;
                $ret[$ano][$etapa_atual]['05'] = 0;
                $ret[$ano][$etapa_atual]['06'] = 0;
                $ret[$ano][$etapa_atual]['07'] = 0;
                $ret[$ano][$etapa_atual]['08'] = 0;
                $ret[$ano][$etapa_atual]['09'] = 0;
                $ret[$ano][$etapa_atual]['10'] = 0;
                $ret[$ano][$etapa_atual]['11'] = 0;
                $ret[$ano][$etapa_atual]['12'] = 0;
            }
            $sql = str_replace('parametro', $ano, $sql_base);
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[$ano][$etapas[$row['nome']]][$row['mes']] = intval($row['total']);
                }
            }
        }
        return $ret;
    }
    
    private function getAllNamePhase($invertido = false){
        $ret = array();
        $sql = "SELECT distinct namephase as nome FROM processos WHERE namephase is not null";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $codigo = $this->montarCodigoNamePhase($row['nome']);
                if($invertido){
                    $ret[$row['nome']] = $codigo;
                }
                else{
                    $ret[$codigo] = $row['nome'];
                }
            }
        }
        return $ret;
    }
    
    private function montarCodigoNamePhase($name){
        return preg_replace('/[^abcdefghijklmnopqrstuvwxyzç0-9]/', '', strtolower($name));
    }
    
    private function montaListaMeses($modo = true){
        $ret = array();
        if($modo){
            $ret = array(
                '01' => 'jan',
                '02' => 'fev',
                '03' => 'mar',
                '04' => 'abr',
                '05' => 'mai',
                '06' => 'jun',
                '07' => 'jul',
                '08' => 'ago',
                '09' => 'set',
                '10' => 'out',
                '11' => 'nov',
                '12' => 'dec',
            );
        }
        return $ret;
    }
    
    private function getDadosMapaQuantidade($anos, $meses){
        $ret = array();
        
        $sql = "SELECT namestate, count(*) as total FROM processos ";
        
        $where_meses = array();
        foreach ($meses as $mes){
            $where_meses[] = "data_last_updated_of_probability like '%/$mes/%'";
        }
        if(count($where_meses) > 0){
            $where[] = '(' . implode(' OR ', $where_meses) . ')';
        }
        
        $where_ano = array();
        foreach ($anos as $ano){
            $where_ano[] = "data_last_updated_of_probability like '$ano/%'";
        }
        if(count($where_ano) > 0){
            $where[] = '(' . implode(' OR ', $where_ano) . ')';
        }
        
        if(count($where) > 0){
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $sql .= ' group by namestate';
        $sql = "select lower(namestate) as estado, total from ($sql) tmp1";
        
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['estado']] = $row['total'];
            }
        }
        
        return $ret;
    }
    
    public function index2(){
        addPortalJS('', 'chart.js', 'I');
        //addPortalJS('link', 'https://cdn.jsdelivr.net/npm/chart.js@4.2.1/dist/chart.umd.min.js');
        //addPortalJS('link', 'https://cdn.jsdelivr.net/npm/chart.js');
        
        /*
        $javascript = "
const ctx = document.getElementById('myChart');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: '# of Votes',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });";
        addPortaljavaScript($javascript, 'F');
        
        return '<canvas id="myChart"></canvas>';
        */
        $java2 = "
'use strict'
    var ticksStyle = {
        fontColor: '#495057',
        fontStyle: 'bold'
    }
    var mode = 'index'
    var intersect = true
    var ssalesChart = document.getElementById('sales-chart')
    var salesChart = new Chart(ssalesChart,{
        type: 'bar',
        data: {
            labels: ['JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
            datasets: [{
                label: 'Digital Goods',
                backgroundColor: '#007bff',
                borderColor: '#007bff',
                data: [1000, 2000, 3000, 2500, 2700, 2500, 3000]
            }, {
                label: 'Electronics',
                backgroundColor: '#ced4da',
                borderColor: '#ced4da',
                data: [700, 1700, 2700, 2000, 1800, 1500, 2000]
            }]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: mode,
                intersect: intersect
            },
            hover: {
                mode: mode,
                intersect: intersect
            },
            legend: {
                display: false
            },
            scales: {
                y: {
                    gridLines: {
                        display: true,
                        lineWidth: '4px',
                        color: 'rgba(0, 0, 0, .2)',
                        zeroLineColor: 'transparent'
                    },
                    ticks: $.extend({
                        beginAtZero: true,
                        callback: function(value) {
                            if (value >= 1000) {
                                value /= 1000
                                value += 'k'
                            }
                            return '$' + value
                        }
                    }, ticksStyle)
                },
                x: {
                    display: true,
                    gridLines: {
                        display: false
                    },
                    ticks: ticksStyle
                }
            }
        }
    })";
        
        $java = "
var areaChartData = {
      labels  : ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
      datasets: [
        {
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
        {
          label               : 'Electronics',
          backgroundColor     : 'rgba(210, 214, 222, 1)',
          borderColor         : 'rgba(210, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#c1c7d1',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : [65, 59, 80, 81, 56, 55, 40]
        },
      ]
    }

//var barChartCanvas = $('#sales-chart').get(0).getContext('2d')
var barChartCanvas = document.getElementById('sales-chart')
    var barChartData = $.extend(true, {}, areaChartData)
    var temp0 = areaChartData.datasets[0]
    var temp1 = areaChartData.datasets[1]
    barChartData.datasets[0] = temp1
    barChartData.datasets[1] = temp0

    var barChartOptions = {
      responsive              : true,
      maintainAspectRatio     : false,
      datasetFill             : false
    }

    new Chart(barChartCanvas, {
      type: 'bar',
      data: barChartData,
      options: barChartOptions
    })
";
        
        

        
        addPortaljavaScript($java2, 'F');
        
        //return '<canvas id="sales-chart"></canvas>';
        
        $grafico = new graficos_chart('line');
        $grafico->addColuna(array('campo' => 'jan', 'etiqueta' => 'January'));
        $grafico->addColuna(array('campo' => 'fev', 'etiqueta' => 'February'));
        $grafico->addColuna(array('campo' => 'mar', 'etiqueta' => 'March'));
        $grafico->addColuna(array('campo' => 'abr', 'etiqueta' => 'April'));
        $grafico->addColuna(array('campo' => 'mai', 'etiqueta' => 'May'));
        $grafico->addColuna(array('campo' => 'jun', 'etiqueta' => 'June'));
        $grafico->addColuna(array('campo' => 'jul', 'etiqueta' => 'July'));
        
        $data_set = array(
            'label' => 'Digital Goods',
            'backgroundColor' => 'rgba(60,141,188,0.9)',
            'borderColor' => 'rgba(60,141,188,0.8)',
            'pointRadius' => false,
            'pointColor' => '#3b8bba',
            'pointStrokeColor' => 'rgba(60,141,188,1)',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(60,141,188,1)',
            'data' => array(
                'jan' => 28, 
                'fev' => 48, 
                'mar' => 40, 
                'abr' => 19, 
                'mai' => 86, 
                'jun' => 27, 
                'jul' => 90),
        );
        $grafico->addDataSet($data_set);
        
        $data_set = array(
            'label' => 'Electronics',
            'backgroundColor' => 'rgba(210, 214, 222, 1)',
            'borderColor' => 'rgba(210, 214, 222, 1)',
            'pointRadius' => false,
            'pointColor' => 'rgba(210, 214, 222, 1)',
            'pointStrokeColor' => '#c1c7d1',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(220,220,220,1)',
            'data' => array(
                'jan' => 65, 
                'fev' => 59, 
                'mar' => 80, 
                'abr' => 81, 
                'mai' => 56, 
                'jun' => 55, 
                'jul' => 40
            ),
        );
        $grafico->addDataSet($data_set);
        
        $grafico2 = new graficos_chart('bar');
        $grafico2->addColuna(array('campo' => 'jan', 'etiqueta' => 'January'));
        $grafico2->addColuna(array('campo' => 'fev', 'etiqueta' => 'February'));
        $grafico2->addColuna(array('campo' => 'mar', 'etiqueta' => 'March'));
        $grafico2->addColuna(array('campo' => 'abr', 'etiqueta' => 'April'));
        $grafico2->addColuna(array('campo' => 'mai', 'etiqueta' => 'May'));
        $grafico2->addColuna(array('campo' => 'jun', 'etiqueta' => 'June'));
        $grafico2->addColuna(array('campo' => 'jul', 'etiqueta' => 'July'));
        
        $data_set = array(
            'label' => 'Digital Goods',
            'backgroundColor' => 'rgba(60,141,188,0.9)',
            'borderColor' => 'rgba(60,141,188,0.8)',
            'pointRadius' => false,
            'pointColor' => '#3b8bba',
            'pointStrokeColor' => 'rgba(60,141,188,1)',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(60,141,188,1)',
            'data' => array(
                'jan' => 28,
                'fev' => 48,
                'mar' => 40,
                'abr' => 19,
                'mai' => 86,
                'jun' => 27,
                'jul' => 90),
        );
        $grafico2->addDataSet($data_set);
        
        $data_set = array(
            'label' => 'Electronics',
            'backgroundColor' => 'rgba(210, 214, 222, 1)',
            'borderColor' => 'rgba(210, 214, 222, 1)',
            'pointRadius' => false,
            'pointColor' => 'rgba(210, 214, 222, 1)',
            'pointStrokeColor' => '#c1c7d1',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(220,220,220,1)',
            'data' => array(
                'jan' => 65,
                'fev' => 59,
                'mar' => 80,
                'abr' => 81,
                'mai' => 56,
                'jun' => 55,
                'jul' => 40
            ),
        );
        $grafico2->addDataSet($data_set);
        
        $grafico3 = new graficos_chart('doughnut');
        $grafico3->addColuna(array('campo' => 'jan', 'etiqueta' => 'January'));
        $grafico3->addColuna(array('campo' => 'fev', 'etiqueta' => 'February'));
        $grafico3->addColuna(array('campo' => 'mar', 'etiqueta' => 'March'));
        $grafico3->addColuna(array('campo' => 'abr', 'etiqueta' => 'April'));
        $grafico3->addColuna(array('campo' => 'mai', 'etiqueta' => 'May'));
        $grafico3->addColuna(array('campo' => 'jun', 'etiqueta' => 'June'));
        $grafico3->addColuna(array('campo' => 'jul', 'etiqueta' => 'July'));
        $grafico3->addDataSet(array(
            //'labels' => array("'January'", "'February'", "'March'", "'April'", "'May'", "'June'", "'July'",),
            'label' =>'pie1',
            'data' => array(
                'jan' => 65,
                'fev' => 59,
                'mar' => 80,
                'abr' => 81,
                'mai' => 56,
                'jun' => 55,
                'jul' => 40
            ),
        ));
        
        $grafico3->addDataSet(array(
            //'labels' => array("'January'", "'February'", "'March'", "'April'", "'May'", "'June'", "'July'",),
            'label' =>'pie2',
            'data' => array(
                'jan' => 1,
                'fev' => 2,
                'mar' => 3,
                'abr' => 4,
                'mai' => 5,
                'jun' => 6,
                'jul' => 7
            ),
        ));
        
        $grafico4 = new graficos_chart('pie');
        $grafico4->addColuna(array('campo' => 'jan', 'etiqueta' => 'January'));
        $grafico4->addColuna(array('campo' => 'fev', 'etiqueta' => 'February'));
        $grafico4->addColuna(array('campo' => 'mar', 'etiqueta' => 'March'));
        $grafico4->addColuna(array('campo' => 'abr', 'etiqueta' => 'April'));
        $grafico4->addColuna(array('campo' => 'mai', 'etiqueta' => 'May'));
        $grafico4->addColuna(array('campo' => 'jun', 'etiqueta' => 'June'));
        $grafico4->addColuna(array('campo' => 'jul', 'etiqueta' => 'July'));
        $grafico4->addDataSet(array(
            //'labels' => array("'January'", "'February'", "'March'", "'April'", "'May'", "'June'", "'July'",),
            'label' => 'p1',
            'data' => array(
                'jan' => 65,
                'fev' => 59,
                'mar' => 80,
                'abr' => 81,
                'mai' => 56,
                'jun' => 55,
                'jul' => 40
            ),
        ));
        
        $grafico4->addDataSet(array(
            //'labels' => array("'January'", "'February'", "'March'", "'April'", "'May'", "'June'", "'July'",),
            'label' => 'p2',
            'data' => array(
                'jan' => 1,
                'fev' => 2,
                'mar' => 3,
                'abr' => 4,
                'mai' => 5,
                'jun' => 6,
                'jul' => 7
            ),
        ));
        
        $grafico4->addDataSet(array(
            //'labels' => array("'January'", "'February'", "'March'", "'April'", "'May'", "'June'", "'July'",),
            'label' => 'p3',
            'data' => array(
                'jan' => 11,
                'fev' => 2,
                'mar' => 3,
                'abr' => 44,
                'mai' => 5,
                'jun' => 6,
                'jul' => 70
            ),
        ));
        
        
        return addCard(array('titulo' => 'Gráfico de Barras', 'conteudo' => $grafico2 . '')) 
        . addCard(array('titulo' => 'Gráfico de Linhas', 'conteudo' => $grafico . ''))
        . addCard(array('titulo' => 'Gráfico Doughnut', 'conteudo' => $grafico3 . ''))
        . addCard(array('titulo' => 'Gráfico Torta', 'conteudo' => $grafico4 . ''));
        return $grafico . '<canvas id="sales-chart"></canvas>';
        return $grafico . '';
    }
}

function teste_cores($dados, $num_grupos){
    $grupos = array();
    
    $chaves = array_rand($dados, $num_grupos);
    
    $valores = array();
    
    for ($i = 0; $i < $num_grupos; $i++) {
        $valores[$i] = 0;
        $grupos[$i][] = $chaves[$i];
    }
    $mudanca = true;
    while($mudanca){
        $mudanca = false;
        
        for ($i = 0; $i < $num_grupos; $i++) {
            $novo_valor = 0;
            foreach ($grupos[$i] as $chave_atual){
                $novo_valor += $dados[$chave_atual];
            }
            $novo_valor = $novo_valor / count($grupos[$i]);
            if($novo_valor != $valores[$i]){
                $mudanca = true;
                $valores[$i] = $novo_valor;
            }
        }
        
        $grupos = array();
        foreach ($dados as $estado => $valor){
            $valor_minimo = PHP_INT_MAX;
            $grupo = '';
            for ($i = 0; $i < $num_grupos; $i++) {
                $valor_teste = abs($valores[$i] - $valor);
                if($valor_teste < $valor_minimo){
                    $valor_minimo = $valor_teste;
                    $grupo = $i;
                }
            }
            if($grupo !== ''){
                $grupos[$grupo][] = $estado;   
            }
        }
    }
    
    return $grupos;
}

function getMesesGrafico(){
    $ret = array(
        array(''   ,  ''   ,),
        array('01'   ,  'Janeiro'   ,),
        array('02'   ,  'Fevereiro' ,),
        array('03'   ,  'Março'     ,),
        array('04'   ,  'Abril'     ,),
        array('05'   ,  'Maio'      ,),
        array('06'   ,  'Junho'     ,),
        array('07'   ,  'Julho'     ,),
        array('08'   ,  'Agosto'    ,),
        array('09'   ,  'Setembro'  ,),
        array('10'   ,  'Outubro'   ,),
        array('11'   ,  'Novembro'  ,),
        array('12'   ,  'Dezembro'  ,),
    );
    return $ret;
}

function getAnosGrafico(){
    $ret = array();
    $sql = "select distinct SUBSTRING(data_last_updated_of_probability, 1, 4) as ano from processos WHERE data_last_updated_of_probability NOT LIKE '%2014%'
            		AND data_last_updated_of_probability NOT LIKE '2015/%' and data_last_updated_of_probability NOT LIKE '2014/%' order by ano";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        $ret[] = array('', '');
        foreach ($rows as $row){
            $ret[] = array($row['ano'], $row['ano']);
        }
    }
    return $ret;
}