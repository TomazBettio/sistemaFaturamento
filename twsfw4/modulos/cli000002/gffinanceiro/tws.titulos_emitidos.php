<?php
/*
 * Data Criacao: 06/12/2017
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Relatório de titulos emitidos no período
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class titulos_emitidos{
	var $_relatorio;
	
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	// Dados
	var $_dados;
	
	// Chave de Teste
	var $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = 'titulosEmitidos';
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'cli'		, 'etiqueta' => 'Codigo'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'				, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cnpj'	, 'etiqueta' => 'CNPJ'					, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'titulo'	, 'etiqueta' => 'Titulo'				, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'parcela'	, 'etiqueta' => 'Prestacao'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
//		$this->_relatorio->addColuna(array('campo' => 'prazo'	, 'etiqueta' => 'Prazo Faturamento'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'emissao'	, 'etiqueta' => 'Emissao'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'original', 'etiqueta' => 'Vencimento<br>Original', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'real'	, 'etiqueta' => 'Vencimento<br>Atual'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'valor'	, 'etiqueta' => 'Valor<br>Original'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'saldo'	, 'etiqueta' => 'Saldo<br>Titulo'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'desconto', 'etiqueta' => 'Desconto'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vencDesc', 'etiqueta' => 'Vencimento<br>Desconto', 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		
		if(false){
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'			, 'variavel' => 'DATAINI'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'			, 'variavel' => 'DATAFIM'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Cliente'			, 'variavel' => 'CLIENTE'	,'tipo' => 'T', 'tamanho' => '60','decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		}
	}
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$dataIni = isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dataFim = isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		$clientes = isset($filtro['CLIENTE']) ? str_replace(';', ',', $filtro['CLIENTE']) : '';
		
		$this->_relatorio->setTitulo("Titulos Emitidos");
		
		if(!$this->_relatorio->getPrimeira() && !empty($clientes) && !empty($dataIni) && !empty($dataFim)){
			$this->_relatorio->setTitulo("Titulos Emitidos. Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim));
			$razaoSocial = "<h3>Gauchafarma Medicamentos Ltda<br>CNPJ 89.735.070/0001-00</h3>";
			$this->_relatorio->setFooter($razaoSocial);
			$this->getDados($dataIni, $dataFim, $clientes);
			
			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	function schedule($param){
		$par = explode('|', $param);
		$cliente = $par[0];
		$email = $par[1];
		
		$dataIni = datas::getDataDias(-7);
		$dataFim = datas::getDataDias(-1);
		
		$titulo = "Titulos Emitidos Gauchafarma. Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim);
		$this->_relatorio->setTitulo($titulo);
		$razaoSocial = "<h3>Gauchafarma Medicamentos Ltda<br>CNPJ 89.735.070/0001-00</h3>";
		$this->_relatorio->setFooter($razaoSocial);
		$this->getDados($dataIni, $dataFim, $cliente);
		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setDados($this->_dados);
		$this->_relatorio->setToExcel(true);
		
		if(!$this->_teste){
		    $this->_relatorio->enviaEmail($email,$titulo,'',$razaoSocial);
		}	
		else{
		    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo,'',$razaoSocial);
		}
	}
	
	private function getDados($dataIni, $dataFim, $clientes){
		$this->_dados = array();
		$sql = "
				SELECT  
				        PCPREST.CODCLI,
				        PCCLIENT.CLIENTE,
				        PCCLIENT.CGCENT CNPJ,
				        TO_CHAR(PCPREST.DUPLIC) TITULO,
				        PCPREST.PREST,
				
				        (SELECT (SELECT DESCRICAO FROM PCPLPAG WHERE CODPLPAG = PCNFSAID.CODPLPAG) FROM PCNFSAID WHERE PCNFSAID.NUMTRANSVENDA = PCPREST.NUMTRANSVENDA) FATURAMENTO,
				
				        PCPREST.DTEMISSAO EMISSAO,
				        PCPREST.DTVENCORIG VENCORIGINAL,
				        PCPREST.DTVENC VENCIMENTO,
				
				        PCPREST.VALORORIG VALOR,
				
				        (PCPREST.VALORORIG - NVL(PCPREST.vpago,0)) SALDO,
				    
				
				        PCPREST.VALORDESC DESCONTO,
				        PCPREST.DTDESC DTDESCONTO
				FROM PCPREST,
				     PCCLIENT
				WHERE PCPREST.DTPAG IS NULL
				    AND PCPREST.CODCOB IN ('C','001','041','DEP','BK','REEM','JURI')
				    AND PCPREST.CODCLI = PCCLIENT.CODCLI (+)
				    AND PCCLIENT.CODCLIPRINC IN ($clientes)
				    AND PCPREST.DTEMISSAO BETWEEN TO_DATE('$dataIni','YYYYMMDD') AND TO_DATE('$dataFim','YYYYMMDD')
				ORDER BY 
				    PCCLIENT.CLIENTE,
				    PCPREST.DTVENC
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = array();
				
				$temp['cli'		] = $row['CODCLI'];
				$temp['cliente'	] = $row['CLIENTE'];
				$temp['cnpj'	] = $row['CNPJ'];
				$temp['titulo'	] = $row['TITULO'];
				$temp['parcela'	] = $row['PREST'];
//				$temp['prazo'	] = $row['FATURAMENTO'];
				$temp['emissao'	] = datas::dataMS2D($row['EMISSAO']);
				$temp['original'] = datas::dataMS2D($row['VENCORIGINAL']);
				$temp['real'	] = datas::dataMS2D($row['VENCIMENTO']);
				$temp['valor'	] = $row['VALOR'];
				$temp['saldo'	] = $row['SALDO'];
				$temp['desconto'] = $row['DESCONTO'];
				$temp['vencDesc'] = datas::dataMS2D($row['DTDESCONTO']);
				
				$this->_dados[] = $temp;
			}
		}
	}
}