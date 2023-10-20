<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class portal_documentos{
    var $funcoes_publicas = array(
        'schedule'          => true,
    );
    
    private $_url_docs;
    
    public function __construct(){
    	$this->_url_docs = 'http://doc.grupomarpa.com.br/api_marpa.php';
    }

    public function schedule(){
    	set_time_limit(0);
        $clientes = $this->getClientesParaIntegrar();
        log::gravaLog('portal_documentos_curl', '');
        log::gravaLog('portal_documentos_curl', "Clientes encontrados: ".count($clientes), 0, true);
        log::gravaLog('portal_documentos_curl', $clientes, 0, true);
echo "<br>\nClientes encontrados: ".count($clientes)."<br>\n";
        if(count($clientes) > 0) {
            foreach($clientes as $cliente) {
            	echo "Marcando cliente: ".$cliente->cnpj."  Contrato: ".$cliente->contrato."<br>\n";
            	log::gravaLog('portal_documentos_curl', "Marcando cliente: ".$cliente->cnpj.'-'.$cliente->contrato, 0, true);
                $marcado = $this->marcaClienteComoIntegrado($cliente);
                if($marcado == 'true') {
                	echo "Resposta cliente: TRUE<br>\n";
                	log::gravaLog('portal_documentos_curl', "Sucesso ao marcar cliente: ".$cliente->cnpj.'-'.$cliente->contrato, 0, true);
                	$this->integraCliente($cliente);
                }else{
                	echo "Resposta cliente: FALSE<br>\n";
                	log::gravaLog('portal_documentos_curl', "Erro ao marcar cliente: ".$cliente->cnpj.'-'.$cliente->contrato, 0, true);
                }
            }
        }
    }
    
    private function getClientesParaIntegrar(){
    	global $config;
        $ret = array();
        $url = $config['linkDOCs']."?getClientes=true";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resposta = curl_exec($ch);
        if($resposta === false) {
            log::gravaLog('erros_portal_documentos_curl', 'Curl error: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if(in_array($httpCode, array(200, 201, 202))){
            if($resposta != ''){
                $ret = json_decode($resposta);
                log::gravaLog('portal_documentos_curl', print_r($ret, true), true);
            }
        }
        curl_close($ch);

        // echo $resposta;
        // print_r($ret);
        return $ret;
    }
    
    private function integraCliente($cliente){
        global $config;

        log::gravaLog('api_integraCliente', 'Integra Cliente: ');
        log::gravaLog('api_integraCliente', $cliente);

        $contrato = $cliente->contrato;
        $cnpj = $cliente->cnpj;
        $data = date('Ymd');
        $operacao = 'get_arquivos';
        $key = md5($contrato.'**'.$cnpj."&&@".$data);

        $contrato = str_replace('/', '-', $cliente->contrato);
        $path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato;
        $this->criaPasta($path);

        $diretorio_recebidos = $path . DIRECTORY_SEPARATOR . 'zip';
        
        $arquivos = ['outros','recebidos'];
        foreach($arquivos as $arquivo) {
        	$url = $this->_url_docs."?operacao=$operacao&contrato=".urlencode($contrato).'&cnpj='.urlencode($cnpj).'&key='.urlencode($key)."&arquivo=$arquivo";
        	$arquivo_dest = $diretorio_recebidos.DIRECTORY_SEPARATOR.$arquivo.'.zip';
        	
        	log::gravaLog('api_integraCliente', 'File: ' . $url);
        	log::gravaLog('api_integraCliente', 'Destino: ' . $arquivo_dest);
        	
        	if(is_file($arquivo_dest)){
        		unlink($arquivo_dest);
        	}
        	$fp = fopen($arquivo_dest, 'w+');
        	if($fp === false){
        		log::gravaLog('api_integraCliente', 'ERRO ao criar o arquivo '.$arquivo_dest);
        		//die();
        	}else{
	        	$curl = curl_init($url);
	        	curl_setopt($curl, CURLOPT_FILE, $fp);
	        	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	        	$res = curl_exec($curl);
	        	curl_close($curl);
	        	fclose($fp);
	        	
	        	if($res  === false){
	        		log::gravaLog('api_integraCliente', 'ERRO ao baixar o arquivo');
	        	}else{
	        		log::gravaLog('api_integraCliente', 'SUCESSO ao baixar o arquivo');
	        	}
        	}
        }

        
        $this->extrai_zip($path);
        $this->separaArquivos($diretorio_recebidos);


        $param = [];
        $param['razao']      = $cliente->razao;
        $param['cnpj']       = $cliente->cnpj;
        $param['contrato']   = $cliente->contrato;
        //$param['datactr']    = '';
        $param['status']     = 'esteira';
        $param['usuario']    = getUsuario();
        $param['integrado']  = 'S';
        $param['del']  		 = ' ';
        
        // Verifica se o cliente já existe no banco
        $sql = "SELECT id FROM mgt_monofasico WHERE cnpj = ".$cliente->cnpj." AND contrato = '".$cliente->contrato."'";
        $row = query($sql);

        if(!is_array($row) || count($row) == 0) {
            // Cria cliente na tabela de monofasico
            $param['id']  = time();
            $param['datactr'] = date('Ymd');
            $param['apura_ini'] = '';
            $param['apura_fim'] = '';
            $sql = montaSQL($param, 'mgt_monofasico');
        }else{
        	$id = $row['id'];
        	$sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = '$id'");
        }
        query($sql);
        
        $this->enviaEmail($cnpj, $contrato, $cliente->razao);
    }
    
    private function processaArquivos($cnpj, $contrato, $id){
    	$logContrato = 'monofasico_processa'.DIRECTORY_SEPARATOR.$cnpj . '_' . $contrato;
    	log::gravaLog($logContrato, "Integração DOCs - Processa $cnpj - $contrato");
    	
   		$processa_xml = new processa_xml($cnpj, $contrato, $id, 'S',true);
   		if ($processa_xml->getExisteArquivo() === true) {
   			log::gravaLog($logContrato, 'Processando arquivos XMLs importados');
   			$processa_xml->getInformacoes($cnpj);
   		}else{
   			log::gravaLog($logContrato, 'Não existem arquivos XMLs para integrar');
   			$processa_sped = new processa_sped($cnpj, $contrato, $id, 'S',true);
   			if ($processa_sped->getExisteArquivo() === true) {
   				log::gravaLog($this->_logContrato, 'Processando arquivos SPED importados');
   				$processa_sped->getInformacoes($cnpj);
   			}else{
   				log::gravaLog($logContrato, 'Não existem arquivos SPED para integrar');
   			}
    	}
    	
    	return;
    }

    private function criaPasta($pasta) {
		if (!file_exists($pasta)) {
			mkdir($pasta, 0777, true);
			chMod($pasta, 0777);
		}
		
		$pastas = [
			'arquivos',
			'erro',
			'processados_xml',
			'processados_sped',
			'zip',
			'zip'.DIRECTORY_SEPARATOR.'xml',
			'zip'.DIRECTORY_SEPARATOR.'sped',
			'zip'.DIRECTORY_SEPARATOR.'outros'
		];
		foreach ($pastas as $pasta_criar){
			$path = $pasta . DIRECTORY_SEPARATOR . $pasta_criar;
echo "Pasta: $path <br>\n";
			if (!file_exists($path)) {
				if(mkdir($path, 0777, true)){
					chMod($path, 0777);
				}else{
echo "Não foi possivel criar a pasta: $path <br>\n";
				}
			}
		}
	}

    private function extrai_zip($path)
	{
        $dir_zip = $path . DIRECTORY_SEPARATOR . 'zip';
        $zip = glob($dir_zip . DIRECTORY_SEPARATOR . '*.zip');

		foreach ($zip as $folder) {
			log::gravaLog('api_extrair_zip', 'Arquivo: '.$folder);
			$zip = new ZipArchive;
			$res = $zip->open($folder);
			if ($res === TRUE) {
				log::gravaLog('api_extrair_zip', 'Sucesso ao abrir');
				$descompactar = $zip->extractTo($dir_zip . DIRECTORY_SEPARATOR);
				if($descompactar){
					log::gravaLog('api_extrair_zip', 'Sucesso ao descompactar');
				}else{
					log::gravaLog('api_extrair_zip', 'Erro ao descompactar');
				}
				$zip->close();
				unlink($folder);
				// die('ok');
			}else{
				log::gravaLog('api_extrair_zip', 'Erro ao abrir o arquivo');
			}
		}
	}

    private function enviaEmail($cnpj, $contrato, $razao) {
        $param = [];
        $param['destinatario'] = 'fiscal@grupomarpa.com.br;alexandre.thiel@verticais.com.br';
        $param['mensagem'] = "Prezado,<br><br> Informamos que o cliente com CNPJ: $cnpj e Contrato: $contrato finalizou os envios de seus arquivos. Os mesmos já estão disponíveis para análise";
        $param['assunto'] = "Envio de arquivos finalizado - $contrato - $razao";

        enviaEmail($param);
    }
    
    private function marcaClienteComoIntegrado($cliente){
    	$post = [];
    	$post['cnpj'] = $cliente->cnpj;
    	$post['contrato'] = $cliente->contrato;
    	$post['operacao'] = 'marcar_cliente';
    	$post['data'] = date('Ymd');
    	$post['key'] = md5($post['contrato'].'**'.$post['cnpj']."&&@".$post['data']);
    	
        $ret = '';
        log::gravaLog('portal_documentos_curl', 'Curl url: ' . $this->_url_docs);
       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_url_docs);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $resposta = curl_exec($ch);
        
        if($resposta === false) {
            log::gravaLog('portal_documentos_curl', 'Curl error: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        log::gravaLog('portal_documentos_curl', 'Marcar Cliente - Curl code: ' . $httpCode);
        if(in_array($httpCode, array(200, 201, 202))){
            if($resposta != ''){
                $ret = $resposta;
            }
        }
        curl_close($ch);

        return $ret;
    }
    
    private function separaArquivos($path){
    	$files = glob($path . '/*');
    	foreach ($files as $file) {
    		if (is_file($file)) {
    			$fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    			switch ($fileExt) {
    				case 'txt':
    					$dir = 'sped';
    					break;
    				case 'xml':
    					$dir = 'xml';
    					break;
    				default:
    					$dir = 'outros';
    					break;
    			}
    			$destino = $path.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR. strtolower(basename($file));
    			rename($file, $destino);
    		}
    	}
    }
}