<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class rh_eventos extends cad01 {
  function __construct() {
    $bt = [];
    $bt['texto'] 	= 'Perfil';
    $bt['link'] 	= getLink() . 'perfil&id=';
    $bt['coluna'] 	= 'id64';
    $bt['width'] 	= 30;
    $bt['flag'] 	= '';
    $bt['cor'] 		= 'success';
    $bt['posicao']  = 'inicio';

    $param = [];
    $param['botoesExtras'] = [$bt];

    parent::__construct('rh_eventos', $param);

    $this->funcoes_publicas['perfil'] = true;
    // $this->funcoes_publicas['salvarComentarioPerfil'] = true;
    // $this->funcoes_publicas['salvarAteracoesPerfil'] = true;
    // $this->funcoes_publicas['incluirEvento'] = true;
    // $this->funcoes_publicas['salvarEvento'] = true;
  }

  public function perfil() {
    $id = base64_decode($_GET['id']);
    $param = [];
    $param['link_salvar_comentario'] = getLink() . 'salvarComentarioPerfil';
    $elemento = new painel_colaboradores($this->_tabela, $id);

    return $elemento->__toString();
  }
}