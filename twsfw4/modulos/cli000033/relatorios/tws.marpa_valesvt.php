<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

// class marpa_valesvt_antigo extends cad02
// {
//   function __construct()
//   {
//     $param = [];
//     parent::__construct('marpa_valesVT', 'Controle - VT', $param);
//   }
  
// }

class marpa_valesvt {
  var $funcoes_publicas = array(
    'index'           => true,
    'avisos'          => true,
    'incluir'         => true,
    'salvar'          => true,
    'excluir'         => true,
  );

  // Classe tabela01
  private $_tabela;

  // Classe formFiltro01
  private $_filtro;

  function __construct() {
    $param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Controle - VT';
		$this->_tabela = new tabela01($param);
  }

  public function index() {
    $ret = '';

    // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
    $dados = $this->getDados();
    $this->_tabela->setDados($dados);

    $param = array(
			'texto' => 'Incluir',
			'onclick' => "setLocation('" . getLink() . "incluir')",
		);
		$this->_tabela->addBotaoTitulo($param);

    $param = array(
			'texto' => 'Editar', //Texto no botão
			'link' => getLink() . 'incluir&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'success', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

    $param = array(
			'texto' => 'Excluir', //Texto no botão
			'link' => getLink() . 'excluir&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			// 'link' => 'razao',
			// 'coluna' => 'CLINOMEFANTASIA',
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'danger', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

    $ret .= $this->_tabela;

    return $ret;
  }

  public function avisos() {
		$tipo = $_GET['tipo'] ?? '';

		if ($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->index();
	}

  private function montaColunas() {
    $this->_tabela->addColuna(array('campo' => 'colaborador', 'etiqueta' => 'Colaborador', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    $this->_tabela->addColuna(array('campo' => 'departamento', 'etiqueta' => 'Departamento', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    $this->_tabela->addColuna(array('campo' => 'vlr_tri', 'etiqueta' => 'Valor TRI', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
    $this->_tabela->addColuna(array('campo' => 'qt_tri', 'etiqueta' => 'Quant. TRI', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
    $this->_tabela->addColuna(array('campo' => 'vlr_teu', 'etiqueta' => 'Valor TEU', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
    $this->_tabela->addColuna(array('campo' => 'qt_teu', 'etiqueta' => 'Quant. TEU', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
    $this->_tabela->addColuna(array('campo' => 'vlr_sim', 'etiqueta' => 'Valor SIM', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
    $this->_tabela->addColuna(array('campo' => 'qt_sim', 'etiqueta' => 'Quant. SIM', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
  }

  private function getDados() {
    $ret = [];

    $sql = "SELECT * FROM marpa_valesVT";
    $rows = query($sql);

    if(is_array($rows) && count($rows) > 0) {
      foreach($rows as $row) {
        $temp = [];
        $temp['colaborador'] = $row['colaborador'];
        $temp['departamento'] = $row['departamento'];
        $temp['vlr_tri'] = $row['vlr_tri'];
        $temp['qt_tri'] = $row['qt_tri'];
        $temp['vlr_teu'] = $row['vlr_teu'];
        $temp['qt_teu'] = $row['qt_teu'];
        $temp['vlr_sim'] = $row['vlr_sim'];
        $temp['qt_sim'] = $row['qt_sim'];
        $temp['id'] = $row['id_vt'];

        $ret[] = $temp;
      }
    }

    return $ret;
  }

  public function incluir() {
    $ret = '';
    $id = $_GET['id'] ?? '';

    if(!empty($id)) {
      $sql = "SELECT * FROM marpa_valesVT WHERE id_vt = $id";
      $row = query($sql);
    }

    $form = new form01();

    $param = [];
    $param['campo'] = 'colaborador';
    $param['etiqueta'] = 'Colaborador';
    $param['largura'] = '4';
    $param['tipo'] = 'C';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['colaborador'] ?? '';
    $form->addCampo($param);

    $param = [];
    $param['campo'] = 'departamento';
    $param['etiqueta'] = 'Departamento';
    $param['largura'] = '4';
    $param['tipo'] = 'C';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['departamento'] ?? '';
    $form->addCampo($param);

    $param = [];
    $param['campo'] = 'vlr_tri';
    $param['etiqueta'] = 'Valor TRI';
    $param['largura'] = '4';
    $param['tipo'] = 'V';
    $param['mascara'] = 'V';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['vlr_tri'] ?? '';
    $form->addCampo($param);

    $param = [];
    $param['campo'] = 'qt_tri';
    $param['etiqueta'] = 'Quant. TRI';
    $param['largura'] = '4';
    $param['tipo'] = 'N';
    $param['mascara'] = 'N';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['qt_tri'] ?? '';
    $form->addCampo($param);

    $param = [];
    $param['campo'] = 'vlr_teu';
    $param['etiqueta'] = 'Valor TEU';
    $param['largura'] = '4';
    $param['tipo'] = 'V';
    $param['mascara'] = 'V';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['vlr_teu'] ?? '';
    $form->addCampo($param);

    $param = [];
    $param['campo'] = 'qt_teu';
    $param['etiqueta'] = 'Quant. TEU';
    $param['largura'] = '4';
    $param['tipo'] = 'N';
    $param['mascara'] = 'N';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['qt_teu'] ?? '';
    $form->addCampo($param);

    $param = [];
    $param['campo'] = 'vlr_sim';
    $param['etiqueta'] = 'Valor SIM';
    $param['largura'] = '4';
    $param['tipo'] = 'V';
    $param['mascara'] = 'V';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['vlr_sim'] ?? '';
    $form->addCampo($param);

    $param = [];
    $param['campo'] = 'qt_sim';
    $param['etiqueta'] = 'Quant. SIM';
    $param['largura'] = '4';
    $param['tipo'] = 'N';
    $param['mascara'] = 'N';
    $param['obrigatorio'] = true;
    $param['valor'] = $row[0]['qt_sim'] ?? '';
    $form->addCampo($param);

    $form->setEnvio(getLink() . "salvar&id=$id", 'formIncluir_cliente');

    $ret .= $form;

		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Incluir Compra';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
  }

  public function salvar() {
    $ret = [];
    $id = $_GET['id'] ?? '';

    $vlr_tri = str_replace('.', '', $_POST['vlr_tri']);
    $vlr_tri = str_replace(',', '.', $vlr_tri);

    $vlr_teu = str_replace('.', '', $_POST['vlr_teu']);
    $vlr_teu = str_replace(',', '.', $vlr_teu);

    $vlr_sim = str_replace('.', '', $_POST['vlr_sim']);
    $vlr_sim = str_replace(',', '.', $vlr_sim);

    $temp = [];
    $temp['colaborador']  = $_POST['colaborador'];
    $temp['departamento'] = $_POST['departamento'];
    $temp['vlr_tri']      = $vlr_tri;
    $temp['qt_tri']       = $_POST['qt_tri'];
    $temp['vlr_teu']      = $vlr_teu;
    $temp['qt_teu']       = $_POST['qt_teu'];
    $temp['vlr_sim']      = $vlr_sim;
    $temp['qt_sim']       = $_POST['qt_sim'];
    $ret[] = $temp;

    $sql = empty($id) ? montaSQL($temp, 'marpa_valesVT') : montaSQL($temp, 'marpa_valesVT', 'UPDATE', "id_vt = $id");
    query($sql);

    redireciona(getLink() . "avisos&mensagem=Cadastrado com sucesso");
  }

  public function excluir() {
    $id = $_GET['id'];

    if(!empty($id)) {
      $sql = "DELETE FROM marpa_valesVT WHERE id_vt = $id";
      query($sql);

      $msgm = "Registro excluido com sucesso";
      $tipo = '';
    } else {
      $msgm = "Erro ao excluido o registro";
      $tipo = 'erro';
    }

    redireciona(getLink() . "avisos&mensagem=$msgm&tipo=$tipo");
  }
}