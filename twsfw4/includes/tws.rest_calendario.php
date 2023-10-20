<?php 


/*
 * Data Criação: 28/08/2023
 * Autor: Gilson Britto
 *
 * Descricao:   Cria e manipula o calendário do Teams com a API de eventos do Microsoft Graph.
 */


 class rest_calendario {

    private $_link;
    private $_path = array();
    private $_rest;
    private $_token;
    private $_dados;

    public function __construct($link,$dados){
        $this->_link = $link;
        $this->_token = $this->getToken();
        $this->_dados = $dados;
        $this->getPath();
        $this->_rest = new rest_cliente01($this->_link);
        $this->_rest->setHeader('Authorization', 'Bearer ' . $this->_token);
      
    }

    /**
     * @param string $email Email do usuario que tera o evento marcado
     * @return array Retorna as informações do evento criado
     */

    public function agendarEvento($email){
        $ret = '';
        $dados = $this->setDadosPost($this->_dados);
        
        $path = str_replace('{user-id}', $email, $this->_path['eventos']['POST']['criar']);
        $this->_rest->setPostData($dados);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
       
        $ret = $this->_rest->executa($path, 'POST', $param);
        return $ret;
    }



    /**
     * @param string $id O ID do evento a ser deletado
     * @param string $email o Email do usuario que tera o evento deletado
     * @return string retorna a confirmação que o evento foi deletado
     */

    public function deletarEvento($id, $email){
        $ret = '';
        
        $path = str_replace('{event-id}', $id,$this->_path['eventos']['DELETE']['excluir']);
        $path = str_replace('{user-id}', $email, $path);
        
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
       
        $ret = $this->_rest->executa($path, 'DELETE', $param);
        return $ret;
    }

    /**
     * @param string $email Email do usuário
     * @return array Retorna todos os eventos ligados aquele email
     */


    public function pesquisarEventos($email){
        $ret = '';
        
        $path = str_replace('{user-id}', $email, $this->_path['eventos']['GET']['pesquisar']);
        $param = array();
        $this->_rest->setHeader('Content-Type', 'application/json');
       
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }


    private function getPath($versao = ''){
        switch ($versao) {
            case 1.3:
                break;
                
            default:
                $this->_path['eventos']['POST']['criar']	             = "/users/{user-id}/events";
                $this->_path['eventos']['DELETE']['excluir']	         = "/users/{user-id}/events/{event-id}";
                $this->_path['eventos']['GET']['pesquisar'] =              "/users/{user-id}/events";
                break;
        }
    }

    private function getToken(){

        $ret = '';
        
        //$this->requestAuthorizationToken();
        //die();
        
        $tenant = "ec5eb142-7143-4b19-b157-74cfcd88eb39";
        $client_id = "01c4745d-a69c-405a-93bd-e5c68b35182a";
       // $scope = "https%3A%2F%2Fgraph.microsoft.com%2F.default";
        $scope = 'https://graph.microsoft.com/.default';
        $client_secret = 'wyf8Q~3uDK5A.CVXj42dLQz0W.vfh-Jh3NFQHcPy';
        $grant_type =  'client_credentials';
        $url = "https://login.microsoftonline.com/$tenant/oauth2/v2.0/token";
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "client_id=$client_id&scope=$scope&client_secret=".urlencode($client_secret)."&grant_type=$grant_type");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        
        
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $server_output = curl_exec ($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close ($ch);
        
        if ($httpCode == 200) //{ ... } else { ... }
        {
            $retorno = json_decode($server_output,true);
     //       var_dump($retorno);
         //   die();
            $ret = $retorno['access_token'];
            
        }
       
        return $ret;
    }
    
    private function requestAuthorizationToken(){
        
        $ret = '';
        
        $tenant = "ec5eb142-7143-4b19-b157-74cfcd88eb39";
        $client_id = "01c4745d-a69c-405a-93bd-e5c68b35182a";
        //$response_type
        $response_type = "code";
        $response_mode = 'query';
        //$redirect_uri (opcional)
        $redirect_uri = "http://localhost/";
        //$scope(lista de permissões separadas por espaço ex: offline_access%20user.read%20mail.read
        $scope = "https://graph.microsoft.com/Calendars.ReadWrite";
        $url = "https://login.microsoftonline.com/$tenant/oauth2/v2.0/authorize";
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "client_id=$client_id&scope=$scope&response_type=$response_type&response_mode=$response_mode&redirect_uri=$redirect_uri");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        
        
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $server_output = curl_exec ($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close ($ch);
        
     //   var_dump($server_output);
        
        if ($httpCode == 200) //{ ... } else { ... }
        {
            $retorno = json_decode($server_output,true);
            $ret = $retorno['access_token'];
        }
        
        return $ret;
    }



    /**
     * @param array $dados Parametros a serem passado para a api:  
     *                          titulo - Titulo do evento
     *                          local - Local onde o evento se passa
     *                          email - Email da agenda que recebera o evento
     *                          turno - Horario em que o evento se passa
     * 
     */
    private function setDadosPost($dados){
        $dia = $dados['dia'];

        $dadosPost = array(
            'subject'=> $dados['titulo'],

            'body'=> array(
                'contentType'=> 'HTML',
                'content' => $dados['tarefa']
            ),
            

            "location" => array(
                'displayName' => $dados['local']
            ), 

            "attendees" => array( array(
                'emailAddress'=> array(
                    'address' => $dados['email'],
                    'name' => $dados['nome']

                ),

                'type' => 'required'
                    

            )
            )
        );


       

        if($dados['turno'] == 'M'){
            $dadosPost['start'] = array(
                
                'dateTime' => date("Y-m-d", strtotime($dia)) . "T08:00:00",
                'timeZone' => 'America/Sao_Paulo'
            );


            $dadosPost['end'] = array(
                'dateTime' => date("Y-m-d", strtotime($dia)). "T12:00:00",
                'timeZone' => 'America/Sao_Paulo'
            );

        }else if($dados['turno'] == 'T'){
            $dadosPost['start'] = array(
                
                'dateTime' => date("Y-m-d", strtotime($dia)) . "T13:00:00",
                'timeZone' => 'America/Sao_Paulo'
            );


            $dadosPost['end'] = array(
                'dateTime' => date("Y-m-d", strtotime($dia)). "T18:00:00",
                'timeZone' => 'America/Sao_Paulo'
            );
        }else if($dados['turno'] == 'N'){
            $dadosPost['start'] = array(
                
                'dateTime' => date("Y-m-d", strtotime($dia)) . "T18:00:00",
                'timeZone' => 'America/Sao_Paulo'
            );


            $dadosPost['end'] = array(
                'dateTime' => date("Y-m-d", strtotime($dia)). "T22:00:00",
                'timeZone' => 'America/Sao_Paulo'
            );
        }else if($dados['turno'] == 'I'){
            $dadosPost['start'] = array(
                
                    'dateTime' => date("Y-m-d", strtotime($dia)) . "T08:00:00",
                    'timeZone' => 'America/Sao_Paulo'
            );


                $dadosPost['end'] = array(
                    'dateTime' => date("Y-m-d", strtotime($dia)). "T22:00:00",
                    'timeZone' => 'America/Sao_Paulo'
                );
                
                
        }
        return $dadosPost;
    }

   

       
}