<?php

/*
 * Data Criacao: 30/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Relatório dos contratos que não constam porcetagem de honorários
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class sem_honorarios {
    var $funcoes_publicas = array(
        'index'                 => true,
    );

    // Classe relatorio01
    private $_relatorio;

    // Classe filtro01
    private $_filtro;

    function __construct() {
        conectaERP();

        $param = [];
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['titulo'] = "Contratos sem percentual de apuração";
        $param['programa'] = 'contratos_connect';
		$this->_relatorio = new relatorio01($param);

        // $param = [];
        // $param['link'] = getLink().'index';
        // $this->_filtro = new formFiltro01('contratos_connect', $param);
    }

    public function index() {
        $ret = '';

        $filtro = $this->_relatorio->getFiltro();

        $de = (empty($filtro['DATAINI'])) ? date('Ymd') : $filtro['DATAINI'];
        $ate = (empty($filtro['DATAFIM'])) ? date('Ymd') : $filtro['DATAFIM'];

        $this->montaColunas();
        $dados = $this->getDados($de, $ate);
        $this->_relatorio->setDados($dados);

        $ret .= $this->_relatorio;

        return $ret;
    }

    private function montaColunas() {
        $this->_relatorio->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'contrato', 'etiqueta' => 'Contrato', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'consultor', 'etiqueta' => 'Consultor', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'natureza', 'etiqueta' => 'Natureza', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cnpj', 'etiqueta' => 'CNPJ/CPF', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
    }

    private function getDados($de, $ate) {
        $ret = [];

        $sql = "SELECT c.CTRDATA_INC, c.CTRNROCONTRATO, u.USUNOME AS CONSULTOR, cli.CLIRAZAOSOCIAL,
                    CONCAT(
                        (
                            CASE WHEN cli.CLITIPOCLIENTE = 'J' THEN cli.CLICNPJ ELSE cli.CLICPF
                            END
                        )
                    ) AS CNPJCPF,
                    (
                        SELECT
                            GROUP_CONCAT(i.PRINOME SEPARATOR ',')
                        FROM
                            CONTRATOSXTIPOS CXT
                        LEFT JOIN
                            PROCESSOSINTERNO i ON i.PRICODIGO = CXT.PRICODIGO
                        WHERE
                            CXT.CXTSTATUS = 'S' AND CXT.CTRCODIGO = c.CTRCODIGO
                    ) AS TIPOCONTRATO
                FROM CONTRATOS AS c
                    LEFT JOIN USUARIOS u ON u.USUCODIGO = c.CTRVENDEDOR
                    LEFT JOIN CLIENTES cli ON cli.CLICODIGO = c.CLICODIGO
                WHERE CTRCREDITOAPURADO IS NULL AND FREPORCENTAGEMPARCELAS IS NULL
                    AND (CTR_HONORARIOS IS NULL OR CTR_HONORARIOS = 0)
                    AND c.CTRSTATUS = 'S' AND c.CTRDATA_INC >= '$de' AND c.CTRDATA_INC <= '$ate'
                ORDER BY c.CTRDATA_INC DESC";
        $rows = queryERP($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['data'] = str_replace('-', '', substr($row['CTRDATA_INC'], 0, 10));
                $temp['contrato'] = $row['CTRNROCONTRATO'];
                $temp['natureza'] = $row['TIPOCONTRATO'];
                $temp['consultor'] = $row['CONSULTOR'];
                $temp['cliente'] = $row['CLIRAZAOSOCIAL'];
                $temp['cnpj'] = $row['CNPJCPF'];

                $ret[] = $temp;
            }
        }

        return $ret;
    }
}