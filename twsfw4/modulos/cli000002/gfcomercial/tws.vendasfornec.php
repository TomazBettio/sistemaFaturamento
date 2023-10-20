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
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class vendasfornec{
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
		$this->_fornecedores = [];
		$this->_marcas = [];
		
		$this->_titulo = 'Vendas por Fornecedor';
		
		$this->_teste = false;
		$this->_enviaEmailERCteste = true;
		
		$this->_programa = 'vendasfornec';
		
		$param = [];
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);

		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data Ini'	, 'variavel' => 'DATAINI'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Fim'	, 'variavel' => 'DATAFIM'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Regiao'		, 'variavel' => 'SUPER'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getSupervisor();', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'ERC'			, 'variavel' => 'ERC'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getERC();'		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Fornecedores', 'variavel' => 'FORNEC'	, 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Origem ERC'	, 'variavel' => 'ORIGEM'	, 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'C=Cadastro;P=Pedido'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '7', 'pergunta' => 'Produtos'		, 'variavel' => 'PRODUTOS'		, 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '8', 'pergunta' => 'Quebra por Mês'	, 'variavel' => 'QUEBRA'		, 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => 'A', 'pergunta' => 'Por cliente'		, 'variavel' => 'CLIENTE'		, 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '9', 'pergunta' => 'Origem Venda'    ,'variavel' => 'ORIGEM_VENDA'	, 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => $this->getOrigens()));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => 'A', 'pergunta' => 'Venda Por'    	,'variavel' => 'POR'			, 'tipo' => 'T', 'tamanho' => '20', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'E=ERC;O=Operadores'));
		
	}			
	
	private function montaColunas($quebra = 'N', $dtDe = '', $dtAte = '', $cliente = 'N'){
		$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'supervisor'	, 'etiqueta' => 'Regiao Nome'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => 'ERC'					, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => 'ERC Nome'				, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		
		if($cliente == 'S'){
		    $this->_relatorio->addColuna(array('campo' => 'codcli'	, 'etiqueta' => 'Cod.Cli.'		       	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		    $this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'			    , 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		}
		
		$this->_relatorio->addColuna(array('campo' => 'fornec'		, 'etiqueta' => 'Cod.Forn.'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'codmarca'	, 'etiqueta' => 'Cod.Marca'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'marca'		, 'etiqueta' => 'Marca'					, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		
		$this->_periodos = datas::getMeses($dtDe, $dtAte);
		
		if($quebra == 'S' && count($this->_periodos) > 1){
			foreach ($this->_periodos as $i => $periodo){
				$this->_relatorio->addColuna(array('campo' => 'venda'.$i	, 'etiqueta' => 'Venda '.$periodo['mesanoNrCurto']					, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
				$this->_relatorio->addColuna(array('campo' => 'quant'.$i	, 'etiqueta' => 'Quantidade<br>Venda '.$periodo['mesanoNrCurto']	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
				$this->_relatorio->addColuna(array('campo' => 'vendaOL'.$i	, 'etiqueta' => 'Venda OL '.$periodo['mesanoNrCurto']				, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
				$this->_relatorio->addColuna(array('campo' => 'quantOL'.$i	, 'etiqueta' => 'Quantidade<br>Venda OL '.$periodo['mesanoNrCurto']	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
				
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
			$this->_relatorio->addColuna(array('campo' => 'vendaOLTotal', 'etiqueta' => 'Venda OL Total'				, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'quantOLTotal', 'etiqueta' => 'Quantidade<br>Venda OL Total'	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
		}else{
			$temp = $this->_periodos[0];
			$this->_periodos = [];
			$this->_periodos[0] = $temp;
			$this->_periodos[0]['dtDe'] = $dtDe;
			$this->_periodos[0]['dtAte'] = $dtAte;
			
			
			$this->_relatorio->addColuna(array('campo' => 'venda0'		, 'etiqueta' => 'Venda'					, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'quant0'		, 'etiqueta' => 'Quantidade<br>Venda'	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'vendaOL0'	, 'etiqueta' => 'Venda OL'				, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'quantOL0'	, 'etiqueta' => 'Quantidade<br>Venda OL', 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
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
		

		$this->_relatorio->setTitulo($this->_titulo." Periodo: ".datas::dataS2D($dtDe)." a ".datas::dataS2D($dtAte));
		
		if(!$this->_relatorio->getPrimeira()){
		    $this->montaColunas($quebra, $dtDe, $dtAte, $cliente);
			$this->getVendedores();
			if($cliente == 'N'){
    			$this->getVendas($dtDe, $dtAte,$super,$erc, $fornecedores, $origem, $produtos, $quebra);
    
    			asort($this->_fornecedores);
    			asort($this->_marcas);
    			
    			$dados = [];
    			if(count($this->_vendas) > 0){
    				foreach ($this->_vendas as $super => $vendas1){
    					foreach ($vendas1 as $erc => $venda){
    						foreach ($this->_fornecedores as $f => $fornecedor){
    							foreach ($this->_marcas as $m => $marca){
    								if(isset($venda[$f][$m])){
    									$dados[] = $venda[$f][$m];
    									
    									$this->somaTotais('geral'	,0	,0	,$venda[$f][$m]); //Total Geral
    									$this->somaTotais('geral'	,$f	,0	,$venda[$f][$m]); //Total Geral
    									$this->somaTotais('geral'	,$f	,$m	,$venda[$f][$m]); // Total Geral por fornecedor e marca
    									$this->somaTotais($super	,0	,0	,$venda[$f][$m]); // Total Super
    									$this->somaTotais($super	,$f	,0	,$venda[$f][$m]); //Total Super por fornecedor
    									$this->somaTotais($super	,$f	,$m	,$venda[$f][$m]); //Total Super por fornecedor e marca
    								}
    							}
    						}
    					}
    				}
    			}
 			}else{
   			    $this->getVendasClientes($dtDe, $dtAte,$super,$erc, $fornecedores, $origem, $produtos, $quebra);
   			    
   			    asort($this->_fornecedores);
   			    asort($this->_marcas);
   			    asort($this->_clientes);
    			    
    			$dados = [];
    			if(count($this->_vendas) > 0){
    			    if(count($this->_vendas) > 0){
    			        foreach ($this->_clientes as $c => $cli){
        			        foreach ($this->_vendas[$c] as $super => $vendas1){
        			            foreach ($vendas1 as $erc => $venda){
        			                foreach ($this->_fornecedores as $f => $fornecedor){
        			                    foreach ($this->_marcas as $m => $marca){
        			                    	if(isset($this->_vendas[$c][$super][$erc][$f][$m])){
        			                    		$dados[] = $this->_vendas[$c][$super][$erc][$f][$m];
        			                        }
    			                        }
    			                    }
    			                }
    			            }
    			        }
    			    }
    			}
 			}
			
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}else{
			$this->montaColunas();
		}
		$ret .= $this->_relatorio;
		
		return $ret;
	}

	function schedule($param){
		$emails = str_replace(',', ';', $param);
		
		$mes = date('m');
		$ano = date('Y');
		log::gravaLog($this->_programa, 'Inicio: '.$mes.'/'.$ano);
		
		//Se for a primeira execucao do mes envia geral do mes passado
		if(!verificaExecucaoSchedule($this->_programa,$ano.$mes)){
			$mes--;
			if($mes == 0){
				$mes = 12;
				$ano--;
			}
			$mes = (int)$mes;
			if($mes< 10){
				$mes= '0'.$mes;
			}
		}
		
		log::gravaLog($this->_programa, 'Executando mês: '.$mes.'/'.$ano);
		
		$dataIni = $ano.$mes.'01';
		$dataFim = $ano.$mes.date("t", mktime(0,0,0,$mes,'01',$ano));
		
		$this->montaColunas('N', $dataIni, $dataFim);
		
		$this->getVendedores();
		$this->getVendas($dataIni, $dataFim,'','');

		
echo "<br>\nDatas: $dataIni a $dataFim <br>\n";

		$dados = [];
		$titulo = $this->_titulo." Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setTitulo($titulo);
		$this->_relatorio->setToExcel(true);
			
		$dados = [];
		if(count($this->_vendas) > 0){
			foreach ($this->_vendas as $super => $vendas){
				$vendasSuper = [];
				foreach ($vendas as $erc => $venda){
					foreach ($this->_fornecedores as $fornec => $fornecedor){
						foreach ($this->_marcas as $m => $marca){
							if(isset($venda[$fornec][$m])){
								$dados[] = $venda[$fornec][$m];
								$vendasSuper[] = $venda[$fornec][$m];
									
								$this->somaTotais('geral'	,0		,0 , $venda[$fornec][$m]); //Total Geral
								$this->somaTotais('geral'	,$fornec,0 , $venda[$fornec][$m]); //Total Geral
								$this->somaTotais('geral'	,$fornec,$m, $venda[$fornec][$m]); // Total Geral por fornecedor
								$this->somaTotais($super	,0		,0 , $venda[$fornec][$m]); // Total Super
								$this->somaTotais($super	,$fornec,0 , $venda[$fornec][$m]); // Total Super
								$this->somaTotais($super	,$fornec,$m, $venda[$fornec][$m]); //Total Super por fornecedor
							}
						}
					}
				}
				
				foreach ($this->_fornecedores as $fornec => $fornecedor){
					foreach ($this->_marcas as $m => $marca){
						if($super != 15){
							if(isset($this->_totais[$super][$fornec][$m])){
								$dados[] = $this->_totais[$super][$fornec][$m];
							}
							if(isset($this->_totais[$super][$fornec][$m])){
								$vendasSuper[] = $this->_totais[$super][$fornec][$m];
							}
						}
					}
					if(isset($this->_totais[$super][$fornec][0])){
						$dados[] = $this->_totais[$super][$fornec][0];
						$vendasSuper[] = $this->_totais[$super][$fornec][0];
					}
				}
				if(isset($this->_totais[$super][0][0])){
					$dados[] = $this->_totais[$super][0][0];
					$vendasSuper[] = $this->_totais[$super][0][0];
				}
				
				$this->_relatorio->setDados($vendasSuper);
				$email = $this->_super[$super]['email'];
				if(!$this->_teste && $email != ''){
					$this->_relatorio->enviaEmail($email,$titulo);
					log::gravaLog($this->_programa, $dataIni.'/'.$dataFim.' Email Super: '.$super.' - '.$email);
				}else{
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$super.' - '.$email);
				}
					
			}
			foreach ($this->_fornecedores as $fornec => $fornecedor){
				foreach ($this->_marcas as $m => $marca){
					if(isset($this->_totais['geral'][$fornec][$m])){
						$dados[] = $this->_totais['geral'][$fornec][$m];
					}
				}
				if(isset($this->_totais['geral'][$fornec][0])){
					$dados[] = $this->_totais['geral'][$fornec][0];
				}
			}
			$dados[] = $this->_totais['geral'][0];
		}
			
		$this->_relatorio->setDados($dados);
		
		if(!$this->_teste){
			$this->_relatorio->enviaEmail($emails,$titulo);
			log::gravaLog($this->_programa, "Enviado email Geral: ".$emails);
		}else{
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
		}
		
		gravaExecucaoSchedule($this->_programa,$ano.$mes);
	}
	
	
	function somaTotais($total,$fornec,$marca, $v){
		if(!isset($this->_totais[$total][$fornec][$marca])){
			$temp = [];
			if($total == 'geral'){
				$temp['super']	 	= '';
				$temp['supervisor'] = 'Total Geral';
			}else{
				$temp['super']	 	= '';
				$temp['supervisor'] = 'Total '.$this->_super[$total]['nome'];
			}
			$temp['erc'] 		= '';
			$temp['vendedor'] 	= '';
			
			if($fornec == 0){
				$temp['fornec'] 	= '';
				$temp['fornecedor'] = '';
			}else{
				$temp['fornec'] 	= $fornec;
				$temp['fornecedor'] = $this->_fornecedores[$fornec];
			}
			if($marca == 0){
				$temp['codmarca'] 	= '';
				$temp['marca'] 		= '';
			}else{
				$temp['codmarca'] 	= $marca;
				$temp['marca'] 		= $this->_marcas[$marca];
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
			
			$this->_totais[$total][$fornec][$marca] = $temp;
		}

		foreach($this->_periodos as $i => $periodo){
			$this->_totais[$total][$fornec][$marca]['venda'.$i] 	+= $v['venda'.$i];
			$this->_totais[$total][$fornec][$marca]['quant'.$i] 	+= $v['quant'.$i];
			$this->_totais[$total][$fornec][$marca]['vendaOL'.$i] += $v['vendaOL'.$i];
			$this->_totais[$total][$fornec][$marca]['quantOL'.$i] += $v['quantOL'.$i];
			
			if(count($this->_periodos) > 1){
				//Totais
				$this->_totais[$total][$fornec][$marca]['vendaTotal']  	+= $v['vendaTotal'];    
				$this->_totais[$total][$fornec][$marca]['quantTotal']		+= $v['quantTotal'];    
				$this->_totais[$total][$fornec][$marca]['vendaOLTotal'] 	+= $v['vendaOLTotal'];  
				$this->_totais[$total][$fornec][$marca]['quantOLTotal']	+= $v['quantOLTotal'];  
			}
		}
	}
	
	function geraMatriz($erc, $super, $fornecedor, $marca, $cliente = ''){
		if(!isset($this->_vendas[$super][$erc][$fornecedor][$marca])){
			$temp = [];
			$temp['super']	 	= $super;
			$temp['supervisor'] = $this->_super[$super]['nome'];
			$temp['erc'] 		= $erc;
			$temp['vendedor'] 	= $this->_erc[$erc]['nome'];
			
			if($cliente != ''){
			    $temp['codcli']     = $cliente;
			    $temp['cliente']    = $this->getNomeCliente($cliente);
			}
			
			$temp['fornec'] 	= $fornecedor;
			$temp['fornecedor'] = $this->getNomeFornecedor($fornecedor);
				
			$temp['codmarca'] 	= $marca;
			$temp['marca']		= $this->getNomeMarca($marca);
					
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
			
			if($cliente != ''){
				$this->_vendas[$cliente][$super][$erc][$fornecedor][$marca] = $temp;
			}else{
				$this->_vendas[$super][$erc][$fornecedor][$marca] = $temp;
			}
		}
	}
	
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

	function getVendas($dtDe, $dtAte, $superParam, $ercParam, $fornecParam = '', $origem = 'P', $produtos = '', $quebra = 'N'){
		$param = [];
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
		if($origem == 'P'){
			$campos = array('CODUSUR','CODFORNEC','CODMARCA');
		}else{
			$campos = array('ERCCLI','CODFORNEC','CODMARCA');
		}

		
		foreach ($this->_periodos as $i => $periodo){
			$param['origem'] = 'NOL';
			$param['bonificacao'] = false;
			$vendas = vendas1464Campo($campos, $periodo['dtDe'], $periodo['dtAte'], $param);
//if($i > 0)
//print_r($vendas);		
			if(count($vendas) > 0){
				foreach ($vendas as $erc => $venda){
					foreach ($venda as $fornec => $v){
						foreach ($v as $marca => $m){
							$super = $this->_erc[$erc]['super'];
							if(!isset($this->_vendas[$super][$erc][$fornec][$marca])){
								$this->geraMatriz($erc, $super, $fornec, $marca);
							}
//print_r($this->_vendas[$super][$erc][$fornec][$marca]);				
							$this->_vendas[$super][$erc][$fornec][$marca]['venda'.$i] = $m['venda'];
							$this->_vendas[$super][$erc][$fornec][$marca]['quant'.$i] = $m['quant'];
							
							if(count($this->_periodos) > 1){
								//Totais
								$this->_vendas[$super][$erc][$fornec][$marca]['vendaTotal'] += $m['venda'];
								$this->_vendas[$super][$erc][$fornec][$marca]['quantTotal']	+= $m['quant'];
							}
							
						}
					}
				}
			}
			
			$campos = array('ERCCLI','CODFORNEC','CODMARCA');
			$param['origem'] = 'OL';
			$vendasOL = vendas1464Campo($campos, $periodo['dtDe'], $periodo['dtAte'], $param);
			if(count($vendasOL) > 0){
				foreach ($vendasOL as $erc => $venda){
					foreach ($venda as $fornec => $v){
						foreach ($v as $marca => $m){
							$super = $this->_erc[$erc]['super'];
							if(!isset($this->_vendas[$super][$erc][$fornec][$marca])){
								$this->geraMatriz($erc, $super, $fornec, $marca);
							}
							
							$this->_vendas[$super][$erc][$fornec][$marca]['vendaOL'.$i] = $m['venda'];
							$this->_vendas[$super][$erc][$fornec][$marca]['quantOL'.$i] = $m['quant'];
							
							if(count($this->_periodos) > 1){
								//Totais
								$this->_vendas[$super][$erc][$fornec][$marca]['vendaOLTotal'] += $m['venda'];
								$this->_vendas[$super][$erc][$fornec][$marca]['quantOLTotal'] += $m['quant'];
							}
							
						}
					}
				}
			}
			
		}
		
//print_r($this->_vendas);
	}
	
	private function getVendasClientes($dtDe, $dtAte, $superParam, $ercParam, $fornecParam = '', $origem = 'P', $produtos = '', $quebra = 'N'){
	    $param = [];
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
	    if($origem == 'P'){
	        $campos = array('CODUSUR','CODFORNEC','CODMARCA', 'CODCLI');
	    }else{
	        $campos = array('ERCCLI','CODFORNEC','CODMARCA', 'CODCLI');
	    }
	    
	    
	    foreach ($this->_periodos as $i => $periodo){
	        $param['origem'] = 'NOL';
	        $param['bonificacao'] = false;
	        $vendas = vendas1464Campo($campos, $periodo['dtDe'], $periodo['dtAte'], $param);
	        //if($i > 0)
	            //print_r($vendas);
	        if(count($vendas) > 0){
	            foreach ($vendas as $erc => $venda){
	                foreach ($venda as $fornec => $v){
	                    foreach ($v as $marca => $m){
	                        foreach ($m as $cliente => $c){
    	                        $super = $this->_erc[$erc]['super'];
    	                        if(!isset($this->_vendas[$cliente][$super][$erc][$fornec][$marca])){
    	                            $this->geraMatriz($erc, $super, $fornec, $marca, $cliente);
    	                        }
    	                        //print_r($this->_vendas[$super][$erc][$fornec][$marca]);
    	                        $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['venda'.$i] = $c['venda'];
    	                        $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['quant'.$i] = $c['quant'];
    	                        
    	                        if(count($this->_periodos) > 1){
    	                            //Totais
    	                            $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['vendaTotal'] += $c['venda'];
    	                            $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['quantTotal'] += $c['quant'];
    	                        }
	                        }
	                    }
	                }
	            }
	        }
	        
	        $campos = array('ERCCLI','CODFORNEC','CODMARCA', 'CODCLI');
	        $param['origem'] = 'OL';
	        $vendasOL = vendas1464Campo($campos, $periodo['dtDe'], $periodo['dtAte'], $param);
	        if(count($vendasOL) > 0){
	            foreach ($vendasOL as $erc => $venda){
	                foreach ($venda as $fornec => $v){
	                    foreach ($v as $marca => $m){
	                        foreach ($m as $cliente => $c){
    	                        $super = $this->_erc[$erc]['super'];
    	                        if(!isset($this->_vendas[$cliente][$super][$erc][$fornec][$marca])){
    	                            $this->geraMatriz($erc, $super, $fornec, $marca, $cliente);
    	                        }
    	                        
    	                        $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['vendaOL'.$i] = $c['venda'];
    	                        $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['quantOL'.$i] = $c['quant'];
    	                        
    	                        if(count($this->_periodos) > 1){
    	                            //Totais
    	                            $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['vendaOLTotal'] += $c['venda'];
    	                            $this->_vendas[$cliente][$super][$erc][$fornec][$marca]['quantOLTotal'] += $c['quant'];
    	                        }
	                        }
	                    }
	                }
	            }
	        }
	        
	    }
	    
	    //print_r($this->_vendas);
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
}