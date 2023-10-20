<?php
function query2($sql){
	global $db2;
	$ret = array();
	//echo "\nSQL: $sql <br>\n";
	//print_r($db);
	$res = odbc_exec($db2, $sql);
	if ($res === false ){
		echo "\n\n\n$sql <br>\n\n\n";
		print odbc_error($db2);
		return false;
	}else{
		if(strpos(strtoupper($sql), "SELECT") !== false){
			$ret = array();
			while ($hash = @odbc_fetch_array ($res)) {
				$ret[] = $hash;
			}
			return $ret;
		}
		return $ret;
	}
}

function getFilial(){
    return '01';
}

function criarRestAgendor(){
    $rest = new rest_agendor('https://api.agendor.com.br/v3', '6d81c352-1bac-4ee4-9402-3d40f2f22aa9');
    return $rest;
}

function criarRestProtheus(){
    global $config;
    $rest_protheus = new rest_protheus($config['protheus']['link'], $config['protheus']['user'], $config['protheus']['senha']);
    return $rest_protheus;
}

function moverCardAgendor($id_card, $nova_etapa, $status = ''){
    $sql = "update bs_agendor_negocios set etapa = $nova_etapa";
    if(!empty($status)){
        $sql .= ", tipo = '$status'";
    }
    $sql .= " where id = $id_card";
    query($sql);
    $rest = criarRestAgendor();
    $funil = getInfoNegocioAgendor($id_card, 'funil');
    return $rest->moverEtapa($id_card, $nova_etapa, $funil);
}

function getInfoNegocioAgendor($id_agendor, $campo = ''){
    $ret = false;
    $sql = "select * from bs_agendor_negocios where id = '$id_agendor'";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        if(!empty($campo)){
            if(isset($rows[0][$campo])){
                $ret = $rows[0][$campo];
            }
        }
        else{
            $ret = $rows[0];
        }
    }
    return $ret;
}

function getInfoOrcamento($codigo_unico, $campo = ''){
    $ret = false;
    $sql = "select * from bs_orcamentos where codigo_unico = '$codigo_unico'";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        if(!empty($campo)){
            if(isset($rows[0][$campo])){
                $ret = $rows[0][$campo];
            }
        }
        else{
            $ret = $rows[0];
        }
    }
    return $ret;
}

function gerarPedidoProtheus($codigo_unico = '', $num_orcamento = ''){
    $ret = '';
    if(empty($num_orcamento) && !empty($codigo_unico)){
           $num_orcamento = getNumOrcamento($codigo_unico);
    }
    
    if(!empty($num_orcamento)){
        global $config;
        $rest_protheus = new rest_protheus($config['protheus']['link'], $config['protheus']['user'], $config['protheus']['senha']);
        $ret = $rest_protheus->confirmarOrcamento(['orcamento' => $num_orcamento]);
    }
    
    return $ret;
}

function getNumOrcamento($codigo_unico){
    $ret = '';
    $sql = "select id from bs_orcamentos where codigo_unico = '$codigo_unico'";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        $ret = $rows[0]['id'];
    }
    return $ret;
}

function getNumOrcamentoProtheus($codigo_unico){
    $ret = '';
    $filial = getFilial();
    $sql = "select CJ_NUM from SCJ040 where CJ_MFIDORC = '$codigo_unico' AND D_E_L_E_T_ != '*' and CJ_FILIAL = '$filial'";
    //$sql = "select CJ_NUM from SCJ040 where CJ_MFIDORC = '$codigo_unico' AND D_E_L_E_T_ != '*'";
    $rows = query2($sql);
    if(is_array($rows) && count($rows) > 0){
        $ret = $rows[0]['CJ_NUM'];
    }
    return $ret;
}

