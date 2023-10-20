<?php
/*
 * Data Criacao: 06/10/2023
 * Autor: Verticais - Thiel
 *
 * Descricao: Verifica clientes sem arquivos (arquivos perdidos)
 *
 * Alteracoes;
 * 
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class perdidos{
	var $funcoes_publicas = array(
			'index' 			=> true,
	);
	
	//Nome do programa
	private $_programa;
	
	//Classe relatorio
	private $_relatorio;
	
	//Caminho dos arquivos
	private $_path;
	private $_path_insumos;
	private $_path_monofasico;
	
	public function __construct(){
		global $config;
		set_time_limit(0);
		
		$this->_programa = get_class($this);
		
		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = "Análise";
		$this->_relatorio = new relatorio01($param);
		
		$this->_path = '/var/www/mgt/html/';
		$this->_path_insumos = $this->_path.'insumos/';
		$this->_path_monofasico = $this->_path.'monofasico/';
	}
	
	public function index(){
		$ret = 	'';
		
		$processos = $this->getProcessos();
		
		$this->montaColunas();
		
		$this->_relatorio->setDados($processos);
		$this->_relatorio->setToExcel(true);
	$ret .= $this->_relatorio;
	
	return $ret;
}

private function montaColunas(){
	$this->_relatorio->addColuna(array('campo' => 'id'		, 'etiqueta' => 'ID'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	$this->_relatorio->addColuna(array('campo' => 'tipo'	, 'etiqueta' => 'Tipo'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
	$this->_relatorio->addColuna(array('campo' => 'cnpj'	, 'etiqueta' => 'CNPJ'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
	$this->_relatorio->addColuna(array('campo' => 'contrato', 'etiqueta' => 'Contrato'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
	$this->_relatorio->addColuna(array('campo' => 'razao'	, 'etiqueta' => 'Razão Social'	, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
	$this->_relatorio->addColuna(array('campo' => 'data'	, 'etiqueta' => 'Data'			, 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
	$this->_relatorio->addColuna(array('campo' => 'user'	, 'etiqueta' => 'Usuario'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
	//$this->_relatorio->addColuna(array('campo' => ''	, 'etiqueta' => ''		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
}
	

	
	
	private function getProcessos(){
		//Pega Insumos
		$insumos = $this->getProcessosInsumos();
		
		//Pega Monofasicos
		$monofasico  = $this->getProcessosMonofasico();
		
		
		return array_merge($insumos, $monofasico);
	}
	
	private function getProcessosMonofasico(){
		$ret = [];
		
		$sql =  "SELECT * FROM mgt_monofasico WHERE  diretorio = '' ORDER BY cnpj, contrato";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				
				$temp['id'] 		= $row['id'];
				$temp['tipo']		= 'Monofafico';
				$temp['cnpj'] 		= $row['cnpj'];
				$temp['contrato'] 	= $row['contrato'];
				$temp['razao'] 		= substr($row['razao'],0, 50);
				$temp['data'] 		= datas::dataMS2S($row['data_inc']);
				$temp['user'] 		= $row['usuario'];
				
				$ret[$row['id']] = $temp;
			}
		}
		
//echo "Total: ".count($ret)."<br>\n";
		$temp = [];
		if(count($ret) > 0){
			foreach ($ret as $key => $processo){
				$dir = $this->_path_monofasico.$processo['cnpj'].'_'.$processo['contrato'];
//echo "$dir<br>\n";
				if(!is_file($dir.'/arquivos/resultado.vert')){
					$temp[] = $processo;
				}else{
					$sql = "UPDATE mgt_monofasico SET diretorio = '$dir' WHERE id = '$key'";
//echo "$sql <br>\n";
					query($sql);
				}
			}
		}
		
		$ret = $temp;
		
//echo "Faltando: ".count($ret)."<br>\n";
		
		return $ret;
	}
	
	private function getProcessosInsumos(){
		$ret = [];
		
		$sql =  "SELECT * FROM mgt_insumos WHERE diretorio = '' ORDER BY cnpj";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				
				$temp['id'] 		= $row['id'];
				$temp['tipo']		= 'Insumos';
				$temp['cnpj'] 		= $row['cnpj'];
				$temp['contrato'] 	= '';
				$temp['razao'] 		= substr($row['razao'],0, 50);
				$temp['data'] 		= datas::dataMS2S($row['data_inc']);
				$temp['user'] 		= $row['user_inc'];
				
				$ret[$row['id']] = $temp;
			}
		}
		
//echo "Total: ".count($ret)."<br>\n";
		
		$temp = [];
		if(count($ret) > 0){
			foreach ($ret as $key => $processo){
				$dir = $this->_path_insumos.$key;
				if(!is_file($dir.'/I250_resumo.csv')){
					$temp[] = $processo;
				}else{
					$sql = "UPDATE mgt_insumos SET diretorio = '$dir' WHERE id = '$key'";
					//echo "$sql <br>\n";
					query($sql);
				}
			}
		}
		
		$ret = $temp;
		
//echo "Faltando: ".count($ret)."<br>\n";
		
		return $ret;
	}
}