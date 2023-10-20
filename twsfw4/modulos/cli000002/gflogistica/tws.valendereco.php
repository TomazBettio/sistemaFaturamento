<?php
/*
 * Data Criacao 3 de ago de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: class.ora_valEndereco.inc.php
 * 
 * Descricao: 
 * 
 * Altera��es:
 *            17/10/2018 - Emanuel - Migra��o para intranet2
 *            
 * TODO: AJUSTAR FUN��O setFooter NA CLASSE RELATORIO
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);


class valEndereco{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Query de selecao de produtos quando for indicado um endereco
	var $_selectProdutos;
	
	//Clausulas para selecionar os produtos
	var $_where;
	
	//Dados
	var $_dados;
	
	//Produtos
	var $_produtos;
	
	//Lotes
	var $_lotes;
	
	//Enderecos
	var $_enderecos;
	
	//Produto indicado no parametro (pois quando nao for indicado produto, somente endere�o, os produtos sem endere�o n�o devem ser listados)
	var $_prod;
	
	//Indica que nenhum parametro foi informado, deve mostrar todos os produtos em todos os enderecos
	var $_todos;
	
	// Custo
	var $_custo;
	
	function __construct(){
		set_time_limit(0);
		$this->_selectProdutos = '';
		$this->_todos = false;
		
		$this->_programa = 'valEndereco';
		
		$param = [];
		$param['programa'] 	= $this->_programa;
		$param['titulo']	= "Validade por Endereco";
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'produto'	, 'etiqueta' => 'Produto'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'deposito', 'etiqueta' => 'Dep'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'rua'		, 'etiqueta' => 'Rua'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'predio'	, 'etiqueta' => 'Predio'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'nivel'	, 'etiqueta' => 'Nivel'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'apto'	, 'etiqueta' => 'Apto'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'loteg'	, 'etiqueta' => 'Lote Gestao'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'lotew'	, 'etiqueta' => 'Lote WMS'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		
		$this->_relatorio->addColuna(array('campo' => 'valdia'	, 'etiqueta' => 'Val.Dia'		, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'valmes'	, 'etiqueta' => 'Val.Mes'		, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'valano'	, 'etiqueta' => 'Val.Ano'		, 'tipo' => 'T', 'width' =>  50, 'posicao' => 'centro'));
		
		$this->_relatorio->addColuna(array('campo' => 'fabg'	, 'etiqueta' => 'Fab.Gestao'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'valg'	, 'etiqueta' => 'Val.Gestao'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'valw'	, 'etiqueta' => 'Val.WMS'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		//$this->_relatorio->addColuna(array('campo' => 'fabw'	, 'etiqueta' => 'Fab.WMS'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		//$this->_relatorio->addColuna(array('campo' => 'emb'		, 'etiqueta' => 'Embalagem'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		//$this->_relatorio->addColuna(array('campo' => 'uni'		, 'etiqueta' => 'Unidade'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'estg'	, 'etiqueta' => 'Est. Gestao'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'estw'	, 'etiqueta' => 'Estoque WMS'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'dif'		, 'etiqueta' => 'Diferenca'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
		
		$this->_relatorio->addColuna(array('campo' => 'totalg'	, 'etiqueta' => 'Total Val.Gestao'	, 'tipo' => 'V', 'width' => 150, 'posicao' => 'direita'));
		$this->_relatorio->addColuna(array('campo' => 'totalw'	, 'etiqueta' => 'Total Val.WMS'		, 'tipo' => 'V', 'width' => 150, 'posicao' => 'direita'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Produtos'	, 'variavel' => 'PRODUTOS'	, 'tipo' => 'T', 'tamanho' => '200', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Deposito'	, 'variavel' => 'DEPOSITO'	, 'tipo' => 'T', 'tamanho' => '30', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Rua'		, 'variavel' => 'RUA'		, 'tipo' => 'T', 'tamanho' => '30', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Predio'	, 'variavel' => 'PREDIO'	, 'tipo' => 'T', 'tamanho' => '30', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Nivel'		, 'variavel' => 'NIVEL'		, 'tipo' => 'T', 'tamanho' => '30', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Apto'		, 'variavel' => 'APTO'		, 'tipo' => 'T', 'tamanho' => '30', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '7', 'pergunta' => 'Cod.Endereco'		, 'variavel' => 'CODEND'		, 'tipo' => 'T', 'tamanho' => '30', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$produtos 	= $filtro['PRODUTOS'];	
		$this->_prod = $produtos;
		$deposito 	= $filtro['DEPOSITO'];	
		$rua 		= $filtro['RUA'];		
		$predio	 	= $filtro['PREDIO'];	
		$nivel	 	= $filtro['NIVEL'];
		$apto 		= $filtro['APTO'];		
		
		$codend 	= $filtro['CODEND'];
		
		
		if(!$this->_relatorio->getPrimeira()){
			
			if($produtos == '' && $deposito == '' && $rua == '' && $predio == '' && $nivel == '' && $apto == '' && $codend == ''){
				$this->_todos = true;
			}
			
			$this->getCusto();
			$this->getProdutos($produtos, $deposito, $rua, $predio, $nivel, $apto, $codend);
			$this->getLotes($produtos, $deposito, $rua, $predio, $nivel, $apto, $codend);
			$this->getEnderecos($produtos, $deposito, $rua, $predio, $nivel, $apto, $codend);
			
			$this->montaDados();
	
			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
			if($this->_prod == ''){
			
				$this->_relatorio->setFooter("** - Produto com lote estocado em mais de um local, pode haver divergencia de quantidades");
			}
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	
	}

	function schedule($param){
	
	}
	
	function getCusto(){
		$sql = "select codprod, custoreal from pcest where codfilial = 1";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_custo[$row[0]] = $row[1];
			}
		}
	}
	
	function montaLoteTemp($cod){
		$loteTempGeral = array();
		$loteTemp = array();
		$contador = array();
		//$loteQuant = array();
		//$contador = array();

		$loteTemp = isset($this->_lotes[$cod]) ? $this->_lotes[$cod] : '';
//echo '$loteTemp'."\n";
//print_r($loteTemp);					
		
		if(isset($this->_enderecos[$cod])){
    		foreach ($this->_enderecos[$cod] as $i => $endereco){
    			$lote = $endereco['lote'];
    			if(isset($contador[$lote])){
    				$contador[$lote]['cont'] ++;
    				$contador[$lote]['qt'] += $endereco['qt'];
    				$contador[$lote]['end'][] = $endereco['endereco'];
    			}else{
    				$contador[$lote]['cont'] = 1;
    				$contador[$lote]['qt'] = $endereco['qt'];
    				$contador[$lote]['end'][] = $endereco['endereco'];
    			}
    			$loteTempGeral[] = array(	'endereco'	=> $endereco['endereco'],
    										'lotewms' 	=> $endereco['lote'],	// Lote WMS
    										'validadew'	=> $endereco['validade'],
    										'validade'	=> '',
    										'deposito'	=> $endereco['deposito'],
    										'rua'		=> $endereco['rua'],
    										'predio'	=> $endereco['predio'],
    										'nivel'		=> $endereco['nivel'],
    										'apto'		=> $endereco['apto'],
    										'lote'		=> '',					// Lote
    										'qwms'		=> $endereco['qt'],		// Quantidade WMS
    										'ql'		=> 0					// Quantidade lote
    			);
    		}
		}
		
		foreach ($loteTempGeral as $i => $temp){
			$quantLocais = 0;
			$lote = $temp['lotewms'];
			if($this->_prod == ''){
				$quantLocais = $this->getLocaisProdutoLote($cod,$lote);
//echo "LOte: $lote - $quantLocais \n";
			}
			if(isset($loteTemp[$lote])){
				$qt = $loteTemp[$lote]['qt'];
				$loteTempGeral[$i]['lote'] = $lote;
				if($quantLocais >1){
					$loteTempGeral[$i]['lote'] .= ' **';
				}
				$loteTempGeral[$i]['validade'] = $loteTemp[$lote]['validade'];
				if($contador[$lote]['cont'] == 1){
					if($quantLocais > 1){
						$loteTempGeral[$i]['ql'] = $loteTempGeral[$i]['qwms'] > 0 ? $loteTempGeral[$i]['qwms'] : 0;
					}else{
						$loteTempGeral[$i]['ql'] = $qt;
					}
					$loteTemp[$lote]['qt'] = 0;
				}else{
					$qwms = $loteTempGeral[$i]['qwms'];
					if($qwms <= 0){
						$loteTempGeral[$i]['ql'] = 0;
					}else{
						if($qt > $qwms){
							$loteTempGeral[$i]['ql'] = $qwms;
							$loteTemp[$lote]['qt'] = $qt - $qwms;
						}else{
							$loteTempGeral[$i]['ql'] = $qt;
							$loteTemp[$lote]['qt'] = 0;
						}
					}
				}
			}	
		}

		if(trim($this->_prod) <> ''){
			foreach ($loteTemp as $lote){
				if($lote['qt'] <> 0){
					$loteTempGeral[] = array(	'endereco'	=> '',
												'lotewms' 	=> '',				// Lote WMS
												'lote'		=> $lote['lote'],	// Lote
												'qwms'		=> 0,				// Quantidade WMS
												'validadew'	=> '',
												'validade'	=> $lote['validade'],
												'deposito'	=> '',
												'rua'		=> '',
												'predio'	=> '',
												'nivel'		=> '',
												'apto'		=> '',
												'fabg'		=> $lote['fabg'],
												'ql'		=> $lote['qt']		// Quantidade lote
					);
					
				}
			}
		}
//echo '$loteTempGeral'."\n";
//print_r($loteTempGeral);
//echo '$contador'."\n";
//print_r($contador);
//echo '$loteTemp'."\n";
//print_r($loteTemp);			

		return $loteTempGeral;
	}

	function montaDados(){
		foreach ($this->_produtos as $cod => $produto){
			if($produto['estLote'] == 'N'){
				//Produtos que não tem o estoque por lote controlado
				$enderecos = $this->getDadosSemControleQuantLote($cod, $produto);
				if(count($enderecos) > 0){
					foreach ($enderecos as $dados){
						$this->matriz($dados);
					}
				}
			}else{
				$loteTemp = $this->montaLoteTemp($cod);
	//print_r($loteTemp);
				foreach ($loteTemp as $lote){
					$dados = array();
					$dados['cod']		= $cod;
					$dados['produto']	= $produto['produto'];
					//$dados['emb']		= $produto['embalagem'];
					//$dados['uni']		= $produto['unidade'];
						
					$dados['deposito']	= $lote['deposito'];
					$dados['rua']		= $lote['rua'];
					$dados['predio']	= $lote['predio'];
					$dados['nivel']		= $lote['nivel'];
					$dados['apto']		= $lote['apto'];
	
					$dados['lotew']		= $lote['lotewms'];
					$dados['valw']		= $lote['validadew'];
	
					$dados['loteg'] = $lote['lote'];
					//$dados['fabg']	= isset($this->_lotes[$cod][$lote['lote']]['fabg']) && $this->_lotes[$cod][$lote['lote']]['fabg'] != '' ? $this->_lotes[$cod][$lote['lote']]['fabg'] : '';//$lote['fabg'];
					$dados['fabg']	= $this->getFaricacaoGestao($cod, $lote['lote'],$lote['lotewms']);
					$dados['valg']	= $lote['validade'];
					$dados['estg']	= $lote['ql'];
					
	
					$dados['estw']	= $lote['qwms'];
					$dados['dif']	= $lote['ql'] - $lote['qwms'];
					
					$this->matriz($dados);
				}
			}
		}
	}
	
	private function getDadosSemControleQuantLote($cod, $produto){
		$ret = [];
		
		$sql = "
				SELECT
				    DEPOSITO,
				    RUA,
				    PREDIO,
				    NIVEL,
				    APTO,
				    PCESTENDERECO.DTVAL,
				    DTFABRICACAO,
				    PCESTENDERECO.QT
				    
				FROM 
				    PCESTENDERECO,
				    PCENDERECO
				WHERE 
				    PCESTENDERECO.CODENDERECO = PCENDERECO.CODENDERECO (+)
				    AND PCESTENDERECO.CODPROD = $cod 
				    AND PCESTENDERECO.QT > 0
				";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['cod']		= $cod;
				$temp['produto']	= $produto['produto'];
				$temp['deposito']	= $row['DEPOSITO'];
				$temp['rua']		= $row['RUA'];
				$temp['predio']		= $row['PREDIO'];
				$temp['nivel']		= $row['NIVEL'];
				$temp['apto']		= $row['APTO'];
				$temp['loteg']		= '';
				$temp['lotew']		= '';
				$temp['fabg']		= $row['DTFABRICACAO'];
				$temp['fabw']		= $row['DTFABRICACAO'];
				$temp['valg']		= $row['DTVAL'];
				$temp['valw']		= $row['DTVAL'];
				$temp['emb']		= '';
				$temp['uni']		= '';
				$temp['estg']		= 0;
				$temp['estw']		= $row['QT'];
				$temp['este']		= isset($dados['este']) ? $dados['este'] : '';
				$temp['dif']		= '';
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
		
	function matriz($dados){
		$temp = array();
		$cod = $dados['cod'];
		$temp['cod'] 		= $dados['cod'];
		$temp['produto'] 	= $dados['produto'];	
		$temp['deposito'] 	= $dados['deposito'];
		$temp['rua'] 		= $dados['rua'];		
		$temp['predio'] 	= $dados['predio'];	
		$temp['nivel'] 		= $dados['nivel'];
		$temp['apto'] 		= $dados['apto'];	
		$temp['loteg'] 		= $dados['loteg'];	
		$temp['lotew'] 		= $dados['lotew'];	

		$temp['valdia']		= substr($dados['valg'], 0, 2);
		$temp['valmes']		= substr($dados['valg'], 3, 2);
		$temp['valano']		= substr($dados['valg'], 6, 4);
		
		$temp['fabg'] 		= $dados['fabg'];
		//$temp['fabw'] 		= $dados['fabw'];
		$temp['valg'] 		= $dados['valg'];	
		$temp['valw'] 		= $dados['valw'];	
		//$temp['emb'] 		= $dados['emb'];		
		//$temp['uni'] 		= $dados['uni'];		
		$temp['estg'] 		= $dados['estg'];	
		$temp['estw'] 		= $dados['estw'];
		$temp['este'] 		= isset($dados['este']) ? $dados['este'] : '';	
		$temp['dif'] 		= $dados['dif'];		
		$temp['totalg']		= 0;
		$temp['totalw']		= 0;
		if(isset($this->_custo[$cod]) && $this->_custo[$cod] <> 0){
			$temp['totalg']		= $this->_custo[$cod] * $dados['estg'];
			$temp['totalw']		= $this->_custo[$cod] * $dados['estw'];
		}
		
		$this->_dados[] = $temp;
	}
	
	
	function getProdutos($produtos, $deposito, $rua, $predio, $nivel, $apto, $codEnd){
		if($codEnd == ''){
			if($deposito != ''){
				$this->selectProduto(" pcendereco.deposito IN ($deposito) ");
			}
			if($rua != ''){
				$this->selectProduto(" pcendereco.rua IN ($rua) ");
			}
			if($predio != ''){
				$this->selectProduto(" pcendereco.predio IN ($predio) ");
			}
			if($nivel != ''){
				$this->selectProduto(" pcendereco.nivel IN ($nivel) ");
			}
			if($apto != ''){
				$this->selectProduto(" pcendereco.apto IN ($apto) ");
			}
		}else{
			$this->selectProdutoCodEnd($codEnd);
		}
		
		if($this->_todos){
			$this->_selectProdutos = "(select distinct codprod from pcestendereco)";
		}
		
		
		$where = $this->_selectProdutos == '' ? $produtos : $this->_selectProdutos;
		
		$sql = "select 
				    codprod,
				    descricao,
				    embalagem,
				    unidade,
					ESTOQUEPORLOTE
				from 
					pcprodut
				where 
					codprod in ($where)";
		$rows = query4($sql);
//echo "$sql \n";
//print_r($rows);	
		if(count($rows) > 0){
			foreach($rows as $row){
				$cod = $row[0];
				$this->_produtos[$cod]['produto']	= $row[1];
				$this->_produtos[$cod]['embalagem']	= $row[2];
				$this->_produtos[$cod]['unidade']	= $row[3];
				$this->_produtos[$cod]['estLote']	= $row['ESTOQUEPORLOTE'];
			}
		}		
		
//print_r($this->_produtos[25299]);
	}
	
	function matrizProduto($cod, $produto, $embalagem, $unidade){
		if(!isset($this->_produtos[$cod])){
			$this->_produtos[$cod]['cod'] 		= $cod;
			$this->_produtos[$cod]['produto'] 	= $produto;
			$this->_produtos[$cod]['emb'] 		= $embalagem;
			$this->_produtos[$cod]['uni'] 		= $unidade;
		}
	}
	
	function selectProduto($where){
		if($this->_where == ''){
			$this->_where = $where;
		}else{
			$this->_where .= " AND ".$where;
		}
		$this->_selectProdutos = "(select codprod from pcestendereco where codendereco in ( select codendereco FROM pcendereco WHERE ".$this->_where." ))";
	}

	function selectProdutoCodEnd($where){
		$this->_selectProdutos = "(select codprod from pcestendereco where codendereco = $where)";
//echo "OK: ".$this->_selectProdutos."\n";
	}
	
	private function getFaricacaoGestao($cod, $loteG, $loteW){
		$ret = '';
		if($loteG == ''){
			$lote = $loteW;
		}else{
			$lote = $loteG;
		}
		if($lote != ''){
			$sql = "
					select
						DATAFABRICACAO
					from
						pclote
					where 
						codprod = $cod
						AND numlote = '$lote'
			";
			$rows = query4($sql);
			
			if(count($rows) > 0){
				$ret = datas::dataMS2D($rows[0]['DATAFABRICACAO']);
			}
		}
		return $ret;
	}
	
	function getLotes($produtos, $deposito, $rua, $predio, $nivel, $apto){
		$ret = array();
		$where = $this->_selectProdutos == '' ? $produtos : $this->_selectProdutos;
		$sql = "select 
				    codprod,
				    (qt - qtbloqueada) qt,
				    to_char(dtvalidade,'DD/MM/YYYY') dtvalidade ,
				    numlote,
					DATAFABRICACAO
				from 
					pclote
				where codprod IN ($where)
				    and (qt - qtbloqueada)<> 0
				    and codfilial = 1
				order by numlote
		";
//echo "$sql \n";
		$rows = query4($sql);
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$cod = $row[0];
				$lote = $row[3];
				
				$this->_lotes[$cod][$lote]['cod'] 		= $cod;
				$this->_lotes[$cod][$lote]['qt'] 		= $row[1];
				$this->_lotes[$cod][$lote]['validade'] 	= $row[2];
				$this->_lotes[$cod][$lote]['lote'] 		= $lote;
				$this->_lotes[$cod][$lote]['fabg'] 		= $row['DATAFABRICACAO'];
			}
		}
//print_r($this->_lotes[$cod]);
	}
	
	/*
	 * Retorna a quantidade de locais que este produto/lote se encontra, pois se estiver em mais de um local fica imposs�vel
	 * indicar a quantidade do gest�o que est� em cada local
	 */
	
	function getLocaisProdutoLote($cod,$lote){
		$sql = "
				select
                    count(*) quant
                from
                    PCESTENDERECOI
                where 
					codprod = $cod
					and numlote = '$lote'
					and qt <> 0
				";
		
		$rows = query4($sql);
		
		return $rows[0][0];
	}

	function getEnderecos($produtos, $deposito, $rua, $predio, $nivel, $apto, $codend){
		$ret = array();
		$where = $this->_selectProdutos == '' ? $produtos : $this->_selectProdutos;
		$sql = "
				select 
                    pcestendereco.codprod,
                    PCESTENDERECOI.qt,
                    to_char(PCESTENDERECOI.dtval,'DD/MM/YYYY') dtval,
                    PCESTENDERECOI.numlote,
                    deposito,
                    rua,
                    predio,
                    nivel,
                    apto,
                    pcestendereco.codendereco
                from 
                    pcestendereco,
                    pcendereco,
                    PCESTENDERECOI
                where pcestendereco.codendereco = pcendereco.codendereco (+)
                    AND pcestendereco.codprod = PCESTENDERECOI.codprod
                    and pcestendereco.codendereco = PCESTENDERECOI.codendereco
					and pcendereco.codfilial = 1
                    
				";
		if($deposito == '99' || $rua == '99' || $predio == '99' || $nivel == '99' || $apto == '99' || $codend == '1'){
			$sql .= "  AND pcendereco.STATUS NOT IN('F')  \n";
		}else{
			$sql .= "  AND pcendereco.STATUS NOT IN('A','F')  \n";
		}
		
		if($produtos != ''){
			$sql .= "  and pcestendereco.codprod in ($produtos)  ";
		}
		if($deposito != ''){
			$sql .= "  and deposito in ($deposito)  ";
		}
		if($rua != ''){
			$sql .= "  and rua in ($rua)  ";
		}
		if($predio != ''){
			$sql .= "  and predio in ($predio)  ";
		}
		if($nivel != ''){
			$sql .= "  and nivel in ($nivel)  ";
		}
		if($apto != ''){
			$sql .= "  and apto in ($apto)  ";
		}
		if($codend != ''){
			$sql .= "  and pcestendereco.codendereco =  $codend ";
		}
		$sql .= "  order by pcestendereco.codprod";
//echo "$sql \n";
		$rows = query4($sql);
	
		if(count($rows) > 0){
			foreach($rows as $row){
				$cod = $row[0];
				if(!isset($this->_enderecos[$cod])){
					$i = 0;
				}else{
					$i = count($this->_enderecos[$cod]);
				}
				$this->_enderecos[$cod][$i]['cod'] 		= $cod;
				$this->_enderecos[$cod][$i]['qt'] 		= $row[1];
				$this->_enderecos[$cod][$i]['validade'] = $row[2];
				$this->_enderecos[$cod][$i]['lote'] 	= $row[3];
				$this->_enderecos[$cod][$i]['deposito'] = $row[4];
				$this->_enderecos[$cod][$i]['rua'] 		= $row[5];
				$this->_enderecos[$cod][$i]['predio'] 	= $row[6];
				$this->_enderecos[$cod][$i]['nivel'] 	= $row[7];
				$this->_enderecos[$cod][$i]['apto'] 	= $row[8];
				$this->_enderecos[$cod][$i]['endereco']	= $row[9];
			}
		}
//print_r($this->_enderecos[$cod]);
	}
	
}