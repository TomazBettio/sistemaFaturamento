<?php
/*
 * Data Criacao 03/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class baixa_titulo{
	var $_relatorio;
	
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Indica que se é teste (utiliza banco teste)
	var $_teste;
	
	//tabela da intranet que tem os dados
	var $_tabelaIntranet;
	
	//rotina que baixa o titulo
	var $_rotinaBaixa;
	
	//URL baixa
	var $_urlBaixa;
	
	//Arquivo de log
	var $_arquivoLog;
	
	//Realiza trace?
	private $_trace;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		$this->_trace = false;
		

		if($this->_teste){
			$this->_tabelaIntranet = 'gf_titulosbaixados_teste';
			$this->_arquivoLog = 'processa_baixa_teste_log';
		}else{
			$this->_tabelaIntranet = 'gf_titulosbaixados';
			$this->_arquivoLog = 'processa_baixa_log';
		}
	}
	
	function index(){
		//$this->baixaTitulo(2503140, 9, 419.34, '001', '041', 'D', '20180227',1,2,3,4,5,6);
		//$this->baixaTitulo(2507349, 1, 16.31, '001', '001', 1,'D', '20171213');
		//$this->baixaTitulo(2506276, 1, 24.28, '001', '001', 1,'D', '20171213');
		//$this->baixaTitulo(2506335, 1, 10.07, 'DEP', '001', 1,'DNI', '20171213');
	}
	
	
	function schedule($param){
	}
	
	function baixaTitulo($duplic, $prest, $valorPago, $codBanco, $moeda, $dataPagamento, $historico, $desconto = 0, $numTrans = 0, $juros = 'NULL', $banco = '', $codCob = '',
			$multa = 0, $despBancarias = 0, $despCartorio = 0, $despOutros = 0){
		if($valorPago <= 0 && $desconto <= 0){
			log::gravaLog($this->_arquivoLog,"Valor pago e desconto zerados: $duplic / $prest");
	  		return false;
	  	}
	  	$sql = "SELECT * FROM ".$this->_tabelaIntranet." WHERE duplicata = $duplic AND parcela = '$prest' AND processado = 'S'";
//echo "SQL: $sql <br>\n";
		$rows = query($sql);
		if(count($rows) > 0){
			//Titulo já foi processado
			log::gravaLog($this->_arquivoLog,"Titulo ja baixado: $duplic / $prest");
			return false;
		}
//die( "ok<br>");								  	
		$info= $this->getNumTransVenda($duplic, $prest);
		$valorTitulo = $this->getValorTitulo($duplic, $prest);
		
		if($valorTitulo < $valorPago){
			//Não baixa título com valor pago maior que o título (provavelmente pagaram mais de uma parcela juntas - deve ser baixado manualmente)
			log::gravaLog($this->_arquivoLog,"Titulo com valor menor que o pago: $duplic / $prest");
			return false;
		}
		
		$numTransVenda = $info['transVenda'];
		$codCli = $info['codcli'];
		$funcionario = 68;
		
		$dados = array();
		$dados['filial'] = 1;
		$dados['codcli'] = $codCli;
		$dados['numTransVenda'] = $numTransVenda;
		$dados['historico'] = $historico;
		$dados['duplic'] = $duplic;
		$dados['prest'] = $prest;
		$dados['codcob'] = $codCob;
		$dados['valorPago'] = $valorPago;
		$dados['juros'] = $juros;
		$dados['dtpag'] = $dataPagamento;
		$dados['descontoV'] = $desconto;
		$dados['descontoP'] = $desconto == 0 ? 0 : ($desconto/$valorPago) * 100;
		$dados['multa'] = $multa;
		$dados['despBancarias'] = $despBancarias;
		$dados['despCartorio'] = $despCartorio;
		$dados['despOutros'] = $despOutros;
		$dados['despTotal'] = $dados['despBancarias'] + $dados['despCartorio'] + $dados['despOutros'];
		$dados['funcionario'] = $funcionario;
		$dados['codBaixa'] = $funcionario;
		$dados['obs2'] = '';
		$dados['obsTitulo'] = '';
		$dados['banco'] = $banco;
		$dados['codBanco'] = $codBanco;
		$dados['moeda'] = $moeda;
		
		$dados['contaCredito'] = 101;
		$dados['contaDebito'] = 1;
//log::gravaLog($this->_arquivoLog,print_r($dados,true),0,true);
//die();		
		if($numTrans == 0){
			$dados['numTrans'] = $this->getNumTrans();
		}else{
			$dados['numTrans'] = $numTrans;
		}
		
		$original = $this->getDadosTituloOrig($dados);
//print_r($dados);		
		if(($valorPago + $desconto) < $original['VALOR']){
//echo "Desdobramento<br>";
			//Desdobramento
			$this->updPCPREST($dados, $original,'desd');
			$prestPago = $this->insertPCPREST($dados, $original,$valorPago);
			$this->insertPCDESD($dados, $original, $prestPago);
			
			$dados['prest'] = $prestPago;
			$this->updPCPREST($dados, $original);
			$this->updPCLOGCR($duplic, $prestPago, $codCli, $numTransVenda, $funcionario);
			$this->updPCLOGCR($duplic, $prestPago, $codCli, $numTransVenda, $funcionario);
			
			$this->updSaldoBancario($banco, $moeda, $valorPago);
			
			// Se o nr da transação foi informado quer dizer que vai ser lote, e só um histórico será informado no fianl
			if($numTrans == 0){
				$this->updPCMOVCR($dados);
			}
			
			$valorAberto = $original['VALOR'] - $valorPago;
			$prestAberto = $this->insertPCPREST($dados, $original,$valorAberto,'aberto');
			$this->insertPCDESD($dados, $original, $prestAberto);
			if($moeda == 'LP'){
				$this->updPCLANC($dados,'perda', $valorPago);
			}
		}else{
//echo "baixa<br>";
			$this->updPCPREST($dados, $original);
		
			$this->updPCLOGCR($duplic, $prest, $codCli, $numTransVenda, $funcionario);
			$this->updPCLOGCR($duplic, $prest, $codCli, $numTransVenda, $funcionario);
		
			$this->updSaldoBancario($banco, $moeda, $valorPago);
		
			// Se o nr da transação foi informado quer dizer que vai ser lote, e só um histórico será informado no fianl
			if($numTrans == 0){
				$this->updPCMOVCR($dados);
			}
			
			if($dados['descontoV'] > 0 && $dados['valorPago'] == 0){
				$this->updPCLANC($dados,'baixaDesc', $dados['descontoV']);
			}elseif($dados['descontoV'] > 0){
				$this->updPCLANC($dados,'desconto', $dados['descontoV']);
			}
			if($dados['juros'] != 'NULL'){
				$this->updPCLANC($dados,'juros', $dados['juros']);
			}
			if($dados['despCartorio'] > 0){
				$this->updPCLANC($dados,'cartorio', $dados['despCartorio']);
			}
			if($dados['despBancarias'] > 0){
				$this->updPCLANC($dados,'bancaria', $dados['despBancarias']);
			}
			if($dados['despOutros'] > 0){
				$this->updPCLANC($dados,'outras', $dados['despOutros']);
			}
		}
		
		return true;
	}
	
	/**
	 * Realiza o lançamento do Histórico na PCMOVCR
	 * 
	 * @param array $dados
	 * @param string $tipo
	 */
	private function updPCMOVCR($dados, $tipo = 'D'){
		//$historico = "BAIXA REF. TITULO NRO. ".$dados['duplic']."-".$dados['prest']."(".$dados['codcob'].")";
		$saldo = $this->getSaldo($dados['codBanco'], $dados['moeda']);
		$info = $this->getNomeCliente($dados['codcli']);
		$nomeCli = $info['nome'];
		//$codPrinc = $info['principal'];
		
		$campos = [];
		$campos['NUMTRANS'] 	= $dados['numTrans'];
		$campos['DATA'] 		= '"TRUNC(SYSDATE)';
		$campos['CODBANCO'] 	= $dados['codBanco'];
		$campos['CODCOB'] 		= $dados['moeda'];
		$campos['VALOR'] 		= $dados['valorPago'];
		$campos['TIPO'] 		= $tipo;
		$campos['HISTORICO'] 	= $dados['historico'];
		$campos['NUMCARR'] 		= $dados['numTransVenda'];
		$campos['VLSALDO'] 		= $saldo;
		$campos['HORA'] 		= '"TO_CHAR(SYSDATE, \'HH24\')';
		$campos['MINUTO'] 		= '"TO_CHAR(SYSDATE, \'MI\')';
		$campos['CODFUNC'] 		= $dados['funcionario'];
		$campos['CODCONTADEB'] 	= $dados['contaDebito'];
		$campos['CODCONTACRED'] = $dados['contaCredito'];
		$campos['INDICE'] 		= 'A';
		$campos['HISTORICO2'] 	= $nomeCli;
		$campos['CODROTINALANC']= '1207';
		
		$campos['DUPLICBAIXA']	= $dados['duplic'];
		$campos['PRESTBAIXA']	= $dados['prest'];
		
		$sql = montaSQL($campos, 'PCMOVCR');
		
		$this->queryOracle($sql);
	}
	
	/**
	 * Realiza o lançamento do Histórico na PCMOVCR - Lote
	 * 
	 * @param string $numTrans
	 * @param string $banco
	 * @param string $moeda
	 * @param string $totalPago
	 * @param string $historico
	 * @param string $tipo
	 */
	public function updPCMOVCRLote($numTrans, $banco, $moeda, $totalPago, $historico, $tipo = 'D'){
		$saldo = $this->getSaldo($banco, $moeda);
		
		$campos = [];
		$campos['NUMTRANS'] 	= $numTrans;
		$campos['DATA'] 		= '"TRUNC(SYSDATE)';
		$campos['CODBANCO'] 	= $banco;
		$campos['CODCOB'] 		= $moeda;
		$campos['VALOR'] 		= $totalPago;
		$campos['TIPO'] 		= $tipo;
		$campos['HISTORICO'] 	= $historico;
		$campos['NUMCARR'] 		= 0;
		$campos['VLSALDO'] 		= $saldo;
		$campos['HORA'] 		= '"TO_CHAR(SYSDATE, \'HH24\')';
		$campos['MINUTO'] 		= '"TO_CHAR(SYSDATE, \'MI\')';
		$campos['CODFUNC'] 		= 68;
		$campos['CODCONTADEB'] 	= 1;
		$campos['CODCONTACRED'] = 101;
		$campos['INDICE'] 		= 'A';
		$campos['HISTORICO2'] 	= 'REF. BAIXA LOTE INTRANET';
		$campos['CODROTINALANC']= '1207';
		
		$campos['DUPLICBAIXA']	= '';
		$campos['PRESTBAIXA']	= '';
		
		$sql = montaSQL($campos, 'PCMOVCR');
		
		$this->queryOracle($sql);
	}
	
	public function getNumTrans(){
		$ret = 0;
		$sql = "SELECT NVL(PROXNUMTRANS,1) PROXNUMTRANS FROM PCCONSUM";// FOR UPDATE";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
			
			$sql = "UPDATE PCCONSUM SET PROXNUMTRANS = NVL(PROXNUMTRANS,1) + 1";
			$this->queryOracle($sql); 
		}
		
		return $ret;
	}
	
	private function getNumLanc(){
		$ret = 0;
		$sql = "SELECT NVL(PROXNUMLANC,1) PROXNUMLANC FROM PCCONSUM";// FOR UPDATE";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
			
			$sql = "UPDATE PCCONSUM SET PROXNUMLANC = NVL(PROXNUMLANC,1) + 1";
			$this->queryOracle($sql);
		}
		
		return $ret;
	}
	
	private function getNumTransVenda($duplicata, $prest){
		$ret = [];
		$sql = "SELECT DISTINCT NUMTRANSVENDA, CODCLI FROM PCPREST WHERE PREST = '$prest' AND DUPLIC = $duplicata";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$ret['transVenda'] = $rows[0][0];
			$ret['codcli'] = $rows[0][1];
		}
		
		return $ret;
	}
	
	private function getValorTitulo($duplicata, $prest){
		$ret = 0;
		$sql = "SELECT VALOR FROM PCPREST WHERE PREST = '$prest' AND DUPLIC = $duplicata";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	private function updPCPREST($dados, $original, $tipo = ''){
		if($tipo == 'desd'){
			$ret[] = array('CODCOB'				, 'T','DESD');
			$ret[] = array('VPAGO'				, 'N',$original['VALOR']);
			$ret[] = array('DTPAG'				, 'T',$original['DTVENC']);
			$ret[] = array('DTULTALTER'			, 'N','TRUNC(SYSDATE)');
			$ret[] = array('DTBAIXA'			, 'N','TRUNC(SYSDATE)');
			$ret[] = array('CODBAIXA'			, 'N',$dados['funcionario']);
			$ret[] = array('DTDESD'				, 'N','TRUNC(SYSDATE)');
			$ret[] = array('DTCXMOT'			, 'N','TRUNC(SYSDATE)');
			$ret[] = array('CODFUNCULTALTER'	, 'N',$dados['funcionario']);
			$ret[] = array('CODFUNCDESD'		, 'N',$dados['funcionario']);
			$ret[] = array('HORADESD'			, 'N',date('G'));
			$ret[] = array('MINUTODESD'			, 'N',date('i'));
			$ret[] = array('HORAFECHA'			, 'N',date('G'));
			$ret[] = array('MINUTOFECHA'		, 'N',date('i'));
			$ret[] = array('ROTDESD'			, 'N',1207);
			$ret[] = array('VLRDESPBANCARIAS'	, 'N',0);
			$ret[] = array('VLRDESPCARTORAIS'	, 'N',0);
			$ret[] = array('VLROUTROSACRESC'	, 'N',0);
			$ret[] = array('ROTINALANCULTALT'	, 'T','[INTRANET]');
			$ret[] = array('ROTINAPAG'			, 'T','[INTRANET]');
			$ret[] = array('ROTINADESD'			, 'T','[INTRANET]');
		}else{
			$ret[] = array('VPAGO'					, 'N',$dados['valorPago']);
			$ret[] = array('DTPAG'					, 'N',"TO_DATE('".$dados['dtpag']."','YYYYMMDD')");
			$ret[] = array('DTULTALTER'				, 'N','TRUNC(SYSDATE)');
			$ret[] = array('DTBAIXA'				, 'N','TRUNC(SYSDATE)');
			$ret[] = array('CODBAIXA'				, 'N',$dados['funcionario']);
			$ret[] = array('NUMTRANS'				, 'N',$dados['numTrans']);
			$ret[] = array('CODBANCO'				, 'N',$dados['codBanco']);
			$ret[] = array('CODCOBBANCO'			, 'T',$dados['moeda']);
			$ret[] = array('DTCXMOT'				, 'N','TRUNC(SYSDATE)');
			$ret[] = array('CODFUNCCXMOT'			, 'N',$dados['funcionario']);
			$ret[] = array('CODFUNCFECHA'			, 'N',$dados['funcionario']);
			$ret[] = array('CODFUNCULTALTER'		, 'N',$dados['funcionario']);
			$ret[] = array('CARTORIO'				, 'T','N');
			$ret[] = array('DATAHORAMINUTOBAIXA'	, 'N','SYSDATE');
			$ret[] = array('PROTESTO'				, 'T','N');
			$ret[] = array('VLRDESPBANCARIAS'		, 'N',0);
			$ret[] = array('VLRDESPCARTORAIS'		, 'N',0);
			$ret[] = array('VLRTOTDESPESASEJUROS'	, 'N',0);
			$ret[] = array('VLROUTROSACRESC'		, 'N',0);
			$ret[] = array('ROTINALANCULTALT'		, 'T','[INTRANET]');
			$ret[] = array('ROTINAPAG'				, 'T','[INTRANET]');
			$ret[] = array('VALORMULTA'				, 'N',0);
		}
		
		$sql = "
				UPDATE PCPREST
				SET
				";
		foreach ($ret as $n => $r){
			$sql .= $r[0].' = ';
			if($r[1] == 'N'){
				$sql .= $r[2];
			}else{
				$sql .= "'".$r[2]."'";
			}
			if($n + 1 < count($ret)){
				$sql .= ",\n";
			}
		}
		$sql .= "
				    WHERE
				      PREST = '".$dados['prest']."' AND
				      NUMTRANSVENDA = ".$dados['numTransVenda']."
			";
		$this->queryOracle($sql);
	}
	
	private function updPCLOGCR($duplic, $prest, $codcli, $transVenda, $func){
		if($duplic != '' && $prest != '' && $codcli != '' && $transVenda!= '' && $func != ''){
			$sql = "INSERT INTO pclogcr (codfilial, duplic, prest, data, rotina, codcli, numtransvenda, codfunc)
					VALUES (1, $duplic, '$prest', TRUNC(SYSDATE), 1207, $codcli, $transVenda, $func)";
			$this->queryOracle($sql);
		}
	}
	
	private function getSaldo($banco, $moeda){
		$saldo = 0;
		
		$sql = "SELECT VALOR, VALORCONCILIADO FROM PCESTCR WHERE CODBANCO = '$banco' AND CODCOB = '$moeda'";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$saldo = $rows[0][0];
		}

		return $saldo;
	}
	
	private function updPCLANC($dados, $tipo, $valor){
		$sql = "SELECT NVL(PROXNUMLANC,1) PROXNUMLANC FROM PCCONSUM";// FOR UPDATE";
		$rows = query4($sql);
		$sql = "UPDATE PCCONSUM SET PROXNUMLANC = NVL(PROXNUMLANC,1) + 1";
		$this->queryOracle($sql);
		$recnum = $rows[0][0];
		
		$nomeFunc = $this->getNomeFuncionario($dados['funcionario']);
		$info = $this->getNomeCliente($dados['codcli']);
		$nomeCli = $info['nome'];
		$codPrinc = $info['principal'];
		switch ($tipo) {
			case 'desconto':
				$historico = 'REF DESC. NA BAIXA DUPLIC.'.$dados['duplic'].'-'.$dados['prest'];
				$conta = '301004';
				break;
			case 'juros':
				$historico = 'REF JUROS NA BAIXA DUPLIC.'.$dados['duplic'].'-'.$dados['prest'];
				$conta = '301009';
				break;
			case 'cartorio':
				$historico = 'REF DESPESAS CARTORAIS NA BAIXA DUPLIC.';
				$conta = '401006';
				break;
			case 'bancaria':
				$historico = 'REF DESPESAS BANCÁRIAS NA BAIXA DUPLIC.';
				$conta = '401006';
				break;
			case 'outras':
				$historico = 'REF DESPESAS OUT.ACRESC. NA BAIXA DUPLIC';
				$conta = '401006';
				break;
			case 'perdas':
				$historico = 'REF BAIXA PERDAS DUPLIC. '.$dados['duplic'].'-'.$dados['prest'];
				$conta = '200001';
				break;
			case 'baixaDesc':
				//Baixa a desconto
				$historico = 'BAIXA A DESCONTO REF ACORDO COMERCIAL';
				$conta = '300034 ';
				break;
			default:
				$historico = '';
				$conta = '';
				break;
		}

		$campos = [];
		$campos['RECNUM'] 					= $recnum;
		$campos['DTLANC'] 					= '"TO_DATE(\''.$dados['dtpag'].'\',\'YYYYMMDD\')';
		$campos['HISTORICO'] 				= $historico;
		$campos['DUPLIC'] 					= $dados['prest'];
		$campos['LOCALIZACAO']				= $dados['numTransVenda'];
		$campos['CODFILIAL'] 				= 1;
		$campos['INDICE'] 					= 'A';
		$campos['NUMCHEQUE'] 				= 'NULL';
		$campos['TIPOLANC'] 				= 'C';
		$campos['TIPOPARCEIRO'] 			= 'C';
		$campos['NOMEFUNC'] 				= $nomeFunc;
		$campos['ASSINATURA'] 				= 'NULL';
		$campos['TIPOPAGTO'] 				= 'NULL';
		$campos['NUMDVDESTDOC'] 			= 'NULL';
		$campos['NUMCODBARRA'] 				= 'NULL';
		$campos['HISTORICO2'] 				= 'NULL';
		$campos['MOEDA'] 					= 'NULL';
		$campos['DVAG'] 					= 'NULL';
		$campos['PORTADORCHEQUE'] 			= 'NULL';
		$campos['NUMDIIMPORTACAO'] 			= 'NULL';
		$campos['ADIANTAMENTO'] 			= 'NULL';
		$campos['NFSERVICO'] 				= 'N';
		$campos['PREST'] 					= $dados['prest'];
		$campos['BOLETO'] 					= 'NULL';
		$campos['PAGTONOMEDOCLIENTE'] 		= 'NULL';
		$campos['OBSBLOQ'] 					= 'NULL';
		$campos['AGENDAMENTO']				= 'NULL';
		$campos['NUMCONTRATOCAMBIO'] 		= 'NULL';
		$campos['IDCONTROLEEMBARQUE'] 		= 'NULL';
		$campos['FORMAPGTO'] 				= 'NULL';
		$campos['CODROTINACAD'] 			= '1207';
		$campos['DTMOEDA'] 					= 'NULL';
		$campos['CODROTINAALT'] 			= 'PCSIS1207.EXE';
		$campos['PARCELA'] 					= 'NULL';
		$campos['NUMNOTA'] 					= $dados['duplic'];
		$campos['CODFORNEC'] 				= $dados['codcli'];
		$campos['VLRUTILIZADOADIANTFORNEC'] = 0;
		$campos['TIPOSERVICO'] 				= 'NULL';
		$campos['IDENTIFICADORFGTS'] 		= 'NULL';
		$campos['LACREDIGCONECSOCIAL'] 		= 'NULL';// -- Lacre Digital Conectividade Social
		$campos['OPCAOPAGAMENTOIPVA'] 		= 0;
		$campos['VALOR'] 					= $valor;
		$campos['CODCONTA'] 				= $conta;
		$campos['VPAGO'] 					= $valor;
		$campos['CODFORNECPRINC'] 			= $codPrinc;
		$campos['CODFUNCBAIXA'] 			= $dados['funcionario'];
		$campos['NUMTRANS'] 				= $dados['numTrans'];
		$campos['CODROTINABAIXA'] 			= '1207';
		$campos['DTVENC'] 					= $dados['dtpag'];
		$campos['DTPAGTO'] 					= $dados['dtpag'];
		$campos['DTEMISSAO'] 				= $dados['dtpag'];
		$campos['DTCOTACAO'] 				= 'NULL';
		$campos['RECNUMBAIXA'] 				= 0;

		$sql = montaSQL($campos, 'PCLANC');
		$this->queryOracle($sql);
	}
	
	private function insertPCPREST($dados, $original, $valor = 0, $tipo = ''){
		$prest = $this->getNumProxPrest($original['DUPLIC']);
		
		$ret[] = array('DUPLIC'					, 'N',$original['DUPLIC']);
		$ret[] = array('PREST'					, 'T',$prest);
		$ret[] = array('CODCLI'					, 'N',$original['CODCLI']);
		$ret[] = array('DTVENC'					, 'N',"TO_DATE('".$original['DTVENC']."','YYYY-MM-DD')");
		$ret[] = array('CODCOB'					, 'T',$original['CODCOB']);
		
		$ret[] = array('VALOR'					, 'N',$valor);
		$ret[] = array('VALORORIG'				, 'N',$valor);
		
		$ret[] = array('DTEMISSAO'				, 'N',"TO_DATE('".$original['DTEMISSAO']."','YYYY-MM-DD')");
		$ret[] = array('OPERACAO'				, 'T',$original['OPERACAO']);
		$ret[] = array('PERDESC'				, 'N',$original['PERDESC']);
		$ret[] = array('CODFILIAL'				, 'N',$original['CODFILIAL']);
		$ret[] = array('STATUS'					, 'T',$original['STATUS']);
		$ret[] = array('CODUSUR'				, 'N',$original['CODUSUR']);
		$ret[] = array('VALORDESC'				, 'N',$original['VALORDESC']);
		$ret[] = array('DTULTALTER'				, 'N','TRUNC(SYSDATE)');
		$ret[] = array('BOLETO'					, 'N',$original['BOLETO']);
		$ret[] = array('NUMCAR'					, 'N',$original['NUMCAR']);
		$ret[] = array('DTDESD'					, 'N','TRUNC(SYSDATE)');
		$ret[] = array('DTFECHA'				, 'N',"TO_DATE('".$original['DTFECHA']."','YYYY-MM-DD')");
		$ret[] = array('CODSUPERVISOR'			, 'N',$original['CODSUPERVISOR']);
		$ret[] = array('DTVENCORIG'				, 'N',"TO_DATE('".$original['DTVENCORIG']."','YYYY-MM-DD')");
		$ret[] = array('NUMTRANSVENDA'			, 'N',$original['NUMTRANSVENDA']);
		$ret[] = array('CODCOBORIG'				, 'T',$original['CODCOBORIG']);
		$ret[] = array('PERCOM'					, 'N',$original['PERCOM']);
		
		//Calcula proporcional
		$valorProporcional = $this->calculaProporcional($original['VALOR'],$original['VALORLIQCOM'],$valor);
		$ret[] = array('VALORLIQCOM'			, 'N',$valorProporcional);
		
		$ret[] = array('CODFILIALNF'			, 'N',$original['CODFILIALNF']);
		$ret[] = array('CODFUNCULTALTER'		, 'N',$dados['funcionario']);
		$ret[] = array('NUMPED'					, 'N',$original['NUMPED']);
		$ret[] = array('CODFUNCDESD'			, 'N',$dados['funcionario']);
		$ret[] = array('HORADESD'				, 'N',date('G'));
		$ret[] = array('MINUTODESD'				, 'N',date('i'));
		$ret[] = array('HORAFECHA'				, 'N',date('G'));
		$ret[] = array('MINUTOFECHA'			, 'N',date('i'));
		$ret[] = array('ROTDESD'				, 'N',1207);
		$ret[] = array('AGRUPADO'				, 'T','N');
		$ret[] = array('DTEMISSAOORIG'			, 'N',"TO_DATE('".$original['DTEMISSAOORIG']."','YYYY-MM-DD')");
		$ret[] = array('BLOQDESDEMITENTEDIF'	, 'T',$original['BLOQDESDEMITENTEDIF']);
		$ret[] = array('CODEMITENTEPEDIDO'		, 'N',$original['CODEMITENTEPEDIDO']);
		$ret[] = array('ROTINALANC'				, 'N',1207);
		//$ret[] = array('EQUIPLANC'			, '','ERP1');
		//$ret[] = array('FUNCLANC'				, '','thiel');
		$ret[] = array('DTRECEBIMENTOPREVISTO'	, 'N',"TO_DATE('".$original['DTVENC']."','YYYY-MM-DD')");
		$ret[] = array('HISTORIGDESDOBRAMENTO'	, 'T',$original['DUPLIC'].'-'.$original['PREST'].';');
		
		if($tipo == 'aberto'){
			$info = $this->getNomeCliente($original['CODCLI']);
			$ret[] = array('OBS2'				, '',$info['nome']);
		}
		
		$campos = array();
		$valores = array();
		foreach ($ret as $n => $r){
			$campos[] = $r[0];
			if($r[1] == 'N'){
				$valores[] = $r[2];
			}else{
				$valores[] = "'".$r[2]."'";
			}
		}
		$sql = "Insert Into PCPREST(
				".implode(",\n", $campos)."
	    		) Values (
		     	".implode(",\n", $valores)."	 
			    )

				";
		$this->queryOracle($sql);
		
		return $prest;
	}
	
	private function calculaProporcional($valorOriginal,$liquidoOriginal,$valor){
		$ret = $liquidoOriginal * ($valor/$valorOriginal);
		
		return $ret;
	}

	/*
	 * Retorna os daodos do titulo original
	 */
	private function getDadosTituloOrig($dados, $duplic = 0, $prest =0){
		$ret = array();
		if(!isset($dados['duplic'])){
			$dados['duplic'] = $duplic;
			$dados['prest'] = $prest;
		}
		$sql = "SELECT * FROM PCPREST WHERE DUPLIC = ".$dados['duplic']." AND PREST = '".$dados['prest']."'";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			foreach ($rows[0] as $key => $valor){
				if(gettype($key) == 'string'){
					$ret[$key] = $valor;
				}
			}
		}
		return $ret;
	}
	
	private function insertPCDESD($dados, $original, $prest){
		
		$sql = "
			    Insert Into PCDESD(
			    NUMTRANSVENDADEST, 
			    PRESTDEST, 
			    CODCLIDEST, 
			    NUMTRANSVENDAORIG, 
			    PRESTORIG, 
			    CODCLIORIG, 
			    DTLANC, 
			    CODFUNCCXMOT, 
			    CODROTINA
			    ) Values (
			     ".$dados['numTransVenda'].", 
			     $prest, 
			     ".$original['CODCLI'].", 
			     ".$dados['numTransVenda'].", 
			     ".$original['PREST'].", 
			     ".$original['CODCLI'].", 
			     TRUNC(SYSDATE), 
			     ".$dados['funcionario'].", 
			     1207
			    )

			";
		$this->queryOracle($sql);
	}
	
	private function updSaldoBancario($banco, $cobranca, $valor){
		if($banco != '' && $cobranca != '' && $valor != 0){
			$sql = "UPDATE PCESTCR SET VALOR = NVL(VALOR,0) + $valor WHERE CODBANCO = $banco AND CODCOB = '$cobranca'";
			
			$this->queryOracle($sql);
		}
	}
	
	private function updTransacao($numTransvenda, $prest, $numTrans){
		if($numTransvenda != '' && $prest != '' && $numTrans != ''){
			$sql = "UPDATE PCPREST SET NUMTRANS = $numTrans WHERE PREST = '$prest' AND NUMTRANSVENDA = $numTransvenda";
			$this->queryOracle($sql);
		}
	}
	
	private function getNomeCliente($codcli){
		$ret = array();;
		
		$sql = "SELECT CLIENTE, CODCLIPRINC FROM PCCLIENT WHERE CODCLI = $codcli";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$ret['nome'] = $rows[0][0];
			$ret['principal'] = $rows[0][1];
		}
		
		return $ret;
	}
	
	private function getNomeFuncionario($cod){
		$ret = '';
		
		$sql = "SELECT NOME FROM PCEMPR WHERE MATRICULA = $cod";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	private function getNumProxPrest($duplicata){
		$ret = 1;
		$sql = "SELECT MAX(PREST) FROM PCPREST WHERE DUPLIC = $duplicata";
		$rows = $this->queryOracle($sql);
//print_r($rows);
//echo "Atual Nr: ".$rows[0][0]." \n";
		if(count($rows) > 0){
			$ret = $rows[0][0] + 1;			
		}
//echo "Proximo Nr: $ret \n";
		return $ret;
	}

	
	private function queryOracle($sql){
		$rows = array();
//echo "\nSQL: $sql <br>\n\n";

		if($this->_trace){
			log::gravaLog($this->_arquivoLog, $sql);
		}
		
		if($this->_teste){
			$rows = query5($sql);
		}else{
			$rows = query4($sql);
		}
		
		return $rows;
	}
	
	/*
	 * Verifica se o titulo foi baixado
	 */
	function verificaBaixaTitulo($duplic, $prest, $codcob){
		$ret = array();
		$sql = "
				SELECT VPAGO, DTPAG, CODFUNCCXMOT, PCEMPR.NOME_GUERRA
				FROM 
				    PCPREST,
				    PCEMPR
				WHERE 
				    CODFUNCCXMOT = PCEMPR.MATRICULA (+)
				    AND DUPLIC = $duplic 
				    AND PREST = '$prest'
				    AND CODCOB = '$dep'
			";
		$rows = $this->queryOracle($sql);
		if(count($rows) > 0){
			$ret['valor'] = $rows[0]['VPAGO'];
			$ret['data'] = datas::dataMS2S($rows[0]['DTPAG']);
			$ret['func'] = $rows[0]['NOME_GUERRA'];
		}
		
		return $ret;
	}
}