<?php
/*
 * Data Criacao 28/06/2018
 * Autor: TWS - Alexandre
 *
 * Descricao: Realiza o CRUD de tabelas com base no SYS002, SYS003, SYS008
 * 
 * -------> não utilizar <---------------
 *
 * Alterações:
 * 				16/10/19 - Thiel - Possibilidade de usar a máscara 'VN' -> Valor negativo (com isto aceita a digitação de valores negativos)
 * 				21/10/19 - Thiel - Possibilidade de utilizar o tipo F - File (somente inclusão e Real = (V)irtual
 * 				23/02/23 - Thiel - Migrada para I4 para manter a compatibilidade de programas antigos
 * 
 * 
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
if(!defined('BPMN_VERSAO_TABELA')) define('BPMN_VERSAO_TABELA', '99');

class crud02{
	var $funcoes_publicas = array(
	);
	
	// Tabela utilizada
	var $_tabela;
	
	//Titulo
	var $_titulo;
	
	//Informações sys002
	var $_sys002;
	
	//Informações sys003
	var $_sys003;
	
	//Campos da tabela sys003
	var $_camposSys003;
	
	//Campos browser
	var $_camposBrowser;
	
	//Nome dos campos
	var $_nomeCampos;
	
	//Nome campos browser
	var $_nomeCamposBrowser;
	
	//Nome campos REAIS do browser
	var $_nomeCamposBrowserSQL;
	
	//Campos editaveis
	var $_camposEditaveis;
	
	//Campos obrigatórios
	var $_camposObrigatorios;
	
	//Campos Reais
	var $_camposReais;
	
	//Campos virtais que retornam layout
	private $_camposVirtuaisLayout = [];
	
	//LINK
	var $_link;
	
	//Indica se dee filtrar cliente na tabela
	var $_filtraCliente;
	
	//Indica se o campo ID deve aparecer no browser e nos formulários
	var $_mostraID;
	
	//Pastas
	var $_pastas;
	
	//Quantidade de pastas
	var $_quantPastas;
	
	function __construct($tabela, $titulo = '', $param = []){
		
		$this->_tabela = $tabela;
		$this->_link = 'index.php?menu='.getModulo().'.'.getClasse().'.';
		$this->_camposSys003 = array('campo','ordem','descricao','etiqueta','tipo','tamanho','casas','mascara','funcao_browser','funcao_lista','opcoes','tabela_itens','validacao','nivel','gatilho','browser','usado','obrigatorio','editavel','real','pasta','alinhamento','tambrowser','inicializador','help','funcao_layout','largura','onchange');
		$this->_camposObrigatorios = [];
		
		if($this->_tabela != ''){
			$this->carregaSys002();
			$this->carregaSys003();
		}

		$paramPadrao = array(
				'filtraCliente' => false,
				'mostraID'		=> true,
				'titulo'		=> true,
		);
		$parametros = mesclaParametros($paramPadrao, $param);

		$this->_titulo = $titulo;

		if(empty($titulo)){
			if($parametros['titulo']){
				$this->_titulo = $this->_sys002['etiqueta'];
			}
		}

		$this->_filtraCliente 	= $parametros['filtraCliente'];
		$this->_mostraID 		= $parametros['mostraID'];
	}
	
	
	
	function browser($parametros = [], $titulo = '', $dados = []){
		$ret = '';
		$param = [];
		
		//Mostra ID?
		if(isset($parametros['mostraID']) && $parametros['mostraID'] === false){
			$this->_mostraID = false;
		}
		
		$campoChave = $this->getSys002('chave');
		if(count($this->_camposBrowser) > 0){
			$tit = empty($titulo) ? $this->_titulo : $titulo;
			$param['paginacao'] = isset($parametros['paginacao']) && $parametros['paginacao'] == false ? false : true;
			$param['scroll'] 	= isset($parametros['scroll']) && $parametros['scroll'] == false ? false: true;
			$param['scrollX'] 	= isset($parametros['scrollX']) && $parametros['scrollX'] == true ? true: false;
			$param['scrollY'] 	= isset($parametros['scrollY']) && $parametros['scrollY'] == true ? true : false;
			$param['filtro'] 	= isset($parametros['filtro']) && $parametros['filtro'] == false ? false : true;
			$param['width'] 	= isset($parametros['width']) ? $parametros['width'] : '';
			$param['ordenacao']	= isset($parametros['ordenacao']) && $parametros['ordenacao'] == true ? true : false;
			$param['info']		= isset($parametros['info']) && $parametros['info'] == true ? true : false;
			
			$tabela = new Tabela01($param);
			foreach ($this->_camposBrowser as $campo){
				if($this->_mostraID === true || $campo['campo']!= 'id' ){
					$tipo = $campo['tipo'];
					if($this->_sys003[$campo['campo']]['mascara'] == 'V' || $this->_sys003[$campo['campo']]['mascara'] == 'V4' || $this->_sys003[$campo['campo']]['mascara'] == 'VN'){
						$tipo = $this->_sys003[$campo['campo']]['mascara'];
						if($this->_sys003[$campo['campo']]['mascara'] == 'VN'){
							$tipo = 'V';
						}
						
					}
					$tabela->addColuna(array('campo' => $campo['campo'], 'etiqueta' => $campo['etiqueta'], 'tipo' => $tipo, 'width' => $campo['tambrowser'], 'posicao' => $campo['alinhamento']));
				}
			}
			
			$funcaoExcluir = isset($parametros['funcaoExcluir']) && !empty($parametros['funcaoExcluir']) ? $parametros['funcaoExcluir'] : 'excluir';
			$campoChaveExcluir = isset($parametros['campoChaveExcluir']) && !empty($parametros['campoChaveExcluir']) ? $parametros['campoChaveExcluir'] : $campoChave;
			if(isset($parametros['excluir']) && $parametros['excluir'] === true){
				$this->jsConfirmaExclusao('"Confirma a EXCLUSAO? \n\n '.$this->_titulo.'\n\n'.ucfirst($campoChaveExcluir).': "+id+"\n\n"+desc');
				$acao= [];
				$acao['texto'] = 'Excluir';
				$colunaDescricaoExcluir = isset($parametros['colunaDescricaoExcluir']) && !empty($parametros['colunaDescricaoExcluir']) ? '{COLUNA:'.$parametros['colunaDescricaoExcluir'].'}' : "''";
				$acao['link'] 	= "javascript:confirmaExclusao('".$this->_link.$funcaoExcluir."&".$campoChaveExcluir."=','{ID}',$colunaDescricaoExcluir)";
				$acao['coluna']= $campoChaveExcluir;
				$acao['flag'] 	= '';
				//$acao['width'] = 100;
				$acao['cor'] = 'danger';
				$acao['pos'] = isset($parametros['excluirPosicao']) && $parametros['excluirPosicao'] == 'F' ? 'F' : 'I';
				$tabela->addAcao($acao);
			}
			
			$funcaoEditar = isset($parametros['funcaoEditar']) && !empty($parametros['funcaoEditar']) ? $parametros['funcaoEditar'] : 'editar';
			$linkEditar	  = isset($parametros['linkEditar']) && !empty($parametros['linkEditar']) ? $parametros['linkEditar'] : $this->_link;
			$campoChaveEditar = isset($parametros['campoChaveEditar']) && !empty($parametros['campoChaveEditar']) ? $parametros['campoChaveEditar'] : $campoChave;
			if(isset($parametros['editar']) && $parametros['editar'] === true){
				$acao = [];
				$acao['texto'] 	= 'Editar';
				$acao['coluna'] = $campoChaveEditar;
				$acao['link'] 	= $linkEditar.$funcaoEditar.'&'.$campoChaveEditar.'=';
				$acao['pos'] = isset($parametros['editarPosicao']) && $parametros['editarPosicao'] == 'F' ? 'F' : 'I';
				$tabela->addAcao($acao);
			}
			if(isset($parametros['acoes']) && is_array($parametros['acoes'])){
				foreach ($parametros['acoes'] as $acao){
					$tabela->addAcao($acao);
				}
			}
			
			//Caso queira alterar a função a ser chamada para incluir
			$funcaoIncluir = isset($parametros['funcaoIncluir']) && !empty($parametros['funcaoIncluir']) ? $parametros['funcaoIncluir'] : 'incluir';
			$linkIncluir	  = isset($parametros['linkIncluir']) && !empty($parametros['linkIncluir']) ? $parametros['linkIncluir'] : $this->_link;
			if(isset($parametros['incluir']) && $parametros['incluir'] === true){
				$p = [];
				$p['onclick'] = "setLocation('".$linkIncluir.$funcaoIncluir."')";
				$p['tamanho'] = 'pequeno';
				$p['cor'] = 'primary';
				$p['texto'] = 'Incluir';
				$parametros['boxInfoParam']['botoesTitulo'][] = $p;
			}
			
			//Se deve mostrar o botão cancelar
			if(isset($parametros['linkCancelar']) && $parametros['linkCancelar'] != ''){
				$p = [];
				$p['onclick'] = "setLocation('".$parametros['linkCancelar']."')";
				$p['tamanho'] = 'pequeno';
				$p['cor'] = 'warning';
				$p['texto'] = 'Cancelar';
				$parametros['boxInfoParam']['botoesTitulo'][] = $p;
			}
			
			if(count($dados) == 0){
				if(!isset($parametros['sql']) || $parametros($param['sql'])){
					$dados = $this->getDadosBrowser($parametros);
				}else{
					//$dados = 
				}
			}
//print_r($dados);
			$tabela->setDados($dados);
			
			$ret .= $tabela;
			
			if(isset($parametros['boxInfo']) && $parametros['boxInfo'] === true){
				$titulo = isset($parametros['boxInfoParam']['titulo']) ? $parametros['boxInfoParam']['titulo'] : $tit;
				$param = isset($parametros['boxInfoParam']) ? $parametros['boxInfoParam'] : [];
				$param['titulo'] 	= $titulo;
				$param['conteudo']	= $ret;
				$ret = addCard($param);
			}else{
				setTituloPagina($tit);
			}
		
		}else{
			$ret = "Não existe campos setados para o browser, favor verificar!";
		}
		return $ret;
	}
	
	//--------------------------------------------------------------------------------------------- SETS -----------------------------------
	
	function setMostraID($mostra = true){
		if($mostra === false){
			$this->_mostraID = false;
		}
	}
	
	function setTitulo($titulo){
		if(!empty($titulo)){
			$this->_titulo = $titulo;
		}
	}
	
	//---------------------------------------------------------------------------------------------
	
	function gravaEdicao($dados = [], $param = []){
		$ret = '';
		$campoChave = $this->getSys002('chave');
		$camposEditaveis = [];
		
		if(count($dados) == 0){
			$dados = getParam($_POST, 'formCRUD');
		}
		$chave = getAppVar('CRUDchaveEdita');
		
		if($dados[$campoChave] != $chave){
			logAcesso('Tentativa de alterar o GET. Original: '.$chave.' Alterado: '.$dados[$campoChave], 5);
			addPortalMensagem('ATENÇÃO: ', 'Erro ao processar sua solicitação, tente novamente!','erro');
			$ret = 'erro';
		}else{
			foreach ($this->_sys003 as $campoDados){
				//Seleciona os campos editaveis
				if($campoDados['editavel'] == 'S' && $campoDados['tipo'] != 'L'){
					$camposEditaveis[] = $campoDados['campo'];
				}
			}
			
			$sql = $this->montaSQL('UPDATE', $dados, $camposEditaveis, $param);
//echo "$sql <br>\n";
			query($sql);
			addPortalMensagem('', 'Registro alterado com sucesso!');
		}
		
		return $ret;
	}
	
	function gravaInclusao($dados = []){
		$ret = '';
		$campoChave = $this->getSys002('chave');
		$camposObrigatorios = [];
		
		if(count($dados) == 0){
			$dados = getParam($_POST, 'formCRUD');
		}

		if(!empty($this->_sys002['unico'])){
			$chavesUnicas = explode(',', $this->_sys002['unico']);
			$verificaChave = $this->verificaChave($dados, $chavesUnicas);
		}else{
			$verificaChave = true;
		}
		
		//Verifica dos campos obrigatórios
		$erro = [];
		if(count($this->_camposObrigatorios) > 0){
			foreach ($this->_camposObrigatorios as $obrigatorio){
				if((!isset($dados[$obrigatorio]) || $dados[$obrigatorio] == '') && isset($this->_camposEditaveis[$obrigatorio]) ){
					$erro[] = $obrigatorio;
				}
			}
		}

		if(count($erro) > 0 || !$verificaChave){
			if(!$verificaChave){
				$chvUn = $chavesUnicas[0];
				$msg = 'O campo <b>'.$this->_sys003[$chvUn]['etiqueta'].'</b> já existe com o valor <b>'.$dados[$chvUn].'</b>, favor alterar!';
			}else{
				if(count($erro) > 1){
					$msg = 'Os campos <br>';
					foreach ($erro as $e){
						$msg .= $this->_sys003[$e]['etiqueta'].'<br>';
					}
					$msg .= 'devem ser preenchidos';
				}else{
					$msg = 'O campo '.$this->_sys003[$erro[0]]['etiqueta'].' deve ser preenchido!';
				}
			}
			addPortalMensagem('ATENÇÃO: ', $msg,'erro');
			//$ret = $dados;
			$parametros = getAppVar('crud_parametros_insert');
			$dados = $this->getMatriz('',$dados);
			$ret = $this->formulario($dados, 'I', $parametros);
		}else{
//print_r($dados);
			foreach ($this->_sys003 as $campoDados){
				if($campoDados['real'] == 'R' && $campoDados['tipo'] != 'L'){
					$camposObrigatorios[] = $campoDados['campo'];
				}
			}
			
			$sql = $this->montaSQL('INSERT', $dados, $camposObrigatorios);
//echo "$sql <br>\n";
			query($sql);
			addPortalMensagem('', 'Registro alterado com sucesso!');
		}
		return $ret;
	}
	
	function gravaExclusao($cod, $sql = ''){
		$dados = [];
		if(empty($sql)){
			$dados[$this->getSys002('chave')] = $cod;
			$sql = $this->montaSQL('DELETE',$dados);
		}
		query($sql);
	}
	//------------------------------------------------------------------------------ FORM ---------
	function formEditar($id, $parametros = []){
		$ret = [];
		
		$ret['retorno']  = '';
		$ret['conteudo'] = '';
		
		$campoPesquisa = isset($parametros['campoChave']) && !empty($parametros['campoChave']) ? $parametros['campoChave'] : $this->getSys002('chave');
		
		$param = [];
		$param['where'] = $campoPesquisa." = '$id'";
		if(isset($parametros['where']) && !empty($parametros['where'])){
			$param['where'] .= " AND ".$parametros['where'];
		}
		$dados = $this->executaSQL('*', $param);
		
		$registros = count($dados);
		if($registros == 0){
			$ret['retorno'] = 'erro';
			$ret['conteudo'] = 'Nenhum registro retornado';
		//}elseif($registros > 1){
		//	$ret['retorno'] = 'erro';
		//	$ret['conteudo'] = 'Mais de um registro retornado';
		}else{
			$ret['retorno']  = 'sucesso';
			$ret['conteudo'] = $this->formulario($dados[0], 'E', $parametros);
		}
//print_r($ret);		
		return $ret;
	}
	
	function formIncluir($parametros, $dadosParam = []){
		$ret = '';
		putAppVar('crud_parametros_insert', $parametros);
		$dados = $this->getMatriz('',$dadosParam);
		$ret .= $this->formulario($dados, 'I', $parametros);
		
		return $ret;
	}
	
	/**
	 * Monta o formulario
	 * 
	 * @param array 	$dados	Dados (quando for Visualização ou Ediçaõ)
	 * @param string 	$acao	Ação do formulario (Visualizar, Editar, Incluir)
	 *
	 * @return string 	formulario
	 */
	private function formulario($dados, $acao, $parametros = []){
		$ret = '';
		$campoChave = $this->getSys002('chave');
		$titulo = $this->_titulo;

		switch ($acao) {
			case 'I':
				$titulo .= ' - Inclusão';
				break;
			case 'V':
				$titulo .= ' - Visualização';
				break;
			case 'E':
				$titulo .= ' - Edição';
				break;
		}
		
		$param = [];
		$param['geraScriptValidacaoObrigatorios'] = true;
		$form = new form01($param);
		if($this->_quantPastas > 1){
			$form->setPastas($this->_pastas);
		}
		$funcaoGravar = isset($parametros['funcaoGravar']) && !empty($parametros['funcaoGravar']) ? $parametros['funcaoGravar'] : 'gravar';
		$url = $this->_link.$funcaoGravar.'.'.$acao;
		$form->setEnvio($url, 'crudForm'.$this->_tabela, 'crudForm'.$this->_tabela);
		
		foreach ($this->_sys003 as $campoDados){
			//tipo == V -> campo virtual que retorna HTML
			if($campoDados['real'] != 'V' && $campoDados['tipo'] != 'V' && $campoDados['tipo'] != 'L'){
				$campo = $campoDados['campo'];
				if($acao == 'V'){
					$tipo = 'I';
				}elseif($acao == 'E'){
					if($campoDados['editavel'] == 'S' && $campo != $campoChave){//Não pode editar campo chave
						if($campoDados['tipo'] == 'D'){
							$tipo = 'D';
						}elseif(empty($campoDados['funcao_lista']) && empty($campoDados['tabela_itens'])){
							$tipo = 'T';
						}else{
							$tipo = 'A';
						}
					}else{
						$tipo = 'I';
					}
				}elseif($acao == 'I'){
					if($campo != $campoChave){
						if($campoDados['funcao_lista'] == '' && empty($campoDados['tabela_itens'])){
							if($campoDados['tipo'] != 'D'){
								$tipo = 'T';
							}else{
								$tipo = 'D';
							}
						}else{
							$tipo = 'A';
						}
					}else{
						//Se chave gerada automaticamente não é possível editar
						if($this->getSys002('chaveAuto') == 'S'){
							$tipo = 'I';
						}else{
							$tipo = 'T';
						}
					}
					if($campoDados['editavel'] == 'N'){
						$tipo = 'I';
					}
				}
				
				$obrigatorio = $campoDados['obrigatorio'] == 'S' ? true : false;
				if($this->_mostraID === true || $campo != 'id' ){
					$mascara = $campoDados['mascara'];
					$negativo = false;
					//Valores negativos
					if($mascara == 'VN'){
						$mascara = 'V';
						$negativo = true;
					}
					$form->addCampo(array(
						'id' 			=> $campo, 
						'campo' 		=> 'formCRUD['.$campo.']', 
						'etiqueta' 		=> $campoDados['etiqueta'], 
						'pasta' 		=> $campoDados['pasta'],
						'mascara' 		=> $mascara, 
						'negativo' 		=> $negativo, 
						'tipo' 			=> $tipo, 
						'tamanho' 		=> $campoDados['tamanho'], 
						'linhas' 		=> '', 
						'valor' 		=> $dados[$campo], 
						'lista' 		=> '', 
						'funcao_lista' 	=> $campoDados['funcao_lista'],
						'opcoes' 		=> $campoDados['opcoes'],
						'validacao' 	=> '', 
						'obrigatorio' 	=> $obrigatorio, 
						'help' 			=> $campoDados['help'], 
						'largura' 		=> $campoDados['largura'], 
						'tabela_itens' 	=> $campoDados['tabela_itens'],
						'onchange' 		=> $campoDados['onchange']
					));
				}else{
					$form->addHidden("formCRUD[$campo]", $dados[$campo]);
				}
//echo "form->addCampo(array('id' => $campo, 'campo' => 'formCRUD[$campo]', 'etiqueta' => '".$campoDados['etiqueta']."'	, 'tipo' => '$tipo', 'tamanho' => ".$campoDados['tamanho'].", 'linhas' => '', 'valor' => '".$dados[$campo]."', 'lista' => '', 'funcao_lista' => '".$campoDados['funcao_lista']."','opcao' => '".$campoDados['opcoes']."','validacao' => '', 'obrigatorio' => $obrigatorio, 'help' => ".$campoDados['help']."));<br>\n";
				//$form->addCampo(array('id' => '', 'campo' => 'formsys005[desc]', 'etiqueta' => 'Valor'	, 'tipo' => 'T' , 'tamanho' => '30', 'linhas' => '', 'valor' => $dados[0]['descricao']	, 'opcao' => ''	,'lista' => ''		, 'validacao' => '', 'obrigatorio' => true));
				//$form->addCampo(array('id' => '', 'campo' => 'formsys005[ativo]', 'etiqueta' => 'Ativo'	, 'tipo' => 'A' , 'tamanho' => '01', 'linhas' => '', 'valor' => $dados[0]['ativo']		, 'opcao' => ''	,'lista' => $SN		, 'validacao' => '', 'obrigatorio' => true));
			}else{
				if ($campoDados['tipo'] == 'F') {
					$campo = $campoDados['campo'];
					$tipo = 'F';
					$mascara = '';
					$negativo = false;
					$form->addCampo(array('id' => $campo, 'campo' => 'formCRUD['.$campo.']', 'etiqueta' => $campoDados['etiqueta'], 'pasta' => $campoDados['pasta'],'mascara' => $mascara, 'negativo' => $negativo, 'tipo' => $tipo, 'tamanho' => $campoDados['tamanho'], 'linhas' => '', 'valor' => $dados[$campo], 'lista' => '', 'funcao_lista' => $campoDados['funcao_lista'],'opcao' => $campoDados['opcao'],'validacao' => '', 'obrigatorio' => $obrigatorio, 'help' => $campoDados['help'], 'largura' => $campoDados['largura'], 'tabela_itens' => $campoDados['tabela_itens']));
				}elseif($campoDados['tipo'] == 'L'){
					$form->addCampo(array('id' => $campo, 'campo' => '', 'etiqueta' => '', 'pasta' => $campoDados['pasta'], 'tipo' => 'L', 'largura' => $campoDados['largura']));
				}else{
					//Campo virtual (tipo = V) que retorna HTML
					$comando = $campoDados['funcao_layout'];
					if(!empty($comando)){
						$separa = $this->separaFuncaoBrowser($comando);
						foreach ($separa as $key => $s) {
							$comando = str_replace($s, $dados[$key], $comando);
						}
						$comando = "'".str_replace(',', "','", $comando)."'";
						$comando = '$conteudo = ExecMethod('.$comando.');';
		//echo "$comando \n";
						eval($comando);
						$form->addConteudoPastas($campoDados['pasta'], $conteudo);
					}
				}
			}
		}
		$form->addHidden("formCRUD[$campoChave]", $dados[$campoChave]);
		putAppVar('CRUDchaveEdita', $dados[$campoChave]);
		
		$param = [];
		if(isset($parametros['botaoCancela']) && $parametros['botaoCancela']){
			$p = [];
			if(!isset($parametros['linkCancela']) || $parametros['linkCancela'] == ''){
				$p['onclick'] = "setLocation('".$this->_link."index')";
			}else{
				$p['onclick'] = "setLocation('".$parametros['linkCancela']."')";
			}
			$p['tamanho'] = 'pequeno';
			$p['cor'] = 'warning';
			$p['texto'] = 'Cancelar';
			$param['botoesTitulo'][] = $p;
		}
//print_r($parametros);		
		if(isset($parametros['botaoForm']) && count($parametros['botaoForm']) > 0){
			foreach ($parametros['botaoForm'] as $botao){
				$param['botoesTitulo'][] = $botao;
			}
		}
		$param['titulo'] = $titulo;
		$param['conteudo'] = $form;
		$ret = addCard($param);
		
		return $ret;
	}
	
	
	//------------------------------------------------------------------------------ GET-----------
	private function getMatriz($tipo = '', $dados = []){
		$ret = [];
		
		foreach ($this->_sys003 as $campo => $campos){
			//Quando já vem de um form cria somente os campos que não existem (não zera os que existem)
			if(!isset($dados[$campo])){
				if($tipo == '' || ($tipo == 'BROWSER' && $campos['browser'] == 'S')){
					$ret[$campo] = '';
					$inicializador = $campos['inicializador'];
					if($inicializador != ''){
						//Se é função
						if(strpos($inicializador,'(') !== false){
							$pv = '';
							if(substr($inicializador, -1) != ';'){
								$pv = ';';
							}
							eval('$ret[$campo] = '.$inicializador.$pv);
						}else{
							$ret[$campo] = $inicializador;
						}
					}
				}
			}else{
				$ret[$campo] = $dados[$campo];
			}
		}
		
		return $ret;
	}
	
	private function getDadosBrowser($param){
		$ret = [];
		$matriz = $this->getMatriz('BROWSER');
		$param['ajustaDatas'] = false;
		$rows = $this->executaSQL($this->_nomeCamposBrowserSQL, $param);
		foreach ($rows as $row){
			$temp = [];
			foreach ($matriz as $campo => $valor){
				if($this->_sys003[$campo]['real'] != 'V'){
					//if($this->_sys003[$campo]['tipo'] == 'D' && $row[$campo]!= ''){
						//$temp[$campo] = datas::dataS2D($row[$campo]);
					//	$temp[$campo] = $row[$campo];
					//}else{
					//	$temp[$campo] = $row[$campo];
					//}
					$temp[$campo] = $row[$campo];
				}
				//Se tem "OPCOES" preenche o valor corretamente
				if(!empty($this->_sys003[$campo]['opcao'])){
					$opcoes = $this->getOpcoes($this->_sys003[$campo]['opcao']);
					if(isset($opcoes[$row[$campo]])){
						$temp[$campo] = $opcoes[$row[$campo]];
					}
				}
				if(!empty($this->_sys003[$campo]['tabela_itens'])){
					$temp[$campo] = $this->getDescTabelaItens($this->_sys003[$campo]['tabela_itens'], $row[$campo]);
				}
			}
			foreach ($matriz as $campo => $valor){
				$funcao = $this->_sys003[$campo]['funcao_browser'];
				if($this->_sys003[$campo]['real'] == 'V' || !empty($funcao)){
					
					if(!empty($funcao)){
						$campoParametros = $this->separaFuncaoBrowser($funcao);
						if(count($campoParametros) > 0){
							foreach ($campoParametros as $campoP => $string){
								$funcao = str_replace($string, "'".$temp[$campoP]."'", $funcao);
							}
						}
						if(substr($funcao, -1) != ';'){
							$funcao .= ';';
						}
//echo "Executando: $campo ".'$temp[$campo] = '.$funcao."<br>";
						try {
							eval('$temp[$campo] = '.$funcao);
						} catch (Exception $e) {
							gravaLog('erro_crud','Erro ao executar eval: $temp[$campo] = '.$funcao, 5);
						}
						
					}else{
						$temp[$campo] = '';
					}
				}
			}
			$ret[] = $temp;
		}
//print_r($ret);		
		return $ret;
	}
	
	private function executaSQL($camposArray, $param){
		$ret = [];
		$where = '';
		if(is_array($camposArray) && count($camposArray) > 0){
			$campos = implode(',', $camposArray);
		}else{
			$campos = implode(',', $this->_camposReais);
			$camposArray = $this->_camposReais;
		}
		
		if(isset($param['where']) && !empty($param['where'])){
			$where = " AND ".$param['where'];
		}
		
		if(!empty($this->_sys002['campoAtivo'])){
			$where .= " AND ".$this->_sys002['campoAtivo']." = 'S'";
		}
		
		if($this->_filtraCliente){
			$where .= " AND cliente = '".getCliente()."'";
		}
		if(!empty($where)){
			$where = "WHERE 1=1 ".$where;
		}

		if(isset($param['orderby']) && !empty($param['orderby'])){
			$where .= " ORDER BY ".$param['orderby'];
		}
		
		$sql = "SELECT $campos FROM ".$this->_tabela." ".$where;
//echo "$sql <br>\n";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				foreach ($camposArray as $campo){
					if($this->_sys003[$campo]['tipo'] == 'D' && $row[$campo] != '' && (!isset($param['ajustaDatas']) || $param['ajustaDatas'] == true)){
						//Não ajusta a data quando for para o browser
						$temp[$campo] = datas::dataS2D($row[$campo]);
					}else{
						$temp[$campo] = $row[$campo];
					}
				}
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function getSys002($campo){
		$ret = '';
		if(isset($this->_sys002[$campo])){
			$ret = $this->_sys002[$campo];
		}
		return $ret;
	}
	
	private function getCampos($tipo = ''){
		$ret = [];
		
		foreach ($this->_sys003 as $campo){
			if($tipo == '' || ($tipo == 'browser' && $campo['browser'] == 'S')){
				$ret[] = $campo;
			}
		}
		return $ret;
	}
	
	private function getCamposObrigatorios(){
		$ret = [];
		
		foreach ($this->_sys003 as $campo){
			if($campo['obrigatorio'] == 'S'){
				$ret[] = $campo['campo'];
			}
		}
		return $ret;
	}
	
	//-------------------------------------------------------------------------------------------- UTEIS --------------
	private function jsConfirmaExclusao($titulo){
		addPortaljavaScript('function confirmaExclusao(link,id,desc){');
		addPortaljavaScript('	if (confirm('.$titulo.')){');
		//$portal_javaScript[] = '	if (confirm("Confirma a EXCLUSAO? \n\n ERC:"+erc+" - "+desc+"\n\n Periodo:"+periodo)){';
		addPortaljavaScript('		setLocation(link+id);');
		addPortaljavaScript('	}');
		addPortaljavaScript('}');
	}
	private function separaFuncaoBrowser($funcao){
		$temp = [];
		$quant = substr_count($funcao, '@@');
		$primeira = strpos($funcao, '@@');
		$funcao = substr($funcao, $primeira,strlen($funcao) - $primeira);
		for($i=0;$i<$quant;$i++){
			$tam = 0;
			for($e=0;$e<strlen($funcao);$e++){
				$letra = substr($funcao, $e, 1);
				if($letra == '@' || ($letra >= 'a' && $letra <= 'z') || ($letra >= 'A' && $letra <= 'Z') || ($letra >= '0' && $letra <= '9') || $letra == '_'){
					$tam++;
				}else{
					break;
				}
				
			}
			$temp[] = substr($funcao, 0, $tam);
			$funcao = substr($funcao, $tam, strlen($funcao) - $tam);
			$primeira = strpos($funcao, '@@');
			$funcao = substr($funcao, $primeira,strlen($funcao) - $primeira);
		}
		
		$ret = [];
		if(count($temp) > 0){
			foreach ($temp as $c){
				$campo = str_replace('@@', '', $c);
				$ret[$campo] = $c;
			}
			
		}
		return $ret;
	}
	
	//-------------------------------------------------------------------------------------------- VO -----------------
	
	private function montaSQL($tipo, $dados, $campos = [], $param = []){
		$sql = '';
		$sets = [];
		$mascarasAlimpar = array('cpf','telefone','cep','cnpj');
		$chave = $this->getSys002('chave');
		if($tipo == 'UPDATE'){
			$sql = 'UPDATE '.$this->_tabela.' SET ';
			foreach ($campos as $c){
//echo "Campo: $c - Tipo: ".$this->_sys003[$c]['tipo']."  Mascara: ".$this->_sys003[$c]['mascara']."<br>\n";
				if($this->_sys003[$c]['tipo'] == 'D' && $dados[$c] != ''){
					$sets[] = $c." = '".datas::dataD2S($dados[$c])."'";
				}elseif(($this->_sys003[$c]['tipo'] == 'N' || $this->_sys003[$c]['tipo'] == 'V') && !empty($this->_sys003[$c]['mascara'])){
					$sets[] = $c." = '".ajustaValor($dados[$c])."'";
				}else{
					$dados[$c] = str_replace("'", "\'", $dados[$c]);
					if(array_search($this->_sys003[$c]['mascara'], $mascarasAlimpar) !== false){
						$dados[$c] = $this->limpaMascara($dados[$c]);
						$sets[] = $c." = '".$dados[$c]."'";
					}else{
						$sets[] = $c." = '".$dados[$c]."'";
					}
				}
			}
			$sql .= implode(',', $sets);
			$sql .= " WHERE ".$chave." = '".$dados[$chave]."'";
			if(isset($param['where']) && $param['where'] != ''){
				$sql .= " AND ".$param['where'];
			}
			if($this->_filtraCliente){
				$sql .= " AND cliente = '".getCliente()."'";
			}
		}elseif($tipo == 'INSERT'){
			$camposInsert = implode(',', $campos);
			$campoChave = true;
			if(strpos($camposInsert, $chave) === false){
				$camposInsert = $chave.','.$camposInsert;
				$campoChave = false;
			}
			//Valores
			$val = [];
			foreach ($campos as $c){
//echo "Campo: $c - Tipo: ".$this->_sys003[$c]['tipo']."  Mascara: ".$this->_sys003[$c]['mascara']."<br>\n";
				$inicializador = $this->_sys003[$c]['inicializador'];
				if(isset($dados[$c])){
					//se o campo for data ajusta
					if($this->_sys003[$c]['tipo'] == 'D' && $dados[$c] != ''){
						$val[$c] = "'".datas::dataD2S($dados[$c])."'";
					}else{
						//$val[$c] = "'".$dados[$c]."'";
						if(array_search($this->_sys003[$c]['mascara'], $mascarasAlimpar) !== false){
							$dados[$c] = $this->limpaMascara($dados[$c]);
							$val[$c] = "'".$dados[$c]."'";
						}elseif(($this->_sys003[$c]['tipo'] == 'N' || $this->_sys003[$c]['tipo'] == 'V') && !empty($this->_sys003[$c]['mascara'])){
							$val[$c] = "'".ajustaValor($dados[$c])."'";
						}else{
							
							$val[$c] = "'".$dados[$c]."'";
						}
					}
				}else{
					if($inicializador == 'AUTO'){
						$val[$c] = 'null';
					}elseif(strpos($inicializador,'(') !== false){
						$pv = '';
						if(substr($inicializador, -1) != ';'){
							$pv = ';';
						}
						$valor = '';
						eval('$valor = '.$inicializador.$pv);
						$val[$c] = "'".$valor."'";
					}elseif($inicializador != ''){
						$val[$c] = $inicializador;
					}else{
						$val[$c] = "''";
					}
				}
			}
//print_r($val);
			$camposValores = implode(',', $val);
			if(!$campoChave){
				$inicializador = $this->_sys003[$chave]['inicializador'];
				if($inicializador == 'AUTO'){
					$camposValores = 'null,'.$camposValores;
				}elseif(strpos($inicializador,'(') !== false){
					$pv = '';
					if(substr($inicializador, -1) == ';'){
						//retira o ;
						$inicializador = substr($inicializador, 0, -1);
					}
					eval('$$camposValores = '.$inicializador.'.$camposValores;');
				}else{
					$camposValores = "'".$inicializador."',".$camposValores;
				}
			}
			$sql = "INSERT INTO ".$this->_tabela." ($camposInsert) VALUES ($camposValores)";
		}elseif($tipo == 'DELETE'){
			$sql = "DELETE FROM ".$this->_tabela." WHERE $chave = '".$dados[$chave]."'";
			if($this->_filtraCliente){
				$sql .= " AND cliente = '".getCliente()."'";
			}
		}
//echo "$sql<br>";		
		return $sql;
	}
	
	private function carregaSys002(){
		if(!empty($this->_tabela)){
			$sql = "SELECT * FROM sys002 WHERE tabela = '".$this->_tabela."'";
			$rows = query($sql);
			if(count($rows) > 0){
				$this->_sys002['descricao'] = $rows[0]['descricao'];
				$this->_sys002['chave'] 	= $rows[0]['chave'];
				$this->_sys002['tipo'] 		= $rows[0]['chave_tipo'];
				$this->_sys002['chaveAuto']	= $rows[0]['chave_auto'];
				$this->_sys002['campo'] 	= $rows[0]['campo_desc'];
				$this->_sys002['etiqueta'] 	= $rows[0]['etiqueta'];
				$this->_sys002['campoAtivo']= $rows[0]['campoativo'];
				$this->_sys002['icone'] 	= $rows[0]['icone'];
				$this->_sys002['unico'] 	= $rows[0]['unico'];
			}
		}
	}
	
	private function carregaSys003(){
		$sql = "SELECT * FROM sys003 WHERE tabela = '".$this->_tabela."' AND usado = 'S' ORDER BY ordem";
		$rows = query($sql);
		if(count($rows) > 0){
			
			foreach ($rows as $row){
				$temp = [];
				
				foreach ($this->_camposSys003 as $campo){
					$temp[$campo] = $row[$campo];
				}
				
				$this->_sys003[$temp['campo']] = $temp;
				$this->_nomeCampos[$temp['campo']] = $temp['campo'];
				if($temp['browser'] == 'S'){
					$this->_camposBrowser[$temp['campo']] = $temp;
					//Todos os campos que aparecem no browser
					$this->_nomeCamposBrowser[$temp['campo']] = $temp['campo'];
					//Campos reais
					if($temp['real'] != 'V'){
						$this->_nomeCamposBrowserSQL[$temp['campo']] = $temp['campo'];
					}else{
						
					}
				}
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
			}
		}
		
		$this->getPastas();
	}
	
	//--------------------------------------------------------------------------------------------------- UTEIS ------------------------
	
	private function getOpcoes($opcao){
		$ret = [];
		
		$temp = explode(';', $opcao);
		if(count($temp) > 0){
			foreach ($temp as $t){
				$a = explode('=', $t);
				if(count($a) > 1){
					$ret[$a[0]] = $a[1];
				}
			}
		}
		
		return $ret;
	}
	
	private function getDescTabelaItens($tabela_itens, $valor){
		$ret = $valor;
		if(!empty($ret)){
			$tab = explode('|', $tabela_itens);
			if(count($tab) > 2){
				$tabela = $tab[0];
				$id = $tab[1];
				$desc = $tab[2];
				$sql = "SELECT $desc FROM $tabela WHERE $id = '$valor'";
				$rows = query($sql);
				if(isset($rows[0][$desc])){
					$ret = $rows[0][$desc];
				}
			}
		}
		
		return $ret;
	}

	private function getPastas(){
		$this->_pastas = [];
		$this->_quantPastas = 1;
		$sql = "SELECT * FROM sys008 WHERE tabela = '".$this->_tabela."' ORDER BY pasta";
		$rows = query($sql);
		if(count($rows) > 0){
			$this->_quantPastas = count($rows);
			foreach ($rows as $row){
				$this->_pastas[$row['pasta']] = $row['descricao'];
			}
		}
	}
	
	private function verificaChave($dados, $chavesUnicas){
		$ret = true;
		$where = '';
//print_r($chavesUnicas);
//print_r($dados);
		foreach ($chavesUnicas as $chave){
			if(!empty($where)){
				$where .= ' AND ';
			}
			$where .= $chave." = '".$dados[$chave]."'";
		}
		
		$sql = "SELECT * FROM ".$this->_tabela." WHERE ".$where;
//echo $sql."<br>\n";
		$rows = query($sql);
		if(count($rows) > 0){
			$ret = false;
		}
		
		return $ret;
	}
	
	private function limpaMascara($string){
		$string = str_replace('(', '', $string);
		$string = str_replace(')', '', $string);
		$string = str_replace(' ', '', $string);
		$string = str_replace('-', '', $string);
		$string = str_replace('/', '', $string);
		$string = str_replace('\\', '', $string);
		$string = str_replace('.', '', $string);
		
		return $string;
	}
}