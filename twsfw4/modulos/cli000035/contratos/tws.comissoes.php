<?php

/*
 * Data Criacao: 26/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para criar e editar comissões
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class comissoes {
    var $funcoes_publicas = array(
        'index'                 => true,
        'avisos'                => true,
        'incluir'               => true,
        'salvar'                => true,
    );

    // Classe tabela01
    private $_tabela;

    // Classe form01
    private $_form;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['titulo'] = "Contratos Pagos";
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

        $param = array(
			'texto' => 'Incluir novos parâmetros',
			'onclick' => "setLocation('" . getLink() . "incluir')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
            'texto' => 'Editar',
            'cor' => 'success',
            'link' => getLink() . "incluir&id=",
            'coluna' => 'id',
        );
        $this->_tabela->addAcao($param);

        $ret .= $this->_tabela;

        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'a_partir_de', 'etiqueta' => 'A Partir de', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT id_contratos_comissoes, a_partir_de, ativo FROM contratos_comissoes ORDER BY a_partir_de";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['id_contratos_comissoes'];
                $temp['a_partir_de'] = Datas::dataMS2D($row['a_partir_de']);
                $temp['ativo'] = ($row['ativo'] == 'S') ? 'Sim' : 'Não';

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';

        $comissoes = new contratos_comissoes();
        $ret = $comissoes->incluir();

        return $ret;
    }

    public function salvar() {
        $ret = '';

        $comissoes = new contratos_comissoes();
        $ret = $comissoes->salvar();

        return $ret;
    }
}