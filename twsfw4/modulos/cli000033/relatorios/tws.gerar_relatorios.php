<?php
/*
 * Data Criacao: 14/09/2023
 * Autor: Verticais - Rafael
 *
 * Descricao: Para relatórios que serão usados uma única vez
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class gerar_relatorios {
    var $funcoes_publicas = array(
        'index'             => true,
    );

    // Classe relatorio01
    private $_relatorio;

    function __construct() {
        conectaCONSULT();
        conectaTRIB();

        $param = [];
        $param['titulo'] = 'Clientes ativos';
        $param['ordenacao'] = false;
        $this->_relatorio = new relatorio01($param);

        // addPortalJquery('"ordering": false,');
    }

    public function index() {
        $ret = '';

        $this->montaColunas();
        $dados = $this->getDados();
        $this->_relatorio->setDados($dados);

        $ret .= $this->_relatorio;
        // $this->_relatorio->enviaEmail('faturamento@marpa.com.br', "Relatório de Clientes ativos");
        return $ret;
    }

    private function montaColunas() {
        $this->_relatorio->addColuna(array('campo' => 'sigla', 'etiqueta' => 'Sigla', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'nome', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' =>  400, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'connect', 'etiqueta' => 'Connect', 'tipo' => 'T', 'width' =>  400, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT mc.sigla, mc.empresa, mb.nome
                FROM marpacliente AS mc
                INNER JOIN marpabancoagconnect AS mb USING(sigla)
                ORDER BY mc.empresa";
        $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['sigla'] = $row['sigla'];
                $temp['nome'] = mb_convert_encoding($row['empresa'], 'UTF-8', 'ASCII');
                $temp['connect'] = mb_convert_encoding($row['nome'], 'UTF-8', 'ASCII');

                $ret[] = $temp;
            }
        }

        return $ret;
    }
}