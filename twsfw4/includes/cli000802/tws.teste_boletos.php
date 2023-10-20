<?php

//Incluindo o arquivo padrão de inicialização do ObjectBoleto
include '/var/www/twsfw4_dev/includes/PHP-Object-Boleto-master/OB_init.php';
include_once($config['include'].'vendor/autoload.php');
use Spipu\Html2Pdf\Html2Pdf;

class teste_boletos {
    private $_ob;

    private $_param = [];

    private $_banco;

    private $_digitoBanco;

    private $_code;

    private $_path;

    function __construct($param) {
        global $config;

        $this->_path = $config['base'].'html/boletos/';
        //Instanciando a class e informando o código do banco a ser utilizado
        $this->_ob = new OB($param['banco']);
        $this->_param = $param;
    
        //Definindo os dados do vendedor
        $this->_ob->Vendedor
            ->setAgencia($param['agencia'])
            ->setConta($param['conta'])
            ->setDigitoConta($param['digitoConta'])
            ->setRazaoSocial($param['razao'])
            ->setCnpj($param['cnpj'])
            ->setEndereco($param['endereco'])
            ->setEmail($param['emailVendedor'])
            ;
    
        //Definindo configurações gerais
        // $ob->Configuracao
        //     ->setLocalPagamento('Pagável em qualquer banco até o vencimento')
        //     ;
    
        //Definindo configurações do template. Variáveis enviadas para a configuração do template
        $this->_ob->Template
            ->setTitle('ObjBoleto')
            ->setTemplate('html5')
            ->set('variavel', 'valor')
            ;
    
        //Identificando o cliente
        $this->_ob->Cliente
            ->setNome($param['nome'])
            ->setCpf($param['cpf'])
            ->setEmail($param['emailCliente'])
            ;

        //Passando dados pro boleto   
        $this->_ob->Boleto
            ->setValor($param['valor'])
            ->setDiasVencimento($param['diasVencimento'])
            ->setNossoNumero($param['nossoNumero'])
            ->setNumDocumento($param['numeroDocumento'])
            ;

        $this->_banco = $this->_ob->Banco->Nome;
        $this->_digitoBanco = ($param['banco'] == 237) ? '2' : '8';
        $this->_code = $this->_ob->geraCodigo();

        $barcode = new gera_codigo_barra();
        $barcode->getBarcode($this->_code);
        //Renderizando o boleto       
        // $this->_ob->render();
    }

