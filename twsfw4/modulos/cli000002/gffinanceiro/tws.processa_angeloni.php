<?php
/*
 * Data Criacao: 16/12/2019
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Utilizado para realizar baixa dos títulos de grandes redes que realizam o pagamento via depósito bancário
 * 		      e enviam um arquivo com a relação dos títulos que integram este depósito
 * 			  baseado no processa_baixas
 * 			  específico para os títulos da Angeloni, que possuem um desconto financeiro de 5% em todos os títulos
 * 			  somente o que ficar acima dos 5% serão questionjados como desconto
 *
 * Alterações:
 * 		15/04/19 - Inclusão do processamento das baixas do grupo São João - Neto
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
include_once($config["include"].'phpExcel/PHPExcel.php');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);




class processa_angeloni{
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
		global $pagina;
		set_time_limit(0);
		
		$this->_trace = false;
		$this->_teste = false;
		
		if($this->_teste){
			$this->_tabelaIntranet = 'gf_titulosbaixados_teste';
			$this->_rotinaBaixa = 'testes.baixa_titulo.baixaTitulo';
			$this->_urlBaixa = 'testes.processa_angeloni';
			$this->_arquivoLog = 'processa_baixa_teste_Angeloni_log';
		}else{
			$this->_tabelaIntranet = 'gf_titulosbaixados';
			$this->_rotinaBaixa = 'gffinanceiro.baixa_titulo.baixaTitulo';
			$this->_urlBaixa = 'gffinanceiro.processa_angeloni';
			$this->_arquivoLog = 'processa_baixa_Angeloni_log';
		}
		
		$pagina->addScript('js','cliente000002/processa_baixas_angeloni.js');
		
		//$this->limpaNaoProcessados();
		
		if($this->_teste === true){
			global $config, $db5;
			$db5 = ADONewConnection($config["db5_banco"]);
			$db5->Connect($config["db5_server"], $config["db5_usuario"], $config["db5_senha"], $config["db5_database"]);
			//$db5->debug = true;
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
			if($row['dtbaixa'] == 0 && $dif >= 0){
				$checked = 'checked';
				//				$checked = '';
				if($dif <> 0){
					//$checked = '';
					$abre  = '<font color="#FF0000">';
					$fecha = '</font>';
				}
				if(getUsuario() == 'thiel'){
					$checked = '';
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
				$param['valor']	= round($row['valor'] - ($row['vpagoarq'] + $row['vdesconto']),2);
				//$param['valor']	= '';
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
		$bw->addColuna(array('campo' => 'codcli'	, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$bw->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 400, 'posicao' => 'esquerda'));
		$bw->addColuna(array('campo' => 'duplic'	, 'etiqueta' => 'Duplicata'		, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'centro'));
		$bw->addColuna(array('campo' => 'prest'		, 'etiqueta' => 'Parcela'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$bw->addColuna(array('campo' => 'codcob'	, 'etiqueta' => 'CODCOB'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$bw->addColuna(array('campo' => 'valor'		, 'etiqueta' => 'Valor'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
		$bw->addColuna(array('campo' => 'vencimento', 'etiqueta' => 'Vencimento'	, 'tipo' => 'D', 'width' => 100, 'posicao' => 'centro'));
		$bw->addColuna(array('campo' => 'dtpag'		, 'etiqueta' => 'Dt.Pagamento'	, 'tipo' => 'D', 'width' => 100, 'posicao' => 'esquerda'));
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
		$param['titulo'] = 'Processo de baixa de títulos - ANGELONI.';
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
//print_r($_POST);	
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
			$ret = &ExecMethod($this->_rotinaBaixa, $duplicata, $parcela, $valorArq, $banco, $moeda, $dataPag, $historico, ($desconto+$descontoArq));
			if($ret){
				$this->upd_gf_titulosbaixados($duplicata, $parcela, $banco, $moeda, $dataPag, $historico, $valorArq, ($desconto+$descontoArq));
			}
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
				if(strtoupper($ext) == 'TXT' || strtoupper($ext) == 'XLS' || strtoupper($ext) == 'LSX'){
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
			$param['titulo'] ='Titulos Não Baixados';
			$param['conteudo'] = $this->browser($abertos, 'Titulos Não Baixados');
			$ret .= addCard($param);
		}
		//setTituloPagina('', '');
		
		return $ret;
	}
	
	private function leArquivo($arquivo, $nomeArquivo){
		$ret = [];
		
		putAppVar('arquivo_lido_baixa_titulo', $nomeArquivo);
		$cliente = '';
		if(strpos(strtoupper($nomeArquivo), 'ANGELONI') !== false){
			$cliente = 'angeloni';
			$ret = $this->processaArquivoXLS($arquivo, $nomeArquivo, $cliente);
		}else{
			return 'Arquivo não processado - Uso específico para arquivos Angeloni';
		}
		
		return $ret;
	}
	
	
	
	
	
	/**
	 * Processa os arquivos 
	 *
	 * @param string $arquivo
	 * @param string $nomeArquivo
	 * @param int $cliente
	 * @return array|string
	 */
	private function processaArquivoXLS($arquivo, $nomeArquivo, $cliente){
		$ret = [];
		$retorno = [];
		/*/
		$objReader = new PHPExcel_Reader_Excel5();
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($arquivo);
		$objPHPExcel->setActiveSheetIndex(0);
		/*/
		
		$input_file_type = PHPExcel_IOFactory::identify($arquivo);
		$obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
		$obj_reader->setReadDataOnly(true);
		$objPHPExcel = $obj_reader->load($arquivo);
//		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
		
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
//print_r($ret);die();
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

	
	private function verificaTitulo($nota, $valor = 0, $parcela = ''){
		$ret = [];
		$sql = "SELECT * FROM PCPREST WHERE DUPLIC = $nota";// AND CODCOB = 'DEP'";
		if(!empty($parcela)){
			$sql .= " AND prest = $parcela";
		}
		if($valor > 0){
			$sql .= " AND valor = ROUND($valor,2)";
		}
		//if($nota == 2717654)echo "SQL: $sql \n";
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
		$sql = "SELECT * FROM ".$this->_tabelaIntranet." WHERE duplicata = $duplicata AND parcela = $parcela";
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
		$hash = md5(getUsuario().$hoje);
		$usuario = getUsuario();
		
		//Verifica se o titulo já não está incluido
		$sql = "SELECT * FROM ".$this->_tabelaIntranet." WHERE usuario = '".getUsuario()."' AND duplicata = ".$titulo['duplic']." AND parcela = ".$titulo['prest']." AND processado = 'S'";
		$rows = query($sql);
		
		if(count($rows) == 0){
			$setProcessado = '';
			if($titulo['pagamento'] > 0){
				$setProcessado = 'S';
			}
			$sql = "INSERT INTO ".$this->_tabelaIntranet." (dia, chave, arquivo, usuario, codcli, duplicata, parcela, codcob, valor, vpago,valorPag, vdesconto,descontoPag, dtvenc, dtbaixa,dataPag, obs, dtpagoarq, vpagoarq, integradora, processado)
			VALUES
			( 	'$hoje',
			'$hash',
			'$nomeArquivo',
			'$usuario',
			".$titulo['codcli'].",
						".$titulo['duplic'].",
						".$titulo['prest'].",
						'".$titulo['codcob']."',
						".$titulo['valor'].",
						".$titulo['pago'].",
						".$titulo['pago'].",
						$desconto,
						$desconto,
						".$titulo['vencimento'].",
						".$titulo['pagamento'].",
						".$titulo['pagamento'].",
						'',
						$pagamento,
						$valor,
						'$integradora',
						'$setProcessado'
						)";
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
		AND parcela = $parcela
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
			//			echo "\nSQL: $sql <br>\n\n";
			//			if(strpos($sql, 'SELECT') !== false)
			$rows = query5($sql);
		}else{
			$rows = query4($sql);
		}
		
		return $rows;
	}
}