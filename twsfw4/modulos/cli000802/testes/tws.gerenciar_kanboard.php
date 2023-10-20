<?php
function getListaPermissoesKanboard(){
    return array(
        array('visualizar', 'Visualizar {entidade}'),
        array('criar', 'Criar Tarefas'), 
        array('excluir', 'Excluir Tarefas'), 
        array('comentar', 'Criar Comentários'), 
        array('fechar', 'Finalizar Tarefas'), 
        array('criarSub', 'Criar Subtarefas'),
        array('alterarSub', 'Alterar Subtarefas'),
        array('editar', 'Editar Tarefas'), 
    );
}

function getListaIcones(){
    return array(
        array('f2d3', '&#xf2d3'),
        array('f0d6', '&#xf0d6'),
    );
    /*
    return array(
        array('f2d3', '&#xf2d3'),
        array('f0d6', '&#xf0d6'),
    );
    */
}

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class gerenciar_kanboard{
    var $funcoes_publicas = array(
        'index' 	=> true,
        'ajax'      => true,
        'editarColunasProjeto' => true,
        'editarRaiasProjeto' => true,
        'permissoesProjeto' => true,
        'grupos' => true,
        'salvarGrupo' => true,
        'editarGrupo' => true,
        'membrosGrupo' => true,
        'excluirGrupo' => true,
        'permissoesColunas' => true,
        'permissoesRaias' => true,
        'movimentacoes' => true,
        'editarProjeto' => true,
        'salvarProjeto' => true,
        'hierarquia' => true,
        'excluirProjeto' => true,
        'tags' => true,
        'salvarTags' => true,
    );
    
    function __construct(){
        
    }
    
    public function ajax(){
        $ret = '';
        $projeto = $_GET['projeto'] ?? '';
        $objeto = $_GET['objeto'] ?? '';
        $grupo = $_GET['grupo'] ?? '';
        $op = getOperacao();
        if($op == 'incluirPermissaoAjax'){
            $ret = '0';
            $sentido = $_GET['sentido'];
            $origem = $_GET['origem'];
            $sql = "select * from kanboard_permissoes_movimentacao_status where id = $origem";
            $rows = query($sql);
            $id_entidade_origem = '';
            $tipo_entidade = '';
            if(is_array($rows) && count($rows) > 0){
                $id_entidade_origem = $rows[0]['id_entidade'];
                $tipo_entidade = $rows[0]['entidade'];
                $id_entidade_alvo = $_GET['entidade'];
                $param = array();
                if($sentido == 'vindo'){
                    $param = array(
                        'grupo' => $grupo,
                        'tipo' => $tipo_entidade,
                        'origem' => $id_entidade_alvo,
                        'destino' => $id_entidade_origem,
                    );
                }
                elseif($sentido == 'indo'){
                    $param = array(
                        'grupo' => $grupo,
                        'tipo' => $tipo_entidade,
                        'destino' => $id_entidade_alvo,
                        'origem' => $id_entidade_origem,
                    );
                }
                $sql = montaSQL($param, 'kanboard_permissoes_movimentacao', 'SELECT', '', 'id');
                $rows = query($sql);
                if(is_array($rows) && count($rows) == 0){
                    $sql = montaSQL($param, 'kanboard_permissoes_movimentacao');
                    query($sql);
                    $sql = montaSQL($param, 'kanboard_permissoes_movimentacao', 'SELECT', '', 'id');
                    $rows = query($sql);
                    if(is_array($rows) && count($rows) > 0){
                        $ret = strval($rows[0]['id']);
                    }
                }
            }
        }
        elseif ($op == 'excluirPermissaoAjax'){
            $id = $_GET['id'];
            $sql = "delete from kanboard_permissoes_movimentacao where id = $id";
            query($sql);
        }
        elseif($objeto == 'colunas'){
            $temp = new gerenciar_colunas($projeto);
            $ret =  $temp->ajax();
        }
        elseif($objeto == 'raias'){
            $temp = new gerenciar_raias($projeto);
            $ret = $temp->ajax();
        }
        elseif($objeto == 'membros_grupos'){
            $temp = new gerenciar_membros_grupo($grupo);
            $ret = $temp->ajax();
        }
        elseif ($objeto == 'permissoes_projeto'){
            $temp = new gerenciar_permissoes_projeto($projeto);
            $ret = $temp->ajax();
        }
        elseif ($objeto == 'permissoes_coluna'){
            $temp = new gerenciar_permissoes_coluna($projeto);
            $ret = $temp->ajax();
        }
        elseif ($objeto == 'permissoes_raia'){
            $temp = new gerenciar_permissoes_raia($projeto);
            $ret = $temp->ajax();
        }
        elseif ($objeto == 'movimentacao'){
            $temp = new gerenciar_permissoes_movimentacao($projeto);
            $ret = $temp->ajax();
        }
        elseif ($objeto == 'hierarquia'){
            $temp = new gerenciar_hierarquia($grupo);
            $ret = $temp->ajax();
        }
        return $ret;
    }
    
    function arrumarPermissoes(){
        $sql = "select id from kanboard_grupos where id not in (2, 3, 7)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $grupos = array();
            foreach ($rows as $row){
                $grupos[] = $row['id'];
            }
            $permissoes = getListaPermissoesKanboard();
            $linhas = array();
            foreach ($grupos as $grupo){
                foreach ($permissoes as $permissao){
                    $linhas[] = "(null, 'coluna', 1, '{$permissao[0]}', $grupo, 'livre')";
                }
            }
            if(count($linhas) > 0){
                $sql = "insert into kanboard_permissoes_status (id, entidade, id_entidade, tipo, grupo, status) values " . implode(', ', $linhas);
                query($sql);
            }
        }
    }
    
    function index($param = array()){
        //$this->arrumarPermissoes();
        $this->geraScriptConfirmacao();
        $param = array();
        $param['titulo'] = 'Gerenciar Projetos Kanboard';
        $tabela_projetos = new tabela01($param);
        $tabela_projetos->addColuna(array('campo' => 'id'		, 'etiqueta' => 'ID'		,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $tabela_projetos->addColuna(array('campo' => 'etiqueta'	, 'etiqueta' => 'Etiqueta'  ,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        
        $param = array();
        $param['texto'] = 'Excluir';
        $param['link'] 	= "javascript:confirmaExclusao('".getLink()."excluirProjeto&projeto=','{ID}',{COLUNA:etiqueta})";
        $param['coluna']= 'id';
        $param['cor'] 	= 'danger';
        $param['flag'] 	= '';
        $param['width'] = 80;
        $param['pos'] = 'I';
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Editar',
            'link' => getLink() . 'editarProjeto&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'primary'
        );
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Editar Colunas',
            'link' => getLink() . 'editarColunasProjeto&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Editar Raias',
            'link' => getLink() . 'editarRaiasProjeto&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela_projetos->addAcao($param);
        //////////////////////////////////////////////////////////////////////////////
        $param = array(
            'texto' => 'Pemissões Projeto',
            'link' => getLink() . 'permissoesProjeto&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Pemissões Colunas',
            'link' => getLink() . 'permissoesColunas&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Pemissões Raias',
            'link' => getLink() . 'permissoesRaias&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Movimentações',
            'link' => getLink() . 'movimentacoes&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Tags',
            'link' => getLink() . 'tags&projeto=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela_projetos->addAcao($param);
        
        $botao = array();
        $botao["onclick"]= "setLocation('" . getLink() . "editarProjeto')";
        $botao["texto"]	= "Novo Projeto";
        $botao['cor'] = 'success';
        $tabela_projetos->addBotaoTitulo($botao);
        
        $botao = array();
        $botao["onclick"]= "setLocation('" . getLink() . "grupos')";
        $botao["texto"]	= "Editar Grupos";
        $botao['cor'] = 'success';
        $tabela_projetos->addBotaoTitulo($botao);
        
        /*
        $botao = array();
        $botao["onclick"]= "setLocation('" . getLink() . "editarTags')";
        $botao["texto"]	= "Editar Tags";
        $botao['cor'] = 'success';
        $tabela_projetos->addBotaoTitulo($botao);
        */
        
        $dados = $this->getListaProjetos();
        $tabela_projetos->setDados($dados);
        
        return $tabela_projetos . '';
    }
    
    public function excluirProjeto(){
        $projeto = $_GET['projeto'];
        $sql = "delete from kanboard_projetos where id = $projeto";
        query($sql);
        redireciona(getLink() . 'index');
    }
    
    public function grupos(){
        $ret = '';
        
        $param = array();
        $param['titulo'] = 'Gerenciar Grupos';
        $tabela = new tabela01($param);
        
        $tabela->addColuna(array('campo' => 'id'		, 'etiqueta' => 'ID'		,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'etiqueta'		, 'etiqueta' => 'Nome'		,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        
        $param = array(
            'texto' => 'Excluir',
            'link' => getLink() . 'excluirGrupo&grupo=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'danger'
        );
        $tabela->addAcao($param);
        
        $param = array(
            'texto' => 'Membros',
            'link' => getLink() . 'membrosGrupo&grupo=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela->addAcao($param);
        
        $param = array(
            'texto' => 'Editar',
            'link' => getLink() . 'editarGrupo&grupo=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela->addAcao($param);
        
        $param = array(
            'texto' => 'Hierarquia',
            'link' => getLink() . 'hierarquia&grupo=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'secondary'
        );
        $tabela->addAcao($param);
        
        $botao = array();
        $botao["onclick"]= "setLocation('" . getLink() . "editarGrupo&grupo=0')";
        $botao["texto"]	= "Criar Grupo";
        $botao['cor'] = 'success';
        $tabela->addBotaoTitulo($botao);
        
        $botao = array();
        $botao['onclick']	= "setLocation('" . getLink() . "index');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $tabela->addBotaoTitulo($botao);
        
        $dados = $this->getDadosTabelaGrupos();
        $tabela->setDados($dados);
        
        $ret .= $tabela;
        return $ret;
    }
    
    public function editarGrupo(){
        $ret = '';
        $grupo = $_GET['grupo'];
        putAppVar('link_salvar_cad', getLink() . 'salvarGrupo&grupo=' . $grupo);
        putAppVar('link_redirecionar_cad_cancelar', getLink() . 'grupos');
        $cad = new cad01('kanboard_grupos');
        if($grupo == '0' || $grupo == 0){
            $ret = $cad->incluir();
        }
        else{
            $ret = $cad->editar(base64_encode($grupo));
        }
        return $ret;
    }
    
    public function salvarGrupo(){
        $grupo = $_GET['grupo'];
        $cad = new cad01('kanboard_grupos');
        if($grupo == '0' || $grupo == 0){
            $cad->salvar(0, array(), 'I');
            $sql = montaSQL($_POST['formCRUD'], 'kanboard_grupos', 'SELECT', '', 'id');
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $id_novo_grupo = $rows[0]['id'];
                
                $sql = "select id from kanboard_projetos";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    $sql = "insert into kanboard_permissoes_status (id, entidade, tipo, grupo, status, id_entidade) values ";
                    $linhas = array();
                    $permissoes = getListaPermissoesKanboard();
                    foreach ($rows as $row){
                        foreach ($permissoes as $p){
                            $linhas[] = "(null, 'coluna', '{$p[0]}', $id_novo_grupo, 'livre', {$row['id']})";
                            $linhas[] = "(null, 'raia', '{$p[0]}', $id_novo_grupo, 'livre', {$row['id']})";
                        }
                    }
                    $sql .= implode(', ', $linhas);
                    query($sql);
                }
            }
        }
        else{
            $cad->salvar(base64_encode($grupo), array(), 'E');
        }
        redireciona(getLink() . 'grupos');
    }
    
    public function excluirGrupo(){
        $grupo = $_GET['grupo'];
        $sql = "DELETE FROM kanboard_grupos WHERE id = $grupo";
        query($sql);
        $sql = "DELETE FROM kanboard_membros WHERE grupo = $grupo";
        query($sql);
        $sql = "DELETE FROM kanboard_permissoes WHERE grupo = $grupo";
        query($sql);
        $sql = "DELETE FROM kanboard_permissoes_movimentacao WHERE grupo = $grupo";
        query($sql);
        $sql = "DELETE FROM kanboard_permissoes_status WHERE grupo = $grupo";
        query($sql);
        redireciona(getLink() . 'grupos');
    }
    
    public function membrosGrupo(){
        $grupo = $_GET['grupo'];
        $teste = new gerenciar_membros_grupo($grupo);
        return $teste->index();
    }
    
    private function getDadosTabelaGrupos(){
        $ret = array();
        $sql = "select * from kanboard_grupos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = array('id', 'etiqueta');
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
    
    public function editarRaiasProjeto(){
        $projeto = $_GET['projeto'];
        $teste = new gerenciar_raias($projeto);
        return $teste->index();
    }
    
    public function editarColunasProjeto(){
        $projeto = $_GET['projeto'];
        $teste = new gerenciar_colunas($projeto);
        return $teste->index();
    }
    
    private function geraScriptConfirmacao(){
        addPortaljavaScript('function confirmaExclusao(link,id,desc){');
        addPortaljavaScript('	if (confirm("Confirma a EXCLUSAO do projeto "+desc+"?")){');
        addPortaljavaScript('		setLocation(link+id);');
        addPortaljavaScript('	}');
        addPortaljavaScript('}');
    }
    
    private function getListaProjetos(){
        $ret = array();
        $sql = "SELECT * FROM kanboard_projetos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = array('id', 'etiqueta');
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
    
    public function editarProjeto(){
        $ret = '';
        $projeto = $_GET['projeto'] ?? 0;
        putAppVar('link_salvar_cad', getLink() . 'salvarProjeto&projeto=' . $projeto);
        //putAppVar('link_redirecionar_cad_cancelar', getLink() . 'index');
        $cad = new cad01('kanboard_projetos');
        if($projeto == '0' || $projeto == 0){
            $ret = $cad->incluir();
        }
        else{
            $ret = $cad->editar(base64_encode($projeto));
        }
        return $ret;
    }
    
    public function salvarProjeto(){
        $projeto = $_GET['projeto'] ?? 0;
        $cad = new cad01('kanboard_projetos');
        if($projeto == '0' || $projeto == 0){
            $cad->salvar(0, array(), 'I');
            $sql = montaSQL($_POST['formCRUD'], 'kanboard_projetos', 'SELECT', '', 'id');
            $rows = query($sql);
            
            $id_novo_projeto = $cad->_ultimoIdIncluido;
            if($id_novo_projeto !== 0){
                
                //permissões do projeto
                $sql = "select id from kanboard_grupos";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    $sql_permissoes_col_raia = "insert into kanboard_permissoes_status (id, entidade, tipo, grupo, status, id_entidade) values ";
                    $sql_permissoes_projeto  = "insert into kanboard_permissoes (id, grupo, entidade, tipo, id_entidade) values ";
                    $linhas_permissoes_col_raia = array();
                    $linhas_permissoes_projeto  = array();
                    $permissoes = getListaPermissoesKanboard();
                    foreach ($rows as $row){
                        foreach ($permissoes as $p){
                            $linhas_permissoes_col_raia[] = "(null, 'coluna', '{$p[0]}', {$row['id']}, 'livre', $id_novo_projeto)";
                            $linhas_permissoes_col_raia[] = "(null, 'raia', '{$p[0]}', {$row['id']}, 'livre', $id_novo_projeto)";
                            $linhas_permissoes_projeto[] = "(null, {$row['id']}, 'projeto', '{$p[0]}', $id_novo_projeto)";
                        }
                    }
                    $sql_permissoes_col_raia .= implode(', ', $linhas_permissoes_col_raia);
                    query($sql_permissoes_col_raia);
                    
                    $sql_permissoes_projeto .= implode(', ', $linhas_permissoes_projeto);
                    query($sql_permissoes_projeto);
                }
                //////////////////////////////////////
                //criando colunas
                $sql = "insert into kanboard_colunas (id, projeto, etiqueta, posicao, limite, descricao, esconder)
                values ";
                
                $valores = array(
                    "(null, $id_novo_projeto, 'Backlog', 1, 0, null 'N')",
                    "(null, $id_novo_projeto, 'To-Do', 2, 0, null 'N')",
                    "(null, $id_novo_projeto, 'Realizando', 3, 0, null 'N')",
                    "(null, $id_novo_projeto, 'Pausado', 4, 0, null 'N')",
                    "(null, $id_novo_projeto, 'Validação', 5, 0, null 'N')",
                    "(null, $id_novo_projeto, 'Finalizados', 6, 0, null 'N')",
                );
                
                $sql .= implode(', ', $valores);
                query($sql);
                //permissões colunas
                $sql = "select id from kanboard_colunas where projeto = $id_novo_projeto";
                $rows = query($sql);
                foreach ($rows as $row){
                    $id = $row['id'];
                    $sql = "insert into kanboard_permissoes_movimentacao_status (id, entidade, id_entidade, status)
                                                                values (null, 'coluna', $id, 'livre')";
                    query($sql);
                }
                //////////////////////////////////////
                //criando raia
                $sql = "insert into kanboard_raia (id, projeto, etiqueta, posicao)
                values ";
                
                $valores = array(
                    "(null, $id_novo_projeto, 'Base', 1)"
                );
                
                $sql .= implode(', ', $valores);
                query($sql);
                //permissões raia
                $sql = "select id from kanboard_raia where projeto = $id_novo_projeto";
                $rows = query($sql);
                foreach ($rows as $row){
                    $id = $row['id'];
                    $sql = "insert into kanboard_permissoes_movimentacao_status (id, entidade, id_entidade, status)
                                                                values (null, 'raia', $id, 'livre')";
                    query($sql);
                }
            }
        }
        else{
            $cad->salvar(base64_encode($projeto), array(), 'E');
        }
        redireciona(getLink() . 'index');
    }
    
    public function permissoesProjeto(){
        $projeto = $_GET['projeto'];
        $teste = new gerenciar_permissoes_projeto($projeto);
        return $teste->index();
    }
    public function permissoesColunas(){
        $projeto = $_GET['projeto'];
        $teste = new gerenciar_permissoes_coluna($projeto);
        return $teste->index();
    }
    public function permissoesRaias(){
        $projeto = $_GET['projeto'];
        $teste = new gerenciar_permissoes_raia($projeto);
        return $teste->index();
    }
    public function movimentacoes(){
        $projeto = $_GET['projeto'];
        $teste = new gerenciar_permissoes_movimentacao($projeto);
        return $teste->index();
    }
    public function hierarquia(){
        $projeto = $_GET['grupo'];
        $teste = new gerenciar_hierarquia($projeto);
        return $teste->index();
    }
    public function tags(){
        $ret = '';
        $projeto = $_GET['projeto'];
        
        $param = array();
        $param['titulo'] = 'Gerenciar Tags';
        $tabela = new tabela01($param);
        
        $tabela->addColuna(array('campo' => 'tag'		, 'etiqueta' => 'Tag'		,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'cor'		, 'etiqueta' => 'Cor'		,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        
        $dados = $this->getDadosTags($projeto);
        $tabela->setDados($dados);
        
        
        $ret .= $tabela;
        $param = array(
            'acao' => getLink() . 'salvarTags&projeto=' . $projeto,
            'sendFooter' => true,
            'nome' => 'idDoForm',
            'id' => 'idDoForm',
            'metodo' => 'post',
        );

        $ret = formbase01::form($param, $ret);
        
        return $ret;
    }
    private function getDadosTags($projeto){
        $ret = array();
        $sql = "select tags from kanboard_tarefas where coluna in (select id from kanboard_colunas where projeto = $projeto)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $lista_tags = [];
            $temp = [];
            foreach ($rows as $row){
                $temp[] = $row['tags'];
            }
            $temp = implode(' ', $temp);
            $temp = explode(' ', $temp);
            foreach ($temp as $tag){
                if(!empty($tag)){
                    $lista_tags[$tag] = true;
                }
            }
            
            $lista_tags = array_keys($lista_tags);
            
            $cores_tags = array();
            
            $sql = "select tag, cor from kanboard_tags where projeto = $projeto";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $cores_tags[$row['tag']] = $row['cor'];
                }
            }
            
            foreach ($lista_tags as $tag){
                $temp = array(
                    'tag' => $tag,
                    'cor' => $this->montaSelectCor(($cores_tags[$tag] ?? ''), $tag),
                );
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    private function montaSelectCor($cor, $tag){
        $cores = array(
            'yellow' => 'Amarelo',
            'blue' => 'Azul',
            'green' => 'Verde',
            'purple' => 'Roxo',
            'red' => 'Vermelho',
            'orange' => 'Laranja',
            'grey' => 'Cinza',
            'brown' => 'Marrom',
            'deep_orange' => 'Laranja escuro',
            'dark_grey' => 'Cinza escuro',
            'pink' => 'Rosa',
            'teal' => 'Turquesa',
            'cyan' => 'Azul intenso',
            'lime' => 'Verde limão',
            'light_green' => 'Verde claro',
            'amber' => 'Âmbar',
        );
        
        $ret = '';
        
        $ret .= '<select name="idDoForm[cores][' . $tag . ']" id="' . $tag . '" form="idDoForm">';
        $ret .= "<option value=\"\"></option>";
        foreach ($cores as $chave => $valor){
            if($chave == $cor){
                $ret .= "<option value=\"$chave\" selected=\"selected\">$valor</option>";
            }
            else{
                $ret .= "<option value=\"$chave\">$valor</option>";
            }
        }
                  
        $ret .= '</select>';
        
        return $ret;
    }
    public function salvarTags(){
        $dados = $_POST['idDoForm']['cores'];
        $projeto = $_GET['projeto'];
        $sql = "delete from kanboard_tags where projeto = $projeto";
        query($sql);
        $sql = 'insert into kanboard_tags (id, projeto, tag, cor) values ';
        $linhas = [];
        foreach ($dados as $tag => $cor){
            $linhas[] = "(null, $projeto, '$tag', '$cor')";
        }
        if(count($linhas) > 0){
            $sql .= implode(', ', $linhas);
            query($sql);
        }
        redireciona(getLink() . 'index');
    }
}

class gerenciar_colunas extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'colunas';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&projeto=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkCheck .= "&objeto=$objeto";
        $this->_linkReload = getLink() . "editarColunasProjeto&projeto=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&projeto=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta coluna: "' . kanboard_cabecalho_coluna::getTituloColuna($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'];
        $raia = $_GET['raia'];
        
        $this->incluirTarefa($etiqueta, $coluna, $raia);
        
        header('X-Ajax-Redirect: ' . $this->_linkReload);
        //header('Location: ' . '/intranet4/index.php?menu=testes.mercado_pago.index');
    }
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);

        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        //cria a tarefa
        $pos = $this->getUltimaPosicaoColunaDestino($coluna, $raia);
        $sql = "insert into kanboard_colunas (id, projeto, etiqueta, posicao, limite, descricao, esconder)
                values (null, $coluna,'$etiqueta', $pos, 0, null, 'N')";
        query($sql);
        
        //cria o log da criação de tarefas
        $id = '';
        $sql = "select id from kanboard_colunas where projeto = $coluna and etiqueta = '$etiqueta' and posicao = $pos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $id = $rows[0]['id'];
            $this->gerarLog('coluna', 'criar', $id);
        }
        
        //cria a permissao de movimentação
        if(!empty($id)){
            $sql = "insert into kanboard_permissoes_movimentacao_status (id, entidade, id_entidade, status)
                                                                values (null, 'coluna', $id, 'livre')";
            query($sql);
        }
        
        
        
        //atualiza no banco a ultima vez q o projeto foi modificado
        $this->atualizarProjeto($coluna);
        
        
    }
    
    protected function deletarTarefaBanco($id){
        $this->atualizarProjeto('', $id);
        $sql = "delete from kanboard_colunas where id = $id";
        query($sql);
        $this->gerarLog('colunas', 'excluir', $id);
        
        $sql = "delete from kanboard_permissoes where entidade = 'coluna' and and id_entidade = $id";
        query($sql);
        $sql = "delete from kanboard_permissoes_movimentacao_status where entidade = 'coluna' and id_entidade = $id";
        query($sql);
        $sql = "detele from kanboard_permissoes_movimentacao where tipo = 'coluna' and origem = $id";
        query($sql);
        $sql = "detele from kanboard_permissoes_movimentacao where tipo = 'coluna' and destino = $id";
        query($sql);
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_colunas WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_colunas
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_colunas where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_colunas
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_colunas set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', $id_tarefa);
        
        //cria o log
        $this->gerarLog('coluna', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_colunas
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        $raia = array(
            'id' => $projeto,
            'projeto' => $projeto,
            'etiqueta' => 'padrao',
            'posicao' => 1,
            'num_raias' => 1,
            'num_tarefas' => 0,
        );
        $coluna = array(
            'id' => $projeto,
            'etiqueta' => 'Ordem das Colunas',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => $this->_linkAjaxFormulario . '&coluna=' . $projeto,
            'esconder' => false,
            'tarefas' => array(),
            'bt_fechar_tarefas' => false,
        );
        $ret['raias'][1] = $raia;
        $ret['raias'][1]['colunas'][1] = $coluna;
        $sql = "select * from kanboard_colunas where projeto = $projeto order by posicao";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'coluna' => $projeto,
                    'cor'  => 'yellow',
                    'posicao' => $row['posicao'],
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $projeto,
                    'link_excluir' => getLinkAjax('excluirTarefaForm') . "&projeto=$projeto&objeto=colunas",
                    'link_editar' => getLinkAjax('editarTarefa') . '&objeto=colunas&projeto=' . $projeto,
                    'bt_editar' => false,
                );
                $ret['raias'][1]['num_tarefas']++;
                $ret['raias'][1]['colunas'][1]['tarefas'][] = $temp;
                $ret['raias'][1]['colunas'][1]['num_abertas']++;
            }
        }
        return $ret;
    }
    
    protected function editarTarefa(){
        $tarefa = $_GET['tarefa'];
        $etiqueta = kanboard_cabecalho_coluna::getCampoColuna($tarefa, 'etiqueta');
        $tipo = 'Coluna';
        $link = getLinkAjax('salvarComentario') . '&objeto=colunas&tarefa=' . $tarefa . '&projeto=' . $this->_projeto;
        $ret = kanboard_formularios::formularioEditarColunaRaia($etiqueta, $tipo, $link);
        return $ret;
    }
    
    protected function salvarComentario($a, $b){
        $tarefa = $_GET['tarefa'];
        $estiqueta = $_POST['etiqueta'];
        $sql = "update kanboard_colunas set etiqueta = '$estiqueta' where id = $tarefa";
        query($sql);
    }
}



