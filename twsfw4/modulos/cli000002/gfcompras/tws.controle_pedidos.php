<?php
/*
 * Data Criacao: 13/09/2022
 * Autor: Alexandre Thiel - thiel@thielws.com.br
 *
 * Resumo: Painel de acompanhamento de envio de pedidos de fornecedores
 */
/*
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
*/
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class controle_pedidos{
	var $funcoes_publicas = array(
			'index' 		=> true,
			'marcar' 		=> true,
			'reenviar'		=> true,
			'enviar'		=> true,
			'editaModelo'	=> true,
	);
	
	// Nome do Programa
	private $_programa = '';
	
	//Titulo
	private $_titulo = '';
	
	// Classe relatorio
	private $_relatorio;
	
	//Filtro
	private $_filtro;
	
	//sys020
	private $_sys020;
	
	//Indica se é teste
	private $_teste;
	
	//Nome fornecedores
	private $_fornecedores = [];
	
	
	//Nome compradores
	private $_compradores = [];
	
	public function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = get_class($this);
		$this->_titulo = 'Controle de Pedidos';
		$this->_sys020 = new sys020();
		
		$param = [];
		$param['tamanho'] 	= 12;
		$param['colunas'] 	= 3;
		$param['titulo'] 	= 'Filtro';
		$this->_filtro = new formfiltro01($this->_programa, $param);
		$this->_filtro->setLink(getLink().'index');

		
		$botao = array();
		$botao['onclick']= 'setLocation(\''.getLink().'editaModelo\')';
		$botao['texto']	= 'Edita Modelo Email';
		$botao['id'] = 'btConfigurar';
		$botao['icone'] = 'fa-cog';
		$this->_filtro->addBotaoTitulo($botao);
		
		if(false){
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Comprador'		 , 'variavel' => 'COMPRADOR', 'tipo' => 'T', 'tamanho' => '8', 'funcaodados' => 'CP_getComprador()'	, 'help' => '', 'opcoes' => '']);
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Fornecedor'		 , 'variavel' => 'FORNEC'	, 'tipo' => 'T', 'tamanho' => '8', 'funcaodados' => 'CP_getFornec()'	, 'help' => '', 'opcoes' => '']);
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Status'			 , 'variavel' => 'STATUS'	, 'tipo' => 'T', 'tamanho' => '8', 'funcaodados' => ''					, 'help' => '', 'opcoes' => '=;T=Não recebido;N=Não enviado;E=Enviado;R=Recebido']);
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Data de'			 , 'variavel' => 'DATAINI'	, 'tipo' => 'D', 'tamanho' => '8', 'funcaodados' => ''					, 'help' => '', 'opcoes' => '']);
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '5', 'pergunta' => 'Data ate'		 , 'variavel' => 'DATAFIM'	, 'tipo' => 'D', 'tamanho' => '8', 'funcaodados' => ''					, 'help' => '', 'opcoes' => '']);
		    sys004::inclui(['programa' => $this->_programa, 'ordem' => '6', 'pergunta' => 'Enviar Fornecedor', 'variavel' => 'ENVIA'	, 'tipo' => 'T', 'tamanho' => '8', 'funcaodados' => ''					, 'help' => '', 'opcoes' => 'S=Sim;N=Não']);
		}
		
		if(false){
			$param['programa'] = $this->_programa;
			$param['parametro'] = 'MODELO_EMAIL';
			$param['tipo'] = 'ED';
			$param['config'] = '';
			$param['descricao'] = 'Modelo de email';
			$param['valor'] = '';
			$this->_sys020->inclui($param);
		}
	}
	
	
	public function index(){
		$ret = '';
		$operacao = getOperacao();
		
		if($operacao == 'sysParametrosGravar'){
			$this->_sys020->gravaFormulario($this->_programa);
			addPortalMensagem('', 'Configurações alteradas com sucesso!');
		}
				
		$ret .= $this->_filtro;
		
		$filtro = $this->_filtro->getFiltro();
		$status = isset($filtro['STATUS']) ? $filtro['STATUS'] : '';
		$comprador = isset($filtro['COMPRADOR']) ? $filtro['COMPRADOR'] : '';
		$dataIni = isset($filtro['DATAINI']) ? $filtro['DATAINI'] : datas::getDataDias();
		$dataFim = isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] :  datas::getDataDias(-90);
		$fornecedor = isset($filtro['FORNEC']) ? $filtro['FORNEC'] : '';
		$enviar_fornecedor = isset($filtro['ENVIA'] ) ? $filtro['ENVIA']  : '';
		putAppVar('envia_email_fornecedor', $enviar_fornecedor);

		$ret .= $this->getPainel($status, $dataIni, $dataFim, $comprador, $fornecedor, $enviar_fornecedor);
		
		return $ret;
	}
	
	public function marcar(){
		$ret = '';
		
		$pedido = $_GET['pedido'];
		if(!empty($pedido)){
			$pedido = intval(base64_decode($pedido));
			$sql = "SELECT status FROM gf_sit_pedido WHERE pedido = $pedido";
			$rows = query($sql);
			
			if(isset($rows[0]['status'])){
				$sql = "UPDATE gf_sit_pedido SET status='E' WHERE pedido = $pedido";
				query($sql);
				
				$campos = [];
				$campos['pedido'] = $pedido;
				$campos['usuario'] = getUsuario();
				$campos['historico'] = 'Pedido marcado como já enviado!';
				$sql = montaSQL($campos, 'gf_sit_pedido_hist');
				query($sql);
				
				addPortalMensagem('Atenção!', "Pedido $pedido alterado para 'Já Enviado'.");
			}else{
				addPortalMensagem('Atenção!', "Pedido $pedido não encontrado!", 'erro');
			}
		}
		$ret = $this->index();
		
		return $ret;
	}
	
	public function enviar(){
		$ret = '';
		
		$pedido = $_GET['pedido'];
		if(!empty($pedido)){
			$pedido = intval(base64_decode($pedido));
			$sql = "SELECT status FROM gf_sit_pedido WHERE pedido = $pedido";
			$rows = query($sql);
			
			if(isset($rows[0]['status'])){
				$enviado = $this->enviaEmail($pedido);
				
				if($enviado['envio'] && !empty($ret['email_fornec'])){
					
					$sql = "UPDATE gf_sit_pedido SET status='E' WHERE pedido = $pedido";
					query($sql);
				
					$campos = [];
					$campos['pedido'] = $pedido;
					$campos['usuario'] = getUsuario();
					$campos['historico'] = 'Pedido enviado para o fornecedor! ('.$enviado['email_comprador'].''.$enviado['email_fornec'].')';
					$sql = montaSQL($campos, 'gf_sit_pedido_hist');
					query($sql);
					
					addPortalMensagem('Atenção!', "Pedido $pedido: enviado email para o fornecedor (".$enviado['email_fornec'].").");
				}elseif($enviado['envio']){
					addPortalMensagem('Atenção!', "Pedido $pedido: enviado email para o comprador (".$enviado['email_comprador'].").");
				}else{
					addPortalMensagem('Atenção!', "Erro ao enviar email do pedido $pedido, favor tentar mais tarde ou entrar em contato com a equipe de TI!", 'erro');
				}
			}else{
				addPortalMensagem('Atenção!', "Pedido $pedido não encontrado!", 'erro');
			}
		}
		$ret = $this->index();
		
		return $ret;
	}
	
	public function reenviar(){
		$ret = '';
		
		$pedido = $_GET['pedido'];
		if(!empty($pedido)){
			$pedido = intval(base64_decode($pedido));
			$sql = "SELECT status FROM gf_sit_pedido WHERE pedido = $pedido";
			$rows = query($sql);
			
			if(isset($rows[0]['status'])){
				$enviado = $this->enviaEmail($pedido);
				
				if($enviado['envio']){
					$campos = [];
					$campos['pedido'] = $pedido;
					$campos['usuario'] = getUsuario();
					$campos['historico'] = 'Pedido re-enviado para o fornecedor ('.$enviado['email_comprador'].''.$enviado['email_fornec'].')';
					$sql = montaSQL($campos, 'gf_sit_pedido_hist');
					query($sql);
					
					addPortalMensagem('Atenção!', "Pedido $pedido: reenviado email para o fornecedor (".$enviado['email_fornec'].")");
				}else{
					addPortalMensagem('Atenção!', "Erro ao enviar email do pedido $pedido, favor tentar mais tarde ou entrar em contato com a equipe de TI!", 'erro');
				}
			}else{
				addPortalMensagem('Atenção!', "Pedido $pedido não encontrado!", 'erro');
			}
		}
		$ret = $this->index();
		
		return $ret;
	}
	
	public function editaModelo(){
		$ret = '';

		$ret .= $this->_sys020->formulario($this->_programa, $this->_titulo);
	
		return $ret;
	}
	
	public function schedule($param){
		
	}
	
	// --------------------------------------------------------- ENVIA EMAIL --------------------------------
	private function enviaEmail($pedido){
		$ret = [];
		$dados = $this->getDadosPedido($pedido);
//print_r($dados);
        $param = array(
            'programa' => $this->_programa,
            'titulo' => 'Gauchafarma - Pedido '.$pedido.' - '.$dados['fornecedor'],
        );
		//$relatorio = new relatorio03($this->_programa, 'Gauchafarma - Pedido '.$pedido.' - '.$dados['fornecedor']);
        $relatorio = new relatorio01($param);
		$nomeArq = $dados['fornecedor'].'_'.$pedido;
		$nomeArq = str_replace(' ', '_', $nomeArq);
		$relatorio->setToExcel(true, $nomeArq);
		$relatorio->setToPDF(true, $nomeArq);
		$relatorio->setEnviaTabelaEmail(false);
		
	    $cabecalho = $this->monta_cabecalho($pedido, $dados);
	    $relatorio->setHeaderPdf($cabecalho, 60);
	    
	    $dados_cabecalho_excel = $this->montarDadosCabecalhoExcel($pedido, $dados);
	    $relatorio->setHeaderExcel($dados_cabecalho_excel);
		    
		$relatorio->addColuna(array('campo' => 'codprod'	, 'etiqueta' => 'Código' 		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'codfabr'	, 'etiqueta' => 'Cod.Fábrica' 	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'descricao'	, 'etiqueta' => 'Descrição' 	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'quant'		, 'etiqueta' => 'Quantidade' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'precoF'		, 'etiqueta' => 'Preço Fábrica' , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc1'		, 'etiqueta' => 'DESC 1' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc2'		, 'etiqueta' => 'DESC 2' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc3'		, 'etiqueta' => 'DESC 3' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc10'		, 'etiqueta' => 'DESC 10' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'precoL'		, 'etiqueta' => 'Preço Líquido' , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'ipi'		, 'etiqueta' => 'IPI' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'st'		  	, 'etiqueta' => 'ST' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'icms'		, 'etiqueta' => 'ICMS' 			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'frete'		, 'etiqueta' => 'Frete' 		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'total'		, 'etiqueta' => 'Valor Total' 	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'E'));
		//$relatorio->addColuna(array('campo' => 'obs'		, 'etiqueta' => 'OBS Pedido' 	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		//$relatorio->addColuna(array('campo' => 'pedido'		, 'etiqueta' => 'Nr. Pedido' 	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		//$relatorio->addColuna(array('campo' => 'data'		, 'etiqueta' => 'Data Pedido' 	, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'E'));
		//$relatorio->addColuna(array('campo' => 'prazo'		, 'etiqueta' => 'Prazo Pedido' 	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'E'));
		//$relatorio->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor' 	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		//$relatorio->addColuna(array('campo' => 'cnpj'		, 'etiqueta' => 'CNPJ' 			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->setFooter($this->getAtencaoPDF($dados));
		
		$relatorio->setDados($dados['itens']);
		$mensagem = $this->getMensagemEmail();
		
		if($this->_teste){
			$ret['email_comprador'] = 'emanuel.thiel@verticais.com.br';
		}else{
			$ret['email_comprador'] = strtolower($dados['email']);
		}
		
		if(getAppVar('envia_email_fornecedor') == 'S'){
			if($this->_teste){
				$ret['email_fornec'] = 'emanuel.thiel@verticais.com.br';
			}else{
				$ret['email_fornec'] = $dados['rep_email'];
			}
		}else{
			$ret['email_fornec'] = '';
		}
		
		$email = $ret['email_comprador'];
		if(!empty($ret['email_fornec'])){
			$email .= ';'.$ret['email_fornec'];
		}
		//$email = 'emanuel.thiel@verticais.com.br';
		$param = array(
		    'mensagem' => $mensagem,
		);
		$ret['envio'] = $relatorio->enviaEmail($email, 'Gauchafarma - Pedido '.$pedido.' - '.$dados['fornecedor'], $param);
		
		return $ret;
	}
	

	private function getMensagemEmail(){
	    /*
		$ret = '';

		$parametros = $this->_sys020->getParametros($this->_programa);
		
		$ret = $parametros[0]['valor'];

		return $ret;
		*/
	    return $this->_sys020->getParametroAtualizado($this->_programa, 'MODELO_EMAIL');
	}
	
	
	private function getDadosPedido($pedido){
		$ret = [];
		
		$sql = "
				SELECT
				    PCPEDIDO.*,
				    PCFORNEC.CODFORNEC,
                    PCFORNEC.ENDER,
                    PCFORNEC.BAIRRO,
                    PCFORNEC.IE,
                    PCFORNEC.TELFAB,
                    PCFORNEC.CIDADE,
                    PCFORNEC.ESTADO,
				    PCFORNEC.FORNECEDOR,
                    PCFORNEC.CEP,
                    PCFORNEC.TELREP,
                    PCFORNEC.TIPOFRETECIFFOB,
                    PCFORNEC.CODFORNECFRETE,
                    PCFORNEC.REPRES,
                    PCFORNEC.CODPARCELA,
				    PCFORNEC.FANTASIA,
				    PCFORNEC.CGC,
				    PCFORNEC.EMAIL,
				    PCFORNEC.COM_EMAIL,
				    PCFORNEC.REP_EMAIL,
				    PCEMPR.NOME,
					PCEMPR.EMAIL,
                    (SELECT TRANSPORTADORA.FORNECEDOR FROM PCFORNEC TRANSPORTADORA WHERE TRANSPORTADORA.CODFORNEC = PCFORNEC.CODFORNECFRETE) NOME_TRANSPORTADORA,
                    PCPARCELASC.DESCRICAO PARCELA_DESC
				FROM
				    PCPEDIDO,
				    PCFORNEC,
				    PCEMPR,
                    PCPARCELASC
				WHERE
				    NUMPED = $pedido
				    AND PCPEDIDO.CODFORNEC = PCFORNEC.CODFORNEC (+)
				    AND PCPEDIDO.CODCOMPRADOR = PCEMPR.MATRICULA (+)
                    AND PCFORNEC.CODPARCELA = PCPARCELASC.CODPARCELA (+)
				";
		
		$rows = query4($sql);
		
		if(isset($rows[0]['NOME'])){
			$ret['emissao'] 	= $rows[0]['DTEMISSAO'];
			$ret['obs'] 		= $this->getObservacao($rows[0]['OBS'], $rows[0]['OBS2'], $rows[0]['OBS3'], $rows[0]['OBS5']);
			$ret['fornec'] 		= $rows[0]['CODFORNEC'];
			$ret['fornecedor'] 	= $rows[0]['FORNECEDOR'];
			$ret['fantasia'] 	= $rows[0]['FANTASIA'];
			$ret['cnpj'] 		= $rows[0]['CGC'];
			$ret['for_email'] 	= $rows[0]['EMAIL'];
			$ret['com_email'] 	= $rows[0]['COM_EMAIL'];
			$ret['rep_email'] 	= $rows[0]['REP_EMAIL'];
			$ret['comprador'] 	= $rows[0]['NOME'];
			$ret['email'] 		= $rows[0]['EMAIL'];
			
			$ret['valor_total']	= $rows[0]['VLTOTAL'];
			$ret['total_quant']	= 0;
			
			$ret['faturamento'] = $rows[0]['DTFATUR'];
			$ret['entrega']     = $rows[0]['DTPREVENT'];
			$ret['filial']      = $rows[0]['CODFILIAL'];
			$ret['verba']       = $rows[0]['NUMVERBA'];
			$ret['insc_estadual']   = $rows[0]['IE'];
			$ret['for_bairro'] = $rows[0]['BAIRRO'];
			$ret['for_endereco'] = $rows[0]['ENDER'];
			$ret['for_telefone'] = $rows[0]['TELFAB'];
			$ret['for_cidade'] = $rows[0]['CIDADE'];
			$ret['for_estado'] = $rows[0]['ESTADO'];
			$ret['for_cep'] = $rows[0]['CEP'];
			$ret['rep_telefone'] = $rows[0]['TELREP'];
			$ret['tipo_frete'] = $rows[0]['TIPOFRETECIFFOB'] == 'C' ? 'CIF' : ($rows[0]['TIPOFRETECIFFOB'] == 'F' ? 'FOB' : '') ;
			$ret['transportadora'] = $rows[0]['NOME_TRANSPORTADORA'];
			$ret['cond_pagamento'] = $rows[0]['PARCELA_DESC'];
			$ret['compra_merc'] = $rows[0]['TIPOBONIFIC'] == 'N' ? 'Compra de Mercadoria' : ($rows[0]['TIPOBONIFIC'] == 'B' ? 'BONIFICAÇÃO' : '');
			$ret['compra_merc_excel'] = $rows[0]['TIPOBONIFIC'] == 'N' ? 'NORMAL' : ($rows[0]['TIPOBONIFIC'] == 'B' ? 'BONIFICAÇÃO' : '');
			$ret['for_representante'] = $rows[0]['REPRES'];
			/*
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			$ret['']	= $rows[0][''];
			*/
		}
		
		$sql = "
				SELECT 
				    PCITEM.CODPROD,
				    PCPRODUT.DESCRICAO,
				    PCPRODUT.CODFAB,
				    PCITEM.PCOMPRA,
				    PCITEM.QTPEDIDA,
				    PCITEM.PLIQUIDO,
				    PCITEM.VLIPI,
				    PCITEM.PERCDESC1,
				    PCITEM.PERCDESC2,
				    PCITEM.PERCDESC3,
				    PCITEM.PERCDESC10,
				    PCITEM.VLFRETE,
				    PCITEM.VLST,
				    PCITEM.VLICMS,
				    PCITEM.PTABELA
				FROM 
				    PCITEM,
				    PCPRODUT
				WHERE 
				    NUMPED = $pedido
				    AND PCITEM.CODPROD = PCPRODUT.CODPROD (+)
				";
		$rows = query4($sql);
		
		if(isset($rows[0]['CODPROD'])){
			foreach ($rows as $row){
				$temp = [];
				$temp['codprod'] 	= $row['CODPROD'];
				$temp['descricao'] 	= $row['DESCRICAO'];
				$temp['codfabr'] 	= $row['CODFAB'];
				$temp['precoF'] 	= $row['PCOMPRA'];
				$temp['quant'] 		= $row['QTPEDIDA'];
				$ret['total_quant'] += $row['QTPEDIDA'];
				$temp['precoL'] 	= $row['PLIQUIDO'];
				$temp['desc1'] 		= $row['PERCDESC1'];
				$temp['desc2'] 		= $row['PERCDESC2'];
				$temp['desc3'] 		= $row['PERCDESC3'];
				$temp['desc10'] 	= $row['PERCDESC10'];
				$temp['frete'] 		= $row['VLFRETE'];
				$temp['ipi'] 		= $row['VLIPI'];
				$temp['st'] 		= $row['VLST'];
				$temp['icms'] 		= $row['VLICMS'];
				//$temp[''] 		= $row['PTABELA'];
				$temp['prazo'] 		= '';
				$temp['total'] 		= $row['QTPEDIDA'] * $row['PLIQUIDO'];
				$temp['data'] 		= $ret['emissao'];
				$temp['pedido'] 	= $pedido;
				//$temp['obs'] 		= $ret['obs'];
				$temp['fornecedor'] = $ret['fornecedor'];
				$temp['cnpj'] 		= $ret['cnpj'];
				
				$ret['itens'][] = $temp;
			}
		}

		
		return $ret;
	}
	
	//---------------------------------------------------------- UI -----------------------------------------
	
	private function getPainel($status, $dataIni, $dataFim, $comprador, $fornecedor){
		$ret = '';
		
		if($status == 'T' || $status == 'N' || empty($status)){
			$pedidosNaoEnviados = $this->getPedidos('N', $dataIni, $dataFim, $comprador, $fornecedor);
		}
		if($status == 'T' || $status == 'E' || empty($status)){
			$pedidosEnviados = $this->getPedidos('E', $dataIni, $dataFim, $comprador, $fornecedor);
		}
		if($status == 'R' || empty($status)){
			$pedidosRecebidos = $this->getPedidos('R', $dataIni, $dataFim, $comprador, $fornecedor);
		}
		
		if($status == 'T' || $status == 'N' || empty($status)){
			$tab = $this->getTabela('N', $pedidosNaoEnviados);
			$param = array(
			    'titulo' => 'Pedidos Não Enviados',
			    'conteudo' => $tab,
			);
			$ret .= addCard($param);
		}
		if($status == 'T' || $status == 'E' || empty($status)){
			$tab = $this->getTabela('E', $pedidosEnviados);
			$param = array(
			    'titulo' => 'Pedidos Enviados',
			    'conteudo' => $tab,
			);
			$ret .= addCard($param);
		}
		if($status == 'R' || empty($status)){
			$tab = $this->getTabela('R', $pedidosRecebidos);
			$param = array(
			    'titulo' => 'Pedidos Recebidos',
			    'conteudo' => $tab,
			);
			$ret .= addCard($param);
		}

		
		return $ret;
	}
	
	private function getTabela($tipo, $pedidos){
		$ret = '';
		
		$param = [];
		$param['paginacao'] = false;
		$param['width'] = 'AUTO';
		$tab = new tabela01($param );
		//$tab->setDetalhes(getLink().'detalhe&chamado=',0);
		
		$tab->addColuna(array('campo' => 'comprador'	, 'etiqueta' => 'Comprador'	,'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'emissao'		, 'etiqueta' => 'Emissão'	,'tipo' => 'D', 'width' => 100, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor','tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'numped'		, 'etiqueta' => 'Pedido'	,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		if($tipo == 'N' || $tipo == 'E'){
			$tab->addColuna(array('campo' => 'acao'		, 'etiqueta' => 'Ação'	,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		}
		$tab->addColuna(array('campo' => 'previsao'		, 'etiqueta' => 'Previsão'	,'tipo' => 'D', 'width' => 100, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'tipo'			, 'etiqueta' => 'Tipo'		,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'total'		, 'etiqueta' => 'Total R$'	,'tipo' => 'V', 'width' => 150, 'posicao' => 'D'));
		
		$tab->setDados($pedidos);
		
		$ret .= $tab;
		
		return $ret;
	}
	
	//---------------------------------------------------------------------------- VO ---------------
	
	private function getPedidos($tipo, $dataIni, $dataFim, $comprador, $fornecedor) {
		$ret = [];
		
		$where = " DTEMISSAO >= TO_DATE('$dataIni', 'YYYYMMDD')  AND DTEMISSAO <= TO_DATE('$dataFim', 'YYYYMMDD')";
		
		if(!empty($comprador)){
			$where .= " AND CODCOMPRADOR = $comprador";
		}
		
		if(!empty($fornecedor)){
			$where .= " AND CODFORNEC = $fornecedor";
		}
		
		if($tipo == 'R'){
			$where .= " AND VLENTREGUE > 0";
		}else{
			$where .= " AND VLENTREGUE = 0";
		}
		
		$sql = "SELECT CODCOMPRADOR, NUMPED, DTEMISSAO, DTPREVENT, TIPOBONIFIC, CODFORNEC, VLTOTAL FROM PCPEDIDO WHERE $where";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$ped = $row['NUMPED'];
				$temp['comprador'] 	= $this->getNomeComprador($row['CODCOMPRADOR']);
				$temp['numped'] 	= $ped;
				$temp['emissao'] 	= datas::dataMS2S($row['DTEMISSAO']);
				$temp['previsao'] 	= datas::dataMS2S($row['DTPREVENT']);
				$temp['tipo'] 		= $row['TIPOBONIFIC'];
				$temp['fornecedor'] = $row['CODFORNEC'].'-'.$this->getNomeFornecedor($row['CODFORNEC']);
				$temp['total'] 		= $row['VLTOTAL'];
				
				if($tipo != 'R'){
					//Monta o botão de ação
					$temp['acao'] = $this->getBotaoAcao($tipo, $ped);
				}
				
				if($tipo == 'R'){
					$ret[] = $temp;
				}else{
					$enviado = $this->getPosicaoPedido($temp['numped']);
					
					if(($tipo == 'N' && $enviado == 'N') || ($tipo == 'E' && $enviado == 'E')){
						$ret[] = $temp;
					}
				}
				
				
			}
		}
		return $ret;
	}
	
	private function getBotaoAcao($tipo, $ped){
		$ret = '';
		
		if($tipo == 'N'){
			$item1 = '<li><a href="'.getLink().'enviar&pedido='.base64_encode($ped).'">Enviar email</a></li>';
			$item2 = '<li><a href="'.getLink().'marcar&pedido='.base64_encode($ped).'">Marcar como já enviado</a></li>';
		}else{
			$item1 = '<li><a href="'.getLink().'reenviar&pedido='.base64_encode($ped).'">Enviar novamente o email</a></li>';
			$item2 = '';
			
		}
		
		$ret .= '
					<div class="btn-group">
					  <button type="button" class="btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ação <span class="caret"></span></button>
					  <ul class="dropdown-menu">'.$item1.$item2.'</ul>
					</div>';
		
		return $ret;
	}
	
	private function getPosicaoPedido($numped){
		$ret = 'N';
		
		$sql = "SELECT status FROM gf_sit_pedido WHERE pedido = $numped";
		$rows = query($sql);
		
		if(isset($rows[0]['status'])){
			$ret = $rows[0]['status'];
		}else{
			$sql = "INSERT INTO gf_sit_pedido (pedido,status) VALUES ($numped, 'N')";
			query($sql);
		}
		
		
		return $ret;
	}
	
	private function getNomeFornecedor($fornec){
		$ret = '';
		
		if(!isset($this->_fornecedores[$fornec])){
			$sql = "SELECT FORNECEDOR FROM PCFORNEC WHERE CODFORNEC = $fornec";
			$rows = query4($sql);
			
			if(isset($rows[0]['FORNECEDOR'])){
				$this->_fornecedores[$fornec] = substr($rows[0]['FORNECEDOR'], 0, 40);
			}else{
				$this->_fornecedores[$fornec] = '';
			}
		}
		
		$ret = $this->_fornecedores[$fornec];
		
		return $ret;
	}
	
	private function getNomeComprador($comprador){
		$ret = '';
		
		if(!isset($this->_compradores[$comprador])){
			$sql = "SELECT NOME FROM PCEMPR WHERE MATRICULA = $comprador";
			$rows = query4($sql);
			
			if(isset($rows[0]['NOME'])){
				$this->_compradores[$comprador] = $rows[0]['NOME'];
			}else{
				$this->_compradores[$comprador] = '';
			}
		}
		
		$ret = $this->_compradores[$comprador];
		
		return $ret;
	}


	private function getTabelaTotaisPDF($dados){
		$ret = '';
		
		$param = [];
		$param['mostraCab'] = false;
		$param['border'] = false;
		$tabPdf = new tabela_pdf($param);
		$tabPdf->setMostraCabecalho(false);
		
		$tabPdf->addColuna(['campo' => 'cab1', 'etiqueta' => '', 'width' => '150', 'pos' => 'D', 'tipo' => 'T']);
		$tabPdf->addColuna(['campo' => 'val1', 'etiqueta' => '', 'width' => '100', 'pos' => 'D', 'tipo' => 'T']);
		$tabPdf->addColuna(['campo' => 'cab2', 'etiqueta' => '', 'width' => '100', 'pos' => 'D', 'tipo' => 'T']);
		$tabPdf->addColuna(['campo' => 'val2', 'etiqueta' => '', 'width' => '100', 'pos' => 'D', 'tipo' => 'T']);
		$tabPdf->addColuna(['campo' => 'cab3', 'etiqueta' => '', 'width' => '100', 'pos' => 'D', 'tipo' => 'T']);
		$tabPdf->addColuna(['campo' => 'val3', 'etiqueta' => '', 'width' => '100', 'pos' => 'D', 'tipo' => 'T']);
		$tabPdf->addColuna(['campo' => 'cab4', 'etiqueta' => '', 'width' => '80', 'pos' => 'D', 'tipo' => 'T']);
		$tabPdf->addColuna(['campo' => 'val4', 'etiqueta' => '', 'width' => '50', 'pos' => 'D', 'tipo' => 'T']);
		
		$dadosTabela = [];
		//Linha 
		$temp = [];
		$temp['cab1'] = 'Total ST Guia:';
		$temp['val1'] = '';
		$temp['cab2'] = 'Total Quantidade:';
		$temp['val2'] = $dados['total_quant'];
		$temp['cab3'] = 'Total Valor Verba Bonificação:';
		$temp['val3'] = '';
		$temp['cab4'] = 'Nr. Verba D:';
		$temp['val4'] = '0,00';
		$dadosTabela[] = $temp;
		
		$temp = [];
		$temp['cab1'] = 'Valor ICMS:';
		$temp['val1'] = '';
		$temp['cab2'] = 'Base ICMS:';
		$temp['val2'] = '';
		$temp['cab3'] = 'Total Valor Merc. Bonif.';
		$temp['val3'] = '';
		$temp['cab4'] = 'Nr. Verba M:';
		$temp['val4'] = '0,00';
		$dadosTabela[] = $temp;
		
		$temp = [];
		$temp['cab1'] = 'Vl. Total Frete (FOB):';
		$temp['val1'] = '';
		$temp['cab2'] = 'Total Prod.:';
		$temp['val2'] = '';
		$temp['cab3'] = 'Valor IPI:';
		$temp['val3'] = '';
		$temp['cab4'] = 'Nr. Verba O:';
		$temp['val4'] = '0,00';
		$dadosTabela[] = $temp;
		
		$temp = [];
		$temp['cab1'] = 'Vl. Total Outras Desp. Fora NF:';
		$temp['val1'] = '';
		$temp['cab2'] = 'Total Pedido:';
		$temp['val2'] = '';
		$temp['cab3'] = '';
		$temp['val3'] = '';
		$temp['cab4'] = '';
		$temp['val4'] = '';
		$dadosTabela[] = $temp;
		
		$tabPdf->setDados($dadosTabela);
		
		$ret .= $tabPdf;
		
		return $ret;
	}
	
	private function getAtencaoPDF($dados){
		$nl = "<br>\n";
		$ret = '';
		
		$ret .= $this->getTabelaTotaisPDF($dados);
		
		if(!empty($dados['obs'])){
			$ret .= $dados['obs'];
			$ret .= "<br><br>\n";
		}
		
		$ret .= '<b>ATENÇÃO:</b>'.$nl;
		$ret .= '1. Obrigatório enviar arquivo XML da nota eletrônica ou espelho de nota fiscal para o e-mail nfe@gauchafarma.com antes da entrega da mercadoria.'.$nl;
		$ret .= '2. Produtos sujeitos à Substituição Tributaria: fornecedor deve recolher GNRE antes da mercadoria entrar no estado.'.$nl;
		$ret .= '3. Agendar entrega pelo e-mail agendamento@gauchafarma.com, dúvidas entrar em contato com setor de recebimento pelo fone (51) 3382.2039.'.$nl;
		$ret .= '4. Horário de recebimento 8:00 as 11h.'.$nl;
		$ret .= '5. Não aceitamos produtos com vida útil inferior a 2/3 do seu prazo total de validade.'.$nl;
		$ret .= '6. Não reconhecemos títulos endossados à terceiros sem prévio acordo.'.$nl;
		$ret .= '7. Do valor a pagar por esta compra, poderão ser descontados valores relativos à notas de devolução, verbas comerciais, produtos vencidos e diferenças de preço.'.$nl;
		$ret .= '8. Dúvidas tratar com setor de compras pelo fone (51) 3382.2045.'.$nl;
		$ret .= '9. Não será permitido a entrada de ajudantes terceirizados (Chapas) na Gauchafarma. Se a transportadora necessitar de ajudantes o setor de recebimento irá entrar em contato com cooperativas credenciadas. Agendar com o recebimento antes da carga chegar na empresa.'.$nl;
		
		return $ret;
	}
	
	private function getObservacao($obs1, $obs2, $obs3, $obs4){
		$ret = "<b>OBSERVAÇÃO:</b><br>\n";
		
		if(!empty($obs1)){
			if(!empty($ret)){
				$ret .= "<br>\n";
			}
			$ret .= $obs1;
		}
		if(!empty($obs2)){
			if(!empty($ret)){
				$ret .= "<br>\n";
			}
			$ret .= $obs2;
		}
		if(!empty($obs3)){
			if(!empty($ret)){
				$ret .= "<br>\n";
			}
			$ret .= $obs3;
		}
		if(!empty($obs4)){
			if(!empty($ret)){
				$ret .= "<br>\n";
			}
			$ret .= $obs4;
		}
		
		return $ret;
	}
	
	private function montarEstiloTdCabecalho($tamanho){
	    return ' style="border-style:none;border: 0px solid black;width: ' . $tamanho . '%;" ';
	}
	
	private function monta_cabecalho($pedido, $dados){
	    $ret = '';
	    $estilo = '';
	    
	    $estilo = '';
	    $estilo_tr = 'style="border-top:thin solid;"';
	    $primeiro_bloco = "
<table>
    <tr>
        <td" . $this->montarEstiloTdCabecalho('30') . "> *** PEDIDO EMITIDO NA UNID. DE VENDA **</td>
        <td" . $this->montarEstiloTdCabecalho('20') . "> </td>
        <td" . $this->montarEstiloTdCabecalho('20') . "> </td>
        <td" . $this->montarEstiloTdCabecalho('30') . "> </td>
    </tr>
    <tr>
        <td" . $this->montarEstiloTdCabecalho('30') . "> Data Emissão: " . datas::dataS2D(datas::dataMS2S($dados['emissao'], '/', '-')) . "</td>
        <td" . $this->montarEstiloTdCabecalho('20') . "> N.Verba " . $dados['verba'] . "</td>
        <td" . $this->montarEstiloTdCabecalho('20') . "> Número do Pedido: $pedido</td>
        <td" . $this->montarEstiloTdCabecalho('30') . "> </td>
    </tr>
    <tr>
        <td" . $this->montarEstiloTdCabecalho('30') . "> Data Faturamento: " . datas::dataS2D(datas::dataMS2S($dados['faturamento'], '/', '-')) . "</td>
        <td" . $this->montarEstiloTdCabecalho('20') . "> Filial: " . $dados['filial'] . "</td>
        <td" . $this->montarEstiloTdCabecalho('50') . " colspan=" . '"2"' . "> Condições de Pagamento: " . $dados['cond_pagamento'] . " </td>
    </tr>
    <tr>
        <td" . $this->montarEstiloTdCabecalho('30') . "> Data Entrega: " . datas::dataS2D(datas::dataMS2S($dados['entrega'], '/', '-')) . "</td>
        <td" . $this->montarEstiloTdCabecalho('20') . "> " . $dados['compra_merc'] . "</td>
        <td" . $this->montarEstiloTdCabecalho('20') . "> Frete: " . $dados['tipo_frete'] . "</td>
        <td" . $this->montarEstiloTdCabecalho('30') . "> Transportadora: " . $dados['transportadora'] . "</td>
    </tr>
</table>";
	    $segundo_bloco = "
<table $estilo_tr>
    <tr>
        <td colspan=" . '"3"' . " " . $this->montarEstiloTdCabecalho('45') . ">Empresa: GAUCHAFARMA MEDICAMENTOS LTDA.</td>
        <td " . $this->montarEstiloTdCabecalho('20') . ">CNPJ/CPF: 89735070000100</td>
        <td " . $this->montarEstiloTdCabecalho('30') . ">Comprador: " . $dados['comprador'] . "</td>
        
    </tr>
    <tr>
        <td colspan=" . '"3"' . $this->montarEstiloTdCabecalho('45') . ">Endereço: AV FRANCISCO SILVEIRA BITENCOURT 1785 SARANDI</td>
        <td " . $this->montarEstiloTdCabecalho('55') . " colspan=" . '"2"' . ">Inscrição Estadual: 0960816739</td>
    </tr>
    <tr>
        <td " . $this->montarEstiloTdCabecalho('20') . ">Cidade: PORTO ALEGRE</td>
        <td " . $this->montarEstiloTdCabecalho('12') . ">UF: RS</td>
        <td " . $this->montarEstiloTdCabecalho('13') . ">CEP: 91150010</td>
        <td " . $this->montarEstiloTdCabecalho('50') . " colspan=" . '"2"' . ">Telefone: 5133822000</td>
    </tr>
</table>";
	    $terceiro_bloco = "
<table $estilo_tr>
    <tr>
        <td " . $this->montarEstiloTdCabecalho('35') . " colspan=" . '"2"' . ">Fornecedor: " . $dados['fornecedor'] . "</td> 
        <td " . $this->montarEstiloTdCabecalho('15') . ">Codigo: " . $dados['fornec'] . "</td> 
        <td " . $this->montarEstiloTdCabecalho('25') . ">CNPJ/CPF: " . $dados['cnpj'] . "</td> 
        <td " . $this->montarEstiloTdCabecalho('25') . ">Inscrição Estadual: " . $dados['insc_estadual'] . "</td> 
    </tr>
    <tr>
        <td " . $this->montarEstiloTdCabecalho('50') . " colspan=" . '"3"' . ">Endereço: " . $dados['for_endereco'] . ' ' . $dados['for_bairro'] . "</td> 
        <td " . $this->montarEstiloTdCabecalho('50') . " colspan=" . '"2"' . ">Telefone: " . $dados['for_telefone'] . "</td> 
    </tr>
    <tr>
        <td" . $this->montarEstiloTdCabecalho('25') . ">Cidade: " . $dados['for_cidade'] . "</td>
        <td" . $this->montarEstiloTdCabecalho('13') . ">UF: " . $dados['for_estado'] . "</td>  
        <td" . $this->montarEstiloTdCabecalho('12') . ">CEP: " . $dados['for_cep'] . "</td>
        <td" . $this->montarEstiloTdCabecalho('25') . "></td>
        <td" . $this->montarEstiloTdCabecalho('25') . "></td>
    </tr>
    <tr>
        <td " . $this->montarEstiloTdCabecalho('50') . " colspan=" . '"3"' . ">Repres.: " . $dados['for_representante'] . "</td>
        <td " . $this->montarEstiloTdCabecalho('50') . " colspan=" . '"2"' . ">Telefone: " . $dados['rep_telefone'] . "</td>  
    </tr>
</table>";
	    $ret = $estilo . $primeiro_bloco . $segundo_bloco . $terceiro_bloco;
	    //log::gravaLog('cabecalho_html', $ret);
	    //$ret = str_replace('<td', $td, $ret);
	    return $ret;
	}
	
	private function montarDadosCabecalhoExcel($pedido, $dados){
	    $ret = array();
	    $ret[] = array('EMPRESA', 'GAUCHAFARMA MEDICAMENTOS LTDA.');
	    $ret[] = array('CNPJ/MG:', '89.735.070/0001-00');
	    $ret[] = array('ENDERECO:', 'AV FRANCISCO SILVEIRA BITENCOURT');
	    $ret[] = array('FORNECEDOR:', $dados['fornecedor']);
	    $ret[] = array('CNPJ:', $dados['cnpj']);
	    $ret[] = array('NUMERO PEDIDO:', $pedido);
	    $ret[] = array('CONDIÇÃO DE PAGAMENTO', $dados['cond_pagamento']);
	    $ret[] = array('DATA DO PEDIDO', datas::dataS2D(datas::dataMS2S($dados['emissao'], '/', '-')));
	    $ret[] = array('TIPO PEDIDO:', $dados['compra_merc_excel']);
	    $ret[] = array('', '');
	    return $ret;
	}
}

function CP_getComprador(){
	$ret = array();
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT MATRICULA, NOME FROM PCEMPR WHERE MATRICULA IN (SELECT DISTINCT CODCOMPRADOR FROM PCPEDIDO) AND SITUACAO = 'A' ORDER BY NOME";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['MATRICULA'];
			$temp[1] = $row['NOME'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}

function CP_getFornec(){
	$ret = array();
	
	$ret[0][0] = "";
	$ret[0][1] = "&nbsp;";
	
	$sql = "SELECT CODFORNEC, FORNECEDOR FROM PCFORNEC WHERE CODFORNEC IN (SELECT DISTINCT CODFORNEC FROM PCPEDIDO) AND DTBLOQUEIO IS NULL ORDER BY FORNECEDOR";
	$rows = query4($sql);
	
	if(is_array($rows) && count($rows) > 0){
		foreach ($rows as $row) {
			$temp[0] = $row['CODFORNEC'];
			$temp[1] = $row['CODFORNEC'].'-'.$row['FORNECEDOR'];
			
			$ret[] = $temp;
		}
	}
	return $ret;
}