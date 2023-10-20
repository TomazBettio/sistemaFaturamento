<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_telefones extends cad01
{
  function __construct()
  {
    $bt = [];
    $bt['texto'] 	= 'Perfil';
    $bt['link'] 		= 'index.php?menu=testes.perfil_crm.index&tabela=crm_telefones&id=';
    $bt['coluna'] 	= 'id64';
    $bt['width'] 	= 30;
    $bt['flag'] 		= '';
    $bt['cor'] 		= 'success';
    $bt['posicao'] = 'inicio';

    $param = [];
    $param['botoesExtras'] = [$bt];
    
    parent::__construct('crm_telefones', $param);
  }
}
function telefonesEntidades() {
  $ret = [['', 'Selecione uma opção'], ['contatos', 'Contato'], ['evento', 'Evento'], ['lead', 'Lead'], ['organizacoes', 'Organização'], ['vendedores', 'Vendedores'], ['marcas', 'Marca']];

  return $ret;
}