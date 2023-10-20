<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_oportunidades extends cad01
{
  function __construct()
  {
    $bt = [];
    $bt['texto'] 	= 'Perfil';
    $bt['link'] 		= 'index.php?menu=testes.perfil_crm.index&tabela=crm_oportunidades&id=';
    $bt['coluna'] 	= 'id64';
    $bt['width'] 	= 30;
    $bt['flag'] 		= '';
    $bt['cor'] 		= 'success';
    $bt['posicao'] = 'inicio';

    $param = [];
    $param['botoesExtras'] = [$bt];
    
    parent::__construct('crm_oportunidades', $param);
  }
}
function crmOportunidadeTipo() {
    $ret = [['', 'Selecione uma opção.'], ['negocio_existente', 'Negócio Existente'], ['novo_negocio', 'Novo Negócio']];

    return $ret;
}
function crmOportunidadeFonteLead() {
    $ret = [['', 'Selecione uma opção.'], ['cold_call', 'Cold Call'], ['cliente_existente', 'Cliente Existente'], ['auto_gerado', 'Auto Gerado'], ['empregado', 'Empregado'], ['parceiro', 'Parceiro'], ['relacoes_publicas', 'Relações Públicas'], ['mala_direta', 'Mala Direta'], ['conferencia', 'Conferência'], ['feira_negocio', 'Feira Negócio'], ['website', 'Web Site'], ['boca_boca', 'Boca a Boca'], ['outro', 'Outro']];

    return $ret;
}
function crmOportunidadeEstagioVenda() {
    $ret = [['', 'Selecione uma opção.'], ['prospectado', 'Prospectado'], ['qualificacao', 'Qualificação'], ['analise', 'Análise'], ['proposta_valor', 'Proposta Valor'], ['identificacao_decisor', 'Identificação Decisor'], ['analise_percepcao', 'Análise Percepção'], ['proposal', 'Proposal or Price Quote'], ['negotiation', 'Negotiation or Review'], ['vencido', 'Fechado Vencido'], ['perdido', 'Fechado Perdido']];

    return $ret;
}