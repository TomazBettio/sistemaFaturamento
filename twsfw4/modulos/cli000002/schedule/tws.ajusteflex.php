<?php
/*
* Data Cria��o: 10/06/2014 - 15:23:02
* Autor: Thiel
*
* Arquivo: class.ora_ajusteflex.inc.php
* 
* Schedule di�rio que retira 10% do flex positivo da venda de cada vendedor e 
* lan�a na conta do gerente
* 
* 
* Alterções:
*           02/01/2019 - Emanuel - Migração para intranet2
*           26/01/2023 - Thiel - Por solicitação no Neto: 15% p/ GD e 5% para GC
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class ajusteflex{
	var $_lancamentos = [];
	var $_percent;
	var $_saldo;
	
	//Contas que recebem os bonus (não devem ser monitoradas)
	private  $_contas_add;
	
	//Conta que recebe os valores 
	private  $_conta;
	
	//Conta RCA bonus parceria
	var $_contaParceria;
	var $funcoes_publicas = array(
		'index' 		=> true,
		'schedule' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '000002.ajusteflex';
	
	//Supervisores por RCA
	var $_super;
	
	//Contas CIFARMA monitoradas
	private $_contas_cifarma = [];
	
	//Conta CIFARMA destino
	private $_conta_cifarma;

	function __construct(){
		set_time_limit(0);
		
		//Codigo ERC do GC
		$this->_conta = 88;
		
		$this->_contas_add = '268, 824';
		
		$this->_contaParceria = 564;
		
		$this->_contas_cifarma = [820,821];
		$this->_conta_cifarma = 824;
		
		$this->_percent = 0.2;
	}			
	
	function index(){
	
	}

	function schedule($param){
		//Seleciona lancamentos de vendedores
		$this->getLancamentos();
		
		$supers = $this->getCodSuper();
		
		if(count($this->_lancamentos) > 0){
			foreach ($this->_lancamentos as $lanc){
				$isParceria = $this->getParceria($lanc['ped']);
				if(!$isParceria){
					//Nao tem contrato de parceria
					$valorG = $lanc['val'] * 0.05; // Diretor
					$valorS = $lanc['val'] * 0.15; // Supervisor
					if(in_array($lanc['vend'], $this->_contas_cifarma)){
						$historico = ' CIFARMA';
					}else{
						$historico = '';
					}
				}else{
					//Tem contrato de parceria
					$valorG = $lanc['val'];
					$historico = ' - PARCERIA';
				}
				
				log::gravaLog("ajustaFlex", 'Valor Original Flex: '.$lanc['val']);
				
				if(!$isParceria){
			//----------------------------------------------------------------------- Lancamento Diretor ---------------
					if($valorG > 0){
						$this->_saldo[$lanc['vend']] += $valorG;
						
						//insete historio de retirada
						$saldo = $this->getSaldo($lanc['vend']);
						$this->insereHistorico($lanc['vend'], ($saldo - $valorG), $saldo, 'CAMPANHA'.$historico.' - PEDIDO: '.$lanc['ped'],$lanc['ped']);
						$this->ajustaSaldo($lanc['vend'], ($valorG * -1));
						log::gravaLog("ajustaFlex", $historico.' - '.$valorG.' Vendedor: '.$lanc['vend'].' Pedido: '.$lanc['ped'].' (Diretor)');
						
						
						//insete historio de entrada
						if(in_array($lanc['vend'], $this->_contas_cifarma)){
							//Venda CIFARMA
							$saldo = $this->getSaldo($this->_conta_cifarma);
							$this->insereHistorico($this->_conta_cifarma, ($saldo + $valorG), $saldo, 'CAMPANHA - ERC: '.$lanc['vend'].' - PEDIDO: '.$lanc['ped'],$lanc['ped']);
							$this->ajustaSaldo($this->_conta_cifarma, $valorG);
							log::gravaLog("ajustaFlex", $historico.' + '.$valorG.' Diretor Pedido: '.$lanc['ped']);
						}else{
							//Outras vendas
							$saldo = $this->getSaldo($this->_conta);
							$this->insereHistorico($this->_conta, ($saldo + $valorG), $saldo, 'CAMPANHA - ERC: '.$lanc['vend'].' - PEDIDO: '.$lanc['ped'],$lanc['ped']);
							$this->ajustaSaldo($this->_conta, $valorG);
							log::gravaLog("ajustaFlex", $historico.' + '.$valorG.' Diretor Pedido: '.$lanc['ped']);
						}
					}
				
			//----------------------------------------------------------------------- Lancamento Super ----------------
				
					if(!in_array($lanc['vend'], $supers)){
						$super = $this->getSuper($lanc['vend']);
						if($super > 0 && $valorS > 0){
							$this->_saldo[$lanc['vend']] += $valorS;
							//insete historio de retirada
							$saldo = $this->getSaldo($lanc['vend']);
							$this->insereHistorico($lanc['vend'], ($saldo - $valorS), $saldo, 'CAMPANHA - SUPER - PEDIDO: '.$lanc['ped'],$lanc['ped']);
							$this->ajustaSaldo($lanc['vend'], ($valorS * -1));
							log::gravaLog("ajustaFlex", '- '.$valorS.' Vendedor: '.$lanc['vend'].' Pedido: '.$lanc['ped'].' (Super: '.$super.')');
							
							//insete historio de entrada
							$saldo = $this->getSaldo($super);
							$this->insereHistorico($super, ($saldo + $valorS), $saldo, 'CAMPANHA - SUPER - RCA: '.$lanc['vend'].' - PEDIDO: '.$lanc['ped'],$lanc['ped']);
							$this->ajustaSaldo($super, $valorS);
							log::gravaLog("ajustaFlex", '+ '.$valorS.' Super: '.$super.' Pedido: '.$lanc['ped']);
						}
					}
				}else{
					$this->_saldo[$lanc['vend']] += $valorG;
					
					//insete historio de retirada
					$saldo = $this->getSaldo($lanc['vend']);
					$this->insereHistorico($lanc['vend'], ($saldo - $valorG), $saldo, 'CAMPANHA'.$historico.' - PEDIDO: '.$lanc['ped'],$lanc['ped']);
					$this->ajustaSaldo($lanc['vend'], ($valorG * -1));
					log::gravaLog("ajustaFlex", $historico.' - '.$valorG.' Vendedor: '.$lanc['vend'].' Pedido: '.$lanc['ped'].' (Parceria)');
					
					//insete historio de entrada
					$saldo = $this->getSaldo($this->_contaParceria);
					$this->insereHistorico($this->_contaParceria, ($saldo + $valorG), $saldo, 'CAMPANHA - ERC: '.$lanc['vend'].' - PEDIDO: '.$lanc['ped'],$lanc['ped']);
					$this->ajustaSaldo($this->_contaParceria, $valorG);
					log::gravaLog("ajustaFlex", $historico.' + '.$valorG.' Parceria Pedido: '.$lanc['ped']);
					
				}
				
				$this->bloqueiaMovimento($lanc['vend'], $lanc['ped']);
				echo "existem lançamentos";
			}
		}else{
			echo "nao existem lancamentos";
			log::gravaLog("ajustaFlex", "nao existem lancamentos");
		}

	}
	
	//Retorna o supervisor do RCA
	function getSuper($rca){
		$super = 0;
		if(isset($this->_super[$rca])){
			$super = $this->_super[$rca];
		}else{
			$sql = "select (select cod_cadrca from pcsuperv where pcsuperv.codsupervisor = pcusuari.codsupervisor) SUPER from pcusuari where pcusuari.codusur = $rca";
			$rows = query4($sql);
			$super = $rows[0][0];
			$this->_super[$rca] = $super;
		}
		
		return $super;
	}
	
	
	function getSaldo($rca){
		$sql = "
				SELECT  nvl(pc_pkg_controlarsaldorca.ccrca_checar_saldo_atual($rca),0) VLCORRENTE,
				        nvl(pc_pkg_controlarsaldorca.ccrca_checar_limite_credito($rca),0) VLLIMCRED
				FROM DUAL
		";
		$rows = query4($sql);
		
		return floatval('0'.str_replace(',', '.',$rows[0][0]));
	}
	
	function insereHistorico($rca, $valNovo, $valAnt, $historico,$pedido = 0){
		if($pedido == ''){
			$pedido = 0;
		}
		$sql = "
			INSERT INTO PCLOGRCA (DATA, CODFUNC, CODUSUR, ROTINA, VLCORRENTE, VLCORRENTEANT, HISTORICO,GF_PROC,NUMPED)
			VALUES
			((SELECT SYSDATE FROM DUAL), 68, $rca, 999, $valNovo, $valAnt, '$historico','*',$pedido)
		";
//echo "Historico: $sql <br>\n";
		query4($sql);
	}
	
	function ajustaSaldo($rca,$valor){
		$sql = "UPDATE PCUSUARI SET VLCORRENTE = (NVL(VLCORRENTE, 0) + $valor) WHERE CODUSUR = $rca";
		query4($sql);

		if($this->_conta == $rca){
			$sql = "INSERT INTO gf_ajusteflex (rca, transf) VALUES ($rca, $valor)";
			query($sql);
		}
	}
	
	function bloqueiaMovimento($rca, $pedido){
		$sql = "UPDATE PCLOGRCA SET GF_PROC = '*' WHERE CODUSUR = $rca and numped = $pedido";
//echo "Bloqueia: $sql <br>\n";
		query4($sql);
	}

	function getLancamentos(){
		$sql = "		
			select 
			    sum(NVL(vlcorrente,0) - NVL(vlcorrenteant,0)) val, 
			    round(sum(NVL(vlcorrente,0) - NVL(vlcorrenteant,0))* ".$this->_percent.",2) desconto, 
			    pclogrca.codusur,
			    data,
			    historico,
			    numped
			from 
			    pclogrca 
			where  historico not like '%ESTORNO%'
			   and historico not like '%DEVOLUCAO%'
			   and numped is not null
			   and GF_PROC is null
			   and codusur NOT IN  (".$this->_contas_add.")
			   and data > SYSDATE -1
			   and (NVL(vlcorrente,0) - NVL(vlcorrenteant,0)) > 0.1
			group by
			    pclogrca.codusur,
			    data,
			    historico,
			    numped
			order by codusur, data 
		";
				
		$rows = query4($sql);
		if(count($rows) > 0){
			$i=0;
			foreach ($rows as $row){
				$this->_saldo[$row[2]] = 0;
				$this->_lancamentos[$i]['val'] = floatval('0'.str_replace(',', '.',$row[0]));
				$this->_lancamentos[$i]['desc'] = floatval('0'.str_replace(',', '.',$row[1]));
				$this->_lancamentos[$i]['vend'] = $row[2];
				$this->_lancamentos[$i]['data'] = $row[3];
				$this->_lancamentos[$i]['hist'] = $row[4];
				$this->_lancamentos[$i]['ped'] = $row[5];
				$i++;
			}
		}
		
		echo "<br>\nEncontrados ".count($this->_lancamentos)." lançamentos. <br>\n";
	}
	
	function getCodSuper(){
		$ret = array();
		$sql = "select cod_cadrca from pcsuperv where dtdemissao is null";
		$rows = query4($sql);

		if(count($rows) > 0){
			foreach ($rows as $row){
				$ret[] = $row;
			}
		}
		
		return $ret;
	}

	/*
	 * Verifica se o cliente tem contrato de parceria ativo, pois caso tenha todo o bonus e transferido para o Ivair
	 * 
	 */
	function getParceria($pedido){
		$ret = false;
		$sql = "
				select  
				    PCACORDOPARCERIA.codacordo
				from 
				    PCACORDOPARCERIA, pcpedc, PCACORDOPARCERIACLI
				where
				    pcpedc.numped = $pedido
				    and PCACORDOPARCERIACLI.codcli = pcpedc.codcli
				    and PCACORDOPARCERIACLI.codacordo = PCACORDOPARCERIA.codacordo
				    and PCACORDOPARCERIA.dtvigenciaini <= SYSDATE  and PCACORDOPARCERIA.dtvigenciafin >= SYSDATE
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			$ret = true;
		}
		
		return $ret;
	}
}