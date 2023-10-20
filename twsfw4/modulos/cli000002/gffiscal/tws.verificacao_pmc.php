<?php
/*
 * Data Criacao: 03/08/2018	
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Gera planilha EXCEL para verificação do PMC
 * 
 * Alterações:	
 * 				28/01/2021 - Inclusão das colunas de 17,5% de ICMS (RS)
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
include_once ($config['include'].'phpExcel/PHPExcel.php');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class verificacao_pmc{

	var $funcoes_publicas = array(
			'index' 		=> true,
			'geraExcel'		=> true,
			'uploadArquivo'	=> true,
			'processar'		=> true,
			'limpar'		=> true,
			'processarFARMA'=> true,
			'arquivos'		=> true,
	);
	
	// Objeto excel
	private $_excel;
	
	// Arquivo a ser gravado
	private $_arquivo;
	
	//Link
	private $_link;
	
	//Colunas
	private $_colunas;
	
	//Estilos
	private $_estilos;
	
	// Nome do Programa
	private $_programa = '';
	
	//Titulo variavel
	private $_titulo;
	
	//Dados do relatorio
	private $_dados;
	
	//Itens
	private $_itens;
	
	//Grupos Cab
	private $_grupos;
	
	//Quantidade de itens em cada grupo
	private $_quant_itens;
	
	//Indices para comparação
	private $_compara;
	
	//Diretório de upload
	private $_dir_upload;
	
	//Diretorio de processamento
	private $_dir_processo;
	
	function __construct(){
		global $config;
		set_time_limit(0);
		
		$this->setaCampos();
		$this->geraColunas();
		$this->setaEstilos();
		
		$this->_programa = 'verificacao_pmc';
		$this->_titulo = 'Verificação PMC';
		$this->_link = 'index.php?menu='.getModulo().'.'.getClasse().'.';
		
		
		$this->_dir_processo = $config['tempUPD'].'verificacao_pmc/processo/';;
		$this->_dir_upload = $config['tempUPD'].'verificacao_pmc/';
	}
	
	function index($botaoExcel = false){
		global $nl, $config;
		$ret = '';

		$tabela = new tabela_simples(array('condensed'	=> false));
		
		$tabela->addColuna(array('campo' => 'arquivo'	, 'etiqueta' => 'Revista'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$tabela->addColuna(array('campo' => 'data'		, 'etiqueta' => 'Importação', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
		$tabela->addColuna(array('campo' => 'quant'		, 'etiqueta' => 'Itens'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
		$tabela->addColuna(array('campo' => 'botao'		, 'etiqueta' => ''			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$tabela->addColuna(array('campo' => 'botao2'	, 'etiqueta' => ''			, 'tipo' => 'T', 'width' => 110, 'posicao' => 'E'));
		$tabela->addColuna(array('campo' => 'status'	, 'etiqueta' => 'Status'	, 'tipo' => 'T', 'width' => 110, 'posicao' => 'E'));
		$tabela->addColuna(array('campo' => 'botao3'	, 'etiqueta' => ''			, 'tipo' => 'T', 'width' => 110, 'posicao' => 'E'));
		
		$dados = $this->getDadosArquivos();
		
		$tabela->addDados($dados);
		
		$arquivos = '';
		$arquivos .= $tabela;
		
		$param = [];
		$param['acao'] = $this->_link."uploadArquivo";
		$param['nome'] = 'formPMC';
		$param['id']   = 'formPMC';
		$param['enctype'] = true;
		$arquivos = formbase01::form($param, $arquivos);
		
		$param = [];
		$param['titulo'] = 'Arquivos';
		$param['conteudo'] = $arquivos;
		$tabela1 = addCard($param);
		
		$tabela2 = '';
		$tabela2 .= '<br>'.$nl;
		$param = [];
		$param['tamanho'] 	= 'grande';
		$param['cor']		= 'success';
		$param['texto']		= 'Gerar Planilha';
		$param['bloco']		= true;
		$param['onclick']		= "setLocation('".$this->_link."geraExcel')";
		$tabela2 .= formbase01::formBotao($param);
		

		
		if($botaoExcel){
			$tabela2 .= '<br>'.$nl;
			$param = [];
			$param['tamanho'] 	= 'grande';
			$param['cor']		= 'warning';
			$param['texto']		= 'Baixar Planilha';
			$param['bloco']		= true;
			$param['onclick']		= "setLocation('".$config['tempURL']."VERIFICACAO_PMC_".date('d-m-Y').".xlsx')";
			$tabela2 .= formbase01::formBotao($param);
		}

		$param = [];
		$param['titulo'] = 'Geração';
		$param['conteudo'] = $tabela2;
		$tabela2 = addCard($param);
		
		$ret .= '<div class = "row">'.$nl;
		$ret .= '	<div class="col-md-10">'.$nl;
		$ret .= $tabela1;
		$ret .= '	</div>'.$nl;
		$ret .= '	<div class="col-md-2">'.$nl;
		$ret .= $tabela2;
		$ret .= '	</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		
		$param = [];
		$param['titulo'] = $this->_titulo;
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return  $ret;
	}
	
	function schedule($param = []){
		set_time_limit(0);
		$diretorio = dir($this->_dir_upload);
		log::gravaLog('verificacao_pmc_processamento', 'Executando Schedule');
		while($arquivo = $diretorio->read()){
			//echo "Verificando Arquivo: ".$this->_dir_upload."$arquivo <br>\n";
			if($arquivo != '.' && $arquivo != '..' && $arquivo != 'processo'){
				log::gravaLog('verificacao_pmc_processamento', 'Vai processar '.$arquivo);
				if(rename( $this->_dir_upload.$arquivo, $this->_dir_processo.$arquivo )){
					//echo "Arquivo ".$this->_dir_upload.$arquivo." movido para ".$this->_dir_processo.$arquivo."<br>\n";
					log::gravaLog('verificacao_pmc_processamento', 'Moveu '.$arquivo);
				}else{
					log::gravaLog('verificacao_pmc_processamento', 'Não conseguiu mover o arquivo: '.$arquivo);
					//echo "Erro movimentação Arquivo ".$this->_dir_upload.$arquivo." <br>\n";
				}
				log::gravaLog('verificacao_pmc_processamento', 'Vai processar o arquivo: '.$arquivo);
				$this->leArquivo($this->_dir_processo.$arquivo);
				log::gravaLog('verificacao_pmc_processamento', 'Vai apagar o arquivo: '.$arquivo);
				unlink($this->_dir_processo.$arquivo);
				break;
			}
		}
	}
	
	function uploadArquivo(){
//		set_time_limit(0);
//echo $this->_dir_upload."  -- $key - $nome - $extensao - $arquivo<br>\n";
//print_r($_FILES);die();
		if(count($_FILES) > 0){
			foreach ($_FILES as $key => $upd){
				if (is_uploaded_file($_FILES[$key]['tmp_name'])) {
					$nome = $_FILES[$key]['name'];
					$extensao = ltrim( substr( $nome, strrpos( $nome, '.' ) ), '.' );
					$arquivo = substr($key, 4);
					move_uploaded_file($_FILES[$key]['tmp_name'], $this->_dir_upload.$arquivo.'.'.$extensao);
					
//echo $this->_dir_upload."  -- $key - $nome - $extensao - $arquivo<br>\n";
					
					//unlink($_FILES[$key]['tmp_name']);
				}
			}
		}
		
		return $this->index();
	}
	
	function geraExcel(){
		global $config;
		set_time_limit(0);
		$this->_excel = new PHPExcel();
		$this->_excel->getProperties()->setCreator("www.thielws.com.br");
		$this->_arquivo = $config['tempPach'].'VERIFICACAO_PMC_'.date('d-m-Y').'.xlsx';
		
		$this->imprimeExcelCab();
		//Seta filtros automaticos
		$ultima = $this->_colunas[count($this->_colunas) - 2];
//		$this->_excel->getActiveSheet()->setAutoFilter('A2:'.$ultima.'2');
		
		 
		$this->getProdutos();
		$this->getTributacaoSaida();
		$this->getPMC();
		$this->ajustaPrecosRevistas();
		$this->incluiDados();

		$this->grava();

		return $this->index(true);
	}
	
	function processar(){
		$this->schedule();
		
		return $this->index();
	}
	
	function limpar(){
		$operacao = getOperacao();
		$operacoes = ['CMED','ABC','FARMACIA'];
		
		
		if(in_array($operacao, $operacoes)){
			$this->limpaTabelaRevista($operacao);
		}
		
		return $this->index();
	}
	
	
	function processarFARMA(){
		$revista = 'FARMACIA';
		$url = "https://webservice.guiadafarmaciadigital.com.br/webservice/";
		$cnpj = '89735070000100';
		$email = 'andreia.almeida@gauchafarma.com';
		$senha = '175780';
		$pagina = 1;
		
		$dados = $this->getWSfarmacia($url, $cnpj, $email, $senha, $pagina);
		if(count($dados) > 0){
			$this->limpaTabelaRevista($revista);
			foreach ($dados as $dado){
				$pf19 = $dado['pf19'] ?? 0;
				$pmc19 = $dado['pmc19'] ?? 0;
				$this->insertTab($revista, $dado['data'], $dado['ean'], $dado['pf17'], $dado['pf175'], $dado['pf18'], $dado['pmc17'], $dado['pmc175'], $dado['pmc18'], $pf19, $pmc19, $dado['tipo'], $dado['hospital']);
			}
		}
		
		return $this->index();
	}
	//------------------------------------------------------------------------------------------------------------------------- LEITURA EXCEL -------------------------------
	
	private function getDadosArquivos(){
		$ret = [];
		$revistas = array('ABC'=>'ABC Farma','CMED'=>'CMED','FARMACIA'=>'Guia da Farmácia');
		$revistaLida = [];
		
		$sql = "SELECT revista, count(*) quant, (SELECT data FROM gf_controle_pmc arq2 WHERE arq2.data <> '' AND arq2.revista = gf_controle_pmc.revista ORDER BY arq2.data DESC LIMIT 1) ultima FROM gf_controle_pmc WHERE data <> '' GROUP BY revista ORDER BY revista";
		$rows = query($sql);
//echo "$sql <br>\n";
		if(count($rows) > 0){
			foreach ($rows as $row){
				$revistaLida[$row['revista']] = $row['revista'];
				$temp = [];
				$temp['arquivo'] = $revistas[$row['revista']];
				$temp['data'] = $row['ultima'];
				$temp['quant'] = $row['quant'];
				
				$param = [];
				if($row['revista'] == 'FARMACIA'){
					$param['tamanho'] 	= 'pequeno';
					$param['cor']		= 'success';
					$param['texto']		= 'Atualizar';
					$param['bloco']		= true;
					$param['onclick']	= "setLocation('".$this->_link."processarFARMA')";
					$temp['botao'] 		= formbase01::formBotao($param);
				}else{
					$param['nome'] 	= 'upd_'.$row['revista'];
					$temp['botao'] = formbase01::formFile($param);
				}
				//$param['nome'] 	= 'upd_'.$row['revista'];
				//$temp['botao'] = formbase01::formFile($param);

				$temp['status'] = $this->getStatusRevista($row['revista'], $row['quant']);
				
				$param = [];
				
				$param['tamanho'] 	= 'pequeno';
				$param['cor']		= 'warning';
				$param['texto']		= 'Processar';
				$param['bloco']		= true;
				$param['onclick']	= "setLocation('".$this->_link."processar')";
				if(strpos($temp['status'], 'Aguardando') !== false){
					$temp['botao2'] = formbase01::formBotao($param);
				}else{
					$temp['botao2'] = '';
				}
				
				$param = [];
				$param['bloco'] = true;
				$param['texto'] = 'Limpar '.$revistas[$row['revista']];
				$param['cor'] = 'danger';
				$param['tamanho'] 	= 'pequeno';
				$param["onclick"] = "setLocation('" . getLink() . "limpar.".$row['revista']."')";
				$temp['botao3'] = formbase01::formBotao($param);
				
				$ret[] = $temp;
			}
		}
		
		//Verifica se existe as revistas
		foreach ($revistas as $key => $revista){
			if(!isset($revistaLida[$key])){
				$revistaLida[$key] = $key;
				$temp = [];
				$temp['arquivo'] = $revistas[$key];
				$temp['data'] = '';
				$temp['quant'] = 0;
				
				$param = [];
//echo "$key <br>\n";
				if($key == 'FARMACIA'){
					$param['tamanho'] 	= 'pequeno';
					$param['cor']		= 'success';
					$param['texto']		= 'Atualizar';
					$param['bloco']		= true;
					$param['onclick']	= "setLocation('".$this->_link."processarFARMA')";
					$temp['botao'] 		= formbase01::formBotao($param);
				}else{
					$param['nome'] 	= 'upd_'.$key;
					$temp['botao'] = formbase01::formFile($param);
				}
				$temp['status'] = $this->getStatusRevista($key, 0);

				$param = [];
				$param['tamanho'] 	= 'pequeno';
				$param['cor']		= 'warning';
				$param['texto']		= 'Processar';
				$param['bloco']		= true;
				$param['onclick']	= "setLocation('".$this->_link."processar')";
				if(strpos($temp['status'], 'Aguardando') !== false){
					$temp['botao2'] = formbase01::formBotao($param);
				}else{
					$temp['botao2'] = '';
				}
				
				$ret[] = $temp;
				
			}
		}
		
		$temp = [];
		$temp['arquivo'] = '';
		$temp['data'] = '';
		$temp['quant'] = '';
		
		//$param = formbase01::formSendParametros();
		$param = [];
		$param['bloco'] = true;
		$param['texto'] = 'Enviar Arquivos';
		$param['onclick'] = "$('#formPMC').submit()";
		$temp['botao'] = formbase01::formBotao($param);
		$temp['status'] = '';
		$ret[] = $temp;
		
		return $ret;
	}
	
	private function getStatusRevista($revista, $quant){
		$ret = [];
		$ret['ok'] 			= '<span class="label label-success">OK</span>';
		$ret['erro'] 		= '<span class="label label-danger">Sem Dados</span>';
		$ret['processando'] = '<span class="label label-warning">Processando</span>';
		$ret['aguardando'] 	= '<span class="label label-success">Aguardando</span>';
		
		$aguardando = $this->verificaArquivo($this->_dir_upload, $revista);
		$processando = $this->verificaArquivo($this->_dir_processo, $revista);
		
		$status = 'erro';
		if($aguardando === true){
			$status = 'aguardando';
		}elseif($processando === true){
			$status = 'processando';
		}elseif($quant > 0){
			$status = 'ok';
		}
		
		return $ret[$status];
	}
	
	private function verificaArquivo($dir, $revista){
		$ret = false;
		$diretorio = dir($dir);

		while($arquivo = $diretorio->read()){
			if(strpos($arquivo, $revista) !== false){
				$ret = true;
			}
		}
		
		return $ret;
	}
	
	private function leArquivo($arquivo){
		$ret = [];
		$dataArquivo = '';
/*/		$objReader = new PHPExcel_Reader_Excel2007();
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($arquivo);
		$objPHPExcel->setActiveSheetIndex(0);
