<?php
/*
 * Data Criacao: 14/04/2023
 * Autor: Verticais - Rafael
 *
 * Descricao: Relatório de enquadramento fiscal que será enviado por e-mail (eventualmente usado)
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class relatorio_enquadramento_fiscal {
    var $funcoes_publicas = array(
        'index'             => true,
    );

    function __construct() {
        conectaCONSULT();
    }

    public function index() {
        $html = '';

        $sql = "SELECT DISTINCT mc.codigovendedor, mc.sigla, mc.empresa
                FROM marpacliente AS mc
                    LEFT JOIN marpaprocesso AS mp ON mc.sigla = mp.sigla
                    LEFT JOIN marpapatente AS patente ON mc.sigla = patente.siglacliente
                WHERE mc.tipo_cliente = 'C' AND mc.status_cliente = 'A' AND TRIM(mc.fj) = 'J'
                    AND ((TRIM(mp.paismarca) = 'BR' AND (mp.codigomotcancel = 0 OR mp.codigomotcancel IS NULL) AND mp.codigotipoprocesso = 0)
                    OR (TRIM(patente.deppais) = 'BR' AND (patente.codigomotcancel = 0 OR patente.codigomotcancel IS NULL) AND patente.codigotipoprocesso = 5))
                    AND (SELECT COUNT(*) FROM marpaandamento AS ma WHERE (ma.pasta = mp.pasta OR ma.pasta = patente.pasta) AND ma.codstatusandamento = 3) = 0
                    AND (SELECT COUNT(*) FROM marpaandamentopat AS map WHERE map.pasta = patente.pasta AND map.codstatusandamento = 3) = 0";
        $rows = query2($sql);

        $html = "<h2><strong>ANÁLISE E COMPROVAÇÃO DE ENQUADRAMENTO FISCAL:</strong></h2>";

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
                                    <th colspan='2' style='text-align: center;'>".$nome_consultor[$consultor[0]['codigovendedor']]."</th>
                                </tr>
                                <tr>
                                    <th>Sigla</th>
                                    <th>Nome do Cliente</th>
                                </tr>
                            </thead>
                            <tbody>";
                if(is_array($consultor) && count($consultor) > 0) {
                    foreach($consultor as $row) {
                        $html .=    "<tr>
                                        <td>".$row['sigla']."</td>
                                        <td>".utf8_encode($row['empresa'])."</td>
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
        $param['assunto'] = 'Análise e Comprovação de Enquadramento Fiscal';
        // enviaEmail($param);
            return $style . $html;
        return '<p>E-mail ('.$param['assunto'].') enviado para '.$param['destinatario'].'</p>';
    }
}