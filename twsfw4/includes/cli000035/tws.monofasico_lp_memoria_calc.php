<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_lp_memoria_calc {

    //CNPJ
    private $_cnpj;

    private $_programa;

    // Diretório
    private $_path;

    // Total de PIS por período
    private $_pis_per = [];

    function __construct($cnpj, $contrato) {
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Memória de calculo';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'] . 'lucro_presumido' . DIRECTORY_SEPARATOR . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
        $this->_cnpj = $cnpj;

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Monofásicos';
		$this->_tabela = new tabela01($param);

		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$this->_relatorio = new relatorio01($param);
	}

    public function index() {
        $ret = '';

		$dados = $this->getDadosRelatorio();
		$this->montaColunasRelatorio();
		$this->_relatorio->setDados($dados);

        $botaoCancela = [];
		$botaoCancela["onclick"] = "setLocation('" . getLink() . "index')";
		$botaoCancela["texto"] = "Retornar";
		$botaoCancela['cor'] = 'warning';
		$this->_relatorio->addBotao($botaoCancela);

		$ret .= $this->_relatorio;

		return $ret;
    }

    private function montaColunasRelatorio() {
		$this->_relatorio->addColuna(array('campo' => 'periodo', 'etiqueta' => 'Periodo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'pis_periodo', 'etiqueta' => 'Base de cálculo PIS', 'tipo' => 'V', 'width' => 280, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'base', 'etiqueta' => 'Base M600', 'tipo' => 'V', 'width' => 180, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'comparacao', 'etiqueta' => 'Comparação', 'tipo' => 'V', 'width' => 180, 'posicao' => 'E'));
	}

    private function getArquivos($dir) {
		$ret = [];
		$diretorio = dir($dir);

		while ($arquivo = $diretorio->read()) {
			$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
			$ext = strtolower($ext);
			if ($arquivo != '.' && $arquivo != '..') {
				$ret[] = $arquivo;
			}
		}
		return $ret;
	}

	private function getDadosRelatorio() {
        $ret = [];

        $arquivos = $this->getArquivos($this->_path . 'arquivosExcel');

		$pis_per_comp = [];

        foreach ($arquivos as $arquivo) {
			if(strpos($arquivo, 'C400') !== false || strpos($arquivo, 'C100') !== false || strpos($arquivo, 'C860') !== false){ 
				$this->lerGeral($arquivo);
			} else if(strpos($arquivo, 'M600') !== false) {
				$pis_per_comp = $this->lerM600($arquivo);
			}
        }

        if(!empty($this->_pis_per) && !empty($pis_per_comp)) {
            foreach($pis_per_comp[0] as $k => $v) {
                // echo $k . "<br> \n";
                if(isset($this->_pis_per[$k])) {
                    $param = [];
                    $param['periodo'] = $k;
                    $param['pis_periodo'] = round($this->_pis_per[$k], 2);
                    $param['base'] = round($pis_per_comp[0][$k], 2);

                    if($param['pis_periodo'] == $param['base']) {
                        $param['comparacao'] = 'sim';
                    } else {
                        $param['comparacao'] = 'não';
                    }
                    $ret[] = $param;
                }
            }
        }

		return $ret;
    }

	private function lerGeral($arquivo) {
		$files = glob($this->_path . 'arquivosExcel' . DIRECTORY_SEPARATOR . $arquivo);
		
		foreach($files as $file) {
			$filename = pathinfo($file, PATHINFO_BASENAME);
			$csv = fopen($file, "r");

			while(!feof($csv)) {
				$linha = fgets($csv);
				if(!empty($linha)) {
					$sep = explode(';', $linha);

					if(strpos($filename, 'C400') !== false) {
						if($sep[16] == 01) { // só roda se o CST for 01
							$per_mes = substr($sep[1], 3, 2);
							$per_ano = substr($sep[1], 6, 4);
							
							$dados = [];
							$dados['periodo'] = $sep[1];
                            $dados['base_calc_pis'] = str_replace(' ', '', $sep[18]);
							$C400[] = $dados;

                            if(!isset($this->_pis_per[$per_ano.'/'.$per_mes])) {
                                $this->_pis_per[$per_ano.'/'.$per_mes] = 0;
                            }
							
							$this->_pis_per[$per_ano.'/'.$per_mes] += $dados['base_calc_pis'];
						}
					} else if(strpos($filename, 'C100') !== false) {
						if($sep[71] == 01) {
							$per_mes = substr($sep[1], 3, 2);
							$per_ano = substr($sep[1], 6, 4);
							
							$dados = [];
							$dados['periodo'] = $sep[1];
							$dados['desc_item'] = $sep[42];
							$dados['ncm'] = $sep[46];
                            $dados['base_calc_pis'] = str_replace(' ', '', $sep[72]);
							$C100[] = $dados;

                            if(!isset($this->_pis_per[$per_ano.'/'.$per_mes])) {
                                $this->_pis_per[$per_ano.'/'.$per_mes] = 0;
                            }
	
							$this->_pis_per[$per_ano.'/'.$per_mes] += $dados['base_calc_pis'];
						}
					} else if(strpos($filename, 'C860') !== false) {
						if($sep[15] == 01) {
							$per_mes = substr($sep[1], 3, 2);
							$per_ano = substr($sep[1], 6, 4);
							
							$dados = [];
							$dados['periodo'] = $sep[1];
							$dados['desc_item'] = $sep[8];
							$dados['ncm'] = $sep[10];
                            $dados['base_calc_pis'] = str_replace(' ', '', $sep[16]);
							$C100[] = $dados;

                            if(!isset($this->_pis_per[$per_ano.'/'.$per_mes])) {
                                $this->_pis_per[$per_ano.'/'.$per_mes] = 0;
                            }
	
							$this->_pis_per[$per_ano.'/'.$per_mes] += $dados['base_calc_pis'];
						}
					}
				}
			}
		}

		// print_r($pis_per);
		// print_r($C400);

		// print_r($pis_per);

		$ret[] = $this->_pis_per;

		return $ret;
	}

	private function lerM600($arquivo) {
		$ret = [];
		$files = glob($this->_path . 'arquivosExcel' . DIRECTORY_SEPARATOR . $arquivo);

		foreach($files as $file) {
			$csv = fopen($file, "r");

			$pis_per_comp = [];

			while(!feof($csv)) {
				$linha = fgets($csv);
				if(!empty($linha)) {
					$sep = explode(';', $linha);

					if($sep[0] != 'CNPJ') {
						$per_mes = substr($sep[1], 3, 2);
                        $per_ano = substr($sep[1], 6, 4);

						$dados = [];
						$dados['periodo'] = $sep[1];
                        $dados['base_calc_pis'] = str_replace(' ', '', $sep[16]);
						$C600[] = $dados;

                        if(!isset($pis_per_comp[$per_ano.'/'.$per_mes])) {
                            $pis_per_comp[$per_ano.'/'.$per_mes] = 0;
                        }
                        
						$pis_per_comp[$per_ano.'/'.$per_mes] += $dados['base_calc_pis'];

					}
					
				}
			}
		}

		// print_r($pis_per_comp);
		$ret[] = $pis_per_comp;

		return $ret;
	}

}