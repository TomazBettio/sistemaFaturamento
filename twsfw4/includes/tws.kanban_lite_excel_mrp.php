<?php

if (!defined('TWSiNet')|| !TWSiNet) die('Esta nao e uma pagina de entrada valida');

class kanban_lite_excel_mrp{
    function __construct()
    {
        
    }
    static function montarCondeudoCard($id){
        $ret = '';
        $sql = "SELECT * FROM kb_excel_mrp where id_card = '$id'";
        $rows = query($sql);
        if(is_array($rows)){
           // var_dump($rows); die();
            if(count($rows) > 0){
              //  echo "achei dados"; die();
                $dados = $rows[0];
                
                $form1 = new form01();
                $form1->addCampo(array('id' => 'descricao', 'campo' => 'formCard[descricao]'		, 'etiqueta' => 'Conteúdo'			, 'linha' => 3, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => $dados['descricao']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'linhasTA' => 15, 'readonly' => true));
                
                $form2 = new form01();
                $form2->addCampo(array('id' => 'prioridade', 'campo' => 'formCard[prioridade]'		, 'etiqueta' => 'Prioridade'			, 'linha' => 1, 'largura' =>12, 'tipo' => 'T'	, 'tamanho' => '6', 'linhas' => '', 'valor' => $dados['prioridade']		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard',  'readonly' => true));
                $form2->addCampo(array('id' => 'data_inicio', 'campo' => 'formCard[data_inicio]'		, 'etiqueta' => 'Data de Início'			, 'linha' => 2, 'largura' =>12, 'tipo' => 'D'	, 'tamanho' => '6', 'linhas' => '', 'valor' => datas::dataS2D($dados['data_inicio']	)	, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard',  'readonly' => true));
                $form2->addCampo(array('id' => 'data_fim', 'campo' => 'formCard[data_fim]'		, 'etiqueta' => 'Previsão de Conclusão'			, 'linha' => 3, 'largura' =>12, 'tipo' => 'D'	, 'tamanho' => '6', 'linhas' => '', 'valor' => datas::dataS2D($dados['data_fim']	)	, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'readonly' => true));
                $form2->addCampo(array('id' => 'data_conclui', 'campo' => 'formCard[data_conclui]'		, 'etiqueta' => 'Data de Conclusão'			, 'linha' => 3, 'largura' =>12, 'tipo' => 'D'	, 'tamanho' => '6', 'linhas' => '', 'valor' => datas::dataS2D($dados['data_conclui'])		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoEditarCard', 'readonly' => true));

                $param = array();
                $param['tamanhos'] = array(10, 2);
                $param['conteudos'] = array($form1.'', $form2.'');
                $ret = addLinha($param);
            
            }
        }
        return $ret;
    }
}

















?>