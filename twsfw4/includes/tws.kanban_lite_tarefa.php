<?php
class kanban_lite_tarefa{
    static function addJs(){
        $ret = "
function mudarStatusSubTarefa(sub, id){
    var status = 'A';
    if(document.getElementById('CbSubTarefa' + sub).checked){
        status = 'F';
    }
    var link = '" . getLinkAjax('mudarStatusSubTarefa') . "' + '&sub=' + sub + '&id=' + id + '&status=' + status;
    $.post(link, function(retorno){
        desenharTabelaSubTarefas(id);
    });
}

function desenharTabelaSubTarefas(id){
    var link = '" . getLinkAjax('desenharTabelaSubTarefas') . "' + '&id=' + id;
    $.get(link, function(retorno){
        document.getElementById('tabelaSubTarefas').innerHTML = retorno;
    });
}

function incluirSubTarefa(id){
    var link = '" . getLinkAjax('adicionarSubTarefa') . "' + '&id=' + id;
    var objeto = {};
    objeto['etiqueta'] = document.getElementById('etiquetaSubTarefa').value;
    document.getElementById('etiquetaSubTarefa').value = '';
    $.post(link, objeto, function(retorno){
        desenharTabelaSubTarefas(id);
    });
}

function excluirSubTarefa(id, sub){
    var link = '" . getLinkAjax('excluirSubTarefa') . "' + '&id=' + id + '&sub=' + sub;
    $.post(link, function(retorno){
        desenharTabelaSubTarefas(id);
    });
}

function selecionarDataVencimento(id, sub){
    abrirModalSecundario();
    var bt = '" . kanban_lite_tarefa::montarBtSalvarModalSecundario('dtVencimento') . "';
    bt = bt.replace('@@id', \"'\" + id + \"'\");
    bt = bt.replace('@@sub', \"'\" + sub + \"'\");
    document.getElementById('footer-modal-Secundario').innerHTML = bt;

    var link = '" . getLinkAjax('tituloModalSecundarioTarefa') . "' + '&sub=' + sub + '&campo=dtLimite' + '&id=' + id;
    $.get(link, function(retorno_titulo){
        document.getElementById('titulo-modal-Secundario').innerHTML = retorno_titulo;
        link = '" . getLinkAjax('conteudoModalSecundario') . "' + '&sub=' + sub + '&campo=dtLimite' + '&id=' + id;
        $.get(link, function(retorno_corpo){
            document.getElementById('corpo-modal-Secundario').innerHTML = retorno_corpo;
            arrumarMascaras();
        });
    });
}

function salvarDataLimite(id, sub){
    var data_limite = document.getElementById('modal_secundario_dtlimite').value;
    var objeto = {};
    objeto['dt_limite'] = data_limite;
    var link = '" . getLinkAjax('salvarDataLimite') . "' + '&sub=' + sub + '&id=' + id;
    $.post(link, objeto, function(retorno){
        $('#myModal-Secundario').modal('hide');
        desenharTabelaSubTarefas(id);
    });
}

function selecionarResponsavel(id, sub){
    abrirModalSecundario();
    var bt = '" . kanban_lite_tarefa::montarBtSalvarModalSecundario('responsavel') . "';
    bt = bt.replace('@@id', \"'\" + id + \"'\");
    bt = bt.replace('@@sub', \"'\" + sub + \"'\");
    document.getElementById('footer-modal-Secundario').innerHTML = bt;

    var link = '" . getLinkAjax('tituloModalSecundarioTarefa') . "' + '&sub=' + sub + '&campo=responsavel' + '&id=' + id;
    $.get(link, function(retorno_titulo){
        document.getElementById('titulo-modal-Secundario').innerHTML = retorno_titulo;
        link = '" . getLinkAjax('conteudoModalSecundario') . "' + '&sub=' + sub + '&campo=responsavel' + '&id=' + id;
        $.get(link, function(retorno_corpo){
            document.getElementById('corpo-modal-Secundario').innerHTML = retorno_corpo;
        });
    });
}

function salvarResponsavel(id, sub){
    var responsavel = document.getElementById('modal_secundario_responsavel').value;
    var objeto = {};
    objeto['responsavel'] = responsavel;
    var link = '" . getLinkAjax('salvarResponsavel') . "' + '&sub=' + sub + '&id=' + id;
    $.post(link, objeto, function(retorno){
        $('#myModal-Secundario').modal('hide');
        desenharTabelaSubTarefas(id);
    });
}
";
        addPortaljavaScript($ret, 'F');
    }
    
    static function montarBtSalvarModalSecundario($tipo){
        $ret = '';
        $param = array();
        switch ($tipo){
            case 'dtVencimento':
                $param['texto'] = 'Salvar Data Limite';
                $param['onclick'] = "salvarDataLimite(@@id, @@sub);";
                break;
            case 'responsavel':
                $param['texto'] = 'Salvar Responsável';
                $param['onclick'] = "salvarResponsavel(@@id, @@sub);";
                break;
            default:
                break;
        }
        if(count($param) >= 2){
            $ret = formbase01::formBotao($param);
        }
        return $ret;
    }
    
    static function montarFormularioCard($id){
        $ret = '';
        $campo_etiqueta = formbase01::formTexto(array('nome' => 'abcasasd', 'id' => 'etiquetaSubTarefa'));
        $param = array(
            'texto' => 'Adicionar SubTarefa',
            'onclick' => "incluirSubTarefa('$id')",
        );
        $bt_adicionar_tarefa = formbase01::formBotao($param);
        //$bt_adicionar_tarefa = '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModalSecundario">Launch Primary Modal</button>';
        $linha_adicionar_tarefa = addLinha(array('tamanhos' => array(8, 4), 'conteudos' => array($campo_etiqueta, $bt_adicionar_tarefa)));
        
        $tabelaTarefas = kanban_lite_tarefa::montarTabelaSubTarefas($id);  
        $tabelaTarefas = $linha_adicionar_tarefa . '<br>' . $tabelaTarefas;
        
        $ret = $tabelaTarefas;
        
        return $ret;
    }
    
    static function montarTabelaSubTarefas($id){
        $ret = '';
        $sql = "select * from kl_tarefas where card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $tabelaTarefas = array();
            foreach ($rows as $row){
                $checado = $row['status'] === 'F' ? 'checked' : '';
                $cb = '<input type="checkbox" ' . $checado . ' id="CbSubTarefa' . $row['id'] . '" name="vehicle1" value="Bike" onchange="mudarStatusSubTarefa(\'' . $row['id'] . '\', \'' . $id . '\')">';
                //$dt_limite = !empty($row['vencimento']) ? badge(['numeral' => datas::dataS2D($row['vencimento'], 2)]) : '';
                $dt_limite = kanban_lite_tarefa::montarBadgeDataLimite($row['vencimento'], $row['status']);
                $etiqueta = $row['etiqueta'];
                if($row['status'] === 'F'){
                    $etiqueta = "<s>$etiqueta</s>";
                }
                $bt = kanban_lite_tarefa::montarBotaoSubTarefa($id, $row['id']);
                $responsavel = !empty($row['responsavel']) ? badge(['numeral' => getUsuario('apelido', $row['responsavel'])]) : '';
                $tamanhos = array(1, 5, 2, 2, 2);
                $conteudos = array($cb, $etiqueta, $responsavel, $dt_limite, $bt);
                $tabelaTarefas[] = addLinha(array('tamanhos' => $tamanhos, 'conteudos' => $conteudos));
            }
            $ret = implode('<br>', $tabelaTarefas);
            
        }
        $ret =  '<div id="tabelaSubTarefas">' . $ret .  '</div>';
        return $ret;
    }
    
    static function montarBadgeDataLimite($data, $status){
        $ret = '';
        if(!empty($data)){
            $cor = 'primary';
            if($status == 'F'){
                $cor = 'success';
            }
            elseif($data < datas::data_hoje('AMD', '')){
                $cor = 'danger';
            }
            elseif($data == datas::data_hoje('AMD', '') || $data == datas::getDataDias(1)){
                $cor = 'warning';
            }
            $data_formatada = datas::dataS2D($data, 2);
            $ret = badge(['cor' => $cor, 'numeral' => $data_formatada]);
        }
        return $ret;
    }
    
    static function montarBotaoSubTarefa($id, $sub){
        $ret = '';
        $param = array(
            'titulo' => 'Ações',
            'tamanho' => '',
            'opcoes' => array(
                ['texto' => 'Excluir'    , 'onclick' => "excluirSubTarefa($id, $sub)"],
                ['texto' => 'Data Limite', 'onclick' => "selecionarDataVencimento($id, $sub)"],
                ['texto' => 'Responsável', 'onclick' => "selecionarResponsavel($id, $sub)"]
            ),
        );
        $ret = formbase01::formBotaoDropdown($param);
        return $ret;
    }
    
    static function getCampoSubTarefa($id, $campo){
        $ret = '';
        if(!empty($id) && !empty($campo)){
            $sql = "select $campo from kl_tarefas where id = $id";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0][$campo];
            }
        }
        return $ret;
    }
    
    static function mudarStatusSubTarefa($sub, $status){
        $status_possiveis = array('A', 'F');
        if(!empty($sub) && !empty($status) && in_array($status, $status_possiveis)){
            $sql = "update kl_tarefas set status = '$status' where id = $sub";
            query($sql);
        }
    }
    
    static function adicionarSubTarefa($id, $etiqueta){
        $sql = "insert into kl_tarefas values (null, '$id', '$etiqueta', 'A', null, null)";
        query($sql);
        
        $sql = "select * from kl_tarefas where status = 'A' and card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $textos = [];
            foreach ($rows as $row){
                $textos[] = $row['etiqueta'];
            }
            $resumo = implode('\n', $textos);
            $sql = "update kl_cards set resumo = '$resumo' where id = $id";
            query($sql);
        }
    }
    
