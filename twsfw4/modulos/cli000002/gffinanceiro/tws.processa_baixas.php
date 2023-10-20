<?php
/*
 * Data Criacao: 01/03/2018
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Utilizado para realizar baixa dos títulos de grandes redes que realizam o pagamento via depósito bancário
 * 		      e enviam um arquivo com a relação dos títulos que integram este depósito
 *
 * Alterações:
 * 		15/04/19 - Inclusão do processamento das baixas do grupo São João - Neto
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
include_once($config["include"].'phpExcel/PHPExcel.php');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);




class processa_baixas{
	var $_relatorio;
	
	var $funcoes_publicas = array(
			'index' 			=> true,
			'processaTitulos' 	=> true,
			'baixaTitulos'		=> true,
			'arquivos'			=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Titulo variavel
	var $_titulo;
	
	//Dados do relatorio
	var $_dados;
	
	//indica se deve fazer o trace das querys
	var $_trace;
	
	//Indica que se é teste (utiliza banco teste)
	var $_teste;
	
	//Nome de clientes
	var $_clientes;
	
	//tabela da intranet que tem os dados
	var $_tabelaIntranet;
	
	//rotina que baixa o titulo
	var $_rotinaBaixa;
	
	//URL baixa
	var $_urlBaixa;
	
	//Arquivo de log
	var $_arquivoLog;
	
	function __construct(){
		global $pagina, $config, $db5;
		set_time_limit(0);
		
		$this->_trace = true;
		
		$this->_teste = true;
		
		$this->_tabelaIntranet = 'gf_titulosbaixados';
		$this->_rotinaBaixa = 'gffinanceiro.baixa_titulo.baixaTitulo';
		$this->_urlBaixa = 'gffinanceiro.processa_baixas';
		$this->_arquivoLog = 'processa_baixa_log';
		
		$pagina->addScript('js','cliente000002/processa_baixas.js');
		
		//$this->limpaNaoProcessados();
		
		if($this->_teste === true){
			$db5 = ADONewConnection($config["db5_banco"]);
			$db5->debug = true;
			$db5->Connect($config["db5_server"], $config["db5_usuario"], $config["db5_senha"], $config["db5_database"]);
		}
	}
	
	
	function index(){
		$ret = '';
		
		$ret .= $this->formulario();
		
		return $ret;
	}
	
	function schedule($param){
	}
	
	function processaTitulos(){
		$ret = '';
		$this->geraScriptConfirmacao();
		//$hash = md5(getUsuario().date('Ymd'));
		$banco 		= isset($_POST['baixa']['banco']) ? $_POST['baixa']['banco'] : '';
		$moeda 		= isset($_POST['baixa']['moeda']) ? $_POST['baixa']['moeda'] : '';
		$historico 	= isset($_POST['baixa']['historico']) ? $_POST['baixa']['historico'] : '';
		$dataBaixa 	= isset($_POST['baixa']['data']) ? $_POST['baixa']['data'] : '';
		
		if($banco == '' || $moeda == '' || $historico == '' || $dataBaixa == ''){
			$mensagem = "Banco, moeda, data e histórico devem ser preenchidos, favor verificar!";
			$ret .= $this->formulario($mensagem);
			
			return $ret;
		}
		$this->ajustaDataPagamento(datas::dataD2S($dataBaixa));
		
		$sql = "SELECT * FROM ".$this->_tabelaIntranet." WHERE  usuario = '".getUsuario()."' AND processado <> 'S'";
		$rows = query($sql);
		if(count($rows) == 0){
			$mensagem = "Deve ser efetuada a leitura do arquivo de retorno do cliente antes de executar o processamento!";
			$ret .= $this->formulario($mensagem);
			
			return $ret;
		}
		
		$dados = [];
		foreach ($rows as $row){
			$id = $row['duplicata'].'|'.$row['parcela'];
			$campo = $row['duplicata'].'_'.$row['parcela'];
			$temp = [];
			$abre  = '';
			$fecha = '';
			$dif = round($row['valor'] - $row['vpagoarq'] - $row['vdesconto'],2);
			//if($row['dtbaixa'] == 0 && $dif >= 0){
			if($row['dtbaixa'] == 0){
				$checked = 'checked';
				//				$checked = '';
				if($dif <> 0){
					$checked = '';
					$abre  = '<font color="#FF0000">';
					$fecha = '</font>';
				}
				if(getUsuario() == 'thiel'){
//					$checked = 'checked';
				}
				
				$temp['sel'] = '<input name="baixar['.$campo.']" type="checkbox" value="'.$id.'" '.$checked.' id="'.$campo.'">';
			}else{
				$temp['sel'] = '';
			}
			$temp['codcli'] 	= $abre.$row['codcli'].$fecha;
			$temp['nome'] 		= $abre.$this->getNomeCliente($row['codcli']).$fecha;
			$temp['duplic'] 	= $abre.$row['duplicata'].$fecha;
			$temp['prest'] 		= $abre.$row['parcela'].$fecha;
			$temp['codcob'] 	= $abre.$row['codcob'].$fecha;
			$temp['valor'] 		= $abre.$row['valor'].$fecha;
			$temp['vencimento']	= $abre.$row['dtvenc'].$fecha;
			$temp['dtpag']  	= $row['dtbaixa'] == 0 ? '' : $row['dtbaixa'];
			$temp['vpago'] 		= $row['vpago'] = 0 ? '' : $row['vpago'];
			//if($dif <> 0){
			//	$temp['varq'] 	= '<strong><font color="#FF0000">'.$row['vpagoarq'].'</font></strong>';
			//}else{
			$temp['varq'] 	= $row['vpagoarq'];
			//}
			$temp['vDescArq']	= $abre.$row['vdesconto'].$fecha;
			$temp['dtarq'] 		= $abre.$row['dtpagoarq'].$fecha;
			
			if($row['dtbaixa'] == 0 && $dif > 0){
				$param = [];
				$param['nome'] = "desconto[$campo]";
				$param['style'] = "text-align: right";
//echo getUsuario()."<br>\n";
				if(getUsuario() == 'thiel'){
					$param['valor']	= round($row['valor'] - ($row['vpagoarq'] + $row['vdesconto']),2);
				}else{
					$param['valor']	= '';
				}
				$temp['desconto'] 	= formbase01::formTexto($param);
			}else{ 
				$temp['desconto'] 	= '';
			}
			
			if($row['dtbaixa'] == 0 && $dif < 0){
				$param = [];
				$param['nome'] = "juros[$campo]";
				$param['style'] = "text-align: right";
				$param['valor']	= round(($dif * -1),2);
				$temp['juros'] 	= formbase01::formTexto($param);
			}else{
				$temp['juros'] 	= '';
			}
			
			$dados[] = $temp;
		}
		
		$ret .= $this->browser($dados);
		
		$ret .= formbase01::formHidden(array('nome' => 'banco'		, 'valor' => $banco));
		$ret .= formbase01::formHidden(array('nome' => 'moeda'		, 'valor' => $moeda));
		$ret .= formbase01::formHidden(array('nome' => 'historico'	, 'valor' => $historico));
		
		$ret .= '<br>Total Recebido: <input type="text" name="total" id="total" value="0" readonly="readonly" size="9" >';
		$ret .= '<br>Total Titulos: <input type="text" name="totalDesc" id="totalDesc" value="0" readonly="readonly" size="9" >';
		
		$param = [];
		$param['tamanho'] = 'padrao';
		$param['texto'] = 'Baixar Titulos Selecionados';
		$ret .= formbase01::formSend($param);
		
		$param = [];
		$param["acao"] 	= "index.php?menu=".$this->_urlBaixa.".baixaTitulos";
		$param["id"] 	= "formTitulos";
		$param['onsubmit'] = 'confirmaEnvio';
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function geraScriptConfirmacao(){
		addPortaljavaScript('function confirmaEnvio(){');
		addPortaljavaScript("		if(confirm('Confirma a baixa das parcelas?')){");
		addPortaljavaScript("			return true;");
		addPortaljavaScript("		}else{");
		addPortaljavaScript("			return false;");
		addPortaljavaScript("		}");
		addPortaljavaScript("}");
	}
	
	private function browser($dados, $tit = ''){
		$ret = '';
		if($tit == ''){
			$titulo = 'Processo de baixa de títulos.';
		}else{
			//$titulo = $tit;
			$titulo = '';
		}
		$param = [];
		$param['paginacao'] = false;
		$param['titulo']	= $titulo;
		$bw = new tabela01($param);
		
		if($tit == ''){
			$bw->addColuna(array('campo' => 'sel'		, 'etiqueta' => ''				, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'C'));
		}
		$bw->addColuna(array('campo' => 'codcli'	, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'duplic'	, 'etiqueta' => 'Duplicata'		, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'prest'		, 'etiqueta' => 'Parcela'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'codcob'	, 'etiqueta' => 'CODCOB'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'valor'		, 'etiqueta' => 'Valor'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$bw->addColuna(array('campo' => 'vencimento', 'etiqueta' => 'Vencimento'	, 'tipo' => 'D', 'width' => 100, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'dtpag'		, 'etiqueta' => 'Dt.Pagamento'	, 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'vpago'		, 'etiqueta' => 'Valor Pago'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		if($tit == ''){
			$bw->addColuna(array('campo' => 'varq'		, 'etiqueta' => 'Valor Arquivo'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
			$bw->addColuna(array('campo' => 'vDescArq'	, 'etiqueta' => 'Desconto Arq.'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'C'));
			$bw->addColuna(array('campo' => 'dtarq'		, 'etiqueta' => 'Dt.Arquivo'	, 'tipo' => 'D', 'width' => 100, 'posicao' => 'C'));
		}
		$bw->addColuna(array('campo' => 'desconto'	, 'etiqueta' => 'Desconto'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
		//		$bw->addColuna(array('campo' => 'juros'		, 'etiqueta' => 'Juros'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
		
		$bw->setDados($dados);
		$ret .= $bw;
		//echo $ret;
		unset($bw);
		
		return $ret;
	}
	
	function formulario($msg = ''){
		$this->limpaNaoProcessados();
		$ret = '';
		if($msg != ''){
			addPortalMensagem($msg,'error');
		}
		
		$moedas = $this->listaMoedas();
		$bancos = $this->listaBancos();
		$url = 'index.php?menu='.$this->_urlBaixa.'.processaTitulos';
		
		//$ret .= setTituloPagina('Processo de baixa de títulos.','Enviar arquivos');
		
		$form = new form01();
		//$form->setTipoForm(4);
		
		$form->addCampo(array('id' => 'banco'		, 'campo' => 'baixa[banco]'		, 'etiqueta' => 'Banco'		, 'tipo' => 'A'	, 'tamanho' => '80'	, 'linhas' => ''	, 'valor' => ''	, 'lista' => $bancos	, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => 'moeda'		, 'campo' => 'baixa[moeda]'		, 'etiqueta' => 'Moeda'		, 'tipo' => 'A'	, 'tamanho' => '80'	, 'linhas' => ''	, 'valor' => ''	, 'lista' => $moedas	, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => 'historico'	, 'campo' => 'baixa[historico]'	, 'etiqueta' => 'Histórico'	, 'tipo' => 'T'	, 'tamanho' => '50'	, 'linhas' => ''	, 'valor' => ''	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => 'databaixa'	, 'campo' => 'baixa[data]'		, 'etiqueta' => 'Data Pgto' , 'tipo' => 'D'	, 'tamanho' => '50'	, 'linhas' => ''	, 'valor' => ''	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		
		$form->setEnvio($url, 'baixaForm', 'baixaForm');
		
		
		$ret .= $form;
		
		$temp1 = addDivColuna(6, $ret);
		$param = [];
//		$param['url'] = $this->_urlBaixa;
		$temp2 = addDivColuna(6, formbase01::formUploadFile($param));
		
		$ret = addRow($temp1.$temp2);
		
		$param = [];
		$param['titulo'] = 'Processo de baixa de títulos.';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	function baixaTitulos(){
		$ret = '';
		//$hash = md5(getUsuario().date('Ymd'));
		$banco 		= isset($_POST['banco']) ? $_POST['banco'] : '';
		$moeda 		= isset($_POST['moeda']) ? $_POST['moeda'] : '';
		$historico 	= isset($_POST['historico']) ? $_POST['historico'] : '';
		
		$baixar = $_POST['baixar'];
		if(count($baixar) == 0){
			$mensagem = "Não foi marcado nenhum titulo para ser baixado!";
			$ret .= $this->formulario($mensagem);
			
			return $ret;
		}
		
		$numTrans = &ExecMethod('gffinanceiro.baixa_titulo.getNumTrans');
		
//print_r($_POST);	
		$valorTotal = 0;
		foreach ($baixar as $key => $tit){
			list($duplicata, $parcela) = explode('|', $tit);
			
			$desconto = 0;
			if(isset($_POST['desconto'][$key]) && $_POST['desconto'][$key] != ''){
				$desconto = $_POST['desconto'][$key];
			}
			
			$dadosTitulo = $this->getDadosTitulo($duplicata, $parcela);
//print_r($dadosTitulo);die();	
			//$dataPag = $dadosTitulo['dtpagoarq'];
			$dataPag = $dadosTitulo['dataPagParam'];
			$valorArq = $dadosTitulo['vpagoarq'];
			$descontoArq = $dadosTitulo['vdesconto'];
			//$valor =  $dadosTitulo['valor'];
			log::gravaLog($this->_arquivoLog, "$duplicata, $parcela, $valorArq, $banco, $moeda, $dataPag, $historico, ($desconto+$descontoArq)");
			$ret = &ExecMethod($this->_rotinaBaixa, $duplicata, $parcela, $valorArq, $banco, $moeda, $dataPag, $historico, ($desconto+$descontoArq), $numTrans);
			if($ret){
				$valorTotal += $valorArq;
				$this->upd_gf_titulosbaixados($duplicata, $parcela, $banco, $moeda, $dataPag, $historico, $valorArq, ($desconto+$descontoArq));
			}
		}
		
		if($valorTotal > 0){
			$ret = &ExecMethod('gffinanceiro.baixa_titulo.updPCMOVCRLote', $numTrans, $banco, $moeda, $valorTotal, $historico );
		}
		
		
		$ret = $this->browserBaixas();
		return $ret;
	}
	
	function arquivos(){
		$ret = [];
		$this->limpaNaoProcessados();
//print_r($_FILES);
		if(count($_FILES) > 0){
			if (is_uploaded_file($_FILES['files']['tmp_name'][0])) {
				$nome = $_FILES['files']['name'][0];
				$ext = substr($nome, strlen($nome) -3,3);
				if(strtoupper($ext) == 'TXT' || strtoupper($ext) == 'XLS' || strtoupper($ext) == 'LSX' || strtoupper($ext) == 'CSV'){
					$ret = $this->leArquivo($_FILES['files']['tmp_name'][0], $_FILES['files']['name'][0]);
				}
				//unlink($_FILES['files']['tmp_name'][0]);
			}else{
				echo "Não upload \n";
			}
		}else{
			echo "Sem arquivos \n";
		}
		//print_r($ret);return;
		return array('files' => $ret);
	}
	
	private function browserBaixas(){
		$ret = '';
		$arquivo = getAppVar('arquivo_lido_baixa_titulo');
		$baixados = [];
		$abertos = [];
		//$hash = md5(getUsuario().date('Ymd'));
		$sql = "SELECT * FROM ".$this->_tabelaIntranet." WHERE usuario = '".getUsuario()."' AND arquivo = '$arquivo'";
		//echo "$sql <br>\n";die();
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['codcli'	] 	= $row['codcli'];
				$temp['nome'	]   = $this->getNomeCliente($row['codcli']);
				$temp['duplic'	] 	= $row['duplicata'];
				$temp['prest'	]   = $row['parcela'];
				$temp['codcob'	] 	= $row['codcob'];
				$temp['valor'	]   = $row['valor'];
				$temp['vencimento'] = $row['dtvenc'];
				$temp['dtpag'	]   = $row['processado'] == 'S' ? $row['dataPag'] : '';
				$temp['vpago'	]   = $row['processado'] == 'S' ? $row['valorPag'] : 0;
				//$temp['varq'	]   = $row[''];
				//$temp['dtarq'	]   = $row[''];
				$temp['desconto']   = $row['processado'] == 'S' ? $row['descontoPag'] : 0;
				
				if($row['processado'] == 'S'){
					$baixados[] = $temp;
				}else{
					$abertos[] = $temp;
				}
			}
		}
		
		if(count($baixados) > 0){
			//print_r($baixados);
			$param = [];
			$param['titulo'] = 'Titulos Baixados';
			$param['conteudo'] = $this->browser($baixados, 'Titulos Baixados');
			$ret .= addCard($param);
		}
		if(count($abertos) > 0){
			//print_r($abertos);
			$param = [];
			$param['titulo'] = 'Titulos Não Baixados';
			$param['conteudo'] = $this->browser($abertos, 'Titulos Não Baixados');
			$ret .= addCard($param);
		}
		//setTituloPagina('', '');
		
		return $ret;
	}
	
	private function leArquivo($arquivo, $nomeArquivo){
		$ret = [];
//echo 'nome do arquivo: '.strtoupper(retiraAcentos($nomeArquivo))."<br>\n";
		putAppVar('arquivo_lido_baixa_titulo', $nomeArquivo);
		$cliente = '';
		if(strpos(strtoupper($nomeArquivo), 'SESI') !== false){
			$cliente = 'sesi';
			$ret = $this->processaArquivoTexto($arquivo, $nomeArquivo, $cliente);
		}elseif(strpos(strtoupper($nomeArquivo), 'WMS') !== false){
			$cliente = 'walmart';
			$ret = $this->processaArquivoTexto($arquivo, $nomeArquivo, $cliente);
		}elseif(strpos(strtoupper($nomeArquivo), 'CLAMED') !== false){
			$cliente = 'clamed';
			$ret = $this->processaArquivoXLSclamed($arquivo, $nomeArquivo, $cliente);
		}elseif(strpos(strtoupper($nomeArquivo), 'ITAU') !== false){
			$cliente = 'itau';
			$ret = $this->processaArquivoXLSitau($arquivo, $nomeArquivo, $cliente);
		}elseif(strpos(strtoupper(retiraAcentos($nomeArquivo)), 'SAO JOAO') !== false){
			$cliente = 'saojoao';
			$ret = $this->processaArquivoXLSsaoJoao($arquivo, $nomeArquivo, $cliente);
			//echo "São Joao - $nomeArquivo";
		}elseif(strpos(strtoupper(retiraAcentos($nomeArquivo)), 'PERDAS') !== false){
			$cliente = 'PERDAS';
			$ret = $this->processaArquivoXLSperdas($arquivo, $nomeArquivo, $cliente);
		}elseif(strpos(strtoupper(retiraAcentos($nomeArquivo)), 'DESCONTO') !== false){
			$cliente = 'DESCONTO';
			$ret = $this->processaArquivoXLSdesconto($arquivo, $nomeArquivo, $cliente);
		}else{
			return 'Arquivo não processado';
		}
		
		return $ret;
	}
	
	/**
	 * Processa os arquivos de Baixa a Desconto
	 *
	 * @param string $arquivo
	 * @param string $nomeArquivo
	 * @param int $cliente
	 * @return array|string
	 */
	private function processaArquivoXLSdesconto($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$retorno = [];
		
		$input_file_type = PHPExcel_IOFactory::identify($arquivo);
		$obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
		$obj_reader->setReadDataOnly(true);
		
		$objPHPExcel = $obj_reader->load($arquivo);
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
		$max_lin = $objWorksheet->getHighestRow();
		//$max_col = $objWorksheet->getHighestColumn();
		//echo "Linhas: $max_lin \n";die();
		$cab = [];
		$cab[] = 'titulo';
		$cab[] = 'parcela';
		$cab[] = 'cliente';
		$cab[] = 'nome';
		$cab[] = 'cob';
		$cab[] = 'emissao';
		$cab[] = 'vencimento';
		$cab[] = 'valor';
		
		$linhaOK = true;
		$linha = 2;
		while ($linhaOK) {
			$temp = [];
			foreach ($cab as $i => $c){
				$temp[$c] = trim(utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $linha)->getValue()));
				//echo $max_lin.'   -   '.$i.":".$linha." - ".$temp[$c]."\n";
			}
			//die();
			if($max_lin >= $linha){
				//print_r($temp);
				$temp['valor'] = str_replace('-', '', $temp['valor']);
				if( !empty($temp[$cab[1]])){
					$ret[] = $temp;
				}
				$linha++;
			}else{
				$linhaOK = false;
			}
		}
		//print_r($ret);
		//die();
		foreach ($ret as $r){
			$nota 		= trim($r['nota']);
			$pagamento 	= date('Ymd');
			$valor 		= (float)str_replace(',', '.', trim($r['valor']));
			$titulo = $r['titulo'];
			$parcela = $r['parcela'];
			$nota = $titulo;
			//echo $r['titulo']." <- documento titulo: $titulo - $parcela<br>\n";
			$integradora = 'PERDAS';
			
			//$verificaTitulo = $this->verificaTitulo($nota, 0, $parcela);
			$verificaTitulo = $this->verificaTitulo($titulo, 0, $parcela);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, 0, $nomeArquivo, $integradora, $valor);
					if($retInc){
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
		}
		
		return $retorno;
	}
	
	/**
	 * Processa os arquivos de Perdas
	 *
	 * @param string $arquivo
	 * @param string $nomeArquivo
	 * @param int $cliente
	 * @return array|string
	 */
	private function processaArquivoXLSperdas($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$retorno = [];
		
		$input_file_type = PHPExcel_IOFactory::identify($arquivo);
		$obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
		$obj_reader->setReadDataOnly(true);
		
		$objPHPExcel = $obj_reader->load($arquivo);
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
		$max_lin = $objWorksheet->getHighestRow();
		//$max_col = $objWorksheet->getHighestColumn();
		//echo "Linhas: $max_lin \n";die();
		$cab = [];
		$cab[] = 'titulo';
		$cab[] = 'parcela';
		$cab[] = 'cliente';
		$cab[] = 'nome';
		$cab[] = 'cob';
		$cab[] = 'emissao';
		$cab[] = 'vencimento';
		$cab[] = 'valor';
		
		$linhaOK = true;
		$linha = 2;
		while ($linhaOK) {
			$temp = [];
			foreach ($cab as $i => $c){
				$temp[$c] = trim(utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $linha)->getValue()));
//echo $max_lin.'   -   '.$i.":".$linha." - ".$temp[$c]."\n";
			}
			//die();
			if($max_lin >= $linha){
				//print_r($temp);
				$temp['valor'] = str_replace('-', '', $temp['valor']);
				if( !empty($temp[$cab[1]])){
					$ret[] = $temp;
				}
				$linha++;
			}else{
				$linhaOK = false;
			}
		}
