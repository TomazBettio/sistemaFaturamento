<?php
class kanboard_formularios{
    static function formularioComentario(){
        $ret = '    <div class="page-header">
                        <h2>Adicionar um comentário</h2>
<!--
                        <ul>
                            <li>
                                <a href="/kanboard/?controller=CommentMailController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=\'\' >
                                    <i class="fa fa-paper-plane fa-fw js-modal-medium" aria-hidden="true"></i>
                                    Enviar por e-mail
                                </a> 
                            </li>
                        </ul>-->
                    </div>
                    <form method="post" action="{link}" autocomplete="off">
                        <div class="js-text-editor" data-params=\'{"name":"comment","css":"","required":true,"tabindex":"-1","labelPreview":"Pr\u00e9-visualizar","previewUrl":"\/kanboard\/?controller=TaskAjaxController&action=preview","labelWrite":"Escrever","labelTitle":"T\u00edtulo","placeholder":"Escreva seu texto em Markdown","ariaLabel":"Novo coment\u00e1rio","autofocus":true,"suggestOptions":{"triggers":{"#":"\/kanboard\/?controller=TaskAjaxController&action=suggest&search=SEARCH_TERM"}}}\'>
                            <script type="text/template"></script>
                        </div>
                        <div class="js-submit-buttons" data-params=\'{"submitLabel":"Salvar","orLabel":"ou","cancelLabel":"Cancelar","color":"blue","tabindex":null,"disabled":false}\'></div>
                    </form>
';
        $link = getLinkAjax('salvarComentario') . '&tarefa=' . $_GET['tarefa'];
        $ret = str_replace('{link}', $link, $ret);
        return $ret;
    }
    
    static function formularioNovaTarefa($linkAjaxSalvar, $coluna, $raia, $entidade = ''){
        /*
        $ret = '<div class="page-header">
    <h2>teasdddd &gt; New task</h2>
</div>
<form method="post" action="' . $linkAjaxSalvar . '&coluna=' . $coluna . '&raia=' . $raia . '" autocomplete="off">
    <div class="task-form-container">
        <div class="task-form-main-column">
            <label for="form-title" class="ui-helper-hidden-accessible">Title</label><input type="text" name="etiqueta" id="form-title"  class="" autofocus required tabindex="1" placeholder="Title"><span class="form-required">*</span>            <div class="js-text-editor" data-params=' . "'" . '{"name":"description","css":"","required":false,"tabindex":2,"labelPreview":"Preview","previewUrl":"\/kanboard\/?controller=TaskAjaxController&action=preview","labelWrite":"Write","labelTitle":"Title","placeholder":"Write your text in Markdown","ariaLabel":"Description","autofocus":false,"suggestOptions":{"triggers":{"#":"\/kanboard\/?controller=TaskAjaxController&action=suggest&search=SEARCH_TERM","@":"\/kanboard\/?controller=UserAjaxController&action=mention&project_id=1&search=SEARCH_TERM"}}}' . "'" . '><script type="text/template"></script></div>                        <label for="form-tags[]" >Tags</label><input type="hidden" name="tags[]" value=""><select name="tags[]" id="form-tags" class="tag-autocomplete" multiple tabindex="3"></select>
                    </div>
                
        <div class="task-form-secondary-column">
            <label for="form-color_id" >Color</label>
                <label for="form-color_id" >Cor</label><select name="color_id" id="form-color_id" class="color-picker" tabindex="4"><option value="yellow">Amarelo</option><option value="blue">Azul</option><option value="green">Verde</option><option value="purple">Roxo</option><option value="red">Vermelho</option><option value="orange">Laranja</option><option value="grey" selected="selected">Cinza</option><option value="brown">Marrom</option><option value="deep_orange">Laranja escuro</option><option value="dark_grey">Cinza escuro</option><option value="pink">Rosa</option><option value="teal">Turquesa</option><option value="cyan">Azul intenso</option><option value="lime">Verde limão</option><option value="light_green">Verde claro</option><option value="amber">Âmbar</option></select>
            <label for="form-owner_id" >Assignee</label><select name="owner_id" id="form-owner_id" class="" tabindex="5"><option value="0">Unassigned</option><option value="1">admin</option></select>&nbsp;<small><a href="#" class="assign-me" data-target-id="form-owner_id" data-current-id="1" title="Assign to me" aria-label="Assign to me">Me</a></small>                                    <label for="form-column_id" >Column</label><select name="column_id" id="form-column_id" class="" tabindex="8"><option value="1">Backlog</option><option value="2">Ready</option><option value="3">Work in progress</option><option value="4" selected="selected">Done</option></select>            <label for="form-priority" >Priority</label><select name="priority" id="form-priority" class="" tabindex="9"><option value="0" selected="selected">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option></select>
        </div>
                
        <div class="task-form-secondary-column">
            <label for="form-date_due" >Due Date</label><input type="text" name="date_due" id="form-date_due" value="" class="form-datetime" placeholder="11/18/2022 13:49" tabindex="10">            <label for="form-date_started" >Start Date</label><input type="text" name="date_started" id="form-date_started" value="" class="form-datetime" placeholder="11/18/2022 13:49" tabindex="11">            <label for="form-time_estimated" >Original estimate</label><input type="text" name="time_estimated" id="form-time_estimated"  class=" form-numeric" tabindex="12"> hours            <label for="form-time_spent" >Time spent</label><input type="text" name="time_spent" id="form-time_spent"  class=" form-numeric" tabindex="13"> hours            <label for="form-score" >Complexity</label><input type="number" name="score" id="form-score"  class="" tabindex="14">            <label for="form-reference" >Reference</label><input type="text" name="reference" id="form-reference"  class="form-input-small" tabindex="15">
                    </div>
                
        <div class="task-form-bottom">
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Save","orLabel":"or","cancelLabel":"cancel","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>        </div>
    </div>
</form>';
*/
        
        $ret = '<div class="page-header">
    <h2>Novo Card</h2>
</div>
<form method="post" action="' . $linkAjaxSalvar . '&coluna=' . $coluna . '&raia=' . $raia . '" autocomplete="off">
    <div class="task-form-container">
        <div class="task-form-main-column">
            ' . kanboard_formularios::testeFormularioCad($coluna, $entidade) . '
        </div>
                
        <div class="task-form-bottom">
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Salvar","orLabel":"ou","cancelLabel":"Cancelar","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>
        </div>
    </div>
</form>';
        
        
        
        return $ret;
    }
    
