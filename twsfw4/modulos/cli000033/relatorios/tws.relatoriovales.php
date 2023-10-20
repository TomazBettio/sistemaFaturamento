<?php
       /*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class relatoriovales{
	var $funcoes_publicas = array(
			'index' 		=> true
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
	
	public function __construct(){
		$this->_programa = get_class($this);
		$this->_titulo = '';
		
		$this->_teste = false;
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);

		$param= [];
		$param['filtro']= false;
		$param['info']= false;
		$this-> _relatorio->setParamTabela($param);
		// if(true){
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// }
	}
	
	public function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		
		$this->_relatorio->setTitulo("Relatório de benefícios - VT");
		$this->montaColunas();
		//if(!$this->_relatorio->getPrimeira()){
			
			
			$dados = $this->getDados();
			$this->_relatorio->setDados($dados);
			
			$this->_relatorio->setNowrap(false,0);
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	public function schedule($param = ''){
		ini_set('display_errors',0);
		ini_set('display_startup_erros',0);
		error_reporting(E_ALL);
		$this->montaColunas();
		$this->_relatorio->setToExcel(false);
		$this->_relatorio->setAuto(false);
		$this->_relatorio->setTitulo("Aniversariantes");
		log::gravaLog('AvisoCobranca', 'Inicando processo');
		
		$this->_relatorio->setTitulo("Faturas que vencerão em 3 dias:");
		$this->montaColunas();
		//if(!$this->_relatorio->getPrimeira()){
						
		$dados = $this->getDados();	
		$this->_relatorio->setDados($dados);
			
		$this->_relatorio->enviaEmail('vitor.valadas@verticais.com.br');	
	
		
	}
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo' => 'colaborador'		, 'etiqueta' => 'Colaborador'	, 'tipo' => 'T', 'width' =>  150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'departamento'	, 'etiqueta' => 'Departamento'	, 'tipo' => 'T', 'width' =>  150, 'posicao' => 'D'));
		// $this->_relatorio->addColuna(array('campo' => 'qtTri'		    , 'etiqueta' => 'Quant. Tri'	, 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
		// $this->_relatorio->addColuna(array('campo' => 'valorTri'		, 'etiqueta' => 'Valor Tri'	    , 'tipo' => 'V', 'width' =>  150, 'posicao' => 'E'));
		// $this->_relatorio->addColuna(array('campo' => 'qtTeu'	        , 'etiqueta' => 'Quant. Teu'	, 'tipo' => 'T', 'width' =>  140, 'posicao' => 'E'));
        // $this->_relatorio->addColuna(array('campo' => 'valorTeu'	    , 'etiqueta' => 'Valor Teu'	    , 'tipo' => 'V', 'width' =>  140, 'posicao' => 'E'));
        // $this->_relatorio->addColuna(array('campo' => 'qtSim'	        , 'etiqueta' => 'Quant. Sim'	, 'tipo' => 'T', 'width' =>  140, 'posicao' => 'E'));
        // $this->_relatorio->addColuna(array('campo' => 'valorSim'	    , 'etiqueta' => 'Valor Sim'	    , 'tipo' => 'V', 'width' =>  140, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'totalQt'	        , 'etiqueta' => 'Quant. Total'	, 'tipo' => 'T', 'width' =>  150, 'posicao' => 'D'));
        $this->_relatorio->addColuna(array('campo' => 'totalValesVal'	, 'etiqueta' => 'Valor diário'	, 'tipo' => 'V', 'width' =>  150, 'posicao' => 'D'));

	}
	
	private function getDados(){
			$ret = [];
            $totalQt = 0;
            $totalValesVal = 0;
            $valorTotal = 0;
			$sql = 
				"SELECT * FROM marpa_valesVT";
		//echo "$sql <br> ";
				$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['colaborador']    = $row['colaborador'];
				$temp['departamento']   = $row['departamento'];
				$temp['qtTri']          = $row['qtTri'];
				$temp['valorTri']       = $row['valorTri'];
				$temp['qtTeu']          = $row['qtTeu'];
                $temp['valorTeu']       = $row['valorTeu'];
                $temp['qtSim']          = $row['qtSim'];
				$temp['valorSim']       = $row['valorSim'];

                // $temp['totalQt']


                // $temp['totais'] = $row['Valor'] * $row['Quantidade'];

                $totalQt = ($row['qtTri'] + $row['qtTeu'] + $row['qtSim']);
                $totalValesVal = (($row['valorTri'] * $row['qtTri']) + ($row['valorTeu'] * $row['qtTeu']) + ($row['valorSim'] * $row['qtSim']));

                $temp['totalQt'] = $totalQt;
                $temp['totalValesVal'] = $totalValesVal;
				//datas::dataMS2D
				$ret[] = $temp;
			}
            // $temp = [];
            // $temp['colaborador']    = 'Valor total:';
            // // $temp['Quantidade']     = $valesTotal;
            // // $temp['totais']          = $valorTotal;

            // $ret[] = $temp;

		}
		return $ret;
	}
}