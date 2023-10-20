<?php

/*
 * Data Criacao: 29/05/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para importação de revista de Patetes em PDF
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class patentes_pdf {
    var $funcoes_publicas = array(
        'index'             => true,
        'avisos'            => true,
        'upload'            => true,
        'buscaRevista'      => true,
    );

    // Caminho do arquivo txt
    private $_path_txt;
    
    // Caminho do arquivo pdf recebido
    private $_path_pdf;

    // Numero da revista
    private $_numero;

    // Data da revista
    private $_data;

    // Tabela01
    private $_tabela;

    function __construct() {
        global $config;

        conectaCONSULT();

        $this->_path_txt = $config['baseFW'] . "arquivos/patentes_pdf/txt/";
        $this->_path_pdf = $config['baseFW'] . 'arquivos/patentes_pdf/pdf/';
    }

    public function index() {
        $html = '';

        $html .= "<a href='".getLink()."upload' class='btn btn-success float-right'>Importar arquvo</a><br><br>";

        $sql = "SELECT DISTINCT(revista) FROM preImportacaoPdfPatentes";
        $revistas = query2($sql);

        if(is_array($revistas) && count($revistas) > 0) {
            $html .= '<form method="POST" action="'.getLink().'buscaRevista" id="form_revista">
                        <div class="form-group row">
                            <label for="revista" class="col-sm-1 col-form-label">Revistas</label>
                            <div class="col-sm-2">
                                <select name="revista" id="revista" class="form-control">';
            foreach($revistas as $revista) {
                $html .=            "<option>{$revista['revista']}</option>";
            }
            $html .=            '</select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="tipo" class="col-sm-1 col-form-label">Tipo Pesquisa</label>
                            <div class="col-sm-2">
                                <select name="tipo" id="tipo" class="form-control">
                                    <option value="detalhado">Relatório detalhado</option>
                                    <option value="comparado">Comparação XML</option>
                                    <option value="enviar_consultores">Enviar Consultores</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <input type="submit" value="Pesquisar" class="btn btn-primary">
                            </div>
                        </div>
                    </form>';
        } else {
            $html .= "<p>Não há nenhuma revista importada com PDF.</p>";
        }

        $html = addCard(['titulo'=>'Revistas Patentes', 'conteudo'=>$html]);

        return $html;
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

    public function upload() {
        $html = '<a href="'.getLink().'index" class="btn btn-danger float-right">Voltar</a><br><br>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <input class="form-control" type="file" name="arquivo" id="arquivo" accept="application/pdf">
                    </div>

                    <div class="form-group">
                        <input class="btn btn-primary" type="submit" value="Enviar">
                    </div>';

        $valor = '';
        if(!empty($_FILES)) {
            if(!empty($_POST['anteriores'])) {
                $valor = $_POST['anteriores'] . ';';
            }
            
            $arquivo = $_FILES['arquivo'];

            $valor .= $arquivo['name'];
            $lista = explode(';', $valor);
        //    var_dump($arquivo);
        
            $nome = explode('.', $arquivo['name']);
            if(!file_exists($this->_path_txt.$nome[0].'.txt')) { // Se não existe o TXT, sobe o PDF
                if(move_uploaded_file($arquivo['tmp_name'], $this->_path_pdf.$arquivo['name']) ) {
                    $this->pdfToText($this->_path_pdf.$arquivo['name'], $this->_path_txt.$nome[0].'.txt');
                } else {
                    addPortalMensagem("Erro ao subir o arquivo.", 'error');
                    return;
                }
            }

            $meu_pdf = nl2br(file_get_contents($this->_path_txt.$nome[0].'.txt'));

            $revista = $this->buscaTexto2('Patentes – RPI ', $meu_pdf);
            $revista = (empty($revista)) ? $this->buscaTexto2('Desenho Industrial – RPI ', $meu_pdf) : $revista;
            $revista = explode(' ', $revista);

            $meses = [
                'Janeiro'   => '01',
                'Fevereiro' => '02',
                'Março'     => '03',
                'Abril'     => '04',
                'Maio'      => '05',
                'Junho'     => '06',
                'Julho'     => '07',
                'Agosto'    => '08',
                'Setembro'  => '09',
                'Outrubro'  => '10',
                'Novembro'  => '11',
                'Dezembro'  => '12'
            ];

            $this->_numero = $revista[0];
            $this->_data = $revista[6] . '-' . $meses[$revista[4]] . '-' . $revista[2];

            $processos = $this->getProcessos($meu_pdf);
            // echo $processos[1] . "<br>\n"; // . " - " . $processos[6] 
            // return $html;
            $this->gravaBanco($processos);

            addPortalMensagem("PDF enviado e processado com sucesso.");
            // redireciona(getLink()."avisos&mensagem=PDF enviado e processado com sucesso.&redireciona=upload");
        }

        $html .= '<br><br>
                <div class="form-group">
                    <input class="" type="text" name="anteriores" id="anteriores" value="'.$valor.'" hidden>
                </div>
            </form>';

        if(isset($lista) && count($lista) > 0) {
            $html .= '<ul>';
            foreach($lista as $l) {
                $html .= "<li>$l</li>";
            }
            $html .= '</ul>';
        }

        $html = addCard(['titulo'=>'Upload de Patentes', 'conteudo'=>$html]);

        return $html;
    }

    private function buscaTexto2($subs, $texto, $ate = '<br />') {
        $ret = '';

        if(is_string($texto) && strlen($texto) > 0){
            $ini = strpos($texto, $subs);
            if($ini !== false) {
                $ini += strlen($subs);
                $fim = strpos($texto, $ate, $ini);
                $fim = ($fim === false) ? strpos($texto, '<br />', $ini) : $fim;

                $total = $fim - $ini;

                $ret = substr($texto, $ini, $total);
            }
        }

        return $ret;
    }

    //Pega array onde cada item inicia no código de um processo
    private function getProcessos($texto) {
        set_time_limit(120);

        //Faz um pattern-matching nos identificadores e devolve array com todos os processos
        $ret = [];
        $temp=[];
        if(!empty($texto)){
            preg_match_all('/\(\d{2}\) [A-Z]{2} \d{2} \d{4} \d{6}-\d{1}|\(\d{2}\) [A-Z]{2} \d{7}-\d{1}/', $texto,$temp);
            // preg_match_all('/\(\d{2}\) [A-Z]{2} \d{2} \d{4} \d{6}-\d [A-Z]\d/', $texto,$temp);
            // preg_match_all('/\(\d{2}\) [A-Z]{2} \d{2} \d{4} \d{6}-\d [A-Z\d]?/', $texto,$temp); // '/\(\d{2}\) [A-Z]{2} \d{2} \d{4} \d{6}-\d [A-Z\d]?/'
            $temp = $temp[0];

            foreach($temp as $proc) {
                if(substr($proc, 1, 2) != 61) {
                    $texto=str_replace($proc, '@@@'.$proc, $texto);
                }
                // echo $proc . "<br>\n";
            }

            $ret = explode('@@@', $texto);
        }
        return $ret;
    }

    private function gravaBanco($processos) {
        set_time_limit(0);
// print_r($processos);die();
// print_r($processos);
        if(is_array($processos) && count($processos) > 0) {
            foreach($processos as $k => $processo) {
                // if(strpos($processo, '30 2022 005881-5') !== false) {
                //     $ini = strpos($processo, '(73)') + 4;
                //     echo $processo;
                    // echo 'ini ';
                    // var_dump($ini);
                    // echo '(7 ';
                    // $fim = strpos($processo, '(7', $ini);
                    // var_dump($fim);
                    // echo '<br /> ';
                    // $fim2 = strpos($processo, '<br />', $ini);
                    // var_dump($fim2);
                    // if($fim !== false) {
                    //     $fim = $fim - $ini;
                    //     echo 'FIM 01 ';
                    //     echo substr($processo, $ini, $fim);
                    //     echo ' fim.';
                    // } else {
                    //     $fim2 = $fim2 - $ini;
                    //     echo 'FIM 02 ';
                    //     echo substr($processo, $ini, $fim2);
                    //     echo ' fim.';
                    // }
                    // echo $processo;
                // }
                $num_processo = substr($processo, 8, 16);
                $num_processo = str_replace([' ', '-'], '', $num_processo);

                if(!is_numeric($num_processo)) {
                    $num_processo = substr($num_processo, 0, 8);
                }
    // echo "----------------------------------------------------------- $num_processo <br>\n";
                if(is_numeric($num_processo)) {
                    $inf = $this->getInf2($processo, $num_processo);

                    if(!empty($inf)) {
                        // echo " Deve salvar no banco <br>\n";
                        // print_r($processo);
                        // echo $num_processo;
                        // print_r($inf);
                        $sql = "INSERT INTO preimportacaopdfpatentes (revista, codigoprocesso, data_dep, titulo, inventor, titular, depositante, procurador, codigo_despacho)
                        VALUES ({$this->_numero}, $num_processo, {$inf['data']}, '{$inf['titulo']}', '{$inf['inventor']}', '{$inf['titular']}', '{$inf['depositante']}', '{$inf['procurador']}', '{$inf['codigo_despacho']}')";
                        query2($sql);
                        // echo $sql . "<br>\n";
                    } else {
                        // echo " Sem registro <br>\n";
                    }
                }
            }
        }
    }

    private function getInf2($processo, $num_processo) {
        $ret = [];

        $sql = "SELECT titulo FROM marpapatente WHERE codigoprocesso = $num_processo";
        $row = query2($sql);

        if(is_array($row) && count($row) > 0) {
            $quebra_pagina = 'Patentes – RPI' . $this->buscaTexto2('Patentes – RPI', $processo);
            $processo = str_replace(["'", $quebra_pagina], "", $processo);
            // echo "tem no banco $num_processo <br>\n";
// echo 'titular: ' . $this->buscaTexto2('(73) ', $processo, '(7');
            $data = $this->buscaTexto2('(22) ', $processo);
            $ret['data'] = (!empty($data)) ? "'".date('Y-m-d', strtotime(str_replace('/', '-', $data)))."'" : 'NULL';

            $ret['codigo_despacho'] = str_replace(["<br />", "\n"], '', $this->buscaTexto2('Código ', $processo, '('));
            $ret['titulo']          = str_replace(["<br />", "\n"], '', $this->buscaTexto2('(54) ', $processo, '('));
            $ret['titulo']          = (empty($ret['titulo'])) ? mb_convert_encoding($row[0]['titulo'], 'UTF-8', 'ASCII') : $ret['titulo'];
            $ret['inventor']        = str_replace(["<br />", "\n"], '', $this->buscaTexto2('(72) ', $processo, 'Prazo de Validade:'));
            $ret['titular']         = str_replace(["<br />", "\n"], '', $this->buscaTexto2('(73) ', $processo, '(7'));
            $ret['depositante']     = $this->buscaTexto2('(71) ', $processo);
            $ret['procurador']      = $this->buscaTexto2('(74) ', $processo);
        }

        return $ret;
    }

    private function pdfToText($caminho, $destino)
    {
        //echo shell_exec("pip install pypdf");
        $output=null;
        $retval=null;
        $comando = "/usr/bin/env python3 /var/www/python/parser_pdf.py $caminho $destino";
        log::gravaLog('comandos_python', $comando);
        exec($comando, $output, $retval);

        if ($retval !== 0) {
            echo "Ocorreu um erro ao executar o script Python.";

            var_dump($output);
            foreach($output as $line) {
                echo $line . "<br>";
            }
        }
    }


    // ============================== RELATÓRIOS =================================================
    public function buscaRevista() {
        $html = '';

        if(isset($_POST['revista']) && isset($_POST['tipo'])) {
            $this->_numero = $_POST['revista'];
            $tipo = $_POST['tipo'];
    
            if($tipo == 'detalhado') {
                $html = $this->getRevistaDetalhada();
            } else if($tipo == 'comparado') {
                $html = $this->getRevistasComparadas();
            } else if($tipo == 'enviar_consultores') {
                $html = $this->enviarConsultores();
            }
            
            return $html;
        } else {
            redireciona(getLink() . 'index');
        }
    }

    private function getRevistaDetalhada($email = false) {
        $html = '';

        $sql = "SELECT processo.codigoprocesso, processo.pasta, cliente.sigla, processo.titulo as titulo_processo,
                    cliente.empresa, cliente.codigovendedor, cliente.prefixo, cliente.telefone, cliente.estado, cliente.cidade,
                    vendedor.vendedor, tipo.descricao AS tipo_processo, cancel.descricao AS mot_cancel,
                    publica.descricao AS tipo_publicacao,
                    pdf.titulo as titulo_pdf, pdf.inventor, pdf.titular, pdf.depositante, pdf.procurador,
                    TO_CHAR(pdf.data_dep, 'dd/mm/YYYY') as data_dep, pdf.tipo, pdf.codigo_despacho,
                    tipo_class.descodtipoclass
                FROM marpapatente AS processo
                    LEFT JOIN marpacliente AS cliente ON cliente.sigla = processo.siglacliente
                    LEFT JOIN marpavendedor AS vendedor USING(codigovendedor)
                    LEFT JOIN marpatipoprocesso AS tipo USING(codigotipoprocesso)
                    LEFT JOIN marpamotcancel AS cancel USING(codigomotcancel)
                    LEFT JOIN marpatipopublicacao AS publica USING(codigotipopublicacao)
                    LEFT JOIN preImportacaoPdfPatentes AS pdf USING(codigoprocesso)
                    LEFT JOIN marpatipoclassificacao AS tipo_class USING(codtipoclass)
                WHERE
                    pdf.revista = $this->_numero";
        $rows = query2($sql);

        // $sql = "SELECT codigoprocesso, data FROM preImportacaoPdf WHERE revista = $revista";
        // $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            if(!$email) {
                $html = "Total: " . number_format(count($rows), 0, ',', '.');
                $html .= "<a href='".getLink()."index' class='btn btn-danger float-right'>Voltar</a><br><br>";
            }

            $html .= "<table>";

            $cont = 0;
            $pagina = 1;
            foreach($rows as $row) {
                if($cont >= 5 || $pagina == 1) {
                    $html .= "<tr style='border-style:solid; border-width:1px;'>
                                <td colspan='2' style='text-align: right; padding-right: 50px;'>
                                    <b>Relatório de Pré-Importação de Revista de Patentes</b>
                                </td>
                                <td colspan='2'>
                                    <div class='row' style='width: 100%;'>
                                        <div class='col-sm-9'>
                                            Revista: {$this->_numero}
                                        </div>
                                        <div class='col-sm-3 float-right'>
                                            Página $pagina
                                        </div>
                                    </div>
                                </td>
                            </tr>";

                    $cont = 0;
                    $pagina++;
                }
                $cont++;

                $titulo = (empty($row['titulo_pdf'])) ? mb_convert_encoding($row['titulo_processo'], 'UTF-8', 'ASCII') : $row['titulo_pdf'];

                $html .= "<tr>
                            <td >Processo: {$row['codigoprocesso']}</td>
                            <td colspan='2'>Tipo proc.: {$row['tipo_processo']}</td>
                            <td>Tipo Public.: {$row['tipo_publicacao']}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='5'><b>Despacho: {$row['codigo_despacho']}</b></td>
                        </tr>
                        <tr>
                            <td>Cliente: {$row['sigla']}</td>
                            <td colspan='2'>".mb_convert_encoding($row['empresa'], 'UTF-8', 'ASCII')."</td>
                            <td>Fone: ({$row['prefixo']}) {$row['telefone']}</td>
                            <td>Mot cancel.: ".mb_convert_encoding($row['mot_cancel'], 'UTF-8', 'ASCII')."</td>
                        </tr>
                        <tr>
                            <td>Consultor: {$row['codigovendedor']}</td>
                            <td colspan='2'>{$row['vendedor']}</td>
                            <td>Pasta: {$row['pasta']}</td>
                            <td>{$row['estado']} - ".mb_convert_encoding($row['cidade'], 'UTF-8', 'ASCII')."</td>
                        </tr>
                        
                        <tr>
                            <td colspan='4'>Data Dep.: {$row['data_dep']}</td>
                            <td style='text-align: center;'>{$row['descodtipoclass']}</td>
                        </tr>
                        <tr>
                            <td colspan='5'>Inventor: {$row['inventor']}</td>
                        </tr>
                        <tr>
                            <td colspan='5'>Titular: {$row['titular']}</td>
                        </tr>
                        <tr>
                            <td colspan='5'>Procurador: {$row['procurador']}</td>
                        </tr>
                        <tr>
                            <td colspan='5'>Titulo: $titulo</td>
                        </tr>
                        <tr>
                            <td colspan='5'>Inventor: {$row['inventor']}</td>
                        </tr>";
                $quebra_linha = ($cont >= 5) ? 'style="page-break-after: always;"' : '';
                $html .= "<tr $quebra_linha>
                            <td colspan='5'>================================================================================================================================</td>
                        </tr>";
            }
            $html .= "</table>";

            $style = '<style>
            td {
                padding: 0 7px;
            }
            
            </style>';
        }

        $html = addCard(['titulo'=>'Relatório Patentes', 'conteudo'=>$style . $html]);

        return $html;
    }

    private function getRevistasComparadas() {
        $ret = '';
        $titulo = 'Comparativo diferença entre importação XML - PDF';

        $param = [];
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['titulo'] = $titulo;
		$this->_tabela = new tabela01($param);

        $this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

        $param = array(
			'texto' => 'Voltar',
			'onclick' => "setLocation('" . getLink() . "index')",
            'cor' => 'danger',
		);
		$this->_tabela->addBotaoTitulo($param);

        if(!empty($dados)) {
            $ret .= $this->_tabela;
        } else {
            $html = "<a href='".getLink()."index' class='btn btn-danger float-right'>Voltar</a><br><br>
                    <p>Nenhum registro divergente encontrado.</p>";
            $ret .= addCard(['titulo' => $titulo, 'conteudo' => $html]);
        }

        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'pdf', 'etiqueta' => 'Processos PDF', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'xml', 'etiqueta' => 'Processos XML', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];
        $pdf = [];
        $xml = [];

        $sql = "SELECT codigoprocesso FROM preimportacaopdfpatentes WHERE revista = ".$this->_numero;
        $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $pdf[$row['codigoprocesso']] = $row['codigoprocesso'];
            }
        }

        $sql = "SELECT mp.codigoprocesso FROM marpamovpat AS mv
                INNER JOIN marpapatente AS mp ON(mv.codigoprocesso = mp.codigoprocesso)
                WHERE revista = ".$this->_numero;
        $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $xml[$row['codigoprocesso']] = $row['codigoprocesso'];

                if(!isset($pdf[$row['codigoprocesso']])) {
                    $temp = [];
                    $temp['pdf'] = 'Não existe';
                    $temp['xml'] = $row['codigoprocesso'];

                    $ret[] = $temp;
                }
            }
        }

        if(count($pdf) > 0) {
            foreach($pdf as $processo) {
                if(!isset($xml[$processo])) {
                    $temp = [];
                    $temp['pdf'] = $processo;
                    $temp['xml'] = 'Não existe';

                    $ret[] = $temp;
                }
            }
        }

        return $ret;
    }

    private function enviarConsultores() {
        $html = '';

        $sql = "SELECT DISTINCT ON (codigovendedor) codigovendedor,vendedor,
                    CASE WHEN (codstatus = 1 AND mv.email IS NOT NULL AND mv.email != '') THEN mv.email ELSE 'william@grupomarpa.com.br' END as email
                FROM marpavendedor mv
                    INNER JOIN marpacliente USING(codigovendedor)
                WHERE codstatus = 1";
        $consultores = query2($sql);

        if(is_array($consultores) && count($consultores) > 0) {
            foreach($consultores as $consultor) {
                $sql = "SELECT processo.codigoprocesso, processo.pasta, cliente.sigla,
                            cliente.empresa, cliente.codigovendedor, cliente.prefixo, cliente.telefone, cliente.estado, cliente.cidade,
                            cliente.status_cliente, vendedor.vendedor, tipo.descricao AS tipo_processo, cancel.descricao AS mot_cancel,
                            publica.descricao AS tipo_publicacao,
                            pdf.titulo, pdf.inventor, pdf.titular, pdf.depositante, pdf.procurador,
                            TO_CHAR(pdf.data_dep, 'dd/mm/YYYY') as data_dep, pdf.tipo, pdf.codigo_despacho,
                            tipo_class.descodtipoclass
                        FROM marpapatente AS processo
                            LEFT JOIN marpacliente AS cliente ON cliente.sigla = processo.siglacliente
                            LEFT JOIN marpavendedor AS vendedor USING(codigovendedor)
                            LEFT JOIN marpatipoprocesso AS tipo USING(codigotipoprocesso)
                            LEFT JOIN marpamotcancel AS cancel USING(codigomotcancel)
                            LEFT JOIN marpatipopublicacao AS publica USING(codigotipopublicacao)
                            LEFT JOIN preImportacaoPdfPatentes AS pdf USING(codigoprocesso)
                            LEFT JOIN marpatipoclassificacao AS tipo_class USING(codtipoclass)
                        WHERE
                            pdf.revista = {$this->_numero}
                            AND cliente.codigovendedor = {$consultor['codigovendedor']}
                            AND processo.pasta IS NOT NULL
                            AND cliente.status_cliente != 'V'
                            AND cliente.status_cliente != 'D'
                            AND cliente.status_cliente != 'I'
                            AND processo.pasta NOT IN (SELECT pasta FROM marpaandamentopat WHERE codstatusandamento = 3)
                            AND (processo.codigomotcancel NOT IN (1,2,3,4,5,6,7,8,10,11,12,14) OR processo.codigomotcancel IS NULL)";
                $rows = query2($sql);

                if(is_array($rows) && count($rows) > 0) {

                    //18 -> quantidade de colunas
                    $tab = new tabela_gmail01(['colunas' => 18]);
                    //largura da tabela
                    $tab->abreTabela(1000);
                    // $tab->addTitulo('Relatório de Pré-Importação de Revista de Patentes', 18);
                    $tab->addTitulo('Patentes - Relatório da Revista - Clientes', 18);
                    $tab->addTitulo('Revista: '.$this->_numero, 18);

                    // $tab->abreTR();
                    //     $tab->abreTD('<b>Patentes - Relatório da Revista - Clientes</b>', 18, 'centro');
                    //     // $tab->abreTD($row['avaria'],2,'direita');
                    //     // $tab->abreTD($row['verba'],2,'direita');
                    //     // $tab->abreTD($row['total'],2,'direita');
                    //     // $tab->abreTD($row['comprador'],4);
                    // $tab->fechaTR();

                    // $tab->abreTR();
                    //     $tab->abreTD('Revista: '.$this->_numero, 18, 'centro');
                    // $tab->fechaTR();

                    foreach($rows as $row) {
                        switch ($row['status_cliente']) {
                            case "I":
                                $status = "INATIVO";
                            break;
                            case "V":
                                $status = "EM AVISO DE DESACOMPANHAMENTO";
                            break;
                            case "D":
                                $status = "EM DESACOMPANHAMENTO";
                            break;
                            case "T":
                                $status = "TEMPOR�RIO";
                            break;
                            case "E":
                                $status = "EXCLU�DO";
                            break;
                            case "F":
                                $status = "TRANSFERIDO";
                            break;
                            case "A":
                            default:
                                $status = "";
                            break;
                        }
				
                        $tab->abreTR();
                            $tab->abreTD("Processo: ".$row['codigoprocesso'], 6);
                            $tab->abreTD("<b>Despacho: {$row['codigo_despacho']}</b>", 6);
                            $tab->abreTD($row['tipo_publicacao'], 6);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Cliente: ".$row['sigla'], 6);
                            $tab->abreTD(mb_convert_encoding($row['empresa'], 'UTF-8', 'ASCII'), 6);
                            $tab->abreTD("Mot Des: ".$row['mot_cancel'], 6);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Consultor: ".$row['codigovendedor'], 6);
                            $tab->abreTD("Vendedor: ".$row['vendedor'], 6);
                            $tab->abreTD("Pasta: ".$row['pasta'], 3); // $row['estado']." - ".$row['cidade']
                            $tab->abreTD($row['estado']." - ".mb_convert_encoding($row['cidade'], 'UTF-8', 'ASCII'), 3);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Data dep: ".$row['data_dep'], 17, 'esquerda');
                            $tab->abreTD($row['descodtipoclass'], 1);
                        $tab->fechaTR();

                        $tab->abreTR();
                            if(empty($status)) {
                                $tab->abreTD("Inventor: ".$row['inventor'], 18, 'esquerda');
                            } else {
                                $tab->abreTD("Inventor: ".$row['inventor'], 15, 'esquerda');
                                $tab->abreTD($status, 3, 'esquerda');
                            }
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Procurador: ".$row['procurador'], 18, 'esquerda');
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Título: ".$row['titulo'], 18, 'esquerda');
                        $tab->fechaTR();

                        // $tab->addLinhaBranco();
                        $tab->addTitulo('', 18);
                    }	
                    $tab->fechaTabela();

                    $ret = ''.$tab;

                    $param = [];
                    $param['destinatario'] = $consultor['email'];
                    $param['mensagem'] = $ret;
                    $param['assunto'] = 'Relatório de Pré-Importação de Revista de Patentes';
                    enviaEmail($param);
                    // return $ret;
                }
            }

            redireciona(getLink() . "avisos&mensagem=E-mails enviados para os consultores!");
        } else {
            redireciona(getLink() . "avisos&mensagem=Erro ao encontrar os consultores&tipo=erro");
        }

    }
}