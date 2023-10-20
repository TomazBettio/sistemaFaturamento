<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class aprovar_orcamentos{
    var $funcoes_publicas = array(
        'index'             => true,
        'aprovar'           => true,
        'detalhes'          => true,
        'reprovar'          => true,
        'salvarReprovacao'  => true
    );
    
    public function index(){
        $this->gerarPDF('ed5fe687-9d0f-aa7f-934f-35d57ea2784c');
        die();
        
        $relatorio = new relatorio01(['titulo' => 'Aprovar Orçamentos']);
        $relatorio->addColuna(array('campo' => 'bt1'                , 'etiqueta' => ''			            , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt2'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'bt3'                , 'etiqueta' => ''                      , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'num'                , 'etiqueta' => 'Num. Orçamento'        , 'width' =>  120, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'titulo'             , 'etiqueta' => 'Título'                , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'total'              , 'etiqueta' => 'Total'                 , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'V'));
        $relatorio->addColuna(array('campo' => 'responsavel'        , 'etiqueta' => 'Responsável'           , 'width' =>  80, 'posicao' => 'C', 'tipo' => 'T'));
        //NUM ORÇAMENTO, TITULO, VALOR TOTAL, RESPONSAVEL
        
        
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
    }
    
    private function gravarAprovacao($codigo_unico, $id_agendor){
        $sql = "update bs_orcamentos set dt_aprovado_orcamento = current_timestamp(), aprovado_por_orcamento = '" . getUsuario() . "' where id = '$codigo_unico' and id_agendor = '$id_agendor'";
        query($sql);
    }
    
    private function moverCardAgendor($id_agendor){
        $etapa = intval(getInfoNegocioAgendor($id_agendor, 'etapa')) + 1;
        moverCardAgendor($id_agendor, $etapa);
        $sql = "update bs_agendor_negocios set etapa = $etapa, tipo = 'AC' where id = '$id_agendor'";
        query($sql);
    }
    
    private function getDados(){
        //pegar os dados da tabela bs_orcamentos
        //pegar somente o ultimo orcamento de cada negócio
        //mostrar nome do negócio
        //ter um botão para ver os detalhes
        //mostrar o funcionário que criou?
        $ret = [];
        //$sql = "select * from bs_agendor_negocios where id in (select id_agendor from bs_orcamentos join (select id_agendor, max(dt) as dt from bs_orcamentos group by id_agendor) as ocamentos_novos using (id_agendor, dt) where dt_aprovado_orcamento is null and aprovado_por_orcamento is null and proposta is null and dt_proposta is null) and tipo = 'N'";
        $sql = "select bs_orcamentos.id as id, bs_orcamentos.id_agendor as id_agendor, bs_agendor_negocios.titulo as titulo, bs_agendor_negocios.responsavel as responsavel, bs_agendor_negocios.total as total from bs_orcamentos join (select id_agendor, max(dt) as dt from bs_orcamentos where id_agendor in (select id from bs_agendor_negocios where tipo = 'N') group by id_agendor) as ocamentos_novos using (id_agendor, dt) join bs_agendor_negocios on (bs_orcamentos.id_agendor = bs_agendor_negocios.id) where dt_aprovado_orcamento is null and aprovado_por_orcamento is null and proposta is null and dt_proposta is null";
        //NUM ORÇAMENTO, TITULO, VALOR TOTAL, RESPONSAVEL
        
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
    
    private function getCondicaoPagamento($codigo_unico = '', $num_orcamento = ''){
        $ret = '';
        if(empty($num_orcamento)){
            $num_orcamento = getNumOrcamento($codigo_unico);
        }
        $sql = "select E4_DESCRI from SE4040 where E4_CODIGO IN (select CJ_CONDPAG from SCJ040 WHERE CJ_NUM = '$num_orcamento') and D_E_L_E_T_ != '*'";
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
        
        criarTarefaReprovado($id, $motivo, 'O');
        cancelarOrcamento($id);
        
        $motivo = str_replace("'", "''", $motivo);
        $usuario = getUsuario();
        $sql = "update bs_orcamentos set dt_reprovado = current_timestamp(), reprovado_por = '$usuario', motivo_reprovado = '$motivo' where id = '$id'";
        query($sql);
        $etapa = intval(getInfoNegocioAgendor($id_agendor, 'etapa')) + 1;
        moverCardAgendor($id_agendor, $etapa);
        $sql = "update bs_agendor_negocios set etapa = '$etapa', tipo = 'NR' where id = '$id_agendor'";
        query($sql);
        redireciona();
    }
    
    private function gerarPDF($codigo_unico){
        $num_orcamento = getNumOrcamento($codigo_unico);
        //$num_orcamento = '041455';
        $variaveis = $this->montarVariaveisPdf($num_orcamento);
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
    
    private function montarVariaveisPdf($num_orcamento){
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
        $ret['condicao'] = $this->getCondicaoPagamento('', $num_orcamento);
        $ret['garantia'] = '1 ANO VIDEO/10 ANOS GERAL CONTRA DEF. FABRICA';
        $ret['marca'] = 'BHIO SUPPLY';
        $ret['validade_orcamento'] = '30 DIAS';
        $ret['prazo_entrega'] = 'x';
        $ret['frete'] = 'Cif para pedidos acima de R$ 5.000,00';
        
        $ret['width'] = 'width="3000"';
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
                        validade do orcamento @@validade_orcamento <br>
                        Prazo de entrega @@prazo_entrega <br>
                        Frete @@frete
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
        $sql = "SELECT CK_ITEM AS ID, CK_PRODUTO AS CODIGO, CK_DESCRI AS ETIQUETA, CK_QTDVEN AS QTD, CK_PRCVEN AS VALOR_BRUTO, CK_QTDVEN * CK_PRCVEN * (1 + (B1_IPI/100)) AS VALOR_TOTAL, B1_IPI AS IPI from SCK040 LEFT JOIN SB1040 ON (CK_PRODUTO = B1_COD) WHERE CK_NUM = '$num_orcamento' AND SCK040.D_E_L_E_T_ != '*' AND SB1040.D_E_L_E_T_ != '*' ORDER BY CK_ITEM";
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
        $sql = "SELECT SUM(CK_QTDVEN * CK_PRCVEN) AS VALOR_MERCADORIA, SUM(CK_QTDVEN * CK_PRCVEN * (B1_IPI/100)) AS VALOR_IPI from SCK040 LEFT JOIN SB1040 ON (CK_PRODUTO = B1_COD) WHERE CK_NUM = '$num_orcamento' AND SCK040.D_E_L_E_T_ != '*' AND SB1040.D_E_L_E_T_ != '*'";
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
        if(!empty(trim($dados_orcamento['CJ_OBS1']))){
            $obs[] = utf8_encode(trim($dados_orcamento['CJ_OBS1']));
        }
        if(!empty(trim($dados_orcamento['CJ_OBS2']))){
            $obs[] = utf8_encode(trim($dados_orcamento['CJ_OBS2']));
        }
        $ret = implode('<br>', $obs);
        return $ret;
    }
}