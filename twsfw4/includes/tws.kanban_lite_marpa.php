<?php
class kanban_lite_marpa{
    static function montarFormularioCard($id){
        $ret = '';
        $sql = "select * from cards_marpa where card = $id";
        $rows = query($sql);
        if(is_array($rows)){
            if(count($rows) > 0){
                $dados = $rows[0];
                $form = new form01();
                $form->addCampo(array('id' => 'descricao', 'campo' => 'formCard[descricao]'		, 'etiqueta' => 'Conteudo'			, 'linha' => 1, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['descricao']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $TaConteudo = $form . '';
                //$ret .= empty($dados['resumo']) ? '' : $dados['resumo'];
                //$ret .= $form;
                $form = new form01();
                $form->addCampo(array('id' => 'contrato'         , 'campo' => 'formCard[contrato]'		       , 'etiqueta' => 'Contrato'			               , 'linha' => 1, 'largura' =>4, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['contrato']		     , 'lista' => '', 'tabela_itens' => ""      , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'cnpj'              , 'campo' => 'formCard[cnpj]'		           , 'etiqueta' => 'CNPJ'			                   , 'linha' => 1, 'largura' =>4, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['cnpj']		         , 'lista' => '', 'tabela_itens' => ""      , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard cnpj'));
                $form->addCampo(array('id' => 'plataforma'        , 'campo' => 'formCard[plataforma]'		   , 'etiqueta' => 'Plataforma/Agência'			       , 'linha' => 1, 'largura' =>4, 'tipo' => 'T'	, 'tamanho' => '4' , 'linhas' => '', 'valor' => $dados['plataforma']		 , 'lista' => '', 'tabela_itens' => ""      , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard numero'));
                $form->addCampo(array('id' => 'credito_apurado'   , 'campo' => 'formCard[credito_apurado]'	   , 'etiqueta' => 'Créditos Gerados/Apurados'		   , 'linha' => 1, 'largura' =>4, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['credito_apurado']	 , 'lista' => '', 'tabela_itens' => ""      , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard valor'));
                $form->addCampo(array('id' => 'credito_aprovado'  , 'campo' => 'formCard[credito_aprovado]'	   , 'etiqueta' => 'Crédito Aprovado'			       , 'linha' => 1, 'largura' =>4, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['credito_aprovado']	 , 'lista' => '', 'tabela_itens' => ""      , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard valor'));
                $form->addCampo(array('id' => 'credito_utilizacao', 'campo' => 'formCard[credito_utilizacao]'  , 'etiqueta' => 'Tipo de Utilização de Crédito'	   , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['credito_utilizacao'] , 'lista' => '', 'tabela_itens' => "MPTUC" , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'tipo_faturamento'  , 'campo' => 'formCard[tipo_faturamento]'	   , 'etiqueta' => 'Tipo de Faturamento'			   , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['tipo_faturamento']	 , 'lista' => '', 'tabela_itens' => "MPTFAT", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'cliente'           , 'campo' => 'formCard[cliente]'		       , 'etiqueta' => 'Origem de Cliente'			       , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['cliente']		     , 'lista' => '', 'tabela_itens' => "MPCLI" , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'tipo_itau'         , 'campo' => 'formCard[tipo_itau]'		   , 'etiqueta' => 'Tipo ITAÚ'			               , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['tipo_itau']		     , 'lista' => '', 'tabela_itens' => "MPITAU", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'segmento'          , 'campo' => 'formCard[segmento]'		       , 'etiqueta' => 'Segmento'			               , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['segmento']		     , 'lista' => '', 'tabela_itens' => "MPSEG" , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'estagio'           , 'campo' => 'formCard[estagio]'		       , 'etiqueta' => 'Estágio de Negociação/Atendimento' , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['estagio']		     , 'lista' => '', 'tabela_itens' => "MPEST" , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'declinado'         , 'campo' => 'formCard[declinado]'		   , 'etiqueta' => 'Declinado'			               , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['declinado']		     , 'lista' => '', 'tabela_itens' => "MPDECL", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'invalido'          , 'campo' => 'formCard[invalido]'		       , 'etiqueta' => 'Inválido'			               , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['invalido']		     , 'lista' => '', 'tabela_itens' => "MPINVL", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                $form->addCampo(array('id' => 'regime'            , 'campo' => 'formCard[regime]'		       , 'etiqueta' => 'Regime Tributário'			       , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['regime']		     , 'lista' => '', 'tabela_itens' => "MPREGT", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard'));
                
                $labels = $form . '';
                
                $bloco_novo_comentario = kanban_lite::montarBlocoNovoComentario($id);
                /*
                 $bloco_anexo = '';
                 $param = [];
                 $param['url'] = getLinkAjax('anexoMarpa') . "&id=$id";
                 $bloco_anexo .= formbase01::formUploadFile($param);
                 */
                $bloco_anexo = kanban_lite::desenharBlocoAnexo($id);
                
                $param = array();
                $param['tamanhos'] = array(8, 4);
                $param['conteudos'] = array(
                    '<div id="linhaTempo">' . kanban_lite::gerarHistoricoComentarios($id) . '</div>',
                    addCard(array('titulo' => 'Novo Comentário', 'conteudo' => $bloco_novo_comentario)) . addCard(array('titulo' => 'Anexos', 'conteudo' => $bloco_anexo)));
                $historico = addLinha($param) . '<br>';
                $tabs = array(
                    array('titulo' => 'Principal', 'conteudo' => $TaConteudo),
                    array('titulo' => 'Labels', 'conteudo' => $labels),
                    array('titulo' => 'Histórico', 'conteudo' => $historico . ''),
                    array('titulo' => 'Contrato', 'conteudo' => kanban_lite_marpa::montarBlocoContratoMarpa($id)),
                );
                $ret .= formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tabs));
            }
            else{
                $sql = "insert into cards_marpa (card) values ($id)";
                query($sql);
                $ret = kanban_lite_marpa::montarFormularioCard($id);
            }
        }
        return $ret;
    }
    
    static function montarBlocoContratoMarpa($id){
        $ret = '';
        $param = array();
        $param['tamanhos'] = array(4, 8);
        
        $contrato = kanban_lite_marpa::montarHtmlLinkContrato($id);
        $form = new form01();
        $form->addCampo(array('tipo' => 'F', 'nome' => 'id_campo_contrato', 'id' => 'id_campo_contrato', 'campo' => 'id_campo_contrato', 'estilo' => 'opacity:0'));
        
        $bt_enviar_arquivo = formbase01::formBotao(array('texto' => 'Enviar Contrato', 'onclick' => 'enviarArquivoContrato(' . $id . ')'));
        
        $param['conteudos'] = array($contrato, $form . '<br>' . $bt_enviar_arquivo);
        $ret = addLinha($param);
        return $ret;
    }
    
    static function montarHtmlLinkContrato($id){
        $ret = '';
        if(is_dir("/var/www/crm/anexosKanbanLite/$id/contrato")){
            $ret = '<a class="btn btn-tool" href="' . getLinkAjax('mostrarContrato') . "&id=$id" . '" download>' . formbase01::formBotao(array('texto' => 'Baixar o contrato', 'cor' => 'success')) . '</a>';
        }
        else{
            $ret = formbase01::formBotao(array('texto' => 'Contrato ainda não informado', 'cor' => 'success', 'ativo' => false));
        }
        $ret = '<div id="idBlocoContrato">' . $ret . '</div>';
        return $ret;
    }
    
    static function ajax(){
        $ret = '';
        $op = getOperacao();
        $id = getParam($_GET, 'id', '');
        $ret = '';
        if($op === 'mostrarContrato'){
            kanban_lite_marpa::mostrarContrato($id);
        }
        if($op === 'salvarContrato'){
            $ret = kanban_lite_marpa::salvarContrato($id);
        }
        return $ret;
        
    }
    
    static function mostrarContrato($id){
        $dir = "/var/www/crm/anexosKanbanLite/$id/contrato";
        if(is_dir($dir)){
            $arquivos = scandir($dir);
            if(is_array($arquivos) && count($arquivos) > 0){
                $file = '';
                foreach ($arquivos as $arq){
                    if($arq != '.' && $arq != '..'){
                        $file = $arq;
                    }
                }
                if(!empty($file)){
                    kanban_lite::mostrarArquivo($dir . '/' . $file);
                }
            }
        }
    }
    
    static function salvarContrato($id){
        if(isset($_FILES['arquivo']) && isset($_FILES['arquivo']['tmp_name'])){
            $dir = "/var/www/crm/anexosKanbanLite/$id/contrato";
            if(!is_dir($dir)){
                mkdir($dir);
            }
            else{
                $arquivos = scandir($dir);
                if(is_array($arquivos) && count($arquivos) > 0){
                    foreach ($arquivos as $arq){
                        if($arq != '.' && $arq != '..'){
                            unlink($dir . '/' . $arq);
                        }
                    }
                }
            }
            $origem = $_FILES['arquivo']['tmp_name'];
            $destino = $dir . '/' . $_FILES['arquivo']['name'];
            move_uploaded_file($origem, $destino);
        }
        
        return kanban_lite_marpa::montarHtmlLinkContrato($id);
    }
    
    static function montarCondeudoCard($id){
        $ret = '';
        $sql = "select descricao from cards_marpa where card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = nl2br($rows[0]['descricao']);
            //$ret = str_replace('\n', '<br>', $rows[0]['descricao']);
        }
        return $ret;
    }
    
    static function salvarEditar($id){
        $campos = array('descricao', 'contrato', 'cnpj', 'plataforma', 'credito_apurado', 'credito_aprovado', 'credito_utilizacao', 'tipo_faturamento', 'cliente', 'tipo_itau', 'segmento', 'estagio', 'declinado', 'invalido', 'regime');
        $dados = array();
        foreach ($campos as $campo){
            $dados[$campo] = $_POST[$campo];
        }
        if(!empty($dados['cnpj'])){
            $dados['cnpj'] = str_replace(array('.', '/', '-'), '', $dados['cnpj']);
        }
        if(empty($dados['credito_apurado'])){
            $dados['credito_apurado'] = 0;
        }
        else{
            $dados['credito_apurado'] = str_replace(array('.', ','), array('', '.'), $dados['credito_apurado']);
        }
        if(empty($dados['credito_aprovado'])){
            $dados['credito_aprovado'] = 0;
        }
        else{
            $dados['credito_aprovado'] = str_replace(array('.', ','), array('', '.'), $dados['credito_aprovado']);
        }
        
        $sql = montaSQL($dados, 'cards_marpa', 'UPDATE', "card = $id");
        query($sql);
    }
    
    static function addJs(){
        $ret = "
function enviarArquivoContrato(id){
    var arquivo = document.getElementById('id_campo_contrato').files[0];
    var formData = new FormData();
    formData.append('arquivo', arquivo);
    var xhr = new XMLHttpRequest();
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Requisição concluída com sucesso
            //alert(xhr.response);
            document.getElementById('idBlocoContrato').innerHTML = xhr.response;
            //console.log(xhr.response);
        } 
    else {
            // Ocorreu um erro durante a requisição
            console.error('Ocorreu um erro durante a requisição.');
        }
    };
    var link_servidor = '" . getLinkAjax('salvarContrato') . "' + '&id=' + id;
    xhr.open('POST', link_servidor, true);
    xhr.send(formData);
}
";
        addPortaljavaScript($ret, 'F');
    }
}