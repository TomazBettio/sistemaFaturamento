<?php 

class ajusta_comissao{
	var $funcoes_publicas = array(
			'index' 	=> true,
	);
	
	//Dadps
	private $_dados;
	
	//Classe relatorio
	private $_relatorio;
	
	//Nome do programa
	private $_programa;
	
	//Titulo do relatorio
	private $_titulo;
	
	//Comissao Leandro
	private $_leandro = [];
	
	//Comissao Luciano
	private $_luciano = [];
	
	private $_tempTotal = [];
	
	private $_teste = true;
	
	public function __construct(){
		set_time_limit(0);
		$this->_teste = false;
		
		$this->_programa = get_class($this);
		$this->_titulo = 'Comissões';
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->setTitulo('Mapa Tributário');
		
		$param = [];
		$param['paginacao']	= false;
		$param['scroll']	= false;
		$param['scrollX']	= false;
		$param['scrollY']	= false;
		$param['imprimeZero']= false;
		//$param['width']		
		$param['filtro'] 	= false;
		$param['info'] 		= false;
		$param['ordenacao']	= false;
		
//		$this->_relatorio->setParamTabela($param);
	}
	
	public function index(){
		$ret = $this->montaRelatorio();
		return $ret;
	}
	
	private function montaRelatorio(){
		$ret = '';
		$this->getDados();
		$this->montaColunas();
		
		//$email['Andrey'] 	= "comercial.andrey@grupomarpa.com.br";
		//$email['Camila'] 	= "camila@grupomarpa.com.br";
		//$email['Caroline'] 	= "caroline@grupomarpa.com.br";
		//$email['Cassia'] 	= "cassia.nobre@grupomarpa.com.br";
		//$email['Elieser'] 	= "elieser@grupomarpa.com.br";
		//$email['Felix'] 	= "felix@grupomarpa.com.br";
		//$email['KATIA'] 	= "katia@grupomarpa.com.br";
		//$email['Leandro'] 	= "leandro@grupomarpa.com.br";
		//$email['Lorena'] 	= "lorena.cintra@grupomarpa.com.br";
		//$email['Luciano'] 	= "luciano@grupomarpa.com.br";
		//$email['Lucimara'] 	= "lucimara@grupomarpa.com.br";
		//$email['Luiza'] = "luiza@grupomarpa.com.br";
		//$email['Mauricio'] = "mauricio@grupomarpa.com.br";
		//$email['Rafael'] 	= "comercial.rafael@grupomarpa.com.br";
		//$email['VALTER'] 	= "valter@grupomarpa.com.br";
		$email['Juliana']	= "juliana.cardoso@grupomarpa.com.br";
		$sessao = 0;

		$total_leandro = $this->getTotalGerente('leandro');
		$total_luciano = $this->getTotalGerente('luciano');
		
		foreach ($this->_dados as $vendedor => $ds){
			$this->_relatorio->setTituloSecao($sessao, '<br>'.$vendedor);
			$this->_relatorio->setTituloSecaoPlanilha($sessao, $vendedor);
			$this->_tempTotal = [];
			$dados = [];
			foreach ($ds as $d){
				$dados[] = $d;
				$this->addTotal($d);
			}
			$dados[] = $this->_tempTotal;
			if($vendedor == 'Leandro'){
				$com = $this->addValorComissao($total_leandro);
				$dados[] = $com;
				$this->addTotal($com,'Total Geral');
				$dados[] = $this->_tempTotal;
			}
			if($vendedor == 'Luciano'){
				$com = $this->addValorComissao($total_luciano);
				$dados[] = $com;
				$this->addTotal($com,'Total Geral');
				$dados[] = $this->_tempTotal;
			}
			$this->_relatorio->setDados($dados, $sessao);
			if(!empty($email[$vendedor])){
				$this->_relatorio->setAuto(true);
				$this->_relatorio->setToExcel(true,'Mapa_tributario');
				if(!$this->_teste){
					$this->_relatorio->enviaEmail($email[$vendedor].';sistema@marpa.com.br');
				}else{
//					echo $this->_relatorio;
					$this->_relatorio->enviaEmail('alexandre.thiel@verticais.com.br');
				}
			}
		}
exit();
		if(!$this->_teste){
			exit();
		}
		//------------------------------------------------------------- totais para os gestores
		
		
		foreach ($this->_dados as $vendedor => $ds){
			$this->_relatorio->setTituloSecao($sessao, '<br>'.$vendedor);
			$this->_relatorio->setTituloSecaoPlanilha($sessao, $vendedor);
			$this->_tempTotal = [];
			$dados = [];
			foreach ($ds as $d){
				$dados[] = $d;
				$this->addTotal($d);
			}
			$dados[] = $this->_tempTotal;
			if($vendedor == 'Leandro'){
				$com = $this->addValorComissao($total_leandro);
				$dados[] = $com;
				$this->addTotal($com,'Total Geral');
				$dados[] = $this->_tempTotal;
			}
			if($vendedor == 'Luciano'){
				$com = $this->addValorComissao($total_luciano);
				$dados[] = $com;
				$this->addTotal($com,'Total Geral');
				$dados[] = $this->_tempTotal;
			}
			$this->_relatorio->setDados($dados, $sessao);
			$sessao++;
		}
		
//		$ret .= $this->_relatorio;
		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true,'Mapa_tributario');
		if(!$this->_teste){
//			$para = 'leandro@grupomarpa.com.br;valdomiro@grupomarpa.com.br;rosemari@grupomarpa.com.br;eduardo@grupomarpa.com.br;michael@grupomarpa.com.br;greice@grupomarpa.com.br;sistema@marpa.com.br';
		}else{
			echo $this->_relatorio;
			$para = 'alexandre.thiel@verticais.com.br';
			//$para = 'leandro@grupomarpa.com.br;valdomiro@grupomarpa.com.br;rosemari@grupomarpa.com.br;eduardo@grupomarpa.com.br;michael@grupomarpa.com.br;greice@grupomarpa.com.br;sistema@marpa.com.br';
		}
		$this->_relatorio->enviaEmail($para);
		
