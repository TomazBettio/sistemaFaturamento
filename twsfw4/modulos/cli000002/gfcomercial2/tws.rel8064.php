<?php
/*
 * Data Criacao: 27/04/2023
 *
 * Autor: Verticais - Thiel
 *
 * Descricao: Desenvolvido para substituir o 8064 do WinThor
 *
 * Alterações:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class rel8064{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	//Classe relatorio
	private $_relatorio;
	
	//Nome do programa
	private $_programa;
	
	//Titulo do relatorio
	private $_titulo;
	
	//Indica que se é teste (não envia email se for)
	private $_teste;
	
	//Dados
	private $_dados = [];
	
	//Produtos
	private $_produtos = [];
	
	//$clientes
	private $_clientes = [];
	
	//ERC cadastro cliente
	private $_ercCli = [];
	
	public function __construct(){
		set_time_limit(0);
		$this->_programa = get_class($this);
		$this->_titulo = 'Relatório 8064';
		
		$this->_teste = false;
		
		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$param['print']		= false;
		$this->_relatorio = new relatorio01($param);
		
		if(false){
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI'	, 'tipo' => 'D' , 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Data Até'	, 'variavel' => 'DATAFIM'	, 'tipo' => 'D' , 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Supervisor'	, 'variavel' => 'SUPER'		, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'get8064GD()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'ERC'			, 'variavel' => 'ERC'		, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'get8064ERC()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);

			sys004::inclui(['programa' => $this->_programa, 'ordem' => '5', 'pergunta' => 'Cliente Principal'	, 'variavel' => 'PRINCIPAL'		, 'tipo' => 'T', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '6', 'pergunta' => 'Cliente'				, 'variavel' => 'CLIENTE'		, 'tipo' => 'T', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '7', 'pergunta' => 'Rede'				, 'variavel' => 'REDE'			, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'get8064Rede()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '8', 'pergunta' => 'Origem Pedido'		, 'variavel' => 'ORIGEM'		, 'tipo' => 'T', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'todos=Todas;OL=OL;PE=PE;T=TMKT;PDA=ION;W=WEB']);

			sys004::inclui(['programa' => $this->_programa, 'ordem' => '9', 'pergunta' => 'Produto'				, 'variavel' => 'PRODUTO'	, 'tipo' => 'T', 'tamanho' => '20', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => 'A', 'pergunta' => 'Marca'				, 'variavel' => 'MARCA'		, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'get8064Marca()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => 'B', 'pergunta' => 'Seção'				, 'variavel' => 'SECAO'		, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'get8064Secao()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => 'C', 'pergunta' => 'Depto'				, 'variavel' => 'DEPTO'		, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'get8064Depto()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => 'D', 'pergunta' => 'Descrição 7'			, 'variavel' => 'DESC7'		, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'get8064Desc7()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			
			sys004::inclui(['programa' => $this->_programa, 'ordem' => 'E', 'pergunta' => 'Bonificados'			, 'variavel' => 'BONIFICA'	, 'tipo' => 'T', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''				, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não']);
		}
	}
	
	public function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$dtIni 	= $filtro['DATAINI'] ?? date('Ymd');
		$dtFim 	= $filtro['DATAFIM'] ?? date('Ymd');
		
		$super 	= $filtro['SUPER'] ?? '';
		$erc 	= $filtro['ERC'] ?? '';
		
		$filtros['principal'] 	= $filtro['PRINCIPAL'] ?? '';
		$filtros['cliente'] 	= $filtro['CLIENTE'] ?? '';
		$filtros['rede'] 		= $filtro['REDE'] ?? '';
		$filtros['origem'] 		= $filtro['ORIGEM'] ?? '';
		$filtros['produto'] 	= $filtro['PRODUTO'] ?? '';
		$filtros['marca'] 		= $filtro['MARCA'] ?? '';
		$filtros['secao'] 		= $filtro['SECAO'] ?? '';
		$filtros['depto'] 		= $filtro['DEPTO'] ?? '';
		$filtros['desc7'] 		= $filtro['DESC7'] ?? '';
		$filtros['bonificacao'] = $filtro['BONIFICA'] ?? '';
		
		if(!$this->_relatorio->getPrimeira()){
			$this->getDados($dtIni, $dtFim, $super, $erc, $filtros);
			$this->montaColunas();
			
			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
		}else{
			$this->montaColunas();
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo' => 'nota'		, 'etiqueta' => 'Nota'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'pedido'		, 'etiqueta' => 'Pedido'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'codcli'		, 'etiqueta' => 'Cod.Cli'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'codcliprinc'	, 'etiqueta' => 'Principal'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cgcent'		, 'etiqueta' => 'CNPJ'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'rede'		, 'etiqueta' => 'Rede'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'obsger'		, 'etiqueta' => 'OBS'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'estado'		, 'etiqueta' => 'Estado'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		
		//$this->_relatorio->addColuna(array('campo' => 'codfilial'	, 'etiqueta' => 'Filial'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Cod GD'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'super_nome'	, 'etiqueta' => 'Supervisor'	, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc_nome'	, 'etiqueta' => 'Nome'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc_cliente'	, 'etiqueta' => 'ERC Cliente'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'origem'		, 'etiqueta' => 'Origem'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'operacao'	, 'etiqueta' => 'Operação'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'produto'		, 'etiqueta' => 'Prod'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'prod_desc'	, 'etiqueta' => 'Produto'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'linha'		, 'etiqueta' => 'Linha'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'depto'		, 'etiqueta' => 'Depto'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'marca'		, 'etiqueta' => 'Marca'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'secao'		, 'etiqueta' => 'Seção'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'descricao7'	, 'etiqueta' => 'Descrição 7'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'quant'		, 'etiqueta' => 'Quant'			, 'tipo' => 'N', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'valor'		, 'etiqueta' => 'Valor'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	}
	
	private function getDados($dtIni, $dtFim, $super, $erc, $filtros){
		$campos = $this->getCampos();
		$this->getClientes();
		$this->getProdutos();
		$lista_erc = getListaEmailGF('erc', false, '', false, 'erc');
		
		$param = [];
		if($filtros['bonificacao'] == 'N'){
			$param['bonificacao'] = false;
		}else{
			$param['bonificacao'] = true;
		}
		
		if(!empty($super)){
			$param['super'] = $super;
		}
		
		if(!empty($erc)){
			//$param['erc'] = $erc;
			$param['vendedor'] = $erc;
		}
		
		if(!empty($filtros['principal'])){
			$param['clientePrincipal'] = $filtros['principal'];
		}
		
		if(!empty($filtros['cliente'])){
			$param['cliente'] = $filtros['cliente'];
		}
		
		if(!empty($filtros['rede'])){
			$param['rede'] = $filtros['rede'];
		}
		
		if(!empty($filtros['origem']) && $filtros['origem'] != 'todos'){
			$param['origem'] = $filtros['origem'];
		}
		
		if(!empty($filtros['produto'])){
			$param['produto'] = $filtros['produto'];
		}
		
		if(!empty($filtros['marca'])){
			$param['marca'] = $filtros['marca'];
		}
		
		if(!empty($filtros['secao'])){
			$param['secao'] = $filtros['secao'];
		}
		
		if(!empty($filtros['depto'])){
			$param['depto'] = $filtros['depto'];
		}

		$vendas = vendas1464Campo($campos, $dtIni, $dtFim, $param, false);
		
//print_r($vendas);
		
		if(is_array($vendas) && count($vendas) > 0){
			foreach ($vendas as $nota => $v1){
				foreach ($v1 as $pedido => $v2){
					foreach ($v2 as $cliente => $v3){
						foreach ($v3 as $erc => $v4){
							foreach ($v4 as $produto => $v5){
								foreach ($v5 as $origem => $v){
									$temp = $this->_produtos[$produto];
									
									$temp['cliente'] 		= $this->_clientes[$cliente]['nome'];
									$temp['cgcent'] 		= $this->_clientes[$cliente]['cnpj'];	
									$temp['codcliprinc'] 	= $this->_clientes[$cliente]['principal'];
									$temp['obsger'] 		= $this->_clientes[$cliente]['obsger'];
									$temp['estado'] 		= $this->_clientes[$cliente]['estado'];
									$temp['rede'] 			= $this->_clientes[$cliente]['rede'];
									
									$temp['super'] 		= $lista_erc[$erc]['super'] ?? '';
									$temp['super_nome']	= $lista_erc[$erc]['super_nome'] ?? '';
									$temp['erc_nome'] 	= $lista_erc[$erc]['nome'] ?? '';
									
									$temp['erc_cliente']= $this->getNomeCliente($cliente);
									
									$temp['nota'] = $nota;
									$temp['pedido'] = $pedido;
									$temp['codcli'] = $cliente;
									$temp['erc'] = $erc;
									$temp['produto'] = $produto;
									$temp['origem'] = $origem == 'PDA' ? 'ION' : $origem;
									
									$temp['quant'] = $v['quant'];
									$temp['valor'] = $v['venda'];
									
									if($v['bonific'] > 0){
										$temp['operacao'] = 'BONIFICAÇÂO';
									}elseif($v['venda'] < 0){
										$temp['operacao'] = 'DEVOLUÇÃO';
									}else{
										$temp['operacao'] = 'VENDA';
									}
									
									if(empty($filtros['desc7'])){
										$this->_dados[] = $temp;
									}else{
										if($filtros['desc7'] == $this->_produtos[$produto]['descricao7']){
											$this->_dados[] = $temp;
										}
									}
									
								}
							}
						}
					}
				}
			}
		}
		
		return;
	}
	
	private function getCampos(){
		$campos = [];
		$campos[] = 'NUMNOTA';
		$campos[] = 'NUMPED';
		$campos[] = 'CODCLI';
		$campos[] = 'CODUSUR';
		$campos[] = 'CODPROD';
		$campos[] = 'ORIGEM';
		
		return $campos;
	}
	
	private function getClientes(){
		$sql = "SELECT CODCLI, CLIENTE, CGCENT, CODCLIPRINC, OBSGERENCIAL1, ESTCOB, PCCLIENT.CODREDE, PCREDECLIENTE.DESCRICAO REDE
				FROM PCCLIENT, PCREDECLIENTE
				WHERE
				    PCCLIENT.CODREDE = PCREDECLIENTE.CODREDE (+)
				";
		$rows = query4($sql);
		
		foreach ($rows as $row){
			$this->_clientes[$row['CODCLI']]['nome'] 		= $row['CLIENTE'];
			$this->_clientes[$row['CODCLI']]['cnpj'] 		= $row['CGCENT'];
			$this->_clientes[$row['CODCLI']]['principal'] 	= $row['CODCLIPRINC'];
			$this->_clientes[$row['CODCLI']]['obsger'] 		= $row['OBSGERENCIAL1'];
			$this->_clientes[$row['CODCLI']]['estado'] 		= $row['ESTCOB'];
			$this->_clientes[$row['CODCLI']]['codrede'] 	= $row['CODREDE'];
			$this->_clientes[$row['CODCLI']]['rede'] 		= $row['REDE'];
		}
	}
	
	private function getProdutos(){
		$sql = "
                SELECT
                        PCPRODUT.CODPROD,
                        PCPRODUT.DESCRICAO,
                        PCPRODUT.CODLINHAPROD,
                        PCLINHAPROD.DESCRICAO LINHA,
                        PCPRODUT.CODEPTO,
                        PCDEPTO.DESCRICAO DEPTO,
                        PCPRODUT.CODMARCA,
                        PCMARCA.MARCA,
                        PCPRODUT.DESCRICAO7,
                        PCPRODUT.CODSEC,
                        PCSECAO.DESCRICAO SECAO
                from 
                    pcprodut,
                    pclinhaprod,
                    pcdepto,
                    pcmarca,
                    pcsecao
                where
                    pcprodut.revenda = 'S'
                    AND pcprodut.codlinhaprod = pclinhaprod.codlinha (+)
                    AND pcprodut.codepto = pcdepto.codepto (+)
                    and pcprodut.codmarca = pcmarca.codmarca (+)
                    and pcprodut.codsec = pcsecao.codsec (+)
                order by
                    PCPRODUT.CODPROD
				";
		$rows = query4($sql);
		
		foreach ($rows as $row){
			$this->_produtos[$row['CODPROD']]['produto'] 	= $row['CODPROD'];
			$this->_produtos[$row['CODPROD']]['prod_desc'] 	= $row['DESCRICAO'];
			$this->_produtos[$row['CODPROD']]['linha'] 		= $row['LINHA'];
			$this->_produtos[$row['CODPROD']]['depto'] 		= $row['DEPTO'];
			$this->_produtos[$row['CODPROD']]['marca'] 		= $row['MARCA'];
			$this->_produtos[$row['CODPROD']]['descricao7'] = $row['DESCRICAO7'];
			$this->_produtos[$row['CODPROD']]['secao'] 		= $row['SECAO'];
		}
	}
	
	private function getNomeCliente($cliente){
		if(!isset($this->_ercCli[$cliente])){
			$sql = "SELECT CODUSUR1 FROM PCCLIENT WHERE CODCLI = $cliente";
			$rows = query4($sql);
			
			if(isset($rows[0]['CODUSUR1'])){
				$this->_ercCli[$cliente] = $rows[0]['CODUSUR1'];
			}else{
				$this->_ercCli[$cliente] = '';
			}
		}
		
		return $this->_ercCli[$cliente];
	}
}

function get8064GD(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT CODSUPERVISOR, NOME 
			FROM pcsuperv 
			WHERE CODSUPERVISOR IN (SELECT CODSUPERVISOR FROM PCUSUARI WHERE dttermino is null and bloqueio = 'N')
			ORDER BY NOME";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['CODSUPERVISOR'];
			$temp[1] = $row['CODSUPERVISOR'].'-'.$row['NOME'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}

function get8064ERC(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT CODUSUR, NOME FROM PCUSUARI WHERE dttermino is null and bloqueio = 'N' ORDER BY NOME";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['CODUSUR'];
			$temp[1] = $row['CODUSUR'].'-'.$row['NOME'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}

function get8064Marca(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT CODMARCA, MARCA FROM PCMARCA WHERE ATIVO = 'S' ORDER BY MARCA";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['CODMARCA'];
			$temp[1] = $row['CODMARCA'].'-'.$row['MARCA'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}

function get8064Secao(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT CODSEC, DESCRICAO FROM PCSECAO ORDER BY DESCRICAO";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['CODSEC'];
			$temp[1] = $row['CODSEC'].'-'.$row['DESCRICAO'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}

function get8064Rede(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT CODREDE, DESCRICAO FROM PCREDECLIENTE";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['CODREDE'];
			$temp[1] = $row['CODREDE'].'-'.$row['DESCRICAO'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}

function get8064Depto(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT CODEPTO, DESCRICAO FROM PCDEPTO ORDER BY CODEPTO";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['CODEPTO'];
			$temp[1] = $row['CODEPTO'].'-'.$row['DESCRICAO'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}

function get8064Desc7(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT DISTINCT(DESCRICAO7) FROM PCPRODUT ";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['DESCRICAO7'];
			$temp[1] = $row['DESCRICAO7'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}