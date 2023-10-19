<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_robo_incluir
{

    var $funcoes_publicas = array(
        'index'         => true,
    );

    public function index()
    {
        conectaMF();

        if (isset($_GET) && !empty($_GET)) {
            $id = base64_encode(time());
            $id = ajustaID($id);

            $param = [];
            $param['id'] = base64_decode($id);
            $param['razao']      = strtoupper($_GET['razao']) ?? '';
            $param['cnpj']       = $_GET['cnpj'] ?? '';
            $param['contrato']   = $_GET['contrato'] ?? '';
            $param['datactr']    = $_GET['data'] ?? '';
            $param['status']     = 'importado';
            $param['usuario']    = getUsuario();

            $sql = montaSQL($param, 'mgt_monofasico');
            queryMF($sql);
        }
    }
}
