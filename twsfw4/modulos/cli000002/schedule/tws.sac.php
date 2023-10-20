<?php
/*
* Data Cria��o: 14/07/2014 - 16:10:48
* Autor: Thiel
*
* Arquivo: tws.sac.inc.php
* 
* Modificações:
*   22/07/2019 - Emanuel - Migração para a intranet2
*   27/08/2020 - Thiel - Ajuste para origem WEB de pedidos
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class sac{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	//Sac ERC
	var $_sacRca;
	var $_sacSuper;
	
	//Emails
	var $_rca;
	var $_super;
	
	// Nome do Programa
	var $_programa = '';
	
	var $_teste;
	
	var $_excel;
	
	var $_cab = array('Manifesto','Cliente','Contato','Data Abertura','Prazo1','Prazo2','Prazo3','Motivo','Manifestacao','Providencia','Solucao','Observacao','Situacao','ERC','Supervisor','Origem');
	var $_campos = array('nr', 'cliente', 'contato', 'data', 'p1', 'p2', 'p3', 'motivo', 'manifestacao', 'providencia', 'solucao', 'observacao', 'situacao', 'rca', 'super', 'origem');

	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;

		$this->_programa = '000002.sac';

		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data At�'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
	
	function index(){
	}
	
	function schedule($param){
	    
	    global $config;
	    $arquivo = $config['temp'].'sac.xls';
	    $anexos = array();
	    $anexos[0] = $arquivo;
	    $data = date('d/m/Y');
	    $dados = $this->getDados();
	    
	    $param = str_replace(',',';',$param);
	    
	    
	    $this->_rca = getListaEmailGF('rca');
	    $this->_super = getListaEmailGF('supervisor');
	    
	    
	    
	    foreach ($this->_rca as $rca){
	        if(isset($this->_sacRca[$rca['rca']]) && count($this->_sacRca[$rca['rca']]) > 0){
	            $texto = $this->impTabela($this->_sacRca[$rca['rca']],$data);
	            $this->_excel = new excel02($arquivo);
	            $this->_excel->setDados($this->_cab, $this->_sacRca[$rca['rca']], $this->_campos);
	            $this->_excel->grava();
	            if(!$this->_teste){
	               //enviaEmail($rca['email'], "SAC - Pendentes. Data: ".$data,$texto,$anexos);
	            	agendaEmailAntigo('', '08:00', $this->_programa, $rca['email'], "SAC - Pendentes. Data: ".$data,$texto,$anexos);
	               log::gravaLog("SACpendentes", "Enviado email para: ".$rca['email']);
	            }else{
	            	enviaEmailAntigo('suporte@thielws.com.br', "SAC - RCA - Pendentes. Data: ".$data,$texto,$anexos);
	            }
	        }
	    }
	        
	    foreach ($this->_super as $super){
	        if(isset($this->_sacSuper[$super['super']]) && count($this->_sacSuper[$super['super']]) > 0){
	            $texto = $this->impTabela($this->_sacSuper[$super['super']],$data);
	            $this->_excel = new excel02($arquivo);
	            $this->_excel->setDados($this->_cab, $this->_sacSuper[$super['super']], $this->_campos);
	            $this->_excel->grava();
	            if(!$this->_teste){
	               //enviaEmail($super['email'], "SAC - Pendentes. Data: ".$data,$texto,$anexos);
	            	agendaEmailAntigo('', '08:00', $this->_programa, $super['email'], "SAC - Pendentes. Data: ".$data,$texto,$anexos);
	               log::gravaLog("SACpendentes", "Enviado email para: ".$super['email']);
	            }else{
	            	enviaEmailAntigo('suporte@thielws.com.br', "SAC - Super - Pendentes. Data: ".$data,$texto,$anexos);
	            }
	        }
	    }
	    
	    
	    
	    if(count($dados) > 0 && !empty($param)){
	        $texto = $this->impTabela($dados,$data);
	        $this->_excel = new excel02($arquivo);
	        $this->_excel->setDados($this->_cab, $dados, $this->_campos);
	        $this->_excel->grava();
	        if(!$this->_teste){
	            //enviaEmail($param, "SAC - Pendentes. Data: ".$data,$texto,$anexos);
	        	agendaEmailAntigo('', '08:00', $this->_programa, $param, "SAC - Pendentes. Data: ".$data,$texto,$anexos);
	            log::gravaLog("SACpendentes", "Enviado email para: ".$param);
	        }else{
	        	enviaEmailAntigo('suporte@thielws.com.br', "SAC - Geral - Pendentes. Data: ".$data,$texto,$anexos);
	        }
	    }
	}

	function getExcel($dados){
		global $config;
		$arquivo = $config["base"].$config["site"]["tempPath"].'sac.xls';
		//$excel = new ExcelExport($arquivo);
		$excel = '';
		$cab = array('Manifesto','Cliente','Contato','Data Abertura','Prazo1','Prazo2','Prazo3','Motivo','Manifestacao','Providencia','Solucao','Observacao','Situacao','ERC','Supervisor');
		$excel->setDados($cab, $dados);
		$excel->grava();
		$anexos[] = $arquivo;
		
		return $anexos;
	}

	function getDados(){
		$ret = array();
		
		$sql = " 
  SELECT PCMANIF.NUMMANIF,
        PCCLIENT.CODCLI,
        PCCLIENT.CLIENTE,
         PCMANIF.CONTATO,
         to_char(PCMANIF.DTAB,'DD/MM/yyyy') DATA,
         PCMANASSUNTO.PRAZO1,
         PCMANASSUNTO.PRAZO2,
         PCMANASSUNTO.PRAZO3,
         CONVERT( PCMANIF.MANIFESTACAO,'WE8ISO8859P1','WE8MSWIN1252'),
         PCMANIF.PROVIDENCIA,
         PCMANIF.SOLUCAO,
         PCMANIF.OBSERVACAO,
         PCMANIF.SITUACAO ,
         PCEMPR.MATRICULA,
         PCEMPR.NOME,
         pcpedc.codusur,
         (select pcusuari.nome from pcusuari where pcusuari.codusur = pcpedc.codusur) ERC_NOME,
         (select pcsuperv.nome from pcsuperv where codsupervisor = (select pcusuari.codsupervisor from pcusuari where pcusuari.codusur = pcpedc.codusur)) Supervisor,
         pcusuari.codsupervisor,
         PCMANASSUNTO.ASSUNTO,
         CASE 
            WHEN PCPEDC.origemped = 'W' THEN 'WEB'
			WHEN PCPEDC.origemped = 'B' THEN 'BALCAO'
            WHEN PCPEDC.origemped = 'C' THEN 'CALL'
            WHEN PCPEDC.origemped = 'T' THEN 'TLMKT'
            WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv IS NULL THEN 'FV'
            WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'OL' THEN 'OL'
            WHEN PCPEDC.origemped = 'F' AND PCPEDC.tipofv = 'PE' THEN 'PE'
         END ORIGEM,
         PCMANIF.numnota 
  FROM PCMANIF 
        LEFT JOIN PCMANASSUNTO 
            ON PCMANIF.CODASSUNTO = PCMANASSUNTO.CODASSUNTO 
        LEFT JOIN PCCLIENT
            ON PCMANIF.CODCLI = PCCLIENT.CODCLI
        LEFT JOIN PCEMPR
            ON PCEMPR.MATRICULA = PCMANIF.CODFUNCDESTINATARIO 
        LEFT JOIN pcusuari
            ON PCCLIENT.CODUSUR1 = pcusuari.codusur
        LEFT JOIN PCSUPERV
            ON pcsuperv.codsupervisor = pcusuari.codsupervisor
        LEFT JOIN PCPEDC
            ON PCPEDC.numnota = PCMANIF.numnota AND pcpedc.dtcancel IS NULL
  WHERE 
	PCMANIF.SITUACAO IN ('PE', 'P','E','EA')
	AND PCMANIF.DTAB >= SYSDATE -1
  ORDER BY PCMANIF.NUMMANIF, PCMANIF.NUMSEQ
		";
		$rows = query4($sql);
		
		if(count($rows) > 0){
			$i = 0;
			log::gravaLog("SACpendentes", "Chamados encontratos: ".count($rows));
			foreach($rows as $row){
				$ret[$i]['nr'] 		= $row[0];
				$temp1 	= $row[1] <> '' ? $row[1] : $row[13];
				$temp2 	= $row[2] <> '' ? $row[2] : $row[14];
				$ret[$i]['cliente']	= $temp1.'-'.$temp2;
				$ret[$i]['contato'] = $row[3];
				$ret[$i]['data'] 	= $row[4];
				$ret[$i]['p1'] 		= $row[5];
				$ret[$i]['p2'] 		= $row[6];
				$ret[$i]['p3'] 		= $row[7];
				$ret[$i]['motivo'] 	= $row[19];
				$ret[$i]['manifestacao']= htmlentities($row[8]);
				$ret[$i]['providencia'] = htmlentities($row[9]);
				$ret[$i]['solucao'] 	= htmlentities($row[10]);
				$ret[$i]['observacao'] 	= htmlentities($row[11]);
				switch ($row[12]) {
					case 'PE':
						$ret[$i]['situacao'] = 'Pendente';
						break;
					case 'P':
						$ret[$i]['situacao'] = 'Pendente';
						break;
					case 'EA':
						$ret[$i]['situacao'] = 'Em Andamento';
						break;
					case 'E':
						$ret[$i]['situacao'] = 'Em Andamento';
						break;
				}
				$ret[$i]['rca'] 	= $row[15].'-'.$row[16];
				$ret[$i]['super']	= $row[17];
				$ret[$i]['origem'] = $row[20];
				
				//$ret[$i][''] = $row[];
				
				$e = isset($this->_sacRca[$row[15]]) ? count($this->_sacRca[$row[15]]) : 0;
				$this->_sacRca[$row[15]][$e] = $ret[$i];
				
				$e = isset($this->_sacSuper[$row[18]]) ? count($this->_sacSuper[$row[18]]) : 0;
				$this->_sacSuper[$row[18]][$e] = $ret[$i];
				
				$i++;
			}
		}
		
		return $ret;	
	}
	
	function impTabela($sac, $data){
		global $nl;
		$ret = '';
		
		$ret .= '<table width="1000" border="0" align="center" cellpadding="5" cellspacing="0" rules="all" style="border: 1px solid #063; border-collapse: collapse;">'.$nl;
		$ret .= '<th colspan="5" scope="col" style="border-top-width: 0px; border-right-width: 0px; border-bottom-width: 1px; border-left-width: 0px; border-top-style: none; border-right-style: none; border-bottom-style: solid; border-left-style: none; border-top-color: #063; border-right-color: #063; border-bottom-color: #FFF; border-left-color: #063;">SAC - Pendentes. Data: '.$data.'</th>'.$nl;
		$ret .= '</tr>'.$nl;
		$ret .= '<tr class="tab_tit2" style="font-family: Verdana, Geneva, sans-serif;	font-size: 14px; font-weight: bold;	color: #FFF; background-color: #063; text-align: center;	border: 1px solid #063;">'.$nl;
		$ret .= '<th scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Manif.</th>'.$nl;
		$ret .= '<th scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Cliente</th>'.$nl;
		$ret .= '<th scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">ERC</th>'.$nl;
		$ret .= '<th scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Regiao</th>'.$nl;
		$ret .= '<th scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Contato</th>'.$nl;
		$ret .= '<th scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Data</th>'.$nl;
		$ret .= '<th scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Situacao</th>'.$nl;
		$ret .= '</tr>'.$nl;
		
		foreach ($sac as $s){
		
			$ret .= '<tr>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>'.$s['nr'].'</strong></td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>'.$s['cliente'].'</strong></td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>'.$s['rca'].'</strong></td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>'.$s['super'].'</strong></td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>'.$s['contato'].'</strong></td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>'.$s['data'].'</strong></td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>'.$s['situacao'].'</strong></td>'.$nl;
			$ret .= '</tr>'.$nl;

			$ret .= '<tr>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;">Motivo</td>'.$nl;
			$ret .= '<td colspan="4" class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 10px; text-align: left; border: 1px solid #063;">'.$s['motivo'].'</td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;"><strong>Origem:</strong></td>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 10px; text-align: left; border: 1px solid #063;">'.$s['origem'].'</td>'.$nl;
			$ret .= '</tr>'.$nl;
			
			$ret .= '<tr>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;">Manifesta&ccedil;&atilde;o</td>'.$nl;
			$ret .= '<td colspan="6" class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 10px; text-align: left; border: 1px solid #063;">'.$s['manifestacao'].'</td>'.$nl;
			$ret .= '</tr>'.$nl;

			$ret .= '<tr>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;">Provid&ecirc;ncia</td>'.$nl;
			$ret .= '<td colspan="6" class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 10px; text-align: left; border: 1px solid #063;">'.$s['providencia'].'</td>'.$nl;
			$ret .= '</tr>'.$nl;

			$ret .= '<tr>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 12px; text-align: left; border: 1px solid #063;">Solu&ccedil;&atilde;o</td>'.$nl;
			$ret .= '<td colspan="6" class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 10px; text-align: left; border: 1px solid #063;">'.$s['solucao'].'</td>'.$nl;
			$ret .= '</tr>'.$nl;

			$ret .= '<tr>'.$nl;
			$ret .= '<td class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 12px; text-align: left; border: 1px solid #063;">Observa&ccedil;&atilde;o</td>'.$nl;
			$ret .= '<td colspan="6" class="tab_item3"  style="font-family: Verdana, Geneva, sans-serif;	font-size: 10px; text-align: left; border: 1px solid #063;">'.$s['observacao'].'</td>'.$nl;
			$ret .= '</tr>'.$nl;
		}
		$ret .= '</table>'.$nl;		
		
		return $ret;
	}
}