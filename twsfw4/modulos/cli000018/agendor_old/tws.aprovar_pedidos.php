<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class aprovar_pedidos{
    var $funcoes_publicas = array(
        'index'     => true,
        'aprovar'   => true,
        'detalhes'  => true,
        'reprovar'          => true,
        'salvarReprovacao'  => true
    );
    
    public function index(){
        //desenhar tabela com todos os negócios ainda não aprovados
        //filtro?
        $relatorio = new relatorio01(['titulo' => 'Aprovar Pedidos']);
        $relatorio->addColuna(array('campo' => 'bt1'                , 'etiqueta' => ''			            , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt2'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt3'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'num'                , 'etiqueta' => 'Num. Orçamento'        , 'width' =>  120, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'titulo'             , 'etiqueta' => 'Título'                , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'total'              , 'etiqueta' => 'Total'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'));
        $relatorio->addColuna(array('campo' => 'responsavel'        , 'etiqueta' => 'Responsável'           , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        
        $dados = $this->getDados();
        $relatorio->setDados($dados);
        
        return $relatorio . '';
    }
    
    public function detalhes(){
        $codigoUnicoProtheus = $_GET['id'] ?? '';
        $id_agendor = recuperarIdAgendor($codigoUnicoProtheus);
        if(empty($codigoUnicoProtheus) || empty($id_agendor)){
            addPortalMensagem('Nenhum orçamento informado', 'error');
            redireciona(getLink() . 'index');
        }
        if(!validarCodigoUnico($codigoUnicoProtheus)){
            addPortalMensagem('Já existe outro orçamento mais atual', 'error');
            redireciona(getLink() . 'index');
        }
        
        $dados = $this->getItensOrcamento($codigoUnicoProtheus);
        $dados_agendor = getInfoNegocioAgendor($id_agendor);
        
        $relatorio = new relatorio01(['titulo' => getNumOrcamento($codigoUnicoProtheus) . " - " . $dados_agendor['titulo']]);
        $relatorio->setParamTabela([
            'paginacao' => false,
            'filtro' => false,
            'info' => false,
            'ordenacao' => false,
        ]);
        $relatorio->addColuna(array('campo' => 'INFO'               , 'etiqueta' => 'Informação'			         , 'width' =>  80, 'posicao' => 'E', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'VALOR'              , 'etiqueta' => 'Valor'			             , 'width' =>  80, 'posicao' => 'D', 'tipo' => 'T'));
        $info = $this->getInfoOrcamento($codigoUnicoProtheus);
        $relatorio->setDados($info);
        
        $relatorio->addColuna(array('campo' => 'COD'                , 'etiqueta' => 'Código'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'NOME'               , 'etiqueta' => 'Nome'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'QTD'                , 'etiqueta' => 'Quantidade'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'VALOR_ORCAMENTO'    , 'etiqueta' => 'Preço Orçamento'			 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'VALOR_LISTA'        , 'etiqueta' => 'Preço Base'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'PORCENTAGEM'        , 'etiqueta' => 'Diferença'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->setParamTabela([
            'paginacao' => false,
            'filtro' => false,
            'info' => false,
            'ordenacao' => false,
        ], 1);
        $relatorio->setDados($dados, 1);
        $relatorio->addBotao([
            'texto' => 'Aprovar',
            'tipo'  => 'link',
            'url'   => getLink() . 'aprovar&id=' . $codigoUnicoProtheus,
            'cor'   => 'success',
        ]);
        $relatorio->addBotao([
            'texto' => 'Reprovar',
            'tipo'  => 'link',
            'url'   => getLink() . 'reprovar&id=' . $codigoUnicoProtheus,
            'cor'   => 'danger',
        ]);
        $relatorio->addBotao([
            'texto' => 'Voltar',
            'tipo'  => 'link',
            'url'   => getLink() . 'index',
            'cor'   => 'warning',
        ]);
        
        return $relatorio . '';
        
        //pegar o código unico do orçamento no mysql
        //pegar o id do orçamento no protheus
        //pegar os produtos no protheus
        //apresentar em uma tabela
        //desenhar o botão no titulo do card para aprovar o pedido
    }
    
    private function getItensOrcamento($codigoUnicoProtheus){
        $ret = [];
        $sql = "
        
SELECT ITENS.CK_PRODUTO AS COD
	,CK_DESCRI AS NOME
	,CK_VALOR AS VALOR_ORCAMENTO
	,COALESCE(VALOR_LISTA, 0) AS VALOR_LISTA
    ,CK_QTDVEN AS QTD
FROM SCK040 AS ITENS
LEFT JOIN (
	SELECT DA1_CODPRO AS CK_PRODUTO
		,MAX(DA1_PRCVEN) AS VALOR_LISTA
	FROM DA1040
	WHERE DA1_CODTAB = '001'
		AND D_E_L_E_T_ != '*'
	GROUP BY DA1_CODPRO
	) AS VALORES ON (ITENS.CK_PRODUTO = VALORES.CK_PRODUTO)
WHERE CK_NUM IN (
		SELECT CJ_NUM
		FROM SCJ040
		WHERE CJ_MFIDORC = '$codigoUnicoProtheus'
		)
	AND ITENS.D_E_L_E_T_ != '*'
	
	
";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['COD', 'NOME', 'VALOR_ORCAMENTO', 'VALOR_LISTA', 'QTD'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $porcentagem_base = $temp['VALOR_LISTA'] > 0 ? ($temp['VALOR_ORCAMENTO']/$temp['VALOR_LISTA']) : 0;
                $porcentagem_final = 0;
                if($porcentagem_base != 0){
                    if($porcentagem_base > 1){
                        $porcentagem_final = '(+)' . formataReais(($porcentagem_base - 1) * 100);
                    }
                    else{
                        $porcentagem_final = '(-)' . formataReais((1 - $porcentagem_base) * 100);
                    }
                }
                $temp['PORCENTAGEM'] = $porcentagem_final . '%';
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function aprovar(){
        $codigo_unico = $_GET['id'] ?? '';
        $id_agendor = recuperarIdAgendor($codigo_unico);
        
        if(!validarCodigoUnico($codigo_unico)){
            addPortalMensagem('Já existe outro orçamento mais atual', 'error');
            redireciona(getLink() . 'index');
        }
        if(empty($codigo_unico)){
            addPortalMensagem('Nenhum id informado', 'error');
            redireciona(getLink() . 'index');
        }
        if(empty($id_agendor)){
            addPortalMensagem('Nenhum negócio vinculado ao orçamento', 'error');
            redireciona(getLink() . 'index');
        }
        
        gerarPedidoProtheus($codigo_unico);
        $this->moverCardAgendor($id_agendor);
        $this->gravarAprovacao($codigo_unico, $id_agendor);
        criarTarefaAprovado($codigo_unico, 'P');
        
        
        redireciona(getLink() . 'index');
    }
    
    private function moverCardAgendor($id_agendor){
        $etapa = intval(getInfoNegocioAgendor($id_agendor, 'etapa')) + 1;
        moverCardAgendor($id_agendor, $etapa);
        $sql = "update bs_agendor_negocios set etapa = $etapa, tipo = 'F' where id = '$id_agendor'";
        query($sql);
    }
    
    private function gravarAprovacao($codigo_unico, $id_agendor){
        $sql = "update bs_orcamentos set dt_aprovado_pedido = current_timestamp(), aprovado_por_pedido = '" . getUsuario() . "' where id = '$codigo_unico' and id_agendor = '$id_agendor'";
        query($sql);
    }
    
    private function gravarProposta($codigo_unico, $id_agendor){
        $num_pedido = getNumPedido($codigo_unico);
        $sql = "update bs_orcamentos set proposta = '$num_pedido', dt_proposta = current_timestamp() where id = '$codigo_unico' and id_agendor = '$id_agendor'";
        query($sql);
    }
    
    private function getDados(){
        $ret = [];
        //$sql = "select * from bs_agendor_negocios where id in () and tipo = 'P'";
        $sql = "select bs_orcamentos.id as id, bs_orcamentos.id_agendor as id_agendor, bs_agendor_negocios.titulo as titulo, bs_agendor_negocios.responsavel as responsavel, bs_agendor_negocios.total as total from bs_orcamentos join (select id_agendor, max(dt) as dt from bs_orcamentos where id_agendor in (select id from bs_agendor_negocios where tipo = 'P') group by id_agendor) as ocamentos_novos using (id_agendor, dt) join bs_agendor_negocios on (bs_orcamentos.id_agendor = bs_agendor_negocios.id) where dt_aprovado_pedido is null and aprovado_por_pedido is null and proposta is null and dt_proposta is null";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['id', 'titulo', 'responsavel', 'id_agendor', 'total'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $temp['num'] = getNumOrcamento($temp['id']);
                $temp['bt1'] = formbase01::formBotao([
                    'texto' => 'Aprovar',
                    'cor'   => 'success',
                    'tipo'  => 'link',
                    'url'   => getLink() . 'aprovar&id=' . $temp['id'],
                ]);
                $temp['bt2'] = formbase01::formBotao([
                    'texto' => 'Detalhes',
                    'tipo'  => 'link',
                    'url'   => getLink() . 'detalhes&id=' . $temp['id'],
                ]);
                $temp['bt3'] = formbase01::formBotao([
                    'texto' => 'Reprovar',
                    'cor'   => 'danger',
                    'tipo'  => 'link',
                    'url'   => getLink() . 'reprovar&id=' . $temp['id'],
                ]);
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getInfoOrcamento($codigoUnicoProtheus){
        $ret = [];
        $dados_orcamento = getInfoOrcamento($codigoUnicoProtheus);
        $ret[4] = ['INFO' => 'Criado em', 'VALOR' => datas::dataMS2D($dados_orcamento['dt'])];
        
        $dados_agendor = getInfoNegocioAgendor($dados_orcamento['id_agendor']);
        //$ret[0] = ['INFO' => 'Título', 'VALOR' => $dados_agendor['titulo']];
        $ret[0] = ['INFO' => 'Reponsável', 'VALOR' => $dados_agendor['responsavel']];
        $ret[3] = ['INFO' => 'Total', 'VALOR' => formataReais($dados_agendor['total'], 2, true)];
        
        $dados_cliente = getClienteOrcamentoProtheus($codigoUnicoProtheus);
        $ret[1] = ['INFO' => 'Cliente', 'VALOR' => $dados_cliente['A1_NREDUZ']];
        $ret[2] = ['INFO' => 'Forma de Pagamento', 'VALOR' => $this->getCondicaoPagamento($codigoUnicoProtheus)];
        
        //faltou o total
        ksort($ret);
        return $ret;
    }
    
    private function getCondicaoPagamento($codigo_unico){
        $ret = '';
        $sql = "select E4_DESCRI from SE4040 where E4_CODIGO IN (select CJ_CONDPAG from SCJ040 WHERE CJ_MFIDORC = '$codigo_unico')";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['E4_DESCRI'];
        }
        return $ret;
    }
    
    public function reprovar(){
        //formulario com campo de texto
        $ret = '';
        $id = $_GET['id'] ?? '';
        if(empty($id)){
            addPortalMensagem('Nenhum orçamento informado', 'error');
            redireciona();
        }
        $form = new form01();
        $form->addCampo(array('id' => 'motivo'      , 'campo' => 'formReprovar[motivo]'		, 'etiqueta' => 'Motivo da reprovação'	        , 'pasta' => 0, 'linha' => 1, 'largura' =>12,  'tipo' => 'TA'	, 'tamanho' => '600', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => true, 'maxtamanho' => 1000, 'classeadd' => '', 'linhasTA' => 15, 'lista' => '',));
        $form->setEnvio(getLink() . 'salvarReprovacao&id=' . $id, 'formReprovar', 'formReprovar');
        $ret = addCard(['conteudo' => $form . '', 'titulo' => 'Formulário Reprovação']);
        return $ret;
    }
    
    public function salvarReprovacao(){
        $id = $_GET['id'] ?? '';
        if(empty($id)){
            addPortalMensagem('Nenhum orçamento informado', 'error');
            redireciona();
        }
        if(!validarCodigoUnico($id)){
            addPortalMensagem('Já existe outro orçamento mais atual', 'error');
            redireciona(getLink() . 'index');
        }
        $motivo = $_POST['formReprovar']['motivo'];
        if(empty(trim($motivo))){
            addPortalMensagem('Nenhum motivo informado informado', 'error');
            redireciona();
        }
        $id_agendor = recuperarIdAgendor($id);
        if(empty($id_agendor) || $id_agendor === false){
            addPortalMensagem('Nenhum negócio vinculado ao orçamento', 'error');
            redireciona();
        }
        
        criarTarefaReprovado($id, $motivo, 'P');
        cancelarOrcamento($id);
        
        $motivo = str_replace("'", "''", $motivo);
        $usuario = getUsuario();
        $sql = "update bs_orcamentos set dt_reprovado = current_timestamp(), reprovado_por = '$usuario', motivo_reprovado = '$motivo' where id = '$id'";
        query($sql);
        $etapa = intval(getInfoNegocioAgendor($id_agendor, 'etapa')) + 1;
        moverCardAgendor($id_agendor, $etapa);
        $sql = "update bs_agendor_negocios set etapa = '$etapa', tipo = 'PR' where id = '$id_agendor'";
        query($sql);
        redireciona();
    }
}