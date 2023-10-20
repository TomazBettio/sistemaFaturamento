<?php
/*
 * Data Criação: 21/02/2022
 * Autor: Alexandre Thiel
 *
 * Descricao: Funções para geração e controle de cadastros
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class syscad01{
	
	// Tabela
	private $_tabela;
	
	//Informações sys002
	private $_sys002 = [];
	
	//Informações sys003
	private $_sys003 = [];
	
	//Informações sys008
	private $_sys008 = [];
	
	//Indica se o campo ID deve aparecer no browser e nos formulários
	private $_mostraID;
	
	//Campos da tabela sys002
	private $_camposSys002;
	
	//Campos da tabela sys003
	private $_camposSys003;
	
	public function __construct($tabela, $param = []){
		$this->_tabela = $tabela;

		$this->_mostraID = $param['mostraID'] ?? true;
		
		$this->_camposSys002 = ['id', 'tabela', 'descricao', 'chave', 'chave_tipo', 'chave_auto', 'campo_desc', 'etiqueta', 'campoativo', 'icone', 'unico'];
		$this->_camposSys003 = ['campo','descricao','etiqueta','tipo','tamanho','casas','largura','linha','linhasTA','negativo','onchange','mascara','funcao_browser','funcao_lista','opcoes','tabela_itens','validacao','nivel','gatilho','browser','usado','obrigatorio','editavel','real','pasta','alinhamento','tambrowser','inicializador','help'];
		
		if(!empty($this->_tabela)){
			$this->carregaSys002();
			$this->carregaSys003();
			$this->carregaSys008();
		}
	}
	
	private function carregaSys002(){
		$sql = "SELECT * FROM sys002 WHERE tabela = '".$this->_tabela."'";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($this->_camposSys002 as $campo){
				$this->_sys002[$campo] = $rows[0][$campo];
			}
		}
	}
	
	private function carregaSys003(){
		$sql = "SELECT * FROM sys003 WHERE tabela = '".$this->_tabela."' AND usado = 'S' ORDER BY ordem";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				foreach ($this->_camposSys003 as $campo){
					$temp[$campo] = $row[$campo];
				}
				$this->_sys003[] = $temp;
			}
		}
	}
	
	private function carregaSys008(){
		$sql = "SELECT * FROM sys008 WHERE tabela = '".$this->_tabela."' ORDER BY pasta";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['pasta'] 	= $row['pasta'];
				$temp['descricao'] = $row['descricao'];
				$temp['icone'] 	= $row['icone'];
				
				$this->_sys008[] = $temp;
			}
		}
	}
	
	//-------------------------------------------------------------------------------------- GET -------------------------------------
	
	public function getCampos($tipo = ''){
		$ret = [];
		$tipo = empty($tipo) ? '' : 'browser';
		
		foreach ($this->_sys003 as $campo){
			if($tipo == '' || ($tipo == 'browser' && $campo['browser'] == 'S')){
				$ret[] = $campo['campo'];
			}
		}
		return $ret;
	}
	
	public function getEstrutura($tipo = ''){
		$ret = [];
		$tipo = empty($tipo) ? '' : 'browser';
		
		foreach ($this->_sys003 as $campo){
			if($tipo == '' || ($tipo == 'browser' && $campo['browser'] == 'S')){
				if($tipo == 'browser'){
					$campo['width'] = $campo['tambrowser'];
				}
				$campo['readonly'] = $campo['editavel'] == 'S' ? true : false;
				$ret[] = $campo;
			}
		}
		return $ret;
	}
	
	public function getDados($tipo = '', $where){
		$ret = [];
		$tipo = empty($tipo) ? '' : 'browser';
		
		if($tipo == 'browser'){
			$campos = $this->getCampos('browser');
			$estrutura = $this->getEstrutura('browser');
		}else{
			$campos = $this->getCampos();
			$estrutura = $this->getEstrutura();
		}
		
		$sql = 'SELECT '.implode(', ', $campos).' FROM '.$this->_tabela.' WHERE '.$where;
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				foreach ($estrutura as $est){
					$campo = $est['campo'];
					$temp[$campo] = $row[$campo];
					if($tipo == 'browser' && (!empty($est['funcao_browser']) || !empty($est['tabela_itens']))){
						if(!empty($est['tabela_itens'])){
							$temp[$campo] = getTabelaDesc($est['tabela_itens'], $temp[$campo]);
						}
					}
					
					if($campo == $this->_sys002['chave']){
						$temp[$campo.'64'] = base64_encode($temp[$campo]);
					}
				}
				
				$ret[] = $temp;
			}
		}
		
		
		return $ret;
	}
	
	public function getCampoChave($base64 = false){
		$ret = $this->_sys002['chave'];
		$ret .= $base64 ? '64' : '';
		
		return $ret;
	}
	
	public function getEstruturaForm($prefixo_campo = ''){
		$ret = [];
		
		foreach ($this->_sys003 as $campos){
			$temp = [];
			
			$col_i = '';
			$col_f = '';
			if(!empty($prefixo_campo)){
				$col_i = '[';
				$col_f = ']';
			}
			
			$temp['campo'] 		= $prefixo_campo.$col_i.$campos['campo'].$col_f;
			$temp['campoBD'] 	= $campos['campo'];		// Nome do campo no banco de dados
			$temp['etiqueta']	= $campos['etiqueta'];
			$temp['tipo']		= $campos['tipo'];
			$temp['tamanho']	= $campos['tamanho'];
			$temp['largura']	= $campos['largura'];
			$temp['linha']		= $campos['linha'];
			$temp['linhasTA']	= $campos['linhasTA'];
			$temp['pasta']		= $campos['pasta'];
			$temp['help']		= $campos['help'];
			$temp['negativo']	= $campos['negativo'];
			$temp['onchange']	= $campos['onchange'];
			$temp['mascara']	= $campos['mascara'];
			$temp['opcoes']		= $campos['opcoes'];
			//$temp['readonly']	= $campos[''];
			$temp['obrigatorio']= $campos['obrigatorio'] == 'S' ? true : false;
			//$temp['valor']	= $campos[''];
			if(!empty($campos['tabela_itens'])){
				$param = [];
				//$param['branco'] = false;
				$lista = tabela($campos['tabela_itens'],$param);
				$temp['lista']	= $lista;
				$temp['tipo']	= 'A';
			}
			//$temp['']	= $campos[''];
			$temp['validacao']		= $campos['validacao'];
			$temp['inicializador'] 	= $campos['inicializador'];
			$temp['funcao_lista'] 	= $campos['funcao_lista'];
			$ret[] = $temp;
		}
		
		return $ret;
	}
	
	public function getPastasDescricoes(){
		$ret = [];
		
		if(count($this->_sys008) > 0){
			foreach ($this->_sys008 as $pasta){
				$ret[] = $pasta['descricao'];
			}
		}
		
		return $ret;
	}
	
	public function getDadosID($id = '', $where = ''){
		$ret = [];
		$estrutura = $this->getEstrutura();
		
		$sql = "SELECT * FROM ".$this->_tabela." WHERE ".$this->_sys002['chave']." = '$id' ";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($estrutura as $est){
				$campo = $est['campo'];
				$ret[$campo] = $rows[0][$campo];
				if($campo == $this->_sys002['chave']){
					$ret[$campo.'64'] = base64_encode($ret[$campo]);
				}
			}
		}
		
		return $ret;
	}
	//-------------------------------------------------------------------------------------- SET -------------------------------------
	
	//-------------------------------------------------------------------------------------- ADD -------------------------------------
	
	//-------------------------------------------------------------------------------------- UTEIS -----------------------------------
	
	public function geraSYS($tabela){
		if(!empty($tabela)){
			
			$sql = "DESCRIBE $tabela";
			$rows = query($sql);
			
			if(is_array($rows) && count($rows) > 0){
				//Verifica se já não está cadastrada na SYS002
				$sql = "SELECT * FROM SYS002 WHERE tabela = '$tabela'";
				$r2 = query($sql);
				
				if(isset($r2[0]['tabela'])){
					addPortalMensagem("Tabela $tabela já esxiste na SYS002", 'danger');
				}else{
					//Procura a chave primária
					$chave = [];
					foreach ($rows as $row){
						if($row['Key'] == 'PRI'){
							$chave[] = $row['Field'];
						}
					}
					
					$campos = [];
					$campos['tabela'] 		= $tabela;
					$campos['descricao'] 	= 'Descrição Tabela '.$tabela;
					$campos['chave'] 		= implode(',', $chave);
					$campos['chave_auto'] 	= 'N';
					$campos['campo_desc'] 	= 'campo';
					$campos['etiqueta'] 	= 'Tabela '.$tabela;
					$campos['campoativo'] 	= '';
					$campos['icone'] 		= '';
					$campos['unico'] 		= 'campo';
					
					$sql = montaSQL($campos, 'sys002');
					query($sql);
				}
				
				//Verifica e cadastra na SYS003
				$ordem = 0;
				foreach ($rows as $row){
					$ordem++;
					$campo = $row['Field'];
					$tipoTemp = $row['Type'];
					
					if(strpos($tipoTemp, 'int') !== false){
						$tipo = 'N';
					}elseif(strpos($tipoTemp, 'char') !== false){
						$tipo = 'C';
					}elseif(strpos($tipoTemp, 'double') !== false){
						$tipo = 'N';
					}
					
					if(strpos($tipoTemp, '(') !== false){
						$tam = substr($tipoTemp, strpos($tipoTemp, '(') + 1, strpos($tipoTemp, ')') - strpos($tipoTemp, '('));
					}else{
						$tam = 0;
					}
					
					$sql = "SELECT * FROM SYS003 WHERE tabela = '$tabela' AND campos = '$campo'";
					$r2 = query($sql);
					
					if(isset($r2[0]['campo'])){
						addPortalMensagem("Campo $campo já esxiste na SYS003", 'danger');
					}else{
						$campos = [];
						$campos['tabela'] 	= $tabela;
						$campos['campo'] 	= $campo;
						$campos['tipo'] 	= $tipo;
						$campos['ordem'] 	= $ordem;
						$campos['descricao']= 'Campo '.$campo;
						$campos['etiqueta'] = $campo;
						$campos['tamanho']  = $tam;
						$campos['casas']  	= 0;
			
						$sql = montaSQL($campos, 'sys003');
						query($sql);
					}
				}
			}
		}
	}
	
}