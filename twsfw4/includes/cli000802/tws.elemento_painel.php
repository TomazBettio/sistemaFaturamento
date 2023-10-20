<?php
/*
 * Data Criacao 15/12/2022
 * Autor: TWS - Rafael Postal
 *
 * Descricao:
 *
 * Altera��es:
 *
 */
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class elemento_painel {
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
    // Tabela para atualizações
    private $_tabela_atualizacoes;
    // Tabela conexão colaborador
    private $_tablela_conexao;
    // Tabela contrato de expectativa
    private $_tabela_contrato;
    // Tabela de endereços
    private $_tabela_enderecos;
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
    // link para salvar alterações no endereço
    protected $_linkSalvarAlteracoesEndereco;
    // Link para cancelar criação de evento
    protected $_link_redirecionar_cad_cancelar;
    // Link para incluir evento
    protected $_link_incluir_evento;
    // Link para incluir endereços
    protected $_link_incluir_endereco;
    // Link para salvar alterações em conexão com o colaborador
    protected $_link_incluir_alteracoes_conexao;
    // Link para salvar alterações em contrato de expectativaas
    protected $_link_incluir_alteracoes_contrato;
    // Link para gerar PDF de contrato de expetativa
    protected $_link_pdf_contrato;
    // Link para gerar PDF de conexão com o colaborador
    protected $_link_pdf_conexao;
    // Link para gerar calendário
    protected $_link_calendario;

    // Comentarios
    private $_comentarios;
    
    public function __construct($tabela, $id, $tabela_atualizacoes, $param = array()){
        if(!empty($tabela) && !empty($id)){
            $this->_id = $id;
            $this->_id_codifcado = base64_encode($this->_id);
            $this->_tabela = $tabela;
            $this->_tabela_atualizacoes = $tabela_atualizacoes;
            
            $cad = new cad01($tabela);
            $this->_sys002 = $cad->getSys002();
            $this->_sys003 = $cad->getSys003();
            $this->_dados = $cad->getEntrada($this->_id, false);
            $this->_titulo = ucfirst(str_replace('_', ' ', str_replace('rh_', '', $this->_tabela)));
            $this->_linkBase = ($param['link_base'] ?? getLink() . 'perfil') . '&tabela='.$this->_tabela . '&id='.$this->_id_codifcado;
            $this->_linkSalvarComentario = ($param['link_salvar_comentario'] ?? getLink() . 'salvarComentarioPerfil') . '&tabela='.$this->_tabela.'&id='.$this->_id_codifcado;
            $this->_linkSalvarAlteracoes = ($param['link_salvar_alteracoes'] ?? getLink() . 'salvarAteracoesPerfil') . '&tabela='.$this->_tabela.'&id='.$this->_id_codifcado;
            $this->_linkSalvarAlteracoesEndereco = ($param['link_salvar_alteracoes_endereco'] ?? getLink() . 'salvarAteracoesEndereco') . '&tabela='.$this->_tabela.'&id='.$this->_id_codifcado;
            $this->_link_redirecionar_cad_cancelar = ($param['link_cancelar'] ?? (getLink() . getMetodo() . "&id={$this->_id_codifcado}")) . "&tabela=".$this->_tabela;
            $this->_link_incluir_evento = ($param['link_incluir_evento'] ?? getLink() . 'incluirEvento') . "&id={$this->_id_codifcado}&tabela=".$this->_tabela;
            $this->_link_incluir_endereco = ($param['link_incluir_endereco'] ?? getLink() . 'incluirEndereco') . "&id={$this->_id_codifcado}&tabela=".$this->_tabela;
            $this->_link_incluir_alteracoes_conexao = ($param['link_incluir_alteracoes_conexao'] ?? getLink() . 'salvarAlteracoesConexao') . "&id={$this->_id_codifcado}&tabela=".$this->_tabela;
            $this->_link_incluir_alteracoes_contrato = ($param['link_incluir_alteracoes_contrato'] ?? getLink() . 'salvarAlteracoesContrato') . "&id={$this->_id_codifcado}&tabela=".$this->_tabela;
            $this->_link_pdf_contrato = ($param['link_pdf_contrato'] ?? getLink() . 'gerarPDF') . "&id={$this->_id_codifcado}&tabela=".$this->_tabela;
            $this->_link_pdf_conexao = ($param['link_pdf_conexao'] ?? getLink() . 'gerarPDFConexao') . "&id={$this->_id_codifcado}&tabela=".$this->_tabela;
            $this->_link_calendario = ($param['link_calendario'] ?? getLink() . 'calendario') . "&id={$this->_id_codifcado}&tabela=".$this->_tabela;

            $this->criaFuncoes();
        }
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
            return '';
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
            // echo "$cont arquivos salvos com sucesso!";
            // redireciona(getLink() . "avisos&tabela=$tabela&id=$id&mensagem=$cont arquivos salvos com sucesso!&tipo=erro");

            $param = [];
            $param['descricao'] = $cont.' novos arquivos salvos';
            $param['operacao'] = 'inclusão';

            $this->gravarAtualizacoes($param);
        } else {
            // echo "nenhum arquivo anexado ou arquivo incorreto";
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
	
    //////////////////// HTML INTEIRO /////////////////////////
	protected function criaFuncoes() {
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
            const botoes = ["btn-todos", "btn-camposChave", "btn-documentos", "btn-atividades", "btn-emails", "btn-comentarios", "btn-atualizacoes", "btn-conexao", "btn-contrato"];

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
            const ids = ["card-camposChave", "card-documentos", "card-atividades", "card-comentarios", "card-contratos"];
            const detalhados = ["detalhe-camposChave", "detalhe-documentos", "detalhe-atividades", "detalhe-emails", "detalhe-atualizacoes", "card-conexao"];

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
            const detalhados = ["detalhe-camposChave", "detalhe-documentos", "detalhe-atividades", "detalhe-emails", "detalhe-atualizacoes", "card-conexao"];

            detalhados.forEach(hide);
        }');
        addPortaljavaScript('window.onload = function() {
            //dom not only ready, but everything is loaded
            excluiDetalhado();
        }');

        // $tarefa = $_GET['tarefa'];
		$ret = "function callAjax(operacao){
    				link = '" . getLinkAjax('') . "' + operacao + '&tabela={$this->_tabela}&id={$this->_id}';
                    $.get(link, function(retorno){
                        document.getElementById('adwsadasdsda').innerHTML = retorno;
                    });
    		  }";
		addPortaljavaScript($ret);
	}

    // protected function addJSAjax(){
    //  $tarefa = $_GET['tarefa'];
	// 	$ret = "function callAjax(operacao){
    // 				link = '" . getLinkAjax('') . "' + operacao + '&tarefa=$tarefa';
    //                 $.get(link, function(retorno){
    //                     document.getElementById('adwsadasdsda').innerHTML = retorno;
    //                 });
    // 		  }";
	// 	addPortaljavaScript($ret);
	// }

    ////////////////////// CABEÇALHO site ////////////////////////////////
    protected function cabecalhoHtml($cor) {
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
                                    <h4>'.$this->_dados['nome'].' '.($this->_dados['sobrenome'] ?? '').'
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

    protected function navegacao($cor) {
        $html = '<div class="card" id="div-botoes">
                    <div class="container">
                        <nav class="row justify-content-md-center">
                            <div class="row">
                                <div class="col-sm" style="border-bottom: thick solid #000000"><button onclick="minhaFuncao(\'todos\'); color(this.id); callAjax(\'elemento_resumo\');" class="btn btn-outline-'.$cor.' btn-sm" id="btn-todos">Resumo</i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-camposChave\'); color(this.id); callAjax(\'elemento_detalhes\');" class="btn btn-outline-'.$cor.' btn-sm" id="btn-camposChave">Detalhes</button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-atualizacoes\'); color(this.id); callAjax(\'elemento_atualizacoes\');" class="btn btn-outline-'.$cor.' btn-sm" id="btn-atualizacoes">Atualizações</button></div>
                                <div class="col-sm"></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-atividades\'); color(this.id); callAjax(\'elemento_eventos\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Eventos" id="btn-atividades"><i class="fa fa-calendar fa-2x" aria-hidden="true"></i></button></div>
                                <!-- <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-emails\'); color(this.id); callAjax(\'elemento_emails\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="E-mails" id="btn-emails"><i class="fa fa-envelope fa-2x" aria-hidden="true"></i></button></div> -->
                                <div class="col-sm"><button onclick="minhaFuncao(\'detalhe-documentos\'); color(this.id); callAjax(\'elemento_documentos\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Documentos" id="btn-documentos"><i class="fa fa-file-text fa-2x" aria-hidden="true"></i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'card-comentarios\'); color(this.id); callAjax(\'elemento_comentarios\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Comentários" id="btn-comentarios"><i class="fa fa-commenting fa-2x" aria-hidden="true"></i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'card-conexao\'); color(this.id); callAjax(\'elemento_conexao\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Conexão com colaborador" id="btn-conexao"><i class="fa fa-users fa-2x" aria-hidden="true"></i></button></div>
                                <div class="col-sm"><button onclick="minhaFuncao(\'card-contrato\'); color(this.id); callAjax(\'elemento_contrato\');" class="btn btn-outline-'.$cor.' btn-sm" data-toggle="tooltip" data-toggle="tooltip" data-placement="top" title="Contrato de expectativa" id="btn-contrato"><i class="fa fa-list-alt fa-2x" aria-hidden="true"></i></button></div>
                            </div>
                        </nav>
                    </div>
                </div>';

        return $html;
    }

    /////////////////// cards ///////////////////////////
    protected function cardCamposChave($cor) {
        // ================== CARD RESUMO CAMPOS CHAVE =================================
        $html = '<div class="card" id="card-camposChave">
                    <div class="summaryViewHeader">
                        <h4 class="display-inline-block">Campos Chave</h4>
                    </div>
                    <div class="summaryViewFields">
                            <div class="recordDetails">
                                <ul class="todo-list" data-widget="todo-list">';
        // $cad = new cad01($this->_tabela,[]);
        // $param = $cad->getSys003();

        // $form = new form01();
    
        $html .= '<form method="POST" action="' . $this->_linkSalvarAlteracoes.'">
                    <table>';
        if(is_array($this->_sys003) && count($this->_sys003) > 0) {
            foreach($this->_sys003 as $p) {
                if(isset($this->_dados[$p['campo']]) && $p['campo'] != 'id' && $p['campo_chave'] == 'S') {
                    $html .= '<tr>
                                <td>
                                    <label for="'.$p['campo'].'">'.$p['etiqueta'].'  </label>
                                </td>
                                <td>';
                                    if($p['tipo'] == 'A') {
                                        $html .= '<select id="'.$p['campo'].'" name="'.$p['campo'].'">';
                                        $html .=    $this->getOpcoes($p, $this->_dados[$p['campo']]);
                                        $html .= '</select>';
                                    } else {
                                        $html .= '<input type="text" name="'.$p['campo'].'" id="'.$p['campo'].'" value="'.$this->_dados[$p['campo']].'">';
                                    }
                    $html .=    '</td>
                            </tr>';
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
            
    public function cardDetalhesCamposChave($tabela_enderecos, $cor) {
        $this->_tabela_enderecos = $tabela_enderecos;
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

        // $cad = new cad01($this->_tabela,[]);
        // $param = $cad->getSys003();

        // $form = new form01();

        $html .=                    '<form method="POST" action="' . $this->_linkSalvarAlteracoes.'">
                                        <table>';
        
        $html .=                            $this->getCamposChaves();

        $html .=                        '</table>
                            
                                        <button type="submit" class="btn btn-outline-'.$cor.' float-right btn-sm">Salvar</button>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>';

        // ---------------------- CARD DETALHE ENDERECOS ------------------------
        
        $html .= $this->getEnderecos($cor);

        $html .=    '</div>
                    </div>
                    </div>';
    
        return $html;
    }

    private function getCamposChaves() {
        $html = '';
        
        foreach($this->_sys003 as $p) {
            if(isset($this->_dados[$p['campo']]) && $p['campo'] != 'id') {
                $html .= '<tr>
                            <td>
                                <label for="'.$p['campo'].'">'.$p['etiqueta'].'  </label>
                            </td>
                            <td>';
                                if($p['tipo'] == 'A') {
                                    $html .= '<select id="'.$p['campo'].'" name="'.$p['campo'].'">';
                                    $html .=    $this->getOpcoes($p, $this->_dados[$p['campo']]);
                                    $html .= '</select>';
                                } else {
                                    $html .= '<input type="text" name="'.$p['campo'].'" id="'.$p['campo'].'" value="'.$this->_dados[$p['campo']].'">';
                                }
                $html .=    '</td>
                        </tr>';
            }
        }

        return $html;
    }

    protected function cardDocumentos($tabela_doc, $cor) {
        $link = getLink() . 'salvarDocumentos&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela . "&tabela_doc=$tabela_doc";
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
                    $arquivos = $this->getArquivosCrm($tabela_doc);
    
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
                                        <div class="col-sm-9">
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
        
    public function cardDetalhesArquivos($tabela_doc) {
        $link_baixar = '/download.php?menu=' . getModulo()  . '.' . getClasse() . '.' . 'baixarArquivo&arquivo=';
        //$salvar = getLink() . 'baixarDocumentos&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        // ================= CARD DETALHE DOCUMENTOS ===========================
        $arquivos = $this->getArquivosCrm($tabela_doc);
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

    protected function cardAtividades($tabela_event, $cor) {
        $link = getLink() . 'incluirEvento&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;

        // ============================== CARD RESUMO EVENTOS ================================
        $html = '<div class="card" id="card-atividades">
                    <div class="card-header">
                        <h3 class="card-title">
                        Eventos
                        </h3>
                        <a href="'.$this->_link_incluir_evento.'" type="button" class="btn btn-outline-'.$cor.' float-right btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Add Evento</a>
                    </div>
                    <div class="card-header">
                        <div>';
                    $html_cont = $this->getEventos($tabela_event);
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
             
    public function cardDetalhesAtividades($tabela_event, $cor) {
        $link = getLink() . 'incluirEvento&id='.$this->_id_codifcado . '&tabela=' . $this->_tabela;
        // =========================== CARD DETALHE EVENTOS ===================================
        $html = '<div class="card" id="detalhe-atividades">
                    <div class="card-header">
                        <h3 class="card-title">
                            Eventos
                        </h3>
                        <div class="float-right">
                            <a href="'.$this->_link_calendario.'" type="button" class="btn btn-outline-'.$cor.' btn-sm"><i class="fa fa-calendar" aria-hidden="true"></i> Acessar Calendário</a>
                            <a href="'.$this->_link_incluir_evento.'" type="button" class="btn btn-outline-'.$cor.' float-right btn-sm"><i <i class="fa fa-plus" aria-hidden="true"></i> Add Evento</a>
                        </div>
                    </div>
                    <div class="card-header">
                        <div>';
                            $html_cont = $this->getEventos($tabela_event, 'detalhado');
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

    public function cardContrato($tabela_contrato, $detalhado = false) {
        $this->_tabela_contrato = $tabela_contrato;
        $botao = $detalhado ? '<button class="btn btn-outline-danger float-right btn-sm" onclick="op2(\''.$this->_link_pdf_contrato.'\')"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Gerar PDF</a>' : '';

        $html = '<div class="card" id="card-contratos">
                    <div class="card-header">
                        <h3 class="card-title">
                            Contrato de Expectativas
                        </h3>
                        '.$botao.'
                    </div>
                    <div class="card-header">';
        $html .=        $this->getContratos($detalhado);
        $html .=    '</div>
                </div>';

        return $html;
    }

    private function getContratos($detalhado) {
        $sql = "SELECT * FROM {$this->_tabela_contrato} where id_colaborador = {$this->_id}";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            $contrato = [];
            $contrato['gestor']        = $rows[0]['gestor'];
            $contrato['inicio']        = $rows[0]['inicio'];
            $contrato['fim']           = $rows[0]['fim'];
            $contrato['desempenho']    = $rows[0]['desempenho'];
            $contrato['comportamento'] = $rows[0]['comportamento'];
            $contrato['data']          = $rows[0]['data'];
            $contrato['data_proximo']  = $rows[0]['data_proximo'];

            $cabecalho = $detalhado ? '<th>Desempenho</th>
                                        <th>Comportamento</th>' : '';

        
            $html = '<form method="POST" action="'.$this->_link_incluir_alteracoes_contrato.'">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Gestor</th>
                                <th>Início</th>
                                <th>Fim</th>
                                '.$cabecalho.'
                                <th>Data</th>
                                <th>Data do próximo alinhamento</th>
                            </tr>
                        </thead>';
            $html .=    '<tbody>
                            <tr>';
            foreach($contrato as $k => $dado) {
                if($k != 'desempenho' && $k != 'comportamento' || $detalhado) {
                    $html .=    '<td>
                                    <div id="editar-'.$k.'">
                                        '.nl2br($dado).'
                                        <br>
                                        <button type="button" onclick="show(\'contrato-'.$k.'\'); show(\'botao-contrato\'); hide(\'editar-'.$k.'\');" class="btn btn-light btn-sm">Editar</button>
                                        </div>
                                    <div id="contrato-'.$k.'" style="display: none;">
                                        <textarea style="width: 100%; height: 100%;" id="'.$k.'" name="'.$k.'">'.$dado.'</textarea>
                                    </div>
                                </td>';
                }
            }
                        
            $html .=            '<td><button type="submit" id="botao-contrato" class="btn btn-success float-right" style="display: none;">Enviar</button></td>';
            $html .=        '</tr>
                        </tbody>';

            $html .= '</table>
                    </form>';
        } else {
            $html = '<p class="row justify-content-md-center">
                        Sem contrato
                    </p>';
        }

        return $html;
    }

    public function cardComentarios($tabela_coment, $cor, $detalhado = false) {
        $html = '<div class="card" id="card-comentarios">
                    <div class="card-header">
                        <h3 class="card-title">Comentários</h3>
                        <br>
                        <form method="POST" action="' . $this->_linkSalvarComentario .'">
                            <textarea id="mensagem" name="mensagem" class="w-100 p-3"></textarea>
                            <br>
                            <input type="submit" value="Postar" class="btn btn-outline-'.$cor.' float-right btn-sm">
                        </form>
                    </div>
                    <div class="card-header">
                        <div class="w-100 p-3">';

                    $html .= $this->getComentarios($tabela_coment, $cor, $detalhado);
    
        $html .=        '</div>
                    </div>
                </div>';
    
        return $html;
    }

    private function getRespostas($row) {
        // $sql = "SELECT * FROM rh_comentarios WHERE entidade = 'rh_comentarios'";
        // $comentarios = query($sql);

        $html = '<span class="justify-content-center"><strong>'.$row['usuario']. ', ' .$row['id'] .' -> ' . $row['entidade'] . ' = ' . $row['id_entidade'] .'</strong><br>'.$row['comentario'].'<br><br><small>'.$row['data'].'</small>
        <br>';
        
        foreach($this->_comentarios as $comentario) {
            if($comentario['id_entidade'] == $row['id']) {
                // $teste = $this->getRespostas($comentario);
            }
        }

        return $html;
    }

    private function getComentarios($tabela_coment, $cor, $detalhado){
        $html = '';
        $comentarios = [];
        $sql = "SELECT * FROM $tabela_coment WHERE entidade = '{$this->_tabela}' AND id_entidade = {$this->_id} ORDER BY data DESC";
        $rows = query($sql);

            foreach($rows as $row){
                $temp = [];
                $temp['id'] = $row['id'];
                $temp['usuario'] = $row['usuario'];
                $temp['data'] = datas::dataMS2D($row['data']);
                $temp['pai'] = $row['id_pai'];
                $temp['comentario'] = $row['comentario'];

                $this->_comentarios[] = $temp;
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
        } else {
            $html = '<p class="row justify-content-md-center">
                        Sem comentários
                    </p>';
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

    public function cardEmails($tabela_emails, $cor) {
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
        $html_cont = $this->getEmails($tabela_emails);
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

    public function cardAtualizcoes($tabela_atualizacoes) {
        $html = '<div class="card" id="detalhe-atualizacoes">
                    <div class="card-header">
                        <h3 class="card-title">
                            Atualizações
                        </h3>
                    </div>
                    <div class="card-header">
                        <div>';
        $html .=            $this->getAtualizacoes($tabela_atualizacoes);
        $html .=        '</div>
                    </div>
                </div>';

        return $html;
    }

    public function cardConexao($tablela_conexao) {
        $this->_tablela_conexao = $tablela_conexao;

        $html = '<div class="card" id="card-conexao">
                    <div class="card-header">
                        <h3 class="card-title">Conexão com o colaborador</h3>
                        <button class="btn btn-outline-danger float-right btn-sm" onclick="op2(\''.$this->_link_pdf_conexao.'\')"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Gerar PDF</a>
                    </div>
                    <form method="POST" action="'.$this->_link_incluir_alteracoes_conexao.'">
                        <div class="row card-header justify-content-md-center">';
                        
        $html .=            $this->getConexoes();
    
        $html .=        '<div style = "aling: bottom;"><button type="submit" id="botao-conexao" class="btn btn-success float-right" style="display: none;">Enviar</button></div>
                        </div>
                    </form>
                </div>';
    
        return $html; // float-right
    }

    private function getConexoes() {
        $html = '';
        $sql = "SELECT * FROM $this->_tablela_conexao WHERE id_colaborador = $this->_id";
        $rows = query($sql);

        $cad = new cad01($this->_tablela_conexao);
        $sys003_conexao = $cad->getSys003();

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                foreach($sys003_conexao as $s) {
                    if($s['campo'] != 'id' && $s['campo'] != 'id_colaborador') {
                        $html .= '<div class="card col-sm-2 m-2">
                                    <h4>'.$s['etiqueta'].'</h4>
                                    <div id="campo-'.$s['campo'].'">
                                        <p>'.nl2br($row[$s['campo']]).'</p>
                                        <button type="button" onclick="show(\'conexao-'.$s['campo'].'\'); show(\'botao-conexao\'); hide(\'campo-'.$s['campo'].'\')" id="editar-'.$s['campo'].'" class="btn btn-light btn-sm">Editar</button>
                                    </div>
                                    <div id="conexao-'.$s['campo'].'" style="display: none;">
                                        <textarea id="'.$s['campo'].'" name="'.$s['campo'].'">'.$row[$s['campo']].'</textarea>
                                    </div>
                                </div>';
                    }
                }
            }
        } else {
            $html = '<p class="row justify-content-md-center">
                        Não ha registros de conexão
                    </p>';
        }

        return $html;
    }

    //////////////////// FUNÇÕES GET ///////////////////////
    private function getEnderecos($cor) {
        $html = '';
        $cad = new cad01($this->_tabela_enderecos,[]);
        $sys003 = $cad->getSys003();
        
        $sql = "SELECT * FROM $this->_tabela_enderecos WHERE entidade = '{$this->_tabela}' AND id_entidade = {$this->_id} AND ativo = 'S'";
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


                $html .=                    '<form method="POST" action="' . $this->_linkSalvarAlteracoesEndereco . '&id_endereco='.base64_encode($valor['id']).'">
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
            $html .=    '<div class="card col-sm-4" style="width: 18rem;">
                            <div class="summaryViewHeader">
                                <h4 class="display-inline-block">Endereço</h4>
                            </div>
                            <div class="summaryViewFields">
                                <div class="recordDetails">
                                    <ul class="todo-list" data-widget="todo-list">';


            $html .=                    '<form method="POST" action="' . $this->_link_incluir_endereco .'">
                                            <table>';
            $html .=                            '<tr>
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

        return $html;
    }

    private function getEmails($tabela_emails) {
        $html = '';
        $sql = "SELECT * FROM $tabela_emails WHERE entidade = '$this->_tabela' AND id_entidade = {$this->_id} AND ativo = 'S'";
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

    private function getAtualizacoes($tabela_atualizacoes) {
        $html = '';

        $sql = "SELECT * FROM $tabela_atualizacoes WHERE entidade = '{$this->_tabela}' AND id_entidade = {$this->_id} ORDER BY data DESC";
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
                                <td>'.datas::dataMS2D($row['data']).'</td>
                            </tr>
                        </tbody>';
            }
            $html .= '</table>';
        } else {
            $html = '<p class="row justify-content-md-center">
                        Nenhum Atualização encontrado
                    </p>';
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
                    //echo "$sql <br>\n\n";
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
//OBTENDO DADOS
    private function getEventos($tabela_event, $detalhado = '') {
        $html = '';
    
        $sql = "SELECT * FROM $tabela_event WHERE entidade_tipo = '{$this->_tabela}' AND entidade_id = {$this->_id} AND ativo = 'S'";
        $dados = query($sql);

        if(is_array($dados) && count($dados) > 0) {
            if($detalhado == '') {
                $html = '<table class="table">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Data</th>
                                                <th>Onde</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
            
                foreach($dados as $dado) {
                    $html .=                '<tr>
                                                <td>'.$dado['nome'].'</td>
                                                <td>'.$dado['data'].'</td>
                                                <td>'.$dado['onde'].'</td>
                                            </tr>';
                }
                $html .=                '</tbody>
                                    </table>';
            } else {
                $html = '<table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Data</th>
                                                <th>Tipo</th>
                                                <th>Onde</th>
                                                <th>Detalhes</th>
                                                <th>Notas</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
            
                foreach($dados as $dado) {
                    $html .=                '<tr>
                                                <td>'.$dado['nome'].'</td>
                                                <td>'.$dado['data'].'</td>
                                                <td>'.$dado['tipo'].'</td>
                                                <td>'.$dado['onde'].'</td>
                                                <td>'.$dado['detalhes'].'</td>
                                                <td>'.$dado['notas'].'</td>
                                            </tr>';
                }
                $html .=                '</tbody>
                                    </table>';
            }
        }
        
        return $html;
    }
//OBTENDO DADOS
    private function getArquivosCrm($tabela_doc) {
        $ret = array();
        $sql = "select id, nome from sys007 where id in (select id_arquivo from $tabela_doc where entidade = '{$this->_tabela}' and id_entidade = {$this->_id})";
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
        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Responsavel</th>
                            <th>Tipo</th>
                            <th>Onde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.$dado['id'].'</td>
                            <td>'.$dado['nome'].'</td>
                            <td>'.$dado['tipo'].'</td>
                            <td>'.$dado['onde'].'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    private function atividadesDatas($dado) {
        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Data InÃ­cio</th>
                            <th>Data Fim</th>
                            <th>Status</th>
                            <th>Dia Todo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.$dado['dt_inicio'].'</td>
                            <td>'.$dado['dt_fim'].'</td>
                            <td>'.$dado['status'].'</td>
                            <td>'.$dado['dia_todo'].'</td>
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
                            <td>'.$dado['recorrente'].'</td>
                            <td>'.$dado['frequencia'].'</td>
                            <td>'.$dado['repeticao'].'</td>
                            <td>'.$dado['qt_recorrencia'].'</td>
                            <td>'.$dado['ate_recorrencia'].'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    private function atividadesOutros($dado) {
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
                            <td>'.$dado['contato'].'</td>
                            <td>'.$dado['detalhe'].'</td>
                            <td>'.$dado['notas'].'</td>
                            <td>'.$dado['convidados'].'</td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    /////////////////// FUNÇÃO PARA GRAVAR AS MODIFICAÇÕES FEITAS NO SITE //////////////////////
    public function gravarAtualizacoes($param) {
        $param['entidade'] = $this->_tabela;
        $param['id_entidade'] = $this->_id;
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');

        $sql = montaSQL($param, $this->_tabela_atualizacoes);
        query($sql);
    }
}