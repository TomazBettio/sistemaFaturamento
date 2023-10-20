<?php
/*
 * Data Criacao: 23/03/2022
 * Autor: Alexandre Thiel
 *
 * Descricao: Marcação de agenda múltipla
 *
 * Alteracoes:
 * 
 * 08/09/2023 - Debug e integração com agenda MS Teams - Alex
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class agenda_multi{
	var $funcoes_publicas = array(
			'index' 	=> true,
			'marcar'	=> true,
	);
	
	//Programa
	private $_programa = '';
	
	//Recursos
	private $_recursos = [];
	
	//Agendas Marcadas
	private $_agendas = [];
	
	//Nome clientes
	private $_clientes = [];
	
	public function __construct(){
		formbase01::setLayout('basico');
		$this->getRecursos();
		$this->getClientesNome();
		$this->_programa = get_class($this);
		$javascript = '
			function marcarTodos(modulo, marcado){
				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
			}';
		addPortaljavaScript($javascript);
	}
	
	public function index(){
		$ret = '';
		$ret .= $this->formMarcacao();		
		return $ret;
	}
	
	public function marcar(){
		$dados = getParam($_POST, 'formAgenda');
		$dataIni 	= datas::dataD2S($dados['dataIni']);
		$dataFim 	= datas::dataD2S($dados['dataFim']);
		$recursos 	= $dados['REC'];
		/*
		$cliente 	= $dados['cliente'];
		$projeto 	= $dados['projeto'];
		$turno 		= $dados['turno'];
		$local 		= $dados['local'];
		$contato 	= $dados['contato'];
		$tipo 		= $dados['tipo'];
		$tarefa 	= $dados['tarefa'];
		*/
		$dias 		= getParam($_POST, 'diasSemana');
		
		$erro = [];
		
		if(empty($dataIni) || empty($dataFim)){
			$erro[] = 'As datas devem ser preenchidas.';
		}
		
		if(!is_array($dias) || count($dias) < 1){
			$dias = [];
		}
		
		if(count($recursos) < 1){
			$erro[] = 'Pelo menos um recurso deve ser marcado.';
		}
		
		if(count($erro) == 0){
			$datas = datas::calendario($dataIni, $dataFim, 'N');
			//print_r($datas);
			$marcadas = [];
			if(count($datas[0]) > 0){
				foreach ($datas[0] as $key => $d){
					if(array_key_exists($datas[1][$key], $dias) || count($dias) == 0){
//echo "$d <br>\n";
						foreach ($recursos as $rec => $ok){
							$marcadas[] = $this->marcarAgenda($dados, $d, $rec);
							$ret = $this->montaTabelaMarcadas($marcadas);
						}
					}
				}
			}else{
				$erro[] = 'Algo de errado não deu certo na determinação dos dias entre as datas.';
			}
		}
		
		if(count($erro) > 0){
			foreach ($erro as $e){
				addPortalMensagem($e,'error');
			}
			
			$ret = $this->formMarcacao();
		}
		
		return $ret;
		//return '';
	}
	
	//----------------------------------------------------------------- VO --------------
	private function marcarAgenda($param, $d, $rec){
		$ret = [];
		
		$campos = [];
		
		$campos['cliente'] 			= getCliente();
		$campos['emp'] 				= '';
		$campos['fil'] 				= '01';
		$campos['recurso'] 			= $rec;
		$campos['data'] 			= $d;
		$campos['turno'] 			= $param['turno'];
		$campos['local'] 			= $param['local'];
		$campos['contato'] 			= $param['contato'];
		$campos['cliente_agenda'] 	= $param['cliente'];
		$campos['tipo'] 			= $param['tipo'];
		$campos['tarefa'] 			= $param['tarefa'];
		$campos['status'] 			= 'F';
		$campos['os'] 				= '';
		$campos['ticket'] 			= '';
		$campos['marcado_por'] 		= getUsuario();
		$campos['projeto']			= $param['projeto'];
		
		
		$temp = preg_split('/[.]/', $rec);
		$nome = $temp[0];
		
		$dadosPost = array();
		$dadosPost['dia'] = $d;
		$dadosPost['email'] = $rec;
		$dadosPost['nome'] = $nome;
		$dadosPost['turno'] = $campos['turno'];
		$dadosPost['local'] = $campos['local'];
		$dadosPost['titulo'] = $param['titulo'] ?? "Agenda Múltipla - Automática";
		$dadosPost['tarefa'] = $campos['tarefa'];
		
		
		$calendario = new rest_calendario('https://graph.microsoft.com/v1.0', $dadosPost);    //, $this->_token);
		
		$ret = $calendario->agendarEvento($dadosPost['email']);

	    if($ret !== false && isset($ret['id'])){
	        $campos['id_api'] = $ret['id'];
	    } else {
	        $campos['id_api'] = '';
	        addPortalMensagem('Problema ao salvar na agenda do Teams!', 'erro');
	    }
	    
	    $sql = montaSQL($campos, 'sdm_agenda');
	     $res = query($sql);
	    
	    $campos['erro'] = false;
	    if(!$res){
	        $campos['erro'] = true;
	    }else{
	       // echo " - Enviarei email para {$campos['recurso']} - ";
	        app_sdm::enviaEmailAgendaRecurso($campos);
	    }
	
		
		
		/*
		$sql = montaSQL($campos, 'sdm_agenda');
		//$res = query($sql);
		$res = true;
		
		$campos['erro'] = false;
		if(!$res){
			$campos['erro'] = true;
		}else{
		    echo " - Enviarei email para {$campos['recurso']} - ";
			//app_sdm::enviaEmailAgendaRecurso($campos);
		}
		*/
		
		return $campos;
	}
	
	
	//----------------------------------------------------------------- UI   ------------
	
	private function formMarcacao(){
		global $nl;
		$ret = '';
		$form1 = '';
		$form2 = '';
		
		$formDatas = $this->montaFormDatas();
		$formRecursos = $this->montaFormRecursos();
		
		$param = [];
		$param['titulo'] = 'Datas';
		$param['conteudo'] = $formDatas;
		$form1 .= addCard($param);
		
		$param = [];
		$param['titulo'] = 'Recursos';
		$param['conteudo'] = $formRecursos;
		$form1 .= addCard($param);
		
		
		$turnos = [['M','Manhã'],['T','Tarde'],['N','Noite'],['I','Integral']];
		//$turnos = tabela('000022');
		$local = tabela('000023');
		$tipo = tabela('000024');
		$clientes = $this->getClientes();
		
		if(true){
			$turnosVal = '';
			$localVal = 'C';
			$tipoVal = 'E';
		}
		
		$this->addJSProjeto();
		
		$form = new form01(['geraScriptValidacaoObrigatorios'=>true]);
		$form->setBotaoCancela();
		$form->addCampo(array('id' => 'titulo'	, 'campo' => 'formAgenda[titulo]'	, 'etiqueta' => 'Titulo da Agenda'	, 'tipo' => 'T'	, 'tamanho' => '20', 'linha' => 1, 'largura' => 12	, 'linhasTA' => ''	, 'valor' => ''		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'cliente'	, 'campo' => 'formAgenda[cliente]'	, 'etiqueta' => 'Cliente'	, 'tipo' => 'A'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => '000001'	, 'lista' => $clientes	, 'validacao' => '', 'obrigatorio' => true, 'onchange' => 'callAjax();'));
		$form->addCampo(array('id' => 'projeto'	, 'campo' => 'formAgenda[projeto]'	, 'etiqueta' => 'Projeto'	, 'tipo' => 'A'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => array(array('', ''))	, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => 'turno'	, 'campo' => 'formAgenda[turno]'	, 'etiqueta' => 'Turno'		, 'tipo' => 'A'		, 'tamanho' => '20', 'linha' => 2, 'largura' => 4	, 'linhasTA' => ''	, 'valor' => $turnosVal , 'lista' => $turnos	, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'local'	, 'campo' => 'formAgenda[local]'	, 'etiqueta' => 'Local'		, 'tipo' => 'A'		, 'tamanho' => '10', 'linha' => 2, 'largura' => 8	, 'linhasTA' => ''	, 'valor' => $localVal	, 'lista' => $local		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'contato'	, 'campo' => 'formAgenda[contato]'	, 'etiqueta' => 'Contato'	, 'tipo' => 'T'		, 'tamanho' => '10', 'linha' => 3, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'tipo'	, 'campo' => 'formAgenda[tipo]'		, 'etiqueta' => 'Tipo'		, 'tipo' => 'A'		, 'tamanho' => '20', 'linha' => 3, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => $tipoVal	, 'lista' => $tipo		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => 'tarefa'	, 'campo' => 'formAgenda[tarefa]'	, 'etiqueta' => 'Tarefa'	, 'tipo' => 'TA'	, 'tamanho' => '20', 'linha' => 4, 'largura' => 12	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
		
		//$form->setEnvio(getLink().'marcar', 'formAgenda', 'formAgenda');
		
		$param = [];
		$param['titulo'] = 'Detalhes:';
		$param['conteudo'] = $form;
		$form2 = addCard($param);
		
		$param = [];
		$param['tamanhos'] = [6,6];
		$param['conteudos'][] = $form1;
		$param['conteudos'][] = $form2;
		$ret = addLinha($param);
		
		$param = [];
		$param['titulo'] = 'Agenda - Marcação Múltipla';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		$param = [];
		$param['acao'] = getLink().'marcar';
		$param['nome'] = 'formAgenda';
		$param['sendFooter'] = true;
		//$param['URLcancelar'] = $this->_URLcancelar;
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function montaFormDatas(){
		$ret = '';
		
		$param = [];
		$param['tamanhos'] = [6,6];
		$param['conteudos'][] = formbase01::formData(['nome' => 'formAgenda[dataIni]', 'etiqueta' => 'Data De']);
		$param['conteudos'][] = formbase01::formData(['nome' => 'formAgenda[dataFim]', 'etiqueta' => 'Data Até']);
		$ret .= addLinha($param);
		
		$ret .= formbase01::checkSemana('Dias de Semana');
		
		return $ret;
	}
	
	private function montaFormRecursos(){
		$ret = '';
		
		/*
		 $dados = [];
		 foreach ($this->_recursos as $rec){
		 $dados[] = ['nome' => "formAgenda[REC][{$rec['recurso']}]", 'etiqueta' => $rec['apelido']];
		 }
		 
		 if(is_array($dados) && count($dados) > 0){
		 $param = [];
		 $param['colunas'] = 3;
		 $param['combos']  = $dados;
		 $ret = formbase01::formGrupoCheckBox($param);
		 }
		 */
		$type = 'Todos';
		$descricao = 'Marcar Todos';
		$dados = [];
		
		foreach ($this->_recursos as $rec){
		    $temp = [];
		    $temp["nome"] 		= "formAgenda[REC][{$rec['recurso']}]";
		    $temp["etiqueta"] 	= $rec['apelido'];
		    $temp["modulo"] 	= $type;
		    $temp["classeadd"] 	= $type;
		    $temp["checked"]    = false;
		    $dados[] = $temp;
		}
		
		if(is_array($dados) && count($dados) > 0){
		    $param = [];
		    $param['colunas'] 	= 3;
		    $param['combos']	= $dados;
		    $formCombo = formbase01::formGrupoCheckBox($param);
		    $param = [];
		    $param['titulo'] = '<label><input type="checkbox"  onclick="marcarTodos(\''.$type.'\',this.checked);"  name="['.$type.']" id="' . $descricao . '_id"  />&nbsp;&nbsp;'.$descricao.'</label>';
		    $param['conteudo'] = $formCombo;
		    
		    $ret .= $param['titulo'] . $param['conteudo'];
		    
		    //$ret .= addCard($param).'<br><br>';
		}
		
		
		
		return $ret;
	}
	
	private function montaTabelaMarcadas($marcadas){
		$ret = '';
		
		$dados = [];
		foreach ($marcadas as $m){
			$temp = [];
			$temp['dia'] = $m['data'];
			$temp['turno'] = $m['turno'];
			$temp['nome'] = $this->_clientes[$m['cliente_agenda']];
			$temp['recurso'] = $this->_recursos[$m['recurso']]['apelido'];
			
			$dados[] = $temp;
		}
		
		$param = [];
		$param['width'] = 'AUTO';
		$param['paginacao'] = false;
		//$param['ordenacao'] = false;
		//$param['filtro'] = false;
		$param['scroll'] = false;
		//$param['info'] = false;
		$param['titulo'] = 'Agendas Marcadas';
		$tab = new tabela01($param );
		
		$tab->addColuna(array('campo' => 'dia'		, 'etiqueta' => 'Dia'		,'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Cliente'	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'turno'	, 'etiqueta' => 'Turno'		,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'recurso'	, 'etiqueta' => 'Recurso'	,'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		
		$tab->setDados($dados);
		
		$botao = [];
		$botao["onclick"]= "setLocation('" . getLink() . "index')";
		$botao["texto"]	= "Voltar";
		$botao['cor'] = 'danger';
		$tab->addBotaoTitulo($botao);
		
		$ret .= $tab;
		
		return $ret;
	}
	//----------------------------------------------------------------- GETs ------------
	
	
	private function getRecursos($recurso = ''){
		$where = '';
		if(!empty($recurso)){
			$where = " AND recurso = '$recurso'";
		}
		$sql = "SELECT * FROM sdm_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' $where ORDER BY apelido";
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
		
		$sql = "SELECT * FROM sdm_agenda WHERE data IN ($dia) $recursoWhere AND IFNULL(del,'') = '' ORDER BY recurso, data";
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
		$sql = "SELECT cod, nreduz FROM cad_organizacoes ";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$this->_clientes[$row['cod']] = $row['nreduz'];
			}
		}
	}
	
	private function getClientes(){
		$ret = array();
		
		//$ret[0][0] = "";
		//$ret[0][1] = "&nbsp;";
		
		$sql = "SELECT cod, nreduz FROM cad_organizacoes WHERE ativo = 'S' ORDER BY nreduz";
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
	
	private function addJSProjeto(){
		$ret = "function callAjax(){
	        var cliente = document.getElementById('cliente').value;
	        var option = '';
	        $.getJSON('" . getLinkAjax('getProjetosCliente', true, 'sdm_ajax','sdm_geral')  . "&cliente=' + cliente, function (dados){
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

