<?php
/*
 * Data Criacao: 09/01/2023
 * Autor: Verticais - Rafael Postal
 *
 * Descricao: Leitura dos arquivos processados e com erro para relatório mensal dos valores brutos
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_arquivos {
    // diretório raiz
    private $_path;

    // CNPJ do cliente
    private $_cnpj;

    // Contrato do cliente
    private $_contrato;

    // ID do cliente
    private $_id;

    // Razão do cliente
    private $_titulo;

    // Programa
    private $_programa;

    // Classe relatório
    private $_relatorio;

    // Quantidade de arquivos processados separados por mês
    private $_processados = [];

    // Quantidade de arquivos com erro separados por mês
    private $_erros = [];

    public function __construct($cnpj, $contrato, $id){
        global $config;
        $this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;

        $this->_cnpj = $cnpj;
        $this->_contrato = $contrato;

        $this->_id = $id;

        $this->_programa = get_class($this);
        $this->getTitulo();

        $param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$this->_relatorio = new relatorio01($param);
    }

    public function index($email = false) {
        $ret = '';

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_relatorio->setDados($dados);

        if(!$email) {
            $botaoCancela = [];
            $botaoCancela["onclick"] = "setLocation('" . getLink() . "index')";
            $botaoCancela["texto"] = "Retornar";
            $botaoCancela['cor'] = 'warning';
            $this->_relatorio->addBotao($botaoCancela);
        }

        $ret .= $this->_relatorio;

        return $ret;
    }

    private function montaColunas() {
        $this->_relatorio->addColuna(array('campo' => 'referencia', 'etiqueta' => 'Referência', 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'processados', 'etiqueta' => 'Processados', 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'negados', 'etiqueta' => 'Negados', 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'valor', 'etiqueta' => 'Bruto', 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT * FROM mgt_monofasico_arquivos WHERE id_monofasico = $this->_id";
        $rows = queryMF($sql);

        if(is_array($rows) && count($rows) > 0) {
            $total_processados = 0;
            $total_negados = 0;
            $total_bruto = 0;
            foreach($rows as $row) {
                $temp = [];
                $temp['referencia']     = substr($row['data'], 4, 2).'/'.substr($row['data'], 0, 4);
                $temp['processados']    = $row['arq_autorizados'];
                $temp['negados']        = $row['arq_negados'];
                $temp['valor']          = number_format($row['valor_bruto'], 2, ',', '.');
                $ret[] = $temp;

                $total_processados += $row['arq_autorizados'];
                $total_negados += $row['arq_negados'];
                $total_bruto += $row['valor_bruto'];
            }

            $temp = [];
            $temp['referencia']     = '<b>Total</b>';
            $temp['processados']    = '<b>' . $total_processados . '</b>';
            $temp['negados']        = '<b>' . $total_negados . '</b>';
            $temp['valor']          = '<b>' . number_format($total_bruto, 2, ',', '.') . '</b>';
            $ret[] = $temp;
        }

        return $ret;



        // // Gera os valores que já foram processados
        // $this->getProcessados();

        // // Gera os valores dos arquivos com erro
        // $arquivos_xml = $this->getArquivos($this->_path . 'erro' . DIRECTORY_SEPARATOR, 'xml');
        // $arquivos_sped = $this->getArquivos($this->_path . 'erro' . DIRECTORY_SEPARATOR, 'txt');
        // if(count($arquivos_xml) > 0){
        //     foreach($arquivos_xml as $arquivo) {
        //         $this->getErrosXml($arquivo);
        //     }
        // }
        // if(count($arquivos_sped) > 0) {
        //     foreach($arquivos_sped as $arquivo) {
        //         $this->getErrosSped($arquivo);
        //     }
        // }
        
        // // Para que o proximo foreach tenha todas as chaves
        // if(count($this->_erros) > 0) {
        //     foreach($this->_erros as $k => $erro) {
        //         if(!isset($this->_processados[$k])) {
        //             $this->_processados[$k] = 0;
        //         }
        //     }
        // }

        // // armazena os resultados
        // if(count($this->_processados) > 0) {
        //     foreach($this->_processados as $k => $processado) {
        //         $erro = $this->_erros[$k] ?? 0;

        //         $temp = [];
        //         $temp['referencia'] = $k;
        //         $temp['processados'] = number_format($processado, 2, ',', '.');
        //         $temp['negados'] = number_format($erro, 2, ',', '.');
        //         $temp['total'] = number_format($processado + $erro, 2, ',', '.');
        //         $ret[] = $temp;
        //     }
        // }

        // return $ret;
    }

    private function getTitulo() {
		$sql = "SELECT razao FROM mgt_monofasico WHERE cnpj = '$this->_cnpj' AND contrato = '$this->_contrato'";
		$rows = queryMF($sql);

		if(!empty($rows)) {
			$this->_titulo = $rows[0]['razao'];
			return;
		} else {
			$this->_titulo = '';
			return;
		}
	}

//     private function getProcessados() {
//         $arquivo = $this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'C100.vert';

//         if(file_exists($arquivo)) {
//             $arquivo = file($arquivo);
//             foreach($arquivo as $linha) {
//                 $sep = explode('|', $linha);

//                 if($sep[0] == 'C100') {
//                     $mes_ano = substr($sep[5], 4, 2) .'/'.substr($sep[5], 0, 4);
//                     if(!isset($this->_processados[$mes_ano])) {
//                         $this->_processados[$mes_ano] = 0;
//                     }
//                     $this->_processados[$mes_ano] += $sep[6];
//                 }
//             }

//             // $files = glob($this->_path . $arquivo);
//             // print_r($files);

//             // if (count($files) > 0) {
//             //     foreach ($files as $file) {
//             //         $handle = fopen($file, "r");
//             //         if ($handle) {
//             //             while (!feof($handle)) {
//             //                 $linha = fgets($handle);
//             //                 if (!empty($linha)) {
//             //                     $sep = explode('|', $linha);
//             //                     if (count($sep) > 1) {
//             //                         $this->_mes_ano[$sep[6]] = $sep[7];
//             //                         print_r($sep);
//             //                     }
//             //                 }
//             //             }
//             //         }
//             //     }
//             // }
//         }

//         // print_r($this->processados);
//     }

//     private function getErrosXml($arquivo) {
//         $files = glob($this->_path . 'erro' . DIRECTORY_SEPARATOR . $arquivo);

//         if(count($files) > 0) {
// 			foreach ($files as $file) {
// 				$xml = simplexml_load_file($file);

//                 $dhEmi = $xml->NFe->infNFe->ide->dhEmi; 
//                 $dhEmi = str_replace('-', '', substr($dhEmi, 0, 10));
//                 $mes_ano = substr($dhEmi, 4, 2) .'/'.substr($dhEmi, 0, 4);

//                 if(!isset($this->_erros[$mes_ano])) {
//                     $this->_erros[$mes_ano] = 0;
//                 }
//                 $this->_erros[$mes_ano] += $xml->NFe->infNFe->total->ICMSTot->vNF . '';
//             }
//         }

//         // print_r($this->_erro);
//     }

//     private function getErrosSped($arquivo) {
//         set_time_limit(200);

//         $files = glob($this->_path . 'erro' . DIRECTORY_SEPARATOR . $arquivo);

//         if (count($files) > 0) {
//             foreach ($files as $file) {
//                 $handle = fopen($file, "r");
//                 if ($handle) {
//                     while (!feof($handle)) {
//                         $linha = fgets($handle);
//                         if (!empty($linha)) {
//                             $sep = explode('|', $linha);
//                             if (count($sep) > 1) {
//                                 if ($sep[1] == 'C100') {
//                                     $data = datas::dataD2S($sep[11], '', '');
//                                     $mes_ano = substr($data, 4, 2) .'/'.substr($data, 0, 4);

//                                     if(!isset($this->_erros[$mes_ano])){
//                                         $this->_erros[$mes_ano] = 0;
//                                     }
//                                     $this->_erros[$mes_ano] += str_replace(',', '.', $sep[12]);
//                                 }
//                             }
//                         }
//                     }
//                 }
//             }
//         }
//     }

//     private function getArquivos($dir, $tipo) {
//         $ret = [];
//         $diretorio = dir($dir);

//         while ($arquivo = $diretorio->read()) {
//             $ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
//             $ext = strtolower($ext);
//             if ($arquivo != '.' && $arquivo != '..' && $ext == $tipo) {
//                 $ret[] = $arquivo;
//             }
//         }

//         return $ret;
//   }
}