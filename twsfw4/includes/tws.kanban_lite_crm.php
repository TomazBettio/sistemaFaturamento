<?php

if (!defined('TWSiNet')|| !TWSiNet) die('Esta nao e uma pagina de entrada valida');

class kanban_lite_crm{
    static function montarFormularioCard($id){
        $ret = '';
        $sql = "select * from kl_crm where card = $id";
        $rows = query($sql);
        if(is_array($rows)){
            if(count($rows) > 0){
                $dados = $rows[0];
                $form = new form01();
                $form->addCampo(array('id' => 'organizacao'         , 'campo' => 'formCard[organizacao]'		, 'etiqueta' => 'Organização'	    , 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['organizacao']		, 'lista' => kanban_lite_crm::montaListaClientes(), 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'vendedor'            , 'campo' => 'formCard[vendedor]'		    , 'etiqueta' => 'Vendedor'			, 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['vendedor']		, 'lista' => kanban_lite_crm::montarListaVendedores(), 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'tipo'                , 'campo' => 'formCard[tipo]'		        , 'etiqueta' => 'Tipo'			    , 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['tipo']		              , 'lista' => kanban_lite_crm::montarListaTipos(), 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'previsao'            , 'campo' => 'formCard[previsao]'		    , 'etiqueta' => 'Previsão'			, 'linha' => 1, 'largura' =>3,  'tipo' => 'D'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['previsao']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard data', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'origem'              , 'campo' => 'formCard[origem]'		        , 'etiqueta' => 'Origem'			, 'linha' => 1, 'largura' =>3,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['origem']		, 'lista' => kanban_lite_crm::montarListaLead(), 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'probabilidade'       , 'campo' => 'formCard[probabilidade]'		, 'etiqueta' => 'Probabilidade'		, 'linha' => 1, 'largura' =>3,  'tipo' => 'N'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['probabilidade']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard numero', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'motivo_perda'        , 'campo' => 'formCard[motivo_perda]'		, 'etiqueta' => 'Motivo Perda'	    , 'linha' => 1, 'largura' =>3,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['motivo_perda']		, 'lista' => tabela('CRM003'), 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'detalhe'             , 'campo' => 'formCard[detalhe]'		    , 'etiqueta' => 'Detalhes'			, 'linha' => 1, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['detalhe']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 8));
                
                $principal = $form . '';
                
                $form = new form01();
                $form->addCampo(array('id' => 'moeda'               , 'campo' => 'formCard[moeda]'		        , 'etiqueta' => 'Moeda'			    , 'linha' => 1, 'largura' =>4,  'tipo' => 'T'	, 'tamanho' => '2' , 'linhas' => '', 'valor' => $dados['moeda']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'valor'               , 'campo' => 'formCard[valor]'		        , 'etiqueta' => 'Valor'			    , 'linha' => 1, 'largura' =>4,  'tipo' => 'V'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['valor']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard valor', 'linhasTA' => 15));
                $form->addCampo(array('id' => 'receita_ponderada'   , 'campo' => 'formCard[receita_ponderada]'  , 'etiqueta' => 'Receita Ponderada'	, 'linha' => 1, 'largura' =>4,  'tipo' => 'V'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['receita_ponderada']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard valor', 'linhasTA' => 15));
                
                $financeiro = $form . '';
                
                $bloco_novo_comentario = kanban_lite::montarBlocoNovoComentario($id);
                
                $bloco_anexo = kanban_lite::desenharBlocoAnexo($id);
                
                $param = array();
                $param['tamanhos'] = array(8, 4);
                $param['conteudos'] = array(
                    addCard(array('conteudo' => '<div id="linhaTempo">' . kanban_lite::gerarHistoricoComentarios($id) . '</div>', 'titulo' => 'Comentários')),
                    addCard(array('titulo' => 'Novo Comentário', 'conteudo' => $bloco_novo_comentario)) . addCard(array('titulo' => 'Anexos', 'conteudo' => $bloco_anexo)));
                $historico = addLinha($param) . '<br>';
                $tabs = array(
                    array('titulo' => 'Histórico', 'conteudo' => $historico . ''),
                    array('titulo' => 'Principal', 'conteudo' => $principal),
                    array('titulo' => 'Financeiro', 'conteudo' => $financeiro),
                );
                $ret .= formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tabs, 'desativado' => [0]));
            }
            else{
                $sql = "insert into kl_crm values ($id, '', 0, '', '', '', '', '', 0, 0, '', '')";
                query($sql);
                $ret = kanban_lite_crm::montarFormularioCard($id);
            }
        }
        return $ret;
    }
    
    static function montaListaClientes(){
        $ret = [['', '']];
        $sql = "select cod, nreduz, tipo from cad_organizacoes order by tipo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $tipo_anterior = '';
            $dicionario = montarDicionarioSys005('CRM016');
            foreach ($rows as $row){
                if($tipo_anterior != $row['tipo']){
                    $ret[] = [$row['tipo'], '--' . $dicionario[$row['tipo']] . '--', true];
                    $tipo_anterior = $row['tipo'];
                }
                $ret[] = [$row['cod'], $row['nreduz']];
            }
        }
        return $ret;
    }
    
    static function montarListaVendedores(){
        $ret = [['', '']];
        $sql = "select id, apelido from sdm_recursos where ativo = 'S' and del != '*'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = [$row['id'], $row['apelido']];
            }
        }
        return $ret;
    }
    
    static function montarListaTipos(){
        $ret = [['', ''], ['negocio', 'Negócio Existente'], ['novo', 'Novo Negócio']];
        return $ret;
    }
    
    static function montarListaLead(){
        $ret = [['', ''], ['cold_call', 'Cold Call'], ['cliente_existente', 'Cliente Existente'], ['auto_gerado', 'Auto Gerado'], ['empregado', 'Empregado'], ['parceiro', 'Parceiro'], ['relacoes_publicas', 'Relações Públicas'], ['mala_direta', 'Mala Direta'], ['conferencia', 'Conferência'], ['feira_negocio', 'Feira Negócio'], ['website', 'Web Site'], ['boca_boca', 'Boca a Boca'], ['outro', 'Outro']];
        return $ret;
    }
    /*
    static function montarCondeudoCard($id){
        $ret = '';
        $sql = "select texto from kl_textos where card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = nl2br($rows[0]['texto']);
        }
        return $ret;
    }
    */
    
    static function salvarEditar($id){
        /*
        $moeda = $_POST['moeda'];
        $valor = $_POST['valor'];
        $valor = str_replace(['.', ','], ['', '.'], $valor);
        $cliente = $_POST['cliente'];
        $vendedor = $_POST['vendedor'];
        $previsao = $_POST['previsao'];
        $lead = $_POST['lead'];
        $tipo = $_POST['tipo'];
        $probabilidade = $_POST['probabilidade'];
        $receita_ponderada = $_POST['receita_ponderada'];
        $receita_ponderada = str_replace(['.', ','], ['', '.'], $receita_ponderada);
        $motivo_perda = $_POST['motivo_perda'];
        $detalhe = $_POST['detalhe'];
        */
        $dados = array();
        foreach ($_POST as $chave => $valor){
            $dados[$chave] = $valor;
        }
        $dados['valor'] = str_replace(['.', ','], ['', '.'], $dados['valor']);
        $dados['receita_ponderada'] = str_replace(['.', ','], ['', '.'], $dados['receita_ponderada']);
        if(!empty($dados['previsao']) && strpos($dados['previsao'], '/') !== false){
            $dados['previsao'] = datas::dataD2S($dados['previsao']);
        }
        
        $sql = montaSQL($dados, 'kl_crm', 'UPDATE', "card = $id");
        query($sql);
        
        $sql = "update kl_cards set score = " . $dados['valor'] . " where id = $id";
        query($sql);
    }
}

















?>