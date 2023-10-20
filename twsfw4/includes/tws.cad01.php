<?php
/*
 * Data Criacao 28/02/2022
 * Autor: TWS - Emanuel Thiel
 *
 * Descricao:
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class cad01
{
	public $funcoes_publicas = array(
		'index' => true,
		'incluir' => true,
		'excluir' => true,
		'editar' => true,
		'salvar' => true,
		'ajax' => true,
		'criarCampoVirtual' => true,
		'salvarCampoVirtual' => true,
		'criarCampoVirtualInfinito' => true,
		'salvarCampoVirtualInfinito' => true,
	);

	//nome da tabela a ser usada
	protected $_tabela;
	//nome do programa
	protected $_programa;
	//titulo do programa
	protected $_titulo;
	//se verdadeiro, em vez de marcar como deletado realmente deleta a entrada na tabela
	protected $_excluir_permanente;
	//se deve ou não mostrar o filtro
	protected $_mostrar_filtro;
	//query a ser usada para gerar os dados
	protected $_query_dados;
	//where usado caso uma query não seja especificada
	protected $_where;
	//campos que vão aparecer na tabela do index
	protected $_camposBrowser;
	//informações da sys002
	protected $_sys002;
	//todos os campos da sys003
	protected $_sys003;
	//todas as colunas da tabela sys003
	protected $_camposSys003;
	//boa pergunta
	protected $_camposSqlBrowser;
	//se true, cria o botão de incluir
	protected $_btIncluir;
	//se true, cria o botão de excluir para cada entrada
	protected $_btExcluir;
	//se true, cria o botão de editar para cada entrada
	protected $_btEditar;
	//se true, mostra o id da entrada na tabela do index
	protected $_mostraID;
	//array com as pastas a serem usadas no formulário
	protected $_pastasFormulario;
	//icone que vai aparecer antes do título
	protected $_iconeTitulo;
	//mostrar mensagens
	protected $_mostrarMensagens;
	//botoes extras da tabela do index
	protected $_botoesExtras;
	//botoes extras do card do index
	protected $_botoesExtrasCard;
    //id da entrada criada no ultimo include
	public $_ultimoIdIncluido;
	//dicionario do browser
	protected $_dicionario_bw = array();
	//filtro do browser
	protected $_filtro;
	//parametros do filtro
	protected $_filtro_param;
	

	function __construct($tabela = '', $param = array())
	{

		$this->_tabela = $tabela;
		
		$this->_ultimoIdIncluido = 0;

		$this->carregar002();
		$this->_camposSys003 = array('campo', 'ordem', 'descricao', 'etiqueta', 'tipo', 'tamanho', 'casas', 'largura', 'linha', 'linhasTA', 'negativo', 'onchange', 'mascara', 'funcao_browser', 'funcao_lista', 'opcoes', 'tabela_itens', 'validacao', 'nivel', 'gatilho', 'browser','campo_chave' ,  'usado', 'obrigatorio', 'editavel', 'real', 'pasta', 'alinhamento', 'tambrowser', 'inicializador', 'help', 'funcao_layout', 'funcao_salvar', 'estilo_form', 'class_form');
		$this->carregar003();
		$this->getPastas();

		$this->_iconeTitulo = getParam($param, 'icone_titulo', '');
		if ($this->_iconeTitulo !== '') {
			if (strpos($this->_iconeTitulo, 'fa-') === false) {
				$this->_iconeTitulo = 'fa-' . $this->_iconeTitulo;
			}
			$this->_sys002['etiqueta'] = addIcone($this->_iconeTitulo) . $this->_sys002['etiqueta'];
		}

		$this->_programa = getParam($param, 'programa', 'cad_' . $tabela);
		$this->_titulo = $this->_sys002['etiqueta'];

		$this->_excluir_permanente = $param['excluir_permanente'] ?? false;
		
		$this->_mostrar_filtro = getParam($param, 'mostrar_filtro', false);
		$this->_filtro_param = getParam($param, 'filtro_param', array());
		$this->_filtro_param['tamanho'] =  $this->_filtro_param['tamanho'] ?? 12;
		$this->_filtro_param['colunas'] =  $this->_filtro_param['colunas'] ?? 2;
		$this->_filtro_param['carregaRespostas'] =  $this->_filtro_param['carregaRespostas'] ?? (true && $this->_mostrar_filtro);

		$this->_query_dados = getParam($param, 'query_dados', '');
		$this->_where = getParam($param, 'where', '');

		$this->_btIncluir = getParam($param, 'btIncluir', true);
		$this->_btExcluir = getParam($param, 'btExcluir', true);
		$this->_btEditar = getParam($param, 'btEditar', true);
		$this->_mostraID = getParam($param, 'mostraID', false);

		$this->_mostrarMensagens = getParam($param, 'mostraMensagens', true);
		
		$this->_botoesExtras = getParam($param, 'botoesExtras', array());
		$this->_botoesExtrasCard = getParam($param, 'botoesCard', array());
		$this->ajustarBotoesExtras();
	}
	
	protected function criarFiltro(){
	    if($this->_mostrar_filtro){
            $this->_filtro = new formfiltro01($this->_programa, $this->_filtro_param);
            if($this->_filtro->getQuantPerguntas() <= 0){
                $this->_filtro = ''; 
            }
	    }
	    else{
            $this->_filtro = '';
	    }
	}

	public function ajax()
	{
		$ret = '';
		$op = getOperacao();


		switch ($op) {
			case 'CampoVirtualInfinito':
				$this->_pastasFormulario = array();
				$indice = getParam($_GET, 'indice', '');
				if ($indice != '') {
					$ret = $this->montarFormulario(array(), 'I', $indice) . '';
				}
				break;
			case 'CampoVirtualInfinitoBotao':
				$indice = getParam($_GET, 'indice', '');
				$numero = getParam($_GET, 'numero', '');
				$ret = $this->montarBotaoExclusaoCampoVirtualInfinito($indice, $numero);
			default:
				break;
		}
		return $ret;
	}

	protected function getPathAjax($op)
	{
		$reflector = new \ReflectionClass(get_class($this));
		$arquivo = $reflector->getFileName();
		$caminho_arquivo_explode = explode('/', $arquivo);
		if (count($caminho_arquivo_explode) < 2) {
			$caminho_arquivo_explode = explode('\\', $arquivo);
		}
		$modulo = '';
		foreach ($caminho_arquivo_explode as $cae) {
			if (strpos($cae, '.php') === false) {
				$modulo = $cae;
			}
		}
		return getLinkAjax($op, false, get_class($this), $modulo);
	}
	
	protected function adicionarBotoesExtras($tabela, $indice){
	    if(isset($this->_botoesExtras[$indice])){
	        foreach ($this->_botoesExtras[$indice] as $bt){
	            $tabela->addAcao($bt);
	        }
	    }
	    return $tabela;
	}
	
	protected function ajustarBotoesExtras(){
	    $ret = array();
	    if(is_array($this->_botoesExtras) && count($this->_botoesExtras) > 0){
	        foreach ($this->_botoesExtras as $bt){
	            $ret[$bt['posicao']][] = $bt;
	        }
	    }
	    $this->_botoesExtras = $ret;
	}
	
	public function index()
	{
	    $this->criarFiltro();
		$param = array('titulo' => $this->_titulo);
		$tabela = $this->criarTabela();
		$tabela = $this->adicionarColunasTabela($tabela);
		$bt_incluir = array();
		if ($this->_btIncluir) {
			$param = [];
			//$param['texto'] 	= traducoes::traduzirTextoDireto('Incluir');
			$param['texto'] 	= 'Incluir';
			$param['link'] 		= getLink() . 'incluir';
			$param['coluna'] 	= 'id64';
			$param['width'] 	= 30;
			$param['flag'] 		= '';
			$param['onclick'] 		= "setLocation('" . getLink() . "incluir')";
			$param['cor'] 		= 'success';
			$bt_incluir = $param;
			//$tabela->addBotaoTitulo($param);
		}
		$tabela = $this->adicionarBotoesExtras($tabela, 'inicio');
		if ($this->_btEditar) {
			$param = [];
			$param['texto'] 	= 'Editar';
			$param['link'] 		= getLink() . 'editar&id=';
			$param['coluna'] 	= 'id64';
			$param['width'] 	= 30;
			$param['flag'] 		= '';
			$param['cor'] 		= 'success';
			$tabela->addAcao($param);
		}
		$tabela = $this->adicionarBotoesExtras($tabela, 'meio');
		if ($this->_btExcluir) {
			$param = [];
			$param['texto'] 	= 'Excluir';
			$param['link'] 		= getLink() . 'excluir&id=';
			$param['coluna'] 	= 'id64';
			$param['width'] 	= 30;
			$param['flag'] 		= '';
			$param['cor'] 		= 'danger';
			$tabela->addAcao($param);
		}
		$tabela = $this->adicionarBotoesExtras($tabela, 'fim');
		
		if($this->_filtro !== '' && $this->_filtro->getQuantPerguntas() > 0){
		    $botao = [];
		    $botao["onclick"]= "$('#formFiltro').toggle();";
		    $botao["texto"]	= "Par&acirc;metros";
		    $botao["id"] = "bt_form";
		    $dados = array();
		    if(!$this->_filtro->getPrimeira()){
		        $dados = $this->getDados();
		    }
		    $tabela->setDados($dados);
		    $ret = $this->_filtro . $tabela;
		    $param_card = array('titulo' => $this->_titulo, 'conteudo' => $ret, 'botoesTitulo' => [$botao]);
		    if(count($this->_botoesExtrasCard) > 0){
		        foreach($this->_botoesExtrasCard as $bot){
		            $param_card['botoesTitulo'][] = $bot;
		        }
		    }
		    if(count($bt_incluir) > 0){
		        $param_card['botoesTitulo'][] = $bt_incluir;
		    }
		    
		    $ret = addCard($param_card);
		}
		else{
		    if(count($this->_botoesExtrasCard) > 0){
		        foreach($this->_botoesExtrasCard as $bot){
		            $tabela->addBotaoTitulo($bot);
		        }
		    }
		    if(count($bt_incluir) > 0){
		        $tabela->addBotaoTitulo($bt_incluir);
		    }
		    
		    $dados = $this->getDados();
		    $tabela->setDados($dados);
		    $ret = $tabela . '';
		}
		return $ret;
	}
	
	protected function adicionarColunasTabela($tabela){
	    foreach ($this->_camposBrowser as $campo) {
	        $tabela->addColuna($campo);
	    }
	    return $tabela;
	}
	
	protected function criarTabela(){
	    if($this->_filtro !== '' && $this->_filtro->getQuantPerguntas() > 0){
	        $param = array();
	    }
	    else{
	        $param = array('titulo' => $this->_titulo);
	    }
	    $tabela = new tabela01($param);
	    return $tabela;
	}

	protected function getDados()
	{
		$ret = array();
		$sql = $this->montarSqlBrowser();
		//echo $sql;
		$rows = query($sql);
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$temp = array();
				foreach ($this->_camposBrowser as $campo) {
					if ($campo['tabela_itens'] != '') {
					    $tab = explode('|', $campo['tabela_itens']);
					    if(count($tab) > 2){
					        if(!isset($this->_dicionario_bw[$campo['campo']])){
					            //formato: tabela|campo_id|campo_etiqueta|ordem_by|where
					            $this->_dicionario_bw[$campo['campo']] = array();
					            $tabela = $tab[0];
					            $id = $tab[1];
					            $desc = $tab[2];
					            $where = isset($tab[4]) && !empty($tab[4]) ? ' WHERE '.$tab[4] : '';
					            $sql = "SELECT $id,$desc FROM $tabela $where";
					            $rows_dic = query($sql);
					            if(is_array($rows_dic) && count($rows_dic) > 0){
					                foreach ($rows_dic as $row_dic){
					                    $this->_dicionario_bw[$campo['campo']][$row_dic[0]] = $row_dic[1];
					                }
					            }
					        }
					        $temp[$campo['campo']] = $this->_dicionario_bw[$campo['campo']][$row[$campo['campo']]] ?? '';
					    }
					    else{
					        $temp[$campo['campo']] = getTabelaDesc($campo['tabela_itens'], $row[$campo['campo']]);
					    }
					} elseif ($campo['funcao_browser'] != '') {
						$comando = $campo['funcao_browser'];
						$campos_funcao = $this->separaFuncaoBrowser($comando);
						foreach ($campos_funcao as $cf) {
							//$comando = str_replace('@@' . $cf, isset($row[$cf]) ? $row[$cf] : '', $comando);
						    $comando = str_replace('@@' . $cf, "'" . ($row[$cf] ?? '') . "'", $comando);
						}
						//$comando = "'" . str_replace(',', "','", $comando) . "'";
						//$comando = '$conteudo = ExecMethod(' . $comando . ');';
						$comando = '$conteudo' . " = $comando;";
						//echo "$comando \n";
						$conteudo = '';
						eval($comando);
						$temp[$campo['campo']] = $conteudo;
					} 
					elseif($campo['real'] === 'R') {
						$temp[$campo['campo']] = $row[$campo['campo']];
					}
				}
				$temp['id64'] = $this->montarIdBase64($row);
				$ret[] = $temp;
			}
		}
		return $ret;
	}

	protected function montarSqlBrowser()
	{
		$ret = '';
		if (!empty($this->_query_dados)) {
			//se for setado uma query, usa ela
			$ret = $this->_query_dados;
		} else {
			//se não for setado uma query, cria uma generica
			$ret = "select * from $this->_tabela ";
			$where = array();
			if (!empty($this->_sys002['campoAtivo'])) {
				$where[] = $this->_sys002['campoAtivo'] . " = 'S'";
			}
			if($this->_filtro !== '' && $this->_filtro->getQuantPerguntas() > 0){
			    $dados_filtro = $this->_filtro->getFiltro();
			    foreach ($this->_sys003 as $campo => $info) {
			        if(isset($dados_filtro[$campo]) && !empty($dados_filtro[$campo])){
			            $where[] = "{$campo} = '{$dados_filtro[$campo]}'";
			        }
			        if(isset($dados_filtro[$campo . '_min']) && !empty($dados_filtro[$campo . '_min'])){
			            $where[] = "{$campo} >= '{$dados_filtro[$campo . '_min']}'";
			        }
			        if(isset($dados_filtro[$campo . '_max']) && !empty($dados_filtro[$campo . '_max'])){
			            $where[] = "{$campo} <= '{$dados_filtro[$campo . '_max']}'";
			        }
			    }
			}
			if (count($where) > 0) {
				$ret .= 'where ' . implode(' AND ', $where);
			}
		}
		if ((strpos($ret, 'WHERE') === false && strpos($ret, 'where') === false) && !empty($this->_where)) {
			$ret .= ' where ';
		}
		$ret .= $this->_where;
		return $ret;
	}

	protected function montarIdBase64($row)
	{
		$ret = '';
		if (strpos($this->_sys002['chave'], ',') === false) {
			//somente uma chave
			$ret = base64_encode($row[$this->_sys002['chave']]);
		} else {
			//mais de uma chave
			$ret = array();
			$campos = explode(',', $this->_sys002['chave']);
			foreach ($campos as $c) {
				if (isset($row[$c])) {
					$ret[] = $row[$c];
				} else {
					return '';
				}
			}
			$ret = implode('##', $ret);
			$ret = base64_encode($ret);
		}
		return $ret;
	}

	public function getEntrada($id, $decodificar_id = true)
	{
		$ret = array();
		if (($id != '' && !is_array($id)) || (is_array($id) && count($id) > 0)) {
			$sql = "select * from $this->_tabela where 1=1";
			if ($decodificar_id) {
				$id_decodificado = $this->decodificarId($id);
				foreach ($id_decodificado as $campo => $valor) {
					$sql .= " and $campo = '$valor'";
				}
			} else {
				if (strpos($this->_sys002['chave'], ',') === false) {
					$sql .= " and " . $this->_sys002['chave'] . " = '$id'";
				} else {
					$campos_chave = explode(',', $this->_sys002['chave']);
					foreach ($campos_chave as $c) {
						$sql .= " and $c = '" . $id[$c] . "'";
					}
				}
			}
			$rows = query($sql);
			if (is_array($rows) && count($rows) > 0) {
				foreach ($this->_sys003 as $sys003) {
					if ($sys003['real'] == 'R') {
						$ret[$sys003['campo']] = $rows[0][$sys003['campo']];
					}
				}
			}
		}
		return $ret;
	}

	public function montarFormulario($dados = array(), $acao = 'I', $indice_extra = '')
	{
		if ($indice_extra != '') {
			$indice_extra = '[' . $indice_extra . ']';
		}
		if (count($dados) == 0) {
			foreach ($this->_sys003 as $dadosCampo) {
				$dados[$dadosCampo['campo']] = '';
			}
		}
		//$dicionario_classes_mascara foi criado para lidar com o modal do kanboard
		//caso seja necessário editar as mascaras olhar no arquivo app.min.js
		$dicionario_classes_mascara = array(
		    false => array(
		        'cpf' => 'cpf',
		        'cnpj' => 'cnpj',
		        'telefone' => 'telefone',
		        'cep' => 'cep',
		        'I' => 'inteiroPositivo',
		        'N' => 'numeroPositivo',
		        'V' => 'valorPositivo',
		        'V4' => 'valor4Positivo',
		        'P' => 'percentagem',
		        'hora' => 'hora',
		        'H' => 'hora',
		    ),
		    
		    true => array(
		        'cpf' => 'cpf',
		        'cnpj' => 'cnpj',
		        'telefone' => 'telefone',
		        'cep' => 'cep',
		        'I' => 'inteiroNegativo',
		        'N' => 'numeroNegativo',
		        'V' => 'valorNegativo',
		        'V4' => 'valor4Negativo',
		        'P' => 'percentagem',
		        'hora' => 'hora',
		        'H' => 'hora',
		    ),
		);
		$form = new form01(['geraScriptValidacaoObrigatorios' => true]);
		$camposChave = explode(',', $this->_sys002['chave']);
		foreach ($this->_sys003 as $campoDados) {
			if (!in_array($campoDados['tipo'], array('F', 'L', 'V')) || $campoDados['real'] === 'R') {
				$campo = $campoDados['campo'];
				if ($acao == 'V') {
					$tipo = 'I';
					$form->addHidden("formCRUD" . $indice_extra . '[' . $campo . ']', $dados[$campo]);
				} elseif ($acao == 'E') {
					if ($campoDados['editavel'] == 'S' && !in_array($campo, $camposChave)) { //Não pode editar campo chave
					    /*
						if ($campoDados['tipo'] == 'D') {
							$tipo = 'D';
						} elseif (empty($campoDados['funcao_lista']) && empty($campoDados['tabela_itens'])) {
							$tipo = 'T';
						} else {
							$tipo = 'A';
						}
						*/
					    if (empty($campoDados['funcao_lista']) && empty($campoDados['tabela_itens'])) {
					        $tipo = $campoDados['tipo'];
					    } else {
					        $tipo = 'A';
					    }
					} else {
						$tipo = 'I';
						$form->addHidden("formCRUD" . $indice_extra . '[' . $campo . ']', $dados[$campo]);
					}
				} elseif ($acao == 'I') {
					if (!in_array($campo, $camposChave)) {
						if ($campoDados['funcao_lista'] == '' && empty($campoDados['tabela_itens'])) {
						    /*
							if ($campoDados['tipo'] != 'D') {
								$tipo = 'T';
							} else {
								$tipo = 'D';
							}*/
						    $tipo = $campoDados['tipo']; 
						} else {
							$tipo = 'A';
						}
					} else {
						//Se chave gerada automaticamente não é possível editar
						if ($this->_sys002['chaveAuto'] == 'S') {
							$tipo = 'I';
							$form->addHidden("formCRUD" . $indice_extra . '[' . $campo . ']', $dados[$campo]);
						} else {
							$tipo = 'T';
						}
					}
					if ($campoDados['editavel'] == 'N') {
						$tipo = 'I';
						$form->addHidden("formCRUD" . $indice_extra . '[' . $campo . ']', $dados[$campo]);
					}
				}

				$obrigatorio = $campoDados['obrigatorio'] == 'S' ? true : false;
				if (($this->_mostraID === true || $campo != 'id') && (($campoDados['editavel'] == 'S' && $acao == 'I') || ($acao == 'E'))) {
					$mascara = $campoDados['mascara'];
					$negativo = false;
					//Valores negativos
					if ($mascara == 'VN') {
						$mascara = 'V';
						$negativo = true;
					}
					$classe_add = [];
					if($mascara != ''){
					    $classe_add[] = $dicionario_classes_mascara[$negativo][$mascara];
					}
					if($campoDados['class_form'] != ''){
					    $classe_temp = str_replace(' ', ',', $campoDados['class_form']);
					    $classe_add = array_merge($classe_add, explode(',', $classe_temp));
					}
					$classe_add = implode(' ', $classe_add);
					
					$form->addCampo(array(
						'campo' => "formCRUD" . $indice_extra . '[' . $campo . ']',
						'etiqueta' => $campoDados['etiqueta'],
						'pasta' => ($campoDados['pasta'] === '' ? 0 : $campoDados['pasta']),
						'mascara' => $mascara,
						'negativo' => $negativo,
						'tipo' => $tipo,
						'tamanho' => $campoDados['tamanho'],
						'linhas' => '',
						'valor' => isset($dados[$campo]) ? $dados[$campo] : '',
						'lista' => '',
						'funcao_lista' => $campoDados['funcao_lista'],
						'opcoes' => $campoDados['opcoes'],
						'validacao' => '',
						'obrigatorio' => $obrigatorio,
						'help' => $campoDados['help'],
						'largura' => $campoDados['largura'],
						'tabela_itens' => $campoDados['tabela_itens'],
					    'estilo' => $campoDados['estilo_form'],
					    'classeadd' => $classe_add,
					    'autocomplete' => false,
					));
				} else {
					$form->addHidden("formCRUD" . $indice_extra . '[' . $campo . ']', $dados[$campo]);
				}
			} elseif ($campoDados['real'] === 'V') {
				$comando = $campoDados['funcao_layout'];
				if (!empty($comando)) {
					$campos_funcao = $this->separaFuncaoBrowser($comando);
					foreach ($campos_funcao as $cf) {
					    $comando = str_replace('@@' . $cf, "'" . ($dados[$cf] ?? '') . "'", $comando);
					}
					
					
					//$comando = "'".str_replace(',', "','", $comando)."'";
					$comando = '$conteudo' . " = $comando;";
					//echo "$comando \n";
					$conteudo = '';
					
					eval($comando);
					$pasta = $campoDados['pasta'] == '' ? 1 : $campoDados['pasta'];
					$form->addConteudoPastas($pasta, $conteudo);
				}
			}
		}

		$form->setPastas($this->_pastasFormulario);

		$link_cancelar = getAppVar('link_redirecionar_cad_cancelar');
		unsetAppVar('link_redirecionar_cad_cancelar');
		if($link_cancelar != null){
			$form->setBotaoCancela($link_cancelar);
		}

		return $form;
	}

	public function incluir($dados = array())
	{
		$ret = '';

		$formulario = $this->montarFormulario($dados, 'I');

		$link = getAppVar('link_salvar_cad');
		unsetAppVar('link_salvar_cad');
		if($link === null){
			$link = getLink() . 'salvar&acao=I&id=0';
		}
		$formulario->setEnvio($link, 'formPrograma', 'formPrograma');


		$param = [];
		$param['icone'] = 'fa-edit';
		$param['titulo'] = 'Incluir ' . $this->_sys002['unidade'];
		$param['conteudo'] = $formulario . '';

		$ret = addCard($param);

		return $ret;
	}

	public function editar($id = '')
	{
	    if((is_string($id) && $id == '') || (!is_string($id) && $id == 0)){
	        $id = getParam($_GET, 'id', 0);
	    }
		$dados = $this->getEntrada($id);
		$dados = $this->arrumarCamposPorTipo($dados);
		$formulario = $this->montarFormulario($dados, 'E');
		$link = getAppVar('link_salvar_cad');
		unsetAppVar('link_salvar_cad');
		if($link === null){
		    $link = getLink() . 'salvar&acao=E&id=' . $id;
		}
		$formulario->setEnvio($link, 'formPrograma', 'formPrograma');

		$param = [];
		$param['icone'] = 'fa-edit';
		$param['titulo'] = 'Editar ' . $this->_sys002['unidade'];
		$param['conteudo'] = $formulario . '';

		$ret = addCard($param);

		return $ret;
	}
	
	protected function arrumarCamposPorTipo($entrada){
	    $ret = array();
	    foreach ($this->_sys003 as $campo){
	        if(isset($entrada[$campo['campo']])){
    	        if($campo['tipo'] == 'D'){
    	            $ret[$campo['campo']] = datas::dataS2D($entrada[$campo['campo']]);
    	        }
    	        else{
    	            $ret[$campo['campo']] = $entrada[$campo['campo']];
    	        }
	        }
	        else{
	            $ret[$campo['campo']] = '';
	        }
	    }
	    
	    return $ret;
	}

	public function salvar($id = 0, $dados = array(), $acao = '', $redireciona = true){
		$id = $id !== 0 ? $id : getParam($_GET, 'id', 0);
		$acao = $acao != '' ? $acao : getParam($_GET, 'acao', 'D');
		$formCrud = count($dados) > 0 ? $dados : getParam($_POST, 'formCRUD', array());
		if ($acao == 'I') {
			$sql = $this->montarSqlIncluir($formCrud);
			//echo '<br>' . $sql . '<br>';
			$this->_ultimoIdIncluido = query($sql);
			if ($this->_mostrarMensagens) {
				addPortalMensagem($this->_sys002['unidade'] . ' Criado(a)');
			}
		} elseif ($acao == 'E') {
			$sql = $this->montarSqlEditar($formCrud, $id);
			//echo '<br>' . $sql . '<br>';
			query($sql);
			if ($this->_mostrarMensagens) {
				addPortalMensagem($this->_sys002['unidade'] . ' Alterado(a)');
			}
		} else {
			if ($this->_mostrarMensagens) {
				addPortalMensagem('Por algum motivo não fiz nada com os dados');
			}
		}
		foreach ($this->_sys003 as $campo_sys003) {
			if ($campo_sys003['funcao_salvar'] != '') {
				$metodo = $campo_sys003['funcao_salvar'] . ';';
				//echo '<br>' . $metodo . '<br>';
				eval($metodo);
			}
		}
		
		if($redireciona){
    		$link_redirecionar = getAPPvar('link_redirecionar_cad_salvar');
    		unsetAppVar('link_redirecionar_cad_salvar');
    		if($link_redirecionar == null){
    			redireciona(getLink() . 'index');
    		}
    		else{
    			redireciona($link_redirecionar);
    		}
		}
	}

	public function recuperarIdEntradaPorDados($dados)
	{
		$ret = '';
		$dados_sql = array();
		foreach ($this->_sys003 as $campoDados) {
			if (isset($dados[$campoDados['campo']]) && strpos($this->_sys002['chave'], $campoDados['campo']) === false) {
				//$temp[] = $campoDados['campo'] . " = '" . $dados[$campoDados['campo']] . "'";
				$dados_sql[$campoDados['campo']] = $dados[$campoDados['campo']];
			}
		}
		if (count($dados_sql) > 0) {
			$sql = montaSQL($dados_sql, $this->_tabela, 'SELECT', '', $this->_sys002['chave']);
			$rows = query($sql);
			if (is_array($rows) && count($rows) > 0) {
				$ret = $this->getIdFromArray($rows[0]);
			}
		}
		return $ret;
	}

	protected function getIdFromArray($dados)
	{
		$ret = '';
		if (count($dados) > 0) {
			if (strpos($this->_sys002['chave'], ',') === false) {
				$ret = $dados[$this->_sys002['chave']];
			} else {
				$campos_chave = explode(',', $this->_sys002['chave']);
				$ret = array();
				foreach ($campos_chave as $c) {
					if (!isset($dados[$c])) {
						return '';
					}
					$ret[$c] = $dados[$c];
				}
			}
		}
		return $ret;
	}

	protected function montarSqlIncluir($dados)
	{
		$ret = '';
		//$temp = array();
		$dados_sql = array();
		foreach ($this->_sys003 as $campoDados) {
		    if (isset($dados[$campoDados['campo']]) && $campoDados['real'] == 'R' && (strpos($this->_sys002['chave'], $campoDados['campo']) === false || $this->_sys002['chaveAuto'] == 'N')) {
				//$temp[] = $campoDados['campo'] . " = '" . $dados[$campoDados['campo']] . "'";
			    if($dados[$campoDados['campo']] == ''){
			        if($campoDados['tipo'] == 'N' || in_array($campoDados['mascara'], array('I', 'N', 'V', 'V4'))){
			            $dados_sql[$campoDados['campo']] = 0;
			        }
			        else{
			            $dados_sql[$campoDados['campo']] = $dados[$campoDados['campo']];
			        }
			    }
			    else{
			        if(in_array($campoDados['mascara'], array('N', 'V', 'V4')) || in_array($campoDados['tipo'], array('N', 'V'))){
			            $dados_sql[$campoDados['campo']] = str_replace(array('.', ','), array('', '.'), $dados[$campoDados['campo']]);
			        }
			        elseif($campoDados['mascara'] != ''){
			            $dados_sql[$campoDados['campo']] = str_replace(array('(', ')', '-', '/', '.'), '', $dados[$campoDados['campo']]);
			        }
			        elseif($campoDados['tipo'] == 'D'){
			            $dados_sql[$campoDados['campo']] = datas::dataD2S($dados[$campoDados['campo']]);
			        }
			        else{
			            $dados_sql[$campoDados['campo']] = $dados[$campoDados['campo']];
			        }
			    }
			}
		}
		if(!isset($dados_sql[$this->_sys002['campoAtivo']])){
		    $dados_sql[$this->_sys002['campoAtivo']] = 'S';
		}
		if (count($dados_sql) > 0) {
			$ret = montaSQL($dados_sql, $this->_tabela);
		}
		return $ret;
	}

	protected function montarSqlEditar($dados, $id)
	{
		$ret = '';
		$dados_sql = array();
		foreach ($this->_sys003 as $campoDados) {
		    if (isset($dados[$campoDados['campo']]) && $campoDados['real'] == 'R' && strpos($this->_sys002['chave'], $campoDados['campo']) === false) {
		        //$temp[] = $campoDados['campo'] . " = '" . $dados[$campoDados['campo']] . "'";
		        if($dados[$campoDados['campo']] == ''){
		            if($campoDados['tipo'] == 'N' || in_array($campoDados['mascara'], array('I', 'N', 'V', 'V4'))){
		                $dados_sql[$campoDados['campo']] = 0;
		            }
		            else{
		                $dados_sql[$campoDados['campo']] = $dados[$campoDados['campo']];
		            }
		        }
		        else{
		            if(in_array($campoDados['mascara'], array('N', 'V', 'V4','P')) || in_array($campoDados['tipo'], array('N', 'V','P'))){
		                $dados_sql[$campoDados['campo']] = str_replace(array('.', ','), array('', '.'), $dados[$campoDados['campo']]);
		            }
		            elseif($campoDados['mascara'] != ''){
		                $dados_sql[$campoDados['campo']] = str_replace(array('(', ')', '-', '/', '.'), '', $dados[$campoDados['campo']]);
		            }
		            elseif($campoDados['tipo'] == 'D'){
		                $dados_sql[$campoDados['campo']] = datas::dataD2S($dados[$campoDados['campo']]);
		            }
		            else{
		                $dados_sql[$campoDados['campo']] = $dados[$campoDados['campo']];
		            }
		        }
		    }
		}
		$id_decodificado = $this->decodificarId($id);
		$where = array();
		foreach ($id_decodificado as $campo => $valor) {
			$where[] = "$campo = '$valor'";
		}

		if (count($dados_sql) > 0 && count($where) > 0) {
			$where = implode(' and ', $where);
			$ret = montaSQL($dados_sql, $this->_tabela, 'UPDATE', $where);
		}
		return $ret;
	}

	public function excluir($redireciona = true)
	{
		$id = getParam($_GET, 'id', 0);
		$id_decodificado = $this->decodificarId($id);
		if ($this->_excluir_permanente) {
			$sql = "delete from $this->_tabela where 1=1 ";
		} else {
			$sql = "update $this->_tabela set ";
			$sql .= $this->montarSetExcluir();
			$sql .= ' where 1=1 ';
		}

		foreach ($id_decodificado as $campo => $valor) {
			$sql .= " and $campo = '$valor'";
		}
		//echo $sql;
		query($sql);
		if ($this->_mostrarMensagens) {
			addPortalMensagem($this->_sys002['unidade'] . ' Excluido(a)');
		}
		if($redireciona){
		    redireciona('');
		}
	}

	protected function montarSetExcluir()
	{
		$ret = '';
		$campos_setar = array();
		if (!empty($this->_sys002['campoAtivo'])) {
			$campos_setar[] = $this->_sys002['campoAtivo'] . " = 'N' ";
		}
		$ret = implode(', ', $campos_setar);
		return $ret;
	}

	public function decodificarId($id)
	{
		$ret = array();
		$campos = explode(',', $this->_sys002['chave']);
		$valores = explode('##', base64_decode($id));
		foreach ($campos as $chave => $campo) {
			$ret[$campo] = isset($valores[$chave]) ? $valores[$chave] : '';
		}
		return $ret;
	}

	protected function carregar002($primeira_tentativa = true)
	{
		$this->_sys002 = array();
		if (!empty($this->_tabela)) {
			$sql = "SELECT * FROM sys002 WHERE tabela = '" . $this->_tabela . "'";
			//echo $sql . '<br>';
			$rows = query($sql);
			if (count($rows) > 0) {
				$this->_sys002['descricao'] = $rows[0]['descricao'];
				$this->_sys002['chave'] 	= str_replace(' ', '', $rows[0]['chave']);
				$this->_sys002['tipo'] 		= $rows[0]['chave_tipo'];
				$this->_sys002['chaveAuto']	= $rows[0]['chave_auto'];
				$this->_sys002['campo'] 	= $rows[0]['campo_desc'];
				$this->_sys002['etiqueta'] 	= $rows[0]['etiqueta'];
				$this->_sys002['campoAtivo'] = $rows[0]['campoativo'];
				$this->_sys002['icone'] 	= $rows[0]['icone'];
				$this->_sys002['unico'] 	= $rows[0]['unico'];
				$this->_sys002['unidade']   = $rows[0]['unidade'];
			} else {
				if ($primeira_tentativa) {
					$this->geraSYS($this->_tabela);
					$this->carregar002(false);
				}
			}
		}
	}

	protected function carregar003($primeira_tentativa = true)
	{
		$this->_camposBrowser = array();
		$this->_camposSqlBrowser = array();
		$sql = "SELECT * FROM sys003 WHERE tabela = '" . $this->_tabela . "' AND usado = 'S' ORDER BY ordem";
		//echo $sql . '<br>';
		$rows = query($sql);
		if (count($rows) > 0) {

			foreach ($rows as $row) {
				$temp = array();

				foreach ($this->_camposSys003 as $campo) {
					$temp[$campo] = $row[$campo];
				}
				
				$temp['width'] = empty($row['tambrowser']) ? null : $row['tambrowser'];

				$this->_sys003[$temp['campo']] = $temp;
				if ($temp['browser'] == 'S' && (($temp['real'] === 'V' && $temp['funcao_browser'] !== '') || ($temp['real'] === 'R'))) {
					$this->_camposBrowser[] = $temp;
				}
				if ($temp['real'] != 'V') {
					$this->_camposSqlBrowser[$temp['campo']] = $temp['campo'];
				}

				/*
				 //Campos Reais
				 if($temp['real'] != 'V'){
				 $this->_camposReais[$temp['campo']] = $temp['campo'];
				 }
				 //Campos obrigatorios
				 if($temp['obrigatorio'] == 'S' && $temp['real'] != 'V'){
				 $this->_camposObrigatorios[$temp['campo']] = $temp['campo'];
				 }
				 //Campos editaveis
				 if($temp['editavel'] == 'S' && $temp['real'] != 'V'){
				 $this->_camposEditaveis[$temp['campo']] = $temp['campo'];
				 }
				 
				 //Campos virtuais (funções que retornam layout)
				 if($temp['tipo'] != 'V'){
				 $this->_camposVirtuaisLayout[$temp['campo']] = $temp['campo'];
				 }
				 
				 //Pastas diferentes
				 $this->_pastas[$temp['pasta']] = '';
				 */
			}
		} else {
			if ($primeira_tentativa) {
				$this->geraSYS($this->_tabela);
				$this->carregar003(false);
			}
		}

		//$this->_sys003 = traducoes::traduzirSys003($this->_tabela, $this->_sys003);
	}

	protected function getPastas()
	{
		$this->_pastasFormulario = array();
		//$sql = "SELECT * FROM sys008 WHERE tabela = '".$this->_tabela."' ORDER BY sequencia";
		$sql = "SELECT * FROM sys008 WHERE tabela = '" . $this->_tabela . "' ORDER BY pasta";
		$rows = query($sql);
		if (is_array($rows) && count($rows) > 1) {
			foreach ($rows as $row) {
				$this->_pastasFormulario[$row['pasta']] = $row['descricao'];
			}
		} else {
			$this->_pastasFormulario[0] = 'Principal';
			$this->_pastasFormulario[1] = 'Extra';
		}
	}

	protected function geraSYS($tabela)
	{
		if (!empty($tabela)) {
			$sql = "DESCRIBE $tabela";
			$rows = query($sql);

			if (is_array($rows) && count($rows) > 0) {
				//Verifica se já não está cadastrada na SYS002
				$sql = "SELECT * FROM sys002 WHERE tabela = '$tabela'";
				$r2 = query($sql);

				if (isset($r2[0]['tabela'])) {
					if ($this->_mostrarMensagens) {
						addPortalMensagem("Tabela $tabela já esxiste na SYS002", 'danger');
					}
				} else {
					//Procura a chave primária
					$chave = [];
					foreach ($rows as $row) {
						if ($row['Key'] == 'PRI') {
							$chave[] = $row['Field'];
						}
					}

					$campos = [];
					$campos['tabela'] 		= $tabela;
					$campos['descricao'] 	= 'Descrição Tabela ' . $tabela;
					$campos['chave'] 		= implode(',', $chave);
					$campos['chave_auto'] 	= 'N';
					$campos['campo_desc'] 	= 'campo';
					$campos['etiqueta'] 	= 'Tabela ' . $tabela;
					$campos['campoativo'] 	= '';
					$campos['icone'] 		= '';
					$campos['unico'] 		= 'campo';
					$campos['chave_tipo']   = 'T';
					$campos['unidade']      = 'Registro';

					$sql = montaSQL($campos, 'sys002');
					query($sql);
				}

				//Verifica e cadastra na SYS003
				$ordem = 0;
				$linha = 1;
				$conteudo_linha = 0;
				foreach ($rows as $row) {
					$conteudo_linha += 3;
					if ($conteudo_linha > 12) {
						$linha += 1;
						$conteudo_linha = 3;
					}
					$ordem++;
					$campo = $row['Field'];
					$tipoTemp = $row['Type'];
                    
					$tipo = 'T';
					if (strpos($tipoTemp, 'int') !== false) {
						$tipo = 'N';
					} elseif (strpos($tipoTemp, 'char') !== false) {
						$tipo = 'C';
					} elseif (strpos($tipoTemp, 'double') !== false) {
						$tipo = 'N';
					}

					if (strpos($tipoTemp, '(') !== false) {
						$temp = substr($tipoTemp, strpos($tipoTemp, '(') + 1, strpos($tipoTemp, ')') - strpos($tipoTemp, '(') - 1);
						$temp = explode(',', $temp);
						$tam = $temp[0];
						$casas = isset($temp[1]) ? $temp[1] : 0;
					} else {
						$tam = 0;
						$casas = 0;
					}

					$sql = "SELECT * FROM sys003 WHERE tabela = '$tabela' AND campo = '$campo'";
					$r2 = query($sql);

					if (isset($r2[0]['campo'])) {
						if ($this->_mostrarMensagens) {
							addPortalMensagem("Campo $campo já esxiste na SYS003", 'danger');
						}
					} else {
						$campos = [];
						$campos['tabela'] 	= $tabela;
						$campos['campo'] 	= $campo;
						$campos['tipo'] 	= $tipo;
						$campos['ordem'] 	= $ordem;
						$campos['descricao'] = 'Campo ' . $campo;
						$campos['etiqueta'] = $campo;
						$campos['tamanho']  = $tam;
						$campos['casas']  = $casas;
						$campos['largura'] = 3;
						$campos['linha']  = $linha;
						$campos['linhasTA'] = 0;
						$campos['onchange'] = '';
						$campos['mascara'] = '';
						$campos['help'] = '';
						$campos['browser'] = 'S';
						$sql = montaSQL($campos, 'sys003');
						query($sql);
					}
				}
			}
		}
	}

	protected function separaFuncaoBrowser($funcao)
	{
		$ret = array();
		if (strpos($funcao, '@@') !== false) {
			$temp = explode(',', $funcao);
			foreach ($temp as $t) {
				if (strpos($t, '@@') !== false) {
					$ret[] = str_replace(array('@@', ' '), '', $t);
				}
			}
			/*
			 if(count($temp) > 1){
			 unset($temp[0]);
			 foreach ($temp as $campo){
			 $ret[] = str_replace(array(',', ' ', ';'), '', $campo);
			 }
			 }
			 */
		}
		return $ret;
	}

	public function criarCampoVirtual($id_pai, $tabela_pai, $titulo, $dados_extras, $indice_extra)
	{
		$ret = '';
		$this->_pastasFormulario = array();
		$dados = $this->recuperarDadosPorTabelaPai($id_pai, $tabela_pai, $dados_extras);
		if (count($dados) > 0) {
			$acao = 'I';
		} else {
			$acao = 'E';
		}
		$form = $this->montarFormulario($dados, $acao, $indice_extra) . '';
		$param = array(
			'conteudo' => $form . '',
			'titulo' => $titulo,
		);
		$ret = addcard($param);
		return $ret;
	}

	protected function recuperarDadosPorTabelaPai($id_pai, $tabela_pai, $dados_extras, $unico = true)
	{
		$ret = array();
		if (is_array($id_pai)) {
			foreach ($id_pai as $campo => $valor) {
				$dados_extras[$campo] = $valor;
			}
		} else {
			$dados_extras['id_pai'] = $id_pai;
		}
		$sql = "select * from sys023 where tabela_pai = '$tabela_pai' and tabela_filho = '$this->_tabela'";
		//echo $sql . '<br>';
		$rows = query($sql);
		if (is_array($rows) && count($rows) > 0) {
			$campos_join = array();
			$campos_where = array();
			foreach ($rows as $row) {
				if ($row['campo_pai'] != '' && $row['campo_filho'] != '') {
					$campos_join[] = $this->_tabela . '.' . $row['campo_filho'] . ' = ' . $tabela_pai . '.' .  $row['campo_pai'];
				} elseif ($row['campo_pai'] != '' && $row['valor_pai'] != '') {
					$campos_where[] = $tabela_pai . "." . $row['campo_pai'] . " = '" . $row['valor_pai'] . "'";
				} elseif ($row['campo_pai'] != '' && $row['dados_extras_pai'] != '') {
					if (isset($dados_extras[$row['dados_extras_pai']])) {
						$campos_where[] = $tabela_pai . "." . $row['campo_pai'] . " = '" . $dados_extras[$row['dados_extras_pai']] . "'";
					}
				} elseif ($row['campo_filho'] != '' && $row['valor_filho'] != '') {
					$campos_where[] = $this->_tabela . "." . $row['campo_filho'] . " = '" . $row['valor_filho'] . "'";
				} elseif ($row['campo_filho'] != '' && $row['dados_extras_filho'] != '') {
					if (isset($dados_extras[$row['dados_extras_filho']]) && $dados_extras[$row['dados_extras_filho']] != '') {
						$campos_where[] = $this->_tabela . "." . $row['campo_filho'] . " = '" . $dados_extras[$row['dados_extras_filho']] . "'";
					}
				}
			}
			if (count($campos_join) > 0) {
				$sql = "select $this->_tabela.*  from $this->_tabela join $tabela_pai on " . implode(' and ', $campos_join);
			} else {
				$sql = "select * from $this->_tabela";
			}

			if (count($campos_where) > 0) {
				$sql .= " where " . implode(' and ', $campos_where);
			}

			//echo $sql . '<br>';
			$rows = query($sql);
			if (is_array($rows) && count($rows) > 0) {
				if ($unico) {
					$ret = $rows[0];
				} else {
					$ret = $rows;
				}
			}
		}
		return $ret;
	}

	public function salvarCampoVirtual($form, $indice_extra, $tabela_pai, $dados_diretos = array())
	{
		if (isset($form[$indice_extra])) {
			$dados = $form[$indice_extra];
			if (count($dados_diretos) > 0) {
				foreach ($dados_diretos as $campo => $valor) {
					if ($dados[$campo] == '') {
						$dados[$campo] = $valor;
					}
				}
			}
			$sql = "select * from sys023 where tabela_pai = '$tabela_pai' and tabela_filho = '$this->_tabela'";
			$rows = query($sql);
			if (is_array($rows) && count($rows) > 0) {
				foreach ($rows as $row) {
					if (!empty($row['campo_pai']) && !empty($row['campo_filho'])) {
						if (isset($form[$row['campo_pai']])) {
							if ($form[$row['campo_pai']] != '') {
								$dados[$row['campo_filho']] = $form[$row['campo_pai']];
							}
						}
					} elseif (!empty($row['campo_filho']) && !empty($row['valor_filho'])) {
						$dados[$row['campo_filho']] = $row['valor_filho'];
					}
				}
			}
			//echo '<br>' . $this->montarIdBase64($dados) . '<br>';
			$id = $this->montarIdBase64($dados);
			if (count($this->getEntrada($id)) > 0) {
				$this->salvar($id, $dados, 'E');
			} else {
				$this->salvar(0, $dados, 'I');
			}
		}
	}

	protected  function montarBotaoExclusaoCampoVirtualInfinito($indice_extra, $numero)
	{
		$param = [];
		$param['id']        = "btExcluir$indice_extra" . $numero;
		$param['texto'] 	= 'X';
		$param['width'] 	= 10;
		$param['flag'] 		= '';
		$param['onclick'] 		= "excluirLinha$indice_extra(this)";
		$param['cor'] 		= 'danger';
		return formbase01::formBotao($param) . '';
	}

	public function criarCampoVirtualInfinito($id_pai, $tabela_pai, $titulo, $dados_extras, $indice_extra)
	{
		$ret = '';
		$this->addJSCampoVirtualInfinito($indice_extra);
		$this->_pastasFormulario = array();
		$dados = $this->recuperarDadosPorTabelaPai($id_pai, $tabela_pai, $dados_extras, false);
		$param = array(
			'id' => 'tabela' . $indice_extra,
			'titulo' => $titulo,
		);
		$tabela = new tabela01($param);

		$tabela->addColuna(array(
			'campo' => 'bt',
			'etiqueta' => 'Excluir',
			'width' => '',
			'posicao' => 'C',
			'tipo' => 'T',
		));

		$tabela->addColuna(array(
			'campo' => 'form',
			'etiqueta' => 'Dados',
			'width' => '',
			'posicao' => 'C',
			'tipo' => 'T',
		));

		$dados_form = array();
		$linha = 1;
		foreach ($dados as $d) {
			$form = $this->montarFormulario($d, 'E', $indice_extra . $linha);
			$linha += 1;
			$dados_form[] = array(
				'form' => $form . '',
				'bt' => $this->montarBotaoExclusaoCampoVirtualInfinito($indice_extra, $linha),
			);
		}
		$param = [];
		$param['id']        = "btIncluir$indice_extra";
		$param['texto'] 	= '+';
		$param['width'] 	= 30;
		$param['flag'] 		= '';
		$param['onclick'] 		= "incluiLinha$indice_extra($linha)";
		$param['cor'] 		= 'success';
		$tabela->addBotaoTitulo($param);

		$tabela->setDados($dados_form);
		$ret .= $tabela;
		return $ret;
	}

	protected function addJSCampoVirtualInfinito($indice_extra)
	{
		addPortaljavaScript("
function incluiLinha$indice_extra(valor){
    var t = $('#tabela$indice_extra').DataTable();
    //var valor = t.data().count('.odd') + t.data().count('.even');
    //var valor = t.rows().count() + 1;
    $.ajax({url: '" . $this->getPathAjax('CampoVirtualInfinito') . "&indice=$indice_extra" . "' + valor, success: function(retorno_ajax_form){
        $.ajax({url: '" . $this->getPathAjax('CampoVirtualInfinitoBotao') . "&indice=$indice_extra" . "' + '&numero=' + valor, success: function(retorno_ajax_botao){
            t.row.add( [retorno_ajax_botao, retorno_ajax_form] ).draw( false );
            valor = valor + 1;
            $('#btIncluir$indice_extra').attr('onclick', 'incluiLinha$indice_extra('+valor+')');
        }})
    }})
}
				
function excluirLinha$indice_extra(e){
    var t = $('#tabela$indice_extra').DataTable();
	t.row( $(e).parents('tr') ).remove().draw();
}");
	}

	public function salvarCampoVirtualInfinito($form, $indice_extra, $tabela_pai, $dados_diretos = array())
	{
		$indices = array();
		foreach ($form as $indice => $valor) {
			if (strpos($indice, $indice_extra) !== false && is_array($valor)) {
				$indices[] = $indice;
			}
		}
		foreach ($indices as $idx) {
			$this->salvarCampoVirtual($form, $idx, $tabela_pai, $dados_diretos);
		}
	}
	
	public function getSys002(){
	    return $this->_sys002;
	}
	
	public function getSys003(){
	    return $this->_sys003;
	}
}
