<?php

//REST Para o API Flask
//debug: ,true,true no executa
class rest_fast{
    private $_link;
    private $_path = array();
    private $_rest;
    
    function __construct($link, $usuario = '', $senha = ''){
        $this->_link = $link;
        $this->getPath();
        $this->_rest = new rest_cliente01($this->_link);
        
        //Envio de header de teste
      //  if(!empty($usuario) && !empty($senha)){
            $this->_rest->setHeader('Authorization',  base64_encode($usuario . ':' . $senha));
      //  }
        
    }
    //GETs
    function dadosIndex()
    {
        $path = $this->_path['index']['GET']['all'];
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function dadosPauta($reuniao)
    {
        $path = $this->_path['editar']['GET']['pautas'];
        $path = str_replace('{reuniao}', $reuniao, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function infoReuniao($reuniao)
    {
        $path = $this->_path['editar']['GET']['info'];
        $path = str_replace('{reuniao}', $reuniao, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getParticipantesPauta($reuniao)
    {
        $path = $this->_path['editar']['GET']['pPauta'];
        $path = str_replace('{reuniao}', $reuniao, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getParticipantesReuniao($reuniao,$tipo)
    {
        $path = $this->_path['editar']['GET']['participantes'];
        $path = str_replace('{reuniao}', $reuniao, $path);
        $path = str_replace('{tipo}', $tipo, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getValoresSys5($tabela)
    {
        //                $this->_path['sys5']['GET']['tabela']             = '/api/crm/v1/tabela/{tabela}/lista';
        $path = $this->_path['sys5']['GET']['tabela'] ;
        $path = str_replace('{tabela}', $tabela, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getNomeUsuario($user)
    {
        //$this->_path['usuarios']['GET']['nome']             = '/api/crm/v1/usuarios/{usuario}/nome';
        $path = $this->_path['usuarios']['GET']['nome'] ;
        $path = str_replace('{usuario}', $user, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getListaUsuarios()
    {
        //                $this->_path['usuarios']['GET']['all']            = '/api/crm/v1/usuarios/lista';
        $path = $this->_path['usuarios']['GET']['all']   ;
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getUsuariosCB()
    {
        //                $this->_path['usuarios']['GET']['tipo']           = '/api/crm/v1/usuarios/tipos';
        $path = $this->_path['usuarios']['GET']['CB'] ;
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getTiposUsuarios()
    {
        //                $this->_path['usuarios']['GET']['tipo']           = '/api/crm/v1/usuarios/tipos';
        $path = $this->_path['usuarios']['GET']['tipo'] ;
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getClienteNreduz($cod)
    {
        //                $this->_path['cliente']['GET']['nreduz']          = '/api/crm/v1/cliente/{cod}/nreduz';
        $path = $this->_path['cliente']['GET']['nreduz']   ;
        $path = str_replace('{cod}', $cod, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getFormConvidados($reuniao)
    {
        //query montaformconvidados
        //$this->_path['participantes']['GET']['all']	      = '/api/crm/v1/editar/participantes/info';
        //$this->_path['participantes']['GET']['reuniao']	  = '/api/crm/v1/editar/participantes/{reuniao}/info';
        if($reuniao == 0){
            $path = $this->_path['participantes']['GET']['all']	;
        } else {
            $path = $this->_path['participantes']['GET']['reuniao'];
            $path = str_replace('{reuniao}', $reuniao, $path);
        }
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getDadosAcao($acao)
    {
        //    $this->_path['acao']['GET']['info']               = '/api/crm/v1/acao/{acao}/dados';
        $path = $this->_path['acao']['GET']['info']  ;
        $path = str_replace('{acao}', $acao, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getAcoes($reuniao,$pauta)
    {
        //    $this->_path['acao']['GET']['all']                = '/api/crm/v1/{reuniao}/{pauta}/acao/lista';
        $path = $this->_path['acao']['GET']['all']   ;
        $path = str_replace('{reuniao}', $reuniao, $path);
        $path = str_replace('{pauta}', $pauta, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }

    function getInfoPauta($pauta)
    {
        //                $this->_path['pauta']['GET']['info']              = '/api/crm/v1/pauta/{pauta}/info';
        $path = $this->_path['pauta']['GET']['info'];
        $path = str_replace('{pauta}', $pauta, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getAnexosReuniao($reuniao)
    {
        //                $this->_path['reuniao']['GET']['anexo']           = '/api/crm/v1/reuniao/{reuniao}/anexo';
        $path = $this->_path['reuniao']['GET']['anexo']  ;
        $path = str_replace('{reuniao}', $reuniao, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getMeuAnexo($id)
    {
        //                $this->_path['anexo']['GET']['info']              = '/api/crm/v1/anexo/{anexo}';
        $path = $this->_path['anexo']['GET']['info'];
        $path = str_replace('{anexo}', $id, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getHistoricoComentarios($id)
    {
        $path = $this->_path['pauta']['GET']['comentario'];
        $path = str_replace('{pauta}', $id, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getParticipantesIdSalvar($reuniao,$tipo)
    {
        //$this->_path['salvar']['GET']['participante']['id']= '/api/crm/v1/participante/{reuniao}/{tipo}';
        $path = $this->_path['salvar']['GET']['participante']['id'];
        $path = str_replace('{reuniao}', $reuniao, $path);
        $path = str_replace('{tipo}', $tipo, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    function getParticipantesNomeSalvar($reuniao,$nome)
    {
        //$this->_path['salvar']['GET']['participante']     = '/api/crm/v1/participante/{reuniao}/{nome}';
        $path = $this->_path['salvar']['GET']['participante']['nome'];
        $path = str_replace('{reuniao}', $reuniao, $path);
        $path = str_replace('{nome}', $nome, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
        
    }
    
    function getRespAcao($acao)
    {
       // $this->_path['acao']['GET']['responsavel']       = '/api/crm/v1/acao/{acao}/responsavel/info';
        $path = $this->_path['acao']['GET']['responsavel']   ;
        $path = str_replace('{acao}', $acao, $path);
        $ret = $this->_rest->executa($path, 'GET', []);
        return $ret;
    }
    
    //POST&PUTs
    
   // $this->_path['acao']['POST']['responsavel']      = '/api/crm/v1/acao/{acao}/responsavel/{resp}/salvar';
    function salvarRespAcao($acao,$rep)
    {
        $path = $this->_path['acao']['POST']['responsavel'] ;
        $path = str_replace('{acao}', $acao, $path);
        $path = str_replace('{resp}', $rep, $path);
        $ret = $this->_rest->executa($path, 'POST', []);
        return $ret;
    }
    
    function salvarReuniao($dados, $reuniao=0)
    {
        if($reuniao == 0){
            $path = $this->_path['salvar']['POST']['reuniao'];
            $this->_rest->setPostData($dados);
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'POST', []);
        } else {
            $path = $this->_path['salvar']['PUT']['reuniao'];
            $path = str_replace('{reuniao}', $reuniao, $path);
            $this->_rest->setPostData($dados);
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'PUT', [],true,true);
        }
        return $ret;
    }
    
    function salvarParticipante($reuniao,$dados)
    {
        $path = $this->_path['salvar']['PUT']['participante'] ;
        $path = str_replace('{reuniao}', $reuniao, $path);
        $this->_rest->setPostData($dados);
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'PUT', []);
        return $ret;
    }
    
    function salvarNovoParticipante($reuniao,$dados)
    {
        $path = $this->_path['salvar']['POST']['participante'] ;
        $path = str_replace('{reuniao}', $reuniao, $path);
        $this->_rest->setPostData($dados);
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', []);
        return $ret;
    }
    
    function salvarAnexo($reuniao,$arquivo)
    {
        //         = '/api/crm/v1/reuniao/new/{reuniao}/anexo/{arquivo}';
        $path = $this->_path['reuniao']['POST']['anexo'];
        $path = str_replace('{reuniao}', $reuniao, $path);
        $path = str_replace('{arquivo}', $arquivo, $path);
        
        $ret = $this->_rest->executa($path, 'POST', []);
        return $ret;
    }
    
    function salvarComentario($pauta, $dados)
    {
        //$this->_path['pauta']['POST']['comentario']       = '/api/crm/v1/pauta/{pauta}/comentario/new';
        $path = $this->_path['pauta']['POST']['comentario']   ;
        $path = str_replace('{pauta}', $pauta, $path);
        $this->_rest->setPostData($dados);
        $this->_rest->setHeader('Content-Type', 'application/json');
        $ret = $this->_rest->executa($path, 'POST', []);
        return $ret;
    }
    
    function salvarPauta($pauta,$dados)
    {
        // = '/api/crm/v1/pauta/new';
        if($pauta==0){
            $path = $this->_path['pauta']['POST']['salvar']; 
            $this->_rest->setPostData($dados);
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'POST', []);
        } else {
                    //           = '/api/crm/v1/pauta/{pauta}/update';
            $path = $this->_path['pauta']['PUT']['salvar'] ;
            $path = str_replace('{pauta}', $pauta, $path);
            $this->_rest->setPostData($dados);
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'PUT', []);
        }
        return $ret;
    }
    
    function salvarAcao($acao,$dados)
    {
        if($acao==0){
            $path = $this->_path['acao']['POST']['salvar'] ;
            $this->_rest->setPostData($dados);
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'POST', []);
        } else {
            $path = $this->_path['acao']['PUT']['salvar'] ;
            $path = str_replace('{acao}', $acao, $path);
            $this->_rest->setPostData($dados);
            $this->_rest->setHeader('Content-Type', 'application/json');
            $ret = $this->_rest->executa($path, 'PUT', []);
        }
        return $ret;
    }
    
    //DELETEs
    function deleteParticipante($id)
    {
       // $this->_path['salvar']['DELETE']['participante']  = '/api/crm/v1/participante/exclui/{id}';
        $path = $this->_path['salvar']['DELETE']['participante'];
        $path = str_replace('{id}', $id, $path);
        $ret = $this->_rest->executa($path, 'DELETE', [],true,true);
        return $ret;
    }
    
    function deleteRespAcao($acao,$rep)
    {
        // $this->_path['acao']['DELETE']['responsavel']    = '/api/crm/v1/acao/{acao}/responsavel/{resp}/exclui';
        $path = $this->_path['acao']['DELETE']['responsavel'] ;
        $path = str_replace('{acao}', $acao, $path);
        $path = str_replace('{resp}', $rep, $path);
        $ret = $this->_rest->executa($path, 'DELETE', [],true,true);
        return $ret;
    }
    
   // $this->_path['excluir']['DELETE']['acao']         = '/api/crm/v1/acao/exclui/{id}';
    function deleteAcao($id)
    {
        $path = $this->_path['excluir']['DELETE']['acao'];
        $path = str_replace('{id}', $id, $path);
        $ret = $this->_rest->executa($path, 'DELETE', [],true,true);
        return $ret;
    }
   // $this->_path['excluir']['DELETE']['pauta']        = '/api/crm/v1/pauta/exclui/{id}';
    function deletePauta($id)
    {
        $path = $this->_path['excluir']['DELETE']['pauta'];
        $path = str_replace('{id}', $id, $path);
        $ret = $this->_rest->executa($path, 'DELETE', [],true,true);
        return $ret;
    }
   // $this->_path['excluir']['DELETE']['reuniao']      = '/api/crm/v1/reuniao/exclui/{id}';
    function deleteReuniao($id)
    {
        $path = $this->_path['excluir']['DELETE']['reuniao'];
        $path = str_replace('{id}', $id, $path);
        $ret = $this->_rest->executa($path, 'DELETE', [],true,true);
        return $ret;
    }
    
    
    //paths
    private function getPath($versao = '')
    {
        switch ($versao) 
        {
            case 1.3:
                break;
                
            default:
                //index
                $this->_path['index']['GET']['all']	              = '/api/crm/v1/index';
                //editar reunião
                $this->_path['editar']['GET']['info']	          = '/api/crm/v1/editar/{reuniao}/info';
                $this->_path['editar']['GET']['pautas']	          = '/api/crm/v1/editar/{reuniao}/pautas';
                $this->_path['editar']['GET']['pPauta']	          = '/api/crm/v1/editar/{reuniao}/participantes';
                $this->_path['editar']['GET']['participantes']	  = '/api/crm/v1/editar/{reuniao}/participantes/{tipo}';
                //query montaformconvidados
                $this->_path['participantes']['GET']['all']	      = '/api/crm/v1/editar/participantes/info';
                $this->_path['participantes']['GET']['reuniao']	  = '/api/crm/v1/editar/participantes/{reuniao}/info';
                
                //pauta
                //query getinfopauta
                $this->_path['pauta']['GET']['info']              = '/api/crm/v1/pauta/{pauta}/info';
                
                //acao
                //query getDadosAcao
                $this->_path['acao']['GET']['info']               = '/api/crm/v1/acao/{acao}/dados';
                //query getAcoes
                $this->_path['acao']['GET']['all']                = '/api/crm/v1/{reuniao}/{pauta}/acao/lista';
                
                //anexos
                $this->_path['reuniao']['GET']['anexo']           = '/api/crm/v1/reuniao/{reuniao}/anexo';
                $this->_path['anexo']['GET']['info']              = '/api/crm/v1/anexo/{anexo}';
                $this->_path['reuniao']['POST']['anexo']          = '/api/crm/v1/reuniao/new/{reuniao}/anexo/{arquivo}';
                
                
                //comentarios
                $this->_path['pauta']['POST']['comentario']       = '/api/crm/v1/pauta/{pauta}/comentario/new';
                //query gerahistoricocomentarios
                $this->_path['pauta']['GET']['comentario']        = '/api/crm/v1/pauta/{pauta}/comentario';
                
                
                //salvar
                $this->_path['salvar']['POST']['reuniao']         = '/api/crm/v1/salvar/new';
                $this->_path['salvar']['PUT']['reuniao']          = '/api/crm/v1/salvar/{reuniao}/update';
                //query salvarParticipantes
                $this->_path['salvar']['PUT']['participante']     = '/api/crm/v1/salvar/{reuniao}/participante';
                $this->_path['salvar']['POST']['participante']    = '/api/crm/v1/salvar/{reuniao}/participante/new';
                $this->_path['salvar']['GET']['participante']['nome']= '/api/crm/v1/participante/{reuniao}/{nome}';
                $this->_path['salvar']['GET']['participante']['id']= '/api/crm/v1/participante/{reuniao}/{tipo}';
                
                $this->_path['salvar']['DELETE']['participante']  = '/api/crm/v1/participante/exclui/{id}';
                
                //query salvarPauta
                $this->_path['pauta']['POST']['salvar']           = '/api/crm/v1/pauta/new';
                $this->_path['pauta']['PUT']['salvar']            = '/api/crm/v1/pauta/{pauta}/update';
                //query salvarAcao
                $this->_path['acao']['POST']['salvar']            = '/api/crm/v1/acao/new';
                $this->_path['acao']['PUT']['salvar']             = '/api/crm/v1/acao/{acao}/update';
                //query salvarEnvolvidos
                $this->_path['acao']['GET']['responsavel']       = '/api/crm/v1/acao/{acao}/responsavel/info';
                $this->_path['acao']['DELETE']['responsavel']    = '/api/crm/v1/acao/{acao}/responsavel/{resp}/exclui';
                $this->_path['acao']['POST']['responsavel']      = '/api/crm/v1/acao/{acao}/responsavel/{resp}/salvar';

                //excluir
                $this->_path['excluir']['DELETE']['acao']         = '/api/crm/v1/acao/exclui/{id}';
                $this->_path['excluir']['DELETE']['pauta']        = '/api/crm/v1/pauta/exclui/{id}';
                $this->_path['excluir']['DELETE']['reuniao']      = '/api/crm/v1/reuniao/exclui/{id}';
                
                
                //Outros
                //query getValoresSYS5
                $this->_path['sys5']['GET']['tabela']             = '/api/crm/v1/tabela/{tabela}/lista';
                //query getClienteNreduz
                $this->_path['cliente']['GET']['nreduz']          = '/api/crm/v1/cliente/{cod}/nreduz';
                //query getListaUsuarios
                $this->_path['usuarios']['GET']['all']            = '/api/crm/v1/usuarios/lista';
                //query getlistatiposusuarios
                $this->_path['usuarios']['GET']['tipo']           = '/api/crm/v1/usuarios/tipos';
                //query getallusuarioscb
                $this->_path['usuarios']['GET']['CB']             = '/api/crm/v1/usuarios/CB';
                //query getnomeusuario
                $this->_path['usuarios']['GET']['nome']             = '/api/crm/v1/usuarios/{usuario}/nome';
                
                break;
        }
    }
   
}