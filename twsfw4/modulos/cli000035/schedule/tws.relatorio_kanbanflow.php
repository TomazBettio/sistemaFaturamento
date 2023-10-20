<?php

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
 ini_set('display_errors',1);
 ini_set('display_startup_erros',1);
 error_reporting(E_ALL);
 set_time_limit(0);
class relatorio_kanbanflow{
    var $funcoes_publicas = array(
        'schedule' 	=> true,
        'index'     => true,
    );
    
    private $_listaPl;
    private $_chaves;
    private $_colunas;
    private $_usuarios;
    private $_agrupamentoEtapas;
    private $_nomesAgrupamentos;
    private $_nomesRelatorioGeral;
    private $_timeZone;
    
    function __construct(){
        $this->_listaPl = array(
            '138'  => array(
                array('nome' => 'Tatiane Vilella C de Andrade'    , 'email' => 'tatiane.andrade@itau-unibanco.com.br'),
            ),
            '1000' => array(
                array('nome' => 'Tuany Suter'                     , 'email' => 'tuany.suter@itau-unibanco.com.br'),
                array('nome' => 'LUIZ CLAUDIO DE OLIVEIRA GOMES'                     , 'email' => 'luiz.oliveira-gomes@itau-unibanco.com.br'),
                array('nome' => 'FERNANDA HELENA DO NASCIMENTO'                     , 'email' => 'fernanda.salles-rocha@itau-unibanco.com.br'),
                array('nome' => 'PAULA ALVES LIMA BRUM'                     , 'email' => 'paula.brum@itau-unibanco.com.br'),
                array('nome' => 'CRISTINA RENATA DO SOUTO KAWAH'                     , 'email' => 'cristina.souto@itau-unibanco.com.br'),
                array('nome' => 'ARISTOTELES SILVA NETO'                     , 'email' => 'aristoteles.silva@itau-unibanco.com.br'),
                array('nome' => 'RAFAELLA SARAIVA PINTO DOS SANTOS'                     , 'email' => 'rafaella.pinto-santos@itau-unibanco.com.br'),
            ),
            '45'   => array(
                array('nome' => 'Thiago Evandro Martão'           , 'email' => 'thiago.martao@itau-unibanco.com.br'),
            ),
            '23'   => array(
                array('nome' => 'Alvaro Souza'                    , 'email' => 'alvaro.souza-azevedo@itau-unibanco.com.br'),
                array('nome' => 'FERNANDA GARRETT DA COSTA PINH'                    , 'email' => 'fernanda.costa@itau-unibanco.com.br'),
                array('nome' => 'TATIANA MEDEIROS ROCHA'                    , 'email' => 'tatiana.rocha@itau-unibanco.com.br'),
                array('nome' => 'FERNANDA DE MORAES VIEIRA'                    , 'email' => 'fernanda.moraes-vieira@itau-unibanco.com.br'),
                array('nome' => 'CIRA DA SILVA GUIMARAES'                    , 'email' => 'cira.guimaraes@itau-unibanco.com.br'),
                array('nome' => 'HENDRIX AZEVEDO DE CARVALHO'                    , 'email' => 'hendrix.carvalho@itau-unibanco.com.br'),
                array('nome' => 'CLAUDIO MARCIO DA SILVA'                    , 'email' => 'claudio.c.silva@itau-unibanco.com.br'),
                array('nome' => 'RELVIS SARDINHA DIAS DE AZEVED'                    , 'email' => 'relvis.azevedo@itau-unibanco.com.br'),
                array('nome' => 'HUGO MARQUES DA SILVA'                    , 'email' => 'hugo-marques.silva1@itau-unibanco.com.br'),
            ),
            '9'    => array(
                array('nome' => 'Paulo Rogério'                   , 'email' => 'paulo.rogerio@itau-unibanco.com.br'),
                array('nome' => 'ANDERSON RICHIARDI DOS SANTOS'                   , 'email' => 'anderson-richard.santos@itau-unibanco.com.br'),
                array('nome' => 'GISELLE OLIVEIRA ALMEIDA DE AN'                   , 'email' => 'giselle.almeida@itau-unibanco.com.br'),
                array('nome' => 'ANA JULIA BAUER'                   , 'email' => 'ana-julia.bauer@itau-unibanco.com.br'),
                array('nome' => 'CAROLINE CHAMBA DOMINGOS'          , 'email' => 'caroline.domingos@itau-unibanco.com.br'),
                array('nome' => 'LUIZ ALVARO DE GOES FILHO'                   , 'email' => 'luiz.goes@itau-unibanco.com.br'),
                array('nome' => 'THIAGO CESAR DA SILVA'                   , 'email' => 'thiago.e.silva@itau-unibanco.com.br'),
                array('nome' => 'THIAGO PERLUIZ GOMES'                   , 'email' => 'thiago.gomes@userede.com.br'),
            ),
            '480'  => array(
                array('nome' => 'Valdinei Simas'                  , 'email' => 'valdinei.simas@itau-unibanco.com.br'),
            ),
            '327'  => array(
                array('nome' => 'Franceli Alberti'                , 'email' => 'franceli.alberti@itau-unibanco.com.br'),
                array('nome' => 'JULIANE AVILA MARQUETOTTI'                , 'email' => 'juliane.marquetotti@itau-unibanco.com.br'),
                array('nome' => 'ANGELA DA ROCHA BARBOSA PARAVI'                , 'email' => 'angela-rocha-barbosa.paravizi@itau-unibanco.com.br'),
                array('nome' => 'JULIO SENGER'                , 'email' => 'julio.senger@itau-unibanco.com.br'),
                array('nome' => 'MARCIO EDISON BEAL'                , 'email' => 'marcio.beal@itau-unibanco.com.br'),
                array('nome' => 'JOEL RENATO DA SILVA BOLES'                , 'email' => 'joel.boles@itau-unibanco.com.br'),
            ),
            //'327'  => array('nome' => 'Marcelo Basso'                   , 'email' => 'marcelo.basso-silva@itau-unibanco.com.br'),
            '57'   => array(
                array('nome' => 'Adriano Vyborny'                 , 'email' => 'adriano.vyborny@itau-unibanco.com.br'),
                array('nome' => 'MARCUS ANTONIO SAVIAN'                 , 'email' => 'marcus.savian@itau-unibanco.com.br'),
                array('nome' => 'WILLIAN MARTINS LIMA'                 , 'email' => 'willian-martins.lima@itau-unibanco.com.br'),
                array('nome' => 'RODRIGO NOGUEIRA DEGAKI'                 , 'email' => 'rodrigo.degaki@itau-unibanco.com.br'),
                array('nome' => 'TANIA REGINA BECARINI'                 , 'email' => 'tania.becarini@itau-unibanco.com.br'),
                array('nome' => 'BRUNO DA SILVA BARRETTA'                 , 'email' => 'bruno.barretta@itau-unibanco.com.br'),
                array('nome' => 'RODRIGO STEFANINI DE CASTRO'                 , 'email' => 'rodrigo.castro@itau-unibanco.com.br'),
                array('nome' => 'LEANDRO TAPPIS POZENATO'                 , 'email' => 'leandro.pozenato@itau-unibanco.com.br'),
            ),
            '1338' => array(
                array('nome' => 'Joaquim Bezerra'                 , 'email' => 'joaquim.bezerra@itau-unibanco.com.br'),
                array('nome' => 'RAFAELLA PINHEIRO DA ROCHA'                 , 'email' => 'rafaella.rocha@itau-unibanco.com.br'),
                array('nome' => 'KIRLIAN PALACIO LEITE GOMES'                 , 'email' => 'kirlian.gomes@itau-unibanco.com.br'),
                array('nome' => 'ANTONIO ELTON MONTEIRO DA SILV'                 , 'email' => 'antonio.monteiro-silva@itau-unibanco.com.br'),
                array('nome' => 'EDUARDO PINHEIRO PEQUENO'                 , 'email' => 'eduardo.pequeno@itau-unibanco.com.br'),
                array('nome' => 'MIRELA RODRIGUES PRUDENTE DE A'                 , 'email' => 'mirela.almeida@itau-unibanco.com.br'),
                array('nome' => 'LINCOLN PESSOA REBOUÇAS'                 , 'email' => 'lincoln.reboucas@itau-unibanco.com.br'),
            ),
            '936'  => array(
                array('nome' => 'Kelly Negreiros Serruya'         , 'email' => 'kelly.serruya@itau-unibanco.com.br'),
            ),
            '7140' => array(
                array('nome' => 'Vinícius Lopes Felipe'           , 'email' => 'vinicius.felipe@itau-unibanco.com.br'),
            ),
            '654'  => array(
                array('nome' => 'Rodrigo Prietto'                 , 'email' => 'rodrigo.prietto@itau-unibanco.com.br'),
            ),
            //'654'  => array('nome' => 'Jair Simões'                     , 'email' => 'jair-simoes.coelho@itau-unibanco.com.br'),
            '5665' => array(
                array('nome' => 'Guilherme Sevija Silveira'       , 'email' => 'guilherme.sevija-silveira@itau-unibanco.com.br'),
            ),
            '3834' => array(
                array('nome' => 'Henrique Block'                  , 'email' => 'henrique.block@itau-unibanco.com.br'),
                array('nome' => 'PAULO CELSO DUTRA NETO'                  , 'email' => 'paulo.celso@itau-unibanco.com.br'),
                array('nome' => 'RAFAELA BARBOSA CUNHA'                  , 'email' => 'rafaelab.cunha@itau-unibanco.com.br'),
                array('nome' => 'RAFAEL ZOTTIS'                  , 'email' => 'rafael.zottis@itau-unibanco.com.br'),
                array('nome' => 'ALANNA DANTAS'                  , 'email' => 'alanna.dantas@itau-unibanco.com.br'),
                array('nome' => 'CLEBER GONCALVES HEUSER'                  , 'email' => 'cleber.heuser@itau-unibanco.com.br'),
                array('nome' => 'RAFAEL CRISTIANO GOMES VIEIRA'                  , 'email' => 'rafael.gomes-vieira@itau-unibanco.com.br'),
            ),
            //'3834' => array('nome' => 'Claudia Danieli'                 , 'email' => 'claudia.corradi@itau-unibanco.com.br'),
        );
        /*
        $this->_listaPl = array(
            '138'  => array('nome' => 'Tatiane Vilella C de Andrade', 'email' => 'emanuel.thiel@verticais.com.br'),
            '1000' => array('nome' => 'Tuany Suter', 'email' => 'emanuel.thiel@verticais.com.br'),
            '45'   => array('nome' => 'Thiago Evandro Martão', 'email' => 'emanuel.thiel@verticais.com.br'),
            '23'   => array('nome' => 'Alvaro Souza', 'email' => 'emanuel.thiel@verticais.com.br'),
            '9'    => array('nome' => 'Paulo Rogério', 'email' => 'emanuel.thiel@verticais.com.br'),
            '480'  => array('nome' => 'Valdinei Simas', 'email' => 'emanuel.thiel@verticais.com.br'),
            '327'  => array('nome' => 'Marcelo Basso', 'email' => 'emanuel.thiel@verticais.com.br'),
            '57'   => array('nome' => 'Adriano Vyborny', 'email' => 'emanuel.thiel@verticais.com.br'),
            '1338' => array('nome' => 'Joaquim Bezerra', 'email' => 'emanuel.thiel@verticais.com.br'),
            '936'  => array('nome' => 'Kelly Negreiros Serruya', 'email' => 'emanuel.thiel@verticais.com.br'),
            '7140' => array('nome' => 'Vinícius Lopes Felipe', 'email' => 'emanuel.thiel@verticais.com.br'),
            '654'  => array('nome' => 'Jair Simões', 'email' => 'emanuel.thiel@verticais.com.br'),
            '5665' => array('nome' => 'Guilherme Sevija Silveira', 'email' => 'emanuel.thiel@verticais.com.br'),
            '3834' => array('nome' => 'Claudia Danieli', 'email' => 'emanuel.thiel@verticais.com.br'),
        );
        */
        /*
        $this->_listaPl = array(
            '3834' => array(
                array('nome' => 'Claudia Danieli', 'email' => 'emanuel.thiel@verticais.com.br'),
            ),
        );
        */
        
        //$this->ajustarListaPl();
        
        $this->_chaves = array(
            'dMfuPksRQQYDCDpZcCdBthRdjY',
            'QiZJuXgAJVdApxermZXyMpGf1R',
            'CVa7QcJxLHihaK6CU5ZSbUowgx',
            'rk2QY1XxdLmtKopHfLEtoQ6RMX',
            'HWd1vkpgBTHNbuVR1kmnYefbLa',
            'mHWvQvdkCoFUV6kgTqrAwKwEuz',
            'xjsDLRVfMseT7uojpfQp12K33c',
            'bbGEVHVjYhbAwaWxHJhVApLjeb',
            'K5m4qxsyieD3M2TJpTjbKkaDw8',
            'VAahhqkXebQDfeE8HaqotmVBQS',
            'iJshDYjFKdheTa3xfNKvCfHCB7',
            'NeE4WjBbpNnUsgHBFq7jmkVa3z',
            'HUfg7TEEvmfmutdSZFUsUUnnyx',
            '4uuJc47xAecvVNeqcpGr948vVv',
        );
        
        //$this->_chaves = array('CVa7QcJxLHihaK6CU5ZSbUowgx');
        
        $this->_nomesRelatorioGeral = array(
            '327' =>  'Lucineia',
            '654' =>  'Lorena',
            '9' =>  'Cassia',
            '3834' =>  'Lucimara Rocha',
            '23' =>  'Rogério',
            '1338' =>  'Andrey Henrique',
            '57' =>  'Elieser',
            '1000' =>  'Rafael',
            '7140' =>  'Felix 2',
            '936' =>  'Felix',
            '45' =>  'Andrey Henrique 2',
            '480' =>  'Luiza',
            '5665' =>  'Camila',
            '138' =>  'Socorro Amorim',
        );
        
        $this->_agrupamentoEtapas = array(
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 1,
            7 => 2,
            8 => 2,
            9 => 2,
            10 => 3,
            11 => 3,
            12 => 4,
            13 => 4,
            14 => 4,
            15 => 4,
            16 => 5,
            17 => 5,
            18 => 5,
        );
        
        $this->_nomesAgrupamentos = array(
            1 => 'Primeiro Contato',
            2 => 'Aguardando Documentação',
            3 => 'Em Apuração',
            4 => 'Apurados/Concluídos',
            5 => 'Declinados',
        );
        
        $this->_timeZone = new DateTimeZone('America/Sao_Paulo');
    }
    
