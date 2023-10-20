<?php
/*
* Data Criação: 22/05/2015 - 14:34:56
* Autor: Thiel
*
* Arquivo: tws.geral_faturamento.inc.php
* 
* 
* Alterções:
*            26/10/2018 - Emanuel - Migração para intranet2
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class geral_faturamento{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';

	// Classe relatorio
	var $_relatorio;
	
	//RCAs
	var $_rca;
	
	//Dados
	var $_dados;
	
	//Soma temp
	var $_somaTemp;
	
	//Soma supervisores
	var $_somaSuper;
	
	//Dados gerais
	var $_geral;
	
	//Lista de campos valores
	var $_campos;
	
	// Envia email para Supervisores (utilizado nos testes)
	var $_teste;
	
	// Indica que é executado por schedule
	var $_schedule;
	
	//Periodos anteriores
	var $_periodos;
	
	//Linhas do resumo do email geral
	var $_linhas_resumo;
	
	function __construct(){
		set_time_limit(0);
		
		// true = não envia email para Super, false = envia
		//$this->_teste = true;
		$this->_teste = false;
		$this->_schedule = false;

		$this->_programa = '000002.geral_faturamento';
		
		$param = [];
		$param['programa'] 	= $this->_programa;
		$param['titulo']	= "Relatorio Geral Faturamento.";
		$this->_relatorio = new relatorio01($param);

		$this->_campos = array(
				'venda_2',
				'venda_1',
				'venda',
				'precMed',
				'mixMed',
				'vendaPDA',
				'vendaTMKT',
				'vendaPE',
				'vendaDia',
				'vendaDiaPDA',
				'pedPEdia',
				'vendaDiaTMKT',
				//	'vendaDiaPE',
				'realMix1',
				'realMix12',
				'ped',
				//	'pedDia',
				'pedPDA',
				'pedPDAdia',
				'pedTMKT',
				'pedTMKTdia',
				'pedPE',
				'cli',
				'cliAtend',
				//	'cliDia',
				'cliPDA',
				//	'cliDiaPDA',
				'cliTMKT',
				//	'cliDiaTMKT',
				'cliPE',
				//	'cliDiaPE',
				'tit',
				'dev',
				'bonif',
				'bonusG',
				'bonusU',
				'bonus',
				'pedW',
				'cliW',
				'vendaW',
		        'vendaDiaW',
		        'pedW',
		        'pedWdia',
		);
		
		$this->_linhas_resumo = array();
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
			
	private function setaCampos($diaIni = ''){
		if($diaIni != ''){
			$this->getPeriodos($diaIni);
			$mes_1 = $this->_periodos[0]['nome'];
			$mes_2 = $this->_periodos[1]['nome'];
		}else{
			$mes_1 = '';
			$mes_2 = '';
		}
		$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'				, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'rca'			, 'etiqueta' => 'ERC'					, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'venda_2'		, 'etiqueta' => 'Venda Mes<br>'.$mes_2	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'venda_1'		, 'etiqueta' => 'Venda Mes<br>'.$mes_1	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'venda'		, 'etiqueta' => 'Venda Mes' 			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'precMed'		, 'etiqueta' => 'Preço Médio' 			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'mixMed'		, 'etiqueta' => 'Mix Médio' 			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'vendaPDA'	, 'etiqueta' => 'Venda Mes PDA' 		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaTMKT'	, 'etiqueta' => 'Venda Mes TMKT' 		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaPE'		, 'etiqueta' => 'Venda Mes PE' 			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaW'		, 'etiqueta' => 'Venda Mes<br>eCommerce', 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'vendaDia'	, 'etiqueta' => 'Venda dia' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaDiaPDA'	, 'etiqueta' => 'Venda Dia PDA' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//Incluído novamente por solicitação do Márcio - 27/03/18
		$this->_relatorio->addColuna(array('campo' => 'pedPDAdia'	, 'etiqueta' => 'Pedidos PDA Dia' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//-----incluido por solicitação da Amanda - 13/03/2020
		$this->_relatorio->addColuna(array('campo' => 'vendaDiaTMKT', 'etiqueta' => 'Venda Dia TMKT' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaDiaW'   , 'etiqueta' => 'Venda Dia<br>eCommerce', 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//------
		//$this->_relatorio->addColuna(array('campo' => 'vendaDiaPE'	, 'etiqueta' => 'Venda Dia PE' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		//$this->_relatorio->addColuna(array('campo' => 'metaMix'		, 'etiqueta' => 'Sugestao Mix' 					, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'realMixT'		, 'etiqueta' => 'Real Mix Total' 				, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'metaMix1'	, 'etiqueta' => 'Sugestao Mix<br>Medicamentos' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'realMix1'	, 'etiqueta' => 'Real Mix<br>Medicamentos' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'metaMix12'	, 'etiqueta' => 'Sugestao Mix<br>Nao Medic.' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'realMix12'	, 'etiqueta' => 'Real Mix<br>Nao Medic.' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'ped'			, 'etiqueta' => 'Numero pedidos Mes' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedDia'		, 'etiqueta' => 'Pedidos dia' 			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'pedPDA'		, 'etiqueta' => 'Pedidos PDA Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'pedTMKT'		, 'etiqueta' => 'Pedidos TMKT Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedTMKTdia'	, 'etiqueta' => 'Pedidos TMKT Dia' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'pedPE'		, 'etiqueta' => 'Pedidos PE Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedPEdia'	, 'etiqueta' => 'Pedidos PE Dia' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pedW'		, 'etiqueta' => 'Pedidos eCom.Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'cli'			, 'etiqueta' => 'Cli Cadastrados' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'cliAtend'	, 'etiqueta' => 'Clientes atendidos' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'cliDia'		, 'etiqueta' => 'Cli.Atendidos Dia' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'cliPDA'		, 'etiqueta' => 'Clientes Atend PDA' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'cliDiaPDA'	, 'etiqueta' => 'Cli.PDA dia' 			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'cliTMKT'		, 'etiqueta' => 'Clientes Atend TMKT' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'cliDiaTMKT'	, 'etiqueta' => 'Cli.TMKT dia' 			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'cliPE'		, 'etiqueta' => 'Clientes Atend PE' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'cliDiaPE'	, 'etiqueta' => 'Cli.PE dia' 			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'cliW'		, 'etiqueta' => 'Clientes Atend eCom.' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'tit'			, 'etiqueta' => 'Titulos venc 90 dias' 	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'dev'			, 'etiqueta' => 'Valor devolucoes'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'bonif'		, 'etiqueta' => 'Valor Bonificacoes'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//		$this->_relatorio->addColuna(array('campo' => 'margem'		, 'etiqueta' => 'Indice'				, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'bonusG'		, 'etiqueta' => 'Bonus Gerado Mes'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'bonusU'		, 'etiqueta' => 'Bonus Utiliz.Mes'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'bonus'		, 'etiqueta' => 'Bonus Saldo'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
	}
	
	function index(){
		$ret = '';
		
		$dados = array();
		$filtro = $this->_relatorio->getFiltro();
		$diaIni = $filtro['DATAINI'];
		$diaFim = $filtro['DATAFIM'];
		
		$this->_relatorio->setTitulo("Relatorio Geral Faturamento.");
		
		if(!$this->_relatorio->getPrimeira() && $diaIni != '' && $diaFim != ''){
			$this->setaCampos($diaIni);
			$this->_relatorio->setTitulo("Relatorio Geral Faturamento. Periodo: ".datas::dataS2D($diaIni)." a ".datas::dataS2D($diaFim));
			
			$this->geraMatriz();
			$this->getVendas($diaIni, $diaFim);
			
			$this->ajustaClientes($diaIni, $diaFim);
			
			$this->getVencidos(90);
			
			$this->getBonus($diaIni, $diaFim);
			
			foreach ($this->_dados as $dado){
				$dados[] = $dado;
			}
		}else{
			$this->setaCampos();
		}
		
		$this->_relatorio->setDados($dados);
		$this->_relatorio->setToExcel(true);

		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	function schedule($param){
		log::gravaLog('geral_faturamento', 'Inicio schedule');

		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		
		$this->_schedule = true;
		$emails = str_replace(',', ';', $param);
		
		$mes = date('m');
		$ano = date('Y');

		//Verifica se é a primeira vez que roda no mês
		if(verificaExecucaoSchedule($this->_programa,$ano.$mes)){
			//Já foi executado no mês
		    if(date('j') > 1 && date('N') == 1){
		        $diaFim = datas::getDataDias(-3);
		    }
		    else{
		        $diaFim = datas::getDataDias(-1);
		    }
			$diaIni = date('Ym').'01';
		}else{
			//Primeira execução no mês (vai calcular mês passado completo)
			gravaExecucaoSchedule($this->_programa,$ano.$mes);
			$mes--;
			if($mes == 0){
				$mes = 12;
				$ano--;
			}
			if($mes < 10){
				$mes = '0'.$mes;
			}
			$diaIni = $ano.$mes.'01';
			$diaFim = date('Ymt',mktime(0,0,0,$mes,15,$ano));
			
		}
//$diaIni = '20200301';
//$diaFim = '20200305';

		log::gravaLog('geral_faturamento', 'Dia Ini: '.$diaIni.'  Dia Fim: '.$diaFim);
		
		//Calcula
		echo '<br>Calculo - Dia Ini: '.$diaIni.'  Dia Fim: '.$diaFim."<br>\n";
		log::gravaLog('geral_faturamento', 'Calculo - Dia Ini: '.$diaIni.'  Dia Fim: '.$diaFim);
		$this->geraMatriz();
		echo "<br>Calculo - Periodos<br>\n";
		log::gravaLog('geral_faturamento', 'Periodos');
		$this->getPeriodos($diaIni);
		echo "<br>Calculo - Vendas<br>\n";
		log::gravaLog('geral_faturamento', 'Vendas');
		$this->getVendas($diaIni, $diaFim);
		echo "<br>Calculo - Ajusta<br>\n";
		log::gravaLog('geral_faturamento', 'Ajustes');
		$this->ajustaClientes($diaIni, $diaFim);
		echo "<br>Calculo - Vencidos<br>\n";
		log::gravaLog('geral_faturamento', 'Vencidos');
		$this->getVencidos(90);
		echo "<br>Calculo - Bonus<br>\n";
		log::gravaLog('geral_faturamento', 'Bonus');
		$this->getBonus($diaIni, $diaFim);

		//Envia Email
		log::gravaLog('geral_faturamento', 'Email - Dia Ini: '.$diaIni.'  Dia Fim: '.$diaFim);
		$this->setaCampos($diaIni);

		$titulo = "Relatorio Geral Faturamento. Data: ".datas::dataS2D($diaIni)." a ".datas::dataS2D($diaFim);
		$this->_relatorio->setTitulo($titulo);
		
		//Transforma o indice no GD
		$temp = $this->_dados;
		$this->_dados = [];
		foreach ($temp as $erc => $linha){
			$super = $this->_rca[$erc]['superCod'];
			$this->_dados[$super][$erc] = $linha;
		}
//print_r($this->_dados);

		if(count($this->_dados) > 0){
			$supervisores = getListaEmailGF('supervisor');
			foreach ($supervisores as $super){
				$dados = array();
				$this->_somaTemp = array();
				if(isset($this->_dados[$super['super']])){
					foreach ($this->_dados[$super['super']] as $key => $rca){
						if($this->verificaItemVazio($rca)){
							$dados[] = $rca;
							$this->_geral[] = $rca;
							$this->somaDados($rca);
						}
					}
				}
				if(count($dados) > 0){
				    $dados[] = $this->_somaTemp;
				    $mensagem = $this->montaResumo($dados);
					$this->_somaTemp['super'] = $super['super'].' - '.$super['nome'];
					$this->_somaSuper[] = $this->_somaTemp;
					$this->_relatorio->setDados($dados);
// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
//					$this->_relatorio->setEnviaTabelaEmail(false);
					$this->_relatorio->setAuto(true);
					$this->_relatorio->setToExcel(true,'Faturamento_geral_'.datas::dataS2D(datas::getDataDias(),4,'.'));
					if(!$this->_teste){
						$this->_relatorio->agendaEmail('','08:00', $this->_programa, $super['email'],$titulo,'',$mensagem);
						log::gravaLog('geral_faturamento_email', $titulo.' Super: '.$super['super'].' - '.$super['email']);
					}
					else{
						$this->_relatorio->enviaEmail('suporte@thielws.com.br','Super  ' . $super['super'], '', $mensagem);
						//$this->_relatorio->agendaEmail('','08:00', $this->_programa, 'suporte@thielws.com.br','Super ' . $titulo,'',$mensagem, '', array(),array(), array(), true);
					}

				}
			}
			$this->_somaTemp = array();
			if(count($this->_somaSuper) > 0){
				foreach ($this->_somaSuper as $dado){
					$this->_geral[] = $dado;
					$this->somaDados($dado);
					$this->_linhas_resumo[] = $dado;
				}
			}
			$this->_somaTemp['super'] = '<b>Total Geral</b>';
			$this->_geral[] = $this->_somaTemp;
			
			//print_r($dados);
// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
//			$this->_relatorio->setEnviaTabelaEmail(false);
			$this->_relatorio->setDados($this->_geral);
			$this->_relatorio->setAuto(true);
			$this->_relatorio->setToExcel(true,'Relatorio_Geral_Faturamento_'.datas::dataS2D(datas::getDataDias(),4,'.'));
			
			if(!$this->_teste){
				$this->_relatorio->agendaEmail('','08:00', $this->_programa, $emails,$titulo);
				log::gravaLog('geral_faturamento_email', $titulo.' Geral: '.$emails);
			}else{
			    $this->_relatorio->enviaEmail('suporte@thielws.com.br','Geral  ' . $super['super']);
				//$this->_relatorio->agendaEmail('','08:00', $this->_programa, 'suporte@thielws.com.br','geral' . $titulo, '', '', '', array(),array(), array(), true);
			}
			print_r($this->_linhas_resumo);
		}
	}
	
	function montaResumo($dados){
	    $ret = '';
	    if(is_array($dados) && count($dados) > 0){
	        $mes_1 = $this->_periodos[0]['nome'];
	        $mes_2 = $this->_periodos[1]['nome'];
	        
	        
	        $tabela = new tabela01();
	        $tabela->setAuto(true);
	        
	        $tabela->addColuna(array('campo' => 'super'		    , 'etiqueta' => 'Regiao'				, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
	        $tabela->addColuna(array('campo' => 'rca'			, 'etiqueta' => 'ERC'					, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
	        $tabela->addColuna(array('campo' => 'venda_2'		, 'etiqueta' => 'Venda Mes<br>'.$mes_2	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'venda_1'		, 'etiqueta' => 'Venda Mes<br>'.$mes_1	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'venda'		    , 'etiqueta' => 'Venda Mes' 			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'vendaPDA'	    , 'etiqueta' => 'Venda Mes PDA' 		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'vendaTMKT'	    , 'etiqueta' => 'Venda Mes TMKT' 		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'vendaPE'		, 'etiqueta' => 'Venda Mes PE' 			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'vendaW'		, 'etiqueta' => 'Venda Mes<br>eCommerce', 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'vendaDia'	    , 'etiqueta' => 'Venda dia' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
	        $tabela->addColuna(array('campo' => 'vendaDiaPDA'	, 'etiqueta' => 'Venda Dia PDA' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
	        
	        
	        $tabela->setDados($dados);
	        $ret = $tabela . '';
	    }
	    return $ret;
	}
	
	function verificaItemVazio($dado){
		$ret = false;
		$soma = 0;
		foreach ($this->_campos as $campo){
			if($campo != 'margem' && $campo != 'bonus' && isset($dado[$campo]))
				$soma += $dado[$campo];
		}
		if($soma > 0){
			$ret = true;
		}
		
		return $ret; 
	}
	
	function somaDados($dado){
		if(count($this->_somaTemp) == 0){
			$this->_somaTemp['super'] 		= '';
			$this->_somaTemp['rca'] 		= '<b>Total</b>';
			foreach ($this->_campos as $campo){
				$this->_somaTemp[$campo] = 0;
			}
		}
		foreach ($this->_campos as $campo){
			if($campo != 'margem')
				$this->_somaTemp[$campo] += $dado[$campo];
		}
	}
	
	function geraMatriz(){
		$rcas = getListaEmailGF('rca');
		foreach ($rcas as $rca){
			$this->_rca[$rca['rca']]['rca'] = $rca['rca'];
			if($this->_schedule){
				$this->_rca[$rca['rca']]['desc'] = $rca['nome'] ;
				$this->_rca[$rca['rca']]['super'] = $rca['super_nome'] ;
			}else{
				$this->_rca[$rca['rca']]['desc'] = $rca['rca'].' - '.$rca['nome'] ;
				$this->_rca[$rca['rca']]['super'] = $rca['super'].' - '.$rca['super_nome'] ;
			}
			$this->_rca[$rca['rca']]['superCod'] = $rca['super'];
		}
//print_r($this->_rca);die();
		foreach ($this->_rca as $rca){
			$this->addRCA($rca['rca'], $rca['desc'], $rca['super']);
		}
	}
	
	function addRCA($rca, $desc = '', $super = ''){
//print_r($this->_campos );die();
		foreach ($this->_campos as $campo){
			$this->_dados[$rca][$campo] = 0;
		}
		$this->_dados[$rca]['super'] 		= $super;
		$this->_dados[$rca]['rca'] 			= $desc;
	}
	
	/*
	 * Verifica se o RCA está cadastrado (RCAs que não estão mais ativos)
	 */
	
	function verificaRCA($rca){
		if(!isset($this->_dados[$rca])){
			$dados = getDadosRCA($rca);
			$this->_rca[$rca]['rca'] = $dados['rca'];
			if($this->_schedule){
				$this->_rca[$rca]['desc'] = $dados['nome'] ;
				$this->_rca[$rca]['super'] = $dados['super_nome'] ;
			}else{
				$this->_rca[$rca]['desc'] = $dados['rca'].' - '.$dados['nome'] ;
				$this->_rca[$rca]['super'] = $dados['super'].' - '.$dados['super_nome'] ;
			}
			$this->_rca[$rca]['superCod'] = $dados['super'];
			$this->addRCA($rca,$this->_rca[$rca]['desc'],$this->_rca[$rca]['super']);
		}
		return;
	}
	
	function getVendas($diaIni, $diaFim){
		//----------------------------------- Venda Geral --------------------------------------------------------
		log::gravaLog('geral_faturamento', 'Venda Geral');
		$param = array();
		$campo = 'CODUSUR';
		//$param['origem'] = 'NOL';
		$vendas = vendas1464Campo($campo, $diaIni, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			$this->_dados[$erc]['dev'] 		= $venda['devol'];
			$this->_dados[$erc]['cliAtend'] = $venda['positivacao'];
			$this->_dados[$erc]['venda'] 	= $venda['venda'];
			
			$this->_dados[$erc]['ped'] 		= $venda['pedidos'];
			$this->_dados[$erc]['bonif'] 	= $venda['bonific'];
		}
		
		//----------------------------------- Preço Médio --------------------------------------------------------
		log::gravaLog('geral_faturamento', 'Preço Médio');
		$param = array();
		$param['bonificacao'] = false;
		//$campos = array('CODUSUR', 'CODCLI');
		//$campos = array('ERCCLI', 'CODCLI');
		$preco = calculaPrecoMedio('CODUSUR', $diaIni, $diaFim, $param, false);
		foreach ($preco as $erc => $pre){
			$this->verificaRCA($erc);
			$this->_dados[$erc]['precMed'] 		= $pre;
		}
		
		//----------------------------------- MIX Médio --------------------------------------------------------
		log::gravaLog('geral_faturamento', 'MIX Médio');
		$param = array();
		$param['bonificacao'] = false;
		$mix = calculaMIX('CODUSUR', $diaIni, $diaFim, $param, false);
		foreach ($mix as $erc => $m){
			$this->verificaRCA($erc);
			$this->_dados[$erc]['mixMed'] 		= $m;
		}
		
		//----------------------------------- MIX por depto -----------------------------------------------------
		log::gravaLog('geral_faturamento', 'MIX por depto');
		$param = array();
		$campo = array('CODCLI','CODUSUR','CODEPTO');
		
		$vendas = vendas1464Campo($campo, $diaIni, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $cli => $ven){
			foreach ($ven as $erc => $venda){
				foreach ($venda as $depto => $v){
					if($depto == 1){
						$this->_dados[$erc]['realMix1'] += $v['mix'];
					}else{
						$this->_dados[$erc]['realMix12'] += $v['mix'];
					}
				}
			}
		}
		
		
		// ------------------------------------------------------------------ Vendas PDA -----------------
		log::gravaLog('geral_faturamento', 'Vendas PDA');
		$param = array();
		$param['origem'] = 'PDA';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaIni, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			
			$this->_dados[$erc]['pedPDA'] 	= $venda['pedidos'];
			$this->_dados[$erc]['cliPDA'] 	= $venda['positivacao'];
			$this->_dados[$erc]['vendaPDA'] = $venda['venda'];
		}
		
		// ------------------------------------------------------------------ Vendas TMKT -----------------
		log::gravaLog('geral_faturamento', 'Vendas TMKT');
		$param = array();
		$param['origem'] = 'T';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaIni, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			
			$this->_dados[$erc]['pedTMKT'] 	= $venda['pedidos'];
			$this->_dados[$erc]['cliTMKT'] 	= $venda['positivacao'];
			$this->_dados[$erc]['vendaTMKT']= $venda['venda'];
		}
		
		// ------------------------------------------------------------------ Vendas PE -----------------
		log::gravaLog('geral_faturamento', 'Vendas PE');
		$param = array();
		$param['origem'] = 'PE';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaIni, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			
			$this->_dados[$erc]['pedPE'] 	= $venda['pedidos'];
			$this->_dados[$erc]['cliPE'] 	= $venda['positivacao'];
			$this->_dados[$erc]['vendaPE']	= $venda['venda'];
		}
		
		// ------------------------------------------------------------------ Vendas WEB -----------------
		log::gravaLog('geral_faturamento', 'Vendas WEB');
		$param = array();
		$param['origem'] = 'W';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaIni, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			
			$this->_dados[$erc]['pedW'] 	= $venda['pedidos'];
			$this->_dados[$erc]['cliW'] 	= $venda['positivacao'];
			$this->_dados[$erc]['vendaW']	= $venda['venda'];
		}
		
		// ------------------------------------------------------------------ Vendas DIA -----------------
		log::gravaLog('geral_faturamento', 'Vendas DIA');
		$param = array();
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaFim, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			//$this->_dados[$erc]['pedDia'] 		= $venda['pedidos'];
			//$this->_dados[$erc]['cliDia'] 		= $venda['positivacao'];
			$this->_dados[$erc]['vendaDia'] 	= $venda['venda'];
		}
		
		// ------------------------------------------------------------------ Vendas Dia PDA --------------
		log::gravaLog('geral_faturamento', 'Vendas Dia PDA');
		$param = array();
		$param['origem'] = 'PDA';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaFim, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			
			$this->_dados[$erc]['pedPDAdia'] 	= $venda['pedidos'];
			//$this->_dados[$erc]['cliDiaPDA'] 	= $venda['positivacao'];
			$this->_dados[$erc]['vendaDiaPDA'] 	= $venda['venda'];
		}
		// ------------------------------------------------------------------ Vendas Dia TMKT --------------
		log::gravaLog('geral_faturamento', 'Vendas Dia TMKT');
		$param = array();
		$param['origem'] = 'T';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaFim, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			
			$this->_dados[$erc]['pedTMKTdia'] 	= $venda['pedidos'];
			$this->_dados[$erc]['cliDiaTMKT'] 	= $venda['positivacao'];
			$this->_dados[$erc]['vendaDiaTMKT']= $venda['venda'];
		}
		
		// ------------------------------------------------------------------ Vendas Dia PE --------------
		log::gravaLog('geral_faturamento', 'Vendas Dia PE');
		$param = array();
		$param['origem'] = 'PE';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaFim, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			
			$this->_dados[$erc]['pedPEdia'] 	= $venda['pedidos'];
			$this->_dados[$erc]['cliDiaPE'] 	= $venda['positivacao'];
			$this->_dados[$erc]['vendaDiaPE']	= $venda['venda'];
		}
		// ------------------------------------------------------------------ Vendas Dia WEB --------------
		log::gravaLog('geral_faturamento', 'Vendas Dia WEB');
		$param = array();
		$param['origem'] = 'W';
		$campo = 'CODUSUR';
		$vendas = vendas1464Campo($campo, $diaFim, $diaFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
		    $this->verificaRCA($erc);
		    
		    $this->_dados[$erc]['pedWdia'] 	 = $venda['pedidos'];
		    $this->_dados[$erc]['cliDiaW'] 	 = $venda['positivacao'];
		    $this->_dados[$erc]['vendaDiaW'] = $venda['venda'];
		}
		// ------------------------------------------------------------------ Vendas Mes - 1 --------------
		log::gravaLog('geral_faturamento', 'Vendas Mes - 1');
		$param = array();
		$campo = 'CODUSUR';
		$periodoIni = $this->_periodos[0]['diaIni'];
		$periodoFim = $this->_periodos[0]['diaFim'];
		$vendas = vendas1464Campo($campo, $periodoIni, $periodoFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			$this->_dados[$erc]['venda_1'] 	= $venda['venda'];
		}
		
		// ------------------------------------------------------------------ Vendas Mes - 2 --------------
		log::gravaLog('geral_faturamento', 'Vendas Mes - 2');
		$param = array();
		$campo = 'CODUSUR';
		$periodoIni = $this->_periodos[1]['diaIni'];
		$periodoFim = $this->_periodos[1]['diaFim'];
		$vendas = vendas1464Campo($campo, $periodoIni, $periodoFim, $param, false);
