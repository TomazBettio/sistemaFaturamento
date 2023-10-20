<?php
/*
 * Data Criacao: 02/12/2018
 * Autor: Alexandre Thiel
 *
 * Descricao: Faz cálculo da margem de vendas
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class margem{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	// Classe relatorio
	var $_relatorio;
	
	// Dados
	var $_dados;
	
	// Valor de credito de ICMS (interestadual)
	var $_creditoICMS;
	
	//Custos
	var $_custos;
	
	function __construct(){
		set_time_limit(0);
		$this->_programa = 'calculo_margem';
	}
	
	function index(){
		$dataIni = '20210301';
		$dataFim = '20210303';
		
		$dias = datas::getEspacoDias($dataIni,$dataFim);
		log::gravaLog("calcula_margem", "Datas: $dataIni | $dataFim - Quant: $dias");
		for($n=0;$n < $dias;$n++){
			$data = datas::getDataDias($n, $dataIni);
			
			log::gravaLog("calcula_margem", "Inicio: $data");
			echo "Inicio: $data <br>\n";
			$this->limpaMovimento($data, $data);
			
			log::gravaLog("calcula_margem", "getVendas: $data");
			echo "getVendas: $data <br>\n";
			$this->getVendas($data, $data);
			
			log::gravaLog("calcula_margem", "Bonus: $data");
			echo "Bonus: $data <br>\n";
			$this->ajustaBonus($data, $data);
			
			log::gravaLog("calcula_margem", "Ressarcimento: $data");
			echo "Ressarcimento: $data <br>\n";
			$this->ressarcimentoST($data, $data);
			
			log::gravaLog("calcula_margem", "ICMS Cesta Basica: $data");
			echo "ICMS Cesta Básica: $data <br>\n";
			$this->icmsCestaBasica($data, $data,'38.33');
			
			log::gravaLog("calcula_margem", "Lucro: $data");
			echo "Lucros: $data <br>\n";
			$this->ajustaLucro($data, $data);
			
			log::gravaLog("calcula_margem", "Atualizando Oracle: $data");
			echo "Atualiza Oracle: $data <br>\n";
			$this->atualizaOracle($data, $data);
			
			//$this->limpaMovimento($dataIni, $dataFim);
			
			log::gravaLog("calcula_margem", "Execute Fim: $data");
		}
		
	}
	
	function schedule($parametros){
		$dataIni = datas::getDataDias(-15);
		$dataFim = datas::getDataDias(-1);;
		
		$dias = datas::getEspacoDias($dataIni,$dataFim);
		log::gravaLog("calcula_margem", "Datas: $dataIni | $dataFim - Quant: $dias");
		for($n=0;$n < $dias;$n++){
			$data = datas::getDataDias($n, $dataIni);
			
			log::gravaLog("calcula_margem", "Inicio: $data");
			echo "Inicio: $data <br>\n";
			$this->limpaMovimento($data, $data);
			
			log::gravaLog("calcula_margem", "getVendas: $data");
			echo "getVendas: $data <br>\n";
			$this->getVendas($data, $data);
			
			log::gravaLog("calcula_margem", "Bonus: $data");
			echo "Bonus: $data <br>\n";
			$this->ajustaBonus($data, $data);
			
			log::gravaLog("calcula_margem", "Ressarcimento: $data");
			echo "Ressarcimento: $data <br>\n";
			$this->ressarcimentoST($data, $data);
			
			log::gravaLog("calcula_margem", "ICMS Cesta Basica: $data");
			echo "ICMS Cesta Básica: $data <br>\n";
			$this->icmsCestaBasica($data, $data,'38.33');
			
			log::gravaLog("calcula_margem", "Lucro: $data");
			echo "Lucros: $data <br>\n";
			$this->ajustaLucro($data, $data);
			
			log::gravaLog("calcula_margem", "Atualizando Oracle: $data");
			echo "Atualiza Oracle: $data <br>\n";
			$this->atualizaOracle($data, $data);
			
			//$this->limpaMovimento($dataIni, $dataFim);
			
			log::gravaLog("calcula_margem", "Execute Fim: $data");
		}
	}
	
	
	private function limpaMovimento($dataIni, $dataFim){
		$sql = "DELETE FROM gf_margem WHERE data >= '$dataIni' AND data <= '$dataFim'";
		log::gravaLog("calcula_margem", "Limpa: $sql");
		query($sql);
	}
	
	private function getVendas($dataIni, $dataFim){
		$sql = "SELECT
					    TO_CHAR(DTMOV,'YYYYMMDD') DTMOV,
					    CODCLI,
					    CODSUPERVISOR,
					    CODUSUR,
					    CODPROD,
					    CUSTOREAL,
						CUSTOCONT,
					    SUM(NVL(QTVENDA,0)) QTVENDA,
					    SUM(NVL(VLVENDA,0)) VLVENDA,
					    SUM(NVL(VLVENDA_SEMST,0)) VLVENDA_SEMST,
				       	SUM(NVL(VLBONIFIC,0)) VLBONIFIC,
						SUM(NVL(ST,0)) ST,
						CASE
				            WHEN ORIGEMPED = 'B' THEN 'BALCAO'
				            WHEN origemped = 'C' THEN 'CALLC'
				            WHEN origemped = 'T' THEN 'TELE'
				            WHEN origemped = 'F' AND tipofv IS NULL THEN 'FV'
				            WHEN origemped = 'F' AND tipofv = 'OL' THEN 'OL'
				            WHEN origemped = 'F' AND tipofv = 'PE' THEN 'PE'
							WHEN origemped = 'W' THEN 'WEB'
						END ORIGEM,
						NUMTRANSVENDA,
						NUMNOTA,
       					CODFISCAL,
						INTEGRADORA,
						NUMSEQ
				FROM (
						SELECT                
							PCMOV.DTMOV,
							PCMOV.NUMSEQ,
                            PCNFSAID.NUMTRANSVENDA,
                            PCMOV.NUMNOTA,
                            PCMOV.CODCLI,
                            PCUSUARI.CODUSUR,
                            (select pcusuari.codsupervisor from pcusuari where pcusuari.codusur = PCCLIENT.codusur1) COORDENADOR,
                            PCUSUARI.NOME VENDEDOR,
                            PCNFSAID.CODSUPERVISOR,
                            PCSUPERV.NOME SUPERV,
                            PCNFSAID.NUMPED,
                            PCPEDC.ORIGEMPED,
                            PCPEDC.TIPOFV,
                            ((DECODE(PCMOV.CODOPER,
                                    'S', (NVL(DECODE(PCNFSAID.CONDVENDA,
                                                7,PCMOV.QTCONT,
                                                PCMOV.QT),
                                    0)),
                                   'SM',
                                   (NVL(DECODE(PCNFSAID.CONDVENDA,
                                               7,
                                               PCMOV.QTCONT,
                                               PCMOV.QT),
                                        0)),
                                   'ST',
                                   (NVL(DECODE(PCNFSAID.CONDVENDA,
                                               7,
                                               PCMOV.QTCONT,
                                               PCMOV.QT),
                                        0)),
                                   'SB',
                                   (NVL(DECODE(PCNFSAID.CONDVENDA,
                                               7,
                                               PCMOV.QTCONT,
                                               PCMOV.QT),
                                        0)),
                                   0))) QTVENDA,
                            ((DECODE(PCMOV.CODOPER,
                                   'S',
                                   (NVL(DECODE(PCNFSAID.CONDVENDA,
                                               7,
                                               PCMOV.QTCONT,
                                               PCMOV.QT),
                                        0)),
                                   'ST',
                                   (NVL(DECODE(PCNFSAID.CONDVENDA,
                                               7,
                                               PCMOV.QTCONT,
                                               PCMOV.QT),
                                        0)),
                                   'SM',
                                   (NVL(DECODE(PCNFSAID.CONDVENDA,
                                               7,
                                               PCMOV.QTCONT,
                                               PCMOV.QT),
                                        0)),
                                   0))) QTVENDIDA,
                                   
                            ROUND( ( ((DECODE (PCMOV.CODOPER,
                                                    'S', (NVL(DECODE(PCNFSAID.CONDVENDA,7, PCMOV.QTCONT, PCMOV.QT), 0) ),
                                                    'ST',(NVL(DECODE(PCNFSAID.CONDVENDA,7, PCMOV.QTCONT, PCMOV.QT), 0) ),
                                                    'SM',(NVL(DECODE(PCNFSAID.CONDVENDA,7, PCMOV.QTCONT, PCMOV.QT), 0) ),0))
                                      * (NVL(DECODE(PCNFSAID.CONDVENDA,7,PCMOV.PUNITCONT,NVL(PCMOV.PUNIT,0) + NVL(PCMOV.VLFRETE, 0) +
                                       NVL(PCMOV.VLOUTRASDESP, 0) + NVL(PCMOV.Vlfrete_Rateio, 0) + NVL(PCMOV.Vloutros, 0) - NVL(PCMOV.VLREPASSE, 0) ),0)))  ) ,2) VLVENDA,
                                       
                            ( ((DECODE (PCMOV.CODOPER,'S', (NVL(DECODE(PCNFSAID.CONDVENDA,7, PCMOV.QTCONT, PCMOV.QT), 0) ),
                                                                  'ST',(NVL(DECODE(PCNFSAID.CONDVENDA,7, PCMOV.QTCONT, PCMOV.QT), 0) ),
                                                                  'SM',(NVL(DECODE(PCNFSAID.CONDVENDA,7, PCMOV.QTCONT, PCMOV.QT), 0) ),0))
                                        * (NVL(DECODE(PCNFSAID.CONDVENDA,7,PCMOV.PUNITCONT,NVL(PCMOV.PUNIT,0) + NVL(PCMOV.VLFRETE, 0)
                                        - NVL(PCMOV.ST, 0)  ),0)))  )  VLVENDA_SEMST,
                                        
							PCMOV.CUSTOREAL,
							PCMOV.ST,
							PCMOV.CUSTOCONT,
                           	PCNFSAID.NUMTRANSVENDA QTNUMTRANSVENDA,
                           	PCNFSAID.CODFILIAL,
                           	PCMOV.CODPROD,
                           	PCPRODUT.CODEPTO,
                           	ROUND(    (NVL(PCMOV.QT, 0) *
                            	DECODE(PCNFSAID.CONDVENDA,
                                       5,
                                       DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
                                       6,
                                       DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
                                       11,
                                       DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
                                       12,
                                       DECODE(PCMOV.PBONIFIC, NULL, PCMOV.PTABELA, PCMOV.PBONIFIC),
                                       0)),2) VLBONIFIC,
                           PCMOV.CODFISCAL,
						   PCPEDRETORNO.INTEGRADORA
						   
                FROM   PCNFSAID,
                       PCPRODUT,
                       PCMOV,
                       PCCLIENT,
                       PCUSUARI,
                       PCSUPERV,
                       PCDEPTO,
                       PCPEDC,
					   PCPEDRETORNO
                WHERE PCMOV.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA
                    AND PCMOV.DTMOV BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
                    AND PCNFSAID.DTSAIDA BETWEEN  TO_DATE('$dataIni', 'YYYYMMDD') AND TO_DATE('$dataFim', 'YYYYMMDD')
                    AND PCMOV.CODPROD = PCPRODUT.CODPROD
                    AND PCMOV.CODCLI = PCCLIENT.CODCLI
                    AND PCNFSAID.CODUSUR = PCUSUARI.CODUSUR
                    AND PCMOV.CODOPER <> 'SR'
                    AND PCNFSAID.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR(+)
                    AND PCNFSAID.NUMPED = PCPEDC.NUMPED(+)
                    AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO(+)
					AND PCPEDC.NUMPED = PCPEDRETORNO.NUMPED (+)
                    AND PCNFSAID.CODFISCAL NOT IN (522, 622, 722, 532, 632, 732)
                    AND PCNFSAID.CONDVENDA NOT IN (4, 8, 10, 13, 20, 98, 99)
                    AND (PCNFSAID.DTCANCEL IS NULL)
     ) VENDAS
     GROUP BY  	
				CODCLI,
               	CODSUPERVISOR,
               	CODUSUR,
               	ORIGEMPED,
               	TIPOFV,
               	codprod,
               	DTMOV,
               	CUSTOREAL,
			   	CUSTOCONT,
			   	NUMTRANSVENDA,
			   	NUMNOTA,
			   	CODFISCAL,
				INTEGRADORA,
				NUMSEQ
	ORDER BY NUMNOTA, codprod, NUMSEQ";
		
		$rows = query4($sql);
		echo "Vendas - quantidade: ".count($rows)." <br>\n";
		if(count($rows) >0){
			foreach ($rows as $row){
				$campos = array();
				$campos['data'] 		= $row['DTMOV'];
				$campos['super'] 		= $row['CODSUPERVISOR'];
				$campos['rca'] 			= $row['CODUSUR'];
				$campos['origem'] 		= $row['ORIGEM'];
				$campos['cliente'] 		= $row['CODCLI'];
				$campos['produto'] 		= $row['CODPROD'];
				$campos['quant'] 		= $row['QTVENDA'];
				$campos['vlliquido'] 	= round($row['VLVENDA_SEMST'],2);
				$campos['cmv'] 			= round($row['CUSTOREAL'] * $row['QTVENDA'],2);
				$campos['numtransvenda']= $row['NUMTRANSVENDA'];
				$campos['numnota'] 		= $row['NUMNOTA'];
				$campos['vlbonific'] 	= round($row['VLBONIFIC'],2);
				$campos['interestadual']= $row['CODFISCAL'] > 6000 ? 'S' : 'N';
				$campos['integradora'] 	= $row['INTEGRADORA'] == '' ? 0 : $row['INTEGRADORA'];
				$campos['item'] 		= $row['NUMSEQ'];
				$campos['st'] 			= round($row['ST'] * $row['QTVENDA'],2);
				
				$sql = montaSQL($campos, 'gf_margem'); 
//echo "$sql \n\n";die();
				query($sql);
			}
		}
		
	}

	private function ajustaBonus($dataIni, $dataFim){
		$notas = array();
		$flex = array();
		$sql = "SELECT DISTINCT rca, numnota, item, produto, quant FROM gf_margem WHERE data >= '$dataIni' AND data <= '$dataFim'
		        AND bgerado IS NULL AND bconsumido IS NULL ORDER BY numnota";
		$rows = query($sql);
		echo "Ajusta Bonus - quantidade: ".count($rows)." <br>\n";
		log::gravaLog("calcula_margem", "Execute data ajusta bonus: $sql");
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$nota = $row['numnota'];
				$prod = $row['produto'];
				$notas[$nota]['rca'] = $row['rca'];
				$notas[$nota]['produtos'][$prod][$row['item']] = $row['quant'];
				if(!isset($notas[$nota][$prod]['quant'])){
					$notas[$nota][$prod]['quant'] = 0;
				}
				$notas[$nota][$prod]['quant'] += $row['quant'];
			}
		}
//print_r($notas);die();
		// Verifica custo médio, custo e bonus de cada nota
		if( count($notas) > 0){
			foreach ($notas as $numNota => $nota){
				$sql = "SELECT
							CODIGO,
							SUM(VLCUSTOREAL) VLCUSTOREAL,
							SUM(CUSTOMEDREAL) CUSTOMEDREAL,
							SUM(VLFLEX) VLFLEX
							
						FROM table(CAST(FUNC_RESUMOFATURAMENTO(
						NULL,
						TO_DATE('$dataIni', 'YYYYMMDD'), --:P_DATAINI,
						TO_DATE('$dataFim', 'YYYYMMDD'), --:P_DATAFIM,
						3,
						0,
						1,
						1,
						1,
						0,
						0,
						0,
						'1,5',
						0,
						0,
						0,
						0,
						0,
						0,
						NULL,
						NULL,
						NULL,
						NULL,
						$numNota,
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						0,
						NULL,
						0)as tabela_faturamento))
						
						GROUP BY CODIGO
						";
				//echo "$sql \n\n\n";
				//log::gravaLog("calcula_margem", "Get Bonus Nota: $numNota");
				$rows2 = query4($sql);
				foreach ($rows2 as $row2){
					$produto = $row2['CODIGO'];
					$vlcustoreal = $nota[$produto]['quant'] <> 0 ? ($row2['VLCUSTOREAL'] / $nota[$produto]['quant']) : 0;
					$customedreal = $nota[$produto]['quant'] <> 0 ? ($row2['CUSTOMEDREAL'] / $nota[$produto]['quant']) : 0;
//					$flex = ($row2['VLFLEX'] / $nota[$produto]['quant']);
					
					$notas[$numNota][$produto]['custoReal'] = $vlcustoreal;
					$notas[$numNota][$produto]['medioReal'] = $customedreal;
//					$notas[$numNota][$produto]['flex'] = $flex;
				}
			}
		}
//print_r($notas);			
		//Ajusta a tabela da intranet
		if( count($notas) > 0){
			foreach ($notas as $numNota => $nota){
				$upd = array();
				foreach ($nota['produtos'] as $produto => $itens){
					foreach ($itens as $item => $quant){
						$temp = array();
						$temp['produto'] = $produto;
						$temp['item'] = $item;
						$temp['vlcustoreal'] = $quant * $notas[$numNota][$produto]['custoReal'];
						$temp['customedreal'] = $quant * $notas[$numNota][$produto]['medioReal'];
						if(!isset($flex[$numNota][$produto])){
							$flex[$numNota][$produto] = ($this->getFlex($numNota, $produto)/$notas[$numNota][$produto]['quant']);
						}
						//$temp['flex'] = $quant * $notas[$numNota][$produto]['flex'];
						$temp['flex'] = $quant * $flex[$numNota][$produto];
						
						$upd[] = $temp; 
					}
				}
				
				foreach ($upd as $u){
					$setFlex = '';
					if($u['flex'] > 0){
						$setFlex = "bgerado = ".$u['flex'].", bconsumido = 0, ";
					}elseif ($u['flex'] < 0){
						$setFlex = "bconsumido = ".$u['flex'].", bgerado = 0, ";
					}else{
						$setFlex = "bconsumido = 0, bgerado = 0, ";
					}
					if($nota['rca'] == 11){
						$setFlex = "bconsumido = 0, bgerado = 0, ";
					}
					
					$sql = "UPDATE gf_margem SET $setFlex 	vlcustoreal = ".$u['vlcustoreal'].", customedreal = ".$u['customedreal']." WHERE numnota = $numNota AND produto = ".$u['produto']." AND item = ".$u['item'];
//echo "$sql \n";
					query($sql);
					
				}
			}
		}
	}
	
	private function getFlex($numNota, $produto){
		$ret = 0;
		$sql = "SELECT 
				    (NVL(VLCORRENTE,0) - NVL(VLCORRENTEANT,0)) FLEX
				FROM
				    PCLOGRCA
				WHERE
				    NUMPED = (SELECT NUMPED FROM PCNFSAID WHERE NUMNOTA = $numNota)
				    AND CODPROD = $produto
				";
		$rows = query4($sql);
		if(isset($rows[0]['FLEX'])){
			$ret = $rows[0]['FLEX'];
		}
		
		return $ret;
	}

	private function ressarcimentoST($dataIni, $dataFim){
		$sql = "SELECT CODPROD,
				--  NBM,
				--  DESCRICAO,
				--  UNIDADE,
				    DTSAIDA,
				    NUMNOTA,
					NUMSEQ,
				    CODCLI,
				    CLIENTE,
				--  DATA_ENTRADA,
				--  NOTA_ENTRADA,
				--  CODFORNEC,
				--  FORNECEDOR,
				    sum(QTCONT) QTCONT,
				    SUM(VLRESSARC) VLRESSARC,
				    SUM(ICMSPROPRIO) ICMSPROPRIO,
				    SUM(VLRESSARC) + SUM(ICMSPROPRIO) TOTAL
				    FROM (
				SELECT DADOS.CODPROD,
				       DADOS.NBM,
				       DADOS.DESCRICAO,
				       DADOS.UNIDADE,
				       DADOS.CODFILIAL,
				       DADOS.UFFILIAL,
				       DADOS.NUMNOTA,
					   DADOS.NUMSEQ,
				       DADOS.NUMTRANSVENDA,
				       DADOS.SERIE,
				       DADOS.MODELO,
				       DADOS.DTSAIDA,
				       DADOS.CODCLI,
				       DADOS.CLIENTE,
				       PCFORNEC.CODFORNEC,
				       PCFORNEC.FORNECEDOR,
				       PCNFENT.NUMNOTA AS NOTA_ENTRADA,
				       PCNFENT.DTENT AS DATA_ENTRADA,
				       DADOS.QTCONT QTCONT,
				       DADOS.QTCONT * (NVL(PCMOV.BASEICST,0) + NVL(PCMOV.VLBASESTFORANF,0)) VLBASEST,
				       PCPRODFILIAL.PERCALIQVIGINT ALIQINTERNA,
				       PCMOV.PERCIVA,
				       --------------------------------------------------------------------------------
				       DADOS.QTCONT * (NVL(ST,0) + NVL(VLDESPADICIONAL, 0)) VLRESSARC,
				       --------------------------------------------------------------------------------
				       (DADOS.QTCONT * DECODE(NVL(PCMOV.BASEICMS,0), 0, NVL(PCMOV.PUNITCONT,0) - NVL(PCMOV.VLDESCONTO,0),
				                              PCMOV.BASEICMS) * NVL(PCMOV.PERCICM, 0) / 100) ICMSPROPRIO
				  FROM (SELECT /*+ INDEX_ASC(PCNFSAID PCNFSAID_IDX22, PCMOV PCMOV_IDX15) */ P.CODPROD,
							   M.NUMSEQ,
				               --------------------------------------------------------------------------------
				               P.NBM,
				               --------------------------------------------------------------------------------
				               P.DESCRICAO,
				               --------------------------------------------------------------------------------
				               P.UNIDADE,
				               --------------------------------------------------------------------------------
				               SUM(M.QTCONT) QTCONT,
				               --------------------------------------------------------------------------------
				               FISCAL.RETORNAULTIMAENTRADA(M.CODPROD,M.DTMOV,NVL(N.CODFILIALNF, N.CODFILIAL)) NUMTRANSENT,
				               --------------------------------------------------------------------------------
				               MAX(M.PUNITCONT - NVL(M.VLDESCONTO, 0) + NVL(M.VLIPI, 0)) VLUNITARIO,
				               --------------------------------------------------------------------------------
				               N.NUMNOTA,
				               N.NUMTRANSVENDA,
				               N.SERIE,
				               N.UFFILIAL,
				               DECODE(N.CHAVENFE, NULL, '01', '55') MODELO,
				               N.DTSAIDA,
				               NVL(N.CODCLINF, N.CODCLI) CODCLI,
				               NVL(N.CODFILIALNF, N.CODFILIAL) CODFILIAL,
				               N.CLIENTE
				        FROM PCNFSAID N, PCMOV M, PCPRODUT P, PCCLIENT C
				         WHERE M.NUMTRANSVENDA = N.NUMTRANSVENDA
				           AND M.NUMNOTA = N.NUMNOTA
				           AND P.CODPROD = M.CODPROD
				           AND NVL(N.CODCLINF, N.CODCLI) = C.CODCLI
				           AND NVL(N.CODFILIALNF, N.CODFILIAL)  = '1'
				           AND N.DTSAIDA between to_date('$dataIni','YYYYMMDD') and to_date('$dataFim','YYYYMMDD')
				           AND M.DTCANCEL is null
				           AND N.ESPECIE = 'NF'
				           AND M.CODOPER IN ('S', 'SB', 'ST')
				           AND M.QTCONT > 0
				           
				           AND ((M.CODFISCAL BETWEEN 5000 AND 6999 AND N.ORGAOPUB = 'S') OR
				               (M.CODFISCAL BETWEEN 6000 AND 7999
				                AND NVL(M.BASEICMS, 0) > 0
				                AND NVL(M.PERCICM, 0) > 0)
				                AND N.IE NOT IN ('ISENTO', 'ISENTA')
				                AND NVL(N.CONSUMIDORFINAL, NVL(C.CONSUMIDORFINAL, 'N')) ='N'
				                AND NVL(N.CONTRIBUINTE, NVL(C.CONTRIBUINTE, 'N')) = 'S')
				           AND EXISTS
				         (SELECT DTMOV
				                  FROM PCMOV
				                 WHERE CODPROD = M.CODPROD
				                   AND DTMOV < M.DTMOV
				                   AND CODFILIAL  = '1'
				                   AND CODOPER in ('E', 'EB')
				                   AND (ST > 0 or VLDESPADICIONAL > 0))
				         GROUP BY P.CODPROD,
				               P.NBM,
				               P.DESCRICAO,
				               P.UNIDADE,
				               N.NUMNOTA,
							   M.NUMSEQ,
				               N.NUMTRANSVENDA,
				               N.SERIE,
				               N.UFFILIAL,
				               DECODE(N.CHAVENFE, NULL, '01', '55'),
				               N.DTSAIDA,
				               NVL(N.CODCLINF, N.CODCLI),
				               NVL(N.CODFILIALNF, N.CODFILIAL),
				               N.CLIENTE,
				               M.CODPROD,
				               M.DTMOV
				               ) DADOS,
				       PCMOV,
				       PCNFENT,
				       PCFORNEC,
				       PCPRODFILIAL
				 WHERE DADOS.NUMTRANSENT = PCMOV.NUMTRANSENT
				   AND PCMOV.NUMTRANSENT = PCNFENT.NUMTRANSENT
				   AND PCMOV.CODPROD = DADOS.CODPROD
				   AND PCMOV.CODPROD = PCPRODFILIAL.CODPROD(+)
				   AND NVL(PCMOV.CODFILIALNF, PCMOV.CODFILIAL) = PCPRODFILIAL.CODFILIAL(+)
				   AND NVL(PCNFENT.CODFORNECNF, PCNFENT.CODFORNEC) = PCFORNEC.CODFORNEC
				   AND PCMOV.NUMNOTA = PCNFENT.NUMNOTA
				   AND PCNFENT.ESPECIE = 'NF'
					AND PCMOV.codprod IN (select codprod from PCTABPR where numregiao = 1 and codst in (9,10,294))
				)
				GROUP BY CODPROD, NBM, DESCRICAO, UNIDADE, DTSAIDA, NUMNOTA, CODCLI, CLIENTE, DATA_ENTRADA, NOTA_ENTRADA, CODFORNEC, FORNECEDOR, NUMSEQ
				ORDER BY DESCRICAO
		";
