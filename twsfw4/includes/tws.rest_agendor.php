<?php
class rest_agendor{
    public function __construct($link, $token){
        //$link = 'https://api.agendor.com.br/v3';
        $this->_link = $link;
        $this->getPath();
        $this->_rest = new rest_cliente01($this->_link);
        $this->_rest->setHeader('Authorization', 'Token ' . $token);
    }
    
    public function getAllUsuarios(){
        $ret = '';
        $path = $this->_path['usuarios']['GET']['all'];
        $param = array();
        //$this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function cadastrarEmpresa($dados){
        $ret = '';
        $path = $this->_path['empresas']['POST']['novaEmpresa'];
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', $param);
        return $ret;
    }
    
    public function getProdutos($pagina = ''){
        $ret = '';
        $path = $this->_path['produtos']['GET']['all'];
        if(!empty($pagina)){
            $path .= "?page=$pagina";
        }
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getProdutoEspecifico($id){
        $ret = '';
        if(!empty($id)){
            $path = $this->_path['produtos']['GET']['all'];
            $path .= "/$id";
            $param = array();
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'GET', $param);
        }
        return $ret;
    }
    
    public function getAllProdutos(){
        $ret = array();
        $pagina = 1;
        do{
            $retorno_rest = $this->getProdutos($pagina);
            if(is_array($retorno_rest) && count($retorno_rest) > 0){
                if(isset($retorno_rest['data']) && count($retorno_rest['data']) > 0){
                    $cards = $retorno_rest['data'];
                    $ret = array_merge($ret, $cards);
                };
            };
            $pagina++;
        }
        while(is_array($retorno_rest) && isset($retorno_rest['links']['next']));
        return $ret;
    }
    
    public function cadastrarProduto($dados){
        $ret = '';
        $path = $this->_path['produtos']['POST']['novoProduto'];
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', $param);
        return $ret;
    }
    
