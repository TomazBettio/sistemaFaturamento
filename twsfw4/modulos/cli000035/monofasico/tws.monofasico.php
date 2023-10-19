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
		'resultadoExcel'	=> true,
		'analise'			=> true,
		'analise02'			=> true,
		'abreDocs'			=> true,
		'salvar' 			=> true,
		'incluir' 			=> true,
		'editar' 			=> true,
		'geraPlanilha'		=> true,
		'geraPDF'			=> true,
		'relatorio'			=> true,
		'arquivarDir'		=> true,
		'excluirDir'		=> true,
		'uploadCSV'			=> true,
		'transformarCSV'	=> true,
		'envios'			=> true,
		'enviarRelatorio'	=> true,
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
	
	//log do processamento
	private $_logContrato;

	public function __construct()
	{
		global $config;
		conectaERP();
		conectaMF();

		$this->_titulo = 'Modelo Monofásico';
		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'];
		
		$param = [];
		$param['width'] = 'AUTO';

		//$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['scrollCollapse'] = false;
		$param['titulo'] = 'Monofásicos';
		$this->_tabela = new tabela01($param);
	}

	public function index()
	{
		$ret = '';
		//$tabela = $this->_tabela;
		// $this->montaColunas();

		$usuario = getUsuario();

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

		// ================ FUNÇÃO PARA ENVIAR O RELATÓRIO SEMANAL ======================
		if ($usuario == 'emanuel.thiel@verticais.com.br') {
			$param = array(
				'texto' => 'Enviar relatório',
				'onclick' => "setLocation('" . getLink() . "enviarRelatorio')",
			);
			$this->_tabela->addBotaoTitulo($param);
		}

		$param = [];
		$param['titulo'] = 'Ações';
		$param['width'] 	= 100;
		
		$i = 0;
		$param['opcoes'][$i]['texto'] 	= 'Editar';
		$param['opcoes'][$i]['link'] 	= getLink() . 'editar&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Importar';
		$param['opcoes'][$i]['link'] 	= getLink() . 'enviarArquivo&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= 'naoMigrado';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 			= 'Processar';
		$param['opcoes'][$i]['link'] 			= getLink() . 'lerArquivo&cnpj=';
		$param['opcoes'][$i]['coluna'] 			= $get;
		$param['opcoes'][$i]['flag_habilitado']	= 'habilitado';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Análise';
		$param['opcoes'][$i]['link'] 	= getLink() . 'analise&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Análise Consolidada';
		$param['opcoes'][$i]['link'] 	= getLink() . 'analise02&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Arq_CSV';
		$param['opcoes'][$i]['link'] 	= getLink() . 'resultadoExcel&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Planilha';
		$param['opcoes'][$i]['link'] 	= getLink() . 'geraPlanilha&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'PDF';
		$param['opcoes'][$i]['link'] 	= getLink() . 'geraPDF&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Painel';
		$param['opcoes'][$i]['link'] 	= getLink() . 'relatorio&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Abre recepção DOCs';
		$param['opcoes'][$i]['link'] 	= getLink() . 'abreDocs&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= 'migrado';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Envios';
		$param['opcoes'][$i]['link'] 	= getLink() . 'envios&cnpj=';
		$param['opcoes'][$i]['coluna'] 	= $get;
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		if(verificarAcaoSys016($usuario, 'arquivar')){
			$i++;
			$param['opcoes'][$i]['texto'] 	= 'Arquivar';
			$param['opcoes'][$i]['link'] 	= getLink() . 'arquivarDir&cnpj=';
			$param['opcoes'][$i]['coluna'] 	= $get;
			$param['opcoes'][$i]['flag'] 	= '';
			//$param['opcoes'][$i]['onclick'] 	= '';
		}
		
		if(verificarAcaoSys016($usuario, 'monofasico_excluir')){
			$i++;
			$param['opcoes'][$i]['texto'] 	= 'Excluir';
			$param['opcoes'][$i]['link'] 	= getLink() . 'excluirDir&cnpj=';
			$param['opcoes'][$i]['coluna'] 	= $get;
			$param['opcoes'][$i]['flag'] 	= '';
			//$param['opcoes'][$i]['onclick'] 	= '';
		}
		
		$this->_tabela->addAcaoDropdown($param);


		$ret .= $this->_tabela;

		return $ret;
	}

	public function arquivarDir()
	{
	    if(isset($_GET['cnpj'])){
	        $get = explode('|', str_replace(' ', '', $_GET['cnpj']));
	        $cnpj = str_replace(['/', '.', '-'], '', $get[0]);
	        $contrato = str_replace('/', '-', $get[1]);
	        $id = $get[2];
	        
	        $param = [];
	        $param['status'] = 'arquivado';
	        $param['data_alt'] = date('Y-m-d H:i:s');
	        $sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
	        query($sql);
	        
	        addPortalMensagem("Contrato arquivado com sucesso");
	    }
	    else{
	        addPortalMensagem("Não foi possível arquivar o contrato");
	    }
		

		redireciona();
	}

	public function excluirDir()
	{
		$confirma = isset($_GET['confirma']) ? base64_decode($_GET['confirma']) : '';
		$somenteArquivos = $_GET['somArquivos'] ?? false;
		
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];
		
		$dir = $this->_path . $cnpj . '_' . $contrato;
		
		if(empty($confirma)){
			$param = [];
			$param["onclick"]	= "setLocation('".getLink()."index')";
			$param["texto"]		= "Cancelar";
			$param['cor'] 		= 'warning';
			$botaoConfirma = formbase01::formBotao($param);
			
			$param = [];
			$param["onclick"]	= "setLocation('".getLink()."excluirDir&cnpj=".$_GET['cnpj']."&confirma=".base64_encode('123bjhuasgdgajgejhdjagsd')."')";
			$param["texto"]		= "EXCLUIR Tudo";
			$param['cor'] 		= 'danger';
			$param['help'] 		= 'Exlui todos os arquivos recebidos, os cálculos e o processo no Banco de Dados.';
			$botaoExcluir = formbase01::formBotao($param);
			
			$param = [];
			$param["onclick"]	= "setLocation('".getLink()."excluirDir&cnpj=".$_GET['cnpj']."&confirma=".base64_encode('123bjhuasgdgajgejhdjagsd')."&somArquivos=1')";
			$param["texto"]		= "EXCLUIR Somente Arquivos";
			$param['cor'] 		= 'danger';
			$param['help'] 		= 'Exlui todos os arquivos recebidos e os cálculos (mas mantem o processo no Banco de Dados)';
			$botaoExcluirArq = formbase01::formBotao($param);
			
			$param = [];
			$param['titulo'] = "Confirma exclusão do processo $cnpj - $contrato?" ;
			$param['conteudo'] = $botaoConfirma.' '.$botaoExcluir.' '.$botaoExcluirArq;
			$ret = addCard($param);
			
			return $ret;
		}elseif($confirma = 'confirmado'){
			if(is_dir($dir) && $dir != $this->_path){
				$iterator     = new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS);
				$rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
				
				foreach($rec_iterator as $file){
					if($file->getFilename() != 'memoria_calculo'){
						$file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
					}
				}
	
				rmdir($dir);
			}else{
				addPortalMensagem('Diretorio com os arquivos não encontrado!','error');
			}
			
			if(!$somenteArquivos){
				//Exclui o processo na base
				$sql = "DELETE FROM mgt_monofasico WHERE id = $id";
				query($sql);
				log::gravaLog('monofasico_processa'.DIRECTORY_SEPARATOR.'monofasico_pdf_' . $cnpj, 'Exclusão geral');
			}else{
				log::gravaLog('monofasico_processa'.DIRECTORY_SEPARATOR.'monofasico_pdf_' . $cnpj, 'Exclusão somente arquivos');
			}
			
			//Deleta os logs de leitura de XML como excluidos
			$sql = "DELETE FROM  mgt_monofasico_log_xml WHERE cnpj = '$cnpj' AND contrato = '$contrato'";
			query($sql);
			
			/*/
			$param = [];
			$param['status'] = 'excluido';
			$param['data_alt'] = date('Y-m-d H:i:s');
			$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
			query($sql);
			/*/
			
			addPortalMensagem('Processo excluído com sucesso!');
		}

		redireciona();
	}
		
	public function relatorio()
	{
		$ret = '';
		$operacao = getOperacao();

		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		$ret = $this->painelContrato($cnpj, $contrato, $id, $_GET['cnpj']);
		
		if(!empty($operacao)){
			switch ($operacao) {
				case 'sped':
					$relatorio = new monofasico_relatorio($cnpj, $contrato, $id, 'sped');
					break;
				case 'xml':
					$relatorio = new monofasico_relatorio($cnpj, $contrato, $id, 'xml');
					break;
				case 'erro':
					$relatorio = new monofasico_relatorio($cnpj, $contrato, $id, 'erro');
					break;
			}
			
			$ret .= '<br>'.$relatorio;
		}

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

			//Atualiza o status do processo
			$param = [];
			$param['status'] = 'em compliance';
			$param['data_alt'] = date('Y-m-d H:i:s');
			$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
			query($sql);

			$this->setValorApurado($get);

			$ret .= $this->_pdf_relatorio->index();
			// ob_get_clean();
			$link = $config['linkMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/' . $cnpj . '.zip';
			redirect($link, false);

			// redireciona(getLink() . 'index');
		} else {
			log::gravaLog('monofasico_processa'.DIRECTORY_SEPARATOR.'monofasico_pdf_' . $this->_cnpj, 'Nao foi encontrado o arquivo resumoCompliance.vert');
			addPortalMensagem('Não existem arquivos analisados neste cliente','error');
			redireciona();
		}
	}

	public function analise()
	{
		set_time_limit(0);
		mantemConectado(120);
		$ret = '';
		$operacao = getOperacao();
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2] ?? '';

		$this->_analise = new monofasico_analise($cnpj, $contrato, $id);
		
		if (!empty($id)) {
			$param = [];
			$param['status'] = 'em andamento';
			$param['data_alt'] = date('Y-m-d H:i:s');
			$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
			query($sql);
		}

		if ($operacao == 'salvarItens') {
			$this->_analise->salvarItens();
			redireciona(getLink() . 'analise&cnpj=' . $cnpj . '|' . $contrato . '|' . $id);
		} elseif($operacao == 'refazerAnalise') {
			$this->_analise->refazerAnalise();
			// redireciona(getLink() . 'analise&cnpj=' . $cnpj);
		} elseif($operacao == 'gerarPlanilha') {
			$this->_analise->gerarPlanilha();
		}
		// $this->_analise

		$ret .= $this->_analise->index();

		$setArquivos = new monofasico_set_arquivos($cnpj, $contrato, $id);
		$setArquivos->setDados();

		return $ret;
	}
	
	public function analise02()
	{
		set_time_limit(0);
		mantemConectado(120);
		$ret = '';
		$operacao = getOperacao();
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2] ?? '';
		
		$this->_analise = new monofasico_analise02($cnpj, $contrato, $id);
		
		if (!empty($id)) {
			$param = [];
			$param['status'] = 'em andamento';
			$param['data_alt'] = date('Y-m-d H:i:s');
			$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
			query($sql);
		}
		
		if ($operacao == 'salvarItens') {
			$this->_analise->salvarItens();
			redireciona(getLink() . 'analise02&cnpj=' . $cnpj . '|' . $contrato . '|' . $id);
		} elseif($operacao == 'refazerAnalise') {
			$this->_analise->refazerAnalise();
			// redireciona(getLink() . 'analise&cnpj=' . $cnpj);
		} elseif($operacao == 'gerarPlanilha') {
			$this->_analise->gerarPlanilha();
		}
		// $this->_analise
		
		$ret .= $this->_analise->index();
		
		$setArquivos = new monofasico_set_arquivos($cnpj, $contrato, $id);
		$setArquivos->setDados();
		
		return $ret;
	}

	public function editar()
	{
		$ret = '';
		if(isset($_GET['cnpj'])){
			$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
			$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
			$contrato = str_replace('/', '-', $get[1]);
			$id = $get[2];
			
			$operacao = getOperacao();
			if($operacao == ''){
				$ret = $this->incluir($cnpj, $contrato, $id);
				return $ret;
			}elseif($operacao == 'gravar'){
				
			}
		}
		else{
			addPortalMensagem("Não foi possível encontrar o contrato");
		}
		
		
		redireciona();
	}
	
	public function incluir($cnpj='', $contrato='', $id='')
	{
		$ret = '';
		
		if(empty($id)){
			$titulo = 'Incluir Cliente Manual';
			$dados = [];
			$dados['razao'] 	= '';
			$dados['cnpj'] 		= '';
			$dados['contrato'] 	= '';
			$dados['datactr'] 	= '';
			$dados['apura_ini'] = '';
			$dados['apura_fim'] = '';
			$tipo = 'T';
		}else{
			$titulo = 'Alteração de Contrato';
			$dados = $this->getDados($id);
			$tipo = 'I';
		}

		$form = new form01();
		$form->addHidden('id', $id);

		$form->addCampo(['campo'=> 'razao'		, 'etiqueta'=> 'Razão'			, 'tipo'=> $tipo, 'obrigatorio'=> true, 'valor' => $dados['razao']	, 'tamanho'=>'255', 'linha'=> '1', 'largura'=> '12']);
		$form->addCampo(['campo'=> 'cnpj'		, 'etiqueta'=> 'CNPJ'			, 'tipo'=> $tipo, 'obrigatorio'=> true, 'valor' => $dados['cnpj']		, 'tamanho'=> '20', 'linha'=> '2', 'largura'=> '4', 'mascara' => 'cnpj']);
		$form->addCampo(['campo'=> 'contrato'	, 'etiqueta'=> 'Contrato'		, 'tipo'=> $tipo, 'obrigatorio'=> true, 'valor' => $dados['contrato']	, 'tamanho'=> '30', 'linha'=> '2', 'largura'=> '4']);
		$form->addCampo(['campo'=> 'datactr'	, 'etiqueta'=> 'Data Contrato'	, 'tipo'=> 'D', 'obrigatorio'=> true, 'valor' => $dados['datactr']	, 'tamanho'=> '10', 'linha'=> '2', 'largura'=> '2']);

		$form->addCampo(['campo'=> 'apura_ini', 'etiqueta'=> 'Início Apuração'	, 'tipo'=> 'D', 'obrigatorio'=> true, 'valor' => $dados['apura_ini'], 'tamanho'=> '10', 'linha'=> '3', 'largura'=> '2', 'help' => 'Dia de início do período de apuração']);
		$form->addCampo(['campo'=> 'apura_fim', 'etiqueta'=> 'Final Apuração'	, 'tipo'=> 'D', 'obrigatorio'=> true, 'valor' => $dados['apura_fim'], 'tamanho'=> '10', 'linha'=> '3', 'largura'=> '2', 'help' => 'Dia fim do período de apuração']);

		if(empty($id)){
			$form->addCampo(['campo'=> 'criaDOCs'	, 'etiqueta'=> 'Cria usuário no DOCs'		, 'tipo'=> 'A', 'obrigatorio'=> false, 'valor' => 'N', 'tamanho'=> '10', 'linha'=> '3', 'largura'=> '4', 'tabela_itens' => '000003']);
//			$form->addCampo(['campo'=> 'enviaEmail'	, 'etiqueta'=> 'Envia email para o cliente'	, 'tipo'=> 'A', 'obrigatorio'=> false, 'valor' => 'N', 'tamanho'=> '10', 'linha'=> '3', 'largura'=> '4', 'tabela_itens' => '000003']);
		}
		
		$form->setEnvio(getLink() . 'salvar', 'formIncluir_cliente');

		$ret .= $form;

		$param = array();
		$param['titulo'] = $titulo;
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
	}

	/**
	 * Salvar dados da função incluir 
	 * 
	 * @return tabela01
	 */
	public function salvar()
	{
		$ret = '';

		$cnpj = str_replace(['-', '.', '/'], '', $_POST['cnpj']);
		$contrato = str_replace([' ','\\','/'],'-', $_POST['contrato']);
		
		$param = [];
		$param['datactr'] 	= datas::dataD2S($_POST['datactr']);
		$param['apura_ini'] = datas::dataD2S($_POST['apura_ini']);
		$param['apura_fim'] = datas::dataD2S($_POST['apura_fim']);
		
		
		if(empty($_POST['id'])){
			$param['id'] 	= time();
			$param['data_inc'] 	= datas::getTimeStampMysql();
			$param['usuario']	= getUsuario();
			$param['razao']	 	= strtoupper($_POST['razao']);
			$param['cnpj'] 		= $cnpj;
			$param['contrato'] 	= $contrato;

			$sql = montaSQL($param, 'mgt_monofasico');
			$mensagem = "Registro incluído com sucesso!";
		}else{
			$id = $_POST['id'];
			$param['data_alt'] 		= datas::getTimeStampMysql();
			$param['usuario_alt']	= getUsuario();
			
			$sql = montaSQL($param, 'mgt_monofasico', 'update', " id = $id");
			$mensagem = "Registro alterado com sucesso!";
		}

		query($sql);
		addPortalMensagem($mensagem);
		
		if(isset($_POST['criaDOCs']) && $_POST['criaDOCs'] == 'S'){
			$data = date('Ymd');
			$operacao = 'cadastro';
			
			$key = md5($contrato . '**' . $cnpj . "&&@" . $data);
			
			$razao = $_POST['razao'];
			
			$curl = curl_init();
			
			$post = [];
			$post['cnpj'] = $cnpj;
			$post['contrato'] = $contrato;
			$post['operacao'] = $operacao;
			$post['razao'] = $razao;
			$post['email'] = '';
			$post['key'] = $key;
			
			$url = "http://doc.grupomarpa.com.br/api_marpa.php";
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url, //https://arq.twslabs.com.br/api_marpa.php
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			));
			
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			
			$response = curl_exec($curl);
			
			curl_close($curl);
			// echo $response; //debug <- descomente para testar
			
			if ($response == 'Registrado com sucesso') {
				addPortalMensagem('Cliente criado com sucesso no Portal Docs!');
			}
		}
		
		if(isset($_POST['enviaEmail']) && $_POST['enviaEmail'] == 'S'){
			
		}
		

		redireciona();
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

					$salvo = $this->moverArquivo($files['tmp_name'][$key], $pasta.DIRECTORY_SEPARATOR.'recebidos'.DIRECTORY_SEPARATOR.$arquivo);
					if ($salvo) {
						$cont++;
					}
				}
			}
			if ($cont > 0) {
				//add aqui
				$param = [];
				$param['status'] = 'importado';
				$param['data_alt'] = date('Y-m-d H:i:s');
				$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
				query($sql);

				addPortalMensagem($cont . ' arquivos salvos com sucesso!');
				redireciona();
			}
		} else {
			$ret = 'Nenhum arquivo enviado!';
		}

		$ret = $this->index();

		return $ret;
	}

	public function lerArquivo()
	{
		set_time_limit(0);
		//$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$contrato_original = $get[1];
		$id = $get[2] ?? '';
		
		$dados_contrato = $this->getDados($id);
		
		$this->_logContrato = 'monofasico_processa'.DIRECTORY_SEPARATOR.$cnpj . '_' . $contrato;
		
		//Indica seos arquivos foram enviados por integração com o Portal DOCs
		$arquivos_importados = $this->getContratoIntegrado($cnpj, $contrato_original);
		
		log::gravaLog($this->_logContrato, "$cnpj - $contrato : ".$arquivos_importados);
		
		if($arquivos_importados == 'N'){
			log::gravaLog($this->_logContrato, 'Contrato NÃO integrado');
			$this->extrai_zip($cnpj . '_' . $contrato);

			$processa_xml = new processa_xml($cnpj, $contrato, $id, $arquivos_importados, $dados_contrato);
			$processa_sped = new processa_sped($cnpj, $contrato, $id, $arquivos_importados);
	
			$param = [];
			$param['status'] = 'esteira';
			$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "cnpj = $cnpj AND contrato = '$contrato'");
			query($sql);
	
			if ($processa_xml->getExisteArquivo() === true) {
				$processa_xml->getInformacoes($cnpj);
				$this->_tipo = 'xml';
	
				addPortalMensagem('Arquivos XMLs processados');
			}else{
				log::gravaLog($this->_logContrato, 'Não foram encontrados arquivos XML');
			}
	
			if ($processa_sped->getExisteArquivo() === true) {
				$processa_sped->getInformacoes($cnpj);
				$this->_tipo = 'sped';
	
				addPortalMensagem('Arquivos SPED processados');
			}else{
				log::gravaLog($this->_logContrato, 'Não foram encontrados arquivos SPED');
			}
	
		}else{
			log::gravaLog($this->_logContrato, 'Contrato INTEGRADO');
			$processa_xml = new processa_xml($cnpj, $contrato, $id, $arquivos_importados, $dados_contrato);
			if ($processa_xml->getExisteArquivo() === true) {
				log::gravaLog($this->_logContrato, 'Processando arquivos XMLs importados');
				$processa_xml->getInformacoes($cnpj);
				addPortalMensagem('Arquivos XMLs processados - Portal Documentos');
			}else{
				log::gravaLog($this->_logContrato, 'Não existem arquivos XMLs para integrar');
				$processa_sped = new processa_sped($cnpj, $contrato, $id, $arquivos_importados);
				if ($processa_sped->getExisteArquivo() === true) {
					log::gravaLog($this->_logContrato, 'Processando arquivos SPED importados');
					$processa_sped->getInformacoes($cnpj);
					addPortalMensagem('Arquivos SPED processados');
				}else{
					log::gravaLog($this->_logContrato, 'Não existem arquivos SPED para integrar');
				}
			}
		}
		
		redireciona(getLink() . 'index');
	}

	/**
	 *  Libera o upload de arquivos no Portal DOC para o contrato
	 */
	public function abreDocs(){
		$confirma = isset($_GET['confirma']) ? base64_decode($_GET['confirma']) : '';
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];
		
		if(empty($confirma)){
			$param = [];
			$param["onclick"]	= "setLocation('".getLink()."index')";
			$param["texto"]		= "Cancelar";
			$param['cor'] 		= 'warning';
			$botaoConfirma = formbase01::formBotao($param);
			
			$param = [];
			$param["onclick"]	= "setLocation('".getLink()."abreDocs&cnpj=".$_GET['cnpj']."&confirma=".base64_encode('123bjhuasgdgajgejhdjagsd')."')";
			$param["texto"]		= "Excluir contrato e liberar DOCs";
			$param['cor'] 		= 'danger';
			$param['help'] 		= 'Exlui todos os arquivos recebidos, os cálculos e o processo no Banco de Dados e libera o acesso do cliente ao Portal DOCs para complementar os arquivos';
			$botaoExcluir = formbase01::formBotao($param);
			
			
			$param = [];
			$param['titulo'] = "Confirma liberação no DOCs do processo $cnpj - $contrato?" ;
			$param['conteudo'] = $botaoConfirma.' '.$botaoExcluir;
			$ret = addCard($param);
			
			return $ret;
		}elseif($confirma = 'confirmado'){
			$dir = $this->_path . $cnpj . '_' . $contrato;
			if(is_dir($dir)){
				$iterator     = new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS);
				$rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
				
				foreach($rec_iterator as $file){
					if($file->getFilename() != 'memoria_calculo'){
						$file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
					}
				}
				rmdir($dir);
			}
			
			//Exclui o processo na base
			$sql = "DELETE FROM mgt_monofasico WHERE id = $id";
			query($sql);
			
			//Deleta os logs de leitura de XML como excluidos
			$sql = "DELETE FROM  mgt_monofasico_log_xml WHERE cnpj = '$cnpj' AND contrato = '$contrato'";
			query($sql);
			
			addPortalMensagem('Arquivos e processo excluído com sucesso!');
			
			$res = $this->integraLiberaUploadDOCs($cnpj, $contrato);
			if($res == 'sucesso'){
				addPortalMensagem('Cliente liberado no Portal DOCs!');
			}else{
				addPortalMensagem('Erro ao liberar o acesso no Portal Docs!','error');
			}
		}
		
		redireciona();
	}
	//-------------------------------------------------------------------------------------------------- UI 
		
	private function montaColunas()
	{
		
		//$this->_tabela->addColuna(array('campo' => 'id'			, 'etiqueta' => 'ID#'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'status'		, 'etiqueta' => 'Status'	, 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'cnpj'		, 'etiqueta' => 'CNPJ'		, 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'razao'		, 'etiqueta' => 'Razão'		, 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'datactr'	, 'etiqueta' => 'Data CTR'	, 'tipo' => 'D', 'width' => 80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'contrato'	, 'etiqueta' => 'Contrato'	, 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'usuario'	, 'etiqueta' => 'Usuario'	, 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
	}
	
	private function painelContrato($cnpj, $contrato, $id, $identificacao)
	{
		global $nl;
		$ret = '';
		
		$path = $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
		$sped = verifica_quant_arquivos($path.'processados_sped');
		$xml = verifica_quant_arquivos($path.'processados_xml');
		$erro = verifica_quant_arquivos($path.'erro');
		
		//$falta = $this->getAgendasSemOS();
		
		$ret .= '<div class="row">'.$nl;
		
		$ret .= '<div class="col-lg-4">'.$nl;
		$param = [];
		$param['cor'] = 'bg-blue';
		$param['valor'] = $sped;
		$param['medida'] = '';
		$param['texto'] = 'Processados SPED';
		$param['icone'] = 'fa-file-text-o';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'relatorio.sped&cnpj='.$identificacao;
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$ret .= '<div class="col-lg-4">'.$nl;
		$param = [];
		$param['cor'] = 'bg-blue';
		$param['valor'] = $xml;
		$param['medida'] = '';
		$param['texto'] = 'Processados XML';
		$param['icone'] = 'fa-file-text-o';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'relatorio.xml&cnpj='.$identificacao;
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$ret .= '<div class="col-lg-4">'.$nl;
		$param = [];
		$param['cor'] = 'bg-red';
		$param['valor'] = $erro;
		$param['medida'] = '';
		$param['texto'] = 'ERROS';
		$param['icone'] = 'fa-exclamation-triangle';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'relatorio.erro&cnpj='.$identificacao;
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		
		$param = [];
		$param['titulo'] = "Painel - $cnpj - $contrato";
		$param['botaoCancelar'] = true;
		$param['conteudo'] = $ret;
		
		$ret = addCard($param);
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------ DADOS

	private function getDados($id = '')
	{
		$ret = [];
		
		$where = '';
		if(!empty($id)){
			$where = " AND id = $id ";
		}
		
		$sql = "SELECT * FROM mgt_monofasico where status NOT IN ('excluido','arquivado','perdido') AND del <> '*' $where ORDER BY id DESC";
		$rows = query($sql);
		
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$temp = [];
				$temp['id'] 			= $row['id'];
				$temp['razao'] 			= substr($row['razao'], 0, 70);
				$temp['cnpj'] 			= $row['cnpj'];
				$temp['contrato'] 		= $row['contrato'];
				$temp['contratoLimpo'] 	= $this->limpaContrato($temp['contrato']);
				$temp['datactr'] 		= $row['datactr'];
				$temp['status'] 		= $row['status'];
				$temp['usuario'] 		= $row['usuario'];
				$temp['migrado'] 		= $row['integrado'] == 'S' ? true : false;
				$temp['naoMigrado'] 	= $row['integrado'] == 'S' ? false: true;
				$temp['apura_ini']		= $row['apura_ini'];
				$temp['apura_fim']		= $row['apura_fim'];
				
				$temp['habilitado']		= empty(trim($row['apura_ini'])) ? false : true;
				
				$ret[] = $temp;
			}
		}
		
		if(!empty($id) && count($ret) > 0){
			$ret = $ret[0];
		}
		
		return $ret;
	}
		
	private function getValorApurado($get)
	{
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		
		$ret = [];
		
		$arquivo = $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resumoCompliance.vert';
		
		if (file_exists($arquivo)) {
			$arquivo = file($arquivo);
			foreach ($arquivo as $linha) {
				$sep = explode('|', $linha);
				
				$temp = [];
				$temp['data'] = $sep[0];
				$temp['valor'] = $sep[1];
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function setValorApurado($get)
	{
		$id = $get[2];
		
		$dados = $this->getValorApurado($get);
		
		if (is_array($dados) && count($dados) > 0) {
			foreach ($dados as $dado) {
				$sql = "UPDATE mgt_monofasico_arquivos SET valor_apurado = " . $dado['valor'] . " WHERE id_monofasico = $id AND data = " . $dado['data'];
				query($sql);
			}
		}
	}
	
	
	
	//------------------------------------------------------------------------------------------ UTEIS
	private function integraLiberaUploadDOCs($cnpj, $contrato)
	{
		global $config;
		$ret = '';
		
		$post = [];
		$post['cnpj'] = $cnpj;
		$post['contrato'] = $contrato;
		$post['operacao'] = 'liberar_cliente';
		$post['data'] = date('Ymd');
		$post['key'] = md5($post['contrato'].'**'.$post['cnpj']."&&@".$post['data']);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $config['linkDOCs']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$resposta = curl_exec($ch);
		
		if($resposta === false) {
			log::gravaLog('portal_documentos_curl', 'Curl error liberar_cliente: ' . curl_error($ch));
		}
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		log::gravaLog('portal_documentos_curl', 'Curl liberar_cliente code: ' . $httpCode);
		if(in_array($httpCode, array(200, 201, 202))){
			if($resposta != ''){
				$ret = $resposta;
			}
		}
		curl_close($ch);
		
		return $ret;
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
		if (!file_exists($pasta . DIRECTORY_SEPARATOR . 'recebidos')) {
			mkdir($pasta . DIRECTORY_SEPARATOR . 'recebidos', 0777, true);
			chMod($pasta . DIRECTORY_SEPARATOR . 'recebidos', 0777);
		}
	}
	
	
	private function extrai_zip($cnpj_ctr)
	{
		$path_recebidos = $this->_path.$cnpj_ctr.DIRECTORY_SEPARATOR.'recebidos'.DIRECTORY_SEPARATOR;
		
		$dir = glob($path_recebidos.'*', GLOB_ONLYDIR);
		$zip = glob($path_recebidos.'*.zip');

		foreach ($zip as $folder) {
			log::gravaLog($this->_logContrato, 'Abrindo zip: '.$folder);
			$zip = new ZipArchive;
			$res = $zip->open($folder);
			if ($res === TRUE) {
				$zip->extractTo($path_recebidos);
				log::gravaLog($this->_logContrato, 'Arquivo: ' . $folder . ' extraído com sucesso!');
				$zip->close();
				unlink($folder);
				// die('ok');
			}else{
				log::gravaLog($this->_logContrato, 'Não foi possível abrir o zip: '.$folder);
			}
		}
		$zip = glob($this->_path . $cnpj_ctr . DIRECTORY_SEPARATOR . '*.zip');

		$dir = glob($path_recebidos.'*', GLOB_ONLYDIR);
		$zip = glob($path_recebidos.'*.zip');
		while (count($dir) > 0 || count($zip) > 0) {
			if(count($zip) > 0){
				foreach ($zip as $folder) {
					$zip = new ZipArchive;
					$res = $zip->open($folder);
					if ($res === TRUE) {
						$zip->extractTo($path_recebidos);
						log::gravaLog($this->_logContrato, 'Arquivo: ' . $folder . ' extraído com sucesso!');
						$zip->close();
						unlink($folder);
					}else{
						log::gravaLog($this->_logContrato, 'Não foi possível abrir o zip: '.$folder);
					}
				}
			}
			
			if(count($dir) > 0){
				foreach ($dir as $folder) {
					chmod($folder, 0777);
					$files = glob($folder . DIRECTORY_SEPARATOR . '*');
					foreach ($files as $file) {
						if (is_dir($file)) {
							$dir[] = $file;
						}
						log::gravaLog($this->_logContrato, 'arquivo :  ' . $file);
						chmod($file, 0777);
						rename($file, $path_recebidos.basename($file));
					}
					log::gravaLog($this->_logContrato, 'Excluindo pasta ' . $folder);
					rmdir($folder);
					unset($dir[$folder]);
				}
			}
			$dir = glob($path_recebidos.'*', GLOB_ONLYDIR);
			$zip = glob($path_recebidos.'*.zip');
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

	public function resultadoExcel()
	{
		$ret = '';
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		$resultado_vert = $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado.vert';
		$resultado_csv = $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado' . $contrato . '.csv';

		if (file_exists($resultado_vert) && !file_exists($resultado_csv)) {
			$csv = new resultado_excel($cnpj, $contrato);
			$csv->setPlanilhaResultado();

			$ret = $this->montaAjuste($cnpj, $contrato, $id);
			return $ret;
		} else if (file_exists($resultado_csv)) {
			$ret = $this->montaAjuste($cnpj, $contrato, $id);
			return $ret;
		} else { 
			addPortalMensagem('Não há arquivos analisados', 'error');
			redireciona();
		}
	}

	private function montaAjuste($cnpj, $contrato, $id = '')
	{
		global $config;
		$ret = '';

		$param = [];
		$param['url'] = $config['linkMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos/resultado' . $contrato . '.csv';
		$param['texto'] = 'Exportar CSV';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$res_csv = formbase01::formBotao($param);

		$param = [];
		$param['url'] = getLink() . 'transformarCSV&cnpj=' . $_GET['cnpj'];
		$param['texto'] = 'Salvar Alterações';
		$param['cor'] = 'success';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$transf_csv = formbase01::formBotao($param);


		$param = [];
		$param['tamanhos'] = [2, 2];
		$param['conteudos'] = [$res_csv, $transf_csv];
		$ret .= addLinha($param);


		$param = [];
		$param['titulo'] = 'Download CSV';
		$param['conteudo'] = $ret;
		$param['botaoCancelar'] = false;
		$ret = addCard($param);

		$ret .= $this->updRetorno();
		return $ret;
	}

	public function transformarCSV()
	{
		$ret = '';
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		$file = $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado' . $contrato . '.csv';
		$resultado_vert = $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado.vert';

		if (file_exists($file)) {
			$linhas = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$str = '';
			array_shift($linhas);
			foreach ($linhas as $linha) {
				// $linha = str_replace(["\r\n", "\n", "\r", "'"], '', $linha);
				$linha = str_replace("'", '', $linha);
				$linha = str_replace(';', '|', $linha);
				$str .= $linha . "\r\n";
			}
			//colocar as mudanças no arquivo
			// file_put_contents($file, $str);
			file_put_contents($resultado_vert, $str);
		} else {
			addPortalMensagem('Não foi criado o arquivo csv deste cliente!', 'error');
			redireciona();
		}
		$analise = new monofasico_analise($cnpj, $contrato, $id);
		$analise->resumoCompliance($resultado_vert);
		return $this->index();
	}

	private function updRetorno()
	{
		global $nl;
		$ret = '';

		$param = [];
		$param['nome'] 	= 'upd_csv';
		$form = formbase01::formFile($param) . '<br><br>';

		$param = formbase01::formSendParametros();

		$param['texto'] = 'Importar CSV';
		$form2 = formbase01::formBotao($param);

		$ret .= '<div class="row">' . $nl;
		$ret .= '	<div  class="col-md-4">' . $form . '</div>' . $nl;
		$ret .= '	<div  class="col-md-2"></div>' . $nl;
		$ret .= '	<div  class="col-md-5">' . $form2 . '</div>' . $nl;
		$ret .= '</div>' . $nl;

		$param = array();
		$param['acao'] = getLink() . "uploadCSV&cnpj= " . $_GET['cnpj'];
		$param['nome'] = 'formUPD';
		$param['id']   = 'formUPD';
		$param['enctype'] = true;
		$ret = formbase01::form($param, $ret);

		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['cor'] = 'warning';
		$p['texto'] = 'Retornar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Enviar Arquivo ".csv"';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
	}

	public function uploadCSV()
	{
		$ret = '';
		if (!isset($_FILES['upd_csv'])) {
			$ret = $this->index();
			return $ret;
		}

		$file = $_FILES['upd_csv'];
		// print_r($file['error'][0]);

		//Vai servir para identificao de diretorio unico de upload

		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		$pasta = $this->_path . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR;

		$nome = $file['name'];
		//echo "Processando $nome <br>\n";

		$ext = ltrim(substr($nome, strrpos($nome, '.')), '.');
		$arquivo = $nome;

		if (strtolower($ext) == 'csv') {
			//echo $arq."<br>\n";

			$salvo = $this->moverArquivo($file['tmp_name'], $pasta . DIRECTORY_SEPARATOR . $arquivo);
			if ($salvo) {
				$ret = $this->montaAjuste($cnpj, $contrato, $id);;
				return $ret;
			} else {
				addPortalMensagem('Erro ao salvar arquivo!', 'error');
				redireciona();
			}
		}
	}

	//função não utilizada
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
			addPortalMensagem('Não existem arquivos analisados neste cliente!', 'error');
			redireciona();
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

	public function envios()
	{
		$get = explode('|', str_replace(' ', '', $_GET['cnpj']));
		$cnpj = str_replace(['/', '.', '-'], '', $get[0]);
		$contrato = str_replace('/', '-', $get[1]);
		$id = $get[2];

		$arquivos = new monofasico_arquivos($cnpj, $contrato, $id);
		$ret = $arquivos->index();

		return $ret;
	}

	public function enviarRelatorio()
	{
		$relatorio = new monofasico_relatorio_arq();

		$relatorio->enviarRelatorio();
	}
	
	private function getContratoIntegrado($cnpj, $contrato){
		$ret = '';
		
		$sql = "SELECT integrado FROM mgt_monofasico WHERE cnpj = '$cnpj' AND contrato = '$contrato'";
		log::gravaLog($this->_logContrato, $sql);
		$rows = query($sql);
		
		if(isset($rows[0]['integrado'])){
			$ret = $rows[0]['integrado'];
		}
		
		return $ret;
	}
	
	private function limpaContrato($contrato){
		$contrato = str_replace(' ', '', $contrato);
		$contrato = str_replace('/', '-', $contrato);
		
		return $contrato;
	}
}
