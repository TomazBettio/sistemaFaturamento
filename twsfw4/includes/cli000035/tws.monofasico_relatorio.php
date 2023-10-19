<?php

/*
 * Data Criacao:
 * Autor:
 *
 * Descricao:
 *
 * Altera��es:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_relatorio {

    // CNPJ cliente
    private $_cnpj;

    // pasta com os arquivos
    private $_path;

    // Classe relatorio
	private $_relatorio01;
    // Classe relatorio
	private $_relatorio02;

	// Nome do programa
	private $_programa;

    // Titulo
	private $_titulo;

    // Razão social do cliente
    private $_razao;

    public function __construct($cnpj, $contrato) {
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Relatório';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
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
		$param['titulo'] = 'Resumo geral de produtos adquiridos';
		$this->_relatorio01 = new relatorio01($param);

        $param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = 'Resumo proporcional de crédito - Fabricante';
		$this->_relatorio02 = new relatorio01($param);

        $param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = 'Resumo proporcional de crédito - Distribuidor';
		$this->_relatorio03 = new relatorio01($param);

        $param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = 'Resumo de crédito a compensar';
		$this->_relatorio04 = new relatorio01($param);
	}

    public function index() {
        $ret = '';

        $dados = [];
		$dados = $this->getDados('geral');
		$this->montaColunas($this->_relatorio01);
		$this->_relatorio01->setDados($dados);

        $botaoCancela = [];
		$botaoCancela["onclick"] = "setLocation('" . getLink() . "index')";
		$botaoCancela["texto"] = "Retornar";
		$botaoCancela['cor'] = 'warning';
		$this->_relatorio01->addBotao($botaoCancela);
		$ret .= $this->_relatorio01;


        $dados = [];
        $dados = $this->getDados('fabricante');
		$this->montaColunas($this->_relatorio02);
		$this->_relatorio02->setDados($dados);
        $ret .= $this->_relatorio02;


        $dados = [];
        $dados = $this->getDados('distribuidor');
		$this->montaColunas($this->_relatorio03);
		$this->_relatorio03->setDados($dados);
        $ret .= $this->_relatorio03;


        $dados = [];
        $dados = $this->getDados('compensar');
		$this->montaColunas($this->_relatorio04);
		$this->_relatorio03->setDados($dados);
        $ret .= $this->_relatorio04;

		return $ret;
    }
    
    private function montaColunas($relatorio) {
        $relatorio->addColuna(array('campo' => 'data_emi',        'etiqueta' => 'Período',             'tipo' => 'T','width' => 80,'posicao' => 'C'));
        $relatorio->addColuna(array('campo' => 'razao',           'etiqueta' => 'Empresa',             'tipo' => 'T','width' => 80,'posicao' => 'C'));
		$relatorio->addColuna(array('campo' => 'bruto',           'etiqueta' => 'Valor bruto',         'tipo' => 'V','width' => 80,'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'total_pis',    'etiqueta' => 'Valor total PIS',     'tipo' => 'V','width' => 80,'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'total_cofins',    'etiqueta' => 'Valor total COFINS',  'tipo' => 'V','width' => 80,'posicao' => 'C'));
		$relatorio->addColuna(array('campo' => 'liquido',         'etiqueta' => 'Valor líquido',       'tipo' => 'V','width' => 100,'posicao' => 'E'));
    }

    private function getDados($tipo) {
        $ret = [];

		$files = glob($this->_path . 'arquivos/resumoCompliance.vert');

		if (count($files) > 0) {
			$handle = fopen($files[0], "r");
			if($handle) {
				while(!feof($handle)) {
					$linha = fgets($handle);
					if(!empty($linha)) {
						$sep = explode('|', $linha);
                        $this->getRazao();
						if(count($sep) > 1) {
                            $mes = substr($sep[0], 4, 2);
                            $ano = substr($sep[0], 0, 4);
                            $data_emi = $mes . '/' . $ano;
                            if ($tipo == 'geral') {
                                $param = [];
                                $param['data_emi'] = $data_emi;
                                $param['razao'] = $this->_razao;
                                $param['bruto'] = $sep[1];
                                $param['total_pis'] = $sep[2] + $sep[6];
                                $param['total_cofins'] = $sep[3] + $sep[7];
                                $param['liquido'] = $param['total_pis'] + $param['total_cofins'];
                                $ret[] = $param;
                            } else if ($tipo == 'fabricante') {
                                $param = [];
                                $param['data_emi'] = $data_emi;
                                $param['razao'] = $this->_razao;
                                $param['bruto'] = $sep[1];
                                $param['total_pis'] = $sep[4];
                                $param['total_cofins'] = $sep[5];
                                $param['liquido'] = $param['total_pis'] + $param['total_cofins'];
                                $ret[] = $param;
                            } else if ($tipo == 'distribuidor') {
                                $param = [];
                                $param['data_emi'] = $data_emi;
                                $param['razao'] = $this->_razao;
                                $param['bruto'] = $sep[1];
                                $param['total_pis'] = $sep[8];
                                $param['total_cofins'] = $sep[9];
                                $param['liquido'] = $param['total_pis'] + $param['total_cofins'];
                                $ret[] = $param;
                            } else if ($tipo == 'compensar') {
                                $param = [];
                                $param['data_emi'] = $data_emi;
                                $param['razao'] = $this->_razao;
                                $param['bruto'] = $sep[1];
                                $param['total_pis'] = $sep[4] + $sep[8];
                                $param['total_cofins'] = $sep[5] + $sep[9];
                                $param['liquido'] = $param['total_pis'] + $param['total_cofins'];
                                $ret[] = $param;
                            }
						}
					}
				}
			}
		}

        return $ret;
    }

    private function getRazao() {
		$file = glob($this->_path . 'arquivos/0000.vert');

		if (count($file) > 0) {
			$handle = fopen($file[0], "r");
			if($handle) {
				while(!feof($handle)) {
					$linha = fgets($handle);
					$linha = str_replace(["\r\n","\n","\r"], '', $linha);
					if(!empty($linha)) {
						$sep = explode('|', $linha);
						$this->_razao = $sep[2];
						return true;
					}
				}
			}
		}
	}

}