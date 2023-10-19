<?php
/*
 * Data Criacao: 01/07/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Calculo de insumos - MGT - Tabela de resultados
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class insumos_ui{
	
	private $_path;
	
	private $_anos;
	
	//Arquivo
	private $_arquivo;
	
	//Dados
	private $_I250;
	
	//Resumo Anual
	private $_resumo_anual = [];
	
	//Resumo Mensal
	private $_resumo_mensal = [];
	
	//Plano de contas
	private $_planoContas;
	
	//Contas fora
	private $_contasFora = [];
	
	//Dados para gerar o excel
	private $_dadosExcel;
	
	//Execel
	private $_excel;
	
	//Arqivo resultante excel
	private $_excelArquivo;
	
	//Raazao Social
	private $_razao;
	
	//Indices de ws excel
	private $_indicesWS = [];
	
	//Link do excel
	private $_linkExcel;
	
	public function __construct($path, $arquivo, $cnpj){
		global $config;
		$this->_path = $path;
		$this->_arquivo = $arquivo;
		$this->_excelArquivo = $path.DIRECTORY_SEPARATOR.'Insumos.xlsx';
		
		if(empty($this->_razao)){
			$this->_razao = $this->getNomeEmpresa($cnpj);
		}
		
		$this->getAnos();
		
		$this->getContasFora($cnpj);

		$this->_linkExcel = $config['linkInsumos'].$cnpj.'/'.'Insumos.xlsx';
	}
	
	public function verificaArquivo(){
		if(is_file($this->_path.DIRECTORY_SEPARATOR.$this->_arquivo)){
			return true;
		}else{
			addPortalMensagem('Não encontrado arquivo resumo I250 - Favor fazer upload!','danger');
		}
	}
	
	public function getPainel(){
		$ret = '';
		$this->recuperaI250();
//print_r($this->_I250);die();

		$ret .= $this->montaTabelaAnual();

		$ret .= $this->montaTabelaMensalBase();
		
		$ret .= $this->montaTabelaMensal();
		
		$this->geraExcel();
		
		return $ret;
	}
	

	//---------------------------------------------------------------------------------------------------------
	private function recuperaI250(){
		$handle = fopen($this->_path.DIRECTORY_SEPARATOR.$this->_arquivo, "r");
		if ($handle) {
			//Retira o cabecalho
			$linha = fgets($handle);
			while (!feof($handle)) {
				$linha = fgets($handle);
//echo "$linha <br>\n";
				if (strlen(trim($linha)) > 0) {
					$linha = str_replace("\n", '', $linha);
					$linha = str_replace(",", '.', $linha);
					$dados = explode(';', $linha);
					
					$conta = $dados[1];
					if(!isset($this->_planoContas[$conta])){
						$this->_planoContas[$conta] = utf8_encode($dados[4]);
					}
//echo "$conta <br>\n";
					if(array_search($conta, $this->_contasFora) === false){
					
						$data = datas::dataD2S($dados[9]);
						
						$this->_I250[] = [
								'ano'		=> substr($data, 0, 4),
								'mes'	=> substr($data, 4, 2),
								'conta'		=> $conta,
								'data'		=>$data,
								'DC'		=>$dados[7],
								'vl'		=> isset($dados[6]) && !empty($dados[6]) ? $dados[6] : '0',
								'pis'		=>isset($dados[13]) && !empty($dados[13]) ? $dados[13] : 0,
								'cof'		=>isset($dados[14]) && !empty($dados[14]) ? $dados[14] : 0,
								'pisCalc'	=>isset($dados[15]) && !empty($dados[15]) ? $dados[15] : 0,
								'cofCalc'	=>isset($dados[16]) && !empty($dados[16]) ? $dados[16] : 0,
						];
					}

				}
			}
			ksort($this->_I250);
			fclose($handle);
//print_r($this->_I250);
		}
	}
	
	//--------------------------------------------------------- UTEIS ------------------------------------------
	private function getAnos(){
		$diretorio = dir($this->_path);
		
		while($arquivo = $diretorio->read()){
			if($arquivo != '.' && $arquivo != '..' && $arquivo >= '2000' && $arquivo <= '2500'){
				$this->_anos[] = $arquivo;
			}
		}
		sort($this->_anos);
	}
	
	//--------------------------------------------------------- RESUMO MENSAL ------------------------------------
	private function montaTabelaMensal(){
		$ret = '';
		
		$this->calculaResumoMensal();
		$dados = $this->getDadosMensal();
		$this->_dadosExcel['mensal'] = $dados;
		
		$param = [];
		$param['titulo'] = "Total a Recuperar por mês";
		$param['cancela'] = true;
		$param['ordenacao'] = false;
		$param['info'] = false;
		$param['filtro'] = false;
		$relatorio = new tabela01($param);
		
		$relatorio->addColuna(array('campo' => 'comp'	, 'etiqueta' => 'Competência'	,'tipo' => 'T', 'width' => 100,	'posicao' => 'C'));
		foreach ($this->_anos as $ano){
			$relatorio->addColuna(array('campo' => $ano.'base'	, 'etiqueta' => 'Base de Cálculo '.$ano		,'tipo' => 'V', 'width' => 130, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => $ano.'total' , 'etiqueta' => 'Total a Recuperar '.$ano	,'tipo' => 'V', 'width' => 130, 'posicao' => 'D'));
		}
		
		$relatorio->setDados($dados);
		
		$ret .= $relatorio;
		
		return $ret;
	}
	
	private function calculaResumoMensal(){
		if(count($this->_resumo_mensal) > 0){
			//Não é necessário calcular novamente
			return;
		}
		foreach ($this->_I250 as $I250){
			$ano = $I250['ano'];
			$mes = $I250['mes'];
			$conta = $I250['conta'];
			if(!isset($this->_resumo_mensal[$ano][$mes][$conta])){
				$this->_resumo_mensal[$ano][$mes][$conta]['base'] = 0;
				$this->_resumo_mensal[$ano][$mes][$conta]['pisCalc'] = 0;
				$this->_resumo_mensal[$ano][$mes][$conta]['cofCalc'] = 0;
			}
			$pis = (float)$I250['pisCalc'];
			$cofins = (float)$I250['cofCalc'];
			$base = (float)$I250['vl'];
					
			$this->_resumo_mensal[$ano][$mes][$conta]['pisCalc'] = round((float)$this->_resumo_mensal[$ano][$mes][$conta]['pisCalc'] + $pis, 2);
			$this->_resumo_mensal[$ano][$mes][$conta]['cofCalc'] = round((float)$this->_resumo_mensal[$ano][$mes][$conta]['cofCalc'] + $cofins, 2);
			$this->_resumo_mensal[$ano][$mes][$conta]['base'] = round((float)$this->_resumo_mensal[$ano][$mes][$conta]['base'] + $base, 2);
		}
		//print_r($this->_resumo_mensal);
	}
	
	private function getDadosMensal(){
		$ret = [];
		$mesesAbr = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez' ];
		$meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ];
		
		foreach ($this->_resumo_mensal as $ano => $resumoMensal){
			foreach ($meses as $k => $mes){
				$temp = [];
				$temp['comp'] = $mesesAbr[$k];
				$temp[$ano.'base'	] = 0;
				$temp[$ano.'total'	] = 0;
				if(isset($resumoMensal[$mes])){
					foreach ($resumoMensal[$mes] as $valores){
						$temp[$ano.'base'	] += $valores['base'];
						$temp[$ano.'total'	] += $valores['pisCalc'] + $valores['cofCalc'];
					}
				}
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	
	//--------------------------------------------------------- RESUMO MENSAL BASE ------------------------------------
	private function montaTabelaMensalBase(){
		$ret = '';
		$meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ];
		$mesesAbr = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez' ];
		
		$this->calculaResumoMensal();
		
		$dados = $this->getDadosMensalBase();
		$this->_dadosExcel['mensalBase'] = $dados;
		
		$param = [];
		$param['titulo'] = "Total Mensal";
		$param['cancela'] = true;
		$relatorio = new relatorio01($param);
		
		//$relatorio->addColuna(array('campo' => 'conta'	, 'etiqueta' => 'Conta'		,'tipo' => 'T', 'width' => 130, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc'	, 'etiqueta' => 'Conta'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
		foreach ($this->_anos as $ano){
			foreach ($meses as $k => $mes){
				$a = $ano - 2000;
				$relatorio->addColuna(array('campo' => $ano.$mes.'base'	, 'etiqueta' => 'BC '.$mesesAbr[$k].'/'.$a	,'tipo' => 'V', 'width' => 130, 'posicao' => 'D'));
			}
		}
		$relatorio->addColuna(array('campo' => 'total'	, 'etiqueta' => 'Total a Recuperar'	,'tipo' => 'V', 'width' => 130, 'posicao' => 'D'));
		
		$relatorio->setDados($dados);
		//$relatorio->setToExcel(true,$this->_path.DIRECTORY_SEPARATOR.'Resumo_mensal.xlsx', true);
		$relatorio->setToExcel(false);
		
		$ret .= $relatorio;
		
		return $ret;
	}
	
	private function getDadosMensalBase(){
		$ret = [];
		$temp = [];
		
		foreach ($this->_resumo_mensal as $ano => $resumoMensal){
			foreach ($resumoMensal as $mes => $resumo){
				foreach ($resumo as $conta => $valor){
					if(!isset($temp[$conta][$ano][$mes]['baseTotal'])){
						$temp[$conta][$ano][$mes]['baseTotal'] = 0;
						$temp[$conta][$ano][$mes]['pisTotal']  = 0;
						$temp[$conta][$ano][$mes]['cofTotal']  = 0;
					}
					
					$temp[$conta][$ano][$mes]['base'] = $valor['base'];
					$temp[$conta][$ano][$mes]['pis']  = $valor['pisCalc'];
					$temp[$conta][$ano][$mes]['cof']  = $valor['cofCalc'];
					
					$temp[$conta][$ano][$mes]['baseTotal'] += $valor['base'];
					$temp[$conta][$ano][$mes]['pisTotal']  += $valor['pisCalc'];
					$temp[$conta][$ano][$mes]['cofTotal']  += $valor['cofCalc'];
				}
			}
		}
		
		foreach ($temp as $conta => $anos){
			$temp_ret = [];
			$temp_ret['conta'] = $conta;
			$temp_ret['desc'] = isset($this->_planoContas[$conta]) ? $this->_planoContas[$conta] : '';
			
			foreach ($anos as $ano => $meses){
				foreach ($meses as $mes => $valor){
					$temp_ret[$ano.$mes.'base'] = $valor['base'];
					$temp_ret[$ano.$mes.'pis'] = $valor['pis'];
					$temp_ret[$ano.$mes.'cof'] = $valor['cof'];
				}
			}
			$ret[] = $temp_ret;
		}
		
		
		//print_r($ret);die();
		return $ret;
	}
	
	//--------------------------------------------------------- RESUMO ANUAL ------------------------------------
	private function montaTabelaAnual(){
		$ret = '';
		
		$this->calculaResumoAnual();
		//print_r($this->_resumo_anual);die();
		$dados = $this->getDadosAnual();
		$this->_dadosExcel['anual'] = $dados;
		
		$param = [];
		$param['titulo'] = "Resumo Anual";
		$param['cancela'] = true;
		$relatorio = new relatorio01($param);
		
		//$relatorio->addColuna(array('campo' => 'conta'	, 'etiqueta' => 'Conta'		,'tipo' => 'T', 'width' => 130, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc'	, 'etiqueta' => 'Descrição'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
		foreach ($this->_anos as $ano){
			$relatorio->addColuna(array('campo' => $ano.'_valor', 'etiqueta' => 'Base de Cálculo '.$ano		,'tipo' => 'V', 'width' => 200, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => $ano.'_pis'	, 'etiqueta' => 'PIS a Recuperar '.$ano		,'tipo' => 'V', 'width' => 200, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => $ano.'_cof'	, 'etiqueta' => 'COFINS a Recuperar'.$ano	,'tipo' => 'V', 'width' => 200, 'posicao' => 'D'));
			$relatorio->addColuna(array('campo' => $ano.'_total', 'etiqueta' => 'Total a Recuperar '.$ano	,'tipo' => 'V', 'width' => 200, 'posicao' => 'D'));
		}
		
		$botao = [];
		$botao['onclick']= 'window.open(\''.$this->_linkExcel.'\')';
		$botao['texto']	= 'Insumos';
		$botao['id'] = 'bt_excel';
		$relatorio->addBotao($botao);
		
		$relatorio->setDados($dados);
		//$relatorio->setToExcel(true,$this->_path.DIRECTORY_SEPARATOR.'Resumo_anual', true);
		$relatorio->setToExcel(false);
		
		$ret .= $relatorio;
		
		return $ret;
	}
		
	private function calculaResumoAnual(){
		foreach ($this->_I250 as $I250){
			$conta = $I250['conta'];
			$ano = $I250['ano'];
//print_r($I250);
			if(!isset($this->_resumo_anual[$ano][$conta])){
				$this->_resumo_anual[$ano][$conta]['valor'] 	= 0;
				$this->_resumo_anual[$ano][$conta]['pisCalc'] 	= 0;
				$this->_resumo_anual[$ano][$conta]['cofCalc'] 	= 0;
			}
				
			$this->_resumo_anual[$ano][$conta]['valor'] 	= round((float)$this->_resumo_anual[$ano][$conta]['valor']   + (float)$I250['vl'], 2);
			$this->_resumo_anual[$ano][$conta]['pisCalc'] 	= round((float)$this->_resumo_anual[$ano][$conta]['pisCalc'] + (float)$I250['pisCalc'], 2);
			$this->_resumo_anual[$ano][$conta]['cofCalc'] 	= round((float)$this->_resumo_anual[$ano][$conta]['cofCalc'] + (float)$I250['cofCalc'], 2);
		}
	}
	
	private function getDadosAnual(){
		$ret = [];

		foreach ($this->_resumo_anual as $ano => $resumo){
			foreach ($resumo as $conta => $valores){
				$t = [];
				$t['conta'] = $conta;
				$t['desc'] = isset($this->_planoContas[$conta]) ? $this->_planoContas[$conta] : '';
				
				$t[$ano.'_valor'] = $valores['valor'];
				$t[$ano.'_pis'] = $valores['pisCalc'];
				$t[$ano.'_cof'] = $valores['cofCalc'];
				
				$t[$ano.'_total'] = $valores['pisCalc'] + $valores['cofCalc'];
				
				$ret[] = $t;
			}
		}

		return $ret;
	}
	
	//----------------------------------------------------------------- EXCEL ----------------------------------------
	private function geraExcel(){
		$this->_excel = new excel_marpa($this->_excelArquivo);
		
		$this->_indicesWS = $this->criaSheets();
		
		$this->geraAnual();
		$this->geraMensal();
		//$this->geraMensalResult();
		$this->geraContasAno();
		
		if(count($this->_anos) > 1){
			$this->geraAnualTotal();
		}
		
		$this->_excel->grava();
	}
	
	private function geraContasAno(){
		$this->_excel->setWSAtiva($this->_indicesWS['CA']);
		
		$cab = [];
		$campos = [];
		$tipos = [];
		$cab[] = 'Conta';
		$campos[] = 'desc';
		$tipos[] = '';
		
		foreach ($this->_anos as $ano){
			$cab[] = $ano;
			$campos[] = $ano;
			$tipos[] = 'V';
		}
		$cab[] = 'Total Geral';
		$campos[] = 'total';
		$tipos[] = 'V';
		
		$cab[] = 'Total a Recuperar';
		$campos[] = 'recuperar';
		$tipos[] = 'V';
	
		$dados = $this->getContasAno();
		$this->_excel->setDados($cab, $dados, $campos, $tipos);
		
		$totais = $this->getTotaisContasAno($dados);
		$this->_excel->setTotal($totais);
		
		$this->_excel->setNomeEmpresa($this->_razao);
		
	}
	
	private function getContasAno(){
		$ret = [];
		$temp = [];
//print_r($this->_dadosExcel);
		
		foreach ($this->_dadosExcel['anual'] as $dado){
			$conta = $dado['conta'];
			if(!isset($temp[$conta])){
				$temp[$conta]['desc'] = $dado['desc'];
				foreach ($this->_anos as $ano){
					$temp[$conta][$ano] = 0;
				}
				$temp[$conta]['total'] = 0;
				$temp[$conta]['recuperar'] = 0;
			}
			
			foreach ($this->_anos as $ano){
				$temp[$conta][$ano] += isset($dado[$ano.'_valor']) ? $dado[$ano.'_valor'] : 0;
				$temp[$conta]['total'] += isset($dado[$ano.'_valor']) ? $dado[$ano.'_valor'] : 0;
				$temp[$conta]['recuperar'] += isset($dado[$ano.'_valor']) ? ($dado[$ano.'_pis'] + $dado[$ano.'_cof']) : 0;
			}
		}
		
		if(count($temp) > 0){
			foreach ($temp as $t){
				$ret[] = $t;
			}
		}
		
//print_r($ret);
		return $ret;
	}
	
	private function getTotaisContasAno($dados){
		$ret = [];
		
		foreach ($dados as $dado){
			foreach ($dado as $key => $valor){
				$ret[$key] = 0;
			}
		}
		
		foreach ($dados as $dado){
			foreach ($dado as $key => $valor){
				if($key != 'desc'){
					$ret[$key] += $valor;
				}
			}
		}
		
		$ret['desc'] = 'Total Geral';
		
		return $ret;
	}
	
	private function geraMensalResult(){
		foreach ($this->_indicesWS['TR'] as $ano => $ind){
			$this->_excel->setWSAtiva($ind);
			
			$cab = ['Competência', 'Base de cálculo', 'Total a recuperar'];
			$dados = $this->_dadosExcel['mensal'];
//print_r($this->_dadosExcel['mensal']);
			$campos = ['comp', $ano.'base', $ano.'total'];
			$tipos = ['', 'V', 'V', 'V'];
			$this->_excel->setDados($cab, $dados, $campos, $tipos);
			
			$totais = $this->getTotaisMensaisResult($dados);
			$this->_excel->setTotal($totais);
			
			$this->_excel->setNomeEmpresa($this->_razao);
		}
	}
	
	private function geraMensal(){
		$mesesAbr = ['01'=>'jan', '02'=>'fev', '03'=>'mar', '04'=>'abr', '05'=>'mai', '06'=>'jun', '07'=>'jul', '08'=>'ago', '09'=>'set', '10'=>'out', '11'=>'nov', '12'=>'dez' ];
		
		foreach ($this->_indicesWS['TM'] as $ano => $ind){
			$cab = [];
			$campos = [];
			$tipos = [];
			
			$this->_excel->setWSAtiva($ind);
			$cab[] = 'Conta';
			$campos[] = 'desc';
			$tipos[] = '';
			
			$dadosMistos = $this->getBaseMensal($ano);
			
			$a = $ano - 2000;
			foreach ($dadosMistos['meses'] as $k => $mes){
				if($k != 'total_'){
					$cab[] = 'BC '.$mesesAbr[$k].'/'.$a;
					$campos[] = $mes.'base';
					$tipos[] = 'V';
				}else{
					$cab[] = $mes;
					$campos[] = $k.'base';
					$tipos[] = 'V';}
			}
//print_r($campos);
			$this->_excel->setDados($cab, $dadosMistos['valores'], $campos, $tipos);
			$totais = $this->getTotaisMensais($dadosMistos['valores']);
			$this->_excel->setTotal($totais);
			
			$this->_excel->setNomeEmpresa($this->_razao);
		}
	}
	
	private function geraAnual(){
		foreach ($this->_indicesWS['TA'] as $ano => $ind){

			$this->_excel->setWSAtiva($ind);
			
			$cab = ['Conta', 'Base de cálculo', 'PIS a recuperar', 'COFINS a recuperar', 'Total a recuperar'];
			$dados = $this->getDadosExcelTotalAnual($ano);

			$campos = ['desc', 'valor', 'pis', 'cof', 'total'];
			$tipos = ['', 'V', 'V', 'V', 'V'];
			$this->_excel->setDados($cab, $dados, $campos, $tipos);
			
			$totais = $this->getTotaisAnual($dados);
			$this->_excel->setTotal($totais);
			
			$this->_excel->setNomeEmpresa($this->_razao);
		}
	}
	
	private function geraAnualTotal(){
		foreach ($this->_indicesWS['T'] as $ind){
			
			$this->_excel->setWSAtiva($ind);
			
			$cab = ['Conta', 'Soma Base de cálculo', 'Soma PIS a recuperar', 'Soma COFINS a recuperar', 'Soma Total a recuperar'];
			$dados = $this->getDadosExcelTotalGeral();
			
			$campos = ['desc', 'valor', 'pis', 'cof', 'total'];
			$tipos = ['', 'V', 'V', 'V', 'V'];
			$this->_excel->setDados($cab, $dados, $campos, $tipos);
			
			$totais = $this->getTotaisAnual($dados);
			$this->_excel->setTotal($totais);
			
			$this->_excel->setNomeEmpresa($this->_razao);
		}
	}
	
	private function getBaseMensal($ano){
		$ret = [];
		$mesesValor = [];
		$contasValor = [];
		$temp = [];
		
		$meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ];
		
		foreach ($this->_dadosExcel['mensalBase'] as $dado){
			$conta = $dado['conta'];
			if(!isset($temp[$conta])){
				$temp[$conta]['desc'] = $dado['desc'];
				foreach ($meses as $mes){
					$temp[$conta][$mes.'base'] = 0;
					$temp[$conta][$mes.'pis'] = 0;
					$temp[$conta][$mes.'cof'] = 0;
				}
			}
			
			foreach ($meses as $mes){
				if(isset($dado[$ano.$mes.'base']) && $dado[$ano.$mes.'base'] > 0){
					$mesesValor[$mes] = $mes;
					$contasValor[$conta] = $conta;
					
					$temp[$conta][$mes.'base'] = $dado[$ano.$mes.'base'];
					$temp[$conta][$mes.'pis'] = $dado[$ano.$mes.'pis'];
					$temp[$conta][$mes.'cof'] = $dado[$ano.$mes.'cof'];
				}
			}
		}
		
		ksort($contasValor);
		ksort($mesesValor);

		foreach ($contasValor as $conta){
			$t = [];
			$t['desc'] = $temp[$conta]['desc'];
			$t['total_base'] = 0;
			$t['total_pis'] = 0;
			$t['total_cof'] = 0;
			foreach ($mesesValor as $mes){
				if(!isset($temp[$conta][$mes.'base'])){
					$t[$mes.'base'] = 0;
					$t[$mes.'pis'] = 0;
					$t[$mes.'cof'] = 0;
				}else{
					$t[$mes.'base'] = $temp[$conta][$mes.'base'];
					$t[$mes.'pis'] = $temp[$conta][$mes.'pis'];
					$t[$mes.'cof'] = $temp[$conta][$mes.'cof'];

					$t['total_base'] += $temp[$conta][$mes.'base'];
					$t['total_pis'] += $temp[$conta][$mes.'pis'];
					$t['total_cof'] += $temp[$conta][$mes.'cof'];
				}
			}
			$ret['valores'][] = $t;
		}
		
		$mesesValor['total_'] = 'Total Geral';
		$ret['meses'] = $mesesValor;
		
//print_r($ret);
		return $ret;
	}
	
	private function getTotaisAnual($dados){
		$ret = ['desc' 	=> 'Total Geral',
				'valor' => 0,
				'pis'	=> 0,
				'cof' 	=> 0,
				'total' => 0
		];
		
		foreach ($dados as $dado){
			$ret['valor'] 	+= $dado['valor'];
			$ret['pis'] 	+= $dado['pis'];
			$ret['cof'] 	+= $dado['cof'];
			$ret['total'] 	+= $dado['total'];
		}
		
		return $ret;
	}
	
	private function getTotaisMensais($dados){
		$ret = [];
		
		foreach ($dados as $dado){
			foreach ($dado as $key => $valor){
				$ret[$key] = 0;
			}
		}
		
		foreach ($dados as $dado){
			foreach ($dado as $key => $valor){
				if($key != 'conta' && $key != 'desc' ){
					$ret[$key] += $valor;
				}
			}
		}
		$ret['desc'] = 'Total Geral';
		
		return $ret;
	}
	
	private function getDadosExcelTotalGeral(){
		$ret = [];
		$temp = [];
		
		foreach ($this->_dadosExcel['anual'] as $dado){
			$conta = $dado['conta'];
			if(!isset($temp[$conta])){
				$temp[$conta] = ['desc' 	=> $dado['desc'],
						'valor' => 0,
						'pis'	=> 0,
						'cof' 	=> 0,
						'total' => 0
				];
			}
			foreach ($this->_anos as $ano){
				if(isset($dado[$ano.'_valor'])){
					$temp[$conta]['valor'] += $dado[$ano.'_valor'];
					$temp[$conta]['pis']   += $dado[$ano.'_pis'];
					$temp[$conta]['cof']   += $dado[$ano.'_cof'];
					$temp[$conta]['total'] += $dado[$ano.'_total'];
				}
			}
		}
		
		
		foreach ($temp as $dados){
			$ret[] = $dados;
		}
		return $ret;
	}
	
	private function getDadosExcelTotalAnual($ano){
		$ret = [];
//print_r($this->_dadosExcel['anual']);
		foreach ($this->_dadosExcel['anual'] as $dados){
			if(isset($dados[$ano.'_valor'])){
				$temp = [];
				$temp['desc'] = $dados['desc'];
				$temp['valor'] = $dados[$ano.'_valor'];
				$temp['pis'] = $dados[$ano.'_pis'];
				$temp['cof'] = $dados[$ano.'_cof'];
				$temp['total'] = $dados[$ano.'_total'];
				
				$ret[] = $temp;
			}
		}
		return $ret;
	}
	
	private function getTotaisMensaisResult($dados){
		$ret = [];
//print_r($dados);
		
		foreach ($dados as $dado){
			foreach ($dado as $key => $valor){
				$ret[$key] = 0;
			}
		}
		
		foreach ($dados as $dado){
			foreach ($dado as $key => $valor){
				if($key != 'comp'){
					$ret[$key] += $valor;
				}
			}
		}
		
		$ret['comp'] = 'Total';
		
		return $ret;
	}
	
	private function criaSheets(){
		$this->_indicesWS = [];
		$variosAnos = count($this->_anos) > 1 ? true : false;
		$sessao = 0;
		
		foreach ($this->_anos as $ano){
			if($sessao == 0){
				$this->_excel->setWSAtiva($sessao);
				$this->_excel->setTituloWS($ano.' ABERTO MENSAL');
			}else{
				$this->_excel->addWorksheet($sessao, $ano.' ABERTO MENSAL');
			}
			$this->_indicesWS['TM'][$ano] = $sessao;
			$sessao++;
		}
/*/
		foreach ($this->_anos as $ano){
			if($sessao == 0){
				$this->_excel->setWSAtiva($sessao);
				$this->_excel->setTituloWS($ano.' TOTAL A RECUPERAR MÊS');
			}else{
				$this->_excel->addWorksheet($sessao, $ano.' TOTAL A RECUPERAR MÊS');
			}
			$this->_indicesWS['TR'][$ano] = $sessao;
			$sessao++;
		}
