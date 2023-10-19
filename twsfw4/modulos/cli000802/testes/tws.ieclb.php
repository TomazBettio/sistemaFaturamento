<?php
/*
 * Data Criacao: 28/12/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Agenda
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);




class ieclb{
	var $funcoes_publicas = array(
			'index' 	=> true,
	);
	
	//Pessoa
	private $_pessoa;
	
	//Contratos Beneficios
	private $_contratosB;
	
	//Sub contrato Beneficio
	private $_subContratoB;
	
	//Contratos Restituicao
	private $_contratosR;
	
	//Fiadores
	private $_fiadores;
	
	//Parcelas Beneficio
	private $_parcelasB = [];
	
	//Parcelas Restituicao
	private $_parcelasR = [];
	
	//Parcelas Beneficio por fiador
	private $_parcelasBfiador = [];
	
	//Extrato
	private $_extrato;
	
	public function index(){
		$pessoa = 19722;
		$this->_pessoa = $pessoa;
		
		$this->getContratosB($pessoa);
		$this->getSubContratoB();
		$this->getContratosR($pessoa);
		$this->getFiadores();
		
		$this->getParcelasB();
		$this->getParcelasR();
		
		//print_r($this->_contratosB);
		//print_r($this->_subContratoB);
		//print_r($this->_contratosR);
		//print_r($this->_fiadores);
		
		//print_r($this->_parcelasB);
		//print_r($this->_parcelasR);
		
		$this->separaBeneficioFiador();
		//print_r($this->_parcelasBfiador);
		
		$this->calculaAtualização();
		
		$ret = $this->montaRelatorio();
		
		return $ret;
	}
	
	private function montaRelatorio(){
		$ret = '';
		
		$param = array();
		$param['filtro'] = false;
		$param['programa'] = 'IECLB_fiadores';
		$param['titulo'] = 'Extrato por fiador';
		$relatorio = new relatorio01('extrato_plano',$param, 'Extrato');
		$paramTabela = array();
		$paramTabela['ordenacao'] = false;
		$paramTabela['filtro'] = false;
		$paramTabela['info'] = false;
		$relatorio->setParamTabela($paramTabela);
		$relatorio->setToExcel(true);
		
		$relatorio->addColuna(array('campo' => 'competencia'		, 'etiqueta' => 'Competência'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'), 0);
		$relatorio->addColuna(array('campo' => 'contrato'		, 'etiqueta' => 'Contrato'  			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'), 0);
		$relatorio->addColuna(array('campo' => 'vl_pago'			, 'etiqueta' => 'Vl.Pago'				, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'), 0);
		$relatorio->addColuna(array('campo' => 'vl_recebido'		, 'etiqueta' => 'Vl.Recebido'			, 'tipo' => 'V', 'width' => 150, 'posicao' => 'D'), 0);
		$relatorio->addColuna(array('campo' => 'dt_pagamento'	, 'etiqueta' => 'Pagamento'				, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'C'), 0);
		$relatorio->addColuna(array('campo' => 'vl_efetuado'		, 'etiqueta' => 'Vl.Efetuado'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'), 0);
		$relatorio->addColuna(array('campo' => 'vl_acumulado'	, 'etiqueta' => 'Acumulado Original'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'), 0);
		$relatorio->addColuna(array('campo' => 'correcao'		, 'etiqueta' => '% Correção'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'), 0);
		$relatorio->addColuna(array('campo' => 'vl_correcao'		, 'etiqueta' => 'Vl.Correção'			, 'tipo' => 'V', 'width' => 120, 'posicao' => 'D'), 0);
		$relatorio->addColuna(array('campo' => 'vl_corrigido'	, 'etiqueta' => 'Acumulado Corrigido'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'), 0);

		$secao = 0;
		foreach ($this->_extrato as $fiador => $contratos){
			foreach ($contratos as $contrato => $extrato){
				$parcelas = [];
				foreach ($extrato as $ext){
					$parcelas[] = $ext;
				}
				
				$titulo = $fiador.' - '.$this->_fiadores[$fiador]['nome'].' - Contrato: '.$contrato;
				$relatorio->setTituloSecao($secao, $titulo);
				$relatorio->setTituloSecaoPlanilha($secao, substr($this->_fiadores[$fiador]['nome'], 0, 15).'  Contrato '.$contrato);
				$relatorio->setDados($parcelas, $secao);
				
				$secao++;
			}
		}
		
		$ret .= $relatorio;
		
		return $ret;
	}
	
	private function calculaAtualização(){
		foreach ($this->_parcelasBfiador as $fiador => $contratos){
			foreach ($contratos as $contrato => $parcelas){
				$competenciaIni = $parcelas[0]['competencia'];
				$competenciaFim = date('Ym').'01';
				
				$periodos = $this->getPeriodos($competenciaIni, $competenciaFim);
				$indexador = $this->getIndicador($this->_contratosB[$contrato]['tipo']);
				
				$index = $this->getIndexadores($competenciaIni, $competenciaFim, $indexador);
				
				$this->montaExtrato($fiador, $contrato, $periodos, $index, $parcelas);
//print_r($this->_extrato);
				
				//print_r($periodos);
			}
		}
		
		foreach ($this->_extrato as $fiador => $contratos){
			foreach ($contratos as $contrato => $extrato){
				$this->atualizaExtrato($fiador, $contrato, $extrato);
			}
		}
		//print_r($this->_extrato);
			
			
	}
	
	private function atualizaExtrato($fiador, $contrato, $extrato){
		$acumulado_original = 0;
		$acumulado_corrigido = 0;
		$acumulado_anterior = 0;
//print_r($extrato);
		$valorParcelas = array();
		//Soma as parcelas pagas em uma competencia no ano/mes de pagamento
		foreach ($extrato as $comp => $parcela){
				if(!isset($valorParcelas[$comp])){
					$valorParcelas[$comp] = 0;
				}
				$valorParcelas[$comp] += $parcela['vl_pago'];
		}
		
//print_r($valorParcelas);
		//print_r($this->_extrato);
		if(count($extrato) > 0){
			foreach ($extrato as $competencia => $ext){
				$parcela = isset($valorParcelas[$competencia]) ? $valorParcelas[$competencia] : 0;
				//echo "Parcela: $parcela <br>\n";
				$acumulado_original += $parcela;
				//echo "Acumulado Anterior: $acumulado_anterior <br>\n";
				$vl_correcao = round($acumulado_anterior * ($ext['correcao'] / 100),2);
				
				$acumulado_corrigido = $acumulado_corrigido + $parcela + $vl_correcao;
				
				$this->_extrato[$fiador][$contrato][$competencia]['vl_acumulado'] = $acumulado_original;
				$this->_extrato[$fiador][$contrato][$competencia]['vl_correcao'] = $vl_correcao;
				$this->_extrato[$fiador][$contrato][$competencia]['vl_corrigido'] = $acumulado_corrigido;
				
				$acumulado_anterior = $acumulado_corrigido;
				$this->_competenciaUltimaAtualizacao = $competencia;
/*/				
				//Desconto na atualização (covid)
				if(isset($this->_descontosAtualizacao[$competencia])){
					//echo "$competencia <br>\n";
					//print_r($this->_descontosAtualizacao[$competencia]);
					
					$valorDesconto = round(($vl_correcao * ($this->_descontosAtualizacao[$competencia]['percentual']/100)), 2) * -1;
					$acumulado_corrigido = $acumulado_corrigido + $valorDesconto;
					$acumulado_anterior = $acumulado_corrigido;
					
					$this->_extrato[$competencia.'D'] = [];
					$this->_extrato[$competencia.'D']['tipo_mov'] 		= 'D';
					$this->_extrato[$competencia.'D']['contrato'] 		= $this->_extrato[$competencia]['contrato'];
					$this->_extrato[$competencia.'D']['subcontrato']	= $this->_extrato[$competencia]['subcontrato'];
					$this->_extrato[$competencia.'D']['competencia']	= $competencia;
					$this->_extrato[$competencia.'D']['comp'] 			= $this->_extrato[$competencia]['comp'];
					$this->_extrato[$competencia.'D']['vl_mensalidade'] = 0;
					$this->_extrato[$competencia.'D']['dt_pagamento'] 	= '';
					$this->_extrato[$competencia.'D']['vl_pago'] 		= 0;
					$this->_extrato[$competencia.'D']['vl_acumulado'] 	= $this->_extrato[$competencia]['vl_acumulado'];
					$this->_extrato[$competencia.'D']['correcao'] 		= '';
					$this->_extrato[$competencia.'D']['vl_correcao'] 	= $valorDesconto;
					$this->_extrato[$competencia.'D']['vl_corrigido'] 	= $acumulado_corrigido;
				}
