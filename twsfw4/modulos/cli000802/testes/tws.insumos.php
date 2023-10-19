<?php
/*
 * Data Criacao: 01/07/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Calculo de insumos - MGT
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);




class insumos{
	var $funcoes_publicas = array(
			'index' 	=> true,
	);
	
	//Caminho dos arquivos
	private $_path;
	
	//Anos disponiveis
	private $_anos = [];
	
	//Plano de contas
	private $_planoContas = [];
	
	//Plano contas KEY Conta
	private $_planoConta;
	
	//Plano contas KEY COD
	private $_planoCod;
	
	//Lançamentos I250
	private $_I250 = [];
	
	//I075
	private $_I075 = [];
	
	//Lancamentos C100
	private $_EFD = [];
	
	//Lancamentos EFD Copia
	private $_EFD_copia = [];
	
	//Notas encontradas
	private $_notas_encontradas = [];
	
	//Notas nao encontradas
	private $_notas_nao_encontradas = [];
	
	//Resumo Anual
	private $_resumo_anual;
	
	//Resumo mensal
	private $_resumo_mensal;
	
	//Percentual PIS
	private $_percentPis;
	
	//Percentual Cofins
	private $_percentCofins;
	
	//Indica se gera excel de conferência
	private $_conferencia;
	
	//Contas que não devem ser analisadas
	private $_contas_fora = [];
	
	private $_letras = [];
	
	public function __construct(){
		set_time_limit(0);
		$this->_percentCofins = 0.076;
		$this->_percentPis = 0.0165;
		
		$this->_conferencia = true;
		
		$this->_contas_fora = ['(-)','(+)','INSS','FGTS','PIS','COFINS','IRRF','IRPJ','VENDAS','SALARIO','IPVA','ICMS','LABORE','IOF','FERIAS','CSLL','HORAS EXTRAS'];
		
		$this->_letras = range('A', 'Z');
		$this->_letras[] = '&';
		$this->_letras[] = 'º';
	}
	
	public function index(){
		$ret = '';
		
		$this->_path = 'C:\Users\thiel\Downloads\MGT\MATOS E MELO';
		//$this->_path = 'C:\Users\thiel\Downloads\MGT\Paineiras';
		
		$this->getAnos();
		
		$I075 = new insumos_i075($this->_path, $this->_anos, 'ecd');
		$this->_I075 = $I075->getDados();
		unset($I075);
		
		echo "I050\n";
		$I050 = new insumos_i050($this->_path, $this->_anos, 'ecd');
		$this->_planoContas = $I050->getDados();
		unset($I050);
		echo "FIM I050\n";
		
		echo "Ini Ajuste Contas\n";
		$this->ajustaPlanoConta();
		$this->ajustaPlanoCod();
		echo "Fim  Ajuste Contas\n";
		
		echo "Get250\n";
		$this->getI250();
		echo "Fim 250\n";
		
		echo "EFD\n";
		$this->getNotasEFD();
		$this->_EFD_copia = $this->_EFD;
		echo "Fim Efd\n";
		
		echo "Comprara\n";
		$this->comparaNotas();
		unset($this->_EFD_copia);
		echo "Fim Compara\n";
		
		//$ret .= $this->montaTabelaAnual();
		//$ret .= $this->montaTabelaMensal();
		echo "Conf\n";
		if($this->_conferencia){
			$this->geraExcelConferencia();
		}
		echo "Fim Conf\n";
		
		return $ret;
		
	}
	
	private function montaTabelaMensal(){
		$ret = '';
		$meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ];
		
		$this->montaDadosResumoMensal();
		$dados = $this->getDadosMensal();
		
		$param = [];
		$param['programa'] = "Resumo Mensal";
		$relatorio = new relatorio01($param);
		
		$relatorio->addColuna(array('campo' => 'conta'	, 'etiqueta' => 'Conta'		,'tipo' => 'T', 'width' => 130, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc'	, 'etiqueta' => 'Descrição'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
		foreach ($this->_anos as $ano){
			foreach ($meses as $mes){
				$relatorio->addColuna(array('campo' => $ano.$mes.'pis'	, 'etiqueta' => 'PIS '.$mes.'/'.$ano	,'tipo' => 'V', 'width' => 130, 'posicao' => 'D'));
				$relatorio->addColuna(array('campo' => $ano.$mes.'cof'	, 'etiqueta' => 'COFINS '.$mes.'/'.$ano	,'tipo' => 'V', 'width' => 130, 'posicao' => 'D'));
			}
		}
		
		$relatorio->setDados($dados);
		$relatorio->setToExcel(true,$this->_path.DIRECTORY_SEPARATOR.'Resumo_mensal.xlsx', true);
		
		$ret .= $relatorio;
		
		return $ret;
	}
	
	private function montaTabelaAnual(){
		$ret = '';
		$this->montaDadosResumoAnual();
		
		$dados = $this->getDadosAnual();
		
		$param = [];
		$param['programa'] = "Resumo Anual";
		$relatorio = new relatorio01($param);
		
		$relatorio->addColuna(array('campo' => 'conta'	, 'etiqueta' => 'Conta'		,'tipo' => 'T', 'width' => 130, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'desc'	, 'etiqueta' => 'Descrição'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
		foreach ($this->_anos as $ano){
			$relatorio->addColuna(array('campo' => $ano	, 'etiqueta' => 'Ano '.$ano	,'tipo' => 'V', 'width' => 130, 'posicao' => 'D'));
		}
		
		$relatorio->setDados($dados);
		$relatorio->setToExcel(true,$this->_path.DIRECTORY_SEPARATOR.'Resumo_anual.xlsx', true);
		
		$ret .= $relatorio;
		
		return $ret;
	}
	
	private function getDadosAnual(){
		$ret = [];
		$temp = [];
		
		foreach ($this->_resumo_anual as $ano => $resumo){
			foreach ($resumo as $conta => $valor){
				$contaReal = $this->_planoCod[$conta]['conta'];
				$temp[$contaReal][$ano] = $valor;
			}
		}
		
		foreach ($temp as $t){
			$zerado = true;
			foreach ($this->_anos as $ano){
				if(isset($t[$ano]) && $t[$ano] <> 0){
					$zerado = false;
				}
			}
			if(!$zerado){
				$ret[] = $t;
			}
		}
		//print_r($ret);
		
		
		
		return $ret;
	}
	
	private function getDadosMensal(){
		$ret = [];
		$temp = [];
		
		$planoContas = $this->ajustaPlanosContas();
		
		foreach ($this->_resumo_mensal as $ano => $resumoMensal){
			foreach ($resumoMensal as $mes => $resumo){
				foreach ($resumo as $conta => $valor){
					if(isset($planoContas[$conta])){
						$temp[$conta][$ano][$mes]['pis'] = $valor['pis'];
						$temp[$conta][$ano][$mes]['cof'] = $valor['cof'];
					}
				}
			}
		}
		
		foreach ($temp as $conta => $anos){
			$temp_ret = [];
			$temp_ret['conta'] = $conta;
			
			$temp_ret['desc'] = $planoContas[$conta];
			foreach ($anos as $ano => $meses){
				foreach ($meses as $mes => $valor){
					$temp_ret[$ano.$mes.'pis'] = $valor['pis'];
					$temp_ret[$ano.$mes.'cof'] = $valor['cof'];
				}
			}
			$ret[] = $temp_ret;
		}
		
		
		//print_r($ret);die();
		return $ret;
	}
	
	private function montaDadosResumoMensal(){
		foreach ($this->_I250 as $data => $I250){
			$ano = substr($data, 0, 4);
			$mes = substr($data, 4, 2);
			foreach ($I250 as $i){
				
				$conta = $i['conta'];
				if($i['pis'] == 0 && $i['cof'] == 0){
					if(!isset($this->_resumo_mensal[$ano][$mes][$conta])){
						$this->_resumo_mensal[$ano][$mes][$conta]['pis'] = 0;
						$this->_resumo_mensal[$ano][$mes][$conta]['cof'] = 0;
					}
					$pis = $i['pisCalc'];
					$cofins = $i['cofCalc'];
					
					$this->_resumo_mensal[$ano][$mes][$conta]['pis'] = round($this->_resumo_mensal[$ano][$mes][$conta]['pis'] + $pis, 2);
					$this->_resumo_mensal[$ano][$mes][$conta]['cof'] = round($this->_resumo_mensal[$ano][$mes][$conta]['cof'] + $cofins, 2);
				}
			}
		}
		//print_r($this->_resumo_mensal);
	}
	
	private function montaDadosResumoAnual(){
		foreach ($this->_I250 as $data => $I250){
			$ano = substr($data, 0, 4);
			foreach ($I250 as $i){
				$conta = $i['conta'];
				if($i['pis'] == 0 && $i['cof'] == 0){
					if(!isset($this->_resumo_anual[$ano][$conta])){
						$this->_resumo_anual[$ano][$conta] = 0;
					}
					$mult = 1;
					if($i['DC'] == 'C'){
						$mult = -1;
					}
					$valor = round($i['vl'] * $mult,2);
					//echo "Valor $valor <br>\n";
					$this->_resumo_anual[$ano][$conta] = round($this->_resumo_anual[$ano][$conta] + $valor, 2);
				}
			}
		}
		print_r($this->_resumo_anual);
	}
	
	private function ajustaPlanosContas(){
		$ret = [];
		
		foreach ($this->_planoContas as $conta){
			if(!isset($ret[$conta[2]])){
				$inclui = true;
				$desc = $conta[1];
				
				foreach ($this->_contas_fora as $fora){
					if(strpos($desc,$fora) !== false){
						$inclui = false;
					}
				}
				
				if($inclui){
					$ret[$conta[2]] = $desc;
				}
			}
		}
		
		return $ret;
	}
	
	private function getAnos(){
		$diretorio = dir($this->_path);
		
		while($arquivo = $diretorio->read()){
			if($arquivo != '.' && $arquivo != '..' && $arquivo >= '2000' && $arquivo <= '2500'){
				$this->_anos[] = $arquivo;
			}
		}
	}
	
	private function getI250(){
		$existeResumo = $this->verificaResumo('I250');
		$existeResumo = true;
		if($existeResumo){
			foreach ($this->_anos as $ano){
				$dir = $this->_path.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'ecd';
				if(!is_dir($dir)){
					$dir = $this->_path.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'ECD';
					if(!is_dir($dir)){
						die("'Diretorio $dir não encontrado, favor revisar!");
					}
				}
				
				$arquivos = $this->getArquivos($dir);
				
				if(count($arquivos) == 0){
					die("Não constam arquivo de ECD no diretorio $dir, favor verificar!");
				}
				
				foreach ($arquivos as $arquivo){
					//echo "Lendo arquivo I250 $arquivo <br>\n";
					$linhas = $this->leituraArquivo($dir.DIRECTORY_SEPARATOR.$arquivo, 'I250');
					if(count($linhas) > 0){
						foreach ($linhas as $l){
							//$conta = $l['CONTA'];
							//$conta = isset($this->_planoContas[$contaI250]) ? $this->_planoContas[$contaI250][2] : 'SEM_CONTA';
							
							$data = $l['I200_DT'];
							$data = substr($data, 4,4).substr($data, 2,2).substr($data, 0,2);
							
							$valor		= empty($l['VALOR']) ? 0 : str_replace(',', '.', $l['VALOR']);
							
							$this->_I250[$data][] = [
									'conta'	=>$l['CONTA'],
									'data'	=>$data,
									'lanc'	=>$l['I200_LANC'],
									'vl'	=>$valor,
									'DC'	=>$l['DC'],
									'hist'	=>strtoupper($l['HISTORICO']),
									'I075'	=>$l['I075']
									
							];
						}
					}
				}
			}
			//$this->gravaResumo('I250');
		}else{
			$this->recuperaResumo('I250');
		}
		//print_r($this->_I250);
	}
	
	private function getNotasEFD(){
		$this->getEFD('A100');
		$this->getEFD('C100');
		$this->getEFD('D100');
		$this->getEFD('C500');
		
		//print_r($this->_EFD['C100']);
		
		
		$this->ajustaEFDdata();
		
		
		foreach ($this->_EFD as $data => $efd){
			echo "$data <br>\n";
		}
		//		die();
		//print_r($this->_EFD);die();
		
		$this->gravaResumo('EFD');
		
	}
	
	
	private function comparaNotas(){
		foreach ($this->_I250 as $data => $I250){
			foreach ($I250 as $k => $n){
				$ret = $this->compara($data, $n);
				$this->_I250[$data][$k]['pis'] = $ret['pis'];
				$this->_I250[$data][$k]['cof'] = $ret['cof'];
				
				$this->_I250[$data][$k]['pisCalc'] = $ret['pisCalc'];
				$this->_I250[$data][$k]['cofCalc'] = $ret['cofCalc'];
				
				$this->_I250[$data][$k]['nota'] = isset($ret['nota']) ? $ret['nota'] : '';
			}
		}
		//die();
		//$this->gravaResumo('I250_AJ');
	}
	
	private function compara($data, $n){
		$ret = [
				'pis' => 0,
				'cof' => 0,
				'pisCalc' => 0,
				'cofCalc' => 0,
				'nota' => 0,
		];
		//print_r($n);die();
		//print_r($this->_EFD[$data]);die();
		if(isset($this->_EFD_copia[$data])){
			//print_r($this->_EFD[$data]);
			foreach ($this->_EFD_copia[$data] as $k => $efd){
				//print_r($efd);
				//echo "$data - ".$efd['vl']."".$n[3]."";
				if($data == '20170102'){
					echo "----------xxxxxxxxxxxxxxxxxxxxx------------Pesquisa por valor: : ".$n['vl'] ." <br>\n";
				}
				if($efd['vl'] == $n['vl']){
					//echo "encontrou $data - ".$n[3]."<br>\n";
					$ret['pis'] = $efd['pis'];
					$ret['cof'] = $efd['cof'];
					//echo "============================================== encontrou =================================<br>\n";
					unset($this->_EFD_copia[$data][$k]);
					//Marca a nota como encontrada
					$this->_EFD[$data][$k][] = 'S';
					break;
				}
				
			}
			
			//Se ainda existe o registro, vai pesquisar pelo nr da nota
			if($ret['pis'] == 0 && $ret['cof'] == 0){
				$nota = $this->pesquisaNrNota($n['hist']);
				$ret['nota'] = $nota;
				if($data == '20210102'){
					echo "-----------------------------------Pesq por nota: : ".$n['hist'] ."== $nota <br>\n";
				}
				if(!empty($nota)){
					$pis = 0;
					$cof = 0;
					foreach ($this->_EFD_copia[$data] as $k => $efd){
						// print_r($efd);
						// print_r($n);
						// echo "Nota: $nota <br>\n";
						// die();
						
						if($data == '20210102'){
							//	echo $efd['nota'] ."== $nota <br>\n";
						}
						if($efd['nota'] == $nota){
							if($data == '20210102'){
								echo "Encontrou: ".$efd['nota'] ."== $nota <br>\n";
							}
							$pis += $efd['pis'];
							$cof += $efd['cof'];
							//unset($this->_EFD_copia[$data][$k]);
							//Marca a nota como encontrada
							$this->_EFD[$data][$k][] = 'S';
						}
						
					}
					$ret['pis'] = $pis;
					$ret['cof'] = $cof;
					
				}
			}
			
			//Se ainda existe o registro, quer dizer que não encontrou, então calcula pis/cofins
			if($ret['pis'] == 0 && $ret['cof'] == 0){
				$valor = str_replace(',', '.', $n['vl']);
				
				$ret['pisCalc'] = round($valor * $this->_percentPis, 2);
				$ret['cofCalc'] = round($valor * $this->_percentCofins, 2);
			}
			
		}
		
		return $ret;
	}
	
	private function pesquisaNrNota($historico){
		$novo = trim(str_replace($this->_letras, '', $historico));
		//$novo = preg_replace("/[^0-9]/", "", $historico.' 99');
		//echo "novo: $novo - $historico<br>\n";
		
		return $novo;
	}
	//-----------------------------------------------------------------------------------------------------
	
	private function ajustaEFDdata(){
		$temp = [];
		
		foreach ($this->_EFD as $bloco => $efds){
			if(count($efds) > 0){
				foreach ($efds as $efd){
					$data = $efd['data'];
					$data = substr($data, 4,4).substr($data, 2,2).substr($data, 0,2);
					unset($efd['data']);
					$temp[$data][] = $efd;
				}
			}
		}
		ksort($temp);
		$this->_EFD = $temp;
		
		//print_r($temp);
	}
	
	private function getEFD($bloco){
		foreach ($this->_anos as $ano){
			$dir = $this->_path.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'efd';
			if(!is_dir($dir)){
				$dir = $this->_path.DIRECTORY_SEPARATOR.$ano.DIRECTORY_SEPARATOR.'EFD';
				if(!is_dir($dir)){
					die("'Diretorio $dir não encontrado, favor revisar!");
				}
			}
			
			$arquivos = $this->getArquivos($dir);
			
			if(count($arquivos) == 0){
				die("Não constam arquivo de EFD no diretorio $dir, favor verificar!");
			}
			
			foreach ($arquivos as $arquivo){
				//echo "Lendo arquivo $bloco $arquivo <br>\n";
				$linhas = [];
				$linhas = $this->leituraArquivo($dir.DIRECTORY_SEPARATOR.$arquivo, $bloco);
				//echo "$arquivo - $bloco <br>";
				//if($bloco == 'C100'){
				//	print_r($linhas);
				//}
				if(count($linhas) > 0){
					foreach ($linhas as $l){
						$this->_EFD[$bloco][] = $l;
						//if(isset($l[2])){
						
						//}
					}
				}
			}
		}
	}
	
	private function getArquivos($dir){
		$ret = [];
		$diretorio = dir($dir);
		
		while($arquivo = $diretorio->read()){
			if($arquivo != '.' && $arquivo != '..'){
				$ret[] = $arquivo;
			}
		}
		
		return $ret;
	}
	
	private function leituraArquivo($arq, $bloco = ''){
		$ret = [];
		$temp_I050 = '';
		$temp_I050_red = '';
		
		switch ($bloco) {
			case 'I250':
				$blocosAtivos = ['I200', 'I250'];
				break;
			case 'C100':
				$blocosAtivos = ['C100','C170'];
				break;
			case 'A100':
				$blocosAtivos = ['A100'];
				break;
			case 'D100':
				$blocosAtivos = ['D100','D101','D105'];
				break;
			case 'C500':
				$blocosAtivos = ['C500','C501','C505'];
				break;
				
			default:
				$blocosAtivos = [];
				echo "Não foram definidos blocos ativos.<br>\n";
				return;
				break;
		}
		
		$handle = fopen($arq, "r");
		if ($handle) {
			$ret_temp = [];
			while (!feof($handle)) {
				$linha = fgets($handle);
				if (strlen(trim($linha)) > 0) {
					$processa = false;
					if(count($blocosAtivos) == 0){
						$processa = true;
					}else{
						foreach ($blocosAtivos as $ativo){
							if(strpos($linha, $ativo) === 1){
								$processa = true;
							}
						}
					}
					if($processa){
						$sep = $this->separaLinha($linha);
						if($bloco == 'I050'){
							if($sep[1] == 'I050'){
								if(strpos($sep[6],'1') !== 0 && strpos($sep[6],'2') !== 0 && strpos($sep[6],'01') !== 0 && strpos($sep[6],'02') !== 0){
									if(empty($temp_I050)){
										$temp_I050 = $sep[6];
										$temp_I050_red = $sep[7];
									}
									//			$ret[$sep[6]] = [$sep[6], utf8_encode($sep[8])];
								}else{
									$temp_I050 = '';
								}
							}else{
								if(!empty($temp_I050) && !empty(trim($sep[4]))){
									$t = [];
									$t['reduzida'] = $temp_I050_red;
									$t['desc'] = $sep[4];
									$t['conta'] = $temp_I050;
									$ret[$temp_I050][] = $t;
									//echo $linha."<br>\n";
									//echo $temp_I050.' - '.$sep[4]."<br>\n";
								}
							}
						}elseif($bloco == 'I250'){
							if($sep[1] == 'I200'){
								$temp_I200_LANC = $sep[2];
								$temp_I200_DT = $sep[3];
							}else{
								if(strpos($sep[2],'1') !== 0 && strpos($sep[2],'2') !== 0 && strpos($sep[2],'01') !== 0 && strpos($sep[2],'02') !== 0){
									$temp = [];
									$temp['I200_LANC']  = $temp_I200_LANC;
									$temp['I200_DT']	= $temp_I200_DT;
									
									$temp['CONTA']		= $sep[2];
									$temp['VALOR']		= str_replace(',', '.', $sep[4]);
									$temp['DC']			= $sep[5];
									$temp['HISTORICO']	= utf8_encode($sep[8]);
									$temp['I075']		= $sep[7];
									
									$ret[] = $temp;
								}
							}
						}elseif($bloco == 'C100'){
							if($sep[1] == 'C100'){
								$entSaid = $sep[2];
								$nota = $sep[8];
								$data = $sep[11];
								$valor = str_replace(',', '.', $sep[12]);
								$pis = str_replace(',', '.', $sep[26]);
								$cofins = str_replace(',', '.', $sep[27]);
								
								if($entSaid == '0'){
									//echo "$linha";
									$ret[] = ['data'=>$data, 'nota'=>$nota, 'vl'=>$valor, 'pis'=>$pis, 'cof'=>$cofins, 'bloco' =>'C100'];
								}
							}else{
								$valor = str_replace(',', '.', $sep[7]);
								$pis = str_replace(',', '.', $sep[30]);
								$cofins = str_replace(',', '.', $sep[36]);
								
								if($entSaid == 0){
									//echo "$linha";
									$ret[] = ['data'=>$data, 'nota'=>$nota, 'vl'=>$valor, 'pis'=>$pis, 'cof'=>$cofins, 'bloco' =>'C170'];
								}
							}
						}elseif($bloco == 'A100'){
							$nota = $sep[8];
							$data = $sep[11];
							$valor = str_replace(',', '.', $sep[12]);
							$pis = str_replace(',', '.', $sep[16]);
							$cofins = str_replace(',', '.', $sep[17]);
							
							$ret[] = ['data'=>$data, 'nota'=>$nota, 'vl'=>$valor, 'pis'=>$pis, 'cof'=>$cofins, 'bloco' =>'A100'];
						}elseif($bloco == 'D100'){
							if($sep[1] == 'D100'){
								if(count($ret_temp) > 0){
									$ret[] = ['data'=>$ret_temp['data'], 'nota'=>$ret_temp['nota'], 'vl'=>$ret_temp['valor'], 'pis'=>$ret_temp['pis'], 'cof'=>$ret_temp['cofins'], 'bloco' =>'D100'];
								}
								$ret_temp['nota'] = $sep[9];
								$ret_temp['data'] = $sep[12];
								$ret_temp['valor'] = str_replace(',', '.', $sep[15]);
								$ret_temp['pis'] = 0;
								$ret_temp['cofins'] = 0;
							}elseif($sep[1] == 'D101'){
								$ret_temp['pis'] = str_replace(',', '.', $sep[8]);
							}else{
								$ret_temp['cofins'] = str_replace(',', '.', $sep[8]);
							}
						}elseif($bloco == 'C500'){
							if($sep[1] == 'C500'){
								if(count($ret_temp) > 0){
									$ret[] = ['data'=>$ret_temp['data'], 'nota'=>$ret_temp['nota'], 'vl'=>$ret_temp['valor'], 'pis'=>$ret_temp['pis'], 'cof'=>$ret_temp['cofins'], 'bloco' =>'C500'];
								}
								$ret_temp['nota'] = $sep[7];
								$ret_temp['data'] = $sep[8];
								$ret_temp['valor'] = str_replace(',', '.', $sep[10]);
								$ret_temp['pis'] = 0;
								$ret_temp['cofins'] = 0;
							}elseif($sep[1] == 'C501'){
								$ret_temp['pis'] = str_replace(',', '.', $sep[7]);
							}else{
								$ret_temp['cofins'] = str_replace(',', '.', $sep[7]);
							}
						}
						
						
					}
				}
			}
			if(count($ret_temp) > 0 && ($bloco == 'D100' || $bloco == 'C500')){
				$ret[] = ['data'=>$ret_temp['data'], 'nota'=>$ret_temp['nota'], 'vl'=>$ret_temp['valor'], 'pis'=>$ret_temp['pis'], 'cof'=>$ret_temp['cofins'], 'bloco' =>$bloco];
			}
			fclose($handle);
		}else{
			echo "Erro ao abrir o arquivo $arq <br>\n";
			return false;
		}
		return $ret;
	}
	
	//------------------------------------------------------------------- ESTRUTTURAS ---------------------
	
	private function getDadosI250(){
		$ret = [];
		//print_r($this->_I250);die();
		if(count($this->_I250) > 0){
			foreach ($this->_I250 as $data => $i250){
				
				foreach ($i250 as $i){
					$temp = [];
					$temp[] = $data;
					foreach ($i as $k => $item){
						$temp[$k] = $item;
					}
					
					$ret[] = $temp;
				}
			}
		}
		
		//print_r($ret);die();
		return $ret;
	}
	
	private function getDadosEFD(){
		$ret = [];
		//print_r($this->_I250);die();
		if(count($this->_EFD) > 0){
			foreach ($this->_EFD as $data => $efd){
				
				foreach ($efd as $i){
					$temp = [];
					$temp['data'] = $data;
					foreach ($i as $k => $item){
						$temp[$k] = $item;
					}
					
					$ret[] = $temp;
				}
			}
		}
		
		//print_r($ret);die();
		return $ret;
	}
	
	private function putPlanoContas($linhas){
		if(count($linhas) > 0){
			foreach ($linhas as $linha){
				$i = explode('|', $linha);
				$conta = $i[0];
				$this->_planoContas[$conta] = [$i[0],$i[1],$i[2]];
			}
		}
		
		//print_r($this->_planoContas);
	}
	
	private function putI250($linhas){
		if(count($linhas) > 0){
			foreach ($linhas as $linha){
				$i = explode('|', $linha);
				$data = $i[0];
				$this->_I250[$data][] = [$i[1],$i[2],$i[3],$i[4],$i[5],$i[6]];
			}
		}
		//print_r($this->_I250);
	}
	
	
	//------------------------------------------------------------------- UTEIS ---------------------------
	
	private function separaLinha($linha){
		return explode('|', $linha);
	}
	
	/**
	 * Verifica se o arquivo de resumo existe
	 *
	 * @param string $nome - Nome do arquivo (sem extensão)
	 * @return boolean
	 */
	private function verificaResumo($nome){
		$ret = true;
		$arq = $this->_path.DIRECTORY_SEPARATOR.$nome.'.txt';
		
		if(file_exists($arq)){
			$ret = false;
		}
		
		return $ret;
		
	}
	
	private function gravaResumo($arquivo){
		if(empty($arquivo)){
			return;
		}
		
		switch ($arquivo) {
			case 'plano_contas':
				$dados = $this->_planoContas;
				break;
			case 'I250':
				$dados = $this->getDadosI250();
				break;
			case 'I250_AJ':
				$dados = $this->getDadosI250();
				break;
			case 'EFD':
				$dados = $this->getDadosEFD();
				break;
				
			default:
				return;
				break;
		}
		
		$this->gravaArquivo($arquivo, $dados);
	}
	
	private function recuperaResumo($arquivo){
		if(empty($arquivo)){
			return;
		}
		
		$linhas = $this->recuperaArquivo($arquivo);
		
		switch ($arquivo) {
			case 'plano_contas':
				$this->putPlanoContas($linhas);
				break;
			case 'I250':
				$this->putI250($linhas);
				break;
				
			default:
				return;
				break;
		}
		
	}
	
	private function gravaArquivo($arquivo, $dados){
		$file = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.txt', "w");
		$q = count($dados);
		if($q == 0){
			echo "Arquivo $arquivo sem dados!<br>\n";
			return;
		}
		
		foreach ($dados as $dado){
			$linha = implode('|', $dado);
			fwrite($file, $linha."\n");
		}
		
		fclose($file);
	}
	
	private function recuperaArquivo($arquivo){
		$ret = [];
		
		$handle = fopen($this->_path.DIRECTORY_SEPARATOR.$arquivo.'.txt', "r");
		if ($handle) {
			while (!feof($handle)) {
				$linha = fgets($handle);
				if (strlen(trim($linha)) > 0) {
					$ret[] = str_replace("\n", '', $linha);
				}
			}
			fclose($handle);
		}else{
			addPortalMensagem("Arquivo $arquivo - recuperaArquivo - não encontrado", 'danger');
		}
		return $ret;
	}
	
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
		$dados = $this->separaI250('CO', true);
		$cab 	= ['Bloco','COD_CTA'	,'Lancamento','Conta Sintética','Desc Conta','Nr. Conta','Vl Conta'	,'C/D'	,'Nota'	,'Data Conta'	,'Data Base','Hist. I250','Hist. I075','PIS','COFINS'	,'PIS Marpa','COFINS Marpa'];
		$campos = ['bloco','codconta'	,'lanc'		 ,'reduzida'	   ,'descConta'	,'conta'	,'vl'	    ,'DC'	,'nota'	,'data'			,'base'		, 'hist'	 ,'hist075'	  ,'pis','cof'		,'pisCalc'	,'cofCalc'];
		$tipos  = [''	  ,''			,''			 ,''			   ,''			,''			,''			,''		,''		,'D'];
		$arquivo = new insumos_excel($this->_path, $dados, $campos, $cab, $tipos);
		$arquivo->grava('I250.csv');
		//	$excel->addWorksheet('resumo', 'Resumo');
		
		
		
	}
	
	private function separaEFD(){
		$ret = [];
		
		if(count($this->_EFD) > 0){
			foreach ($this->_EFD as $data => $dia){
				foreach ($dia as $efd){
					$temp = [];
					$temp[] = $data;
					foreach ($efd as $v){
						$temp[] = $v;
					}
					
					$ret[] = $temp;
				}
				//print_r($ret);die();
			}
		}
		
		return $ret;
		
	}
	
	/**
	 * Separa os I250 em descartados e utilizados
	 *
	 * @param string $tipo
	 * @return array[]|array[][]
	 */
	private function separaI250($tipo, $virgula = false){
		$ret = [];
		
		if(count($this->_I250) > 0){
			foreach ($this->_I250 as $dia){
				foreach ($dia as $i250){
					if($tipo == 'IG'){
						if($i250['pis'] != 0 || $i250['cof'] != 0){
							$temp = [];
							$temp['bloco'] = 'I250';
							$temp['hist075'] = $this->_I075[$i250['I075']];
							foreach ($i250 as $k => $v){
								$temp[$k] = $v;
							}
							
							$ret[] = $temp;
						}
					}else{
						//print_r($i250);die();
						if($i250['pis'] == 0 && $i250['cof'] == 0){
							$temp = [];
							$temp['bloco'] = 'I250';
							$temp['hist075'] = !empty($i250['I075']) ? $this->_I075[$i250['I075']] : '';
							//print_r($this->_planoCod);die();
							$temp['codconta'] = $i250['conta'];
							$temp['conta'] = $this->_planoCod[$i250['conta']]['conta'];
							$temp['descConta'] = $this->_planoCod[$i250['conta']]['desc'];
							$temp['reduzida'] = $this->_planoCod[$i250['conta']]['reduzida'];
							
							$temp['hist'] = utf8_encode($i250['hist']);
							$temp['DC'] = $i250['DC'];
							$temp['data'] = substr($i250['data'], 6, 2).'/'.substr($i250['data'], 4, 2).'/'.substr($i250['data'], 0, 4);
							$temp['lanc'] = $i250['lanc'];
							
							$temp['nota'] = isset($i250['nota']) ? $i250['nota'] : '';
							
							$mult = $i250['DC'] == 'D' ? 1 : -1;
							$temp['vl'] = $i250['vl'] * $mult;
							$temp['pis'] = $i250['pis'];
							$temp['cof'] = $i250['cof'];
							$temp['pisCalc'] = $i250['pisCalc'];
							$temp['cofCalc'] = $i250['cofCalc'];
							
							if($virgula){
								$temp['vl'] 	= str_replace('.',',', $temp['vl']);
								$temp['pis'] 	= str_replace('.',',', $temp['pis']);
								$temp['cof'] 	= str_replace('.',',', $temp['cof']);
								$temp['pisCalc']= str_replace('.',',', $temp['pisCalc']);
								$temp['cofCalc']= str_replace('.',',', $temp['cofCalc']);
							}
							
							$temp['base'] = substr($i250['data'], 4,2).'-'.substr($i250['data'], 0,4);
							
							$inclui = true;
							foreach ($this->_contas_fora as $fora){
								if(strpos($temp['descConta'], $fora) !== false){
									$inclui = false;
								}
							}
							
							if($inclui){
								$ret[] = $temp;
							}
							//print_r($i250);
							//print_r($ret);
							
							//return $ret;
						}
					}
				}
			}
		}
		
		return $ret;
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
	
}


function retiraAcentos($string) {
	// matriz de entrada
	$de = array("'", 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','Ã','É','Í','Ó','Ú','ñ','Ñ','ç','Ç' );
	// matriz de saída
	$por   = array( '`','a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','A','E','I','O','U','n','n','c','C');
	// devolver a string
	return str_replace($de, $por, $string);
}