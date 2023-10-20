<?php
class kanboard_modal_tarefa{
    protected $_tarefa;
    protected $_titulo;
    protected $_coluna;
    protected $_conteudo;
    
    function __construct($tarefa, $param = array()){
        $this->_tarefa = $tarefa;
        $this->_titulo = $param['titulo'] ?? kanboard_tarefa_unitaria::getCampoTarefa($this->_tarefa, 'etiqueta');
        $this->_coluna = $param['coluna'] ?? kanboard_cabecalho_coluna::getTituloColuna(kanboard_tarefa_unitaria::getCampoTarefa($this->_tarefa, 'coluna'));
        $this->_conteudo = $param['conteudo'] ?? kanboard_tarefa_unitaria::getCampoTarefa($this->_tarefa, 'descricao');
    }
    
    function __toString(){
        $ret = '
<div class="taskDetails-main taskBorderColor--red">
    <div>
        <div tabindex="0" class="taskDetails-focusPlaceholder"></div>
        <h2 class="taskDetails-name">
            ' . $this->_titulo . '
        </h2>
    </div>
    <div class="taskDetails-highlights">
        <div class="taskDetails-positionTextWrapper taskDetails-highlights-property">
            <span tabindex="0" class="taskDetails-positionText taskDetails-editableProperty truncate">
                <label class="taskDetails-propertyLabel taskDetails-descriptionLabel">Coluna:</label> ' . $this->_coluna . '
            </span>
        </div>
        <div class="taskDetails-highlights-property taskDetails-highlights-icons">
            <i class="taskDetails-highlights-subTasksIcon icons-task-subTasks taskDetails-highlights-icon" title="Show subtasks"></i>
            <i class="taskDetails-highlights-commentsIcon icons-task-comments taskDetails-highlights-icon" title="Show comments"></i>
            <i class="taskDetails-highlights-watchIcon icons-eye-gray taskDetails-highlights-icon" title="You are watching this task"></i>
        </div>
    </div>
    <!-- <div class="taskDetails-properties">
        <div class="taskDetails-color taskDetails-property">
            <label class="taskDetails-propertyLabel">Color</label>
            <div class="taskDetails-propertyValue">
                <button class="taskDetails-selectedColor taskDetails-editableProperty">
                    <i class="taskDetails-colorIcon taskDetails-colorIcon--red"></i>
                    <span class="taskDetails-selectedColorText">Gerente de atendimento</span>
                </button>
            </div>
        </div>
        <div class="taskDetails-property">
            <label class="taskDetails-propertyLabel">
                Members
            </label>
            <div class="taskDetails-propertyValue">
                <div class="taskDetails-members">
                    <img src="/avatarimg/8OTh5H/i4GX1esFbc-28.jpg" class="task-avatar task-avatar--red task-avatar--small taskDetails-members-avatar taskDetails-focusableProperty" tabindex="0">
                    <img src="/avatar/initials/MA/s/28/c/aa5d00/bg/ffffff" class="task-avatar task-avatar--red task-avatar--small taskDetails-members-avatar taskDetails-focusableProperty" tabindex="0">
                    <button class="taskDetails-addMemberButton taskDetails-addButton icons--button icons-plus icons-gray taskDetails-focusableProperty"></button>
                </div>
            </div>
        </div>
    </div> --> 
    ' . $this->montarBlocoDescricao() . '
    <form method="post" action="' . getLinkAjax('salvarModalTarefaSubTarefa')  . "&tarefa={$this->_tarefa}" . '" autocomplete="off">
        <div id="taskDetails-subTasksSection" class="taskDetails-section" style = "border: 5px; border-style: solid none solid none;">
            <table style="width: 100%">
                <colgroup>
                   <col span="1" style="width: 50%;">
                   <col span="1" style="width: 50%;">
                </colgroup>
                <tbody>
                    <tr>
                        <td><div class="taskDetails-sectionHeader" style = "display: inline; text-align: left; width: 50%;">
                            <div class="taskDetails-iconWrapper" style = "display: inline;">
                                <i class="fa fa-list"></i>
                            </div>
                            <h3 class="taskDetails-sectionTitle" style = "display: inline;">
                                Subtarefas
                            </h3>
                            <span class="taskDetails-sectionHeaderSecondaryText" style = "display: inline;">
                                ' . kanboard_tarefa_unitaria::getNumeroSubTarefasFinalizadas($this->_tarefa) . ' / ' . kanboard_tarefa_unitaria::getNumeroSubTarefas($this->_tarefa) . '
                            </span>
                        </div></td>
                        <td align ="right">
                            <!-- <div style = "display: inline; text-align: right; width: 50%;">
                                <button class="taskDetails-subTasksSectionMore taskDetails-moreIcon icons--button icons-more icons-gray" style = "display: inline;"></button> 
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i> -->
                            <!-- </div> -->
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="taskDetails-sectionContent" style = "padding-left: 35px;">
                ' .  $this->criarListaSubTarefas() . '
                <div>
                    ' . kanboard_formularios::formularioSubTarefas(getLinkAjax('salvarModalTarefaSubTarefa') . "&tarefa={$this->_tarefa}", false, $this->_tarefa, false) .'
                </div>
            </div>
        </div>
        <div id="taskDetails-commentsSection" class="taskDetails-section">
            <div class="taskDetails-sectionHeader">
                <div class="taskDetails-iconWrapper">
                    <i class="taskDetails-sectionHeaderIcon icons-task-comments"></i>
                </div>
                <h3 class="taskDetails-sectionTitle">
                    Comentários
                </h3>
                <!-- <span class="taskDetails-sectionHeaderSecondaryText">
                    2
                </span>
                <button class="taskDetails-commentsSectionMore taskDetails-moreIcon icons--button icons-more icons-gray"></button> -->
            </div>
            ' . $this->criarBlocoComentarios() . '
        </div>
    </form>
</div>';
        
        log::gravaLog('190123', $ret);

        return $ret;
    }
    
