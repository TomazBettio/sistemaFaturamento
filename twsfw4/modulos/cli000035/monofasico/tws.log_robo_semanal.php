<?php
/*
 * Data Criacao: 02/2023
 * 
 * Autor:  Tomaz Bettio
 *
 * Descricao: log robo monofasico semanal
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class log_robo_semanal
{
	var $funcoes_publicas = array(
		'index' 		=> true,
		'schedule' 		=> true,
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

	private $_texto;

	public function __construct()
	{
		$this->_programa = get_class($this);
		$this->_titulo = '';
		
		$this->_teste = false;


		conectaMF();
		conectaERP();
		$this->_dados = $this->getDados();

		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);

		if (false) {
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De', 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até', 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente', 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Analista', 'variavel' => 'RECURSO', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
	}

	public function index()
	{
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();

		$dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';

		$this->_relatorio->setTitulo("Log do robô");

		// if (!$this->_relatorio->getPrimeira()) {
		$dados = $this->getDados();
		$this->montaColunas();

		$this->_relatorio->setDados($dados);

		$ret .= $this->_relatorio;

		return $ret;
	}

	public function schedule($param = ''){
		ini_set('display_errors',0);
		ini_set('display_startup_erros',0);
		error_reporting(E_ALL);
		$this->montaColunas();
		$hoje = date('d/m/Y');
		$semana = date('d/m/Y', strtotime('-4 day'));



		$dados = $this->getDados();
		$this->_relatorio->setDados($dados);

		$this->_relatorio->setTitulo("Log de Sucessos Semanal do Robô Monofásico");

		if(is_array($dados) && count($dados) > 0){
			$update = "UPDATE mgt_monofasico_log SET status = 'V' WHERE status = 'S' and data_conclusao IS NOT NULL";
			queryMF($update);
			$update = "UPDATE mgt_monofasico_log SET status = 'V' WHERE status = 'M' and data_conclusao IS NOT NULL";
			queryMF($update); 
			$this->_relatorio->setMensagemInicioEmail("Esta tabela apresenta uma lista com os Sucessos de ".$semana." até ".$hoje . " na rotina do Robô Monofásico: ");
		}else $this->_relatorio->setMensagemInicioEmail("Esta tabela apresenta uma lista com os Sucessos de ".$semana." até ".$hoje . " na rotina do Robô Monofásico: Nenhum erro foi encontrado!");


		// log::gravaLog('log_robo_semanal', 'Inicando processo');

		if ($this->_teste){
			$param = 'tomaz.bettio@verticais.com.br';
			}
		$this->_relatorio->enviaEmail($param);
	
		echo "Email enviado";

				
	}

	private function montaColunas()
	{
		$this->_relatorio->addColuna(array('campo' => 'numero_ctr', 'etiqueta' => 'Numero Contrato', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cnpj', 'etiqueta' => 'CNPJ', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'razao_social', 'etiqueta' => 'Nome da Empresa', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'dt_inc', 'etiqueta' => 'Data de inclusão', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'dt_conc', 'etiqueta' => 'Data de Conclusão', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'responsavel', 'etiqueta' => 'Responsável', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
	}


	///LOG SEMANAL
	private function getDados()
	{
		$ret = [];

		$sqlSucesso = "SELECT id, cnpj, DATE_FORMAT(data_inc,'%d/%m/%Y %H:%i:%s') as dt_inc, DATE_FORMAT(data_conclusao,'%d/%m/%Y %H:%i:%s') as dt_conc, status, razao_social, numero_ctr from mgt_monofasico_log where (status = 'S' or status = 'M') and data_conclusao IS NOT NULL";

		
		
		

		$rows = queryMF($sqlSucesso);
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {

				$temp = [];
				$temp['id'] = $row['id'];
				$temp['cnpj'] = $row['cnpj'];
				$temp['dt_inc'] = $row['dt_inc'];
				$temp['dt_conc'] = $row['dt_conc'];
				$temp['status'] = $row['status'];
				$temp['razao_social'] = $row['razao_social'];
				$temp['numero_ctr'] = $row['numero_ctr'];
				$numeroctr = $row['numero_ctr'];
				
				$sqlVerificaComercial = "SELECT USUNOME from CONTRATOS LEFT JOIN USUARIOS ON USUCODIGO = CTRVENDEDOR WHERE CTRNROCONTRATO = '" . $numeroctr .  "'";
				
				$rows = queryERP($sqlVerificaComercial);
				foreach ($rows as $row){
					$temp['responsavel'] = $row['USUNOME'];
				}


				$ret[] = $temp;
			}
		}


		


		return $ret;
	}

	
}
