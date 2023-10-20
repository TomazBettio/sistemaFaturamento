<?php
/*
* Data Criação: 13/11/2015 - 18:20:57
* Autor: Thiel
*
* Arquivo: tws.indicadorcomprador.inc.php
* 
* 
* 
* 
* Alterções:
*           12/11/2018 - Emanuel - Migração para intranet2
*           29/05/2023 - Thiel - Alteração na query de custo (passada pelo Neto): SUM(NVL(NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTBLOQUEADA,0) -  NVL(PCEST.QTPENDENTE, 0) ,0)) VLCUSTOREAL, --5
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class indicadorcomprador{
	var $funcoes_publicas = array(
		'index' 		=> true,
		'index2' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = 'indicadorCompras';

	// Classe relatorio
	var $_relatorio;
	
	//Dados
	var $_dados = array();
	
	//Indica se é teste
	var $_teste;
	
	//Nome industrias
	var $_industrias;
	
	//Compradores que não devem aparece no envio de email (schedule)
	private $_compradoresForaEmail = array();
	
	public function __construct(){
		set_time_limit(0);
		
		$this->_programa = '000002.indicadorcompras';
		$this->_teste = false;
		
		//$this->_compradoresForaEmail[] = 21;
		$this->_compradoresForaEmail[] = 640;
		$this->_compradoresForaEmail[] = 805;
		$this->_compradoresForaEmail[] = 484;
		$this->_compradoresForaEmail[] = 824;
		$this->_compradoresForaEmail[] = 1164;
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		//$this->_relatorio->addColuna(array('campo' => ''	, 'etiqueta' => ''	, 'tipo' => 'T', 'width' =>  80, 'class' => 'E'));
				
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Mes'		, 'variavel' => 'MES', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '01=01;02=02;03=03;04=04;05=05;06=06;07=07;08=08;09=09;10=10;11=11;12=12'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Ano'		, 'variavel' => 'ANO', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '2015=2015;2016=2016;2017=2017;2018=2018'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Tipo'		, 'variavel' => 'TIPO', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sintetico;A=Analitico'));
	}

	private function colunas($tipo = 'S'){
		$this->_relatorio->addColuna(array('campo' => 'comp'		, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Comprador'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));

		if($tipo == 'A'){
			$this->_relatorio->addColuna(array('campo' => 'indCod'	, 'etiqueta' => 'Industria'		, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'indNome'	, 'etiqueta' => 'Industria Nome', 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		}
		
		$this->_relatorio->addColuna(array('campo' => 'vcompra'		, 'etiqueta' => 'Vl.Compras'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pcompra'		, 'etiqueta' => '% Vl.Compras'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vestoque'	, 'etiqueta' => 'Custo Financeiro'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//		$this->_relatorio->addColuna(array('campo' => 'pestoque'	, 'etiqueta' => '% Vl.Est.'		, 'tipo' => 'V', 'width' =>  80, 'class' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vavaria'		, 'etiqueta' => 'Vl.Avaria'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		//		$this->_relatorio->addColuna(array('campo' => 'pavaria'		, 'etiqueta' => '%Vl.Avar'		, 'tipo' => 'V', 'width' =>  80, 'class' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'qitens'		, 'etiqueta' => 'Itens'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pitens'		, 'etiqueta' => '%Itens'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'qfl'			, 'etiqueta' => 'Items FL'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//		$this->_relatorio->addColuna(array('campo' => 'pfl'			, 'etiqueta' => '%It.FL'		, 'tipo' => 'V', 'width' =>  80, 'class' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'cad90dias'	, 'etiqueta' => 'Items Cadast.<br>90 dias'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'qzero'		, 'etiqueta' => 'Itens Zerados'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'zerovalor'	, 'etiqueta' => 'Valor Zerado'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'qzerofl'		, 'etiqueta' => 'It.Zer.FL'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//		$this->_relatorio->addColuna(array('campo' => 'pzerofl'		, 'etiqueta' => '%It.Zer.FL'	, 'tipo' => 'V', 'width' =>  80, 'class' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'qcadastro'	, 'etiqueta' => 'Itens Cadastrados'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//		$this->_relatorio->addColuna(array('campo' => 'pcadastro'	, 'etiqueta' => '%It.Cadast.'	, 'tipo' => 'V', 'width' =>  80, 'class' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'q90'			, 'etiqueta' => 'Itens > 90'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p90'			, 'etiqueta' => '%It. > 90'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'v90'			, 'etiqueta' => 'Vl.Item > 90'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pv90'		, 'etiqueta' => '%Vl. Item > 90', 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'q10'			, 'etiqueta' => 'Itens < 10'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p10'			, 'etiqueta' => '%It. < 10'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'v10'			, 'etiqueta' => 'Vl.Item < 10'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pv10'		, 'etiqueta' => '%Vl Item <10'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		if($tipo == 'S'){
			$this->_relatorio->addColuna(array('campo' => 'indust'		, 'etiqueta' => 'Quant.Indust.'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
		}
		$this->_relatorio->addColuna(array('campo' => 'venda'		, 'etiqueta' => 'Vl.Vendas'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'margem'		, 'etiqueta' => 'Margem'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'verbas'		, 'etiqueta' => 'Bonif/Verbas'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'cmv'			, 'etiqueta' => 'CMV'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
			
	}
	
	public function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();

		$mes	= isset($filtro['MES']) ? $filtro['MES'] : '';
		$ano	= isset($filtro['ANO']) ? $filtro['ANO'] : '';
		$tipo	= isset($filtro['TIPO']) ? $filtro['TIPO'] : '';

		$this->_relatorio->setTitulo("Indicadores Compras ".$mes."/".$ano);
		$this->colunas($tipo);
		if(!$this->_relatorio->getPrimeira()){
			$dados = array(); 
			$this->getDadosMes($mes,$ano,$tipo);
			if($tipo == 'S'){
				foreach ($this->_dados as $dado){
					$dados[] = $dado;
				}
			}else{
				foreach ($this->_dados as $dado){
					foreach ($dado as $d){
						$dados[] = $d;
					}
				}
			}
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}
		
		$ret .= $this->_relatorio;
		return $ret;
	}
	
	public function index2(){
		$anos = array(2017,2016);
		$meses = array('12','11','10','09','08','07','06','05','04','03','02','01');
		$compradores = $this->getCompradores();
		$compradores = ajustaComprador($compradores);
		foreach ($anos as $ano){
			foreach ($meses as $mes){
				if($ano.$mes < '201711'){
					$dataini = $ano.$mes.'01';
					$datafim = date('Ymt',mktime(0,0,0,$mes,15,$ano));
					$anomes = $ano.$mes;
					
					//$this->getDados($dataini, $datafim, 'S');
					//$this->gravaDados($anomes,$tipo);

					$this->getDados($dataini, $datafim, 'A');
					$this->gravaDados($anomes,$tipo);
					
					log::gravaLog("indicadorCompradores_calculo", "Calculo realizado. Tipo: ".$tipo);
				}
			}
		}
	}
	
	/*
	 * Utilizado para enviar dados para o relatório Indicador de Faltas
	 */
	function autoExec($ano, $mes, $tipo){
		$anomes = $ano.$mes;
		$this->colunas($tipo);
		$this->getDadosMes($mes,$ano,$tipo);
	}
	
	/*
	 * Utilizado para enviar dados para o relatório Ciclo Financeiro 2
	 */
	function autoExec2($dataini, $datafim, $tipo){
		$this->getDados($dataini, $datafim, $tipo);
	}
	
	function schedule($param){
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		$exec = '';
		if(trim($param) == 'SINTETICO'){
			$exec = 'C';
			$tipo = 'S';
		}elseif(trim($param) == 'ANALITICO'){
			$exec = 'C';
			$tipo = 'A';
		}else{
			$exec = 'E';
			$tipo = 'S';
			$param = str_replace(',', ';', $param);
			$emails = $param;
			$emails = str_replace(',', ';', $emails);
		}
		log::gravaLog("indicadorCompradores_calculo", "Calculo iniciado. Tipo: ".$tipo);
		
		if($exec == 'C'){
			//Calcula
			$dataini = date('Ym').'01';
			$datafim = date('Ymd');
			$anomes = date('Ym');
			
//$dataini = '20230101';
//$datafim = '20230131';
//$anomes = '202301';
			$this->getDados($dataini, $datafim, $tipo);
//print_r($this->_dados);
			$this->gravaDados($anomes,$tipo);
			log::gravaLog("indicadorCompradores_calculo", "Calculo realizado. Tipo: ".$tipo);
		}
		
		if($exec == 'E'){
			//Envia email
			$mes = date('m');
			$ano = date('Y');
			$anomes = $ano.$mes;
			// Caso seja primeira execução do mes, faz o total do mes anterior
			if(!verificaExecucaoSchedule($this->_programa,$ano.$mes) && $this->_teste === false){
				gravaExecucaoSchedule($this->_programa,$ano.$mes);
				$mes--;
				if($mes == 0){
					$mes = '12';
					$ano--;
				}
				$mes = $mes < 10 ? '0'.$mes : $mes;
				$anomes = $ano.$mes;
			}	
			$titulo = "Indicadores Compras. ".$mes."/".$ano;
			$this->_relatorio->setTitulo($titulo);
			$this->colunas('S');
			$dados = array();
			$this->getDadosMes($mes,$ano,'S');
			if(count($this->_dados) > 0){
				foreach ($this->_dados as $dado){
					if(array_search($dado['comp'], $this->_compradoresForaEmail) === false){
						$dados[] = $dado;
					}
				}
			}

			$this->_relatorio->setDados($dados);
			$this->_relatorio->setAuto(true);
			$this->_relatorio->setToExcel(true);
			
			if(!$this->_teste){
				$this->_relatorio->agendaEmail('','08:00', $this->_programa, $emails,$titulo);
				log::gravaLog("indicadorCompradores", "Enviado email: ".$emails);
				$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			}else{
				$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			}
		}
	}
	
	function gravaDados($anomes,$tipo){
		if(count($this->_dados) > 0){
			$data = date('Ym');
			if($tipo == 'S'){
				$sql = "DELETE FROM gf_compras WHERE anomes = $anomes AND fornec = 0";
			}else{
				$sql = "DELETE FROM gf_compras WHERE anomes = $anomes AND fornec <> 0";
			}
//echo "$sql <br>\n";
			query($sql);
			
			if($tipo == 'S'){
				foreach ($this->_dados as $dado){
					$dado['indCod'] = 0;
					$dado['indNome'] = '';
					
					$this->gravaDado($dado,$anomes);
				}
			}else{
				foreach ($this->_dados as $camp => $dado){
					foreach ($dado as $fornec => $d){
						$d['indust'] = 0;
						
						$this->gravaDado($d,$anomes);
					}
				}
			}
		}
	}
	
	private function gravaDado($dado,$anomes){
		if($dado['comp'] != 0){
			$campos = [];
			$campos['anomes'] 		= $anomes;
			$campos['compradorid'] 	= $dado['comp'];
			$campos['comprador'] 	= $dado['nome'];
			$campos['fornec'] 		= $dado['indCod'];
			$campos['fornecedor'] 	= $dado['indNome'];
			$campos['vlcompra'] 	= $dado['vcompra'];
			$campos['vlestoque'] 	= $dado['vestoque'];
			$campos['vlavaria'] 	= $dado['vavaria'];
			$campos['ativos'] 		= $dado['qitens'];
			$campos['foralinha'] 	= $dado['qfl'];
			$campos['cad90dias'] 	= $dado['cad90dias'];
			$campos['zeroativo'] 	= $dado['qzero'];
			$campos['zerovalor'] 	= $dado['zerovalor'];
			$campos['zerofl'] 		= $dado['qzerofl'];
			$campos['cadastrados'] 	= $dado['qcadastro'];
			$campos['qt90'] 		= $dado['q90'];
			$campos['vl90'] 		= $dado['v90'];
			$campos['qt10'] 		= $dado['q10'];
			$campos['vl10'] 		= $dado['v10'];
			$campos['indust'] 		= $dado['indust'];
			$campos['venda'] 		= $dado['venda'];
			$campos['margem'] 		= $dado['margem'];
			$campos['verbas'] 		= $dado['verbas'];
			$campos['cmv'] 			= $dado['cmv'];
			
			$sql = montaSQL($campos, 'gf_compras');

//echo "$sql <br>";
			query($sql);
		}
	}

	function getDadosMes($mes,$ano,$tipo='S'){
		
		if($tipo == 'S'){
			$sql = "SELECT * FROM  gf_compras WHERE  anomes = ".$ano.$mes." AND fornec = 0 ORDER BY compradorid, fornec";
		}else{
			$sql = "SELECT * FROM  gf_compras WHERE  anomes = ".$ano.$mes." AND fornec <> 0 ORDER BY compradorid, fornec";
		}
//echo "$sql \n<br>";
		$rows = query($sql);
	
		if(count($rows) > 0){
			foreach ($rows as $row){
				$comprador = $row['compradorid'];
				$industria = $row['fornec'];
				$this->geraEstruturaDados($comprador, $industria, $tipo);
				
				if($tipo == 'S'){
					$this->_dados[$comprador]['comp'		] = $row['compradorid'];
					$this->_dados[$comprador]['nome'		] = $row['comprador'];
					$this->_dados[$comprador]['vcompra'		] = $row['vlcompra'];
					$this->_dados[$comprador]['vestoque'	] = $row['vlestoque'];
					$this->_dados[$comprador]['vavaria'		] = $row['vlavaria'];
					$this->_dados[$comprador]['qitens'		] = $row['ativos'];
					$this->_dados[$comprador]['qfl'			] = $row['foralinha'];
					$this->_dados[$comprador]['cad90dias'	] = $row['cad90dias'];
					$this->_dados[$comprador]['qzero'		] = $row['zeroativo'];
					$this->_dados[$comprador]['zerovalor'	] = $row['zerovalor'];
					$this->_dados[$comprador]['qzerofl'		] = $row['zerofl'];
					$this->_dados[$comprador]['qcadastro'	] = $row['cadastrados'];
					$this->_dados[$comprador]['q90'			] = $row['qt90'];
					$this->_dados[$comprador]['v90'			] = $row['vl90'];
					$this->_dados[$comprador]['q10'			] = $row['qt10'];
					$this->_dados[$comprador]['v10'			] = $row['vl10'];
					$this->_dados[$comprador]['indust'		] = $row['indust'];
					$this->_dados[$comprador]['venda'		] = $row['venda'];
					$this->_dados[$comprador]['margem'		] = $row['margem'];
					$this->_dados[$comprador]['verbas'		] = $row['verbas'];
					$this->_dados[$comprador]['cmv'		 	] = $row['cmv'];
				}else{
					$this->_dados[$comprador][$industria]['comp'		] = $row['compradorid'];
					$this->_dados[$comprador][$industria]['nome'		] = $row['comprador'];
					$this->_dados[$comprador][$industria]['indCod'		] = $row['fornec'];
					$this->_dados[$comprador][$industria]['incNome'		] = $row['fornecedor'];
					$this->_dados[$comprador][$industria]['vcompra'		] = $row['vlcompra'];
					$this->_dados[$comprador][$industria]['vestoque'	] = $row['vlestoque'];
					$this->_dados[$comprador][$industria]['vavaria'		] = $row['vlavaria'];
					$this->_dados[$comprador][$industria]['qitens'		] = $row['ativos'];
					$this->_dados[$comprador][$industria]['qfl'			] = $row['foralinha'];
					$this->_dados[$comprador][$industria]['cad90dias'	] = $row['cad90dias'];
					$this->_dados[$comprador][$industria]['qzero'		] = $row['zeroativo'];
					$this->_dados[$comprador][$industria]['zerovalor'	] = $row['zerovalor'];
					$this->_dados[$comprador][$industria]['qzerofl'		] = $row['zerofl'];
					$this->_dados[$comprador][$industria]['qcadastro'	] = $row['cadastrados'];
					$this->_dados[$comprador][$industria]['q90'			] = $row['qt90'];
					$this->_dados[$comprador][$industria]['v90'			] = $row['vl90'];
					$this->_dados[$comprador][$industria]['q10'			] = $row['qt10'];
					$this->_dados[$comprador][$industria]['v10'			] = $row['vl10'];
					$this->_dados[$comprador][$industria]['venda'		] = $row['venda'];
					$this->_dados[$comprador][$industria]['margem'		] = $row['margem'];
					$this->_dados[$comprador][$industria]['verbas'		] = $row['verbas'];
					$this->_dados[$comprador][$industria]['cmv'		 	] = $row['cmv'];
				}
			}
			$this->calcPercentuais($tipo);
		}
	}
	
	function calcPercentuais($tipo = 'S'){
		$this->calculaPercentual('vcompra', 'pcompra', $tipo);
		$this->calculaPercentual('vestoque', '', $tipo);
		$this->calculaPercentual('vavaria', '', $tipo);
		$this->calculaPercentual('qitens', 'pitens', $tipo);
		$this->calculaPercentual('qfl', '', $tipo);
		$this->calculaPercentual('qzero', '', $tipo);
		$this->calculaPercentual('zerovalor', '', $tipo);
		$this->calculaPercentual('qzerofl', '', $tipo);
		
		$this->calculaPercentual('qcadastro', '', $tipo);
		$this->calculaPercentual('q90', 'p90', $tipo);
		$this->calculaPercentual('v90', 'pv90', $tipo);
		$this->calculaPercentual('q10', 'p10', $tipo);
		$this->calculaPercentual('v10', 'pv10', $tipo);
		
		if($tipo == 'S'){
			$this->calculaPercentual('indust', '', $tipo);
		}
		$this->calculaPercentual('venda', '', $tipo);
		$this->calculaPercentual('verbas', '', $tipo);
		$this->calculaPercentual('cmv', '', $tipo);
		
	}
	
	function getDados($dataini, $datafim, $tipo = 'S'){
		$this->_dados = array();
		log::gravaLog("indicadorCompradores_calculo", "Calcula Compas $dataini, $datafim, $tipo");
		$this->getCompras($dataini, $datafim, $tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Estoque $dataini, $datafim, $tipo");
		$this->getEstoque($dataini, $datafim, $tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Avarias $dataini, $datafim, $tipo");
		$this->getAvarias($tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Estoque/Dias $dataini, $datafim, $tipo");
		$this->getEstoqueDias($tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Industrias $dataini, $datafim, $tipo");
		$this->getIndustrias($tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Vendas $dataini, $datafim, $tipo");
		$this->getVendas($dataini, $datafim, $tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Verbas $dataini, $datafim, $tipo");
		$this->getVerbas($dataini, $datafim, $tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Percentuais $dataini, $datafim, $tipo");
		$this->calcPercentuais($tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Margem $dataini, $datafim, $tipo");
		$this->getMargem($dataini, $datafim, $tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Margem Total $dataini, $datafim, $tipo");
		$this->getMargemTotal($dataini, $datafim, $tipo);
		
		log::gravaLog("indicadorCompradores_calculo", "Calcula Ajusta Nomes $dataini, $datafim, $tipo");
		$this->ajustaNome($tipo);
		
	}
	
	function getVendas($dataini, $datafim, $tipo = 'S'){
		//pcprodut.codfornec
		$param = array();
		$campo = 'codfornec';
		
		$vendas	= vendas1464Campo($campo, $dataini, $datafim, $param,false);
		$compradores = $this->getCompradores();

		if(count($vendas) > 0){
			foreach ($vendas as $fornec => $venda){
				$comprador = $compradores[$fornec];
				if($tipo == 'S'){
					$this->_dados[$comprador]['venda'] += $venda['venda'];
					$this->_dados[$comprador]['cmv'] += $venda['cmv'];
				}else{
					$this->_dados[$comprador][$fornec]['venda'] += $venda['venda'];
					$this->_dados[$comprador][$fornec]['cmv'] += $venda['cmv'];
				}
			}
		}
	}
	
	function getCompradores(){
		$ret = array();
		$sql = "select codfornec, codcomprador  from pcfornec";
		$rows = query4($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row[0]] = $row[1];
			}
		}
		
		return $ret;
	}
	
	
	function getIndustrias($tipo = 'S'){
		if($tipo == 'A'){
			return;
		}
		$sql = "select 
				    codcomprador,
				    count(*) quant
				from pcfornec
				where revenda = 'S'
				    and dtexclusao is null
				group by
				    codcomprador";
		
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$comprador = $row[0];
				$this->geraEstruturaDados($comprador);
				$this->_dados[$comprador]['indust'] = $row[1];
			}
		}
	}
	function calculaPercentual($campoValor, $campoPercent = '', $tipo){
		$total = 0;
		$this->geraEstruturaDados('total','total',$tipo);
		
		if($tipo == 'S'){
			foreach ($this->_dados as $comprador => $dado){
				$total += $dado[$campoValor];
			}
			$this->_dados['total'][$campoValor] = $total;
			
			if($campoPercent != ''){
				foreach ($this->_dados as $comprador => $dado){
					if($total != 0){
						$this->_dados[$comprador][$campoPercent] = ($dado[$campoValor] / $total) * 100;
					}else{
						$this->_dados[$comprador][$campoPercent] = 0;
					}
				}
				$this->_dados['total'][$campoPercent] = 100;
			}
		}else{
			foreach ($this->_dados as $comprador => $dado){
				foreach ($dado as $fornec => $d){
					$total += $d[$campoValor];
				}
			}
			$this->_dados['total']['total'][$campoValor] = $total;
			
			if($campoPercent != ''){
				foreach ($this->_dados as $comprador => $dado){
					foreach ($dado as $fornec => $d){
						if($total != 0){
							$this->_dados[$comprador][$fornec][$campoPercent] = ($d[$campoValor] / $total) * 100;
						}else{
							$this->_dados[$comprador][$fornec][$campoPercent] = 0;
						}
					}
				}
				$this->_dados['total']['total'][$campoPercent] = 100;
			}
		}
	}
	
	function ajustaNome($tipo){
		$compradores = array();
		if($tipo == 'S'){
			foreach ($this->_dados as $comprador => $dado){
				if($comprador != 'total'){
					$sql = "SELECT nome FROM pcempr WHERE matricula = $comprador";
//echo "$sql <br>\n";
					$rows = query4($sql);
					$this->_dados[$comprador]['nome'] = $rows[0][0];
				}else{
					$this->_dados[$comprador]['nome'] = '<b>Total</b>';
					$this->_dados[$comprador]['comp'] = '';
				}
			}
		}else{
			foreach ($this->_dados as $comprador => $dado){
				foreach ($dado as $ind => $d){
					if($comprador != 'total'){
						if(!isset($compradores[$comprador])){
							$sql = "SELECT nome FROM pcempr WHERE matricula = $comprador";
							$rows = query4($sql);
							$compradores[$comprador] = $rows[0][0];
						}
						$this->_dados[$comprador][$ind]['nome'] = $rows[0][0];
					}else{
						$this->_dados[$comprador][$ind]['nome'] = '<b>Total</b>';
						$this->_dados[$comprador][$ind]['comp'] = '';
					}
				}
			}
		}
	}
	
	function getEstoqueDias($tipo){
		$sql = "SELECT PCPRODUT.CODPROD                  
				     , PCFORNEC.codcomprador          -- 1     
				     , NVL(PCEST.CUSTOREAL, 0) * NVL(PCEST.QTESTGER,0) CUSTOREAL -- 2                                                             
				     , NVL(PCEST.QTESTGER,0) QTESTGER                     -- 3
				     --, (NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0)) QTESTDISP 
				     , NVL(PCEST.QTGIRODIA ,0) QTGIRODIA     -- 4
					 , PCPRODUT.CODFORNEC
				  FROM PCPRODUT                                 
				     , PCEST                                    
				     , PCFORNEC                                 
				     , PCFILIAL                                 
				     , PCCONSUM                                 
				     , PCPRODFILIAL                             
				WHERE  PCPRODUT.CODFORNEC = PCFORNEC.CODFORNEC  
				   AND PCPRODUT.CODPROD = PCEST.CODPROD         
				   AND PCFILIAL.CODIGO = PCEST.CODFILIAL        
				   AND PCPRODUT.CODPROD = PCPRODFILIAL.CODPROD  
				   AND PCEST.CODFILIAL = PCPRODFILIAL.CODFILIAL 
				   AND PCEST.CODFILIAL IN ('1')     
				ORDER BY 
				 	PCPRODUT.CODPROD, 
					PCPRODUT.DESCRICAO,
					PCPRODUT.CODFORNEC
					";
		$rows = query4($sql);
		foreach ($rows as $row){
			$comprador = $row[1];
			$industria = $row['CODFORNEC'];
			$this->geraEstruturaDados($comprador, $industria, $tipo);
			if($row[3] > 0 && $row[4] > 0){
				$dias = $row[3] / $row[4];
				if($dias > 90){
					if($tipo == 'S'){
						$this->_dados[$comprador]['q90']++;
						$this->_dados[$comprador]['v90'] += $row[2];
					}else{
						$this->_dados[$comprador][$industria]['q90']++;
						$this->_dados[$comprador][$industria]['v90'] += $row[2];
					}
				}
				if($dias < 10){
					if($tipo == 'S'){
						$this->_dados[$comprador]['q10']++;
						$this->_dados[$comprador]['v10'] += $row[2];
					}else{
						$this->_dados[$comprador][$industria]['q10']++;
						$this->_dados[$comprador][$industria]['v10'] += $row[2];
					}
				}
			}
			
		}
	}
	
	//--SUM(NVL(PCEST.QTESTGER,0)*NVL(PCEST.CUSTOREAL,0)) VLCUSTOREAL, --5
	//--round(SUM((NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTBLOQUEADA,0) ) * NVL(PCEST.CUSTOFIN,0)),2) VLCUSTOREAL,
	function getEstoque($dataini, $datafim, $tipo){
		$sql = "SELECT            
					PCPRODUT.CODPROD,     -- 0 
					PCPRODUT.OBS2,        -- 1
					PCPRODUT.CODEPTO,     -- 2
					PCDEPTO.DESCRICAO DEPARTAMENTO,   -- 3                 
					SUM(PCEST.QTESTGER - PCEST.qtindeniz) QTESTOQUE,    -- 4   
round(SUM((NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTBLOQUEADA,0) /* -  NVL(PCEST.QTPENDENTE, 0)*/) * NVL(PCEST.CUSTOFIN,0)),2) VLCUSTOREAL,
					PCFORNEC.codcomprador,                                         --6
					to_char(pcprodut.dtcadastro,'YYYYMMDD') DTCADASTRO,             -- 7
					PCPRODUT.CODFORNEC,
					(SYSDATE - pcprodut.dtcadastro) DIASCAD
				FROM                                                                 
				  PCPRODUT,
				  PCEST,                                                     
				  PCFORNEC,                                                          
				  PCDEPTO                                                           
				WHERE                                                                
				  PCPRODUT.CODPROD  = PCEST.CODPROD                                                       
				  AND PCEST.CODFILIAL IN('1')
				  AND PCPRODUT.DTEXCLUSAO IS NULL 
				  AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO                                                       
				  AND PCDEPTO.TIPOMERC NOT IN ('IM','CI')                                                  
				  AND PCPRODUT.CODFORNEC = PCFORNEC.CODFORNEC                                                  
				 GROUP BY     
				   PCFORNEC.codcomprador, 
				   PCPRODUT.CODPROD,               
				   PCPRODUT.CODEPTO,               
				   PCDEPTO.DESCRICAO,              
				   PCPRODUT.OBS2,
				   pcprodut.dtcadastro,
					PCPRODUT.CODFORNEC";
		
		$rows = query4($sql);
		foreach ($rows as $row){
			$comprador = $row[6];
			$industria = $row['CODFORNEC'];
			$this->geraEstruturaDados($comprador, $industria, $tipo);
			
			//Cadastrados no periodo
			if($row[7] >= $dataini && $row[7] <= $datafim){
				if($tipo == 'S'){
					$this->_dados[$comprador]['qcadastro']++;
				}else{
					$this->_dados[$comprador][$industria]['qcadastro']++;
				}
			}
				
			if($tipo == 'S'){
				$this->_dados[$comprador]['vestoque'] += $row['VLCUSTOREAL'];
				if($row['DIASCAD'] <= 90){
					$this->_dados[$comprador]['cad90dias']++;
				}
			}else{
				$this->_dados[$comprador][$industria]['vestoque'] += $row['VLCUSTOREAL'];
				if($row['DIASCAD'] <= 90){
					$this->_dados[$comprador][$industria]['cad90dias']++;
				}
			}
	
			if(trim($row[1]) == 'FL'){
				//Fora de linha
				if($tipo == 'S'){
					$this->_dados[$comprador]['qfl']++;
				}else{
					$this->_dados[$comprador][$industria]['qfl']++;
				}
				if($row[4] == 0){
					//Fora de linha zerado
					if($tipo == 'S'){
						$this->_dados[$comprador]['qzerofl']++;
					}else{
						$this->_dados[$comprador][$industria]['qzerofl']++;
					}
				}
			}else{
				// Ativo
				if($tipo == 'S'){
					$this->_dados[$comprador]['qitens']++;
				}else{
					$this->_dados[$comprador][$industria]['qitens']++;
				}
				if($row[4] == 0){
					// Ativo zerado
					if($tipo == 'S'){
						$this->_dados[$comprador]['qzero']++;
						$this->_dados[$comprador]['zerovalor'] += $this->getValorPerdido($row['CODPROD']);
					}else{
						$this->_dados[$comprador][$industria]['qzero']++;
						$this->_dados[$comprador][$industria]['zerovalor'] += $this->getValorPerdido($row['CODPROD']);
					}
				}
			}
		}
	}
	

	
	function geraEstruturaDados($comprador,$industria = '',$tipo = 'S'){
		if($tipo == 'S'){
			if(!isset($this->_dados[$comprador])){
				$this->_dados[$comprador] = $this->getItem($tipo);
				$this->_dados[$comprador]['comp'] = $comprador;
			}
		}else{
			if(!isset($this->_dados[$comprador][$industria])){
				$this->_dados[$comprador][$industria]= $this->getItem($tipo);
				$this->_dados[$comprador][$industria]['comp'] = $comprador;
				$this->_dados[$comprador][$industria]['indCod'] = $industria;
				$this->_dados[$comprador][$industria]['indNome'] = $this->getIndustriaNome($industria);
			}
		}
	}
	
	function getItem($tipo = 'S'){
		$temp = array();
		
		$temp['comp'] = '';
		$temp['nome'] = '';
		if($tipo == 'A'){
			$temp['indCod'] = '';
			$temp['indNome'] = '';
		}
		$temp['vcompra'] = 0;
		$temp['pcompra'] = 0;
		$temp['vestoque'] = 0;
//		$temp['pestoque'] = 0;
		$temp['vavaria'] = 0;
//		$temp['pavaria'] = 0;
		$temp['qitens'] = 0;
		$temp['pitens'] = 0;
		$temp['qfl'] = 0;
		$temp['cad90dias'] = 0;
//		$temp['pfl'] = 0;
		$temp['qzero'] = 0;
		$temp['zerovalor'] = 0;
//		$temp['pzero'] = 0;
		$temp['qzerofl'] = 0;
//		$temp['pzerofl'] = 0;
		$temp['qcadastro'] = 0;
//		$temp['pcadastro'] = 0;
		$temp['q90'] 	= 0;
		$temp['p90'] 	= 0;
		$temp['v90'] 	= 0;
		$temp['pv90'] 	= 0;
		$temp['q10'] 	= 0;
		$temp['p10'] 	= 0;
		$temp['v10'] 	= 0;
		$temp['pv10'] 	= 0;

		if($tipo == 'S'){
			$temp['indust'] = 0;
		}
		$temp['venda'] 	= 0;
		$temp['margem'] = 0;
		$temp['verbas'] = 0;
		$temp['cmv'] 	= 0; 
		
		return $temp;
	}
	
	private function getIndustriaNome($industria){
		$ret = '';
		if($industria == 'total'){
			return '';
		}
		if(!isset($this->_industrias[$industria])){
			$sql = "select FORNECEDOR from pcfornec where codfornec = $industria";
			$rows = query4($sql);
			if(isset($rows[0][0])){
				$this->_industrias[$industria] = $rows[0][0];
			}
		}
		$ret = $this->_industrias[$industria];
		
		return $ret;
	}
	
	function getAvarias($tipo){
		$sql = "SELECT 
						CODCOMPRADOR,
				       	SUM(AVARIA) VALOR,
						CODFORNEC
				FROM (SELECT  pcprodut.CODFORNEC, 
				            pcfornec.codcomprador,
				            SUM(PCEST.QTINDENIZ * pcest.custocont) AVARIA
				    FROM PCEST, pcprodut, pcfornec
				    WHERE PCEST.QTINDENIZ > 0
				        and pcest.codprod = pcprodut.codprod
				        and pcprodut.codfornec = pcfornec.codfornec
				    group by pcprodut.codfornec, 
				             pcfornec.codcomprador) avarias
				GROUP BY CODCOMPRADOR, CODFORNEC
					";
		$rows = query4($sql);
		foreach ($rows as $row){
			$comprador = $row[0];
			$industria = $row['CODFORNEC'];
			$this->geraEstruturaDados($comprador, $industria, $tipo);
			//Avarias
			if($tipo == 'S'){
				$this->_dados[$comprador]['vavaria'] += $row[1];
			}else{
				$this->_dados[$comprador][$industria]['vavaria'] += $row[1];
			}
		}		
	}
	
	/*
	 * Retorna o volume de compras no período
	 * 
	 */
	function getCompras($dataini, $datafim, $tipo){
		$sql = "SELECT CODFORNECPRINC,
				       codcomprador, 
				       SUM(CONTADOR) CONTADOR,
				       SUM(VLENTRADA) VLENTRADA,
				       SUM(TOTPESO) TOTPESO,
				       (SUM(B1.DIF) / SUM(B1.TOT)) PRAZOMEDIO
				FROM (SELECT B.CODFORNECPRINC,
				             B.codcomprador,
				             COUNT(DISTINCT(PCNFENT.NUMTRANSENT)) CONTADOR,
				             SUM(NVL(PCMOV.QT, 0) * NVL(PCMOV.PUNIT, 0)) VLENTRADA,
				             SUM(NVL(PCMOV.QT, 0) * NVL(PCPRODUT.PESOBRUTO, 0)) TOTPESO,
				             PCNFENT.NUMTRANSENT
				      FROM PCMOV, PCNFENT, PCPRODUT, PCDEPTO, PCEMPR, PCFORNEC B
				      WHERE PCMOV.NUMTRANSENT = PCNFENT.NUMTRANSENT
				            AND B.CODFORNEC = PCNFENT.CODFORNEC
				            AND B.CODCOMPRADOR = PCEMPR.MATRICULA(+)
				            AND PCNFENT.DTENT BETWEEN TO_DATE('$dataini','YYYYMMDD') AND TO_DATE('$datafim','YYYYMMDD')
				            AND (PCMOV.CODPROD = PCPRODUT.CODPROD)
				            AND (PCPRODUT.CODEPTO = PCDEPTO.CODEPTO)
				            AND PCMOV.DTCANCEL IS NULL
				            AND PCNFENT.CODCONT = (SELECT CODCONTFOR FROM PCCONSUM)
				            AND PCNFENT.TIPODESCARGA IN ('1','5','I')
				            AND NVL(PCMOV.CODOPER,'X') in ('E','EB')
				            AND  PCNFENT.CODFILIAL= 1
				            AND PCDEPTO.TIPOMERC NOT IN ('CI','IM')
				      GROUP BY B.CODFORNECPRINC, PCNFENT.NUMTRANSENT,codcomprador) A,
				      
				     (SELECT SUM((DTVENC - PCLANC.DTEMISSAO) * GREATEST(VALOR, 0)) DIF,
				        DECODE(SUM(NVL(GREATEST(VALOR, 0), 0)),
				        0,
				        1,
				        SUM(NVL(GREATEST(VALOR, 0), 0))) TOT,
				        PCNFENT.NUMTRANSENT
				      FROM PCLANC, PCNFENT
				      WHERE PCLANC.NUMTRANSENT = PCNFENT.NUMTRANSENT
				            AND PCNFENT.DTENT BETWEEN TO_DATE('$dataini','YYYYMMDD') AND TO_DATE('$datafim','YYYYMMDD')
				            AND PCNFENT.CODCONT = (SELECT CODCONTFOR FROM PCCONSUM)
				            AND PCNFENT.TIPODESCARGA IN ('1','5','I')
				            AND  PCNFENT.CODFILIAL=1
				      GROUP BY PCNFENT.NUMTRANSENT) B1
				WHERE A.NUMTRANSENT = B1.NUMTRANSENT(+)
				GROUP BY CODFORNECPRINC,codcomprador ORDER BY VLENTRADA DESC";
//echo "$sql \n\n";		
		$rows = query4($sql);
//print_r($rows);
		foreach ($rows as $row){
			$comprador = $row[1];
			$industria = $row[0];
			$this->geraEstruturaDados($comprador, $industria, $tipo);
			if($tipo == 'S'){
				//Volume de compras
				$this->_dados[$comprador]['vcompra'] += $row[3];
			}else{
				$this->_dados[$comprador][$industria]['vcompra'] += $row[3];
			}
		}
	}
	
	function getVerbas($dataIni, $dataFim, $tipo = 'S'){
		$campo = '';
		$where = 'AND PCFORNEC.CODCOMPRADOR = TABELAPCLANC.CODCOMPRADOR(+)';
		if($tipo == 'A'){
			$campo = ',PCFORNEC.CODFORNEC ';
			$where = 'AND PCFORNEC.CODFORNEC = TABELAPCLANC.CODFORNEC(+)';
		}
		$sql = "
				SELECT 
						PCEMPR.MATRICULA, 
						PCEMPR.NOME,
				       	SUM(NVL(PCMOV.QT,0) * NVL(PCMOV.PUNIT,0)) VLENTRADAS,
				       	SUM(NVL(PCMOV.QT,0) * NVL(PCPRODUT.PESOBRUTO,0)) PESO,
				 		MAX(DECODE(NVL(TABELAPCLANC.PCLANCVALOR,0),0,0,NVL(GREATEST(TABELAPCLANC.PCLANCVALORPRAZO,0),0)/GREATEST(TABELAPCLANC.PCLANCVALOR,0))) PRAZOMEDIO
						$campo
				FROM 
					PCMOV, 
					PCNFENT, 
					PCPRODUT, 
					PCDEPTO, 
					PCEMPR, 
					PCFORNEC,
					(	SELECT PCFORNEC.CODCOMPRADOR,
				          	SUM((PCLANC.DTVENC - PCLANC.DTEMISSAO) * NVL(GREATEST(PCLANC.VALOR, 0),0)) PCLANCVALORPRAZO,
				          	SUM( NVL(GREATEST(PCLANC.VALOR, 0),0)) PCLANCVALOR
							$campo
				  		FROM 
							PCNFENT, 
							PCEMPR, 
							PCFORNEC, 
							PCLANC
				 		WHERE 
							PCLANC.NUMTRANSENT=PCNFENT.NUMTRANSENT
							AND PCFORNEC.CODFORNEC = PCNFENT.CODFORNEC
							AND pcfornec.codcomprador = pcempr.matricula(+)
							AND PCNFENT.DTENT BETWEEN to_date('$dataIni','YYYYMMDD') AND to_date('$dataFim','YYYYMMDD')
							AND PCNFENT.TIPODESCARGA IN ('1','3','5','9','I')
							AND    PCNFENT.CODCONT = (SELECT CODCONTFOR FROM PCCONSUM)
							AND  PCNFENT.CODFILIAL=1
							AND    PCLANC.TIPOPARCEIRO='F' 
				  			AND PCLANC.CODFILIAL = 1
						GROUP BY 
							PCFORNEC.CODCOMPRADOR 
							$campo
					) TABELAPCLANC
				WHERE 
					PCMOV.NUMTRANSENT  = PCNFENT.NUMTRANSENT
				   	AND PCFORNEC.CODFORNEC = PCNFENT.CODFORNEC
				   	AND PCFORNEC.CODCOMPRADOR = PCEMPR.MATRICULA(+)
				   	$where
				   	AND PCNFENT.DTENT BETWEEN to_date('$dataIni','YYYYMMDD') AND to_date('$dataFim','YYYYMMDD')
				   	AND (PCMOV.CODPROD = PCPRODUT.CODPROD)
				   	AND (PCPRODUT.CODEPTO = PCDEPTO.CODEPTO)
				  	AND  PCMOV.DTCANCEL IS NULL
					AND PCNFENT.TIPODESCARGA IN ('5')
					AND NVL(PCMOV.CODOPER,'X') in ('EB')
					AND    PCNFENT.CODCONT = (SELECT CODCONTFOR FROM PCCONSUM)
					AND  PCNFENT.CODFILIAL=1
					AND PCDEPTO.TIPOMERC NOT IN ('CI','IM')
				GROUP BY 
					PCEMPR.MATRICULA, 
					PCEMPR.NOME
					$campo
				";
//echo "$sql \n\n";
		$rows = query4($sql);
//print_r($rows);
		foreach ($rows as $row){
			$comprador = $row[0];
			$industria = isset($row['CODFORNEC']) ? $row['CODFORNEC'] : '';
			$this->geraEstruturaDados($comprador, $industria, $tipo);
			//Valor das entradas
			if($tipo == 'S'){
				$this->_dados[$comprador]['verbas'] += $row[2];
			}else{
				$this->_dados[$comprador][$industria]['verbas'] += $row[2];
			}
		}
	}
	
	function getMargem($dataIni, $dataFim, $tipo){
		$campo = '';
		if($tipo == 'A'){
			$campo = ',PCFORNEC.CODFORNEC';
		}
		$sql = "
				select
				      pcfornec.codcomprador,
				      SUM(VLLIQUIDO) VLLIQUIDO,
				      SUM(BGERADO) BONUS_GERADO,
				      SUM(BCONSUMIDO) BONUS_CONSUMIDO,
				      SUM(tws_margem.VLCUSTOREAL) VLCMV,
				      SUM(RESSARCST) RESSARCIMENTO_ST,
				      SUM(CREDITOICMS) CREDITOICMS,
				      CASE 
				          WHEN SUM(VLLIQUIDO) > 0 
				             THEN ((1-(SUM(tws_margem.VLCUSTOREAL) + SUM(BCONSUMIDO) + SUM(BGERADO) - SUM(RESSARCST) - SUM(CREDITOICMS))/SUM(VLLIQUIDO))*100)
				             ELSE 0
				      END LUCRO
					  $campo
				    from 
				       tws_margem,
				       PCPRODUT,
				       pcfornec
				    where 
				       tws_margem.PRODUTO = PCPRODUT.CODPROD (+)
				       AND PCPRODUT.codfornec = pcfornec.codfornec (+)
				       --and tws_margem.vlbonific = 0
				       AND data >= '$dataIni' AND data <= '$dataFim'
				GROUP BY
				    pcfornec.codcomprador
					$campo
				
				";
//echo "$sql \n\n";
		$rows = query4($sql);
//print_r($rows);
		foreach ($rows as $row){
			$comprador = $row[0];
			$industria = isset($row['CODFORNEC']) ? $row['CODFORNEC'] : '';
			$this->geraEstruturaDados($comprador, $industria, $tipo);
			//Volume de compras
			if($tipo == 'S'){
				$this->_dados[$comprador]['margem'] += $row[7];
			}else{
				$this->_dados[$comprador][$industria]['margem'] += $row[7];
			}
		}
	}
	function getMargemTotal($dataIni, $dataFim, $tipo){
		$sql = "
		select
			CASE
			WHEN SUM(VLLIQUIDO) > 0
			THEN ((1-(SUM(tws_margem.VLCUSTOREAL) + SUM(BCONSUMIDO) + SUM(BGERADO) - SUM(RESSARCST) - SUM(CREDITOICMS))/SUM(VLLIQUIDO))*100)
			ELSE 0
			END LUCRO
		from
			tws_margem
		where
			data >= '$dataIni' AND data <= '$dataFim'
		";
//echo "$sql \n\n";
		$rows = query4($sql);
		//print_r($rows);
		if(count($rows) > 0){
			if($tipo == 'S'){
				$this->_dados['total']['margem'] += $rows[0][0];
			}else{
				$this->_dados['total']['total']['margem'] += $rows[0][0];
			}
		}
	}
	
	private function getValorPerdido($produto){
		$ret = 0;
		$sql = "
				select 
				    ((qtvendmes1 + qtvendmes2 + qtvendmes)/3) QUANT,
				    pctabpr.pvenda PRECO
				from 
				    pcest,
				    pctabpr
				where 
				    pcest.codfilial = 1
				    and pcest.codprod = pctabpr.codprod 
				    and pctabpr.numregiao = 1
				    and pctabpr.codprod = $produto
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			$quant = $rows[0]['QUANT'];
			$valor = $rows[0]['PRECO'];
			if($quant > 0){
				$ret = $quant * $valor;
			}
		}
		
		return $ret;
	}
}



function getComprasData($ano,$mes){
	$ret = array();
	$ret['dataFim'] = date('Ymt',mktime(0,0,0,$mes,15,$ano));
	$ret['dataIni'] = $ano.$mes.'01';
	
	for($i=0;$i<3;$i++){
		$mes--;
		if($mes == 0){
			$mes = 12;
			$ano--;
		}
		if($i == 0){
			$ret['dataFimTri'] = date('Ymt',mktime(0,0,0,$mes,15,$ano));
		}
		if($i == 1){
			$mesT = $mes < 10 ? '0'.$mes : $mes;
			$ret['dataIni90'] = $ano.$mesT.'01';
		}
	}
	
	$mes = $mes < 10 ? '0'.$mes : $mes;
	$ret['dataIniTri'] = $ano.$mes.'01';
	
	return $ret;
}

function getVendasCompras($dataini, $datafim,$compradores,$zerados){
	$ret = array();
	$param = array();
	$param['produto'] = $zerados;
	$campo = array('CODPROD','CODFORNEC');
	
	$vendas	= vendas1464Campo($campo, $dataini, $datafim, $param,false);
//print_r($vendas);
	if(count($vendas) > 0){
		foreach ($vendas as $prod => $v){
			foreach ($v as $fornec => $venda){
				$comprador = $compradores[$fornec];
				//$ret[$comprador][$fornec] += ($venda['quant'] / 3) *  getValorPerdido($prod);
				$ret[$comprador]['venda'] += ($venda['quant'] / 3) *  getValorPerdido($prod);
				$ret[$comprador]['quant']++;
			}
		}
	}
	
	return $ret;
}

function getValorPerdido($produto){
	$ret = 0;
	$sql = "
			select
				pctabpr.pvenda PRECO
			from
				pctabpr
			where
				pctabpr.numregiao = 1
				and pctabpr.codprod = $produto
			";
	$rows = query4($sql);
	if(count($rows) > 0){
		$ret = $rows[0]['PRECO'];
	}
	
	return $ret;
}

function atualizaBancoDados($vendas,$anomes){
	foreach ($vendas as $comp => $venda){
		$sql = "SELECT * FROM gf_compras WHERE anomes = $anomes AND compradorid = $comp AND fornec = 0";
		$rows = query($sql);
		if(count($rows) > 0){
			$sql = "UPDATE gf_compras SET zerovalor = ".$venda['venda'].", zeroativo = ".$venda['quant']."  WHERE anomes = $anomes AND compradorid = $comp AND fornec = 0";
			//echo $sql."<br>\n";
		}else{
			$nome = getNomeComprador($comp);
			$sql = "INSERT INTO gf_compras (anomes, compradorid, comprador, zeroativo, zerovalor)	VALUES ($anomes, $comp, '$nome',".$venda['quant'].",".$venda['venda'].")";
			//echo $sql."<br>\n";
		}
		query($sql);
	}
}

function getNomeComprador($comp){
	$sql = "SELECT nome FROM pcempr WHERE matricula = $comp";
	$rows = query4($sql);
	return $rows[0][0];
}

function getCadastrados90($dataIni,$dataFim){
	$ret = array();
	$sql = "SELECT
     COUNT(*) QUANT,
     PCFORNEC.CODCOMPRADOR,
     PCPRODUT.CODFORNEC
                FROM
                  PCPRODUT,
                  PCFORNEC
                WHERE
                PCPRODUT.DTCADASTRO >= TO_DATE('$dataIni','$dataFim') AND PCPRODUT.DTCADASTRO <= TO_DATE('20170301','YYYYMMDD')
                  AND PCPRODUT.CODFORNEC = PCFORNEC.CODFORNEC
                 GROUP BY
                   PCFORNEC.CODCOMPRADOR,
                   PCPRODUT.CODFORNEC ";
	$rows = query4($sql);
	foreach ($rows as $row){
		if(isset($ret[$row['CODCOMPRADOR']])){
			$ret[$row['CODCOMPRADOR']]++;
		}else{
			$ret[$row['CODCOMPRADOR']] = 1;
		}
	}
}
	
function getEstoque($dataini, $datafim, $tipo){
	$sql = "SELECT
					PCPRODUT.CODPROD,     -- 0
					SUM(PCEST.QTESTGER) QTESTOQUE,    -- 4
					PCFORNEC.codcomprador,                                         --6
					to_char(pcprodut.dtcadastro,'YYYYMMDD') DTCADASTRO,             -- 7
					PCPRODUT.CODFORNEC,
					(SYSDATE - pcprodut.dtcadastro) DIASCAD
				FROM
				  PCPRODUT,
				  PCEST,
				  PCFORNEC,
				  PCDEPTO
				WHERE
				  PCPRODUT.CODPROD  = PCEST.CODPROD
				  AND PCEST.CODFILIAL IN('1')
				  AND PCPRODUT.DTEXCLUSAO IS NULL
				  AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO
				  AND PCDEPTO.TIPOMERC NOT IN ('IM','CI')
				  AND PCPRODUT.CODFORNEC = PCFORNEC.CODFORNEC
				 GROUP BY
				   PCFORNEC.codcomprador,
				   PCPRODUT.CODPROD,
				   PCPRODUT.CODEPTO,
				   PCDEPTO.DESCRICAO,
				   PCPRODUT.OBS2,
				   pcprodut.dtcadastro,
					PCPRODUT.CODFORNEC";
	
	$rows = query4($sql);
	foreach ($rows as $row){
		$comprador = $row[6];
		$industria = $row['CODFORNEC'];
		$this->geraEstruturaDados($comprador, $industria, $tipo);
		
		//Cadastrados no periodo
		if($row[7] >= $dataini && $row[7] <= $datafim){
			if($tipo == 'S'){
				$this->_dados[$comprador]['qcadastro']++;
			}else{
				$this->_dados[$comprador][$industria]['qcadastro']++;
			}
		}
		
		if($tipo == 'S'){
			$this->_dados[$comprador]['vestoque'] += $row[5];
			if($row['DIASCAD'] <= 90){
				$this->_dados[$comprador]['cad90dias']++;
			}
		}else{
			$this->_dados[$comprador][$industria]['vestoque'] += $row[5];
			if($row['DIASCAD'] <= 90){
				$this->_dados[$comprador][$industria]['cad90dias']++;
			}
		}
		
		if(trim($row[1]) == 'FL'){
			//Fora de linha
			if($tipo == 'S'){
				$this->_dados[$comprador]['qfl']++;
			}else{
				$this->_dados[$comprador][$industria]['qfl']++;
			}
			if($row[4] == 0){
				//Fora de linha zerado
				if($tipo == 'S'){
					$this->_dados[$comprador]['qzerofl']++;
				}else{
					$this->_dados[$comprador][$industria]['qzerofl']++;
				}
			}
		}else{
			// Ativo
			if($tipo == 'S'){
				$this->_dados[$comprador]['qitens']++;
			}else{
				$this->_dados[$comprador][$industria]['qitens']++;
			}
			if($row[4] == 0){
				// Ativo zerado
				if($tipo == 'S'){
					$this->_dados[$comprador]['qzero']++;
					$this->_dados[$comprador]['zerovalor'] += $this->getValorPerdido($row['CODPROD']);
				}else{
					$this->_dados[$comprador][$industria]['qzero']++;
					$this->_dados[$comprador][$industria]['zerovalor'] += $this->getValorPerdido($row['CODPROD']);
				}
			}
		}
	}
}

function ajustaComprador($compradores){
	$novo = array();
	$novo[1016	] = 20 ;
	$novo[741	] = 20 ;
	$novo[16555	] = 20 ;
	$novo[992	] = 20 ;
	$novo[16091	] = 20 ;
	$novo[17065	] = 780;
	$novo[16977	] = 20 ;
	$novo[733	] = 20 ;
	$novo[17350	] = 20 ;
	$novo[16148	] = 20 ;
	$novo[17291	] = 20 ;
	$novo[1139	] = 780;
	$novo[17351	] = 780;
	$novo[17190	] = 20 ;
	$novo[17211	] = 20 ;
	$novo[16691	] = 20 ;
	$novo[187	] = 20 ;
	$novo[146	] = 20 ;
	$novo[1060	] = 780;
	$novo[166	] = 20 ;
	$novo[63	] = 20 ;
	$novo[138	] = 20 ;
	$novo[17018	] = 20 ;
	$novo[1118	] = 780;
	$novo[886	] = 20 ;
	$novo[16979	] = 20 ;
	$novo[15849	] = 20 ;
	$novo[17385	] = 20 ;
	$novo[15048	] = 20 ;
	$novo[969	] = 20 ;
	$novo[16485	] = 20 ;
	
	foreach ($novo as $for => $comp){
		if(isset($compradores[$for])){
			
		}else{
			
		}
	}
	
	return $compradores;
}
