<?php
/*
 * Data Criacao: 20/06/2022
 * 
 * Autor: @LuisCosta94
 *
 * Descricao: Possuí 5 tabelas, sendo elas: 
 * 1. Relatório de pagamentos do dia anterior
 * 2. Relatório de vencimentos no dia
 * 3. Relatório de Inadimplentes
 * 4. Comparação do mês do ano passado com o atual
 * 5. Resumo mensal do mês atual
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class relatorio_diario
{
  var $funcoes_publicas = array(
    'index'     => true,
  );

  //Classe relatorio
  private $_relatorio;

  //Nome do programa
  private $_programa;

  //Titulo do relatorio
  private $_titulo;

  //Indica que se é teste (não envia email se for)
  private $_teste;

  //Dados
  private $_dados;

  public function __construct()
  {
    conectaTRIB();
    $this->_programa = get_class($this);
    $this->_titulo = 'Resumo Financeiro Diário';

    $this->_teste = true;

    $param = [];
    $param['programa'] = $this->_programa;
    $this->_relatorio = new relatorio01($param);
    $param = [];
    $param['paginacao']  = false;
    $param['ordenacao']  = false;
    $param['scroll']    = true;
    $param['scrollX']    = true;
    $param['scrollY']    = true;
    $param['imprimeZero']  = true;
    $param['width']    = 'AUTO';
    $param['filtro']    = false;
    $param['info']    = false;
    $this->_relatorio->setParamTabela($param);

    // if (false) {
    //   sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De', 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
    //   sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até', 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
    //   sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente', 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
    //   sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Analista', 'variavel' => 'RECURSO', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
    // }
  }

  public function index()
  {
    $ret = '';

    $this->monta_relatorio();

    $ret .= $this->_relatorio;

    return $ret;
  }

  private function monta_relatorio()
  {

    // $filtro = $this->_relatorio->getFiltro();
    // $dtDe   = isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
    // $dtAte   = isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';

    // $mes = date('F');
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');
    $mes = strftime('%B', strtotime('today'));

    $dados      = $this->getDados();
    $dados_sec1 = $this->getDadosValorVenc();
    $dados_sec2 = $this->getDadosInadimplente();
    $dados_sec3 = $this->getDadosComparacao();
    $dados_sec4 = $this->getDadosResumoMensal();

    $this->_relatorio->setTitulo("Resumo Diário", 0);
    $this->montaColunas();

    $this->_relatorio->setTituloSecao(0, "Valores Recebidos(Sempre do dia útil anterior)");
    $this->_relatorio->setDados($dados, 0);

    $this->_relatorio->setTituloSecao(1, "<br>Valores Vencendo no Dia");
    $this->_relatorio->setDados($dados_sec1, 1);

    $this->_relatorio->setTituloSecao(2, "<br>Inadimplentes - Valores em Aberto no Mês");
    $this->_relatorio->setDados($dados_sec2, 2);

    $this->_relatorio->setTituloSecao(3, "<br>Comparação anual do mês");
    $this->_relatorio->setDados($dados_sec3, 3);

    $this->_relatorio->setTituloSecao(4, "<br>Resumo Mensal de " . $mes);
    $this->_relatorio->setDados($dados_sec4, 4);
  }

  //Envia os em-ails 
  public function schedule($param = '')
  {
    $this->monta_relatorio();
    $this->_relatorio->setAuto(true);

    if ($this->_teste) {
      $this->_relatorio->enviaEmail('luis.costa@verticais.com.br');
    } else {
      $this->_relatorio->enviaEmail($param);
    }
  }

  private function montaColunas()
  {
    //Valores Recebidos
    // Seção 0
    $this->_relatorio->addColuna(array('campo' => 'cliente',        'etiqueta' => 'Cliente',            'tipo' => 'T', 'width' => 150,  'posicao' => 'E'));
    $this->_relatorio->addColuna(array('campo' => 'consultor',      'etiqueta' => 'Consultor',          'tipo' => 'T', 'width' => 150,  'posicao' => 'E'));
    $this->_relatorio->addColuna(array('campo' => 'procedimento',   'etiqueta' => 'Procedimento',       'tipo' => 'T', 'width' => 150,  'posicao' => 'E'));
    $this->_relatorio->addColuna(array('campo' => 'numero',         'etiqueta' => 'Número',             'tipo' => 'N', 'width' => 80,   'posicao' => 'E'));
    $this->_relatorio->addColuna(array('campo' => 'forma_cobranca', 'etiqueta' => 'Forma de Cobrança',  'tipo' => 'T', 'width' => 80,   'posicao' => 'E'));
    $this->_relatorio->addColuna(array('campo' => 'data_venc',      'etiqueta' => 'Data Vencimento',    'tipo' => 'D', 'width' => 80,   'posicao' => 'C'));
    $this->_relatorio->addColuna(array('campo' => 'valor_parcela',  'etiqueta' => 'Valor Parcela',      'tipo' => 'V', 'width' => 80,   'posicao' => 'D'));
    $this->_relatorio->addColuna(array('campo' => 'data_pagamento', 'etiqueta' => 'Data Pagamento',     'tipo' => 'D', 'width' => 80,   'posicao' => 'C'));
    $this->_relatorio->addColuna(array('campo' => 'valor_recebido', 'etiqueta' => 'Valor Recebido',     'tipo' => 'V', 'width' => 80,   'posicao' => 'D'));

    // Seção 1
    // Valores Vencendo no Dia
    $this->_relatorio->addColuna(array('campo' => 'cliente',        'etiqueta' => 'Cliente',            'tipo' => 'T', 'width' => 150,  'posicao' => 'E'), 1);
    $this->_relatorio->addColuna(array('campo' => 'consultor',      'etiqueta' => 'Consultor',          'tipo' => 'T', 'width' => 150,  'posicao' => 'E'), 1);
    $this->_relatorio->addColuna(array('campo' => 'procedimento',   'etiqueta' => 'Procedimento',       'tipo' => 'T', 'width' => 150,  'posicao' => 'E'), 1);
    $this->_relatorio->addColuna(array('campo' => 'numero',         'etiqueta' => 'Número',             'tipo' => 'N', 'width' => 80,   'posicao' => 'E'), 1);
    $this->_relatorio->addColuna(array('campo' => 'forma_cobranca', 'etiqueta' => 'Forma de Cobrança',  'tipo' => 'T', 'width' => 80,   'posicao' => 'E'), 1);
    $this->_relatorio->addColuna(array('campo' => 'data_venc',      'etiqueta' => 'Data Vencimento',    'tipo' => 'D', 'width' => 80,   'posicao' => 'C'), 1);
    $this->_relatorio->addColuna(array('campo' => 'valor_parcela',  'etiqueta' => 'Valor Parcela',      'tipo' => 'V', 'width' => 80,   'posicao' => 'D'), 1);
    $this->_relatorio->addColuna(array('campo' => 'data_pagamento', 'etiqueta' => 'Data Pagamento',     'tipo' => 'D', 'width' => 80,   'posicao' => 'C'), 1);
    $this->_relatorio->addColuna(array('campo' => 'valor_recebido', 'etiqueta' => 'Valor Recebido',     'tipo' => 'V', 'width' => 80,   'posicao' => 'D'), 1);


    //inadimplentes abertos
    //Seção 2
    $this->_relatorio->addColuna(array('campo' => 'cliente',        'etiqueta' => 'Cliente',            'tipo' => 'T', 'width' => 150,  'posicao' => 'E'), 2);
    $this->_relatorio->addColuna(array('campo' => 'consultor',      'etiqueta' => 'Consultor',          'tipo' => 'T', 'width' => 150,  'posicao' => 'E'), 2);
    $this->_relatorio->addColuna(array('campo' => 'procedimento',   'etiqueta' => 'Procedimento',       'tipo' => 'T', 'width' => 150,  'posicao' => 'E'), 2);
    $this->_relatorio->addColuna(array('campo' => 'numero',         'etiqueta' => 'Número',             'tipo' => 'N', 'width' => 80,   'posicao' => 'E'), 2);
    $this->_relatorio->addColuna(array('campo' => 'forma_cobranca', 'etiqueta' => 'Forma de Cobrança',  'tipo' => 'T', 'width' => 80,   'posicao' => 'E'), 2);
    $this->_relatorio->addColuna(array('campo' => 'data_venc',      'etiqueta' => 'Data Vencimento',    'tipo' => 'D', 'width' => 80,   'posicao' => 'C'), 2);
    $this->_relatorio->addColuna(array('campo' => 'valor_parcela',  'etiqueta' => 'Valor Parcela',      'tipo' => 'V', 'width' => 80,   'posicao' => 'D'), 2);

    //Valores faturados no mês
    //Seção 3
    $this->_relatorio->addColuna(array('campo' => 'ano_mes',          'etiqueta' => 'Ano/Mês',                  'tipo' => 'T', 'width' => 80,  'posicao' => 'C'), 3);
    $this->_relatorio->addColuna(array('campo' => 'val_prev_fat',     'etiqueta' => 'Valor Previsto/Faturado',  'tipo' => 'V', 'width' => 80,  'posicao' => 'D'), 3);
    $this->_relatorio->addColuna(array('campo' => 'val_recebido',     'etiqueta' => 'Valor Realizado/Recebido', 'tipo' => 'V', 'width' => 80,  'posicao' => 'D'), 3);
    $this->_relatorio->addColuna(array('campo' => 'val_inadimplente', 'etiqueta' => 'Valor Inadimplente',       'tipo' => 'V', 'width' => 80,  'posicao' => 'D'), 3);
    $this->_relatorio->addColuna(array('campo' => 'val_aberto',       'etiqueta' => 'Abertos até Fim do Ano',             'tipo' => 'V', 'width' => 80,  'posicao' => 'D'), 3);
    // Resumo Mensal
    // Seção 4
    $this->_relatorio->addColuna(array('campo' => 'dia',          'etiqueta' => 'Dia',          'tipo' => 'T', 'width' => 80,  'posicao' => 'C'), 4);
    $this->_relatorio->addColuna(array('campo' => 'vencimentos',  'etiqueta' => 'Vencimentos',  'tipo' => 'V', 'width' => 80,  'posicao' => 'D'), 4);
    $this->_relatorio->addColuna(array('campo' => 'recebidos',    'etiqueta' => 'Recebidos',    'tipo' => 'V', 'width' => 80,  'posicao' => 'D'), 4);
  }

  private function getDados()
  {
    $ret = [];

    $dataAtual = date('Y-m-d');
    $dia_da_semana = date('w', strtotime($dataAtual)); // 0 = domingo, 6 = sábado

    if ($dia_da_semana == 1) {
      $dia_util_anterior = date('Y-m-d', strtotime(("-3 days")));
      $dataAtualComparativo = date($dia_util_anterior, strtotime(("-1 year")));
    } else {
      $dia_util_anterior = date('Y-m-d', strtotime(("yesterday")));
      $dataAtualComparativo = date($dia_util_anterior, strtotime(("-1 year")));
    }

    $valor_total_parcelas = 0;
    $valor_total_recebido = 0;

    $sql =
      "SELECT 
    CXP.*, 
    T.TABDESCRICAO AS FORMA_COBRANCA, 
    IFNULL(CB.CBANOMEAGENCIA, CONCAT(CB.CBAAGENCIA,' - ', CB.CBACONTA)) AS CONTA,
    (SELECT t1.TABDESCRICAO FROM TABELAS t1 WHERE t1.TABCODIGO = CXP.CXPFORMAENVIO) AS FORMAENVIO,
    CASE 
      WHEN CXP.CXPEMAIL IS NOT NULL 
        THEN CXPEMAIL 
        ELSE '' 
        END AS EMAILENVIO, 
    c.CLINOMEFANTASIA,
    CONCAT(c.CLISEQUENCIAL,' - ', c.CLINOMEFANTASIA) AS CLIENTE, 
    u.USUNOME AS CONSULTOR, 
    t2.TABDESCRICAO AS POSICAO,
    DATEDIFF(CXP.CXPDATAVENCIMENTO, curdate()) as DIFERENCA,
    CASE 
      WHEN CXP.CXPMODOFATURAMENTO = 'RECIBO' 
        THEN 'Recibo' 
        ELSE 'Nota Fiscal' 
        END AS MODOFATURAMENTO,
    p1.PRINOME AS PROCEDIMENTO
    
    FROM CONTASARECEBERXPARCELAS CXP
    LEFT JOIN CONTASARECEBER cp 
      ON cp.CTRCODIGO = CXP.CTRCODIGO
    LEFT JOIN CLIENTES c 
      ON c.CLICODIGO = cp.CLICODIGO
    LEFT JOIN TABELAS T 
      ON T.TABCODIGO = CXP.CXPFORMAPAGAMENTO
    LEFT JOIN CONTASBANCARIAS CB
      ON CB.CBACODIGO = CXP.CBACODIGO
    LEFT JOIN USUARIOS u 
      ON cp.CTRCONSULTOR = u.USUCODIGO
    LEFT JOIN TABELAS t2 
      ON t2.TABCODIGO = CXP.CXPPOSICAO
    LEFT JOIN PROCESSOSINTERNO p1 
      ON p1.PRICODIGO = cp.TABCENTROCUSTO

    WHERE 
        cp.CTRSTATUS = 'S' 
    AND CXP.CXPSTATUS = 'S'
    AND cp.CLICODIGO NOT IN (4241, 3811)
    AND CXP.CXPPOSICAO IN (26785, 26784, 26783, 33991)
    AND (CXPDATAPAGAMENTO BETWEEN '" . $dia_util_anterior . " 00:00:00' and '" . $dia_util_anterior . " 23:59:59')
    ORDER BY CXPNUMERO
    ";
    $rows = queryTRIB($sql);
    // print_r($rows);


    if (is_array($rows) && count($rows) > 0) {
      foreach ($rows as $row) {
        $valor_total_parcelas += $row['CXPVALOR'];
        $valor_total_recebido += $row['CXPVALORPAGO'];

        $temp = [];
        $temp['cliente'] = $row['CLINOMEFANTASIA'];
        $temp['consultor'] = $row['CONSULTOR'];
        $temp['procedimento'] = $row['PROCEDIMENTO'];
        $temp['numero'] = $row['CXPNUMERO'];
        $temp['forma_cobranca'] = $row['FORMA_COBRANCA'];
        $temp['data_venc'] = datas::dataMS2S($row['CXPDATAVENCIMENTO']);
        $temp['valor_parcela'] = $row['CXPVALOR'];
        $temp['data_pagamento'] = datas::dataMS2S($row['CXPDATAPAGAMENTO']);
        $temp['valor_recebido'] = $row['CXPVALORPAGO'];

        $ret[] = $temp;
      }
      $temp = [];
      $temp['cliente']        = 'zValor Total:';


      $temp['valor_parcela']  = $valor_total_parcelas;
      $temp['valor_recebido'] = $valor_total_recebido;

      $ret[] = $temp;
    }

    return $ret;
  }

  private function getDadosValorVenc()
  {
    $ret = [];

    $valor_total_parcelas = 0;
    $valor_total_recebido = 0;
    $dataAtual = date('Y-m-d');
    $dia_da_semana = date('w', strtotime($dataAtual)); // 0 = domingo, 6 = sábado

    if ($dia_da_semana == 1) {
      $dia_util_anterior = date('Y-m-d', strtotime(("-3 days")));
      $dataAtualComparativo = date($dia_util_anterior, strtotime(("-1 year")));
    } else {
      $dia_util_anterior = date('Y-m-d', strtotime(("yesterday")));
      $dataAtualComparativo = date($dia_util_anterior, strtotime(("-1 year")));
    }

    $sql = "
    SELECT 
      CXP.*,
      T.TABDESCRICAO AS FORMA_COBRANCA,
      IFNULL(C.CBANOMEAGENCIA, CONCAT(C.CBAAGENCIA,' - ', C.CBACONTA)) AS CONTA,
    (SELECT t1.TABDESCRICAO FROM TABELAS t1 WHERE t1.TABCODIGO = CXP.CXPFORMAENVIO) AS FORMAENVIO,
    c.CLINOMEFANTASIA,
    CONCAT(c.CLISEQUENCIAL,' - ', c.CLINOMEFANTASIA) AS CLIENTE,
    u.USUNOME AS CONSULTOR,
    t2.TABDESCRICAO AS POSICAO,
    DATEDIFF(CXP.CXPDATAVENCIMENTO, curdate()) as DIFERENCA,

    CASE 
      WHEN CXP.CXPMODOFATURAMENTO = 'RECIBO' 
        THEN 'Recibo' 
        ELSE 'Nota Fiscal' 
        END AS MODOFATURAMENTO,
    p1.PRINOME AS PROCEDIMENTO

    FROM CONTASARECEBERXPARCELAS CXP

    LEFT JOIN CONTASARECEBER cp 
      ON cp.CTRCODIGO = CXP.CTRCODIGO
    LEFT JOIN CLIENTES c 
      ON c.CLICODIGO = cp.CLICODIGO
    LEFT JOIN TABELAS T 
      ON T.TABCODIGO = CXP.CXPFORMAPAGAMENTO
    LEFT JOIN CONTASBANCARIAS C 
      ON C.CBACODIGO = CXP.CBACODIGO
    LEFT JOIN USUARIOS u 
      ON cp.CTRCONSULTOR = u.USUCODIGO
    LEFT JOIN TABELAS t2 
      ON t2.TABCODIGO = CXP.CXPPOSICAO
    LEFT JOIN PROCESSOSINTERNO p1 
      ON p1.PRICODIGO = cp.TABCENTROCUSTO

    WHERE 
        cp.CTRSTATUS = 'S' 
    AND CXP.CXPSTATUS = 'S'
    AND (CXPDATAVENCIMENTO BETWEEN '" . $dataAtual . " 00:00:00' and '" . $dataAtual . " 23:59:59')
    ORDER BY CXPNUMERO
    ";
    $rows = queryTRIB($sql);
    // print_r($rows);
    // echo $sql;

    if (is_array($rows) && count($rows) > 0) {
      foreach ($rows as $row) {
        $valor_total_parcelas += $row['CXPVALOR'];
        $valor_total_recebido += $row['CXPVALORPAGO'];

        $temp = [];
        $temp['cliente'] = $row['CLINOMEFANTASIA'];
        $temp['consultor'] = $row['CONSULTOR'];
        $temp['procedimento'] = $row['PROCEDIMENTO'];
        $temp['numero'] = $row['CXPNUMERO'];
        $temp['forma_cobranca'] = $row['FORMA_COBRANCA'];
        $temp['data_venc'] = datas::dataMS2S($row['CXPDATAVENCIMENTO']);
        $temp['valor_parcela'] = $row['CXPVALOR'];
        $temp['data_pagamento'] = datas::dataMS2S($row['CXPDATAPAGAMENTO']);
        $temp['valor_recebido'] = $row['CXPVALORPAGO'];

        $ret[] = $temp;
      }
      $temp = [];
      $temp['cliente']        = 'zValor Total:';
      $temp['valor_parcela']  = $valor_total_parcelas;
      $temp['valor_recebido'] = $valor_total_recebido;

      $ret[] = $temp;
    }

    return $ret;
  }

  private function getDadosInadimplente()
  {
    $ret = [];
    $valor_total_parcelas = 0;
    $valor_total_recebido = 0;
    $sql = "
    SELECT 
      CXP.*,
      T.TABDESCRICAO AS FORMA_COBRANCA,
      c.CLINOMEFANTASIA, 
      CONCAT(c.CLISEQUENCIAL,' - ', c.CLINOMEFANTASIA) AS CLIENTE,
      (SELECT t1.TABDESCRICAO FROM TABELAS t1 WHERE t1.TABCODIGO = CXP.CXPFORMAENVIO) AS FORMAENVIO,
      IFNULL(CB.CBANOMEAGENCIA, 
      CONCAT(CB.CBAAGENCIA,' - ', CB.CBACONTA)) AS CONTA,
      DATEDIFF(CXP.CXPDATAVENCIMENTO, curdate()) as DIFERENCA,
      u.USUNOME AS CONSULTOR, 
      t2.TABDESCRICAO AS POSICAO, 
      p1.PRINOME AS PROCEDIMENTO
      
      FROM 
        CONTASARECEBERXPARCELAS CXP
      LEFT JOIN CONTASARECEBER cp 
        ON cp.CTRCODIGO = CXP.CTRCODIGO
      LEFT JOIN CLIENTES c 
        ON c.CLICODIGO = cp.CLICODIGO
      LEFT JOIN TABELAS T 
        ON T.TABCODIGO = CXP.CXPFORMAPAGAMENTO
      LEFT JOIN CONTASBANCARIAS CB 
        ON CB.CBACODIGO = CXP.CBACODIGO
      LEFT JOIN USUARIOS u 
        ON cp.CTRCONSULTOR = u.USUCODIGO
      LEFT JOIN TABELAS t2 
        ON t2.TABCODIGO = CXP.CXPPOSICAO
      LEFT JOIN PROCESSOSINTERNO p1 
        ON p1.PRICODIGO = cp.TABCENTROCUSTO
      
      WHERE
	      CXP.CXPSTATUS = 'S' 
        AND CXP.CXPPOSICAO NOT IN (26789, 26788, 26784, 33991)
					AND cp.CTRSTATUS = 'S'
					AND cp.CLICODIGO NOT IN (4241, 3811)
					AND CXP.CXPDATAVENCIMENTO < '" . date('Y-m-d') . "'
					AND CXP.CXPDATAPAGAMENTO IS NULL
					AND YEAR(CXPDATAVENCIMENTO) = '" . date('Y') . "'
					AND MONTH(CXPDATAVENCIMENTO) = '" . date('m') . "'
        
        ORDER BY CXPNUMERO
    ";
    $rows = queryTRIB($sql);
    // echo $sql;



    if (is_array($rows) && count($rows) > 0) {
      foreach ($rows as $row) {
        $valor_total_parcelas += $row['CXPVALOR'];

        $temp = [];
        $temp['cliente'] = $row['CLINOMEFANTASIA'];
        $temp['consultor'] = $row['CONSULTOR'];
        $temp['procedimento'] = $row['PROCEDIMENTO'];
        $temp['numero'] = $row['CXPNUMERO'];
        $temp['forma_cobranca'] = $row['FORMA_COBRANCA'];
        $temp['data_venc'] = datas::dataMS2S($row['CXPDATAVENCIMENTO']);
        $temp['valor_parcela'] = $row['CXPVALOR'];

        $ret[] = $temp;
      }
      $temp = [];
      $temp['cliente']        = 'zValor Total:';
      $temp['valor_parcela']  = $valor_total_parcelas;

      $ret[] = $temp;
    }

    return $ret;
  }

  private function getDadosComparacao()
  {
    $ret = [];
    $val_prev_fat_passado = 0;
    $val_real_rec_passado = 0;
    $valor_inadimplente_passado = 0;

    $val_real_rec_atual = 0;
    $val_prev_fat_atual = 0;
    $valor_inadimplente_atual = 0;

    $valor_aberto_atual = 0;

    $mes_ano = date("m/Y");
    $mes_ano_passado = date("m/Y", strtotime("-1 Year"));


    //Valor total das parcelas do ano passado
    $sql = "
    SELECT 
      SUM(C.CXPVALOR) AS TOTALARECEBER
    FROM 
      CONTASARECEBERXPARCELAS C
    LEFT JOIN 
      CONTASARECEBER ctr 
        ON ctr.CTRCODIGO = C.CTRCODIGO
    WHERE 
          C.CXPSTATUS = 'S' 
      AND ctr.CTRSTATUS = 'S' 
      AND ctr.CTRPOSICAO <> 2 
      AND C.CXPPOSICAO NOT IN (26789, 26788, 33991)
      AND YEAR(CXPDATAVENCIMENTO) = '" . (date('Y') - 1) . "'
      AND MONTH(CXPDATAVENCIMENTO) = '" . date('m') . "'
    
    ";
    $rows = queryTRIB($sql);

    $val_real_rec_passado = isset($rows[0]['TOTALARECEBER']) ? ($rows[0]['TOTALARECEBER']) : 0;
    // echo $val_real_rec_passado;

    //Valor total das parcelas do ano atual
    $sql = "
    SELECT 
      SUM(C.CXPVALOR) AS TOTALARECEBER
    
    FROM 
      CONTASARECEBERXPARCELAS C

    LEFT JOIN 
      CONTASARECEBER ctr 
    ON 
      ctr.CTRCODIGO = C.CTRCODIGO

    WHERE 
      C.CXPSTATUS = 'S' 
      AND ctr.CTRSTATUS = 'S' 
      AND ctr.CTRPOSICAO <> 2 
      AND C.CXPPOSICAO NOT IN (26789, 26788, 33991)
      AND YEAR(CXPDATAVENCIMENTO) = '" . date('Y') . "'
      AND MONTH(CXPDATAVENCIMENTO) = '" . date('m') . "'
  
    ";
    $rows = queryTRIB($sql);
    $val_prev_fat_atual = isset($rows[0]['TOTALARECEBER']) ? ($rows[0]['TOTALARECEBER']) : 0;
    // echo $val_prev_fat_atual;

    // Valor faturado Ano passado
    $sql =
      "
    SELECT 
      SUM(C.CXPVALOR) AS TOTALARECEBER
    FROM 
      CONTASARECEBERXPARCELAS C
    LEFT JOIN 
      CONTASARECEBER ctr ON ctr.CTRCODIGO = C.CTRCODIGO
    WHERE 
    C.CXPSTATUS = 'S' 
    AND ctr.CTRSTATUS = 'S' 
    AND ctr.CTRPOSICAO <> 2 
    AND C.CXPPOSICAO NOT IN (26789, 26788, 33991)
    AND CXPDATAPAGAMENTO IS NOT NULL
    AND YEAR(CXPDATAVENCIMENTO) = '" . (date('Y') - 1) . "'
    AND MONTH(CXPDATAVENCIMENTO) = '" . date('m') . "'
    ";
    $rows = queryTRIB($sql);
    $val_prev_fat_passado = isset($rows[0]['TOTALARECEBER']) ? ($rows[0]['TOTALARECEBER']) : 0;
    // echo $val_prev_fat_passado;

    // Valor Faturado atual
    $sql = "
    SELECT 
      SUM(C.CXPVALOR) AS TOTALARECEBER
    FROM 
      CONTASARECEBERXPARCELAS C
    LEFT JOIN 
      CONTASARECEBER ctr 
    ON 
    ctr.CTRCODIGO = C.CTRCODIGO
    WHERE 
        C.CXPSTATUS = 'S' 
    AND ctr.CTRSTATUS = 'S' 
    AND ctr.CTRPOSICAO <> 2 
    AND C.CXPPOSICAO NOT IN (26789, 26788, 33991)
    AND CXPDATAPAGAMENTO IS NOT NULL
    AND YEAR(CXPDATAVENCIMENTO) = '" . date('Y') . "'
    AND MONTH(CXPDATAVENCIMENTO) = '" . date('m') . "'
    ";
    $rows = queryTRIB($sql);
    $val_real_rec_atual = isset($rows[0]['TOTALARECEBER']) ? ($rows[0]['TOTALARECEBER']) : 0;
    // echo $val_real_rec_atual;

    //Inadimplentes ano passado
    $sql = "
    SELECT 
      SUM(C.CXPVALOR) AS TOTALARECEBER
    FROM 
      CONTASARECEBERXPARCELAS C
    LEFT JOIN 
      CONTASARECEBER ctr 
    ON 
      ctr.CTRCODIGO = C.CTRCODIGO
    WHERE 
        C.CXPSTATUS = 'S' 
    AND ctr.CTRSTATUS = 'S'  
    AND ctr.CTRPOSICAO <> 2 

    AND C.CXPPOSICAO NOT IN (26787, 26785, 26784)
    AND CXPDATAVENCIMENTO BETWEEN '" . date('Y-m-01', strtotime('-1 YEAR')) .  "' AND '" . date('Y-m-t') . "'
    AND CXPDATAPAGAMENTO IS NULL
    AND YEAR(CXPDATAVENCIMENTO) = '" . (date('Y') - 1) . "'
    AND MONTH(CXPDATAVENCIMENTO) = '" . date('m') . "'

    ";
    $rows = queryTRIB($sql);
    $valor_inadimplente_passado = isset($rows[0]['TOTALARECEBER']) ? ($rows[0]['TOTALARECEBER']) : 0;
    // echo $valor_inadimplente_passado . "<br>";

    //Inadimplentes ano atual
    // date('Y-m-d');
    // echo date('Y-m-01', strtotime('-1 YEAR')) . '<br>' . date('Y-m-t');

    $sql = "
    SELECT 
      SUM(C.CXPVALOR) AS TOTALARECEBER
    FROM 
      CONTASARECEBERXPARCELAS C
    LEFT JOIN 
      CONTASARECEBER ctr 
    ON 
      ctr.CTRCODIGO = C.CTRCODIGO
    WHERE 
        C.CXPSTATUS = 'S' 
    AND ctr.CTRSTATUS = 'S' 
    AND ctr.CTRPOSICAO <> 2 
    AND C.CXPPOSICAO NOT IN (26789, 26788, 26784, 33991)
    AND CXPDATAVENCIMENTO <  '" . date('Y-m-d') . "'
    AND CXPDATAPAGAMENTO IS NULL
    AND YEAR(CXPDATAVENCIMENTO) = '" . date('Y') . "'
    AND MONTH(CXPDATAVENCIMENTO) = '" . date('m') . "'
    ";
    $rows = queryTRIB($sql);
    $valor_inadimplente_atual = isset($rows[0]['TOTALARECEBER']) ? ($rows[0]['TOTALARECEBER']) : 0;

    $sql = "
    SELECT 
      SUM(C.CXPVALOR) AS TOTALARECEBER
    FROM 
      CONTASARECEBERXPARCELAS C
    LEFT JOIN 
      CONTASARECEBER ctr 
    ON 
      ctr.CTRCODIGO = C.CTRCODIGO
    WHERE 
        C.CXPSTATUS = 'S' 
    AND ctr.CTRSTATUS = 'S' 
    AND ctr.CTRPOSICAO <> 2 
    AND C.CXPPOSICAO NOT IN (26789, 26788, 26784, 33991)
    AND CXPDATAVENCIMENTO >  '" . date('Y-m-d') . "'
    AND CXPDATAPAGAMENTO IS NULL
    AND YEAR(CXPDATAVENCIMENTO) = '" . date('Y') . "'
    ";
    $rows = queryTRIB($sql);
    $valor_aberto_atual = isset($rows[0]['TOTALARECEBER']) ? ($rows[0]['TOTALARECEBER']) : 0;
    // echo $valor_inadimplente_atual . "<br>";
    $ret[0]["ano_mes"] = $mes_ano_passado;
    $ret[0]["val_prev_fat"] = $val_prev_fat_passado;
    $ret[0]["val_recebido"] = $val_real_rec_passado;
    $ret[0]["val_inadimplente"] = $valor_inadimplente_passado;
    // $ret[0]["val_aberto"] = $valor_aberto_atual;

    $ret[1]["ano_mes"] = $mes_ano;
    $ret[1]["val_prev_fat"] = $val_prev_fat_atual;
    $ret[1]["val_recebido"] = $val_real_rec_atual;
    $ret[1]["val_inadimplente"] = $valor_inadimplente_atual;
    $ret[1]["val_aberto"] = $valor_aberto_atual;

    $ret[2]["ano_mes"] = "Diferença";
    $ret[2]["val_prev_fat"] = $val_prev_fat_atual - $val_prev_fat_passado;
    $ret[2]["val_recebido"] = $val_real_rec_atual - $val_real_rec_passado;
    $ret[2]["val_inadimplente"] = $valor_inadimplente_atual - $valor_inadimplente_passado;

    return $ret;
  }

  private function getDadosResumoMensal()
  {
    $ret = [];
    $valor_total_vencido = 0;
    $valor_total_recebido = 0;
    $dia_primeiro = date('Y-m-01');
    $dia_ultimo = date('Y-m-t');
    $ultimo_dia_mes = date('t');

    //sql para buscar valor recebido
    $sql = "
    SELECT
      DAY(cxp.CXPDATAPAGAMENTO) AS DIA,
      SUM(cxp.CXPVALOR) AS TOTAL
    FROM
      CONTASARECEBERXPARCELAS cxp
      LEFT JOIN CONTASARECEBER ctr ON ctr.CTRCODIGO = cxp.CTRCODIGO
    WHERE
      cxp.CXPSTATUS = 'S'
      AND ctr.CTRSTATUS = 'S' 
      AND ctr.CTRPOSICAO <> 2 
      AND cxp.CXPPOSICAO NOT IN (26789, 26788, 33991)
      AND cxp.CXPDATAPAGAMENTO BETWEEN '" . $dia_primeiro . "' AND '" . $dia_ultimo . "'
      AND cxp.CXPDATAPAGAMENTO IS NOT NULL
    GROUP BY
      DAY(cxp.CXPDATAPAGAMENTO)";

    $rows = queryTRIB($sql);
    // echo $sql;
    // print_r($rows);
    foreach ($rows as $row) {
      $ret['recebidos'][$row['DIA']] = $row['TOTAL'];
    }
    // print_r($ret);
    $sql = "
    SELECT
      DAY(cxp.CXPDATAVENCIMENTO) AS DIA,
      SUM(cxp.CXPVALOR) AS TOTAL
    FROM
      CONTASARECEBERXPARCELAS cxp
      LEFT JOIN 
        CONTASARECEBER ctr 
      ON 
        ctr.CTRCODIGO = cxp.CTRCODIGO
    WHERE
      cxp.CXPSTATUS = 'S'
      AND ctr.CTRSTATUS = 'S' 
      AND ctr.CTRPOSICAO <> 2 
      AND cxp.CXPPOSICAO NOT IN (26789, 26788, 33991)
      AND cxp.CXPDATAVENCIMENTO BETWEEN '" . $dia_primeiro . "' AND '" . $dia_ultimo . "'
      AND cxp.CXPDATAVENCIMENTO IS NOT NULL
    GROUP BY
      DAY(cxp.CXPDATAVENCIMENTO)
      ";
    $rows = queryTRIB($sql);
    foreach ($rows as $row) {
      $ret['vencidos'][$row['DIA']] = $row['TOTAL'];
    }
    // print_r($ret);
    $retorno = [];
    for ($i = 1; $i <= $ultimo_dia_mes; $i++) {
      $temp = [];
      $temp['dia'] = $i;
      $temp['vencimentos'] = isset($ret['vencidos'][$i]) ? $ret['vencidos'][$i] : 0;
      $temp['recebidos'] = isset($ret['recebidos'][$i]) ? $ret['recebidos'][$i] : 0;
      $valor_total_vencido += $temp['vencimentos'];
      $valor_total_recebido += $temp['recebidos'];
      $retorno[] = $temp;
    }
    $temp = [];
    $temp['dia'] = "Total";
    $temp['vencimentos'] = $valor_total_vencido;
    $temp['recebidos'] = $valor_total_recebido;
    $retorno[] = $temp;
    return $retorno;
  }
}
