<?php

/*
 * Data Criacao: 10/07/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Classe usada para importar os percentuais de contratos do excel do Leandro para o banco de dados
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class importa_excel {
    var $funcoes_publicas = array(
        'index'                 => true,
    );

    function __construct() {
        conectaERP();
    }

    public function index() {
        $html = '';
        $dados = fopen("/var/www/twsfw4/temp/GESTÃO_CONTRATOS.csv", 'r');

        while($linha = fgetcsv($dados, null, ';')) {
            // print_r($linha);

            $temp = [];
            preg_match_all('/\d{1,2}%|\d{1,2},\d{1,2}%\b/', $linha[4], $temp);
            // var_dump($temp[0]);

            $porcentagem = '';
            foreach($temp[0] as $p) {
                $p = str_replace(['%', ','], ['', '.'], $p);
                if($p >= 20 && $p <= 30) {
                    $porcentagem = $p;
                }
            }

            if(!empty($porcentagem)) {
                $sql = "SELECT CTRNROCONTRATO, CTRCREDITOAPURADO, FREPORCENTAGEMPARCELAS, CTR_HONORARIOS
                        FROM CONTRATOS WHERE CTRNROCONTRATO = '{$linha[2]}' AND CTRCREDITOAPURADO IS NULL
                            AND FREPORCENTAGEMPARCELAS IS NULL AND (CTR_HONORARIOS IS NULL OR CTR_HONORARIOS = 0)";
                $row = queryERP($sql);
                
                if(is_array($row) && count($row) == 1) {
                    $html .= "<li>Contrato: <b>{$linha[2]}</b> -> $porcentagem%</li>";

                    $sql = "UPDATE CONTRATOS SET CTR_HONORARIOS = $porcentagem
                            WHERE CTRNROCONTRATO = '{$linha[2]}' AND CTRCREDITOAPURADO IS NULL
                                AND FREPORCENTAGEMPARCELAS IS NULL AND (CTR_HONORARIOS IS NULL OR CTR_HONORARIOS = 0)";
                    // queryERP($sql);
                    echo $sql . "<br>\n";
                }
            }
        }

        $ret = "<h1>Lista dos contratos alterados no banco</h1>
                <ul>$html</ul>";

        // $param = [];
        // $param['destinatario'] = 'rafael.postal@verticais.com.br';
        // $param['mensagem'] = $ret;
        // $param['assunto'] = 'Contratos encontrados no excel';
        // enviaEmail($param);

        return $ret;
    }

    private function buscaTexto2($subs, $texto, $ate = '<br />') {
        $ret = '';

        if(is_string($texto) && strlen($texto) > 0){
            $ini = strpos($texto, $subs);
            if($ini !== false) {
                $ini += strlen($subs);
                $fim = strpos($texto, $ate, $ini);
                $fim = ($fim === false) ? strpos($texto, '<br />', $ini) : $fim;

                $total = $fim - $ini;

                $ret = substr($texto, $ini, $total);
            }
        }

        return $ret;
    }
}