<?php
/*
 * Data Criacao: 07/10/2022
 * Autor: Verticais - Luís Costa
 *
 * Descricao: Processa o sped e joga para um arquivo intermediário
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class processa_sped
{

  //Anos
  private $_anos = [];

  //Path
  private $_path;
  
  //Path raiz do cliente-contrato
  private $_path_raiz;
  

  //Dados
  private $_dados = [];

  //Indica se controla resumo (TXT com as informações já extraidas)
  private $_resumo;

  //Nome do arquivo resumo
  private $_arqResumo;

  //Bloco a ser pesquisado
  private $_bloco;

  //Blocos ativos (devem ser pesquisados)
  private $_blocosAtivos;

  //debug
  private $_trace;

  //Existe arquivo
  private $_file_exists;

  //cnpj
  private $_cnpj;

  // ID cliente
  private $_id;

  //Indica se está rodando por schedule
  private $_schedule;
  
  public function __construct($cnpj, $contrato, $id, $arquivos_importados, $schedule = false,$trace = false)
  {
    global $config;
    
    if($arquivos_importados == 'N'){
    	$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR.'recebidos'.DIRECTORY_SEPARATOR;
    }else{
    	$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR.'zip'.DIRECTORY_SEPARATOR.'sped'.DIRECTORY_SEPARATOR;
    }
    $this->_path_raiz = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
    $this->_trace = $trace;
    $this->_resumo = true;

    $this->_cnpj = $cnpj;
    $this->_id = $id;

    $this->_file_exists = $this->existeArquivos('txt');

    $this->_schedule = $schedule;
  }

  public function getExisteArquivo()
  {
    return $this->_file_exists;
  }

  public function existeArquivos($tipo)
  {
    $ret = false;

    $files = glob($this->_path . "*." . $tipo);

    if (count($files) > 0) {
      //rename extension to lower
      foreach ($files as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        rename($file, $this->_path . pathinfo($file, PATHINFO_FILENAME) . '.' . $ext);
      }
    }
    $files = glob($this->_path . "*." . $tipo);

    if (count($files) > 0) {
      $ret = true;
    }
    return $ret;
  }

  public function getDados()
  {
    return $this->_dados;
  }

  public function getInformacoes($cnpj)
  {
    // mantem somente os últimos retificados
    $this->limparArquivos();
    $arquivos = $this->getArquivos($this->_path);

    // if(!file_exists($this->_path . $cnpj)) {
    if ($this->_trace) {
      print_r($arquivos);
    } else {
      // die("Não constam arquivos de " . $arquivos . " no diretorio, favor verificar!");
    }

    $C175 = [];
    foreach ($arquivos as $arquivo) {
      $C175[] = $this->leituraArquivo($cnpj, $arquivo);
      if(is_file($this->_path . $arquivo)){
      	rename($this->_path . $arquivo, $this->_path_raiz . 'processados_sped' . DIRECTORY_SEPARATOR . $arquivo);
      }
      // $this->excluiNota($arquivo, $cnpj);
    }

    if (count($C175) > 0) {
      $mensagem = '';
      $erro = false;
      foreach ($C175 as $file) {
        if (!empty($file[0])) {
          $mensagem .= $file[0] . ', ';
          $erro = true;
        }
      }

      if ($erro) {
      	if(!$this->_schedule){
      		addPortalMensagem('Existem C175 nos seguintes arquivos (' . $mensagem . ')','error');
      		redireciona();
      	}
      }
    }

    //} //print_r($this->_planoContas);
  }


  //------------------------------------------------------------------- UTEIS ---------------------------
  private function limparArquivos()
  {
    $ret = [];
    $meses = [];
    $diretorio = dir($this->_path);

    while ($arquivo = $diretorio->read()) {
      $ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
      $ext = strtolower($ext);
      if ($arquivo != '.' && $arquivo != '..' && $ext == 'txt') {
        $anoMes = substr($arquivo, 10, 6);
        $meses[$anoMes] = $anoMes;
        $ret[$anoMes][] = $arquivo;
      }
    }

    foreach ($meses as $anoMes) {
      if (count($ret[$anoMes]) > 1) {
        foreach ($ret[$anoMes] as $key => $arquivo) {
          if (strpos(strtoupper($arquivo), 'ORIGINAL')) {
          	if(is_file($this->_path . $arquivo)){
          		unlink($this->_path . $arquivo);
          	}

            unset($ret[$anoMes][$key]);
          }

          if (count($ret[$anoMes]) > 1) {
            rsort($ret[$anoMes]);

            $excluir = false;

            foreach ($ret[$anoMes] as $key => $arquivo) {
              if ($excluir) {
              	if(is_file($this->_path . $arquivo)){
              		unlink($this->_path . $arquivo);
              	}

                unset($ret[$anoMes][$key]);
              } else {
                $excluir = true;
              }
            }
          }
        }
      }
    }
  }

  private function getArquivos($dir)
  {
    $ret = [];
    $diretorio = dir($dir);

    while ($arquivo = $diretorio->read()) {
      $ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
      $ext = strtolower($ext);
      if ($arquivo != '.' && $arquivo != '..' && $ext == 'txt') {
        $ret[] = $arquivo;
      }
    }

    return $ret;
  }


  private function leituraArquivo($cnpj, $arquivo)
  {
    set_time_limit(200);
    $ret = [];

    $files = glob($this->_path . $arquivo);

    if (count($files) > 0) {
      foreach ($files as $file) {
        $C175 = false;
        $continua = true;
        $C100_anterior = [];
        $handle = fopen($file, "r");
        if ($handle) {
          while (!feof($handle)) {
            $linha = fgets($handle);
            if (!empty($linha)) {
              $sep = explode('|', $linha);
              if (count($sep) > 1) {

                if ($sep[1] == '0000') {
                  if (substr($sep[9], 0, 7) != substr($cnpj, 0, 7)) {
                    $continua = false;

                    $filename = pathinfo($file, PATHINFO_BASENAME);
                    $this->moveArquivoErro($file, $filename);
                  }

                  if ($this->verificador($sep[9], $sep[6])) {
                    $this->controlador($sep[9], $sep[6], $sep[8]);
                    $anoMes = substr($sep[6], 4, 4) . substr($sep[6], 2, 2);
                  } else {
                    $continua = false;
                  }
                }

                if ($continua) {
                  // print_r($sep);
                  // ----------------------------------------INI Processo especifico ------------------------------------------------------------------
                  if ($sep[1] == '0140') {
                    $param = [];
                    $temp = [];
                    $temp['bloco'] = $sep[1];
                    $temp['cod_part'] = $sep[2];
                    $temp['nome_cliente'] = str_replace("'", "", utf8_encode($sep[3]));
                    $temp['cnpj'] = $sep[4];
                    $param[] = $temp;
                    $this->gravaNotaBloco($param);
                  }
                  if ($sep[1] == '0150') {
                    $param = [];
                    $temp = [];
                    $temp['bloco'] = $sep[1];
                    $temp['cod_part'] = $sep[2];
                    $temp['nome_cliente'] = str_replace("'", "", utf8_encode($sep[3]));
                    $temp['cnpj'] = $sep[5];
                    $param[] = $temp;
                    $this->gravaNotaBloco($param);
                  }
                  if ($sep[1] == 'C010') {
                    $temp_filial = $sep[2];
                  }

                  if ($sep[1] == 'C100') {

                    $C100_anterior = $sep;
                    $chave_nf = trim($sep[9]);
                    if ($sep[2] == '0' && $sep[6] = '00' && $sep[7] <> '890' && !empty($chave_nf)) {

                      if ($sep[4] != '') {
                        $param = [];
                        $temp = [];
                        $temp['bloco'] = $sep[1];
                        $temp['tipo_nf'] = $sep[2];
                        $temp['cod_part'] = $sep[4];
                        $temp['num_doc'] = $sep[8]; //numero da nota
                        $temp['chv_nfe'] = $chave_nf; //chave da nota
                        $temp['data_emissao'] = datas::dataD2S($sep[11], '', '');
                        // $temp['data_saida'] = datas::dataS2D($sep[11]);
                        $temp['total_bruto'] = str_replace(',', '.', $sep[12]);
                        $temp['filial'] = $temp_filial;
                        $param[] = $temp;
                        $this->gravaNotaBloco($param);
                        $this->setPermissoesBanco(substr($temp['data_emissao'], 0, 6));
                      }
                    }
                  }
                  if ($sep[1] == '0200') {
                    $param = [];
                    $temp = [];
                    $temp['bloco'] = $sep[1];
                    $temp['cod_item'] = $sep[2];
                    $temp['nome_produto'] = str_replace(";", '', $sep[3]);
                    $temp['cod_ncm'] = $sep[8];
                    $param[] = $temp;
                    $this->gravaNotaBloco($param);
                  }
                  //não pode ser chave vazia e o cst pis[25] e cst cofins[31] tem que ser 73 - que é entrada monofasica
                  //essa verificação diz que se o valor do pis for 0, então é uma entrada monofasica - pois não tomou crédito
                  if ($sep[1] == 'C170' && !empty($chave_nf) && (empty($sep[30]) || ($sep[30] == '0,00'))) {

                    //sep 30 zerado
                    // && (empty($sep[30]) || ($sep[30] == 0))

                    //cst 73
                    // && $sep[25] == '73' && $sep[31] == '73'
                    if (isset($C100_anterior[2]) && $C100_anterior[2] == '0') {
                      $param = [];
                      $temp = [];
                      $temp['bloco'] = $sep[1];
                      $temp['num_item'] = $sep[2];
                      $temp['cod_item'] = $sep[3];
                      $temp['qtd'] = $sep[5];
                      $temp['vlr_total'] = str_replace(',', '.', $sep[7]);
                      $temp['vlr_desc'] = str_replace(',', '.', $sep[8]);
                      $temp['cfop'] = $sep[11];
                      $temp['cst'] = $sep[25];
                      $aliq_pis = !empty($sep[27]) ? $sep[27] : 0;
                      $aliq_cofins = !empty($sep[33]) ? $sep[33] : 0;
                      $temp['aliq_pis'] = str_replace(',', '.', $aliq_pis);
                      $temp['aliq_cofins'] = str_replace(',', '.', $aliq_cofins);
                      $temp['chv_nfe'] = $chave_nf;
                      
                      $temp['vl_pis'] = $sep[30];
                      $temp['vl_cofins'] = $sep[36];
                      
                      $param[] = $temp;
                      $this->gravaNotaBloco($param);
                    }
                  }

                  if ($sep[1] == 'C175') {
                    $C175 = basename($file);
                  }
                }

                // if(count($temp) > 0) {
                //   $ret[] = $temp;
                // }

                // ----------------------------------------INI Processo especifico ------------------------------------------------------------------

              }
            }
          }
          fclose($handle);
        } else {
          echo "Erro ao abrir o arquivo $arquivo <br>\n";
        }
        if (isset($C175)) {
          $ret[] = $C175;
        }
      }
    }

    return $ret;
  }

  private function setPermissoesBanco($data)
  {
    $sql = "SELECT * FROM mgt_monofasico_arquivos WHERE id_monofasico = $this->_id AND data = $data";
    $row = queryMF($sql);

    if (is_array($row) && count($row) > 0 && $row[0]['alterar'] == 'N') {
      $sql = "UPDATE mgt_monofasico_arquivos SET alterar = 'S' WHERE id_monofasico = $this->_id AND data = $data";
      queryMF($sql);
    }
  }

  private function moveArquivoErro($path, $filename)
  {
  	rename($path, $this->_path_raiz . 'erro' . DIRECTORY_SEPARATOR . $filename);
  }

  private function controlador($num_doc, $data, $nome)
  {
  	$file = fopen($this->_path_raiz . 'arquivos' . DIRECTORY_SEPARATOR . '0000.vert', "a");

    $dados = [];
    $dados['num_doc'] = $num_doc;
    $dados['data'] = $data;
    $dados['nome'] = $nome;

    fwrite($file, implode('|', $dados) . "\n");

    fclose($file);
  }

  private function verificador($num_doc, $data)
  {
    $ret = true;

    $files = glob($this->_path_raiz . 'arquivos' .  DIRECTORY_SEPARATOR . '0000.vert');

    if (count($files) > 0) {
      $handle = fopen($files[0], "r");
      if ($handle) {
        while (!feof($handle)) {
          $linha = fgets($handle);
          if (!empty($linha)) {
            $sep = explode('|', $linha);
            if ($sep[0] == $num_doc && $sep[1] == $data) {
              $ret = false;
            }
          }
        }
      }
    }
    return $ret;
  }

  //--------------------------------------------------------------- RESUMO ----------------------------------

  private function gravaNotaBloco($dados)
  {
    if (empty($dados)) {
      return;
    }

    if ($dados[0]['bloco'] == 'C170') {
      $arquivo = 'C100';
    } else {
      $arquivo = $dados[0]['bloco'];
    }

    $file = fopen($this->_path_raiz . 'arquivos' . DIRECTORY_SEPARATOR . $arquivo . '.vert', "a");

    foreach ($dados as $dado) {
      // fwrite($file, implode('|', $dado) . "\n");
      //write a json file
      // fwrite($file, json_encode($dado) . "\n");
      fwrite($file, implode('|', $dado) . "\n");
      // print_r($dado);
      // echo "<br>";
    }

    fclose($file);
  }

  private function excluiNota($arquivo, $cnpj)
  {
    $file = $this->_path . $arquivo;
    unlink($file);
  }

}
