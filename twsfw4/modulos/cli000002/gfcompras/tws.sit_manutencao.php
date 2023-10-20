<?php
/*
 * Data Cria��o: 24/10/17 - 22:05
 * Autor: Alexandre Thiel
 *
 *
 * Alterções:
 *           02/01/2019 - Emanuel - Migração para intranet2
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class sit_manutencao{
	var $funcoes_publicas = array(
		'index' 			=> true,
		'incluir' 			=> true,
		'excluir' 			=> true,
		'editar' 			=> true,
		'salvar' 			=> true,
		'teste' 			=> true,
	);
	
	function __construct(){
		
	}
	
	function index(){
		$ret = '';
		
		$ret .= $this->browser();

		return $ret;
	}
	
	function incluir(){
		$ret = $this->cadastro();
		putAppVar('manut_sit_gravar', 0);
		return $ret;
	}
	
	function editar(){
		$id = isset($_GET['sit']) ? $_GET['sit'] : 0;
		if($id > 0){
			$ret = $this->cadastro($id);
			putAppVar('manut_sit_gravar', $id);
		}else{
			redireciona('index.php?menu=gfcompras.sit_manutencao.index');
		}
		
		return $ret;
	}

	function teste(){
		$id = isset($_GET['sit']) ? $_GET['sit'] : 0;
		if($id > 0){
			$param = [];
			$param['id'] = $id;
			$param['interno'] = true;
			$param['externo'] = false;
			ExecMethod('gfcompras.sit.schedule',$param);
			addPortalMensagem('Sucesso!', "Teste enviado para os emails internos.");
		}
		$ret = $this->index();
		
		return $ret;
	}
	function excluir(){
		$id = isset($_GET['id']) ? $_GET['id'] : 0;
		if($id > 0){
			$this->sitExcluir($id);
		}
		//redireciona('index.php?menu=gfcompras.sit_manutencao.index');
		$ret = $this->index();
		return $ret;
	}
	
	function salvar(){
//print_r($_POST);
		$id = isset($_POST['formSIT']['id']) ? $_POST['formSIT']['id'] : 0;
		$idLocal = getAppVar('manut_sit_gravar');
//echo "$id == $idLocal \n";
		if($id == $idLocal){
			$dados = $_POST['formSIT'];
			$dados['config']['MARGEM_OL'] = isset($dados['config']['MARGEM_OL']) ?  str_replace(',', '.', $dados['config']['MARGEM_OL']): 0;
			if($id == 0){
				$this->sitInclui($dados);
			}else{
				$this->sitAltera($id, $dados);
			}
//print_r($_POST);
			
		}
		//redireciona('index.php?menu=gfcompras.sit_manutencao.index');
		$ret = $this->index();
		unsetAppVar('manut_sit_gravar');
		return $ret;
	}
	
	private function cadastro($id = 0){
		global $nl;
		$param = [];
		if($id == 0){
			$titulo = "Novo Relatorio SIT";
			$dados	= $this->geraMatriz();
			$dados['id'] = 0;
			$dados['ativo'] = 'S';
		}else{
			$param['id'] = $id;
			$dados =  $this->SITseleciona($param);
			$dados = $dados[0];
			$titulo = 'Editando '.$dados['id'].' - '.$dados['nome'];
		}

		$param = [];
                
        $botao1 = [];
        $botao1['onclick'] = "setLocation('index.php?menu=gfcompras.sit_manutencao.index')";
        $botao1['tamanho'] = 'pequeno';
        $botao1['cor'] = 'danger';
        $botao1['texto'] = 'Cancelar';
        $param['botoesTitulo'][] = $botao1;
        
        $botao2 = [];
        $botao2['onclick'] = "$('#formSit').submit()";
        $botao2['tamanho'] = 'pequeno';
        $botao2['cor'] = 'success';
        $botao2['texto'] = 'Salvar';
        $param['botoesTitulo'][] = $botao2;
        
        $param['titulo'] = 'Geral';
        $param['conteudo'] = $this->getFormCad1($dados);
		$cont1 = addCard($param);
		
		$param = [];
		$param['titulo'] = 'Filtros';
		$param['conteudo'] = $this->getFormCad2($dados);
		$cont2 = addCard($param);

		$param['titulo'] = 'Campos Tipo Estoque';
		$param['conteudo'] = $this->getFormCad4($dados);
		$cont3 = addCard($param);
		
		$param['titulo'] = 'Campos Tipo Faturamento';
		$param['conteudo'] = $this->getFormCad5($dados);
		$cont4 = addCard($param);
		
		$param['titulo'] = 'Configurações Gerais';
		$param['conteudo'] = $this->getFormCad6($dados);
		$cont6 = addCard($param);
		
		$param['titulo'] = 'Email';
		$param['conteudo'] = $this->getFormCad3($dados);
		$cont5 = addCard($param);

		$ret = '';
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-2"></div>'.$nl;
		$ret .= '	<div  class="col-md-8">'.$cont1.$cont2.$cont3.$cont4.$cont6.$cont5.'</div>'.$nl;
		$ret .= '	<div  class="col-md-2"></div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = [];
		$param['acao'] = getLink().'salvar';
		$param['nome'] = 'formSit';
		$ret = formbase01::form($param, $ret);
		
		$param = [];
		$param['titulo'] = $titulo;
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	function getFormCad1($dados){
		$form = new form01();
		$sn = tabela("000003","desc");
		$tipo = $this->getTipoSit();
		$campoZerado = $this->getCamposZerados();
		if($dados['id'] == 0){
			//Possivel alterar
			$alt = "T";
		}else{
			//Somente mostra
			$alt = "I";
		}
		
		//$tabela		= tabela("000003","desc");
		$etiqueta1 = "Indica qual campo deve estar zerado para n&atilde;o levar em conta a linha";
		$etiqueta2 = "Indica qual campo deve ser utilizado para agrupar as informações";
		
		$form->addHidden("formSIT[id]", $dados['id']);
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[nome]'		, 'etiqueta' => 'Nome'			, 'tipo' => 'T' , 'tamanho' => '50', 'linhas' => '', 'valor' => $dados['nome']		, 'opcao' => ''		,'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[tipo]'		, 'etiqueta' => 'Tipo'			, 'tipo' => 'A' , 'tamanho' => '1' , 'linhas' => '', 'valor' => $dados['tipo']		, 'opcao' => ''		,'lista' => $tipo		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[zerado]'		, 'etiqueta' => $etiqueta1		, 'tipo' => 'A' , 'tamanho' => '1' , 'linhas' => '', 'valor' => $dados['zerado']	, 'opcao' => ''		,'lista' => $campoZerado, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[agrupa]'		, 'etiqueta' => $etiqueta2		, 'tipo' => 'A' , 'tamanho' => '1' , 'linhas' => '', 'valor' => $dados['agrupa']	, 'opcao' => ''		,'lista' => $campoZerado, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[ativo]'		, 'etiqueta' => 'Ativo'			, 'tipo' => 'A' , 'tamanho' => '1' , 'linhas' => '', 'valor' => $dados['ativo']		, 'opcao' => ''		,'lista' => $sn			, 'validacao' => '', 'obrigatorio' => false));
		return $form;
	}
	
	function getFormCad2($dados){
		$form = new form01();
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[fornecedor]'	, 'etiqueta' => 'Fornecedor'	, 'tipo' => 'T' , 'tamanho' => '50', 'linhas' => '', 'valor' => $dados['fornecedor'], 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[marca]'		, 'etiqueta' => 'Marca'			, 'tipo' => 'T' , 'tamanho' => '50', 'linhas' => '', 'valor' => $dados['marca']		, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[rede]'		, 'etiqueta' => 'Rede'			, 'tipo' => 'T' , 'tamanho' => '50', 'linhas' => '', 'valor' => $dados['rede']		, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[origem]'		, 'etiqueta' => 'Origem'		, 'tipo' => 'T' , 'tamanho' => '50', 'linhas' => '', 'valor' => $dados['origem']	, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[uf]'			, 'etiqueta' => 'Uf'			, 'tipo' => 'T' , 'tamanho' => '50', 'linhas' => '', 'valor' => $dados['uf']		, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[ramo]'		, 'etiqueta' => 'Ramo Atividade', 'tipo' => 'T' , 'tamanho' => '50', 'linhas' => '', 'valor' => $dados['ramo']		, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		
		return $form;
	}
	
	function getFormCad3($dados){
		$periodo = $this->getPeriodoSit();
		
		$form = new form01();
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[periodo]'		, 'etiqueta' => 'Periodo'		, 'tipo' => 'A' , 'tamanho' => '50' , 'linhas' => '', 'valor' => $dados['periodo']		, 'opcao' => ''		,'lista' => $periodo, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[dia]'			, 'etiqueta' => 'Dia Especifico', 'tipo' => 'T' , 'tamanho' => '2'  , 'linhas' => '', 'valor' => $dados['dia']			, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[emailinterno]', 'etiqueta' => 'Email Interno'	, 'tipo' => 'T' , 'tamanho' => '200', 'linhas' => '', 'valor' => $dados['emailinterno']	, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[emailexterno]', 'etiqueta' => 'Email Externo'	, 'tipo' => 'T' , 'tamanho' => '200', 'linhas' => '', 'valor' => $dados['emailexterno']	, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formSIT[email_texto]' , 'etiqueta' => 'Texto Inicio'	, 'tipo' => 'ED', 'tamanho' => '200', 'linhas' => '', 'valor' => $dados['email_texto']	, 'opcao' => ''		,'lista' => ''		, 'validacao' => '', 'obrigatorio' => false));
		return $form;
	}
	
	/*
	 * Campos de Estoque
	 */
	private function getFormCad4($dados){
		$ret = $this->getFormCampos('E', $dados);

		return $ret;
	}
	
	/*
	 * Campos de Faturamento
	 */
	private function getFormCad5($dados){
		$ret = $this->getFormCampos('F', $dados);
		
		return $ret;
	}
	
	private function getFormCad6($dados){
		$ret = '';
		$campos = [];
		$sql = "SELECT * FROM gf_sit_config WHERE ativo = 'S'";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			$valores = unserialize($dados['config']);
			foreach ($rows as $row){
				$temp = [];
				$temp['campo'] 		= $row['campo'];
				$temp['pergunta'] 	= $row['pergunta'];
				$temp['mascara'] 	= $row['mascara'];
				$temp['tamanho'] 	= empty($row['tamanho']) ? 10 : $row['tamanho'];
				$temp['padrao'] 	= $row['padrao'];
				$temp['valor']		= isset($valores[$row['campo']]) ? $valores[$row['campo']] : $row['padrao'];
				$temp['help'] 		= $row['help'];
				
				$campos[] = $temp;
			}
		}
