<?php
/*
 * Data Criacao 5 de nov de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: tws.rede.inc.php
 * 
 * Descricao: Relatorio de fechamento de redes
 * Solicitante: Joyce
 *
 * Alterções:
 *           26/10/2018 - Emanuel - Migração para intranet2
 *           17/08/2022 - Thiel - Ajuste conforme email do Gabriel/Comercial
 *           			- Alterar o nome das colunas "Medicamentos" -> "Med.SEM OL", "Não medicamentos" -> "N MED SEM OL", "Total" -> "total sem OL"
 *           			- Incluir coluna de "Venda OL"
 *           			- Incluir coluna de "Venda Total"
 *           			- Remover as colunas de genérico e similar
 */


if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class rede{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	    'editar'        => true,
	    'incluir'       => true,
	    'gravar'        => true,
	    'redesCRUD'     => true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	// Dados
	var $_dados;

    // CRUD
    var $_crud;
    
    //porcentagens do repasses
    var $_repasses;
    
    // sys444
    var $_sys444;
    
    //array dos conjuntos de redes
    //exemplo: array(array(id_rede1, id_rede2), array(id_rede3, id_rede4))
    var $_redes_conjuntas;
    
    //array com as redes que fazem parte de algum conjunto, é montado no schedule
    var $_lista_redes_ignorar;
    
    //redes que não vão aparecer em nenhuma parte do schedule
    //exemplo: array(id_rede1, id_rede2)
    //se alguma rede da lista negra aparece em algum conjunto ela é apagada do conjunto
    var $_lista_negra;
    
    //array com as redes que devem receber o relatorio com ol
    var $redes_com_ol;
    
    var $_schedule_dt_ini;
    
    var $_schedule_dt_fim;
    
    //Indica se rede recebe cálculo parcial
    private $_parcial = [];
    
    //Indica se é parcial ou fechamento
    private $_fechamento = false;
    
    //Indica se é teste
    private $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		if(issetAppVar('lista_repasses')) unsetAppVar('lista_repasses');
		
		$this->_teste = false;
		
		$this->_programa = '000002.rede';
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'cod'			  , 'etiqueta' => 'Cod'			        , 'tipo' => 'T', 'width' =>  70, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'		  , 'etiqueta' => 'Cliente'		        , 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cnpj'		  , 'etiqueta' => 'CNPJ'		        , 'tipo' => 'T', 'width' => 120, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'rede'		  , 'etiqueta' => 'Rede'		        , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'super'		  , 'etiqueta' => 'Rede Nome'	        , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cidade'		  , 'etiqueta' => 'Cidade'		        , 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'coderc1'		  , 'etiqueta' => 'ERC 1'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc1'		  , 'etiqueta' => 'ERC 1'			    , 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
//		$this->_relatorio->addColuna(array('campo' => 'coderc2'		  , 'etiqueta' => 'ERC 2'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
//		$this->_relatorio->addColuna(array('campo' => 'erc2'		  , 'etiqueta' => 'ERC 2'			    , 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		//$this->_relatorio->addColuna(array('campo' => 'coderc3'	  , 'etiqueta' => 'ERC Perf.'		    , 'tipo' => 'T', 'width' =>  80, 'class' => 'E'));
		//$this->_relatorio->addColuna(array('campo' => 'erc3'		  , 'etiqueta' => 'ERC Perf.'		    , 'tipo' => 'T', 'width' => 200, 'class' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'med_sem_ol'	  , 'etiqueta' => 'MED SEM OL'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'div_sem_ol'	  , 'etiqueta' => 'N MED SEM OL'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'total_sem_ol'  , 'etiqueta' => 'TOTAL SEM OL'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
//		$this->_relatorio->addColuna(array('campo' => 'ol'			  , 'etiqueta' => 'OL'			        , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'med'			  , 'etiqueta' => 'Medicamentos'	    , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	    //$this->_relatorio->addColuna(array('campo' => 'div'			  , 'etiqueta' => 'Nao Medic.'	        , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
//		$this->_relatorio->addColuna(array('campo' => 'total'		  , 'etiqueta' => 'TOT SEM OL + OL'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		//$this->_relatorio->addColuna(array('campo' => 'generico'	, 'etiqueta' => 'Generico'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		//$this->_relatorio->addColuna(array('campo' => 'similar'		, 'etiqueta' => 'Similar'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'repasse'		, 'etiqueta' => 'Repasse'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'	, 'variavel' => 'DATAFIM'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Rede'		, 'variavel' => 'REDE'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		
		$param = [];
		$param["texto"] = 'Configurar Repasses';
		$param["onclick"] = "setLocation('" . getLink() . "redesCRUD')";
		$param["id"] = 'repasses';
		$param['cor'] = 'success';
		$this->_relatorio->addBotao($param);
		
		$this->_crud = new crud02('gf_redes_percentual');
		
		$this->_redes_conjuntas = array(
		    array(94, 39),
		);
		$this->_lista_negra = [];
		$this->_lista_redes_ignorar = [];
		$this->redes_com_ol = array(12, 92, 63);
	}			
	
	function index(){
		$ret = '';
		
        $filtro = $this->_relatorio->getFiltro();
                $dataIni 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
        $dataFim 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
        $rede		= isset($filtro['REDE']) ? $filtro['REDE'] : '';
        
        $dataIni != '' ? $this->_relatorio->setTitulo("Fechamento de Redes. Periodo: ".datas::dataS2D($dataIni)." a ".datas::dataS2D($dataFim)) : $this->_relatorio->setTitulo("Redes");
        if(!$this->_relatorio->getPrimeira()){
            $dados = [];
            $this->getClientes($dataIni, $dataFim, $rede);
            
            if(is_array($this->_dados) && count($this->_dados)){
                foreach ($this->_dados as $clientes){
                    foreach ($clientes as $venda){
                        $dados[] = $venda;
                    }
                }
            }
            
            $this->_relatorio->setDados($dados);
            $this->_relatorio->setToExcel(true);
            
        }
        $ret .= $this->_relatorio;
        
        return $ret;
	}

	function schedule($param = ''){
		$mes = date('m');
		$ano = date('Y');

		if($this->_teste){
			$this->_schedule_dt_ini = '20210101';
			$this->_schedule_dt_fim = '20210110';
			$this->_fechamento = true;
		}else{
			//Executado somente segunda ou no primeiro dia útil do mês (com dados do mês anterior)
			if(verificaExecucaoSchedule($this->_programa,$ano.$mes)){
				if(date('N') != 1){
					echo "<br>\nNão é segunda feira nem primeiro dia do mês.<br>\n";
					if(!$this->_teste){
						log::gravaLog('fechamento_redes', 'Executado - Não é segunda nem primeiro dia útil');
						return;
					}
					log::gravaLog('fechamento_redes', 'Executado - Teste');
				}
				$this->_schedule_dt_fim = date('Ymd');
			}else{
				gravaExecucaoSchedule($this->_programa,$ano.$mes);
				$this->_fechamento = true;
				$mes--;
				if($mes == 0){
					$mes = 12;
					$ano--;
				}
				if($mes < 10){
					$mes = '0'.$mes;
				}
				$this->_schedule_dt_fim = date('Ymt',mktime(0,0,0,$mes,15,$ano));
			}
			$this->_schedule_dt_ini = $ano.$mes.'01';
		}
		
		$redes = [];
	    $this->getListaRedesIgnorar();

	    $emails = $this->getListaEmails();

	    foreach ($emails as $id_rede => $email_rede){
            if(trim($email_rede) != ''){
                $redes[] = $id_rede;
            }
        }
	    if(count($redes) > 0 || count($this->_lista_redes_ignorar) > 0){
	        /////////////////////////////
	        //envia email de todas as redes que não fazem parte de nenhum conjunto
	        $emails = $this->getListaEmails();
	        if(count($redes) > 0){
    	        foreach ($redes as $rede){
    	            if(!in_array($rede, $this->_lista_redes_ignorar) && !in_array($rede, $this->_lista_negra)){
    	            	$email = '';
    	            	if($this->_teste){
        	                $email = 'suporte@thielws.com.br';
        	            }else{
        	            	/*
        	            	 * Se for fechamento envia para todas as redes que tem email
        	            	 * Se for parcial envia para as redes que tem email e estão indicadas para receber parcial
        	            	 */
        	            	if($this->_fechamento || ($this->_parcial[$rede] == 'S')){
        	            		$email = isset($emails[$rede]) ? $emails[$rede].';'.$param : '';
        	            	}
        	            }
        	            if($email != ''){
        	            	$nomeRede = getNomeRede($rede);
echo "Rede: $rede - $nomeRede <br>\n";
        	                $dados = $this->getDadosScheduleUnitario($rede);
        	                $relatorio = $this->montaRelatorioSchedule($rede);
        	                $relatorio->setDados($dados);
        	                $relatorio->setEnviaTabelaEmail(false);
        	                $relatorio->setAuto(true);
        	                $relatorio->setToExcel(true,'Fechamento_Redes');
        	                $paramEmail = [];
        	                $paramEmail['msgIni'] = 'O relatório é do dia ' . datas::dataS2D($this->_schedule_dt_ini) . ' até o dia ' . datas::dataS2D($this->_schedule_dt_fim);
        	                $relatorio->enviaEmail($email, 'Relatório Fechamento de Redes - '.$nomeRede, $paramEmail);
        	                log::gravaLog('fechamento_redes', 'Enviado email, rede: '.$nomeRede.' Email: '.$email);
        	                unset($relatorio);
        	                
        	            }
    	            }
    	        }
	        }
	        /////////////////////////////////
	        //envia email para os conjuntos
	        if(is_array($this->_lista_redes_ignorar) && count($this->_lista_redes_ignorar) > 0 && is_array($this->_redes_conjuntas) && count($this->_redes_conjuntas) > 0){
	            foreach ($this->_redes_conjuntas as $conjuntos){
	                $email_conjunto = '';
	                if($this->_teste){
	                    $email_conjunto = 'suporte@thielws.com.br';
	                }
	                else{
	                    $email_conjunto = $this->getEmailConjunto($conjuntos, $emails);
	                }
	                if($email_conjunto != ''){
	                    $dados = $this->getDadosScheduleConjuntos($conjuntos);
	                    $relatorio = $this->montaRelatorioSchedule($conjuntos);
	                    $relatorio->setDados($dados);
	                    $relatorio->setEnviaTabelaEmail(false);
	                    $relatorio->setAuto(true);
	                    $relatorio->setToExcel(true);
	                    $paramEmail = [];
	                    $paramEmail['msgIni'] = 'O relatório é do dia ' . datas::dataS2D($this->_schedule_dt_ini) . ' até o dia ' . datas::dataS2D($this->_schedule_dt_fim);
	                    $relatorio->enviaEmail($email_conjunto, 'Relatório Fechamento de Redes', $paramEmail);
	                    unset($relatorio);
	                }
	            }
	        }
	    }
	}
	
	private function getEmailConjunto($conjunto, $emails){
	    $ret = '';
	    if(count($conjunto) > 0 && is_array($emails) && count($emails) > 0){
    	    $temp = [];
    	    foreach ($conjunto as $rede){
    	        if(isset($emails[$rede])){
    	            if(!in_array($emails[$rede], $temp) && trim($emails[$rede]) != ''){
    	               $temp[] = $emails[$rede];
    	            }
    	        }
    	    }
    	    if(count($temp) > 0){
    	        $ret = implode(';', $temp);
    	    }
	    }
	    return $ret;
	}
	
	private function getDadosScheduleConjuntos($conjunto){
	    $ret = [];
	    foreach ($conjunto as $rede){
	        $ret = array_merge($ret, $this->getDadosScheduleUnitario($rede));
	    }
	    return $ret;
	}
	
	private function getDadosScheduleUnitario($rede){
	    $ret = [];
	    $this->getClientes($this->_schedule_dt_ini, $this->_schedule_dt_fim, $rede);
	    if(count($this->_dados) > 0){
    	    foreach ($this->_dados as $clientes){
    	        foreach ($clientes as $venda){
    	            $ret[] = $venda;
    	        }
    	    }
	    }
	    $this->_dados = [];
	    return $ret;
	}
	

	function getERC2(){
		$ret = [];
		$sql = "select codusur, codcli from PCUSURCLI";
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$ret[$row[1]] = $row[0];
			}
		}
		return $ret;
	}
	
	function getTotalRede($valores = []){
	    $ret = 0;
	    if(is_array($valores) && count($valores) > 0){
	        foreach ($valores as $v){
	            $ret += isset($v[1]) ? $v[1] : 0;
	            $ret += isset($v[12]) ? $v[12] : 0;
	        }
	    }
	    return $ret;
	}
	
	function verificarTotalRede($valores = [], $rede = ''){
	    $ret = false;
	    if(is_array($valores) && count($valores) > 0 && trim($rede) != ''){
	        $total_rede = $this->getTotalRede($valores);
	        $sql = 'select valor_minimo_rede from gf_redes_percentual where rede = ' . $rede;
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            $valor_minimo = $rows[0]['valor_minimo_rede'];
	            $ret = ($total_rede > $valor_minimo);
	        }
	    }
	    return $ret;
	}
	
	function getClientes($dataIni, $dataFim, $rede = ''){
		$where = $rede == '' ? '' : "	and pcclient.codrede = $rede and pcredecliente.codrede = $rede";

		$erc = $this->getListaERC();
		
		$erc2 = $this->getERC2();
		$valores = $this->getVendas2($dataIni, $dataFim, $rede);
		$ol = $this->getVendasOL2($dataIni, $dataFim, $rede);
		$NOL = $this->getVendasNOL($dataIni, $dataFim, $rede);
		$generico  = $this->getVendasGenericoSimilar2($dataIni, $dataFim, $rede);
		$calcular_repasse = $this->verificarTotalRede($valores, $rede);
		$sql = "
				select 
				    pcredecliente.codrede,
				    pcredecliente.descricao,
				    pcclient.codcli,
				    pcclient.cliente,
				    pcclient.codusur1,
				    pcclient.codusur2,
				    pcclient.CGCENT,
					pcclient.MUNICCOB,
					pcclient.ESTCOB
				from 
				    pcredecliente,
				    pcclient
				WHERE   
				    pcclient.codrede = pcredecliente.codrede
				    and pcclient.codrede is not NULL
				    and pcclient.codrede <> 0
				    and pcredecliente.codrede <> 0
				    and PCCLIENT.DTEXCLUSAO IS NULL
					$where
				order by
				    pcredecliente.descricao,
				    pcclient.cliente
				";
		
		$rows = query4($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$rede = $row[0];
				$cliente = $row[2];
				
				if(!isset($erc2[$cliente])){
					$erc2[$cliente] = '';
				}
				if(!isset($generico[$cliente])){
					$generico[$cliente][2] = 0;
					$generico[$cliente][15] = 0;
				}
				
				$med = isset($valores[$cliente][1])  ? $valores[$cliente][1]  : 0;
				$nao = isset($valores[$cliente][12]) ? $valores[$cliente][12] : 0;
				
				$medOL = isset($ol[$cliente][1])  ? $ol[$cliente][1]  : 0;
				$naoOL = isset($ol[$cliente][12]) ? $ol[$cliente][12] : 0;
				
				$medNOL = isset($NOL[$cliente][1])  ? $NOL[$cliente][1]  : 0;
				$naoNOL = isset($NOL[$cliente][12]) ? $NOL[$cliente][12] : 0;
				
				$ercNome1 = $row[4] == '' ? '' : $erc[$row[4]];
				$ercNome2 = $row[5] == '' ? '' : $erc[$row[5]];
				$ercNome3 = $erc2[$cliente] == '' ? '' : $erc[$erc2[$cliente]];
				
				$this->_dados[$rede][$cliente]['cod'] 		= $cliente;
				$this->_dados[$rede][$cliente]['cliente'] 	= $row[3];
				$this->_dados[$rede][$cliente]['cnpj'] 		= $row[6];
				$this->_dados[$rede][$cliente]['rede'] 		= $rede;
				$this->_dados[$rede][$cliente]['super'] 	= $row[1];
				$this->_dados[$rede][$cliente]['cidade'] 	= $row['MUNICCOB'].'/'.$row['ESTCOB'];
				$this->_dados[$rede][$cliente]['coderc1'] 	= $row[4];
				$this->_dados[$rede][$cliente]['erc1'] 		= $ercNome1;
				$this->_dados[$rede][$cliente]['coderc2'] 	= $row[5];
				$this->_dados[$rede][$cliente]['erc2'] 		= $ercNome2;
				//$this->_dados[$rede][$cliente]['coderc3'] 	= $erc2[$cliente];
				//$this->_dados[$rede][$cliente]['erc3'] 		= $ercNome3;
				$this->_dados[$rede][$cliente]['med'] 		= $med;
				$this->_dados[$rede][$cliente]['div'] 		= $nao;
				$this->_dados[$rede][$cliente]['total']		= ($med + $nao);
				$this->_dados[$rede][$cliente]['ol'] 		= ($medOL + $naoOL);
				
				$this->_dados[$rede][$cliente]['med_sem_ol'] 		= $medNOL;
				$this->_dados[$rede][$cliente]['div_sem_ol'] 		= $naoNOL;
				$this->_dados[$rede][$cliente]['total_sem_ol'] 		= ($medNOL + $naoNOL);
				
				$this->_dados[$rede][$cliente]['generico'] 	= isset($generico[$cliente][2]) ? $generico[$cliente][2] : '';
				$this->_dados[$rede][$cliente]['similar'] 	= isset($generico[$cliente][15]) ? $generico[$cliente][15] : '';
				$this->_dados[$rede][$cliente]['repasse'] 	= $calcular_repasse ? $this->getRepasse($medNOL, $naoNOL, $medOL, $naoOL, $rede) : 0;
			}
		}
		/*
		 * 'med_sem_ol'	
'div_sem_ol'	
'total_sem_ol'
		 */
	}
	
	function getVendas2($dtDe, $dtAte, $rede){
		$ret = [];
		$param = [];
		$param['depto'] = '1,12';
		$param['cliente'] = "select codcli from pcclient where codrede is not NULL and codrede <> 0 ";
		if($rede != ''){
			$param['cliente'] .= "and codrede = $rede";
		}
		
		//Não leva em conta alguns produtos - Agafarma
		if($rede == 1){
			$param['produtoFora'] = implode(',', prodAGAFARMAfora());
		}
		
		$campos = array('CODCLI','CODEPTO');
		$vendas	= vendas1464Campo($campos, $dtDe, $dtAte, $param, false);
//print_r($vendas);
		
		if(count($vendas) > 0){
			foreach ($vendas as $codcli => $venda){
				foreach ($venda as $depto => $v){
					$ret[$codcli][$depto] = $v['venda'];
				}
			}
		}
//print_r($ret);
		return $ret;
	}
	
	function getVendasOL2($dtDe, $dtAte, $rede){
		$ret = [];
		$param = [];
		$param['depto'] = '1,12';
		$param['origem'] = 'OL';
		$param['cliente'] = "select codcli from pcclient where codrede is not NULL and codrede <> 0 ";
		if($rede != ''){
			$param['cliente'] .= "and codrede = $rede";
		}
		
		//Não leva em conta alguns produtos - Agafarma
		if($rede == 1){
			$param['produtoFora'] = implode(',', prodAGAFARMAfora());
		}
		
		$campos = array('CODCLI','CODEPTO');
		$vendas	= vendas1464Campo($campos, $dtDe, $dtAte, $param, false);
//print_r($vendas);
		
		if(count($vendas) > 0){
			foreach ($vendas as $codcli => $venda){
				foreach ($venda as $depto => $v){
					$ret[$codcli][$depto] = $v['venda'];
				}
			}
		}
//print_r($ret);
		return $ret;
	}
	
	private function getVendasNOL($dtDe, $dtAte, $rede){
	    $ret = [];
	    $param = [];
	    $param['origem'] = 'NOL';
	    $param['depto'] = '1,12';
	    $param['cliente'] = "select codcli from pcclient where codrede is not NULL and codrede <> 0 ";
	    if($rede != ''){
	        $param['cliente'] .= "and codrede = $rede";
	    }
	    
	    //Não leva em conta alguns produtos - Agafarma
	    if($rede == 1){
	    	$param['produtoFora'] = implode(',', prodAGAFARMAfora());
	    }
	    
	    $campos = array('CODCLI','CODEPTO');
	    $vendas	= vendas1464Campo($campos, $dtDe, $dtAte, $param, false);
//print_r($vendas);
	    
	    if(count($vendas) > 0){
	        foreach ($vendas as $codcli => $venda){
	            foreach ($venda as $depto => $v){
	                $ret[$codcli][$depto] = $v['venda'];
	            }
	        }
	    }
//print_r($ret);
	    return $ret;
	}
	
	function getVendasGenericoSimilar2($dtDe, $dtAte, $rede){
		$ret = [];
		$param = [];
		$param['depto'] = '1,2';
		$param['cliente'] = "select codcli from pcclient where codrede is not NULL and codrede <> 0 ";
		if($rede != ''){
			$param['cliente'] .= "and codrede = $rede";
		}
		
		//Não leva em conta alguns produtos - Agafarma
		if($rede == 1){
			$param['produtoFora'] = implode(',', prodAGAFARMAfora());
		}
		
		$campos = array('CODCLI','CODSEC');
		$vendas	= vendas1464Campo($campos, $dtDe, $dtAte, $param, false);
//print_r($vendas);
		
		if(count($vendas) > 0){
			foreach ($vendas as $codcli => $venda){
				foreach ($venda as $secao => $v){
					$ret[$codcli][$secao] = $v['venda'];
				}
			}
		}
//print_r($ret);
		return $ret;
	}
	
	function getListaERC(){
		$ret = [];
		$sql = "select codusur, nome from pcusuari";
		$rows = query4($sql);
		foreach ($rows as $row){
			$ret[$row[0]] = $row[1];
		}
//print_r($ret);
		return $ret;
		
	}
	
	function getListaRepasses($rede){
	    $ret = [];
	    if(issetAppVar('lista_repasses')){
	        $temp = getAppVar('lista_repasses');
	        $ret = isset($temp[$rede]) ? $temp[$rede] : [];
	    }
	    else{
	        $sql = 'select * from gf_redes_percentual';
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            $campos = array('rede', 'pc_med_nol', 'pc_nmed_nol', 'pc_med_ol', 'pc_nmed_ol', 'valor_minimo_loja', 'pc_sub_med_nol', 'pc_sub_nmed_nol', 'pc_sub_med_ol', 'pc_sub_nmed_ol');
	            $campos_dividir = array('pc_med_nol', 'pc_nmed_nol', 'pc_med_ol', 'pc_nmed_ol', 'pc_sub_med_nol', 'pc_sub_nmed_nol', 'pc_sub_med_ol', 'pc_sub_nmed_ol');
	            foreach ($rows as $row){
	                if($row['pc_med_nol'] != 0 || $row['pc_nmed_nol'] != 0 || $row['pc_med_ol'] != 0 || $row['pc_nmed_ol'] != 0 || $row['pc_sub_med_nol'] != 0 || $row['pc_sub_nmed_nol'] != 0 || $row['pc_sub_med_ol'] != 0 || $row['pc_sub_nmed_ol'] != 0 ){
	                    $temp = [];
	                    foreach ($campos as $c){
	                        $temp[$c] = $row[$c];
	                    }
	                    foreach ($campos_dividir as $c){
	                        $temp[$c] = $temp[$c] / 100;
	                    }
	                    $ret[$temp['rede']] = $temp;
	                }
	            }
	            if(count($ret) > 0){
	                putAppVar('lista_repasses', $ret);
	            }
	            $ret = isset($ret[$rede]) ? $ret[$rede] : [];
	        }
	    }
	    return $ret;
	}
	
	function getRepasse($med_nol, $nmed_nol, $med_ol, $nmed_ol, $rede){
	    $ret = 0.00;
	    $lista = $this->getListaRepasses($rede);
	    if(is_array($lista) && count($lista) > 0){
	        $total = $this->getTotalCondicional($med_nol, $nmed_nol, $med_ol, $nmed_ol, $lista);
	        if($total >= $lista['valor_minimo_loja']){
	            $ret += $lista['pc_med_nol']  * $med_nol;
	            $ret += $lista['pc_nmed_nol'] * $nmed_nol;
	            $ret += $lista['pc_med_ol']   * $med_ol;
	            $ret += $lista['pc_nmed_ol']  * $nmed_ol;
	        }else{
	            $ret += $lista['pc_sub_med_nol']  * $med_nol;
	            $ret += $lista['pc_sub_nmed_nol'] * $nmed_nol;
	            $ret += $lista['pc_sub_med_ol']   * $med_ol;
	            $ret += $lista['pc_sub_nmed_ol']  * $nmed_ol;
	        }
	    }
	    return $ret;
	}
	
	function getTotalCondicional($med_nol, $nmed_nol, $med_ol, $nmed_ol, $lista){
	    $ret = 0;
	    $modo = $this->getTipoTotal($lista);
//echo "Lista:  - Modo: $modo <br>\n";
//print_r($lista);
	    if($modo == 'ol'){
	        $ret = $med_ol + $nmed_ol;
	    }
	    elseif($modo == 'nol'){
	        $ret = $med_nol + $nmed_nol;
	    }
	    elseif($modo == 'geral'){
	        $ret = $med_nol + $nmed_nol + $med_ol + $nmed_ol;
	    }
	    return $ret;
	}
	
	function getTipoTotal($lista){
	    $ret = '';
	    if(($lista['pc_med_nol'] != 0 || $lista['pc_nmed_nol'] != 0) && ($lista['pc_med_ol'] != 0 || $lista['pc_nmed_ol'] != 0)){
	        $ret = 'geral';
	    }
	    elseif($lista['pc_nmed_ol'] != 0 || $lista['pc_med_ol'] != 0){
	        $ret = 'ol';
	    }
	    elseif($lista['pc_med_nol'] != 0 || $lista['pc_nmed_nol'] != 0){
	        $ret = 'nol';
	    }
	    return $ret;
	}
	
	function redesCRUD(){
	    //fazer o crud da tabela a ser criada
	    //rede, pc_med_nol, pc_nmed_nol, pc_med_ol, pc_nmed_ol, valor_minimo
	    $param = [];
	    $param['paginacao'] = false;
	    $param['scroll'] 	= true;
	    $param['editar']	= true;
	    //Indica a posição que deve ficar o botão de Editar - 'I'nicio ou 'F'im da linha
	    //$param['editarPosicao']	= 'I';
	    $param['incluir']	= true;
	    $param['excluir']	= false;
	    //Indica a posição que deve ficar o botão de Excluir - 'I'nicio ou 'F'im da linha
	    //$param['excluirPosicao']	= 'I';
	    //$parametros[colunaDescricaoExcluir] = '';
	    
	    $param['boxInfo']	= true;
	    $param['boxInfoParam']['collapse'] = true;
	    
	    $botao = [];
	    $botao['onclick'] = "setLocation('".getLink() . "index')";
	    $botao['tamanho'] = 'pequeno';
	    $botao['cor'] = 'danger';
	    $botao['texto'] = 'Voltar';
	    
	    
	    $param['boxInfoParam']['botoesTitulo'][] = $botao;
	    return $this->_crud->browser($param, 'Porcentagens de Repasse') . '';
	}
	
	function editar(){
	    $ret = '';
	    
	    $cod = getParam($_GET, 'rede');
	    if($cod != null){
	        $param = [];
	        $param['botaoCancela'] = true;
	        $param['linkCancela'] = getLink() . "redesCRUD";
	        $formulario = $this->_crud->formEditar($cod, $param);
	        if($formulario['retorno'] == 'sucesso'){
	            $ret .= $formulario['conteudo'];
	        }
	        
	    }
	    return $ret;
	}
	
	function incluir(){
	    $ret = '';
	    $param = [];
	    $param['botaoCancela'] = true;
	    $ret = $this->_crud->formIncluir($param);
	    
	    return $ret;
	}
	
	function gravar(){
	    $ret = '';
	    $operacao = getOperacao();
	    
	    if($operacao == 'E'){
	        if(count(getParam($_POST, 'formCRUD')) >0 ){
	            $ret = $this->_crud->gravaEdicao();
	        }
	    }elseif($operacao == 'I'){
	        if(count(getParam($_POST, 'formCRUD')) >0 ){
	            $ret = $this->_crud->gravaInclusao();
	        }
	    }
	    
	    if($ret == ''){
	        $ret = $this->redesCRUD();
	    }
	    return $ret;
	}
	
	private function getListaEmails(){
	    $ret = [];
	    $sql = 'select rede, email, parcial from gf_redes_percentual';
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        foreach ($rows as $row){
	            $ret[$row['rede']] = $row['email'];
	            $this->_parcial[$row['rede']] = $row['parcial'];
	        }
	    }
	    return $ret;
	}
	
	private function getListaRedesIgnorar(){
	    $this->ajustarConjuntos();
	    if(is_array($this->_redes_conjuntas) && count($this->_redes_conjuntas) > 0){
	        foreach ($this->_redes_conjuntas as $redes){
	            foreach ($redes as $rede){
	                if(!in_array($rede, $this->_lista_redes_ignorar)){
	                    $this->_lista_redes_ignorar[] = $rede;
	                }
	            }
	        }
	    }
	}
	
	private function ajustarConjuntos(){
	    if(count($this->_lista_negra) > 0 && count($this->_redes_conjuntas) > 0){
    	    $ret = [];
    	    foreach ($this->_redes_conjuntas as $conjuntos){
    	        $temp = [];
    	        foreach ($conjuntos as $rede){
    	            if(!in_array($rede, $this->_lista_negra) && !in_array($rede, $temp)){
    	                $temp[] = $rede;
    	            }
    	        }
    	        if(count($temp) > 1){
    	            $ret[] = $temp;
    	        }
    	    }
    	    $this->_redes_conjuntas = $ret;
	    }
	}
	
	private function montaRelatorioSchedule($rede){
	    $relatorio = new relatorio01($this->_programa,"");
	    $percentuais = $this->getListaRepasses($rede);
	    $relatorio->addColuna(array('campo' => 'cod'		  , 'etiqueta' => 'Cod'			        , 'tipo' => 'T', 'width' =>  70, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'cliente'	  , 'etiqueta' => 'Cliente'		        , 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'cnpj'		  , 'etiqueta' => 'CNPJ'		        , 'tipo' => 'T', 'width' => 120, 'posicao' => 'E'));
	    //$relatorio->addColuna(array('campo' => 'rede'		  , 'etiqueta' => 'Rede'		        , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'super'		  , 'etiqueta' => 'Rede Nome'	        , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $relatorio->addColuna(array('campo' => 'cidade'		  , 'etiqueta' => 'Cidade'		        , 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
	    //$relatorio->addColuna(array('campo' => 'coderc1'	  , 'etiqueta' => 'ERC 1'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    //$relatorio->addColuna(array('campo' => 'erc1'		  , 'etiqueta' => 'ERC 1'			    , 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
	    //$relatorio->addColuna(array('campo' => 'coderc2'	  , 'etiqueta' => 'ERC 2'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    //$relatorio->addColuna(array('campo' => 'erc2'		  , 'etiqueta' => 'ERC 2'			    , 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
	    
	    $chave = $this->conjuntoOL($rede);
	    
	    if(in_array($rede, $this->redes_com_ol) || $chave){
	        $relatorio->addColuna(array('campo' => 'med'		  , 'etiqueta' => 'Medicamentos'	    , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $relatorio->addColuna(array('campo' => 'div'		  , 'etiqueta' => 'Nao Medic.'	        , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $relatorio->addColuna(array('campo' => 'total'		  , 'etiqueta' => 'Total'			    , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        //$relatorio->addColuna(array('campo' => 'med_sem_ol'	  , 'etiqueta' => 'Medicamentos'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        //$relatorio->addColuna(array('campo' => 'div_sem_ol'	  , 'etiqueta' => 'Nao Medicamentos'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        //$relatorio->addColuna(array('campo' => 'total_sem_ol' , 'etiqueta' => 'Total'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        //$relatorio->addColuna(array('campo' => 'ol'			  , 'etiqueta' => 'OL'			        , 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	    }
	    else{
	        $relatorio->addColuna(array('campo' => 'med_sem_ol'		, 'etiqueta' => 'Medicamentos'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $relatorio->addColuna(array('campo' => 'div_sem_ol'		, 'etiqueta' => 'Nao Medicamentos', 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	        $relatorio->addColuna(array('campo' => 'total_sem_ol'	, 'etiqueta' => 'Total'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	    }
	    $relatorio->addColuna(array('campo' => 'generico'	  , 'etiqueta' => 'Generico'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	    $relatorio->addColuna(array('campo' => 'similar'	  , 'etiqueta' => 'Similar'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	    $relatorio->addColuna(array('campo' => 'repasse'	  , 'etiqueta' => 'Repasse'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	    return $relatorio;
	}
	
	private function conjuntoOL($conjunto){
	    $ret = false;
	    if(is_array($conjunto) && count($conjunto) > 0){
	        foreach($conjunto as $rede){
	            if(in_array($rede, $this->redes_com_ol)){
	                $ret = true;
	            }
	        }
	    }
	    return $ret;
	}
}

function lista_redes_nomes(){
    $ret = [];
    if(issetAppVar('lista_nomes_redes')){
        $ret = getAppVar('lista_nomes_redes');
    }
    else{
        $sql = "select codrede, descricao from pcredecliente order by descricao";
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if(isset($row['CODREDE']) && isset($row['DESCRICAO'])){
                    $ret[$row['CODREDE']] = $row['DESCRICAO'];
                }
            }
            putAppVar('lista_nomes_redes', $ret);
        }
    }
    return $ret;
}

function getNomeRede($id){
    $ret = '';
    $lista = lista_redes_nomes();
    if(isset($lista[$id])){
        $ret = $lista[$id];
    }
    return $ret;
}

function prodAGAFARMAfora(){
	$ret = [];
	$ret[] = 26034;
	$ret[] = 26033;
	$ret[] = 26032;
	$ret[] = 26037;
	$ret[] = 26036;
	$ret[] = 26035;
	$ret[] = 31524;
	$ret[] = 31523;
	$ret[] = 31522;
	$ret[] = 31527;
	$ret[] = 31526;
	$ret[] = 31525;
	$ret[] = 23119;
	$ret[] = 23099;
	$ret[] = 23101;
	$ret[] = 23100;
	$ret[] = 24639;
	$ret[] = 27937;
	$ret[] = 27935;
	$ret[] = 27934;
	$ret[] = 27939;
	$ret[] = 27938;
	$ret[] = 26514;
	$ret[] = 17807;
	$ret[] = 28166;
	$ret[] = 21890;
	$ret[] = 21785;
	$ret[] = 21784;
	$ret[] = 13198;
	$ret[] = 27069;
	$ret[] = 19864;
	$ret[] = 30532;
	$ret[] = 27408;
	$ret[] = 24333;
	$ret[] = 24332;
	$ret[] = 24331;
	$ret[] = 17786;
	$ret[] = 17785;
	$ret[] = 17784;
	$ret[] = 17783;
	$ret[] = 17777;
	$ret[] = 31759;
	$ret[] = 17795;
	$ret[] = 24726;
	$ret[] = 18192;
	$ret[] = 26512;
	$ret[] = 24765;
	$ret[] = 24764;
	$ret[] = 24763;
	$ret[] = 24762;
	$ret[] = 24761;
	$ret[] = 24760;
	$ret[] = 22819;
	$ret[] = 24635;
	$ret[] = 22821;
	$ret[] = 22820;
	$ret[] = 24637;
	$ret[] = 27644;
	$ret[] = 32179;
	$ret[] = 31070;
	$ret[] = 20683;
	$ret[] = 20281;
	$ret[] = 19868;
	$ret[] = 16193;
	$ret[] = 21077;
	$ret[] = 2336;
	$ret[] = 2337;
	$ret[] = 30005;
	$ret[] = 26190;
	$ret[] = 26191;
	$ret[] = 26189;
	$ret[] = 26188;
	$ret[] = 26194;
	$ret[] = 26195;
	$ret[] = 26192;
	$ret[] = 17804;
	$ret[] = 30197;
	$ret[] = 28153;
	$ret[] = 28154;
	$ret[] = 4350;
	$ret[] = 31530;
	$ret[] = 25638;
	$ret[] = 24759;
	$ret[] = 12554;
	$ret[] = 13097;
	$ret[] = 13102;
	$ret[] = 13105;
	$ret[] = 22048;
	$ret[] = 22049;
	$ret[] = 27110;
	$ret[] = 22050;
	$ret[] = 22052;
	$ret[] = 22059;
	$ret[] = 22064;
	$ret[] = 22068;
	$ret[] = 27075;
	$ret[] = 21816;
	$ret[] = 21815;
	$ret[] = 21955;
	$ret[] = 21956;
	$ret[] = 16678;
	$ret[] = 24724;
	$ret[] = 27413;
	$ret[] = 27414;
	$ret[] = 27415;
	$ret[] = 17792;
	$ret[] = 24583;
	$ret[] = 17791;
	$ret[] = 17790;
	return $ret;
}