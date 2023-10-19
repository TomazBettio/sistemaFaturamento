<?php
/*
 * Data Criacao: 27/01/2022
 * Autor: 
 *
 * Descricao:
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class controle_logs
{
  var $funcoes_publicas = array(
    'index'     => true,
  );

  //Titulo
  private $_titulo;

  //Programa
  private $_programa;

  //dados
  private $_dados;

  //tabela
  private $_tabela;

  function __construct()
  {
    $this->_titulo = 'Modelo de Programa';
    $this->_programa = get_class($this);
    conectaMF();

    $this->_dados = $this->getDados();

    $this->_tabela = new tabela01;
  }

  public function index()
  {
    $ret = '';

    $this->montaColunas();

    $ret .= $this->_tabela->setDados($this->_dados);

    return $ret;
  }

  private function getDados()
  {
    $ret = [];

    $sql = "SELECT * FROM mgt_monofasico_log_erros";
    $rows = queryMF($sql);

    if (is_array($rows) && count($rows) > 0) {

      foreach ($rows as $row) {
        $temp = [];
        $temp[''] = $row[''];
        $temp[''] = $row[''];
        $temp[''] = $row[''];
        $temp[''] = $row[''];
        $temp[''] = $row[''];
        $temp[''] = $row[''];

        $ret[] = $temp;
      }
    }


    return $ret;
  }
}
