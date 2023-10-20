<?php
/*
 * Data Criacao: 10/11/2018
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Vendas dos operadores do televendas por dia
 *
 * Alterações:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class vendas_dia{

	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	//Nome do programa
	private $_programa;
	
	//Titulo do relatório
	private $_titulo;
	
	//Classe relatório
	private $_relatorio;
	
	//Dados
	private $_dados = array();
	
	//Indica se é teste
	private $_teste;
	
	//Envia teste operadores
	private $_testeOperadores;
	
	//Ramos que não devem ser considerados
	private $_foraRamo;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		$this->_testeOperadores = true;
		
		$this->_programa = 'vendas_dia';
		$this->_titulo = 'Vendas Diárias';
		
		$param = [];
		$param['programa']	= $this->_programa;
		$param['titulo']	= $this->_titulo;
		$this->_relatorio = new relatorio01($param);
		
		$this->_foraRamo = '5,6,715,18,21';
		
		$this->_relatorio->addColuna(array('campo' => 'operador'	, 'etiqueta' => 'Operador'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'atendente'	, 'etiqueta' => 'Atendente'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'esquerda'));
		
		if(false){
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'	, 'variavel' => 'DATAINI'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'	, 'variavel' => 'DATAFIM'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Tipo Venda'	, 'variavel' => 'TIPO'	,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'A=Ambos;V=Venda;B=Bonificacao'));
		}
		
	}
	
	private function montaColunas($dias){
		foreach ($dias as $key => $dia){
			$this->_relatorio->addColuna(array('campo' => 'venda'.$key	, 'etiqueta' => 'Venda<br>'.$dia	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		}
		$this->_relatorio->addColuna(array('campo' => 'total'	, 'etiqueta' => 'Venda<br>Total'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	}
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$diaIni		= $filtro['DATAINI'];
		$diaFim 	= $filtro['DATAFIM'];
		$tipo		= isset($filtro['TIPO']) ? $filtro['TIPO'] : '';
		
		$this->_relatorio->setTitulo($this->_titulo);
		if(!$this->_relatorio->getPrimeira() && $diaIni != '' && $diaFim != ''){
			$dias = $this->getDiasFaturados($diaIni, $diaFim);
			$this->montaColunas($dias);
			$this->getOperadores();
			$this->_relatorio->setTitulo($this->_titulo." Periodo: ".datas::dataS2D($diaIni,2).' a '.datas::dataS2D($diaFim,2));
			$this->getVendas($diaIni, $diaFim, $tipo, false);
			
			$dados = array();
			if(count($this->_dados) > 0){
				foreach ($this->_dados as $d){
					$dados[] = $d;
				}
			}
			
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	function schedule($param){
		$emails = str_replace(',', ';', $param);
		
		$dataIni = date('Ym').'01';
		$dataFim = date('Ymd');
		
		if(!verificaExecucaoSchedule($this->_programa,date('Ym'))){
			$mes = date('m');
			$ano = date('Y');
			
			$mes--;
			if($mes == 0){
				$mes = 1;
				$ano--;
			}
			
			$mes = $mes < 10 ? '0'.$mes : $mes;
			
			$dataIni = $ano.$mes.'01';
			$dataFim = $ano.$mes.date('t',mktime(0,0,0,$mes,15,$ano));
			
			gravaExecucaoSchedule($this->_programa,date('Ym'));
		}
		
		$this->getOperadores();
		$dias = $this->getDiasFaturados($dataIni, $dataFim);
		$this->montaColunas($dias);
		$this->getVendas($dataIni, $dataFim, false);
		
		$titulo = $this->_titulo." Periodo: ".datas::dataS2D($dataIni,2).' a '.datas::dataS2D($dataFim,2);
		$this->_relatorio->setTitulo($titulo);
		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true);
		
		//print_r($this->_dados);
		if(count($this->_dados) > 0){
			$dadosGeral = array();
			foreach ($this->_dados as $oper => $vendas){
				$dados = array();
				$dados[] = $vendas;
				$dadosGeral[] = $vendas;
				$this->_relatorio->setDados($dados);
				if(!$this->_teste){
					if($oper != 25 && isset($this->_operadores[$oper]['email']) && !empty(trim($this->_operadores[$oper]['email']))){
						$this->_relatorio->enviaEmail($this->_operadores[$oper]['email'],$titulo);
						log::gravaLog('vendas_dia', ' Email Operador: '.$oper.' - '.$this->_operadores[$oper]['email']);
					}
				}else{
					$operEmail = isset($this->_operadores[$oper]['email']) ? $this->_operadores[$oper]['email'] : '';
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$oper.' - '.$operEmail);
				}
			}
			
			$this->_relatorio->setDados($dadosGeral);
			if(!$this->_teste){
				$this->_relatorio->enviaEmail($emails,$titulo);
				log::gravaLog("vendas_dia", "Enviado email Geral: ".$emails);
			}else{
				$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
			}
			
			
		}
	}
	
	private function getDiasFaturados($diaIni, $diaFim){
		$ret = array();
		$sql = "SELECT to_char(DTSAIDA,'YYYYMMDD') FROM pcnfsaid WHERE DTSAIDA BETWEEN  TO_DATE('$diaIni', 'YYYYMMDD') AND TO_DATE('$diaFim', 'YYYYMMDD') GROUP BY DTSAIDA ORDER BY DTSAIDA";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row[0]] = datas::dataS2D($row[0], 2);
			}
		}
		
		return $ret;
	}
	
	private function getVendas($diaIni, $diaFim, $tipo = '', $pedidos = '', $trace = false){
		$param = array();
		if($tipo == 'V'){
			$param['atividadeFora'] = $this->_foraRamo;
		}
		
		$param['origem'] = 'T';
		$param['depto'] = '1,12,4';
		$param['bonificacao'] = false;

		$campos = array("CODEMITENTEPEDIDO","to_char(DATA,'YYYYMMDD') DIA",'CODOPER');
		$vendas	= vendas1464Campo($campos, $diaIni, $diaFim, $param, false);
//print_r($vendas);
		if(count($vendas) > 0){
			foreach ($vendas as $operador => $v1){
				foreach ($v1 as $dia => $v2){
					foreach ($v2 as $operacao => $v3){
						if(!isset($this->_dados[$operador])){
							$this->geraMatriz($operador);
						}
						if(!isset($this->_dados[$operador]['venda'.$dia])){
							$this->_dados[$operador]['venda'.$dia] = 0;
						}
						if($operacao == 'S' || $operacao == 'ED'){
							$this->_dados[$operador]['venda'.$dia] += $v3['venda'];
							$this->_dados[$operador]['total'] += $v3['venda'];
						}else{
							$this->_dados[$operador]['venda'.$dia] += $v3['bonific'];
							$this->_dados[$operador]['total'] += $v3['bonific'];
						}
					}
				}
			}
		}
//print_r($this->_dados);
	}
	
	private function geraMatriz($operador){
		if(!isset($this->_dados[$operador])){
			$campos = $this->_relatorio->getCampos();
			foreach ($campos as $campo){
				$this->_dados[$operador][$campo] = 0;
			}
			$this->_dados[$operador]['operador'	] = $operador;
			if(isset($this->_operadores[$operador]['nome'])){
				$this->_dados[$operador]['atendente'] = $this->_operadores[$operador]['nome'];
			}else{
				$this->_dados[$operador]['atendente'] = 'Operador NI '.$operador;
			}
		}
	}
	
	private function getOperadores(){
		$sql = "SELECT MATRICULA, NOME, EMAIL FROM PCEMPR  ORDER BY NOME"; //WHERE AREAATUACAO LIKE '%TELEVENDAS%' AND SITUACAO = 'A'ORDER BY NOME";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = array();
				$temp['matricula'] 	= $row['MATRICULA'];
				$temp['nome'] 		= $row['NOME'];
				$temp['email'] 		= $row['EMAIL'];
				$this->_oper[$row['MATRICULA']] = $row['MATRICULA'];
				$this->_operadores[$row['MATRICULA']] = $temp;
			}
		}
	}
}