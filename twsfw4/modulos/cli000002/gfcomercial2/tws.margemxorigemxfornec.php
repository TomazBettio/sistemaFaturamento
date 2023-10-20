<?php
/* 
 * Data Criacao 24/04/2018
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: class.margemxorigemxfornec.inc.php
 * 
 * Descricao: Solicitado pelo Márcio
 * 			  Fornecedor x margem por origem
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

function ordenaVenda($a,$b) {
	return $a['total']<$b['total'];
}

class margemxorigemxfornec{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Dados
	var $_dados;
	
	//Indica se é teste
	var $_teste;
	
	//Origens
	var $_origem;
	var $_origemEtiqueta;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = true;
		
		$this->_origem = array('PDA' => 'fv','PE' => 'pe','TMKT' => 'tele','OL' => 'ol', 'WEB' => 'WEB');
		$this->_origemEtiqueta = array('PDA' => 'FV', 'PE' => 'PE', 'TMKT' => 'TELE', 'OL' => 'OL', 'WEB' => 'WEB');
		
		$this->_programa = 'margemxorigemxfornec';
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'fornec'		, 'etiqueta' => 'Cod.'					, 'tipo' => 'T', 'width' => 120, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor'			, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'total'		, 'etiqueta' => 'Total<br>Venda'		, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'totalM'		, 'etiqueta' => 'Total<br>Margem'		, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		
		foreach ($this->_origem as $i => $campo){
			$this->_relatorio->addColuna(array('campo' => $campo	, 'etiqueta' => $this->_origemEtiqueta[$i].'<br>Venda'	, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => $campo.'M', 'etiqueta' => $this->_origemEtiqueta[$i].'<br>Margem'	, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		}
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'	, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'	, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$dataIni = $filtro['DATAINI'];
		$dataFim = $filtro['DATAFIM'];
		
		$this->_relatorio->setTitulo("Venda e Margem por Origem e Fornecedor. Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim));
		if(!$this->_relatorio->getPrimeira()){
			$this->getVendas($dataIni,$dataFim);
		
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
		
		$ano = date('Y');
		$mes = date('m');
		
		if(!verificaExecucaoSchedule($this->_programa, $ano.$mes)){
			$mes--;
			if($mes == 0){
				$mes = 12;
				$ano--;
			}
			$mes = $mes < 10 ? '0'.$mes : $mes;
			$dataFim = $ano.$mes.date("t", mktime(0,0,0,$mes,'15',$ano));
			
			gravaExecucaoSchedule($this->_programa, $ano.$mes);
		}else{
			$dataFim = datas::getDataDias(-1);
		}
		
		$dataIni = $ano.$mes.'01';
		
		$titulo = "Venda e Margem por Origem e Fornecedor. Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim);
		
		$this->_relatorio->setTitulo($titulo);
		
		$this->getVendas($dataIni, $dataFim);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setTitulo($titulo);
		$this->_relatorio->setToExcel(true);
		$dados = array();
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $d){
				$dados[] = $d;
			}
		}
		$this->_relatorio->setDados($dados);

		if(!$this->_teste){
			$this->_relatorio->enviaEmail($emails,$titulo);
			log::gravaLog("margemxorigemxfornec", "Enviado email Geral: ".$emails);
		}else{
			$this->_relatorio->enviaEmail('thiel@thielws.com.br',$titulo.' Email Teste');
		}
	}

	function getMargem($dataIni, $dataFim){
		$sql = "
				SELECT
                    TWS_MARGEM.ORIGEM,
                    PCPRODUT.CODFORNEC,
                    CASE 
                        WHEN SUM(VLLIQUIDO) > 0
                        THEN ((1-(SUM(TWS_MARGEM.VLCUSTOREAL) + SUM(BCONSUMIDO) + SUM(BGERADO) - SUM(RESSARCST) - SUM(CREDITOICMS))/SUM(VLLIQUIDO))*100) 
                        ELSE 0
                    END LUCRO
                FROM 
                    TWS_MARGEM,
                    PCPRODUT
                WHERE 
                    DATA >= '$dataIni' AND DATA <= '$dataFim'
                    AND TWS_MARGEM.PRODUTO = PCPRODUT.CODPROD (+)
                GROUP BY
                    TWS_MARGEM.ORIGEM,
                    PCPRODUT.CODFORNEC
				";
		$rows = query4($sql);
//print_r($rows);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$origem = $row['ORIGEM'];
				$fornec = $row['CODFORNEC'];
				$margem = $row['LUCRO'];
				
				if($origem == 'TELE'){
					$origem = 'TMKT';
				}
				if($origem == 'FV'){
					$origem = 'PDA';
				}
				
				if(isset($this->_dados[$fornec])){
					$origem = $this->_origem[$origem];
					$this->_dados[$fornec][$origem.'M'] = $margem;
				}
			}
		}
		return;
	}
	
	function getMargemTotal($dataIni, $dataFim){
		$sql = "
			SELECT
				PCPRODUT.CODFORNEC,
				CASE
					WHEN SUM(VLLIQUIDO) > 0
					THEN ((1-(SUM(TWS_MARGEM.VLCUSTOREAL) + SUM(BCONSUMIDO) + SUM(BGERADO) - SUM(RESSARCST) - SUM(CREDITOICMS))/SUM(VLLIQUIDO))*100)
					ELSE 0
				END LUCRO
			FROM
				TWS_MARGEM,
				PCPRODUT
			WHERE
				DATA >= '$dataIni' AND DATA <= '$dataFim'
				AND TWS_MARGEM.PRODUTO = PCPRODUT.CODPROD (+)
			GROUP BY
			PCPRODUT.CODFORNEC
		";
		$rows = query4($sql);
		//print_r($rows);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$fornec = $row['CODFORNEC'];
				$margem = $row['LUCRO'];

				if(isset($this->_dados[$fornec])){
					$this->_dados[$fornec]['totalM'] = $margem;
				}
			}
		}
		
		return;
	}

	function matriz($fornec){
		if(!isset($this->_dados[$fornec])){
			$this->_dados[$fornec]= array();
			$campos = $this->_relatorio->getCampos();
			foreach ($campos as $campo){
				$this->_dados[$fornec][$campo] = 0;
			}
			$this->_dados[$fornec]['fornec'		] = $fornec;
			$this->_dados[$fornec]['fornecedor'	] = $this->getNomeFornec($fornec);
			if($this->_dados[$fornec]['fornecedor'	] == ''){
				unset($this->_dados[$fornec]['fornecedor']);
			}
		}
	}
	
	function getVendas($dataIni, $dataFim){
		$temp = array();
		$param = array();
		$campos = array('CODFORNEC', 'ORIGEM');
		//---------------------------------------------------------------------- Ultimo Dia
		$vendas = vendas1464Campo($campos,$dataIni, $dataFim, $param, false);
		foreach ($vendas as $fornec => $venda){
			foreach ($venda as $origem => $v){
				if(!isset($this->_dados[$fornec])){
					$this->matriz($fornec);
				}
				//Para eliminar fornecedores que não são de revenda
				if(isset($this->_dados[$fornec])){
					$origem = $this->_origem[$origem] ?? '';
					$this->_dados[$fornec][$origem] = $v['venda'];
					$this->_dados[$fornec]['total'] += $v['venda'];
					//$this->_dados[$fornec]['brinde'] = $v['bonific'];
				}
			}
		}
		
		//Calcula margem por origem/fornecedor
		$this->getMargem($dataIni, $dataFim);

		//Calcula margem total por fornecedor
		$this->getMargemTotal($dataIni, $dataFim);
		
		//Ordena pela venda total
		usort($this->_dados, 'ordenaVenda');
		return;	
	}
	
	
	private function getNomeFornec($fornec){
		$ret = '';
		$sql = "select fornecedor from pcfornec where codfornec = $fornec AND REVENDA = 'S'";
		$rows = query4($sql);
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
}