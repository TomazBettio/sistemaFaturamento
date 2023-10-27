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
		$this->_titulo = 'Controle estoque';

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = true;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['paginacao'] = false;
		$param['titulo'] = 'Controle estoque';
		$this->_tabela = new tabela01($param);

		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;

	}

	public function index()
	{
		$ret = '';


		$this->montaColunas();
		$dados = $this->getDados();
	
		$this->_tabela->setDados($dados);



		$p = [];
		$p['onclick'] = "setLocation('" . getLink() . "incluir&id=')";
		$p['cor'] = 'info';
		$p['texto'] = 'Incluir';
		$this->_tabela->addBotaoTitulo($p);

		$param = array();
		$param['texto'] 	=  'Editar';
		$param['link'] 		= getLink() . 'editar&id=';
		$param['coluna']	= 'id';
		$param['flag'] 		= '';
		$param['width'] 	= 30;
		$this->_tabela->addAcao($param);

		$param = [];
		$param['texto'] 	= 'Excluir';
		$param['link'] 		= getLink() . 'excluir&id=';
		$param['coluna'] 	= 'id';
		$param['width'] 	= 30;
		$param['flag'] 		= '';
		//$param['tamanho'] 	= 'pequeno';
		$param['cor'] 		= 'danger';
		$this->_tabela->addAcao($param);


		$this->_tabela->setTitulo("Tabela de cadastro de estoque");

		$ret .= $this->_tabela;
		


		return $ret;
	}

	private function montaColunas()
	{
		$this->_tabela->addColuna(array('campo' => 'id', 'etiqueta' => 'id', 'tipo' => 'T', 'width' =>  250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'nome_dado', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' =>  250, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cod', 'etiqueta' => 'codigo', 'tipo' => 'T', 'width' =>  250, 'posicao' => 'E'));
		// $this->_tabela->addColuna(array('campo' => 'CDPDATA_INC', 'etiqueta' => 'Data Inclus&atilde;o', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		// $this->_tabela->addColuna(array('campo' => 'CDPDATA_ALT', 'etiqueta' => 'Data Altera&ccedil;&atilde;o', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		// $this->_relatorio->addColuna(array('campo' => 'totalQt'	        , 'etiqueta' => 'Quant. Total'	, 'tipo' => 'T', 'width' =>  150, 'posicao' => 'D'));
		// $this->_relatorio->addColuna(array('campo' => 'totalValesVal'	, 'etiqueta' => 'Valor'	, 'tipo' => 'V', 'width' =>  150, 'posicao' => 'D'));

	}

	private function getDados()
	{
		$ret = [];

		$sql =
			"SELECT
                *
            FROM
                dados_teste";
		// echo "$sql <br> ";
		$rows = query($sql);
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
                // print_r( $row ) . 'aaaaa';
				$temp = [];
				$temp['id']        = $row['id'];
				$temp['nome_dado']       = $row['nome_dado'];
                $temp['cod']       = $row['cod'];

				

				// $this->_dados[] = $temp;
			    $ret[] = $temp;
                // print_r($ret);
			}
		}

		return $ret;
	}

	private function getDadosEditar($id)
	{
		$ret = [];
		$campos = array('nome_dado', 'cod');

		$sql =
			"SELECT
				*
			FROM
				dados_teste";
		// echo $sql . "<br> ";
		$rows = query($sql);
		// print_r($rows);
		if (is_array($rows) && count($rows) > 0) {
			foreach ($campos as $campo) {
				$ret[$campo] = $rows[0][$campo];
			}
		}
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

		$param['valor'] = $dados['cod'];  
		$param['campo'] = 'formPrograma[cod]';
		$param['etiqueta'] = 'codigo';
		$param['largura'] = '4';
		$param['tipo'] = 'N';
		$param['obrigatorio'] = true;
		$form->addCampo($param);

		$param['valor'] = $dados['nome_dado'];
		$param['campo'] = 'formPrograma[nome_dado]';
		$param['etiqueta'] = 'nome';
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
		$param['campo'] = 'cod';
		$param['etiqueta'] = 'codigo';
		$param['largura'] = '4';
		$param['tipo'] = 'N';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;


		$form->addCampo($param);

 
		$param = [];
		$param['campo'] = 'nome_dado';
		$param['etiqueta'] = 'Nome';
		$param['largura'] = '4';
		$param['tipo'] = 'N';
		// $param['tamanho'] = '14';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$form->addCampo($param);

		$form->setPastas([1 => 'Dados Gerais', 2 => 'Nomes']);
		$form->setEnvio(getLink() . 'salvarInc', 'formIncluir_cliente');

		$ret .= $form;


        $param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Incluir objeto';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
	}

	public function excluir()
	{
		$id = getParam($_GET, 'id', 0);
		$data = date('Y-m-d H:i:s');
		// echo $id;
		if ($id !== 0) {
			$sql = "UPDATE
						dados_teste r
					SET
						r.CDPSTATUS = 'N', 
						r.CDPDATA_ALT = '" . $data . "'
					WHERE
						r.CDPCODIGO = " . $id;
			queryCONSULT($sql);
			// echo $sql;
			addPortalMensagem("Sucesso!<br>O programa foi exclu do!");
		}

		$ret = $this->index();
		return $ret;
	}

	public function salvarInc()
	{

		$cod = $_POST['cod'];
		$nome = $_POST['nome_dado'];
		// $data = date('Y-m-d H:i:s');


		$sql = "INSERT INTO 
					dados_teste(id, cod, nome_dado) 
				VALUES 
					('', $cod, '" . $nome . "')";      

		echo $sql;
		// die("ok");
		query($sql);
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


		$cod = $dados['cod'];
		// echo $de;
		$nome = $dados['nome_dado'];
		// echo $para;
		// $data = date('Y-m-d H:i:s');

		$sql = "UPDATE 
					dados_teste 
				SET 
					cod = $cod, 
					nome_dado = '" . $nome  . "'
				
				WHERE 
					id = $id";

		echo $sql;
		// // die('ok');
		query($sql);

		addPortalMensagem("Sucesso!<br>O programa foi editado!");


		$ret = $this->index();
		return $ret;
	}
    

    public function incluirMultiplos(){

      
    }
}
