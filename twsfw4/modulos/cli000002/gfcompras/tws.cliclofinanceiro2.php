<?php
/*
* Data Criacao: 1 de jun de 2016
* Autor: Thiel
*
* Resumo: Novo relatório de ciclo financeiro definido pelo Márcio
*/

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class cliclofinanceiro2{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';

	// Classe relatorio
	var $_relatorio;
	
	//Dados
	var $_dados;
	
	//Indices (industrias ou marcas)
	var $_indices;
	
	//Valor Atual do Estoque
	var $_estoqueValor;
	
	//Valor das compras no período
	var $_comprasPeriodo;
	
	//Prazo Médio dos estoques
	var $_estoquePrazoMedio;
	
	//Prazo Médio Pagamentos
	var $_pagamentoPrazoMedio;
	
	//Prazo Médio Recebimentos
	var $_recebimentosPrazoMedio;
	
	//Compradores
	var $_compradores;
	
	//Titulos a pagar
	var $_tituloPagar = array();
	
	//Titulos a receber
	var $_tituloReceber = array();
	
	//Vendas
	var $_vendas;
	
	//Fornecedores que não devem ser computados
	var $_fornecedoresFora;
	
	//Fornecedores de Medicamentos
	var $_fornecedoresMedic;
	
	//Lead Time
	var $_lt;
	
	// Envia email para Supervisores/RCA (utilizado nos testes)
	var $_teste;

	var $_inclui_dados_Indicador_Comprador;
	
	function __construct(){
		set_time_limit(0);

		$this->_fornecedoresFora = '16845,15083';
		
		$this->_programa = 'cliclofinanceiro2';
		
		$this->_teste = false;
		
		$this->_inclui_dados_Indicador_Comprador = true;
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Prazo (dias)', 'variavel' => 'PRAZO'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Tipo'		, 'variavel' => 'TIPO'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'I=Industria;M=Marca'));
	}
	
	/*
	 * 
	 */
	
	private function montaColunas($tipo){
		$etiqueta = '';
		switch ($tipo) {
			case 'I':
				$etiqueta = 'Industria';
				break;
			case 'M':
				$etiqueta = 'Marca';
				break;
			default:
				$etiqueta = 'Industria';
				break;
		}
		$this->_relatorio->addColuna(array('campo' => 'cod'			, 'etiqueta' => 'Codigo'	, 'tipo' => 'T', 'width' => 80, 'posicao' =>'E'));
		$this->_relatorio->addColuna(array('campo' => 'indice'		, 'etiqueta' => $etiqueta	, 'tipo' => 'T', 'width' =>300, 'posicao' =>'E'));
		$this->_relatorio->addColuna(array('campo' => 'comprador'	, 'etiqueta' => 'Comprador'	, 'tipo' => 'T', 'width' =>150, 'posicao' =>'E'));

		$this->_relatorio->addColuna(array('campo' => 'estoque'		, 'etiqueta' => 'Prazo<br>Estoque'		, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'recebe'		, 'etiqueta' => 'Prazo<br>Recebimento'	, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'pagamento' 	, 'etiqueta' => 'Prazo<br>Pagamento'	, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'ciclo'		, 'etiqueta' => 'Ciclo'					, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'estoqueVal'	, 'etiqueta' => 'Valor<br>Estoque'		, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'pagamentoVal', 'etiqueta' => 'Valor<br>Pagamento'	, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'comprasVal'  , 'etiqueta' => 'Valor<br>Compras'		, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'vendas'  	, 'etiqueta' => 'Valor<br>Vendas'		, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'recebeVal'   , 'etiqueta' => 'Valor<br>Recebimentos'	, 'tipo' => 'V', 'width' => 80, 'posicao' =>'D'));

		$this->_relatorio->addColuna(array('campo' => 'ltfat'   	, 'etiqueta' => 'Lead Time<br>Pedido x Faturado'	, 'tipo' => 'V', 'width' => 100, 'posicao' =>'D'));
		$this->_relatorio->addColuna(array('campo' => 'ltent'   	, 'etiqueta' => 'Lead Time<br>Faturado x Entrega'	, 'tipo' => 'V', 'width' => 100, 'posicao' =>'D'));

		//19/01/18 - Incluído por solicitaçlão do Márcio
		if($this->_inclui_dados_Indicador_Comprador){
			$this->_relatorio->addColuna(array('campo' => 'vcompra'		, 'etiqueta' => 'Vl.Compras'		, 'tipo' => 'V', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'vavaria'		, 'etiqueta' => 'Vl.Avaria'			, 'tipo' => 'V', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'qitens'		, 'etiqueta' => 'Itens'				, 'tipo' => 'N', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'qfl'			, 'etiqueta' => 'Items FL'			, 'tipo' => 'N', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'qzero'		, 'etiqueta' => 'Itens Zerados'		, 'tipo' => 'N', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'zerovalor'	, 'etiqueta' => 'Valor Zerado'		, 'tipo' => 'V', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'qzerofl'		, 'etiqueta' => 'It.Zer.FL'			, 'tipo' => 'N', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'qcadastro'	, 'etiqueta' => 'Itens Cadastrados'	, 'tipo' => 'N', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'q90'			, 'etiqueta' => 'Itens > 90'		, 'tipo' => 'N', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'v90'			, 'etiqueta' => 'Vl.Item > 90'		, 'tipo' => 'V', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'q10'			, 'etiqueta' => 'Itens < 10'		, 'tipo' => 'N', 'width' =>  80, 'posicao' =>'D'));
			$this->_relatorio->addColuna(array('campo' => 'v10'			, 'etiqueta' => 'Vl.Item < 10'		, 'tipo' => 'V', 'width' =>  80, 'posicao' =>'D'));
		}
	}
			
	function index($prazo = 0){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		if($prazo == 0){
			$prazo = $filtro['PRAZO'];
		}
		$prazo2 = 365;
		//$tipo  = $filtro['TIPO'];
		$tipo  = 'I';
		
		$tit = '';
		if($prazo > 0){
			$tit = datas::dataS2D(datas::getDataDias(-$prazo)).' a '.datas::data_hoje();
		}
		
		$titulo = 'Ciclo Financeiro. Periodo: '.$tit;
		$this->_relatorio->setTitulo($titulo);
		
		if(!$this->_relatorio->getPrimeira() && $prazo > 0){
			$this->_relatorio->setTitulo($titulo.' - '.$prazo.' Dias');
			$this->montaColunas($tipo);
			
			$this->getFornecedoresMedic();
			
			$this->getValorAtualEstoque($prazo, $tipo);
			$this->getValorComprasPeriodo($prazo, $tipo);
			$this->getValorTitulosPagar($prazo, $tipo);
			$this->getVendas($prazo, $tipo);
			$this->getValorTitulosReceber($prazo, $tipo);
			
			$this->getPrazoMedioEstoque($prazo, $tipo);
			$this->getPrazoMedioRecebimento($prazo, $tipo);
			$this->getPrazoMedioPagamento($prazo, $tipo);
			
			$this->getLeadTime($prazo, $tipo);

			$this->ajustaIndice($tipo);
			$this->ajustaDados($tipo);
			
			$this->getDadosIndicadores($prazo2);
			
			$this->ajustaTotais($prazo);
			
			$this->_relatorio->setDados($this->_dados);
			$this->_relatorio->setToExcel(true);
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	function schedule($param){
		global $config;
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		
		$parametros = explode('|', $param);
		$prazo= $parametros[0];
		$prazo2= $parametros[1];
		$emails = str_replace(',', ';', $parametros[2]);
		$tipo  = 'I';
		
		$mes = date('m');
		$ano = date('Y');
		
		$titulo = 'Ciclo Financeiro. '.datas::dataS2D(datas::getDataDias(-$prazo)).' a '.datas::data_hoje().' Dias: '.$prazo;
echo "Titulo: $titulo <br>\n";
		$this->_relatorio->setTitulo($titulo.' - '.$prazo.' Dias');
		log::gravaLog("cliclofinanceiro2", "Monta Colunas");
		$this->montaColunas($tipo);
echo "1<br>\n";
		log::gravaLog("cliclofinanceiro2", "Fornecedores Medicamentos");
		$this->getFornecedoresMedic();
echo "2<br>\n";
		log::gravaLog("cliclofinanceiro2", "Valores Atuais de Estoque");
		$this->getValorAtualEstoque($prazo, $tipo);
echo "3<br>\n";
log::gravaLog("cliclofinanceiro2", "Valor Compras Período");
		$this->getValorComprasPeriodo($prazo, $tipo);
echo "4<br>\n";
log::gravaLog("cliclofinanceiro2", "Valor Títulos a Pagar");
		$this->getValorTitulosPagar($prazo, $tipo);
echo "5<br>\n";
log::gravaLog("cliclofinanceiro2", "Valor Vendas");
		$this->getVendas($prazo, $tipo);
echo "6<br>\n";
		log::gravaLog("cliclofinanceiro2", "Valor Título a Receber");
		$this->getValorTitulosReceber($prazo, $tipo);
echo "7<br>\n";
log::gravaLog("cliclofinanceiro2", "Prazo Médio de Estoque");
		$this->getPrazoMedioEstoque($prazo, $tipo);
echo "8<br>\n";
log::gravaLog("cliclofinanceiro2", "Prazo Médio Recebimento");
		$this->getPrazoMedioRecebimento($prazo, $tipo);
echo "9<br>\n";
log::gravaLog("cliclofinanceiro2", "Prazo Médio Pagamento");
		$this->getPrazoMedioPagamento($prazo, $tipo);
echo "10<br>\n";
log::gravaLog("cliclofinanceiro2", "Lead Time");
		$this->getLeadTime($prazo, $tipo);
echo "11<br>\n";
log::gravaLog("cliclofinanceiro2", "Ajustando Indices");
		$this->ajustaIndice($tipo);
echo "12<br>\n";
log::gravaLog("cliclofinanceiro2", "Ajustando Dados");
		$this->ajustaDados($tipo);
echo "13<br>\n";
log::gravaLog("cliclofinanceiro2", "Resgatando Indicadores");
		$this->getDadosIndicadores($prazo2);
echo "14<br>\n";
log::gravaLog("cliclofinanceiro2", "Ajustando Totais");
		$this->ajustaTotais($prazo);
echo "15<br>\n";
		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true,'CicloFinanceiro');
		$this->_relatorio->setDados($this->_dados);
		
		log::gravaLog("cliclofinanceiro2", "Copiando Arquivo");
		$copia = $this->_relatorio->copiaExcel($config['temp'].'ciclo_financeiro/', 'cliclofinanceiro_'.date('Ymd').'_'.$prazo.'_dias.xlsx');
		if($copia === true){
			echo "Copiado com sucesso!<br>\n";
			log::gravaLog("cliclofinanceiro2", "Copia realizada com sucesso");
		}else{
			$errors= error_get_last();
			echo "COPY ERROR: ".$errors['type'];
			echo "<br />\n".$errors['message'];
			echo "Erro ao copiar! ".$config['temp'].'ciclo_financeiro/'.'cliclofinanceiro_'.date('Ymd').'_'.$prazo.'_dias.xlsx'."<br>\n";
			log::gravaLog("cliclofinanceiro2", "Erro ao copiar! ".$config['temp'].'ciclo_financeiro/'.'cliclofinanceiro_'.date('Ymd').'_'.$prazo.'_dias.xlsx'."<br>\n");
		}
		
		log::gravaLog("cliclofinanceiro2", "Verificando Schedule");
		if(verificaExecucaoSchedule($this->_programa.'_'.$prazo,$ano.$mes) || $prazo== 0){
			log::gravaLog("cliclofinanceiro2", "Já foi executado no mês");
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			return;
		}elseif(!$this->_teste){
			log::gravaLog("cliclofinanceiro2", "Gravando Execução Schedule");
			gravaExecucaoSchedule($this->_programa.'_'.$prazo,$ano.$mes);
			echo "Gravada a execução<br>\n";
		}
		
		if(!$this->_teste){
			$this->_relatorio->enviaEmail($emails,$titulo);
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			log::gravaLog("cliclofinanceiro2", "Enviado email: ".$emails);
		}else{
			//$this->_relatorio->enviaEmail('thiel@thielws.com.br',$titulo);
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			log::gravaLog("cliclofinanceiro2", "Enviado email TESTE");
		}
echo "16<br>\n";
	}
	
	/*
	 * Utilizado para enviar dados para o relatório Indicador Industria
	 */
	function autoExec2(){
		$prazo = 90;
		$tipo  = 'I';
		$this->_inclui_dados_Indicador_Comprador = false;
		
		$this->montaColunas($tipo);
			
		$this->getFornecedoresMedic();
			
		$this->getValorAtualEstoque($prazo, $tipo);
		$this->getValorComprasPeriodo($prazo, $tipo);
		$this->getValorTitulosPagar($prazo, $tipo);
		$this->getVendas($prazo, $tipo);
		$this->getValorTitulosReceber($prazo, $tipo);
		
		$this->getPrazoMedioEstoque($prazo, $tipo);
		$this->getPrazoMedioRecebimento($prazo, $tipo);
		$this->getPrazoMedioPagamento($prazo, $tipo);
		
		$this->getLeadTime($prazo, $tipo);
		
		$this->ajustaIndice($tipo);
		$this->ajustaDados($tipo);
		
		$this->ajustaTotais($prazo);
		
		return $this->_dados;
	}
	
	private function ajustaTotais($prazo){
//print_r($this->_dados);
		$campos = $this->_relatorio->getCampos();
		$temp = array();
		$zero = array('estoqueVal','pagamentoVal','comprasVal','vendas','recebeVal','vcompra');
		foreach ($campos as $campo){
			$temp[$campo] = '';
		}
		$zero = array('estoqueVal','pagamentoVal','comprasVal','vendas','recebeVal','vcompra');
		foreach ($zero as $campo){
			$temp[$campo] = 0;
		}
		foreach ($this->_dados as $dados){
			$temp['estoqueVal'] 	+= $dados['estoqueVal'];
			$temp['pagamentoVal'] 	+= $dados['pagamentoVal'];
			$temp['comprasVal'] 	+= $dados['comprasVal'];
			$temp['vendas']			+= $dados['vendas'];
			$temp['recebeVal'] 		+= $dados['recebeVal'];
			$temp['vcompra'] 		+= isset($dados['vcompra']) ? $dados['vcompra'] : 0;
			
			if($prazo <> 0){
				$temp['estoque'] 	= $temp['estoqueVal'] / ($temp['comprasVal'] / $prazo);
				$temp['recebe'] 	= $temp['vendas'] <> 0 ? $temp['recebeVal'] / ($temp['vendas'] / $prazo) : $temp['recebeVal'];
				$temp['pagamento'] 	= $temp['pagamentoVal'] / ($temp['comprasVal'] / $prazo);
			}else{
				$temp['estoque'] 	= $temp['estoqueVal'];
				$temp['recebe'] 	= $temp['recebeVal'];
				$temp['pagamento'] 	= $temp['pagamentoVal'];
			}
		}
		
		$this->_dados[] = $temp;
	}
	
	private function getDadosIndicadores($prazo){
		if($this->_inclui_dados_Indicador_Comprador){
			$campos = array('vcompra','vavaria','qitens','qfl','qzero','zerovalor','qzerofl','qcadastro','q90','v90','q10','v10');
			//$dataIni = datas::getDataDias(-$prazo);
			$dataIni = datas::getDataDias(-$prazo);
			$dataFim = datas::getDataDias();
			$obj = CreateObject('gfcompras.indicadorComprador');
			$obj->autoExec2($dataIni, $dataFim, 'A');
			
			$dadosOrig = $obj->_dados;
			$dados = array();
			foreach ($dadosOrig as $comprador => $dado){
				foreach ($dado as $fornec => $d){
					$dados[$fornec] = $d;
				}
			}
			
			foreach ($this->_dados as $i => $orig){
				$fornec = $orig['cod'];
				if(isset($dados[$fornec])){
					foreach ($campos as $campo){
						$this->_dados[$i][$campo] = $dados[$fornec][$campo];
					}
				}
			}
		}
	}
	
	private function ajustaIndice($tipo){
		foreach ($this->_indices as $indice => $desc){
			if($tipo == 'I'){
				$this->_indices[$indice] = $this->getFornecedor($indice);
			}
		}
	}
	
	private function ajustaDados($tipo){
		//asort($this->_indices);
		//foreach ($this->_indices as $indice => $desc){
		foreach ($this->_fornecedoresMedic as $fornecedor){
			if(isset($this->_indices[$fornecedor])){
				$indice = $fornecedor;
				$desc = $this->_indices[$fornecedor];
				$temp = array();
				$temp['cod'			] = $indice;
				$temp['indice'		] = $desc;
				$temp['comprador'	] = isset($this->_compradores[$indice]) ? $this->_compradores[$indice] : $this->getComprador($tipo, $indice);
				$temp['estoque'		] = isset($this->_estoquePrazoMedio[$indice]) ? $this->_estoquePrazoMedio[$indice] : 0;
				$temp['recebe'		] = isset($this->_recebimentosPrazoMedio[$indice]) ? $this->_recebimentosPrazoMedio[$indice] : 0;
				$temp['pagamento' 	] = isset($this->_pagamentoPrazoMedio[$indice]) ? $this->_pagamentoPrazoMedio[$indice] : 0;
				$temp['ciclo'		] = $temp['estoque'] + $temp['recebe'] - $temp['pagamento'];
				
				$temp['estoqueVal'	] = isset($this->_estoqueValor[$indice]) ? $this->_estoqueValor[$indice] : 0;
				$temp['pagamentoVal'] = isset($this->_tituloPagar[$indice]) ? $this->_tituloPagar[$indice] : 0;
				$temp['comprasVal']	  = isset($this->_comprasPeriodo[$indice]) ? $this->_comprasPeriodo[$indice] : 0;
				$temp['vendas'  	] = isset($this->_vendas[$indice]) ? $this->_vendas[$indice] : 0;
				$temp['recebeVal'   ] = isset($this->_tituloReceber[$indice]) ? $this->_tituloReceber[$indice] : 0;
				
				$temp['ltfat'		] = isset($this->_lt[$indice]) ? $this->_lt[$indice]['ltp'] : 0;
				$temp['ltent' 		] = isset($this->_lt[$indice]) ? $this->_lt[$indice]['lte'] : 0;
				
				$valor = $temp['estoque'] + $temp['pagamento'] + $temp['recebe'] + $temp['vendas'];
				
				if($valor > 0){
					$this->_dados[] = $temp;
				}
			}
		}
//print_r($this->_dados);
	}
	
	private function getPrazoMedioEstoque($prazo, $tipo){
		foreach ($this->_estoqueValor as $indice => $estoque){
			if(isset($this->_comprasPeriodo[$indice]) && $this->_comprasPeriodo[$indice] > 0){
				$this->_estoquePrazoMedio[$indice] = round($estoque / ($this->_comprasPeriodo[$indice] / $prazo),2);
			}else{
				$this->_estoquePrazoMedio[$indice] = 0;
			}
		}
	}
	
	private function getPrazoMedioPagamento($prazo, $tipo){
		foreach ($this->_tituloPagar as $indice => $titulos){
			if(isset($this->_comprasPeriodo[$indice]) && $this->_comprasPeriodo[$indice] > 0){
				$this->_pagamentoPrazoMedio[$indice] = round($titulos / ($this->_comprasPeriodo[$indice] / $prazo),2);
			}else{
				$this->_pagamentoPrazoMedio[$indice] = 0;
			}
		}
	}
	
	private function getPrazoMedioRecebimento($prazo, $tipo){
		foreach ($this->_tituloReceber as $indice => $titulos){
			if(isset($this->_vendas[$indice]) && $this->_vendas[$indice] > 0){
				$this->_recebimentosPrazoMedio[$indice] = round($titulos / ($this->_vendas[$indice] / $prazo),2);
			}else{
				$this->_recebimentosPrazoMedio[$indice] = 0;
			}
		}
		
	}
	
	private function getVendas($prazo, $tipo){
		$diaIni = datas::getDataDias(-$prazo);
		$diaFim = date('Ymd');
		$param = array();
		$param['depto'] = '1,12';
		$campos = 'codfornec';
		
		$vendas = vendas1464Campo($campos, $diaIni, $diaFim, $param);
		if(count($vendas) > 0){
			foreach ($vendas as $indice => $venda){
				$this->_vendas[$indice] = $venda['venda'];
				if(!isset($this->_indices[$indice])){
					$this->_indices[$indice] = '';
				}
			}
		}
	}
	
	private function getValorTitulosReceber($prazo, $tipo){
		/*
		 * Pega o valor do titulo aberto, verifica na nota o valor gasto em um fornecedor
		 * e o valor total da nota, calcula um percentual e multiplica pelo valor
		 */
		$sql = "
				select 
				    pcprest.numtransvenda,
				    pcprest.valor,
				    VENDAFORNEC.codfornec,
				    VENDAFORNEC.VLVENDA,
				    VENDATOTAL.VLTOTAL
				from 
				    pcprest,
				    (
				        select 
				        pcmov.codfornec,
				        numtransvenda,
				        SUM((PCMOV.punit - pcmov.st) * pcmov.qt) VLVENDA
				    from 
				        pcmov
				    where 
				        pcmov.numtransvenda IN (select numtransvenda from pcprest where dtpag is null and NVL(PCPREST.NUMTRANSVENDAST,0) = 0)
				        and pcmov.dtcancel is NULL
				    group by
				        pcmov.codfornec,
				        numtransvenda
				    ) VENDAFORNEC,
				    (
				        select 
				        numtransvenda,
				        SUM((PCMOV.punit - pcmov.st) * pcmov.qt) VLTOTAL
				    from 
				        pcmov
				    where 
				        pcmov.numtransvenda IN (select numtransvenda from pcprest where dtpag is null and NVL(PCPREST.NUMTRANSVENDAST,0) = 0)
				        and pcmov.dtcancel is NULL
				    group by
				        numtransvenda
				    ) VENDATOTAL
				    
				where 
				    dtpag is null
				    and NVL(PCPREST.NUMTRANSVENDAST,0) = 0
				    and PCPREST.NUMTRANSVENDA = VENDAFORNEC.NUMTRANSVENDA
				    and PCPREST.NUMTRANSVENDA = VENDATOTAL.NUMTRANSVENDA
    
				";
		$rows = query4($sql);
//echo "Quantidade de titulos a receber: ".count($rows)."<br>\n";
		if(count($rows) > 0){
			foreach ($rows as $row){
				$valor = $row[1];
				$indice = $row[2];
				if($row[4] <> 0){
					$perc = $row[3] / $row[4];
				}else{
					$indice = 0;
				}
				
				if($indice > 0){
					if(!isset($this->_tituloReceber[$indice])){
						$this->_tituloReceber[$indice] = 0;
					}
					
					$this->_tituloReceber[$indice] += $valor * $perc;
					if(!isset($this->_indices[$indice])){
						$this->_indices[$indice] = '';
					}
				}
			}
		}
//print_r($this->_tituloReceber);
		return;
	}
	
	private function getValorTitulosPagar($prazo, $tipo){
		$sql = "
			select 
			    codfornec,
			    SUM(valor) valor
			from
			    PCLANC
			where 
			    dtpagto is null
			    and numtransent IN (select distinct numtransent from PCNFENT where CODFILIAL=1 and TIPODESCARGA IN ('1','5','I') AND dtcancel is null AND CODCONT = (SELECT CODCONTFOR FROM PCCONSUM) AND coddevol is NULL AND especie = 'NF')
			    and codfornec NOT IN (".$this->_fornecedoresFora.")
			group by
			    codfornec
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_tituloPagar[$row[0]] = $row[1];
				if(!isset($this->_indices[$row[0]])){
					$this->_indices[$row[0]] = '';
				}
			}
		}
	}
	
	private function getLeadTime($prazo, $tipo){
		$temp = array();
		$sql = "
				SELECT 
				    PCMOV.NUMPED,
				    PCNFENT.NUMNOTA,
				    PCNFENT.CODFORNEC,
				    PCNFENT.DTEMISSAO EMISSAO,
				    PCNFENT.DTENT ENTRADA,
				    PCPEDIDO.DTEMISSAO PEDIDO,
				    (PCNFENT.DTEMISSAO - PCPEDIDO.DTEMISSAO) LTP,
				    (PCNFENT.DTENT - PCNFENT.DTEMISSAO) LTE
				FROM 
				    pcmov,
                   	PCNFENT, 
                   	pcpedido,
                   	PCPRODUT, 
                    PCDEPTO, 
                    PCEMPR, 
                    PCFORNEC
              	WHERE PCMOV.NUMTRANSENT = PCNFENT.NUMTRANSENT
                    and pcmov.numped in (select numped from pcpedido where dtemissao >= SYSDATE - $prazo)
                    AND PCMOV.numped = PCPEDIDO.numped (+)
                    AND PCFORNEC.CODFORNEC = PCNFENT.CODFORNEC
                    and PCNFENT.CODFORNEC NOT IN (0)
                    AND PCFORNEC.CODCOMPRADOR = PCEMPR.MATRICULA(+)
                    AND (PCMOV.CODPROD = PCPRODUT.CODPROD)
                    AND (PCPRODUT.CODEPTO = PCDEPTO.CODEPTO)
                    AND PCMOV.DTCANCEL IS NULL
                    AND PCNFENT.CODCONT = (SELECT CODCONTFOR FROM PCCONSUM)
                    AND PCNFENT.TIPODESCARGA IN ('1','5','I')
                    AND NVL(PCMOV.CODOPER,'X') in ('E','EB')
                    AND  PCNFENT.CODFILIAL= 1
                    AND PCDEPTO.TIPOMERC NOT IN ('CI','IM')
				group by 
					pcmov.numped,
					pcnfent.numnota,
					PCNFENT.codfornec,
					PCNFENT.dtemissao ,
					PCNFENT.dtent ,
					pcpedido.dtemissao 
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$t = array();
				$fornecedor = $row['CODFORNEC'];
				$t['LTP'] = $row['LTP'];
				$t['LTE'] = $row['LTE'];
				
				$temp[$fornecedor][] = $t;
			}
		}
		if(count($temp) > 0){
			foreach ($temp as $ind => $t){
				$quant = count($t);
				$ltp = 0;
				$lte = 0;
				foreach ($t as $v){
					$ltp += $v['LTP'];
					$lte += $v['LTE'];
				}
				$this->_lt[$ind]['ltp'] = $ltp / $quant;
				$this->_lt[$ind]['lte'] = $lte / $quant;
			}
		}
	}
	
	private function getValorComprasPeriodo($prazo, $tipo){
		$indice = '';
		switch ($tipo) {
			case 'I':
				$indice= 'CODFORNEC';
				break;
			case 'M':
				$indice= 'codmarca';
				break;
			default:
				$indice= 'CODFORNEC';
				break;
		}
		
		$sql = "SELECT  
				    $indice INDICE,
				    COMPRADOR,
				    SUM(VALOR) VALOR
				FROM
				    (
				    SELECT 
				        PCFORNEC.CODFORNEC,
				        pcprodut.codmarca,
				        PCFORNEC.codcomprador,
				        pcempr.nome_guerra COMPRADOR,
				        SUM(NVL(PCMOV.QT, 0) * NVL(PCMOV.PUNIT, 0)) VALOR
				    FROM 
				        PCMOV, 
				        PCNFENT, 
				        PCPRODUT, 
				        PCDEPTO, 
				        PCEMPR, 
				        PCFORNEC
				    WHERE PCMOV.NUMTRANSENT = PCNFENT.NUMTRANSENT
				        AND PCFORNEC.CODFORNEC = PCNFENT.CODFORNEC
						and PCNFENT.CODFORNEC NOT IN (".$this->_fornecedoresFora.")
				        AND PCFORNEC.CODCOMPRADOR = PCEMPR.MATRICULA(+)
				        AND PCNFENT.DTENT >= SYSDATE - $prazo
				        AND (PCMOV.CODPROD = PCPRODUT.CODPROD)
				        AND (PCPRODUT.CODEPTO = PCDEPTO.CODEPTO)
				        AND PCMOV.DTCANCEL IS NULL
				        AND PCNFENT.CODCONT = (SELECT CODCONTFOR FROM PCCONSUM)
				        AND PCNFENT.TIPODESCARGA IN ('1','5','I')
				        AND NVL(PCMOV.CODOPER,'X') in ('E','EB')
				        AND  PCNFENT.CODFILIAL= 1
				        AND PCDEPTO.TIPOMERC NOT IN ('CI','IM')
				    GROUP BY 
				        PCFORNEC.CODFORNEC, 
				        pcprodut.codmarca,
				        codcomprador,
				        pcempr.nome_guerra
				    )
				GROUP BY
				    $indice,
				    COMPRADOR";
//echo "$sql \n";
	    $rows = query4($sql);
	    if(count($rows) > 0){
	    	foreach ($rows as $row){
	    		$this->_comprasPeriodo[$row[0]] = $row[2];
	    		$this->_compradores[$row[0]] = $row[1];
	    		if(!isset($this->_indices[$row[0]])){
	    			$this->_indices[$row[0]] = '';
	    		}
	    	}
	    }
//print_r($this->_comprasPeriodo);
//print_r($this->_indices);
//print_r($this->_compradores);
	}
		
	
	private function getValorAtualEstoque($prazo, $tipo){
		//Valor Atual do Estoque
		$indice = '';
		$tabela = '';
		switch ($tipo) {
			case 'I':
				$indice= 'pcprodut.codfornec';
				break;
			case 'M':
				$indice= 'pcprodut.codmarca';
				break;
			default:
				$indice= 'pcprodut.codfornec';
				break;
		}
		$sql = "
					SELECT
					    indice,
					    SUM(quant * custo) estoque
					FROM 
					     (           select
					                    $indice indice,
					                   -- (NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0)) quant, (a 105 não diminui est. indenizavel)
					                    (NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0)) quant,
										pcest.custoreal custo
					                FROM
					                    pcest,
					                    pcprodut
					                where 
					                    pcest.codfilial = 1
					                    and pcest.codprod = pcprodut.codprod
					    )
					WHERE
					    indice is not NULL
					    and quant > 0 
					    and custo > 0
					GROUP BY
					    indice
					having 
						sum(NVL(quant,0)) > 0 
				";
//echo "SQL Estoque: $sql \n";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_estoqueValor[$row[0]] = $row[1];
				if(!isset($this->_indices[$row[0]])){
					$this->_indices[$row[0]] = '';
				}
			}
		}
//print_r($this->_estoqueValor);
//print_r($this->_indices);
	}
	
	private function getFornecedor($fornec){
		$ret = '';
		$sql = "select fornecedor from pcfornec where codfornec = $fornec";
		$rows = query4($sql);
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	private function getComprador($tipo, $indice){
		$ret = '';
		if($tipo == 'I'){
			$sql = "select nome_guerra from pcfornec, PCEMPR  where  PCFORNEC.codfornec = $indice AND PCFORNEC.CODCOMPRADOR = PCEMPR.MATRICULA(+)";
		}
		$rows = query4($sql);
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	private function getFornecedoresMedic(){
		$sql = "select codfornec from pcfornec where codfornec in (select codfornec from pcprodut where pcprodut.dtexclusao is null and codepto in (1,12)) order by CGC";
		$rows = query4($sql);
		if(count($rows) >0 ){
			foreach ($rows as $row){
				$this->_fornecedoresMedic[] = $row[0];
			}
		}
	}
}