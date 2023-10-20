<?php

/*
 * Data Criacao: 11/09/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para gerar relatório de faturas pagas
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class faturas_pagos {
    var $funcoes_publicas = array(
        'index'             => true,
        'schedule'          => true,
    );

    // Classe relatorio01
    private $_relatorio;

    // Casse formFiltro01
    private $_filtro;

    // Data do relatório
    private $_data;

    function __construct() {
        conectaCONSULT();

        $param = [];
        $param['programa'] = 'data';
        $param['link'] = getLink().'index';
        $this->_relatorio = new relatorio01($param);
    }

    public function index() {
        $ret = '';

        $filtro = $this->_relatorio->getFiltro();

        $data = $filtro['data'] ?? date('Y-m-d');

        $this->montaColunas();
        $dados = $this->getDados($data);
        $this->_relatorio->setDados($dados);

        $data = str_replace('-', '', $this->_data);
        $data = Datas::dataS2D($data);

        $this->_relatorio->setTitulo("Faturas Pagas - $data");

        $ret .= $this->_relatorio;
        return $ret;
    }

    public function schedule() {
        $this->montaColunas();
        $dados = $this->getDados();
        $this->_relatorio->setDados($dados);

        $data = str_replace('-', '', $this->_data);
        $data = Datas::dataS2D($data);

        $this->_relatorio->enviaEmail('procedimentos.julia@marpa.com.br; procedimentos.gabriela@marpa.com.br; procedimentos.mayara@marpa.com.br;', "Relatório de tipos de procedimento - $data");
    }

    private function montaColunas() {
        $this->_relatorio->addColuna(array('campo' => 'nome', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' =>  400, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'ca', 'etiqueta' => 'CA', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'procedimento', 'etiqueta' => 'Procedimento', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
    }

    private function getDados($data = '') {
        $ret = [];

        if(empty($data)) {
            $diasemana_numero = date('w', strtotime(date('Y-m-d')));
            
            // Caso seja segunda, pega data da última sexta
            $voltar = ($diasemana_numero == 1) ? 3 : 1;
            $dia = date('d') - $voltar;
            $mes = date('m');
            $ano = date('Y');
            $data = mktime(0,0,0,$mes,$dia,$ano);
            $data = date('Y-m-d', $data);
        }
        $this->_data = $data;

        $sql = "SELECT mc.empresa, mf.num_ca, proced.nome AS procedimento, mp.vlpago
                FROM marpafinpc AS mp
                LEFT JOIN marpafin AS mf USING(numlan, tipolan)
                LEFT JOIN marpacliente AS mc USING(sigla)
                LEFT JOIN marpatipoprocedimento AS proced USING(marpatipoprocedimento_id)
                WHERE mp.dtpag = '$data' AND mp.seq = 1 AND
	                mf.marpatipoprocedimento_id IN (1, 2, 3, 5, 8, 9, 13, 18, 44, 50, 53, 59, 60, 61, 62, 63, 67, 68, 70, 71, 113,
                                                    116, 122, 137, 145, 165, 166, 167, 168, 173, 174, 176, 179, 180, 181, 183, 184,
                                                    185, 186, 190, 192, 193, 194, 195, 196, 199, 201, 204, 205, 207, 208, 209, 210,
											        218, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234,
                                                    235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245, 246, 247, 270, 370)";
        $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['nome'] = mb_convert_encoding($row['empresa'], 'UTF-8', 'ASCII');
                $temp['ca'] = $row['num_ca'];
                $temp['valor_pago'] = $row['vlpago'];
                $temp['procedimento'] = mb_convert_encoding($row['procedimento'], 'UTF-8', 'ASCII');

                $ret[] = $temp;
            }
        }

        return $ret;
    }
}