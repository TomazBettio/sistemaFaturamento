<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class mgt_ncm extends cad01
{
  function __construct()
  {
    $param = [];
    parent::__construct('mgt_ncm', 'Cadastro NCM', $param);
  }

  function salvar($id = 0, $dados = array(), $acao = '')
  {
    $formCrud = count($dados) > 0 ? $dados : getParam($_POST, 'formCRUD', array());
    $formCrud['aliq_pis'] = str_replace(',', '.', $formCrud['aliq_pis']);
    $formCrud['aliq_cofins'] = str_replace(',', '.', $formCrud['aliq_cofins']);
//    $formCrud['vig_ini'] = datas::dataD2MSQL($formCrud['vig_ini']);
//    $formCrud['vig_fim'] = datas::dataD2MSQL($formCrud['vig_fim']);
    return parent::salvar($id, $formCrud, $acao);
  }
}
