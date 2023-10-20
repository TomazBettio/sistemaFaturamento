<?php

/*
 * Data Criacao:
 * Autor:
 *
 * Descricao:
 *
 * Altera��es:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_relatorio
{

	// CNPJ cliente
	private $_cnpj;
	
	//Contrato
	private $_contrato;

	// pasta com os arquivos
	private $_path;

	// Classe relatorio
	private $_relatorio;
	// Nome do programa
	
	private $_programa;

	// Titulo
	private $_titulo;

	// Razão social do cliente
	private $_razao;
	
	//Tipo de relatorio
	private $_tipo;

	public function __construct($cnpj, $contrato, $id, $tipo = '')
	{
		global $config;
		conectaMF();

		$this->_programa = get_class($this);
		$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
		$this->_cnpj = $cnpj;
		$this->_contrato = $contrato;
		
		$this->_tipo = $tipo;

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Monofásicos';
		//$param['cancela'] = true;
		$this->_relatorio = new relatorio01($param);
		
		$this->montaColunas();
		
		set_time_limit(0);
	}

	public function __toString()
	{
		$ret = '';

		$dados = $this->getDados();
		if($this->_tipo == 'erro'){
			$this->_titulo = 'Relatório de XML Processados com ERROS';
		}else{
			$this->_titulo = 'Relatório de XML Processados - Sucesso';
		}
		$this->_relatorio->setTitulo($this->_titulo);
		$this->_relatorio->setToExcel(true,'relatorio_importacao_'.$this->_cnpj);
		
		$this->_relatorio->setDados($dados);
		
		if(count($dados) > 20000){
			$this->_relatorio->setPrint(false);
		}
		
		$ret .= $this->_relatorio;

		return $ret;
	}
	
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo' => 'dia'			, 'etiqueta' => 'Dia'			, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'hora'		, 'etiqueta' => 'Hora'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'usuario'		, 'etiqueta' => 'Usuário'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'arquivo'		, 'etiqueta' => 'Arquivo'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'obs'			, 'etiqueta' => 'Observação'	, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'emitente'	, 'etiqueta' => 'Emitente'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'destinatario', 'etiqueta' => 'Destinatario'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'operacao'	, 'etiqueta' => 'Operação'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
	}
	
	private function getDados(){
		$ret = [];
		
		$sql = "SELECT * FROM mgt_monofasico_log_xml WHERE cnpj = '".$this->_cnpj."' AND contrato = '".$this->_contrato."'";
		if($this->_tipo == 'erro'){
			$sql .= " AND erro = 'S'";
		}else{
			$sql .= " AND erro <> 'S'";
		}
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$dia = datas::dataS2D(str_replace('-', '', substr($row['data'], 0, 10)));
				$hora = substr($row['data'], 11, 8);
				$temp = [];
				$temp['dia'] 	 		= $dia;
				$temp['hora'] 	 		= $hora;
				$temp['usuario'] 		= $row['usuario'];
				$temp['arquivo'] 		= $row['arquivo'];
				$temp['obs']			= $row['obs'];
				$temp['emitente'] 	 	= $row['emitente'];
				$temp['destinatario']	= $row['destinatario'];
				$temp['operacao'] 	 	= $row['operacao'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
}
