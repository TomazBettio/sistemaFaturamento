<?php
/*
* Data Criação: 22/05/2015 - 14:34:56
* Autor: Thiel
*
* Arquivo: class.ora_geral_faturamento_cli.inc.php
*  
*  
* Alterções:
*           31/10/2018 - Emanuel - Migração para intranet2
			31/01/2023 - Rafael Postal - Migração para intranet4
*  
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class geral_fat_cli{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	private $_programa = '';

	// Classe relatorio
	private $_relatorio;
	
	//RCAs
	private $_rca;
	
	//Supervisores
	private $_supervisor;
	
	//Dados
	private $_dados;
	
	//Soma temp
	private $_somaTemp;
	
	//Soma supervisores
	private $_somaSuper;
	
	//Dados gerais
	private $_geral;
	
	//Lista de campos valores
	private $_campos;
	
	//Campos tabela banco
	private $_camposTabela;
	
	//Campos texto
	private $_camposTextos;
	
	// Envia email para Supervisores/RCA (utilizado nos testes)
	private $_teste;
	
	//Quando for teste se envia os emails do ERC para o tester
	private $_enviaEmailERCteste;

	// contagem dos emails
	private $_enviados = 0;
	
	//Quando for teste se envia os emails do Super para o tester
	private $_enviaEmailSuperTeste;
	
	//Indica se envia email para os ERCs
	private $_enviaERC;
	
	//Total por RCA
	private $_totalRCA;
	
	//Total por supervisor
	private $_totalSuper;
	
	//Supervisores
	private $_super;
	
	//ERCs
	private $_erc;
	
	//ERC e Região original do cliente
	private $_ercOriginal;
	
	//Periodos anteriores
	private $_periodos;
	
	function __construct(){
		set_time_limit(0);
		
		// true = não envia email para Super, false = envia
		//$this->_teste = true;
		$this->_teste = false;
		$this->_enviaEmailERCteste = false;
		$this->_enviaEmailSuperTeste = true;
		
		$this->_enviaERC = true;

		$this->_programa = 'geral_fat_cli';
		$this->_relatorio = new relatorio01(array('programa' => $this->_programa));

		$this->_camposTabela = array( 
								'cli' 			=> 'codcli',
								'clinome'		=> 'cliente',
								'rca' 			=> 'rca',
								'rcanome' 		=> 'vendedor',
								'super' 		=> 'super',
								'supernome' 	=> 'supervisor',
								'ims' 			=> 'ims',
								'restricao'		=> 'restricao_fin',
								//'vendaAnt' 		=> 'venda_antorior',
								'venda_1'		=> 'venda_mes_1',
								'venda_2'		=> 'venda_mes_2',
								'venda' 		=> 'venda_mes', 		
								'percPDA' 		=> 'percpda',
								'vendaPDA' 		=> 'venda_pda', 	
								'vendaTMKT' 	=> 'venda_tmkt', 	
								'vendaPE' 		=> 'venda_pe',
								'vendaOL' 		=> 'venda_ol',
								//'vendaDia' 		=> 'venda_dia', 	
								//'vendaDiaPDA' 	=> 'venda_dia_pda',	
								//'vendaDiaTMKT' 	=> 'venda_dia_tmkt',	
								//'vendaDiaPE' 	=> 'venda_dia_pe',	
								//'vendaDiaOL' 	=> 'venda_dia_ol',
								'realMix1'		=> 'mix_real1',
								'realMix12'		=> 'mix_real12',
								'realMix'		=> 'mix_real', 		
								'ped' 			=> 'pedidos', 			
								//'pedDia' 		=> 'pedidos_dia', 		
								'pedPDA' 		=> 'pedidos_pda', 		
								//'pedPDAdia' 	=> 'pedidos_pda_dia', 	
								'pedTMKT' 		=> 'pedidos_tmkt', 		
								//'pedTMKTdia' 	=> 'pedidos_tmkt_dia', 	
								'pedPE' 		=> 'pedidos_pe', 		
								//'pedPEdia' 		=> 'pedidos_pe_dia', 	
								'pedOL' 		=> 'pedidos_ol', 		
								//'pedOLdia' 		=> 'pedidos_ol_dia', 	
								'medicamento' 	=> 'medicamento',
								'naomedic' 		=> 'naomedic',
								//'perfumaria' 	=> 'perfumaria',
								//'fralda' 		=> 'fralda',
								'tit' 			=> 'vencidos', 			
								'dev' 			=> 'devolucoes', 			
								'bonif' 		=> 'bonificacoes', 		
								'bonif_tele'	=> 'bonificacoes_tele',
							//	'margem' 		=> 'indice', 		
								'bonusG' 		=> 'bonus_mais', 		
								'bonusU' 		=> 'bonus_menos',
								'vendaW'		=> 'venda_w',		
								'pedW'			=> 'pedidos_w',		
								'cliW'			=> 'cli_w',
								'limite'		=> 'limite',
								'limiteL'		=> 'limiteL',
								'limiteD'		=> 'limiteD'
		 						);
		$this->_camposTextos = array('clinome','rcanome','supernome','restricao');
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Clientes'		, 'variavel' => 'CLIENTES','tipo' => 'T', 'tamanho' => '60','decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	
		$this->_rca = getListaEmailGF('rca');
		$this->_supervisor = getListaEmailGF('supervisor');
		
	}
	
	private function setaCampos($cadastro = false, $diaIni = ''){
		if($diaIni != ''){
			$this->getPeriodos($diaIni);
			$mes_1 = $this->_periodos[0]['nome'];
			$mes_2 = $this->_periodos[1]['nome'];
		}else{
			$mes_1 = '';
			$mes_2 = '';
		}
		$this->_relatorio->addColuna(array('campo' => 'cli'			, 'etiqueta' => 'Cod.Cli'				, 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'clinome'		, 'etiqueta' => 'Cliente'				, 'tipo' => 'T', 'width' => 450, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'rca'			, 'etiqueta' => 'Cod.ERC'				, 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'rcanome'		, 'etiqueta' => 'ERC'					, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'				, 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'supernome'	, 'etiqueta' => 'Regiao Nome'			, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		
		if($cadastro){
			$this->_relatorio->addColuna(array('campo' => 'rcaCad'		, 'etiqueta' => 'Cod.ERC<br>Cadastro'		, 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'rcanomeCad'	, 'etiqueta' => 'ERC<br>Cadastro'			, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'superCad'	, 'etiqueta' => 'Regiao<br>Cadastro'		, 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'supernomeCad', 'etiqueta' => 'Regiao Nome<br>Cadastro'	, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		}
		
		$this->_relatorio->addColuna(array('campo' => 'ims'			, 'etiqueta' => 'IMS Media Mes'			 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'restricao'	, 'etiqueta' => 'Restricao Financeira'	 		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		
		//$this->_relatorio->addColuna(array('campo' => 'vendaAnt'	, 'etiqueta' => 'Venda Mes Anterior' 	, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'venda_2'		, 'etiqueta' => 'Venda Mes<br>'.$mes_2	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'venda_1'		, 'etiqueta' => 'Venda Mes<br>'.$mes_1	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'venda'		, 'etiqueta' => 'Venda Mes' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'percPDA'		, 'etiqueta' => '% Vend.PDA' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaPDA'	, 'etiqueta' => 'Venda Mes PDA' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaTMKT'	, 'etiqueta' => 'Venda Mes TMKT' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaPE'		, 'etiqueta' => 'Venda Mes PE' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaOL'		, 'etiqueta' => 'Venda Mes OL' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendaW'		, 'etiqueta' => 'Venda Mes<br>eCommerce', 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		//$this->_relatorio->addColuna(array('campo' => 'vendaDia'	, 'etiqueta' => 'Venda dia' 			, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		//$this->_relatorio->addColuna(array('campo' => 'vendaDiaPDA'	, 'etiqueta' => 'Venda Dia PDA' 		, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		//$this->_relatorio->addColuna(array('campo' => 'vendaDiaTMKT', 'etiqueta' => 'Venda Dia TMKT' 		, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		//$this->_relatorio->addColuna(array('campo' => 'vendaDiaPE'	, 'etiqueta' => 'Venda Dia PE' 			, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		//$this->_relatorio->addColuna(array('campo' => 'vendaDiaOL'	, 'etiqueta' => 'Venda Dia OL' 			, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		
		//$this->_relatorio->addColuna(array('campo' => 'metaMix'	, 'etiqueta' => 'Meta Mix' 			, 'tipo' => 'N', 'width' =>  80, 'class' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'realMix1'	, 'etiqueta' => 'Real Mix<br>Medicamentos' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'realMix12'	, 'etiqueta' => 'Real Mix<br>Nao Medic.' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'realMix'		, 'etiqueta' => 'Real Mix<br>Total' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'ped'			, 'etiqueta' => 'Numero pedidos Mes' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedDia'		, 'etiqueta' => 'Pedidos dia' 			, 'tipo' => 'N', 'width' =>  80, 'class' => 'direita'));
		
		$this->_relatorio->addColuna(array('campo' => 'pedPDA'		, 'etiqueta' => 'Pedidos PDA Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedPDAdia'	, 'etiqueta' => 'Pedidos PDA Dia' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'direita'));
		
		$this->_relatorio->addColuna(array('campo' => 'pedTMKT'		, 'etiqueta' => 'Pedidos TMKT Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedTMKTdia'	, 'etiqueta' => 'Pedidos TMKT Dia' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'direita'));
		
		$this->_relatorio->addColuna(array('campo' => 'pedPE'		, 'etiqueta' => 'Pedidos PE Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedPEdia'	, 'etiqueta' => 'Pedidos PE Dia' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'direita'));
		
		$this->_relatorio->addColuna(array('campo' => 'pedOL'		, 'etiqueta' => 'Pedidos OL Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'pedOLdia'	, 'etiqueta' => 'Pedidos OL Dia' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'pedW'		, 'etiqueta' => 'Pedidos eCom.Mes' 		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'medicamento'	, 'etiqueta' => 'Venda Medicamentos' 	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'naomedic'	, 'etiqueta' => 'Venda Nao Medicam.' 	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'perfumaria'	, 'etiqueta' => 'Venda Perfumaria' 		, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		//$this->_relatorio->addColuna(array('campo' => 'fralda'		, 'etiqueta' => 'Venda Fraldas' 		, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		
		$this->_relatorio->addColuna(array('campo' => 'tit'			, 'etiqueta' => 'Titulos venc 90 dias' 					, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'dev'			, 'etiqueta' => 'Valor devolucoes'						, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'bonif'		, 'etiqueta' => 'Valor Bonificacoes<br>FORÇA DE VENDAS'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'bonif_tele'	, 'etiqueta' => 'Valor Bonificacoes<br>TELEVENDAS'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//		$this->_relatorio->addColuna(array('campo' => 'margem'		, 'etiqueta' => 'Indice'						, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		
		$this->_relatorio->addColuna(array('campo' => 'bonusG'		, 'etiqueta' => 'Bonus Gerado Mes'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'bonusU'		, 'etiqueta' => 'Bonus Utiliz.Mes'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'bonus'		, 'etiqueta' => 'Bonus Saldo'			, 'tipo' => 'V', 'width' =>  80, 'class' => 'direita'));
		
		$this->_relatorio->addColuna(array('campo' => 'limite'		, 'etiqueta' => 'Limite Credito'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'limiteL'		, 'etiqueta' => 'Limite Livre'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'limiteD'		, 'etiqueta' => 'Vencimento Limite'		, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'C'));
		
		$this->_campos = $this->_relatorio->getCampos();
		// $this->_campos = [
		// 	'cli',
		// 	'clinome',
		// 	'rca',
		// 	'rcanome',
		// 	'super',
		// 	'supernome',
		// 	'ims',
		// 	'restricao',
		// 	'venda_2',
		// 	'venda_1',
		// 	'venda',
		// 	'percPDA',
		// 	'vendaPDA',
		// 	'vendaTMKT',
		// 	'vendaPE',
		// 	'vendaOL',
		// 	'vendaW',
		// 	'realMix1',
		// 	'realMix12',
		// 	'realMix',
		// 	'ped',
		// 	'pedPDA',
		// 	'pedTMKT',
		// 	'pedPE',
		// 	'pedOL',
		// 	'pedW',
		// 	'medicamento',
		// 	'naomedic',
		// 	'tit',
		// 	'dev',
		// 	'bonif',
		// 	'bonif_tele',
		// 	'bonusG',
		// 	'bonusU',
		// 	'limite',
		// 	'limiteL',
		// 	'limiteD'
		// ];

		// if($cadastro) {
		// 	$campos = ['rcaCad', 'rcanomeCad', 'superCad', 'supernomeCad'];

		// 	$this->_campos = array_merge($this->_campos, $campos);
		// }
	}
			
	function index(){
		$dados = array();
		$filtro = $this->_relatorio->getFiltro();
		$diaIni = isset($filtro['DATAINI']) ? datas::dataS2D($filtro['DATAINI']) : '';
		$diaFim = isset($filtro['DATAFIM']) ? datas::dataS2D($filtro['DATAFIM']) : '';
		$clientes = isset($filtro['CLIENTES']) ? $filtro['CLIENTES'] : '';
	
		if(!$this->_relatorio->getPrimeira() && $diaIni != '' && $diaFim != ''){
			$this->setaCampos(true,datas::dataD2S($diaIni));
			$this->getVendedores();
			$this->getVendas($diaIni, $diaFim, $clientes);
			$this->getVencidos(90, $clientes);
			$this->getBonus($diaIni, $diaFim, $clientes);
			$this->getLinhas($diaIni, $diaFim, $clientes);
			$this->ajustaPercPDA();
//			$this->ajustaMargem($diaIni, $diaFim, $clientes);
			//Ajusta dados do vendedor do cadastro
			
			$this->ajustaDadosCadastro();
			
			$this->ajustaRestricaoFinanceira($clientes);
			$this->incluiClientesZerados($clientes);
			$this->ajustaLimites($clientes);
			
			$this->_relatorio->setTitulo("Relatorio Geral Faturamento Cliente. Periodo: $diaIni a $diaFim");
			foreach ($this->_dados as $dado){
				foreach ($dado as $d){
					$dados[] = $d;
				}
			}
		}else{
			$this->setaCampos(true);
		}
		
		$this->_relatorio->setDados($dados);
		$this->_relatorio->setToExcel(true);
		return $this->_relatorio . '';
	}
	
	function schedule($param){
 		ini_set('display_errors',1);
 		ini_set('display_startup_erros',1);
 		error_reporting(E_ALL);
	
 		$calcula = false;
	
 		$this->getVendedores();
	
 		$semana = date('N');
 		$dia = date('j');

 		if($dia == 1 || $param == 'MENSAL'){
 			$mes = date('n');
 			$ano = date('Y');
 			$mes--;
 			if($mes == 0){
 				$mes = 12;
 				$ano--;
 			}
 			if($mes < 10){
 				$mes = '0'.$mes;
 			}
 			$diaIni = '01/'.$mes.'/'.$ano;
 			$diaFim = date('t/m/Y',mktime(0,0,0,$mes,15,$ano));
 		}elseif($semana <> 6 && $semana <> 7){
 			//Nao e sabado nem domingo
 			$diaFim = datas::dataS2D(datas::getDataDias(-1));
 			$diaIni = '01/'.date('m/Y');
 		}else{
 			return;
 		}
	
 //$diaIni = '01/12/2015';
 //$diaFim = '31/12/2015';
 		$this->setaCampos(true,datas::dataD2S($diaIni));
	
 		if($param == 'MENSAL'){
 			//Recalcula mes anterior para ajustar devolucoes e cancelamentos
 			$calcula = true;
 			log::gravaLog('fat_geral_cliente', 'Calculo Mensal '.$diaIni. " a ".$diaFim);
 		}elseif($param == ''){
 			// Calcula Mes Atual (menos dia primeiro)
 			$calcula = true;
 			log::gravaLog('fat_geral_cliente', 'Calculo Diario '.$diaIni. " a ".$diaFim);
 		}else{
 			//Envia relatório
 			$emails = str_replace(',', ';', $param);
		
 			$titulo = "Relatorio Geral Faturamento Cliente. Data: ".$diaIni. " a ".$diaFim;
 			$this->_relatorio->setTitulo($titulo);
		
 			$this->pegaDadosTabela($diaFim);
 			$this->ajustaDadosCadastro();
 //print_r($this->_dados);			
 			if(count($this->_dados) > 0){
 				foreach ($this->_rca as $rca){
 					$codrca = $rca['rca'];
 					if(isset($this->_dados[$codrca]) && count($this->_dados[$codrca]) > 0){
 						$dados = array();
 						$this->_somaTemp = array();
 						foreach ($this->_dados[$codrca] as $cliente){
 							$dados[] = $cliente;
 							$this->somaDados($cliente,$codrca,$rca['nome']);
 						}
 						$this->ajustaTotalPercVendaPDA();
 						$this->_totalRCA[$rca['super']][$codrca] = $this->_somaTemp;
 						$dados[] = $this->_somaTemp;
 						$this->_relatorio->setDados($dados);
 						$this->_relatorio->setAuto(true);
		
 						$this->_relatorio->setToExcel(true,'Faturamento_Geral_Cliente_'.datas::dataS2D(datas::getDataDias(),4,'.'));
 						$this->_relatorio->setEnviaTabelaEmail(false);
 						if($this->_enviaERC){
 							if(!$this->_teste){
 								log::gravaLog('fat_geral_cliente', 'Enviado email ERC '.$codrca.' para: '.$rca['email']);
 								$this->_relatorio->enviaEmail($rca['email'],$titulo);
 							}else{
 								if($this->_enviaEmailERCteste){
 									// Só envia 10 emails
 									if(!isset($this->_enviados)){
 										$this->_enviados = 0;
 									}
 									$this->_enviados++;
 									if($this->_enviados <= 10){
 										$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$codrca.' - '.$rca['email']);
 										log::gravaLog('fat_geral_cliente', 'Enviado email TESTE ERC '.$codrca.' para: '.$rca['email']);
 									}
 								}
 							}
 						}
 					}
 				}
		
 				$this->_totalSuper = array();
//print_r($this->_supervisor);die();
 				//Supervisores
 				foreach ($this->_supervisor as $super){
 					$codsuper = $super['super'];
 					$dados = array();
 					$this->_somaTemp = array();
 					foreach ($this->_rca as $rca){
 						if($rca['super'] == $codsuper){
 							$codrca = $rca['rca'];
 							if(isset($this->_dados[$codrca]) && count($this->_dados[$codrca]) > 0){
 								foreach ($this->_dados[$codrca] as $cliente){
 									$dados[] = $cliente;
 									$this->somaDados($cliente,'','',$codsuper,$super['nome']);
 								}
							
 							}
 						}
 					}
 					if(count($dados) > 0){
 						$this->ajustaTotalPercVendaPDA();
 						$dados[] = $this->_somaTemp;
 						$this->_totalSuper[$codsuper] = $this->_somaTemp;
 						foreach ($this->_totalRCA[$codsuper] as $codrca => $d){
 							$dados[] = $d;
 						}
 						$this->_relatorio->setDados($dados);
 						$this->_relatorio->setAuto(true);
		
 						$this->_relatorio->setToExcel(true,'Faturamento_Geral_Cliente_'.datas::dataS2D(datas::getDataDias(),4,'.'));
 						$this->_relatorio->setEnviaTabelaEmail(false);
 						if(!$this->_teste){
 							$this->_relatorio->enviaEmail($super['email'],$titulo);
 							log::gravaLog('fat_geral_cliente', 'Enviado email Coordenador para: '.$super['email']);
 						}else{
 							if($this->_enviaEmailSuperTeste){
 								$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.'_'.$codsuper."-SUPER - ".$super['email']);
 								log::gravaLog('fat_geral_cliente', 'Enviado email TESTE Coordenador para: '.$super['email']);
 							}
 						}
 					}
 				}
			
 				//Geral
 				$dados = array();
 				$this->_somaTemp = array();
 				foreach ($this->_supervisor as $super){
 					$codsuper = $super['super'];
 					foreach ($this->_rca as $rca){
 						if($rca['super'] == $codsuper){
 							$codrca = $rca['rca'];
 							if(isset($this->_dados[$codrca]) && count($this->_dados[$codrca]) > 0){
 								foreach ($this->_dados[$codrca] as $cliente){
 									$dados[] = $cliente;
 									$this->somaDados($cliente,'','','','');
 								}
 							}
 						}
 					}	
 				}
 				if(count($dados) > 0){
 					$this->ajustaTotalPercVendaPDA();
 					$dados[] = $this->_somaTemp;
 					foreach ($this->_totalSuper as $codsuper => $totalSuper){
 						$dados[] = $totalSuper;
 						foreach ($this->_totalRCA[$codsuper] as $codrca => $d){
 							$dados[] = $d;
 						}
 					}
 					$this->_relatorio->setDados($dados);
 					$this->_relatorio->setAuto(true);
					
 					$this->_relatorio->setToExcel(true,'Faturamento_Geral_Cliente_'.datas::dataS2D(datas::getDataDias(),4,'.'));
				
 					$this->_relatorio->setEnviaTabelaEmail(false);
				
 					if(!$this->_teste){
 						$this->_relatorio->enviaEmail($emails,$titulo);
 						log::gravaLog('fat_geral_cliente', 'Enviado email geral para: '.$emails);
 					}else{
 						//$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
 						$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
 						//$this->_relatorio->agendaEmail('','08:00', $this->_programa, 'suporte@thielws.com.br',$titulo, '', '', '', array(), array(), array(), false, false);
					
 					}
 				}else{
 					echo "Não existem dados para o relatório Geral <br>\n";
 				}
 			}
 		}
	
 		if($calcula){
 			log::gravaLog('fat_geral_cliente', "Calculando Inicio - $diaIni - $diaFim");
 			$this->getPeriodos(datas::dataD2S($diaIni));
 			log::gravaLog('fat_geral_cliente', "Calculou periodos");
 			$this->getVendedores();
 			log::gravaLog('fat_geral_cliente', "Pegou vendedores");
 			$this->getVendas($diaIni, $diaFim);
 			log::gravaLog('fat_geral_cliente', "Calculou vendas");
 			$this->getVencidos(90);
 			log::gravaLog('fat_geral_cliente', "Calculou vencidos");
 			$this->getBonus($diaIni, $diaFim);
 			log::gravaLog('fat_geral_cliente', "Calculou bonus");
 			$this->getLinhas($diaIni, $diaFim);
 			log::gravaLog('fat_geral_cliente', "Calculou linhas");
 			$this->ajustaPercPDA();
 			log::gravaLog('fat_geral_cliente', "Calculou PDA");
 //			$this->ajustaMargem($diaIni, $diaFim);
 			$this->ajustaRestricaoFinanceira();
 			log::gravaLog('fat_geral_cliente', "Ajustou restricoes financeiras");
 			$this->incluiClientesZerados();
 			log::gravaLog('fat_geral_cliente', "Incluiu zerados");
 			$this->ajustaLimites();
 			log::gravaLog('fat_geral_cliente', "Ajustou limites");
			
 			$this->gravaTabela($diaFim);
 			log::gravaLog('fat_geral_cliente', "Calculando Fim - $diaIni - $diaFim");
 		}
	}
	
	function pegaDadosTabela($diaFim){
		$data = substr(datas::dataD2S($diaFim),0,6);
		$sql = "SELECT * FROM gf_fatgeralclientes WHERE data = '$data' ORDER BY supervisor, vendedor";
//echo "<br>SQL: $sql <br>\n";

		$rows = query($sql);
//print_r($rows);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cliente = $row['codcli'];
				$rca = $row['rca'];
				foreach ($this->_camposTabela as $key => $campo){
					if(isset($row[$campo])){
						$this->_dados[$rca][$cliente][$key] 	= $row[$campo];
					}
				}
			}
		}
//print_r($this->_dados);die();
	}
	
	function gravaTabela($diaFim){
		$data = substr(datas::dataD2S($diaFim),0,6);
		$sql = "DELETE FROM gf_fatgeralclientes WHERE data = '$data'";
//echo "$sql <br>\n";
		query($sql);
//print_r($this->_dados);
//print_r($this->_camposTabela);
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $clientes){
				foreach ($clientes as $dado){
//print_r($dado);
					$campos = array();
					$campos['data'] = $data;
					foreach($this->_camposTabela as $key => $campo){
						if(isset($dado[$key])){
							$campos[$campo] = $dado[$key];
						}
					}
					
					$campos['vendedor'] = str_replace("'", "´", $campos['vendedor']);
					
					$sql = montaSQL($campos, 'gf_fatgeralclientes');
					query($sql);
				}
			}
		}
	}
	
	function geraMatriz($cli, $rca, $super){
		if(!isset($this->_dados[$rca][$cli])){
			//$sql = "select cliente from pcclient where pcclient.codcli = $cli";
			$sql = "select CONVERT( cliente,'WE8ISO8859P1','WE8MSWIN1252') from pcclient where pcclient.codcli = $cli";
			$rows = query4($sql);
			$cliente = str_replace("'",'`',$rows[0][0]);

			foreach ($this->_campos as $campo){
				if(array_search($campo,$this->_camposTextos) === false ){
					$this->_dados[$rca][$cli][$campo] = 0;
				}else{
					$this->_dados[$rca][$cli][$campo] = '';
				}
			}
			
			$nomesuper = $this->_super[$super]['nome'] ?? '';
			$nomerca = $this->_erc[$rca]['nome'] ?? '';
			
			$this->_dados[$rca][$cli]['cli'] 		= $cli;
			//$this->_dados[$rca][$cli]['clinome'] 	= str_replace("'", "�", $cliente);
			$this->_dados[$rca][$cli]['clinome'] 	= $cliente;
			$this->_dados[$rca][$cli]['rca'] 		= $rca;
			$this->_dados[$rca][$cli]['rcanome'] 	= $nomerca;
			$this->_dados[$rca][$cli]['super'] 		= $super;
			$this->_dados[$rca][$cli]['supernome']	= $nomesuper;
	
		}
	}

	function ajustaTotalPercVendaPDA(){
		if($this->_somaTemp['venda'] > 0){
			$this->_somaTemp['percPDA'] = ($this->_somaTemp['vendaPDA'] * 100)/$this->_somaTemp['venda'];
		}
	}
	
	function somaDados($dado,$rca='',$rcaNome='',$super='',$superNome=''){
		if(count($this->_somaTemp) == 0){
			foreach ($this->_campos as $campo){
				$this->_somaTemp[$campo] = 0;
			}
			$this->_somaTemp['cli'] 		= '';
			$this->_somaTemp['clinome'] 	= '<b>Total</b>';
			$this->_somaTemp['rca'] 		= $rca;
			$this->_somaTemp['rcanome'] 	= $rcaNome;
			$this->_somaTemp['super'] 		= $super;
			$this->_somaTemp['supernome'] 	= $superNome;

		}
		foreach ($this->_campos as $campo){
			if(isset($dado[$campo])){
				if($campo != 'margem' && $campo != 'cli' && $campo != 'clinome' && $campo != 'rca' && $campo != 'rcanome' && $campo != 'super' && $campo != 'supernome')
					@$this->_somaTemp[$campo] += $dado[$campo];
			}
		}
	}
	

	function ajustaMargem($dataIni, $dataFim, $clientes = ''){
		$dataIni = datas::dataD2S($dataIni);
		$dataFim= datas::dataD2S($dataFim);
		$param = array();
		if($clientes != ''){
			$param['cliente'] = $clientes;
		}
		$margem = getMargemTWS($dataIni, $dataFim, $param);

		if(count($margem) > 0){
			foreach ($margem as $erc => $mar){
				foreach ($mar as $cli => $m){
					$this->_dados[$erc][$cli]['margem'] 	= $m['margem'];
				}
			}
		}

	}
	
	function getVendas($diaIni, $diaFim, $clientes = ''){
		$mes = substr($diaIni, 3,2);
		$ano = substr($diaIni, 6,4);
//echo "$diaIni - $ano - $mes <br>\n";
//print_r($this->_periodos);

		// ------------------------------------------------------------------ Vendas Mes - 1 --------------
		$param = array();
		if($clientes != ''){
			$param['cliente'] = $clientes;
		}
		$campos = array('CODUSUR','CODCLI');
		$periodoIni = $this->_periodos[0]['diaIni'];
		$periodoFim = $this->_periodos[0]['diaFim'];
		$vendas = vendas1464Campo($campos, $periodoIni, $periodoFim, $param, false);
		foreach ($vendas as $erc => $venda){
			foreach ($venda as $cli => $v){
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['venda_1'] 	= $v['venda'];
			}
		}
		
		// ------------------------------------------------------------------ Vendas Mes - 2 --------------
		$param = array();
		if($clientes != ''){
			$param['cliente'] = $clientes;
		}
		$campos = array('CODUSUR','CODCLI');
		$periodoIni = $this->_periodos[1]['diaIni'];
		$periodoFim = $this->_periodos[1]['diaFim'];
		$vendas = vendas1464Campo($campos, $periodoIni, $periodoFim, $param, false);
		foreach ($vendas as $erc => $venda){
			foreach ($venda as $cli => $v){
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['venda_2'] 	= $v['venda'];
			}
		}
		//------------------------------------------------------------------ Vendas Totais - 
		$param = array();
		$campos = array('CODUSUR','CODCLI');
		
		$dataIni = datas::dataD2S($diaIni);
		$dataFim= datas::dataD2S($diaFim);
		
		if($clientes != ''){
			$param['cliente'] = $clientes;
		}
		$vendas	= vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
//print_r($vendas);		
		if(count($vendas) > 0){
			foreach ($vendas as $erc => $venda){
				foreach ($venda as $cli => $v){
					$super = $this->_erc[$erc]['super'];
					$this->geraMatriz($cli, $erc, $super);
					
					$this->_dados[$erc][$cli]['ped'] 	= $v['pedidos'];
					$this->_dados[$erc][$cli]['venda'] 	= $v['venda'];
					$this->_dados[$erc][$cli]['bonif'] 	= 0;
					$this->_dados[$erc][$cli]['dev'] 	= $v['devol'];
				}
			}
		}

		//------------------------------------------------------------------ Bonificações ---------------- 
		$param = array();
		$param['operacao'] = 5;
		$param['origem'] = 'PDA';
		if($clientes != ''){
			$param['cliente'] = $clientes;
		}
		$campos = array('CODUSUR','CODCLI','CODOPER');
		$ini = datas::dataD2S($diaIni);
		$fim = datas::dataD2S($diaFim);
		$vendas = vendas1464Campo($campos, $ini, $fim, $param, false);
		foreach ($vendas as $erc => $v1){
			foreach ($v1 as $cli => $v2){
				foreach ($v2 as $operacao => $v){
					if($operacao == 'SB'){
						$this->_dados[$erc][$cli]['bonif'] 	= $v['bonific'];
					}
				}
			}
		}

		$param['origem'] = 'T';
		$campos = array('CODUSUR','CODCLI','CODOPER');
		$ini = datas::dataD2S($diaIni);
		$fim = datas::dataD2S($diaFim);
		$vendas = vendas1464Campo($campos, $ini, $fim, $param, false);
		foreach ($vendas as $erc => $v1){
			foreach ($v1 as $cli => $v2){
				foreach ($v2 as $operacao => $v){
					if($operacao == 'SB'){
						$this->_dados[$erc][$cli]['bonif_tele'] 	= $v['bonific'];
					}
				}
			}
		}
		//------------------------------------------------------------------ MIX -------------------------
		$rows = $this->getMixRealizado($diaIni, $diaFim, $clientes);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli 	= $row['cliente'];
				$rca 	= $row['erc'];
				$super 	= $row['super'];
				$depto 	= $row['depto'];
				$this->geraMatriz($cli, $rca, $super);
				
				if($depto == 1){
					$this->_dados[$rca][$cli]['realMix1'] 	= $row['mix'];
				}else{
					$this->_dados[$rca][$cli]['realMix12'] 	= $row['mix'];
				}
				$this->_dados[$rca][$cli]['realMix'] 		+= $row['mix'];
			}
		}
		
		// ------------------------------------------------------------------ Vendas DIA -----------------
		/*/
		$rows = $this->getVendasDetalheGeral1464($diaFim, $diaFim, '',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				$temp['erc'] 		= $erc;
				$temp['cliente']	= $cliente;
				$temp['valor'] 		= $v['venda'];
				$temp['pedidos']	= $v['pedidos'];
				$temp['devolucao']	= $v['devol'];
				$temp['bonificacao']= $v['bonific'];
			
				$this->_dados[$erc][$cli]['pedDia'] 	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaDia'] 	= $row['valor'];
			}
		}
		/*/
		// ------------------------------------------------------------------ Vendas PDA -----------------
		$origem = "'F'";
		$tipofv = "PDA";
		//$rows = $this->getVendasDetalhe($diaIni, $diaFim, $origem,$tipofv,$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'PDA',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedPDA'] 	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaPDA'] 	= $row['valor'];
			}
		}
		
		// ------------------------------------------------------------------ Vendas TMKT -----------------
		$origem = "'T'";
		//$rows = $this->getVendasDetalhe($diaIni, $diaFim, $origem, '',$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'T',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedTMKT']	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaTMKT']	= $row['valor'];
			}
		}

		// ------------------------------------------------------------------ Vendas PE -----------------
		$origem = "'F'";
		$tipofv = "PE";
		//$rows = $this->getVendasDetalhe($diaIni, $diaFim, $origem,$tipofv,$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'PE',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedPE']	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaPE']= $row['valor'];
			}
		}
		
		// ------------------------------------------------------------------ Vendas OL -----------------
		$origem = "'F'";
		$tipofv = "OL";
		//$rows = $this->getVendasDetalhe($diaIni, $diaFim, $origem,$tipofv,$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'OL',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedOL'] 	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaOL']= $row['valor'];
			}
		}
		
		// ------------------------------------------------------------------ Vendas WEB -----------------
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'W',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedW'] 	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaW'] = $row['valor'];
			}
		}
		
		// ------------------------------------------------------------------ Vendas Dia PDA --------------
		/*/
		$origem = "'F'";
		$tipofv = "PDA";
		//$rows = $this->getVendasDetalhe($diaFim, $diaFim, $origem,$tipofv,$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaFim, $diaFim, 'PDA',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedPDAdia'] 		= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaDiaPDA'] 	= $row['valor'];
			}
		}
		/*/
		// ------------------------------------------------------------------ Vendas Dia TMKT --------------
		/*/
		$origem = "'T'";
		//$rows = $this->getVendasDetalhe($diaFim, $diaFim, $origem, '',$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaFim, $diaFim, 'T',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedTMKTdia'] 	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaDiaTMKT'] 	= $row['valor'];
			}
		}
		/*/
		// ------------------------------------------------------------------ Vendas Dia PE --------------
		/*/
		$origem = "'F'";
		$tipofv = "PE";
		//$rows = $this->getVendasDetalhe($diaFim, $diaFim, $origem,$tipofv,$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaFim, $diaFim, 'PE',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedPEdia'] 	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaDiaPE']	= $row['valor'];
			}
		}
		/*/
		// ------------------------------------------------------------------ Vendas OL -----------------
		$origem = "'F'";
		$tipofv = "OL";
		//$rows = $this->getVendasDetalhe($diaFim, $diaFim, $origem,$tipofv,$clientes);
		$rows = $this->getVendasDetalheGeral1464($diaFim, $diaFim, 'OL',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['pedOLdia'] 	= $row['pedidos'];
				$this->_dados[$erc][$cli]['vendaDiaOL'] = $row['valor'];
			}
		}
		
		//----------------------------------------------------------------- IMS
		$ims = $this->getIMS($ano,$mes);
		foreach ($ims as $key1 => $valor){
			foreach ($this->_dados as $key2 => $dado){
				if(isset($this->_dados[$key2][$key1])){
//echo "RCA: $key2  Cliente: $key1 Valor: $valor <br>\n";
					$this->_dados[$key2][$key1]['ims'] = $valor;
				}
			}
		}
		
	
	}
	
	function getLinhas($diaIni, $diaFim, $clientes = ''){
		// ------------------------------------------------------------------ Vendas Medicamento -----------------
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'medicamento',$clientes,false);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				$this->_dados[$erc][$cli]['medicamento'] = $row['valor'];
			}
		}

	
		// ------------------------------------------------------------------ Vendas Fraldas -----------------
		/*/
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'fraldas',$clientes);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				$this->_dados[$erc][$cli]['fralda'] = $row['valor'];
			}
		}
		/*/
		// ------------------------------------------------------------------ Vendas Perfumaria -----------------
		/*/
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'perfumaria',$clientes);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				$this->_dados[$erc][$cli]['perfumaria'] = $row['valor'] - $this->_dados[$erc][$cli]['fralda'];
			}
		}
		/*/
	
		// ------------------------------------------------------------------ Vendas Nao Medicamento -----------------
		$rows = $this->getVendasDetalheGeral1464($diaIni, $diaFim, 'naomedic',$clientes);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['cliente'];
				$erc = $row['erc'];
				$super = $this->_erc[$erc]['super'];
				$this->geraMatriz($cli, $erc, $super);
				$this->_dados[$erc][$cli]['naomedic'] = $row['valor'];
			}
		}
	}
	
	function getIMS($ano,$mes){
		$ret = array();
		$anomes = '201601';
		$sql = "SELECT * FROM gf_ims WHERE ims > 0 ORDER BY cliente, anomes DESC";
		$rows = query($sql);
		
		foreach ($rows as $row){
			if(!isset($ret[$row['cliente']])){
				$ret[$row['cliente']] = $row['ims'];
			}
		}
//print_r($ret);		
		return $ret;
	}
	
	function getVendasDetalheGeral1464($dataIni, $dataFim, $linha = '', $clientes = '',$trace = false, $bonificacao = false){
		$ret = array();
		$param = array();
		$campos = array('CODUSUR','CODCLI');
		
		$dataIni = datas::dataD2S($dataIni);
		$dataFim= datas::dataD2S($dataFim);
		
		if($linha != ''){
			switch ($linha) {
				case 'medicamento':
					$param['depto'] = 1;
					break;
				case 'naomedic':
					$param['depto'] = 12;
					break;
				case 'perfumaria':
					$param['secao'] = 12;
					break;
				case 'fraldas':
					$param['produto'] = "select codprod from pcprodut where descricao like 'FRALDA%'";
					break;
				case 'PDA':
					$param['origem'] = 'PDA';
					break;
				case 'T':
					$param['origem'] = 'T';
					break;
				case 'PE':
					$param['origem'] = 'PE';
					break;
				case 'OL':
					$param['origem'] = 'OL';
					break;
				case 'W':
					$param['origem'] = 'W';
					break;
			}

		}
		
		if($clientes != ''){
			$param['cliente'] = $clientes;
		}
		if($bonificacao){
			//$param['bonificacao'] = true;
		}
		$vendas	= vendas1464Campo($campos, $dataIni, $dataFim, $param, $trace);

		if(count($vendas) > 0){
			foreach ($vendas as $erc => $venda){
				foreach ($venda as $cliente => $v){
					$temp = array();
					$temp['erc'] 		= $erc;
					$temp['cliente']	= $cliente;
					$temp['valor'] 		= $v['venda'];
					$temp['pedidos']	= $v['pedidos'];
					$temp['devolucao']	= $v['devol'];
					$temp['bonificacao']= $v['bonific'];
					
					$ret[] = $temp;
				}
			}
		}

		return $ret;
	}
	
	
	function ajustaPercPDA(){
		foreach ($this->_dados as $key1 => $dados){
			foreach ($dados as $key2 => $dado){
				if($this->_dados[$key1][$key2]['venda'] != 0){
					$this->_dados[$key1][$key2]['percPDA'] = ($this->_dados[$key1][$key2]['vendaPDA'] * 100)/$this->_dados[$key1][$key2]['venda'];
				}else{
					$this->_dados[$key1][$key2]['percPDA'] = 0;
				}
			}
		}
	}

	
	function getVencidos($dias){
		$ret = array();
		$sql = "
		SELECT  
            pcprest.codcli,
            sum(pcprest.valor)
        FROM pcprest
        WHERE pcprest.dtpag IS NULL
            AND pcprest.codcob IN ('C','001','041','DEP','BK')
            and TRUNC(pcprest.dtvenc) <= TRUNC(SYSDATE) - $dias
            and pcprest.valor > 0
        GROUP BY pcprest.codcli

		";
		$rows = query4($sql);
//echo "$sql \n\n\n";	
		if(count($rows) > 0){
			foreach($rows as $row){
				foreach ($this->_rca as $rca){
					if(isset($this->_dados[$rca['rca']][$row[0]])){
						$this->_dados[$rca['rca']][$row[0]]['tit'] = $row[1];
					}
				}
			}
		}
	}
	
	private function ajustaLimites($clientes = ''){
		$limite = array();
		$where = '';
		if(!empty($clientes)){
			$where = " AND pcclient.CODCLI IN ($clientes)";
		}
		$sql = "SELECT 
				    CLIENTES.CODCLI,
				    CLIENTES.LIMCRED,
				    --CLIENTES.SALDO_DEVEDOR
				    (CLIENTES.LIMCRED - CLIENTES.SALDO_DEVEDOR) LIMITE_DISP,
					DT_VENCIMENTO
				FROM(
						SELECT 
							pcclient.CODCLI
							,pcclient.LIMCRED
							,(SELECT SUM(NVL(PCPREST.VALOR,0)) FROM PCPREST WHERE PCPREST.DTPAG IS NULL AND PCPREST.CODCOB NOT IN ('BNF','BNFT','BNTR','BNFR', 'BNRP') AND pcclient.CODCLI = PCPREST.CODCLI) SALDO_DEVEDOR
							,pcclient.dtvenclimcred DT_VENCIMENTO
						FROM 
							pcclient
						WHERE 
							1=1
							$where
						ORDER BY 
							PCCLIENT.codcli
					)CLIENTES
				";
		
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$cli = $row['CODCLI'];
				$limite[$cli]['limite'] = $row['LIMCRED'];
				$limite[$cli]['limiteL'] = $row['LIMITE_DISP'];
				$limite[$cli]['limiteD'] = datas::dataMS2S($row['DT_VENCIMENTO']);
			}
		}
		
		if(count($limite) > 0 && count($this->_dados) > 0){
			foreach ($this->_dados as $erc => $clientes){
				foreach ($clientes as $cli => $dado){
					if(isset($limite[$cli])){
						$this->_dados[$erc][$cli]['limite']  = $limite[$cli]['limite'];
						$this->_dados[$erc][$cli]['limiteL'] = $limite[$cli]['limiteL'];
						$this->_dados[$erc][$cli]['limiteD'] = $limite[$cli]['limiteD'];
					}
				}
			}
		}
	}
	

	function getBonus($diaIni, $diaFim){
		$sql = "
				select 'C',pcpedc.codcli, sum (PCLOGRCA.vlcorrente - PCLOGRCA.vlcorrenteant) VALOR,pcpedc.codusur
                from PCLOGRCA, pcpedc
                where PCLOGRCA.data >= TO_DATE('$diaIni', 'DD/MM/YYYY')and PCLOGRCA.data <= TO_DATE('$diaFim', 'DD/MM/YYYY')
                    and PCLOGRCA.vlcorrente - PCLOGRCA.vlcorrenteant > 0
                    and PCLOGRCA.numped is not null
                    and PCLOGRCA.numped = pcpedc.numped
                group by pcpedc.codcli,pcpedc.codusur
				union all
				select 'D',pcpedc.codcli, sum (PCLOGRCA.vlcorrenteant - PCLOGRCA.vlcorrente) VALOR,pcpedc.codusur
				from PCLOGRCA, pcpedc
				where PCLOGRCA.data >= TO_DATE('$diaIni', 'DD/MM/YYYY')and PCLOGRCA.data <= TO_DATE('$diaFim', 'DD/MM/YYYY')
				    and PCLOGRCA.vlcorrenteant - PCLOGRCA.vlcorrente > 0
				    and PCLOGRCA.numped is not null
                    and PCLOGRCA.numped = pcpedc.numped
				group by pcpedc.codcli,pcpedc.codusur
				order by 2
				";
		$rows = query4($sql);
//echo "$sql \n\n\n";
		foreach ($rows as $row){
			$cli = $row[1];
			$codrca = $row[3];
			if(isset($this->_dados[$codrca][$cli])){
				if($row[0] == 'C'){
					$this->_dados[$codrca][$cli]['bonusG'] = $row[2];
				}else{
					$this->_dados[$codrca][$cli]['bonusU'] = $row[2];
				}
			}
		}
	}
	function getMixRealizado($dataIni,$dataFim, $clientes){
		$ret = array();
		$whereClientes = '';
		if($clientes != ''){
			$whereClientes = "		AND VENDAS.CODCLI IN ($clientes) ";
		}
		$sql = "
		SELECT
			pcsuperv.codsupervisor,
			vendas.codusur,
			vendas.codcli,
			count(distinct vendas.codprod),
			vendas.CODEPTO
		FROM
			(VIEW_VENDAS_RESUMO_FATURAMENTO) VENDAS,
			pcusuari,
			pcsuperv
		WHERE
			vendas.codusur = pcusuari.codusur (+)
			and pcusuari.codsupervisor = pcsuperv.codsupervisor (+)
			and dtsaida >= to_date('$dataIni','DD/MM/YYYY')
			AND dtsaida <= to_date('$dataFim','DD/MM/YYYY')
			AND DTCANCEL IS NULL
			AND NVL(VENDAS.CONDVENDA,0) NOT IN (4, 8, 10,13, 20, 98, 99)
			AND NVL(VENDAS.CODFISCAL,0) NOT IN (522, 622, 722, 532, 632, 732)
			AND vendas.CODEPTO IN (1,12)
			$whereClientes
		group by
			pcsuperv.codsupervisor,
			vendas.codusur,
			vendas.codcli,
			vendas.CODEPTO
		";
		$rows = query4($sql);
		if(count($rows) >0){
			$i = 0;
			foreach ($rows as $row){
				$ret[$i]['cliente'] = $row[2];
				$ret[$i]['super'] 	= $row[0];
				$ret[$i]['erc'] 	= $row[1];
				$ret[$i]['depto'] 	= $row[4];
				$ret[$i]['mix'] 	= $row[3];
				
				$i++;
			}
		}
		return $ret;
	}
	
	/*
	 * Carrega ERCs e Supervisores
	 */
	function getVendedores(){
		$vend = getListaEmailGF('rca',false);
		if(count($vend) > 0){
			foreach ($vend as $v){
				$erc = $v['rca'];
				$this->_erc[$erc]['nome'] = $v['nome'];
				$this->_erc[$erc]['email'] = $v['email'];
				$this->_erc[$erc]['super'] = $v['super'];
				
				$super = $v['super'];
				if(!isset($this->_super[$super])){
					$this->_super[$super]['nome'] = $v['super_nome'];
					$this->_super[$super]['email'] = $v['super_email'];
				}
			}
		}
	}
	
	private function ajustaDadosCadastro(){
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $erc => $dado){
				foreach ($dado as $cliente => $d){
					$info = $this->getErcOriginal($cliente); 
					$this->_dados[$erc][$cliente]['rcaCad'] 		= $info['erc'];
					$this->_dados[$erc][$cliente]['rcanomeCad'] 	= $info['ercNome'];
					$this->_dados[$erc][$cliente]['superCad'] 		= $info['super'];
					$this->_dados[$erc][$cliente]['supernomeCad'] 	= $info['superNome'];
				}
			}
		}
	}
	
	private function getErcOriginal($cliente){
		$ret = ['erc' => '', 'ercNome' => '', 'super' => '', 'superNome' => ''];
		
		if(!isset($this->_ercOriginal[$cliente])){
			$sql = "
					SELECT 
						PCUSUARI.CODUSUR,
					    PCUSUARI.nome NOMEERC,
					    PCUSUARI.CODSUPERVISOR,
					    PCSUPERV.nome NOMESUPER
					FROM 
						PCUSUARI,
					    PCSUPERV
					WHERE 
						PCUSUARI.CODUSUR = (SELECT PCCLIENT.CODUSUR1 FROM PCCLIENT WHERE CODCLI = $cliente)
					    AND PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
					";
			$rows = query4($sql);
			if(count($rows) > 0){
				$ret['erc']			= $rows[0]['CODUSUR'];
				$ret['ercNome']		= $rows[0]['NOMEERC'];
				$ret['super']		= $rows[0]['CODSUPERVISOR'];
				$ret['superNome']	= $rows[0]['NOMESUPER'];   
			}
			$this->_ercOriginal[$cliente] = $ret;
		
		}else{
			$ret = $this->_ercOriginal[$cliente];
		}
		
		return $ret;
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

	private function ajustaRestricaoFinanceira($clientes = ''){
		$sql = "SELECT CODCLI, CODUSUR1, OBS FROM PCCLIENT WHERE BLOQUEIO = 'S' AND DTEXCLUSAO IS NULL";
		if($clientes != ''){
			$sql .= " AND CODCLI IN ($clientes)";
		}
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$erc = $row['CODUSUR1'];
				$cli = $row['CODCLI'];
				$obs = $row['OBS'];
				$super = $this->_erc[$erc]['super'] ?? 15;
				$this->geraMatriz($cli, $erc, $super);
				
				$this->_dados[$erc][$cli]['restricao'] 	= $obs;
			}
		}
	}
	
	private function incluiClientesZerados($clientes = ''){
		$sql = "SELECT CODCLI, CODUSUR1 FROM PCCLIENT WHERE DTEXCLUSAO IS NULL";
		if($clientes != ''){
			$sql .= " AND CODCLI IN ($clientes)";
		}
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$erc = $row['CODUSUR1'];
				$cli = $row['CODCLI'];
				$super = $this->_erc[$erc]['super'] ?? 15;
				$this->geraMatriz($cli, $erc, $super);
			}
		}
	}
}