    private function relatorioPorPl(){
        global $config;
        
        $this->getColunas();
        $this->getUsuarios();
        
        $dados_brutos = $this->getDados();
        
        $dados_itau = array();
        
        foreach ($dados_brutos as $pl => $chaves){
            foreach ($chaves as $chave => $tarefas){
                foreach ($tarefas as $tarefa){
                    if($tarefa['aparecer_itau']){
                        $dados_itau[$pl][$chave][] = $tarefa;
                    }
                }
            }
        }
        
        $dados_pivo = $this->pivo($dados_itau);
        $dados = $this->montarDadosRelatorioFinal($dados_itau);
        
        $tabela = new relatorio01();
        
        $tabela->setParamTabela(array('ordenacao' => false), 0);
        $tabela->addColuna(array('campo' => 'name'       , 'etiqueta' => 'Cliente ITAÚ'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'cnpj'       , 'etiqueta' => 'CNPJ'                              , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'regime'     , 'etiqueta' => 'Regime Tributário'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'coluna'     , 'etiqueta' => 'Etapa'			                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'valor'      , 'etiqueta' => 'Valor'			                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //$tabela->addColuna(array('campo' => 'credito'    , 'etiqueta' => 'Crédito Aprovado'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //$tabela->addColuna(array('campo' => 'utilizacao' , 'etiqueta' => 'Forma de Recebimento do Benefício' , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'comentario' , 'etiqueta' => 'Comentário'                        , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //$tabela->addColuna(array('campo' => 'tempo'      , 'etiqueta' => 'Tempo nesta Etapa'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        
        
        $tabela->setParamTabela(array('ordenacao' => false), 1);
        $tabela->addColuna(array('campo' => 'nome'       , 'etiqueta' => 'Etapa'			        , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $tabela->addColuna(array('campo' => 'quantidade' , 'etiqueta' => 'Quantidade de Clientes'	, 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $tabela->addColuna(array('campo' => 'valor'      , 'etiqueta' => 'Valor Apurado'			, 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        
        foreach ($this->_listaPl as $pl => $destinatarios){
            if(isset($dados[$pl]) && isset($dados_pivo[$pl])){
                $dados_primeira_tabela = $dados[$pl];
                $dados_segunda_tabela = $dados_pivo[$pl];
                $tabela->setDados($dados_primeira_tabela);
                $tabela->setDados($dados_segunda_tabela, 1);
                $arquivo = 'resumo_atendimento_mgt_' . $pl;
                $tabela->setToExcel(true, $arquivo);
                $arquivo .= '.xlsx';
                $tabela->setTituloSecaoPlanilha(0, 'Resumo Por Cliente');
                $tabela->setTituloSecaoPlanilha(1, 'Resumo Por Etapa');
                $tabela . '';
                $anexos = array(
                    $config['tempPach'] . $arquivo,
                );
                $titulo_email = 'Resumo Kanbanflow - Agência ' . $pl;
                
                $mensagem = 'Prezado(a) Julia, segue em anexo o resumo do kanbanflow.';
                enviaEmailAntigo('julia.orcesi@itau-unibanco.com.br', $titulo_email, $mensagem, $anexos);
                foreach ($destinatarios as $dt){
                    $mensagem = 'Prezado(a) ' . $dt['nome'] . ', segue em anexo o resumo do kanbanflow.';
                    enviaEmailAntigo($dt['email'], $titulo_email, $mensagem, $anexos);
                    
                    //$tabela->enviaEmail($dt['email'], 'Resumo Kanbanflow - Agência ' . $pl, array('mensagem' => $mensagem));
                }
            }
        }
        
        return '';
    }
    
    public function index(){
        $this->relatorioPorPl();
        return '';
    }
    
    private function relatorioGeral(){
        $this->getColunas();
        $this->getUsuarios();
        
        $dados = $this->getDados();
        
        $tabela = new relatorio01();
        
        $tabela->setParamTabela(array('ordenacao' => false));
        $tabela->addColuna(array('campo' => 'pl'               , 'etiqueta' => 'Agencia'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'usuario'          , 'etiqueta' => 'Executivo'                       , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'cnpj'             , 'etiqueta' => 'CNPJ'                            , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'data_recebido'    , 'etiqueta' => 'Lead Recebido'                   , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'data_concluido'   , 'etiqueta' => 'Data de Entrega do Relatório'    , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'valor'            , 'etiqueta' => 'Crédito Apurado'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'credito'          , 'etiqueta' => 'Credito Aprovado'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'status'           , 'etiqueta' => 'Status'			                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'regime'           , 'etiqueta' => 'Regime Tributário'               , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        
        //$tabela->addColuna(array('campo' => 'ordem'            , 'etiqueta' => 'ordem'			                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        
        
        $dados_concatenados = array();
        foreach ($dados as $pl => $chaves){
            foreach ($chaves as $chave => $tarefas){
                $dados_concatenados = array_merge($dados_concatenados, $tarefas);
            }
        }
        
        /*
        $temp = array();
        $temp_com_valor = array();
        foreach ($dados_concatenados as $tarefa){
            $temp[$tarefa['ordem']][] = $tarefa;
            if($tarefa['credito_int'] != 0){
                $temp_com_valor[$tarefa['ordem']][] = $tarefa;
            }
        }
        $tarefas_12 = $temp[14] ?? array();
        $tarefas_12_com_valor = $temp_com_valor[14] ?? array();
        unset($temp[14]);
        unset($temp_com_valor[14]);
        krsort($temp);
        krsort($temp_com_valor);
        $tarefas_ordenadas = $tarefas_12;
        $tarefas_ordenadas_com_valor = $tarefas_12_com_valor;
        foreach ($temp as $tarefas_ordem_atual){
            $tarefas_ordenadas = array_merge($tarefas_ordenadas, $tarefas_ordem_atual);
        }
        foreach ($temp_com_valor as $tarefas_ordem_atual_com_valor){
            $tarefas_ordenadas_com_valor = array_merge($tarefas_ordenadas_com_valor, $tarefas_ordem_atual_com_valor);
        }
        */
        $tarefas_com_valor = array();
        $tarefas_sem_valor = array();
        foreach ($dados_concatenados as $tarefa){
            if($tarefa['credito_int'] >= 0 || $tarefa['valor_int'] > 0/* || $tarefa['ordem'] == 15*/){
                $tarefas_com_valor[] = $tarefa;
            }
            else{
                $tarefas_sem_valor[] = $tarefa;
            }
        }
        array_multisort(array_column($tarefas_com_valor, "credito_int"), SORT_DESC, $tarefas_com_valor );
        
        $tarefas_ordenadas = array_merge($tarefas_com_valor, $tarefas_sem_valor);
        
        /*
        $tabela->setDados($tarefas_ordenadas);
        $tabela->setToExcel(true, 'geral');
        $tabela . '';
        */
        
        $tabela->setDados($tarefas_com_valor);
        $tabela->setToExcel(true, 'relatorio');
        $email_copias = "corine.iwamura@itau-unibanco.com.br;julia.orcesi@itau-unibanco.com.br;gabriel.albuquerque-andrade@itau-unibanco.com.br;leandro@grupomarpa.com.br;luciano@grupomarpa.com.br";
        //$email_copias = "emanuel.thiel@verticais.com.br";
        $tabela->enviaEmail($email_copias, 'Informações de data e benefício por CNPJ', array('mensagem' => 'Segue em anexo os relatórios sobre as datas e benefícios separados pro CNPJ'));
        //$tabela . '';
        /*
        global $config;
        $anexos = array(
            //$config['tempPach'] . 'geral.xlsx',
            $config['tempPach'] . 'relatorio.xlsx',
        );
        //enviaEmailAntigo('julia.orcesi@itau-unibanco.com.br;luciano@grupomarpa.com.br;rodrigo.ximenes@verticais.com.br;leandro@grupomarpa.com.br', 'Informações de data e benefício por CNPJ', 'Segue em anexo os relatórios sobre as datas e benefícios separados pro CNPJ', $anexos);
        //enviaEmailAntigo('emanuel.thiel@verticais.com.br;rodrigo.ximenes@verticais.com.br', 'Informações de data e benefício por CNPJ', 'Segue em anexo os relatórios sobre as datas e benefícios separados pro CNPJ', $anexos);
        //enviaEmailAntigo('luciano@grupomarpa.com.br;joao.pereira@verticais.com.br;emanuel.thiel@verticais.com.br', 'Informações de data e benefício por CNPJ', 'Segue em anexo os relatórios sobre as datas e benefícios separados pro CNPJ', $anexos);
        enviaEmailAntigo('emanuel.thiel@verticais.com.br', 'Informações de data e benefício por CNPJ', 'Segue em anexo os relatórios sobre as datas e benefícios separados pro CNPJ', $anexos);
        
        //enviaEmailAntigo('emanuel.thiel@verticais.com.br', 'Informações de data e benefício por CNPJ', 'Segue em anexo os relatórios sobre as datas e benefícios separados pro CNPJ', $anexos);
        return $tabela . '';
        */
        return '';
    }
    
    private function getUsuarios(){
        $ret = array();
        foreach ($this->_chaves as $chave){
            $api = new integra_kanbanflow($chave);
            $dados = $api->getUsuariosBoard();
            if(is_array($dados) && count($dados) > 0){
                foreach ($dados as $d){
                    $ret[$chave][$d['_id']] = $d['fullName'];
                }
            }
        }
        $this->_usuarios = $ret;
    }
    
    private function addLabelGlobal($tarefas, $chave, $label){
        $api = new integra_kanbanflow($chave);
        foreach ($tarefas as $tarefa){
            $api->criarLabel($tarefa, $label);
        }
    }
    
    private function allTasksIds($colunas_excluir = array()){
        $ret = array();
        foreach ($this->_chaves as $chave){
            $dados = $this->getDadosBrutosFromBoard($chave);
            if(is_array($colunas_excluir) && count($colunas_excluir) > 0){
                foreach ($colunas_excluir as $coluna_para_excluir){
                    unset($dados[$coluna_para_excluir]);
                }
            }
            foreach ($dados as $coluna){
                foreach ($coluna['tasks'] as $tarefa){
                    $ret[$chave][] = $tarefa['_id'];
                }
            }
        }
        return $ret;
    }
    
    function schedule($param){
        if($param === 'semanal'){
            $this->relatorioPorPl();
        }
        elseif ($param === 'geral'){
            $this->relatorioGeral();
        }
        return '';
    }
    
    private function gerarLog($dados_cliente, $dados_pivo){
        $data = date('Ymd');
        $valores = array();
        foreach ($dados_cliente as $pl => $tarefas){
            foreach ($tarefas as $d){
                /*
                 $campos = array(
                 'pl' => $pl,
                 'cliente' => $d['name'],
                 'cnpj' => $d['cnpj'],
                 'coluna' => $d['coluna'],
                 'valor' => $d['valor_int'],
                 'tempo' => $d['tempo'],
                 'data' => $data,
                 );
                 $sql = montaSQL($campos, '');
                 query($sql);
                 */
                $valores[] = "(null, '$pl', '{$d['name']}', '{$d['cnpj']}', '{$d['coluna']}', {$d['valor_int']}, '{$d['tempo']}', '$data')";
            }
        }
        if(count($valores) > 0){
            $sql = "insert into mgt_kanbanflow_resumo_cliente values " . implode(', ', $valores);
            query($sql);
        }
        $valores = array();
        foreach ($dados_pivo as $pl => $tarefas){
            foreach ($tarefas as $d){
                /*
                 $campos = array(
                 'pl' => $pl,
                 'etapa' => $d['nome'],
                 'quantidade' => $d['quantidade'],
                 'valor' => $d['valor_int'],
                 'data' => $data,
                 );
                 $sql = montaSQL($campos, '');
                 query($sql);
                 */
                $valores[] = "(null, '$pl', '{$d['nome']}', {$d['quantidade']}, {$d['valor_int']}, '$data')";
            }
        }
        if(count($valores)){
            $sql = "insert into mgt_kanbanflow_resumo_etapa values " . implode(', ', $valores);
            query($sql);
        }
    }
    
    private function montarDadosRelatorioFinal($dados){
        $ret = array();
        foreach ($dados as $pl => $chaves){
            $temp = array();
            foreach ($chaves as $chave => $tarefas){
                foreach ($tarefas as $tarefa){
                    $temp[$tarefa['ordem']][] = $tarefa;
                }
            }
            $tarefas_12 = $temp[14] ?? array();
            unset($temp[14]);
            krsort($temp);
            $tarefas_ordenadas = $tarefas_12;
            foreach ($temp as $tarefas_ordem_atual){
                $tarefas_ordenadas = array_merge($tarefas_ordenadas, $tarefas_ordem_atual);
            }
            $ret[$pl] = $tarefas_ordenadas;
        }
        return $ret;
    }
    
    private function pivo($dados){
        /*
        $ret = array();
        foreach ($dados as $pl => $chaves){
            foreach ($chaves as $chave => $tarefas){
                if(!isset($ret[$pl])){
                    $temp = array();
                    foreach ($this->_colunas[$chave] as $coluna){
                        $temp[$coluna['ordem']] = array('nome' => $coluna['nome'], 'quantidade' => 0, 'valor_int' => 0);
                    }
                    ksort($temp);
                    $ret[$pl] = $temp;
                    
                }
                foreach ($tarefas as $d){
                    if(isset($this->_colunas[$chave][$d['columnId']]['ordem'])){
                        $ret[$pl][$this->_colunas[$chave][$d['columnId']]['ordem']]['quantidade']++;
                        $ret[$pl][$this->_colunas[$chave][$d['columnId']]['ordem']]['valor_int'] += $d['valor_int'];
                        $ret[$pl][$this->_colunas[$chave][$d['columnId']]['ordem']]['valor'] = $this->formatarValorCard($ret[$pl][$this->_colunas[$chave][$d['columnId']]['ordem']]['valor_int']);
                    }
                }
            }
        }
        */
        $ret = array();
        
        foreach ($dados as $pl => $chaves){
            foreach ($chaves as $chave => $tarefas){
                if(!isset($ret[$pl])){
                    $temp = array();
                    /*
                    foreach ($this->_colunas[$chave] as $coluna){
                        $temp[$coluna['ordem']] = array('nome' => $coluna['nome'], 'quantidade' => 0, 'valor_int' => 0);
                    }
                    */
                    for ($i = 1; $i <= 5; $i++) {
                        $temp[$i] = array('nome' => $this->_nomesAgrupamentos[$i], 'quantidade' => 0, 'valor_int' => 0);
                    }
                    ksort($temp);
                    $ret[$pl] = $temp;
                    
                }
                foreach ($tarefas as $d){
                    if(isset($this->_colunas[$chave][$d['columnId']]['ordem'])){
                        $agrupado = $d['agrupado'];
                        $ret[$pl][$agrupado]['quantidade']++;
                        $ret[$pl][$agrupado]['valor_int'] += $d['valor_int'];
                        $ret[$pl][$agrupado]['valor'] = $this->formatarValorCard($ret[$pl][$agrupado]['valor_int']);
                    }
                }
            }
        }
        return $ret;
    }
    
    private function getDados(){
        $ret = array();
        $dados = array();
        foreach ($this->_chaves as $chave){
            $dados_novos = $this->getDadosBrutosFromBoard($chave);
            unset($dados[0]);
            $dados[$chave] = $dados_novos;
        }
        foreach ($dados as $chave => $board){
            foreach ($board as $coluna){
                foreach ($coluna['tasks'] as $tarefa){
                    if(isset($tarefa['labels'])){
                        $pl = $this->getPlFromCard($tarefa['labels']);
                        if(!empty($pl) && isset($this->_listaPl[$pl])){
                            $temp = $tarefa;
                            $temp['valor_int'] = $this->getValorFromCard($temp['labels']);
                            $temp['valor'] = $this->formatarValorCard($temp['valor_int']);
                            $temp['credito_int'] = $this->getCreditoFromCard($temp['labels']);
                            $temp['credito'] = $this->formatarValorCard($temp['credito_int']);
                            $temp['regime'] = $this->getRegimeFromCard($temp['labels']);
                            $temp['utilizacao'] = $this->getUtilizacaoFromCard($temp['labels']);
                            $temp['comentario'] = $this->getUltimoComentario($temp['_id'], $chave);
                            /*
                            if(isset($temp['collaborators'][0]['userId']) && !empty($temp['collaborators'][0]['userId'])){
                                $temp['usuario'] = $this->_usuarios[$chave][$temp['collaborators'][0]['userId']] ?? '';
                            }
                            else{
                                $temp['usuario'] = '';
                            }
                            */
                            $temp['usuario'] = $this->_nomesRelatorioGeral[$pl];
                            $temp['status'] = $this->getStatusFromLabels($temp['labels']);
                            $temp['chave'] = $chave;
                            $eventos = $this->getAllEventosTarefa($temp['_id'], $chave);
                            ksort($eventos);
                            $temp['eventos'] = $eventos;
                            $linha_de_tempo = $this->linhaDeTempoTarefa($eventos, $chave, $temp['columnId']);
                            $temp['linha'] = $linha_de_tempo;
                            $temp['linha_datas'] = $this->linhaDeTempoDatas($eventos, $chave);
                            $temp['cnpj'] = $this->getCNPJ($temp['name'], $temp['labels']);
                            $temp['agrupado'] = $this->_agrupamentoEtapas[$this->_colunas[$chave][$temp['columnId']]['ordem']];
                            $temp['nome_agrupado'] = $this->_nomesAgrupamentos[$temp['agrupado']];
                            $temp['pl'] = $pl;
                            
                            $ultima_movimentacao = array_pop($linha_de_tempo);
                            $temp['tempo'] = $ultima_movimentacao[1];
                            $temp['coluna'] = $ultima_movimentacao[0];
                            $temp['ordem'] = $this->_colunas[$chave][$temp['columnId']]['ordem'];
                            
                            //$temp['data_concluido'] = isset($temp['linha_datas'][12]) && $temp['ordem'] >= 12 ? $temp['linha_datas'][12] : '';
                            
                            $data_temp = new DateTime($ultima_movimentacao[2], $this->_timeZone);
                            $temp['data_concluido'] = $data_temp->format('d/m/Y');
                            
                            $temp['data_recebido'] = isset($temp['linha_datas'][4]) && $temp['ordem'] >= 4 ? $temp['linha_datas'][4] : '';
                            
                            if(empty($temp['data_recebido'])){
                                if(count($linha_de_tempo) > 0){
                                    $primeira_movimentacao = array_shift($linha_de_tempo);
                                    $data_temp = new DateTime($primeira_movimentacao[2], $this->_timeZone);
                                    $data_recebido = $data_temp->format('d/m/Y');
                                }
                                else{
                                    $data_recebido = $temp['data_concluido'];
                                }
                                $temp['data_recebido'] = $data_recebido;
                            }
                            
                            $temp['aparecer_itau'] = $this->deveAparcerNoRelatorioGerentesItau($temp['labels']);
                            
                            $ret[$pl][$chave][] = $temp;
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    private function deveAparcerNoRelatorioGerentesItau($tags){
        $ret = false;
        foreach ($tags as $tag){
            $label = strtoupper($tag['name']);
            if(strpos($label, 'TP:') !== false ){
                $valor_sem_caracteres = intval(preg_replace('/[^0-9]/', '', $label));
                $ret = $ret || ($valor_sem_caracteres === 2);
            }
            elseif (strpos($label, 'CLI:') !== false){
                $valor_sem_caracteres = intval(preg_replace('/[^0-9]/', '', $label));
                $ret = $ret || ($valor_sem_caracteres === 3);
            }
        }
        return $ret;
    }
    
    private function getStatusFromLabels($tags){
        $ret = '';
        $cod_regime = 0;
        foreach ($tags as $tag){
            $label = strtoupper($tag['name']);
            if(strpos($label, 'TFAT:') !== false){
                $valor_sem_caracteres = preg_replace('/[^0-9]/', '', $label);
                $cod_regime = intval($valor_sem_caracteres);
            }
        }
        $dicionario_regimes = array(
            1 => 'Compensação mês/mês',
            2 => 'Fechado',
            3 => 'Restituição',
        );
        $ret = $dicionario_regimes[$cod_regime] ?? '';
        return $ret;
    }
    
    private function getDadosBrutosFromBoard($chave){
        $ret = array();
        $api = new integra_kanbanflow($chave);
        $ret = $api->getTodasTarefas();
        unset($api);
        return $ret;
    }
    
    private function getUtilizacaoFromCard($tags){
        $ret = '';
        $cod_regime = 0;
        foreach ($tags as $tag){
            $label = strtoupper($tag['name']);
            if(strpos($label, 'UCRE:') !== false){
                $valor_sem_caracteres = preg_replace('/[^0-9]/', '', $label);
                $cod_regime = intval($valor_sem_caracteres);
            }
        }
        $dicionario_regimes = array(
            1 => 'Compensação',
            2 => 'Restituição',
            3 => 'Compensação/Restituição',
        );
        $ret = $dicionario_regimes[$cod_regime] ?? 'Outro';
        return $ret;
    }
    
    private function getRegimeFromCard($tags){
        $ret = '';
        $cod_regime = 0;
        foreach ($tags as $tag){
            $label = strtoupper($tag['name']);
            if(strpos($label, 'REG:') !== false){
                $valor_sem_caracteres = preg_replace('/[^0-9]/', '', $label);
                $cod_regime = intval($valor_sem_caracteres);
            }
        }
        $dicionario_regimes = array(
            1 => 'Simples',
            2 => 'Presumido',
            3 => 'Lucro Real',
        );
        $ret = $dicionario_regimes[$cod_regime] ?? 'Outro';
        return $ret;
    }
    
    private function getCreditoFromCard($tags){
        $ret = (0 - 1);
        foreach ($tags as $tag){
            $label = strtoupper($tag['name']);
            if(strpos($label, 'CA$:') !== false){
                $valor_sem_caracteres = preg_replace('/[^0-9]/', '', $label);
                $ret = intval($valor_sem_caracteres);
            }
        }
        return $ret;
    }
    
    private function getValorFromCard($tags){
        $ret = 0;
        foreach ($tags as $tag){
            $label = strtoupper($tag['name']);
            if(strpos($label, '$:') !== false && strpos($label, 'CA$:') === false){
                $valor_sem_caracteres = preg_replace('/[^0-9]/', '', $label);
                $ret = intval($valor_sem_caracteres);
            }
        }
        return $ret;
    }
    
    private function formatarValorCard($valor){
        $ret = '';
        if($valor > 0){
            $ret = 'R$ ' . number_format($valor, 2, ',', '.');
        }
        return $ret;
    }
    
    private function getPlFromCard($tags){
        $ret = '';
        foreach ($tags as $tag){
            if(strpos($tag['name'], 'PL:') !== false){
                $ret = strval(intval(preg_replace('/[^0-9]/', '', $tag['name'])));
            }
        }
        return $ret;
    }
    
    private function getCNPJ($name, $tags){
        $ret = 'CNPJ NAO INFORMADO';
        $resultado = array();
        $name = strtolower($name);
        $cnpj_temp = '';
        foreach ($tags as $tag){
            if(strpos(strtoupper($tag['name']), 'CNPJ:') !== false){
                $cnpj_temp = $tag['name'];
            }
        }
        if(empty($cnpj_temp)){
            preg_match('/cnpj[^0-9]*[0-9.\/-]+/', $name, $resultado);
            if(count($resultado) > 0){
                $cnpj_temp = $resultado[0];
            }
        }
        if(!empty($cnpj_temp)){
            $ret = preg_replace('/[^0-9]/', '', $cnpj_temp);
            $ret = mascara($ret, '##.###.###/####-##');
        }
        return $ret;
    }
    
    private function getAllEventosTarefa($tarefa, $chave){
        $ret = array();
        $api = new integra_kanbanflow($chave);
        $dados = $api->getEventosTarefa($tarefa, '', time() * 1000);
        $ret = $dados['events'];
        if($dados['eventsLimited']){
            //tem mais de 100 eventos
            $lista_ids = array();
            foreach ($ret as $evento){
                $lista_ids[] = $evento['_id'];
            }
            while($dados['eventsLimited']){
                $evento_pivo = new evento_kanbanflow($dados['events'][99]);
                $dados = $api->getEventosTarefa($tarefa, '', $evento_pivo->getTimeStamp());
                $eventos_novos = array();
                foreach($dados['events'] as $evento){
                    if(!in_array($evento['_id'], $lista_ids)){
                        $lista_ids[] = $evento['_id'];
                        $eventos_novos[] = $evento;
                    }
                }
                $ret = array_merge($ret, $eventos_novos);
            }
        }
        return $ret;
    }
    
    private function getAllEventosBoard($chave){
        $ret = array();
        $api = new integra_kanbanflow($chave);
        $dados = $api->getEventos('', time() * 1000);
        $ret = $dados['events'];
        return $ret; //comentar
        if($dados['eventsLimited']){
            //tem mais de 100 eventos
            $lista_ids = array();
            foreach ($ret as $evento){
                $lista_ids[] = $evento['_id'];
            }
            while($dados['eventsLimited']){
                $evento_pivo = new evento_kanbanflow($dados['events'][99]);
                $dados = $api->getEventos('', $evento_pivo->getTimeStamp());
                $eventos_novos = array();
                foreach($dados['events'] as $evento){
                    if(!in_array($evento['_id'], $lista_ids)){
                        $lista_ids[] = $evento['_id'];
                        $eventos_novos[] = $evento;
                    }
                }
                $ret = array_merge($ret, $eventos_novos);
            }
        }
        return $ret;
    }
    
    private function linhaDeTempoDatas($eventos, $chave){
        $ret = array();
        $eventos_relevantes = $this->getEventosMovimentacao($eventos);
        ksort($eventos_relevantes);
        $tipo_evento_anterior = '';
        $timestamp_anterior = '';
        foreach ($eventos_relevantes as $timestamp_atual => $evento){
            $tipo_evento = $evento['detailedEvents'][0]['eventType'];
            if($tipo_evento !== 'taskCreated'){
                if($tipo_evento_anterior === 'taskCreated'){
                    $coluna_de = $this->getValorPropriedade($evento, 'columnId', false);
                    if(isset($this->_colunas[$chave][$coluna_de])){
                        $data = new DateTime($timestamp_anterior, $this->_timeZone);
                        $data = $data->format('d/m/Y');
                        $ret[$this->_colunas[$chave][$coluna_de]['ordem']] = $data;
                    }
                }
                $coluna_para = $this->getValorPropriedade($evento, 'columnId', true);
                $data = new DateTime($timestamp_atual, $this->_timeZone);
                $data = $data->format('d/m/Y');
                $ret[$this->_colunas[$chave][$coluna_para]['ordem']] = $data;
            }
            $tipo_evento_anterior = $tipo_evento;
            $timestamp_anterior = $timestamp_atual;
        }
        return $ret;
    }
    
    private function linhaDeTempoTarefa($eventos, $chave, $coluna_atual){
        $ret = array();
        $eventos_relevantes = $this->getEventosMovimentacao($eventos);
        ksort($eventos_relevantes);
        $timestamp_anterior = '';
        $segundo = false;
        foreach ($eventos_relevantes as $timestamp_atual => $evento){
            if($segundo){
                $coluna_de = $this->getValorPropriedade($evento, 'columnId', false);
                $coluna_para = $this->getValorPropriedade($evento, 'columnId', true);
                $ret[] = array($this->_colunas[$chave][$coluna_de]['nome'] ?? 'Coluna sem Nome', $this->getDifDatas($timestamp_anterior, $timestamp_atual), $timestamp_anterior);
            }
            $timestamp_anterior = $timestamp_atual;
            $segundo = true;
        }
        if(count($ret) > 0){
            //se teve pelo menos um evento de movimentação, cria um evento para a ultima movimentação
            $ret[] = array($this->_colunas[$chave][$coluna_para]['nome'], $this->getDifDatas($timestamp_anterior, time()), $timestamp_anterior);
        }
        else{
            //caso n tenha tido nenhum evento de movimentação, cria um evento para dizer que sempre esteve na mesma coluna
            $ret[] = array($this->_colunas[$chave][$coluna_atual]['nome'], $this->getDifDatas($timestamp_anterior, time()), $timestamp_anterior);
        }
        return $ret;
    }
    
    private function getValorPropriedade($evento, $propriedade, $estado = true){
        //se estado true pega o novo valor se false pega o valor antigo
        $ret = '';
        if(isset($evento['detailedEvents'][0]['changedProperties']) && count($evento['detailedEvents'][0]['changedProperties']) > 0){
            foreach ($evento['detailedEvents'][0]['changedProperties'] as $propriedade_modificada){
                if($propriedade_modificada['property'] === $propriedade){
                    if($estado){
                        $ret = $propriedade_modificada['newValue'];
                    }
                    else{
                        $ret = $propriedade_modificada['oldValue'];
                    }
                }
            }
        }
        return $ret;
    }
    
    private function getEventosMovimentacao($eventos){
        $ret = array();
        foreach ($eventos as $evento){
            $tipo_evento = $evento['detailedEvents'][0]['eventType'];
            if($tipo_evento === 'taskChanged'){
                $propriedades_alteradas = $evento['detailedEvents'][0]['changedProperties'] ?? array();
                if(count($propriedades_alteradas) > 0){
                    foreach ($propriedades_alteradas as $campo_alterado){
                        if($campo_alterado['property'] === 'columnId'){
                            $ret[$evento['timestamp']] = $evento;
                        }
                    }
                }
            }
            
            elseif($tipo_evento === 'taskCreated'){
                $ret[$evento['timestamp']] = $evento;
            }
        }
        return $ret;
    }
    
    private function getDifDatas($data_ini, $data_fim){
        $ret = '';
        
        $preg = preg_match('/[^0-9]/', $data_ini);
        if($preg === 0 || $preg === false){
            $tempo1 = $data_ini;
        }
        else{
            $date = new DateTime($data_ini);
            $tempo1 = $date->getTimestamp();
        }
        
        $preg = preg_match('/[^0-9]/', $data_fim);
        if($preg === 0 || $preg === false){
            $tempo2 = $data_fim;
        }
        else{
            $date = new DateTime($data_fim);
            $tempo2 = $date->getTimestamp();
        }
        
        $tempo_total = $tempo2 - $tempo1;
        
        $dados = array();
        
        /*
         * pediram para deixar só meses e dias
        if($tempo_total >= 31540000){
            $anos = intdiv($tempo_total, 31540000);
            $tempo_total = $tempo_total % 31540000;
            $dados[0] =  array(array('ano', 'anos'), $anos);
        }
        */
        if($tempo_total >= 2628000){
            $meses = intdiv($tempo_total, 2628000);
            $tempo_total = $tempo_total % 2628000;
            $dados[1] = array(array('mês', 'meses'), $meses);
        }
        if($tempo_total >= 86400){
            $dias = intdiv($tempo_total, 86400);
            $tempo_total = $tempo_total % 86400;
            $dados[2] = array(array('dia', 'dias'), $dias);
        }
        
        if(!isset($dados[1]) && !isset($dados[2])){
            $tempo_restante = gmdate("H|i|s", $tempo_total);
            $tempo_restante = explode('|', $tempo_restante);
            if(intval($tempo_restante[0]) > 0){
                $dados[3] = array(array('hora', 'horas'), intval($tempo_restante[0]));
            }
            if(intval($tempo_restante[1]) > 0){
                //pediram para deixar somente mes e dia
                //$dados[4] = array(array('minuto', 'minutos'), intval($tempo_restante[1]));
            }
            if(intval($tempo_restante[2]) > 0){
                //pediram para deixar somente mes e dia
                //$dados[5] = array(array('segundo', 'segundos'), intval($tempo_restante[2]));
            }
        }
        
        ksort($dados);
        
        $ultimo_elemento = array(0, 0);
        while ($ultimo_elemento[1] <= 0 && count($dados) > 0) {
            $ultimo_elemento = array_pop($dados);
        }
        
        $temp = array();
        if(count($dados) > 0){
            foreach ($dados as $campo){
                if($campo[1] > 0){
                    if($campo[1] > 1){
                        $objeto = $campo[0][1];
                    }
                    else{
                        $objeto = $campo[0][0];
                    }
                    $temp[] = "{$campo[1]} $objeto";
                }
            }
            $ret = implode(', ', $temp) . " e ";
        }
        
        
        if(is_array($ultimo_elemento[0]) && count($ultimo_elemento[0])){
            if($ultimo_elemento[1] === 1){
                $objeto = $ultimo_elemento[0][0];
            }
            else{
                $objeto = $ultimo_elemento[0][1];
            }
            $ret .= $ultimo_elemento[1] . " " . $objeto;
        }
        
        
        return $ret;
    }
    
    private function getUltimoComentario($tarefa, $chave){
        $ret = '';
        $api = new integra_kanbanflow($chave);
        $dados = $api->getComentariosTask($tarefa);
        if(is_array($dados) && count($dados) > 0){
            $ultimo_comentario = array_pop($dados);
            $ret = $ultimo_comentario['text'];
        }
        return $ret;
    }
    
    private function getColunas(){
        foreach ($this->_chaves as $chave){
            $api = new integra_kanbanflow($chave);
            $dados = $api->getDadosBoard();
            if(is_array($dados) && isset($dados['columns']) && count($dados['columns']) > 0){
                unset($dados['columns'][0]);
                foreach ($dados['columns'] as $ordem => $coluna){
                    $this->_colunas[$chave][$coluna['uniqueId']] = array('nome' =>$coluna['name'], 'ordem' => $ordem);
                }
            }
        }
    }
}