    static function excluirSubTarefa($id, $sub){
        $sql = "delete from kl_tarefas where card = $id and id = $sub";
        query($sql);
        
        $sql = "select * from kl_tarefas where status = 'A' and card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $textos = [];
            foreach ($rows as $row){
                $textos[] = $row['etiqueta'];
            }
            $resumo = implode('\n', $textos);
            $sql = "update kl_cards set resumo = '$resumo' where id = $id";
            query($sql);
        }
    }
    
    static function montarListaResponsaveis(){
        $ret = array(['', '']);
        $sql = "select user, nome from sys001";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = [$row['user'], $row['nome']];
            }
        }
        return $ret;
    }
    
    static function ajax(){
        $ret = '';
        $op = getOperacao();
        $sub = getParam($_GET, 'sub', '');
        $ret = '';
        if($op === 'mudarStatusSubTarefa'){
            $status = $_GET['status'];
            kanban_lite_tarefa::mudarStatusSubTarefa($sub, $status);
        }
        if($op === 'desenharTabelaSubTarefas'){
            $id = $_GET['id'];
            $ret = kanban_lite_tarefa::montarTabelaSubTarefas($id);
        }
        if($op === 'adicionarSubTarefa'){
            $id = $_GET['id'];
            $etiqueta = $_POST['etiqueta'];
            kanban_lite_tarefa::adicionarSubTarefa($id, $etiqueta);
        }
        if($op === 'excluirSubTarefa'){
            $id = $_GET['id'];
            $sub = $_GET['sub'];
            kanban_lite_tarefa::excluirSubTarefa($id, $sub);
        }
        if($op === 'tituloModalSecundarioTarefa'){
            $campo = $_GET['campo'];
            if($campo == 'dtLimite'){
                $ret = 'Definir Data Limite';
            }
            if($campo == 'responsavel'){
                $ret = "Definir Responsável";
            }
        }
        if($op === 'conteudoModalSecundario'){
            $campo = $_GET['campo'];
            if($campo == 'dtLimite'){
                $form = new form01();
                $form->addCampo(array('campo' => 'dtLimite', 'id' => 'modal_secundario_dtlimite', 'tipo' => 'D', 'classeadd' => 'data', 'valor' => datas::dataS2D(kanban_lite_tarefa::getCampoSubTarefa($sub, 'vencimento'))));
                $ret .= $form;
            }
            if($campo == 'responsavel'){
                $form = new form01();
                $form->addCampo(array('campo' => 'responsavel', 'id' => 'modal_secundario_responsavel', 'tipo' => 'A', 'lista' => kanban_lite_tarefa::montarListaResponsaveis(),'valor' => datas::dataS2D(kanban_lite_tarefa::getCampoSubTarefa($sub, 'responsavel'))));
                $ret .= $form;
            }
        }
        if($op === 'salvarDataLimite'){
            $id = $_GET['id'] ?? '';
            $sub = $_GET['sub'] ?? '';
            $dt_limite = $_POST['dt_limite'] ?? '';
            if(!empty($dt_limite) && !empty($id) && !empty($sub)){
                $sql = "update kl_tarefas set vencimento = '" . datas::dataD2S($dt_limite) . "' where id = $sub and card = $id";
                query($sql);
            }
        }
        if($op === 'salvarResponsavel'){
            $id = $_GET['id'] ?? '';
            $sub = $_GET['sub'] ?? '';
            $responsavel = $_POST['responsavel'] ?? '';
            if(!empty($responsavel) && !empty($id) && !empty($sub)){
                $sql = "update kl_tarefas set responsavel = '$responsavel' where id = $sub and card = $id";
                query($sql);
            }
        }
        return $ret;
        
    }
}