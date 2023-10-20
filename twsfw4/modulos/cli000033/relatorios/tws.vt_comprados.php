<?php
/*
 * Data Criacao: 24/02/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Relatório para controle de compras de VT
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class vt_comprados {
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
		$param['titulo'] = 'Controle de compras VT';
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

        // ================= BOTÕES ===========================
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
        $this->_tabela->addColuna(array('campo' => 'colaborador', 'etiqueta' => 'Colaborador', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'departamento', 'etiqueta' => 'Departamento', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'vlr_tri', 'etiqueta' => 'Valor TRI', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'qt_tri', 'etiqueta' => 'Quant. TRI', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'vlr_teu', 'etiqueta' => 'Valor TEU', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'qt_teu', 'etiqueta' => 'Quant. TEU', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'vlr_sim', 'etiqueta' => 'Valor SIM', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'qt_sim', 'etiqueta' => 'Quant. SIM', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'valor_gasto', 'etiqueta' => 'Valor Gasto', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados($data_ini, $data_fim) {
        $ret = [];
        $colaboradores = [];

        if(empty($data_fim) || $data_fim < $data_ini) {
            $data_fim = date('Ymd');
        }

        $sql = "SELECT colaborador, id_vt FROM marpa_valesVT";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $colaboradores[$row['id_vt']] = $row['colaborador'];
            }
        }

        $sql = "SELECT * FROM marpa_valesvt_compras WHERE data >= $data_ini AND data <= $data_fim";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id']             = $row['id'];
                $temp['colaborador']    = $colaboradores[$row['id_colaborador']];
                $temp['departamento']   = $row['departamento'];
                $temp['vlr_tri']        = $row['vlr_tri'];
                $temp['qt_tri']         = $row['qt_tri'];
                $temp['vlr_teu']        = $row['vlr_teu'];
                $temp['qt_teu']         = $row['qt_teu'];
                $temp['vlr_sim']        = $row['vlr_sim'];
                $temp['qt_sim']         = $row['qt_sim'];
                $temp['data']           = $row['data'];
                $temp['valor_gasto']    = $row['valor_gasto'];
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
            $param['tabela_itens'] = "marpa_valesVT|id_vt|colaborador||1 = 1";
            $form->addCampo($param);
        } else {
            $direciona = "editar&id=$id";
            
            $sql = "SELECT * FROM marpa_valesvt_compras WHERE id = $id";
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
        $param['campo'] = 'valor_gasto';
        $param['etiqueta'] = 'Valor Gasto';
        $param['largura'] = '4';
        $param['tipo'] = 'V';
        $param['mascara'] = 'V';
        $param['obrigatorio'] = true;
        $param['valor'] = $row[0]['valor_gasto'] ?? '';
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

    public function salvar() {
        $sql = "SELECT * FROM marpa_valesVT WHERE id_vt = ".$_POST['id_colaborador'];
        $row = query($sql);

        $valor = str_replace('.', '', $_POST['valor_gasto']);
        $valor = str_replace(',', '.', $valor);

        $temp = [];
        $temp['id_colaborador'] = $_POST['id_colaborador'];
        $temp['departamento'] = $row[0]['departamento'] ?? '';
        $temp['vlr_tri']      = $row[0]['vlr_tri'];
        $temp['qt_tri']       = $row[0]['qt_tri'];
        $temp['vlr_teu']      = $row[0]['vlr_teu'];
        $temp['qt_teu']       = $row[0]['qt_teu'];
        $temp['vlr_sim']      = $row[0]['vlr_sim'];
        $temp['qt_sim']       = $row[0]['qt_sim'];
        $temp['data'] = date('Ymd');
        $temp['valor_gasto'] = $valor;

        $sql = montaSQL($temp, 'marpa_valesvt_compras');
        query($sql);

        redireciona(getLink() . "avisos&mensagem=Compra registrada com sucesso");
    }

    public function editar() {
        $id = $_GET['id'];

        $vlr_tri = str_replace('.', '', $_POST['vlr_tri']);
        $vlr_tri = str_replace(',', '.', $vlr_tri);

        $vlr_teu = str_replace('.', '', $_POST['vlr_teu']);
        $vlr_teu = str_replace(',', '.', $vlr_teu);

        $vlr_sim = str_replace('.', '', $_POST['vlr_sim']);
        $vlr_sim = str_replace(',', '.', $vlr_sim);

        $valor = str_replace('.', '', $_POST['valor_gasto']);
        $valor = str_replace(',', '.', $valor);

        $temp = [];
        $temp['departamento'] = $_POST['departamento'];
        $temp['vlr_tri']      = $vlr_tri;
        $temp['qt_tri']       = $_POST['qt_tri'];
        $temp['vlr_teu']      = $vlr_teu;
        $temp['qt_teu']       = $_POST['qt_teu'];
        $temp['vlr_sim']      = $vlr_sim;
        $temp['qt_sim']       = $_POST['qt_sim'];
        $temp['data']         = datas::dataD2S($_POST['data']);
        $temp['valor_gasto']  = $valor;

        $sql = montaSQL($temp, 'marpa_valesvt_compras', 'UPDATE', "id = $id");
        query($sql);

        redireciona(getLink() . "avisos&mensagem=Compra editada com sucesso");
    }
}