<?php
/*
 * Data Criacao 24/07/2023
 * Autor: Verticais - Alexandre thiel
 *
 * Descricao: Realiza a montagem do excel nos padrões enviados pelo comercial
 *
 *  Alterções:
 *  
 *  11/10/2023 - Emanuel thiel - passa a colorir as células de % e premio caso a meta seja alcançada
 *  13/10/2023 - Emanuel Thiel - passa a: calcular as %, fazer os somatórios direto no excel (premios por vendedor, total por super e somatorio final) e calcular o premio direto no excel  
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class fechamento_campanhas{
	
	// Objeto excel
	private $_excel;
	
	//Arquivo
	private $_arquivo;
	
	// Colunas
	private $_colunas = [];
	
	//worksheet
	private $_worksheet = [];
	
	//Worksheet de trabalho
	private $_wsSetada;
	
	//Linha atual
	private $_linha;
	
	//Campos
	private $_campos = [];
	
	//Tipos
	private $_tipos = [];
	
	//VEndas
	private $_vendas = [];
	
	//Linhas com o total dos Super
	private $_linha_total_super = [];
	
	//celulas a pintar
	private $_celulas_pintar = [];
	
	//colunas a monitorar para pintar (pinta celula caso o valor seja > 1)
	private $_colunas_monitorar = [];
	
	//colunas para fazer somatório tanto por supervisor quanto o somatorio final
	private $_colunas_somatorio = [];
	
	//colunas para fazer % direto pelo excel
	private $_colunas_porcentagem = [];
	
	//colunas de premio, utilizado para colocar as formulas no excel
	private $_colunas_premio = [];
	
	//número da coluna que contem a venda global, utilizado para calcular os premios pelo excel
	private $_num_coluna_venda_global = 0;
	
	public function __construct($campos, $promocao, $vendas, $premio, $subCampanhas, $arquivo = ''){
		if(empty($arquivo)){
			$arquivo = 'fechamento.xlsx';
		}
		
		$param = [];
		$param['nomeArquivo'] = $arquivo;
		$this->_excel = new excel_exporta01($param);
		
		$this->_excel->setTituloWS('Fechamento');
		$this->geraCab($campos);
		
		$this->formataColunas($campos);

		$linhas = $this->geraLinhas($vendas, $campos, $subCampanhas, $promocao, $premio);
		$this->gravaLinhas($linhas);
		
		$this->pintarCelulas();
		$this->modificaCor(count($linhas) + 1, count($campos['campo']));
		
		$this->_excel->grava();
		
	}
	
	private function modificaCor($ultima, $colunas){
		if(count($this->_linha_total_super) > 0){
			foreach ($this->_linha_total_super as $linha){
				$this->_excel->aplicaCorLinha($linha,'ffffff00', $colunas);
			}
		}
		
		$this->_excel->aplicaCorLinha($ultima,'ffc6e0b4',$colunas);
	}
	
	private function formataColunas($campos){
		$temp = [];
		
		foreach ($campos['tipo'] as $k => $tipo){
			if(strpos($campos['campo'][$k],'perc') === false){
				$temp[] = $tipo;
			}else{
				$temp[] = 'P';
			}
			
			//aproveita para pegar as colunas que vão ser alteradas ao longo do programa
			if($campos['etiqueta'][$k] == '%'){
			    $this->_colunas_monitorar[] = $k;
                $this->_colunas_somatorio[] = $k - 1;
                $this->_colunas_somatorio[] = $k - 2;
                $this->_colunas_porcentagem[] = $k;
			}
		    if($campos['etiqueta'][$k] == 'Premio'){
		        $this->_colunas_somatorio[] = $k;
                $this->_colunas_premio[] = $k;
		    }
		}
        $this->_colunas_somatorio[] = count($campos['tipo']) - 1;
		
		$this->_excel->setFormatacao($temp);
	}
	
	private function geraCab($campos){
		$linha = $campos['etiqueta'];
		$this->_excel->gravaLinha($linha, 'cabecalho');
	}
	
	private function gravaLinhas($linhas){
		if(count($linhas) > 0){
			foreach ($linhas as $linha){
				$this->_excel->gravaLinha($linha);
			}
		}
	}
	
	private function geraLinhas(&$vendas, &$campos, &$subCampanhas, &$promocao, &$premio){
		$ret = [];
		foreach ($vendas as $super => $v1){
			foreach ($v1 as $erc => $v){
				$temp = [];
				foreach ($campos['campo'] as $campo){
					if(isset($v[$campo])){
						$temp[$campo] = $v[$campo];
					}else{
						$temp[$campo] = 0;
					}
				}
				$ret[$super][$erc] = $temp;
			}
		}
		
		//separa código das campanhas
		$camp = [];
		foreach ($subCampanhas as $scs){
			foreach ($scs as $sub => $c){
				$camp[] = $sub;
			}
		}
		
		//Calcula o realizado
		foreach ($ret as $super => $linhas){
			foreach ($linhas as $erc => $linha){
				foreach ($camp as $campanha){
					$meta = $linha['meta'.$campanha];
					$venda = $linha['venda'.$campanha];
					
					//duas casas decimais
					$ret[$super][$erc]['venda'.$campanha] = round($venda,2);
					
					$real = 0;
					if($meta > 0){
						$real = ($venda / $meta);
					}
					
					$ret[$super][$erc]['perc'.$campanha] = round($real,4);
				}
			}
		}
		
		
		//Calcula o premio
		$percent_MIX = $promocao['prem_perc_mix']/100;
		$percent_POS = $promocao['prem_perc_pos']/100;
		$percent_ENC = $promocao['prem_perc_enc']/100;
		$subGlobal = $promocao['prem_global'];
		$subMix = $promocao['prem_mix'];
		$subPos = $promocao['prem_pos'];
		$subEnc = $promocao['prem_enc'];
		
		$this->_num_coluna_venda_global = array_search('venda' . $subGlobal, $campos['campo']);

		foreach ($ret as $super => $linhas){
			foreach ($linhas as $erc => $linha){
				foreach ($camp as $campanha){
					$premio_val = "=0";
					if($campanha == $subMix){
						$realizado = $linha['venda'.$subGlobal] ?? 0;
						if($ret[$super][$erc]['perc'.$subGlobal] >= 1 &&  $ret[$super][$erc]['perc'.$subMix] >= 1){
					        $premio_val = "=@@realizado_global * $percent_MIX";
					        //$premio_val = round(($realizado * $percent_MIX),2);
						}
						$ret[$super][$erc]['premio'.$campanha] = $premio_val;
						//$ret[$super][$erc]['premio'.$campanha] = round($premio_val,4);
					}elseif($campanha == $subPos){
						$realizado = $linha['venda'.$subGlobal] ?? 0;
						if($linha['perc'.$subGlobal] >= 1 &&  $linha['perc'.$subPos] >= 1){
					        $premio_val = "=@@realizado_global * $percent_POS";
                            //$premio_val = round(($realizado * $percent_POS),2);
						}
						$ret[$super][$erc]['premio'.$campanha] = $premio_val;
						//$ret[$super][$erc]['premio'.$campanha] = round($premio_val,4);
					}elseif($campanha != $subGlobal){
						$percentMin	= isset($premio[$campanha]['atingimento']) ? round($premio[$campanha]['atingimento']/100,2) : 0;
						$realizado = $ret[$super][$erc]['venda'.$campanha] ?? 0;
						if($linha['perc'.$campanha] >= $percentMin){
						    if(isset($premio[$campanha]['tipo'])){
    							if($premio[$campanha]['tipo'] == 'P'){
							        $premio_val = "=@@realizado * " . $premio[$campanha]['premio']/100;
							        //$premio_val = round(($realizado * $premio[$campanha]['premio'])/100,2);
    							}elseif($premio[$campanha]['tipo'] == 'V'){
							        $premio_val = "={$premio[$campanha]['premio']}";
    							}elseif($premio[$campanha]['tipo'] == 'U'){
    								//Valor por unidade vendida
							        $premio_val = "=@@realizado * " . $premio[$campanha]['premio'];
								    //$premio_val = round(($realizado * $premio[$campanha]['premio']),2);
    							}
						    }
							//Se for campanha do encarte (e atingiu o blobal) ganha + x%
							if($campanha == $subEnc && $percent_ENC > 0 && $linha['perc'.$subGlobal] >= 1 &&  $linha['perc'.$subEnc] >= 1){
								$premio_val .= " + @@realizado * $percent_ENC";
								//$premio_val += round(($realizado * $percent_ENC),2);
							}
						}
						$ret[$super][$erc]['premio'.$campanha] = $premio_val;
					}
					
					if(!isset($ret[$super][$erc]['premioTotal'])){
						$ret[$super][$erc]['premioTotal'] = '=SUM(@@celulasPremios)';
					}
				}
			}
		}
		

		
		
//print_r($ret);		
		//Calcula o geral dos supervisores
		$totalSuper = [];
		foreach ($ret as $super => $linhas){
			$temp = [];
			$temp['super'] 		= '';
			$temp['supervisor'] = '';
			$temp['erc'] 		= '';
			$temp['vendedor'] 	= '';
			foreach ($linhas as $erc => $linha){
				$temp['supervisor'] 	= 'Total '.$linha['supervisor'];
				foreach ($campos['campo'] as $k => $campo){
					if($campos['tipo'][$k] != 'T'){
						if(!isset($temp[$campo])){
							$temp[$campo] = 0;
						}
						//$temp[$campo] += strval($linha[$campo]);
					}
				}
			}
//print_r($temp);			
			$totalSuper[$super] = $temp;
		}
		
		//Calcula o total geral e o percentual dos supervisores
		$totalGeral = [];
		$totalGeral['super'] 		= '';
		$totalGeral['supervisor']	= 'Total Geral';
		$totalGeral['erc'] 			= '';
		$totalGeral['vendedor'] 	= '';
		foreach ($totalSuper as $super => $total){
			foreach ($campos['campo'] as $k => $campo){
				if($campos['tipo'][$k] != 'T'){
					if(!isset($totalGeral[$campo])){
						$totalGeral[$campo] = 0;
					}
					//Soma o executado pelo Super ao Geral
					//$totalGeral[$campo] += $total[$campo];
					
					//Calcula o percentual do Super
					foreach ($camp as $campanha){
					    /*
						$meta = $total['meta'.$campanha];
						$venda = $total['venda'.$campanha];
						
						$real = 0;
						if($meta > 0){
							$real = ($venda / $meta);
						}
						*/
					    $real = 0;
						$totalSuper[$super]['perc'.$campanha] = round($real,4);
					}
				}
			}
		}
		
		//Calcula o percentual Geral
		foreach ($camp as $campanha){
			$meta = $totalGeral['meta'.$campanha];
			$venda = $totalGeral['venda'.$campanha];
			
			$real = 0;
			/*
			if($meta > 0){
				$real = ($venda / $meta);
			}
			*/
			$totalGeral['perc'.$campanha] = round($real,4);
		}
		
		//Ajusta em linhas
		$ret_temp = $ret;
		$ret = [];
		
		$num_linha = 1;
		$linhas_supervisores = [];
		foreach ($ret_temp as $super => $linhas){
		    $somatorio_inicio = $num_linha + 1;
			foreach ($linhas as $linha){
			    $num_linha++;
				$ret[] = $this->ajustarPremios($linha, $campos, $num_linha);
				foreach ($this->_colunas_monitorar as $num_coluna){
				    //verifica as % das metas
				    if($linha[$campos['campo'][$num_coluna]] >= 1){
				        $this->_celulas_pintar[] = [$num_coluna, $num_linha, 'verde'];
				        if($campos['etiqueta'][$num_coluna + 1] == 'Premio'){
				            $this->_celulas_pintar[] = [$num_coluna + 1, $num_linha, 'verde'];
				        }
				    }
				}
			}
			$somatorio_fim = $num_linha;
			$ret[] = $this->ajustarSomatorios($totalSuper[$super], $campos, $somatorio_inicio, $somatorio_fim);
			$num_linha++;
			$linhas_supervisores[] = $num_linha;
			$this->_linha_total_super[] = count($ret)+1;
		}
		$num_linha++;
		$ret[] = $this->ajustarSomatorioFinal($totalGeral, $linhas_supervisores, $campos, $num_linha);
		
		return $ret;
	}
	
	private function ajustarSomatorios(&$linha, &$campos, $inicio, $fim){
	    $ret = $linha;
	    if(count($this->_colunas_somatorio) > 0){
	        foreach ($this->_colunas_somatorio as $num_coluna){
	            //$coluna = substr($this->_col, $num_coluna,1);
	            $coluna = $this->getNameFromNumber($num_coluna);
	            //$linha[$campos['campo'][$num_coluna]] = "=SUM({$coluna}{$inicio}:{$coluna}{$fim})";
	            $ret[$campos['campo'][$num_coluna]] = "=SUM({$coluna}{$inicio}:{$coluna}{$fim})";
	            //$ret[$campos['campo'][$num_coluna]] = str_replace(['@@ini', '@@fim'], [$inicio, $fim], $ret[$campos['campo'][$num_coluna]]);
	        }
	    }
	    if(count($this->_colunas_porcentagem)){
	        $linha_atual = $fim + 1;
	        foreach ($this->_colunas_porcentagem as $num_coluna){
	            $celula_somatorio = $this->getNameFromNumber($num_coluna - 1) . $linha_atual;
	            $celula_sugestao  = $this->getNameFromNumber($num_coluna - 2) . $linha_atual;
	            $ret[$campos['campo'][$num_coluna]] = "=IF($celula_sugestao > 0, $celula_somatorio/$celula_sugestao, 0)";
	        }
	    }
	    return $ret;
	}
	
	private function ajustarPremios(&$linha, &$campos, &$num_linha){
	    $ret = $linha;
	    if(count($this->_colunas_premio) > 0){
	        $colunas_somatorio = [];
	        foreach ($this->_colunas_premio as $coluna_premio){
	            $celula_realizado = $this->getNameFromNumber($this->_num_coluna_venda_global) . $num_linha;
	            $ret[$campos['campo'][$coluna_premio]] = str_replace('@@realizado_global', $celula_realizado, $ret[$campos['campo'][$coluna_premio]]);
	            
	            $celula_realizado = $this->getNameFromNumber($coluna_premio - 2) . $num_linha;
	            $ret[$campos['campo'][$coluna_premio]] = str_replace('@@realizado', $celula_realizado, $ret[$campos['campo'][$coluna_premio]]);
	            
	            $colunas_somatorio[] = $this->getNameFromNumber($coluna_premio) . $num_linha;
	        }
	        if(count($colunas_somatorio) > 0){
	            $ret['premioTotal'] = "=SUM(" . implode(', ', $colunas_somatorio) . ")";
	        }
	    }
	    return $ret;
	}
	
	private function ajustarSomatorioFinal($linha_final, $linhas_supervisores, $campos, $linha_atual){
	    $ret = $linha_final;
	    if(count($this->_colunas_somatorio) > 0){
	        foreach ($this->_colunas_somatorio as $num_coluna){
	            //$coluna = substr($this->_col, $num_coluna,1);
	            $coluna = $this->getNameFromNumber($num_coluna);
	            $celulas = [];
	            foreach ($linhas_supervisores as $linha){
	                $celulas[] = $coluna . $linha;
	            }
	            //$linha[$campos['campo'][$num_coluna]] = "=SUM({$coluna}{$inicio}:{$coluna}{$fim})";
	            $ret[$campos['campo'][$num_coluna]] = "=SUM(" . implode(',', $celulas) . ")";
	            //$ret[$campos['campo'][$num_coluna]] = str_replace(['@@ini', '@@fim'], [$inicio, $fim], $ret[$campos['campo'][$num_coluna]]);
	        }
	    }
	    if(count($this->_colunas_porcentagem)){
	        foreach ($this->_colunas_porcentagem as $num_coluna){
	            $celula_somatorio = $this->getNameFromNumber($num_coluna - 1) . $linha_atual;
	            $celula_sugestao  = $this->getNameFromNumber($num_coluna - 2) . $linha_atual;
	            $ret[$campos['campo'][$num_coluna]] = "=IF($celula_sugestao > 0, $celula_somatorio/$celula_sugestao, 0)";
	        }
	    }
	    return $ret;
	}
	
	private function pintarCelulas(){
	    //pinta as celular indicadas
	    if(count($this->_celulas_pintar) > 0){
	        foreach ($this->_celulas_pintar as $celula){
	            $this->_excel->aplicaCorCelula($celula[1], $celula[2], $celula[0]);
	        }
	    }
	}
	
	private function getNameFromNumber($num){
	    $numeric = $num % 26;
	    $letter = chr(65 + $numeric);
	    $num2 = intval($num / 26);
	    if ($num2 > 0) {
	        return $this->getNameFromNumber($num2 - 1) . $letter;
	    } else {
	        return $letter;
	    }
	}
}