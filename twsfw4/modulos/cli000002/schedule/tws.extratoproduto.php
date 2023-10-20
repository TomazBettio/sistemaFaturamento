<?php
/*
 * Data Criacao 10 de ago de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: tws.extratoProduto.inc.php
 * 
 * Descricao:
 * 
 * Alterções:
 *            19/11/2018 - Emanuel - Migração para intranet2
 *            27/01/2023 - Emanuel - Migração para intranet4
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class extratoproduto{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Dados
	var $_dados;
	
	
	//Vendas
	var $_vendas;
	
	//Picking
	var $_picking;
	
	//Indica se é teste
	var $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = '000002.extratoproduto';
		$this->_relatorio = new relatorio01($this->_programa,"");
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data At�'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){

	}
	
	private function montarColunasRelatorio($shedule = false){
	    $this->_relatorio->addColuna(array('campo' => 'cod'			, 'etiqueta' => 'Cod'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'prod'		, 'etiqueta' => 'Produto'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'obs'			, 'etiqueta' => 'Obs'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'est'			, 'etiqueta' => 'Estoque'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'bloq'		, 'etiqueta' => 'Bloqueado'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'avar'		, 'etiqueta' => 'Avaria'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'pend'		, 'etiqueta' => 'Pendente'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'reser'		, 'etiqueta' => 'Reservado'			, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'respen'		, 'etiqueta' => 'Res.+Pend.'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'indeniz'		, 'etiqueta' => 'Indenizavel'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'entrada'		, 'etiqueta' => 'Ult.Entrada'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'inventario'	, 'etiqueta' => 'Ult.Inventario'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'fatura'		, 'etiqueta' => 'Ult.Faturamento'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'pedido'		, 'etiqueta' => 'Ult.Ped.Venda'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
	    $this->_relatorio->addColuna(array('campo' => 'venda'		, 'etiqueta' => 'Venda Dia Ant.'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	    
	    $this->_relatorio->addColuna(array('campo' => 'rua'		, 'etiqueta' => 'Rua'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'predio'	, 'etiqueta' => 'Predio'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'nivel'	, 'etiqueta' => 'Nivel'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'apto'	, 'etiqueta' => 'Apto'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
	    $this->_relatorio->addColuna(array('campo' => 'pick'	, 'etiqueta' => 'Qt.Picking'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
	}

	function schedule($param){
		$emails = str_replace(',', ';', $param);
		
		$titulo = 'Extrato Produto. Data: '.date('d/m/Y');
		
		$this->montarColunasRelatorio(true);
		$this->_relatorio->setTitulo($titulo);
		$this->getVendaDiaAnterior();
		$this->getPicking();
		$dados = $this->getDados();
		$this->_relatorio->setDados($dados);
		unset($dados);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setEnviaTabelaEmail(false);
		$this->_relatorio->setToExcel(true,'extratoProduto_'.date('d.m.Y'));
		if(!$this->_teste){
		    $this->_relatorio->enviaEmail($emails,$titulo);
		    log::gravaLog('extrato_produto', "Email enviado para: $emails");
		}
		else{
		    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
		}
	}

	function getDados(){
		$ret = array();
		$pedido = array();
		
		$sql = "
				SELECT 
				     to_char(MAX(DATA),'DD/MM/YYYY') DTULTPEDIDO,
				    codprod
				FROM PCPEDI 
				GROUP BY CODPROD
				";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
		    foreach ($rows as $row){
		        $pedido[$row[1]] = $row[0];
		    }
		}
		
		$sql = "
SELECT 
       PCPRODUT.CODPROD,
       PCPRODUT.DESCRICAO,
       PCPRODUT.OBS2 OBS,
       NVL(PCEST.QTESTGER,0) ESTOQUE,
	   NVL(PCEST.QTBLOQUEADA,0) BLOQUEADO,
	   0 AVARIADA,
       NVL(PCEST.QTPENDENTE,0) PENDENTE,
	   NVL(PCEST.QTRESERV,0) RESERVADO,
	   (NVL(PCEST.QTRESERV,0) + NVL(PCEST.QTPENDENTE,0)) respen,	
       PCEST.QTINDENIZ,
       to_char(PCEST.DTULTENT,'DD/MM/YYYY') ENTRADA,
       to_char(PCEST.dtultinvent,'DD/MM/YYYY') INVENTARIO,
	   to_char(PCEST.DTULTSAIDA,'DD/MM/YYYY') SAIDA,
       '' PEDIDO --to_char((SELECT MAX(DATA) FROM PCPEDI WHERE CODPROD = PCPRODUT.CODPROD),'DD/MM/YYYY') DTULTPEDIDO
  FROM PCPRODUT
     , PCEST

 WHERE PCEST.CODPROD = PCPRODUT.CODPROD
	AND PCEST.CODFILIAL = 1

   --AND PCEST.CODFILIAL = 1
   --and PCEST.CODPROD = 673
 ORDER BY PCEST.CODPROD
				
				";
		$rows = query4($sql);
//echo "$sql \n";
		
		if(is_array($rows) && count($rows) > 0){
			//$i = 0;
			foreach($rows as $row){
			    $temp = array();
			    
			    $temp['cod'] 		= $row[0];
			    $temp['prod'] 		= $row[1];
			    $temp['obs'] 		= $row[2];
			    $temp['est'] 		= $row[3];
			    $temp['bloq'] 		= $row[4];
			    $temp['avar'] 		= $row[5];
			    $temp['pend'] 		= $row[6];
			    $temp['reser'] 		= $row[7];
			    $temp['respen']     = $row[8];
			    $temp['indeniz'] 	= $row[9];
			    $temp['entrada'] 	= $row[10];
			    $temp['inventario'] = $row[11];
			    $temp['fatura'] 	= $row[12];
			    $temp['pedido'] 	= isset($pedido[$row[0]]) ? $pedido[$row[0]] : '';
			    $temp['venda']		= isset($this->_vendas[$row[0]]) ? $this->_vendas[$row[0]] : 0;	
			    /*
				$ret[$i]['cod'] 		= $row[0];
				$ret[$i]['prod'] 		= $row[1];
				$ret[$i]['obs'] 		= $row[2];
				$ret[$i]['est'] 		= $row[3];
				$ret[$i]['bloq'] 		= $row[4];
				$ret[$i]['avar'] 		= $row[5];
				$ret[$i]['pend'] 		= $row[6];
				$ret[$i]['reser'] 		= $row[7];
				$ret[$i]['respen'] 		= $row[8];
				$ret[$i]['indeniz'] 	= $row[9];
				$ret[$i]['entrada'] 	= $row[10];
				$ret[$i]['inventario'] 	= $row[11];
				$ret[$i]['fatura'] 		= $row[12];
				$ret[$i]['pedido'] 		= isset($pedido[$row[0]]) ? $pedido[$row[0]] : '';
				$ret[$i]['venda']		= isset($this->_vendas[$row[0]]) ? $this->_vendas[$row[0]] : 0;	
				*/
				/*
				 * 15.09.16 Ajuste solicitado pelo Paulo
				 * adicionar o endere�os de picking dos produtos
				 */
				if(isset($this->_picking[$row[0]])){
				    $temp['rua']	= $this->_picking[$row[0]]['rua'];
				    $temp['predio']	= $this->_picking[$row[0]]['predio'];
				    $temp['nivel']	= $this->_picking[$row[0]]['nivel'];
				    $temp['apto']	= $this->_picking[$row[0]]['apto'];
				    $temp['pick']	= $this->_picking[$row[0]]['pick'];
				    
				    /*
					$ret[$i]['rua']		= $this->_picking[$row[0]]['rua'];
					$ret[$i]['predio']	= $this->_picking[$row[0]]['predio'];	
					$ret[$i]['nivel']	= $this->_picking[$row[0]]['nivel']; 	
					$ret[$i]['apto']	= $this->_picking[$row[0]]['apto'];
					$ret[$i]['pick']	= $this->_picking[$row[0]]['pick'];
					*/	
				}else{
				    $temp['rua']		= '';
				    $temp['predio']	= '';
				    $temp['nivel']	= '';
				    $temp['apto']	= '';
				    $temp['pick']	= '';
				    
				    /*
					$ret[$i]['rua']		= '';
					$ret[$i]['predio']	= '';
					$ret[$i]['nivel']	= '';
					$ret[$i]['apto']	= '';
					$ret[$i]['pick']	= '';
					*/
				}
				
				$ret[] = $temp;
				
				//$i++;
			}
		}
