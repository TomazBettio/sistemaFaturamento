<?php
       /*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class aniversario_relatorio{
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
		conectaRH();
		$this->_programa = get_class($this);
		$this->_titulo = '';
		
		$this->_teste = true;
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);

		$param= [];
		$param['filtro']= false;
		$param['info']= false;
		$this-> _relatorio->setParamTabela($param);
		if(true){
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
	}
	
	public function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		
		$this->_relatorio->setTitulo("Aniversariantes");
		$this->montaColunas();
		//if(!$this->_relatorio->getPrimeira()){
			
			
			$dados = $this->getDadosDia();
			$this->_relatorio->setDados($dados, 0);
			$this->_relatorio->setTituloSecao(0,"Aniversariantes do dia");
			
			$dados = $this->getDadosSem();
			$this->_relatorio->setDados($dados, 1);
			$this->_relatorio->setTituloSecao(1,"<br>Aniversariantes da semana");
			
			$dados = $this->getDadosMes();
			$this->_relatorio->setDados($dados,2);
			$this->_relatorio->setTituloSecao(2,"<br>Aniversariantes do mês");


		
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	public function schedule($param = ''){
		ini_set('display_errors',0);
		ini_set('display_startup_erros',0);
		error_reporting(E_ALL);
		$this->montaColunas();
		$this->_relatorio->setToExcel(false);
		$this->_relatorio->setAuto(false);
		$this->_relatorio->setTitulo("Aniversariantes");
		log::gravaLog('aniversariantes', 'Inicando processo');
		
		$dados = $this->getDadosDia();
		$this->_relatorio->setDados($dados);
		$this->_relatorio->setTitulo("Aniversariantes do dia");
		log::gravaLog('aniversariantes', '');
		
		// $dados = $this->getDadosSem();
		// $this->_relatorio->setDados($dados, 1);
		// $this->_relatorio->setTituloSecao(1,"<br>Aniversariantes da semana");
		
		// $dados = $this->getDadosMes();
		// $this->_relatorio->setDados($dados,2);
		// $this->_relatorio->setTituloSecao(2,"<br>Aniversariantes do mês");
		
		if ($this->_teste){
			$param = 'tomaz.bettio@verticais.com.br';
			}
		$this->_relatorio->enviaEmail($param);
		//log::gravaLog('agenda_sem_os', 'Email teste enviado');
		echo "Email enviado";

				
	}
		
	
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo' => 'DIA'					, 'etiqueta' => 'Dia'				, 'tipo' => 'T', 'width' =>  80	, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'USUNOME'				, 'etiqueta' => 'Colaborador'		, 'tipo' => 'T', 'width' => 300	, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'DEPARTAMENTO'		, 'etiqueta' => 'Departamento'		, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'USUDATA_NASCIMENTO'	, 'etiqueta' => 'Data de Nascimento', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'IDADE'				, 'etiqueta' => 'Idade'				, 'tipo' => 'T', 'width' =>  90, 'posicao' => 'E'));
		
	}
	
	private function getDadosDia(){
		$ret = [];
		
		$sql = "SELECT
		DAY(u.USUDATA_NASCIMENTO) AS DIA,
		u.USUNOME,
		t1.TABDESCRICAO AS DEPARTAMENTO,
		u.USUDATA_NASCIMENTO,
		TIMESTAMPDIFF(YEAR, USUDATA_NASCIMENTO, NOW()) AS IDADE
		
	FROM
		USUARIOS u
	LEFT JOIN
		TABELAS t1
	ON
		u.TABDEPARTAMENTO = t1.TABCODIGO
	WHERE
		u.USUSTATUS = 'S'
	AND
		u.USUDATADEMISSAO IS NULL
	AND 
		u.USUDATA_NASCIMENTO IS NOT NULL
	AND 
		DAY(USUDATA_NASCIMENTO) = DAY(NOW())
	AND 
		MONTH(USUDATA_NASCIMENTO) = MONTH(NOW())";
		$rows = queryRH($sql);
		//echo "$sql <br> ";
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['DIA'] = $row['DIA'];
				$temp['USUNOME'] = $row['USUNOME'];
				$temp['DEPARTAMENTO'] = $row['DEPARTAMENTO'];
				$temp['USUDATA_NASCIMENTO'] = datas::dataMS2D($row['USUDATA_NASCIMENTO']);
				$temp['IDADE'] = $row['IDADE'];
		
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function getDadosSem(){
		$ret = [];
		
		$sql = "SELECT
		DAY(u.USUDATA_NASCIMENTO) AS DIA,
		u.USUNOME,
		t1.TABDESCRICAO AS DEPARTAMENTO,
		u.USUDATA_NASCIMENTO,
		TIMESTAMPDIFF(YEAR, USUDATA_NASCIMENTO, NOW()) AS IDADE
		
		FROM
			USUARIOS u
		LEFT JOIN
			TABELAS t1
		ON
			u.TABDEPARTAMENTO = t1.TABCODIGO
		WHERE
			u.USUSTATUS = 'S'
		AND
			u.USUDATADEMISSAO IS NULL
		AND 
			u.USUDATA_NASCIMENTO IS NOT NULL
		AND 
			WEEK(USUDATA_NASCIMENTO) = WEEK(NOW())";
			$rows = queryRH($sql);
			//echo "$sql <br> ";
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['DIA'] = $row['DIA'];
				$temp['USUNOME'] = $row['USUNOME'];
				$temp['DEPARTAMENTO'] = $row['DEPARTAMENTO'];
				$temp['USUDATA_NASCIMENTO'] = datas::dataMS2D($row['USUDATA_NASCIMENTO']);
				$temp['IDADE'] = $row['IDADE'];
		
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}

	private function getDadosMes(){
		$ret = [];
		
		$sql = "SELECT
		DAY(u.USUDATA_NASCIMENTO) AS DIA,
		u.USUNOME,
		t1.TABDESCRICAO AS DEPARTAMENTO,
		u.USUDATA_NASCIMENTO,
		TIMESTAMPDIFF(YEAR, USUDATA_NASCIMENTO, NOW()) AS IDADE
		
		FROM
			USUARIOS u
		LEFT JOIN
			TABELAS t1
		ON
			u.TABDEPARTAMENTO = t1.TABCODIGO
		WHERE
			u.USUSTATUS = 'S'
		AND
			u.USUDATADEMISSAO IS NULL
		AND 
			u.USUDATA_NASCIMENTO IS NOT NULL
		AND 
			MONTH(USUDATA_NASCIMENTO) = MONTH(NOW())";
			$rows = queryRH($sql);
			//echo "$sql <br> ";

		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['DIA'] = $row['DIA'];
				$temp['USUNOME'] = $row['USUNOME'];
				$temp['DEPARTAMENTO'] = $row['DEPARTAMENTO'];
				$temp['USUDATA_NASCIMENTO'] = datas::dataMS2D($row['USUDATA_NASCIMENTO']);
				$temp['IDADE'] = $row['IDADE'];
		
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
}