<?php
/*
 * Data Criacao: 13/03/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Classe relatório
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

include_once($config['include'] . 'dompdf/autoload.inc.php');

use Dompdf\Frame\FrameTree;
use Dompdf\Options;
use Dompdf\Dompdf;
use Dompdf\Css\Stylesheet;

class monofasico_compliance
{

    //Nome do arquivo PDF a ser criado
    private $_arqPDF = '';

    private $_cnpj;

    private $_path;

    public function __construct($cnpj)
    {
        global $config;

        $this->_path = $config['pathUpdMonofasico'];
        $this->_arqPDF = $this->_path . 'arquivo.pdf';

        $this->_cnpj = $cnpj;
    }

    public function criaHTML()
    {
        // $analise = new monofasico_analise($this->_cnpj);
        // $dados = $analise->getDados();

        // print_r($dados);

        // SIMULAÇÃO DOS RETORNOS DOS DADOS
        $dados0140 = array(
            'cod_part' => '21',
            'nome_cliente' => 'PRADO DISTRIBUIDOR LOGISTICO LTDA',
            'cnpj' => '00323283000102',
        );
        
        $dadosC100 = array(
            'tipo_nf' => '0',
            'cod_part' => '924',
            'chv_nfe' => '43170109062881000140550010000000581600808090',
            'data_emissao' => '13012017',
            'total_bruto' => '130.0',
        );
        
        $dadosC170 = array(
            'num_item' => '1',
            'cod_item' => '7898994267027',
            'vlr_total' => '130.0',
            'vlr_desc' => '0.0',
            'cfop' => '1102',
            'cst' => '50',
            'aliq_pis' => '1.65',
            'aliq_cofins' => '7.6',
            'chv_nfe' => '43170109062881000140550010000000581600808090',
        );
        
        $dados0200 = array(
            'cod_item' => '100',
            'nome_produto' => 'LINGUICA CAL FINA STA CLARA',
            'cod_ncm' => '16010000',
        );

        // Formatando a data de emissao
        $mes_emissao = substr($dadosC100['data_emissao'], 2, 2);
        $ano_emissao = substr($dadosC100['data_emissao'], 4, 4);
        // echo $mes_emissao . "   " . $ano_emissao;

        // calculando o valor do PIS e COFINS
        $cRes_pis = number_format(($dadosC100['total_bruto'] * $dadosC170['aliq_pis']) / 100, 2);
        $cRes_cofins = number_format(($dadosC100['total_bruto'] * $dadosC170['aliq_cofins']) / 100, 2);
        
        $cnpj = substr($this->_cnpj, 0, 2) . '.' . substr($this->_cnpj, 2, 3) . '.' . substr($this->_cnpj, 5, 3) . '/' . substr($this->_cnpj, 8, 4) . '-' . substr($this->_cnpj, 12, 2);
        
        // Recebendo a data de inicio das atividades do cliente
        $sql = "SELECT CLIDATA_INICIO_ATIVIDADES FROM CLIENTES WHERE CLICNPJ = '$cnpj'";
        $data = queryERP($sql);
        
        // Buscando informações referente as atividades do cliente
        $sql = "SELECT * FROM mgt_monofasico WHERE cnpj = $this->_cnpj";
        $tipo_arquivo = queryMF($sql);

        // xml ou sped
        $tipo = $tipo_arquivo[0]['tipo'];

        // Data do primeiro arquivo a ser incluido
        $mes_ini = substr($tipo_arquivo[0]['data_inc'], 5, 2);
        $ano_ini = substr($tipo_arquivo[0]['data_inc'], 0, 4);

        // Data do último arquivo a ser incluido
        $mes_fim = substr($tipo_arquivo[count($tipo_arquivo) - 1]['data_inc'], 5, 2);
        $ano_fim = substr($tipo_arquivo[count($tipo_arquivo) - 1]['data_inc'], 0, 4);

        $html = '';

        $style = "<style>
            * {
                text-align: justify;
            }
            p {
                font-family: Arial;
            }
           .center {
                text-align: center;
           }
           .direita {
                text-align: right;
           }
           .corpo {
                width: 95%;
                margin: auto;
           }
           .inf {
                width: 70%;
                margin: 0 0 50px auto;
           }
           table{
                border: solid;
                width: 90%;
                margin: 30px auto;
            }
            td {
                border: solid;
                border-color: black;
                margin: auto;
            }
            .titulo{
                font-weight: bold;
                background-color: rgb(4, 0, 100);
                color: white;
            }
            .assinatura {
                margin-top: 50px;
            }

        </style>";

        $dia = date('d');
        $mes = date('m');
        $ano = date('o');

        $meses = array(
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro',
        );

        $html .= "<div class='corpo'><p class='direita'>Porto Alegre/RS, $dia de ".$meses[$mes]." de $ano</p>";
        $html .= "<p class='center'><strong> Compliance tributária | Contrato *--*</strong></p>";
        $html .= "<p><strong>".$dados0140['nome_cliente']."</strong>, inscrita no CNPJ n.º
        <strong>$cnpj</strong>, com sede na <strong>R. *--*, nº *--*, Bairro *--*, município
        de *--*/RS,</strong> em seu objeto social, tem como atividade econômica principal (47.12-1-
        00) - <strong>Comércio varejista de mercadorias em geral</strong>, com predominância de produtos
        <strong>alimentícios - minimercados, mercearias e armazéns</strong>, com data de abertura em
        <strong>data aqui</strong> – Tributada Pelo Lucro Real no período analisado.</p>"; // ".$data[0][0]."
        $html .= "<p><strong>Serviço contratado: </strong></p>";
        $html .= "<p>O presente trabalho baseou-se na;</P>
                    <ul><li><strong>Identificação de Oportunidades de Créditos na esfera administrativa –
                    Análise dos Monofásicos.</strong></li></ul>";
        $html .= "<p>O trabalho foi realizado com base nos $tipo Contribuições enviados pela contabilidade do
        cliente, referente ao período de <strong>".$meses[$mes_ini]."/$ano_ini a ".$meses[$mes_fim]."/$ano_fim.</strong></p>";
        $html .= "<p><strong>Da Análise Contábil/Fiscal:</strong></p>";
        $html .= "<p>Com o embasamento através do Ato Declaratório Interpretativo RFB Nº 4 – Regime
        Monofásico, foram apurados créditos de PIS/COFINS, conforme itens dispostos nas NCM
        da tabela TIPI e CST 04 da Receita Federal Brasileira</p>";
        $html .= "<p>Conforme o artigo 1º do referido ato declaratório:</p>";
        $html .= "<div class='inf'><p>Art. 1º: A partir de 1º de agosto de 2004, com a entrada em vigor dos
        arts. 21 e 37 da Lei nº 10.865, de 30 de abril de 2004, as receitas
        decorrentes da venda de produtos submetidos à incidência
        concentrada ou monofásica da Contribuição para o PIS/Pasep e da
        Cofins estão, em regra, sujeitas ao regime de apuração não
        cumulativa das contribuições, salvo disposições contrárias
        estabelecidas pela legislação.</p></div>";
        $html .= "<p>O total dos valores apurados, em relação aos meses de ".$meses[$mes_ini]."/$ano_ini a ".$meses[$mes_fim]."/$ano_fim, foram
        divididos pelos integrantes da cadeia, cada um fica com seu percentual de crédito</p>";

        $html .= "<p><strong>Relação de valores apurados pos DCTF Web:<strong></p>";
        $html .= "
        <table>
            <tr>
                <td colspan='4'><strong>".$dados0140['nome_cliente']."<strong></td>
            </tr>
            <tr class='titulo'>
                <td>Competência</td>
                <td>PIS</td>
                <td>COFINS</td>
                <td>Crédito a restituir</td>
            </tr>";
            if($dadosC170['aliq_pis'] != 0 || $dadosC170['aliq_cofins'] != 0) {
                $html .= "<tr>
                            <td>";
                $html .=        $meses[$mes_emissao] . '/' . $ano_emissao;
                $html .=    "</td>
                            <td>";
                $html .=        str_replace('.', ',', $cRes_pis);
                $html .=    "</td>
                            <td>";
                $html .=        str_replace('.', ',', $cRes_cofins);
                $html .=    "</td>
                            <td>";
                $html .=        str_replace('.', ',', ( $cRes_pis + $cRes_cofins));
                $html .=    "</td>
                        </tr>";
            }
    
            $html .= "
                <tr class='titulo'>
                    <td>TOTAL</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr";
            $html .= "</table>";
            
            $html .= "<p><strong>Relação de valores apurados antes DCTF Web:</strong></p>";
            $html .= "
            <table>
                <tr>
                    <td colspan='4'><strong>".$dados0140['nome_cliente']."<strong></td>
                </tr>
                <tr class='titulo'>
                    <td>Competência</td>
                    <td>PIS</td>
                    <td>COFINS</td>
                    <td>Crédito a restituir</td>
                </tr>
            </table>";


        $html .= "<p><strong>Procedimento para utilização do crédito apurado</strong></p>";
        $html .= "<p>A retificação deverá ser feita mensalmente, conforme período apurado, através do $tipo
        Contribuições, de acordo com o modus operandi enviado anexo a este parecer.
        Através disso, há o entendimento que não há necessidade de solicitar a compensação
        desse crédito através de processo administrativo e/ou judicial.</p>";

        $html .= "<p>Ficamos à disposição para quaisquer dúvidas.</p>";

        $html .= "<p>Atenciosamente,</p>";

        $html .= "<p class='center'>Grupo Marpa Gestão Tributária</p>";
        $html .= "<div class='assinatura'><p class='center'><strong>__________________________________<br>
        Eduardo Rossi Bitello</strong><br>
        Diretor Jurídico<br>
        eduardo@grupomarpa.com.br<br></p></div>";

        $this->salvarPDF($style . $html);
    }

    private function getDados()
    {
        $ret = [];

        $analise = new monofasico_analise($this->_cnpj);

        $dados = $analise->getDados0140();

        if (count($dados) == 0) {
            $dados = [];
            $dados = $analise->getDados0150();
        }



        print_r($dados);

        // foreach($dados as $dado) {
        //     $temp = [];
        //     $temp['cod_part'] = $dado[]
        // }

        return $ret;
    }

    private function salvarPDF($htmlPDF)
    {
        $options = new Options();
        $options->set('isPhpEnabled', TRUE);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlPDF);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('contrato.pdf', array('Attachment' => false));
    }
}
