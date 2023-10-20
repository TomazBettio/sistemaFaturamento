<?php

/*
 * Data Criacao: 29/05/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para importação de revista de Marcas em PDF
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class marcas_pdf {
    var $funcoes_publicas = array(
        'index'             => true,
        'avisos'            => true,
        'upload'            => true,
        'buscaRevista'      => true,
        'schedule'          => true,
    );

    // Caminho do arquivo pdf recebido
    private $_path_txt;
    
    private $_path_pdf;
    
    // Classe tabela01
    private $_tabela;

    // Número da revista
    private $_numero;

    // Data da revista;
    private $_data;
    

    function __construct() {
        global $config;
        conectaCONSULT();

        $this->_path_txt = $config['baseFW']."arquivos/marcas_pdf/txt/";
        $this->_path_pdf = $config['baseFW'].'arquivos/marcas_pdf/pdf/';
    }

    public function schedule() {
        $arquivos = $this->getArquivos($this->_path_pdf);

        if(is_array($arquivos) && count($arquivos) > 0) {
            foreach($arquivos as $arquivo) {
                log::gravaLog('marcas_patentes', "arquivo: $arquivo");
                $nome = explode('.', $arquivo);
                if(!file_exists($this->_path_txt.$nome[0].'.txt')) { // Se não existe o TXT, quebra o PDF
                    $this->pdfToText($this->_path_pdf.$arquivo, $this->_path_txt.$nome[0].'.txt');
                    log::gravaLog('marcas_patentes', "quebrou PDF");
                }

                $meu_pdf = nl2br(file_get_contents($this->_path_txt.$nome[0].'.txt'));

                $revista = $this->buscaTexto2('MARCAS   -  RPI ', $meu_pdf);
                $revista = explode(' ', $revista);

                $this->_numero = $revista[0];
                $this->_data = date('Y-m-d', strtotime(str_replace('/', '-', $revista[3])));
                
                $processos = $this->getProcessos($meu_pdf);
                // echo $processos[1] . "<br>\n"; // . " - " . $processos[6] 
                // return $html;
                $this->gravaBanco($processos);

                log::gravaLog('marcas_patentes', "excluiu PDF");
                unlink($this->_path_pdf . $arquivo);
            }
        }
    }

    private function getArquivos($dir) {
		$ret = [];
		$diretorio = dir($dir);

		if(!is_null($diretorio) && $diretorio !== false){
			while ($arquivo = $diretorio->read()) {
				$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
				$ext = strtolower($ext);
				if ($arquivo != '.' && $arquivo != '..' && $ext == 'pdf') {
					$ret[] = $arquivo;
				}
			}
		}
		return $ret;
	}

    public function index() {
        $html = '';

        $html .= "<a href='".getLink()."upload' class='btn btn-success float-right'>Importar arquvo</a><br><br>";

        $sql = "SELECT DISTINCT(revista) FROM preImportacaoPdf ORDER BY revista DESC";
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
                            <div>
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

        $html = addCard(['titulo'=>'Revistas Marcas', 'conteudo'=>$html]);

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
                $this->enviarConsultores();
            }
            
            return $html;
        } else {
            redireciona(getLink() . 'index');
        }
    }

    private function getRevistaDetalhada() {
        $html = '';

        $sql = "SELECT processo.codigoprocesso, processo.pasta, processo.codigoclasse1, processo.codigoclasse2,
                    processo.codigoclasse3, processo.marca as marca_processo,
                    cliente.empresa, cliente.codigovendedor, cliente.prefixo, cliente.telefone, cliente.cidade,
                    vendedor.vendedor, tipo.descricao AS tipo_processo, cancel.descricao AS mot_cancel,
                    publica.descricao AS tipo_publicacao, apresentacao.descricao AS apresentacao,
                    natur.descricao AS natureza, pdf.marca as marca_pdf, pdf.despacho, pdf.inpi, pdf.titular,
                    pdf.natureza, pdf.detalhe_despacho, TO_CHAR(pdf.data, 'dd/mm/YYYY') as data
                FROM marpaprocesso AS processo
                    LEFT JOIN marpacliente AS cliente USING(sigla)
                    LEFT JOIN marpavendedor AS vendedor USING(codigovendedor)
                    LEFT JOIN marpatipoprocesso AS tipo USING(codigotipoprocesso)
                    LEFT JOIN marpamotcancel AS cancel USING(codigomotcancel)
                    LEFT JOIN marpatipopublicacao AS publica USING(codigotipopublicacao)
                    LEFT JOIN marpaapresentacao AS apresentacao USING(codigoapresentacao)
                    LEFT JOIN marpanatureza AS natur USING(codigonatureza)
                    LEFT JOIN preImportacaoPdf AS pdf USING(codigoprocesso)
                WHERE
                    pdf.revista = {$this->_numero}";
        $rows = query2($sql);

        // $sql = "SELECT codigoprocesso, data FROM preImportacaoPdf WHERE revista = $revista";
        // $rows = query2($sql);

        $cont = 0;
        $pagina = 1;
        if(is_array($rows) && count($rows) > 0) {
            $html = "Total: " . number_format(count($rows), 0, ',', '.');
            $html .= "<a href='".getLink()."index' class='btn btn-danger float-right'>Voltar</a><br><br>
                    <table>";
            foreach($rows as $row) {
                if($cont >= 5 || $pagina == 1) {
                    $html .= "<tr style='border-style:solid; border-width:1px;'>
                                <td colspan='4' style='text-align: center; padding-right: 50px;'>
                                    <b>Relatório de Pré-Importação de Revista de Marcas</b>
                                    Revista: {$this->_numero} - {$rows[0]['data']}
                                    <div class='float-right'>Página $pagina</div>
                                </td>
                            </tr>";

                    $cont = 0;
                    $pagina++;
                }
                $cont++;

                $marca = (empty($row['marca_pdf'])) ? mb_convert_encoding($row['marca_processo'], 'UTF-8', 'ASCII') : $row['marca_pdf'];

                $html .= "<tr>
                            <td colspan='2'>Processo: {$row['codigoprocesso']}</td>
                            <td>Tipo processo: {$row['tipo_processo']}</td>
                            <td>Mot Canc: ".mb_convert_encoding($row['mot_cancel'], 'UTF-8', 'ASCII')."</td>
                        </tr>
                        <tr>
                            <td colspan='2'>Cliente: ".mb_convert_encoding($row['empresa'], 'UTF-8', 'ASCII')."</td>
                            <td></td>
                            <td>Fone: ({$row['prefixo']}) {$row['telefone']}</td>
                        </tr>
                        <tr>
                            <td colspan='2'>Vendedor: {$row['codigovendedor']} - {$row['vendedor']}</td>
                            <td></td>
                            <td>Pasta: {$row['pasta']}</td>
                        </tr>
                        <tr>
                            <td colspan='2'><b>Despacho: {$row['despacho']}</b></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='2'>Public. Tipo: {$row['tipo_publicacao']}</td>
                            <td>Cidade: ".mb_convert_encoding($row['cidade'], 'UTF-8', 'ASCII')."</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='2'>Apresentação: {$row['apresentacao']}</td>
                            <td>Natureza: ".mb_convert_encoding($row['natureza'], 'UTF-8', 'ASCII')."</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Classe: {$row['codigoclasse1']} {$row['codigoclasse2']} {$row['codigoclasse3']}
                            <span style='margin-left: 30px;'>INPI: {$row['inpi']}</span></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='2'>Marca: $marca</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='2'>Titular: {$row['titular']}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='4'>{$row['detalhe_despacho']}</td>
                        </tr>";
                $quebra_linha = ($cont >= 5) ? 'style="page-break-after: always;"' : '';
                $html .= "<tr $quebra_linha>
                            <td colspan='4'>================================================================================================================================</td>
                        </tr>";
            }
            $html .= "</table>";
        }

        $html = addCard(['titulo'=>'Relatório Marcas', 'conteudo'=>$html]);

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

        $sql = "SELECT codigoprocesso FROM preimportacaopdf WHERE revista = ".$this->_numero;
        $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $pdf[$row['codigoprocesso']] = $row['codigoprocesso'];
            }
        }

        $sql = "SELECT DISTINCT ON (codigoprocesso,codigodespacho,mv.servico) mv.codigoprocesso, mv.codigodespacho
                FROM marpamovformat mv
                    LEFT JOIN marpaprocesso mp USING(codigoprocesso)
                WHERE mv.revista = {$this->_numero} AND mp.pasta IS NOT NULL";
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
        $sql = "SELECT DISTINCT ON (codigovendedor) codigovendedor,vendedor,
                    CASE WHEN (codstatus = 1 AND mv.email IS NOT NULL AND mv.email != '') THEN mv.email ELSE 'william@grupomarpa.com.br' END as email
                FROM marpavendedor mv
                    INNER JOIN marpacliente USING(codigovendedor)
                WHERE codstatus = 1";
        $consultores = query2($sql);

        if(is_array($consultores) && count($consultores) > 0) {
            foreach($consultores as $consultor) {
                $sql = "SELECT processo.codigoprocesso, processo.pasta, processo.codigoclasse1, processo.codigoclasse2,
                            processo.codigoclasse3, processo.marca as marca_processo,
                            cliente.empresa, cliente.codigovendedor, cliente.prefixo, cliente.telefone, cliente.cidade,
                            cliente.status_cliente, tipo.descricao AS tipo_processo, cancel.descricao AS mot_cancel,
                            publica.descricao AS tipo_publicacao, apresentacao.descricao AS apresentacao,
                            natur.descricao AS natureza, pdf.marca as marca_pdf, pdf.despacho, pdf.inpi, pdf.titular,
                            pdf.natureza, pdf.detalhe_despacho, TO_CHAR(pdf.data, 'dd/mm/YYYY') as data
                        FROM marpaprocesso AS processo
                            LEFT JOIN marpacliente AS cliente USING(sigla)
                            LEFT JOIN marpatipoprocesso AS tipo USING(codigotipoprocesso)
                            LEFT JOIN marpamotcancel AS cancel USING(codigomotcancel)
                            LEFT JOIN marpatipopublicacao AS publica USING(codigotipopublicacao)
                            LEFT JOIN marpaapresentacao AS apresentacao USING(codigoapresentacao)
                            LEFT JOIN marpanatureza AS natur USING(codigonatureza)
                            LEFT JOIN preImportacaoPdf AS pdf USING(codigoprocesso)
                        WHERE
                            pdf.revista = {$this->_numero}
                            AND cliente.codigovendedor = {$consultor['codigovendedor']}
                            AND processo.pasta IS NOT NULL
                            AND cliente.status_cliente != 'V'
                            AND cliente.status_cliente != 'D'
                            AND cliente.status_cliente != 'I'
                            AND processo.pasta NOT IN (SELECT pasta FROM marpaandamento WHERE codstatusandamento = 3)
                            AND processo.codigomotcancel NOT IN (1,2,3,4,5,6,7,8,10,11,12,14)
                        ORDER BY pdf.codigoprocesso";
                $rows = query2($sql);

                if(is_array($rows) && count($rows) > 0) {
                    $tab = new tabela_gmail01(['colunas' => 18]);
                    //largura da tabela
                    $tab->abreTabela(1000);

                    $tab->addTitulo("Relatório de Importação da Revista de Marcas - {$consultor['vendedor']}", 18);
                    $tab->addTitulo("Revista: {$this->_numero} - {$rows[0]['data']}", 18);

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
								$status = "TEMPOR RIO";
							break;
							case "E":
								$status = "EXCLU DO";
							break;
							case "F":
								$status = "TRANSFERIDO";
							break;
							case "A":
							default:
								$status = "";
							break;
						}

                        $marca = (empty($row['marca_pdf'])) ? mb_convert_encoding($row['marca_processo'], 'UTF-8', 'ASCII') : $row['marca_pdf'];

                        $tab->abreTR();
                            $tab->abreTD("Processo: ".$row['codigoprocesso'], 6);
                            $tab->abreTD("Tipo Processo: ".$row['tipo_processo'], 6);
                            $tab->abreTD("Mot Canc.:".mb_convert_encoding($row['mot_cancel'], 'UTF-8', 'ASCII'), 6);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Cliente: ".mb_convert_encoding($row['empresa'], 'UTF-8', 'ASCII'), 12);
                            $tab->abreTD("Fone: ({$row['prefixo']}) {$row['telefone']}", 6);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Vendedor: {$consultor['codigovendedor']} - {$consultor['vendedor']}", 12);
                            $tab->abreTD("Pasta: {$row['pasta']}", 6);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("<b>Despacho: {$row['despacho']}</b>", 18);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Public. Tipo: {$row['tipo_publicacao']}", 9);
                            $tab->abreTD("Cidade: ".mb_convert_encoding($row['cidade'], 'UTF-8', 'ASCII'), 9);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Apresentação: {$row['apresentacao']}", 9);
                            $tab->abreTD("Natureza: ".$row['natureza'], 9);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Classe: {$row['codigoclasse1']} {$row['codigoclasse2']} {$row['codigoclasse3']}
                            <span style='margin-left: 30px;'>INPI: {$row['inpi']}</span>", 18);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Marca: $marca", 18);
                        $tab->fechaTR();

                        $tab->abreTR();
                            $tab->abreTD("Titular: {$row['titular']}", 18);
                        $tab->fechaTR();

                        $tab->addTitulo('', 18);
                    }

                    $tab->fechaTabela();

                    $ret = ''.$tab;

                    $param = [];
                    $param['destinatario'] = $consultor['email'];
                    // $param['destinatario'] = 'rafael.postal@verticais.com.br; marcas.sergio@marpa.com.br';
                    $param['mensagem'] = $ret;
                    $param['assunto'] = 'Relatório de Pré-Importação de Revista de Marcas '.$consultor['email'];
                    enviaEmail($param);
                }
            }

            redireciona(getLink() . "avisos&mensagem=E-mails enviados para os consultores!");
        } else {
            redireciona(getLink() . "avisos&mensagem=Erro ao encontrar os consultores&tipo=erro");
        }
    }

    public function upload() {
        $html = '<a href="'.getLink().'index" class="btn btn-danger float-right">Voltar</a><br><br>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <input class="form-control" type="file" name="arquivo" id="arquivo" accept="application/pdf">
                    </div>

                    <div class="form-group">
                        <input class="btn btn-primary" type="submit" value="Enviar">
                    <div class="form-group">
                </form><br><br>';


        if(!empty($_FILES)) {
            $arquivo = $_FILES['arquivo'];
        //    var_dump($arquivo);
        
            $nome = explode('.', $arquivo['name']);
            if(!file_exists($this->_path_txt.$nome[0].'.txt')) { // Se não existe o TXT, sobe o PDF
                if(move_uploaded_file($arquivo['tmp_name'], $this->_path_pdf.$arquivo['name']) ) {
                    // $this->pdfToText($this->_path_pdf.$arquivo['name'], $this->_path_txt.$nome[0].'.txt');
                    addPortalMensagem("Upload do arquivo {$arquivo['name']} realizado com sucesso!");
                } else {
                    addPortalMensagem("Erro ao subir o arquivo.", 'error');
                    return;
                }
            }

            // $meu_pdf = nl2br(file_get_contents($this->_path_txt.$nome[0].'.txt'));

            // $revista = $this->buscaTexto2('MARCAS   -  RPI ', $meu_pdf);
            // $revista = explode(' ', $revista);

            // $this->_numero = $revista[0];
            // $this->_data = date('Y-m-d', strtotime(str_replace('/', '-', $revista[3])));
            
            // $processos = $this->getProcessos($meu_pdf);
            // // echo $processos[1] . "<br>\n"; // . " - " . $processos[6] 
            // // return $html;
            // $this->gravaBanco($processos);

            // addPortalMensagem("PDF enviado e processado com sucesso.");
            // redireciona(getLink()."avisos&mensagem=PDF enviado e processado com sucesso.&redireciona=upload");
        }

        $arquivos = $this->getArquivos($this->_path_pdf);

        if(is_array($arquivos) && count($arquivos) > 0) {
            $html .= "<p>Arquivos esperando ser processados:</p>
                    <ul>";
            foreach($arquivos as $arquivo) {
                $html .= "<li>$arquivo</li>";
            }
            $html .= "</ul>";
        } else {
            $html .= "<p>Não há arquivos esperando ser processados</p>";
        }

        $html = addCard(['titulo'=>'Upload Marcas', 'conteudo'=>$html]);
        
        return $html;
    }

    private function gravaBanco($processos) {
        set_time_limit(0);
// echo count($processos) . " <br>\n";
// print_r($processos);
        if(is_array($processos) && count($processos) > 0) {
            foreach($processos as $processo) {
                // echo 'inicio: ' . substr($processo, 2, 13);
                $num_processo = trim(substr($processo, 1, 14));
                // echo "Número do processo: $num_processo <br>\n";
                // var_dump($num_processo);
                if(preg_match("/^[0-9]{12}$/", $num_processo)) {
                    // echo "ok <br>\n";
                    $num_processo = $this->buscaTexto2('Processo afetado: ', $processo);//Processo afetado:
                    $num_processo = (empty($num_processo)) ? $this->buscaTexto2('Processo  afetado: ', $processo) : $num_processo;
                    $num_processo = (empty($num_processo)) ? $this->buscaTexto2('Número do processo: ', $processo) : $num_processo;
                    $num_processo = substr(trim($num_processo), 0, 9);

                    $is_processo = false;
                } else {
                    // echo "é processo <br>\n";
                    $num_processo = trim(substr($processo, 1, 11));
                    $num_processo = str_replace(' ', '', $num_processo);

                    $is_processo = true;
                }
// var_dump($num_processo);
                if(is_numeric($num_processo)) {
                    // echo "é nr <br>\n";
                    $inf = $this->getInf2($processo, $num_processo, $is_processo);

                    if(!empty($inf)) {
                        // echo "veio inf <br>\n";
                        // echo " Deve salvar no banco <br>\n";
                        // print_r($processo);
                        // print_r($inf);
                        $sql = "INSERT INTO preImportacaoPdf (revista, codigoprocesso, marca, despacho, inpi, titular, natureza, detalhe_despacho, data)
                        VALUES ({$this->_numero}, $num_processo, '{$inf['marca']}', '{$inf['despacho']}', '{$inf['inpi']}', '{$inf['titular']}', '{$inf['natureza']}', '{$inf['detalhe_despacho']}', '{$this->_data}')";
                        query2($sql);
                        // echo "SQL: $sql <br>\n";
                    }
                }
            }
        }
    }

    private function getInf2($processo, $num_processo, $is_processo) {
        $ret = [];

        $sql = "SELECT processo.marca, natureza.descricao FROM marpaprocesso AS processo
                    LEFT JOIN marpanatureza AS natureza USING(codigonatureza)
                WHERE processo.codigoprocesso = $num_processo";
        $row = query2($sql);

        if(is_array($row) && count($row) > 0) {
            $marca_banco = trim(mb_convert_encoding(str_replace("'", '', $row[0]['marca']), 'UTF-8', 'ASCII'));
            $natureza_banco = trim(mb_convert_encoding($row[0]['descricao'], 'UTF-8', 'ASCII'));

            $processo = str_replace("'", "", $processo);

            if($is_processo) {
                $marca = $this->buscaTexto2('Elemento nominativo:', $processo);

                $ini = 12;
            } else {
                $marca = $this->buscaTexto2($num_processo, $processo);

                /*
                    Verifica se a página foi quebrada na primeira linha.
                    Quando isso acontece, a data que normalmente vem ao lado do protocolo, vem em baixo
                */
                $primeiro_br = strpos($processo, '<br />');
                $ini = (strpos(substr($processo, $primeiro_br, 30), 'MARCAS   -  RPI') !== false) ? 15 : 34;

            }

            $ret['marca'] = (empty($marca)) ? $marca_banco : $marca;

            // exclui o título que gerar no início de cada página
            $quebra_pagina = 'MARCAS   -  RPI' . $this->buscaTexto2('MARCAS   -  RPI', $processo);
            $processo = str_replace($quebra_pagina, "", $processo);

            // $ini = ($tipo == 'processo') ? 18 : 39;
            $i = strpos($processo, '<br />', $ini) - $ini;
            $ret['despacho'] = substr($processo, $ini, $i);

            // Número da classe vinda do PDF
            $inpi = $this->buscaTexto2('NCL(', $processo);
            if(empty($inpi)) {
                $inpi = $this->buscaTexto2('Classe nacional:', $processo);
                $temp = [];
                // Pega todas as classes do texto
                preg_match_all('/\d{2}\.\d{2}/', $inpi, $temp);
                $temp = $temp[0];
                $inpi = implode(', ', $temp);
            } else {
                $ini = (preg_match('/\d{2}/', substr($inpi, 0, 2))) ? 5 : 4;
                $inpi = substr($inpi, $ini);
            }
            $ret['inpi'] = $inpi;

            $titular = $this->buscaTexto2('Titular do registro:', $processo);
            $ret['titular'] = (empty($titular)) ? $this->buscaTexto2('Titular:', $processo) : $titular;

            $natureza = $this->buscaTexto2('Natureza:', $processo);
            $ret['natureza'] = (empty($natureza)) ? $natureza_banco : $natureza;

            $detalhe_despacho = strpos($processo, 'Detalhes do despacho:');
            $especificacao = strpos($processo, 'Especificação:');
            if($especificacao !== false) {
                $detalhe_despacho = ($detalhe_despacho === false) ? '' : substr($processo, $detalhe_despacho+21, 200);
                $detalhe_despacho .= ' # ' . substr($processo, $especificacao+14, 200);
            } else {
                $detalhe_despacho = ($detalhe_despacho === false) ? '' : substr($processo, $detalhe_despacho+21, 400);
            }
            $ret['detalhe_despacho'] = str_replace(['<br />', '\n'], '', $detalhe_despacho);
        }

        return $ret;
    }
    
    //PEGA PDF PELO PYTHON E TORNA EM .TXT
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
    
    //Pega array onde cada item inicia no código de um processo
    private function getProcessos($texto, $num=200, $ini=0) {
        set_time_limit(0);

        //Faz um pattern-matching nos identificadores e devolve array com todos os processos
        $ret = [];
        $temp=[];
        if(!empty($texto)){
            preg_match_all('/\\n\\s?\d{5}\\s?\d{4}\\s\\s|\\n\\s?\d{12}\\s\\s/', $texto,$temp); // "/<br \/>\\n\\s?[0-9]{9} |<br \/>\\n\\s?[0-9]{12} /"
            $temp = $temp[0];

            foreach($temp as $proc) {
                $texto=str_replace($proc, '@@@'.$proc, $texto);
            }
            $ret = explode('@@@', $texto);
        }
        return $ret;
    }

    private function buscaTexto2($subs, $texto) {
        $ret = '';

        if(is_string($texto) && strlen($texto) > 0){
            $ini = strpos($texto, $subs);
            if($ini !== false) {
                $ini += strlen($subs);
                $fim = strpos($texto, '<br />', $ini);
                $total = $fim - $ini;
                
                $ret = substr($texto, $ini, $total);
            }
        }

        return $ret;
    }

}