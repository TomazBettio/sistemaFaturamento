<?php
/*
 * Data Criacao 04/05/2023
 * Autor: Verticais - Alexandre
 *
 * Descricao: Kanban no padrão AdminLTE
 *
 * Alterações:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class kanban_lite{
    private $_id_board;
    private $_colunas;
    //Permissão para editar
    private $_editarCards;
    //Permissão para acrescentar tarefa
    private $_criarCards;
    //Permissão para configurar as colunas
    private $_configurarColuna;
    //Permissão para configurar os cards
    private $_configurarCard;
    //Permissão para ver os detalhes do card
    private $_detalhesCard;
    //Permissão para mover cards
    private $_moverCards;
    //Indica se as tarefas devem ser impressas
    private $_imprimirTarefas;
    //Indica se o contador de tarefas deve ser imprimido
    private $_contador;
    //Indica a cor do contador de tarefas
    private $_contadorCor;
    //Indica se o totalizador do valores das tarefas deve ser imprimido
    private $_totalizador;
    //Indica a cor do totalizador
    private $_totalizadorCor;
    //Indica se o valor do card deve ser impresso
    private $_score;
    //Indica a cord do valor do card
    private $_scoreCor;
    //Indica os tipos de cards permitidos
    private $_tiposPermitidos;
    //
    private $_ordemCards;
    
    public function __construct($id_board = '',$param=[]){
        global $config;
        //Mostra botões das etapas
        putAppVar('bt_etapa', true);
        
        $config['content-wrapper'] = 'kanban';
        
        $this->_colunas = array();
        
        $this->_id_board = $id_board;
        
        $this->_editarCards =  $param['editarCards'] ?? kanban_lite::getPermissao($id_board, 'editarCards');
        $this->_configurarColuna =  $param['configurarColuna'] ?? kanban_lite::getPermissao($id_board, 'configurarColuna');
        $this->_configurarCard = $param['configurarCards'] ?? kanban_lite::getPermissao($id_board, 'configurarCards');
        $this->_criarCards =  $param['criarCards'] ?? kanban_lite::getPermissao($id_board, 'criarCards');
        $this->_detalhesCard = $param['detalhesCards'] ?? kanban_lite::getPermissao($id_board, 'detalhesCards');
        $this->_moverCards = $param['moverCards'] ?? kanban_lite::getPermissao($id_board, 'moverCards');
        
        $this->_imprimirTarefas = $param['imprimirTarefas'] ?? true;
        
        $this->_contador = $param['contador'] ?? (kanban_lite::getCampoBoard($id_board, 'contador', 'N') == 'S');
        $this->_contadorCor = $param['contadorCor'] ?? kanban_lite::getCampoBoard($id_board, 'contadorCor', 'success');
        
        $this->_totalizador = $param['totalizador'] ?? (kanban_lite::getCampoBoard($id_board, 'totalizador', 'N') == 'S');
        $this->_totalizadorCor = $param['totalizadorCor'] ?? kanban_lite::getCampoBoard($id_board, 'totalizadorCor', 'warning');
        
        $this->_score = $param['score'] ?? (kanban_lite::getCampoBoard($id_board, 'score', 'N') == 'S');
        $this->_scoreCor = $param['scoreCor'] ?? kanban_lite::getCampoBoard($id_board, 'scoreCor', 'warning');
        
        $this->_tiposPermitidos = kanban_lite::getTiposPermitidos($param['tipos'] ?? kanban_lite::getCampoBoard($id_board, 'tipos', []), $id_board);
        
        $this->_ordemCards = ($param['ordem'] ?? false) ? 'DESC' : 'ASC';
        
        $this->addJs();
        $this->addJsCards($id_board);
        
        addPortalJS('plugin', 'mask/jquery.mask.js', 'I');
        addPortalJS('plugin', 'maskmoney/jquery.maskMoney.min.js', 'I');
        addPortalCSS('plugin', 'toastr/toastr.min.css', 'I', 'toastr');
        addPortalJS('plugin', 'toastr/toastr.min.js','I', 'toastr');
        addPortalCSS('plugin', 'bootstrap-datepicker/css/bootstrap-datepicker3.min.css', 'I','datepicker');
        addPortalJS('plugin', 'bootstrap-datepicker/js/bootstrap-datepicker.min.js', 'F','datepicker');
        addPortalJS('plugin', 'bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js', 'F','datepicker-BR');
        
        addPortalJquery("
/*
$('#myModal').on('hide.bs.modal', function(e){
    location.reload();
})
*/
$('#myModal').on('shown.bs.modal', function(e){
    var collection = document.getElementsByClassName('focarAuto');
    if(collection.length > 0){     
        collection[0].focus();
    }
})
            
