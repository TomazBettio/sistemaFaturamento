<?php
/*
 * Data Criacao 5 de nov de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: class.ora_vendasfornec.inc.php
 * 
 * Descricao: Acompanhamento de vendas por fornecedor
 * 
 * 
 * Alterações:
 *            05/11/2018 - Emanuel - Migração para intranet2
 *            08/03/2021 - Thiel - Possibilidade de quebrar por cliente
 *            22/02/2023 - Thiel - Migração Intranet4
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class vendasfornec02{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Produtos selecionados
	var $_produtos;
	
	//Forncedores 
	var $_fornecedores;
	
	//Nome dos clientes
	private $_clientes = [];
	
	// Marcas
	var $_marcas;
	
	//Vendas
	var $_vendas;
	
	//Titulo variavel	
	var $_titulo;
	
	//Supervisores
	var $_super;
	
	//ERCs
	var $_erc;
	
	//Dados da campanha
	var $_campanha;
	
	//Indica que se é teste (não envia email se for)
	var $_teste;
	
	//Quando for teste se envia os emails do ERC para o tester
	var $_enviaEmailERCteste;
	
	//Utilizado para guardar totais 
	var $_totais;
	
	//Periodos quando quebra por mês
	private $_periodos;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_produtos = "";
		$this->_fornecedores = array();
		$this->_marcas = array();
		
		$this->_titulo = 'Vendas por Fornecedor';
		
		$this->_teste = false;
		$this->_enviaEmailERCteste = true;
		
		$this->_programa = 'vendasfornec02';
		
		$param = [];
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);

		if(false){
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data Ini'				, 'variavel' => 'DATAINI'	 , 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Fim'				, 'variavel' => 'DATAFIM'	 , 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Regiao'				, 'variavel' => 'SUPER'		 , 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getSupervisor();', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'ERC'					, 'variavel' => 'ERC'		 , 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getERC();'		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Fornecedores'			, 'variavel' => 'FORNEC'	 , 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Origem ERC'			, 'variavel' => 'ORIGEM'	 , 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'C=Cadastro;P=Pedido'));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '7', 'pergunta' => 'Produtos'				, 'variavel' => 'PRODUTOS'	 , 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '8', 'pergunta' => 'Quebra por Mês'		, 'variavel' => 'QUEBRA'	 , 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não'));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '9', 'pergunta' => 'Quebra Por Cliente'	, 'variavel' => 'CLIENTE'	 , 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não'));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => 'A', 'pergunta' => 'Quebra Por Marca'		, 'variavel' => 'MARCA'		 , 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não'));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => 'B', 'pergunta' => 'Origem Venda'    		,'variavel' => 'ORIGEM_VENDA', 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => $this->getOrigens()));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => 'C', 'pergunta' => 'Venda Por'    			,'variavel' => 'VENDEDOR'	 , 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'E=ERC;O=Operadores'));
		}
		
	}			
	
	private function montaColunas($quebra = 'N', $dtDe = '', $dtAte = '', $cliente = 'N', $marca = 'S', $vendedor = 'E'){
		if($vendedor == 'E'){
			$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'supervisor'	, 'etiqueta' => 'Regiao Nome'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => 'ERC'					, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => 'ERC Nome'				, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		}else{
			$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => 'Operador'					, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => 'Operador Nome'				, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		}
		
		if($cliente == 'S'){
		    $this->_relatorio->addColuna(array('campo' => 'codcli'	, 'etiqueta' => 'Cod.Cli.'		       	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		    $this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'			    , 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		}
		
		$this->_relatorio->addColuna(array('campo' => 'fornec'		, 'etiqueta' => 'Cod.Forn.'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		if($marca == 'S'){
			$this->_relatorio->addColuna(array('campo' => 'codmarca'	, 'etiqueta' => 'Cod.Marca'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'marca'		, 'etiqueta' => 'Marca'					, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		}
		
		$this->_periodos = datas::getMeses($dtDe, $dtAte);
		
		if($quebra == 'S' && count($this->_periodos) > 1){
			foreach ($this->_periodos as $i => $periodo){
				$this->_relatorio->addColuna(array('campo' => 'venda'.$i	, 'etiqueta' => 'Venda '.$periodo['mesanoNrCurto']					, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
				$this->_relatorio->addColuna(array('campo' => 'quant'.$i	, 'etiqueta' => 'Quantidade<br>Venda '.$periodo['mesanoNrCurto']	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
				
				//Mantem o promeiro dia indicado
				if($i == 0){
					$this->_periodos[$i]['dtDe'] = $this->_periodos[$i]['anomes'].substr($dtDe, 6, 2);
					$this->_periodos[$i]['dtAte'] = $this->_periodos[$i]['anomes'].$this->_periodos[$i]['diafim'];
				}elseif($i == (count($this->_periodos) -1)){
					$this->_periodos[$i]['dtDe'] = $this->_periodos[$i]['anomes'].$this->_periodos[$i]['diaini'];
					$this->_periodos[$i]['dtAte'] = $this->_periodos[$i]['anomes'].substr($dtAte, 6, 2);
				}else{
					$this->_periodos[$i]['dtDe'] = $this->_periodos[$i]['anomes'].$this->_periodos[$i]['diaini'];
					$this->_periodos[$i]['dtAte'] = $this->_periodos[$i]['anomes'].$this->_periodos[$i]['diafim'];
				}
			}
			
			//Totais
			$this->_relatorio->addColuna(array('campo' => 'vendaTotal'	, 'etiqueta' => 'Venda Total'					, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'quantTotal'	, 'etiqueta' => 'Quantidade<br>Venda Total'		, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
		}else{
			$temp = $this->_periodos[0];
			$this->_periodos = [];
			$this->_periodos[0] = $temp;
			$this->_periodos[0]['dtDe'] = $dtDe;
			$this->_periodos[0]['dtAte'] = $dtAte;
			
			
			$this->_relatorio->addColuna(array('campo' => 'venda0'		, 'etiqueta' => 'Venda'					, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'quant0'		, 'etiqueta' => 'Quantidade<br>Venda'	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
		}

	}
	
	function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		$super 	= isset($filtro['SUPER']) 	? $filtro['SUPER'] : '';
		$erc 	= isset($filtro['ERC']) 	? $filtro['ERC'] : '';
		$origem = isset($filtro['ORIGEM']) 	? $filtro['ORIGEM'] : '';
		
		$fornecedores = isset($filtro['FORNEC']) ? $filtro['FORNEC'] : '';
		$fornecedores = str_replace(';', ',', $fornecedores);
		$fornecedores = str_replace('|', ',', $fornecedores);
		$fornecedores = str_replace('-', ',', $fornecedores);
		
		$produtos = isset($filtro['PRODUTOS']) ? $filtro['PRODUTOS'] : '';
		$produtos = str_replace(';', ',', $produtos);
		$produtos = str_replace('|', ',', $produtos);
		$produtos = str_replace('-', ',', $produtos);
		$quebra   = isset($filtro['QUEBRA']) ? $filtro['QUEBRA'] : 'N';
		$cliente  = isset($filtro['CLIENTE']) ? $filtro['CLIENTE'] : 'N';
		$marca	  = isset($filtro['MARCA']) ? $filtro['MARCA'] : 'S';
		$vendedor = isset($filtro['VENDEDOR']) ? $filtro['VENDEDOR'] : 'S';
		
		$origem_venda = isset($filtro['ORIGEM_VENDA']) ? $filtro['ORIGEM_VENDA'] : 'T';

		$this->_relatorio->setTitulo($this->_titulo." Periodo: ".datas::dataS2D($dtDe)." a ".datas::dataS2D($dtAte));
		
		if(!$this->_relatorio->getPrimeira()){
		    $this->montaColunas($quebra, $dtDe, $dtAte, $cliente, $marca, $vendedor);
		    $this->getVendedores($vendedor);
		    
		    $this->getVendas($dtDe, $dtAte,$super,$erc, $fornecedores, $origem, $produtos, $quebra, $vendedor, $cliente, $marca, $origem_venda);
		    
		    asort($this->_fornecedores);
		    asort($this->_marcas);
		    asort($this->_clientes);
//print_r($this->_vendas);	
//print_r($this->_fornecedores);
//echo "$cliente - $marca - $vendedor <br>\n";
		    $dados = [];
		    if(count($this->_vendas) > 0){
			    if($cliente == 'N' && $marca == 'N' && $vendedor == 'O'){
			    	foreach ($this->_vendas as $oper => $venda){
			    		foreach ($this->_fornecedores as $f => $fornecedor){
			    			if(isset($venda[$f])){
			    				$dados[] = $venda[$f];
			    			}
			    		}
			    	}
			    	//$this->_vendas[$vend][$fornec] = $temp;
			    }elseif($cliente == 'N' && $marca == 'N' && $vendedor == 'E'){
			    	foreach ($this->_vendas as $super => $vendas1){
			    		foreach ($vendas1 as $erc => $venda){
			    			foreach ($this->_fornecedores as $f => $fornecedor){
		    					if(isset($venda[$f])){
		    						$dados[] = $venda[$f];
			    				}
			    			}
			    		}
			    	}
			    }elseif($cliente == 'N' && $vendedor == 'O'){
			    	foreach ($this->_vendas as $oper => $venda){
		    			foreach ($this->_fornecedores as $f => $fornecedor){
		    				foreach ($this->_marcas as $m => $marca){
		    					if(isset($venda[$f][$m])){
			    					$dados[] = $venda[$f][$m];
			    				}
			    			}
			    		}
			    	}
			    }elseif($cliente == 'N' && $vendedor == 'E'){
			    	foreach ($this->_vendas as $super => $vendas1){
			    		foreach ($vendas1 as $erc => $venda){
			    			foreach ($this->_fornecedores as $f => $fornecedor){
			    				foreach ($this->_marcas as $m => $marca){
			    					if(isset($venda[$f][$m])){
			    						$dados[] = $venda[$f][$m];
			    					}
			    				}
			    			}
			    		}
			    	}
			    }elseif($marca == 'N' && $vendedor == 'O'){
			    	foreach ($this->_vendas as $oper => $venda){
			    		foreach ($this->_fornecedores as $f => $fornecedor){
			    			foreach ($this->_clientes as $c => $cli){
			    				if(isset($venda[$f][$c])){
			    					$dados[] = $venda[$f][$c];
			    				}
			    			}
			    		}
			    	}
			    }elseif($marca == 'N' && $vendedor == 'E'){
			    	foreach ($this->_vendas as $super => $vendas1){
			    		foreach ($vendas1 as $erc => $venda){
			    			foreach ($this->_fornecedores as $f => $fornecedor){
			    				foreach ($this->_clientes as $c => $cli){
			    					if(isset($venda[$f][$c])){
			    						$dados[] = $venda[$f][$c];
			    					}
			    				}
			    			}
			    		}
			    	}
			    }elseif($vendedor == 'O'){
			    	foreach ($this->_vendas as $oper => $venda){
			    		foreach ($this->_fornecedores as $f => $fornecedor){
			    			foreach ($this->_marcas as $m => $marca){
			    				foreach ($this->_clientes as $c => $cli){
			    					if(isset($venda[$f][$m][$c])){
			    						$dados[] = $venda[$f][$m][$c];
			    					}
			    				}
			    			}
			    		}
			    	}
			    }elseif($vendedor == 'E'){
			    	foreach ($this->_vendas as $super => $vendas1){
			    		foreach ($vendas1 as $erc => $venda){
			    			foreach ($this->_fornecedores as $f => $fornecedor){
			    				foreach ($this->_marcas as $m => $marca){
			    					foreach ($this->_clientes as $c => $cli){
			    						if(isset($venda[$f][$m][$c])){
			    							$dados[] = $venda[$f][$m][$c];
			    						}
			    					}
			    				}
			    			}
			    		}
			    	}
			    }else{
			    	echo "Problema de processamento, favor entrar em contato com o programador<br>\n";
			    }
		    }
//print_r($dados);
	
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}else{
			$this->montaColunas();
		}
		$ret .= $this->_relatorio;
		
		return $ret;
	}

	function schedule($param){
	}
	

	function geraMatriz($vendedor, $vend, $super, $fornec, $marca = '', $cliente = ''){
		$temp = array();
		$temp['super']	 	= $super;
		$temp['supervisor'] = !empty($super) ? $this->_super[$super]['nome'] : '';
		$temp['erc'] 		= $vend;
		$temp['vendedor'] 	= isset($this->_erc[$vend]) ? $this->_erc[$vend]['nome'] : '';
		
		if($cliente != ''){
		    $temp['codcli']     = $cliente;
		    $temp['cliente']    = $this->getNomeCliente($cliente);
		}
		
		$temp['fornec'] 	= $fornec;
		$temp['fornecedor'] = $this->getNomeFornecedor($fornec);
			
		if(!empty($marca)){
			$temp['codmarca'] 	= $marca;
			$temp['marca']		= $this->getNomeMarca($marca);
		}
				
		foreach($this->_periodos as $i => $periodo){
			$temp['venda'.$i]  	= 0;
			$temp['quant'.$i]	= 0;
			$temp['vendaOL'.$i] = 0;
			$temp['quantOL'.$i]	= 0;
		}
		
		if(count($this->_periodos) > 1){
			//Totais
			$temp['vendaTotal']  	= 0;
			$temp['quantTotal']		= 0;
			$temp['vendaOLTotal'] 	= 0;
			$temp['quantOLTotal']	= 0;
		}
		
		
		if($cliente == '' && $marca == '' && $super == ''){
			$this->_vendas[$vend][$fornec] = $temp;
		}elseif($cliente == '' && $marca == ''  && $super != ''){
			$this->_vendas[$super][$vend][$fornec] = $temp;
		}elseif($cliente == '' && $super == ''){
			$this->_vendas[$vend][$fornec][$marca] = $temp;
		}elseif($cliente == '' && $super != ''){
			$this->_vendas[$super][$vend][$fornec][$marca] = $temp;
		}elseif($marca == '' && $super == ''){
			$this->_vendas[$vend][$fornec][$cliente] = $temp;
		}elseif($marca == '' && $super != ''){
			$this->_vendas[$super][$vend][$fornec][$cliente] = $temp;
		}elseif($super == ''){
			$this->_vendas[$vend][$fornec][$marca][$cliente] = $temp;
		}elseif($super != ''){
			$this->_vendas[$super][$vend][$fornec][$marca][$cliente] = $temp;
		}else{
			echo "Problema de processamento, favor entrar em contato com o programador<br>\n";
		}
	}
	
	function getVendas($dtDe, $dtAte, $superParam, $ercParam, $fornecParam = '', $origem = 'P', $produtos = '', $quebra = 'N', $vendedor = 'E', $cliente = 'N', $marca = 'N', $origem_venda = 'T'){
		$param = array();
		$campos = [];
		
		if($vendedor == 'E'){
			if($origem == 'P'){
				//Por ERC do pedido
				$campos[] = 'CODUSUR';
			}else{
				//Por ERC do cadastro cliente
				$campos[] = 'ERCCLI';
			}
		}elseif ($vendedor == 'O'){
			//Por operador
			$campos[] = 'CODEMITENTEPEDIDO';
		}
		
		//Fornecedor
		$campos[] = 'CODFORNEC';
		
		if($marca == 'S'){
			$campos[] = 'CODMARCA';
		}
		
		if($cliente == 'S'){
			$campos[] = 'CODCLI';
		}
		
		if($superParam != ''){
			$param['super'] = $superParam;
		}
		if($ercParam != ''){
			$param['ERC'] = $ercParam;
		}
		if($fornecParam != ''){
			$param['fornecedor'] = $fornecParam;
		}
		if(!empty($produtos)){
			$param['produto'] = $produtos;
		}

		//Filtra origem de venda
		if($origem_venda != 'T'){
			$param['origem'] = $origem_venda;
		}

		
		foreach ($this->_periodos as $i => $periodo){
			$param['bonificacao'] = false;
			$vendas = vendas1464Campo($campos, $periodo['dtDe'], $periodo['dtAte'], $param);

			if(count($vendas) > 0){
				if($cliente == 'N' && $marca == 'N' && $vendedor == 'O'){
					foreach ($vendas as $oper => $venda){
						foreach ($venda as $fornec => $v){
							if(!isset($this->_vendas[$oper][$fornec])){
								$this->geraMatriz($vendedor, $oper, '', $fornec);
							}
							
							$this->_vendas[$oper][$fornec]['venda'.$i] = $v['venda'];
							$this->_vendas[$oper][$fornec]['quant'.$i] = $v['quant'];
							
							if(count($this->_periodos) > 1){
								//Totais
								$this->_vendas[$oper][$fornec]['vendaTotal'] += $v['venda'];
								$this->_vendas[$oper][$fornec]['quantTotal'] += $v['quant'];
							}
						}
					}
					//$this->_vendas[$vend][$fornec] = $temp;
				}elseif($cliente == 'N' && $marca == 'N' && $vendedor == 'E'){
					foreach ($vendas as $erc => $venda){
						foreach ($venda as $fornec => $v){
							$super = $this->_erc[$erc]['super'];
							if(!isset($this->_vendas[$super][$erc][$fornec])){
								$this->geraMatriz($vendedor, $erc, $super, $fornec);
							}
							$this->_vendas[$super][$erc][$fornec]['venda'.$i] = $v['venda'];
							$this->_vendas[$super][$erc][$fornec]['quant'.$i] = $v['quant'];
								
							if(count($this->_periodos) > 1){
								//Totais
								$this->_vendas[$super][$erc][$fornec]['vendaTotal'] += $v['venda'];
								$this->_vendas[$super][$erc][$fornec]['quantTotal']	+= $v['quant'];
							}
						}
					}
					
				}elseif($cliente == 'N' && $vendedor == 'O'){
					foreach ($vendas as $oper => $venda){
						foreach ($venda as $fornec => $v){
							foreach ($v as $marc => $m){
								if(!isset($this->_vendas[$oper][$fornec][$marc])){
									$this->geraMatriz($vendedor, $oper, '', $fornec, $marc);
								}
								
								$this->_vendas[$oper][$fornec][$marc]['venda'.$i] = $m['venda'];
								$this->_vendas[$oper][$fornec][$marc]['quant'.$i] = $m['quant'];
								
								if(count($this->_periodos) > 1){
									//Totais
									$this->_vendas[$oper][$fornec][$marc]['vendaTotal'] += $m['venda'];
									$this->_vendas[$oper][$fornec][$marc]['quantTotal'] += $m['quant'];
								}
							}
						}
					}
				}elseif($cliente == 'N' && $vendedor == 'E'){
					foreach ($vendas as $erc => $venda){
						foreach ($venda as $fornec => $v){
							foreach ($v as $marc => $m){
								$super = $this->_erc[$erc]['super'];
								if(!isset($this->_vendas[$super][$erc][$fornec][$marc])){
									$this->geraMatriz($vendedor, $erc, $super, $fornec, $marc);
								}
								//print_r($this->_vendas[$super][$erc][$fornec][$marca]);
								$this->_vendas[$super][$erc][$fornec][$marc]['venda'.$i] = $m['venda'];
								$this->_vendas[$super][$erc][$fornec][$marc]['quant'.$i] = $m['quant'];
								
								if(count($this->_periodos) > 1){
									//Totais
									$this->_vendas[$super][$erc][$fornec][$marc]['vendaTotal'] += $m['venda'];
									$this->_vendas[$super][$erc][$fornec][$marc]['quantTotal']	+= $m['quant'];
								}
								
							}
						}
					}
					
				}elseif($marca == 'N' && $vendedor == 'O'){
					foreach ($vendas as $oper => $venda){
						foreach ($venda as $fornec => $v){
							foreach ($v as $cli => $m){
								if(!isset($this->_vendas[$oper][$fornec][$cli])){
									$this->geraMatriz($vendedor, $oper, '', $fornec, '', $cli);
								}
								
								$this->_vendas[$oper][$fornec][$cli]['venda'.$i] = $m['venda'];
								$this->_vendas[$oper][$fornec][$cli]['quant'.$i] = $m['quant'];
								
								if(count($this->_periodos) > 1){
									//Totais
									$this->_vendas[$oper][$fornec][$cli]['vendaTotal'] += $m['venda'];
									$this->_vendas[$oper][$fornec][$cli]['quantTotal'] += $m['quant'];
								}
							}
						}
					}
				}elseif($marca == 'N' && $vendedor == 'E'){
					foreach ($vendas as $erc => $venda){
						foreach ($venda as $fornec => $v){
							foreach ($v as $cli => $m){
								$super = $this->_erc[$erc]['super'];
								if(!isset($this->_vendas[$super][$erc][$fornec][$cli])){
									$this->geraMatriz($vendedor, $erc, $super, $fornec, '', $cli);
								}
								//print_r($this->_vendas[$super][$erc][$fornec][$marca]);
								$this->_vendas[$super][$erc][$fornec][$cli]['venda'.$i] = $m['venda'];
								$this->_vendas[$super][$erc][$fornec][$cli]['quant'.$i] = $m['quant'];
								
								if(count($this->_periodos) > 1){
									//Totais
									$this->_vendas[$super][$erc][$fornec][$cli]['vendaTotal'] += $m['venda'];
									$this->_vendas[$super][$erc][$fornec][$cli]['quantTotal'] += $m['quant'];
								}
								
							}
						}
					}
				}elseif($vendedor == 'O'){
					foreach ($vendas as $oper => $venda){
						foreach ($venda as $fornec => $v){
							foreach ($v as $marc => $m){
								foreach ($m as $cli => $j){
									if(!isset($this->_vendas[$oper][$fornec][$marc][$cli])){
										$this->geraMatriz($vendedor, $oper, '', $fornec, $marc, $cli);
									}
									
									$this->_vendas[$oper][$fornec][$marc][$cli]['venda'.$i] = $j['venda'];
									$this->_vendas[$oper][$fornec][$marc][$cli]['quant'.$i] = $j['quant'];
									
									if(count($this->_periodos) > 1){
										//Totais
										$this->_vendas[$oper][$fornec][$marc][$cli]['vendaTotal'] += $j['venda'];
										$this->_vendas[$oper][$fornec][$marc][$cli]['quantTotal'] += $j['quant'];
									}
								}
							}
						}
					}
				}elseif($vendedor == 'E'){
					foreach ($vendas as $erc => $venda){
						foreach ($venda as $fornec => $v){
							foreach ($v as $marc => $m){
								foreach ($m as $cli => $j){
									$super = $this->_erc[$erc]['super'];
									if(!isset($this->_vendas[$super][$erc][$fornec][$marc][$cli])){
										$this->geraMatriz($vendedor, $erc, $super, $fornec, $marc, $cli);
									}
									//print_r($this->_vendas[$super][$erc][$fornec][$marca]);
									$this->_vendas[$super][$erc][$fornec][$marc][$cli]['venda'.$i] = $j['venda'];
									$this->_vendas[$super][$erc][$fornec][$marc][$cli]['quant'.$i] = $j['quant'];
									
									if(count($this->_periodos) > 1){
										//Totais
										$this->_vendas[$super][$erc][$fornec][$marc][$cli]['vendaTotal'] += $j['venda'];
										$this->_vendas[$super][$erc][$fornec][$marc][$cli]['quantTotal'] += $j['quant'];
									}
								}
							}
						}
					}
				}else{
					echo "Problema de processamento, favor entrar em contato com o programador<br>\n";
				}
			}
		}
	}
	
	function getVendedores($vendedor = 'E'){
		if($vendedor == 'E'){
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
		}elseif($vendedor == 'O'){
			$operadores = getListaOperadores();
			//print_r($operadores);
			foreach ($operadores as $op => $operador){
				$this->_erc[$op]['nome'] = $operador['nome'];
				$this->_erc[$op]['email'] = $operador['email'];
				$this->_erc[$op]['super'] = '';
			}
		}
	}
	
	
	private function getNomeFornecedor($fornec){
		if(!isset($this->_fornecedores[$fornec])){
			$sql = "select fornecedor from pcfornec where codfornec = $fornec";
			$rows = query4($sql);
			if(isset($rows[0][0])){
				$this->_fornecedores[$fornec]= $rows[0][0];
			}else{
				$this->_fornecedores[$fornec] = '';
			}
		}
		
		return $this->_fornecedores[$fornec];
	}
	
	private function getNomeCliente($cliente){
	    if(!isset($this->_clientes[$cliente])){
	        $sql = "select cliente from pcclient where codcli = $cliente";
	        $rows = query4($sql);
	        if(isset($rows[0][0])){
	            $this->_clientes[$cliente]= $rows[0][0];
	        }else{
	            $this->_clientes[$cliente] = '';
	        }
	    }
	    
	    return $this->_clientes[$cliente];
	}
	
	private function getNomeMarca($marca){
		if(!isset($this->_marcas[$marca])){
			$sql = "select marca from pcmarca where codmarca = $marca";
			$rows = query4($sql);
			if(isset($rows[0][0])){
				$this->_marcas[$marca] = $rows[0][0];
			}else{
				$this->_marcas[$marca] = '';
			}
		}
		
		return $this->_marcas[$marca];
	}
	
	private function getOrigens($sys004 = true){
		$ret = array();
		$ret[] = array("T","Todas");
		$ret[] = array("NTELE","Menos Tele");
		$ret[] = array("PDA","PDA");
		$ret[] = array("NOL","Menos OL");
		$ret[] = array("OL","OL");
		$ret[] = array("TELE","Tele");
		$ret[] = array("PE","Pedido Eletronico");
		$ret[] = array("W","eCommerce");
		if($sys004){
			$temp = array();
			foreach ($ret as $valor){
				$temp[] = $valor[0] . "=" . $valor[1];
			}
			$ret = implode(";", $temp);
		}
		return $ret;
	}
	
}