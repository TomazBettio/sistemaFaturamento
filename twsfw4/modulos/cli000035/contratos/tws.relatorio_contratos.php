<?php

/*
 * Data Criacao: 15/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Relatório dos contratos cadastrados
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class relatorio_contratos {
    var $funcoes_publicas = array(
        'index'                 => true,
    );

    // Classe tabela01
    private $_tabela;

    // Classe formFiltro01
    private $_filtro;

    function __construct() {
        conectaERP();

        $param = [];
        $param['link'] = getLink().'index';
        $this->_filtro = new formFiltro01('contratos_connect', $param);
    }

    public function index() {
        $ret = '';

        // ================ CRIA O FILTRO ======================
        $filtrar = $_GET['filtrar'] ?? false;

        $filtro = $this->_filtro->getFiltro();

        $de = (empty($filtro['DATAINI'])) ? date('Ymd') : $filtro['DATAINI'];
        $ate = (empty($filtro['DATAFIM'])) ? date('Ymd') : $filtro['DATAFIM'];

        if($filtrar || empty($de)) {
            $ret .= $this->_filtro;
        }

        // ================ CRIA A TABELA ======================
        $dataini = Datas::dataS2D($de);
        $datafim = Datas::dataS2D($ate);

        $param = [];
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['titulo'] = "Contratos Lançados $dataini - $datafim";
		$this->_tabela = new tabela01($param);

        $this->montaColunas();
        $dados = $this->getDados($de, $ate);
        $this->_tabela->setDados($dados);

        $param = array(
			'texto' => 'Filtrar Por Data',
			'onclick' => "setLocation('" . getLink() . "index&filtrar=1')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $ret .= $this->_tabela;

        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'consultor', 'etiqueta' => 'Consultor', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'contrato', 'etiqueta' => 'Contrato', 'tipo' => 'T', 'width' =>  150, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'parceiro', 'etiqueta' => 'Parceiro', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'valor_contrato', 'etiqueta' => 'Valor', 'tipo' => 'V', 'width' =>  150, 'posicao' => 'D'));
        $this->_tabela->addColuna(array('campo' => 'comissao_consultor', 'etiqueta' => 'Comissão', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'natureza', 'etiqueta' => 'Natureza do Contrato', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cnpj', 'etiqueta' => 'CNPJ/CPF', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'C'));
    }

    private function getDados($de, $ate) {
        $ret = [];

        $sql = "SELECT * FROM contratos_comissoes WHERE ativo = 'S' ORDER BY a_partir_de";
        $comissoes_sql = query($sql);

        $comissoes = [0, 5, 8, 10, 15];

        $sql = "SELECT u.USUNOME AS CONSULTOR, c.CTRVENDEDOR, c.CTRDATA_INC,
                    c.CTRNROCONTRATO, f.FAVNOMEFANTASIA, cli.CLIRAZAOSOCIAL,
                    c.CTRVALORCOBERTURA, cr.CTRVALORARECEBER, p.TABFONTEOPORTUNIDADE,
                    u.USUPORCENTC,
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
                    LEFT JOIN APURACAOMONOFASICO AS ctr_fiscal USING(CTRCODIGO)
                    LEFT JOIN CLIENTES cli ON cli.CLICODIGO = c.CLICODIGO
                    LEFT JOIN USUARIOS u ON u.USUCODIGO = c.CTRVENDEDOR
                    LEFT JOIN FAVORECIDOS f ON f.FAVCODIGO = c.FAVCODIGO
                    LEFT JOIN PROSPECCAO AS p ON p.CLICODIGO = cli.CLICODIGO AND p.CTRCODIGO = c.CTRCODIGO
                    LEFT JOIN CONTASARECEBER AS cr ON c.CTRCODIGO = cr.CTRCONTRATO
                WHERE
                    c.CTRSTATUS = 'S' AND
                    c.CTRDATA_INC >= '$de' AND c.CTRDATA_INC <= '$ate'
                ORDER BY c.CTRDATA_INC DESC"; // PROSPECCAO -> TABFONTEOPORTUNIDADE

        // $sql = "SELECT u.USUNOME AS CONSULTOR, ue.ESTCODIGO, cp.CTRDATA_INC, c.CTRNROCONTRATO, cp.CTRVALORARECEBER,
        //             cli.CLIRAZAOSOCIAL, f.FAVNOMEFANTASIA, p.TABFONTEOPORTUNIDADE, cp.CTRCONSULTOR,
        //             cp.CTRCONTRATO,
        //             (
        //                 SELECT
        //                     GROUP_CONCAT(i.PRINOME SEPARATOR ',')
        //                 FROM
        //                     CONTRATOSXTIPOS CXT
        //                 LEFT JOIN
        //                     PROCESSOSINTERNO i ON i.PRICODIGO = CXT.PRICODIGO
        //                 WHERE
        //                     CXT.CXTSTATUS = 'S' AND CXT.CTRCODIGO = cp.CTRCODIGO
        //             ) AS TIPOCONTRATO,
        //             CONCAT(
        //                 (
        //                     CASE WHEN cli.CLITIPOCLIENTE = 'J' THEN cli.CLICNPJ ELSE cli.CLICPF
        //                     END
        //                 )
        //             ) AS CNPJCPF
        //         FROM  CONTASARECEBER cp
        //             LEFT JOIN CLIENTES cli ON cli.CLICODIGO = cp.CLICODIGO
        //             LEFT JOIN USUARIOS u ON cp.CTRCONSULTOR = u.USUCODIGO
        //             LEFT JOIN USUARIOSXENDERECOS AS ue USING(USUCODIGO)
        //             LEFT JOIN CONTRATOS AS c ON c.CTRCODIGO = cp.CTRCODIGO
        //             LEFT JOIN FAVORECIDOS f USING(FAVCODIGO)
        //             LEFT JOIN PROSPECCAO AS p ON p.CLICODIGO = cli.CLICODIGO AND p.CTRCODIGO = cp.CTRCODIGO
        //         WHERE cp.CTRSTATUS = 'S' AND
        //             cp.CTRDATA_INC >= '$de' AND cp.CTRDATA_INC <= '$ate'
        //         ORDER BY cp.CTRDATA_INC DESC";
        $rows = queryERP($sql);
// print_r($rows);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $data_inc = substr($row['CTRDATA_INC'], 0, 10);

                // VERIFICA SE É CONTRATO CONECT
                if(substr(trim($row['CTRNROCONTRATO']), 0, 1) == 'C') {
                    $ctr = explode('/', trim($row['CTRNROCONTRATO']));
                    if(strlen($ctr[0]) == 3) { // É um conectado
                        $comissao = 0;
                    } else { // É um conect
                        $comissao = (!empty($row['USUPORCENTC'])) ? $row['USUPORCENTC'] : 50;
                    }
                }
                else if($data_inc <= '2023-05-31') { // REGRA ANTIGA DE COMISSÕES
                    if($row['TABFONTEOPORTUNIDADE'] == 3582) { // Cliente Itaú
                        $comissao = 5;
                    }
                    else if($row['CTRVENDEDOR'] == 112 || $row['CTRVENDEDOR'] == 99 || $row['CTRVENDEDOR'] == 33) {
                        $comissao = 20;
                    }
                    else {
                        $meta = $this->calculaMeta($row['CTRVENDEDOR'], $data_inc);
                        $comissao = $comissoes[$meta];
                    }
                }
                else { // CASO NÃO SEJA CONECT E NEM NA DATA ANTIGA, VERIFICA AS NOVAS REGRAS
                    $comissao = '';
                    foreach($comissoes_sql as $k => $com) {
                        if(empty($comissao)) {
                            if(isset($comissoes_sql[$k+1])) {
                                $gerar = ($data_inc >= $com['a_partir_de'] && $data_inc < $comissoes_sql[$k+1]['a_partir_de']) ? true : false;
                            } else {
                                $gerar = ($data_inc >= $com['a_partir_de']) ? true : false;
                            }

                            if($gerar) {
                                if($row['CTRVALORARECEBER'] == 0 || empty($row['CTRVALORARECEBER'])) {
                                    $comissao = 0;
                                }
                                else if($row['CTRVALORARECEBER'] < $com['valor1']) {
                                    $comissao = $com['porcentagem1'];
                                }
                                else if($row['CTRVALORARECEBER'] < $com['valor2']) {
                                    $comissao = $com['porcentagem2'];
                                }
                                else if($row['CTRVALORARECEBER'] < $com['valor3']) {
                                    $comissao = $com['porcentagem3'];
                                }
                                else if($row['CTRVALORARECEBER'] >= $com['valor3']) {
                                    $comissao = $com['porcentagem4'];
                                }
                            }
                            // if(isset($comissoes_sql[$k+1])) {
                            //     if($data_inc >= $com['a_partir_de'] && $data_inc < $comissoes_sql[$k+1]['a_partir_de']) {
                            //         // echo "valor {$row['CTRVALORARECEBER']} <br>\n";
                            //         // print_r($com);

                            //         if($row['CTRVALORARECEBER'] < $com['valor1']) {
                            //             $comissao = $com['porcentagem1'];
                            //         }
                            //         else if($row['CTRVALORARECEBER'] < $com['valor2']) {
                            //             $comissao = $com['porcentagem2'];
                            //         }
                            //         else if($row['CTRVALORARECEBER'] < $com['valor3']) {
                            //             $comissao = $com['porcentagem3'];
                            //         }
                            //         else if($row['CTRVALORARECEBER'] >= $com['valor3']) {
                            //             $comissao = $com['porcentagem4'];
                            //         }
                            //     }
                            // } else if($data_inc >= $com['a_partir_de']) {
                            //     if($row['CTRVALORARECEBER'] < $com['valor1']) {
                            //         $comissao = $com['porcentagem1'];
                            //     }
                            //     else if($row['CTRVALORARECEBER'] < $com['valor2']) {
                            //         $comissao = $com['porcentagem2'];
                            //     }
                            //     else if($row['CTRVALORARECEBER'] < $com['valor3']) {
                            //         $comissao = $com['porcentagem3'];
                            //     }
                            //     else if($row['CTRVALORARECEBER'] >= $com['valor3']) {
                            //         $comissao = $com['porcentagem4'];
                            //     }
                            // }
                        }
                    }

                    // if($row['CTRVALORARECEBER'] == 0 || empty($row['CTRVALORARECEBER'])) {
                    //     $comissao = 0;
                    // }
                    // else if($row['CTRVALORARECEBER'] < 100000) {
                    //     $comissao = $comissoes[1];
                    // }
                    // else if($row['CTRVALORARECEBER'] < 200000) {
                    //     $comissao = $comissoes[2];
                    // }
                    // else if($row['CTRVALORARECEBER'] < 300000) {
                    //     $comissao = $comissoes[3];
                    // }
                    // else if($row['CTRVALORARECEBER'] >= 300000) {
                    //     $comissao = $comissoes[4];
                    // }
                }

                $temp = [];
                $temp['consultor']          = $row['CONSULTOR'];
                $temp['data']               = Datas::dataMS2D($row['CTRDATA_INC']);
                $temp['contrato']           = $row['CTRNROCONTRATO'];
                $temp['parceiro']           = (!empty($row['FAVNOMEFANTASIA'])) ? 'Sim' : 'Não';
                $temp['valor_contrato']     = $row['CTRVALORARECEBER'];
                $temp['comissao_consultor'] = $comissao . '%';
                $temp['natureza']           = $row['TIPOCONTRATO'];
                $temp['cliente']            = $row['CLIRAZAOSOCIAL'];
                $temp['cnpj']               = $row['CNPJCPF'];

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    private function calculaMeta($consultor, $referencia) {
        $ret = 0;

        $ano_mes = substr($referencia, 0, 7);
        $de = $ano_mes . '-01';
        $ate = date('Y-m-t', strtotime($ano_mes . '-01'));

        $sql = "SELECT COUNT(*) FROM CONTRATOS
                WHERE CTRVENDEDOR = $consultor AND CTRDATA_INC >= '$de' AND CTRDATA_INC <= '$ate'";
        $row = queryERP($sql);

        if(is_array($row) && count($row) > 0) {
            $ret = ($row[0][0] > 4) ? 4 : $row[0][0];
        }

        return $ret;
    }
}