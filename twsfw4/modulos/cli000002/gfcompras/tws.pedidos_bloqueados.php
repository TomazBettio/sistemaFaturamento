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
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class pedidos_bloqueados{
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
	
	//Hora que deve ser enviado o email
	private $_horaEmail;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = true;
		$this->_horaEmail = '08:00';
		
		$this->_programa = "pedidos_bloqueados";
		
		$param = array();
		$param['tabela']['scroll'] = false;
		$param['tabela']['info'] = false;
		$param['tabela']['filtro'] = false;
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$this->_relatorio->addColuna(array('campo' => 'pedido'		, 'etiqueta' => 'Pedido'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'direita'));
		
		$this->_relatorio->setTitulo('Relacao de Pedidos de Compras bloqueados');
		
		$hlp01 = "Se este campo for preenchido o relatório será enviado para este email (ou vários separados por ponto e vírgula ´;´). Se ficar em branco só será visto em tela.";
		ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '01', 'pergunta' => 'Email', 'variavel' => 'EMAIL'	, 'tipo' => 'T', 'tamanho' => '60', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => $hlp01, 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
	
	function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$email 	= $filtro['EMAIL'];
		
		if(!$this->_relatorio->getPrimeira()){
			$envia = empty($email) ? false : true;
			
			$tabela = $this->schedule($email, $envia);
			
			$this->_relatorio->setFooter($tabela);
			$this->_relatorio->setTextoSemDados("Email enviado!");
			//$dados = [];
			//$this->_relatorio->setDados($dados);
		}
		$ret .= $this->_relatorio;
		return $ret;
	}
	
	function schedule($param, $envia = true){
		$data = datas::getDataDias(-1);
		
		$dados = $this->getPedidos($data);
		
		$param = str_replace(',', ';', $param);
		$emails = $param;
		
		$email = $this->getEmail($dados, $data);
		
		if(empty($email)){
			$email = "<b>Sem pedidos bloqueados até ".datas::dataS2D($data)."</b>";
		}
		
		if($envia){
			if(!$this->_teste){
				$param = [];
				$param['dia'] 			= '';
				$param['hora'] 			= $this->_horaEmail;
				$param['programa']		= $this->_programa;
				$param['mensagem'] 		= $email;
				$param['destinatario'] 	= $emails;
				$param['assunto'] 		= 'Pedidos de Compras Bloqueados até: '.datas::dataS2D($data);
				
				agendaEmail($param);
				log::gravaLog('Pedidos_bloqueados', 'Enviado email para: '.$emails);
			}else{
				$param = [];
				$param['dia'] 			= '';
				$param['hora'] 			= $this->_horaEmail;
				$param['programa']		= $this->_programa;
				$param['mensagem'] 		= $email;
				$param['destinatario'] 	= 'suporte@thielws.com.br';
				$param['assunto'] 		= 'Pedidos de Compras Bloqueados até: '.datas::dataS2D($data);
				//$param['debug'] 		= true;
				
				agendaEmail($param);
				
				enviaEmail($param);
			}
		}
		
		return;
	}
	
	private function getEmail($dados, $data){
		$ret = '';
		if(count($dados) > 0){
			$tab = new tabela_gmail01();
			$tab->abreTabela(1000);
			$tab->addTitulo('Relacao de Pedidos de Compras bloqueados até '.$data, 15);
			foreach ($dados as $dado){
				
				$itens = $this->getItens($dado['num']);
				
				$tab->abreTR(true);
				$tab->abreTH('Pedido',2);
				$tab->abreTH('Fornecedor',4);
				$tab->abreTH('Data',1);
				$tab->abreTH('Vl. Avariado',2);
				$tab->abreTH('Verba em Aberto',2);
				$tab->abreTH('Valor',2);
				$tab->abreTH('Comprador',2);
				$tab->fechaTR();
				
				$tab->abreTR();
				$tab->abreTD($dado['num'],2,'centro');
				$tab->abreTD($dado['fornec'],4);
				$tab->abreTD($dado['data'],1);
				$tab->abreTD($dado['avaria'],2,'direita');
				$tab->abreTD($dado['verba'],2,'direita');
				$tab->abreTD($dado['total'],2,'direita');
				$tab->abreTD($dado['comprador'],2);
				$tab->fechaTR();
				
				if(!empty($dado['obs'])){
					$tab->abreTR();
					$tab->abreTD($dado['obs'],15);
					$tab->fechaTR();
				}
				
				$tab->abreTR();
				$tab->abreTD($dado['obsBloqueio'],15);
				$tab->fechaTR();
				
				$tab->abreTR(true);
				$tab->abreTH('Cod',1);
				$tab->abreTH('Produto',2);
				$tab->abreTH('Quant.',1);
				$tab->abreTH('Est.Dias',1);
				$tab->abreTH('P. Comp Ant',1);
				$tab->abreTH('Pre&ccedil;o',1);
				$tab->abreTH('Custo',1);
				$tab->abreTH('% Aumento',1);
				$tab->abreTH('Comissao',1);
				$tab->abreTH('P.Tabela',1);
				$tab->abreTH('Margem Contrib. Ideal',1);
				$tab->abreTH('Margem Contrib.',1);
				$tab->abreTH('FV QTDE RS',1);
				$tab->abreTH('Margem Contrib.',1);
				$tab->fechaTR();
				
				foreach ($itens as $item){
					$tab->abreTR();
					$tab->abreTD($item['cod'],1);
					$tab->abreTD($item['prod'],2);
					$tab->abreTD($item['quant'],1,'centro');
					$tab->abreTD($item['dias'],1,'direita');
					$tab->abreTD($item['ant'],1,'direita');
					$tab->abreTD($item['preco'],1,'direita');
					$tab->abreTD($item['custo'],1,'direita');
					$tab->abreTD($item['aumento'].'%',1,'direita');
					$tab->abreTD($item['comissao'],1,'direita');
					$tab->abreTD($item['tabela'],1,'direita');
					$tab->abreTD($item['ideal'].'%',1,'direita');
					$tab->abreTD($item['contrib1'].'%',1,'direita');
					$tab->abreTD($item['9797'],1,'direita');
					$tab->abreTD($item['contrib3'].'%',1,'direita');
					$tab->fechaTR();
				}
				
				//hISTORICO DO BLOQUEIO
				$tab->abreTR(true);
				$tab->abreTH('Usuário',2);
				$tab->abreTH('Ação',1);
				$tab->abreTH('Data.',2);
				$tab->abreTH('Rotina',2);
				$tab->abreTH('Motivo',8);
				$tab->fechaTR();
				
				if(count($dado['hist']) == 0){
					$tab->abreTR();
					$tab->abreTD('Sem histórico de bloqueio',15);
					$tab->fechaTR();
				}else{
					foreach ($dado['hist'] as $hist){
						$tab->abreTR();
						$tab->abreTD($hist['usuario'],2);
						$tab->abreTD($hist['acao'],1,'centro');
						$tab->abreTD($hist['data'],2,'centro');
						$tab->abreTD($hist['rotina'],2,'centro');
						$tab->abreTD($hist['motivo'],8);
						$tab->fechaTR();
					}
				}
				
				$tab->abreTR();
				$tab->abreTD('&nbsp;&nbsp;',16);
				$tab->fechaTR();
			}
			
			$tab->fechaTabela();
			$tab->addBR();
			$tab->termos();
			
			$ret .= $tab;
		}
		
		return $ret;
	}
	
	function getItens($pedido){
		$ret = array();
		$sql = "
				select
                    pcitem.codprod,
                    pcprodut.descricao,
                    --round((pcitem.pliquido + pcitem.vlipi + pcitem.vlst + pcitem.vldespadicional),2) PRECO,
                    pcitem.pliquido PRECO,
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
			$i = 0;
			foreach($rows as $row){
				$ret[$i]['cod'] 		= $row[0];
				$ret[$i]['prod'] 		= $row[1];
				$ret[$i]['preco'] 		= number_format($row[2], 2, ',', '.');
				$ret[$i]['quant'] 		= $row[3];
				$ret[$i]['ant'] 		= number_format($row[4], 2, ',', '.');
				$ret[$i]['tabela'] 		= number_format($row[5], 2, ',', '.');
				$ret[$i]['aumento']		= number_format($row[6], 1, ',', '.');
				$ret[$i]['dias'] 		= $row[7];
				$ret[$i]['margem'] 		= number_format($row[8], 1, ',', '.');
				$ret[$i]['9794'] 		= number_format($row[9], 2, ',', '.');
				$ret[$i]['ideal'] 		= number_format($row['IDEAL'], 2, ',', '.');
				$ret[$i]['9797'] 		= number_format($row['PROMO2'], 2, ',', '.');
				
				//Adicionado em 13/01/16 - por solicitacao do Anderson
				$sql = "SELECT gf_custopedcompra(".$row[0].",".$row[2].") FROM DUAL";
				//08/02/19 - Usando a função do Aless/Neto - Fundo de pobreza e cesta básica
				//$sql = "SELECT gf_custorepst(".$row[0].") FROM DUAL";
				$c = query4($sql);
				$custo = $c[0][0];
				//echo "Custo: $custo <br> \n";
				$margem = $this->getMargem($row[0]);
				$ret[$i]['custo'] = number_format($custo, 2, ',', '.');
				$ret[$i]['comissao'] = $margem[0];
				$ret[$i]['contrib1'] = number_format($this->calculaMargem($row[5], $margem[2], $margem[3], $margem[0], $custo,$row[0]), 1, ',', '.');
				$ret[$i]['contrib2'] = number_format($this->calculaMargem($row[9], $margem[2], $margem[3], $margem[0], $custo,$row[0]), 1, ',', '.');
				$ret[$i]['contrib3'] = number_format($this->calculaMargem($row['PROMO2'], $margem[2], $margem[3], $margem[0], $custo,$row[0]), 1, ',', '.');
				
				if($row[9] > 0){
					$m = (($row[9]/$row[2])-1)*100 ;
				}else{
					$m = 0;
				}
				$ret[$i]['margem9794'] 	= number_format($m, 1, ',', '.');
				if($row['PROMO2'] > 0){
					$m = (($row['PROMO2']/$row[2])-1)*100 ;
				}else{
					$m = 0;
				}
				$ret[$i]['margem9797'] 	= number_format($m, 1, ',', '.');
				$i++;
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
	
	function getPedidos($data){
		$ret = array();
		
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
				    u.nome,
					p.DTLIBERA,
					p.OBSBLOQUEIO
				FROM pcpedido p, pcempr u, pcfornec f
				WHERE
					(p.DTLIBERA IS NULL)
					AND p.DTEMISSAO <= to_date('$data','YYYYMMDD')
					AND p.DTEMISSAO >= to_date('20210101','YYYYMMDD')
				    and p.codcomprador = u.matricula (+)
				    and p.codfornec = f.codfornec (+)
				    and p.codfornec not in (14915)

				ORDER BY p.numped";
		$rows = query4($sql);
		//echo "$sql <br>\n";
		if(count($rows) > 0){
			foreach($rows as $row){
				$temp = [];
				$temp['num'] 		= $row[0];
				$temp['data'] 		= datas::dataMS2D($row[1]);
				$temp['for'] 		= $row[2];
				$temp['fornec'] 	= $row[2].' - '.$row[3];
				$temp['total'] 		= number_format($row[4], 2, ',', '.');
				$temp['avaria'] 	= 0;
				$temp['obs'] 		= $row[5].''.$row[6].''.$row[7].''.$row[8].''.$row[9].''.$row[10].''.$row[11];
				$temp['comprador'] 	= $row[12].' - '.$row[13];
				$temp['obsBloqueio']= $row['OBSBLOQUEIO'];
				
				$temp['hist']		= $this->getBloqueios($row[0]);
				
				//Verifica o total de avarias do fornecedor
				$temp['avaria'] = number_format($this->getAvaria($temp['for']), 2, ',', '.');
				//Verbas
				$temp['verba'] = number_format($this->getVerba($temp['for']), 2, ',', '.');
				
				$ret[] = $temp;
			}
		}
		//print_r($ret);
		return $ret;
	}
	
	private function getBloqueios($pedido){
		$ret = [];
		$sql = "
				SELECT
					PCPEDIDOMOTDESB.*, NOME_GUERRA
				FROM
					PCPEDIDOMOTDESB,
					PCEMPR
				WHERE
				    NUMPED = $pedido
					AND PCPEDIDOMOTDESB.MATRICULA = PCEMPR.MATRICULA (+)
				ORDER BY
				    DTACAO
		";
		//echo "$sql <br>\n";
		$rows = query4($sql);
		//print_r($rows);
		if(count($rows) > 0){
			foreach($rows as $row){
				$temp = [];
				$temp['acao'] 		= $row['ACAO'];
				$temp['usuario'] 	= $row['NOME_GUERRA'];
				$temp['data'] 		= $row['DTACAO'];
				$temp['rotina'] 	= $row['ROTINACAD'];
				$temp['motivo'] 	= $row['MOTIVO'];
				
				$ret[] = $temp;
			}
		}
		
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
}