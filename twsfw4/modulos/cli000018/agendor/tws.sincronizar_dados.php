<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


class sincronizar_dados{
    var $funcoes_publicas = array(
        'index'             => true,
    );
    
    private $_rest;
    
    public function __construct(){
        $this->_rest = criarRestAgendor();
    }
    
    public function index(){
        //$this->renovarProdutosProtheus();
        $this->atualizarProdutosAgendor();
    }
    
    public function schedule($param){
        if($param === 'produtos'){
            $this->renovarProdutosProtheus();
            $this->atualizarProdutosAgendor();
        }
        elseif($param === 'orcamentos'){
            $this->gravarNegociosOrcamento();
        }
        elseif($param === 'aprovados'){
            $this->gravarNegociosAprovadosCliente();
        }
    }
    
    private function verificarNegocioFinalizado($codigo_negocio){
        //verifica se já existe algum pedido para esse negócio
        //se existe, volta true
        $ret = false;
        $sql = "select id_agendor from bs_orcamentos where id_agendor = '$codigo_negocio' and pedido is not null";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = true;
        }
        return $ret;
    }
    
    private function gravarNegociosOrcamento(){
        $negocios = $this->recuperarNegociosOrcamento();
        $negocios_salvar = [];
        $negocios_excluir = [];
        if(is_array($negocios) && count($negocios) > 0){
            //////////////////////////////////////////////////////////
            //monta todas as informações para inserir os cards na tabela de negócios
            foreach ($negocios as $negocio_atual){
                $codigo_negocio = $negocio_atual['id'] ?? '';
                $ordem_etapa = $negocio_atual['dealStage']['sequence'];
                $funil  = $negocio_atual['dealStage']['funnel']['id'];
                if(!$this->verificarNegocioFinalizado($codigo_negocio)){
                    $responsavel = $negocio_atual['owner']['name'] ?? '';
                    $valor = $negocio_atual['value'] ?? '';
                    $titulo = $negocio_atual['title'] ?? '';
                    $cliente = $negocio_atual['organization']['name'] ?? '';
                    
                    //monta uma lista com todos os negócios que foram recuperados
                    $negocios_excluir[] = $codigo_negocio;
                    
                    if(is_array($negocio_atual['products']) && count($negocio_atual['products']) > 0){
                        $negocios_salvar[] = ["'$codigo_negocio'", "'$ordem_etapa'", "'$titulo'", "'$responsavel'", "'$valor'", "'$cliente'", "'N'", "'$funil'"];
                    }
                }
                else{
                    moverCardAgendor($codigo_negocio, $ordem_etapa + 3);
                    criarTarefaAgendor($codigo_negocio, "Já foi gerado um pedido para esse negócio, não é possível criar um novo orçamento para o mesmo");
                    //mover o card para ordem + 3
                    //criar tarefa dizendo que não é possível modificar
                }
            }
            //////////////////////////////////////////////////////
        }
        
        if(count($negocios_salvar) > 0 && count($negocios_excluir) > 0){
            //se foram recuperados negócios
            ////////////////////////////////////////////////////////
            //monta uma lista com todos os negócios que já estavam para receberem um orçamento
            $negocios_antigos = [];
            $sql = "select id from bs_agendor_negocios where tipo = 'N'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $negocios_antigos[] = $row['id'];
                }
            }
            ///////////////////////////////////////////////////////////
            //cria uma lista com os negócios novos e envia um email para os reponsáveis avisando que existem orçamentos a serem aprovados
            $negocios_enviar_email = []; //contem uma lista dos negócios novos a serem inseridos na tabela
            foreach ($negocios_excluir as $negocio){
                if(!in_array($negocio, $negocios_antigos)){
                    $this->excluirOrcamentosAntigos($negocio); //exclui os orçamentos antigos dos negócios novos
                    $negocios_enviar_email[] = $negocio;
                }
            }
            $this->enviarEmailsOrcamentos($negocios_enviar_email, $negocios, 'O');
            ////////////////////////////////////////////////////////////////////////////////////////////
            //exclui os negócios velhos e insere os novos
            $temp = [];
            foreach ($negocios_excluir as $negocio){
                $temp[] = "'$negocio'";
            }
            $sql_excluir = "delete from bs_agendor_negocios where id in (" . implode(', ', $temp) . ")";
            $temp = [];
            foreach ($negocios_salvar as $temp_salvar){
                $temp[] = "(" . implode(', ', $temp_salvar) . ")";
            }
            $sql_incluir = "insert into bs_agendor_negocios values " . implode(', ', $temp);
            
            query($sql_excluir);
            query($sql_incluir);
            ///////////////////////////////////////////////////////////////////////////////////////////////
        }
    }
    
    private function excluirOrcamentosAntigos($id_agendor){
        //exclui todos os orçamentos em aberto relacionados ao negócio informado
        $where = " where id_agendor = '$id_agendor' and reprovacao_dt is null and id is not null and aprovacao_dt is null";
        $sql = "select id from bs_orcamentos $where";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $sql = "update bs_orcamentos set reprovacao_dt = current_timestamp(), reprovacao_user = 'SCHEDULE', reprovacao_motivo = '||SCHEDULE' $where";
            query($sql);
            foreach ($rows as $row){
                cancelarOrcamento('', $row['id']);
            }
        }
    }
    
    private function enviarEmailsOrcamentos($negocios_analizar, &$negocios){
        if(is_array($negocios_analizar) && count($negocios_analizar) > 0){
            $produtos   = $this->getProdutosFromNegocios($negocios_analizar, $negocios);
            $linhas     = $this->getLinhasFromProdutos($produtos);
            $grupos     = $this->getGruposFromLinhas($linhas);
            $emails     = $this->getEmailsResponsaveisGrupos($grupos);
            
            //envia os emails
            if(count($emails) > 0){
                $emails = array_unique($emails);
                $mensagem = "Existem novos orçamentos a serem aprovados";
                $assunto = "Orçamentos Agendor";
                
                foreach ($emails as $email){
                    enviaEmailAntigo($email, $assunto, $mensagem);
                }
            }
        }
    }
    
    private function getProdutosFromNegocios($negocios_analizar, &$negocios){
        $ret = [];
        foreach ($negocios as $negocio_atual){
            if(in_array($negocio_atual['id'], $negocios_analizar) && is_array($negocio_atual['products']) && count($negocio_atual['products']) > 0){
                foreach ($negocio_atual['products'] as $produto){
                    if(!in_array($produto['code'], $ret)){
                        $ret[] = $produto['code'];
                    }
                }
            }
        }
        return $ret;
    }
    
    private function getLinhasFromProdutos($produtos){
        $ret = [];
        if(count($produtos) > 0){
            $temp = [];
            foreach ($produtos as $produto){
                $temp[] = "'$produto'";
            }
            $sql = "select distinct B1_LINHA from SB1040 where B1_COD in (" . implode(', ', $temp) . ") and D_E_L_E_T_ != '*' and B1_LINHA in (select Z3_GRUPO from SZ3040 where D_E_L_E_T_ != '*')";
            $rows = query2($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[] = trim($row['B1_LINHA']);
                }
            }
        }
        return $ret;
    }
    
    private function getGruposFromLinhas($linhas){
        $ret = [];
        if(count($linhas) > 0){
            $temp = [];
            foreach ($linhas as $linha){
                $temp[] = "'$linha'";
            }
            $sql = "select linha, grupo from bs_linha_grupo where linha in (" . implode(', ', $temp) . ")";
            $rows = query($sql);
            if(is_array($rows)){
                if(count($rows) > 0){
                    $linhas_com_grupo = [];
                    foreach ($rows as $row){
                        $linhas_com_grupo[] = $row['linha'];
                        if(!in_array($row['grupo'], $ret)){
                            $ret[] = $row['grupo'];
                        }
                    }
                    
                    $existe_linha_sem_grupo = false;
                    foreach ($linhas as $linha){
                        $existe_linha_sem_grupo = $existe_linha_sem_grupo || !in_array($linha, $linhas_com_grupo);
                    }
                    if($existe_linha_sem_grupo){
                        $ret[] = 'SEM';
                    }
                }
                else{
                    $ret[] = 'SEM';
                }
            }
        }
        return $ret;
    }
    
    private function getEmailsResponsaveisGrupos($grupos){
        $ret = [];
        if(count($grupos) > 0){
            $emails = [];
            $temp = [];
            foreach ($grupos as $grupo){
                $temp[] = "'$grupo'";
            }
            $sql = "select emails from bs_agendor_resp_linha where grupo in (" . implode(', ', $temp) . ")";
            $rows = query($sql);
            foreach ($rows as $row){
                $email_bruto = $row['emails'];
                if(!empty(trim($email_bruto))){
                    $email_bruto = explode(';', $email_bruto);
                    $emails = array_merge($emails, $email_bruto);
                }
            }
            
            foreach ($emails as $email_atual){
                $email_trim = trim($email_atual);
                if(!empty($email_trim) && strpos($email_trim, '@') !== false){
                    $ret[] = $email_trim; 
                }
            }
        }
        return $ret;
    }
    
    private function recuperarNegociosOrcamento(){
        $ret = [];
        $etapas = [2846744];
        foreach ($etapas as $etapa){
            $temp = $this->_rest->getAllNegocios(1, $etapa);
            $ret = array_merge($ret, $temp);
        }
        
        $ret = $temp;
        
        return $ret;
    }
    
    private function recuperarNegociosPedidos(){
        $ret = [];
        $etapas = [2846750];
        foreach ($etapas as $etapa){
            $temp = $this->_rest->getAllNegocios(1, $etapa);
            $ret = array_merge($ret, $temp);
        }
        return $ret;
    }
    
    private function gravarNegociosAprovadosCliente(){
        $negocios = $this->recuperarNegociosPedidos();
        $orcamentos = [];
        if(is_array($negocios) && count($negocios) > 0){
            $negocios_antigos = [];
            $sql = "select tipo, id from bs_agendor_negocios";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    //$negocios_antigos[$row['tipo']][] = $row['id'];
                    $negocios_antigos[$row['id']] = $row['tipo'];
                }
            }
            if(count($negocios_antigos) > 0){
                foreach ($negocios as $negocio_atual){
                    $codigo_negocio = $negocio_atual['id'] ?? '';
                    $ordem_etapa = $negocio_atual['dealStage']['sequence'];
                    if(isset($negocios_antigos[$codigo_negocio]) && $negocios_antigos[$codigo_negocio] != 'P'){
                        //somente negócios que ainda não estavam nessa coluna
                        if($negocios_antigos[$codigo_negocio] == 'AC'){
                            //somente negócios cujo status anterior foi "orçamento aprovado pelo goflow"
                            $orcamentos[] = "'" . getNumOrcamento(recuperarUltimoCodigoUnico($codigo_negocio)) . "'";
                            $sql = "update bs_agendor_negocios set etapa = '$ordem_etapa', tipo = 'P' where id = '$codigo_negocio'";
                            query($sql);
                        }
                        else{
                            $this->criarTarefaErroPedido($codigo_negocio, $ordem_etapa, $negocios_antigos[$codigo_negocio]);
                        }
                    }
                    elseif(!isset($negocios_antigos[$codigo_negocio])){
                        $this->criarTarefaErroPedido($codigo_negocio, $ordem_etapa, '');
                    }
                }
                $this->enviarEmailPedidos($orcamentos);
            }
        }
    }
    
    private function criarTarefaErroPedido($id_agendor, $etapa_atual, $status_atual){
        $mensagem = '';
        if($status_atual == 'NR'){
            $mensagem = 'Não é possível criar um pedido pois o último orçamento foi rejeitado, por favor altere a proposta e devolva o card para a coluna "Solicitação de Orçamento"';
        }
        elseif($status_atual == 'N'){
            $mensagem = 'Não é possível criar um pedido pois o último orçamento não passou pela aprovação, por favor altere a proposta e devolva o card para a coluna "Solicitação de Orçamento"';
        }
        elseif($status_atual == 'PC'){
            $mensagem = 'Não é possível criar um pedido pois já existe um pedido para esse negócio';
        }
        elseif($status_atual == 'PR'){
            $mensagem = 'Não é possível criar um pedido pois o último pedido já foi recusado, por favor altere a proposta e devolva o card para a coluna "Solicitação de Orçamento"';
        }
        else{
            $mensagem = 'Não é possível criar um pedido pois não existe nenhum orçamento vinculado ao negócio';
        }
        if(!empty($mensagem)){
            criarTarefaAgendor($id_agendor, $mensagem);
            moverCardAgendor($id_agendor, $etapa_atual + 1, 'ERROR');
        }
    }
    
    private function enviarEmailPedidos($orcamentos){
        $produtos   = $this->getProdutosFromOrcamentos($orcamentos);
        $linhas     = $this->getLinhasFromProdutos($produtos);
        $grupos     = $this->getGruposFromLinhas($linhas);
        $emails     = $this->getEmailsResponsaveisGrupos($grupos);
        
        if(count($emails) > 0){
            $emails = array_unique($emails);
            $mensagem = "Existem novos pedidos a serem aprovados";
            $assunto = "Orçamentos Agendor";
            
            foreach ($emails as $email){
                enviaEmailAntigo($email, $assunto, $mensagem);
                //echo "$email<br>";
                
            }
        }
    }
    
    private function getProdutosFromOrcamentos($orcamentos){
        $ret = [];
        if(count($orcamentos) > 0){
            $sql = "select distinct CK_PRODUTO FROM SCK040 WHERE D_E_L_E_T_ != '*' and CK_NUM in (" . implode(', ', $orcamentos) . ")";
            $rows = query2($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[] = $row['CK_PRODUTO'];
                }
            }
        }
        return $ret;
    }
    
    private function renovarProdutosProtheus(){
        $sql = "
            
SELECT LTRIM(RTRIM(B1_COD)) AS B1_COD
	,LTRIM(RTRIM(B1_DESC)) AS B1_DESC
	,COALESCE(B1_IPI, 0) AS B1_IPI
	,COALESCE(VALOR, 0) AS VALOR
FROM (
	SELECT *
		,LEN(CASE
				WHEN B1_REGANVI IS NOT NULL
					AND LEN(LTRIM(RTRIM(B1_REGANVI))) >= 1
					THEN B1_REGANVI
				WHEN B1_REGANV2 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV2))) >= 1
					THEN B1_REGANV2
				WHEN B1_REGANV3 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV3))) >= 1
					THEN B1_REGANV3
				WHEN B1_REGANV4 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV4))) >= 1
					THEN B1_REGANV4
				WHEN B1_REGANV5 IS NOT NULL
					AND LTRIM(RTRIM(LEN(B1_REGANV5))) >= 1
					THEN B1_REGANV5
				ELSE ''
				END) AS COD_ANVISA
	FROM SB1040
	WHERE B1_TIPO IN (
			'PA'
			,'PI'
			,'MR'
			)
		AND D_E_L_E_T_ != '*'
	) PRODUTOS