function getOrcamentoProtheus($codigo_unico = '', $num_orcamento = ''){
    $ret = [];
    if(empty($num_orcamento)){
        $num_orcamento = getNumOrcamento($codigo_unico);
    }
    $filial = getFilial();
    $sql = "select * from SCJ040 where CJ_NUM = '$num_orcamento' and CJ_FILIAL = '$filial'";
    //$sql = "select * from SCJ040 where CJ_NUM = '$num_orcamento'";
    
    $rows = query2($sql);
    if(is_array($rows) && count($rows) > 0){
        $ret = $rows[0];
    }
    return $ret;
}

function liberarOrcamento($codigo_unico = '', $codigo_proposta = ''){
    $codigo_proposta = !empty($codigo_proposta) ? $codigo_proposta : getNumOrcamento($codigo_unico);
    alterarStatusOrcamento($codigo_proposta, 'A');
}

function bloquearOrcamento($codigo_unico = '', $num_orcamento = ''){
    $num_orcamento = !empty($num_orcamento) ? $num_orcamento : getNumOrcamento($codigo_unico);
    alterarStatusOrcamento($num_orcamento, 'F');
}

function alterarStatusOrcamento($num_orcamento, $status){
    $ret = '';
    if(!empty($num_orcamento)){
        $rest_protheus = criarRestProtheus();
        $ret = $rest_protheus->alterarStatusOrcamento(['orcamento' => $num_orcamento, 'status' => $status]);
    }
    return $ret;
}

function recuperarUltimoCodigoUnico($id_agendor){
    $ret = '';
    $sql = "select codigo_unico from bs_orcamentos join (select id_agendor, max(insert_dt) as insert_dt from bs_orcamentos where id_agendor = '$id_agendor' group by id_agendor) as orcamentos_novos using (id_agendor, insert_dt) where id_agendor = '$id_agendor'";
    $rows = query($sql);
    if(is_array($rows) && count($rows) > 0){
        $ret = $rows[0]['codigo_unico'];
    }
    return $ret;
}



function recuperarIdAgendor($codigo_unico, $pedido = ''){
    $ret = false;
    $sql = '';
    if(!empty($codigo_unico)){
        $sql = "select id_agendor from bs_orcamentos where codigo_unico = '$codigo_unico'";
    }
    elseif(!empty($pedido)){
        $sql = "select id_agendor from bs_orcamentos where pedido = '$pedido'";
    }
    if(!empty($sql)){
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['id_agendor'];
        }
    }
    return $ret;
}

function getNumPedidoProtheus($codigo_unico = '', $num_orcamento = ''){
    $ret = false;
    if(empty($num_orcamento)){
        $num_orcamento = getNumOrcamento($codigo_unico);
    }
    if(!empty($num_orcamento)){
        $filial = getFilial();
        $sql = "SELECT C6_NUM FROM SC6040 WHERE C6_NUMORC like '$num_orcamento" . "__' and C6_FILIAL = '$filial'";
        //$sql = "SELECT C6_NUM FROM SC6040 WHERE C6_NUMORC like '$num_orcamento" . "__'";
        
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['C6_NUM'];
        }
    }
    return $ret;
}

function getNumPedido($codigo_unico = ''){
    $ret = false;
    if(!empty($codigo_unico)){
        $sql = "select pedido from bs_orcamentos where codigo_unico = '$codigo_unico'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['pedido'];
        }
    }
    return $ret;
}

function validarCodigoUnico($codigo_unico){
    //valida se o codigo único informa é referente ao último orçamento criado para o card
    return $codigo_unico === recuperarUltimoCodigoUnico(recuperarIdAgendor($codigo_unico));
}

function validarNegocioAgendor($id_agendor, $tipo){
    return getInfoNegocioAgendor($id_agendor, 'tipo') == $tipo;
}

