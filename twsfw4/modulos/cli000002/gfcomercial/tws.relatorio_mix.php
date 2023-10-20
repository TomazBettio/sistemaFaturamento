<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class relatorio_mix{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	//Indica se é teste
	private $_teste;
	
	//Indica se deve enviar email para os ERC quando for teste
	private $_testeERC;
	
	var $_programa;
	var $_titulo;
	var $_totais;
	var $_medias;
	var $_email_geral;
	
	var $_lista_gd = [];
	
	var $_schedule_excluidos;
	var $_schedule_ol;
	var $_schedule_cadastro;
	
	//Dados dos ERCs
	private $_erc = [];
	
	//Dados dos GDs
	private $_gd = [];
	
	//Dados dos clientes
	private $cliente = [];
	
	//Nr clientes totais ou clientes atendidos?
	private $_totalClientes;
	
	//Quantidade de clientes dos ERCs
	private $_quantClientesERC;
	
	//Venda PDA
	private $_vendaPDA = [];
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		$this->_testeERC = false;
		
		$this->_programa = 'relatorio_mix';
		$this->_titulo = 'Relatório Mix';
		
		$this->_totalClientes = 'total';
		
		if(false){
			
			//$meses = '1=Janeiro;2=Fevereiro;3=Março;4=Abril;5=Maio;6=Junho;7=Julho;8=Agosto;9=Setembro;10=Outubro;11=Novembro;12=Dezembro';
			//$OL = '1=Sem OL;2=Somente OL;3=Ambos';
			//$niveis = '1=Cliente;2=ERC;3=GD';
			//$cadastro = '1=Cadastro;2=Pedido';
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'			, 'variavel' => 'dt_ini'			,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'			, 'variavel' => 'dt_fim'			,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'OL'	        	, 'variavel' => 'ol'	    	,'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => $OL));
			//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Fonte do ERC'	, 'variavel' => 'cadastro'		,'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => $cadastro));
			//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Nível'	        , 'variavel' => 'nivel'	    	,'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => $niveis));
			//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'ERCS Ecluidos'	, 'variavel' => 'excluidos'		,'tipo' => 'T', 'tamanho' => '200', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '7', 'pergunta' => 'Fornecedores'		, 'variavel' => 'FORNECEDORES'	,'tipo' => 'T', 'tamanho' => '200', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			//$help = 'Indica se a média do ERC deve ser calculada em função do número total de clientes cadastrados para seu código ou somente para a quantidade de clientes positivados.';
			//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '8', 'pergunta' => 'Média por'			, 'variavel' => 'MEDIA'	,'tipo' => 'T', 'tamanho' => '200', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => $help, 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'total=Nr. Total de Clientes;positivados=Nr.Clientes Positivados'));
		}
		
		$this->_totais = array();
		$this->_medias = array();
		
		$this->_email_geral = '';
		$this->_schedule_excluidos = '525;541;542;590;600;611;699;716;725;759;774;120';
		$this->_schedule_ol = 1;
		$this->_schedule_cadastro = 2;
		
		$this->getListaGD();
		
	}
	
	function index(){
		
		$param = array();
		$param['paginacao'] = true;
		$param['scrollX'] = true;
		$param['scrollY'] = true;
		$param['scroll'] = true;
		$param = [];
		$param['programa']	= $this->_programa;
		$param['titulo']	= $this->_titulo;
		$relatorio = new relatorio01($param);
		$relatorio->setToExcel(true);
		
		if(!$relatorio->getPrimeira()){
			$filtro = $relatorio->getFiltro();
			
			$this->_totalClientes = $filtro['MEDIA'] == 'total'? 'total' : 'positivados';
			
			//echo $this->_totalClientes." <- Tipo Filtro->".$filtro['MEDIA']."<br>\n";
			
			$relatorio = $this->setColunas($filtro['nivel'], $relatorio);
			//$dados = $this->getDados($filtro['mes'], $filtro['ano'], false);
			$dados = $this->getDados2($filtro, false);
			$relatorio->setDados($dados);
		}
		
		else{
			$relatorio = $this->setColunas('1', $relatorio);
		}
		
		
		return $relatorio . '';
	}
	
	function schedule($param){
		$mes = date('m');
		$ano = date('Y');
		$primeira_execucao = $this->_teste;
		$dados_analitico_GD = [];
		
		$this->_totalClientes = 'total';
		$this->getMumeroClientes();
		
		if(verificaExecucaoSchedule($this->_programa,$ano.$mes) && $this->_teste === false){
			//Já foi executado
			$dt_ini = $this->getDatas('', '', 'ini');
			$dt_fim = $this->getDatas('', '', 'fim');
		}else{
			//Primeira execução no mes
			gravaExecucaoSchedule($this->_programa,$ano.$mes);
			$mes--;
			if($mes == 0){
				$mes = 12;
				$ano--;
			}
			$mes = $mes < 10 ? '0'.$mes : $mes;
			
			$dt_ini = $ano.$mes.'01';
			$dt_fim = $ano.$mes.date('t',mktime(0,0,0,$mes,15,$ano));
			$primeira_execucao = true;
		}
		
		if($this->_teste){
			//$dt_ini = '20220701';
			//$dt_fim = '20220725';
		}
		
		log::gravaLog('relatorio_mix', "Executado - " . datas::dataS2D($dt_ini) . ' a ' . datas::dataS2D($dt_fim) . ' | param: ' . $param);
		
		$dados = $this->getDadosScheduleERC($dt_ini, $dt_fim);
		//print_r($dados);die();
		if(is_array($dados) && count($dados) > 0){
			$relatorio = $this->setColunasSchedule(1);
			$enviadosTeste = 0;
			foreach ($dados as $erc => $clientes){
				if($primeira_execucao){
					//Se é a primeira execução do mês, envia aos GDs relatório aberto por cliente
					$super = $this->_lista_gd[$erc];
					foreach ($clientes as $tp){
						$dados_analitico_GD[$super][] = $tp;
					}
				}
				$resumo_erc = $this->montaScheduleERC($clientes,$erc);
				//print_r($resumo_erc);die();
				$relatorio->setDados($resumo_erc);
				// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
				//				$relatorio->setEnviaTabelaEmail(false);
				$relatorio->setAuto(true);
				$nome = $this->getInfoERC($erc, 'NOME');
				$relatorio->setToExcel(true,'Mix_Cliente_' . $nome);
				if($this->_teste){
					if($enviadosTeste < 10 && $this->_testeERC){
						$relatorio->enviaEmail('emanuel.thiel@verticais.com.br','ERC Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix<br>de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
						$enviadosTeste++;
					}
				}else{
					$email = $this->getInfoERC($erc, 'EMAIL');
					if(trim($email) != ''){
						$email .= $this->_email_geral;
						//$relatorio->enviaEmail($email,'Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
						$relatorio->agendaEmail('', '08:00', $this->_programa, $email,'Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
						log::gravaLog('relatorio_mix_email', $nome . " - " . datas::dataS2D($dt_ini) . ' a ' . datas::dataS2D($dt_fim) . ' | param: ' . $email);
					}
				}
			}
		}
		
		
		//Envia no primeiro dia o MIX analitico para o GD (aberto por cliente)-------------------------------------------------------------------------------------------------------------------------------------------------------
		//print_r($dados_analitico_GD);die();
		if($primeira_execucao && count($dados_analitico_GD) > 0){
			foreach ($dados_analitico_GD as $super => $dados_gd){
				$relatorio = $this->setColunasSchedule(4);
				$relatorio->setDados($dados_gd);
				// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
				//				$relatorio->setEnviaTabelaEmail(false);
				$relatorio->setAuto(true);
				$relatorio->setToExcel(true,'Mix_Cliente_Analitico');
				$nome = $this->getInfoGD($super, 'NOME');
				if($this->_teste){
					$relatorio->enviaEmail('emanuel.thiel@verticais.com.br','Analitico GD Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix Analitico de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
				}
				else{
					$email = $this->getEmailGD($super);
					if(trim($email) != ''){
						$relatorio->enviaEmail('suporte@thielws.com.br','Analitico GD Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix Analitico de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
						$relatorio->agendaEmail('', '08:00', $this->_programa, $email,'Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix Analitico de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
						log::gravaLog('relatorio_mix_email', $super . " - " . datas::dataS2D($dt_ini) . ' a ' . datas::dataS2D($dt_fim) . ' | param: ' . $email.' Analitico');
					}
				}
			}
		}
		
		$dados = $this->getDadosScheduleGD($dt_ini, $dt_fim);
		//print_r($dados);
		if(is_array($dados) && count($dados) > 0){
			$lista_dados_gd = [];
			if(is_array($this->_lista_gd) && count($this->_lista_gd) > 0){
				foreach ($dados as $erc => $totais){
					if(isset($this->_lista_gd[$erc])){
						$lista_dados_gd[$this->_lista_gd[$erc]][] = $totais;
					}
				}
			}
			$relatorio = $this->setColunasSchedule(2);
			foreach ($lista_dados_gd as $gd => $ercs){
				$resumo_gd = $this->montaScheduleGD($ercs);
				$relatorio->setDados($resumo_gd);
				$relatorio->setEnviaTabelaEmail(false);
				$relatorio->setAuto(true);
				$nome = $this->getInfoGD($gd, 'NOME');
				$relatorio->setToExcel(true,'Mix_Cliente_' . $nome);
				if($this->_teste){
					$relatorio->enviaEmail('emanuel.thiel@verticais.com.br','NOVO GD Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix<br>de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
				}else{
					$email = $this->getEmailGD($gd);
					if(trim($email) != ''){
						$email .= $this->_email_geral;
						$relatorio->agendaEmail('', '08:00', $this->_programa, $email,'Relatório Mix ' . $nome, '', 'Segue anexo o relatório Mix de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
						log::gravaLog('relatorio_mix_email', $nome . " - " . datas::dataS2D($dt_ini) . ' a ' . datas::dataS2D($dt_fim) . ' | param: ' . $email.' Resumo');
					}
				}
			}
			
			if(is_array($lista_dados_gd) && count($lista_dados_gd) > 0){
				//enviar email geral
				$dados_email_geral = array();
				foreach ($lista_dados_gd as $gd => $lista_ercs){
					$ercs_formatados = $this->montaScheduleGD($lista_ercs);
					foreach ($ercs_formatados as $erc){
						$temp = $erc;
						$temp['gd_cod'] = $gd;
						$temp['gd_nome'] = $this->getInfoGD($gd, 'NOME');
						$dados_email_geral[] = $temp;
					}
				}
				$relatorio = $this->setColunasSchedule(3);
				$relatorio->setDados($dados_email_geral);
				// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
				//				$relatorio->setEnviaTabelaEmail(false);
				$relatorio->setAuto(true);
				$relatorio->setToExcel(true,'relatorio_mix_geral');
				if($this->_teste){
					$relatorio->enviaEmail('emanuel.thiel@verticais.com.br','Relatório Mix GERAL', '', 'Segue anexo o relatório Mix<br>de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
				}
				else{
					$relatorio->agendaEmail('', '08:00', $this->_programa, $param,'Relatório Mix Geral', '', 'Segue anexo o relatório Mix de ' . datas::dataS2D($dt_ini) . ' até ' . datas::dataS2D($dt_fim));
				}
			}
		}
	}
	
	
	private function getListaGD(){
		if(count($this->_lista_gd) == 0){
			$sql = "SELECT PCUSUARI.CODUSUR,
							       PCUSUARI.NOME,
							       PCUSUARI.EMAIL,
							       PCUSUARI.CODSUPERVISOR,
							       PCSUPERV.NOME,
							       (SELECT US2.EMAIL FROM PCUSUARI US2 WHERE US2.CODUSUR = PCSUPERV.COD_CADRCA) EMAIL_SUPER,
									PCUSUARI.DTTERMINO,
									BLOQUEIO
							FROM PCUSUARI,
							     PCSUPERV
							WHERE
								PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
								AND BLOQUEIO <> 'S'";
			$rows = query4($sql);
			$lista = array();
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$lista[$row['CODUSUR']] = $row['CODSUPERVISOR'];
				}
			}
			$this->_lista_gd = $lista;
		}
	}
	
	private function getEmailGD($cod){
		$ret = '';
		$sql = "SELECT (SELECT US2.EMAIL FROM PCUSUARI US2 WHERE US2.CODUSUR = PCSUPERV.COD_CADRCA) EMAIL
                FROM PCSUPERV
                WHERE CODSUPERVISOR = '" . $cod . "'";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			if(isset($rows[0]['EMAIL'])){
				$ret = $rows[0]['EMAIL'];
			}
		}
		return $ret;
	}
	
	private function setColunas($nivel, $relatorio){
		if($nivel == 1 || $nivel == '1'){
			//caso o nivel seja cliente
			$relatorio->addColuna(array('campo' => 'gd_cod'			, 'etiqueta' => 'GD'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'gd_nome'		, 'etiqueta' => 'Nome GD'			    , 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			$relatorio->addColuna(array('campo' => 'erc_cod'		, 'etiqueta' => 'ERC'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'erc_nome'		, 'etiqueta' => 'Nome ERC'			    , 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			$relatorio->addColuna(array('campo' => 'cliente_cod'	, 'etiqueta' => 'Código Cliente'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'cliente_nome'	, 'etiqueta' => 'Cliente'			, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			
			$relatorio->addColuna(array('campo' => 'total'			, 'etiqueta' => 'Venda Total'			    , 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'venda_pda'		, 'etiqueta' => 'Venda ION'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'mix'			, 'etiqueta' => 'Mix'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades'		, 'etiqueta' => 'Unidades'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'prec_unidade'	, 'etiqueta' => 'Preço Unidade'		, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		}
		elseif($nivel == 2 || $nivel == '2'){
			//caso o nivel seja erc
			$relatorio->addColuna(array('campo' => 'gd_cod'			, 'etiqueta' => 'GD'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'gd_nome'		, 'etiqueta' => 'Nome GD'			    , 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			$relatorio->addColuna(array('campo' => 'erc_cod'		, 'etiqueta' => 'ERC'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'erc_nome'		, 'etiqueta' => 'Nome ERC'			    , 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			
			$relatorio->addColuna(array('campo' => 'mix'			, 'etiqueta' => 'Mix'			    , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_total'	, 'etiqueta' => 'Unidades'		    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_media'	, 'etiqueta' => 'Unidades med.'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'total'			, 'etiqueta' => 'Total'			    , 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'total_media'	, 'etiqueta' => 'Média '			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'prec_unidade'	, 'etiqueta' => 'Preço Unidade'		, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			
			$relatorio->addColuna(array('campo' => 'mix1'				, 'etiqueta' => 'Mix 1'			    , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_total1'	, 'etiqueta' => 'Unidades 1'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_media1'	, 'etiqueta' => 'Unidades med. 1'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'total1'				, 'etiqueta' => 'Total 1'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'total_media1'		, 'etiqueta' => 'Média 1'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'prec_unidade1'		, 'etiqueta' => 'Preço Unidade 1'	, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			
			$relatorio->addColuna(array('campo' => 'mix12'				, 'etiqueta' => 'Mix 12'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_total12'	, 'etiqueta' => 'Unidades 12'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_media12'	, 'etiqueta' => 'Unidades med. 12'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'total12'			, 'etiqueta' => 'Total 12'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'total_media12'		, 'etiqueta' => 'Média 12'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'prec_unidade12'		, 'etiqueta' => 'Preço Unidade 12'	, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));

			$relatorio->addColuna(array('campo' => 'venda_pda'			, 'etiqueta' => 'Venda ION'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		}
		elseif ($nivel == 3 || $nivel == '3'){
			//caso o nivel seja gd
			$relatorio->addColuna(array('campo' => 'gd_cod'			, 'etiqueta' => 'GD'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'gd_nome'		, 'etiqueta' => 'Nome'			    , 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			
			$relatorio->addColuna(array('campo' => 'mix'			, 'etiqueta' => 'Mix'			    , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_total'	, 'etiqueta' => 'Unidades'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_media'	, 'etiqueta' => 'Unidades med.'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			
			$relatorio->addColuna(array('campo' => 'total'			, 'etiqueta' => 'Total'			    , 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'total_media'	, 'etiqueta' => 'Média'			    , 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
			
			$relatorio->addColuna(array('campo' => 'prec_unidade'	, 'etiqueta' => 'Preço Unidade'		, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		
			$relatorio->addColuna(array('campo' => 'venda_pda'			, 'etiqueta' => 'Venda ION'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		}
		
		return $relatorio;
	}
	
	private function setColunasSchedule($tipo){
		$relatorio = new relatorio01('relatorio_mix');
		
		if($tipo == 4){
			//Relatório aberto por cliente para o GD
			$relatorio->addColuna(array('campo' => 'erc_cod'		, 'etiqueta' => 'ERC'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'erc_nome'		, 'etiqueta' => 'Nome ERC'			, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			$tipo = 1;
		}
		
		if($tipo == 1){
			$relatorio->addColuna(array('campo' => 'cliente_cod'	, 'etiqueta' => 'Código Cliente'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'cliente_nome'	, 'etiqueta' => 'Cliente'			, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			$relatorio->addColuna(array('campo' => 'mix'			, 'etiqueta' => 'Mix'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades'		, 'etiqueta' => 'Unidades'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'total'			, 'etiqueta' => 'Venda'			    , 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'prec_unidade'	, 'etiqueta' => 'Preço Unidade'		, 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
		}elseif($tipo == 2){
			$relatorio->addColuna(array('campo' => 'erc_cod'		, 'etiqueta' => 'ERC'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'erc_nome'		, 'etiqueta' => 'Nome ERC'			, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			$relatorio->addColuna(array('campo' => 'mix'			, 'etiqueta' => 'Mix'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			
			$relatorio->addColuna(array('campo' => 'unidades_total'	, 'etiqueta' => 'Unidades'		    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_media'	, 'etiqueta' => 'Unidades med.'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			
			$relatorio->addColuna(array('campo' => 'total'			, 'etiqueta' => 'Total'			    , 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'total_media'	, 'etiqueta' => 'Média '			, 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
			
			$relatorio->addColuna(array('campo' => 'prec_unidade'	, 'etiqueta' => 'Preço Unidade'		, 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
		}elseif($tipo == 3){
			$relatorio->addColuna(array('campo' => 'gd_cod'		    , 'etiqueta' => 'GD'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'gd_nome'		, 'etiqueta' => 'Nome GD'			, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			
			
			$relatorio->addColuna(array('campo' => 'erc_cod'		, 'etiqueta' => 'ERC'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'erc_nome'		, 'etiqueta' => 'Nome ERC'			, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
			$relatorio->addColuna(array('campo' => 'mix'			, 'etiqueta' => 'Mix'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			
			$relatorio->addColuna(array('campo' => 'unidades_total'	, 'etiqueta' => 'Unidades'		    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$relatorio->addColuna(array('campo' => 'unidades_media'	, 'etiqueta' => 'Unidades med.'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			
			$relatorio->addColuna(array('campo' => 'total'			, 'etiqueta' => 'Total'			    , 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => 'total_media'	, 'etiqueta' => 'Média '			, 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
			
			$relatorio->addColuna(array('campo' => 'prec_unidade'	, 'etiqueta' => 'Preço Unidade'		, 'tipo' => 'T', 'width' =>  120, 'posicao' => 'D'));
		}
		
		//Todos os niveis
		$relatorio->addColuna(array('campo' => 'venda_pda'			, 'etiqueta' => 'Venda PDA'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		
		return $relatorio;
	}
	
	private function montaScheduleERC($clientes,$erc){
		//monta as 2 linhas extras total e média
		$ret = array();
		
		
		
		if(is_array($clientes) && count($clientes) > 0){
			if($this->_totalClientes == 'total'){
				$num_clientes = $this->_quantClientesERC[$erc] ?? 0;
			}else{
				$num_clientes = count($clientes);
			}
			
			//echo $this->_totalClientes." - ERC $erc: $num_clientes <br>\n";
			
			$totais = array(
				'cliente_cod' => '',
				'cliente_nome' => 'TOTAL',
				'mix' => 0,
				'unidades' => 0,
				'total' => 0,
				'prec_unidade' => '',
				'venda_pda' => 0,
			);
			
			$medias = array(
				'cliente_cod' => '',
				'cliente_nome' => 'MEDIA',
				'mix' => 0,
				'unidades' => 0,
				'total' => 0,
				'prec_unidade' => 0,
			);
			
			$campos_total = array('mix', 'unidades', 'total');
			$campos_formatar = array('total', 'prec_unidade');
			foreach ($clientes as $cliente){
				$temp = $cliente;
				foreach ($campos_total as $campo){
					$totais[$campo] += $cliente[$campo];
				}
				foreach ($campos_formatar as $campo){
					$temp[$campo] = $temp[$campo] > 0 ?  number_format($temp[$campo], 2, ',' , '.') : 0;
				}
				
				$ret[] = $temp;
			}
			foreach ($campos_total as $campo){
				$medias[$campo] = $totais[$campo] > 0 && $num_clientes != 0 ?  number_format($totais[$campo] / $num_clientes, 2, ',' , '.') : 0;
			}
			$medias['prec_unidade'] = $totais['total'] > 0 && $totais['unidades'] > 0 ? number_format($totais['total'] / $totais['unidades'], 2, ',' , '.') : 0;
			$totais['total'] = $totais['total'] > 0 ? number_format($totais['total'], 2, ',', '.') : 0;
			
			
			$ret[] = $medias;
			$ret[] = $totais;
		}
		return $ret;
	}
	
	private function montaScheduleGD($ercs){
		$ret = array();
		if(is_array($ercs) && count($ercs) > 0){
			$medias = array(
				'erc_cod' => '',
				'erc_nome' => 'TOTAL',
				'mix' => 0,
				'unidades_total' => 0,
				'unidades_media' => 0,
				'total' => 0,
				'total_media' => 0,
				'prec_unidade' => 0,
			);
			$num_erc = count($ercs);
			$campos_formatar = array('total', 'prec_unidade', 'unidades_media', 'total_media', 'mix');
			$campos_somar = array('mix', 'unidades_total', 'unidades_media', 'total', 'total_media', 'prec_unidade');
			$campos_media = array('mix', 'unidades_media', 'total_media', 'prec_unidade');
			foreach ($ercs as $dados){
				$temp = $dados;
				foreach ($campos_formatar as $campo){
					$temp[$campo] = $temp[$campo] > 0 ?  number_format($temp[$campo], 2, ',' , '.') : 0;
				}
				$ret[] = $temp;
				foreach ($campos_somar as $campo){
					$medias[$campo] += $dados[$campo];
				}
			}
			foreach ($campos_media as $campo){
				$medias[$campo] = $medias[$campo] > 0 ? $medias[$campo] / $num_erc : 0;
			}
			foreach ($campos_formatar as $campo){
				$medias[$campo] = number_format($medias[$campo], 2, ',', '.');
			}
			$ret[] = $medias;
			
			
		}
		return $ret;
	}
	
	private function getTotal($dados, $erc){
		$campos = array('mix', 'unidades', 'total', 'prec_unidade');
		if(!isset($this->_totais[$erc])){
			$temp = array();
			foreach ($campos as $c){
				$temp[$c] = 0;
			}
			$temp['entradas'] = 0;
			$this->_totais[$erc] = $temp;
		}
		if(is_array($dados) && count($dados) > 0){
			foreach ($campos as $c){
				$this->_totais[$erc][$c] += $dados[$c];
			}
			$this->_totais[$erc]['entradas']++;
		}
		
		if($this->_totalClientes == 'total'){
			foreach ($this->_totais as $erc => $totais){
				$this->_totais[$erc]['entradas'] = $this->_quantClientesERC[$erc];
			}
		}
		
		
		//true -> MIX calculado pelo total de clientes / false -> calculado pelo nr. de clientes positivados
		if(true){
			
		}
	}
	
	private function getMedia(){
		if(is_array($this->_totais) && count($this->_totais) > 0){
			$campos = array('mix', 'unidades', 'total', 'prec_unidade');
			foreach ($this->_totais as $erc => $dados){
				$temp = array();
				foreach ($campos as $c){
					$temp[$c] = $dados[$c] / $dados['entradas'];
				}
				$this->_medias[$erc] = $temp;
			}
		}
	}
	
	private function getMediaTemp($erc){
		$ret = array();
		if(isset($this->_totais[$erc])){
			$campos = array('mix', 'unidades', 'total', 'prec_unidade');
			foreach ($campos as $c){
				$ret[$c] = $this->_totais[$erc][$c] / $this->_totais[$erc]['entradas'];
			}
		}
		return $ret;
	}
	
	private function montaDadosGeral(){
		$ret = array();
		if(is_array($this->_totais) && count($this->_totais) > 0){
			foreach ($this->_totais as $erc => $valores){
				$temp = array();
				$temp['erc'] = $erc;
				$temp['erc_nome'] = $this->getInfoERC($erc, 'NOME');
				$temp['mix'] = $this->_medias[$erc]['mix'];
				$temp['valor'] = $valores['total'];
				//$temp['unidades'] = $valores['unidades'];
				$temp['prec_unidade'] = $this->_medias[$erc]['prec_unidade'];
				$ret[] = $temp;
			}
			$ret[] = $this->getMediaGeral();
			$ret[] = $this->getTotalGeral();
		}
		return $ret;
	}
	
	private function getMediaGeral(){
		$ret = array();
		if(is_array($this->_totais) && count($this->_totais) > 0){
			$campos = array('mix', 'valor', 'prec_unidade');
			$num_erc = 0;
			foreach ($campos as $c){
				$ret[$c] = 0;
			}
			foreach ($this->_totais as $linha){
				$ret['mix']          += $linha['mix'] / $linha['entradas'];
				$ret['valor']        += $linha['total'];
				$ret['prec_unidade'] += $linha['prec_unidade'] / $linha['entradas'];
			}
			$num_erc = count(array_keys($this->_totais));
			$ret['mix']          =  $ret['mix'] / $num_erc;
			$ret['valor']        =  $ret['valor'] / $num_erc;
			$ret['prec_unidade'] =  $ret['prec_unidade'] / $num_erc;
			$ret['erc'] = '';
			$ret['erc_nome'] = 'Media Total';
		}
		return $ret;
	}
	
	private function getTotalGeral(){
		$ret = array();
		if(is_array($this->_totais) && count($this->_totais) > 0){
			$campos = array('mix', 'valor', 'prec_unidade');
			foreach ($campos as $c){
				$ret[$c] = 0;
			}
			foreach ($this->_totais as $linha){
				$ret['valor']        += $linha['total'];
			}
			$ret['mix']          = '';
			$ret['prec_unidade'] = '';
			$ret['erc'] = '';
			$ret['erc_nome'] = 'TOTAL';
		}
		return $ret;
	}
	
	/*
	 * Pega dados conforme a função na cli000002
	 */
	private function getDadosScheduleERC($dataIni, $dataFim){
		$ret = [];
		$campos = array('CODUSUR', 'CODCLI');
		
		//Vendas PDA
		$param = [];
		$param['origem'] = 'PDA';
		$param['bonificacao'] = false;
		$pda = $this->getVendaPda($dataIni, $dataFim,'', '', 2, $campos);
		
		$param = [];
		$param['depto'] = '1,12';
		$param['bonificacao'] = false;
		$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
		
		if(is_array($vendas) && count($vendas) > 0){
			//print_r($vendas);
			foreach ($vendas as $erc => $clientes){
				if(isset($this->_lista_gd[$erc])){
					$erc_nome = $this->getInfoERC($erc, 'NOME');
					$gd_nome = $this->getInfoGD($this->_lista_gd[$erc], 'NOME');
					foreach ($clientes as $cod_cliente => $vendas){
						$temp = array();
						$temp['cliente_cod'] 	= $cod_cliente;
						$temp['cliente_nome'] 	= $this->getNomeCliente($cod_cliente);
						$temp['mix'] 			= $vendas['mix'];
						$temp['unidades'] 		= $vendas['quantVend'];
						$temp['total'] 			= $vendas['venda'];
						$temp['prec_unidade'] 	= $temp['unidades'] > 0 && $temp['total'] > 0 ? $temp['total'] / $temp['unidades'] : 0;
						$temp['erc_cod'] 		= $erc;
						$temp['erc_nome'] 		= $erc_nome;
						$temp['gd_cod'] 		= $this->_lista_gd[$erc];
						$temp['gd_nome'] 		= $gd_nome;
						$temp['venda_pda']		= $pda[$erc][$cod_cliente] ?? 0;
						
						$ret[$erc][$cod_cliente] = $temp;
					}
				}else{
					echo "ERC $erc nao encontrado<br>\n";
				}
			}
			$ret = $this->getClientesVendaZero('',true,$ret);
		}
		
		return $ret;
	}
	
	/*
	 *  Pega dados dos GDs conforme a função na cli000002
	 */
	private function getDadosScheduleGD($dataIni, $dataFim){
		$ret = [];
		$this->getListaGD();
		
		$param = [];
		$param['depto'] = '1,12';
		$param['bonificacao'] = false;
		$campos = array('CODUSUR', 'CODCLI');
		$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
		
		if(is_array($vendas) && count($vendas) > 0){
			//print_r($vendas);
			foreach ($vendas as $erc => $clientes){
				//$gd = $this->_lista_gd[$erc];
				//echo "$erc - $gd <br>\n";
				if($this->_totalClientes == 'total'){
					$quant_cli = $this->_quantClientesERC[$erc] ?? 0;
				}else{
					$quant_cli = count($clientes);
				}
				$mix = 0;
				$unidades = 0;
				$venda = 0;
				foreach ($clientes as $vendas){
					$mix			+= $vendas['mix'];
					$unidades 		+= $vendas['quantVend'];
					$venda 			+= $vendas['venda'];
				}
				
				$ret[$erc] = [];
				$ret[$erc]['erc_cod'] 		= $erc;
				$ret[$erc]['erc_nome'] 		= $this->getInfoERC($erc, 'NOME');
				$ret[$erc]['mix'] 			= $mix > 0 && $quant_cli > 0 ? round(($mix/$quant_cli),2) : 0;
				$ret[$erc]['unidades_total']= $unidades;
				$ret[$erc]['unidades_media']= $unidades > 0 && $quant_cli > 0 ? round(($unidades/$quant_cli),2) : 0;
				$ret[$erc]['total'] 		= $venda;
				$ret[$erc]['total_media'] 	= $venda > 0 && $quant_cli > 0 ? round(($venda/$quant_cli),2) : 0;
				$ret[$erc]['prec_unidade']  = $venda > 0 && $unidades > 0 ? round(($venda/$unidades),2) : 0;
			}
		}
		
		return $ret;
	}
	
	private function getDadosCliente2($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos, $ordem = true, $depto = '', $trace = false){
		$ret = array();
		///////
		if($cadastro == 1 || $cadastro == '1'){
			$campos = array('ERCCLI', 'CODCLI');
		}else{
			$campos = array('CODUSUR', 'CODCLI');
		}
		
		if(isset($filtro['dt_ini']) && isset($filtro['dt_fim'])){
			$dt_ini = $filtro['dt_ini'];
			$dt_fim = $filtro['dt_fim'];
		}else{
			$dt_ini = $this->getDatas($mes, $ano, 'ini');
			$dt_fim = $this->getDatas($mes, $ano, 'fim');
		}
		
		if(empty($depto)){
			$pda = $this->getVendaPda($dt_ini, $dt_fim,$fornecedores, $excluidos, $cadastro, $campos);
		}
		
		$param = array();
		if($ol != 3 && $ol != '3'){
			if($ol == 1 && $ol == '1'){
				$param['origem'] = 'NOL';
			}
			if($ol == 2 && $ol == '2'){
				$param['origem'] = 'OL';
			}
		}
		
		if(!empty($depto)){
			$param['depto'] = $depto;
		}else{
			$param['depto'] = '1,12';
		}
		
		if($excluidos != '' && $cadastro == 1){
			$param['erccli'] = "SELECT DISTINCT CODUSUR1 FROM pcclient WHERE CODUSUR1 NOT IN (".$excluidos.")";
			
		}
		
		if($excluidos != '' && $cadastro == 2){
			$param['erc'] = "SELECT CODUSUR FROM pcusuari WHERE CODUSUR NOT IN (".$excluidos.")";
		}
		
		if(!empty($fornecedores)){
			$param['fornecedor'] = $fornecedores;
		}
		
		$param['bonificacao'] = false;
		
		$this->getListaGD();
		//print_r($param);
		$rows = vendas1464Campo($campos, $dt_ini, $dt_fim, $param);
	//	var_dump($rows); die();
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $erc => $clientes_array){
				if(!isset($ret[$erc]) && $ordem){
					$ret[$erc] = array();
				}
				$erc_nome = $this->getInfoERC($erc, 'NOME');
				$gd_nome = isset($this->_lista_gd[$erc]) ? $this->getInfoGD($this->_lista_gd[$erc], 'NOME') : '';
				foreach ($clientes_array as $cod_cliente => $vendas){
					$temp = array();
					$temp['cliente_cod'] = $cod_cliente;
					$temp['cliente_nome'] = $this->getNomeCliente($cod_cliente);
					$temp['mix'] = $vendas['mix'];
					$temp['unidades'] = $vendas['quantVend'];
					$temp['total'] = $vendas['venda'];
					$temp['prec_unidade'] = $temp['unidades'] > 0 && $temp['total'] > 0 ? $temp['total'] / $temp['unidades'] : 0;
					$temp['venda_pda']	= $pda[$erc][$cod_cliente] ?? 0;
					if($ordem){
						$ret[$erc][$cod_cliente] = $temp;
					}else{
						$temp['erc_cod'] = $erc;
						$temp['erc_nome'] = $erc_nome;
						$temp['gd_cod'] = isset($this->_lista_gd[$erc]) ? $this->_lista_gd[$erc] : '';
						$temp['gd_nome'] = $gd_nome;
						$ret[] = $temp;
					}
				}
			}
		}
		$ret = $this->getClientesVendaZero($excluidos, $ordem, $ret);
		return $ret;
	}
	
	
	private function getClientesVendaZero($excluidos, $ordem, $lista)
	{
	    $ret = $lista;
	   // $ret = [];
	    $sql= "SELECT DISTINCT CODUSUR1, CODCLI FROM pcclient WHERE dtexclusao IS NULL ";
	     if($excluidos != '') {
	        $sql .= " AND CODUSUR1 NOT IN ($excluidos)";
	     }
	    
	    $lista_clientes_ignorar = array();
	    if($ordem){
	        foreach ($lista as $l){
	            $temp_clientes = array_keys($l);
	            $lista_clientes_ignorar = array_merge($lista_clientes_ignorar, $temp_clientes);
	        }
	    }
	    else{
	        foreach ($lista as $l){
	            $lista_clientes_ignorar[] = $l['cliente_cod'];
	        }
	    }
	    
	    
	    
	    /*
	    //Cláusula where
	    if( (is_array($lista) && count($lista)>0) || $excluidos != '')
	    {
	        $where = " WHERE CODUSUR1 NOT IN ";
	        if($excluidos != '') {
	            $where .= "($excluidos)";
	            if(is_array($lista) && count($lista)>0) {
    	            $codigos = array_keys($lista);
    	            $codigos = array_chunk($codigos,999);
    	            foreach($codigos as $cod)
    	            {
    	                $excl = implode(', ',$cod);
    	                $where.= " AND CODUSUR1 NOT IN ($excl)";
    	            }
	            }
	        } else {
	            $codigos = array_keys($lista);
	            $codigos = array_chunk($codigos,999);
	            $where .= "(".implode(', ',$codigos[0]).")";
	            for($i=1;$i<count($codigos);$i++){
	                $where.= " AND CODUSUR1 NOT IN (".implode(', ',$codigos[$i]).")";
	            }
	        }
	        $sql .= $where;
	    }
	    $rows = query4($sql);
	   // var_dump($rows); die();
	    */
	    
	    $rows = query4($sql);
	    if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                if(!in_array($row['CODCLI'], $lista_clientes_ignorar)){
                    $temp = [];
                    $temp['cliente_cod'] = $row['CODCLI'];
                    $temp['cliente_nome'] = $this->getNomeCliente($row['CODCLI']);
                    $temp['mix'] = 0;
                    $temp['unidades'] = 0;
                    $temp['total'] = 0;
                    $temp['prec_unidade'] = 0;
                    $temp['venda_pda']	= 0;
                    
                    $erc = $row['CODUSUR1'];
                    
                    if($ordem){
                        if(!empty($erc) && !empty($temp['cliente_cod'])){
                            $ret[$erc][$temp['cliente_cod']] = $temp;
                        }
                    } 
                    else {
                        $gd_nome = isset($this->_lista_gd[$erc]) ? $this->getInfoGD($this->_lista_gd[$erc], 'NOME') : '';
    	                 
                        $temp['erc_cod'] = $erc;
                        $temp['erc_nome'] = $this->getInfoERC($erc, 'NOME');
                        $temp['gd_cod'] = isset($this->_lista_gd[$erc]) ? $this->_lista_gd[$erc] : '';
                        $temp['gd_nome'] = $gd_nome;
    	                 
                        $ret[] = $temp;
                     }
	            }
	        }
	    }
	    return $ret;
	}
	
	
	private function getDadosERC2($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos, $ordem = true){
		$ret = array();
		$dados = $this->getDadosCliente2($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos, true, '', false);
		if(is_array($dados) && count($dados) > 0){
			foreach ($dados as $erc => $clientes){
				//Gera linha do ERC
				$temp = $this->getTotalERC2($erc, $clientes);
				$temp['erc_cod'] 	= $erc;
				$temp['erc_nome'] 	= $this->getInfoERC($erc, 'NOME');
				$temp['gd_cod'] 	= isset($this->_lista_gd[$erc]) ? $this->_lista_gd[$erc] : '';
				$temp['gd_nome'] 	= isset($this->_lista_gd[$erc]) ? $this->getInfoGD($this->_lista_gd[$erc], 'NOME') : '';
				
				$ret[$erc] = $temp;
			}
		}
		
		//Medicamentos
		$dados = $this->getDadosCliente2($filtro,$fornecedores, $mes, $ano, $ol, $cadastro, $excluidos, true, 1);
		//print_r($dados);
		if(is_array($dados) && count($dados) > 0){
			foreach ($dados as $erc => $clientes){
				$temp = $this->getTotalERC2($erc, $clientes,'1');
				if(!isset($ret[$erc])){
					$ret[$erc]['erc_cod'] = $erc;
					$ret[$erc]['erc_nome'] = $this->getInfoERC($erc, 'NOME');
					$ret[$erc]['gd_cod'] = $this->_lista_gd[$erc];
					$ret[$erc]['gd_nome'] = $this->getInfoGD($this->_lista_gd[$erc], 'NOME');
				}
				
				$ret[$erc] = array_merge($ret[$erc],$temp);
			}
		}
		
		//Não Medicamentos
		$dados = $this->getDadosCliente2($filtro,$fornecedores, $mes, $ano, $ol, $cadastro, $excluidos, true, 12);
		//print_r($dados);
		if(is_array($dados) && count($dados) > 0){
			foreach ($dados as $erc => $clientes){
				$temp = $this->getTotalERC2($erc, $clientes,'12');
				if(!isset($ret[$erc])){
					$ret[$erc]['erc_cod'] = $erc;
					$ret[$erc]['erc_nome'] = $this->getInfoERC($erc, 'NOME');
					$ret[$erc]['gd_cod'] = $this->_lista_gd[$erc];
					$ret[$erc]['gd_nome'] = $this->getInfoGD($this->_lista_gd[$erc], 'NOME');
				}
				
				$ret[$erc] = array_merge($ret[$erc],$temp);
			}
		}
		
		//Se não está setada a ordem, retira o indice
		if(!$ordem && count($ret) > 0){
			$temp = [];
			foreach ($ret as $r){
				$temp[] = $r;
			}
			$ret = $temp;
		}
		
		return $ret;
	}
	
	private function getTotalERC2($erc, $vendas, $depto=''){
		$ret = [];
		$ret['mix'.$depto] = 0;
		$ret['unidades_media'.$depto] = 0;
		$ret['unidades_total'.$depto] = 0;
		$ret['total'.$depto] = 0;
		$ret['total_media'.$depto] = 0;
		$ret['prec_unidade'.$depto] = 0;
		if(!isset($this->_vendaPDA[$erc])){
			$this->_vendaPDA[$erc] = 0;
		}
		
		if(is_array($vendas) && count($vendas) > 0){
			$num_clientes = count($vendas);
			if($this->_totalClientes == 'total'){
				$num_clientes = isset($this->_quantClientesERC[$erc]) ? $this->_quantClientesERC[$erc] : 0;
				if($num_clientes == 0){
					//echo "ERC sem clientes cadastrados: $erc <br>\n";
				}
			}else{
				$num_clientes = count($vendas);
			}
			//echo $this->_totalClientes." - $erc - $num_clientes <br>\n";
			
			foreach ($vendas as $venda){
				$ret['mix'.$depto] 				+= $venda['mix'];
				$ret['unidades_total'.$depto] 	+= $venda['unidades'];
				$ret['total'.$depto] 			+= $venda['total'];
				$ret['prec_unidade'.$depto] 	+= $venda['prec_unidade'];
				$this->_vendaPDA[$erc]			+= $venda['venda_pda'];
				$ret['venda_pda']				= $this->_vendaPDA[$erc];
			}
			if($num_clientes == 0){
				$ret['unidades_media'.$depto] 	= 0;
				$ret['total_media'.$depto] 		= 0;
				$ret['prec_unidade'.$depto] 	= 0;
				$ret['mix'.$depto] 				= 0;
			}else{
				$ret['unidades_media'.$depto] 	= $ret['unidades_total'.$depto] > 0 ? $ret['unidades_total'.$depto] / $num_clientes : 0;
				$ret['total_media'.$depto] 		= $ret['total'.$depto] > 0 ? $ret['total'.$depto] / $num_clientes : 0;
				$ret['prec_unidade'.$depto] 	= $ret['prec_unidade'.$depto] > 0  ? $ret['prec_unidade'.$depto] / $num_clientes : 0;
				$ret['mix'.$depto] 				= $ret['mix'.$depto] > 0 ? $ret['mix'.$depto] / $num_clientes : 0;
			}
		}
		//print_r($vendas);print_r($ret);die();
		return $ret;
	}
	
	private function getDadosGD($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos){
		$ret = array();
		$this->getListaGD();
		$dados = $this->getDadosERC2($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos);
//print_r($dados);
		if(is_array($dados) && count($dados) > 0){
			$lista_dados_gd = array();
			if(is_array($this->_lista_gd) && count($this->_lista_gd) > 0){
				foreach ($dados as $erc => $totais){
					if(isset($this->_lista_gd[$erc])){
						$lista_dados_gd[$this->_lista_gd[$erc]][] = $totais;
					}
				}
			}
//print_r($lista_dados_gd);
			foreach ($lista_dados_gd as $cod_gd => $ercs){
				$temp = $this->getTotalGD2($ercs);
				$temp['gd_cod'] = $cod_gd;
				$temp['gd_nome'] = $this->getInfoGD($cod_gd);
				$ret[] = $temp;
			}
		}
		
		
		return $ret;
	}
	
	private function getTotalGD2($dados){
		$ret = array(
			'mix' => 0,
			'unidades_media' => 0,
			'unidades_total' => 0,
			'total' => 0,
			'total_media' => 0,
			'prec_unidade' => 0,
			'venda_pda' => 0,
		);
		
		if(is_array($dados) && count($dados) > 0){
			$num_erc = count($dados);
			foreach ($dados as $dado){
				$ret['mix'] 			+= $dado['mix'];
				$ret['unidades_total'] 	+= $dado['unidades_total'];
				$ret['unidades_media'] 	+= $dado['unidades_media'];
				$ret['total'] 			+= $dado['total'];
				$ret['total_media'] 	+= $dado['total_media'];
				$ret['prec_unidade'] 	+= $dado['prec_unidade'];
				$ret['venda_pda'] 		+= $dado['venda_pda'];
			}
			$ret['mix'] 			= $ret['mix'] > 0 ? $ret['mix'] / $num_erc : 0;
			$ret['unidades_media'] 	= $ret['unidades_media'] > 0 ? $ret['unidades_media'] / $num_erc : 0;
			$ret['total_media'] 	= $ret['total_media'] > 0 ? $ret['total_media'] / $num_erc : 0;
			$ret['prec_unidade'] 	= $ret['prec_unidade'] > 0 ? $ret['prec_unidade'] / $num_erc : 0;
		}
		
		return $ret;
	}
	
	private function getVendaPda($dt_ini, $dt_fim,$fornecedores, $excluidos, $cadastro, $campos){
		$ret = [];
		
		$param = [];
		$param['origem'] = 'PDA';
		if($excluidos != '' && $cadastro == 1){
			$param['erccli'] = "SELECT DISTINCT CODUSUR1 FROM pcclient WHERE CODUSUR1 NOT IN (".$excluidos.")";
		}
		
		if($excluidos != '' && $cadastro == 2){
			$param['erc'] = "SELECT CODUSUR FROM pcusuari WHERE CODUSUR NOT IN (".$excluidos.")";
		}
		
		if(!empty($fornecedores)){
			$param['fornecedor'] = $fornecedores;
		}
		
		$param['bonificacao'] = false;
		
		$vendas = vendas1464Campo($campos, $dt_ini, $dt_fim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $clientes){
			foreach ($clientes as $cliente => $vendas){
				$ret[$erc][$cliente] = $vendas['venda'];
			}
		}
		
		return $ret;
	}
	
	private function getInfoGD($id, $campo = 'NOME'){
		$ret = '';
		
		if(!isset($this->_gd[$id][$campo])){
			$sql = "SELECT $campo FROM PCSUPERV WHERE CODSUPERVISOR = '$id'";
			$rows = query4($sql);
			if(isset($rows[0][$campo])){
				$this->_gd[$id][$campo] = $rows[0][$campo];
			}else{
				$this->_gd[$id][$campo] = '';
			}
		}
		
		$ret = $this->_gd[$id][$campo];
		
		return $ret;
	}
	
	private function getDados2($filtro, $ordem = true){
		$ret = array();
		
		$mes = isset($filtro['mes']) ? $filtro['mes'] : '';
		$ano = isset($filtro['ano']) ? $filtro['ano'] : '';
		$ol = isset($filtro['ol'])  ? $filtro['ol'] : '';
		$cadastro = isset($filtro['cadastro']) ? $filtro['cadastro'] : '';
		$fornecedores = isset($filtro['FORNECEDORES']) ? $filtro['FORNECEDORES'] : '';
		
		//echo "Calculo por: ".$this->_totalClientes."<br>\n";
		
		if($this->_totalClientes == 'total'){
			$this->getMumeroClientes();
		}
		
		
		$nivel = isset($filtro['nivel']) ? $filtro['nivel'] : '';
		$excluidos = isset($filtro['excluidos']) ? $filtro['excluidos'] : '';
		$excluidos = trim($excluidos);
		if(strpos($excluidos, ';') !== false){
			$excluidos = str_replace(';', ',', $excluidos);
			if(substr($excluidos, -1) == ',' || substr($excluidos, -1) == ';'){
				$excluidos = substr($excluidos, 0,  -1);
			}
		}
		
		switch ($nivel){
			case 2:
				log::gravaLog('relatorio_mix', "getDados2 - Nivel 2 - ERC");
				$ret = $this->getDadosERC2($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos, $ordem);
				break;
			case 3:
				log::gravaLog('relatorio_mix', "getDados2 - Nivel 3 - GD");
				$ret = $this->getDadosGD($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos);
				break;
			default:
				log::gravaLog('relatorio_mix', "getDados2 - Nivel 1 - Cliente");
				$ret = $this->getDadosCliente2($filtro, $fornecedores, $mes, $ano, $ol, $cadastro, $excluidos, $ordem);
				break;
		}
		return $ret;
	}
	
	
	private function getNomeCliente($codcli = ''){
		$ret = '';
		if($codcli != '' && $codcli != 0){
			$campo_nome = 'CLIENTE';
			$sql = "SELECT $campo_nome FROM PCCLIENT WHERE CODCLI = '$codcli'";
			$rows = query4($sql);
			if(is_array($rows) && count($rows) > 0){
				if(isset($rows[0][$campo_nome])){
					$ret = $rows[0][$campo_nome];
				}
			}
		}
		return $ret;
	}
	
	private function getInfoERC($codusur, $campo = ''){
		$ret = '';
		$campo = strtolower($campo);
		if(!isset($this->_erc[$codusur])){
			$sql = "select NOME, EMAIL from pcusuari where codusur = $codusur";
			$rows = query4($sql);
			if(is_array($rows) && count($rows) > 0){
				$this->_erc[$codusur]['nome']  = isset($rows[0]['NOME'])  ? $rows[0]['NOME']  : '';
				$this->_erc[$codusur]['email'] = isset($rows[0]['EMAIL']) ? $rows[0]['EMAIL'] : '';
			}else{
				$this->_erc[$codusur]['nome']  = '';
				$this->_erc[$codusur]['email'] = '';
			}
		}
		
		if($campo == ''){
			$ret = $this->_erc[$codusur];
		}else{
			$ret = $this->_erc[$codusur][$campo];
		}
		
		return $ret;
	}
	
	private function getDatas($mes = '', $ano = '', $modo){
		$ret = '';
		if($mes != '' && $mes != 0 && $ano != '' && $ano != 0){
			if($modo == 'ini'){
				$ret = date('Ym',mktime(0,0,0,$mes,15,$ano)).'01';
			}elseif($modo == 'fim'){
				$ret = date('Ymt',mktime(0,0,0,$mes,15,$ano));
			}
		}
		else{
			if($modo == 'ini'){
				$mes_inicial = date('n');
				$ano_inicial = date('Y');
				$dia_inicial = date('j');
				if($dia_inicial == 1){
					$ano_inicial = $this->voltaMes($mes_inicial, $ano_inicial, 'Y');
					$mes_inicial = $this->voltaMes($mes_inicial, $ano_inicial, 'M');
				}
				$mes_inicial = $mes_inicial < 10 ? '0'.$mes_inicial : $mes_inicial;
				$ret = $ano_inicial . $mes_inicial . '01';
			}
			if($modo == 'fim'){
				$ret = datas::getDataDias(-1);
			}
		}
		return $ret;
	}
	
	private function voltaMes($mes, $ano, $retorno){
		$ret = '';
		if($mes > 1){
			$mes -= 1;
		}
		else{
			$mes = 12;
			$ano -= 1;
		}
		if($retorno == 'M'){
			$ret = $mes;
		}
		if($retorno == 'Y'){
			$ret = $ano;
		}
		return $ret;
	}
	
	private function getMumeroClientes(){
		$sql = "SELECT CODUSUR1, COUNT(*) QUANT FROM PCCLIENT WHERE DTEXCLUSAO IS NULL GROUP BY CODUSUR1";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_quantClientesERC[$row['CODUSUR1']] = $row['QUANT'];
			}
		}
		
		//print_r($this->_quantClientesERC);
	}
}