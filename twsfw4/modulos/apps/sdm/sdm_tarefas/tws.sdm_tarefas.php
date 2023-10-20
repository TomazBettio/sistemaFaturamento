<?php
/*
 * Data Criação: 18/08/2020
 * Autor: bcs
 */
if (! defined('TWSiNet') || ! TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);


class sdm_tarefas{
	var $funcoes_publicas = array(
			'index'			=> true,
			'editar'        => true,
			'salvar'        => true,
			'excluir'       => true,
			'faturamento'   => true,
			'ajax'          => true,
			'gravaOS'		=> true,
	);
	
	//Titulo
	private $_titulo;
	
	//Programa
	private $_programa;
	
	public function __construct(){
		$this->_titulo ='Tarefas';
		$this->_programa = get_class($this);
		
		if(false){
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI'	, 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''								, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM'	, 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''								, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente'	, 'variavel' => 'CLIENTE'	, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'funcoes_cad::getListaClientes()', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Recurso'	, 'variavel' => 'RECURSO'	, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'funcoes_cad::getListaRecursos()', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '5', 'pergunta' => 'Faturada', 'variavel' => 'FATURADA'	, 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''								, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ' = ;S=Sim;N=Não;A=Ambas']);
		}
	}
	
	public function index(){
		$param = [];
		$param['scroll'] = true;
		$bw = new tabela01($param);
		
		$bw->addColuna(array('campo' => 'num' 			, 'etiqueta' => '#'					,'tipo' => 'T', 'width' =>  70, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'			,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'nome_projeto'  , 'etiqueta' => 'Projeto'			,'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'solicitante'	, 'etiqueta' => 'Solicitante'		,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'data' 			, 'etiqueta' => 'Data'				,'tipo' => 'D', 'width' =>  70, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'tempo' 		, 'etiqueta' => 'Tempo'				,'tipo' => 'T', 'width' =>  70, 'posicao' => 'C'));
		//$bw->addColuna(array('campo' => 'os' 			, 'etiqueta' => 'OS'				,'tipo' => 'T', 'width' =>  70, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'apelido' 		, 'etiqueta' => 'Recurso'			,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'descricao' 	, 'etiqueta' => 'Descrição'			,'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
		
		// Botão EDITAR
		$param = array(
				'texto' => 'Editar',
				'link' => getLink() . 'editar&id=',
				'coluna' => 'id64',
				'width' => 10,
				'flag' => '',
				//'tamanho' => 'pequeno',
				'cor' => 'success',
				'flag' => 'editar'
		);
		$bw->addAcao($param);
		
		// Botão EXCLUIR
		$param2 = array(
				'texto' => 'Excluir',
				'link' => getLink() . 'excluir&id=',
				'coluna' => 'id64',
				'width' => 10,
				'flag' => '',
				//'tamanho' => 'pequeno',
				'cor' => 'danger',
				'flag' => 'editar'
		);
		$bw->addAcao($param2);
		
		$param = [];
		
		$p = [];
		$p['onclick'] 	= "setLocation('" . getLink() . "editar&id=".base64_encode('0')."')";
		$p['texto'] 	= 'Incluir';
		$p['cor'] 		= 'success';
		
		$param['botoesTitulo'][] = $p;
		if(verificarAcaoSys016(getUsuario(), 'faturar_tarefa')){
			$p = array('onclick' => "setLocation('" . getLink() . "faturamento')",'texto' => 'Faturamento', 'cor' => 'light');
			$param['botoesTitulo'][] = $p;
		}
		
		//Rotina01 pra filtro
		$param['filtro'] = true;
		$param['titulo'] = $this->_titulo;
		$param['programa'] = $this->_programa;
		$roti = new rotina01($param);
		
		$filtro = $roti->getFiltro();
		
		$dados = $this->getDados($filtro);
		if(count($dados) > 0){
			$roti->escondeFiltro();
		}
		$bw->setDados($dados);
		
		$roti->setConteudo($bw);
		
		return $roti.'';
	}
	
	public function editar(){
		$sn = tabela("000003","desc");
		$id = base64_decode(getParam($_GET, 'id', 0));
		$this->addJSProjeto();
		
		if(!$this->validaEdita($id) && $id != 0){
			addPortalMensagem("", "Você não tem permissão para alterar essa tarefa", 'erro');
			return $this->index();
		}else{
			$param = [];
			$form = new form01($param);
			$dados = $this->getDados([], $id);
			
			if($id == 0){
				$tipo = 'A';
				$dados['fat'] 			= 'S';
				$dados['libfat'] 		= 'S';
				$dados['cliente'] 		= '';
				$dados['projeto'] 		= '';
				$dados['solicitante']	= '';
				$dados['data']		    = '';
				$dados['tempo']		    = '';
				$dados['descricao']	   	= '';
			}else{
				$tipo = 'I';
			}

			$form->addCampo(array('id' => '','campo' => 'formPrograma[cliente]'		,'etiqueta' => 'Cliente'			,'tipo' => $tipo	,'tamanho' => '6'	,'linha' => '1','largura' => 4,'valor' => $dados['cliente']		,'pasta' => 0,'lista' => funcoes_cad::getListaClientes(),'validacao' => '','obrigatorio' => true, 'onchange' => 'callAjax();'));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[projeto]'		,'etiqueta' => 'Projeto'			,'tipo' => 'A'		,'tamanho' => '6'	,'linha' => '1','largura' => 4,'valor' => $dados['projeto']		,'pasta' => 0,'lista' => $this->montarListaProjetos($id),'validacao' => '','obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[solicitante]'	,'etiqueta' => 'Solicitante'		,'tipo' => 'T'		,'tamanho' => '25'	,'linha' => '1','largura' => 4,'valor' => $dados['solicitante']	,'pasta' => 0,'lista' => ''								,'validacao' => '','obrigatorio' => true));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[data]'		,'etiqueta' => 'Data'				,'tipo' => 'D'		,'tamanho' => '8'	,'linha' => '2','largura' => 3,'valor' => $dados['data']		,'pasta' => 0,'lista' => ''								,'validacao' => '','obrigatorio' => true));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[tempo]'		,'etiqueta' => 'Tempo'				,'tipo' => 'T'		,'tamanho' => '5'	,'linha' => '2','largura' => 3,'valor' => $dados['tempo']		,'pasta' => 0,'lista' => ''								,'validacao' => '','obrigatorio' => true, 'mascara' => 'H',));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[fat]'			,'etiqueta' => 'Fatura'				,'tipo' => 'A'		,'tamanho' => '1'	,'linha' => '2','largura' => 3,'valor' => $dados['fat']			,'pasta' => 0,'lista' => $sn							,'validacao' => '','obrigatorio' => true));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[libfat]'		,'etiqueta' => 'Fatura Liberada'	,'tipo' => 'A'		,'tamanho' => '1'	,'linha' => '2','largura' => 3,'valor' => $dados['libfat']		,'pasta' => 0,'lista' => $sn							,'validacao' => '','obrigatorio' => true));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[descricao]'	,'etiqueta' => 'Descrição'			,'tipo' =>'TA'		,'tamanho' => '100'	,'linha' => '3','largura' =>12,'valor' => $dados['descricao']	,'pasta' => 0,'lista' => ''								,'validacao' => '','obrigatorio' => true, 'linhasTA' => 10));
			//$form->addCampo(array('id' => '','campo' => 'formPrograma[tkt]'			,'etiqueta' => 'Número do Ticket'	,'tipo' => 'T','tamanho' => '11'	,'linha' => '5','largura' => 2,'valor' => $dados['tkt']			,'pasta' => 0,'lista' => ''						,'validacao' => '','obrigatorio' => false));
			
			$form->setEnvio(getLink() . 'salvar&id=' . base64_encode($id), 'formPrograma', 'formPrograma');

			$param = [];
			$param['titulo'] = $id == 0 ? 'NOVA Tarefa' : 'EDIÇÃO Tarefa';
			$param['conteudo'] = $form;
			$ret = addCard($param);
			
			putAppVar('sdm_tarefa_form', 'ok');
			
			return $ret;
		}
	}
	
	public function salvar(){
		$id = getParam($_GET, 'id', 0);
		$id = intval(base64_decode($id));
		
		$ok = getAppVar('sdm_tarefa_form');
		if(isset($_POST['formPrograma']) && count($_POST['formPrograma']) > 0 && $ok == 'ok'){
			$dados = $_POST['formPrograma'];
			$campos = [];
			
			$campos['usuario'] 		= getUsuario();
			if(isset($dados['cliente'])){
				if($dados['cliente'] != ''){
					$campos['cliente'] 		= $dados['cliente'];
				}
			}
			
			if(isset($dados['projeto'])){
				$campos['projeto'] 	= $dados['projeto'];
			}
			else{
				$campos['projeto'] 	= '';
			}
			
			$campos['solicitante']	= escapeQuery($dados['solicitante']);
			$campos['descricao']	= escapeQuery($dados['descricao']);
			$campos['data'] 		= datas::dataD2S($dados['data']);
			$campos['tempo'] 		= $dados['tempo'];
			
			$campos['fat'] 			= $dados['fat'];
			$campos['libfat'] 		= $dados['libfat'];
			
			$campos['os']			= isset($dados['os']) ? $dados['os'] : 0;
			$campos['ticket']		= isset($dados['ticket']) ? $dados['ticket'] : 0;
			
			if($id == 0){
				$campos['inc_user']	= getUsuario();
				$sql = montaSQL($campos, 'sdm_tarefas');
			}else{
				$sql = montaSQL($campos, 'sdm_tarefas', 'UPDATE'," id = $id ");
			}
			query($sql);
		}
		putAppVar('sdm_tarefa_form', '');
		
		return $this->index();
	}
	
	
	public function excluir(){
		$id = getParam($_GET, 'id', 0);
		if($id != '0'){
			$id = base64_decode($id);
		}
		if(!$this->validaEdita($id)){
			addPortalMensagem("", "Você não tem permissão para alterar essa tarefa", 'erro');
			return $this->index();
		}else{
			query("UPDATE sdm_tarefas SET del = 'S' WHERE id = $id ");
		}
		return $this->index();
	}
	
	public function faturamento(){
		$cliente = getParam($_POST, 'selecionaCliente', '');
		if($cliente==''){
			return $this->formInicial($cliente) . '';
		}
		else{
			$dados = $this->getDadosFat($cliente);
			return $this->formSecundario($dados, $cliente) . '';
		}
	}
	
	public function gravaOS(){
		$cliente = base64_decode(getParam($_GET, 'cliente'));
		$tar = getParam($_POST, 'exportar');
		
		if(strlen($cliente) != 6 || count($tar) == 0){
			if(count($tar) == 0){
				addPortalMensagem('Não foi selecionadas tarefas.','error');
			}
			
			if(strlen($cliente) != 6 || count($tar) == 0){
				addPortalMensagem('Não foi possível identificar o cliente.','error');
			}
			redireciona(getLink().'index');
		}
		
		$horas = getParam($_POST, 'totalHoras');
		$os = $this->gravaCabOS($cliente, $horas, implode(',', $tar));
		
		if($os !== false && $os > 0){
			$tarefas = $this->getTarefasOS($tar);
			$this->gravaItensOS($os, $tarefas, implode(',', $tar));
			addPortalMensagem("OS $os gerada com sucesso!.");
			redireciona("index.php?menu=sdm_os.listar_os.editar&id=$os");
		}else{
			addPortalMensagem('Erro ao gravar o cabeçalho da OS.','error');
		}
		
		redireciona(getLink().'index');
	}
	
	//Pega os dados da tabela
	private function getDados($filtro, $id = 0){
		$ret = [];
		$where = '';
		if (isset($filtro['CLIENTE']) && $filtro['CLIENTE'] != '') {
			$where .= " AND sdm_tarefas.cliente = '" . $filtro['CLIENTE'] . "'";
		}
		
		if (isset($filtro['FATURADA']) && $filtro['FATURADA'] != ''){
			if($filtro['FATURADA'] == 'N') {
				$where .= " AND os = 0";
			}else if($filtro['FATURADA'] == 'S'){
				$where .= " AND os != 0";
			}
		}
		
		if (isset($filtro['RECURSO']) && $filtro['RECURSO'] != ''){
			$where .= " AND sdm_tarefas.usuario = '" . $filtro['RECURSO'] . "'";
		}
		
		if (isset($filtro['DATAINI']) && $filtro['DATAINI'] != ''){
			$where .= " AND data >= " . $filtro['DATAINI'] . "";
		}
		
		if (isset($filtro['DATAFIM']) && $filtro['DATAFIM'] != ''){
			$where .= " AND data <= " . $filtro['DATAFIM'] . "";
		}
		
		if($id > 0){
			$where .= " AND sdm_tarefas.id = $id";
		}
		
		$colunas = array('id', 'usuario', 'cliente', 'projeto', 'solicitante', 'descricao', 'data', 'tempo', 'ticket', 'fat', 'libfat', 'os');
		$sql = "
				SELECT
					sdm_tarefas.*,
					cad_organizacoes.nreduz,
					sdm_recursos.apelido
				FROM
					sdm_tarefas,
					cad_organizacoes,
					sdm_recursos
				WHERE
					sdm_tarefas.del <> '*'
					$where
					AND sdm_tarefas.cliente = cad_organizacoes.cod
					AND sdm_tarefas.usuario = sdm_recursos.usuario
				";
//echo "$sql <br>\n";
					$rows = query($sql);
					$usuario = getUsuario();
					
					if(is_array($rows) && count($rows) > 0){
						foreach($rows as $row){
							foreach($colunas as $col){
								$temp[$col] = $row[$col];
							}
							$temp['nome_projeto'] = $this->getNomeProjeto($temp['projeto']);
							$temp['id64']		= base64_encode($row['id']);
							$temp['apelido'] 	= $row['apelido'];
							$temp['num'] 		= str_pad($temp['id'], 6, "0", STR_PAD_LEFT);
							$temp['cliente'] 	= $row['nreduz'];
							$temp['os'] 		= str_pad($temp['os'], 6, "0", STR_PAD_LEFT);
							//Só pode editar e excluir se for do próprio técnico
							$temp['editar']		= $row['usuario'] == $usuario ? true : false;
							//Se já tiver virado OS não pode mais alterar/excluir
							if($row['os'] > 0){
								$temp['editar']	= false;
							}
							
							if($id > 0){
								$ret = $temp;
							}else{
								$ret[] = $temp;
							}
						}
					}
					return $ret;
	}
	
	private function validaEdita($id){
		$ret = false;
		$row = query("SELECT usuario FROM sdm_tarefas WHERE id = $id ");
		if(isset($row[0]['usuario'])){
			if(getUsuario() == $row[0]['usuario']){
				$ret = true;
			}
		}
		return $ret;
	}
	
	private function getId(){
		$ret = 1;
		$rows = query("SELECT MAX(id) FROM sdm_tarefas where cliente = '".self::getClienteUsuario()."'");
		if(is_array($rows)&&count($rows)>0){
			$ret = $ret + $rows[0][0];
		}
		return $ret;
	}
	
	private function formInicial($cliente){
		global $nl;
		$ret = '';
		formbase01::setLayout('basico');
		$param = [];
		$param['nome'] = 'selecionaCliente';
		$param['valor'] = $cliente;
		$param['etiqueta'] = 'Cliente';
		$param['title'] = 'Selecione o cliente';
		$param['lista'] = funcoes_cad::getListaClientes();
		$param['onchange'] = "$('#formRelatorio').submit()";
		$param['procura'] = true;
		$formCliente = formbase01::formSelect($param);
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-3">'.$formCliente.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = [];
		$param['acao'] = getLink().'faturamento';
		$param['nome'] = 'formRelatorio';
		$form = formbase01::form($param, $ret);
		
		$param = [];
		
		$botao = [];
		$botao["onclick"]= "setLocation('" . getLink() . "index')";
		$botao["texto"]	= "Cancelar";
		$botao['cor'] = 'danger';
		
		$param['botoesTitulo'][] = $botao;
		$param['titulo'] = 'SELECIONE O CLIENTE';
		$param['conteudo'] = $form;
		$ret = addCard($param);
		
		return $ret;
	}
	
	private function formSecundario($dados, $cliente){
		global $nl;
		$ret = '';
		$bw = new tabela01(array('paginacao' => false));
		
		//Caixa de seleção
		$bw->addColuna(array('campo' => 'sel' 			, 'etiqueta' => ''					,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'id' 			, 'etiqueta' => '#'					,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'data' 			, 'etiqueta' => 'Data'				,'tipo' => 'D', 'width' =>  80, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'tempo' 		, 'etiqueta' => 'Tempo'				,'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
		$bw->addColuna(array('campo' => 'solicitante' 	, 'etiqueta' => 'Solicitante'		,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'descricao' 	, 'etiqueta' => 'Descrição'			,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$bw->addColuna(array('campo' => 'apelido'		, 'etiqueta' => 'Recurso'			,'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
		
		$this->addJS();
		$bw->setDados($dados);
		
		formbase01::setLayout('basico');
		
		//Inverte Seleção
		$param = [];
		$param['onclick'] = 'inverterSelecao();';
		$param['texto'] = 'Inverter Seleção';
		//$param['bloco'] = true;
		$param['tamanho'] = 'padrao';
		$formSelecionaTodos = '<br>'.formbase01::formBotao($param);
		
		
		//Soma dos títulos selecionados
		$param = [];
		$param['nome'] = 'formValSelecionados';
		$param['etiqueta'] = 'Tempo Total:';
		$param['valor'] = '0:00';
		$param['mascara'] = 'H';
		$formValor = formbase01::formTexto($param, false);
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-2">'.$formSelecionaTodos.'</div>'.$nl;
		$ret .= '	<div  class="col-md-2">'.$formValor.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = [];
		
		if(count($dados) > 0){
			$p = [];
			$p['texto'] 	= 'Criar Nova OS';
			$p['id'] 		= 'id';
			$p['cor'] 		= 'success';
			$p["onclick"]	= "$('#formExportar').submit()";
			$param['botoesTitulo'][] = $p;
		}
		
		$p = [];
		$p['cor'] 		= 'danger';
		$p['texto'] 	= 'Cancelar';
		$p["onclick"]	= "setLocation('" . getLink() . "index')";
		$param['botoesTitulo'][] = $p;
		
		$ret .= $bw;
		
		$paramTotal = [];
		$paramTotal['nome'] = 'totalHoras';
		$paramTotal['valor'] = '00:00';
		$ret .= formbase01::formHidden($paramTotal);
		
		$param_form = array(
			'acao' => getLink() . 'gravaOS&cliente='.base64_encode($cliente),
			'id' => 'formExportar',
			'nome' => 'formExportar',
		);
		$ret = formbase01::form($param_form, $ret);
		$param['titulo'] = 'Faturamento';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	private function addJS(){
		$ret = '';
		
		$ret .= "
				
function inverterSelecao(){
				var inputs2= $('input:checkbox');
				inputs2.each(function(){
					selecionado = $(this).prop('checked') ? false : true;
					$(this).prop('checked', selecionado);
	 			 });
                ValTotal();
			}
				
				
function ValTotal(){
                var inputs2= $('input:checkbox');
				var valTotal = '0:00';
				inputs2.each(function(){
					if($(this).prop('checked')){
                        valorT = $(this).parent().parent().find('td')[3].innerHTML;
				
                        hora1 = getHora(valorT);
                    	hora2 = getHora(valTotal);
				
                    	minuto1 = getMinuto(valorT);
                    	minuto2 = getMinuto(valTotal);
				
				
                    	minuto = minuto1+minuto2;
                    	hrmin = Math.floor(minuto / 60);
                    	minuto = minuto - hrmin * 60;
				
                    	hora = hora1 + hora2 + hrmin;
				
                    	if (minuto == 0){ minuto = '00';}
                    	if (minuto < 10 && minuto != 0){ minuto = '0'+minuto;}
                    	if (hora < 1){ hora = '0';}
				
				
						valTotal = hora+':'+minuto;
// valTotal = valorT;
					}
	 			 });
				
                $('#formValSelecionados').val(valTotal);
				$('#totalHoras').val(valTotal);
			}
				
function getHora(horas){
 	dp = horas.indexOf(':');
	hora = horas.substr(0,dp);
	if (hora.length == 0)
		hora = '0';
	return(parseFloat(hora));
}
				
function getMinuto(horas){
 	dp = horas.indexOf(':');
	minuto = horas.substr(dp+1,horas.length-1);
	if (minuto.length == 0)
		minuto = '0';
	return(parseFloat(minuto));
}
		";
		
		addPortaljavaScript($ret);
	}
	
	private function addJSProjeto(){
		$ret = "function callAjax(){
	        var cliente = document.getElementById('formProgramacliente').value;
	        var option = '';
 			$.getJSON('" . getLinkAjax('getProjetosCliente', true, 'sdm_ajax','sdm_geral')  . "&cliente=' + cliente, function (dados){
	            if (dados.length > 0){
	                $.each(dados, function(i, obj){
	                    option += '<option value=" . '"' . "'+obj.valor+'" . '"' . ">'+obj.etiqueta+'</option>';
	                    $('#formProgramaprojeto').html(option).show();
	                });
	            }
	        })
	    }";
		addPortaljavaScript($ret);
	}
	
	private function getDadosFat($cliente = ''){
		$ret = [];
		$colunas = array('id', 'descricao', 'data', 'tempo', 'usuario', 'solicitante');
		$sql = "SELECT * FROM sdm_tarefas WHERE del <> '*' AND os = 0  AND cliente = '$cliente' ORDER BY data";
		$rows = query($sql);
		
		if (is_array($rows) && count($rows) > 0){
			foreach ($rows as $r) {
				foreach ($colunas as $col){
					$temp[$col] = $r[$col];
				}
				$temp['apelido']	= funcoes_cad::getRecursoCampo($temp['usuario'],'apelido');
				$temp['id'] 		= str_pad($temp['id'], 6, "0", STR_PAD_LEFT);
				$temp['sel'] 		= '<input name="exportar[' . $r['id'] . ']" type="checkbox" value="' . $r['id'] . '"  id="ID_' . $r['id'] . '" onchange="ValTotal()" >';
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	public function ajax(){
		global $app;
		
		$GLOBALS['tws_pag'] = array(
				'header'   	=> false, //Imprime o cabeçalho (no caso de ajax = false)
				'html'		=> false, //Imprime todo html (padão) ou só o processamento principal?
				'menu'   	=> false,
				'content' 	=> false,
				'footer'   	=> false,
				'onLoad'	=> '',
		);
		
		$ret = [];
		
		
		$ret[] = array('valor' => '', 'etiqueta' => '');
		$cliente = getParam($_GET, 'cliente', '');
		
		if($cliente != ''){
			$sql = "select * from sdm_projetos where cliente = '$cliente'";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$temp = array(
							'valor' => $row['id'],
							'etiqueta' => $row['titulo'],
					);
					$ret[] = $temp;
				}
			}
		}
		return json_encode($ret);
	}
	
	private function montarListaProjetos($id){
		$ret = [];
		$ret[] = array('', '');
		
		$sql = "select cliente from sdm_tarefas where id = '$id'";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			$cliente = $rows[0]['cliente'];
			$sql = "select * from sdm_projetos where cliente = '$cliente'";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$temp = array($row['id'], $row['titulo']);
					$ret[] = $temp;
				}
			}
		}
		
		return $ret;
	}
	
	private function getNomeProjeto($id = ''){
		$ret = '';
		if($id != ''){
			$sql = "select titulo from sdm_projetos where id = '$id'";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				$ret = $rows[0]['titulo'];
			}
		}
		return $ret;
	}
	
	private function gravaCabOS($cliente, $horas, $tarefas){
		global $config;
		$campos = [];
		$campos['cliente'] 		= $config['cliente'];
		$campos['emp'] 			= '1';
		$campos['user'] 		= getUsuario();
		$campos['data'] 		= date('Ymd');
		$campos['os_cliente'] 	= $cliente;
		$campos['modulo'] 		= 'Diversos';
		$campos['projeto'] 		= '';
		$campos['restricao'] 	= '000001';
		$campos['pessoa'] 		= '';
		$campos['servico'] 		= '';
		$campos['hora_ini']		= '00:00';
		$campos['hora_fim'] 	= $horas;
		$campos['hora_trans'] 	= '00:00';
		$campos['hora_add'] 	= '00:00';
		$campos['hora_sub'] 	= '00:00';
		$campos['hora_total'] 	= $horas;
		$campos['observacao'] 	= '';
		$campos['obs_int'] 		= '';
		$campos['faturado'] 	= '';
		$campos['dtfatura'] 	= '';
		$campos['comissao'] 	= '';
		$campos['dt_comissao'] 	= '';
		$campos['entregue'] 	= '';
		$campos['dt_ent'] 		= '';
		$campos['user_ent'] 	= '';
		$campos['coordenador'] 	= '';
		$campos['tarefas']		= $tarefas;
		$campos['del'] 			= 'N';
		
		$sql = montaSQL($campos, 'sdm_os');
		$nr_os = query($sql);
		
		return $nr_os;
	}

	private function getTarefasOS($tarefas){
		$ret = [];
		
		$sql = "SELECT * FROM sdm_tarefas WHERE del <> 'N' AND id IN (".implode(',', $tarefas).") ORDER BY data";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				
				$temp['id'] 		= $row['id'];
				$temp['projeto'] 	= $row['projeto'];
				$temp['solicitante']= $row['solicitante'];
				$temp['descricao'] 	= $row['descricao'];
				$temp['data'] 		= $row['data'];
				$temp['tempo'] 		= $row['tempo'];
				$temp['ticket'] 	= $row['ticket'];
				$temp['fat'] 		= $row['fat'];
				$temp['libfat'] 	= $row['libfat'];

				$ret[] = $temp;
			}
		}
		
		return $ret;
	}

	private function gravaItensOS($os, $tarefas, $listaTarefas){
		global $config;
		
		//Grava os itens da OS
		foreach ($tarefas as $tarefa){
			$campos = [];
			$campos['cliente'] 		= $config['cliente'];
			$campos['emp'] 			= '1';
			$campos['fil'] 			= '1';
			$campos['os'] 			= $os;
			$campos['projeto'] 		= $tarefa['projeto'];
			$campos['tarefa'] 		= $tarefa['id'];
			$campos['ticket'] 		= $tarefa['ticket'];
			$campos['horas'] 		= $tarefa['tempo'];
			$campos['realizado'] 	= '100';
			
			$desc = datas::dataS2D($tarefa['data']);
			if(!empty($tarefa['solicitante'])){
				$desc .= ' - '.$tarefa['solicitante'];
			}
			$desc .= ' - '.$tarefa['descricao'];
			
			$campos['descricao'] 	= $desc;
			$campos['tarefa_pro'] 	= '';
			$campos['excluida'] 	= 'N';
			
			$sql = montaSQL($campos, 'sdm_os_itens');
			query($sql);
		}
		
		//Grava o nr da OS nas tarefas
		$sql = "UPDATE sdm_tarefas SET os = '$os' WHERE id IN ($listaTarefas)";
		query($sql);
	}
}