		return $ret;
	}
	
	private function getTotalGerente($gerente){
		$ret = 0;
		$sql = "select SUM($gerente) total from mgt_comissao ORDER BY comercial, vencimento";
		$rows = query($sql);
		
		if(isset($rows[0][0])){
			$ret = $rows[0][0];
		}
	
		return $ret;
	}
	
	private function addValorComissao($valor){
		$ret = [];
		
		$ret['comissao'] 	= $valor;
		
		return $ret;
	}
	
	private function getDadosGerente($gerente, $dados){
		$ret = [];
		
		foreach ($dados as $d){
			$temp = [];
			$temp['comercial'] 	= $gerente;
			$temp['cliente'] 	= $d['cliente'];
			$temp['pago'] 		= $d['pago'];
			$temp['vencimento'] = $d['vencimento'];
			$temp['percentual'] = $d['percentual'];
			$temp['comissao'] 	= $d[$gerente];
			
			if($temp['comissao'] > 0){
				$ret[] = $temp;
				$this->addTotal($temp);
			}
		}
		
		return $ret;
	}
	
	private function addTotal($dado, $titulo=''){
		if(count($this->_tempTotal) == 0){
			$this->_tempTotal['comercial'] = 'Total';
			$this->_tempTotal['pago'] = 0;
			$this->_tempTotal['comissao'] = 0;
		}
		$this->_tempTotal['pago'] += isset($dado['pago']) ? $dado['pago'] : 0;
		$this->_tempTotal['comissao'] += $dado['comissao'];
		if(!empty($titulo)){
			$this->_tempTotal['comercial'] = $titulo;
			$this->_tempTotal['pago'] = 0;
		}
	}
	
	private function getDados(){
		$fora = ['Cristiano','Daniela','Ederson','Márcio','Ozeias','Régis','Renan','Vivian'];
		$sql = "select * from mgt_comissao ORDER BY comercial, vencimento";
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$comercial = trim($row['comercial']);
				if(array_search($comercial, $fora) === false && $row['pago'] > 0){
					$temp = [];
					$temp['comercial'] 	= $comercial;
					$temp['cliente'] 	= $row['cliente'];
					$temp['pago'] 		= $row['pago'];
					$temp['vencimento'] = $row['vencimento'];
					$temp['percentual'] = $row['percentual'] * 100;
					$temp['comissao'] 	= $row['comissao'];
					$temp['leandro'] 	= $row['leandro'];
					$temp['luciano'] 	= $row['luciano'];
					$temp['periodo'] 	= $row['periodo'];
					
					
					if(true){
						$temp['vencimento'] = $this->limpaData($temp['vencimento']);
					}
					
					if($temp['leandro'] > 0){
						$this->_luciano[] = $temp;
					}
			
					if($temp['luciano'] > 0){
						$this->_leandro[] = $temp;
					}
					
					$this->_dados[$comercial][] = $temp;
				}
			}
		}
	}
	
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo' => 'comercial'	, 'etiqueta' => 'Consultor'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'pago'		, 'etiqueta' => 'Valor'			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vencimento'	, 'etiqueta' => 'Data'			, 'tipo' => 'D', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'percentual'	, 'etiqueta' => 'Percentual'	, 'tipo' => 'N', 'width' => 100, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'comissao'	, 'etiqueta' => 'Comissão'		, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'));
	}
	
	private function ajustaDados(){
		$sql = "select * from mgt_comissao";
		$rows = query($sql);
		
		foreach ($rows as $row){
			$campos = [];
			
			$campos['pago'] = $this->limpaValor($row['pago']);
			$campos['vencimento'] = $this->limpaData($row['vencimento']);
			$campos['percentual'] =  $this->limpaValor($row['percentual']);
			$campos['comissao'] =  $this->limpaValor($row['comissao']);
			$campos['leandro'] =  $this->limpaValor($row['leandro']);
			$campos['luciano'] =  $this->limpaValor($row['luciano']);
			
			$sql = montaSQL($campos, 'mgt_comissao','UPDATE', 'id = '.$row['id']);
			query($sql);
			echo "$sql <br>\n";
		}
		
	}
	
	private function limpaData($data){
		$ret = substr($data, 0, 10);
		$ret = str_replace('/', '', $ret);
		
		return $ret;
	}
	
	private function limpaValor($valor){
		$ret = '';
		
		$ret = str_replace('R$','', $valor);
		$ret = trim($ret);
		$ret = str_replace(' ','', $ret);
		$ret = str_replace('-','', $ret);
		$ret = str_replace('.','', $ret);
		$ret = str_replace(',','.', $ret);
		
		if(empty(trim($ret))){
			$ret = 0;
		}
		
		return$ret;
	}
}


?>