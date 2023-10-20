<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_excel_item {

    //CNPJ
    private $_cnpj;

    private $_programa;

    // Diretório
    private $_path;

    // NCMs ativos
    private $_ncm;

    // Itens
    private $_itens = [];

    function __construct($cnpj) {
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Memória de calculo';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'];
        $this->_cnpj = $cnpj;

        $sql = "SELECT ncm FROM mgt_ncm WHERE ativo = 'S'";
		$rows = query($sql);
        foreach($rows as $row) {
            $this->_ncm[$row['ncm']] = $row['ncm'];
        }

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

		$dados = $this->getDados();
		$this->montaColunas();
		$this->_relatorio->setDados($dados);

        $botaoCancela = [];
		$botaoCancela["onclick"] = "setLocation('" . getLink() . "index')";
		$botaoCancela["texto"] = "Retornar";
		$botaoCancela['cor'] = 'warning';
		$this->_relatorio->addBotao($botaoCancela);

		$ret .= $this->_relatorio;

		return $ret;
    }

    private function montaColunas() {
		$this->_relatorio->addColuna(array('campo' => 'desc_item', 'etiqueta' => 'Descrição do item', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ncm', 'etiqueta' => 'NCM', 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
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

    private function getDados() {
        $ret = [];

        $arquivos = $this->getArquivos($this->_path . $this->_cnpj . DIRECTORY_SEPARATOR . 'arquivosExcel');

        foreach ($arquivos as $arquivo) {
			if(strpos($arquivo, 'C400') !== false || strpos($arquivo, 'C100') !== false || strpos($arquivo, 'C860') !== false){
				$ret = $this->lerArquivo($arquivo);
			}
        }

		return $ret;
    }

    private function lerArquivo($arquivo) {
        // print_r($this->_ncm[0]);
        $files = glob($this->_path . $this->_cnpj . DIRECTORY_SEPARATOR . 'arquivosExcel' . DIRECTORY_SEPARATOR . $arquivo);

        foreach($files as $file) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
			$csv = fopen($file, "r");

            while(!feof($csv)) {
                $linha = fgets($csv);
				if(!empty($linha)) {
                    $sep = explode(';', $linha);

                    if(strpos($filename, 'C400') !== false) {
                        if($sep[16] == 01) {
                            if(!isset($this->_itens[$sep[13]])) {
                                $this->_itens[$sep[13]] = $sep[13];

                                $dados = [];
                                $dados['desc_item'] = $sep[13];
                                $dados['ncm'] = $sep[15];
                                if(isset($this->_ncm[$dados['ncm']])) {
                                    $dados['ativo'] = 'sim';
                                } else {
                                    $dados['ativo'] = 'não';
                                }
                                $ret[] = $dados;
                            }
                        }
                    } else if(strpos($filename, 'C100') !== false) {
                        if($sep[71] == 01) {
                            if(!isset($this->_itens[$sep[13]])) {
                                $this->_itens[$sep[13]] = $sep[13];

                                $dados = [];
                                $dados['desc_item'] = $sep[42];
                                $dados['ncm'] = $sep[46];
                                if(isset($this->_ncm[$dados['ncm']])) {
                                    $dados['ativo'] = 'sim';
                                } else {
                                    $dados['ativo'] = 'não';
                                }
                                $ret[] = $dados;
                            }
						}
                    } else if(strpos($filename, 'C860') !== false) {
                        if($sep[15] == 01) {
                            if(!isset($this->_itens[$sep[13]])) {
                                $this->_itens[$sep[13]] = $sep[13];

                                $dados = [];
                                $dados['desc_item'] = $sep[8];
                                $dados['ncm'] = $sep[10];
                                if(isset($this->_ncm[$dados['ncm']])) {
                                    $dados['ativo'] = 'sim';
                                } else {
                                    $dados['ativo'] = 'não';
                                }
                                $ret[] = $dados;
                            }
						}
                    }
                }
            }
        }

        // print_r($ret);
        return $ret;
    }
}