<?php

/*
 * Data Criacao: 13/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para cadastro de pagamentos folha ou vales(adiantamento)
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class pagamentos {
    var $funcoes_publicas = array(
        'avisos'            => true,
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
        'enviar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    // Classe relatorio01
    private $_relatorio;

    // Classe formFiltro01
    private $_filtro;

    function __construct() {
        // $param = [];
		// $param['programa'] = get_class($this);
		// $this->_relatorio = new relatorio01($param);

		// $param= [];
		// $param['filtro']= false;
		// $param['info']= false;
		// $this-> _relatorio->setParamTabela($param);

        $param = [];
        $param['link'] = getLink().'index';
        $this->_filtro = new formFiltro01('aniversario_relatorio', $param);
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
        $filtrar = $_GET['filtrar'] ?? false;

        $filtro = $this->_filtro->getFiltro();

        $de = $filtro['DATAINI'] ?? '';
        $ate = $filtro['DATAFIM'] ?? '';
        
		$dtDe 	= (!empty($de)) ? substr($de, 0, 4).'-'.substr($de, 4, 2) : '';
		$dtAte 	= (!empty($ate)) ? substr($ate, 0, 4).'-'.substr($ate, 4, 2) : '';
        
        $dados = $this->getDados($dtDe, $dtAte);

        if($filtrar || empty($dtDe)) {
            $ret .= $this->_filtro;
        }

        // ========= CRIA TABELA DOS PAGAMENTOS MENSAIS =========
        $param = [];
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['titulo'] = "Mensal";
		$this->_tabela = new tabela01($param);

        $this->montaColunas();
        $this->_tabela->setDados($dados['mensal']);

        $param = array(
			'texto' => 'Enviar por E-mail',
			'onclick' => "setLocation('" . getLink() . "enviar&tipo=mensal')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $ret .= $this->_tabela;

        // ========= CRIA TABELA DOS ADIANTAMENTOS =========
        $param = [];
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['titulo'] = 'Adiantamentos';
		$this->_tabela = new tabela01($param);

        $this->montaColunas(false);
        $this->_tabela->setDados($dados['adiantamento']);

        $param = array(
			'texto' => 'Enviar por E-mail',
			'onclick' => "setLocation('" . getLink() . "enviar&tipo=adiantamento')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $ret .= $this->_tabela;

        // =========== ADICIONA AS DUAS TABELAS EM UM CARD ==========
        $de = substr($de, 4, 2) . '/' . substr($de, 0, 4);
        $ate = substr($ate, 4, 2) . '/' . substr($ate, 0, 4);

        $param = [];
        $p = array();
		$p['onclick'] = "setLocation('" . getLink() . "incluir')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'success';
		$p['texto'] = 'Novos Pagamentos';
		$param['botoesTitulo'][] = $p;

        $p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index&filtrar=1')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'success';
		$p['texto'] = 'Filtrar';
		$param['botoesTitulo'][] = $p;

        $param['titulo'] = "Pagamentos $de - $ate";
        $param['conteudo'] = $ret;
        $param['cor'] = 'success';
        $ret = addCard($param);

        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'funcionario', 'etiqueta' => 'Funcionário', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'online', 'etiqueta' => 'Online', 'tipo' => 'V', 'width' =>  150, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'pf', 'etiqueta' => 'PF', 'tipo' => 'V', 'width' =>  150, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'mes_referente', 'etiqueta' => 'Mês Referente', 'tipo' => 'T', 'width' =>  150, 'posicao' => 'E'));
    }

    private function getDados($de, $ate) {
        $ret['mensal'] = [];
        $ret['adiantamento'] = [];

        $ate = (empty($ate)) ? date('Y-m') : $ate;

        $sql = "SELECT pagamentos.*, funcionarios.nome AS funcionario
                FROM rh_pagamentos_funcionarios AS pagamentos
                    LEFT JOIN funcionarios USING(id_funcionarios)
                WHERE mes_referente >= '$de' AND mes_referente <= '$ate'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $mes = explode('-', $row['mes_referente']);
                $mes = $mes[1].'/'.$mes[0];

                $temp = [];
                $temp['funcionario']    = $row['funcionario'];
                $temp['online']         = $row['online'];
                $temp['pf']             = $row['pf'];
                $temp['mes_referente']  = $mes;

                if($row['tipo'] == 'mensal') {
                    $ret['mensal'][] = $temp;
                } else {
                    $ret['adiantamento'][] = $temp;
                }
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';

        $pagamentos = new rh_pagamentos();
        $ret = $pagamentos->incluir();

        return $ret;
    }

    public function salvar() {

        $pagamentos = new rh_pagamentos();
        $pagamentos->salvar();

    }

    public function enviar() {
        $tipo = $_GET['tipo'];

        $filtro = $this->_filtro->getFiltro();

        if(isset($filtro['DATAINI']) && !empty($filtro['DATAINI'])) {
            $pagamentos = new rh_pagamentos();
            $pagamentos->enviar($tipo, $filtro);
        }

    }
}