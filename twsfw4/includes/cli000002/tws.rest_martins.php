<?php
class rest_martins{
	
	//url da api do catalogo
	private  $_url_catalogo;
	
	//url da api de pedidos
	private $_url_pedidos;
	
	//String de autenticacao
	private $_autenticacao = '';
	
	//URL Path
	private $_path = [];
	
	//REST catologo
	private $_rest_catalogo;
	
	//REST pedidos
	private $_rest_pedidos;
	
	public function __construct($url_catalogo, $url_pedidos, $token, $versao = 1){
	    $this->_rest_catalogo = new rest_cliente01($url_catalogo);
	    $this->_rest_catalogo->setHeader("Authorization", "BASIC " . $token);
	    
	    $this->_rest_pedidos = new rest_cliente01($url_pedidos);
	    $this->_rest_pedidos->setHeader("Authorization", "BASIC " . $token);
		
		$this->getPath($versao);
	}
	
	// PRODUTOS ================================================================================================================================
	//==========================================================================================================================================
	public function getProdutos(){
	    $ret = '';
	    $path = $this->_path['produtos']['GET']['todos'];
	    $param = array();
	    $ret = $this->_rest_catalogo->executa($path, 'GET', $param);
	    return $ret;
	}
	
	public function getProdutoExpecifico($id_produto){
	    $ret = '';
	    $path = str_replace('{id_produto}', $id_produto, $this->_path['produtos']['GET']['especifico']);
	    $param = array();
	    $ret = $this->_rest_catalogo->executa($path, 'GET', $param);
	    return $ret;
	}
	
	public function editarProduto($id_produto, $dados_alterados){
	    $dados_produto = $this->getProdutoExpecifico($id_produto);
	    foreach ($dados_alterados as $chave => $valor){
	        if(isset($dados_produto[$chave])){
	            $dados_produto[$chave] = $valor;
	        }
	    }
	    $path = $this->_path['produtos']['POST']['editar'];
	    $param = array();
	    $this->_rest_catalogo->setPostData($dados_produto);
	    $ret = $this->_rest_catalogo->executa($path, 'POST', $param);
	    return $ret;
	}
	
	public function incluirFotoProduto($dados){
	    $ret = '';
	    $path = $this->_path['produtos']['POST']['fotos'];
	    $param = array();
	    $this->_rest_catalogo->setPostData($dados);
	    $this->_rest_catalogo->setHeader('Content-Type', 'application/json');
	    $ret = $this->_rest_catalogo->executa($path, 'POST', $param);
	    return $ret;
	}
	
	public function cadastrarProduto($dados){
	    $ret = '';
	    $path = $this->_path['produtos']['POST']['completo'];
	    $param = array();
	    $this->_rest_catalogo->setPostData($dados);
	    $this->_rest_catalogo->setHeader('Content-Type', 'application/json');
	    $ret = $this->_rest_catalogo->executa($path, 'POST', $param);
	    return $ret;
	}
	
	public function cadastrarEstoque($dados){
	    $ret = '';
	    $path = $this->_path['estoque']['PUT']['completo'];
	    $param = array();
	    $this->_rest_catalogo->setPostData($dados);
	    $this->_rest_catalogo->setHeader('Content-Type', 'application/json');
	    $ret = $this->_rest_catalogo->executa($path, 'PUT', $param);
	    return $ret;
	}
	
	public function cadastrarPrecos($dados){
	    $ret = '';
	    $path = $this->_path['precos']['PUT']['completo'];
	    $param = array();
	    $this->_rest_catalogo->setPostData($dados);
	    $this->_rest_catalogo->setHeader('Content-Type', 'application/json');
	    $ret = $this->_rest_catalogo->executa($path, 'PUT', $param);
	    return $ret;
	}
	
	public function getCategorias(){
	    $ret = '';
	    $path = $this->_path['categorias']['GET']['todas'];
	    $param = array();
	    $ret = $this->_rest_catalogo->executa($path, 'GET', $param);
	    return $ret;
	}
	//------------------------------------------------------------------------------------- UTEIS ---------------------------------------------
	
	private function getPath($versao){
		switch ($versao) {
			case 1.3:
				break;
			
			default:
				$this->_path['produtos']['POST']['completo']	= '/api/Product';
				$this->_path['produtos']['POST']['editar']	    = '/api/Product';
			    $this->_path['produtos']['POST']['variacoes']	= '/api/Product/Variations';
				$this->_path['produtos']['POST']['atributos']	= '/api/Product/Attributes';
				$this->_path['produtos']['POST']['fotos']		= '/api/Product/Photos';
				
				
				$this->_path['produtos']['GET']['todos']		= '/api/Products';
				$this->_path['produtos']['GET']['especifico']   = '/api/Product/{id_produto}';
				
				$this->_path['estoque']['PUT']['completo']      = '/api/Product/StockByDistribuitionCenter';
				
				$this->_path['precos']['PUT']['completo']       = '/api/PriceList/Items';
				
				$this->_path['categorias']['GET']['todas']      = '/api/Category';
				
				break;
		}
	}
	

}