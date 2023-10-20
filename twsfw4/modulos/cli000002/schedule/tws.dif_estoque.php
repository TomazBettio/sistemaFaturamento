<?php
/*
* Data Criação: 29/05/2015 - 10:31:23
* Autor: Thiel
*
* Arquivo: tws.dif_estoque.inc.php
* 
* 
* Alterções:
*            19/11/2018 - Emanuel - Migração para intranet2
*            30/01/2023 - Emanuel - Migração para intranet4
* 
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class dif_estoque{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';

	// Classe relatorio
	var $_relatorio;
	
	//Controle de limpeza da base
	var $_limpa;
	
	//Indica se é teste
	var $_teste;
	
	function __construct(){
		set_time_limit(0);
		$this->_limpa = true;
		
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		
		$this->_teste = false;

		$this->_programa = '000002.dif_estoque';
		$this->_relatorio = new relatorio01($this->_programa,"");
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
	
	function index(){
		
	}
	
	private function montarColunasRelatorio($schedule = false){
	    $this->_relatorio->addColuna(array('campo' => 'fil'		, 'etiqueta' => 'FILIAL'				, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'direita'));
	    $this->_relatorio->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'CODPROD'				, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'direita'));
	    $this->_relatorio->addColuna(array('campo' => 'prod'	, 'etiqueta' => 'DESCRICAO'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'ger'		, 'etiqueta' => 'EST.GERENCIAL'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'cont'	, 'etiqueta' => 'EST.CONTABIL'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'dif1'	, 'etiqueta' => 'DIF_EST.GER_X_EST.CONT', 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'dif1ant'	, 'etiqueta' => 'DIF_ANTERIOR'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'wms'		, 'etiqueta' => 'EST.WMS'				, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'dif2'	, 'etiqueta' => 'DIF_EST.GER_X_EST.WMS'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'dif2ant'	, 'etiqueta' => 'DIF_ANTERIOR'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'novo'	, 'etiqueta' => 'NOVA DIF'				, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
	}
	
	function schedule($param){
		$emails = str_replace(',', ';', $param);
		
		$this->montarColunasRelatorio(true);
		
		$data = date('d/m/Y');
		$dados = $this->getDados();
		
		$diaSemana 	= date("N");
		
		if($diaSemana < 6){
			if(count($dados) > 0){
				$this->_relatorio->setDados($dados);
				$this->_relatorio->setAuto(true);
				$this->_relatorio->setToExcel(true,"Diferenca_Estoque_".date('d.m.Y'));
				if(!$this->_teste){
				   $this->_relatorio->enviaEmail($emails,"Diferenca Estoque. Data: ".$data);
				   log::gravaLog('diferenca_estoque', "Relatorio enviado para: $emails");
				}
				else{
				    $this->_relatorio->enviaEmail('suporte@thielws.com.br',"Diferenca Estoque. Data: ".$data);
				}
			}else{
				$this->_relatorio->enviaEmail('suporte@thielws.com.br',"Diferenca Estoque. Data: ".$data." Sem itens!");
			}
		}
		
	}
	
	function getDados(){
		$ret = array();
		$retNovo = array();
		$retAnt = array();
		
		$ontem = datas::getDataDias(-1);
		$estAnt = $this->getEstoqueAnterior($ontem);
		
		$sql = "SELECT
                    PCEST.CODPROD,
                    PCPRODUT.DESCRICAO,
                    PCEST.QTESTGER AS ESTGERENCIAL,
                    PCEST.QTEST    AS ESTCONTABIL,
                    PCEST.QTESTGER - PCEST.QTEST AS DIF_ESTGER_X_ESTCONT,
                    DECODE(PCEST.CODFILIAL,
                        1, SUM(PCESTENDERECO.QT),
                        0) AS ESTWMS,
                    DECODE(PCEST.CODFILIAL,
                        1, PCEST.QTESTGER - SUM(PCESTENDERECO.QT),
                        0) AS DIF_ESTGER_X_ESTWMS,
                    PCEST.CODFILIAL
                FROM PCEST,PCPRODUT,PCESTENDERECO
                  WHERE PCEST.CODPROD=PCPRODUT.CODPROD (+)
                  AND   PCEST.CODPROD=PCESTENDERECO.CODPROD

                
                  --AND   PCEST.CODFILIAL='1'
           --       HAVING PCEST.QTESTGER <> PCEST.QTEST  OR  PCEST.QTESTGER <> SUM(PCESTENDERECO.QT)
                GROUP BY PCEST.CODFILIAL,PCEST.CODPROD,PCPRODUT.DESCRICAO,PCEST.QTESTGER,PCEST.QTEST
                ORDER BY CODPROD";
		$rows = query4($sql);
		if(count($rows) >0){
			foreach ($rows as $row){
				$dif1 = isset($estAnt[$row[7]][$row[0]]['dif1']) ? $estAnt[$row[7]][$row[0]]['dif1'] : 0;
				$dif2 = isset($estAnt[$row[7]][$row[0]]['dif2']) ? $estAnt[$row[7]][$row[0]]['dif2'] : 0;
				if($row[4] != 0 || $row[6] != 0 || $row[4] != $dif1 || $row[6] != $dif2){
					$temp = array();
					$temp['fil'] = $row[7];
					$temp['cod'] = $row[0];
					$temp['prod'] = $row[1];
					$temp['ger'] = $row[2];
					$temp['cont'] = $row[3];
					$temp['dif1'] = $row[4];
					$temp['dif1ant'] = $dif1;
					$temp['wms'] = $row[5];
					$temp['dif2'] = $row[6];
					$temp['dif2ant'] = $dif2;
					$temp['novo'] = $row[4] != $dif1 || $row[6] != $dif2 ? '*' : '';
					
					if($temp['novo'] == '*'){
						$retNovo[] = $temp;
					}else{
						$retAnt[] = $temp;
					}
					
					$this->gravaDif($row[7], $row[0], $row[4], $row[6]);
				}
			}
//print_r($retNovo);
//print_r($retAnt);			
			if(count($retNovo) > 0){
				foreach ($retNovo as $novo){
					$ret[] = $novo;
				}
			}
			if(count($retAnt) > 0){
				foreach ($retAnt as $ant){
					$ret[] = $ant;
				}
			}
		}
//print_r($ret);
		return $ret;
	}
	
	function gravaDif($filial, $produto, $dif1, $dif2){
		$dia = date('Ymd');
		
		//Limpa se já rodou anteriormente
		if($this->_limpa){
			$sql = "DELETE FROM gf_dif_estoque WHERE dia = '$dia'";
			query($sql);
			$this->_limpa = false;
		}
		
		if($dif1 == ''){
			$dif1 = 0;
		}
			if($dif2 == ''){
			$dif2 = 0;
		}
		$sql = "INSERT INTO gf_dif_estoque (dia,filial,produto,dif1,dif2) VALUES ('$dia', $filial, $produto, $dif1, $dif2)";
//echo "$sql <br>\n";
		query($sql);
	}
	
	function getEstoqueAnterior($ontem){
		$ret = array();

		$dia = date('Ymd');
		$sql = "DELETE FROM gf_dif_estoque WHERE dia = '$dia'";
		query($sql);
		
		$sql = "SELECT * FROM gf_dif_estoque WHERE dia = '$ontem'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row['filial']][$row['produto']]['dif1'] = $row['dif1'];
				$ret[$row['filial']][$row['produto']]['dif2'] = $row['dif2'];
			}
		}
		return $ret;
	}
}