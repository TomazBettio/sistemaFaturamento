<?php

class perfil_crm extends elemento_crm{
    function __construct($id = '', $a = '', $b = '')
    {
        $tabela = $_GET['tabela'];
        $id = base64_decode($_GET['id']);
        parent::__construct($tabela, $id, $this->montarParamConstruct($tabela, $id));

        $novas_funcoes_publicas = array(
            'salvarComentarioPerfil'    => true,
            'salvarAteracoesPerfil'     => true,
            'incluirEvento'             => true,
            'salvarEvento'              => true,
            'incluirEmail'              => true,
            'salvarEmail'               => true,
            'salvarAteracoesEndereco'   => true,
            'incluirEndereco'           => true,
            'salvarEndereco'            => true,
            'salvarDocumentos'          => true,
            'uploadDocumentos'          => true,
            'baixarDocumentos'          => true,
            'baixarArquivo'             => true,
            'salvarTarefa'              => true,
            'incluirTarefaCrm'          => true,
            'ajax'                      => true,
        );

        $this->funcoes_publicas = array_merge($this->funcoes_publicas, $novas_funcoes_publicas);
    }
    
    private function montarParamConstruct($tabela, $id){
        $ret = array();
        $ret['tarefas'] = array(
            'tabela' => 'crm_sub_tarefas',
            'sql' => "select * from (SELECT conteudo, status, usuarios, dt_criacao FROM crm_sub_tarefas WHERE entidade = '$tabela' AND id_entidade = $id union select conteudo, status, usuario as usuarios, dt_criacao from kanboard_sub_tarefas where tarefa in (select tarefa from kanboard_entidades where id_entidade = $id and tabela_entidade = '$tabela')) temp1 order by dt_criacao",
            'campos' => array(
                'conteudo'      => 'Conteúdo',
                'status'        => 'Status',
                'usuarios'       => 'Usuário',
                'dt_criacao'    => 'Data Criação',
            )
        );
        return $ret;
    }

    public function index() {
        return $this->__toString();
    }

    public function ajax() {
        $op = getOperacao();
        $ret = '';
        $tabela = $_GET['tabela'];
        $id = base64_decode($_GET['id']);
        $elemento = new elemento_crm($tabela,$id, []);
        
        switch($op) {
            case 'elemento_resumo':
                $ret = $elemento->cardResumo();
                $ret = ajustaCaractHTML($ret);
                break;
            case 'elemento_detalhes':
                $ret = $elemento->cardDetalhesCamposChave('primary');
                $ret = ajustaCaractHTML($ret);
                break;
            case 'elemento_atualizacoes':
                $ret = $elemento->cardAtualizcoes();
                $ret = ajustaCaractHTML($ret);
                break;
            case 'elemento_eventos':
                $ret = $elemento->cardDetalhesAtividades('primary');
                $ret = ajustaCaractHTML($ret);
                break;
            case 'elemento_emails':
                $ret = $elemento->cardEmails('primary');
                $ret = ajustaCaractHTML($ret);
                break;
            case 'elemento_documentos':
                $ret = $elemento->cardDetalhesArquivos();
                $ret = ajustaCaractHTML($ret);
                break;
            case 'elemento_comentarios':
                $ret = $elemento->cardComentarios('primary', true);
                $ret = ajustaCaractHTML($ret);
                break;
            case 'elemento_pedidos':
                $ret = $elemento->cardPedidos();
                $ret = ajustaCaractHTML($ret);
                break;
            default:
                break;
        }
        return $ret;
    }

    protected function editarTarefa(){
        $id = $_GET['tarefa'];
        $id_entidade = $_GET['id_entidade'];
        $tabela = $_GET['tabela'];
        
        $elemento = new elemento_crm($tabela, $id_entidade, []);
        return $elemento . '';
    }
    
    public function salvarComentarioPerfil(){
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $id_comentario = isset($_GET['id_comentario']) ? base64_decode($_GET['id_comentario']) : 0;
        $elemento = new elemento_crm($tabela, $id, []);
        $elemento->salvarComentario($id_comentario);
        redireciona(getLink() . "index&tabela=$tabela&id=" . base64_encode($id));
    }
    
