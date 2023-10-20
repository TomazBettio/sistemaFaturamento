<?php

/*
 * Data Criacao: 22/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Relatório dos contratos pagos
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class contratos_pagos {
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

        // $de = $filtro['DATAINI'] ?? '';
        // $ate = $filtro['DATAFIM'] ?? '';

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
		$param['titulo'] = "Contratos Pagos $dataini - $datafim";
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
        $this->_tabela->addColuna(array('campo' => 'consultor', 'etiqueta' => 'Consultor', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'contrato', 'etiqueta' => 'Contrato', 'tipo' => 'T', 'width' =>  150, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'pago', 'etiqueta' => 'Pago', 'tipo' => 'V', 'width' =>  100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'retificacao', 'etiqueta' => 'Retificação', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'pagamento', 'etiqueta' => 'Pagamento', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'vencimento', 'etiqueta' => 'Vencimento', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'porcentagem_consultor', 'etiqueta' => 'Porcentagem Consultor', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'valor_consultor', 'etiqueta' => 'Valor Consultor', 'tipo' => 'V', 'width' =>  100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'comissao_leandro', 'etiqueta' => 'Comissão Leandro', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'comissao_luciano', 'etiqueta' => 'Comissão Luciano', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'porcentagem_parceiro', 'etiqueta' => 'Percentual Parceiro', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'valor_parceiro', 'etiqueta' => 'Valor Parceiro', 'tipo' => 'V', 'width' =>  100, 'posicao' => 'E'));
    }

    private function getDados($de, $ate) {
        $ret = [];

        $sql = "SELECT * FROM contratos_comissoes WHERE ativo = 'S' ORDER BY a_partir_de";
        $comissoes_sql = query($sql);
// print_r($comissoes_sql);
        $comissoes = [0, 5, 8, 10, 15];

        // $sql = "SELECT crp.CXPVALORPAGO, crp.CXPDATAVENCIMENTO,
        //             u.USUNOME AS NOMERESPONSAVEL, cli.CLIRAZAOSOCIAL,
        //             f.FAVCOMISSAO, c.CTRDATAAINICIO, c.CTRVENDEDOR,
        //             p.TABFONTEOPORTUNIDADE, vr.CTRVALORARECEBER, ue.ESTCODIGO,
        //             crp.CTRCODIGO
        //         FROM CONTASARECEBERXPARCELAS AS crp
        //             LEFT JOIN CONTASARECEBER cp ON cp.CTRCODIGO = crp.CTRCODIGO
        //             LEFT JOIN CONTRATOS AS c USING(CTRCODIGO)
        //             LEFT JOIN CLIENTES cli USING(CLICODIGO)
        //             LEFT JOIN USUARIOS u ON u.USUCODIGO = c.CTRVENDEDOR
        //             LEFT JOIN USUARIOSXENDERECOS AS ue USING(USUCODIGO)
        //             LEFT JOIN FAVORECIDOS f USING(FAVCODIGO)
        //             LEFT JOIN PROSPECCAO AS p ON p.CLICODIGO = cli.CLICODIGO AND p.CTRCODIGO = c.CTRCODIGO
        //             LEFT JOIN CONTASARECEBER AS vr ON vr.CTRCODIGO = c.CTRCODIGO
        //         WHERE cp.CTRSTATUS = 'S' AND crp.CXPDATAPAGAMENTO IS NOT NULL
        //             AND crp.CXPDATAVENCIMENTO >= '$de' AND crp.CXPDATAVENCIMENTO <= '$ate'
        //         ORDER BY crp.CXPDATAVENCIMENTO DESC";

        $sql = "SELECT u.USUNOME AS CONSULTOR, cli.CLIRAZAOSOCIAL, CXP.CXPVALORPAGO, CXP.CXPDATAVENCIMENTO, ue.ESTCODIGO,
                    f.FAVCOMISSAO, p.TABFONTEOPORTUNIDADE, cp.CTRVALORARECEBER, c.CTRDATA_INC, c.CTRVENDEDOR, c.CTRNROCONTRATO,
                    u.USUPORCENTC, CXP.CXPDATAPAGAMENTO, c.CTRDESCRICAO, c.CTRCREDITOAPURADO, c.FREPORCENTAGEMPARCELAS,
                    c.CTR_HONORARIOS, tipo.CXTCODIGO AS RETIFICACAO
                FROM  CONTASARECEBERXPARCELAS CXP
                    LEFT JOIN CONTASARECEBER cp ON cp.CTRCODIGO = CXP.CTRCODIGO
                    LEFT JOIN CLIENTES cli ON cli.CLICODIGO = cp.CLICODIGO
                    LEFT JOIN CONTRATOS AS c ON c.CTRCODIGO = cp.CTRCONTRATO
                    LEFT JOIN USUARIOS u ON c.CTRVENDEDOR = u.USUCODIGO
                    LEFT JOIN USUARIOSXENDERECOS AS ue USING(USUCODIGO)
                    LEFT JOIN FAVORECIDOS f USING(FAVCODIGO)
                    LEFT JOIN PROSPECCAO AS p ON p.CLICODIGO = cli.CLICODIGO AND p.CTRCODIGO = c.CTRCODIGO
                    LEFT JOIN CONTRATOSXTIPOS AS tipo ON c.CTRCODIGO = tipo.CTRCODIGO AND tipo.PRICODIGO = 36
                WHERE
                    c.CTRSTATUS = 'S' AND CXP.CXPDATAPAGAMENTO IS NOT NULL AND CXP.CXPVALORPAGO != 0
                    AND CXP.CXPDATAPAGAMENTO >= '$de' AND CXP.CXPDATAPAGAMENTO <= '$ate'
                ORDER BY CXP.CXPDATAPAGAMENTO DESC";
        $rows = queryERP($sql);
// print_r($rows);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                // echo "------------------------------------<br>\n";
                $data_inc = substr($row['CTRDATA_INC'], 0, 10);

                if(substr(trim($row['CTRNROCONTRATO']), 0, 1) == 'C') {
                    $ctr = explode('/', trim($row['CTRNROCONTRATO']));
                    if(strlen($ctr[0]) == 3) { // É um conectado
                        $comissao = 0;
                    } else { // É um conect
                        $comissao = (!empty($row['USUPORCENTC'])) ? $row['USUPORCENTC'] : 50;
                    }
                }
                else if($data_inc <= '2023-05-31') {
                    if($row['TABFONTEOPORTUNIDADE'] == 3582) { // Contrato Itaú
                        $comissao = 5;
                    }
                    else if($row['CTRVENDEDOR'] == 112 || $row['CTRVENDEDOR'] == 99 || $row['CTRVENDEDOR'] == 33) {
                        $comissao = 20;
                    }
                    else {
                        $meta = $this->calculaMeta($row['CTRVENDEDOR'], $data_inc);
                        $comissao = $comissoes[$meta];
                    }
                } else {
                    $comissao = '';
                    foreach($comissoes_sql as $k => $com) {
                        if(empty($comissao)) {
                            if(isset($comissoes_sql[$k+1])) {
                                $gerar = ($data_inc >= $com['a_partir_de'] && $data_inc < $comissoes_sql[$k+1]['a_partir_de']) ? true : false;
                            } else {
                                $gerar = ($data_inc >= $com['a_partir_de']) ? true : false;
                            }

                            if($gerar) {
                                if($row['CTRVALORARECEBER'] < $com['valor1']) {
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
                    // if($row['CTRVALORARECEBER'] < 100000) {
                    //     $comissao = $comissoes[1];
                    // } else if($row['CTRVALORARECEBER'] < 200000) {
                    //     $comissao = $comissoes[2];
                    // } else if($row['CTRVALORARECEBER'] < 300000) {
                    //     $comissao = $comissoes[3];
                    // } else if($row['CTRVALORARECEBER'] >= 300000) {
                    //     $comissao = $comissoes[4];
                    // }

                    // echo $data_inc . "<br>\n";
                    // echo "$comissao --- $comissao2<br>\n";
                    // $comissao = $comissao2;
                }

                // a partir do dia 2019-08-23 luciano começa a representar RS, a partir de 2022-06-09 ele representa todos
                if($data_inc >= '2022-06-09' || ($data_inc >= '2019-08-23' && $row['ESTCODIGO'] == 43)) { // 43 = RS
                    $comissao_leadro = 1;
                    $comissao_luciano = 2;
                } else {
                    $comissao_leadro = 3;
                    $comissao_luciano = 0;
                }
// FREPORCENTAGEMPARCELAS
                $honorarios = empty($row['CTRCREDITOAPURADO']) ? $row['FREPORCENTAGEMPARCELAS'] : $row['CTRCREDITOAPURADO'];
                $honorarios = empty($honorarios) ? $row['CTR_HONORARIOS'] : $honorarios;
                $honorarios = (empty($honorarios) || $honorarios == 0) ? 100 : $honorarios;

                if(!empty($row['RETIFICACAO']) || strpos($row['CTRDESCRICAO'], 'retificação') !== false) {
                    $apuracao = $honorarios - 5;
                    $valor = ($row['CXPVALORPAGO'] * $apuracao) / $honorarios;

                    $retificacao = 'Sim';
                } else {
                    $apuracao = $honorarios;
                    $valor = $row['CXPVALORPAGO'];

                    $retificacao = 'Não';
                }

                // if(strpos($row['CTRDESCRICAO'], 'retificação') !== false) {
                //     // $valor_retificação = ($row['CXPVALORPAGO'] * 5) / $row['CTRCREDITOAPURADO'];
                //     // $valor = $row['CXPVALORPAGO'] - $valor_retificação;
                //     $apuracao = $honorarios - 5;
                //     $valor = ($row['CXPVALORPAGO'] * $apuracao) / $honorarios;

                //     $retificacao = 'Sim';
                // } else {
                //     $apuracao = $honorarios;
                //     $valor = $row['CXPVALORPAGO'];

                //     $retificacao = 'Não';
                // }

                $comissao_parceiro = (empty($row['FAVCOMISSAO'])) ? 0 : $row['FAVCOMISSAO'];
                $valor_parceiro = ($valor * $comissao_parceiro) / $apuracao;

                $valor -= $valor_parceiro;

                $valor_consultor = ($valor * $comissao) / $apuracao;

                $temp = [];
                $temp['consultor']              = $row['CONSULTOR'];
                $temp['cliente']                = $row['CLIRAZAOSOCIAL'];
                $temp['contrato']               = $row['CTRNROCONTRATO'];
                $temp['pago']                   = $row['CXPVALORPAGO'];
                $temp['retificacao']            = $retificacao;
                $temp['pagamento']              = Datas::dataMS2D($row['CXPDATAPAGAMENTO']);
                $temp['vencimento']             = Datas::dataMS2D($row['CXPDATAVENCIMENTO']);
                $temp['porcentagem_consultor']  = $comissao . '%';
                $temp['valor_consultor']        = $valor_consultor;
                $temp['comissao_leandro']       = $comissao_leadro . '%';
                $temp['comissao_luciano']       = $comissao_luciano . '%';
                $temp['porcentagem_parceiro']   = $comissao_parceiro . '%';
                $temp['valor_parceiro']         = $valor_parceiro;

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    private function calculaMeta($consultor, $referencia) {
        $ret = 0;

        $ano_mes = substr($referencia, 0, 7);
        $de = $ano_mes . '-01';
        $ate = date('Y-m-t', strtotime($ano_mes . '-01')); // último dia do mês referente

        $sql = "SELECT COUNT(*) FROM CONTRATOS
                WHERE CTRSTATUS = 'S' AND CTRVENDEDOR = $consultor
                    AND CTRDATA_INC >= '$de' AND CTRDATA_INC <= '$ate'";
        $row = queryERP($sql);

        if(is_array($row) && count($row) > 0) {
            $ret = ($row[0][0] > 4) ? 4 : $row[0][0];
        }

        return $ret;
    }
}