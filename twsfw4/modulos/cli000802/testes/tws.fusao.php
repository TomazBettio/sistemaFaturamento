<?php

class fusao extends kanboard {
    public function __construct() {
        parent::__construct(1);
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
                'calendario'                => true,
        );
        
        $this->funcoes_publicas = array_merge($this->funcoes_publicas, $novas_funcoes_publicas);
    }
    
    protected function getOpercaoClicarTarefas(){
        return '';
    }
    
    public function ajax($op = '') {
        if($op == ''){
            $op = getOperacao();
        }
        $ret = '';
        $tarefa = $_GET['tarefa'] ?? '';
        $tabela = $_GET['tabela'] ?? $this->getTabelaEntidadesFromTarefa($tarefa);
        $id = $_GET['id'] ?? $this->getIdEntidade($tarefa);
        if(!empty($tarefa)) {
            $tarefa = $this->getTarefaByEntidade($id, $tabela);
            $elemento = new elemento_crm($tabela,$id,$this->criarParametrosElementos($tarefa));
        }

        $meses = array(
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Aug' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12'
          );
        
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

                //////////////////////// Caso sja uma requisição do calendário //////////////////////////////////
            case 'eventos':
                $sql = "SELECT * from crm_evento WHERE entidade_tipo = '$tabela' AND entidade_id = $id AND ativo = 'S'";
                $rows = query($sql);
    
                $eventos = [];
                foreach($rows as $row) {
    
                    $temp = [];
                    $temp['id'] = $row['id'];
                    $temp['title'] = $row['nome'];
                    $temp['start'] = $row['dt_inicio'];
                    $temp['backgroundColor'] = $row['cor_fundo'] ??  '#f56954';
                    $temp['borderColor'] = $row['cor_borda'] ?? 'f56954';
                    $temp['allDay'] = true;
    
                    $eventos[] = $temp;
                }
    
                return json_encode($eventos);
                
                break;
            case 'mover':
                $datas = explode(' ', $_GET['data']);
                $mes = $meses[$datas[1]];
                $dia = $datas[2];
                $ano = $datas[3];
                $data = $ano . '-' . $mes . '-' . $dia . ' 00:00:00';
                
                $sql = "UPDATE crm_evento SET dt_inicio = '$data' WHERE id = $id";
                query($sql);
    
                break;
            case 'incluir':
                $permissao = true;
    
                if($permissao) {
        
                    $titulo = $_GET['titulo'];
                    $datas = explode(' ', $_GET['data']);
                    $mes = $meses[$datas[1]];
                    $dia = $datas[2];
                    $ano = $datas[3];
                    $data = $ano . '-' . $mes . '-' . $dia . ' 00:00:00';
        
                    $param = [];
                    $param['entidade_tipo'] = $tabela;
                    $param['entidade_id'] = $id;
                    $param['dt_inicio'] = $data;
                    $param['nome'] = $titulo;
                    $param['cor_fundo'] = $_GET['cor_fundo'];
                    $param['cor_borda'] = $_GET['cor_borda'];
                    $param['ativo'] = 'S';
                    
                    $sql = montaSQL($param, 'crm_evento');
                    query($sql);
        
                    return 'true';
                }
                else {
                    return 'false';
                }
    
                break;

            case 'editar':

                $html = '<p>Está funcionando</p>';

                return $html;

                break;

            default:
                $ret = parent::ajax();
        }
        return $ret;
    }
    
    protected function editarTarefa(){
        $id = $_GET['tarefa'];
        $id_entidade = $this->getIdEntidade($id);
        $tabela = $this->getTabelaEntidadesFromTarefa($id);
        
        $elemento = new elemento_crm($tabela, $id_entidade, $this->criarParametrosElementos($id));
        return $elemento . '';
    }
    
    public function salvarComentarioPerfil(){
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $id_comentario = isset($_GET['id_comentario']) ? base64_decode($_GET['id_comentario']) : 0;
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        $elemento = new elemento_crm($tabela, $id, $this->criarParametrosElementos($tarefa));
        $elemento->salvarComentario($id_comentario);
        redireciona(getLink() . "ajax.editarTarefa&tarefa=$tarefa");
    }
    
    public function salvarAteracoesPerfil(){
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        $elemento = new elemento_crm($tabela, $id, $this->criarParametrosElementos($tarefa));
        $elemento->salvarAlteracoes();
        unset($elemento);
        redireciona(getLink() . 'ajax.editarTarefa&tarefa=' . $tarefa);
    }
    
    public function salvarAteracoesEndereco(){
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $id_endereco = $_GET['id_endereco'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        $elemento = new elemento_crm($tabela, $id, $this->criarParametrosElementos($tarefa));
        $elemento->salvarAlteracoesEnderecos($id_endereco);
        unset($elemento);
        redireciona(getLink() . 'ajax.editarTarefa&tarefa=' . $tarefa);
    }
    
    public function incluirEndereco() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        putAppVar('link_salvar_cad', getLink() . 'salvarEndereco&id='.$_GET['id'] . '&tabela=' . $_GET['tabela']);
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "ajax.editarTarefa&tarefa=$tarefa");
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
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        
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
        
        redireciona(getLink() . 'ajax.editarTarefa&tarefa='.$tarefa);
    }
    
    public function incluirEvento() {
        $id = $_GET['id'];
        $id = base64_decode($id);
        $tabela = $_GET['tabela'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        putAppVar('link_salvar_cad', getLink() . 'salvarEvento&id='.$_GET['id'] . '&tabela=' . $_GET['tabela']);
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "ajax.editarTarefa&tarefa=$tarefa");
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
        $tarefa = $this->getTarefaByEntidade($id, $tabela);

        $_POST['formCRUD']['dt_inicio'] = Datas::dataD2S($_POST['formCRUD']['dt_inicio']);
        $_POST['formCRUD']['dt_fim'] = Datas::dataD2S($_POST['formCRUD']['dt_fim']);
        
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
        
        redireciona(getLink() . 'ajax.editarTarefa&tarefa='.$tarefa);
    }

    public function incluirTarefaCrm() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tabela_tarefa = $_GET['tarefa'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        putAppVar('link_salvar_cad', getLink() . 'salvarTarefa&id='.$_GET['id'] . '&tabela=' . $_GET['tabela'] . "&tarefa=$tabela_tarefa");
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "ajax.editarTarefa&tarefa=$tarefa");
        $cad = new cad01($tabela_tarefa);
        $dados = [];
        $sys003 = $cad->getSys003();
        
        foreach($sys003 as $sys) {
            $dados[$sys['campo']] = '';
        }

        $dados['tarefa'] = $tarefa;
 
        return $cad->incluir($dados);
    }
    
    public function salvarTarefa() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tabela_tarefa = $_GET['tarefa'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        
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
        
        redireciona(getLink() . 'ajax.editarTarefa&tarefa='.$tarefa);
    }
    
    public function incluirEmail() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        putAppVar('link_salvar_cad', getLink() . 'salvarEmail&id='.$_GET['id'] . '&tabela=' . $_GET['tabela']);
        putAppVar('link_redirecionar_cad_cancelar', getLink() . "ajax.editarTarefa&tarefa=$tarefa");
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
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        
        $this->salvarCad('crm_email', $_POST['formCRUD']);
        
        $param = [];
        $param['entidade'] = $tabela;
        $param['id_entidade'] = $id;
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');
        $param['descricao'] = 'Incluido um novo E-mail';
        $param['operacao'] = 'inclusão';
        $this->salvarCad('crm_atualizacoes', $param);
        
        redireciona(getLink() . 'ajax.editarTarefa&tarefa='.$tarefa);
    }
    
    private function salvarCad($tabela, $param) {
        $cad = new cad01($tabela);
        $cad->salvar(0, $param, 'I');
    }
    
    public function salvarDocumentos() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        $elemento = new elemento_crm($tabela, $id, $this->criarParametrosElementos($tarefa));
        return $elemento->importar();
        // unset($elemento);
    }

    public function baixarDocumentos() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $arquivo = $_GET['arquivo'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);

        $link = '/arquivos/' . $tabela . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $arquivo;
        redirect($link, false);

        redireciona(getLink() . 'ajax.editarTarefa&tarefa='.$tarefa);
    }
    
    public function uploadDocumentos() {
        $id = base64_decode($_GET['id']);
        $tabela = $_GET['tabela'];
        $tarefa = $this->getTarefaByEntidade($id, $tabela);
        $elemento = new elemento_crm($tabela, $id, $this->criarParametrosElementos($tarefa));
        $elemento->upload();
        unset($elemento);
        redireciona(getLink() . 'ajax.editarTarefa&tarefa='.$tarefa);
    }
    
    protected function criarParametrosElementos($tarefa = '') {
        $ret = [];

        $ret['link_base'] = getLink() . "ajax.editarTarefa&tarefa=$tarefa";
        $ret['link_cancelar'] = getLink() . "ajax.editarTarefa&tarefa=$tarefa";
        
        $sql = "select id_entidade, tabela_entidade from kanboard_entidades where tarefa = $tarefa";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $tabela = $rows[0]['tabela_entidade'];
            $id_entidade = $rows[0]['id_entidade'];
            $ret['tarefas'] = array(
                'tabela' => 'kanboard_sub_tarefas',
                'sql'       => "select * from (select conteudo, status, usuario, dt_criacao  from kanboard_sub_tarefas WHERE tarefa = $tarefa union select conteudo, status, usuarios as usuario, dt_criacao from crm_sub_tarefas where entidade = '$tabela' and id_entidade = $id_entidade) temp1 order by dt_criacao",
                'campos'    => array(
                    'conteudo'      => 'Conteúdo',
                    'status'        => 'Status',
                    'usuario'       => 'Usuário',
                    'dt_criacao'    => 'Data Criação',
                ),
            );
        }
        else{
            $ret['tarefas'] = array(
                'tabela' => 'kanboard_sub_tarefas',
                'sql'       => "select * from kanboard_sub_tarefas WHERE tarefa = $tarefa ",
                'campos'    => array(
                    'tarefa'        => 'Tarefa',
                    'conteudo'      => 'Conteúdo',
                    'status'        => 'Status',
                    'usuario'       => 'Usuário',
                    'dt_criacao'    => 'Data Criação',
                ),
            );
        }
        
        
        
        return $ret;
    }
    
    protected function criarTabelaEdicao($id){
        $cad = new cad01($this->getTabelaEntidades());
        $dados = $cad->getEntrada($this->getIdEntidade($id), false);
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

    public function calendario() {
        $tabela = $_GET['tabela'];
        $id = base64_decode($_GET['id']);
        $tarefa = $this->getTarefaByEntidade($id, $tabela);

        $param = [];
        $param['link_retorno'] = getLink() . 'ajax.editarTarefa&tarefa='.$tarefa;
        $calendario = new crm_calendario($tabela, $id, $param);
        return $calendario->index();
    }
}