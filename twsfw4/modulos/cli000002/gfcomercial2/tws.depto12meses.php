<?php
/*
 * Data Criacao 03/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: 
 * 
 * Altera��es:
 *            17/10/2018 - Emanuel - Migração para intranet2
 *            15/02/2023 - Emanuel - Migração I4
 *            28/02/2023 - Thiel - Eduardo e Daniel solicitaram para não ser mais enviado para os ERCs
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class depto12meses{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Anos e meses
	var $_anomes;
	
	//Dados
	var $_dados;
	
	//Supervisores
	var $_super;
	
	//ERCs
	var $_erc;
	
	//Clientes
	var $_clientes;
	
	//Clientes existentes no mes de referencia (para listar os inexistentes)
	var $_clientesExistentes;
	
	//Linhas
	var $_linhas;
	
	//ERC e Região original do cliente
	var $_ercOriginal;
	
	//Periodos
	var $_periodos;
	
	//Indica que se é teste (não envia email se for)
	var $_teste;
	
	//Quando for teste se envia os emails do ERC para o tester
	var $_enviaEmailERCteste;
	
	//Mostra clientes excluidos?
	private $_mostraExcluidos;
	
	//Indica se deve enviar aos ERCs
	private $_envia_erc;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_enviaEmailERCteste = true;
		
		$this->_periodos = array();
		$this->_mostraExcluidos = false;
		
		$this->_programa = 'depto12meses';
		$this->_relatorio = new relatorio01(array('programa' => $this->_programa, 'print' => false));
		$this->_relatorio->setTextoSemDados('   ');
		$this->_teste = false;
		
		$this->_envia_erc = false;

		/*
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Mes Base'	, 'variavel' => 'MES'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '01=01;02=02;03=03;04=04;05=05;06=06;07=07;08=08;09=09;10=10;11=11;12=12'));
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Ano Base'	, 'variavel' => 'ANO'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '2015=2015;2016=2016;2017=2017;2018=2018;2019=2019'));
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Supervisor'	, 'variavel' => 'SUPER'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getSupervisor();', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'ERC'			, 'variavel' => 'ERC'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getERC();'		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Departamento', 'variavel' => 'DEPTO'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '=;1=Medicamentos;12=Nao Medicamentos'));
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Origem'		, 'variavel' => 'ORIGEM'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'todos=Todas;OL=OL;PE=PE;T=TMKT;PDA=PDA'));
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '9', 'pergunta' => 'Clientes'	, 'variavel' => 'CLIENTE'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '10', 'pergunta' => 'Clientes Principais'	, 'variavel' => 'CLIENTEPRI'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		*/
	}
	
	private function montaColunas($schedule = false){
	    $this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'supervidor'	, 'etiqueta' => 'Regiao Nome'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'rcaCad'		, 'etiqueta' => 'Cod.ERC<br>Cadastro'		, 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'rcanomeCad'	, 'etiqueta' => 'ERC<br>Cadastro'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		//$this->_relatorio->addColuna(array('campo' => 'erc'		, 'etiqueta' => 'ERC'				, 'tipo' => 'T', 'width' =>  80, 'class' => 'esquerda'));
		//$this->_relatorio->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => 'ERC Nome'			, 'tipo' => 'T', 'width' => 250, 'class' => 'esquerda'));
		
		$this->_relatorio->addColuna(array('campo' => 'cli'			, 'etiqueta' => 'Cod.Cliente'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'endereco'	, 'etiqueta' => 'Endereco'			, 'tipo' => 'T', 'width' => 350, 'posicao' => 'E'));
		//$this->_relatorio->addColuna(array('campo' => 'numero'	, 'etiqueta' => 'Numero'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'bairro'		, 'etiqueta' => 'Bairro'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cidade'		, 'etiqueta' => 'Cidade'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'uf'			, 'etiqueta' => 'Estado'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'ims'			, 'etiqueta' => 'Potencial<br>IMS'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));

		if(count($this->_periodos) > 0){
			foreach ($this->_periodos as $periodo){
				$this->_relatorio->addColuna(array('campo' => $periodo['anomes'].'venda1'	, 'etiqueta' => $periodo['desc'].'<br>Medicamento<br>(sem OL)'		, 'tipo' => 'V', 'width' => 120, 'posicao' => 'D'));
				$this->_relatorio->addColuna(array('campo' => $periodo['anomes'].'venda12'	, 'etiqueta' => $periodo['desc'].'<br>Nao Medicamento<br>(sem OL)'	, 'tipo' => 'V', 'width' => 120, 'posicao' => 'D'));
				if($schedule === false){
				    $this->_relatorio->addColuna(array('campo' => $periodo['anomes'].'vendaOL'	, 'etiqueta' => $periodo['desc'].'<br>Venda OL'						, 'tipo' => 'V', 'width' => 120, 'posicao' => 'D'));
				}
				$this->_relatorio->addColuna(array('campo' => $periodo['anomes'].'venda'	, 'etiqueta' => $periodo['desc'].'<br>Venda Total'					, 'tipo' => 'V', 'width' => 120, 'posicao' => 'D'));
				
			}
		}
		//$this->_relatorio->addColuna(array('campo' => 'superCad'	, 'etiqueta' => 'Regiao<br>Cadastro'		, 'tipo' => 'T', 'width' => 80, 'class' => 'esquerda'));
		//$this->_relatorio->addColuna(array('campo' => 'supernomeCad', 'etiqueta' => 'Regiao Nome<br>Cadastro'	, 'tipo' => 'T', 'width' => 150, 'class' => 'esquerda'));
	}
	
	function index(){
		$filtro = $this->_relatorio->getFiltro();
		
		$mes 	= isset($filtro['MES']) ? $filtro['MES'] : '';
		$ano 	= isset($filtro['ANO']) ? $filtro['ANO'] : '';
		if($mes == ''){
			$mes = date('m');
		}
		if($ano == ''){
			$ano = date('Y');
		}
		
		$mes--;
		$mes = $mes < 10 ? '0'.$mes : $mes;
		if($mes == 0){
			$mes = 12;
			$ano--;
		}
		
		$anomes = $mes.'/'.substr($ano, 2, 2);
		$dtAte 	= $ano.$mes.date('t',mktime(0,0,0,$mes,15,$ano));
		
		for($i=0;$i<12;$i++){
			$mes--;
			if($mes == 0){
				$mes = 12;
				$ano--;
			}
		}
		$mes = $mes < 10 ? '0'.$mes : $mes;
/*/		
		if($mes == 12){
			$mes = '01';
		}else{
			$mes++;
			$ano--;
			$mes = $mes < 10 ? '0'.$mes : $mes;
		}
/*/
		$dtDe = $ano.$mes.'01';

		$super 	= $filtro['SUPER'];
		$erc 	= $filtro['ERC'];
		$depto	= $filtro['DEPTO'];
		$origem = $filtro['ORIGEM'];
		$clientes = str_replace(';', ',', $filtro['CLIENTE']);
		$clientesPrincipais = str_replace(';', ',', $filtro['CLIENTEPRI']);
		
		$this->_relatorio->setTitulo("Venda 12 Meses");
		
		if(!$this->_relatorio->getPrimeira() ){
//echo "$dtDe - $dtAte <br>\n";die();
			//$this->_periodos = datas::getPeriodos(13,$dtDe,'P',true);
		    $this->_periodos = datas::getPeriodos(13,$dtDe,'P',true);
			$this->montaColunas();
			$this->getVendedores();
			$this->_relatorio->setTitulo("Venda 12 Meses. Periodo: ".datas::dataS2D($dtDe)." a ".datas::dataS2D($dtAte));
			
			$this->getDados($dtDe, $dtAte, $anomes,$super,$erc,$depto,$origem,$clientes, $clientesPrincipais, false);
			log::gravaLog('12meses_controle', 'Inicio Ajuste Zero');
			$this->ajustaZerados($anomes,$super,$erc,$depto,$origem,$clientes, $clientesPrincipais);
			log::gravaLog('12meses_controle', 'Final ajuste Zero');

			$dados = array();
			if(count($this->_dados) > 0){
				foreach ($this->_dados as $super => $vendas){
					foreach ($vendas as $erc => $venda){
						foreach ($venda as $v){
							if($this->_mostraExcluidos === true || trim($v['endereco']) != 'INATIVO'){
							//if($this->_mostraExcluidos === true || empty(trim($dados['exclusao']))){
								$dados[] = $v;
							}
						}
					}
				}
			}
			
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}else{
			$this->montaColunas();
		}
		//$this->_relatorio->setPrint(false);
		return $this->_relatorio . '';
	
	}

	
	function schedule($param){
		$emails = str_replace(',', ';', $param);
		$this->_relatorio->setEnviaTabelaEmail(false); //a tabela estava indo no corpo do email e estava gerando problemas
		
		log::gravaLog('depto12meses_email', 'Executando Schedule');
		
		$mes = date('m');
		$ano = date('Y');
		
		if(verificaExecucaoSchedule($this->_programa,$ano.$mes) && $this->_teste === false){
			log::gravaLog('depto12meses_email', 'Já foi executado no mês: '.$mes.'/'.$ano);
			return;
		}
		
		if($this->_teste === false){
		    gravaExecucaoSchedule($this->_programa,$ano.$mes);
		    log::gravaLog('depto12meses_email', 'Executando: '.$mes.'/'.$ano);
		}
		
		
		$mes--;
		if($mes == 0){
			$mes = 12;
			$ano--;
		}
		$mes = $mes < 10 ? '0'.$mes : $mes;
		
		$anomes = $mes.'/'.substr($ano, 2, 2);
		$dtAte 	= $ano.$mes.date('t',mktime(0,0,0,$mes,15,$ano));
		
		//08/10/2020 - Amanda solicitou envio dos 13 últimos meses
		$ano--;
		$dtDe = $ano.$mes.'01';
		
		$this->_periodos = datas::getPeriodos(13,$dtDe,'P',true);

		$this->montaColunas(true);
		$this->getVendedores();
		$titulo = "Venda 12 Meses. Periodo: ".datas::dataS2D($dtDe)." a ".datas::dataS2D($dtAte);
		$this->_relatorio->setTitulo($titulo);
		
		$this->getDados($dtDe, $dtAte, $anomes, '', '', '', '', '', '', true);
		$this->ajustaZerados($anomes);
		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true);
		
		$dados = array();
		
		if(count($this->_dados) > 0){
			$dadosGeral = array();
			foreach ($this->_dados as $super => $vend){
				$dadosSuper = array();
				foreach ($vend as $erc => $vendas){
					$dados = array();
					foreach ($vendas as $v){
						if($this->_mostraExcluidos === true || trim($v['endereco']) != 'INATIVO'){
							$dados[] = $v;
							$dadosGeral[] = $v;
							$dadosSuper[] = $v;
						}
					}
					$this->_relatorio->setDados($dados);
					$email = $this->_erc[$erc]['email'];
					if(!$this->_teste){
						if($this->_envia_erc){
							$this->_relatorio->enviaEmail($email,$titulo);
							log::gravaLog('depto12meses_email', ' Email ERC: '.$erc.' - '.$email);
						}
					}else{
						if($this->_enviaEmailERCteste && count($dados) > 0){
							// Só envia 10 emails
							if(!isset($this->_enviados)){
								$this->_enviados = 0;
							}
							$this->_enviados++;
							if($this->_enviados <= 10){
								$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo. ' - ' . 'ERC' . ' - '.$erc.' - '.$email);
							}
						}
					}
					
				}
				$this->_relatorio->setDados($dadosSuper);
				$email = $this->_super[$super]['email'];
				if(!$this->_teste){
					$this->_relatorio->enviaEmail($email,$titulo);
					log::gravaLog('depto12meses_email', ' Email Super: '.$super.' - '.$email);
				}else{
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo. ' - ' . 'super' . ' - '.$super.' - '.$email);
				}
				
			}
		}
		$this->_relatorio->setDados($dadosGeral);
		
		if(!$this->_teste){
			$this->_relatorio->enviaEmail($emails,$titulo);
			log::gravaLog("depto12meses_email", "Enviado email Geral: ".$emails);
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
		}else{
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
		}
		
		
	}
	
	private function ajustaZerados($anomes,$super='',$erc='',$depto='',$origem='',$clientes='', $clientesPrincipais = ''){
		$where = '';
		
		if($super != '' && $erc == ''){
			$where .= " AND CODUSUR1 in (".$this->getERCsuper($super).")";
		}
		if($erc != ''){
			$where .= " AND CODUSUR1 in ($erc)";
		}
		if($depto != ''){
			//$where .= " AND depto = $depto";
		}
		if($origem != '' && $origem != 'todos'){
			//$where .= " AND origem = '$ori]'";
		}
		if(!empty(trim($clientesPrincipais))){
			$where .= " AND CODCLIPRINC IN ($clientesPrincipais)";
		}elseif($clientes != ''){
			$where .= " AND CODCLI in ($clientes)";
		}
		
		$sql = "
		SELECT
			CODCLI,
			CLIENTE,
			CODUSUR1 ERC,
			ENDERCOM,
			NUMEROCOM,
			BAIRROCOM,
			MUNICCOM,
			ESTCOM,
			DTEXCLUSAO
		FROM
			PCCLIENT
		WHERE
			1=1
			$where
		";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$codcli = $row['CODCLI'];
				$erc = $row['ERC'];
				$super = $this->_erc[$erc]['super'];
				if(!isset($this->_dados[$super][$erc][$codcli])){
					$info = array();
					$info['super'] = $super;
					$info['superNome'] = $this->_super[$super]['nome'];
					$info['erc'] = $erc;
					$info['ercNome'] = $this->_erc[$erc]['nome'];
					
					$this->_dados[$info['super']][$info['erc']][$codcli] = $this->geraMatriz($info, $codcli);
					
					$this->_dados[$info['super']][$info['erc']][$codcli]['endereco']= $row['DTEXCLUSAO'] == '' ? $row['ENDERCOM'].', '.$row['NUMEROCOM'] : ' INATIVO';
					$this->_dados[$info['super']][$info['erc']][$codcli]['bairro'] 	= $row['DTEXCLUSAO'] == '' ? $row['BAIRROCOM'] : '';
					$this->_dados[$info['super']][$info['erc']][$codcli]['cidade'] 	= $row['DTEXCLUSAO'] == '' ? $row['MUNICCOM'] : '';
					$this->_dados[$info['super']][$info['erc']][$codcli]['uf'] 		= $row['DTEXCLUSAO'] == '' ? $row['ESTCOM'] : '';
				}
			}
		}
	}
	
	private function getDados($dtDe, $dtAte, $anomes,$super='',$erc='',$depto='',$origem='',$clientes='', $clientesPrincipais = '', $schedule = false){
		$doisMeses = datas::getDataDias(-30);
		$doisMeses = substr($doisMeses, 0, 6);
		log::gravaLog('12meses_controle', 'Inicio calculo');
		foreach ($this->_periodos as $periodo){
			log::gravaLog('12meses_controle', 'Verifica mes: '.$periodo['anomes']);
			$calcula = $this->verificaDadosGravados($periodo['anomes']);
//echo "Mes: ".$periodo['anomes']." >= ".$doisMeses."<br>\n";
//if($calcula)echo "Calcula<br>\n";else echo "nao Calcula<br>\n";
			if($periodo['anomes'] >= $doisMeses){
				$calcula = true;
			}
//if($calcula)echo "Calcula<br>\n";else echo "nao Calcula<br>\n";
			if($calcula){
				log::gravaLog('12meses_controle', 'Calcula Periodo: '.$periodo['anomes']);
				//echo "vai clcular: ".$periodo['anomes']."<br>";
				$sql = "DELETE FROM gf_depto12meses WHERE anomes = ".$periodo['anomes'];
//echo "Deletando antigos: $sql <br>\n";
				query($sql);
				$this->getDadosPeriodo($periodo);
				log::gravaLog('12meses_controle', 'Fim calculo periodo: '.$periodo['anomes']);
			}
			log::gravaLog('12meses_controle', 'Recupera dados: '.$periodo['anomes']);
			$this->recuperaDados($periodo, $anomes,$super,$erc,$depto,$origem,$clientes, $clientesPrincipais, $schedule);
			log::gravaLog('12meses_controle', 'Fim recupera dados: '.$periodo['anomes']);
		}
		
	}
	
	
	private function verificaDadosGravados($anomes){
		$ret = true;
		$sql = "SELECT count(*) FROM gf_depto12meses WHERE anomes = '$anomes'";
//echo "SQL: $sql <br>\n";
		$rows = query($sql, false);
		if(isset($rows[0][0]) && $rows[0][0] > 0){
			$ret = false;
//echo "falso <br>\n";
		}
		
		return $ret;
	}
	
	function getDadosPeriodo($periodo){
		//$ret = array();
		$param = array();
		//$param['cliente'] = '11615,15921,10063';
		$dtDe 	= $periodo['ini'];
		$dtAte 	= $periodo['fim'];
		$anomes = $periodo['anomes'];
		
		$campos = array("to_char(DATA,'YYYYMM') MESANO",'CODCLI','CODEPTO','ORIGEM');
//echo "\n\n<br>periodo: $dtDe a $dtAte <br>\n";
		$vendas	= vendas1464Campo($campos, $dtDe, $dtAte, $param, false);
//print_r($vendas);
	
		if(count($vendas) > 0){
			foreach ($vendas as $anomes => $venda){
				foreach ($venda as $codcli => $ven){
					foreach ($ven as $codepto => $ve){
						foreach ($ve as $origem => $v){
							$erc = $this->getErcOriginal($codcli);
							$this->gravaDado($anomes, $erc['erc'], $codcli, $codepto, $origem, $v['quant'], $v['venda']);
						}
					}
				}
			}
		}
	}
	
	private function gravaDado($anomes, $erc, $codcli, $codepto, $origem, $quant, $venda){

		if($origem == 'OL'){
			$campoV= 'vendaOL';
			$campoQ = 'quantOL';
		}elseif($codepto == 1){
			$campoV = 'venda1';
			$campoQ = 'quant1';
		}else{
			$campoV = 'venda12';
			$campoQ = 'quant12';
		}
//echo "$anomes, $erc, $codcli, $codepto, $origem, $quant, $venda <br>\n";
		$sql = "SELECT * FROM gf_depto12meses WHERE anomes = $anomes AND erc = $erc AND codcli = $codcli";
		$rows = query($sql);

		if(count($rows) > 0){
			//Nao sei pq está somando.... retirado em 05/02/20 pois estava duplicando as informações
			$quant += $rows[0][$campoQ];
			$venda += $rows[0][$campoV];

			$sql = "UPDATE gf_depto12meses SET $campoQ = $quant, $campoV = $venda WHERE anomes = $anomes AND erc = $erc AND codcli = $codcli";
		}else{
			$sql = "INSERT INTO gf_depto12meses (anomes,erc,codcli,$campoQ,$campoV) VALUES ($anomes, $erc, $codcli, $quant, $venda)";
		}

//echo "SQL: $sql <br>\n";
		query($sql);
	}
	
	function recuperaDados($periodo, $anomesPar, $super='',$erc='',$depto='',$origem='',$clientes='', $clientesPrincipais = '', $schedule = false){
		//$ret = array();
		//$departamento = array(1 => 'Medicamento', 12 => 'Nao Medicamento');
		$anomes = $periodo['anomes'];
		//$descCurta = $periodo['descCurta'];
//echo "Parametros: $periodo, $anomesPar, $super,$erc,$depto,$origem,$clientes, $clientesPrincipais <br>\n";		
		$where = '';
		
		if($super != '' && $erc == ''){
			$where .= " AND erc in (".$this->getERCsuper($super).")";
		}
		if($erc != ''){
			$where .= " AND erc in ($erc)";
		}
		//if($depto != ''){
		//	$where .= " AND depto = $depto";
		//}
		//if($origem != '' && $origem != 'todos'){
		//	$where .= " AND origem = '$ori]'";
		//}
		
		//Verifica os clientes do cliente Principal
		if(!empty(trim($clientesPrincipais))){
			$sql = "SELECT CODCLI FROM PCCLIENT WHERE PCCLIENT.CODCLIPRINC IN ($clientesPrincipais)";
			$rows = query4($sql);
			$cliTemp = [];
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$cliTemp[] = $row['CODCLI'];
				}
			}
			$clientes = implode(',', $cliTemp);
		}
		
		if(!empty(trim($clientes))){
			$where .= " AND codcli in ($clientes)";
		}
		
		$sql = "SELECT * FROM gf_depto12meses WHERE anomes = $anomes $where";
		$rows = query($sql);
