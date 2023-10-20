<?php
/*
 * Data Criacao 23/10/2017
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Novo relatório SIT
 * 
 * Alterções:
 *           02/01/2019 - Emanuel - Migração para intranet2
 *           17/08/2020 - Thiel - Solicitação do Gustavo/Compras
 *           			- Não enviar emails sem "dados"
 *           			- Voltar a controlar pendências de pedidos de compras mesmo se o mesmo não foi liberado 
 *           13/02/2023 - Thiel - Migração Intranet4
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class sit{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
		'email' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	// Parametros dos relatorios
	var $_param;
	
	// Colunas do relatorio
	var $_colunas;
	
	// Desconto entrada
	var $_desconto;
	
	//Indica se é teste
	var $_teste;
	
	//Fornecedores
	private $_fornecedor = array();
	
	//Data inicial e final quando se infica o dia de fechamento
	private $_dataIni = '';
	private $_dataFim = '';
	
	//Quantidade de produtos em uma caixa
	private $_qtCaixa = [];
	
	function __construct(){
		set_time_limit(0);
		
		$this->_programa = 'sit';
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$this->_teste = false;
		
		//getListaRelatoriosSIT() está no final desta classe, somente como função
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Relatorio'		, 'variavel' => 'RELATO', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getListaRelatoriosSIT();', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Mes'			, 'variavel' => 'MES'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getListaMesesSIT();', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Ano'			, 'variavel' => 'ANO'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index($id = 0){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$rel = isset($filtro['RELATO']) ? $filtro['RELATO']: '';
		$mes = isset($filtro['MES']) ? $filtro['MES']: '';
		$ano = isset($filtro['ANO']) ? $filtro['ANO']: '';
		
		$this->_relatorio->setTitulo("Relatorios SIT");
		if(!$this->_relatorio->getPrimeira() && $ano >= 2016 && $ano <= 2030){ 
			
			//Adiciona botão para enviar ao fornecedor
			$this->adicionaBotaoEmail($rel);
			
			$this->defineRelatorios($rel, $ano, $mes);
			
			
			$fechamento = $this->getConfigValor('FECHAMENTO');
			
			if($this->_param['tipo'] == 'E'){
				$dados = $this->getProdutos($rel, $mes, $ano, $fechamento);
			}else{
				$dados = $this->getFaturamento($rel, $mes, $ano, $fechamento);
			}
			
			if(empty($fechamento) || $this->_param['tipo'] != 'E'){
				$this->_relatorio->setTitulo($rel.' - '.$this->_param['nome']);
			}else{
				$this->_relatorio->setTitulo($rel.' - '.$this->_param['nome'].' - '.$this->_dataIni.' a '.$this->_dataFim);
			}
	
			if(count($this->_relatorio->getCampos()) > 0){
				$this->_relatorio->setDados($dados);
				$this->_relatorio->setToExcel(true);
			}
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	
	}

	function schedule($param){
		//Chamado pela pagina de manutenção - teste - envia para emails internos
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		
		if(isset($param['id']) && $param['id'] > 0){
			$id = $param['id'];
			$this->envia($id,true);
		}else{
			$relatorios = $this->getRelatorios();
			$diaSemana = date('N');
			$dia = date('d');		
			foreach ($relatorios as $id => $rel){
				echo "Verificando SIT $id Periodo: >".$rel['periodo']."< <br>\n";
				$envia = false;
				$periodo = $rel['periodo'];
				$diaEspecifico = $rel['dia'];
				switch ($periodo) {
					case '2S':
						echo "Verificando 2S <br>\n";
						if(($diaSemana == 1 || $diaSemana == 4) && !verificaExecucaoSchedule('SIT_'.$id,date('Ymd'))){
							//Segunda e quinta
							$envia = true;
							gravaExecucaoSchedule('SIT_'.$id,date('Ymd'));
						}
						break;
					case 'S':
						echo "Verificando S <br>\n";
						if($diaSemana == 1 && !verificaExecucaoSchedule('SIT_'.$id,date('Ymd'))){
							//Segunda
							$envia = true;
							gravaExecucaoSchedule('SIT_'.$id,date('Ymd'));
						}
						break;
					case '6':
						echo "Verificando 6 <br>\n";
						if($diaSemana == 5 && !verificaExecucaoSchedule('SIT_'.$id,date('Ymd'))){
							//Sexta
							$envia = true;
							gravaExecucaoSchedule('SIT_'.$id,date('Ymd'));
						}
						break;
					case 'M':
						echo "Verificando M <br>\n";
						if(!verificaExecucaoSchedule('SIT_'.$id,date('Ym'))){
							//Inicio do mes
							$envia = true;
							gravaExecucaoSchedule('SIT_'.$id,date('Ym'));
						}
						break;
					case 'Q':
						echo "Verificando Q <br>\n";
						$dia = date('d');
						if($dia < 15 && !verificaExecucaoSchedule('SIT_'.$id,date('Ym').'01')){
							//Inicio e meio do mes
							$envia = true;
							gravaExecucaoSchedule('SIT_'.$id,date('Ym').'01');
						}
						if($dia > 15 && !verificaExecucaoSchedule('SIT_'.$id,date('Ym').'15')){
							//Inicio e meio do mes
							$envia = true;
							gravaExecucaoSchedule('SIT_'.$id,date('Ym').'15');
						}
						break;
					case 'E':
						echo "Verificando E - Dia Específico <br>\n";
						if($dia >= $diaEspecifico && !verificaExecucaoSchedule('SIT_'.$id,date('Ym').$diaEspecifico)){
							//Dia específico
							$envia = true;
							gravaExecucaoSchedule('SIT_'.$id,date('Ym').$diaEspecifico);
						}
						break;
					default:
						break;
				}
				if($envia){
					echo "Enviando SIT $id <br>\n";
					$this->envia($id);
					echo "SIT $id Enviado<br>\n";
				}
			}
		}
	}
	
	function email(){
		$id = $_GET['id'];
		$mes = $_GET['mes'];
		$ano = $_GET['ano'];
		if(!empty($id) && $id > 0){
//echo "$id,false,$mes,$ano <br>\n";die();
			$this->envia($id,false,$mes,$ano);
		}
		$this->_relatorio->setAuto(false);
		$this->_relatorio->setEnviaTabelaEmail(false);
		//$this->adicionaBotaoEmail("");
		return $this->index($id);
	}
	
	private function adicionaBotaoEmail($rel){
		$this->scriptEmail();
		$param = array();
		$param["texto"] = 'Envia email';
		//$param["onclick"] = "window.location.href=('index.php?menu=gfcompras.sit.email&id=".$rel."')";
		$param["onclick"] = "enviaRelatorio();";
		$param["id"] = 'trsnfID';
		$this->_relatorio->addBotao($param);
	}
	
	private function scriptEmail(){
		addPortaljavaScript('function enviaRelatorio(){');
		addPortaljavaScript("	relatorio = $('#sitRELATO option:selected').val();");
		addPortaljavaScript("	mes = $('#sitMES option:selected').val();");
		addPortaljavaScript("	ano = $('#sitANO').val();");
		//addPortaljavaScript("	alert(relatorio + ' - ' + mes + ' - ' + ano)");
		addPortaljavaScript("	window.location.href=('index.php?menu=gfcompras.sit.email&id='+relatorio+'&mes='+mes+'&ano='+ano)");
		addPortaljavaScript('return;');
		addPortaljavaScript('}');
	}
	
	private function envia($id, $teste = false, $mes = '', $ano = ''){
		$this->_relatorio = new relatorio01($this->_programa,"");
		if(empty($mes) && empty($ano)){
			$ano = date('Y');
			$mes = date('m');
			$mes--;
			if($mes == 0){
				$mes = 12;
				$ano--;
			}
			$mes = $mes < 10 ? '0'.$mes : $mes;
		}
		$this->defineRelatorios($id, $ano, $mes, $teste);
//echo "Relatorio $id - $ano - $mes ";die();
		$emailInterno = $this->_param['interno'];
		$emailExterno = $this->_param['externo'];
		
		if(!empty($this->_param['texto'])){
			$this->_relatorio->setTextoEmail($this->_param['texto']);
		}
		
//print_r($this->_param);die();
		$this->_relatorio->setTitulo($this->_param['nome']);
		
		$fechamento = $this->getConfigValor('FECHAMENTO');
		
		$titulo = $this->_param['nome'];
		if($this->_param['tipo']== 'E'){
			$dados = $this->getProdutos($id, $mes, $ano, $fechamento);
		}else{
			$dados = $this->getFaturamento($id, $mes, $ano, $fechamento);
		}
		
		if(empty($fechamento) || $this->_param['tipo'] != 'E'){
			$titulo = $this->_param['nome'];
		}else{
			$titulo = $this->_param['nome'].' - '.$this->_dataIni.' a '.$this->_dataFim;
		}
		
		if(count($this->_relatorio->getCampos()) > 0){
		
			if(count($dados) > 0){
				$this->_relatorio->setEnviaTabelaEmail(false);
				$this->_relatorio->setDados($dados);
				$this->_relatorio->setAuto(true);
				$arquivo = $this->_param['nome'].'_'.date('d.m.Y');
				$arquivo = str_replace(' ', '_', $arquivo);
				$arquivo = str_replace('&', '', $arquivo);
				$arquivo = str_replace(';', '', $arquivo);
				$arquivo = str_replace(',', '', $arquivo);
				$arquivo = str_replace('%', '', $arquivo);
				$arquivo = str_replace('/', '_', $arquivo);
				$arquivo = str_replace('\\', '_', $arquivo);
				
				
				$this->_relatorio->setToExcel(true,$arquivo);
				
				//$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo); //talvez
				
				$email = '';
				if($emailInterno != ''){
					$email .= $emailInterno;
				}
				if($emailExterno!= '' && !$teste && count($dados) > 0){
					if($email != ''){
						$email .= ';';
					}
					$email .= $emailExterno;
				}
		
		
				if($email != ''){
				    $acao = 'Envio Email';
				    if($teste){
				        $acao = 'Envio Email Teste';
				    }
//$email .=';thiel@thielws.com.br';
				    if(!$this->_teste){
					    $this->_relatorio->enviaEmail($email,$titulo); //descomentar
					    $this->gravaLogSIT($id,$acao,$email); //descomentar
				        $this->_relatorio->enviaEmail('suporte@thielws.com.br',"SIT - Prod - .$titulo");
				    }else{
					    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
					    log::gravaLog('sit_erro', 'Enviado email teste - suporte@thielws.com.br - '.$titulo);
					}
				}else{
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
					log::gravaLog('sit_erro', 'Enviado email teste - sem email indicado- suporte@thielws.com.br - '.$titulo);
				}
			}else{
				log::gravaLog('sit_erro', 'Relatório sem DADOS - '.$id);
			}
		}else{
			log::gravaLog('sit_erro', 'Relatório sem campos - '.$id);
			if($this->_teste){
				$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
				log::gravaLog('sit_erro', 'Enviado email teste zerado - suporte@thielws.com.br - '.$titulo);
			}
		}
			
		return;
	}

	function getFaturamento($rel, $mes, $ano, $fechamento){
		$ret = array();
		$sqlFornec = '';
		
		if(!empty($fechamento)){
			$dia = date('d');
			if($dia > $fechamento){
				$dataFim = $ano.$mes.$fechamento;
				$mesIni = $mes - 1;
				if($mesIni < 1){
					$mesIni = 12;
					$ano--;
				}
				$mesIni = $mesIni < 10 ? '0'.$mesIni : $mesIni;
				$diaIni = $fechamento - 1;
				if($diaIni < 1){
					$mesIni = $mes - 1;
					if($mesIni < 1){
						$mesIni = 12;
						$ano--;
					}
					$mesIni = $mesIni < 10 ? '0'.$mesIni : $mesIni;
					
					$diaIni = date('t',mktime(0,0,0,$mesIni,15,$ano));
				}
				$dataIni = $ano.$mesIni.$diaIni;
			}else{
				$mesFim = $mes - 1;
				if($mesFim < 1){
					$mesFim = 12;
					$ano--;
				}
				$mesFim = $mesFim < 10 ? '0'.$mesFim : $mesFim;
				$dataFim = $ano.$mesFim.$fechamento;
				
				$mesIni = $mesFim - 1;
				if($mesIni < 1){
					$mesIni = 12;
					$ano--;
				}
				$mesIni = $mesIni < 10 ? '0'.$mesIni : $mesIni;
				$diaIni = $fechamento - 1;
				if($diaIni < 1){
					$mesIni = $mes - 1;
					if($mesIni < 1){
						$mesIni = 12;
						$ano--;
					}
					$mesIni = $mesIni < 10 ? '0'.$mesIni : $mesIni;
					
					$diaIni = date('t',mktime(0,0,0,$mesIni,15,$ano));
				}
				$dataIni = $ano.$mesIni.$diaIni;
			}
		}else{
			$dataIni = $ano.$mes.'01';
			$dataFim = $ano.$mes.date("t", mktime(0,0,0,$mes,'01',$ano));
		}
		
		$this->_dataIni = datas::dataS2D($dataIni);
		$this->_dataFim = datas::dataS2D($dataFim);
		
		$rede = $this->_param['rede'];
		$sqlRede = "";
		if($rede != ''){
			$sqlRede = "and pcmov.codcli in (select codcli from pcclient where codrede in ($rede))";
		}
		
		$whereRamo = '';
		if(!empty($this->_param['ramo'])){
			$whereRamo = " AND pcmov.CODCLI IN (SELECT CODCLI FROM PCCLIENT WHERE CODATV1 IN (".$this->_param['ramo']."))";
		}
		
		$fornec = $this->_param['fornecedor'];
		if($fornec != ''){
			$sqlFornec = "select codprod from pcprodut where pcprodut.codfornec IN ($fornec)";
		}
		
		$marca = $this->_param['marca'];
		if($marca != ''){
			if($sqlFornec != ''){
				$sqlFornec .= " and pcprodut.codmarca in ($marca)";
			}else{
				$sqlFornec = "select codprod from pcprodut where pcprodut.codmarca in ($marca)";
			}
		}
		
		$origem = $this->_param['origem'];
		$whereOrigem = '';
		if($origem != ''){
			$whereOrigem =  "AND PCNFSAID.NUMPED IN ( SELECT PCPEDC.NUMPED FROM PCPEDC WHERE PCPEDC.ORIGEMPED = 'F' AND PCPEDC.TIPOFV = 'OL') ";
		}
		
		$uf = $this->_param['uf'];
		$whereUF = '';
		if($uf != ''){
			$whereUF =  "AND pcclient.estent = '$uf' ";
		}
		
		if($this->verificaCampo('descnf') || $this->verificaCampo('descent') || $this->verificaCampo('limite') || $this->verificaCampo('defict') || $this->verificaCampo('ressarc')){
			$this->getDescontoEntrada();
		}
		
		$sql = "
SELECT  
    PCMOV.DTMOV DATA,
    PCCFO.DESCCFO CFOP,
    PCMOV.NUMNOTA NOTA,
    PCREDECLIENTE.DESCRICAO REDE,
    PCCLIENT.CGCENT CNPJ,
    PCCLIENT.CLIENTE CLIENTE,
    PCMOV.CODPROD PROD,
    PCPRODUT.CODFAB CODFAB,
    PCPRODUT.DESCRICAO DESCRI,
    PCPRODUT.CODAUXILIAR EAN,
    PCPRODUT.CUSTOREP PF,
    PCMOV.QTCONT QT,
    (PCMOV.PUNIT - PCMOV.ST) PRECO,
    PCMARCA.MARCA MARCA,
    PCCLIENT.ESTENT UF,
	PCMOV.NUMPED,
	PCNFSAID.CHAVENFE,
	PCPRODUT.CODFORNEC,
	PCMOV.CODCLI,
	PCPEDC.NUMPEDRCA,
	PCPEDC.CODUSUR
FROM 
    PCMOV,
    PCCLIENT,
    PCREDECLIENTE,
    PCPRODUT,
    PCMARCA,
    PCCFO,
    PCNFSAID,
	PCPEDC
WHERE 
    PCMOV.CODCLI = PCCLIENT.CODCLI 
    AND PCCLIENT.CODREDE = PCREDECLIENTE.CODREDE (+)
    AND PCMOV.CODPROD = PCPRODUT.CODPROD (+)
    AND PCPRODUT.CODMARCA = PCMARCA.CODMARCA (+)
    AND PCMOV.CODFISCAL = PCCFO.CODFISCAL (+)
	AND PCNFSAID.NUMTRANSVENDA=PCPEDC.NUMTRANSVENDA (+)
    AND PCNFSAID.TIPOVENDA <> 'TR'
    AND PCNFSAID.DTCANCEL IS NULL
    AND PCMOV.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA
    AND PCMOV.NUMNOTADEV IS NULL
    AND PCMOV.DTMOV >= TO_DATE('$dataIni','YYYYMMDD') and pcmov.dtmov <= to_date('$dataFim','YYYYMMDD')
    AND PCNFSAID.DTSAIDA >= TO_DATE('$dataIni','YYYYMMDD') and pcmov.dtmov <= to_date('$dataFim','YYYYMMDD')
    AND PCMOV.DTCANCEL IS NULL
     AND PCMOV.CODOPER IN ('S','SB')
     AND PCMOV.STATUS = 'AB'
     AND PCMOV.CODPROD IN ($sqlFornec)
     $whereOrigem
     $sqlRede
     $whereUF
	$whereRamo
ORDER BY 
	PCMOV.DTMOV,
	PCMOV.NUMNOTA
				";
//echo "\n\n $sql \n";
     $rows = query4($sql);
     
     if(count($rows) > 0){
     	foreach($rows as $row){
     		$temp = $this->montaMatriz();
     		
     		if($this->verificaCampo('data')){
     			$temp['data'] = datas::dataMS2D($row['DATA']);
     		}
     		if($this->verificaCampo('cfop')){
     			$temp['cfop'] = $row['CFOP'];
     		}
     		if($this->verificaCampo('nf')){
     			$temp['nf'] = $row['NOTA'];
     		}
     		if($this->verificaCampo('rede')){
     			$temp['rede'] = $row['REDE'];
     		}
     		if($this->verificaCampo('cnpj')){
     			$temp['cnpj'] = $row['CNPJ'];
     		}
     		if($this->verificaCampo('cnpjGF')){
     			$temp['cnpjGF'] = '89.735.070/0001-00';
     		}
     		if($this->verificaCampo('GF')){
     			$temp['GF'] = 'Gauchafama Medicamentos LTDA';
     		}
     		if($this->verificaCampo('texto_livre')){
     			$temp['texto_livre'] = $this->getConfigValor('CL_VALOR');
     		}
     		if($this->verificaCampo('cliente')){
     			$temp['cliente'] = $row['CLIENTE'];
     		}
     		if($this->verificaCampo('cod')){
     			$temp['cod'] = $row['PROD'];
     		}
     		if($this->verificaCampo('codfab')){
     			$temp['codfab'] = $row['CODFAB'];
     		}
     		if($this->verificaCampo('desc')){
     			$temp['descricao'] = $row['DESCRI'];
     		}
     		if($this->verificaCampo('ean')){
     			$temp['ean'] = $row['EAN'];
     		}
     		if($this->verificaCampo('pf')){
     			$temp['pf'] = $row['PF'];
     		}
     		if($this->verificaCampo('qt')){
     			$temp['qt'] = $row['QT'];
     		}
     		if($this->verificaCampo('vlunit')){
     			$temp['vlunit'] = $row['PRECO'];
     		}
     		if($this->verificaCampo('marca')){
     			$temp['marca'] = $row['MARCA'];
     		}
     		if($this->verificaCampo('fornecedor')){
     			$temp['fornecedor'] = $this->getNomeFornecedor($row['CODFORNEC']);
     		}
     		if($this->verificaCampo('totalpf')){
     			$temp['totalpf'] = $row['PF'] * $row['QT'];
     		}
     		if($this->verificaCampo('totalliq')){
     			$temp['totalliq'] = $row['QT'] * $row['PRECO'];
     		}
     		if($this->verificaCampo('uf')){
     			$temp['uf'] = $row['UF'];
     		}
     		if($this->verificaCampo('tipo')){
     			$temp['tipo'] = $this->_param['origem'];
     		}
     		if($this->verificaCampo('pedido')){
     			$temp['pedido'] = $row['NUMPED'];
     		}
     		if($this->verificaCampo('margemOL')){
     			$temp['margemOL'] = $this->getConfigValor('MARGEM_OL').'%';
     		}
     		if($this->verificaCampo('pedvan')){
     			$pedVan = $this->getNumPedVan($row['CODCLI'],$row['NUMPEDRCA'],$row['CODUSUR']);
     			$temp['pedvan'] = $pedVan;
     		}
     		if($this->verificaCampo('chavenfe')){
     			$temp['chavenfe'] = 'NFe'.$row['CHAVENFE'];
     		}
     		if($this->verificaCampo('descnf')){
     			$temp['descnf'] = number_format((1 - (($row['QT'] * $row['PRECO']) / ($row['PF'] * $row['QT'])))* 100, 2, ',', '.').' %';
     		}
     		if($this->verificaCampo('descpedido')){
     			$temp['descpedido'] = number_format((1 - (($row['QT'] * $row['PRECO']) / ($row['PF'] * $row['QT'])))* 100, 2, ',', '.').' %';
     		}
     		if($this->verificaCampo('descnf') || $this->verificaCampo('descent') || $this->verificaCampo('limite') || $this->verificaCampo('defict') || $this->verificaCampo('ressarc')){
     			//$this->getDescontoEntrada();
     			$margemOL = $this->getConfigValor('MARGEM_OL');
     			
     			if($this->verificaCampo('descent')){
     				$temp['descent'] = isset($this->_desconto[$row['PROD']]) ? $this->_desconto[$row['PROD']].' %' : '';
     			}
     			
     			if($this->verificaCampo('limite')){
     				$temp['limite'] = isset($this->_desconto[$row['PROD']]) ? number_format(($this->_desconto[$row['PROD']] - $margemOL), 2, ',', '.').'%' : '';
     			}
     			
     			if($this->verificaCampo('defict')){
     				$temp['defict'] = isset($this->_desconto[$row['PROD']]) ? number_format(($this->_desconto[$row['PROD']] - $margemOL) - ((1 - (($row['QT'] * $row['PRECO']) / ($row['PF'] * $row['QT'])))* 100), 2, ',', '.')."%" : '';
     			}
     			
     			if($this->verificaCampo('ressarc')){
     				$descnf = (1 - (($row['QT'] * $row['PRECO']) / ($row['PF'] * $row['QT'])))* 100;
     				$temp['ressarc'] = isset($this->_desconto[$row['PROD']]) ? ($row['PF'] * $row['QT']) * ((($this->_desconto[$row['PROD']] - $margemOL) - $descnf)/100) : '';
     			}
     		}
     		 
     		//Verifica se campo zerado
     		$inclui = true;
     		if($this->_param['zerado'] != ''){
     			if($this->verificaCampo($this->_param['zerado'])){
     				if($temp[$this->_param['zerado']] == 0 || $temp[$this->_param['zerado']] == ''){
     					$inclui = false;
     				}
     			}
     		}
     		
     		if($inclui){
     			$ret[] = $temp;
     		}
     		
     	}
     	
     	if($this->_param['agrupa'] != ''){
     		$ret = $this->agrupaDadosFaturamento($ret, $this->_param['agrupa']);
     	}
     	
     	return $ret;
     }
     
     return $ret;
     	
	}
	
	private function agrupaDadosFaturamento($dados, $campo){
		$ret = [];
//print_r(array_keys($dados[0]));
		$campos = ['qt','totalliq','vlunit','ressarc'];
		if(count($dados) > 0 && !empty($campo)){
			foreach ($dados as $dado){
				if(!isset($ret[$dado[$campo]])){
					foreach ($dado as $chave => $valor){
						$ret[$dado[$campo]][$chave] = $valor;
					}
				}else{
					foreach ($dado as $chave => $valor){
						if(array_search($chave, $campos) !== false){
							$ret[$dado[$campo]][$chave] += $valor;
						}
					}
				}
			}
		}else{
			$ret = $dados;
		}

		$temp = $ret;
		$ret = [];
		foreach ($temp as $t){
			$ret[] = $t;
		}
		
		return $ret;
	}
	
	private function getNomeFornecedor($fornecedor){
		if(!isset($this->_fornecedor[$fornecedor])){
			$sql = "SELECT FORNECEDOR FROM PCFORNEC WHERE CODFORNEC = $fornecedor";
			$rows = query4($sql);
			if(isset($rows[0]['FORNECEDOR'])){
				$this->_fornecedor[$fornecedor] = $rows[0]['FORNECEDOR'];
			}else{
				$this->_fornecedor[$fornecedor] = '';
			}
		}
		$ret = $this->_fornecedor[$fornecedor];
		return $ret;
	}
	
	private function getNumPedVan($codcli,$pedido,$rca){
		$ret = '';
		
		$sql = "SELECT NUMPEDVAN FROM PCPEDRETORNO WHERE NUMPEDRCA = $pedido AND CODUSUR = $rca AND CODCLI = $codcli";
		$rows = query4($sql);
		if(isset($rows[0]['NUMPEDVAN'])){
			$ret = $rows[0]['NUMPEDVAN'];
		}
		
		return $ret;
	}
	
	private function getConfigValor($campo){
		$ret = '';
		if(isset($this->_param['config'][$campo])){
			$ret = $this->_param['config'][$campo];
		}else{
			$sql = "SELECT padrao FROM gf_sit_config WHERE campo = '$campo'";
			$rows = query($sql);
			if(isset($rows[0]['padrao'])){
				$ret = $rows[0]['padrao'];
			}
		}
		
		return $ret;
	}
	
	function getDescontoEntrada(){
		$fornec = $this->_param['fornecedor'];
		$marca = $this->_param['marca'];

		$sql = "select codprod, percdesc1 from pcprodut where ";// codfornec in ($fornec)";// and pcprodut.dtexclusao is null  ";
		if($fornec != ''){
			$sql .= " codfornec in ($fornec)";
		}
		if($fornec != '' && $marca != ''){
			$sql .= ' AND ';
		}
		if($marca != ''){
			$sql .= " pcprodut.codmarca in ($marca)";
		}
		
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_desconto[$row[0]] = $row[1];
			}
		}
//print_r($this->_desconto);die();
	}
	
	function getProdutos($rel, $mes, $ano, $fechamento){
		$ret = array();
		$dadoPedidos = [];
		$sqlProd = $this->getSQLProdutos();
		
		$demanda = $this->verificaCampo('demanda1');
		$uf = $this->verificaCampo('uf');
		$positivacao = $this->verificaCampo('positivacao');
		
		$demandaValores = array();
		if($demanda){
			$demandaValores= $this->getDemanda($mes, $ano, $fechamento, $sqlProd, $uf);
		}
		
		if($this->verificaCampo('demandaAtu')){
			$demandaAtu = $this->getDemanda(date('m'), date('Y'), $fechamento, $sqlProd, $uf);
		}
		
		
		$positivacaoValores = array();
		if($positivacao){
			$positivacaoValores= $this->getPositivacao($mes, $ano, $sqlProd, $uf);
		}
		
		if($this->verificaCampo('somaNot')){
			$venda = $this->getValorNota($mes, $ano, $sqlProd);
		}
		
		//Verifica se tem campos de pedidos
		if($this->verificaCampo('ped_')){
			$dadoPedidos = $this->getDadosPedidos($sqlProd);
		}
		
		$sql = "
				SELECT 
				    PCPRODUT.CODPROD, 
				    PCPRODUT.CODFAB,
				    PCPRODUT.DESCRICAO,
				    PCPRODUT.CODAUXILIAR,
				    PCMARCA.MARCA,
				    PCPRODUT.CUSTOREP,
				     (PCEST.QTESTGER - PCEST.QTINDENIZ - PCEST.QTBLOQUEADA) ESTOQUE
				FROM 
				    PCPRODUT,
				    PCMARCA,
				    PCEST
				WHERE  
				    PCPRODUT.CODMARCA = PCMARCA.CODMARCA (+)
				    AND PCPRODUT.CODPROD = PCEST.CODPROD (+)
				    AND PCEST.CODFILIAL = 1
					and pcprodut.codprod in ($sqlProd)
				    and pcprodut.dtexclusao is null
				    --and pcprodut.obs2 <> 'FL'
				order by 
				    pcprodut.descricao
				";
		$rows = query4($sql);
//echo "REL: $rel \n\n $sql \n";

		//Se existir mesca o resultado dos produtos com os pedidos
		if(count($rows) > 0 && count($dadoPedidos) > 0){
			$rows = $this->mesclaProdtutoPedidos($rows, $dadoPedidos);
		}
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$temp = $this->montaMatriz();
				$produto = $row[0];
				
				$temp['cod'] 		= $produto;
				$temp['codfab'] 	= $row[1];
				$temp['descricao'] 	= $row[2];
				$temp['ean'] 		= $row[3];
				$temp['marca'] 		= $row[4];
				
				if($this->verificaCampo('texto_livre')){
					$temp['texto_livre'] = $this->getConfigValor('CL_VALOR');
				}
				if($this->verificaCampo('pf')){
					$temp['pf'] = $row[5];
				}
				if($this->verificaCampo('estoque')){
					$temp['estoque'] = $row[6];
				}
				if($this->verificaCampo('somaFab')){
					$temp['somaFab'] = isset($demandaValores[$row[0]][1]) ? $demandaValores[$row[0]][1] * $temp['pf'] : 0;
				}
				
				if($this->verificaCampo('somaNot')){
					$temp['somaNot'] = isset($venda[$row[0]]) ? $venda[$row[0]] : 0;
				}
				
				if($this->verificaCampo('demandaAtu') && !$demanda){
					$temp['demandaAtu'] = isset($demandaAtu[$produto][1]) ? $demandaAtu[$produto][1] : '';
				}
				//Verifica se tem campos de pedidos
				if($this->verificaCampo('ped_') && isset($dadoPedidos[$produto])){
//					foreach ($dadoPedidos[$produto] as $d){
						if($this->verificaCampo('ped_quant')){
							if($temp['ped_quant'] != ''){
								$temp['ped_quant'] .= '<br>';
							}
							$temp['ped_quant'] .= $row['qt'];
						}
						if($this->verificaCampo('ped_entregue')){
							if($temp['ped_entregue'] != ''){
								$temp['ped_entregue'] .= '<br>';
							}
							$temp['ped_entregue'] .= $row['entregue'];
						}
						if($this->verificaCampo('ped_pre')){
							if($temp['ped_pre'] != ''){
								$temp['ped_pre'] .= '<br>';
							}
							$temp['ped_pre'] .= $row['pre'];
						}
						if($this->verificaCampo('ped_pendente')){
							if($temp['ped_pendente'] != ''){
								$temp['ped_pendente'] .= '<br>';
							}
							$temp['ped_pendente'] .= $row['pendente'];
						}
						if($this->verificaCampo('ped_oc')){
							if($temp['ped_oc'] != ''){
								$temp['ped_oc'] .= '<br>';
							}
							$temp['ped_oc'] .= $row['pedido'];
						}

					
						if($this->verificaCampo('ped_emissao')){
							if($temp['ped_emissao'] != ''){
								$temp['ped_emissao'] .= '<br>';
							}
							$temp['ped_emissao'] .= $row['emissao'];
						}
						if($this->verificaCampo('ped_diasPendentes')){
							if($temp['ped_diasPendentes'] != ''){
								$temp['ped_diasPendentes'] .= '<br>';
							}
							$temp['ped_diasPendentes'] .= $row['diasPend'];
						}
//					}
				}
				
				
				if($this->verificaCampo('uf')){
					if($this->_param['uf'] == '' || $this->_param['uf'] == 'RS'){
						$temp['uf'] = 'RS';
						if($this->verificaCampo('demanda1')){
							$temp['demanda1'] = isset($demandaValores[$produto]['RS'][1]) ? $demandaValores[$produto]['RS'][1] : 0;
						}
						if($this->verificaCampo('demanda2')){
							$temp['demanda2'] = isset($demandaValores[$produto]['RS'][2]) ? $demandaValores[$produto]['RS'][2] : 0;
						}
						if($this->verificaCampo('demanda3')){
							$temp['demanda3'] = isset($demandaValores[$produto]['RS'][3]) ? $demandaValores[$produto]['RS'][3] : 0;
						}
						if($positivacao){
							$temp['positivacao'] = isset($positivacaoValores[$produto]['RS']) ? $positivacaoValores[$produto]['RS']: 0;
						}
						if($this->verificaCampo('demandaAtu')){
							$temp['demandaAtu'] = isset($demandaAtu[$produto]['RS'][1]) ? $demandaAtu[$produto]['RS'][1] : 0;
						}
						$ret[] = $temp;
					}
					
					if($this->_param['uf'] == '' || $this->_param['uf'] == 'SC'){
						$temp['uf'] = 'SC';
						if($this->verificaCampo('demanda1')){
							$temp['demanda1'] = isset($demandaValores[$produto]['SC'][1]) ? $demandaValores[$produto]['SC'][1] : 0;
						}
						if($this->verificaCampo('demanda2')){
							$temp['demanda2'] = isset($demandaValores[$produto]['SC'][2]) ? $demandaValores[$produto]['SC'][2] : 0;
						}
						if($this->verificaCampo('demanda3')){
							$temp['demanda3'] = isset($demandaValores[$produto]['SC'][3]) ? $demandaValores[$produto]['SC'][3] : 0;
						}
						if($positivacao){
							$temp['positivacao'] = isset($positivacaoValores[$produto]['SC']) ? $positivacaoValores[$produto]['SC']: 0;
						}
						if($this->verificaCampo('demandaAtu')){
							$temp['demandaAtu'] = isset($demandaAtu[$produto]['SC'][1]) ? $demandaAtu[$produto]['SC'][1] : 0;
						}
						$ret[] = $temp;
					}
					if($this->_param['uf'] == '' || $this->_param['uf'] == 'PR'){
						$temp['uf'] = 'PR';
						if($this->verificaCampo('demanda1')){
							$temp['demanda1'] = isset($demandaValores[$produto]['PR'][1]) ? $demandaValores[$produto]['PR'][1] : 0;
						}
						if($this->verificaCampo('demanda2')){
							$temp['demanda2'] = isset($demandaValores[$produto]['PR'][2]) ? $demandaValores[$produto]['PR'][2] : 0;
						}
						if($this->verificaCampo('demanda3')){
							$temp['demanda3'] = isset($demandaValores[$produto]['PR'][3]) ? $demandaValores[$produto]['PR'][3] : 0;
						}
						if($positivacao){
							$temp['positivacao'] = isset($positivacaoValores[$produto]['PR']) ? $positivacaoValores[$produto]['PR']: 0;
						}
						if($this->verificaCampo('demandaAtu')){
							$temp['demandaAtu'] = isset($demandaAtu[$produto]['PR'][1]) ? $demandaAtu[$produto]['PR'][1] : 0;
						}
						$ret[] = $temp;
					}
				}else{
					if($this->verificaCampo('demanda1')){
						$temp['demanda1'] = isset($demandaValores[$produto][1]) ? $demandaValores[$produto][1] : 0;
					}
					if($this->verificaCampo('demanda2')){
						$temp['demanda2'] = isset($demandaValores[$produto][2]) ? $demandaValores[$produto][2] : 0;
					}
					if($this->verificaCampo('demanda3')){
						$temp['demanda3'] = isset($demandaValores[$produto][3]) ? $demandaValores[$produto][3] : 0;
					}
					if($positivacao){
						$temp['positivacao'] = isset($positivacaoValores[$produto]) ? $positivacaoValores[$produto] : 0;
					}
					if($this->verificaCampo('demandaAtu')){
						$temp['demandaAtu'] = isset($demandaAtu[$produto][1]) ? $demandaAtu[$produto][1] : 0;
					}
					
					//Verifica se campo zerado
					$inclui = true;
					if($this->_param['zerado'] != ''){
						if($this->verificaCampo($this->_param['zerado'])){
							if($temp[$this->_param['zerado']] == 0 || $temp[$this->_param['zerado']] == ''){
								$inclui = false;
							}
						}
					}
					
					if($inclui){
						$ret[] = $temp;
					}
				}				
			}
			//Inclui total de positivação
			if($positivacao){
				$temp = $this->montaMatriz();
				if($this->verificaCampo('demanda1')){
					$temp['demanda1'] = '';
				}
				if($this->verificaCampo('demanda2')){
					$temp['demanda2'] = '';
				}
				if($this->verificaCampo('demanda3')){
					$temp['demanda3'] = '';
				}
				
				$positivacaoValores = $this->getPositivacaoGeral($mes, $ano, $sqlProd, $uf);
				if($this->verificaCampo('uf')){
					if($this->_param['uf'] == '' || $this->_param['uf'] == 'RS'){
						$temp['uf'] = 'RS';
						$temp['positivacao'] = isset($positivacaoValores['RS']) ? $positivacaoValores['RS']: 0;
						$ret[] = $temp;
					}
					
					if($this->_param['uf'] == '' || $this->_param['uf'] == 'SC'){
						$temp['uf'] = 'SC';
						$temp['positivacao'] = isset($positivacaoValores['SC']) ? $positivacaoValores['SC']: 0;
						$ret[] = $temp;
					}
				}else{
					$temp['positivacao'] = isset($positivacaoValores) ? $positivacaoValores : 0;
					$ret[] = $temp;
				}	
			}
			
		}

		return $ret;	
	}
	
	/*
	 * Retorna demanda dos produtos dos ultimos 3 meses
	 */
	function getDemanda($mes, $ano, $fechamento, $sqlProd, $uf){
		$ret = array();
		if($this->_param['uf'] == ''){
			$whereUF = "'RS','SC'";
		}else{
			$whereUF = "'".$this->_param['uf']."'";
		}
		
		$rede = $this->_param['rede'];
		$whereRede = "";
		if($rede != ''){
			$whereRede = "and pcmov.codcli in (select codcli from pcclient where codrede in ($rede))";
		}
		$whereRamo = '';
		if(!empty($this->_param['ramo'])){
			$whereRamo = " AND pcmov.CODCLI IN (SELECT CODCLI FROM PCCLIENT WHERE CODATV1 IN (".$this->_param['ramo']."))";
		}
		
		for($i=0;$i<3;$i++){
			if($i > 0){
				$mes = $mes - 1;
				if($mes == 0){
					$mes = 12;
					$ano--;
				}
			}
			$mes = (int)$mes;
			$mes = $mes < 10 ? '0'.$mes : $mes;
			
			$dataIni = $ano.$mes.'01';
			$dataFim = $ano.$mes.date("t", mktime(0,0,0,$mes,'01',$ano));
			$sql = "
			select 
			    codprod,
			    uf,
			    sum(qt) qt
			from(
			    select 
			        codprod,
			        sum(qtcont) qt,
			        (select pcclient.estent from pcclient where codcli = pcmov.codcli) uf,
			        pcmov.codcli
			    from 
			        pcmov,
			        pcnfsaid
			    where 
			        dtmov >= to_date('$dataIni','YYYYMMDD') and dtmov <= to_date('$dataFim','YYYYMMDD')
						    and pcmov.numtransvenda = pcnfsaid.numtransvenda
						    and pcmov.numnotadev is null
						    and pcnfsaid.dtsaida >= to_date('$dataIni','YYYYMMDD') and pcmov.dtmov <= to_date('$dataFim','YYYYMMDD')
			        and codoper like 'S%'
			        and pcmov.dtcancel is null
					$whereRede
					$whereRamo
			        and status = 'AB'
			        and codprod IN ($sqlProd)
			    group by codprod,3,pcmov.codcli
			    )
			where
			    uf in ($whereUF)
			group by 
			    codprod, uf
			";
			$rows = query4($sql);
//echo "$sql \n";
		
			if(count($rows) > 0){
				foreach($rows as $row){
					$prod = $row[0];
					$est = $row[1];
					$quant = $row[2];
					if($uf){
						if(!isset($ret[$prod][$est][$i+1])){
							$ret[$prod][$est][$i+1] = 0;
						}
						$ret[$prod][$est][$i+1] += $quant;
					}else{
						if(!isset($ret[$prod][$i+1])){
							$ret[$prod][$i+1] = 0;
						}
						$ret[$prod][$i+1] += $quant;
						
					}
				}
			}
		}
	
		return $ret;
	}
	
	/*
	 * Retorna positivacao dos produtos
	 */
	function getPositivacao($mes, $ano, $sqlProd, $uf){
		$ret = array();
		$whereUF = '';
		if($this->_param['uf'] != ''){
			$whereUF = "AND PCPEDC.CODCLI IN (select codcli from pcclient where estent IN ('".$this->_param['uf']."')";
		}
		$whereRamo = '';
		if(!empty($this->_param['ramo'])){
			$whereRamo = " AND PCPEDC.CODCLI IN (SELECT CODCLI FROM PCCLIENT WHERE CODATV1 IN (".$this->_param['ramo']."))";
		}
		
		$dataIni = $ano.$mes.'01';
		$dataFim = $ano.$mes.date("t", mktime(0,0,0,$mes,'01',$ano));
		$rede = $this->_param['rede'];
		$whereRede = "";
		if($rede != ''){
			$whereRede = "and pcpedc.codcli in (select codcli from pcclient where codrede in ($rede))";
		}
		$sql = "
				SELECT 
				    PCPEDI.CODPROD,
				    pcclient.estent UF,
				    COUNT(DISTINCT(PCPEDC.CODCLI)) QTCLIPOS 
				FROM  
				    PCPEDC, 
				    PCPEDI,
				    pcclient
				WHERE 
				    PCPEDC.DATA >=to_date('$dataIni','YYYYMMDD') AND PCPEDC.DATA <= to_date('$dataFim','YYYYMMDD')
				    and pcpedc.codcli = pcclient.codcli 
				    AND PCPEDC.NUMPED = PCPEDI.NUMPED
				    AND PCPEDI.CODPROD IN ($sqlProd)
				    $whereUF
				    $whereRede
					$whereRamo
				    AND PCPEDC.DTCANCEL IS NULL
				    AND (PCPEDC.CODFILIAL IS NOT NULL) AND (PCPEDC.CODFILIAL IN ('1'))
				    AND PCPEDC.POSICAO = 'F'
				    AND PCPEDC.CONDVENDA IN (1, 2, 3, 7, 9, 14, 15, 17, 18, 19, 98)
				GROUP BY 
				    PCPEDI.CODPROD,
				    pcclient.estent
				ORDER BY 
				    PCPEDI.CODPROD

		";
		$rows = query4($sql);
		//echo "$sql \n";
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$prod = $row['CODPROD'];
				$est = $row['UF'];
				$quant = $row['QTCLIPOS'];
				if($uf){
					if(!isset($ret[$prod][$est])){
						$ret[$prod][$est] = 0;
					}
					$ret[$prod][$est] += $quant;
				}else{
					if(!isset($ret[$prod])){
						$ret[$prod] = 0;
					}
					$ret[$prod] += $quant;
					
				}
			}
		}
		
		return $ret;
	}
	
	/*
	 * Retorna positivacao geral
	 */
	function getPositivacaoGeral($mes, $ano, $sqlProd, $uf){
		if($uf){
			$ret = array();
		}else{
			$ret = 0;
		}
		$whereUF = '';
		if($this->_param['uf'] != ''){
			$whereUF = "AND PCPEDC.CODCLI IN (select codcli from pcclient where estent IN ('".$this->_param['uf']."')";
		}
		$rede = $this->_param['rede'];
		$whereRede = "";
		if($rede != ''){
			$whereRede = "and PCPEDC.codcli in (select codcli from pcclient where codrede in ($rede))";
		}
		$whereRamo = '';
		if(!empty($this->_param['ramo'])){
			$whereRamo = " AND PCPEDC.CODCLI IN (SELECT CODCLI FROM PCCLIENT WHERE CODATV1 IN (".$this->_param['ramo']."))";
		}
		
		$dataIni = $ano.$mes.'01';
		$dataFim = $ano.$mes.date("t", mktime(0,0,0,$mes,'01',$ano));
		$sql = "
				SELECT
					pcclient.estent UF,
					COUNT(DISTINCT(PCPEDC.CODCLI)) QTCLIPOS
				FROM
					PCPEDC,
					PCPEDI,
					pcclient
				WHERE
					PCPEDC.DATA >=to_date('$dataIni','YYYYMMDD') AND PCPEDC.DATA <= to_date('$dataFim','YYYYMMDD')
					and pcpedc.codcli = pcclient.codcli
					AND PCPEDC.NUMPED = PCPEDI.NUMPED
					AND PCPEDI.CODPROD IN ($sqlProd)
					$whereUF
					$whereRede
					$whereRamo
					AND PCPEDC.DTCANCEL IS NULL
					AND (PCPEDC.CODFILIAL IS NOT NULL) AND (PCPEDC.CODFILIAL IN ('1'))
					AND PCPEDC.POSICAO = 'F'
					AND PCPEDC.CONDVENDA IN (1, 2, 3, 7, 9, 14, 15, 17, 18, 19, 98)
				GROUP BY
					pcclient.estent
		";
		$rows = query4($sql);
		//echo "$sql \n";
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$est = $row['UF'];
				$quant = $row['QTCLIPOS'];
				if($uf){
					if(!isset($ret[$prod])){
						$ret[$prod] = 0;
					}
					$ret[$prod] += $quant;
				}else{
					$ret += $quant;
				}
			}
		}
		
		return $ret;
	}
	
	/*
	 * Retorna valor de nota dos produtos vendidos
	 */
	function getValorNota($mes, $ano, $sqlProd){
		$ret = array();
		$dataIni = $ano.$mes.'01';
		$dataFim = $ano.$mes.date("t", mktime(0,0,0,$mes,'01',$ano));
		$whereUF = '';
		if($this->_param['uf'] != ''){
			$whereUF = "AND PCMOV.CODCLI IN (select codcli from pcclient where estent IN ('".$this->_param['uf']."'))";
		}
		$whereRamo = '';
		if(!empty($this->_param['ramo'])){
			$whereRamo = " AND pcmov.CODCLI IN (SELECT CODCLI FROM PCCLIENT WHERE CODATV1 IN (".$this->_param['ramo']."))";
		}
		
		$rede = $this->_param['rede'];
		$whereRede = "";
		if($rede != ''){
			$whereRede = "and pcmov.codcli in (select codcli from pcclient where codrede in ($rede))";
		}
		
		$sql = "
		select
			codprod,
			SUM((PCMOV.punit - pcmov.st) * pcmov.qt) VLVENDA
		from
			pcmov
		where
			dtmov >= to_date('$dataIni','YYYYMMDD') and dtmov <= to_date('$dataFim','YYYYMMDD')
			and codoper like 'S%'
			and pcmov.dtcancel is null
			and status = 'AB'
			and codprod IN ($sqlProd)
			$whereUF
			$whereRede
			$whereRamo
		group by codprod
		";
//echo "$sql \n";
		$rows = query4($sql);
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$ret[$row[0]] 	= $row[1];
			}
		}
		
		return $ret;
		
	}
	
	/*
	 * Monta matriz 
	 */
	function montaMatriz(){
		$temp = array();
		$counas = $this->_relatorio->getCampos();
		if(count($counas) > 0){
			foreach ($counas as $col){
				$temp[$col] = '';
			}
		}
//print_r($this->_colunas);		
		return $temp;
	}
	
	
	/*
	 * Monta query de seleção de produtos
	 */
	function getSQLProdutos(){
		$fornec = $this->_param['fornecedor'];
		$marca = $this->_param['marca'];

		$sql = "select codprod from pcprodut where codfornec in ($fornec) and pcprodut.dtexclusao is null  ";
		if($marca != ''){
			$sql .= " and codmarca IN ($marca)";
		}
		return $sql;
	}
	
	/*
	 * Retorna o nomes dos meses para tipo 2
	 */
	function getNomeMeses($mes, $ano){
		$ret = array();
		$meses = array('Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez');
		for($i=0;$i<3;$i++){
			if($i > 0){
				$mes = $mes - 1;
				if($mes == 0){
					$mes = 12;
					$ano--;
				}
			}
			
			$a = substr($ano, 2,2);
			$ret[$i] = $meses[$mes - 1].'/'.$a;	
		}
		
		return $ret;
	}

	/*
	 * Define as caracteristicas dos relatorios
	 */
	function defineRelatorios($relatorio, $ano, $mes, $teste = false){
		$this->_param = array();
		$campos = array();
		if($relatorio > 0){
			$where = " AND ativo = 'S'";
			if($teste){
				$where = "";
			}
			$sql = "SELECT * FROM gf_sit WHERE  del = ' ' $where AND id = $relatorio";
			$rows = query($sql);
			if(count($rows) > 0){
				$this->_param['nome'] 		= utf8_encode($rows[0]['nome']);
				$this->_param['tipo'] 		= $rows[0]['tipo'];
				$this->_param['fornecedor'] = $this->ajustaSeparador($rows[0]['fornecedor']);
				$this->_param['marca'] 		= $this->ajustaSeparador($rows[0]['marca']);
				$this->_param['rede'] 		= $this->ajustaSeparador($rows[0]['rede']);
				$this->_param['origem'] 	= $rows[0]['origem'];
				$this->_param['uf'] 		= $rows[0]['uf'];
				$this->_param['ramo'] 		= $rows[0]['ramo'];
				$this->_param['zerado'] 	= $rows[0]['zerado'];
				$this->_param['agrupa'] 	= $rows[0]['agrupa'];
				
				$this->_param['campos']		= trim($rows[0]['campos']);
				$this->_param['config']		= unserialize(trim($rows[0]['config']));
				
				$this->_param['periodo'] 	= $rows[0]['periodo'];
				$this->_param['interno'] 	= $rows[0]['emailInterno'];
				$this->_param['externo'] 	= $rows[0]['emailExterno'];
				$this->_param['texto'] 		= $rows[0]['email_texto'];
			}
			
			//Nome dos meses para demandas
			$meses = $this->getNomeMeses($mes, $ano);
			
			//Pega a lista de campos
			$sql = "SELECT * FROM gf_sit_campos WHERE ativo = 'S'";
			$rows = query($sql);
			if(count($rows) > 0){
				foreach ($rows as $row){
					$temp = array();
					$campo = $row['campo'];
					$temp['campo'] 			= $campo;
					$temp['etiqueta'] 		= $row['etiqueta'];
					if(strpos($temp['campo'], 'demanda') !== false && $temp['campo'] != 'demandaAtu'){
						$temp['etiqueta'] = $this->getEtiquetaDemanda($temp['campo'], $meses);
					}elseif(strpos($temp['campo'], 'demanda') !== false && $temp['campo'] == 'demandaAtu'){
						$temp['etiqueta'] = 'Demanda';
					}
					$temp['tipo'] 			= $row['tipo'];
					$temp['tamanho'] 		= $row['tamanho'];
					$temp['alinhamento'] 	= $row['alinhamento'];
					
					$campos[$campo] = $temp;
				}
			}
			
			$camposRel = explode(',', $this->_param['campos']);
			if(count($camposRel) > 0 && count($campos) > 0){
//print_r($camposRel);
//print_r($campos);
				foreach ($camposRel as $c){
					if(isset($campos[$c])){
						if($c == 'texto_livre'){
							$etiqueta = $this->getConfigValor('CL_CAB');
						}else{
							$etiqueta = $campos[$c]['etiqueta'];
						}
						$this->_relatorio->addColuna(array('campo' => $campos[$c]['campo']	, 'etiqueta' => $etiqueta	, 'tipo' => $campos[$c]['tipo']	,'width' => $campos[$c]['tamanho'], 'posicao' => $campos[$c]['alinhamento']));
					}
				}
			}
		}
	}
	
	private function getEtiquetaDemanda($campo, $meses){
		$demanda = substr($campo, -1) - 1;
		
		return 'Demanda '.$meses[$demanda];
	}
	
	private function verificaCampo($campo){
		$ret = false;
//echo $this->_param['campos']."    - $campo <br>\n";
		if(strpos($this->_param['campos'], $campo) !== false){
			$ret = true;
		}
		
		return $ret;
	}
	
	private function getRelatorios(){
		$ret = array();
		$sql = "SELECT * FROM gf_sit WHERE ativo = 'S' AND del = ' ' AND periodo NOT IN ('',' ','N')";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				if(!empty($row['campos'])){
					$ret[$row['id']]['periodo'] = $row['periodo'];
					$ret[$row['id']]['dia'] 	= $row['dia'];
				}
			}
		}
