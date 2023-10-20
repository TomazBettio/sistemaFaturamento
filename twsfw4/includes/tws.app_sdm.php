<?php
/*
 * Data Criacao: 12/04/2022
 * Autor: Thiel
 *
 * Descricao: Funções comuns no SDM
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class app_sdm{
	
	//-------------------------------------------------------------------------------------- UI --------------------------------------
	
	//-------------------------------------------------------------------------------------- GET -------------------------------------
	
	public static function getAgendasSemOS($param = []){
		$ret = 0;
		$where = '';

		$clienteFora = $param['clientesFora'] ?? true;
		if($clienteFora !== false){
			$listaClientesFora = getParametroSistema('sdm_clientes_sem_os');
			if(!empty($listaClientesFora)){
				$where .= " AND cliente_agenda NOT IN (".$listaClientesFora.") ";
			}
		}
		
		if(isset($param['usuario'])){
			$where .= " AND recurso = '".$param['usuario']."' ";
		}else{
			$where .= " AND recurso = '".getUsuario()."' ";
		}
		
		if(isset($param['data'])){
			$where .= " AND data < '".$param['data']."'";
		}else{
			$where .= " AND data < '".date('Ymd')."'";
		}
		
		$sql = "SELECT COUNT(*) FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' ".$where;
//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	public static function getAgendasMarcadas($param = []){
		$ret = [];
		$where = '';
		$somenteQuantidade = $param['somenteQuantidade'] ?? false;
		
		$clienteFora = $param['clientesFora'] ?? true;
		if($clienteFora !== false){
			$where .= " AND cliente_agenda NOT IN (".getParametroSistema('sdm_clientes_sem_os').") ";
		}
		
		if(isset($param['usuario'])){
			$where .= " AND recurso = '".$param['usuario']."' ";
		}else{
			$where .= " AND recurso = '".getUsuario()."' ";
		}
		
		if(isset($param['data'])){
			$where .= " AND data < '".$param['data']."'";
		}else{
			$where .= " AND data > '".date('Ymd')."'";
		}
		
		
		if($somenteQuantidade){
			$sql = "SELECT count(*) FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' ".$where;
		}else{
			$campos = ['recurso','data','turno','contato','cliente_agenda'];
			$sql = "SELECT * FROM  sdm_agenda WHERE TRIM(os) = ''  AND IFNULL(del,'') = '' ".$where;
		}
		
		
		//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			if($somenteQuantidade){
				$ret = $rows[0][0];
			}else{
				foreach ($rows as $row){
					$temp = [];
					foreach ($campos as $c){
						$temp[$c] = $row[$c];
					}
			
					$ret[] = $temp;
				}
			}
		}		
		
		return $ret;
	}
	
	public static function getRecursos($recurso = ''){
		$ret = [];
		
		$where = '';
		if(!empty($recurso)){
			$where = " AND recurso = '$recurso'";
		}
		$sql = "SELECT * FROM sdm_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' $where ORDER BY apelido";
		//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['nome'] 	= $row['nome'];
				$temp['apelido']= $row['apelido'];
				$temp['tipo'] 	= $row['tipo'];
				$temp['recurso']= $row['usuario'];
				
				$ret[$row['usuario']] = $temp;
			}
		}
		
		return $ret;
	}
	
	public static function getRecursosLista($apelido = true, $ativos = true){
		$ret = [];
		
		$ret[0][0] = '';
		$ret[0][1] = '';
		
		$where = '';
		if($ativos){
			$where = " AND ativo = 'S'";
		}
		$sql = "SELECT nome, apelido, usuario FROM sdm_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' $where ORDER BY apelido";
//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp[0] = $row['usuario'];
				$temp[1] = $apelido ? $row['apelido'] : $row['nome'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	public static function getClienteCampo($cliente, $campo = 'nreduz'){
		$ret = '';
		
		$sql = "SELECT $campo FROM cad_organizacoes WHERE cod = '$cliente'";
//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
	//-------------------------------------------------------------------------------------- SET -------------------------------------
	
	//-------------------------------------------------------------------------------------- ADD -------------------------------------
	
	//-------------------------------------------------------------------------------------- VO  -------------------------------------
	
	//-------------------------------------------------------------------------------------- UTEIS -----------------------------------
	public static function enviaEmailAgendaRecurso($agenda, $exclusao = false){
		$apelido = getUsuario('apelido', $agenda['recurso']);
		$agendador = getUsuario('apelido', $agenda['marcado_por']);
		
		$param = [];
		$param['programa'] = 'envio_agenda_email';
		$param['imprimeCabecalho'] = false;
		$param['auto'] = true;
		$param['mensagem_inicio_email'] = $exclusao ? app_sdm::getMensagemInicioEmailExclusaoAgenda($apelido) : app_sdm::getMensagemInicioEmailAgenda($apelido);
		$email = new relatorio01($param);
		
		$email->addColuna(['campo' => 'etiqueta', 'etiqueta' => ''	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E']);
		$email->addColuna(['campo' => 'valor' 	, 'etiqueta' => ''	, 'tipo' => 'T', 'width' => 400, 'posicao' => 'E']);
		
		$dados = [];
		$dados[] = ['etiqueta' => 'Data'		, 'valor' => datas::dataS2D($agenda['data'])];
		$dados[] = ['etiqueta' => 'Turno'		, 'valor' => getTabelaDesc('000022',$agenda['turno'])];
		$dados[] = ['etiqueta' => 'Local'		, 'valor' => getTabelaDesc('000023',$agenda['local'])];
		$dados[] = ['etiqueta' => 'Cliente'		, 'valor' => $agenda['cliente_agenda'].' - '.funcoes_cad::getClienteCampo($agenda['cliente_agenda'])];
		$dados[] = ['etiqueta' => 'Contato'		, 'valor' => $agenda['contato']];
		$dados[] = ['etiqueta' => 'Tipo'		, 'valor' => getTabelaDesc('000024',$agenda['tipo'])];
		$dados[] = ['etiqueta' => 'Tarefa'		, 'valor' => nl2br($agenda['tarefa'])];
		//$dados[] = ['etiqueta' => 'Status'		, 'valor' => $agenda['status']];
		//$dados[] = ['etiqueta' => 'OS'			, 'valor' => $agenda['os']];
		//$dados[] = ['etiqueta' => 'Ticket'		, 'valor' => $agenda['ticket']];
		$dados[] = ['etiqueta' => 'Projeto'		, 'valor' => app_sdm::getProjetoCampo($agenda['projeto'])];
		$dados[] = ['etiqueta' => 'Agendado por', 'valor' => $agendador];
		
		$para = $agenda['recurso'];
		
		
		$email->setDados($dados);
		
		$email->enviaEmail($para);
	}
	
	public static function enviaEmailExclusaoAgendaRecurso($dados){
		$sql = "SELECT * FROM sdm_agenda
				WHERE recurso = '".$dados[0]."'
					AND data = '".$dados[1]."'
					AND cliente_agenda = '".$dados[3]."'
					AND turno = '".$dados[2]."'
					AND status = 'E'
				ORDER BY del_em DESC
				LIMIT 1";
		$rows = query($sql);
		
		if(isset($rows[0]['id'])){
			app_sdm::enviaEmailAgendaRecurso($rows[0], true);
		}
	}
	
	public static function getMensagemInicioEmailAgenda($apelido){
		$ret = '';
		
		$ret .= "Prezado $apelido <br>\n<br>\n";
		$ret .= "A agenda abaixo foi marcada: <br>\n<br>\n";
		
		return $ret;
	}
	
	public static function getMensagemInicioEmailExclusaoAgenda($apelido){
		$ret = '';
		
		$ret .= "Prezado $apelido <br>\n<br>\n";
		$ret .= "A agenda abaixo foi <b><font color='#FF0000'>EXCLUÍDA</font></b>: <br>\n<br>\n";
		
		return $ret;
	}
	
	public static function getProjetoCampo($projeto, $campo = 'titulo'){
		$ret = '';
		if(strval($projeto) > 0){
			$sql = "select $campo from sdm_projetos where id = $projeto";
			$rows = query($sql);
			if(isset($rows[0][$campo])){
				$ret = $rows[0][$campo];
			}
		}
		
		return $ret;
	}
}