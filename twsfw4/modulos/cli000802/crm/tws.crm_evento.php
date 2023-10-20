<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_evento extends cad01
{
  function __construct()
  {
    $param = [];
    
    parent::__construct('crm_evento', $param);
  }
}
function eventoEntidade() {
  $ret = [['', 'Selecione uma opção'], ['categorias', 'Categorias'], ['contatos', 'Contatos'], ['email', 'E-mail'], ['enderecos', 'Endereços'], ['lead', 'LEAD'], ['marcas', 'Marcas'], ['oportunidades', 'Oportunidades'], ['organizacoes', 'Organizações'], ['pipeline_itens', 'Pipeline Itens'], ['produtos', 'Produtos'], ['sla', 'SLA'], ['tarefa', 'Tarefa'], ['telefones', 'Telefones'], ['Vendedores', 'Responsável']];

  return $ret;
}