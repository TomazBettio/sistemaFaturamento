<?php
/*
 * Data Criacao: 22/08/18
 * Autor: TWS
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class carta_cobranca{
	var $funcoes_publicas = array(
			'index' 		=> true,
			'enviarEmails'	=> true,
	);
	
	//Programa
	private $_programa;
	
	//Rotina
	private $_rotina;
	
	//Configurações
	private $_configuracoes;
	
	//Link
	private $_link;
	
	//Informações de clientes
	private $_clientes;
	
	//Informações de Titulos
	private $_titulos;
	
	//Indica se é um teste de schedule
	private $_teste;
	
	function __construct(){
		$this->_programa = 'carta_cobranca';
		$this->_link = 'index.php?menu='.getModulo().'.'.getClasse().'.';
		
		$param = [];
		$param['titulo'] = 'Rotina Cartas de Cobrança';
		$param['botaoConfiguracao'] = true;
		$param['filtro'] = true;
		$param['programa'] = $this->_programa;
		
		$this->_rotina = new rotina01($param);
		$this->_configuracoes = $this->_rotina->getParametros();
//print_r($this->_configuracoes);die();		
		
		if(false){
			$this->adicionaParametros($param);
		}
		
		$this->_teste = false;
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => getEmp(), 'fil' => '', 'ordem' => '1', 'pergunta' => 'Lista'	, 'variavel' => 'TIPO'	,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'dias4=4 dias;dias7=7 a 30 dias;dias12=12 dias;dias17=17 dias;dias30=Acima 30 dias'));
	}
	

	function index(){
		$ret = '';
		
		if(!$this->_rotina->isPrimeiro()){
			$filtro = $this->_rotina->getFiltro();
			$dados = [];
			$titulo = 'Títulos - ';
			switch ($filtro['TIPO']) {
				case 'dias4':
					$dados = $this->getDados(4, true);
					$titulo .= '4 dias vencidos';
					break;
				case 'dias12':
					$dados = $this->getDados(12, true);
					$titulo .= '12 dias vencidos';
					break;
				case 'dias17':
					$dados = $this->getDados(17, true);
					$titulo .= '17 dias vencidos';
					break;
				case 'dias7':
					$dados = $this->getDados(7);
					$titulo .= '7 a 30 dias vencidos';
					break;
				case 'dias30':
					$dados = $this->getDados(30);
					$titulo .= 'mais de 30 dias e não protestados';
					break;
			}
			$ret .= $this->browser($dados, $titulo, $filtro['TIPO']);
		}else{
			$ret .= $this->_rotina;
		}
		
		return $ret;
	}
	
	function schedule($param){
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);
		error_reporting(E_ALL);
		
		$dias = trim($param);
		$tipo = '';
		$dados = $this->getDados($dias, true);
		log::gravaLog('Carta_Cobranca', 'Prazo: '.$dias.'  Titulos: '.count($dados));
		switch ($dias) {
			case '4':
				$tipo = 'dias4';
				break;
			case '7':
				$tipo = 'dias7';
				break;
			case '12':
				$tipo = 'dias12';
				break;
			case '17':
				$tipo = 'dias17';
				break;
			case '30':
				$tipo = 'dias30';
				break;
		}
		if(!empty($tipo)){
			$this->enviarEmails($dados, $tipo, $dias);
		}
	}
	
	function enviarEmails($dados = [], $tipo = '', $dias = 0){
		$titulosEnviados = [];
		$teste = $this->_configuracoes['TESTE'];
		//Se for schedule
		if(count($dados) == 0){
			$teste = $this->_teste;
		}
		$emailResumo = $this->_configuracoes['EMAIL_RESUMO'];
		$tituloEmail = $this->_configuracoes['TITULO_EMAIL'];
		
		$email = $this->_configuracoes['EMAIL'];
		$emissor = $this->_configuracoes['EMISSOR_EMAIL'];
		
		$emailsender = [];
		$emailsender[0] = $email;
		$emailsender[1] = $emissor;
		
		$titulos = $this->separaTitulos($dados);
		if(empty($tipo)){
			$tipo = getParam($_POST, 'tipoCarta');
		}
		$enviadosTeste = 0;
//print_r($titulos);die();
		if(count($titulos) > 0){
			log::gravaLog('Carta_Cobranca', 'Quantidade: '.count($titulos).'  Teste: '.$teste);
			foreach ($titulos as $cliente => $carteiras){
				foreach ($carteiras as $carteira => $tit){
					$carta = $this->getCarta($cliente, $carteira, $tit, $tipo);
					if($this->_teste){
						if($enviadosTeste < 10){
							$enviado = enviaEmailAntigo('suporte@thielws.com.br', 'Teste - '.$tituloEmail, $carta, [], $emailsender);
							$enviadosTeste++;
						}
					}elseif($teste == 'S'){
						$enviado = enviaEmailAntigo($emailResumo, 'Teste - '.$tituloEmail, $carta, [], $emailsender);
						$enviadosTeste++;
						//$this->gravaLog($cliente, $tit);
					}else{
						$email = $this->_clientes[$cliente]['email'];
						$bcc = '';
						//$bcc = 'suporte@thielws.com.br';
						$enviado = enviaEmailAntigo($email, $tituloEmail, $carta, [], $emailsender, [], [],$bcc);
						$this->gravaLog($cliente, $tit, $dias);
					}
					
					//Grava LOG
					if($enviado === true){
						foreach ($tit as $titulo){
							$temp = [];
							$temp['codcli'] 	= $titulo['info']['codcli'];
							$temp['cliente'] 	= $titulo['info']['cliente'];
							$temp['cnpj']		= $titulo['info']['cnpj'];
							$temp['rede'] 		= $titulo['info']['rede'];
							$temp['atividade'] 	= $titulo['info']['atividade'];
							$temp['duplicata'] 	= $titulo['info']['duplicata'];
							$temp['parcela'] 	= $titulo['info']['parcela'];
							$temp['vencimento'] = $titulo['info']['vencimento'];
							$temp['dias'] 		= $titulo['info']['dias'];
							$temp['valor'] 		= $titulo['info']['valor'];
							$temp['cobranca'] 	= $titulo['info']['codcob'];
							$temp['email'] 		= $titulo['info']['email'];
	
							$titulosEnviados[] = $temp; 
						}
					}
				}
			}
		}
		//Envia resumo de emails
//print_r($titulosEnviados);
		if(count($titulosEnviados) > 0){
			$emailResumo .= ';suporte@thielws.com.br';
			$this->enviaEmailResumo($titulosEnviados, $emailResumo);
			//Quando for por schedule não adiciona a mensagem
			if($dias == 0){
				addPortalMensagem('', 'Emails enviados com sucesso!');
			}
		}else{
			//Quando for por schedule não adiciona a mensagem
			if($dias == 0){
				addPortalMensagem('', 'Houve algum problema no envio dos emails, favor tentar novamente.','erro');
			}
		}
		//print_r($this->_clientes);
		//print_r($this->_titulos);
		//print_r($_POST);
		//print_r($titulosEnviados);
		
		return $this->index();
	}
	
	private function enviaEmailResumo($titulosEnviados, $emailResumo){
		$param = [];
		$param['programa']	= $this->_programa;
		$param['titulo']	= 'Emails cobrança enviados '.date('d/m/y');
		$relatorio = new relatorio01($param);
		
		$relatorio->addColuna(array('campo' => 'codcli'		, 'etiqueta' => 'Cod.'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'cnpj'		, 'etiqueta' => 'CNPJ'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'rede'		, 'etiqueta' => 'Rede'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'atividade'	, 'etiqueta' => 'Atividade'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$relatorio->addColuna(array('campo' => 'duplicata'	, 'etiqueta' => 'Duplicata'		, 'tipo' => 'T', 'width' => 110, 'posicao' => 'C'));
		$relatorio->addColuna(array('campo' => 'parcela'	, 'etiqueta' => 'Parcela'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$relatorio->addColuna(array('campo' => 'vencimento'	, 'etiqueta' => 'Vencimento'	, 'tipo' => 'T', 'width' => 110, 'posicao' => 'C'));
		$relatorio->addColuna(array('campo' => 'dias'		, 'etiqueta' => 'Dias Venc.'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'C'));
		$relatorio->addColuna(array('campo' => 'valor'		, 'etiqueta' => 'Valor'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));
		$relatorio->addColuna(array('campo' => 'cobranca'	, 'etiqueta' => 'Cobrança'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
		$relatorio->addColuna(array('campo' => 'email'		, 'etiqueta' => 'Email'			, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		
		$relatorio->setAuto(true);
		$relatorio->setToExcel(true, $this->_programa.'_'.date('d_m_y'));
		$relatorio->setDados($titulosEnviados);
		$relatorio->setEnviaTabelaEmail(false);
		$relatorio->enviaEmail($emailResumo);
		log::gravaLog($this->_programa, 'Enviado email resumo : '.$emailResumo);
			
	}
	
	private function browser($dados, $titulo, $tipo){
		$ret = '';
		$param = [];
		$param['paginacao'] = false;
		$param['titulo']	= $titulo;
		$tabela = new Tabela01($param);
		
		$tabela->addColuna(array('campo' => 'sel' 		, 'etiqueta' => 'Enviar'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));  
		$tabela->addColuna(array('campo' => 'enviado' 	, 'etiqueta' => 'Enviado'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$tabela->addColuna(array('campo' => 'codcli' 	, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));  
		$tabela->addColuna(array('campo' => 'cliente' 	, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));  
		$tabela->addColuna(array('campo' => 'rede' 		, 'etiqueta' => 'Rede'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));  
		$tabela->addColuna(array('campo' => 'atividade'	, 'etiqueta' => 'Cod.Ativ'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));  
		$tabela->addColuna(array('campo' => 'duplicata'	, 'etiqueta' => 'Duplicata'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));  
		$tabela->addColuna(array('campo' => 'parc' 		, 'etiqueta' => 'Parc.'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));  
		$tabela->addColuna(array('campo' => 'vencimento', 'etiqueta' => 'Vencimento'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));  
		$tabela->addColuna(array('campo' => 'dias' 		, 'etiqueta' => 'Dias Ab.'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));  
		$tabela->addColuna(array('campo' => 'valor'		, 'etiqueta' => 'Valor'			, 'tipo' => 'T', 'width' => 100, 'posicao' => 'D'));  
		$tabela->addColuna(array('campo' => 'codcob'	, 'etiqueta' => 'Cobrança'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));  
		$tabela->addColuna(array('campo' => 'protesto' 	, 'etiqueta' => 'Dt.Protesto'	, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));  
		
		$tabela->setDados($dados);
		
		$botaoCancela = [];
		$botaoCancela["onclick"]= "setLocation('".$this->_link."index')";
		$botaoCancela["texto"]	= "Cancelar";
		$botaoCancela['cor'] = 'warning';
		$tabela->addBotaoTitulo($botaoCancela);
		
		$ret .= $tabela;
		//echo $ret;
		
		$param = [];
		$param['tamanho'] = 'padrao';
		$param['texto'] = 'Enviar emails Selecionados';
		$ret .= formbase01::formSend($param);	$param = [];
		
		$param = [];
		$param['nome']	= 'tipoCarta';
		$param['valor']	= $tipo;
		$param['id']	= 'tipoCartaID';
		$ret .= formbase01::formHidden($param);
		
		$param = [];
		$param["acao"] 	= $this->_link."enviarEmails";
		$param["id"] 	= "formEmails";
		$ret = formbase01::form($param, $ret);
		
		return $ret;
	}

	//------------------------------------------------------------------------------------------- VO ----------------------------------
	
	private function gravaLog($cliente, $tit, $dias){
		$email = $this->_clientes[$cliente]['email'];
		foreach ($tit as $titulo){
			$campos = [];
			$campos['cliente'] 	= $cliente;
			$campos['duplicata']= $titulo['duplicata'];
			$campos['parcela'] 	= $titulo['parcela'];
			$campos['data'] 	= date('Ymd');
			$campos['hora'] 	= date('H:i');
			$campos['usuario'] 	= getUsuario();
			$campos['email'] 	= $email;
			$campos['dias'] 	= $dias;
			
			if(!$this->_teste){
				$sql = montaSQL($campos, 'gf_email_cobranca');
			}

			query($sql);
		}
	}
	
	//------------------------------------------------------------------------------------------- GETs --------------------------------
	
	private function getDados($dias = 0, $exato = false){
		$ret = [];
		$where = '';
		$clientesFora = $this->ajustaLista($this->_configuracoes['CLIENTE_FORA']);
		$redeFora = $this->ajustaLista($this->_configuracoes['REDE_FORA']);
		$selecionados = $this->_configuracoes['SELECIONADOS'];
		
		if(!empty(trim($clientesFora) )){
			$where .= " AND PCPREST.CODCLI NOT IN ($clientesFora) \n";
		}
		
		if(!empty(trim($redeFora))){
			$where .= " AND PCCLIENT.CODREDE NOT IN ($redeFora) \n";
		}
		
//print_r($this->_configuracoes);
		if($exato){
			$where .= " AND TRUNC(PCPREST.DTVENC) = TRUNC(SYSDATE) - $dias";
		}else{
			if($dias == 7 || $dias == 4){
				$where .= " AND TRUNC(PCPREST.DTVENC) <= TRUNC(SYSDATE) - $dias AND TRUNC(PCPREST.DTVENC) >= TRUNC(SYSDATE) - 30";
			}elseif($dias == 30){
				$where .= " AND TRUNC(PCPREST.DTVENC) <= TRUNC(SYSDATE) - 30 AND (SELECT PCLOGCOBMAG.DATA FROM PCLOGCOBMAG WHERE PCPREST.NUMTRANSVENDA = PCLOGCOBMAG.NUMTRANSVENDA AND PCPREST.PREST = PCLOGCOBMAG.PREST AND PCLOGCOBMAG.CODOCORRENCIA = '40' AND ROWNUM = 1 ) IS NULL";
			}
		}
		
		$codcob = "'001','041','341','1341','1001','1237'";
		//12 e 17 dias somente PEFIN
		if($dias == 12 || $dias == 17){
			$codcob = "'1341','1001'";
		}
		
		$sql = "
			SELECT  
				--PCUSUARI.CODSUPERVISOR,
				--PCCLIENT.CODUSUR1,
				--PCSUPERV.NOME,
				--PCUSUARI.NOME,
				PCPREST.DTVENC,
				(SELECT ((TRUNC(PCPREST.DTVENC) - TRUNC(SYSDATE)) * -1) FROM DUAL WHERE PCPREST.DTVENC < SYSDATE)DIAS_ATRASO,
				(SELECT PCLOGCOBMAG.DATA FROM PCLOGCOBMAG WHERE PCPREST.NUMTRANSVENDA = PCLOGCOBMAG.NUMTRANSVENDA AND PCPREST.PREST = PCLOGCOBMAG.PREST AND PCLOGCOBMAG.CODOCORRENCIA = '40' AND ROWNUM = 1 ) DTPROTESTO,
				(SELECT SUM(PCLOGCOBMAG.VLCUSTAS) FROM PCLOGCOBMAG WHERE PCPREST.NUMTRANSVENDA = PCLOGCOBMAG.NUMTRANSVENDA AND PCPREST.PREST = PCLOGCOBMAG.PREST AND PCLOGCOBMAG.CODOCORRENCIA = '40') VLCUSTAS,
				PCPREST.DUPLIC,
				PCPREST.PREST,
				PCPREST.DTEMISSAO,
				PCPREST.DTVENC,
				PCPREST.VALOR,
				PCPREST.CODCLI,
				PCCLIENT.CLIENTE,
				PCPREST.CODCOB,
				PCCLIENT.CODATV1,
				PCATIVI.RAMO,
				PCCLIENT.CODREDE,
                PCREDECLIENTE.DESCRICAO REDE,
				PCPREST.BOLETO,
PCPREST.NOSSONUMBCO,
PCPREST.LINHADIG,
PCPREST.CODBARRA,
PCPREST.CODBANCOCM
			FROM 
				PCPREST,
				PCCLIENT,
				PCUSUARI,
				PCSUPERV,
				PCATIVI,
                PCREDECLIENTE
			WHERE 
				PCPREST.DTPAG IS NULL
				AND PCCLIENT.CODREDE = PCREDECLIENTE.CODREDE (+)
				AND PCPREST.CODCOB IN ($codcob)
				AND PCPREST.CODCLI = PCCLIENT.CODCLI (+)
				--AND PCPREST.CODUSUR = PCUSUARI.CODUSUR (+)
				AND PCCLIENT.CODUSUR1 = PCUSUARI.CODUSUR (+)
				AND PCCLIENT.CODATV1 = PCATIVI.CODATIV (+)
				AND PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
				$where
				and pcclient.codatv1 not in (7,4,15,5)
			ORDER BY 
				pcprest.codcli,
				pcprest.dtvenc
		";
//echo "$sql <br>\n";
		$rows = query4($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$enviado = $this->verificaTituloEnviado($row['DUPLIC'], $row['PREST']);
				
				$checked = ($enviado == '' && $selecionados == 'S')? 'checked' : '';
				$campo = $row['DUPLIC'].'_'.$row['PREST'];
				$temp['sel'] = '<input name="enviar['.$campo.']" type="checkbox" value="'.$row['CODCLI'].'" '.$checked.' id="'.$campo.'">';
				$temp['enviado']	= $enviado;
				$temp['codcli'] 	= $row['CODCLI'];
				$temp['cliente'] 	= $row['CLIENTE'];
				$temp['rede'] 		= $row['CODREDE'].'-'.$row['REDE'];
				$temp['atividade']	= $row['CODATV1'].'-'.$row['RAMO'];
				$temp['duplicata'] 	= $row['DUPLIC'];
				$temp['parc'] 		= $row['PREST'];
				$temp['emissao'] 	= datas::dataS2D(datas::dataMS2S($row['DTEMISSAO']));
				$temp['vencimento']	= datas::dataS2D(datas::dataMS2S($row['DTVENC']));
				$temp['dias'] 		= $row['DIAS_ATRASO'];
				$temp['valor'] 		= $row['VALOR'];
				$temp['codcob'] 	= $row['CODCOB'];
				$temp['protesto'] 	= $row['DTPROTESTO'];
				//$temp[''] = $row[''];
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	private function getInfoCli($cliente){
		$ret = [];
		
		if(!isset($this->_clientes[$cliente])){
			$sql = "SELECT CLIENTE, CGCENT, EMAIL, EMAILCOB FROM PCCLIENT WHERE CODCLI = $cliente";
			$rows = query4($sql);
			if(count($rows) > 0){
				$ret['nome'] = $rows[0]['CLIENTE'];
				$ret['cnpj'] = $rows[0]['CGCENT'];
				$ret['email']= $rows[0]['EMAILCOB'];
				if(empty(trim($ret['email']))){
					$ret['email']= $rows[0]['EMAIL'];
				}
				
				$this->_clientes[$cliente] = $ret;
			}
		}
		$ret = $this->_clientes[$cliente];
		
		return $ret;
	}

	private function getInfoTitulo($duplicata, $parcela, $cliente){
		$ret = [];
		$sql = "
			SELECT  
				PCPREST.DTVENC,
				(SELECT ((TRUNC(PCPREST.DTVENC) - TRUNC(SYSDATE)) * -1) FROM DUAL WHERE PCPREST.DTVENC < SYSDATE)DIAS_ATRASO,
				PCPREST.DUPLIC,
				PCPREST.PREST,
				PCPREST.DTEMISSAO,
				PCPREST.VALOR,
				PCPREST.CODCLI,
				PCCLIENT.CLIENTE,
				PCPREST.CODCOB,
				PCCLIENT.CODATV1,
				PCATIVI.RAMO,
				PCCLIENT.CODREDE,
                PCREDECLIENTE.DESCRICAO REDE,
				PCCLIENT.CGCENT, 
				PCCLIENT.EMAIL
			FROM 
				PCPREST,
				PCCLIENT,
				PCUSUARI,
				PCSUPERV,
				PCATIVI,
                PCREDECLIENTE
			WHERE 
				PCPREST.DTPAG IS NULL
				AND PCCLIENT.CODREDE = PCREDECLIENTE.CODREDE (+)
				AND PCPREST.CODCLI = PCCLIENT.CODCLI (+)
				AND PCCLIENT.CODUSUR1 = PCUSUARI.CODUSUR (+)
				AND PCCLIENT.CODATV1 = PCATIVI.CODATIV (+)
				AND PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
				AND PCPREST.DUPLIC = $duplicata
				AND PCPREST.PREST = '$parcela'
				";
		//echo "$sql <br>\n";
		$rows = query4($sql);
		if(count($rows) > 0){
			$ret['duplicata'] 	= $duplicata;
			$ret['parcela'] 	= $parcela;
			$ret['vencimento'] 	= datas::dataS2D(datas::dataMS2S($rows[0]['DTVENC']));
			$ret['dias'] 		= $rows[0]['DIAS_ATRASO'];
			$ret['valor'] 		= formataReais($rows[0]['VALOR']);
			$ret['codcli'] 		= $rows[0]['CODCLI'];
			$ret['cliente'] 	= $rows[0]['CLIENTE'];
			$ret['rede'] 		= $rows[0]['CODREDE'].'-'.$rows[0]['REDE'];
			$ret['atividade']	= $rows[0]['CODATV1'].'-'.$rows[0]['RAMO'];
			$ret['dias'] 		= $rows[0]['DIAS_ATRASO'];
			$ret['codcob'] 		= $rows[0]['CODCOB'];
			$ret['cnpj'] 		= $rows[0]['CGCENT'];
			$ret['email'] 		= $rows[0]['EMAIL'];
			
			$this->_titulos[$cliente][] = $ret;
		}
		
		return $ret;
	}
	
	
	private function verificaTituloEnviado($duplicata, $prestacao){
		$ret = '';
		$sql = "SELECT * FROM gf_email_cobranca WHERE duplicata = $duplicata AND parcela = '$prestacao'";
		$rows = query($sql);
		if(count($rows) > 0){
			$ret = 'S';
		}
		
		return $ret;
	}
	//------------------------------------------------------------------------------------------- UTEIS --------------------------------
	
	private function getCarta($cliente, $carteira, $titulos, $tipo){
		$templ = '';
		$template = 'TEMPLATE_';
		if($carteira == 'P'){
			$template = 'TEMPLATE_PEFIN_';
		}
		switch ($tipo) {
			case 'dias4':
				$templ = $template.'4';
				break;
			case 'dias7':
				$templ = $template.'7';
				break;
			case 'dias12':
				$templ = $template.'12';
				break;
			case 'dias17':
				$templ = $template.'17';
				break;
			case 'dias30':
				$templ = $template.'30';
				break;
		}
//echo "Template: $templ <br>\n";
		if(!empty($templ)){
			$template = $this->_configuracoes[$templ];
			
			$infoCli = $this->getInfoCli($cliente);
			$tabTitulos = $this->getTabelaTitulos($titulos, $cliente);
			
			$template = str_replace('@@CLIENTE_NOME', $infoCli['nome'], $template);
			$template = str_replace('@@CLIENTE_CNPJ', $infoCli['cnpj'], $template);
			
			if(!empty($tabTitulos )){
				$template = str_replace('@@CLIENTE_TITULOS', $tabTitulos, $template);
			}else{
				$template = '';
			}
		}else{
			return false;
		}
		
		return $template;
	}
	
	private function getTabelaTitulos($titulos, $cliente){
		global $nl;
		$ret = '';
		if(count($titulos) > 0){
			$ret .= '<table border="1" cellpadding="1" cellspacing="1" style="width:500px" summary="Títulos Vencidos">'.$nl;
//			$ret .= '<caption>T&iacute;tulos Vencidos</caption>'.$nl;
			$ret .= '<thead>'.$nl;
			$ret .= '<tr>'.$nl;
			$ret .= '<th scope="col"><div align="center">Duplicata</div></th>'.$nl;
			$ret .= '<th scope="col"><div align="center">Parcela</div></th>'.$nl;
			$ret .= '<th scope="col"><div align="center">Vencimento</div></th>'.$nl;
			$ret .= '<th scope="col"><div align="center">Valor (R$)</div></th>'.$nl;
			$ret .= '<th scope="col"><div align="center">Dias Vencidos</div></th>'.$nl;
			$ret .= '</tr>'.$nl;
			$ret .= '</thead>'.$nl;
			$ret .= '<tbody>'.$nl;
			
			foreach ($titulos as $titulo){
				//$infoTitulo = $this->getInfoTitulo($titulo['duplicata'], $titulo['parcela'], $cliente);
//print_r($titulo['info']);
				if(count($titulo['info']) > 0){
					$ret .= '<tr>'.$nl;
					$ret .= '<td><div align="center">'.$titulo['info']['duplicata'].'</div></td>'.$nl;
					$ret .= '<td><div align="center">'.$titulo['info']['parcela'].'</div></td>'.$nl;
					$ret .= '<td><div align="center">'.$titulo['info']['vencimento'].'</div></td>'.$nl;
					$ret .= '<td><div align="right">'.$titulo['info']['valor'].'</div></td>'.$nl;
					$ret .= '<td><div align="center">'.$titulo['info']['dias'].'</div></td>'.$nl;
					$ret .= '</tr>'.$nl;
				}
			}
			
			$ret .= '</tbody>'.$nl;
			$ret .= '</table>'.$nl;
		}
		
		return $ret;
	}
	
	private function ajustaLista($lista){
		$lista = str_replace(';', ',', $lista);
		$lista = str_replace('|', ',', $lista);
		$lista = str_replace('.', ',', $lista);
		$lista = str_replace(':', ',', $lista);
		
		return $lista;
	}
	
	private function separaTitulos($dados){
		$ret = [];
		if(count($dados) == 0){
			$titulos = getParam($_POST,'enviar');
			if(is_array($titulos) && count($titulos) > 0){
				foreach ($titulos as $tit => $cliente){
					$titulo = explode('_', $tit);
					
					$temp = [];
					$temp['duplicata'] = $titulo[0];
					$temp['parcela'] = $titulo[1];
					$temp['info'] = $this->getInfoTitulo($titulo[0], $titulo[1], $cliente);
					
					$carteira = 'N';
					if($temp['info']['codcob'] == '1341' || $temp['info']['codcob'] == '1001'){
						$carteira = 'P';
					}
					
					$ret[$cliente][$carteira][] = $temp;
				}
			}
		}else{
			foreach ($dados as $dado){
				$carteira = 'N';
				if($dado['codcob'] == '1341' || $dado['codcob'] == '1001'){
					$carteira = 'P';
				}
				$cliente = $dado['codcli'];
				$temp = [];
				$temp['duplicata'] = $dado['duplicata'];
				$temp['parcela'] = $dado['parc'];
				$temp['info'] = $this->getInfoTitulo($temp['duplicata'], $temp['parcela'], $cliente);
				
				$ret[$cliente][$carteira][] = $temp;
			}
		}
//print_r($ret);die();
		return $ret;
	}
	
	private function adicionaParametros($parametros){
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'CLIENTE_FORA';
		$param['tipo'] = 'TA';
		$param['linhas'] = 3;
		$param['config'] = '';
		$param['descricao'] = 'Clientes que não devem receber carta de cobrança';
		$param['$valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);

		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'REDE_FORA';
		$param['tipo'] = 'TA';
		$param['linhas'] = 3;
		$param['config'] = '';
		$param['descricao'] = 'Rede de clientes que não devem receber carta de cobrança';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TITULO_EMAIL';
		$param['tipo'] = 'T';
		$param['config'] = '';
		$param['descricao'] = 'Título do email a ser enviado aos clientes';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'EMAIL_RESUMO';
		$param['tipo'] = 'TA';
		$param['linhas'] = 3;
		$param['config'] = '';
		$param['descricao'] = 'Emails a enviar resumo e/ou testes';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TESTE';
		$param['tipo'] = 'T';
		$param['config'] = '';
		$param['descricao'] = 'Indica se é teste (se sim não envia email para os clientes)';
		$param['valor'] = 'S';
		$param['opcoes'] = 'S=Sim;N=Não';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'SELECIONADOS';
		$param['tipo'] = 'T';
		$param['config'] = '';
		$param['descricao'] = 'Indica se por padrão deve trazer os itens selecionados ou não';
		$param['valor'] = 'N';
		$param['opcoes'] = 'S=Selecionar;N=Não Selecionar';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_4';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email carta 4 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_7';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email carta 7 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_30';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email carta 30 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'EMAIL';
		$param['tipo'] = 'T';
		$param['config'] = '';
		$param['descricao'] = 'Email utilizado para enviaras correspondências';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'EMISSOR_EMAIL';
		$param['tipo'] = 'T';
		$param['config'] = '';
		$param['descricao'] = 'Nome do Emissor do Email';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_PEFIN_04';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email PEFIN 4 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_PEFIN_07';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email PEFIN 7 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_PEFIN_12';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email PEFIN 12 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_PEFIN_17';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email PEFIN 17 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
		
		$param = [];
		$param['programa'] = $parametros['programa'];
		$param['parametro'] = 'TEMPLATE_PEFIN_30';
		$param['tipo'] = 'ED';
		$param['config'] = '';
		$param['descricao'] = 'Email PEFIN 30 dias';
		$param['valor'] = '';
		
		$this->_rotina->adicionaSys020Parametro($param);
	}
	
	private function geraBoleto($idTitulo){
		/*/

	tabela de bancos - PCBANCO
	
	
	/*/
	}
}