    public function excluirProduto($id){
        $ret = '';
        $path = $this->_path['produtos']['DELETE']['excluirProduto'];
        $path = str_replace('{id}', $id, $path);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'DELETE', $param);
        return $ret;
    }
    
    public function atualizarProduto($id = '', $dados = []){
        $ret = '';
        if(!empty($id) && is_array($dados) && count($dados) > 0){
            $path = $this->_path['produtos']['POST']['novoProduto'] . "/$id";
            $this->_rest->setPostData($dados);
            $param = array();
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'PUT', $param);
        }
        return $ret;
    }
    
    public function getAllNegocios($status = '', $colunaNumero = ''){
        $ret = array();
        $pagina = 1;
        if(empty($status) || !is_array($status)){
            $status = array($status);
        }
        foreach ($status as $stat){
            do{
                $retorno_rest = $this->getNegocios($pagina, $stat, $colunaNumero);
                if(is_array($retorno_rest) && count($retorno_rest) > 0){
                    if(isset($retorno_rest['data']) && count($retorno_rest['data']) > 0){
                        $cards = $retorno_rest['data'];
                        $ret = array_merge($ret, $cards);
                    };
                };
                $pagina++;
            }
            while(is_array($retorno_rest) && isset($retorno_rest['links']['next']));
        }
        return $ret;
    }
    
    public function getNegocios($pagina = '', $status = '', $colunaNumero = ''){
        $ret = '';
        $path = $this->_path['negocios']['GET']['all'];
        if(!empty($pagina)){
            $path .= '&page=' . $pagina;
        }
        if(!empty($status)){
            $path .= '&dealStatus=' . $status;
        }
        if(!empty($colunaNumero)){
            $path .= '&dealStage=' . $colunaNumero;
        }
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getEmpresa($id){
        $ret = '';
        $path = $this->_path['empresas']['GET']['especifica'];
        $path = str_replace('{id}', $id, $path);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getEmpresas($pagina = 1){
        $ret = '';
        $path = $this->_path['empresas']['GET']['geral'];
        if(!empty($pagina)){
            $path .= '&page=' . $pagina;
        }
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getAllEmpresas(){
        $ret = array();
        $pagina = 1;
        do{
            $retorno_rest = $this->getEmpresas($pagina);
            if(is_array($retorno_rest) && count($retorno_rest) > 0){
                if(isset($retorno_rest['data']) && count($retorno_rest['data']) > 0){
                    $cards = $retorno_rest['data'];
                    $ret = array_merge($ret, $cards);
                };
            };
            $pagina++;
        }
        while(is_array($retorno_rest) && isset($retorno_rest['links']['next']));
        return $ret;
    }
    
    public function getNegocio($id){
        $ret = '';
        $path = $this->_path['negocios']['GET']['especifico'];
        $path = str_replace('{id}', $id, $path);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function atualizarNegocio($id, $dados){
        //por enquanto não é possível mudar o título do card 12/09/23
        $ret = '';
        $path = $this->_path['negocios']['PUT']['atualizar'];
        $path = str_replace('{id}', $id, $path);
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'PUT', $param);
        return $ret;
    }
    
    public function getUser($id){
        $ret = '';
        $path = $this->_path['usuarios']['GET']['all'] . "?id=$id";
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function limparProdutos(){
        $produtos = $this->getAllProdutos();
        if(is_array($produtos) && count($produtos)> 0){
            foreach ($produtos as $p){
                do{
                    $retorno = $this->excluirProduto($p['id']);
                }
                while($retorno === false);
            }
        }
    }
    
    public function getEtapas(){
        $ret = '';
        $path = $this->_path['etapas']['GET']['all'];
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function moverEtapa($id, $nova_etapa, $funil = ''){
        $ret = '';
        $dados = ['dealStage' => $nova_etapa];
        if(!empty($funil) && $funil !== false){
            $dados['funnel'] = $funil;
        }
        $path = $this->_path['negocios']['PUT']['moverEtapa'];
        $path = str_replace('{id}', $id, $path);
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'PUT', $param);
        return $ret;
    }
    
    public function criarTarefa($id, $texto){
        $ret = '';
        $path = $this->_path['tarefas']['POST']['criar'];
        $path = str_replace('{id}', $id, $path);
        
        $dados = [
            'text' => $texto,
        ];
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', $param);
        return $ret;
    }
    
    public function criarTarefaComAnexo($id, $texto, $anexo){
        $ret = false;
        if(is_file($anexo)){
            //verifica se o anexo existe
            $nome_arquivo = pathinfo($anexo, PATHINFO_BASENAME);
            $extensao = strtolower(pathinfo($anexo, PATHINFO_EXTENSION));
            if(in_array($extensao, ['pdf', 'doc', 'docx', 'mp3', 'ogg', 'png', 'jpg', 'gif', 'jpeg'])){
                //verifica a extensão do arquivo a ser anexado
                $dados_anexo_bruto = $this->criarAnexo($nome_arquivo);
                if(is_array($dados_anexo_bruto) && isset($dados_anexo_bruto[$nome_arquivo])){
                    //se a criação do anexo deu certo
                    $dados_anexo = $dados_anexo_bruto[$nome_arquivo];
                    $resposta_upload = $this->fazerUploadAnexo($dados_anexo['url'], $anexo);
                    if(empty($resposta_upload)){
                        //se o upload deu certo
                        $tipo_documento = 'document';
                        if(in_array($extensao, ['png', 'jpg', 'gif', 'jpeg'])){
                            $tipo_documento = "image";
                        }
                        
                        $path = $this->_path['tarefas']['POST']['criar'];
                        $path = str_replace('{id}', $id, $path);
                        
                        $dados = [
                            'text' => $texto,
                            'attachments' => [[
                                'type' => $tipo_documento,
                                'filename' => $nome_arquivo,
                                'temporary_key' => $dados_anexo['key'],
                            ]]
                        ];
                        $this->_rest->setPostData($dados);
                        $param = array();
                        $this->_rest->setHeader('Content-Type', 'application/json');
                        $ret = $this->_rest->executa($path, 'POST', $param, true, true);
                    }
                }
            }
        }
        return $ret;
    }
    
    public function criarAnexo($nome_arquivo){
        $ret = '';
        $path = $this->_path['tarefas']['POST']['criarAnexo'];
        $dados = [
            'filenames' => [$nome_arquivo]
        ];
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', $param);
        return $ret;
        
        /*
         * 
         * array(1) { ["41455.pdf"]=> array(2) { ["url"]=> string(376) "https://agendor-user-files.s3.sa-east-1.amazonaws.com/tmp/596345/20230912154402529/41455.pdf?x-amz-acl=private&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAJEYB5UYWJO2H56PA%2F20230912%2Fsa-east-1%2Fs3%2Faws4_request&X-Amz-Date=20230912T154402Z&X-Amz-Expires=900&X-Amz-SignedHeaders=host&X-Amz-Signature=70e8c9b7733d2b13c8f33790b8d957801a8c324ddb8ab69e0001babbf1c03ec5" ["key"]=> string(38) "tmp/596345/20230912154402529/41455.pdf" } }
         * 
         */
    }
    
    public function editarEmpresa($id, $dados){
        $ret = '';
        $path = $this->_path['empresas']['PUT']['editar'];
        $path = str_replace('{id}', $id, $path);
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'PUT', $param);
        return $ret;
        
        /*
         *
         * array(1) { ["41455.pdf"]=> array(2) { ["url"]=> string(376) "https://agendor-user-files.s3.sa-east-1.amazonaws.com/tmp/596345/20230912154402529/41455.pdf?x-amz-acl=private&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAJEYB5UYWJO2H56PA%2F20230912%2Fsa-east-1%2Fs3%2Faws4_request&X-Amz-Date=20230912T154402Z&X-Amz-Expires=900&X-Amz-SignedHeaders=host&X-Amz-Signature=70e8c9b7733d2b13c8f33790b8d957801a8c324ddb8ab69e0001babbf1c03ec5" ["key"]=> string(38) "tmp/596345/20230912154402529/41455.pdf" } }
         *
         */
    }
    
    public function fazerUploadAnexo($url, $arquivo){
        //$url = "https://agendor-user-files.s3.sa-east-1.amazonaws.com/tmp/596345/20230912154402529/41455.pdf?x-amz-acl=private&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAJEYB5UYWJO2H56PA%2F20230912%2Fsa-east-1%2Fs3%2Faws4_request&X-Amz-Date=20230912T154402Z&X-Amz-Expires=900&X-Amz-SignedHeaders=host&X-Amz-Signature=70e8c9b7733d2b13c8f33790b8d957801a8c324ddb8ab69e0001babbf1c03ec5";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/octet-stream',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $arquivo = file_get_contents($arquivo);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arquivo);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
        }
        curl_close ($ch);
        return $result;
    }
    
    
    
    private function getPath($versao = ''){
        switch ($versao) {
            case 1.3:
                break;
            default:
                $this->_path['usuarios']['GET']['all']	             = '/users';
                
                $this->_path['empresas']['POST']['novaEmpresa']	     = '/organizations';
                $this->_path['empresas']['GET']['especifica']	     = '/organizations/{id}?withCustomFields=true';
                $this->_path['empresas']['PUT']['editar']	         = '/organizations/{id}?withCustomFields=true';
                $this->_path['empresas']['GET']['geral']	         = '/organizations?withCustomFields=true&per_page=100';
                
                
                $this->_path['produtos']['GET']['all']	             = '/products';
                $this->_path['produtos']['POST']['novoProduto']	     = '/products';
                $this->_path['produtos']['DELETE']['excluirProduto'] = '/products/{id}';
                
                $this->_path['negocios']['GET']['all']	             = '/deals?per_page=100';
                $this->_path['negocios']['GET']['especifico']	     = '/deals/{id}';
                $this->_path['negocios']['PUT']['atualizar']	     = '/deals/{id}';
                $this->_path['negocios']['PUT']['moverEtapa']	     = '/deals/{id}/stage';
                
                $this->_path['status']['GET']['all']	             = '/deal_statuses';
                
                $this->_path['etapas']['GET']['all']	             = '/deal_stages';
                
                $this->_path['tarefas']['POST']['criar']	         = '/deals/{id}/tasks';
                $this->_path['tarefas']['POST']['criarAnexo']	     = '/attachments/presigned_urls';
                
                break;
        }
    }
}