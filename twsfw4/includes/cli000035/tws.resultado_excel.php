<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class resultado_excel
{

  private $_path;

  private $_excelArquivo;

  private $_csvArquivo;

  private $_colunas;

  private $_cnpj;


  //Indices de ws excel
  private $_indicesWS = [];

  public function __construct($cnpj, $contrato)
  {
    global $config;

    $this->_colunas = [
      'chv_nf',
      'fornecedor',
      'num_doc',
      'data_emi',
      'descr_item',
      'ncm',
      'cfop',
      'ind_oper',
      'num_item',
      'itens_nota',
      'vl_item',
      'vl_desc',
      'vl_base',
      'aliq_pis',
      'aliq_cofins',
      'vl_final_pis',
      'vl_final_cofins',
      'vl_calc_final_pis',
      'vl_calc_final_cofins',
      'selecionado',
      'qtd',
      'cod_item',
      'filial',
      'cnpj_forn'
    ];

    $this->_cnpj = $cnpj;

    $this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;

    $this->_csvArquivo = 'arquivos/resultado' . $contrato . '.csv';
  }

  public function setPlanilhaResultado()
  {
    $ret = [];

    $files = glob($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado.vert');

    if (count($files) > 0) {
      foreach ($files as $file) {
        $handle = fopen($file, "r");
        while (!feof($handle)) {
          $linha = fgets($handle);
          $linha = str_replace([
            "\r\n",
            "\n",
            "\r",
          	";"
          ], '', $linha);
          if (!empty($linha)) {
            $sep = explode('|', $linha);
            if (count($sep) > 1) {
              $temp = [];
              $temp['chv_nf']               = "'" . $sep[0];
              $temp['fornecedor']           = $sep[1];
              $temp['num_doc']              = "'" . $sep[2];
              $temp['data_emi']             = $sep[3];
              $temp['descr_item']           = $sep[4];
              $temp['ncm']                  = $sep[5];
              $temp['cfop']                 = $sep[6];
              $temp['ind_oper']             = $sep[7];
              $temp['num_item']             = $sep[8];
              $temp['itens_nota']           = "'" . $sep[9];
              $temp['vl_item']              = $sep[10];
              $temp['vl_desc']              = $sep[11];
              $temp['vl_base']              = $sep[12];
              $temp['aliq_pis']             = $sep[13];
              $temp['aliq_cofins']          = $sep[14];
              $temp['vl_final_pis']         = round($sep[15], 2);
              $temp['vl_final_cofins']      = round($sep[16], 2);
              $temp['vl_calc_final_pis']    = round($sep[17], 2);
              $temp['vl_calc_final_cofins'] = round($sep[18], 2);
              $temp['selecionado']          = $sep[19];
              $temp['qtd']                  = $sep[20];
              $temp['cod_item']             = "'" . $sep[21];
              $temp['filial']               = "'" . $sep[22];
              $temp['cnpj_forn']            = "'" . $sep[23];

              $ret[] = $temp;
            }
          }
        }
      }
    }

    ksort($ret);
    // print_r($ret);
    $this->geraCSV($ret);
  }

  private function geraCSV($dados)
  {
    $cab = [];
    $campos = [];
    $tipos = [];

    $cab[] = 'Chave de acesso';
    $campos[] = 'chv_nf';
    $tipos[] = 'T';

    $cab[] = 'Fornecedor';
    $campos[] = 'fornecedor';
    $tipos[] = 'T';

    $cab[] = 'Nota Fiscal';
    $campos[] = 'num_doc';
    $tipos[] = 'T';

    $cab[] = 'Data Nota';
    $campos[] = 'data_emi';
    $tipos[] = 'D';

    $cab[] = 'Nome Produto';
    $campos[] = 'descr_item';
    $tipos[] = 'T';

    $cab[] = 'NCM';
    $campos[] = 'ncm';
    $tipos[] = 'T';

    $cab[] = 'CFOP';
    $campos[] = 'cfop';
    $tipos[] = 'T';

    $cab[] = 'operacao';
    $campos[] = 'ind_oper';
    $tipos[] = 'T';

    $cab[] = 'N Item';
    $campos[] = 'num_item';
    $tipos[] = 'T';

    $cab[] = 'itens_nota';
    $campos[] = 'itens_nota';
    $tipos[] = 'T';

    $cab[] = 'Valor item';
    $campos[] = 'vl_item';
    $tipos[] = 'V';

    $cab[] = 'Desconto';
    $campos[] = 'vl_desc';
    $tipos[] = 'V';

    $cab[] = 'Valor Base';
    $campos[] = 'vl_base';
    $tipos[] = 'V';

    $cab[] = 'Aliq_pis';
    $campos[] = 'aliq_pis';
    $tipos[] = 'V';

    $cab[] = 'Aliq_Cofins';
    $campos[] = 'aliq_cofins';
    $tipos[] = 'V';

    $cab[] = 'Valor Final PIS';
    $campos[] = 'vl_final_pis';
    $tipos[] = 'V';

    $cab[] = 'Valor Final COFINS';
    $campos[] = 'vl_final_cofins';
    $tipos[] = 'V';

    $cab[] = 'PIS a recuperar';
    $campos[] = 'vl_calc_final_pis';
    $tipos[] = 'V';

    $cab[] = 'COFINS a recuperar';
    $campos[] = 'vl_calc_final_cofins';
    $tipos[] = 'V';

    $cab[] = 'Selecionado';
    $campos[] = 'selecionado';
    $tipos[] = 'T';

    $cab[] = 'Quantidade';
    $campos[] = 'qtd';
    $tipos[] = 'N';

    $cab[] = 'Codigo do item';
    $campos[] = 'cod_item';
    $tipos[] = 'T';

    $cab[] = 'Filial';
    $campos[] = 'filial';
    $tipos[] = 'T';

    $cab[] = 'Cnpj Fornecedor';
    $campos[] = 'cnpj_forn';
    $tipos[] = 'T';

    $csv = new insumos_excel($this->_path, $dados, $campos, $cab, $tipos);
    $csv->grava($this->_csvArquivo);
  }
}