//print_r($rows);		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$codcli 	= $row['codcli'];
				$info = $this->getErcOriginal($codcli);
				
				if(!isset($this->_dados[$info['super']][$info['erc']][$codcli])){
					$this->_dados[$info['super']][$info['erc']][$codcli] = $this->geraMatriz($info, $codcli);
				}
				
				$this->_dados[$info['super']][$info['erc']][$codcli][$periodo['anomes'].'venda1'	] = $row['venda1'];
				$this->_dados[$info['super']][$info['erc']][$codcli][$periodo['anomes'].'venda12'	] = $row['venda12'];
				$this->_dados[$info['super']][$info['erc']][$codcli][$periodo['anomes'].'vendaOL'	] = $row['vendaOL'];
				if($schedule){
				    $this->_dados[$info['super']][$info['erc']][$codcli][$periodo['anomes'].'venda'		] = $row['venda1'] + $row['venda12'];
				}
				else{
				    $this->_dados[$info['super']][$info['erc']][$codcli][$periodo['anomes'].'venda'		] = $row['venda1'] + $row['venda12'] + $row['vendaOL'];
				}
			}
		}
//print_r($this->_dados);die();
	}
	
	private function getIMS($codcli){
		$ret = 0;
		$sql = "SELECT ims FROM gf_ims_12meses WHERE codcli = $codcli";
		$rows = query($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	private function geraMatriz($info, $codcli){
		$temp = array();
		$campos = $this->_relatorio->getCampos();
		foreach ($campos as $campo){
			$temp[$campo] = 0;
		}
		
		$temp['super']	 	= $info['super'];
		$temp['supervidor'] = $info['superNome'];
		
		$temp['rcaCad'] 	= $info['erc'];
		$temp['rcanomeCad'] = $info['ercNome'];
		
		$temp['cli'] 		= $codcli;
		$dadosCli = $this->getClienteNome($codcli);
		$temp['cliente'] 	= $dadosCli['nome'] ?? '';
		$temp['endereco'] 	= $dadosCli['endereco'] ?? '';
		$temp['bairro'	] 	= $dadosCli['bairro'] ?? '';
		$temp['cidade'	] 	= $dadosCli['cidade'] ?? '';
		$temp['uf'		] 	= $dadosCli['uf'] ?? '';
		$temp['exclusao']	= $dadosCli['exclusao'] ?? '';
		
		$temp['ims'] = $this->getIMS($codcli);
		
		return $temp;
	}
	
	private function getERCsuper($super){
		$ret = '';
		$sql = "select codusur from pcusuari where codsupervisor = $super";
		$rows = query4($sql);
		if(count($rows) > 0){
			$temp = '';
			foreach ($rows as $row){
				$temp[] = $row[0];
			}
			$ret = implode(',', $temp);
		}
		
		return $ret;
	}
	
	private function getErcOriginal($cliente){
		$ret = array();
		
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
				
				$this->_ercOriginal[$cliente] = $ret;
			}
			
		}else{
			$ret = $this->_ercOriginal[$cliente];
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
	
	function getClienteNome($codcli){
		$ret = array();
		if(isset($this->_clientes[$codcli])){
			$ret = $this->_clientes[$codcli];
		}else{
			$sql = "select CLIENTE, ENDERCOM, NUMEROCOM,  BAIRROCOM, MUNICCOM, ESTCOM, DTEXCLUSAO from pcclient where codcli = $codcli";
			$rows = query4($sql);
			if(count($rows) > 0){
				$ret['nome'] 	= $rows[0]['CLIENTE'];
				
				$ret['endereco']= $rows[0]['DTEXCLUSAO'] == '' ? $rows[0]['ENDERCOM'].', '.$rows[0]['NUMEROCOM'] : ' INATIVO';
				$ret['bairro'] 	= $rows[0]['DTEXCLUSAO'] == '' ? $rows[0]['BAIRROCOM'] : '';
				$ret['cidade'] 	= $rows[0]['DTEXCLUSAO'] == '' ? $rows[0]['MUNICCOM'] : '';
				$ret['uf'] 		= $rows[0]['DTEXCLUSAO'] == '' ? $rows[0]['ESTCOM'] : '';
				$ret['exclusao']= $rows[0]['DTEXCLUSAO'];
				
				$this->_clientes[$codcli] = $ret;
			}
		}
		return $ret;
	}
}