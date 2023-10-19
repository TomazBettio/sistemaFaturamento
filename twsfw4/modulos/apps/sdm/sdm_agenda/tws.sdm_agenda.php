<?php
/*
 * Data Criacao: 28/12/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Agenda
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class sdm_agenda{
	var $funcoes_publicas = array(
			'index' 	=> true,
			'agenda' 	=> true,
			'marcar' 	=> true,
			'excluir' 	=> true,
	        'ajax'      => true,
	);
	
	//Programa
	private $_programa = '';
	
	//Filtro
	private $_filtro;
	
	//Recursos
	private $_recursos = [];
	
	//Agendas Marcadas
	private $_agendas = [];
	
	//Nome clientes
	private $_clientes = [];
	
	function __construct(){
		$this->getRecursos();
		$this->getClientesNome();
		$this->_programa = get_class($this).'_geral';
		
		$paramFiltro = array();
		$paramFiltro['tamanho'] = 12;
		$this->_filtro = new formFiltro01($this->_programa, $paramFiltro);
		
		if(false){
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente'	, 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sdm_agenda_clientes()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Analista', 'variavel' => 'RECURSO', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sdm_agenda_recursos()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
	}
	
	function index(){
		$ret = '';
		
		$this->scriptAgenda();
		$filtro  = $this->_filtro->getFiltro();
		addPortalJquery("$('#formFiltro').hide();");
		
		$diaIni = !empty($filtro['DATAINI']) ? $filtro['DATAINI'] : datas::getDataDias();
		$diaFim = !empty($filtro['DATAFIM']) ? $filtro['DATAFIM'] : datas::getDataDias(15, $diaIni);
		$recurso = !empty($filtro['RECURSO']) ? $filtro['RECURSO'] : '';
		$cliente = !empty($filtro['CLIENTE']) ? $filtro['CLIENTE'] : '';
		
		//Botoes semana e mês
		$operacao = getOperacao();
		if(!empty($operacao)){
			$datas = $this->getDatas($operacao);
			$diaIni = $datas['ini'];
			$diaFim = $datas['fim'];
			
			$param['DATAINI'] = $diaIni;
			$param['DATAFIM'] = $diaFim;
			$this->_filtro->setRespostas($param);
			$this->_filtro->setLink(getLink().'index');
		}
		
		if($diaIni > $diaFim){
			$diaTemp = $diaIni;
			$diaIni = $diaFim;
			$diaFim = $diaTemp;
	
			$param['DATAINI'] = $diaIni;
			$param['DATAFIM'] = $diaFim;
			$this->_filtro->setRespostas($param);
			$this->_filtro->setLink(getLink().'index');
		}
		
		$dias = datas::calendario($diaIni, $diaFim);
		
		$this->getAgendas($dias, $recurso, $cliente);
		
		$ret .= $this->_filtro;
		$ret .= $this->montaTabela($dias);
		
		$param = [];

		$botao = [];
		$botao['onclick']	= "setLocation('" . getLink() . "index.semana');";
		$botao['texto']		= 'Esta Semana';
		$botao['id'] 		= 'bt_semana';
		$botao['cor']		= 'info';
		$param['botoesTitulo'][] = $botao;
		
		$botao = [];
		$botao['onclick']	= "setLocation('" . getLink() . "index.mes');";
		$botao['texto']		= 'Este Mês';
		$botao['id'] 		= 'bt_mes';
		$botao['cor']		= 'info';
		$param['botoesTitulo'][] = $botao;
		
		$botao = [];
		$botao['onclick']	= "$('#formFiltro').toggle();";
		$botao['texto']		= FORMFILTRO_TITULO;
		$botao['id'] 		= 'bt_form';
		$param['botoesTitulo'][] = $botao;
		
		$param['titulo'] 	= 'Agendas';
		$param['conteudo'] 	= $ret;
		$param['cor']		= 'success';
		$ret = addCard($param);
		
		return $ret;
	}
	
	function agenda(){
		$recurso = base64_decode(getParam($_GET, 'recurso'));
		$dia = base64_decode(getParam($_GET, 'dia'));
		
		if(!empty($dia) && !empty($recurso)){
			$ret = $this->getMarcacao($recurso, $dia);
		}else{
			$ret = $this->index();
		}
		
		return $ret;
	}
	
	function marcar(){
		$dia = getAppVar('vert_agenda_dia');
		$recurso = getAppVar('vert_agenda_recurso');
		
		if(!empty($dia) && !empty($recurso)){
			$dados = getParam($_POST, 'formAgenda');
			
			$campos = [];
			
			$campos['cliente'] 			= getCliente();
			$campos['emp'] 				= '';
			$campos['fil'] 				= '01';
			$campos['recurso'] 			= $recurso;
			$campos['data'] 			= $dia;
			$campos['turno'] 			= $dados['turno'];
			$campos['local'] 			= $dados['local'];
			$campos['contato'] 			= $dados['contato'];
			$campos['cliente_agenda'] 	= $dados['cliente'];
			$campos['tipo'] 			= $dados['tipo'];
			$campos['tarefa'] 			= $dados['tarefa'];
			$campos['status'] 			= 'F';
			$campos['os'] 				= '';
			$campos['ticket'] 			= '';
			$campos['marcado_por'] 		= getUsuario();
			$campos['projeto']			= $dados['projeto'] ?? '';
			
			$sql = montaSQL($campos, 'sdm_agenda');
			$res = query($sql);
			if($res){
				addPortalMensagem('Sucesso', 'Agenda marcada!');
			}else{
				addPortalMensagem('Erro', 'Algo sinistro aconteceu, por favor tente novamente!', 'erro');
			}
		}else{
			addPortalMensagem('Erro', 'Algo sinistro aconteceu, por favor tente novamente!', 'erro');
		}
		
		putAppVar('vert_agenda_recurso', '');
		putAppVar('vert_agenda_dia', '');
		
		return $this->index();
	}
	
	function excluir(){
		$erro = false;
		$agenda = base64_decode(getParam($_GET, 'agenda'));
		
		if(!empty($agenda)){
			$dados = explode('|', $agenda);
			if(count($dados) == 4){
				$sql = "UPDATE sdm_agenda SET status = 'E', del = '*', del_por = '".getUsuario()."', del_em = '".date("Y-m-d H:i:s")."' WHERE recurso = '".$dados[0]."' AND data = '".$dados[1]."' AND cliente_agenda = '".$dados[3]."' AND turno = '".$dados[2]."'";
				//echo "$sql <br>\n";
				query($sql);
				
				addPortalMensagem('Sucesso', 'Agenda excluída!');
				
				$ret = $this->getMarcacao($dados[0], $dados[1]);
			}else{
				$erro = true;
			}
		}else{
			$erro = true;
		}
		
		if($erro){
			addPortalMensagem('Erro', 'Algo sinistro aconteceu, por favor tente novamente!', 'erro');
			$ret = $this->index();
		}
		
		return $ret;
	}
	
	//----------------------------------------------------------------- UI   ------------
	
	private function getTabelaMarcadas($agendas, $recurso, $dia){
		$ret = '';
		$this->geraScriptConfirmacao();
		
		$dados = [];
		foreach ($agendas as $turno => $cliente){
			$temp = [];
			$temp['cod'] = $cliente;
			$temp['nome'] = $this->_clientes[$cliente];
			$temp['turno'] = $turno;
			$temp['chave'] = base64_encode($recurso.'|'.$dia.'|'.$turno.'|'.$cliente);
			$temp['descricao'] = $recurso.' | '.datas::dataS2D($dia,2).' | '.$turno;
			
			$dados[] = $temp;
		}
		
		$param = [];
		$param['width'] = 'AUTO';
		$param['paginacao'] = false;
		$param['ordenacao'] = false;
		$param['filtro'] = false;
		$param['scroll'] = false;
		$param['info'] = false;
		$tab = new tabela01($param );
		
		$tab->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'Cod.'		,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Cliente'	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'turno'	, 'etiqueta' => 'Turno'		,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		
		// Botão Excluir
		if(getUsuario('tipo') == 'S' || getUsuario('tipo') == 'A'){
    		$param = [];
    		$param['texto'] = 'Excluir';
    		$param['link'] 	= "javascript:confirmaExclusao('".getLink()."excluir&agenda=','{ID}',{COLUNA:descricao})";
    		$param['coluna']= 'chave';
    		$param['cor'] 	= 'danger';
    		$param['flag'] 	= '';
    		$param['width'] = 80;
    		$param['pos'] = 'I';
    		$tab->addAcao($param);
		}
		
		$tab->setDados($dados);
		
		$ret .= $tab;
		
		return $ret;
	}
	
	private function getMarcacao($recurso, $dia){
		global $nl;
		$ret = '';
		$marcadas = [];
		
		putAppVar('vert_agenda_recurso', $recurso);
		putAppVar('vert_agenda_dia', $dia);
		
		$analista = $this->_recursos[$recurso];
		$this->getAgendas($dia, $recurso);
		$agendas = $this->_agendas[$recurso][$dia] ?? [];
		if(count($agendas) > 0){
			$marcadas = $this->getTabelaMarcadas($agendas, $recurso, $dia);
		}else{
			$marcadas = 'Não existe agenda marcarda nesta data!';
		}
		
		
		$turnos = $this->getTurnosLivres($agendas);
		//$turnos = tabela('000022');
		$local = tabela('000023');
		$tipo = tabela('000024');
		$clientes = $this->getClientes();
		
		if(true){
			$turnosVal = '';
			$localVal = 'C';
			$tipoVal = 'E';
		}
		
		$this->addAjaxProjetos();
		
		$form = new form01();
		$form->setBotaoCancela();
		$form->addCampo(array('id' => 'cliente'	, 'campo' => 'formAgenda[cliente]'	, 'etiqueta' => 'Cliente'	, 'tipo' => 'A'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => $clientes	, 'validacao' => '', 'obrigatorio' => true, 'onchange' => 'callAjax();'));
		
		$form->addCampo(array('id' => 'projeto'	, 'campo' => 'formAgenda[projeto]'	, 'etiqueta' => 'Projeto'	, 'tipo' => 'A'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => array(array('', ''))	, 'validacao' => '', 'obrigatorio' => false));
		
		$form->addCampo(array('id' => 'turno'	, 'campo' => 'formAgenda[turno]'	, 'etiqueta' => 'Turno'		, 'tipo' => 'A'		, 'tamanho' => '20', 'linha' => 2, 'largura' => 4	, 'linhasTA' => ''	, 'valor' => $turnosVal , 'lista' => $turnos	, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'local'	, 'campo' => 'formAgenda[local]'	, 'etiqueta' => 'Local'		, 'tipo' => 'A'		, 'tamanho' => '10', 'linha' => 2, 'largura' => 8	, 'linhasTA' => ''	, 'valor' => $localVal	, 'lista' => $local		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'contato'	, 'campo' => 'formAgenda[contato]'	, 'etiqueta' => 'Contato'	, 'tipo' => 'T'		, 'tamanho' => '10', 'linha' => 3, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'tipo'	, 'campo' => 'formAgenda[tipo]'		, 'etiqueta' => 'Tipo'		, 'tipo' => 'A'		, 'tamanho' => '20', 'linha' => 3, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => $tipoVal	, 'lista' => $tipo		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'tarefa'	, 'campo' => 'formAgenda[tarefa]'	, 'etiqueta' => 'Tarefa'	, 'tipo' => 'TA'	, 'tamanho' => '20', 'linha' => 4, 'largura' => 12	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
		
		$form->setEnvio(getLink().'marcar', 'formAgenda', 'formAgenda');
		
		$param = [];
		$param['titulo'] = 'Recurso: '.$analista['apelido'];
		$param['conteudo'] = $form;
		$formAgenda = addCard($param);
		
		$param = [];
		$param['titulo'] = 'Marcadas '.datas::dataS2D($dia);
		$param['conteudo'] = $marcadas;
		$agendasMarcadas = addCard($param);
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-6">'.$formAgenda.'</div>'.$nl;
		//$ret .= '	<div  class="col-md-2"></div>'.$nl;
		$ret .= '	<div  class="col-md-6">'.$agendasMarcadas.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = [];
		$param['titulo'] = '	Agenda';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	private function montaTabela($dias){
		global $nl;
		$ret = '';
		//print_r($this->_agendas);
		$ret .= '<div class="table-responsive">'.$nl;
		$ret .= '<table class="table table-striped table-bordered table-hover" id="agenda-tabela">'.$nl;
		$ret .= '	<thead class="thead-light">'.$nl;
		$ret .= '		<tr>'.$nl;
		$ret .= '			<th scope="col">Analista</th>'.$nl;
		foreach ($dias[0] as $key => $dia){
			$ret .= '			<th scope="col" align="center">'.datas::dataS2D($dia,2).'<br>'.$dias[1][$key].'</th>'.$nl;
		}
		$ret .= '		</tr>'.$nl;
		$ret .= '	</thead>'.$nl;
		$ret .= '	<tbody>'.$nl;
		foreach ($this->_recursos as $recurso){
			$ret .= '		<tr>'.$nl;
			$ret .= '		<th scope="row">'.$recurso['apelido'].'</th>'.$nl;
			foreach ($dias[0] as $key => $dia){
				$cor = 'table-danger';
				$conteudo = '';
				if(isset($this->_agendas[$recurso['recurso']][$dia])){
					$cor = 'table-success';
					foreach ($this->_agendas[$recurso['recurso']][$dia] as $turno => $cliente){
						$conteudo .= $turno.'-'.$this->_clientes[$cliente]."<br>\n";
					}
				}else{
					//parcial
					//$cor = 'table-warning';
				}
				$ret .= '			<td class="'.$cor.'" align="center" onclick="agendaClick(\''.base64_encode($recurso['recurso']).'\',\''.base64_encode($dia).'\');">'.$conteudo.'</td>'.$nl;
			}
			$ret .= '		</tr>'.$nl;
		}
		$ret .= '	</tbody>'.$nl;
		$ret .= '</table>'.$nl;
		$ret .= '</div>'.$nl;
		
		return $ret;
	}
	
	//----------------------------------------------------------------- GETs ------------
	
	private function getTurnosLivres($agendas){
		$ret = [];
		$livre = ['M'=>true,'T'=>true,'N'=>true,'I'=>true];
		
		foreach ($agendas as $t => $cli){
			if($t == 'I'){
				unset($livre['M']);
				unset($livre['T']);
			}
			if($t == 'M' || $t == 'T'){
				unset($livre['I']);
			}
			unset($livre[$t]);
		}
		
		$turnos = tabela('000022');
		foreach ($turnos as $t){
			if(isset($livre[$t[0]])){
				$ret[] = $t;
			}
		}
		
		//print_r($ret);
		
		return $ret;
	}
	
	private function getRecursos($recurso = ''){
		$where = '';
		if(!empty($recurso)){
			$where = " AND recurso = '$recurso'";
		}
		$sql = "SELECT * FROM cad_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' $where ORDER BY apelido";
		//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['nome'] 	= $row['nome'];
				$temp['apelido']= $row['apelido'];
				$temp['tipo'] 	= $row['tipo'];
				$temp['recurso']= $row['usuario'];
				
				$this->_recursos[$row['usuario']] = $temp;
			}
		}
		//print_r($this->_recursos);
	}
	
	private function getAgendas($dias, $recurso = '', $cliente = ''){
		$this->_agendas = [];
		
		$dia = '';
		if(is_array($dias)){
			$datas = [];
			foreach ($dias[0] as $dia){
				$datas[] = $dia;
			}
			$dia = "'".implode("','", $datas)."'";
		}else{
			$dia = "'".$dias."'";
		}
		
		$recursoWhere = '';
		if(!empty($recurso)){
			$recursoWhere .= " AND recurso = '$recurso'";
		}
		if(!empty($cliente)){
			$recursoWhere .= " AND cliente_agenda = '$cliente'";
		}
		
		$sql = "SELECT * FROM sdm_agenda WHERE data IN ($dia) $recursoWhere AND cliente_agenda <> '' AND IFNULL(del,'') = '' ORDER BY recurso, data";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$usuario = $row['recurso'];
				$dia 	 = $row['data'];
				$turno	 = $row['turno'];
				
				$this->_agendas[$usuario][$dia][$turno] = $row['cliente_agenda'];
			}
		}
	}
	
	private function getClientesNome(){
		$sql = "SELECT cod, nreduz FROM cad_clientes ";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$this->_clientes[$row['cod']] = $row['nreduz'];
			}
		}
	}
	
	private function getClientes(){
		$ret = array();
		
		$ret[0][0] = "";
		$ret[0][1] = "&nbsp;";
		
		$sql = "SELECT cod, nreduz FROM cad_clientes WHERE ativo = 'S' ORDER BY nreduz";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$temp[0] = $row['cod'];
				$temp[1] = $row['nreduz'];
				
				$ret[] = $temp;
			}
		}
		//print_r($tabela);
		return $ret;
	}
	//----------------------------------------------------------------------------- UTEIS ------------------------------------------------------------
	private function scriptAgenda(){
		addPortaljavaScript('function agendaClick(recurso, dia){');
		addPortaljavaScript("	setLocation('".getLink()."agenda&recurso='+recurso+'&dia='+dia);");
		addPortaljavaScript("}");
	}
	
	private function geraScriptConfirmacao(){
		addPortaljavaScript('function confirmaExclusao(link,id,desc){');
		addPortaljavaScript('	if (confirm("Confirma a EXCLUSAO da agenda "+desc+"?")){');
		addPortaljavaScript('		setLocation(link+id);');
		addPortaljavaScript('	}');
		addPortaljavaScript('}');
		
	}
	
	private function getDatas($operacao){
		$ret = [];
		
		if($operacao == 'semana'){
			$sem = date('w');
			$ret['ini'] = datas::getDataDias($sem * -1);
			$ret['fim'] = datas::getDataDias(6 - $sem);
		}else{
			$ret['ini'] = date('Ym').'01';
			$ret['fim'] = date('Ymt');
		}
		
		return $ret;
	}
	
	public function ajax(){
	    $ret = array();
	    
	    
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
	
	private function addAjaxProjetos(){
	    $ret = "function callAjax(){
	        var cliente = document.getElementById('cliente').value;
	        var option = '';
	        $.getJSON('" . getLinkAjax('projeto') . "&cliente=' + cliente, function (dados){
	            if (dados.length > 0){
	                $.each(dados, function(i, obj){
	                    option += '<option value=" . '"' . "'+obj.valor+'" . '"' . ">'+obj.etiqueta+'</option>';
	                    $('#projeto').html(option).show();
	                });
	            }
	        })
	    }";
	    addPortaljavaScript($ret);
	}
	
}

function sdm_agenda_clientes(){
	$ret = array();
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT cod, nreduz FROM cad_clientes WHERE ativo = 'S' ORDER BY nreduz";
	$rows = query($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['cod'];
			$temp[1] = $row['nreduz'];
			
			$ret[] = $temp;
		}
	}
	//print_r($tabela);
	return $ret;
}

function sdm_agenda_recursos(){
	$ret = array();
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT usuario, apelido FROM cad_recursos WHERE agenda = 'S'  ORDER BY apelido";
	$rows = query($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['usuario'];
			$temp[1] = $row['apelido'];
			
			$ret[] = $temp;
		}
	}
	//print_r($tabela);
	return $ret;
}