<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_lp_processa {
    //CNPJ
    private $_cnpj;

    private $_programa;

    // Diretório
    private $_path;

	// NCM dos itens
	private $_ncm = [];

	// Itens da nota
	private $_itens = [];

    function __construct($cnpj, $contrato) {
		global $config;
		conectaERP();
		conectaMF();
		
		$this->_titulo = 'Itens da Nota Fiscal';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'] . 'lucro_presumido' . DIRECTORY_SEPARATOR . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
        $this->_cnpj = $cnpj;
		
		$sql = "SELECT ncm FROM mgt_ncm WHERE ativo = 'S'";
		$rows = query($sql);
        foreach($rows as $row) {
			$this->_ncm[$row['ncm']] = $row['ncm'];
        }
	}

    public function getInformacoes() {
        $arquivos = $this->getArquivos($this->_path . 'arquivosExcel');

		foreach($arquivos as $arquivo) {
			$filename = str_replace('.xlsx', '.csv', $arquivo);

			if(strpos($arquivo, '.xlsx')) {
				
				$base_python = '/usr/bin/env python3 ';
				$arquivo_python = '/var/www/python/goodbye.py ';
				$arquivo_csv = $this->_path . 'arquivosExcel' . DIRECTORY_SEPARATOR . $filename;
				$arquivo_excel = $this->_path . 'arquivosExcel' . DIRECTORY_SEPARATOR . $arquivo;
				
				$resultado = true;
				$comando = $base_python . $arquivo_python . $arquivo_excel . ' ' . $arquivo_csv;
				exec($comando, $resultado);
				if($resultado > 0){
					$base_python = 'python ';
					$comando = $base_python . $arquivo_python . $arquivo_excel . ' ' . $arquivo_csv;
					exec($comando);
				}

				unlink($arquivo_excel);
			}

			$this->leituraArquivo($filename);
			// $ret = array_merge($ret, funcao);
			// rename($this->_path . $arquivo, $this->_path . 'processados' . DIRECTORY_SEPARATOR . $arquivo);
		}
    }

    private function getArquivos($dir) {
        $ret = [];
		$diretorio = dir($dir);

		while($arquivo = $diretorio->read()) {
			$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
			$ext = strtolower($ext);
			if($arquivo != '.' && $arquivo != '..' && $ext == 'csv' || $ext == 'xlsx') {
				$ret[] = $arquivo;
			}
		}
		return $ret;
    }

    private function leituraArquivo($arquivo) {
		$files = glob($this->_path . 'arquivosExcel' . DIRECTORY_SEPARATOR . $arquivo);
		$param = [];

		foreach($files as $file) {
			$filename = pathinfo($file, PATHINFO_BASENAME);
			$csv = fopen($file, "r");
			while(!feof($csv)) {
				$linha = fgets($csv);
				if(!empty($linha)) {
					$linha = str_replace([
                        "\r\n",
                        "\n",
                        "\r"
                    ], '', $linha);
					$sep = explode(';', $linha);

					if(substr($sep[0], 0, 7) == substr($this->_cnpj, 0, 7)) {
						if(strpos($filename, 'C400') !== false) {
							if($sep[16] == 01) { // só roda se o CST for 01
								if(isset($this->_ncm[$sep[15]])) {
									$dados = [];
									$dados['bloco'] 			= 'C400';
									$dados['cnpj'] 				= $sep[0];
									$dados['periodo'] 			= $sep[1];
									$dados['tipo_op'] 			= '';
									$dados['ind_emit'] 			= '';
									$dados['chave_nf'] 			= '';
									$dados['data_doc'] 			= '';
									$dados['vlr_merc'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[17]));
									$dados['vlr_pis_c100'] 		= 0;
									$dados['vlr_cofins_c100'] 	= 0;
									$dados['cod_item'] 			= $sep[12];
									$dados['desc_item'] 		= $sep[13];
									$dados['tipo_item'] 		= '';
									$dados['ncm'] 				= $sep[15];
									$dados['tipo'] 				= 'monofasico'; // isset($this->_ncm[$dados['ncm']]) ? 'monofasico' : 'tributado';
									$dados['cfop'] 				= '';
									$dados['cst_pis'] 			= $sep[16]; // CST PIS e COFINS
									$dados['vlr_calc_pis'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[18]));
									$dados['aliq_pis'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[19]));
									$dados['vlr_pis'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[22]));
									$dados['cst_cofins'] 		= $dados['cst_pis'];
									$dados['vlr_calc_cofins'] 	= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[24]));
									$dados['aliq_cofins'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[25]));
									$dados['vlr_cofins'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[28]));
									$dados['selecionado'] 		= 's';
									$param[] = $dados;
								}
							}
						} else if(strpos($filename, 'C100') !== false) {
							if($sep[71] == 01) {
								if(isset($this->_ncm[$sep[46]])) {
									$dados = [];
									$dados['bloco']				= 'C100';
									$dados['cnpj'] 				= $sep[0];
									$dados['periodo'] 			= $sep[1];
									$dados['tipo_op'] 			= $sep[2];
									$dados['ind_emit'] 			= $sep[3];
									$dados['chave_nf'] 			= $sep[19];
									$dados['data_doc'] 			= $sep[20];
									$dados['vlr_merc'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[26]));
									$dados['vlr_pis_c100'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[36]));
									$dados['vlr_cofins_c100'] 	= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[37]));
									$dados['cod_item'] 			= $sep[41];
									$dados['desc_item'] 		= $sep[42];
									$dados['tipo_item'] 		= $sep[44];
									$dados['ncm'] 				= $sep[46];
									$dados['tipo'] 				= isset($this->_ncm[$dados['ncm']]) ? 'monofasico' : 'tributado';
									$dados['cfop'] 				= $sep[52];
									$dados['cst_pis'] 			= $sep[71]; // a partir daqui
									$dados['vlr_calc_pis'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[72]));
									$dados['aliq_pis'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[73]));
									$dados['vlr_pis'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[76]));
									$dados['cst_cofins'] 		= $sep[77];
									$dados['vlr_calc_cofins'] 	= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[78]));
									$dados['aliq_cofins'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[79]));
									$dados['vlr_cofins'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[82]));
									$dados['selecionado'] = 's';
									$param[] = $dados;
								}
							}
						} else if(strpos($filename, 'C860') !== false) {
							if($sep[15] == 01) {
								if(isset($this->_ncm[$sep[10]])) {
									$dados = [];
									$dados['bloco'] 			= 'C860';
									$dados['cnpj'] 				= $sep[0];
									$dados['periodo'] 			= $sep[1];
									$dados['tipo_op'] 			= '';
									$dados['ind_emit'] 			= '';
									$dados['chave_nf'] 			= '';
									$dados['data_doc'] 			= $sep[4];
									$dados['vlr_merc'] 			= 0;
									$dados['vlr_pis_c100'] 		= 0;
									$dados['vlr_cofins_c100'] 	= 0;
									$dados['cod_item'] 			= $sep[7];
									$dados['desc_item'] 		= $sep[8];
									$dados['tipo_item'] 		= '';
									$dados['ncm'] 				= $sep[10];
									$dados['tipo'] 				= isset($this->_ncm[$dados['ncm']]) ? 'monofasico' : 'tributado';
									$dados['cfop'] 				= $sep[11];
									$dados['cst_pis'] 			= $sep[15]; // CST PIS e COFINS
									$dados['vlr_calc_pis'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[16]));
									$dados['aliq_pis'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[17]));
									$dados['vlr_pis'] 			= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[18]));
									$dados['cst_cofins'] 		= $dados['cst_pis'];
									$dados['vlr_calc_cofins'] 	= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[19]));
									$dados['aliq_cofins'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[20]));
									$dados['vlr_cofins'] 		= str_replace([',', ' '], ['.', ''], str_replace('.', '', $sep[21]));
									$dados['selecionado'] 		= 's';
									$param[] = $dados;
								}
							}
						}
					}
				}
			}
		}

		$this->gravaArquivo($param);
    }

	private function gravaArquivo($dados) {
		if (empty($dados)) {
			return;
		}

		$file = fopen($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado.vert', "a");

		foreach ($dados as $dado) {
			fwrite($file, implode('|', $dado) . "\n");
		}

		fclose($file);
	}
}