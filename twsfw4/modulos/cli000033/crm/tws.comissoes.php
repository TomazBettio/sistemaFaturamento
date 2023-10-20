<?php

/*
 * Data Criacao: 05/06/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para ajuste de comissões
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class comissoes {
    var $funcoes_publicas = array(
        'index'             => true,
        'avisos'            => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    // Classe form01
    private $_form;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';
		$param['ordenacao'] = false;
		$param['titulo'] = 'Comissões';
		$this->_tabela = new tabela01($param);
    }

    public function avisos() {
		$tipo = $_GET['tipo'] ?? '';
        $redireciona = $_GET['redireciona'] ?? 'index';

		if ($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->{$redireciona}();
	}

    public function index() {
        $ret = '';

        $sql = "SELECT * FROM comissoes";
        $rows = query($sql);

        if(is_array($rows) && count($rows)) {
            $this->_form = new form01();
            $this->_form->setDescricao('Comissões');

            foreach($rows as $row) {
                switch($row['estado']) {
                    case 'assistentes':
                        $this->setFormAssistentes($row);
                        break;
                    case 'sc':
                        $this->setFormNormal($row, 2);
                        break;
                    case 'pr':
                        $this->setFormPr($row);
                        break;
                    case 'rs':
                        $this->setFormNormal($row, 4);
                        break;
                    case 'acao_judicial':
                    case 'dolly':
                        $this->setFormOutros($row);
                        break;
                }
            }
            
            // $param = [];
            // $param['campo'] = 'estado';
            // $param['etiqueta'] = 'Tipo';
            // $param['largura'] = '2';
            // $param['tipo'] = 'C';
            // // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
            // $param['obrigatorio'] = true;
            // $param['pasta'] = 1;
            // $param['valor'] = $pedido[0]['estado'] ?? '';
            // $form->addCampo($param);

            $this->_form->setEnvio(getLink() . "salvar", 'formEditarComissoes');
            $this->_form->setPastas([1 => 'Assistentes', 2 => 'SC', 3 => 'PR', 4 => 'RS', 5 => 'Outros']);
            
            $ret .= $this->_form;

        }

        return $ret;
    }

    private function setFormAssistentes($row) {
        $param = [];
        $param['campo'] = 'ca_assistentes';
        $param['etiqueta'] = 'CA';
        $param['largura'] = '2';
        $param['tipo'] = 'C';
        // $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
        $param['obrigatorio'] = true;
        $param['pasta'] = 1;
        $param['valor'] = $row['ca'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'parcelas_de_contrato_assistentes';
        $param['etiqueta'] = 'Parcelas de Contrato';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['parcelas_de_contrato'] ?? '';
        $param['pasta'] = 1;
        $this->_form->addCampo($param);


        $aporte = json_decode($row['aporte']);

        $param = [];
        $param['campo'] = 'aporte01_assistentes';
        $param['etiqueta'] = 'Aporte 01 Contrato';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $aporte[1] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 1;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'aporte02_assistentes';
        $param['etiqueta'] = 'Aporte 02 Contratos';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $aporte[2] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 1;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'aporte03_assistentes';
        $param['etiqueta'] = 'Aporte 03 Contratos';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $aporte[3] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 1;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'aporte04_assistentes';
        $param['etiqueta'] = 'Aporte 04 Contratos';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $aporte[4] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 1;
        $this->_form->addCampo($param);


        $contrato_premium = json_decode($row['contratos_premium']);

        $param = [];
        $param['campo'] = 'contrato_premium01_assistentes';
        $param['etiqueta'] = 'Contratos Premium 01 Contrato';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $contrato_premium[1] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 2;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'contrato_premium02_assistentes';
        $param['etiqueta'] = 'Contratos Premium 02 Contratos';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $contrato_premium[2] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 2;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'contrato_premium03_assistentes';
        $param['etiqueta'] = 'Contratos Premium 03 Contratos';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $contrato_premium[3] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 2;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'contrato_premium04_assistentes';
        $param['etiqueta'] = 'Contratos Premium 04 Contratos';
        $param['largura'] = '3';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $contrato_premium[4] ?? '';
        $param['pasta'] = 1;
        $param['linha'] = 2;
        $this->_form->addCampo($param);
    }

    private function setFormNormal($row, $pasta) {
        $tipo = $row['estado'];

        $param = [];
        $param['campo'] = 'ca_'.$tipo;
        $param['etiqueta'] = 'CA';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['ca'] ?? '';
        $param['pasta'] = $pasta;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'parcelas_de_contrato_'.$tipo;
        $param['etiqueta'] = 'Parcelas de Contrato';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['parcelas_de_contrato'] ?? '';
        $param['pasta'] = $pasta;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'aporte_'.$tipo;
        $param['etiqueta'] = 'Aporte';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['aporte'] ?? '';
        $param['pasta'] = $pasta;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'contratos_premium_'.$tipo;
        $param['etiqueta'] = 'Contratos Premium';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['contratos_premium'] ?? '';
        $param['pasta'] = $pasta;
        $this->_form->addCampo($param);
    }

    private function setFormPr($row) {
        $ca = json_decode($row['ca']);

        $param = [];
        $param['campo'] = 'ca01_pr';
        $param['etiqueta'] = 'CA Tipo 01';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $ca[0] ?? '';
        $param['pasta'] = 3;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'ca02_pr';
        $param['etiqueta'] = 'CA Tipo 02';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $ca[1] ?? '';
        $param['pasta'] = 3;
        $this->_form->addCampo($param);


        $param = [];
        $param['campo'] = 'parcelas_de_contrato_pr';
        $param['etiqueta'] = 'Parcela de Contrato';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['parcelas_de_contrato'] ?? '';
        $param['pasta'] = 3;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'aporte_pr';
        $param['etiqueta'] = 'Aporte';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['aporte'] ?? '';
        $param['pasta'] = 3;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'contratos_premium_pr';
        $param['etiqueta'] = 'Contratos Premium';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['contratos_premium'] ?? '';
        $param['pasta'] = 3;
        $this->_form->addCampo($param);
    }

    private function setFormOutros($row) {
        $param = [];
        $param['campo'] = 'ca_'.$row['estado'];
        $param['etiqueta'] = ($row['estado'] == 'dolly') ? 'Dolly' : 'Ação Judicial';
        $param['largura'] = '2';
        $param['tipo'] = 'N';
        $param['obrigatorio'] = true;
        $param['valor'] = $row['ca'] ?? '';
        $param['pasta'] = 5;
        $this->_form->addCampo($param);
    }

    public function salvar() {
        if(is_array($_POST) && count($_POST) > 0) {

            // =========== ASSISTENTES =========================
            $ca = $_POST['ca_assistentes'];
            $parcelas = $_POST['parcelas_de_contrato_assistentes'];
            $aporte = [0, $_POST['aporte01_assistentes'], $_POST['aporte02_assistentes'], $_POST['aporte03_assistentes'], $_POST['aporte04_assistentes']];
            $aporte = json_encode($aporte);
            $contrato = [0, $_POST['contrato_premium01_assistentes'], $_POST['contrato_premium02_assistentes'], $_POST['contrato_premium03_assistentes'], $_POST['contrato_premium04_assistentes']];
            $contrato = json_encode($contrato);

            $sql = "UPDATE comissoes SET
                    ca = '$ca', parcelas_de_contrato = '$parcelas', aporte = '$aporte', contratos_premium = '$contrato'
                    WHERE estado = 'assistentes'";
            query($sql);


            // =========== SC =========================
            $ca         = $_POST['ca_sc'];
            $parcelas   = $_POST['parcelas_de_contrato_sc'];
            $aporte     = $_POST['aporte_sc'];
            $contrato   = $_POST['contratos_premium_sc'];

            $sql = "UPDATE comissoes SET
                    ca = '$ca', parcelas_de_contrato = '$parcelas', aporte = '$aporte', contratos_premium = '$contrato'
                    WHERE estado = 'sc'";
            query($sql);


            // =========== PR =========================
            $ca         = [$_POST['ca01_pr'], $_POST['ca02_pr']];
            $ca         = json_encode($ca);
            $parcelas   = $_POST['parcelas_de_contrato_pr'];
            $aporte     = $_POST['aporte_pr'];
            $contrato   = $_POST['contratos_premium_pr'];

            $sql = "UPDATE comissoes SET
                    ca = '$ca', parcelas_de_contrato = '$parcelas', aporte = '$aporte', contratos_premium = '$contrato'
                    WHERE estado = 'pr'";
            query($sql);


            // =========== RS =========================
            $ca         = $_POST['ca_rs'];
            $parcelas   = $_POST['parcelas_de_contrato_rs'];
            $aporte     = $_POST['aporte_rs'];
            $contrato   = $_POST['contratos_premium_rs'];

            $sql = "UPDATE comissoes SET
                    ca = '$ca', parcelas_de_contrato = '$parcelas', aporte = '$aporte', contratos_premium = '$contrato'
                    WHERE estado = 'rs'";
            query($sql);

            // =========== AÇÃO JUDICIAL =========================
            $valor = $_POST['ca_acao_judicial'];

            $sql = "UPDATE comissoes SET ca = $valor WHERE estado = 'acao_judicial'";
            query($sql);


            // =========== DOLLY =========================
            $valor = $_POST['ca_dolly'];

            $sql = "UPDATE comissoes SET ca = $valor WHERE estado = 'dolly'";
            query($sql);


            redireciona(getLink() . 'avisos&mensagem=Comissões editadas com sucesso!');
        } else {
            redireciona(getLink() . 'avisos&mensagem=Erro ao receber as comissões&tipo=erro');
        }
    }
}