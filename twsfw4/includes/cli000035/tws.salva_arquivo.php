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

class salva_arquivo {


  private $_path;

    public function __construct($trace = false) {
        global $config;
        $this->_path = $config['pathUpdMonofasico'];
        // $this->_path = $config['pathProcessaMonofasico'];
        $this->_trace = $trace;
        $this->_resumo = true;

        return;
    }

    public function enviarBanco($cnpj) {
        $arquivos = $this->getArquivos($this->_path . $cnpj . DIRECTORY_SEPARATOR . 'arquivos');
        foreach($arquivos as $arquivo) {
          $this->salvarArquivo($cnpj, $arquivo);
        }
      }
    
      private function salvarArquivo($cnpj, $arquivo) {
        $ret = [];
    
        $files = glob($this->_path . $cnpj . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . $arquivo);

        if(count($files) > 0) {
          foreach ($files as $file) {
            $handle = fopen($file, "r");
            if ($handle) {
              while (!feof($handle)) {
                $linha = fgets($handle);
                $sep = explode('|', $linha);
                if (count($sep) > 0) {
                  $valid = false;
                  
                  // print_r($sep);
                  // ----------------------------------------INI Processo especifico ------------------------------------------------------------------
                  $temp = [];
                  if ($sep[0] == '0140') {
                    $temp = [];
                    $temp['cod_part'] = $sep[1];
                    $temp['razao_social'] = $sep[2];
                    $temp['cnpj'] = $sep[3];

                    // $sql = "SELECT * FROM mgt_monofasico WHERE id_part = " . $temp['cod_part'];
                    // $row = queryMF($sql);
                    // if(count($row) == 0) {
                      // $sql = montaSQL($temp, 'mgt_monofasico');
                      // queryMF($sql);
                    // }   // conferir a forma em que será integrado ao banco
                  }
                  if ($sep[0] == '0150') {
                    $temp = [];
                    $temp['cod_part'] = $sep[1];
                    $temp['razao_social'] = $sep[2];
                    $temp['cnpj'] = $sep[3];

                    // $sql = "SELECT * FROM mgt_fornecedor WHERE cod_part = " . $temp['cod_part'];
                    // $row = queryMF($sql);
                    // if(count($row) == 0) {
                    //   $sql = montaSQL($temp, 'mgt_fornecedor');
                    //   queryMF($sql);
                    // }          // tá OK
                  }
                  
                  if ($sep[0] == 'C100') {
                    $temp = [];
                    $temp['ind_oper'] = $sep[1];
                    $temp['cod_part'] = $sep[2]; //
                    $temp['chv_nfe'] = $sep[3];
                    $temp['dt_doc'] = $sep[4];
                    $temp['vl_doc'] = $sep[5];

                    // $sql = "SELECT * FROM mgt_nfentrada WHERE chv_nfe = " . $temp['chv_nfe'];
                    // $item = queryMF($sql);

                    // $sql = "SELECT * FROM mgt_monofasico WHERE cod_part = " . $temp['cod_part'];
                    // $mon = queryMF($sql);

                    // $sql = "SELECT * FROM mgt_fornecedor WHERE cod_part = " . $temp['cod_part'];
                    // $forn = queryMF($sql);

                    // if(count($item) == 0) {
                    //   $sql = montaSQL($temp, 'mgt_nfentrada');
                    //   queryMF($sql);
                    // }   // ok, está rodando com os dois arquivos
                  }
                  if ($sep[0] == '0200') {
                    $temp['cod_item'] = $sep[1];
                    $temp['descr_item'] = $sep[2];
                    $temp['cod_ncm'] = $sep[3];

                    // $sql = "SELECT * FROM mgt_nfentrada_itens WHERE cod_item = " . $temp['cod_item'];
                    // $row = queryMF($sql);

                    // $sql = montaSQL($temp, 'mgt_produto'); // NÃO RODAR COM SPED
                    // queryMF($sql); // VAI SALVAR MAIS DE 7 MIL ITENS NO BANCO
                  }
                  if ($sep[0] == 'C170') {
                      $temp['num_item'] = $sep[1];
                      $temp['cod_item'] = $sep[2];
                      $temp['vl_item'] = $sep[3];
                      $temp['vl_desc'] = $sep[4];
                      $temp['cfop'] = $sep[5];
                      $temp['cst_pis_cofins'] = $sep[6];
                      $temp['aliq_pis'] = $sep[7];
                      $temp['aliq_cofins'] = $sep[8];
                      $temp['chv_nfe'] = $sep[9];

                      // $sql = "SELECT * FROM mgt_nfentrada_itens WHERE cod_item = " . $temp['cod_item'];
                      // $row = queryMF($sql);

                      // if(count($row) == 0) {
                      //   $sql = montaSQL($temp, 'mgt_nfentrada_itens');
                      //   queryMF($sql);  //  SALVA QUASE 4 MIL ITENS NO BANCO COM SPED
                      // } // ok, está rodando com os dois arquivos
                    }
                    
                  // if(count($temp) > 0) {
                  //   $ret[] = $temp;
                  // }
    
                  // ----------------------------------------INI Processo especifico ------------------------------------------------------------------
    
                }
              }
              fclose($handle);
            } else {
              echo "Erro ao abrir o arquivo $arquivo <br>\n";
              // return false;
            }
          }
        }
        // return $ret;
      }

      private function getArquivos($dir) {
        $ret = [];
        $diretorio = dir($dir);

        while ($arquivo = $diretorio->read()) {
          $ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
          $ext = strtolower($ext);
          if ($arquivo != '.' && $arquivo != '..' && $ext == 'vert') {
            $ret[] = $arquivo;
          }
        }

        return $ret;
      }
}