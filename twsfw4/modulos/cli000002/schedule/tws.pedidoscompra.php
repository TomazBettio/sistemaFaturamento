<?php

/*
* Data Cria��o: 25/08/2014 - 10:16:29
* Autor: Thiel
*
* Arquivo: tws.pedidoscompra.inc.php
* 
* 
*  Alterções:
*             30/11/2018 - Emanuel - Migração para intranet2
*
*/ 

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class pedidoscompra{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Relatorio
	private $_relatorio;
	
	//Variaveis para calculo da margem
	var $_margem = [];
	
	//Indica se é teste
	var $_teste;

	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = "pedidoscompra";
	
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'pedido'		, 'etiqueta' => 'Pedido'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'direita'));
		
		$this->_relatorio->setTextoSemDados('Inclua os emails separados por ponto e virgula.');
		$this->_relatorio->setTitulo('Relacao de Pedidos de Compras efetuados');

		if(false){
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data'	, 'variavel' => 'DATA'	, 'tipo' => 'D', 'tamanho' => '10', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Email', 'variavel' => 'EMAIL'	, 'tipo' => 'T', 'tamanho' => '60', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
			$help = 'Não mostra itens que atendam a todas as seguintes regras:\n- Já tenha sido comprado anteriormente;\n- Estoque/dias < 90;\n- Aumento < 0,10%\n- Mergem Contribuição >= Margem Ideal';
			ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Filtra', 'variavel' => 'FILTRA'	, 'tipo' => 'T', 'tamanho' => '60', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => $help, 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'completo=Não;filtrado=Sim'));
		}
	}			
	
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();

		$data 	= datas::dataS2D($filtro['DATA']);
		$email 	= $filtro['EMAIL'];
		$filtra = $filtro['FILTRA'] ?? 'completo';
		
		if(!$this->_relatorio->getPrimeira()){
			
			$this->schedule($email, $filtra, $data);
			$this->_relatorio->setTextoSemDados("Email enviado!");
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}

	function schedule($param, $filtra = false, $data=''){
		$parametros = explode('|', $param);
		if(count($parametros) == 2){
			$filtra = strtolower($parametros[0]) == 'filtrado' ? true : false;
			$emails = $parametros[1];
		}else{
			$emails = $param;
			$filtra = strtolower($filtra) == 'filtrado' ? true : false;
		}
		$emails = str_replace(',', ';', $emails);
		
		$somente_dia = false;
		if(!empty($data)){
			$somente_dia = true;
		}
		//Se gerado após ao meio dia, pega somente do dia
		if(date('G') > 12 || !empty($data)){
			$retrocede = 0;
		}else{
			if($data == ''){
				if(date("N") == 1){
					//Segunda, então tem que enviar o de sexta
					$retrocede = -3;
				}else{
					$retrocede = -1;
				}
			}
		}
		if(empty($data)){
			$data = datas::dataS2D(datas::getDataDias($retrocede));
		}
		$dados = $this->getPedidos($data, $somente_dia);
		
		
		if(count($dados) > 0){
			$titulo = 'Relacao de Pedidos de Compras efetuados em '.$data;
			if($filtra){
				$titulo .= ' - FILTRADO';
				$colunas = 20;
			}else{
				$colunas = 18;
			}
			$param = [];
			$param['colunas'] = $colunas;
			$tab = new tabela_gmail01($param);
			$tab->abreTabela(1000);
			$tab->addTitulo($titulo);
			foreach ($dados as $dado){
				
				$itens = $this->getItens($dado['num'], $data, $filtra);
				
				if(count($itens) > 0){
					
					$tab->abreTR(true);
						$tab->abreTH('Pedido',2);
						$tab->abreTH('Fornecedor',6);
						$tab->abreTH('Vl. Avariado',2);
						$tab->abreTH('Verba em Aberto',2);
						$tab->abreTH('Valor',2);
						if($filtra){
							$tab->abreTH('Comprador',6);
						}else{
							$tab->abreTH('Comprador',4);
						}
					$tab->fechaTR();
					
					$tab->abreTR();
						$tab->abreTD($dado['num'],2,'centro');
						$tab->abreTD($dado['fornec'],6);
						$tab->abreTD($dado['avaria'],2,'direita');
						$tab->abreTD($dado['verba'],2,'direita');
						$tab->abreTD($dado['total'],2,'direita');
						if($filtra){
							$tab->abreTD($dado['comprador'],6);
						}else{
							$tab->abreTD($dado['comprador'],4);
						}
					$tab->fechaTR();
					
					if(!empty($dado['obs'])){
						$tab->abreTR();
						$tab->abreTD($dado['obs'],$colunas,'esquerda');
						$tab->fechaTR();
					}
					
					$tab->abreTR(true);
						$tab->abreTH('Cod',1);
						$tab->abreTH('Produto',5);
						$tab->abreTH('Quant.',1);
						$tab->abreTH('Est.Dias',1);
						$tab->abreTH('Ult.Entr.',1);
						$tab->abreTH('P. Comp Ant',1);
						$tab->abreTH('Pre&ccedil;o',1);
						$tab->abreTH('Custo',1);
						$tab->abreTH('% Aumento',1);
						$tab->abreTH('Comissao',1);
						//$tab->abreTH('P.Tabela',1);
						$tab->abreTH('Margem Contrib. Ideal',1);
						$tab->abreTH('Margem Contrib.',1);
						$tab->abreTH('FV QTDE RS',1);
						$tab->abreTH('Margem Contrib.',1);
						if($filtra){
							$tab->abreTH('Filtro',2);
						}
					$tab->fechaTR();
					
					foreach ($itens as $item){
						$tab->abreTR();
						$tab->abreTD($item['cod'],1);
						$tab->abreTD($item['prod'],5);
						$tab->abreTD($item['quant'],1,'centro');
						$tab->abreTD($item['dias'],1,'direita');
						$tab->abreTD($item['ultima'],1,'centro');
						$tab->abreTD($item['ant'],1,'direita');
						$tab->abreTD($item['preco'],1,'direita');
						$tab->abreTD($item['custo'],1,'direita');
						$tab->abreTD($item['aumento'].'%',1,'direita');
						$tab->abreTD($item['comissao'],1,'direita');
						$tab->abreTD($item['ideal'].'%',1,'direita');
						$tab->abreTD($item['contrib1'].'%',1,'direita');
						$tab->abreTD($item['9797'],1,'direita');
						$tab->abreTD($item['contrib3'].'%',1,'direita');
						if($filtra){
							$tab->abreTD($item['regra'],2);
						}
						$tab->fechaTR();
					}
					$tab->addLinhaBranco();
				}
			}	
			$tab->fechaTabela();
			$tab->addBR();
			$tab->termos();
			if(!$this->_teste){
				enviaEmailAntigo($emails, 'Pedidos de Compras. Data: '.$data,$tab);
			    log::gravaLog('PedidosCompras', 'Enviado email para: '.$emails);
			}
			else{
//echo "".$tab;
				enviaEmailAntigo('suporte@thielws.com.br', 'Pedidos de Compras. Data: '.$data,$tab);
			}
		}
		
		return '';
	}
	
	function getItens($pedido, $data, $filtra = false){
		$ret = [];
		$sql = "
				select 
                    pcitem.codprod,
                    pcprodut.descricao,
                    --round((pcitem.pliquido + pcitem.vlipi + pcitem.vlst + pcitem.vldespadicional),2) PRECO,
                    --pcitem.pliquido PRECO,
					round((pcitem.pliquido + pcitem.vlipi),2) PRECO,
                    pcitem.qtpedida,
                    pcest.valorultent,
                    --pcitem.ptabela,
                    (select pvenda from pctabpr where codprod = pcitem.codprod and numregiao = 1) PRECO_TAB,
                    decode(pcest.valorultent,0,0, (round((round((pcitem.pliquido + pcitem.vlipi + pcitem.vlst + pcitem.vldespadicional),2) / round(pcest.valorultent,2)) - 1,4) * 100)) AUMENTO, 
                    decode(pcest.qtgirodia,0,0,ROUND(((pcitem.qtpedida + pcest.qtest) / pcest.qtgirodia),0)) EST_DIAS,
                    ROUND(((pcitem.ptabela/round((pcitem.pliquido + pcitem.vlipi + pcitem.vlst + pcitem.vldespadicional),2))-1)*100,2) MARGEM,
                    (select MIN(pcdesconto.precofixopromocaomed) from pcdesconto where pcdesconto.codpromocaomed = 9794 and pcdesconto.codprod = pcitem.codprod) PROMO1,
                    (select MARGEM from pctabpr where codprod = pcitem.codprod and numregiao = 1) IDEAL,
                    (select MIN(pcdesconto.precofixopromocaomed) from pcdesconto where pcdesconto.codpromocaomed = 9797 and pcdesconto.codprod = pcitem.codprod) PROMO2
					
                from pcitem, pcprodut, pcest
                where pcitem.numped = $pedido
                    and pcitem.codprod = pcprodut.codprod (+)
                    and  pcitem.codprod = pcest.codprod
                    and pcest.codfilial = 1
                order by pcprodut.descricao 
		";
//echo "$sql <br>\n";
		$rows = query4($sql);
//print_r($rows);
		if(count($rows) > 0){
			foreach($rows as $row){
				$temp = [];
				$temp['cod'] 		= $row[0];
				$temp['prod'] 		= $row[1];
				$temp['preco'] 		= number_format($row[2], 2, ',', '.');
				$temp['quant'] 		= $row[3];
				$temp['ant'] 		= number_format($row[4], 2, ',', '.');
				$temp['tabela'] 	= number_format($row[5], 2, ',', '.');
				$temp['aumento']	= number_format($row[6], 1, ',', '.');
				$temp['dias'] 		= $row[7];
				$temp['margem'] 	= number_format($row[8], 1, ',', '.');
				$temp['9794'] 		= number_format($row[9], 2, ',', '.');
				$temp['ideal'] 		= number_format($row['IDEAL'], 2, ',', '.');
				$margem_ideal 		= $row['IDEAL'];
				$temp['9797'] 		= number_format($row['PROMO2'], 2, ',', '.');
				
				$temp['ultima'] 		= $this->getUltimaEntrada($row[0]);
				
				//Adicionado em 13/01/16 - por solicitacao do Anderson
				$sql = "SELECT gf_custopedcompra(".$row[0].",".$row[2].") FROM DUAL";
				//08/02/19 - Usando a função do Aless/Neto - Fundo de pobreza e cesta básica
				//$sql = "SELECT gf_custorepst(".$row[0].") FROM DUAL";
				$c = query4($sql);
				$custo = $c[0][0]; 
//echo "Custo: $custo <br> \n";
				$margem = $this->getMargem($row[0]);
				$temp['custo'] = number_format($custo, 2, ',', '.');
				$temp['comissao'] = $margem[0];
				$temp['contrib1'] = number_format($this->calculaMargem($row[5], $margem[2], $margem[3], $margem[0], $custo,$row[0]), 1, ',', '.');
				$margem_calculada = $this->calculaMargem($row[5], $margem[2], $margem[3], $margem[0], $custo,$row[0]);
				$temp['contrib2'] = number_format($this->calculaMargem($row[9], $margem[2], $margem[3], $margem[0], $custo,$row[0]), 1, ',', '.');
				$temp['contrib3'] = number_format($this->calculaMargem($row['PROMO2'], $margem[2], $margem[3], $margem[0], $custo,$row[0]), 1, ',', '.');
				
				if($row[9] > 0){
					$m = (($row[9]/$row[2])-1)*100 ;
				}else{
					$m = 0;
				}
				$temp['margem9794'] 	= number_format($m, 1, ',', '.');
				if($row['PROMO2'] > 0){
					$m = (($row['PROMO2']/$row[2])-1)*100 ;
				}else{
					$m = 0;
				}
				$temp['margem9797'] 	= number_format($m, 1, ',', '.');
				
				$adiciona = true;
				if($filtra){
					$regra_fora = [];
					$regra_fora_abrev = [];
					//Processa os 4 filtros
					$situacoes = 0;
					//produtos que a entrada NAO for a primeira     
					if(!empty($temp['ultima'])){
						$situacoes++;
					}else{
						$regra_fora[] = 'Primeira entrada<br>';
						$regra_fora_abrev[] = 'Primeira';
					}
					//Coluna do estoque-dias < 90
					if($temp['dias'] < 90){
						$situacoes++;
					}else{
						$regra_fora[] = 'Estoque maior que 90 dias';
						$regra_fora_abrev[] = '>90';
					}
					//% de aumento for < 0,10%
					if($temp['aumento'] < 0.10){
						$situacoes++;
					}else{
						$regra_fora[] = 'Aumento maior que 0.10';
						$regra_fora_abrev[] = '>0.10';
					}
					//Margem contribuição > margem ideal
					if($margem_calculada >= $margem_ideal){
						$situacoes++;
					}else{
						$regra_fora[] = 'Fora da margem ideal';
						$regra_fora_abrev[] = 'Margem';
					}
					
					$temp['regra'] = implode(',', $regra_fora_abrev);
					
					if($situacoes == 4){
						$adiciona = false;
						log::gravaLog('Pedido_compras_filtrados', $pedido.' - '.$data.' Produto: '.$temp['cod'].' Ultima Compra: '.$temp['ultima'].' Dias/Estoque: '.$temp['dias'].' %Aumento: '.$temp['aumento'].' Margens: '.$margem_calculada.' >= '.$margem_ideal);
					}else{
						log::gravaLog('Pedido_compras_filtrados_incluidos', $pedido.' - '.$data.' Produto: '.$temp['cod'].' Ultima Compra: '.$temp['ultima'].' Dias/Estoque: '.$temp['dias'].' %Aumento: '.$temp['aumento'].' Margens: '.$margem_calculada.' >= '.$margem_ideal);
						log::gravaLog('Pedido_compras_filtrados_incluidos', $regra_fora);
					}
				}
				
				if($adiciona){
					$ret[] = $temp;
				}
				
			}
		}
		
		return $ret;
	}
	
	function calculaMargem($preco,$imposto,$frete,$comissao,$custo,$produto){
		$margem = 0;
		if($preco > 0){
			$imp = ($preco * $imposto)/100;
			$fre = ($preco * $frete)/100;
			$com = ($preco * $comissao)/100;
			
			$cmv = $custo + $imp + $fre + $com;
			$margem = ($preco - $cmv)/$preco*100;
		}
		return $margem;
	}

	function getMargem($produto){
		if(!isset($this->_margem[$produto])){
			$sql = "
					SELECT PCTABPR.CODPROD                                                                                       
				    , NVL(PCPRODUT.PCOMREP1, 0) PCOMREP1                                                                      
				    , DECODE(DECODE(PCFILIAL.UF, NVL(PCREGIAO.UF, PCFILIAL.UF), 1, 0), 1, NVL(EST.CUSTOREAL, 0), NVL(EST.CUSTOREALSEMST, NVL(EST.CUSTOREAL, 0))) CUSTOREAL                                                              
				    , NVL(PCTRIBUT.CODICMTAB, 0) CODICMTAB                                                                                                               
				    , NVL(PCREGIAO.PERFRETETERCEIROS, 0) PERFRETETERCEIROS                                                                                               
				   FROM PCREGIAO                                                                                              
				      , PCTABPR                                                                                               
				      , PCTRIBUT                                                                                              
				      , PCEST EST                                                                                             
				      , PCPRODUT                                                                                              
				      , PCFILIAL                                                                                              
				      , PCFORNEC                                                                                            
				      , PCPRODFILIAL                                                                                         
				      , PCCONSUM                                                                                           
				   WHERE ((PCREGIAO.STATUS NOT IN ('I')) OR (PCREGIAO.STATUS IS NULL))                                      
				    AND PCTABPR.CODPROD     = PCPRODUT.CODPROD                                                                
				    AND PCTABPR.NUMREGIAO   = PCREGIAO.NUMREGIAO                                                              
				    AND PCTABPR.CODPROD   = $produto           
				    AND PCPRODUT.CODFORNEC  = PCFORNEC.CODFORNEC  
				    AND PCPRODFILIAL.CODPROD   = EST.CODPROD      
				    AND PCPRODFILIAL.CODFILIAL = EST.CODFILIAL    
				    AND EST.CODFILIAL     = PCFILIAL.CODIGO       
				    AND PCTABPR.CODPROD     = EST.CODPROD                                                                   
				    AND EST.CODFILIAL     = 1                                                                      
				    AND PCTABPR.CODST      = PCTRIBUT.CODST  
				 AND PCTABPR.NUMREGIAO IN(1)                                                                    
				ORDER BY PCTABPR.NUMREGIAO
					";
//echo "$sql <br>\n";			
			$rows = query4($sql);
			$this->_margem[$produto] = array(
											$rows[0]['PCOMREP1'],
											$rows[0]['CUSTOREAL'],
											$rows[0]['CODICMTAB'],
											$rows[0]['PERFRETETERCEIROS'],
											);
		}
		
		return $this->_margem[$produto];
	}
	
	function getPedidos($data, $somente_dia = false){
		$ret = [];
		
		$simbolo = '>=';
		if($somente_dia){
			$simbolo = '=';
		}
		
		$sql = "SELECT 
				    p.numped ,
				    p.dtemissao,
				    p.codfornec,
				    f.fornecedor,
				    p.vltotal,
				    p.obs,
				    p.obs2,
				    p.obs3,
				    p.obs4,
				    p.obs5,
				    p.obs6,
				    p.obs7,
				    p.codcomprador,
				    u.nome
				FROM pcpedido p, pcempr u, pcfornec f
				WHERE 
					p.DTEMISSAO $simbolo to_date('$data','DD/MM/YYYY')
					--p.DTEMISSAO >= to_date('20230301','YYYYMMDD') AND p.DTEMISSAO <= to_date('20230331','YYYYMMDD')  
				    and p.codcomprador = u.matricula (+)
				    and p.codfornec = f.codfornec (+)
				    and p.codfornec not in (14915)
				ORDER BY p.numped";
		$rows = query4($sql);
//echo "$sql <br>\n";
		if(count($rows) > 0){
			$i = 0;
			foreach($rows as $row){
				$ret[$i]['num'] 		= $row[0];
				$ret[$i]['data'] 		= $row[1];
				$ret[$i]['for'] 		= $row[2];
				$ret[$i]['fornec'] 		= $row[2].' - '.$row[3];
				$ret[$i]['total'] 		= number_format($row[4], 2, ',', '.');
				$ret[$i]['avaria'] 		= 0;
				$ret[$i]['obs'] 		= $row[5].''.$row[6].''.$row[7].''.$row[8].''.$row[9].''.$row[10].''.$row[11];
				$ret[$i]['comprador'] 	= $row[12].' - '.$row[13];
				$i++;
			}
		}
		
		//Verifica o total de avarias do fornecedor
		for($i=0;$i<count($ret);$i++){
			$ret[$i]['avaria'] = number_format($this->getAvaria($ret[$i]['for']), 2, ',', '.');
			$ret[$i]['verba'] = number_format($this->getVerba($ret[$i]['for']), 2, ',', '.');
		}
//print_r($ret);		
		return $ret;	
	}
	
	function getAvaria($fornec){
		$ret = 0;
		$sql = "select SUM(pcest.QTINDENIZ * pcest.custocont) from pcest where pcest.codprod in (select codprod from pcprodut where codfornec = $fornec)";
		$rows = query4($sql);
		
		return $rows[0][0];
	}
	
	function getVerba($fornec){
		$ret = 0;
		$sql = "
				SELECT ROUND(SUM(DECODE(TIPO,'D',VALOR,0)),2) VALORDEB,
				       ROUND(SUM(DECODE(TIPO,'C',VALOR,0)),2) VALORCRED,
				       (ROUND(SUM(DECODE(TIPO,'D',VALOR,0)),2) - ROUND(SUM(DECODE(TIPO,'C',VALOR,0)),2) ) saldo
				FROM PCMOVCRFOR
				WHERE NUMVERBA in (select 
				                        numverba
				                    from
				                        PCVERBA
				                    where
				                        codfornec = $fornec
				                        and formapgto in ('D','M'))
				";
		$rows = query4($sql);
//print_r($rows);	
		return ($rows[0][2]);
	}
	
	private function getUltimaEntrada($codprod){
		$ret = '';
		
		$sql = "SELECT DTULTENT FROM PCEST WHERE CODPROD = $codprod AND CODFILIAL = 1";
		$rows = query4($sql);
		
		if(isset($rows[0]['DTULTENT'])){
			$ret = datas::dataMS2D($rows[0]['DTULTENT']);
		}
		
//echo "$codprod - $ret ".$rows[0]['DTULTENT']." <br>\n";
		
		return $ret;
	}
}