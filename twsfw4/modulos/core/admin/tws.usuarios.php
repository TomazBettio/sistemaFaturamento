<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class usuarios{
	var $funcoes_publicas = array(
			'index' 	    => true,
			'incluir' 		=> true,
			'editar'     => true,
			'salvarUsuario' => true,
	);
	
	//Classe syscad
	private $_syscad;
	
	public function __construct(){
		$param = [];
		$this->_syscad = new syscad01('sys001', $param);
		
		$temp = '
			function marcarTodos(modulo, marcado){
				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
			}
			';
		addPortaljavaScript($temp);
	}
	
	public function index(){
		$ret = '';
		
		$nivel = getUsuario('nivel');
		$dados = $this->_syscad->getDados('browser', ' nivel <= ' . $nivel);
		
		$param = [];
		$param['icone']		= 'fa-user';
		$param['titulo']	= 'Usuários';
		$bw = new tabela01($param);
		$estruturaBrowser = $this->_syscad->getEstrutura('browser');
		foreach ($estruturaBrowser as $campo_coluna){
			$bw->addColuna($campo_coluna);
		}
		$bw->setDados($dados);
		
		$botao = [];
		$botao['texto']		= 'Cadastrar Usuário';
		$botao['onclick']	= "setLocation('".getLink()."incluir')";
		$botao['icone']		= 'fa-user-plus';
		$bw->addBotaoTitulo($botao);
		
		$param = [];
		$param['texto'] 	= 'Editar';
		$param['icone'] 	= 'glyphicon-pencil';
		$param['link'] 		= 'index.php?menu=admin.usuarios.editar&usuario=';
		$param['textoAlt'] 	= 'Editar o usuário';
		$param['coluna']	= $this->_syscad->getCampoChave(true);
		$param['flag'] 		= '';
		$param['width'] 	= 30;
		$bw->addAcao($param);
		
		$ret .= $bw;
		
		return $ret;
	}
	
	public function incluir(){
		$ret = '';
		
		$ret .= $this->montaFormulario();
		
		return $ret;
	}
	

	public function editar(){
		$ret = '';
		
		$id = base64_decode(getParam($_GET, 'usuario'));
		$dados = $this->_syscad->getDadosID($id);
//echo "ID: $id<br>\n";
//print_r($dados);
		if(is_array($dados) && count($dados) > 0){
			$ret .= $this->montaFormulario($id, $dados);
		}else{
			addPortalMensagem('Erro ao recuperar o registro','danger');
			$ret = $this->index();
		}
		
		return $ret;
		
	}
	
	//---------------------------------------------------------------------------------------------------- UI ------------------------
	
	private function montaFormulario($id = '', $dados = []){
		$ret = '';
		
		$param = [];
		$form1 = new form01($param);
		$form1->setBotaoCancela();
		$form1->setPastas($this->_syscad->getPastasDescricoes());
		$estruturaTemp = $this->_syscad->getEstruturaForm();
		$estrutura = [];
		foreach ($estruturaTemp as $est){
			if(empty($id)){
				$est['valor'] = $est['inicializador'];
			}else{
				$est['valor'] = $dados[$est['campo']];
			}
			if($est['campo'] != 'senha'){
				$estrutura[] = $est;
			}
		}
		unset($estruturaTemp);
//print_r($estrutura);
		foreach ($estrutura as $est){
			$form1->addCampo($est);
		}
		
		$senhaObrigatoria = false;
		if(empty($id)){
			$senhaObrigatoria = true;
		}
		$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha1]'          	, 'etiqueta' => 'Senha'					, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => ''				            , 'pasta'	=> 2	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 6, 'obrigatorio' => $senhaObrigatoria));
		$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha2]'          	, 'etiqueta' => 'Confirma Senha'		, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => ''				            , 'pasta'	=> 2	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 6, 'obrigatorio' => $senhaObrigatoria));
		

		
		$form1->addConteudoPastas(3, $this->montaFormPermissoes(''));
		$form1->addConteudoPastas(4, $this->montaFormAcoes(''));
		
		$form1->setEnvio(getLink() . 'salvarUsuario.incluir', 'formUser');

		$param = [];
		$param['icone'] = 'fa-edit';
		$param['titulo'] 	= empty($id) ? 'Inclusão de Usuário' : 'Edição de Usuário';
		$param['conteudo']	= $ret.$form1;
	
		$ret .= addCard($param);
		
		return $ret; 
	}

	private function montaFormPermissoes($usuario){
		$ret = '';
		$retorno1 = $this->getModulosUsuario($usuario);
		foreach ($retorno1 as $teste){
			$temp = $this->montaComboBoxProgramas($usuario, $teste['modulo']);
			if($temp != ''){
				$param = [];
				$param['titulo'] 	= $teste['titulo'];
				$param['conteudo']	= $temp;
				$ret .= addCard($param);
			}
		}
		
		return $ret;
	}
	
	private function montaComboBoxProgramas($usuario, $modulo){
		$ret = '';

		$dados	= $this->getProgramasUsuarios($usuario, $modulo);
		if(is_array($dados) && count($dados) > 0){
			$param = [];
			$param['colunas'] = 3;
			$param['combos']  = $dados;
			$ret = formbase01::formGrupoCheckBox($param);
		}
		
		return $ret;
	}
	
	private function montaFormAcoes($user){
		$ret = '';
		
		$sql = "SELECT sys014.id as sys14id, sys014.grupo as sys14grupo, sys014.etiqueta as sys14etiqueta, item, tipo, opcao, descricao, tamanho, sys015.etiqueta from sys014 join sys015 using (grupo) WHERE  sys014.ativo = 'S' AND sys015.ativo = 'S' ";
		$rows = query($sql);
		$acoes = array();
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $linha){
				if(!isset($acoes[$linha['sys14grupo']])){
					$acoes[$linha['sys14grupo']] = array(
							'id' => $linha['sys14id'],
							'grupo' => $linha['sys14grupo'],
							'etiqueta' => $linha['sys14etiqueta'],
							'acoes'   => array(),
					);
				}
				$temp = array();
				$temp['item'] = $linha['item'];
				$temp['opcao'] = $linha['opcao'];
				$temp['descricao'] = $linha['descricao'];
				$temp['etiqueta'] = $linha['etiqueta'];
				$temp['permitido'] = '';
				$temp['tamanho'] = $linha['tamanho'] != '' && $linha['tamanho'] != 0 ? $linha['tamanho'] : 6;
				$temp['valor'] = '';
				
				if(!isset($acoes[$linha['sys14grupo']]['acoes'][$linha['tipo']])){
					$acoes[$linha['sys14grupo']]['acoes'][$linha['tipo']] = array();
				}
				
				$acoes[$linha['sys14grupo']]['acoes'][$linha['tipo']][$linha['item']] = $temp;
				
			}
			
		}
		
		$sql = "SELECT * FROM sys016 JOIN sys015 using (item) WHERE usuario = '" . $user . "' ";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $linha){
				$acoes[$linha['grupo']]['acoes'][$linha['tipo']][$linha['item']]['permitido'] = 'S';
				$acoes[$linha['grupo']]['acoes'][$linha['tipo']][$linha['item']]['valor'] = $linha['valor'];
			}
		}
		// print_r($acoes);
		foreach ($acoes as $dados){
			$titulo = $dados['etiqueta'];
			$form_normal = new form01();
			$temp = [];
			$form_cb = '';
			$ordem = array('T', 'A', 'N', 'V', 'CB');
			foreach ($ordem as $tipo_atual){
				if(isset($dados['acoes'][$tipo_atual])){
					foreach ($dados['acoes'][$tipo_atual] as $acao_unidade){
						if($tipo_atual == 'CB'){
							$checked = $acao_unidade['valor'] == 'N' || $acao_unidade['valor'] == '' ? false : true;
							
							$campo = array(
									'nome' => 'formAcao['.'CB__' . $acao_unidade['item'].']',
									'etiqueta' => $acao_unidade['descricao'],
									'checked' => $checked,
							);
							$temp[] = $campo;
						}
						else{
							$campo = array(
									'id' => '',
									'campo' => 'formAcao[' . $acao_unidade['item'] . ']',
									'etiqueta' => $acao_unidade['etiqueta'],
									'help' => $acao_unidade['descricao'],
									'tipo' => $tipo_atual,
									'largura' => $acao_unidade['tamanho'],
									'linhas' => '',
									'valor' => $acao_unidade['valor'],
									'pasta' => '',
									'lista' => $acao_unidade['opcao'],
									'validacao' => '',
									'obrigatorio' => false,
							);
							$form_normal->addCampo($campo);
						}
					}
				}
			}
			$form_cb = '';
			if(is_array($temp) && count($temp) > 0){
				$param = [];
				$param['colunas'] = 3;
				$param['combos']  = $temp;
				$form_cb = formbase01::formGrupoCheckBox($param);
			}
			$param = [];
			$param['titulo'] = $titulo;
			$param['conteudo'] = $form_normal . $form_cb;
			$ret .= addCard($param);
		}
		return $ret;
	}
	//---------------------------------------------------------------------------------------------------- GET ------------------------
	
	private function getModulosUsuario($usuario){
		$ret = [];
		$modulos = $this->getModulos($usuario);
		if(is_array($modulos) && count($modulos) >0){
			foreach ($modulos as $mod){
				$temp = [];
				$checked = $mod["checked"] == true ? 'checked="checked"' : "";
				$temp["titulo"] = '<label><input type="checkbox"  onclick="marcarTodos(\''.$mod["nome"].'\',this.checked);"  name="formUserModulos['.$mod["nome"].']" id="" '.$checked.'/>&nbsp;'.$mod["etiqueta"].'</label>';
				$temp["modulo"] = $mod["nome"];
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	private function getModulos($usuario){
		$ret = [];
		$tipoUsuario = getUsuario('tipo', $usuario);
		$sql  = "SELECT nome, etiqueta FROM app001 WHERE ativo = 'S' ";
		if($tipoUsuario != "S"){
			if($tipoUsuario != "A"){
				$sql .= " AND nivel < 500";
			}else{
				$sql .= " AND nivel < 900";
			}
		}
		//echo "SQL: $sql <br>";
		$rows = query($sql);
		if(count($rows) > 0){
			$i = 0;
			foreach ($rows as $row){
				$checked = '';
				$sql = "SELECT app002.programa FROM app002 join sys115 using (programa) where perm = 'S' AND user = '" . $usuario . "'";
				$linhas = query($sql);
				$temp = [];
				foreach ($linhas as $linha_atual){
					$temp[] = "'" . $linha_atual['programa'] . "'";
				}
				
				$ret[$i]["checked"] 	= false;
				if(is_array($temp) && count($temp) > 0){
					$sql = "SELECT * FROM app001 join app002 on (nome = modulo) where  app002.programa not in (" . implode(', ', $temp) . ") AND app002.ativo = 'S'";
					$linhas = query($sql);
					if(is_array($linhas) && count($linhas) == 0){
						$ret[$i]["checked"] 	= true;
					}
				}
				
				
				$ret[$i]["nome"] 		= $row["nome"];
				$ret[$i]["etiqueta"] 	= $row["etiqueta"];
				//$ret[$i]["checked"] 	= true;
				$i++;
			}
		}
		//print_r($ret);
		return $ret;
	}
	
	function getProgramasUsuarios($usuario, $modulo = ""){
		$ret = [];
		$sql  = "SELECT app002.programa, app002.etiqueta, perm, app002.modulo FROM app002 ";
		$sql .= "LEFT JOIN sys115 ON app002.programa = sys115.programa AND sys115.user = '$usuario'  ";
		$sql .= "WHERE app002.ativo = 'S' ";
		if($modulo != ""){
			$sql .= " AND app002.modulo = '$modulo' ";
		}
		// Se o usuário que está operando não for Super e o alterado tb não for Super
		//echo "SQL: $sql <br>";
		$rows = query($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				
				$temp["nome"] 		= 'formUserAcessos['.str_replace(".","__",$row["programa"]).']';
				$temp["etiqueta"] 	= $row["etiqueta"];
				$temp["checked"] 	= $row["perm"] == "S" ? true : false;
				$temp["modulo"] 	= $row["modulo"];
				$temp["classeadd"]	= $row["modulo"];
				
				$ret[] = $temp;
			}
		}
		//print_r($ret);
		return $ret;
	}
	
	//---------------------------------------------------------------------------------------------------- SET ------------------------
	
	
}


function getProgramasIniciais(){
	$ret = array();
	$ret[0][0] = '';
	$ret[0][1] = '';
	
	$usuario = getUsuario();
	
	$sql = "SELECT
					app002.programa,
					app002.etiqueta,
					app001.etiqueta
				FROM
					app002,
					app001
				WHERE
					app002.programa in (SELECT programa FROM sys115 where user = '$usuario' AND perm = 'S')
					AND app001.nome = app002.modulo
				ORDER BY
					app001.etiqueta,
					app002.etiqueta";
	//echo "$sql <br>";
	$rows = query($sql);
	if(count($rows) > 0){
		$i = 1;
		foreach ($rows as $row){
			$ret[$i][0] = $row[0];
			$ret[$i][1] = $row[2] .' - '.$row[1];
			$i++;
		}
	}
	//print_r($ret);
	return $ret;
}