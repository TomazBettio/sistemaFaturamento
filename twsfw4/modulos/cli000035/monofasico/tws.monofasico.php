<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico
{

	private $_cnpj;

	var $funcoes_publicas = array(
		'index' 			=> true,
		'enviarArquivo' 	=> true,
		'upload'			=> true,
		'lerArquivo'		=> true,
		// 'enviarBanco'		=> true,
		'analise'			=> true,
		// 'compliance'		=> true,
		'salvar' => true,
		'incluir' 			=> true,
		'geraPlanilha'		=> true,
		'geraPDF'			=> true,
		'relatorio'			=> true,
		'arquivarDir'		=> true,
		'excluirDir'		=> true,
		'avisos'			=> true,
	);

	//Tabela
	private $_tabela;

	//Classe relatorio
	private $_relatorio;

	//Nome do programa
	private $_programa;

	//Titulo do relatorio
	private $_titulo;

	//Path arquivos upload XML
	private $_path;

	//nome do cliente
	private $_razaoSocial = '';

	//Razao social utilizada no nome dos arquivos
	private $_arquivoRS;

	//tipo dos arquivos
	private $_tipo;

	private $_analise;

	private $_pdf_relatorio;

	// Contratos dos clientes (I = CNPJ, V = CTR)
	private $_contrato = [];

	function __construct()
	{
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Modelo Monofásico';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'];

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Monofásicos';
		$this->_tabela = new tabela01($param);
	}

	public function index()
	{
		$ret = '';
		//$tabela = $this->_tabela;
		// $this->montaColunas();

		// =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

		// =================== parâmetros para passar como $_GET[]
		$get = [];
		$get['cnpj'] = 'cnpj';
		$get['contrato'] = 'contrato';
		$get['id'] = 'id';
		// ===================== FUNÇÃO TEMPORÁRIA ====================================


		// =============== FUNÇÃO PARA INCLUIR O ARQUIVO ===============================
		$param = array(
			'texto' => 'Incluir',
			'onclick' => "setLocation('" . getLink() . "incluir')",
		);
		$this->_tabela->addBotaoTitulo($param);

		$param = array(
			'texto' => 'Importar', //Texto no botão
			'link' => getLink() . 'enviarArquivo&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			// 'link' => 'razao',
			// 'coluna' => 'CLINOMEFANTASIA',
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'Processar', //Texto no botão
			'link' => getLink() . 'lerArquivo&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		// $param = array(
		// 	'texto' => 'Salvar', //Texto no botão
		// 	'link' => getLink() . 'enviarBanco&cnpj=', //Link da página para onde o botão manda
		// 	'coluna' => 'CLICNPJ', //Coluna impressa no final do link
		// 	'width' => 100, //Tamanho do botão
		// 	'flag' => '',
		// 	'tamanho' => 'pequeno', //Nenhum fez diferença?
		// 	'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
		// 	'pos' => 'F',
		// );
		// $this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'Análise', //Texto no botão
			'link' => getLink() . 'analise&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'Planilha', //Texto no botão
			'link' => getLink() . 'geraPlanilha&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

		$param = array(
			'texto' => 'PDF', //Texto no botão
			'link' => getLink() . 'geraPDF&cnpj=', //Link da página para onde o botão manda
			'coluna' => $get, //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);

		$this->_tabela->addAcao($param);


		$usuario = getUsuario();
		if ($usuario == 'emanuel.thiel@verticais.com.br' || $usuario == 'lisnei@grupomarpa.com.br') {
			$param = array(
				'texto' => 'Arquivar', //Texto no botão
				'link' => getLink() . 'arquivarDir&cnpj=', //Link da página para onde o botão manda
				'coluna' => $get, //Coluna impressa no final do link
				// 'link' => 'razao',
				// 'coluna' => 'CLINOMEFANTASIA',
				'width' => 100, //Tamanho do botão
				'flag' => '',
				'tamanho' => 'pequeno', //Nenhum fez diferença?
				'cor' => 'warning', //padrão: azul; danger: vermelho; success: verde
				'pos' => 'F',
			);
			$this->_tabela->addAcao($param);


			$param = array(
				'texto' => 'Excluir', //Texto no botão
				'link' => getLink() . 'excluirDir&cnpj=', //Link da página para onde o botão manda
				'coluna' => $get, //Coluna impressa no final do link
				// 'link' => 'razao',
				// 'coluna' => 'CLINOMEFANTASIA',
				'width' => 100, //Tamanho do botão
				'flag' => '',
				'tamanho' => 'pequeno', //Nenhum fez diferença?
				'cor' => 'danger', //padrão: azul; danger: vermelho; success: verde
				'pos' => 'F',
			);
			$this->_tabela->addAcao($param);
		}
		// $param = array(
		// 	'texto' => 'Relatório', //Texto no botão
		// 	'link' => getLink() . 'relatorio&cnpj=', //Link da página para onde o botão manda
		// 	'coluna' => 'CLICNPJ', //Coluna impressa no final do link
		// 	'width' => 100, //Tamanho do botão
		// 	'flag' => '',
		// 	'tamanho' => 'pequeno', //Nenhum fez diferença?
		// 	'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
		// 	'pos' => 'F',
		// );
		// $this->_tabela->addAcao($param);

		// $param = array(
		// 	'texto' => 'Compliance', //Texto no botão
		// 	'link' => getLink() . 'compliance&cnpj=', //Link da página para onde o botão manda
		// 	'coluna' => 'CLICNPJ', //Coluna impressa no final do link
		// 	'width' => 100, //Tamanho do botão
		// 	'flag' => '',
		// 	'tamanho' => 'pequeno', //Nenhum fez diferença?
		// 	'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
		// 	'pos' => 'F',
		// );
		// $this->_tabela->addAcao($param);


		$ret .= $this->_tabela;

		return $ret;
	}

	public function arquivarDir()
	{
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		//=================ZIPAR PASTA E FAZER DOWNLOAD DELA==================

		// ===================================================================
		$param = [];
		$param['status'] = 'arquivado';
		$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
		queryMF($sql);

		redireciona(getLink() . 'index');
	}

	public function avisos()
	{
		if ($_GET['tipo'] == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->index();
	}

	public function excluirDir()
	{
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		$diretorio = dir($this->_path . $cnpj . '_' . $contrato);
		while ($arquivo = $diretorio->read()) {
			$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
			$ext = strtolower($ext);
			if ($arquivo != '.' && $arquivo != '..') {
				unlink($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . $arquivo);
				// echo $arquivo . "<br> \n";
			}
		}

		$diretorio = dir($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos');
		while ($arquivo = $diretorio->read()) {
			$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
			$ext = strtolower($ext);
			if ($arquivo != '.' && $arquivo != '..') {
				unlink($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . $arquivo);
				// echo $arquivo . "<br> \n";
			}
		}

		$diretorio = dir($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'erro');
		while ($arquivo = $diretorio->read()) {
			$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
			$ext = strtolower($ext);
			if ($arquivo != '.' && $arquivo != '..') {
				unlink($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'erro' . DIRECTORY_SEPARATOR . $arquivo);
				// echo $arquivo . "<br> \n";
			}
		}

		$param = [];
		$param['status'] = 'excluido';
		$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
		queryMF($sql);

		redireciona(getLink() . 'index');
		addPortalMensagem('arquivos excluidos com sucesso!');
	}

	public function relatorio()
	{
		$ret = '';

		$get = str_replace(' ', '', $_GET['cnpj']);
		$contrato = str_replace('/', '-', substr($get, 15));
		$cnpj = substr(str_replace(['/', '.', '-'], '', $get), 0, 14);

		$relatorio = new monofasico_relatorio($cnpj, $contrato);

		$ret = $relatorio->index();

		return $ret;
	}

	public function geraPDF()
	{
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];



		if (file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/resumoCompliance.vert')) {
			global $config;
			// ob_start();
			$ret = '';

			$this->_pdf_relatorio = new monofasico_pdf_relatorio($cnpj, $contrato);

			$param = [];
			$param['status'] = 'em compliance';
			$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
			queryMF($sql);

			$ret .= $this->_pdf_relatorio->index();
			// ob_get_clean();
			$link = $config['linkMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/' . $cnpj . '.zip';
			redirect($link, false);

			// redireciona(getLink() . 'index');
		} else {
			return header('Location: ' . getLink() . "avisos&mensagem=Não existem arquivos analisados neste cliente!&tipo=erro");
		}
	}


	public function analise()
	{
		$ret = '';
		$operacao = getOperacao();
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2] ?? '';

		$this->_analise = new monofasico_analise($cnpj, $contrato);

		if (!empty($id)) {
			$param = [];
			$param['status'] = 'em andamento';
			$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
			queryMF($sql);
		}

		if ($operacao == 'salvarItens') {
			$this->_analise->salvarItens();
			redireciona(getLink() . 'analise&cnpj=' . $cnpj . '|' . $contrato);
		} else if ($operacao == 'refazerAnalise') {
			$this->_analise->refazerAnalise();
			// redireciona(getLink() . 'analise&cnpj=' . $cnpj);
		} else if ($operacao == 'gerarPlanilha') {
			$this->_analise->gerarPlanilha();
		}
		// $this->_analise

		$ret .= $this->_analise->index();

		return $ret;
	}

	private function getDados()
	{
		$ret = [];

		$sql = "SELECT * FROM mgt_monofasico";
		$rows = queryMF($sql);

		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$temp = [];
				$temp['id'] 			= $row['id'];
				$temp['razao'] 		= $row['razao'];
				$temp['cnpj'] 		= $row['cnpj'];
				$temp['contrato'] = $row['contrato'];
				$temp['datactr'] 	= $row['datactr'];
				$temp['status'] 	= $row['status'];
				$temp['usuario'] 	= $row['usuario'];

				$ret[] = $temp;
			}
		}

		// putAppVar('contrato', $contrato);

		return $ret;
	}

	public function incluir()
	{
		$ret = '';
		$id = base64_encode(time());

		$form = new form01();
		$form->addHidden('id', $id);

		$param = [];
		$param['campo'] = 'razao';
		$param['etiqueta'] = 'Razão';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$form->addCampo($param);


		$param = [];
		$param['campo'] = 'cnpj';
		$param['etiqueta'] = 'CNPJ';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		// $param['tamanho'] = '14';
		$param['mascara'] = 'cnpj';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

		$param = [];
		$param['campo'] = 'contrato';
		$param['etiqueta'] = 'Contrato';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

		$param = [];
		$param['campo'] = 'datactr';
		$param['etiqueta'] = 'Data';
		$param['largura'] = '2';
		$param['tipo'] = 'D';
		// $param['mascara'] = 'D';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

		$form->setEnvio(getLink() . 'salvar', 'formIncluir_cliente');

		$ret .= $form;

		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Incluir Cliente Manual';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
	}

	// ================== Salvar dados da função incluir ==================
	public function salvar()
	{
		$ret = '';

		$cnpj = str_replace(['-', '.', '/'], '', $_POST['cnpj']);

		$param = [];
		$param['id'] 				= base64_decode($_POST['id']);
		$param['razao']	 		= strtoupper($_POST['razao']);
		$param['cnpj'] 			= $cnpj;
		$param['contrato'] 	= $_POST['contrato'];
		$param['datactr'] 	= datas::dataD2S($_POST['datactr']);
		$param['usuario']		= getUsuario();

		$sql = montaSQL($param, 'mgt_monofasico');
		queryMF($sql);

		$ret .= $this->index();

		return $ret;
	}

	private function montaColunas()
	{

		$this->_tabela->addColuna(array('campo' => 'id', 'etiqueta' => 'ID#', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'razao', 'etiqueta' => 'Razão', 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'cnpj', 'etiqueta' => 'CNPJ', 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'datactr', 'etiqueta' => 'Data CTR', 'tipo' => 'D', 'width' => 80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'contrato', 'etiqueta' => 'Contrato', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'status', 'etiqueta' => 'Status', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'usuario', 'etiqueta' => 'Usuario', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
	}

	private function criaPasta($pasta)
	{
		if (!file_exists($pasta)) {
			mkdir($pasta, 0777, true);
			chMod($pasta, 0777);
		}
		if (!file_exists($pasta . DIRECTORY_SEPARATOR . 'arquivos')) {
			mkdir($pasta . DIRECTORY_SEPARATOR . 'arquivos', 0777, true);
			chMod($pasta . DIRECTORY_SEPARATOR . 'arquivos', 0777);
		}
		if (!file_exists($pasta . DIRECTORY_SEPARATOR . 'erro')) {
			mkdir($pasta . DIRECTORY_SEPARATOR . 'erro', 0777, true);
			chMod($pasta . DIRECTORY_SEPARATOR . 'erro', 0777);
		}
		if (!file_exists($pasta . DIRECTORY_SEPARATOR . 'processados_xml')) {
			mkdir($pasta . DIRECTORY_SEPARATOR . 'processados_xml', 0777, true);
			chMod($pasta . DIRECTORY_SEPARATOR . 'processados_xml', 0777);
		}
		if (!file_exists($pasta . DIRECTORY_SEPARATOR . 'processados_sped')) {
			mkdir($pasta . DIRECTORY_SEPARATOR . 'processados_sped', 0777, true);
			chMod($pasta . DIRECTORY_SEPARATOR . 'processados_sped', 0777);
		}
	}

	public function enviarArquivo()
	{
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

	public function upload()
	{
		$ret = '';
		if (!isset($_FILES['upd_arquivo'])) {
			$ret = $this->index();
			return $ret;
		}

		$files = $_FILES['upd_arquivo'];
		// print_r($files['error'][0]);

		if (count($files['name']) > 0 && $files['error'][0] == 0) {
			//Vai servir para identificao de diretorio unico de upload

			$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
			$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
			$contrato = str_replace('/', '-', $get[1]);
			$id = $get[2];

			$pasta = $this->_path . $cnpj . '_' . $contrato;
			$this->criaPasta($pasta);

			$cont = 0;
			foreach ($files['name'] as $key => $arq) {
				$nome = $files['name'][$key];
				//echo "Processando $nome <br>\n";

				$ext = ltrim(substr($nome, strrpos($nome, '.')), '.');
				$arquivo = $nome;

				if (strtolower($ext) == 'xml' || strtolower($ext) == 'txt' || strtolower($ext) == 'zip') {
					//echo $arq."<br>\n";

					$salvo = $this->moverArquivo($files['tmp_name'][$key], $pasta . DIRECTORY_SEPARATOR . $arquivo);
					if ($salvo) {
						$cont++;
					}
				}
			}
			if ($cont > 0) {
				//add aqui
				$param = [];
				$param['status'] = 'importado';
				$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
				queryMF($sql);

				redireciona(getLink() . 'index');
				addPortalMensagem($cont . ' arquivos salvos com sucesso!');
			}
		} else {
			$ret = 'Nenhum arquivo enviado!';
		}

		$ret = $this->index();

		return $ret;
	}

	public function lerArquivo()
	{
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		$this->extrai_zip($cnpj . '_' . $contrato);

		$processa_xml = new processa_xml($cnpj, $contrato);
		$processa_sped = new processa_sped($cnpj, $contrato);

		$param = [];
		$param['status'] = 'esteira';
		$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
		queryMF($sql);

		if ($processa_xml->getExisteArquivo() === true) {
			$processa_xml->getInformacoes($cnpj);
			$this->_tipo = 'xml';
		}

		if ($processa_sped->getExisteArquivo() === true) {
			$processa_sped->getInformacoes($cnpj);
			$this->_tipo = 'sped';
		}

		// $param = [];
		// $param['cnpj'] = $cnpj;
		// $param['tipo'] = $this->_tipo;
		// $param['user_inc'] = getUsuario();
		// $sql = montaSQL($param, 'mgt_monofasico');
		// queryMF($sql);

		redireciona(getLink() . 'index');
		// return $this->index();
	}

	private function extrai_zip($cnpj_ctr)
	{
		$arquivos = $this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . 'arquivos';
		$erro = $this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . 'erro';
		$processados_xml = $this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . 'processados_xml';
		$processados_sped = $this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . 'processados_sped';

		$dir = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
		$dir = array_diff($dir, array($arquivos, $erro, $processados_xml, $processados_sped));
		sort($dir);

		$zip = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*.zip');

		foreach ($zip as $folder) {
			$zip = new ZipArchive;
			$res = $zip->open($folder);
			if ($res === TRUE) {
				$zip->extractTo($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR);
				// log::gravaLog('extrair_zip', 'Arquivo: ' . $folder . ' extraído com sucesso!');
				$zip->close();
				unlink($folder);
				// die('ok');
			}
		}
		$zip = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*.zip');

		$dir = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
		$dir = array_diff($dir, array($arquivos, $erro, $processados_xml, $processados_sped));
		sort($dir);
		// log::gravaLog('extrair_zip', 'antes do while' . print_r($dir, true));

		while (count($dir) > 0 || count($zip) > 0) {
			$zip = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*.zip');

			foreach ($zip as $folder) {
				$zip = new ZipArchive;
				$res = $zip->open($folder);
				if ($res === TRUE) {
					$zip->extractTo($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR);
					// log::gravaLog('extrair_zip', 'Arquivo: ' . $folder . ' extraído com sucesso!');
					$zip->close();
					unlink($folder);

					// die('ok');
				}
			}
			// log::gravaLog('extrair_zip', 'entrou no while');
			foreach ($dir as $folder) {

				// log::gravaLog('extrair_zip', 'nome da pasta: ' . $folder);
				chmod($folder, 0777);
				$files = glob($folder . DIRECTORY_SEPARATOR . '*');
				// $files = glob($this->_path . $cnpj . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . '*');
				// print_r($files);
				foreach ($files as $file) {
					if (is_dir($file)) {
						$dir[] = $file;
					}
					log::gravaLog('extrair_zip', 'arquivo :  ' . $file);
					chmod($file, 0777);

					// log::gravaLog('extrair_zip', 'renomeando: ' . $file . ' para ' . $this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . basename($file));

					rename($file, $this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . basename($file));
					// $newfile = basename($file);
					// rename($file, $this->_path . $cnpj . DIRECTORY_SEPARATOR . $newfile);
				}
				log::gravaLog('extrair_zip', 'Excluindo pasta ' . $folder);
				rmdir($folder);
				unset($dir[$folder]);
			}
			$zip = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*.zip');

			$dir = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
			$dir = array_diff($dir, array($arquivos, $erro, $processados_xml, $processados_sped));
		}
	}


	private function moverArquivo($file, $arquivo)
	{
		$ret = false;


		if (move_uploaded_file($file, $arquivo)) {
			$ret = true;
		}

		return $ret;
	}

	public function geraPlanilha()
	{
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		if (file_exists($this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resumoCompliance.vert')) {
			global $config;

			$teste = new gerar_excel($cnpj, $contrato);

			$teste->setPlanilha();

			$link = $config['linkMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/' . $cnpj . '_' . $contrato . '.xlsx';

			redirect($link, false);
			// redireciona(getLink() . 'index');
		} else {
			return header('Location: ' . getLink() . "avisos&mensagem=Não existem arquivos analisados neste cliente!&tipo=erro");
		}
	}

	public function compliance()
	{
		$cnpj = str_replace(['/', '.', '-'], '', $_GET['cnpj']);
		$pdf = new monofasico_compliance($cnpj);

		$pdf->criaHTML();

		$ret = $this->index();
		return $ret;
	}
}
