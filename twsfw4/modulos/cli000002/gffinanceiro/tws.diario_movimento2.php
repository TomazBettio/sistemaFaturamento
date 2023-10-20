<?php
/*
 * Data Criacao: 06/12/2017
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Resumo Diario de Movimentação de Cobranca
 * 
 * Alterações: 
 * 				17/12/2020 - Thiel - Ajustes conforme solicitação da Daniele (retirar coluna Itau e mudar de lugar o Crédito Manual Sicredi
 * 				31/10/2022 - Mudança do Banco do Brasil (001) para Sicredi (748)
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class diario_movimento2{
	var $_relatorio;
	var $_tabela;
	
	var $funcoes_publicas = array(
			'index' 		=> true,
			'gravar' 		=> true,
			'bloquear'		=> true,
			'limpar'		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	// Dados
	var $_dados;
	
	//Titulos Linhas
	var $_titulosBlocos;
	
	//Bancos
	var $_bancos;
	
	//Totais
	var $_totais;
	
	//Filtro
	var $_filtro;
	
	//formularios ocultps
	var $_hidden;
	
	//Indices dos Bancos
	var $_bancoInd;
	
	//Indica a linha com o saldo final de um bloco
	var $_linhaSaldoFinal;
	
	//Banco 0
	private $_banco0 = 748;
	
	//Indica se o dia já foi bloqueado
	private $_bloqueado;
	
	//Link do PDF
	private $_linkPDF;
	
	//Arquivo PDF
	private $_arquivoPDF;
	
	//Contas bebito
	private $_contas_debito;
	
	public function __construct(){
//		set_time_limit(0);
		
		$this->_hidden = '';
		$this->_bancoInd = array($this->_banco0 => 0, 41 => 1, 237 => 2); //, 341 => 3);
		$this->_linhaSaldoFinal[1] = 8;
		$this->_linhaSaldoFinal[4] = 4;
		$this->_linhaSaldoFinal[5] = 4;
		
		$this->_programa = 'diarioMovimento';
		$param = [];
		$param['botaoTexto'] = 'Enviar';
		$param['imprimePainel'] = false;
		$param['tamanho'] = 12;
		$param['colunas'] = 1;
		$param['layout'] = 'horizontal';
		$this->_filtro = new formFiltro01($this->_programa, $param);
		
		$this->_bloqueado = false;
		
		$this->_contas_debito = '200015';
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data'		, 'variavel' => 'DATA'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
	
	public function index($botoes = true, $bloqueado = null){
		global $config;
		$ret = '';
		
		$filtro = $this->_filtro->getFiltro();
		
		$dia = $filtro['DATA'];
		if($dia == ''){
			$dia = date('Ymd');
		}
		$this->_linkPDF = $config['tempURL'].'Movimento_Financeiro_'.datas::dataS2D($dia,4,'-').'.pdf';
//echo $this->_linkPDF."<br>\n";
		$this->_arquivoPDF = $config['tempPach'].'Movimento_Financeiro_'.datas::dataS2D($dia,4,'-').'.pdf';
//echo $this->_arquivoPDF."<br>\n";
		if(!is_bool($bloqueado)){
			$this->_bloqueado = $this->verificaBloqueado($dia);
		}else{
			$this->_bloqueado = $bloqueado; 
		}
		
		$botoes_titulo = [];
		
		if($botoes){
			$botao = [];
			$botao["onclick"]= "$('#formFiltro').toggle();";
			$botao["texto"]	= "Par&acirc;metros";
			$botao["id"] = "bt_form";
			//$botoes[] = $botao;
			//addBreadcrumbPrincipal($botao);
			$botoes_titulo[] = $botao;
			
			//PDF
			$botao = [];
			$botao['onclick']= 'window.open(\''.$this->_linkPDF.'\')';
			$botao['texto']	= 'Download PDF';
			$botao['id'] = 'bt_pdf';
			//addBreadcrumbPrincipal($botao);
			$botoes_titulo[] = $botao;
			
			if(!$this->_bloqueado){
				/*
				 * Se não tiver bloqueado pode gravar ou bloquear ou limpar os saldos
				 */
				$botao = [];
				$botao['onclick']= "$('#formMovimento').submit();";
				$botao['texto']	= 'Gravar';
				$botao['id'] = 'bt_gravar';
				//addBreadcrumbPrincipal($botao);
				$botoes_titulo[] = $botao;
				
				$this->addScriptBloqueio();
				
				$botao = [];
				$botao['onclick']= "confirmaBloqueio();";
				$botao['texto']	= 'Bloquear';
				$botao['cor']	= 'warning';
				$botao['id'] = 'bt_bloquear';
				//addBreadcrumbPrincipal($botao);
				$botoes_titulo[] = $botao;

				$this->addScriptLimparSaldos();
				
				$botao = [];
				$botao['onclick']= "confirmaLimpezaSaldos();";
				$botao['texto']	= 'Limpar Saldos';
				$botao['cor']	= 'warning';
				$botao['id'] = 'bt_limpar';
				//addBreadcrumbPrincipal($botao);
				$botoes_titulo[] = $botao;
			
			}else{
				/*
				 * Se estiver bloqueado pode gerar PDF
				 *
				$botao = [];
				$botao['onclick']= 'window.open(\''.$this->_linkPDF.'\')';
				$botao['texto']	= 'Download PDF';
				$botao['id'] = 'bt_pdf';
				addBreadcrumbPrincipal($botao);
				*/
			}
		}
		
		$param = [];
		$param['titulo'] = 'Resumo - Diário de Movimentação de Cobrança - '.datas::dataS2D($dia);
		$param['conteudo'] = ''.$this->_filtro;
		$param['botoesTitulo'] = $botoes_titulo;
		$ret = addCard($param);
		
		$this->getTitulos($dia);
		$this->getDados($dia);
		
		$param = [];
		//$param['colunas'] = '';
		$param['corBorda'] = '#000000';
		$param['corFundoTitulo'] = '#3c8dbc';
		$param['corFonteTitulo'] = '#FFF';
		$param['alinhamentoTitulo'] = 'center';
		//$param['fonte'] = 'font-family: Verdana, Geneva, sans-serif;';
		//$param['tamanhoTD'] = false;
		$this->_tabela = new tabela_gmail01($param);
		$this->_tabela->abreTabela(1200);
		
		$blocos = count($this->_titulosBlocos);
		for($i=0;$i<$blocos;$i++){
			$this->abreTR($i);
			$this->imprimeBloco($i);
		}
		$this->_tabela->fechaTabela();
		
		$conteudo = $this->_tabela;
		$param = [];
		$param['nome']  = 'data';
		$param['valor'] = $dia;
		$conteudo .= formbase01::formHidden($param);
		
		$conteudo .= $this->_hidden;
		
		putAppVar('diaMovimentoDiario', $dia);
		
		$param = [];
		$param['acao'] = $config['raiz'].'index.php?menu=gffinanceiro.diario_movimento2.gravar';
		$param['nome'] = 'formMovimento';
		$ret .= formbase01::form($param, $conteudo);
		//$ret .= $this->_tabela;
		
		//if($this->_bloqueado){
			$this->geraPDF($dia, $botoes);
		//}
		
		return $ret;
	}
	
	public function schedule($param){
		
	}
	
	public function bloquear(){
		$ret = '';
		if(!isset($_POST['data'])){
			return $this->index();
		}
		
		$diaApp = getAppVar('diaMovimentoDiario');
		$dia = $_POST['data'];
		if($dia == $diaApp){
			$ret = $this->gravar(true);
		}
		
		return $ret;
	}
	
	public function limpar(){
		if(!isset($_POST['data'])){
			return $this->index();
		}
		
		$diaApp = getAppVar('diaMovimentoDiario');
		$dia = $_POST['data'];
		if($dia == $diaApp){
			$this->limpaSaldo($dia);
		}
		
		return $this->index();
	}
	
	public function gravar($bloqueado = false){
		if(!isset($_POST['data'])){
			return $this->index();
		}
	
		$diaApp = getAppVar('diaMovimentoDiario');
		$dia = $_POST['data'];
		//$diaAnt = $this->getDiaUtilAnterior($dia);
		if($dia == $diaApp){
//print_r($_POST);		
			$saldos = $_POST['saldo']['valor'];
			$status = $_POST['saldo']['status'];
			$tipo = $_POST['saldo']['tipo'];
			$data = $_POST['saldo']['data'];
			
			foreach ($saldos as $bloco => $saldo){
				foreach ($saldo as $banco => $s){
					if($status[$bloco][$banco] != 'B'){
						$s = $this->ajustaValorDiario($s);
						$dia = $data[$bloco][$banco];
						if($bloqueado){
							$statusG = 'B';
						}else{
							$statusG = $status[$bloco][$banco];
						}
						$this->gravaSaldo($dia, $bloco, $banco, $s, $tipo[$bloco][$banco],$statusG);
					}
				}
			}
			
			//Calcula finais
			$this->index(false);
			
			$saldos = $_POST['saldoFinal']['valor'];
			$status = $_POST['saldoFinal']['status'];
			$tipo = $_POST['saldoFinal']['tipo'];
			$data = $_POST['saldoFinal']['data'];
//print_r($this->_dados);			
			foreach ($saldos as $bloco => $saldo){
				foreach ($saldo as $banco => $s){
					if($status[$bloco][$banco] != 'B'){
//echo "Final - [$bloco][$banco] <br>\n";
						$dia= $data[$bloco][$banco];
						$linhaFinal = $this->_linhaSaldoFinal[$bloco];
						$valor = $this->_dados[$bloco][$linhaFinal][$this->_bancoInd[$banco]];
//echo "[$bloco][$linhaFinal][".$this->_bancoInd[$banco]."]<br>\n";
						if($bloqueado){
							$statusG = 'B';
						}else{
							$statusG = $status[$bloco][$banco];
						}
						$this->gravaSaldo($dia, $bloco, $banco, $valor, $tipo[$bloco][$banco],$statusG);
					}
				}
			}
			
			//Grava Outros
			$outros = isset($_POST['outros']) ? $_POST['outros'] : [];
//print_r($outros);
			$this->atualizaOutros($dia, $outros, $bloqueado);
		}
		return $this->index();
	}
	
	private function geraPDF($dia, $botoes){
		if(!$this->_bloqueado || $botoes === true){
			$this->index(false, true);
		}
		
		$conteudo = '';
		$tipo = array('T', 'V', 'V', 'V', 'V', 'V');
		$posicao = array('E', 'D', 'D', 'D', 'D', 'D');
		$width = array(300, 100, 100, 100, 100);
		$campos = ['titulo', 'banco0', 'banco1', 'banco2', 'total'];
		
		$param = [];
		$param['corBorda'] = '#000000';
		$param['corFundoTitulo'] = '#3c8dbc';
		$param['corFonteTitulo'] = '#FFF';
		
		for($bloco = 0; $bloco < 5; $bloco++){
			$this->_tabela = new tabela_pdf01($param);
			$cab = $this->getTitulosBloco($bloco);
			$this->_tabela->setTabela($campos, $cab, $width, $posicao, $tipo);
			$this->_tabela->setDados($this->getDadosLinha($bloco));
			if($bloco == 4){
				$footer = $this->assinaturaPDF();
				$this->_tabela->setFooter($footer);
			}
			$conteudo .= $this->_tabela;
		}
		
		$paramPDF = [];
		$paramPDF['orientacao'] = 'L';
		$PDF = new pdf_exporta($paramPDF);
		$PDF->setHTML($conteudo);
		//$PDF->setHeader('', 10);
		$PDF->setHeader($this->getHeaderPDF('Movimentação Diária<br>'.datas::dataS2D($dia)), 20);
//echo $conteudo;
		$PDF->grava($this->_arquivoPDF);
	}
	
	private function getHeaderPDF($titulo){
		global $nl, $config;
		$logo = $config['raizS3'].'imagens/logo_gf_rel.jpeg';
		
		$ret = '';
		$titulo = '<h2>'.$titulo.'</h2>'.$nl;

		$largura = 'width="257"';
		$altura = 'height="54"';
		
		$ret .= '<table align="center" cellspacing="0" style="width: 100%; border: 0px; border-collapse: collapse;">'.$nl;
		$ret .= '	<tr>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 30%;border: 0px;"><img src="'.$logo.'" '.$largura.' '.$altura.'></td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 60%;border: 0px;">'.$titulo.'</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 10%;border: 0px;"><b>Emissão</b><br> '.date('d/m/Y').'</td>'.$nl;
		$ret .= '	</tr>'.$nl;
		$ret .= '</table>'.$nl;
		//echo $ret;
		return $ret;
	}
	
	private function assinaturaPDF(){
		global $nl;
		$ret = '';
		
		$ret .= '<br><br><br><br>';
		$ret .= '<table align="center" cellspacing="0" style="width: 100%; border: 0px; border-collapse: collapse;">'.$nl;
		$ret .= '	<tr>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 15%;border: 0px;">&nbsp;</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 20%;border: 0px;"><br><br><br><br>_______________________________</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 30%;border: 0px;">&nbsp;</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 20%;border: 0px;"><br><br><br><br>_______________________________</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 15%;border: 0px;">&nbsp;</td>'.$nl;
		$ret .= '	</tr>'.$nl;
		$ret .= '	<tr>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 15%;border: 0px;">1&nbsp;</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 20%;border: 0px;">Assinatura 1</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 30%;border: 0px;">2&nbsp;</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 20%;border: 0px;">Assinatura 2</td>'.$nl;
		$ret .= '		<td scope="col" align="center" valign="middle" style="width: 15%;border: 0px;">3&nbsp;</td>'.$nl;
		$ret .= '	</tr>'.$nl;
		$ret .= '</table>'.$nl;

		return $ret;
	}
	
	private function getTitulosBloco($bloco){
		$ret = [];
		$ret[] = $this->_titulosBlocos[$bloco][0];
		foreach ($this->_bancos as $banco){
			if($bloco == 0 || $bloco == 3 || $bloco == 4){
				$ret[] = $banco[$bloco];
			}else{
				$ret[] = '';
			}
		}
		if($bloco == 0 || $bloco == 3 || $bloco == 4){
			$ret[] = 'Total';
		}else{
			$ret[] = '';
		}
		return $ret;
	}
	
	private function getDadosLinha($bloco){
		$ret = [];
		foreach ($this->_titulosBlocos[$bloco] as $l => $titulo){
			if($l > 0){
				$temp = [];
				$temp['titulo'] = $titulo;
				for($e=0;$e<count($this->_bancos);$e++){
					$temp['banco'.$e] = $this->_dados[$bloco][$l][$e];
				}
				$temp['total'] = $this->_total[$bloco][$l];
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function calcula($diaIni, $diaFim, $banco, $moeda, $cobranca = '', $atividade = '', $trace = false, $calculo = ''){
		$ret = array('pago' => 0,
					 'desconto' => 0,
					 'juros' => 0,
					 'valor' => 0
					);
		$whereAtividade = '';
		if($atividade != ''){
			if(strpos($atividade, '-') !== false){
				$atividade = str_replace('-', '', $atividade);
				$whereAtividade = "AND NVL(C.CODATV1,0) <> $atividade";
			}else{
				$whereAtividade = "AND C.CODATV1 = $atividade";
			}
		}
	
		if($cobranca == '' && !is_array($cobranca)){
			$whereCobranca = '';
		}elseif(is_array($cobranca)){
			$cobranca = "'".implode("','", $cobranca)."'";
		}else{
			$cobranca = "'".$cobranca."'";
		}
		if($cobranca != ''){
			$whereCobranca = "AND (P.CODCOB IN ( $cobranca )) ";
		}
		$sql = "
				SELECT 
				    P.CODCLI, 
				    P.PREST, 
				    P.DUPLIC, 
				    P.VALOR, 
				    P.DTVENC, 
				    P.CODCOB,                      
				    NVL(P.VPAGO,0) AS VPAGO, 
				    NVL(P.VLDEVOL,0) VLDEVOL, 
				    P.NUMASSOCDNI, 
				    (P.VALOR + NVL(P.TXPERMPREVISTO, 0)) VALORCOMJURPREV, 
				    DECODE(P.CODCOB,'DEVP',0,'DEVT',0,(P.VALOR-NVL(P.VLDEVOL, 0)-NVL(P.VALORDESC,0)+                                                 
				        CASE                                                                                 
				            WHEN P.DTPAG IS NOT NULL THEN                                                       
				            NVL(P.TXPERM, 0)                                                                   
				            ELSE 0                                                                              
				        END                                                                                  
				        -NVL(P.VPAGO,0))) AS VLRDOC,                   
				    CASE                                                                                 
				        WHEN P.DTPAG IS NOT NULL THEN                                                       
				            NVL(P.TXPERM, 0)                                                                   
				        ELSE 0                                                                              
				    END TXPERM, 
				    P.TXPERMPREVREAL,                                                        
				    NVL(P.TXPERMPREVISTO, 0) TXPERMPREVISTO, 
				    P.DTPAG, 
				    P.DTEMISSAO, 
				    P.OPERACAO, 
				    P.DTDESC,                                          
				    P.PERDESC, 
				    NVL(P.VALORDESC,0) VALORDESC, 
				    P.BOLETO, 
				    P.NUMBANCO, 
				    P.NUMAGENCIA, 
				    P.NUMCHEQUE, 
				    P.DTLANCCH, 
				    P.DTBAIXA,               
				    P.CODBAIXA, 
				    P.NOSSONUMBCO, 
				    P.OBS2, 
				    P.ALINEA, 
				    P.NUMTRANS, 
				    P.DTLANCPRORROG,             
				    P.DTVENCORIG, 
				    P.CODBANCO, 
				    P.CODCOBBANCO, 
				    P.NUMCAR, 
				    B.TXJUROS TXCOB,       
				    P.DTBAIXA,  
				    P.CODBANCO, 
				    C.FANTASIA,             
				    P.NUMTRANSVENDA, 
				    B.COBRANCA, 
				    C.CLIENTE, 
				    P.VALORORIG, 
				    P.CODCOBORIG,   
				    A.NOME, 
				    A.AGENCIA, 
				    A.CONTA, 
				    (SELECT NOME FROM PCEMPR WHERE MATRICULA = P.CODFUNCESTORNO) AS FUNCESTORNO,                                  
				    P.OBSFINANC,                                                                          
				    NVL(P.AGRUPADO,'N') AS AGRUPADO,                                                    
				    (SELECT MAX(PCNFSAID.NUMNOTA) FROM PCNFSAID WHERE  PCNFSAID.NUMTRANSVENDAORIGEM = P.NUMTRANSVENDA) AS NUMNOTA,                                                                          
				    P.OBS,                                                                               
				    P.VLRDESPBANCARIAS, 
				    P.VLRDESPCARTORAIS,
				    P.VLROUTROSACRESC,                             
				    NVL(P.VLRTOTDESPESASEJUROS, P.TXPERM) VLRTOTDESPESASEJUROS,                            
				    0 cDIASATRASO,                                                                 
				    0 cVLJUROS,                                                                    
				    0 cVLJUROSDIA,                                                                 
				    0 cVLTOTAL, B.DIASCARENCIA,                                                    
				    0 JUROSDEV, 
				    0 VLJUROS, 
				    0 VALORJUROS, 
				    0 TXJUROS, 
				    0 TXPERMSTR, 
				    0 cTAXAJUROSMES, 
				    NVL(B.PERCMULTA, 0) PERCMULTA, 
				    (CASE 
				        WHEN (P.DTBAIXA IS NOT NULL) THEN 
				            NVL(P.VALORMULTA, 0) 
				        ELSE 
				            (CASE 
				                WHEN ((TRUNC(P.DTVENC) + NVL(B.DIASCARENCIA, 0)) < TRUNC(SYSDATE)) AND 
				                    (NVL(B.PERCMULTA, 0) <> 0) THEN 
				                    (P.VALOR * (NVL(B.PERCMULTA, 0) / 100)) 
				                ELSE 
				                    0 
				            END) 
				    END) VALORMULTA, 
				    ((CASE 
				        WHEN (P.DTBAIXA IS NOT NULL) THEN 
				            NVL(P.VALORMULTA, 0) 
				        ELSE 
				            (CASE 
				                WHEN ((TRUNC(P.DTVENC) + NVL(B.DIASCARENCIA, 0)) < TRUNC(SYSDATE)) AND 
				                    (NVL(B.PERCMULTA, 0) <> 0) THEN 
				                    (P.VALOR * (NVL(B.PERCMULTA, 0) / 100)) 
				                ELSE 
				                    0 
				            END) 
				    END) + (P.VALOR + NVL(P.TXPERMPREVISTO, 0))) VLTOTALJUROSMULTA, 
				    P.DTRECEBIMENTOPREVISTO,                                                       
				    CASE                                                                           
				          WHEN P.DTRECEBIMENTOPREVISTO >= TRUNC(SYSDATE) THEN                         
				            '*'                                                                     
				           ELSE                                                                       
				            ''                                                                      
				    END TITULOCOMDATAPREVISTA,                                                   
				    CASE                                                                           
				          WHEN P.DTRECEBIMENTOPREVISTO >= TRUNC(SYSDATE) THEN                         
				            'Sim'                                                                   
				           ELSE                                                                       
				            'Não'                                                                   
				    END TITULOCOMDATAPREVISTASIMNAO,                                              
				    (P.NUMTRANSVENDA || P.PREST) NUMTRANSVENDAPREST,                                
				    CASE WHEN NVL(P.NUMBANCO, 0) > 0 THEN P.NUMBANCO || '/' || P.NUMAGENCIA || '/' || P.NUMCHEQUE 
				        ELSE '' 
				    END AS BANCARIO, 
				    '                                                    ' AS cNOMEBANCO,
				    '                                                    ' AS cAGENCIA,
				    '                                                    ' AS cCONTA,
				    TO_NUMBER(0) cTXJUROS                                         
				FROM
				    PCPREST P, 
				    PCCOB B, 
				    PCCLIENT C, 
				    PCBANCO A, 
				    PCFILIAL F 
				WHERE 
				    ( P.CODCOB = B.CODCOB) 
				    AND    ( C.CODCLI = P.CODCLI ) 
				    AND    ( P.CODFILIAL = F.CODIGO ) 
				    AND    ( P.CODBANCO = A.CODBANCO(+) ) 
				    AND P.DTPAG BETWEEN to_date('$diaIni','YYYYMMDD') AND to_date('$diaFim','YYYYMMDD') 
				    AND (P.CODBANCO IN ( '$banco' )) 
				    AND (P.CODCOBBANCO IN ( '$moeda' )) 
					AND P.DTESTORNO IS NULL
				    $whereCobranca
				    $whereAtividade
				    AND P.DTPAG IS NOT NULL
				ORDER BY 
				    P.DTVENC, 
				    P.CODCLI
				";
		if($trace){
			echo "\n\n$sql\n\n";
		}
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				if($calculo == ''){
					$ret['pago'] += $row['VPAGO'];
					$ret['valor'] += $row['VALOR'];
					$dif = $row['VPAGO'] - $row['VALOR'];
					if($dif > 0){
						$ret['juros'] += $dif;
					}else{
						$ret['desconto'] += $dif * -1;
					}
				}elseif ($calculo == 'D'){
					$ret['pago'] += $row['VPAGO'];
					$ret['valor'] += $row['VALOR'];
					$ret['desconto'] += $row['VALORDESC'];
					//$ret['juros'] += $row['VLRTOTDESPESASEJUROS'];
					$ret['juros'] += ($row['VPAGO'] + $row['VALORDESC']) - $row['VALOR'];
				}
			}
		}
		return $ret;
	}
	
	private function get8275($dia, $banco, $moeda, $trace = false){
		$ret = ['valor' => 0, 'juros' => 0, 'descontos' => 0, 'creditos'=>0];
		$sql = "
				SELECT 
				    PCPREST.DUPLIC,
				    PCPREST.PREST,
					PCPREST.VALOR,
					PCPREST.VPAGO,
				    VALORDESC,
				    TXPERM,
				    (VPAGO-PCPREST.VALOR) as DIFERENCA
				 FROM PCPREST,PCMOVCR 
				 WHERE PCPREST.NUMTRANS = PCMOVCR.NUMTRANS(+) 
--para compensar erros ocorridos em 10/02/23
AND PCPREST.VPAGO <> 0
				   AND PCPREST.CODBANCO = $banco
				   AND PCMOVCR.DTCOMPENSACAO BETWEEN TO_DATE('$dia','YYYYMMDD') AND TO_DATE('$dia','YYYYMMDD') 
				   AND CODCOBBANCO = '$moeda' 
				   AND PCPREST.VALOR<>0 
				   AND PCMOVCR.DTESTORNO IS NULL -- Estorno de conciliação
				   --AND PCPREST.DTESTORNO IS NULL -- Estorno normal
				   --AND PCPREST.VALORESTORNO IS NULL 
		";
		if($trace){
			echo "$sql <br>\n";
		}
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$ret['valor'] += $row['VALOR'];
				$ret['creditos'] += $row['VPAGO'];
				if($row['DIFERENCA'] > 0){
					$ret['juros'] += $row['DIFERENCA'];
				}else{
					$ret['descontos'] -= $row['DIFERENCA'];
				}
			}
		}
		if($trace){
			print_r($ret);
		}
		
		return $ret;
	}
	
	private function valor645($param, $trace = false){
		$valor = 0;
		
		$diaIni = verificaParametro($param, 'diaIni');
		$diaFim = verificaParametro($param, 'diaFim');
		$banco = verificaParametro($param, 'banco');
		$moeda = verificaParametro($param, 'moeda');
		$contaDeb = verificaParametro($param, 'contaDeb');
		$contaCred = verificaParametro($param, 'contaCred');
		$tipo = verificaParametro($param, 'tipo','tarifa');
				
		$whereDuplicidade = '';
		if($tipo == 'duplic'){
			$whereDuplicidade = "AND UPPER(PCMOVCR.HISTORICO) LIKE '%DUPLICIDADE%'";
		}elseif($tipo == 'verba comercial'){
			$whereDuplicidade = "AND ((UPPER(PCMOVCR.HISTORICO) LIKE '%VERBA%' AND UPPER(PCMOVCR.HISTORICO) LIKE '%COMERCIAL%') OR (UPPER(PCMOVCR.HISTORICO) LIKE '%ESTORNO%' AND UPPER(PCMOVCR.HISTORICO) LIKE '%VERBA%'))";
		}elseif($tipo == 'deposito'){
			$whereDuplicidade = "AND (UPPER(PCMOVCR.HISTORICO) LIKE '%DEP%' OR UPPER(PCMOVCR.HISTORICO) LIKE '%CREDITO%')";
		}elseif($tipo == 'devolvido'){
			$whereDuplicidade = "AND UPPER(PCMOVCR.HISTORICO) LIKE '%DEVOLVIDO%'";
		}elseif ($tipo == 'tarifaBanri'){
			//'DEBITO SERVICO DE COBRANCA';
			$whereDuplicidade = "AND UPPER(PCMOVCR.HISTORICO) LIKE '%ESTORNO%' AND UPPER(PCMOVCR.HISTORICO) NOT LIKE '%TITULO%' AND UPPER(PCMOVCR.HISTORICO) NOT LIKE '%LANCAMENTO%'";
		}elseif ($tipo == 'NFD'){
			$whereDuplicidade = "AND UPPER(PCMOVCR.HISTORICO) LIKE '%BAIXA NFD%'";
		}elseif ($tipo == 'MANUAL'){
			$whereDuplicidade = "AND (UPPER(PCMOVCR.HISTORICO) LIKE '%INCLUSAO MANUAL DE CREDITO CLIENTE%' OR (UPPER(PCMOVCR.HISTORICO) LIKE '%CLIENTE%' AND UPPER(PCMOVCR.HISTORICO) LIKE '%CANCELAMENTO%') )";
		}elseif ($tipo == 'baixa' || strtoupper($tipo) == 'SAIDA'){
			$whereDuplicidade = "AND UPPER(PCMOVCR.HISTORICO) NOT LIKE '%VERBA%' 
								 --AND UPPER(PCMOVCR.HISTORICO) LIKE '%COMERCIAL%' 
								 AND UPPER(PCMOVCR.HISTORICO) NOT LIKE '%ESTORNO%'";
		}elseif($tipo == 'tarifa'){
			$whereDuplicidade = "AND (NOT (UPPER(PCMOVCR.HISTORICO) LIKE '%VERBA%' AND UPPER(PCMOVCR.HISTORICO) LIKE '%COMERCIAL%') AND (UPPER(PCMOVCR.HISTORICO) NOT LIKE '%ESTORNO%' AND UPPER(PCMOVCR.HISTORICO) LIKE '%VERBA%'))";
		}elseif($tipo == 'MKTPLACE'){
			
		}
		
		if(!empty($moeda)){
			$whereDuplicidade .= "\n AND pcmovcr.CODCOB  in ('$moeda')";
		}
		if(!empty($contaDeb) && empty($contaCred)){
			$whereDuplicidade .= "\n AND CODCONTADEB IN ($contaDeb)";
		}
		
		if(!empty($contaCred) && empty($contaDeb)){
			$whereDuplicidade .= "\n AND CODCONTACRED IN ($contaCred)";
		}
		
		if(!empty($contaCred) && !empty($contaDeb)){
			$whereDuplicidade .= "\n AND (CODCONTACRED IN ($contaCred) OR CODCONTADEB IN ($contaDeb))";
		}
		
		if($trace){
			echo "Tipo: $tipo  Where: $whereDuplicidade <br>\n";
		}
		$sql = "SELECT 
					sum(DECODE(PCMOVCR.TIPO,'C',PCMOVCR.VALOR*(-1),0)) VlSaida, 
					sum(DECODE(PCMOVCR.TIPO,'D',PCMOVCR.VALOR,0)) VlEntrada, 
					sum(PCMOVCR.VALOR) Valor 
				FROM 
					PCMOVCR 
				WHERE  1=1 
					AND  pcmovcr.dtcompensacao >= TO_DATE('$diaIni','YYYYMMDD') 
					AND  pcmovcr.dtcompensacao <= TO_DATE('$diaFim','YYYYMMDD')
					AND PCMOVCR.DTESTORNO IS NULL
					AND PCMOVCR.DTESTORNOLANC IS NULL
					AND pcmovcr.CODBANCO in ('$banco')
					$whereDuplicidade
			";
		if($trace){
			echo "$sql <br> \n";
		}
		$rows = query4($sql);
		if(isset($rows[0])){
			if($tipo == 'tarifa'){
				$valor = $rows[0][0] + $rows[0][1];
			}elseif ($tipo == 'duplic'){
				$valor = $rows[0][2];
			}elseif($tipo == 'verba comercial'){
				$valor = $rows[0][1] + $rows[0][0];
			}elseif($tipo == 'deposito' || $tipo == 'devolvido'){
				$valor = [];
				$valor['saida'] = $rows[0][0] * -1;
				$valor['entrada'] = $rows[0][1];
			}elseif ($tipo == 'tarifaBanri'){
				$valor = $rows[0][2];
			}elseif ($tipo == 'MANUAL'){
				$valor = $rows[0][1] + $rows[0][0];
			}elseif (strtoupper($tipo) == 'BAIXA'){
				$valor = $rows[0]['VLENTRADA'];
			}elseif (strtoupper($tipo) == 'SAIDA'){
				$valor = $rows[0][0];
			}elseif ($tipo == 'MKTPLACE'){
				$valor = [];
				$valor['credito'] = $rows[0]['VLSAIDA'] * -1;
				$valor['debito'] = $rows[0]['VLENTRADA'];
			}
		}
		
		if(!is_array($valor) && empty($valor)){
			$valor = 0;
		}
		
		if($trace){
			print_r($rows[0]);
			print_r($valor);
		}
		return $valor;
	}
	
	private function abreTR($bloco){
		$this->_tabela->abreTR(true);
		$this->_tabela->abreTH($this->_titulosBlocos[$bloco][0],1);
		$this->_tabela->fechaTH();
		$quantBancos = count($this->_bancos);
		for($i=0;$i<$quantBancos;$i++){
			$tit = isset($this->_bancos[$i][$bloco]) ? $this->_bancos[$i][$bloco] : '&nbsp;';			
			$this->_tabela->abreTH($tit,1);
			$this->_tabela->fechaTH();
		}
		if($bloco == 0){
			$total = 'TOTAL GERAL';
		}else{
			$total = '';
		}
		$this->_tabela->abreTH($total,1);
		$this->_tabela->fechaTH();
		$this->_tabela->fechaTR();
	}
	
	private function imprimeBloco($b){
//print_r($this->_dados[$b]);
		foreach ($this->_titulosBlocos[$b] as $l => $titulo){
			if($l > 0){
				$this->_tabela->abreTR();
				$this->_tabela->abreTD($titulo,1);
				$quantBancos = count($this->_bancos);
				for($e=0;$e<$quantBancos;$e++){
//echo "$b - $l - $e \n";
					if(!isset($this->_titulosBlocos[$b]['banco'][$l][$e])){
						if(strpos($this->_dados[$b][$l][$e], '>') === false){
							$this->_tabela->abreTD($this->formataValor($this->_dados[$b][$l][$e]),1,'direita');
						}else{
							//e um formulario
							$this->_tabela->abreTD($this->_dados[$b][$l][$e],1,'direita');
						}
					}else{
						$this->_tabela->abreTD($this->_titulosBlocos[$b]['banco'][$l][$e],1,'centro',true);
					}
				}
				$this->_tabela->abreTD($this->formataValor($this->_total[$b][$l]),2,'direita');
				$this->_tabela->fechaTR();
			}
		}
	}
	
	private function getDados($dia){
		foreach ($this->_titulosBlocos as $b => $titulos){
			foreach ($titulos as $l => $titulo){
				if($l > 0){
					$valoresBancos = $this->getValor($b,$l);
					foreach ($valoresBancos as $i => $v){
						if(!isset($this->_dados[$b][$l][$i])){
							$this->_dados[$b][$l][$i] = $v;
						}
					}
				}
			}
		}
		$diaAnt = $this->getDiaUtilAnterior($dia);
		$diaPos = $this->getDiaUtilPosterior($dia);

//Bloco 0 - Cobrança Bancária
		$valores = $this->get8275($dia, $this->_banco0, 'COBS', false);
		// linha 1 - Sicredi -  Valor da Liquidação de Títulos
		$this->_dados[0][1][0] = $valores['valor'];
		// linha 2 - Sicredi - Juros/Encargos/Tarifas Recebidos
		$this->_dados[0][2][0] = $valores['juros'];
		// linha 3 - Sicredi - Descontos Concedidos
		$this->_dados[0][3][0] = $valores['descontos'];
		// linha 4 - Sicredi - Estorno Tarifas Banárias 
		$this->_dados[0][4][0] = '0';
		// linha 5 - Sicredi - Duplicidade Cobrança
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = $this->_banco0;
		$param['moeda'] = '';
		$param['contaDeb'] = $this->_contas_debito;
		$param['tipo'] = 'tarifa';
		$valor = $this->valor645($param, false);
		$this->_dados[0][5][0] = $valor;
		// linha 6 - Sicredi - Valor Líquido Creditado
		$this->_dados[0][6][0] = $valores['valor'] + $valores['juros'] - $valores['descontos'] + $valor;
		
		// linha 1 - Banrisul -  Valor da Liquidação de Títulos
		$valores = $this->get8275($dia, 41, 'COBS');
		$this->_dados[0][1][1] = $valores['valor'];
		// linha 2 - Banrisul - Juros/Encargos/Tarifas Recebidos
		$this->_dados[0][2][1] = $valores['juros'];
		// linha 3 - Banrisul - Descontos Concedidos
		$this->_dados[0][3][1] = $valores['descontos'];
// linha 4 - Banrisul - Estorno Tarifas Banárias
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 41;
		$param['moeda'] = 'COBS';
		$param['contaDeb'] = '';
		$param['tipo'] = 'tarifaBanri';
		$tarifa = $this->valor645($param, false);
		$this->_dados[0][4][1] = $tarifa;
		// linha 5 - Banrisul - Duplicidade Cobrança
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 41;
		$param['moeda'] = 'COBS';
		$param['contaDeb'] = $this->_contas_debito;
		$param['tipo'] = 'tarifa';
		$duplicado = $this->valor645($param, false);
		$this->_dados[0][5][1] = $duplicado;
		// linha 6 - Banrisul - Valor Líquido Creditado
		$this->_dados[0][6][1] = $valores['valor'] + $valores['juros'] - $valores['descontos'] - $tarifa + $duplicado;
		
		
		// linha 1 - Bradesco -  Valor da Liquidação de Títulos
		$valores = $this->get8275($dia, 237, 'COBS');
		$this->_dados[0][1][2] = $valores['valor'];
		// linha 2 - Bradesco - Juros/Encargos/Tarifas Recebidos
		$this->_dados[0][2][2] = $valores['juros'];
		// linha 3 - Bradesco - Descontos Concedidos
		$this->_dados[0][3][2] = $valores['descontos'];
		// linha 4 - Bradesco - Estorno Tarifas Banárias
		$this->_dados[0][4][2] = 0;
		// linha 5 - Bradesco - Duplicidade Cobrança
		$this->_dados[0][5][2] = 0;
		// linha 5 - Bradesco - Valor Líquido Creditado
		$this->_dados[0][6][2] = $valores['valor'] + $valores['juros'] - $valores['descontos'];
		
/*/		
		// linha 1 - Itau -  Valor da Liquidação de Títulos
		$valores = $this->get8275($dia, 341, 'COBS');
		$this->_dados[0][1][3] = $valores['valor'];
		// linha 2 - Itau - Juros/Encargos/Tarifas Recebidos
		$this->_dados[0][2][3] = $valores['juros'];
		// linha 3 - Itau - Descontos Concedidos
		$this->_dados[0][3][3] = $valores['descontos'];
		// linha 4 - Itau - Estorno Tarifas Banárias
		$tarifa = 0;
		$this->_dados[0][4][3] = '';
		// linha 5 - Itau - Duplicidade Cobrança
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 341;
		$param['moeda'] = 'COBS';
		$param['contaDeb'] = $this->_contas_debito;
		$param['tipo'] = 'tarifa';
		$valor = $this->valor645($param, false);
		$this->_dados[0][5][3] = $valor;
		// linha 5 - Itau - Valor Líquido Creditado
		$this->_dados[0][6][3] = $valores['valor'] + $valores['juros'] - $valores['descontos'] - $tarifa + $duplicado;
/*/	
		
		$valores['valor'] = 0;
		$valores['juros'] = 0;
		$valores['descontos'] = 0;
		
//Bloco 1 - Depósitos Bancários (DNI)
		// linha 1 - Sicredi - Saldo Anterior
		$form = $this->getSaldoInicial($dia, 1, $this->_banco0);
		$this->_dados[1][1][0] = $form[0];
		// linha 2 - Sicredi - Crédito na Conta no Dia
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = $this->_banco0;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$valor = $this->valor645($param, false);
		if($valor < 0){
			$valor *= -1;
		}else{
			$valor = 0;
		}
		$this->_dados[1][2][0] = $valor;
		// linha 3 - Sicredi - Valor da Liquidação de Títulos
		$this->_dados[1][3][0] = $valores['valor'];
		// linha 4 - Sicredi - Juros/Encargos/Tarifas recebidos (+)
		$this->_dados[1][4][0] = $valores['juros'];
		// linha 5 - Sicredi - Descontos concedidos (-)
		$this->_dados[1][5][0] = $valores['descontos'];
		// linha 6 - Sicredi - Valor liquido baixado do DNI
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = $this->_banco0;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$valorB = $this->valor645($param, false);
		if($valorB < 0){
			$valorB = 0;
		}
		$this->_dados[1][6][0] = $valorB + $valores['juros'] - $valores['descontos'];
		// linha 7 - Sicredi - Receita c/recup COMPRAS
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = $this->_banco0;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$param['tipo'] = 'verba comercial';
		$valor = $this->valor645($param, false);
		$this->_dados[1][7][0] = $valor;
		// linha 8 - Sicredi - Saldo final
		$this->_dados[1][8][0] = $form[1] + $this->_dados[1][2][0] + $this->_dados[1][3][0] - $this->_dados[1][4][0] - $this->_dados[1][5][0] - $valor;
		$this->gravaFormSaldo($dia, 1, $this->_banco0, $this->_dados[1][8][0], 'FINAL','F');
		
		// linha 1 - Banrisul - Saldo Anterior
		$form = $this->getSaldoInicial($dia,1,41);
		$this->_dados[1][1][1] = $form[0];
		// linha 2 - Banrisul - Crédito na Conta no Dia
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 41;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$param['tipo'] = 'SAIDA';
		$valor = $this->valor645($param, false);
		if($valor < 0){
			$valor *= -1;
		}else{
			$valor = 0;
		}
		$this->_dados[1][2][1] = $valor;
		// linha 3 - Banrisul - Valor da Liquidação de Títulos
		$this->_dados[1][3][1] = $valores['valor'];
		// linha 4 - Banrisul - Juros/Encargos/Tarifas recebidos (+)
		$this->_dados[1][4][1]= $valores['juros'];
		// linha 5 - Banrisul - Descontos concedidos (-)
		$this->_dados[1][5][1] = $valores['descontos'];
		// linha 6 - Banrisul - Valor liquido baixado do DNI
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 41;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$param['tipo'] = 'baixa';
		$valorB = $this->valor645($param, false);
		if($valorB < 0){
			$valorB = 0;
		}
		$this->_dados[1][6][1] = $valorB;
		// linha 7 - Banrisul - Receita c/recup COMPRAS
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 41;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$param['tipo'] = 'verba comercial';
		$verba = $this->valor645($param, false);
		$this->_dados[1][7][1] = $verba;
		// linha 8 - Banrisul - Saldo final
		$this->_dados[1][8][1] = $form[1] + $this->_dados[1][2][1] - $this->_dados[1][6][1] - $verba;
		$this->gravaFormSaldo($dia, 1, 41, $this->_dados[1][8][1], 'FINAL','F');

		
		// linha 1 - Bradesco - Saldo Anterior
		$form = $this->getSaldoInicial($dia,1,237);
		$this->_dados[1][1][2] = $form[0];
		// linha 2 - Bradesco - Crédito na Conta no Dia
		//$valores = $this->get8275($dia, 237, 'DNI', true);
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 237;
		$param['moeda'] = 'DNI';
		$param['tipo'] = 'SAIDA';
		$param['contaDeb'] = '';
		$valor = $this->valor645($param, false);
		if($valor < 0){
			$valor *= -1;
		}else{
			$valor = 0;
		}
		$this->_dados[1][2][2] = $valor;
		// linha 3 - Bradesco - Valor da Liquidação de Títulos
		$this->_dados[1][3][2] = $valores['valor'];
		// linha 4 - Bradesco - Juros/Encargos/Tarifas recebidos (+)
		$this->_dados[1][4][2] = $valores['juros'];
		// linha 5 - Bradesco - Descontos concedidos (-)
		$this->_dados[1][5][2] = $valores['descontos'];
		// linha 6 - Bradesco - Valor liquido baixado do DNI
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 237;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$valorB = $this->valor645($param, false);
		if($valorB < 0){
			$valorB = 0;
		}
		$this->_dados[1][6][2] = $valorB + $valores['juros'] - $valores['descontos'];
		// linha 7 - Bradesco - Receita c/recup COMPRAS
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 237;
		$param['moeda'] = 'DNI';
		$param['contaDeb'] = '';
		$param['tipo'] = 'verba comercial';
		$verba = $this->valor645($param, false);
		$this->_dados[1][7][2] = $verba;
		// linha 8 - Bradesco - Saldo final
		$this->_dados[1][8][2] = $form[1] - $valorB - $verba + $valor;
		$this->gravaFormSaldo($dia, 1, 237, $this->_dados[1][8][2], 'FINAL','F');
		
		// linha 1 - Itau - Saldo Anterior
		//$form = $this->getSaldoInicial($dia, 1, 341);
		$this->_dados[1][1][3] = '';
		// linha 2 - Itau - Crédito na Conta no Dia
		$this->_dados[1][2][3] = '';
		// linha 3 - Itau - Valor da Liquidação de Títulos
		$this->_dados[1][3][3] = '';
		// linha 4 - Itau - Juros/Encargos/Tarifas recebidos (+)
		$this->_dados[1][4][3] = '';
		// linha 5 - Itau - Descontos concedidos (-)
		$this->_dados[1][5][3] = '';
		// linha 6 - Itau - Valor liquido baixado do DNI
		$this->_dados[1][6][3] = '';
		// linha 7 - Itau - Receita c/recup COMPRAS
		$this->_dados[1][7][3] = '';
		// linha 8 - Itau - Saldo final
		$this->_dados[1][8][3] = '';
		//$this->gravaFormSaldo($dia, 1, 1, $this->_dados[1][8][3], 'FINAL','F');
		
		
//Bloco 2 - Depósitos Bancários D
		$bloco = 2;
		$banco = 0;
		$linha = 0;
		// linha 1 - Sicredi - Valor da liquidação de titulo
		$valores = $this->get8275($dia, $this->_banco0, 'D');
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['valor'];
		// linha 2 - Sicredi - Juros/Encargos/Tarifas Recebidas
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['juros'];
		// linha 3 - Sicredi - Descontos Concedidos
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['descontos'];
		// linha 4 - Sicredi - Receitas com Recuperacao
		$linha++;
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = $this->_banco0;
		$param['moeda'] = 'D';
		$param['contaDeb'] = '';
		$param['tipo'] = 'verba comercial';
		$valor = $this->valor645($param, false);
		$this->_dados[$bloco][$linha][$banco] = $valor;
		
		// Credito MKTPLACE
		$linha++;
		
		//Debito MKTPLACE
		$linha++;
		
		
		// linha 5 - Sicredi - Valor Liquido Baixado
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $this->_dados[$bloco][1][$banco] + $this->_dados[$bloco][2][$banco] - $this->_dados[$bloco][3][$banco] + $this->_dados[$bloco][4][$banco];
		
		
		$banco = 1;
		$linha = 0;
		// linha 1 - Banrisul - Valor da liquidação de titulo
		$valores = $this->get8275($dia, 41, 'D', false);
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['valor'];
		// linha 2 - Banrisul - Juros/Encargos/Tarifas Recebidas
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['juros'];
		// linha 3 - Banrisul - Descontos Concedidos
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['descontos'];
		// linha 4 - Banrisul - Receitas com Recuperacao
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 41;
		$param['moeda'] = 'D';
		$param['contaDeb'] = '';
		$param['tipo'] = 'verba comercial';
		$valor = $this->valor645($param, false);
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valor;
		
		// Credito MKTPLACE
		$linha++;
		
		//Debito MKTPLACE
		$linha++;
		
		// linha 5 - Banrisul - Valor Liquido Baixado
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $this->_dados[$bloco][1][$banco] + $this->_dados[$bloco][2][$banco] - $this->_dados[$bloco][3][$banco] + $this->_dados[$bloco][4][$banco];
		
		//DEPOSITO BANCÁRIO D - Bradesco ---------------------------------------------------------------------------------------------------------------------------------------------------------------------
		
		$banco = 2;
		$linha = 0;
		// linha 1 - Bradesco - Valor da liquidação de titulo
		$valores = $this->get8275($dia, 237, 'D', false);
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['valor'];
		// linha 2 - Bradesco - Juros/Encargos/Tarifas Recebidas
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['juros'];
		// linha 3 - Bradesco - Descontos Concedidos
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valores['descontos'];
		// linha 4 - Bradesco - Receitas com Recuperacao
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 237;
		$param['moeda'] = 'D';
		$param['contaDeb'] = '';
		$param['tipo'] = 'verba comercial';
		$valor = $this->valor645($param, false);
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valor;
		
		//Bradesco - Credito MKTPLACE
		$contras = '200052, 200055, 200057, 200059, 200058, 200050,200054, 200051,200053, 200056, 200061';
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 237;
		$param['moeda'] = 'D';
		$param['contaCred'] = $contras;
		$param['contaDeb'] = $contras;
		$param['tipo'] = 'MKTPLACE';
		$valor = $this->valor645($param, false);
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valor['credito'];
		
		//Bradesco - Debito MKTPLACE
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valor['debito'];
		
		// linha 5 - Bradesco - Valor Liquido Baixado
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $this->_dados[$bloco][1][$banco] + $this->_dados[$bloco][2][$banco] - $this->_dados[$bloco][3][$banco] + $this->_dados[$bloco][4][$banco] - $this->_dados[$bloco][5][$banco] + $this->_dados[$bloco][6][$banco];
		
		$banco = 3;
		$linha = 0;
		// linha 1 - Itau - Valor da liquidação de titulo
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 2 - Itau - Juros/Encargos/Tarifas Recebidas
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 3 - Itau - Descontos Concedidos
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 4 - Itau - Receitas com Recuperacao
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 5 - Itau - Valor Liquido Baixado
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		
		
		
//Bloco 3 - Caixa 7 Moeda D
		$bloco = 3;
		$banco = 0;
		$linha = 0;
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = $this->_banco0;
		$param['moeda'] = 'D';
		$param['contaDeb'] = '';
		$param['tipo'] = 'MANUAL';
		$valor = $this->valor645($param, false);
		// linha 1 - Sicredi - Credito Manual
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valor;
		// linha 2 -
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 3 -
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 4 -
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		
		
		$banco = 1;
		$linha = 0;
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 41;
		$param['moeda'] = 'D';
		$param['contaDeb'] = '';
		$param['tipo'] = 'MANUAL';
		$valor = $this->valor645($param, false);
		// linha 1 - Banrisul - Credito Manual
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valor;
		// linha 2 - 
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 3 - 
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 4 - 
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		
		
		$banco = 2;
		$linha = 0;
		$param = [];
		$param['diaIni'] = $dia;
		$param['diaFim'] = $dia;
		$param['banco'] = 237;
		$param['moeda'] = 'D';
		$param['contaDeb'] = '';
		$param['tipo'] = 'MANUAL';
		$valor = $this->valor645($param, false);
		// linha 1 - Bradesco - Credito Manual
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = $valor;
		// linha 2 - 
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 3 - 
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		// linha 4 - 
		$linha++;
		$this->_dados[$bloco][$linha][$banco] = '';
		
		//Totais
		foreach ($this->_dados as $b => $bloco){
			foreach ($bloco as $l => $linha){
				$total = 0;
				foreach ($linha as $valor){
					if(empty($valor)){
						$valor = 0;
					}
					@$total += $valor;
				}
				if($b < 4){
					$this->_total[$b][$l] = $total;
				}else{
					$this->_total[$b][$l] = '';
				}
			}
		}
		
//print_r($this->_total);
	}
	
	private function getValor($bloco, $linha){
		$ret = [];
		$quantBancos = count($this->_bancos);
		for($i=0;$i<$quantBancos;$i++){
			$ret[$i] = 0;
		}
		
		return $ret;
	}
	
	private function getTitulos($dia){
		$diaAnt = $this->getDiaUtilAnterior($dia);
		$this->_titulosBlocos[0][0] = 'COBRANÇA BANCÁRIA (COBS)';
		$this->_titulosBlocos[0][1] = 'Valor da liquidação de títulos';
		$this->_titulosBlocos[0][2] = 'Juros/Encargos/Tarifas recebidos (+)';
		$this->_titulosBlocos[0][3] = 'Descontos concedidos (-)';
		$this->_titulosBlocos[0][4] = 'Estorno Tarifa Bancária';
		$this->_titulosBlocos[0][5] = 'Duplicidade de Cobrança';
		$this->_titulosBlocos[0][6] = 'Valor líquido creditado na conta';
		
		$this->_titulosBlocos[1][0] = 'DEPOSITO BANCÁRIO (DNI)';
		$this->_titulosBlocos[1][1] = 'Saldo anterior';
		$this->_titulosBlocos[1][2] = 'Crédito de DNI do dia';
		$this->_titulosBlocos[1][3] = 'Valor da Liquidação de Títulos';
		$this->_titulosBlocos[1][4] = 'Juros/Encargos/Tarifas recebidos (+)';
		$this->_titulosBlocos[1][5] = 'Descontos concedidos (-)';
		$this->_titulosBlocos[1][6] = 'Valor Líquido Baixado do DNI';
		$this->_titulosBlocos[1][7] = 'Receita c/ Recup COMPRAS (Verba Comercial)';
		$this->_titulosBlocos[1][8] = 'Saldo Final';
		
		$this->_titulosBlocos[2][0] = 'DEPOSITO BANCÁRIO D';
		$this->_titulosBlocos[2][1] = 'Valor da liquidação de títulos';
		$this->_titulosBlocos[2][2] = 'Juros/Encargos/Tarifas recebidos (+)';
		$this->_titulosBlocos[2][3] = 'Descontos concedidos (-)';
		$this->_titulosBlocos[2][4] = 'Receita c/ Recup COMPRAS (Verba Comercial)';
		$this->_titulosBlocos[2][5] = 'Débitos MKTPLACE';
		$this->_titulosBlocos[2][6] = 'Créditos MKTPLACE';
		$this->_titulosBlocos[2][7] = 'Valor Líquido Baixado Moeda D';
		
		$this->_titulosBlocos[3][0] = 'CAIXA 7 MOEDA D';
		$this->_titulosBlocos[3][1] = 'Valor da liquidação de títulos';
		$this->_titulosBlocos[3][2] = 'Juros/Encargos/Tarifas recebidos (+)';
		$this->_titulosBlocos[3][3] = 'Descontos concedidos (-)';
		$this->_titulosBlocos[3][4] = 'Valor Líquido Recebido no Caixa';
		
		//Bloco 8 - Outros (digitável
		$blocoOutros = 4;
		$this->_titulosBlocos[$blocoOutros] = [];
		$this->_titulosBlocos[$blocoOutros][0] = 'OUTROS';
		$outros = $this->getOutros($dia);
		foreach ($outros as $i => $outro){
			$desc = $this->getFormOutros($i, $outro['titulo'],0);
			$val = $this->getFormOutros($i, $outro['valor'],1);
			if(!empty($desc) || !empty($val)){
				$this->_titulosBlocos[$blocoOutros][$i+1] = $desc;
				$this->_dados[$blocoOutros][$i+1][0] = $val;
				$this->_dados[$blocoOutros][$i+1][1] = '';
				$this->_dados[$blocoOutros][$i+1][2] = '';
				$this->_dados[$blocoOutros][$i+1][3] = '';
			}
		}
		
		
		$this->_bancos[0][0] = 'Sicredi';
		$this->_bancos[1][0] = 'Banrisul';
		$this->_bancos[2][0] = 'Bradesco';
		//$this->_bancos[3][0] = 'Itaú';
		
		$this->_bancos[0][$blocoOutros -1] = 'Credito Manual<br>Sicredi';
		$this->_bancos[1][$blocoOutros -1] = 'Credito Manual<br>Banrisul';
		$this->_bancos[2][$blocoOutros -1] = 'Credito Manual<br>Bradesco';

		//Bloco 8 - Outros (digitável
		$this->_bancos[0][$blocoOutros] = 'Valor';
		$this->_bancos[1][$blocoOutros] = '';
		$this->_bancos[2][$blocoOutros] = '';
		//$this->_bancos[3][$blocoOutros] = '';
		
	}

	private function formataValor($valor){
		$ret = '';
		if(!empty($valor) || $valor == '0'){
			$ret = number_format($valor,2,',','.');
		}
		
		return $ret;
	}
	
	private function getDiaUtilAnterior($data){
		$ret = '';
		$ano = substr($data, 0,4);
		$mes = substr($data, 4,2);
		$dia = substr($data, 6,2);
		
		$diaSemana = date('N',mktime(0,0,0,$mes,$dia,$ano));
//echo "$ano - $mes - $dia - $diaSemana <br>\n";
		if($diaSemana == 1){
			//Segunda
			$ret = datas::getDataDias(-3, $data);
		}else{
			$ret = datas::getDataDias(-1, $data);
		}
		
		if($this->verificaFerido($ret)){
			$ret = $this->getDiaUtilAnterior($ret);
		}
		
		return $ret;
	}
	
	private function verificaFerido($data){
		$ret = false;
		$sql = "SELECT * FROM gf_feriados WHERE data = $data";
		$rows = query($sql);
		if(count($rows) > 0){
			$ret = true;
		}
		
		return $ret;
	}
	
	private function getDiaUtilPosterior($data){
		$ret = '';
		$ano = substr($data, 0,4);
		$mes = substr($data, 4,2);
		$dia = substr($data, 6,2);
		
		$diaSemana = date('N',mktime(0,0,0,$mes,$dia,$ano));
//echo "$ano - $mes - $dia - $diaSemana <br>\n";
		if($diaSemana == 5){
			//Sexta
			$ret = datas::getDataDias(3, $data);
		}else{
			$ret = datas::getDataDias(1, $data);
		}
		
		return $ret;
	}
	
	private function getSaldoInicial($dia, $bloco, $banco){
		global $nl;
		$ret = '';
		$valor = 0;
		$status = '';
		$editavel = true;
		
		$sql = "SELECT valor, status FROM gf_mov_fin_diario WHERE data = $dia AND bloco = $bloco AND banco = $banco AND tipo = 'INICIAL' AND status <> '*'";
//echo "$sql <br>\n";
		$rows = query($sql);
		if(count($rows) > 0){
			$valor = $rows[0][0];
			$status = $rows[0][1];
		}else{
			$diaAnt = $this->getDiaUtilAnterior($dia);
			$sql = "SELECT valor, status FROM gf_mov_fin_diario WHERE data = $diaAnt AND bloco = $bloco AND banco = $banco AND tipo = 'FINAL' AND status <> '*'";
//echo "$sql <br>\n";
			$rows = query($sql);
			if(count($rows) > 0){
				$valor = $rows[0][0];
				$status = $rows[0][1];
			}
		}
		
		$param = [];
		$param['nome'] = "saldo[valor][$bloco][$banco]";
		$param['style'] = "text-align: right";
		$param['valor'] = $valor;
		$param['maxtamanho'] = 15;
		$param['tamanho'] = 15;
		$param['mascara'] = 'V';
		if($status == 'B'){
			$editavel = false;
		}
		
		if($this->_bloqueado){
			$ret = $valor;
		}else{
			$ret .= formbase01::formTexto($param, $editavel).$nl;

			$param = [];
			$param['nome']  = "saldo[status][$bloco][$banco]";
			$param['valor'] = $status;
			$ret .= formbase01::formHidden($param).$nl;
			
			$param = [];
			$param['nome']  = "saldo[tipo][$bloco][$banco]";
			$param['valor'] = 'INICIAL';
			$ret .= formbase01::formHidden($param).$nl;
			
			$param = [];
			$param['nome']  = "saldo[data][$bloco][$banco]";
			$param['valor'] = $dia;
			$ret .= formbase01::formHidden($param).$nl;
		}
		
		$retorno[0] = $ret;
		$retorno[1] = $valor;
		
		return $retorno;
	}
	
	private function getOutros($data){
		$ret = [];
		$sql = "SELECT * FROM gf_mov_fin_diario_outros WHERE data = $data AND status <> '*' ORDER BY linha";
//echo "$sql \n";
		$rows = query($sql);
		for($i=0;$i<10;$i++){
			if(isset($rows[$i])){
				$ret[$i]['titulo'] = $rows[$i]['titulo'];
				$ret[$i]['valor'] = $rows[$i]['valor'];
			}else{
				$ret[$i]['titulo'] = '';
				$ret[$i]['valor'] = '';
			}
		}
//print_r($ret);
		return $ret;
	}
	
	private function limpaSaldo($dia){
		$sql = "UPDATE gf_mov_fin_diario SET status = '*' WHERE data = $dia";
//echo "$sql <br>\n";
		query($sql);
	}
	
	private function gravaSaldo($dia, $bloco, $banco, $saldo, $tipo,$status){
		$sql = "UPDATE gf_mov_fin_diario SET status = '*' WHERE data = $dia AND bloco = $bloco AND banco = $banco AND tipo = '$tipo'";
//echo "$sql <br>\n";
		query($sql);
		
		$sql = "INSERT INTO gf_mov_fin_diario (data,bloco,banco,tipo,valor,status,user) VALUES ($dia, $bloco, $banco, '$tipo', $saldo, '$status',  '".getUsuario()."')";
//echo "$sql <br>\n";
		query($sql);
	}
	
	private function gravaFormSaldo($dia, $bloco, $banco, $saldo, $tipo,$status){
		global $nl;
//echo "$dia, $bloco, $banco, $saldo, $tipo,$status <br>\n";
		$ret ='';
		
		$param = [];
		$param['nome']  = "saldoFinal[valor][$bloco][$banco]";
		$param['valor'] = $saldo;
		$ret .= formbase01::formHidden($param).$nl;
		
		$param = [];
		$param['nome']  = "saldoFinal[status][$bloco][$banco]";
		$param['valor'] = $status;
		$ret .= formbase01::formHidden($param).$nl;
		
		$param = [];
		$param['nome']  = "saldoFinal[tipo][$bloco][$banco]";
		$param['valor'] = $tipo;
		$ret .= formbase01::formHidden($param).$nl;
		
		$param = [];
		$param['nome']  = "saldoFinal[data][$bloco][$banco]";
		$param['valor'] = $dia;
		$ret .= formbase01::formHidden($param).$nl;
		
		$this->_hidden .= $ret;
	}
	
	private function ajustaValorDiario($valor){
		$tam = strlen(''.$valor) - 1;
		
		if(substr($valor, $tam , 1) == ',' || substr($valor, $tam -1 , 1) == ',' || substr($valor, $tam -2 , 1) == ','){
			$valor = str_replace('.', '', $valor);
			$valor = str_replace(',', '.', $valor);
		}
		
		return $valor;
	}
	
	private function getFormOutros($linha, $valor, $tipo){
		global $nl;
		$param = [];
		$param['nome'] = "outros[$tipo][$linha]";
		$param['style'] = $tipo == 1 ? "text-align: right" : '';
		$param['valor'] = $valor;
		$param['maxtamanho'] = $tipo == 1 ? 15 : 100;
		$param['tamanho'] = $tipo == 1 ? 15 : 70;
		$param['mascara'] = $tipo == 1 ? 'V' : '';
		
		if($this->_bloqueado){
			$ret = $param['valor'];
		}else{
			$ret = formbase01::formTexto($param).$nl;
		}
		
		
		return $ret;
	}
	
	private function atualizaOutros($dia, $outros, $bloqueado = false){
		if(count($outros[0]) != 10){
			return;
		}
		
		$status = $bloqueado ? 'B' : ' ';
		
		$sql = "UPDATE gf_mov_fin_diario_outros SET status = '*' WHERE data = $dia";
		query($sql);
//print_r($outros);		
		for($i=0;$i<count($outros[0]);$i++){
			if($outros[0][$i] != '' && $outros[1][$i] != ''){
				$titulo = $outros[0][$i];
				$valor = $this->ajustaValorDiario($outros[1][$i]);			
				$sql = "INSERT INTO gf_mov_fin_diario_outros (data,linha,titulo,valor,status,user) VALUES ($dia, $i, '$titulo', $valor, '$status',  '".getUsuario()."')";
				query($sql, false);
			}
		}
	}

	
	private function verificaBloqueado($dia){
		$ret = false;
		
		$sql = "SELECT status FROM gf_mov_fin_diario WHERE data = '$dia' AND status <> '*'";
//echo "$sql <br>\n";
		$rows = query($sql);
//print_r($rows);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				if($row['status'] == 'B'){
					$ret = true;
//echo "true<br>\n";
				}
			}
		}
		
		return $ret;
	}
	
	private function addScriptBloqueio(){
		$js = "
				function confirmaBloqueio(){
					if (confirm('Confirma o bloqueio deste dia?')){
						$('#formMovimento').attr('action','".getLink()."bloquear');
						$('#formMovimento').submit();
					}
				}
			";
		addPortaljavaScript($js);
	}
	
	private function addScriptLimparSaldos(){
		$js = "
			function confirmaLimpezaSaldos(){
				if (confirm('Confirma limpeza dos saldos deste dia?')){
					$('#formMovimento').attr('action','".getLink()."limpar');
					$('#formMovimento').submit();
				}
			}
		";
		addPortaljavaScript($js);
	}
}