class gerenciar_raias extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'raias';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&projeto=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkCheck .= "&objeto=$objeto";
        $this->_linkReload = getLink() . "editarRaiasProjeto&projeto=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&projeto=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta raia: "' . kanboard_raia::getTituloRaia($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'];
        $raia = $_GET['raia'];
        
        $this->incluirTarefa($etiqueta, $coluna, $raia);
        
        header('X-Ajax-Redirect: ' . $this->_linkReload);
        //header('Location: ' . '/intranet4/index.php?menu=testes.mercado_pago.index');
    }
    
    
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);
        
        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        //cria a tarefa
        $pos = $this->getUltimaPosicaoColunaDestino($coluna, $raia);
        $sql = "insert into kanboard_raia (id, projeto, etiqueta, posicao)
                values (null, $coluna,'$etiqueta', $pos)";
        query($sql);
        
        //atualiza no banco a ultima vez q o projeto foi modificado
        $this->atualizarProjeto($coluna);
        
        //cria o log da criação de tarefas
        $id = '';
        $sql = "select id from kanboard_raia where projeto = $coluna and etiqueta = '$etiqueta' and posicao = $pos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $id = $rows[0]['id'];
            $this->gerarLog('raia', 'criar', $id);
        }
        
        //cria a permissao de movimentação
        if(!empty($id)){
            $sql = "insert into kanboard_permissoes_movimentacao_status (id, entidade, id_entidade, status)
                                                                values (null, 'raia', $id, 'livre')";
            query($sql);
        }
        
        //atualiza no banco a ultima vez q o projeto foi modificado
        $this->atualizarProjeto($coluna);
    }
    
    protected function deletarTarefaBanco($id){
        $this->atualizarProjeto('', $id);
        $sql = "delete from kanboard_raia where id = $id";
        query($sql);
        $this->gerarLog('raia', 'excluir', $id);
        
        $sql = "delete from kanboard_permissoes where entidade = 'raia' and and id_entidade = $id";
        query($sql);
        $sql = "delete from kanboard_permissoes_movimentacao_status where entidade = 'raia' and id_entidade = $id";
        query($sql);
        $sql = "detele from kanboard_permissoes_movimentacao where tipo = 'raia' and origem = $id";
        query($sql);
        $sql = "detele from kanboard_permissoes_movimentacao where tipo = 'raia' and destino = $id";
        query($sql);
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_raia WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_raia where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_raia set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', '', '', $id_tarefa);
        
        //cria o log
        $this->gerarLog('raia', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        $raia = array(
            'id' => $projeto,
            'projeto' => $projeto,
            'etiqueta' => 'padrao',
            'posicao' => 1,
            'num_raias' => 1,
            'num_tarefas' => 0,
        );
        $coluna = array(
            'id' => $projeto,
            'etiqueta' => 'Ordem das Raias',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => $this->_linkAjaxFormulario . '&coluna=' . $projeto,
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        $ret['raias'][1] = $raia;
        $ret['raias'][1]['colunas'][1] = $coluna;
        $sql = "select * from kanboard_raia where projeto = $projeto order by posicao";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'coluna' => $projeto,
                    'cor'  => 'yellow',
                    'posicao' => $row['posicao'],
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $projeto,
                    'link_excluir' => getLinkAjax('excluirTarefaForm') . "&projeto=$projeto&objeto=raias",
                    'link_editar' => getLinkAjax('editarTarefa') . '&objeto=raias&projeto=' . $projeto,
                    'bt_editar' => false,
                );
                $ret['raias'][1]['num_tarefas']++;
                $ret['raias'][1]['colunas'][1]['tarefas'][] = $temp;
                $ret['raias'][1]['colunas'][1]['num_abertas']++;
            }
        }
        return $ret;
    }
    
    protected function editarTarefa(){
        $tarefa = $_GET['tarefa'];
        $etiqueta = kanboard_raia::getCampoRaia($tarefa, 'etiqueta');
        $tipo = 'Raia';
        $link = getLinkAjax('salvarComentario') . '&objeto=raias&tarefa=' . $tarefa . '&projeto=' . $this->_projeto;
        $ret = kanboard_formularios::formularioEditarColunaRaia($etiqueta, $tipo, $link);
        return $ret;
    }
    
    protected function salvarComentario($a, $b){
        $tarefa = $_GET['tarefa'];
        $estiqueta = $_POST['etiqueta'];
        $sql = "update kanboard_raia set etiqueta = '$estiqueta' where id = $tarefa";
        query($sql);
    }
}


