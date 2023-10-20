<?php
/* 
 * Data Criacao 6 de jun de 2017
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: class.ora_margemxorigem.inc.php
 * 
 * Descricao: 
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class margemxorigem{
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
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = 'ora_margemxorigem';
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'dia'			, 'etiqueta' => 'Data'					, 'tipo' => 'T', 'width' => 120, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'origem'		, 'etiqueta' => 'Origem'				, 'tipo' => 'T', 'width' => 120, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'venda'		, 'etiqueta' => 'Venda'					, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'margem'		, 'etiqueta' => 'Margem'				, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'margem2'		, 'etiqueta' => 'Margem 2'				, 'tipo' => 'V', 'width' => 150, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'brinde'		, 'etiqueta' => 'Brinde<br>Bonificacao'	, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$diaIni = $filtro['DATAINI'];
		$diaFim = $filtro['DATAFIM'];
		
		$this->_relatorio->setTitulo("Venda e Margem por Origem. Periodo: ".datas::dataS2D($diaIni)." a ".datas::dataS2D($diaFim));
		if(!$this->_relatorio->getPrimeira()){
			$this->getVendas($diaIni,$diaFim);
	
			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}

	function schedule($param){
		$emails = str_replace(',', ';', $param);
		
		$data = getDatasRelAut(1);
		$dataIni = $data[0];
		$dataFim = $data[1];

		$titulo = "Venda e Margem por Origem. Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim);
		
		$this->_relatorio->setTitulo($titulo);
		
		$this->getVendas($dataIni, $dataFim);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setTitulo($titulo);
		$this->_relatorio->setToExcel(true);
		$this->_relatorio->setDados($this->_dados);

		if(!$this->_teste){
			$this->_relatorio->enviaEmail($emails,$titulo);
			log::gravaLog("ora_margemxorigem", "Enviado email Geral: ".$emails);
		}else{
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
		}
	}

	function getMargem($dataIni, $dataFim){
		$ret = array();
		$sql = "
				select
				    tws_margem.origem,
				    ((1-(SUM(tws_margem.VLCUSTOREAL) + SUM(BCONSUMIDO) + SUM(BGERADO) - SUM(RESSARCST) - SUM(CREDITOICMS))/SUM(VLLIQUIDO))*100) LUCRO
				from 
				    tws_margem
				where 
				    data >= '$dataIni' AND data <= '$dataFim'
				group by
				    tws_margem.origem
				";
//echo "SQL: $sql <br>\n";
		$rows = query4($sql);
//print_r($rows);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$origem = $row[0];
				$valor = $row[1];
				if($origem == 'TELE'){
					$origem = 'TMKT';
				}
				if($origem == 'FV'){
					$origem = 'PDA';
				}
				$ret[$origem] = $valor;
			}
		}
		
		return $ret;
	}
	
	function getMargem2($dataIni, $dataFim){
		$ret = array();
		$sql = "
		select
		tws_margem.origem,
		((1-(SUM(tws_margem.CUSTOMEDREAL * tws_margem.QUANT) + SUM(BCONSUMIDO) + SUM(BGERADO) - SUM(RESSARCST) - SUM(CREDITOICMS))/SUM(VLLIQUIDO))*100) LUCRO
		from
		tws_margem
		where
		data >= '$dataIni' AND data <= '$dataFim'
		group by
		tws_margem.origem
		";
		$rows = query4($sql);
		//print_r($rows);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$origem = $row[0];
				$valor = $row[1];
				if($origem == 'TELE'){
					$origem = 'TMKT';
				}
				if($origem == 'FV'){
					$origem = 'PDA';
				}
				$ret[$origem] = $valor;
			}
		}
		
		return $ret;
	}
	function matriz($dia,$temp){
//print_r($temp);
		if(count($temp) > 0){
			foreach ($temp as $origem => $valores){
//echo "$dia - $origem \n";print_r($valores);
				if($origem != ''){
					$venda['dia'	] = $dia;
					$venda['origem'	] = $origem;
					$venda['venda'	] = isset($valores['venda']) ? $valores['venda'] : 0;
					$venda['margem'	] = isset($valores['margem']) ? $valores['margem'] : 0;
					//$venda['margem2'] = isset($valores['margem2']) ? $valores['margem2'] : 0;
					$venda['brinde'	] = isset($valores['brinde']) ? $valores['brinde'] : 0;
					
					$this->_dados[] = $venda;
				}
			}
		}
	}
	
	function getVendas($diaIni, $diaFim){
		$temp = array();
		$param = array();
		$campos = 'ORIGEM';
		//---------------------------------------------------------------------- Ultimo Dia
		$vendas = vendas1464Campo($campos,$diaFim, $diaFim, $param, false);
		foreach ($vendas as $origem => $venda){
			$temp[$origem]['venda'] = $venda['venda'];
			$temp[$origem]['brinde'] = $venda['bonific'];
		}
		
		$margem = $this->getMargem($diaFim, $diaFim);
		if(count($margem) > 0){
//print_r($margem);
			foreach ($margem as $origem => $valor){
				$temp[$origem]['margem'] = $valor;
			}
		}
		
/*/
		$margem2 = $this->getMargem2($diaFim, $diaFim);
		if(count($margem2) > 0){
			//print_r($margem);
			foreach ($margem2 as $origem => $valor){
				$temp[$origem]['margem2'] = $valor;
			}
		}
/*/		
		$this->matriz(datas::dataS2D($diaFim),$temp);

		$temp = array();
		//---------------------------------------------------------------------- Acumulado
		$vendas = vendas1464Campo($campos,$diaIni, $diaFim, $param, false);
		foreach ($vendas as $origem => $venda){
			$temp[$origem]['venda'] = $venda['venda'];
			$temp[$origem]['brinde'] = $venda['bonific'];
		}
		
		$margem = $this->getMargem($diaIni, $diaFim);
		if(count($margem) > 0){
			foreach ($margem as $origem => $valor){
				$temp[$origem]['margem'] = $valor;
			}
		}
		
/*/
		$margem2 = $this->getMargem2($diaIni, $diaFim);
		if(count($margem2) > 0){
			foreach ($margem2 as $origem => $valor){
				$temp[$origem]['margem2'] = $valor;
			}
		}
/*/
		$this->matriz('Acumulado',$temp);
		
		return;	
	}
	
}