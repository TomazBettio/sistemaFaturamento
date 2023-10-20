<?php
/*
 * Data Criação: 21/02/2021
 * Autor: Thiel
 *
 * Descrição: Permite a manutenção das imagens dos produtos
 *
 * Alterções:
 *
 */


if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class imagens{
	
	var $funcoes_publicas = array(
			'index' 	=> true,
			//'carrega'	=> true,
			'ajax'      => true,
			'upload'	=> true,
			'excluir'	=> true,
	);
	
	//Título do processo
	private $_titulo;
	
	//Pasta onde se encontram as imagens
	private $_pasta;
	
	//Extensoes de imagens
	private $_extensoes = [];
	
	//Caminho windows das imagens
	private $_path;
	
	function __construct(){
		//set_time_limit(0);
		
		$this->_titulo = 'Manutenção Imagens';
		
		$this->_pasta = '/mnt/winthor/IMG/Produtos/';
		
		$this->_path = 'P:\\IMG\PRODUTOS\\';
		
		$this->_extensoes[] = 'jpg';
		//$this->_extensoes[] = '';
	}
	
	function index(){
		$operacao = getOperacao();
		$ret = '';
		if(empty($operacao)){
			//Questiona o produto
			$ret = $this->formProduto();
		}elseif($operacao == 'imagens'){
			$ret = $this->mostraProduto();
		}
		return $ret;
	}
	
	//function carrega(){
	function ajax(){
		$arq = base64_decode(getParam($_GET, 'img'));
		$produto = getAppVar('produtoUpload');
		
		/*
		$GLOBALS['tws_pag'] = array(
				'header'   	=> false, //Imprime o cabeçalho (no caso de ajax = false)
				'html'		=> false, //Imprime todo html (padão) ou só o processamento principal?
				'menu'   	=> false,
				'content' 	=> false,
				'footer'   	=> false,
				'onLoad'	=> '',
		);*/
		
		
		header("Content-type: image/jpg");
		if (file_exists($this->_pasta.$produto.'/'.$arq)) {
			$arq = file_get_contents($this->_pasta.$produto.'/'.$arq);
			if($arq !== false){
			    echo $arq;
			}
		}
		return '';
		
	}
		
	function upload(){
		$produto = getAppVar('produtoUpload');
		if(count($_FILES) > 0){
			if (is_uploaded_file($_FILES['upd_produto']['tmp_name'])) {
				$nome = $_FILES['upd_produto']['name'];
				$ext = ltrim( substr( $nome, strrpos( $nome, '.' ) ), '.' );
				$arquivo = str_replace('.'.$ext, '', $nome);
				
				if(strtolower($ext) == 'jpg'){
					if(!$this->moverImagem($_FILES['upd_produto']['tmp_name'], $arquivo, $ext, $produto)){
						addPortalMensagem('Erro', 'Ocorreu um erro ao transferir a imagem, favor tentar novamente!','erro');
					}
				}else{
					addPortalMensagem('Erro', 'O arquivo deve ser .JPG','erro');
				}
			}else{
				addPortalMensagem('Erro', 'Ocorreu um erro ao transferir a imagem, favor tentar novamente!','erro');
			}
		}
		
		return $this->mostraProduto($produto);
	}
	
	function excluir(){
		$arq = base64_decode(getParam($_GET, 'img'));
		$produto = getAppVar('produtoUpload');
		
		if(strpos($arq, $produto) !== false){
			unlink($this->_pasta.$produto.'/'.$arq);
		}
		
		//Verifica se ainda tem alguma imagem, se não limpa o campo de imagem no cadastro de produto
		$this->verificaQuantImagens($produto);
		
		return $this->mostraProduto($produto);
	}
	
	//---------------------------------------------------------------------- UI ------------------------------------
	
	private function formProduto(){
		global $nl;
		$ret = '';
		
		formbase01::setLayout('basico');
		
		$param = array();
		$param['nome'] = 'selecionaProduto';
		$param['valor'] = '';
		$param['etiqueta'] = 'Produto: ';
		$formProduto = formbase01::formTexto($param);
		
		$param = formbase01::formSendParametros();
		$param['texto'] = 'Carregar';
		$enviar = formbase01::formBotao($param);
		
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-4">'.$formProduto.'</div>'.$nl;
		$ret .= '	<div  class="col-md-2"></div>'.$nl;
		$ret .= '	<div  class="col-md-5"><br>'.$enviar.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		$param = array();
		$param['acao'] = getLink().'index.imagens';
		$param['nome'] = 'formProduto';
		$form = formbase01::form($param, $ret);
		
		//$param = array();
		//$ret = addBoxInfo($this->_titulo, $form, $param);
		$ret = addCard(array('titulo' => $this->_titulo, 'conteudo' => $form));
		
		return $ret;
	}

	private function mostraProduto($prod = ''){
		$ret = '';
		
		$produto = !empty($prod) ? $prod : getParam($_POST, 'selecionaProduto');
		$info = $this->carregaProduto($produto);
		putAppVar('produtoUpload', $produto);
		
		if(count($info) > 0){
			$ret .= $this->infoProduto($info);
			$ret .= $this->imagensProduto($info);
		}else{
			$ret = $this->formProduto();
			addPortalMensagem('ERRO:', 'Produto '.$produto.' não encontrado!', 'erro');
		}
		return $ret;
	}
	
	private function infoProduto($info){
		global $nl;
		$ret = '';
		
		$txt = '<b>Produto: </b>'.$info['codprod'].'<br>';
		$txt .= '<b>Descrição: </b>'.$info['produto'];
		
		$param = [];
		$param['nome'] 	= 'upd_produto';
		$form = formbase01::formFile($param);
		$param = formbase01::formSendParametros();
		$param['texto'] = 'Enviar Imagem';
		$form .= formbase01::formBotao($param);
		
		$param = array();
		$param['acao'] = getLink()."upload";
		$param['nome'] = 'formUPD';
		$param['id']   = 'formUPD';
		$param['enctype'] = true;
		$form = formbase01::form($param, $form);
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div  class="col-md-4">'.$txt.'</div>'.$nl;
		$ret .= '	<div  class="col-md-2"></div>'.$nl;
		$ret .= '	<div  class="col-md-5">'.$form.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		//$param = array();
		$p = array();
		$p['onclick'] = "setLocation('".getLink()."index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		//$param['botoesTitulo'][] = $p;
		//$ret = addBoxInfo($this->_titulo, $ret, $param);
		$ret = addCard(array('titulo' => $this->_titulo, 'conteudo' => $ret, 'botoesTitulo' => array($p)));
		
		return $ret;
	}
	
	private function imagensProduto($info){
		global $nl;
		$ret = '';
		
		$imagens = $info['imagens'];
		$quant = count($imagens);
		
		if($quant > 0){
			for($i=0;$i<$quant;$i+=2){
				$img1 = '';
				$img2 = '';
				if(isset($imagens[$i])){
					//$img1 = '<img src="'.getLink().'carrega&img='.base64_encode($imagens[$i]).'" style="height: 100%; width: 100%; object-fit: contain" />';
				    $img1 = '<img src="'.getLinkAjax('carrega').'&img='.base64_encode($imagens[$i]).'" style="height: 100%; width: 100%; object-fit: contain" />';
				    
					//$param = array();
					$p = array();
					$p['onclick'] = "setLocation('".getLink()."excluir&img=".base64_encode($imagens[$i])."')";
					$p['tamanho'] = 'pequeno';
					$p['cor'] = 'danger';
					$p['texto'] = 'Excluir';
					//$param['botoesTitulo'][] = $p;
					//$img1 = addBoxInfo($imagens[$i], $img1, $param);
					$img1 = addCard(array('titulo' => $imagens[$i], 'conteudo' => $img1, 'botoesTitulo' => array($p)));
				}
				if(isset($imagens[$i+1])){
					$img2 = '<img src="'.getLinkAjax('carrega').'&img='.base64_encode($imagens[$i+1]).'" style="height: 100%; width: 100%; object-fit: contain" />';
					//$param = array();
					$p = array();
					$p['onclick'] = "setLocation('".getLink()."excluir&img=".base64_encode($imagens[$i+1])."')";
					$p['tamanho'] = 'pequeno';
					$p['cor'] = 'danger';
					$p['texto'] = 'Excluir';
					//$param['botoesTitulo'][] = $p;
					//$img2 = addBoxInfo($imagens[$i+1], $img2, $param);
					$img2 = addCard(array('titulo' => $imagens[$i+1], 'conteudo' => $img2, 'botoesTitulo' => array($p)));
				}
				$ret .= '<div class="row">'.$nl;
				$ret .= '	<div  class="col-md-4">'.$img1.'</div>'.$nl;
				$ret .= '	<div  class="col-md-2"></div>'.$nl;
				$ret .= '	<div  class="col-md-5">'.$img2.'</div>'.$nl;
				$ret .= '</div>'.$nl;
			}
		}
		
		
		//$param = array();
		//$ret = addBoxInfo('Imagens', $ret, $param);
		$ret = addCard(array('titulo' => 'Imagens', 'conteudo' => $ret));
		
		return $ret;
	}

	//---------------------------------------------------------------------- VO ------------------------------------
	
	private function carregaProduto($produto){
		$ret = [];
		
		$sql = "SELECT * FROM pcprodut WHERE codprod = $produto";
		$rows = query4($sql);
		
		if(isset($rows[0]['CODPROD']) && $produto == $rows[0]['CODPROD']){
			$ret['codprod'] = $produto;
			$ret['produto'] = $rows[0]['DESCRICAO'];
			
			$this->verificaDiretorio($produto);
			$ret['imagens'] = $this->carregarImagens($produto);
			
			$this->verificaQuantImagens($produto);
		}
		
		return $ret;
	}
	
	
	//---------------------------------------------------------------------- BO ------------------------------------
	
	private function carregarImagens($produto){
		$ret = [];
		$diretorio = dir($this->_pasta.$produto);
		
		while($arquivo = $diretorio -> read()){
			if($arquivo != '.' && $arquivo != '..'){
				$ext = ltrim( substr( $arquivo, strrpos( $arquivo, '.' ) ), '.' );
				if(strtolower($ext) == 'jpg'){
					$ret[] = $arquivo;
				}
			}
		} 
		
		return $ret;
	}
	
	private function verificaDiretorio($produto){
		if(!is_dir($this->_pasta.$produto)){
			mkdir($this->_pasta.$produto, 0777);
		}
		
		foreach ($this->_extensoes as $ext){
			if (file_exists($this->_pasta.$produto.'.'.$ext)) {
				if (file_exists($this->_pasta.$produto.'/'.$produto.'.'.$ext)) {
					//Apaga o arquivo na raiz do diretorio
					unlink($this->_pasta.$produto.'.'.$ext);
				}else{
					//Move o arquivo para o diretorio
					rename($this->_pasta.$produto.'.'.$ext, $this->_pasta.$produto.'/'.$produto.'.'.$ext);
				}
			} 
		}
	}

	private function moverImagem($file, $arquivo, $ext, $produto){
		$ret = false;
		
		$arquivo = $this->proximoNome($produto);
		
		if(move_uploaded_file($file, $arquivo)){
			$ret = true;
		}
		
		return $ret;
	}
	
	private function proximoNome($produto){
		$ret = '';
		$quant = 1;
		
		while (true) {
			if($quant == 1){
				$ret = $this->_pasta.$produto.'/'.$produto.'.jpg';
			}else{
				$ret = $this->_pasta.$produto.'/'.$produto.'_'.$quant.'.jpg';
			}
			
			if (file_exists($ret)) {
				$quant++;
			}else{
				break;
			}
			
		}
		
		return $ret;
	}
	
	private function verificaQuantImagens($produto){
		$diretorio = dir($this->_pasta.$produto);
		$quant = 0;
		$caminho1 = '';
		
		while($arquivo = $diretorio -> read()){
			if($arquivo != '.' && $arquivo != '..'){
				$ext = ltrim( substr( $arquivo, strrpos( $arquivo, '.' ) ), '.' );
				if(strtolower($ext) == 'jpg'){
					if($quant == 0){
						$caminho1 = $arquivo;
					}
					$quant++;
				}
			}
		} 
		if (file_exists($this->_pasta.$produto.'/'.$produto.'.jpg')) {
			$caminho1 = $this->_path.$produto.'\\'.$produto.'.jpg';
		}else{
			$caminho1 = $this->_path.$produto.'\\'.$caminho1;
		}

		if($quant == 0){
			$sql = "UPDATE pcprodut SET DIRFOTOPROD = NULL, DIRETORIOFOTOS = NULL WHERE codprod = $produto";
		}elseif($quant == 1){
			$sql = "UPDATE pcprodut SET DIRFOTOPROD = '$caminho1', DIRETORIOFOTOS = NULL WHERE codprod = $produto";
		}else{
			$sql = "UPDATE pcprodut SET DIRFOTOPROD = '$caminho1', DIRETORIOFOTOS = '".$this->_path.$produto.'\\'."' WHERE codprod = $produto";
		}
		query4($sql);
//echo "$sql <br>\n";
	}
	
}