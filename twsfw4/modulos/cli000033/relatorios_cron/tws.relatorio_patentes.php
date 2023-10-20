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

	//Nome vendedores
	private $_vendedores = [];

	public function __construct()
	{
		$this->_programa = get_class($this);
		$this->_titulo = '';

		$this->_teste = false;

		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);

		if (true) {
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Separar por Vendedor?', 'variavel' => 'SEPARA', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'S=Sim; N=Não']);
		}
	}

	public function index()
	{
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();

		$separa 	= isset($filtro['SEPARA']) ? $filtro['SEPARA'] : 'N';

		$this->_relatorio->setTitulo("Relatório de Patentes");

		$this->getDados($separa);
		$this->montaColunas();

		if ($separa == 'S') {
			$secao = 0;
			foreach ($this->_dados as $vendedor => $dados) {
				$this->_relatorio->setDados($dados, $secao);
				$this->_relatorio->setTituloSecao($secao, 'Vendedor - ' . $vendedor . ' - ' . $this->_vendedores[$vendedor]);
				$secao++;
			}
		} else {
			$this->_relatorio->setDados($this->_dados);
		}


		$ret .= $this->_relatorio;

		return $ret;
	}

	private function montaColunas()
	{
		$this->_relatorio->addColuna(array('campo' => 'nomecliente', 'etiqueta' => 'Nome Cliente', 'tipo' => 'T', 'width' =>  150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'classificacao', 'etiqueta' => 'Tipo Classificação', 'tipo' => 'T', 'width' => 20, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'processo', 'etiqueta' => 'Processo', 'tipo' => 'T', 'width' =>  60, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'data_dep', 'etiqueta' => 'Data Depósito', 'tipo' => 'T', 'width' => 60, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'data_con', 'etiqueta' => 'Data Concessão', 'tipo' => 'T', 'width' =>  60, 'posicao' => 'D'));
	}




	private function getDados($separa = 'N')
	{
		$sql = "
		SELECT
			to_ascii((SELECT empresasemacento FROM marpacliente WHERE sigla=marpapatente.siglacliente))nomecliente,
			descodtipoclass,
			codigoprocesso,
			datapublicacao,
			(SELECT dataandamento FROM marpaandamentopat 
				WHERE pasta=marpapatente.pasta AND despacho='16.1') dataandamento,
			vendedor,
			vendedorcliente.codigovendedor
			
		FROM 
			marpapatente
	
		LEFT JOIN 
			marpacliente 
			ON 
			marpapatente.siglacliente = marpacliente.sigla
		LEFT JOIN 
			marpatipoclassificacao 
			USING (codtipoclass)
		LEFT JOIN
			vendedorcliente
			ON 
			marpacliente.sigla = vendedorcliente.sigla
	
		WHERE 
			marpacliente.sigla IN (SELECT sigla FROM marpacliente WHERE tipo_cliente='C' AND status_cliente='A')
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
				$temp['nomecliente'] = $row['nomecliente'];
				$temp['classificacao'] = $row['descodtipoclass'];
				$temp['processo'] = $row['codigoprocesso'];
				$temp['data_dep'] = datas::dataMS2D($row['datapublicacao']);
				$temp['data_con'] = datas::dataMS2D($row['dataandamento']);

				$this->_vendedores[$row['codigovendedor']] = $row['vendedor'];

				if ($separa == 'S') {
					$this->_dados[$row['codigovendedor']][] = $temp;
				} else {
					$this->_dados[] = $temp;
				}
			}
		}
	}
}
