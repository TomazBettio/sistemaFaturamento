<?php
/*
 * Data Criacao:
 * Autor: 
 *
 * Descricao: 
 *
 * Alterações:
 * 				04/05/2023
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


class rh_colaboradores extends cad01 {
    
    
    protected $_titulo;
    
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

    parent::__construct('rh_colaboradores', $param);

    $this->funcoes_publicas['perfil'] = true;
    $this->funcoes_publicas['salvarComentarioPerfil'] = true;
    $this->funcoes_publicas['salvarAteracoesPerfil'] = true;
    $this->funcoes_publicas['incluirEvento'] = true;
    $this->funcoes_publicas['salvarEvento'] = true;
    $this->funcoes_publicas['salvarAlteracoesConexao'] = true;
    $this->funcoes_publicas['salvarAlteracoesContrato'] = true;
    $this->funcoes_publicas['salvarAteracoesEndereco'] = true;
    $this->funcoes_publicas['incluirEndereco'] = true;
    $this->funcoes_publicas['salvarEndereco'] = true;
    $this->funcoes_publicas['gerarPDF'] = true;
    $this->funcoes_publicas['gerarPDFConexao'] = true;
    
    $this->_titulo = 'Colaboradores';
  }

  public function perfil() {
      
    $ret = '';
    
    //Tabela de eventos
    $tabelaEvento = new tabela01();
    $tabelaEvento->addColuna(array('campo' => 'nome'	    , 'etiqueta' => 'Nome'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
    $tabelaEvento->addColuna(array('campo' => 'data'		, 'etiqueta' => 'Data', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    $tabelaEvento->addColuna(array('campo' => 'local'		, 'etiqueta' => 'Local'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
    
    $dados = $this->getDadosEvento();
    $tabelaEvento->setDados($dados);
    /*
    $param = array();
    $param['titulo'] = 'Eventos';
    $param['conteudo'] = $tabelaEvento;
    $ret .=
    */
    $param = [];
    $param['titulo'] = $this->_titulo;
    $param['conteudo'] = $tabelaEvento.'';
    
    //Tabela Contrato de Expectativas
    $tabelaContrato = new tabela01();
    $tabelaContrato->addColuna(array('campo' => 'gestor'	    , 'etiqueta' => 'Gestor'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
    $tabelaContrato->addColuna(array('campo' => 'inicio'		, 'etiqueta' => 'Início', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    $tabelaContrato->addColuna(array('campo' => 'fim'		, 'etiqueta' => 'Fim'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
    $tabelaContrato->addColuna(array('campo' => 'data'	    , 'etiqueta' => 'Data'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
    $tabelaContrato->addColuna(array('campo' => 'proximo'		, 'etiqueta' => 'Data do Próximo Alinhamento', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    $tabelaContrato->addColuna(array('campo' => ''		, 'etiqueta' => 'Local'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
    
    
    
    $ret = addCard($param);
    
    return  $ret;
    
    /*
    $bloco1 = addCard(array('conteudo' => 'bloco 1'));
    $bloco2 = addCard(array('conteudo' => 'bloco 2'));
    $bloco3 = addCard(array('conteudo' => 'bloco 3'));
    
    $param = array();
    $param['tamanhos'] = array(3, 3, 3);
    $param['conteudos'] = array($bloco1, $bloco2, $bloco3);
    $ret .= addLinha($param);
    
    $ret = addCard(array('conteudo' => $ret));
    return $ret;*/
    
      /*
    $id = base64_decode($_GET['id']);
    $elemento = new painel_colaboradores($this->_tabela, $id);
    $ret = $elemento->__toString();
    return $ret;*/
  }
  
  public function getDadosEvento() {
      $ret = [];
      $id = base64_decode($_GET['id']);
      $q = "SELECT * FROM rh_eventos WHERE entidade_tipo = '{$this->_tabela}' AND entidade_id = {$id} AND ativo = 'S'";
      $dados = query($q);
      if(is_array($dados) && count($dados) > 0){
          foreach ($dados as $row){
              $temp = [];
              $temp['nome'] = $row['nome'];
              $temp['data'] = $row['data'];//datas::dataMS2D($row['data']);
              $temp['local']= $row['onde'];
              
              $ret[] = $temp;
          }
      }
      
      return $ret;
  }

  public function ajax($op = '') {
    if($op == ''){
        $op = getOperacao();
    }
    $ret = '';
    $tabela = $_GET['tabela'];
    $id = $_GET['id'];
    $elemento = new painel_colaboradores($tabela,$id);
    
    switch($op) {
        case 'elemento_resumo':
            $ret = $elemento->cardGeral();
            break;
        case 'elemento_detalhes':
            $ret = $elemento->cardDetalhesCamposChave('rh_enderecos', 'primary');
            break;
        case 'elemento_atualizacoes':
            $ret = $elemento->cardAtualizcoes('rh_atualizacoes');
            break;
        case 'elemento_eventos':
            $ret = $elemento->cardDetalhesAtividades('rh_eventos' , 'secondary');
            break;
        case 'elemento_emails':
            $ret = $elemento->cardEmails('rh_email' , 'secondary');
            break;
        case 'elemento_documentos':
            $ret = $elemento->cardDetalhesArquivos('rh_arquivos');
            break;
        case 'elemento_comentarios':
            $ret = $elemento->cardComentarios('rh_comentarios' , 'primary', true);
            break;
        case 'elemento_conexao':
            $ret = $elemento->cardConexao('rh_conexao_colaborador');
            break;
        case 'elemento_contrato' :
            $ret = $elemento->cardContrato('rh_contrato_expectativa', true);
            break;
        default:
            $ret = parent::ajax();
    }
    return $ret;
}

  public function salvarAteracoesPerfil(){
    $id = base64_decode($_GET['id']);
    $elemento = new painel_colaboradores($this->_tabela, $id);
    $elemento->salvarAlteracoes();
    
    redireciona(getLink() . "perfil&id=".$_GET['id']);
  }

  public function salvarComentarioPerfil() {
    $id = base64_decode($_GET['id']);
    $tabela = $_GET['tabela'];
    $id_comentario = isset($_GET['id_comentario']) ? base64_decode($_GET['id_comentario']) : 0;

    $painel = new painel_colaboradores($tabela, $id);
    $painel->salvarComentario($_POST['mensagem'], 'rh_comentarios', $id_comentario);
    
    redireciona(getLink() . "perfil&id=".$_GET['id']);
  }

  public function incluirEvento() {
    $id = $_GET['id'];
    $id = base64_decode($id);
    $tabela = $_GET['tabela'];

    putAppVar('link_salvar_cad', getLink() . 'salvarEvento&id='.$_GET['id']);
    putAppVar('link_redirecionar_cad_cancelar', getLink() . "perfil&id={$_GET['id']}&tabela=$tabela");

    $cad = new cad01('rh_eventos');
    $sys003 = $cad->getSys003();
    
    $dados = [];
    foreach($sys003 as $sys) {
      $dados[$sys['campo']] = '';
    }

    $dados['entidade_tipo'] = $tabela;
    $dados['entidade_id'] = $id;
    $dados['ativo'] = 'S';

    return $cad->incluir($dados);
  }

  public function salvarEvento() {
    $id = $_GET['id'];
    $id = base64_decode($id);

    $cad = new cad01('rh_eventos');
    $cad->salvar(0, $_POST['formCRUD'], 'I');

    $param = [];
    $param['descricao'] = 'Salvo um novo evento';
    $param['operacao'] = 'implementação';

    $painel = new painel_colaboradores($this->_tabela, $id);
    $painel->gravarAtualizacoes($param);

    redireciona(getLink() . 'perfil&id='.$_GET['id']);
  }

  public function incluirEndereco() {
    $id = base64_decode($_GET['id']);
    $tabela = $_GET['tabela'];

    putAppVar('link_salvar_cad', getLink() . 'salvarEndereco&id='.$_GET['id']);
    putAppVar('link_redirecionar_cad_cancelar', getLink() . "perfil&id={$_GET['id']}&tabela=$tabela");

    $cad = new cad01('rh_enderecos');
    $sys003 = $cad->getSys003();
    
    $dados = [];
    foreach($sys003 as $sys) {
      $dados[$sys['campo']] = '';
    }

    $dados['entidade'] = $tabela;
    $dados['id_entidade'] = $id;
    $dados['ativo'] = 'S';

    return $cad->incluir($dados);
  }

  public function salvarEndereco() {
    $id = base64_decode($_GET['id']);

    $cad = new cad01('rh_enderecos');
    $cad->salvar(0, $_POST['formCRUD'], 'I');

    $param = [];
    $param['descricao'] = 'Salvo um novo endereço';
    $param['operacao'] = 'implementação';

    $painel = new painel_colaboradores($this->_tabela, $id);
    $painel->gravarAtualizacoes($param);

    redireciona(getLink() . 'perfil&id='.$_GET['id']);
  }

  public function salvarAlteracoesConexao() {
    $id = base64_decode($_GET['id']);
    
    $elemento = new painel_colaboradores($this->_tabela, $id);
    $elemento->salvarAlteracoesConexao('rh_conexao_colaborador');
    
    redireciona(getLink() . "perfil&id=".$_GET['id']);
  }

  public function salvarAlteracoesContrato() {
    $id = base64_decode($_GET['id']);
    
    $elemento = new painel_colaboradores($this->_tabela, $id);
    $elemento->salvarAlteracoesContrato('rh_contrato_expectativa');
    
    redireciona(getLink() . "perfil&id=".$_GET['id']);
  }

  public function salvarAteracoesEndereco() {
    $id = base64_decode($_GET['id']);
    $id_endereco = isset($_GET['id_endereco']) ? base64_decode($_GET['id_endereco']) : '';
    
    $elemento = new painel_colaboradores($this->_tabela, $id);
    $elemento->salvarAlteracoesEndereco('rh_enderecos', $id_endereco);
    
    redireciona(getLink() . "perfil&id=".$_GET['id']);
  }

  public function gerarPDF() {
    $id = base64_decode($_GET['id']);
    $tabela = $_GET['tabela'];

    $pdf = new rh_contrato_pdf($tabela, $id);
    $pdf->index();

    $link = 'arquivos/' . $tabela . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'contrato_expectativa.pdf';
    redirect($link, false);
  }

  public function gerarPDFConexao() {
    $id = base64_decode($_GET['id']);
    $tabela = $_GET['tabela'];

    $pdf = new rh_conexao_pdf($tabela, $id);
    $pdf->index();

    $link = 'arquivos/' . $tabela . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'conexao.pdf';
    redirect($link, false);
  }
}