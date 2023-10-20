<?php
/*
 * Data Criacao 19 de set de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: tws.vendadepto.inc.php
 * 
 * Descricao: 
 * 
 *  Alterções:
 *            26/10/2018 - Emanuel - Migração para intranet2
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class vendadepto{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Anos e meses
	var $_anomes;
	
	//Dados
	var $_dados;
	
	//Supervisores
	var $_super;
	
	//ERCs
	var $_erc;
	
	//Clientes
	var $_clientes;
	
	//Linhas
	var $_linhas;
	
	//ERC e Região original do cliente
	var $_ercOriginal;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_programa = '000002.vendaDepto';
		
		$param = [];
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'supervidor'	, 'etiqueta' => 'Regiao Nome'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => 'ERC'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => 'ERC Nome'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'cli'			, 'etiqueta' => 'Cod.Cliente'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'coddepto'	, 'etiqueta' => 'Depto'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'depto'		, 'etiqueta' => 'Departamento'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'codlinha'	, 'etiqueta' => 'Linha'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'linha'		, 'etiqueta' => 'Linha Desc'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'origem'		, 'etiqueta' => 'Origem'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'quant'		, 'etiqueta' => 'Quant'				, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'venda'		, 'etiqueta' => 'Venda'				, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'anomes'		, 'etiqueta' => 'Mes'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		
		$this->_relatorio->addColuna(array('campo' => 'rcaCad'		, 'etiqueta' => 'Cod.ERC<br>Cadastro'		, 'tipo' => 'T', 'width' => 80, 'posicao'=> 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'rcanomeCad'	, 'etiqueta' => 'ERC<br>Cadastro'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'superCad'	, 'etiqueta' => 'Regiao<br>Cadastro'		, 'tipo' => 'T', 'width' => 80, 'posicao'=> 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'supernomeCad', 'etiqueta' => 'Regiao Nome<br>Cadastro'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'esquerda'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data Ini'		, 'variavel' => 'DATAINI'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Fim'		, 'variavel' => 'DATAFIM'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Supervisor'	, 'variavel' => 'SUPER'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getSupervisor();', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'ERC'			, 'variavel' => 'ERC'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getERC();'		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Departamento'	, 'variavel' => 'DEPTO'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ' = ;1=Medicamentos;12=Nao Medicamentos'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Origem'	, 'variavel' => 'ORIGEM'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'todos=Todas;OL=OL;PE=PE;T=TMKT;PDA=PDA'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '7', 'pergunta' => 'Clientes'	, 'variavel' => 'CLIENTE'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '8', 'pergunta' => 'Clientes Principais'	, 'variavel' => 'CLIENTEPRI'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function montaColunasMes($dtDe, $dtAte, $depto){
		$this->_anomes = datas::getMeses($dtDe, $dtAte);
		foreach ($this->_anomes as $mes){
			$anomes = $mes['anomes'];

			if($depto == '' || $depto == 1){
				$this->_relatorio->addColuna(array('campo' => 'P'.$anomes.'01quant'		, 'etiqueta' => $mes['mesanoNrCurto'].'<br>Medicamentos<br>Quant'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
				$this->_relatorio->addColuna(array('campo' => 'P'.$anomes.'01venda'		, 'etiqueta' => $mes['mesanoNrCurto'].'<br>Medicamentos<br>Venda'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'direita'));
			}
			if($depto == '' || $depto == 12){
				$this->_relatorio->addColuna(array('campo' => 'P'.$anomes.'12quant'		, 'etiqueta' => $mes['mesanoNrCurto'].'<br>Nao Medic.<br>Quant'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
				$this->_relatorio->addColuna(array('campo' => 'P'.$anomes.'12venda'		, 'etiqueta' => $mes['mesanoNrCurto'].'<br>Nao Medic.<br>Venda'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'direita'));
			}
		}
	}
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		$super 	= isset($filtro['SUPER']) ? $filtro['SUPER'] : '';
		$erc 	= isset($filtro['ERC']) ? $filtro['ERC'] : '';
		$depto	= isset($filtro['DEPTO']) ? $filtro['DEPTO'] : '';
		$origem = isset($filtro['ORIGEM']) ? $filtro['ORIGEM'] : '';
		$clientes = str_replace(';', ',', $filtro['CLIENTE']); //isset() : '';
		$clientesPrincipais = str_replace(';', ',', $filtro['CLIENTEPRI']);
		
		$this->_relatorio->setTitulo("Venda Mes/Mes Depto. Periodo: ".datas::dataS2D($dtDe)." a ".datas::dataS2D($dtAte));
		
		if(!$this->_relatorio->getPrimeira()){
			//$this->montaColunasMes($dtDe, $dtAte,$depto);
			$this->getVendedores();

			$this->getDados($dtDe, $dtAte,$super,$erc,$depto,$origem,$clientes,$clientesPrincipais);
			//$this->getDadosOrigem($dtDe, $dtAte,$super,$erc,$depto,$origem,$clientes);

			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
		}
		$ret .= $this->_relatorio;
	
		return $ret;
	}

	function schedule($param){
	
	}

	function getDados($dtDe, $dtAte,$super,$erc,$depto,$origem,$clientes,$clientesPrincipais = ''){
		$ret = array();
		$param = array();
		$departamento = array(1 => 'Medicamento', 12 => 'Nao Medicamento');
		if($dtDe > $dtAte){
			$t = $dtDe;
			$dtDe = $dtAte;
			$dtAte = $t;
		}
		
		if($super != ''){
			$param['super'] = $super;
		}
		if($erc != ''){
			$param['erc'] = $erc;
		}
		if(!empty(trim($depto))){
			$param['depto'] = $depto;
		}
		if($origem != '' && $origem != 'todos'){
			$param['origem'] = $origem;
		}
		//Verifica os clientes do cliente Principal
		if(!empty(trim($clientesPrincipais))){
			$sql = "SELECT CODCLI FROM PCCLIENT WHERE PCCLIENT.CODCLIPRINC IN ($clientesPrincipais)";
			$rows = query4($sql);
			$cliTemp = [];
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$cliTemp[] = $row['CODCLI'];
				}
			}
			$clientes = implode(',', $cliTemp);
		}
		
		if($clientes != ''){
			$param['cliente'] = $clientes;
		}

		$campos = array("to_char(DATA,'YYYYMM') MESANO",'CODSUPERVISOR','CODUSUR','CODCLI','CODEPTO','CODLINHAPROD','ORIGEM');
		
		$vendas	= vendas1464Campo($campos, $dtDe, $dtAte, $param, false);
//print_r($vendas);

		if(count($vendas) > 0){
			foreach ($vendas as $anomes => $venda){
				foreach ($venda as $superV => $vend){
					foreach ($vend as $ercV => $ven){
						foreach ($ven as $codcli => $ve){
							foreach ($ve as $codepto => $v){
								foreach ($v as $linha => $l){
									foreach ($l as $origemV => $k){
										if($k['venda'] <> 0 || $k['quant'] <> 0){
											$temp = array();
											$temp['super']	 	= $superV;
											$temp['supervidor'] = $this->_super[$superV]['nome'];
											$temp['erc'] 		= $ercV;
											$temp['vendedor'] 	= $this->_erc[$ercV]['nome'];
											$temp['cli'] 		= $codcli;
											$temp['cliente'] 	= $this->getClienteNome($codcli);
											$temp['coddepto']	= $codepto;
											$temp['depto'] 		= $departamento[$codepto];
											$temp['codlinha']	= $linha;
											$temp['linha'] 		= $this->getLinhaDesc($linha);
											$temp['origem']		= $origemV;
											$temp['quant'] 		= $k['quant'];
											$temp['venda'] 		= $k['venda'];
											$temp['anomes'] 	= substr($anomes, 4,2).'/'.substr($anomes, 2,2);
											
											$info = $this->getErcOriginal($codcli);
											$temp['rcaCad'] 		= $info['erc'];
											$temp['rcanomeCad'] 	= $info['ercNome'];
											$temp['superCad'] 		= $info['super'];
											$temp['supernomeCad'] 	= $info['superNome'];
											
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
	
	function getClienteNome($codcli){
		$nome = '';
		if(isset($this->_clientes[$codcli])){
			$nome = $this->_clientes[$codcli];
		}else{
			$sql = "select cliente from pcclient where codcli = $codcli";
			$rows = query4($sql);
			if(count($rows) > 0){
				$nome = $rows[0][0];
				$this->_clientes[$codcli] = $nome;
			}
			
		}
		
		return $nome;
	}
	
	function getLinhaDesc($linha){
		$nome = '';
		if(isset($this->_linhas[$linha])){
			$nome = $this->_linhas[$linha];
		}else{
			$sql = "select descricao from pclinhaprod where codlinha = $linha";
			$rows = query4($sql);
			if(count($rows) > 0){
				$nome = str_replace('DIVIS?O', 'DIVISÃO', $rows[0][0]);
				$this->_linhas[$linha] = $nome;
			}
			
		}
		
		return $nome;
	}
	
	private function getErcOriginal($cliente){
		$ret = array();
		
		if(!isset($this->_ercOriginal[$cliente])){
			$sql = "
			SELECT
				PCUSUARI.CODUSUR,
				PCUSUARI.nome NOMEERC,
				PCUSUARI.CODSUPERVISOR,
				PCSUPERV.nome NOMESUPER
			FROM
				PCUSUARI,
				PCSUPERV
			WHERE
				PCUSUARI.CODUSUR = (SELECT PCCLIENT.CODUSUR1 FROM PCCLIENT WHERE CODCLI = $cliente)
				AND PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
			";
			$rows = query4($sql);
			if(count($rows) > 0){
				$ret['erc']			= $rows[0]['CODUSUR'];
				$ret['ercNome']		= $rows[0]['NOMEERC'];
				$ret['super']		= $rows[0]['CODSUPERVISOR'];
				$ret['superNome']	= $rows[0]['NOMESUPER'];
			}
			
		}else{
			$ret = $this->_ercOriginal[$cliente];
		}
		
		return $ret;
	}
}