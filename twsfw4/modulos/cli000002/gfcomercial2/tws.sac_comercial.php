<?php
/*
 * Data Criacao: 28/12/2017
 * Autor: TWS - Alexandre Thiel
 *
 * Descrição: Relatórios de chamados no SAC que estão abertos
 *
 * Alterações:
 * 		28/12/2017 - Criação. Alexandre Thiel
 * 		21/11/2019 - Solicitado pelo Gustava para voltar a ser enviado para os ERCs. Alexandre Thiel
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class sac_comercial{
	var $_relatorio;
	
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Titulo variavel
	var $_titulo;
	
	//Dados do relatorio
	var $_dados;
	
	//Supervisores
	var $_super;
	
	//ERCs
	var $_erc;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		$this->_enviaEmailERCteste = true;
		
		$this->_programa = 'sac_comercial';
		
		$this->_titulo = 'SAC - Comercial';
		
		$this->_periodos = [];
		
		$param= [];
		$param['colunasForm'] 	= 1;
		$param['programa']		= $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		//$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'supervisor'	, 'etiqueta' => 'Regiao Nome'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
//		$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => 'ERC'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => 'ERC Nome'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'codcli'		, 'etiqueta' => 'CodCli'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'			, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'obs'			, 'etiqueta' => 'OBS'				, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'obs2'		, 'etiqueta' => 'OBS SAC'			, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'motivo'		, 'etiqueta' => 'Motivo'			, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'valor'		, 'etiqueta' => 'Valor Devolucao'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'notaE'		, 'etiqueta' => 'Nota Ent.'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'dataE'		, 'etiqueta' => 'Data Ent.'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
		
		$this->_relatorio->addColuna(array('campo' => 'notaS'	 	, 'etiqueta' => 'Nota Saida'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'dataS'	 	, 'etiqueta' => 'Data Saida'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
		
		if(false){
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'	, 'variavel' => 'DATAINI'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'	, 'variavel' => 'DATAFIM'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		}
	}
	
	
	function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$dataIni = isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dataFim = isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		
		
		$this->_relatorio->setTitulo($this->_titulo);
		if(!$this->_relatorio->getPrimeira() ){
			$this->_relatorio->setTitulo($this->_titulo." Periodo: ".datas::dataS2D($dataIni,2).' a '.datas::dataS2D($dataFim,2));
			$this->getDados($dataIni, $dataFim);
			$this->getOBS2();
//print_r($this->_dados);
			$dados = [];
			foreach ($this->_dados as $super => $sacs){
				foreach ($sacs as $erc => $sac){
					foreach ($sac as $s){
						$dados[] = $s;
					}
				}
			}
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	function schedule($param){
	    $dia = date('Ymd');
		$dataIni = datas::getDataDias(-31);
		$dataFim = datas::getDataDias(-1);
		$emails = str_replace(',', ';', $param);
		
		$titulo = $this->_titulo." Periodo: ".datas::dataS2D($dataIni,2).' a '.datas::dataS2D($dataFim,2);
		$this->_relatorio->setTitulo($titulo);
		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true);
		
		$this->getVendedores();
		
		$this->getDados($dataIni, $dataFim);
		$this->getOBS2();
		
		$this->_relatorio->setEnviaTabelaEmail(false);
		$dados = [];
		if(count($this->_dados) > 0){
			$dadosGeral = [];
			foreach ($this->_dados as $super => $vend){
				$dadosSuper = [];
				foreach ($vend as $erc => $vendas){
					$dados = [];
					foreach ($vendas as $v){
						$dados[] = $v;
						$dadosGeral[] = $v;
						$dadosSuper[] = $v;
					}
					/*/ Não enviar para os ERCs
					 * 21/11/2019 - Solicitado voltar a enviar aos ERCs. Gustavo
					 */
					$this->_relatorio->setDados($dados);
					$email = $this->_erc[$erc]['email'];
					if(!$this->_teste){
						//$this->_relatorio->enviaEmail($email,$titulo);
						$this->_relatorio->agendaEmail($dia,'09:00','sac_comercial',$email,$titulo);
						log::gravaLog('sac_comercial', ' Email ERC: '.$erc.' - '.$email);
					}else{
						if($this->_enviaEmailERCteste){
							if($this->_enviaEmailERCteste){
								// Só envia 10 emails
								if(!isset($this->_enviados)){
									$this->_enviados = 0;
								}
								$this->_enviados++;
								if($this->_enviados <= 10){
									$this->_relatorio->enviaEmail('thiel@thielws.com.br',$titulo.' - '.$erc.' - '.$email);
								}
							}
						}
					}
					
					
				}
				$this->_relatorio->setDados($dadosSuper);
				$email = $this->_super[$super]['email'];
				if(!$this->_teste){
					//$this->_relatorio->enviaEmail($email,$titulo);
				    $this->_relatorio->agendaEmail($dia,'09:00','sac_comercial',$email,$titulo);
					log::gravaLog('sac_comercial', 'Agendado email Super: '.$super.' - '.$email);
				}else{
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$super.' - '.$email);
				}
				
			}
		}
		$this->_relatorio->setDados($dadosGeral);
		
		if(!$this->_teste){
			//$this->_relatorio->enviaEmail($emails,$titulo);
		    $this->_relatorio->agendaEmail($dia,'09:00','sac_comercial',$emails,$titulo);
			log::gravaLog("sac_comercial", "Agendado email Geral: ".$emails);
		}else{
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
		}
	}
	
	private function getOBS2(){
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $super => $sacs){
				foreach ($sacs as $erc => $sac){
					foreach ($sac as $i => $s){
						$obs_sac = $this->getManifOBS($s['notaS']);
						$this->_dados[$super][$erc][$i]['obs2'] = $obs_sac;
					}
				}
			}
		}
	}
	
	private function getManifOBS($nota){
		$ret = '';
		if(!empty($nota)){
			$sql = "select PCMANIF.CARGO from PCMANIF where PCMANIF.numnota = $nota";
			$rows = query4($sql);
			if(count($rows) > 0){
				foreach ($rows as $row){
					if($ret != ''){
						$ret .= "<br>\n";
					}
					$ret .= $row['CARGO'];
				}
			}
		}
		
		return $ret;
	}
	
	function getDados($dataIni, $dataFim){
		$sql = "
				SELECT 
				    PCNFENT.NUMNOTA, 
				    PCNFENT.NUMNOTAVENDA,
				    PCNFSAID.NUMTRANSVENDA,
					PCNFSAID.numnota NUMNOTAVENDA2,
				       PCNFENT.DTENT,
				       PCNFsaid.DTSAIDA,
				       DECODE(PCNFENT.VLTOTAL,0,PCESTCOM.VLDEVOLUCAO,PCNFENT.VLTOTAL) AS VLTOTAL,
				       PCNFENT.CODDEVOL,
				       PCNFENT.OBS,
				       PCTABDEV.MOTIVO,
				       PCNFENT.NUMTRANSENT,
				       PCCLIENT.CODCLI,
				       NVL(PCDEVCONSUM.CLIENTE, PCCLIENT.CLIENTE) CLIENTE,
					   PCUSUARI.CODUSUR,
				       PCUSUARI.NOME NOMERCA,
				       PCUSUARI.CODSUPERVISOR,
				       PCSUPERV.NOME SUPERVISOR,

				        PCNFENT.VLFRETE,
				        PCNFENT.VLOUTRAS,
				        DECODE(PCNFENT.VLTOTAL,0,PCESTCOM.VLDEVOLUCAO,PCNFENT.VLTOTAL) - NVL(PCNFENT.VLOUTRAS,0)  - NVL(PCNFENT.VLFRETE,0)AS VLTOTNF 
				FROM 
				    PCNFENT, 
				    PCESTCOM, 
				    PCTABDEV, 
				    PCCLIENT, 
				    PCEMPR, 
				    PCUSUARI, 
				    PCSUPERV, 
				    PCEMPR FUNC, 
				    PCNFSAID, 
				    PCDEVCONSUM
				--  PCMANIF
				WHERE  
				    ( PCNFENT.CODDEVOL = PCTABDEV.CODDEVOL(+) )
				    AND PCNFENT.NUMTRANSENT = PCESTCOM.NUMTRANSENT (+)
				    AND   ( PCNFENT.CODFORNEC  = PCCLIENT.CODCLI )
				    AND ( PCNFENT.NUMTRANSENT = PCDEVCONSUM.NUMTRANSENT(+) )
				    AND NVL(PCNFENT.CODFILIALNF, PCNFENT.CODFILIAL) = 1
				    AND   ( PCNFENT.CODFUNCLANC       = FUNC.MATRICULA(+))
				    AND   ( PCNFENT.CODMOTORISTADEVOL = PCEMPR.MATRICULA(+))
				    AND   (  PCNFENT.CODUSURDEVOL  = PCUSUARI.CODUSUR )
				    AND   ( PCUSUARI.CODSUPERVISOR    = PCSUPERV.CODSUPERVISOR(+))
				    
				    AND   ( PCNFENT.DTENT BETWEEN to_date('$dataIni','YYYYMMDD') AND to_date('$dataFim','YYYYMMDD') )
				
				    AND   ( PCNFENT.TIPODESCARGA IN ('6','7','T') ) 
				    AND   ( NVL(PCNFENT.OBS, 'X') <> 'NF CANCELADA')
				    AND   ( PCNFENT.CODFISCAL IN ('131','132','231','232','199','299') )
				    AND EXISTS (SELECT PCPRODUT.CODPROD FROM PCPRODUT, PCMOV
				                 WHERE PCMOV.CODPROD = PCPRODUT.CODPROD
				                 AND PCMOV.NUMTRANSENT = PCNFENT.NUMTRANSENT
				                 AND PCMOV.NUMNOTA = PCNFENT.NUMNOTA
				                 AND PCMOV.DTCANCEL IS NULL
				    AND PCMOV.CODFILIAL = PCNFENT.CODFILIAL)
				    AND PCESTCOM.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA(+) 
				    AND NVl(PCNFSAID.CONDVENDA,0) NOT IN (4, 8, 10, 13, 20, 98, 99)
				    AND PCESTCOM.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA(+) 
				GROUP BY 
				    PCNFENT.NUMNOTA,
				    PCNFENT.DTENT,
				    PCNFsaid.DTSAIDA,                                           
				    PCNFENT.CODDEVOL,
				    PCNFENT.OBS,
				    PCTABDEV.MOTIVO,
				    PCNFENT.NUMTRANSENT,                                            
				    PCCLIENT.CODCLI,           
					PCUSUARI.CODUSUR,                                      
				    PCUSUARI.NOME ,
				    PCUSUARI.CODSUPERVISOR,
				    PCSUPERV.NOME ,
				    PCNFENT.VLFRETE, 
				    PCNFENT.VLOUTRAS,
				    PCNFENT.VLTOTAL,
				    PCESTCOM.VLDEVOLUCAO,
				    pcnfent.numnotavenda,
				    PCNFSAID.NUMTRANSVENDA,
				 --   PCMANIF.CARGO,
					PCNFSAID.numnota,
					NVL(PCDEVCONSUM.CLIENTE, PCCLIENT.CLIENTE) ,
				    PCNFENT.CODFORNEC                                        
				ORDER BY 

				    PCNFENT.CODDEVOL, 
				    PCNFENT.CODFORNEC, 
				    PCNFENT.DTENT
				
				";
//echo "$sql";die();
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];

				$super = $row['CODSUPERVISOR'];
				$erc = $row['CODUSUR'];
				
				$temp['supervisor'] = $row['SUPERVISOR'];
				$temp['vendedor'] = $row['NOMERCA'];
				
				$temp['codcli'] = $row['CODCLI'];
				$temp['cliente'] = $row['CLIENTE'];
				
				$temp['obs'] 	= $row['OBS'];
				$temp['obs2'] 	= '';
				$temp['motivo'] = $row['MOTIVO'];
				
				$temp['valor'] 	= $row['VLTOTAL'];
				
				$temp['notaE'] = $row['NUMNOTA'];
				$temp['dataE'] = datas::dataMS2D($row['DTENT']);
				
				if($row['NUMNOTAVENDA'] != ''){
					$temp['notaS'] = $row['NUMNOTAVENDA'];
				}else{
					$temp['notaS'] = $row['NUMNOTAVENDA2'];
				}
				if($row['DTSAIDA'] != ''){
					$temp['dataS'] = datas::dataMS2D($row['DTSAIDA']);
				}else{
					$temp['dataS'] = $this->getDataSaida($temp['notaS']);
				}
				
				//$temp['transvenda'] = $row['NUMTRANSVENDA'];
				//$temp['codDev'] = $row['CODDEVOL'];
				
				//$temp['transentrada'] = $row['NUMTRANSENT'];
				//$temp['frete'] = $row['VLFRETE'];
				//$temp['outras'] = $row['VLOUTRAS'];
				//$temp['total'] = $row['VLTOTNF'];
				
				$this->_dados[$super][$erc][] = $temp;
			}
		}
	}
	
	private function getDataSaida($nota){
		$ret = '';
		if(!empty($nota)){
			$sql = "select DTSAIDA from pcnfsaid where numnota = $nota";
			$rows = query4($sql);
			if(isset($rows[0][0])){
				$ret = datas::dataMS2D($rows[0]['DTSAIDA']);
			}
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
}