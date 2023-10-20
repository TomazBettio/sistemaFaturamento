<?php
/*
 * Data Criacao 5 de dez de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: class.ora_superestocados.inc.php
 *  
 * Descricao: Relatório de super estocados
 * Solicitante: Joyce
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class superestocados{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa;
	
	//Dados
	var $_dados;
	
	//Promocoes
	var $_promo;
	
	//Apelidos promocoes
	var $_apelidos;
	
	//Dias de faturamento
	var $_diasFat;
	
	//Dias uteis
	var $_diasUteis;
	
	//Meses
	var $_meses;
	
	//Quantidade de faixas da promo 9797
	var $_p9797;
	
	//Se deve aparecer os preços da promo 9798
	var $_p9798;
	
	//Calculo margem
	var $_margem;
	
	//Custo do produto
	var $_custo;
	
	//datas
	var $_datas;
	
	//Preços da região 1 e 2
	private $_precosTab = [];
	
	//Estados da promocao
	private $_ufPromo = [];
	
	function __construct(){
		set_time_limit(0);
		//$this->_promo = array(21281,21961,21962);
		//$this->_promo = array(21961,21962, 43314, 43315);
		//$this->_apelidos = array('PERF 35D RS','PERF 35D SC','EXC FV DESC RS','EXC FV DESC SC');
		//28/07/20 - Vanessa Ignacio
		$this->_promo = array(44925,44926, 43314, 43315, 43312, 43313);
		$this->_apelidos = array('PERF 35D RS','PERF 35D SC','EXC FV DESC RS','EXC FV DESC SC','PROMO 2% RS','PROMO 2% SC');
		$this->_ufPromo = ['1','2','1','2','1','2'];
		
		//9797 – FV QTDE RS
		//$this->_p9797 = $this->getNumeroFaixas('9797');
		$this->_p9797 = false; 
		
		//9798 - FV SC
		$this->_p9798 = false;
		
		$this->_meses = array('','Janeiro','Fevereiro','Marco','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
		
		$meses = $this->getMeses();
		
		$this->_programa = 'superestocados';
		$param = array();
		$param['paginacao'] = true;
		$param['scrollX'] 	= true;
		$param['scrollY'] 	= true;
		$param['scroll'] 	= true;
		$param['programa'] 	= $this->_programa;
		$param['titulo'] 	= 'Relatorio Super Estocados ' . datas::data_hoje();
		$param['print']		= false;
		$this->_relatorio = new relatorio01($param);
		
		$this->_relatorio->addColuna(array('campo' => 'cod'			, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'fab'			, 'etiqueta' => 'Fab'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'desc'		, 'etiqueta' => 'Descricao'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'descWEB'		, 'etiqueta' => 'Descricao WEB'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ean'			, 'etiqueta' => 'Cod.Barras'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'categ'		, 'etiqueta' => 'Categoria'		, 'tipo' => 'T', 'width' =>  90, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'codfornec'	, 'etiqueta' => 'Cod.Fornec'	, 'tipo' => 'T', 'width' =>  30, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'fornec'		, 'etiqueta' => 'Fornecedor'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'comprador'	, 'etiqueta' => 'Comprador'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'marca'		, 'etiqueta' => 'Marca'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'marca2'		, 'etiqueta' => 'Marca 2'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'depto'		, 'etiqueta' => 'Depto'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'principio'	, 'etiqueta' => 'Princ.Ativo'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'principio2'	, 'etiqueta' => 'Princ.Ativo 2'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'descricao3'	, 'etiqueta' => 'Descricao 3'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'descricao4'	, 'etiqueta' => 'Descricao 4'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'compra'		, 'etiqueta' => 'Lib.Compra'	, 'tipo' => 'T', 'width' =>  30, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'popular'		, 'etiqueta' => 'Fcia Popular'	, 'tipo' => 'T', 'width' =>  30, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'abc'			, 'etiqueta' => 'Classf.Venda'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'qtmaster'	, 'etiqueta' => 'Qt Master'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'C'));
		//QTUNITCX
		$this->_relatorio->addColuna(array('campo' => 'pf'			, 'etiqueta' => 'PF'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pmc'			, 'etiqueta' => 'PMC'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pmpf'		, 'etiqueta' => 'PMPF'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'desc1'		, 'etiqueta' => '%Desc 1'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'desc2'		, 'etiqueta' => '%Desc 2'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'desc3'		, 'etiqueta' => '%Desc 3'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'desc4'		, 'etiqueta' => '%Desc 4'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'desc9'		, 'etiqueta' => '%Desc 9'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'desc10'		, 'etiqueta' => '%Desc 10'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'rep'			, 'etiqueta' => 'Vlr.Rep'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'saldo'		, 'etiqueta' => 'Saldo'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'validade'	, 'etiqueta' => 'Validade'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'qtval'		, 'etiqueta' => 'Qt. Val'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		//22.03.18 - Jeniffer - 
		for($i=0;$i<4;$i++){
			$mes = $this->_meses[$meses[$i]];
			$this->_relatorio->addColuna(array('campo' => 'qtPedItem'.$i, 'etiqueta' => 'Qt. Pedidos<br>'.$mes	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'mes'.($i+1)	, 'etiqueta' => 'Venda<br>'.$mes		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'qtMedia'.$i	, 'etiqueta' => 'Venda Media<br>'.$mes	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'qtCli'.$i	, 'etiqueta' => 'Qt.Clientes<br>'.$mes	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		}
		//$this->_relatorio->addColuna(array('campo' => 'mes2'		, 'etiqueta' => $this->_meses[$meses[1]]	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'mes3'		, 'etiqueta' => $this->_meses[$meses[2]]	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'mes4'		, 'etiqueta' => $this->_meses[$meses[3]]	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'media'		, 'etiqueta' => 'Media'				, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'dia'			, 'etiqueta' => 'Giro Dia'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'est'			, 'etiqueta' => 'Estoque Dias'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));

		// INI: 06.03.2019--------------------------------------------------------------------------------------------------------------------------------------
		$this->_relatorio->addColuna(array('campo' => 'est_disp'	 , 'etiqueta' => 'Est.Disp'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'est_dias_disp', 'etiqueta' => 'Est.Dias Disp'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		// FIM ----------------------------------------------------------------------------------------------------------------------------- by Aless|Gustavo --

		$this->_relatorio->addColuna(array('campo' => 'pendente'	, 'etiqueta' => 'Qt.Pendente'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'pre'			, 'etiqueta' => 'Qt.Pre Ent.'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'DTultima'	, 'etiqueta' => 'Data Ult.Ent.'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'ultima'		, 'etiqueta' => 'Vl.Ult.Ent.'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'custo'		, 'etiqueta' => 'Custo Ult. Ent.'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'real'		, 'etiqueta' => 'Custo Real'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'tabela1'		, 'etiqueta' => 'TABELA 1<br>(RS)'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'tab1marg'	, 'etiqueta' => 'TABELA 1<br>Margem', 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
//		$this->_relatorio->addColuna(array('campo' => 'ptabela11'	, 'etiqueta' => 'PTABELA1<br>Tab 1' , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'tabela2'		, 'etiqueta' => 'TABELA 2<br>(SC)'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
//		$this->_relatorio->addColuna(array('campo' => 'ptabela12'	, 'etiqueta' => 'PTABELA1<br>Tab 2' , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		foreach ($this->_promo as $i => $promo){
			$this->_relatorio->addColuna(array('campo' => 'tab'.$i	, 'etiqueta' => $this->_apelidos[$i], 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		}
		if(gettype($this->_p9797) != 'boolean'){
			for($i=0;$i<$this->_p9797;$i++){
				$this->_relatorio->addColuna(array('campo' => 'q9797'.$i	, 'etiqueta' => 'Quant.<br>FV QTDE RS'	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
				$this->_relatorio->addColuna(array('campo' => 'p9797'.$i	, 'etiqueta' => 'Valor<br>FV QTDE RS'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
				$this->_relatorio->addColuna(array('campo' => 'm9797'.$i	, 'etiqueta' => 'Margem<br>FV QTDE RS'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
			}
		}
		
		if($this->_p9798){
			$this->_relatorio->addColuna(array('campo' => 'p9798'		, 'etiqueta' => 'Valor<br>FV QTDE SC'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		}
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Dias Uteis'		, 'variavel' => 'DIAS', 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){
		$filtro = $this->_relatorio->getFiltro();
		$this->_diasUteis = isset($filtro['DIAS']) && $filtro['DIAS'] != '' ? $filtro['DIAS'] : 1;
		
		//$this->_relatorio->setTitulo("Relatorio Super Estocados ".date('d.m.Y'));
		if(!$this->_relatorio->getPrimeira()){
			$this->getDiasFatMes();
			$this->getDadosCadastro();
			$this->getPMPF();
			$this->getEstoque();
			$this->getLotes();
			$this->ajustaListas();
			$this->ajustaTabela(1);
			$this->ajustaTabela(2);
			if(gettype($this->_p9797) != 'boolean'){
				$this->ajusta9797();
			}
			if($this->_p9798){
				$this->ajusta9798();
			}
			
			$this->getDetalheQuantidades();
			
			$dados = array();
			if(count($this->_dados) > 0){
				foreach ($this->_dados as $dado){
					$dados[] = $dado;
				}
			}
	
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}
		return $this->_relatorio . '';
	}

	function schedule($param){
	
	}

	function getDadosCadastro(){
		$pedidos = $this->getDadosPedidos();
		//print_r($pedidos);die();
		$sql = "
				select 
				    pcprodut.codprod,
				    pcprodut.codfab,
				    pcprodut.descricao,
				    pcprodut.codauxiliar,
					(SELECT pccategoria.categoria 
				       FROM pccategoria 
					  WHERE pccategoria.codcategoria = pcprodut.codcategoria and pccategoria.codsec = pcprodut.codsec ) as categ,
					  
				    pcfornec.fornecedor,
				    (select pcempr.nome_guerra from pcempr where pcempr.matricula = pcfornec.codcomprador) comprador,
				    pcmarca.marca,
				    pcprodut.obs2 liberado,
				    farmaciapopular,
				    
				    pcprodut.custoreptab,
				    pcprodut.precomaxconsum,
				    
				    pcprodut.percdesc1,
				    pcprodut.percdesc2,
				    pcprodut.percdesc3,
				    pcprodut.percdesc4,
				    pcprodut.percdesc9,
				    pcprodut.percdesc10,
				    pcdepto.descricao,
					pcprincipativo.descricao PRINCIPIO,
				    pcprodut.DESCRICAO3,
					pcprodut.DESCRICAO4,
					PCPRODUT.CLASSEVENDA,
					pcprodut.QTUNITCX,
					pcprodut.codfornec,
                    pcprodut.DESCRICAO6,
					pcprodut.NOMEECOMMERCE,
					pcprodut.NOMEECOMMERCE_MARKETPLACE
				from 
				    pcprodut,
				    pcfornec,
				    pcmarca,
				    pcdepto,
				    pcprincipativo
				where 
				    pcprodut.codfornec = pcfornec.codfornec (+)
				    and pcprodut.codmarca = pcmarca.codmarca (+)
				    and pcprodut.codepto = pcdepto.codepto (+)
					and pcprodut.codprincipativo = pcprincipativo.codprincipativo (+)
				    --and pcprodut.obs2 <> 'FL'
				    and pcprodut.dtexclusao is null
				    --and pcprodut.codprod = 31
				order by
				    pcprodut.codprod
				";
		$rows = query4($sql);
		//echo "$sql \n";
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$produto = $row[0];
				$this->_dados[$produto] = $this->geraMatriz();
				$this->_dados[$produto]['cod'		] 	= $row[0];
				$this->_dados[$produto]['fab'		] 	= $row[1];
				$this->_dados[$produto]['desc'		] 	= $row[2];
				$this->_dados[$produto]['descWEB'	] 	= $row['NOMEECOMMERCE'];
				$this->_dados[$produto]['ean'		] 	= $row[3];

				$this->_dados[$produto]['categ'		]	= $row[4];

				$this->_dados[$produto]['fornec'	] 	= $row[5];
				$this->_dados[$produto]['comprador'] 	= $row[6];
				$this->_dados[$produto]['marca'	] 		= $row[7];
				$this->_dados[$produto]['marca2'	] 	= $row['DESCRICAO6'];
				$this->_dados[$produto]['depto'	] 		= $row[18];
				$this->_dados[$produto]['principio'	]	= $row['PRINCIPIO'];
				$this->_dados[$produto]['principio2']	= $row['DESCRICAO3'];
				$this->_dados[$produto]['descricao3']	= $row['DESCRICAO3'];
				$this->_dados[$produto]['descricao4']	= $row['DESCRICAO4'];
				$this->_dados[$produto]['compra'	] 	= $row[8] == 'FL' ? 'N' : 'S';
				$this->_dados[$produto]['popular'	] 	= $row[9];
				$this->_dados[$produto]['abc'] 			= $row['CLASSEVENDA'];
				$this->_dados[$produto]['qtmaster'] 	= $row['QTUNITCX'];
				
				$this->_dados[$produto]['pf'	]	 	= $row[10];
				$this->_dados[$produto]['pmc'	] 		= $row[11];
				$this->_dados[$produto]['desc1'	] 		= $row[12];
				$this->_dados[$produto]['desc2'	] 		= $row[13];
				$this->_dados[$produto]['desc3'	] 		= $row[14];
				$this->_dados[$produto]['desc4'	] 		= $row[15];
				$this->_dados[$produto]['desc9'	] 		= $row[16];
				$this->_dados[$produto]['desc10'] 		= $row[17];
				$this->_dados[$produto]['rep'	] 		= $this->calculaREP($row[0]);				

				$this->_dados[$produto]['pendente']		= isset($pedidos[$produto]) ? $pedidos[$produto]['pendente'] : 0;
				$this->_dados[$produto]['pre']			= isset($pedidos[$produto]) ? $pedidos[$produto]['pre'] : 0;

				$this->_dados[$produto]['codfornec'] 	= $row[24];
			}                                             
		}

		return;	
	}
	
	private function getPMPF(){
		$sql = "SELECT CODPROD, PMPF FROM PCTABMEDABCFARMA WHERE PCTABMEDABCFARMA.UF = 'RS' AND PMPF IS NOT NULL AND PMPF > 0";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach($rows as $row){
				$this->_dados[$row['CODPROD']]['pmpf'] = $row['PMPF'];
			}
		}
	}
	
	function getCusto($produto,$preco){
		$sql = "SELECT gf_custopedcompra($produto,$preco) FROM DUAL";
		$c = query4($sql);
		$custo = $c[0][0];
		//echo "Produto: $produto  Preco: $preco  Custo: $custo \n";
		return $custo;
	}
	
	/*
	 * Retorna dados referentes a pedidos de compras
	 */
	private function getDadosPedidos(){
		$ret = array();
		$sql = "
				SELECT
                    CODPROD,
                    SUM(QTPEDIDA) QTPEDIDA,
                    SUM(QTENTREGUE) QTENTREGUE,
                    SUM(QTPRE) QTPRE
                FROM
                    (
                    SELECT
                            P.NUMPED,
                            I.CODPROD,
                            I.QTPEDIDA,
                            I.QTENTREGUE,
                            (SELECT SUM(MOV.QT) FROM PCMOVPREENT MOV WHERE MOV.NUMPED = I.NUMPED AND MOV.CODPROD = I.CODPROD) QTPRE
                            --(NVL(  (SELECT SUM(PCMOVPREENT.QT) FROM PCMOVPREENT, PCNFENTPREENT  
                            --        WHERE PCMOVPREENT.NUMTRANSENT = PCNFENTPREENT.NUMTRANSENT                                                    
                            --            AND PCMOVPREENT.CODPROD     = I.CODPROD                                                              
                            --            AND PCMOVPREENT.NUMPED      = I.NUMPED 
                            --            AND PCMOVPREENT.DTCANCEL IS NULL                                                                          
                            --            AND NVL(PCNFENTPREENT.STATUS, 'X') <> 'E')
                            --,0)) QTPRE
                        FROM 
                            PCPEDIDO P,
                            PCITEM I
                        WHERE
                            P.NUMPED = I.NUMPED
                            AND (NVL(I.QTPEDIDA, 0) > NVL(I.QTENTREGUE, 0))
                    )
                WHERE
                    1 = 1
                GROUP BY 
                    CODPROD
		";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = array();
				$codprod = $row['CODPROD'];

				$temp['qt'] 		= $row['QTPEDIDA'];
				$temp['entregue'] 	= $row['QTENTREGUE'];
				$temp['pendente'] 	= $row['QTPEDIDA'] - $row['QTENTREGUE'];
				$temp['pre'] 		= ($row['QTPRE'] - $row['QTENTREGUE'])> 0 ? ($row['QTPRE'] - $row['QTENTREGUE']) : 0;
				//13/06/23 - Questionamento do Gabriel, ajuste para ficar igual ao WT
				//18/09/23 - Gustavo - Solicitação do compras para retornar ao calculo antigo
				//$temp['pre'] 		= $row['QTPRE'];
				
				$ret[$codprod] = $temp;
			}
		}
		return $ret;
	}
	
	private function getDetalheQuantidades(){
		$param = array();
		$campos = 'CODPROD';;
		
		for($i=0;$i<4;$i++){
			$vendas = vendas1464Campo($campos, $this->_datas[$i]['ini'], $this->_datas[$i]['fim'], $param, false);
			foreach ($vendas as $prod => $venda){
				if(isset($this->_dados[$prod])){
					$this->_dados[$prod]['qtPedItem'.$i] = $venda['pedidos'];
					$this->_dados[$prod]['mes'.($i+1)] 	= $venda['quantVend'];
					$this->_dados[$prod]['qtMedia'.$i] 	= $venda['pedidos'] > 0 ? $venda['quantVend']/$venda['pedidos']: 0;
					$this->_dados[$prod]['qtCli'.$i] 	= $venda['positivacao'];
				}
			}
		}
	}
	
	function getEstoque(){
		$sql = "SELECT pcest.codprod       
					  ,pcest.qtestger 
					  ,pcest.custoreal
					  ,pcest.qtvendmes     
					  ,pcest.qtvendmes1
					  ,pcest.qtvendmes2
					  ,pcest.qtvendmes3
					  ,pcest.custoultentfin
					  ,pcest.valorultent
					  ,pcest.QTINDENIZ
					  ,pcest.DTULTENT
					  ,NVL(((NVL(PCEST.QTESTGER, 0) - NVL (PCEST.QTRESERV, 0)- NVL (PCEST.QTBLOQUEADA, 0))), 0) as est_disp
				 FROM pcest 
				WHERE pcest.codfilial=1 ";
				
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				if(isset($this->_dados[$prod])){
					$this->_dados[$prod]['saldo'] 	 = ($row[1]-$row[9]);
					$this->_dados[$prod]['real']  	 = $row[2];
					$this->_dados[$prod]['mes1']  	 = $row[3];
					$this->_dados[$prod]['mes2']  	 = $row[4];
					$this->_dados[$prod]['mes3'] 	 = $row[5];
					$this->_dados[$prod]['mes4'] 	 = $row[6];					
					$this->_dados[$prod]['media'] 	 = ((($row[3]/$this->_diasFat)*$this->_diasUteis)+$row[4]+$row[5]+$row[6])/4;
					$this->_dados[$prod]['dia']   	 = $this->_dados[$prod]['media']/30;
					$est = 0;
					if($row[1] > 0){
						if($this->_dados[$prod]['dia'] <= 0){
							$est = 999;
						}else{
							$est = $row[1] / $this->_dados[$prod]['dia'];
						}
					}
					$this->_dados[$prod]['est'] 	 = $est;
					$this->_dados[$prod]['custo'] 	 = $row[7];
					$this->_dados[$prod]['ultima'] 	 = $row[8];
					$this->_dados[$prod]['DTultima'] = datas::dataMS2D($row['DTULTENT']);

					// INI: 06.03.2019----------------------------------------------------------------------------------------------------------------------
					$this->_dados[$prod]['est_disp'] 	  = $row[11]; //EST_DISP
					$this->_dados[$prod]['est_dias_disp'] = $row[11] /  ( $this->_dados[$prod]['dia']>0 ? $this->_dados[$prod]['dia'] : 1 ); //EST_DIAS_DISP
					// FIM ------------------------------------------------------------------------------------------------------------- by Aless|Gustavo --
				}
			}
		}
	}
	
	/*
	 * Calcula o valor de reposição
	 */
	function calculaREP($produto){
		$preco = $this->_dados[$produto]['pf'	];
		$desc1 = $this->_dados[$produto]['desc1'	];
		$desc2 = $this->_dados[$produto]['desc2'	];
		$desc3 = $this->_dados[$produto]['desc3'	];
		$desc4 = $this->_dados[$produto]['desc4'	];
		$desc9 = $this->_dados[$produto]['desc9'	];
		$desc10 = $this->_dados[$produto]['desc10'];
		
		$vl = $preco - ($preco * ($desc1/100));
		
		$vl = $vl - ($vl * ($desc2/100));
		$vl = $vl - ($vl * ($desc3/100));
		$vl = $vl - ($vl * ($desc4/100));
		$vl = $vl - ($vl * ($desc9/100));
		$vl = $vl - ($vl * ($desc10/100));
		
		return $vl;
	}
	
	function getLotes(){
		$sql = "
				SELECT
				    codprod,
				    dtvalidade,
				    sum(qt - nvl(qtreserv,0) - nvl(qtbloqueada,0)) QT
				from
				    pclote
				where (qt - nvl(qtreserv,0) - nvl(qtbloqueada,0)) > 0
				    and codfilial = 1
					and dtbloqueio1 is null
				group by 
				    codprod,
				    dtvalidade
				order by 
				    codprod,
				    dtvalidade
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				if(isset($this->_dados[$prod]) && (!isset($this->_dados[$prod]['qtval']) || $this->_dados[$prod]['qtval'] == 0)){
					$this->_dados[$prod]['validade'] = datas::dataMS2D($row[1]);
					$this->_dados[$prod]['qtval'] 	 = $row[2];
				}
			}
		}
	}
	
	function geraMatriz(){
		$temp = array();
		$campos = $this->_relatorio->getCampos();
		//print_r($campos);die();
		foreach ($campos as $campo){
			$temp[$campo] = '';
		}
		
		return $temp;
	}
	
	/*
	function geraMatriz2(){
		$ret = array();
		$ret['cod'		] = '';
		$ret['fab'		] = '';
		$ret['desc'		] = '';
		$ret['ean'		] = '';

		$ret['categ'	] = '';

		$ret['fornec'	] = '';
		$ret['comprador'] = '';
		$ret['marca'	] = '';
		$ret['depto'	] = '';
		$ret['principio'] = '';
		$ret['descricao3']= '';
		$ret['descricao4']= '';
		$ret['compra'	] = '';
		$ret['popular'	] = '';
		$ret['abc'] 	  = '';
		$ret['qtmaster']  = 0;
		
		$ret['pf'		] = 0;
		$ret['pmc'		] = 0;
		$ret['desc1'	] = 0;
		$ret['desc2'	] = 0;
		$ret['desc3'	] = 0;
		$ret['desc4'	] = 0;
		$ret['desc9'	] = 0;
		$ret['desc10'	] = 0;
		$ret['rep'		] = 0;
		
		$ret['saldo'	] = 0;
		$ret['validade'	] = 0;
		$ret['qtval'	] = 0;
		 
		$ret['mes1'		] = 0;
		$ret['mes2'		] = 0;
		$ret['mes3'		] = 0;
		$ret['mes4'		] = 0;
		$ret['media'	] = 0;
		$ret['dia'		] = 0;
		$ret['est'		] = 0;
		
		// INI: 06.03.2019 -----------
		$ret['est_disp']	  =	0;
		$ret['est_dias_disp'] =	0;
		// FIM --- by Aless|Gustavo --
		
		$ret['pendente'	] = 0;
		$ret['pre'		] = 0;
		
		$ret['DTultima'	] = '';
		$ret['ultima'	] = 0;
		$ret['custo'	] = 0;
		$ret['real'		] = 0;
		
		$ret['tabela1'	] = 0;
		$ret['tab1marg'	] = 0;
		$ret['tabela2'	] = 0;
		
		foreach ($this->_promo as $i => $promo){
			$ret['tab'.$i] = 0;
		}
		for($i=0;$i<$this->_p9797;$i++){
			$ret['q9797'.$i] = 0;
			$ret['p9797'.$i] = 0;
			$ret['m9797'.$i] = 0;
		}
		if($this->_p9798){
			$ret['p9798'] = 0;
		}
		
		
		return $ret;
	}
	*/
	
	/*
	 * Faixas 9797
	 */
	function  ajusta9797(){
		$sql = "
                SELECT 
					CODPROD, 
					INICIOINTERVALOPROMOCAOMED, 
					MAX(PRECOFIXOPROMOCAOMED) PRECO
                FROM 
					PCDESCONTO
                WHERE 
					PCDESCONTO.CODPROMOCAOMED = 9797 
                GROUP BY 
					CODPROD, 
					INICIOINTERVALOPROMOCAOMED
                ORDER BY 
					CODPROD, 
					INICIOINTERVALOPROMOCAOMED
				";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				$quant = $row[1];
				$val = $row[2];
				if(isset($this->_dados[$prod])){
					for($i=0;$i<$this->_p9797;$i++){
						if($this->_dados[$prod]['q9797'.$i] == 0){
							$this->_dados[$prod]['q9797'.$i] = $quant;
							$this->_dados[$prod]['p9797'.$i] = $val;

							if($val > 0){
								//Margem Tabela 1
								$margem = $this->getMargem($prod);
								//$custo = $this->getCusto($prod, $val);
								$custo = $this->_dados[$prod]['real'];
								//if($prod == 56){echo "calculaMargem($val, ".$margem[2].", ".$margem[3].", ".$margem[0].", $custo,$prod";}
								//$this->_dados[$prod]['m9797'.$i] = number_format($this->calculaMargem($val, $margem[2], $margem[3], $margem[0], $custo,$prod), 1, ',', '.');
								$this->_dados[$prod]['m9797'.$i] = $this->calculaMargem($val, $margem[2], $margem[3], $margem[0], $custo,$prod);
								
							}
							
							break;
						}
					}
				}
			}
		}
	}
	
	/*
	 * Faixas 9798
	 */
	function  ajusta9798(){
		$sql = "SELECT CODPROD, INICIOINTERVALOPROMOCAOMED, MAX(PRECOFIXOPROMOCAOMED) PRECO
                  FROM PCDESCONTO
                 WHERE PCDESCONTO.CODPROMOCAOMED = 9798
                 GROUP BY CODPROD, INICIOINTERVALOPROMOCAOMED
                 ORDER BY CODPROD, INICIOINTERVALOPROMOCAOMED ";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				$quant = $row[1];
				$val = $row[2];
				if(isset($this->_dados[$prod])){
					$this->_dados[$prod]['p9798'] = $val;
				}
			}
		}
	}
	
	/*
	 * Retorna a quantidade de dias já faturado
	 */
	function getDiasFatMes(){
		$anomes = date('Ym');
		$sql = " SELECT count(distinct(VENDAS.DTSAIDA))
				   FROM (VIEW_VENDAS_RESUMO_FATURAMENTO) VENDAS
				  WHERE VENDAS.DTCANCEL IS NULL 
				    AND to_char(VENDAS.DTSAIDA,'YYYYMM') = '$anomes'
				    AND NVL(VENDAS.CONDVENDA,0) NOT IN (4, 8, 10,13, 20, 98, 99)
				    AND NVL(VENDAS.CODFISCAL,0) NOT IN (522, 622, 722, 532, 632, 732)
				    AND vendas.codepto in (1,12) ";
		$rows = query4($sql);
		
		$this->_diasFat = $rows[0][0];
		$sql = " select codprod, iniciointervalopromocaomed, max(precofixopromocaomed) preco
	               from PCDESCONTO
	              where PCDESCONTO.codpromocaomed = 9797
	              group by codprod, iniciointervalopromocaomed
	              ORDER BY codprod, iniciointervalopromocaomed ";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				$quant = $row[1];
				$val = $row[2];
				if(isset($this->_dados[$prod])){
					for($i=0;$i<$this->_p9797;$i++){
						if($this->_dados[$prod]['q9797'.$i] == 0) {
						   $this->_dados[$prod]['q9797'.$i] = $quant;
						   $this->_dados[$prod]['p9797'.$i] = $val;
						   break;
						}
					}
				}
			}
		}
	
	}
	
	/*
	 * Retorna o mes atual e os 3 anteriores
	 */
	function getMeses(){
		$atual 	= date('m');
		$ano 	= date('Y');
		$dia 	= date('t',mktime(0,0,0,$atual,15,$ano));
		$ret[] = (int)$atual;
		
		$this->_datas[0]['ini'] = $ano.$atual.'01';
		$this->_datas[0]['fim'] = $ano.$atual.$dia;
		
		for($i=0;$i<3;$i++){
			$atual--;
			if($atual == 0){
				$atual = 12;
				$ano--;
			}
			$ret[] = $atual;

			$atual = $atual < 10 ? '0'.$atual : $atual;
			$dia 	= date('t',mktime(0,0,0,$atual,15,$ano));
			$this->_datas[$i+1]['ini'] = $ano.$atual.'01';
			$this->_datas[$i+1]['fim'] = $ano.$atual.$dia;
		}
		return $ret;
	}
	
	/*
	 * Ajusta o valor de tabela
	 */
	function ajustaTabela($tab){
		// 30/01/18 - Evandro solicitou para utilizar preço atual 1 e margem ideal
		//$sql = "select codprod, pvenda from pctabpr where numregiao = $tab";
		$sql = "select codprod, PVENDA, MARGEM, PTABELA1 from pctabpr where numregiao = $tab";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row[0];
				$val = $row[1];
				if(isset($this->_dados[$prod])){
					$this->_dados[$prod]['tabela'.$tab] = $val;
					
					#Solicitado por compras/Gabriel - 02/08/22
					$this->_dados[$prod]['ptabela1'.$tab] = $row['PTABELA1'];
					
					if($val > 0){
						//Margem Tabela 1
						//$margem = $this->getMargem($prod);
						//$custo = $this->getCusto($prod, $val);
						//$custo = $this->_dados[$prod]['real'];
						if(isset($this->_dados[$prod]['tab'.$tab.'marg'])){
							//$this->_dados[$prod]['tab'.$tab.'marg'] = number_format($this->calculaMargem($val, $margem[2], $margem[3], $margem[0], $custo,$prod), 1, ',', '.');
							//$this->_dados[$prod]['tab'.$tab.'marg'] = number_format($row[2], 1, ',', '.');
						    $this->_dados[$prod]['tab'.$tab.'marg'] = $row[2];
						}
					}
				}
			}
		}
		
	}
	/*
	 * Ajusta valors de promocoes
	 */
	function ajustaListas(){
		if(count($this->_promo) > 0){
			foreach ($this->_promo as $i => $promo){
				$sql = "select codprod, MAX(precofixopromocaomed) PRECO, MIN(PERCDESC) DESCONTO from PCDESCONTO where CODPROMOCAOMED = $promo group by codprod";
				$rows = query4($sql);
				if(count($rows) > 0){
					foreach ($rows as $row){
						$prod = $row[0];
						if(isset($this->_dados[$prod])){
							$val = $row['PRECO'];
							$desconto = $row['DESCONTO'];
							if($val > 0 || $desconto == 0){
								$this->_dados[$prod]['tab'.$i] = $val;
							}else{
								$precoTabela = $this->getPrecoTabela($prod, $this->_ufPromo[$i]);
								$this->_dados[$prod]['tab'.$i] = $precoTabela - round(($precoTabela * $desconto)/100, 2);
							}
						}
					}
				}
			}
		}
		
	}
	
	private function getPrecoTabela($prod, $uf){
		if(count($this->_precosTab) == 0){
			$sql = "SELECT CODPROD, NUMREGIAO, PVENDA FROM PCTABPR WHERE NUMREGIAO IN (1,2)";
			$rows = query4($sql);
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row) {
					$this->_precosTab[$row['CODPROD']][$row['NUMREGIAO']] = $row['PVENDA'];
				}
			}
		}
		
		return $this->_precosTab[$prod][$uf];
	}
	
	/*
	 * Verifica a quantidade máxima de faixas existentes na tabela 9797
	 */
	function getNumeroFaixas($promo){
		$sql = "
				select max(numero)
				from (
				        select codprod, count(*) numero
				        from (
				                select codprod, iniciointervalopromocaomed, max(precofixopromocaomed) preco
				                from PCDESCONTO
				                where PCDESCONTO.codpromocaomed = $promo 
				                group by codprod, iniciointervalopromocaomed
				                ) 
				        GROUP by codprod)
				";
		$rows = query4($sql);
		
		return $rows[0][0];
		
	}
	
	function getMargem($produto){
		if(!isset($this->_margem[$produto])){
			$sql = "
			SELECT PCTABPR.CODPROD
			, NVL(PCPRODUT.PCOMREP1, 0) PCOMREP1
			, DECODE(DECODE(PCFILIAL.UF, NVL(PCREGIAO.UF, PCFILIAL.UF), 1, 0), 1, NVL(EST.CUSTOREAL, 0), NVL(EST.CUSTOREALSEMST, NVL(EST.CUSTOREAL, 0))) CUSTOREAL
			, NVL(PCTRIBUT.CODICMTAB, 0) CODICMTAB
			, NVL(PCREGIAO.PERFRETETERCEIROS, 0) PERFRETETERCEIROS
			FROM PCREGIAO
			, PCTABPR
			, PCTRIBUT
			, PCEST EST
			, PCPRODUT
			, PCFILIAL
			, PCFORNEC
			, PCPRODFILIAL
			, PCCONSUM
			WHERE ((PCREGIAO.STATUS NOT IN ('I')) OR (PCREGIAO.STATUS IS NULL))
			AND PCTABPR.CODPROD     = PCPRODUT.CODPROD
			AND PCTABPR.NUMREGIAO   = PCREGIAO.NUMREGIAO
			AND PCTABPR.CODPROD   = $produto
			AND PCPRODUT.CODFORNEC  = PCFORNEC.CODFORNEC
			AND PCPRODFILIAL.CODPROD   = EST.CODPROD
			AND PCPRODFILIAL.CODFILIAL = EST.CODFILIAL
			AND EST.CODFILIAL     = PCFILIAL.CODIGO
			AND PCTABPR.CODPROD     = EST.CODPROD
			AND EST.CODFILIAL     = 1
			AND PCTABPR.CODST      = PCTRIBUT.CODST
			AND PCTABPR.NUMREGIAO IN(1)
			ORDER BY PCTABPR.NUMREGIAO
			";
			//echo "$sql <br>\n";
			$rows = query4($sql);
			$this->_margem[$produto] = array(
					$rows[0]['PCOMREP1'],
					$rows[0]['CUSTOREAL'],
					$rows[0]['CODICMTAB'],
					$rows[0]['PERFRETETERCEIROS'],
			);
		}
	//if($produto == 56)print_r($this->_margem[$produto])	;
		return $this->_margem[$produto];
	}
	
	function calculaMargem($preco,$imposto,$frete,$comissao,$custo,$produto){
		
		//echo "Preco: $preco Impostos: $imposto  Frete: $frete  Comissao: $comissao  Custo: $custo  Produto: $produto \n";
		$margem = 0;
		if($preco > 0){
			$imp = ($preco * $imposto)/100;
			$fre = ($preco * $frete)/100;
			$com = ($preco * $comissao)/100;
				
			$cmv = $custo + $imp + $fre + $com;
			//echo "CMV: $cmv \n";
			$margem = ($preco - $cmv)/$preco*100;
		}
		/*/
		if($produto == 56){
			echo "Preco: $preco Impostos: $imposto  Frete: $frete  Comissao: $comissao  Custo: $custo  Produto: $produto \n";
			echo "CMV: $custo + $imp + $fre + $com;";
			echo "CMV: $cmv \n";
			echo "Margem: $margem \n";
			die();
		}
		/*/
		//echo "Margem: $margem \n";
		return $margem;
	}
	
}