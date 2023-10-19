<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class usuarios{
	var $funcoes_publicas = array(
			'index' 	    => true,
			'perfil' 		=> true,
			'gerenciar'     => true,
			'salvarUsuario' => true,
	);
	
	private $_programa;
	
	private $_info;
	
	private $_dados;
	
	function __construct(){
		$this->_programa = 'core_'.get_class($this);
		
		$this->_info = new syscad01('sys001');
		
		$temp = '
			function marcarTodos(modulo, marcado){
				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
			}
			';
		addPortaljavaScript($temp);
	}
	
	function index(){
		$campos_browser = array();
		$campos_todos = array('id');
		$temp = $this->_info->getCampos('');
		foreach ($temp as $campo_array){
			$teste = $campo_array;
			$teste['width'] = $campo_array['tambrowser'];
			$campos_todos[] = $campo_array['campo'];
			//$campos_todos[] = $teste;
			if($campo_array['browser'] == 'S' && $campo_array['campo'] != 'apelido'){
				//$campos_browser[] = $campo_array;
				$campos_browser[] = $teste;
			}
		}
		
		$permissao = $this->getPermissao();
		$this->_dados = $this->getDados($campos_todos, 'WHERE cliente = ' . getCliente() . ' AND nivel <= ' . $permissao);
		
		$param = array(
				'scroll' => true,
		);
		$bw = new tabela03($param, 'Usuários');
		foreach ($campos_browser as $campo_coluna){
			$bw->addColuna($campo_coluna);
		}
		$bw->setDados($this->_dados);
		
		
		$botao = array(
				'texto' => 'Cadastrar Usuário',
				'icone' => 'fa-user-plus',
				'onclick' => "setLocation('index.php?menu=admin.usuarios.gerenciar.adiciona')",
		);
		
		/*
		 $botao = array(
		 'texto' => 'Cadastrar Usuário',
		 'onclick' => "setLocation('index.php?menu=admin.usuarios.gerenciar.perfil')",
		 );
		 */
		$bw->addBotaoTitulo($botao);
		
		
		$param = array();
		$param['texto'] = 'Editar';
		$param['icone'] = 'glyphicon-pencil';
		$param['link'] 	= 'index.php?menu=admin.usuarios.gerenciar.editar&usuario=';
		$param['textoAlt'] = 'Editar o usuário';
		$param['coluna']= 'id';
		$param['flag'] 	= '';
		$param['width'] = 30;
		$bw->addAcao($param);
		
		return '' . $bw;
	}
	
	private function getDados($campos, $where = ''){
		$ret = array();
		
		$sql = 'SELECT ' . implode(', ', $campos) . ' FROM sys001 ' . $where;
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $linha){
				$temp = array();
				foreach ($campos as $camp){
					if($camp != 'tipo'){
						$temp[$camp] = $linha[$camp];
					}
					else{
						$temp[$camp] = getTabelaDesc('000014', $linha[$camp]);
					}
				}
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	public function gerenciar(){
		$operacao = getOperacao();
		
		
		switch($operacao){
			case "adiciona":
				$ret = $this->addUsuarios();
				break;
			case "editar":
				$user = $_GET['usuario'];
				$ret = $this->editarUsuario($user);
				break;
			case "perfil":
				$ret = $this->perfil();
				break;
			default:
				$ret = $this->index();
		}
		
		return $ret;
	}
	
	private function addUsuarios(){
		//add user
		return $this->formEdicao('add');
	}
	
	private function editarUsuario($user = ''){
		//editar mas aponta pro add passando algum paramentro
		$sql = 'SELECT * FROM sys001 WHERE id = ' . $user;
		$rows = query($sql);
		
		return $this->formEdicao('edit', $rows[0], $user);
	}
	
	private function formEdicao($operacao, $dados = array(), $id = ''){
		$form1 = new form02();
		$campos = array('user', 'nome', 'email', 'tipo', 'apelido', 'depto', 'cargo', 'inicial', 'fone1', 'fone2', 'ativo', 'senha1', 'senha2', 'skype');
		
		$sn 		= tabela("000003","desc",false);
		$tipoUser	= tabela("000014","desc",false);
		
		if(!is_array($dados) || !count($dados) > 0){
			foreach ($campos as $camp){
				$dados[$camp] = '';
			}
		}
		
		if($operacao == 'perfil'){
			$dados['tipo'] = $this->converteUser($dados['tipo']);
		}
		
		if($operacao == 'add'){
			//form para incluir
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[user]'	            , 'etiqueta' => 'Usuário'				, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['user']              , 'pasta'	=> 0	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[nome]'	            , 'etiqueta' => 'Nome'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['nome']              , 'pasta'	=> 0	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[email]'	            , 'etiqueta' => 'Email'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['email']             , 'pasta'	=> 1	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[tipo]'	            , 'etiqueta' => 'Tipo'					, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['tipo']              , 'pasta'	=> 0	, 'lista' => $tipoUser	                           , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			//$form1->addCampo(array('id' => '', 'campo' => 'formUser[apelido]'	        , 'etiqueta' => 'Apelido'				, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['apelido']           , 'pasta'	=> 0	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[depto]'	            , 'etiqueta' => 'Departamento'			, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['depto']             , 'pasta'	=> 0	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[cargo]'	            , 'etiqueta' => 'Cargo'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['cargo']             , 'pasta'	=> 0	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[inicial]'	        , 'etiqueta' => 'Programa Inicial'		, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['inicial']           , 'pasta'	=> 0	, 'lista' => $this->getProgramasIniciais('')	   , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[skype]'          	, 'etiqueta' => 'Skype'					, 'tipo' => 'T' 	, 'tamanho' => '100','linhas' => '', 'valor' => $dados['skype']             , 'pasta'	=> 1	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[fone1]'          	, 'etiqueta' => 'Fone1'					, 'tipo' => 'T' 	, 'tamanho' => '11', 'linhas' => '', 'valor' => $dados['fone1']             , 'pasta'	=> 1	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[fone2]'          	, 'etiqueta' => 'Fone2'					, 'tipo' => 'T' 	, 'tamanho' => '11', 'linhas' => '', 'valor' => $dados['fone2']             , 'pasta'	=> 1	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[ativo]'          	, 'etiqueta' => 'Ativo'					, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo']             , 'pasta'	=> 0	, 'lista' => $sn		                           , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha1]'          	, 'etiqueta' => 'Senha'					, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => ''				            , 'pasta'	=> 2	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 6, 'obrigatorio' => true));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha2]'          	, 'etiqueta' => 'Confirma Senha'		, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => ''				            , 'pasta'	=> 2	, 'lista' => ''			                           , 'validacao' => '', 'largura' => 6, 'obrigatorio' => true));
			
			$form1->setPastas(array('Geral', 'Contato', 'Senha', 'Acessos', 'Ações'));
			
			$form1->addConteudoPastas(3, $this->getFormPermissoes(''));
			$form1->addConteudoPastas(4, $this->novoFormAcoes(''));
			
			$form1->setEnvio(getLink() . 'salvarUsuario.incluir', 'formUser');
			return '' . addBoxInfo('Inclusão de Usuário', '' . $form1);
		}
		if($operacao == 'edit' && $id != ''){
			$dados['senha1'] = '';
			$dados['senha2'] = '';
			if(!isset($dados['user'])){
				$dados['user'] = getAppVar('user_editado');
			}
			putAppVar('user_editado', $dados['user']);
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[user]'	            , 'etiqueta' => 'Usuário'				, 'tipo' => 'I' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['user']              , 'pasta'	=> 0	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[nome]'	            , 'etiqueta' => 'Nome'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['nome']              , 'pasta'	=> 0	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[email]'	            , 'etiqueta' => 'Email'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['email']             , 'pasta'	=> 1	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[tipo]'	            , 'etiqueta' => 'Tipo'					, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['tipo']              , 'pasta'	=> 0	, 'lista' => $tipoUser	                                             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			//$form1->addCampo(array('id' => '', 'campo' => 'formUser[apelido]'	        , 'etiqueta' => 'Apelido'				, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['apelido']           , 'pasta'	=> 0	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[depto]'	            , 'etiqueta' => 'Departamento'			, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['depto']             , 'pasta'	=> 0	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[cargo]'	            , 'etiqueta' => 'Cargo'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['cargo']             , 'pasta'	=> 0	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[inicial]'	        , 'etiqueta' => 'Programa Inicial'		, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['inicial']           , 'pasta'	=> 0	, 'lista' => $this->getProgramasIniciais($dados['user'])			 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[skype]'          	, 'etiqueta' => 'Skype'					, 'tipo' => 'T' 	, 'tamanho' => '100','linhas' => '', 'valor' => $dados['skype']             , 'pasta'	=> 1	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[fone1]'          	, 'etiqueta' => 'Fone1'					, 'tipo' => 'T' 	, 'tamanho' => '11', 'linhas' => '', 'valor' => $dados['fone1']             , 'pasta'	=> 1	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[fone2]'          	, 'etiqueta' => 'Fone2'					, 'tipo' => 'T' 	, 'tamanho' => '11', 'linhas' => '', 'valor' => $dados['fone2']             , 'pasta'	=> 1	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[ativo]'          	, 'etiqueta' => 'Ativo'					, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo']             , 'pasta'	=> 0	, 'lista' => $sn		                                             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha1]'          	, 'etiqueta' => 'Senha'					, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => ''				            , 'pasta'	=> 2	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 6, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha2]'          	, 'etiqueta' => 'Confirma Senha'		, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => ''				            , 'pasta'	=> 2	, 'lista' => ''			                                             , 'validacao' => '', 'largura' => 6, 'obrigatorio' => false));
			
			
			$form1->setPastas(array('Geral', 'Contato', 'Senha', 'Acessos', 'Ações'));
			
			$form1->addConteudoPastas(3, $this->getFormPermissoes($dados['user']));
			$form1->addConteudoPastas(4, $this->novoFormAcoes($dados['user']));
			
			
			
			$form1->setEnvio(getLink() . 'salvarUsuario.editar', 'formUser');
			$param = [];
			$p = array();
			$p['onclick'] = "setLocation('".getLink()."index')";
			$p['tamanho'] = 'pequeno';
			$p['cor'] = 'danger';
			$p['texto'] = 'Cancelar';
			$param['botoesTitulo'][] = $p;
			return '' . addBoxInfo('Edição de Usuário', '' . $form1,$param);
			//$form2 = new form02();
			//$form2->addConteudoPastas(3, $teste_box);
			
		}
		if($operacao == 'perfil'){
			$dados['senha1'] = '';
			$dados['senha2'] = '';
			$dados['ativo'] = $dados['ativo'] == 'S' ? 'Sim' : 'Não';
			//form para editar + imagem de perfil
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[user]'	            , 'etiqueta' => 'Usuário'				, 'tipo' => 'I' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['user']              , 'pasta'	=> 0	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[nome]'	            , 'etiqueta' => 'Nome'					, 'tipo' => 'I' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['nome']              , 'pasta'	=> 0	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[email]'	            , 'etiqueta' => 'Email'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['email']             , 'pasta'	=> 1	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[tipo]'	            , 'etiqueta' => 'Tipo'					, 'tipo' => 'I' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['tipo']              , 'pasta'	=> 0	, 'lista' => $tipoUser	                                     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			//$form1->addCampo(array('id' => '', 'campo' => 'formUser[apelido]'	        , 'etiqueta' => 'Apelido'				, 'tipo' => 'I' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['apelido']           , 'pasta'	=> 0	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => , 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[depto]'	            , 'etiqueta' => 'Departamento'			, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['depto']             , 'pasta'	=> 0	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[cargo]'	            , 'etiqueta' => 'Cargo'					, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['cargo']             , 'pasta'	=> 0	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[inicial]'	        , 'etiqueta' => 'Programa Inicial'		, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['inicial']           , 'pasta'	=> 0	, 'lista' => $this->getProgramasIniciais($dados['user'])	 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[skype]'          	, 'etiqueta' => 'Skype'					, 'tipo' => 'T' 	, 'tamanho' => '100','linhas' => '', 'valor' => $dados['skype']             , 'pasta'	=> 1	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[fone1]'          	, 'etiqueta' => 'Fone1'					, 'tipo' => 'T' 	, 'tamanho' => '11', 'linhas' => '', 'valor' => $dados['fone1']             , 'pasta'	=> 1	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[fone2]'          	, 'etiqueta' => 'Fone2'					, 'tipo' => 'T' 	, 'tamanho' => '11', 'linhas' => '', 'valor' => $dados['fone2']             , 'pasta'	=> 1	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 3, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[ativo]'          	, 'etiqueta' => 'Ativo'					, 'tipo' => 'I' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo']             , 'pasta'	=> 0	, 'lista' => '' 		                                     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha1]'          	, 'etiqueta' => 'Senha'					, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['senha1']		    , 'pasta'	=> 2	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 6, 'obrigatorio' => false));
			$form1->addCampo(array('id' => '', 'campo' => 'formUser[senha2]'          	, 'etiqueta' => 'Confirma Senha'		, 'tipo' => 'S' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['senha2']            , 'pasta'	=> 2	, 'lista' => ''			                                     , 'validacao' => '', 'largura' => 6, 'obrigatorio' => false));
			
			$form1->addCampo(array('id' => '', 'campo' => 'formArquivo'	, 'etiqueta' => 'Selecione a imagem...'	,'largura' => 4, 'tipo' => 'F'	, 'tamanho' => '60' , 'linhas' => '', 'valor' => ''	, 'lista' => ''	, 'validacao' => '', 'pasta' => 3, 'obrigatorio' => false));
			
			$form1->setPastas(array('Geral', 'Contato', 'Senha', 'Imagem'));
			
			$form1->addConteudoPastas(3, $this->getFormImagem($this->getUsuarioInfo($id), true));
			
			$form1->setEnvio(getLink() . 'salvarUsuario.perfil', 'formUser');
			return '' . addBoxInfo('Perfil', '' . $form1);
			
			
		}
	}
	
	public function salvarUsuario(){
		global $config;
		$ret = '';
		$operacao = getOperacao();
		$dados_brutos = $_POST['formUser'];
		if(!isset($dados_brutos['apelido'])){
			$dados_brutos['apelido'] = '';
		}
		if(isset($dados_brutos['tipo'])){
			if(strlen($dados_brutos['tipo']) > 2){
				$dados_brutos['tipo'] = $this->converteUser($dados_brutos, false);
			}
		}
		
		if($operacao == 'incluir'){
			
			if($dados_brutos['senha1'] != $dados_brutos['senha2']){
				addPortalMensagem('', 'As senhas informadas não são iguais', 'erro');
				$ret = $this->formEdicao('add', $dados_brutos);
			}
			else{
				$sql = "SELECT * FROM sys001 WHERE USER = '" . $dados_brutos['user'] . "'";
				$rows = query($sql);
				if(is_array($rows) && count($rows) > 0){
					addPortalMensagem('', 'Esse usuário já esta em uso', 'erro');
					$dados_brutos['user'] = '';
					$ret = $this->formEdicao('add', $dados_brutos);
				}
				else{
					$dados = array();
					$dados['cliente'] = getCliente();
					foreach ($dados_brutos as $campo => $dado_unitario){
						if($campo != 'senha1' && $campo != 'senha2'){
							$dados[$campo] = $dado_unitario;
						}
						else{
							$dados['senha'] = $dado_unitario;
						}
					}
					
					if((!isset($dados_brutos['inicial']) || empty(trim($dados_brutos['inicial']))) && isset($config['site']['programaInicial']) && !empty(trim($config['site']['programaInicial']))){
						$dados['inicial'] = $config['site']['programaInicial'];
					}
					
					$sql = montaSQL($dados, 'sys001');
					query($sql);
					$this->atualizaAcesso($dados['user']);
					$this->atualizaAcoes($dados['user']);
					$ret = $this->index();
				}
			}
		}
		
		if($operacao == 'editar'){
			$user = getAppVar('user_editado');
			if($this->chegarSenha($user, $dados_brutos['senha1'], $dados_brutos['senha2'])){
				addPortalMensagem('', 'As senhas informadas não são iguais', 'erro');
				$ret = $this->formEdicao('edit', $dados_brutos, $user);
			}
			else{
				$dados = array();
				$dados['cliente'] = getCliente();
				foreach ($dados_brutos as $campo => $dado_unitario){
					if($campo != 'senha1' && $campo != 'senha2'){
						$dados[$campo] = $dado_unitario;
					}
					else{
						if($dado_unitario != ''){
							$dados['senha'] = $dado_unitario;
						}
					}
				}
				
				if(getAppVar('user_editado') == null){
					addPortalMensagem('', 'Houve um erro ao editar o usuário', 'erro');
					$ret = $this->formEdicao('edit', $dados_brutos, $user);
				}
				else{
					$where = "user = '" . $user . "' AND cliente = '" . getCliente() . "'";
					$sql = montaSQL($dados, 'sys001', 'UPDATE', $where);
					query($sql);
					$this->atualizaAcesso($user);
					$this->atualizaAcoes($user);
					$ret = $this->index();
				}
			}
		}
		if($operacao == 'perfil'){
			$user = getUsuario();
			if(isset($dados_brutos['tipo'])){
				$dados_brutos['tipo'] = $this->converteUser($dados_brutos['tipo'], false);
			}
			if($this->chegarSenha(getUsuario(), $dados_brutos['senha1'], $dados_brutos['senha2'])){
				addPortalMensagem('', 'As senhas informadas não são iguais', 'erro');
				$ret = $this->perfil();
			}
			
			else{
				$ret = $this->perfil();
				$dados = array();
				$dados['cliente'] = getCliente();
				foreach ($dados_brutos as $campo => $dado_unitario){
					if($campo != 'senha1' && $campo != 'senha2'){
						$dados[$campo] = $dado_unitario;
					}
					else{
						if($dado_unitario != '' ){
							$dados['senha'] = $dado_unitario;
						}
					}
					
					$where = "user = '" . getUsuario() . "' AND cliente = " . getCliente();
					$sql = montaSQL($dados, 'sys001', 'UPDATE', $where);
					query($sql);
					
				}
				
				if(isset($_FILES['formImagem'])){
					global $config;
					$file = $_FILES['formImagem'];
					
					if($file['error'] != 4 && $file['size'] != 0){
						if($file['error'] != 0){
							addPortalMensagem('', 'Não foi possivel fazer upload da imagem', 'erro');
						}
						
						else{
							
							$arq_temp = $_FILES['formImagem']['tmp_name'];
							if(is_uploaded_file($arq_temp)){
								$ext = explode('.', $_FILES['formImagem']['name']);
								$ext = strtolower(end($ext));
								if(in_array($ext, array('jpg'))){
									$arq_dest = $config['baseS3']  . 'imagens/avatares/' . getUsuario() . '.' . $ext;
									
									$upd = move_uploaded_file($arq_temp, $arq_dest);
									if($upd === false){
										addPortalMensagem('', 'Não foi possivel fazer upload da imagem', 'erro');
									}
								}
								else{
									addPortalMensagem('', 'Formato de imagem não suportado, user uma imagem jpg', 'erro');
								}
							}
							else{
								addPortalMensagem('', 'Não foi possivel fazer upload da imagem', 'erro');
							}
						}
					}
				}
				
				
			}
		}
		
		return $ret;
	}
	
	public function perfil(){
		//quando clica em perfil
		$sql = 'SELECT * FROM sys001 WHERE id = ' . getUsuario('id');
		$rows = query($sql);
		return $this->formEdicao('perfil', $rows[0], getUsuario('id'));
	}
	
	private function getPermissao(){
		$ret = '';
		
		$id = getUsuario('id');
		
		$sql = 'SELECT nivel FROM sys001 WHERE id = ' . $id;
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			$ret = $rows[0]['nivel'];
		}
		
		return $ret;
	}
	
	

	

	

	

	
	private function getFormImagem($dados){
		global $config, $nl;
		$ret = '';
		//formbase01::setLayout('basico');
		$usuario = $dados['user'];
		//print_r($dados);
		$avatar = $config['baseS3'].'imagens/avatares/'.$usuario.'.jpg';
		if(!file_exists($avatar)){
			$avatar = $config['baseS3'].'imagens/avatares/'.$usuario.'.png';
			if(!file_exists($avatar)){
				$avatar = $config['baseS3'].'imagens/avatares/'.$usuario.'.gif';
				if(!file_exists($avatar)){
					$avatar = $config['imagens'].'avatares/avatarGenerico.jpg';
				}else{
					$avatar = $config['imagens'].'avatares/'.$usuario.'.gif';
				}
			}else{
				$avatar = $config['imagens'].'avatares/'.$usuario.'.png';
			}
		}else{
			$avatar = $config['imagens'].'avatares/'.$usuario.'.jpg';
		}
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-1"></div>'.$nl;
		$ret .= '	<div  class="col-md-8">'.$nl;
		
		$form = $form = new form01();
		$form->addCampo(array('id' => '', 'campo' => 'formImagem', 'etiqueta' => 'Selecione a imagem...'	, 'tipo' => 'F' , 'tamanho' => '10'	, 'linhas' => '', 'valor' => ''	, 'lista' => ''	, 'validacao' => '', 'obrigatorio' => false));
		
		$ret .= $form;
		$ret .= '	</div>'.$nl;
		$ret .= '	<div  class="col-md-3">'.$nl;
		$ret .= '		<img src="'.$avatar.'" class="img-circle" alt="Imagem do Usuário">'.$nl;
		$ret .= '	</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		//formbase01::setLayout('');
		return $ret;
	}
	
	private function getUsuarioInfo($id){
		$ret = array();
		$sql = "SELECT * FROM sys001 WHERE id = '$id' and cliente = '".getCliente() . "'";
		
		$rows = query($sql);
		
		if(count($rows) == 1){
			$ret['id'] 			= $rows[0]['id'];
			$ret['user'] 		= $rows[0]['user'];
			//$ret['senha'] 	= $rows[0]['senha'];
			$ret['nome'] 		= $rows[0]['nome'];
			$ret['tipo'] 		= $rows[0]['tipo'];
			$ret['email'] 		= $rows[0]['email'];
			$ret['apelido'] 	= $rows[0]['apelido'];
			$ret['cargo'] 		= $rows[0]['cargo'];
			$ret['depto'] 		= $rows[0]['depto'];
			$ret['inicial']		= $rows[0]['inicial'];
			$ret['fone1'] 		= $rows[0]['fone1'];
			$ret['fone2'] 		= $rows[0]['fone2'];
			$ret['ativo'] 		= $rows[0]['ativo'];
		}
		return $ret;
	}
	
	function selecionaAcoes($usuario){
		$ret = array();
		$grupos = array();
		$itens = array();
		$sql = "SELECT * FROM sys014 where ativo = 'S' AND cliente = '".getCliente()."'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$grupos[$row['grupo']] = $row['etiqueta'];
			}
			
			$sql  = " SELECT grupo, sys015.item, etiqueta, descricao, valores, complemento, valor, extra ";
			$sql .= " FROM sys015 ";
			$sql .= " LEFT JOIN sys016 ";
			$sql .= " 	ON usuario = '$usuario' AND sys015.item = sys016.item ";
			$sql .= " WHERE ativo = 'S' and cliente = '".getCliente()."'";
			//echo "SQL: $sql <br>";
			$rows = query($sql);
			$ret['grupo'] = array();
			if(count($rows) > 0){
				foreach ($rows as $row){
					$temp = array();
					$temp['item']		= $row['item'];
					$temp['etiqueta']	= utf8_encode($row['etiqueta']);
					$temp['desc'] 	= utf8_encode($row['descricao']);
					$temp['valores'] 	= $row['valores'];
					$temp['comp'] 	= $row['complemento'];
					$temp['val'] 		= $row['valor'];
					$temp['extra']	= $row['extra'];
					
					$itens[$row['grupo']][] = $temp;
					/*/
					 $i = isset($ret['item'][$row['grupo']]) ? count($ret['item'][$row['grupo']]) : 0;
					 $ret['item'][$row['grupo']][$i]['item']		= $row['item'];
					 $ret['item'][$row['grupo']][$i]['etiqueta']	= utf8_encode($row['etiqueta']);
					 $ret['item'][$row['grupo']][$i]['desc'] 	= utf8_encode($row['descricao']);
					 $ret['item'][$row['grupo']][$i]['valores'] 	= $row['valores'];
					 $ret['item'][$row['grupo']][$i]['comp'] 	= $row['complemento'];
					 $ret['item'][$row['grupo']][$i]['val'] 		= $row['valor'];
					 $ret['item'][$row['grupo']][$i]['extra']	= $row['extra'];
					 
					 if(!in_array($row['grupo'], $ret['grupo'])){
					 $ret['grupo'][] = $row['grupo'];
					 }
					 /*/
				}
			}
			$ret['grupo'] = $grupos;
			$ret['item'] = $itens;
			//print_r($ret);
		}
		return $ret;
	}
	
	private function getConteudoFormAcoes($itens){
		$ret = '';
		
		$dado = array();
		foreach ($itens as $i => $item){
			$e = count($dado);
			$dado[$e]['nome'] 		= $item['item'];
			$dado[$e]['etiqueta'] 	= $item['etiqueta'];
			$dado[$e]['checked'] 	= $item["val"] == 'S' ? true : false;
			$e++;
		}
		$form = new form();
		$form->setTipoForm(2);
		$form->setCheckBox("formUserAcoes", $dado);
		$ret .= $form;
		unset($form);
		return $ret;
	}
	

	
	private function atualizaAcesso($user){
		$this->limpasys115($user);
		$user = "'" . $user . "'";
		
		$acessos = array();
		
		if(isset($_POST['formUserAcessos'])){
			$acessos = $_POST['formUserAcessos'];
		}
		
		if(is_array($acessos) && count($acessos) > 0){
			$temp = array();
			foreach ($acessos as $programa => $status){
				if($status == 'on'){
					$temp[] = "'" . str_replace('__', '.', $programa) . "'";
				}
			}
			
			$perm = "'S'";
			
			foreach ($temp as $dado){
				$sql = "INSERT INTO sys115 (id, cliente, user, programa, perm) VALUES (null, '" . getcliente() . "', " . $user . ", " . $dado . ", " . $perm . ")";
				query($sql);
			}
		}
	}
	
	private function limpasys115($user){
		if($user != ''){
			$sql = "DELETE FROM sys115 WHERE cliente = '" . getCliente() . "' AND user = '" . $user . "'";
			query($sql);
		}
	}
	
	private function atualizaAcoes($user){
		if($user != ''){
			$user = "'" . $user . "'";
			$sql = "DELETE FROM sys016 WHERE usuario = " . $user;
			query($sql);
			$dados = array();
			
			if(isset($_POST['formAcao'])){
				$dados = $_POST['formAcao'];
			}
			
			if(is_array($dados) && count($dados) > 0){
				foreach ($dados as $campo => $valor){
					if($valor != ''){
						if($valor != 'on'){
							$sql = "INSERT INTO sys016 (item, usuario, valor, extra) VALUES ('" . $campo . "', " . $user . ", '" . $valor   . "', '')";
						}
						else{
							$temp = '' . str_replace('CB__', '', $campo);
							$sql = "SELECT tipo FROM sys015 WHERE item = '" . $temp . "'";
							$tipo = query($sql);
							if(is_array($tipo) && count($tipo) > 0){
								$tipo = $tipo[0]['tipo'];
								if($tipo == 'CB'){
									$sql = "INSERT INTO sys016 (item, usuario, valor, extra) VALUES ('" . $temp . "', " . $user . ", 'S', '')";
								}
								else{
									$sql = "INSERT INTO sys016 (item, usuario, valor, extra) VALUES ('" . $temp . "', " . $user . ", '" . $valor   . "', '')";
								}
							}
						}
						if($sql != ''){
							query($sql);
						}
					}
				}
			}
		}
	}
	
	private function converteUser($valor, $dir = true){
		$ret = '';
		if($dir){
			$ret = getTabelaDesc('000014', $valor);
		}
		
		else{
			$sql = "SELECT chave FROM sys005 WHERE descricao = '" . $valor . "' AND tabela = '000014'";
			$temp = query($sql);
			$ret = $temp[0]['chave'];
		}
		return $ret;
	}
	
	private function getProgramasIniciais($usuario){
		$ret = array();
		$ret[0][0] = '';
		$ret[0][1] = '';
		
		$sql = "SELECT
					app002.programa,
					app002.etiqueta,
					app001.etiqueta
				FROM
					app002,
					app001
				WHERE
					app002.programa in (SELECT programa FROM sys115 where cliente = '".getCliente()."' AND user = '$usuario' AND perm = 'S')
					AND app001.nome = app002.modulo
					AND app001.cliente = '".getCliente()."'
					AND app002.cliente = '".getCliente()."'
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
	
	private function chegarSenha($user, $senha1, $senha2){
		$ret = true;
		if($senha1 != $senha2){
			if($senha2 == ''){
				$sql = "select id from sys001 where user = '$user' and senha = '$senha1'";
				$rows = query($sql);
				$ret = !(is_array($rows) && count($rows) > 0);
			}
		}
		else{
			$ret = false;
		}
		
		return $ret;
	}
}