");
    }
    
    static function getTiposPermitidos($tipos, $id_board){
        $ret = [];
        if(is_string($tipos)){
            $tipos = explode(';', $tipos);
        }
        $sql = "SELECT * FROM kl_tipos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if(count($tipos) == 0 || in_array(strtoupper($row['codigo']), $tipos) || in_array(strtolower($row['codigo']), $tipos)){
                    $ret[] = [$row['codigo'], $row['etiqueta']];
                }
            }
        }
        return $ret;
    }
    
    static public function getPermissao($id, $codigo){
        $ret = false;
        if(!empty($id) && !empty($codigo)){
            $permissao_geral = kanban_lite::getPermissaoGeral($id, $codigo);
            if($permissao_geral == 'livre'){
                $ret = true;
            }
            if($permissao_geral == 'caso'){
                $ret = kanban_lite::getPermissaoEspecifica($id, $codigo);
            }
        }
        return $ret;
    }
    
    static public function getPermissaoGeral($id, $codigo){
        $ret = 'proibido';
        $sql = "select valor from kl_permissoes_geral where operacao = '$codigo' and board = $id";
        $rows = query($sql);
        if(is_array($rows)){
            if(count($rows) > 0){
                $ret = $rows[0]['valor'];
            }
            else{
                $ret = 'livre';
            }
        }
        return $ret;
    }
    
    static public function getPermissaoEspecifica($id, $codigo){
        $ret = false;
        $sql = "select * from kl_permissoes_especificas where board = $id and operacao = '$codigo' and usuario = '" . getUsuario() . "'";
        $rows = query($sql);
        $ret = (is_array($rows) && count($rows) > 0);
        return $ret;
    }
    
    static public function getCampoBoard($id, $campo, $valor_padrao){
        $ret = $valor_padrao;
        $sql = "select $campo from kl_boards where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0 && !empty($rows[0][$campo])){
            $ret = $rows[0][$campo];
        }
        return $ret;
    }
    
    private function getTarefasAuto($id_etapa){
        $ret = array();
        $sql = "select * from kl_cards where etapa = $id_etapa ORDER BY ID $this->_ordemCards";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'etiqueta' => $row['etiqueta'],
                    'id' => $row['id'],
                    'cor' => $row['cor'],
                    'score' => floatval($row['score']),
                );
                /*
                if(!empty(str_replace(['0', ',', '.'], '', $row['score']))){
                    $temp['etiqueta'] = $row['etiqueta'] . '  ' . badge(['numeral' => formataReais($row['score']), 'texto' => 'Valor do Card', 'concatenarNumeral' => false, 'cor' => 'warning']);
                }
                */
                $resumo = nl2br($row['resumo']);
                if(!empty($row['tags'])){
                    $array_badges = array();
                    $tags = $row['tags'];
                    $temp_explode = explode(';', $tags);
                    
                    if(is_array($temp_explode) && count($temp_explode) > 0){
                        foreach ($temp_explode as $te){
                            $array_badges[] =  badge(['numeral' => $te]);
                        }
                        $resumo = implode(' ', $array_badges) . '<br>' . $resumo;
                    }
                }
                $temp['conteudo'] = $resumo;
                
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function addColunasAuto(){
        $ret = array();
        if(!empty($this->_id_board)){
            $sql = "select * from kl_etapas where board = '$this->_id_board' order by ordem";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $temp = array(
                        'etiqueta' => $row['etiqueta'],
                        'tarefas' => $this->getTarefasAuto($row['id']),
                        'id' => $row['id'],
                        'cor' => $row['cor'],
                    );
                    $ret[] = $temp;
                }
            }
        }
        $this->_colunas = $ret;
    }
    
    public function __toString(){
        $ret = '';
        if(is_array($this->_colunas)){
            if(count($this->_colunas) === 0){
                $this->addColunasAuto();
            }
            if(count($this->_colunas) > 0){
                foreach ($this->_colunas as $coluna){
                    $ret .= $this->print_coluna($coluna);
                }
            }
        }
        $ret = $this->addHtmlModal() . $ret;
        return $ret;
    }
    
    private function addHtmlModal(){
        //modal-sm modal-lg modal-xl
        $ret = '
<div class="modal fade" id="myModal" data-backdrop="static">
    <div class="modal-dialog modal-xl" id="divTamanho">
        <div class="modal-content">
            
            <!-- Cabeçalho do modal -->
            <div class="modal-header">
                <h4 class="modal-title" id="titulo-modal">Título do Modal</h4>
                <button type="button" class="close" data-dismiss="modal" ' . 'onclick="location.reload();"' . '>&times;</button>
            </div>
                    
            <!-- Corpo do modal -->
            <div class="modal-body" id="corpo-modal">
                <p>Conteúdo do Modal aqui...</p>
            </div>
                    
            <!-- Rodapé do modal -->
            <div class="modal-footer" id="footer-modal">
            <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button> -->
            </div>
                    
        </div>
    </div>
</div>
                    
<div class="modal fade" data-backdrop="static" id="myModal-Secundario">
    <div class="modal-dialog modal-sm modal-dialog-centered" id="divTamanho-Secundario">
        <div class="modal-content">
                    
            <!-- Cabeçalho do modal -->
            <div class="modal-header">
                <h4 class="modal-title" id="titulo-modal-Secundario">Título do Modal</h4>
                <button type="button" class="close" data-dismiss="modal"' . /*'onclick="location.reload();" .*/ '>&times;</button>
            </div>
                    
            <!-- Corpo do modal -->
            <div class="modal-body" id="corpo-modal-Secundario">
                <p>Conteúdo do Modal aqui...</p>
            </div>
                    
            <!-- Rodapé do modal -->
            <div class="modal-footer" id="footer-modal-Secundario">
            <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button> -->
            </div>
                    
        </div>
    </div>
</div>
';
        return $ret;
    }
    
    public function addColuna($param){
        if(!empty($param['etiqueta'])){
            $this->_colunas[] = $param;
        }
    }
    
    private function print_coluna($param){
        global $nl;
        $ret = '';
        
        $score_total = 0;
        if(isset($param['tarefas'])){
            foreach ($param['tarefas'] as $tarefa){
                $score_total += $tarefa['score'] ?? 0;
            }
        }
        
        $etiqueta = $param['etiqueta'];
        if($this->_contador){
            $etiqueta .= ' ' . badge(['numeral' => count($param['tarefas']), 'texto' => 'Número de tarefas na coluna', 'concatenarNumeral' => false, 'cor' => $this->_contadorCor]);
        }
        
        $ret .= '<div class="card card-row card-' . ((isset($param['cor']) && !empty($param['cor'])) ? $param['cor'] : 'secondary') . '" id="card' . $param['id'] . 'capsula">'.$nl;
        $ret .= '	<div class="card-header" id="card' .  $param['id'] . 'titulo">'.$nl;
        $ret .= '		<h3 class="card-title">'.$etiqueta.'</h3>'.$nl;
        $ret .= '       <div class="card-tools">'.$nl;
        
        if($this->_totalizador){
            $ret .= badge(['numeral' => formataReais($score_total), 'texto' => 'Somatório dos valores dos cards', 'concatenarNumeral' => false, 'cor' => $this->_totalizadorCor]);
        }
        if($this->_configurarColuna === true){
            $ret .= '       <a class="btn btn-tool" data-toggle="modal" data-target="#myModal" onclick="editarEtapa(\'' . $param['id'] . '\')">'.$nl;
            $ret .= '           <i class="fa fa-cog"></i>'.$nl;
            $ret .= '       </a>'.$nl;
        }
        $ret .= '           <a class="btn btn-tool" onclick="esconder(' . "'{$param['id']}'" . ')">'.$nl;
        $ret .= '               <i class="fa fa-minus"></i>'.$nl;
        $ret .= '           </a>'.$nl;
        if($this->_criarCards){
            $ret .= '       <a href="#" class="btn btn-tool" data-toggle="modal" data-target="#myModal" onclick="formularioIncluir(' . "'{$param['id']}'" . ')">'.$nl;
            $ret .= '           <i class="fa fa-plus"></i>'.$nl;
            $ret .= '       </a>'.$nl;
        }
        $ret .= '       </div>'.$nl;
        $ret .= '	</div>'.$nl;
        $ret .= '	<div class="card-body" style="overflow-y:auto; height:400px;" id="card' . $param['id'] . 'corpo">'.$nl;
        if($this->_imprimirTarefas){
            if(!empty($param['conteudo'] ?? '')){
                $ret .= $param['conteudo'];
            }
            if(!empty($param['tarefas'] ?? '')){
                $ret .= $this->print_tarefas($param['tarefas']);
            }
        }
        $ret .= '	</div>'.$nl;
        $ret .= '</div>'.$nl;
        
        return $ret;
    }
    
    private function print_tarefas($tarefas = array()){
        $ret = '';
        foreach ($tarefas as $tarefa){
            $ret .= $this->print_terefa_unitaria($tarefa);
        }
        return $ret;
    }
    
    private function print_terefa_unitaria($tarefa){
        $ret = '';
        if(isset($tarefa['etiqueta'])){
            $ret = '
<div class="card card-' . (isset($tarefa['cor']) && !empty($tarefa['cor']) ? $tarefa['cor'] : 'primary') .  ' card-outline">
    <div class="card-header">
        <h5 class="card-title">' . $tarefa['etiqueta'] . '</h5>
        <div class="card-tools">';
            if($this->_score){
                $ret.= badge(['numeral' => formataReais($tarefa['score']), 'texto' => 'Valor do Card', 'concatenarNumeral' => false, 'cor' => $this->_scoreCor]);
            }
            if($this->_configurarCard === true){
                $ret.='<a href="#" class="btn btn-tool" data-toggle="modal" data-target="#myModal" onclick="' . "carregarModal('{$tarefa['id']}', 'configurar');" . '">
                    <i class="fa fa-cog"></i>
                </a>';
            }
            if($this->_detalhesCard){
                $ret.='<a href="#" class="btn btn-tool" data-toggle="modal" data-target="#myModal" onclick="' . "carregarModal('{$tarefa['id']}', 'visualizar');" . '">
                    <i class="fa fa-search"></i>
                </a>';
            }
            if($this->_editarCards && ($tarefa['editar'] ?? true)){
                $ret.= '
                <a href="#" class="btn btn-tool" data-toggle="modal" data-target="#myModal" onclick="' . "carregarModal('{$tarefa['id']}', 'editar');" . '">
                <i class="fa fa-pencil"></i>
                </a>';
            }
            $ret.= ' </div>
    </div>';
            if(!empty($tarefa['conteudo']) ?? ''){
                $ret .= '
<div class="card-body">
        ' . $tarefa['conteudo'] . '
    </div>
';
            }
            $ret .= '</div>';
        }
        return $ret;
    }
    
    private function addJs(){
        $ret = "
function carregarModal(id, modo){
    getTamanhoModal(id, 'divTamanho');
    document.getElementById('footer-modal').innerHTML = '';
    var link = '" . getLinkAjax('titulo') . "' + '&id=' + id;
    $.get(link, function(retorno){
            document.getElementById('titulo-modal').innerHTML = retorno;
    });
    if(modo == 'editar'){
        var link = '" . getLinkAjax('editar') . "' + '&id=' + id;
        $.get(link, function(retorno){
            document.getElementById('corpo-modal').innerHTML = retorno;
            arrumarMascaras();

            link = '" . getLinkAjax('montarBtSalvarEditar') . "' + '&id=' + id;
            $.get(link, function(retorno){
                var bt = retorno.replace('@@id', \"'\" + id + \"'\");
                document.getElementById('footer-modal').innerHTML = bt;
            });
        });
    }
    else if(modo == 'visualizar'){
        var link = '" . getLinkAjax('visualizar') . "' + '&id=' + id;
        $.get(link, function(retorno){
                document.getElementById('corpo-modal').innerHTML = retorno;
        });
    }
    else if(modo == 'configurar'){
        alterarTamanhoModal('pequeno', 'divTamanho');
        var link = '" . getLinkAjax('configurar') . "' + '&id=' + id;
        $.get(link, function(retorno){
                document.getElementById('corpo-modal').innerHTML = retorno;
        });
            
        var bt = '" . kanban_lite::criarBotaoSalvarInclusao('configurar') . "';
        bt = bt.replace('@@id', \"'\" + id + \"'\");
        document.getElementById('footer-modal').innerHTML = bt;
    }
    else{
        document.getElementById('corpo-modal').innerHTML = 'Sem Dados';
    }
}
            
function abrirModalSecundario(){
    $('#myModal-Secundario').modal();
}
            
function getTamanhoModal(id, id_alvo){
    var ret = 'grande';
    var link = '" . getLinkAjax('getTamanhoModal') . "' + '&id=' + id;
    $.get(link, function(retorno){
            if(document.getElementById(id_alvo)){
                alterarTamanhoModal(retorno, id_alvo);
            }
            else{
                ret = retorno;
                return ret;
            }
    });
}
        
function alterarTamanhoModal(tamanho, id_modal){
    	    //modal-sm modal-lg modal-xl
    var modal = document.getElementById(id_modal);
        
    modal.classList.remove('modal-sm');
    modal.classList.remove('modal-lg');
    modal.classList.remove('modal-xl');
        
    if(tamanho == 'pequeno'){
        modal.classList.add('modal-sm');
    }
    if(tamanho == 'medio'){
        modal.classList.add('modal-lg');
    }
    if(tamanho == 'grande'){
        modal.classList.add('modal-xl');
    }
}
        
function editarEtapa(id){
    alterarTamanhoModal('pequeno', 'divTamanho');
    //document.getElementById('titulo-modal').innerHTML = 'Editar Etapa';
    var link = '" . getLinkAjax('formularioEtapa') . "' + '&etapa=' + id;
    $.get(link, function(retorno){
        retorno = retorno.replace('@@id', \"'\" + id + \"'\");
        document.getElementById('corpo-modal').innerHTML = retorno;
        arrumarMascaras();
    });
    link = '" . getLinkAjax('cabEtapa') . "' + '&etapa=' + id;
    $.get(link, function(retorno){
        retorno = retorno.replace('@@id', \"'\" + id + \"'\");
        retorno = retorno.replace('@@id', \"'\" + id + \"'\");
        document.getElementById('titulo-modal').innerHTML = retorno;
    });
    var bt = '" . kanban_lite::criarBotaoSalvarInclusao('editarEtapa') . "';
    bt = bt.replace('@@id', \"'\" + id + \"'\");
    document.getElementById('footer-modal').innerHTML = bt;
}
        
function trocarOrdemEtapa(id, sentido){
    var link = '" . getLinkAjax('trocarOrdemEtapa') . "' + '&etapa=' + id + '&sentido=' + sentido;
    $.get(link, function(retorno){
        link = '" . getLinkAjax('cabEtapa') . "' + '&etapa=' + id;
        $.get(link, function(retorno){
            if(sentido == 'D'){
                toastr.success('Etapa movida para a direita');
            }
            if(sentido == 'E'){
                toastr.success('Etapa movida para a esquerda');
            }
            retorno = retorno.replace('@@id', \"'\" + id + \"'\");
            retorno = retorno.replace('@@id', \"'\" + id + \"'\");
            document.getElementById('titulo-modal').innerHTML = retorno;
        });
    });
}
            
function salvarEtapaEditada(id){
    var objeto = {};
            
    objeto['etiqueta'] = document.getElementById('campoEtiqueta').value;
    objeto['cor'] = document.getElementById('campoCor').value;
    //objeto['ordem'] = document.getElementById('campoOrdem').value;
            
    var link = '" . getLinkAjax('salvarEtapaEditada') ."' + '&etapa=' + id;
    $.post(link, objeto, function(retorno){
        location.reload();
    })
}
        
function adicionarComentario(id){
    var comentario = {};
    comentario['texto'] = document.getElementById('novoComentario').value;
    document.getElementById('novoComentario').value = '';
    var link = '" . getLinkAjax('addComentario') ."' + '&id=' + id;
    $.post(link, comentario, function(retorno){
        var linha_tempo = document.getElementById('linhaTempo');
        linha_tempo.innerHTML = retorno;
    })
}
        
function salvarCardEditado(id){
    var elementos = document.getElementsByClassName('campoEditarCard');
    var link = '" . getLinkAjax('salvaEditar') . "' + '&id=' + id;
    var objeto = {};
    for(let elemento of elementos){
        objeto[elemento.id] = elemento.value;
    }
    $.post(link, objeto, function(retorno){
        location.reload();
    });
}
        
function moverEtapa(bt, id){
    var collection = document.getElementsByClassName('bt_etapa');
    var modificar = true;
    for (let i = 0; i < collection.length; i++) {
        if(modificar){
            //botoes antes do apertado
            collection[i].classList.remove('btn-primary');
            collection[i].classList.add('btn-info');
        }
        else{
            //botoes dps do apertado
            collection[i].classList.remove('btn-info');
            collection[i].classList.add('btn-primary');
        }
        if(collection[i].id == bt.id){
            modificar = false;
        }
        //collection[i].disabled = false;
        //bt.disabled = true;
    }
    var link = '" . getLinkAjax('mover') . "' + '&id=' + id + '&coluna=' + bt.id;
    $.get(link, function(retorno){
    });
}
        
function formularioIncluir(id){
    alterarTamanhoModal('pequeno', 'divTamanho');
    document.getElementById('titulo-modal').innerHTML = 'Incluir Tarefa';
    document.getElementById('corpo-modal').innerHTML = '" . kanban_lite::formularioNovaTarefa('', $this->_tiposPermitidos) . "';
    var bt = '" . kanban_lite::criarBotaoSalvarInclusao('incluir') . "';
    bt = bt.replace('@@id', \"'\" + id + \"'\");
    document.getElementById('footer-modal').innerHTML = bt;
}
        
function salvarCard(id){
    var etiqueta = document.getElementById('campoEtiqueta').value;
    var tipo = document.getElementById('campoTipo').value;
    var resumo = document.getElementById('campoResumo').value;
    var cor = document.getElementById('campoCor').value;
    var tags = document.getElementById('campoTags').value;
        
    var objeto = {};
    objeto['etiqueta'] = etiqueta;
    objeto['tipo'] = tipo;
    objeto['resumo'] = resumo;
    objeto['etapa'] = id;
    objeto['cor'] = cor;
    objeto['tags'] = tags;
    //link = '" . getLinkAjax('salvarInclusao') . "' + '&etiqueta=' + etiqueta + '&tipo=' + tipo + '&resumo=' + resumo + '&etapa=' + id;
    link = '" . getLinkAjax('salvarInclusao') . "';
    $.post(link, objeto, function(retorno){
        location.reload();
    });
}
        
function salvarCardConfigurado(id){
    var etiqueta = document.getElementById('campoEtiqueta').value;
    //var tipo = document.getElementById('campoTipo').value;
    var resumo = document.getElementById('campoResumo').value;
    var cor = document.getElementById('campoCor').value;
    var tags = document.getElementById('campoTags').value;
        
        
    var objeto = {};
    objeto['etiqueta'] = etiqueta;
    //objeto['tipo'] = tipo;
    objeto['resumo'] = resumo;
    objeto['cor'] = cor;
    objeto['tags'] = tags;
        
    link = '" . getLinkAjax('salvarConfigurar') . "' + '&id=' + id;
    $.post(link, objeto, function(retorno){
        location.reload();
    });
}
        
function esconder(id){
    var id_completo = 'card' + id + 'corpo';
    var id_capsula = 'card' + id + 'capsula';
    var id_header = 'card' + id + 'titulo';
    var x = document.getElementById(id_completo);
    var y = document.getElementById(id_capsula);
    var z = document.getElementById(id_header);
    if (x.style.display === 'none') {
        x.style.display = 'block';
        y.style.height = z.offsetHeight + 400;
        //y.style.transform = 'rotate(0deg)';
    }
    else {
        x.style.display = 'none';
        y.style.height = z.offsetHeight;
        //y.style.transform = 'rotate(90deg)';
    }
}
        
function enviarArquivoAnexo(id){
    var arquivo = document.getElementById('id_campo_anexo').files[0];
    var formData = new FormData();
    formData.append('arquivo', arquivo);
    var xhr = new XMLHttpRequest();
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Requisição concluída com sucesso
            //alert(xhr.response);
            document.getElementById('idBlocoAnexo').innerHTML = xhr.response;
            //console.log(xhr.response);

            var link = '" . getLinkAjax('addComentario') ."' + '&id=' + id;
                $.get(link, function(retorno){
                var linha_tempo = document.getElementById('linhaTempo');
                linha_tempo.innerHTML = retorno;
            })
        }
    else {
            // Ocorreu um erro durante a requisição
            console.error('Ocorreu um erro durante a requisição.');
        }
    };
    var link_servidor = '" . getLinkAjax('salvarAnexo') . "' + '&id=' + id;
    xhr.open('POST', link_servidor, true);
    xhr.send(formData);
}
        
function arrumarMascaras(){
    $('.cnpj').mask('00.000.000/0000-00');
    $('.valor').mask('###.###.###.###.##0,00', {reverse: true});
    $('.numero').mask('##################');
    $('.data').datepicker({
											    todayBtn: 'linked',
											    language: 'pt-BR',
											    todayHighlight: true
											});
}
";
        addPortaljavaScript($ret, 'F');
    }
    
    private function addJsCards($id_board){
        $sql = "select distinct tipo from kl_cards where etapa in (select id from kl_etapas where board = $id_board)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $tipo = $row['tipo'];
                $classe = 'kanban_lite_' . strtolower($tipo);
                if(method_exists($classe, 'addJs')){
                    $classe::addJs();
                }
            }
        }
    }
    
    private static function criarBotaoSalvarInclusao($tipo = '', $id = ''){
        $ret = '';
        $param = array(
            'texto' => 'Salvar',
        );
        
        switch ($tipo){
            case 'incluir':
                $param['onclick'] = "salvarCard(@@id)";
                break;
            case 'editar':
                $param['onclick'] = "salvarCardEditado(@@id)";
                break;
            case 'configurar':
                $param['onclick'] = "salvarCardConfigurado(@@id)";
                break;
            case 'editarEtapa':
                $param['onclick'] = "salvarEtapaEditada(@@id)";
                break;
            default:
                break;
        }
        $ret = formbase01::formBotao($param);
        if(!empty($id)){
            $sql = "select bt_salvar from kl_tipos where codigo in (select tipo from kl_cards where id = $id)";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                if($rows[0]['bt_salvar'] == 'N'){
                    $ret = '';
                }
            }
        }
        return $ret;
    }
    
    static function criarBotoesEtapas($id_card){
        $ret = '';
        if(getAppVar('bt_etapa') === NULL || getAppVar('bt_etapa') !== FALSE ){
            $sql = "select * from (select 'false' as desable, kl_etapas.*  from kl_etapas where board in (select board from kl_etapas where id in (select etapa from kl_cards where id = $id_card)) and id not in (select etapa from kl_cards where id = $id_card) union select 'true' as desable, kl_etapas.* from kl_etapas where board in (select board from kl_etapas where id in (select etapa from kl_cards where id = $id_card)) and id in (select etapa from kl_cards where id = $id_card)) tmp1 order by ordem";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $botoes = array();
                if(count($rows) == 1){
                    //$botao1 = formbase01::formBotao(array('texto' => 'texto botao', 'style' => 'clip-path: polygon(0 0,95% 0,100% 50%,95% 100%,0 100%);'));
                    //$botao2 = formbase01::formBotao(array('texto' => 'texto botao', 'style' => 'clip-path: polygon(95% 0,100% 50%,95% 100%,0 100%,5% 50%,0 0);'));
                    //$botao3 = formbase01::formBotao(array('texto' => 'texto botao', 'style' => 'clip-path: polygon(100% 0,100% 50%,100% 100%,0 100%,5% 50%,0 0);'));
                    foreach ($rows as $row){
                        $temp = array(
                            'texto' => $row['etiqueta'],
                            'classe' => 'bt_etapa',
                            'onclick' => "moverEtapa(this, '$id_card')",
                            'ativo' => false,
                            'id' => $row['id'],
                            'cor' => 'info',
                        );
                        $temp = formbase01::formBotao($temp);
                        $botoes[] = $temp;
                    }
                }
                else{
                   
                    $etapa_atual_card =  kanban_lite::getCampoEtapa(kanban_lite::getCampoCard($id_card, 'etapa'), 'ordem');
                    $etapa_atual_card = intval($etapa_atual_card);
                    
                    $primeiro_bt_dado = array_shift($rows);
                    $ultimo_bt_dado = array_pop($rows);
                    
                    $primeiro_bt = array(
                        'texto' => $primeiro_bt_dado['etiqueta'] . '&nbsp;',
                        'classe' => 'bt_etapa',
                        'onclick' => "moverEtapa(this, '$id_card')",
                        //'ativo' => ($primeiro_bt_dado['desable'] == 'false'),
                        'id' => $primeiro_bt_dado['id'],
                        'style' => 'clip-path: polygon(0 0,95% 0,100% 50%,95% 100%,0 100%);',
                        'cor' => intval($primeiro_bt_dado['ordem']) <= $etapa_atual_card ? 'info' : 'primary',
                    );
                    
                    $primeiro_bt = formbase01::formBotao($primeiro_bt);
                    $botoes[] = $primeiro_bt;
                    
                    foreach ($rows as $row){
                        $temp = array(
                            'texto' => '&nbsp;&nbsp;' . $row['etiqueta'] . '&nbsp;',
                            'classe' => 'bt_etapa',
                            'onclick' => "moverEtapa(this, '$id_card')",
                            //'ativo' => ($row['desable'] == 'false'),
                            'id' => $row['id'],
                            'style' => 'clip-path: polygon(95% 0,100% 50%,95% 100%,0 100%,5% 50%,0 0);',
                            'cor' => intval($row['ordem']) <= $etapa_atual_card ? 'info' : 'primary',
                        );
                        $temp = formbase01::formBotao($temp);
                        $botoes[] = $temp;
                        
                    }
                    
                    $ultimo_bt = array(
                        'texto' => '&nbsp;' . $ultimo_bt_dado['etiqueta'],
                        'classe' => 'bt_etapa',
                        'onclick' => "moverEtapa(this, '$id_card')",
                        //'ativo' => ($ultimo_bt_dado['desable'] == 'false'),
                        'id' => $ultimo_bt_dado['id'],
                        'style' => 'clip-path: polygon(100% 0,100% 50%,100% 100%,0 100%,5% 50%,0 0);',
                        'cor' => intval($ultimo_bt_dado['ordem']) <= $etapa_atual_card ? 'info' : 'primary',
                    );
                    $ultimo_bt = formbase01::formBotao($ultimo_bt);
                    $botoes[] = $ultimo_bt;
                }
                
                $ret = implode('', $botoes);
            }
        }
        return $ret;
    }
    
    static function criarTituloCard($id_card){
        $ret = '';
        $sql = "select etiqueta from kl_cards where id = $id_card";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $etiqueta = $rows[0]['etiqueta'];
            $ret = $etiqueta;
            if(kanban_lite::getPermissao(kanban_lite::getBoardCard($id_card), 'moverCards')){
                $ret .= '<br>' . kanban_lite::criarBotoesEtapas($id_card);
            }
            
        }
        else{
            $ret = 'Sem Título';
        }
        return $ret;
    }
    
    static function getBoardCard($id_card){
        $ret = '';
        $sql = "select board from kl_etapas where id in (select etapa from kl_cards where id = $id_card)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['board'];
        }
        return $ret;
    }
    
    static function formularioNovaTarefa($id = '', $tipo_permitidos = []){
        $ret = '';
        $form = new form01();
        //focarAuto
        $form->addCampo(array('id' => 'campoEtiqueta', 'campo' => 'formCard[colunas][etiqueta]'		, 'etiqueta' => 'Titulo'			, 'linha' => 1, 'largura' =>12, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => kanban_lite::getCampoCard($id, 'etiqueta')		, 'lista' => '', 'funcao_lista' => ""			 ,'classeadd' => 'focarAuto'                                    , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        if(empty($id)){
            $form->addCampo(array('id' => 'campoTipo'    , 'campo' => 'formCard[tipo]'		, 'etiqueta' => 'Tipo'				, 'linha' => 2, 'largura' =>12, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => kanban_lite::getCampoCard($id, 'tipo')		, 'lista' =>  $tipo_permitidos, 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 60));
        }
        $form->addCampo(array('id' => 'campoCor'    , 'campo' => 'formCard[cor]'		, 'etiqueta' => 'Cor'				, 'linha' => 2, 'largura' =>12, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => kanban_lite::getCampoCard($id, 'cor')		, 'lista' => kanban_lite::getListaCores(), 'funcao_lista' => ''	 , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 60));
        $form->addCampo(array('id' => 'campoResumo'  , 'campo' => 'formCard[resumo]'		, 'etiqueta' => 'Resumo'			, 'linha' => 3, 'largura' =>12, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => kanban_lite::getCampoCard($id, 'resumo')		, 'lista' => '', 'funcao_lista' => ""			                                     , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => 'campoTags', 'campo' => 'formCard[tags]', 'etiqueta' => 'Tags', 'linha' => 4, 'largura' => 12, 'tipo' => 'T', 'tamanho' => 60, 'valor' => kanban_lite::getCampoCard($id, 'tags')));
        $ret .= $form;
        global $nl;
        $ret = str_replace($nl, '', $ret);
        return $ret;
    }
    
    static function montarListaTipos(){
        $ret = [];
        $sql = "select * from kl_tipos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = [$row['codigo'], $row['etiqueta']];
            }
        }
        return $ret;
    }
    
    static function criarCard($etiqueta, $etapa, $resumo, $tipo, $cor, $tags){
        $sql = "insert into kl_cards values (null, '$etiqueta', '$etapa', '$resumo', '$cor', '$tipo', '$tags', 0, CURRENT_TIMESTAMP())";
        query($sql);
    }
    
    static function montarCondeudoCard($id){
        $ret = '';
        $tipo = kanban_lite::getCampoCard($id, 'tipo');
        $classe = 'kanban_lite_' . strtolower($tipo);
        if(method_exists($classe, 'montarCondeudoCard')){
            $ret = $classe::montarCondeudoCard($id);
        }
        /*
         switch ($tipo){
         case 'TEXTO':
         $ret = kanban_lite::getTexto($id);
         break;
         case 'TAREFA':
         $ret = 'o cadr é do tipo tarefa';
         break;
         case 'MARPA':
         $sql = "select descricao from cards_marpa where card = $id";
         $rows = query($sql);
         if(is_array($rows) && count($rows) > 0){
         $ret = nl2br($rows[0]['descricao']);
         //$ret = str_replace('\n', '<br>', $rows[0]['descricao']);
         }
         break;
         default:
         break;
         }
         */
        return $ret;
    }
    
    static function montarFormularioCard($id){
        $ret = '';
        $tipo = kanban_lite::getCampoCard($id, 'tipo');
        $classe = 'kanban_lite_' . strtolower($tipo);
        if(method_exists($classe, 'montarFormularioCard')){
            $ret = $classe::montarFormularioCard($id);
        }
        /*
         switch ($tipo){
         case 'TEXTO':
         $form = new form01();
         $texto = kanban_lite::getTexto($id);
         $form->addCampo(array('id' => 'campoTexto', 'campo' => 'formCard[texto]'		, 'etiqueta' => 'Conteudo'			, 'linha' => 1, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $texto		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
         $ret .= $form;
         break;
         case 'TAREFA':
         $ret = 'o cadr é do tipo tarefa formulario';
         break;
         case 'MARPA':
         $ret = kanban_lite_marpa::montarFormularioCard($id);
         break;
         default:
         break;
         }
         */
        return $ret;
    }
    
    static function montarBlocoNovoComentario($id){
        $ret = '';
        $form = new form01();
        $form->addCampo(array('id' => 'novoComentario', 'campo' => 'formCard[novoComentario]'		, 'etiqueta' => ''			, 'linha' => 1, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'linhasTA' => 6));
        $ret .= $form . '<br>';
        $ret .= formbase01::formBotao(array('texto' => 'Adicionar Comentário', 'onclick' => "adicionarComentario($id)"));
        return $ret;
    }
    
    static function getTexto($id){
        $ret = '';
        $sql = "select texto from kl_textos where card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['texto'];
        }
        return $ret;
    }
    
    static function getCampoCard($id, $campo){
        $ret = '';
        if(!empty($id) && !empty($campo)){
            $sql = "select $campo from kl_cards where id = $id";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0][$campo] ?? '';
            }
        }
        return $ret;
    }
    
    static function salvarEditar($id){
        $tipo = kanban_lite::getCampoCard($id, 'tipo');
        $classe = 'kanban_lite_' . strtolower($tipo);
        if(method_exists($classe, 'salvarEditar')){
            $classe::salvarEditar($id);
        }
    }
    
    static function gerarHistoricoComentarios($id){
        $ret = '';
        $sql = "select * from kl_comentarios where card = $id order by dt_criado desc";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ultima_data = '';
            $param = array('pai' => array());
            $temp  = array();
            foreach ($rows as $row){
                if($ultima_data != datas::dataMS2D($row['dt_criado'])){
                    //cria novo bloco
                    if(count($temp) > 0){
                        $param['pai'][] = $temp;
                    }
                    $ultima_data = datas::dataMS2D($row['dt_criado']);
                    $temp = array();
                    //$temp['titulo'] =
                    $temp['titulo'] =  datas::dataMS2D($row['dt_criado']);
                    $temp['cor'] = "bg-green";
                }
                $titulo = $row['titulo'] . ' ' . datas::dataMS2H($row['dt_criado']);
                if(!empty($titulo)){
                    $titulo = '<div style="text-align: left;pointer-events: auto;word-wrap: break-word;font-family: inherit;font-size: 16px;line-height: 1.1;box-sizing: border-box;color: #007bff;text-decoration: none;background-color: transparent;font-weight: 600;">' . $titulo . '</div>';
                }
                $temp['filho'][] = array('titSub' => /*$row['titulo']*/ $titulo , 'conteudo' => nl2br($row['conteudo']), 'icone' => 'fa-user', 'iconeCor' => 'bg-aqua', 'imagem' => getAvatarUsuario($row['usuario']));
            }
            if(count($temp) > 0){
                $param['pai'][] = $temp;
            }
            $ret = addTimeline($param);
        }
        return $ret;
    }
    
    static function salvarComentario($id, $texto = ''){
        $ret = '';
        $texto = $_POST['texto'] ?? $texto;
        if(!empty($texto)){
            $texto = str_replace("'", "''", $texto);
            $sql = "insert into kl_comentarios (card, titulo, conteudo, usuario) values ($id, '" . getUsuario('apelido') . "', '$texto', '" . getUsuario() . "')";
            query($sql);
        }
        $ret = kanban_lite::gerarHistoricoComentarios($id);
        return $ret;
    }
    
    static function desenharBlocoAnexo($id){
        $ret = '';
        $tabela_anexos = new tabela01();
        $tabela_anexos->addColuna(array('campo' => 'nome', 'etiqueta' => 'Arquivos'));
        
        $dados = array();
        $sql = "select * from kl_anexos where card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $html = '<a class="btn btn-tool" onclick="op2(\'' . getLinkAjax('mostrarAnexo') . "&id=$id&arquivo={$row['id']}')" . '">' . $row['arquivo'] . '</a>';
                $html = '<a class="btn btn-tool" href="' . getLinkAjax('mostrarAnexo') . "&id=$id&arquivo={$row['id']}" . '" download>' . $row['arquivo'] . '</a>';
                
                $temp = array(
                    'nome' => $html,
                );
                $dados[] = $temp;
            }
        }
        $tabela_anexos->setDados($dados);
        
        
        $form = new form01();
        $form->addCampo(array('tipo' => 'F', 'nome' => 'id_campo_anexo', 'id' => 'id_campo_anexo', 'campo' => 'id_campo_anexo', 'estilo' => 'opacity:0'));
        $bt_enviar_arquivo = formbase01::formBotao(array('texto' => 'Enviar Arquivo', 'onclick' => 'enviarArquivoAnexo(' . $id . ')'));
        $ret = '<div id="idBlocoAnexo">' . $tabela_anexos . '<br>' . $form . '<br>' . $bt_enviar_arquivo . '</div>';
        return $ret;
    }
    
    static function salvarAnexo($id){
        if(isset($_FILES['arquivo']) && isset($_FILES['arquivo']['tmp_name'])){
            global $config;
            $dir = ($config['anexos_kanban_lite'] ?? '/var/www/crm/anexosKanbanLite/') . $id;
            if(!is_dir($dir)){
                mkdir($dir);
            }
            $origem = $_FILES['arquivo']['tmp_name'];
            $destino = $dir . '/' . $_FILES['arquivo']['name'];
            move_uploaded_file($origem, $destino);
            $sql = "insert into kl_anexos values (null, $id, '{$_FILES['arquivo']['name']}')";
            query($sql);
            
            $comentario = "Anexou o arquivo " . $_FILES['arquivo']['name'];
            kanban_lite::salvarComentario($id, $comentario);
        }
        
        return kanban_lite::desenharBlocoAnexo($id);
    }
    
    static function mostrarArquivo($file){
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        header('Connection: close');
        ob_clean();
        flush();
        readfile($file);
    }
    
    static function mostrarAnexo($id, $anexo){
        $sql = "select * from kl_anexos where card = $id and id = $anexo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            global $config;
            $file = ($config['anexos_kanban_lite'] ?? '/var/www/crm/anexosKanbanLite/') . "$id/" . $rows[0]['arquivo'];
            kanban_lite::mostrarArquivo($file);
        }
    }
    
    static function alterarCard($id, $titulo, $resumo, $cor, $tags){
        $sql = "update kl_cards set ";
        if(!empty($titulo)){
            $sql .= "etiqueta = '$titulo', ";
        }
        $sql .= " resumo = '$resumo', cor = '$cor', tags = '$tags' where id = $id";
        query($sql);
    }
    
    static function getListaCores($card = true){
        if($card){
            $ret = array(
                array('primary', 'Azul'),
                array('secondary', 'Cinza'),
                array('success', 'Verde'),
                array('info', 'Ciano'),
                array('warning', 'Amarelo'),
                array('danger', 'Vermelho'),
                array('orange', 'Laranja'),
            );
        }
        else{
            $ret = array(
                array('secondary', 'Cinza'),
                array('primary', 'Azul'),
                array('success', 'Verde'),
                array('info', 'Ciano'),
                array('warning', 'Amarelo'),
                array('danger', 'Vermelho'),
                array('orange', 'Laranja'),
            );
        }
        
        return $ret;
    }
    
    static function montarFormularioEtapa($etapa){
        $form = new form01();
        
        $form->addCampo(array('id' => 'campoEtiqueta', 'campo' => 'formCard[etiqueta]'		, 'etiqueta' => 'Titulo'	, 'linha' => 1, 'largura' =>12, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => kanban_lite::getCampoEtapa($etapa, 'etiqueta')		, 'lista' => '', 'funcao_lista' => ""			                                     , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => 'campoCor'    , 'campo' => 'formCard[cor]'		, 'etiqueta' => 'Cor'			, 'linha' => 2, 'largura' =>12, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => kanban_lite::getCampoEtapa($etapa, 'cor')		, 'lista' => kanban_lite::getListaCores(false), 'funcao_lista' => ''	 , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 60));
        //$form->addCampo(array('id' => 'campoOrdem'  , 'campo' => 'formCard[ordem]'		, 'etiqueta' => 'Ordem'			, 'linha' => 3, 'largura' =>12, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => kanban_lite::getCampoEtapa($etapa, 'ordem')		, 'lista' => '', 'funcao_lista' => ""			                                     , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'numero'));
        
        return $form . '';
    }
    
    static function getCampoEtapa($etapa, $campo){
        $ret = '';
        if(!empty($etapa) && !empty($campo)){
            $sql = "select $campo from kl_etapas where id = $etapa";
            $rows = query($sql);
            $ret = $rows[0][$campo];
        }
        return $ret;
    }
    
    static function atualizarEtapaEditada($etapa, $etiqueta, $cor, $ordem){
        $sql = "update kl_etapas set cor = '$cor'";
        if(!empty($etiqueta)){
            $sql .= ", etiqueta = '$etiqueta'";
        }
        $sql .= " where id = $etapa";
        query($sql);
    }
    
    static function montarCabEditarEtapa($etapa){
        $ret = "Editar Etapa<br>" . kanban_lite::montarBotoesMoverEtapa($etapa);
        return $ret;
    }
    
    static function montarBotoesMoverEtapa($etapa){
        $ret = '';
        $sql = "select count(*) as total from kl_etapas where board = " . kanban_lite::getCampoEtapa($etapa, 'board');
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $total = $rows[0]['total'];
            $ordem = kanban_lite::getCampoEtapa($etapa, 'ordem');
            $bt_esquerda = array(
                //'texto' => 'Mover para a Esquerda',
                'texto' => '&#8203;',
                'onclick' => "trocarOrdemEtapa(@@id, 'E')",
                'bloco' => true,
                'style' => 'clip-path: polygon(0 50%,50% 0%,100% 0, 100% 100%, 50% 100%);',
            );
            if(intval($ordem) == 1){
                $bt_esquerda['ativo'] = false;
            }
            $bt_esquerda = formbase01::formBotao($bt_esquerda);
            
            $bt_direita = array(
                //'texto' => 'Mover para a Direita',
                'texto' => '&#8203;',
                'onclick' => "trocarOrdemEtapa(@@id, 'D')",
                'bloco' => true,
                'style' => 'clip-path: polygon(0 0,50% 0,100% 50%,50% 100%,0 100%);',
            );
            if (intval($ordem) == intval($total)){
                $bt_direita['ativo'] = false;
            }
            $bt_direita = formbase01::formBotao($bt_direita);
            
            $bt_meio = array(
                'texto' => 'Mover',
                'bloco' => true,
            );
            $bt_meio = formbase01::formBotao($bt_meio);
            
            $ret =
            '<div class="row">
                <div class="col-lg-3" style="margin: 0; padding: 0;">
                    ' . $bt_esquerda . '
                </div>
                <div class="col-lg-6" style="margin: 0; padding: 0;">
                    ' . $bt_meio . '
                </div>
                <div class="col-lg-3" style="margin: 0; padding: 0;">
                    ' . $bt_direita . '
                </div>
            </div>';
            //$ret = addLinha($param);
            //$ret .= $bt_esquerda . $bt_direita;
        }
        return $ret;
    }
    
    static function trocarOrdemEtapa($etapa, $sentido){
        $ordem_original = intval(kanban_lite::getCampoEtapa($etapa, 'ordem'));
        $ordem_alvo = $ordem_original;
        if($sentido == 'D'){
            $ordem_alvo++;
        }
        if($sentido == 'E'){
            $ordem_alvo--;
        }
        $board = kanban_lite::getCampoEtapa($etapa, 'board');
        $sql = "select id from kl_etapas where board = $board and ordem = $ordem_alvo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $etapa_pivo = $rows[0]['id'];
            $sql = "update kl_etapas set ordem = 1000 where id = $etapa_pivo";
            query($sql);
            $sql = "update kl_etapas set ordem = $ordem_alvo where id = $etapa";
            query($sql);
            $sql = "update kl_etapas set ordem = $ordem_original where id = $etapa_pivo";
            query($sql);
        }
        
    }
    
    static function getTamanhoModal($id){
        $ret = 'grande';
        $sql = "select tamanho from kl_tipos where codigo in (select tipo from kl_cards where id = $id)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['tamanho'];
        }
        return $ret;
    }
    
    static function ajax(){
        $op = getOperacao();
        $id = getParam($_GET, 'id', '');
        $ret = '';
        if(!empty($id)){
            $id = intval($id);
            if($op === 'titulo'){
                $ret = kanban_lite::criarTituloCard($id);
            }
            
            if($op === 'mover'){
                $coluna = $_GET['coluna'] ?? '';
                if(!empty($coluna)){
                    $sql = "update kl_cards set etapa = '$coluna' where id = $id";
                    query($sql);
                }
            }
            if($op === 'visualizar'){
                $ret = kanban_lite::MontarCondeudoCard($id);
            }
            if($op === 'editar'){
                $ret = kanban_lite::montarFormularioCard($id);
            }
            if($op === 'salvaEditar'){
                kanban_lite::salvarEditar($id);
            }
            if($op === 'addComentario'){
                $ret = kanban_lite::salvarComentario($id);
            }
            if($op === 'salvarAnexo'){
                $ret = kanban_lite::salvarAnexo($id);
            }
            if($op === 'mostrarAnexo'){
                $anexo = $_GET['arquivo'] ?? '';
                kanban_lite::mostrarAnexo($id, $anexo);
            }
            if($op == 'configurar'){
                $ret = kanban_lite::formularioNovaTarefa($id);
            }
            if($op == 'salvarConfigurar'){
                $etiqueta = $_POST['etiqueta'] ?? '';
                $resumo = $_POST['resumo'] ?? '';
                $cor = $_POST['cor'] ?? 'primary';
                $tags = $_POST['tags'] ?? '';
                //$tipo = $_POST['tipo'] ?? '';
                kanban_lite::alterarCard($id, $etiqueta, $resumo, $cor, $tags);
            }
            if($op === 'getTamanhoModal'){
                $ret = kanban_lite::getTamanhoModal($id);
            }
            if($op === 'montarBtSalvarEditar'){
                $ret = kanban_lite::criarBotaoSalvarInclusao('editar', $id);
            }
            /*
             if($op === 'mostrarContrato'){
             kanban_lite::mostrarContrato($id);
             }
             if($op === 'salvarContrato'){
             $ret = kanban_lite::salvarContrato($id);
             }
             */
            $classe = 'kanban_lite_' . strtolower(kanban_lite::getCampoCard($id, 'tipo'));
            if(method_exists($classe, 'ajax')){
                $ret_ajax_card = $classe::ajax();
                if(!empty($ret_ajax_card)){
                    $ret = $ret_ajax_card;
                }
            }
        }
        if($op == 'formularioEtapa'){
            $etapa = $_GET['etapa'];
            $ret = kanban_lite::montarFormularioEtapa($etapa);
        }
        if($op == 'cabEtapa'){
            $etapa = $_GET['etapa'];
            $ret = kanban_lite::montarCabEditarEtapa($etapa);
        }
        if($op === 'salvarEtapaEditada'){
            $etapa = $_GET['etapa'];
            $etiqueta = $_POST['etiqueta'];
            $cor = $_POST['cor'];
            $ordem = $_POST['ordem'];
            kanban_lite::atualizarEtapaEditada($etapa, $etiqueta, $cor, $ordem);
        }
        if($op === 'trocarOrdemEtapa'){
            $etapa = $_GET['etapa'];
            $sentido = $_GET['sentido'];
            kanban_lite::trocarOrdemEtapa($etapa, $sentido);
        }
        if($op === 'salvarInclusao'){
            $etiqueta = $_POST['etiqueta'] ?? '';
            $etapa = $_POST['etapa'] ?? '';
            $resumo = $_POST['resumo'] ?? '';
            $tipo = $_POST['tipo'] ?? '';
            $cor = $_POST['cor'] ?? 'primary';
            $tags = $_POST['tags'] ?? '';
            kanban_lite::criarCard($etiqueta, $etapa, $resumo, $tipo, $cor, $tags);
        }
        return $ret;
    }
}