<?php
/*
 * Data Criacao 5 de nov de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 *
 *  Alterções:
 *           16/02/2019 - Emanuel - Migração para intranet2
 *           23/01/2023 - Thiel   - Migração para Intranet4
 *           14/02/2023 - Emanuel - Arrumado o schedule no intranet4
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class campanhas{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Dados da promocao
	var $_promocao;
	
	//ID promo
	var $_promo;
	
	//Dados das sub promocoes
	var $_subs;
	
	//codigos envolvidos (produtos,fornecedores.marcas,...)
	var $_codigos;
	
	//Vendas
	var $_vendas = [];
	
	//Titulo
	var $_titulo;
	
	//Supervisores
	var $_super;
	
	//ERCs
	var $_erc;
	
	//Operadores
	var $_operadores;
	
	//Indica que se é teste (não envia email se for)
	var $_teste;
	
	//Quando for teste se envia os emails do ERC para o tester
	var $_enviaEmailERCteste;
	
	//Utilizado para guardar totais
	var $_totais;
	
	//Periodo indicado nos parametros
	var $_dataIni;
	var $_dataFim;
	
	//Produtos quando a sub campanha controla venda por itens
	var$_itensVendaItens;
	
	//Integradoras quando a sub campanha controla venda por itens
	var$_integradorasVendaItens;
	
	//ERC e Região original do cliente
	private $_ercOriginal;
	
	//Super que não recebem email
	private $_superFora = array();
	
	//Nome e código cli principal do cliente
	private $_clientes;
	
	//Indica se o MIX vai ser calculado pelo total de clientes positivado ou pelo total de clientes do ERC ('total' => total de clientes)
	private $_MIXtotalCli;
	
	//Quantidade de clientes dos ERCs
	private $_quantClientesERC;
	
	//Data fim usada no schwdule
	private $_dataFimShedule;
	
	//Indica se é para mostrar o fechamento da campanha
	private $_fechamento;
	
	//Pastas x Subcampanhas (fechamento)
	private $_pastasXsub = [];
	
	//Subcampanhas x Pastas (fechamento)
	private $_subXpastas = [];
	
	//Parametros da premiacao
	private $_premio = [];
	
	//Códigos dos operadores x erc
	private $_operador_erc = [];
	
	//Códigos dos erc x operadores
	private $_erc_operador = [];
	
	function __construct(){
		set_time_limit(0);
		
		$this->_superFora[15] = 15;
		
		$this->_teste = false;
		$this->_enviaEmailERCteste = false;
		
		$this->_dataIni = '';
		$this->_dataFim = '';
		
		$this->_programa = 'campanhas';
		
		$this->_MIXtotalCli = 'total';
		
		if(false){
			sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Promocao'		, 'variavel' => 'PROMO'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'campanhas::getCampanhas();'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Regiao'		, 'variavel' => 'SUPER'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getSupervisor();'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'ERC'			, 'variavel' => 'ERC'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sys044_getERC();'			, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''							, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Data Ate'		, 'variavel' => 'DATAFIM'	,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''							, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Fechamento'	, 'variavel' => 'FECHAMENTO','tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''							, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'N=Não;S=Sim'));
		}
	}
	
	private function montaRelatorio(){
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$this->_promocao['vendedor'] 	= $this->_promocao['vendedor'] ?? '';
		$this->_promocao['tipoMeta'] 	= $this->_promocao['tipoMeta'] ?? '';
		$this->_promocao['totalMeta'] 	= $this->_promocao['totalMeta'] ?? '';
		$this->_promocao['totalReal'] 	= $this->_promocao['totalReal'] ?? '';
		$this->_promocao['porCliente']	= $this->_promocao['porCliente'] ?? '';
	
		//Mostra supervisor somente quando for por ERC
		if($this->_promocao['vendedor'] == 'E'){
			$this->_relatorio->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Regiao'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'supervisor'	, 'etiqueta' => 'Regiao Nome'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		}
		switch ($this->_promocao['vendedor']) {
			case 'S':
				$cabCod = 'Regiao';
				$cadDesc = 'Regiao Nome';
				break;
			case 'T':
			case 'X':
				$cabCod = 'Operador';
				$cadDesc = 'Operador Nome';
				break;
			default:
				$cabCod = 'ERC';
				$cadDesc = 'ERC Nome';
				break;
		}
		
		$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => $cabCod		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => $cadDesc	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$tipoTotal = 'N';
		
		if($this->_promocao['tipoMeta'] == 'C'){
			//Meta por Cliente ou Abre por cliente
			$this->_relatorio->addColuna(array('campo' => 'codcli'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Nome Cliente'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'principal'	, 'etiqueta' => 'Cliente Princ.', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		}elseif($this->_promocao['tipoMeta'] == 'P'){
			//Meta por Cliente ou Abre por cliente
			$this->_relatorio->addColuna(array('campo' => 'principal'	, 'etiqueta' => 'Cliente Princ.', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
			$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Nome Cliente'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		}
		
		if(isset($this->_subs[$this->_promo]) && count($this->_subs[$this->_promo]) > 0){
			foreach ($this->_subs[$this->_promo] as $i => $sub){
				// Se Meta = Venda/Mix/Preço Médio -> tipo = Valor, Meta = Quantidade ou Positivação -> tipo = N
				$tipo = $sub['meta'] == 'V' || $sub['meta'] == 'M' || $sub['meta'] == 'N' ? 'V' : 'N';
				if($tipo == 'V'){
					$tipoTotal = 'V';
				}
				
				if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
					//Abre os produtos em cada coluna
					$this->getProdutosCampanhaItem($this->_promo, $i);
					foreach ($this->_itensVendaItens as $produto){
						$etiqueta = 'Venda<br>'.$produto.' - '.$this->getNomeProduto($produto);
						$this->_relatorio->addColuna(array('campo' => 'venda'.$produto, 'etiqueta' => $etiqueta, 'tipo' => $tipo, 'width' => 110, 'posicao' => 'D'));
					}
				}elseif($sub['origem'] == 'OL' && $sub['vendaItem'] == 'S'){
					//Abre as integradoras em cada coluna
					$this->getIntegradorasCampanhaItem($this->_promo, $i);
					foreach ($this->_integradorasVendaItens as $integradora){
						$etiqueta = 'Venda<br>'.$integradora.'<br>'.$this->getNomeIntegradora($integradora);
						//echo "$integradora - $etiqueta <br>\n";
						$this->_relatorio->addColuna(array('campo' => 'venda'.$integradora, 'etiqueta' => $etiqueta, 'tipo' => $tipo, 'width' => 110, 'posicao' => 'D'));
					}
				}else{
					if($sub['impMeta'] != 'N'){
						$etiqueta = $sub['tituloMeta'] == '' ? 'Sugestao<br>'.$sub['titulo'] : 'Sugestao<br>'.$sub['tituloMeta'];
						$this->_relatorio->addColuna(array('campo' => 'meta'.$i		, 'etiqueta' => $etiqueta	, 'tipo' => $tipo, 'width' => 110, 'posicao' => 'D'));
					}
					if($sub['impReal'] != 'N'){
						
						$etiqueta = $sub['tituloReal'] == '' ? 'Venda<br>'.$sub['titulo'] : 'Venda<br>'.$sub['tituloReal'];
						$this->_relatorio->addColuna(array('campo' => 'venda'.$i	, 'etiqueta' => $etiqueta	, 'tipo' => $tipo, 'width' => 110, 'posicao' => 'D'));
					}
				}
				
				if($this->_fechamento == 'S'){
					$this->_relatorio->addColuna(array('campo' => 'perc'.$i		, 'etiqueta' => '%'		, 'tipo' => 'V', 'width' => 150, 'posicao' => 'E'));
					if($i != $this->_promocao['prem_global']){
						$this->_relatorio->addColuna(array('campo' => 'premio'.$i	, 'etiqueta' => 'Premio', 'tipo' => 'RS', 'width' => 150, 'posicao' => 'E'));
					}
				}
				
				//Imprime coluna % realizado acima da meta?
				if($sub['percent_acima'] == 'S'){
					$etiqueta = $sub['titulo_percent'] == '' ? '% Realizado acima Meta<br>'.$sub['titulo'] : $sub['titulo_percent'];
					$this->_relatorio->addColuna(array('campo' => 'percent_acima'.$i	, 'etiqueta' => $etiqueta	, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
				}
			}
		}

		if($this->_fechamento == 'S'){
			$this->_relatorio->addColuna(array('campo' => 'premioTotal'		, 'etiqueta' => 'Premio Total'		, 'tipo' => 'RS', 'width' => 150, 'posicao' => 'E'));
		}
		
		if($this->_promocao['totalMeta'] == 'S'){
			$this->_relatorio->addColuna(array('campo' => 'totalMeta'	, 'etiqueta' => 'Sugestao<br>Total'		, 'tipo' => $tipoTotal, 'width' => 110, 'posicao' => 'D'));
		}
		if($this->_promocao['totalReal'] == 'S'){
			$this->_relatorio->addColuna(array('campo' => 'totalReal'	, 'etiqueta' => 'Realizado<br>Total'	, 'tipo' => $tipoTotal, 'width' => 110, 'posicao' => 'D'));
		}
	}
	
	function index(){
		global $config;
		$ret = '';
		$this->montaRelatorio();
		$filtro = $this->_relatorio->getFiltro();
		
		$promo 	= $filtro['PROMO'];
		$super 	= $filtro['SUPER'];
		$erc 	= $filtro['ERC'];
		$this->_dataIni = $filtro['DATAINI'];
		$this->_dataFim = $filtro['DATAFIM'];
		$this->_fechamento = $filtro['FECHAMENTO'];
		
		if(!$this->_relatorio->getPrimeira() && $promo != ''){
			
			$this->getPromocao($promo);
			$inativo = false;
			
			if($this->_promocao['vendedor'] == 'T'){
				$inativo = true;
			}
			/*
			 * 27/02/19 - Para atender o tele (Vanessa) quando o relatório for executado na intranet deve aparecer todos os vendedores (ativos e inativos)
			 */
			$this->getVendedores($inativo);
			$this->montaRelatorio();
			$this->getMetas($super,$erc, $this->_promocao['vendedor']);
			$this->_relatorio->setTitulo($this->_promocao['titulo']." Periodo: ".datas::dataS2D($this->_promocao['ini'] )." a ".datas::dataS2D($this->_promocao['fim'] ));
			$this->getVendas($super,$erc);
			$this->_relatorio->setToExcel(true);
			
			if($this->_fechamento == 'S'){
				$campos = $this->_relatorio->getConfigCampos();
				$this->getPremios();
				$arquivo = 'fechamento.xlsx';
				
				$fechamento = new fechamento_campanhas($campos, $this->_promocao, $this->_vendas, $this->_premio, $this->_subs, $arquivo);
				
				$botao = [];
				// $botao['cor'] = 'sucess';
				$botao['texto'] = 'Baixar Planilha';
				$botao['cor'] = 'danger';
				$botao["onclick"] = "op2('".$config["tempURL"].$arquivo."')";
				$this->_relatorio->addBotao($botao);

				//-------- FIM FECHAMENTO ------------------------------------------------------------------------------------------------
			}else{
				//Se campanha por cliente e imprime clientes sem vendas
				if($this->_promocao['tipoMeta'] == 'C' && $this->_promocao['cliSemVenda'] == 'S'){
					//Gera todos os clientes
					$this->geraMatrizTodosClientes();
				}
				
				
				//print_r($this->_vendas);
				$dados = [];
				if(count($this->_vendas) > 0){
					if($this->_promocao['vendedor'] == 'E'){
						foreach ($this->_vendas as $super => $vend){
							foreach ($vend as $erc => $v){
								if($this->_promocao['tipoMeta'] == 'E'){
									$dados[] = $v;
								}elseif($this->_promocao['tipoMeta'] == 'C' || $this->_promocao['tipoMeta'] == 'P'){
									foreach ($v as $vCli){
										$dados[] = $vCli;
									}
								}
							}
						}
					}elseif($this->_promocao['vendedor'] == 'S'){
						foreach ($this->_vendas as $cod => $venda){
							if($this->_promocao['tipoMeta'] == 'E'){
								$dados[] = $venda;
							}elseif($this->_promocao['tipoMeta'] == 'C'){
								foreach ($v as $vCli){
									$dados[] = $vCli;
								}
							}
						}
					}else{
						foreach ($this->_vendas as $cod => $venda){
							if($this->verificaZerado($venda)){
								if($this->_promocao['tipoMeta'] == 'E'){
									$dados[] = $venda;
								}elseif($this->_promocao['tipoMeta'] == 'C'){
									foreach ($v as $vCli){
										$dados[] = $vCli;
									}
								}
							}
						}
					}
				}
				
				$this->_relatorio->setDados($dados);
			}
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	private function ajustaDadosMixPosTotal($dados, $pasta_MIX, $pasta_POS){
		$fechamento = $this->_pastasXsub['totalFechamento'];
		foreach ($dados[$fechamento] as $super => $d1){
			foreach ($d1 as $erc => $d){
				$dados[$fechamento][$super][$erc]['total'.$this->_subXpastas[$pasta_MIX]] = $dados[$pasta_MIX][$super][$erc]['premio'];
				$dados[$fechamento][$super][$erc]['totalGeral'] += $dados[$pasta_MIX][$super][$erc]['premio'];
				
				$dados[$fechamento][$super][$erc]['total'.$this->_subXpastas[$pasta_POS]] = $dados[$pasta_POS][$super][$erc]['premio'];
				$dados[$fechamento][$super][$erc]['totalGeral'] += $dados[$pasta_POS][$super][$erc]['premio'];
			}
		}
		
		return $dados;
	}
	
	private function ajustaDadosMixPos($dados, $pasta_GLOBAL, $pasta_MIX, $pasta_POS, $percent_MIX, $percent_POS){
		if($pasta_GLOBAL !== '' && $pasta_MIX !== ''){
			$dados[$pasta_MIX] = $this->calculaPasta($dados[$pasta_MIX], $dados[$pasta_GLOBAL], $percent_MIX);
		}
		if($pasta_GLOBAL  !== '' && $pasta_POS !== ''){
			$dados[$pasta_POS] = $this->calculaPasta($dados[$pasta_POS], $dados[$pasta_GLOBAL], $percent_POS);
		}
		return $dados;
	}
	
	private function calculaPasta($dados, $dadosGlobal, $percentual){
		foreach ($dados as $super => $d1){
			foreach ($d1 as $erc => $d){
				if(isset($dadosGlobal[$super][$erc])){
					$dados[$super][$erc]['geral_vend'] = $dadosGlobal[$super][$erc]['realizado'];
					$dados[$super][$erc]['geral_real'] = $dadosGlobal[$super][$erc]['percentReal'];
					if($d['percentReal'] >= $d['percentMin'] && $dadosGlobal[$super][$erc]['percentReal'] >= $dadosGlobal[$super][$erc]['percentMin']){
						$dados[$super][$erc]['premio'] = rand(($dadosGlobal[$super][$erc]['realizado'] * ($percentual/100)),2);
					}else{
						$dados[$super][$erc]['premio'] = 0;
					}
				}
			}
		}
		return $dados;
	}
	
	public function schedule($param){
		//Seleciona as campanhas que devem ser acompanhadas/enviado email de fechamento
		$dia = date('Ymd');
		$sql = "SELECT * FROM gf_camp_campanhas WHERE ativo = 'S' AND ((ini <= '$dia' AND fim >= '$dia') OR fechamento = '$dia')";
		if($this->_teste){
			$sql = "SELECT * FROM gf_camp_campanhas WHERE id = 'BD04ZQM7229BT5T'";
		}
		echo "\n<br>\n$sql <br>\n";
		$rows = query($sql);
		
		echo "Campanhas encontradas: ".count($rows)."<br>\n";
		if(count($rows) > 0){
			$this->getVendedores();
			foreach ($rows as $row){
				$super = '';
				$erc = '';
				
				$this->_promo = 0;
				$this->_promocao = array();
				$this->_dataIni = '';
				$this->_dataFim = '';
				$this->_subs = array();
				$this->_vendas = array();
				$this->_enviados = 0;
				$this->_totais = array();
				
				$this->getPromocao($row['id']);
				log::gravaLog("campanhas", 'Executando '.$row['id'].' - '.$row['titulo']);
				if($this->_promocao['enviaEmail'] == 'S'){
					$this->montaRelatorio();
					$this->getMetas($super,$erc);
					//print_r($this->_promocao);
					//print_r($this->_subs);
					//print_r($this->_codigos);
					
					$this->getVendas($super,$erc);
					//print_r($this->_vendas);
					$this->enviaEmails($param, $this->_promocao['email_para']);
					log::gravaLog("campanhas", 'Envia email '.$row['id'].' - '.$row['titulo']);
				}else{
					log::gravaLog("campanhas", 'Não envia email '.$row['id'].' - '.$row['titulo']);
				}
			}
		}
	}
	
	private function enviaEmails($param, $email_para = ''){
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		if(empty($email_para)){
			$emails = str_replace(',', ';', $param);
		}else{
			$emails = $email_para;
		}
		
		if(empty($this->_dataFimShedule)){
			$this->_dataFimShedule = $this->_promocao['fim'];
		}
		
		$titulo = $this->_promocao['titulo']." Periodo: ".datas::dataS2D($this->_promocao['ini'] )." a ".datas::dataS2D($this->_dataFimShedule );
		
		$this->_relatorio->setTitulo($titulo);
		$this->_relatorio->setAuto(true);
		$arquivoExcel = str_replace(' ', '_', $this->_promocao['titulo']);
		$arquivoExcel = str_replace('/', '-', $arquivoExcel);
		$this->_relatorio->setToExcel(true,$arquivoExcel.'_'.datas::dataS2D($this->_promocao['ini'],2,'.').'_a_'.datas::dataS2D($this->_promocao['fim'],2,'.'));
		
		$dados = array();
		if(count($this->_vendas) > 0){
			$dadosGeral = array();
			//print_r($this->_vendas);
			if($this->_promocao['tipoMeta'] == 'E'){
				//Meta por ERC
				if($this->_promocao['vendedor'] == 'E'){
					foreach ($this->_vendas as $super => $vend){
						$dadosSuper = array();
						foreach ($vend as $erc => $v){
							$dados = array();
							$dados[] = $v;
							$this->_relatorio->setDados($dados);
							$email = $this->_erc[$erc]['email'];
							if(!$this->_teste){
								// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
								//								$this->_relatorio->setEnviaTabelaEmail(false);
								//$this->_relatorio->enviaEmail($email,$titulo);
								$this->_relatorio->agendaEmail('','08:00','campanhas',$email,$titulo);
								log::gravaLog('campanhas', $this->_promocao['titulo'].' Email ERC: '.$erc.' - '.$email);
							}else{
								if($this->_enviaEmailERCteste){
									if($this->_enviaEmailERCteste){
										// Só envia 10 emails
										if(!isset($this->_enviados)){
											$this->_enviados = 0;
										}
										//										$this->_enviados++;
										if($this->_enviados <= 10){
											$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$erc.' - '.$email);
										}
									}
								}
							}
							
							$dadosGeral[] = $v;
							$dadosSuper[] = $v;
							
							$this->somaTotais('geral',$v);
							$this->somaTotais($super,$v);
						}
						$dadosSuper[] = $this->_totais[$super];
						$dadosGeral[] = $this->_totais[$super];
						
						$this->_relatorio->setDados($dadosSuper);
						$email = $this->_super[$super]['email'];
						if(!$this->_teste){
							if(!isset($this->_superFora[$super])){
								// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
								//								$this->_relatorio->setEnviaTabelaEmail(true);
								//$this->_relatorio->enviaEmail($email,$titulo);
								
								//$email .= ';suporte@thielws.com.br';
								
								$this->_relatorio->agendaEmail('','08:00','campanhas',$email,$titulo);
								log::gravaLog('campanhas', $this->_promocao['titulo'].' Email Super: '.$super.' - '.$email);
							}
						}else{
							$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$super.' - '.$email);
						}
						
					}
				}else{
					foreach ($this->_vendas as $cod => $v){
						$dados = array();
						$dados[] = $v;
						$this->_relatorio->setDados($dados);
						if($this->_promocao['vendedor'] == 'S'){
							$email = $this->_super[$cod]['email'];
						}else{
							$email = $this->_operadores[$cod]['email'];
						}
						if(!$this->_teste){
							// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
							//							$this->_relatorio->setEnviaTabelaEmail(false);
							//$this->_relatorio->enviaEmail($email,$titulo);
							$this->_relatorio->agendaEmail('','08:00','campanhas',$email,$titulo);
							log::gravaLog('campanhas', $this->_promocao['titulo'].' Vendedor: '.$this->_promocao['vendedor'].' Email ERC: '.$cod.' - '.$email);
						}else{
							$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - TLMKT - '.$cod.' - '.$email);
						}
						$dadosGeral[] = $v;
						$this->somaTotais('geral',$v);
					}
				}
				$dadosGeral[] = $this->_totais['geral'];
			}elseif($this->_promocao['tipoMeta'] == 'C' || $this->_promocao['tipoMeta'] == 'P'){
				//Meta por clientes
				if($this->_promocao['vendedor'] == 'E'){
					//print_r($this->_vendas);
					foreach ($this->_vendas as $super => $vend){
						$dadosSuper = array();
						foreach ($vend as $erc => $v2){
							$dados = array();
							foreach ($v2 as $v){
								//print_r($v);
								$dados[] = $v;
								$dadosGeral[] = $v;
								$dadosSuper[] = $v;
								
								$this->somaTotais('geral',$v);
								$this->somaTotais($super,$v);
								//$this->somaTotais($erc,$v);
							}
							//$dados[] = $this->_totais[$erc];
							//print_r($dados);
							$this->_relatorio->setDados($dados);
							$email = $this->_erc[$erc]['email'];
							if(!$this->_teste){
								// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
								//								$this->_relatorio->setEnviaTabelaEmail(false);
								//$this->_relatorio->enviaEmail($email,$titulo);
								$this->_relatorio->agendaEmail('','08:00','campanhas',$email,$titulo);
								log::gravaLog('campanhas', $this->_promocao['titulo'].' Email ERC: '.$erc.' - '.$email);
							}else{
								if($this->_enviaEmailERCteste){
									if($this->_enviaEmailERCteste){
										// Só envia 10 emails
										if(!isset($this->_enviados)){
											$this->_enviados = 0;
										}
										$this->_enviados++;
										if($this->_enviados <= 10){
											$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$erc.' - '.$email);
										}
									}
								}
							}
							
						}
						$dadosSuper[] = $this->_totais[$super];
						$dadosGeral[] = $this->_totais[$super];
						
						$this->_relatorio->setDados($dadosSuper);
						$email = $this->_super[$super]['email'];
						if(!$this->_teste){
							if(!isset($this->_superFora[$super])){
								// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
								//								$this->_relatorio->setEnviaTabelaEmail(false);
								//$this->_relatorio->enviaEmail($email,$titulo);
								$this->_relatorio->agendaEmail('','08:00','campanhas',$email,$titulo);
								log::gravaLog('campanhas', $this->_promocao['titulo'].' Email Super: '.$super.' - '.$email);
							}
						}else{
							$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - '.$super.' - '.$email);
						}
						
					}
				}else{
					foreach ($this->_vendas as $cod => $v2){
						$dados = array();
						foreach ($v2 as $v){
							$dados[] = $v;
						}
						$this->_relatorio->setDados($dados);
						if($this->_promocao['vendedor'] == 'S'){
							$email = $this->_super[$cod]['email'];
						}else{
							$email = $this->_operadores[$cod]['email'];
						}
						if(!$this->_teste){
							// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
							//							$this->_relatorio->setEnviaTabelaEmail(false);
							//$this->_relatorio->enviaEmail($email,$titulo);
							$this->_relatorio->agendaEmail('','08:00','campanhas',$email,$titulo);
							log::gravaLog('campanhas', $this->_promocao['titulo'].' Vendedor: '.$this->_promocao['vendedor'].' Email ERC: '.$cod.' - '.$email);
						}else{
							$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' - TLMKT - '.$cod.' - '.$email);
						}
						$dadosGeral[] = $v;
						$this->somaTotais('geral',$v);
					}
				}
				$dadosGeral[] = $this->_totais['geral'];
				
			}
		}//if(count($this->_vendas) > 0)
		$this->_relatorio->setDados($dadosGeral);
		
		if(!$this->_teste){
			// Alterado por solicitação do Daniel em 07/07/22 (receber os relatório que ele está recebendo em planilha, receber no corpo do e-mail)
			//			$this->_relatorio->setEnviaTabelaEmail(false);
			//$this->_relatorio->enviaEmail($emails,$titulo);
			$this->_relatorio->agendaEmail('','08:00','campanhas',$emails,$titulo);
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
			log::gravaLog("campanhas", $this->_promocao['titulo']."  Geral: ".$emails);
		}else{
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo.' Email Geral');
		}
	}
	
	private function somaTotais($total,$v){
		if(!isset($this->_totais[$total])){
			$temp = array();
			if($total == 'geral'){
				$temp['super']	 	= '';
				$temp['supervisor'] = 'Total Geral';
			}else{
				$temp['super']	 	= '';
				$temp['supervisor'] = 'Total '.$this->_super[$total]['nome'];
			}
			$temp['erc'] 		= '';
			$temp['vendedor'] 	= '';
			if(count($this->_subs[$this->_promo]) > 0){
				foreach ($this->_subs[$this->_promo] as $i => $sub){
					if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
						//Abre os produtos em cada coluna
						$this->getProdutosCampanhaItem($this->_promo, $i);
						foreach ($this->_itensVendaItens as $produto){
							$temp['venda'.$produto] = 0;
						}
					}else{
						if($sub['impMeta'] != 'N'){
							$temp['meta'.$i	] = 0;
						}
						$temp['venda'.$i] = 0;
					}
				}
				if($this->_promocao['totalMeta'] == 'S'){
					$temp['totalMeta'] = 0;
				}
				if($this->_promocao['totalReal'] == 'S'){
					$temp['totalReal'] = 0;
				}
				
			}
			
			$this->_totais[$total] = $temp;
		}
		if(count($this->_subs[$this->_promo]) > 0){
			foreach ($this->_subs[$this->_promo] as $i => $sub){
				if(!isset($this->_totais[$total]['meta'.$i])){
					$this->_totais[$total]['meta'.$i] = 0;
				}
				if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
					//Abre os produtos em cada coluna
					$this->getProdutosCampanhaItem($this->_promo, $i);
					foreach ($this->_itensVendaItens as $produto){
						$this->_totais[$total]['venda'.$produto] += $v['venda'.$produto];
					}
				}else{
					if($sub['impMeta'] != 'N'){
						$this->_totais[$total]['meta'.$i] += $v['meta'.$i];
					}
					$this->_totais[$total]['venda'.$i] += isset($v['venda'.$i]) ? $v['venda'.$i] : 0;
				}
				if($this->_promocao['totalMeta'] == 'S'){
					$this->_totais[$total]['totalMeta'] += $v['totalMeta'];
				}
				if($this->_promocao['totalReal'] == 'S'){
					$this->_totais[$total]['totalReal'] += $v['totalReal'];
				}
			}
		}
	}
	
	private function verificaZerado($venda){
		$ret = true;
		$total = 0;
		foreach ($venda as $campo => $valor){
			if($campo != 'erc' && $campo != 'vendedor'){
				$total += $valor;
			}
		}
		if($total == 0){
			$ret = false;
		}
		
		return $ret;
	}
	
	private function getPremios(){
		$sql = "SELECT * FROM gf_camp_premio WHERE campanha = '".$this->_promo."'";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['sub'] 		= $row['sub'];
				$temp['atingimento']= $row['atingimento'];
				$temp['premio'] 	= $row['premio'];
				$temp['tipo'] 		= $row['tipo'];
				
				$this->_premio[$row['sub']] = $temp;
			}
		}else{
			$this->_premio = [];
		}
	}
	
	/*
	 * Retorna a lista de campanhas
	 */
	static public function getCampanhas(){
		$tabela = array();
		$sql = "SELECT * FROM gf_camp_campanhas WHERE ativo = 'S'";
		$rows = query($sql);
		//echo "$sql <br> \n";
		$i = count($tabela);
		foreach ($rows as $row) {
			$tabela[$i][0] = $row['id'];
			$tabela[$i][1] = $row['seq'].' - '.$row['titulo'].' - '.datas::dataS2D($row['ini'],2).' a '.datas::dataS2D($row['fim'],2);
			$i++;
		}
		return $tabela;
	}
	
	
	/*
	 * Carrega dados da promocao
	 */
	private function getPromocao($promo){
		$sql = "SELECT * FROM gf_camp_campanhas WHERE id = '$promo'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row) {
				$this->_promo = $row['id'];
				$this->_promocao['id'] 			= $row['id'];
				$this->_promocao['titulo'] 		= $row['titulo'];
				if($this->_dataIni != '' && $this->_dataFim != ''){
					$this->_promocao['ini'] 		= $this->_dataIni;
					$this->_promocao['fim'] 		= $this->_dataFim;
				}else{
					$this->_promocao['ini'] 		= $row['ini'];
					$this->_promocao['fim'] 		= $row['fim'];
				}
				$this->_promocao['totalReal'] 	= $row['totalReal'];
				$this->_promocao['totalMeta'] 	= $row['totalMeta'];
				$this->_promocao['vendedor'] 	= $row['vendedor'];
				$this->_promocao['enviaEmail'] 	= $row['enviaEmail'];
				$this->_promocao['fechamento'] 	= $row['fechamento'];
				$this->_promocao['origCli'] 	= $row['origCli'];
				$this->_promocao['tipoMeta'] 	= $row['tipoMeta'];
				$this->_promocao['pedidoAte'] 	= $row['pedidoAte'];
				
				$this->_promocao['erc_fora'] 	= trim($row['erc_fora']);
				$this->_promocao['erc_fora']	= str_replace(';', ',', $this->_promocao['erc_fora']);
				$this->_promocao['erc_fora']	= str_replace('|', ',', $this->_promocao['erc_fora']);
				
				$this->_promocao['ped_fora'] 	= trim($row['ped_fora']);
				$this->_promocao['ped_fora']	= str_replace(';', ',', $this->_promocao['ped_fora']);
				$this->_promocao['ped_fora']	= str_replace('|', ',', $this->_promocao['ped_fora']);
				
				$this->_promocao['cli_fora'] 	= trim($row['cli_fora']);
				$this->_promocao['cli_fora']	= str_replace(';', ',', $this->_promocao['cli_fora']);
				$this->_promocao['cli_fora']	= str_replace('|', ',', $this->_promocao['cli_fora']);
				
				$this->_promocao['email_para'] 	= trim($row['email_para']);
				$this->_promocao['email_para']	= str_replace(',', ';', $this->_promocao['email_para']);
				
				$this->_promocao['porCliente']	= $row['porCliente'];
				$this->_promocao['cliSemVenda']	= $row['cliSemVenda'];
				$this->_promocao['ercSemMeta']	= $row['ercSemMeta'];
				
				$this->_promocao['prem_global']		= $row['prem_global'];
				$this->_promocao['prem_mix']		= $row['prem_mix'];
				$this->_promocao['prem_pos']		= $row['prem_pos'];
				$this->_promocao['prem_enc']		= $row['prem_enc'];
				$this->_promocao['prem_perc_mix']	= $row['prem_perc_mix'];
				$this->_promocao['prem_perc_pos']	= $row['prem_perc_pos'];
				$this->_promocao['prem_perc_enc']	= $row['prem_perc_enc'];
				
			}
			$this->getSubCampanhas($this->_promocao['id']);
		}
	}
	
	/*
	 * Carrega subCampanhas
	 */
	private function getSubCampanhas($campanha){
		$sql = "SELECT * FROM gf_camp_subcamp WHERE ativo = 'S' AND campanha = '$campanha' ORDER BY sequencia";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_subs[$campanha][$row['id']]['titulo'] 			= $row['titulo'];
				$this->_subs[$campanha][$row['id']]['tituloMeta']		= $row['tituloMeta'];
				$this->_subs[$campanha][$row['id']]['tituloReal']		= $row['tituloReal'];
				$this->_subs[$campanha][$row['id']]['meta'] 			= $row['meta'];
				$this->_subs[$campanha][$row['id']]['impMeta'] 			= $row['impMeta'];
				$this->_subs[$campanha][$row['id']]['impReal'] 			= $row['impReal'];
				$this->_subs[$campanha][$row['id']]['tipo'] 			= $row['tipo'];
				$this->_subs[$campanha][$row['id']]['wt'] 				= $row['campanhaWT'];
				$this->_subs[$campanha][$row['id']]['origem']			= $row['origem'];
				$this->_subs[$campanha][$row['id']]['vendaItem']		= $row['vendaItem'];
				$this->_subs[$campanha][$row['id']]['prodFora']			= $row['produto_fora'];
				$this->_subs[$campanha][$row['id']]['min_positivacao']	= $row['min_positivacao'];
				$this->_subs[$campanha][$row['id']]['percent_acima']	= $row['percent_acima'];
				$this->_subs[$campanha][$row['id']]['titulo_percent']	= $row['titulo_percent'];
			}
			$this->getCodigos($campanha);
		}
	}
	
	private function getCodigos($campanha){
		$temp = array();
		$sql = "SELECT * FROM gf_camp_itens WHERE campanha = '$campanha' ORDER BY sub, valor";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp[$row['sub']][] = $row['valor'];
			}
			foreach ($temp as $sub => $valores){
				$this->_codigos[$sub] = implode(',', $valores);
			}
		}
		
	}
	
	private function getMetas($superPar,$ercPar, $vendedor = ''){
		$cliente = 0;
		$sql = "SELECT * FROM gf_camp_metas WHERE campanha = '".$this->_promo."'";
		if($ercPar !== ''){
			$sql .= " AND erc = $ercPar";
		}
		$rows = query($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$erc = $row['erc'];
				if($this->_promocao['vendedor'] == 'E'){
					if($this->_promocao['tipoMeta'] == 'E'){
						//Meta por ERC
						$super = $this->_erc[$erc]['super'];
					}else{
						//Meta por Cliente
						$cliente = $erc;
						$erc = $this->getErcOriginal($erc)['erc'];
						$super = $this->_erc[$erc]['super'];
					}
				}else{
					$super = '';
				}
				if($superPar == '' || $superPar == $super){
					$sub = $row['sub'];
					$this->geraMatriz($erc, $cliente);
					
					if(isset($this->_subs[$this->_promo][$sub])){
						if($this->_promocao['vendedor'] == 'E'){
							if($this->_subs[$this->_promo][$sub]['impMeta'] != 'N'){
								if($this->_promocao['tipoMeta'] == 'E'){
									//Meta por ERC
									$this->_vendas[$super][$erc]['meta'.$sub] = $row['valor'];
								}else{
									//Meta por Cliente
									$this->_vendas[$super][$erc][$cliente]['meta'.$sub] = $row['valor'];
								}
							}
							if($this->_promocao['totalMeta'] == 'S'){
								if($this->_promocao['tipoMeta'] == 'E'){
									$this->_vendas[$super][$erc]['totalMeta'] += $row['valor'];
								}else{
									$this->_vendas[$super][$erc]['totalMeta'] += $row['valor'];
								}
							}
						}else{
							if($this->_subs[$this->_promo][$sub]['impMeta'] != 'N'){
								$this->_vendas[$erc]['meta'.$sub] = $row['valor'];
							}
							if($this->_promocao['totalMeta'] == 'S'){
								$this->_vendas[$erc]['totalMeta'] += $row['valor'];
							}
						}
					}
					
				}
			}
		}
	}
	
	private function geraMatriz($erc, $cliente = 0){
		if($this->_promocao['vendedor'] == 'E'){
			$super = $this->_erc[$erc]['super'];
		}else{
			$super = '';
		}
		if($this->_promocao['tipoMeta'] == 'E'){
			//Meta por ERC
			if((!isset($this->_vendas[$super][$erc]) && $this->_promocao['vendedor'] == 'E') || (!isset($this->_vendas[$erc]) && $this->_promocao['vendedor'] != 'E')){
				$temp = array();
				if($this->_promocao['vendedor'] == 'E'){
					$temp['super']	 	= $super;
					$temp['supervisor'] = $this->_super[$super]['nome'];
				}
				switch ($this->_promocao['vendedor']) {
					case 'S':
						$desc = $this->_super[$erc]['nome'];
						break;
					case 'T':
						$desc = $this->_operadores[$erc]['nome'];
						break;
					case 'X':
						$desc = $this->_operadores[$erc]['nome'] ?? 'Operador '.$erc;
						break;
					default:
						$desc = $this->_erc[$erc]['nome'];
						break;
				}
				$temp['erc'] 		= $erc;
				$temp['vendedor'] 	= $desc;
				
				if(count($this->_subs[$this->_promo]) > 0){
					foreach ($this->_subs[$this->_promo] as $i => $sub){
						if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
							//$this->getProdutosCampanhaItem($this->_promo, $i);
							foreach ($this->_itensVendaItens as $produto){
								$temp['venda'.$produto] = 0;
							}
						}elseif($sub['origem'] == 'OL' && $sub['vendaItem'] == 'S'){
							foreach ($this->_integradorasVendaItens as $integradora){
								$temp['venda'.$integradora] = 0;
							}
						}else{
							if($sub['impMeta'] != 'N'){
								$temp['meta'.$i	] = 0;
							}
							$temp['venda'.$i] = 0;
						}
						if($sub['percent_acima'] == 'S'){
							$temp['percent_acima'.$i] 	= 0;
						}
					}
				}
				if($this->_promocao['totalMeta'] == 'S'){
					$temp['totalMeta'] 	= 0;
				}
				if($this->_promocao['totalReal'] == 'S'){
					$temp['totalReal'] 	= 0;
				}
				if($this->_promocao['vendedor'] == 'E'){
					$this->_vendas[$super][$erc] = $temp;
				}else{
					$this->_vendas[$erc] = $temp;
				}
			}
		}elseif($this->_promocao['tipoMeta'] == 'C' || $this->_promocao['tipoMeta'] == 'P'){
			//Meta por Cliente
			//echo "Matriz: $super - $erc - $cliente <br>\n";
			if((!isset($this->_vendas[$super][$erc][$cliente]) && $this->_promocao['vendedor'] == 'E') || (!isset($this->_vendas[$erc][$cliente]) && $this->_promocao['vendedor'] != 'E')){
				$temp = array();
				if($this->_promocao['vendedor'] == 'E'){
					$temp['super']	 	= $super;
					$temp['supervisor'] = $this->_super[$super]['nome'];
				}
				switch ($this->_promocao['vendedor']) {
					case 'S':
						$desc = $this->_super[$erc]['nome'];
						break;
					case 'T':
						$desc = $this->_operadores[$erc]['nome'];
						break;
					default:
						$desc = $this->_erc[$erc]['nome'];
						break;
				}
				$temp['erc'] 		= $erc;
				$temp['vendedor'] 	= $desc;
				
				//Cliente
				$temp['codcli']		= $cliente;
				$temp['cliente'] 	= $this->getNomeCliente($cliente);
				$temp['principal'] 	= $this->getNomeCliente($cliente, 'P');
				
				if(count($this->_subs[$this->_promo]) > 0){
					foreach ($this->_subs[$this->_promo] as $i => $sub){
						if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
							//$this->getProdutosCampanhaItem($this->_promo, $i);
							foreach ($this->_itensVendaItens as $produto){
								$temp['venda'.$produto] = 0;
							}
						}elseif($sub['origem'] == 'OL' && $sub['vendaItem'] == 'S'){
							foreach ($this->_integradorasVendaItens as $integradora){
								$temp['venda'.$integradora] = 0;
							}
						}else{
							if($sub['impMeta'] != 'N'){
								$temp['meta'.$i	] = 0;
							}
							$temp['venda'.$i] = 0;
						}
						if($sub['percent_acima'] == 'S'){
							$temp['percent_acima'.$i] 	= 0;
						}
					}
				}
				if($this->_promocao['totalMeta'] == 'S'){
					$temp['totalMeta'] 	= 0;
				}
				if($this->_promocao['totalReal'] == 'S'){
					$temp['totalReal'] 	= 0;
				}
				if($this->_promocao['vendedor'] == 'E'){
					$this->_vendas[$super][$erc][$cliente] = $temp;
				}else{
					$this->_vendas[$erc][$cliente] = $temp;
				}
				//print_r($temp);
			}
		}
	}
	
	private function geraMatrizPrincipal($super, $erc, $cliente){
		$temp = array();
		$temp['super']	 	= $super;
		$temp['supervisor'] = $this->_super[$super]['nome'];
		switch ($this->_promocao['vendedor']) {
			case 'S':
				$desc = $this->_super[$erc]['nome'];
				break;
			case 'T':
				$desc = $this->_operadores[$erc]['nome'];
				break;
			default:
				$desc = $this->_erc[$erc]['nome'];
				break;
		}
		$temp['erc'] 		= $erc;
		$temp['vendedor'] 	= $desc;
		
		//Cliente
		$temp['codcli']		= $cliente;
		$temp['cliente'] 	= $this->getNomeCliente($cliente);
		$temp['principal'] 	= $this->getNomeCliente($cliente, 'P');
		
		if(count($this->_subs[$this->_promo]) > 0){
			foreach ($this->_subs[$this->_promo] as $i => $sub){
				if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
					//$this->getProdutosCampanhaItem($this->_promo, $i);
					foreach ($this->_itensVendaItens as $produto){
						$temp['venda'.$produto] = 0;
					}
				}elseif($sub['origem'] == 'OL' && $sub['vendaItem'] == 'S'){
					foreach ($this->_integradorasVendaItens as $integradora){
						$temp['venda'.$integradora] = 0;
					}
				}else{
					if($sub['impMeta'] != 'N'){
						$temp['meta'.$i	] = 0;
					}
					$temp['venda'.$i] = 0;
				}
				if($sub['percent_acima'] == 'S'){
					$temp['percent_acima'.$i] 	= 0;
				}
			}
		}
		if($this->_promocao['totalMeta'] == 'S'){
			$temp['totalMeta'] 	= 0;
		}
		if($this->_promocao['totalReal'] == 'S'){
			$temp['totalReal'] 	= 0;
		}
		if($this->_promocao['vendedor'] == 'E'){
			$this->_vendas[$super][$erc][$cliente] = $temp;
		}else{
			$this->_vendas[$erc][$cliente] = $temp;
		}
		//print_r($temp);
	}
	
	private function geraMatrizTodosClientes(){
		$sql= "
				SELECT
				    PCCLIENT.CODUSUR1,
				    PCCLIENT.CODCLI,
				    PCCLIENT.CLIENTE,
				    PCUSUARI.NOME ERC,
				    PCUSUARI.CODSUPERVISOR,
				    PCSUPERV.NOME SUPER
				FROM
				    PCCLIENT,
				    PCUSUARI,
				    PCSUPERV
				WHERE
				    PCCLIENT.DTEXCLUSAO IS NULL
				    AND PCCLIENT.CODUSUR1 = PCUSUARI.CODUSUR (+)
				    AND PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
				 ";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$cliente = $row['CODCLI'];
				$erc = $row['CODUSUR1'];
				$ercNome = $row['ERC'];
				$super = $row['CODSUPERVISOR'];
				$superNome = $row['SUPER'];
				
				if(!isset($this->_erc[$erc]['nome'])){
					$this->_erc[$erc]['nome'] = $ercNome;
				}
				if(!isset($this->_super[$erc]['nome'])){
					$this->_super[$erc]['nome'] = $superNome;
				}
				
				$this->geraMatrizPrincipal($super, $erc, $cliente);
			}
		}
	}
	
	private function getVendas($superPar,$ercPar){
		if($this->_promocao['vendedor'] == 'E'){
			$this->getVendasERC($superPar, $ercPar, false);
		}else{
			$this->getVendasOutros($superPar, $ercPar);
		}
	}
	
	private function getVendasOutros($superPar,$ercPar){
		$dataIni = $this->_promocao['ini'];
		$dataFim = $this->_promocao['fim'];
		$data = datas::getDataDias(-1);
		//echo "$dataIni - $dataFim - $data <br>\n";
		if($dataFim > $data && $data >= $dataIni){
			$dataFim = $data;
		}
		$this->_dataFimShedule = $dataFim;
		if(count($this->_subs[$this->_promo]) > 0){
			foreach ($this->_subs[$this->_promo] as $i => $sub){
				//print_r($sub);
				$param = [];
				
				//Ignorar os ERCs
				if(!empty($this->_promocao['erc_fora'])){
					$param['erc'] = "SELECT CODUSUR FROM pcusuari WHERE CODUSUR NOT IN (".$this->_promocao['erc_fora'].")";
				}
				
				if(!empty($this->_promocao['ped_fora'])){
					$param['pedidoFora'] = $this->_promocao['ped_fora'];
				}
				
				if(!empty($this->_promocao['cli_fora'])){
					$param['cliente'] = "SELECT CODCLI FROM PCCLIENT WHERE CODCLI NOT IN (".$this->_promocao['cli_fora'].")";
				}
				
				if(!empty($sub['prodFora'])){
					$param['produtoFora'] = $sub['prodFora'];
				}else{
					$param['produtoFora'] = '';
				}
				
				if($superPar != ''){
					$param['super'] = $superPar;
				}
				if($ercPar != ''){
					$param['ERC'] = $ercPar;
				}
				if($sub['tipo'] == 'P'){
					$param['produto'] = $this->_codigos[$i];
					$param['depto'] = '1,12,4';
				}elseif($sub['tipo'] == 'F'){
					$param['fornecedor'] = $this->_codigos[$i];
				}elseif($sub['tipo'] == 'M'){
					$param['marca'] = $this->_codigos[$i];
				}elseif($sub['tipo'] == 'D'){
					$param['depto'] = $this->_codigos[$i];
				}
				
				switch ($sub['origem']) {
					case 'NTELE':
						$param['origem'] = 'NT';
						break;
					case 'TELE':
						$param['origem'] = 'T';
						break;
					case 'PE':
						$param['origem'] = 'PE';
						break;
					case 'PDA':
						$param['origem'] = 'PDA';
						break;
					case 'OL':
						$param['origem'] = 'OL';
						break;
					case 'NOL':
						$param['origem'] = 'NOL';
						break;
					case 'W':
						$param['origem'] = 'W';
						break;
					default:
						break;
				}
				
				$campos = array('CODEMITENTEPEDIDO');
				
				if($sub['impReal'] == 'S'){
					if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
						//Abre os produtos em cada coluna
						$this->getProdutosCampanhaItem($this->_promo, $i);
						foreach ($this->_itensVendaItens as $produto){
							$param['produto'] = $produto;
							$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
							$this->processaVendasOutros($produto,$sub,$vendas);
						}
					}elseif($sub['origem'] == 'OL' && $sub['vendaItem'] == 'S'){
						//Venda OL e mostra venda por item
						if(!empty($this->_integradorasVendaItens)){
							$param['integradora'] = implode(',', $this->_integradorasVendaItens);
						}
						$campos = array('INTEGRADORA','CODEMITENTEPEDIDO');
						if($this->_promocao['vendedor'] == 'S'){
							//Venda OL cai toda para o 15, então tem que ver o ERC original do cadastro
							$campos = array('INTEGRADORA','ERCCLI');
							$campos = array('INTEGRADORA','CODCLI');
							
							$campos = array('INTEGRADORA','CODSUPERVISOR');
						}
						//print_r($campos);print_r($param);
						$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
						if(count($vendas) > 0){
							foreach ($vendas as $integradora => $v1){
								$vendasOL = array();
								if($this->_promocao['vendedor'] == 'S'){
									foreach ($v1 as $super => $v2){
										if(!isset($vendasOL[$super])){
											$vendasOL[$super]['venda'] = 0;
											$vendasOL[$super]['quant'] = 0;
										}
										$vendasOL[$super]['venda'] 	+= $v2['venda'];
										$vendasOL[$super]['quant'] 	+= $v2['quant'];
									}
									//echo "Integradora: $integradora \n";
									//print_r($vendasOL);
									$this->processaVendasOutros($integradora,$sub,$vendasOL);
								}else{
									$this->processaVendasOutros($integradora,$sub,$v1);
								}
							}
						}
					}elseif($sub['meta'] == 'N'){
						//Preço Médio
						$campos = array('CODEMITENTEPEDIDO', 'CODCLI');
						$param['bonificacao'] = false;
						$vendas = [];
						$preco_medio = calculaPrecoMedio('CODEMITENTEPEDIDO', $dataIni, $dataFim, $param);
						if(count($preco_medio) > 0){
							foreach ($preco_medio as $operador => $preco){
								// Somente quem tem meta é acompanhado
								if(isset($this->_vendas[$operador])){
									$this->_vendas[$operador]['venda'.$i] = $preco;
								}
							}
						}
					}elseif($sub['meta'] == 'C'){
						//Positivação Cliente
						$param['bonificacao'] = false; // Solicitado pelo comercial em 08/12/22
						$campos = array('CODEMITENTEPEDIDO', 'CODCLI');
						$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
						if(count($vendas) > 0){
							foreach ($vendas as $operador => $v1){
								// Somente quem tem meta é acompanhado
								if(isset($this->_vendas[$operador])){
									$this->_vendas[$operador]['venda'.$i] = count($v1);
								}
							}
						}
						//print_r($vendas);
					}else{
						$trace = false;
						$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, $trace);
						$this->processaVendasOutros($i,$sub,$vendas);
					}
				}
			}
		}
		
		/**
		 * Se for os valores do TELE + o código de ERC dele
		 *
		 */
		if($this->_promocao['vendedor'] == 'X'){
			//echo $this->getListaOperador_ERC()."<br>\n";
			$this->_promocao['ercSemMeta'] = 'S';
			$this->getVendasERC('',$this->getListaOperador_ERC(),false);
			$this->ajustaVendasOperadoresERC();
			//print_r($this->_vendas);
			//die();
		}
	}
	
	private function ajustaVendasOperadoresERC(){
//print_r($this->_vendas);
//print_r($this->_operador_erc);
//print_r($this->_erc_operador);
		foreach ($this->_vendas as $op => $venda){
			if(!isset($this->_operador_erc[$op])){
//echo "Não encontrato $op <br>\n";
				foreach ($venda as $erc => $v1){
					$operador = $this->_erc_operador[$erc];
					if(isset($this->_vendas[$operador])){
						foreach ($v1 as $camp => $v){
							if(isset($this->_vendas[$operador][$camp])){
								$this->_vendas[$operador][$camp] += $v;
							}else{
								$this->_vendas[$operador][$camp] = $v;
							}
						}
					}
				}
				unset($this->_vendas[$op]);
			}
		}
//print_r($this->_vendas);
	}
	
	private function getListaOperador_ERC(){
		$ret = '';
		
		if(count($this->_operador_erc) == 0){
			$this->getOperadores_ERC();
		}
		
		$ret = implode(',', $this->_operador_erc);
		
		return $ret;
	}
	
	private function getOperadores_ERC(){
		$this->_operador_erc = [];
		
		$sql = "SELECT * FROM gf_operador_erc WHERE del <> '*'";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_operador_erc[$row['operador']] = $row['erc'];
				$this->_erc_operador[$row['erc']] = $row['operador'];
			}
		}
	}
	
	private function getVendasERC($superPar,$ercPar, $trace = false){
		$dataIni = $this->_promocao['ini'];
		$dataFim = $this->_promocao['fim'];
		$data = datas::getDataDias(-1);
		//echo "$dataIni - $dataFim - $data <br>\n";
		if($dataFim > $data && $data >= $dataIni){
			$dataFim = $data;
		}
		$this->_dataFimShedule = $dataFim;
		//print_r($this->_subs);
		if(count($this->_subs[$this->_promo]) > 0){
			foreach ($this->_subs[$this->_promo] as $i => $sub){
				$this->trace($trace, "Sub $i");
				$param = array();
				
				//Pedido Até
				if($this->_promocao['pedidoAte'] > 0){
					$param['pedidoAte'] = $this->_promocao['pedidoAte'];
				}
				
				
				
				//Ignorar os ERCs
				if(!empty($this->_promocao['erc_fora'])){
					$param['erc'] = "SELECT CODUSUR FROM pcusuari WHERE CODUSUR NOT IN (".$this->_promocao['erc_fora'].")";
				}
				
				if(!empty($this->_promocao['ped_fora'])){
					$param['pedidoFora'] = $this->_promocao['ped_fora'];
				}
				
				if(!empty($this->_promocao['cli_fora'])){
					$param['cliente'] = "SELECT CODCLI FROM PCCLIENT WHERE CODCLI NOT IN (".$this->_promocao['cli_fora'].")";
				}
				
				if(!empty($sub['prodFora'])){
					$param['produtoFora'] = $sub['prodFora'];
				}else{
					$param['produtoFora'] = '';
				}
				
				if($superPar != ''){
					$param['super'] = $superPar;
				}
				if($ercPar != ''){
					$param['ERC'] = $ercPar;
				}
				if($sub['tipo'] == 'P' || $sub['tipo'] == 'K'){
					$param['produto'] = $this->_codigos[$i];
					$param['depto'] = '1,12';
				}elseif($sub['tipo'] == 'F'){
					$param['fornecedor'] = $this->_codigos[$i];
				}elseif($sub['tipo'] == 'M'){
					$param['marca'] = $this->_codigos[$i];
				}elseif($sub['tipo'] == 'D'){
					$param['depto'] = $this->_codigos[$i];
				}
				
				switch ($sub['origem']) {
					case 'NTELE':
						$param['origem'] = 'NT';
						break;
					case 'TELE':
						$param['origem'] = 'T';
						break;
					case 'PE':
						$param['origem'] = 'PE';
						break;
					case 'PDA':
						$param['origem'] = 'PDA';
						break;
					case 'OL':
						$param['origem'] = 'OL';
						break;
					case 'W':
						$param['origem'] = 'W';
						break;
					default:
						break;
				}
				//Se for a venda de ERC de operadores, tanto faz a origem (na realidade não tele)
				if($this->_promocao['vendedor'] == 'X'){
					$param['origem'] = 'W';
				}
//print_r($sub);
				if($sub['impReal'] == 'S'){
					if($sub['tipo'] == 'P' && $sub['vendaItem'] == 'S'){
						//Abre os produtos em cada coluna
						$this->getProdutosCampanhaItem($this->_promo, $i);
						foreach ($this->_itensVendaItens as $produto){
							$param['produto'] = $produto;
							if($this->_promocao['origCli'] == 'C'){
								$campos = array('ERCCLI');
							}else{
								$campos = array('CODUSUR');
							}
							$param['bonificacao'] = false;
							//$vendas = vendas1464($dataIni, $dataFim, $param, false);
							//print_r($param);print_r($campos);
							$this->trace($trace, "Vendas 7");
							$this->trace($trace, $param);
							$vendasCampo = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
							/**
							 * Se for os valores do TELE + o código de ERC dele
							 *
							 */
							if(count($vendasCampo) > 0){
								$this->processaVendas($produto,$sub,$vendasCampo, $trace);
							}else{
								$this->trace($trace, "Sem vendas a processar 3");
							}
						}
					}elseif($sub['origem'] == 'OL' && $sub['vendaItem'] == 'S'){
						//Venda OL e mostra venda por item
						$param['integradora'] = implode(',', $this->_integradorasVendaItens);
						//Venda OL cai toda para o 15, então tem que ver o ERC original do cadastro
						$campos = array('INTEGRADORA','ERCCLI');
						$this->trace($trace, "Vendas 6");
						$this->trace($trace, $param);
						$vendasCampo = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
						if(count($vendasCampo) > 0){
							foreach ($vendasCampo as $integradora => $v1){
								$vendas = array();
								foreach ($v1 as $erc => $v){
									//$info = $this->getErcOriginal($codcli);
									$temp = array();
									$temp['CODUSUR'] 		= $erc;
									//$temp['CODUSUR'] 		= $info['erc'];
									$temp['VLVENDA_SEMST'] 	= $v['venda'];
									$temp['QTVENDA'] 		= $v['quant'];
									
									$vendas[] = $temp;
								}
								//print_r($vendas);
								if(count($vendas) > 0){
									$this->processaVendas($integradora,$sub,$vendas);
								}else{
									$this->trace($trace, "Sem vendas a processar 2");
								}
							}
						}
					}else{
						//print_r($param);
						$vendas = [];
						if($this->_promocao['tipoMeta'] == 'E'){
							//Meta por ERC
							if($sub['tipo'] == 'K'){
								//Venda por ERC/Super e tipo KIT
								if($this->_promocao['origCli'] == 'C'){
									$campos = array('ERCCLI', 'CODCLI', 'CODPROD');
								}else{
									$campos = array('CODUSUR', 'CODCLI', 'CODPROD');
								}
								$param['bonificacao'] = false;
								
//print_r($param);
//print_r($campos);
								$this->trace($trace, "Vendas - Metas por KIT");
								$this->trace($trace, $param);
								$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
//print_r($vendas);
							}elseif($sub['meta'] == 'M'){
								//Venda por ERC/Super e meta MIX (soma o mix de cada cliente e divide pelo nr. de clientes)
								if($this->_promocao['origCli'] == 'C'){
									$campos = array('ERCCLI', 'CODCLI');
								}else{
									$campos = array('CODUSUR', 'CODCLI');
								}
								$param['bonificacao'] = false;
								
								//print_r($param);
								$this->trace($trace, "Vendas 4");
								$this->trace($trace, $param);
								$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
							}elseif($sub['meta'] == 'N'){
								//Preço Médio
								if($this->_promocao['origCli'] == 'C'){
									$campo = 'ERCCLI';
								}else{
									$campo = 'CODUSUR';
								}
								$param['bonificacao'] = false;
								$vendas = [];
								$preco_medio = calculaPrecoMedio($campo, $dataIni, $dataFim, $param, false);
								if(count($preco_medio) > 0){
									foreach ($preco_medio as $erc => $preco){
										$super = $this->_erc[$erc]['super'];
										// Somente quem tem meta é acompanhado
										if(isset($this->_vendas[$super][$erc])){
											$this->_vendas[$super][$erc]['venda'.$i] = $preco;
										}
									}
								}
							}elseif($sub['meta'] == 'C'){
								//Positivação Cliente
								$param['bonificacao'] = false; // Solicitado pelo comercial em 08/12/22
								if($this->_promocao['origCli'] == 'C'){
									$campos = array('ERCCLI', 'CODCLI');
								}else{
									$campos = array('CODUSUR', 'CODCLI');
								}
								$this->trace($trace, "Vendas 3");
								$this->trace($trace, $param);
								$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
//print_r($sub);
//print_r($campos);
//print_r($param);
//print_r($vendas);
								if(count($vendas) > 0){
									foreach ($vendas as $erc => $v1){
										$super = $this->_erc[$erc]['super'];
										// Somente quem tem meta é acompanhado
										if(isset($this->_vendas[$super][$erc])){
											if($sub['min_positivacao'] == 0){
												$this->_vendas[$super][$erc]['venda'.$i] = count($v1);
											}else{
												$this->_vendas[$super][$erc]['venda'.$i] = 0;
												foreach ($v1 as $v2){
													if($v2['venda'] >= $sub['min_positivacao']){
														$this->_vendas[$super][$erc]['venda'.$i]++;
													}
												}
											}
										}
									}
								}
								$vendas = [];
							}else{
								//$vendas = vendas1464($dataIni, $dataFim, $param, false);
								if($this->_promocao['origCli'] == 'C'){
									$campos = array('ERCCLI');
								}else{
									$campos = array('CODUSUR');
								}
								$this->trace($trace, "Vendas 2");
								$this->trace($trace, $param);
								$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
								//$this->trace($trace, $vendas);
							}
						}elseif($this->_promocao['tipoMeta'] == 'C'){
							//Meta por cliente
							$campos = array('CODSUPERVISOR','ERCCLI','CODCLI');
							$this->trace($trace, "Vendas 1");
							$this->trace($trace, $param);
							$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
						}elseif($this->_promocao['tipoMeta'] == 'P'){
							//Meta por cliente Principal
							$campos = array('CODSUPERVISOR','ERCCLI','CODCLIPRINC');
							//print_r($param);
							$this->trace($trace, "Vendas 0");
							$this->trace($trace, $param);
							$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
							//print_r($vendas);
						}
						//print_r($param);die();
						if(count($vendas) > 0){
							$this->processaVendas($i,$sub,$vendas);
						}else{
							$this->trace($trace, "Sem vendas a processar");
						}
						//print_r($this->_vendas);
					}
				}
			}
		}
	}
	
	private function processaVendasOutros($key, $sub, $vendas){
		if(count($vendas) > 0){
			foreach ($vendas as $cod => $venda){
				if($this->_promocao['vendedor'] == 'S'){
					//Venda OL cai toda para o 15, então tem que ver o ERC original do cadastro
					//$cod = $this->_erc[$cod]['super'];
				}
				// Somente quem tem meta é acompanhado
				if(isset($this->_vendas[$cod])){
					if(!isset($this->_vendas[$cod]['venda'.$key])){
						$this->_vendas[$cod]['venda'.$key] = 0;
					}
					if($sub['meta'] == 'V'){
						//Valor
						$this->_vendas[$cod]['venda'.$key] += $venda['venda'];
						if($this->_promocao['totalReal'] == 'S'){
							$this->_vendas[$cod]['totalReal'] += $venda['venda'];
						}
					}else{
						//Quantidade
						$this->_vendas[$cod]['venda'.$key] += $venda['quant'];
						if($this->_promocao['totalReal'] == 'S'){
							$this->_vendas[$cod]['totalReal'] += $venda['quant'];
						}
					}
					
					//Verifica % realizado
					if($sub['percent_acima'] == 'S'){
						if(isset($this->_vendas[$cod]['meta'.$key]) && $this->_vendas[$cod]['meta'.$key] > 0){
							$this->_vendas[$cod]['percent_acima'.$key] 	= round( ($this->_vendas[$cod]['venda'.$key]/ $this->_vendas[$cod]['meta'.$key]), 2);
							if($this->_vendas[$cod]['percent_acima'.$key] < 100){
								$this->_vendas[$cod]['percent_acima'.$key] = 0;
							}
						}
					}
					
				}
			}
		}
		//print_r($this->_vendas);
	}
	
	private function processaVendas($key, $sub, $vendas, $trace = false){
		$this->trace($trace, "Processando Vendas - $key");
		if(count($vendas) > 0){
			if($this->_promocao['tipoMeta'] == 'E' && $sub['meta'] == 'M'){
				//ERC e MIX
				if($this->_MIXtotalCli == 'total'){
					$this->getMumeroClientes();
				}
				//Venda por ERC/Super e meta MIX (soma o mix de cada cliente e divide pelo nr. de clientes)
				foreach ($vendas as $erc => $v3){
					$super = $this->_erc[$erc]['super'];
					
					if($this->_MIXtotalCli == 'total'){
						$clientes = isset($this->_quantClientesERC[$erc]) ? $this->_quantClientesERC[$erc] : 1;
					}else{
						$clientes = 0;
					}
					// Somente quem tem meta é acompanhado
					if(isset($this->_vendas[$super][$erc])){
						foreach ($v3 as $cliente => $v){
							$this->_vendas[$super][$erc]['venda'.$key] += $v['mix'];
							if($this->_MIXtotalCli != 'total'){
								$clientes++;
							}
						}
						if($clientes > 0){
							//echo "Quant: ".$this->_vendas[$super][$erc]['venda'.$key]." Clientes: ".$clientes." MIX: ".round($this->_vendas[$super][$erc]['venda'.$key] / $clientes, 2)." <br>\n";
							$this->_vendas[$super][$erc]['venda'.$key] = round($this->_vendas[$super][$erc]['venda'.$key] / $clientes, 2);
						}
						if($sub['percent_acima'] == 'S'){
							if(isset($this->_vendas[$super][$erc]['meta'.$key]) && $this->_vendas[$super][$erc]['meta'.$key] > 0){
								$this->_vendas[$super][$erc]['percent_acima'.$key] 	= round( ($this->_vendas[$super][$erc]['venda'.$key]/ $this->_vendas[$super][$erc]['meta'.$key]) * 100, 2);
								if($this->_vendas[$super][$erc]['percent_acima'.$key] < 100){
									$this->_vendas[$super][$erc]['percent_acima'.$key] = 0;
								}
							}
						}
					}
				}
			}elseif($this->_promocao['tipoMeta'] == 'E' && $sub['meta'] == 'N'){
				//Preço Médio
				foreach ($vendas as $erc => $v2){
					$super = $this->_erc[$erc]['super'];
					if(isset($this->_vendas[$super][$erc])){
						// Somente quem tem meta é acompanhado
						
						$quant_cli = count($v2);
						$preço = 0;
						foreach ($v2 as $v3){
							$preço += $v3['quantVend'] > 0 && $v3['venda'] > 0 ? $v3['venda'] / $v3['quantVend'] : 0;
						}
						
						$this->_vendas[$super][$erc]['venda'.$key] = $preço / $quant_cli;
					}
				}
			}elseif($this->_promocao['tipoMeta'] == 'E'){
				//ERC
				foreach ($vendas as $erc => $venda){
					//Utilizada a função vendas1464
					$super = isset($this->_erc[$erc]['super']) ? $this->_erc[$erc]['super'] : '';
					
					//Controle de venda de KIT
					$kits = [];
					
					// Somente quem tem meta é acompanhado (ou se indicar se deve aparecer sem meta)
					if(isset($this->_vendas[$super][$erc]) || $this->_promocao['ercSemMeta'] == 'S'){
						if($sub['meta'] == 'V'){
							//Valor
							//print_r($venda);
							$this->_vendas[$super][$erc]['venda'.$key] = $venda['venda'];
							if($this->_promocao['totalReal'] == 'S'){
								$this->_vendas[$super][$erc]['totalReal'] += $venda['venda'];
							}
						}elseif($sub['meta'] == 'Q'){
							//Quantidade
							if($sub['tipo'] == 'K'){
//KIT------------------------------------------------------------------------------------------------------------------------------
								$quant_kits = $this->verificaKits($this->_codigos[$key], $venda, 'Q');
								$this->_vendas[$super][$erc]['venda'.$key] = $quant_kits;
//print_r($kits);
//print_r($this->_codigos[$key]);
//print_r($venda);
//echo "Verificação de KIT - Quant: $quant_kits <br>\n";
							}else{
								// Demais
								$this->_vendas[$super][$erc]['venda'.$key] = $venda['quantVend'];
								if($this->_promocao['totalReal'] == 'S'){
									$this->_vendas[$super][$erc]['totalReal'] += $venda['quantVend'];
								}
							}
						}elseif($sub['meta'] == 'P'){
							if($sub['tipo'] == 'K'){
//KIT + Positivacao ------------------------------------------------------------------------------------------------------------------------------
								$quant_kits = $this->verificaKits($this->_codigos[$key], $venda, 'P');
								$this->_vendas[$super][$erc]['venda'.$key] = $quant_kits;
							}else{
								//Positivacao
								$this->_vendas[$super][$erc]['venda'.$key] = $venda['positivacao'];
								if($this->_promocao['totalReal'] == 'S'){
									$this->_vendas[$super][$erc]['totalReal'] += $venda['positivacao'];
								}
							}
						}elseif($sub['meta'] == 'M'){
							//Mix (antigo, mix por erc - produtos diferentes que o mesmo vendeu)
							$this->_vendas[$super][$erc]['venda'.$key] = $venda['mix'];
							if($this->_promocao['totalReal'] == 'S'){
								$this->_vendas[$super][$erc]['totalReal'] += $venda['mix'];
							}
						}elseif($sub['meta'] == 'C'){
							//Positivacao - Cliente
							$this->_vendas[$super][$erc]['venda'.$key] = $venda['positivacao'];
						}
						//Verifica % realizado acima da meta
						if($sub['percent_acima'] == 'S'){
							if(isset($this->_vendas[$super][$erc]['meta'.$key]) && $this->_vendas[$super][$erc]['meta'.$key] > 0){
								$this->_vendas[$super][$erc]['percent_acima'.$key] 	= round( ($this->_vendas[$super][$erc]['venda'.$key]/ $this->_vendas[$super][$erc]['meta'.$key]) * 100, 2);
								if($this->_vendas[$super][$erc]['percent_acima'.$key] < 100){
									$this->_vendas[$super][$erc]['percent_acima'.$key] = 0;
								}
							}
						}
						
					}
				}
			}elseif($this->_promocao['tipoMeta'] == 'C' || $this->_promocao['tipoMeta'] == 'P'){
				//Meta por cliente ou Cliente Principal
				foreach ($vendas as $super => $v2){
					foreach ($v2 as $erc => $v3){
						foreach ($v3 as $cliente => $v){
							//Se for por cliente principal mostra todo mundo
							if($this->_promocao['tipoMeta'] == 'P' && !isset($this->_vendas[$super][$erc][$cliente])){
								$this->geraMatrizPrincipal($super, $erc, $cliente);
							}
							//Se for por cliente e deve mostrar mesmo os sem meta
							if($this->_promocao['tipoMeta'] == 'C' && $this->_promocao['ercSemMeta'] == 'S'){
								$this->geraMatrizPrincipal($super, $erc, $cliente);
							}
							// Somente quem tem meta é acompanhado (ou se deve listar sem meta)
							if(isset($this->_vendas[$super][$erc][$cliente]) || $this->_promocao['ercSemMeta'] == 'S'){
								if($sub['meta'] == 'V'){
									//Valor
									$this->_vendas[$super][$erc][$cliente]['venda'.$key] = $v['venda'];
									if($this->_promocao['totalReal'] == 'S'){
										$this->_vendas[$super][$erc][$cliente]['totalReal'] += $v['venda'];
									}
								}elseif($sub['meta'] == 'Q'){
									//Quantidade
									$this->_vendas[$super][$erc][$cliente]['venda'.$key] = $v['quant'];
									if($this->_promocao['totalReal'] == 'S'){
										$this->_vendas[$super][$erc][$cliente]['totalReal'] += $v['quant'];
									}
								}elseif($sub['meta'] == 'P'){
									//Positivacao - Produto
									$this->_vendas[$super][$erc][$cliente]['venda'.$key] = $v['positivacao'];
									if($this->_promocao['totalReal'] == 'S'){
										$this->_vendas[$super][$erc][$cliente]['totalReal'] += $v['positivacao'];
									}
								}elseif($sub['meta'] == 'M'){
									//Mix
									$this->_vendas[$super][$erc][$cliente]['venda'.$key] = $v['mix'];
									if($this->_promocao['totalReal'] == 'S'){
										$this->_vendas[$super][$erc][$cliente]['totalReal'] += $v['mix'];
									}
								}elseif($sub['meta'] == 'P'){
									//Preço Médio
									$this->_vendas[$super][$erc][$cliente]['venda'.$key] = $v['quantVend'] > 0 && $v['venda'] > 0 ? $v['venda'] / $v['quantVend'] : 0;
								}elseif($sub['meta'] == 'P'){
									//Positivacao - Cliente
									$this->_vendas[$super][$erc][$cliente]['venda'.$key] = 1;
								}
								if($sub['percent_acima'] == 'S'){
									if(isset($this->_vendas[$super][$erc]['meta'.$key]) && $this->_vendas[$super][$erc]['meta'.$key] > 0){
										$this->_vendas[$super][$erc]['percent_acima'.$key] 	= round( ($this->_vendas[$super][$erc]['venda'.$key]/ $this->_vendas[$super][$erc]['meta'.$key]) * 100, 2);
										if($this->_vendas[$super][$erc]['percent_acima'.$key] < 100){
											$this->_vendas[$super][$erc]['percent_acima'.$key] = 0;
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
	
	private function getProdutosCampanhaItem($campanha, $sub){
		$this->_itensVendaItens = array();
		$sql = "SELECT * FROM gf_camp_itens WHERE campanha = '$campanha' AND sub = '$sub'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_itensVendaItens[] = $row['valor'];
			}
		}
	}
	
	private function getIntegradorasCampanhaItem($campanha, $sub){
		$this->_itensVendaItens = array();
		$sql = "SELECT * FROM gf_camp_itens WHERE campanha = '$campanha' AND sub = '$sub'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_integradorasVendaItens[] = $row['valor'];
			}
		}
	}
	
	private function getNomeCliente($cliente, $campo = ''){
		$ret = '';
		if(!isset($this->_clientes[$cliente])){
			$sql = "SELECT CLIENTE, CODCLIPRINC FROM PCCLIENT WHERE CODCLI = $cliente";
			$rows = query4($sql);
			if(isset($rows[0][0])){
				$this->_clientes[$cliente]['N'] = str_replace("'", '´', $rows[0][0]);
				$this->_clientes[$cliente]['P'] = $rows[0][1];
			}else{
				$this->_clientes[$cliente]['N'] = '';
				$this->_clientes[$cliente]['P'] = '';
			}
		}
		if($campo == 'P'){
			$ret = $this->_clientes[$cliente]['P'];
		}else{
			$ret = $this->_clientes[$cliente]['N'];
		}
		
		return $ret;
	}
	
	private function getNomeProduto($produto){
		$ret = '';
		$sql = "SELECT DESCRICAO FROM PCPRODUT WHERE CODPROD = $produto";
		$rows = query4($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	private function getNomeIntegradora($integradora){
		$ret = '';
		$sql = "SELECT DESCRICAO FROM PCINTEGRADORA WHERE INTEGRADORA = $integradora";
		$rows = query4($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	/*
	 * Carrega ERCs e Supervisores
	 */
	function getVendedores($inativos = false){
		$vend = getListaEmailGF('rca',$inativos);
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
		
		//Carrega Operadores TLMKT
		$sql = "
			SELECT
			    MATRICULA,
			    NOME,
				EMAIL
			FROM
			    PCEMPR
			WHERE
			    CODPERFILTELEVMED IS NOT NULL
			";
		
		if(!$inativos){
			$sql .= "AND SITUACAO = 'A' AND DTDEMISSAO IS NULL";
		}
		//echo "SQL: $sql<br>\n";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$operador = $row['MATRICULA'];
				$this->_operadores[$operador]['nome'] = $row['NOME'];
				$this->_operadores[$operador]['email'] = $row['EMAIL'];
			}
		}
		//print_r($this->_operadores);
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
	
	private function trace($trace, $msg){
		if($trace){
			if(is_array($msg)){
				echo print_r($msg, true)."<br>\n";
			}else{
				echo $msg."<br>\n";
			}
		}
	}
	
	private function getMumeroClientes(){
		$sql = "SELECT CODUSUR1, COUNT(*) QUANT FROM PCCLIENT WHERE DTEXCLUSAO IS NULL GROUP BY CODUSUR1";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_quantClientesERC[$row['CODUSUR1']] = $row['QUANT'];
			}
		}
	}
	
	/**
	 * 
	 * @param string $produtos_lista - Lista de produtos separados por virgula
	 * @param array $venda - Vendas realizadas pelo ERC
	 * @param string $tipo (Q - Quantidade, P- Positivacao)
	 * 
	 * @return number - quantidade de venda ou positivação
	 */
	private function verificaKits($produtos_lista, $venda, $tipo){
		$ret = 0;
		$produtos = explode(',', $produtos_lista);
		$kits = [];
//print_r($produtos);
//print_r($venda);
		if(count($venda) > 0){
			foreach ($venda as $cliente => $v1){
				foreach ($v1 as $produto => $v){
					$kits[$cliente][$produto] = $v['quant'];
				}
			}
		}
		
		if(count($produtos) == 0 || count($kits) == 0){
			return 0;
		}
		
		foreach ($kits as $venda1){
			$quant_temp = -1;
			foreach ($produtos as $prod){
				$quant = $venda1[$prod] ?? 0;
				
				if(($quant_temp == -1 && $quant >= 0) || $quant < $quant_temp){
					$quant_temp = $quant;
				}
			}
			if($quant_temp >0){
				if($tipo == 'Q'){
					$ret += $quant_temp;
				}elseif($tipo == 'P'){
					$ret++;
				}
			}
		}
		
		
		
		return $ret;
	}
}