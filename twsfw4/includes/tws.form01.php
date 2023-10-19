<?php
/*
 * Data Criação: 15/02/2022
 * Autor: Thiel
 *
 * Descricao: Gera formulários
 * 			  
 * 			  
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class form01{
	
	
	//Pastas
	private $_pastas = [];
	
	//Conteudo adicionais na pasta
	private $_addConteudoPastas = [];
	
		//Tamanho minimo de um campo para se tornar TextArea
	private $_tamanhoTA;
	
	//Indica a linha atual
	private $_linhaAtual = [];
	
	//Largura atual da linha (para quebrar quando chegar a 12)
	private $_larguraLinhaAtual = [];
	
	// Campos dos formulários
	private $_campos = [];
	
	//Indica se existe botão para upload de arquivo
	private $_enviaArquivo = false;
	
	// Informações do envio do form
	private $_urlEnvio 	= '';
	private $_nomeEnvio = '';
	private $_idEnvio 	= '';
	
	//Posição do botao submit
	private $_posicaoBotao = '';
	
	// Campos ocultos
	private $_hidden = [];
	
	// Botão a serem apresentados na barra de titulso
	private $_botaoTitulo = [];
	
	// Titulo (se utilizar card)
	private $_descricao = '';
	
	//Indica se deve incluir o botão de submit
	private $_botaoSubmit = true;
	
	/* Indica se o formulário é editável
	 * true -> imprime normalmente o formulario
	 * false -> imprime somente o conteudo (valores) dos forms
	 */
	private $_edicao;
	
	// Indica se deve gerar o JS de confirmacao
	private $_formJS;
	
	// Controle de quantidade de botoes data
	private $_quantDatas = 0;
	
	// Dados da tag <form>
	private $_form = [];
	
	//Indica se vai gerar script para validar os campos obrigatorios (se contiver pastas é automático)
	private $_geraScriptValidarObrigatorios = false;
	
	//Onsubmit - Script que deve ser executado
	private $_onSubmit = '';
	
	//URL do botão CANCELAR, se vazio não mostra o botão
	private $_URLcancelar = '';
	
	//Indica se os botões vão estar no footer
	private $_sendNoFooter;
	
	public function __construct($param = []){
		$this->_tamanhoTA 						= verificaParametro($param, 'tamanhoTA', 150, false);
		$this->_geraScriptValidarObrigatorios 	= verificaParametro($param, 'geraScriptValidacaoObrigatorios', false);
		$this->_onSubmit 						= verificaParametro($param, 'onsubmit', '');
		$this->_botaoSubmit						= verificaParametro($param, 'botaoSubmit', true);
		$this->_edicao							= verificaParametro($param, 'edicao', true);
		$this->_formJS 							= verificaParametro($param, 'formJS', false);;
		$this->_URLcancelar 					= verificaParametro($param, 'cancelar', '');
		
		$this->_sendNoFooter					= verificaParametro($param, 'sendNoFooter', true);
		if($this->_sendNoFooter){
			$this->setBotaoCancela();
		}
	}
	
	public function __toString(){
		global $nl;
		formbase01::setLayout('basico');
		$ret = '';
		$retPastas = [];
		
		$ret .= $this->printHidden();
		
		$quantPastas = count($this->_pastas);
		if($quantPastas > 1){
			$this->_geraScriptValidarObrigatorios = true;
			foreach ($this->_pastas as $i => $pasta){
				$retPastas[$i] = '';
			}
		}
		
//print_r($this->_campos);		
		foreach ($this->_campos as $pasta => $campos){
			$tempPasta = '';
			foreach ($campos as $linha => $camp){
				$tempPasta .= '<div class="row">'.$nl;
				foreach ($camp as $i => $c){
					$tam = $c['largura'];
					$temp = $this->printFormCampo($c);
					//para controlar os obrigatórios é necessário saber quem é select
					if(strpos($temp, 'select') !== false){
						$this->_campos[$pasta][$linha][$i]['select'] = true;
					}else{
						$this->_campos[$pasta][$linha][$i]['select'] = false;
					}
					$tempPasta .= '	<div  class="col-md-'.$tam.'">'.$temp.'</div>'.$nl;
				}
				$tempPasta .= '</div>'.$nl;
			}
			if($quantPastas > 1){
				$retPastas[$pasta] .= $tempPasta;
			}else{
				$ret .= $tempPasta;
			}
		}
//print_r($this->_addConteudoPastas);		
		if($quantPastas > 1){
			$tempPastas = array();
			foreach ($this->_pastas as $i => $pasta){
				$temp = array();
				$temp['titulo'] = $pasta;
				$temp['conteudo'] = $retPastas[$i];
				
				if(isset($this->_addConteudoPastas[$i])){
					foreach ($this->_addConteudoPastas[$i] as $addPasta){
						$temp['conteudo'] .= $addPasta;
					}
				}
				
				if(!empty($temp['conteudo'])){
					$tempPastas[] = $temp;
				}
			}
			$ret .= formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tempPastas));
		}elseif(count($this->_addConteudoPastas) == 1){
			foreach ($this->_addConteudoPastas[1] as $addPasta){
				$ret .=  $addPasta;
			}
		}
		
		if($this->_botaoSubmit && ($this->_urlEnvio != '' || (isset($this->_form['acao']) && !empty($this->_form['acao'])))){
			if($this->_sendNoFooter){
				$param = [];
				$param['URLcancelar'] = $this->_URLcancelar;
				$param['IDform'] = $this->_idEnvio;
				formbase01::formSendFooter($param);
			}else{
				$param = [];
				$param['posicao'] = $this->_posicaoBotao;
				$ret .= formbase01::formSend($param);
				
				if(!empty($this->_URLcancelar)){
					$param = [];
					$param['onclick'] = "setLocation('".$this->_URLcancelar."')";
					$param['tamanho'] = 'pequeno';
					$param['cor'] = COR_PADRAO_BOTAO_CANCELAR;
					$param['texto'] = 'Cancelar';
					$this->_botaoTitulo[] = $param;
				}
			}
		}
		
		if(!empty($this->_descricao)){
			$titulo = $this->_descricao;
			$param = array();
			if(count($this->_botaoTitulo) > 0){
				foreach ($this->_botaoTitulo as $botao){
					$param['botoesTitulo'][] = $botao;
				}
			}
			$param['versao'] = 1;
			$ret = addBoxInfo($titulo, $ret, $param);
		}
		
		if($this->_geraScriptValidarObrigatorios){
			$this->geraScriptValidacao($this->_idEnvio, $quantPastas);
		}
		
		if($this->_urlEnvio != '' || (isset($this->_form['acao']) && !empty($this->_form['acao']))){
			$param = $this->_form;
			if(!empty($this->_urlEnvio)){
				$param['acao'] 	= $this->_urlEnvio;
			}
			if(!empty($this->_nomeEnvio)){
				$param['nome'] 	= $this->_nomeEnvio;
			}
			if(!empty($this->_idEnvio)){
				$param['id'] 	= $this->_idEnvio;
			}
			if($this->_enviaArquivo){
				$param['enctype'] = true;
			}
			if($this->_geraScriptValidarObrigatorios){
				$param['onsubmit'] = 'verificaObrigatorios';
			}elseif(!empty($this->_onSubmit)){
				$param['onsubmit'] = $this->_onSubmit;
			}
			$ret = formbase01::form($param, $ret);
		}
		
		formbase01::setLayout('');
		return $ret;
	}

	//---------------------------------------------------------------------------------------------- UI ----------------------------------
	
	private function printHidden(){
		$ret = '';
		if(count($this->_hidden) > 0){
			foreach ($this->_hidden as $hidden){
				$param = array();
				$param['nome']	= $hidden["campo"];
				$param['valor']	= $hidden["valor"];
				$param['id']	= isset($hidden["id"]) && $hidden["id"] != '' ? $hidden["id"] : '';
				
				$ret .= formbase01::formHidden($param);
			}
		}
		return $ret;
	}
	
	private function printFormCampo($campo){
		$ret = '';
		$tipo = $this->_edicao ? $campo["tipo"] : "I";
		//print_r($campo);
		
		if($campo['obrigatorio'] == true){
			$label = '<strong>'.$campo['etiqueta'].'*</strong>';
		}else{
			$label = $campo['etiqueta'];
		}
		$campo['etiqueta'] = $label;
		
		//controle é feito por JS
		if($this->_geraScriptValidarObrigatorios === true){
			$campo['obrigatorio'] = false;
		}
		
		switch ($tipo){
			case "L": // Linha
				$ret = '<hr style="width: 100%; color: black; height: 1px; background-color:black;" />'."\n";
				break;
			case "A": // Array
				$ret = formbase01::formSelect($campo);
				break;
			case "AP": // Array Procura
				$campo['procura'] = true;
				$ret = formbase01::formSelect($campo);
				break;
			case "H": // HTML
				$ret = $campo['valor'];
				break;
			case "F": // File
				$ret = formbase01::formFile($campo);
				break;
			case "N": // Numerico
				$ret = formbase01::formTexto($campo);
				break;
			case "V": // Valor (2 casas decimais)
				$campo['mascara'] = 'V';
				$ret = formbase01::formTexto($campo);
				break;
			case "T": // Texto
				if($campo['opcoes'] != "" && strpos($campo['opcoes'], '=') !== false){
					$campo['lista'] = $this->getOpcoes($campo['opcoes']);
					$ret = formbase01::formSelect($campo);
				}else{
					$ret = formbase01::formTexto($campo);
				}
				break;
			case "TA": // Texto area
				$ret = formbase01::formTextArea($campo);
				break;
			case "ED": // Editor
				$ret = formbase01::formEditor($campo);
				break;
			case "CB": // checkbox
				break;
			case "S": // Senha
				$ret = formbase01::formSenha($campo);
				break;
			case "D": // Data
				//$campo['id']		= "form_data".$this->_quantDatas;
				$campo['tamanho'] 	= 10;
				$campo['maxtamanho']= 10;
				$ret = formbase01::formData($campo);
				$this->_quantDatas++;
				break;
			case "DS": //dias da semana
				$ret .= "dias da semana<br>";
				$ret = formbase01::checksemana($campo['etiqueta'],$campo['valor']);
				break;
			case "I": // Somente impressao
			default:
				if($campo['opcoes'] != ""){
					$campo['lista'] = $this->getOpcoes($campo['opcao']);
				}
				if(is_array($campo['lista'])){
					$valAtu = $campo['valor'];
					$param['valor']	= '';
					foreach ($campo['lista'] as $item){
						if($item[0] == $valAtu){
							if($campo['valor'] != ''){
								$campo['valor'] .= ' / ';
							}
							$campo['valor'] .= $item[1];
						}
					}
				}
				$ret = formbase01::formTexto($campo,false);
				break;
		}
		
		return $ret;
	}
	
	//---------------------------------------------------------------------------------------------- ADD ---------------------------------
	
	/**
	 * Inclui um campo no formulario
	 *
	 * @param	array	$param	Parametros para criação do campo
	 * 							tipo - 	T - Caracter N - Numero (inteiro) V - Valor (2 casas decimais) V4 - Valor (4 casas decimais)
	 * 							id
	 * 							nome
	 * 							campo
	 * 							etiqueta (label)
	 * 							tamanho
	 * 							linhas (textbox)
	 * 							valor
	 * 							lista -> array(array(valor1, etiqueta1),array(valor2,etiqueta2)....)
	 * 							validacao
	 * 							obrigatorio
	 * 							opcao -> String "valor=etiqueta;valor2=etiqueta2;...."
	 * 							help
	 * @return	void
	 *
	 * @version 0.01
	 */
	public function addCampo($param){
		
		$param['tamanho'] = verificaParametro($param, 'tamanho'	, 20, false);
		if ($param['tipo'] == 'C' || empty($param['tipo'])){
			$param['tipo'] = 'T';
		}
		if($param['tipo'] == 'T' && $param['tamanho'] > $this->_tamanhoTA){
			//Se for 'grande' vira TA
			$param['tipo'] = 'TA';
		}
		
		$pasta = verificaParametro($param, 'pasta'		, 0);

		if(!isset($this->_linhaAtual[$pasta])){
			$this->_linhaAtual[$pasta] = 0;
			$this->_larguraLinhaAtual[$pasta] = 0;
		}
		$linha = verificaParametro($param, 'linha'		, 0);
		if($linha > $this->_linhaAtual[$pasta]){
			$this->_linhaAtual[$pasta] = $linha;
			$this->_larguraLinhaAtual[$pasta] = 0;
		}
		$largura = verificaParametro($param, 'largura'	, 12, false);
		if($largura == 0){
			$largura = 12;
		}
		
		if($this->_larguraLinhaAtual[$pasta] + $largura > 12){
			$this->_larguraLinhaAtual[$pasta] = $largura;
			$this->_linhaAtual[$pasta]++;
		}else{
			$this->_larguraLinhaAtual[$pasta] += $largura;
		}
		
		//$linha = $this->_linhaAtual[$pasta];
		if(!isset($this->_campos[$pasta][$linha])){
			$this->_campos[$pasta][$linha] = [];
		}
		$prox = count($this->_campos[$pasta][$linha]);
		
		$this->_campos[$pasta][$linha][$prox]['largura'] = $largura;
		$this->_campos[$pasta][$linha][$prox]['linha'] = $pasta;
		if(isset($param['id']) && !empty($param['id'])){
			$this->_campos[$pasta][$linha][$prox]['id']	= $param['id'];
		}else{
			$this->_campos[$pasta][$linha][$prox]['id']	= ajustaID($param['campo']);
		}
		$this->_campos[$pasta][$linha][$prox]['nome']			= $param['campo'];
		$this->_campos[$pasta][$linha][$prox]['campo']			= $param['campo'];
		$this->_campos[$pasta][$linha][$prox]['etiqueta']		= verificaParametro($param, 'etiqueta'		, '');
		$this->_campos[$pasta][$linha][$prox]['tipo']			= strtoupper($param['tipo']);
		$this->_campos[$pasta][$linha][$prox]['tamanho']		= $param['tamanho'];
		$this->_campos[$pasta][$linha][$prox]['linhasTA']		= verificaParametro($param, 'linhasTA'		, '');
		$this->_campos[$pasta][$linha][$prox]['pasta']			= $pasta;
		$this->_campos[$pasta][$linha][$prox]['valor']			= verificaParametro($param, 'valor'			, '');
		//Lista - array [][0] - chave | [][1] - valor
		$this->_campos[$pasta][$linha][$prox]['lista']			= verificaParametro($param, 'lista'			, '');
		$this->_campos[$pasta][$linha][$prox]['validacao']		= verificaParametro($param, 'validacao'		, '');
		$this->_campos[$pasta][$linha][$prox]['obrigatorio']	= verificaParametro($param, 'obrigatorio'	, false);
		$this->_campos[$pasta][$linha][$prox]['opcoes']			= verificaParametro($param, 'opcoes'		, '');
		$this->_campos[$pasta][$linha][$prox]['help']			= verificaParametro($param, 'help'			, '');
		$this->_campos[$pasta][$linha][$prox]['mascara']		= verificaParametro($param, 'mascara'		, '');
		$this->_campos[$pasta][$linha][$prox]['readonly']		= verificaParametro($param, 'readonly'		, false);
		$this->_campos[$pasta][$linha][$prox]['onchange']		= verificaParametro($param, 'onchange'		, '');
		//Indica se deve aceitar valor negativo (ai vai usar o maskMoney)
		$this->_campos[$pasta][$linha][$prox]['negativo']		= verificaParametro($param, 'negativo'		, false);
		$this->_campos[$pasta][$linha][$prox]['idGroup']		= verificaParametro($param, 'idGroup'		, '');
		
		//Se existe uma "tabela" com a lista
		if(isset($param['tabela_itens']) && !empty($param['tabela_itens'])){
			$valores = array();
			$valores[0][0] = "";
			$valores[0][1] = "&nbsp;";
			
			$tab = explode('|', $param['tabela_itens']);
			if(count($tab) > 2){
				$tabela = $tab[0];
				$id = $tab[1];
				$desc = $tab[2];
				$ordem = isset($tab[3]) && !empty($tab[3])? ' ORDER BY '.$tab[3] : '';
				$where = isset($tab[4]) && !empty($tab[4]) ? ' WHERE '.$tab[4] : '';
				$sql = "SELECT $id,$desc FROM $tabela $where $ordem";
				//echo "$sql <br>\n\n";
				$rows = query($sql);
				if(isset($rows[0][$desc])){
					foreach ($rows as $row){
						$i = count($valores);
						$valores[$i][0] = $row[0];
						$valores[$i][1] = $row[1];
					}
				}
			}else{
				$valores = tabela($param['tabela_itens']);
			}
			
			$this->_campos[$pasta][$linha][$prox]['lista'] = $valores;
		}
		
		//Ajusta as opcoes
		if(!empty($this->_campos[$pasta][$linha][$prox]['opcoes'])){
			$this->_campos[$pasta][$linha][$prox]['lista'] = $this->getOpcoes($this->_campos[$pasta][$linha][$prox]['opcoes']);
			$this->_campos[$pasta][$linha][$prox]['tipo'] = 'A';
		}
		
		//Função que retorna a 'lista'
		if(isset($param['funcao_lista']) && !empty($param['funcao_lista'])){
			if(substr($param['funcao_lista'], -1) != ';'){
				$param['funcao_lista'] .= ';';
			}
			eval('$this->_campos[$pasta][$linha][$prox]["lista"] = '.$param['funcao_lista']);
			$this->_campos[$pasta][$linha][$prox]['tipo'] = 'A';
		}
		
		if($this->_campos[$pasta][$linha][$prox]['tipo'] == 'F'){
			$this->_enviaArquivo = true;
		}
	}
	
	private	function getOpcoes($opcoes){
		$ret = array();
		$e = 0;
		if(is_array($opcoes)){
			$quant = count($opcoes);
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
	
	/**
	 * Adiciona um contúdo a uma pasta, se a pasta não existir deve ser informado o titulo da mesma
	 *
	 * @param int 	 $pasta			- chave da pasta
	 * @param string $conteudo		- Conteúdo a ser adicionado na pasta
	 * @param string $tituloPasta	- título da pasta (se ela não existir
	 */
	public function addConteudoPastas($pasta, $conteudo, $tituloPasta = ''){
		if($tituloPasta != '' && !isset($this->_pastas[$pasta])){
			$this->_pastas[$pasta] = $tituloPasta;
		}
		
		$this->_addConteudoPastas[$pasta][] = $conteudo;
	}
	
	public function addHidden($campo,$valor,$id=''){
		$prox = count($this->_hidden);
		$this->_hidden[$prox]['campo'] 	= $campo;
		$this->_hidden[$prox]['valor'] 	= $valor;
		$this->_hidden[$prox]['id'] 	= empty($id) ? ajustaID($id) : $id;
	}
	
	
	
	public function quebraLinha($pasta = 1){
		$this->_larguraLinhaAtual[$pasta] = 0;
		$this->_larguraLinhaAtual[$pasta]++;
		
	}
	
	/**
	 * Adiciona um botão na barra de tirulo
	 */
	public function addBotaoTitulo($botao){
		$prox = count($this->_botaoTitulo);
		$this->_botaoTitulo[$prox] = $botao;
	}
	
	//---------------------------------------------------------------------------------------------- SET ---------------------------------
	
	public function setPastas($pastas){
		if(is_array($pastas)){
			$this->_pastas = [];
			$this->_pastas = $pastas;
		}
	}
	
	public function setEnvio($url, $nome, $id = '', $posicao = 'E'){
		$this->_urlEnvio = $url;
		$this->_nomeEnvio = $nome;
		$this->_posicaoBotao = $posicao;
		$id = empty($id) ? ajustaID($nome) : $id;
		$this->_idEnvio = $id;
	}
	
	
	/*
	 *  Quando nao for edicao somente mostra os campos
	 */
	public function setEdicao($tipo = true){
		$this->_edicao = $tipo === false ? false : true;
	}
	
	/**
	 * Determina se deve ser gerado o JS de confirmacao dos dados
	 */
	public function setJS($tipo = false){
		$this->_formJS = $tipo === false ? false : true;
	}
	
	/**
	 * Indica se deve imprimir o botão de submit
	 */
	public function setSubmit($submit = true){
		$this->_botaoSubmit = $submit === false ? false : true;
	}
	
	/**
	 * Inclui a descricao (titulo)
	 */
	public function setDescricao($desc){
		$this->_descricao = $desc;
	}
	
	public function setBotaoCancela($url = ''){
		$url = empty(trim($url)) ? getLink().'index' : $url;
		$this->_URLcancelar = $url;
	}
	
	// Determina os dados da tag <form>
	public function setForm($param){
		$this->_form = $param;
	}
	
	//---------------------------------------------------------------------------------------------- GET ---------------------------------
	
	//---------------------------------------------------------------------------------------------- UTEIS -------------------------------
	
	private function geraScriptValidacao($id, $quantPastas = 1){
		addPortaljavaScript('function verificaObrigatorios(){ ');
		addPortaljavaScript("	msg = '';");
		addPortaljavaScript("");
		foreach ($this->_campos as $pasta => $campos){
			foreach ($campos as $linha => $camp){
				foreach ($camp as $i => $c){
					if($c['obrigatorio'] == true){
						if(isset($c['select']) && $c['select'] === true){
							addPortaljavaScript("	conteudo = $('#".$c['id']." option:selected').val();");
						}else{
							addPortaljavaScript("	conteudo = $('#".$c['id']."').val();");
						}
						addPortaljavaScript("	if(conteudo.trim() == '' || conteudo == 'undefined'){");
						$pasta = '';
						if($quantPastas > 1){
							$pasta = ' na pasta '.$this->_pastas[$c['pasta']];
						}
						addPortaljavaScript("		msg += 'O campo ".$c['etiqueta']."$pasta deve ser preenchido\\n';");
						//		addPortaljavaScript("		msg = conteudo;");
						addPortaljavaScript("	}");
						//echo "Campo: ".$c['id']." - ".$c['etiqueta']." - ".$c['pasta']." - ".$this->_pastas[$c['pasta']]."<br>\n";
					}
				}
			}
		}
		addPortaljavaScript("	if(msg == ''){");
		addPortaljavaScript("		return true;");
		addPortaljavaScript("	}else{");
		addPortaljavaScript("		alert(msg);");
		addPortaljavaScript("		return false;");
		addPortaljavaScript("	}");
		addPortaljavaScript("};");
	}
	
}