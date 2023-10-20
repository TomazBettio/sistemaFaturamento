<?php
/*
* Data Criação: 23/09/2014 - 22:25:29
* Autor: Thiel
*
* Arquivo: tws.listastmulti.inc.php
* 
* Baseado no programa ora_listaST mas para m�ltiplas tabelas
* 
* 
* Alterções:
*           18/10/2018 - Emanuel - Migração para intranet2
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class listastmulti{
	private $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	private $_programa = '';
	
	//Dados
	private $_dados;

	//Clientes padrões
	private $_clientePadrao;
	
	private $_teste = true;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_clientePadrao[4] = 17946;

		$this->_programa = 'listastmulti';
		$this->_relatorio = new relatorio01(array('programa' => $this->_programa));
		
		//sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Cod.Promocao'		, 'variavel' => 'PROMO' , 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Tabela Preco'		, 'variavel' => 'TABELA', 'tipo' => 'T', 'tamanho' => '40', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'UF DESTINO'			, 'variavel' => 'UF'	, 'tipo' => 'T', 'tamanho' => '2', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'RS=RS;SC=SC;PR=PR'));
	}			

	private function geraColunas($promo = ''){
		$this->_relatorio->addColuna(array('campo' => 'cod'			, 'etiqueta' => 'Cod'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'descri'		, 'etiqueta' => 'Produto'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'depto'		, 'etiqueta' => 'Depto'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'pmpf'		, 'etiqueta' => 'Usa PMPF'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'preco_pmpf'	, 'etiqueta' => 'PMPF'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		if(!empty($promo)){
			$this->_relatorio->addColuna(array('campo' => 'promonome'	, 'etiqueta' => 'Promocao'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
			$this->_relatorio->addColuna(array('campo' => 'fixo'		, 'etiqueta' => 'Preco<br>Fixo'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
			$this->_relatorio->addColuna(array('campo' => 'desconto'	, 'etiqueta' => 'Preco<br>Desconto'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		}
	}
	
	function adicionaColunas($tabelas, $promo){
		foreach ($tabelas as $tab){
			if(empty(trim($promo))){
				$this->_relatorio->addColuna(array('campo' => 'preco'.$tab		, 'etiqueta' => 'Preço<br>Tab '.$tab	, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
			}
			$this->_relatorio->addColuna(array('campo' => 'st'.$tab		, 'etiqueta' => 'ST Tab '.$tab	, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		}
	}
	
	function index(){
		$filtro = $this->_relatorio->getFiltro();
		
		$promo 	= isset($filtro['PROMO']) ? $filtro['PROMO'] : '';
		$tabela = isset($filtro['TABELA']) ? $filtro['TABELA'] : '';
		$tabela = str_replace(';', ',', $tabela);
		$tabelas = explode(',', $tabela);
		$uf		= isset($filtro['UF']) ? $filtro['UF'] : '';
		
		
		$this->_relatorio->setTitulo('Lista Preço ST Multi');
		
		if(!$this->_relatorio->getPrimeira() && count($tabelas) > 0){
			$this->geraColunas($promo);
			$this->adicionaColunas($tabelas, $promo);

			$dados = [];
			if(!empty(trim($promo))){
				foreach ($tabelas as $tab){
					$dados = $this->getDados($promo, $tab, $uf,$tabelas);
				}
			}else{
				$dados = $this->getDados2($tabela, $uf,$tabelas);
			}
//log::gravaLog('ST_Multi',$this->_dados, 0,true);
			if(isset($this->_dados)){
    			foreach ($this->_dados as $produtos){
    				foreach ($produtos as $dado){
    					$dados[] = $dado;
    				}
    			}
			}


			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}else{
			$this->geraColunas();
		}
		return $this->_relatorio . '';
	}

	function schedule($param){
	
	}
	
	function geraItem($dado, $lista, $tabelas){
		$cod = $dado['prod'];
		$promo = isset($dado['promocao']) ? $dado['promocao'] : 0;
		if(!isset($this->_dados[$cod][$promo])){
			$this->_dados[$cod][$promo]['cod']			= $cod;
			$this->_dados[$cod][$promo]['descri']		= $dado['descri'];
			$this->_dados[$cod][$promo]['depto']		= $dado['depto'];
			$this->_dados[$cod][$promo]['promonome']	= $dado['promonome'];
			$this->_dados[$cod][$promo]['fixo']			= $dado['fixo'];
			$this->_dados[$cod][$promo]['pmpf']			= $dado['pmpf']	;
			$this->_dados[$cod][$promo]['preco_pmpf']	= $dado['preco_pmpf'];
			if(isset($dado['desconto'])){
				$this->_dados[$cod][$promo]['desconto']		= $dado['desconto'];
			}
			//if(isset($tabelas)){
			if(is_array($tabelas) && count($tabelas) > 0) {
    			foreach ($tabelas as $tab){
    				$this->_dados[$cod][$promo]['st'.$tab]	= 0;
    			}
			}
		}
		$this->_dados[$cod][$promo]['st'.$lista]	= $dado['st'];
		if(empty($promo)){
			$this->_dados[$cod][$promo]['preco'.$lista]	= $dado['preco'];
		}
//print_r($dado);print_r($lista);print_r($tabelas);print_r($this->_dados);die();
	}

	function getDados($promo, $tabela, $uf,$tabs){
		$ret = array();
		$sql = " 
				 SELECT DISTINCT
				        PCDESCONTO.CODPROD,
				        PCPRODUT.DESCRICAO,
				        PCDEPTO.DESCRICAO DEPARTAMENTO,
				        PCPROMOCAOMED.DESCRICAODETALHADA,
				        PCPROMOCAOMED.TIPOPOLITICA,
				        PCPROMOCAOMED.TIPOPROMOCAO ,
				        PCDESCONTO.PERCDESC,
				        PCDESCONTO.PRECOFIXOPROMOCAOMED PRECO_FIXO,
				        PCTABPR.PTABELA1,
				        PCTABPR.CODST,
				        (SELECT PCTABMEDABCFARMA.PRECOMAXCONSUM FROM PCTABMEDABCFARMA WHERE PCTABPR.CODPROD = PCTABMEDABCFARMA.CODPROD AND PCTABMEDABCFARMA.UF = '$uf'),
				        PCTRIBUT.PERCBASERED,
				        PCTRIBUT.IVAFONTE,
				        PCTRIBUT.ALIQICMS1FONTE,
				        PCTRIBUT.PERCBASEREDSTFONTE,
				        PCTRIBUT.USAPMCBASEST,
				        PCTRIBUT.USABASEICMSREDUZIDA,
				        PCTRIBUT.ALIQICMS2FONTE,
				        PCTRIBUT.VLPAUTASEMIVA,
				        PCTRIBUT.PAUTAFONTE,
						PCPROMOCAOMED.CODPROMOCAOMED,
						PCTRIBUT.USAPMPFBASEST,
						(SELECT PCTABMEDABCFARMA.PMPF FROM PCTABMEDABCFARMA WHERE PCTABPR.CODPROD = PCTABMEDABCFARMA.CODPROD AND PCTABMEDABCFARMA.UF = '$uf') PRECO_PMPF
				FROM PCPROMOCAOMED,
				        PCDESCONTO,
				        PCDEPTO,
				        PCPRODUT,
				        PCTABPR,
				        PCTRIBUT
				 WHERE (PCPROMOCAOMED.CODPROMOCAOMED = PCDESCONTO.CODPROMOCAOMED)
					AND PCDESCONTO.CODPROD IN (SELECT CODPROD FROM PCPRODUT WHERE DTEXCLUSAO IS NULL)
				   AND PCDESCONTO.CODPROD = PCPRODUT.CODPROD (+)
				   AND PCDESCONTO.CODPROD = PCTABPR.CODPROD (+)
				   AND PCTABPR.numregiao in ($tabela)
				   AND pcprodut.codepto = pcdepto.codepto (+)
				   AND PCPROMOCAOMED.CODPROMOCAOMED in ($promo)
				   and pctabpr.codst = pctribut.codst (+)
				 ORDER BY pcprodut.descricao		
		";
		 

		$rows = query4($sql);
// print_r($rows);		
		if(count($rows) > 0){
			$i = 0;
			foreach($rows as $row){
				$ret[$i]['prod'] 		= $row[0];
		        $ret[$i]['descri'] 		= $row[1];
		        $ret[$i]['depto'] 		= $row[2];
		        $ret[$i]['promonome'] 	= $row[3];
		        $ret[$i]['promocao'] 	= $row['CODPROMOCAOMED'];
		        $ret[$i]['politica'] 	= $row[4];
		        $ret[$i]['promo'] 		= $row[5];
		        $ret[$i]['desc'] 		= $row['PERCDESC'];
		        $ret[$i]['fixo'] 		= $row[7];
		        if($row['PERCDESC'] > 0){
		        	$ret[$i]['desconto'] = $row['PTABELA1'] * (1 - ($row['PERCDESC']/100));
		        }else{
		        	$ret[$i]['desconto'] = 0;
		        }
		        $ret[$i]['tabela'] 		= $row[8];
		        $ret[$i]['codst'] 		= $row[9];
		        $ret[$i]['pmc'] 		= $row[10];
		        $ret[$i]['perc_basered']= $row[11]>0 ? ($row[11]/100): 1;
		        $ret[$i]['iva'] 		= $row[12];
		        $ret[$i]['icms'] 		= ($row[13]/100);
		        $ret[$i]['icms2'] 		= ($row[17]/100);
		        $ret[$i]['perc_basest'] = $row[14]>0 ? ($row[14]/100): 1;
		        $ret[$i]['usapmc'] 		= $row[15];
		        $ret[$i]['usabr'] 		= $row[16];
		        $ret[$i]['pmpf'] 		= $row['USAPMPFBASEST'] == 'S' ? 'S' : 'N';
		        $ret[$i]['preco_pmpf'] 	= $row['PRECO_PMPF'];
		        
		        $valPauta = $row[19] ?? 0;
		        
		        $iva = $ret[$i]['iva'] /100;
		        if($uf == 'RS'){
		        	$icms = $ret[$i]['icms'];
		        }else{
		        	$icms = $ret[$i]['icms2'];
		        }
		        
		        if($ret[$i]['desc'] > 0){
		        	$preco = $ret[$i]['tabela'] * (1 - ($ret[$i]['desc']/100));
//echo "Preço desconto: ".$preco."<br>\n";
		        }else{
		        	$preco = $ret[$i]['fixo'];
//echo "Preço Fixo: ".$preco."<br>\n";
		        }
		        $precoCalcST = $preco;
		        
		        $ret[$i]['st']	 		= 0;
		        
		        if($ret[$i]['pmpf'] == 'S' && $ret[$i]['preco_pmpf'] > 0){
		        	$preco1 = $ret[$i]['preco_pmpf'];
		        	//echo "<br>\nProduto: ".$ret[$i]['prod']." UF: $uf PMPF: $preco1 <br>\n";
		        }elseif($ret[$i]['usapmc'] == 'S'){
		        	//PMC
		        	$preco1 = $ret[$i]['pmc'];
		        }else{
		        	$preco1 = $preco;
		        }
		        
		        $preco2 = $preco;
		        
		        //Usa base icms reduzida
		        if($ret[$i]['usabr'] == 'S'){
		        	$preco2 = $preco2 * $ret[$i]['perc_basered'];
		        }
		        
		        if($valPauta > 0){
		        	$st1 = $valPauta * $ret[$i]['icms'];
		        }else{
		        	$st1 = ((($preco1 * (1+$iva)) * $ret[$i]['perc_basest']) * $ret[$i]['icms']);
		        }
				$st2 = $preco2 * $icms;
				$ret[$i]['st'] = round($st1-$st2,2);
				$st = $ret[$i]['st'];
/*/				
echo $ret[$i]['prod']."<br>\n";
echo "Pauta: ".$valPauta."<br>\n";
echo "Preco1: ".$preco1."<br>\n";
echo "Preco2: ".$preco2."<br>\n";
echo "ST1: ".$st1."<br>\n";
echo "ST1: ".$st1."<br>\n";
echo "ST2: ".$st2."<br>\n";
echo "icms: ".$ret[$i]['icms']."<br>\n";
echo "perc_basest: ".$ret[$i]['perc_basest']."<br>\n";
/*/				
				if(isset($ret[$i]['regiao']) && isset($this->_clientePadrao[$ret[$i]['regiao']])){
					$sql = "SELECT FNC_OBTER_STFONTE_COTACAO('1', ".$ret[$i]['prod'].", ".$this->_clientePadrao[$ret[$i]['regiao']].", $precoCalcST) from dual";
					$rows = query4($sql);
					$st = round($rows[0][0],2);
					$ret[$i]['st'] = $st;
				}
				
				$this->geraItem($ret[$i], $tabela, $tabs);
				$i++;
			}

		}
	}
	function getDados2($tabelas, $uf, $tabs){
		$ret = array();
		$sql = " SELECT DISTINCT
                        PCTABPR.CODPROD,
                        PCPRODUT.DESCRICAO,
                        PCDEPTO.DESCRICAO DEPARTAMENTO,
                        PCTABPR.PTABELA1,
                        PCTABPR.CODST,
                        (SELECT PCTABMEDABCFARMA.PRECOMAXCONSUM FROM PCTABMEDABCFARMA WHERE PCTABPR.CODPROD = PCTABMEDABCFARMA.CODPROD AND PCTABMEDABCFARMA.UF = '$uf') PMC,
                        PCTRIBUT.PERCBASERED,
                        PCTRIBUT.IVAFONTE,
                        PCTRIBUT.ALIQICMS1FONTE,
                        PCTRIBUT.PERCBASEREDSTFONTE,
                        PCTRIBUT.USAPMCBASEST,
                        PCTRIBUT.USABASEICMSREDUZIDA,
                        PCTRIBUT.ALIQICMS2FONTE,
                        PCTRIBUT.VLPAUTASEMIVA,
                        PCTRIBUT.PAUTAFONTE,
                        PCTABPR.MARGEM,
						PCTABPR.NUMREGIAO,
						PCTRIBUT.USAPMPFBASEST,
						(SELECT PCTABMEDABCFARMA.PMPF FROM PCTABMEDABCFARMA WHERE PCTABPR.CODPROD = PCTABMEDABCFARMA.CODPROD AND PCTABMEDABCFARMA.UF = '$uf') PRECO_PMPF,
						0 as campo_19
                FROM 
                        PCDEPTO,
                        PCPRODUT,
                        PCTABPR,
                        PCTRIBUT
                WHERE 
                    	PCTABPR.NUMREGIAO IN ($tabelas)
						AND PCTABPR.CODPROD IN (SELECT CODPROD FROM PCPRODUT WHERE DTEXCLUSAO IS NULL)
                  		AND PCTABPR.CODPROD = PCPRODUT.CODPROD(+)
				  		AND PCDEPTO.CODEPTO IN (1,12)
                  		AND PCPRODUT.CODEPTO = PCDEPTO.CODEPTO (+)
                  		AND PCTABPR.CODST = PCTRIBUT.CODST (+)
                ORDER BY 
						PCPRODUT.DESCRICAO 
		";
		
//echo "$sql <br>\n";	die();
		$rows = query4($sql);
//print_r($rows);
		if(is_array($rows) && count($rows) > 0){
			$i = 0;
			foreach($rows as $row){
				$ret[$i]['prod'] 		= $row['CODPROD'];
				$ret[$i]['descri'] 		= $row['DESCRICAO'];
				$ret[$i]['depto'] 		= $row['DEPARTAMENTO'];
				$ret[$i]['promonome'] 	= '';
				$ret[$i]['fixo'] 		= '';
				$ret[$i]['tabela'] 		= $row['PTABELA1'];
				$ret[$i]['codst'] 		= $row['CODST'];
				$ret[$i]['pmc'] 		= $row['PMC'];
				$ret[$i]['perc_basered']= $row['PERCBASERED']>0 ? ($row['PERCBASERED']/100): 1;
				$ret[$i]['iva'] 		= $row['IVAFONTE'];
				$ret[$i]['icms'] 		= ($row['ALIQICMS1FONTE']/100);
				$ret[$i]['icms2'] 		= ($row['ALIQICMS2FONTE']/100);
				$ret[$i]['perc_basest'] = $row['PERCBASEREDSTFONTE']>0 ? ($row['PERCBASEREDSTFONTE']/100): 1;
				$ret[$i]['usapmc'] 		= $row['USAPMCBASEST'];
				$ret[$i]['usabr'] 		= $row['USABASEICMSREDUZIDA'];
				$ret[$i]['regiao'] 		= $row['NUMREGIAO'];
				$ret[$i]['pmpf'] 		= $row['USAPMPFBASEST'] == 'S' ? 'S' : 'N';
				$ret[$i]['preco_pmpf'] 	= $row['PRECO_PMPF'];
				$lista = $ret[$i]['regiao'];
// print_r($row);
				$valPauta = $row[19];
				
				$iva = $ret[$i]['iva'] /100;
				if($uf == 'RS'){
					$icms = $ret[$i]['icms'];
				}else{
					$icms = $ret[$i]['icms2'];
				}
				
				$preco = $ret[$i]['tabela'];
				$ret[$i]['preco'] = $preco;
				
				$ret[$i]['st']	 		= 0;
				
				if($ret[$i]['pmpf'] == 'S' && $ret[$i]['preco_pmpf'] > 0){
					$preco1 = $ret[$i]['preco_pmpf'];
					//echo "<br>\nProduto: ".$ret[$i]['prod']." UF: $uf PMPF: $preco1 <br>\n";
				}elseif($ret[$i]['usapmc'] == 'S'){
					//PMC
					$preco1 = $ret[$i]['pmc'];
				}else{
					$preco1 = $preco;
				}
				$precoCalcST = $preco1;
				
				
				$preco2 = $preco;
				
				//Usa base icms reduzida
				if($ret[$i]['usabr'] == 'S'){
					$preco2 = $preco2 * $ret[$i]['perc_basered'];
				}
				
				if($valPauta > 0){
					$st1 = $valPauta * $ret[$i]['icms'];
				}else{
					$st1 = ((($preco1 * (1+$iva)) * $ret[$i]['perc_basest']) * $ret[$i]['icms']);
				}
				$st2 = $preco2 * $icms;
				$ret[$i]['st'] = round($st1-$st2,2);
				$st = $ret[$i]['st'];
				
				if(isset($this->_clientePadrao[$ret[$i]['regiao'] ])){
					if($precoCalcST > 0){
						$sql = "SELECT FNC_OBTER_STFONTE_COTACAO('1', ".$ret[$i]['prod'].", ".$this->_clientePadrao[$ret[$i]['regiao']].", ".$precoCalcST.") from dual";
						if($ret[$i]['prod'] == 8623) echo "SQL: $sql <br>\n";
						$rows = query4($sql);
						$st = round($rows[0][0],2);
						$ret[$i]['st'] = $st;
					}else{
						if($ret[$i]['prod'] == 8623) echo "SQL: zero <br>\n";
						$ret[$i]['st'] = 0;
					}
				}
								
				$this->geraItem($ret[$i],$lista,$tabelas);
				$i++;
			}
			
		}
	}
	
	function calculaST($tab, $prod, $preco){
		/*/
		 * SELECT FNC_OBTER_STFONTE_COTACAO('1',
		 19942, -->> produto
		 4787, -->> cliente
		 3.6286) -->> preço
		 from dual
		 
		 
		 */
		$st = 0;
		if($tab > 0 && $tab <32){
			$sql = "SELECT FNC_OBTER_STFONTE_COTACAO('1', $prod, ".$this->_clientePadrao[$tab].",$preco) from dual";
			$rows = query4($sql);
			if(count($rows) > 0){
				$st = round($rows[0][0],2);
			}
		}
		
		return $st;
	}
}