//print_r($vendas);die();
		foreach ($vendas as $erc => $venda){
			$this->verificaRCA($erc);
			$this->_dados[$erc]['venda_2'] 	= $venda['venda'];
		}
	}
	
	function ajustaClientes($diaIni, $diaFim){
		//Clientes ativos
		$sql = "select 
				    erc,
				    count(distinct codcli)
				from (
				        select codusur erc, codcli from PCUSURCLI where codcli not in (select codcli from pcclient where DTEXCLUSAO IS NOT NULL)
				        union all
				        select codusur1 erc, codcli from pcclient where NOT (PCCLIENT.DTEXCLUSAO IS NOT NULL)
				--        union all
				--        select codusur2 erc, codcli from pcclient where NOT (PCCLIENT.DTEXCLUSAO IS NOT NULL)
				    )
				where
				    erc is not null
				group by 
				    erc
				order by 
				    erc";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->verificaRCA($row[0]);
				$this->_dados[$row[0]]['cli'] = $row[1];
			}
		}
		
		
	}
	


	function getVencidos($dias){
		$sql = "
       SELECT  
            pcclient.codusur1,
            sum(pcprest.valor)
        FROM pcprest, pcclient
        WHERE pcprest.dtpag IS NULL
            AND pcprest.codcob IN ('C','001','041','DEP','BK')
            and pcprest.codcli = pcclient.codcli
            and TRUNC(pcprest.dtvenc) <= TRUNC(SYSDATE) - $dias
            and pcprest.valor > 0
        GROUP BY pcclient.codusur1
        ORDER BY pcclient.codusur1
		";
		$rows = query4($sql);
	
		if(count($rows) > 0){
			foreach($rows as $row){
				$this->verificaRCA($row[0]);
				$this->_dados[$row[0]]['tit'] = $row[1];
			}
		}
	}
	

	function getBonus($diaIni, $diaFim){
		foreach ($this->_dados as $key => $dado){
			$sql = "SELECT  nvl(pc_pkg_controlarsaldorca.ccrca_checar_saldo_atual($key),0) VLCORRENTE FROM DUAL ";
			$rows = query4($sql);
			
			$this->_dados[$key]['bonus'] = $rows[0][0];
		}
		
		$sql = "
				select 'C',codusur, sum (vlcorrente - vlcorrenteant)
				from PCLOGRCA
				where data >= TO_DATE('$diaIni', 'YYYYMMDD')and data <= TO_DATE('$diaFim', 'YYYYMMDD')
				    and vlcorrente - vlcorrenteant > 0
				    and numped is not null
				group by codusur
				union all
				select 'D',codusur, sum (vlcorrenteant - vlcorrente)
				from PCLOGRCA
				where data >= TO_DATE('$diaIni', 'YYYYMMDD')and data <= TO_DATE('$diaFim', 'YYYYMMDD')
				    and vlcorrenteant - vlcorrente > 0
				    and numped is not null
				group by codusur
				order by 2
				";
		$rows = query4($sql);
		foreach ($rows as $row){
			if($row[0] == 'C'){
				$this->_dados[$row[1]]['bonusG'] = $row[2];
			}else{
				$this->_dados[$row[1]]['bonusU'] = $row[2];
			}
		}
	}

	private function getPeriodos($diaIni){
		$meses = datas::getMeses($diaIni, datas::getDataDias(-60,$diaIni));
		
		foreach ($meses as $i => $mes){
			if($i > 0){
				$this->_periodos[$i-1]['nome'] = $mes['mes'];
				$this->_periodos[$i-1]['diaIni'] = $mes['anomes'].$mes['diaini'];
				$this->_periodos[$i-1]['diaFim'] = $mes['anomes'].$mes['diafim'];
			}
		}
	}
	
	private function geraResumo($dados){
	    $ret = array();
	    $totais = array();
	    foreach ($this->_campos as $campo){
	        $totais[$campo] = 0;
	    }
	    $nomes_campos = array(
	            'venda_2'      => 'Venda Mês 1',
	            'venda_1'      => 'Venda Mês 2',
	            'venda'        => 'Venda Mês',
	            'vendaPDA'     => 'Venda Mês PDA',
	            'vendaTMKT'    => 'Venda Mês TMKT',
	            'vendaPE'      => 'Venda Mês PE',
	            'vendaDia'     => 'Venda Dia',
	            'vendaDiaPDA'  => 'Venda Dia PDA',
	            'pedPEdia'     => 'Pedidos PE Dia',
	            'vendaDiaTMKT' => 'Venda Dia TMKT',
	        //	'vendaDiaPE'   => '',
	            'realMix1'     => 'Real Mix<br>Medicamentos',
	            'realMix12'    => 'Real Mix<br>Nao Medic.',
	            'ped'          => 'Numero pedidos Mes',
	        //	'pedDia'       => '',
	            'pedPDA'       => 'Pedidos PDA Mês',
	            'pedPDAdia'    => 'Pedidos PDA Dia',
	            'pedTMKT'      => 'Pedidos TMKT Mês',
	            'pedTMKTdia'   => 'Pedidos TMKT Dia',
	            'pedPE'        => 'Pedidos PE Mês',
	            'cli'          => 'Cli Cadastrados',
	            'cliAtend'     => 'Clientes atendidos',
	        //	'cliDia'       => '',
	            'cliPDA'       => 'Clientes Atend PDA',
	        //	'cliDiaPDA'    => '',
	            'cliTMKT'      => 'Clientes Atend TMKT',
	        //	'cliDiaTMKT'   => '',
	            'cliPE'        => 'Clientes Atend PE',
	        //	'cliDiaPE'     => '',
	            'tit'          => 'Titulos venc 90 dias',
	            'dev'          => 'Valor devolucoes',
	            'bonif'        => 'Valor Bonificacoes',
	            'bonusG'       => 'Bonus Gerado Mes',
	            'bonusU'       => 'Bonus Utiliz.Mes',
	            'bonus'        => 'Bonus Saldo',
	            'cliW'         => 'Clientes Atend eCommerce',
	            'vendaW'       => 'Venda Mês<br>eCommerce',
	            'vendaDiaW'    => 'Venda Dia<br>eCommerce',
	            'pedW'         => 'Pedidos eCommerce<br>Mês',
	            'pedWdia'      => 'Pedidos eCommerce<br>Dia',
	    );
	    if(is_array($dados) && count($dados) > 0){
	        foreach ($dados as $linha){
	            foreach ($this->_campos as $campo){
	                $totais[$campo] += $linha[$campo];
	            }
	        }
	        foreach ($this->_campos as $campo){
	            $temp = array();
	            $temp['campo'] = $nomes_campos[$campo];
	            $temp['valor'] = $totais[$campo];
	            $ret[] = $temp;
	        }
	    }
	    return $ret;
	}
	
	function montaTabelaCorpo($dados){
	    $ret = '';
	    $mes_1 = isset($this->_periodos[0]['nome']) ? $this->_periodos[0]['nome'] : '';
	    $mes_2 = isset($this->_periodos[1]['nome']) ? $this->_periodos[1]['nome'] : '';
	    
	    $nomes_campos = array(
	        'venda_2'      => 'Venda Mês ' . $mes_2,
	        'venda_1'      => 'Venda Mês ' . $mes_1,
	        'venda'        => 'Venda Mês',
	        'vendaPDA'     => 'Venda Mês PDA',
	        'vendaTMKT'    => 'Venda Mês TMKT',
	        'vendaPE'      => 'Venda Mês PE',
    		'vendaW'       => 'Venda Mês eCommerce',
	    	
	        'vendaDia'     => 'Venda Dia',
	        'vendaDiaPDA'  => 'Venda Dia PDA',
    		'pedPDAdia'    => 'Pedidos PDA Dia',
    		'pedPEdia'     => 'Pedidos PE Dia',
	        
	    	'vendaDiaTMKT' => 'Venda Dia TMKT',
    		'vendaDiaW'    => 'Venda Dia eCommerce',
    		'pedPDA'       => 'Pedidos PDA Mês',
	        //	'vendaDiaPE'   => '',
	        //'realMix1'     => 'Real Mix Medicamentos',
	        //'realMix12'    => 'Real Mix Nao Medic.',
	        //'ped'          => 'Numero pedidos Mes',
	        //	'pedDia'       => '',
	        
	        
	        //'pedTMKT'      => 'Pedidos TMKT Mês',
	        //'pedTMKTdia'   => 'Pedidos TMKT Dia',
	        //'pedPE'        => 'Pedidos PE Mês',
	        //'cli'          => 'Cli Cadastrados',
	        //'cliAtend'     => 'Clientes atendidos',
	        //	'cliDia'       => '',
	        //'cliPDA'       => 'Clientes Atend PDA',
	        //	'cliDiaPDA'    => '',
	        //'cliTMKT'      => 'Clientes Atend TMKT',
	        //	'cliDiaTMKT'   => '',
	        //'cliPE'        => 'Clientes Atend PE',
	        //	'cliDiaPE'     => '',
	        //'tit'          => 'Titulos venc 90 dias',
	        //'dev'          => 'Valor devolucoes',
	        //'bonif'        => 'Valor Bonificacoes',
	        //'bonusG'       => 'Bonus Gerado Mes',
	        //'bonusU'       => 'Bonus Utiliz.Mes',
	        //'bonus'        => 'Bonus Saldo',
	        //'cliW'         => 'Clientes Atend eCommerce',
	        
	        
	        //'pedW'         => 'Pedidos eCommerce Mês',
	        //'pedWdia'      => 'Pedidos eCommerce Dia',
	    );
	    
	    $tipos_campos = array(
	        'venda_2'      => 'v',
	        'venda_1'      => 'v',
	        'venda'        => 'v',
	        'vendaPDA'     => 'v',
	        'vendaTMKT'    => 'v',
	        'vendaPE'      => 'v',
	        'vendaDia'     => 'v',
	        'vendaDiaPDA'  => 'v',
	        'pedPEdia'     => 't',
	        'vendaDiaTMKT' => 'v',
	        //	'vendaDiaPE'  't',
	        'realMix1'     => 't',
	        'realMix12'    => 't',
	        'ped'          => 't',
	        //	'pedDia'      't',
	        'pedPDA'       => 't',
	        'pedPDAdia'    => 't',
	        'pedTMKT'      => 't',
	        'pedTMKTdia'   => 't',
	        'pedPE'        => 't',
	        'cli'          => 't',
	        'cliAtend'     => 't',
	        //	'cliDia'      't',
	        'cliPDA'       => 't',
	        //	'cliDiaPDA'   't',
	        'cliTMKT'      => 't',
	        //	'cliDiaTMKT'  't',
	        'cliPE'        => 't',
	        //	'cliDiaPE'    't',
	        'tit'          => 'v',
	        'dev'          => 'v',
	        'bonif'        => 'v',
	        'bonusG'       => 'v',
	        'bonusU'       => 'v',
	        'bonus'        => 'v',
	        'cliW'         => 't',
	        'vendaW'       => 'v',
	        'vendaDiaW'    => 'v',
	        'pedW'         => 't',
	        'pedWdia'      => 't',
	    );
	    
	    if(is_array($this->_somaTemp) && count($this->_somaTemp) > 0){
	        $tab = new tabela_gmail01();
	        $tab->abreTabela(500);
	        $tab->abreTR(true);
	        $tab->abreTH('<strong>Indicador</strong>', 1);
	        $tab->abreTH('<strong>Total</strong>', 1);
	        $tab->fechaTR();
	        foreach ($this->_somaTemp as $campo => $valor){
	            if(isset($nomes_campos[$campo])){
		            $tab->abreTR();
		            
		            $tab->abreTD($nomes_campos[$campo], 1, 'direita');
		            $tab->fechaTD();
		            
		            $temp = $tipos_campos[$campo] === 'v' ? number_format($valor, 2, ',', '.') : $valor;
		            $tab->abreTD($temp, 1, 'direita');
		            $tab->fechaTD();
		            
		            $tab->fechaTR();
	            }
	        }
	        $tab->fechaTabela();
	        $tab->termos();
	        //enviaEmail('emanuel@thielws.com.br', 'tabela', $teste_tabela . '');
	        $ret .= $tab;
	    }
	    return $ret;
	}
}