/*/
		log::gravaLog('verificacao_pmc_processamento', 'Criando objeto excel '.$arquivo);
		$input_file_type = PHPExcel_IOFactory::identify($arquivo);
		$obj_reader = PHPExcel_IOFactory::createReader($input_file_type);
		$obj_reader->setReadDataOnly(true);
		
		log::gravaLog('verificacao_pmc_processamento', 'Lendo excel');
		$objPHPExcel = $obj_reader->load($arquivo);
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
		$max_lin = $objWorksheet->getHighestRow();
		$max_col = $objWorksheet->getHighestColumn();

		if(strpos(strtoupper($arquivo), 'ABC') !== false){
			log::gravaLog('verificacao_pmc_processamento', 'Arquvo ABC');
			$linhaInicio = 2;
			$revista = 'ABC';
			/*/Antigo
			$col['data'] 		= 'AJ';
			$col['ean'] 		= 'AM';
			$col['pf17'] 		= 'M';
			$col['pf18'] 		= 'J';
			$col['pmc17'] 		= 'N';
			$col['pmc18'] 		= 'K';
			$col['tipo'] 		= 'B';
			//$col['hospital']	= '';
			 /*/
			//$col['data'] 		= '';
			$col['ean'] 		= 'A';
			$col['pf17'] 		= 'J';
			$col['pf175'] 		= 'H';
			$col['pf18'] 		= 'F';
			$col['pmc17'] 		= 'K';
			$col['pmc175'] 		= 'I';
			$col['pmc18'] 		= 'G';
			//$col['tipo'] 		= '';
			//$col['hospital']	= '';
			
		}elseif(strpos(strtoupper($arquivo), 'CMED') !== false){
			log::gravaLog('verificacao_pmc_processamento', 'Arquivo CMED');
			$linhaInicio = 1;
			$linhaOK = true;
			while ($linhaOK) {
				$celula = $objPHPExcel->getActiveSheet()->getCell('B'.$linhaInicio);
				$valor = utf8_decode($celula->getValue());
				$linhaInicio++;
				if($valor == 'CNPJ' || $linhaInicio > 50){
					$linhaOK = false;
				}
			}

			$dataArquivo = $this->getDataArquivo($arquivo);
			$revista = 'CMED';
			$col['data'] 		= '';     //'';     
			$col['ean'] 		= 'F';    //'F';    
			$col['pf17'] 		= 'Q';    //'N';    
			$col['pf18'] 		= 'U';    //'R';    
			$col['pmc17'] 		= 'Z';    //'W';    
			$col['pmc18'] 		= 'AD';   //'AA';   
			$col['tipo'] 		= 'AL';   //'AJ';   
			$col['hospital']	= 'AG';   //'AD';   
			$col['pf19'] 		= 'W';    //'R';
			$col['pmc19'] 		= 'AI';    //'W';
			
			$col = $this->procuraColunasCMED($objPHPExcel, $linhaInicio - 1, $max_col);
//print_r($col);			
		}if(strpos(strtoupper($arquivo), 'FARMACIA') !== false || strpos(strtoupper($arquivo), 'GUIA') !== false){
			log::gravaLog('verificacao_pmc_processamento', 'Arquivo FARMACIA');
			$linhaInicio = 2;
			$revista = 'FARMACIA';
			$col['data'] 		= 'F';
			$col['ean'] 		= 'A';
			$col['pf17'] 		= 'M';
			$col['pf175'] 		= 'AC';
			$col['pf18'] 		= 'H';
			$col['pmc17'] 		= 'L';
			$col['pmc175'] 		= 'AD';
			$col['pmc18'] 		= 'I';
			//$col['tipo'] 		= '';
			//$col['hospital']	= '';
		}
		
		log::gravaLog('verificacao_pmc_processamento', 'Limpando tabela revista: '.$revista);
		$this->limpaTabelaRevista($revista);

		if(isset($col)){
			$linhaOK = true;
			while ($linhaOK) {
				$temp = [];
				foreach ($col as $i => $c){
					if(!empty($c)){
						$celula = $objPHPExcel->getActiveSheet()->getCell($c.$linhaInicio);
						$valor = utf8_decode($celula->getValue());
					}
					if($i == 'data' && empty($dataArquivo)) {
						$valor = date('Ymd', PHPExcel_Shared_Date::ExcelToPHP($valor));
					}elseif($i == 'data' && !empty($dataArquivo)){
						$valor = $dataArquivo;
					}
					
					$temp[$i] = $valor;
					//$temp[$c] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, $linha)->getValue());
				}
				if($temp['ean'] != ''){
					//$ret[] = $temp;
					$this->processaItens($revista, $temp);
				}
				if($linhaInicio >= $max_lin){
				//if($linhaInicio >= 100){
					$linhaOK = false;
				}
				$linhaInicio++;
			}

		}else{
			log::gravaLog('verificacao_pmc_processamento', 'Não conseguiu definir uma revista');
		}
		
		log::gravaLog('verificacao_pmc_processamento', 'Finalizando processamento -------------------------------------------------------------------------------');
		unset($objPHPExcel);
		unset($objWorksheet);
		unset($obj_reader);
	}
	
	private function procuraColunasCMED($objPHPExcel, $linha, $max_col){
		$col = [];
		$col['data'] 		= '';
		$colunas = $this->getColunas(99);
		$achouColunaEAN = false;
		
		foreach ($colunas as $coluna){
			$celula = $objPHPExcel->getActiveSheet()->getCell($coluna.$linha);
			$valor = utf8_decode($celula->getValue());
			
			if(trim($valor) == 'PF 17%'){
				$col['pf17'] 		= $coluna;
			}elseif(trim($valor) == 'PF 17,5%'){
				$col['pf175'] 		= $coluna;
			}elseif(trim($valor) == 'PF 18%'){
				$col['pf18'] 		= $coluna;
			}elseif(trim($valor) == 'PF 19%'){
				$col['pf19'] 		= $coluna;
			}elseif(trim($valor) == 'PMC 17%'){
				$col['pmc17'] 		= $coluna;
			}elseif(trim($valor) == 'PMC 17,5%'){
				$col['pmc175'] 		= $coluna;
			}elseif(trim($valor) == 'PMC 18%'){
				$col['pmc18'] 		= $coluna;
			}elseif(trim($valor) == 'PMC 19%'){
				$col['pmc19'] 		= $coluna;
			}elseif(strpos($valor, 'EAN') !== false  && !$achouColunaEAN){
				$col['ean'] 		= $coluna;
				$achouColunaEAN = true;
			}elseif(strpos($valor, 'TARJA') !== false){
				$col['tipo'] 		= $coluna;
			}elseif(strpos($valor, 'HOSPITALAR') !== false){
				$col['hospital'] 		= $coluna;
			}
		}
		
		return $col;
	}
	
	//------------------------------------------------------------------------------------------------------------------------- VO ------------------------------------------
	
	private function limpaTabelaRevista($revista){
		$sql = "DELETE FROM gf_controle_pmc WHERE revista = '$revista'";
		query($sql);
	}
	
	private function ajustaPrecosRevistas(){
		$revistas = array('FARMACIA' => 1,'ABC' => 2,'CMED' => 3);
		
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $i => $dado){
				$ean = $dado['ean'];
				
				$sql = "SELECT * FROM gf_controle_pmc WHERE ean = '$ean'";
//echo "SQL: $sql <br>\n";
				$rows = query($sql);
//if($ean == '7895296034029')
//print_r($rows);
				if(count($rows) > 0){
					foreach ($rows as $row){
						$revista = $row['revista'];
						$indice = $revistas[$revista];
//if($ean == '7895296034029')
//echo "Indice: $indice <br>\n";
						$this->_dados[$i]['pf_17_'.$indice] = $row['pf17'];
						$this->_dados[$i]['pmc_17_'.$indice]= $row['pmc17'];
						
						//03/01/22 - Alteracao do ICMD do RS para 17% (igual a SC)
						//$this->_dados[$i]['pf_175_'.$indice] = $row['pf175'];
						//$this->_dados[$i]['pmc_175_'.$indice]= $row['pmc175'];
						$this->_dados[$i]['pf_175_'.$indice] = $row['pf17'];
						$this->_dados[$i]['pmc_175_'.$indice]= $row['pmc17'];
						
						$this->_dados[$i]['pf_18_'.$indice] = $row['pf18'];
						$this->_dados[$i]['pmc_18_'.$indice]= $row['pmc18'];
						
						$this->_dados[$i]['pf_19_'.$indice] = $row['pf19'];
						$this->_dados[$i]['pmc_19_'.$indice]= $row['pmc19'];
						
						$this->_dados[$i]['tp_'.$indice] 	= $row['tipo'];
						if($indice == 3){
							$this->_dados[$i]['hosp_3'] = $row['hospital'];
						}
//if($ean == '7895296034029')
//print_r($this->_dados[$i]);
					}
				}
			}
		}
	}
	
	private function getDataArquivo($arquivo){
		return "20180804";
	}
	
	private function processaItens($revista, $item){
//print_r($item);die();
		if(count($item) > 0 && !empty($revista)){
//			foreach ($itens as $item){
				//$data	= $item['data'];
				$data = date('d/m/Y');
				$ean	= $item['ean'];
				$pf17	= empty($item['pf17'])  ? 0 : $item['pf17'];
				$pf175	= empty($item['pf175']) ? 0 : $item['pf175'];
				$pf18	= empty($item['pf18'])  ? 0 : $item['pf18'];
				$pmc17	= empty($item['pmc17']) ? 0 : $item['pmc17'];
				$pmc175	= empty($item['pmc175'])? 0 : $item['pmc175'];
				$pmc18	= empty($item['pmc18']) ? 0 : $item['pmc18'];
				
				$pf19	= empty($item['pf19'])  ? 0 : $item['pf19'];
				$pmc19	= empty($item['pmc19']) ? 0 : $item['pmc19'];
				
				if(strpos($pf17, ',') !== false) $pf17 = str_replace(',', '.', $pf17);
				if(strpos($pf175, ',')!== false) $pf175 = str_replace(',', '.', $pf175);
				if(strpos($pf18, ',') !== false) $pf18 = str_replace(',', '.', $pf18);
				if(strpos($pf19, ',') !== false) $pf19 = str_replace(',', '.', $pf19);
				
				if(strpos($pmc17, ',') !== false) $pmc17 = str_replace(',', '.', $pmc17);
				if(strpos($pmc175, ',')!== false) $pmc175= str_replace(',', '.', $pmc175);
				if(strpos($pmc18, ',') !== false) $pmc18 = str_replace(',', '.', $pmc18);
				if(strpos($pmc19, ',') !== false) $pmc19 = str_replace(',', '.', $pmc19);
				
				$tipo	= isset($item['tipo']) ? preg_replace('/[^A-Za-z0-9-]/', '', $item['tipo']) : '';
				$tipo = substr($tipo, 0, 20);
				if(empty($item['hospital'])){
					$hospital = '';
				}elseif(substr($item['hospital'], 0, 1) == 'S'){
					$hospital = 'Sim';
				}else{
				$hospital = 'Nao';
				}
				$this->insertTab($revista, $data, $ean, $pf17, $pf175, $pf18, $pmc17, $pmc175, $pmc18, $pf19, $pmc19, $tipo, $hospital);
//			}
		}
	}
	
	private function insertTab($revista, $data, $ean, $pf17, $pf175, $pf18, $pmc17, $pmc175, $pmc18, $pf19, $pmc19, $tipo, $hospital){
		$sql = "SELECT * FROM gf_controle_pmc WHERE revista = '$revista' AND data = '$data' AND ean = '$ean'";
		$rows = query($sql);

		$campos = [];
		$campos['revista'] = $revista;
		$campos['data'] = datas::dataD2S($data);
		$campos['ean'] = $ean;
		$campos['tipo'] = $tipo;
		$campos['hospital'] = $hospital;
		$campos['pf17'] = $pf17;
		$campos['pf175'] = $pf175;
		$campos['pf18'] = $pf18;
		$campos['pmc17'] = $pmc17;
		$campos['pmc175'] = $pmc175;
		$campos['pmc18'] = $pmc18;
		//PR
		$campos['pf19'] = $pf19;
		$campos['pmc19'] = $pmc19;
		
		
		
		if(count($rows) == 0){
			$sql = montaSQL($campos, 'gf_controle_pmc');
			query($sql);
			//log::gravaLog('verificacao_pmc_processamento', $sql);
		}else{
			$sql = montaSQL($campos, 'gf_controle_pmc', 'UPDATE', "revista = '$revista' AND data = '$data' AND ean = '$ean'");
			query($sql);
			//log::gravaLog('verificacao_pmc_processamento', $sql);
		}
//echo "$sql <br>\n";
	}
	
	//------------------------------------------------------------------------------------------------------------------------- GET -----------------------------------------
	
	private function getProdutos(){
		$sql = "
				SELECT 
				    PCPRODUT.CODPROD,
				    PCPRODUT.DESCRICAO,
				    --PCPRODUT.CODFORNEC,
				    PCFORNEC.FORNECEDOR FORNECEDOR,
				    PCPRODUT.CODFAB,
				    --PCPRODUT.CODMARCA,
				    PCMARCA.MARCA,
				    --PCPRODUT.CODSEC,
				    PCSECAO.DESCRICAO SECAO,
				    --PCPRODUT.CODEPTO,
				    PCDEPTO.DESCRICAO DEPTO,
				    PCPRODUT.OBS2,
				    PCPRODUT.UTILIZAPRECOMAXCONSUMIDOR,
				    PCPRODUT.CODAUXILIAR,
				    PCPRODUT.NBM,
				    PCPRODUT.CODNCMEX
				FROM 
				    PCPRODUT,
				    PCFORNEC,
				    PCMARCA,
				    PCSECAO,
				    PCDEPTO
				WHERE 
				    PCPRODUT.CODFORNEC = PCFORNEC.CODFORNEC (+)
				    AND PCPRODUT.CODMARCA = PCMARCA.CODMARCA (+)
				    AND PCPRODUT.CODSEC = PCSECAO.CODSEC (+)
				    AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO (+)
					AND PCPRODUT.CODEPTO = 1
					--AND PCPRODUT.OBS2 <> 'FL'
					 AND NOT (PCPRODUT.DTEXCLUSAO IS NOT NULL)
--and PCPRODUT.CODPROD < 200
				ORDER BY
					PCPRODUT.DESCRICAO
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cod = $row['CODPROD'];
				$this->geraMatriz($cod);
				
				$this->_dados[$cod]['codigo'	] = $cod;
				$this->_dados[$cod]['fabrica'	] = $row['CODFAB'];
				$this->_dados[$cod]['descricao'	] = $row['DESCRICAO'];
				$this->_dados[$cod]['fornecedor'] = $row['FORNECEDOR'];
				$this->_dados[$cod]['marca'		] = $row['MARCA'];
				$this->_dados[$cod]['secao'		] = $row['SECAO'];
				$this->_dados[$cod]['depto'		] = $row['DEPTO'];
				if($row['OBS2'] != 'FL'){
					$this->_dados[$cod]['ativo'		] = 'Ativo';
				}else{
					$this->_dados[$cod]['ativo'		] = 'Fora de Linha';
				}
				if($row['UTILIZAPRECOMAXCONSUMIDOR'] == 'S'){
					$this->_dados[$cod]['tributa'	] = 'PMC MONITORADO';
				}else{
					$this->_dados[$cod]['tributa'	] = 'PMC LIBERADO';
				}
				$this->_dados[$cod]['ean'		] = $row['CODAUXILIAR'];
				$this->_dados[$cod]['ncm'		] = $row['NBM'];
			}
		}
	}
	
	private function getTributacaoSaida(){
		$sql = "
				SELECT 
				    PCTABPR.CODPROD,
				    PCTABPR.NUMREGIAO,
				    PCTRIBUT.MENSAGEM
				FROM 
				    PCTRIBUT,
				    PCTABPR
				WHERE 
				    PCTABPR.CODST = PCTRIBUT.CODST (+)
				    AND PCTABPR.NUMREGIAO IN (1,2,6)
					AND PCTABPR.CODPROD IN (SELECT PCPRODUT.CODPROD FROM PCPRODUT WHERE PCPRODUT.CODEPTO = 1)
--and PCTABPR.CODPROD < 200
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cod = $row['CODPROD'];
				$regiao = $row['NUMREGIAO'];
				$msg = $row['MENSAGEM'];
				
				//$this->geraMatriz($cod);
				if(isset($this->_dados[$cod])){
					$this->_dados[$cod]['tab'.$regiao] = $msg;
				}
			}
		}
	}
	
	private function getPMC(){
		$sql = "
				SELECT 
				    PCTABMEDABCFARMA.CODPROD,
				    PCTABMEDABCFARMA.UF,
				    PCTABMEDABCFARMA.PRECOMAXCONSUM,
					PRECOFABRICA
				FROM 
				    PCTABMEDABCFARMA 
				WHERE 
				    UF IN ('RS','SC','PR')
					AND PCTABMEDABCFARMA.CODPROD IN (SELECT PCPRODUT.CODPROD FROM PCPRODUT WHERE PCPRODUT.CODEPTO = 1)
--AND  PCTABMEDABCFARMA.CODPROD < 200
				ORDER BY 
				    CODPROD,UF
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cod = $row['CODPROD'];
				$uf = $row['UF'];
				$preco = $row['PRECOMAXCONSUM'] == '' ? 0 : $row['PRECOMAXCONSUM'];
				$pf = $row['PRECOFABRICA'] == '' ? 0 : $row['PRECOFABRICA'];
				
				if(isset($this->_dados[$cod])){
					$this->_dados[$cod]['pmc_'.$uf] = $preco;
					$this->_dados[$cod]['pfgf_'.$uf] = $pf;
				}
			}
		}
	}
	
	private function getWSfarmacia($url, $cnpj, $email, $senha, $pagina){
		$ret = [];
		
		$primeira = json_decode($this->acessaWSfarmacia($url, $cnpj, $email, $senha, $pagina, $cnpj));
		log::gravaLog('verificacao_pmc_processamento', print_r($primeira,true));
		$total_paginas = $primeira->total_paginas ?? 0;
		
		if($total_paginas > 0){
			$data = datas::dataD2S($primeira->data_atualizacao);
			
			$produtos = $primeira->data;
			foreach ($produtos as $produto){
				$ret[] = $this->separaProdutoFarmacia($produto, $data);
			}
			
			if($total_paginas > 1){
				for($i = 2; $i <= $total_paginas; $i++){
					$dados = json_decode($this->acessaWSfarmacia($url, $cnpj, $email, $senha, $i));
					$produtos = $dados->data;
	//echo "$i Quant: ".count($produtos)."<br>\n";
					foreach ($produtos as $produto){
						$ret[] = $this->separaProdutoFarmacia($produto, $data);
					}
				}
			}
		}else{
			addPortalMensagem('Houve algum problema ao baixar a revista, favor entrar em contato com a equipe de TI.', 'error');
			$erro = $primeira->error_message ?? '';
			if(!empty($erro)){
				addPortalMensagem($erro, 'error');
			}
		}
		
		return $ret;
	}
	
	private function acessaWSfarmacia($url, $cnpj, $email, $senha, $pagina){
		$curl = curl_init($url);
		$data = "cnpj_cpf=$cnpj&email=$email&senha=$senha&pagina=$pagina&cnpj_sh=$cnpj";
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$headers = array(
				"Content-Type: application/x-www-form-urlencoded",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		
		$resp = curl_exec($curl);
		curl_close($curl);
//print_r($resp);		
		return $resp;
	}
	
	private function separaProdutoFarmacia($produto, $data){
		$ret = [];
		
		$ret['revista'] = 'FARMACIA';
		$ret['data'] 	= $produto->DATA_VIGENCIA;
		$ret['ean'] 	= $produto->EAN;
		$ret['tipo'] 	= $produto->DESCRICAO_TARJA;
		$ret['hospital']= '';
		$ret['pf17'] 	= $produto->PRECO_FABRICA_17;
		$ret['pf175'] 	= $produto->PRECO_FABRICA_175;
		$ret['pf18'] 	= $produto->PRECO_FABRICA_18;
		$ret['pmc17'] 	= $produto->PRECO_MAXIMO_17;
		$ret['pmc175'] 	= $produto->PRECO_MAXIMO_175;
		$ret['pmc18'] 	= $produto->PRECO_MAXIMO_18;
		
		//PR
		$ret['pf19'] 	= $produto->PRECO_FABRICA_19;
		$ret['pmc19'] 	= $produto->PRECO_MAXIMO_19;
		
//print_r($produto);die();
		
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------------------------------------- EXCEL ---------------------------------------
	
	private function incluiDados(){
		$linha = 3;

		foreach ($this->_dados as $dado){
			foreach ($this->_itens as $i => $item){
				$chave = $i;
				if(!empty($item[0])){
					$chave = $item[0];
				}
				if(strpos($chave, 'dif_gf') !== false){
					$comp = substr($chave, -1);
					switch ($comp) {
						case 1:
//							$col1 = $this->_colunas[$this->_compara['pmc_SC']];
//							$col2 = $this->_colunas[$this->_compara['pmc_17_1']];
							break;
						case 2:
							$col1 = $this->_colunas[$this->_compara['pmc_RS']];
							$col2 = $this->_colunas[$this->_compara['pmc_17_1']];
							break;
						case 3:
							$col1 = $this->_colunas[$this->_compara['pmc_PR']];
							$col2 = $this->_colunas[$this->_compara['pmc_19_1']];
							break;
						case 4:
//							$col1 = $this->_colunas[$this->_compara['pmc_SC']];
//							$col2 = $this->_colunas[$this->_compara['pmc_17_2']];
							break;
						case 5:
							$col1 = $this->_colunas[$this->_compara['pmc_RS']];
							$col2 = $this->_colunas[$this->_compara['pmc_17_2']];
							break;
						case 6:
							$col1 = $this->_colunas[$this->_compara['pmc_PR']];
							$col2 = $this->_colunas[$this->_compara['pmc_19_2']];
							break;
						case 7:
//							$col1 = $this->_colunas[$this->_compara['pmc_SC']];
//							$col2 = $this->_colunas[$this->_compara['pmc_17_3']];
							break;
						case 8:
							$col1 = $this->_colunas[$this->_compara['pmc_RS']];
							$col2 = $this->_colunas[$this->_compara['pmc_17_3']];
							break;
						case 9:
							$col1 = $this->_colunas[$this->_compara['pmc_PR']];
							$col2 = $this->_colunas[$this->_compara['pmc_19_3']];
							break;
					}
					$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$i].$linha, '='.$col1.$linha.'-'.$col2.$linha);
				}elseif(strpos($chave, 'dif_rev') !== false && $chave != 'dif_rev_10'){
					$comp = substr($chave, -1);
					switch ($comp) {
						case 1:
							$col1 = $this->_colunas[$this->_compara['pmc_17_1']];
							$col2 = $this->_colunas[$this->_compara['pmc_17_2']];
							break;
						case 2:
//							$col1 = $this->_colunas[$this->_compara['pmc_175_1']];
//							$col2 = $this->_colunas[$this->_compara['pmc_175_2']];
							break;
						case 3:
							$col1 = $this->_colunas[$this->_compara['pmc_19_1']];
							$col2 = $this->_colunas[$this->_compara['pmc_19_2']];
							break;
						case 4:
							$col1 = $this->_colunas[$this->_compara['pmc_17_2']];
							$col2 = $this->_colunas[$this->_compara['pmc_17_3']];
							break;
						case 5:
//							$col1 = $this->_colunas[$this->_compara['pmc_175_2']];
//							$col2 = $this->_colunas[$this->_compara['pmc_175_3']];
							break;
						case 6:
							$col1 = $this->_colunas[$this->_compara['pmc_19_2']];
							$col2 = $this->_colunas[$this->_compara['pmc_19_3']];
							break;
						case 7:
							$col1 = $this->_colunas[$this->_compara['pmc_17_1']];
							$col2 = $this->_colunas[$this->_compara['pmc_17_3']];
							break;
						case 8:
//							$col1 = $this->_colunas[$this->_compara['pmc_175_1']];
//							$col2 = $this->_colunas[$this->_compara['pmc_175_3']];
							break;
						case 9:
							$col1 = $this->_colunas[$this->_compara['pmc_19_1']];
							$col2 = $this->_colunas[$this->_compara['pmc_19_3']];
							break;
					}
					if($comp <> 10){
						$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$i].$linha, '='.$col1.$linha.'-'.$col2.$linha);
					}
				}elseif(strpos($chave, 'dif_pfgf') !== false){
					// Diferença do Preço Fábrica
					$comp = substr($chave, -1);
					switch ($comp) {
						case 1:
//							$col1 = $this->_colunas[$this->_compara['pfgf_SC']];
//							$col2 = $this->_colunas[$this->_compara['pf_17_1']];
							break;
						case 2:
							$col1 = $this->_colunas[$this->_compara['pfgf_RS']];
							$col2 = $this->_colunas[$this->_compara['pf_17_1']];
							break;
						case 3:
							$col1 = $this->_colunas[$this->_compara['pfgf_PR']];
							$col2 = $this->_colunas[$this->_compara['pf_19_1']];
							break;
						case 4:
//							$col1 = $this->_colunas[$this->_compara['pfgf_SC']];
//							$col2 = $this->_colunas[$this->_compara['pf_17_2']];
							break;
						case 5:
							$col1 = $this->_colunas[$this->_compara['pfgf_RS']];
							$col2 = $this->_colunas[$this->_compara['pf_17_2']];
							break;
						case 6:
							$col1 = $this->_colunas[$this->_compara['pfgf_PR']];
							$col2 = $this->_colunas[$this->_compara['pf_19_2']];
							break;
						case 7:
//							$col1 = $this->_colunas[$this->_compara['pfgf_SC']];
//							$col2 = $this->_colunas[$this->_compara['pf_17_3']];
							break;
						case 8:
							$col1 = $this->_colunas[$this->_compara['pfgf_RS']];
							$col2 = $this->_colunas[$this->_compara['pf_17_3']];
							break;
						case 9:
							$col1 = $this->_colunas[$this->_compara['pfgf_PR']];
							$col2 = $this->_colunas[$this->_compara['pf_19_3']];
							break;
					}
					$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$i].$linha, '='.$col1.$linha.'-'.$col2.$linha);
				}else{
					$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$i].$linha, $dado[$chave]);
//echo $this->_colunas[$i].$linha." - ".$dado[$chave]." - Chave: $chave<br>\n";
				}
				
			}
			$linha++;
		}
		
		//Pinta os PMC GF de azul
		$estilo = $this->_estilos['azul'];
		$col1 = $this->_colunas[$this->_compara['pmc_RS']];

		$col6 = $this->_colunas[$this->_compara['pfgf_PR']]; // Preço Fábrica Gauchafarma
		
		$fim = count($this->_dados) +2;
		$this->_excel->getActiveSheet()->getStyle($col1.'1:'.$col6.$fim)->applyFromArray($estilo);
	}
	
	private function imprimeExcelCab(){
		//Cab1
		$estilo1 = $this->_estilos['titulo'];
		$linha = 1;
		$colunaIni = 0;
		foreach ($this->_grupos as $i => $grupo){
			$colunaFim = $colunaIni + $this->_quant_itens[$i] - 1;
			$this->_excel->getActiveSheet()->mergeCells($this->_colunas[$colunaIni].$linha.':'.$this->_colunas[$colunaFim].$linha);

			$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$colunaIni].$linha, $grupo);
			$this->_excel->getActiveSheet()->getColumnDimension($this->_colunas[$colunaIni])->setAutoSize(true);
			$this->_excel->getActiveSheet()->getStyle($this->_colunas[$colunaIni].$linha)->applyFromArray($estilo1);
			
			$colunaIni += $this->_quant_itens[$i];
		}
		
		
		$linha = 2;
		foreach ($this->_itens as $coluna => $item){
			$this->_excel->getActiveSheet()->setCellValue($this->_colunas[$coluna].$linha, $item[2]);
			$this->_excel->getActiveSheet()->getColumnDimension($this->_colunas[$coluna])->setAutoSize(true);
			$this->_excel->getActiveSheet()->getStyle($this->_colunas[$coluna].$linha)->applyFromArray($estilo1);
		}
	}
	
	private function grava(){
		$objWriter = PHPExcel_IOFactory::createWriter($this->_excel, 'Excel2007');
		$grava = $objWriter->save($this->_arquivo);
	}
	
	//------------------------------------------------------------------------------------------------------------------------- UTEIS ---------------------------------------
	private function geraMatriz($cod){
		if(!isset($this->_dados[$cod])){
			$temp = [];
			foreach ($this->_itens as $i => $item){
				$chave = $i;
				if(!empty($item[0])){
					$chave = $item[0];
				}
				if(strpos($chave, 'pmc') !== false || strpos($chave, 'dif_') !== false || strpos($chave, 'pf_') !== false){
					$temp[$chave] = 0;
				}else{
					$temp[$chave] = '';
				}
			}
			
			$this->_dados[$cod] = $temp;
		}
	}
	
	private function contaItensGrupo(){
		$ret = [];
		foreach ($this->_itens as $item){
			if(!isset($ret[$item[1]])){
				$ret[$item[1]] = 0;
			}
			$ret[$item[1]]++;
		}
		
		return $ret;
	}
	
	private function setaCampos(){
		$grupo = 0;
		$this->_itens[] = array('codigo'	, $grupo,'Código');
		$this->_itens[] = array('fabrica'	, $grupo,'Cód. Fábrica');
		$this->_itens[] = array('descricao'	, $grupo,'Descrição');
		$this->_itens[] = array('fornecedor', $grupo,'Fornec');
		$this->_itens[] = array('marca'		, $grupo,'Marca');
		$this->_itens[] = array('secao'		, $grupo,'Seção');
		$this->_itens[] = array('depto'		, $grupo,'Depto');
		$this->_itens[] = array('ativo'		, $grupo,'LIB COMP');
		$this->_itens[] = array('tributa'	, $grupo,'Trib');
		$this->_itens[] = array('ean'		, $grupo,'EAN');
		$this->_itens[] = array('ncm'		, $grupo,'NCM');
		
		$grupo++;
		$this->_itens[] = array('pf_17_1'	, $grupo,'PF 17%');		$this->_compara['pf_17_1'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_17_1'	, $grupo,'PMC 17%');	$this->_compara['pmc_17_1'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pf_175_1'	, $grupo,'PF 17%');		$this->_compara['pf_175_1'	] = count($this->_itens) - 1; // RS 17,5%
//		$this->_itens[] = array('pmc_175_1'	, $grupo,'PMC 17%');	$this->_compara['pmc_175_1'	] = count($this->_itens) - 1; // RS 17,5%
//		$this->_itens[] = array('pf_18_1'	, $grupo,'PF 18%');		$this->_compara['pf_18_1'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pmc_18_1'	, $grupo,'PMC 18%');	$this->_compara['pmc_18_1'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pf_19_1'	, $grupo,'PF 19%');		$this->_compara['pf_19_1'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_19_1'	, $grupo,'PMC 19%');	$this->_compara['pmc_19_1'	] = count($this->_itens) - 1;
		$this->_itens[] = array('tp_1'		, $grupo,'TP PRC');
		
		$grupo++;
		$this->_itens[] = array('pf_17_2'	, $grupo,'PF 17%');		$this->_compara['pf_17_2'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_17_2'	, $grupo,'PMC 17%');	$this->_compara['pmc_17_2'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pf_175_2'	, $grupo,'PF 17%');	$this->_compara['pf_175_2'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pmc_175_2'	, $grupo,'PMC 17%');	$this->_compara['pmc_175_2'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pf_18_2'	, $grupo,'PF 18%');		$this->_compara['pf_18_2'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pmc_18_2'	, $grupo,'PMC 18%');	$this->_compara['pmc_18_2'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pf_19_2'	, $grupo,'PF 19%');		$this->_compara['pf_19_2'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_19_2'	, $grupo,'PMC 19%');	$this->_compara['pmc_19_2'	] = count($this->_itens) - 1;
		$this->_itens[] = array('tp_2'		, $grupo,'TP PRC');
		
		$grupo++;
		$this->_itens[] = array('pf_17_3'	, $grupo,'PF 17%');		$this->_compara['pf_17_3'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_17_3'	, $grupo,'PMC 17%');	$this->_compara['pmc_17_3'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pf_175_3'	, $grupo,'PF 17%');	$this->_compara['pf_175_3'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pmc_175_3'	, $grupo,'PMC 17%');	$this->_compara['pmc_175_3'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pf_18_3'	, $grupo,'PF 18%');		$this->_compara['pf_18_3'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pmc_18_3'	, $grupo,'PMC 18%');	$this->_compara['pmc_18_3'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pf_19_3'	, $grupo,'PF 19%');		$this->_compara['pf_19_3'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_19_3'	, $grupo,'PMC 19%');	$this->_compara['pmc_19_3'	] = count($this->_itens) - 1;
		$this->_itens[] = array('tp_3'		, $grupo,'TP PRC');
		$this->_itens[] = array('hosp_3'	, $grupo,'REST. HOSP');
		
		$grupo++;
		$this->_itens[] = array('dif_rev_1', $grupo,'Guia X ABC (17%)');
//		$this->_itens[] = array('dif_rev_2', $grupo,'Guia X ABC (17%)');
//		$this->_itens[] = array('dif_rev_3', $grupo,'Guia X ABC (18%)');
		$this->_itens[] = array('dif_rev_3', $grupo,'Guia X ABC (19%)');
		
		$this->_itens[] = array('dif_rev_4', $grupo,'ABC X CMED (17%)');
//		$this->_itens[] = array('dif_rev_5', $grupo,'ABC X CMED (17%)');
//		$this->_itens[] = array('dif_rev_6', $grupo,'ABC X CMED (18%)');
		$this->_itens[] = array('dif_rev_6', $grupo,'ABC X CMED (18%)');
		
		$this->_itens[] = array('dif_rev_7', $grupo,'GUIA X CMED (17%)');
//		$this->_itens[] = array('dif_rev_8', $grupo,'GUIA X CMED (17%)');
//		$this->_itens[] = array('dif_rev_9', $grupo,'GUIA X CMED (18%)');
		$this->_itens[] = array('dif_rev_9', $grupo,'GUIA X CMED (19%)');
		$this->_itens[] = array('dif_rev_10', $grupo,'OBSERVAÇÃO REVISTAS');
		
		$grupo++;
//		$this->_itens[] = array('pmc_SC'	, $grupo,'PMC SC  (17%)');	$this->_compara['pmc_SC'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_RS'	, $grupo,'PMC RS (17%)');	$this->_compara['pmc_RS'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pmc_PR'	, $grupo,'PMC PR (19%)');	$this->_compara['pmc_PR'	] = count($this->_itens) - 1;
//		$this->_itens[] = array('pfgf_SC'	, $grupo,'PF SC Gauchafarma');	$this->_compara['pfgf_SC'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pfgf_RS'	, $grupo,'PF RS Gauchafarma');	$this->_compara['pfgf_RS'	] = count($this->_itens) - 1;
		$this->_itens[] = array('pfgf_PR'	, $grupo,'PF PR Gauchafarma');	$this->_compara['pfgf_PR'	] = count($this->_itens) - 1;
		
		$grupo++;
		$this->_itens[] = array('dif_gf_1', $grupo,'GF x GUIA SC (17%)');
//		$this->_itens[] = array('dif_gf_2', $grupo,'GF x GUIA RS (17%)');
		$this->_itens[] = array('dif_gf_3', $grupo,'GF x GUIA PR (19%)');
		
		$this->_itens[] = array('dif_gf_4', $grupo,'GF x ABC SC (17%)');
//		$this->_itens[] = array('dif_gf_5', $grupo,'GF x ABC RS (17%)');
		$this->_itens[] = array('dif_gf_6', $grupo,'GF x ABC PR (19%)');
		
		$this->_itens[] = array('dif_gf_7', $grupo,'GF x CMED SC (17%)');
//		$this->_itens[] = array('dif_gf_8', $grupo,'GF x CMED RS (17%)');
		$this->_itens[] = array('dif_gf_9', $grupo,'GF x CMED PR (19%)');
		
		$this->_itens[] = array('obs_rev' , $grupo,'OBSERVAÇÃO VALOR  2301 X REVISTAS e TAB FORN');
		
		$grupo++;
		$this->_itens[] = array('dif_pfgf_1', $grupo,'GF x GUIA SC (17%)');
//		$this->_itens[] = array('dif_pfgf_2', $grupo,'GF x GUIA RS (17%)');
		$this->_itens[] = array('dif_pfgf_3', $grupo,'GF x GUIA PR (19%)');
		
		$this->_itens[] = array('dif_pfgf_4', $grupo,'GF x ABC SC (17%)');
//		$this->_itens[] = array('dif_pfgf_5', $grupo,'GF x ABC RS (17%)');
		$this->_itens[] = array('dif_pfgf_6', $grupo,'GF x ABC PR (19%)');
		
		$this->_itens[] = array('dif_pfgf_7', $grupo,'GF x CMED SC (17%)');
//		$this->_itens[] = array('dif_pfgf_8', $grupo,'GF x CMED RS (17%)');
		$this->_itens[] = array('dif_pfgf_9', $grupo,'GF x CMED PR (19%)');
		
		$grupo++;
		$this->_itens[] = array('tab1'		, $grupo,'TAB 1 RS>RS');
//		$this->_itens[] = array('tab2'		, $grupo,'TAB 2 RS>SC');
		$this->_itens[] = array('tab6'		, $grupo,'TAB 6 RS>PR');
		$this->_itens[] = array('obsTrib'	, $grupo,'OBSERVAÇÃO TRIBUTAÇÃO');
		$this->_itens[] = array('obs'		, $grupo,'OBSERVAÇÃO');
		
		$this->_grupos[] = 'DADOS ROTINA 203';
		$this->_grupos[] = 'GUIA FCIA';
		$this->_grupos[] = 'ABC FARMA';
		$this->_grupos[] = 'CMED';
		$this->_grupos[] = 'DIFERENÇA ENTRE REVISTAS';
		$this->_grupos[] = 'PMC ROTINA 2301';
		$this->_grupos[] = 'DIFERENÇA PMC ENTRE CADASTRO GF x REVISTAS e TAB FORN';
		$this->_grupos[] = 'DIFERENÇA PF ENTRE CADASTRO GF x REVISTAS';
		$this->_grupos[] = 'TRIBUTAÇÃO SAÍDA GF (8028)';
		
		$this->_quant_itens = $this->contaItensGrupo();
		
	}
	
	private function geraColunas(){
		$quant = count($this->_itens) + 1;
		$this->_colunas = [];
		$col = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$q = strlen($col);
		$add = '';
		$colAdd = 0;
		$indCol = 0;
		for($i=0;$i<$quant;$i++){
			if($indCol > ($q-1)){
				$indCol = 0;
				$add = substr($col, $colAdd,1);
				$colAdd++;
			}
			$this->_colunas[] = $add.substr($col, $indCol,1);
			$indCol++;
		}
//print_r($this->_colunas);
	}
	
	private function getEstilo($param){
		$estilo = [];
		if(isset($param['horizontal'])){
			if($param['horizontal'] == 'C'){
				$estilo['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
			}
		}
		if(isset($param['vertical'])){
			if($param['vertical'] == 'C'){
				$estilo['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_CENTER;
			}
		}
		if(isset($param['negrito']) && $param['negrito'] == true){
			$estilo['font']['bold'] = true;
		}
		if(isset($param['letraCor'])){
			$estilo['font']['color'] = array('rgb' => $param['letraCor']);
		}
		if(isset($param['letraTamanho'])){
			$estilo['font']['size'] = $param['letraTamanho'];
		}
		if(isset($param['letraNome'])){
			$estilo['font']['name'] = $param['letraNome'];
		}
		
		if(isset($param['borda']) && $param['borda'] == true){
			$estilo['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_THIN;
		}
		
		if(isset($param['celulaCor'])){
			$estilo['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
			$estilo['fill']['color'] = array('rgb' => $param['celulaCor']);
		}
		return $estilo;
	}
	
	private function setaEstilos(){
		$param = [];
		$param['vertical'] = 'C';
		$param['horizontal'] = 'C';
		$param['negrito'] = true;
		$param['borda'] = true;
		//$param['celulaCor'] = 'FFFF00';
		$this->_estilos['titulo'] = $this->getEstilo($param);
		
		$param = [];
		//$param['vertical'] = 'C';
		//$param['horizontal'] = 'C';
		//$param['negrito'] = true;
		//$param['borda'] = true;
		$param['celulaCor'] = 'D9E2F3';
		$this->_estilos['azul'] = $this->getEstilo($param);
		
		$param = [];
		$param['borda'] = true;
		$this->_estilos['normal'] = $this->getEstilo($param);
		
		$param = [];
		$param['borda'] = true;
		$param['letraTamanho'] = 8;
		$this->_estilos['normalPequeno'] = $this->getEstilo($param);
		
		$param = [];
		$param['vertical'] = 'C';
		$param['horizontal'] = 'C';
		$param['borda'] = true;
		$this->_estilos['normalCentralizado'] = $this->getEstilo($param);
		
		$param = [];
		$param['vertical'] = 'C';
		$param['horizontal'] = 'C';
		$param['negrito'] = true;
		$param['borda'] = true;
		$this->_estilos['negritoCentralizado'] = $this->getEstilo($param);
	}

	private function getColunas($quant){
		$colunas = [];
		$col = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$q = strlen($col);
		$add = '';
		$colAdd = 0;
		$indCol = 0;
		for($i=0;$i<=$quant;$i++){
			if($indCol > ($q-1)){
				$indCol = 0;
				$add = substr($col, $colAdd,1);
				$colAdd++;
			}
			$colunas[] = $add.substr($col, $indCol,1);
			$indCol++;
		}
		return $colunas;
	}
}