//print_r($campos);		
		if(count($campos) > 0){
			$form = new form01();
			//$form->setTipoForm(10);
			foreach ($campos as $campo){
				$form->addCampo(array('id' => $campo['campo'], 'campo' => 'formSIT[config]['.$campo['campo'].']'	, 'etiqueta' => $campo['pergunta'], 'tipo' => 'T', 'mascara' => $campo['mascara'] , 'tamanho' => $campo['tamanho'], 'linhas' => '', 'valor' => $campo['valor']	, 'opcao' => ''	,'lista' => ''	, 'validacao' => '', 'obrigatorio' => false, 'help' => $campo['help'], 'largura' => 6));
			}
			$ret .= $form;
		}
		

		
		return $ret;
	}
	
	private function getFormCampos($tipo, $dados){
		//Busca os possíveis campos 
		$campos = $this->getCamposSit($tipo);
		//Verifica os campos marcados e sequencia no relatório SIT
		$camposMarcados = $this->getCamposMarcados($tipo, $dados['tipo'], $dados['campos']);
		$form = new form01();
		//$form->setTipoForm(10);
		
		foreach ($campos as $campo){
			$valor = isset($camposMarcados[$campo['campo']]) ? $camposMarcados[$campo['campo']] : '';
			if($campo['etiquetaManut'] != ''){
				$et = $campo['etiquetaManut'];
			}else{
				$et = $campo['etiqueta'];
			}
			$form->addCampo(array('id' => '', 'campo' => 'formSIT['.$tipo.']['.$campo['campo'].']'	, 'etiqueta' => $et, 'tipo' => 'T' , 'tamanho' => '3', 'linhas' => '', 'valor' => $valor	, 'opcao' => ''	,'lista' => ''	, 'validacao' => '', 'obrigatorio' => false, 'largura' => 4));
		}
		return $form;
	}
	
	function browser(){
		//global $nl, $app, $config;
		
		$param = [];
		$param['id'] 		= "";
		//$param['campos']	= "id,nome,tipo,fornecedor,marca,rede,origem,uf,campos,periodo,emailInterno,emailExterno,ativo ";
		$param['campos']	= "id,nome,tipo,periodo,dia,ativo ";
		$param['filtro']	= " del = ' '";
		$param['ordem']		= "nome";
		
		$dados =  $this->sitSeleciona($param,'browse');
		
		$param = [];
		$param['paginacao'] = false;
		$param['titulo'] = 'Manutenção Relatorios SIT';
		$param['width'] = '100%';
		$browse = new tabela01($param);
		$browse->setDados($dados);
		//$browse->setImpLinha(1); //descomentar
		
		$browse->addColuna(array('campo' => 'id'			, 'etiqueta' => '#ID'			, 'width' => 80, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'nome'			, 'etiqueta' => 'Nome'			, 'width' => 300, 'posicao' => 'E'));
		$browse->addColuna(array('campo' => 'tipo'			, 'etiqueta' => 'Tipo'			, 'width' => 80, 'posicao' => 'C'));
		//$browse->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor'	, 'width' => 100, 'class' => 'centro'));
		//$browse->addColuna(array('campo' => 'marca'			, 'etiqueta' => 'Marca'			, 'width' => 100, 'class' => 'centro'));
		//$browse->addColuna(array('campo' => 'rede'			, 'etiqueta' => 'Rede'			, 'width' => 100, 'class' => 'centro'));
		//$browse->addColuna(array('campo' => 'origem'		, 'etiqueta' => 'Origem'		, 'width' => 100, 'class' => 'centro'));
		//$browse->addColuna(array('campo' => 'uf'			, 'etiqueta' => 'Uf'			, 'width' => 100, 'class' => 'centro'));
		//$browse->addColuna(array('campo' => 'campos'		, 'etiqueta' => 'Campos'		, 'width' => 100, 'class' => 'centro'));
		$browse->addColuna(array('campo' => 'periodo'		, 'etiqueta' => 'Periodo'		, 'width' => 150, 'posicao' => 'E'));
		//$browse->addColuna(array('campo' => 'dia'			, 'etiqueta' => 'Dia'			, 'width' =>  80, 'posicao' => 'centro'));
		//$browse->addColuna(array('campo' => 'emailinterno'	, 'etiqueta' => 'Emailinterno'	, 'width' => 100, 'class' => 'centro'));
		//$browse->addColuna(array('campo' => 'emailexterno'	, 'etiqueta' => 'Emailexterno'	, 'width' => 100, 'class' => 'centro'));
		$browse->addColuna(array('campo' => 'ativo'			, 'etiqueta' => 'Ativo'			, 'width' => 100, 'posicao' => 'C'));
		
		//TODO Verificar se tem permiss�o para cadastrar
		$botao["onclick"]= "setLocation('index.php?menu=gfcompras.sit_manutencao.incluir')";
		$botao["texto"]	= "Incluir";
		//$browse->setAddBotaoTitulo($botao); //antigo
		$browse->addBotaoTitulo($botao);
		
		// Adiciona acao editar
		$param["texto"] = "Editar";
		$param["link"] 	= "index.php?menu=gfcompras.sit_manutencao.editar&sit=";
		$param["coluna"]= 'id';
		$param["flag"] 	= "";
		$param["width"] = 30;
		$browse->addAcao($param);
		
		// Adiciona acao testar (envia para email interno)	
		$param["texto"] = "Testar";
		$param["link"] 	= "index.php?menu=gfcompras.sit_manutencao.teste&sit=";
		$param["coluna"]= 'id';
		$param["flag"] 	= "";
		$param["width"] = 30;
		$param["cor"] 	= 'warning';
		$browse->addAcao($param);
		
		$this->jsConfirmaExclusao('"Confirma a exclusão? \nSub Campanha: "+desc');
		// Adiciona acao excluir
		$param["texto"] = "Excluir";
		$param["link"] 	= "javascript:confirmaExclusao('index.php?menu=gfcompras.sit_manutencao.excluir&id=','{ID}',{COLUNA:nome})";
		$param["coluna"]= 'id';
		$param["flag"] 	= '';
		$param["cor"] 	= 'danger';
		$param["width"] = 30;
		$browse->addAcao($param);
		
		return $browse;
	}
	
	function jsConfirmaExclusao($titulo){
	    addPortaljavaScript('function confirmaExclusao(link,id,desc){');
	    addPortaljavaScript('	if (confirm('.$titulo.')){');
	    addPortaljavaScript('		setLocation(link+id);');
	    addPortaljavaScript('	}');
	    addPortaljavaScript('}');
	}
	
	
	//------------------------------------------------------------------------------------------------ VO -------------------------------------------------
	function sitSeleciona($param = [], $tipo = ''){
		$periodos = $this->getPeriodoSit('array');
		$where = '';
		
		if(!isset($param['campos']) || $param['campos'] == ""){
			$param['campos'] = "*";
		}
		
		$id = $param['id'];
		
		if(is_array($id)){
			$ids = implode(",", $id);
			$where .= " id IN ($ids) ";
		}elseif($id != ""){
			$where .= " id = $id ";
		}
		if(isset($param['filtro']) && $param['filtro'] != ""){
			if($where != "")
				$where .= " AND ";
				$where .= $param['filtro'];
		}
		
		$sql = "SELECT ".$param['campos']." FROM gf_sit ";
		if($where != ""){
			$sql .= " WHERE ".$where;
		}
		if(isset($param['ordem']) && $param['ordem'] != ""){
			$sql .= " ORDER BY ".$param['ordem'];
		}	
//echo "SQL: $sql \n";
		$rows = query($sql);
		$ret = [];
		if(count($rows) > 0){
			$i = 0;
			foreach ($rows as $row){
				$ret[$i]['id'] = $row['id'];
				$ret[$i]['nome'] = utf8_encode($row['nome']);
				$ret[$i]['tipo'] = $row['tipo'];
				$ret[$i]['zerado'] = isset($row['zerado']) ? $row['zerado'] : '';
				$ret[$i]['agrupa'] = isset($row['agrupa']) ? $row['agrupa'] : '';
				if(isset($row['fornecedor'])){
					$ret[$i]['fornecedor'] = $row['fornecedor'];
				}
				if(isset($row['marca'])){
					$ret[$i]['marca'] = $row['marca'];
				}
				if(isset($row['rede'])){
					$ret[$i]['rede'] = $row['rede'];
				}
				if(isset($row['origem'])){
					$ret[$i]['origem'] = $row['origem'];
				}
				if(isset($row['uf'])){
					$ret[$i]['uf'] = $row['uf'];
				}
				if(isset($row['ramo'])){
					$ret[$i]['ramo'] = $row['ramo'];
				}
				if(isset($row['campos'])){
					$ret[$i]['campos'] = $row['campos'];
				}
				if(isset($row['config'])){
					$ret[$i]['config'] = $row['config'];
				}
				if(isset($row['periodo'])){
					$ret[$i]['periodo'] = $tipo == 'browse' ? $periodos[$row['periodo']] : $row['periodo'];
				}
				if(isset($row['dia'])){
					$ret[$i]['dia'] = $row['dia'] == 0 ? '' : $row['dia'];
				}
				if(isset($row['emailInterno'])){
					$ret[$i]['emailinterno'] = $row['emailInterno'];
				}
				if(isset($row['emailExterno'])){
					$ret[$i]['emailexterno'] = $row['emailExterno'];
				}
				$ret[$i]['email_texto'] = $row['email_texto'] ?? '';
				$ret[$i]['ativo'] = $row['ativo'];
				$i++;
			}
		}
		
		return $ret;
	}
	
	function sitInclui($dados){
		$tipo = $dados['tipo'];
		if($dados['periodo'] != 'E'){
			$dados['dia'] = 0;
		}
		$campos = $this->getCamposForm($dados[$tipo]);
		
		//Configurações
		$config = serialize($dados['config']);
		
		$camp = [];
		$camp['nome'] 			= $dados['nome'];
		$camp['tipo'] 			= $tipo;
		$camp['fornecedor'] 	= $dados['fornecedor'];
		$camp['marca'] 			= $dados['marca'];
		$camp['rede'] 			= $dados['rede'];
		$camp['origem'] 		= $dados['origem'];
		$camp['uf'] 			= $dados['uf'];
		$camp['ramo'] 			= $dados['ramo'];
		$camp['campos'] 		= $campos;
		$camp['config'] 		= $config;
		$camp['periodo'] 		= $dados['periodo'];
		$camp['dia'] 			= $dados['dia'];
		$camp['emailInterno'] 	= $dados['emailinterno'];
		$camp['emailExterno'] 	= $dados['emailexterno'];
		$camp['zerado']			= $dados['zerado'];
		$camp['agrupa'] 		= $dados['agrupa'];
		$camp['ativo'] 			= $dados['ativo'];
		$camp['email_texto']	= $dados['email_texto'];
		

		$sql = montaSQL($camp, 'gf_sit');
//echo "SQL Inclusao: $sql \n";
		query($sql);

		return;
	}
	
	function sitAltera($id, $dados){
		$tipo = $dados['tipo'];
		$campos = $this->getCamposForm($dados[$tipo]);
		if($dados['periodo'] != 'E'){
			$dados['dia'] = 0;
		}
		
		//Configurações
		$config = serialize($dados['config']);

		$camp = [];
		$camp['nome'] 			= $dados['nome'];
		$camp['tipo'] 			= $tipo;
		$camp['fornecedor'] 	= $dados['fornecedor'];
		$camp['marca'] 			= $dados['marca'];
		$camp['rede'] 			= $dados['rede'];
		$camp['origem'] 		= $dados['origem'];
		$camp['uf'] 			= $dados['uf'];
		$camp['ramo'] 			= $dados['ramo'];
		$camp['campos'] 		= $campos;
		$camp['config'] 		= $config;
		$camp['periodo'] 		= $dados['periodo'];
		$camp['dia'] 			= $dados['dia'];
		$camp['emailInterno'] 	= $dados['emailinterno'];
		$camp['emailExterno'] 	= $dados['emailexterno'];
		$camp['zerado']			= $dados['zerado'];
		$camp['agrupa'] 		= $dados['agrupa'];
		$camp['ativo'] 			= $dados['ativo'];
		$camp['email_texto']	= $dados['email_texto'];
		
		
		$sql = montaSQL($camp, 'gf_sit', 'UPDATE',"id = $id");
//echo "SQL Alteração: $sql \n";die();
		query($sql);
		
		return "";
	}

	private function sitExcluir($id){
		$sql = "UPDATE gf_sit SET del = '*' WHERE id = $id";
		query($sql);
		
		return;
	}
	
	private function geraMatriz(){
		$ret = [];
		$campos = explode(',', 'id,nome,tipo,fornecedor,marca,rede,origem,uf,campos,periodo,dia,emailinterno,emailexterno,ativo');
		foreach ($campos as $c){
			$ret[$c] = '';
		}
		
		return $ret;
	}
	
	private function getTipoSit(){
		$ret = [];
		
		$ret[0][0] = 'E';
		$ret[0][1] = 'Estoque';
		
		$ret[1][0] = 'F';
		$ret[1][1] = 'Faturamento';
		
		return $ret;
	}
	
	private function getCamposZerados(){
		$ret = [];
		$temp_F = [];
		$temp_E = [];
		$sql = "SELECT * FROM gf_sit_campos WHERE ativo = 'S' ORDER BY etiqueta";
		$rows = query($sql);
		
		if(count($rows) >0 ){
			foreach ($rows as $row){
				$i_f = count($temp_F);
				$i_e = count($temp_E);
				switch ($row['grupo']) {
					case 'F':
						$temp_F[$i_f][0] = $row['campo'];
						$temp_F[$i_f][1] = 'Faturamento - '.$row['etiqueta'];
						break;
					case 'E':
						$temp_E[$i_e][0] = $row['campo'];
						$temp_E[$i_e][1] = 'Estoque - '.$row['etiqueta'];
						break;
					case 'FE':
						$temp_F[$i_f][0] = $row['campo'];
						$temp_F[$i_f][1] = 'Faturamento - '.$row['etiqueta'];
						$temp_E[$i_e][0] = $row['campo'];
						$temp_E[$i_e][1] = 'Estoque - '.$row['etiqueta'];
						break;
				}
			}
		}
		
		$ret[0][0] = '';
		$ret[0][1] = '';
		
		$ret = array_merge($ret, $temp_E, $temp_F);
		
		return $ret;
	}
	
	private function getPeriodoSit($tipo = 'lista'){
		$ret = [];
		$i = 0;
		
		$ret[$i][0] = 'N';
		$ret[$i][1] = 'Nao envia';
		
		$i++;
		$ret[$i][0] = 'S';
		$ret[$i][1] = 'Semanal - Segunda';
		
		$i++;
		$ret[$i][0] = '2S';
		$ret[$i][1] = 'Semanal - Seg/Qui';
		
		$i++;
		$ret[$i][0] = '6';
		$ret[$i][1] = 'Semanal - Sexta';
		
		$i++;
		$ret[$i][0] = 'Q';
		$ret[$i][1] = 'Quinzenal - Inicio e meio do mes';
		
		$i++;
		$ret[$i][0] = 'M';
		$ret[$i][1] = 'Mensal - Inicio do mes';
		
		$i++;
		$ret[$i][0] = 'E';
		$ret[$i][1] = 'Dia Especifico';
		
		if($tipo == 'array'){
			$temp = [];
			foreach ($ret as $r){
				$temp[$r[0]] = $r[1];
			}
			$ret = $temp;
		}
		
		return $ret;
	}
	
	private function getCamposSit($tipo){
		$ret = [];
		
		$sql = "SELECT * FROM gf_sit_campos WHERE grupo LIKE '%$tipo%'";
		$rows = query($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['campo'] 		= $row['campo'];
				$temp['etiqueta']	= utf8_encode($row['etiqueta']);
				$temp['etiquetaManut']	= utf8_encode($row['etiq_manut']);
				
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	/*
	 * Retorna os campos marcados e a sequencia
	 */
	private function getCamposMarcados($tipo, $sitTipo, $sitCampos){
		$ret = [];
		if($tipo == $sitTipo){
			if($sitCampos != ''){
				$campos = explode(',', $sitCampos);
				foreach ($campos as $i => $campo){
					$ret[$campo] = $i + 1;
				}
			}
		}
		
		return $ret;
	}
	
	private function getCamposForm($campos){
		$ret = '';
		$temp = [];
		$tempNN = [];
		
		foreach ($campos as $campo => $ordem){
			if($ordem != ''){
				if($ordem > 0){
					if(isset($temp[$ordem])){
						$temp[$ordem] .= ','.$campo;
					}else{
						$temp[$ordem] = $campo;
					}
				}else{
					if(isset($tempNN[$ordem])){
						$tempNN[$ordem] .= ','.$campo;
					}else{
						$tempNN[$ordem] = $campo;
					}
				}
			}
		}
		ksort($temp);
		ksort($tempNN);

		$ret = implode(',', $temp);
		if(count($tempNN) > 0){
			if($ret != ''){
				$ret .= ',';
			}
			$ret .= implode(',', $tempNN);
		}
		
		return $ret;
	}
	
	/*
	 * Grava log SIT
	 */
	private function gravaLogSIT($id, $acao, $complemento = ''){
		$dia = date('Ymd');
		$hora = date('h:i');
		$usuario = getUser();
		$sql = "INSERT INTO gf_sit_log (dia,hora,usuario,relatorio,acao,complemento) VALUES ('$dia', '$hora', '$usuario', $id, '$acao', '$complemento')";
		query($sql);
	}
}