<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_lp_cadastro {

    //CNPJ
    private $_cnpj;

    // Contrato do cliente
    private $_contrato;

    private $_programa;

    // Diretório
    private $_path;

    // NCMs ativos
    private $_ncm;

    // Itens
    private $_itens = [];

    // Colunas do arquivo resultado.vert
    private $_colunas = [];

    function __construct($cnpj, $contrato) {
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Cadastro de itens';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'] . 'lucro_presumido' . DIRECTORY_SEPARATOR . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
        $this->_cnpj = $cnpj;
        $this->_contrato = $contrato;

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

        $this->_colunas = [
            'bloco',
            'cnpj',
            'periodo',
            'tipo_op',
            'ind_emit',
            'chave_nf',
            'data_doc',
            'vlr_merc',
            'vlr_pis_c100',
            'vlr_cofins_c100',
            'cod_item',
            'desc_item',
            'tipo_item',
            'ncm',
            'tipo',
            'cfop',
            'cst_pis',
            'vlr_calc_pis',
            'aliq_pis',
            'vlr_pis',
            'cst_cofins',
            'vlr_calc_cofins',
            'aliq_cofins',
            'vlr_cofins',
            'selecionado'
        ];
	}

    public function index() {
        $ret = '';

		$dados = $this->getDados();
		$this->montaColunas();
        $this->_relatorio->setDados($dados[0]);

        $botao = [];
		$botao['texto'] = 'Excluir Itens Selecionados';
		$botao['cor'] = 'danger';
		$botao["onclick"] = "$('#formItens').submit();";
		$this->_relatorio->addBotao($botao);

        $botao_processa = [];
		$botao_processa['texto'] = 'Processar Novamente';
		$botao_processa["onclick"] = "setLocation('" . getLink() . "processar.processarNovamente&cnpj=" . $this->_cnpj . '|' . $this->_contrato . "')"; // $this->_cnpj . '_' . $this->_contrato
		$this->_relatorio->addBotao($botao_processa);

        $botaoCancela = [];
		$botaoCancela["onclick"] = "setLocation('" . getLink() . "index')";
		$botaoCancela["texto"] = "Retornar";
		$botaoCancela['cor'] = 'warning';
		$this->_relatorio->addBotao($botaoCancela);

		$ret .= $this->_relatorio;

        $param = [];
		$param['acao'] = getLink() . "cadastro.salvarItens&cnpj=" . $this->_cnpj . "|" . $this->_contrato;
		$param['id'] = 'formItens';
		$param['nome'] = 'formItens';

		$ret = formbase01::form($param, $ret);

		return $ret;
    }

    private function montaColunas() {
        $this->_relatorio->addColuna(array('campo' => 'sel', 'etiqueta' => '<span class="text-danger">Excluir<span>', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'cod_item', 'etiqueta' => 'Código', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'desc_item', 'etiqueta' => 'Descrição do item', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ncm', 'etiqueta' => 'NCM', 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
	}

    public function salvarItens() {
        $itens = $_POST['item'] ?? [];

        if(count($itens) > 0) {
            // Recupera todo o arquivo e marca todos os itens como "N" utilizados
			$dados = [];
			$resultado = [];
			$arquivo = $this->_path . 'arquivos/resultado.vert';
            if(is_file($arquivo)) {
                $arquivo = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                foreach ($arquivo as $linha) {
					$linha = explode('|', $linha);
					$temp = [];
					foreach ($this->_colunas as $key => $coluna) {
						$temp[$coluna] = $linha[$key];
					}
					$dados[] = $temp;
				}

                // Varre os dados e seta como SELECIONADO "S" os que estiverem no $_POST
				foreach ($dados as $key => $dado) {
					$id = $dado['cod_item'];
					if (isset($itens[$id])) {
						$dados[$key]['selecionado'] = 'N';
					}
				}

                $this->salvarResultado($dados);
            }
        }
    }

    private function salvarResultado($dados) {
		$arquivo = fopen($this->_path . 'arquivos/resultado.vert', 'w');
		foreach ($dados as $dado) {
			$temp = [];
			foreach($this->_colunas as $coluna) {
				$temp[] = $dado[$coluna];
			}
			$linha = implode('|', $temp);
			fwrite($arquivo, $linha . "\n");
		}
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

        $ret[] = $this->lerArquivo();

        return $ret;
    }

    private function lerArquivo() {
        $ret = [];
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
                        if(!isset($this->_itens[$sep[11]])) {
							$this->_itens[$sep[11]] = $sep[11];

                            $checked = '';
                            $dados = [];
                            $dados['sel'] = '<input name="item[' . $sep[10] . ']" type="checkbox" value="" ' . $checked . ' id="' . $sep[10] . '">';
                            $dados['cod_item'] = $sep[10];
                            $dados['desc_item'] = $sep[11];
                            $dados['ncm'] = $sep[13];
                            $dados['ativo'] = $sep[14];
                            $ret[] = $dados;
                        }
                    }
                }
            }
        }

        return $ret;
    }
}