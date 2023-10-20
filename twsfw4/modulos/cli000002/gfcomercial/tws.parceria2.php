<?php
/*
* Data Criação: 16/01/2015 - 12:13:57
* Autor: Thiel
*
* Arquivo: tws.parceria2.inc.php
* 
* Alterções:
*           31/10/2018 - Emanuel - Migração para intranet2
*           03/05/2019 - Alexandre - ALterado para filtrar por contrato, e quando isto for feito abrir por cliente
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class parceria2{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);

	// Nome do Programa
	var $_programa = '';
	
	//Campos tabela banco
	var $_campos;
	
	//Campos Texto
	var $_camposTexto;
	
	//Contratos
	var $_contratos;
	
	//Contrato por RCA
	var $_contRCA;
	
	//Contrato por Supervisor
	var $_contSuper;
	
	//Contrato Gerencia
	var $_contGerencia = [];
	
	// Tipo de relatorio - ADM/DIR/GER
	var $_tipo;
	
	// Campos a serem impressos
	var $_camposRel;
	
	//Dados Analiticos
	private $_dadosA = array();
	
	//Restrição de fornecedores em determinados clientes
	private $_restricaoFonec = [];
	
	//Contratos que devem pegar o ERC do cliente, e não do Cliente Principal
	private $_contratosERCcliente = array();
	
	function __construct(){
		set_time_limit(0);

		$this->_teste = false;
		
		$this->_contratosERCcliente[] = 4702;
		$this->_contratosERCcliente[] = 2546;
		$this->_contratosERCcliente[] = 6320;
		
		$this->_restricaoFonec[5507] = '894';
		
		$this->_programa = 'parceria2';
		
		$param = [];
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Tipo'		, 'variavel' => 'TIPO'		, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'ERC=ERC;ADM=Administrativo;GER=Gerencial'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Vigencia'	, 'variavel' => 'VIGENCIA'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'V=Vigentes;E=Encerrados;F=Futuros'));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Contratos'	, 'variavel' => 'CONTRATOS'	, 'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function getEstrutura(){
		switch ($this->_tipo) {
			case 'ADM':
				$this->_camposRel = array('super','regiao','vend','erc','acordo','descricao','cliente','dataini','datafim','tempofim','valfim','tipo','objetivo','premio','pago','venda1','venda12','vendaOL','venda','margem','indice','mediaR','mediaF','bonusG','bonusU','obs','vendaAnt');

				$this->_relatorio->addColuna(array('campo' => 'super'   	, 'etiqueta' => 'Regiao'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'regiao'  	, 'etiqueta' => 'Regiao Nome'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'vend'      	, 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'erc'			, 'etiqueta' => 'ERC Nome'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'acordo'		, 'etiqueta' => 'Acordo'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'descricao'  	, 'etiqueta' => 'Descricao'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'cliente'  	, 'etiqueta' => 'Clientes'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'dataini'	 	, 'etiqueta' => 'Data Ini'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'datafim'	 	, 'etiqueta' => 'Data Fim'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
					
				$this->_relatorio->addColuna(array('campo' => 'tempofim'	, 'etiqueta' => 'Tempo Fim'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'valfim'		, 'etiqueta' => 'Val. Fim'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'esquerda'));
				
				$this->_relatorio->addColuna(array('campo' => 'tipo'     	, 'etiqueta' => 'Tipo'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
				$this->_relatorio->addColuna(array('campo' => 'objetivo' 	, 'etiqueta' => 'Objetivo'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'premio'   	, 'etiqueta' => 'Premio'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'pago'		, 'etiqueta' => 'Premio Pago'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'direita'));
				
				$this->_relatorio->addColuna(array('campo' => 'venda1'   	, 'etiqueta' => 'Venda Medicamento'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'venda12'  	, 'etiqueta' => 'Venda Nao Medic.'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'vendaOL'  	, 'etiqueta' => 'Venda OL'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'venda'    	, 'etiqueta' => 'Venda Total'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'margem'		, 'etiqueta' => 'Margem'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				
				$this->_relatorio->addColuna(array('campo' => 'indice'		, 'etiqueta' => 'Indice'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'mediaR'		, 'etiqueta' => 'Media Real'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'mediaF'		, 'etiqueta' => 'Media Futura'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'bonusG'		, 'etiqueta' => 'Bonus Gerado'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'bonusU'		, 'etiqueta' => 'Bonus Usado'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'obs'			, 'etiqueta' => 'Obs'					, 'tipo' => 'T', 'width' => 400, 'posicao' => 'direita'));
				
				$this->_relatorio->addColuna(array('campo' => 'vendaAnt'	, 'etiqueta' => 'Venda Periodo<br>Anterior', 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				break;
			case 'GER':
				$this->_camposRel = array('acordo','descricao','venda','cmv','margem','Pmargem','margemB','PmargemB');

				$this->_relatorio->addColuna(array('campo' => 'acordo'		, 'etiqueta' => 'Acordo'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'descricao'  	, 'etiqueta' => 'Descricao'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'venda'    	, 'etiqueta' => 'Venda Total'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'cmv'			, 'etiqueta' => ''			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'margem'		, 'etiqueta' => ''			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'Pmargem'		, 'etiqueta' => ''			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'margemB'		, 'etiqueta' => ''			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'PmargemB'	, 'etiqueta' => ''			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				break;
			default:
				$this->_camposRel = array('regiao','erc','descricao','cliente','dataini','datafim','tempofim','tipo','objetivo','premio','venda1','venda12','vendaOL','venda');
					
				$this->_relatorio->addColuna(array('campo' => 'regiao'   , 'etiqueta' => 'Regiao'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'erc'      , 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descricao'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'cliente'  , 'etiqueta' => 'Clientes'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'dataini'	 , 'etiqueta' => 'Data Ini'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'datafim'	 , 'etiqueta' => 'Data Fim'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
				$this->_relatorio->addColuna(array('campo' => 'tempofim' , 'etiqueta' => 'Tempo Fim<br>(dias)'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'esquerda'));
				
				$this->_relatorio->addColuna(array('campo' => 'tipo'     , 'etiqueta' => 'Tipo'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'centro'));
				$this->_relatorio->addColuna(array('campo' => 'objetivo' , 'etiqueta' => 'Objetivo'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'premio'   , 'etiqueta' => 'Premio'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'direita'));
					
				$this->_relatorio->addColuna(array('campo' => 'venda1'   , 'etiqueta' => 'Venda Medicamento'	, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'venda12'  , 'etiqueta' => 'Venda Nao Medic.'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'vendaOL'  , 'etiqueta' => 'Venda OL'				, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				$this->_relatorio->addColuna(array('campo' => 'venda'    , 'etiqueta' => 'Venda Total'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'direita'));
				break;
		}
		$this->setaCampos();
	}
				
	function index(){
		$ret = '';
		
		$filtro = $this->_relatorio->getFiltro();
		
		$tipo = $filtro['TIPO'];
		$vigencia = $filtro['VIGENCIA'];
		$contratos = $filtro['CONTRATOS'];
		
		$this->_relatorio->setTitulo('Contratos de Parceria. Data: '.date('d/m/Y'));

		$this->_tipo = $tipo;
		$this->getEstrutura();
		
		if(!$this->_relatorio->getPrimeira()){
			
			$this->pegaDadosTabela($vigencia, $contratos);
//print_r($this->_contGerencia);
			$this->_relatorio->setDados($this->_contGerencia);
			$this->_relatorio->setToExcel(true);
		}	
		$ret .= $this->_relatorio;
		
		return $ret;
	}

	function schedule($param){
		if(strpos($param, '@') !== false){
			$emails = str_replace(',', ';', $param);
			
			$this->_tipo = 'ERC';
			$this->getEstrutura();
			
			$titulo = 'Contratos de Parceria. Data: '.date('d/m/Y');
		
			$this->_relatorio->setTitulo($titulo);
			log::gravaLog("parcerias", "Titulo, Selecionando registros...");
			$this->pegaDadosTabela();
			
			$this->_relatorio->setAuto(true);
			$this->_relatorio->setToExcel(true,'Contratos_de_Parceria_'.date('d.m.Y'));
				
			foreach ($this->_rca as $rca => $em){
				$this->_relatorio->setDados($this->_contRCA[$rca]);
				if(!$this->_teste){
					$this->_relatorio->enviaEmail($em,$titulo);
					log::gravaLog("parcerias", "Enviado email ERC $rca: ".$em);
				}else{
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',"ERC: ".$rca." - ".$titulo);
				}
			}
			foreach ($this->_super as $super => $em){
				$this->_relatorio->setDados($this->_contSuper[$super]);
				if(!$this->_teste){
					$this->_relatorio->enviaEmail($em,$titulo);
					log::gravaLog("parcerias", "Enviado email Regiao $super: ".$em);
				}else{
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',"Regiao: ".$super." - ".$titulo);
				}
			}
			
		
			$this->_relatorio->setDados($this->_contGerencia);
			if(!$this->_teste){
				$this->_relatorio->enviaEmail($emails,$titulo);
				log::gravaLog("parcerias", "Enviado email: ".$emails);
			}else{
				$this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
			}
		}else{
			//Calculo
echo "<br>\nCalculando<br>\n";
			$this->setaCampos();
echo "<br>\nGet Contratos<br>\n";
			$this->getContratos(''); 	// Todos
		
			//$this->getContratos('N');	// Novos
echo "<br>\nCalculando Vendas<br>\n";
			$this->calculaVendas();
echo "<br>\nCalculando Médias<br>\n";
			$this->calculaMedias();
echo "<br>\nVerificando Pagos<br>\n";
			$this->getPagos();
echo "<br>\nCalculando Flex<br>\n";
			$this->calculaFlex();
echo "<br>\nCalculando Margem<br>\n";
			$this->calculaMargem();
			
echo "<br>\nGravando Tabela<br>\n";
			$this->gravaTabela();
		}
	}
	
	private function getPagos(){
		$pago = array();
		$sql = "SELECT * FROM gf_parceriaspagos";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$contrato = $row['acordo'];
				if($row['valor'] <> 0){
					$pago[$contrato] = $row['valor'];
				}
			}
		}
		
		if(count($pago) > 0){
			foreach ($pago as $acordo => $valor){
				if(isset($this->_contratos[$acordo])){
					$this->_contratos[$acordo]['pago'] = $valor;
				}
			}
		}
	}
	
	function pegaDadosTabela($vigencia = '', $contratos = ''){
		//data::getDataDias(),data::getDataDias(-15)
		$where = '';
		$hoje = datas::getDataDias();
		switch ($vigencia) {
			case 'T':
				$where = "1=1";
				break;
			case 'V':
				$where = "dataini <= '$hoje' AND datafim >= '$hoje'";
				break;
			case 'E':
				$where = "datafim < '$hoje'";
				break;
			case 'F':
				$where = "dataini > '$hoje'";
				break;
			default:
				$where = "dataini <= '$hoje' AND datafim >= '".datas::getDataDias(-15)."'";
				break;
		}
		
		if(!empty(trim($contratos))){
			$where .= " AND acordo IN ($contratos)";
		}

		$sql = "SELECT * FROM gf_parcerias WHERE $where ORDER BY super, vend";
//echo "$sql <br>";
		$rows = query($sql);
	
		if(count($rows) > 0){
			foreach ($rows as $row){
				//$acordo = $row['acordo'];
				$super = $row['super'];
				$erc = $row['vend'];
				
				//Ajuste no 
				
				$temp = array();
				foreach ($this->_campos as $key => $campo){
					$key = $campo;
					if(array_search($key,$this->_camposRel) !== false ){
						if(array_search($key, array('dataini','datafim','premio')) === false){
							$temp[$key] = $row[$campo];
						}
						if($key == 'dataini' || $key == 'datafim'){
							$temp[$key] = datas::dataS2D($row[$campo]);
						}
						if($key == 'premio'){
							$premio = $row[$campo];
							if($premio < 100 && $premio > 0){
								$temp[$key] 	= $premio." %";
							}else{
								$temp[$key] 	= number_format($premio, 2, ',', '.');
							}
						}
					}
				}
				
				//Se for analitico, abre os clientes
				if(!empty(trim($contratos))){
					$sql = "SELECT * FROM  gf_parceriasClientes WHERE acordo IN ($contratos)";
					$rows = query($sql);
//print_r($rows);
					if(is_array($rows) && count($rows) > 0){
						foreach ($rows as $row2) {
							$temp2 = array();
							
							$temp2['super'] 	= $row['super'];
							$temp2['regiao'] 	= $row['regiao'];
							$temp2['vend'] 		= $row['vend'];
							$temp2['erc'] 		= $row['erc'];
							$temp2['descricao'] = $row['descricao'];
							
							$temp2['acordo'] 	= $row2['acordo'];
							$temp2['cliente'] 	= $row2['cliente'].'-'.$this->getClienteNome($row2['cliente']);
							$temp2['venda1'] 	= $row2['venda1'];
							$temp2['venda12'] 	= $row2['venda12'];
							$temp2['vendaOL'] 	= $row2['vendaOL'];
							$temp2['venda'] 	= $row2['venda'];
							$temp2['bonusG'] 	= $row2['bonusG'];
							$temp2['bonusU'] 	= $row2['bonusU'];
							$temp2['margem'] 	= $row2['margem'];
							$temp2['vendaAnt'] 	= $row2['vendaAnt'];
							
							$this->_contRCA[$erc][]		= $temp2;
							$this->_contSuper[$super][]	= $temp2;
							$this->_contGerencia[]		= $temp2;
							
						}
					}
				}
				
				$this->_contRCA[$erc][]		= $temp;
				$this->_contSuper[$super][]	= $temp;
				$this->_contGerencia[]		= $temp;
				
				//Monta lista de ERCs
				if(!isset($this->_rca[$erc])){
					$this->_rca[$erc] = getEmailERC($erc);
				}
					
				//Monta lista de Supervisores
				if(!isset($this->_super[$super])){
					$this->_super[$super] = getEmailCoordenador($super);
				}
				
			}
		}
	}
	
	private function getClienteNome($cliente){
		$ret = '';
		$sql = "SELECT CLIENTE FROM PCCLIENT WHERE CODCLI = $cliente";
		$rows = query4($sql);
		if(isset($rows[0]['CLIENTE'])){
			$ret = $rows[0]['CLIENTE'];
		}
		
		return $ret;
	}
	
	function getContratos($tipo = ''){
		$sql = "
			select
				PCACORDOPARCERIA.CODACORDO,
			    PCACORDOPARCERIA.DESCRICAO,
			    TO_CHAR(PCACORDOPARCERIA.DTVIGENCIAINI,'YYYYMMDD') DATAINI,
			    TO_CHAR(PCACORDOPARCERIA.DTVIGENCIAFIN,'YYYYMMDD') DATAFIM,
			    PCACORDOPARCERIA.TIPOMETA,
			    PCACORDOPARCERIA.META,
			    PCACORDOPARCERIA.PREMIACAO,
			    CONVERT( PCACORDOPARCERIA.OBSERVACAO,'WE8ISO8859P1','UTF8') OBSERVACAO,
				(PCACORDOPARCERIA.DTVIGENCIAFIN - SYSDATE) FALTA,
				(PCACORDOPARCERIA.DTVIGENCIAFIN - PCACORDOPARCERIA.DTVIGENCIAINI) DURACAO
			FROM
			    PCACORDOPARCERIA
			WHERE
				1 = 1
				--AND PCACORDOPARCERIA.CODACORDO IN (4402,4222,3882)
				--PCACORDOPARCERIA.DTVIGENCIAFIN >= SYSDATE - 15
				--AND PCACORDOPARCERIA.DTVIGENCIAINI <= SYSDATE
				--AND PCACORDOPARCERIA.CODACORDO = 1762
				--AND STATUS <> 'E'
			   -- PCACORDOPARCERIA.CODACORDO = PCACORDOPARCERIACLI.CODACORDO
--AND PCACORDOPARCERIA.CODACORDO = 6017
				--AND DESCRICAO like '%5103%'
				-- Para não precisar recalcular todos, ignora os bem antigos
				AND PCACORDOPARCERIA.dtvigenciafin >= TO_DATE('20210101','YYYYMMDD')
				";
		// Se for indicado 'N'ovos, pega somente contratos em vigencia e futuros
		if($tipo == 'N'){
			$sql .= " AND PCACORDOPARCERIA.dtvigenciafin >= SYSDATE ";
		}
		
		$rows = query4($sql);

		foreach ($rows as $row){
			$clientes = $this->getClientes($row[0]);
			$temp = array();
				
			$temp['cliente'] = $clientes[0];
			if(strlen($clientes[4]) > 0){
				$temp['cliente'] .= ','.$clientes[4];
			}
			$temp['acordo']		= $row['CODACORDO'];
			$temp['regiao'] 	= $clientes[2];
			$temp['erc'] 		= $clientes[3];
			$temp['super']		= $clientes['super'];
			$temp['vend']		= $clientes['vend'];
			$temp['acordo'] 	= $row[0];
			$temp['descricao']	= str_replace("'","´",$row[1]);
			$temp['dataini']	= $row[2];
			$temp['datafim']	= $row[3];
			$temp['tipo'] 		= $row[4];
			$temp['objetivo']	= empty($row[5]) ? 0 : $row[5];
			$temp['premio'] 	= empty($row[6]) ? 0 : $row[6];
			$temp['tempofim'] 	= $row[8] > 0 ? $row[8] : 0;
			$temp['obs'] 		= $row[7];
			$temp['duracao']	= $row['DURACAO']; 
			$temp['vendaAnt']	= 0;
			
			foreach ($this->_campos as $campo){
				if(!isset($temp[$campo])){
					$temp[$campo] = 0;
				}
			}
			
			$this->_contratos[$temp['acordo']] = $temp;
//break;
		}
//print_r($this->_contratos);
	}
	
	function calculaVendas(){
		foreach ($this->_contratos as $contrato){
			$acordo 	= $contrato['acordo'];
			$cliente 	= $contrato['cliente'];
			$dataIni 	= $contrato['dataini'];
			$dataFim 	= $contrato['datafim'];
			
			$this->_dadosA[$acordo] = array();
			$venda 		= $this->getVendas($acordo,$cliente, $dataIni, $dataFim);
			if(count($venda) > 0){
				foreach ($venda as $clienteV => $vendas1){
					foreach ($vendas1 as $depto => $v){
						if($depto == 1){
							$this->_contratos[$acordo]['venda1'] += $v;
							$this->_dadosA[$acordo][$clienteV]['venda1'] = $v;
						}else{
							$this->_contratos[$acordo]['venda12'] += $v;
							$this->_dadosA[$acordo][$clienteV]['venda12'] = $v;
						}
					}
				}
			}
			$vendaOL 	= $this->getVendas($acordo,$cliente, $dataIni, $dataFim,'OL');
			if(count($vendaOL) > 0){
				foreach ($vendaOL as $cliente => $vendas1){
					foreach ($vendas1 as $depto => $v){
						if(!isset($this->_dadosA[$acordo][$cliente]['vendaOL'])){
							$this->_dadosA[$acordo][$cliente]['vendaOL'] = 0;
						}
						$this->_contratos[$acordo]['vendaOL'] += $v;
						$this->_dadosA[$acordo][$cliente]['vendaOL'] += $v;
//echo "$v \n";
					}
				}
			}
//print_r($this->_dadosA[$acordo]);
//print_r($vendaOL);
			$this->_contratos[$acordo]['venda'] = $this->_contratos[$acordo]['venda1'] + $this->_contratos[$acordo]['venda12'] + $this->_contratos[$acordo]['vendaOL'];
			$this->_contratos[$acordo]['valfim'] 	= $this->_contratos[$acordo]['objetivo'] - $this->_contratos[$acordo]['venda'] > 0 ? $this->_contratos[$acordo]['objetivo'] - $this->_contratos[$acordo]['venda'] : 0;
			
			//Ajusta venda total por cliente
			if(count($this->_dadosA[$acordo]) > 0){
				foreach ($this->_dadosA[$acordo] as $cliente => $venda){
					$this->_dadosA[$acordo][$cliente]['venda'] = 0;
					if(isset($this->_dadosA[$acordo][$cliente]['venda1'])){
						$this->_dadosA[$acordo][$cliente]['venda'] += $this->_dadosA[$acordo][$cliente]['venda1'];
					}
					if(isset($this->_dadosA[$acordo][$cliente]['venda12'])){
						$this->_dadosA[$acordo][$cliente]['venda'] += $this->_dadosA[$acordo][$cliente]['venda12'];
					}
					//if(isset($this->_dadosA[$acordo][$cliente]['vendaOL'])){
					//	$this->_dadosA[$acordo][$cliente]['vendaOL'] += $this->_dadosA[$acordo][$cliente]['vendaOL'];
					//}
				}
			}
			
			$dataI 		= $contrato['dataini'];
			$duracao 	= $contrato['duracao'];
			$dataFim	= datas::getDataDias(-1, $dataI);
			$dataIni 	= datas::getDataDias($duracao * -1, $dataFim);
			
			$vendasAnterior = $this->getVendas($acordo,$cliente, $dataIni, $dataFim,'TODOS','1,12');
			//if(count($vendasAnterior) >0 ){
			//	print_r($vendasAnterior);
			//	die();
			//}
			if(count($vendasAnterior) > 0) {
				foreach ($vendasAnterior as $cliente => $vendas1){
					foreach ($vendas1 as $depto => $v){
						if(!isset($this->_dadosA[$acordo][$cliente]['vendaAnt'])){
							$this->_dadosA[$acordo][$cliente]['vendaAnt'] = 0;
						}
						$this->_dadosA[$acordo][$cliente]['vendaAnt'] += $v;
					}
				}
				
			}
//print_r($this->_contratos);
//print_r($this->_dadosA);
			//$venda = $venda1 + $venda12 + $vendaOL;
			
			//$this->_contratos[$acordo]['venda1'] 	= $venda1;
			//$this->_contratos[$acordo]['venda12'] 	= $venda12;
			//$this->_contratos[$acordo]['vendaOL'] 	= $vendaOL;
			//$this->_contratos[$acordo]['venda'] 	= $venda;
			
			
		}
//print_r($this->_dadosA);die();
	}
	
	function calculaMedias(){
		foreach ($this->_contratos as $acordo =>$contrato){
			$diaHoje = datas::data_hoje();
			if($contrato['dataini'] > $diaHoje){
				$this->_contratos[$acordo]['mediaR'] = 0;
				$this->_contratos[$acordo]['mediaF'] = 0;
			}elseif($contrato['datafim'] < $diaHoje){
				$this->_contratos[$acordo]['mediaF'] = 0;
				$meses = datas::calculaDifMesesS($contrato['dataini'],$contrato['datafim']);
				if($meses == 0){
					$meses = 1;
				}
				$this->_contratos[$acordo]['mediaR'] = $contrato['venda'] / $meses;
			}else{
				$meses = datas::calculaDifMesesS($contrato['dataini'],$diaHoje) - 1;
				if($meses == 0){
					$meses = 1;
				}
				$this->_contratos[$acordo]['mediaR'] = $contrato['venda'] / $meses;
				$meses = datas::calculaDifMesesS($diaHoje,$contrato['datafim']);
				if($meses == 0){
					$meses = 1;
				}
				$this->_contratos[$acordo]['mediaF'] = $contrato['valfim'] / $meses;
			}
		}
	}
	
	function calculaFlex(){
		/*/
		$sql = "select PCACORDOPARCERIA.codacordo, (pclogrca.vlcorrente - pclogrca.vlcorrenteant) VALOR
				from PCACORDOPARCERIA, PCLOGRCA
				where pclogrca.numtransvenda in (select pcpedc.numtransvenda from pcpedc where pcpedc.posicao = 'F' and pcpedc.CODACORDOPARCERIA = PCACORDOPARCERIA.codacordo)
				    AND (pclogrca.vlcorrente - pclogrca.vlcorrenteant) <> 0
				";
		
		$sql = "
				select 
				    PCACORDOPARCERIA.codacordo, 
				    (SELECT CODCLI FROM PCPEDC WHERE PCPEDC.numtransvenda = pclogrca.numtransvenda) CODCLI,
				    (pclogrca.vlcorrente - pclogrca.vlcorrenteant) VALOR
				from 
				    PCACORDOPARCERIA, 
				    PCLOGRCA
				where 
				    pclogrca.numtransvenda in (select distinct pcpedc.numtransvenda from pcpedc where pcpedc.posicao = 'F' and pcpedc.CODACORDOPARCERIA IS NOT NULL and pcpedc.CODACORDOPARCERIA = PCACORDOPARCERIA.codacordo AND pcpedc.CODACORDOPARCERIA = 631)
				    AND (pclogrca.vlcorrente - pclogrca.vlcorrenteant) <> 0
				";
		/*/
		$sql = "select 
                    PCACORDOPARCERIA.codacordo, 
                    PCPEDC.CODCLI,
                    (pclogrca.vlcorrente - pclogrca.vlcorrenteant) VALOR
                from 
                    PCACORDOPARCERIA, 
                    PCLOGRCA,
                    PCPEDC
                where 
                    pclogrca.numtransvenda = pcpedc.numtransvenda 
                    AND pcpedc.posicao = 'F' 
                    and pcpedc.CODACORDOPARCERIA IS NOT NULL 
                    and pcpedc.CODACORDOPARCERIA = PCACORDOPARCERIA.codacordo 
                    --AND pcpedc.CODACORDOPARCERIA = 631
                    AND (pclogrca.vlcorrente - pclogrca.vlcorrenteant) <> 0
				";
		
		$rows = query4($sql);
//print_r($rows);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$acordo = $row['CODACORDO'];
				$cliente = $row['CODCLI'];
				$valor = $row['VALOR'];
				if(isset($this->_contratos[$acordo])){
					if($valor > 0){
						$this->_contratos[$acordo]['bonusG'] += $valor;
						if(!isset($this->_dadosA[$acordo][$cliente]['bonusG'])){
							$this->_dadosA[$acordo][$cliente]['bonusG'] = 0;
						}
						$this->_dadosA[$acordo][$cliente]['bonusG'] += $valor;
					}else{
						$this->_contratos[$acordo]['bonusU'] += ($valor * -1);
						if(!isset($this->_dadosA[$acordo][$cliente]['bonusU'])){
							$this->_dadosA[$acordo][$cliente]['bonusU'] = 0;
						}
						$this->_dadosA[$acordo][$cliente]['bonusU'] += ($valor * -1);
					}
				}
			}
		}
//print_r($this->_dadosA[631]);
//print_r($this->_contratos[631]['bonusG']);
	}
	
	private function calculaMargem(){
		foreach ($this->_contratos as $contrato){
			$param = array();
			$acordo 	= $contrato['acordo'];
			$dataIni 	= $contrato['dataini'];
			$dataFim 	= $contrato['datafim'];
			
			$depto = $this->getDepto($acordo);
			if($depto != ''){
				$param['depto'] = $depto;
			}
			$produtos = $this->getProdutos($acordo);
			if($produtos != ''){
				$param['produto'] = $produtos;
			}
			$linhas = $this->getLinha($acordo);
			if($linhas != ''){
				$param['linha'] = $linhas;
			}
			$param['cliente'] 	= $contrato['cliente'];
			
			$campo = '';
			$margem = getMargemTWS($dataIni, $dataFim, $param, $campo);
			if($margem == '' || is_array($margem)){
				$margem = 0;
			}
			$this->_contratos[$acordo]['margem'] = $margem;
			
			//Margem por cliente
			$campo = [];
			$campo[] = 'CLIENTE';
			$margem = getMargemTWS($dataIni, $dataFim, $param, $campo);
			
			if(count($margem) > 0){
				foreach ($margem as $cliente => $m){
					if($m['margem'] == ''){
						$m['margem'] = 0;
					}
					$this->_dadosA[$acordo][$cliente]['margem'] = $m['margem'];
				}
			}

		}
//print_r($this->_dadosA);
	}
	
	function getVendas($acordo,$cliente, $dataIni, $dataFim, $origem = '', $depto = ''){
		$ret = array();
		if($depto == ''){
			$depto = $this->getDepto($acordo);
		}
		$produtos = $this->getProdutos($acordo);
		$param = array();
		$param['cliente'] 	= $cliente;
		$param['depto']		= $depto;
		$param['produto']	= $produtos;
		
		if(isset($this->_restricaoFonec[$acordo])){
			$param['fornecedorFora']	= $this->_restricaoFonec[$acordo];
		}
	
		if($origem == ''){
			$param['origem'] = 'NOL';
		}elseif($origem <> 'TODOS'){
			$param['origem'] = 'OL';
		}
		
		$campos = array('CODCLI','CODEPTO');
//print_r($param);	
		$vendas = vendas1464Campo($campos, $dataIni, $dataFim, $param, false);
//print_r($vendas);die();
	
		if(count($vendas) > 0){
			foreach ($vendas as $cliente => $venda1){
				foreach ($venda1 as $d => $venda){
					$ret[$cliente][$d] = $venda['venda'];
				}
			}
		}
//print_r($ret);die();
		return $ret;
	}
	
	function getDepto($acordo){
		$ret = '';
		$temp = array();
	
		$sql = "SELECT codepto FROM PCACORDOPARCERIADEPTO WHERE codacordo = $acordo";
		$rows = query4($sql);
	
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp[] = $row[0];
			}
			$ret = implode(',', $temp);
		}
		
		return $ret;
	}
	
	function getLinha($acordo){
		$ret = '';
		$temp = array();
	
		$sql = "SELECT codlinha FROM PCACORDOPARCERIALINHA WHERE codacordo = $acordo";
		$rows = query4($sql);
	
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp[] = $row[0];
			}
		}
	
		$ret = implode(',', $temp);
		return $ret;
	}
	
	function getProdutos($acordo){
		$ret = '';
		$temp = array();
	
		$sql = "SELECT codprod FROM PCACORDOPARCERIAPROD WHERE codacordo = $acordo";
		$rows = query4($sql);
	
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp[] = $row[0];
			}
		}
	
		$ret = implode(',', $temp);
		return $ret;
	}
	
	function getClientes($acordo){
		//POG necessário pois este é um acordo "diferente"
		if(array_search($acordo, $this->_contratosERCcliente) !== false){
			$ret = $this->getClientesAcordo($acordo);
//print_r($ret);
			return  $ret;
		}else{
			return $this->getClientesPrincipal($acordo);
		}
	}
	
	function getClientesAcordo($acordo){
		$ret = array();
		 $sql = "SELECT PCACORDOPARCERIACLI.CODCLI,
			 PCCLIENT.CLIENTE,
			 PCSUPERV.NOME SUPERVISOR,
			 PCUSUARI.NOME RCA,
			 PCUSUARI.EMAIL,
			 PCCLIENT.CODUSUR1,
			 PCUSUARI.CODSUPERVISOR,
			 (SELECT US2.EMAIL FROM PCUSUARI US2 WHERE US2.CODUSUR = PCSUPERV.COD_CADRCA) EM_SUPER
		 FROM PCACORDOPARCERIACLI,
			 pcclient,
			 pcusuari,
			 pcsuperv
		 WHERE codacordo = $acordo
			 and PCACORDOPARCERIACLI.codcli = pcclient.codcli (+)
			 and pcclient.codusur1 = pcusuari.codusur (+)
			 and pcusuari.codsupervisor = pcsuperv.codsupervisor (+)
		 ";
		
		//echo "$sql <br>";
		$rows = query4($sql);
		//Quando tem mais de um RCA pegar o que tiver mais clientes
		foreach ($rows as $row){
			if(!isset($ret[0])){
				$ret[0] = $row[0];
				$ret[1] = $row[1];
				$ret[4] = '';
				$ret[2] 			= $row['SUPERVISOR']; // Nome super
				$ret[3] 			= $row['RCA']; // Nome RCA
				$ret['email_rca'] 	= $row['EMAIL'];
				$ret['vend'] 		= $row['CODUSUR1'];
				$ret['super'] 		= $row['CODSUPERVISOR'];
				$ret['email_super'] = $row['EM_SUPER'];
			}else{
				if(strlen($ret[4]) == 0){
					$ret[4] = $row[0];
				}else{
					$ret[4] .= ','.$row[0];
				}
			}
		}

		return $ret;
	}
	
	
	function getClientesPrincipal($acordo){
		$ret = array();
		$sql = "
			SELECT
			    ACORDO.codcli,
			    pcclient.cliente,
			    ACORDO.codcliprinc,
			    pcsuperv.nome SUPERVISOR,
			    pcusuari.nome RCA,
			    pcusuari.email,
			    pcclient.codusur1,
			    pcusuari.codsupervisor,
			    (select us2.email from pcusuari us2 where us2.codusur = pcsuperv.cod_cadrca) em_super
			FROM
			    (SELECT 
			      PCACORDOPARCERIACLI.codcli,
			      pcclient.cliente,
			      pcclient.codcliprinc
			    FROM 
			      PCACORDOPARCERIACLI,
			      pcclient
			    WHERE codacordo = $acordo
			      and PCACORDOPARCERIACLI.codcli = pcclient.codcli (+)
			    ) ACORDO,
			      pcclient,
			      pcusuari,
			      pcsuperv
			  WHERE 
			        ACORDO.codcliprinc = pcclient.codcli (+)
			        and pcclient.codusur1 = pcusuari.codusur (+)
        			and pcusuari.codsupervisor = pcsuperv.codsupervisor (+)    
				
		";

		//echo "$sql <br>";
		$rows = query4($sql);
		//Quando tem mais de um RCA pegar o que tiver mais clientes
		foreach ($rows as $row){
			if(!isset($ret[0])){
				$ret[0] = $row[0];
				$ret[1] = $row[1];
				$ret[4] = '';
				$ret[2] 			= $row[3]; // Nome super
				$ret[3] 			= $row[4]; // Nome RCA
				$ret['email_rca'] 	= $row[5];
				$ret['vend'] 		= $row[6];
				$ret['super'] 		= $row[7];
				$ret['email_super'] = $row[8];
			}else{
				if(strlen($ret[4]) == 0){
					$ret[4] = $row[0];
				}else{
					$ret[4] .= ','.$row[0];
				}
			}
		}
		return $ret;
	}
	
	function setaCampos(){
		$this->_campos = $this->_relatorio->getCampos();
		
		//Se é no cálculo
		if(count($this->_campos) == 0){
			$this->_campos = array('super','regiao','vend','erc','acordo','descricao','cliente','dataini','datafim','tempofim','valfim','tipo','objetivo','premio','pago','venda1','venda12','vendaOL','venda','indice','mediaR','mediaF','bonusG','bonusU','obs','cmv','margem','Pmargem','margemB','PmargemB','vendaAnt');
		}

		$this->_camposTexto = array(
								'regiao'	,
								'erc'		,
								'descricao'	,
								'cliente'	,
								'dataini' 	,
								'datafim' 	,
								'tipo'   	,
								'obs'		
						);

		}

	function gravaTabela(){
//print_r($this->_contratos);
//print_r($this->_dadosA);
//die();
		//Grava dados dos contratos
		foreach ($this->_contratos as $contrato){
			if($contrato['acordo'] > 0){
				$sql = "DELETE FROM gf_parcerias WHERE acordo = ".$contrato['acordo'];
				query($sql);
				
				$sql_array = array();
				foreach ($this->_campos as $campo){
					$key = $campo;
					if(array_search($key,$this->_camposTexto) === false ){
						$sql_array[$campo] = $contrato[$key];
					}else{
						$sql_array[$campo] = "'".$contrato[$key]."'";
					}
					
					if($campo == 'margem' && empty($contrato[$campo])){
						$contrato[$campo] = 0;
					}
				}
				$chaves = implode(',', array_keys($sql_array));
				$valores = implode(',', array_values($sql_array));
				$sql = "INSERT INTO gf_parcerias ($chaves) VALUES ($valores)";
//echo "<br>\n$sql \n<br>\n";
				query($sql);
			}
		}
		
		//Grava por cliente
		foreach ($this->_contratos as $contrato){
			$acordo = $contrato['acordo'];
//echo "Gravando Acordo: $acordo <br>\n";
			if( $acordo > 0){
//print_r($this->_dadosA[$acordo]);
				if(isset($this->_dadosA[$acordo])){
					
					$sql = "DELETE FROM gf_parceriasClientes WHERE acordo = ".$acordo;
					query($sql);
					
					foreach ($this->_dadosA[$acordo] as $cliente => $c){
						$campos = array();
						$campos['acordo'] 	= $acordo;
						$campos['cliente'] 	= $cliente;
						$campos['venda1'] 	= isset($c['venda1']) ? $c['venda1'] : 0;
						$campos['venda12'] 	= isset($c['venda12']) ? $c['venda12'] : 0;
						$campos['vendaOL'] 	= isset($c['vendaOL']) ? $c['vendaOL'] : 0;
						$campos['venda'] 	= isset($c['venda']) ? $c['venda'] : 0;
						$campos['indice'] 	= isset($c['indice']) ? $c['indice'] : 0;
						$campos['mediaR'] 	= isset($c['mediaR']) ? $c['mediaR'] : 0;
						$campos['mediaF'] 	= isset($c['mediaF']) ? $c['mediaF'] : 0;
						$campos['bonusG'] 	= isset($c['bonusG']) ? $c['bonusG'] : 0;
						$campos['bonusU'] 	= isset($c['bonusU']) ? $c['bonusU'] : 0;
						$campos['cmv'] 		= isset($c['cmv']) ? $c['cmv'] : 0;
						$campos['margem'] 	= isset($c['margem']) ? $c['margem'] : 0;
						$campos['Pmargem'] 	= isset($c['Pmargem']) ? $c['Pmargem'] : 0;
						$campos['margemB'] 	= isset($c['margemB']) ? $c['margemB'] : 0;
						$campos['PmargemB'] = isset($c['PmargemB']) ? $c['PmargemB'] : 0;
						$campos['vendaAnt'] = isset($c['vendaAnt']) ? $c['vendaAnt'] : 0;
						
						$sql = montaSQL($campos, 'gf_parceriasClientes');
//echo "$sql <br>\n";
						query($sql);
					}
				}
			}
		}
	}
}