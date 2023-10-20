<?php
/*
* Data Criação: 15/01/2016 - 15:16:29
* Autor: Thiel
*
* Arquivo: tws.comprometimento.inc.php
* 
* Relatorio automatico semanal que indica o comprometimento de 70% do limite do cliente
* Solicitante: Renata
* 
* Alterções:
*            19/11/2018 - Emanuel - Migração para intranet2
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class comprometimento{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';

	// Classe relatorio
	var $_relatorio;
	
	//Indica se é teste
	var $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;

		$this->_programa = '000002.comprometimento';
		
		$param = [];
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$this->_relatorio->addColuna(array('campo' => 'coord'	, 'etiqueta' => 'Regiao'		, 'tipo' => 'T', 'width' =>  100, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'rca'		, 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' =>  150, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' =>  80,  'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Razao Social'	, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'cnpj'	, 'etiqueta' => 'CNPJ'			, 'tipo' => 'T', 'width' =>  120, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'limite'	, 'etiqueta' => 'Limite'		, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'saldo'	, 'etiqueta' => 'Saldo'			, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'perc'	, 'etiqueta' => '%'				, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'direita'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
			
	function index(){
		
	}
	
	function schedule($param){
		$param = str_replace(',', ';', $param);
		$emails = explode(',', $param);
		
		$titulo = 'Comprometimento de Credito. Data: '.date('d/m/Y');
		
		$this->_relatorio->setTitulo($titulo);
		$this->_dados = $this->getDados();
		
		$gerentes = getListaEmailGF('supervisor');
		
		foreach ($gerentes as $super){
			$dados = array();
			$i=0;
			foreach ($this->_dados as $dado){
				if($dado['codsup'] == $super['super']){
					$dados[$i]['coord'] 	= $dado['coord'];
					$dados[$i]['rca'] 		= $dado['rca'];
					$dados[$i]['cod'] 		= $dado['cod'];
					$dados[$i]['cliente'] 	= $dado['cliente'];
					$dados[$i]['cnpj'] 		= $dado['cnpj'];
					$dados[$i]['limite'] 	= $dado['limite'];
					$dados[$i]['saldo'] 	= $dado['saldo'];
					$dados[$i]['perc'] 		= $dado['perc'];
					$i++;
				}
			}
			
			if(count($dados) > 0){
				$this->_relatorio->setDados($dados);
				$this->_relatorio->setAuto(true);
				$this->_relatorio->setToExcel(true,'comprometimento_credito_'.date('d.m.Y'));
				if(!$this->_teste){
				    $this->_relatorio->enviaEmail($super['email'],$titulo);
				    log::gravaLog("comprometimento", "Enviado email para: ".$super['email']);
				}
				else {
				    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
				}
			}
		}

		$dados = array();
		$i=0;
		foreach ($this->_dados as $dado){
			$dados[$i]['coord'] 	= $dado['coord'];
			$dados[$i]['rca'] 		= $dado['rca'];
			$dados[$i]['cod'] 		= $dado['cod'];
			$dados[$i]['cliente'] 	= $dado['cliente'];
			$dados[$i]['cnpj'] 		= $dado['cnpj'];
			$dados[$i]['limite'] 	= $dado['limite'];
			$dados[$i]['saldo'] 	= $dado['saldo'];
			$dados[$i]['perc'] 		= $dado['perc'];
			$i++;
		}
		
			
		$this->_relatorio->setDados($dados);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true,'comprometimento_credito_'.date('d.m.Y'));
		
		if(!$this->_teste){
		    foreach ($emails as $email){
			   $this->_relatorio->enviaEmail($email,$titulo);
		    }
		}
		
		else{
		    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
		}
		
	}
	
	function getDados(){
		$ret = array();
		$sql = "
				SELECT 
				    PCPREST.CODCLI
				    ,pcclient.cliente
				    ,pcclient.codusur1 || ' - ' || pcusuari.nome RCA
				    ,pcsuperv.codsupervisor || ' - ' || pcsuperv.nome SUPERVISOR
				    ,(SELECT pcclient.LIMCRED FROM pcclient WHERE CODCLI = PCPREST.CODCLI) LIMCRED
				    ,NVL(SUM(PCPREST.VALOR),0) VALOR 
					,pcclient.cgcent
					,pcsuperv.codsupervisor CODSUP
					,pcclient.codusur1 CODERC
				FROM 
				    PCPREST
				    ,pcclient
				    ,pcusuari
				    ,pcsuperv 
				WHERE PCPREST.DTPAG IS NULL 
				    AND PCPREST.CODCOB NOT IN ('BNF','BNFT','BNTR','BNFR', 'BNRP')
				    AND PCPREST.CODCLI = pcclient.CODCLI (+)
				    AND pcclient.codusur1 = pcusuari.codusur (+)
				    AND pcusuari.codsupervisor = pcsuperv.codsupervisor (+)
					AND pcclient.codusur1 <> 11
					AND pcclient.dtultcomp > SYSDATE -40
				GROUP BY 
				    PCPREST.CODCLI
				    ,pcclient.cliente
				    ,pcclient.codusur1
				    ,pcusuari.nome
				    ,pcsuperv.codsupervisor
				    ,pcsuperv.nome
					,pcclient.cgcent
				";
		$rows = query4($sql);
		if(count($rows) >0){
			$i=0;
			foreach ($rows as $row){
				$limite = $row['LIMCRED'] == 0 ? 1 : $row['LIMCRED'];
				$perc = ($row['VALOR']/$limite) * 100;
				if($perc >= 85){
					$ret[$i]['coord'] = $row['SUPERVISOR'];
					$ret[$i]['rca'] = $row['RCA'];
					$ret[$i]['cod'] = $row['CODCLI'];
					$ret[$i]['cliente'] = $row['CLIENTE'];
					$ret[$i]['cnpj'] = $row['CGCENT'];
					$ret[$i]['limite'] = $row['LIMCRED'];
					$ret[$i]['saldo'] = $row['VALOR'];
					$ret[$i]['perc'] = $perc;
					$ret[$i]['codsup'] = $row['CODSUP'];
					$ret[$i]['coderc'] = $row['CODERC'];
					$i++;
				}
			}
		}
		
		return $ret;
	}
}