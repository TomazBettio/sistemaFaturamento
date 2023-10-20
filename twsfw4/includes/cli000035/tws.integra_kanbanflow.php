<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class integra_kanbanflow{
    private $_url = '';
    private $_chave = '';
    private $_rest;
    
    public function __construct($chave){
        $this->_url = 'https://kanbanflow.com/api/v1';
        $this->_chave = $chave;
        $this->getPath();
        
        //$this->_rest = new rest_cliente01($this->_url, true);
        $this->_rest = new rest_cliente01($this->_url, true);
    }
    
    public function getDadosBoard(){
        $path = $this->_path['board']['GET']['board'];
        $param = array(
            'apiToken' => $this->_chave,
        );
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getTodasTarefas(){
        $path = $this->_path['task']['GET']['todas'];
        $param = array(
            'apiToken' => $this->_chave,
        );
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getTarefaById($id){
        $path = $this->_path['task']['GET']['unica'];
        $path = str_replace('{id}', $id, $path);
        $param = array(
            'apiToken' => $this->_chave,
        );
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    public function getTarefaByParam($param = array()){
        $path = $this->_path['task']['GET']['param'];
        if(!is_array($param)){
            $param = array();
        }
        $param['apiToken'] = $this->_chave;
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    //eventos antigos tem prioridade na API caso a data_ini seja informada
    public function getEventos($data_ini = '', $data_fim = ''){
        $ret = array();
        if(!empty($data_ini) || !empty($data_fim)){
            $this->modificarRest();
            $path = $this->_path['board']['GET']['eventos'];
            $param = array('apiToken' => $this->_chave);
            if(!empty($data_ini)){
                $param['from'] = $data_ini;
            }
            if(!empty($data_fim)){
                $param['to'] = $data_fim;
            }
            $ret = $this->_rest->executa($path, 'GET', $param);
            $this->voltarRest();
        }
        return $ret;
    }
    
    //eventos antigos tem prioridade na API caso a data_ini seja informada
    public function getEventosTarefa($id, $data_ini = '', $data_fim = ''){
        $ret = array();
        if(!empty($id) && (!empty($data_ini) || !empty($data_fim))){
            $this->modificarRest();
            $path = $this->_path['task']['GET']['eventos'];
            $path = str_replace('{id}', $id, $path);
            $param = array('apiToken' => $this->_chave);
            if(!empty($data_ini)){
                $param['from'] = $data_ini;
            }
            if(!empty($data_fim)){
                $param['to'] = $data_fim;
            }
            $ret = $this->_rest->executa($path, 'GET', $param);
            $this->voltarRest();
        }
        return $ret;
    }
    
    //reescreve o rest para não substituir caracteres no URL
    private function modificarRest(){
        unset($this->_rest);
        $this->_rest = new rest_cliente01($this->_url, true);
    }
    
    //retorna o $_rest para o modelo original
    private function voltarRest(){
        unset($this->_rest);
        $this->_rest = new rest_cliente01($this->_url);
    }
    
    public function criarLabel($tarefa = '', $label = ''){
        $ret = false;
        if(!empty($tarefa) && !empty($label)){
            $path = $this->_path['task']['POST']['label'];
            $path = str_replace('{id}', $tarefa, $path);
            $path .= "?apiToken=" . $this->_chave;
            $this->_rest->setHeader('Content-Type', 'application/json');
            $this->_rest->setPostData(array('name' => $label));
            $param = array();
            $ret = $this->_rest->executa($path, 'POST', $param);
        }
        return $ret;
    }
    
    public function getComentariosTask($tarefa = ''){
        $ret = false;
        if(!empty($tarefa)){
            $path = $this->_path['task']['GET']['comentarios'];
            $path = str_replace('{id}', $tarefa, $path);
            $param = array('apiToken' => $this->_chave);
            $ret = $this->_rest->executa($path, 'GET', $param);
        }
        return $ret;
    }
    
    public function getUsuariosBoard(){
        $ret = array();
        $path = $this->_path['board']['GET']['usuarios'];
        $param = array('apiToken' => $this->_chave);
        $ret = $this->_rest->executa($path, 'GET', $param);
        return $ret;
    }
    
    private function getPath($versao = 1){
        switch ($versao) {
            default:
                $this->_path['board']['GET']['board']	    = '/board';
                
                $this->_path['task']['GET']['unica']	    = '/tasks/{id}';
                $this->_path['task']['GET']['todas']        = '/tasks';
                $this->_path['task']['GET']['param']        = '/tasks';
                
                $this->_path['board']['GET']['eventos']      = '/board/events';
                $this->_path['task']['GET']['eventos']	    = '/tasks/{id}/events';
                
                $this->_path['board']['GET']['usuarios']      = '/users';
                
                $this->_path['task']['GET']['comentarios']	    = '/tasks/{id}/comments';
                
                $this->_path['task']['POST']['label']        = '/tasks/{id}/labels';
                break;
        }
    }
}

class evento_kanbanflow{
    private $_id;
    private $_userId;
    private $_timeStamp;
    private $_tipo;
    public function __construct($json){
        $this->_id = $json['_id'];
        $this->_timeStamp = $json['timestamp'];
        $this->_userId = $json['userId'];
        $this->_tipo = $json['detailedEvents'][0]['eventType'] ?? '';
    }
    
    public function getTimeStamp(){
        return $this->_timeStamp;
    }
    
    public function getId(){
        return $this->_id;
    }
}