LEFT JOIN (
	SELECT DA1_CODPRO
		,MAX(DA1_PRCVEN) AS VALOR
	FROM DA1040
	WHERE DA1_CODTAB = '001'
		AND D_E_L_E_T_ != '*'
        AND DA1_PRCVEN > 0
	GROUP BY DA1_CODPRO
	) AS PRECOS ON (PRODUTOS.B1_COD = PRECOS.DA1_CODPRO)
WHERE COD_ANVISA >= 1 AND PRECOS.VALOR > 0
            
            
";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            //esvaziar tabela
            $sql = "insert into bs_produtos values ";
            $dados = [];
            foreach ($rows as $row){
                $etiqueta = str_replace("'", "''", $row['B1_DESC']);
                $dados[] = "('{$row['B1_COD']}', '$etiqueta', {$row['B1_IPI']}, {$row['VALOR']})";
            }
            if(count($dados) > 0){
                $sql_excluir = "truncate bs_produtos";
                $sqls_incluir = [];
                while(count($dados) > 0){
                    $dados_temp = [];
                    $sql = "insert into bs_produtos values ";
                    while (count($dados) > 0 && count($dados_temp) < 200) {
                        $dados_temp[] = array_shift($dados);
                    }
                    $sql .= implode(', ', $dados_temp);
                    $sqls_incluir[] = $sql;
                }
                
                if(count($sqls_incluir) > 0){
                    query($sql_excluir);
                    foreach ($sqls_incluir as $sql_atual){
                        query($sql_atual);
                    }
                }
            }
        }
    }
    
    private function getProdutosProtheus(){
        $ret = [];
        $sql = "select * from bs_produtos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $cod = $row['cod'];
                $etiqueta = trim($cod) . '-' . $row['etiqueta'];
                if(strlen($etiqueta) > 60){
                    $etiqueta = substr($etiqueta, 0, 60);
                }
                $temp = [
                    'cod' => $cod,
                    'etiqueta' => $etiqueta,
                    'valor' => floatval($row['valor']),
                ];
                $ret[$cod] = $temp;
            }
        }
        return $ret;
    }
    
    private function compararProdutos($produto_agendor, $produto_protheus){
        $nome = $produto_agendor['name'] === $produto_protheus['etiqueta'];
        $valor = floatval($produto_agendor['price']) === $produto_protheus['valor'];
        return $nome && $valor;
    }
    
    private function atualizarProdutosAgendor(){
        $produtos_protheus = $this->getProdutosProtheus();
        $produtos_agendor = $this->_rest->getAllProdutos(); //pega todos os produtos do agendor
        if(is_array($produtos_protheus) && count($produtos_protheus) > 0 && is_array($produtos_agendor) && count($produtos_agendor) > 0){
            set_time_limit(0);
            $codigos_protheus = array_keys($produtos_protheus);
            $codigos_agendor = array_column($produtos_agendor, 'code');
            foreach ($codigos_protheus as $cod){
                echo "$cod<br>";
                if(!in_array($cod, $codigos_agendor)){
                    $etiqueta = $produtos_protheus[$cod]['etiqueta'];
                    $valor = $produtos_protheus[$cod]['valor'];
                    $dados = ['name' => $etiqueta, 'active' => true, 'code' => $cod, 'price' => $valor];
                    do{
                        $resposta = $this->_rest->cadastrarProduto($dados);
                        sleep(3);
                    }
                    while($resposta === false);
                }
            }
            foreach ($produtos_agendor as $produto){
                $cod = $produto['code'];
                echo "$cod<br>";
                $temp = [];
                if(!in_array($cod, $codigos_protheus)){
                    if($produto['active']){
                        $temp = $produto;
                        $temp['active'] = false;
                        unset($temp['createdAt']);
                        unset($temp['id']);
                    }
                }
                else{
                    //verifica se precisa atualizar
                    if(!$this->compararProdutos($produto, $produtos_protheus[$cod])){
                        $temp = $produto;
                        $temp['active'] = true;
                        unset($temp['createdAt']);
                        unset($temp['id']);
                        $temp['name'] =  $produtos_protheus[$cod]['etiqueta'];
                        $temp['price'] = $produtos_protheus[$cod]['valor'];
                    }
                }
                if(count($temp) > 0){
                    do{
                        $resposta = $this->_rest->atualizarProduto($produto['id'], $temp);
                        sleep(3);
                    }
                    while($resposta === false);
                }
            }
        }
    }
}