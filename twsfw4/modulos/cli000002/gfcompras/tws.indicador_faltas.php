<?php
/*
 * Data Criacao: 22/01/2018
 * Autor: Thiel
 *
 * Resumo: Gera planilha de indicadores de Faltas
 */

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class indicador_faltas{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	// Classe relatorio
	var $_relatorio;
	
	//Classe Excel
	var $_excel;
	
	//Dados
	var $_dados;
	
	//Filtro
	var $_filtro;
	
	//Cabecalhos
	private $_cab;
	
	//Campos
	private $_campos;
	
	//Tipos
	private $_tipos;
	
	//Dias com vendas
	private $_dias;
	
	//Link excel
	var $_link;
	
	//Codigo da promocao 35 dias
	var $_promo35;
	
	//Codigo da promocao FV
	var $_promoFV;
	
	function __construct(){
		global $config;
		
		set_time_limit(0);
		
		$this->_programa = 'indicador_faltas';
		
		$this->_filtro = new formFiltro01($this->_programa,800);
		
		$this->_promo35 = 21961;
		$this->_promoFV = 9797;
		
		$this->_giroDia = [];
		
		$arq = $config['tempPach'].'indicador_faltas.xlsx';
		$this->_link = $config['tempURL'].'indicador_faltas.xlsx';
		$this->_excel = new excel02($arq);
		
		
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Mes Base'		, 'variavel' => 'MES', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '01=01;02=02;03=03;04=04;05=05;06=06;07=07;08=08;09=09;10=10;11=11;12=12'));
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Ano Base'		, 'variavel' => 'ANO', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '2017=2017;2018=2018;2019=2019;2020=2020'));
	}
	
	
	function index(){
	    ini_set('display_errors',1);
	    ini_set('display_startup_erros',1);
	    error_reporting(E_ALL);
	    
	    
	    
		$ret = '';
		$botao = [];
		$texto = '';
		
		$filtro = $this->_filtro->getFiltro();
		
		$mes = isset($filtro['MES']) ? $filtro['MES'] : '';
		$ano = isset($filtro['ANO']) ? $filtro['ANO'] : '';
		
		if(!$this->_filtro->getPrimeira()){
			$this->preparaPastas($ano,$mes);
			$this->ajustaDataPrimeiraCompra();
			
			$dadosIndicadores = $this->getDadosIndicadores($ano, $mes);
			$dadosFornecedores = $this->getDadosFornecedores();
			$dadosProdutos = $this->getDadosProdutos($ano, $mes);
			
			$dadosIncFornec = $this->processaDadosFornec($dadosProdutos, $dadosFornecedores);
			$dadosIncComprador = $this->processaDadosComprador($dadosProdutos);
			
			$this->_excel->addWorksheet(0, 'RESUMO - Fornecedor');
			$this->_excel->setDados($this->_cab[0], $dadosIncFornec, $this->_campos[0],$this->_tipos[0]);
			$this->_excel->addWorksheet(1, 'RESUMO - Comprador');
			$this->_excel->setDados($this->_cab[1], $dadosIncComprador,$this->_campos[1],$this->_tipos[1]);
			$this->_excel->addWorksheet(2, 'Produtos');
			$this->_excel->setDados($this->_cab[2], $dadosProdutos,$this->_campos[2],$this->_tipos[2]);
			$this->_excel->addWorksheet(3, 'Indicadores');
			$this->_excel->setDados($this->_cab[3], $dadosIndicadores,$this->_campos[3],$this->_tipos[3]);
			$this->_excel->addWorksheet(4, 'Fornecedores e Compradores');
			$this->_excel->setDados($this->_cab[4], $dadosFornecedores,$this->_campos[4],$this->_tipos[4]);
			$this->_excel->setWSAtiva(0);
			
			$this->_excel->grava();
			
			$botao = [];
			$botao["onclick"]= 'window.open(\''.$this->_link.'\')';
			$botao["texto"]	= "Planilha";
			$botao["id"] = "bt_excel";
			
			$texto = "Informações geradas com sucesso. Por favor efetue o download da planilha.";
			
		}
		
		/*
		$ret .= tituloPrincipal("Indicadores de Faltas", "grupo.gif", $botao);
		$ret .= $this->_filtro;
		*/
		$param = [];
		if(count($botao) > 0){
		  $param['botoesTitulo'][] = $botao;
		}
		$param['titulo'] = "Indicadores de Faltas";
		$param['conteudo'] = $this->_filtro . '';
		
		$ret .= addCard($param);
		$ret .= "<br><br><center><b>$texto</b></center>";
		
		return $ret . '';
	}
	
	function schedule($param){
		
	}
	
	private function preparaPastas($ano, $mes){
		$this->getDiasPeriodo($ano, $mes);
		
		$this->_cab[0][] = 'Cod';                 $this->_tipos[0][] = 'T';				$this->_campos[0][] = 'cod';
		$this->_cab[0][] = 'Fornecedor';          $this->_tipos[0][] = 'T'; 			$this->_campos[0][] = 'fornecedor';
		$this->_cab[0][] = 'Cod';                 $this->_tipos[0][] = 'T'; 			$this->_campos[0][] = 'codcomp';
		$this->_cab[0][] = 'Comprador';           $this->_tipos[0][] = 'T'; 			$this->_campos[0][] = 'comprador';
		$this->_cab[0][] = 'Venda Perdida R$';    $this->_tipos[0][] = 'V'; 			$this->_campos[0][] = 'vendaP';
		$this->_cab[0][] = '% Partic. R$';        $this->_tipos[0][] = 'V'; 			$this->_campos[0][] = 'PvendaP';
		$this->_cab[0][] = 'Venda Perdida QTD';   $this->_tipos[0][] = 'N'; 			$this->_campos[0][] = 'QvendaP';
		$this->_cab[0][] = '% Partic. QTD';       $this->_tipos[0][] = 'V'; 			$this->_campos[0][] = 'PQvendaP';
		
		$this->_cab[0][] = '% Participação: QTD Itens em Falta sob. QTD itens cadastrados'; $this->_tipos[0][] = 'V'; $this->_campos[0][] = 'Pqifsqic';
		$this->_cab[0][] = '% Participação: Valor da Falta sob. Valor da Venda';       		$this->_tipos[0][] = 'V'; $this->_campos[0][] = 'Pqvfsvv';
		
		$this->_cab[0][] = 'Itens Faltantes';     $this->_tipos[0][] = 'N'; 			$this->_campos[0][] = 'if';
		$this->_cab[0][] = '% Partic IF';         $this->_tipos[0][] = 'V'; 			$this->_campos[0][] = 'Pif';
		$this->_cab[0][] = 'Itens Cadastados';    $this->_tipos[0][] = 'N'; 			$this->_campos[0][] = 'ic';
		$this->_cab[0][] = '% Partic IC';         $this->_tipos[0][] = 'V'; 			$this->_campos[0][] = 'Pic';
		$this->_cab[0][] = 'Venda R$';            $this->_tipos[0][] = 'V'; 			$this->_campos[0][] = 'venda';
		$this->_cab[0][] = '% Partic. Venda';     $this->_tipos[0][] = 'V'; 			$this->_campos[0][] = 'Pvenda';

		$this->_cab[1][] = 'Cod';                 $this->_tipos[1][] = 'T'; 			$this->_campos[1][] = 'cod';
		$this->_cab[1][] = 'Comprador';           $this->_tipos[1][] = 'T'; 			$this->_campos[1][] = 'comprador';
		$this->_cab[1][] = 'Venda Perdida R$';    $this->_tipos[1][] = 'V'; 			$this->_campos[1][] = 'vendaP';
		$this->_cab[1][] = '% Partic. R$';        $this->_tipos[1][] = 'V'; 			$this->_campos[1][] = 'PvendaP';
		$this->_cab[1][] = 'Venda Perdida QTD';   $this->_tipos[1][] = 'N'; 			$this->_campos[1][] = 'QvendaP';
		$this->_cab[1][] = '% Partic. QTD';       $this->_tipos[1][] = 'V'; 			$this->_campos[1][] = 'PQvendaP';
		
		$this->_cab[1][] = '% Participação: QTD Itens em Falta sob. QTD itens cadastrados'; $this->_tipos[1][] = 'V'; $this->_campos[1][] = 'Pqifsqic';
		$this->_cab[1][] = '% Participação: Valor da Falta sob. Valor da Venda';       		$this->_tipos[1][] = 'V'; $this->_campos[1][] = 'Pqvfsvv';
		
		$this->_cab[1][] = 'Itens Faltantes';     $this->_tipos[1][] = 'N'; 			$this->_campos[1][] = 'if';
		$this->_cab[1][] = '% Partic IF';         $this->_tipos[1][] = 'V'; 			$this->_campos[1][] = 'Pif';
		$this->_cab[1][] = 'Itens Cadastados';    $this->_tipos[1][] = 'N'; 			$this->_campos[1][] = 'ic';
		$this->_cab[1][] = '% Partic IC';         $this->_tipos[1][] = 'V'; 			$this->_campos[1][] = 'Pic';
		$this->_cab[1][] = 'Venda R$';            $this->_tipos[1][] = 'V'; 			$this->_campos[1][] = 'venda';
		$this->_cab[1][] = '% Partic. Venda';     $this->_tipos[1][] = 'V'; 			$this->_campos[1][] = 'Pvenda';
		
		$this->_cab[2][] = 'Cod';                 $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'cod';
		$this->_cab[2][] = 'Descricao';           $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'descricao';
		$this->_cab[2][] = 'Cod.Fornec';          $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'fornec';
		$this->_cab[2][] = 'Fornecedor';          $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'fornecedor';
		$this->_cab[2][] = 'Cod Fabrica';         $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'fabrica';
		$this->_cab[2][] = 'EAN';                 $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'ean';
		$this->_cab[2][] = 'Cod Linha';           $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'codlinha';
		$this->_cab[2][] = 'Linha';               $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'linha';
		$this->_cab[2][] = 'Cod Depto';           $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'codepto';
		$this->_cab[2][] = 'Departamento';        $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'depto';
		$this->_cab[2][] = 'Cod Marca';           $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'codmarca';
		$this->_cab[2][] = 'Marca';               $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'marca';
		$this->_cab[2][] = 'Cod Secao';           $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'codsecao';
		$this->_cab[2][] = 'Secao';               $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'secao';
		$this->_cab[2][] = 'DT Cadastro';         $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'dtcadastro';
		$this->_cab[2][] = 'Status';              $this->_tipos[2][] = 'T'; 			$this->_campos[2][] = 'status';

		foreach ($this->_dias as $dia){
			$this->_cab[2][] = datas::dataS2D($dia);  $this->_tipos[2][] = 'T'; 		$this->_campos[2][] = $dia;
		}
		
		$this->_cab[2][] = 'Cod. Comprador';         $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'codcomp';
		$this->_cab[2][] = 'Comprador';              $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'comprador';
		$this->_cab[2][] = 'Giro Dia';               $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'giro';
		$this->_cab[2][] = 'Dias Sem Estoque';       $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'semestoque';
		$this->_cab[2][] = 'Falta de Estoque';       $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'falta';
		$this->_cab[2][] = 'Preço 35 RS';            $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'p35';
		$this->_cab[2][] = 'Preço FV RS';            $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'pfv';
		$this->_cab[2][] = 'Preço Tab 1';		     $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'pt1';
		$this->_cab[2][] = 'Menor Preço';            $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'pmenor';
		$this->_cab[2][] = 'TAB 1 Margem';           $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'margemt1';
		$this->_cab[2][] = 'Preço Tab 2';            $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'pt2';
		$this->_cab[2][] = 'Preço 35 SC';            $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'p35sc';
		$this->_cab[2][] = 'QT FV QTD RS';           $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'qtfvrs';
		$this->_cab[2][] = 'Valor FV QTD RS';        $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'vlfvrs';
		$this->_cab[2][] = 'Margem FV QTD RS';       $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'mfvrs';
		$this->_cab[2][] = 'Venda Perdida QTD';      $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'vendaPerdidaQ';
		$this->_cab[2][] = 'Venda Perdida R$';       $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'vendaPerdidaV';
		$this->_cab[2][] = 'OBS';                    $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'obs';
		$this->_cab[2][] = 'Principio Ativo';        $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'principio';
		$this->_cab[2][] = 'Descrição 3';            $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'desc3';
		$this->_cab[2][] = 'Lib.Compra';             $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'libcompra';
		$this->_cab[2][] = 'FciaPopular';            $this->_tipos[2][] = 'T';		$this->_campos[2][] = 'popular';
		$this->_cab[2][] = 'PF';                     $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'pf';
		$this->_cab[2][] = 'PMC';                    $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'pmc';
		$this->_cab[2][] = 'Qt. Val';                $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'qtval';
		$this->_cab[2][] = 'Mês';                    $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'venda0';
		$this->_cab[2][] = 'Mês -1';                 $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'venda1';
		$this->_cab[2][] = 'Mês -2';                 $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'venda2';
		$this->_cab[2][] = 'Mês -3';                 $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'venda3';
		$this->_cab[2][] = 'Média';                  $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'media';
		$this->_cab[2][] = 'Estoque Dias';           $this->_tipos[2][] = 'N';		$this->_campos[2][] = 'estdias';
		$this->_cab[2][] = 'Vl.Ult.Ent.';            $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'vlultent';
		$this->_cab[2][] = 'Custo Ult.Ent.';         $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'custoultent';
		$this->_cab[2][] = 'Custo Real';             $this->_tipos[2][] = 'V';		$this->_campos[2][] = 'custo';

		$this->_cab[3][] = 'Cod';                    $this->_tipos[3][] = 'T';
		$this->_cab[3][] = 'Comprador';              $this->_tipos[3][] = 'T';
		$this->_cab[3][] = 'Vl.Compras';             $this->_tipos[3][] = 'V';
		$this->_cab[3][] = '% Vl.Compras';           $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Vl.Estoque';             $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Vl.Avaria';              $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Itens';                  $this->_tipos[3][] = 'N';
		$this->_cab[3][] = '% Itens';                $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Itens FL';               $this->_tipos[3][] = 'N';
		$this->_cab[3][] = 'Itens Cadast.90 Dias';   $this->_tipos[3][] = 'N';
		$this->_cab[3][] = 'Itens Zerados';          $this->_tipos[3][] = 'N';
		$this->_cab[3][] = 'Valor Zerado';           $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'It.Zer.FL';              $this->_tipos[3][] = 'N';
		$this->_cab[3][] = 'Itens Cadastrados';      $this->_tipos[3][] = 'N';
		$this->_cab[3][] = 'Itens > 90';		     $this->_tipos[3][] = 'N';
		$this->_cab[3][] = '%It.>90';                $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Vl.Item>90';             $this->_tipos[3][] = 'V';
		$this->_cab[3][] = '%.Item>90';              $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Item <10';               $this->_tipos[3][] = 'N';
		$this->_cab[3][] = '%It.<10';                $this->_tipos[3][] = 'N';
		$this->_cab[3][] = 'Vl.Item<10';             $this->_tipos[3][] = 'V';
		$this->_cab[3][] = '%.Item<10';              $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Quant.Industr.';         $this->_tipos[3][] = 'N';
		$this->_cab[3][] = 'Vl.Vendas';              $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Margem';                 $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'Bonif/Verbas';           $this->_tipos[3][] = 'V';
		$this->_cab[3][] = 'CMV';                    $this->_tipos[3][] = 'V';

		$this->_cab[4][] = 'Código';                 $this->_tipos[4][] = 'N';		$this->_campos[4][] = 'cod';
		$this->_cab[4][] = 'Fornecedor';             $this->_tipos[4][] = 'T';		$this->_campos[4][] = 'fornecedor';
		$this->_cab[4][] = 'Dt Cadastro';            $this->_tipos[4][] = 'T';		$this->_campos[4][] = 'data';
		$this->_cab[4][] = 'UF';                     $this->_tipos[4][] = 'T';		$this->_campos[4][] = 'uf';
		$this->_cab[4][] = 'Codigo';                 $this->_tipos[4][] = 'N';		$this->_campos[4][] = 'codComp';
		$this->_cab[4][] = 'Comprador';              $this->_tipos[4][] = 'T';		$this->_campos[4][] = 'comprador';
		$this->_cab[4][] = 'Cod.Fornec.Pronc.';      $this->_tipos[4][] = 'N';		$this->_campos[4][] = 'codprinc';
		$this->_cab[4][] = 'Fornecedor Principal';   $this->_tipos[4][] = 'T';		$this->_campos[4][] = 'nomePrincipal';
		$this->_cab[4][] = 'Tipo Fornecedor';        $this->_tipos[4][] = 'T';		$this->_campos[4][] = 'tipo';
		$this->_cab[4][] = 'Gera Dif.Preço';         $this->_tipos[4][] = 'T';		$this->_campos[4][] = 'diferenca';
	
	}
	
	
	private function processaDadosComprador($dadosProdutos){
		$dados = [];
		$totalVendaPerdidaV = 0;
		$totalVendaPerdidaQ = 0;
		$totalItens = 0;
		$totalFL = 0;
		$totalVenda = 0;
		foreach ($dadosProdutos as $d){
			if(!isset($dados[$d['codcomp']])){
				$dados[$d['codcomp']]['cod'       ] = $d['codcomp'];
				$dados[$d['codcomp']]['comprador'] = $d['comprador'];
				$dados[$d['codcomp']]['vendaP'    ] = 0;
				$dados[$d['codcomp']]['PvendaP'   ] = 0;
				$dados[$d['codcomp']]['QvendaP'   ] = 0;
				$dados[$d['codcomp']]['PQvendaP'  ] = 0;
				$dados[$d['codcomp']]['if'        ] = 0;
				$dados[$d['codcomp']]['Pif'       ] = 0;
				$dados[$d['codcomp']]['ic'        ] = 0;
				$dados[$d['codcomp']]['Pic'       ] = 0;
				$dados[$d['codcomp']]['venda'     ] = 0;
				$dados[$d['codcomp']]['Pvenda'    ] = 0;
			}
			$dados[$d['codcomp']]['QvendaP'] += $d['vendaPerdidaQ'];
			$totalVendaPerdidaQ += $d['vendaPerdidaQ'];
			
			//$dados[$d['codcomp']]['venda'] += $d['venda0'];
			//$totalVenda += $d['venda0'];
			$prod = $d['cod'];
			$dados[$d['codcomp']]['venda'] += isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mesVenda'] : 0;
			$totalVenda += isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mesVenda'] : 0;
			
			$dados[$d['codcomp']]['vendaP'] += $d['vendaPerdidaV'];
			$totalVendaPerdidaV += $d['vendaPerdidaV'];
			$dados[$d['codcomp']]['ic']++;
			$totalItens++;
			if($d['semestoque'] > 0){
				$dados[$d['codcomp']]['if']++;
				$totalFL++;
			}
		}
		
		$ret = [];
		foreach ($dados as $comprador => $dado){
			$dado['PvendaP'] 	= $totalVendaPerdidaV > 0 ? ($dado['vendaP'] / $totalVendaPerdidaV) * 100 : 0;
			$dado['PQvendaP'] 	= $totalVendaPerdidaQ > 0 ? ($dado['QvendaP'] / $totalVendaPerdidaQ) * 100 : 0;
			$dado['Pif'] 		= $totalFL > 0 ? ($dado['if'] / $totalFL) * 100 : 0;
			$dado['Pic'] 		= $totalItens > 0 ? ($dado['ic'] / $totalItens) * 100 : 0;
			$dado['Pvenda'] 	= $totalVenda > 0 ? ($dado['venda'] / $totalVenda) * 100 : 0;
			
			$dado['Pqifsqic'] = $dado['ic'] > 0 ? ($dado['if'] / $dado['ic']) * 100: 0;
			$dado['Pqvfsvv'] = $dado['venda'] > 0 ? ($dado['vendaP'] / $dado['venda']) * 100: 0;
			
			$ret[] = $dado;
		}
		
		return $ret;
	}
	
	private function processaDadosFornec($dadosProdutos, $dadosFornecedores){
		$dados = [];
		$totalVendaPerdidaV = 0;
		$totalVendaPerdidaQ = 0;
		$totalItens = 0;
		$totalFL = 0;
		$totalVenda = 0;
		foreach ($dadosProdutos as $d){
		    if(isset($dadosFornecedores[$d['fornec']]['codComp'      ]) && isset($dadosFornecedores[$d['fornec']]['comprador'    ])){
    			if(!isset($dados[$d['fornec']])){
    				$dados[$d['fornec']]['cod'       ] = $d['fornec'];
    				$dados[$d['fornec']]['fornecedor'] = $d['fornecedor'];
    
    				$dados[$d['fornec']]['codcomp'   ] = $dadosFornecedores[$d['fornec']]['codComp'      ];
    				$dados[$d['fornec']]['comprador' ] = $dadosFornecedores[$d['fornec']]['comprador'    ];
    				
    				$dados[$d['fornec']]['vendaP'    ] = 0;
    				$dados[$d['fornec']]['PvendaP'   ] = 0;
    				$dados[$d['fornec']]['QvendaP'   ] = 0;
    				$dados[$d['fornec']]['PQvendaP'  ] = 0;
    				$dados[$d['fornec']]['if'        ] = 0;
    				$dados[$d['fornec']]['Pif'       ] = 0;
    				$dados[$d['fornec']]['ic'        ] = 0;
    				$dados[$d['fornec']]['Pic'       ] = 0;
    				$dados[$d['fornec']]['venda'     ] = 0;
    				$dados[$d['fornec']]['Pvenda'    ] = 0;
    			}
    			$dados[$d['fornec']]['QvendaP'] += $d['vendaPerdidaQ'];
    			$totalVendaPerdidaQ += $d['vendaPerdidaQ'];
    			
    			//$dados[$d['fornec']]['venda'] += $d['venda0'];
    			//$totalVenda += $d['venda0'];
    			
    			$prod = $d['cod'];
    			$dados[$d['fornec']]['venda'] += isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mesVenda'] : 0;
    			$totalVenda += isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mesVenda'] : 0;
    			
    			$dados[$d['fornec']]['vendaP'] += $d['vendaPerdidaV'];
    			$totalVendaPerdidaV += $d['vendaPerdidaV'];
    			$dados[$d['fornec']]['ic']++;
    			$totalItens++;
    			if($d['semestoque'] > 0){
    				$dados[$d['fornec']]['if']++;
    				$totalFL++;
    			}
		    }
		}
		
		$ret = [];
		foreach ($dados as $dado){
			if($totalVendaPerdidaV> 0){
				$dado['PvendaP'] 	= ($dado['vendaP'] / $totalVendaPerdidaV) * 100;
			}else{
				$dado['PvendaP'] 	= 0;
			}
			if($totalVendaPerdidaQ > 0){
				$dado['PQvendaP'] 	= ($dado['QvendaP'] / $totalVendaPerdidaQ) * 100;
			}else{
				$dado['PQvendaP'] 	= 0;
			}
			$dado['Pif'] 		= $totalFL > 0 ? ($dado['if'] / $totalFL) * 100 : 0;
			$dado['Pic'] 		= $totalItens > 0 ? ($dado['ic'] / $totalItens) * 100 : 0;
			$dado['Pvenda'] 	= $totalVenda > 0 ? ($dado['venda'] / $totalVenda) * 100 : 0;
			
			$dado['Pqifsqic'] = $dado['ic'] > 0 ? ($dado['if'] / $dado['ic']) * 100: 0;
			$dado['Pqvfsvv'] = $dado['venda'] > 0 ? ($dado['vendaP'] / $dado['venda']) * 100: 0;
			
			$ret[] = $dado;
		}
		
		return $ret;
	}
	
	private function getDadosProdutos($ano, $mes){
		$ret = [];
		//Utilizado para identificar se o produto é novo
		if($ano == date('Y') && $mes == date('m')){
			$diaFim = date('Ymd');
		}else{
			$diaFim = $ano.$mes.date('t',mktime(0,0,0,$mes,15,$ano));
		}
		$faltas = $this->getFaltas();
		$this->_giroDia = $this->getGiroDia($ano, $mes);
		$vendas = $this->getVendas($ano, $mes);
		$pmc = $this->getPMC();
		$preco35 = $this->getPreçoPromo($this->_promo35);
		$precoTab1 = $this->getPreco(1);
		$precoFV = $this->getPreçoPromo($this->_promoFV);
		if($ano == date('Y') && $mes == date('m')){
			if(date('Ymd') > $this->_dias[count($this->_dias) - 1]){
				// Se for no mesmo dia, mas ainda não houve venda no dia
				$dataFim = $this->_dias[count($this->_dias) - 1];
			}else{
				//No mesmo dia, mas já houve venda no dia
				$dataFim = $this->_dias[count($this->_dias) - 2];
			}
		}else{
			$dataFim = $this->_dias[count($this->_dias) - 1];
		}
		$sql = "
				SELECT
				        PCPRODUT.CODPROD,
				        PCPRODUT.DESCRICAO,
				        PCPRODUT.CODFORNEC,
				        PCFORNEC.FORNECEDOR,
				        PCPRODUT.CODFAB,
				        PCPRODUT.CODAUXILIAR,
				        PCPRODUT.CODLINHAPROD,
				        PCLINHAPROD.DESCRICAO LINHA,
				        PCPRODUT.CODEPTO,
				        PCDEPTO.DESCRICAO DEPTO,
				        PCPRODUT.CODMARCA,
				        PCMARCA.MARCA,
				        PCPRODUT.OBS2,
						PCPRODUT.OBS,
				        PCFORNEC.CODCOMPRADOR,
				        PCEMPR.NOME COMPRADOR,
						PCPRODUT.DTCADASTRO,
				        PCPRODUT.CODSEC,
				        PCSECAO.DESCRICAO SECAO,
						PCPRODUT.CUSTOREP PFABRICA,
						PCPRODUT.FARMACIAPOPULAR,
						PCPRODUT.DESCRICAO3,
						PCPRINCIPATIVO.DESCRICAO PRINCIPIOATIVO
				from 
				    pcprodut,
				    pcfornec,
				    pclinhaprod,
				    pcdepto,
				    pcmarca,
				    pcempr,
				    pcsecao,
					pcprincipativo
				where
				    pcprodut.revenda = 'S'
					and pcprodut.dtexclusao is null
				    AND pcprodut.codfornec = pcfornec.codfornec (+)
				    AND pcprodut.codlinhaprod = pclinhaprod.codlinha (+)
				    AND pcprodut.codepto = pcdepto.codepto (+)
				    and pcprodut.codmarca = pcmarca.codmarca (+)
				    and PCFORNEC.codcomprador = pcempr.matricula (+)
				    and pcprodut.codsec = pcsecao.codsec (+)
				    and pcprodut.CODPRINCIPATIVO = PCPRINCIPATIVO.CODPRINCIPATIVO (+)
					AND NVL(PCPRODUT.OBS2,'') <> 'FL'
					AND DTPRIMEIRACOMPRA <= TO_DATE('$diaFim','YYYYMMDD') - 30
				order by
					PCPRODUT.CODPROD
				";
//echo "$sql <br><br><br><br><br>\n";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$prod = $row['CODPROD'];
				$temp['cod'       ] = $row['CODPROD'];
				$temp['descricao' ] = $row['DESCRICAO'];
				$temp['fornec'    ] = $row['CODFORNEC'];
				$temp['fornecedor'] = $row['FORNECEDOR'];
				$temp['fabrica'   ] = $row['CODFAB'];
				$temp['ean'       ] = $row['CODAUXILIAR'];
				$temp['codlinha'  ] = $row['CODLINHAPROD'];
				$temp['linha'     ] = $row['LINHA'];
				$temp['codepto'   ] = $row['CODEPTO'];
				$temp['depto'     ] = $row['DEPTO'];
				$temp['codmarca'  ] = $row['CODMARCA'];
				$temp['marca'     ] = $row['MARCA'];
				$temp['codsecao'  ] = $row['CODSEC'];
				$temp['secao'     ] = $row['SECAO'];
				$temp['dtcadastro'] = datas::dataMS2D($row['DTCADASTRO']);
				$temp['status'    ] = $row['OBS2'];
				
				$diasZero = 0;
				foreach ($this->_dias as $dia){
					$temp[$dia] = isset($faltas[$prod][$dia]) ? $faltas[$prod][$dia]['quant'] : 0;
					if($dia <= $dataFim && $temp[$dia] == 0){
						$diasZero++;
					}
				}
				
				$temp['codcomp'      ] = $row['CODCOMPRADOR'];
				$temp['comprador'    ] = $row['COMPRADOR'];
				$temp['giro'         ] = isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['giro'] : 0;
				$temp['semestoque'   ] = $diasZero;
				$temp['falta'        ] = $diasZero > 0 ? 'SIM' : '';
				$temp['p35'          ] = isset($preco35[$prod]) ? $preco35[$prod] : 0;
				$temp['pfv'          ] = isset($precoFV[$prod]) ? $precoFV[$prod] : 0;
				$temp['pt1'          ] = isset($precoTab1[$prod]) ? $precoTab1[$prod] : 0;
				$temp['pmenor'       ] = $this->verificaMenorPreco($temp['p35'],$temp['pfv'],$temp['pt1']);
				$temp['margemt1'     ] = '';
				$temp['pt2'          ] = '';
				$temp['p35sc'        ] = '';
				$temp['qtfvrs'       ] = '';
				$temp['vlfvrs'       ] = '';
				$temp['mfvrs'        ] = '';
				$temp['vendaPerdidaQ'] = (int)($temp['semestoque'] * $temp['giro']);
				$temp['vendaPerdidaV'] = $temp['vendaPerdidaQ'] * $temp['pmenor'];
				$temp['obs'          ] = $row['OBS'];
				$temp['principio'    ] = $row['PRINCIPIOATIVO'];
				$temp['desc3'        ] = $row['DESCRICAO3'];
				$temp['libcompra'    ] = '';
				$temp['popular'      ] = $row['FARMACIAPOPULAR'];
				$temp['pf'           ] = $row['PFABRICA'];
				$temp['pmc'          ] = isset($pmc[$prod]) ? $pmc[$prod] : '';
				$temp['qtval'        ] = '';
				$temp['venda0'       ] = isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mes']  : 0;
				$temp['venda1'       ] = isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mes1'] : 0;
				$temp['venda2'       ] = isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mes2'] : 0;
				$temp['venda3'       ] = isset($this->_giroDia[$prod]) ? $this->_giroDia[$prod]['mes3'] : 0;
				$temp['media'        ] = '';
				$temp['estdias'      ] = '';
				$temp['vlultent'     ] = isset($faltas[$prod][$dataFim]) ? $faltas[$prod][$dataFim]['valorult']: 0;
				$temp['custoultent'  ] = isset($faltas[$prod][$dataFim]) ? $faltas[$prod][$dataFim]['custoult']: 0;
				$temp['custo'        ] = isset($faltas[$prod][$dataFim]) ? $faltas[$prod][$dataFim]['custo']: 0;
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function getVendas($ano, $mes){
		
	}
	
	private function verificaMenorPreco($p1,$p2,$p3){
		$ret = [];
		if($p1 > 0) $ret[] = $p1;
		if($p2 > 0) $ret[] = $p2;
		if($p3 > 0) $ret[] = $p3;
		if(count($ret) > 0){
			sort($ret, SORT_NUMERIC );
			return $ret[0];
		}else{
			return 0;
		}
	}
	
	private function getPreçoPromo($promo){
		$ret = [];
		$sql = "select codprod, MAX(precofixopromocaomed) from PCDESCONTO where CODPROMOCAOMED = $promo group by codprod";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				$ret[$prod] = $row[1];
			}
		}
		return $ret;
	}
	
	private function getPreco($tabela){
		$ret = [];
		$sql = "select codprod, pvenda from pctabpr where pctabpr.numregiao = $tabela";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				$ret[$prod] = $row[1];
			}
		}
		return $ret;
	}
	
	private function getPMC(){
		$ret = [];
		$sql = "SELECT CODPROD, NVL(PRECOMAXCONSUM,0) PMC FROM PCTABMEDABCFARMA WHERE PRECOMAXCONSUMTAB > 0 AND UF = 'RS'";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row['CODPROD'];
				$ret[$prod] = $row['PMC'];
			}
		}
		
		return $ret;
	}
	
	private function getGiroDia($ano, $mes){
		$ret = [];
		$temp = [];
		$anomes = [];
		$mesTemp = $mes;
		$anoTemp = $ano;
		
		$anomes[] = $ano.$mes;
		for($i=0;$i<3;$i++){
			$mesTemp--;
			if($mesTemp == 0){
				$mesTemp = 12;
				$anoTemp--;
			}
			$mesTemp = $mesTemp < 10 ? '0'.$mesTemp : $mesTemp;
			$anomes[] = $anoTemp.$mesTemp;
		}
		
		$diasVenda = $this->getDiasVenda($anomes);
//echo "Dias: $diasVenda <br>\n";
		$dataIni = $anomes[3].'01';
		$dataFim = date('Ymt',mktime(0,0,0,$mes,15,$ano));
		$param = [];
		$campos = array("to_char(DATA,'YYYYMM') MESANO",'CODPROD');
		
		$vendas	= vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
		//print_r($vendas);
		
		if(count($vendas) > 0){
			foreach ($vendas as $am => $venda){
				foreach ($venda as $prod => $v){
					$temp[$prod][$am]['quant'] = $v['quantVend'];
					$temp[$prod][$am]['venda'] = $v['venda'];
				}
			}
		}
		
		//Ajusta dados e calcula media
		foreach ($temp as $prod => $vendas){
			foreach ($vendas as $am => $venda){
				if(!isset($ret[$prod])){
					$ret[$prod]['mes']  = 0;
					$ret[$prod]['mes1'] = 0;
					$ret[$prod]['mes2'] = 0;
					$ret[$prod]['mes3'] = 0;
					$ret[$prod]['giro'] = 0;

					$ret[$prod]['mesVenda']  = 0;
					$ret[$prod]['mes1Venda'] = 0;
					$ret[$prod]['mes2Venda'] = 0;
					$ret[$prod]['mes3Venda'] = 0;
				}
				if($am == $anomes[0]){
					$ret[$prod]['mes']  	= $temp[$prod][$am]['quant'];
					$ret[$prod]['mesVenda'] = $temp[$prod][$am]['venda'];
				}elseif($am == $anomes[1]){
					$ret[$prod]['mes1']  	= $temp[$prod][$am]['quant'];
					$ret[$prod]['mes1Venda']= $temp[$prod][$am]['venda'];
				}elseif($am == $anomes[2]){
					$ret[$prod]['mes2']  	= $temp[$prod][$am]['quant'];
					$ret[$prod]['mes2Venda']= $temp[$prod][$am]['venda'];
				}elseif($am == $anomes[3]){
					$ret[$prod]['mes3']  	= $temp[$prod][$am]['quant'];
					$ret[$prod]['mes3Venda']= $temp[$prod][$am]['venda'];
				}
			}
		}
		
		foreach ($ret as $prod => $val){
			$total = $val['mes1'] + $val['mes2'] + $val['mes3'];
			IF($total > 0 && $diasVenda > 0){
				$ret[$prod]['giro'] = (int)($total / $diasVenda);
			}
		}
		
		return $ret;
	}
	
	private function getDiasVenda($anomes){
		$ret = 0;
		$dataIni = $anomes[3].'01';
		$ano = substr($anomes[1], 0, 4);
		$mes = substr($anomes[1], 4, 2);
		$dataFim = date('Ymt',mktime(0,0,0,$mes,15,$ano));
		//$sql = "select count(*) from pcdiasuteis where diavendas = 'S' and data >= TO_DATE('$dataIni','YYYYMMDD') and data <= TO_DATE('$dataFim','YYYYMMDD')";
		$sql = "select count(DISTINCT DTSAIDA) FROM pcnfsaid WHERE DTSAIDA >= TO_DATE('$dataIni','YYYYMMDD') AND DTSAIDA <= TO_DATE('$dataFim','YYYYMMDD') ORDER BY DTSAIDA";

		$rows = query4($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	private function getFaltas(){
		$ret = [];
		if(count($this->_dias) > 0){
			$dataIni = $this->_dias[0];
			$dataFim = $this->_dias[count($this->_dias) - 1];
			
			$sql = "SELECT DATA, CODPROD, QTESTGER, QTINDENIZ, CUSTOREAL, CUSTOULTENT, VALORULTENT, DTULTENT  FROM PCHISTEST WHERE codfilial = 1 and data >= to_date('$dataIni','YYYYMMDD') and data <= to_date('$dataFim','YYYYMMDD')";
//echo "$sql <br><br><br><br>\n";
			$rows = query4($sql);
			if(count($rows) > 0){
				foreach ($rows as $row){
					$prod = $row['CODPROD'];
					$dia = datas::dataD2S(datas::dataMS2D($row['DATA']));
					$ret[$prod][$dia]['quant'] = $row['QTESTGER'] - $row['QTINDENIZ'];
					$ret[$prod][$dia]['custo'] = $row['CUSTOREAL'];
					$ret[$prod][$dia]['custoult'] = $row['CUSTOULTENT'];
					$ret[$prod][$dia]['valorult'] = $row['VALORULTENT'];
					$ret[$prod][$dia]['datault'] = datas::dataMS2D($row['DTULTENT']);
				}
			}
			
			return $ret;
		}
	}
	
	private function getDadosFornecedores(){
		$ret = [];
		$tipo = array('D' => 'Central Distribuição','K' => 'Cliente','C' => 'Comércio Atacadista','V' => 'Comércio Varegista','F' => 'Fornecedor Interno','I' => 'Indústria','O' => 'Outros','S' => 'Prestador Serviço', 'N' => 'Simples Nascional');
		$sql = "
				SELECT 
				    pcfornec.CODFORNEC,
				    FORNECEDOR,
				    DTCADASTRO,
				    pcfornec.ESTADO,
				    CODCOMPRADOR,
				    NOME,
				    CODFORNECPRINC,
				    (SELECT FORNEC.FORNECEDOR FROM pcfornec FORNEC WHERE FORNEC.CODFORNEC = pcfornec.CODFORNECPRINC) NOMEPRINCIPAL,
				    TIPOFORNEC,
				    GERACREDDIFPRECO
				FROM
				    pcfornec,
				    pcempr
				WHERE
				    CODCOMPRADOR = MATRICULA
				    AND TIPOFORNEC NOT IN ('T','K','S')
				
				";
		$rows = query4($sql);
		if(count($rows) >0 ){
			foreach ($rows as $row){
				$temp = [];
				
				
				
				$temp['cod'          ] = $row['CODFORNEC']; 
				$temp['fornecedor'   ] = $row['FORNECEDOR']; 
				$temp['data'         ] = datas::dataMS2D($row['DTCADASTRO']); 
				$temp['uf'           ] = $row['ESTADO']; 
				$temp['codComp'      ] = $row['CODCOMPRADOR']; 
				$temp['comprador'    ] = $row['NOME']; 
				$temp['codprinc'     ] = $row['CODFORNECPRINC']; 
				$temp['nomePrincipal'] = $row['NOMEPRINCIPAL'];
			    $temp['tipo'         ] = $tipo[$row['TIPOFORNEC']]; 
		        $temp['diferenca'    ] = $row['GERACREDDIFPRECO'];
		        
		        $ret[$row['CODFORNEC']] = $temp;
			}
		}	
		return $ret;
	}
	
	private function getDadosIndicadores($ano, $mes){
		$obj = CreateObject('gfcompras.indicadorComprador');
		$obj->autoExec($ano, $mes, 'S');
		
		$this->_campos[3] = $obj->_relatorio->getCampos();
		return $obj->_dados;
	}
	
	private function getDiasPeriodo($ano, $mes){
		$dataIni = $ano.$mes.'01';
		$dataFim = date('Ymt',mktime(0,0,0,$mes,15,$ano));
		
		$sql = "select DISTINCT DTSAIDA FROM pcnfsaid WHERE DTSAIDA >= TO_DATE('$dataIni','YYYYMMDD') AND DTSAIDA <= TO_DATE('$dataFim','YYYYMMDD') ORDER BY DTSAIDA";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$this->_dias[] = datas::dataD2S( datas::dataMS2D($row[0]));
			}
		}
	}
	
	/*
	 * Por solicitação da Jeniffer não deve aparecer produtos novos
	 * Produtos novos = produtos com menos de 30 dias da primeira entrada
	 * Para não ficar demorado foi criado no cadastro de produto o campo dtprimeiracompra
	 * que é atualizado por esta rotina
	 */
	
	private function ajustaDataPrimeiraCompra(){
		$sql = "SELECT CODPROD, MIN(DTMOV) DTMOV FROM PCMOV WHERE CODOPER LIKE 'E%' AND PCMOV.DTCANCEL IS NULL AND CODPROD IN (SELECT CODPROD FROM PCPRODUT WHERE DTPRIMEIRACOMPRA IS NULL) GROUP BY CODPROD";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$sql = "UPDATE pcprodut SET dtprimeiracompra = to_date('".$row['DTMOV']."','YYYY-MM-DD') WHERE CODPROD = ".$row['CODPROD'];
				$rows = query4($sql);
			}
		}
	}
}