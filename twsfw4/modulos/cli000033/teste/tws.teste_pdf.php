<?php

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class teste_pdf {
    var $funcoes_publicas = array(
        'index'             => true,
        'avisos'            => true,
        'upload'            => true,
        'buscaRevista'      => true,
    );

    // Caminho do arquivo pdf recebido
    private $_path;
    
    private $_local;
    
    //Array com os tipos de processos que se deseja exibir
    private $_buscados =[];
    
    // Classe tabela01
    private $_tabela;

    // Número da revista
    private $_numero;

    // Data da revista;
    private $_data;
    

    function __construct() {
        conectaCONSULT();

        $this->_buscados = array("Exigência de pagamento", "Exigência formal", "Publicação");
        $this->_path = "/var/www/twsfw4_dev/includes/pdfparser/txt/";
        $this->_local = '/var/www/twsfw4_dev/includes/pdfparser/arquivos/';
    }

    public function index() {
        $html = '';

        $html .= "<a href='".getLink()."upload' class='btn btn-primary'>Enviar arquvo</a><br><br>";

        $sql = "SELECT DISTINCT(revista) FROM preImportacaoPdf";
        $revistas = query2($sql);

        if(is_array($revistas) && count($revistas) > 0) {
            $html .= '<form method="POST" action="'.getLink().'buscaRevista" id="form_revista">
                        <label for="revista">Revistas</label>
                        <select name="revista" id="revista">';
            foreach($revistas as $revista) {
                $html .=    "<option>{$revista['revista']}</option>";
            }
            $html .=    '</select>

                        <input type="submit" value="Pesquisar" class="btn btn-primary">
                    </form>';
        } else {
            $html .= "<p>Não há nenhuma revista importada com PDF.</p>";
        }

        $html = addCard(['titulo'=>'Revistas', 'conteudo'=>$html]);

        return $html;

        $t='/var/www/twsfw4_dev/includes/pdfparser/arquivos/Marcas2731.txt';
        $meu_pdf = nl2br(file_get_contents($t));

        $revista = $this->buscaTexto2('MARCAS   -  RPI ', $meu_pdf);
        $revista = explode(' ', $revista);

        // $i = strpos($meu_pdf, 'Nº') + 4;
        $this->_numero = $revista[0];
        $this->_data = $revista[3];

        // $this->montaColunas();
        $html .= "<table>
                    <tr style='border-style:solid; border-width:1px;'>
                        <td colspan='2' style='text-align: right; padding-right: 50px;'><b>Relatório de Pré-Importação de Revista de Marcas</b></td>
                        <td>Revista: {$this->_numero} - <br> {$this->_data}</td>
                    </tr>";
        $posicao_ini = 0;
        $intervalo = 50;
        $html .= $this->getDados($this->getProcessos($meu_pdf, $intervalo,$posicao_ini));
        $html .= "</table>";
        //BOTÃO +25 vai aqui
        $html = addCard(['titulo'=>'Registro', 'conteudo'=>$html]);
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
        $revista = $_POST['revista'];

        $sql = "SELECT processo.codigoprocesso, processo.pasta, processo.codigoclasse1, processo.codigoclasse2, processo.codigoclasse3,
                    cliente.empresa, cliente.codigovendedor, cliente.prefixo, cliente.telefone, cliente.cidade,
                    vendedor.vendedor, tipo.descricao AS tipo_processo, cancel.descricao AS mot_cancel,
                    publica.descricao AS tipo_publicacao, apresentacao.descricao AS apresentacao,
                    natur.descricao AS natureza, pdf.marca, pdf.despacho, pdf.inpi, pdf.titular,
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
                    processo.codigoprocesso IN(SELECT codigoprocesso FROM preImportacaoPdf WHERE revista = $revista)";
        $rows = query2($sql);

        // $sql = "SELECT codigoprocesso, data FROM preImportacaoPdf WHERE revista = $revista";
        // $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            $html = "Total: " . number_format(count($rows), 0, ',', '.');
            $html .= "<a href='".getLink()."index' class='btn btn-danger float-right'>Voltar</a><br><br>
                    <table>
                        <tr style='border-style:solid; border-width:1px;'>
                            <td colspan='2' style='text-align: right; padding-right: 50px;'><b>Relatório de Pré-Importação de Revista de Marcas</b></td>
                            <td colspan='2'>Revista: $revista - <br> {$rows[0]['data']}</td>
                        </tr>";
            foreach($rows as $row) {
                $html .= "<tr>
                            <td colspan='2'>Processo: {$row['codigoprocesso']}</td>
                            <td>Tipo processo: {$row['tipo_processo']}</td>
                            <td>Mot Canc: {$row['mot_cancel']}</td>
                        </tr>
                        <tr>
                            <td colspan='2'>Cliente: ".utf8_encode($row['empresa'])."</td>
                            <td></td>
                            <td>Fone: ({$row['prefixo']}) {$row['telefone']}</td>
                        </tr>
                        <tr>
                            <td colspan='2'>Vendedor: {$row['codigovendedor']} - {$row['vendedor']}</td>
                            <td></td>
                            <td>Pasta: {$row['pasta']}</td>
                        </tr>
                        <tr>
                            <td colspan='2'>Despacho: {$row['despacho']}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='2'>Public. Tipo: {$row['tipo_publicacao']}</td>
                            <td>Cidade: ".utf8_encode($row['cidade'])."</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan='2'>Apresentação: {$row['apresentacao']}</td>
                            <td>Natureza: {$row['natureza']}</td>
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
                            <td colspan='2'>Marca: {$row['marca']}</td>
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
                        </tr>
                        <tr>
                            <td colspan='4'>================================================================================================================================</td>
                        </tr>";
            }
            $html .= "</table>";
        }

        $html = addCard(['titulo'=>'Relatório', 'conteudo'=>$html]);

        return $html;
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
                </form>';

        $html = addCard(['titulo'=>'Upload', 'conteudo'=>$html]);


        if(!empty($_FILES)) {
            $arquivo = $_FILES['arquivo'];
        //    var_dump($arquivo);
        
            $nome = explode('.', $arquivo['name']);
            if(!file_exists($this->_local.$nome[0].'.txt')) {
                if(move_uploaded_file($arquivo['tmp_name'], $this->_local.$arquivo['name']) ) {
                    $this->pdfToText($this->_local.$arquivo['name'], $this->_local.$nome[0].'.txt');
                } else {
                    redireciona(getLink() . 'index&erro=true');
                }
            }

            $meu_pdf = nl2br(file_get_contents($this->_local.$nome[0].'.txt'));

            $revista = $this->buscaTexto2('MARCAS   -  RPI ', $meu_pdf);
            $revista = explode(' ', $revista);

            $this->_numero = $revista[0];
            $this->_data = date('Y-m-d', strtotime(str_replace('/', '-', $revista[3])));
            
            $processos = $this->getProcessos($meu_pdf);
            // echo $processos[1] . "<br>\n"; // . " - " . $processos[6] 
            // return $html;
            $this->gravaBanco($processos);

            addPortalMensagem("PDF enviado e processado com sucesso.");
            // redireciona(getLink()."avisos&mensagem=PDF enviado e processado com sucesso.&redireciona=upload");
        }
        return $html;
    }

    private function gravaBanco($processos) {
        set_time_limit(0);

        if(is_array($processos) && count($processos) > 0) {
            foreach($processos as $processo) {
                $num_processo = substr($processo, 7, 12);
                if(preg_match("/^[0-9]{12}$/", $num_processo)) {
                    $num_processo = $this->buscaTexto2('Processo afetado:  ', $processo);
                    $num_processo = (empty($num_processo)) ? $this->buscaTexto2('Processo  afetado:  ', $processo) : $num_processo;
                    $num_processo = substr($num_processo, 0, 9);

                    $is_processo = false;
                } else {
                    $num_processo = substr($processo, 7, 9);

                    $is_processo = true;
                }

                if(is_numeric($num_processo)) {
                    $inf = $this->getInf2($processo, $num_processo, $is_processo);

                    if(!empty($inf)) {
                        // echo " Deve salvar no banco <br>\n";
                        // print_r($processo);
                        // print_r($inf);
                        $sql = "INSERT INTO preImportacaoPdf (revista, codigoprocesso, marca, despacho, inpi, titular, natureza, detalhe_despacho, data)
                        VALUES ({$this->_numero}, $num_processo, '{$inf['marca']}', '{$inf['despacho']}', '{$inf['inpi']}', '{$inf['titular']}', '{$inf['natureza']}', '{$inf['detalhe_despacho']}', '{$this->_data}')";
                        query2($sql);
                    } else {
                        // echo " Sem registro <br>\n";
                    }
                }
            }
        }
    }

    private function getInf2($processo, $num_processo, $is_processo) {
        $ret = [];

        $sql = "SELECT natureza.descricao FROM marpaprocesso AS processo
                    LEFT JOIN marpanatureza AS natureza USING(codigonatureza)
                WHERE processo.codigoprocesso = $num_processo";
        $row = query2($sql);

        if(is_array($row) && count($row) > 0) {
            if($is_processo) {
                $ret['marca'] = $this->buscaTexto2('Elemento nominativo:', $processo);

                $ini = 18;
            } else {
                $ret['marca'] = $this->buscaTexto2($num_processo, $processo);

                $ini = 39;
            }

            // $ini = ($tipo == 'processo') ? 18 : 39;
            $i = strpos($processo, '<br />', $ini) - $ini;
            $ret['despacho'] = substr($processo, $ini, $i);
    
            // Número da classe vinda do PDF
            $inpi = $this->buscaTexto2('NCL(', $processo);
            $ret['inpi'] = (empty($inpi)) ? $this->buscaTexto2('Classe nacional:', $processo) : substr($inpi, 5);

            $titular = $this->buscaTexto2('Titular do registro:', $processo);
            $ret['titular'] = (empty($titular)) ? $this->buscaTexto2('Titular:', $processo) : $titular;

            $natureza = $this->buscaTexto2('Natureza:', $processo);
            $ret['natureza'] = (empty($natureza)) ? $row[0]['descricao'] : $natureza;
    
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
        $output=null;
        $retval=null;
        $comando = "/usr/bin/env python3 /var/www/python/parser_pdf.py $caminho $destino";
        log::gravaLog('comandos_python', $comando);
        exec($comando, $output, $retval);
    }
    
    //Pega array onde cada item inicia no código de um processo
    private function getProcessos($texto, $num=200, $ini=0) {
        // echo substr($texto, 10000, 50000);
        set_time_limit(0);

        //Faz um pattern-matching nos identificadores e devolve array com todos os processos
        $ret = [];
        $temp=[];
        if(!empty($texto)){
            preg_match_all("/<br \/>\\n\\s?[0-9]{9} |<br \/>\\n\\s?[0-9]{12} /", $texto,$temp);
            // preg_match_all("/\b[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]\s/i", $texto,$temp);
            // echo 'temp2: ' . count($temp2[0]) . "<br>\n";
            // if($ini!=0){
                //     $temp=array_slice($temp[$ini],0,$ini+$num+1,true);
                // } else{
                    //     $temp=array_slice($temp[$ini],1,$ini+$num+1,true);
                    // }
            $temp = $temp[0];
            // echo 'temp: ' . count($temp) . "<br>\n";
            // $temp=array_slice($temp, 18000, $num, true);
            foreach($temp as $proc) {
                // $ini = strpos($texto, $proc);
                // $inicio_linha = (trim(substr($texto, $ini-8, 7)) == '<br />') ? true : false;
                // $is_processo = (substr($texto, $ini+9, 2) == '  ') ? true : false;
                // $is_protocolo = (preg_match("/^[0-9]{12}$/", substr($texto, $ini, 12)) && substr($texto, $ini+12, 2) == '  ') ? true : false;
                
                // if($inicio_linha && ($is_processo || $is_protocolo)) { // Verifica se o número não está no meio de um texto
                    // echo substr($proc, 7, 9) . "<br>\n";
                    $texto=str_replace($proc, '@@@'.$proc, $texto);
                // }
            }
            // $temp = $temp[0];
            // $limite = 500;
            // while($limite<900) {
            //     $texto=str_replace($temp[$limite], '@@@'.$temp[$limite], $texto);

            //     $limite++;
            // }
            // $ret = array_slice(explode('@@@', $texto),1,$num,true);
            $ret = explode('@@@', $texto);
          //  var_dump($ret);
          //  die();
          
            
            /*
            $limite = 0;
            while($limite<900){
                $limite++;
            }
            $ret=$temp;
                
                $ini = strpos($texto, $temp[$limite]);
                $inicio_linha = (trim(substr($texto, $ini-8, 7)) == '<br />') ? true : false;
                $is_processo = (substr($texto, $ini+9, 2) == '  ') ? true : false;
                $is_protocolo = (preg_match("/^[0-9]{12}$/", substr($texto, $ini, 12)) && substr($texto, $ini+12, 2) == '  ') ? true : false;

                if($inicio_linha && ($is_processo || $is_protocolo)) { // Verifica se o número não está no meio de um texto
                    $texto=str_replace($temp[$limite], '@@@'.$temp[$limite], $texto);
                }
                $limite++;
            }
            $ret = explode('@@@', $texto);*/
        }
        return $ret;
    }
    
    private function getDadoProcesso($processo)
    //Retorna os dados do processo como uma string para exibir na tela
    {        
       // $padrao = "MARCAS - RPI 2731 de 09/05/2023"; -> limpa padrão de cabeçalho
       // $padrao='/MARCAS\\s-\\sRPI\\s[0-9]+\\sde\\s([A-Za-z0-9]+(/[A-Za-z0-9]+)+)\\s[0-9]+/u';
      //  preg_replace($padrao, '', $processo);
        
        //separa linhas
        $linhas = explode('<br />',$processo);
      //  var_dump($linhas);
      //  die();
      if(is_array($linhas) && count($linhas) > 0)
      {
            $texto = $linhas[0];
            for($i=1;$i<count($linhas);$i++)
            {
                if(count(explode(':',$linhas[$i])) > 1){
                    $texto.="<br>";
                } 
                $texto .= $linhas[$i];

            } 
            $texto .= "<br>==================================================================================================<br><br>";

      }
        return $texto;        
        
        /*
        if(is_string($processo) && !empty($processo))
        {
            $linhas = explode('<br>',$processo);
            //linha zero
            $ret['num_processo'] = substr($linhas[0],0,9);
            $ret['tipo'] = substr($linhas[0],9);
            //linha 1
            $linha = explode(':',$linhas[1]);
            $ret['titular'] = $linha[1];

            $linha = explode(':',$linhas[2]);
            $ret['data_dep'] = $linha[1];

            $linha = explode(':',$linhas[3]);
            $ret['data_inpi'] = $linha[1];

            $linha = explode(':',$linhas[4]);
            $ret['nro_inscricao'] = $linha[1];

            $linha = explode(':',$linhas[5]);
            $ret['apresentacao'] = $linha[1];

            $linha = explode(':',$linhas[6]);
            $ret['natureza'] = $linha[1];

            $linha = explode(':',$linhas[7]);
            $ret['cfe'] = $linha[1];

            $linha = explode(':',$linhas[8]);
            $ret['ncl'] = $linha[1];

            $linha = explode(':',$linhas[9]);
            $ret['especificacao'] = $linha[1];
            
            $linha = explode(':',$linhas[10]);
            $ret['especificacao_br'] = $linha[1];
        }*/
    }
    
    private function getDadosProc2($processo)
    //Retorna os dados do processo como array, índices tal texto (lowercase): 
    //titular, procurador, detalhes do despacho, data de depósito, prioridade unionista, apresentação etc.
    {
        $ret=[];
        $linhas = explode('<br />',$processo);
        if(is_array($linhas) && count($linhas) > 0)
        {
            foreach($linhas as $lin)
            {
                $temp = explode(':',$lin);
                if(is_array($temp) && count($temp) > 1)
                {
                    $indice = strtolower(trim($temp[0]));
                    $indice = str_replace(' ', '_', $indice);
                    $indice = str_replace('ç', 'c', $indice);
                    $indice = str_replace('â', 'a', $indice);
                    $indice = str_replace('ã', 'a', $indice);
                    $indice = str_replace('á', 'a', $indice);
                    $indice = str_replace('õ', 'o', $indice);
                    $indice = str_replace('ó', 'o', $indice);
                    $indice = str_replace('é', 'e', $indice);                 
                    
                    
                    
                    $aux[$indice] = $temp[1];
                    if(count($temp) > 2){
                        $i = 2;
                        while(is_array($temp[$i])){
                            $i++;
                            $aux[$indice] .= $temp[$i];
                        }
                    }
                    $ret = $aux;
                }
            }
            $ret['num_processo'] =  substr($linhas[0],0,9);
            $ret['tipo_processo'] = substr($linhas[0],9);
        }
        return $ret;
    }
    
    private function getDados($processos)
    {
        $html = '';
        if(is_array($processos) && count($processos) > 0)
            {
                foreach($processos as $processo)
                {
                //    $processo = $this->getDadoProcesso($processo);                    
                //    $html .= $processo;
                    $dados=$this->getDadosProc2($processo);
                    echo "DADOS PROCESSO: ";
                    var_dump($dados);
                    echo "<br>";
                        
                    
                /*    $html .= "<tr>
                                    <td>Processo: ".$dados['num_processo']."</td>
                                    <td>Tipo processo: {$dados['tipo']}</td>
                                </tr>
                                <tr>
                                    <td>Apresentação: {$dados['apresentacao']}</td>
                                    <td>Natureza: {$dados['natureza']}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Depósito: {$dados['data_dep']}
                                    INPI: {$dados['data_inpi']}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>NCL(11): {$dados['ncl']}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Titular: {$dados['titular']}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan='3'>{$dados['especificacao_br']}</td>
                                </tr>
                                <tr>
                                    <td colspan='3'>================================================================================================================================</td>
                                </tr>";*/
                }
            }
        return $html;
    }
  /*  
    //Busca no array de processos se existem processos do tipo indicado
    private function getDados($processos) {
        set_time_limit(120);
        $html = '';

        if(is_array($processos) && count($processos) > 0) {
            foreach($processos as $processo) {
                $num_processo = substr($processo, 0, 12);
                if(preg_match("/^[0-9]{12}$/", $num_processo)) {
                    $num_processo = $this->buscaTexto2('Processo afetado:  ', $processo);
                    $num_processo = (empty($num_processo)) ? $this->buscaTexto2('Processo  afetado:  ', $processo) : $num_processo;
                    $num_processo = substr($num_processo, 0, 9);

                    $marca = $this->buscaTexto2($num_processo, $processo);
                    $tipo_processo = 'protocolo';
                } else {
                    $num_processo = substr($processo, 0, 9);
                    $marca = $this->buscaTexto2('Elemento nominativo:', $processo);
                    $tipo_processo = 'processo';
                }

                if(is_numeric($num_processo)) {
                    $inf = $this->getInf($processo, $num_processo, $tipo_processo);

                    if(!empty($inf)) {
                        $html .= "<tr>
                                    <td>Processo: $num_processo</td>
                                    <td>Tipo processo: {$inf['tipo_processo']}</td>
                                    <td>Mot Canc: {$inf['mot_cancel']}</td>
                                </tr>
                                <tr>
                                    <td>Cliente: {$inf['empresa']}</td>
                                    <td></td>
                                    <td>Fone: ({$inf['prefixo']}) {$inf['telefone']}</td>
                                </tr>
                                <tr>
                                    <td>Vendedor: {$inf['codigovendedor']} - {$inf['vendedor']}</td>
                                    <td></td>
                                    <td>Pasta: {$inf['pasta']}</td>
                                </tr>
                                <tr>
                                    <td>Despacho: {$inf['despacho']}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Public. Tipo: {$inf['tipo_publicacao']}</td>
                                    <td>Cidade: ".utf8_encode($inf['cidade'])."</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Apresentação: {$inf['apresentacao']}</td>
                                    <td>Natureza: {$inf['natureza']}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Classe: {$inf['codigoclasse1']} {$inf['codigoclasse2']} {$inf['codigoclasse3']}                 
                                    INPI: {$inf['inpi']}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Marca: $marca</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Titular: {$inf['titular']}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan='3'>{$inf['detalhe_despacho']}</td>
                                </tr>
                                <tr>
                                    <td colspan='3'>================================================================================================================================</td>
                                </tr>";
                    }
                }
            }
        }

        return $html;
    }
*/
    private function getInf($processo, $num_processo, $tipo) {
        $ret = [];

        // ================= INFORMAÇÕES VINDAS DO BANCO ============================
        $sql = "SELECT processo.pasta, processo.codigoclasse1, processo.codigoclasse2, processo.codigoclasse3,
                    cliente.empresa, cliente.codigovendedor, cliente.prefixo, cliente.telefone, cliente.cidade,
                    vendedor.vendedor, tipo.descricao AS tipo_processo, cancel.descricao AS mot_cancel,
                    publica.descricao AS tipo_publicacao, apresentacao.descricao AS apresentacao,
                    natur.descricao AS natureza
                FROM marpaprocesso AS processo
                    LEFT JOIN marpacliente AS cliente USING(sigla)
                    LEFT JOIN marpavendedor AS vendedor USING(codigovendedor)
                    LEFT JOIN marpatipoprocesso AS tipo USING(codigotipoprocesso)
                    LEFT JOIN marpamotcancel AS cancel USING(codigomotcancel)
                    LEFT JOIN marpatipopublicacao AS publica USING(codigotipopublicacao)
                    LEFT JOIN marpaapresentacao AS apresentacao USING(codigoapresentacao)
                    LEFT JOIN marpanatureza AS natur USING(codigonatureza)
                WHERE
                    processo.codigoprocesso = $num_processo
                LIMIT 1";
        $row = query2($sql);

        if(is_array($row) && count($row) > 0) {
            $ret = $row[0];
    /*
            // ================= INFORMAÇÕES VINDAS DO PDF ============================
            $ini = ($tipo == 'processo') ? 11 : 32;
        //    if(strlen($processo)>$ini){
                $i = strpos($processo, '<br />', $ini) - $ini ;
                $ret['despacho'] = substr($processo, $ini, $i);
        
                // Número da classe vinda do PDF
                $inpi = $this->buscaTexto2('NCL(', $processo);
                $ret['inpi'] = (empty($inpi)) ? $this->buscaTexto2('Classe normal:', $processo) : substr($inpi, 5);
    
                $titular = $this->buscaTexto2('Titular do registro:', $processo);
                $ret['titular'] = (empty($titular)) ? $this->buscaTexto2('Titular:', $processo) : $titular;
    
                $natureza = $this->buscaTexto2('Natureza:', $processo);
                $ret['natureza'] = (empty($natureza)) ? $ret['natureza'] : $natureza;
        
                $detalhe_despacho = strpos($processo, 'Detalhes do despacho:') + 21;
                $detalhe_despacho = substr($processo, $detalhe_despacho, 400);
                $ret['detalhe_despacho'] = str_replace(['<br />', '\n'], '', $detalhe_despacho);
           //     }
         //   else {
         ///       $ret = '';
           // } */
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

    private function gravaArquivo($arquivo, $texto) {
        $caminho = $this->_path."$arquivo.vert";

        $arquivo = fopen($caminho, 'w');

        fwrite($arquivo, $texto); // escreve no arquivo

        fclose($arquivo); // fecha o arquivo
    }

}