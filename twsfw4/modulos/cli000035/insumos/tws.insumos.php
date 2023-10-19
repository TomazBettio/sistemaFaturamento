<?php
/*
 * Data Criacao: 11/07/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Agen
 *
 * Alteracoes;
 * 
 * ATENCAO: ajustar no PHP.INI
 * 	max_file_uploads = 200
	memory_limit = -1
	post_max_size = 5000M
	upload_max_filesize = 5000M
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);




class insumos{
	var $funcoes_publicas = array(
			'index' 			=> true,
			'incluir' 			=> true,
			'separaArquivos'	=> true,
			'salvarPlano'		=> true,
			'processar'			=> true,
			'selecionaContasUI' => true,
			'painel'			=> true,
			'ajustes'			=> true,
			'uploadAnalise'		=> true,
			'arquivos'			=> true,
			'separar'			=> true,
			'excluir'			=> true,
			'arquivar'			=> true,
	);
	
	//Tabela
	private $_tabela;

	//Nome do programa
	private $_programa;
	
	//Titulo do relatorio
	private $_titulo;
	
	//Path arquivos upload SPED
	private $_path;
	
	//Plano de contas
	private $_planoContas = [];
	
	//Plano contas KEY Conta
	private $_planoConta;
	
	//Plano contas KEY COD
	private $_planoCod;
	
	//Plano de contas selecionavel
	private $_planoContasSelect = [];
	
	//CNPJ do cliente ao fazer upload
	private $_cnpj;
	
	//nome do cliente
	private $_razaoSocial = '';
	
	//Razao social utilizada no nome dos arquivos
	private $_arquivoRS;
	
	public function __construct(){
		global $config;
		set_time_limit(0);
		
		$this->_path = $config['pathUpdInsumos'];
		
		$this->_cnpj = '';
		$this->_razaoSocial = '';
	}
	
	public function index(){
		$ret = '';

		$param = [];
		$param['width'] = 'AUTO';
		//$param['scroll'] = false;
		$param['info'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Insumos Processados';
		$this->_tabela = new tabela01($param);
		
		$this->montaColunas();
		$dados = $this->getDadosInsumos();
		$this->_tabela->setDados($dados);
		
		$param = [];
		$param['texto'] 	= 'Processar';
		$param['link'] 		= getLink()."processar&cnpj=";
		$param['coluna']	= 'id';
		//$param['cor'] 		= 'danger';
		$param['flag'] 		= '';
		$param['width'] 	= 80;
		$param['pos'] 		= 'F';
		$this->_tabela->addAcao($param);
		
		$param = [];
		$param['texto'] 	= 'Sel.Contas';
		$param['link'] 		= getLink()."selecionaContasUI&cnpj=";
		$param['coluna']	= 'id';
		#$param['cor'] 		= 'danger';
		$param['flag'] 		= '';
		$param['width'] 	= 80;
		$param['pos'] 		= 'F';
		$this->_tabela->addAcao($param);
		
		$param = [];
		$param['texto'] 	= 'Ajustes';
		$param['link'] 		= getLink()."ajustes&cnpj=";
		$param['coluna']	= 'id';
		#$param['cor'] 		= 'danger';
		$param['flag'] 		= '';
		$param['width'] 	= 80;
		$param['pos'] 		= 'F';
		$this->_tabela->addAcao($param);
		
		$param = [];
		$param['texto'] 	= 'Painel';
		$param['link'] 		= getLink()."painel&cnpj=";
		$param['coluna']	= 'id';
		#$param['cor'] 		= 'danger';
		$param['flag'] 		= '';
		$param['width'] 	= 80;
		$param['pos'] 		= 'F';
		$this->_tabela->addAcao($param);
//Fecha o processo		
		$param = [];
		$param['texto'] 	= 'Arquivar';
		$param['link'] 		= getLink()."arquivar&cnpj=";
		$param['coluna']	= 'id64';
		$param['cor'] 		= 'warning';
		$param['flag'] 		= '';
		$param['width'] 	= 80;
		$param['pos'] 		= 'F';
//		$this->_tabela->addAcao($param);
//Excluir		
		$param = [];
		$param['texto'] 	= 'Excluir';
		$param['link'] 		= getLink()."excluir&cnpj=";
		$param['coluna']	= 'id64';
		$param['cor'] 		= 'danger';
		$param['flag'] 		= '';
		$param['width'] 	= 80;
		$param['pos'] 		= 'F';
		$this->_tabela->addAcao($param);
		
		$botao = [];
		//$botao['cor'] 		= 'sucess';
		$botao['texto'] 	= 'Incluir';
		$botao["onclick"]	= "setLocation('".getLink()."incluir')";
		$this->_tabela->addBotaoTitulo($botao);
		
		$ret .= $this->_tabela;
		
//		$this->processa('1657748622');
		
//		$ret = $this->selecionaContas('1657748622');
		
		return $ret;
	}
	
	public function selecionaContasUI(){
		$ret = '';
		
		$cnpj = isset($_GET['cnpj']) ? $_GET['cnpj'] : false;
		
		if($cnpj !== false){
			//Verifica se já existe plano de contas
			$sql = "SELECT count(*) quant FROM mgt_insumos_plano_contas WHERE cnpj = '$cnpj'";
			$rows = query($sql);
			
			if($rows[0]['quant'] == 0){
				$this->processa($cnpj);
			}
			
			$ret = $this->selecionaContas($cnpj);
		}
		
		return $ret;
	}
	
	public function incluir(){
		$ret = '';
		$id = base64_encode(time());
		
		$param = [];
		$param['texto'] 	= 'Finalizar';
		//$param["onclick"]	= "setLocation('".getLink()."separaArquivos&id=$id')";
		$param['url'] 		= getLink()."separaArquivos&id=$id";
		$param['tipo'] 		= 'link';
		$botaoFim = formbase01::formBotao($param);
		
		$param = [];
		$param['get'] = 'id='.$id;
		$param['botaoFim'] = $botaoFim;
		$ret .= formbase01::formUploadFile($param);

		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('".getLink()."index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Upload Arquivos SPED';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	public function uploadAnalise(){
		$ret = '';
//print_r($_FILES);
		if(!isset($_FILES['upd_analise'])){
			$ret = $this->index();
			return $ret;
		}
		$cnpj = getAppVar('insumos_update_lanc');
		
		if(is_dir($this->_path.$cnpj)){
			$file = $_FILES['upd_analise'];
			if (is_uploaded_file($file['tmp_name'])) {
				$nome = 'retorno.csv';
				if(move_uploaded_file($file['tmp_name'], $this->_path.$cnpj.'/'.$nome)){
					addPortalMensagem('Sucesso ao atualizar o arquivo '.$cnpj.'/'.$nome.'!');
				}else{
					addPortalMensagem('Erro ao mover o arquivo.','error');
				}
			}else{
				addPortalMensagem('Erro ao encontrar o arquivo temporário.','error');
			}
		}else{
			addPortalMensagem('Diretorio '.$this->_path.$cnpj.' não encontrado.','error');
		}
		
		$ret .= $this->index();
		
		return $ret;
	}
	
	public function arquivos(){
		$ret = '';
		//$operacao = getOperacao();
		$id = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
		
		//Se for entre 'ontem' e agora....
		if($id > 1664296452 && $id < time()){
			putAppVar('insumos_diretorio_unico', $id);
			if(count($_FILES) > 0){
				if (is_uploaded_file($_FILES['files']['tmp_name'][0])) {
					//$nome = $_FILES['files']['name'][0];
					//$ext = substr($nome, strlen($nome) -3,3);
					//if(strtoupper($ext) == 'TXT' || strtoupper($ext) == 'XLS' || strtoupper($ext) == 'LSX'){
						$ret = $this->leArquivo(
								$_FILES['files']['tmp_name'][0], 
								$_FILES['files']['name'][0],
								$_FILES['files']['error'][0],
								$id);
					//}
					//unlink($_FILES['files']['tmp_name'][0]);
				}else{
					echo "Não upload \n";
					print_r($_FILES);
				}
			}else{
				echo "Sem arquivos \n";
			}
			//print_r($ret);return;
			return ['files' => $ret];
		}
		
		return $ret;
	}
	
	public function separar(){
		$ret = '';
		$operacao = getOperacao();
		$id = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
		
		//Se for entre 'ontem' e agora....
		if($id > 1664296452 && $id < time()){
			if(empty($operacao)){
				//Formulario para indicar como quer separado
				$ret = $this->getFormSepara($id);
			}elseif($operacao == 'excluir'){
				$arquivo = getAppVar('INSUMOS_SEPARA_arquivoRS');
				$separa = new insumos_separa($this->_path, $arquivo, '', $id);
				$separa->exluiZIP();
				unsetAppVar('INSUMOS_SEPARA_arquivoRS');
				redireciona(getLink().'ajustes&cnpj='.$id);
			}else{
				//Realiza a separação
				$separaPor = $_POST['separaPor'];
				$arquivo = getAppVar('INSUMOS_SEPARA_arquivoRS');
				
				$separa = new insumos_separa($this->_path, $arquivo, $separaPor, $id);
				$separa->separa();
				unsetAppVar('INSUMOS_SEPARA_arquivoRS');
				redireciona(getLink().'ajustes&cnpj='.$id);
			}
		}else{
			$ret = $this->index();
		}
		
		return $ret;
	}

	public function salvarPlano(){
		$cnpj = getAppVar('insumos_cnpj');
		
		if(!empty($cnpj)){
			
			//Limpratodos as contas
			$sql = "UPDATE mgt_insumos_plano_contas set status = 'N' WHERE cnpj = '$cnpj'";
			query($sql);
			
			if(isset($_POST['conta']) && count($_POST['conta']) > 0){
				$sql = '';
				foreach ($_POST['conta'] as $conta){
					$sql .= "UPDATE mgt_insumos_plano_contas set status = 'S' WHERE cnpj = '$cnpj' AND codigo = '$conta';\n";
				}
				query($sql);
			}
		}
		
		redireciona(getLink().'selecionaContasUI&cnpj='.$cnpj);
		//$ret = $this->selecionaContas($cnpj);
		//return $ret;
	}

	public function processar(){
		if(isset($_GET['cnpj'])){
			$cnpj = $_GET['cnpj'];
		}else{
			$cnpj = getAppVar('insumos_cnpj');
		}
		
		if(!empty($cnpj)){
			$this->separaArquivos($cnpj);
			$dir = $this->_path.$cnpj;
			$processa = new processa_insumos($dir, $cnpj);
			
			$processa->processa($cnpj);
			
			addPortalMensagem('Arquivos processados!');
		}
		
		$ret = $this->index();
		
		return $ret;
	}
	
	public function ajustes(){
		$ret = '';
		
		$cnpj = isset($_GET['cnpj']) ? $_GET['cnpj'] : false;
		
		if($cnpj !== false){
			putAppVar('insumos_update_lanc', $cnpj);
			$ret = $this->montaAjustes($cnpj);
		}else{
			addPortalMensagem('CNPJ não encontrado!','error');
			$ret = $this->index();
		}
		
		return $ret;
	}
	
	public function painel(){
		$ret = '';
		
		$cnpj = isset($_GET['cnpj']) ? $_GET['cnpj'] : false;
		
		$existeRetorno = file_exists($this->_path.DIRECTORY_SEPARATOR.$cnpj.DIRECTORY_SEPARATOR.'retorno.csv');
		
		if($cnpj !== false && $existeRetorno){
			$painel = new insumos_ui($this->_path.DIRECTORY_SEPARATOR.$cnpj, 'retorno.csv', $cnpj);
			
			if($painel->verificaArquivo()){
				$ret = $painel->getPainel();
			}else{
				if($cnpj === false){
					addPortalMensagem('Não foi possível identificar o cliente!','error');
				}else{
					addPortalMensagem('Não encontrado arquivo resumo I250 - Favor fazer upload!.','error');
				}
				$ret = $this->index();
			}
		}
		
		return $ret;
	}
	
	public function separaArquivos($id = ''){
		//$operacao = getOperacao();
		if(empty($id)){
			$unico = base64_decode($_GET['id']);
		}else{
			$unico = $id;
		}

		//Verifica se os arquivos já foram separados
		$sql = "SELECT separado FROM mgt_insumos WHERE id = $unico";
		$rows = query($sql);
		$separado = $rows[0]['separado'] ?? 'N';
		
		
		//Se for entre 'ontem' e agora....
		if($unico > 1664296452 && $unico < time() && $separado == 'N'){
			$cnpj = '';
			$razao = '';
			
			$dir = $this->_path.$unico;
			$arquivos = $this->getArquivos($dir);
			
			foreach ($arquivos as $arq){
				$linha = $this->leituraLinha($dir.DIRECTORY_SEPARATOR.$arq);
				$tipoArquivo = $linha[2] == 'LECD' ? 'ECD' : 'EFD';
				
				if(empty($this->_cnpj) && empty($cnpj)){
					$cnpj = $tipoArquivo == 'ECD' ? $linha[6] : $linha[9];
					//echo "$tipoArquivo <br>";
					//print_r($linha);die();
					//echo "$cnpj <br>\n";
				}
				if(empty($this->_razaoSocial) && empty($razao) ){
					$razao = $tipoArquivo == 'ECD' ? $linha[5] : $linha[8];
					$razao = $this->retiraAcentos(utf8_encode($razao));
					//echo "$razao <br>\n";
				}
				//echo "$tipoArquivo <br>";
				//echo "$cnpj <br>";
				//echo "$razao <br>";
				//print_r($linha);die();
				
				$data = $tipoArquivo == 'ECD' ? $linha[3] : $linha[6];
				$ano = substr($data, 4, 4);
				$this->verificaAno($dir, $ano);
				
				rename($dir.DIRECTORY_SEPARATOR.$arq, $dir.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.$tipoArquivo.DIRECTORY_SEPARATOR.$arq);
				
				//Marca como já separado
				$sql = "UPDATE mgt_insumos SET separado = 'S' WHERE id = $unico";
				query($sql);
			}
			
			if(empty($this->_cnpj) || empty($this->_razaoSocial) ){
				$this->_cnpj = $cnpj;
				$this->_razaoSocial = $razao;
				//echo "$cnpj <br>\n";
				//echo "$razao <br>\n";
			}
			
			if(empty($id)){
				$this->gravaInsumo($unico, $this->_cnpj, $this->_razaoSocial);
			}
		}
		
		return $this->index();
	}
	
	public function excluir(){
		$ret = '';
		
		$id = isset($_GET['cnpj']) ? base64_decode($_GET['cnpj']) : '';
		$confirma = isset($_GET['confirma']) ? base64_decode($_GET['confirma']) : '';
		
		if(!empty($id)){
			$nome = $this->getNomeEmpresa($id);
			if(empty($confirma)){
				if(!empty($nome)){
					$conteudo = '';
					
					$param = [];
					$param["onclick"]	= "setLocation('".getLink()."index')";
					$param["texto"]		= "Cancelar";
					$param['cor'] 		= 'warning';
					$botaoConfirma = formbase01::formBotao($param);
					
					$param = [];
					$param["onclick"]	= "setLocation('".getLink()."excluir&cnpj=".base64_encode($id)."&confirma=".base64_encode('123bjhuasgdgajgejhdjagsd')."')";
					$param["texto"]		= "EXCLUIR";
					$param['cor'] 		= 'danger';
					$botaoExcluir = formbase01::formBotao($param);
					
					$param = [];
					$param['titulo'] = "Confirma exclusão do processo $id - $nome?" ;
					$param['conteudo'] = $botaoConfirma.' '.$botaoExcluir;
					$ret .= addCard($param);
				}else{
					redireciona(getLink().'index');
				}
			}elseif($confirma = 'confirmado'){
				$dir = $this->_path.$id;
				if(is_dir($dir)){
					$iterator     = new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS);
					$rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
					
					foreach($rec_iterator as $file){
						$file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
					}
					
					rmdir($dir); 
					
					if(is_dir($dir)){
						addPortalMensagem('Erro ao excluir os arquivos!','error');
					}else{
						$sql = "DELETE FROM mgt_insumos WHERE id = $id";
						query($sql);
						$this->gravaLogInsumos("Exclusão do processo $id - $nome");
						addPortalMensagem('Processo excluído com sucesso!');
					}
				}
				$ret = $this->index();
			}else{
				redireciona(getLink().'index');
			}
		}
		
		return $ret;
	}
	
	public function arquivar(){
		
	}
	
	//----------------------------------------------------------------------- PROCESSA ------------------
	
	private function processa($unico){
		$dir = $this->_path.$unico;
		$processa = new processa_insumos($dir, $unico);
		
		$plano = $processa->getPlanoContas();
//print_r($plano);
		$this->gravaPlanoContas($unico, $plano);
	
//print_r($plano);
	}
	
	//----------------------------------------------------------------------- ARQUIVOS ------------------
	
	
	private function retiraAcentos($string) {
		// matriz de entrada
		$de = array("'", 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','Ã','É','Í','Ó','Ú','ñ','Ñ','ç','Ç' );
		// matriz de saída
		$por   = array( '`','a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','A','E','I','O','U','n','n','c','C');
		// devolver a string
		return str_replace($de, $por, $string);
	}
	
	private function verificaAno($dir, $ano){
		if(!is_dir($dir.DIRECTORY_SEPARATOR.$ano)){
			mkdir($dir.DIRECTORY_SEPARATOR.$ano , '0777');
			chmod ($dir.DIRECTORY_SEPARATOR.$ano, 0777);
			
			mkdir($dir.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'ECD' , '0777');
			chmod ($dir.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'ECD', 0777);
			mkdir($dir.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'EFD' , '0777');
			chmod ($dir.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'EFD', 0777);
		}
	}
	
	/**
	 * Le a primeira linha do arquivo
	 * 
	 * @param string $arquivo
	 * @return array
	 */
	private function leituraLinha($arquivo){
		$ret = [];
		$handle = fopen($arquivo, "r");
		
		if ($handle)
		{
			$linha = fgets($handle);
			$ret = explode("|", $linha);
		}
		
		fclose($handle);
		
		return $ret;
	}
	
	private function getArquivos($dir){
		$ret = [];
		$diretorio = dir($dir);
		
		while($arquivo = $diretorio->read()){
			if($arquivo != '.' && $arquivo != '..'){
				$ret[] = $arquivo;
			}
		}
		
		return $ret;
	}
	
	private function moverArquivo($file, $arquivo){
		$ret = false;

		if(move_uploaded_file($file, $arquivo)){
			$ret = true;
		}
		
		return $ret;
	}
	
	//----------------------------------------------------------------------- UI -------------------------
	private function getFormSepara($id){
		$ret = '';
		
		$form = new form01();
		$form->setBotaoCancela();
		$form->addCampo(array('id' => 'separaPor', 'campo' => 'separaPor', 'etiqueta' => 'Separar por', 'tipo' => 'A', 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => '', 'lista' => '', 'opcoes' => '=;A=Anos;C=Contas;AC=Anos e Contas','validacao' => '', 'obrigatorio' => true, 'onchange' => ''));
		
		$ret .= $form;
		
		$param = [];
		$param['titulo'] = 'Insumos - Separar Arquivo';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		$param = [];
		$param['acao'] = getLink().'separar.separar&id='.base64_encode($id);
		$param['nome'] = 'formSepara';
		$param['sendFooter'] = true;
		//$param['URLcancelar'] = $this->_URLcancelar;
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}
	
	private function montaAjustes($cnpj){
		global $config;
		$ret = '';
		
		$this->_razaoSocial = $this->getNomeEmpresa($cnpj);
		$this->_arquivoRS = str_replace(' ', '_', $this->_razaoSocial);
		$this->_arquivoRS = str_replace('/', '_', $this->_arquivoRS);
		$this->_arquivoRS = substr($this->_arquivoRS, 0, 15);
		
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'0200_resumo.txt';
		$param['texto'] = '0200';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c1 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'A100_resumo.txt';
		$param['texto'] = 'A100';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c2 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'C100_resumo.txt';
		$param['texto'] = 'C100';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c3 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'C500_resumo.txt';
		$param['texto'] = 'C500';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c4 = formbase01::formBotao($param);
		
		$param = [];
		$param['tamanhos'] = [3,3,3,3];
		$param['conteudos'] = [$l1c1, $l1c2, $l1c3, $l1c4];
		$ret .= addLinha($param);
		
		$ret .= "<br><br>";
		
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'D100_resumo.txt';
		$param['texto'] = 'D100';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c1 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'I050_resumo.csv';
		$param['texto'] = 'I050';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c2 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'I075_resumo.csv';
		$param['texto'] = 'I075';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c3 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'I250_resumo.csv';
		$param['texto'] = 'I250';
		//$param['cor'] = 'success';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c4 = formbase01::formBotao($param);
		
		$param = [];
		$param['tamanhos'] = [3,3,3,3];
		$param['conteudos'] = [$l1c1, $l1c2, $l1c3, $l1c4];
		$ret .= addLinha($param);

		$ret .= "<br><br>";
		
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'F100_resumo.txt';
		$param['texto'] = 'F100';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c1 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'F120_resumo.csv';
		$param['texto'] = 'F120';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c2 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'F130_resumo.csv';
		$param['texto'] = 'F130';
		//$param['cor'] = 'danger';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c3 = formbase01::formBotao($param);
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'F150_resumo.csv';
		$param['texto'] = 'F150';
		//$param['cor'] = 'success';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c4 = formbase01::formBotao($param);
		
		$param = [];
		$param['tamanhos'] = [3,3,3,3];
		$param['conteudos'] = [$l1c1, $l1c2, $l1c3, $l1c4];
		$ret .= addLinha($param);
		
		$ret .= "<br><br>";
		
		$param = [];
		$param['url'] = $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'I250_'.$this->_arquivoRS.'.csv';
		$param['texto'] = 'RESULTADO';
		$param['cor'] = 'success';
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c1 = formbase01::formBotao($param);
		
//echo $this->_path.$cnpj.DIRECTORY_SEPARATOR.'I250_'.$this->_arquivoRS.'.zip'."<br>\n";
		$apagarZip = false;
		$param = [];
		if(is_file($this->_path.$cnpj.DIRECTORY_SEPARATOR.'I250_'.$this->_arquivoRS.'.zip')){
			$param['texto'] = 'RESULTADO SEPARADO';
			$param['cor'] 	= 'success';
			$param['url'] 	= $config['linkInsumos'].$cnpj.DIRECTORY_SEPARATOR.'I250_'.$this->_arquivoRS.'.zip';
			$apagarZip = true;
		}else{
			$param['texto'] = 'SEPARAR RESULTADO';
			$param['cor'] 	= 'secondary';
			$param['url'] 	= getLink()."separar&id=".base64_encode($cnpj);
			putAppVar('INSUMOS_SEPARA_arquivoRS', $this->_arquivoRS);
		}
		$param['bloco'] = true;
		$param['tipo'] = 'link';
		$l1c2 = formbase01::formBotao($param);
		
		if($apagarZip){
			$param = [];
			$param['texto'] = 'EXCLUI ZIP';
			$param['cor'] 	= 'danger';
			$param['url'] 	= getLink()."separar.excluir&id=".base64_encode($cnpj);
			$param['bloco'] = true;
			$param['tipo'] = 'link';
			$l1c3 = formbase01::formBotao($param);
			putAppVar('INSUMOS_SEPARA_arquivoRS', $this->_arquivoRS);
		}else{
			$l1c3 = '';
		}
		
		$l1c4 = '';
		
		$param = [];
		$param['tamanhos'] = [3,3,3,3];
		$param['conteudos'] = [$l1c1, $l1c2, $l1c3, $l1c4];
		$ret .= addLinha($param);
		/*/		
		$param = [];
		$param['nome'] 	= 'upd_lancamentos';
		$form .= formbase01::formFile($param).'<br><br>';
		
		$param = formbase01::formSendParametros();
		$param['texto'] = 'Enviar Arquivo';
		$form .= formbase01::formBotao($param);
		
		$param = array();
		$param['acao'] = getLink()."upload_lanc";
		$param['nome'] = 'formUPD';
		$param['id']   = 'formUPD';
		$param['enctype'] = true;
		$form = formbase01::form($param, $form);
		
		$cont = '';
		$cont .= 'Faça o download do arquivo de lançamentos: <a href="local">I250.csv</a> <br><hr>'.$nl;
		$cont .= 'Ao finalizar as alterações realize o upload do arquivo:<br>'.$nl;
		$cont .= $form;
/*/		
		$param = [];
		$param['titulo'] = 'Ajustes Lançamentos';
		$param['conteudo'] = $ret;
		$param['botaoCancelar'] = true;
		$ret = addCard($param);
		
		$ret .= $this->formUPDretorno();
		return $ret;
	}	
	
	private function formUPDretorno(){
		global $nl;
		$ret = '';
		
		$param = [];
		$param['nome'] 	= 'upd_analise';
		$form = formbase01::formFile($param).'<br><br>';
		
		$param = formbase01::formSendParametros();
		
		$param['texto'] = 'Enviar Arquivo';
		$form2 = formbase01::formBotao($param);
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-4">'.$form.'</div>'.$nl;
		$ret .= '	<div  class="col-md-2"></div>'.$nl;
		$ret .= '	<div  class="col-md-5">'.$form2.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = array();
		$param['acao'] = getLink()."uploadAnalise";
		$param['nome'] = 'formUPD';
		$param['id']   = 'formUPD';
		$param['enctype'] = true;
		$ret = formbase01::form($param, $ret);
		
		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('".getLink()."index')";
		$p['cor'] = 'warning';
		$p['texto'] = 'Cancelar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Upload Arquivos SPED';
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	private function montaColunas(){
		$this->_tabela->addColuna(array('campo' => 'id'			, 'etiqueta' => 'ID#'			,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$this->_tabela->addColuna(array('campo' => 'cnpj'		, 'etiqueta' => 'CNPJ'			,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'razao'		, 'etiqueta' => 'Razão Social'	,'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'data_inc'	, 'etiqueta' => 'Data'			,'tipo' => 'D', 'width' => 100, 'posicao' => 'C'));
		$this->_tabela->addColuna(array('campo' => 'user_inc'	, 'etiqueta' => 'Usuário'		,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		
	}
	
	private function selecionaContas($cnpj){
		$ret = '';
		
		$param = [];
		$param['width'] = 'AUTO';
		$param['titulo'] = 'Selecione as Contas';
		$tab = new tabela01($param);

		$tab->addColuna(array('campo' => 'sel'		, 'etiqueta' => ''			,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'conta'	, 'etiqueta' => 'Conta'		,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descricao'	,'tipo' => 'T', 'width' => 400, 'posicao' => 'E'));
		
	 	$plano = $this->getPlanoContasSelecionado($cnpj);
	 	$tab->setDados($plano);
	 	
	 	$botaoCancela = array();
	 	$botaoCancela["onclick"]= "setLocation('".getLink()."index')";
	 	$botaoCancela["texto"]	= "Cancelar";
	 	$botaoCancela['cor'] = 'warning';
	 	$tab->addBotaoTitulo($botaoCancela);
	 	/*/
	 	$botao = array();
	 	$botao["onclick"]= "setLocation('".getLink()."processar')";
	 	$botao["texto"]	= "Processar";
	 	$botao['cor'] = 'sucess';
	 	$tab->addBotaoTitulo($botao);
	 	/*/
	 	$botao = [];
	 	//$botao['cor'] 		= 'sucess';
	 	$botao['texto'] 	= 'Gravar';
	 	$botao["onclick"]	= "$('#formPlano').submit();";
	 	$tab->addBotaoTitulo($botao);
	 	
	 	$ret .= $tab;
	 	
	 	putAppVar('insumos_cnpj', $cnpj);
	 	
	 	$param = [];
	 	$param['acao'] = getLink()."salvarPlano&cnpj=$cnpj";
	 	$param['id']   = 'formPlano';
 		$param['nome'] = 'formPlano';
	 	
	 	$ret = formbase01::form($param, $ret);
	 	
	 	return $ret;
	}
	
	//----------------------------------------------------------------------- BANCO ------------------------
	
	private function gravaPlanoContas($unico, $plano){
		if(count($plano) > 0){
			foreach ($plano as $p){
//print_r($p);
				$sql = "SELECT codigo FROM mgt_insumos_plano_contas WHERE codigo = '".$p['cod']."' AND cnpj = '$unico'";
//echo "$sql <br>\n";
				$rows = query($sql);
				
				if(!isset($rows[0][0])){
					$campos = [];
					$campos['cnpj'] 	= $unico;
					$campos['reduzida'] = $p['reduzida'];
					$campos['descricao']= addslashes($p['desc']);
					$campos['codigo'] 	= $p['cod'];
					$campos['conta'] 	= $p['conta'];
					$sql = montaSQL($campos, 'mgt_insumos_plano_contas');
					query($sql);
				}
			}
		}
	}
	
	private function getPlanoContasSelecionado($cnpj){
		$ret = [];
		
		$sql = "SELECT * FROM mgt_insumos_plano_contas WHERE cnpj = '$cnpj'";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$checked = $row['status'] == 'S' ? 'checked' : '';
				$temp = [];
				$temp['sel'] 		= '<input name="conta['.$row['codigo'].']" type="checkbox" value="'.$row['codigo'].'" '.$checked.' id="'.$row['codigo'].'">';
				$temp['conta'] 		= $row['codigo'];
				$temp['descricao'] 	= $row['descricao'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}

	private function gravaInsumo($unico, $cnpj, $razao){
		$campos = [];
		$campos['id'] = $unico;
		$campos['cnpj'] = $cnpj;
		$campos['razao'] = $razao;
		//$campos['data_inc'] = ;
		$campos['user_inc'] = getUsuario();
		
		$sql = montaSQL($campos, 'mgt_insumos');
		query($sql);
	}
	
	private function getDadosInsumos(){
		$ret = [];
		
		$sql = "SELECT * FROM mgt_insumos ORDER BY id DESC";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['id'] 		= $row['id'];
				$temp['id64'] 		= base64_encode($row['id']);
				$temp['cnpj'] 		= $row['cnpj'];
				$temp['razao'] 		= $row['razao'];
				$temp['data_inc'] 	= datas::dataMS2S($row['data_inc']);
				$temp['user_inc'] 	= $row['user_inc'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
		
	}
	
	private function getNomeEmpresa($cnpj){
		$ret = '';
		
		$sql = "SELECT razao FROM mgt_insumos WHERE id = '$cnpj'";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	
	private function leArquivo($nomeTemp, $nome, $erro, $id){
		$ret = [];
		
		//Se o diretório não existir, cria
		if(!is_dir($this->_path.$id)){
			mkdir($this->_path.$id,'0777');
			chmod ($this->_path.$id, 0777);
		}
		
		//echo $nomeTemp.' - '. $this->_path.DIRECTORY_SEPARATOR  .$id.DIRECTORY_SEPARATOR  .$nome."<br>\n";
		
		if(move_uploaded_file($nomeTemp, $this->_path.$id.DIRECTORY_SEPARATOR  .$nome)){
			$ret[]['name'] = '<b>Arquivo:</b> '.$nome.' </b>';
		}
		
		return $ret;
	}
	
	private function gravaLogInsumos($msg){
		$campos = [];
		$campos['usuario'] 	= getUsuario();
		$campos['acao']		= $msg;
		$sql = montaSQL($campos, 'mgt_insumos_log');
		query($sql);
	}
}