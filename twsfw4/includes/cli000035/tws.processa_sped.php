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

  public function __construct($cnpj, $contrato, $trace = false)
  {
    global $config;
    $this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
    // $this->_path = $config['pathProcessaMonofasico'];
    $this->_trace = $trace;
    $this->_resumo = true;

    $this->_cnpj = $cnpj;

    $this->_file_exists = $this->existeArquivos('txt');

    return;
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
      rename($this->_path . $arquivo, $this->_path . 'processados_sped' . DIRECTORY_SEPARATOR . $arquivo);
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
        redireciona(getLink() . 'avisos&mensagem=Existem C175 nos seguintes arquivos (' . $mensagem . ')&tipo=erro');
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
        foreach ($ret[$anoMes] as $key => $arquivos) {
          if (strpos(strtoupper($arquivo), 'ORIGINAL')) {
            unlink($this->_path . $arquivo);

            unset($ret[$anoMes][$key]);
          }

          if (count($ret[$anoMes]) > 1) {
            rsort($ret[$anoMes]);

            $excluir = false;

            foreach ($ret[$anoMes] as $key => $arquivo) {
              if ($excluir) {
                unlink($this->_path . $arquivo);

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
                      }
                    }
                  }
                  if ($sep[1] == '0200') {
                    $param = [];
                    $temp = [];
                    $temp['bloco'] = $sep[1];
                    $temp['cod_item'] = $sep[2];
                    $temp['nome_produto'] = $sep[3];
                    $temp['cod_ncm'] = $sep[8];
                    $param[] = $temp;
                    $this->gravaNotaBloco($param);
                  }
                  //não pode ser chave vazia e o cst pis[25] e cst cofins[31] tem que ser 73 - que é entrada monofasica
                  if ($sep[1] == 'C170' && !empty($chave_nf) && (empty($sep[30]) || ($sep[30] == round(0, 2)))) {

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

  private function moveArquivoErro($path, $filename)
  {
    rename($path, $this->_path . 'erro' . DIRECTORY_SEPARATOR . $filename);
  }

  private function controlador($num_doc, $data, $nome)
  {
    $file = fopen($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . '0000.vert', "a");

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

    $files = glob($this->_path . 'arquivos' .  DIRECTORY_SEPARATOR . '0000.vert');

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

  /**
   * Verifica se o arquivo de resumo existe
   *
   * @param string $nome - Nome do arquivo (sem extensão)
   * @return boolean
   */
  // private function verificaResumo($nome)
  // {
  //   $ret = true;
  //   $arq = $this->_path . DIRECTORY_SEPARATOR . $nome . '.txt';

  //   if (file_exists($arq)) {
  //     $ret = false;
  //   }

  //   return $ret;
  // }

  // private function gravaResumo($arquivo)
  // {
  //   $dados = [];
  //   if (empty($arquivo)) {
  //     return;
  //   }

  //   ksort($this->_dados);

  //   $file = fopen($this->_path . DIRECTORY_SEPARATOR . $arquivo . '.txt', "w");
  //   if (count($this->_dados) == 0) {
  //     echo "Arquivo $arquivo sem dados!<br>\n";
  //     return;
  //   }

  //   foreach ($this->_dados as $dado) {
  //     fwrite($file, implode('|', $dado) . "\n");
  //   }

  //   fclose($file);
  // }

  private function gravaNota($dados, $cnpj)
  {
    if (empty($dados)) {
      return;
    }

    $file = fopen($this->_path . 'arquivo_sped' . DIRECTORY_SEPARATOR . 'sped.vert', "a");

    foreach ($dados as $dado) {
      // fwrite($file, implode('|', $dado) . "\n");
      //write a json file
      // fwrite($file, json_encode($dado) . "\n");
      fwrite($file, implode('|', $dado) . "\n");
    }

    fclose($file);
  }

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

    $file = fopen($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . $arquivo . '.vert', "a");

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


  // private function recuperaResumo($arquivo)
  // {
  //   $dados = [];
  //   if (empty($arquivo)) {
  //     return;
  //   }

  //   $handle = fopen($this->_path . DIRECTORY_SEPARATOR . $arquivo . '.txt', "r");
  //   if ($handle) {
  //     while (!feof($handle)) {
  //       $linha = fgets($handle);
  //       if (strlen(trim($linha)) > 0) {
  //         $dados[] = str_replace("\n", '', $linha);
  //       }
  //     }
  //     fclose($handle);
  //   } else {
  //     addPortalMensagem("Arquivo $arquivo - recuperaArquivo - não encontrado", 'danger');
  //   }

  //   foreach ($dados as $dado) {
  //     $dado = explode('|', $dado);

  //     $temp = [];
  //     $temp['data']   = $dado[0];
  //     $temp['nota']  = $dado[1];
  //     $temp['vl']    = $dado[2];
  //     $temp['pis']  = $dado[3];
  //     $temp['cof']  = $dado[4];
  //     $temp['bloco']   = $dado[5];
  //     $temp['seq']  = isset($dado[6]) ? $dado[6] : '';
  //     $temp['prod']  = isset($dado[7]) ? $dado[7] : '';

  //     $this->_dados[] = $temp;
  //   }
  // }
}
