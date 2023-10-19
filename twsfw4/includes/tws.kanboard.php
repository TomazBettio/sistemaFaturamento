<?php
class kanboard{
    var $funcoes_publicas = array(
        'index' 	=> true,
        'ajax'      => true
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
    
    function __construct($projeto, $param = array()){
        $this->_projeto = $projeto;
        
        $this->_linkAjaxFormulario = $param['linkFormulario'] ?? getLinkAjax('formulario');
        $this->_linkAjaxMover = $param['linkMover'] ?? getLinkAjax('mover');
        $this->_linkAjaxSalvar = $param['linkSalvar'] ?? getLinkAjax('salvar');
        $this->_linkReload = $param['linkReload'] ?? (getLink() . 'index');
    }
    
    public function ajax(){
        $GLOBALS['tws_pag'] = array(
            'header'   	=> false,
            'html'		=> false,
            'menu'   	=> false,
            'content' 	=> false,
            'footer'   	=> false,
        );
        $ret = '';
        $op = getOperacao();
        if($op == 'mover'){
            $ret = $this->moverAjax();
        }
        if($op == 'salvar'){
            $this->salvarAjax();
        }
        if($op == 'formulario'){
            $ret = $this->formularioAjax();
        }
        if($op == 'check'){
            $ret = $this->checarAjax();
        }
        return $ret;
    }
    
    protected function checarAjax(){
        $ret = '';
        $projeto = $_GET['projeto'];
        $sql = "select modificado from kanboard_projetos where id = $projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $estampa_ajax = $_GET['estampa_intranet'];
            $estampa_banco = $rows[0]['modificado'];
            if(intval($estampa_ajax) < $estampa_banco){
                http_response_code(200);
                $ret = $this->criarTabelaTarefas();
            }
            else{
                http_response_code(304);
                //caso não tenha ocorrido nenhuma modificação
            }
        }
        return $ret;
    }
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        $form = '<div class="page-header">
    <h2>teasdddd &gt; New task</h2>
</div>
<form method="post" action="' . $this->_linkAjaxSalvar . '&coluna=' . $coluna . '" autocomplete="off">
    <div class="task-form-container">
        <div class="task-form-main-column">
            <label for="form-title" class="ui-helper-hidden-accessible">Title</label><input type="text" name="etiqueta" id="form-title"  class="" autofocus required tabindex="1" placeholder="Title"><span class="form-required">*</span>            <div class="js-text-editor" data-params=' . "'" . '{"name":"description","css":"","required":false,"tabindex":2,"labelPreview":"Preview","previewUrl":"\/kanboard\/?controller=TaskAjaxController&action=preview","labelWrite":"Write","labelTitle":"Title","placeholder":"Write your text in Markdown","ariaLabel":"Description","autofocus":false,"suggestOptions":{"triggers":{"#":"\/kanboard\/?controller=TaskAjaxController&action=suggest&search=SEARCH_TERM","@":"\/kanboard\/?controller=UserAjaxController&action=mention&project_id=1&search=SEARCH_TERM"}}}' . "'" . '><script type="text/template"></script></div>                        <label for="form-tags[]" >Tags</label><input type="hidden" name="tags[]" value=""><select name="tags[]" id="form-tags" class="tag-autocomplete" multiple tabindex="3"></select>
                    </div>
                
        <div class="task-form-secondary-column">
            <label for="form-color_id" >Color</label><select name="color_id" id="form-color_id" class="color-picker" tabindex="4"><option value="yellow" selected="selected">Yellow</option><option value="blue">Blue</option><option value="green">Green</option><option value="purple">Purple</option><option value="red">Red</option><option value="orange">Orange</option><option value="grey">Grey</option><option value="brown">Brown</option><option value="deep_orange">Deep Orange</option><option value="dark_grey">Dark Grey</option><option value="pink">Pink</option><option value="teal">Teal</option><option value="cyan">Cyan</option><option value="lime">Lime</option><option value="light_green">Light Green</option><option value="amber">Amber</option></select>            <label for="form-owner_id" >Assignee</label><select name="owner_id" id="form-owner_id" class="" tabindex="5"><option value="0">Unassigned</option><option value="1">admin</option></select>&nbsp;<small><a href="#" class="assign-me" data-target-id="form-owner_id" data-current-id="1" title="Assign to me" aria-label="Assign to me">Me</a></small>                                    <label for="form-column_id" >Column</label><select name="column_id" id="form-column_id" class="" tabindex="8"><option value="1">Backlog</option><option value="2">Ready</option><option value="3">Work in progress</option><option value="4" selected="selected">Done</option></select>            <label for="form-priority" >Priority</label><select name="priority" id="form-priority" class="" tabindex="9"><option value="0" selected="selected">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option></select>
                    </div>
                
