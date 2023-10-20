<?php
#[\AllowDynamicProperties]
class kanboard{
    var $funcoes_publicas = array(
        'index' 	                => true,
        'ajax'                      => true,
    );
    
    //id do projeto
    protected $_projeto;
    
    //link do ajax que retorna o formulario para incluir uma tarefa nova
    protected $_linkAjaxFormulario;
    
    //link do ajax que move as tarefas
    protected $_linkAjaxMover;
    
    //link do ajax para salvar uma tarefa nova
    protected $_linkAjaxSalvar;
    
    //link usado para recarregar a board
    protected $_linkReload;
    
    //link usado para checar se a board foi modificada
    protected $_linkCheck;
    
    protected $_linkAjaxFormularioFecharTarefas;
    
    protected $_linkConfirmarFecharTarefasColuna;
    
    protected $_tabelaEntidades;
    
    protected $_nivelTarefas;
    
    function __construct($projeto, $param = array()){
        $this->_projeto = $projeto;
        $this->_tabelaEntidades = $this->getTabelaEntidades();
        
        $this->_linkAjaxFormulario = $param['linkFormulario'] ?? (getLinkAjax('formulario'));
        $this->_linkAjaxMover = $param['linkMover'] ?? getLinkAjax('mover');
        $this->_linkAjaxSalvar = $param['linkSalvar'] ?? getLinkAjax('salvar');
        $this->_linkReload = $param['linkReload'] ?? (getLink() . 'index');
        $this->_linkCheck = $param['linkCheck'] ?? (getLinkAjax('check') . '&projeto=' . $this->_projeto);
        $this->_linkExcluir = $param['linkExcluir'] ?? (getLinkAjax('excluirTarefaConfirmar'));
        $this->_linkAjaxFormularioFecharTarefas = $param['linkFormFecharTarefas'] ?? (getLinkAjax('formFecharTarefas') . '&projeto=' . $this->_projeto);
        $this->_linkConfirmarFecharTarefasColuna = $param['linkFecharTarefasColuna'] ?? (getLinkAjax('fecharTarefasColuna'));
        $this->_nivelTarefas = $param['nivelTarefas'] ?? $this->getNivelTarefas();
    }
    
