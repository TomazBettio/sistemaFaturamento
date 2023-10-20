<?php
class kanban_lite_bianchini{
    static function montarFormularioCard($id){
        $ret = '';
        //criar um botão para incluir uma compra
        $bt = formbase01::formBotao([
            'texto' => 'Nova Venda',
            'onclick' => "nova_venda($id);",
        ]);
        $ret = addLinha(['tamanhos' => [10, 2], 'conteudos' => ['', $bt]]);
        
        $dados = kanban_lite_bianchini::getDados($id);
        if(count($dados) > 0){
            $tabela = new tabela01();
            $tabela->addColuna(array('campo' => 'comprador' , 'etiqueta' => 'Comprador'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
            $tabela->addColuna(array('campo' => 'vendedor' , 'etiqueta' => 'Fornecedor'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
            $tabela->addColuna(array('campo' => 'quantidade' , 'etiqueta' => 'Quantidade'         , 'tipo' => 'V', 'width' =>  160, 'posicao' => 'E'));
            $tabela->addColuna(array('campo' => 'total' , 'etiqueta' => 'Total'         , 'tipo' => 'V', 'width' =>  160, 'posicao' => 'E'));
            $tabela->setDados($dados);
            $ret .= $tabela;
        }
        
        $ret = '<div style="height: 400px;">' . $ret . "</div>";
        
        return $ret;
    }
    
    static function getDados($id){
        $ret = [];
        $sql = "select compras_bianchini.*, sys001.apelido from compras_bianchini left join sys001 on (compras_bianchini.comprador = sys001.user) where card = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = [];
                $temp['comprador'] = $row['apelido'];
                $temp['vendedor'] = kanban_lite_bianchini::getListaFornecedores($row['fornecedor']);
                //$temp['quantidade'] = formataReais($row['quantidade']);
                $temp['quantidade'] = $row['quantidade'];
                //$temp['total'] = formataReais($row['total']);
                $temp['total'] = $row['total'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    
    static function montarCondeudoCard($id){
        return '';
    }
    
    static function montarFormularioBase($id = ''){
        $form = new form01();
        $form->setPastas([
            0 => 'Principal',
            1 => 'Dados Fornecedor',
            2 => 'Produto',
            3 => 'Movimenção',
        ]);
        $valor_minimo = kanban_lite_bianchini::getPrecoByCard($id);
        $form->addCampo(array('id' => 'fornecedor'      , 'campo' => 'formCard[fornecedor]'		, 'etiqueta' => 'Fornecedor'	        , 'pasta' => 0, 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => true, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => kanban_lite_bianchini::getListaFornecedores(),));
        $form->addCampo(array('id' => 'quantidade'      , 'campo' => 'formCard[quantidade]'		, 'etiqueta' => 'Quantidade'	        , 'pasta' => 0, 'linha' => 1, 'largura' =>4,  'tipo' => 'V'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => true, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard valor', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'preco'           , 'campo' => 'formCard[preco]'		    , 'etiqueta' => 'Preço'	                , 'pasta' => 0, 'linha' => 1, 'largura' =>4,  'tipo' => 'V'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $valor_minimo		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => true, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard valor', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'dt_pagamento'    , 'campo' => 'formCard[dt_pagamento]'   , 'etiqueta' => 'Dt. de Pagamento'	    , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'D'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard data', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'safra'           , 'campo' => 'formCard[safra]'		    , 'etiqueta' => 'Safra'	                , 'pasta' => 2, 'linha' => 1, 'largura' =>4,  'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'intacta'         , 'campo' => 'formCard[intacta]'		, 'etiqueta' => 'Intacta'	            , 'pasta' => 2, 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'tabela_itens' => '000003',));
        $form->addCampo(array('id' => 'credito'         , 'campo' => 'formCard[credito]'		, 'etiqueta' => 'Crédito'	            , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'tabela_itens' => '000003',));
        $form->addCampo(array('id' => 'frete'           , 'campo' => 'formCard[frete]'		    , 'etiqueta' => 'Frete'	                , 'pasta' => 3, 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => kanban_lite_bianchini::getListaFrete(),));
        $form->addCampo(array('id' => 'dt_entrega'      , 'campo' => 'formCard[dt_entrega]'		, 'etiqueta' => 'Dt. de Entrega'	    , 'pasta' => 3, 'linha' => 1, 'largura' =>4,  'tipo' => 'D'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard data', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'dt_retirada'     , 'campo' => 'formCard[dt_retirada]'    , 'etiqueta' => 'Dt. de Retirada'	    , 'pasta' => 3, 'linha' => 1, 'largura' =>4,  'tipo' => 'D'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard data', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'memorando'       , 'campo' => 'formCard[memorando]'		, 'etiqueta' => 'Memorando Exportação'  , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'contrato_tipo'   , 'campo' => 'formCard[contrato_tipo]'	, 'etiqueta' => 'Tipo de Contrato'	    , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => kanban_lite_bianchini::getListaContratos(),));
        $form->addCampo(array('id' => 'participante'    , 'campo' => 'formCard[participante]'	, 'etiqueta' => 'Participante'          , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'senar'           , 'campo' => 'formCard[senar]'		    , 'etiqueta' => 'Senar'	                , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'funrural'        , 'campo' => 'formCard[funrural]'		, 'etiqueta' => 'Funrural'	            , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => '',));
        $form->addCampo(array('id' => 'lti'             , 'campo' => 'formCard[lti]'		    , 'etiqueta' => 'LTI Monsanto'	        , 'pasta' => 1, 'linha' => 1, 'largura' =>4,  'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'lista' => '',));
        return $form;
    }
    
    static function montarFormularioNovaCompra($id){
        $form = kanban_lite_bianchini::montarFormularioBase($id);
        return $form . '';
    }
    
    static function addJs(){
        $ret = '
function nova_venda(id_card){
    //fazer coisas
    var link = "' . getLinkAjax('novaVendaBianchini') . '&id=" + id_card;
    $.get(link, function(retorno){
        document.getElementById("corpo-modal-Secundario").innerHTML = retorno;
        link = "' . getLinkAjax('btSalvarVendaBianchini') . '&id=" + id_card;
        $.get(link, function(retorno){
            document.getElementById("footer-modal-Secundario").innerHTML = retorno;
            link = "' . getLinkAjax('tituloNovaVendaBianchini') . '&id=" + id_card;
            $.get(link, function(retorno){
                document.getElementById("titulo-modal-Secundario").innerHTML = retorno;
                alterarTamanhoModal("grande", "divTamanho-Secundario");
                //criar bt de salvar
                abrirModalSecundario();
                arrumarMascaras();       
            });
        });
    });
}

function salvarVenda(id){
    var valor = document.getElementById("fornecedor").value;
    if(valor == ""){
        alert("Nenhum fornecedor informado");
        return false;
    }
    valor = parseFloat(document.getElementById("quantidade").value.replace(/\./, \'\').replace(/,/, \'.\'));
    if(valor <= 0){
        alert("A quantidade informada deve ser maior que 0(zero)");
        return false;
    }
    valor = parseFloat(document.getElementById("preco").value.replace(/\./, \'\').replace(/,/, \'.\'));
    var link = "' . getLinkAjax('getPrecoMinimo') . '&id=" + id;
    $.get(link, function(retorno){
        if(valor < parseFloat(retorno)){
            alert("O preço informado é menor do que o mínimo");
            return false;
        }
        else{
            var elementos = document.getElementsByClassName("campoEditarCard");
            link = "' . getLinkAjax('salvarCompraBianchini') . '" + "&id=" + id;
            var objeto = {};
            for(let elemento of elementos){
                objeto[elemento.id] = elemento.value;
            }
            $.post(link, objeto, function(retorno){
                $("#myModal-Secundario").modal("toggle");
                //redesenhar conteudo
                carregarModal(id, "editar");
            });
        }
    });
}
';
        addPortaljavaScript($ret);
        
        /*
        $form = kanban_lite_bianchini::montarFormularioBase();
        $form . '';
        */
    }
    
    static function getPrecoByCard($id = ''){
        $ret = 0;
        if(!empty($id)){
            $sql = "select etiqueta from kl_cards where id = $id";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $temp = explode('-', $rows[0]['etiqueta']);
                if(count($temp) >= 2){
                    $valor_bruto = array_pop($temp);
                    $valor_bruto = str_replace(['.', ','], ['', '.'], $valor_bruto);
                    $valor_bruto = preg_replace('/[^0-9.]/i', '', $valor_bruto);
                    $ret = floatval($valor_bruto);
                }
            }
        }
        return $ret;
    }
    
    static function ajax(){
        $ret = '';
        $op = getOperacao();
        $id = $_GET['id'] ?? '';
        if($op === 'novaVendaBianchini'){
            $ret = kanban_lite_bianchini::montarFormularioNovaCompra($id);
        }
        if($op === 'tituloNovaVendaBianchini'){
            $ret = 'Preço Minimo: ' . formataReais(floatval(kanban_lite_bianchini::getPrecoByCard($id)), 2, true);
        }
        if($op === 'btSalvarVendaBianchini'){
            $ret = formbase01::formBotao([
                'texto' => 'Salvar Compra',
                'onclick' => "salvarVenda($id);",
            ]);
        }
        if($op === 'salvarCompraBianchini'){
            $dados = $_POST;
            $dados['quantidade'] = str_replace(['.', ','], ['', '.'], $dados['quantidade']);
            $dados['preco'] = str_replace(['.', ','], ['', '.'], $dados['preco']);
            $dados['total'] = floatval($dados['quantidade']) * floatval($dados['preco']);
            $dados['filial'] = kanban_lite_bianchini::getFilialCard($id);
            $dados['comprador'] = getUsuario();
            $dados['card'] = $id;
            if(!empty($dados['dt_pagamento'])){
                $dados['dt_pagamento'] = datas::dataD2S($dados['dt_pagamento']);
            }
            if(!empty($dados['dt_entrega'])){
                $dados['dt_entrega'] = datas::dataD2S($dados['dt_entrega']);
            }
            if(!empty($dados['dt_retirada'])){
                $dados['dt_retirada'] = datas::dataD2S($dados['dt_retirada']);
            }
            if(!empty($dados['filial']) && !empty($dados['comprador'])){
                $sql = montaSQL($dados, 'compras_bianchini');
                query($sql);
            }
            kanban_lite_bianchini::atualizarResumo($id);
        }
        if($op === 'getPrecoMinimo'){
            die(kanban_lite_bianchini::getPrecoByCard($id));
        }
        return $ret;
    }
    
    static function atualizarResumo($id){
        $sql = "select * from compras_bianchini where card = $id";
        $rows = query($sql);
        $linhas_resumo = [];
        $total_score = 0;
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $fornecedor = kanban_lite_bianchini::getListaFornecedores($row['fornecedor']);
                $valor = formataReais($row['total'], 2, true);
                $total_score += $row['total'];
                $linhas_resumo[] = "$fornecedor - $valor";
            }
        }
        $resumo = implode('<br>', $linhas_resumo);
        $sql = "update kl_cards set resumo = '$resumo', score = $total_score where id = $id";
        query($sql);
    }
    
    static function getFilialCard($id_card){
        $ret = '';
        $sql = "select etiqueta from kl_etapas where id in (select etapa from kl_cards where id = $id_card)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['etiqueta'];
        }
        return $ret;
    }
    
    static function getListaFornecedores($indice = false){
        $dados =  [
            ['', ''],
            ['f1' , 'Fornecedor 1'],
            ['f2' , 'Fornecedor 2'],
            ['f3' , 'Fornecedor 3'],
        ];
        if($indice !== false){
            foreach ($dados as $d){
                if($d[0] == $indice){
                    return $d[1];
                }
            }
        }
        return $dados;
    }
    
    static function getListaFrete($indice = ''){
        $dados =  [
            ['', ''],
            ['CIF' , 'CIF'],
            ['FOB' , 'FOB'],
        ];
        return empty($indice) ? $dados : ($dados[$indice] ?? '');
    }
    
    static function getListaContratos($indice = ''){
        $dados =  [
            ['', ''],
            ['t1' , 'Tipo 1'],
            ['t2' , 'Tipo 2'],
            ['t3' , 'Tipo 3'],
        ];
        return empty($indice) ? $dados : ($dados[$indice] ?? '');
    }
}