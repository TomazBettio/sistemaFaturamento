<?php
/*
* Data Criação: 27/04/2018
* Autor: Thiel
*
* Descrição: Solicitado pelo Márcio para acompanhar as entradas
* 
* Alteracoes:
* 				08/12/22 - Incluir o IPI no valor do produto - Gustavo
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class entradas_simples{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Dados
	var $_dados = [];
	
	//Indica se é teste
	var $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		$this->_programa = get_class($this);
		
		$param = [];
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$this->_relatorio->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'nota'		, 'etiqueta' => 'Nota'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		
		//$this->_relatorio->addColuna(array('campo' => 'cfop'		, 'etiqueta' => 'CFOP'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'cod'			, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'produto'		, 'etiqueta' => 'Produto'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'qt'			, 'etiqueta' => 'Quant.'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'preco'		, 'etiqueta' => 'Preco'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'C'));
		
		$this->_relatorio->addColuna(array('campo' => 'Mideal'		, 'etiqueta' => 'Margem<br>Ideal'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'Mpreco'		, 'etiqueta' => 'Margem<br>Precificacao', 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'difMargem'	, 'etiqueta' => 'Margem<br>Diferenca'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'Svenda'		, 'etiqueta' => 'S.Venda'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'futuro'		, 'etiqueta' => 'Preco<br>Futuro'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'tabela'		, 'etiqueta' => 'Preco<br>Tabela 1'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		//$this->_relatorio->addColuna(array('campo' => 'contAnt'		, 'etiqueta' => 'Contabil<br>Anterior'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'contAtu'		, 'etiqueta' => 'Contabil<br>Atual'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'Vcont'		, 'etiqueta' => '%<br>Reajuste'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));

		//$this->_relatorio->addColuna(array('campo' => 'realAnt'		, 'etiqueta' => 'Real<br>Anterior'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'realAtu'		, 'etiqueta' => 'Real<br>Atual'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'Vreal'		, 'etiqueta' => '%<br>Reajuste'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'entAnt'		, 'etiqueta' => 'Custo Ult.Ent.<br>Anterior'	, 'tipo' => 'V4', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'entAtu'		, 'etiqueta' => 'Custo Ult.Ent.<br>Atual'		, 'tipo' => 'V4', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'Vent'		, 'etiqueta' => '%<br>Reajuste'					, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		if(false){
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'	, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'	, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Mostra Devoluções'	, 'variavel' => 'DDEVOLUCOES', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não'));
		}
	}			
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$dataIni = $filtro['DATAINI'];
		$dataFim = $filtro['DATAFIM'];
		$devolucoes = $filtro['DDEVOLUCOES'];
		
		$this->_relatorio->setTitulo("Entradas Simplificadas.");
		if(!$this->_relatorio->getPrimeira()){
			$this->_relatorio->setTitulo("Entradas Simplificadas. Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim));
			$this->getEntradas($dataIni,$dataFim, $devolucoes);
			
			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
		}
		$ret .= $this->_relatorio;
		
		return $ret;
	}

	function schedule($param){
		$emails = str_replace(',', ';', $param);
		$semana = date('N');
		if($semana == 1){
			$data = datas::getDataDias(-3);
		}else{
			$data = datas::getDataDias(-1);
		}
//$data = '20230201';
		$this->_relatorio->setTitulo("Entradas Simplificadas. Dia: ".datas::dataS2D($data));
		$this->getEntradas($data,$data);
//print_r($this->_dados);
		$dados = array();
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $dado){
				/*
				 * Retirada a restrição de margem ou custo em 14/01/19 por solicitação do Márcio
				 * - Deve mostrar todas as entradas -
				 */
				//if($dado['difMargem'] < 0 || $dado['Vent'] > 0 ){
					$dados[] = $dado;
				//}
			}
		}
		