class gerenciar_membros_grupo extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'membros_grupos';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&grupo=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&grupo=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&grupo=$projeto";
        $this->_linkCheck .= "&objeto=$objeto&grupo={$this->_projeto}";
        $this->_linkReload = getLink() . "membrosGrupo&grupo=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&grupo=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta raia: "' . kanboard_raia::getTituloRaia($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'];
        $raia = $_GET['raia'];
        
        $this->incluirTarefa($etiqueta, $coluna, $raia);
        
        header('X-Ajax-Redirect: ' . $this->_linkReload);
        //header('Location: ' . '/intranet4/index.php?menu=testes.mercado_pago.index');
    }
    
    
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);
        
        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        $param = array(
            'grupo' => $this->_projeto,
            'usuario' => $etiqueta,
        );
        $sql = montaSQL($param, 'kanboard_membros');
        query($sql);
    }
    
    protected function deletarTarefaBanco($id){
        $sql = "delete from kanboard_membros where grupo = {$this->_projeto} and usuario = $id";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function moverTarefa($dados){
        $coluna_destino = $dados['dst_column_id'];
        $coluna_origem = $dados['src_column_id'];
        $id_tarefa = $dados['task_id'];
        
        if($coluna_destino != $coluna_origem){
            if($coluna_destino == 1 || $coluna_destino == '1'){
                //apagar permissao
                $this->deletarTarefaBanco($id_tarefa);
            }
            else{
                //criar permissao
                $this->incluirTarefa($id_tarefa, '', '');
            }
        }
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_raia WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_raia where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_raia set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', '', '', $id_tarefa);
        
        //cria o log
        $this->gerarLog('raia', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "grupos');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        $raia = array(
            'id' => $projeto,
            'projeto' => $projeto,
            'etiqueta' => 'padrao',
            'posicao' => 1,
            'num_raias' => 1,
            'num_tarefas' => 0,
        );
        $ret['raias'][1] = $raia;
        $coluna = array(
            'id' => 1,
            'etiqueta' => 'Fora do Grupo',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        $ret['raias'][1]['colunas'][1] = $coluna;
        $coluna = array(
            'id' => 2,
            'etiqueta' => 'Membros do Grupo',
            'projeto' => $projeto,
            'posicao' => 2,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        $ret['raias'][1]['colunas'][2] = $coluna;
        
        $sql = "SELECT sys001.*, temp2.grupo FROM sys001 left join (select * from kanboard_membros where grupo = {$this->_projeto}) as temp2 on (sys001.id = temp2.usuario)";
        $rows = query($sql);
        $pos_fora = 1;
        $pos_dentro = 1;
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'cor'  => 'yellow',
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['nome'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $projeto,
                    'link_excluir' => '',
                    'link_editar' => '',
                    'bt_editar' => false,
                );
                
                if(empty($row['grupo'])){
                    $temp['posicao'] = $pos_fora;
                    $temp['coluna'] = 1;
                    $pos_fora++;
                    $ret['raias'][1]['colunas'][1]['tarefas'][] = $temp;
                    $ret['raias'][1]['colunas'][1]['num_tarefas']++;
                }
                else{
                    $temp['posicao'] = $pos_dentro;
                    $temp['coluna'] = 2;
                    $pos_dentro++;
                    $ret['raias'][1]['colunas'][2]['tarefas'][] = $temp;
                    $ret['raias'][1]['colunas'][2]['num_tarefas']++;
                }
                
                $ret['raias'][1]['num_tarefas']++;
                
            }
        }
        return $ret;
    }
    
    protected function editarTarefa(){
        $tarefa = $_GET['tarefa'];
        $tarefa = base64_encode($tarefa);
        $cad = new cad01('kanboard_raia');
        return $cad->editar($tarefa);
    }
    
    protected function verificarDtModificacao($arg1, $arg2){
        return true;
    }
}

