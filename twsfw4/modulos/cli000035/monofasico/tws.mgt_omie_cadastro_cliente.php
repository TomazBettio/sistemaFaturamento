<?php
/*
 * Data Criacao 03/2023
 * Autor: Tomaz Bettio
 *
 * Descricao: interface para remoção dos logs dos contratos manuais
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
class mgt_omie_cadastro_cliente
{
	var $funcoes_publicas = array(
		'index'			=> true,
		'excluir'		=> true,
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
		conectaERP();

		$this->_programa = get_class($this);
		$this->_titulo = 'Interface para indicar contratos feitos manualmente';

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = true;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['paginacao'] = false;
		$param['titulo'] = 'Logs';
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
	
		$this->_tabela->setDados($this->_dados);

		// $param = [];
		// $param['texto'] 	= 'Desativar';
		// $param['link'] 		= getLink() . 'excluir&id=';
		// $param['coluna'] 	= 'numero_ctr';
		// $param['width'] 	= 30;
		// $param['flag'] 		= '';
		// //$param['tamanho'] 	= 'pequeno';
		// $param['cor'] 		= 'info';
		// $this->_tabela->addAcao($param);


		$this->_tabela->setTitulo("omie");

		$ret .= $this->_tabela;
		


		return $ret;
	}

	private function montaColunas()
	{
		$this->_tabela->addColuna(array('campo' => 'CLICODIGO', 'etiqueta' => 'codigo do cliente', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLIRAZAOSOCIAL', 'etiqueta' => 'razao social', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLIEMAIL', 'etiqueta' => 'email', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLICNPJ', 'etiqueta' => 'cnpj', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLICPF', 'etiqueta' => 'CLICPF', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLIFONE', 'etiqueta' => 'fone', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'ENDLOGRADOURO', 'etiqueta' => 'logradouro', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'ESTSIGLA', 'etiqueta' => 'estado', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CIDDESCRICAO', 'etiqueta' => 'cidade', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'ENDCOMPLEMENTO', 'etiqueta' => 'complemento', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'ENDBAIRRO', 'etiqueta' => 'bairro', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'CLIIE', 'etiqueta' => 'inscricao estadual', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));


		// $this->_relatorio->addColuna(array('campo' => '', 'etiqueta' => '', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
	}

	private function getDados()
	{
		// $ret = [];

		$sql = "SELECT
		cl.CLICODIGO, 
		cl.CLIIE,
		CLIEMAIL, 
		CLIRAZAOSOCIAL, 
		CLICNPJ, 
		CLICPF, 
		CLIFONE,
		e.ENDLOGRADOURO, 
		es.ESTSIGLA, 
		cd.CIDDESCRICAO, 
		e.ENDCOMPLEMENTO, 
		e.ENDBAIRRO 
	FROM 
		CLIENTES AS cl
	LEFT JOIN 
		ENDERECOS AS e 
		ON cl.CLICODIGO = e.CLICODIGO 
	LEFT JOIN 
		ESTADOS as es
		ON e.ESTCODIGO = es.ESTCODIGO 
	LEFT JOIN 
		CIDADES as cd
		ON e.CIDCODIGO = cd.CIDCODIGOIBGE 
		WHERE 
		CLISTATUS = 'S' 
        AND YEAR(CLIDATA_INC) = 2022
		AND CLIEMAIL IS NULL
        AND(
		CLIRAZAOSOCIAL IS NULL
		OR (CLICNPJ IS NULL OR CLICPF IS NULL)
		OR CLIFONE IS NULL
		OR e.ENDLOGRADOURO IS NULL
		OR es.ESTSIGLA IS NULL
		OR cd.CIDDESCRICAO IS NULL
		OR e.ENDCOMPLEMENTO IS NULL
		OR e.ENDBAIRRO IS NULL)";

	$rows = queryERP($sql);



	if (is_array($rows) && count($rows) > 0) {
		foreach ($rows as $row) {

		$temp = [];
		$temp['CLICODIGO'] 			= $row['CLICODIGO'];
		$temp['CLIRAZAOSOCIAL'] 	= $row['CLIRAZAOSOCIAL'];
		$temp['CLIEMAIL'] 			= $row['CLIEMAIL'];
		$temp['CLICNPJ'] 			= $row['CLICNPJ'];
		$temp['CLICPF'] 			= $row['CLICPF'];
		$temp['CLIFONE'] 			= $row['CLIFONE'];
		$temp['ENDLOGRADOURO'] 		= $row['ENDLOGRADOURO'];
		$temp['ESTSIGLA'] 			= $row['ESTSIGLA'];
		$temp['CIDDESCRICAO'] 		= $row['CIDDESCRICAO'];
		$temp['ENDCOMPLEMENTO'] 	= $row['ENDCOMPLEMENTO'];
		$temp['ENDBAIRRO'] 			= $row['ENDBAIRRO'];
		$temp['CLIIE'] 				= $row['CLIIE'];




		$ddd_com_zero = '';
		$telefone_sem_espaço = '';
		$pessoa_fisica = '';
		$inscricao = '';
		$posicao_ultimo_parentese = strrchr($row['CLIFONE'], ')');

		if (preg_match('/\((.*?)\)/', $row['CLIFONE'], $matches)) {
			$conteudo_dentro_dos_parenteses = $matches[1]; 
			$ddd_com_zero = '0' . $conteudo_dentro_dos_parenteses;
		}

		if ($posicao_ultimo_parentese !== false) {
			$telefone = substr($posicao_ultimo_parentese, 1);
			$telefone_sem_espaço = trim($telefone);
		}

		$documento = $row['CLICNPJ'];
		if($documento == ''){
			$documento = $row['CLICPF'];
			$pessoa_fisica = 'S';
			$inscricao = $row['CLIIE'];
		}

		$stringLimitada = substr($row['CLIRAZAOSOCIAL'], 0, 60);
		

			$data = array(
				'app_key' => '3744235255761',
				'app_secret' => '96bf60f37caa26ea799630c4622234cf',
				'call' => 'IncluirCliente',
				'param' => array(
					array(
						'codigo_cliente_integracao' => $row['CLICODIGO'],
						'email' => $row['CLIEMAIL'],
						'razao_social' => $stringLimitada,
						'nome_fantasia' => $row['CLIRAZAOSOCIAL'],
						'cnpj_cpf' => $documento,
						"telefone1_ddd"=> $ddd_com_zero,
						"telefone1_numero"=> $telefone_sem_espaço,
						'endereco' => $row['ENDLOGRADOURO'],
						'complemento' => $row['ENDCOMPLEMENTO'],
						'bairro' => $row['ENDBAIRRO'],
						'estado' => $row['ESTSIGLA'],
						'cidade' => $row['CIDDESCRICAO'],
						'inativo' => 'N',
						'pessoa_fisica' => $pessoa_fisica,
						'inscricao_estadual' => $inscricao
					)
				)
			);

			$stringLimitada = '';

			$jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);
			// var_dump($jsonData);
			
			$apiUrl = 'https://app.omie.com.br/api/v1/geral/clientes/';
			
			// Configura a solicitação cURL
			// $ch = curl_init($apiUrl);
			// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			// 	'Content-Type: application/json',
			// 	'Content-Length: ' . strlen($jsonData)
			// ));
			
			// $response = curl_exec($ch);
			
			// if (curl_errno($ch)) {
			// 	echo 'Erro na requisição cURL: ' . curl_error($ch);
			// }
			
			// // curl_close($ch);
			
			// if ($response) {
			// 	echo 'Resposta da API: ' . $response;
				
			// } else {
			// 	echo 'Não foi possível obter resposta da API.';
			// }

			$this->_dados[] = $temp;
		}
	}
		return;
	

	}



	// public function excluir()
	// {
	// 	$id = getParam($_GET, 'id', 0);
    //     $data = date('Y-m-d H:i:s');
	// 	// echo $id;
	// 	$sql = "UPDATE
	// 				mgt_monofasico_log_erros
	// 			SET
	// 			    status = 'M'
	// 			WHERE
	// 				numero_ctr = '" . $id . "'";
	// 		queryMF($sql);
    //     $sql = "UPDATE
	// 				mgt_monofasico_log
	// 			SET
	// 			    status = 'M', data_conclusao = '" .$data. "
	// 			WHERE
	// 				numero_ctr = '" . $id . "'";
	// 		queryMF($sql);
	// 		// echo $sql;
	// 		addPortalMensagem("Sucesso!<br>O programa foi desativados dos e-mails de LOG!");

	// 	$ret = $this->index();
	// 	return $ret;
	// }

	
}


