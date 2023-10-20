<?php
class kanboard_comentarios{
    private $_comentarios;
    private $_titulo_task;
    private $_tarefa;
    
    function __construct($param){
        $this->_tarefa = $param['tarefa'];
        $this->_titulo_task = $param['titulo'];
        $this->_comentarios = $param['comentarios'] ?? kanboard_comentarios::getComentariosTarefa($this->_tarefa);
    }
    
    static function getComentariosTarefa($tarefa){
        $ret = array();
        $sql = "select kanboard_comentarios.*, sys001.nome as criador_nome  from kanboard_comentarios left join sys001 on (kanboard_comentarios.criador = sys001.id) where tarefa = $tarefa order by kanboard_comentarios.dt_criado";
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
    
    function __toString(){
        
        $ret = '<div class="page-header" ' . $this->gerarStyleHeaderLista() . '>
                    <h2 ' . $this->gerarStyleH2Lista() . '>' . $this->_titulo_task . '</h2>
                    <!--
                        <ul ' . $this->gerarStyleUlLista() . '>
                            <li ' . $this->gerarStyleLiLista() . '>
                                ' . $this->renderizarBotaoOrdenar() . '
                            </li>
                            <li ' . $this->gerarStyleLiLista() . '>
                                ' . $this->renderizarBotaoEmail() . '
                            </li>
                        </ul>
                    -->
                </div>
                <div class="comments">';
        
        foreach ($this->_comentarios as $c){
            $ret .= $this->renderizarComentarioUnitario($c);
        }
        $ret .= $this->renderizarFormComentario();

        $ret .= '</div>';
        
        return $ret;
    }
    
    public function montarBlocoComentariosExistentes(){
        $ret = '';
        foreach ($this->_comentarios as $c){
            $ret .= $this->renderizarComentarioUnitario($c);
        }
        return $ret;
    }
    
    private function gerarStyleLiLista(){
        $ret = '    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-size: 100%;
    color: var(--color-primary);
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    margin: 0;
    padding: 0;
    display: inline;
    padding-right: 15px;
    box-sizing: initial;';
        return $ret;
    }
    
    private function gerarStyleUlLista(){
        $ret = ' style = "    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-size: 100%;
    color: var(--color-primary);
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    margin: 0;
    padding: 0;
    text-align: left;
    margin-top: 5px;
    display: inline-block;
    box-sizing: initial;"';
        return $ret;
    }
    
    private function gerarStyleH2Lista(){
        $ret = ' style = "    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    color: var(--color-primary);
    font-size: 1.4em;
    margin: 0;
    padding: 0;
    font-weight: 700;
    border-bottom: 1px dotted #ccc;
    text-align: initial;
    box-sizing: initial;
    "';
        return $ret;
    }
    
    private function gerarStyleHeaderLista(){
        $ret = ' style = "    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-size: 100%;
    color: var(--color-primary);
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    margin-bottom: 10px;
    box-sizing: initial;
    text-align: initial;
    font-weight: initial;
    line-height: initial;"';
        return $ret;
    }
    
    private function desenharAvatar($id_usuario, $nome_usuario){
        $ret = '<div class="avatar avatar-48 avatar-left"><img src="' . $this->getCaminhoFoto($id_usuario) . '" alt="' . $nome_usuario . '" title="' . $nome_usuario . '" width="48" height="48"></div>';
        return $ret;
    }
    
    private function getCaminhoFoto($id){
        global $config;
        $ret = '';
        $avatar = $config['baseS3'].'imagens/avatares/'.$id.'.jpg';
        $ret = $config['imagens'].'avatares/'.$id.'.jpg';
        if(!file_exists($avatar)){
            $avatar = $config['baseS3'].'imagens/avatares/'.$id.'.png';
            $ret = $config['imagens'].'avatares/'.$id.'.png';
            if(!file_exists($avatar)){
                $avatar = $config['baseS3'].'imagens/avatares/'.$id.'.gif';
                $ret = $config['imagens'].'avatares/'.$id.'.gif';
                if(!file_exists($avatar)){
                    $ret = $config['imagens'].'avatares/avatarGenerico.jpg';
                }
            }
        }
        return $ret;
    }
    
    private function gerarTituloComentarioUnitario($comentario){
        $ret = '       <div class="comment-title">
                            <strong class="comment-username">' . $comentario['criador_nome'] . '</strong>
                            <small class="comment-date">Criado em: ' . $comentario['dt_criado'] . '</small>
                            <small class="comment-date">Modificado em: ' . $comentario['dt_modificado'] . '</small>
                        </div>';
        
        $ret = '       <div class="comment-title">
                            <strong class="comment-username">' . $comentario['criador_nome'] . '</strong>
                            <small class="comment-date">Criado em: ' . $this->formatarDatas($comentario['dt_criado']) . '</small>
                            <small class="comment-date">Modificado em: ' . $this->formatarDatas($comentario['dt_modificado']) . '</small>
                        </div>';
        return $ret;
    }
    
    private function formatarDatas($data){
        $data = substr($data, 0, 8);
        return datas::dataS2D($data, '/');
    }
    
    private function gerarMenuDropComentarioUnitario($comentario){
        $ret = '   <div class="comment-actions">
                            <div class="dropdown">
                                <a href="" class="dropdown-menu dropdown-menu-link-icon" ' . $this->gerarStyleDropA() . '>
                                    <i class="fa fa-cog" ' . $this->gerarStyleIDrop() . '></i>
                                    <i class="fa fa-caret-down" ' . $this->gerarStyleIDrop() . '></i>
                                </a>
                                <ul ' . $this->gerarStyleUlDrop() . '>
                                    <li>
                                        <a href="/kanboard/task/2#comment-1" class="" title="" target="_blank">
                                            <i class="fa fa-fw fa-link" aria-hidden="true"></i>
                                            Link
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/kanboard/?controller=CommentController&amp;action=edit&amp;task_id=2&amp;comment_id=1" class="js-modal-medium" title="">
                                            <i class="fa fa-edit fa-fw js-modal-medium" aria-hidden="true"></i>
                                            Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/kanboard/?controller=CommentController&amp;action=confirm&amp;task_id=2&amp;comment_id=1" class="js-modal-confirm" title="">
                                            <i class="fa fa-trash-o fa-fw js-modal-confirm" aria-hidden="true"></i>
                                            Remover
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>';
        $ret = '<!-- ' . $ret . ' -->';
        return $ret;
    }
    
    private function gerarConteudoComentarioUnitario($comentario){
        $ret = '       <div class="comment-content">
                            <div class="markdown">
                                <p>' . $comentario['conteudo'] . '</p>
                            </div>
                        </div>';
        return $ret;
    }
    
    function renderizarComentarioUnitario($comentario){
        $ret = '';
        $preview = false;
        $acoes = true;
        /*
        $ret .= '   <div class="comment' . ($preview ? ' comment-preview' : '') . '" id="comment-' . $comentario['id'] . '">';
        $ret .= $this->desenharAvatar($comentario['criador'], $comentario['criador_nome']);
        $ret .= $this->gerarTituloComentarioUnitario($comentario);
        if($acoes){
            $ret .= $this->gerarMenuDropComentarioUnitario($comentario);
        }
        $ret .= $this->gerarConteudoComentarioUnitario($comentario);
        $ret .= '</div>';
        */
        $ret .= '   <div class="comment' . ($preview ? ' comment-preview' : '') . '" id="comment-' . $comentario['id'] . '">';
        $ret .= '   <table style="width: 100%">
                        <colgroup>
                            <col span="1" style="width: 10%;">
                           <col span="1" style="width: 80%;">
                           <col span="1" style="width: 10%;">
                        </colgroup>
                        <tbody>
                            <tr>
                                <td rowspan="3">' . $this->desenharAvatar($comentario['criador'], $comentario['criador_nome']) . '</td>
                                <td>' . $this->gerarTituloComentarioUnitario($comentario) . '</td>
                                <td>' . $this->gerarMenuDropComentarioUnitario($comentario) . '</td>
                            </tr>
                            <tr>
                                <td rowspan="2">' . $this->gerarConteudoComentarioUnitario($comentario) . '</td>
                                <td>' . '</td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>';
        $ret .= '</div>';
        return $ret;
    }
    
    private function gerarStyleIDrop(){
        $ret = ' style="    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    border-collapse: collapse;
    border-spacing: 0;
    -webkit-user-select: none;
    word-wrap: break-word;
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    padding-right: 3px;
    text-decoration: none;
    color: #333;
    box-sizing: initial;"';
        return $ret;
    }
    
    private function gerarStyleDropA(){
        return ' style="    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    border-collapse: collapse;
    border-spacing: 0;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    border: none;
    color: #000;
    text-decoration: none;
            
    position: initial;
    top: initial;
    left: initial;
    z-index: initial;
    display: initial;
    float: initial;
    min-width: initial;
    padding: initial;
    margin: initial;
    text-align: initial;
    list-style: initial;
    background-color: initial;
    background-clip: initial;
    border-radius: initial;
    box-shadow: initial;"';
    }
    
    private function gerarStyleUlDrop(){
        $ret = ' style="    --color-primary: #333;
    --color-light: #999;
    --color-lighter: #dedede;
    --color-dark: #000;
    --color-medium: #555;
    --color-error: #b94a48;
    --link-color-primary: #36C;
    --link-color-focus: #DF5353;
    --link-color-hover: #333;
    --alert-color-default: #c09853;
    --alert-color-success: #468847;
    --alert-color-error: #b94a48;
    --alert-color-info: #3a87ad;
    --alert-color-normal: #333;
    --alert-background-color-default: #fcf8e3;
    --alert-background-color-success: #dff0d8;
    --alert-background-color-error: #f2dede;
    --alert-background-color-info: #d9edf7;
    --alert-background-color-normal: #f0f0f0;
    --alert-border-color-default: #fbeed5;
    --alert-border-color-success: #d6e9c6;
    --alert-border-color-error: #eed3d7;
    --alert-border-color-info: #bce8f1;
    --alert-border-color-normal: #ddd;
    --button-default-color: #333;
    --button-default-background-color: #f5f5f5;
    --button-default-border-color: #ddd;
    --button-default-color-focus: #000;
    --button-default-background-color-focus: #fafafa;
    --button-default-border-color-focus: #bbb;
    --button-primary-color: #fff;
    --button-primary-background-color: #4d90fe;
    --button-primary-border-color: #3079ed;
    --button-primary-color-focus: #fff;
    --button-primary-background-color-focus: #357ae8;
    --button-primary-border-color-focus: #3079ed;
    --button-danger-color: #fff;
    --button-danger-background-color: #d14836;
    --button-danger-border-color: #b0281a;
    --button-danger-color-focus: #fff;
    --button-danger-background-color-focus: #c53727;
    --button-danger-border-color-focus: #b0281a;
    --button-disabled-color: #ccc;
    --button-disabled-background-color: #f7f7f7;
    --button-disabled-border-color: #ccc;
    --avatar-color-letter: #fff;
    --activity-title-color: #000;
    --activity-title-border-color: #efefef;
    --activity-event-background-color: #fafafa;
    --activity-event-hover-color: #fff8dc;
    --user-mention-color: #000;
    --board-task-limit-color: #DF5353;
    color: var(--color-primary);
    font-family: Helvetica,Arial,sans-serif;
    text-rendering: optimizeLegibility;
    border-collapse: collapse;
    border-spacing: 0;
    cursor: pointer;
    -webkit-user-select: none;
    word-wrap: break-word;
    font-size: .9em;
    margin: 0;
    padding: 0;
    display: none;
    box-sizing: initial;
    position: absolute;
    background-color: #fff;
    list-style-type: none;"';
        return $ret;
    }
    
    public function renderizarFormComentario($link = '', $tag_form = true){
        $link_excluir = htmlentities($link);
        $opcoes_ajax = array(
            'url'         => $link_excluir,
            'submitLabel' => 'Salvar',
            'orLabel'     => 'OU',
            'cancelLabel' => 'Cancelar',
            'tabindex'    => null,
            'disabled'    => false,
            'IdForm'         => 'formComentario',
        );
        
        
        if(empty($link)){
            $link = getLinkAjax('salvarListarComentarios');
        }
        $link .= '&tarefa=' . $this->_tarefa;
        $ret = '<div class="page-header">
    <h2>Adicionar um comentário</h2>
</div>';
        $ret .= '   <div class="js-text-editor-rendered" data-params="{&quot;name&quot;:&quot;comment&quot;,&quot;css&quot;:&quot;&quot;,&quot;required&quot;:true,&quot;tabindex&quot;:&quot;-1&quot;,&quot;labelPreview&quot;:&quot;Pr\u00e9-visualizar&quot;,&quot;previewUrl&quot;:&quot;\/kanboard\/?controller=TaskAjaxController&amp;action=preview&quot;,&quot;labelWrite&quot;:&quot;Escrever&quot;,&quot;labelTitle&quot;:&quot;T\u00edtulo&quot;,&quot;placeholder&quot;:&quot;Escreva seu texto em Markdown&quot;,&quot;ariaLabel&quot;:&quot;Novo coment\u00e1rio&quot;,&quot;autofocus&quot;:false,&quot;suggestOptions&quot;:{&quot;triggers&quot;:{&quot;#&quot;:&quot;\/kanboard\/?controller=TaskAjaxController&amp;action=suggest&amp;search=SEARCH_TERM&quot;,&quot;@&quot;:&quot;\/kanboard\/?controller=UserAjaxController&amp;action=mention&amp;project_id=1&amp;search=SEARCH_TERM&quot;}}}">
                        <script type="text/template"></script>
                        <div class="text-editor">
                            <div class="text-editor-view-mode" style="display: none;">
                                <div class="text-editor-toolbar">
                                    <a href="#">
                                        <i class="fa fa-pencil-square-o fa-fw"></i> 
                                        Escrever
                                    </a>
                                </div>
                                <div class="text-editor-preview-area markdown"></div>
                            </div>
                            <div class="text-editor-write-mode">
                                <div class="text-editor-toolbar">
                                    <a href="#">
                                        <i class="fa fa-eye fa-fw"></i> 
                                        Pré-visualizar
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-bold fa-fw"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-italic fa-fw"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-strikethrough fa-fw"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-link fa-fw"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-quote-right fa-fw"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-list-ul fa-fw"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-code fa-fw"></i>
                                    </a>
                                </div>
                                <textarea name="comment" tabindex="-1" required="required" aria-label="Novo comentário" placeholder="Escreva seu texto em Markdown"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="js-submit-buttons" data-params=\'' . json_encode($opcoes_ajax, JSON_HEX_APOS) . '\'></div>
                ';
        if($tag_form){
            $ret = '<form method="post" id="formComentario" action="' . $link . '" autocomplete="off">' . $ret . '</form>';
        }
        return $ret;
    }
    
    function renderizarBotaoOrdenar(){
        //$this->url->icon(\'sort\', t(\'Change sorting\'), \'CommentListController\', \'toggleSorting\', array(\'task_id\' => $task[\'id\']), false, \'js-modal-replace\')
        $ret = '<a href="/kanboard/?controller=CommentListController&amp;action=toggleSorting&amp;task_id=2" class="js-modal-replace" title=""><i class="fa fa-fw fa-sort" aria-hidden="true"></i>alterar ordenação</a>';
        //$ret = '<!-- ' . $ret . ' -->';
        return $ret;
    }
    
    function renderizarBotaoEmail(){
        //$this->modal->medium(\'paper-plane\', t(\'Send by email\'), \'CommentMailController\', \'create\', array(\'task_id\' => $task[\'id\']))
        $ret = '<a href="/kanboard/?controller=CommentMailController&amp;action=create&amp;task_id=2" class="js-modal-medium" title=""><i class="fa fa-paper-plane fa-fw js-modal-medium" aria-hidden="true"></i>Enviar por e-mail</a>';
        //$ret = '<!-- ' . $ret . ' -->';
        return $ret;
    }
}