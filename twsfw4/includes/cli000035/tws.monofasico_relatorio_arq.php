<?php
/*
 * Data Criacao: 09/01/2023
 * Autor: Verticais - Rafael Postal
 *
 * Descricao: Envia um relatório dos últimos 7 dias referente aos valores trabalhados
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_relatorio_arq {
    // Diretório raiz
    private $_path;

    public function __construct(){
        global $config;
        $this->_path = $config['pathUpdMonofasico'];
    }

    public function enviarRelatorio() {
        $ret = '';

        $data_atual = Datas::dataD2S(Datas::data_hoje());
        $data_anterior = date("Y-m-d", strtotime($data_atual) - (7 * 24 * 60 * 60));

        $sql = "SELECT * FROM mgt_monofasico WHERE data_alt < '$data_atual' AND data_alt > '$data_anterior'";
        $rows = queryMF($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $tabelas = new monofasico_arquivos($row['cnpj'], $row['contrato'], $row['id']);
                $ret .= $tabelas->index(true);
                $ret .= '<hr>';
            }
        }

        $param = [];
        $param['destinatario'] = 'rafael.postal@verticais.com.br';
        $param['mensagem'] = $ret;
        $param['assunto'] = 'Teste relatório arquivos';
        enviaEmail($param);
    }
}