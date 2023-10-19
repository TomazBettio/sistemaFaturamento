<?php
/*
 * Data Criacao: 07/07/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Resgata dados I075
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);


class processa_insumos{
	
	private $_path;
	
	//Anos disponiveis
	private $_anos = [];
	
	//Plano de contas
	private $_planoContas = [];
	
	//Plano contas KEY Conta
	private $_planoConta;
	
	//Plano contas KEY COD
	private $_planoCod;
	
	private $_I250 = [];
	
	private $_EFD = [];
	
	//I075
	private $_I075 = [];
	
	//Percentual PIS
	private $_percentPis;
	
	//Percentual Cofins
	private $_percentCofins;
	
	private $_letras = [];
	
	//Contas que não devem ser analisadas
	private $_contas_fora = [];
	
	//Produtos - bloco 0200
	private $_P200 = [];
	
	//Blocos F
	private $_blocoF = [];
	
	//Resultado
	private $_resultado = [];
	
	//nome do cliente
	private $_razaoSocial = '';
	
	//Razao social utilizada no nome dos arquivos
	private $_arquivoRS;
	
	public function __construct($dir, $cnpj){
		$this->_path = $dir;
		
		$this->_percentCofins = 0.076;
		$this->_percentPis = 0.0165;
		
//echo "Diretorio: $dir <br>\n";

		$this->getAnos();
		
		$this->_letras = range('A', 'Z');
		$this->_letras[] = '&';
		$this->_letras[] = 'º';
		$this->_letras[] = '-';
		$this->_letras[] = ':';
		$this->_letras[] = '/';
		$this->_letras[] = '\\';
		$this->_letras[] = '.';
		$this->_letras[] = ')';
		$this->_letras[] = '(';
		$this->_letras[] = 'Ã';
		$this->_letras[] = 'é';
				
		//$this->_contas_fora = ['(-)','(+)','INSS','FGTS','PIS','COFINS','IRRF','IRPJ','VENDAS','SALARIO','IPVA','ICMS','LABORE','IOF','FERIAS','CSLL','HORAS EXTRAS'];
		$this->getContasFora($cnpj);
		
		$this->_razaoSocial = $this->getNomeEmpresa($cnpj);
		$this->_arquivoRS = str_replace(' ', '_', $this->_razaoSocial);
		$this->_arquivoRS = str_replace('/', '_', $this->_arquivoRS);
		$this->_arquivoRS = substr($this->_arquivoRS, 0, 15);
		
	}
	
	public function processa($cnpj){
		
		//Cadastro de produtos
		log::gravaLog($cnpj.'_log', 'Inicio - Cadastro Produtos');
		$P200 = new insumos_0200($this->_path, $this->_anos, 'efd');
		$this->_P200 = $P200->getDados();
		unset($P200);
		log::gravaLog($cnpj.'_log', 'Final - Cadastro Produtos');
//print_r($this->_P200);die();

		log::gravaLog($cnpj.'_log', 'Inicio - I075');
		$I075 = new insumos_i075($this->_path, $this->_anos, 'ecd');
		$this->_I075 = $I075->getDados();
		unset($I075);
		log::gravaLog($cnpj.'_log', 'Final - I075');
//print_r($this->_I075);die();
		
//echo "I050\n";
		log::gravaLog($cnpj.'_log', 'Inicio - I050');
		$I050 = new insumos_i050($this->_path, $this->_anos, 'ecd');
		$this->_planoContas = $I050->getDados();
		unset($I050);
		log::gravaLog($cnpj.'_log', 'Final - I050');
//print_r($this->_planoContas);die();
		
		log::gravaLog($cnpj.'_log', 'Inicio - ajustaPlanoConta');
		$this->ajustaPlanoConta();
		log::gravaLog($cnpj.'_log', 'Final - ajustaPlanoConta');
		
		log::gravaLog($cnpj.'_log', 'Inicio - ajustaPlanoCod');
		$this->ajustaPlanoCod();
		log::gravaLog($cnpj.'_log', 'Final - ajustaPlanoCod');
//print_r($this->_planoConta);

		log::gravaLog($cnpj.'_log', 'Inicio - I250');
		$I250 = new insumos_i250($this->_path, $this->_anos, 'ECD');
		$this->_I250 = $I250->getDados();
		unset($I250);
		log::gravaLog($cnpj.'_log', 'Final - I250');
//print_r($this->_I250);die();

		log::gravaLog($cnpj.'_log', 'Inicio - getNotasEFD');
		$this->getNotasEFD();
		log::gravaLog($cnpj.'_log', 'Final - getNotasEFD');
		
		log::gravaLog($cnpj.'_log', 'Inicio - getBlocoF');
		$this->getBlocoF();
		log::gravaLog($cnpj.'_log', 'Final - getBlocoF');
		
		log::gravaLog($cnpj.'_log', 'Inicio - comparaNotas');
		$this->_EFD_copia = $this->_EFD;
		$this->comparaNotas();
		log::gravaLog($cnpj.'_log', 'Final - comparaNotas');
		
		//Procura C500 pois podem estar em dias diferentes
		log::gravaLog($cnpj.'_log', 'Inicio - procuraForaData');
		$this->procuraForaData('C500');
		unset($this->_EFD_copia);
		log::gravaLog($cnpj.'_log', 'Final - procuraForaData');
		
//print_r($this->_EFD);
//print_r($this->_resultado);die();
		//echo "Vai gerar conferencia<br>\n";
		log::gravaLog($cnpj.'_log', 'Inicio - geraExcelConferencia');
		$this->geraExcelConferencia();
		log::gravaLog($cnpj.'_log', 'Final - geraExcelConferencia');
	}
	
	private function procuraForaData($bloco){
		foreach ($this->_EFD as $data => $efds){
			foreach ($efds as $key => $efd){
				if($efd['bloco'] == $bloco && (!isset($efd['encontrado']) || $efd['encontrado'] == 'N')){
					foreach ($this->_resultado as $k => $i250){
						if($efd['vl'] == $i250['vl'] && $efd['nota'] == $i250['nota']){
							$this->_EFD[$data][$key]['encontrado'] = 'S';
							unset($this->_resultado[$k]);
						}
					}
				}
			}
		}
//print_r($this->_resultado);
	}
	
	private function getBlocoF(){
		$F100 = new insumos_f100($this->_path, $this->_anos, 'efd');
		$dados = $F100->getDados();
		unset($F100);
		$this->ajustaEFDdata($dados);
		
		$F120 = new insumos_f120($this->_path, $this->_anos, 'efd');
		$dados = $F120->getDados();
		unset($F120);
		$this->ajustaEFDdata($dados);
		
		$F130 = new insumos_f130($this->_path, $this->_anos, 'efd');
		$dados = $F130->getDados();
		unset($F130);
		$this->ajustaEFDdata($dados);
		
		$F150 = new insumos_f150($this->_path, $this->_anos, 'efd');
		$dados = $F150->getDados();
		unset($F150);
		$this->ajustaEFDdata($dados);
		
//print_r($this->_blocoF);
	}
	
	//------------------------------------------------------- COMPARA --------------------------

	private function comparaNotas(){
		foreach ($this->_I250 as $data => $I250){
			foreach ($I250 as $k => $n){
				$ret = $this->compara($data, $n);
//print_r($this->_I250[$data][$k]);die();
				$this->_I250[$data][$k]['pis'] = $ret['pis'];
				$this->_I250[$data][$k]['cof'] = $ret['cof'];
				
				$this->_I250[$data][$k]['pisCalc'] = $ret['pisCalc'];
				$this->_I250[$data][$k]['cofCalc'] = $ret['cofCalc'];
				
				if(!isset($this->_I250[$data][$k]['nota'])){
					$this->_I250[$data][$k]['nota'] = isset($ret['nota']) ? $ret['nota'] : '';
				}
				
				if($ret['pis'] == 0 && $ret['cof'] == 0){
					$this->_resultado[] = $this->_I250[$data][$k];
				}

				if(count($ret['c170']) > 0){
					foreach ($ret['c170'] as $c170){
						if($c170['pis'] == 0 && $c170['cof'] == 0){
							$this->_resultado[] = $c170;
						}
					}
				}
			}
		}
	}
	
	private function procuraC170($nota, $data, $n){
		$ret = [];
		
		foreach ($this->_EFD_copia[$data] as $k => $efd){
			if(!isset($this->_EFD_copia[$data][$k]['proc'])){
				if($efd['nota'] == $nota && $efd['bloco'] == 'C170'){
					if($efd['pis'] == 0 || $efd['cof'] == 0){
//print_r($n);print_r($efd);die();
//print_r($this->_P200);die();
						$temp = [];
						$temp['bloco'] 		= 'C170';
						$temp['conta'] 		= $n['conta'];
						$temp['data'] 		= $n['data'];
						$temp['lanc'] 		= $n['lanc'];
						$temp['vl'] 		= $efd['vl'];
						$temp['DC'] 		= $n['DC'];
						$temp['hist'] 		= $n['hist'];
						$desc = isset($this->_P200[$efd['prod']]) ? $this->_P200[$efd['prod']]['desc'].'-'.$this->_P200[$efd['prod']]['NCM'] : 'Produto nao encontrado';
						$temp['I075'] 		= $efd['seq'].'-'.$efd['prod'].'-'.$desc;
						$temp['pis'] 		= 0;
						$temp['cof'] 		= 0;
						$temp['pisCalc'] 	= round($efd['vl'] * $this->_percentPis, 2);
						$temp['cofCalc'] 	= round($efd['vl'] * $this->_percentCofins, 2);
						$temp['nota'] 		= $efd['nota'];

						$ret[] = $temp;
						$this->_EFD_copia[$data][$k]['proc'] = 'S';
					}
				}
			}
		}
		
		return $ret;
	}
	
	private function compara($data, $n){
		$ret = [
				'pis' => 0,
				'cof' => 0,
				'pisCalc' => 0,
				'cofCalc' => 0,
				'nota' => 0,
				'c170' => []
		];
//print_r($this->_EFD_copia);
//echo "Data: $data <br>\n";
//print_r($this->_EFD_copia);
		if(isset($this->_EFD_copia[$data])){

			foreach ($this->_EFD_copia[$data] as $k => $efd){
				if(!isset($this->_EFD_copia[$data][$k]['proc'])){
					if($efd['vl'] == $n['vl']){
						$ret['pis'] = $efd['pis'];
						$ret['cof'] = $efd['cof'];
						$ret['nota'] = $efd['nota'];
						
						if($efd['bloco'] == 'C100'){
							$ret['c170'] = $this->procuraC170($efd['nota'], $data, $n);
						}
						$this->_EFD_copia[$data][$k]['proc'] = 'S';
						//Marca a nota como encontrada
						$this->_EFD[$data][$k]['encontrado'] = 'S';
						break;
					}
				}
			
			}
			
			//Se ainda existe o registro, vai pesquisar pelo nr da nota
			if($ret['pis'] == 0 && $ret['cof'] == 0 && empty($ret['nota'])){
				if(!isset($n['nota'])){
					$nota = $this->pesquisaNrNota($n['hist']);
				}else{
					$nota = $n['nota'];
				}
				$ret['nota'] = $nota;
				if(!empty($nota)){
					$pis = 0;
					$cof = 0;
					foreach ($this->_EFD_copia[$data] as $k => $efd){
						
						if($efd['nota'] == $nota){
							if(empty($efd['pis'])){
								$efd['pis'] = 0;
							}
							if(empty($efd['cof'])){
								$efd['cof'] = 0;
							}
							$pis += $efd['pis'];
							$cof += $efd['cof'];
							//unset($this->_EFD_copia[$data][$k]);
							//Marca a nota como encontrada
							$this->_EFD[$data][$k]['encontrado'] = 'S';
							
							if($efd['bloco'] == 'C100'){
								$ret['c170'] = $this->procuraC170($efd['nota'], $data, $n);
							}
							
						}
						
					}
					$ret['pis'] = $pis;
					$ret['cof'] = $cof;
				}
			}
		}
		//Se ainda existe o registro, quer dizer que não encontrou, então calcula pis/cofins
		if($ret['pis'] == 0 && $ret['cof'] == 0){
			$valor = str_replace(',', '.', $n['vl']);
//echo "calculando pis/cofins ".$this->_percentPis." - ".$this->_percentCofins." - ".$valor."<br>\n";
			$ret['pisCalc'] = round($valor * $this->_percentPis, 2);
			$ret['cofCalc'] = round($valor * $this->_percentCofins, 2);
			$ret['c170'] = [];
		}
		
		return $ret;
	}
	
	private function pesquisaNrNota($historico){
		$novo = trim(str_replace($this->_letras, '', $historico));
		//$novo = preg_replace("/[^0-9]/", "", $historico.' 99');
		//echo "novo: $novo - $historico<br>\n";
		
		return $novo;
	}
	
	
	//------------------------------------------------------- EFD ------------------------------
	
	private function getNotasEFD(){
		$A100 = new insumos_a100($this->_path, $this->_anos, 'EFD', false);
		$a100_dados = $A100->getDados();
		unset($A100);
		$this->ajustaEFDdata($a100_dados);
//print_r($a100_dados);die();
		
		$C100 = new insumos_c100($this->_path, $this->_anos, 'EFD');
		$c100_dados = $C100->getDados();
		unset($C100);
		$this->ajustaEFDdata($c100_dados);
//print_r($c100_dados);die();
		
		$D100 = new insumos_d100($this->_path, $this->_anos, 'EFD');
		$d100_dados = $D100->getDados();
		unset($D100);
		$this->ajustaEFDdata($d100_dados);
//print_r($this->_D100);die();
		
		$C500 = new insumos_c500($this->_path, $this->_anos, 'EFD');
		$c500_dados = $C500->getDados();
		unset($C500);
//print_r($this->_C500);die();
		$this->ajustaEFDdata($c500_dados);
		
		ksort($this->_EFD);

//print_r($this->_EFD);die();
		
	}
	
	private function ajustaEFDdata($dados){
		if(count($dados) > 0){
			foreach ($dados as $efd){
				$data = $efd['data'];
				$data = substr($data, 4,4).substr($data, 2,2).substr($data, 0,2);
				unset($efd['data']);
				$this->_EFD[$data][] = $efd;
			}
		}
	}
	
	private function ajustaBlocoF($dados){
		if(count($dados) > 0){
			foreach ($dados as $efd){
				$data = $efd['data'];
				$data = substr($data, 4,4).substr($data, 2,2).substr($data, 0,2);
				unset($efd['data']);
				$this->_blocoF[$data][] = $efd;
			}
		}
	}
	//------------------------------------------------------- Plano de contas ------------------------------
	public function getPlanoContas(){
		$I075 = new insumos_i075($this->_path, $this->_anos, 'ecd');
		$this->_I075 = $I075->getDados();
		unset($I075);
		
//echo "I050\n";
		$I050 = new insumos_i050($this->_path, $this->_anos, 'ecd');
		$this->_planoContas = $I050->getDados();
		unset($I050);
//echo "FIM I050\n";
		
//echo "Ini Ajuste Contas\n";
		$this->ajustaPlanoConta();
		$this->ajustaPlanoCod();
//echo "Fim  Ajuste Contas\n";
		
//	print_r($this->_planoCod);
//print_r($this->_planoConta);
//print_r($this->_planoContas);
		return $this->_planoContas;
	}
	
	private function ajustaPlanoConta(){
		$this->_planoConta = [];
		foreach ($this->_planoContas as $plano){
			if(!isset($this->_planoConta[$plano['conta']])){
				$this->_planoConta[$plano['conta']] = $plano;
			}
		}
	}
	
	private function ajustaPlanoCod(){
		$this->_planoCod = [];
		foreach ($this->_planoContas as $plano){
			if(!isset($this->_planoCod[$plano['cod']])){
				$this->_planoCod[$plano['cod']] = $plano;
			}
		}
	}
	
	//-------------------------------------------------------- Uteis ----------------------------------------
	private function getAnos(){
		$diretorio = dir($this->_path);
		
		while($arquivo = $diretorio->read()){
			if($arquivo != '.' && $arquivo != '..' && $arquivo >= '2000' && $arquivo <= '2500'){
				$this->_anos[] = $arquivo;
			}
		}
	}
	
	//-------------------------------------------------------- Excel Conferencia -----------------------------
	private function geraExcelConferencia(){
		//	$excel->addWorksheet('pc','Plano de Contas');
		/*/
		 $excel = new excel01($this->_path.DIRECTORY_SEPARATOR.'conferencia_insumos_notas.xlsx');
		 $excel->addWorksheet(0, 'Notas');
		 $dados = $this->separaEFD();
		 $cab = ['Data','Nota','Valor','PIS','COFINS','Bloco','Encontrado'];
		 $campos = ['0','1','2','3','4','5','6'];
		 $tipos = ['D','','','','','',''];
		 $excel->setDados($cab, $dados, $campos, $tipos);
		 $excel->grava();
		 unset($excel);
		 
		 $excel = new excel01($this->_path.DIRECTORY_SEPARATOR.'conferencia_insumos_250_ignoradas.xlsx');
		 $excel->addWorksheet(0,'I250 Ignoradas');
		 $dados = $this->separaI250('IG');
		 $cab = ['Conta','Dia','Lançamento','Valor','C/D','Historico','PIS','COFINS','PIS Calc', 'COFINS Calc'];
		 $campos = ['0','1','2','3','4','5','6','7','8','9'];
		 $tipos = ['','D','','','','','','','',''];
		 $excel->setDados($cab, $dados, $campos, $tipos);
		 $excel->grava();
		 unset($excel);
		 
		 $excel = new excel01($this->_path.DIRECTORY_SEPARATOR.'conferencia_insumos_250.xlsx');
		 $excel->addWorksheet(0, 'ECD');
		 $dados = $this->separaI250('CO');
		 //$cab = ['Conta','Dia','Lançamento','Valor','C/D','Historico','PIS','COFINS','PIS Calc', 'COFINS Calc'];
		 $cab 	= ['Bloco','COD_CTA'	,'Lancamento','Conta Sintética','Desc Conta','Nr. Conta','Vl Conta'	,'C/D'	,'Nota'	,'Data Conta'	,'Data Base','Hist. I250','Hist. I075','PIS','COFINS'	,'PIS Marpa','COFINS Marpa'];
		 $campos = ['bloco','codconta'	,'lanc'		 ,'reduzida'	   ,'descConta'	,'conta'	,'vl'	    ,'DC'	,'nota'	,'data'			,'base'		, 'hist'	 ,'hist075'	  ,'pis','cof'		,'pisCalc'	,'cofCalc'];
		 $tipos  = [''	  ,''			,''			 ,''			   ,''			,''			,''			,''		,''		,'D'];
		 $excel->setDados($cab, $dados, $campos, $tipos);
		 $excel->grava();
		 unset($excel);
		 /*/
		//$dados = $this->separaI250('CO', true);
		$dados = $this->separaI250novo('CO', true);
		$cab 	= ['Bloco','COD_CTA'	,'Lancamento','Conta Sintética','Desc Conta','Nr. Conta','Vl Conta'	,'C/D'	,'Nota'	,'Data Conta'	,'Data Base','Hist. I250','Hist. I075','PIS','COFINS'	,'PIS Marpa','COFINS Marpa','Encontrada'];
		$campos = ['bloco','codconta'	,'lanc'		 ,'reduzida'	   ,'descConta'	,'conta'	,'vl'	    ,'DC'	,'nota'	,'data'			,'base'		, 'hist'	 ,'hist075'	  ,'pis','cof'		,'pisCalc'	,'cofCalc','encontrado'];
		$tipos  = [''	  ,''			,''			 ,''			   ,''			,''			,''			,''		,''		,'D'];
		$arquivo = new insumos_excel($this->_path, $dados, $campos, $cab, $tipos);
		$arquivo->grava('I250_'.$this->_arquivoRS.'.csv');
		
//print_r($this->_EFD);
	}
	
	/**
	 * Separa os I250 em descartados e utilizados
	 *
	 * @param string $tipo
	 * @return array[]|array[][]
	 */
	private function separaI250novo($tipo, $virgula = false){
		$ret = [];
		
		if(count($this->_resultado) > 0){
			foreach ($this->_resultado as $i250){
					if($tipo == 'IG'){
						if($i250['pis'] != 0 || $i250['cof'] != 0){
							$temp = [];
							$temp['bloco'] = isset($i250['bloco']) ? $i250['bloco'] : 'I250';
							$temp['hist075'] =  !empty($i250['I075']) && isset($this->_I075[$i250['I075']]) ? $this->_I075[$i250['I075']] : $i250['I075'];
							foreach ($i250 as $k => $v){
								$temp[$k] = $v;
							}
							
							$ret[] = $temp;
						}
					}else{
						if($i250['pis'] == 0 && $i250['cof'] == 0){
							$temp = [];
							$temp['bloco'] = isset($i250['bloco']) ? $i250['bloco'] : 'I250';
							$temp['hist075'] =  !empty($i250['I075']) && isset($this->_I075[$i250['I075']]) ? $this->_I075[$i250['I075']] : $i250['I075'];
//print_r($this->_planoCod);die();
							$temp['codconta'] = $i250['conta'];
							$temp['conta'] = isset($i250['conta']) && isset($this->_planoCod[$i250['conta']]) ? $this->_planoCod[$i250['conta']]['conta'] : '';
							$temp['descConta'] = isset($i250['conta']) && isset($this->_planoCod[$i250['conta']]) ? $this->_planoCod[$i250['conta']]['desc'] : '';
							$temp['reduzida'] = isset($i250['conta']) && isset($this->_planoCod[$i250['conta']]) ? $this->_planoCod[$i250['conta']]['reduzida'] : '';
							
							$temp['hist'] = utf8_encode($i250['hist']);
							$temp['DC'] = $i250['DC'];
							$temp['data'] = substr($i250['data'], 6, 2).'/'.substr($i250['data'], 4, 2).'/'.substr($i250['data'], 0, 4);
							$temp['lanc'] = $i250['lanc'];
							
							$temp['nota'] = isset($i250['nota']) ? $i250['nota'] : '';
							
							$mult = $i250['DC'] == 'D' ? 1 : -1;
							$temp['vl'] = $i250['vl'] * $mult;
							$temp['pis'] = $i250['pis'];
							$temp['cof'] = $i250['cof'];
							$temp['pisCalc'] = $i250['pisCalc'] * $mult;
							$temp['cofCalc'] = $i250['cofCalc'] * $mult;
							
							if($virgula){
								$temp['vl'] 	= str_replace('.',',', $temp['vl']);
								$temp['pis'] 	= str_replace('.',',', $temp['pis']);
								$temp['cof'] 	= str_replace('.',',', $temp['cof']);
								$temp['pisCalc']= str_replace('.',',', $temp['pisCalc']);
								$temp['cofCalc']= str_replace('.',',', $temp['cofCalc']);
							}
							
							$temp['base'] = substr($i250['data'], 4,2).'-'.substr($i250['data'], 0,4);
							$temp['encontrado'] = isset($i250['encontrado']) ? 'S' : 'N';
							
							if(!isset($this->_contas_fora[$i250['conta']])){
								$ret[] = $temp;
							}
							//print_r($i250);
							//print_r($ret);
							
							//return $ret;
						}
				//	}
				}
			}
		}
		
		return $ret;
	}
	

	private function getContasFora($cnpj){
		$sql = "SELECT * FROM mgt_insumos_plano_contas WHERE cnpj = '$cnpj' AND status = 'N'";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_contas_fora[$row['codigo']] = $row['codigo'];
			}
		}
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