        <div class="task-form-secondary-column">
            <label for="form-date_due" >Due Date</label><input type="text" name="date_due" id="form-date_due" value="" class="form-datetime" placeholder="11/18/2022 13:49" tabindex="10">            <label for="form-date_started" >Start Date</label><input type="text" name="date_started" id="form-date_started" value="" class="form-datetime" placeholder="11/18/2022 13:49" tabindex="11">            <label for="form-time_estimated" >Original estimate</label><input type="text" name="time_estimated" id="form-time_estimated"  class=" form-numeric" tabindex="12"> hours            <label for="form-time_spent" >Time spent</label><input type="text" name="time_spent" id="form-time_spent"  class=" form-numeric" tabindex="13"> hours            <label for="form-score" >Complexity</label><input type="number" name="score" id="form-score"  class="" tabindex="14">            <label for="form-reference" >Reference</label><input type="text" name="reference" id="form-reference"  class="form-input-small" tabindex="15">
                    </div>
                
        <div class="task-form-bottom">
                
                
                            <label><input type="checkbox" name="another_task" class="" value="1" tabindex="16">&nbsp;Create another task</label>                <label><input type="checkbox" name="duplicate_multiple_projects" class="" value="1" tabindex="17">&nbsp;Duplicate to multiple projects</label>
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Save","orLabel":"or","cancelLabel":"cancel","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>        </div>
    </div>
</form>';
        /*
         $form = new kanboard_formulario_nova_tarefa(array(), '/intranet4/ajax.php?menu=testes.mercado_pago.ajax.teste&coluna=' . $coluna);
         $form = new kanboard_formulario_nova_tarefa(array(), '/intranet4/index.php?menu=testes.mercado_pago.index.nova_tarefa');
         $form->addCampo(array('id' => 'modulo'	    , 'campo' => 'etiqueta'	             ,'valor'=> ''     , 'etiqueta' => 'Etiqueta'	                                , 'tipo' => 'T'  , 'tamanho' => '10', 'linha' => 2, 'largura' => 6	, 'lista' => ''		                 								, 'validacao' => ''));
         */
        $ret = $form . '';
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'];
        $this->incluirTarefa($etiqueta, $coluna);
        header('X-Ajax-Redirect: ' . $this->_linkReload);
        //header('Location: ' . '/intranet4/index.php?menu=testes.mercado_pago.index');
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
    
    public function index($param_exterior = array()){
        $ret = '';
        
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'modal.css', 'I');
        addPortalCSS('', 'table_drag_and_drop.css', 'I');
        addPortalCSS('', 'board.css', 'I');
        
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
        $dados = $this->getDadosTabela($this->_projeto);
        $param = array(
            'link_salvar' => $this->_linkAjaxMover,
            'link_reload' => getLink() . 'index',
            'link_check'  => getLinkAjax('check') . '&projeto=' . $this->_projeto,
        );
        $tabela = new kanboard_tabela_tarefas($this->_projeto, $dados, $param);
        $ret .= $tabela;
        return $ret;
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
    