    static function formularioTarefaSemEntidade($linkAjaxSalvar, $coluna, $raia){
        $ret = '<div class="page-header">
    <h2>teasdddd &gt; New task</h2>
</div>
<form method="post" action="' . $linkAjaxSalvar . '&coluna=' . $coluna . '&raia=' . $raia . '" autocomplete="off">
    <div class="task-form-container">
        <div class="task-form-main-column">
            <label for="form-title" class="ui-helper-hidden-accessible">Title</label><input type="text" name="etiqueta" id="form-title"  class="" autofocus required placeholder="Title"><span class="form-required">*</span>
        </div>
        <div class="task-form-bottom">
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Salvar","orLabel":"ou","cancelLabel":"Cancelar","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>
        </div>
    </div>
</form>';
        
        return $ret;
    }
    
    static function testeFormularioCad($coluna, $entidade){
        $ret = '';
        if($entidade == ''){
            $sql = "select tabela_entidades from kanboard_projetos where id in (select projeto from kanboard_colunas where id = $coluna)";
            $rows = query($sql);
            $tabela = $rows[0]['tabela_entidades'];
        }
        else{
            $tabela = $entidade;
        }
        if(!empty($entidade)){
            $ret = '<label for="form-title" class="ui-helper-hidden-accessible">Title</label><input type="text" name="etiqueta" id="form-title"  class="" autofocus required placeholder="Title"><span class="form-required">*</span>';
            $cad = new cad01($tabela);
        }
        else{
            $cad = new cad01('kanboard_tarefas');
        }
        $ret .= $cad->montarFormulario();
        
        return $ret;
    }
    
    static function formularioFecharTarefas($raia, $coluna, $link){
        $ret = '<div class="page-header">
    <h2>Você realmente deseja finalizar todas as tarefas desta coluna?</h2>
</div>
<form method="post" action="' . $link . '">
    <input type="hidden" name="coluna" id="coluna" value="' . $coluna . '"/>    <input type="hidden" name="raia" id="raia" value="' . $raia . '"/>
    <p class="alert">1 tarefa(s) da coluna &quot;Backlog&quot; e da raia &quot;Default swimlane&quot; serão finalizadas.</p>

    <div class="js-submit-buttons" data-params=\'{"submitLabel":"Sim","orLabel":"ou","cancelLabel":"Cancelar","color":"red","tabindex":null,"disabled":false}\'></div></form>
';
        return $ret;
    }
    
    static function formularioExcluirTarefa($param, $param_ajax){
        $ret = '<div class="page-header">
    <h2>' . $param['titulo'] . '</h2>
</div>
    <div class="confirm">
    <p class="alert alert-info">
        ' . $param['corpo'] . '
    </p>
<div class="js-confirm-buttons" data-params=\'' . json_encode($param_ajax, JSON_HEX_APOS) . '\'>'
    //'<div class="js-confirm-buttons" data-params="{&quot;url&quot;:&quot;' . $link_excluir . '&quot;,&quot;submitLabel&quot;:&quot;Yes&quot;,&quot;orLabel&quot;:&quot;or&quot;,&quot;cancelLabel&quot;:&quot;cancel&quot;,&quot;tabindex&quot;:null}">'
        .'</div>
</div>';
        
        return $ret;
    }
    
