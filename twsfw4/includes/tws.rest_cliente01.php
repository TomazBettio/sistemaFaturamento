<?php
/*
 * Data Criação: 04/07/2020
 * Autor: Alexandre Thiel
 *
 * Descricao: Realiza o consumo de um serviço REST
 * 
 * Alterações:
 * 		04/07/2020 - Criação. Alexandre Thiel
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class rest_cliente01{
	
	//Parametros a serem passados
	private $_parametros = [];
	
	//URL
	private $_endPoint = '';
	
	//Resource
	private $_resource = '';
	
	//Metodo
	private $_metodo = '';
	
	//Usuario para autenticação BASIC
	private $_usuario = '';
	
	//Senha para autenticação BASIC
	private $_senha = '';
	
	//Headers
	private $_header = [];
	
	//Key Bearer
	private $_bearer = '';
	
	//POST data (body)
	private $_postData = [];
	
	function __construct($url){
		$this->_endPoint = $url;
	}
	
	public function setAutenticacao($usuario, $senha){
		$this->_usuario = $usuario;
		$this->_senha = $senha;
	}
	
	public function setAutenticacaoBearer($bearer){
		$this->_bearer = $bearer;
	}
	
	public function setHeader($header, $valor){
		if(!empty($header)){
			$this->_header[$header] = $valor;
		}
	}
	
	public function setPostData($data, $debug = false){
		if(is_array($data)){
			$this->_postData = json_encode($data);
		}else{
			$this->_postData = $data;
		}
		if($debug){
			echo "\n".$this->_postData."<br>\n";
		}
	}
	
	public function executa($resource, $metodo, $parametros, $debug = false, $devolver_resposta_erro = false){
		$this->_resource = $resource;
		$this->_metodo = $metodo;
		$this->_parametros = $parametros;

		if($debug){
			echo "<br>\nResurce: ".$this->_resource."<br>\n";
			echo "<br>\nMetodo: ".$this->_metodo."<br>\n";
			echo "<br>\nParametros: "; 
			print_r($this->_parametros);
			echo "<br>\n";
			echo "<br>\nEndPoint: ".$this->_endPoint."<br>\n";
		}
		
		//return $this->exec($debug);
		return $this->exec($debug, $devolver_resposta_erro);
	}
	
	private function exec($debug = false, $devolver_resposta_erro = false){
		global $config;
		$error_status = '';
		$url = $this->_endPoint;
		$url .= $this->_resource;
		if(is_array($this->_parametros) && count($this->_parametros) > 0 && $this->_metodo != 'POST' && $this->_metodo != 'PATCH'){
			$url .= '?'.http_build_query($this->_parametros);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if($debug){
			echo "URL: $url<br>\n";
			echo "Método: ".$this->_metodo." <br>\n";
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			$verbose = fopen($config['debugPath'].'REST_'.$this->_metodo.'.log'	, 'w+');
			curl_setopt($ch, CURLOPT_STDERR, $verbose);
		}
		
		if(!empty(trim($this->_usuario)) && !empty(trim($this->_senha))){
			curl_setopt($ch, CURLOPT_USERPWD, $this->_usuario.':'.$this->_senha);
		}
		
		if(!empty($this->_bearer)){
			$this->setHeader('Authorization', 'Bearer '.$this->_bearer);
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if(count($this->_header)){
			$header = $this->getHeader();
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		
		switch ($this->_metodo){
			case "POST":
				//Inclusão
				//curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				if(!empty($this->_postData)){
					curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_postData);
				}elseif(!is_array($this->_parametros)){
					curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_parametros);
				}else{
					curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $this->_parametros));
				}
				break;
			case "PUT":
				//Atualização
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				if(!empty($this->_postData)){
					curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_postData);
				}elseif(!is_array($this->_parametros)){
					curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_parametros);
				}else{
					curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $this->_parametros));
				}
				break;
			case "PATCH":
			    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
			    if(!empty($this->_postData)){
			        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_postData);
			    }elseif(!is_array($this->_parametros)){
			        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_parametros);
			    }else{
			        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $this->_parametros));
			    }
			    break;
			case 'DELETE':
				//Exclusão
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			default:
				break;
		}
		
		$res = curl_exec($ch);
		
		/* Check for 404 (file not found). */
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// Check the HTTP Status code
		switch ($httpCode) {
			case 200:
				//$error_status = "200: Success";
				$error_status = '';
				break;
			case 201:
				//$error_status = "201: Registro criado";
				$error_status = '';
				break;
			case 202:
			    $error_status = '';
			    break;
			case 400:
				$error_status = "400: Bad Request";
				break;
			case 404:
				$error_status = "404: API Not found";
				break;
			case 500:
				$error_status = "500: servers replied with an error.";
				break;
			case 502:
				$error_status = "502: servers may be down or being upgraded. Hopefully they'll be OK soon!";
				break;
			case 503:
				$error_status = "503: service unavailable. Hopefully they'll be OK soon!";
				break;
			default:
				$error_status = "Undocumented error: " . $httpCode . " : " . curl_error($ch);
				break;
		}
		
		log::gravaLog('tudo_rest', 'teste');
		log::gravaLog('tudo_rest', $res);
		log::gravaLog('tudo_rest',print_r($this->_postData,true));
		
		if($debug && !empty($error_status)){
			echo 'Erro REST: '.$error_status."<br>\n\n";
		}
		if(!empty($error_status)){
		    log::gravaLog('erros_rest', $res);
		    log::gravaLog('erros_rest',print_r($this->_postData,true));
		}
		
		curl_close($ch);
		if(!empty($error_status)){
		    if($devolver_resposta_erro){
		        return $res;
		    }
		    else{
		        return false;
		    }
		}else{
		    log::gravaLog('sucesso_rest', $res);
		    log::gravaLog('sucesso_rest',print_r($this->_postData,true));
			return json_decode($res, true);
		}
	}
	
	private function getHeader(){
		$ret = [];
		if(count($this->_header) > 0){
			foreach ($this->_header as $key => $valor){
				$ret[] = $key.': '.$valor;
			}
		}
		return $ret;
	}
	
	public function recuperarHeaders(){
	    return array_keys($this->_header);
	}
	
	public function unSetHeader($header){
	    if(isset($this->_header[$header])){
	        unset($this->_header[$header]);
	    }
	}
	
	public function unSetAllHeaders(){
        $this->_header = [];	    
	}
}
