<?php
/*
 * Data Criacao: 29/12/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Controle de tickets
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class sdm_ticket{
	var $funcoes_publicas = array(
			'index' 		=> true,
			'incluir' 		=> true,
			'salvar'		=>true,
			'editar'		=> true,
			'addHistorico'	=> true,
	        'ajax'          => true,
	);
	
	//Classe relatorio
	private $_relatorio;
	
	//Titulo
	private $_titulo;
	
	//Programa
	private $_programa;
	
	//Tickets
	private $_tickets = [];
	
	//Atendimentos do ticket
	private $_atendimentos = [];
	
	//Nome clientes
	private $_clientes = [];
	
	//Nome Recursos
	private $_recursos = [];
	
	function __construct(){
		$this->_titulo ='Tickets';
		$this->_programa = get_class($this);
		
		if(false){
			sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Cliente'					, 'variavel' => 'CLIENTE'         ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'SDM_getClientes()'			, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Responsável/Solicitante'	, 'variavel' => 'RESPONSAVEL'     ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'SDM_getResponsavel()'		, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Status'	    			, 'variavel' => 'STATUS'          ,'tipo' => 'T', 'tamanho' => '6', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'SDM_getStatus()'			, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Prioridade'				, 'variavel' => 'PRIORIDADE'      ,'tipo' => 'T', 'tamanho' => '6', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'tabela("000009","chave")'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
		
	}
	
	function index(){
		$ret = '';
		$param = [];
		$param['programa'] 	= $this->_programa;
		$param['titulo']	= $this->_titulo;
		$this->_relatorio = new relatorio01($param);
		
		//Limpa a possibilidade de receber/incluir um tkt
		unsetAppVar($this->_programa.'_incluir_tkt');
		
		//Botão incluir Tickets
		$param = [];
		$param['onclick'] = "setLocation('".getLink()."incluir')";
		//$param['tamanho'] = 'pequeno';
		$param['cor'] = 'primary';
		$param['texto'] = 'Incluir';
		$this->_relatorio->addBotao($param);
		
		$filtro = $this->_relatorio->getFiltro();
		
		$this->getDados(0,$filtro);
		$this->montaRelatorio();
		
		$this->_relatorio->setDados($this->_tickets);
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	function incluir(){
		$ret = '';
		
		$ret .= $this->formCadastro();
		
		return $ret;
	}
	
	function editar($nrTicket = 0){
		$ret = '';
		
		if($nrTicket == 0){
			$nrTicket = base64_decode(getParam($_GET, 'id'));
		}
		
		if(!empty($nrTicket)){
			$this->getDados($nrTicket);
			if(count($this->_tickets) == 1){
				$ret .= $this->formCadastro(false,false,$this->_tickets[0]);
			}
		}
		
		if(empty($ret)){
			$ret = $this->index();
		}
		
		return $ret;
	}
	
	function salvar(){
		$ret = '';
		$permite = getAppVar($this->_programa.'_incluir_tkt');
		
		if($permite !== null){
			$ticket = [];
			$formTkt = getParam($_POST, 'formTkt');
			
			$ticket['cliente']		= $formTkt['cliente'];
			$ticket['projeto']		= $formTkt['projeto'];
			$ticket['tipo']			= $formTkt['tipo'];
			$ticket['prioridade']	= $formTkt['prioridade'];
			$ticket['titulo']		= $formTkt['titulo'];
			$ticket['apontamento']	= $formTkt['apontamento'];
			$ticket['solicitante']	= $formTkt['solicitante'];
			$ticket['tipotkt']		= $formTkt['tipotkt'];
			$ticket['status']		= $formTkt['status'];
			$ticket['responsavel']	= $formTkt['responsavel'];
			$ticket['percentual']	= $formTkt['percentual'];
			$ticket['fatura']		= $formTkt['faturado'];
			
			$ticket['tempoest']	    = $formTkt['tempoest'];
			$ticket['tempo']        = $formTkt['tempo'];
			//datas::dataD2S($formTkt['dtfim']);
			$ticket['dtiniprev']    = datas::dataD2S($formTkt['dtiniprev']);
			$ticket['dtini']        = datas::dataD2S($formTkt['dtini']);
			$ticket['dtfimprev']    = datas::dataD2S($formTkt['dtfimprev']);
			$ticket['dtfim']        = datas::dataD2S($formTkt['dtfim']);
			
			//emails
			$ticket['email']        = getUsuario('email');
			
			if(getUsuario() != $ticket['responsavel']){
				$ticket['email']   .= ';'.getUsuario('email',$ticket['responsavel']);
			}
			
			//'dtiniprev', 'dtini', 'dtfimprev', 'dtfim'
			//print_r($formTkt);
			//print_r($ticket);
			$ret = $this->gravaTicket($ticket);
			unsetAppVar($this->_programa.'_incluir_tkt');
		}
		
		if(empty($ret)){
			$ret = $this->index();
		}
		
		return $ret;
	}
	
	public function addHistorico(){
		$ret = '';
		$nrTicket = getAppVar($this->_programa.'_add_historico_ticket');
		$this->getNomeRecursos();
		$camposAlteraveis = ['tipo','prioridade', 'percentual', 'status', 'tipotkt', 'fatura', 'tempoest', 'tempo', 'dtiniprev', 'dtini', 'dtfimprev', 'dtfim', 'projeto'];
		$tabelas = ['tipo' => '000008','prioridade' => '000009', 'percentual' => '000010', 'status' => '000011', 'tipotkt' => '000012', 'fatura' => '000013'];
		$texto =  ['tipo' => 'TIPO','prioridade' => 'PRIORIDADE', 'percentual' => 'REALIZADO', 'status' => 'STATUS', 'tipotkt' => 'TICKET', 'fatura' => 'FATURAMENTO', 'projeto' => 'PROJETO'];
		$camposPodemVazios = ['projeto'];
		
		if(!empty($nrTicket)){
			$this->getDados($nrTicket);
			if(count($this->_tickets) == 1){
				$novoAtendimento = '';
				$camposTkt = [];
				$ticket = $this->_tickets[0];
				$formTkt = getParam($_POST, 'formTkt');
				$atendimento = getParam($_POST, 'formAtendimento');
				
				foreach ($camposAlteraveis as $campoAlt){
				    if($formTkt[$campoAlt] != $ticket[$campoAlt] && (!empty($formTkt[$campoAlt] || (in_array($campoAlt, $camposPodemVazios) && empty($formTkt[$campoAlt]))))){
						$de = '';
						if(!empty($ticket[$campoAlt])){
							$de = " de ".getTabelaDesc($tabelas[$campoAlt], $ticket[$campoAlt]);
						}
						$novoAtendimento .= "Alterado <b>".$texto[$campoAlt]."</b> $de para ".getTabelaDesc($tabelas[$campoAlt], $formTkt[$campoAlt])."\n";
						$camposTkt[$campoAlt] = $formTkt[$campoAlt];
						
					}
				}
				
				
				// Altera o responsavel
				if($formTkt['responsavel'] != $ticket['responsavel'] && !empty($formTkt['responsavel'])){
					$de = '';
					if(!empty($ticket['responsavel'])){
						$de = " de ".$this->_recursos[$ticket['responsavel']];
					}
					$novoAtendimento .= "<b>Alterado RESPONSÁVEL</b> $de para ".$this->_recursos[$formTkt['responsavel']]."\n";
					$camposTkt['responsavel'] = $formTkt['responsavel'];
					if(!empty($ticket['email'])){
						$ticket['email'] .= ';';
					}
					$camposTkt['email']   .= $ticket['email'].getUsuario('email',$camposTkt['responsavel']);
				}
				
				$novoAtendimento .= $atendimento;
				
				$this->gravaNovoAtendimento($ticket, $novoAtendimento, $camposTkt);
				
				redireciona(getLink().'editar&id='.base64_encode($nrTicket));
			}
		}
		
		if(empty($ret)){
			$ret = $this->index();
		}
		
		return $ret;
	}
	//----------------------------------------------------------------- UI   ------------
	
	private function formCadastro($inc = true, $alt = true, $ticket = []){
		$param = [];
		$form = new form01($param);
		
		$cliente 	= $this->getClientes();
		$tipos 		= tabela("000008","desc");
		$prioridade = tabela("000009","chave");
		$percentual = tabela("000010","chave");
		$status 	= tabela("000011","desc");
		$tipotkt	= tabela("000012","chave");
		$resp 		= $this->getRecursos();
		$fatura		= tabela("000013","desc");
		
		
		if($alt){
			//Permite alteração
			$tipo1 = 'A';
		}else{
			//Somente visualização
		}
		
		//Cliente e solicitante quando inclusão não são readonly
		$camposCliente = false;
		
		//valores padrões
		if($inc){
			$titulo 	= 'Cadastrar Ticket';
			putAppVar($this->_programa.'_incluir_tkt', true);
			$valResp 	= getUsuario();
			$valTkt 	= 'E';
			$valPrior 	= '05';
			$valStatus 	= '10';
			$valCliente = '';
			$valtipo 	= '03';
			$valSolic	= '';
			$valPercen  = '01';
			$valFat		= '';
			$valTestim	= '';
			
			$valTestim	     = '';
			$valTreal        = '';
			$valDT_ini_est   = '';
			$valDT_ini_real  = '';
			$valDT_fim_est   = '';
			$valDT_fim_real  = '';
			
			$valProjeto      = '';
		}else{
			//Alteração/impressao
			$titulo = 'Ticket Nr. '.formataNum($ticket['id'],6).' - '.$ticket['titulo'];
			
			$valResp 	= $ticket['responsavel'];
			$valTkt 	= $ticket['tipotkt'];
			$valPrior 	= $ticket['prioridade'];
			$valStatus 	= $ticket['status'];
			$valCliente = $ticket['cliente'];
			$valtipo 	= $ticket['tipo'];
			$valSolic 	= $ticket['solicitante'];
			$valPercen  = $ticket['percentual'];
			$valFat		= $ticket['fatura'];
			
			$valTestim	     = $ticket['tempoest'];
			$valTreal        = $ticket['tempo'];
			
			$valDT_ini_est   = $ticket['dtiniprev'];
			$valDT_ini_real  = $ticket['dtini'];
			
			$valDT_fim_est   = $ticket['dtfimprev'];
			$valDT_fim_real  = $ticket['dtfim'];
			
			$valProjeto      = $ticket['projeto'];
			
			
			$camposCliente = true;
		}
		$projetos   = $this->montarListaProjetos($valCliente);
		$this->addJSProjeto();
		
		
		$form->addCampo(array('id' => 'tipo'		,'campo' => 'formTkt[tipo]'			,'etiqueta' => 'Tipo'			,'tipo' => 'A'	,'linha' => 1, 'largura' => 3	,'tamanho' => '20','linhas' => '','valor' => $valtipo	,'pasta' => 0 ,'lista' => $tipos		,'validacao' => '','obrigatorio' => true));
		$form->addCampo(array('id' => 'prioridade'	,'campo' => 'formTkt[prioridade]'	,'etiqueta' => 'Prioridade'		,'tipo' => 'A'	,'linha' => 1, 'largura' => 3	,'tamanho' => '50','linhas' => '','valor' => $valPrior	,'pasta' => 0 ,'lista' => $prioridade	,'validacao' => '','obrigatorio' => true));
		$form->addCampo(array('id' => 'tipotkt'		,'campo' => 'formTkt[tipotkt]'		,'etiqueta' => 'Ticket'			,'tipo' => 'A'	,'linha' => 1, 'largura' => 3	,'tamanho' => '50','linhas' => '','valor' => $valTkt	,'pasta' => 0 ,'lista' => $tipotkt		,'validacao' => '','obrigatorio' => true));
		$form->addCampo(array('id' => 'status'		,'campo' => 'formTkt[status]'		,'etiqueta' => 'Status'			,'tipo' => 'A'	,'linha' => 1, 'largura' => 3	,'tamanho' => '20','linhas' => '','valor' => $valStatus	,'pasta' => 0 ,'lista' => $status		,'validacao' => '','obrigatorio' => true));
		$form->addCampo(array('id' => 'cliente'		,'campo' => 'formTkt[cliente]'		,'etiqueta' => 'Cliente'		,'tipo' => 'A'	,'linha' => 1, 'largura' => 7	,'tamanho' => '60','linhas' => '','valor' => $valCliente,'pasta' => 0 ,'lista' => $cliente		,'validacao' => '','obrigatorio' => true, 'readonly' => $camposCliente, 'onchange' => 'callAjax();'));
		$form->addCampo(array('id' => 'projeto'		,'campo' => 'formTkt[projeto]'		,'etiqueta' => 'Projeto'		,'tipo' => 'A'	,'linha' => 1, 'largura' => 5	,'tamanho' => '60','linhas' => '','valor' => $valProjeto,'pasta' => 0 ,'lista' => $projetos		,'validacao' => '','obrigatorio' => false));
		
		$help = 'Nome do recurso do cliente que solicitou o ticket.';
		$form->addCampo(array('id' => 'solicitante'	,'campo' => 'formTkt[solicitante]'	,'etiqueta' => 'Solicitante'	,'tipo' => 'T'	,'linha' => 1, 'largura' => 5	,'tamanho' => '50','linhas' => '','valor' => $valSolic	,'pasta' => 0 ,'lista' => ''			,'validacao' => '','obrigatorio' => true, 'readonly' => $camposCliente, 'help' => $help));
		$form->addCampo(array('id' => 'responsavel'	,'campo' => 'formTkt[responsavel]'	,'etiqueta' => 'Responsável'	,'tipo' => 'A'	,'linha' => 1, 'largura' => 3	,'tamanho' => '15','linhas' => '','valor' => $valResp	,'pasta' => 0 ,'lista' => $resp			,'validacao' => '','obrigatorio' => false));
		
		$form->addCampo(array('id' => 'percentual'	,'campo' => 'formTkt[percentual]'	,'etiqueta' => 'Percentual'		,'tipo' => 'A'	,'linha' => 1, 'largura' => 3	,'tamanho' => '20','linhas' => '','valor' => $valPercen	,'pasta' => 0 ,'lista' => $percentual	,'validacao' => '','obrigatorio' => true));
		$form->addCampo(array('id' => 'faturado'	,'campo' => 'formTkt[fatura]'		,'etiqueta' => 'Faturamento'	,'tipo' => 'A'	,'linha' => 1, 'largura' => 3	,'tamanho' => '15','linhas' => '','valor' => $valFat	,'pasta' => 0 ,'lista' => $fatura		,'validacao' => '','obrigatorio' => true));
		
		$help = 'Tempo estimado em horas para a solução do ticket.';
		$form->addCampo(array('id' => 'tempoest'	,'campo' => 'formTkt[tempoest]'		,'etiqueta' => 'Tempo Estimado'	            ,'tipo' => 'T'	,'linha' => 1, 'largura' => 3	,'tamanho' => '2', 'linhas' => '','valor' => $valTestim	,'pasta' => 1 ,'lista' => ''			,'validacao' => '','obrigatorio' => false, 'help' => $help,'mascara'=>'hora','linha' => 1));
		$form->addCampo(array('id' => 'tempo'		,'campo' => 'formTkt[tempo]'		,'etiqueta' => 'Tempo Decorrido'            ,'tipo' => 'T'	,'linha' => 1, 'largura' => 3	,'tamanho' => '2', 'linhas' => '','valor' => $valTreal	,'pasta' => 1 ,'lista' => ''			,'validacao' => '','obrigatorio' => false,'mascara'=>'hora','linha' => 1));
		$form->addCampo(array('id' => 'dtiniprev'	,'campo' => 'formTkt[dtiniprev]'	,'etiqueta' => 'Data de início Prevista'	,'tipo' => 'D'	,'linha' => 1, 'largura' => 3	,'tamanho' => '10', 'linhas' => '','valor' => $valDT_ini_est	,'pasta' => 1 ,'lista' => ''			,'validacao' => '','obrigatorio' => false,'linha' => 2));
		$form->addCampo(array('id' => 'dtini'		,'campo' => 'formTkt[dtini]'		,'etiqueta' => 'Data de início'	            ,'tipo' => 'D'	,'linha' => 1, 'largura' => 3	,'tamanho' => '10', 'linhas' => '','valor' => $valDT_ini_real	,'pasta' => 1 ,'lista' => ''			,'validacao' => '','obrigatorio' => false,'linha' => 2));
		$form->addCampo(array('id' => 'dtfimprev'	,'campo' => 'formTkt[dtfimprev]'	,'etiqueta' => 'Data de Termino Prevista'	,'tipo' => 'D'	,'linha' => 1, 'largura' => 3	,'tamanho' => '10', 'linhas' => '','valor' => $valDT_fim_est	,'pasta' => 1 ,'lista' => ''			,'validacao' => '','obrigatorio' => false,'linha' => 3));
		$form->addCampo(array('id' => 'dtfim'		,'campo' => 'formTkt[dtfim]'		,'etiqueta' => 'Data de Termino'	        ,'tipo' => 'D'	,'linha' => 1, 'largura' => 3	,'tamanho' => '10', 'linhas' => '','valor' => $valDT_fim_real	,'pasta' => 1 ,'lista' => ''			,'validacao' => '','obrigatorio' => false,'linha' => 3));
		
		$form->setPastas(array(0 => 'Principal', 1 => 'Tempo'));
		
		if($inc){
			//So aparece ser for inclusão
			$help = 'Resumo do problema ou solicitação do cliente.';
			$form->addCampo(array('id' => 'titulo'		,'campo' => 'formTkt[titulo]'		,'etiqueta' => 'Título'			,'tipo' => 'T'	,'linha' => 1, 'largura' => 6	,'tamanho' => '100','linhas' => '','valor' => ''			,'pasta' => 0 ,'lista' => ''			,'validacao' => '','obrigatorio' => true, 'help' => $help));
			$help = 'Descrição do problema ou solicitação do cliente.';
			$form->addCampo(array('id' => 'descricao'	,'campo' => 'formTkt[apontamento]'	,'etiqueta' => 'Descrição'		,'tipo' => 'TA'	,'linha' => 1, 'largura' => 6	,'tamanho' => '20','linhasTA' => '10','valor' => ''			,'pasta' => 0 ,'lista' => ''			,'validacao' => '','obrigatorio' => true, 'help' => $help));
			
			$form->setEnvio(getLink() . 'salvar', 'formPrograma', 'formPrograma');
		}
		
		$param = [];
		$p = [];
		$p['onclick'] 	= "setLocation('".getLink()."index')";
		//$p['tamanho'] 	= 'pequeno';
		$p['cor'] 		= 'danger';
		$p['texto'] 	= 'Cancelar';
		$param['botoesTitulo'][] = $p;
		
		$param['titulo'] = $titulo;
		$param['conteudo'] = $form;
		$ret = addCard($param);
		
		if(!$inc){
			$this->getDadosItens($ticket['id']);
			$ret .= $this->getTabelaAtendimentos($ticket);
			
			$param = [];
			$param['acao'] = getLink().'addHistorico';
			$param['nome'] = 'adicionaHistorico';
			$ret = formbase01::form($param, $ret);
		}
		
		return $ret;
	}
	
	private function getTimelineAtendimento(){
		$ret = '';
		
		if(count($this->_atendimentos) > 0){
			$param = [];
			foreach ($this->_atendimentos as $atendimento){
				$paramPai = [];
				$paramPai['titulo'] = 'Seq.'.$atendimento['seq'].' - '.$atendimento['data'];
				$paramPai['cor'] = 'bg-green';
				
				$filho = [];
				//$filho['titulo'] = 'Apontamento: ';
				//$filho['titSub'] = $atendimento['nometecnico'];
				//$filho['hora'] = $atendimento['horaChegada'];
				$filho['conteudo'] = $atendimento['apontamento'];
				$filho['icone'] = 'fa-user';
				$filho['iconeCor'] = 'bg-aqua';
				$paramPai['filho'][] = $filho;
				
				$filho = [];
				$filho['titulo'] = 'Problema: ';
				//$filho['titSub'] = $atendimento['problema'];
				//$filho['hora'] = $historico['horaChegada'];
				$filho['icone'] = 'fa-times-circle';
				$filho['iconeCor'] = 'bg-red';
				//$paramPai['filho'][] = $filho;
				
				$param['pai'][] = $paramPai;
			}
			
			$ret = addTimeline($param);
		}
		
		return $ret;
	}
	
	private function getTabelaAtendimentos($ticket){
		global $nl;
		$ret = '';
		
		$historico = $this->getTimelineAtendimento();
		$param = [];
		$param['titulo'] = 'Histórico';
		$param['conteudo'] = $historico;
		$historico = addCard($param);
		
		$incluir = $this->formAdicionarHistorico();
		$param = [];
		$param['titulo'] = 'Adicionar Histórico';
		$param['conteudo'] = $incluir;
		$incluir = addCard($param);
		
		$anexos = '';
		$param = [];
		$param['titulo'] = 'Anexos';
		$param['conteudo'] = $anexos;
		$anexos = addCard($param);
		
		$emails = $this->getListaEmailChamado($ticket);
		$param = [];
		$param['titulo'] = 'Emails';
		$param['conteudo'] = $emails;
		$emails = addCard($param);
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-8">'.$historico.'</div>'.$nl;
		$ret .= '	<div  class="col-md-4">'.$incluir.$anexos.$emails.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		putAppVar($this->_programa.'_add_historico_ticket', $ticket['id']);
		
		return $ret;
	}
	
	private function getListaEmailChamado($ticket){
		$ret = '';
		
		$ret = str_replace(';', "<br>\n", $ticket['email']);
		
		return $ret;
	}
	
	private function formAdicionarHistorico(){
		$ret = '';
		
		$param = [];
		$param['nome'] = 'formAtendimento';
		//$param['etiqueta'] = 'Produtos a Precificar';
		$ret .= formbase01::formTextArea($param);
		$param = [];
		$param['onclick'] = "$('#adicionaHistorico').submit();";
		$ret .= formbase01::formSend($param);
		
		return $ret;
	}
	
	private function montaRelatorio(){
		$this->_relatorio->setNowrap(false);
		$this->_relatorio->addColuna(array('campo' => 'edita'			, 'etiqueta' => '&nbsp;'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'id'				, 'etiqueta' => 'Ticket'		, 'tipo' => 'T', 'width' => 120, 'posicao' => 'C'));
		//		$this->_relatorio->addColuna(array('campo' => 'tipotktDesc'		, 'etiqueta' => 'Tipo'			, 'tipo' => 'T', 'width' => 120, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'clienteDesc'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'tipoDesc'		, 'etiqueta' => 'Tipo'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'titulo'			, 'etiqueta' => 'Título'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'statusDesc'		, 'etiqueta' => 'Status'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'prioridadeDesc'	, 'etiqueta' => 'Prioridade'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'responsavelDesc'	, 'etiqueta' => 'Responsável'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'data'			, 'etiqueta' => 'Abertura'		, 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'percentualDesc'	, 'etiqueta' => 'Realizado'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'solicitante'		, 'etiqueta' => 'Solicitante'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
	}
	
	private function montaCorpoEmail($ticket){
		$ret = '';
		
		$this->getDadosItens($ticket['id']);
		
		$param = [];
		//$param['colunas'] = '';
		$param['corBorda'] = '#000000';
		$param['corFundoTitulo'] = '#3c8dbc';
		$param['corFonteTitulo'] = '#FFF';
		$param['alinhamentoTitulo'] = 'center';
		//$param['fonte'] = 'font-family: Verdana, Geneva, sans-serif;';
		//$param['tamanhoTD'] = false;
		$tabela = new tabela_gmail01($param);
		$tabela->abreTabela(1200);
		
		$link = '<a href="'.getLink(true).'editar&id='.base64_encode($ticket['id']).'" target="_blank" style="color: #FFFFFF; font-size:14px; font-weight: bold;">Ticket '.$ticket['id'].'</a>';
		$tabela->abreTR(true);
		$tabela->abreTH($link,12);
		$tabela->fechaTR();
		
		
		$tabela->abreTR(true);
		$tabela->abreTH($ticket['titulo'],8);
		$tabela->abreTH($ticket['clienteDesc'],4);
		$tabela->fechaTR();
		
		
		$tabela->abreTR(true);
		$tabela->abreTH('Seq',1);
		$tabela->abreTH('Data',1);
		$tabela->abreTH('Apontamento',8);
		$tabela->abreTH('Usuário',2);
		$tabela->fechaTR();
		
		
		if(count($this->_atendimentos) > 0){
			$param = [];
			foreach ($this->_atendimentos as $atendimento){
				$tabela->abreTR();
					$tabela->abreTD($atendimento['seq']			, 1, 'C');
					$tabela->abreTD($atendimento['data']		, 1, 'C');
					$tabela->abreTD($atendimento['apontamento']	, 8, 'E');
					$tabela->abreTD(getUsuario('nome',$atendimento['user']), 2, 'E');
				$tabela->fechaTR();
			}
		}
		
		$tabela->fechaTabela();
		
		$ret .= $tabela;
		
		return $ret;
	}
	//----------------------------------------------------------------- GETs ------------
	
	private function getDados($nrTicket = 0, $filtro = []){
		$this->getNomeClientes();
		$this->getNomeRecursos();
		$this->_tickets = [];
		$where = '';
		
		if(isset($filtro['CLIENTE']) && !empty($filtro['CLIENTE'])){
			$where .= " AND cliente = '".$filtro['CLIENTE']."'";
		}
		
		if(isset($filtro['RESPONSAVEL']) && !empty($filtro['RESPONSAVEL'])){
			$where .= " AND (responsavel = '".$filtro['RESPONSAVEL']."' OR inc_user  = '".$filtro['RESPONSAVEL']."')";
		}
		
		if(isset($filtro['PRIORIDADE']) && !empty($filtro['PRIORIDADE'])){
			$where .= " AND prioridade = '".$filtro['PRIORIDADE']."'";
		}
		
		if(isset($filtro['STATUS']) && !empty($filtro['STATUS'])){
			if($filtro['STATUS'] == 'A'){
				$where .= " AND status NOT IN ('02','03','04')";
			}elseif ($filtro['STATUS'] == 'F'){
				$where .= " AND status IN ('02','03','04')";
			}else{
				$where .= " AND status = '".$filtro['STATUS']."'";
			}
			
		}
		
		if($nrTicket > 0){
			$nrTicket = (int)$nrTicket;
			$where .= " AND id = $nrTicket";
		}
		
		$sql = "SELECT * FROM sdm_tkt WHERE IFNULL(del,'') = '' $where ORDER BY id";
		$rows = query($sql);
//echo "$sql <br>\n";
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				
				$resp 		= $this->getRecursos();
				
				$temp['id'			] 	= formataNum($row['id'], 6);
				$temp['tipotkt'		] 	= $row['tipotkt'	];
				$temp['tipotktDesc'	] 	= getTabelaDesc('000012',$row['tipotkt']);
				$temp['cliente'		] 	= $row['cliente'	];
				$temp['projeto'     ]   = $row['projeto'    ];
				$temp['projeto_desc']   = $this->getnomeProjeto($row['projeto']);
				$temp['clienteDesc'	] 	= $this->_clientes[$row['cliente']];
				$temp['tipo'		] 	= $row['tipo'];
				$temp['tipoDesc'	] 	= getTabelaDesc('000008',$row['tipo']);
				$temp['titulo'		] 	= $row['titulo'	];
				$temp['status'		] 	= $row['status'];
				$temp['statusDesc'	] 	= getTabelaDesc('000011',$row['status']);
				$temp['prioridade'	] 	= $row['prioridade'];
				$temp['prioridadeDesc'] = getTabelaDesc('000009',$row['prioridade']);
				$temp['responsavel'	] 	= $row['responsavel'];
				$temp['responsavelDesc']= $this->_recursos[$row['responsavel']];
				$temp['data'		] 	= $row['data'		];
				$temp['percentual'	] 	= $row['percentual'];
				$temp['percentualDesc'] = getTabelaDesc('000010',$row['percentual']);
				$temp['solicitante'	] 	= $row['solicitante'];
				$temp['fatura'		] 	= $row['fatura'];
				$temp['faturaDesc'	] 	= getTabelaDesc('000013',$row['fatura']);
				
				$temp['tempoest'	] 	= $row['tempoest'];
				$temp['tempo'	    ] 	= $row['tempo'];
				$temp['dtiniprev'	] 	= datas::dataS2D($row['dtiniprev']);
				$temp['dtini'	    ] 	= datas::dataS2D($row['dtini']);
				$temp['dtfimprev'	] 	= datas::dataS2D($row['dtfimprev']);
				$temp['dtfim'	    ] 	= datas::dataS2D($row['dtfim']);
				
				if($nrTicket > 0){
					$temp['email'	] 	= $row['email'];
				}
				
				$temp['edita'		] 	= '<a href="'.getLink().'editar&id='.base64_encode($temp['id']).'" class="btn btn-xs btn-success" role="button" id="Editar">Editar</a>';
				
				$this->_tickets[] = $temp;
			}
		}
	}
	
	private function getDadosItens($nrTicket){

		$this->_atendimentos = [];
		
		if($nrTicket > 0){
			$sql = "SELECT * FROM sdm_tkt_item WHERE IFNULL(del,'') = '' AND ticket = $nrTicket ORDER BY time_inc DESC";
			$rows = query($sql);
			
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$temp = [];
					
					$temp['seq'			] = formataNum($row['seq'], 3);
					$temp['ticket'		] = $row['ticket'	];
					$temp['tipo'		] = $row['tipo'];
					$temp['status'		] = $row['status'];
					$temp['prioridade'	] = $row['prioridade'];
					$temp['responsavel'	] = $row['responsavel'];
					$temp['percentual'	] = $row['percentual'];
					$temp['data'		] = datas::dataS2D($row['data'], 2).' - '.$row['hora'];
					$temp['dia'			] = $row['data'		];
					$temp['hora'		] = $row['hora'		];
					$temp['user'		] = $row['user'];
					$temp['apontamento'	] = nl2br($row['apontamento']);
					
					$this->_atendimentos[] = $temp;
				}
			}
		}
	}
	
	
	private function getClientes(){
		$ret = [];
		
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
	
	private function getNomeClientes(){
		$sql = "SELECT cod, nreduz FROM cad_clientes";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$this->_clientes[$row['cod']] = $row['nreduz'];
			}
		}
	}
	
	private function getRecursos($campo = 'apelido'){
		$ret = [];
		
		if($campo != 'apelido'){
			$campo = 'nome';
		}
		
		$ret[0][0] = "";
		$ret[0][1] = "&nbsp;";
		
		$sql = "SELECT usuario, nome, apelido FROM cad_recursos WHERE ativo = 'S' ORDER BY $campo";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$temp[0] = $row['usuario'];
				$temp[1] = $row[$campo];
				
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	private function getNomeRecursos(){
		$sql = "SELECT usuario, apelido FROM cad_recursos";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$this->_recursos[$row['usuario']] = $row['apelido'];
			}
		}
	}
	
	private function getNumTicket($ticket, $diaAbertura, $horaAbertura){
		$ret = 0;
		
		$sql = "SELECT * FROM sdm_tkt WHERE cliente = '".$ticket['cliente']."' AND data = '$diaAbertura' AND hora = '$horaAbertura'";
		$rows = query($sql);
		
		if(isset($rows[0]['id'])){
			$ret = $rows[0]['id'];
		}
		
		return $ret;
	}
	//----------------------------------------------------------------------------- VO ------------------------------------------------------------
	
	private function gravaNovoAtendimento($ticket, $novoAtendimento, $camposTkt){
		if(!empty($novoAtendimento)){
			$atendiento = [];
			
			$atendiento['ticket'] 		= $ticket['id'];
			$atendiento['tipo'] 		= isset($camposTkt['tipo']) ? $camposTkt['tipo'] : $ticket['tipo'];
			$atendiento['status'] 		= $ticket['status'];
			$atendiento['prioridade'] 	= $ticket['prioridade'];
			$atendiento['responsavel'] 	= $ticket['responsavel'];
			$atendiento['percentual'] 	= $ticket['percentual'];
			$atendiento['apontamento'] 	= $novoAtendimento;
			
			$this->gravaAtendimento($atendiento);
			
			$verificaCamposAlterados = ['email','tipo','tipotkt','responsavel','status','tempoest','tempo','percentual'];
			
			$campos = [];
			
			foreach ($verificaCamposAlterados as $ca){
				if(isset($camposTkt[$ca])){
					$campos[$ca] = $camposTkt[$ca];
				}
			}
			
			if(isset($camposTkt['dtiniprev'])){
				$campos['dtiniprev'] = datas::dataD2S($camposTkt['dtiniprev']);
			}
			if(isset($camposTkt['dtini'])){
				$campos['dtini'] = datas::dataD2S($camposTkt['dtini']);
			}
			
			if(isset($camposTkt['dtfimprev'])){
				$campos['dtfimprev'] = datas::dataD2S($camposTkt['dtfimprev']);
			}
			if(isset($camposTkt['dtfim'])){
				$campos['dtfim'] = datas::dataD2S($camposTkt['dtfim']);
			}
			if(isset($camposTkt['projeto'])){
			    $campos['projeto'] = $camposTkt['projeto'];
			}
			
			if(count($campos) > 0){
				$sql = montaSQL($campos, 'sdm_tkt', 'UPDATE', " id = ".$ticket['id']);
				query($sql);
			}
			
			$this->enviaEmail($ticket['id']);
		}
	}
	
	private function gravaTicket($ticket){
		$ret = '';
		
		$diaAbertura = date('Ymd');
		$horaAbertura = date("H:i");
		
		$campos = [];
		
		$campos['cliente'] 		= $ticket['cliente'];
		$campos['projeto']      = $ticket['projeto'];
		$campos['tipo'] 		= $ticket['tipo'];
		$campos['prioridade'] 	= $ticket['prioridade'];
		$campos['titulo'] 		= escapeQuery($ticket['titulo']);
		$campos['status'] 		= $ticket['status'];
		$campos['responsavel']	= $ticket['responsavel'];
		$campos['data'] 		= $diaAbertura;
		$campos['hora'] 		= $horaAbertura;
		$campos['percentual'] 	= $ticket['percentual'];
		$campos['solicitante']	= $ticket['solicitante'];
		$campos['tipotkt']		= $ticket['tipotkt'];
		$campos['fatura'] 		= $ticket['fatura'];
		$campos['inc_user'] 	= getUsuario();
		
		$campos['email'] 		= $ticket['email'];
		
		$campos['dtiniprev'] 	= isset($ticket['dtiniprev']) ? $ticket['dtiniprev'] : '';
		$campos['dtfimprev'] 	= isset($ticket['dtfimprev']) ? $ticket['dtfimprev'] : '';
		
		$campos['tempoest']	    = $ticket['tempoest'];
		$campos['tempo']        = $ticket['tempo'];
		$campos['dtiniprev']    = $ticket['dtiniprev'];
		$campos['dtini']        = $ticket['dtini'];
		$campos['dtfimprev']    = $ticket['dtfimprev'];
		$campos['dtfim']        = $ticket['dtfim'];
		
		$sql = montaSQL($campos, 'sdm_tkt');
		query($sql);
		
		$nrTicket = $this->getNumTicket($ticket, $diaAbertura, $horaAbertura);
		
		if($nrTicket > 0){
			$atendiento = [];
			
			$atendiento['ticket'] 		= $nrTicket;
			$atendiento['tipo'] 		= $ticket['tipo'];
			$atendiento['status'] 		= $ticket['status'];
			$atendiento['prioridade'] 	= $ticket['prioridade'];
			$atendiento['responsavel'] 	= $ticket['responsavel'];
			$atendiento['percentual'] 	= $ticket['percentual'];
			$atendiento['apontamento'] 	= $ticket['apontamento'];
			
			$this->gravaAtendimento($atendiento);
			
			$ret = $this->editar($nrTicket);
			
			$this->enviaEmail($nrTicket);
		}else{
			addPortalMensagem('Erro', 'Algo sinistro aconteceu, o ticket não foi salvo, por favor tente novamente!', 'erro');
			$ret = $this->incluir($ticket);
		}
		
		return $ret;
	}
	
	private function gravaAtendimento($atendimento){
		$nrTicket = isset($atendimento['ticket']) ? $atendimento['ticket'] : 0;
		
		if($nrTicket > 0){
			$seq = getProximoNumero("sdm_tkt_item", "seq", " ticket = $nrTicket",0,false,false);
			
			$data = isset($atendimento['data']) && !empty($atendimento['data']) ? $atendimento['data'] : date('Ymd');
			$hora = isset($atendimento['hora']) && !empty($atendimento['hora']) ? $atendimento['hora'] : date('H:i');
			$usuario = getUsuario();
			
			$atendimento['apontamento'] = escapeQuery($atendimento['apontamento']);
			
			$campos = [];
			$campos['ticket'] 		= $nrTicket;
			$campos['seq'] 			= $seq;
			$campos['tipo'] 		= $atendimento['tipo'];
			$campos['status'] 		= $atendimento['status'];
			$campos['prioridade'] 	= $atendimento['prioridade'];
			$campos['responsavel']	= $atendimento['responsavel'];
			$campos['percentual'] 	= $atendimento['percentual'];
			$campos['apontamento'] 	= $atendimento['apontamento'];
			$campos['data'] 		= $data;
			$campos['hora'] 		= $hora;
			$campos['user'] 		= $usuario;
			
			$sql = montaSQL($campos, 'sdm_tkt_item');
			query($sql);
		}
	}
	//----------------------------------------------------------------------------- UTEIS ------------------------------------------------------------
	
	private function enviaEmail($nrTicket){
		if($nrTicket > 0){
			$this->getDados($nrTicket);
			$ticket = $this->_tickets[0];
			
			$para = $ticket['email'];
			$titulo = 'Ticket '.$ticket['id'].' - Inclusão/Alteração';
			
			$corpo = $this->montaCorpoEmail($ticket);
			
			enviaEmailAntigo($para, $titulo, $corpo);
		}
	}
	
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
	
	private function montarListaProjetos($cliente = ''){
	    $ret = [];
	    $ret[] = array('', '');
	    
	    if($cliente != ''){
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
}


function SDM_getClientes(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT cod, nreduz FROM cad_clientes WHERE 1 = 1 ORDER BY nreduz";
	$rows = query($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['cod'];
			$temp[1] = $row['nreduz'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}
function SDM_getResponsavel(){
	$ret = [];
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT usuario, apelido FROM cad_recursos WHERE 1 = 1 ORDER BY apelido";
	$rows = query($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['usuario'];
			$temp[1] = $row['apelido'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}
function SDM_getStatus(){
	$ret = [];
	$status 	= tabela("000011","desc", false);
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$ret[1][0] = "A";
	$ret[1][1] = "Abertos";
	
	$ret[2][0] = "F";
	$ret[2][1] = "Fechados";
	
	$ret = $ret + $status;
	
	return $ret;
}
