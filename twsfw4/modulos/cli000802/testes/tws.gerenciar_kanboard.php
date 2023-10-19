<?php
class gerenciar_kanboard extends kanboard{
    var $funcoes_publicas = array(
        'index' 	=> true,
        'ajax'      => true,
        'excluirProjeto' => true,
        'editarProjeto' => true,
        'salvarProjeto' => true,
        'editarColunasProjeto' => true,
    );
    
    function __construct(){
        parent::__construct(0);
    }
    
    protected function checarAjax(){
        $this->arrumarPassagem();
        return parent::checarAjax();
    }
    
    protected function getDadosTabela($projeto){
        $ret['id'] = $projeto;
        $ret['projeto'] = '';
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
        );
        $ret['colunas'][1] = $coluna;
        $sql = "select * from kanboard_colunas where projeto = $projeto";
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
                    'tags' => '',
                    'ativo' => true,
                    'arrastavel' => true,
                    'projeto' => $projeto
                );
                $ret['colunas'][1]['num_tarefas']++;
                $ret['colunas'][1]['tarefas'][] = $temp;
            }
        }
        return $ret;
    }
    
    
    
    function index($param = array()){
        $this->geraScriptConfirmacao();
        $param = array();
        $param['titulo'] = 'Gerenciar Projetos Kanboard';
        $tabela_projetos = new tabela01($param);
        $tabela_projetos->addColuna(array('campo' => 'id'		, 'etiqueta' => 'ID'		,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $tabela_projetos->addColuna(array('campo' => 'etiqueta'	, 'etiqueta' => 'Etiqueta'  ,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        
        $param = array();
        $param['texto'] = 'Excluir';
        $param['link'] 	= "javascript:confirmaExclusao('".getLink()."excluirProjeto&id=','{ID}',{COLUNA:etiqueta})";
        $param['coluna']= 'id';
        $param['cor'] 	= 'danger';
        $param['flag'] 	= '';
        $param['width'] = 80;
        $param['pos'] = 'I';
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Editar',
            'link' => getLink() . 'editarProjeto&id=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'primary'
        );
        $tabela_projetos->addAcao($param);
        
        $param = array(
            'texto' => 'Editar Colunas',
            'link' => getLink() . 'editarColunasProjeto&id=',
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
        
        $dados = $this->getListaProjetos();
        $tabela_projetos->setDados($dados);
        
        return $tabela_projetos . '';
    }
    
    private function arrumarPassagem(){
        $this->_projeto = $_GET['projeto'] ?? ($_GET['id'] ?? 0);
        $this->_linkAjaxMover = $this->_linkAjaxMover . '&projeto=' . $this->_projeto;
        $this->_linkAjaxSalvar = $this->_linkAjaxSalvar . '&projeto=' . $this->_projeto;
        $this->_linkAjaxFormulario = $this->_linkAjaxFormulario . '&projeto=' . $this->_projeto;
        //$this->_linkReload = $this->_linkReload . '&projeto=' . $this->_projeto;
        $this->_linkReload = getLink() . 'editarColunasProjeto&id=' . $this->_projeto;
    }
    
    public function editarColunasProjeto(){
        $this->arrumarPassagem();
        
        $botao = array();
        $botao["onclick"]= "setLocation('" . getLink() . "index')";
        $botao["texto"]	= "Voltar";
        $botao['cor'] = 'success';
        
        
        $param = array(
            'botoesTitulo' => array($botao),
        );
        
        return parent::index($param);
    }
    
    protected function formularioAjax(){
        $this->arrumarPassagem();
        return parent::formularioAjax();
    }
    
    protected function salvarAjax(){
        $this->arrumarPassagem();
        parent::salvarAjax();
    }
    
    protected function moverAjax(){
        $this->arrumarPassagem();
        return parent::moverAjax();
    }    
    
    protected function moverTarefaBanco($id_tarefa, $coluna_destino, $pos){
        $sql = "update kanboard_colunas set posicao = $pos where id = $id_tarefa";
        query($sql);
        $sql = "update kanboard_projetos set modificado = '" . time() . "' where id in (select projeto from kanboard_colunas where id = $id_tarefa)";
        query($sql);
    }
    
    protected function rebaixarTarefas($coluna, $id_tarefa){
        $sql = "UPDATE kanboard_colunas
                SET posicao = posicao - 1
                WHERE
                    projeto = $coluna
                    and posicao > (select posicao from kanboard_colunas where id = $id_tarefa)
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function elevarTarefas($coluna, $id_tarefa, $pos_nova){
        $sql = "UPDATE kanboard_colunas
                SET posicao = posicao + 1
                WHERE
                    projeto = $coluna
                    and posicao >= $pos_nova
                    and id != $id_tarefa";
        query($sql);
    }
    
    protected function reordenarTarefas($coluna){
        $sql = "UPDATE kanboard_colunas
                SET posicao = (@rownum := 1 + @rownum)
                WHERE
                    0 = (@rownum:=0)
                    and projeto = $coluna
                ORDER BY posicao;";
        query($sql);
    }
    
    public function editarProjeto(){
        $ret = '';
        $id = getParam($_GET, 'id', 0);
        $dados = $this->getEntradaProjeto($id);
        $form = new form01();
        $form->addCampo(array('id' => 'id'	        , 'campo' => 'formProjeto[id]'	        , 'etiqueta' => 'ID'	    , 'tipo' => 'I'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => $dados['id']			, 'validacao' => '', 'obrigatorio' => false, ));
        $form->addCampo(array('id' => 'etiqueta'	, 'campo' => 'formProjeto[etiqueta]'	, 'etiqueta' => 'Etiqueta'	, 'tipo' => 'T'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => $dados['etiqueta']	    , 'validacao' => '', 'obrigatorio' => true, ));
        
        $form->setEnvio(getLink() . 'salvarProjeto&id=' . $id, 'formProjeto', 'formProjeto');
        
        $titulo = ($id == 0 ? 'Criar' : 'Editar') . ' Projeto';
        
        $param = [];
        $param['titulo'] = $titulo;
        $param['conteudo'] = $form;
        $ret = addCard($param);
        
        return $ret;
    }
    
    public function salvarProjeto(){
        $id = getParam($_GET, 'id', 0);
        $form = $_POST['formProjeto'];
        $etiqueta = $form['etiqueta'];
        if($id == 0){
            //cria um projeto novo
            
            $sql = "insert into kanboard_projetos values (null, '$etiqueta')";
        }
        else{
            $sql = "update kanboard_projetos set etiqueta = '$etiqueta' where id = $id";
            //atualiza um projeto existente
        }
        query($sql);
        redireciona(getLink() . 'index');
    }
    
    private function getEntradaProjeto($id){
        $ret = array(
            'id' => '',
            'etiqueta' => '',
        );
        if($id != 0){
            $sql = "select * from kanboard_projetos";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret['id'] = $rows[0]['id'];
                $ret['etiqueta'] = $rows[0]['etiqueta'];
            }
        }
        return $ret;
    }
    
    public function excluirProjeto(){
        $id = $_GET['id'];
        $sql = "delete from kanboard_projetos where id = $id";
        query($sql);
        redireciona(getLink() . 'index');
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
    
    protected function incluirTarefa($etiqueta, $coluna){
        $pos = $this->getUltimaPosicaoColunaDestino($coluna);
        $sql = "insert into kanboard_colunas (id, projeto, etiqueta, posicao, limite, descricao, esconder)
                values                      (null, $coluna, '$etiqueta', $pos, 0, null, 'N')";
        query($sql);
        $sql = "update kanboard_projetos set modificado = '" . time() . "' where id = '$coluna'";
        query($sql);
    }
    
    protected function getUltimaPosicaoColunaDestino($coluna){
        $ret = 1;
        $sql = "SELECT (COALESCE(max(posicao), 0) + 1) as pos FROM kanboard_colunas WHERE projeto = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pos'];
        }
        return $ret;
    }
}