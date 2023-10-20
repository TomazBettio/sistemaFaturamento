<?php
/*
 * Data Criacao:
 * Autor: Verticais - Thiel
 *
 * Descricao: Envia email para os ERCs 30 minutos depois que um pedido seja bloqueado, e novamente depois de 2 horas
 *
 * Alteracoes;
 *
 */
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class pedidos_bloqueados{
	var $funcoes_publicas = array(
		'index'	=> true,
//		'ajustaHorarioEmails' => true,
	);
	
	//Progrma
	private $_programa = '';
	
	//Titulo
	private $_titulo = '';
	
	//Relatorio
	private $_relatorio;
	
	private $_rca;
	
	//Indica se é teste
	private $_teste;
	
	//Nome de clientes
	private $_clientes = [];
	
	//Copiar email para
	private $_copiaEmail = '';
	
	//Motivos de bloqueios
	private $_motivos = [];
	
	function __construct(){
		$this->_teste = false;
		
		$this->_programa = 'pedidos_bloqueados_log';
		$this->_titulo = 'Pedidos Bloqueados';
		
		$this->_copiaEmail = '';
		
		$this->_rca = [];
	}
	
	function index(){
		$this->getRCA();
		$ret = '';
		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$this->_relatorio = new relatorio01($param);
		$this->setColunas();
		if(true){
			$this->setFiltro();
		}
		$filtro = $this->_relatorio->getFiltro();
		
		if(!$this->_relatorio->getPrimeira()){
			
			$dados = $this->getDados2($filtro);
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	private function getDados2($filtro){
		$ret = [];
		
		$diaIni = isset($filtro['DIAINI']) && !empty($filtro['DIAINI']) ? $filtro['DIAINI'] : date('Ymd');
		$DiaFim = isset($filtro['DIAFIM']) && !empty($filtro['DIAFIM']) ? $filtro['DIAFIM'] : date('Ymd');
		$gd = isset($filtro['GD']    ) ? $filtro['GD']     : '';
		$erc = isset($filtro['ERC']   ) ? $filtro['ERC']    : '';
		$pedido = isset($filtro['PEDIDO']) ? $filtro['PEDIDO'] : '';
		$codcli = isset($filtro['CODCLI']) ? $filtro['CODCLI'] : '';
		$status = isset($filtro['STATUS']) ? $filtro['STATUS'] : '';
		
		$where = '';
		
		if(!empty($gd)){
			$where .= " AND gd IN ($gd)";
		}
		if(!empty($erc)){
			$where .= " AND erc IN ($erc)";
		}
		if(!empty($pedido)){
			$where .= " AND pedido IN ($pedido)";
		}
		if(!empty($codcli)){
			$where .= " AND codcli IN ($codcli)";
		}
		if(!empty($$status)){
			$where .= " AND status = '$status'";
		}
		
		$sql = "SELECT * FROM gf_pedidos_bloqueados_log WHERE data >= '$diaIni' AND data <= '$DiaFim' $where";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['pedido'	] = $row['pedido'];
				$temp['data'	] = $row['data'];
				$temp['hora'	] = $row['hora'];
				$temp['valor'	] = $row['valor'];
				$temp['gd'		] = $row['gd'];
				$temp['gd_nome'	] = $this->_rca[$row['erc']]['super_nome'] ?? '';
				$temp['erc'		] = $row['erc'];
				$erc_nome = $this->_rca[$row['erc']]['nome'] ?? '';
				$temp['erc_nome'] = substr($erc_nome, 0, 40);
				$temp['codcli'	] = $row['codcli'];
				$temp['cli_nome'] = $this->getNomeCliente($row['codcli']);
				$temp['obs'		] = $row['obs'];
				$temp['motivo'	] = $row['motivo'];
				$temp['tipo'	] = $row['tempo'];
				$temp['status'	] = $row['status'];
				$temp['envio'	] = $row['email'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function setFiltro(){
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Dia de'	, 'variavel' => 'DIAINI'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Dia Até'	, 'variavel' => 'DIAFIM'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'GD'	    , 'variavel' => 'GD'	    ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'ERC'		, 'variavel' => 'ERC'		,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Pedido'	, 'variavel' => 'PEDIDO'	,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Cliente'	, 'variavel' => 'CODCLI'	,'tipo' => 'T', 'tamanho' => '200', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '7', 'pergunta' => 'Status'	, 'variavel' => 'STATUS'	,'tipo' => 'A', 'tamanho' => '200', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'Enviado=Enviado;Erro=Erro;Sem email=Sem email'));
	}
	
	private function setColunas(){
		$this->_relatorio->addColuna(array('campo' => 'pedido'	, 'etiqueta' => 'Pedido'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'data'	, 'etiqueta' => 'Data'		, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'hora'	, 'etiqueta' => 'Hora'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'valor'	, 'etiqueta' => 'Valor'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'gd'		, 'etiqueta' => 'GD'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'gd_nome'	, 'etiqueta' => 'Nome'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc'		, 'etiqueta' => 'ERC'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'erc_nome', 'etiqueta' => 'Nome'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'codcli'	, 'etiqueta' => 'Cliente'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'cli_nome', 'etiqueta' => 'Nome'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'obs'		, 'etiqueta' => 'Obs'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'motivo'	, 'etiqueta' => 'Motivo'	, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'tipo'	, 'etiqueta' => 'Minutos'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'status'	, 'etiqueta' => 'Status'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'envio'	, 'etiqueta' => 'Envio'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	}
	
	function schedule(){
		$this->getRCA();
		
		$hm = date('G');
		if( $hm < 9 || ($hm > 20 && date('i') > 30)){
			log::gravaLog("pedidos_bloqueados", "Execucao após horário: $hm - ".date('N'));
			//            return;
		}
		
		log::gravaLog('pedidos_bloqueados', 'Executando');
		$this->atualizaDados();
		$dados = $this->getDados();
		if(is_array($dados) && count($dados) > 0){
			foreach ($dados as $rca => $d){
				$email = isset($this->_rca[$rca]['email']) ? $this->_rca[$rca]['email'] : '';
				$this->montaTabela($d, $rca, $email);
			}
		}
		
		log::gravaLog('pedidos_bloqueados', 'Executado com sucesso');
		echo "Executado com sucesso<br>\n";
	}
	
	function montaTabela($dados, $rca, $email){
		$ret = '';
		if(is_array($dados) && count($dados) > 0){
			$param = [];
			$param['titulo'] = 'pedidos_bloqueados';
			$tabela = new relatorio01( $param);
			$titulo = "Pedidos Bloqueados - ERC $rca - ".$this->_rca[$rca]['nome'];
			$mensagem = "<b>E-MAIL AUTOMÁTICO, NÃO RESPONDER</b><br>";
			$tabela->setTitulo($titulo);
			$tabela->addColuna(array('campo' => 'pedido'		, 'etiqueta' => 'Pedido'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$tabela->addColuna(array('campo' => 'codcli'		, 'etiqueta' => 'Codcli'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$tabela->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
			$tabela->addColuna(array('campo' => 'data'		    , 'etiqueta' => 'Data'			    , 'tipo' => 'D', 'width' =>  80, 'posicao' => 'C'));
			$tabela->addColuna(array('campo' => 'valor'		    , 'etiqueta' => 'Valor'			    , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'C'));
			$tabela->addColuna(array('campo' => 'obs'		    , 'etiqueta' => 'Obs'				, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
			$tabela->addColuna(array('campo' => 'motivo'	    , 'etiqueta' => 'Motivo'			, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
			
			$secao = 0;
			if(isset($dados[30])){
				$tabela->setTituloSecao($secao, "Pedidos Bloqueados a 30 minutos");
				$tabela->setDados($dados[30], $secao);
				$tabela->setAuto(true, $secao);
				$secao++;
			}
			
			if(isset($dados[120])){
				$tabela->setTituloSecao($secao, "Pedidos Bloqueados a 2 horas");
				$tabela->setDados($dados[120], $secao);
				$tabela->setAuto(true, $secao);
				$secao++;
			}
			
			if($this->_teste){
				$param = [];
				$param['msgIni'] = $mensagem;
				$envio = $tabela->enviaEmail('suporte@thielws.com.br', $titulo, $param);
				if($envio){
					$this->atualizaLog('Enviado', $dados);
				}else{
					$this->atualizaLog('Erro', $dados);
				}
			}else{
				if(!empty($email)){
					$param = [];
					$param['msgIni'] = $mensagem;
					$envio = $tabela->enviaEmail($email.$this->_copiaEmail, $titulo,$param);
					//$envio = $tabela->enviaEmail('suporte@thielws.com.br', $titulo,'',$mensagem);
					if($envio){
						log::gravaLog('pedidos_bloqueados', "Erro enviado email ERC $rca - ".$this->_rca[$rca]['nome']." - $email ");
						$this->atualizaLog('Enviado', $dados);
					}else{
						log::gravaLog('pedidos_bloqueados', "Enviado email ERC $rca - ".$this->_rca[$rca]['nome']." - $email ");
						$this->atualizaLog('Erro', $dados);
					}
				}else{
					log::gravaLog('pedidos_bloqueados', "ERC $rca - ".$this->_rca[$rca]['nome']."sem email ");
					$this->atualizaLog('Sem email', $dados);
				}
			}
		}
		return $ret;
	}
	
	private function atualizaLog($status, $dados){
		$pedidos = [];
		if(isset($dados[30])){
			foreach ($dados[30] as $ped){
				$ped['gd'] = $this->_rca[$ped['erc']]['super'];
				$ped['status'] = $status;
				$ped['tempo'] = '30';
				$pedidos[] = $ped;
			}
		}
		
		if(isset($dados[120])){
			foreach ($dados[120] as $ped){
				$ped['gd'] = $this->_rca[$ped['erc']]['super'];
				$ped['status'] = $status;
				$ped['tempo'] = '120';
				$pedidos[] = $ped;
			}
		}
		
		if(count($pedidos) > 0){
			foreach ($pedidos as $ped){
				$this->gravaLog($ped);
			}
		}
	}
	
	private function gravaLog($dados){
		$campos = [];
		
		$campos['pedido'] 	= $dados['pedido'];
		$campos['erc'] 		= $dados['erc'];
		$campos['gd'] 		= $dados['gd'];
		$campos['codcli'] 	= $dados['codcli'];
		$campos['data'] 	= $dados['data'];
		$campos['hora'] 	= $dados['hora'];
		$campos['obs'] 		= $dados['obs'];
		$campos['motivo'] 	= $dados['motivo'];
		$campos['valor'] 	= $dados['valor'];
		$campos['email'] 	= datas::getTimeStampMysql();
		$campos['tempo'] 	= $dados['tempo'];
		$campos['status'] 	= $dados['status'];
		
		$sql = montaSQL($campos, 'gf_pedidos_bloqueados_log');
		query($sql);
	}
	
	private function getDados(){
		$ret = [];
		
		$atual = date('G:i');
		$minutos30 = datas::somaTempo($atual, '00:30', 1, false);
		$minutos120 = datas::somaTempo($atual, '02:00',1, false);

		$hoje = date('Ymd');
		
		//30 minutos - Primeiro email
		$sql = "SELECT * FROM gf_pedidos_bloqueados WHERE bloqueado = 'S' AND data = '".$hoje."' AND hora <= '$minutos30'  AND hora > '$minutos120'  AND email1 IS NULL AND email2 IS NULL";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$row['cliente'] = $this->getNomeCliente($row['codcli']);
				$ret[$row['erc']][30][] = $row;
				$this->atualizaEmailPedido($row['pedido'], 'email1');
			}
		}
		
		//120 minutos - segundo email
		$sql = "SELECT * FROM gf_pedidos_bloqueados WHERE bloqueado = 'S' AND data = '".$hoje."' AND hora <= '$minutos120' AND email2 IS NULL";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$row['cliente'] = $this->getNomeCliente($row['codcli']);
				$ret[$row['erc']][120][] = $row;
				$this->atualizaEmailPedido($row['pedido'], 'email2');
			}
		}
		
		return $ret;
	}
	
	private function atualizaDados(){
		$ret = [];
		
		//Marca todos os pedidos como não bloqueados
		$this->marcaDesbloqueado();
		
		$sql = "
				select  pcpedc.numped,
				        pcpedc.data,
				        pcpedc.HORA,
				        pcpedc.MINUTO,
				        pcpedc.codcli,
				        pcclient.cliente,
				        pcpedc.vltotal,
				        pcpedc.obs,
				        pcpedc.obs1,
				        pcpedc.obs2,
						pcpedc.log,
				        pcpedc.numvolume,
				        pcpedc.motivoposicao,
				        pcclient.codpraca,
				        pcpraca.praca,
				        pcclient.codfornecfrete,
				        pcfornec.fornecedor,
				        pcpedc.totpeso,
				        pcpedc.totvolume,
				        pcpedc.codusur,
						PCPEDC.CODMOTIVO
				from    pcpedc,
				        pcclient,
				        pcpraca,
				        pcfornec
				where pcpedc.posicao = 'B'
				    AND PCPEDC.dtcancel IS NULL
				    and pcpedc.codcli = pcclient.codcli (+)
				    and pcclient.codpraca = pcpraca.codpraca (+)
				    and pcclient.codfornecfrete = pcfornec.codfornec (+)
					and pcpedc.data > to_date('20220508','YYYYMMDD')
   		";
		//$sql .= $this->getPedidosBloqueados();
		$rows = query4($sql);
		//print_r($rows);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->incluiPedido($row);
			}
		}
		return $ret;
	}
	
	private function incluiPedido($ped){
		$pedido = $ped['NUMPED'];
		$incluido = $this->verificaEnviado($pedido);
		
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		
		if($incluido){
			//echo "Pedido $pedido  <br>\n";
			//Cadastra o pedido
			$campos = [];
			$campos['pedido'] 	= $pedido;
			$campos['erc'] 		= $ped['CODUSUR'];
			$campos['codcli'] 	= $ped['CODCLI'];
			$campos['data'] 	= datas::dataMS2S($ped['DATA']);
			$minuto = $ped['MINUTO'] < 10 ? '0'.$ped['MINUTO'] : $ped['MINUTO'];
			$hora = $ped['HORA'] < 10 ? '0'.$ped['HORA'] : $ped['HORA'];
			$campos['hora'] 	= $hora.':'.$minuto;
			$campos['obs'] 		= $ped['OBS'] . ' ' . $ped['OBS1'] . ' ' . $ped['OBS2'];
			$campos['motivo']	= $this->getMotivoBloqueio($ped['CODMOTIVO']); //$ped['MOTIVOPOSICAO'];
			$campos['valor'] 	= $ped['VLTOTAL'];
			
			$sql = montaSQL($campos, 'gf_pedidos_bloqueados');
			log::gravaLog('pedidos_bloqueados_sql',  $sql);
			query($sql);
		}else{
			$campos = [];
			$campos['obs'] 		= $ped['OBS'] . ' ' . $ped['OBS1'] . ' ' . $ped['OBS2'];
			$campos['motivo']	= $this->getMotivoBloqueio($ped['CODMOTIVO']);
			$campos['bloqueado']= 'S';
			
			$sql = montaSQL($campos, 'gf_pedidos_bloqueados','UPDATE'," pedido = '$pedido' ");
			//    		log::gravaLog('pedidos_bloqueados_sql',  $sql);
			query($sql);
		}
	}
	
	private function verificaEnviado($pedido){
		$ret = true;
		$sql = "select pedido from gf_pedidos_bloqueados WHERE pedido = '$pedido'";
		$rows = query($sql);
		if(isset($rows[0][0])){
			$ret = false;
		}
		return $ret;
	}
	
	private function marcaDesbloqueado(){
		$sql = "UPDATE gf_pedidos_bloqueados SET bloqueado = 'N' WHERE bloqueado = 'S'";
		query($sql);
	}
	
	private function getRCA(){
		$vend = getListaEmailGF('rca', true, '', false);
		if(is_array($vend) && count($vend) > 0){
			foreach ($vend as $v){
				$erc = $v['rca'];
				$this->_rca[$erc]['nome'] = $v['nome'];
				$this->_rca[$erc]['email'] = $v['email'];
				$this->_rca[$erc]['super'] = $v['super'];
				$this->_rca[$erc]['super_nome'] = $v['super_nome'];
				$this->_rca[$erc]['super_email'] = $v['super_email'];
			}
		}
	}
	
	private function getNomeCliente($codcli){
		if(!isset($this->_clientes[$codcli])){
			$sql = "SELECT cliente FROM PCCLIENT WHERE codcli = $codcli";
			$rows = query4($sql);
			if(isset($rows[0][0])){
				$this->_clientes[$codcli] = $rows[0][0];
			}else{
				$this->_clientes[$codcli] = '';
			}
		}
		
		return $this->_clientes[$codcli];
	}
	
	private function atualizaEmailPedido($pedido, $campo){
		$sql = "UPDATE gf_pedidos_bloqueados SET $campo = '".datas::getTimeStampMysql()."' WHERE pedido = '$pedido'";
		query($sql);
	}
	
	private function getMotivoBloqueio($mot){
		$ret = 'Sem motivo indicado';
		if(!empty($mot)){
			if(!isset($this->_motivos[$mot])){
				$sql = "SELECT DESCRICAO FROM PCMOTBLOQUEIO WHERE CODMOTIVO = $mot";
				$rows = query4($sql);
				
				if(isset($rows[0][0])){
					$this->_motivos[$mot] = $rows[0][0];
					$ret = $this->_motivos[$mot];
				}
			}
		}
				
		return $ret;
	}
	
	/**
	 * 12/06/23 - Thiel
	 * Função desenvolvida para ajustar o horário de envio dos emails, pois na migração para a intranet4
	 * o servidor ficou com time zone 0, e os emails com 3 horas a menos
	 */
	public function ajustaHorarioEmails() {
		$emails = [];
		
		$sql = "SELECT * FROM gf_pedidos_bloqueados_log WHERE data >= '20230101' ORDER BY `id` DESC";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$horaEnvio = substr($row['email'], 11,5);
				if($horaEnvio <= $row['hora']){
					$temp = [];
					$temp['id'] = $row['id'];
					$temp['hora'] = $row['hora'];
					$temp['email'] = $row['email'];
					$temp['tempo'] = $row['tempo'];
					$temp['envio'] = $horaEnvio;
					
					$emails[] = $temp;
				}
			}
		}

		if(count($emails) > 0){
			foreach ($emails as $em){
				if($em['tempo'] == '30'){
					$correto = datas::somaTempo($em['hora'], '00:30', 1, true);
				}else{
					$correto = datas::somaTempo($em['hora'], '02:00', 1, true);
				}
				
				$email = substr_replace($em['email'],$correto, 11, 5);
				
				$sql = "UPDATE gf_pedidos_bloqueados_log SET email = '$email' WHERE id = ".$em['id'];
				query($sql);
			}
		}
	}
}