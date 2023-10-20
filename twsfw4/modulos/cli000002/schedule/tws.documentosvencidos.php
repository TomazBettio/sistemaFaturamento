<?php
/*
* Data Criação: 19/04/2016 - 12:56:15
* Autor: Thiel
*
* Arquivo: class.ora_documentosvencidos.inc.php
* 
* Envia email para clientes com documentação vencida
*  
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class documentosvencidos{
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';

	// Classe relatorio
	var $_relatorio;
	
	// Dados
	var $_dados;
	
	//Termos do email
	var $_termos;
	
	private $_teste;
	private $_emailSuper;
	private $_emailERC;
	private $_emailCliente;
	
	//Indica se filtra clientes bloqueados
	private $_filtraBloqueados;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_filtraBloqueados = true;
		
		$this->_teste = false;
		$this->_emailSuper = false;
		$this->_emailERC = false;
		$this->_emailCliente = true;
		
		$this->_termos = $this->termos();

		$this->_programa = '000002.documentacao';
		
		$param = [];
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		
		$this->_relatorio->addColuna(array('campo' => 'super'	, 'etiqueta' => 'Regiao'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'erc'		, 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cli'		, 'etiqueta' => 'Cod.'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cnpj'	, 'etiqueta' => 'CNPJ'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'doc'		, 'etiqueta' => 'Documento'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'nr'		, 'etiqueta' => 'Doc.Número'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'venc'	, 'etiqueta' => 'Vencimento'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'dias'	, 'etiqueta' => 'Dias'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'obs'		, 'etiqueta' => 'Observacao'	, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'email'	, 'etiqueta' => 'email'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}
			
	function index(){
		
	}
	
	function schedule($param){
		$emails= str_replace(',', ';', $param);
		
		$contadorCli = 0;
		
		$this->getDados();
		
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true,'Vencimento_Documentacao_'.date('d.m.Y'));
		$titulo = 'Vencimento de Documentacao Sanitaria';
		
		
		$dadosGeral = array();
		
		foreach ($this->_dados as $super => $dadosS){
			$dadosSuper = array();
			foreach ($dadosS as $erc => $dadosE){
				$dadosERC = array();
				foreach ($dadosE as $cli => $dadosC){
					$dadosCliente = array();
					$docs = array();
					$d = array();
					foreach ($dadosC as $doc => $dado){
						$d['super'] 	= $super.' '.$dado['super'];
						$d['erc'] 		= $erc.' '.$dado['erc'];
						$d['cli'] 		= $cli;
						$d['cliente'] 	= $dado['cliente'];
						$d['cnpj'] 		= $dado['cnpj'];
						$d['doc'] 		= $dado['documento'];
						$d['nr'] 		= $dado['nr'];
						$d['venc'] 		= $dado['validade'];
						if($dado['dias'] >= 0){
							$d['dias'] = 'Vencido';
						}else{
							$d['dias'] = $dado['dias'] * -1;
						}
						$d['obs'] 		= '';
						if(trim($dado['email']) == ''){
							$d['obs'] = "Cliente sem email";
						}
						$d['email'] = $dado['email'];
						
						$docs[] = array('cliente' => $d['cliente'], 'cnpj' => $d['cnpj'], 'doc' => $dado['documento'], 'venc' => $dado['validade'], 'dias' => $d['dias']);
					
						$dadosGeral[] = $d;
						$dadosSuper[] = $d;
						$dadosERC[] = $d;
						$dadosCliente[] = $d;
					}
					
					// Envia email para o cliente
					if(count($dadosCliente) > 0 && trim($dado['email']) != '' && trim($dado['email']) != 'clientesememailnfe@zipmail.com.br'){
					    if(!$this->_teste){
					        $this->email($docs,trim($dado['email']));
					        log::gravaLog("documentacao", "Enviado email cliente : $cli -".$dado['cliente'].'-'.trim($dado['email']));
					    }elseif($this->_emailCliente){
					        if($contadorCli < 10){
					            $contadorCli++;
					            echo "<br>ID: $contadorCli Enviado email para ".$dado['cliente'];
					            $this->email($docs,'suporte@thielws.com.br');
					        }else{
					        	break 2;
					        }
					    }
					}
				}
				
				//Envia email para o ERC
				if(count($dadosERC) > 0){
					$this->_relatorio->setEnviaTabelaEmail(false);
					$this->_relatorio->setDados($dadosERC);
					$email = getEmailERC($erc);
					if(!$this->_teste){
					    $this->_relatorio->enviaEmail($email,$titulo);
					    log::gravaLog("documentacao", "Enviado email para: $erc ".$email);
					}
					elseif($this->_emailERC){
				        $this->_relatorio->enviaEmail('suporte@thielws.com.br',$email.' '.$titulo." - TESTE");
					}
				}
			}
			
			//Envia email para o Coordenador
			if(count($dadosERC) > 0){
				$this->_relatorio->setDados($dadosSuper);
				$this->_relatorio->setEnviaTabelaEmail(false);
				$email = getEmailCoordenador($super);
				if(!$this->_teste){
				    $this->_relatorio->enviaEmail($email,$titulo);
				    log::gravaLog("documentacao", "Enviado email para: $super ".$email);
				}
				elseif($this->_emailSuper){
					$this->_relatorio->enviaEmail('suporte@thielws.com.br',$email.' '.$titulo." - SUPER");
				}

			}
		}
		
		if(count($dadosGeral) > 0){
			//Envia email geral
			$this->_relatorio->setDados($dadosGeral);
			$this->_relatorio->setEnviaTabelaEmail(false);
			if(!$this->_teste){
			    $this->_relatorio->enviaEmail($emails,$titulo);
			    log::gravaLog("documentacao", "Enviado email para: ".$emails);
			}
			else{
			    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo." - GERAL");
			}

		}
	}
	
	function getDados(){
		$ret = array();
		$whereBloqueio = '';
		if($this->_filtraBloqueados){
			$whereBloqueio = " AND NVL(PCCLIENT.BLOQUEIO,'N') <> 'S'";
		}
		$sql = "
				SELECT 
				       PCSUPERV.CODSUPERVISOR,
				       PCSUPERV.NOME NOMESUPER,
				       PCUSUARI.CODUSUR,
				       PCUSUARI.NOME NOMEERC,
				       PCCLIENT.CLIENTE,
					   PCCLIENT.EMAIL,
				       PCCLICONTROLEVENDA.CODCLI, 
				       PCCLICONTROLEVENDA.CODTIPOCONTROLEVENDA, 
				       PCTIPOCONTROLEVENDA.DESCRICAO, 
				       PCCLICONTROLEVENDA.DTVALIDADE,
				       TRUNC(SYSDATE) - TRUNC(PCCLICONTROLEVENDA.DTVALIDADE) DIAS,
						PCCLIENT.CGCENT,
						PCCLICONTROLEVENDA.NUMDOC
				FROM PCCLICONTROLEVENDA,
				     PCTIPOCONTROLEVENDA,
				     PCCLIENT,
				     PCUSUARI,
				     PCSUPERV
				WHERE PCCLICONTROLEVENDA.CODTIPOCONTROLEVENDA = PCTIPOCONTROLEVENDA.CODTIPOCONTROLEVENDA (+)
				    AND PCCLICONTROLEVENDA.CODCLI = PCCLIENT.CODCLI (+)
				    AND PCCLIENT.CODUSUR1 = PCUSUARI.CODUSUR (+)
					AND PCCLIENT.CODATV1 NOT IN (5)
					AND PCCLIENT.CODUSUR1 <> 11 
				    AND PCUSUARI.CODSUPERVISOR = PCSUPERV.CODSUPERVISOR (+)
				    AND PCCLICONTROLEVENDA.CODCLI IN (SELECT DISTINCT CODCLI FROM PCNFSAID WHERE DTSAIDA + 365 >= SYSDATE)
				    AND DTVALIDADE <= SYSDATE + 30
				    AND PCCLICONTROLEVENDA.CODTIPOCONTROLEVENDA IN (1,2,3,6,7)
					AND PCCLIENT.DTEXCLUSAO IS NULL
					$whereBloqueio
--AND PCCLICONTROLEVENDA.CODCLI IN (15729,6987,17547, 3675, 11413) 
				ORDER BY PCSUPERV.CODSUPERVISOR, PCCLIENT.CODUSUR1, PCCLIENT.CLIENTE
				";
//echo "$sql <br>";
		$rows = query4($sql);
		if(count($rows) >0){
			foreach ($rows as $row){
				$super 		= $row['CODSUPERVISOR'];
				$erc 		= $row['CODUSUR'];
				$cliente 	= $row['CODCLI'];
				$doc 		= $row['CODTIPOCONTROLEVENDA'];
				
				$this->_dados[$super][$erc][$cliente][$doc]['super'] 	 = $row['NOMESUPER'];
				$this->_dados[$super][$erc][$cliente][$doc]['erc'] 		 = $row['NOMEERC'];
				$this->_dados[$super][$erc][$cliente][$doc]['cliente'] 	 = $row['CLIENTE'];
				$this->_dados[$super][$erc][$cliente][$doc]['email'] 	 = $row['EMAIL'];
				$this->_dados[$super][$erc][$cliente][$doc]['documento'] = $row['DESCRICAO'];
				$this->_dados[$super][$erc][$cliente][$doc]['nr'] 		 = $row['NUMDOC'];
				$this->_dados[$super][$erc][$cliente][$doc]['validade']  = datas::dataMS2D($row['DTVALIDADE']);
				$this->_dados[$super][$erc][$cliente][$doc]['dias'] 	 = $row['DIAS'];
				$this->_dados[$super][$erc][$cliente][$doc]['cnpj'] 	 = $row['CGCENT'];
			}
		}
		
		return $ret;
	}
	
	function email($docs,$email){
		global $config;
//print_r($docs);
		$dias = 7;
		$cnpj = $docs[0]['cnpj'];
		$cnpj = str_replace('.', '', $cnpj);
		$cnpj = str_replace('/', '', $cnpj);
		$cnpj = str_replace('-', '', $cnpj);
		
		$corpo = '';
		
		$pos1 = $cnpj;			// CNPJ do cliente sem máscara
		$pos2 = md5($cnpj);		// Nesta primeira versão mandar o CNPJ
		$pos3 = date('dmY');	// Data da geração do link, no formato indicado (DDMMAAAA)
		$pos4 = date('Hi');		// Hora de geração do link, no formato indicado (HHMM)
		$pos5 = $dias;			// Número de dias em que este link deve ficar válido

		$auxDigits  = CalcDigitMod9($pos1);
		$auxDigits .= CalcDigitMod9($pos3);
		$auxDigits .= CalcDigitMod9($pos4);
		$auxDigits .= CalcDigitMod9($pos5);
		$auxDigits .= CalcDigitMod9($auxDigits);
		$pos6 = $auxDigits;
		
		$stringLink = $pos1.'|'.$pos2.'|'.$pos3.'|'.$pos4.'|'.$pos5.'|'.$pos6;

		$corpo .= ajustaCaractHTML("Prezado Cliente,");
		$corpo .= ajustaCaractHTML('<p>Disponibilizamos, atrav&eacute;s do link abaixo, uma ferramenta que possibilita a atualiza&ccedil;&atilde;o de sua documenta&ccedil;&atilde;o sanit&aacute;ria.</p>');
		$corpo .= ajustaCaractHTML('<p>Este link estar&aacute; dispon&iacute;vel por 7 dias onde devem ser anexados seus documentos. Ser&atilde;o preferencialmente aceitos arquivos em .PDF e/ou imagens (.JPG,.JPEG, .GIF, .PNG, .TIFF ou .BMP).</p>');
		$corpo .= ajustaCaractHTML('<p><b>Acesse a ferramenta clicando no link em azul:</b> ').'<a href="https://athenas.gauchafarma.com/easy/login.php?lc='.$stringLink.'">[ easy - gauchafarma ]</a></p>';;
		$corpo .= ajustaCaractHTML('<p>Abaixo sua documenta&ccedil;&atilde;o que est&aacute; pendente:</p>');
		
		$corpo .= $this->montaTabela($docs);
		
		//$corpo .= ajustaCaractHTML('<p>Para evitar o bloqueio de seus pedidos solicitamos a gentileza de enviar a documenta&ccedil;&atilde;o atualizada para o e-mail <a href="mailto:credito@gauchafarma.com">credito@gauchafarma.com</a> ou <a href="mailto:farmaceutica@gauchafarma.com">farmaceutica@gauchafarma.com</a>, ou entrar em contato pelo telefone (51) 3382-2015</p>');
		//$corpo .= ajustaCaractHTML('<p>Para evitar o bloqueio de seus pedidos solicitamos a gentileza de enviar a documenta&ccedil;&atilde;o assim que possível. Estamos a disposição no e-mail <a href="mailto:credito@gauchafarma.com">credito@gauchafarma.com</a> ou <a href="mailto:farmaceutica@gauchafarma.com">farmaceutica@gauchafarma.com</a>, ou pelo telefone (51) 3382-2015</p>');
		$corpo .= ajustaCaractHTML('<p>Atenciosamente,</p>');
		$corpo .= "<img alt='Gauchafarma' src='cid:embedded1'>";
		$corpo .= '<br>';
		$corpo .= $this->_termos;

		$titulo = "Gauchafarma Medicamentos - Cliente com Documentacao a Vencer";
		
		$emailsender = array();
		$emailsender[0] = 'credito@gauchafarma.com';
		$emailsender[1] = 'Gauchafarma - Credito';
		
		enviaEmailAntigo($email, $titulo, $corpo,array(), $emailsender, array(array('caminho' => $config["baseS3"].'imagens/','nome'=>'assinatura.png')));
		//enviaEmail($email, $titulo, $corpo,array(), array('intranet@gauchafarma.com','Intranet - Gauchafarma'));
//		echo $corpo;

	}
	function montaTabela($docs){
		global $nl;
		$ret = '';
		$tam = 800;
		
		$ret .= '<table width="'.$tam.'" border="0" align="center" cellpadding="5" cellspacing="0" rules="all" style="border: 1px solid #063; border-collapse: collapse;">'.$nl;
		$ret .= '	<tr style="font-family: Verdana, Geneva, sans-serif;	font-size: 14px; font-weight: bold;	color: #FFF; background-color: #063; text-align: center;	border: 1px solid #063;">'.$nl;
		$ret .= '		<td scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">'.ajustaCaractHTML("Razão Social").'</td>'.$nl;
		$ret .= '		<td scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">CNPJ</td>'.$nl;
		$ret .= '		<td scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Documento</td>'.$nl;
		$ret .= '		<td scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Vencimento</td>'.$nl;
		$ret .= '		<td scope="col" style="border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-color: #FFF; border-right-color: #FFF; border-bottom-color: #FFF; border-left-color: #063;">Dias Restantes</td>'.$nl;
		$ret .= '	</tr>'.$nl;
		
		foreach ($docs as $doc){
			$ret .= '	<tr>'.$nl;
			$ret .= '		<td style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;">'.$doc['cliente'].'</td>'.$nl;
			$ret .= '		<td style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;">'.$doc['cnpj'].'</td>'.$nl;
			$ret .= '		<td style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: left; border: 1px solid #063;">'.$doc['doc'].'</td>'.$nl;
			$ret .= '		<td style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: center; border: 1px solid #063;">'.$doc['venc'].'</td>'.$nl;
			$ret .= '		<td style="font-family: Verdana, Geneva, sans-serif; font-size: 12px; text-align: center; border: 1px solid #063;">'.$doc['dias'].'</td>'.$nl;
			$ret .= '	</tr>'.$nl;
		}
		
		$ret .= '</table>'.$nl;
		
		return $ret;
	}

	function termos(){
		$texto = ''; 
		$texto .= "Esta mensagem e seus anexos são destinados exclusivamente ao(s) destinatário(s) identificado(s) acima e contêm "; 
		$texto .= "informações confidenciais ou privilegiadas. Se você não é o destinatário destes materiais, não está autorizado a utilizá-los "; 
		$texto .= "para nenhum fim. Solicitamos que você apague a mensagem e seus anexos e avise imediatamente o remetente. O "; 
		$texto .= "conteúdo desta mensagem e de seus anexos não representa necessariamente a opinião e a intenção da empresa, não "; 
		$texto .= "implicando em qualquer obrigação ou responsabilidade adicionais";
				
		$ret = '<br><hr />';
		$ret .= '<table width="800" border="0" align="center" cellpadding="5" cellspacing="0">';
		$ret .= '  <tr>';
		$ret .= '    <td align="center" style="font-family: Verdana, Geneva, sans-serif; font-size: 9px; color: #999;">'.ajustaCaractHTML("ATENÇÃO: E-MAIL GERADO AUTOMATICAMENTE, NÃO O RESPONDA!").'</td>';
		$ret .= '  </tr>';
		$ret .= '  <tr>';
		$ret .= '    <td style="font-family: Verdana, Geneva, sans-serif; font-size: 9px; color: #999;">'.ajustaCaractHTML($texto).'</td>';
		$ret .= '  </tr>';
		$ret .= '  <tr>';
		$ret .= '    <td style="font-family: Verdana, Geneva, sans-serif; font-size: 9px; color: #999;">Todos os nossos e-mails em nenhuma hip&oacute;tese trazem arquivos anexos execut&aacute;veis ou links para download. Pedimos que desconsidere qualquer e-mail enviado com estas caracter&iacute;sticas.</td>';
		$ret .= '  </tr>';
		$ret .= '</table>';
	
		return $ret;
	}
}