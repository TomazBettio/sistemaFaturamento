<?php

if (!defined('TWSiNet')|| !TWSiNet) die('Esta nao e uma pagina de entrada valida');

class kanban_lite_texto{
    static function montarFormularioCard($id){
        $ret = '';
        $sql = "select * from kl_textos where card = $id";
        $rows = query($sql);
        if(is_array($rows)){
            if(count($rows) > 0){
                $dados = $rows[0];
                $form = new form01();
                $form->addCampo(array('id' => 'texto', 'campo' => 'formCard[descricao]'		, 'etiqueta' => 'Conteudo'			, 'linha' => 1, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['texto']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $TaConteudo = $form . '';
                
                $bloco_novo_comentario = kanban_lite::montarBlocoNovoComentario($id);

                $bloco_anexo = kanban_lite::desenharBlocoAnexo($id);
                
                $param = array();
                $param['tamanhos'] = array(8, 4);
                $param['conteudos'] = array(
                    '<div id="linhaTempo">' . kanban_lite::gerarHistoricoComentarios($id) . '</div>',
                    addCard(array('titulo' => 'Novo Comentário', 'conteudo' => $bloco_novo_comentario)) . addCard(array('titulo' => 'Anexos', 'conteudo' => $bloco_anexo)));
                $historico = addLinha($param) . '<br>';
                $tabs = array(
                    array('titulo' => 'Principal', 'conteudo' => $TaConteudo),
                    array('titulo' => 'Histórico', 'conteudo' => $historico . ''),
                );
                $ret .= formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tabs));
            }
            else{
                $sql = "insert into kl_textos values ($id, '')";
                query($sql);
                $ret = kanban_lite_texto::montarFormularioCard($id);
            }
        }
        return $ret;
    }
    
    static function novoCardCompleto($coluna){
        $ret = '';
        $aba_geral = kanban_lite_thiel::formularioNovaTarefa();
        
        $form = new form01();
        $form->addCampo(array('id' => 'texto', 'campo' => 'formCard[descricao]'		, 'etiqueta' => 'Conteudo'			, 'linha' => 1, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
        $TaConteudo = $form . '';
        
        $tabs = array(
            array('titulo' => 'Geral', 'conteudo' => $aba_geral . ''),
            array('titulo' => 'Principal', 'conteudo' => $TaConteudo),
        );
        $ret = formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tabs));
        return $ret;
    }
    
    static function montarCondeudoCard($id){
        $ret = '';
        $sql = "select texto from kl_textos where card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = nl2br($rows[0]['texto']);
        }
        return $ret;
    }
    
    static function salvarEditar($id){
        $texto = $_POST['texto'];
        $texto = str_replace("'", "''", $texto);
        $sql = "update kl_textos set texto = '$texto' where card = $id";
        query($sql);
    }
}

















?>