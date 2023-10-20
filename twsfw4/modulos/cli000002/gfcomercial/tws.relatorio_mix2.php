<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class relatorio_mix2{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	//Indica se é teste
	private $_teste;
	
	//Indica se deve enviar email para os ERC quando for teste
	private $_testeERC;
	
	//Relatorio
	private $_relatorio;
	
	var $_programa;
	var $_titulo;
	var $_totais;
	var $_medias;
	var $_email_geral;
	
	var $_lista_gd = [];
	
	var $_schedule_excluidos;
	var $_schedule_ol;
	var $_schedule_cadastro;
	
	//Dados dos ERCs
	private $_erc = [];
	
	//Dados dos GDs
	private $_gd = [];
	
	//Dados dos clientes
	private $cliente = [];
	
	//Nr clientes totais ou clientes atendidos?
	private $_totalClientes;
	
	//Quantidade de clientes dos ERCs
	private $_quantClientesERC;
	
	//Venda PDA
	private $_vendaPDA = [];
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = true;
		$this->_testeERC = true;
		
		$this->_programa = 'relatorio_mix2';
		$this->_titulo = 'Relatório Mix';
		
		$this->_totalClientes = 'total';
		
		if(false){
			$OL = '1=Sem OL;2=Somente OL;3=Ambos';
			$cadastro = '1=Cadastro;2=Pedido';
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'			, 'variavel' => 'DATAINI'			,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'			, 'variavel' => 'DATAFIM'			,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'OL'	        	, 'variavel' => 'OL'	    	,'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => $OL));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Fonte do ERC'		, 'variavel' => 'CADASTRO'		,'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => $cadastro));
			$help = 'Indica se a média do ERC deve ser calculada em função do número total de clientes cadastrados para seu código ou somente para a quantidade de clientes positivados.';
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Média por'			, 'variavel' => 'MEDIA'	,'tipo' => 'T', 'tamanho' => '200', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => $help, 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'total=Nr. Total de Clientes;positivados=Nr.Clientes Positivados'));
		}
		
		$this->_totais = array();
		$this->_medias = array();
		
		$this->_email_geral = '';
		$this->_schedule_excluidos = '525;541;542;590;600;611;699;716;725;759;774;120';
		$this->_schedule_ol = 1;
		$this->_schedule_cadastro = 2;
		
		$param = [];
		$param['programa']	= $this->_programa;
		$param['titulo']	= $this->_titulo;
		$this->_relatorio = new relatorio01($param);
		
		$this->_relatorio->addColuna(array('campo' => 'gd_cod'		, 'etiqueta' => 'GD'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'gd_nome'		, 'etiqueta' => 'Nome GD'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc_cod'		, 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'erc_nome'	, 'etiqueta' => 'Nome ERC'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'cliente_cod'	, 'etiqueta' => 'Código Cliente', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'cliente_nome', 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'total'		, 'etiqueta' => 'Venda Total'	, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'venda_pda'	, 'etiqueta' => 'Venda ION'		, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'mix'			, 'etiqueta' => 'Mix'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'unidades'	, 'etiqueta' => 'Unidades'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'prec_unidade', 'etiqueta' => 'Preço Unidade'	, 'tipo' => 'V', 'width' =>  120, 'posicao' => 'D'));
		
		$this->_erc = getListaEmailGF('rca', true, '', true, 'erc');
		
	}
	
	function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		if(!$this->_relatorio->getPrimeira()){
			$dataIni 	= $filtro['DATAINI'] ?? '';
			$dataFim 	= $filtro['DATAFIM'] ?? '';
			$ol			= $filtro['OL'] ?? '3';
			$cadastro	= $filtro['CADASTRO'] ?? '2';
			$media		= $filtro['MEDIA'] ?? 'total';

			
			$this->_relatorio->setToExcel(true);
			
			$this->_totalClientes = $filtro['MEDIA'] == 'total'? 'total' : 'positivados';
			$dados = $this->getDados2($dataIni, $dataFim, $ol, $cadastro, $media);
			$this->_relatorio->setDados($dados);
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	
	//------------------------------------------------------------------------------------------------------------------------------------------------
	
	private function getDados2($dataIni, $dataFim, $ol, $cadastro, $media){
		$ret = [];
		
		if($this->_totalClientes == 'total'){
			$this->getMumeroClientes();
		}
		
		log::gravaLog('relatorio_mix2', "getDados2 - Nivel 1 - Cliente");

		if($cadastro == 1 || $cadastro == '1'){
			$campos = array('ERCCLI', 'CODCLI');
		}else{
			$campos = array('CODUSUR', 'CODCLI');
		}
		
		if(empty($dataIni)){
			$dataIni = date('Ym',mktime()).'01';
		}
		if(empty($dataFim)){
			$dataFim = $ret = date('Ymt',mktime());
		}
		
		$vendaPDA = $this->getVendaPda($dataIni, $dataFim, $cadastro, $campos);
		
		$param = [];
		$param['depto'] = '1,12';
		$param['bonificacao'] = false;
		
		if($ol != 3 && $ol != '3'){
			if($ol == 1 && $ol == '1'){
				$param['origem'] = 'NOL';
			}
			if($ol == 2 && $ol == '2'){
				$param['origem'] = 'OL';
			}
		}
		
		$rows = vendas1464Campo($campos, $dataIni, $dataFim, $param);

		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $erc => $clientes_array){
				foreach ($clientes_array as $cod_cliente => $vendas){
					$temp = [];
					
					$temp['erc_cod'] 	= $erc;
					$temp['erc_nome'] 	= $this->_erc[$erc]['nome'] ?? '';
					$temp['gd_cod'] 	= $this->_erc[$erc]['super'] ?? '';
					$temp['gd_nome'] 	= $this->_erc[$erc]['super_nome'] ?? '';
					
					$temp['cliente_cod'] 	= $cod_cliente;
					$temp['cliente_nome'] 	= $this->getNomeCliente($cod_cliente);
					$temp['mix'] 			= $vendas['mix'];
					$temp['unidades'] 		= $vendas['quantVend'];
					$temp['total'] 			= $vendas['venda'];
					$temp['prec_unidade'] 	= $temp['unidades'] > 0 && $temp['total'] > 0 ? $temp['total'] / $temp['unidades'] : 0;
					$temp['venda_pda']		= $vendaPDA[$erc][$cod_cliente] ?? 0;

					$ret[] = $temp;
				}
			}
		}
		
		return $ret;
	}
	
	private function getVendaPda($dataIni, $dataFim, $cadastro, $campos){
		$ret = [];
		
		$param = [];
		$param['origem'] = 'PDA';
		$param['bonificacao'] = false;
		$param['depto'] = '1,12';
		
		$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
		
		foreach ($vendas as $erc => $clientes){
			foreach ($clientes as $cliente => $vendas){
				$ret[$erc][$cliente] = $vendas['venda'];
			}
		}
		
		return $ret;
	}
	
	
	
	private function getMumeroClientes(){
		$sql = "SELECT CODUSUR1, COUNT(*) QUANT FROM PCCLIENT WHERE DTEXCLUSAO IS NULL GROUP BY CODUSUR1";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_quantClientesERC[$row['CODUSUR1']] = $row['QUANT'];
			}
		}
		
		//print_r($this->_quantClientesERC);
	}
	
	private function getNomeCliente($codcli = ''){
		$ret = '';
		if($codcli != '' && $codcli != 0){
			$campo_nome = 'CLIENTE';
			$sql = "SELECT $campo_nome FROM PCCLIENT WHERE CODCLI = '$codcli'";
			$rows = query4($sql);
			if(is_array($rows) && count($rows) > 0){
				if(isset($rows[0][$campo_nome])){
					$ret = $rows[0][$campo_nome];
				}
			}
		}
		return $ret;
	}
}