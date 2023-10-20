<?php
/*
 * Data Criacao: 13/03/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Classe relatório
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

#[\AllowDynamicProperties]
class relatorio01{
	
	//Nome do Programa
	protected $_programa;
	
	//Icone
	protected $_icone;
	
	//Titulo
	protected $_titulo;
	
	// Botões no titulo
	protected $_botaoTitulo = [];
	
	//Filtro
	protected $_filtro;
	
	//Indica se deve mostrar filtro
	protected $_mostraFiltro;
	
	//Indica o tipo de filtro a ser utilizado
	protected $_tipoFiltro;
	
	// Indica se exporta os dados para EXCEL
	protected $_toExcel;
	
	// Indica se o botao excel ja foi adicionado
	protected $_btExcel = false;
	
	//Indica se deve imprimir o cabecalho da tabela ou direto os dados
	protected $_impCab = true;
	
	//Botão cancela e link cancela
	protected $_btCancela;
	protected $_linkCarcela = '';
	
	//Indica se será usado o botão de configuração da sys020
	protected $_botaoConfigura;
	
	//Indica se e uma execucao automatica
	protected $_auto;
	
	// Indica a quantidade de linhas do relatorio
	protected $_quantDados = [];
	
	// Indica de a tabela deve ser incluída no corpo do email (caso negativo só vai a planilha)
	protected $_enviaTabelaCorpoEmail;
	
	//Indica o texto que deve ser impresso caso nao existam dados a serem exibidos
	protected $_textoSemDados;
	
	//Mensagem a ser incluida no inicio do email
	protected $_mensagem_inicio_email = '';
	
	// Rodape
	protected $_footer = '';
	
	// Indica se os botões do Título vão ser separados ou dropDown
	protected $_dropDown = false;
	
	
	protected $_formTabela = array();
	//-------------------------------------------------------------------------- Tabela
	protected $_browser = [];
	
	//Parametros das tabelas criadas
	protected $_paramTabela;
	
	//Texto do botão de exportar planilha
	protected $_textoExportaPlanilha;
	
	//-------------------------------------------------------------------------- Secoes
	//Titulo Secoes
	protected $_tituloSecao = [];
	protected $_tituloSecaoPDF = [];
	
	//-------------------------------------------------------------------------- Parametros
	protected $_sys020;
	
	//--------------------------------------------------- PDF -------------------------------------
	
	//Indica se exporta dos dados para PDF
	protected $_toPDF = false;
	
	//Nome do arquivo PDF a ser criado
	protected $_arqPDF = '';
	
	//Link para o arquivo PDF
	protected $_linkPDF = '';
	
	//Header do PDF
	protected $_headerPDF = '';
	
	//Altura Header PDF
	protected $_headerAltPDF = 7;
	
	//Paginação no PDF
	protected $_paginacaoPDF = true;
	
	//Dados do cabeçalho PDF
	protected $_cabPDF = [];
	
	//Indica se o pdf vai ser listrado
	protected $_stripePDF = ['stripe' => false];
	
	//Cabeçalho do pdf
	protected $_cabecalhoPDF = '';
	
	//print
	protected $_print;
	
	//Texto a ser incluido antes da tabela
	protected $_textoEmail = '';
	
	//--------------------------------------------------- EXCEL -------------------------------------
	protected $_headerExcel = [];
	
	public function __construct($param = []){
		$this->_programa	= $param['programa'] ?? '';
		$this->_titulo 		= $param['titulo'] ?? '';
		$this->_icone 		= $param['icone'] ?? '';
		
		$this->_sys020 		= new sys020();
		
		$this->_toExcel 	= $param['toExcel'] ?? false;
		
		$this->_impCab		= $param['imprimeCabecalho'] ?? true;
		
		//------------------------------------------------------------------------------------------------------- Filtro -------------------
		$this->_mostraFiltro = $param['filtro'] ?? true;
		$this->_tipoFiltro = $param['filtroTipo'] ?? 1;
		$paramFiltro = [];
		$paramFiltro['tamanho'] = 12;
		if(isset($param['colunasForm'])){
			$paramFiltro['colunas'] = $param['colunasForm'];
		}
		$paramFiltro['carregaRespostas'] = $param['carregaRespostas'] ?? true;
		if(!$this->_mostraFiltro){
			$paramFiltro['carregaRespostas'] = false;
		}
		
		if(isset($param['link'])){
		    $paramFiltro['link'] = $param['link'];
		}
		if($this->_tipoFiltro == 1){
		    $this->_filtro = new formFiltro01($this->_programa, $paramFiltro);
		}
		elseif($this->_tipoFiltro == 2){
		    $this->_filtro = new formFiltro02($this->_programa, $paramFiltro);
		}
		
		
		$this->_btCancela 		= $param['cancela'] ?? false;
		$this->_linkCarcela 	= $param['cancelaLink'] ?? getLink().'index';
		$this->_botaoConfigura 	= $param['configuraSys020'] ?? false;
		
		$this->_textoExportaPlanilha = $param['textoBotaoExportaPlanilha'] ?? 'Planilha';
		
		$this->_auto 					= $param['auto'] ?? false;
		$this->_print 					= $param['print'] ?? true;
		$this->_enviaTabelaCorpoEmail 	= $param['enviaTabelaEmail'] ?? true;
		$this->_mensagem_inicio_email	= $param['mensagem_inicio_email'] ?? 'Este é um e-mail automatizado, não responda a ele.';
	}
	
	public function __toString(){
		$ret = '';
		
		$operacao = getOperacao();
		$filtro = '';
		$quantDados = 0;
		if($operacao == 'sysParametros'){
			$ret .= $this->_sys020->formulario($this->_programa, $this->_titulo);
		}elseif($operacao == 'sysParametrosGravar'){
			$ret .= $this->_sys020->gravaFormulario($this->_programa);
			addPortalMensagem('', 'Configurações alteradas com sucesso!');
			$ret = '';
		}
		
		if(empty($ret)){
			// Mostra o relatório (não é alterar parametros)
			if(count($this->_quantDados) > 0){
				foreach ($this->_quantDados as $quant){
					$quantDados += $quant;
				}
			}
			
			// Se existir dados a serem mostrados esconde o filtro
			if($quantDados > 0){
			    if($this->_tipoFiltro == 1){
				    addPortalJquery("$('#formFiltro').hide();");
			    }
			}
			else{
			    if($this->_tipoFiltro == 2){
			        addPortalJquery("$('#bt_form').ControlSidebar('toggle');", 'F');
			    }
			}
			
			if($this->_mostraFiltro){
				$filtro .= $this->_filtro;
			}
			
			if($this->_toPDF && !$this->_auto && $quantDados > 0){
				$botao = [];
				$botao['onclick']= 'window.open(\''.$this->_linkPDF.'\')';
				$botao['texto']	= 'PDF';
				$botao['id'] = 'bt_pdf';
				if($this->_dropDown && count($this->_botaoTitulo) > 0){
				    $this->_botaoTitulo[] = ['separador' => true];
				}
				$this->_botaoTitulo[] = $botao;
			}
			//print_r($this->_campos);
			if($this->_toExcel && !$this->_auto && $quantDados > 0){
				$excel = new excel02($this->_arqExcel);
				if(is_array($this->_headerExcel) && count($this->_headerExcel) > 0){
				    $excel->setCabecalho($this->_headerExcel);
				}
				$primeira_worksheet = '';
				foreach ($this->_browser as $secao => $tabela){
				    if($primeira_worksheet === ''){
				        $primeira_worksheet = $secao;
				    }
					if(isset($this->_tituloSecao[$secao]['worksheet'])){
						$excel->addWorksheet($secao, $this->_tituloSecao[$secao]['worksheet']);
					}else{
						$excel->addWorksheet($secao, 'Planilha '.$secao);
					}
					$dadosExcel = $tabela->getDados();
					//Adiciona o total a tabela excel
					if(isset($this->_dadosTfoot[$secao])){
						$dadosExcel[] = $this->_dadosTfoot[$secao];
					}
					$excel->setDados($this->_cab[$secao], $dadosExcel, $this->_campo[$secao],$this->_tipo[$secao]);
				}
				if($primeira_worksheet !== ''){
				    //seta a WS ativa para a primeira seção
				    $excel->setWSAtiva($primeira_worksheet);
				}
				//$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
				//$excel->grava();
				$excel->grava();
				unset($excel);
				
				if(!$this->_btExcel){
					$botao = [];
					$botao['onclick']= 'window.open(\''.$this->_linkExcel.'\')';
					$botao['texto']	= $this->_textoExportaPlanilha;
					$botao['id'] = 'bt_excel';
					if($this->_dropDown && count($this->_botaoTitulo) > 0 && !$this->_toPDF){
					    $this->_botaoTitulo[] = ['separador' => true];
					}
					$this->_botaoTitulo[] = $botao;
					$this->_btExcel = true;
				}
			}
			
			if($this->_filtro->getQuantPerguntas() > 0 && !$this->_auto && $this->_mostraFiltro){
			    if($this->_tipoFiltro == 1){
			        $botao = [];
			        $botao["onclick"]= "$('#formFiltro').toggle();";
			        $botao["texto"]	= "Par&acirc;metros";
			        $botao["id"] = "bt_form";
			        $this->_botaoTitulo[] = $botao;
			    }
			    if($this->_tipoFiltro == 2){
			        $botao = [];
			        $botao["data-widget"]= "control-sidebar";
			        $botao["texto"]	= "Par&acirc;metros";
			        $botao["id"] = "bt_form";
			        $this->_botaoTitulo[] = $botao;
			    }
				
				$ret .= $filtro;
			}
			
			if($this->_botaoConfigura){
				$botao = [];
				$botao['onclick']= 'setLocation(\''.getLink().'index.sysParametros\')';
				$botao['texto']	= 'Configurações';
				$botao['id'] = 'btConfigurar';
				$botao['icone'] = 'fa-cog';
				$this->_botaoTitulo[] = $botao;
			}
			
			if($this->_btCancela){
				$botao = [];
				$botao['onclick'] = "setLocation('".$this->_linkCarcela."')";
				//$botao['tamanho'] = 'pequeno';
				$botao['cor'] = 'danger';
				$botao['texto'] = 'Cancelar';
				$botao['id'] = 'bt_cancela';
				$this->_botaoTitulo[] = $botao;
			}
			
			//print_r($this->_tituloSecao);
			$retTemp = '';
			if(count($this->_browser ) > 0){
				foreach ($this->_browser as $secao => $tabela){
					if(isset($this->_tituloSecao[$secao]['titulo'])){
					    $retTemp .= '<h2>'.$this->_tituloSecao[$secao]['titulo'];
						if(isset($this->_tituloSecao[$secao]['sub'])){
						    $retTemp .= '<small>'.$this->_tituloSecao[$secao]['sub'].'</small>';
						}
						$retTemp .= '</h2>'."\n";
					}
					$retTemp .= $tabela;
				}
			}
			
			if(is_array($this->_formTabela) && count($this->_formTabela) > 0){
			    $retTemp = formbase01::form($this->_formTabela, $retTemp);
			}
			
			$ret .= $retTemp;
			
			if($this->_toPDF && !$this->_auto && $quantDados > 0){
				$htmlPDF = '';
				$paramTabPdf = [];
				
				foreach ($this->_browser as $secao => $tabela){
					$tabPdf = new tabela_pdf($paramTabPdf);
					
					if($this->_stripePDF['stripe'] === true){
						$tabPdf->setStripe(true);
						if(isset($this->_stripePDF['cor1']) && !empty($this->_stripePDF['cor1']) && isset($this->_stripePDF['cor2']) && !empty($this->_stripePDF['cor2'])){
							$tabPdf->setCorStripe($this->_stripePDF['cor1'], $this->_stripePDF['cor2']);
						}
					}
					
					$titulo =  isset($this->_tituloSecaoPDF[$secao]['titulo']) ? $this->_tituloSecaoPDF[$secao]['titulo'] : '';
					$sub =  isset($this->_tituloSecaoPDF[$secao]['sub']) ? $this->_tituloSecaoPDF[$secao]['sub'] : '';
					$tabPdf->setTitulo($titulo, $sub);
					
					
					$dadosTabela = $tabela->getDados();
					//Adiciona o total a tabela
					if(isset($this->_dadosTfoot[$secao])){
						$tabPdf->setDadosTotais($this->_dadosTfoot[$secao]);
					}
					$tabPdf->setTabela($this->_campo[$secao], $this->_cab[$secao], $this->_width[$secao], $this->_posicao[$secao], $this->_tipo[$secao]);
					$tabPdf->setDados($dadosTabela);
					$tabPdf->setFooter($this->_footer);
					
					$htmlPDF .= $tabPdf;
				}
				//echo "<br><br><br><br>$htmlPDF<br><br><br><br>";
				
				$paramPDF = [];
				$paramPDF['orientacao'] = 'L';
				$PDF = new pdf_exporta($paramPDF);
				if($this->_cabecalhoPDF != ''){
				    $htmlPDF = $this->_cabecalhoPDF . $htmlPDF;
				}
				$PDF->setHTML($htmlPDF);
				if(count($this->_cabPDF) > 0){
					$PDF->setHeader($this->getHeaderPDF(), $this->_cabPDF['altura']);
				}elseif(!empty($this->_headerPDF)){
					$PDF->setHeader($this->_headerPDF, $this->_headerAltPDF);
				}
				$PDF->grava( $this->_arqPDF);
				unset($PDF);
				
			}
			
			if($quantDados <= 0){
				if(!empty($this->_textoSemDados)){
					$ret .= $this->_textoSemDados;
				}else{
					$ret .= "Nao existem dados!";
				}
			}
			
			$param = [];
			if(count($this->_botaoTitulo) > 0){
				foreach ($this->_botaoTitulo as $botao){
					$param['botoesTitulo'][] = $botao;
				}
			}
			
			$param['titulo'] = $this->_titulo;
			$param['conteudo'] = $ret;
			$param['icone'] = $this->_icone;
			$param['botoesTituloDropDown'] = $this->_dropDown;
			$ret = addCard($param);
		}
		
		return $ret;
	}
	
	function __destruct(){
		unset($this->_browser);
		unset($this->_filtro);
	}
	
	
	//-------------------------------------------------------------------------------------- UI --------------------------------------
	
	//-------------------------------------------------------------------------------------- GET -------------------------------------
	
	public function getFiltro(){
		//return $this->_filtro->getRetorno();
		return $this->_filtro->getFiltro();
	}
	
	/**
	 * Retorna se é a primeira execucao do filtro
	 */
	public function getPrimeira(){
		return $this->_filtro->getPrimeira();
	}
	
	public function getCampos($secao = 0){
        return $this->_campo[$secao] ?? [];
	}
	
	public function getConfigCampos($secao = 0){
	    $ret = [];
	    
	    $ret['etiqueta'] 	= $this->_cab[$secao] ?? [];
	    $ret['tipo'] 		= $this->_tipo[$secao] ?? [];
	    $ret['campo'] 		= $this->_campo[$secao] ?? [];
	    $ret['width'] 		= $this->_width[$secao] ?? [];
	    $ret['posicao'] 	= $this->_posicao[$secao] ?? [];
	    
	    return $ret;
	}
	//-------------------------------------------------------------------------------------- SET -------------------------------------
	
	public function setNowrap($now, $secao = 0){
		if($now){
			if(isset($this->_browser[$secao])){
				$this->_browser[$secao]->setNowrap(true);
			}
		}else{
			if(isset($this->_browser[$secao])){
				$this->_browser[$secao]->setNowrap(false);
			}
		}
	}
	
	public function setParamTabela($param = [], $secao = 0){
		$param['paginacao'] 	    = $param['paginacao']	?? false;
		$param['scroll'] 		    = $param['scroll']		?? true;
		$param['scrollX']		    = $param['scrollX']		?? true;
		$param['scrollY']		    = $param['scrollY']		?? true;
		$param['imprimeZero'] 	    = $param['imprimeZero']	?? true;
		$param['width'] 		    = $param['width']		?? 'AUTO';
		$param['filtro']		    = $param['filtro']		?? true;
		$param['info']			    = $param['info']		?? true;
		$param['ordenacao']		    = $param['ordenacao']	?? true;
		$param['print']             = $param['print']       ?? $this->_print;
		$param['acoes']			    = $param['acoes']		?? [];
		//Indica se deve imprimir o cabecalho da tabela ou direto os dados
		$param['imprimeCabecalho']	= $param['impCab']		?? $this->_impCab;
		$this->_paramTabela[$secao] = $param;
	}
	
	public function setDados(&$dados, $secao = 0){
		$this->_quantDados[$secao] = count($dados);
		if(!isset($this->_browser[$secao])){
			$this->_paramTabela[$secao] = isset($this->_paramTabela[$secao]) ? $this->_paramTabela[$secao] : $this->_paramTabela[0];
			$this->_browser[$secao] = new tabela01($this->_paramTabela[$secao]);
			$this->_browser[$secao]->setImprimeZero($this->_paramTabela[$secao]['imprimeZero']);
			$this->_browser[$secao]->setAuto($this->_auto);
			foreach ($this->_colunas as $coluna){
				$this->addColuna($coluna, $secao);
			}
		}
		$this->_browser[$secao]->setDados($dados);
	}
	
	/**
	 * Indica se sera gerado arquivo excel deste relatorio.
	 * No caso de shedule pode ser indicado o nome do arquivo.
	 * Se nao for indicado o nome sera cliente + usuario + programa
	 */
	public function setToExcel($excel,$nome = '', $path = false){
		global $config;
		
		if($excel){
			$this->_toExcel = true;

			if($nome == ""){
				$arquivo = $this->_programa.".xlsx";
				if($this->_auto === false && !$path){
					$arquivo = getUsuario().".".$arquivo;
				}
			}else{
				$arquivo = $nome.".xlsx";
			}
			
			if(!$path){
				$this->_arqExcel = $config['tempPach'].$arquivo;
//echo "Arquivo1 : ".$this->_arqExcel." <br>\n";
				$this->_linkExcel = $config['tempURL'].$arquivo;
//echo "Arquivo2 : ".$this->_linkExcel." <br>\n";
			}else{
				$this->_arqExcel = $arquivo;
				$this->_linkExcel = '';
			}
		}else{
			$this->_toExcel = false;
		}
		
	}
	
	
	public function setToPDF($pdf,$nome = ''){
		global $config;
		
		if($pdf){
			$this->_toPDF = true;
		}else{
			$this->_toPDF = false;
		}
		
		if($nome == ''){
			$arquivo = getUsuario().".".$this->_programa.".pdf";
		}else{
			$arquivo = $nome.".pdf";
		}
		$this->_arqPDF = $config['tempPach'].$arquivo;
		//echo "Arquivo1 : ".$this->_arqPDF." <br>\n";
		$this->_linkPDF = $config["tempURL"].$arquivo;
		//echo "Arquivo2 : ".$this->_linkPDF." <br>\n";
	}
	
	public function setColunaCor($coluna, $secao = 0){
		if(!empty($coluna)){
			$this->_colunaCor[$secao] = $coluna;
		}
	}
	
	public function setCorColunas($coluna, $cor, $secao = 0){
		$this->_coresColunas[$secao][$coluna] = $cor;
	}
	
	//----------------Cor Coluna condicional
	public function addCorColunaIf($coluna, $key, $cor, $secao = 0){
		if(!empty($coluna) && !empty($key) && !empty($cor)){
			$this->_coresColunasCondicional[$secao][$coluna][$key] = $cor;
		}
	}
	
	public function setColunaCorIf($coluna, $controle, $secao = 0){
		if(!empty($coluna) && !empty($controle)){
			$this->_coresColunasIf[$secao][$coluna] = $controle;
		}
	}
	
	public function setFiltroLink($link){
		if(!empty($link)){
			$this->_filtro->setLink($link);
		}
	}
	
	/**
	 * Seta se o relatorio esta sendo executado automaticamente
	 *
	 * @param	boolean	$auto	Indica se � um relat�rio autom�tico
	 * @return	void
	 */
	public function setAuto($auto){
		$this->_auto = $auto === true ? true : false;
		if(count($this->_browser) > 0){
			foreach ($this->_browser as $secao => $b){
				$this->_browser[$secao]->setAuto($this->_auto);
			}
		}
	}
	
	/**
	 * Indica se deve enviar a tabela no corpo do email (por padrão TRUE)
	 */
	public function setEnviaTabelaEmail($envia){
		if(!$envia){
			$this->_enviaTabelaCorpoEmail = false;
		}else{
			$this->_enviaTabelaCorpoEmail = true;
		}
	}
	
	/**
	 * Seta um texto alternativo a ser enviado no email o exibido na tela caso nao existam dados a ser exibidos
	 *
	 * @param	string	$texto	Texto a ser informado caso n�o existam dados
	 * @return	void
	 */
	public function setTextoSemDados($texto){
		if($texto != ""){
			$this->_textoSemDados = $texto;
		}
	}
	
	/**
	 * Mensagem a ser impressa no inicio do email
	 * 
	 * @param string $msg Mensagem a ser incluida (texto/HTML)
	 */
	public function setMensagemInicioEmail($msg){
		$this->_mensagem_inicio_email = $msg;
	}
	
	public function setTitulo($titulo, $icone = ''){
		$this->_titulo = $titulo;
		$this->_icone = $icone;
	}
	
	public function setFooter($texto, $secao = 0){
		$this->_footer = $texto;
		$this->_browser[$secao]->setFooter($texto);
	}
	
	public function setTituloSecao($secao, $titulo, $subTitulo = ''){
		//echo "$secao - $titulo - $subTitulo <br>\n";
		if(!empty($titulo)){
			$this->_tituloSecao[$secao]['titulo'] = $titulo;
			$this->_tituloSecao[$secao]['sub'] = $subTitulo;
			
			$this->setTituloSecaoPDF($titulo, $subTitulo, $secao);
		}
	}
	
	public function setTituloSecaoPDF($titulo, $subTitulo = '', $secao = 0){
		//echo "$secao - $titulo - $subTitulo <br>\n";
		if(!empty($titulo)){
			$this->_tituloSecaoPDF[$secao]['titulo'] = $titulo;
			$this->_tituloSecaoPDF[$secao]['sub'] = $subTitulo;
		}
	}
	
	/**
	 * Determina o nome das Worksheet na planilha excel
	 *
	 * @param mixed  $secao 	- Identificação da Secao
	 * @param string $titulo	- Título da Worksheet
	 */
	public function setTituloSecaoPlanilha($secao, $titulo){
		//echo "$secao - $titulo - $subTitulo <br>\n";
		if(!empty($titulo)){
			$this->_tituloSecao[$secao]['worksheet'] = $titulo;
		}
	}
	
	/**
	 * 
	 * @param boolean $stripe - Indica se o PDF vai ser listrado
	 * @param string $cor1 - Cor das linhas pares
	 * @param string $cor2 - Cor das linhas impares
	 */
	public function setPdfStripe($stripe, $cor1 = '', $cor2 = ''){
		$this->_stripePDF['stripe'] = $stripe === true ? true : false;
		if(!empty($cor1)){
			$this->_stripePDF['cor1'] = $cor1;
		}
		if(!empty($cor2)){
			$this->_stripePDF['cor1'] = $cor2;
		}
	}
	
	public function setTextoEmail($texto){
		$this->_textoEmail = $texto;
	}
	
	public function setHeaderPdf($html, $altura = 7){
	    $this->_headerPDF = $html;
	    $this->_headerAltPDF = $altura;
	}
	
	public function setHeaderExcel($dadosHeaderExcel){
	    $this->_headerExcel = $dadosHeaderExcel;
	}
	
	public function setCabecalhoPdf($html){
	    $this->_cabecalhoPDF = $html;
	}
	
	public function setPrint($print){
		$this->_print = $print === false ? false : true;
		
		if(count($this->_browser) > 0){
			foreach ($this->_browser as $i => $broser){
				$this->_browser[$i]->setPrint($this->_print);
			}
		}
	}
	//-------------------------------------------------------------------------------------- ADD -------------------------------------
	
	public function addBotao($param){
		$this->_botaoTitulo[] = $param;
	}
	
	public function addColuna($param, $secao = 0){
		if(!isset($this->_browser[$secao])){
			if(!isset($this->_paramTabela[$secao])){
				if(!isset($this->_paramTabela[0])){
					$this->setParamTabela([], $secao);
				}else{
					//Se não foi indicado parametros para a tabela atual, pega a da [0]
					$this->setParamTabela($this->_paramTabela[0], $secao);
				}
			}
			$this->_browser[$secao] = new tabela01($this->_paramTabela[$secao]);
			$this->_browser[$secao]->setImprimeZero($this->_paramTabela[$secao]['imprimeZero']);
			if(isset($this->_cores[$secao]) && count($this->_cores[$secao]) > 0){
				foreach ($this->_cores[$secao] as $key => $cor){
					$this->_browser[$secao]->addCorLinha($key, $cor);
				}
			}
			if(isset($this->_colunaCor[$secao])){
				$this->_browser[$secao]->setColunaCor($this->_colunaCor[$secao]);
			}
			if(isset($this->_coresColunas[$secao]) && count($this->_coresColunas[$secao]) > 0){
				foreach ($this->_coresColunas[$secao] as $key => $cor){
					$this->_browser[$secao]->setCorColunas($key, $cor);
				}
			}
			
			//Cores coluna Condicioal (somente coluna)
			if(isset($this->_coresColunasCondicional[$secao])){
				foreach ($this->_coresColunasCondicional[$secao] as $coluna => $temp){
					foreach ($temp as $key => $cor){
						$this->_browser[$secao]->addCorColunaIf($coluna, $key, $cor);
					}
				}
			}
			
			if(isset($this->_coresColunasIf[$secao])){
				foreach ($this->_coresColunasIf[$secao] as $coluna => $controle){
					$this->_browser[$secao]->setColunaCorIf($coluna, $controle);
				}
			}
			
		}
		//Guarda as colunas para setar secções não informadas (repete)
		if($secao == 0){
			$this->_colunas[] = $param;
		}
		$this->_browser[$secao]->addColuna($param);
		$this->_cab[$secao][] = $param['etiqueta'];
		$this->_tipo[$secao][] = $param['tipo'];
		$this->_campo[$secao][] = $param['campo'];
		$this->_width[$secao][] = $param['width'];
		$this->_posicao[$secao][] = $param['posicao'];
	}
	
	//-------------------------------------------------------------------------------------- UTEIS -----------------------------------
	
	public function enviaEmail($para, $titulo = '', $param = []){
		$de 			= $param['de'] ?? [];
		$bcc			= $param['copiaOculta'] ?? '';
		$emailsender	= $param['emailsender'] ?? [];
		$embeddedImage	= $param['embeddedImage'] ?? [];
		$responderPara	= $param['responderPara'] ?? [];
		$agendado		= $param['agendado'] ?? false;
		$dia			= $param['dia'] ?? '';
		$hora			= $param['hora'] ?? '08:00';
		
		$msgIni			= $param['msgIni'] ?? '';
		$msgFim			= $param['msgFim'] ?? '';
		$mensagem 		=  $param['mensagem'] ?? '';
		
		$anexos = [];
		$msg = '';
		
		if(!empty($msgIni)){
			$msg = $msgIni;
		}
		
		if(!empty($this->_textoEmail)){
			$msg .= $this->_textoEmail;
		}
		
		//Passa as tabelas como AUTO 
		if(count($this->_browser) > 0){
			foreach ($this->_browser as $secao => $b){
				$this->_browser[$secao]->setAuto(true);
			}
		}
		
		if($this->_toExcel && $this->_quantDados[0] > 0){
			
//			$excel = new excel01($this->_arqExcel);
//			$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
//			$excel->grava();
//			$anexos[] = $this->_arqExcel;
//			unset($excel);
			
			$excel = new excel02($this->_arqExcel);
			if(is_array($this->_headerExcel) && count($this->_headerExcel) > 0){
			    $excel->setCabecalho($this->_headerExcel);
			}
			$primeira_worksheet = '';
			foreach ($this->_browser as $secao => $tabela){
			    if($primeira_worksheet === ''){
			        $primeira_worksheet = $secao;
			    }
				if(isset($this->_tituloSecao[$secao]['worksheet'])){
					$excel->addWorksheet($secao, $this->_tituloSecao[$secao]['worksheet']);
				}else{
					$excel->addWorksheet($secao, 'Planilha '.$secao);
				}
				$dadosExcel = $tabela->getDados();
				//Adiciona o total a tabela excel
				if(isset($this->_dadosTfoot[$secao])){
					$dadosExcel[] = $this->_dadosTfoot[$secao];
				}
				$excel->setDados($this->_cab[$secao], $dadosExcel, $this->_campo[$secao],$this->_tipo[$secao]);
			}
			if($primeira_worksheet !== ''){
			    //seta a WS ativa para a primeira seção
			    $excel->setWSAtiva($primeira_worksheet);
			}
			//$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
			//$excel->grava();
			$excel->grava();
			unset($excel);

			$anexos[] = $this->_arqExcel;
		}
		
		
		if($this->_toPDF && $this->_quantDados[0] > 0){
			$htmlPDF = '';
			$paramTabPdf = [];
			
			foreach ($this->_browser as $secao => $tabela){
				$tabPdf = new tabela_pdf($paramTabPdf);
				
				$titulo =  isset($this->_tituloSecaoPDF[$secao]['titulo']) ? $this->_tituloSecaoPDF[$secao]['titulo'] : '';
				$sub =  isset($this->_tituloSecaoPDF[$secao]['sub']) ? $this->_tituloSecaoPDF[$secao]['sub'] : '';
				$tabPdf->setTitulo($titulo, $sub);
				
				
				$dadosTabela = $tabela->getDados();
				//Adiciona o total a tabela
				if(isset($this->_dadosTfoot[$secao])){
					$tabPdf->setDadosTotais($this->_dadosTfoot[$secao]);
				}
				$tabPdf->setTabela($this->_campo[$secao], $this->_cab[$secao], $this->_width[$secao], $this->_posicao[$secao], $this->_tipo[$secao]);
				$tabPdf->setDados($dadosTabela);
				$tabPdf->setFooter($this->_footer);
				
				$htmlPDF .= $tabPdf;
			}
			//echo "<br><br><br><br>$htmlPDF<br><br><br><br>";
			
			$paramPDF = [];
			$paramPDF['orientacao'] = 'L';
			$PDF = new pdf_exporta($paramPDF);
			if($this->_cabecalhoPDF != ''){
			    $htmlPDF = $this->_cabecalhoPDF . $htmlPDF;
			}
			$PDF->setHTML($htmlPDF);
			if(count($this->_cabPDF) > 0){
				$PDF->setHeader($this->getHeaderPDF(), $this->_cabPDF['altura']);
			}elseif(!empty($this->_headerPDF)){
				$PDF->setHeader($this->_headerPDF, $this->_headerAltPDF);
			}
			$PDF->grava( $this->_arqPDF);
			$anexos[] = $this->_arqPDF;
			unset($PDF);
			
		}
		
		if($titulo == ""){
			$titulo = $this->_titulo;
		}
		
		if(!empty($this->_mensagem_inicio_email)){
			$msg .= $this->_mensagem_inicio_email;
		}
		
		if(count($this->_browser ) > 0){
			foreach ($this->_browser as $secao => $tabela){
				if(isset($this->_tituloSecao[$secao]['titulo'])){
					$msg .= '<h2>'.$this->_tituloSecao[$secao]['titulo'];
					if(isset($this->_tituloSecao[$secao]['sub'])){
						$msg .= '<small>'.$this->_tituloSecao[$secao]['sub'].'</small>';
					}
					$msg .= '</h2>'."\n";
				}
				$msg .= $tabela;
			}
		}
		
		//Se o email ficar muito grande ou não envia tabela no corpo do email
		//TODO: mesmo que a mensagem seja grande, mas não for para enviar planilha ou pdf deve ir no corpo da mensagem
		if(strlen($msg) > 2000000 || count($this->_campo) > 56 || $this->_enviaTabelaCorpoEmail == false){
			$msg = "Segue anexo o relatório ".$titulo.".";
		}
		
		if(empty($msg)){
			if(!empty($this->_textoSemDados)){
				$msg = $this->_textoSemDados;
			}else{
				$msg = "Não existem dados!";
			}
		}
		
		if(!empty($msgFim)){
			$msg .= $msgFim;
		}
		
		if(!empty($mensagem)){
			$msg = $mensagem;
		}
		
		
		$param = [];
		$param['emailsender'] 	= $de;
		$param['destinatario'] 	= $para;
		$param['mensagem'] 		= $msg;
		$param['assunto'] 		= $titulo;
		$param['anexos'] 		= $anexos;
		$param['embeddedImage'] = $embeddedImage;
		$param['responderPara'] = $responderPara;
		$param['bcc'] 			= $bcc;
		
		return enviaEmail($param);
	}
    
	public function agendaEmail($dia, $hora, $programa, $para,$titulo = '', $de = '', $msgAntes = '', $copiaOculta = '', $emailsender = [],$embeddedImage = [], $responderPara=[], $teste = false, $compactado = false){
		$anexos = [];
		$msg = '';

		if($this->_toExcel && $this->_quantDados[0] > 0){
			
			//			$excel = new excel01($this->_arqExcel);
			//			$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
			//			$excel->grava();
			//			$anexos[] = $this->_arqExcel;
			//			unset($excel);
			
			$excel = new excel02($this->_arqExcel);
			if(is_array($this->_headerExcel) && count($this->_headerExcel) > 0){
				$excel->setCabecalho($this->_headerExcel);
			}
			$primeira_worksheet = '';
			foreach ($this->_browser as $secao => $tabela){
				if($primeira_worksheet === ''){
					$primeira_worksheet = $secao;
				}
				if(isset($this->_tituloSecao[$secao]['worksheet'])){
					$excel->addWorksheet($secao, $this->_tituloSecao[$secao]['worksheet']);
				}else{
					$excel->addWorksheet($secao, 'Planilha '.$secao);
				}
				$dadosExcel = $tabela->getDados();
				//Adiciona o total a tabela excel
				if(isset($this->_dadosTfoot[$secao])){
					$dadosExcel[] = $this->_dadosTfoot[$secao];
				}
				$excel->setDados($this->_cab[$secao], $dadosExcel, $this->_campo[$secao],$this->_tipo[$secao]);
			}
			if($primeira_worksheet !== ''){
				//seta a WS ativa para a primeira seção
				$excel->setWSAtiva($primeira_worksheet);
			}
			$excel->grava($compactado);
			if($compactado){
				$anexos[] = str_replace('.xlsx', '.zip', $this->_arqExcel);
			}else{
				$anexos[] = $this->_arqExcel;
			}
			unset($excel);
			
			$anexos[] = $this->_arqExcel;
		}
		
		if($titulo == ""){
			$titulo = $this->_titulo;
		}
		
		if($msgAntes != ''){
			$msg .= $msgAntes;
		}
		
		if(!empty($this->_textoEmail)){
			$msg .= $this->_textoEmail;
		}
		
		
		if(count($this->_browser ) > 0){
			foreach ($this->_browser as $secao => $tabela){
				if(isset($this->_tituloSecao[$secao]['titulo'])){
					$msg .= '<h2>'.$this->_tituloSecao[$secao]['titulo'];
					if(isset($this->_tituloSecao[$secao]['sub'])){
						$msg .= '<small>'.$this->_tituloSecao[$secao]['sub'].'</small>';
					}
					$msg .= '</h2>'."\n";
				}
				$msg .= $tabela;
			}
		}
		
		if(strlen($msg) > 2000000 || count($this->_campo) > 56 || $this->_enviaTabelaCorpoEmail == false){
			$msg = "Segue anexo o relatório ".$titulo.".";
		}
		
		if(empty($msg)){
			if(!empty($this->_textoSemDados)){
				$msg = $this->_textoSemDados;
			}else{
				$msg = "Não existem dados!";
			}
		}
		
		/*/
		if(!empty($msgFim)){
			$msg .= $msgFim;
		}
		
		if(!empty($mensagem)){
			$msg = $mensagem;
		}
		/*/
		
		$param = [];
		$param['dia'] 			= $dia;
		$param['hora'] 			= $hora;
		$param['programa']		= $programa;
		$param['mensagem'] 		= $msg;
		$param['destinatario'] 	= $para;
		$param['assunto'] 		= $titulo;
		$param['emailsender']	= $emailsender;
		$param['bcc']			= $copiaOculta;
		$param['teste']			= $teste;
		$param['anexos']		= $anexos;
		$param['embeddedImage'] = $embeddedImage;
		$param['responderPara']	= $responderPara;
		
		agendaEmail($param);
	}
	
	public function copiaExcel($dir, $nomeArquivo){
		$ret = false;
		if(!empty(trim($this->_arqExcel)) && !empty(trim($dir)) && !empty(trim($nomeArquivo))){
			if(substr($dir, -1) != '\\' && substr($dir, -1) != '/'){
				$dir .= '/';
			}
			$ret = copy($this->_arqExcel, $dir.$nomeArquivo);
		}
		
		return $ret;
	}
	
	public function setFormTabela($param){
	    $this->_formTabela = $param;
	}
	
	public function setBotaoDropDownTitulo($drop){
	    $this->_dropDown = $drop;
	}
}