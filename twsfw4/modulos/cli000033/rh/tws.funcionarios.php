<?php

/*
 * Data Criacao: 13/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para cadastro de funcionários
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class funcionarios {
    var $funcoes_publicas = array(
        'avisos'            => true,
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';
		// $param['ordenacao'] = false;
		$param['titulo'] = 'Funcionários';
		$this->_tabela = new tabela01($param);
    }

    public function avisos() {
		$tipo = $_GET['tipo'] ?? '';
        $redireciona = $_GET['redireciona'] ?? 'index';

		if ($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->{$redireciona}();
	}

    public function index() {
        $ret = '';

        $this->montaColunas();
        $dados = $this->getDados();
        $this->_tabela->setDados($dados);

        // =============== INCLUI UM BOTÃO NO TÍTULO ===============================
		$param = array(
			'texto' => 'Novo Funcionário',
			'onclick' => "setLocation('" . getLink() . "incluir')",
		);
		$this->_tabela->addBotaoTitulo($param);

        // =============== INCLUI UM BOTÃO AO LADO ===============================
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

        $ret .= $this->_tabela;

        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'nome', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'empresa', 'etiqueta' => 'Empresa', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'salario', 'etiqueta' => 'Salário', 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'pf', 'etiqueta' => 'PF', 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'quinquenio', 'etiqueta' => 'Quinquênio', 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT * FROM funcionarios";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id']         = $row['id_funcionarios'];
                $temp['nome']       = $row['nome'];
                $temp['empresa']    = $row['empresa'];
                $temp['salario']    = $row['salario'];
                $temp['pf']         = $row['pf'];
                $temp['quinquenio'] = $row['quinquenio'];
                $temp['ativo']      = ($row['ativo'] == 'S') ? 'Sim' : 'Não';

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';

        $id = $_GET['id'] ?? '';

        if(!empty($id)) {
            $sql = "SELECT * FROM funcionarios WHERE id_funcionarios = $id";
            $row = query($sql);
            $row = $row[0];
        }

        $form = new form01();
        $form->setDescricao('Funcionários');

        $param = [];
		$param['campo'] = 'nome';
		$param['etiqueta'] = 'Nome';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['nome'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'empresa';
		$param['etiqueta'] = 'Empresa';
		$param['largura'] = '4';
		$param['tipo'] = 'A';
        $param['funcao_lista'] = "[['MRP', 'MRP'], ['MGT', 'MGT']]";
		$param['obrigatorio'] = true;
		// $param['tamanho'] = 255;
        $param['valor'] = $row['empresa'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'salario';
		$param['etiqueta'] = 'Salário';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
        // $param['mascara'] = 'V';
		$param['obrigatorio'] = true;
		// $param['tamanho'] = 255;
        $param['valor'] = (isset($row['salario'])) ? number_format($row['salario'], 2, ',', '.') : 0;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'pf';
		$param['etiqueta'] = 'PF';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
        // $param['mascara'] = 'V';
		$param['obrigatorio'] = true;
		// $param['tamanho'] = 255;
        $param['valor'] = (isset($row['pf'])) ? number_format($row['pf'], 2, ',', '.') : 0;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'quinquenio';
		$param['etiqueta'] = 'Quinquênio';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
        // $param['mascara'] = 'V';
		$param['obrigatorio'] = true;
		// $param['tamanho'] = 255;
        $param['valor'] = (isset($row['quinquenio'])) ? number_format($row['quinquenio'], 2, ',', '.') : 0;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'ativo';
		$param['etiqueta'] = 'Ativo';
		$param['largura'] = '4';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = '000003';
		$param['obrigatorio'] = true;
		// $param['tamanho'] = 255;
        $param['valor'] = $row['ativo'] ?? '';
		$form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formEditarFuncionarios');

        return $form;
    }

    public function salvar() {
        if(!empty($_POST) && count($_POST) > 0) {
            $id = $_GET['id'] ?? '';

            $salario = str_replace('.', '', $_POST['salario']);
            $salario = str_replace(',', '.', $salario);

            $pf = str_replace('.', '', $_POST['pf']);
            $pf = str_replace(',', '.', $pf);

            $quinquenio = str_replace('.', '', $_POST['quinquenio']);
            $quinquenio = str_replace(',', '.', $quinquenio);

            $temp = [];
            $temp['nome']       = $_POST['nome'];
            $temp['empresa']    = $_POST['empresa'];
            $temp['salario']    = $salario;
            $temp['pf']         = $pf;
            $temp['quinquenio'] = $quinquenio;
            $temp['ativo']      = $_POST['ativo'];

            if(empty($id)) {
                $temp['data_inclusao'] = date('Y-m-d');

                $tipo_sql = 'INSERT';
                $where = '';
            } else {
                $tipo_sql = 'UPDATE';
                $where = "id_funcionarios = $id";
            }

            $sql = montaSQL($temp, 'funcionarios', $tipo_sql, $where);
            query($sql);

            $msgm = "Operação realizada com sucesso";
            $tipo = '';
        } else {
            $msgm = "Erro ao efetuar o registro";
            $tipo = 'erro';
        }

        redireciona(getLink() . "avisos&mensagem=$msgm&tipo=$tipo");
    }
}