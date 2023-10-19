<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class mgt_monofasico extends cad01
{
  function __construct()
  {
    $param = [];
    $param['excluir_permanente'] = true;
    parent::__construct('mgt_monofasico', $param);
  }

  function salvar($id = 0, $dados = array(), $acao = '')
  {
    $formCrud = count($dados) > 0 ? $dados : getParam($_POST, 'formCRUD', array());
    $formCrud['cnpj'] = str_replace(['/', '.', '-'], '', $formCrud['cnpj']);
    $formCrud['razao']   = strtoupper($formCrud['razao']);
    $formCrud['datactr'] = datas::dataD2S($formCrud['datactr']);
    // echo $formCrud['datactr'];

    return parent::salvar($id, $formCrud, $acao);
  }

  protected function getEntrada($id, $decodificar_id = true)
  {
    $ret = parent::getEntrada($id, $decodificar_id);
    $ret['datactr'] = datas::dataS2D($ret['datactr']);

    return $ret;
  }
}
