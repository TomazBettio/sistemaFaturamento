<?php
/*
 * Data Criacao: 06/06/22
 * 
 * Autor: Alexandre Thiel
 *
 * Descricao: Executado toda a sexta feira - informa contratos que devem ser renovados em 30/60/90 dias
 *
 * Alterações:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class renovacoes{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	//Classe relatorio
	private $_relatorio;
	
	//Nome do programa
	private $_programa;
	
	//Titulo do relatorio
	private $_titulo;
	
	//Indica que se é teste (não envia email se for)
	private $_teste;
	
	//Dados
	private $_dados;
	
	//Vendedores
	private $_vendedores = [];
	
	//Arquivo LOG
	private $_log;
	
	public function __construct(){
		$this->_programa = get_class($this);
		$this->_titulo = '';
		
		$this->_teste = false;
		$this->_log = 'cron_renovacoes';
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$this->montaColunas();
		
		if(false){
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente'	, 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Analista', 'variavel' => 'RECURSO', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
	}
	
	public function index(){
		$ret = '';
		$ret = '';
		
		$this->_relatorio->setTitulo("Renovações 30/60 dias");
		
		$this->getDados(30);
		$this->_relatorio->setTituloSecao(0, 'Renovações - 30 dias');
		$this->_relatorio->setDados($this->_dados);
		
		$this->getDados(60);
		$this->_relatorio->setTituloSecao(1, 'Renovações - 60 dias');
		$this->_relatorio->setDados($this->_dados, 1);
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	public function schedule($param = ''){
		$this->_relatorio->setAuto(true);
		
		//Relatório de 30 dias para os emails constantes no parametro
		$this->getDados(30);
		$this->_relatorio->setTitulo("Relatório de Renovações de Contratos (30 dias) - ".datas::data_hoje());
		$this->_relatorio->setDados($this->_dados);
		if($this->_teste){
			$this->_relatorio->enviaEmail('suporte@thielws.com.br');
		}else{
			$param = 'rosemari@grupomarpa.com.br;valdomiro@grupomarpa.com.br;greice@grupomarpa.com.br;faturamento@marpa.com.br;sistema@marpa.com.br;suporte@thielws.com.br';
			$this->_relatorio->enviaEmail($param);
			log::gravaLog($this->_log, 'Renovações 30 dias enviado: '.$param);
		}
		
		
		$this->_relatorio->setTitulo("Relatório de Renovações de Contratos (60 dias) - ".datas::data_hoje());
		$this->_relatorio->setMensagemInicioEmail("<h3>Informamos que você tem 30 dias para renovar os contratos listados abaixo, após esse período será renovado automaticamente pela empresa e não terá direito a participação</h3>");

		$this->getVendedores();
		
		if(count($this->_vendedores) > 0){
			foreach ($this->_vendedores as $vend => $email){
				$this->getDados(60, $vend);
				
				if(count($this->_dados) > 0){
					$this->_relatorio->setDados($this->_dados);
					if($this->_teste){
						$this->_relatorio->enviaEmail('suporte@thielws.com.br');
					}else{
						$para = $email.";valdomiro@grupomarpa.com.br; sistema@marpa.com.br";
						$this->_relatorio->enviaEmail($para);
						log::gravaLog($this->_log, 'Renovações 60 dias enviado: '.$para);
					}
				}
			}
		}
	}
	
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo' => 'sigla'	, 'etiqueta' => 'Sigla'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'empresa'	, 'etiqueta' => 'Empresa'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vendedor', 'etiqueta' => 'Vendedor'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'nr'		, 'etiqueta' => 'Número'	, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'tipo'	, 'etiqueta' => 'Tipo'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'fim'		, 'etiqueta' => 'Término'	, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
	}
	
	private function getDados($dias = 30, $vend = ''){
		$this->_dados = [];
		
		$dia = datas::dataMSSQL(datas::getDataDias($dias));
		
		$where = '';
		if(!empty($vend)){
			$where = " AND mv.codigovendedor = $vend";
		}
		
		$sql = "
				SELECT 
					mc.sigla,
					mc.empresa,
					vendedor, 
					mg.numero,
					descrtipolan,
					TO_CHAR(dttermctr,'dd/mm/YYYY') as termino
				FROM 
					marpacliente mc 
						inner join marpagold mg 
							ON(mg.sigla = mc.sigla) 
						inner join marpatipolan using(tipolan) 
						inner join marpavendedor mv 
							ON(mv.codigovendedor = mc.codigovendedor)
				WHERE 
					mg.codstatus = 1 
					AND dttermctr <= '$dia' 
					AND status_cliente = 'A' 
					AND mg.sigla NOT IN	( 
										SELECT 
											sigla 
										FROM 
											marpafinpc 
											LEFT JOIN marpafin USING(tipolan,numlan) 
										WHERE 
											codclassif = 9 
										) 
					AND (mg.tipolan NOT IN (16, 17) 
					and tipolan < 50)
					AND mc.sigla NOT IN ( 88102, 164496, 145874 )
					$where
				ORDER BY 
					dttermctr desc
				";
		$rows = query2($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['sigla'] 		= $row['sigla'];
				$temp['empresa'] 	= mb_convert_encoding($row['empresa'], "UTF-8", "CP1252");
				$temp['vendedor'] 	= $row['vendedor'];
				$temp['nr'] 		= $row['numero'];
				$temp['tipo'] 		= $row['descrtipolan'];
				$temp['fim'] 		= $row['termino'];
				
				$this->_dados[] = $temp;
			}
		}
		
		return;
	}
	
	private function getVendedores($dias = 60){
		$dia = datas::dataMSSQL(datas::getDataDias($dias));
		$sql = "
				SELECT 
					mv.codigovendedor, 
					mv.email
				FROM 
					marpacliente mc 
						inner join marpagold mg 
							ON(mg.sigla = mc.sigla) 
						inner join marpatipolan using(tipolan) 
						inner join marpavendedor mv 
							ON(mv.codigovendedor = mc.codigovendedor)
				WHERE 
					mg.codstatus = 1 
					AND dttermctr <= '$dia' 
					AND status_cliente = 'A' 
					AND mg.sigla NOT IN ( 
											SELECT 
												sigla 
											FROM 
												marpafinpc 
												LEFT JOIN marpafin USING(tipolan,numlan) 
											WHERE 
												codclassif = 9 
										) 
					AND (mg.tipolan NOT IN (16, 17) 
					and tipolan < 50)
				GROUP BY 
					mv.codigovendedor, 
					mv.email
				";
		$rows = query2($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$this->_vendedores[$row['codigovendedor']] = $row['email'];
			}
		}
	}
}