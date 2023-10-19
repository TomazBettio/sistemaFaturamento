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
class mgt_remover_contrato_manual
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
		conectaMF();

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

		$param = [];
		$param['texto'] 	= 'Desativar';
		$param['link'] 		= getLink() . 'excluir&id=';
		$param['coluna'] 	= 'numero_ctr';
		$param['width'] 	= 30;
		$param['flag'] 		= '';
		//$param['tamanho'] 	= 'pequeno';
		$param['cor'] 		= 'info';
		$this->_tabela->addAcao($param);


		$this->_tabela->setTitulo("Logs do robô para remoção");

		$ret .= $this->_tabela;
		


		return $ret;
	}

	private function montaColunas()
	{
		$this->_tabela->addColuna(array('campo' => 'numero_ctr', 'etiqueta' => 'Numero Contrato', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'cnpj', 'etiqueta' => 'CNPJ', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'razao_social', 'etiqueta' => 'Nome da Empresa', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'dt_inc', 'etiqueta' => 'Data da última alteração', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'status', 'etiqueta' => 'Status', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'StatusContribuicao', 'etiqueta' => 'Status Contribuição', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'StatusContabil', 'etiqueta' => 'Status Contabil', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'StatusFiscal', 'etiqueta' => 'Status Fiscal', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'StatusECF', 'etiqueta' => 'Status ECF', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'TipoContribuicao', 'etiqueta' => 'Tipo Contribuição', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'TipoContabil', 'etiqueta' => 'Tipo Contabil', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'TipoFiscal', 'etiqueta' => 'Tipo Fiscal', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'TipoECF', 'etiqueta' => 'Tipo ECF', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));

		// $this->_relatorio->addColuna(array('campo' => '', 'etiqueta' => '', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
	}

	private function getDados()
	{
		// $ret = [];

		$sqlSucesso = "SELECT id, cnpj, DATE_FORMAT(data_inc,'%d/%m/%Y %H:%i:%s') as dt_inc, status, razao_social, numero_ctr, StatusContribuicao, StatusContabil, StatusFiscal, StatusECF, TipoContribuicao, TipoContabil, TipoFiscal, TipoECF FROM mgt_monofasico_log_erros  where status = 'D' order by data_inc DESC";
	
		$rows = queryMF($sqlSucesso);

		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				
				$temp = [];
				$temp['id'] 				= $row['id'];
				$temp['cnpj'] 				= $row['cnpj'];
				$temp['dt_inc'] 			= $row['dt_inc'];
				$temp['status'] 			= $row['status'];
				// $temp['tipo_erro'] = $row['tipo_erro'];
				// $temp['etapa_erro'] = $row['etapa_erro'];
				$temp['razao_social'] 		= $row['razao_social'];
				$temp['StatusContribuicao'] = $row['StatusContribuicao'];
				$temp['StatusContabil'] 	= $row['StatusContabil'];
				$temp['StatusFiscal'] 		= $row['StatusFiscal'];
				$temp['StatusECF'] 			= $row['StatusECF'];
				$temp['TipoContribuicao'] 	= $row['TipoContribuicao'];
				$temp['TipoContabil'] 		= $row['TipoContabil'];
				$temp['TipoFiscal'] 		= $row['TipoFiscal'];
				$temp['TipoECF'] 			= $row['TipoECF'];

				$temp['numero_ctr'] = $row['numero_ctr'];

				$this->_dados[] = $temp;
			}
		}

		return;

	}



	public function excluir()
	{
		$id = getParam($_GET, 'id', 0);
        $data = date('Y-m-d H:i:s');
		// echo $id;
		$sql = "UPDATE
					mgt_monofasico_log_erros
				SET
				    status = 'M'
				WHERE
					numero_ctr = '" . $id . "'";
			queryMF($sql);
        $sql = "UPDATE
					mgt_monofasico_log
				SET
				    status = 'M', data_conclusao = '" .$data. "
				WHERE
					numero_ctr = '" . $id . "'";
			queryMF($sql);
			// echo $sql;
			addPortalMensagem("Sucesso!<br>O programa foi desativados dos e-mails de LOG!");

		$ret = $this->index();
		return $ret;
	}

	
}
