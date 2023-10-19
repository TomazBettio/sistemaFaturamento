<?php
/*
 * Data Criacao: 02/2023
 * 
 * Autor:  Tomaz Bettio
 *
 * Descricao: log robo monofasico diario
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
$emailConsultor = '';

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class log_sem_procuracao
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
		
		$this->_teste = true;


		conectaMF();
		conectaERP();

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

		$this->_relatorio->setTitulo("Log de Documentos sem procuração");

		// if (!$this->_relatorio->getPrimeira()) {
		// $this->getDados();
		$this->montaColunas();

		$this->_relatorio->setDados($this->getDados());

		$ret .= $this->_relatorio;

		return $ret;
	}

	public function schedule($param = ''){
		log::gravaLog('log_robo_diario', 'Inicando processo');
		ini_set('display_errors',0);
		ini_set('display_startup_erros',0);
		error_reporting(E_ALL);
		// $hoje = date('d/m/Y');
		$this->getDados();
        
			
		// if ($this->_teste){
		// 	$param = 'tomaz.bettio@verticais.com.br';
		// }
		// $this->_relatorio->enviaEmail($param);
	
		// log::gravaLog('log_robo_diario', 'Processo finalizado, enviado email para: '.$param);
		
		echo "Email enviado";
	}

	private function montaColunas()
	{
		$this->_relatorio->addColuna(array('campo' => 'numero_ctr'	, 'etiqueta' => 'Numero Contrato'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cnpj'		, 'etiqueta' => 'CNPJ'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'razao_social', 'etiqueta' => 'Nome da Empresa'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'dt_inc'		, 'etiqueta' => 'Data de inclusão'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		// $this->_relatorio->addColuna(array('campo' => 'etapa_erro', 'etiqueta' => 'Etapa Erro', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'status'		, 'etiqueta' => 'Status'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'responsavel'	, 'etiqueta' => 'Responsavel'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'status'		, 'etiqueta' => 'Status'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'andamento'	, 'etiqueta' => 'Andamento'			, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'documentacao', 'etiqueta' => 'Documentação'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		// $this->_relatorio->addColuna(array('campo' => '', 'etiqueta' => '', 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
	}


	///LOG DIARIO
	private function getDados()
	{
		$ret = [];
        $emailComercial = '';

		$sqlSucesso = "SELECT 
							id, 
							cnpj, 
							DATE_FORMAT(data_inc,'%d/%m/%Y %H:%i:%s') as dt_inc, 
							status, 
							razao_social, 
							numero_ctr, 
							StatusContribuicao, 
							StatusContabil, 
							StatusFiscal, 
							StatusECF, 
							TipoContribuicao, 
							TipoContabil, 
							TipoFiscal, 
							TipoECF 
						FROM 
							mgt_monofasico_log_erros  
						where 
							status = 'D' 
						order by 
							dt_inc DESC
		";
	
		$rows = queryMF($sqlSucesso);

		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {

                $temp = [];
                $numeroctr = $row['numero_ctr'];

				$sqlVerificaComercial = "SELECT USUNOME, USUEMAIL from CONTRATOS LEFT JOIN USUARIOS ON USUCODIGO = CTRVENDEDOR WHERE CTRNROCONTRATO LIKE '%" . $numeroctr .  "%'";
				
				$rowsComercial = queryERP($sqlVerificaComercial);


				

				$primeiroCaracter = substr($numeroctr, 0, 1);
				if($primeiroCaracter == '0'){
					$numeroctr = substr($numeroctr, 1);
					$sqlVerificaComercial = "SELECT USUNOME, USUEMAIL from CONTRATOS LEFT JOIN USUARIOS ON USUCODIGO = CTRVENDEDOR WHERE CTRNROCONTRATO LIKE '%" . $numeroctr .  "%'";
					$rowsComercial = queryERP($sqlVerificaComercial);
				}
				

				foreach ($rowsComercial as $rowC){
					$responsavel = $rowC['USUNOME'];
                    // echo 'resp: ' . $temp['responsavel'];
                    $emailComercial = $rowC['USUEMAIL'];
				}
                

                $documentacao = '';
				$temp['documentacao'] = $documentacao;
                $procuracao = 'o existe procura';
                
				if (stripos($row['TipoContribuicao'], $procuracao) !== false){
					$documentacao .= $row['TipoContribuicao'];

				}else if (stripos($row['TipoContabil'], $procuracao) !== false){
					$documentacao .= $row['TipoContabil'];

				}else if(stripos($row['TipoECF'], $procuracao) !== false){
					$documentacao .= $row['TipoECF'];

				}
				$temp['documentacao'] .= $documentacao;


                if($temp['documentacao'] != ''){

				$temp['id'] 				= $row['id'];
				$temp['cnpj'] 				= $row['cnpj'];
				$temp['dt_inc'] 			= $row['dt_inc'];
				$temp['status'] 			= $row['status'];
				// $temp['tipo_erro'] 			= $row['tipo_erro'];
				// $temp['etapa_erro'] 		= $row['etapa_erro'];
				$temp['razao_social'] 		= $row['razao_social'];
				$temp['numero_ctr'] 		= $row['numero_ctr'];
				$temp['StatusContribuicao'] = $row['StatusContribuicao'];
				$temp['StatusContabil'] 	= $row['StatusContabil'];
				$temp['StatusFiscal'] 		= $row['StatusFiscal'];
				$temp['StatusECF'] 			= $row['StatusECF'];
				$temp['TipoContribuicao'] 	= $row['TipoContribuicao'];
				$temp['TipoContabil'] 		= $row['TipoContabil'];
				$temp['TipoFiscal'] 		= $row['TipoFiscal'];
				$temp['TipoECF'] 			= $row['TipoECF'];
                $temp['email']              = $emailComercial . ';';
                $temp['responsavel']        = $responsavel;
	
				

				$andamento = '';
				$temp['andamento'] = $andamento;

				if($temp['StatusContribuicao'] == 'PENDENTE'){
					$andamento .= '- Pendencia Contribuição; ';

				}else{
					$andamento .= '- Erro inesperado Contribuição; ';
				}if($temp['StatusContabil'] == 'PENDENTE'){
					$andamento .= '- Pendencia Contabil; ';

				}else{
					$andamento .= '- Erro inesperado Contabil; ';

				}if ($temp['StatusFiscal'] == 'PENDENTE'){
					$andamento .= '- Pendencia Fiscal; ';

				}else{
					$andamento .= '- Erro inesperado Fiscal; ';


				}if ($temp['StatusECF'] == 'PENDENTE'){
					$andamento .= '- Pendencia ECF; ';
				}else{
					$andamento .= '- Erro inesperado ECF; ';

				}
				$temp['andamento'] .= $andamento;

				// $documentacao = '';
				// $temp['documentacao'] = $documentacao;
                // $procuracao = 'o existe procura';
                
				// if (stripos($row['TipoContribuicao'], $procuracao) !== false){
				// 	$documentacao .= $row['TipoContribuicao'];

				// }else if (stripos($row['TipoContabil'], $procuracao) !== false){
				// 	$documentacao .= $row['TipoContabil'];

				// }else if(stripos($row['TipoECF'], $procuracao) !== false){
				// 	$documentacao .= $row['TipoECF'];

				// }
				// $temp['documentacao'] .= $documentacao;
                // $responsavel = 'rafa';
                if(!isset($ret[$responsavel])) {
                    $ret[$responsavel] = [];
                }
				$ret[$responsavel][] = $temp;
                // var_dump($ret[$responsavel]);
                
                
                }

                
			}
            if(is_array($ret) && count($ret) > 0) {
                foreach($ret as $r) {
                    // print_r($ret);
                    $this->_relatorio->setDados($r);
                    $this->montaColunas();
                    $this->_relatorio->setDados($r);
                    $this->_relatorio->setTitulo("Documentos sem procuração");
                    $this->_relatorio->setMensagemInicioEmail("Esta tabela apresenta uma lista com os contratos que faltam Procuração: ");
                    $this->_relatorio->enviaEmail('fiscal@grupomarpa.com.br;' .'luciano@grupomarpa.com.br;' . 'roberio.santos@grupomarpa.com.br'. 'lorena.cintra@grupomarpa.com.br'. $r[0]['email']);
                    // $this->_relatorio->enviaEmail('tomaz.bettio@verticais.com.br;'.$r[0]['USUEMAIL']);
    
                }
            }
		}

		// return $ret;
	}

	
}