    static function formularioSubTarefas($link_salvar, $cabecalho = true, $tarefa, $tag_form = true){
        $ret = '';
        if($cabecalho){
            $ret .= '<div class="page-header">
                        <h2>Adicionar uma subtarefa</h2>
                    </div>';
        }
        
        

$ret .= '        <label for="form-title" >Título</label>
                <textarea name="title" id="form-title" class="" tabindex="1" required autofocus></textarea>
                <p class="form-help">Escreva uma subtarefa por linha.</p>
                <label for="form-user_id" >Designação</label>
                <select name="user_id" id="form-user_id" class="" tabindex="2">{opcoes}</select>
                &nbsp;
                <small>
                    ' . kanboard_formularios::montarBotaoAtribuirSubTarefaEu() . '
                </small>    
                <div class="js-submit-buttons" data-params=\'{"submitLabel":"Salvar","orLabel":"ou","cancelLabel":"Cancelar","color":"blue","tabindex":null,"disabled":false}\'></div>
            ';
        if($tag_form){
            $ret = '<form method="post" id="formSubTarefas" action="' . $link_salvar . '" autocomplete="off">' . $ret . '</form>';
        }
        //<!–- <label for="form-time_estimated" >Estimativa original</label><input type="text" name="time_estimated" id="form-time_estimated"  class=" form-numeric" tabindex="3"> horas -–>
        //<option value="0">Não Atribuída</option><option value="1" selected="selected">admin</option>
        $opcoes = kanboard::getListaUsuarios($tarefa);
        $op_final = '';
        $id_usuario_atual = getIdUsuario(getUsuario());
        foreach ($opcoes as $op){
            if($op[0] == $id_usuario_atual){
                $op_final .= "<option value=\"{$op[0]}\" selected>{$op[1]}</option>";
            }
            else{
                $op_final .= "<option value=\"{$op[0]}\">{$op[1]}</option>";
            }
        }
        $ret = str_replace('{opcoes}', $op_final, $ret);
        
        //log::gravaLog('190123', $ret);
        
        return $ret;
    }
    
    static function montarBotaoAtribuirSubTarefaEu(){
        $ret = formbase01::formBotao(array('cor' => 'success', 'onclick' => 'atribuirUsuarioSubTarefa();', 'texto' => 'Eu'));
        return $ret;
    }
    
    static function formularioBtEditarTarefa($tarefa){
        $linkAjaxSalvar = getLinkAjax('salvarBtEditarTarefa');
        $tarefa = base64_encode($tarefa);
        $cad = new cad01('kanboard_tarefas');     
        log::gravaLog('040123', '' . $cad->editar($tarefa));
        $ret = '<div class="page-header">
</div>
<form method="post" action="' . $linkAjaxSalvar . '&tarefa=' . $tarefa . '" autocomplete="off">
    <div class="task-form-container">
        <div class="task-form-main-column">
            ' . $cad->editar($tarefa) . '
        </div>
                
        <div class="task-form-bottom">
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Salvar","orLabel":"ou","cancelLabel":"Cancelar","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>
        </div>
    </div>
</form>';
        
        return $ret;
    }
    
    static function formularioFecharTarefasUnitaria($link, $tarefa){
        $ret = '<div class="page-header">
    <h2>Finalizar uma tarefa</h2>
</div>
<form method="post" action="' . $link . '">
    <p class="alert">Você realmente deseja finalizar a tarefa "' . kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'etiqueta') .'"?</p>
        
    <div class="js-submit-buttons" data-params=\'{"submitLabel":"Sim","orLabel":"ou","cancelLabel":"cancelar","color":"red","tabindex":null,"disabled":false}\'></div></form>
';
        return $ret;
    }
    
    static function gerarCampoTags(){
        $ret = '';
        $projeto = $_GET['projeto'];
        $tarefa = $_GET['tarefa'];
        $sql = "select tags from kanboard_tarefas where id = $tarefa";
        $dados = array();
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados = explode(',', $rows[0]['tags']);
        }
        $sql = "select * from kanboard_tags where projeto = $projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret .= '
<label for="campo' . $row['id'] . '">' . $row['etiqueta'] . '</label>
<input type="checkbox" id="campo' . $row['id'] . '" name="tags[' . $row['id'] . ']" ' . (isset($dados[$row['id']]) ? 'checked' : '') . '><br>';
            }
        }
        return $ret;
    }
    
    static function formularioEditarColunaRaia($etiqueta, $tipo, $link){
        $ret = '    <div class="page-header">
                        <h2>Editar ' . $tipo . '</h2>
                    </div>
                    <form method="post" action="{link}" autocomplete="off">
                         <label for="form-title" class="ui-helper-hidden-accessible">Title</label><input type="text" name="etiqueta" id="form-title"  class="" autofocus required value="' . $etiqueta . '"><span class="form-required">*</span>

                        <div class="js-submit-buttons" data-params=\'{"submitLabel":"Salvar","orLabel":"ou","cancelLabel":"Cancelar","color":"blue","tabindex":null,"disabled":false}\'></div>
                    </form>
';
        $ret = str_replace('{link}', $link, $ret);
        return $ret;
    }
}