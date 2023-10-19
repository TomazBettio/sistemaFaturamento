<?php
/*
 * Data Criacao: 10/06/2022
 * 
 * Autor:  Luís Costa
 *
 * Descricao: 
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class relatorio_patentes
{
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

	public function __construct()
	{
		$this->_programa = get_class($this);
		$this->_titulo = '';

		$this->_teste = false;

		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);

		if (false) {
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De', 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até', 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente', 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Analista', 'variavel' => 'RECURSO', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
	}

	public function index()
	{
		$ret = '';
		// $filtro = $this->_relatorio->getFiltro();

		// $dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		// $dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';

		$this->_relatorio->setTitulo("Relatório de Patentes");

		$this->getDados();
		$this->montaColunas();

		$this->_relatorio->setDados($this->_dados);
		//$this->_relatorio->toExcel(False);


		$ret .= $this->_relatorio;

		return $ret;
	}

	private function montaColunas()
	{
		$this->_relatorio->addColuna(array('campo' => 'depositante', 'etiqueta' => 'Nome Cliente', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'classificacao', 'etiqueta' => 'Tipo Classificação', 'tipo' => 'T', 'width' => 20, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'processo', 'etiqueta' => 'Processo', 'tipo' => 'T', 'width' =>  60, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'data_dep', 'etiqueta' => 'Data Depósito', 'tipo' => 'T', 'width' => 60, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'data_con', 'etiqueta' => 'Data Concessão', 'tipo' => 'T', 'width' =>  60, 'posicao' => 'E'));
	}

	private function getDados()
	{
		$ret = [];

		$sql = "
		SELECT
			depositante,
			codtipoclass,
			codigoprocesso,
			datapublicacao,
			(SELECT dataandamento FROM marpaandamentopat 
				WHERE pasta=marpapatente.pasta AND despacho='16.1') dataandamento
		
		FROM 
			marpapatente
	
		LEFT JOIN 
			marpacliente 
			ON 
			marpapatente.siglacliente = marpacliente.sigla
	
		WHERE 
			sigla IN (SELECT sigla FROM marpacliente WHERE tipo_cliente='C' AND status_cliente='A')
			AND 
				deppais='BR'
			AND 
				titular IS NULL
			AND 
				(codigomotcancel=0 OR codigomotcancel IS NULL)
			AND 
				codtipoclass IN (4,5,7,8)
			AND 
				pasta NOT IN (SELECT pasta FROM marpaandamentopat WHERE codstatusandamento =3)
			AND 
				pasta IN (SELECT pasta FROM marpaandamentopat WHERE despacho='16.1')	";

		$rows = query2($sql);

		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$temp = [];
				$temp['depositante'] = $row['depositante'];
				$temp['classificacao'] = $row['codtipoclass'];
				$temp['processo'] = $row['codigoprocesso'];
				$temp['data_dep'] = $row['datapublicacao'];
				$temp['data_con'] = $row['dataandamento'];

				$ret[] = $temp;
			}
		}

		return $ret;
	}
}
