<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_lp_itens {
    //CNPJ
    private $_cnpj;

    private $_programa;

    // Diretório
    private $_path;

    function __construct($cnpj, $contrato) {
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Itens da Nota Fiscal';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'] . 'lucro_presumido' . DIRECTORY_SEPARATOR . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
        $this->_cnpj = $cnpj;

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Monofásico';
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
        $this->_relatorio->addColuna(array('campo' => 'arquivo'         , 'etiqueta' => 'Arquivo'            , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cnpj'            , 'etiqueta' => 'CNPJ'               , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'periodo'         , 'etiqueta' => 'Período'            , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'tipo_op'         , 'etiqueta' => 'Tipo Operação'      , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'ind_emit'        , 'etiqueta' => 'Indicador Emitente' , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'chave_nf'        , 'etiqueta' => 'Chave NF-e'         , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'data_doc'        , 'etiqueta' => 'Data Documento'     , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlr_merc'        , 'etiqueta' => 'Valor Mercadoria'   , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlr_pis_c100'    , 'etiqueta' => 'Valor PIS C100'     , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlr_cofins_c100' , 'etiqueta' => 'Valor COFINS C100'  , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cod_item'        , 'etiqueta' => 'Código Item'        , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'desc_item'       , 'etiqueta' => 'Descrição Item'     , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'tipo_item'       , 'etiqueta' => 'Tipo Item'          , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'ncm'             , 'etiqueta' => 'NCM'                , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'tipo'            , 'etiqueta' => 'Ativo'              , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cfop'            , 'etiqueta' => 'CFOP'               , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cst_pis'         , 'etiqueta' => 'CST PIS'            , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlr_calc_pis'    , 'etiqueta' => 'Base Cálculo PIS'   , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'aliq_pis'        , 'etiqueta' => 'Alíquota PIS'       , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlr_pis'         , 'etiqueta' => 'Valor PIS'          , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cst_cofins'      , 'etiqueta' => 'CST COFINS'         , 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlr_calc_cofins' , 'etiqueta' => 'Base Cálculo COFINS', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'aliq_cofins'     , 'etiqueta' => 'Alíquota COFINS'    , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlr_cofins'      , 'etiqueta' => 'Valor COFINS'       , 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $arquivos = $this->getArquivos($this->_path . 'arquivos');

        // foreach ($arquivos as $arquivo) {
		// 	if(strpos($arquivo, 'C100') !== false){ 
		// 		$ret = $this->lerArquivo($arquivo);
		// 	}
        // }

        if(count($arquivos) > 0) {
            $ret = $this->lerArquivo();
        }

        return $ret;
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
                        $dados = [];
                        $dados['arquivo'] = $sep[0];
                        $dados['cnpj'] = $sep[1];
                        $dados['periodo'] = $sep[2];
                        $dados['tipo_op'] = $sep[3];
                        $dados['ind_emit'] = $sep[4];
                        $dados['chave_nf'] = $sep[5];
                        $dados['data_doc'] = $sep[6];
                        $dados['vlr_merc'] = $sep[7];
                        $dados['vlr_pis_c100'] = $sep[8];
                        $dados['vlr_cofins_c100'] = $sep[9];
                        $dados['cod_item'] = $sep[10];
                        $dados['desc_item'] = $sep[11];
                        $dados['tipo_item'] = $sep[12];
                        $dados['ncm'] = $sep[13];
                        $dados['tipo'] = $sep[14];
                        $dados['cfop'] = $sep[15];
                        $dados['cst_pis'] = $sep[16];
                        $dados['vlr_calc_pis'] = $sep[17];
                        $dados['aliq_pis'] = $sep[18];
                        $dados['vlr_pis'] = $sep[19];
                        $dados['cst_cofins'] = $sep[20];
                        $dados['vlr_calc_cofins'] = $sep[21];
                        $dados['aliq_cofins'] = $sep[22];
                        $dados['vlr_cofins'] = $sep[23];
                        $ret[] = $dados;
                    }
                }
            }
        }

        return $ret;
    }
}