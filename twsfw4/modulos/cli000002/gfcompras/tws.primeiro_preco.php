<?php
/*
 * Data Criacao: 21/10/2019
 * Autor: TWS - Thiel
 *
 * Descricao: Programa para precificação de novos produtos. Definido por: Adriano
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class primeiro_preco{
	var $funcoes_publicas = array(
			'index' 		=> true,
			'margem'		=> true,
			'gravarMargem'	=> true,
			'precifica'		=> true,
			'calcula_ajax'	=> true,
			'gravarPrecos'	=> true,
	);
	
	//Programa
	private $_programa;
	
	//Titulo
	private $_titulo;
	
	//Tabelas de preços
	private $_tabelas = [];
	
	//Quantidade máxima de dias que o produto possa ter sido cadastrado
	private $_diasCadastro = 30;
	
	//Produtos a serem precificados
	private $_produtos = [];
	
	//Comissao padrão dos produtos
	private $_comissoes = [];
	
	function __construct(){
		formbase01::setLayout('basico');
		$this->_programa = 'primeiro_preco';
		$this->_titulo = 'Precificação Novos Produtos';
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => getEmp(), 'fil' => '', 'ordem' => '1', 'pergunta' => 'Lista'	, 'variavel' => 'TIPO'	,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'pefin=PEFIN;dias4=4 dias;dias7=7 a 30 dias;dias30=Acima 30 dias'));
	}
	
	function index(){
		$ret = '';
		
		$conteudo = $this->getFormProdutos();
		$ret .= $this->nucleo($conteudo);
		
		return $ret;
	}
	
	function margem(){
		$ret = '';
		
		$conteudo = $this->getTabelaMargens();
		$param = [];
		$param['cancelar'] = true;
		$param['margem'] = false;
		$param['salvar'] = 'formMargem';
		$ret .= $this->nucleo($conteudo, $param);
		
		return $ret;
	}
	
	function gravarMargem(){
		$campos = getParam($_POST, 'campoTabela');
		if(is_array($campos) && count($campos) > 0){
			foreach ($campos as $tab => $valores){
				$itens = [];
				$itens['margem'] = ajustaValor($valores['margem']);
				$itens['comissao'] = ajustaValor($valores['comissao']);
				
				$sql = montaSQL($itens, 'gf_precificacao01', 'UPDATE', 'tabela = '.$tab);
				query($sql);
			}
			addPortalMensagem('Valores atualizados!');
		}else{
			addPortalMensagem('Não foram encontrados itens a gravar!','error');
		}
		
		$ret = $this->index();
		
		return $ret;
	}
	
	function precifica(){
		$ret = '';
		$produtos = getParam($_POST, 'formProdutos');
		$produtos = str_replace(',', ';', $produtos);
		$produtos = str_replace("\r\n", ';', $produtos);
		$produtos = str_replace("\n\r", ';', $produtos);
		$produtos = str_replace("\n", ';', $produtos);
		$produtos = str_replace("\r", ';', $produtos);
		
		$verifica = $this->verificaNovos($produtos);
		
		if(count($this->_produtos) > 0){
			$conteudo = $this->getTabelaPrecos();
			$param = [];
			$param['cancelar'] = true;
			$param['margem'] = false;
			$param['salvar'] = 'formPreco';
			$ret .= $this->nucleo($conteudo, $param);
		}else{
			$ret .= $this->index();
		}
		
		
		return $ret;
	}
	
	function calcula_ajax(){
		$ret = '';
		$GLOBALS['tws_pag'] = array(
				'header'   	=> false,
				'html'		=> false,
				'menu'   	=> false,
				'content' 	=> false,
				'footer'   	=> false,
		);
		
		$tabela = getParam($_GET, 'tabela');
		$produto = getParam($_GET, 'produto');
		
		$margem = getParam($_GET, 'margem');
		$comissao = getParam($_GET, 'comissao');
		
		$ret = $this->calculaPreco($produto, $tabela, $margem, $comissao);
		
		$ret = '<b>'.formataReais($ret).'</br>';
		
		return $ret;
	}
	
	function gravarPrecos(){
		$tabela = getParam($_POST, 'campoTabela');
		if(is_array($tabela) && count($tabela) > 0){
			foreach ($tabela as $produto => $p){
				foreach ($p as $t => $d){
					$margem = ajustaValor($d['margem']);
					$comissao = ajustaValor($d['comissao']);
					$preco = $this->calculaPreco($produto, $t, $margem, $comissao);
					
					$sql = "SELECT CODPROD, NUMREGIAO, PTABELA FROM PCTABPR WHERE CODPROD = $produto AND NUMREGIAO = $t";
//echo "$sql \n";
					$rows = query4($sql);
					$campos = [];
					if(isset($rows[0]['CODPROD'])){
						$campos['PTABELA'] = $preco;
						$campos['PTABELA1'] = $preco;
						$campos['PTABELA2'] = $preco;
						$campos['PTABELA3'] = $preco;
						$campos['PTABELA4'] = $preco;
						$campos['PTABELA5'] = $preco;
						$campos['PTABELA6'] = $preco;
						$campos['PTABELA7'] = $preco;
						
						$sql = montaSQL($campos, 'PCTABPR', 'UPDATE', "codprod = $produto AND numregiao = $t");
						
						//echo "$sql \n";
						$rows = query4($sql);
						
					}else{
						//Não existe tabela de preço
						$campos['NUMREGIAO'] = $t;
						$campos['CODPROD'] = $produto;
						$campos['PTABELA'] = $preco;
						$campos['PTABELA1'] = $preco;
						$campos['PTABELA2'] = $preco;
						$campos['PTABELA3'] = $preco;
						$campos['PTABELA4'] = $preco;
						$campos['PTABELA5'] = $preco;
						$campos['PTABELA6'] = $preco;
						$campos['PTABELA7'] = $preco;
						
						$sql = montaSQL($campos, 'PCTABPR');
						
						//echo "$sql \n";
						$rows = query4($sql);
						
					}
				}
				addPortalMensagem('Atualizado: Produto '.$produto);
			}
		}
		
		return $this->index();
	}
	
	//-----------------------------------------------------------------------------------------------
	
	private function nucleo($conteudo, $paramFunc = []){
		$ret = '';
		
		$cancelar = verificaParametro($paramFunc, 'cancelar', false);
		$margem = verificaParametro($paramFunc, 'margem', true);
		$salvar = verificaParametro($paramFunc, 'salvar', false);
		
		$param = [];
		
		if($salvar){
			$botao = [];
			$botao["onclick"]	= "$('#".$salvar."').submit()";
			$botao['tamanho'] = 'pequeno';
			$botao['cor'] = 'success';
			$botao['texto'] = 'Gravar';
			$param['botoesTitulo'][] = $botao;
		}
		if($margem){
			$botao = [];
			$botao['onclick'] = "setLocation('".getLink()."margem')";
			$botao['tamanho'] = 'pequeno';
			$botao['cor'] = 'info';
			$botao['texto'] = 'Margem/Comissão';
			$param['botoesTitulo'][] = $botao;
		}
		if($cancelar){
			$botao = [];
			$botao['onclick'] = "setLocation('".getLink()."index')";
			$botao['tamanho'] = 'pequeno';
			$botao['cor'] = 'danger';
			$botao['texto'] = 'Cancelar';
			$param['botoesTitulo'][] = $botao;
		}
		
		$param['titulo'] 	= $this->_titulo;
		$param['conteudo']	= $conteudo;
		$ret = addCard($param);
		
		return $ret;
	}
	
	private function getFormProdutos(){
		global $nl;
		$ret = '';
		
		$param = [];
		$param['nome'] = 'formProdutos';
		$param['etiqueta'] = 'Produtos a Precificar';
		$produtos = formbase01::formTextArea($param);
		
		$produtos .= formbase01::formSend();
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-3"></div>'.$nl;
		$ret .= '	<div  class="col-md-5">'.$produtos.'</div>'.$nl;
		$ret .= '	<div  class="col-md-4"></div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = [];
		$param['acao'] = getLink().'precifica';
		$param['nome'] = 'formPreco';
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function getTabelaPrecos(){
		$ret = '';
		$this->geraScriptCalculo();
		$this->getTabelas();
		$tabelas = [];
		if(count($this->_tabelas) > 0){
			foreach ($this->_tabelas as $t => $tab){
				$temp = [];
				$temp['tabela'] 	= $t;
				$temp['nome'] 		= $tab['nome'];
				$temp['margem'] 	= $tab['margem'];
				$temp['comissao'] 	= $tab['comissao'];
				
				$tabelas[] = $temp;
			}
		}
//print_r($tabelas);
		
		$param = [];
		$param['paginacao'] = false;
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['filtro'] = false;
		$param['info'] = false;
		//$param['scroll'] = false;
		//$param['scrollX'] = false;
		//$param['scrollY'] = false;
		$tab = new tabela01($param);
		//
		//$browse->setImpLinha(1);
		
		//Monta dados
		$dados = [];
		foreach ($this->_produtos as $produto){
			$temp = [];
			$temp['prod'] 		= $produto['prod'];
			$temp['descicao'] 	= $produto['descicao'];
			$temp['fornecedor'] = $produto['fornecedor'];
			foreach ($tabelas as $tabela){
				$tabP = $tabela['tabela'];
				$comissao = $tabela['comissao'];
				if($comissao == 0){
					$comissao = $this->getComissao($produto['prod']);
				}
				$temp['margem'.$tabP] = $this->getForm($tabP,'margem', $tabela['margem'], $produto['prod']);
				$temp['comissao'.$tabP] = $this->getForm($tabP,'comissao', $comissao, $produto['prod']);
				$preco = $this->calculaPreco($produto['prod'], $tabP, $tabela['margem'], $comissao);
				$temp['preco'.$tabP] = '<div id="preco_'.$tabP.'_'.$produto['prod'].'"><b>'.formataReais($preco).'</b> </div>';
			}
			$dados[] = $temp;
		}
		
		$tab->setDados($dados);
		$tab->addColuna(array('campo' => 'prod'			, 'etiqueta' => 'Cod'			, 'width' => 100, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'descicao'		, 'etiqueta' => 'Produto'		, 'width' => 200, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor'	, 'width' => 100, 'posicao' => 'E'));
		
		foreach ($tabelas as $tabela){
			$tabP = $tabela['tabela'];
			$nome = str_replace('TABELA ', '', $tabela['nome']);
			$tab->addColuna(array('campo' => 'margem'.$tabP		, 'etiqueta' => 'Margem<br>Tabela '.$tabP.'<br>'.$nome 		, 'width' => 100, 'posicao' => 'D'));
			$tab->addColuna(array('campo' => 'comissao'.$tabP	, 'etiqueta' => 'Comissão<br>Tabela '.$tabP.'<br>'.$nome 	, 'width' => 100, 'posicao' => 'D'));
			$tab->addColuna(array('campo' => 'preco'.$tabP		, 'etiqueta' => 'Preço<br>Tabela '.$tabP.'<br>'.$nome 		, 'width' => 100, 'posicao' => 'D'));
		}
		
		
		$ret .= $tab;
		
		$param = [];
		$param['acao'] = getLink().'gravarPrecos';
		$param['nome'] = 'formPreco';
		//$param['onsubmit'] = 'verificaForm';
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function getTabelaMargens(){
		$ret = '';
		$this->getTabelas();
		$tabelas = [];
		if(count($this->_tabelas) > 0){
			foreach ($this->_tabelas as $t => $tab){
				$temp = [];
				$temp['tabela'] 	= $t;
				$temp['nome'] 		= $tab['nome'];
				$temp['margem'] 	= $this->getForm($t,'margem', $tab['margem']);
				$temp['comissao'] 	= $this->getForm($t,'comissao', $tab['comissao']);
				
				$tabelas[] = $temp;
			}
		}
		
		$param = [];
		$param['paginacao'] = false;
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['filtro'] = false;
		$param['info'] = false;
		$param['scroll'] = false;
		$param['scrollX'] = false;
		$param['scrollY'] = false;
		$tab = new tabela01($param);
		$tab->setDados($tabelas);
		//$browse->setImpLinha(1);
		
		$tab->addColuna(array('campo' => 'tabela'	, 'etiqueta' => 'Tabela'		, 'width' => 100, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Região'		, 'width' => 200, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'margem'	, 'etiqueta' => 'Margem'		, 'width' => 100, 'posicao' => 'D'));
		$tab->addColuna(array('campo' => 'comissao'	, 'etiqueta' => 'Comissão'		, 'width' => 100, 'posicao' => 'D'));
		
		$ret .= $tab;
		
		$param = [];
		$param['acao'] = getLink().'gravarMargem';
		$param['nome'] = 'formMargem';
		//$param['onsubmit'] = 'verificaForm';
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function getForm($tabela,$campo,$valor, $produto = 0){
		$ret = '';
		
		$param = [];
		if($produto == 0){
			$param['nome'] = "campoTabela[$tabela][$campo]";
		}else{
			$param['nome'] = "campoTabela[$produto][$tabela][$campo]";
			$param['onchange'] = "alteraPreco($tabela, $produto);";
		}
		$param['style'] = "text-align: right";
		$param['valor'] = $valor;
		$param['maxtamanho'] = 14;
		$param['tamanho'] = 10;
		//$param['sizing'] = '';
		$param['mascara'] = 'V';
		$ret .= formbase01::formTexto($param);
		
		return $ret;
	}
	
	private function getTabelas(){
		$sql = "SELECT NUMREGIAO, REGIAO FROM PCREGIAO WHERE STATUS = 'A' AND NUMREGIAO NOT IN (41,51) ORDER BY NUMREGIAO";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$tabela = $row['NUMREGIAO'];
				$margem = $this->getMargemTabela($tabela);
				
				$temp = [];
				$temp['nome'] = $row['REGIAO'];
				$temp['margem'] = $margem['margem'];
				$temp['comissao'] = $margem['comissao'];
				
				$this->_tabelas[$tabela] = $temp;
			}
		}
	}
	
	private function getMargemTabela($tabela){
		$ret = [];
		$sql = "SELECT * FROM gf_precificacao01 WHERE tabela = $tabela";
		$rows = query($sql);
		if(isset($rows[0]['tabela'])){
			$ret['margem'] = $rows[0]['margem'];
			$ret['comissao'] = $rows[0]['comissao'];
		}else{
			$campos = [];
			$campos['tabela'] = $tabela;
			$campos['margem'] = 0;
			$campos['comissao'] = 0;
			$sql = montaSQL($campos, 'gf_precificacao01');
			query($sql);
			
			$ret = $this->getMargemTabela($tabela);
		}
		
		return $ret;
	}
	
	private function calculaPreco($produto, $tabela, $margem, $comissao){
		$ret = 0;
		$sql = "
                SELECT 
                    (GF_CUSTOREPST(PCTABPR.CODPROD) * (1 - (NVL(PCTRIBUT.PERDESCCUSTO,0)/100)) / 
            		( 1 - ((PCTRIBUT.CODICMTAB /100) + (PCREGIAO.PERFRETETERCEIROS /100) + ($comissao /100) + ($margem /100)))
       				) PRECO
                FROM 
                    PCTABPR,
                    PCREGIAO,
                    PCTRIBUT,
                    PCPRODUT
                WHERE 
                    PCTABPR.CODST = PCTRIBUT.CODST 
                    AND ((PCREGIAO.STATUS NOT IN ('I')) OR (PCREGIAO.STATUS IS NULL))
                    AND PCTABPR.NUMREGIAO   = PCREGIAO.NUMREGIAO  
                    AND PCTABPR.CODPROD = PCPRODUT.CODPROD
                    AND PCTABPR.CODPROD IN (SELECT PCPRODUT.CODPROD FROM PCPRODUT WHERE PCPRODUT.REVENDA = 'S' AND PCPRODUT.DTEXCLUSAO IS NULL )
                    AND PCTABPR.CODPROD = $produto
                    AND PCTABPR.NUMREGIAO = $tabela
                ORDER BY
                    PCTABPR.CODPROD
				";
		$rows = query4($sql);
		if(isset($rows[0]['PRECO'])){
			$ret = round($rows[0]['PRECO'], 2);
		}
		
		return $ret;
	}
	
	/**
	 * Verifica se os produtos indicados foram cadastrados a menos de x dias
	 * @param string $produtos lista de produtos a serem verificados
	 */
	private function verificaNovos($produtos){
		$produtosFora = [];
		$ret = true;
		$produtos = str_replace(';', ',', $produtos);
		
		$listaProd = explode(',', $produtos);
		$tempProd = [];
		foreach ($listaProd as $prod){
			if(!empty(trim($prod))){
				$tempProd[] = $prod;
			}
		}
		
		$produtos = implode(',', $tempProd);
		
		$sql = "SELECT CODPROD, DESCRICAO, PCFORNEC.FORNECEDOR, (SYSDATE - PCPRODUT.DTCADASTRO) DIAS  FROM PCPRODUT, PCFORNEC WHERE PCFORNEC.CODFORNEC = PCPRODUT.CODFORNEC AND CODPROD IN ($produtos)";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$prod = $row['CODPROD'];
				if($row['DIAS'] <= $this->_diasCadastro){
					$temp = [];
					$temp['prod'] = $prod;
					$temp['descicao'] = $row['DESCRICAO'];
					$temp['fornecedor'] = $row['FORNECEDOR'];
					
					$this->_produtos[] = $temp;
				}else{
					$ret = false;
					$produtosFora[] = $prod;
				}
			}
		}else{
			$ret = false;
		}
		
		if(count($produtosFora) > 0){
			addPortalMensagem('Os seguintes produtos tem mais de '.$this->_diasCadastro.' dias de cadastro: '.implode(',', $produtosFora),'error');
		}
		
		return $ret;
	}
	
	private function getComissao($produto){
		$ret = 0;
		if(!isset($this->_comissoes[$produto])){
			$sql = "SELECT PCOMREP1 FROM PCPRODUT WHERE CODPROD = $produto";
			$rows = query4($sql);
			if(isset($rows[0]['PCOMREP1'])){
				$this->_comissoes[$produto] = $rows[0]['PCOMREP1'];
			}
		}
		$ret = $this->_comissoes[$produto];
		
		return $ret;
	}
	private function geraScriptCalculo(){
		$url = getLink().'calcula_ajax';
		$js = "
				function alteraPreco(tabela, produto){
					var campoMargem = 'campoTabela' + produto + tabela + 'margem';
					var campoComissao = 'campoTabela' + produto + tabela + 'comissao';

					var Smargem = $('#'+campoMargem).val();
					Smargem = Smargem.replace('.', '');
					Smargem = Smargem.replace(',', '.');
					var margem = parseInt(Smargem);

					var Scomissao = $('#'+campoComissao).val();
					Scomissao = Scomissao.replace('.', '');
					Scomissao = Scomissao.replace(',', '.');
					var comissao = parseInt(Scomissao);

					$.ajax(
						{
							url: '".$url."&tabela=' + tabela + '&produto=' + produto + '&margem=' + margem + '&comissao=' + comissao, 
							success: function(result){
								$('#preco_'+tabela+'_'+produto).html(result);
							}
						}
					);
				}
				";
		addPortaljavaScript($js);
		
	}
}