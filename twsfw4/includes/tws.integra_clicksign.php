<?php
/*
 * Data Criacao 26/01/2022
 * Autor: TWS - Emanuel Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class integra_clicksign{
    private $_url = '';
    private $_chave = '';
    private $_rest;
    
    public function __construct($chave, $teste = false){
        $this->_url = $teste ? 'https://sandbox.clicksign.com' : 'https://app.clicksign.com';
        $this->_chave = $chave;
        
        $this->_rest = new rest_cliente01($this->_url);
    }
    
    public function documentoViaModelo($documento, $parametros, $path_arquivo){
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        
        $dados = array('document' => array('path' => $path_arquivo, 'template' => array('data' => $parametros)));
        $this->_rest->setPostData($dados);
        $path = '/api/v1/templates/{documento}/documents?access_token=' . $this->_chave;
        $path = str_replace('{documento}', $documento, $path);
        
        $param = array('access_token' => $this->_chave);
        $param = array();
        $resposta = $this->_rest->executa($path, 'POST', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing', json_encode($resposta));
        }
        return $resposta;
    }
    
    public function criarSignatario($dados){
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        $this->_rest->setPostData($dados);
        $path = '/api/v1/signers?access_token=' . $this->_chave;
        $param = array();
        $resposta = $this->_rest->executa($path, 'POST', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_sig', json_encode($resposta));
        }
        return $resposta;
    }
    
    public function vincularDocumentoSignatario($documento, $signatario, $opcoes){
        $opcoes['document_key'] = $documento;
        $opcoes['signer_key'] = $signatario;
        
        $dados = array('list' => $opcoes);
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        $this->_rest->setPostData($dados);
        
        $path = '/api/v1/lists?access_token=' . $this->_chave;
        $param = array();
        $resposta = $this->_rest->executa($path, 'POST', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_vinculo', json_encode($resposta));
        }
        return $resposta;
    }
    
    public function assinarDocumentoApi($chave_assinatura, $segredo){
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        
        $dados = array(
            'request_signature_key' => $chave_assinatura,
            'secret_hmac_sha256' => $this->gerarSegredoCompleto($chave_assinatura, $segredo),
        );
        $this->_rest->setPostData($dados);
        
        $path =  '/api/v1/sign?access_token=' . $this->_chave;
        $param = array();
        
        $resposta = $this->_rest->executa($path, 'POST', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_assinaturaApi', json_encode($resposta));
        }
        return $resposta;
    }
    
    private function gerarSegredoCompleto($chave_assinatura, $segredo_signatario){
        return hash_hmac('sha256', $chave_assinatura, $segredo_signatario);
    }
    
    public function excluirSignatario($signatario){
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        
        $path = "/api/v2/signers/$signatario?access_token=" . $this->_chave;
        $param = array();
        
        $resposta = $this->_rest->executa($path, 'DELETE', $param, true, true);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_exclusaoSignatario', json_encode($resposta));
        }
        return $resposta;
    }
    
    public function visualizarDocumento($documento){
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        
        $path = "/api/v1/documents/$documento?access_token=" . $this->_chave;
        $param = array();
        
        $resposta = $this->_rest->executa($path, 'GET', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_visuDocumento', json_encode($resposta));
        }
        return $resposta;
    }
    
    public function desvincularDocumentoSignatario($lista_assinantes, $signatario){
        //não é possível desvincular o signatario de um documento que ele já assinou
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        
        $path = "/api/v1/lists/$lista_assinantes?access_token=" . $this->_chave;
        $param = array();
        
        $resposta = $this->_rest->executa($path, 'DELETE', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_desvincDocSig', json_encode($resposta));
        }
        return $resposta;
    }
    
    public function excluirDocumento($documento){
        //funciona com documentos assinados e finalizados
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        
        $path = "/api/v1/documents/$documento?access_token=" . $this->_chave;
        $param = array();
        
        $resposta = $this->_rest->executa($path, 'DELETE', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_exclDoc', json_encode($resposta));
        }
        return $resposta;
    }
    
    public function visualizarSignatario($signatario){
        $this->_rest->setHeader('Accept', 'application/json');
        $this->_rest->setHeader('Content-Type', 'application/json');
        
        $path = "/api/v1/signers/$signatario?access_token=" . $this->_chave;
        $param = array();
        
        $resposta = $this->_rest->executa($path, 'GET', $param);
        if($resposta != false){
            log::gravaLog('respostas_clicksing_visuSig', json_encode($resposta));
        }
        return $resposta;
    }
    
}