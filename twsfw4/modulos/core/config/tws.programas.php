<?php
/*
 * Data Criação: 11/05/2020
 * Autor: Emanuel
 *
 * Arquivo: class.programas.inc.php
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class programas{
	
	var $funcoes_publicas = array(
		'index'			=> true,
		'editar'        => true,
		'salvar'        => true,
		'excluir' => true,
	);
	
	private $_programa = 'programas_novo';
	
	private $_titulo = '';
	
	private $_relatorio;
	
	public function __construct(){
		$this->_programa = 'core_'.get_class($this);
		
		$this->_titulo = 'Programas';
		
		$temp = '
			function marcarTodos(modulo, marcado){
				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
			}
			';
		addPortaljavaScript($temp);
		
		if(true){
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Módulo'	, 'variavel' => 'MODULO', 'tipo' => 'T', 'tamanho' => '15', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'coreGetModulos();'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Ativo'	, 'variavel' => 'ATIVO'	, 'tipo' => 'T', 'tamanho' => '01', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim;N=Não;T=Todos']);
			
		}
		
	}
	
	public function index(){
		$ret = '';
		
		$this->montaRelatorio();
		
		$filtro = $this->_relatorio->getFiltro();
		
		$modulo = $filtro['MODULO'] ?? '';
		$ativo 	= $filtro['ATIVO'] ?? 'T';
		
		$dados = $this->getDados($modulo, $ativo);
		$this->_relatorio->setDados($dados);
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	public function editar($id = ''){
		if($id == ''){
		    $id = getParam($_GET, 'id', 0);
		    if($id != 0){
		        $id = base64_decode($id);
		    }
			//$id = base64_decode(getParam($_GET, 'id', 0));
		}
		$dados = $this->getDadosPrograma($id);
		
		$param = [];
		$form = new form01($param);
		$form->setBotaoCancela();
		$form->setPastas(array('Geral', 'Usuários'));
		if($id == 0){
			$form->addCampo(array('id' => '', 'campo' => 'formPrograma[programa]' , 'etiqueta' => 'Programa' , 'tipo' => 'T' 	, 'tamanho' => '100', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
		}else{
			$form->addCampo(array('id' => '', 'campo' => 'formPrograma[programa]' , 'etiqueta' => 'Programa', 'tipo' => 'I' 	, 'tamanho' => '100', 'linhas' => '', 'valor' => $dados['programa'], 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
			$temp = $this->montaFormUsuarios($dados['programa']);
			$form->addConteudoPastas(1, $temp);
		}
		$param = [];
		$param['camposChave']	= 'nome';
		$param['campoDescricao']= 'etiqueta';
		$form->addCampo(array('id' => '', 'campo' => 'formPrograma[modulo]'		, 'etiqueta' => 'Módulo'	, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['modulo'] 	, 'pasta'	=> 0, 'lista' => listaTabela('app001',$param)	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
		$form->addCampo(array('id' => '', 'campo' => 'formPrograma[etiqueta]'	, 'etiqueta' => 'Etiqueta'	, 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['etiqueta'] 	, 'pasta'	=> 0, 'lista' => ''								, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
		$form->addCampo(array('id' => '', 'campo' => 'formPrograma[descricao]'	, 'etiqueta' => 'Descrição'	, 'tipo' => 'TA' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['descricao']	, 'pasta'	=> 0, 'lista' => ''								, 'validacao' => '', 'largura' => 8, 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formPrograma[ativo]'		, 'etiqueta' => 'Ativo'		, 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo']		, 'pasta'	=> 0, 'lista' => tabela('000003')				, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
		//$form->setEnvio($url, $nome);
		$form->setEnvio(getLink() . 'salvar&id=' . base64_encode($id), 'formPrograma', 'formPrograma');
		
		$param = [];
		$param['icone'] = 'fa-edit';
		$param['titulo'] = $id == 0 ? 'Incluir Programa' : 'Editar Programa';
		$param['conteudo'] = $form;
		
		$ret = addCard($param);
		
		putAppVar('config_programas_editar', 'editar');
		
		return $ret;
	}
	
	public function salvar($id = ''){
		$ret = '';
		$confirm = getAppVar('config_programas_editar');
		if($confirm == 'editar'){
			$id = base64_decode(getParam($_GET, 'id', ''));
			$formPrograma = getParam($_POST, 'formPrograma', []);
			
			$campos = [];
			$campos['modulo'] 	= $formPrograma['modulo'];
			$campos['etiqueta'] = $formPrograma['etiqueta'];
			$campos['descricao']= $formPrograma['descricao'];
			$campos['ativo'] 	= $formPrograma['ativo'];
			
			if(empty($id)){
				$campos['seq'] 		= $formPrograma['seq'] ?? 1;
				$campos['programa'] = $formPrograma['programa'];
				
				$sql = montaSQL($campos, 'app002');
				query($sql);
				$ret = $this->editar($this->getIdPrograma($formPrograma['programa']));
			}
			else{
				
				$programa = $this->recuperarPrograma($id);
				$sql = montaSQL($campos, 'app002', 'UPDATE', "programa = '$programa' ");
				query($sql);
				
				$form_permissoes = getParam($_POST, 'fromPermissoes', []);
				$this->salvarPermissoes($form_permissoes, $programa);
				
				$ret = $this->index();
			}
		}else{
			$ret = $this->index();
		}
		//   	putAppVar('config_programas_editar', '');
		redireciona(getLink().'index');
	}
	
	private function montaRelatorio(){
		
		$param = [];
		$param['icone']		= 'fa-window-restore';
		$param['titulo'] 	= $this->_titulo;
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$paramTabela['acoes'] = [];
		
		$param = [];
		$param['texto'] =  'Editar';
		$param['link'] 	= getLink().'editar&id=';
		$param['coluna']= 'id64';
		$param['flag'] 	= '';
		$param['width'] = 30;
		$param['cor'] = 'success';
		$paramTabela['acoes'][] = $param;
		
		//Botão EXCLUIR
		$param = [];
		$param['texto'] 	= 'Excluir';
		$param['link'] 		= getLink().'excluir&id=';
		$param['coluna'] 	= 'id64';
		$param['width'] 	= 30;
		$param['flag'] 		= '';
		//$param['tamanho'] 	= 'pequeno';
		$param['cor'] 		= 'danger';
		$paramTabela['acoes'][] = $param;
		
		$this->_relatorio->setParamTabela( $paramTabela);
		
		$this->_relatorio->addColuna(array('campo' => 'id'			    , 'etiqueta' => 'ID'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'modulo'			, 'etiqueta' => 'Módulo'			, 'tipo' => 'T', 'width' => 110, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'seq'			    , 'etiqueta' => 'Seq'		        , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'programa'		, 'etiqueta' => 'Programa'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'etiqueta'		, 'etiqueta' => 'Etiqueta'			, 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'descricao'		, 'etiqueta' => 'Descrição'			, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ativo'			, 'etiqueta' => 'Ativo'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		
		$p = [];
		$p['onclick'] = "setLocation('".getLink()."editar&id=0')";
		//$p['tamanho'] = 'pequeno';
		$p['cor'] = 'info';
		$p['texto'] = 'Incluir Programa';
		$this->_relatorio->addBotao($p);
		
	}
	
	private function getDados($modulo, $ativo){
		$ret = [];
		$where = ' WHERE 1=1 ';
		
		if(!empty($modulo)){
			$where .= " AND app002.modulo = '$modulo'";
		}
		
		if(!empty($ativo) && $ativo != 'T'){
			$where .= " AND app002.ativo = '$ativo'";
		}
		
		$sql = "select app002.id as id, app001.etiqueta as modulo, app002.seq as seq, app002.programa as programa, app002.etiqueta as etiqueta, app002.descricao as descricao, app002.ativo  from app002 join app001 on (app002.modulo = app001.nome)".$where;
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			$campos = array('id', 'modulo', 'seq', 'programa', 'etiqueta', 'descricao', 'ativo');
			foreach ($rows as $row){
				$temp = [];
				foreach ($campos as $campo){
					$temp[$campo] = $row[$campo];
				}
				$temp['id64'] = base64_encode($temp['id']);
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	
	private function getDadosPrograma($id){
		$ret = [];
		$campos = array('programa', 'modulo', 'etiqueta', 'descricao', 'ativo');
		foreach ($campos as $campo){
			$ret[$campo] = '';
		}
		if($id != 0 && $id != ''){
			$sql = "select programa, modulo, etiqueta, descricao, ativo from app002 where id = $id";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				foreach ($campos as $campo){
					$ret[$campo] = $rows[0][$campo];
				}
			}
		}
		return $ret;
	}
	
	private function getPermissoes($programa){
		$ret = [];
		$sql = "select user from sys001 where 1=1";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row['user']] = false;
			}
			$sql = "select user from sys115 where programa = '$programa'";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$ret[$row['user']] = true;
				}
			}
		}
		return $ret;
	}
	
	private function montaFormUsuarios($programa){
		//separar por tipo de usuário => usar tabela 000015
		//aquele monte de CB com os nome
		$ret = '';
		$lista_tipos_usuarios = $this->getListaTiposUsuarios();
		//print_r($lista_tipos_usuarios);
		$CB = $this->montaArraysCB($programa, 'fromPermissoes');
		foreach ($lista_tipos_usuarios as $tipo => $descricao){
			if(isset($CB[$tipo])){
				$param = [];
				$param['colunas'] 	= 3;
				$param['combos']	= $CB[$tipo];
				$formCombo = formbase01::formGrupoCheckBox($param);
				//print_r($CB);
				$param = [];
				$param['titulo'] = '<label><input type="checkbox"  onclick="marcarTodos(\''.$tipo.'\',this.checked);"  name="formTipo['.$tipo.']" id="' . $descricao . '_id" />&nbsp;&nbsp;'.$descricao.'</label>';
				$param['conteudo'] = $formCombo;
				$ret .= addCard($param).'<br><br>';
			}
		}
		return $ret;
	}
	
	private function getListaTiposUsuarios(){
		$ret = [];
		$sql = "select chave, descricao from sys005 where tabela = '000015' and ativo = 'S'";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row['chave']] = $row['descricao'];
			}
		}
		return $ret;
	}
	
	private function montaArraysCB($programa, $nomeCampo){
		$ret = [];
		$lista_usuarios = $this->getIfoUsuariosCB($programa);
		foreach ($lista_usuarios as $user => $info){
			$temp = [];
			$temp["nome"] 		= $nomeCampo.'['.$user.']';
			$temp["etiqueta"] 	= $info['nome'];
			$temp["checked"] 	= $info['permissao'];
			$temp["modulo"] 	= $info['tipo'];
			$temp["classeadd"] 	= $info['tipo'];
			$ret[$info['tipo']][] = $temp;
		}
		return $ret;
	}
	
	private function getIfoUsuariosCB($programa){
		$ret = [];
		$permissoes = $this->getPermissoes($programa);
		if(is_array($permissoes) && count($permissoes) > 0){
			$sql = "select user, nome, tipo from sys001 where ativo = 'S'";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				foreach($rows as $row){
					$temp = [];
					$temp['nome'] = $row['nome'];
					$temp['tipo'] = $row['tipo'];
					$temp['permissao'] = isset($permissoes[$row['user']]) ? $permissoes[$row['user']] : false;
					$ret[$row['user']] = $temp;
				}
			}
		}
		return $ret;
	}
	
	private function recuperarPrograma($id){
		$ret = '';
		if($id != '' && $id != 0){
			$sql = "select programa from app002 where id = $id ";
			$rows = query($sql);
			if(is_array($rows) && count($rows) == 1){
				$ret = isset($rows[0]['programa']) ? $rows[0]['programa'] : '';
			}
		}
		return $ret;
	}
	
	private function getIdPrograma($programa){
		$ret = 0;
		if(trim($programa) != ''){
			$sql = "select id from app002 where programa = '$programa' ";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				$ret = isset($rows[0]['id']) ? $rows[0]['id'] : 0;
			}
		}
		return $ret;
	}
	
	private function salvarPermissoes($permissoes, $programa){
		//if(is_array($permissoes) && count($permissoes) > 0 && trim($programa) != ''){
		if(is_array($permissoes) && trim($programa) != ''){
			$sql = "DELETE FROM sys115 WHERE programa = '$programa'";
			$res = query($sql);
			if($res !== false){
				if(count($permissoes) > 0){
					foreach ($permissoes as $user => $valor){
						if($valor == 'on'){
							$temp = array(
								'user' => $user,
								'programa' => $programa,
								'perm' => 'S',
							);
							$sql = montaSQL($temp, 'sys115');
							query($sql);
						}
					}
				}
			}else{
				echo "Erro <br>\n";
			}
		}
	}
	
	public function excluir()
	{
		$id = getParam($_GET, 'id', 0);
		if($id !== 0){
			$id = base64_decode($id);
			$sql =  "SELECT app002.programa FROM (sys115 JOIN app002 on (app002.programa = sys115.programa)) where app002.id = $id";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				addPortalMensagem("ERRO!<br>O Programa está em uso!", 'error');
			}else{
				$sql = "UPDATE app002 SET ativo = 'N' WHERE app002.id = $id";
				query($sql);
				addPortalMensagem("Sucesso!<br>O programa foi excluído!");
			}
		}
		return $this->index();
	}
}

function coreGetModulos(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT nome, etiqueta FROM app001 WHERE 1=1 ORDER BY etiqueta";
	$rows = query($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['nome'];
			$temp[1] = $row['etiqueta'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}