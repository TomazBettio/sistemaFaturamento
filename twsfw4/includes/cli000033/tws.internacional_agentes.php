<?php
/*
 * Data Criacao 15/09/2023
 * 
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Funcoes utilizadas gerar pagamentos
 *
 * Alteracoes:
 *
 */

 class internacional_agentes {

    // Classe tabela01
    private $_tabela;

    // Define se está apensa em modo visualização
    private $_visualizacao;

    function __construct() {
        $this->criaJs();
    }

    public function incluir($id = '', $visualizacao = false) {
        $ret = '';
        $this->_visualizacao = $visualizacao;

        if(!empty($id)) {
            $sql = "SELECT * FROM marpaagente
                    LEFT JOIN marpabancoagentes USING(codagente)
                    WHERE codagente = $id";
            $row = query2($sql);
            $row = $row[0];
        }

        $form = new form01(['enviaArquivo' => true]);
        $form->setDescricao('Funcionários');

        // ==================== PASTA 1 ====================
        $param = [];
		$param['campo'] = 'nomeagente';
		$param['etiqueta'] = 'Agente';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 60;
        $param['valor'] = $row['nomeagente'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'contato';
		$param['etiqueta'] = 'Contato';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['contato'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'email';
		$param['etiqueta'] = 'E-mail';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['email'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'email2';
		$param['etiqueta'] = 'E-mail 02';
		$param['largura'] = '3';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['email2'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'email3';
		$param['etiqueta'] = 'E-mail 03';
		$param['largura'] = '3';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['email3'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'telefone1';
		$param['etiqueta'] = 'Fone 01';
		$param['largura'] = '3';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 20;
        $param['valor'] = $row['telefone1'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'telefone2';
		$param['etiqueta'] = 'Fone 02';
		$param['largura'] = '3';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 20;
        $param['valor'] = $row['telefone2'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'site';
		$param['etiqueta'] = 'Site';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['site'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'obs';
		$param['etiqueta'] = 'Observações';
		$param['largura'] = '8';
		$param['tipo'] = 'TA';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 200;
        $param['valor'] = $row['obs'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        if(!empty($id)) {
            $param = [];
            $param['campo'] = 'ativo';
            $param['etiqueta'] = 'Ativo';
            $param['largura'] = '3';
            $param['tipo'] = 'A';
            // $param['obrigatorio'] = true;
            $param['opcoes'] = ['S' => 'Sim', 'N' => 'Não'];
            $param['tamanho'] = 1;
            $param['valor'] = $row['ativo'] ?? '';
            $param['pasta'] = 1;
            $param['readonly'] = $this->_visualizacao;
            $form->addCampo($param);
        }

        // ==================== PASTA 2 ENDEREÇO ====================
        $param = [];
		$param['campo'] = 'enderagente';
		$param['etiqueta'] = 'Endereço';
		$param['largura'] = '6';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 120;
        $param['valor'] = $row['enderagente'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cep';
		$param['etiqueta'] = 'CEP';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 10;
        $param['valor'] = $row['cep'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cidade';
		$param['etiqueta'] = 'Cidade';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 50;
        $param['valor'] = $row['cidade'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $sql = "SELECT codpais, nomepais FROM marpapais ORDER BY nomepais";
        $rows_paises = query2($sql);

        $paises = [];
        if(is_array($rows_paises) && count($rows_paises) > 0) {
            foreach($rows_paises as $pais) {
                $paises[$pais['codpais']] = mb_convert_encoding($pais['nomepais'], 'UTF-8', 'ASCII');
            }
        }

        $param = [];
		$param['campo'] = 'paisagente';
		$param['etiqueta'] = 'País';
		$param['largura'] = '3';
		$param['tipo'] = 'A';
		// $param['obrigatorio'] = true;
        $param['opcoes'] = $paises;
        $param['tamanho'] = 2;
        $param['valor'] = $row['paisagente'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        // ==================== PASTA 3 DADOS BANCÁRIOS ====================
        $param = [];
		$param['campo'] = 'dados_bancarios[banco]';
		$param['etiqueta'] = 'Banco';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['valor'] = $row['banco'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[endereco_banco]';
		$param['etiqueta'] = 'Endereço Banco';
		$param['largura'] = '5';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['endereco_banco'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[beneficiario]';
		$param['etiqueta'] = 'Beneficiario';
		$param['largura'] = '5';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 150;
        $param['valor'] = $row['beneficiario'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[agencia]';
		$param['etiqueta'] = 'Agência';
		$param['largura'] = '1';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 4;
        $param['valor'] = $row['agencia'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[conta]';
		$param['etiqueta'] = 'Conta';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 11;
        $param['valor'] = $row['conta'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[digito]';
		$param['etiqueta'] = 'Dígito';
		$param['largura'] = '1';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 1;
        $param['valor'] = $row['digito'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[swift]';
		$param['etiqueta'] = 'Swift';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 50;
        $param['valor'] = $row['swift'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[iban]';
		$param['etiqueta'] = 'IBAN';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 50;
        $param['valor'] = $row['iban'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dados_bancarios[obs_banco]';
		$param['etiqueta'] = 'Observações Banco';
		$param['largura'] = '6';
		$param['tipo'] = 'TA';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 200;
        $param['valor'] = $row['obs_banco'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $this->_visualizacao;
		$form->addCampo($param);

        // ==================== PASTA 5 HONORÁRIOS ====================
        if(!empty($id)) {
            $form->addConteudoPastas(5, $this->getComissoes($id));
        }


        if(!$this->_visualizacao) {
            $form->setEnvio(getLink() . "salvar&id=$id", 'formEditarComissoes');
        } else {
            $param = array(
                'texto' => 'Editar',
                'onclick' => "setLocation('" . getLink() . "incluir&id=$id&visualizar=0')",
                'cor' => 'success',
            );
            $form->addBotaoTitulo($param);

            $param = array(
                'texto' => 'Voltar',
                'onclick' => "setLocation('" . getLink() . "index')",
                'cor' => 'danger',
            );
            $form->addBotaoTitulo($param);
        }
        $form->setPastas([1 => 'Agente', 2 => 'Endereço', 3 => 'Dados Bancários', 4 => 'Arquivos', 5 => 'Honorários']);

        $ret .= $form;
        return $ret;
    }

    private function getComissoes($id) {
        $ret = '';

        $style = "
        <style>
            input[type=number]::-webkit-inner-spin-button { 
                -webkit-appearance: none;
            }
            input[type=number] { 
                -moz-appearance: textfield;
                appearance: textfield;
            }
        </style>
        ";

        $html = "
        <div class='form-row'>
            <input type='hidden' name='id_comissao' id='id_comissao'>

            <div class='col-md-4 mb-3'>
                <label for='comissoes_data'><b>Data</b></label><br>
                <input type='date' class='form-control' name='comissoes_data' id='comissoes_data'>
            </div>

            <div class='col-md-4 mb-3'>
                <label for='comissoes_valor'><b>Valor</b></label><br>
                <input type='text' class='form-control' name='comissoes_valor' id='comissoes_valor' onkeyup='formataNumeros(this)' onkeypress='return somenteNumeros(event)'>
            </div>

            <div class='col-md-4 mb-3'>
                <label for='comissoes_descricao'><b>Descrição</b></label><br>
                <textarea class='form-control' name='comissoes_descricao' id='comissoes_descricao'></textarea>
            </div>
        </div>

        <div class='form-row'>
            <div class='col-md-11 mb-3'>
            </div>

            <div class='col-md-1 mb-3'>
                <input type='button' class='btn btn-secondary' value='Salvar' onclick='salvarComissoes($id)'>
            </div>
        </div>";

        $param = [
            'titulo' => 'Registro de comissões',
            'conteudo' => $style . $html,
            'cor' => 'secondary'
        ];
        $ret .= addCard($param);


        // =============== INFORMAÇÕES JÁ REGISTRADAS NO BANCO =====================
        $ret .= "<div id='conteudo_comissao'></div>";

        addPortaljavaScript("callAjax($id);");

        return $ret;
    }

    private function getHtmlArquivos() {
        $ret = '';

        $desabilitado = $this->_visualizacao ? 'disabled' : '';

        $ret .= "
        <div class='row'>
            <div class='col'>
                <label for='digitalizacoes' class='form-label'>Digitalização:</label><br>
                <input type='file' name='digitalizacoes' id='digitalizacoes' $desabilitado class='form-control'>
            </div>

            <div class='col'>
                <label for='comprovantes' class='form-label'>Comprovante:</label><br>
                <input type='file' name='comprovantes' id='comprovantes' $desabilitado class='form-control'>
            </div>
        </div>";

        return $ret;
    }

    public function salvar($id = '') {
        $ret = 0;

        if(count($_POST) > 0 && count($_POST['dados_bancarios']) > 0) {
            // ================== INSERE O AGENTE ==================
            $temp = [];

            if(empty($id)) {
                $sql_id = "SELECT MAX(codagente) AS ultimo_id FROM marpaagente";
                $id_agente = query2($sql_id);
                $id_agente = $id_agente[0]['ultimo_id'] + 1;

                $temp['codagente'] = $id_agente;

                $tipo = 'INSERT';
                $where = '';
            } else {
                $id_agente = $id;

                $tipo = 'UPDATE';
                $where = "codagente = $id";
            }

            $temp['nomeagente']     = $_POST['nomeagente'];
            $temp['contato']        = $_POST['contato'];
            $temp['email']          = $_POST['email'];
            $temp['email2']         = $_POST['email2'];
            $temp['email3']         = $_POST['email3'];
            $temp['telefone1']      = $_POST['telefone1'];
            $temp['telefone2']      = $_POST['telefone1'];
            $temp['site']           = $_POST['site'];
            $temp['obs']            = str_replace(["'", '"'], '', $_POST['obs']);
            $temp['enderagente']    = $_POST['enderagente'];
            $temp['cep']            = $_POST['cep'];
            $temp['cidade']         = $_POST['cidade'];
            $temp['paisagente']     = $_POST['paisagente'];
            $temp['ativo']          = $_POST['ativo'] ?? 'S';

            $sql = montaSQL($temp, 'marpaagente', $tipo, $where);
            query2($sql);

            // ================== INSERE O BANCO DO AGENTE ==================
            $dados_bancarios = $_POST['dados_bancarios'];

            $sql_banco = "SELECT * FROM marpabancoagentes WHERE codagente = $id_agente";
            $banco = query2($sql_banco);

            $temp = [];
            if(is_array($banco) && count($banco) > 0) { // Edição
                $tipo = 'UPDATE';
                $where = "codagente = $id_agente";
            } else {
                $temp['codagente']      = $id_agente;

                $tipo = 'INSERT';
                $where = '';
            }

            $temp['banco']          = $dados_bancarios['banco'];
            $temp['endereco_banco'] = $dados_bancarios['endereco_banco'];
            $temp['beneficiario']   = $dados_bancarios['beneficiario'];
            $temp['agencia']        = str_replace('.', '', $dados_bancarios['agencia']);
            $temp['conta']          = str_replace('.', '', $dados_bancarios['conta']);
            $temp['digito']         = $dados_bancarios['digito'];
            $temp['swift']          = $dados_bancarios['swift'];
            $temp['iban']           = $dados_bancarios['iban'];
            $temp['obs_banco']      = $dados_bancarios['obs_banco'];

            $sql = montaSQL($temp, 'marpabancoagentes', $tipo, $where);
            query2($sql);

            $ret = $id_agente;
        }

        return $ret;
    }

    private function criaJs() {
        $js = "
        function callAjax(id){
            link = '" . getLink('') . "ajax.consultar&id='+id;
            $.get(link, function(retorno){
                document.getElementById('conteudo_comissao').innerHTML = retorno;
            });
        }
        
        function salvarComissoes(id) {
            var id_comissao = document.getElementById('id_comissao').value;
            var data = document.getElementById('comissoes_data').value;
            var valor = document.getElementById('comissoes_valor').value;
            var descricao = document.getElementById('comissoes_descricao').value;

            link = '" . getLink('') . "ajax.incluir_comissao&id='+id+'&id_comissao='+id_comissao+'&data='+data+'&valor='+valor+'&descricao='+descricao;
            $.get(link, function(retorno){
                document.getElementById('conteudo_comissao').innerHTML = retorno;
            });

            document.getElementById('comissoes_data').value = '';
            document.getElementById('comissoes_valor').value = '';
            document.getElementById('comissoes_descricao').value = '';
        }

        function editarComissao(id, data, valor, descricao) {
            document.getElementById('id_comissao').value = id;
            document.getElementById('comissoes_data').value = data;
            document.getElementById('comissoes_valor').value = valor;
            document.getElementById('comissoes_descricao').value = descricao;
        }

        function excluirComissao(id, id_comissao) {
            if(confirm('Tem certeza de que deseja excluir essa comissão?')) {
                link = '" . getLink('') . "ajax.excluir_comissao&id_comissao='+id_comissao+'&id='+id;
                $.get(link, function(retorno){
                    document.getElementById('conteudo_comissao').innerHTML = retorno;
                });
            }
        }
        
        function somenteNumeros(e) {
            var charCode = e.charCode ? e.charCode : e.keyCode;
            // charCode 8 = backspace   
            // charCode 9 = tab
            if (charCode != 8 && charCode != 9) {
                // charCode 48 equivale a 0   
                // charCode 57 equivale a 9
                if (charCode < 48 || charCode > 57) {
                    return false;
                }
            }
        }
        
        function formataNumeros(e) {
            var valor = e.value;
            valor = valor.replace(',', '');
            var quantidade = valor.length;

            valor = valor.toString(); // Transforma em String 
            var beforeDot = valor.substring(0, valor.length-2); // Captura do primeiro ao penúltimo caractere
            var afterDot = valor.substring(valor.length-2, valor.length); // Captura o penúltimo ao último caractere
            valor = beforeDot + ',' + afterDot; // retorna um NÚMERO com com o ponto inserido

            e.value = valor;
        }";

        addPortaljavaScript($js);
    }
 }