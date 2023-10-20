<?php
/*
* Data Criação: 19/01/2015 - 14:45:07
* Autor: Thiel
*
* Arquivo: avariasRelato.php
* 
* 
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class avariasrelato{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	var $_relatorio;
	
	var $_produtos;
	
	var $_usuarios;

	function __construct(){
		set_time_limit(0);
		$this->_relatorio = new relatorio01(array('programa' => 'gflogistica.avariasrel', 'titulo' => 'Avarias '));
		$this->_relatorio->addColuna(array('campo' => 'leitura'	, 'etiqueta' => 'Leitura'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'user'	, 'etiqueta' => 'Usuario'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'data'	, 'etiqueta' => 'Data'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'Cod'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'prod'	, 'etiqueta' => 'Produto'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'end'		, 'etiqueta' => 'Endereco'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'quant'	, 'etiqueta' => 'Quantidade', 'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => 'gflogistica.avariasRel', 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'	, 'variavel' => 'DATAINI'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''					, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => 'gflogistica.avariasRel', 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Ate'	, 'variavel' => 'DATAFIM'	, 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''					, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => 'gflogistica.avariasRel', 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Produto'	, 'variavel' => 'PRODUTO' 	, 'tipo' => 'N', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''					, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => 'gflogistica.avariasRel', 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Usuario'	, 'variavel' => 'USUARIO' 	, 'tipo' => 'N', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'funcoesusuario::listaRecursos();'	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => 'gflogistica.avariasRel', 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Tipo'		, 'variavel' => 'TIPO' 		, 'tipo' => 'N', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''					, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '1=Analitico;2=Sintetico'));
		//ExecMethod('config.sys004.inclui',array('programa' => 'gflogistica.avariasRel', 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => '#Leitura'		, 'variavel' => 'LEITURA' 		, 'tipo' => 'N', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''					, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
			
	function index(){
		$filtro = $this->_relatorio->getFiltro();
		
		$diaIni = isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$diaFim = isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		$produto= isset($filtro['PRODUTO']) ? $filtro['PRODUTO'] : '';
		$usuario= isset($filtro['USUARIO']) ? $filtro['USUARIO'] : '';
		$tipo 	= isset($filtro['TIPO']) ? $filtro['TIPO'] : '';
		$leitura= isset($filtro['LEITURA']) ? $filtro['LEITURA'] : '';
		
		$this->_relatorio->setTitulo("Avarias");
		if(!$this->_relatorio->getPrimeira()){
			$dados = $this->getDados($diaIni, $diaFim, $produto, $usuario, $tipo, $leitura);
			
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}

		return $this->_relatorio . '';
		
	}
	
	function getDados($diaIni, $diaFim, $produto, $usuario, $tipo, $leitura){
		$ret = array();

	//	$dini = data::dataD2S($dataIni);
	//	$dfim = data::dataD2S($dataFim);
		
		$where = '';
		if($produto != '') {
			$where .= " AND produto = '$produto'";
		}
		if($usuario != '') {
			$where .= " AND user = '$usuario'";
		}
		if((int)$leitura > 0) {
			$where .= " AND leitura = $leitura";
		}
		if($tipo == 1){
			//Analitico
			$sql = "SELECT * FROM gf_avarias WHERE ativo = 'S' AND data >= '$diaIni' AND data <= '$diaFim' ";
			$sql .= $where;
		}else{
			//Sintetico
			$sql = "SELECT leitura,produto, endereco, '' data, '' user, sum(quant) quant FROM gf_avarias WHERE ativo = 'S' AND data >= '$diaIni' AND data <= '$diaFim'";
			$sql .= $where;
			$sql .= " GROUP BY leitura,produto, endereco ";
		}
		
		
//echo "$sql <br>";		
		$rows = query($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['leitura']	= $row['leitura'];
				$produto = $this->getProdDesc($row['produto']);
				if($tipo == 2 && $usuario != ''){
					$row['user'] = $usuario;
				}
				$temp['user']	= $this->getUserDesc($row['user']);
				$temp['data']	= datas::dataS2D($row['data']);
				$temp['cod']	= $row['produto'];
				$temp['prod']	= $produto;
				$temp['end']	= $row['endereco'];
				$temp['quant']	= $row['quant'];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	function getProdDesc($prod){
		if(!isset($this->_produtos[$prod])){
			$sql = "SELECT descricao FROM PCPRODUT WHERE codprod = $prod";
//echo "$sql <br> \n";
			$rows = query4($sql);
			$this->_produtos[$prod] = $rows[0][0];
		}
		return $this->_produtos[$prod];
	}
	
	function getUserDesc($user){
		if(!isset($this->_usuarios[$user])){
		    $this->_usuarios[$user] = getUsuario('nome', $user);
			//$this->_usuarios[$user] = getUsuarioNome($user);
		}
		return $this->_usuarios[$user];
	}
	
}