/*/
			}
		}
	}
	
	private function montaExtrato($fiador, $contrato, $periodos, $index, $parcelas){
		$parcelas = $this->ajustaParcelasCompetencia($parcelas);
//print_r($parcelas);
		if(count($periodos) > 0){
			foreach ($periodos as $comp){
				$temp = array();
				$temp['tipo_mov'] 		= 'A';
				$temp['contrato'] 		= $contrato;
				$temp['subcontrato'] 	= 0;
				$temp['competencia'] 	= $comp;
				$temp['comp'] 		 	= $comp;
				$temp['vl_mensalidade'] = 0;
				$temp['dt_pagamento'] 	= '';
				$temp['vl_pago'] 		= 0;
				$temp['vl_acumulado'] 	= 0;
				$temp['correcao'] 		= isset($index[$comp]) ? $index[$comp] : 0;
				$temp['vl_correcao'] 	= 0;
				$temp['vl_corrigido'] 	= 0;
				if(isset($parcelas[$comp])){
					//					$temp['tipo_mov'] 		= 'B';
					//					$temp['subcontrato'] 	= $this->_parcelas[$comp]['subcontrato'];
					$temp['vl_mensalidade'] = $parcelas[$comp]['vl_mensalidade'];
					$temp['dt_pagamento'] 	= $parcelas[$comp]['dt_pagamento'];
					$temp['vl_pago'] 		= $parcelas[$comp]['vl_pago'];
				}
				
				$this->_extrato[$fiador][$contrato][$comp] = $temp;
				
			}
		}
		
	}
	
	private function ajustaParcelasCompetencia($parcelas){
		$ret = [];
		
		foreach ($parcelas as $p){
			$temp = [];
			$comp = substr($p['competencia'], 0, 6);
			$temp['vl_mensalidade'] = $p['valor'];
			$temp['dt_pagamento'] 	= $p['pagamento'];
			$temp['vl_pago'] 		= $p['vl_pago'];
			
			$ret[$comp] = $temp;
		}
		
		return $ret;
	}
	
	private function getIndexadores($competenciaIni, $competenciaFim, $indexador){
		$ret = [];
		
		$sql = "SELECT dt_inicio, vl_cota FROM ieclb_indexador_valor WHERE dt_inicio >= '$competenciaIni' AND dt_inicio <= '$competenciaFim' AND indexador = $indexador ORDER BY dt_inicio";
		$rows = queryIECLB($sql);
		if(isset($rows[0]['dt_inicio'])){
			foreach ($rows as $row){
				$comp = substr($row['dt_inicio'], 0,6);
				$ret[$comp] = round($row['vl_cota'],2);
			}
		}
		
		return $ret;
	}
	
	private function getPeriodos($competenciaIni, $competenciaFim){
		$ret = [];
		$sql = "SELECT distinct dt_inicio FROM ieclb_indexador_valor WHERE dt_inicio >= '$competenciaIni' AND dt_inicio <= '$competenciaFim'  ORDER BY dt_inicio";
//echo "Periodos: $sql <br>\n";
		$rows = queryIECLB($sql);
		if(isset($rows[0]['dt_inicio'])){
			foreach ($rows as $row){
				$comp = substr($row['dt_inicio'], 0,6);
				$ret[$comp] = $comp;
			}
		}
		
		return $ret;
	}
	
	private function separaBeneficioFiador(){
		foreach ($this->_parcelasB as $parcela){
			$fiador = $parcela['fiador1'];
			$contrato = $parcela['contrato'];
			
			$this->_parcelasBfiador[$fiador][$contrato][] = $parcela;
		}
	}
	
	private function getContratosB($pessoa){
		$sql = "SELECT * FROM ieclb_contratos_beneficios WHERE pessoa = $pessoa";
		$rows = queryIECLB($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['contrato'] 		= $row['contrato'];
				$temp['tipo'] 			= $row['tipo'];
				$temp['inicio'] 		= $row['dt_inicio'];
				$temp['restituicao'] 	= $row['contrato_restituicao'];
				$temp['fim'] 			= $row['dt_fim_correcao'];

				$this->_contratosB[$row['contrato']] = $temp;
			}
		}
	}
	
	private function getIndicador($plano){
		$ret = '';
		
		$sql = "SELECT indexador FROM ieclb_planos WHERE  plano = $plano";
		$rows = queryIECLB($sql);
		if(isset($rows[0]['indexador'])){
			$ret = $rows[0]['indexador'];
		}
		
		return $ret;
	}
	
	private function getSubContratoB(){
		if(count($this->_contratosB) > 0){
			$contratos = array_column($this->_contratosB,'contrato');
			
			$sql = "SELECT * FROM ieclb_subcontrato_beneficio WHERE contrato IN (".implode(',', $contratos).")";
			$rows = queryIECLB($sql);
			
			if(is_array($rows) && count($rows) > 0){
				foreach ($rows as $row){
					$temp = [];
					$temp['sub'] 		= $row['id'];
					$temp['contrato'] 	= $row['contrato'];
					$temp['tipo'] 		= $row['tipo'];
					$temp['inicio'] 	= $row['dt_inicio'];
					$temp['parcelas'] 	= $row['parcelas'];
					$temp['valor'] 		= $row['valor_original'];
					$temp['total'] 		= $row['total_concessao'];
					$temp['fiador1'] 	= $row['fiador_1'];
					$temp['fiador2'] 	= $row['fiador_2'];
					
					$this->_subContratoB[$row['id']] = $temp;
				}
			}
		}
	}
	
	private function getContratosR($pessoa){
		$sql = "SELECT * FROM ieclb_contratos_devedor WHERE pessoa = $pessoa";
		$rows = queryIECLB($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['contrato'] 	= $row['contrato'];
				$temp['tipo'] 		= $row['tipo'];
				$temp['inicio'] 	= $row['dt_inicio'];
				$temp['divida'] 	= $row['divida_corrigida'];
				
				$this->_contratosR[] = $temp;
			}
		}
	}

	private function getFiadores(){
		$fiadores = array_column($this->_subContratoB,'fiador1');
		$sql = "SELECT * FROM ieclb_pessoa WHERE id IN (".implode(',', $fiadores).")";
		$rows = queryIECLB($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['fiador'] 		= $row['id'];
				$temp['nome'] 			= $row['nome'];
				
				$this->_fiadores[$row['id']] = $temp;
			}
		}
	}

	private function getParcelasB(){
		//$sub = array_column($this->_contratosB,'sub');
		$sub = [];
		foreach ($this->_subContratoB as $subContrato){
			$sub[] = $subContrato['sub'];
		}
		
		$sql = "SELECT * FROM ieclb_beneficio_parcelas WHERE vl_pago > 0 AND vl_devolvido IS NULL AND subcontrato IN (".implode(',', $sub).") ORDER BY dt_vencimento";
//echo "$sql <br>\n";
		$rows = queryIECLB($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['id'] 			= $row['id'];
				$temp['sub'] 			= $row['subcontrato'];
				$temp['contrato'] 		= $row['contratoID'];
				$temp['fiador1'] 		= $this->_subContratoB[$row['subcontrato']]['fiador1'];
				$temp['fiador2'] 		= $this->_subContratoB[$row['subcontrato']]['fiador2'];
				
				$temp['competencia'] 	= $row['dt_competencia'];
				$temp['vencimento'] 	= $row['dt_vencimento'];
				$temp['pagamento'] 		= $row['dt_pagamento'];
				$temp['valor'] 			= $row['vl_mensalidade'];
				$temp['vl_pago'] 		= $row['vl_pago'];
		
				$this->_parcelasB[] = $temp;
			}
		}
	}
	
	private function getParcelasR(){
		$contratos = array_column($this->_contratosR,'contrato');
		$sql = "SELECT * FROM ieclb_devedor_parcela WHERE IFNULL(dt_exc, '')  = '' AND contrato IN (".implode(',', $contratos).") AND vl_pago > 0 ORDER BY dt_vencimento";
		//echo "$sql <br>\n";
		$rows = queryIECLB($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['id'] 			= $row['id'];
				$temp['contrato'] 		= $row['contrato'];
				
				$temp['competencia'] 	= $row['dt_competencia'];
				$temp['vencimento'] 	= $row['dt_vencimento'];
				$temp['pagamento'] 		= $row['dt_pagamento'];
				$temp['valor'] 			= $row['vl_emitida'];
				$temp['correcao'] 		= $row['vl_correcao_emitida'];
				$temp['desconto']		= $row['vl_desconto_emitido'];
				$temp['tarifa'] 		= $row['vl_tarifa'];
				$temp['multa'] 			= $row['vl_multa'];
				$temp['juros'] 			= $row['vl_juros'];
				$temp['pago'] 			= $row['vl_pago'];
				$this->_parcelasR[] = $temp;
			}
		}
	}
}


function queryIECLB($sql, $debugQuery = false, $debugRet = false){
	global $dbIECLB, $config;
	$ret = array();
	if($debugQuery){
		echo "\nSQL: $sql <br>\n";
	}
	if(isset($config['site']['logQuery']) && $config['site']['logQuery']){
		log::gravaLog('logQuery', getModulo().' - '.getClasse().' - '.getMetodo());
		log::gravaLog('logQuery', $sql);
	}
	//print_r($db);
	$res = $dbIECLB->Execute($sql);
	if ($res === false){
		if($config['debug'] || $debugQuery){
			echo "<br>\nErro no SQL: $sql \n<br>";
			print $dbIECLB->ErrorMsg();
			echo "\n<br>------------------------------<br>\n";
		}
		return false;
	}else{
		$sql = strtoupper(trim($sql));
		$pos1 = strpos($sql, "SELECT");
		$pos2 = strpos($sql, "DESCRIBE");
		if(($pos1 === false || $pos1 > 5) && $pos2 === false){
			//			$ret = $db->GenID;
			return true;
		}else{
			$ret = $res->GetRows();
		}
		if($debugRet){
			print_r($ret);
		}
		return $ret;
	}
}