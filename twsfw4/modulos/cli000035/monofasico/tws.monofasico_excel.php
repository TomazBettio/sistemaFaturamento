<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_excel {

    var $funcoes_publicas = array(
        'index' 			=> true,
        'enviarArquivo'     => true,
        'upload'            => true,
		'memoriaCalculo'	=> true,
		'cadastro'			=> true,
		'itensNota'			=> true,
		'processar'			=> true,
		'avisos'			=> true,
		'pisCofins'			=> true,
    );

    //Titulo do relatorio
    private $_titulo;

    //Nome do programa
    private $_programa;

    //CNPJ do cliente
    private $_cnpj;

    private $_path;

    //Tabela
    private $_tabela;

	// Total de PIS em cada período (mês/ano)
	private $_pis_per;

    function __construct() {
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Modelo Monofásico';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'] . 'lucro_presumido' . DIRECTORY_SEPARATOR;

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Monofásicos - Lucro Presumido';
		$this->_tabela = new tabela01($param);

		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$this->_relatorio = new relatorio01($param);
	}

    public function index() {
        $ret = '';

		// =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

		// =========== COLUNAS A SEREM UTILIZADAS COM O $_GET ==================
		$get = [];
		$get['cnpj'] = 'CLICNPJ';
		$get['contrato'] = 'CTRNROCONTRATO';

		// =============== FUNÇÃO PARA INCLUIR O ARQUIVO ===============================
		$param = array(
			'texto' => 'Importar', //Texto no botão
			'link' => getLink() . 'enviarArquivo&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			// 'link' => '&razao=',
			// 'coluna' => 'CLINOMEFANTASIA',
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		// =============== FUNÇÃO PARA PROCESSAR OS ARQUIVOS ===============================
		$param = array(
			'texto' => 'processar', //Texto no botão
			'link' => getLink() . 'processar&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			// 'link' => '&razao=',
			// 'coluna' => 'CLINOMEFANTASIA',
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'Cálculo', //Texto no botão
			'link' => getLink() . 'memoriaCalculo&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 200, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'Cadastro', //Texto no botão
			'link' => getLink() . 'cadastro&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 200, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'Itens', //Texto no botão
			'link' => getLink() . 'itensNota&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 200, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'PIS+COFINS', //Texto no botão
			'link' => getLink() . 'pisCofins&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 200, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$ret .= $this->_tabela;

		return $ret;
    }

    private function montaColunas() {
		$this->_tabela->addColuna(array('campo' => 'CLICODIGO', 'etiqueta' => 'ID', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLINOMEFANTASIA', 'etiqueta' => 'Nome fantasia', 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLICNPJ', 'etiqueta' => 'CNPJ', 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
		// $this->_tabela->addColuna(array('campo' => 'CLIREGIMETRIBUTARIO', 'etiqueta' => 'Reg. Tributario', 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CTRDATAAINICIO', 'etiqueta' => 'Data CTR', 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CTRNROCONTRATO', 'etiqueta' => 'Nº Contrato', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
		// $this->_tabela->addColuna(array('campo' => 'status', 'etiqueta' => 'Status', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
	}

    private function getDados() {
		$ret = [];

		$sql = "SELECT C.CLICODIGO, 
		C.CLINOMEFANTASIA, 
		C.CLICNPJ, 
		C.CLIREGIMETRIBUTARIO,
		CTR.CTRDATAAINICIO,
		CTR.CTRNROCONTRATO,
		CASE
			WHEN CLIENQTRIBUTARIO = 'S' 
				THEN'Simples Nacional'
			WHEN CLIENQTRIBUTARIO = 'R' 
				THEN 'Lucro Real'
			WHEN CLIENQTRIBUTARIO = 'P' 
				THEN 'Lucro Presumido'
			ELSE 'Não Informado'
				END AS TRIBUTACAO 
		FROM 
		CONTRATOS CTR
		LEFT JOIN CLIENTES C ON CTR.CLICODIGO = C.CLICODIGO
		WHERE CTR.CTRDATAAINICIO IS NOT NULL  AND 
		CTR.CTRDATAAINICIO > '2022-01-01'
		ORDER BY CTR.CTRDATAAINICIO DESC";
		$rows = queryERP($sql);

		$contrato = [];
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				if ($row['CLICNPJ'] != null) {
					$temp = [];
					$temp['CLICODIGO']   			= $row['CLICODIGO'];
					$temp['CLINOMEFANTASIA'] 	= $row['CLINOMEFANTASIA'];
					$temp['CLICNPJ']   				= $row['CLICNPJ'];
					// $temp['TRIBUTACAO']  			= $row['TRIBUTACAO'];
					$temp['CTRDATAAINICIO']  	= datas::dataMS2D($row['CTRDATAAINICIO']);
					$temp['CTRNROCONTRATO']		= $row['CTRNROCONTRATO'];
					// $temp['CTRNMRCONTRATO']  		= $row['CTRNMRCONTRATO'];
					$ret[] = $temp;

					// $contrato[str_replace(['.', '/', '-'], '', $temp['CLICNPJ'])] = $temp['CTRNROCONTRATO'];
				}
			}
		}

		// putAppVar('contrato', $contrato);

		return $ret;
	}

	public function avisos()
	{
		$tipo = $_GET['tipo'] ?? '';
		if ($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->index();
	}

    public function enviarArquivo() {
		global $nl;
		$ret = '';

		//Cria a pasta com o arquivo

		$param = [];
		// $param['nome'] 	= 'upd_sped[]';
		$param['nome'] 	= 'upd_arquivo[]';
		$param['multi'] = true;
		$form = formbase01::formFile($param) . '<br><br>';

		$param = formbase01::formSendParametros();

		$param['texto'] = 'Enviar Arquivos';
		$form .= formbase01::formBotao($param);

		$param = array();
		$param['acao'] = getLink() . "upload&cnpj=" . $_GET['cnpj'];
		$param['nome'] = 'formUPD';
		$param['id']   = 'formUPD';
		$param['enctype'] = true;
		$form = formbase01::form($param, $form);

		$ret .= '<div class="row">' . $nl;
		$ret .= '	<div  class="col-md-4">' . '' . '</div>' . $nl;
		$ret .= '	<div  class="col-md-2"></div>' . $nl;
		$ret .= '	<div  class="col-md-5">' . $form . '</div>' . $nl;
		$ret .= '</div>' . $nl;

		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Upload Arquivos clientes';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
	}

    public function upload() {
		$ret = '';
		if (!isset($_FILES['upd_arquivo'])) {
			$ret = $this->index();
			return $ret;
		}
// print_r($_FILES);

		$files = $_FILES['upd_arquivo'];
		// print_r($files['error'][0]);

		if (count($files['name']) > 0 && $files['error'][0] == 0) {
			//Vai servir para identificao de diretorio unico de upload
// echo "<br> \n";
// print_r($files['name']);

			$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
			$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
			$contrato = str_replace('/', '-', $get[1]);

			$this->criaPasta($this->_path, $cnpj . '_' . $contrato);

			$cont = 0;
			foreach ($files['name'] as $key => $arq) {
				$nome = $files['name'][$key];
				//echo "Processando $nome <br>\n";
                
				$ext = ltrim(substr($nome, strrpos($nome, '.')), '.');
				$arquivo = $nome;

				if(strtolower($ext) == 'xlsx' || strtolower($ext) == 'csv') {
					//echo $arq."<br>\n";

					$salvo = $this->moverArquivo($files['tmp_name'][$key], $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivosExcel' . DIRECTORY_SEPARATOR . $arquivo);
					if($salvo) {
						$cont++;
					}
// echo $arquivo;
				}
			}
		} else {
			addPortalMensagem('Erro: nenhum arquivo encontrado.','error');
		}

		if($cont > 0) {
			header('Location: ' . getLink() . "avisos&mensagem=".$cont." arquivos salvos com sucesso!");
		} else {
			header('Location: ' . getLink() . "avisos&mensagem=nenhum arquivo anexado ou arquivo incorreto&tipo=erro");
		}
	}
    
    private function criaPasta($path, $dirname) {
		if (!file_exists($path . $dirname)) {
			mkdir($path . $dirname, 0777, true);
			chMod($path . $dirname, 0777);
		}
        if (!file_exists($path . $dirname . DIRECTORY_SEPARATOR . 'arquivosExcel')) {
			mkdir($path . $dirname . DIRECTORY_SEPARATOR . 'arquivosExcel', 0777, true);
			chMod($path . $dirname . DIRECTORY_SEPARATOR . 'arquivosExcel', 0777);
		}
		if (!file_exists($path . $dirname . DIRECTORY_SEPARATOR . 'arquivos')) {
			mkdir($path . $dirname . DIRECTORY_SEPARATOR . 'arquivos', 0777, true);
			chMod($path . $dirname . DIRECTORY_SEPARATOR . 'arquivos', 0777);
		}
	}

    private function moverArquivo($file, $arquivo) {
		$ret = false;

		if (move_uploaded_file($file, $arquivo)) {
			rename($arquivo, str_replace([' ', ','], ['_', ''], $arquivo));
			$ret = true;
		}

		return $ret;
	}

	public function processar() {
		$operacao = getOperacao();
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);

		if($operacao == 'processarNovamente') {
			$funcao = 'cadastro';
			unlink($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado.vert');
		} else {
			$funcao = 'index';
		}

		// VERIFICA SE EXISTE DIRETÓRIO
		if(file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivosExcel')) {
			// VERIFICA SE JÁ EXISTEM ARQUIVOS PROCESSADOS
			if(file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado.vert')) {
				$funcao = 'avisos';
				$get = "&mensagem=Já existem arquivos processados para este CNPJ!<br>Para processar novamente acesse<br>(cadastro de itens => processar novamente)&tipo=erro";
			} else { // Caso não tenha nenhm arquivo resultado.vert, irá processar
				$processa = new monofasico_lp_processa($cnpj, $contrato);

				$processa->getInformacoes();
			}
		} else { // Caso não exista um diretório deve retornar uma mensagem de erro
			$funcao = 'avisos';
			$get = "&mensagem=Não foi encontrado um diretório para este CNPJ!&tipo=erro";
		}

		header('Location: ' . getLink() . $funcao."&cnpj=".$cnpj . '|' . $contrato);
	}

	public function memoriaCalculo() {
		$ret = '';
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);

		if(file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos')) {
			$memoria = new monofasico_lp_memoria_calc($cnpj, $contrato);
	
			$ret = $memoria->index();
		} else {
			$ret = redireciona(getLink() . 'avisos&mensagem=Os arquivos ainda não foram processados!&tipo=erro');
		}

		return $ret;
	}

	public function cadastro() {
		$ret = '';
		$operacao = getOperacao();

		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);

		if(file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/resultado.vert')) {
			$cadastro = new monofasico_lp_cadastro($cnpj, $contrato);

			if($operacao == 'salvarItens') {
				$cadastro->salvarItens();
				redireciona(getLink() . 'cadastro&cnpj=' . $cnpj . '|' . $contrato);
			}

			$ret = $cadastro->index();
		} else {
			$ret = redireciona(getLink() . 'avisos&mensagem=Não há arquivos processados para este CNPJ&tipo=erro');
		}

		return $ret;
	}

	public function itensNota() {
		$ret = '';
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);

		if(file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/resultado.vert')) {
			$itens = new monofasico_lp_itens($cnpj, $contrato);

			$ret = $itens->index();
		} else {
			$ret = redireciona(getLink() . 'avisos&mensagem=Não há arquivos processados para este CNPJ&tipo=erro');
		}

		return $ret;
	}

	public function pisCofins() {
		$ret = '';
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);

		if(file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/resultado.vert')) {
			$pisCofins = new monofasico_lp_piscofins($cnpj, $contrato);

			$ret = $pisCofins->index();
		} else {
			$ret = redireciona(getLink() . 'avisos&mensagem=Não há arquivos processados para este CNPJ&tipo=erro');
		}

		return $ret;
	}

	public function existeArquivos($tipo, $local) {
		$ret = false;

		$files = glob($this->_path . $local . DIRECTORY_SEPARATOR . "*." . $tipo);

		if(count($files) > 0) {
			$ret = true;
		}

		return $ret;
	}
}