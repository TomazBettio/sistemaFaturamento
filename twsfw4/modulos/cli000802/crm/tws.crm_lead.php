<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class crm_lead extends cad01
{
  function __construct()
  {
    $bt = [];
    $bt['texto'] 	= 'Perfil';
    $bt['link'] 		= 'index.php?menu=testes.perfil_crm.index&tabela=crm_lead&id=';
    $bt['coluna'] 	= 'id64';
    $bt['width'] 	= 30;
    $bt['flag'] 		= '';
    $bt['cor'] 		= 'success';
    $bt['posicao'] = 'inicio';

    $param = [];
    $param['botoesExtras'] = [$bt];

    parent::__construct('crm_lead', $param);

    $this->funcoes_publicas['perfil'] = true;
    $this->funcoes_publicas['salvarComentarioPerfil'] = true;
    $this->funcoes_publicas['salvarAteracoesPerfil'] = true;
    $this->funcoes_publicas['incluirEvento'] = true;
    $this->funcoes_publicas['salvarEvento'] = true;
  }

  public function perfil() {
    $id = base64_decode($_GET['id']);
    $param = [];
    $param['link_salvar_comentario'] = getLink() . 'salvarComentarioPerfil';
    $elemento = new elemento_crm($this->_tabela, $id);

    return $elemento . '';
  }

  public function salvarComentarioPerfil(){
    $id = base64_decode($_GET['id']);
    $elemento = new elemento_crm($this->_tabela, $id);
    $elemento->salvarComentario();
    return $elemento . '';
  }

  public function salvarAteracoesPerfil(){
    $id = base64_decode($_GET['id']);
    $elemento = new elemento_crm($this->_tabela, $id);
    $elemento->salvarAlteracoes();
    return $elemento . '';
  }

  public function incluirEvento() {
    $id = $_GET['id'];
    $id = base64_decode($id);
    $cad = new cad01('crm_evento');
    $dados = [];
    $sys003 = $cad->getSys003();

    foreach($sys003 as $sys) {
      $dados[$sys['campo']] = '';
    }

    $dados['entidade_tipo'] = $this->_tabela;
    $dados['entidade_id'] = $id;
    $dados['ativo'] = 'S';

    return $cad->incluir($dados);
  }

  public function salvarEvento() {
    $id = $_GET['id'];
    $id = base64_decode($id);

    $cad = new cad01('crm_evento');
    $cad->salvar(0, $_POST['formCRUD'], 'I');

    redireciona(getLink() . 'perfil&id='.$_GET['id']);
  }
}