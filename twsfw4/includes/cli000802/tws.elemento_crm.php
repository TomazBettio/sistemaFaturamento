<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class elemento_crm {
    var $funcoes_publicas = array(
        'index'                 => true,
        'salvarAlteracoes'      => true,
        'salvarComentario'      => true,
        'importar'              => true,
        'upload'                => true,
        'avisos'                => true,
        'salvarAlteracoesEnderecos' => true,
    );
    
    
    // Dados do perfil
    protected $_dados = [];
    //id da entrada
    protected $_id;
    //id codificado da entrada
    protected $_id_codifcado;
    //tabela da entrada
    protected $_tabela;
    //sys003 da entrada
    protected $_sys003;
    //sys002 da entrada
    protected $_sys002;
    //titulo
    protected $_titulo;
    //link base
    protected $_linkBase;
    //link para salvar comentarios
    protected $_linkSalvarComentario;
    //link para salvar alteraÃ§Ãµes
    protected $_linkSalvarAlteracoes;
    // Link para cancelar criaÃ§Ã£o de evento
    protected $_link_redirecionar_cad_cancelar;
    // Link para salvar alterações no endereço
    protected $_linkSalvarAlteracoesEndereco;
    // tarefas (para conexão com o banco)
    private $_tarefas;
    // Comentarios
    private $_comentarios;
    // Usuários
    private $_usuarios = [];
    // Clientes que fizeram pedidos
    private $_produtos = [];
    
    public function __construct($tabela, $id, $param = array()){
        if(!empty($tabela) && !empty($id)){
            $this->_id = $id;
            $this->_id_codifcado = base64_encode($this->_id);
            $this->_tabela = $tabela;
            
            $cad = new cad01($tabela);
            $this->_sys002 = $cad->getSys002();
            $this->_sys003 = $cad->getSys003();
            $this->_dados = $cad->getEntrada($this->_id, false);
            $this->_titulo = ucfirst(str_replace('_', ' ', str_replace('crm_', '', $this->_tabela)));
            $this->_linkBase = ($param['link_base'] ?? getLink() . 'perfil') . '&tabela='.$this->_tabela;
            $this->_linkSalvarComentario = ($param['link_salvar_comentario'] ?? getLink() . 'salvarComentarioPerfil') . '&id='.$this->_id_codifcado.'&tabela='.$this->_tabela;
            $this->_linkSalvarAlteracoes = ($param['link_salvar_alteracoes'] ?? getLink() . 'salvarAteracoesPerfil') . '&tabela='.$this->_tabela . "&id={$this->_id_codifcado}";
            $this->_linkSalvarAlteracoesEndereco = ($param['link_salvar_alteracoes_endereco'] ?? getLink() . 'salvarAteracoesEndereco') . '&tabela='.$this->_tabela.'&id='.$this->_id_codifcado;
            $this->_link_redirecionar_cad_cancelar = ($param['link_cancelar'] ?? (getLink() . getMetodo() . "&id={$this->_id_codifcado}")) . "&tabela=".$this->_tabela;
            $this->_tarefas = $param['tarefas'] ?? array(
                                                    'tabela' => 'crm_sub_tarefas',
                                                    'sql' => "SELECT * FROM crm_sub_tarefas WHERE entidade = '$tabela' AND id_entidade = $id", 
                                                    'campos' => array(
                                                        'conteudo'      => 'Conteúdo', 
                                                        'status'        => 'Status', 
                                                        'usuarios'       => 'Usuário', 
                                                        'dt_criacao'    => 'Data Criação',
                                                    )
                                                );
        }
    }

    //////////////////// FUNÇÕES PARA AVISOS /////////////////////////
    public function avisos()
	{
		$tipo = $_GET['tipo'] ?? '';
		if ($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->__toString();
	}

    /////////////////// FUNÇÕES DE INTERATIVIDADE /////////////////////////////////
    public function salvarAlteracoes() {
        $crudTemp = new cad01($this->_tabela);
        $crudTemp->salvar($this->_id_codifcado, $_POST, 'E');
        
        $param = [];
        $param['descricao'] = "Alterações no banco $this->_tabela";
        $param['operacao'] = 'alteração';

        $this->gravarAtualizacoes($param);
    }

    public function salvarAlteracoesEnderecos($id) {
        $id = base64_encode($id);
        $crudTemp = new cad01('crm_enderecos');
        $crudTemp->salvar($id, $_POST, 'E');
        
        $param = [];
        $param['descricao'] = 'Alterado um endereço';
        $param['operacao'] = 'alteração';

        $this->gravarAtualizacoes($param);
    }

    public function salvarComentario($id_comentario = 0) {
        $id = getParam($_GET, 'id', '');
        if($id != ''){ 
            $id = base64_decode($id); 
            $param = [];
            $param['usuario'] = getUsuario();
            $param['data'] = date('YmdHis');
            $param['tabela'] = $this->_tabela;
            $param['id_entidade'] = $id;
            $param['comentario'] = $_POST['mensagem'];
            $param['id_pai'] = $id_comentario;

            $sql = montaSQL($param, 'crm_comentarios');
            echo $sql;
            query($sql);

            // --- SALVANDO A MODIFICAÃ‡ÃƒO EM crm_atualizacoes ------
            $param = [];
            $param['descricao'] = 'Adicionado um novo comentário';
            $param['operacao'] = 'inclusão';

            $this->gravarAtualizacoes($param);
        }
        //redireciona(getLink() . "index&tabela=$tabela&id=$id_cod");
    }

    public function importar() {
        $tabela = $_GET['tabela'] ?? $this->_tabela;
        $id = $_GET['id'] ?? $this->_id_codifcado;
        $link = getLink() . 'uploadDocumentos&id='.$id . '&tabela=' . $tabela;

        global $nl;
        $ret = '';

        $param = [];
        // $param['nome'] 	= 'upd_sped[]';
        $param['nome'] 	= 'upd_arquivo[]';
        $param['multi'] = true;
        $form = formbase01::formFile($param) . '<br><br>';

        $param = formbase01::formSendParametros();

        $param['texto'] = 'Enviar Arquivos';
        $form .= formbase01::formBotao($param);

        $param = array();
        $param['acao'] = $link;
        $param['nome'] = 'formUPD';
        $param['id']   = 'formUPD';
        $param['enctype'] = true;
        $form = formbase01::form($param, $form);

        $ret .= '<div class="row">' . $nl;
        $ret .= '	<div  class="col-md-4">' . '' . '</div>' . $nl;
        $ret .= '	<div  class="col-md-2"></div>' . $nl;
        $ret .= '	<div  class="col-md-5">' . $form . '</div>' . $nl;
        $ret .= '</div>' . $nl;

        $param = array();
        $p = array();
        $p['onclick'] = "setLocation('$link')";
        $p['tamanho'] = 'pequeno';
        $p['cor'] = 'danger';
        $p['texto'] = 'Voltar';
        $param['botoesTitulo'][] = $p;
        $param['titulo'] = 'Upload Arquivos clientes';
        $param['conteudo'] = $ret;
        $ret = addCard($param);

        return $ret;
    }

    public function upload() {
        global $config;
        $path = $config['arquivosDir'];

        $tabela = $_GET['tabela'] ?? $this->_tabela;
        $id = $_GET['id'] ?? $this->_id_codifcado;
        $id = base64_decode($id);

        $ret = '';
        if (!isset($_FILES['upd_arquivo'])) {
            $ret = $this->__toString();
            return $ret;
        }
// print_r($_FILES);

        $files = $_FILES['upd_arquivo'];
        // print_r($files['error'][0]);

        if (count($files['name']) > 0 && $files['error'][0] == 0) {
            //Vai servir para identificao de diretorio unico de upload
// echo "<br> \n";
// print_r($files['name']);

            $this->criaPasta($path, $tabela, $id);

            $cont = 0;
            foreach ($files['name'] as $key => $arq) {
                $nome = $files['name'][$key];
                //echo "Processando $nome <br>\n";
                
                $ext = ltrim(substr($nome, strrpos($nome, '.')), '.');
                $arquivo = $nome;
                
                $id_arquivo = salvarArquivoUpload($files['tmp_name'][$key], $path . $tabela . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $arquivo);
                
                if($id_arquivo !== false){
                    $this->amarrarArquivos($id_arquivo, $id, $tabela);
                    $cont++;
                }
            }
        } else {
            echo "nenhum arquivo encontrado.";
            // redireciona(getLink() . "avisos&tabela=$tabela&id=$id&mensagem=nenhum arquivo encontrado.&tipo=erro");
        }

        if($cont > 0) {
            echo "$cont arquivos salvos com sucesso!";
            // redireciona(getLink() . "avisos&tabela=$tabela&id=$id&mensagem=$cont arquivos salvos com sucesso!&tipo=erro");

            $param = [];
            $param['descricao'] = $cont.' novos arquivos salvos';
            $param['operacao'] = 'inclusão';

            $this->gravarAtualizacoes($param);
        } else {
            echo "nenhum arquivo anexado ou arquivo incorreto";
            // redireciona(getLink() . "avisos&tabela=$tabela&id=$id&mensagem=nenhum arquivo anexado ou arquivo incorreto&tipo=erro");
        }
    }
    
    private function amarrarArquivos($id_arquivo, $id_entidade, $tabela_entidade){
        $param = array(
            'tabela_entidade' => $tabela_entidade,
            'id_entidade' => $id_entidade,
            'id_arquivo' => $id_arquivo,
        );
        $sql = montaSQL($param, 'crm_arquivos');
        query($sql);
    }

    private function criaPasta($path, $entidade, $id) {
        if (!file_exists($path . $entidade)) {
			mkdir($path . $entidade, 0777, true);
			chMod($path . $entidade, 0777);
		}
		if (!file_exists($path . $entidade . DIRECTORY_SEPARATOR . $id)) {
			mkdir($path . $entidade . DIRECTORY_SEPARATOR . $id, 0777, true);
			chMod($path . $entidade . DIRECTORY_SEPARATOR . $id, 0777);
		}
	}

    private function moverArquivo($file, $arquivo) {
		$ret = false;

		if(move_uploaded_file($file, $arquivo)) {
			rename($arquivo, str_replace([' ', ','], ['_', ''], $arquivo));
			$ret = true;
		}

		return $ret;
	}

    public function index() {
        return $this->__toString();
    }
	
    //////////////////// HTML INTEIRO /////////////////////////
	function __toString() {
        addPortaljavaScript("function hide(id) {
            elements = document.getElementById(id)
	    if(elements != null){
			elements = elements.length ? elements : [elements];
            for (var index = 0; index < elements.length; index++) {
              elements[index].style.display = 'none';
            }
		}
            
          }");
        addPortaljavaScript("function show(id) {
            elements = document.getElementById(id);
		if(elements != null){
			elements = elements.length ? elements : [elements];
            for (var index = 0; index < elements.length; index++) {
              elements[index].style.display = 'block';
            }
}
            
          }");
        addPortaljavaScript('function color(id) {
            const botoes = ["btn-todos", "btn-camposChave", "btn-documentos", "btn-pedidos", "btn-atividades", "btn-emails", "btn-comentarios", "btn-atualizacoes"];

            for (var index = 0; index < botoes.length; index++) {
                elemento = document.getElementById(botoes[index]);
                if(elemento != null){
                    // elemento.style.color = "#343a40";
                    // // elemento.style.textDecoration = "initial";
                    // elemento.style.backgroundColor = "transparent";
                    // elemento.style.borderColor = "#343a40";
                    elemento.parentElement.style.borderBottom = "none";
                }
            }

            elements = document.getElementById(id);
		    if(elements != null){
                elements = elements.length ? elements : [elements];
                for (var index = 0; index < elements.length; index++) {
                    // elements[index].style.color = "#fff";
                    // // elements[index].style.textDecoration = "none";
                    // elements[index].style.backgroundColor = "#343a40";
                    // elements[index].style.borderColor = "#343a40";
                    elements[index].parentElement.style.borderBottom = "thick solid #000000";
                }
		    }
            
        }');
        addPortaljavaScript('function minhaFuncao(id){
            const ids = ["card-camposChave", "card-documentos", "card-atividades", "card-comentarios"];
            const detalhados = ["detalhe-camposChave", "detalhe-documentos", "detalhe-pedidos", "detalhe-atividades", "detalhe-emails", "detalhe-atualizacoes"];

            if(id == "todos") {
                ids.forEach(show);
                detalhados.forEach(hide);
            } else {
                ids.forEach(hide);
                detalhados.forEach(hide);
                show(id);
            }
        }');
        addPortaljavaScript('function excluiDetalhado(){
            const detalhados = ["detalhe-camposChave", "detalhe-documentos", "detalhe-pedidos", "detalhe-atividades", "detalhe-emails", "detalhe-atualizacoes"];

            detalhados.forEach(hide);
        }');
        addPortaljavaScript('window.onload = function() {
            //dom not only ready, but everything is loaded
            excluiDetalhado();
        }');
	    $html = '<div class="content">
                <!-- Content Header (Page header) -->
                    <div class="content-header">
                        <div class="container-fluid">
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <h1 class="m-0">'.$this->_titulo.'</h1>
                                </div><!-- /.col -->
                            </div><!-- /.row -->
                        <!-- /.container-fluid -->
                    
                ';
	    if(isset($this->_dados['nome'])) {
	        $html .= $this->cabecalhoHtml($this->_dados, 'primary');
	    }
	    
	    $html .=    $this->navegacao('primary').
                            '<div class="div-main" id = "adwsadasdsda">'.
                                $this->cardResumo().
                            '</div>
                            <!-- /.row (main row) -->
                        </div><!-- /.container-fluid -->
                    <!-- /.content -->
                    </div>
                </div>
            </div>';

        $this->addJSAjax();
	    
	    return $html;
	}

    private function addJSAjax(){
        if(isset($_GET['tarefa'])){
            $tarefa = $_GET['tarefa'];
		    $ret = "function callAjax(operacao){
                        link = '" . getLinkAjax('') . "' + operacao + '&tarefa=$tarefa';
                        $.get(link, function(retorno){
                            document.getElementById('adwsadasdsda').innerHTML = retorno;
                        });
    		        }";
        }
        else{
            $tabela = $_GET['tabela'];
            $id = $_GET['id'];
            $ret = "function callAjax(operacao){
                link = '" . getLinkAjax('') . "' + operacao + '&id=$id&tabela=$tabela';
                $.get(link, function(retorno){
                    document.getElementById('adwsadasdsda').innerHTML = retorno;
                });
            }";
        }
        	
		addPortaljavaScript($ret);
	}
    public function cardResumo() {
        $html = '<div class="col-lg-12 resizable-summary-view">
                    <!-- TO DO List -->
                    <div class="row">
                        <!-- /.card-header -->
                        <div class="col-sm-3">
                            '.$this->cardCamposChave($this->_dados, 'primary').'
                                
                            '.$this->cardDocumentos('primary').'
                        </div>
                        <!-- /.card-body -->
                                
                        <div class="col-sm">';

        $html .=            $this->cardAtividades('primary').
                            $this->cardTarefas('primary');

        $html .=            $this->cardComentarios('primary').'
                        </div>
                    </div>
                    <!-- /.card -->
                <!-- right col -->
                </div>';

        return $html;
    }

    ////////////////////// CABEÇALHO site ////////////////////////////////
    private function cabecalhoHtml($dados, $cor) {
        $tratamento = isset($dados['tratamento']) && !empty($dados['tratamento']) ? $dados['tratamento'] . ', ' : '';
        $html = '<!-- /.content-header -->
    
                    <!-- Main content -->
                    <div class="container-fluid">
                        <!-- Small boxes (Stat box) -->
                        <div class="card">
                            <div class="col-15">
                                <div class="form-row">
                                <!-- small box -->
                                <div class="col-2">
                                    <div class="inner">
                                    <p>A foto fica aqui</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="inner">
                                    <h4>'.$tratamento . $dados['nome'].' '.($dados['sobrenome'] ?? '').'
                                    </div>
                                    <div class="icon">
                                    <i class="ion ion-bag"></i>
                                    </div>
                                    <a href="#" class="small-box-footer">Exibir Mapa <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                                <div class="col-4 card-title">
                                    <button class="btn btn-outline-'.$cor.' btn-sm">Seguir</button>
                                    <button class="btn btn-outline-'.$cor.' btn-sm">Converter Lead</button>
                                    <button class="btn btn-outline-'.$cor.' btn-sm">Mais</button>
                                </div>
                            </div>
                        </div>
                                    <!-- ./col -->
                    </div>
                </div>';
    
        return $html;
    }

    private function navegacao($cor) {
        $html = '<div class="card" id="div-botoes">
                    <div class="container">
                        <nav class="row justify-content-md-center">
                            <div class="row">
                                <div class="col-sm" style="border-bottom: thick solid #000000"><button onclick="minhaFuncao(\'todos\'); color(this.id); callAjax(\'elemento_resumo\');" class="btn btn-outline-'.$cor.' btn-sm" id="btn-todos">Resumo</i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-camposChave\'); color(this.id); callAjax(\'elemento_detalhes\');" class="btn btn-outline-'.$cor.' btn-sm" id="btn-camposChave">Detalhes</button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-atualizacoes\'); color(this.id); callAjax(\'elemento_atualizacoes\');" class="btn btn-outline-'.$cor.' btn-sm" id="btn-atualizacoes">Atualizações</button></div>
                                <div class="col-sm"></div>
                                <div style="display: none;" class="col-sm"><button onclick="minhaFuncao(\'detalhe-pedidos\'); color(this.id); callAjax(\'elemento_pedidos\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Eventos" id="btn-pedidos"><i class="fa fa-shopping-bag fa-2x" aria-hidden="true"></i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-atividades\'); color(this.id); callAjax(\'elemento_eventos\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Eventos" id="btn-atividades"><i class="fa fa-calendar fa-2x" aria-hidden="true"></i></button></div>
                                <div style="display: none;" class="col-sm"><button onclick="minhaFuncao(\'detalhe-emails\'); color(this.id); callAjax(\'elemento_emails\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="E-mails" id="btn-emails"><i class="fa fa-envelope fa-2x" aria-hidden="true"></i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-documentos\'); color(this.id); callAjax(\'elemento_documentos\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Documentos" id="btn-documentos"><i class="fa fa-file-text fa-2x" aria-hidden="true"></i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'card-comentarios\'); color(this.id); callAjax(\'elemento_comentarios\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Comentários" id="btn-comentarios"><i class="fa fa-commenting fa-2x" aria-hidden="true"></i></button></div>
                            </div>
                        </nav>
                    </div>
                </div>';

        return $html;
    }

    /////////////////// cards ///////////////////////////
    private function cardCamposChave($dados, $cor) {
        // ================== CARD RESUMO CAMPOS CHAVE =================================
        $html = '<div class="card" id="card-camposChave">
                    <div class="summaryViewHeader">
                        <h4 class="display-inline-block">Campos Chave</h4>
                    </div>
                    <div class="summaryViewFields">
                            <div class="recordDetails">
                                <ul class="todo-list" data-widget="todo-list">';
        $cad = new cad01($this->_tabela,[]);
        $param = $cad->getSys003();
    
        // $form = new form01();
    
        $html .= '<form method="POST" action="' . $this->_linkSalvarAlteracoes . '&id='.$this->_id_codifcado.'">
                    <table>';
        if(is_array($param) && count($param) > 0) {
            foreach($param as $p) {
                if($p['campo_chave'] == 'S' && $p['usado'] == 'S') {
                    if(isset($dados[$p['campo']])) {
                        $html .= '<tr>
                                    <td>
                                        <label for="'.$p['campo'].'">'.$p['etiqueta'].'  </label>
                                    </td>
                                    <td>';
                                        if($p['tipo'] == 'A' || !empty($p['funcao_lista']) || !empty($p['opcoes']) || !empty($p['tabela_itens'])) {
                                            $html .= '<select id="'.$p['campo'].'" name="'.$p['campo'].'">';
                                            $html .=    $this->getOpcoes($p, $dados[$p['campo']]);
                                            $html .= '</select>';
                                        } else {
                                            $html .= '<input type="text" name="'.$p['campo'].'" id="'.$p['campo'].'" value="'.$dados[$p['campo']].'">';
                                        }
                        $html .=    '</td>
                                </tr>';
                    }
                }
            }
        }
    
        $html .=            '</table>
                            
                            <button type="submit" class="btn btn-outline-'.$cor.' float-right btn-sm">Salvar</button>
                        </form>
                        </ul>
                    </div>
                </div>
            </div>';
        
        return $html;
    }
            
    public function cardDetalhesCamposChave($cor) {
        // ====================== CARD DETALHES ==================================
        $html = '<div class="card" style="width: 85rem;" id="detalhe-camposChave">
                    <div class="container">
                        <div class="row justify-content-md-center">';

        // ------------- CARD DETALHES CAMPO CHAVE --------------------
        $html .=    '<div class="card col-sm-3" style="width: 18rem;">
                        <div class="summaryViewHeader">
                            <h4 class="display-inline-block">Campos Chave</h4>
                        </div>
                        <div class="summaryViewFields">
                            <div class="recordDetails">
                                <ul class="todo-list" data-widget="todo-list">';

        $cad = new cad01($this->_tabela,[]);
        $param = $cad->getSys003();

        // $form = new form01();

        $html .=                    '<form method="POST" action="' . $this->_linkSalvarAlteracoes . '&id='.$this->_id_codifcado.'">
                                        <table>';
        foreach($param as $p) {
            if(isset($this->_dados[$p['campo']])) {
                if($p['campo'] != 'id' && $p['usado'] == 'S') {
                    $html .=                    '<tr>
                                                    <td>
                                                        <label for="'.$p['campo'].'">'.$p['etiqueta'].'  </label>
                                                    </td>
                                                    <td>';
                                                        if($p['tipo'] == 'A' || !empty($p['funcao_lista']) || !empty($p['opcoes']) || !empty($p['tabela_itens'])) {
                                                            $html .= '<select id="'.$p['campo'].'" name="'.$p['campo'].'">';
                                                            $html .=    $this->getOpcoes($p, $this->_dados[$p['campo']]);
                                                            $html .= '</select>';
                                                        } else {
                                                            $html .= '<input type="text" name="'.$p['campo'].'" id="'.$p['campo'].'" value="'.$this->_dados[$p['campo']].'">';
                                                        }
                    $html .=                        '</td>
                                                </tr>';
                }
            }
        }

        $html .=                        '</table>
                            
                                        <button type="submit" class="btn btn-outline-'.$cor.' float-right btn-sm">Salvar</button>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>';

        // ---------------------- CARD DETALHE ENDERECOS ------------------------
        $cad = new cad01('crm_enderecos',[]);
        $sys003 = $cad->getSys003();
        
        $sql = "SELECT * FROM crm_enderecos WHERE entidade = '{$this->_tabela}' AND cod = {$this->_id} AND ativo = 'S'";
        $valores = query($sql);

        if(is_array($valores) && count($valores) > 0) {
            foreach($valores as $valor) {
                $html .=    '<div class="card col-sm-4" style="width: 18rem;">
                                <div class="summaryViewHeader">
                                    <h4 class="display-inline-block">Endereço</h4>
                                </div>
                                <div class="summaryViewFields">
                                    <div class="recordDetails">
                                        <ul class="todo-list" data-widget="todo-list">';


                $html .=                    '<form method="POST" action="' . $this->_linkSalvarAlteracoesEndereco . '&id_endereco='.$valor['id'].'">
                                                <table>';

                foreach($sys003 as $s) {
                    if(isset($valor[$s['campo']])) {
                        $html .=                    '<tr>
                                                        <td>
                                                            <label for="'.$s['campo'].'">'.$s['etiqueta'].'  </label>
                                                        </td>
                                                        <td>';
                                                        if($s['campo'] == 'id') {
                                                            $block = 'disabled=""';
                                                        } else {
                                                            $block = '';
                                                        }
                                                        if($s['campo'] == 'entidade') {
                                                            $html .= '<input type="text" name="'.$s['campo'].'" id="'.$s['campo'].'" value="'.$this->_tabela.'" disabled="">';
                                                        } else if($s['campo'] == 'cod') {
                                                            $html .= '<input type="text" name="'.$s['campo'].'" id="'.$s['campo'].'" value="'.$this->_id.'" disabled="">';
                                                        } else if($s['tipo'] == 'A') {
                                                            $html .= '<select id="'.$s['campo'].'" name="'.$s['campo'].'">';
                                                            $html .=    $this->getOpcoes($s, $valor[$s['campo']]);
                                                            $html .= '</select>';
                                                        } else {
                                                            $html .= '<input type="text" name="'.$s['campo'].'" id="'.$s['campo'].'" value="'.$valor[$s['campo']].'"'.$block.'>';
                                                        }
                        $html .=                        '</td>
                                                    </tr>';
                    }
                }
                $html .=                        '</table>
                                                <button type="submit" class="btn btn-outline-'.$cor.' float-right btn-sm">Salvar</button>
                                            </form>
                                        </ul>
                                    </div>
                                </div>
                            </div>';
            }
        }  else {
            $html .=    '<div class="card col-sm-3" style="width: 18rem;">
                                <div class="summaryViewHeader">
                                    <h4 class="display-inline-block">Endereço</h4>
                                </div>
                                <div class="summaryViewFields">
                                    <div class="recordDetails">
                                        <ul class="todo-list" data-widget="todo-list">';


            $html .=                    '<form method="POST" action="' . getLink() . 'incluirEndereco&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela.'">
                                            <table>';
            $html .=                    '<tr>
                                            <td>
                                                <p class="justify-content-md-center">Não há Endereços.</p>
                                            </td>
                                        </tr>
                                        </table>
                                                <button type="submit" class="btn btn-outline-'.$cor.' float-right btn-sm">Incluir</button>
                                            </form>
                                        </ul>
                                    </div>
                                </div>
                            </div>';
        }
        $html .=    '</div>
                    </div>
                    </div>';
    
        return $html;
    }

    public function cardDocumentos($cor) {
        $link = getLink() . 'salvarDocumentos&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        //$link_baixar = getLink() . 'baixarDocumentos&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        $link_baixar = '/download.php?menu=' . getModulo()  . '.' . getClasse() . '.' . 'baixarArquivo&arquivo=';
        // =============================== CARD RESUMO DOCUMENTOS ==============================
        
        $botao = array();
        $botao['texto']	= 'Baixar   ';
        $botao['id'] = 'bt_excel';
        
        $html = '<div class="card" id="card-documentos">
                    <div class="card-header">
                        <h3 class="card-title">
                            Documentos
                        </h3>
                        <a href="'.$link.'" type="button" class="btn btn-outline-'.$cor.' float-right btn-sm"><i class="fa fa-upload" aria-hidden="true"></i> Importar</a>
                    </div>';
                    $arquivos = [];
                    $arquivos = $this->getArquivosCrm();
    
                    if(is_array($arquivos) && count($arquivos) > 0) {
                        foreach($arquivos as $id_arquivo => $arquivo) {
                            $botao['onclick']= 'window.open(\''. $link_baixar . $id_arquivo . '\')';
                            /*
                            $html .= '<div class="summaryWidgetContainer noContent">
                                        <p class="row justify-content-md-center">'.$arquivo.'</p>
                                            <!-- <a href="'.$link_baixar.'&arquivo='.$id_arquivo.'" class="btn btn-outline-'.$cor.' btn-sm">Baixar</a> -->
                                            ' . formbase01::formBotao($botao) . '
                                    </div>
                                    <br><hr>';
                                    */
                            $html .= '<div class="row">
                                        <div class="col-sm-9 text-truncate">
                                            <p class="row justify-content-md-center">'.$arquivo.'</p>
                                        </div>
                                        <div class="col-sm-1">
                                            ' . formbase01::formBotao($botao) . '
                                        </div>
                                     </div>
                                     <br><hr>';
                        }
                    } else {
                        $html .= '<div class="summaryWidgetContainer noContent">
                                    <p class="row justify-content-md-center">Nenhum documnto</p>
                                </div>';
                    }
        $html .= '</div>';

        return $html;
    }
        
    public function cardDetalhesArquivos() {
        $link_baixar = '/download.php?menu=' . getModulo()  . '.' . getClasse() . '.' . 'baixarArquivo&arquivo=';
        //$salvar = getLink() . 'baixarDocumentos&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        // ================= CARD DETALHE DOCUMENTOS ===========================
        $arquivos = $this->getArquivosCrm();
        $html = '<div class="card" style="width: 85rem;" id="detalhe-documentos">
                    <div class="summaryViewHeader">
                        <h4 class="display-inline-block">Documentos</h4>
                    </div>
                    '.$this->importar();
                    if(is_array($arquivos) && count($arquivos) > 0) {
                        foreach($arquivos as $id_arquivo => $arquivo) {
                            $html .= '<div class="row">
                                        <div class="col-sm-9">
                                            <p class="row justify-content-md-center">'.$arquivo.'</p>
                                        </div>
                                        <div class="col-sm-1">
                                            <a href="'.$link_baixar.$id_arquivo.'" class="btn btn-outline-secondary float-right btn-sm">Baixar</a>
                                        </div>
                                    </div>
                                    <br><hr>';
                        }
                    } else {
                        $html .= '<div class="summaryWidgetContainer noContent">
                                    <p class="row justify-content-md-center">Nenhum documnto</p>
                                </div>';
                    }
        $html .= '</div>';
    
        return $html;
    }

    public function cardPedidos() {
        $html = '<div class="card" id="card-pedidos">
                    <div class="card-header">
                        <h3 class="card-title">
                            Eventos
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="horizontal-scrollable">
                            '.$this->getPedidos().'
                        </div>
                    </div>
                </div>';

        return $html;
    }

    private function cardAtividades($cor) {
        $link = getLink() . 'incluirEvento&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        // putAppVar('link_salvar_cad', getLink() . 'salvarEvento&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela);
        // putAppVar('link_redirecionar_cad_cancelar', $this->_link_redirecionar_cad_cancelar);

        // ============================== CARD RESUMO EVENTOS ================================
        $html = '<div class="card" id="card-atividades">
                    <div class="card-header">
                        <h3 class="card-title">
                        Eventos
                        </h3>
                        <a href="'.$link.'" type="button" class="btn btn-outline-'.$cor.' float-right btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Add Evento</a>
                    </div>
                    <div class="card-header">
                        <div>';
                    $html_cont = $this->getEventos();
                    if(empty($html_cont)){
                        $html .= '<p class="row justify-content-md-center">
                                    Nenhum evento agendado
                                </p>';
                    } else {
                        $html .= "<table class='table table-sm'>
                                    $html_cont
                                </table>";
                    }
        $html .=        '</div>
                    </div>
                </div>';

        return $html;
    }
             
    public function cardDetalhesAtividades($cor) {
        $link = getLink() . 'incluirEvento&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        $link_calendario = getLink() . "calendario&tabela=$this->_tabela&id=$this->_id_codifcado";
        // =========================== CARD DETALHE EVENTOS ===================================
        $html = '<div class="card" id="detalhe-atividades">
                    <div class="card-header">
                        <h3 class="card-title">
                            Eventos
                        </h3>
                        <div class="float-right">
                            <a href="'.$link_calendario.'" type="button" class="btn btn-outline-'.$cor.' btn-sm"><i class="fa fa-calendar" aria-hidden="true"></i> Acessar Calendário</a>
                            <a href="'.$link.'" type="button" class="btn btn-outline-'.$cor.' float-right btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Add Evento</a>
                        </div>
                    </div>
                    <div class="card-header">
                        <div>';
                            $html_cont = $this->getEventos('detalhado');
                            if(empty($html_cont)){
                                $html .= '<p class="row justify-content-md-center">
                                            Nenhum evento agendado
                                        </p>';
                            } else {
                                $html .= "<table class='table table-sm'>
                                            $html_cont
                                        </table>";
                            }
        $html .=        '</div>
                    </div>
                </div>';
    
        return $html;
    }

    public function cardComentarios($cor, $detalhado = false) {
        $html = '<div class="card" id="card-comentarios">
                    <div class="card-header">
                        <h3 class="card-title">Comentários</h3>
                        <br>
                        <form method="POST" action="' . $this->_linkSalvarComentario . '">
                            <textarea id="mensagem" name="mensagem" class="w-100 p-3"></textarea>
                            <br>
                            <input type="submit" value="Postar" class="btn btn-outline-'.$cor.' float-right btn-sm">
                        </form>
                    </div>
                    <div class="card-header">
                        <div class="w-100 p-3">';
    
                    // $sql = "SELECT usuario, data, comentario FROM crm_comentarios WHERE tabela = '{$this->_tabela}' AND id_entidade = {$this->_id}";
                    // $rows = query($sql);

                    // if(is_array($rows) && count($rows) > 0) {
                    //     foreach($rows as $row) {
                    //         $html .= '<span class="justify-content-center"><strong>'.$row['usuario'].'</strong><br>'.$row['comentario'].'<br><br><small>'.$row['data'].'</small></span>
                    //                 <hr>';
                    //     }
                    // } else {
                    //     $html .= '<span class="d-flex justify-content-center" title="Sem Comentários">Sem Comentários</span>';
                    // }

        $html .= $this->getComentarios($cor, $detalhado);
    
        $html .=        '</div>
                    </div>
                </div>';
    
        return $html;
    }

    private function getComentarios($cor, $detalhado) {
        $html = '';
        $comentarios = [];
        $sql = "SELECT * FROM crm_comentarios WHERE tabela = '{$this->_tabela}' AND id_entidade = {$this->_id} ORDER BY data DESC";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row){
                $temp = [];
                $temp['id'] = $row['id'];
                $temp['usuario'] = $row['usuario'];
                $temp['data'] = datas::dataMS2D($row['data']);
                $temp['pai'] = $row['id_pai'];
                $temp['comentario'] = $row['comentario'];

                $this->_comentarios[] = $temp;
            }
        } else {
            $html = '<span class="d-flex justify-content-center" title="Sem Comentários">Sem Comentários</span>';
        }

        if(is_array($this->_comentarios) && count($this->_comentarios) > 0) {
            foreach($this->_comentarios as $key => $coment){
                if($coment['pai'] == 0){
                    $temp = [];
                    $temp['comentario'] = $coment;
                    $temp['filhos'] = $this->getComentFilhos($coment['id']);

                    unset($this->_comentarios[$key]);

                    $comentarios[] = $temp;
                }
            }
        }

        $contador = 0;
        foreach($comentarios as $comentario) {
            if($contador < 5 || $detalhado) {
                $id = base64_encode($comentario['comentario']['id']);
                $html .= '<span class="justify-content-center">
                            <strong>'.$comentario['comentario']['usuario'].'</strong>
                            <br>'.$comentario['comentario']['comentario'].'<br><br>
                            <small>'.$comentario['comentario']['data'].'</small>
                            <br><button onclick="show('.$comentario['comentario']['id'].')" class="btn btn-light btn-sm">Responder</button>
                            <div id="'.$comentario['comentario']['id'].'" style="display:none">
                                <br>
                                <form method="POST" action="' . $this->_linkSalvarComentario."&id_comentario=$id" .'">
                                    <input type="text" id="mensagem" name="mensagem" placeholder="Publique o seu comentário aqui" class="w-75 p-3">
                                    <input type="submit" value="Postar" class="btn btn-success btn-sm">
                                    <input type="reset" onclick="hide('.$comentario['comentario']['id'].')" value="Cancelar" class="btn btn-outline-success btn-sm">
                                </form>
                            </div>
                        </span>
                            <hr><br>';

                if(is_array($comentario['filhos']) && count($comentario['filhos']) > 0) {
                    $html .= $this->htmlFilhos($comentario['filhos'], $cor);
                }
                $contador++;
            }
        }

        return $html;
    }

    private function getComentFilhos($id){
        $ret = [];

        foreach($this->_comentarios as $key => $coment){
            if($coment['pai'] == $id){
                $temp = [];
                $temp['comentario'] = $coment;
                $temp['filhos'] = $this->getComentFilhos($coment['id']);

                unset($this->_comentarios[$key]);

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    private function htmlFilhos($comentarios, $cor, $distancia = 50) {
        $html = '';
        foreach($comentarios as $comentario) {
            $id = base64_encode($comentario['comentario']['id']);
            $html .= '<div style="padding-left: '.$distancia.'px;">
                        <span class="justify-content-center">
                            <strong>'.$comentario['comentario']['usuario'].'</strong>
                            <br>'.$comentario['comentario']['comentario'].'<br><br><small>'.$comentario['comentario']['data'].'</small>
                            <br>
                            <button onclick="show('.$comentario['comentario']['id'].')" class="btn btn-light btn-sm">Responder</button>
                            <div id="'.$comentario['comentario']['id'].'" style="display:none">
                            <br>
                                <form method="POST" action="' . $this->_linkSalvarComentario."&id_comentario=$id" .'">
                                    <input type="text" id="mensagem" name="mensagem" placeholder="Publique a sua resposta aqui" class="w-75 p-3">
                                    <input type="submit" value="Postar" class="btn btn-success btn-sm">
                                    <input type="reset" onclick="hide('.$comentario['comentario']['id'].')" value="Cancelar" class="btn btn-outline-success btn-sm">
                                </form>
                            </div>
                            <hr>
                        </span>
                    </div>
                    <br>';

            if(is_array($comentario['filhos']) && count($comentario['filhos']) > 0) {
                $html .= $this->htmlFilhos($comentario['filhos'], $cor, $distancia + 50);   
            }
        }

        return $html;
    }

    public function cardTarefas($cor) {
        $link = getLink() . 'incluirTarefaCrm&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela . '&tarefa=' . $this->_tarefas['tabela'];
        $html = '<div class="card" id="card-tarefas">
                    <div class="card-header">
                        <h3 class="card-title">
                            Tarefas
                        </h3>
                        <a href="'.$link.'" type="button" class="btn btn-outline-'.$cor.' float-right btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Add Tarefa</a>
                    </div>
                    <div class="card-header">
                        <div>';
        $html .=            $this->getTarefas();
        $html .=        '</div>
                    </div>
                </div>';

        return $html;
    }

    public function cardEmails($cor) {
        $link = getLink() . 'incluirEmail&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        // putAppVar('link_salvar_cad', getLink() . 'salvarEmail&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela);
        // putAppVar('link_redirecionar_cad_cancelar', $this->_link_redirecionar_cad_cancelar);

        $html = '<div class="card" id="detalhe-emails">
                    <div class="card-header">
                        <h3 class="card-title">
                            E-mails
                        </h3>
                        <a href="'.$link.'" type="button" class="btn btn-outline-'.$cor.' float-right btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Add E-mail</a>
                    </div>
                    <div class="card-header">
                        <div>';
        $html_cont = $this->getEmails();
        if(empty($html_cont)){
            $html .= '<p class="row justify-content-md-center">
                        Nenhum E-mail encontrado
                    </p>';
        } else {
            $html .= "<table class='table table-sm'>
                        $html_cont
                    </table>";
        }
        $html .=        '</div>
                    </div>
                </div>';

        return $html;
    }

    public function cardAtualizcoes() {
        $html = '<div class="card" id="detalhe-atualizacoes">
                    <div class="card-header">
                        <h3 class="card-title">
                            Atualizações
                        </h3>
                    </div>
                    <div class="card-header">
                        <div>';
        $html_cont = $this->getAtualizacoes();
        if(!empty($html_cont)) {
            $html .=        $html_cont;
        } else {
            $html .=        '<p class="row justify-content-md-center">
                                Nenhuma atualização feita
                            </p>';
        }
        $html .=        '</div>
                    </div>
                </div>';

        return $html;
    }

    //////////////////// FUNÇÕES GET ///////////////////////
    private function getPedidos() {
        $html = '';

        $sql = "SELECT * FROM crm_pedido_cab WHERE cliente = '$this->_tabela' AND id_cliente = $this->_id AND ativo = 'S'";
        $rows = query($sql);

        $cad = new cad01('crm_pedido_cab');
        $sys003 = $cad->getSys003();

        if(is_array($rows) && count($rows) > 0) {

            $html = '<table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Cond. Pagamento</th>
                                <th>Assunto</th>
                                <th>oportunidades</th>
                                <th>Estagio Pedido</th>
                                <th>Validade</th>
                                <th>Contatos</th>
                                <th>Entrega</th>
                                <th>Organizações</th>
                                <th>Vendedores</th>
                                <th>Prazo Condição</th>
                                <th>Descricao</th>
                            </tr>
                        </thead>
                        <tbody>';
            foreach($rows as $row) {
                $sql = "SELECT * FROM crm_pedido_itens WHERE id_pedido = ".$row['id']." AND ativo = 'S'";
                $itens = query($sql);

                $html .= '<tr>
                            <td>'.$row['cond_pagamento'].'</td>
                            <td>'.$row['assunto'].'</td>
                            <td>'.$row['oportunidades'].'</td>
                            <td>'.$row['estagio_pedido'].'</td>
                            <td>'.$row['validade'].'</td>
                            <td>'.$row['contatos'].'</td>
                            <td>'.$row['entrega'].'</td>
                            <td>'.$row['organizacoes'].'</td>
                            <td>'.$row['vendedores'].'</td>
                            <td>'.$row['prazo_condicao'].'</td>
                            <td>'.$row['descricao'].'</td>
                        </tr>';

                if(is_array($itens) && count($itens) > 0) {
                    foreach($itens as $item) {
                        if(!isset($this->_produtos[$item['id_produto']])) {
                            $sql = "SELECT nome, preco_unit FROM crm_produtos WHERE id = ".$item['id_produto'];
                            $produto = query($sql);

                            if(is_array($produto) && count($produto) > 0) {
                                $this->_produtos[$item['id_produto']]['nome'] = $produto[0]['nome'];
                                $this->_produtos[$item['id_produto']]['valor'] = $produto[0]['preco_unit'];
                            }
                        }

                        $valor_porcentagem = ($item['desconto_porcentagem'] * $this->_produtos[$item['id_produto']]['valor']) / 100;
                        $valor_cobrado = ($this->_produtos[$item['id_produto']]['valor'] * $item['quantidade']) - $item['desconto_valor'] - $valor_porcentagem;
                    
                        $html .= '<tr>
                                    <td colspan="2">'.$this->_produtos[$item['id_produto']]['nome'].'</td>
                                    <td colspan="2">'.$this->_produtos[$item['id_produto']]['valor'].'</td>
                                    <td>'.$item['quantidade'].'</td>
                                    <td>'.$item['seq'].'</td>
                                    <td colspan="2">'.$item['desconto_porcentagem'].'</td>
                                    <td colspan="2">'.$item['desconto_valor'].'</td>
                                    <td colspan="3">(=) '.$valor_cobrado.'</td>
                                </tr>';
                    }
                }
            }
            $html .= '<tbody>
                    </table>';
        }

        return $html;
    }

    private function getEnderecos() {
        $html = '';

        $sql = "SELECT * FROM crm_enderecos WHERE entidade = '{$this->_tabela}' AND cod = {$this->_id}";
        $dados = query($sql);

        $cad = new cad01('crm_enderecos',[]);
        $sys003 = $cad->getSys003();

        if(is_array($dados) && count($dados) > 0) {
            $html = '<table>
                        <thead>
                            <tr>';
            foreach($sys003 as $s) {
                $html .=        '<th>'.$s['etiqueta'].'</th>';
            }
            $html .=        '</tr>
                        </thead>
                        <tbody>';
            foreach($dados as $dado) {
                $html .=    '<tr>';
                foreach($dado as $campo) {
                    $html .=    '<td>'.$campo.'</td>';
                }
                $html .=    '</tr>';
            }
            $html .=    '</tbody>';
                               
        }

        return $html;
    }

    private function getEmails() {
        $html = '';
        $sql = "SELECT * FROM crm_email WHERE entidade = '$this->_tabela' AND cod = {$this->_id} AND ativo = 'S'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            $html = '<table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Entidade</th>
                                <th>Cod.</th>
                                <th>Sequência</th>
                                <th>E-mail</th>
                                <th>Observações</th>
                            </tr>
                        </thead>';

            foreach($rows as $row) {
                if($row['ativo'] == 'S') {
                    $html .= '<tbody>
                                <tr>
                                    <td>'.$row['entidade'].'</td>
                                    <td>'.$row['cod'].'</td>
                                    <td>'.$row['seq'].'</td>
                                    <td>'.$row['email'].'</td>
                                    <td>'.$row['observacao'].'</td>
                                </tr>
                            </tbody>';
                }
            }

            $html .= '</table>';
        }

        return $html;
    }

    private function getAtualizacoes() {
        $html = '';

        $sql = "SELECT * FROM crm_atualizacoes WHERE entidade = '{$this->_tabela}' AND id_entidade = {$this->_id} ORDER BY data DESC"; // DESC LIMIT 10
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            $html = '<table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Descrição</th>
                                <th>Operações</th>
                                <th>Data</th>   
                            </tr>
                        </thead>';

            foreach($rows as $row) {
                $html .= '<tbody>
                            <tr>
                                <td>'.$row['usuario'].'</td>
                                <td>'.$row['descricao'].'</td>
                                <td>'.$row['operacao'].'</td>
                                <td>'.Datas::dataS2D(substr($row['data'], 0, 8)).'</td>
                            </tr>
                        </tbody>';
            }
            $html .= '</table>';
        }

        return $html;
    }

    private function getOpcoes($p, $dado){
        $html = '';
        $tabelas = ['categorias', 'contatos', 'email', 'enderecos', 'evento', 'lead', 'marcas', 'oportunidades', 'organizacoes', 'pipeline_cab', 'pipeline_itens', 'produtos', 'sla', 'tarefa', 'telefones', 'vendedores'];
    
        if(in_array($p['campo'], $tabelas)){
            $sql = "SELECT id, nome FROM crm_".$p['campo'];
            $rows = query($sql);
        
            foreach($rows as $row) {
                if($dado == $row['id']) {
                    $selecionado = 'selected';
                } else {
                    $selecionado = '';
                }
                $html .= '<option value="'.$row['id'].'"'.$selecionado.'>'.$row['nome'].'</option>';
            }
        } else if($p['tabela_itens'] != '') {
            if(isset($p['tabela_itens']) && !empty($p['tabela_itens'])){
                $valores = array();
                $valores[0][0] = "";
                $valores[0][1] = "&nbsp;";
                
                $tab = explode('|', $p['tabela_itens']);
                if(count($tab) > 2){
                    $tabela = $tab[0];
                    $id = $tab[1];
                    $desc = $tab[2];
                    $ordem = isset($tab[3]) && !empty($tab[3])? ' ORDER BY '.$tab[3] : '';
                    $where = isset($tab[4]) && !empty($tab[4]) ? ' WHERE '.$tab[4] : '';
                    $sql = "SELECT $id,$desc FROM $tabela $where $ordem";
                    // echo "$sql <br>\n\n";
                    $rows = query($sql);
                    if(isset($rows[0][$desc])){
                        foreach ($rows as $row){
                            $i = count($valores);
                            $valores[$i][0] = $row[0];
                            $valores[$i][1] = $row[1];
                        }
                    }
                }else{
                    $valores = tabela($p['tabela_itens']);
                }
            }
    
            if(is_array($valores) && count($valores) > 0) {
                foreach($valores as $opcao) {
                    if($dado == $opcao[0]) {
                        $selecionado = 'selected';
                    } else {
                        $selecionado = '';
                    }
                    $html .= '<option value="'.$opcao[0].'"'.$selecionado.'>'.$opcao[1].'</option>';
                }
            }
        } else if(!empty($p['funcao_lista'])) {
            $opcoes = [];
            eval('$opcoes = '.$p['funcao_lista'].';');
    
            foreach($opcoes as $opcao) {
                if($dado == $opcao[0]) {
                    $selecionado = 'selected';
                } else {
                    $selecionado = '';
                }
                $html .= '<option value="'.$opcao[0].'"'.$selecionado.'>'.$opcao[1].'</option>';
            }
        }
    
        return $html;
    }

    private function getEventos($detalhado = '') {
        $html = '';

        $dicionarioStatus = montarDicionarioSys005('CRM011');
    
        $sql = "SELECT * FROM crm_evento WHERE entidade_tipo = '{$this->_tabela}' AND entidade_id = {$this->_id} AND ativo = 'S'";
        $dados = query($sql);
    
        if(is_array($dados) && count($dados) > 0) {
            if($detalhado == '') {
                $html = '<tr>
                            <td>Responsável</td>
                            <td>Data Início</td>
                            <td>Data Fim</td>
                            <td>Status</td>
                        </tr>';
                        
                foreach($dados as $dado) {
                    $responsavel = '';
                    if(isset($dado['responsavel'])) {
                        $sql = "SELECT nome FROM crm_vendedores WHERE id = ".$dado['responsavel'];
                        $param = query($sql);
                        $responsavel = $param[0][0];
                    }

                    $html .= '<tr>
                                <td>'.
                                    $responsavel
                                .'</td>
                                <td>'.
                                    Datas::dataMS2D($dado['dt_inicio'])
                                .'</td>
                                <td>'.
                                    Datas::dataMS2D($dado['dt_fim'])
                                .'</td>
                                <td>'.
                                    ($dicionarioStatus[$dado['status']] ?? '')
                                .'</td>
                            </tr>';
                }
            } else {
                $html = '<table>';
                foreach($dados as $dado) {
                    $html .= '<tr>
                                <td>';
                    $temp =         '<table>
                                        <tr>
                                            <td>' . 
                                                addCard(['conteudo'=>$this->atividadesIdentificadores($dado), 'cor'=>'info']) . 
                                            '</td>
                                            <td>' . 
                                                addCard(['conteudo'=>$this->atividadesDatas($dado), 'cor'=>'info']) . 
                                            '</td>
                                            <td>
                                                '.addCard(['conteudo'=>$this->atividadesLembretes($dado), 'cor'=>'info']).'
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                '.addCard(['conteudo'=>$this->atividadesRecorrencia($dado), 'cor'=>'info']).'
                                            </td>
                                            <td>
                                                '.addCard(['conteudo'=>$this->atividadesOutros($dado), 'cor'=>'info']).'
                                            </td>
                                        </tr>
                                    </table>';
                    $html .= addCard(['conteudo' => $temp, 'titulo' => 'evento '.$dado['nome']]);
                    $html .= '</td>
                            </tr>';
                }
                $html .= '</table>';
            }
        }
        
        return $html;
    }

    private function getDadosTarefas() {
        $ret = [];

        $sql = "SELECT id, nome FROM sys001 WHERE ativo = 'S'";
        $usuarios = query($sql);
        if(is_array($usuarios) && count($usuarios)) {
            foreach($usuarios as $usuario) {
                $this->_usuarios[$usuario['id']] = $usuario['nome'];
            }
        }

        $dicionarioStatus = montarDicionarioSys005('CRM012');

        $rows = query($this->_tarefas['sql']);

// echo $this->_tarefas['tabela'] . '<br>' . $this->_tarefas['sql'] . '<br>';
// print_r($this->_tarefas['campos']);
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $temp = [];
                foreach($this->_tarefas['campos'] as $chave => $valor){
                    if($chave == 'dt_criacao') {
                        $temp[$valor] = datas::dataMS2D($row[$chave]);
                    } else if($chave == 'status') {
                        $temp[$valor] = $dicionarioStatus[$row[$chave]] ?? '';
                    } else if($chave == 'usuario') {
                        $temp[$valor] = $this->_usuarios[$row['usuario']] ?? '';
                    } else {
                        $temp[$valor] = $row[$chave];
                    }
                    // $temp[$valor] = ($chave != 'dt_criacao') ? $row[$chave] : datas::dataMS2D($row[$chave]);
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }

    private function getTarefas() {
        $rows = $this->getDadosTarefas();

        if(is_array($rows) && count($rows)) {
            $html = '<table class="table table-sm">
                        <thead>
                            <tr>';
            foreach($this->_tarefas['campos'] as $campos) {
                if($campos != 'Tarefa') {
                    $html .=    '<th>'.$campos.'</th>';
                }
            }          
            $html .=       '</tr>
                        </thead>
                        <tbody>';
            foreach($rows as $row) {
                $html .=    '<tr>';

                foreach($row as $k => $dado) {
                    if($k != 'Tarefa') {
                        $html .= '<td>'.$dado.'</td>';
                    }
                }
                $html .=    '</tr>';
            }
            $html .=    '</tbody>
                    </table>';
        } else {
            $html = '<p class="row justify-content-md-center">
                        Nenhuma tarefa encontrada
                    </p>';
        }

        return $html;
    }

    private function getArquivosCrm() {
        $ret = array();
        $sql = "select id, nome from sys007 where id in (select id_arquivo from crm_arquivos where tabela_entidade = '{$this->_tabela}' and id_entidade = {$this->_id})";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[base64_encode($row['id'])] = $row['nome'];
            }
        }
        return $ret;
        /*
        global $config;
        $path = $config['arquivosDir'];
        if(file_exists($path . $this->_tabela . DIRECTORY_SEPARATOR . $this->_id)) {
            $ret = [];
            $diretorio = dir($path . $this->_tabela . DIRECTORY_SEPARATOR . $this->_id . DIRECTORY_SEPARATOR);

            while ($arquivo = $diretorio->read()) {
                // $ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
                // $ext = strtolower($ext);
                if ($arquivo != '.' && $arquivo != '..') {
                    $ret[] = $arquivo;
                }
            }

            return $ret;
        }
        */
    }

    /////////////////// FUNÇÕES QUE GERAM OS DETALHAMENTOS DOS EVENTOS //////////////////////
    private function atividadesIdentificadores($dado) {
        $responsavel = '';
        if(isset($dado['responsavel'])) {
            $sql = "SELECT nome FROM crm_vendedores WHERE id = ".$dado['responsavel'];
            $row = query($sql);
            $responsavel = $row[0][0];
        }

        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Responsavel</th>
                            <th>Tipo</th>
                            <th>Onde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.$dado['nome'].'</td>
                            <td>'.$responsavel.'</td>
                            <td>'.$dado['tipo'].'</td>
                            <td>'.$dado['onde'].'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    private function atividadesDatas($dado) {
        $dicionarioStatus = montarDicionarioSys005('CRM011');

        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Data Iní­cio</th>
                            <th>Data Fim</th>
                            <th>Status</th>
                            <th>Dia Todo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.Datas::dataMS2D($dado['dt_inicio']).'</td>
                            <td>'.Datas::dataMS2D($dado['dt_fim']).'</td>
                            <td>'.($dicionarioStatus[$dado['status']] ?? '').'</td>
                            <td>'.($dado['dia_todo'] == 'S' ? 'Sim' : 'Não').'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    private function atividadesLembretes($dado) {
        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Lembrete Dias</th>
                            <th>Lembrete Horas</th>
                            <th>Lembrete minutos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.$dado['lembrete_dias'].'</td>
                            <td>'.$dado['lembrete_horas'].'</td>
                            <td>'.$dado['lembrete_minutos'].'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    private function atividadesRecorrencia($dado) {
        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Recorrente</th>
                            <th>Frequencia</th>
                            <th>Repetição</th>
                            <th>Quant. Recorrência</th>
                            <th>Até Recorrência</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.($dado['recorrente'] == 'S' ? 'Sim' : 'Não').'</td>
                            <td>'.$dado['frequencia'].'</td>
                            <td>'.$dado['repeticao'].'</td>
                            <td>'.$dado['qt_recorrencia'].'</td>
                            <td>'.Datas::dataS2D($dado['ate_recorrencia']).'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    private function atividadesOutros($dado) {
        if(!empty($dado['contato'])) {
            $sql = "SELECT nome FROM crm_contatos WHERE id = ".$dado['contato'];
            $row = query($sql);
        }
        
        $contato = $row[0]['nome'] ?? $dado['contato'];

        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Contato</th>
                            <th>Detalhe</th>
                            <th>Notas</th>
                            <th>Convidados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.$contato.'</td>
                            <td>'.$dado['detalhe'].'</td>
                            <td>'.$dado['notas'].'</td>
                            <td>'.$dado['convidados'].'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    /////////////////// FUNÇÃO PARA GRAVAR AS MODIFICAÇÕES FEITAS NO SITE //////////////////////
    private function gravarAtualizacoes($param) {
        $param['entidade'] = $this->_tabela;
        $param['id_entidade'] = $this->_id;
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');

        $sql = montaSQL($param, 'crm_atualizacoes');
        query($sql);
    }
}
function getListaUsuarios(){
    $ret = array();
    $sql = "select id, nome from sys001";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        foreach ($rows as $row){
            $ret[] = array($row['id'], $row['nome']);
        }
    }
    return $ret;
}