<?php
/*
 * Data Criacao: 14/04/2023
 * Autor: Verticais - Rafael
 *
 * Descricao: Relatório de Processos de Patentes de Terceiros Ativos que será enviado por e-mail (eventualmente usado)
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class relatorio_patentes_terceiros {
    var $funcoes_publicas = array(
        'index'             => true,
    );

    function __construct() {
        conectaCONSULT();
    }

    public function index() {
        $html = '';

        $sql = "SELECT mc.codigovendedor, mc.sigla, mc.empresa, mp.codigoprocesso
                FROM marpacliente AS mc
                LEFT JOIN marpapatente AS mp ON mc.sigla = mp.siglacliente
                LEFT JOIN marpagold AS mg ON mc.sigla = mg.sigla
                WHERE mc.tipo_cliente = 'C' AND mc.status_cliente = 'A'
                AND TRIM(mp.deppais) = 'BR' AND (mp.codigomotcancel = 0 OR mp.codigomotcancel IS NULL)
                AND mp.titular IS NOT NULL AND mp.titular != ''
                AND (SELECT COUNT(*) FROM marpaandamentopat AS ma WHERE mp.pasta = ma.pasta AND ma.codstatusandamento = 3) = 0
                AND (SELECT COUNT(*) FROM marpagold AS mg
                            LEFT JOIN marpatipolan AS mtl ON mg.tipolan = mtl.tipolan
                            WHERE mg.sigla = mc.sigla
                                AND (mtl.descrtipolan LIKE '%Bronze%'
                                    OR mtl.descrtipolan LIKE '%Prata%'
                                    OR mtl.descrtipolan LIKE '%Ouro%'
                                    OR mtl.descrtipolan LIKE '%Platina%'
                                    OR mtl.descrtipolan LIKE '%Diamante%'
                                )
                        ) = 0";
        $rows = query2($sql);

        $html = "<h2><strong>PROCESSOS DE PATENTES DE TERCEIROS ATIVOS:</strong></h2>";

        $consultores = [];
        $nome_consultor = [];
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($consultores[$row['codigovendedor']])) {
                    $consultores[$row['codigovendedor']] = [];
                }
                $consultores[$row['codigovendedor']][] = $row;

                if(!isset($nome_consultor[$row['codigovendedor']])) {
                    $sql = "SELECT vendedor FROM marpavendedor WHERE codigovendedor = ".$row['codigovendedor'];
                    $nome = query2($sql);
                    $nome_consultor[$row['codigovendedor']] = $nome[0]['vendedor'];
                }
            }

            foreach($consultores as $consultor) {
                $html .= "<table>
                            <thead>
                                <tr>
                                    <th colspan='3' style='text-align: center;'>".$nome_consultor[$consultor[0]['codigovendedor']]."</th>
                                </tr>
                                <tr>
                                    <th>Sigla</th>
                                    <th>Nome do Cliente</th>
                                    <th>Número do Processo</th>
                                </tr>
                            </thead>
                            <tbody>";
                if(is_array($consultor) && count($consultor) > 0) {
                    foreach($consultor as $row) {
                        $html .=    "<tr>
                                        <td>".$row['sigla']."</td>
                                        <td>".utf8_encode($row['empresa'])."</td>
                                        <td>".$row['codigoprocesso']."</td>
                                    </tr>";
                    }
                }
                $html .=    "</tbody>
                        </table>";
            }
        } else {
            $html .= "<p>Não foram encontrados registros</p>";
        }

        $style = "<style>
            table {
                width: 800px;
                border-collapse: collapse; /* CSS2 */
                color: black;
                margin-bottom: 50px;
            }
            td {
                border: 1px solid black;
            }
            th {
                border: 1px solid black;
                background: black;
                color: white;
            }
        </style>";

        $param = [];
        $param['destinatario'] = 'advogada.dolly@marpa.com.br';
        $param['mensagem'] = $style . $html;
        $param['assunto'] = 'Relatório de Processos de Patentes de Terceiros Ativos';
        // enviaEmail($param);
            return $style . $html;
        return '<p>E-mail ('.$param['assunto'].') enviado para '.$param['destinatario'].'</p>';
    }
}