//print_r($ret);die();
		return $ret;
	}
	private function gravaLogSIT($id, $acao, $complemento = ''){
		$dia = date('Ymd');
		$hora = date('H:i');
		$usuario = getUsuario();
		$sql = "INSERT INTO gf_sit_log (dia,hora,usuario,relatorio,acao,complemento) VALUES ('$dia', '$hora', '$usuario', $id, '$acao', '$complemento')";
		query($sql);
	}

	/*
	 * Retorna dados referentes a pedidos de compras
	 */
	private function getDadosPedidos($sqlProd){
		$ret = array();
		$sql = "
				SELECT
		            P.NUMPED,
		            I.CODPROD,
		            I.QTPEDIDA,
		            I.QTENTREGUE,
					(SELECT SUM(MOV.QT) FROM PCMOVPREENT MOV WHERE MOV.NUMPED = I.NUMPED AND MOV.CODPROD = I.CODPROD) QTPRE,
					(SELECT MOV.UNIDADE FROM PCMOVPREENT MOV WHERE MOV.NUMPED = I.NUMPED AND MOV.CODPROD = I.CODPROD AND ROWNUM = 1) UNIDADE,
					DTEMISSAO,
					(trunc(SYSDATE) - trunc(DTEMISSAO))  PENDENTE
		        FROM 
					PCPEDIDO P,
		            PCITEM I
		        WHERE 
		            P.NUMPED = I.NUMPED
					--AND DTLIBERA IS NOT NULL --Somente pedidos liberados
		            --AND P.CODFORNEC = 1118
		            AND (NVL(I.QTPEDIDA, 0) > NVL(I.QTENTREGUE, 0))
					AND I.CODPROD IN ($sqlProd)
		        ORDER BY 
					I.NUMSEQ
				";
//echo "$sql <br>\n";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = array();
				$codprod = $row['CODPROD'];
				
				$temp['pedido'] 	= $row['NUMPED'];
				$temp['qt'] 		= $row['QTPEDIDA'];
				$temp['entregue'] 	= $row['QTENTREGUE'];
				$qtPre = $row['QTPRE'];
				if($row['UNIDADE'] == 'CX'){
					$qtCaixa = $this->getQuantCaixa($codprod);
					$qtPre = $row['QTPRE'] * $qtCaixa;
//echo "Produto: $codprod <br>\n";
//echo "Caixa: $qtCaixa <br>\n";
//echo "Pre: ".$row['QTPRE']." <br>\n";
//echo "Total: $qtPre <br>\n<br>\n";
				}
				$temp['pre'] 		= $qtPre - $row['QTENTREGUE'];
				$temp['pendente'] 	= $row['QTPEDIDA'] - $row['QTENTREGUE'];
				$temp['emissao']	= datas::dataMS2D($row['DTEMISSAO']);
				$temp['diasPend']	= $row['PENDENTE'];
				
				$ret[$codprod][] = $temp;
			}
		}
		return $ret;
	}
	
	private function getQuantCaixa($codprod){
		if(!isset($this->_qtCaixa[$codprod])){
			$sql = "select QTUNITCX from PCprodut where codprod = $codprod";
			$rows = query4($sql);
//echo "SQL $sql <br>\n";
//print_r($rows);
			
			if(isset($rows[0]['QTUNITCX'])){
				$this->_qtCaixa[$codprod] = $rows[0]['QTUNITCX'];
			}else{
				$this->_qtCaixa[$codprod] = 1;
			}
		}
//echo "QT: ".$this->_qtCaixa[$codprod]." <br>\n";
		return $this->_qtCaixa[$codprod];
	}
	
	private function mesclaProdtutoPedidos($rows, $dadoPedidos){
		$ret = [];

		foreach ($rows as $row){
			$temp = $row;
			$prod = $row['CODPROD'];
			
			if(isset($dadoPedidos[$prod])){
				foreach ($dadoPedidos[$prod] as $pedido){
					$temp = array_merge($row, $pedido);
					$ret[] = $temp;
				}
			}else{
				$temp['pedido'] 	= '';
				$temp['qt'] 		= '';
				$temp['entregue'] 	= '';
				$temp['pre'] 		= '';
				$temp['pendente'] 	= '';
				$temp['emissao']	= '';
				$temp['diasPend']	= '';
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function ajustaSeparador($texto){
		$texto = str_replace(';', ',', $texto);
		$texto = str_replace('|', ',', $texto);
		$texto = str_replace('.', ',', $texto);
		$texto = str_replace(':', ',', $texto);
		
		return $texto;
	}
}

function getListaRelatoriosSIT(){
	$ret = array();

	$sql = "SELECT id, nome, campos FROM gf_sit WHERE ativo = 'S' and del = ' ' ORDER BY nome";
	$rows = query($sql);
	if(count($rows) > 0){
		foreach ($rows as $row){
			if(!empty($row['campos'])){
				$ret[$row['id']][0] = $row['id'];
				$ret[$row['id']][1] = $row['id'].' - '.utf8_encode($row['nome']);
			}
		}
	}

	return $ret;
}

function getListaMesesSIT(){
	$ret = array();
	$meses = array( '01' => 'Janeiro',
					'02' => 'Fevereiro',
					'03' => 'Marco',
					'04' => 'Abril',
					'05' => 'Maio',
					'06' => 'Junho',
					'07' => 'Julho',
					'08' => 'Agosto',
					'09' => 'Setembro',
					'10' => 'Outubro',
					'11' => 'Novembro',
					'12' => 'Dezembro');
	$i = 0;
	foreach ($meses as $mes => $desc){
		$ret[$i][0] = $mes;
		$ret[$i][1] = $mes.' - '.$desc;
		$i++;
	}
	
	return $ret;
}