//echo "$sql \n";
		$rows = query4($sql);
		echo "Ressarcimento ST - quantidade: ".count($rows)." <br>\n";
		log::gravaLog("calcula_margem", "Quantidade Ressarcimento: ".count($rows));
		if(count($rows) > 0){
			foreach ($rows as $row){
				$nota = $row['NUMNOTA'];
				$item = $row['NUMSEQ'];
				$produto = $row['CODPROD'];
				$valor = $row['VLRESSARC'];
				$icmsProprio = $row['ICMSPROPRIO'];
				
				$sql = "UPDATE gf_margem SET ressarcst = $valor, creditoicms = $icmsProprio WHERE numnota = $nota AND produto = $produto AND item = $item";
				query($sql);
			}
		}
		//prenche todos os coutros com zero
		$sql = "UPDATE gf_margem SET ressarcst = 0 WHERE ressarcst IS NULL";
		query($sql);
		return;
	}

	private function ajustaLucro($dataIni, $dataFim){
		$sql = "SELECT numnota, item, produto, vlliquido, vlbonific, bgerado, bconsumido, ressarcst,vlcustoreal,creditoicms  FROM gf_margem WHERE data >= '$dataIni' AND data <= '$dataFim' ";
		$rows = query($sql);
		echo "Ajusta Lucros. Quantidade: ".count($rows)." <br>\n";
		log::gravaLog("calcula_margem", "Ajusta Lucro. Quantidade: ".count($rows));
		if(is_array($rows) && count($rows) >0){
			foreach ($rows as $row){
				$nota = $row['numnota'];
				$item = $row['item'];
				$produto = $row['produto'];
				$valor = $row['vlliquido'];
				$bonificacao = $row['vlbonific'] * -1;
				$bonusG = $row['bgerado'];
				$bonusC = $row['bconsumido'];
				$ressarc = $row['ressarcst'];
				//$cmv = $row['CMV'];
				$cmv = $row['vlcustoreal'];
				
				$creditoicms = $row['creditoicms'];
				
				$lucro = false;
				if($valor > 0){
					$lucro = (1-($cmv + $bonusC + $bonusG - $ressarc - $creditoicms)/$valor)*100;
				}elseif($bonificacao > 0){
					//$lucro = ($cmv + $bonusC + $bonusG + $ressarc)/$bonificacao;
					$lucro = (1-(($cmv + $bonusC + $bonusG - $ressarc - $creditoicms))/$bonificacao)*100;
				}
				//echo "Calculo: $lucro <br>\n";
				if($lucro === false){
					$lucro = 0;
				}
				$sql = "UPDATE gf_margem SET lucro = $lucro WHERE numnota = $nota AND produto = $produto AND item = $item";
				//echo "$sql \n";
				query($sql);
			}
			
		}
		return;
	}
	
	/*
	* São produtos que a gente estorna o ICMS nas vendas feitas para o rio grande do sul
	* Solicitado por Márcio/Neto em 05/2017
	*
	* Query retirada da rotina 1031 - RS - Relatório 8
	*/
	private function icmsCestaBasica($dataIni, $dataFim,$percentual = '38.33'){
		$sql = "
			SELECT
				PRODUTO,
			 	VLREDCREDADIMITIDO,
			 	-- Informações de Saída
			 	DTSAIDA,
			 	NUMNOTASAI,
				NUMSEQ,
			 	QTCONTSAI,
			 	-- Informações de Entrada
			 	NUMNOTAENT,
			 	DTENT,
			 	ALIQICMSENT,
			 	VLBASEICMSUNITENT,
			 	VLICMSUNITENT,
			 	-- Informações Calculadas
			 	VLBASEENTREDUZIDA,
			 	VLCREDTOTENTRADA,
			 	VLCREDTOTREDUZIDO,
			 	GREATEST(VLCREDTOTENTRADA - VLCREDTOTREDUZIDO, 0) VLTOTALESTCREDITO
			 FROM (
				 SELECT
					PRODUTO,
					VLREDCREDADIMITIDO,
					-- Informações de Saída
					DTSAIDA,
					NUMNOTASAI,
					NUMSEQ,
					QTCONTSAI,
					-- Informações de Entrada
					NUMNOTAENT,
					DTENT,
					ALIQICMSENT,
					VLBASEICMSUNITENT,
					VLICMSUNITENT,
					-- Informações Calculadas
					ROUND(VLBASEICMSUNITENT * VLREDCREDADIMITIDO / 100, 2) VLBASEENTREDUZIDA,
					ROUND(VLICMSUNITENT * QTCONTSAI, 2) VLCREDTOTENTRADA,
					ROUND((ROUND(VLBASEICMSUNITENT * VLREDCREDADIMITIDO / 100, 2) * ALIQICMSENT / 100) * QTCONTSAI, 2) VLCREDTOTREDUZIDO,
					VLTOTALESTCREDITO
				 FROM (
				 	SELECT
							P.CODPROD PRODUTO,
				 			$percentual VLREDCREDADIMITIDO,
							-- Informações de Saída
							SAI.DTSAIDA,
							SAI.NUMNOTASAI,
							SAI.NUMSEQ,
							SUM(SAI.QTCONTSAI) QTCONTSAI,
							-- Informações de Entrada
							ENT.NUMNOTAENT,
							ENT.DTENT,
							ENT.ALIQICMSENT,
							ENT.VLBASEICMSUNITENT,
							ENT.VLICMSUNITENT,
							-- Informações Calculadas
							0 VLBASEENTREDUZIDA,
							0 VLCREDTOTENTRADA,
							0 VLCREDTOTREDUZIDO,
							0 VLTOTALESTCREDITO
				 	FROM (
					 		SELECT
					 			S.NUMTRANSVENDA,
								S.NUMNOTA NUMNOTASAI,
								MS.NUMSEQ,
								S.DTSAIDA,
								MS.CODPROD,
								MS.QTCONT QTCONTSAI,
								(SELECT MAX(E1.NUMTRANSENT)
							 		FROM
							 			PCNFENT E1,
							 			PCMOV M1
							 		WHERE
							 			E1.NUMTRANSENT = M1.NUMTRANSENT
										AND E1.NUMNOTA = M1.NUMNOTA
										AND M1.CODPROD = MS.CODPROD
										AND E1.ESPECIE = 'NF'
										AND M1.CODFISCAL IN (1102,1910,2102,2910)
										AND M1.QTCONT > 0
										AND E1.DTENT < S.DTSAIDA
										AND NVL(M1.PERCICM, 0) > 0
										AND NVL(M1.BASEICMS, 0) > 0
										AND M1.STATUS IN ('A','AB')
									) NUMTRANSENTULTENT
				 	FROM
				 		PCNFSAID S,
				 		PCMOV MS
				 	WHERE
				 		S.NUMTRANSVENDA = MS.NUMTRANSVENDA
						AND S.NUMNOTA = MS.NUMNOTA
						AND S.DTCANCEL IS NULL
						AND S.ESPECIE IN ('NF','CF','CP','CT','CO')
						AND MS.DTCANCEL IS NULL
						AND MS.STATUS IN ('A','AB')
						AND MS.QTCONT > 0
						AND S.DTSAIDA BETWEEN to_date('$dataIni','YYYYMMDD') AND to_date('$dataFim','YYYYMMDD')
						AND NVL(S.CODFILIALNF, S.CODFILIAL) = 1
						AND MS.CODFISCAL IN (5102,5403,5910)
				 	) SAI,
				 	(
				 	SELECT
				 		E.NUMNOTA NUMNOTAENT,
						E.NUMTRANSENT,
						E.DTENT,
						ME.CODPROD,
						MAX(NVL(ME.PERCICM, 0)) ALIQICMSENT,
						MAX(NVL(ME.BASEICMS, 0)) VLBASEICMSUNITENT,
						MAX(ROUND(NVL(MCE.VLICMS, ME.BASEICMS * ME.PERCICM / 100), 2)) VLICMSUNITENT
				 	FROM
				 		PCNFENT E,
						PCMOV ME,
						PCMOVCOMPLE MCE
				 	WHERE
				 		E.NUMTRANSENT = ME.NUMTRANSENT
						AND E.NUMNOTA = ME.NUMNOTA
						AND ME.NUMTRANSITEM = MCE.NUMTRANSITEM(+)
						AND E.ESPECIE = 'NF'
						AND ME.STATUS IN ('A','AB')
						AND ME.DTCANCEL IS NULL
						AND ME.QTCONT > 0
						AND NVL(ME.PERCICM, 0) > 0
						AND NVL(ME.BASEICMS, 0) > 0
						AND ME.CODFISCAL IN (1102,1910,2102,2910)
						AND NVL(E.CODFILIALNF, E.CODFILIAL) = 1
				 	GROUP BY
				 		E.NUMNOTA,
				 		E.NUMTRANSENT,
				 		E.DTENT,
				 		ME.CODPROD
				 	) ENT,
				 	PCPRODUT P
				 WHERE
				 	ENT.NUMTRANSENT = SAI.NUMTRANSENTULTENT
				 	AND NVL(P.CESTABASICALEGIS,'N') = 'S'
				 	AND P.CODPROD = SAI.CODPROD
				 	AND P.CODPROD = ENT.CODPROD
				 GROUP BY
				 	SAI.DTSAIDA,
				 	SAI.NUMNOTASAI,
				 	ENT.NUMNOTAENT,
					SAI.NUMSEQ,
				 	ENT.DTENT,
				 	ENT.ALIQICMSENT,
				 	ENT.VLBASEICMSUNITENT,
				 	ENT.VLICMSUNITENT,
				 	P.CODPROD,
				 	P.DESCRICAO
				 )
				 )
			 WHERE 0 = 0
			 	AND (VLCREDTOTENTRADA - VLCREDTOTREDUZIDO) > 0
			 ORDER BY
				DTSAIDA,
				NUMNOTASAI,
				PRODUTO,
				DTENT,
				NUMNOTAENT
				";
//echo "$sql <br>\n\n";
 		//log::gravaLog("calcula_margem", "ICMS $sql");
 		$rows = query4($sql);
 		log::gravaLog("calcula_margem", "ICMS quantidade: ".count($rows));
 		if(count($rows) > 0){
 			foreach ($rows as $row){
 				$nota = $row['NUMNOTASAI'];
 				$item = $row['NUMSEQ'];
 				$produto = $row['PRODUTO'];
 				$valor = $row['VLTOTALESTCREDITO'];
 				$sql = "SELECT * FROM gf_margem WHERE numnota = $nota AND produto = $produto AND item = $item";
 				//log::gravaLog("Procura: $sql");
 				$tempR = query($sql);
 				if(count($tempR) > 0){
 					$sql = "UPDATE gf_margem SET icmsCestaBasica = $valor WHERE numnota = $nota AND produto = $produto AND item = $item";
 					//log::gravaLog("calcula_margem", "ICMS: $sql");
 					query($sql);
 				}else{
 					log::gravaLog("calcula_margem_erro", "Não encontrada nota $nota produto $produto");
 					echo "Não encontrada nota $nota produto $produto <br>\n";
 				}
 			}
 		}
	}

	private function atualizaOracle($dataIni, $dataFim){
		$sql = "DELETE FROM TWS_MARGEM WHERE DATA >= '$dataIni' AND DATA <= '$dataFim'";
		query4($sql);
		
		$sql = "SELECT * FROM gf_margem WHERE data >= '$dataIni' AND data <= '$dataFim'";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$bgerado = $row['bgerado'] == '' ? 0 : $row['bgerado'];
				$bconsumido = $row['bconsumido'] == '' ? 0 : $row['bconsumido'];
				
				$vlliquido		= $row['vlliquido']    		== '' ? 0 : $row['vlliquido'];
				$vlbonific		= $row['vlbonific']    		== '' ? 0 : $row['vlbonific'];
				$cmv			= $row['cmv']          		== '' ? 0 : $row['cmv'];
				$vlcustoreal	= $row['vlcustoreal']  		== '' ? 0 : $row['vlcustoreal'];
				
				$custoMedioReal = $this->getCustoMedio($row['data'], $row['produto'], $row['customedreal']);
				//$customedreal	= $row['customedreal'] 		== '' ? 0 : $row['customedreal'];
				$customedreal	= $custoMedioReal;
				
				$ressarcst		= $row['ressarcst']    		== '' ? 0 : $row['ressarcst'];
				$lucro			= $row['lucro']        		== '' ? 0 : $row['lucro'];
				$icmsCestaBasica= $row['icmsCestaBasica']   == '' ? 0 : $row['icmsCestaBasica'];
				
				$integradora = $row['integradora'];
				$st = $row['st'];
				
				$campos = array();
				$campos['DATA']				= $row['data'];
				$campos['SUPER']			= $row['super'];
				$campos['RCA']				= $row['rca'];
				$campos['ORIGEM']			= $row['origem'];
				$campos['CLIENTE']			= $row['cliente'];
				$campos['PRODUTO']			= $row['produto'];
				$campos['QUANT']			= $row['quant'];
				$campos['VLLIQUIDO']		= $vlliquido;
				$campos['VLBONIFIC']		= $vlbonific;
				$campos['BGERADO']			= $bgerado;
				$campos['BCONSUMIDO']		= $bconsumido;
				$campos['CMV']				= $cmv;
				$campos['VLCUSTOREAL']		= $vlcustoreal;
				$campos['CUSTOMEDREAL']		= $customedreal;
				$campos['RESSARCST']		= $ressarcst;
				$campos['LUCRO']			= $lucro;
				$campos['NUMTRANSVENDA']	= $row['numtransvenda'];
				$campos['NUMNOTA']			= $row['numnota'];
				$campos['CREDITOICMS']		= $row['creditoicms'];
				$campos['ICMSCESTABASICA']	= $icmsCestaBasica;
				$campos['INTEGRADORA']		= $integradora;
				$campos['ITEM']				= $row['item'];
				$campos['CMV_ST']			= $cmv - $st;
				
				$sql = montaSQL($campos, 'TWS_MARGEM');
				query4($sql);
			}
		}
	}
	
	private function getCustoMedio($dia, $produto, $custoTabela){
		$ret = 0;
		if(!isset($this->_custos[$dia][$produto])){
			$sql = "select CUSTOREAL from pchistest where codprod = $produto and data = to_date('$dia','YYYYMMDD') and codfilial = 1";
			$rows = query4($sql);
			if(isset($rows[0][0])){
				$this->_custos[$dia][$produto] = $rows[0][0];
			}else{
				$this->_custos[$dia][$produto] = $custoTabela;
			}
		}
		$ret = $this->_custos[$dia][$produto];
		
		return $ret;
	}
}