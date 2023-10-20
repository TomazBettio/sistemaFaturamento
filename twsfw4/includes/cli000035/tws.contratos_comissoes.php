<?php

/*
 * Data Criacao: 27/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Include para criar e editar comissões
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class contratos_comissoes {
    // Classe form01
    private $_form;

    function __construct() {
        $this->_form = new form01();
        $this->_form->setDescricao('Comissões');
    }

    public function incluir() {
        $ret = '';

        $id = $_GET['id'] ?? '';

        if(!empty($id)) {
            $sql = "SELECT * FROM contratos_comissoes WHERE id_contratos_comissoes = $id";
            $row = query($sql);
            $row = (is_array($row) && count($row) > 0) ? $row[0] : [];
        }

        $param = [];
        $param['campo'] = 'a_partir_de';
        $param['etiqueta'] = 'A Partir de';
        $param['largura'] = '2';
        $param['tipo'] = 'D';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['valor'] = isset($row['a_partir_de']) ? Datas::dataMS2D($row['a_partir_de']) : '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'valor1';
        $param['etiqueta'] = 'Valor até:';
        $param['largura'] = '2';
        $param['tipo'] = 'V';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['linha'] = 1;
        $param['valor'] = $row['valor1'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'porcentagem1';
        $param['etiqueta'] = 'Recebe a porcentagem:';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['linha'] = 1;
        $param['valor'] = $row['porcentagem1'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'valor2';
        $param['etiqueta'] = 'Valor até:';
        $param['largura'] = '2';
        $param['tipo'] = 'V';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['linha'] = 2;
        $param['valor'] = $row['valor2'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'porcentagem2';
        $param['etiqueta'] = 'Recebe a porcentagem:';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['linha'] = 2;
        $param['valor'] = $row['porcentagem2'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'valor3';
        $param['etiqueta'] = 'Valor até:';
        $param['largura'] = '2';
        $param['tipo'] = 'V';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['linha'] = 3;
        $param['valor'] = $row['valor3'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'porcentagem3';
        $param['etiqueta'] = 'Recebe a porcentagem:';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['linha'] = 3;
        $param['valor'] = $row['porcentagem3'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'porcentagem4';
        $param['etiqueta'] = 'Porcentagem dos demais:';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        // $param['pasta'] = 1;
        $param['linha'] = 4;
        $param['valor'] = $row['porcentagem4'] ?? '';
        $this->_form->addCampo($param);

        if(!empty($id)) {
            $param = [];
            $param['campo'] = 'ativo';
            $param['etiqueta'] = 'Ativo:';
            $param['largura'] = '2';
            $param['tipo'] = 'A';
            $param['tabela_itens'] = '000003';
            $param['obrigatorio'] = true;
            // $param['pasta'] = 1;
            $param['linha'] = 4;
            $param['valor'] = $row['ativo'] ?? '';
            $this->_form->addCampo($param);
        }

        $this->_form->setEnvio(getLink() . "salvar&id=$id", 'formEditarComissoes');

        $ret .= $this->_form;

        return $ret;
    }

    public function salvar() {
        if(count($_POST) > 0) {
            $id = $_GET['id'] ?? '';

            $valor1 = str_replace('.', '', $_POST['valor1']);
            $valor1 = str_replace(',', '.', $valor1);

            $valor2 = str_replace('.', '', $_POST['valor1']);
            $valor2 = str_replace(',', '.', $valor2);

            $valor3 = str_replace('.', '', $_POST['valor1']);
            $valor3 = str_replace(',', '.', $valor3);

            $temp = [];
            $temp['a_partir_de']    = Datas::dataD2S($_POST['a_partir_de']);
            $temp['valor1']         = $valor1;
            $temp['porcentagem1']   = $_POST['porcentagem1'];
            $temp['valor2']         = $valor2;
            $temp['porcentagem2']   = $_POST['porcentagem2'];
            $temp['valor3']         = $valor3;
            $temp['porcentagem3']   = $_POST['porcentagem3'];
            $temp['porcentagem4']   = $_POST['porcentagem4'];
            $temp['ativo']          = $_POST['ativo'] ?? 'S';

            if(empty($id)) {
                $tipo = 'INSERT';
                $where = '';

                $msgm = "Comissões registradas com sucesso";
            } else {
                $tipo = 'UPDATE';
                $where = "id_contratos_comissoes = $id";

                $msgm = "Comissões editadas com sucesso";
            }

            $sql = montaSQL($temp, 'contratos_comissoes', $tipo, $where);
            query($sql);

            $tipo_msgm = '';
            $redireciona = 'index';
        } else {
            $msgm = "Erro ao receber os dados!";
            $tipo_msgm = 'erro';
            $redireciona = 'incluir';
        }

        redireciona(getLink() . "avisos&mensagem=$msgm&tipo=$tipo_msgm&redireciona=$redireciona");
    }
}