class gerenciar_permissoes_projeto extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'permissoes_projeto';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&projeto=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkCheck .= "&objeto=$objeto&projeto={$this->_projeto}";
        $this->_linkReload = getLink() . "permissoesProjeto&projeto=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&projeto=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta raia: "' . kanboard_raia::getTituloRaia($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'];
        $raia = $_GET['raia'];
        
        $this->incluirTarefa($etiqueta, $coluna, $raia);
        
        header('X-Ajax-Redirect: ' . $this->_linkReload);
        //header('Location: ' . '/intranet4/index.php?menu=testes.mercado_pago.index');
    }
    
    
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);
        
        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        $param = array(
            'grupo' => $etiqueta,
            'entidade' => 'projeto',
            'tipo' => $raia,
            'id_entidade' => $this->_projeto,
        );
        $sql = montaSQL($param, 'kanboard_permissoes');
        query($sql);
    }
    
    protected function deletarTarefaBanco($id){
        $sql = "delete from kanboard_permissoes where id = $id";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function deletarPermissao($grupo, $id_permissao){
        $base = getListaPermissoesKanboard();
        $permissao = $base[$id_permissao][0];
        $entidade = 'projeto';
        $sql = "delete from kanboard_permissoes where grupo = $grupo and tipo = '$permissao' and entidade = '$entidade'";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function moverTarefa($dados){
        $coluna_destino = $dados['dst_column_id'];
        $coluna_origem = $dados['src_column_id'];
        $id_tarefa = $dados['task_id'];
        $raia = intval($dados['swimlane_id']);
        $base = getListaPermissoesKanboard();
        
        if($coluna_destino != $coluna_origem){
            if($coluna_destino == '2' || $coluna_destino == 2){
                //criando permissao
                $tipo = $base[$raia - 1][0];
                $sql = "select * from kanboard_permissoes where entidade = 'projeto' and id_entidade = {$this->_projeto} and tipo = '$tipo' and grupo = $id_tarefa";
                $rows = query($sql);
                if(is_array($rows) && count($rows) === 0){
                    $this->incluirTarefa($id_tarefa, '', $tipo);
                }
            }
            else{
                //excluindo permissao
                if($coluna_destino != $coluna_origem && kanboard_permissoes::getCampoPermissao($id_tarefa, 'tipo') == $base[$raia - 1][0]){
                    $this->deletarTarefaBanco($id_tarefa);
                }
            }
        }
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_raia WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_raia where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_raia set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', '', '', $id_tarefa);
        
        //cria o log
        $this->gerarLog('raia', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        /*
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        $raia = array(
            'id' => 1,
            'projeto' => $projeto,
            'etiqueta' => 'Com Permissão',
            'posicao' => 1,
            'num_raias' => 2,
            'num_tarefas' => 0,
        );
        $ret['raias'][1] = $raia;
        
        $raia = array(
            'id' => 2,
            'projeto' => $projeto,
            'etiqueta' => 'Sem Permissão',
            'posicao' => 2,
            'num_raias' => 2,
            'num_tarefas' => 0,
        );
        $ret['raias'][2] = $raia;
        
        $colunas_base = getListaPermissoesKanboard();
        foreach ($colunas_base as $key => $dados){
            $coluna = array(
                'id' => $key + 1,
                'etiqueta' => $dados[1],
                'projeto' => $projeto,
                'posicao' => $key + 1,
                'limite' => 0,
                'descricao' => '',
                'num_abertas' => 0,
                'num_fechadas' => 0,
                'num_tarefas' => 0,
                'link_nova' => '',
                'esconder' => false,
                'tarefas' => array(),
                'link_fechar' => '',
                'bt_fechar_tarefas' => false,
            );
            
            $ret['raias'][1]['colunas'][$key + 1] = $coluna;
            $ret['raias'][2]['colunas'][$key + 1] = $coluna;
        }
        
        
        $sql = "SELECT cruzada.*";
        $sql .= ', case when temp1.id is null then 2 else 1 end as raia';
        $sql .= $this->gerarSqlSinistro();
        $sql .= " left join (select * from kanboard_permissoes where entidade = 'projeto' and id_entidade = {$this->_projeto}) as temp1 on (cruzada.id = temp1.grupo and cruzada.campo = temp1.tipo)";
        $rows = query($sql);
        $posicoes = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'cor'  => 'yellow',
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $row['raia'],
                    'link_excluir' => '',
                    'link_editar' => '',
                    'bt_editar' => false,
                    'coluna' => $row['coluna']
                );
                
                if(isset($posicoes[$row['coluna']][$row['raia']])){
                    $posicoes[$row['coluna']][$row['raia']]++;
                }
                else{
                    $posicoes[$row['coluna']][$row['raia']] = 1;
                }
                $temp['posicao'] = $posicoes[$row['coluna']][$row['raia']]; 
                
                $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['tarefas'][] = $temp;
                $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['num_tarefas']++;          
                $ret['raias'][$row['raia']]['num_tarefas']++;
                
            }
        }
        return $ret;
        */
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        $entidade_sql = 'projeto';
        $coluna_sem = array(
            'id' => 1,
            'etiqueta' => 'Sem Permissão',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $coluna_com = array(
            'id' => 2,
            'etiqueta' => 'Com Permissão',
            'projeto' => $projeto,
            'posicao' => 2,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $raias_base = getListaPermissoesKanboard();
        foreach ($raias_base as $chave => $raia_atual){
            $temp = array(
                'id' => $chave + 1,
                'projeto' => $projeto,
                'etiqueta' => str_replace('{entidade}', 'Projeto', $raia_atual[1]),
                'posicao' => $chave + 1,
                'num_raias' => count($raias_base),
                'num_tarefas' => 0,
                'mostrar_totalizador' => false,
            );
            $ret['raias'][$chave + 1] = $temp;
            $ret['raias'][$chave + 1]['colunas'][1] = $coluna_sem;
            $ret['raias'][$chave + 1]['colunas'][2] = $coluna_com;
            //$ret['raias'][1]['colunas'][$key + 1] = $coluna;
        }
        
        
        $sql = "SELECT cruzada.*";
        //$sql .= ', case when temp1.id is null then 2 else 1 end as raia';
        $sql .= ', case when temp1.id is null then 1 else 2 end as raia, COALESCE(temp1.id, cruzada.id) as id_permissao';
        $sql .= $this->gerarSqlSinistro();
        $sql .= " left join (select * from kanboard_permissoes where entidade = '$entidade_sql' and id_entidade = {$this->_projeto}) as temp1 on (cruzada.id = temp1.grupo and cruzada.campo = temp1.tipo)";
        $rows = query($sql);
        $posicoes = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id_permissao'],
                    'cor'  => 'yellow',
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $row['coluna'],
                    'link_excluir' => '',
                    'link_editar' => '',
                    'bt_editar' => false,
                    'coluna' => $row['raia']
                );
                
                if(isset($posicoes[$row['raia']][$row['coluna']])){
                    $posicoes[$row['raia']][$row['coluna']]++;
                }
                else{
                    $posicoes[$row['raia']][$row['coluna']] = 1;
                }
                $temp['posicao'] = $posicoes[$row['raia']][$row['coluna']];
                
                $ret['raias'][$row['coluna']]['colunas'][$row['raia']]['tarefas'][] = $temp;
                $ret['raias'][$row['coluna']]['colunas'][$row['raia']]['num_tarefas']++;
                $ret['raias'][$row['coluna']]['num_tarefas']++;
                
            }
        }
        return $ret;
    }
    
    protected function gerarSqlSinistro(){
        $campos = getListaPermissoesKanboard();
        $sql = array();
        foreach ($campos as $key => $value){
            $sql[] = " select '{$value[0]}' as campo, " . ($key+1) . " as coluna ";
        }
        $ret = " from (select * from kanboard_grupos cross join (" . implode('union', $sql) . ") as todas_permissoes) as cruzada";
        return $ret;
    }
    
    protected function editarTarefa(){
        $tarefa = $_GET['tarefa'];
        $tarefa = base64_encode($tarefa);
        $cad = new cad01('kanboard_raia');
        return $cad->editar($tarefa);
    }
    
    protected function verificarDtModificacao($arg1, $arg2){
        return true;
    }
}

class gerenciar_permissoes_coluna extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'permissoes_coluna';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&projeto=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkCheck .= "&objeto=$objeto&projeto={$this->_projeto}";
        $this->_linkReload = getLink() . "permissoesColunas&projeto=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&projeto=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta raia: "' . kanboard_raia::getTituloRaia($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);
        
        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        $param = array(
            'grupo' => $etiqueta,
            'entidade' => 'coluna',
            'tipo' => $raia,
            'id_entidade' => $this->_projeto,
        );
        $sql = montaSQL($param, 'kanboard_permissoes');
        query($sql);
    }
    
    protected function deletarTarefaBanco($id){
        $sql = "delete from kanboard_permissoes where id = $id";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function deletarPermissao($grupo, $id_permissao){
        $base = getListaPermissoesKanboard();
        $permissao = $base[$id_permissao][0];
        $entidade = 'coluna';
        $sql = "delete from kanboard_permissoes where grupo = $grupo and tipo = '$permissao' and entidade = '$entidade'";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function moverTarefa($dados){
        /*
        $coluna_destino = $dados['dst_column_id'];
        $coluna_origem = $dados['src_column_id'];
        $id_tarefa = $dados['task_id'];
        $raia = intval($dados['swimlane_id']);
        $base = getListaPermissoesKanboard();
        
        if($coluna_destino != $coluna_origem){
            if($coluna_destino == '2' || $coluna_destino == 2){
                //criando permissao
                $tipo = $base[$raia - 1][0];
                $sql = "select * from kanboard_permissoes where entidade = 'coluna' and id_entidade = {$this->_projeto} and tipo = '$tipo' and grupo = $id_tarefa";
                $rows = query($sql);
                if(is_array($rows) && count($rows) === 0){
                    $this->incluirTarefa($id_tarefa, '', $tipo);
                }
            }
            else{
                //excluindo permissao
                if($coluna_destino != $coluna_origem && kanboard_permissoes::getCampoPermissao($id_tarefa, 'tipo') == $base[$raia - 1][0]){
                    $this->deletarTarefaBanco($id_tarefa);
                }
            }
        }
        */
        $coluna_destino = $dados['dst_column_id'];
        $id_tarefa = $dados['task_id'];
        $lista_status = array(
            1 => 'livre',
            2 => 'proibido',
            3 => 'restrito',
        );
        $sql = "update kanboard_permissoes_status set status = '{$lista_status[$coluna_destino]}' where id = $id_tarefa";
        query($sql);
        if($coluna_destino == 1){
            $sql = "delete from kanboard_permissoes where entidade = 'coluna' and grupo in (select grupo from kanboard_permissoes_status where id = $id_tarefa)
            and tipo in (select tipo from kanboard_permissoes_status where id = $id_tarefa) 
            and id_entidade in (select id from kanboard_colunas where projeto in (select id_entidade from kanboard_permissoes_status where id = $id_tarefa))";
            query($sql);
        }
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_raia WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_raia where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_raia set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', '', '', $id_tarefa);
        
        //cria o log
        $this->gerarLog('raia', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        $coluna_livre = array(
            'id' => 1,
            'etiqueta' => 'Liberado',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $coluna_proibido = array(
            'id' => 2,
            'etiqueta' => 'Proibido',
            'projeto' => $projeto,
            'posicao' => 2,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $coluna_restrito = array(
            'id' => 3,
            'etiqueta' => 'Restrito',
            'projeto' => $projeto,
            'posicao' => 3,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $raias_base = getListaPermissoesKanboard();
        foreach ($raias_base as $chave => $raia_atual){
            $temp = array(
                'id' => $chave + 1,
                'projeto' => $projeto,
                'etiqueta' => str_replace('{entidade}', 'Coluna', $raia_atual[1]),
                'posicao' => $chave + 1,
                'num_raias' => count($raias_base),
                'num_tarefas' => 0,
                'mostrar_totalizador' => false,
            );
            $ret['raias'][$chave + 1] = $temp;
            $ret['raias'][$chave + 1]['colunas'][1] = $coluna_livre;
            $ret['raias'][$chave + 1]['colunas'][2] = $coluna_proibido;
            $ret['raias'][$chave + 1]['colunas'][3] = $coluna_restrito;
            //$ret['raias'][1]['colunas'][$key + 1] = $coluna;
        }
        
        
        $sql = "select kanboard_permissoes_status.id, temp1.etiqueta, temp1.raia, 
case when kanboard_permissoes_status.status = 'livre' then 1 when kanboard_permissoes_status.status = 'proibido' then 2 when  kanboard_permissoes_status.status = 'restrito' then 3 end as coluna
from kanboard_permissoes_status join (
    SELECT *
	FROM kanboard_grupos
	CROSS JOIN ("
		. $this->gerarSqlSinistro() .
		") AS todas_permissoes) as temp1 on (kanboard_permissoes_status.grupo = temp1.id and kanboard_permissoes_status.tipo = temp1.campo) 
where kanboard_permissoes_status.entidade = 'coluna' and kanboard_permissoes_status.id_entidade  = {$this->_projeto}";
        $rows = query($sql);
        $posicoes = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'cor'  => 'yellow',
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $row['raia'],
                    'link_excluir' => '',
                    'link_editar' => $row['coluna'] == 1 ? '' : getLinkAjax('editarTarefa') . "&projeto={$this->_projeto}&objeto=permissoes_coluna",
                    'bt_editar' => false,
                    'coluna' => $row['coluna']
                );
                
                if(isset($posicoes[$row['raia']][$row['coluna']])){
                    $posicoes[$row['raia']][$row['coluna']]++;
                }
                else{
                    $posicoes[$row['raia']][$row['coluna']] = 1;
                }
                $temp['posicao'] = $posicoes[$row['raia']][$row['coluna']];
                
                $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['tarefas'][] = $temp;
                $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['num_tarefas']++;
                $ret['raias'][$row['raia']]['num_tarefas']++;
                
            }
        }
        return $ret;
    }
    
    protected function gerarSqlSinistro(){
        $campos = getListaPermissoesKanboard();
        $sql = array();
        foreach ($campos as $key => $value){
            $sql[] = " select '{$value[0]}' as campo, " . ($key+1) . " as raia ";
        }
        $ret = implode('union', $sql) ;
        return $ret;
    }
    
    protected function editarTarefa(){
        $tarefa = $_GET['tarefa'];
        $projeto = $_GET['projeto'];
        $link = getLinkAjax('salvar') . "&projeto={$this->_projeto}&tarefa=$tarefa&objeto=permissoes_coluna";
        $ret = '<div class="page-header">
    <h2>Gerenciar Colunas</h2>
</div>
<form method="post" action="' . $link . '" autocomplete="off">
    <div class="task-form-container">
        <div class="task-form-main-column" align="left">
            {form}
        </div>
                
        <div class="task-form-bottom">
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Save","orLabel":"or","cancelLabel":"cancel","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>
        </div>
    </div>
</form>';
        /*
        $form = new form01();
        $form->addCampo(array('campo' => 'sentido', 'id' => 'campo_sentido' ,'etiqueta' => 'Sentido' , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3));
        */
        $form = '';
        $sql = 'select * from kanboard_colunas where projeto = ' . $this->_projeto;
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados_edit = $this->getDadosEditarTarefa($projeto, $tarefa);
            foreach ($rows as $row){
                $form .= '
<label for="vehicle1">' . $row['etiqueta'] . '</label>
<input type="checkbox" id="campo' . $row['id'] . '" name="entidade[' . $row['id'] . ']" '  . ($dados_edit[$row['id']] ?? '') . '><br>';
            }
        }
        $ret = str_replace('{form}', $form, $ret);
        return $ret;
    }
    
    protected function getDadosEditarTarefa($projeto, $tarefa){
        $ret = array();
        $sql = "select id_entidade as id from kanboard_permissoes where entidade = 'coluna' and id_entidade in (select id from kanboard_colunas where projeto = $projeto) and tipo in (select tipo from kanboard_permissoes_status where id = $tarefa)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['id']] = 'checked';
            }
        }
        return $ret;
    }
    
    protected function salvarAjax(){
        $projeto = $_GET['projeto'];
        $tarefa = $_GET['tarefa'];
        $sql = "delete from kanboard_permissoes where grupo in (select grupo from kanboard_permissoes_status where id = $tarefa)
            and tipo in (select tipo from kanboard_permissoes_status where id = $tarefa) and entidade = 'coluna'
            and id_entidade in (select id from kanboard_colunas where projeto in (select id_entidade from kanboard_permissoes_status where id = $tarefa))";
        query($sql);
        $entidades = $_POST['entidade'];
        if(is_array($entidades) && count($entidades)){
            $sql = "select grupo, tipo from kanboard_permissoes_status where id = $tarefa";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $grupo = $rows[0]['grupo'];
                $tipo = $rows[0]['tipo'];
                foreach ($entidades as $chave => $valor){
                    $param = array(
                        'grupo' => $grupo,
                        'tipo' => $tipo,
                        'entidade' => 'coluna',
                        'id_entidade' => $chave,
                    );
                    $sql = montaSQL($param, 'kanboard_permissoes');
                    query($sql);
                }
            }
        }
        header('X-Ajax-Redirect: ' . $this->_linkReload);
    }
    
    protected function verificarDtModificacao($arg1, $arg2){
        return true;
    }
}

