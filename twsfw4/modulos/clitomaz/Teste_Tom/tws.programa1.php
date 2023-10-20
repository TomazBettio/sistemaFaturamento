<?php
/*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
class programa1
{
	var $funcoes_publicas = array(
		'index'			=> true,
		'salvar'        => true,
		'incluir'       => true,
		'editar'		=> true,
		'excluir'		=> true,
		'salvarEdit'	=> true,
		'salvarInc'	=> true

	);
	//Classe relatorio
	private $_relatorio;

	//Classe tabela
	private $_tabela;

	//Nome do programa 
	private $_programa;

	//Titulo do relatorio
	private $_titulo;

	private $_dados;

	private $_edit;

	public function __construct()
	{

		$this->_programa = get_class($this);
		$this->_titulo = 'Cruzamento de Similares';

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['paginacao'] = true;
		$param['titulo'] = 'Classe De/Para';
		$this->_tabela = new tabela01($param);

		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;

	}

	public function index()
	{
		$ret = '';


		$this->montaColunas();
		$this->getDados();
	
		// $this->_tabela->setDados($this->_dados);



		$p = [];
		$p['onclick'] = "setLocation('" . getLink() . "incluir&id=')";
		$p['cor'] = 'info';
		$p['texto'] = 'Incluir';
		$this->_tabela->addBotaoTitulo($p);

		$param = array();
		$param['texto'] 	=  'Editar';
		$param['link'] 		= getLink() . 'editar&id=';
		$param['coluna']	= 'CDPCODIGO';
		$param['flag'] 		= '';
		$param['width'] 	= 30;
		$this->_tabela->addAcao($param);

		$param = [];
		$param['texto'] 	= 'Excluir';
		$param['link'] 		= getLink() . 'excluir&id=';
		$param['coluna'] 	= 'CDPCODIGO';
		$param['width'] 	= 30;
		$param['flag'] 		= '';
		//$param['tamanho'] 	= 'pequeno';
		$param['cor'] 		= 'danger';
		$this->_tabela->addAcao($param);


		$this->_tabela->setTitulo("Tabela Classes DE/PARA");

		$ret .= $this->_tabela;
		


		return $ret;
	}

	private function montaColunas()
	{
		$this->_tabela->addColuna(array('campo' => 'id', 'etiqueta' => 'id', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'nome', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		// $this->_tabela->addColuna(array('campo' => 'CDPDATA_INC', 'etiqueta' => 'Data Inclus&atilde;o', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		// $this->_tabela->addColuna(array('campo' => 'CDPDATA_ALT', 'etiqueta' => 'Data Altera&ccedil;&atilde;o', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		// $this->_relatorio->addColuna(array('campo' => 'totalQt'	        , 'etiqueta' => 'Quant. Total'	, 'tipo' => 'T', 'width' =>  150, 'posicao' => 'D'));
		// $this->_relatorio->addColuna(array('campo' => 'totalValesVal'	, 'etiqueta' => 'Valor'	, 'tipo' => 'V', 'width' =>  150, 'posicao' => 'D'));

	}

	private function getDados()
	{
		// $ret = array();

		$sql =
			"SELECT
                *
            FROM
                dados_teste";
		echo "$sql <br> ";
		$rows = query($sql);
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$temp = [];
				$temp['id']        = $row['id'];
				$temp['nome']       = $row['nome'];
				

				$this->_dados[] = $temp;
				// $ret[] = $temp;
			}
		}

		return;
	}

	private function getDadosEditar($id)
	{
		$ret = [];
		$campos = array('id', 'codclasse', 'codclasseproc');

		// $sql =
		// 	"SELECT
		// 		r.CDPCODIGO as id, 
		// 		r.CDPCODCLASSE as codclasse, 
		// 		r.CDPCODCLASSEPROC as codclasseproc
		// 	FROM
		// 		CLASSESDEPARA r
		// 	WHERE
		// 		r.CDPSTATUS =  'S'  
		// 	and r.CDPCODIGO = $id 
		// 	order by 
		// 		r.CDPCODIGO ";
		// // echo $sql . "<br> ";
		// $rows = queryCONSULT($sql);
		// // print_r($rows);
		// if (is_array($rows) && count($rows) > 0) {
		// 	foreach ($campos as $campo) {
		// 		$ret[$campo] = $rows[0][$campo];
		// 	}
		// }
		return $ret;
	}

	public function editar($id = '')
	{
		if ($id == '') {
			$id = getParam($_GET, 'id', 0);
		}
		$dados = $this->getDadosEditar($id);

		$param = [];
		$form = new form01($param);
		$form->setBotaoCancela();

		$param['valor'] = $dados['codclasse'];
		$param['campo'] = 'formPrograma[CDPCODCLASSE]';
		$param['etiqueta'] = 'DE';
		$param['largura'] = '4';
		$param['tipo'] = 'N';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

		$param['valor'] = $dados['codclasseproc'];
		$param['campo'] = 'formPrograma[CDPCODCLASSEPROC]';
		$param['etiqueta'] = 'PARA';
		$param['largura'] = '4';
		$param['tipo'] = 'N';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

		// print_r($form);
		$form->setEnvio(getLink() . 'salvarEdit&id=' . $id, 'formPrograma', 'formPrograma');

		// print_r($dados);
		$param['icone'] = 'fa-edit';
		$param['titulo'] = 'Editar Programa';
		$param['conteudo'] = $form;

		$ret = addCard($param);

		putAppVar('config_programas_editar', 'editar');

		return $ret;
	}

	public function incluir()
	{
		$ret = '';
		$form = new form01();
		// $form->addHidden('CDPCODIGO', $id);

		$param = [];
		$param['campo'] = 'CDPCODCLASSE';
		$param['etiqueta'] = 'DE';
		$param['largura'] = '4';
		$param['tipo'] = 'N';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

 
		$param = [];
		$param['campo'] = 'CDPCODCLASSEPROC';
		$param['etiqueta'] = 'PARA';
		$param['largura'] = '4';
		$param['tipo'] = 'N';
		// $param['tamanho'] = '14';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

		$form->setEnvio(getLink() . 'salvarInc', 'formIncluir_cliente');

		$ret .= $form;

		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Inclus o De/Para';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
	}

	public function excluir()
	{
		$id = getParam($_GET, 'id', 0);
		$data = date('Y-m-d H:i:s');
		// echo $id;
		// if ($id !== 0) {
		// 	$sql = "UPDATE
		// 				CLASSESDEPARA r
		// 			SET
		// 				r.CDPSTATUS = 'N', 
		// 				r.CDPDATA_ALT = '" . $data . "'
		// 			WHERE
		// 				r.CDPCODIGO = " . $id;
		// 	queryCONSULT($sql);
		// 	// echo $sql;
		// 	addPortalMensagem("Sucesso!<br>O programa foi exclu do!");
		// }

		$ret = $this->index();
		return $ret;
	}

	public function salvarInc()
	{

		$de = $_POST['CDPCODCLASSE'];
		$para = $_POST['CDPCODCLASSEPROC'];
		$data = date('Y-m-d H:i:s');


		// $sql = "INSERT INTO 
		// 			CLASSESDEPARA(CDPDATA_INC, CDPUSU_INC, CDPDATA_ALT, CDPUSU_ALT, CDPSTATUS, CDPCODCLASSE, CDPCODCLASSEPROC) 
		// 		VALUES 
		// 			('" . $data . "', 78, '" . $data . "', 78, 'S', $de, $para)";

		// echo $sql;
		// // die("ok");
		// queryCONSULT($sql);
		// $sql = "INSERT INTO 
		// 			CLASSESDEPARA(CDPDATA_INC, CDPUSU_INC, CDPDATA_ALT, CDPUSU_ALT, CDPSTATUS, CDPCODCLASSE, CDPCODCLASSEPROC) 
		// 		VALUES 
		// 			('" . $data . "', 78, '" . $data . "', 78, 'S', $para, $de)";
		// queryCONSULT($sql);

		addPortalMensagem("Sucesso!<br>As informa  es foram inclu das!");


		$ret = $this->index();
		return $ret;
	}

	public function salvarEdit($id = '')
	{

		$id = getParam($_GET, 'id', 0);
		$dados = getParam($_POST, 'formPrograma', []);


		$de = $dados['CDPCODCLASSE'];
		echo $de;
		$para = $dados['CDPCODCLASSEPROC'];
		echo $para;
		$data = date('Y-m-d H:i:s');

		// $sql = "UPDATE 
		// 			CLASSESDEPARA 
		// 		SET 
		// 			CDPCODCLASSE = $de, 
		// 			CDPCODCLASSEPROC = $para, 
		// 			CDPDATA_ALT = '" . $data . "'
		// 		WHERE 
		// 			CDPCODIGO = $id";

		// echo $sql;
		// // die('ok');
		// queryCONSULT($sql);

		addPortalMensagem("Sucesso!<br>O programa foi editado!");


		$ret = $this->index();
		return $ret;
	}
}