function getClienteOrcamentoProtheus($codigo_unico = '', $num_orcamento = ''){
    $ret = false;
    if(empty($num_orcamento)){
        $num_orcamento = getNumOrcamento($codigo_unico);
    }
    if(!empty($num_orcamento)){
        $filial = getFilial();
        $sql = "SELECT * FROM SA1040 WHERE A1_COD IN (SELECT CJ_CLIENT FROM SCJ040 WHERE CJ_NUM = '$num_orcamento' and CJ_FILIAL = '$filial')";
        //$sql = "SELECT * FROM SA1040 WHERE A1_COD IN (SELECT CJ_CLIENT FROM SCJ040 WHERE CJ_NUM = '$num_orcamento')";
        
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0];
        }
    }
    return $ret;
}

function criarTarefaAgendor($id_agendor, $texto, $anexo = ''){
    $rest = criarRestAgendor();
    if(empty($anexo)){
        return $rest->criarTarefa($id_agendor, $texto);
    }
    else{
        return $rest->criarTarefaComAnexo($id_agendor, $texto, $anexo);
    }
}

function criarTarefaReprovado($id_agendor, $motivo, $tipo){
    $prefixo = '';
    if($tipo == 'O'){
        $prefixo = "O seu orçamento foi reprovado, seguem os motivos informados:
";
    }
    if($tipo == 'P'){
        $num_orcamento = getNumOrcamento(recuperarUltimoCodigoUnico($id_agendor));
        $prefixo = "O orçamento $num_orcamento foi reprovado e não foi transformado em pedido, seguem os motivos informados:
";
    }
    $motivo = $prefixo . $motivo;
    criarTarefaAgendor($id_agendor, $motivo);
}

function criarTarefaOrcamentoCriado($id_agendor, $codigo_unico){
    $num_orcamento = getNumOrcamento($codigo_unico);
    $texto = "Orçamento número $num_orcamento criado";
    criarTarefaAgendor($id_agendor, $texto);
}

function criarTarefaAprovado($id_agendor, $num_orcamento, $codigo_unico, $tipo, $anexo = ''){
    $motivo = '';
    if($tipo == 'O'){
        $motivo = "Orçamento $num_orcamento criado, por favor mova o card para a próxima coluna caso o cliente concorde com a proposta em anexo";
    }
    if($tipo == 'P'){
        $numero_pedido = getNumPedido($codigo_unico);
        $motivo = "O orçamento $num_orcamento foi aprovado e tranformado em pedido de número $numero_pedido";
    }
    if(!empty($motivo)){
        criarTarefaAgendor($id_agendor, $motivo, $anexo);
    }
}

function cancelarOrcamento($codigo_unico = '', $num_orcamento = ''){
    $num_orcamento = !empty($num_orcamento) ? $num_orcamento : getNumOrcamento($codigo_unico);
    alterarStatusOrcamento($num_orcamento, 'C');
    excluirOrcamento($num_orcamento);
}

function excluirOrcamento($num_orcamento){
    $rest_protheus = criarRestProtheus();
    return $rest_protheus->excluirOrcamento($num_orcamento);
}

function getIpiProduto($produto){
     $ret = 0;
     $produto = trim($produto);
     if(!empty($produto)){
        $sql = "select ipi from bs_produtos where cod = '$produto'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['ipi'];
        }
     }
     return $ret;
}

function getCardAgendor($id_agendor){
    $ret = '';
    $rest = criarRestAgendor();
    $temp = $rest->getNegocio($id_agendor);
    if(is_array($temp) && isset($temp['data'])){
        $ret = $temp['data'];
    }
    return $ret;
}

function verificarOrdemEtapaAgendor($id_agendor){
    $etapa_banco = intval(getInfoNegocioAgendor($id_agendor, 'etapa'));
    $card = getCardAgendor($id_agendor);
    $etapa_agendor = intval($card['dealStage']['sequence']);
    return $etapa_banco == $etapa_agendor;
}

function excluirNegocioGoflow($id_agendor){
    $sql = "delete from bs_agendor_negocios where id = '$id_agendor'";
    query($sql);
}