    protected function getNivelTarefas(){
        //retorna o nível de visualização das tarefas
        $ret = 'livre';
        $sql = "select visualizacao from kanboard_projetos where id = $this->_projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['visualizacao'];
        }
        return $ret;
    }
    
    protected function getComentariosTarefa($id_tarefa){
        $ret = array();
        $sql = "select kanboard_comentarios.*, sys001.nome as criador_nome  from kanboard_comentarios left join sys001 on (kanboard_comentarios.criador = sys001.id) where tarefa = $id_tarefa order by kanboard_comentarios.dt_criado";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = array('id', 'tarefa', 'conteudo', 'dt_criado', 'dt_modificado', 'criador_nome', 'criador');
            foreach ($rows as $row){
                $temp = array();
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $ret[] = $temp;
            }
        }
        
        return $ret;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return kanboard_permissoes::checarPermissao($operacao, $projeto, $coluna, $raia, $usuario);
    }
    
    public function ajax($op = ''){
        $ret = '';
        if($op == ''){
            $op = getOperacao();
        }
        
        if($op == 'editarTarefa'){
            if($this->getOpercaoClicarTarefas() === 'janela'){
                $GLOBALS['tws_pag'] = array(
                    'header'   	=> false,
                    'html'		=> false,
                    'menu'   	=> false,
                    'content' 	=> false,
                    'footer'   	=> false,
                );
            }
            $ret = $this->editarTarefa();
        }
        else{
            $GLOBALS['tws_pag'] = array(
                'header'   	=> false,
                'html'		=> false,
                'menu'   	=> false,
                'content' 	=> false,
                'footer'   	=> false,
            );
            if($op == 'mover'){
                $ret = $this->moverAjax();
            }
            elseif($op == 'salvar'){
                $this->salvarAjax();
            }
            elseif($op == 'formulario'){
                $coluna = $_GET['coluna'];
                $raia = $_GET['raia'];
                if($this->verificarPermissaoLocal('criar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $ret = $this->formularioAjax();
                }
                else{
                    $ret = 'Você não tem permissão para criar tarefas nesse(a) projeto, coluna ou raia';
                }
            }
            elseif($op == 'check'){
                $ret = $this->checarAjax();
            }
            elseif($op == 'excluirTarefaForm'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('excluir', $this->_projeto, $coluna, $raia, getUsuario())){
                    $ret = $this->excluirTarefaForm();
                }
                else{
                    $ret = 'Você não pode excluir tarefas desse(a) projeto, coluna ou raia';
                }
            }
            elseif($op == 'excluirTarefaConfirmar'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('excluir', $this->_projeto, $coluna, $raia, getUsuario())){
                    $this->excluirTarefaConfirmar();
                }
                else{
                    header('X-Ajax-Redirect: ' . $this->_linkReload);
                }
            }
            elseif($op == 'formularioComentario'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('comentar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $ret = kanboard_formularios::formularioComentario();
                }
                else{
                    $ret = 'Você não pode comentar tarefas nesse(a) projeto, coluna ou raia';
                }
            }
            elseif($op == 'salvarComentario'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('comentar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $comentario = $_POST['comment'] ?? '';
                    $this->salvarComentario($tarefa, $comentario);
                }
                header('X-Ajax-Redirect: ' . $this->_linkReload);
            }
            elseif($op == 'formFecharTarefas'){
                $raia = $_GET['raia'];
                $coluna = $_GET['coluna'];
                if($this->verificarPermissaoLocal('fechar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $ret = kanboard_formularios::formularioFecharTarefas($raia, $coluna, $this->_linkConfirmarFecharTarefasColuna);
                }
                else{
                    $ret = 'Você não pode fechar tarefas nesse(a) projeto, coluna ou raia';
                }
            }
            elseif($op == 'fecharTarefasColuna'){
                $raia = $_POST['raia'];
                $coluna = $_POST['coluna'];
                if($this->verificarPermissaoLocal('fechar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $this->fecharTarefasColuna($raia, $coluna);
                }
                header('X-Ajax-Redirect: ' . $this->_linkReload);
            }
            elseif ($op == 'listarComentarios'){
                $tarefa = $_GET['tarefa'];
                $ret = $this->montarJanelaComentarios($tarefa);;
            }
            elseif ($op == 'salvarListarComentarios'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('comentar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $comentario = $_POST['comment'];
                    $this->salvarComentario($tarefa, $comentario);
                }
                $ret = $this->montarJanelaComentarios($tarefa);
            }
            elseif ($op == 'listaSubTarefas'){
                $tarefa = $_GET['tarefa'];
                $ret = '    <div class="tooltip-large">
                ' . $this->criarTabelaSubTarefas($tarefa) . '
                            </div>';
                    
            }
            elseif ($op == 'mudarStatusSubTarefa'){
                $sub_tarefa = $_GET['sub_tarefa'];
                $status = $_GET['status'];
                $tarefa = $this->getTarefaFromSubTarefa($sub_tarefa);
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('alterarSub', $this->_projeto, $coluna, $raia, getUsuario())){
                    $this->mudarStatusSubTarefa($sub_tarefa, $status);
                }
                $ret = $this->desenharNovoStatusSubTarefa($sub_tarefa);
            }
            elseif ($op == 'formSubTarefa'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('criarSub', $this->_projeto, $coluna, $raia, getUsuario())){
                    $link_salvar = getLinkAjax('salvarSubTarefa') . "&tarefa=$tarefa";
                    $ret = kanboard_formularios::formularioSubTarefas($link_salvar, true, $tarefa);
                }
                else{
                    $ret = 'Você não pode criar sub tarefas nesse(a) projeto, coluna ou raia';
                }
            }
            elseif ($op == 'salvarSubTarefa'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('criarSub', $this->_projeto, $coluna, $raia, getUsuario())){
                    $texto = $_POST['title'];
                    $usuario = $_POST['user_id'];
                    $this->salvarSubTarefas($tarefa, $texto, $usuario);
                }
                header('X-Ajax-Redirect: ' . $this->_linkReload);
            }
            elseif ($op == 'salvarModalTarefaComentario'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('comentar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $comentario = $_POST['comment'];
                    $this->salvarComentario($tarefa, $comentario);
                }
                $ret = $this->editarTarefa();
            }
            elseif ($op == 'salvarModalTarefaSubTarefa'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                log::gravaLog('190123_post', json_encode($_POST));
                if(!empty( $_POST['comment'])){
                    //não consegui arrumar o modal de edição pra ele salvar as subTarefas e comentários separados, vou testar isso
                    if($this->verificarPermissaoLocal('comentar', $this->_projeto, $coluna, $raia, getUsuario())){
                        $comentario = $_POST['comment'];
                        $this->salvarComentario($tarefa, $comentario);
                    }
                }
                if(!empty($_POST['title'])){
                    if($this->verificarPermissaoLocal('criarSub', $this->_projeto, $coluna, $raia, getUsuario())){
                        $texto = $_POST['title'];
                        $usuario = $_POST['user_id'];
                        $this->salvarSubTarefas($tarefa, $texto, $usuario);
                    }
                }
                
                $ret = $this->editarTarefa();
            }
            elseif ($op == 'btEditarTarefa'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('editar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $ret = kanboard_formularios::formularioBtEditarTarefa($tarefa);
                }
                else{
                    $ret = 'Você não tem permissão para editar tarefas nesse(a) projeto, coluna ou raia';
                }
            }
            elseif ($op == 'salvarBtEditarTarefa'){
                $tarefa = base64_decode($_GET['tarefa']);
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('editar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $tarefa = base64_encode($tarefa);
                    $cad = new cad01('kanboard_tarefas');
                    $cad->salvar($tarefa, $_POST['formCRUD'], 'E');
                }
                header('X-Ajax-Redirect: ' . $this->_linkReload);
            }
            elseif ($op == 'fecharTarefaForm'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('fechar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $ret = kanboard_formularios::formularioFecharTarefasUnitaria(getLinkAjax('fecharTarefaFormConfirmar') . "&tarefa=$tarefa", $tarefa);
                }
                else{
                    $ret = 'Você não pode fechar tarefas nesse(a) projeto, coluna ou raia';
                }
            }
            elseif ($op == 'fecharTarefaFormConfirmar'){
                $tarefa = $_GET['tarefa'];
                $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
                $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
                if($this->verificarPermissaoLocal('fechar', $this->_projeto, $coluna, $raia, getUsuario())){
                    $this->fecharTarefa($tarefa);
                }
                header('X-Ajax-Redirect: ' . $this->_linkReload);
            }
        }
        return $ret;
    }
    
    protected function fecharTarefa($tarefa){
        $sql = "update kanboard_tarefas set status = 'F' where id = $tarefa";
        query($sql);
    }
    
    protected function criarTabelaSubTarefas($tarefa){
        $ret = '<table class="table-small" style="font-family: Helvetica,Arial,sans-serif,FontAwesome;">
                    <tr>
                        <th class="column-70">Subtarefa</th>
                        <th>Designação</th>
                    </tr>';
        $sub_tarefas = $this->getSubTarefas($tarefa);
        foreach ($sub_tarefas as $s){
            $ret .= "<tr>
                        <td style=\"font-family: Helvetica,Arial,sans-serif,FontAwesome;\">{$s['tarefa']}</td>
                        <td style=\"font-family: Helvetica,Arial,sans-serif,FontAwesome;\">{$s['usuario']}</td>
                    </tr>";
        }
        $ret .= '</table>';
        return $ret;
    }
    
    protected function salvarSubTarefas($tarefa, $texto, $usuario){
        if(!empty(trim($texto))){
            $subtasks = explode("\r\n", $texto);
            foreach ($subtasks as $sub){
                $sub = trim($sub);
                if(!empty($sub)){
                    $data = date('YmdHis');
                    $param = array(
                        'tarefa' => $tarefa,
                        'conteudo' => $sub,
                        'status' => 'a',
                        'usuario' => $usuario,
                        'dt_criacao' => $data,
                    );
                    $sql = montaSQL($param, 'kanboard_sub_tarefas');
                    query($sql);
                }
            }
        }
    }
    
    protected function mudarStatusSubTarefa($sub_tarefa, $status_velho){
        $lista_status = array(
            'a' => 'e',
            'e' => 'f',
            'f' => 'a',
        );
        $status_novo = $lista_status[$status_velho];
        $sql = "update kanboard_sub_tarefas set status = '$status_novo' where id = $sub_tarefa";
        query($sql);
        return $status_novo;
    }
    
    protected function desenharNovoStatusSubTarefa($sub_tarefa){
        $sql = "select kanboard_sub_tarefas.*, temp1.nome, temp1.icone, sys001.nome as nome_usuario from kanboard_sub_tarefas join (
SELECT 'a' as id, 'Subtarefa não iniciada' as nome, 'fa fa-square-o fa-fw' as icone UNION SELECT 'e' as id, 'Subtarefa atualmente em progresso' as nome, 'fa fa-gears fa-fw' as icone UNION SELECT 'f' as id, 'Subtarefa finalizada' as nome, 'fa fa-check-square-o fa-fw' as icone) temp1 on (kanboard_sub_tarefas.status = temp1.id)
left join sys001 on (kanboard_sub_tarefas.usuario = sys001.id) where kanboard_sub_tarefas.id = $sub_tarefa";
        $rows = query($sql);
        $row = $rows[0];
        $ret = '<span class="subtask-title">
                                                <a href="' . getLinkAjax('mudarStatusSubTarefa') . '&sub_tarefa=' . $row['id'] . '&status=' . $row['status'] . '" class="js-subtask-toggle-status" title="' . $row['nome'] . '">
                                                    <i class="' . $row['icone'] . '" style="font-family: Helvetica,Arial,sans-serif,FontAwesome;"></i>
                                                    ' . $row['conteudo'] . '
                                                </a>
                                            </span>';
        return $ret;
    }
    
    protected function getSubTarefas($tarefa){
        $ret = array();
        $sql = "select kanboard_sub_tarefas.*, temp1.nome, temp1.icone, sys001.nome as nome_usuario from kanboard_sub_tarefas join (
SELECT 'a' as id, 'Subtarefa não iniciada' as nome, 'fa fa-square-o fa-fw' as icone UNION SELECT 'e' as id, 'Subtarefa atualmente em progresso' as nome, 'fa fa-gears fa-fw' as icone UNION SELECT 'f' as id, 'Subtarefa finalizada' as nome, 'fa fa-check-square-o fa-fw' as icone) temp1 on (kanboard_sub_tarefas.status = temp1.id) 
left join sys001 on (kanboard_sub_tarefas.usuario = sys001.id) where tarefa = $tarefa";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['tarefa'] = '<span class="subtask-title">
                                                <a href="' . getLinkAjax('mudarStatusSubTarefa') . '&sub_tarefa=' . $row['id'] . '&status=' . $row['status'] . '" class="js-subtask-toggle-status" title="' . $row['nome'] . '">
                                                    <i class="' . $row['icone'] . '" style="font-family: Helvetica,Arial,sans-serif,FontAwesome;"></i>
                                                    ' . $row['conteudo'] . '
                                                </a>
                                            </span>';
                $temp['usuario'] = $row['nome_usuario'];
                $ret[] = $temp;
            }
        }
        //print_r($ret);
        return $ret;
    }
    
    protected function salvarComentario($tarefa, $comentario){
        $data = date('YmdHis');
        $param = array(
            'tarefa' => $tarefa,
            'conteudo' => $comentario,
            'dt_criado' => $data,
            'dt_modificado' => $data,
            'criador' => getUsuario('id'),
        );
        
        $sql = montaSQL($param, 'kanboard_comentarios');
        query($sql);
        
        $sql = montaSQL($param, 'kanboard_comentarios', 'SELECT', '', 'id');
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $this->gerarLog('comentario', 'criar', $rows[0]['id'], "tarefa $tarefa | comentario $comentario");
        }
    }
    
    protected function montarJanelaComentarios($tarefa){
        addPortalJS('plugin', 'kanboard/app.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor.min.js', 'I');
        addPortalJS('plugin', 'kanboard/dropdown.js', 'I');
        addPortalJS('plugin', 'kanboard/comment-highlight.js', 'I');
        
        
        
        addPortalCSS('plugin', 'kanboard/css/vendor.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/print.min.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/app.min.css', 'I');
        $comentarios = $this->getComentariosTarefa($tarefa);
        $param = array(
            'comentarios' => $comentarios,
            'titulo' => 'Comentários do card <b>' . kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'etiqueta') . '</b>',
            'tarefa' => $tarefa,
        );
        $obj = new kanboard_comentarios($param);
        return $obj . '';
    }
    
    protected function fecharTarefasColuna($raia, $coluna){
        $sql = "select id from kanboard_tarefas where raia = $raia and coluna = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $this->gerarLog('tarefa', 'fechamento', $row['id']);
            }
        }
        $sql = "update kanboard_tarefas set status = 'F' where raia = $raia and coluna = $coluna";
        query($sql);
    }
    
    protected function gerarLog($entidade, $operacao, $id, $complemento = '', $usuario = ''){
        if($usuario === ''){
            $usuario = getUsuario();
        }
        $sql = "insert into kanboard_log values (null, {$this->getProjetoLog()}, '$usuario', '$operacao', '$entidade', '$id', '$complemento')";
        query($sql);
    }
    
    protected function getProjetoLog(){
        return $this->_projeto;
    }
    
    protected function excluirTarefaConfirmar(){
        $id = $_GET['tarefa'];
        $this->deletarTarefaBanco($id);
        header('X-Ajax-Redirect: ' . $this->_linkReload);
    }
    
    protected function deletarTarefaBanco($id){
        $this->atualizarProjeto('', '', $id);
        $sql = "update kanboard_tarefas set status = 'D' where id = $id";
        query($sql);
        $this->gerarLog('tarefa', 'excluir', "$id");
    }
    
    protected function atualizarProjeto($projeto, $coluna = '', $tarefa = '', $raia = ''){
        $sql = '';
        if($projeto != ''){
            $sql = "update kanboard_projetos set modificado = '" . time() . "' where id = $projeto";
        }
        elseif($projeto == '' && $coluna != ''){
            $sql = "update kanboard_projetos set modificado = '" . time() . "' where id in (select projeto from kanboard_colunas where id = $coluna)";
        }
        elseif($projeto == '' && $coluna == '' && $tarefa != ''){
            $sql = "update kanboard_projetos set modificado = '" . time() . "' where id in (select projeto from kanboard_tarefas where id = $tarefa)";
        }
        elseif($projeto == '' && $coluna == '' && $tarefa == '' && $raia != ''){
            $sql = "update kanboard_projetos set modificado = '" . time() . "' where id in (select projeto from kanboard_raia where id = $raia)";
        }
        query($sql);
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('plugin', 'kanboard/app.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor.min.js', 'I');
        addPortalJS('plugin', 'kanboard/dropdown.js', 'I');
        
        addPortalCSS('plugin', 'kanboard/css/vendor.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/print.min.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/app.min.css', 'I');
        $tarefa = $_GET['tarefa'];
        $link_excluir = $this->_linkExcluir . '&tarefa=' . $tarefa;
        $link_excluir = htmlentities($link_excluir);
        $opcoes_ajax = array(
            'url'         => $link_excluir,
            'submitLabel' => 'Excluir',
            'orLabel'     => 'OU',
            'cancelLabel' => 'Cancelar',
            'tabindex'    => null,
        );
        
        $param_janela = array(
            'titulo' => 'Excluir tarefa',
            'corpo' => 'Você realmente quer excluir esta tarefa: "' . kanboard_tarefa_unitaria::getTituloTarefa($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function verificarDtModificacao($projeto, $estampa_ajax){
        $ret = false;
        $sql = "select modificado from kanboard_projetos where id = $projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $estampa_banco = $rows[0]['modificado'];
            $ret = intval($estampa_ajax) < $estampa_banco;
        }
        return $ret;
    }
    
    protected function checarAjax($projeto = ''){
        $ret = '';
        if($projeto == ''){
            $projeto = $_GET['projeto'] ?? $this->_projeto;
        }
        $estampa_ajax = $_GET['estampa_intranet'];
        
        if($this->verificarDtModificacao($projeto, $estampa_ajax)){
            http_response_code(200);
            $ret = $this->criarTabelaTarefas();
        }
        else{
            http_response_code(304);
            //caso não tenha ocorrido nenhuma modificação
        }
        
        return $ret;
    }
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        $entidade = $_GET['multi_entidade'] ?? '';
        addPortalJS('plugin',  'kanboard/app.min.js', 'I');
        addPortalJS('plugin',  'kanboard/vendor.min.js', 'I');
        addPortalCSS('plugin', 'kanboard/css/app.min.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/print.min.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/vendor.min.css', 'I');
        addPortalJS('plugin',  'kanboard/Task.js', 'I');
        
        $link_salvar = $this->_linkAjaxSalvar;
        if($entidade !== ''){
            $link_salvar .= "&multi_entidade=$entidade";
        }
        $ret = kanboard_formularios::formularioNovaTarefa($link_salvar, $coluna, $raia, $entidade);
        
        /*
         $form = new kanboard_formulario_nova_tarefa(array(), '/intranet4/ajax.php?menu=testes.mercado_pago.ajax.teste&coluna=' . $coluna);
         $form = new kanboard_formulario_nova_tarefa(array(), '/intranet4/index.php?menu=testes.mercado_pago.index.nova_tarefa');
         $form->addCampo(array('id' => 'modulo'	    , 'campo' => 'etiqueta'	             ,'valor'=> ''     , 'etiqueta' => 'Etiqueta'	                                , 'tipo' => 'T'  , 'tamanho' => '10', 'linha' => 2, 'largura' => 6	, 'lista' => ''		                 								, 'validacao' => ''));
         */
        //$ret = $form . '';
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'] ?? '';
        $raia = $_GET['raia'];
        $entidade = $_GET['multi_entidade'] ?? $this->_tabelaEntidades;
        
        if($entidade != ''){
            $id_entidade = $this->salvarEntidade($entidade);
            if($id_entidade != 0){
                $id_tarefa = $this->incluirTarefa($etiqueta, $coluna, $raia);
                $this->amarrarTarefaEntidade($id_tarefa, $id_entidade, $entidade);
            }
        }
        else{
            $this->incluirTarefaCompleta($coluna, $raia);
        }
        header('X-Ajax-Redirect: ' . $this->_linkReload);
    }
    
    protected function incluirTarefaCompleta($coluna, $raia){
        $dados_post = $_POST['formCRUD'];
        unset($dados_post['id']);
        $dados_post['coluna'] = $coluna;
        $dados_post['raia'] = $raia;
        $dados_post['data_limite'] = datas::dataD2S($dados_post['data_limite']);
        $dados_post['score'] = str_replace(array('.', ','), array('', '.'), $dados_post['score']);
        $dados_post['posicao'] = $this->getUltimaPosicaoColunaDestino($coluna, $raia);
        //$cad = new cad01('kanboard_tarefas');
        //$cad->salvar(0, $dados_post, 'I');
        
        $sql = montaSQL($dados_post, 'kanboard_tarefas');
        query($sql);
    }
    
    protected function amarrarTarefaEntidade($tarefa, $entidade, $tabela = ''){
        if($tabela == ''){
            $sql = "insert into kanboard_entidades values (null, $tarefa, '$entidade', '{$this->_tabelaEntidades}')";
        }
        else{
            $sql = "insert into kanboard_entidades values (null, $tarefa, '$entidade', '$tabela')";
        }
        query($sql);
    }
    
    protected function salvarEntidade($entidade = ''){
        $ret = '';
        if($entidade == ''){
            $tabela = $this->getTabelaEntidades();
        }
        else{
            $tabela = $entidade;
        }
        $cad = new cad01($tabela);
        $cad->salvar([], [], 'I');
        $ret = $cad->_ultimoIdIncluido;
        return $ret;
    }
    
    protected function moverAjax(){
        $ret = '';
        $entityBody = file_get_contents('php://input');
        $dados = json_decode($entityBody, true);
        $this->moverTarefa($dados);
        $tabela = $this->criarTabelaTarefas();
        $ret .= $tabela;
        return $ret;
    }
    
    protected function getOpercaoClicarTarefas(){
        $ret = '';
        $sql = "select operacao from kanboard_projetos where id = {$this->_projeto}";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['operacao'];
        }
        return $ret;
    }
    
    protected function gerarJsData(){
        //só pra incluir o javascript das datas
        
        $temp = new form01();
        $temp->addCampo(array('tipo' => 'D', 'nome' => 'teste', 'campo' => 'teste'));
        $temp = $temp . '';
    }
    
    protected function tentativaJS(){
        //addPortaljavaScript('$(".cpf").mask("000.000.000-00");');
        addPortalJquery('$(".cpf").mask("000.000.000-00");', 'I');
        //$cad = new cad01('crm_lead');
        //$cad->incluir();
    }
    
    public function index($param_exterior = array()){
        $ret = '';
        
        
        addPortalJS('plugin', 'kanboard/app.min.js', 'I');
        
        //addPortalJS('', 'vendor.min.js', 'I');
        
        
        addPortalJS('plugin', 'kanboard/vendor/jquery-3.6.1.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/jquery-ui.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/datepicker-pt-BR.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/jquery-ui-timepicker-addon.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/jquery-ui-timepicker-addon-i18n.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/jquery.ui.touch-punch.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/select2.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/pt-BR.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/d3.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/c3.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor/isMobile.min.js', 'I');
        
        
        //addPortalJS('', 'modal.js', 'I');
        
        
        addPortalJS('plugin', 'kanboard/dropdown.js', 'I');
        addPortalJS('plugin', 'kanboard/confirm-buttons.js', 'I');
        addPortalJS('plugin', 'kanboard/submit-buttons.js', 'I');
        addPortalJS('plugin', 'kanboard/Task.js', 'I');
        addPortalJS('plugin', 'kanboard/comment-highlight.js', 'I');
        addPortalJS('plugin', 'kanboard/tooltip.js', 'I');
        
        addPortalJS('plugin', 'mask/jquery.mask.js', 'I');
        addPortalJS('plugin', 'maskmoney/jquery.maskMoney.min.js', 'I');
            
        //addPortalJS('kanboard', 'App.js', 'F');
        if($this->getOpercaoClicarTarefas() === 'janela'){
            //altera o q acontece quando se clica nos cards, caso verdadeiro se passa a ter um modal com o resultado do url do card
            //só funciona pois no javascript parece valer a ultima definição
            addPortalJS('plugin', 'kanboard/board-task-click.js', 'F');
        }
        
        $this->tentativaJS();
        $this->gerarJsData();
        
        addPortaljavaScript('
            function atribuirUsuarioSubTarefa(){
                var e = document.getElementById("form-user_id");
                e.value = ' . getIdUsuario(getUsuario()) . ';
            }');
        
        
        addPortalCSS('plugin', 'kanboard/css/modal.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/table_drag_and_drop.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/board.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/teste.css', 'I');
        
        //addPortaljavaScript('$(".cpf").mask("000.000.000-00");', 'F');
        
        $tabela = $this->criarTabelaTarefas();
        $ret .= $this->encapsular($tabela . '');
        
        $param = [];
        $param['titulo'] = $this->getNomeProjeto($this->_projeto);
        $param['conteudo'] = $ret;
        foreach ($param_exterior as $chave => $valor){
            $param[$chave] = $valor;
        }
        $ret = addCard($param);
        
        return $ret;
    }
    
    protected function encapsular($html){
        $ret = '<section class="page"><section id="main">';
        $ret .= $html;
        $ret .= '</section></section>';
        return $ret;
    }
    
    public function criarTabelaTarefas(){
        $ret = '';
        if($this->verificarPermissao()){
            $dados = $this->getDadosTabela($this->_projeto);
            if(count($dados) > 0){
                $param = array(
                    'link_salvar' => $this->_linkAjaxMover,
                    'link_reload' => $this->_linkReload,
                    'link_check'  => $this->_linkCheck,
                );
                $tabela = new kanboard_tabela_tarefas($this->_projeto, $dados, $param);
                $ret .= $tabela;
            }
            else{
                $ret = 'Não existem colunas e/ou raias a serem exibidas';
            }
        }
        else{
            $ret = 'Você não tem permissão para ver esse projeto';
        }
        return $ret;
    }
    
    protected function verificarPermissao(){
        return kanboard_permissoes::verificarPermissaoProjeto($this->_projeto, getUsuario());
    }
    
    protected function getNomeProjeto($projeto){
        $ret = '';
        $sql = "select etiqueta from kanboard_projetos where id = $projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['etiqueta'];
        }
        return $ret;
    }
    
    protected function getInfoScore($projeto){
        $ret = array();
        $sql = "select icone_score, prefixo_score, mostrar_score, mostrar_totalizador from kanboard_projetos where id = $projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret['mostrar'] = ($rows[0]['mostrar_score'] == 'S');
            $ret['totalizador'] = ($rows[0]['mostrar_totalizador'] == 'S');
            $ret['icone'] = empty($rows[0]['icone_score']) ? '' : ('&#x' . $rows[0]['icone_score'] . ';');
            $ret['prefixo'] = $rows[0]['prefixo_score'];
        }
        else{
            $ret['mostrar'] = false;
            $ret['totalizador'] = false;
            $ret['icone'] = '';
            $ret['prefixo'] = '';
        }
        return $ret;
    }
    
    protected function getDadosTabela($projeto){
        $ret = array();
        
        $colunas_permitidas = kanboard_permissoes::getColunasPermitidas($this->_projeto, getUsuario());
        $raias_permitidas = kanboard_permissoes::getRaisPermitidas($this->_projeto, getUsuario());
        $usuarios_permitidos = $this->getListaUsuariosByFiltro($this->_nivelTarefas);
        
        $info_score = $this->getInfoScore($projeto);
        
        $mostrar_score = $info_score['mostrar'];
        $mostrar_totalizador = $info_score['totalizador'];
        $icone_score = $info_score['icone'];
        $prefixo_score = $info_score['prefixo'];
        
        $dicionario_tags = $this->montarDicionarioCoresTags($projeto);
        
        if(count($colunas_permitidas) > 0 && count($raias_permitidas) > 0){
            $sql = "
            SELECT kanboard_raia.*
            	,COALESCE(tmp1.total, 0) as total
            FROM kanboard_raia
            left JOIN (
            	SELECT raia
            		,count(*) AS total
            	FROM kanboard_tarefas
            	WHERE raia IN (" . implode(', ', $raias_permitidas) . ")
                and coluna in (" . implode(', ', $colunas_permitidas) . ") 
                and status = 'A'
                AND $usuarios_permitidos
            	GROUP BY raia
            	) tmp1 ON (kanboard_raia.id = tmp1.raia)
            WHERE
            kanboard_raia.id in (". implode(', ', $raias_permitidas) . ")
            ORDER BY kanboard_raia.posicao";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret['id'] = $projeto;
                $ret['titulo'] = $this->getNomeProjeto($projeto);
                
                $raias = array();
                $campos = array('id', 'projeto', 'etiqueta', 'posicao');
                $num_raias = 0;
                foreach ($rows as $row){
                    $temp = array();
                    foreach ($campos as $c){
                        $temp[$c] = $row[$c];
                    }
                    $temp['num_tarefas'] = $row['total'];
                    $raias[$temp['id']] = $temp;
                    $num_raias++;
                }
                
                $sql = "select * from kanboard_colunas where projeto = $projeto";
                $sql .= " and id in (" . implode(', ', $colunas_permitidas) . ")";
                $sql .= " order by posicao";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    $colunas = array();
                    $campos = array('id', 'projeto', 'etiqueta', 'posicao', 'limite', 'descricao');
                    foreach ($rows as $row){
                        $temp = array();
                        foreach ($campos as $c){
                            $temp[$c] = $row[$c];
                        }
                        $temp['tabelaEntidades'] = $this->_tabelaEntidades;
                        if(strpos($this->_tabelaEntidades, '|') === false){
                            $temp['link_nova'] = $this->_linkAjaxFormulario . '&coluna=' . $temp['id'];
                        }
                        else{
                            $links = array();
                            $tabelas = explode('|', $this->_tabelaEntidades);
                            foreach ($tabelas as $t){
                                $info = explode('=', $t);
                                $info[1] = $this->_linkAjaxFormulario . '&coluna=' . $temp['id'] . '&multi_entidade=' . $info[1];
                                $links[] = array($info[0], $info[1]);
                            }
                            $temp['link_nova'] = $links;
                        }
                        $temp['link_fechar'] = $this->_linkAjaxFormularioFecharTarefas . '&coluna=' . $temp['id'];
                        $temp['totalizar'] = $mostrar_totalizador;
                        $temp['score_total'] = 0;
                        $temp['icone_score'] = $icone_score;
                        $temp['prefixo_score'] = $prefixo_score;
                        $temp['esconder'] = ($row['esconder'] == 'S');
                        $temp['tarefas'] = array();
                        $colunas[$temp['id']] = $temp;
                    }
                    foreach ($raias as $raia){
                        $ret['raias'][$raia['id']] = $raia;
                        $ret['raias'][$raia['id']]['colunas'] = $colunas;
                        foreach ($colunas as $c){
                            if(is_array($ret['raias'][$raia['id']]['colunas'][$c['id']]['link_nova'])){
                                $links_novos = array();
                                foreach ($ret['raias'][$raia['id']]['colunas'][$c['id']]['link_nova'] as $link_velho){
                                    $temp = $link_velho;
                                    $temp[1] .=  '&raia=' . $raia['id'];
                                    $links_novos[] = $temp;
                                }
                                $ret['raias'][$raia['id']]['colunas'][$c['id']]['link_nova'] = $links_novos;
                            }
                            else{
                                $ret['raias'][$raia['id']]['colunas'][$c['id']]['link_nova'] .= '&raia=' . $raia['id'];
                            }
                            $ret['raias'][$raia['id']]['colunas'][$c['id']]['link_fechar'] .= '&raia=' . $raia['id'];
                        }
                        $ret['raias'][$raia['id']]['num_raias'] = $num_raias;
                    }
                    
                    $sql = "
                    SELECT cruzada.raia AS raia
                    	,cruzada.coluna AS coluna
                    	,COALESCE(temp1.total, 0) AS num_tarefas
                    	,COALESCE(temp2.total, 0) AS num_abertas
                    	,COALESCE(temp3.total, 0) AS num_fechadas
                    	,COALESCE(temp4.total, 0) AS total_coluna
                    FROM (
                    	SELECT todas_raias.id AS raia
                    		,todas_colunas.id AS coluna
                    	FROM (
                    		SELECT id
                    		FROM kanboard_raia
                    		WHERE projeto = $projeto
                            AND id in (" . implode(', ', $raias_permitidas) . ")
                    		) todas_raias
                    	CROSS JOIN (
                    		SELECT id
                    		FROM kanboard_colunas
                    		WHERE projeto = $projeto
                            AND ID IN (" . implode(', ', $colunas_permitidas) . ")
                    		) todas_colunas
                    	) cruzada
                    LEFT JOIN (
                    	SELECT raia
                    		,coluna
                    		,count(*) AS total
                    	FROM kanboard_tarefas
                        WHERE status in ('A', 'F')
                        and $usuarios_permitidos
                    	GROUP BY raia
                    		,coluna
                    	) AS temp1 ON (
                    		cruzada.raia = temp1.raia
                    		AND cruzada.coluna = temp1.coluna
                    		)
                    LEFT JOIN (
                    	SELECT raia
                    		,coluna
                    		,count(*) AS total
                    	FROM kanboard_tarefas
                    	WHERE status = 'A'
                        and $usuarios_permitidos
                    	GROUP BY raia
                    		,coluna
                    	) AS temp2 ON (
                    		cruzada.raia = temp2.raia
                    		AND cruzada.coluna = temp2.coluna
                    		)
                    LEFT JOIN (
                    	SELECT raia
                    		,coluna
                    		,count(*) AS total
                    	FROM kanboard_tarefas
                    	WHERE status = 'F'
                        and $usuarios_permitidos
                    	GROUP BY raia
                    		,coluna
                    	) AS temp3 ON (
                    		cruzada.raia = temp3.raia
                    		AND cruzada.coluna = temp3.coluna
                    		)
                    LEFT JOIN (
                    	SELECT coluna
                    		,count(*) AS total
                    	FROM kanboard_tarefas
                    	WHERE status = 'A'
                        and $usuarios_permitidos
                    	GROUP BY coluna
                    	) AS temp4 ON (cruzada.coluna = temp4.coluna)
                    ";
                    $rows = query($sql);
                    if(is_array($rows) && count($rows) > 0){
                        foreach ($rows as $row){
                            $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['num_abertas'] = $row['num_abertas'];
                            $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['num_fechadas'] = $row['num_fechadas'];
                            $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['num_tarefas'] = $row['num_tarefas'];
                            
                            $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['total_coluna'] = $row['total_coluna'];
                            //'num_abertas', 'num_fechadas', 'num_tarefas'
                        }
                    }
                }
                
                $sql = "SELECT kanboard_tarefas.*
                    	,COALESCE(temp1.total, 0) as comentarios
                        ,case when temp2.total is not null then CONVERT(round((COALESCE(temp3.total, 0)*100/temp2.total), 0),char) else '' end por_subTarefas
                    FROM kanboard_tarefas
                    LEFT JOIN (
                    	SELECT tarefa, count(*) AS total
                    	FROM kanboard_comentarios
                    	WHERE tarefa IN (
                    			SELECT id
                    			FROM kanboard_tarefas
                    			WHERE coluna IN (
                    					SELECT id
                    					FROM kanboard_colunas
                    					WHERE projeto = $projeto
                    					)
                    				AND STATUS = 'A'
                    			)
                    	GROUP BY tarefa
                    	) temp1 ON (kanboard_tarefas.id = temp1.tarefa)
                    	
                    	
LEFT JOIN (
                    	SELECT tarefa, count(*) AS total
                    	FROM kanboard_sub_tarefas
                    	WHERE tarefa IN (
                    			SELECT id
                    			FROM kanboard_tarefas
                    			WHERE coluna IN (
                    					SELECT id
                    					FROM kanboard_colunas
                    					WHERE projeto = $projeto
                    					)
                    				AND STATUS = 'A'
                    			)
                    	GROUP BY tarefa
                    	) temp2 ON (kanboard_tarefas.id = temp2.tarefa)
                    	
                    	
                    	
                    	
LEFT JOIN (
                    	SELECT tarefa, count(*) AS total
                    	FROM kanboard_sub_tarefas
                    	WHERE tarefa IN (
                    			SELECT id
                    			FROM kanboard_tarefas
                    			WHERE coluna IN (
                    					SELECT id
                    					FROM kanboard_colunas
                    					WHERE projeto = $projeto
                    					)
                    				AND STATUS = 'A'
                    			)
                        and status = 'f'
                    	GROUP BY tarefa
                    	) temp3 ON (kanboard_tarefas.id = temp3.tarefa)
                    	
                    	
                    	
                    WHERE kanboard_tarefas.coluna IN (
                    		" . implode(', ', $colunas_permitidas) . "
                    		)
                        AND kanboard_tarefas.raia in (" . implode(', ', $raias_permitidas) . ")
                    	AND kanboard_tarefas.STATUS = 'A'
                        and $usuarios_permitidos
                    ORDER BY kanboard_tarefas.posicao
                    ";
                $rows = query($sql);
                $link_editar_tarefa = $this->getOpercaoClicarTarefas() === 'janela' ? getLinkAjax('editarTarefa') : (getLink() . 'ajax.editarTarefa');
                if(is_array($rows) && count($rows) > 0){
                    $campos = array('id', 'coluna', 'cor', 'posicao', 'dono', 'categoria', 'data_limite', 'etiqueta', 'responsavel', 'tags', 'raia', 'comentarios', 'descricao');
                    foreach ($rows as $row){
                        $temp = array();
                        foreach ($campos as $c){
                            $temp[$c] = $row[$c];
                        }
                        $temp['ativo'] = ($row['status'] == 'A');
                        $temp['tags'] = $this->montarArrayTags($row['tags'], $row['cor'], $dicionario_tags);
                        $temp['arrastavel'] = ($row['arrastavel'] == 'S');
                        $temp['score'] = $icone_score . $prefixo_score . floatval($row['score']);
                        //$temp['score'] = (double)filter_var($row['score'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        //$temp['arrastavel'] = false;
                        $temp['projeto'] = $projeto;
                        $temp['subTarefas'] = $row['por_subTarefas'];
                        $temp['mostrarScore'] = $mostrar_score;
                        $temp['link_editar'] = $link_editar_tarefa;
                        $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['tarefas'][] = $temp;
                        
                        $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['score_total'] += $row['score'];
                    }
                }
                
            }
        }
        return $ret;
    }
    
    protected function montarArrayTags($tags, $cor_card, $dicionario_cores){
        $ret = array();
        if(!empty($tags)){
            $temp = explode(' ', $tags);
            foreach ($temp as $tag){
                $cor = (!isset($dicionario_cores[$tag]) || empty($dicionario_cores[$tag]) || $dicionario_cores[$tag] == $cor_card) ? 'white' : $dicionario_cores[$tag];
                $ret[] = array('cor' => $cor, 'etiqueta' => $tag);
            }
        }
        return $ret;
    }
    
    protected function montarDicionarioCoresTags($projeto){
        $ret = array();
        $sql = "select tag, cor from kanboard_tags where projeto = $projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['tag']] = $row['cor'];
            }
        }
        return $ret;
    }
    
    protected function moverTarefa($dados){
        $coluna_destino = $dados['dst_column_id'];
        $coluna_origem = $dados['src_column_id'];
        $id_tarefa = $dados['task_id'];
        $raia = $dados['swimlane_id'];
        if($this->checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia, $id_tarefa)){
            $pos = $dados['position'];
            
            //$pos = kanboard_logica::getUltimaPosicaoColunaDestino($coluna_destino);
            $this->gerarLogMovimentacao($id_tarefa, $coluna_destino, $raia);
            
            $this->rebaixarTarefas($coluna_origem, $id_tarefa, $raia);
            $this->elevarTarefas($coluna_destino, $id_tarefa, $pos, $raia);
            $this->moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia);
            $this->reordenarTarefas($coluna_origem, $raia);
            $this->reordenarTarefas($coluna_destino, $raia);
            
            $this->gerarLog('tarefa', 'mover', $id_tarefa, "para a coluna/raia $coluna_destino/$raia");
        }
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        $ret = false;
        
        $permissao_coluna =  kanboard_permissoes::checarPermissaoMovimentacao($coluna_origem, $coluna_destino, 'coluna', getUsuario());
        
        $raia_origem = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
        $permissao_raia = kanboard_permissoes::checarPermissaoMovimentacao($raia_origem, $raia_destino, 'raia', getUsuario());
        
        $permissao_sentido = kanboard_permissoes::checarPermissaoSentidoMovimentacao($coluna_origem, $coluna_destino);
        
        $ret = $permissao_coluna && $permissao_raia && $permissao_sentido;
        
        return $ret;
    }

    protected function gerarLogMovimentacao($tarefa, $coluna_nova, $raia_nova){
        $sql = "select kanboard_colunas.projeto, kanboard_tarefas.coluna, kanboard_tarefas.raia from kanboard_tarefas join kanboard_colunas on (kanboard_tarefas.coluna = kanboard_colunas.id) where kanboard_tarefas.id = $tarefa";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $coluna_antiga = $rows[0]['coluna'];
            $raia_antiga = $rows[0]['raia'];
            $projeto = $rows[0]['projeto'];
            
            $param = array(
                'tarefa' => $tarefa,
                'coluna_origem' => $coluna_antiga,
                'raia_origem' => $raia_antiga,
                'projeto_origem' => $projeto,
                'coluna_destino' => $coluna_nova,
                'raia_destino' => $raia_nova,
                'projeto_destino' => $projeto
            );
            
            $sql = montaSQL($param, 'kanboard_movimentacoes');
            query($sql);
        }
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_tarefas set coluna = $coluna_destino, posicao = $pos, raia = $raia where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', $coluna_destino);
        
        //cria o log
        $this->gerarLog('tarefa', 'mover', $id_tarefa, "raia $raia | coluna $coluna_destino | posicao $pos");
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_tarefas WHERE coluna = $coluna and raia = $raia";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_tarefas
                SET posicao = posicao + 1
                WHERE
                    coluna = $coluna
                    and raia = $raia
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_tarefas
                SET posicao = posicao - 1
                WHERE
                    coluna = $coluna
                    and raia = (select raia from kanboard_tarefas where id = $id_tarefa)
                    and posicao > (select posicao from kanboard_tarefas where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_tarefas
                SET posicao = (@rownum := 1 + @rownum)
                WHERE 
                    0 = (@rownum:=0)
                    and coluna = $coluna
                    and raia = $raia
                ORDER BY posicao;";
        query($sql);
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        //cria a tarefa
        $pos = $this->getUltimaPosicaoColunaDestino($coluna, $raia);
        $sql = "insert into kanboard_tarefas (id, raia, coluna, etiqueta, posicao, cor, dono, categoria, data_limite, responsavel, tags, arrastavel, status, descricao)
                values (null, $raia, $coluna, '$etiqueta', $pos, 'yellow', null, null, null, null, null, 'S', 'A', null)";
        query($sql);
        
        //atualiza no banco a ultima vez q o projeto foi modificado
        $this->atualizarProjeto('', $coluna);
        
        //cria o log da criação de tarefas
        $sql = "select id from kanboard_tarefas where raia = $raia and coluna = $coluna and etiqueta = '$etiqueta' and posicao = $pos";
        $rows = query($sql);
        $id = '';
        if(is_array($rows) && count($rows) > 0){
            $id = $rows[0]['id'];
            $this->gerarLog('tarefa', 'criar', $id);
        }
        return $id;
    }
    
    protected function getTabelaEntidades(){
        $ret = '';
        $sql = "select tabela_entidades from kanboard_projetos where id = {$this->_projeto}";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['tabela_entidades'];
        }
        return $ret;
    }
    
    protected function getIdEntidade($id){
        $ret = '';
        if(!empty($id)){
            $sql = "select id_entidade from kanboard_entidades where tarefa = '$id'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0]['id_entidade'];
            }
        }
        return $ret;
    }
    
    protected function editarTarefa(){
        addPortalJS('plugin', 'kanboard/app.min.js', 'I');
        addPortalJS('plugin', 'kanboard/vendor.min.js', 'I');
        addPortalJS('plugin', 'kanboard/dropdown.js', 'I');
        addPortalJS('plugin', 'kanboard/comment-highlight.js', 'I');
        
        
        
        addPortalCSS('plugin', 'kanboard/css/vendor.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/print.min.css', 'I');
        addPortalCSS('plugin', 'kanboard/css/app.min.css', 'I');
        $ret = '';
        $tarefa = $_GET['tarefa'];
        $modal = new kanboard_modal_tarefa($tarefa);
        $ret .= $modal;
        return $ret;
    }
    
    protected function getTabelaEntidadesFromTarefa($id_tarefa = ''){
        $ret = '';
        if(!empty($id_tarefa)){
            $sql = "select tabela_entidade from kanboard_entidades where tarefa = '$id_tarefa'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0]['tabela_entidade'];
            }
        }
        return $ret;
    }

    protected function getTarefaByEntidade($id, $tabela) {
        $ret = '';
        if(!empty($id) && !empty($tabela)){
            $sql = "SELECT tarefa FROM kanboard_entidades WHERE tabela_entidade = '$tabela' and id_entidade = '$id' AND tarefa IN (SELECT id FROM kanboard_tarefas WHERE coluna IN (SELECT id FROM kanboard_colunas WHERE projeto = {$this->_projeto}))";
            $rows = query($sql);
    
            if(is_array($rows) && count($rows) > 0) {
                $ret = $rows[0]['tarefa'];
            }
        }
        return $ret;
    }
    
    protected function getListaUsuariosByFiltro($filtro){
        $ret = '';
        if($filtro == 'livre'){
            $ret = "1 = 1";
        }
        elseif($filtro == 'pessoal'){
            $ret = "(kanboard_tarefas.responsavel in (select id from sys001 where user = '" . getUsuario() . "') or kanboard_tarefas.responsavel is null)";
        }
        elseif($filtro == 'hierarquia'){
            $grupos_pendentes = array();
            $grupos_saida = array();
            $sql = "select distinct grupo from kanboard_membros where usuario in (select id from sys001 where user = '" . getUsuario() . "')";
            $rows = query($sql);
            foreach ($rows as $row){
                $grupos_pendentes[] = $row['grupo'];
                $grupos_saida[] = $row['grupo'];
            }
            
            while(count($grupos_pendentes) > 0){
                $grupo_atual = array_shift($grupos_pendentes);
                $sql = "select subordinado from kanboard_hierarquia where superior = $grupo_atual and subordinado not in (" . implode(', ', $grupos_saida) . ")";
                if(count($grupos_pendentes) > 0){
                    $sql .= " and subordinado not in (" . implode(', ', $grupos_pendentes) . ")";
                }
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    foreach ($rows as $row){
                        $grupos_pendentes[] = $row['subordinado'];
                        $grupos_saida[] = $row['subordinado'];
                    }
                }
            }
            $ret = "(kanboard_tarefas.responsavel in (select distinct usuario from kanboard_membros where grupo in (" . implode(', ', $grupos_saida) . ")) or kanboard_tarefas.responsavel is null)";
        }
        return $ret;
    }
    
    static function getListaUsuarios($tarefa = ''){
        $ret = array();
        if($tarefa === ''){
            $tarefa = $_GET['tarefa'] ?? '';
        }
        
        if($tarefa === ''){
            $coluna = $_GET['coluna'];
            $raia = $_GET['raia'];
            $projeto = kanboard_cabecalho_coluna::getCampoColuna($coluna, 'projeto');
            $usuarios_permitidos = kanboard_permissoes::getListaPossiveisUsuarios($tarefa, $projeto, $coluna, $raia);
        }
        else{
            $usuarios_permitidos = kanboard_permissoes::getListaPossiveisUsuarios($tarefa);
        }
        
        $sql = "select id, nome from sys001 where id in (" . implode(', ', $usuarios_permitidos) . ")";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret[] = array('0', '');
            foreach ($rows as $row){
                $temp = array(
                    $row['id'] , $row['nome']
                );
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    static function getListaCores(){
        $ret = array(
            array('yellow', 'Amarelo'),
            array('blue', 'Azul'),
            array('green', 'Verde'),
            array('purple', 'Roxo'),
            array('red', 'Vermelho'),
            array('orange', 'Laranja'),
            array('grey', 'Cinza'),
            array('brown', 'Marrom'),
            array('deep_orange', 'Laranja escuro'),
            array('dark_grey', 'Cinza escuro'),
            array('pink', 'Rosa'),
            array('teal', 'Turquesa'),
            array('cyan', 'Azul intenso'),
            array('lime', 'Verde limão'),
            array('light_green', 'Verde claro'),
            array('amber', 'Âmbar'),
        );
        return $ret;
    }
    
    protected function getTarefaFromSubTarefa($subTarefa){
        $ret = '';
        $sql = "select tarefa from kanboard_sub_tarefas where id = $subTarefa";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['tarefa'];
        }
        return $ret;
    }
}