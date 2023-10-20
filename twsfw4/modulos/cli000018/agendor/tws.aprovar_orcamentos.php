<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class aprovar_orcamentos{
    var $funcoes_publicas = array(
        'index'             => true,
        'aprovar'           => true,
        'detalhes'          => true,
        'reprovar'          => true,
        'salvarReprovacao'  => true,
        'salvarAprovacao'   => true,
        'historico'         => true,
        'ajax'              => true,
    );
    
    private $_programa;
    
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
    
    public function __construct(){
        $this->_programa = 'aprovar_orcamentos';
    }
    
    public function index(){
        $relatorio = new relatorio01(['titulo' => 'Aprovar Orçamentos']);
        $relatorio->addColuna(array('campo' => 'bt1'                , 'etiqueta' => ''			            , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt2'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt3'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt4'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //$relatorio->addColuna(array('campo' => 'num'                , 'etiqueta' => 'Num. Orçamento'        , 'width' =>  120, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'titulo'             , 'etiqueta' => 'Título'                , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'total'              , 'etiqueta' => 'Total'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'));
        $relatorio->addColuna(array('campo' => 'responsavel'        , 'etiqueta' => 'Responsável'           , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'cliente'            , 'etiqueta' => 'Cliente'               , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        
        //NUM ORÇAMENTO, TITULO, VALOR TOTAL, RESPONSAVEL
        
        
        $dados = $this->getDados();
        $relatorio->setDados($dados);
        
        return $relatorio . '';
    }
    
    public function detalhes(){
        $id_agendor = $_GET['id'] ?? '';
        
        if(empty($id_agendor)){
            addPortalMensagem('Nenhum negócio informado', 'error');
            redireciona(getLink() . 'index');
        }
        if(!validarNegocioAgendor($id_agendor, 'N')){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            redireciona(getLink() . 'index');
        }
        if(!verificarOrdemEtapaAgendor($id_agendor)){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            excluirNegocioGoflow($id_agendor);
            redireciona(getLink() . 'index');
        }
        
        $dados = $this->getItensOrcamento($id_agendor);
        $dados_agendor = getInfoNegocioAgendor($id_agendor);
        
        $relatorio = new relatorio01(['titulo' => $dados_agendor['titulo']]);
        $relatorio->setParamTabela([
            'paginacao' => false,
            'filtro' => false,
            'info' => false,
            'ordenacao' => false,
        ]);
        $relatorio->addColuna(array('campo' => 'INFO'               , 'etiqueta' => 'Informação'			         , 'width' =>  80, 'posicao' => 'E', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'VALOR'              , 'etiqueta' => 'Valor'			             , 'width' =>  80, 'posicao' => 'D', 'tipo' => 'T'));
        $info = $this->getInfoOrcamento($id_agendor);
        $relatorio->setDados($info);
        
        $relatorio->addColuna(array('campo' => 'codigo'                 , 'etiqueta' => 'Código'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'nome_protheus'          , 'etiqueta' => 'Nome'			             , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'qtde'                   , 'etiqueta' => 'Quantidade'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'valor_orcamento'        , 'etiqueta' => 'Preço Orçamento'			 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'valor_protheus'         , 'etiqueta' => 'Preço Base'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'porcentagem'            , 'etiqueta' => 'Diferença'			         , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'), 1);
        $relatorio->addColuna(array('campo' => 'ipi'                    , 'etiqueta' => 'IPI'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'total'                  , 'etiqueta' => 'Total'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        $relatorio->addColuna(array('campo' => 'total_ipi'              , 'etiqueta' => 'Total com IPI'			     , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'), 1);
        
        
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
    }
    
    private function getInfoOrcamento($id_agendor){
        $ret = [];
        $dados_agendor = getInfoNegocioAgendor($id_agendor);
        //$ret[0] = ['INFO' => 'Título', 'VALOR' => $dados_agendor['titulo']];
        $ret[0] = ['INFO' => 'Reponsável', 'VALOR' => $dados_agendor['responsavel']];
        $ret[1] = ['INFO' => 'Cliente', 'VALOR' => $dados_agendor['cliente']];
        $ret[2] = ['INFO' => 'Total', 'VALOR' => formataReais($dados_agendor['total'], 2, true)];
        
        //faltou o total
        ksort($ret);
        return $ret;
    }
    
    private function getItensOrcamento($codigoUnicoProtheus){
        $temp = new card_agendor($codigoUnicoProtheus);
        $ret = $temp->getDadosOrcamento();
        return $ret;
    }
    
    public function aprovar(){
        $ret = '';
        
        $id_agendor = $_GET['id'] ?? '';
        
        if(!validarNegocioAgendor($id_agendor, 'N')){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            redireciona(getLink() . 'index');
        }
        if(!verificarOrdemEtapaAgendor($id_agendor)){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            excluirNegocioGoflow($id_agendor);
            redireciona(getLink() . 'index');
        }
        
        $resposta_salvas = $this->getUltimasResposta();
        
        $form = new form01(['geraScriptValidacaoObrigatorios' => true]);
        $form->addCampo(array('id' => 'marca'       , 'campo' => 'formAprovar[marca]'		, 'etiqueta' => 'Marca'                       , 'pasta' => 0, 'linha' => 1, 'largura' =>3,  'tipo' => 'T'	, 'tamanho' => '50', 'linhas' => '', 'obrigatorio' => true, 'valor' => $resposta_salvas['marca']     , 'lista' => '',));
        $form->addCampo(array('id' => 'validade'    , 'campo' => 'formAprovar[validade]'	, 'etiqueta' => 'Validade do orçamento'       , 'pasta' => 0, 'linha' => 1, 'largura' =>3,  'tipo' => 'T'	, 'tamanho' => '50', 'linhas' => '', 'obrigatorio' => true, 'valor' => $resposta_salvas['validade']  , 'lista' => '',));
        $form->addCampo(array('id' => 'prazo'     , 'campo' => 'formAprovar[prazo]'		, 'etiqueta' => 'Prazo de entrega'            , 'pasta' => 0, 'linha' => 1, 'largura' =>3,  'tipo' => 'T'	, 'tamanho' => '50', 'linhas' => '', 'obrigatorio' => true, 'valor' => $resposta_salvas['prazo']    , 'lista' => '',));
        $form->addCampo(array('id' => 'pagamento'   , 'campo' => 'formAprovar[pagamento]'	, 'etiqueta' => 'Condição de Pagamento'       , 'pasta' => 0, 'linha' => 1, 'largura' =>3,  'tipo' => 'A'	, 'tamanho' => '50', 'linhas' => '', 'obrigatorio' => true, 'valor' => $resposta_salvas['pagamento']                , 'lista' => $this->getListaCondPagamento(),));
        
        $form->addCampo(array('id' => 'garantia'    , 'campo' => 'formAprovar[garantia]'	, 'etiqueta' => 'Garantia'                    , 'pasta' => 0, 'linha' => 1, 'largura' =>6,  'tipo' => 'T'	, 'tamanho' => '50', 'linhas' => '', 'obrigatorio' => true, 'valor' => $resposta_salvas['garantia']  , 'lista' => '',));
        $form->addCampo(array('id' => 'frete'       , 'campo' => 'formAprovar[frete]'		, 'etiqueta' => 'Frete'                       , 'pasta' => 0, 'linha' => 1, 'largura' =>6,  'tipo' => 'T'	, 'tamanho' => '50', 'linhas' => '', 'obrigatorio' => true, 'valor' => $resposta_salvas['frete']     , 'lista' => '',));
        
        
        $form->setEnvio(getLink() . 'salvarAprovacao&id=' . $id_agendor, 'formAprovar', 'formAprovar');
        
        $ret = addCard(['conteudo' => $form . '', 'titulo' => "Aprovar Negócio: " . getInfoNegocioAgendor($id_agendor, 'titulo')]);
        return $ret;
        /*
         *         $codigo_unico = $_GET['id'] ?? '';

        if(empty($codigo_unico)){
            addPortalMensagem('Nenhum id informado', 'error');
            redireciona(getLink() . 'index');
        }
        if(empty($id_agendor)){
            addPortalMensagem('Nenhum negócio vinculado ao orçamento', 'error');
            redireciona(getLink() . 'index');
        }
        if(!validarCodigoUnico($codigo_unico)){
            addPortalMensagem('Já existe outro orçamento mais atual', 'error');
            redireciona(getLink() . 'index');
        }
        
        $this->gravarAprovacao($codigo_unico, $id_agendor);
        $this->moverCardAgendor($id_agendor);
        liberarOrcamento($codigo_unico);
        $arquivo = $this->gerarPDF($codigo_unico);
        criarTarefaAprovado($codigo_unico, 'O', $arquivo);
        
        redireciona(getLink() . 'index');
        */
    }
    
    private function getUltimasResposta(){
        $ret = [
            'marca' => 'BHIO SUPPLY',
            'validade' => '30 DIAS',
            'prazo' => 'A COMBINAR',
            'pagamento' => '007',
            'garantia' => '1 ANO VIDEO/10 ANOS GERAL CONTRA DEF. FABRICA',
            'frete' => 'Cif para pedidos acima de R$ 5.000,00',
        ];
        
        $usuario = getUsuario();
        $sql = "select valor from sys044 where programa = '$this->_programa' and usuario = '$usuario'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) == 1){
            $ret = unserialize($rows[0]['valor']);
        }
        return $ret;
    }
    
    private function geravarRespostas($marca, $validade, $prazo, $pagamento, $garantia, $frete){
        $usuario = getUsuario();
        $sql = "delete from sys044 where programa = '$this->_programa' and usuario = '$usuario'";
        query($sql);
        
        $valor = [
            'marca' => $marca,
            'validade' => $validade,
            'prazo' => $prazo,
            'pagamento' => $pagamento,
            'garantia' => $garantia,
            'frete' => $frete,
        ];
        $campos = [
            'programa' => $this->_programa,
            'usuario' => getUsuario(),
            'valor' => serialize($valor),
        ];
        $sql = montaSQL($campos, 'sys044');
        query($sql);
    }
    
    public function salvarAprovacao(){
        $id_agendor = $_GET['id'] ?? '';
        if(!validarNegocioAgendor($id_agendor, 'N')){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            redireciona(getLink() . 'index');
        }
        if(!verificarOrdemEtapaAgendor($id_agendor)){
            addPortalMensagem('Esse negócio já foi movido para outra etapa', 'error');
            excluirNegocioGoflow($id_agendor);
            redireciona(getLink() . 'index');
        }
        
        $dados = $_POST['formAprovar'] ?? [];
        
        $garantia = $dados['garantia'] ?? 'null';
        $marca = $dados['marca'] ?? 'null';
        $validade = $dados['validade'] ?? 'null';
        $prazo = $dados['prazo'] ?? 'null';
        $frete = $dados['frete'] ?? 'null';
        $pagamento = $dados['pagamento'] ?? 'null';
        
        $this->geravarRespostas($marca, $validade, $prazo, $pagamento, $garantia, $frete);
        
        if(empty($prazo)){
            addPortalMensagem("Não foi informado nenhum prazo de entrega", 'error');
            redireciona();
        }
        if(empty($pagamento)){
            addPortalMensagem("Não foi informado nenhuma condição de pagamento", 'error');
            redireciona();
        }
        
        
        $card = new card_agendor($id_agendor);
        //se não existe orçamento: cria o orçamento
        $codigo_unico = $card->gerarOrcamento($pagamento);
        if(!empty($codigo_unico)){
            $num_orcamento = getNumOrcamentoProtheus($codigo_unico);
            
            $campos = [
                'id' => $num_orcamento,
                'codigo_unico' => $codigo_unico,
                'id_agendor' => $id_agendor,
                'prazo' => $prazo,
                'cond_pagamento' => $pagamento,
                'insert_user' => getUsuario(),
                'insert_dt' => '"current_timestamp()"',
                'garantia' => $garantia,
                'marca' => $marca,
                'validade' => $validade,
                'frete' => $frete,
            ];
            $sql = montaSQL($campos, 'bs_orcamentos');
            query($sql);
            
            $this->moverCardAgendor($id_agendor, 'AC');
            $arquivo = $this->gerarPDF($codigo_unico);
            criarTarefaAprovado($id_agendor, $num_orcamento, $codigo_unico, 'O', $arquivo);
            addPortalMensagem("Orçamento $num_orcamento criado com sucesso");
        }
        else{
            addPortalMensagem("Não foi possível criar o orçamento", 'error');
        }
        redireciona(getLink() . 'index');
    }
    
    private function getListaCondPagamento(){
        $ret = [];
        $sql = "select E4_CODIGO, E4_DESCRI from SE4040 where D_E_L_E_T_ != '*'";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = [
                    $row['E4_CODIGO'], $row['E4_DESCRI']
                ];
            }
        }
        return $ret;
    }
    
    private function gravarAprovacao($codigo_unico, $id_agendor){
        $sql = "update bs_orcamentos set dt_aprovado_orcamento = current_timestamp(), aprovado_por_orcamento = '" . getUsuario() . "' where id = '$codigo_unico' and id_agendor = '$id_agendor'";
        query($sql);
    }
    
    private function moverCardAgendor($id_agendor, $status){
        $etapa = intval(getInfoNegocioAgendor($id_agendor, 'etapa')) + 1;
        moverCardAgendor($id_agendor, $etapa, $status);
    }
    
    private function getDados(){
        //pegar os dados da tabela bs_orcamentos
        //pegar somente o ultimo orcamento de cada negócio
        //mostrar nome do negócio
        //ter um botão para ver os detalhes
        //mostrar o funcionário que criou?
        $ret = [];
        //$sql = "select * from bs_agendor_negocios where id in (select id_agendor from bs_orcamentos join (select id_agendor, max(dt) as dt from bs_orcamentos group by id_agendor) as ocamentos_novos using (id_agendor, dt) where dt_aprovado_orcamento is null and aprovado_por_orcamento is null and proposta is null and dt_proposta is null) and tipo = 'N'";
        //$sql = "select bs_orcamentos.id as id, bs_orcamentos.id_agendor as id_agendor, bs_agendor_negocios.titulo as titulo, bs_agendor_negocios.responsavel as responsavel, bs_agendor_negocios.total as total from bs_orcamentos join (select id_agendor, max(dt) as dt from bs_orcamentos where id_agendor in (select id from bs_agendor_negocios where tipo = 'N') group by id_agendor) as ocamentos_novos using (id_agendor, dt) join bs_agendor_negocios on (bs_orcamentos.id_agendor = bs_agendor_negocios.id) where dt_aprovado_orcamento is null and aprovado_por_orcamento is null and proposta is null and dt_proposta is null";
        //NUM ORÇAMENTO, TITULO, VALOR TOTAL, RESPONSAVEL
        $sql = "select * from bs_agendor_negocios where tipo = 'N'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['id', 'titulo', 'responsavel', 'total', 'cliente'];
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
    
    private function getCondicaoPagamento($num_orcamento){
        $ret = '';
        $sql = "select E4_DESCRI from SE4040 where E4_CODIGO IN (select CJ_CONDPAG from SCJ040 WHERE CJ_NUM = '$num_orcamento' and D_E_L_E_T_ != '*') and D_E_L_E_T_ != '*'";
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
        if(!validarNegocioAgendor($id, 'N')){
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
        if(!validarNegocioAgendor($id_agendor, 'N')){
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
        
        criarTarefaReprovado($id_agendor, $motivo_agendor, 'O');
        
        /*
        $usuario = getUsuario();
        $sql = "update bs_orcamentos set dt_reprovado = current_timestamp(), reprovado_por = '$usuario', motivo_reprovado = '$motivo_banco' where id = '$id'";
        query($sql);
        */
        $this->criarOrcamentoVazio($id_agendor, $motivo_banco);
        $this->moverCardAgendor($id_agendor, 'NR');
        
        redireciona();
    }
    
    private function criarOrcamentoVazio($id_agendor, $motivo){
        $codigo_unico = card_agendor::gerarCodigoUnico();
        $user = getUsuario();
        $sql = "insert into bs_orcamentos (codigo_unico, id_agendor, insert_user, insert_dt, reprovacao_user, reprovacao_dt, reprovacao_motivo) values ('$codigo_unico', '$id_agendor', '$user', current_timestamp(), '$user', current_timestamp(), '$motivo')";
        query($sql);
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
    
    private function gerarPDF($codigo_unico){
        $num_orcamento = getNumOrcamento($codigo_unico);
        //$num_orcamento = '041455';
        $variaveis = $this->montarVariaveisPdf($num_orcamento, $codigo_unico);
        $html = $this->gerarHtmlPdf();
        foreach ($variaveis as $chave => $valor){
            $html = str_replace("@@$chave", $valor, $html);
        }
        
        $paramPDF = [];
        $paramPDF['orientacao'] = 'L';
        $arquivo = "C:\\xampp\\pdf_orcamento\\$num_orcamento.pdf";
        
        $PDF = new pdf_exporta02($paramPDF);
        
        $PDF->setModoConversao( 'HTML2PDF');
        $PDF->setHTML($html);
        $PDF->grava($arquivo);
        unset($PDF);
        
        return $arquivo;
    }
    
    private function montarVariaveisPdf($num_orcamento, $codigo_unico){
        $ret = [];
        ////////////////////////////////////////////////////////////
        //cab
        $ret['email_bhiosupply'] = 'contato@bhiosupply.com.br';
        $ret['orcamento'] = $num_orcamento;
        $ret['data_emissao'] = datas::data_hoje();
        
        ///////////////////////////////////////////////////////////
        //dados cliente
        $dados_cliente = getClienteOrcamentoProtheus('', $num_orcamento);
        $ret['codigo_cliente'] = $dados_cliente['A1_COD'] . " - Loja " . $dados_cliente['A1_LOJA'];
        $ret['razao_social'] = $dados_cliente['A1_NOME'];
        $ret['cnpj_cic'] = $dados_cliente['A1_CGC'];
        $ret['insc_estadual'] = $dados_cliente['A1_INSCR'];
        $ret['endereco'] = $dados_cliente['A1_END'];
        $ret['bairro'] = $dados_cliente['A1_BAIRRO'];
        $ret['cep'] = $dados_cliente['A1_CEP'];
        $ret['cidade'] = $dados_cliente['A1_MUN'];
        $ret['estado'] = $dados_cliente['A1_EST'];
        $ret['fone'] = $dados_cliente['A1_TEL'];
        $ret['fax'] = $dados_cliente['A1_FAX'];
        $ret['contato_setor'] = $dados_cliente['A1_EMAIL'];
        //////////////////////////////////////////////////////////
        //produtos
        $ret['lista_produtos'] = '';
        $dados_produtos = $this->getDadosItens($num_orcamento);
        foreach ($dados_produtos as $dados_item_atual){
            $html_linha = $this->getHtmlLinhaProdutos();
            foreach ($dados_item_atual as $chave => $valor){
                $html_linha = str_replace('@@' . $chave, $valor, $html_linha);
            }
            $ret['lista_produtos'] .= $html_linha;
        }
        /////////////////////////////////////////////////////////
        //totais
        $dados_totais = $this->getDadosTotais($num_orcamento);
        $ret['valor_mercadoria'] = $dados_totais['valor_mercadoria'];
        $ret['valor_ipi'] = $dados_totais['valor_ipi'];
        $ret['valor_orcamento'] = $dados_totais['valor_orcamento'];
        ////////////////////////////////////////////////////////////
        $ret['observacao'] = $this->montarObservacao($num_orcamento);
        
        
        $dados_orcamento_goflow = getInfoOrcamento($codigo_unico);
        
        $ret['condicao'] = $this->getCondicaoPagamento($num_orcamento);
        $ret['garantia'] = $dados_orcamento_goflow['garantia'];
        $ret['marca'] = $dados_orcamento_goflow['marca'];
        $ret['validade_orcamento'] = $dados_orcamento_goflow['validade'];
        $ret['prazo_entrega'] = $dados_orcamento_goflow['prazo'];
        $ret['frete'] = $dados_orcamento_goflow['frete'];
        
        return $ret;
    }
    
    private function getHtmlLinhaProdutos(){
        return '

<tr>
                    <th>@@id</th>
                    <th>@@codigo</th>
                    <th>@@etiqueta</th>
                    <th style="text-align: right;">@@qtd</th>
                    <th style="text-align: right;">@@valor_bruto</th>
                    <th style="text-align: right;">@@valor_total</th>
                    <th style="text-align: right;">@@ipi</th>
                </tr>

';
    }
    
    private function gerarHtmlPdf(){
        global $config;
        //<img src="' . $config['baseS3'] . 'img\\logo_pdf.png' . '" align="left" width="370px" height="74px">
        $ret = '
<html>
    <head>
        <style>
            table,tr, th, td {
                border: 1px solid black;
                border-collapse: collapse;
                margin-left: 50px;
                margin-top: 0px;
                empty-cells: show;
                font-size: 11px;
            }
            img{
                width:100%;
                height:74px;
            }
            h3{
                margin-bottom: 0px;
                margin-top: 2px;
            }

            .tabela_titulo{
                text-align: left;
                width: 90%;
            }
            .tabela_inicio{
                text-align: left;
                width: 90%;
            }
            .table_height{
               height: 74px;
            }
            .tabela_meio{
                width: 90%;
            }
            .tabela_fim{
                width: 90%;

            }

            

        </style>

    </head>
    <body>
        <table  class="tabela_titulo">
            <colgroup>
                <col span="1" style="width: 40%;">
                <col span="1" style="width: 60%;">
            </colgroup>
            <tr>
                <th>
                    <img src="' . $config['baseS3'] . 'img\\logo_pdf.png' . '" align="left" style="width:100%;height:74px;">
                </th>
                <th>
                    BHIO SUPPLY IND. E COM. DE EQUIP. MEDICOS SA <BR>
                    AV. LUIZ PASTEUR. 4959 <br>
                    CEP: 93290010 ESTEIO        RS<br>
                    FONE: 55-51-34594000 - FAX : 55-51-34594000 <br>
                    C.N.P.J: 73.297.509/0001-11 <br>
                    http://www.bhiosupply.com.br - email: @@email_bhiosupply
                </th>
            </tr>
        </table>
        <table style="width: 90%;">
            <colgroup>
                <col span="1" style="width: 33%;">
                <col span="1" style="width: 33%;">
                <col span="1" style="width: 34%;">
            </colgroup>
            <tr>
                <th>
                    Orçamento N°: @@orcamento
                </th>
                <th>
                    Data de Emissão: @@data_emissao
               </th>
               <th>
                    Código Cliente: @@codigo_cliente
               </th>
            </tr>
        </table>

        <h3 align="center">Dados do Cliente</h3>

        <table class="tabela_meio" >
            <colgroup>
                <col span="1" style="width: 20%;">
                <col span="1" style="width: 60%;">
                <col span="1" style="width: 20%;">
            </colgroup>
            <tr class="tabela_meio">
                <th>
                     Razão Social :
                    </th>
                    <th colspan="2">
                       @@razao_social
                    </th>
                </tr>
                <tr>
                    <th>
                        C.N.P.J/C.I.C :
                    </th>
                    <th>
                       @@cnpj_cic
                    </th>
                    <th>
                        Insc.Estadual/RG : @@insc_estadual
                    </th>
                </tr>
                <tr>
                    <th>
                     Endereço :
                    </th>
                    <th colspan="2">
                       @@endereco
                    </th>
                </tr>
                <tr>
                    <th>
                        Bairro
                    </th>
                    <th>
                       @@bairro
                       </th>
                       <th>
                        CEP : @@cep
                        </th>
                </tr>
                <tr>
                    <th>
                     Cidade :
                    </th>
                    <th>
                       @@cidade
                    </th>
                    <th>
                        Estado : @@estado
                    </th>
                </tr>
                <tr>
                    <th>
                     Fone :
                    </th>
                    <th colspan="2">
                       @@fone
                    </th>
                </tr>
                <tr>
                    <th>
                        Contato/Setor :
                    </th>
                    <th colspan="2">
                        @@contato_setor
                    </th>
                </tr>
                <tr>
                    <th>
                        Observação :
                    </th>
                    <th colspan="2">
                        @@observacao
                    </th>
                </tr>
                <tr> 
                    <th>
                        Operação:
                    </th>
                    <th>
                       Orçamento
                    </th>
                    <th>
                        Sua Cotação:
                     </th>
                </tr>
        </table>

        <h3 align="center">Descrição dos produtos</h3>


        <table style="width: 90%;">
            <colgroup>
                <col span="1" style="width: 5%;">
                <col span="1" style="width: 10%;">
                <col span="1" style="width: 55%;">
                <col span="1" style="width: 5%;">
                <col span="1" style="width: 10%;">
                <col span="1" style="width: 10%;">
                <col span="1" style="width: 5%;">
            </colgroup>
                <tr>
                    <th>id</th>
                    <th>Código</th>
                    <th>Produtos/acessórios</th>
                    <th>Qtde</th>
                    <th>Valor Bruto</th>
                    <th>Valor Total</th>
                    <th >%IPI</th>
                </tr>
                @@lista_produtos
                <tr text-align="left">
                    <th colspan="3">
                        Garantia: @@garantia <br>
                        Marca: @@marca <br>
                        validade do orcamento: @@validade_orcamento <br>
                        Prazo de entrega: @@prazo_entrega <br>
                        Frete: @@frete
                    </th>
                    <th colspan="4">
                        Valor da mercadoria: @@valor_mercadoria <br>
                        Valor do IPI: @@valor_ipi <br>
                        valor do orcamento: @@valor_orcamento <br>
                        Condição: @@condicao
                    </th>
                </tr>
        </table>
        <table style="width: 90%;">
            <colgroup>
                <col span="1" style="width: 50%;">
                <col span="1" style="width: 50%;">
            </colgroup>
            <tr>
                <th style="text-align: center;">Confirmação</th>
                <th style="text-align: center;">Atensiosamente</th>
            </tr>
            <tr class="table_height">
                <td></td>
                <td><img src="' . $config['baseS3'] . 'img\\branco.png' . '"> </td>
            </tr>
        </table>
    </body>
</html>
';
        return $ret;
    }
    
    private function getDadosItens($num_orcamento){
        $ret = [];
        $filial = getFilial();
        $sql = "SELECT CK_ITEM AS ID, CK_PRODUTO AS CODIGO, CK_DESCRI AS ETIQUETA, CK_QTDVEN AS QTD, CK_PRCVEN AS VALOR_BRUTO, CK_QTDVEN * CK_PRCVEN * (1 + (B1_IPI/100)) AS VALOR_TOTAL, B1_IPI AS IPI from SCK040 LEFT JOIN SB1040 ON (CK_PRODUTO = B1_COD) WHERE CK_NUM = '$num_orcamento' AND SCK040.D_E_L_E_T_ != '*' AND SB1040.D_E_L_E_T_ != '*' AND CK_FILIAL = '$filial' ORDER BY CK_ITEM";
        //$sql = "SELECT CK_ITEM AS ID, CK_PRODUTO AS CODIGO, CK_DESCRI AS ETIQUETA, CK_QTDVEN AS QTD, CK_PRCVEN AS VALOR_BRUTO, CK_QTDVEN * CK_PRCVEN * (1 + (B1_IPI/100)) AS VALOR_TOTAL, B1_IPI AS IPI from SCK040 LEFT JOIN SB1040 ON (CK_PRODUTO = B1_COD) WHERE CK_NUM = '$num_orcamento' AND SCK040.D_E_L_E_T_ != '*' AND SB1040.D_E_L_E_T_ != '*' ORDER BY CK_ITEM";
        
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['ID', 'CODIGO', 'QTD', 'ETIQUETA', 'VALOR_BRUTO', 'VALOR_TOTAL', 'IPI'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[strtolower($c)] = $row[$c];
                }
                $temp['qtd'] = formataReais($temp['qtd']);
                $temp['valor_bruto'] = formataReais($temp['valor_bruto']);
                $temp['valor_total'] = formataReais($temp['valor_total']);
                $temp['ipi'] = formataReais($temp['ipi']);
                if(in_array($temp['ipi'], [0, '0'])){
                    $temp['ipi'] = '';
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getDadosTotais($num_orcamento){
        $ret = [];
        $filial = getFilial();
        $sql = "SELECT SUM(CK_QTDVEN * CK_PRCVEN) AS VALOR_MERCADORIA, SUM(CK_QTDVEN * CK_PRCVEN * (B1_IPI/100)) AS VALOR_IPI from SCK040 LEFT JOIN SB1040 ON (CK_PRODUTO = B1_COD) WHERE CK_NUM = '$num_orcamento' AND SCK040.D_E_L_E_T_ != '*' AND SB1040.D_E_L_E_T_ != '*' AND CK_FILIAL = '$filial'";
        //$sql = "SELECT SUM(CK_QTDVEN * CK_PRCVEN) AS VALOR_MERCADORIA, SUM(CK_QTDVEN * CK_PRCVEN * (B1_IPI/100)) AS VALOR_IPI from SCK040 LEFT JOIN SB1040 ON (CK_PRODUTO = B1_COD) WHERE CK_NUM = '$num_orcamento' AND SCK040.D_E_L_E_T_ != '*' AND SB1040.D_E_L_E_T_ != '*'";
        
        $rows = query2($sql);
        if(is_array($rows) && count($rows) == 1){
            $ret['valor_mercadoria'] = formataReais($rows[0]['VALOR_MERCADORIA']);
            $ret['valor_ipi'] = formataReais($rows[0]['VALOR_IPI']);
            $ret['valor_orcamento'] = formataReais($rows[0]['VALOR_MERCADORIA'] + $rows[0]['VALOR_IPI']);
        }
        else{
            $ret['valor_mercadoria'] = 0;
            $ret['valor_ipi'] = 0;
            $ret['valor_orcamento'] = 0;
        }
        return $ret;
    }
    
    private function montarObservacao($num_orcamento){
        $ret = '';
        $dados_orcamento = getOrcamentoProtheus('', $num_orcamento);
        $obs = [];
        if(!empty(trim($dados_orcamento['CJ_OBS1'])) && strpos($dados_orcamento['CJ_OBS1'], 'APP0001') === false){
            $obs[] = utf8_encode(trim($dados_orcamento['CJ_OBS1']));
        }
        if(!empty(trim($dados_orcamento['CJ_OBS2'])) && strpos($dados_orcamento['CJ_OBS2'], 'APP0001') === false){
            $obs[] = utf8_encode(trim($dados_orcamento['CJ_OBS2']));
        }
        $ret = implode('<br>', $obs);
        return $ret;
    }
}