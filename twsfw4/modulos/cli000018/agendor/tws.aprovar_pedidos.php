<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class aprovar_pedidos{
    var $funcoes_publicas = array(
        'index'     => true,
        'aprovar'   => true,
        'detalhes'  => true,
        'reprovar'          => true,
        'salvarReprovacao'  => true,
        'ajax' => true,
        'historico'         => true,
    );
    
    public function index(){
        $relatorio = new relatorio01(['titulo' => 'Aprovar Pedidos']);
        $relatorio->addColuna(array('campo' => 'bt1'                , 'etiqueta' => ''			            , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt2'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt3'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt4'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'num'                , 'etiqueta' => 'Num. Orçamento'        , 'width' =>  120, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'titulo'             , 'etiqueta' => 'Título'                , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'total'              , 'etiqueta' => 'Total'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'));
        $relatorio->addColuna(array('campo' => 'responsavel'        , 'etiqueta' => 'Responsável'           , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        
        $dados = $this->getDados();
        $relatorio->setDados($dados);
        
        return $relatorio . '';
    }
    
    public function historico(){
        $id_agendor = $_GET['id'];
        $ret = '';
        $dados_tabela = [];
        $sql = "select id,
case when reprovacao_user is null and aprovacao_user is null then 'Orçamento Aprovado'
when reprovacao_user is null and aprovacao_user is not null then 'Pedido Aprovado'
when reprovacao_user = 'SCHEDULE' then 'Rejeitado p/ Cliente'
else 'Pedido Rejeitado' end as status,
insert_user, aprovacao_user, reprovacao_user, reprovacao_motivo as motivo
  from bs_orcamentos where id_agendor = '$id_agendor' and id is not null order by insert_dt desc";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dicionario_usuarios = $this->montarDicionarioUsuarios();
            $dicionario_motivos = $this->montarDicionarioMotivos();
            foreach ($rows as $row){
                $link = getLinkAjax('visualizarPdf') . '&orcamento=' . base64_encode($row['id']);
                $motivo = '';
                if(!empty($row['motivo'])){
                    //
                    if($row['motivo'] == '||SCHEDULE'){
                        $motivo = 'Movido de volta para a coluna "Solicitação de Orçamento"';
                    }
                    else{
                        $motivos_temp = [];
                        $temp = explode('||', $row['motivo']);
                        if(!empty($temp[0])){
                            $motivos_cb = explode(',', $temp[0]);
                            foreach ($motivos_cb as $id_motivo){
                                if(isset($dicionario_motivos[$id_motivo])){
                                    $motivos_temp[] = $dicionario_motivos[$id_motivo];
                                }
                            }
                        }
                        if(!empty($temp[1])){
                            $motivos_temp[] = str_replace('\n', '<br>', $temp[1]);
                        }
                        if(count($motivos_temp) == 1){
                            $motivo = $motivos_temp[0];
                        }
                        elseif(count($motivos_temp) > 1){
                            $motivo = '-' . implode('<br>-', $motivos_temp);
                        }
                    }
                }
                $temp = [
                    'num_orcamento' => $row['id'],
                    'status' => $row['status'],
                    'insert_user' => $dicionario_usuarios[$row['insert_user']] ?? '',
                    'aprovacao_user' => $dicionario_usuarios[$row['aprovacao_user']] ?? '',
                    'reprovacao_user' => $dicionario_usuarios[$row['reprovacao_user']] ?? '',
                    'motivo' => $motivo,
                    'bt' => formbase01::formBotao([
                        'texto' => 'Visualizar',
                        //'tipo'  => 'link',
                        //'url'   => getLink() . 'detalhes&id=' . $temp['id'],
                        'onclick' => "window.open('$link')",
                    ]),
                ];
                $dados_tabela[] = $temp;
            }
        }
        
        if(count($dados_tabela) > 0){
            $tabela = new relatorio01(['titulo' => 'Histórico']);
            $tabela->setParamTabela(['paginacao' => false, 'info' => false, 'ordenacao' => false, 'filtro' => false]);
            $tabela->addColuna(array('campo' => 'bt'         , 'etiqueta' => ''			        , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
            $tabela->addColuna(array('campo' => 'num_orcamento'         , 'etiqueta' => 'Num. Orçamento'			        , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
            $tabela->addColuna(array('campo' => 'status'                , 'etiqueta' => 'Status'			                , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
            $tabela->addColuna(array('campo' => 'insert_user'           , 'etiqueta' => 'Criado Por'			            , 'width' =>  200, 'posicao' => 'C', 'tipo' => 'T'));
            $tabela->addColuna(array('campo' => 'aprovacao_user'        , 'etiqueta' => 'Aprovado Por'			            , 'width' =>  200, 'posicao' => 'C', 'tipo' => 'T'));
            $tabela->addColuna(array('campo' => 'reprovacao_user'       , 'etiqueta' => 'Reprovado Por'			            , 'width' =>  200, 'posicao' => 'C', 'tipo' => 'T'));
            $tabela->addColuna(array('campo' => 'motivo'                , 'etiqueta' => 'Motivo da Reprovação'			    , 'width' =>  150, 'posicao' => 'E', 'tipo' => 'T'));
            
            $tabela->addBotao(['texto' => 'Voltar', 'cor' => 'danger', 'tipo' => 'link', 'url' => getLink() . 'index']);
            
            $tabela->setDados($dados_tabela);
            $ret .= $tabela;
            //$ret = addCard(['conteudo' => $ret, 'titulo' => 'Histórico']);
        }
        
        
        if(empty($ret)){
            addPortalMensagem('Nenhum histórico a ser exibido', 'error');
            redireciona();
        }
        return $ret;
    }
    
    private function montarDicionarioUsuarios(){
        $ret = [];
        $sql = "select user, apelido from sys001";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['user']] = $row['apelido'];
            }
            $ret['SCHEDULE'] = "Cliente";
        }
        return $ret;
    }
    
    public function ajax(){
        $op = getOperacao();
        if($op == 'visualizarPdf'){
            $orcamento = $_GET['orcamento'] ?? '';
            if(!empty($orcamento)){
                $orcamento = base64_decode($orcamento);
                $arquivo = "C:\\xampp\\pdf_orcamento\\$orcamento.pdf";
                if(file_exists($arquivo)){
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($arquivo));
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($arquivo));
                    header('Connection: close');
                    ob_clean();
                    flush();
                    readfile($arquivo);
                }
            }
        }
        return '';
    }
    
    public function detalhes(){
        $id_agendor = $_GET['id'] ?? '';
        if(empty($id_agendor)){
            addPortalMensagem('Nenhum negócio informado', 'error');
            redireciona(getLink() . 'index');
        }
        if(!validarNegocioAgendor($id_agendor, 'P')){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            redireciona(getLink() . 'index');
        }
        if(!verificarOrdemEtapaAgendor($id_agendor)){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            excluirNegocioGoflow($id_agendor);
            redireciona(getLink() . 'index');
        }
        
        $codigo_unico = recuperarUltimoCodigoUnico($id_agendor);
        $num_orcamento = getNumOrcamento($codigo_unico);
        
        $dados = $this->getItensOrcamento($id_agendor);
        $dados_agendor = getInfoNegocioAgendor($id_agendor);
        
        $relatorio = new relatorio01(['titulo' => "$num_orcamento - " . $dados_agendor['titulo']]);
        $relatorio->setParamTabela([
            'paginacao' => false,
            'filtro' => false,
            'info' => false,
            'ordenacao' => false,
        ]);
        $relatorio->addColuna(array('campo' => 'INFO'               , 'etiqueta' => 'Informação'			         , 'width' =>  80, 'posicao' => 'E', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'VALOR'              , 'etiqueta' => 'Valor'			             , 'width' =>  80, 'posicao' => 'D', 'tipo' => 'T'));
        $info = $this->getInfoOrcamento($codigo_unico);
        $relatorio->setDados($info);
        
        $relatorio->addColuna(array('campo' => 'COD'                , 'etiqueta' => 'Código'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'NOME'               , 'etiqueta' => 'Nome'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'QTD'                , 'etiqueta' => 'Quantidade'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'VALOR_ORCAMENTO'    , 'etiqueta' => 'Preço Orçamento'			 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'VALOR_LISTA'        , 'etiqueta' => 'Preço Base'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'PORCENTAGEM'        , 'etiqueta' => 'Diferença'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'IPI'              , 'etiqueta' => 'IPI'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'TOTAL'              , 'etiqueta' => 'Total'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'TOTAL_IPI'              , 'etiqueta' => 'Total com IPI'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        
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
            'url'   => getLink() . 'aprovar&id=' . $id_agendor,
            'cor'   => 'success',
        ]);
        $relatorio->addBotao([
            'texto' => 'Reprovar',
            'tipo'  => 'link',
            'url'   => getLink() . 'reprovar&id=' . $id_agendor,
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
    
    private function getItensOrcamento($id_agendor){
        $ret = [];
        $num_orcamento = getNumOrcamento(recuperarUltimoCodigoUnico($id_agendor));
        $filial = getFilial();
        $sql = "
        
SELECT ITENS.CK_PRODUTO AS COD
	,CK_DESCRI AS NOME
	,CK_PRCVEN AS VALOR_ORCAMENTO
	,COALESCE(VALOR_LISTA, 0) AS VALOR_LISTA
    ,CK_QTDVEN AS QTD
    ,CK_PRCVEN * CK_QTDVEN as TOTAL
FROM SCK040 AS ITENS
LEFT JOIN (
	SELECT DA1_CODPRO AS CK_PRODUTO
		,MAX(DA1_PRCVEN) AS VALOR_LISTA
	FROM DA1040
	WHERE DA1_CODTAB = '001'
		AND D_E_L_E_T_ != '*'
	GROUP BY DA1_CODPRO
	) AS VALORES ON (ITENS.CK_PRODUTO = VALORES.CK_PRODUTO)
WHERE CK_NUM = '$num_orcamento'
	AND ITENS.D_E_L_E_T_ != '*' AND CK_FILIAL = '$filial'
	
	
";
        
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['COD', 'NOME', 'VALOR_ORCAMENTO', 'VALOR_LISTA', 'QTD', 'TOTAL'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $porcentagem_base = $temp['VALOR_LISTA'] > 0 ? ($temp['VALOR_ORCAMENTO']/$temp['VALOR_LISTA']) : 0;
                $porcentagem_final = 0;
                if($porcentagem_base != 0 && $porcentagem_base != 1){
                    if($porcentagem_base > 1){
                        $porcentagem_final = '(+)' . formataReais(($porcentagem_base - 1) * 100);
                    }
                    else{
                        $porcentagem_final = '(-)' . formataReais((1 - $porcentagem_base) * 100);
                    }
                }
                $temp['PORCENTAGEM'] = $porcentagem_final . '%';
                $temp['IPI'] = getIpiProduto($temp['COD']);
                $temp['TOTAL_IPI'] = (1 + ($temp['IPI'] / 100)) * $temp['TOTAL'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function aprovar(){
        $id_agendor = $_GET['id'] ?? '';
        
        if(empty($id_agendor)){
            addPortalMensagem('Nenhum negócio foi informado', 'error');
            redireciona(getLink() . 'index');
        }
        if(!validarNegocioAgendor($id_agendor, 'P')){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            redireciona();
        }
        if(!verificarOrdemEtapaAgendor($id_agendor)){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            excluirNegocioGoflow($id_agendor);
            redireciona(getLink() . 'index');
        }
        
        $codigo_unico = recuperarUltimoCodigoUnico($id_agendor);
        $num_orcamento = getNumOrcamento($codigo_unico);
        if(empty($codigo_unico) || empty($num_orcamento)){
            addPortalMensagem('Nenhum orçamento vinculado ao negócio', 'error');
            redireciona(getLink() . 'index');
        }
        
        gerarPedidoProtheus($codigo_unico, $num_orcamento);
        $this->moverCardAgendor($id_agendor, 'PC');
        $this->gravarAprovacao($codigo_unico, $id_agendor);
        criarTarefaAprovado($id_agendor, $num_orcamento, $codigo_unico, 'P');
        
        
        redireciona(getLink() . 'index');
    }
    
    private function moverCardAgendor($id_agendor, $tipo){
        $etapa = intval(getInfoNegocioAgendor($id_agendor, 'etapa')) + 1;
        moverCardAgendor($id_agendor, $etapa, $tipo);
    }
    
    private function gravarAprovacao($codigo_unico, $id_agendor){
        $pedido = getNumPedidoProtheus($codigo_unico);
        $sql = "update bs_orcamentos set aprovacao_dt = current_timestamp(), aprovacao_user = '" . getUsuario() . "', pedido = '$pedido' where codigo_unico = '$codigo_unico' and id_agendor = '$id_agendor'";
        query($sql);
    }
    
    private function getDados(){
        $ret = [];
        $sql = "select * from bs_agendor_negocios where tipo = 'P'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['id', 'titulo', 'responsavel', 'total', 'cliente'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $temp['num'] = getNumOrcamento(recuperarUltimoCodigoUnico($temp['id']));
                //$temp['num'] = getNumOrcamento($temp['id']);
                $temp['bt1'] = formbase01::formBotao([
                    'texto' => 'Aprovar',
                    'cor'   => 'success',
                    'tipo'  => 'link',
                    'url'   => getLink() . 'aprovar&id=' . $temp['id'],
                ]);
                
                $link = getLinkAjax('visualizarPdf') . '&orcamento=' . base64_encode($temp['num']);
                $temp['bt2'] = formbase01::formBotao([
                    'texto' => 'Orçamento',
                    //'tipo'  => 'link',
                    //'url'   => getLink() . 'detalhes&id=' . $temp['id'],
                    'onclick' => "window.open('$link')",
                ]);
                $temp['bt3'] = formbase01::formBotao([
                    'texto' => 'Reprovar',
                    'cor'   => 'danger',
                    'tipo'  => 'link',
                    'url'   => getLink() . 'reprovar&id=' . $temp['id'],
                ]);
                $temp['bt4'] = formbase01::formBotao([
                    'texto' => 'Histórico',
                    'cor'   => 'warning',
                    'tipo'  => 'link',
                    'url'   => getLink() . 'historico&id=' . $temp['id'],
                ]);
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getInfoOrcamento($codigoUnicoProtheus){
        $ret = [];
        $dados_orcamento = getInfoOrcamento($codigoUnicoProtheus);
        $ret[4] = ['INFO' => 'Criado em', 'VALOR' => datas::dataMS2D($dados_orcamento['insert_dt'])];
        
        $dados_agendor = getInfoNegocioAgendor($dados_orcamento['id_agendor']);
        //$ret[0] = ['INFO' => 'Título', 'VALOR' => $dados_agendor['titulo']];
        $ret[0] = ['INFO' => 'Reponsável', 'VALOR' => $dados_agendor['responsavel']];
        $ret[3] = ['INFO' => 'Total', 'VALOR' => formataReais($dados_agendor['total'], 2, true)];
        
        $dados_cliente = getClienteOrcamentoProtheus($codigoUnicoProtheus);
        $ret[1] = ['INFO' => 'Cliente', 'VALOR' => $dados_cliente['A1_NREDUZ']];
        $ret[2] = ['INFO' => 'Condição de Pagamento', 'VALOR' => $this->getCondicaoPagamento($codigoUnicoProtheus)];
        
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
            addPortalMensagem('Nenhum negócio informado', 'error');
            redireciona();
        }
        if(!validarNegocioAgendor($id, 'P')){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            redireciona(getLink() . 'index');
        }
        if(!verificarOrdemEtapaAgendor($id)){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            excluirNegocioGoflow($id);
            redireciona(getLink() . 'index');
        }
        /*
         $form = new form01();
         $form->addCampo(array('id' => 'motivo'      , 'campo' => 'formReprovar[motivo]'		, 'etiqueta' => 'Motivo da reprovação'	        , 'pasta' => 0, 'linha' => 1, 'largura' =>12,  'tipo' => 'TA'	, 'tamanho' => '600', 'linhas' => '', 'valor' => ''		, 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => true, 'maxtamanho' => 1000, 'classeadd' => '', 'linhasTA' => 15, 'lista' => '',));
         $form->setEnvio(getLink() . 'salvarReprovacao&id=' . $id, 'formReprovar', 'formReprovar');
         $ret = addCard(['conteudo' => $form . '', 'titulo' => 'Formulário Reprovação']);
         */
        $relatorio = new tabela01(['paginacao' => false, 'info' => false, 'ordenacao' => false, 'filtro' => false]);
        //$relatorio->setParamTabela(['paginacao' => false, 'info' => false, 'ordenacao' => false, 'filtro' => false]);
        $relatorio->addColuna(array('campo' => 'etiqueta'   , 'etiqueta' => 'Motivo'        , 'width' =>  350, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'campo'      , 'etiqueta' => 'Atribuir'      , 'width' =>  50, 'posicao' => 'C', 'tipo' => 'T'));
        $dados = $this->getDadosTabelaMotivos();
        $relatorio->setDados($dados);
        $relatorio = addCard(['titulo' => 'Motivos Padrão', 'conteudo' => $relatorio . '']);
        
        $campo_outros = addCard(['titulo' => 'Outros Motivos','conteudo' => formbase01::formTextArea(['nome' => "formMotivo[ta]", 'linhasTA' => 10])]);
        
        //$ret .= $relatorio;
        
        $ret = addLinha(['tamanhos' => [6, 6], 'conteudos' => [$relatorio . '', $campo_outros]]);
        
        $ret = formbase01::form(['nome' => 'formMotivo', 'id' => 'formMotivo', 'sendFooter' => true, 'acao' => getLink() . 'salvarReprovacao&id=' . $id], $ret);
        
        $ret = addCard(['titulo' => 'Formulário de reprovação', 'conteudo' => $ret]);
        
        return $ret;
    }
    
    private function getDadosTabelaMotivos(){
        $ret = [];
        
        $sql = "select id, etiqueta from bs_motivos_reprovacao where ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = [];
                $temp['etiqueta'] = $row['etiqueta'];
                $temp['campo'] = formbase01::formCheck(['nome' => "formMotivo[cb][{$row['id']}]"]);
                $ret[] = $temp;
            }
        }
        
        return $ret;
    }
    
    public function salvarReprovacao(){
        $id_agendor = $_GET['id'] ?? '';
        if(empty($id_agendor)){
            addPortalMensagem('Nenhum negócio informado', 'error');
            redireciona();
        }
        if(!validarNegocioAgendor($id_agendor, 'P')){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            redireciona();
        }
        if(!verificarOrdemEtapaAgendor($id_agendor)){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            excluirNegocioGoflow($id_agendor);
            redireciona(getLink() . 'index');
        }
        $dados_motivo = $_POST['formMotivo'];
        $motivo_banco = $this->criarMotivoBanco($dados_motivo);
        if(empty(trim($motivo_banco))){
            addPortalMensagem('Nenhum motivo informado', 'error');
            redireciona();
        }
        $motivo_agendor = $this->criarMotivoAgendor($dados_motivo);
        
        criarTarefaReprovado($id_agendor, $motivo_agendor, 'P');
        $this->moverCardAgendor($id_agendor, 'PR');
        $usuario = getUsuario();
        $codigo_unico = recuperarUltimoCodigoUnico($id_agendor);
        $sql = "update bs_orcamentos set reprovacao_dt = current_timestamp(), reprovacao_user = '$usuario', reprovacao_motivo = '$motivo_banco' where codigo_unico = '$codigo_unico'";
        query($sql);
        cancelarOrcamento($codigo_unico);
        /*
         $etapa = intval(getInfoNegocioAgendor($id_agendor, 'etapa')) + 1;
         moverCardAgendor($id_agendor, $etapa);
         $sql = "update bs_agendor_negocios set etapa = '$etapa', tipo = 'NR' where id = '$id_agendor'";
         query($sql);
         */
        
        redireciona();
    }
    
    private function montarDicionarioMotivos(){
        $ret = [];
        $sql = "select id, etiqueta from bs_motivos_reprovacao";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['id']] = $row['etiqueta'];
            }
        }
        return $ret;
    }
    
    private function criarMotivoAgendor($dados_motivo){
        $ret = [];
        $motivos_cb = $dados_motivo['cb'] ?? [];
        if(is_array($motivos_cb) && count($motivos_cb) > 0){
            $dicionario = $this->montarDicionarioMotivos();
            $temp = array_keys($motivos_cb);
            foreach ($temp as $motivo){
                if(isset($dicionario[$motivo])){
                    $ret[] = $dicionario[$motivo];
                }
            }
        }
        if(isset($dados_motivo['ta']) && !empty($dados_motivo['ta'])){
            $ret[] = $dados_motivo['ta'];
        }
        global $nl;
        return implode($nl, $ret);
    }
    
    private function criarMotivoBanco($dados_motivo){
        $ret = [];
        $motivos_cb = $dados_motivo['cb'] ?? [];
        if(is_array($motivos_cb) && count($motivos_cb) > 0){
            $ret[] = implode(',', array_keys($motivos_cb));
        }
        if(isset($dados_motivo['ta']) && !empty($dados_motivo['ta'])){
            $ret[] = str_replace("'", "''", $dados_motivo['ta']);
        }
        return implode('||', $ret);
    }
}