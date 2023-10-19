<?php

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class mercado_pago{
    var $funcoes_publicas = array(
        'index' 	=> true,
    );
    
    function index(){
        
        //$rest = new integra_mercado_pago('APP_USR-5519747617154394-060809-50b0f7c42660975ecc0639392e43e32b-260662116');
        /*
        $filtro = array(
            'begin_date' => '2019-05-01T00:00:00Z',
            'end_date' => '2019-06-01T00:00:00Z',
        );
        $resposta = $rest->gerarRelatorio($filtro);
        var_dump($resposta);
        */
        
        //$rest->baixarRelatorio($rest->pesquisarUltimoRelatorio(), 'jawiubc.csv');
        $chave = 'de34c296-8394-4d64-bcc5-43d64fbe0757';
        $rest = new integra_clicksign($chave, true);
        /*
        $dados = array(
            'Empresa' => 'p1',
            'Endereco Completo' => 'p2',
            'Cidade' => 'p3',
            'Estado' => 'p4',
            'CEP' => 'p5',
            'CNPJ' => 'p6',
            'Valor' => 'p7',
        );
        $modelo = '21fc4b25-f0e1-4fd0-b5ca-9e29743add30';
        $rest->documentoViaModelo($modelo, $dados, '/Modelos/Teste-123.docx');
        */
        /*
        $dados = array(
            'signer' => array(
                'email' => 'suporte@thielws.com.br',
                'phone_number' => '11999999999',
                'auths' => array('email'),
                'name' => 'suporte ws',
                'documentation' => "123.321.123-40",
                'birthday' => '1983-03-31',
                'has_documentation' => true,
                'selfie_enabled' => false,
                'handwritten_enabled' => false,
                'official_document_enabled' => false,
                'liveness_enabled' => false,
                'facial_biometrics_enabled' => false,
            ),
        );
        $rest->criarSignatario($dados);
        */
        $opcoes = array(
            'sign_as' => 'sign',
            'refusable' => true,
            'message' => 'kk eae meu assina isso ai',
        );
        $rest->vincularDocumentoSignatario('a50a3f21-6207-4eaa-8155-d76aeae019b5', '62aa4bbe-749b-46ea-a815-80fe398cfd25', $opcoes);
    }
}