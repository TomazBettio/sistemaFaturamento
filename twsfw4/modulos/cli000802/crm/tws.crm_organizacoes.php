<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_organizacoes extends cad01
{
  function __construct()
  {
    $bt = [];
    $bt['texto'] 	= 'Perfil';
    $bt['link'] 		= 'index.php?menu=testes.perfil_crm.index&tabela=crm_organizacoes&id=';
    $bt['coluna'] 	= 'id64';
    $bt['width'] 	= 30;
    $bt['flag'] 		= '';
    $bt['cor'] 		= 'success';
    $bt['posicao'] = 'inicio';

    $param = [];
    $param['botoesExtras'] = [$bt];
    
    parent::__construct('crm_organizacoes', $param);
  }

}
function crmAtividades() {
    $ret = [['', 'Selecione uma opção.'], ['vestuario', 'Vestuário'], ['banco', 'Banco'], ['biotecnologia', 'Biotecnologia'], ['quimica', 'Química'], ['comunicacoes', 'Comunicações'], ['construcao', 'Construção'], ['consultoria', 'Consultoria'], ['educacao', 'Educação'], ['eletronicos', 'Eletrônicos'], ['energia', 'Energia'], ['engenharia', 'Engenharia'], ['entretenimento', 'Entretenimento'], ['meio_ambiente', 'Meio Ambiente'], ['financas', 'Finanças'], ['alimentacao_bebida', 'Alimentação e Bebidas'], ['governo', 'Governo'], ['saude', 'Saúde'], ['hoteis', 'Hotéis'], ['seguros', 'Seguros'], ['maquinaria', 'Maquinaria'], ['industria', 'Indústria'], ['midia', 'Mídia'], ['ong', 'ONG'], ['recreacao', 'Recreação'], ['varejo', 'Varejo'], ['entrega', 'Entrega'], ['tecnologia', 'Tecnologia'], ['telecomunicacoes', 'Telecomunicações'], ['transporte', 'Transporte'], ['servicos_publicos', 'Serviços Públicos'] ,['outros', 'Outros']];
    
    return $ret;
}
function crmAvaliacao() {
    $ret = [['', 'Selecione uma opção.'], ['adquirido', 'Adquirido'], ['ativo', 'Ativo'], ['perdido', 'Perdido'], ['projeto_cancelado', 'Projeto Cancelado'], ['encerrado', 'Encerrado']];
    
    return $ret;
}
function crmTipo() {
    $ret = [['', 'Selecione uma opção.'], ['analista', 'Analista'], ['concorrente', 'Concorrente'], ['cliente', 'Cliente'], ['integrador', 'Integrador'], ['investidor', 'Investidor'], ['parceiro', 'Parceiro'], ['imprensa', 'Imprensa'], ['prospect', 'Prospect'], ['revendedor', 'Revendedor'], ['outro', 'Outro']];
    
    return $ret;
}
function crmRecusarEmail() {
  $ret = [['', 'Selecione uma opção.'], ['S', 'Sim'], ['N', 'Não']];

  return $ret;
}
