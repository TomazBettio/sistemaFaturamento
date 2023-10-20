<?php
/*
 * Data Criacao 14/06/2023
 * 
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Funcoes utilizadas gerar pagamentos
 *
 * Alteracoes:
 *
 */

 class rh_pagamentos {
    function __construct() {
        $this->adicionaJS();
    }

    public function incluir() {
        $ret = '';

        $sql = "SELECT * FROM funcionarios WHERE ativo = 'S'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            $html = '<form method="POST" action="'.getLink().'salvar" id="pagamentos">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="tipo" class="col-form-label col-form-label-sm"><strong>Tipo</strong></label>
                                <select title="" name="tipo"  id="tipo" class="form-control  form-select-sm selectpicker">
                                    <option value="mensal">Mensal</option>
                                    <option value="adiantamento">Adiantamento</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="mes_referente" class="col-form-label col-form-label-sm"><strong>Mês Referente*</strong></label>
                                <input type="text" name="mes_referente" id="mes_referente" class="form-control  form-control-sm"  size="20" maxlength="20" required>
                            </div>
                        </div>
                        <br><br>
                   
                    <table id="tabelaTWS3" class="table table-sm table-striped table-bordered"  width="640">
                        <thead>
                            <tr>
                                <th width="300">Nome</th>
                                <th width="80" class="online">Online</th>
                                <th width="80">PF</th>
                                <th width="80">Total</th>
                            </tr>
                        </thead>
                        <tbody>';
            $ids = [];
            foreach($rows as $row) {
                $salario = number_format($row['salario'], 2, ',', '.');
                $id = $row['id_funcionarios'];
                $ids[] = $id;
                $html .=    "<tr>
                                <td align='left' width='300' nowrap>{$row['nome']}</td>
                                <td align='left' width='80' nowrap class='online'>
                                    <input type='text' name='online$id' id='online$id' class='valor_online' onblur='atualizaTotal($id)'>
                                </td>
                                <td align='left' width='80' nowrap>
                                    <input type='text' name='pf$id' id='pf$id' onblur='atualizaTotal($id)'>
                                </td>
                                <td align='left' width='80' nowrap id='total$id'></td>
                            </tr>";
            }
            $ids = implode(';', $ids);
            $html .=    '</tbody>
                    </table>
                    <input type="text" name="ids" id="ids" value="'.$ids.'" hidden>
                </form>';

            $param = [];
            $param['URLcancelar'] = getLink().'index';
            $param['IDform'] = 'pagamentos';
            formbase01::formSendFooter($param);
            $ret = $html;
        }

        return $ret;
    }

    public function salvar() {
        if(!empty($_POST) && $_POST['mes_referente']) {
            $verificacao = explode('/', $_POST['mes_referente']);
            // echo count($verificacao) . ' - ' . strlen($_POST['mes_referente']);
            // return;
            if(count($verificacao) != 2 || strlen($_POST['mes_referente']) != 7) {
                redireciona(getLink() . "avisos&mensagem=Insira um mês referência válido (Ex.: 01/2023)&tipo=erro&redireciona=incluir");
            }

            $ids = explode(';', $_POST['ids']);

            if(is_array($ids) && count($ids) > 0) {
                foreach($ids as $id) {
                    $online = str_replace('.', '', $_POST['online'.$id]);
                    $online = str_replace(',', '.', $online);

                    $pf = str_replace('.', '', $_POST['pf'.$id]);
                    $pf = str_replace(',', '.', $pf);

                    $mes = explode('/', $_POST['mes_referente']);
                    $mes = $mes[1].'-'.$mes[0];

                    $temp = [];
                    $temp['id_funcionarios']    = $id;
                    $temp['online']             = $online;
                    $temp['pf']                 = $pf;
                    $temp['mes_referente']      = $mes;
                    $temp['tipo']               = $_POST['tipo'];

                    $sql = montaSQL($temp, 'rh_pagamentos_funcionarios');
                    query($sql);
                }

                $msgm = 'Pagamentos cadastrados';
                $tipo = '';
                $redireciona = 'index';
            } else {
                $msgm = 'Erro ao identificar os funcionários';
                $tipo = 'erro';
                $redireciona = 'incluir';
            }
        } else {
            $msgm = 'Erro ao cadastrar as informações, preencha todos os campos obrigatórios';
            $tipo = 'erro';
            $redireciona = 'incluir';
        }

        redireciona(getLink() . "avisos&mensagem=$msgm&tipo=$tipo&redireciona=$redireciona");
    }

    public function enviar($tipo, $filtro) {
        $de = substr($filtro['DATAINI'], 0, 4) . '-' . substr($filtro['DATAINI'], 4, 2);
        $ate = (!empty($filtro['DATAFIM'])) ? substr($filtro['DATAFIM'], 0, 4) . '-' . substr($filtro['DATAFIM'], 4, 2) : date('Y-m');

        // if($tipo == 'mensal') {
            $enviado = $this->enviarMensal($de, $ate, $tipo);
        // } else {
        //     $enviado = $this->enviaAdiantamentos($de, $ate, $email);
        // }

        if($enviado) {
            $msgm = "E-mail enviado com sucesso!";
            $tipo = '';
        } else {
            $de = explode('-', $de);
            $de = $de[1] . '/' . $de[0];

            $ate = explode('-', $ate);
            $ate = $ate[1] . '/' . $ate[0];

            $msgm = "Não foram encontrados registros nos períodos $de - $ate!";
            $tipo = 'erro';
        }

        redireciona(getLink() . "avisos&mensagem=$msgm&tipo=$tipo");
    }

    private function enviarMensal($de, $ate, $tipo) {
        $ret = false;
        $html = '';

        $sql = "SELECT * FROM rh_pagamentos_funcionarios
                WHERE tipo = '$tipo' AND mes_referente >= '$de' AND mes_referente <= '$ate'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            $nomes = [];

            $html = "<table>
                        <theader>
                            <tr>
                                <th>Funcionário</th>
                                <th>Online</th>
                                <th>PF</th>
                                <th>Total</th>
                                <th>Mês Referente</th>
                            </tr>
                        </theader>
                        <tbody>";
            foreach($rows as $row) {
                if(!isset($nomes[$row['id_funcionarios']])) {
                    $sql = "SELECT nome FROM funcionarios WHERE id_funcionarios = ".$row['id_funcionarios'];
                    $nome = query($sql);

                    $nomes[$row['id_funcionarios']] = $nome[0]['nome'];
                }

                $mes = explode('-', $row['mes_referente']);
                $mes = $mes[1] . '/' . $mes[0];

                $html .=    "<tr>
                                <td>{$nomes[$row['id_funcionarios']]}</td>
                                <td>R$ ".number_format($row['online'], 2, ',', '.')."</td>
                                <td>R$ ".number_format($row['pf'], 2, ',', '.')."</td>
                                <td>R$ ".number_format($row['online']+$row['pagamento']+$row['pf'], 2, ',', '.')."</td>
                                <td><center>$mes</center></td>
                            </tr>";
            }
            $html .=    "</tbody>
                    </table>";

            $style = "<style>
                table {
                    width: 800px;
                    border-collapse: collapse; /* CSS2 */
                    color: black;
                    margin-bottom: 50px;
                }
                td {
                    border: 1px solid black;
                }
                th {
                    border: 1px solid black;
                    background: black;
                    color: white;
                }
            </style>";

            $param = [];
            $param['destinatario'] = 'rh.michela@marpa.com.br';
            $param['mensagem'] = $style . $html;
            $param['assunto'] = "Relatório de Pagamentos ($tipo)";
            enviaEmail($param);

            $ret = true;
        }

        return $ret;
    }

    private function adicionaJS() {
        addPortaljavaScript("
            function atualizaTotal(id) {
                var online = document.getElementById('online'+id).value;
                if(online != '') {
                    online = online.replace(/\./g, '');
                    online = online.replace(',', '.');
                    online = parseFloat(online);
                } else {
                    online = 0;
                }

                var pf = document.getElementById('pf'+id).value;
                if(pf != '') {
                    pf = pf.replace(/\./g, '');
                    pf = pf.replace(',', '.');
                    pf = parseFloat(pf);
                } else {
                    pf = 0;
                }

                var total = online + pf;
                total = total.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                

                var obj_total = document.getElementById('total'+id);
                obj_total.innerHTML = total;
            }

            function defineTipo(obj) {
                console.log('valor obj: '+obj);

                var inputs = document.querySelectorAll('.online');

                var none = '';
                if(obj == 'adiantamento') {
                    none = 'none';

                    var inputs_online = document.querySelectorAll('.valor_online');
                    inputs_online.forEach(function(input_online) {
                        input_online.value = 0;

                        input_online.onblur();
                    });
                }
                inputs.forEach(function(input) {
                    input.style.display = none;
                });
            }
        ");
    }
}
