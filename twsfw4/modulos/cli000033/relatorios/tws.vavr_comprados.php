<?php
/*
 * Data Criacao: 24/02/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Relatório para controle de compras de vales
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class vavr_comprados {
    var $funcoes_publicas = array(
        'index'                 => true,
        'avisos'                => true,
        'incluir'               => true,
        'salvar'                => true,
        'editar'                => true,
    );

    // Classe tabela01
    private $_tabela;

    // Classe formFiltro01
    private $_filtro;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';

		$param['titulo'] = 'Controle de compras VA/VR';
		$this->_tabela = new tabela01($param);

        $param = [];
		$param['botaoTexto'] = 'Enviar';
		$param['imprimePainel'] = false;
		$param['tamanho'] = 12;
		$param['colunas'] = 1;
		$param['layout'] = 'horizontal';
        $param['link'] = getLink() . 'index';
		$this->_filtro = new formFiltro01('aniversario_relatorio', $param);
    }

    public function index() {
        $ret = '';
        $filtrar = $_GET['filtrar'] ?? 0;

        $filtro = $this->_filtro->getFiltro();

        if(empty($filtro['DATAINI']) || $filtrar) {
            $ret .= $this->_filtro;
        }

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
        $dados = [];
        if(!empty($filtro['DATAINI'])) {
            $dados = $this->getDados($filtro['DATAINI'], $filtro['DATAFIM']);
        }
		$this->_tabela->setDados($dados);

        $param = array(
			'texto' => 'Filtrar',
			'onclick' => "setLocation('" . getLink() . "index&filtrar=1')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
			'texto' => 'Incluir',
			'onclick' => "setLocation('" . getLink() . "incluir')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
			'texto' => 'Editar', //Texto no botão
			'link' => getLink() . 'incluir&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			// 'link' => 'razao',
			// 'coluna' => 'CLINOMEFANTASIA',
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'success', //padrão: azul; danger: vermelho; success: verde
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
        $this->_tabela->addColuna(array('campo' => 'colaborador', 'etiqueta' => 'Colaborador', 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'departamento', 'etiqueta' => 'Departamento', 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'vale', 'etiqueta' => 'Vale', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' => 150, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'vlr_comprado', 'etiqueta' => 'Valor Comprado', 'tipo' => 'V', 'width' => 200, 'posicao' => 'E'));
    }

    private function getDados($data_ini, $data_fim) {
        $ret = [];
        $colaboradores = [];
        $colaboradores[0] = 'Colaborador não encontrado';

        if(empty($data_fim) || $data_fim < $data_ini) {
            $data_fim = date('Ymd');
        }

        $sql = "SELECT * FROM marpa_vavrvales_compras WHERE data >= $data_ini AND data <= $data_fim";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($colaboradores[$row['id_colaborador']])) {
                    $sql = "SELECT colaborador FROM marpa_vavrvales WHERE id_vavr = ".$row['id_colaborador'];
                    $colaborador = query($sql);

                    $colaboradores[$row['id_colaborador']] = $colaborador[0]['colaborador'];
                }

                $temp = [];
                $temp['id'] = $row['id'];
                $temp['colaborador'] = $colaboradores[$row['id_colaborador']];
                $temp['departamento'] = $row['departamento'];
                $temp['vale'] = $row['vale'];
                $temp['data'] = $row['data'];
                $temp['vlr_comprado'] = $row['vlr_comprado'];
                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';

        $form = new form01();

        if(empty($id)) {
            $direciona = "salvar";

            $param = [];
            $param['campo'] = 'id_colaborador';
            $param['etiqueta'] = 'Colaborador';
            $param['largura'] = '4';
            $param['tipo'] = 'A';
            $param['obrigatorio'] = true;
            $param['tabela_itens'] = "marpa_vavrvales|id_vavr|colaborador||1 = 1";
            $form->addCampo($param);
        } else {
            $direciona = "editar&id=$id";

            $sql = "SELECT * FROM marpa_vavrvales_compras WHERE id = $id";
            $row = query($sql);

            $param = [];
            $param['campo'] = 'departamento';
            $param['etiqueta'] = 'Departamento';
            $param['largura'] = '4';
            $param['tipo'] = 'C';
            $param['obrigatorio'] = true;
            $param['valor'] = $row[0]['departamento'] ?? '';
            $form->addCampo($param);

            $param = [];
            $param['campo'] = 'vale';
            $param['etiqueta'] = 'Vale';
            $param['largura'] = '4';
            $param['tipo'] = 'C';
            $param['obrigatorio'] = true;
            $param['valor'] = $row[0]['vale'] ?? '';
            $form->addCampo($param);

            $param = [];
            $param['campo'] = 'data';
            $param['etiqueta'] = 'Data';
            $param['largura'] = '4';
            $param['tipo'] = 'D';
            $param['obrigatorio'] = true;
            $param['valor'] = isset($row[0]['data']) ? datas::dataS2D($row[0]['data']) : '';
            $form->addCampo($param);
        }

        $param = [];
		$param['campo'] = 'vlr_comprado';
		$param['etiqueta'] = 'Valor Comprado';
		$param['largura'] = '4';
		$param['tipo'] = 'V';
		$param['obrigatorio'] = true;
        $param['mascara'] = "V";
        $param['valor'] = $row[0]['vlr_comprado'] ?? '';
		$form->addCampo($param);

        $form->setEnvio(getLink() . $direciona, 'formIncluir_cliente');

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

    public function salvar () {
        $sql = "SELECT * FROM marpa_vavrvales WHERE id_vavr = ".$_POST['id_colaborador'];
        $row = query($sql);

        $valor = str_replace('.', '', $_POST['vlr_comprado']);
        $valor = str_replace(',', '.', $valor);

        $temp = [];
        $temp['id_colaborador'] = $_POST['id_colaborador'];
        $temp['departamento'] = $row[0]['departamento'];
        $temp['vale'] = $row[0]['vavr'];
        $temp['data'] = date('Ymd');
        $temp['vlr_comprado'] = $valor;

        $sql = montaSQL($temp, 'marpa_vavrvales_compras');
        query($sql);

        redireciona(getLink() . "avisos&mensagem=Compra de vale registrada com sucesso");
    }

    public function editar() {
        $id = $_GET['id'];

        $valor = str_replace('.', '', $_POST['vlr_comprado']);
        $valor = str_replace(',', '.', $valor);

        $temp = [];
        $temp['id_colaborador'] = $_POST['id_colaborador'];
        $temp['departamento'] = $_POST['departamento'];
        $temp['vale'] = $_POST['vale'];
        $temp['data'] = datas::dataD2S($_POST['data']);
        $temp['vlr_comprado'] = $valor;

        $sql = montaSQL($temp, 'marpa_vavrvales_compras', 'UPDATE', "id = $id");
        query($sql);

        redireciona(getLink() . "avisos&mensagem=Compra de vale editado com sucesso");
    }
}