    public function index($param = array()) {
        $codigo = substr($this->_code, 0, 5).'.'.substr($this->_code, 5, 5).' '.substr($this->_code, 10, 5).'.'.substr($this->_code, 15, 6).' '.substr($this->_code, 21, 5).'.'.substr($this->_code, 26, 6).' '.substr($this->_code, 32, 1).' '.substr($this->_code, 33);
// 23790.04209 00000.012344 56000.000002 1 92230000012944
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html>
            <head>
                <title>ObjBoleto</title>';

        $style = '<style>
            div {
                width: auto;
            }
             
            .linha_corte{
                border-bottom:1px dotted #000;
                height:10px;
            }
            .linha_corte label, .autenticacao_mecanica label{
                text-align:right;
            }

            .cabecalho{
                border-bottom: 3px solid #000000;
                width:690px;
                margin-bottom:5px;
            }
            .cabecalho div{float:left;height:25px;margin-top:25px;border:0}
            .cabecalho .banco_logo{width:160px;height:40px;margin-top:10px;}
            .cabecalho .banco_codigo{width:70px;font-size:20px;font-weight:bold;text-align:center;border:0;border-left:3px solid #000000;border-right:3px solid #000000;}
            .cabecalho .linha_digitavel{width:450px;font-size:15px;font-weight:bold;text-align:right}

            .cabecalho td {
                float:left;
                height:25px;
                margin-top:25px;
                border:0;
                border-bottom: 3px solid #000000;
                margin-bottom:5px;
                border-colapse: colapse;
            }

            .banco_logo {
                width:160px;
                height:40px;
                margin-top:10px;
            }

            .banco_codigo {
                font-size:20px;
                font-weight:bold;
                text-align:center;
                border:0;
                border-left:3px solid #000000;
                border-right:3px solid #000000;
            }

            .linha_digitavel {
                width:450px;
                font-size:15px;
                font-weight:bold;
                text-align:right
            }

            .linha div{
                height:27px;
                margin:0;
                margin-bottom:2px;
                float:left;
                border-bottom: 1px solid #C0C0C0;
            }

            .item{
                border-left:5px solid #C0C0C0;
                padding-left:5px;
                padding-right:0px;
                width: 100%;
            }

            .item label {
                font-size: 10px;
            }
            
            .cedente {
                width:270px;
            }

            .autenticacao_mecanica {
                border-bottom:0
            }
            
            .linha_corte label, .autenticacao_mecanica label{
                text-align: right;
                font-size: 10px;
            }

            .demonstrativo {
                border-bottom: 0;
                height: 100px;
            }

            .linha_corte{
                border-bottom:1px dotted #000;
                height:10px;
                width: 100%;
                text-align: right;
            }

            .mensagens {
                height: 150px;
            }

            .sacado-rodape {
                width: 100%;
                border: none;
                heigt: 50px;
            }

            .sacado-rodape label {
                font-size: 10px;
            }

            .codigo_barras {
                border: none;
                border-top: 1px solid #C0C0C0;
            }
        </style>';

        $html .= '
                <link rel="stylesheet" type="text/css" media="all" href="/PHP-Object-Boleto-master/public/css/default.css" />
            </head>
            
            <body>
                <table>
                    <tbody>
                        <tr class="cabecalho">
                            <td>
                                <div class="banco_logo"><img src="'. $this->_path . 'imagensBancos/' . strtolower($this->_banco).'.png" /></div>
                            </td>
                            <td>
                                <div class="banco_codigo">'.$this->_param['banco'].'-'.$this->_digitoBanco.'</div>
                            </td>
                            <td colspan="7">
                                <div class="linha_digitavel">'.$codigo.'</div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="3">
                                <div class="item"">
                                    <label>Cedente</label><br>
                                    '.$this->_param['razao'].'
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="agencia item" style="float: right;">
                                    <label>Ag./Código do Cedente</label><br>
                                    '.$this->_param['agencia'].' / '.$this->_param['conta'].'-'.$this->_param['digitoConta'].'
                                </div>
                            </td>
                            <td colspan="1">
                                <div class="moeda item">
                                    <label>Moeda</label><br>
                                    R$
                                </div>
                            </td>
                            <td colspan="1">
                                <div class="qtd item">
                                    <label>Qtd.</label><br>
                                    1
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="nosso_numero item">
                                    <label>Nosso Número</label><br>
                                    / '.$this->_param['nossoNumero'].'
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="2">
                                <div class="num_doc item">
                                    <label>Número do Documento</label><br>
                                    '.$this->_param['numeroDocumento'].'
                                </div>
                            </td>
                            <td colspan="3">
                                <div class="cpf_cnpj item">
                                    <label>CPF/CNPJ</label><br>
                                    '.$this->_param['cnpj'].'
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="vencimento item">
                                    <label>Vencimento</label><br>
                                    '.$this->_ob->Boleto->Vencimento.'
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="valor item">
                                    <label>Valor do Documento</label><br>
                                    '.$this->_param['valor'].'
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td>
                                <div class="descontos item">
                                    <label>(-) Desconto/Abatimento</label>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="outras_deducoes item">
                                    <label>(-) Outras Deduções</label>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="multa item">
                                    <label>(+) Mora/Multa</label>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="outros_acrescimos item">
                                    <label>(+) Outros Acréscimos</label>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="valor item">
                                    <label>(=) Valor Cobrado</label>
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="9">
                                <div class="sacado item">
                                    <label>Sacado</label><br>
                                    '.$this->_param['nome'].'
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="8">
                                <div class="demonstrativo item">
                                    <label>Demonstrativo</label><br>
                                    Detalhes da compra<br>
                                    Detalhes da compra<br>
                                    Detalhes da compra<br>
                                </div>
                            </td>
                            <td colspan="1">
                                <div class="autenticacao_mecanica">
                                    <label>Autenticação Mecânica</label>
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="9">
                                <div class="linha_corte"><label>Corte na linha pontilhada</label></div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table style="width: 70px;">
                    <tbody>
                        <tr class="cabecalho">
                            <td>
                                <div class="banco_logo"><img src="'. $this->_path . 'imagensBancos/' . strtolower($this->_banco).'.png" /></div>
                            </td>
                            <td>
                                <div class="banco_codigo">'.$this->_param['banco'].'-'.$this->_digitoBanco.'</div>
                            </td>
                            <td colspan="7">
                                <div class="linha_digitavel">'.$codigo.'</div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="7">
                                <div class="local_pagamento item">
                                    <label>Local de Pagamento</label><br>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="data_doc item">
                                    <label>Data do documento</label><br>
                                    '.$this->_ob->Boleto->Vencimento.'
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="1">
                                <div class="data_doc item">
                                    <label>Data do documento</label><br>
                                    '.$this->_ob->Boleto->Vencimento.'
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="num_doc item">
                                    <label>Número do documento</label><br>
                                    '.$this->_param['numeroDocumento'].'
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="espec_doc item">
                                    <label>Espécie Doc.</label><br>
                    
                                </div>
                            </td>
                            <td>
                                <div class="aceite item">
                                    <label>Aceite</label><br>
                    
                                </div>
                            </td>
                            <td colspan="1">
                                <div class="dt_proc item">
                                        <label>Data proc</label><br>
                                        '.Datas::data_hoje().'
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="nosso_numero item">
                                    <label>Nosso Número</label><br>
                                    '.$this->_ob->Boleto->NossoNumero.'
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="2">
                                <div class="uso_banco item">
                                    <label>Uso do Banco</label><br>
                                    
                                </div>
                            </td>
                            <td>
                                <div class="carteira item">
                                    <label>Carteira</label><br>
                                </div>
                            </td>
                            <td>
                                <div class="moeda item">
                                    <label>Moeda</label><br>
                                    R$
                                </div>
                            </td>
                            <td>
                                <div class="qtd item">
                                    <label>Quantidade</label><br>
                                    1
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="valor item">
                                    <label>(x) Valor</label><br>
                                    '.$this->_param['valor'].'
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="item">
                                    <label>(=) Valor do documento</label><br>
                                    '.$this->_param['valor'].'
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="7" rowspan="5">
                                <div class="mensagens item">
                                    <label>Instruções (Texto de responsabilidade do cedente)</label>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="item">
                                    <label>(-) Desconto/Abatimento</label><br>
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="2">
                                <div class="item">
                                    <label>(-) Outras deduções</label><br>
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="2">
                                <div class="item">
                                    <label>(+) Mora/Multa</label>
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="2">
                                <div class="item">
                                    <label>(+) Outros Acréscimos</label>
                                </div>

                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="2">
                                <div class="item">
                                    <label>(=) Valor cobrado</label>
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="9">
                                <div class="sacado-rodape">
                                    <label>Sacado</label><br>
                                    '.$this->_param['nome'].'<br>CPF: '.$this->_param['cpf'].'<br>
                                </div>
                            </td>
                        </tr>
                        <tr class="linha">
                            <td colspan="7">
                                <div class="codigo_barras">
                                    <label>Sacador/Avalista</label><br>
                                    <img src="'. $this->_path . 'codigosDeBarra/' . $this->_code.'.png" />
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="codigo_barras">
                                    <span>Ficha de Compensação</span><br>
                                    <label>Autenticação Mecânica</label>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                
            </body>
        </html>';

        // $paramPDF = [];
        // $paramPDF['orientacao'] = 'L';
        // $PDF = new pdf_exporta02($paramPDF);
        // $PDF->setModoConversao('HTML2PDF');
        // $PDF->setHTML($style.$html);
        // $PDF->grava($this->_path . 'boletosPDF/' . $this->_code . '.pdf');
	    // unset($PDF);

        $html2pdf = new Html2Pdf('P', 'A4', 'pt');
        $html2pdf->writeHTML($style.$html);
        $html2pdf->output($this->_path . 'boletosPDF/' . $this->_code . '.pdf', 'FD'); // , 'FD'

        // print_r($this->_ob->Banco);

        // return $style . $html;
    }
}