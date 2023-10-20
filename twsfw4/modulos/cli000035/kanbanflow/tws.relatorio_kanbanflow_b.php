<?php

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
set_time_limit(0);
class relatorio_kanbanflow_b{
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
        //sempre 4 digitos?
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
            '138'  => array('nome' => 'Tatiane Vilella C de Andrade'    , 'email' => 'tatiane.andrade@itau-unibanco.com.br'),
            '1000' => array('nome' => 'Tuany Suter'                     , 'email' => 'tuany.suter@itau-unibanco.com.br'),
            '45'   => array('nome' => 'Thiago Evandro Martão'           , 'email' => 'thiago.martao@itau-unibanco.com.br'),
            '23'   => array('nome' => 'Alvaro Souza'                    , 'email' => 'alvaro.souza-azevedo@itau-unibanco.com.br'),
            '9'    => array('nome' => 'Paulo Rogério'                   , 'email' => 'paulo.rogerio@itau-unibanco.com.br'),
            '480'  => array('nome' => 'Valdinei Simas'                  , 'email' => 'valdinei.simas@itau-unibanco.com.br'),
            '327'  => array('nome' => 'Franceli Alberti'                , 'email' => 'franceli.alberti@itau-unibanco.com.br'),
            //'327'  => array('nome' => 'Marcelo Basso'                   , 'email' => 'marcelo.basso-silva@itau-unibanco.com.br'),
            '57'   => array('nome' => 'Adriano Vyborny'                 , 'email' => 'adriano.vyborny@itau-unibanco.com.br'),
            '1338' => array('nome' => 'Joaquim Bezerra'                 , 'email' => 'joaquim.bezerra@itau-unibanco.com.br'),
            '936'  => array('nome' => 'Kelly Negreiros Serruya'         , 'email' => 'kelly.serruya@itau-unibanco.com.br'),
            '7140' => array('nome' => 'Vinícius Lopes Felipe'           , 'email' => 'vinicius.felipe@itau-unibanco.com.br'),
            '654'  => array('nome' => 'Rodrigo Prietto'                 , 'email' => 'rodrigo.prietto@itau-unibanco.com.br'),
            //'654'  => array('nome' => 'Jair Simões'                     , 'email' => 'jair-simoes.coelho@itau-unibanco.com.br'),
            '5665' => array('nome' => 'Guilherme Sevija Silveira'       , 'email' => 'guilherme.sevija-silveira@itau-unibanco.com.br'),
            '3834' => array('nome' => 'Henrique Block'                  , 'email' => 'henrique.block@itau-unibanco.com.br'),
            //'3834' => array('nome' => 'Claudia Danieli'                 , 'email' => 'claudia.corradi@itau-unibanco.com.br'),
        );
        */
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
        
        //$this->_chaves = array('CVa7QcJxLHihaK6CU5ZSbUowgx');
        
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
        $dados = $this->getDados();
        
        $dados_pivo = $this->pivo($dados);
        $dados = $this->montarDadosRelatorioFinal($dados);
        
        $tabela = new relatorio01();
        
        $tabela->setParamTabela(array('ordenacao' => false), 0);
        $tabela->addColuna(array('campo' => 'nome'       , 'etiqueta' => 'Cliente ITAÚ'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'cnpj'       , 'etiqueta' => 'CNPJ'                              , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'regime'     , 'etiqueta' => 'Regime Tributário'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'etapa'     , 'etiqueta' => 'Etapa'			                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'valor_apurado'      , 'etiqueta' => 'Valor'			                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //$tabela->addColuna(array('campo' => 'credito'    , 'etiqueta' => 'Crédito Aprovado'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //$tabela->addColuna(array('campo' => 'utilizacao' , 'etiqueta' => 'Forma de Recebimento do Benefício' , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'ultimo_comentario' , 'etiqueta' => 'Comentário'                        , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //$tabela->addColuna(array('campo' => 'tempo'      , 'etiqueta' => 'Tempo nesta Etapa'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        
        
        $tabela->setParamTabela(array('ordenacao' => false), 1);
        $tabela->addColuna(array('campo' => 'nome'       , 'etiqueta' => 'Etapa'			        , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $tabela->addColuna(array('campo' => 'quantidade' , 'etiqueta' => 'Quantidade de Clientes'	, 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $tabela->addColuna(array('campo' => 'valor'      , 'etiqueta' => 'Valor Apurado'			, 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        
        global $config;
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
                $titulo_email = 'Resumo Kanbanflow - Agência ' . $pl . ' | ' . date('d/m/Y - H:i');
                
                $mensagem = 'Prezado(a) Julia, segue em anexo o resumo do kanbanflow.';
                enviaEmailAntigo('julia.orcesi@itau-unibanco.com.br', $titulo_email, $mensagem, $anexos);
                
                foreach ($destinatarios as $dt){
                    $mensagem = 'Prezado(a) ' . $dt['nome'] . ', segue em anexo o resumo do kanbanflow.';
                    enviaEmailAntigo($dt['email'], $titulo_email, $mensagem, $anexos);
                    
                    //$tabela->enviaEmail($dt['email'], 'Resumo Kanbanflow - Agência ' . $pl, array('mensagem' => $mensagem));
                }
                //$email_copias = "corine.iwamura@itau-unibanco.com.br;julia.orcesi@itau-unibanco.com.br;gabriel.albuquerque-andrade@itau-unibanco.com.br;leandro@grupomarpa.com.br;luciano@grupomarpa.com.br";
                //$tabela->enviaEmail($dados_pl['email'], 'Resumo Kanbanflow - Agência ' . $pl, array('mensagem' => $mensagem, 'copiaOculta' => $email_copias));
                //$tabela->enviaEmail('julia.orcesi@itau-unibanco.com.br;luciano@grupomarpa.com.br;rodrigo.ximenes@verticais.com.br;leandro@grupomarpa.com.br', 'Resumo Kanbanflow - Agência ' . $pl, array('mensagem' => $mensagem, 'copiaOculta' => $email_copias));
                //$tabela->enviaEmail('luciano@grupomarpa.com.br', 'Resumo Kanbanflow - Agência ' . $pl, array('mensagem' => $mensagem, 'copiaOculta' => $email_copias));
                //$tabela->enviaEmail('emanuel.thiel@verticais.com.br', 'Resumo Kanbanflow - Agência ' . $pl, array('mensagem' => $mensagem));
            }
        }
        
        return '';
    }
    
    public function index(){
        $this->relatorioGeral();
        //$dados = $this->getDados('GERAL');
        return '';
    }
    
    private function relatorioGeral(){
        $dados = $this->getDados('GERAL');
        
        $tabela = new relatorio01();
        
        $tabela->setParamTabela(array('ordenacao' => false));
        $tabela->addColuna(array('campo' => 'pl'               , 'etiqueta' => 'Agencia'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'executivo'          , 'etiqueta' => 'Executivo'                       , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'cnpj'             , 'etiqueta' => 'CNPJ'                            , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'dt_lead'    , 'etiqueta' => 'Lead Recebido'                   , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'ultima_mov'   , 'etiqueta' => 'Data de Entrega do Relatório'    , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'valor_apurado'            , 'etiqueta' => 'Crédito Apurado'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'valor_aprovado'          , 'etiqueta' => 'Credito Aprovado'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'status'           , 'etiqueta' => 'Status'			                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $tabela->addColuna(array('campo' => 'regime'           , 'etiqueta' => 'Regime Tributário'               , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));


        array_multisort(array_column($dados, "valor_aprovado_int"), SORT_DESC, $dados);

        
        $tabela->setDados($dados);
        $tabela->setToExcel(true, 'relatorio');
        
        $email_copias = "corine.iwamura@itau-unibanco.com.br;julia.orcesi@itau-unibanco.com.br;gabriel.albuquerque-andrade@itau-unibanco.com.br;leandro@grupomarpa.com.br;luciano@grupomarpa.com.br";
        
        $tabela->enviaEmail($email_copias, 'Informações de data e benefício por CNPJ | ' . date('d/m/Y - H:i'), array('mensagem' => 'Segue em anexo os relatórios sobre as datas e benefícios separados pro CNPJ'));
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
    
    function schedule($param){
        if($param === 'semanal'){
            $this->relatorioPorPl();
        }
        elseif ($param === 'geral'){
            $this->relatorioGeral();
        }
        return '';
    }
    
    private function montarDadosRelatorioFinal($dados){
        $ret = array();
        foreach ($dados as $pl => $tarefas){
            $temp = array();
            foreach ($tarefas as $tarefa){
                $temp[$tarefa['num_coluna']][] = $tarefa;
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
    
    private function getAgrupamentos(){
        $ret = array();
        $sql = "select * from kanbanflow_columns_agrup";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'quantidade' => 0,
                    'valor_int' => 0,
                    'nome' => $row['descricao'],
                );
                
                $ret[strval($row['grupo'])] = $temp;
            }
        }
        return $ret;
    }
    
    private function pivo($dados){
        $ret = array();
        
        $agrupamentos_base = $this->getAgrupamentos();
        
        foreach ($dados as $pl => $tarefas){
            $ret[$pl] = $agrupamentos_base;
            foreach ($tarefas as $tarefa){
                $ret[$pl][$tarefa['cod_agrupamento']]['quantidade']++;
                if($tarefa['valor_apurado_int'] > 0){
                    $ret[$pl][$tarefa['cod_agrupamento']]['valor_int'] += $tarefa['valor_apurado_int'];
                    $ret[$pl][$tarefa['cod_agrupamento']]['valor'] = $this->formatarValorCard($ret[$pl][$tarefa['cod_agrupamento']]['valor_int']);
                }
            }
        }
        return $ret;
    }
    
    private function getDados($modalidade = 'PL'){
        $ret = array();
        $pls_brutos = array_keys($this->_listaPl);
        $lista_pls = array();
        foreach ($pls_brutos as $plb){
            $lista_pls[] = "'" . $plb . "'";
        }
        $pls = "(" . implode(', ', $lista_pls) . ")";
        
        $sql = "
SELECT t.*
	,c.texto as ultimo_comentario
	,COALESCE(l_apurado.valor, '-1') AS valor_apurado
	,COALESCE(l_aprovado.valor, '-1') AS valor_aprovado
    ,l_cnpj.valor as cnpj
    ,l_pl.valor as pl
    ,dic_regime.descricao as regime
    ,dic_status.descricao as status
    ,coluna_desc.descricao as etapa
    ,coluna_desc.ordem as num_coluna
    ,coluna_desc.agrupamento as cod_agrupamento
    ,coluna_agrup.descricao as agrupamento
    ,mov.dt as ultima_mov
    ,COALESCE(mov_lead.dt, evento_criacao.dt) as dt_lead
FROM kanbanflow_tasks AS t
left JOIN (
	SELECT c1.id_task
		,c1.texto
	FROM kanbanflow_comments AS c1
	JOIN (
		SELECT id_task
			,max(dt) AS dt
		FROM kanbanflow_comments
		GROUP BY id_task
		) AS tmp2 ON c1.id_task = tmp2.id_task
		AND c1.dt = tmp2.dt
	) AS c ON (t.id = c.id_task)
LEFT JOIN kanbanflow_labels AS l_apurado ON (
		t.id = l_apurado.task
		AND l_apurado.chave = '$'
		)
LEFT JOIN kanbanflow_labels AS l_aprovado ON (
		t.id = l_aprovado.task
		AND l_aprovado.chave = 'CA$'
		)
LEFT JOIN kanbanflow_labels AS l_cnpj ON (
		t.id = l_cnpj.task
		AND l_cnpj.chave = 'CNPJ'
		)
LEFT JOIN kanbanflow_labels AS l_pl ON (
		t.id = l_pl.task
		AND l_pl.chave = 'PL'
		)
LEFT JOIN kanbanflow_labels AS l_regime ON (
		t.id = l_regime.task
		AND l_regime.chave = 'REG'
		)
LEFT JOIN kanbanflow_labels_desc as dic_regime on(
		dic_regime.chave = l_regime.chave
    	and dic_regime.valor = l_regime.valor
		)
   
        
LEFT JOIN kanbanflow_labels AS l_status ON (
		t.id = l_status.task
		AND l_status.chave = 'TFAT'
		)
LEFT JOIN kanbanflow_labels_desc as dic_status on(
		dic_status.chave = l_status.chave
    	and dic_status.valor = l_status.valor
		)        
        
left join kanbanflow_columns as coluna_inter on (
		coluna_inter.id_kanbanflow = t.coluna
    	and coluna_inter.token = t.token
		)
left join kanbanflow_columns_desc as coluna_desc on (
		coluna_inter.id_desc = coluna_desc.ordem
		)
left join kanbanflow_columns_agrup as coluna_agrup on(
		coluna_desc.agrupamento = coluna_agrup.grupo
		)
left JOIN (
	SELECT mov1.task, mov1.token, mov1.dt
	FROM kanbanflow_events AS mov1
	JOIN (
		SELECT task, token
			,max(dt) as dt
		FROM kanbanflow_events
        where tipo = 'taskChanged' and campo = 'columnId'
		GROUP BY task, token
		) AS mov2 ON mov1.task = mov2.task and mov1.token = mov2.token and mov1.dt = mov2.dt
	) AS mov ON (t.id_kanbanflow = mov.task and t.token = mov.token)
left JOIN (
	SELECT mov3.task, mov3.token, mov3.dt
	FROM kanbanflow_events AS mov3
	JOIN (
		SELECT kanbanflow_events.task, kanbanflow_events.token
			,max(kanbanflow_events.dt) as dt
		FROM kanbanflow_events
        join kanbanflow_columns on (kanbanflow_events.valor_novo = kanbanflow_columns.id_kanbanflow 
                                    		and kanbanflow_columns.id_desc = 4)
        where kanbanflow_events.tipo = 'taskChanged' and kanbanflow_events.campo = 'columnId'
		GROUP BY kanbanflow_events.task, kanbanflow_events.token
		) AS mov4 ON mov3.task = mov4.task and mov3.token = mov4.token and mov3.dt = mov4.dt
	) AS mov_lead ON (t.id_kanbanflow = mov_lead.task and t.token = mov_lead.token)

left JOIN kanbanflow_events as evento_criacao
ON (t.id_kanbanflow = evento_criacao.task and t.token = evento_criacao.token and evento_criacao.tipo = 'taskCreated')
";
        if($modalidade === 'PL'){
            $sql .= "LEFT JOIN kanbanflow_labels AS l_cli ON (
		t.id = l_cli.task
		AND l_cli.chave = 'CLI'
		)
LEFT JOIN kanbanflow_labels AS l_tp ON (
		t.id = l_tp.task
		AND l_tp.chave = 'TP'
		)
";
        }
        
        $sql .=  "where l_pl.valor is not null and coluna_desc.ordem is not null and l_pl.valor in $pls";
        
        if($modalidade === 'PL'){
            $sql .= "
and (l_cli.valor = '3' or l_tp.valor = '2')
";
        }
        
        elseif($modalidade === 'GERAL'){
            $sql .= "
and (l_apurado.valor is not null or l_aprovado.valor is not null)";
        }

        //$sql .= " and t.id in (178)";
        
        echo $sql;
        
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos_direto = array('descricao', 'nome', 'membros', 'ultimo_comentario', 'regime', 'status', 'etapa', 'agrupamento', 'num_coluna');
            $campos_datas = array('dt_lead', 'ultima_mov');
            $campos_dinheiro = array('valor_apurado', 'valor_aprovado');
            foreach ($rows as $row){
                $temp = array();
                foreach ($campos_direto as $campo){
                    $temp[$campo] = $row[$campo];
                }
                foreach ($campos_datas as $campo){
                    $temp[$campo] = datas::dataMS2D($row[$campo]);
                }
                foreach ($campos_dinheiro as $campo){
                    $valor = intval($row[$campo]);
                    if($valor < 0){
                        $temp[$campo . '_int'] = -1;
                        $temp[$campo] = '';
                    }
                    else{
                        $temp[$campo . '_int'] = $valor;
                        $temp[$campo] = $this->formatarValorCard($valor);
                    }
                }
                $temp['pl'] = strval(intval($row['pl']));
                $cnpj = $row['cnpj'];
                if(empty($cnpj)){
                    $resultado = array();
                    preg_match('/cnpj[^0-9]*[0-9.\/-]+/', $row['nome'], $resultado);
                    if(is_array($resultado) && count($resultado) > 0){
                        $cnpj = $resultado[0];
                    }
                }
                if(!empty($cnpj)){
                    $cnpj = preg_replace('/[^0-9]/', '', $cnpj[0]);
                    $temp['cnpj'] = mascara($row['cnpj'], '##.###.###/####-##');
                }
                else{
                    $temp['cnpj'] = '';
                }
                $temp['cod_agrupamento'] = strval($row['cod_agrupamento']);
                
                $temp['executivo'] = $this->_nomesRelatorioGeral[$temp['pl']];
                
                if($modalidade === 'GERAL'){
                    //algumas entradas que deveriam passar por esse teste não estão passando
                    if($temp['valor_apurado_int'] > 0 || $temp['valor_aprovado_int'] >= 0){
                        $ret[] = $temp;
                    }
                    else{
                        
                    }
                }
                else{
                    $ret[$temp['pl']][] = $temp;
                }
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
}