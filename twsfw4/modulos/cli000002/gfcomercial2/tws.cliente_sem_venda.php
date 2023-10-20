<?php
/*
 * Data Criacao: 16/05/2022
 * Autor: Thiel
 *
 * Descricao: Clientes sem venda a mais de 30 dias
 *
 * Alterações:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class cliente_sem_venda{
	var $funcoes_publicas = array(
			'index' 		=> true,
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
	
	//ERC
	private $_erc = [];
	
	//Clientes com vendas
	private $_clientes = [];
	
	
	public function __construct(){
		$this->_programa = get_class($this);
		$this->_titulo = 'Clientes não positivados.';
		
		$this->_teste = false;
		
		$param = [];
		$param['programa']	= $this->_programa;
		$param['titulo']	= $this->_titulo;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->setToExcel(true);
		
		 //ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'GD'		    , 'variavel' => 'GD'           ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		 //ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'ERC'		    , 'variavel' => 'ERC'           ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		
	}
	
	public function index(){
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();
		
		$gd 	= isset($filtro['GD']) ? $filtro['GD'] : '';
		$erc 	= isset($filtro['ERC']) ? $filtro['ERC'] : '';
		
		//$this->_relatorio->setTitulo("");
		
		if(!$this->_relatorio->getPrimeira()){
			
			$this->getERC();
			$this->getDados($gd, $erc);
			$this->montaColunas();
			$this->_relatorio->setTitulo($this->_titulo.' Data: '.date('d/m/Y'));
			$this->_relatorio->setDados($this->_dados);
		}else{
			$this->montaColunas();
		}
		
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	public function schedule($param=''){
		$this->getERC();
		$this->montaColunas();
		$this->getDados('', '');
		$this->_relatorio->setTitulo($this->_titulo.' Data: '.date('d/m/Y'));
		$this->_relatorio->setAuto(true);
		if(count($this->_dados) > 0){
			$this->_relatorio->setDados($this->_dados);
			
			if(!$this->_teste){
				$this->_relatorio->agendaEmail('', '08:00', $this->_programa, $param);
			}else{
				$this->_relatorio->agendaEmail('', '08:00', $this->_programa, 'suporte@thielws.com.br');
				$this->_relatorio->enviaEmail('suporte@thielws.com.br');
			}

			$this->_relatorio->setToExcel(false);
			$dados_erc = array();
			$dados_gd = array();
			$email_erc = array();
			$email_gd = array();
			foreach ($this->_dados as $d){
				$erc = $d['erc'];
				$gd = $d['gd'];
				
				$email_erc[$erc] = $this->_erc[$erc]['email'];
				$email_gd[$gd] = $this->_erc[$erc]['super_email'];
				
				$dados_erc[$erc][] = $d;
				$dados_gd[$gd][] = $d;
			}
			
			foreach ($dados_erc as $erc => $d){
				$this->_relatorio->setDados($d);
				if(!$this->_teste){
					if(!empty($email_erc[$erc])){
						$this->_relatorio->agendaEmail('', '08:00', $this->_programa, $email_erc[$erc]);
						log::gravaLog('clientes_nao_positivados', "Email enviado ERC - $erc - ".$email_erc[$erc]);
					}else{
						log::gravaLog('clientes_nao_positivados', "Email NÃO enviado ERC - $erc - Sem email");
					}
				}else{
					$this->_relatorio->agendaEmail('', '08:00', $this->_programa, 'suporte@thielws.com.br');
					log::gravaLog('clientes_nao_positivados', "Email teste ERC - $erc - ".$email_erc[$erc]);
				}
			}
			
			$this->_relatorio->setToExcel(true);
			foreach ($dados_gd as $gd => $d){
				$this->_relatorio->setDados($d);
				if(!$this->_teste){
					if(!empty($email_gd[$gd])){
						$this->_relatorio->agendaEmail('', '08:00', $this->_programa, $email_gd[$gd]);
						log::gravaLog('clientes_nao_positivados', "Email enviado GD - $gd - ".$email_gd[$gd]);
					}else{
						log::gravaLog('clientes_nao_positivados', "Email NÃO enviado GD - $gd - Sem email");
					}
				}else{
					$this->_relatorio->agendaEmail('', '08:00', $this->_programa, 'suporte@thielws.com.br');
					log::gravaLog('clientes_nao_positivados', "Email teste GD - $gd - ".$email_gd[$gd]);
				}
			}
		}
	}
	
	
	private function montaColunas(){
		$this->_relatorio->addColuna(array('campo' => 'codcli'	, 'etiqueta' => 'Cliente'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Nome'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'gd'		, 'etiqueta' => 'GD'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'gd_nome'	, 'etiqueta' => 'Nome'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc'		, 'etiqueta' => 'ERC'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'erc_nome', 'etiqueta' => 'Nome'		, 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'fone'	, 'etiqueta' => 'Telefone'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'endereco', 'etiqueta' => 'Endereço'  , 'tipo' => 'T', 'width' => 250, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'bairro'	, 'etiqueta' => 'Bairro'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cidade'	, 'etiqueta' => 'Cidade'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'uf'		, 'etiqueta' => 'UF'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'ultima'	, 'etiqueta' => 'Última'	, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'dias'	, 'etiqueta' => 'Dias'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
	}
	
	private function getDados($gd, $erc){
		$where = '';
		if(!empty($erc)){
			$where .= " AND CODUSUR1 IN ($erc)";
		}
		if(!empty($gd)){
			$where .= " AND CODUSUR1 IN (SELECT CODUSUR FROM PCUSUARI WHERE CODSUPERVISOR IN ($gd))";
		}
		$sql = "SELECT 
				    CODCLI,
				    CLIENTE,
				    PCCLIENT.CODUSUR1 ERC,
				    ULTIMA,
				    (SYSDATE - ULTIMA) DIAS,
					PCCLIENT.TELENT, 
					PCCLIENT.ENDERENT,
					PCCLIENT.NUMEROENT,
					PCCLIENT.MUNICENT, 
					PCCLIENT.ESTENT,
					PCCLIENT.CEPCOB, 
					PCCLIENT.BAIRROENT
				FROM
				    (SELECT 
				        PCPEDC.CODCLI CLIPED,
				        MAX(PCPEDC.DATA) ULTIMA
				    FROM 
				        PCPEDC 
				    WHERE 
				        PCPEDC.DTCANCEL IS NULL
				        AND PCPEDC.DATA >= SYSDATE - 130
				    GROUP BY
				        PCPEDC.CODCLI
				    ORDER BY
				        CODCLI),
				    PCCLIENT
				WHERE 
				    PCCLIENT.CODCLI = CLIPED
                    AND PCCLIENT.DTEXCLUSAO IS NULL
					$where
				";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['codcli'] 	= $row['CODCLI'];
				$this->_clientes[]  = $row['CODCLI'];
				$temp['cliente'] 	= substr($row['CLIENTE'], 0, 40);
				$temp['erc'] 		= $row['ERC'];
				$temp['ultima'] 	= datas::dataMS2S($row['ULTIMA']);
				$temp['dias'] 		= intval($row['DIAS']);
				$temp['gd'] 		= $this->_erc[$row['ERC']]['super'];
				$temp['gd_nome'] 	= $this->_erc[$row['ERC']]['super_nome'];
				$temp['erc_nome'] 	= $this->_erc[$row['ERC']]['nome'];
	
				$temp['fone'	] = $row['TELENT'];
				$temp['endereco'] = $row['ENDERENT'].', '.$row['NUMEROENT'];
				$temp['bairro'	] = $row['BAIRROENT'];
				$temp['cidade'	] = $row['MUNICENT'];
				$temp['uf'		] = $row['ESTENT'];
				
				
				//if($temp['dias'] > 29 && $temp['dias'] <= 60){
				if($temp['dias'] > 29){
					$this->_dados[] = $temp;
				}
			}
		}
		
		//$this->getClientesSemVenda($gd, $erc);
	}
	
	private function getClientesSemVenda($gd, $erc){
		$where = '';
		if(!empty($erc)){
			$where .= " AND CODUSUR1 IN ($erc)";
		}
		if(!empty($gd)){
			$where .= " AND CODUSUR1 IN (SELECT CODUSUR FROM PCUSUARI WHERE CODSUPERVISOR IN ($gd))";
		}
		$sql = "SELECT
				    CODCLI,
				    CLIENTE,
				    PCCLIENT.CODUSUR1 ERC
				FROM
				   PCCLIENT
				WHERE
				    PCCLIENT.DTEXCLUSAO IS NULL
    				AND NVL(PCCLIENT.BLOQUEIO,'N') = 'N' 
					$where
				";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				if(array_search($row['CODCLI'], $this->_clientes) === false){
					$temp = [];
					$temp['codcli'] 	= $row['CODCLI'];
					$temp['cliente'] 	= $row['CLIENTE'];
					$temp['erc'] 		= $row['ERC'];
					$temp['ultima'] 	= '';
					$temp['dias'] 		= '60 dias ou mais';
					$temp['gd'] 		= $this->_erc[$row['ERC']]['super'];
					$temp['gd_nome'] 	= $this->_erc[$row['ERC']]['super_nome'];
					$temp['erc_nome'] 	= $this->_erc[$row['ERC']]['nome'];
					
					$this->_dados[60][] = $temp;
				}
			}
		}
	}
	
	private function getERC(){
		$vend = getListaEmailGF('rca', true);
		if(is_array($vend) && count($vend) > 0){
			foreach ($vend as $v){
				$erc = $v['rca'];
				$this->_erc[$erc]['nome'] = substr($v['nome'], 0, 40);
				$this->_erc[$erc]['email'] = $v['email'];
				$this->_erc[$erc]['super'] = $v['super'];
				$this->_erc[$erc]['super_nome'] = $v['super_nome'];
				$this->_erc[$erc]['super_email'] = $v['super_email'];
			}
		}
	}
}