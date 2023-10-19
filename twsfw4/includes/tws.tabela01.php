<?php
/*
 * Data Criacao 13/02/2022
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Tabela genérica
 *
 * Altera��es:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class tabela01{
	
	//ID da tabela
	private $_id;
	
	//* Classes
	private $_classes = [];
	
	//Titulo da tabela (se não for informado não vai incluir a tabela em um card)
	private $_titulo;
	
	//Botão a serem apresentados na barra de titulso
	private $_botaoTitulo = [];
	
	//Ações a serem realizadas (botães nas linhas)
	private $_acoes = [];
	
	//Indica se vai ocorrer quebra de texto dentro das celulas
	private $_nowrap;
	
	//Icone a ser mostrado no titulo
	private $_icone;
	
	//Indica que o browser estã rodando em modo automãtico (provavelmente vai ser enviado por email)
	private $_auto;
	
	// Campos
	private $_campos = [];
	
	// Cabecalhos da tabela - array titulo=>campo
	private $_cab = [];
	
	// Cabecalhos da tabela - tamanhos das colunas
	private $_cabWidth = [];
	
	// Cabecalhos da tabela - classes das colunas
	private $_cabClass = [];

	// Posicao dos itens dentro da coluna (D-direita, E-esquerda, C-centro, J-justificado)
	private $_cabPosicao = [];
	
	// Tipo de dado
	private $cabTipo = [];
	
	// Largura total
	private $_tabWidth = 0;
	
	//Tamanho da tabela
	private $_width = '';
	
	//Dados a serem mostrados
	private $_dados = [];
	
	//Dados a serem mostrados no TFOOT
	private $_dadosTfoot = [];
	
	//Indica se a tabela vai ser impressa
	private $_print;
	
	//Indica se deve imprimir zero se um campo numérico estiver em branco
	private $_imprimeZero;
	
	//Algo que deva ser impresso abaixo da tabela (fora da mesma)
	private $_footer = '';
	
	//indica se a tabela deve ser printada como um cubo do rm
	private $_cubo;
	
	//-------------------------------------------------------------------------- Selecao de linhas
	//Seleciona (primeira coluna indicando que a linha possa ser selecionada)
	private $_seleciona;		// Indica que sera utilizado o processo
	private $_selec_variavel;	// Nome da variavel
	private $_selec_coluna;		// indica qual a coluna tera o valor a ser preechido na variavel
	private $_selec_tam = 80;	// tamanho da coluna
	private $_selec_link;		// Indica o link para onde sera direcionado o form de seleãão
	private $_selec_name;		// Nome do formulario de selecao (se não informado utiliza "browseForm"
	private $_selec_flag;		// Coluna que contem o flag que possibilita selecionar a linha
	private $_selec_txtBotao;	// Texto do botao de "enviar selecao"
	
	//--------------------------------------------------------------------------- datatables
	private $_paginacao; 	//Indica se vai ter paginacao
	private $_scroll;		//Indica que vai ter scroll
	private $_scrollX;		//Indica que vai ter scroll X
	private $_scrollY;		//Indica que vai ter scroll Y
	private $_ordenacao;	// Indica se deve ter ordenação nas colunas
	private $_filtro;		//Indica se vai utilizar filtro
	private $_info;			//Indica se vai mostrar informacoes
	
	//-------------------------------------------------------------------------- Valores padrães
	private $_tamPadraoColuna = 100;
	private $_posicaoPadraoColuna = 'E';
	private $_tipoPadraoColuna = "T";
	
	//-------------------------------------------------------------------------- Cores para as linhas
	private $_cores = [];
	private $_colunaCor = '';
	
	//-------------------------------------------------------------------------- Cores para as colunas (cores fixas)
	private $_coresColunas = [];
	
	//-------------------------------------------------------------------------- Cores para as colunas (cores condicionais)
	private $_coresColunasIf = [];
	private $_coresColunasCondicional = [];
	
	//Cores possíveis
	private $_coresPossiveis = ['active','primary','secondary','success','danger','warning','info','light','dark'];
	
	//--------------------------------- cores das linhas
	private $_corLinha = '';
	
	public function __construct($param = []){
		$this->carregaScripts();
		$this->_titulo 	= $param['titulo'] ?? '';
		$this->_icone	= $param['icone'] ?? '';
		$id	= $param['id'] ?? '';
		if(empty($id)){
			$quantID = getAppVar('tabelaTWSquant') === false ? 1 : getAppVar('tabelaTWSquant');
			$this->_id = 'tabelaTWS'.$quantID;
			putAppVar('tabelaTWSquant', $quantID + 1);
		}else{
			$this->_id = $id;
		}
		$this->_auto 	= $param['auto'] ?? false;
		$this->_nowrap 	= $param['nowrap'] ?? 'nowrap';
		$this->_print 	= $param['print'] ?? true;
		$this->_width	= $param['width'] ?? 'AUTO';
		
		$this->_imprimeZero = $param['imprimeZero'] ?? true;
		
		//Data Table
		//Por padrão tabela com scroll
		$this->_scroll 		= $param['scroll'] ?? true;
		$this->_scrollX 	= $param['scrollX'] ?? false;
		$this->_scrollY 	= $param['scrollY'] ?? false;
		$this->_ordenacao 	= $param['ordenacao'] ?? true;
		//Por padrão tabela sem paginação
		$this->_paginacao 	= $param['paginacao'] ?? false;
		$this->_filtro 		= $param['filtro'] ?? true;
		$this->_info 		= $param['info'] ?? true;
		$this->_cubo        = $param['cubo'] ?? false;
		
		if(!isset($param['table-striped']) || $param['table-striped'] === true){
			$this->setClasse('table', 'table-striped', true);
		}
		if(!isset($param['table-bordered']) || $param['table-bordered'] === true){
			$this->setClasse('table', 'table-bordered', true);
		}
	}
	
	public function __toString(){
		$ret = '';
		$this->iniTemplateDataTable();
		
		if($this->_auto){
			$ret = $this->geraAutomatico();
			return $ret;
		}else{
			if($this->_print){
				$ret = $this->montaTabela();
			}else{
				if(count($this->_dados) > 0){
					$ret .= "Não será exibida a tabela, favor baixar a planilha.";
				}
			}
		}
		
		if(!empty($this->_titulo)){
			$param = [];
			$param['titulo'] = $this->_titulo;
			$param['icone'] = $this->_icone;
			$param['conteudo'] = $ret;
			if(count($this->_botaoTitulo) > 0){
				foreach ($this->_botaoTitulo as $botao){
					$param['botoesTitulo'][] = $botao;
				}
			}
			$ret = addCard($param);
		}else{
			if(count($this->_botaoTitulo) > 0){
				foreach ($this->_botaoTitulo as $botao){
					addBreadcrumbPrincipal($botao);
				}
			}
			
		}
		
		return $ret;
	}
	
	private function geraAutomatico(){
		$tab = new tabela_gmail01();
		
		$tab->setColunasTotais(count($this->_cab));
		
		$tamanho = $this->_tabWidth > 0 ? $this->_tabWidth : "100%";
		$tab->abreTabela($tamanho);
		
		if($this->_titulo != ""){
			$tab->addTitulo($this->_titulo);
		}
		
		$tab->abreTR(true);
		foreach ($this->_cab as $chave => $etiq){
			$tab->abreTH($etiq,1);
		}
		$tab->fechaTR();
		
		$linhas = count($this->_dados);
		$colunas = count($this->_cab);
		for ($l=0;$l<$linhas;$l++){
			$tab->abreTR();
			foreach ($this->_cab as $i => $cab){
				$tipo = $this->_cabTipo[$i];
				$tamanho = "";
				$valorCampo = isset($this->_dados[$l][$this->_campos[$i]]) ? $this->_dados[$l][$this->_campos[$i]] : '';
				
				switch ($tipo){
					case "V":
						//Valor (duas casas decimais)
						if($this->_imprimeZero || $valorCampo != '' ){
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 2, ',', '.');
							}
						}else{
							$valorCampo = '';
						}
						break;
					case "V4":
						//Valor (quatro casas decimais)
						if($this->_imprimeZero || $valorCampo != '' ){
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 4, ',', '.');
							}
						}else{
							$valorCampo = '';
						}
						break;
					case "N":
						//Valor inteiro
						if($this->_imprimeZero || $valorCampo != '' ){
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 0, ',', '.');
							}
						}else{
							$valorCampo = '';
						}
						break;
					case "T":
						//Valor inteiro
						$valorCampo = $valorCampo;
						break;
					case "D":
						//Data
						if($valorCampo != '' && $valorCampo != 0){
							$valorCampo = datas::dataS2D($valorCampo);
						}else{
							$valorCampo = '';
						}
						break;
				}
				
				if($valorCampo == ''){
					$valorCampo =  '&nbsp;';
				}
				$tab->abreTD($valorCampo,1,$this->_cabPosicao[$i]);
			}
			$tab->fechaTR();
		}
		$tab->fechaTabela();
		$tab->addBR();
		//$tab->termos();
		
		$ret = ''.$tab;
		
		return $ret;
	}
	
	//------------------------------------------------------------------- UI --------------------
	private function montaTabela(){
		global $nl;
		$ret = '';
		
		if($this->_width == ''){
			$this->_width = '100%';
		}elseif (strtoupper($this->_width) == 'AUTO'){
			$this->_width = $this->_tabWidth;
		}
		
		$classeTable = $this->getClasseItem('table');
		
		$ret .= $nl.'<table id="'.$this->_id.'" class="table table-sm '.$classeTable.'"  width="'.$this->_width.'">'.$nl;
		
		$ret .= $this->impCabecalho();
		if($this->_cubo){
		    $ret .= $this->impDadosCubo();
		}
		else{
		    $ret .= $this->impDados();
		}
		$ret .= $this->impFoot();
		
		$ret .= '</table>'.$nl;
		if(!empty($this->_footer)){
			$ret .= '<br>'.$this->_footer;
		}
		
		return $ret;
	}
	
	private function impCabecalho(){
		global $nl;
		$ret = '';
		
		//Conta a quantidade de ações a direita e a esquerda
		$acoes_i = 0;
		$acoes_f = 0;
		$quant = count($this->_acoes);

		if($quant > 0){
			foreach ($this->_acoes as $acoes){
				if($acoes['pos'] == 'I'){
					$acoes_i++;
				}
				if($acoes['pos'] == 'F'){
					$acoes_f++;
				}
			}
		}
		
		$ret .= '<thead>'.$nl;
		
		$ret .= '	<tr>'.$nl;
		//Acoes no inicio da tabela
		if(!$this->_cubo){
    		if($quant > 0){
    			foreach ($this->_acoes as $acoes){
    				if($acoes['pos'] == 'I'){
    					$tam = ' width="'.$acoes['width'].'"';
    					$ret .= '		<th'.$tam.'>&nbsp;</th>'.$nl;
    				}
    			}
    		}
		}
		if(count($this->_cab) > 0){
			foreach ($this->_cab as $chave => $etiq){
				$w = '';
				if($this->_cabWidth[$chave] > 0){
					$w = ' width="'.$this->_cabWidth[$chave].'"';
				}
				$ret .= '		<th'.$w.'>'.$etiq.'</th>'.$nl;
			}
		}
		//Acoes no final da tabela
		if(!$this->_cubo){
    		if($quant > 0){
    			foreach ($this->_acoes as $acoes){
    				if($acoes['pos'] == 'F'){
    					$tam = ' width="'.$acoes['width'].'"';
    					$ret .= '		<th'.$tam.'>&nbsp;</th>'.$nl;
    				}
    			}
    		}
		}
		$ret .= '	</tr>'.$nl;
		$ret .= '</thead>'.$nl;
		
		return $ret;
		
	}
	
	private function impDados(){
		global $nl;
		$ret = '';
		
		$quant = count($this->_acoes);
		$linhas = count($this->_dados);
		$ret .= '	<tbody>'.$nl;
		for ($l=0;$l<$linhas;$l++){
			$classTR = '';
			if(!empty($this->_corLinha) && isset($this->_dados[$l][$this->_corLinha]) && !empty($this->_dados[$l][$this->_corLinha])){
				$classTR = ' class="table-'.$this->_dados[$l][$this->_corLinha].'"';
			}
				
			$ret .= '	<tr'.$classTR.'>'.$nl;
			//Acoes no inicio da tabela
			if($quant > 0){
				foreach ($this->_acoes as $a => $acoes){
					if($acoes['pos'] == 'I')
						$ret .= $this->impAcoes($a, $l);
				}
			}
			foreach ($this->_cab as $i => $cab){
				$sort = '';
				$tipo = $this->_cabTipo[$i];
				$valorCampo = isset($this->_dados[$l][$this->_campos[$i]]) ? $this->_dados[$l][$this->_campos[$i]] : '';
				switch ($tipo){
					case "V":
						//Valor (duas casas decimais)
						if($valorCampo == "" || !$valorCampo){
							if($this->_imprimeZero){
								$valorCampo = 0;
							}else{
								$valorCampo = '';
							}
						}
						if(!preg_match("/([a-zA-Z])/", $valorCampo ) && !empty($valorCampo)){
							$valorCampo = number_format($valorCampo, 2, ',', '.');
						}
						break;
					case "V4":
						//Valor (quatro casas decimais)
						if($valorCampo == "" || !$valorCampo){
							if($this->_imprimeZero){
								$valorCampo = 0;
							}else{
								$valorCampo = '';
							}
						}
						if(!preg_match("/([a-zA-Z])/", $valorCampo && !empty($valorCampo)) ){
							$valorCampo = number_format($valorCampo, 4, ',', '.');
						}
						break;
					case "N":
						//Valor inteiro
						if($valorCampo == "" || !$valorCampo){
							if($this->_imprimeZero){
								$valorCampo = 0;
							}else{
								$valorCampo = '';
							}
						}
						if($valorCampo == 0 && !$this->_imprimeZero){
							$valorCampo = '';
						}
						if(!preg_match("/([a-zA-Z])/", $valorCampo)  && !empty(trim($valorCampo)) ){
							$valorCampo = number_format($valorCampo, 0, ',', '.');
						}
						break;
					case "T":
						//Valor inteiro
						$valorCampo = ajustaCaractHTML($valorCampo);
						break;
					case "D":
						//Data
						$sort = 'data-sort="'.$valorCampo.'"';
						if($valorCampo != '' && $valorCampo != 0){
							$valorCampo = datas::dataS2D($valorCampo);
						}
						break;
				}
				switch (strtoupper($this->_cabPosicao[$i])) {
					case "D":
						$pos = 'right';
						break;
					case "direita":
						$pos = 'right';
						break;
					case "C":
						$pos = 'center';
						break;
					case "centro":
						$pos = 'center';
						break;
					case "J":
						$pos = 'justify';
						break;
					case "justificado":
						$pos = 'justify';
						break;
					default:
						$pos = 'left';
						break;
				}
				$w = '';
				if($this->_cabWidth[$i] > 0){
					$w = ' width="'.$this->_cabWidth[$i].'"';
				}
				
				$cor = '';
				if(!empty($this->_colunaCor) && isset($this->_cores[$this->_dados[$l][$this->_colunaCor]])){
					$cor = "bgcolor='".$this->_cores[$this->_dados[$l][$this->_colunaCor]]."'";
				}elseif(isset($this->_coresColunas[$i])){
					$cor = "bgcolor='".$this->_coresColunas[$i]."'";
				}
				
				//Se a coluna tiver uma cor especifica condicional
				//$this->_coresColunasIf[$coluna] = $controle;
				//$this->_coresColunasCondicional[$coluna][$key] = $cor;
				
				if(isset($this->_coresColunasIf[$i]) && isset($this->_coresColunasCondicional[$i][$this->_dados[$l][$this->_coresColunasIf[$i]]])){
					$cor = "bgcolor='".$this->_coresColunasCondicional[$i][$this->_dados[$l][$this->_coresColunasIf[$i]]]."'";
				}
				
				$ret .= '		<td align="'.$pos.'"'.$w.' '.$this->_nowrap.' '.$cor.' '.$sort.'>'.$valorCampo.'</td>'.$nl;
			}
			//Acoes no fim da tabela
			if($quant > 0){
				foreach ($this->_acoes as $a => $acoes){
					if($acoes['pos'] == 'F')
						$ret .= $this->impAcoes($a, $l);
				}
			}
			$ret .= '	</tr>'.$nl;
		}
		$ret .= '	</tbody>'.$nl;
		
		return $ret;
	}
	
	private function impAcoes($a,$l){
		global $nl;
		$ret = '';
		$acao = $this->_acoes[$a];
		//Se nao foi indicado condicao para mostrar a acao, ou a condicao e positiva, imprime a acao
		if($acao['flag'] == '' || $this->_dados[$l][$acao['flag']]){
			
			if(!empty($acao['link'])){
				if(strpos($acao['link'], '{ID}') !== false){
					$url = str_replace('{ID}' ,$this->_dados[$l][$acao['coluna']],$acao['link']);
					if(strpos($url, '{COLUNA:') !== false){
						$campo = $this->separaCampo($url);
						$url = str_replace("{COLUNA:$campo}" ,"'".$this->_dados[$l][$campo]."'",$url);
					}
					$acao['url'] = $url;
				}else{
					$colunas = [];
					if(!is_array($acao['coluna'])){
						$colunas[] = $this->_dados[$l][$acao['coluna']];
					}else{
						foreach ($acao['coluna'] as $col){
							$colunas[] = $this->_dados[$l][$col];
						}
					}
					$url = $acao["link"].implode('|', $colunas);
					$acao['url'] = $url;
				}
			}
			
			if(!empty($acao['onclick'])){
				$url = $acao['onclick'];
				if(strpos($url, '{COLUNA:') !== false){
					$campo = $this->separaCampo($url);
					$url = str_replace("{COLUNA:$campo}" ,$this->_dados[$l][$campo],$url);
				}
				$acao['onclick'] = $url;
				$acao['tipo'] = 'botao';
			}
			
			if($acao['funcao'] == ''){
				//$acao['url'] = $acao['link'].$this->_dados[$l][$campo];
			}else{
				eval('$d = '.$acao['funcao']."('".$this->_dados[$l][$campo]."');");
				$acao['url'] = $acao['link'].$d;
			}
			//print_r($this->_dados[$l]);
			//$acao['tamanho'] = 'pequeno';
			$ret = formbase01::formBotao($acao);
		}else{
			$ret = "&nbsp;";
		}
		$tam = ' width="'.$acao['width'].'"';
		$cor = '';
		if(!empty($this->_colunaCor) && isset($this->_cores[$this->_dados[$l][$this->_campos[$this->_colunaCor]]])){
			$cor = "bgcolor='".$this->_cores[$this->_dados[$l][$this->_campos[$this->_colunaCor]]]."'";
		}
		$ret = '		<td align="center" '.$tam.' '.$cor.'>'.$ret.'</td>'.$nl;
		
		return $ret;
	}
	
	private function impFoot(){
		
	}
	
	//------------------------------------------------------------------- ADD -------------------
	
	/*
	 * Adiciona uma coluna a tabela
	 */
	public function addColuna($param){
		$campo = '';
		if(!isset($param['campo']) || $param['campo'] == ''){
			$param['campo'] = count($this->_cab);
		}
		$campo = $param['campo'];
		$this->_campos[$campo]		= $param['campo'];
		$this->_cab[$campo] 		= $param['etiqueta'];
		$this->_cabWidth[$campo]	= $param['width'] ?? $this->_tamPadraoColuna;
		$this->_cabPosicao[$campo]	= $param['posicao'] ?? $this->_posicaoPadraoColuna;
		$this->_cabClass[$campo]	= $param['class'] ?? '';
		$this->_cabTipo[$campo]		= strtoupper($param['tipo'] ?? $this->_tipoPadraoColuna);
		$this->getTamanhoTotalTabela();
	}
	
	/**
	 * Adiciona um botão na barra de tirulo da tabela
	 */
	public function addBotaoTitulo($botao){
		$this->_botaoTitulo[] = $botao;
	}

	/**
	 * Adiciona uma acao na tabela (se nao indicado onde, fica no final)
	 *
	 * @param	array	$param
	 * 					[texto] 	string com o texto a ser impresso na tabela
	 * 					[imagem]	icone bootstrap utilizado
	 * 					[tipo]		indica que 'link' ou 'botao' - padrao
	 * 					[cor] 		cor do botao(azul - padrao -, verde, azulfraco, laranja, vermelho, branco)
	 * 					[tamanho]	tamanho do botao (default, grande, pequeno, minimo - padrao -)
	 * 					[link]		link a ser utilizado (sendo que o parametro coluna vai ser impresso no final
	 * 					[coluna]	indica a coluna que vai ser impressa no final do link
	 * 					[flag]		coluna que indica se a ação vai ser impressa ou não
	 * 					[width]		tamanho da coluna a ser impressa
	 * 					[pos]		posicao (Inicio, Fim)
	 * 					[funcao]	Funcao a ser executada nos valores
	 */
	function addAcao($param){
		if(is_array($param)){
			$temp = [];
			$temp['texto'] 		= $param['texto'] ?? '';
			$temp['icone'] 		= $param['icone'] ?? '';
			$temp['botao'] 		= $param['botao'] ?? '';
			$temp['tipo'] 		= $param['tipo'] ?? 'link';
			$temp['cor'] 		= $param['cor'] ?? COR_PADRAO_BOTOES;
			$temp['tamanho'] 	= $param['tamanho'] ?? '';
			$temp['link'] 		= $param['link'] ?? '';
			$temp['coluna'] 	= $param['coluna'] ?? '';
			$temp['flag'] 		= $param['flag'] ?? '';
			$temp['width'] 		= $param['width'] ?? '30';
			$temp['pos'] 		= $param['pos'] ?? 'I';
			$temp['onclick']	= $param['onclick'] ?? '';
			$temp['help'] 		= $param['help'] ?? '';
			//Funcao a ser executada nos valores
			$temp['funcao'] 	= $param[''] ?? '';
			$temp['textoAlt'] 	= $param['textoAlt'] ?? '';

			$this->_acoes[] = $temp;
			$this->getTamanhoTotalTabela();
		}
	}
	
	public function addCorColunaIf($coluna, $key, $cor){
		if(!empty($coluna) && !empty($key) && !empty($cor)){
			$this->_coresColunasCondicional[$coluna][$key] = $cor;
		}
	}
	public function addCorLinha($key, $cor){
		if(!empty($key) && !empty($cor)){
			$this->_cores[$key] = $cor;
		}
	}
	
	//------------------------------------------------------------------- SET -------------------
	public function setTitulo($titulo){
		$this->_titulo = $titulo;
	}
	
	public function setDados($dados){
			$this->_dados = $dados;
	}
	
	public function setDadosTfoot($dados){
		$this->_dadosTfoot = $dados;
	}
	
	
	public function setPrint($print){
		$this->_print = $print === false ? false : true;
	}
	
	public function setNowrap($now){
		$this->_nowrap = $now === true ? 'nowrap' : '';
	}
	
	/**
	 * Indica se deve imprir zero nas colunas numericas se estiver vazio
	 *
	 * @param boolean $imp
	 */
	public function setImprimeZero($imp = true){
		$this->_imprimeZero = $imp === false ? false : true;
	}
	
	public function setFooter($footer){
		$this->_footer = $footer;
	}
	
	/**
	 * Seta se o browser estã sendo executado automaticamente
	 *
	 * @param	boolean	$auto	Indica se ã um relatãrio automãtico
	 * @return	void
	 */
	public function setAuto($auto){
		$this->_auto = $auto === true ? true : false;
	}
	
	public function setColunaCor($coluna){
		if(!empty($coluna)){
			$this->_colunaCor = $coluna;
		}
	}
	
	public function setCorColunas($coluna, $cor){
		if(!empty($coluna) && !empty($cor)){
			$this->_coresColunas[$coluna] = $cor;
		}
	}
	
	public function setColunaCorIf($coluna, $controle){
		if(!empty($coluna) && !empty($controle)){
			$this->_coresColunasIf[$coluna] = $controle;
		}
	}
	
	/**
	 * Indica a coluna onde vai ser informada a cor da linha
	 * 
	 * @param string $coluna - qual coluna vai indicar a cor
	 */
	public function setCorLinha($coluna){
		$this->_corLinha = $coluna;
	}
	
	/**
	 * Habilita ou desabilita uma determinada classe $classe para um determinado $item
	 * PEX: para desabilitar a classe table-bordered na tabela:
	 * setClasse('table', 'table-bordered', false);
	 */
	function setClasse($item, $classe, $valor){
		$this->_classes[$item][$classe] = $valor ? $classe : "";
	}
	//------------------------------------------------------------------- GET -------------------
	private function getTamanhoTotalTabela(){
		$this->_tabWidth = 0;
		if(count($this->_cabWidth) > 0){
			foreach ($this->_cabWidth as $tam){
				$this->_tabWidth += $tam;
			}
		}
		if(count($this->_acoes) > 0 && $this->_tabWidth > 0){
			foreach ($this->_acoes as $acoes){
				$this->_tabWidth += $acoes['width'];
			}
		}
		if($this->_seleciona){
			$this->_tabWidth += $this->_selec_tam;
		}
		return $this->_tabWidth;
	}
	
	/**
	 * Retorna o array com os dados da tabela
	 *
	 * @param	void
	 * @return	mixed	Dados da tabela
	 */
	public function getDados(){
		return $this->_dados;
	}
	
	/**
	 * Retorna as classes setadas em um item em sequencia
	 */
	function getClasseItem($item){
		$ret = implode(' ', $this->_classes[$item]);
		return $ret;
	}
	
	//------------------------------------------------------------------- UTEIS -----------------
	
	private function carregaScripts(){
		global $pagina;
		
		if(is_object($pagina)){
			// Se executado no schedule não cria o objeto pagina
			$pagina->addStyle('plugin', 'datatables-bs4/css/dataTables.bootstrap4.min.css', 'I');
			$pagina->addStyle('plugin', 'datatables-responsive/css/responsive.bootstrap4.min.css', 'I');
			$pagina->addStyle('plugin', 'datatables-buttons/css/buttons.bootstrap4.min.css', 'I');
			
			$pagina->addScript('plugin', 'datatables/jquery.dataTables.min.js', 'F');
			$pagina->addScript('plugin', 'datatables-bs4/js/dataTables.bootstrap4.min.js', 'F');
			$pagina->addScript('plugin', 'datatables-responsive/js/dataTables.responsive.min.js', 'F');
			$pagina->addScript('plugin', 'datatables-responsive/js/responsive.bootstrap4.min.js', 'F');
			$pagina->addScript('plugin', 'datatables-buttons/js/dataTables.buttons.min.js', 'F');
			$pagina->addScript('plugin', 'datatables-buttons/js/buttons.bootstrap4.min.js', 'F');
			$pagina->addScript('plugin', 'jszip/jszip.min.js', 'F');
			$pagina->addScript('plugin', 'pdfmake/pdfmake.min.js', 'F');
			$pagina->addScript('plugin', 'pdfmake/vfs_fonts.js', 'F');
			$pagina->addScript('plugin', 'datatables-buttons/js/buttons.html5.min.js', 'F');
			$pagina->addScript('plugin', 'datatables-buttons/js/buttons.print.min.js', 'F');
			$pagina->addScript('plugin', 'datatables-buttons/js/buttons.colVis.min.js', 'F');
		}
	}

	private function iniTemplateDataTable(){
		addPortalJquery('$(\'#'.$this->_id.'\').DataTable( {');
		if($this->_ordenacao == false){
			addPortalJquery('"ordering": false,');
		}else{
			addPortalJquery('"ordering": true,');
		}
		if($this->_scroll){
			//addPortalJquery('"scrollY": 300,');
			addPortalJquery('"scrollY": \'60vh\',');
			addPortalJquery('"scrollX": true,');
			addPortalJquery('"scrollCollapse": true,');
			//addPortalJquery('"scrollX": true,');
		}else{
			if($this->_scrollY){
				addPortalJquery('"scrollY": 300,');
			}
			if($this->_scrollX){
				addPortalJquery('	"scrollX": true,');
			}
		}
		if(!$this->_paginacao){
			addPortalJquery('"paging": false,');
			//addPortalJquery('"info":     false,'); // mensagem abaixo da tabela indicando x de n registros
		}
		if(!$this->_filtro){
			addPortalJquery('"bFilter": false,');
		}
		if(!$this->_info){
			addPortalJquery('"info":     false,');
		}
		$portugues = '"language": {
	            "sEmptyTable": "Nenhum registro encontrado",
	            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
	            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
	            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
	            "sInfoPostFix": "",
	            "sInfoThousands": ".",
	            "sLengthMenu": "_MENU_ resultados por página",
	            "sLoadingRecords": "Carregando...",
	            "sProcessing": "Processando...",
	            "sZeroRecords": "Nenhum registro encontrado",
	            "sSearch": "Pesquisar",
	            "oPaginate": {
	                "sNext": "Próximo",
	                "sPrevious": "Anterior",
	                "sFirst": "Primeiro",
	                "sLast": "Último"
	            },
	            "oAria": {
	                "sSortAscending": ": Ordenar colunas de forma ascendente",
	                "sSortDescending": ": Ordenar colunas de forma descendente"
	            }}';
		addPortalJquery($portugues);
		addPortalJquery('} );');
		
	}
	
	private function separaCampo($url){
		$pos = strpos($url, '{COLUNA:');
		$url = substr($url, $pos+1);
		$pos = strpos($url, '}');
		$url = substr($url,0,$pos);
		
		$campo = explode(':', $url);
		return $campo[1];
	}
	
	private function impDadosCubo(){
	    global $nl;
	    $ret = '';
	    
	    $dados = $this->limparDadosCubo($this->_dados);
	    $copia_campos = $this->_campos;
	    $dados_remontados = $this->montarDadosCubo($dados, $copia_campos);
	    $copia_campos = $this->_campos;
	    $linhas_cubo = $this->montarLinhasImpCubo($dados_remontados, $copia_campos);
	    $ret .= '	<tbody>'.$nl;
	    foreach ($linhas_cubo as $linha){
	        $classTR = '';
	        $ret .= '	<tr'.$classTR.'>'.$nl;
	        
	        $ret .= implode(' ', $linha);
	        
	        $ret .= '	</tr>'.$nl;
	    }
	    $ret .= '	</tbody>'.$nl;
	    
	    return $ret;
	}
	
	private function criarCelulaImpDadosCubo($campo, $index_campo, $num_linhas, $valor){
	    //////////////////////////////////////////
	    //corrigir caso n tenha o campo e o index do mesmo
	    if($campo === '' && $index_campo === ''){
	        return '';
	    }
	    if($campo === ''){
	        $campo = $this->_campos[$index_campo];
	    }
	    elseif($index_campo === ''){
	        foreach ($this->_campos as $index => $campo_foreach){
	            if($campo_foreach === $campo){
	                $index_campo = $index;
	            }
	        }
	    }
	    /////////////////////////////////////
	    global $nl;
	    $sort = '';
	    $tipo = $this->_cabTipo[$index_campo];
	    $valorCampo = $valor;
	    switch ($tipo){
	        case "V":
	            //Valor (duas casas decimais)
	            if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
	            if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
	                $valorCampo = number_format($valorCampo, 2, ',', '.');
	            }
	            break;
	        case "V4":
	            //Valor (quatro casas decimais)
	            if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
	            if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
	                $valorCampo = number_format($valorCampo, 4, ',', '.');
	            }
	            break;
	        case "N":
	            //Valor inteiro
	            if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
	            if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
	                $valorCampo = number_format($valorCampo, 0, ',', '.');
	            }
	            break;
	        case "T":
	            //Valor inteiro
	            $valorCampo = ajustaCaractHTML($valorCampo);
	            break;
	        case "D":
	            //Data
	            $sort = 'data-sort="'.$valorCampo.'"';
	            if($valorCampo != '' && $valorCampo != 0){
	                $valorCampo = datas::dataS2D($valorCampo);
	            }
	            break;
	    }
	    switch (strtoupper($this->_cabPosicao[$index_campo])) {
	        case "D":
	            $pos = 'right';
	            break;
	        case "direita":
	            $pos = 'right';
	            break;
	        case "C":
	            $pos = 'center';
	            break;
	        case "centro":
	            $pos = 'center';
	            break;
	        case "J":
	            $pos = 'justify';
	            break;
	        case "justificado":
	            $pos = 'justify';
	            break;
	        default:
	            $pos = 'left';
	            break;
	    }
	    $w = '';
	    if($this->_cabWidth[$index_campo] > 0){
	        $w = ' width="'.$this->_cabWidth[$index_campo].'"';
	    }
	    
	    $cor = '';
	    /*
	    if(!empty($this->_colunaCor) && isset($this->_cores[$this->_dados[$l][$this->_colunaCor]])){
	        $cor = "bgcolor='".$this->_cores[$this->_dados[$l][$this->_colunaCor]]."'";
	    }elseif(isset($this->_coresColunas[$i])){
	        $cor = "bgcolor='".$this->_coresColunas[$i]."'";
	    }
	    
	    //Se a coluna tiver uma cor especifica condicional
	    //$this->_coresColunasIf[$coluna] = $controle;
	    //$this->_coresColunasCondicional[$coluna][$key] = $cor;
	    
	    if(isset($this->_coresColunasIf[$i]) && isset($this->_coresColunasCondicional[$i][$this->_dados[$l][$this->_coresColunasIf[$i]]])){
	        $cor = "bgcolor='".$this->_coresColunasCondicional[$i][$this->_dados[$l][$this->_coresColunasIf[$i]]]."'";
	    }
	    */
	    
	    return '		<td rowspan="' . $num_linhas . '"align="'.$pos.'"'.$w.' '.$this->_nowrap.' '.$cor.' '.$sort.'>'.$valorCampo.'</td>'.$nl;
	}
	
	private function montarLinhasImpCubo($dados, $campos){
	    $c = getProxCampo($campos);
	    $ret = array();
	    if(count($campos) > 0){
	        foreach ($dados as $valor => $proximo_nivel){
	            $ret_prox_nivel = $this->montarLinhasImpCubo($proximo_nivel, $campos);
	            $num_colunas = count($ret_prox_nivel);
	            foreach ($ret_prox_nivel as $index => $r){
	                $temp = $r;
	                if($index == 0){
	                    $temppp = array($this->criarCelulaImpDadosCubo($c, '', $num_colunas, $valor));
	                    $temp = array_merge($temppp, $temp);
	                }
	                $ret[] = $temp;
	            }
	        }
	    }
	    else{
	        foreach ($dados as $d){
	            $temp = array();
	            //$temp[] = "<td> $d </td>";
	            $temp[] = $this->criarCelulaImpDadosCubo($c, '', 1, $d);
	            //$temp[] = "</tr>";
	            $ret[] = $temp;
	        }
	    }
	    return $ret;
	}
	
	private function montarBucketCubo($dados, $campo){
	    $ret = array();
	    foreach ($dados as $d){
	        $valor = $d[$campo];
	        unset($d[$campo]);
	        $ret[$valor][] = $d;
	    }
	    return $ret;
	}
	
	private function montarDadosCubo($dados, $campos){
	    $c = getProxCampo($campos);
	    $ret = array();
	    if(count($campos) > 0){
	        $balde = $this->montarBucketCubo($dados, $c);
	        foreach ($balde as $index => $b){
	            $ret[$index] = $this->montarDadosCubo($b, $campos);
	        }
	        ksort($ret);
	    }
	    else{
	        foreach ($dados as $d){
	            $ret[] = $d[$c];
	        }
	        asort($ret);
	    }
	    return $ret;
	}
	
	private function limparDadosCubo($dados){
	    $ret = array();
	    foreach ($dados as $d){
	        $temp = array();
	        foreach ($this->_campos as $campo){
	            $temp[$campo] = $d[$campo];
	        }
	        $ret[] = $temp;
	    }
	    return $ret;
	}
	
	
}

function getProxCampo(&$campos){
    if(count($campos) > 0){
        return array_shift($campos);
    }
    else{
        return '';
    }
}