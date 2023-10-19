<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_lp_piscofins {
    // Título
    private $_titulo;

    //CNPJ
    private $_cnpj;

    private $_programa;

    // Diretório
    private $_path;

    // PIS por período
    private $_pis = [];

    // COFINS por período
    private $_cofins = [];

    function __construct($cnpj, $contrato) {
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Soma PIS + COFINS';
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
		$this->_relatorio->addColuna(array('campo' => 'periodo' , 'etiqueta' => 'Período'   , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'pis'     , 'etiqueta' => 'PIS'       , 'tipo' => 'V', 'width' => 280, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cofins'  , 'etiqueta' => 'COFINS'    , 'tipo' => 'V', 'width' => 180, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'soma'    , 'etiqueta' => 'Soma'      , 'tipo' => 'V', 'width' => 180, 'posicao' => 'E'));
	}

    private function getDados() {
        $this->lerArquivo();

        foreach($this->_pis as $k => $v) {
            $param = [];
            $param['periodo'] = $k;
            $param['pis'] = $this->_pis[$k];
            $param['cofins'] = $this->_cofins[$k];
            $param['soma'] = $param['pis'] + $param['cofins'];
            $ret[] = $param;
        }

        return $ret;
    }

    private function lerArquivo() {
        $files = glob($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado.vert');

        foreach($files as $file) {
			$csv = fopen($file, "r");

            while(!feof($csv)) {
				$linha = fgets($csv);
                if(!empty($linha)) {
                    $linha = str_replace([
                        "\r\n",
                        "\n",
                        "\r"
                    ], '', $linha);
					$sep = explode('|', $linha);

                    if($sep[24] == 's') {
                        $per_mes = substr($sep[2], 3, 2);
                        $per_ano = substr($sep[2], 6, 4);

                        if(!isset($this->_pis[$per_ano.'/'.$per_mes])) {
                            $this->_pis[$per_ano.'/'.$per_mes] = 0;
                            $this->_cofins[$per_ano.'/'.$per_mes] = 0;
                        }

                        $this->_pis[$per_ano.'/'.$per_mes] += $sep[19];
                        $this->_cofins[$per_ano.'/'.$per_mes] += $sep[23];
                    }
                }
            }
        }

        // return $ret;
    }
}