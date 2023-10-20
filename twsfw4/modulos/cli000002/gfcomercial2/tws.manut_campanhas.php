<?php
/*
 * Data Criacao 1 de Julho de 2017
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Programa para manutenção de campanhas de vendas
 *
 * Alterções:
 *           12/02/2019 - Alexandre - Migração para intranet2
 *			 12/01/2020 - Alexandre - Incluído o conceito de Meta por ERC x Cliente e se pega o ERC do cadastro de Cliente ou do Pedido
 *			 04/02/2022 - Alexandre - Criada a possibilidade de imprimir coluna com o percentual executado acima da meta na sub campanha (Aline/Ivair) - campo percent_acima (gf_camp_subcamp)
 *           14/02/2023 - Emanuel   - Migrado para a intranet4
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
//$config['site']['debug'] = true;

class manut_campanhas{
	var $_relatorio;
	
	var $funcoes_publicas = array(
			'index' 		=> true,
			'copiar' 		=> true,
			'editar' 		=> true,
			'metas' 		=> true,
			'metasTV' 		=> true,
			'incluir' 		=> true,
			'sub'	 		=> true,
			'excluir'		=> true,
			'premiacoes'	=> true,
			'download'		=> true,
	);
	
	//Titulo geral
	var $_titulo;
	
	//Supervisores
	var $_super;
	
	//ERCs
	var $_erc;
	
	//Operadores
	var $_operadores;
	
	//Campanhas
	var $_campanhas;
	
	//Sub Campanhas
	var $_subCampanhas;
	
	public function __construct(){
		set_time_limit(0);
		$this->getVendedores();
	}
	
	public function index(){
		global $operacao;
		$ret = '';
		
		if($operacao == ''){
			$ret = $this->browser1();
		}
		
		return $ret;
	}

	public function excluir(){
		$id = getParam($_GET, 'id');
		if(!empty($id) && strlen($id) == 15){
			$sql = "DELETE FROM gf_camp_itens 		WHERE campanha = '$id'";
			$this->log(__FUNCTION__." $sql", $id);
			query($sql);
			$sql = "DELETE FROM gf_camp_campanhas 	WHERE id = '$id'";
			$this->log(__FUNCTION__." $sql", $id);
			query($sql);
			$sql = "DELETE FROM gf_camp_metas 		WHERE campanha = '$id'";
			$this->log(__FUNCTION__." $sql", $id);
			query($sql);
			$sql = "DELETE FROM gf_camp_subcamp		WHERE campanha = '$id'";
			$this->log(__FUNCTION__." $sql", $id);
			query($sql);
			
			addPortalMensagem('Campanha excluída com sucesso!');
		}
		return $this->browser1();
	}
	
	public function copiar(){
		$ret = '';
		
		$id = getParam($_GET, 'id');
		if(!empty($id) && strlen($id) == 15){
			$this->realizarCopia($id);
		}
		$ret .= $this->browser1();
		
		return $ret;
	}
	
	public function incluir(){
		$ret = '';
		$operacao = getOperacao();
		
		if($operacao == ''){
			//$ret = $this->getTitulo("Campanhas");
			$ret .= $this->formCampanha();
		}elseif($operacao == 'form'){
			$erro = '';
			$dados = array();
			$formInc = getParam($_POST, 'formInc');
			$dados['titulo'] 	= $formInc['titulo'];
			$dados['ini'] 		= $formInc['ini'];
			$dados['fim'] 		= $formInc['fim'];
			$dados['fechamento']= $formInc['fechamento'];
			$dados['ativo'] 	= $formInc['ativo'];
			$dados['totalReal']	= $formInc['totalReal'];
			$dados['totalMeta']	= $formInc['totalMeta'];
			$dados['vendedor']	= $formInc['vendedor'];
			$dados['enviaEmail']= $formInc['enviaEmail'];
			$dados['erc_fora']	= $formInc['erc_fora'];
			$dados['ped_fora']	= $formInc['ped_fora'];
			$dados['cli_fora']	= $formInc['cli_fora'];
			$dados['origCli']	= $formInc['origCli'];
			$dados['tipoMeta']	= $formInc['tipoMeta'];
			$dados['email_para']= $formInc['email_para'];

			$erro = $this->verificaErrosCampanha($dados);
			
			if($erro == ''){
				$this->gravaCampanha($dados);
				$ret .= $this->browser1();
			}else{
				//$ret = $this->getTitulo("Campanhas");
				$ret .= $this->formCampanha('',$dados,$erro);
			}
			//	print_r($_POST);
		}
		
		return $ret;
	}
	
	public function sub(){
		$operacao = getOperacao();
		$ret = '';
		$id = getParam($_GET, 'id');
		if(empty($id) || strlen($id) != 15){
			return $this->browser1();
		}
		
		if($operacao == ''){
			putAppVar('manut_campanha2017_editarSub', $id);
			$this->getCampanhas($id);
			$campanha = $this->_campanhas[0]['titulo'];
			putAppVar('manut_campanha_titulo', $campanha);
			$ret = $this->browser2();
		}elseif($operacao == 'excluir'){
			$this->excluirSubCampanha($id);
			$ret = $this->browser2();
		}elseif($operacao == 'copiar'){
			$this->copiarSubCampanha($id);
			$ret = $this->browser2();
		}elseif($operacao == 'incluir'){
			$ret .= $this->formSubCampanha();
		}elseif($operacao == 'editar'){
			$ret .= $this->formSubCampanha($id);
		}elseif($operacao == 'gravar'){
			$id = getAppVar('manut_campanha2017_editarSub_sub');
			if(!empty($id) && strlen($id) == 15){
				$formInc = getParam($_POST, 'formInc');
				$erro = $this->verificaErrosSubCampanha($formInc);
				if($erro == ''){
					$this->gravarSubCampanha($formInc);
					addPortalMensagem('Sub campanha gravada com sucesso!');
					$ret .= $this->browser2();
				}else{
					$ret .= $this->formSubCampanha($id,$formInc,$erro);
				}
			}else{
				$ret = $this->browser2();
			}
		}elseif($operacao == 'metas'){
			$id = getParam($_GET, 'id');
			if(!empty($id) && strlen($id) == 15){
				putAppVar('manut_campanha2017_editarSub_metas', $id);
				$ret .= $this->formSubMetas($id);
			}else{
				$ret = $this->browser2();
			}
		}elseif($operacao == 'gravarMetas'){
			$id = getAppVar('manut_campanha2017_editarSub_metas');
			$this->getCampanhas($id);
			if(!empty($id) && strlen($id) == 15){
				$formInc = getParam($_POST, 'formInc');
				$this->gravarSubCampanhaMetas($formInc);
				$ret .= $this->browser2();
				//unsetAppVar('manut_campanha2017_editarSub_metas');
				addPortalMensagem('Metas gravada com sucesso!');
			}
			$ret = $this->browser2();
		}
		
		return $ret;
	}
	
	public function editar(){
		$operacao = getOperacao();
		$ret = '';
		
		$id = getParam($_GET, 'id');
		if(empty($id) || strlen($id) != 15){
			return $this->browser1();
		}
		
		if($operacao == ''){
			$this->getCampanhas($id);
			putAppVar('manut_campanha2017_editar', $id);
			if(count($this->_campanhas) == 1){
				$dado = $this->_campanhas[0];
				$ret .= $this->formCampanha($id,$dado);
			}
		}elseif($operacao == 'gravar'){
			$id = getAppVar('manut_campanha2017_editar');
			$erro = '';
			$formInc = getParam($_POST, 'formInc');
			$formInc['id'] = $id;
			$erro = $this->verificaErrosCampanha($formInc);
			
			if($erro == ''){
				$this->gravaCampanha($formInc);
				addPortalMensagem('Campanha salva com sucesso!');
				unsetAppVar('manut_campanha2017_editar');
				redireciona(getLink().'index');
			}else{
				//$ret = $this->getTitulo("Campanhas");
				$ret .= $this->formCampanha($id,$formInc,$erro);
			}
		}
		
		return $ret;
	}
	
	public function metas(){
		$operacao = getOperacao();
		$ret = '';
		
		
		if($operacao == ''){
			$id = getParam($_GET, 'id');
			if(empty($id) || strlen($id) != 15){
				return $this->browser1();
			}
			putAppVar('manut_campanha2017_metas', $id);
			$ret .= $this->browser3();
		}elseif($operacao == 'excluir'){
			$id = getParam($_GET, 'id');
			if($id > 0){
				$this->excluirERCmeta($id);
			}
			$ret .= $this->browser3();
		}elseif($operacao == 'gravar'){
			$id = getParam($_GET, 'id');
			if(!empty($id) && strlen($id) == 15){
				$this->gravarMetas();
			}
			$ret .= $this->browser3();
		}elseif($operacao == 'incluirERC'){
			$ret = $this->getFormAddERC();
		}elseif($operacao == 'incluirERCgravar'){
			$this->adicionaERC();
			$ret .= $this->browser3();
		}
		
		return $ret;
	}
	
	public function premiacoes(){
		$operacao = getOperacao();
		$ret = '';
		
		if($operacao == ''){
			$id = getParam($_GET, 'id');
			if(empty($id) || strlen($id) != 15){
				$ret .= $this->browser1();
			}else{
				putAppVar('manut_campanha2017_premiacoes', $id);
				$this->getCampanhas($id);
				$campanha = $this->_campanhas[0]['titulo'];
				putAppVar('manut_campanha_titulo', $campanha);
				$ret .= $this->browser4();
			}
		}elseif($operacao == 'gravar'){
			$id = getParam($_GET, 'id');
			if(!empty($id) && strlen($id) == 15){
				$this->gravarPremiacoes($id);
			}
			$ret .= $this->browser4();
		}
		
		return $ret;
	}
	
	public function download(){
		global $config;
		$id = getParam($_GET, 'id');
		if(!empty($id) && strlen($id) == 15){
			$arquivo = $id.'.log';
			$caminho = $config['debugPath'].'manutencao_campanhas'.DIRECTORY_SEPARATOR.$arquivo;
			if(is_file($caminho)){
				
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$arquivo");
				header("Content-Length: " . filesize($caminho));
				
				$fp = fopen($caminho, 'rb');
				fpassthru($fp);
				fclose($fp);
			}else{
				addPortalMensagem('Arquivo de log não encontrado!','error');
			}
		}
		
//		redireciona();
	}
	
	//------------------------------------------------------------------------------------------------------ FORMULARIOS -------------------------------
	
	private function formCampanha($id = '',$dados = array(), $erro = ''){
		$mensagem = '';
		$form = new form01();
		$sn = tabela("000003","desc");
		$vendedor = $this->getTipoVendedor();
		$origemCli = 'C=Cadastro Cliente;P=Pedido';
		$tipoMeta = 'E=ERC;C=Cliente;P=Cliente Principal';
		if($id == ''){
			$titulo = 'Incluir Campanha';
			$url = getLink().'incluir.form';
			if(count($dados) == 0){
				$dados['titulo'] = '';
				$dados['ini'] = '';
				$dados['fim'] = '';
				$dados['fechamento'] 	= '';
				$dados['totalReal'] 	= 'N';
				$dados['totalMeta'] 	= 'N';
				$dados['vendedor'] 		= 'E';
				$dados['enviaEmail'] 	= 'S';
				$dados['erc_fora'] 		= '';
				$dados['cli_fora'] 		= '';
				$dados['ped_fora'] 		= '';
				$dados['ativo'] 		= 'S';
				$dados['origCli'] 		= 'C';
				$dados['tipoMeta'] 		= 'E';
				$dados['email_para'] 	= '';
				$dados['porCliente']	= 'N';
				$dados['cliSemVenda']	= 'N';
				$dados['ercSemMeta']	= 'N';
			}
		}else{
			$titulo = 'Alterar Campanha #'.$dados['seq'];
			$form->addHidden('formInc[id]', $id);
			$url = getLink().'editar.gravar&id='.$id;
		}
		
		$linha = 1;
		$form->addCampo(array('id' => '', 'campo' => 'formInc[titulo]'		, 'etiqueta' => 'Titulo'				, 'linha' => $linha, 'largura' =>12, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['titulo']		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 60));
		
		$linha++;
		$form->addCampo(array('id' => '', 'campo' => 'formInc[ini]'	 		, 'etiqueta' => 'Inicio'				, 'linha' => $linha, 'largura' => 3, 'tipo' => 'D' , 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['ini']			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[fim]'			, 'etiqueta' => 'Fim'					, 'linha' => $linha, 'largura' => 3, 'tipo' => 'D' , 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['fim']			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[fechamento]'	, 'etiqueta' => 'Fechamento'			, 'linha' => $linha, 'largura' => 3, 'tipo' => 'D' , 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['fechamento']	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[origCli]'		, 'etiqueta' => 'Cliente de'			, 'linha' => $linha, 'largura' => 3, 'tipo' => 'T' , 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['origCli']		, 'opcoes' => $origemCli	, 'validacao' => '', 'obrigatorio' => false));
		
		$linha++;
		$form->addCampo(array('id' => '', 'campo' => 'formInc[totalReal]'	, 'etiqueta' => 'Totaliza Realizado?'	, 'linha' => $linha, 'largura' => 2, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['totalReal']	, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[totalMeta]'	, 'etiqueta' => 'Totaliza Metas?'		, 'linha' => $linha, 'largura' => 2, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['totalMeta']	, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[vendedor]'	, 'etiqueta' => 'Venda de?'				, 'linha' => $linha, 'largura' => 2, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['vendedor']		, 'lista' => $vendedor	, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[enviaEmail]'	, 'etiqueta' => 'Envia Email?'			, 'linha' => $linha, 'largura' => 2, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['enviaEmail']	, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[tipoMeta]'	, 'etiqueta' => 'Meta Por'				, 'linha' => $linha, 'largura' => 2, 'tipo' => 'A' , 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['tipoMeta']		, 'opcoes' => $tipoMeta	, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[ativo]'		, 'etiqueta' => 'Ativo'					, 'linha' => $linha, 'largura' => 2, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['ativo']		, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false));
		
		$linha++;
//		$help = "Abre o relatório por cliente (mostra todos os clientes)";
//		$form->addCampo(array('id' => '', 'campo' => 'formInc[porCliente]'	, 'etiqueta' => 'Abre por Cliente?'		, 'linha' => $linha, 'largura' => 2, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['porCliente']	, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false, 'help' => $help));
		$help = "No caso de meta por cliente, deve mostrar os clientes sem venda?";
		$form->addCampo(array('id' => '', 'campo' => 'formInc[cliSemVenda]'	, 'etiqueta' => 'Clientes sem Venda?'	, 'linha' => $linha, 'largura' => 3, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['cliSemVenda']	, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false, 'help' => $help));
		$help = "Deve mostrar os ERCs/Clientes sem meta?";
		$form->addCampo(array('id' => '', 'campo' => 'formInc[ercSemMeta]'	, 'etiqueta' => 'ERC/Cliente sem meta?'	, 'linha' => $linha, 'largura' => 3, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados['ercSemMeta']	, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false, 'help' => $help));
		
		$linha++;
		$form->addCampo(array('id' => '', 'campo' => 'formInc[erc_fora]'	, 'etiqueta' => 'Ignorar ERCs'			, 'linha' => $linha, 'largura' => 6, 'tipo' => 'TA', 'tamanho' => '200'	, 'linhas' => '', 'valor' => $dados['erc_fora']		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		$form->addCampo(array('id' => '', 'campo' => 'formInc[ped_fora]'	, 'etiqueta' => 'Ignorar Pedidos'		, 'linha' => $linha, 'largura' => 6, 'tipo' => 'TA', 'tamanho' => '200'	, 'linhas' => '', 'valor' => $dados['ped_fora']		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		
		$linha++;
		$form->addCampo(array('id' => '', 'campo' => 'formInc[cli_fora]'	, 'etiqueta' => 'Ignorar Clientes'		, 'linha' => $linha, 'largura' => 6, 'tipo' => 'TA', 'tamanho' => '200'	, 'linhas' => '', 'valor' => $dados['cli_fora']		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
		$help = "Indica para quais emails deve ser enviado o resumo da campanha.";
		$form->addCampo(array('id' => '', 'campo' => 'formInc[email_para]'	, 'etiqueta' => 'Enviar email para'		, 'linha' => $linha, 'largura' => 6, 'tipo' => 'TA', 'tamanho' => '200'	, 'linhas' => '', 'valor' => $dados['email_para']	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'help' => $help));
		
		$form->setEnvio($url, 'campanhaForm', 'campanhaForm');
		
		if($erro != ''){
			addPortalMensagem('Erro de Cadastro: '.$erro, 'error');
		}
		
		//$param = array();
		$p = array();
		$p['onclick'] = "setLocation('".getLink()."index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Cancelar';
		//$param['botoesTitulo'][] = $p;
		//$ret = addBoxInfo($titulo.' Campanha', $mensagem.$form, $param);
		$ret = addCard(array('titulo' => $titulo, 'conteudo' => $mensagem.$form, 'botoesTitulo' => array($p)));
		return $ret;
	}

	private function formSubCampanha($id = '',$dados = array(), $erro = ''){
		$ret = '';
		$mensagem = '';
		$campanha = getAppVar('manut_campanha2017_editarSub');
		$nome = getAppVar('manut_campanha_titulo');
		
		if(!empty($campanha) && strlen($campanha) == 15){
			$form = new form01();
			$tabMeta = array(array("",""),array("V","Valor"),array("Q","Quantidade"),array("P","Positivacao - Produto"),array("C","Positivacao - Cliente"),array("M","Mix"),array('N','Preço Médio'));
			$tabImpMeta = array(array("",""),array("S","Sim"),array("N","Nao"));
			$tabImpReal = array(array("",""),array("S","Sim"),array("N","Nao"));
			$tabTipo = array(array("",""),array("P","Produto"),array("F","Fabricante"),array("M","Marca"),array("D","Departamento"),array("K","KIT"),array("G","Sem filtro"));
			$origens = $this->getOrigens();
			
			$url = getLink().'sub.gravar&id='.$campanha;
			
			if($id == ''){
				$titulo = 'Incluir';
				$dados = array();
				$dados['id'] = geraID('gf_camp_subcamp');
				$dados['campanha'] = $campanha;
				$dados['sub'] = $this->getProximaSub($campanha, 'gf_camp_subcamp');
				$dados['titulo'] = '';
				$dados['meta'] = '';
				$dados['tipo'] = '';
				$dados['campanhaWT'] = '';
				$dados["ativo"] = 'S';
				$dados['itens'] = '';
				$dados['origem'] = 'T';
				$dados['impMeta'] = 'S';
				$dados['impReal'] = 'S';
				$dados['tituloMeta'] = '';
				$dados['tituloReal'] = '';
				$dados['vendaItem'] = '';
				$dados['sequencia'] = '0';
				$dados['min_positivacao'] = 0;
				$dados['percent_acima'] = 'N';
				$dados['titulo_percent'] = 'Realizado acima da meta';
				putAppVar('manut_campanha2017_editarSub_sub', $dados['id']);
			}else{
				$titulo = 'Alterar';
				if(count($dados) == 0){
					putAppVar('manut_campanha2017_editarSub_sub', $id);
					$this->getSubCampanhas($campanha,true,$id);
					$dados = $this->_subCampanhas[0];
//print_r($dados);
					if(!isset($dados['itens'])){
						$dados['itens'] = $this->getItensString($campanha, $id);
					}
				}
				
			}
			
			//Por padrão imprime a meta
			if(!isset($dados['impMeta']) || $dados['impMeta'] == ''){
				$dados['impMeta'] = 'S';
			}
			$form->addCampo(array('id' => '', 'campo' => 'formInc[sub]'			, 'etiqueta' => 'ID'		 					, 'linha' => 1, 'largura' => 2, 'tipo' => 'I', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['sub']			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false,));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[sequencia]'	, 'etiqueta' => 'Seq'		 					, 'linha' => 1, 'largura' => 1, 'tipo' => 'N', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['sequencia']	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false,));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[titulo]'		, 'etiqueta' => 'Titulo'	 					, 'linha' => 1, 'largura' => 9, 'tipo' => 'T', 'tamanho' => '60'	, 'linhas' => '', 'valor' => $dados['titulo']		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 60));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[tituloMeta]'	, 'etiqueta' => 'Titulo Meta'	 				, 'linha' => 2, 'largura' => 6, 'tipo' => 'T', 'tamanho' => '60'	, 'linhas' => '', 'valor' => $dados['tituloMeta']	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 30));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[tituloReal]'	, 'etiqueta' => 'Titulo Realizado'				, 'linha' => 2, 'largura' => 6, 'tipo' => 'T', 'tamanho' => '60'	, 'linhas' => '', 'valor' => $dados['tituloReal']	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 30));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[meta]'	 	, 'etiqueta' => 'Meta'		 					, 'linha' => 3, 'largura' => 3, 'tipo' => 'A', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['meta']			, 'lista' => $tabMeta	, 'validacao' => '', 'obrigatorio' => false));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[impMeta]'	 	, 'etiqueta' => 'Imprime Meta'					, 'linha' => 3, 'largura' => 3, 'tipo' => 'A', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['impMeta']		, 'lista' => $tabImpMeta, 'validacao' => '', 'obrigatorio' => false));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[impReal]'	 	, 'etiqueta' => 'Imprime Realizado'				, 'linha' => 3, 'largura' => 3, 'tipo' => 'A', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['impReal']		, 'lista' => $tabImpReal, 'validacao' => '', 'obrigatorio' => false));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[origem]'	 	, 'etiqueta' => 'Origem Venda'					, 'linha' => 3, 'largura' => 3, 'tipo' => 'A', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['origem']		, 'lista' => $origens	, 'validacao' => '', 'obrigatorio' => false));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[tipo]'		, 'etiqueta' => 'Tipo'		 					, 'linha' => 4, 'largura' => 4, 'tipo' => 'A', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['tipo']			, 'lista' => $tabTipo	, 'validacao' => '', 'obrigatorio' => false));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[vendaItem]'	, 'etiqueta' => 'Mostrar venda por item'		, 'linha' => 4, 'largura' => 4, 'tipo' => 'A', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['vendaItem']	, 'lista' => $tabImpMeta, 'validacao' => '', 'obrigatorio' => false));
			//$form->addCampo(array('id' => '', 'campo' => 'formInc[campanhaWT]'	, 'etiqueta' => 'Campanha WT'					, 'linha' => 4, 'largura' => 4, 'tipo' => 'T', 'tamanho' => '10'	, 'linhas' => '', 'valor' => $dados['campanhaWT']	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[itens]'		, 'etiqueta' => 'Itens'		 					, 'linha' => 5, 'largura' => 6, 'tipo' => 'TA','tamanho' => '60'	, 'linhas' => '', 'valor' => $dados['itens']		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false	, 'linhas' => 20));
			//$form->addCampo(array('id' => '', 'campo' => 'formInc[ativo]'		, 'etiqueta' => 'Ativo'		 					, 'linha' => 2, 'largura' => 4, 'tipo' => 'A' , 'tamanho' => '1'	, 'linhas' => '', 'valor' => $dados["ativo"]		, 'lista' => $sn		, 'validacao' => '', 'obrigatorio' => false));
			$help = "Indica o valor mínimo de venda para o cliente/produto ser contabilizado positivado.";
			$valMinimo = str_replace('.', ',', $dados['min_positivacao']);
			$form->addCampo(array('id' => '', 'campo' => 'formInc[min_positivacao]'	, 'etiqueta' => 'Vl. minimo POSITIVAÇÃO'	, 'linha' => 5, 'largura' => 3, 'tipo' => 'T', 'tamanho' => '60'	, 'linhas' => '', 'valor' => $valMinimo	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'help' => $help));
			
			$help = "Indica se será mostrada coluna com o percentual realizado acima da meta.";
			$form->addCampo(array('id' => '', 'campo' => 'formInc[percent_acima]'	, 'etiqueta' => 'Mostra % acima da meta?'	, 'linha' => 6, 'largura' => 3, 'tipo' => 'A', 'tamanho' => '60'	, 'linhas' => '', 'valor' => $dados['percent_acima'] , 'lista' => $tabImpMeta	, 'validacao' => '', 'obrigatorio' => false, 'help' => $help));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[titulo_percent]'	, 'etiqueta' => 'Titulo % acima da meta'	, 'linha' => 6, 'largura' => 9, 'tipo' => 'T', 'tamanho' => '60'	, 'linhas' => '', 'valor' => $dados['titulo_percent'], 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 60));
			
			$form->setEnvio($url, 'subCampanhaForm', 'subCampanhaForm');
			
			if($erro != ''){
				addPortalMensagem('Erro de cadastro: '.$erro, 'error');
			}
			
			//$param = array();
			$p = array();
			$p['onclick'] = "setLocation('".getLink()."sub&id=$campanha')";
			$p['tamanho'] = 'pequeno';
			$p['cor'] = 'danger';
			$p['texto'] = 'Cancelar';
			//$param['botoesTitulo'][] = $p;
			//$ret = addBoxInfo($nome.' - '.$titulo.' Sub Campanha', $mensagem.$form, $param);
			$ret = addCard(array('titulo' => $nome . ' - ' . $titulo . ' Sub Campanha', 'conteudo' => $mensagem.$form, 'botoesTitulo' => array($p)));
		}else{
			$ret = $this->browser2();
		}
		return $ret;
	}
	
	private function formSubMetas($id = '',$dados = array(), $erro = ''){
		$ret = '';
		$mensagem = '';
		$campanha = getAppVar('manut_campanha2017_editarSub');
		$sub = getAppVar('manut_campanha2017_editarSub_metas');
		
		$this->getCampanhas($campanha);
		$tipoMeta = $this->_campanhas[0]['tipoMeta'];
		
		if(!empty($campanha) && strlen($campanha) == 15 && !empty($sub) && strlen($sub) == 15){
			$form = new form01();
			$url = getLink().'sub.gravarMetas&id='.$campanha;
			
			$this->getCampanhas($campanha);
			$this->getSubCampanhas($campanha,true,$sub);
			
			$campanhaDesc = $this->_campanhas[0]['titulo'];
			$subDesc = $this->_subCampanhas[0]['titulo'];
			$metas = $this->getMetasString($campanha, $sub);
			if($metas == ''){
				$metas = "345;130000\n122;40000,55\n232;56780,66";
			}
			
			$vendedor = $this->_campanhas[0]['vendedor'];
			$vendDesc = $this->getTipoVendedor(false)[$vendedor];
			$form->addHidden('formInc[vendedor]', $vendedor);
			
			if($tipoMeta == 'E'){
				$texto = "Digite em cada linha o Cod $vendDesc;  (ponto e virgula) Valor meta - Ver exemplo abaixo:";
			}else{
				$texto = "Digite em cada linha o Código do Cliente; (ponto e virgula) Valor meta - Ver exemplo abaixo:";
			}
			$form->addCampo(array('id' => '', 'campo' => 'formInc[campanha]'	, 'etiqueta' => 'Campanha'	 , 'linha' => 1, 'largura' => 6, 'tipo' => 'I', 'tamanho' => '10'	, 'linhas' => ''	, 'valor' => $campanhaDesc	, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false,));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[sub]'			, 'etiqueta' => 'Sub'		 , 'linha' => 1, 'largura' => 6, 'tipo' => 'I', 'tamanho' => '10'	, 'linhas' => ''	, 'valor' => $subDesc		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false,));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[dica]'		, 'etiqueta' => 'Dica'		 , 'linha' => 2, 'largura' =>12, 'tipo' => 'I', 'tamanho' => '60'	, 'linhas' => ''	, 'valor' => $texto			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 60));
			$form->addCampo(array('id' => '', 'campo' => 'formInc[metas]'		, 'etiqueta' => 'Metas'		 , 'linha' => 3, 'largura' =>12, 'tipo' => 'TA','tamanho' => '40'	, 'linhasTA' => '15'	, 'valor' => $metas			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => false));
			
			
			
			$form->setEnvio($url, 'subCampanhaForm', 'subCampanhaForm');
			
			if($erro != ''){
				addPortalMensagem('Erro de cadastro: '.$erro, 'error');
			}
			//$param = array();
			$p = array();
			$p['onclick'] = "setLocation('".getLink()."sub&id=$campanha')";
			$p['tamanho'] = 'pequeno';
			$p['cor'] = 'danger';
			$p['texto'] = 'Cancelar';
			//$param['botoesTitulo'][] = $p;
			//$ret = addBoxInfo('Sub Campanhas - Metas', $mensagem.$form, $param);
			$ret = addCard(array('titulo' => 'Sub Campanhas - Metas', 'conteudo' => $mensagem.$form, 'botoesTitulo' => array($p)));
		}else{
			$ret = $this->browser2();
		}
		return $ret;
	}
	
	private function browser1(){
		$ret = '';
		$this->jsConfirmaCopia('"Confirma a copia? \nCampanha: "+desc');
		$this->jsConfirmaExclusao('"Confirma a exclusão? \nCampanha: "+desc');
		$this->getCampanhas();
		
		$param = array();
		$param['paginacao'] = false;
		$param['width'] = 'AUTO';
		$param['titulo'] = 'Manutenção Campanhas';
		$browse = new tabela01($param);
		$browse->setDados($this->_campanhas);
		
		$browse->addColuna(array('campo' => 'seq'		, 'etiqueta' => 'ID'			, 'width' =>  80, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'titulo'	, 'etiqueta' => 'Titulo'		, 'width' => 200, 'posicao' => 'E'));
		$browse->addColuna(array('campo' => 'ini'		, 'etiqueta' => 'Inicio'		, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'fim'		, 'etiqueta' => 'Fim'			, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'fechamento', 'etiqueta' => 'Fechamento'	, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'vendedor'	, 'etiqueta' => 'Venda de'		, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'enviaEmail', 'etiqueta' => 'Email'			, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'ativo'		, 'etiqueta' => 'Ativo'			, 'width' =>  80, 'posicao' => 'C'));
	
		
		$param = [];
		$param['titulo'] = 'Ações';
		$param['width'] 	= 100;

		$i = 0;
		$param['opcoes'][$i]['texto'] 	= 'Copiar';
		$param['opcoes'][$i]['link'] 	= "javascript:confirmaCopia('".getLink()."copiar&id=','{ID}',{COLUNA:titulo})";
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= ''; 
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Editar';
		$param['opcoes'][$i]['link'] 	= getLink().'editar&id=';
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Sub Campan.';
		$param['opcoes'][$i]['link'] 	= getLink().'sub&id=';
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Metas';
		$param['opcoes'][$i]['link'] 	= getLink().'metas&id=';
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Premiações';
		$param['opcoes'][$i]['link'] 	= getLink().'premiacoes&id=';
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Excluir';
		$param['opcoes'][$i]['link'] 	= "javascript:confirmaExclusao('".getLink()."excluir&id=','{ID}',{COLUNA:titulo})";
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'LOG';
		$param['opcoes'][$i]['link'] 	= getLink().'download&id=';
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$browse->addAcaoDropdown($param);

		$novoBotao = array();
		//$novoBotao['icone'] 	= 'fa-arrow-right';
		$novoBotao['cor'] 		= 'success';
		$novoBotao['texto'] 	= 'Incluir Campanha';
		$novoBotao['tamanho'] = 'pequeno';
		$novoBotao['id'] 		= 'bt_icluir';
		$novoBotao['onclick']	= "setLocation('".getLink()."incluir')";
		$browse->addBotaoTitulo($novoBotao);
		
		$ret .= $browse;
		
		return $ret;
		
	}
	
	private function browser2(){
		$ret = '';
		$id = getAppVar('manut_campanha2017_editarSub');
		$nome = getAppVar('manut_campanha_titulo');
//echo "ID: $id <br>\n";		
		$this->jsConfirmaCopia('"Confirma a copia? \nSub Campanha: "+desc');
		$this->jsConfirmaExclusao('"Confirma a exclusão? \nSub Campanha: "+desc');
		
		//$this->jsConfirmaCopia();
		$this->getSubCampanhas($id, false);
		
		$param = array();
		$param['paginacao'] = false;
		$param['width'] = 'AUTO';
		$param['titulo'] = $nome.' - Sub Campanhas';
		$browse = new tabela01($param);
		$browse->setDados($this->_subCampanhas);
		
		$browse->addColuna(array('campo' => 'sub'			, 'etiqueta' => 'Sub ID'		, 'width' =>  80, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'sequencia'		, 'etiqueta' => 'Seq'			, 'width' =>  80, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'titulo'		, 'etiqueta' => 'Titulo'		, 'width' => 200, 'posicao' => 'E'));
		$browse->addColuna(array('campo' => 'meta'			, 'etiqueta' => 'Meta'			, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'impMeta'		, 'etiqueta' => 'Imp.Meta'		, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'impReal'		, 'etiqueta' => 'Imp.Realiz.'	, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'tipo'			, 'etiqueta' => 'Tipo'			, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'campanhaWT'	, 'etiqueta' => 'Campanha WT'	, 'width' => 100, 'posicao' => 'C'));
		//$browse->addColuna(array('campo' => 'ativo'			, 'etiqueta' => 'Ativo'		, 'width' =>   80, 'posicao' => 'C'));
		
		$param = [];
		$param['titulo'] = 'Ações';
		$param['width'] 	= 100;
		
		$i = 0;
		$param['opcoes'][$i]['texto'] 	= 'Copiar';
		$param['opcoes'][$i]['link'] 	= "javascript:confirmaCopia('".getLink()."sub.copiar&id=','{ID}',{COLUNA:titulo})";
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Editar';
		$param['opcoes'][$i]['link'] 	= getLink()."sub.editar&id=";
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';

		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Metas';
		$param['opcoes'][$i]['link'] 	= getLink()."sub.metas&id=";
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$i++;
		$param['opcoes'][$i]['texto'] 	= 'Excluir';
		$param['opcoes'][$i]['link'] 	= "javascript:confirmaExclusao('".getLink()."sub.excluir&id=','{ID}',{COLUNA:titulo})";
		$param['opcoes'][$i]['coluna'] 	= 'id';
		$param['opcoes'][$i]['flag'] 	= '';
		//$param['opcoes'][$i]['onclick'] 	= '';
		
		$browse->addAcaoDropdown($param);
		
		$novoBotao = array();
		//$novoBotao['icone'] 	= 'fa-arrow-right';
		$novoBotao['cor'] 		= 'success';
		$novoBotao['texto'] 	= 'Incluir Sub Campanha';
		$novoBotao['tamanho'] = 'pequeno';
		$novoBotao['id'] 		= 'bt_icluir';
		$novoBotao['onclick']	= "setLocation('".getLink()."sub.incluir&id=$id')";
		$browse->addBotaoTitulo($novoBotao);
		
		$botaoCancela = array();
		$botaoCancela['onclick'] = "setLocation('".getLink()."index')";
		$botaoCancela['tamanho'] = 'pequeno';
		$botaoCancela['cor'] = 'danger';
		$botaoCancela['texto'] = 'Cancelar';
		$browse->addBotaoTitulo($botaoCancela);
		
		$ret .= $browse;
		
		return $ret;
		
	}
	
	private function browser3(){
		$ret = '';
		$campanha = getAppVar('manut_campanha2017_metas');
		$formID = 'formMetas';
		$acaoForm = getLink().'metas.gravar&id='.$campanha;
		
		if(!empty($campanha) && strlen($campanha) == 15){
			$this->getVendedores();
			$this->getCampanhas($campanha);
			$campanhaDados = $this->_campanhas[0];
			$vendedor = $campanhaDados['vendedor'];
			$vendDesc = $this->getTipoVendedor(false)[$vendedor];
			$this->jsConfirmaExclusao('"Confirma a EXCLUSAO? \n\n '.$vendDesc.':"+id+" - "+desc');
			
			$dados = $this->getMetas($campanha, $vendedor);
			
			$param = array();
			$param['paginacao'] = false;
			$param['width'] = 'AUTO';
			$param['titulo'] = 'Editar Metas Campanha: '.$campanhaDados['titulo'];
			$browse = new tabela01($param);
			$browse->setDados($dados);
			
			$etiqCod = 'ERC';
			$etiqNome = 'Nome ERC';
			if($vendedor == 'S'){
				$etiqCod = 'Regiao';
				$etiqNome = 'Nome Regiao';
			}elseif($vendedor == 'T' || $vendedor == 'X'){
				$etiqCod = 'Operador';
				$etiqNome = 'Nome Operador';
			}
			
			if($vendedor == 'E'){
				$browse->addColuna(array('campo' => 'super'		, 'etiqueta' => 'Supervisor'		, 'width' =>  80, 'posicao' => 'C'));
				$browse->addColuna(array('campo' => 'superNome'	, 'etiqueta' => 'Nome<br>Supervidor', 'width' => 100, 'posicao' => 'E'));
			}
			$browse->addColuna(array('campo' => 'erc'		, 'etiqueta' => $etiqCod				, 'width' =>  80, 'posicao' => 'C'));
			$browse->addColuna(array('campo' => 'ercNome'	, 'etiqueta' => $etiqNome				, 'width' => 200, 'posicao' => 'E'));
			
			foreach ($this->_subCampanhas as $subCampanha){
				$sub = 'meta'.$subCampanha['id'];
				$etiqueta = $subCampanha['titulo'];
				$browse->addColuna(array('campo' => $sub, 'etiqueta' => $etiqueta	, 'width' =>  120, 'posicao' => 'D'));
			}
			
			$param = array();
			$param["texto"] = "Excluir";
			$param["link"] 	= "javascript:confirmaExclusao('".getLink()."metas.excluir&vendedor=".$vendedor."&id=','{ID}',{COLUNA:ercNome})";
			$param["coluna"]= 'erc';
			$param["flag"] 	= "";
			$param["width"] = 100;
			$browse->addAcao($param);
	
			$novoBotao = array();
			//$novoBotao['icone'] 	= 'fa-arrow-right';
			$novoBotao['cor'] 		= 'success';
			$novoBotao['texto'] 	= 'Incluir Meta';
			$novoBotao['tamanho'] = 'pequeno';
			$novoBotao['id'] 		= 'bt_icluir';
			$novoBotao['onclick']	= "setLocation('".getLink()."metas.incluirERC&id=".$campanha."&vendedor=".$vendedor."')";
			$browse->addBotaoTitulo($novoBotao);
			
			$novoBotao = array();
			//$novoBotao['icone'] 	= 'fa-arrow-right';
			$novoBotao['cor'] 		= 'success';
			$novoBotao['texto'] 	= 'Gravar';
			$novoBotao['tamanho'] 	= 'pequeno';
			$novoBotao['id'] 		= 'bt_gravar';
			$novoBotao["onclick"]	= "$('#".$formID."').submit();";
			$browse->addBotaoTitulo($novoBotao);
			
			$botaoCancela = array();
			$botaoCancela['onclick'] = "setLocation('".getLink()."index')";
			$botaoCancela['tamanho'] = 'pequeno';
			$botaoCancela['cor'] = 'danger';
			$botaoCancela['texto'] = 'Cancelar';
			$browse->addBotaoTitulo($botaoCancela);
			
			$ret .= $browse;
			$param = array();
			$param['acao'] 	= $acaoForm;
			$param['nome'] 	= $formID;
			$param['id']	= $formID;
			//$ret = formbase01::formForm($param, $ret);
			$ret = formbase01::form($param, $ret);
		}
		
		return $ret;
		
	}
	
	private function browser4(){
		$ret = '';
		$id = getAppVar('manut_campanha2017_premiacoes');
		$nome = getAppVar('manut_campanha_titulo');
		$formID = 'formGravaPremiacao';
		
		$this->getSubCampanhas($id, false);
		
		//Lista de sub campanhas
		$listaSub = [['','']];
		foreach ($this->_subCampanhas as $campanha){
			$temp = [];
			$temp[] = $campanha['id'];
			$temp[] = $campanha['titulo'];
			
			$listaSub[] = $temp;
		}
		
		//Recupera valores
		$valores = $this->recuperaPremioMixPos($id);
		
		// Identificação GLOBAL x MIX e GLOBAL x Positivação
		$form = new form01();
		$form->addCampo(['campo'=> 'global[camp_global]', 'etiqueta'=> 'Campanha Global'		, 'tipo'=> 'A', 'obrigatorio'=> true, 'valor' => $valores['prem_global']	, 'tamanho'=> '15', 'linha'=> '1', 'lista' => $listaSub	, 'largura'=> '6']);
		$form->addCampo(['campo'=> 'global[camp_mix]'	  , 'etiqueta'=> 'Campanha MIX'			, 'tipo'=> 'A', 'obrigatorio'=> true, 'valor' => $valores['prem_mix']		, 'tamanho'=> '15', 'linha'=> '2', 'lista' => $listaSub	, 'largura'=> '6']);
		$form->addCampo(['campo'=> 'global[perc_mix]'	  , 'etiqueta'=> '% Prêmio'				, 'tipo'=> 'V', 'obrigatorio'=> true, 'valor' => $valores['prem_perc_mix']	, 'tamanho'=> ' 5', 'linha'=> '2'						, 'largura'=> '3', 'help' => 'Percentual do prêmio']);
		$form->addCampo(['campo'=> 'global[camp_pos]'	  , 'etiqueta'=> 'Campanha Positivação'	, 'tipo'=> 'A', 'obrigatorio'=> true, 'valor' => $valores['prem_pos']		, 'tamanho'=> '15', 'linha'=> '3', 'lista' => $listaSub	, 'largura'=> '6']);
		$form->addCampo(['campo'=> 'global[perc_pos]'	  , 'etiqueta'=> '% Prêmio'				, 'tipo'=> 'V', 'obrigatorio'=> true, 'valor' => $valores['prem_perc_pos']	, 'tamanho'=> ' 5', 'linha'=> '3'						, 'largura'=> '3', 'help' => 'Percentual do prêmio']);
		$form->addCampo(['campo'=> 'global[camp_enc]'	  , 'etiqueta'=> 'Campanha Encarte'		, 'tipo'=> 'A', 'obrigatorio'=> true, 'valor' => $valores['prem_enc']		, 'tamanho'=> '15', 'linha'=> '3', 'lista' => $listaSub	, 'largura'=> '6']);
		$form->addCampo(['campo'=> 'global[perc_enc]'	  , 'etiqueta'=> '% Prêmio'				, 'tipo'=> 'V', 'obrigatorio'=> true, 'valor' => $valores['prem_perc_enc']	, 'tamanho'=> ' 5', 'linha'=> '3'						, 'largura'=> '3', 'help' => 'Percentual do prêmio']);
		
		$ret .= $form;
		
		$param = array();
		$param['titulo'] = 'Global x Mix x Positivação';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		$param = array();
		$param['paginacao'] = false;
		$param['width'] = 'AUTO';
		$param['titulo'] = $nome.' - Premiações';
		$browse = new tabela01($param);
		$dados = $this->getFormPremiacoes($id);
		$browse->setDados($dados);
		
		$browse->addColuna(array('campo' => 'seq'		, 'etiqueta' => 'Seq'			, 'width' =>  80, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'titulo'	, 'etiqueta' => 'Subcampanha'	, 'width' => 400, 'posicao' => 'E'));
		$browse->addColuna(array('campo' => 'ating'		, 'etiqueta' => '% Mínimo'		, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'premio'	, 'etiqueta' => 'Premio'		, 'width' => 100, 'posicao' => 'C'));
		$browse->addColuna(array('campo' => 'tipo'		, 'etiqueta' => 'Tipo'			, 'width' => 200, 'posicao' => 'C'));
		
		$novoBotao = [];
		$novoBotao['cor'] 		= 'success';
		$novoBotao['texto'] 	= 'Gravar';
		$novoBotao['tamanho'] 	= 'pequeno';
		$novoBotao['id'] 		= 'bt_gravar';
		$novoBotao["onclick"]	= "$('#".$formID."').submit();";
		$browse->addBotaoTitulo($novoBotao);
		
		$botaoCancela = array();
		$botaoCancela['onclick'] = "setLocation('".getLink()."index')";
		$botaoCancela['tamanho'] = 'pequeno';
		$botaoCancela['cor'] = 'danger';
		$botaoCancela['texto'] = 'Cancelar';
		$browse->addBotaoTitulo($botaoCancela);
		
		$ret .= $browse;
		

		
		$param = array();
		$param['acao'] 	= getLink().'premiacoes.gravar&id='.$id;;
		$param['nome'] 	= $formID;
		$param['id']	= $formID;
		//$ret = formbase01::formForm($param, $ret);
		$ret = formbase01::form($param, $ret);
		
		return $ret;
		
	}
	
	function jsConfirmaCopia($titulo){
		addPortaljavaScript('function confirmaCopia(link,id,desc){');
		addPortaljavaScript('	if (confirm('.$titulo.')){');
		addPortaljavaScript('		setLocation(link+id);');
		addPortaljavaScript('	}');
		addPortaljavaScript('}');
	}
	
	function jsConfirmaExclusao($titulo){
		addPortaljavaScript('function confirmaExclusao(link,id,desc){');
		addPortaljavaScript('	if (confirm('.$titulo.')){');
		addPortaljavaScript('		setLocation(link+id);');
		addPortaljavaScript('	}');
		addPortaljavaScript('}');
	}
	
	//----------------------------------------------------------------------------------------------------------------- Uteis

	private function verificaErrosCampanha($dados){
		$erro = '';

		$dataIni = datas::dataD2S($dados['ini']);
		$dataFim = datas::dataD2S($dados['fim']);
		$dataFecha = datas::dataD2S($dados['fechamento']);
		
		if($dados['titulo'] == ''){
			$erro .= "Deve ser informado um titulo.<br>\n";
		}
		if($dados['ini'] == ''){
			$erro .= "Deve ser informada a data de inicio.<br>\n";
		}
		if($dados['fim'] == ''){
			$erro .= "Deve ser informada a data de fim.<br>\n";
		}
		if($dados['fechamento'] == ''){
			$erro .= "Deve ser informada a data de fechamento .<br>\n";
		}
		if($dataIni > $dataFim && $dados['ini'] != '' && $dados['fim'] != ''){
			$erro .= "A data final deve ser maior que a inicial.<br>\n";
		}
		if($dataFim > $dataFecha && $dados['fechamento'] != '' && $dados['fim'] != ''){
			$erro .= "A data de fechamento deve ser maior ou igual a data final.<br>\n";
		}
		
		return $erro;
	}
	
	private function verificaErrosSubCampanha($dados){
		$erro = '';
		
		if($dados['titulo'] == ''){
			$erro .= "Deve ser informado um titulo.<br>\n";
		}
		if($dados['meta'] == ''){
			$erro .= "Deve ser informada um tipo de meta.<br>\n";
		}
		if($dados['tipo'] == ''){
			$erro .= "Deve ser informada o tipo de campanha.<br>\n";
		}
		if($dados['itens'] == '' && $dados['campanhaWT'] == '' && $dados['tipo'] != 'G'){
			$erro .= "Deve ser informada os itens da campanha ou código da campanha no WinThor ou ser 'Sem filtro'.<br>\n";
		}
		
		return $erro;
	}
	
	private function validaERC($erc, $vendedor = 'E'){
		$ret = false;
		$tipoMeta = $this->_campanhas[0]['tipoMeta'];
//echo "Tipo meta: $tipoMeta <br>\n";
		if($tipoMeta == 'C'){
			$sql = "select codcli from pcclient where codcli = 	 $erc ";
		}else{
			switch ($vendedor) {
				case 'S':
					$sql = "select codsupervisor from pcsuperv where codsupervisor = $erc AND posicao = 'A'";
					break;
				case 'T':
				case 'X':
					$sql = "SELECT MATRICULA FROM PCEMPR WHERE CODPERFILTELEVMED IS NOT NULL AND DTDEMISSAO IS NULL AND SITUACAO = 'A' AND MATRICULA = $erc";
					break;
				default:
					$sql = "select codusur from pcusuari where codusur = $erc ";
					break;
			}
		}
//echo "$sql <br>\n";
		$rows = query4($sql);
		if(count($rows) > 0){
			$ret = true;
		}
		
		return $ret;
	}
	
	private function getForm($erc,$campo,$valor){
		$ret = '';
		
		$param = array();
		$param['nome'] = "meta[$erc][$campo]";
		$param['style'] = "text-align: right";
		$param['valor'] = $valor;
		$param['maxtamanho'] = 14;
		$param['tamanho'] = 10;
		
		$ret .= formbase01::formTexto($param);
		
		return $ret;
	}
	
	private function getFormAddERC(){
		global $nl;
		$ret = '';
		$formulario = '';
		$campanha = getAppVar('manut_campanha2017_metas');
		$vendedor = $_GET['vendedor'];
		$ercLivres = $this->getERClivres($campanha, $vendedor);

		$formulario = '<center>';
		$formulario .= $nl.'<br><br>';
		
		$param = array();
		$param['nome'] 		= 'erc';
		$param['lista'] 	= $ercLivres;
		$formulario .= formbase01::formSelect($param);
		$formulario .= $nl.'<br><br>';
		
		$formulario .= formbase01::formSend(array());
		$formulario .= '</center>';
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-4"></div>'.$nl;
		$ret .= '	<div  class="col-md-4">'.$formulario.'</div>'.$nl;
		$ret .= '	<div  class="col-md-4"></div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = array();
		$param['acao'] 	= getLink().'metas.incluirERCgravar&id='.$campanha.'&vendedor='.$vendedor;
		$param['nome'] 	= 'addERCForm';
		$param['id']	= 'copiarForm';
		//$form = formbase01::formForm($param, $ret);
		$form = formbase01::form($param, $ret);
		
		$this->getCampanhas($campanha);
		
		//$param = array();
		$p = array();
		$p['onclick'] = "setLocation('".getLink()."sub&id=$campanha')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Cancelar';
		//$param['botoesTitulo'][] = $p;
		//$ret = addBoxInfo('Incluir ERC em  '.$this->_campanhas[0]['titulo'], $form, $param);
		$ret = addCard(array('titulo' => 'Incluir ERC em  '.$this->_campanhas[0]['titulo'], 'conteudo' => $form, 'botoesTitulo' => array($p)));
		return $ret;
	}
	
	private function log($msg, $campanha = 'generica'){
		$arq = 'manutencao_campanhas'.DIRECTORY_SEPARATOR.$campanha;
		log::gravaLog($arq, $msg);
		
	}
	
	private function getERClivres($campanha, $vendedor = 'E'){
		$comMeta = array();
		$ret = array();
		$this->getVendedores();
		
		$sql = "SELECT DISTINCT erc FROM gf_camp_metas WHERE campanha = '$campanha'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$comMeta[$row['erc']] = $row['erc'];
			}
		}
//print_r($comMeta);		
		switch ($vendedor) {
			case 'S':
				$lista = $this->_super;
				break;
			case 'T':
			case 'X':
				$lista = $this->_operadores;
				break;
			default:
				$lista = $this->_erc;
				break;
		}
//print_r($lista);		
		foreach ($lista as $cod => $cad){
			if(!isset($comMeta[$cod])){
				$ret[] = array($cod , $cod.' - '.$cad['nome']);
			}
		}
		
		return $ret;
	}
	//------------------------------------------------------------------------------------------------------------------- VO
	
	private function getCampanhas($id = ''){
		$this->_campanhas = array();
		$where = '';
		if($id != ''){
			$where = "WHERE id = '$id' ";
		}
		$sql = "SELECT * FROM gf_camp_campanhas
		$where
		ORDER BY seq DESC";
//echo "$sql <br>\n";
		$rows = query($sql);
//print_r($rows);
		foreach ($rows as $row) {
			$temp = array();
			
			$temp['seq'			] = $row['seq'];
			$temp['id'			] = $row['id'];
			$temp['titulo'		] = $row['titulo'];
			$temp['ini'			] = datas::dataS2D($row['ini']);
			$temp['fim'			] = datas::dataS2D($row['fim']);
			$temp['fechamento'	] = datas::dataS2D($row['fechamento']);
			$temp['totalReal'	] = $row['totalReal'];
			$temp['totalMeta'	] = $row['totalMeta'];
			$temp['vendedor'	] = $row['vendedor'];
			$temp['enviaEmail'	] = $row['enviaEmail'];
			$temp['erc_fora'	] = $row['erc_fora'];
			$temp['ped_fora'	] = $row['ped_fora'];
			$temp['cli_fora'	] = $row['cli_fora'];
			$temp['origCli'		] = $row['origCli'];
			$temp['tipoMeta'	] = $row['tipoMeta'];
			$temp['ativo'		] = $row['ativo'];
			$temp['email_para'	] = $row['email_para'];
			$temp['porCliente'	] = $row['porCliente'];
			$temp['cliSemVenda'	] = $row['cliSemVenda'];
			$temp['ercSemMeta'	] = $row['ercSemMeta'];
			
			$this->_campanhas[] = $temp;
			
		}
//print_r($this->_campanhas);
		return;
	}
	
	private function getSubCampanhas($campanha, $abreviado = true, $sub = ''){
		$this->_subCampanhas = array();
		$tipoMeta = [];
		$tipoMeta['V'] = 'Valor';
		$tipoMeta['Q'] = 'Quantidade';
		$tipoMeta['M'] = 'Mix';
		$tipoMeta['P'] = 'Positivação - Produto';
		$tipoMeta['N'] = 'Preço Médio';
		$tipoMeta['C'] = 'Positivação - Cliente';
		
		$impMeta = array('S' => 'Sim','N' => 'Nao');
		$tipoTipo = array('P' => 'Produto', 'F' => 'Fornecedor', 'M' => 'Marca', 'D' => 'Departamento', 'K' => 'KIT', 'G' => 'Sem filtro');
		
		$whereSub = '';
		if($sub != ''){
			$whereSub = " AND id = '$sub' ";
		}

		$sql = "SELECT * FROM  gf_camp_subcamp WHERE campanha = '$campanha' $whereSub ORDER BY sequencia";
//echo "$sql \n";
		$rows = query($sql);
//print_r($rows);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row) {
				$temp = array();
				$temp['id'			] = $row['id'];
				$temp['sub'			] = $row['sub'];
				$temp['sequencia'	] = $row['sequencia'];
				$temp['campanha'	] = $row['campanha'];	
				$temp['sub'			] = $row['sub'];
				$temp['titulo'		] = $row['titulo'];
				$temp['tituloMeta'		] = $row['tituloMeta'];
				$temp['tituloReal'		] = $row['tituloReal'];
				if($abreviado){
					$temp['meta'	] = $row['meta'];
					$temp['impMeta'	] = $row['impMeta'];
					$temp['impReal'	] = $row['impReal'];
					$temp['tipo'	] = $row['tipo'];
				}else{
					$temp['meta'	] = $tipoMeta[$row['meta']];
					$temp['impMeta'	] = $impMeta[$row['impMeta']];
					$temp['impReal'	] = $impMeta[$row['impReal']];
					$temp['tipo'	] = $tipoTipo[$row['tipo']];
				}
				$temp['campanhaWT'	] 	= $row['campanhaWT'];
				$temp['origem'		]  	= $row['origem'];
				$temp['vendaItem'	]  	= $row['vendaItem'];
				$temp['min_positivacao']= $row['min_positivacao'];
				$temp['percent_acima']	= $row['percent_acima'];
				$temp['titulo_percent']	= $row['titulo_percent'];
				$temp['ativo'		] 	= $row['ativo'];
				
				$this->_subCampanhas[] = $temp;
				
			}
		}
//print_r($this->_subCampanhas);
		return;
	}
	
	private function getMetas($campanha, $vendedor = 'E'){
		$ret = array();
		$metas = array();
		$temporario= array();
		
		$this->getSubCampanhas($campanha);
		$this->getVendedores();
		
		$sql = "SELECT * FROM gf_camp_metas WHERE campanha = '$campanha'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$sub = $row['sub'];
				$metas[$row['erc']][$sub] = $row['valor'];
			}
		}
//print_r($metas);		
		foreach ($metas as $erc => $meta){
			$temp = array();
			
			//Form com o valor
//print_r($this->_subCampanhas);
			foreach ($this->_subCampanhas as $sub){
				$subCamp = $sub['id'];
				$valor = isset($meta[$subCamp]) ? $meta[$subCamp]: 0;
				$temp['meta'.$subCamp] = $this->getForm($erc,$subCamp,$valor);
			}
			
			if($vendedor == 'E'){
				$super = isset($this->_erc[$erc]['super']) ? $this->_erc[$erc]['super'] : '';
				$temp['super'	] 	= $super;
				$temp['superNome'] 	= isset($this->_super[$super]['nome']) ? $this->_super[$super]['nome'] : '';
				$temp['erc'		] 	= $erc;
				$temp['ercNome'	] 	= isset($this->_erc[$erc]['nome']) ? $this->_erc[$erc]['nome'] : '';
				
				$temporario[$super][$erc] = $temp;
			}elseif($vendedor == 'S'){
				$temp['erc'		] 	= $erc;
				$temp['ercNome'	] 	= $this->_super[$erc]['nome'];
				$temporario[] = $temp;
			}elseif($vendedor == 'T' || $vendedor == 'X'){
				if(isset($this->_operadores[$erc]['nome'])){
					$temp['erc'		] 	= $erc;
					$temp['ercNome'	] 	= $this->_operadores[$erc]['nome'];
					$temporario[] = $temp;
				}else{
					$this->excluiMeta($campanha, $erc);
				}
			}
		}
		
		if($vendedor == 'E'){
			foreach ($temporario as $temp){
				foreach ($temp as $t){
					$ret[] = $t;
				}
			}
		}else{
			$ret = $temporario;
		}

//print_r($ret);
		return $ret;
	}
	
	private function getFormPremiacoes($campanha){
		$ret = [];
		
		$subCampanhas = $this->_subCampanhas;
		$premiacoes = $this->getPremiacoes($campanha);
		
		foreach ($subCampanhas as $sub){
			$temp = [];
			$subID = $sub['id'];
			
			$temp['seq'] = $sub['sequencia'];
			$temp['titulo'] = $sub['titulo'];
			$temp['ating'] = $this->getCampoPremiacoes($subID, 'ating', $premiacoes[$subID]['ating'] ?? 0);
			$temp['premio'] = $this->getCampoPremiacoes($subID, 'premio', $premiacoes[$subID]['premio'] ?? 0);
			$temp['tipo'] = $this->getCampoPremiacoesTipo($subID, 'tipo', $premiacoes[$subID]['tipo'] ?? 'P');
			
			$ret[] = $temp;
		}

		return $ret;
	}
	
	private function getCampoPremiacoesTipo($sub, $tipo, $valor){
		$ret = '';
		
		$param = [];
		$param['nome'] = "premiacao[$sub][$tipo]";
		$param['valor'] = $valor;
		$param['maxtamanho'] = 14;
		$param['tamanho'] = 10;
		$param['lista'] = [['P', 'Percentual'], ['V', 'Valor'], ['U', 'Unidade Vendida']];
		$ret .= formbase01::formSelect($param);
		
		return $ret;
	}
	
	private function getCampoPremiacoes($sub, $tipo, $valor){
		$ret = '';
		
		$param = [];
		$param['nome'] = "premiacao[$sub][$tipo]";
		$param['style'] = "text-align: right";
		$param['valor'] = $valor;
		$param['maxtamanho'] = 14;
		$param['tamanho'] = 10;
		
		$ret .= formbase01::formTexto($param);
		
		return $ret;
	}
	
	private function getPremiacoes($campanha){
    	$ret = [];
    	
    	$sql = "SELECT * FROM gf_camp_premio WHERE campanha = '$campanha'";
    	$rows = query($sql);
    	
    	if(is_array($rows) && count($rows) > 0){
    		foreach ($rows as $row){
    			$temp = [];
    			$temp['ating'] = $row['atingimento'];
    			$temp['premio'] = $row['premio'];
    			$temp['tipo'] = $row['tipo'];
    			
    			$ret[$row['sub']] = $temp;
    		}
    	}
    	
    	return $ret;
	}
	
	private function gravaPremioMIX($id, $premio_MIX){
		$campos = [];
		
		$campos['prem_global'] 		= $premio_MIX['camp_global'];
		$campos['prem_mix'] 		= $premio_MIX['camp_mix'];
		$campos['prem_pos'] 		= $premio_MIX['camp_pos'];
		$campos['prem_enc'] 		= $premio_MIX['camp_enc'];
		$campos['prem_perc_mix'] 	= ajustaValor($premio_MIX['perc_mix']);
		$campos['prem_perc_pos'] 	= ajustaValor($premio_MIX['perc_pos']);
		$campos['prem_perc_enc'] 	= ajustaValor($premio_MIX['perc_enc']);
		
		$sql = montaSQL($campos, 'gf_camp_campanhas', 'update', "id = '$id'");
		query($sql);
		
	}
	
	private function recuperaPremioMixPos($id){
		$ret = ['prem_global' => '','prem_mix' => '', 'prem_pos' => '', 'prem_enc' => '', 'prem_perc_mix' => '', 'prem_perc_pos' => '', 'prem_perc_enc' => ''];
		
		$sql = "SELECT * FROM gf_camp_campanhas WHERE id = '$id'";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			$ret['prem_global'] 	= $rows[0]['prem_global'];
			$ret['prem_mix'] 		= $rows[0]['prem_mix'];
			$ret['prem_pos'] 		= $rows[0]['prem_pos'];
			$ret['prem_pos'] 		= $rows[0]['prem_pos'];
			$ret['prem_enc'] 		= $rows[0]['prem_enc'];
			$ret['prem_perc_mix'] 	= $rows[0]['prem_perc_mix'];
			$ret['prem_perc_pos'] 	= $rows[0]['prem_perc_pos'];
			$ret['prem_perc_enc'] 	= $rows[0]['prem_perc_enc'];
		}
		
		return $ret;
	}
	
	private function gravarPremiacoes($id){
		$resultados = $_POST['premiacao'];
		
		$premio_MIX = $_POST['global'];	
		$this->gravaPremioMIX($id, $premio_MIX);
		
		if(count($resultados) > 0){
			$sql = "DELETE FROM gf_camp_premio WHERE campanha = '$id'";
			query($sql);
			
			foreach ($resultados as $sub => $result){
				$campos = [];
				$campos['campanha'] 	= $id;
				$campos['sub'] 			= $sub;
				$campos['atingimento'] 	= $result['ating'];
				$campos['premio'] 		= $result['premio'];
				$campos['tipo'] 		= $result['tipo'];
				
				$sql = montaSQL($campos, 'gf_camp_premio');
				query($sql);
				
				$this->log('gravarPremiacoes: '.$sql, $id);
			}
		}
	}
	private function getItensString($campanha, $sub){
		$temp = array();
		$ret = '';
		$sql = "SELECT * FROM gf_camp_itens WHERE campanha = '$campanha' AND sub = '$sub'";
//echo "sql: $sql <br>\n\n";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp[] = $row['valor'];
			}
			
			$ret = implode(',', $temp);
		}
//echo "Itens: $ret <br>\n\n";
		return $ret;
	}
	
	private function getMetasString($campanha, $sub){
		$ret = '';
		$temp = array();
		if($campanha != '' && $campanha != 0 && $sub != '' && $sub != 0){
			$sql = "SELECT * FROM gf_camp_metas WHERE campanha = $campanha AND sub = '$sub'";
			$rows = query($sql);
			if(count($rows) > 0){
				foreach ($rows as $row){
					$temp[] = $row['erc'].','.(int)$row['valor'];
				}
			}
			$ret = implode("\n", $temp);
		}
		
		return $ret;
	}
	/*
	 * Carrega ERCs e Supervisores
	 */
	private function getVendedores(){
		$vend = getListaEmailGF('rca',false,'nome');
		if(count($vend) > 0){
			foreach ($vend as $v){
				$erc = $v['rca'];
				$this->_erc[$erc]['nome'] = $v['nome'];
				$this->_erc[$erc]['email'] = $v['email'];
				$this->_erc[$erc]['super'] = $v['super'];
				
				$super = $v['super'];
				if(!isset($this->_super[$super])){
					$this->_super[$super]['nome'] = $v['super_nome'];
					$this->_super[$super]['email'] = $v['super_email'];
				}
			}
		}
		$sql = "
			SELECT
			    MATRICULA,
			    NOME,
				EMAIL
			FROM
			    PCEMPR
			WHERE
			    CODPERFILTELEVMED IS NOT NULL
			    AND DTDEMISSAO IS NULL
			    AND SITUACAO = 'A'
			";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$operador = $row['MATRICULA'];
				$this->_operadores[$operador]['nome'] = $row['NOME'];
				$this->_operadores[$operador]['email'] = $row['EMAIL'];
			}
		}
	}
	
	private function gravaCampanha($dados){
		$ret = '';
		if($dados['ativo'] == ''){
			$dados['ativo'] = 'S';
		}
		$dataIni = datas::dataD2S($dados['ini']);
		$dataFim = datas::dataD2S($dados['fim']);
		$dataFecha = datas::dataD2S($dados['fechamento']);
		if(!isset($dados['id']) || empty($dados['id']) || strlen($dados['id']) != 15){
			$id = geraID('gf_camp_campanhas');
			$ret = $id;
			$campos = array();
			$campos['id']			= $id;
			$campos['titulo']		= $dados['titulo'];
			$campos['ini']			= $dataIni;
			$campos['fim']			= $dataFim;
			$campos['fechamento']	= $dataFecha;
			$campos['totalReal']	= $dados['totalReal'];
			$campos['totalMeta']	= $dados['totalMeta'];
			$campos['vendedor']		= $dados['vendedor'];
			$campos['enviaEmail']	= $dados['enviaEmail'];
			$campos['erc_fora']		= $dados['erc_fora'];
			$campos['ped_fora']		= $dados['ped_fora'];
			$campos['cli_fora']		= $dados['cli_fora'];
			$campos['origCli']		= $dados['origCli'];
			$campos['ativo']		= $dados['ativo'];
			$campos['tipoMeta'] 	= $dados['tipoMeta'];
			$campos['ativo'] 		= $dados['ativo'];
			$campos['email_para'] 	= $dados['email_para'];
			$campos['porCliente'] 	= $dados['porCliente'];
			$campos['cliSemVenda'] 	= $dados['cliSemVenda'];
			$campos['ercSemMeta'] 	= $dados['ercSemMeta'];
			
			$sql = montaSQL($campos, 'gf_camp_campanhas');
		}else{
			$ret = $dados['id'];
			$campos = array();
			$campos['id']			= $dados['id'];
			$campos['titulo']		= $dados['titulo'];
			$campos['ini']			= $dataIni;
			$campos['fim']			= $dataFim;
			$campos['fechamento']	= $dataFecha;
			$campos['totalReal']	= $dados['totalReal'];
			$campos['totalMeta']	= $dados['totalMeta'];
			$campos['vendedor']		= $dados['vendedor'];
			$campos['enviaEmail']	= $dados['enviaEmail'];
			$campos['erc_fora']		= $dados['erc_fora'];
			$campos['ped_fora']		= $dados['ped_fora'];
			$campos['cli_fora']		= $dados['cli_fora'];
			$campos['origCli']		= $dados['origCli'];
			$campos['ativo']		= $dados['ativo'];
			$campos['tipoMeta'] 	= $dados['tipoMeta'];
			$campos['ativo'] 		= $dados['ativo'];
			$campos['email_para'] 	= $dados['email_para'];
			$campos['porCliente'] 	= $dados['porCliente'];
			$campos['cliSemVenda'] 	= $dados['cliSemVenda'];
			$campos['ercSemMeta'] 	= $dados['ercSemMeta'];
			
			$sql = montaSQL($campos, 'gf_camp_campanhas','UPDATE', "id = '".$dados['id']."'");
		}
		
//echo "$sql <br>\n";die();
		$this->log(__FUNCTION__." $sql", $ret);
		query($sql);
		
		return $ret;
	}
	
	private function gravaSubCampanha($dados, $campanha = '', $sub = ''){
		$ret = '';
		if(!isset($dados['ativo']) || $dados['ativo'] == ''){
			$dados['ativo'] = 'S';
		}
		if($campanha != ''){
			$dados['campanha'] = $campanha;
		}
		if($sub != ''){
			$dados['id'] = $sub;
		}

		if(!isset($dados['sub']) || empty($dados['sub'])){
			$dados['sub'] = $this->getProximaSub($dados['campanha'],'gf_camp_subcamp');
		}
		
		if(!isset($dados['id']) || empty($dados['id']) || strlen($dados['id']) != 15){
			$dados['id'] = geraID('gf_camp_subcamp');
		}
		
		if(!isset($dados['campanhaWT']) || $dados['campanhaWT'] == ''){
			$dados['campanhaWT'] = "NULL";
		}

		$sql = "SELECT * FROM gf_camp_subcamp WHERE campanha = '".$dados['campanha']."' AND id = '".$dados['id']."'";
		$rows = query($sql);
		
		$min_positivacao = str_replace('.', '', $dados['min_positivacao']);
		$min_positivacao = str_replace(',', '.', $min_positivacao);
		
		$campos = array();
		$ret = $dados['id'];
		if(count($rows) == 0){
			$campos['id'] 			= $dados['id'];
			$campos['sequencia'] 	= $dados['sequencia'];
			$campos['campanha'] 	= $dados['campanha'];
			$campos['sub'] 			= $dados['sub'];
			$campos['titulo'] 		= $dados['titulo'];
			$campos['tituloMeta'] 	= $dados['tituloMeta'];
			$campos['tituloReal'] 	= $dados['tituloReal'];
			$campos['meta'] 		= $dados['meta'];
			$campos['impMeta'] 		= $dados['impMeta'];
			$campos['impReal'] 		= $dados['impReal'];
			$campos['tipo'] 		= $dados['tipo'];
			$campos['origem'] 		= $dados['origem'];
			$campos['vendaItem'] 	= $dados['vendaItem'];
			$campos['campanhaWT'] 	= $dados['campanhaWT'];
			$campos['min_positivacao'] 	= $min_positivacao;
			$campos['percent_acima']= $dados['percent_acima'];
			$campos['titulo_percent']= $dados['titulo_percent'];
			$campos['ativo'] 		= $dados['ativo'];
			
			$sql = montaSQL($campos, 'gf_camp_subcamp');
		}else{
			$campos['sequencia'] 	= $dados['sequencia'];
			$campos['titulo'] 		= $dados['titulo'];
			$campos['tituloMeta'] 	= $dados['tituloMeta'];
			$campos['tituloReal'] 	= $dados['tituloReal'];
			$campos['meta'] 		= $dados['meta'];
			$campos['impMeta'] 		= $dados['impMeta'];
			$campos['impReal'] 		= $dados['impReal'];
			$campos['tipo'] 		= $dados['tipo'];
			$campos['origem'] 		= $dados['origem'];
			$campos['vendaItem'] 	= $dados['vendaItem'];
			$campos['campanhaWT'] 	= $dados['campanhaWT'];
			$campos['min_positivacao'] 	= $min_positivacao;
			$campos['percent_acima']= $dados['percent_acima'];
			$campos['titulo_percent']= $dados['titulo_percent'];
			$campos['ativo'] 		= $dados['ativo'];
			
			$sql = montaSQL($campos, 'gf_camp_subcamp','UPDATE', "id = '".$dados['id']."'");

		}
//echo "$sql <br>\n";
		$this->log(__FUNCTION__." $sql", $dados['id']);
		query($sql);
		
		return $ret;
	}
	
	private function gravaMeta($dados, $tipoMeta){
		if( isset($dados['erc']) && $dados['erc'] > 0 &&
			isset($dados['campanha']) && !empty($dados['campanha']) &&
			isset($dados['sub']) && !empty($dados['sub']) &&
			isset($dados['valor'])){
//print_r($dados);die();
			if($this->validaERC($dados['erc'], $dados['vendedor']) || $tipoMeta == 'C' || $tipoMeta == 'P'){
				$sql = "SELECT valor FROM gf_camp_metas WHERE campanha = '".$dados['campanha']."' AND sub = '".$dados['sub']."' AND erc = ".$dados['erc'];
				$rows = query($sql);
				if(count($rows) > 0){
					$sql = "UPDATE gf_camp_metas SET valor = ".$dados['valor']." WHERE campanha = '".$dados['campanha']."' AND sub = '".$dados['sub']."' AND erc = ".$dados['erc'];
					$this->log("Update de meta SUB: ".$dados['sub']." ERC: ".$dados['erc']." Valor: ".$dados['valor'], $dados['campanha']);
				}else{
					$sql = "INSERT INTO gf_camp_metas (campanha,sub,erc,valor) VALUES ('".$dados['campanha']."',  '".$dados['sub']."', ".$dados['erc'].", ".$dados['valor'].")";
					$this->log("Inclusão de meta SUB: ".$dados['sub']." ERC: ".$dados['erc']." Valor: ".$dados['valor'], $dados['campanha']);
				}
//echo "$sql <br>\n";
				query($sql);
				addPortalMensagem('Vendedor/Operador '.$dados['erc'].' incluído!');
			}else{
				addPortalMensagem('Vendedor inválido ou não está ativo - '.$dados['erc'],'error');
			}
		}
		return;
	}
	
	private function gravaItem($dados){
//print_r($dados);
		if(!empty($dados['campanha']) && !empty($dados['sub'])){
			$sql = "SELECT * FROM gf_camp_itens WHERE campanha = '".$dados['campanha']."' AND sub = '".$dados['sub']."' AND valor = ".$dados['valor'];
			$rows = query($sql);
			
			if(count($rows) == 0){
				$sql = "INSERT INTO gf_camp_itens (campanha,sub,valor) VALUES ('".$dados['campanha']."',  '".$dados['sub']."', ".$dados['valor'].")";
			}else{
				$sql = "UPDATE gf_camp_itens SET valor = ".$dados['valor']." WHERE campanha = '".$dados['campanha']."' AND sub = '".$dados['sub']."' AND valor = ".$dados['valor'];
			}
//echo "$sql <br>\n";
			query($sql);
		}
		return;
	}
	
	private function gravarMetas(){
		$campanha = getAppVar('manut_campanha2017_metas');
//echo "Campanha: $campanha <br>";
		$this->getCampanhas($campanha);
		$vendedor = $this->_campanhas[0]['vendedor'];
		$metas = getParam($_POST, 'meta');
		$dado = array();
		$dado['campanha'] = $campanha;
		$dado['vendedor'] = $vendedor;
		$dado['tipoMeta'] = $this->_campanhas[0]['tipoMeta'];
		$tipoMeta = $this->_campanhas[0]['tipoMeta'];
//print_r($metas);
		if(!empty($campanha) && count($metas) > 0){
			$this->log(__FUNCTION__." Campanha: $campanha Dados: ".serialize($metas), $campanha);
			foreach ($metas as $erc => $meta){
				foreach ($meta as $sub => $valor){
					$dado['erc'] = $erc;
					$dado['sub'] = $sub;
					if(substr($valor, -3, 1) == ','){
						$valor = str_replace('.', '', substr($valor, 0, (strpos($valor, ',')))).'.'.substr($valor, -2);
					}
					$dado['valor'] = $valor;
//echo "Campanha:$campanha ERC: $erc  $sub $valor Vendedor: $vendedor <br>\n";					
					$this->gravaMeta($dado, $tipoMeta);
				}

			}
		}
//print_r($_POST);
	}
	
	private function excluiMeta($campanha, $erc){
		$sql = "DELETE FROM gf_camp_metas WHERE campanha = '$campanha' AND erc = $erc";
		query($sql);
	}
	
	private function getProximaSub($campanha, $tabela){
		$ret = 1;
		$sql = "SELECT MAX(sub) FROM $tabela WHERE campanha = '$campanha'";
		$rows = query($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0] + 1;
		}
		
		return $ret;
	}
	
	private function gravaItensWT($campanha, $sub, $dados){
		if($campanha != '' && $sub != '' && isset($dados['campanhaWT']) && $dados['campanhaWT'] != ''){
			$sql = "select distinct codprod from PCDESCONTO where codpromocaomed = ".$dados['campanhaWT'];
			$rows = query4($sql);
			if(count($rows) > 0){
				$sql = "DELETE FROM gf_camp_itens WHERE campanha = '$campanha' AND sub = '$sub'";
				query($sql);
				
				$dados = array();
				$dados['campanha'] = $campanha;
				$dados['sub'] = $sub;
				foreach ($rows as $row){
					if((int)$row[0] > 0){
						$dados['valor'] = (int)$row[0];
						
						$this->gravaItem($dados);
					}
				}
			}
		}
	}
	
	private function gravaItensString($campanha, $sub, $dados){
		if($campanha != '' && $sub != '' && isset($dados['itens'])){
			$item = $dados['itens'];
			$item= str_replace(';', ',', $item);
			$item= str_replace('|', ',', $item);
			$item= str_replace(';', ',', $item);
			$item= str_replace(':', ',', $item);
			$item= str_replace("\n", ',', $item);
			
			$itens = explode(',', $item);
//print_r($itens);
			if(count($itens) > 0){
				$sql = "DELETE FROM gf_camp_itens WHERE campanha = '$campanha' AND sub = '$sub'";
				query($sql);
				
				$dados = array();
				$dados['campanha'] = $campanha;
				$dados['sub'] = $sub;
				foreach ($itens as $item){
					if((int)$item > 0){
						$dados['valor'] = (int)$item;
//echo "$item <br>\n";						
						$this->gravaItem($dados);
					}
				}
			}
		}
		
	}
	
	private function realizarCopia($id){
		$this->getCampanhas($id);
		if(count($this->_campanhas) == 1){
			$dado = $this->_campanhas[0];
			$dado['id'] = 0;
			$dado['ativo'] = 'S';
			$dado['titulo'] = 'Copia de '.$dado['titulo'];
			
			$newID = $this->gravaCampanha($dado);
			
			$this->log(__FUNCTION__." Campanha: $id Nova Campanha: $newID", $id);
			
			if(!empty($newID) && strlen($newID) == 15){
				$subs = $this->copiaSubCampanhas($id, $newID);
				$this->copiaMetas($id, $newID, $subs, $dado['vendedor'], $dado['tipoMeta']);
				$this->copiaItens($id, $newID, $subs);
			}
		}
	}
		
	private function copiaSubCampanhas($id, $new){
		$ret = array();
		$sql = "SELECT * FROM gf_camp_subcamp WHERE campanha = '$id'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$idOld = $row['id'];
				$row['id'] = '';
				$row['campanha'] = $new;
				if($row['campanhaWT'] == ''){
					$row['campanhaWT'] = 'NULL';
				}
				
				$ret[$idOld] = $this->gravaSubCampanha($row);
			}
		}
		
		return $ret;
	}

	private function copiaMetas($id, $new, $subs, $vendedor, $tipoMeta){
		foreach ($subs as $ant => $nova){
			$sql = "SELECT * FROM gf_camp_metas WHERE campanha = '$id' AND sub = '$ant'";
			$rows = query($sql);
			if(count($rows) > 0){
				foreach ($rows as $row){
					$row['campanha'] = $new;
					$row['sub'] = $nova;
					$row['vendedor'] = $vendedor;
					$this->gravaMeta($row, $tipoMeta);
				}
			}
		}
	}
	
	private function copiaItens($id, $new, $subs){
		foreach ($subs as $ant => $nova){
			$sql = "SELECT * FROM gf_camp_itens WHERE campanha = '$id' AND sub = '$ant'";
			$rows = query($sql);
			if(count($rows) > 0){
				foreach ($rows as $row){
					$row['campanha'] = $new;
					$row['sub'] = $nova;
					$this->gravaItem($row);
				}
			}
		}
	}
	
	private function excluirSubCampanha($id){
		$campanha = getAppVar('manut_campanha2017_editarSub');
		
		$this->log(__FUNCTION__." Campanha: $campanha Sub: $id", $id);
//echo "Campanha: $campanha SUB: $id <br>\n";		
		if(!empty($campanha) && strlen($campanha) == 15){
			$sql = "DELETE FROM gf_camp_subcamp WHERE campanha = '$campanha' AND id = '$id'";
//echo "$sql <br>\n";
			query($sql);
			
			$sql = "DELETE FROM gf_camp_metas WHERE campanha = '$campanha' AND sub = '$id'";
//echo "$sql <br>\n";
			query($sql);
		
			$sql = "DELETE FROM gf_camp_itens WHERE campanha = '$campanha' AND sub = '$id'";
//echo "$sql <br>\n";
			query($sql);
		
		}
		
		return;
	}
	
	private function copiarSubCampanha($id){
		$campanha = getAppVar('manut_campanha2017_editarSub');
		
		$this->log(__FUNCTION__." $id", $id);
		
		if(!empty($campanha) && strlen($campanha) == 15 && !empty($id) && strlen($id) == 15){
			$this->getSubCampanhas($campanha, true, $id);
			$subDados = $this->_subCampanhas[0];
			$subDados['id'] = '';
			$subDados['sub'] = '';
			$subDados['titulo'] = 'Copia de '.$subDados['titulo'];
						
			$this->gravaSubCampanha($subDados);
		}
	}
	
	private function gravarSubCampanha($formInc){
		$campanha = getAppVar('manut_campanha2017_editarSub');
		$sub = getAppVar('manut_campanha2017_editarSub_sub');

		$this->log(__FUNCTION__." Campanha: $campanha Sub; $sub - ".serialize($formInc), $campanha);
		
		if($campanha != ''){
			$this->gravaSubCampanha($formInc, $campanha, $sub);
			if(trim($formInc['campanhaWT'] ?? '') == ''){
				$this->gravaItensString($campanha, $sub, $formInc);
			}else{
				$this->gravaItensWT($campanha, $sub, $formInc);
			}
		}
		
		return;
	}
	
	private function gravarSubCampanhaMetas($formInc){
		$campanha = getAppVar('manut_campanha2017_editarSub');
		$sub = getAppVar('manut_campanha2017_editarSub_metas');
		$dados = array();
		$dados['campanha'] = $campanha;
		$dados['sub'] = $sub;
		$dados['vendedor'] = $formInc['vendedor'];
		$this->getCampanhas($campanha);
		$tipoMeta = $this->_campanhas[0]['tipoMeta'];
//echo "Tipo: $tipoMeta <br>\n";		
		$this->log(__FUNCTION__." ", $campanha);
		if(!empty($campanha) && strlen($campanha) == 15 && !empty($sub) && strlen($sub) == 15){
			//$sql = "UPDATE camp_metas SET valor = 0 WHERE campanha = $campanha AND sub = $sub";
			$sql = "DELETE FROM gf_camp_metas WHERE campanha = '$campanha' AND sub = '$sub'";
			$this->log("Limpando metas SubCampanha: $sub", $campanha);
			query($sql);
			$m = $formInc['metas'];
			//12/05/22 - passa a separar por ; e respeitar a virgula como casa decimal
			$m= str_replace(',', '.', $m);
			
			$m= str_replace('|', ';', $m);
			$m= str_replace(':', ';', $m);
			$metas = explode("\n", $m);
			if(count($metas) > 0){
				foreach ($metas as $meta){
					if(trim($meta) != ''){
						$temp = explode(';', $meta);
						
						$dados['erc'] = strval($temp[0]);
						$dados['valor'] = strval($temp[1]);
						
						if($dados['erc']> 0 && $dados['valor']> 0){
							$this->gravaMeta($dados, $tipoMeta);
						}
					}
				}
			}
		}
	}

	private function excluirERCmeta($erc){
		$campanha = getAppVar('manut_campanha2017_metas');
		//$vendedor = getParam($_GET, 'vendedor');
		if(!empty($campanha)){
			$sql = "DELETE FROM gf_camp_metas WHERE campanha = '$campanha' AND erc = $erc";
			query($sql);
			
			$this->log("Exclusão de meta ERC: $erc", $campanha);
		}
	}
	
	private function adicionaERC(){
		$ret = '';
		$this->getVendedores();
		$campanha = getAppVar('manut_campanha2017_metas');
		$vendedor = getParam($_GET, 'vendedor');
		$erc = getParam($_POST, 'erc');
		$dado = array();
		$this->getCampanhas($campanha);
		$tipoMeta = $this->_campanhas[0]['tipoMeta'];

		switch ($vendedor) {
			case 'S':
				$lista = $this->_super;
				break;
			case 'T':
			case 'X':
				$lista = $this->_operadores;
				break;
			default:
				$lista = $this->_erc;
				break;
		}
		if(!empty($campanha) && $erc != '' && isset($lista[$erc])){
			$this->getSubCampanhas($campanha);
			foreach ($this->_subCampanhas as $subCamp){
				$dado['vendedor'] = $vendedor;
				$dado['sub'] = $subCamp['id'];
				$dado['campanha'] = $campanha;
				$dado['erc'] = $erc;
				$dado['valor'] = 0;
//print_r($dado);			
				$this->gravaMeta($dado,$tipoMeta);
			}
			
			$this->log(__FUNCTION__." Campanha: $campanha ERC: $erc", $campanha);
		}
		
		return $ret;
	}

	private function getOrigens(){
		$ret = array();
		$ret[] = array("T","Todas");
		$ret[] = array("NTELE","Menos Tele");
		$ret[] = array("PDA","ION");
		$ret[] = array("OL","OL");
		$ret[] = array("NOL","Menos OL");
		$ret[] = array("TELE","Tele");
		$ret[] = array("PE","Pedido Eletronico");
		$ret[] = array("W","eCommerce");
		
		return $ret;
	}
	
	private function getTipoVendedor($lista = true){
		$ret = array();
		if($lista){
			$ret[] = ['E','ERC'];
			$ret[] = ['S','Regiao'];
			$ret[] = ['T','Televendas'];
			$ret[] = ['X','Televendas+ERC'];
		}else{
			$ret = ['E'=>'ERC','S'=>'Regiao','T'=>'Televendas','X'=>'Televendas+ERC'];
		}
		
		return $ret;
	}
}
