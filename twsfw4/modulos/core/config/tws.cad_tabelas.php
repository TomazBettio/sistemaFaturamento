<?php
/*
 * Data Criação: 07/03/22
 * Autor: Thiel
 *
 * Descricao: Manutenção de tabelas auxiliares
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class cad_tabelas{
	var $funcoes_publicas = array(
			'index'			=> true,
			'addTabela'		=> true,
			'novoItem'		=> true,
			'atualizaItens' => true,
	);
	
	//Nome do programa
	private $_programa;
	
	//Titulo
	private $_titulo;
	
	public function __construct(){
		$this->_programa = get_class($this);
		$this->_titulo = 'Tabelas Auxiliares';
	}
	
	public function index(){
		$ret = '';

		$grupo = getParam($_POST, 'tab_grupo');
		$tabela = getParam($_POST, 'tab_tabela');
		
		if(empty($grupo) || empty($tabela)){
			$ret .= $this->montaFormulario($grupo, $tabela);
		}else{
			$ret .= $this->montaPainel($grupo, $tabela);
		}
		
		return $ret;
	}
	
	public function addTabela(){
		$ret = '';
		$operacao = getOperacao();
		
		if(empty($operacao)){
			$ret .= $this->adicionarTabelaForm();
		}else{
			$ok 		= getParam($_POST, 'novoItem');
			$tabela 	= getParam($_POST, 'tabID');
			$descricao 	= getParam($_POST, 'tabDescricao');
			$grupo 		= getParam($_POST, 'tab_grupo');
			
			if($ok == 'ok' && !empty($tabela) && !empty($descricao) && !empty($grupo)){
				$verifica = $this->verificaDuplicado('000000', $tabela);
				if($verifica){
					$campos = [];
					$campos['tabela'] 	= '000000';
					$campos['chave'] 	= $tabela;
					$campos['descricao']= $descricao;
					$campos['grupo'] 	= $grupo;
					$campos['ativo'] 	= 'S';
					
					$sql = montaSQL($campos, 'sys005');
					if(query($sql)){
						addPortalMensagem('Tabela criada com sucesso!');
						$ret .=  $this->montaPainel($grupo, $tabela);
					}else{
						addPortalMensagem('Erro indeterminado ao gravar, por favor tente novamente!', 'error');
						$ret .= $this->adicionarTabelaForm($tabela, $descricao, $grupo);
					}
				}else{
					addPortalMensagem('Já existe esta tabela cadastrada!', 'error');
					$ret .= $this->adicionarTabelaForm($tabela, $descricao, $grupo);
				}
			}else{
				addPortalMensagem('Erro indeterminado, por favor tente novamente!', 'error');
				$ret .= $this->adicionarTabelaForm($tabela, $descricao, $grupo);
			}
		}
		
		
		return $ret;
	}
	
	/**
	 * Adiciona um novo item a tabela existente
	 */
	public function novoItem(){
		$ret = '';
		
		$grupo 	= getAppVar('cad_tabelas_grupo_edit');
		$tabela = getAppVar('cad_tabelas_tabela_edit');
		$form 	= getAppVar('cad_tabelas_form');
		
		if($form === true && !empty($grupo) && !empty($tabela)){
			
			$chave = getParam($_POST, 'itemChave');
			$descricao = getParam($_POST, 'itemDescricao');
			
			if(!empty($chave) && !empty($descricao)){
				if($this->verificaDuplicado($tabela, $chave)){
					$campos = [];
					$campos['tabela'] 	= $tabela;
					$campos['chave'] 	= $chave;
					$campos['descricao']= $descricao;
					$campos['ativo'] 	= 'S';
					
					$sql = montaSQL($campos, 'sys005');
				
					if(query($sql)){
						$chave = '';
						$descricao = '';
						addPortalMensagem('Incluído com sucesso!');
					}else{
						addPortalMensagem('Erro indeterminado, por favor tente novamente!', 'error');
					}
				}else{
					addPortalMensagem('Chave duplicada!', 'error');
				}
			}else{
				addPortalMensagem('Chave e/ou descrição não podem ficar em branco!', 'error');
			}
			
			$ret .= $this->montaPainel($grupo, $tabela, $chave, $descricao);
			
		}else{
			$ret = $this->index();
		}
		
		return $ret;
	}
	
	public function atualizaItens(){
		$ret = '';
		
		$grupo 	= getAppVar('cad_tabelas_grupo_edit');
		$tabela = getAppVar('cad_tabelas_tabela_edit');
		$form 	= getAppVar('cad_tabelas_form');
		
		$descricoes = getParam($_POST, 'descricao');
		$ativos = getParam($_POST, 'ativo');
		
		$descricoes = is_array($descricoes) ? $descricoes : [];
		$ativos = is_array($ativos) ? $ativos : [];
		
		if($form === true && !empty($grupo) && !empty($tabela) && count($descricoes) > 0 && count($descricoes) == count($ativos)){
			$infos = $this->getItens($tabela);
			$alterados = 0;
			foreach ($descricoes as $chave => $desc){
				if($desc != $infos[$chave]['descricao'] || $ativos[$chave] != $infos[$chave]['ativo']){
					$res = $this->atualizaItem($tabela, $chave, $desc, $infos[$chave]['descricao'], $ativos[$chave], $infos[$chave]['ativo']);
					if($res){
						$alterados++;
					}
				}
			}
			if($alterados == 0){
				addPortalMensagem('Nenhum item alterado!','warning');
			}else{
				if($alterados > 1){
					addPortalMensagem($alterados.' itens alterados!');
				}else{
					addPortalMensagem($alterados.' item alterado!');
				}
			}
			
			$ret .= $this->montaPainel($grupo, $tabela);
		}else{
			$ret = $this->index();
		}
		
		
		
		return $ret;
	}
	//-------------------------------------------------------------------------------------- UI --------------------------------------
	
	private function montaPainel($grupo, $tabela, $chaveAdd = '', $descricaoAdd = ''){
		$ret = '';
		$grupoNome = sys005::getGrupoNome($grupo);
		$tabelaNome = sys005::getTabelaNome($tabela);
		
		putAppVar('cad_tabelas_grupo_edit', $grupo);
		putAppVar('cad_tabelas_tabela_edit', $tabela);
		putAppVar('cad_tabelas_form', true);

		$ret .= $this->adicionarItemForm($chaveAdd, $descricaoAdd);
		$ret .= $this->editarItemTabela($tabela);
		
		$param = [];
		$param['titulo'] = 'Tabela Auxiliar - '.$tabela.' - '.$tabelaNome;
		$param['conteudo'] = $ret;
		$param['botaoCancelar'] = true;
		$ret = addCard($param);
		
		return $ret;
	}
	
	private function editarItemTabela($tabela){
		$ret = '';
		$IDform = 'formEditItem';
		
		$param = [];
		$param['scroll'] = false;
		$param['filtro'] = false;
		$param['info']	 = false;
		$param['width']	 = '100%';
		$param['table-striped'] = false;
		$tab = new tabela01($param);
		
		$tab->addColuna(array('campo' => 'chave' 		, 'etiqueta' => 'Chave'				,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'descricao'	, 'etiqueta' => 'Descrição'			,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'ativo'  		, 'etiqueta' => 'Ativo'				,'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));

		$dados = $this->getItensTabela($tabela);
		$tab->setDados($dados);
		
		$ret .= $tab;
		
		$param = [];
		$param['titulo'] = 'Itens';
		$param['conteudo'] = $ret;
		$bt = [];
		$bt['onclick'] = "$('#".$IDform."').submit();";
		$bt['texto'] = 'Gravar';
		$param['botoesTitulo'][] = $bt;
		$ret = addCard($param);
		
		$param = [];
		$param['acao'] = getLink().'atualizaItens';
		$param['nome'] = $IDform;
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function adicionarItemForm($chave, $descricao){
		$ret = '';
		formbase01::setLayout();
		$IDform = 'formAddItem';

		$param = [];
		$param['nome'] 			= 'itemChave';
		$param['valor']			= $chave;
		$param['etiqueta'] 		= 'Chave';
		$param['obrigatorio']	= true;
		$formID = formbase01::formTexto($param);
		
		$param = [];
		$param['nome'] 			= 'itemDescricao';
		$param['valor']			= $descricao;
		$param['etiqueta'] 		= 'Descrição';
		$param['obrigatorio']	= true;
		$formDesc = formbase01::formTexto($param);
		
		$param = [];
		$param['tamanhos'] = [4,8];
		$param['conteudos'][] = $formID;
		$param['conteudos'][] = $formDesc;
		$ret = addLinha($param);
		
		$param = [];
		$param['titulo'] = 'Adicionar Item';
		$param['conteudo'] = $ret;
		$bt = [];
		$bt['onclick'] = "$('#".$IDform."').submit();";
		$bt['texto'] = 'Adicionar';
		$param['botoesTitulo'][] = $bt;
		$param['collapse'] = true;
		$ret = addCard($param);
		
		$param = [];
		$param['acao'] = getLink().'novoItem';
		$param['nome'] = $IDform;
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function adicionarTabelaForm($id = '', $descricao = '', $grupo = ''){
		$ret = '';
		$IDform = 'addTabelaForm';
		formbase01::setLayout();
		
		$param = [];
		$param['nome'] = 'tabID';
		$param['valor'] = $id;
		$param['etiqueta'] = 'ID Tabela';
		$param['obrigatorio']	= true;
		$formID = formbase01::formTexto($param);
		
		$param = [];
		$param['nome'] = 'tabDescricao';
		$param['valor'] = $descricao;
		$param['etiqueta'] = 'Descrição Tabela';
		$param['obrigatorio']	= true;
		$formDesc = formbase01::formTexto($param);
		
		$param = [];
		$param['nome'] = 'tab_grupo';
		$param['valor'] = $grupo;
		$param['etiqueta'] = 'Grupo';
		$param['title'] = 'Selecione o grupo';
		$param['lista'] = sys005::getGrupos(true);
		$param['obrigatorio']	= true;
		$formGrupo = formbase01::formSelectProcura($param);
		
		$param = [];
		$param['tamanhos'] = [3,6,3];
		$param['conteudos'][] = $formID;
		$param['conteudos'][] = $formDesc;
		$param['conteudos'][] = $formGrupo;
		$ret = addLinha($param);
		
		$param = [];
		$param['nome'] = 'novoItem';
		$param['valor'] = 'ok';
		$ret .= formbase01::formHidden($param);
		
		$param = [];
		$param['acao'] = getLink().'addTabela.gravar';
		$param['nome'] = $IDform;
		$ret = formbase01::form($param, $ret);
		
		$param = [];
		$param['titulo'] = 'Tabela Auxiliar - Adicionar Tabela';
		$param['conteudo'] = $ret;
		$param['botaoCancelar'] = true;
		$bt = [];
		$bt['onclick'] = "$('#".$IDform."').submit();";
		$bt['texto'] = 'Gravar';
		$param['botoesTitulo'][] = $bt;
		$ret = addCard($param);
		
		return $ret;
	}
	
	private function montaFormulario($grupo, $tabela) {
		$ret = '';
		
		$param = [];
		$param['nome'] = 'tab_grupo';
		$param['valor'] = $grupo;
		$param['etiqueta'] = 'Grupo';
		$param['title'] = 'Selecione o grupo';
		$param['lista'] = sys005::getGrupos(true);
		$param['onchange'] = "$('#formTabelas').submit()";
		$param['procura'] = true;
		$formGrupo = formbase01::formSelect($param);
		
		$param = [];
		$param['nome'] = 'tab_tabela';
		$param['valor'] = $tabela;
		$param['etiqueta'] = 'Tabela';
		$param['title'] = 'Selecione a tabela';
		if(empty($grupo)){
			$param['readonly'] = true;
			$param['title'] = 'Selecione primeiro um grupo';
		}else{
			$param['lista'] = sys005::getTabelasGrupo($grupo);
		}
		$param['onchange'] = "$('#formTabelas').submit()";
		$param['procura'] = true;
		$formPrograma = formbase01::formSelect($param);
		
		$param = [];
		$param['tamanhos'] = [5,2,5];
		$param['conteudos'][] = $formGrupo;
		$param['conteudos'][] = '';
		$param['conteudos'][] = $formPrograma;
		$ret = addLinha($param);
		
		$param = [];
		$param['acao'] = getLink().'index';
		$param['nome'] = 'formTabelas';
		$form = formbase01::form($param, $ret);
		
		$temp = [];
		$temp['onclick'] 	= "setLocation('".getLink()."addTabela')";
		$temp['cor'] 		= COR_PADRAO_BOTOES;
		$temp['texto'] 		= 'Incluir Tabela';
		
		$param = [];
		$param['titulo'] = $this->_titulo;
		$param['conteudo'] = $form;
		$param['botoesTitulo'][] = $temp;
		$ret = addCard($param);
		
		putAppVar('cad_tabelas_grupo_edit', '');
		putAppVar('cad_tabelas_tabela_edit', '');
		putAppVar('cad_tabelas_form', '');
		
		return $ret;
	}
	
	//-------------------------------------------------------------------------------------- GET -------------------------------------
	
	private function getItensTabela($tabela){
		$ret = [];
		
		$sql = "SELECT * FROM sys005 WHERE tabela = '$tabela' ORDER BY descricao";
		$rows = query($sql);
	
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$param = [];
				$param['nome'] = 'chave['.$row['chave'].']';
				$param['valor'] = $row['chave'];
				$temp['chave'] 		= formbase01::formTexto($param, false);
				
				$param = [];
				$param['nome'] = 'descricao['.$row['chave'].']';
				$param['valor'] = $row['descricao'];
				$temp['descricao'] 	= formbase01::formTexto($param);
				
				$param = [];
				$param['nome'] = 'ativo['.$row['chave'].']';
				$param['valor'] = $row['ativo'];
				$param['lista'] = tabela('000003',['branco' => false]);
				$param['lista'] = tabela('000003');
				$temp['ativo'] 		= formbase01::formSelect($param);
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function getItens($tabela){
		$ret = [];
		
		$sql = "SELECT * FROM sys005 WHERE tabela = '$tabela' ORDER BY chave";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row['chave']]['descricao'] = $row['descricao'];
				$ret[$row['chave']]['ativo'] = $row['ativo'];
			}
		}
		
		return $ret;
	}
	//-------------------------------------------------------------------------------------- SET -------------------------------------
	
	//-------------------------------------------------------------------------------------- ADD -------------------------------------
	
	//-------------------------------------------------------------------------------------- VO  -------------------------------------
	
	private function atualizaItem($tabela, $chave, $descPara, $descDe, $ativoPara, $ativoDe){
		$ret = true;
		$campos = [];
		$campos['descricao'] = $descPara;
		$campos['ativo'] = $ativoPara;
		
		$sql = montaSQL($campos, 'sys005', 'UPDATE', "tabela = '$tabela' AND chave = '$chave' ");
		if(query($sql)){
			$texto = "Descricao: $descDe | $descPara";
			log::gravaLog('manutencao_sys005', $texto);
			$texto = "Ativo: $ativoDe | $ativoPara";
			log::gravaLog('manutencao_sys005', $texto);
		}else{
			addPortalMensagem('Erro ao atualizar a chave '.$chave.'!','error');
			$ret = false;
		}
		
		return $ret;
	}
	
	//-------------------------------------------------------------------------------------- UTEIS -----------------------------------
	
	private function verificaDuplicado($tabela, $chave){
		$ret = true;
		
		$sql = "SELECT ativo FROM sys005 WHERE tabela = '$tabela' AND chave = '$chave'";
		$rows = query($sql);
		
		if(isset($rows[0]['ativo'])){
			$ret = false;
		}
		
		return $ret;
	}
}