class gerenciar_permissoes_raia extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'permissoes_raia';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&projeto=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkCheck .= "&objeto=$objeto&projeto={$this->_projeto}";
        $this->_linkReload = getLink() . "permissoesRaias&projeto=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&projeto=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta raia: "' . kanboard_raia::getTituloRaia($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);
        
        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        $param = array(
            'grupo' => $etiqueta,
            'entidade' => 'raia',
            'tipo' => $raia,
            'id_entidade' => $this->_projeto,
        );
        $sql = montaSQL($param, 'kanboard_permissoes');
        query($sql);
    }
    
    protected function deletarTarefaBanco($id){
        $sql = "delete from kanboard_permissoes where id = $id";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function deletarPermissao($grupo, $id_permissao){
        $base = getListaPermissoesKanboard();
        $permissao = $base[$id_permissao][0];
        $entidade = 'raia';
        $sql = "delete from kanboard_permissoes where grupo = $grupo and tipo = '$permissao' and entidade = '$entidade'";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function moverTarefa($dados){
        /*
         $coluna_destino = $dados['dst_column_id'];
         $coluna_origem = $dados['src_column_id'];
         $id_tarefa = $dados['task_id'];
         $raia = intval($dados['swimlane_id']);
         $base = getListaPermissoesKanboard();
         
         if($coluna_destino != $coluna_origem){
         if($coluna_destino == '2' || $coluna_destino == 2){
         //criando permissao
         $tipo = $base[$raia - 1][0];
         $sql = "select * from kanboard_permissoes where entidade = 'coluna' and id_entidade = {$this->_projeto} and tipo = '$tipo' and grupo = $id_tarefa";
         $rows = query($sql);
         if(is_array($rows) && count($rows) === 0){
         $this->incluirTarefa($id_tarefa, '', $tipo);
         }
         }
         else{
         //excluindo permissao
         if($coluna_destino != $coluna_origem && kanboard_permissoes::getCampoPermissao($id_tarefa, 'tipo') == $base[$raia - 1][0]){
         $this->deletarTarefaBanco($id_tarefa);
         }
         }
         }
         */
        $coluna_destino = $dados['dst_column_id'];
        $id_tarefa = $dados['task_id'];
        $lista_status = array(
            1 => 'livre',
            2 => 'proibido',
            3 => 'restrito',
        );
        $sql = "update kanboard_permissoes_status set status = '{$lista_status[$coluna_destino]}' where id = $id_tarefa";
        query($sql);
        if($coluna_destino == 1){
            $sql = "delete from kanboard_permissoes where entidade = 'raia' and grupo in (select grupo from kanboard_permissoes_status where id = $id_tarefa)
            and tipo in (select tipo from kanboard_permissoes_status where id = $id_tarefa)
            and id_entidade in (select id from kanboard_raia where projeto in (select id_entidade from kanboard_permissoes_status where id = $id_tarefa))";
            query($sql);
        }
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_raia WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_raia where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_raia set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', '', '', $id_tarefa);
        
        //cria o log
        $this->gerarLog('raia', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        $coluna_livre = array(
            'id' => 1,
            'etiqueta' => 'Liberado',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $coluna_proibido = array(
            'id' => 2,
            'etiqueta' => 'Proibido',
            'projeto' => $projeto,
            'posicao' => 2,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $coluna_restrito = array(
            'id' => 3,
            'etiqueta' => 'Restrito',
            'projeto' => $projeto,
            'posicao' => 3,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
            'mostrar_totalizador' => false,
        );
        
        $raias_base = getListaPermissoesKanboard();
        foreach ($raias_base as $chave => $raia_atual){
            $temp = array(
                'id' => $chave + 1,
                'projeto' => $projeto,
                'etiqueta' => str_replace('{entidade}', 'Coluna', $raia_atual[1]),
                'posicao' => $chave + 1,
                'num_raias' => count($raias_base),
                'num_tarefas' => 0,
                'mostrar_totalizador' => false,
            );
            $ret['raias'][$chave + 1] = $temp;
            $ret['raias'][$chave + 1]['colunas'][1] = $coluna_livre;
            $ret['raias'][$chave + 1]['colunas'][2] = $coluna_proibido;
            $ret['raias'][$chave + 1]['colunas'][3] = $coluna_restrito;
            //$ret['raias'][1]['colunas'][$key + 1] = $coluna;
        }
        
        
        $sql = "select kanboard_permissoes_status.id, temp1.etiqueta, temp1.raia,
case when kanboard_permissoes_status.status = 'livre' then 1 when kanboard_permissoes_status.status = 'proibido' then 2 when  kanboard_permissoes_status.status = 'restrito' then 3 end as coluna
from kanboard_permissoes_status join (
    SELECT *
	FROM kanboard_grupos
	CROSS JOIN ("
            . $this->gerarSqlSinistro() .
            ") AS todas_permissoes) as temp1 on (kanboard_permissoes_status.grupo = temp1.id and kanboard_permissoes_status.tipo = temp1.campo)
where kanboard_permissoes_status.entidade = 'raia' and kanboard_permissoes_status.id_entidade  = {$this->_projeto}";
        $rows = query($sql);
        $posicoes = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'cor'  => 'yellow',
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $row['raia'],
                    'link_excluir' => '',
                    'link_editar' => $row['coluna'] == 1 ? '' : getLinkAjax('editarTarefa') . "&projeto={$this->_projeto}&objeto=permissoes_raia",
                    'bt_editar' => false,
                    'coluna' => $row['coluna']
                );
                
                if(isset($posicoes[$row['raia']][$row['coluna']])){
                    $posicoes[$row['raia']][$row['coluna']]++;
                }
                else{
                    $posicoes[$row['raia']][$row['coluna']] = 1;
                }
                $temp['posicao'] = $posicoes[$row['raia']][$row['coluna']];
                
                $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['tarefas'][] = $temp;
                $ret['raias'][$row['raia']]['colunas'][$row['coluna']]['num_tarefas']++;
                $ret['raias'][$row['raia']]['num_tarefas']++;
                
            }
        }
        return $ret;
    }
    
    protected function gerarSqlSinistro(){
        $campos = getListaPermissoesKanboard();
        $sql = array();
        foreach ($campos as $key => $value){
            $sql[] = " select '{$value[0]}' as campo, " . ($key+1) . " as raia ";
        }
        $ret = implode('union', $sql) ;
        return $ret;
    }
    
    protected function editarTarefa(){
        $tarefa = $_GET['tarefa'];
        $projeto = $_GET['projeto'];
        $link = getLinkAjax('salvar') . "&projeto={$this->_projeto}&tarefa=$tarefa&objeto=permissoes_raia";
        $ret = '<div class="page-header">
    <h2>Gerenciar Raias</h2>
</div>
<form method="post" action="' . $link . '" autocomplete="off">
    <div class="task-form-container">
        <div class="task-form-main-column" align="left">
            {form}
        </div>
    
        <div class="task-form-bottom">
            <div class="js-submit-buttons" data-params=' . "'" . '{"submitLabel":"Save","orLabel":"or","cancelLabel":"cancel","color":"blue","tabindex":null,"disabled":false}'. "'" . '></div>
        </div>
    </div>
</form>';
        /*
         $form = new form01();
         $form->addCampo(array('campo' => 'sentido', 'id' => 'campo_sentido' ,'etiqueta' => 'Sentido' , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3));
         */
        $form = '';
        $sql = 'select * from kanboard_raia where projeto = ' . $this->_projeto;
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados_edit = $this->getDadosEditarTarefa($projeto, $tarefa);
            foreach ($rows as $row){
                $form .= '
<label for="vehicle1">' . $row['etiqueta'] . '</label>
<input type="checkbox" id="campo' . $row['id'] . '" name="entidade[' . $row['id'] . ']" '  . ($dados_edit[$row['id']] ?? '') . '><br>';
            }
        }
        $ret = str_replace('{form}', $form, $ret);
        return $ret;
    }
    
    protected function getDadosEditarTarefa($projeto, $tarefa){
        $ret = array();
        $sql = "select id_entidade as id from kanboard_permissoes where entidade = 'raia' and id_entidade in (select id from kanboard_raia where projeto = $projeto) and tipo in (select tipo from kanboard_permissoes_status where id = $tarefa)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['id']] = 'checked';
            }
        }
        return $ret;
    }
    
    protected function salvarAjax(){
        $projeto = $_GET['projeto'];
        $tarefa = $_GET['tarefa'];
        $sql = "delete from kanboard_permissoes where grupo in (select grupo from kanboard_permissoes_status where id = $tarefa)
            and tipo in (select tipo from kanboard_permissoes_status where id = $tarefa) and entidade = 'raia'
            and id_entidade in (select id from kanboard_raia where projeto in (select id_entidade from kanboard_permissoes_status where id = $tarefa))";
        query($sql);
        $entidades = $_POST['entidade'];
        if(is_array($entidades) && count($entidades)){
            $sql = "select grupo, tipo from kanboard_permissoes_status where id = $tarefa";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $grupo = $rows[0]['grupo'];
                $tipo = $rows[0]['tipo'];
                foreach ($entidades as $chave => $valor){
                    $param = array(
                        'grupo' => $grupo,
                        'tipo' => $tipo,
                        'entidade' => 'raia',
                        'id_entidade' => $chave,
                    );
                    $sql = montaSQL($param, 'kanboard_permissoes');
                    query($sql);
                }
            }
        }
        header('X-Ajax-Redirect: ' . $this->_linkReload);
    }
    
    protected function verificarDtModificacao($arg1, $arg2){
        return true;
    }
}