/*/		
		
		$this->_excel->addWorksheet($sessao, 'Contas por ANO');
		$this->_indicesWS['CA'] = $sessao;
		$sessao++;
		
		foreach ($this->_anos as $ano){
			$titulo = $variosAnos ? '' : ' TOTAL ANUAL';
			if($sessao == 0){
				$this->_excel->setWSAtiva($sessao);
				$this->_excel->setTituloWS($ano.$titulo);
			}else{
				$this->_excel->addWorksheet($sessao, $ano.$titulo);
			}
			$this->_indicesWS['TA'][$ano] = $sessao;
			$sessao++;
		}
		
		if($variosAnos){
			$this->_excel->addWorksheet($sessao, 'TOTAL');
			$this->_indicesWS['T'][$ano] = $sessao;
		}
		
		
//print_r($this->_indicesWS);
		
		return $this->_indicesWS;
	}
	
	//------------------------------------------------------------ UTEIS --------------------------------------------------------
	private function getPlanoContas(){
		$I050 = new insumos_i050($this->_path, $this->_anos, 'ecd');
		$plano = $I050->getDados();
		unset($I050);
//print_r($plano);die();
		
		if(count($plano) > 0){
			foreach ($plano as $conta){
				$this->_planoContas[$conta['conta']] = $conta['desc'];
			}
		}
		
//print_r($this->_planoContas);
	}

	
	private function getContasFora($cnpj){
		$sql = "SELECT codigo FROM mgt_insumos_plano_contas WHERE cnpj = '$cnpj' AND status != 'S'";
//echo "$sql <br>\n";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_contasFora[] = $row['codigo'];
			}
		}
		
//print_r($this->_contasFora);
	}
	
	private function getNomeEmpresa($cnpj){
		$ret = '';
		
		$sql = "SELECT razao FROM mgt_insumos WHERE id = '$cnpj'";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
		
		return $ret;
	}
}