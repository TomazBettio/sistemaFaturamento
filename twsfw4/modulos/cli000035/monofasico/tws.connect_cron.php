<?php
/*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class connect_cron
{
	var $funcoes_publicas = array(
		'index' 		=> true,
		'schedule' 		=> true
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

	public function __construct()
	{

		conectaERP();
		conectaMRP();
		// conectaRH();
		$this->_programa = get_class($this);
		$this->_titulo = '';

		$this->_teste = true;

		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);

		// $param= [];
		// $param['filtro']= false;
		// $param['info']= true;
		// $this-> _relatorio->setParamTabela($param);
		// if(true){
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// }
	}

	public function index()
	{
		$ret = '';
		// $filtro = $this->_relatorio->getFiltro();

		// $dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		// $dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';

		$this->_relatorio->setTitulo("Relatório");
		$this->montaColunas();
		//if(!$this->_relatorio->getPrimeira()){


		$dados = $this->getDados();
		$this->_relatorio->setDados($dados, 0);
		$this->_relatorio->setTituloSecao(0, "");

		$ret .= $this->_relatorio;

		return $ret;
	}
	public function schedule($param = '')
	{
		// ini_set('display_errors',0);
		// ini_set('display_startup_erros',0);
		// error_reporting(E_ALL);
		$this->montaColunas();
		$hoje = date('d/m/Y');

		$dados = $this->getDados();
		$this->_relatorio->setDados($dados);
		// $this->_relatorio->setTitulo("");


		// if(is_array($dados) && count($dados) > 0){
		// 	$update = "UPDATE mgt_monofasico_log SET status = 'S' WHERE status = 'D'";
		// 	queryMF($update);
		// 	$this->_relatorio->setMensagemInicioEmail("Esta tabela apresenta uma lista com os erros do dia ".$hoje." (podendo haver datas do dia anterior em função do horario da atualização) na rotina do Robô Monofásico: ");
		// }else {
		// 	$update = "UPDATE mgt_monofasico_log SET status = 'S' WHERE status = 'D'";
		// 	queryMF($update);
		// 	$this->_relatorio->setMensagemInicioEmail("Esta tabela apresenta uma lista com os erros do dia ".$hoje." na rotina do Robô Monofásico: Nenhum erro foi encontrado!");
		// }



		// log::gravaLog('log_robo_diario', 'Inicando processo');
		if ($this->_teste) {
			$param = 'tomaz.bettio@verticais.com.br';
		}
		$this->_relatorio->enviaEmail($param);

		echo "Email enviado";
	}


	private function montaColunas()
	{
		$this->_relatorio->addColuna(array(
			'campo'    => 'numero_contrato',
			'etiqueta' => 'CODIGO',
			'tipo'  	  => 'T',
			'width' 	  =>  80,
			'posicao'  => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo'    => 'valor_bruto',
			'etiqueta' => 'Nome do conectado',
			'tipo'  	  => 'T',
			'width' 	  =>  80,
			'posicao'  => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo'    => 'CXPCODIGO',
			'etiqueta' => 'VALOR',
			'tipo'     => 'T',
			'width'    =>  90,
			'posicao'  => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo'    => 'nome_connect',
			'etiqueta' => 'VALOR PARCELA',
			'tipo'     => 'T',
			'width'    =>  90,
			'posicao'  => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo'    => 'cnpj_connect',
			'etiqueta' => 'Codigo cliente',
			'tipo'     => 'T',
			'width'    =>  90,
			'posicao'  => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo'    => 'USUCODBANCO',
			'etiqueta' => 'Data de inclusão da fatura',
			'tipo'     => 'T',
			'width'    =>  90,
			'posicao'  => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo'    => 'USUCONTAC',
			'etiqueta' => 'Valor a receber',
			'tipo'     => 'T',
			'width'    =>  90,
			'posicao'  => 'E'
		));
	}

	private function getDados()
	{
		$ret = [];

		$sql = "SELECT 
				c.CLINOMEFANTASIA,
				p.TABDESCRICAO AS PROCEDIMENTO,                        
				f.TABDESCRICAO AS FORMAPGTO,
				u.USUNOME,
				ctr.CTRVALORARECEBER,
				cxp.CXPVALOR,
				cont.CTRNROCONTRATO,
				cont.CTRDATA_INC,
				cxp.CXPCODIGO,
				u.USUCNPJ,
				u.USUCODBANCO,
				u.USUCODAG,
				u.USUCONTAC,
				u.USUNOSSONUM,
				u.USUPORCENTC
				FROM
				CONTASARECEBERXPARCELAS cxp
				LEFT JOIN CONTASARECEBER ctr ON ctr.CTRCODIGO = cxp.CTRCODIGO
				LEFT JOIN CLIENTES c ON c.CLICODIGO = ctr.CLICODIGO
				LEFT JOIN TABELAS p ON p.TABCODIGO = ctr.TABCENTROCUSTO
				LEFT JOIN TABELAS f ON f.TABCODIGO = cxp.CXPFORMAPAGAMENTO
				LEFT JOIN USUARIOS u ON u.USUCODIGO = ctr.CTRCONSULTOR
				LEFT JOIN CONTRATOS cont ON ctr.CTRCONTRATO = cont.CTRCODIGO
				LEFT JOIN APURACAOMONOFASICO a ON a.CTRCODIGO = cont.CTRCODIGO
				WHERE cont.CTRDATA_INC > '2023-01-01' 
				ORDER BY CTRDATA_INC DESC";
		
		$rb = queryERP($sql);


		if (is_array($rb) && count($rb) > 0) {

			foreach ($rb as $row) {

				$temp = [];
				$temp['mgt_cxpcodigo'] = $row['CXPCODIGO'];
				$temp['numero_contrato'] = $row['CTRNROCONTRATO'];
				$temp['valor_bruto'] = $row['CTRVALORARECEBER'];
				$temp['nome_connect'] = $row['USUNOME'];
				$temp['cnpj_connect'] = $row['USUCNPJ'];
				$temp['USUCODBANCO'] = $row['USUCODBANCO'];
				$temp['USUCONTAC'] = $row['USUCONTAC'];
				$temp['USUNOSSONUM'] = $row['USUNOSSONUM'];
				$temp['USUPORCENTC'] = $row['USUPORCENTC'];
				$temp['data_inc_ctr'] = $row['CTRDATA_INC'];
				

				if(substr($temp['CTRNROCONTRATO'], 0 , 1) == 'C'){
					$cxpcodigo = $temp['CXPCODIGO'];
					$sql_verifica = "SELECT CXPCODIGO FROM dados_connect WHERE mgt_cxpcodigo = $cxpcodigo ";
					$row = queryERP($sql_verifica);
					foreach ($row as $r){
						if(count($r) == 0){
							$sql = montaSQL($temp, 'dados_connect');
							queryMRP($sql);
						}else{
							echo 'ja existe' . $cxpcodigo;
						}
					}
					
				  }
				}


				$ret[] = $temp;
			}

		return $ret;
	}
}
