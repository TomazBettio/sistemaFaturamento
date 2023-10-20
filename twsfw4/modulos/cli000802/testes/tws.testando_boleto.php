<?php

// include '/var/www/twsfw4_dev/includes/PHP-Object-Boleto-master/OB_init.php';

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class testando_boleto {
    var $funcoes_publicas = array(
        'index' 	=> true,
    );

    public function index() {
        $param = [];

        $param['banco'] = '237';

        // Vendedor
        $param['agencia'] = '0042';
        $param['conta'] = 19024188;
        $param['digitoConta'] = 1;
        $param['razao'] = 'Jose Claudio Medeiros de Lima';
        $param['cnpj'] = '18.847.308/0001-32';
        $param['endereco'] = 'Rua dos Mororós 111 Centro, São Paulo/SP CEP 12345-678';
        $param['emailVendedor'] = 'joseclaudiomedeirosdelima@uol.com.br';

        // Cliente
        $param['nome'] = 'Maria Joelma Bezerra de Medeiros';
        $param['cpf'] = '111.999.888-39';
        $param['emailCliente'] = 'mariajoelma85@hotmail.com';

        // Boleto
        $param['valor'] = 129.45;
        $param['diasVencimento'] = 5;
        $param['nossoNumero'] = 123456;
        $param['numeroDocumento'] = 873245;

        $boleto = new teste_boletos($param);

        return $boleto->index();

        // criaImagem('23796922400000129440042000000012345600000000');
    }
}