//print_r($ret);
//die();
		foreach ($ret as $r){
			$nota 		= trim($r['nota']);
			$pagamento 	= date('Ymd');
			$valor 		= (float)str_replace(',', '.', trim($r['valor']));
			$titulo = $r['titulo'];
			$parcela = $r['parcela'];
			$nota = $titulo;
//echo $r['titulo']." <- documento titulo: $titulo - $parcela<br>\n";
			$integradora = 'PERDAS';
			
			//$verificaTitulo = $this->verificaTitulo($nota, 0, $parcela);
			$verificaTitulo = $this->verificaTitulo($titulo, 0, $parcela);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora);
					if($retInc){
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
		}
		
		return $retorno;
	}
	
	/**
	 * Processa os arquivos da São Joao
	 *
	 * @param string $arquivo
	 * @param string $nomeArquivo
	 * @param int $cliente
	 * @return array|string
	 */
	private function processaArquivoXLSsaoJoao($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$retorno = [];
		
		$input_file_type = PHPExcel_IOFactory::identify($arquivo);
		$obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
		$obj_reader->setReadDataOnly(true);
		
		$objPHPExcel = $obj_reader->load($arquivo);
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
		$max_lin = $objWorksheet->getHighestRow();
		//$max_col = $objWorksheet->getHighestColumn();
//echo "Linhas: $max_lin \n";die();
		$cab = [];
		$cab[] = 'cnpj';
		$cab[] = 'titulo';
		$cab[] = 'valor';
		$cab[] = 'emissao';
		$cab[] = 'vencimento';
		$cab[] = 'pagamento';
		$cab[] = 'id';
		
		$linhaOK = true;
		$linha = 2;
		while ($linhaOK) {
			$temp = [];
			foreach ($cab as $i => $c){
				$temp[$c] = trim(utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $linha)->getValue()));
				//				echo $max_lin.'   -   '.$i.":".$linha." - ".$temp[$c]."\n";
			}
			//die();
			if($max_lin >= $linha){
//print_r($temp);
				$temp['valor'] = str_replace('-', '', $temp['valor']);
				if( !empty($temp[$cab[1]])){
					$ret[] = $temp;
				}
				$linha++;
			}else{
				$linhaOK = false;
			}
		}
		//print_r($ret);die();
		foreach ($ret as $r){
			$nota 		= trim($r['nota']);
			$pagamento 	= date('Ymd');
			$valor 		= (float)str_replace(',', '.', trim($r['valor']));
			$separa = explode('-',$r['titulo']);
			$titulo = $separa[0];
			$parcela = (int)$separa[1];
			$nota = $titulo;
			//echo $r['titulo']." <- documento titulo: $titulo - $parcela<br>\n";
			$integradora = 'SAOJOAO';
			
			$verificaTitulo = $this->verificaTitulo($nota, 0, $parcela);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora);
					if($retInc){
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
		}
		
		return $retorno;
	}
	
	/**
	 * Processa os arquivos da Clamed
	 *
	 * @param string $arquivo
	 * @param string $nomeArquivo
	 * @param int $cliente
	 * @return array|string
	 */
	private function processaArquivoXLSclamed($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$retorno = [];
		//$objReader = new PHPExcel_Reader_Excel5();
		//$objReader->setReadDataOnly(true);
		//$objPHPExcel = $objReader->load($arquivo);
		//$objPHPExcel->setActiveSheetIndex(0);
		
		$input_file_type = PHPExcel_IOFactory::identify($arquivo);
		$obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
		$obj_reader->setReadDataOnly(true);
		
		$objPHPExcel = $obj_reader->load($arquivo);
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
		$max_lin = $objWorksheet->getHighestRow();
		//$max_col = $objWorksheet->getHighestColumn();
		//echo "Linhas: $max_lin \n";die();
		$cab = [];
		$cab[0] = 'filial';
		$cab[1] = 'doc';
		$cab[2] = 'venc';
		$cab[3] = 'documento';
		$cab[4] = 'valor';
		//$cab[] = 'entrada';
		//$cab[] = 'vencimento';
		//$cab[] = 'prorrogacao';
		//$cab[] = 'credor';
		//$cab[] = 'valor';
		
		$linhaOK = true;
		$linha = 3;
		while ($linhaOK) {
			$temp = [];
			foreach ($cab as $i => $c){
				$temp[$c] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $linha)->getValue());
				//				echo $max_lin.'   -   '.$i.":".$linha." - ".$temp[$c]."\n";
			}
			//die();
			if($max_lin >= $linha){
				if( substr($temp[$cab[3]], 0, 4) == '001-'){
					$ret[] = $temp;
				}
				$linha++;
			}else{
				$linhaOK = false;
			}
		}
		
		foreach ($ret as $r){
			$nota 		= trim($r['nota']);
			$pagamento 	= date('Ymd');
			$valor 		= (float)str_replace(',', '.', trim($r['valor']));
			$separa = explode('-', $r['documento']);
			$titulo = $separa[1];
			$nota = $titulo;
			//echo $r['documento']." <- documento titulo: $titulo <br>\n";
			$integradora = 'CLAMED';
			
			$verificaTitulo = $this->verificaTitulo($nota, $valor);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora);
					if($retInc){
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
		}
		
		return $retorno;
	}
	
	
	/**
	 * Processa os arquivos do Itau
	 *
	 * @param string $arquivo
	 * @param string $nomeArquivo
	 * @param int $cliente
	 * @return array|string
	 */
	private function processaArquivoXLSitau($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$retorno = [];
		
		$input_file_type = PHPExcel_IOFactory::identify($arquivo);
		$obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
		$obj_reader->setReadDataOnly(true);
		
		$objPHPExcel = $obj_reader->load($arquivo);
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
		$max_lin = $objWorksheet->getHighestRow();
		//$max_col = $objWorksheet->getHighestColumn();
		//echo "Linhas: $max_lin \n";die();
		$cab = [];
		$cab[] = 'cliente';
		$cab[] = 'vencimento';
		//$cab[] = 'pagamento';
		$cab[] = 'valor';
		//$cab[] = 'carteira';
		//$cab[] = 'nosso';
		$cab[] = 'titulo';
		//$cab[] = 'situacao';
		//$cab[] = 'tipo';
		$cab[] = 'pagamento';
		
		$linhaOK = true;
		$linha = 2;
		while ($linhaOK) {
			$temp = [];
			foreach ($cab as $i => $c){
				$temp[$c] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $linha)->getValue());
				//				echo $max_lin.'   -   '.$i.":".$linha." - ".$temp[$c]."\n";
			}
			//die();
			if($max_lin >= $linha){
				//print_r($temp);
				if( !empty($temp[$cab[0]])){
					$ret[] = $temp;
				}
				$linha++;
			}else{
				$linhaOK = false;
			}
		}
		//print_r($ret);die();
		foreach ($ret as $r){
			$nota 		= trim($r['nota']);
			$pagamento 	= date('Ymd');
			$valor 		= (float)str_replace(',', '.', trim($r['valor']));
			$separa = trim(str_replace(' ', '', $r['titulo']));
			$titulo = substr($separa, 0,8);
			$parcela = substr($separa, -1);
			$nota = $titulo;
			//echo $r['titulo']." <- documento titulo: $titulo - $parcela<br>\n";
			$integradora = 'ITAU';
			
			$verificaTitulo = $this->verificaTitulo($nota, 0, $parcela);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora);
					if($retInc){
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
		}
		
		return $retorno;
	}
	
	
	/**
	 * Processa os arquivos do Walmart
	 *
	 * @param string $arquivo
	 * @param string $nomeArquivo
	 * @param int $cliente
	 * @return array|string
	 */
	private function processaArquivoXLS($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$retorno = [];
		$objReader = new PHPExcel_Reader_Excel5();
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($arquivo);
		$objPHPExcel->setActiveSheetIndex(0);
		
		$cab = [];
		$cab[] = 'nota';
		$cab[] = 'emissao';
		$cab[] = 'valor';
		$cab[] = 'desconto';
		$cab[] = 'antecipado';
		$cab[] = 'liquido';
		$cab[] = 'reposicao';
		
		$linhaOK = true;
		$linha = 2;
		while ($linhaOK) {
			$temp = [];
			foreach ($cab as $i => $c){
				$temp[$c] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $linha)->getValue());
			}
			if($temp[$cab[0]] != ''){
				$ret[] = $temp;
				$linha++;
			}else{
				$linhaOK = false;
			}
		}
		//print_r($ret);
		foreach ($ret as $r){
			$nota 		= trim($r['nota']);
			$pagamento 	= date('Ymd');
			//$valor 		= str_replace(',', '.', trim($r['valor']));
			$valor		= str_replace(',', '.', trim($r['liquido']));
			$desconto 	= str_replace(',', '.', trim($r['desconto']));
			$antecipado = str_replace(',', '.', trim($r['antecipado']));
			$reposicao	= str_replace(',', '.', trim($r['reposicao']));
			$liquido	= str_replace(',', '.', trim($r['liquido']));
			
			$integradora = 'ANGELONI';
			
			$verificaTitulo = $this->verificaTitulo($nota);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora, $desconto, $liquido);
					if($retInc){
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
		}
		
		return $retorno;
	}
	/*/
	private function processaArquivoTexto($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$handle = fopen($arquivo, "r");
		//echo "$arquivo \n";
		$l = 0;
		if ($handle) {
			while (!feof($handle)) {
				$linha = trim(fgets($handle));
				if (strlen($linha) > 0 ) {
					if($cliente == 'sesi'){
						$temp = $this->processaLinhaSesi($linha, $nomeArquivo);
						if(count($temp) > 0){
							$ret[] = $temp;
						}
					}elseif($cliente == 'walmart'){
						$temp = $this->processaLinhaWM($linha, $nomeArquivo);
						if(count($temp) > 0){
							$ret[] = $temp;
						}
					}
				}
				$l++;
			}
			fclose($handle);
		}else{
			return false;
		}
		
		//$ret = $this->processaLeitura($temp, $data);
		
		return $ret;
	}
	/*/
	private function processaArquivoTexto($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$handle = fopen($arquivo, "r");
		//echo "$arquivo \n";
		$l = 0;
		if ($handle) {
			while (!feof($handle)) {
				$linha = trim(fgets($handle));
				if (strlen($linha) > 0 ) {
					if($cliente == 'sesi'){
						$temp = $this->processaLinhaSesi($linha, $nomeArquivo);
						if(count($temp) > 0){
							$ret[] = $temp;
						}
					}elseif($cliente == 'walmart'){
						$this->processaLinhaWM($linha, $nomeArquivo);
					}
				}
				$l++;
			}
			fclose($handle);
		}else{
			return false;
		}
		
		if($cliente == 'walmart'){
			$ret = $this->processaWM($linha, $nomeArquivo);
		}
		
//nl2br(print_r($this->_linhasTemp));
//print_r($ret);
		//$ret = $this->processaLeitura($temp, $data);
		
		return $ret;
	}
	
	//Novo arquivo do SESI - 28/02/2023
	private function processaLinhaSesi($linha, $nomeArquivo){
		$ret = [];
		$colunas = explode(';', $linha);
		if(strpos($linha, 'PGTO TOTAL') !== false || strpos($linha, 'PGTO PARCIAL') !== false){
			$nota = trim($colunas[1]);
			$pagamento = datas::dataD2S(trim($colunas[2]));
			$valor = str_replace(',', '.', trim($colunas[4]));
			$integradora = 'SESI';
			
			$verificaTitulo = $this->verificaTitulo($nota);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora);
					if($retInc){
						$ret['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$ret['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$ret['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
			
			//print_r($verificaTitulo);
			//echo "$nota \t\t $pagamento \t\t $valor \n";
		}
		
		return $ret;
	}
/*/ -> Arquivo anterior do SESI	
	private function processaLinhaSesi($linha, $nomeArquivo){
		$retorno = [];
		if(strpos($linha, 'Baixado por envio de remessa de pagamento automatizado.') !== false){
			$nota = trim(substr($linha, 50, 10));
			$pagamento = datas::dataD2S(trim(substr($linha, 68, 10)));
			$valor = str_replace(',', '.', trim(substr($linha, 85, 13)));
			$integradora = 'SESI';
			
			$verificaTitulo = $this->verificaTitulo($nota);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora);
					if($retInc){
						$retorno['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
			
			//print_r($verificaTitulo);
			//echo "$nota \t\t $pagamento \t\t $valor \n";
		}
		
		return $retorno;
	}
/*/
	
	/*/
	private function processaLinhaWM($linha, $nomeArquivo){
		$retorno = [];
		//echo "Inicio: ".substr($linha, 0, 2)."  -  ".substr($linha, 87, 1)."<br>\n";
		if(substr($linha, 0, 2) == 'DN' && substr($linha, 87, 1) == '+'){
			$nota = trim(substr($linha, 11, 11));
			$pagamento = trim(substr($linha, 60, 8));
			$pagamento = substr($pagamento, 4, 4).substr($pagamento, 3, 2).substr($pagamento, 0, 2);
			$valor = str_replace(',', '.', trim(substr($linha, 72, 15)));
			$integradora = 'WMS';
			
			$verificaTitulo = $this->verificaTitulo($nota);
			if(count($verificaTitulo) > 0){
				foreach ($verificaTitulo as $titulo){
					//Verifica se o título já foi baixado
					$processado = 'N';
					if($titulo['valor'] == $titulo['pago']){
						$processado = 'S';
					}
					$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora, 0, 0, $processado);
					if($retInc){
						$retorno['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
					}else{
						$retorno['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
					}
				}
			}else{
				$retorno['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
			}
			
			
			//print_r($verificaTitulo);
			//echo "$nota \t\t $pagamento \t\t $valor \n";
		}
		
		return $retorno;
	}
	/*/
	private function processaLinhaWM($linha, $nomeArquivo){
		if(substr($linha, 0, 2) == 'DN' && substr($linha, 87, 1) == '+'){
			$nota = trim(substr($linha, 11, 11));
			$pagamento = trim(substr($linha, 60, 8));
			$pagamento = substr($pagamento, 4, 4).substr($pagamento, 3, 2).substr($pagamento, 0, 2);
			$valor = str_replace(',', '.', trim(substr($linha, 72, 15)));
			$valor = floatval($valor);
			
			$integradora = 'WMS';
			
			if(!isset($this->_linhasTemp[$nota])){
				$temp = [];
				$temp['nota'] 		= $nota;
				$temp['pagamento'] 	= $pagamento;
				$temp['valor'] 		= $valor;
				$temp['integradora']= $integradora;
				
				$this->_linhasTemp[$nota] = $temp;
			}else{
				$this->_linhasTemp[$nota]['valor'] 		+= $valor;
			}
		}
	}
	
	private function processaWM($linha, $nomeArquivo){
		$retorno = [];
		
		if(count($this->_linhasTemp) > 0){
			foreach ($this->_linhasTemp as $linha){
				$nota 		= $linha['nota'];
				$pagamento	= $linha['pagamento'];
				$valor		= $linha['valor'];
				$saldo		= $linha['valor'];
				$integradora= $linha['integradora'];
				
				$verificaTitulo = $this->verificaTitulo($nota);
				if(count($verificaTitulo) > 0){
					foreach ($verificaTitulo as $i => $titulo){
						//Verifica se o título já foi baixado
						$processado = 'N';
						if($titulo['valor'] == $titulo['pago']){
							$processado = 'S';
						}
//if($titulo['duplic'] == 3022959){
//	echo "Valor Titulo: ".$titulo['valor']."  Saldo: ".$saldo."  Count: ".count($verificaTitulo)." I: ".($i+1)." <br>\n";	
//}
						//Caso o valor seja maior e exista mais de um título
						if($titulo['valor'] < $saldo && count($verificaTitulo) > ($i+1)){
//if($titulo['duplic'] == 3022959){
//	echo "Parcial - Valor Titulo: ".$titulo['valor']."  Saldo: ".$saldo."  Count: ".count($verificaTitulo)." I: ".($i+1)." <br>\n";
//}
							$valor = $titulo['valor'];
							$saldo = $saldo - $valor;
						}else{
//if($titulo['duplic'] == 3022959){
//	echo "Nao Parcial <br>\n";
//}
							$valor = $saldo;
						}
						
						$retInc = $this->insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora, 0, 0, $processado);
						if($retInc){
							$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <strong>Encontado</strong>';
						}else{
							$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>Já processado</strong></font>';
						}
					}
				}else{
					$retorno[]['name'] = '<b>Titulo:</b> '.$nota.' <font color="#FF0000"><strong>N&atilde;o encontado</strong></font>';
				}
				
				
				//print_r($verificaTitulo);
				//echo "$nota \t\t $pagamento \t\t $valor \n";
			}
		}
		
		return $retorno;
	}
	
	private function verificaTitulo($nota, $valor = 0, $parcela = ''){
		$ret = [];
		$where = ' 1 = 1 ';
		$sql = "SELECT * FROM PCPREST WHERE ";
		if(!empty($parcela)){
			$where .= " AND PCPREST.PREST = '".$parcela."'";
		}
		if($valor > 0){
			$where .= " AND valor = ROUND($valor,2)";
		}
		
		$where .= " AND DUPLIC = $nota ";

		$sql .= $where;
		
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['duplic'] 	= $row['DUPLIC'];
				$temp['prest'] 		= $row['PREST'];
				$temp['codcob'] 	= $row['CODCOB'];
				$temp['codcli']		= $row['CODCLI'];
				$temp['valor'] 		= $row['VALOR'];
				$temp['vencimento'] = datas::dataMS2S($row['DTVENC']);
				$temp['pago'] 		= $row['VPAGO'] == '' ? 0 :$row['VPAGO'];
				$temp['pagamento'] 	= $row['DTPAG'] == '' ? 0 : datas::dataMS2S($row['DTPAG']);
				
				$ret[] = $temp;
			}
		}
		//print_r($ret);
		return $ret;
	}
	
	private function getDadosTitulo($duplicata, $parcela){
		$ret = [];
		$sql = "SELECT * FROM ".$this->_tabelaIntranet." WHERE duplicata = $duplicata AND parcela = '$parcela'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$ret['codcob'] 		= $row['codcob'];
				$ret['codcli']		= $row['codcli'];
				$ret['valor'] 		= $row['valor'];
				$ret['vencimento'] 	= $row['dtvenc'];
				$ret['pago'] 		= $row['vpago'];
				$ret['pagamento'] 	= $row['dtbaixa'];
				$ret['dtpagoarq'] 	= $row['dtpagoarq'];
				$ret['vpagoarq'] 	= $row['vpagoarq'];
				$ret['dataPagParam']= $row['dataPagParam'];
				$ret['vdesconto']	= $row['vdesconto'];
			}
		}
		
		return $ret;
		
	}
	
	private function insertTitulo($titulo, $pagamento, $valor, $nomeArquivo, $integradora, $desconto = 0, $liquido = 0, $processado = 'N'){
		$hoje = date('Ymd');
		$hash = md5(getUsuario().date('Ymd'));
		$usuario = getUsuario();
		
		//Verifica se o titulo já não está incluido
		$sql = "SELECT * FROM ".$this->_tabelaIntranet." WHERE usuario = '".getUsuario()."' AND duplicata = ".$titulo['duplic']." AND parcela = '".$titulo['prest']."' AND processado = 'S'";
		$rows = query($sql);
		
		if(count($rows) == 0){
			$setProcessado = '';
			if($titulo['pagamento'] > 0){
				$setProcessado = 'S';
			}
			$campos = [];
			$campos['dia'] 			= $hoje;
			$campos['chave'] 		= $hash;
			$campos['arquivo'] 		= $nomeArquivo;
			$campos['usuario'] 		= $usuario;
			$campos['codcli'] 		= $titulo['codcli'];
			$campos['duplicata'] 	= $titulo['duplic'];
			$campos['parcela'] 		= $titulo['prest'];
			$campos['codcob'] 		= $titulo['codcob'];
			$campos['valor'] 		= $titulo['valor'];
			$campos['vpago'] 		= $titulo['pago'];
			$campos['valorPag'] 	= $titulo['pago'];
			$campos['vdesconto'] 	= $desconto;
			$campos['descontoPag'] 	= $desconto;
			$campos['dtvenc'] 		= $titulo['vencimento'];
			$campos['dtbaixa'] 		= $titulo['pagamento'];
			$campos['dataPag'] 		= $titulo['pagamento'];
			$campos['obs'] 			= '';
			$campos['dtpagoarq'] 	= $pagamento;
			$campos['vpagoarq'] 	= $valor;
			$campos['integradora'] 	= $integradora;
			$campos['processado'] 	= $setProcessado;

			$sql = montaSQL($campos, $this->_tabelaIntranet);
//echo "$sql \n\n";
			query($sql);
						
						return true;
		}else{
			return false;
		}
	}
	
	private function ajustaDataPagamento($dataBaixa){
		$sql = "UPDATE ".$this->_tabelaIntranet." SET dataPagParam = $dataBaixa WHERE processado <> 'S'";
		query($sql);
	}
	
	private function upd_gf_titulosbaixados($duplicata, $parcela, $banco, $moeda, $dataPag, $historico, $valor, $desconto){
		//$hash = md5(getUsuario().date('Ymd'));
		$sql = "UPDATE ".$this->_tabelaIntranet." SET
		processado = 'S',
		bancoPag = $banco,
		moedaPag = '$moeda',
		dataPag = $dataPag,
		dtbaixa = $dataPag,
		historicoPag = '$historico',
		descontoPag = $desconto,
		valorPag = $valor,
		usuarioPag = '".getUsuario()."'
		WHERE
		duplicata = $duplicata
		AND parcela = '$parcela'
		AND processado <> 'S'
		";
		
		query($sql);
	}
	private function limpaNaoProcessados(){
		$sql = "DELETE FROM ".$this->_tabelaIntranet." WHERE usuario = '".getUsuario()."' AND processado <> 'S'";
		query($sql);
	}
	
	private function listaBancos($branco = true){
		$tabela = [];
		if($branco){
			$tabela[0][0] = "";
			$tabela[0][1] = "&nbsp;";
		}
		$sql = "SELECT CODBANCO, CODBANCO || ' - ' || NOME AS NOME FROM PCBANCO WHERE PCBANCO.CODBANCO <> '9999' AND TIPOCXBCO <> 'I'  ORDER BY CODBANCO";
		$rows = $this->queryOracle($sql, $this->_trace);
		
		$i = count($tabela);
		foreach ($rows as $row) {
			$tabela[$i][0] = $row[0];
			$tabela[$i][1] = $row[1];
			$i++;
		}
		return $tabela;
	}
	
	private function listaMoedas($branco = true){
		$tabela = [];
		if($branco){
			$tabela[0][0] = "";
			$tabela[0][1] = "&nbsp;";
		}
		$sql = "SELECT * FROM PCMOEDA ";
		$rows = $this->queryOracle($sql, $this->_trace);
		
		$i = count($tabela);
		foreach ($rows as $row) {
			$tabela[$i][0] = $row[0];
			$tabela[$i][1] = $row[0].' - '.$row[1];
			$i++;
		}
		return $tabela;
	}
	
	private function getNomeCliente($codcli){
		$ret = '';
		if(!isset($this->_clientes[$codcli])){
			$sql = "SELECT CLIENTE FROM PCCLIENT WHERE CODCLI = $codcli";
			$rows = $this->queryOracle($sql);
			if(count($rows) > 0){
				$this->_clientes[$codcli] = $rows[0][0];
			}
		}
		$ret = $this->_clientes[$codcli];
		
		return $ret;
	}
	
	private function queryOracle($sql){
		$rows = [];
		if($this->_teste){
			$rows = query5($sql);
		}else{
			$rows = query4($sql, false);
		}
		
		return $rows;
	}
}