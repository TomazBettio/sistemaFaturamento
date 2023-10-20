<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_contatos extends cad01
{
  function __construct()
  {
    $bt = [];
    $bt['texto'] 	= 'Perfil';
    $bt['link'] 		= 'index.php?menu=testes.perfil_crm.index&tabela=crm_contatos&id=';
    $bt['coluna'] 	= 'id64';
    $bt['width'] 	= 30;
    $bt['flag'] 		= '';
    $bt['cor'] 		= 'success';
    $bt['posicao'] = 'inicio';

    $param = [];
    $param['botoesExtras'] = [$bt];
    
    parent::__construct('crm_contatos', $param);
  }
}
function crmContatosFontLead() {
  $ret = [['', 'Selecione uma opção'], ['cold_call', 'Cold Call'], ['cliente_existente', 'Cliente existente'], ['auto_gerado', 'Auto Gerado'], ['empregado', 'Empregado'], ['parceiro', 'Parceiro'], ['relacoes_publicas', 'Relações Públicas'], ['mala_direta', 'Mala Direta'], ['conferencia', 'Conferência'], ['feira_negocios', 'Feira Negócios'], ['website', 'Web Site'], ['boca_boca', 'Boca a Boca'], ['outro', 'Outro']];

  return $ret;
}
function crmContatosOrganizacao() {
  $ret = [];

  $sql = 'SELECT id, nome FROM crm_organizacoes';
  $rows = query($sql);

  $ret[] = ['', 'Selecione uma opção'];

  if(count($rows) > 0) {
    foreach($rows as $row) {
      $ret[] = [$row['id'], $row['nome']];
    }
  }

  return $ret;
}