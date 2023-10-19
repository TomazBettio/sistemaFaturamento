<?php
       /*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class contratos_connect{
	var $funcoes_publicas = array(
			'index' 		=> true
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
	
	public function __construct(){

		conectaERP();
		$this->_programa = get_class($this);
		$this->_titulo = '';
		
		$this->_teste = false;
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);

		// $param= [];
		// $param['filtro']= false;
		// $param['info']= true;
		// $this-> _relatorio->setParamTabela($param);
		// if(true){
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// }
	}
	
	public function index(){
		$ret = '';
		// $filtro = $this->_relatorio->getFiltro();
		
		// $dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		// $dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		
		$this->_relatorio->setTitulo("Relatório");
		$this->montaColunas();
		//if(!$this->_relatorio->getPrimeira()){
			
			
		$dados = $this->getDados();
		$this->_relatorio->setDados($dados, 0);
		$this->_relatorio->setTituloSecao(0,"Contratos Connect");
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	// public function schedule($param = ''){
	// 	ini_set('display_errors',0);
	// 	ini_set('display_startup_erros',0);
	// 	error_reporting(E_ALL);
	// 	$this->montaColunas();
	// 	$this->_relatorio->setToExcel(false);
	// 	$this->_relatorio->setAuto(false);
	// 	$this->_relatorio->setTitulo("Log de contratos");
		
	// 	$dados = $this->getDados();
	// 	$this->_relatorio->setDados($dados);
	// 	$this->_relatorio->setTitulo("Contratos de parceiros Connect");
	// 	// log::gravaLog('aniversariantes', '');
		
	// 	// $dados = $this->getDadosSem();
	// 	// $this->_relatorio->setDados($dados, 1);
	// 	// $this->_relatorio->setTituloSecao(1,"<br>Aniversariantes da semana");
		
	// 	// $dados = $this->getDadosMes();
	// 	// $this->_relatorio->setDados($dados,2);
	// 	// $this->_relatorio->setTituloSecao(2,"<br>Aniversariantes do mês");
		
	// 	if ($this->_teste){
	// 		$param = 'tomaz.bettio@verticais.com.br';
	// 		}
	// 	$this->_relatorio->enviaEmail($param);
	// 	//log::gravaLog('agenda_sem_os', 'Email teste enviado');
	// 	echo "Email enviado";

				
	// }
		
	
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo'    => 'CXPMINUTA', 
										   'etiqueta' => 'Numero minuta', 
										   'tipo'  	  => 'T', 
										   'width' 	  =>  80, 
										   'posicao'  => 'E'
										));
		$this->_relatorio->addColuna(array('campo' 	  => 'CLINOMEFANTASIA', 
										   'etiqueta' => 'Nome da empresa', 
										   'tipo'     => 'T', 
										   'width'    => 300, 
										   'posicao'  => 'E'
										));
		$this->_relatorio->addColuna(array('campo'    => 'CXPDATA_ALT', 
										   'etiqueta' => 'Data de inclusão', 
										   'tipo'     => 'T', 
										   'width'    =>  200, 
										   'posicao'  => 'E'
										));
		$this->_relatorio->addColuna(array('campo'    => 'USUNOME', 
										   'etiqueta' => 'Nome do representante', 
										   'tipo'     => 'T', 
										   'width'    =>  90, 
										   'posicao'  => 'E'
										));
		
	}
	
	private function getDados(){
		$ret = [];
		
		$sql = "SELECT 
				p.CXPMINUTA,
				p.CXPDATA_ALT,
				c.CLINOMEFANTASIA,
				u.USUNOME
				
				FROM   
				PROSPECCAO AS p

				LEFT JOIN
				CLIENTES AS c using (CLICODIGO)
				
				LEFT JOIN
				USUARIOS as u on p.CXPREPRESENTANTE = u.USUCODIGO
				
				WHERE 
				SUBSTRING(p.CXPMINUTA, 1, 1) = 'C'";
		$rows = queryERP($sql);
		//echo "$sql <br> ";
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['CXPMINUTA'] = $row['CXPMINUTA'];
				$temp['CLINOMEFANTASIA'] = $row['CLINOMEFANTASIA'];
				$temp['CXPDATA_ALT'] = datas::dataMS2D($row['CXPDATA_ALT']);
				$temp['USUNOME'] = $row['USUNOME'];
		
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	

}