    protected function criarBlocoComentarios(){
        $ret = '<div class="taskDetails-sectionContent">
                    <ul class="taskDetails-comments">
                        <li data-commentid="uUqUg7z7" class="taskDetails-comment">
                            <div class="taskDetails-commentHeader">
                                <div class="taskDetails-iconWrapper">
                                    <img src="/avatarimg/8OTh5H/i4GX1esFbc-28.jpg" class="taskDetails-commentAuthorAvatar task-avatar task-avatar--red">
                                </div>
                                <div class="taskDetails-commentInfo">
                                    <span class="taskDetails-commentAuthor">
                                        Lucineia Schossler
                                    </span>
                                    <span class="taskDetails-commentTimestamps">
                                        7 December 2022 14:31
                                    </span>
                                </div>
                                <div class="taskDetails-commentHeaderButtons">
                                    <button class="taskDetails-commentReactButton icons--button icons-react"></button>
                                    <button class="taskDetails-moreIcon taskDetails-commentMore icons-more icons-gray icons--button"></button>
                                </div>
                            </div>
                            <div class="taskDetails-commentContent">
                                <div class="taskDetails-commentText markdown">
                                    <p>Reunião agendada para dia 12/12 ás 11hs</p>
                                </div>
                            </div>
                        </li>
                        <li data-commentid="t1yf33gq" class="taskDetails-comment">
                            <div class="taskDetails-commentHeader">
                                <div class="taskDetails-iconWrapper">
                                    <img src="/avatarimg/8OTh5H/i4GX1esFbc-28.jpg" class="taskDetails-commentAuthorAvatar task-avatar task-avatar--red">
                                </div>
                                <div class="taskDetails-commentInfo">
                                    <span class="taskDetails-commentAuthor">
                                        Lucineia Schossler
                                    </span>
                                    <span class="taskDetails-commentTimestamps">
                                        12 December 2022 11:52
                                    </span>
                                </div>
                                <div class="taskDetails-commentHeaderButtons">
                                    <button class="taskDetails-commentReactButton icons--button icons-react"></button>
                                    <button class="taskDetails-moreIcon taskDetails-commentMore icons-more icons-gray icons--button"></button>
                                </div>
                            </div>
                            <div class="taskDetails-commentContent">
                                <div class="taskDetails-commentText markdown">
                                    <p>Não saiu reunião, será remarcada, aguardado retorno da Juliane.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="taskDetails-addCommentRow">
                        <div class="taskDetails-iconWrapper">
                            <img src="/avatar/initials/MA/s/28/c/aa5d00/bg/ffffff" class="taskDetails-commentAuthorAvatar task-avatar task-avatar--red taskDetails-addCommentAvatar">
                        </div>
                        <button class="taskDetails-addCommentButton">
                            Add comment...
                        </button>
                    </div>
        </div>';
        $comentarios = new kanboard_comentarios(array('tarefa' => $this->_tarefa, 'titulo' => ''));
        $ret = '<div style = "padding-left: 35px;">';
        $ret .= $comentarios->montarBlocoComentariosExistentes();
        $ret .= $comentarios->renderizarFormComentario(getLinkAjax('salvarModalTarefaComentario'), false);
        $ret .= '</div>';

        return $ret;
    }
    
    protected function criarListaSubTarefas(){
        $ret = $this->criarTabelaSubTarefas();
        return $ret;
    }
    
    protected function getSubTarefas(){
        $ret = array();
        $sql = "select kanboard_sub_tarefas.*, temp1.nome, temp1.icone, sys001.nome as nome_usuario from kanboard_sub_tarefas join (
SELECT 'a' as id, 'Subtarefa não iniciada' as nome, 'fa fa-square-o fa-fw' as icone UNION SELECT 'e' as id, 'Subtarefa atualmente em progresso' as nome, 'fa fa-gears fa-fw' as icone UNION SELECT 'f' as id, 'Subtarefa finalizada' as nome, 'fa fa-check-square-o fa-fw' as icone) temp1 on (kanboard_sub_tarefas.status = temp1.id)
left join sys001 on (kanboard_sub_tarefas.usuario = sys001.id) where tarefa = {$this->_tarefa}";
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
        return $ret;
    }
    
    protected function criarTabelaSubTarefas(){
        $ret = '<div><table class="table-small">
                    <tr>
                        <th class="column-70">Subtarefa</th>
                        <th>Designação</th>
                    </tr>';
        $sub_tarefas = $this->getSubTarefas();
        foreach ($sub_tarefas as $s){
            $ret .= "<tr>
                        <td>{$s['tarefa']}</td>
                        <td>{$s['usuario']}</td>
                    </tr>";
        }
        $ret .= '</table></div>';
        return $ret;
    }
    
    protected function montarBlocoDescricao(){
        $ret = '';
        if(!empty($this->_conteudo)){
            $ret = '<div class="taskDetails-descriptionSection">
        <label class="taskDetails-propertyLabel taskDetails-descriptionLabel">
            Descrição:
        </label>
        <div class="taskDetails-descriptionWrapper">
            <div tabindex="0" class="taskDetails-focusPlaceholder"></div>
            <div class="taskDetails-description taskDetails-editableProperty markdown">
                ' . $this->_conteudo . '
            </div>
        </div>
    </div>';
        }
        return $ret;
    }
}