class gerenciar_permissoes_movimentacao extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'movimentacao';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&projeto=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&projeto=$projeto";
        $this->_linkCheck .= "&objeto=$objeto&projeto={$this->_projeto}";
        $this->_linkReload = getLink() . "permissoesProjeto&projeto=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&projeto=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta raia: "' . kanboard_raia::getTituloRaia($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'];
        $raia = $_GET['raia'];
        
        $this->incluirTarefa($etiqueta, $coluna, $raia);
        
        header('X-Ajax-Redirect: ' . $this->_linkReload);
        //header('Location: ' . '/intranet4/index.php?menu=testes.mercado_pago.index');
    }
    
    
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);
        
        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        $param = array(
            'grupo' => $etiqueta,
            'entidade' => 'raia',
            'tipo' => $raia,
            'id_entidade' => $this->_projeto,
        );
        $sql = montaSQL($param, 'kanboard_permissoes');
        query($sql);
    }
    
    protected function deletarTarefaBanco($id){
        $sql = "delete from kanboard_permissoes where id = $id";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function deletarPermissao($grupo, $id_permissao){
        $base = getListaPermissoesKanboard();
        $permissao = $base[$id_permissao][0];
        $entidade = 'raia';
        $sql = "delete from kanboard_permissoes where grupo = $grupo and tipo = '$permissao' and entidade = '$entidade'";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function moverTarefa($dados){
        $coluna_destino = $dados['dst_column_id'];
        $coluna_origem = $dados['src_column_id'];
        $id_tarefa = $dados['task_id'];
        $raia = intval($dados['swimlane_id']);
        /*
        $num_colunas = 1;
        $sql = "select count(*) as total from kanboard_colunas where projeto = {$this->_projeto} group by projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $num_colunas = $rows[0]['total'];
        }
        
        $num_raias = 1;
        $sql = "select count(*) as total from kanboard_raia where projeto = {$this->_projeto} group by projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $num_raias = $rows[0]['total'];
        }
        */
        /*
        $traduzir_raia = array();
        if($num_colunas > 1){
            $traduzir_raia = array('coluna' => 1);
            if($num_raias > 1){
                $traduzir_raia['raia'] = 2;
            }
        }
        else{
            if($num_raias > 1){
                $traduzir_raia = array('raia' => 1);
            }
        }
        
        */
        
        $traduzir_coluna = array(
            1 => 'livre',
            2 => 'entrada',
            3 => 'saida',
            4 => 'especifico',
        );
        
        $sql = "update kanboard_permissoes_movimentacao_status set status = '{$traduzir_coluna[$coluna_destino]}' where id = $id_tarefa";
        query($sql);
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_raia WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_raia where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_raia set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', '', '', $id_tarefa);
        
        //cria o log
        $this->gerarLog('raia', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        $temp = new tabela01();
        unset($temp);
        addPortaljavaScript('
function minhaFuncao() {
    alert(\'teste2\');
}');
     
        addPortaljavaScript("
function incluirLinha() {

    var t = $('#tabPermissoes').DataTable();

    var e = document.getElementById(\"campo_grupo\");
    var value = e.value;
    var grupo = e.options[e.selectedIndex].text;

    e = document.getElementById(\"campo_entidade\");
    value = e.value;
    var entidade = e.options[e.selectedIndex].text;

    e = document.getElementById(\"campo_sentido\");
    value = e.value;
    var sentido = e.options[e.selectedIndex].text;
	//var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";

	//var hora = \"<input  type='text' name='formOS[tarefas][horas][]' value='' style='width:100%;text-align: right;' id='\"+valor+\"tabelacampohora' class='form-control  form-control-sm'          >\";
    //var texto = \"<input  type='text' name='formOS[tarefas][descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
	t.row.add( [grupo, entidade, sentido] ).draw( false );
    
    //valor = valor + 1;
    //$('#myInput').attr('onclick', 'incluiRat('+valor+');' );
}

function callAjax(id_origem){
    var e = document.getElementById(\"campo_grupo\");
    var grupo_value = e.value;
    var grupo = e.options[e.selectedIndex].text;

    e = document.getElementById(\"campo_entidade\");
    var entidade_value = e.value;
    var entidade = e.options[e.selectedIndex].text;

    e = document.getElementById(\"campo_sentido\");
    var sentido_value = e.value;
    var sentido = e.options[e.selectedIndex].text;
	//var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";

    var link = '" . getLinkAjax('incluirPermissaoAjax') . "' + '&grupo=' + grupo_value + '&entidade=' + entidade_value + '&sentido=' + sentido_value + '&origem=' + id_origem;

    $.get(link, function(retorno){
        if(retorno != 0 && retorno != '0'){
            var t = $('#tabPermissoes').DataTable();
            var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirPermissao(\" + retorno + \", this);'>Excluir</button>\";
            t.row.add( [grupo, entidade, sentido, bt] ).draw( false );
        }
        

});}

function excluirPermissao(id, e){
    var link = '" . getLinkAjax('excluirPermissaoAjax') . "' + '&id=' + id ;
    $.get(link, function(retorno){});
    var t = $('#tabPermissoes').DataTable();
	t.row( $(e).parents('tr') ).remove().draw();
}");
        
        addPortalJS('kanboard', 'board-task-click.js', 'I');
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        
        $coluna_liberado = array(
            'id' => 1,
            'etiqueta' => 'Liberado',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        
        $coluna_entrada = array(
            'id' => 2,
            'etiqueta' => 'Somente Entrada',
            'projeto' => $projeto,
            'posicao' => 2,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        
        $coluna_saida = array(
            'id' => 3,
            'etiqueta' => 'Somente Saida',
            'projeto' => $projeto,
            'posicao' => 3,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        
        $coluna_especifico = array(
            'id' => 4,
            'etiqueta' => 'Específico',
            'projeto' => $projeto,
            'posicao' => 4,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        /*
         * select cruzada.origem, cruzada.destino, case when permissoes.total is null then 'livre' else 'outro' end campoTeste from (select distinct colunas1.id as origem, colunas2.id as destino from (select id from kanboard_colunas where projeto = 1) colunas1 cross join (select id from kanboard_colunas where projeto = 1) colunas2) as cruzada left join (select origem, destino, count(*) as total from kanboard_permissoes_movimentacao where tipo = 'coluna' and origem in (select id from kanboard_colunas where projeto = 1) and destino in (select id from kanboard_colunas where projeto = 1) GROUP by origem, destino) as permissoes on (cruzada.origem = permissoes.origem and cruzada.destino = permissoes.destino)
         */
        $num_colunas = 1;
        $sql = "select count(*) as total from kanboard_colunas where projeto = {$this->_projeto} group by projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $num_colunas = $rows[0]['total'];
        }
        
        $num_raias = 1;
        $sql = "select count(*) as total from kanboard_raia where projeto = {$this->_projeto} group by projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $num_raias = $rows[0]['total'];
        }
        
        if($num_colunas > 1){
            $raia_padrao = array(
                'id' => 1,
                'projeto' => $projeto,
                'etiqueta' => 'Movimentação entre Colunas',
                'posicao' => 1,
                'num_raias' =>$num_raias > 1 ? 2 : 1,
                'num_tarefas' => 0,
                'num_abertas' => 0,
            );
            
            $ret['raias'][1] = $raia_padrao;
            $ret['raias'][1]['colunas'][1] = $coluna_liberado;
            $ret['raias'][1]['colunas'][2] = $coluna_entrada;
            $ret['raias'][1]['colunas'][3] = $coluna_saida;
            $ret['raias'][1]['colunas'][4] = $coluna_especifico;
        }
        
        if($num_raias > 1){
            $pos = $num_colunas > 1 ? 2 : 1;
            $raia_padrao = array(
                'id' => $pos,
                'projeto' => $projeto,
                'etiqueta' => 'Movimentação entre Raias',
                'posicao' => $pos,
                'num_raias' =>$pos,
                'num_tarefas' => 0,
                'num_abertas' => 0,
            );
            
            $ret['raias'][$pos] = $raia_padrao;
            $ret['raias'][$pos]['colunas'][1] = $coluna_liberado;
            $ret['raias'][$pos]['colunas'][2] = $coluna_entrada;
            $ret['raias'][$pos]['colunas'][3] = $coluna_saida;
            $ret['raias'][$pos]['colunas'][4] = $coluna_especifico;
        }
        $traduzir_raia = array();
        if($num_colunas > 1){
            $traduzir_raia = array('coluna' => 1);
            if($num_raias > 1){
                $traduzir_raia['raia'] = 2;
            }
        }
        else{
            if($num_raias > 1){
                $traduzir_raia = array('raia' => 1);
            }
        }
        
        $traduzir_coluna = array(
            'livre' => 1,
            'entrada' => 2,
            'saida' => 3,
            'especifico' => 4,
        );
        
        $sql = array();
        if($num_colunas > 1){
            $sql[] = "select kanboard_permissoes_movimentacao_status.*, kanboard_colunas.etiqueta from kanboard_permissoes_movimentacao_status join kanboard_colunas on (kanboard_permissoes_movimentacao_status.id_entidade = kanboard_colunas.id) where entidade = 'coluna' and id_entidade in (select id from kanboard_colunas where projeto = {$this->_projeto})";
        }
        if($num_raias > 1){
            $sql[] = "select kanboard_permissoes_movimentacao_status.*, kanboard_raia.etiqueta from kanboard_permissoes_movimentacao_status join kanboard_raia on (kanboard_permissoes_movimentacao_status.id_entidade = kanboard_raia.id) where entidade = 'raia' and id_entidade in (select id from kanboard_raia where projeto = {$this->_projeto})";
        }
        
        $sql = implode(' UNION ', $sql);
        $rows = query($sql);
        $posicoes = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'cor'  => 'yellow',
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => $traduzir_raia[$row['entidade']],
                    'coluna' => $traduzir_coluna[$row['status']],
                    'link_excluir' => '',
                    'link_editar' => $row['status'] == 'especifico' ? (getLinkAjax('editarTarefa') . '&objeto=movimentacao&projeto=' . $projeto) : '',
                    'bt_editar' => false,
                );
                
                if(isset($posicoes[$traduzir_raia[$row['entidade']]][$traduzir_coluna[$row['status']]])){
                    $posicoes[$traduzir_raia[$row['entidade']]][$traduzir_coluna[$row['status']]]++;
                }
                else{
                    $posicoes[$traduzir_raia[$row['entidade']]][$traduzir_coluna[$row['status']]] = 1;
                }
                $temp['posicao'] = $posicoes[$traduzir_raia[$row['entidade']]][$traduzir_coluna[$row['status']]];
                
                $ret['raias'][$traduzir_raia[$row['entidade']]]['colunas'][$traduzir_coluna[$row['status']]]['tarefas'][] = $temp;
                $ret['raias'][$traduzir_raia[$row['entidade']]]['colunas'][$traduzir_coluna[$row['status']]]['num_abertas']++;
                $ret['raias'][$traduzir_raia[$row['entidade']]]['num_abertas']++;
                
            }
        }
        return $ret;
    }
    
    protected function gerarOpcoesGrupos(){
        $ret = '';
        $sql = "select id, etiqueta from kanboard_grupos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $temp = array();
            foreach ($rows as $row){
                $temp[] = $row['id'] . '=' . $row['etiqueta'];
            }
            $ret = implode(';', $temp);
        }
        return $ret;
    }
    
    protected function editarTarefa(){
        $ret = '<div align="center">
                    {form}
                </div>';
        $form = new form01();
        $opcoes_grupos = $this->gerarOpcoesGrupos();
        $id_status = $_GET['tarefa'];
        $opcoes_sentido = 'vindo=vindo de;indo=indo para';
        $form->addCampo(array('campo' => 'grupo'  , 'id' => 'campo_grupo'   ,'etiqueta' => 'Grupo'   , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3, 'opcoes' => $opcoes_grupos));
        $form->addCampo(array('campo' => 'sentido', 'id' => 'campo_sentido' ,'etiqueta' => 'Sentido' , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3, 'opcoes' => $opcoes_sentido));
        $form->addCampo(array('campo' => 'coluna' , 'id' => 'campo_entidade','etiqueta' => 'Coluna'  , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3, 'opcoes' => $this->criarOpcoesOrigemDestino($id_status)));
        
        $param = [];
        $param['texto'] = 'Incluir Permissão';
        $param['onclick'] = "callAjax($id_status);";
        $param['id'] = 'myInput';
        $bt = formbase01::formBotao($param);
        $ret = str_replace('{form}', $form . $bt, $ret);
        $ret .= '<div align="center">
                    {tabela}
                </div>';
        
        $param = [];
        $param['paginacao'] = false;
        $param['scroll'] 	= false;
        $param['scrollX'] 	= false;
        $param['scrollY'] 	= false;
        $param['ordenacao'] = false;
        $param['filtro']	= false;
        $param['info']		= false;
        $param['id']		= 'tabPermissoes';
        $param['width']		= '100%';
        $tab = new tabela01($param);
        
        
        $tab->addColuna(array('campo' => 'grupo'	, 'etiqueta' => 'Grupo'		, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'sentido'  , 'etiqueta' => 'Sentido'	, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'coluna'	, 'etiqueta' => 'Coluna'	, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'bt'  , 'etiqueta' => ''	, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        
        $dados = $this->getDadosTabelaAutrizacoes($id_status    );
        $tab->setDados($dados);
        
        
        
        $ret = str_replace('{tabela}', $tab . '', $ret);
        return $ret;
    }
    
    protected function getDadosTabelaAutrizacoes($id_status){
        $ret = array();
        $sql = "select pm.*, kg.etiqueta, 'indo para' as sentido, temp1.etiqueta as etiqueta_entidade from kanboard_permissoes_movimentacao as pm join kanboard_grupos as kg on (pm.grupo = kg.id) join (select id, 'coluna' as campo_esp, etiqueta from kanboard_colunas union select id, 'raia' as campo_esp, etiqueta from kanboard_raia) temp1 on (pm.tipo = temp1.campo_esp and pm.destino = temp1.id) where pm.origem in (select id_entidade from kanboard_permissoes_movimentacao_status where id = $id_status) 
union
                select pm.*, kg.etiqueta, 'vindo de' as sentido, temp1.etiqueta as etiqueta_entidade from kanboard_permissoes_movimentacao as pm join kanboard_grupos as kg on (pm.grupo = kg.id) join (select id, 'coluna' as campo_esp, etiqueta from kanboard_colunas union select id, 'raia' as campo_esp, etiqueta from kanboard_raia) temp1 on (pm.tipo = temp1.campo_esp and pm.origem = temp1.id) where pm.destino in (select id_entidade from kanboard_permissoes_movimentacao_status where id = $id_status) ";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['grupo'] = $row['etiqueta'];
                $temp['coluna'] = $row['etiqueta_entidade'];
                $temp['sentido'] = $row['sentido'];
                $temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirPermissao({$row['id']}, this);'>Excluir</button>";
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    protected function criarOpcoesOrigemDestino($id_status){
        $ret = array();
        $sql = "select id_entidade, entidade from kanboard_permissoes_movimentacao_status where id = $id_status";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $entidade = $rows[0]['entidade'];
            $id_entidade = $rows[0]['id_entidade'];
            
            $tabela = $entidade == 'raia' ? 'kanboard_raia' : 'kanboard_colunas';
            
            $sql = "select * from $tabela where projeto in (select projeto from $tabela where id = $id_entidade) and id != $id_entidade";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[$row['id']] = $row['etiqueta'];
                }
            }
        }
        return $ret;
    }
    
    protected function verificarDtModificacao($arg1, $arg2){
        return true;
    }
}

class gerenciar_hierarquia extends kanboard{
    function __construct($projeto){
        parent::__construct($projeto);
        $objeto = 'hierarquia';
        $this->_linkAjaxFormulario .= "&objeto=$objeto&raia=1&grupo=$projeto";
        $this->_linkAjaxMover .= "&objeto=$objeto&grupo=$projeto";
        $this->_linkAjaxSalvar .= "&objeto=$objeto&grupo=$projeto";
        $this->_linkCheck .= "&objeto=$objeto&grupo={$this->_projeto}";
        $this->_linkReload = getLink() . "permissoesProjeto&grupo=$projeto";
        $this->_linkExcluir .= "&objeto=$objeto&grupo=$projeto";
    }
    
    protected function checarPermissaoMovimentar($coluna_origem, $coluna_destino, $raia_destino, $tarefa){
        return true;
    }
    
    protected function getOpercaoClicarTarefas(){
        return 'janela';
    }
    
    protected function verificarPermissao(){
        return true;
    }
    
    protected function verificarPermissaoLocal($operacao, $projeto, $coluna, $raia, $usuario){
        return true;
    }
    
    protected function getTabelaEntidades(){
        return '';
    }
    
    protected function excluirTarefaForm(){
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalJS('kanboard', 'dropdown.js', 'I');
        
        addPortalCSS('', 'vendor.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'app.min.css', 'I');
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
            'titulo' => 'Excluir Coluna',
            'corpo' => 'Você realmente quer excluir esta raia: "' . kanboard_raia::getTituloRaia($tarefa) . '"?',
        );
        
        $ret = kanboard_formularios::formularioExcluirTarefa($param_janela, $opcoes_ajax);
        
        return $ret;
    }
    
    protected function salvarAjax(){
        $coluna = $_GET['coluna'];
        $etiqueta = $_POST['etiqueta'];
        $raia = $_GET['raia'];
        
        $this->incluirTarefa($etiqueta, $coluna, $raia);
        
        header('X-Ajax-Redirect: ' . $this->_linkReload);
        //header('Location: ' . '/intranet4/index.php?menu=testes.mercado_pago.index');
    }
    
    
    
    protected function formularioAjax(){
        $ret = '';
        $coluna = $_GET['coluna'];
        $raia = $_GET['raia'];
        addPortalJS('kanboard', 'app.min.js', 'I');
        addPortalJS('kanboard', 'vendor.min.js', 'I');
        addPortalCSS('', 'app.min.css', 'I');
        addPortalCSS('', 'print.min.css', 'I');
        addPortalCSS('', 'vendor.min.css', 'I');
        addPortalJS('kanboard', 'Task.js', 'I');
        
        $ret = kanboard_formularios::formularioTarefaSemEntidade($this->_linkAjaxSalvar, $coluna, $raia);
        
        return $ret;
    }
    
    protected function incluirTarefa($etiqueta, $coluna, $raia){
        $param = array(
            'grupo' => $etiqueta,
            'entidade' => 'raia',
            'tipo' => $raia,
            'id_entidade' => $this->_projeto,
        );
        $sql = montaSQL($param, 'kanboard_permissoes');
        query($sql);
    }
    
    protected function deletarTarefaBanco($id){
        $sql = "delete from kanboard_permissoes where id = $id";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function deletarPermissao($grupo, $id_permissao){
        $base = getListaPermissoesKanboard();
        $permissao = $base[$id_permissao][0];
        $entidade = 'raia';
        $sql = "delete from kanboard_permissoes where grupo = $grupo and tipo = '$permissao' and entidade = '$entidade'";
        query($sql);
        //$this->gerarLog('raia', 'excluir', $id);
    }
    
    protected function moverTarefa($dados){
        $coluna_destino = $dados['dst_column_id'];
        $coluna_origem = $dados['src_column_id'];
        $id_grupo = $dados['task_id'];
        
        if($coluna_destino != $coluna_origem){
            $sql = '';
            if($coluna_origem == 2){
                $sql = "delete from kanboard_hierarquia where superior = $id_grupo and subordinado = {$this->_projeto}";
            }
            elseif ($coluna_origem == 3){
                $sql = "delete from kanboard_hierarquia where subordinado = $id_grupo and superior = {$this->_projeto}";
            }
            if(!empty($sql)){
                query($sql);
            }
            $sql = '';
            if ($coluna_destino == 2){
                $sql = "insert into kanboard_hierarquia (id, superior, subordinado) values (null, $id_grupo, {$this->_projeto})";
            }
            elseif ($coluna_destino == 3){
                $sql = "insert into kanboard_hierarquia (id, superior, subordinado) values (null, {$this->_projeto}, $id_grupo)";
            }
            if(!empty($sql)){
                query($sql);
            }
        }
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna, $raia){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_raia WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_raia where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos, $raia){
        //move a tarefa no banco
        $sql = "update kanboard_raia set posicao = $pos where id = $id_tarefa";
        query($sql);
        
        //atualiza o projeto
        $this->atualizarProjeto('', '', '', $id_tarefa);
        
        //cria o log
        $this->gerarLog('raia', 'mover', $id_tarefa, "posicao $pos");
    }
    
    protected function reordenarTarefas($coluna, $raia){
        $sql = "UPDATE kanboard_raia
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    function index($param_exterior = array()){
        $param = array();
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "grupos');";
        $botao['texto']		= 'Voltar';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        return parent::index($param);
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
        
        $coluna_sem = array(
            'id' => 1,
            'etiqueta' => 'Sem Relação',
            'projeto' => $projeto,
            'posicao' => 1,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        
        $coluna_sup = array(
            'id' => 2,
            'etiqueta' => 'Superiores',
            'projeto' => $projeto,
            'posicao' => 2,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        
        $coluna_sub = array(
            'id' => 3,
            'etiqueta' => 'Subordinados',
            'projeto' => $projeto,
            'posicao' => 3,
            'limite' => 0,
            'descricao' => '',
            'num_abertas' => 0,
            'num_fechadas' => 0,
            'num_tarefas' => 0,
            'link_nova' => '',
            'esconder' => false,
            'tarefas' => array(),
            'link_fechar' => '',
            'bt_fechar_tarefas' => false,
        );
        
        $raia_padrao = array(
            'id' => 1,
            'projeto' => $projeto,
            'etiqueta' => 'padrao',
            'posicao' => 1,
            'num_raias' => 1,
            'num_tarefas' => 0,
        );
        
        $ret['raias'][1] = $raia_padrao;
        $ret['raias'][1]['colunas'][1] = $coluna_sem;
        $ret['raias'][1]['colunas'][2] = $coluna_sup;
        $ret['raias'][1]['colunas'][3] = $coluna_sub;
        
        
        $sql = "
        select id, etiqueta, 1 as coluna from kanboard_grupos where id not in (select superior from kanboard_hierarquia where subordinado = $projeto) and id not in (select subordinado from kanboard_hierarquia where superior = $projeto) and id != $projeto
        union select id, etiqueta, 2 as coluna from kanboard_grupos where id in (select superior from kanboard_hierarquia where subordinado = $projeto) and id != $projeto
        union select id, etiqueta, 3 as coluna from kanboard_grupos where id in (select subordinado from kanboard_hierarquia where superior = $projeto) and id != $projeto";
        
        $rows = query($sql);
        $posicoes = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'id' => $row['id'],
                    'cor'  => 'yellow',
                    'dono' => '',
                    'categoria' => '',
                    'data_limite' => '',
                    'etiqueta' => $row['etiqueta'],
                    'responsavel' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto,
                    'raia' => 1,
                    'coluna' => $row['coluna'],
                    'link_excluir' => '',
                    'link_editar' => '',
                    'bt_editar' => false,
                );
                
                if(isset($posicoes[$row['coluna']])){
                    $posicoes[$row['coluna']]++;
                }
                else{
                    $posicoes[$row['coluna']] = 1;
                }
                $temp['posicao'] = $posicoes[$row['coluna']];
                
                $ret['raias'][1]['colunas'][$row['coluna']]['tarefas'][] = $temp;
                $ret['raias'][1]['colunas'][$row['coluna']]['num_tarefas']++;
                $ret['raias'][1]['num_tarefas']++;
                
            }
        }
        return $ret;
    }
    
    protected function gerarOpcoesGrupos(){
        $ret = '';
        $sql = "select id, etiqueta from kanboard_grupos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $temp = array();
            foreach ($rows as $row){
                $temp[] = $row['id'] . '=' . $row['etiqueta'];
            }
            $ret = implode(';', $temp);
        }
        return $ret;
    }
    
    protected function editarTarefa(){
        $ret = '<div align="center">
                    {form}
                </div>';
        $form = new form01();
        $opcoes_grupos = $this->gerarOpcoesGrupos();
        $id_status = $_GET['tarefa'];
        $opcoes_sentido = 'vindo=vindo de;indo=indo para';
        $form->addCampo(array('campo' => 'grupo'  , 'id' => 'campo_grupo'   ,'etiqueta' => 'Grupo'   , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3, 'opcoes' => $opcoes_grupos));
        $form->addCampo(array('campo' => 'sentido', 'id' => 'campo_sentido' ,'etiqueta' => 'Sentido' , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3, 'opcoes' => $opcoes_sentido));
        $form->addCampo(array('campo' => 'coluna' , 'id' => 'campo_entidade','etiqueta' => 'Coluna'  , 'tipo' => 'A', 'width' => '5'  , 'posicao' => 'C', 'largura' => 3, 'opcoes' => $this->criarOpcoesOrigemDestino($id_status)));
        
        $param = [];
        $param['texto'] = 'Incluir Permissão';
        $param['onclick'] = "callAjax($id_status);";
        $param['id'] = 'myInput';
        $bt = formbase01::formBotao($param);
        $ret = str_replace('{form}', $form . $bt, $ret);
        $ret .= '<div align="center">
                    {tabela}
                </div>';
        
        $param = [];
        $param['paginacao'] = false;
        $param['scroll'] 	= false;
        $param['scrollX'] 	= false;
        $param['scrollY'] 	= false;
        $param['ordenacao'] = false;
        $param['filtro']	= false;
        $param['info']		= false;
        $param['id']		= 'tabPermissoes';
        $param['width']		= '100%';
        $tab = new tabela01($param);
        
        
        $tab->addColuna(array('campo' => 'grupo'	, 'etiqueta' => 'Grupo'		, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'sentido'  , 'etiqueta' => 'Sentido'	, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'coluna'	, 'etiqueta' => 'Coluna'	, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'bt'  , 'etiqueta' => ''	, 'tipo' => 'T', 'width' => '30', 'posicao' => 'C'));
        
        $dados = $this->getDadosTabelaAutrizacoes($id_status    );
        $tab->setDados($dados);
        
        
        
        $ret = str_replace('{tabela}', $tab . '', $ret);
        return $ret;
    }
    
    protected function getDadosTabelaAutrizacoes($id_status){
        $ret = array();
        $sql = "select pm.*, kg.etiqueta, 'indo para' as sentido, temp1.etiqueta as etiqueta_entidade from kanboard_permissoes_movimentacao as pm join kanboard_grupos as kg on (pm.grupo = kg.id) join (select id, 'coluna' as campo_esp, etiqueta from kanboard_colunas union select id, 'raia' as campo_esp, etiqueta from kanboard_raia) temp1 on (pm.tipo = temp1.campo_esp and pm.destino = temp1.id) where pm.origem in (select id_entidade from kanboard_permissoes_movimentacao_status where id = $id_status)
union
                select pm.*, kg.etiqueta, 'vindo de' as sentido, temp1.etiqueta as etiqueta_entidade from kanboard_permissoes_movimentacao as pm join kanboard_grupos as kg on (pm.grupo = kg.id) join (select id, 'coluna' as campo_esp, etiqueta from kanboard_colunas union select id, 'raia' as campo_esp, etiqueta from kanboard_raia) temp1 on (pm.tipo = temp1.campo_esp and pm.origem = temp1.id) where pm.destino in (select id_entidade from kanboard_permissoes_movimentacao_status where id = $id_status) ";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['grupo'] = $row['etiqueta'];
                $temp['coluna'] = $row['etiqueta_entidade'];
                $temp['sentido'] = $row['sentido'];
                $temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirPermissao({$row['id']}, this);'>Excluir</button>";
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    protected function criarOpcoesOrigemDestino($id_status){
        $ret = array();
        $sql = "select id_entidade, entidade from kanboard_permissoes_movimentacao_status where id = $id_status";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $entidade = $rows[0]['entidade'];
            $id_entidade = $rows[0]['id_entidade'];
            
            $tabela = $entidade == 'raia' ? 'kanboard_raia' : 'kanboard_colunas';
            
            $sql = "select * from $tabela where projeto in (select projeto from $tabela where id = $id_entidade) and id != $id_entidade";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[$row['id']] = $row['etiqueta'];
                }
            }
        }
        return $ret;
    }
    
    protected function verificarDtModificacao($arg1, $arg2){
        return true;
    }
}