//print_r($dados);		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true, 'entradas_simplificadas_'.datas::dataS2D($data,2,'.'));
		$this->_relatorio->setTextoSemDados('Não foram realizadas entradas no dia!');
		
		$this->_relatorio->setDados($dados);
		
		if(!$this->_teste){
			$this->_relatorio->enviaEmail($emails);
			log::gravaLog('entradas_dia_simplificado', 'Enviado email: '.$emails);
		}else{
			$this->_relatorio->enviaEmail('suporte@thielws.com.br');
			log::gravaLog('entradas_dia_simplificado', 'Enviado email: suporte@thielws.com.br');
		}
	}

	function getEntradas($dataIni,$dataFim, $devolucoes = 'N'){
		$ret = array();
		$where = '';
		if($devolucoes == 'N'){
			$where = "AND PCMOV.CODOPER NOT IN ('ER','ED') 	AND PCNFENT.CODFORNEC NOT IN (885,17965,17966,17967,17968,14915,1178,18603) AND PCNFENT.REVENDA = 'S'";
		}
		$sql = "
				SELECT 
				    PCMOV.CODOPER,
				    PCNFENT.CODFORNEC,
				    PCNFENT.FORNECEDOR,
				    PCNFENT.NUMNOTA,
				    PCMOV.CODPROD,
				    PCPRODUT.DESCRICAO,
				    PCMOV.QT,
				    PCMOV.PUNIT,
				    PCMOV.CUSTOCONT CONT_ATUAL,
				    PCMOV.CUSTOCONTANT CONT_ANTER,
				    PCMOV.CODFISCAL CFOP,
				    PCNFENT.DTEMISSAO,
				    PCNFENT.DTENT,
					PCMOV.CUSTOREAL,
					PCMOV.CUSTOREALANT,
					PCMOV.CUSTOULTENT,
					PCMOV.CUSTOULTENTANT
				FROM 
					PCNFENT, 
     				PCMOV,
     				PCPRODUT
				WHERE 
					PCNFENT.DTENT >= TO_DATE('$dataIni', 'YYYYMMDD')
					AND PCNFENT.DTENT <= TO_DATE('$dataFim', 'YYYYMMDD')
					AND PCMOV.CODOPER NOT IN ('ET','EB')
    				$where
    				--AND PCNFENT.CODFISCAL NOT IN (212,112)
				    AND PCNFENT.ESPECIE = 'NF'
				    
				    AND PCNFENT.NUMTRANSENT = PCMOV.NUMTRANSENT
				    AND PCMOV.CODPROD = PCPRODUT.CODPROD (+)
				ORDER BY 
					PCNFENT.FORNECEDOR
		";
		$rows = query4($sql);
//echo "$sql <br>\n";
//print_r($rows);
		if(count($rows) > 0){
			foreach($rows as $row){
				$temp = array();
				$tipo = '1 – Entrada Normal';
				if($row['CODOPER'] == 'EB'){
					$tipo = '5- Bonificação';
				}elseif($row['CODOPER'] == 'ER'){
					$tipo = 'ER';
				}elseif($row['CODOPER'] == 'ED'){
					$tipo = 'Devolução';
				}
				
				$info = $this->getValoresMargem($row['CODPROD']);
								
				$temp['tipo'	] 	= $tipo;
				$temp['fornec'	] 	= $row['CODFORNEC'];
				$temp['fornecedor'] = $row['FORNECEDOR'];
				$temp['nota'	] 	= $row['NUMNOTA'];
				$temp['cfop'	] 	= $row['CFOP'];
				$temp['emissao'	] 	= datas::dataMS2D($row['DTEMISSAO']);
				$temp['entrada'	] 	= datas::dataMS2D($row['DTENT']);
				$temp['cod'		] 	= $row['CODPROD'];
				$temp['produto'	] 	= $row['DESCRICAO'];
				$temp['qt'		] 	= $row['QT'];
				$temp['preco'	] 	= $row['PUNIT'];
				$temp['Mideal'	] 	= $info['margem'];
				$temp['Mpreco'	] 	= $info['margemP'];
				$temp['Svenda'	] 	= $info['sugestao'];
				$temp['contAnt'	] 	= $row['CONT_ANTER'];
				$temp['contAtu'	] 	= $row['CONT_ATUAL'];
				$temp['futuro'	] 	= $info['futuro'];
				$temp['tabela'	] 	= $info['preco'];
				//$temp['Vcont'	]	= round((1 -($row['CONT_ATUAL']/$row['CONT_ANTER'])) * 100,2);
				if($row['CONT_ATUAL'] != 0){
					$temp['Vcont'	]	= round((1 -($row['CONT_ANTER']/$row['CONT_ATUAL'])) * 100,2);
				}else{
					$temp['Vcont'	]	= 0;
				}
				
				$temp['realAnt'	] 	= $row['CUSTOREALANT'];
				$temp['realAtu'	] 	= $row['CUSTOREAL'];
				//$temp['Vreal'	]	= round((1 -($row['CUSTOREAL']/$row['CUSTOREALANT'])) * 100,2);
				if($row['CUSTOREAL'] != 0){
					$temp['Vreal'	]	= round((1 -($row['CUSTOREALANT']/$row['CUSTOREAL'])) * 100,2);
				}else{
					$temp['Vreal'	]	= 0;
				}
				
				$temp['entAnt'	] 	= $row['CUSTOULTENTANT'];
				$temp['entAtu'	] 	= $row['CUSTOULTENT'];
				//$temp['Vent'	]	= round((1 -($row['CUSTOULTENT']/$row['CUSTOULTENTANT'])) * 100,2);
				if($row['CUSTOULTENT'] > 0){
					$temp['Vent'	]	= round((1 -($row['CUSTOULTENTANT']/$row['CUSTOULTENT'])) * 100,2);
				}else{
					$temp['Vent'	]	= 0;
				}
				
				$temp['difMargem']	= round($temp['Mpreco'] - $temp['Mideal'],2);
				
				//Solicitação do Márcio, somente com variação maior que 0,1%
				if($temp['Vent'] >= 0.1 && $temp['entAnt'] > 0){
					$this->_dados[] = $temp;
				}
			}
		}
		
		return $ret;	
	}
	
	
	
	private function getValoresMargem($produto){
		$ret = array();
		$sql = "
				SELECT
				    PCTRIBUT.CODICMTAB,
				    PCPRODUT.PCOMREP1,
				    PCREGIAO.PERFRETETERCEIROS,
				    PCEST.CUSTOREAL, 
				    PCEST.CUSTOULTENT,
					PCTABPR.MARGEM,
					PCTABPR.PVENDA,
					PCTABPR.PTABELA
				FROM
				    PCTRIBUT,
				    PCTABPR,
				    PCPRODUT,
				    PCREGIAO,
				    PCEST
				WHERE
				    PCTRIBUT.CODST = PCTABPR.CODST
				    AND PCREGIAO.NUMREGIAO(+) = PCTABPR.NUMREGIAO
				    AND PCEST.CODFILIAL = 1 
				    AND PCEST.CODPROD = PCTABPR.CODPROD
				    AND PCPRODUT.CODPROD = PCTABPR.CODPROD (+)
				    AND PCTABPR.NUMREGIAO = 1
				    AND PCTABPR.CODPROD = $produto 
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			$ret['icms'] 	= $rows[0]['CODICMTAB'];
			$ret['comis'] 	= $rows[0]['PCOMREP1'];
			$ret['frete'] 	= $rows[0]['PERFRETETERCEIROS'];
			$ret['real'] 	= $rows[0]['CUSTOREAL'];
			$ret['ultent'] 	= $rows[0]['CUSTOULTENT'];
			$ret['margem'] 	= $rows[0]['MARGEM'];
			$ret['preco'] 	= $rows[0]['PVENDA'];
			$ret['futuro'] 	= $rows[0]['PTABELA'];
			
			$ret['sugestao'] = $this->calculaValores('S', $ret);
			$ret['margemP'] = $this->calculaValores('M', $ret);
		}
//echo "$produto<br>\n";print_r($ret);		
		return $ret;
	}
	
	private function calculaValores($tipo, $info){
		$ret = 0;
		$custo = $info['ultent'] > $info['real'] ? $info['ultent'] : $info['real'];
		$icms = $info['icms']/100;
		$comis = $info['comis']/100;
		$frete = $info['frete']/100;
		$margem = $info['margem']/100;
		$preco = $info['preco'];
//echo "Tipo: $tipo <br>\n";
		if($tipo == 'S'){
			$ret = $custo / (1 - ($icms + $comis + $frete + $frete));
//echo "$custo / (1 - ($icms + $comis + $frete + $frete)) <br>\n";
		}elseif($tipo == 'M'){
			if($preco <> 0){
				$ret = (1 - (($custo + ($preco * $icms) + ($preco * $comis) + ($preco * $frete))/$preco)) * 100;
			}else{
				$ret = 0;
			}
//echo "(($custo + ($preco * $icms) + ($preco * $comis) + ($preco * $frete))/$preco) * 100<br>\n";
		}
		
		return $ret;
	}
}