    protected function getDadosTabela($projeto){
        $ret = array();
        $sql = "
select kanboard_colunas.*, temp2.total as num_tarefas, temp4.total as num_abertas, temp6.total as num_fechadas
from kanboard_colunas

join (select temp1.id, count(kanboard_tarefas.id) as total from (SELECT id FROM `kanboard_colunas` WHERE projeto = $projeto) as temp1 left join kanboard_tarefas on (temp1.id = kanboard_tarefas.coluna) group by id) as temp2 on kanboard_colunas.id = temp2.id

join (select temp3.id, count(kanboard_tarefas.id) as total from (SELECT id FROM `kanboard_colunas` WHERE projeto = $projeto) as temp3 left join kanboard_tarefas on (temp3.id = kanboard_tarefas.coluna and kanboard_tarefas.ativo = 'S') group by id) as temp4 on kanboard_colunas.id = temp4.id

join (select temp5.id, count(kanboard_tarefas.id) as total from (SELECT id FROM `kanboard_colunas` WHERE projeto = $projeto) as temp5 left join kanboard_tarefas on (temp5.id = kanboard_tarefas.coluna and kanboard_tarefas.ativo = 'N') group by id) as temp6 on kanboard_colunas.id = temp6.id

where projeto = $projeto;
";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret['id'] = $projeto;
            $ret['titulo'] = $this->getNomeProjeto($projeto);
            $campos = array('id', 'projeto', 'etiqueta', 'posicao', 'limite', 'descricao', 'num_abertas', 'num_fechadas', 'num_tarefas');
            foreach ($rows as $row){
                $temp = array();
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $temp['link_nova'] = $this->_linkAjaxFormulario . '&coluna=' . $temp['id'];
                $temp['esconder'] = ($row['esconder'] == 'S');
                $temp['tarefas'] = array();
                $ret['colunas'][$temp['id']] = $temp;
            }
            
            $sql = "select * from kanboard_tarefas where coluna in (select id from kanboard_colunas where projeto = $projeto)";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $campos = array('id', 'coluna', 'cor', 'posicao', 'dono', 'categoria', 'data_limite', 'etiqueta', 'responsavel', 'tags');
                foreach ($rows as $row){
                    $temp = array();
                    foreach ($campos as $c){
                        $temp[$c] = $row[$c];
                    }
                    $temp['ativo'] = ($row['ativo'] == 'S');
                    $temp['arrastavel'] = ($row['arrastavel'] == 'S');
                    $temp['projeto'] = $projeto;
                    $ret['colunas'][$row['coluna']]['tarefas'][] = $temp;
                }
            }
        }
        return $ret;
    }
    
    protected function moverTarefa($dados){
        $coluna_destino = $dados['dst_column_id'];
        $coluna_origem = $dados['src_column_id'];
        $id_tarefa = $dados['task_id'];
        $pos = $dados['position'];
        //$pos = kanboard_logica::getUltimaPosicaoColunaDestino($coluna_destino);
        $this->rebaixarTarefas($coluna_origem, $id_tarefa);
        $this->elevarTarefas($coluna_destino, $id_tarefa, $pos);
        $this->moverTarefaBanco($id_tarefa, $coluna_destino, $pos);
        $this->reordenarTarefas($coluna_origem);
        $this->reordenarTarefas($coluna_destino);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos){
        $sql = "update kanboard_tarefas set coluna = $coluna_destino, posicao = $pos where id = $id_tarefa";
        query($sql);
        $sql = "update kanboard_projetos set modificado = '" . time() . "' where id in (select projeto from kanboard_colunas where id = $coluna_destino)";
        query($sql);
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_tarefas WHERE coluna = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova){
        $sql = "UPDATE kanboard_tarefas
                SET posicao = posicao + 1
                WHERE
                    coluna = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa){
        $sql = "UPDATE kanboard_tarefas
                SET posicao = posicao - 1
                WHERE
                    coluna = $coluna
                    and posicao > (select posicao from kanboard_tarefas where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function reordenarTarefas($coluna){
        $sql = "UPDATE kanboard_tarefas
                SET posicao = (@rownum := 1 + @rownum)
                WHERE 
                    0 = (@rownum:=0)
                    and coluna = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    protected function incluirTarefa($etiqueta, $coluna){
        $pos = $this->getUltimaPosicaoColunaDestino($coluna);
        $sql = "insert into kanboard_tarefas (id, coluna, etiqueta, posicao, cor, dono, categoria, data_limite, responsavel, tags, arrastavel, ativo)
                values (null, $coluna, '$etiqueta', $pos, 'yellow', null, null, null, null, null, 'S', 'S')";
        query($sql);
        $sql = "update kanboard_projetos set modificado = '" . time() . "' where id in (select projeto from kanboard_colunas where id = $coluna)";
        query($sql);
    }
}