    public function salvarAteracoesPerfil(){
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $elemento = new elemento_crm($tabela, $id, []);
        $elemento->salvarAlteracoes();
        unset($elemento);
        redireciona(getLink() . 'index&tabela=' . $tabela . '&id=' . base64_encode($id));
    }
    
    public function salvarAteracoesEndereco(){
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $id_endereco = $_GET['id_endereco'];
        $elemento = new elemento_crm($tabela, $id, []);
        $elemento->salvarAlteracoesEnderecos($id_endereco);
        unset($elemento);
        redireciona(getLink() . 'index&tabela=' . $tabela . '&id=' . base64_encode($id));
    }
    
    public function incluirEndereco() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        putAppVar('link_salvar_cad', getLink() . 'salvarEndereco&id='.$_GET['id'] . '&tabela=' . $_GET['tabela']);
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "index&tabela=$tabela&id=".$_GET['id']);
        $cad = new cad01('crm_enderecos');
        $dados = [];
        $sys003 = $cad->getSys003();
        
        foreach($sys003 as $sys) {
            $dados[$sys['campo']] = '';
        }
        
        $dados['entidade'] = $tabela;
        $dados['cod'] = $id;
        $dados['ativo'] = 'S';
        
        return $cad->incluir($dados);
    }
    
    public function salvarEndereco() {
        $id = $_GET['id'];
        $id = base64_decode($id);
        $tabela = $_GET['tabela'];
        
        $cad = new cad01('crm_enderecos');
        $cad->salvar(0, $_POST['formCRUD'], 'I');
        
        $param = [];
        $param['entidade'] = $tabela;
        $param['id_entidade'] = $id;
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');
        $param['descricao'] = 'Novo endereço incluído';
        $param['operacao'] = 'inclusão';
        $this->salvarCad('crm_atualizacoes', $param);
        
        // redireciona(getLink() . 'ajax.editarTarefa&tabela='.$tabela);
        redireciona(getLink() . "index&tabela=$tabela&id=".$_GET['id']);
    }
    
    public function incluirEvento() {
        $id = $_GET['id'];
        $id = base64_decode($id);
        $tabela = $_GET['tabela'];
        putAppVar('link_salvar_cad', getLink() . 'salvarEvento&id='.$_GET['id'] . '&tabela=' . $_GET['tabela']);
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "index&tabela=$tabela&id=".$_GET['id']);
        $cad = new cad01('crm_evento');
        $dados = [];
        $sys003 = $cad->getSys003();
        
        foreach($sys003 as $sys) {
            $dados[$sys['campo']] = '';
        }
        
        $dados['entidade_tipo'] = $tabela;
        $dados['entidade_id'] = $id;
        $dados['ativo'] = 'S';
        
        return $cad->incluir($dados);
    }
    
    public function salvarEvento() {
        $id = $_GET['id'];
        $id = base64_decode($id);
        $tabela = $_GET['tabela'];
        
        $cad = new cad01('crm_evento');
        $cad->salvar(0, $_POST['formCRUD'], 'I');
        
        $param = [];
        $param['entidade'] = $tabela;
        $param['id_entidade'] = $id;
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');
        $param['descricao'] = 'Novo evento incluído';
        $param['operacao'] = 'inclusão';
        $this->salvarCad('crm_atualizacoes', $param);
        
        // redireciona(getLink() . "ajax.editarTarefa&tabela=$tabela&id=$id");
        redireciona(getLink() . "index&tabela=$tabela&id=".$_GET['id']);
    }

    public function incluirTarefaCrm() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tabela_tarefa = $_GET['tarefa'];
        putAppVar('link_salvar_cad', getLink() . 'salvarTarefa&id='.$_GET['id'] . '&tabela=' . $_GET['tabela'] . "&tarefa=$tabela_tarefa");
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "index&tabela=$tabela&id=".$_GET['id']);
        $cad = new cad01($tabela_tarefa);
        $dados = [];
        $sys003 = $cad->getSys003();
        
        foreach($sys003 as $sys) {
            $dados[$sys['campo']] = '';
        }

        $dados['entidade'] = $tabela;
        $dados['id_entidade'] = $id;
        
        return $cad->incluir($dados);
    }
    
    public function salvarTarefa() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tabela_tarefa = $_GET['tarefa'];
        
        $cad = new cad01($tabela_tarefa);
        $cad->salvar(0, $_POST['formCRUD'], 'I');
        
        $param = [];
        $param['entidade'] = $tabela;
        $param['id_entidade'] = $id;
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');
        $param['descricao'] = 'Nova tarefa incluído';
        $param['operacao'] = 'inclusão';
        $this->salvarCad('crm_atualizacoes', $param);
        
        // redireciona(getLink() . 'ajax.editarTarefa&tabela='.$tabela);
        redireciona(getLink() . "index&tabela=$tabela&id=".$_GET['id']);
    }
    
    public function incluirEmail() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        putAppVar('link_salvar_cad', getLink() . 'salvarEmail&id='.$_GET['id'] . '&tabela=' . $_GET['tabela']);
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "index&tabela=$tabela&id=".$_GET['id']);
        $cad = new cad01('crm_email');
        $dados = [];
        $sys003 = $cad->getSys003();
        
        foreach($sys003 as $sys) {
            $dados[$sys['campo']] = '';
        }
        
        $dados['entidade'] = $tabela;
        $dados['cod'] = $id;
        $dados['ativo'] = 'S';
        
        return $cad->incluir($dados);
    }
    
    public function salvarEmail() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        
        $this->salvarCad('crm_email', $_POST['formCRUD']);
        
        $param = [];
        $param['entidade'] = $tabela;
        $param['id_entidade'] = $id;
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');
        $param['descricao'] = 'Incluido um novo E-mail';
        $param['operacao'] = 'inclusão';
        $this->salvarCad('crm_atualizacoes', $param);
        
        // redireciona(getLink() . 'ajax.editarTarefa&tabela='.$tabela);
        redireciona(getLink() . "index&tabela=$tabela&id=".$_GET['id']);
    }
    
    private function salvarCad($tabela, $param) {
        $cad = new cad01($tabela);
        $cad->salvar(0, $param, 'I');
    }
    
    public function salvarDocumentos() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $elemento = new elemento_crm($tabela, $id, []);
        return $elemento->importar();
        // unset($elemento);
    }

    public function baixarDocumentos() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $arquivo = $_GET['arquivo'];

        $link = '/arquivos/' . $tabela . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $arquivo;
        redirect($link, false);

        // redireciona(getLink() . 'ajax.editarTarefa&tabela='.$tabela);
        redireciona(getLink() . "index&tabela=$tabela&id=".$_GET['id']);
    }
    
    public function uploadDocumentos() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $elemento = new elemento_crm($tabela, $id);
        $elemento->upload();
        unset($elemento);
        // redireciona(getLink() . "ajax.editarTarefa&tabela=$tabela&id=$id");
        redireciona(getLink() . "index&tabela=$tabela&id=".$_GET['id']);
    }
    
    // protected function criarParametrosElementos($tarefa = '') {
    //     $ret = [];
    //     $ret['link_base'] = getLink() . "ajax.editarTarefa&tarefa=$tarefa";
    //     $ret['link_cancelar'] = getLink() . "ajax.editarTarefa&tarefa=$tarefa";
    //     $ret['tarefas'] = array(
    //         'tabela' => 'kanboard_sub_tarefas',
    //         'sql'       => 'select * from kanboard_sub_tarefas',
    //         'campos'    => array(
    //             'tarefa'        => 'Tarefa', 
    //             'conteudo'      => 'Conteúdo', 
    //             'status'        => 'Status', 
    //             'usuario'       => 'Usuário', 
    //             'dt_criacao'    => 'Data Criação',
    //         ),
    //     );
        
    //     return $ret;
    // }
    
    protected function criarTabelaEdicao($id){
        $id = base64_decode($_GET['id']);
        $cad = new cad01($_GET['tabela']);
        $dados = $cad->getEntrada($id, false);
        print_r($dados);
    }
    
    public function baixarArquivo(){
        $ret = false;
        $id = $_GET['arquivo'] ?? '';
        if(!empty($id)){
            $id = base64_decode($id);
            if($this->verificarPermissaoArquivo($id)){
                $ret = getDadosArquivoDownload($id);
            }
        }
        return $ret;
    }
    
    //TODO: criar um teste pra verificar a permissao
    protected function verificarPermissaoArquivo($id){
        return true;
    }
}