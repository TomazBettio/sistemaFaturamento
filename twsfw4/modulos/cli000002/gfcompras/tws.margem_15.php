<?php
/*
 * Data Criacao 19/09/19
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: 	Relatório solicitado pelo Neto.
 * 				Tem a finalidade de verificar quais produtos estão abaixo da margem de 15% em todas as tabelas de preços
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class margem_15{
	var $_relatorio;
	
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	// Nome do Programa
	private $_programa = '';
	
	//Indica que se é teste (utiliza banco teste)
	private $_teste;
	
	//Dados 
	private $_dados;
	
	//Tabelas com preços inferiores
	private $_tabelas = array();
	
	//Descrição das tabelas de preços
	private $_nomeTabelas = array();
	
	//Produtos que não devem aparecer
	private $_produtosFora = '';
	
	//Compradores
	private $_compradores = [];
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_produtosFora = '2016,23959,23960,26743';
		
		$this->_programa = 'margem_15';
		
		$param = [];
		$param['programa']  = $this->_programa;
		$param['titulo']	= 'Verificação Margem < 15%';
		$this->_relatorio = new relatorio01($param);
		
		$param = [];
		$param['imprimeZero'] = false;
		$this->_relatorio->setParamTabela($param);
		
	}
	
	function getEstrutura(){
		$this->_relatorio->addColuna(array('campo' => 'codprod'		, 'etiqueta' => 'CodProd'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'produto'  	, 'etiqueta' => 'Produto'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'comprador'  	, 'etiqueta' => 'Comprador'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		
		if(count($this->_tabelas) > 0){
			foreach ($this->_tabelas as $tabela){
				$nome = 'Tabela '.$tabela.'<br>'.$this->_nomeTabelas[$tabela];
				$this->_relatorio->addColuna(array('campo' => 'tabela'.$tabela	, 'etiqueta' => $nome	, 'tipo' => 'V', 'width' =>  90, 'posicao' => 'D'));
			}
		}
	}
	
	function index(){
		$ret = '';
		
		//$filtro = $this->_relatorio->getFiltro();
		//$tipo = $filtro['TIPO'];
		
			$this->getNomeTabelas();
			$this->getDados();
			asort($this->_tabelas);
			$this->getEstrutura();
			
			$dadosTemp = array();
			foreach ($this->_dados as $produto => $d1){
				foreach ($d1 as $tabela => $d2){
					if(isset($dadosTemp[$produto])){
						$dadosTemp[$produto]['tabela'.$tabela] = $d2;
					}else{
						$temp = array();
						$temp['codprod'] = $produto;
						$temp['produto'] = $this->getProduto($produto);
						$temp['comprador'] = isset($this->_compradores[$produto]) ? $this->_compradores[$produto] : '';
						$temp['tabela'.$tabela] = $d2;
						
						$dadosTemp[$produto] = $temp;
					}
				}
			}
			
			$dados = array();
			foreach ($dadosTemp as $d){
				$dados[] = $d;
			}
			unset($dadosTemp);
			
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
//		}else{
//			$this->getEstrutura();
//		}
		
		$ret .= $this->_relatorio;
		return $ret;
	}
	
	function schedule($param){
		$emails = str_replace(',', ';', $param);
		log::gravaLog('margem_15', 'Executando rotina');
		
		$this->_relatorio->setTitulo('Verificação Margem < 15%');
		$titulo = 'Verificação Margem < 15%';
		
		$this->getNomeTabelas();
		$this->getDados();
		asort($this->_tabelas);
		$this->getEstrutura();
echo "1<br> \n";
		$dadosTemp = array();
		foreach ($this->_dados as $produto => $d1){
			foreach ($d1 as $tabela => $d2){
				if(isset($dadosTemp[$produto])){
					$dadosTemp[$produto]['tabela'.$tabela] = $d2;
				}else{
					$temp = array();
					$temp['codprod'] = $produto;
					$temp['produto'] = $this->getProduto($produto);
					$temp['comprador'] = isset($this->_compradores[$produto]) ? $this->_compradores[$produto] : '';
					$temp['tabela'.$tabela] = $d2;
					
					$dadosTemp[$produto] = $temp;
				}
			}
		}
echo "2<br> \n";
		$dados = array();
		foreach ($dadosTemp as $d){
			$dados[] = $d;
		}
		unset($dadosTemp);
echo "3<br> \n";
		$this->_relatorio->setDados($dados);
		$this->_relatorio->setToExcel(true);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setEnviaTabelaEmail(false);
echo "4<br> \n";
		if($this->_teste){
			$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			echo "Enviado email teste <br> \n";
			log::gravaLog('margem_15', 'Enviado email teste');
		}else{
			$this->_relatorio->agendaEmail('', '08:00', $this->_programa, $emails,$titulo);
			echo "Agendado email <br> \n";
			log::gravaLog('margem_15', 'Agendado email: '.$emails);
		}
	}
	
	private function getDados(){
		$sql = "
				SELECT 
				    PCTABPR.NUMREGIAO,
				    PCTABPR.CODPROD,
					PCEMPR.NOME,
				    PTABELA1,
				    (GF_CUSTOREPST(PCTABPR.CODPROD) * (1 - (NVL(PCTRIBUT.PERDESCCUSTO,0)/100)) / 
				            ( 1 - ((PCTRIBUT.CODICMTAB /100) + (PCREGIAO.PERFRETETERCEIROS /100) + (15 /100)))
				       ) PRECO_15
				FROM 
				    PCTABPR,
				    PCREGIAO,
				    PCTRIBUT,
				    PCPRODUT, 
                    PCEMPR, 
                    PCFORNEC
				WHERE 
				    PCTABPR.CODST = PCTRIBUT.CODST 
				    AND ((PCREGIAO.STATUS NOT IN ('I')) OR (PCREGIAO.STATUS IS NULL))
				    AND PCTABPR.NUMREGIAO   = PCREGIAO.NUMREGIAO  
				    AND PCTABPR.CODPROD = PCPRODUT.CODPROD
					AND PCTABPR.CODPROD IN (SELECT PCPRODUT.CODPROD FROM PCPRODUT WHERE PCPRODUT.REVENDA = 'S' AND PCPRODUT.DTEXCLUSAO IS NULL )
					AND PCTABPR.CODPROD NOT IN (".$this->_produtosFora.")
					AND PCFORNEC.CODFORNEC = pcprodut.CODFORNEC
                    AND PCFORNEC.CODCOMPRADOR = PCEMPR.MATRICULA(+)
				ORDER BY
					PCTABPR.CODPROD
				";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$preco = $row['PTABELA1'];
				$preco15 = $row['PRECO_15'];
				if(!isset($this->_compradores[$row['CODPROD']])){
					$this->_compradores[$row['CODPROD']] = $row['NOME'];
				}
				
				if(round($preco,2) < round($preco15,2) && $preco15 > 0){
					$margem = round((($preco * 0.15) / $preco15) * 100,2);
					$this->_dados[$row['CODPROD']][$row['NUMREGIAO']] = $margem;
					$this->_tabelas[$row['NUMREGIAO']] = $row['NUMREGIAO'];
				}
			}
		}
	}

	private function getProduto($produto){
		$ret = '';
		$sql = "SELECT DESCRICAO FROM PCPRODUT WHERE CODPROD = $produto";
		$rows = query4($sql);
		if(isset($rows[0]['DESCRICAO'])){
			$ret = $rows[0]['DESCRICAO'];
		}
		
		return $ret;
	}
	
	private function getNomeTabelas(){
		$sql = "SELECT NUMREGIAO, REGIAO FROM PCREGIAO";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_nomeTabelas[$row['NUMREGIAO']] = $row['REGIAO'];
			}
		}
	}
}