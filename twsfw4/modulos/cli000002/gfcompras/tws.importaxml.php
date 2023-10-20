<?php
/*
* Data Criação: 07/08/2015 - 16:53:48
* Autor: Thiel
*
* Arquivo: tws.importaxml.inc.php
* 
* 
* Alterções:
*            30/01/2023 - Emanuel - Migração para intranet4
* 
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class importaxml{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';

	// Classe relatorio
	var $_relatorio;
	
	// Teste schedule
	private $_teste = false;
	
	function __construct(){
		set_time_limit(0);

		$this->_programa = '000002.importaxml';
		$this->_relatorio = new relatorio01($this->_programa,"");
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
			
	function index(){
		$ret = '';
		$dia = '20210615';
		$titulo = 'Importacao XML. Data: '.datas::dataS2D($dia);
		
		$this->_relatorio->setTitulo($titulo);
		$this->montarColunasRelatorio();
		$dados = $this->getDados($dia);
	
		$this->_relatorio->setDados($dados);
		$this->_relatorio->setToExcel(true,'Arq_xml_importados'.date('d.m.Y'));
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	function schedule($param){
		$param = str_replace(',', ';', $param);
		
		if(date('N') == 1){
			$dia = datas::getDataDias(-3);
		}else{
			$dia = datas::getDataDias(-1);
		}
		
		$titulo = 'Importacao XML. Data: '.datas::dataS2D($dia);
		
		$this->_relatorio->setTitulo($titulo);
		$this->montarColunasRelatorio(true);
		$dados = $this->getDados($dia);
		
		if(count($dados) > 0){
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setAuto(true);
			$this->_relatorio->setToExcel(true,'Arq_xml_importados'.date('d.m.Y'));
			if(!$this->_teste){
			    $this->_relatorio->enviaEmail($param,$titulo);
			    log::gravaLog('importa_xml', "Email enviado para: $param");
			}
			else{
			    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			}
		}
	}
	
	private function montarColunasRelatorio($schdule = false){
	    $this->_relatorio->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'Cod.'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'codfab'	, 'etiqueta' => 'Cod.Fab.'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'desc'	, 'etiqueta' => 'Produto'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'qt'		, 'etiqueta' => 'Quant.'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'unit'	, 'etiqueta' => 'Vl.Unit.'		, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
	    $this->_relatorio->addColuna(array('campo' => 'total'	, 'etiqueta' => 'Vl.Total'		, 'tipo' => 'V', 'width' => 110, 'posicao' => 'D'));
	    $this->_relatorio->addColuna(array('campo' => 'nota'	, 'etiqueta' => 'Nota'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'tipo'	, 'etiqueta' => 'Operação'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'saldo'	, 'etiqueta' => 'Saldo Disp.'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
	    $this->_relatorio->addColuna(array('campo' => 'diasest'	, 'etiqueta' => 'Dias Est.'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'fornec'	, 'etiqueta' => 'Fornecedor'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'marca'	, 'etiqueta' => 'Marca'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
	    $this->_relatorio->addColuna(array('campo' => 'emissao'	, 'etiqueta' => 'Dt.Emissao'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'importa'	, 'etiqueta' => 'Dt.Import.'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    
	    $this->_relatorio->addColuna(array('campo' => 'pedido'	, 'etiqueta' => 'Pedido'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'qtPed'	, 'etiqueta' => 'Qt.Pedido'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'qtDif'	, 'etiqueta' => 'Qt.Diferença'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $this->_relatorio->addColuna(array('campo' => 'valPed'	, 'etiqueta' => 'Vl.Pedido'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
	    $this->_relatorio->addColuna(array('campo' => 'valDif'	, 'etiqueta' => 'Vl.Diferença'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
	}
	
	function getDados($dia){
		$ret = array();
		$sql = "SELECT 
				    PCNFENTPREENT.NUMNOTA, 		-- 0
				    PCNFENTPREENT.DTEMISSAO, 	-- 1
				    PCNFENTPREENT.DTENT,		-- 2
				    PCNFENTPREENT.CODFORNEC,	-- 3
				    PCFORNEC.FORNECEDOR,		-- 4
				    PCMOVPREENT.CODPROD,		-- 5
				    PCPRODUT.CODFAB,			-- 6
				    PCPRODUT.DESCRICAO,			-- 7
				    PCMOVPREENT.QT,				-- 8
				    PCMOVPREENT.QTCONT,			-- 9
				    PCMARCA.MARCA,				-- 10
				    PCMOVPREENT.PUNIT,			-- 11
				    PCMOVPREENT.ST,				-- 12
				    (PCMOVPREENT.QT * PCMOVPREENT.PUNIT) TOTAL,		-- 13
				    (PCEST.QTEST - PCEST.QTBLOQUEADA) ESTOQUE,		-- 14
				    ((NVL(PCEST.QTESTGER,0)-NVL(PCEST.QTRESERV,0)-NVL(PCEST.QTBLOQUEADA,0)) / (DECODE(NVL(PCEST.QTGIRODIA,1), 0, 1, PCEST.QTGIRODIA))) ESTDIA,									-- 15
					PCMOVPREENT.CODOPER,
					PCMOVPREENT.TIPOEMBALAGEMPEDIDO,
					PCPRODUT.QTUNITCX,
					PCMOVPREENT.NUMPED
				FROM PCNFENTPREENT, PCMOVPREENT, PCFORNEC, PCPRODUT, PCMARCA, PCEST
				WHERE PCNFENTPREENT.NUMTRANSENT   = PCMOVPREENT.NUMTRANSENT
				    AND PCNFENTPREENT.CODFORNEC = PCFORNEC.CODFORNEC (+)
				    AND PCMOVPREENT.CODPROD = PCPRODUT.CODPROD (+)
				    AND PCPRODUT.CODMARCA = PCMARCA.CODMARCA (+)
				    AND PCMOVPREENT.CODPROD = PCEST.CODPROD AND PCEST.CODFILIAL = 1
				    AND (NVL(PCNFENTPREENT.TIPODESCARGA, '1') IN ('1','5','A','N', 'H')) 
				    AND PCNFENTPREENT.DTENT BETWEEN TO_DATE('$dia','YYYYMMDD') AND TO_DATE('$dia','YYYYMMDD')  
				ORDER BY 
					PCNFENTPREENT.CODFORNEC,
					PCPRODUT.DESCRICAO
				";
		$rows = query4($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
			    $temp = array();
			    
				$temp['cod'] 	= $row['CODPROD'];
				$temp['codfab'] 	= $row['CODFAB'];
				$temp['desc'] 	= $row['DESCRICAO'];
				if($row['TIPOEMBALAGEMPEDIDO'] == 'M'){
					$temp['qt'] 		= $row['QT'] * $row['QTUNITCX'];
					$temp['unit'] 	= round($row['PUNIT'] / $row['QTUNITCX'], 2);
				}else{
					$temp['qt'] 		= $row['QT'];
					$temp['unit'] 	= $row['PUNIT'];
				}
				$temp['total'] 	= $row[13];
				$temp['nota'] 	= $row['NUMNOTA'];
				$temp['saldo'] 	= $row[14];
				$temp['diasest'] = ceil($row[15]);
				$temp['fornec'] 	= $row[4];
				$temp['marca'] 	= $row[10];
				$temp['emissao'] = datas::dataMS2D($row[1]);
				$temp['importa'] = datas::dataMS2D($row[2]);
				$temp['tipo'] 	= $row['CODOPER'];
				
				//16/06/21 - Neto: Incluir informações do pedido
				$pedido = $this->getPedido($row['NUMPED'], $row['CODPROD']);
				
				$temp['pedido'] 	= !empty($row['NUMPED']) ? $row['NUMPED'] : $pedido['pedido'];
				$temp['qtPed'] 	    = $pedido['qt'];
				$temp['qtDif'] 	    = $temp['qt'] - $pedido['qt'];
				$temp['valPed'] 	= $pedido['valor'];
				$temp['valDif'] 	= $temp['unit'] - $pedido['valor'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function getPedido($pedido, $produto){
		$ret = [];
		$ret['qt'] = 0;
		$ret['valor'] = 0;
		
		if(!empty($pedido)){
			$sql = "SELECT QTPEDIDA, PLIQUIDO FROM PCITEM WHERE NUMPED = $pedido AND CODPROD = $produto";
			$rows = query4($sql);
			if(is_array($rows) && count($rows) > 0){
    			if(isset($rows[0]['QTPEDIDA'])){
    				$ret['qt'] = $rows[0]['QTPEDIDA'];
    				$ret['valor'] = $rows[0]['PLIQUIDO'];
    				$ret['pedido'] = '';
    			}
			}
		}else{
			$sql = "SELECT NUMPED, QTPEDIDA, PLIQUIDO FROM PCITEM WHERE CODPROD = $produto ORDER BY NUMPED DESC";
			$rows = query4($sql);
			if(is_array($rows) && count($rows) > 0){
    			if(isset($rows[0]['QTPEDIDA'])){
    				$ret['qt'] = $rows[0]['QTPEDIDA'];
    				$ret['valor'] = $rows[0]['PLIQUIDO'];
    				$ret['pedido'] = $rows[0]['NUMPED'];
    			}
			}
		}
		
		return $ret;
	}
}