//print_r($ret);
		return $ret;	
	}
	
	function getPicking(){
		$sql = "
			select 
			    pcestendereco.codprod,
			    NVL(pcestendereco.qt,0) qt,
			    rua,
			    predio,
			    nivel,
			    apto
			from 
			    pcestendereco,
			    pcendereco
			where 
			    pcestendereco.codendereco = pcendereco.codendereco (+)
			    and pcestendereco.codendereco in (select codendereco from pcendereco where rua in (1,2))
			order by pcestendereco.codprod				
		";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
			    $chave = $row[0];
				$this->_picking[$chave]['pick'] 	= $row[1];
				$this->_picking[$chave]['rua'] 	    = $row[2];
				$this->_picking[$chave]['predio'] 	= $row[3];
				$this->_picking[$chave]['nivel'] 	= $row[4];
				$this->_picking[$chave]['apto'] 	= $row[5];
			}
		}
	}
	
	function getVendaDiaAnterior(){
		$dia = datas::getDataDias(-1);
		$sql = "
				SELECT
		            codprod,
		            sum(qt)
		        FROM
		            VIEW_VENDAS_RESUMO_FATURAMENTO VENDAS
		        WHERE
		            VENDAS.DTSAIDA BETWEEN TO_DATE('$dia', 'YYYYMMDD') and TO_DATE('$dia', 'YYYYMMDD')
		            AND NVL(VENDAS.CONDVENDA,0) NOT IN (4, 8, 10,13, 20, 98, 99)
		            AND NVL(VENDAS.CODFISCAL,0) NOT IN (522, 622, 722, 532, 632, 732)
		            and VENDAS.DTCANCEL IS NULL
		        GROUP BY
		            codprod
		        ORDER BY
		            codprod
				";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_vendas[$row[0]] = $row[1];
			}
		}
	}
	
}