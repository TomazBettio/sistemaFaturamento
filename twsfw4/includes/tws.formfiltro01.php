<?php
/*
 * Data Criacao: 28/02/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Crfia e administra formulários de filtros/parametros
 *
 * Alteracoes;
 * 				28/02/22 - Reescrito para atender a intranet 4
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class formfiltro01{
	
	// Nome dos programas (conjunto de perguntas)
	protected  $_programas = [];
	
	// Perguntas
	protected $_perguntas = [];
	
	// Largura
	protected $_tamanho;
	
	// Indica a posição dos botoes
	protected $_posicaoBotoes;
	
	// Layout dos forms
	protected $_layout;
	
	// Titulo
	protected $_titulo;
	
	// Modelo do painel BootStrap
	protected $_modelo;
	
	// Link para enviar o formul�rio
	protected $_link;
	
	// Nome normalizado
	protected $_nomePrograma;
	
	// Quantidade de colunas
	protected $_colunas;
	
	// Retorno
	protected $_retorno;
	
	// Indica se eh o primeiro carregamento do filtro
	protected $_primeiro;
	
	// Botões no titulo
	protected $_botaoTitulo = [];
	
	//Imprime painel
	protected $_imprimePainel;
	
	//Texto botão
	protected $_botaoEnviarTexto;
	
	//Cor botao enviar
	protected $_botaoEnviarCor;
	
	public function __construct($programas, $parametros){
		if(!is_array($programas)){
			$this->_programas[] = $programas;
		}else{
			$this->_programas = $programas;
		}
		$this->normalizaPrograma();
		
		$this->_tamanho 			= verificaParametro($parametros, 'tamanho', 6);
		$this->_colunas 			= verificaParametro($parametros, 'colunas', 2);
		$this->_posicaoBotoes 		= verificaParametro($parametros, 'posicaoBotoes', FORMFILTRO_BT_POS);
		$this->_layout 				= verificaParametro($parametros, 'layout', 'basico');
		$carragaPerguntas		 	= verificaParametro($parametros, 'carragaPerguntas', true);
		$carragaRespostas 			= verificaParametro($parametros, 'carragaRespostas', true);
		$this->_titulo 				= verificaParametro($parametros, 'titulo', FORMFILTRO_TITULO);
		$this->_modelo 				= verificaParametro($parametros, 'cor', FORMFILTRO_COR);
		$this->_link 				= verificaParametro($parametros, 'link','index.php?'.$_SERVER['QUERY_STRING']);
		
		$this->_imprimePainel		= verificaParametro($parametros, 'imprimePainel',true);
		$this->_botaoEnviarCor		= verificaParametro($parametros, 'botaoCor',FORMFILTRO_BT_COR);
		$this->_botaoEnviarTexto	= verificaParametro($parametros, 'botaoTexto',FORMFILTRO_BT_ENVIAR);
		
		
		if($this->_tamanho > 12) $this->_tamanho = 12;
		if($this->_tamanho < 0)  $this->_tamanho = 4;
		
		if($carragaPerguntas){
			$this->getPerguntas();
		}
		
		/*
		 * Deve carregar as respostas anteriores do usuário?
		 */
		if($carragaRespostas){
			$this->_retorno = $this->getRetornos();
		}
	}
	
	public function __toString(){
		global $nl;
		$ret = '';
		
		formbase01::setLayout('basico');
		if($this->_colunas == 1){
			$tamCol1 = 12;
			$tamCol2 = 0;
		}else{
			$tamCol1 = (int)((12 - $this->_tamanho)/2);
			$tamCol2 = 12 - $tamCol1 - $this->_tamanho;
		}
		
		$parametros = $this->imprimeParametros();
		$cont = $this->imprimeEstrutura($parametros);
		if($this->_imprimePainel === false){
			$filtro = $cont;
		}else{
			$filtro = $this->imprimePainel($cont);
		}
		
		$ret .= '<form id="formFiltro" name="form1" method="post" action="'.$this->_link.'">'.$nl;
		$token = geraStringAleatoria(30);
		putAppVar('token_form_filtro', $token);
		$ret .= '<input name="fwFiltro" type="hidden" value="'.$token.'" />'.$nl;
		
		$ret .= '<div class="row">'.$nl;
		if($tamCol1 > 0 && $tamCol1 != 12){
			$ret .= '	<div class="col-md-'.$tamCol1.'">&nbsp;</div>'.$nl;
		}
		$ret .= '	<div class="col-md-'.$this->_tamanho.'">'.$nl.$filtro.$nl.'</div>'.$nl;
		if($tamCol2 > 0){
			$ret .= '	<div class="col-md-'.$tamCol2.'">&nbsp;</div>'.$nl;
		}
		$ret .= '</div>'.$nl;
		
		$ret .= '</form>'.$nl;
		//echo $ret;
		formbase01::setLayout('');
		
		return $ret;
	}

	
	//----------------------------------------------------------- UI ---------------------------------------------
	
	function imprimeParametros(){
		global $nl;
		$ret = '';
		
		$quant = count($this->_perguntas);
		if($quant == 0){
			$this->getPerguntas();
			$quant = count($this->_perguntas);
		}
		$colunas = $this->_colunas;
		$linhas = ceil($quant / $colunas);
		for($i=0;$i<$linhas;$i++){
			$ret .= $nl.'<div class="row">'.$nl;
			for($e=0;$e<$colunas;$e++){
				$ret .= $this->imprimeColuna($i,$e);
			}
			$ret .= '</div>'.$nl;
		}
		
		return $ret;
	}
	
	protected function imprimePainel($conteudo){
		$ret = '';
		
		$param = [];
		$param['cor'] = $this->_modelo;
		$param['titulo'] = $this->_titulo;
		$param['conteudo'] = $conteudo;
		
		if(count($this->_botaoTitulo) > 0){
			$param['botoesTitulo'] = $this->_botaoTitulo;
		}
		
		$ret = addCard($param);

		return $ret;
	}
	
	protected function imprimeEstrutura($filtro){
		global $nl;
		$ret = $nl;
		
		$param = [];
		$param['bloco'] 	= true;
		$param['texto'] 	= $this->_botaoEnviarTexto;
		$param['type'] 		= 'submit';
		$param['cor'] 		= $this->_botaoEnviarCor;
		//$param['tamanho'] 	= 'pequeno';
		$enviar = formbase01::formBotao($param);
		
		if($this->_posicaoBotoes == 'L'){
			$ret .= '<div class="row">'.$nl;
			$ret .= '	<div class="col-md-2">'.$enviar.'</div>'.$nl;
			$ret .= '	<div class="col-md-10">'.$filtro.'</div>'.$nl;
			$ret .= '</div>'.$nl;
		}elseif($this->_posicaoBotoes == 'I'){
			$ret .= $filtro.$nl;
			$ret .= '<div class="row">'.$nl;
			$ret .= '	<div class="col-md-12">'.$enviar.'</div>'.$nl;
			$ret .= '</div>'.$nl;
		}else{
			$ret .= '<div class="row">'.$nl;
			$ret .= '	<div class="col-md-12">'.$enviar.'</div>'.$nl;
			$ret .= '</div>'.$nl;
			$ret .= $filtro.$nl;
		}
		
		return $ret;
	}
	
	protected function imprimeColuna($i,$pos){
		global $nl;
		$param = [];
		$ret = '';
		$formLayoutAtual = getAppVar('formBase_layout');
		formBase01::setLayout($this->_layout);
		if(count($this->_perguntas) > ($i * $this->_colunas + $pos)){
			$pergunta = $this->_perguntas[$i * $this->_colunas + $pos];
			
			$selecionado = isset($this->_retorno[$pergunta['variavel']]) ? $this->_retorno[$pergunta['variavel']] : '';
			
			if($selecionado == "" && $pergunta['inicializador'] != ""){
				$selecionado = $pergunta['inicializador'];
			}elseif($selecionado == "" && $pergunta['inicFunc'] != ""){
				//@todo: implementar função para inicializar
			}
			if($pergunta['tabela'] != ''){
				$form = $this->montaSelect($pergunta['tabela'], $pergunta['variavel'],$selecionado, $pergunta['tipo']);
			}elseif($pergunta['funcaodados'] != ''){
				$form = $this->montaSelect2($pergunta['funcaodados'], $pergunta['variavel'], $pergunta['tipo'],$selecionado);
			}elseif($pergunta['tipo'] == "D"){
				$form = $this->montaData($pergunta['variavel'],$selecionado);
			}elseif($pergunta['opcoes'] != ""){
				$form = $this->montaSelect2($pergunta['opcoes'], $pergunta['variavel'], $pergunta['tipo'],$selecionado,2);
			}elseif($pergunta['tipo'] == "TA"){
				$param['nome']	= self::getNomeCampo($pergunta['variavel']);
				$param['valor']	= $selecionado;
				$param['id']	= self::getNomeCampo($pergunta['variavel']);
				$param['linhas'] = 5;
				$form = formBase01::formTextArea($param);
			}else{
				$param['nome']	= $this->getNomeCampo($pergunta['variavel']);
				$param['valor']	= $selecionado;
				$param['id']	= $this->getNomeCampo($pergunta['variavel']);
				
				$form = formBase01::formTexto($param);
			}
		}
		$tam = (int)(12/$this->_colunas);
		$ret .= '	<div class="col-md-'.$tam.'">';
		if(isset($pergunta)){
			if(!isset($param['id']) || $param['id'] == ''){
				$param['id'] = $this->getID($pergunta['variavel']);
			}
			$ret .= Formbase01::formLinha($form,$pergunta['pergunta'],$param['id'],$pergunta['help']);
		}else{
			$ret .= '&nbsp;';
		}
		$ret .= '</div>'.$nl;
		
		putAppVar('formBase_layout', $formLayoutAtual);
		
		return $ret;
	}
	
	//----------------------------------------------------------- GET ---------------------------------------------
	protected function getPerguntas(){
		if(count($this->_programas) > 0){
			foreach($this->_programas as $programa){
				if(!empty($programa)){
					$sql = "SELECT * FROM sys004 WHERE programa = '$programa' ORDER BY ordem";
					$rows = query($sql);

					if(count($rows) > 0){
						foreach ($rows as $row){
							$temp = [];
							$temp['programa']		= $row['programa'];
							$temp['ordem']			= $row['ordem'];
							$temp['pergunta']		= $row['pergunta'].":&nbsp;";
							$temp['variavel']		= $row['variavel'];
							$temp['tipo']			= $row['tipo'];
							$temp['tamanho']		= $row['tamanho'];
							$temp['casadec']		= $row['casadec'];
							$temp['validador']		= $row['validador'];
							$temp['tabela']			= $row['tabela'];
							$temp['funcaodados']	= $row['funcaodados'];
							$temp['help']			= $row['help'];
							$temp['inicializador']	= $row['inicializador'];
							$temp['inicFunc']		= $row['inicFunc'];
							$temp['opcoes']			= $row['opcoes'];
							
							$this->_perguntas[] = $temp;
						}
					}
				}
			}
		}else{
			addPortalMensagem('Deve ser indicado ao menos um programa para gerar o formulário de filtros - FormFiltro','danger');
		}
	}
	
	public function getFiltro(){
		return $this->getChaveValor();
	}
	
	protected function getChaveValor(){
		$ret = [];
		if(count($this->_perguntas) > 0){
			foreach ($this->_perguntas as $param){
				if($param['tipo'] == "D"){
					if(isset($this->_retorno[$param['variavel']])){
						if(strpos($this->_retorno[$param['variavel']], '/') !== false){
							$valor = datas::dataD2S($this->_retorno[$param['variavel']]);
						}else{
							$valor = $this->_retorno[$param['variavel']];
						}
					}else{
						$valor = '';
					}
				}else{
					$valor = isset($this->_retorno[$param['variavel']]) ? $this->_retorno[$param['variavel']] : '';
				}
				$ret[$param['variavel']] = $valor;
			}
		}
		$ret['fwFiltro'] = getParam($_POST,'fwFiltro');
		return $ret;
	}
	
	/*
	 * Verifica se existe retorno do formul�rio
	 * Se n�o existir pesquisa e se existir respostas anteriores retorna as mesmas
	 * Se existir retorna as mesmas e grava
	 *
	 */
	protected function getRetornos(){
		$ret = getParam($_POST, $this->_nomePrograma);
		//Para possibilitar utilizar a clase mesmo fora do framework
		if(is_array($ret) && count($ret) > 0){
			$this->_primeiro = false;
			$this->setRespostasBD($ret);
		}else{
			$this->_primeiro = true;
			$ret = $this->getValoresArmazenados();
		}
		
		return $ret;
	}
	
	protected function getValoresArmazenados(){
		$ret = [];
		$usuario = getUsuario();
		
		$sql = "SELECT valor FROM sys044 WHERE programa = '".$this->_programas[0]."' AND usuario = '$usuario' ";
		$rows = query($sql);
		if(count($rows)>0){
			$ret = unserialize($rows[0][0]);
		}
	
		return $ret;
	}
	
	protected function getNomeCampo($campo){
		return $this->_nomePrograma."[".$campo."]";
	}
	
	
	protected function getID($campo){
		return $this->_nomePrograma.$campo;
	}
	
	/**
	 * Retorna se é a primeira execucao do filtro
	 *
	 * @param	void
	 * @return	boolean	Indica se é a primeira execucao
	 */
	
	public function getPrimeira(){
		$ret = true;
		$token = getAppVar('token_form_filtro');
		if(isset($_POST['fwFiltro']) && $_POST['fwFiltro'] == $token)
			$ret = false;
			return $ret;
	}
	
	/**
	 * Retorna uma string com a descrição dos parametros: valor parametro
	 */
	public function getParametrosString($perguntas){
		$ret = '';
		
		foreach ($this->_perguntas as $pergunta){
			if(array_search($pergunta['variavel'], $perguntas) !== false){
				if(!empty($ret)){
					$ret .= ' - ';
				}
				$ret .= '<font style="font-weight: bold;">'.$pergunta['pergunta'].'</font> ';
				if(!empty(trim($pergunta['opcoes']))){
					$ret .= $this->retornaOpcoesSelecionada($this->_retorno[$pergunta['variavel']], $pergunta['opcoes']);
				}else{
					$ret .= $this->_retorno[$pergunta['variavel']];
				}
			}
		}
		return $ret;
	}
	
	public function getQuantPerguntas(){
		$quant = count($this->_perguntas);
		if($quant == 0){
			$this->getPerguntas();
			$quant = count($this->_perguntas);
		}
		return $quant;
	}
	
	public function isPrimeiro(){
		return $this->_primeiro;
	}
	
	
	//--------------------------------------------------------------------------------------------- ADD ---------------------
	
	public function addPergunta($pergunta){
		$i = count($this->_perguntas);
		$this->_perguntas[$i]['programa']		= $pergunta['programa'];
		$this->_perguntas[$i]['ordem']			= $pergunta['ordem'];
		$this->_perguntas[$i]['pergunta']		= $pergunta['pergunta'];
		$this->_perguntas[$i]['variavel']		= $pergunta['variavel'];
		$this->_perguntas[$i]['tipo']			= $pergunta['tipo'];
		$this->_perguntas[$i]['tamanho']		= $pergunta['tamanho'];
		$this->_perguntas[$i]['casadec']		= $pergunta['casadec'];
		$this->_perguntas[$i]['validador']		= $pergunta['validador'];
		$this->_perguntas[$i]['tabela']			= $pergunta['tabela'];
		$this->_perguntas[$i]['funcaodados']	= $pergunta['funcaodados'];
		$this->_perguntas[$i]['help']			= $pergunta['help'];
		$this->_perguntas[$i]['inicializador']	= $pergunta['inicializador'];
		$this->_perguntas[$i]['inicFunc']		= isset($pergunta['inicFunc']) ? $pergunta['inicFunc'] : '';
		$this->_perguntas[$i]['opcoes']			= $pergunta['opcoes'];
	}
	
	public function addBotaoTitulo($param){
		$this->_botaoTitulo[] = $param;
	}
	
	//--------------------------------------------------------------------------------------------- SET ---------------------
	
	public function setCor($modelo){
		$possiveis = ['','primary','','success','info','warning','danger','default'];
		if(array_search($modelo,$possiveis) === false){
			$modelo = 'primary';
		}
		$this->_modelo = $modelo;
	}
	
	public function setLink($link){
		$this->_link = $link;
	}
	
	public function setTitulo($titulo){
		$this->_titulo = $titulo;
	}
	
	public function setRespostas($respostas){
		$gravadas = $this->getValoresArmazenados();
		
		if(is_array($respostas) && count($respostas) > 0 && count($gravadas) > 0 && count($this->_perguntas) > 0){
			foreach ($this->_perguntas as $param){
				if(isset($respostas[$param['variavel']])){
					if($param['tipo'] == "D"){
						if(strpos($respostas[$param['variavel']],'/') === false){
							$valor = $respostas[$param['variavel']];
						}else{
							$valor = datas::dataD2S($respostas[$param['variavel']]);
						}
					}else{
						$valor = isset($this->_retorno[$param['variavel']]) ? $this->_retorno[$param['variavel']] : '';
					}
					$gravadas[$param['variavel']] = $valor;
				}
			}
			
			$this->setRespostasBD($gravadas);
			$this->getChaveValor();
			$this->_retorno = $this->getRetornos();
		}
	}
	
	protected function setRespostasBD($respostas){
		$usuario = getUsuario();
		$serial = serialize($respostas);
		$sql = "DELETE FROM sys044 WHERE programa = '".$this->_programas[0]."' AND usuario = '$usuario'";
		query($sql);
		$campos = [];
		$campos['programa'] = $this->_programas[0];
		$campos['usuario'] 	= $usuario;
		$campos['valor'] 	= $serial;
		$sql = montaSQL($campos, 'sys044');
		query($sql);
	}
	
	
	//----------------------------------------------------------- UTEIS -------------------------------------------
	/*
	 * Retira do nome do programa caracteres que não podem ser utilizados em nome de variáveis
	 */
	protected function normalizaPrograma(){
		$troca = [',','.'];
		$this->_nomePrograma = str_replace($troca, "_", $this->_programas[0]);
		return;
	}
	
	/*
	 *  #TODO: Fazer isto direito (incluir na tabela de parametros esta possibilidade)
	 */
	protected function montaSelect($tabela, $variavel,$selecionado, $tipoForm){
		$dados = array();
		$i = 0;
		$dados[$i][0] = "";
		$dados[$i][1] = "";
		$sql = "SELECT sys2_chave,sys2_campo_desc FROM sys002 WHERE sys2_tabela = '$tabela'";
		$rows = query($sql);
		if(count($rows) == 0){
			die("Tabela $tabela nao cadastrada no sys002");
		}else{
			$indice = $rows[0]['sys2_chave'];
			$desc	= $rows[0]['sys2_campo_desc'];
			$sql = "SELECT $indice, $desc FROM $tabela WHERE 1=1 ORDER BY $desc";
			$rows = query($sql);
			$i = 1;
			if(count($rows) > 0){
				foreach ($rows as $row){
					$dados[$i][0] = $row[$indice];
					$dados[$i][1] = $row[$desc];
					$i++;
				}
			}
		}
		$variavel = self::getNomeCampo($variavel);
		$param = array();
		$param['nome']		= $variavel;
		$param['valor']		= $selecionado;
		$param['campos']	= $dados;
		if($tipoForm == 'AM'){
			$param['multi'] = true;
		}
		$ret = formbase01::formSelect($param);
		return $ret;
	}

	protected function montaData($variavel, $valor){
		$campo = $this->getNomeCampo($variavel);
		$ret = '';
		
		if(strpos($valor,'/') === false){
			$valor = datas::dataS2D($valor);
		}
		
		$param = array();
		$param['nome']		= $campo;
		$param['valor']		= $valor;
		
		$ret = formbase01::formData($param);
		return $ret;
	}
	
	protected function montaSelect2($funcao, $variavel, $tipoForm, $selecionado,$tipo = 1){
		if($tipo == 1){
			if(strpos($funcao, ';') === false){
				$funcao .= ';';
			}
			$func = explode(";", $funcao);
			$q = sizeof($func) -1;
			for($i=0;$i<$q;$i++){
				if(($i+1) == $q){
					$func[$i] = '$dados = '.$func[$i];
				}
				eval($func[$i].';');
			}
		}else{
			$dados = $this->getOpcoes($funcao);
		}
		$variavel = $this->getNomeCampo($variavel);
		$param = array();
		$param['nome']		= $variavel;
		$param['valor']		= $selecionado;
		$param['lista']		= $dados;
		if(count($dados) < 21 && $tipoForm != 'AM'){
			$ret = formbase01::formSelect($param);
		}else{
			if($tipoForm == 'AM'){
				$param['multi'] = true;
			}
			$param['procura'] = true;
			$ret = formbase01::formSelect($param);
		}
		return $ret;
	}
	
	protected	function getOpcoes($opcoes){
		$ret = array();
		$e = 0;
		if(is_array($opcoes)){
			foreach ($opcoes as $key => $valor){
				$ret[$e][0] = $key;
				$ret[$e][1] = $valor;
				$e++;
			}
		}else{
			$opcs = explode(";", $opcoes);
			for($i=0; $i<count($opcs); $i++){
				$tmp = explode("=", $opcs[$i]);
				$ret[$e][0] = $tmp[0];
				$ret[$e][1] = $tmp[1];
				$e++;
			}
		}
		return $ret;
	}
	
	protected function retornaOpcoesSelecionada($selecionado, $opcoes){
		$ret = '';
		
		$opcs = explode(";", $opcoes);
		for($i=0; $i<count($opcs); $i++){
			$tmp = explode("=", $opcs[$i]);
			if($tmp[0] == $selecionado){
				$ret = $tmp[1];
			}
		}
		
		return $ret;
	}
}