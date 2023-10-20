<?php
/*
 * Data Criacao: 07/03/2018
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Vendas dos operadores do televendas
 * 
 * OBS: querys desenvolvidas pelo Neto
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);

include_once($config['include'].'dompdf/autoload.inc.php'); 
use Dompdf\Options;
use Dompdf\Dompdf;

class devolucao{

	
	var $funcoes_publicas = array(
			'index' 		=> true,
	);

	var $_pdf = '';
	var $_style = '';
	
	//Arquivo PDF do SAC
	var $_arquivo;
	
	//Email do SAC
	var $_emailSac;
	
	//Indica se e teste
	private $_teste;
	
	function __construct(){
		set_time_limit(0);
		$this->style();
		
		$this->_teste = false;
		
		$this->_emailSac = 'sac@gauchafarma.com';
	}
	
	function index(){
		$ret = '';
		
		if(isset($_POST['formSAC']) && $_POST['formSAC'] != ''){
			$sac = $_POST['formSAC'];
			$dados = $this->getSac($sac);
			if(count($dados) > 0){
				$ret .= $this->formEmail($sac, $dados);
				$ret .= '<br>';
				$ret .= $this->layout($dados);
				$this->exportPdf($sac);
			}else{
				addPortalMensagem( 'SAC '.$sac.' não encontrado, favor conferir !','error');
				$ret = addBoxInfo("SAC - Devoluções", $ret);
			}
		}elseif(isset($_GET['sac'])){
			$dadosForm = isset($_POST['formEmail']) ? $_POST['formEmail'] : [];
			if(count($dadosForm) == 0){
				$ret .= $this->formSAC();
			}
			$sac = $dadosForm['sac'];
//print_r($dadosForm);
			$erro = false;
			if(empty(trim($dadosForm['cliente']))){
				$erro = true;
				addPortalMensagem('O campo <b>Email Cliente</b> não deve ficar em branco, favor verificar!','error');
			}
			if(empty(trim($dadosForm['trasp']))){
				$erro = true;
				addPortalMensagem('O campo <b>Email Transportadora</b> não deve ficar em branco, favor verificar!','error');
			}
			if(empty(trim($dadosForm['sacEmail']))){
				$erro = true;
				addPortalMensagem('O campo <b>Email SAC</b> não deve ficar em branco, favor verificar!','error');
			}
			if($dadosForm['motivo'] == ''){
				$erro = true;
				addPortalMensagem('O campo <b>MOTIVO</b> não deve ficar em branco, favor verificar!','error');
			}
			if($erro){
				$dados = $this->getSac($sac);
				$ret .= $this->formEmail($sac, $dados);
				$ret .= '<br>';
				$ret .= $this->layout($dados);
			}else{
				//Recupera os dados do sac
				$dados = $this->getSac($sac);
				//Grava novo log
				$this->gravaLogSac($dados, $dadosForm);
				
				$dados['motivo'] = $dadosForm['motivo'];
				$dados['tipo_dev']= $dadosForm['tipo'];
				
				$this->_pdf = $this->layout($dados, true);
				$this->exportPdf($sac);
				$this->enviaEmail($dados, $dadosForm);
				
				addPortalMensagem('SAC enviado!','info');
				
				$ret .= $this->formSAC();
				$ret .= '<br><br>';
				$ret .= $this->_pdf;
			}
		//	print_r($dados);
		//	print_r($dadosForm);
		}else{
			$ret .= $this->formSAC();
		}
		
		$param = [];
		$param['titulo'] = "SAC - Devoluções";
		$param['conteudo'] = $ret;
		$ret = addCard($param);
		
		return $ret;
	}
	
	function schedule($param){

	}
	
	private function formSAC(){
		$ret = '';
		$form = new form01();
//$form->setTipoForm(4);
		
		$url = 'index.php?menu=gfsac.devolucao.index';
		$form->addCampo(array('id' => '', 'campo' => 'formSAC'	, 'etiqueta' => 'SAC'	, 'tipo' => 'T','tamanho' => '80'	, 'linhas' => '' , 'valor' => ''	, 'lista' => ''	, 'validacao' => '', 'obrigatorio' => true));
		
		$form->setEnvio($url, 'sacForm', 'sacForm');
		
		$ret .= addDivColuna(4, ''); 
		$ret .= addDivColuna(4, $form);
		$ret .= addDivColuna(4, '');
		
		$ret = addRow($ret);
		
		return $ret;
	}
	
	private function formEmail($sac, $dados){
		$ret = '';
		$form = new form01();
//$form->setTipoForm(4);
		
		$tipos = $this->getTiposDevolucao();
		
		$url = 'index.php?menu=gfsac.devolucao.index&sac='.$sac;
		$form->addHidden('formEmail[sac]', $sac);
		$form->addCampo(array('id' => '', 'campo' => 'formEmail[sac2]'		, 'etiqueta' => 'SAC'					, 'tipo' => 'I','tamanho' => '80'	, 'linhas' => '' , 'valor' => $sac					, 'lista' => ''		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => '', 'campo' => 'formEmail[tipo]'		, 'etiqueta' => 'Tipo Devolução'		, 'tipo' => 'A','tamanho' => '80'	, 'linhas' => '' , 'valor' => 'P'					, 'lista' => $tipos	, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => '', 'campo' => 'formEmail[motivo]'	, 'etiqueta' => 'Motivo'				, 'tipo' => 'T','tamanho' => '80'	, 'linhas' => '' , 'valor' => $dados['motivo']		, 'lista' => ''		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => '', 'campo' => 'formEmail[cliente]'	, 'etiqueta' => 'Email Cliente'			, 'tipo' => 'T','tamanho' => '80'	, 'linhas' => '' , 'valor' => $dados['mail_cli']	, 'lista' => ''		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => '', 'campo' => 'formEmail[trasp]'		, 'etiqueta' => 'Email Transportadora'	, 'tipo' => 'T','tamanho' => '80'	, 'linhas' => '' , 'valor' => $dados['mail_transp']	, 'lista' => ''		, 'validacao' => '', 'obrigatorio' => true));
		$form->addCampo(array('id' => '', 'campo' => 'formEmail[sacEmail]'	, 'etiqueta' => 'Email SAC'				, 'tipo' => 'T','tamanho' => '80'	, 'linhas' => '' , 'valor' => $this->_emailSac		, 'lista' => ''		, 'validacao' => '', 'obrigatorio' => true));
		
		
		$form->setEnvio($url, 'sacForm', 'sacForm');
		
		$ret .= addDivColuna(3, '');
		$ret .= addDivColuna(6, $form);
		$ret .= addDivColuna(3, '');
		
		$ret = addRow($ret);
		
		return $ret;
	}
	
	function getSac($sac){
		$ret = [];
		$sql = "       
				SELECT 
				    PCMANIF.NUMMANIF,
				    PCMANIF.NUMNOTA,
				    PCMANIF.CODCLI,
				    PCCLIENT.CLIENTE,
				    PCCLIENT.EMAIL,
				    PCCLIENT.ENDERCOM,
				    PCCLIENT.NUMEROCOM,
				    PCCLIENT.BAIRROCOM,
				    PCCLIENT.MUNICCOM,
				    PCMANASSUNTO.ASSUNTO,
				    PCMANIF.CODTRANSPORTADOR,
				    PCFORNEC.FORNECEDOR,
				    PCFORNEC.EMAIL EMAILTRANSP,
					PCCLIENT.ESTCOB
				FROM 
				    PCMANIF,
				    PCCLIENT,
				    PCMANASSUNTO,
				    PCFORNEC
				WHERE
				    PCMANIF.CODCLI = PCCLIENT.CODCLI (+)
				    AND PCMANIF.CODASSUNTO = PCMANASSUNTO.CODASSUNTO (+)
				    AND PCMANIF.CODTRANSPORTADOR = PCFORNEC.CODFORNEC (+)
				    AND PCMANIF.NUMMANIF = $sac
			";
		$results = query4($sql);
		if(is_array($results) && count($results) > 0){
			foreach($results as $key => $value){
				$ret['codSac'] 					= $value['NUMMANIF'];
				$ret['numNota']					= $value['NUMNOTA'];
				$ret['codCli'] 					= $value['CODCLI'];
				$ret['cliente'] 				= $value['CLIENTE'];
				$ret['mail_cli']				= $value['EMAIL'];
				$ret['endereco']				= $value['ENDERCOM'];
				$ret['num']						= $value['NUMEROCOM'];
				$ret['bairro']					= $value['BAIRROCOM'];
				$ret['mun']						= $value['MUNICCOM'];
				$ret['motivo']					= $value['ASSUNTO'];
				$ret['tipo_dev']				= 'INDEFINIDO';
				$ret['transp']					= $value['FORNECEDOR'];
				$ret['mail_transp']				= $value['EMAILTRANSP'];
				$ret['uf']						= $value['ESTCOB'];
			}
			
			//Pega produtos
			$sql = "
					SELECT
					    PCMANIFPROD.CODPROD,
					    PCPRODUT.DESCRICAO,
					    PCMANIFPROD.QTRECLAMADA
					FROM 
					    PCMANIFPROD,
					    PCPRODUT
					WHERE 
					    PCMANIFPROD.CODPROD = PCPRODUT.CODPROD
					    AND NUMMANIF = $sac
					";
			$rows = query4($sql);
			if(count($rows) > 0){
				foreach ($rows as $row){
					$temp = [];
					$temp['cod']	= $row['CODPROD'];
					$temp['prod']	= $row['DESCRICAO'];
					$temp['qnt']	= $row['QTRECLAMADA'];
					
					$ret['prods'][] = $temp;
				}
			}
			
			// Pega dados já alterados
			$sql = "SELECT * FROM gf_sac WHERE cod_sac =  ".$sac." ORDER BY date DESC LIMIT 1";
			unset($results);
			$results = query($sql);
			if(is_array($results) && count($results) > 0){
				foreach($results as $key => $value){
					$ret['motivo']					= $value['motivo'];
					$ret['tipo_dev']				= $value['tipo_dev'];
					
				}
			}
		}
		return $ret;
	}
	
	private function getTiposDevolucao(){
		$ret = [];
		
		$ret[0][0] = 'P';
		$ret[0][1] = 'Devolução Parcial';

		$ret[1][0] = 'I';
		$ret[1][1] = 'Devolução Integral';
		
		$ret[2][0] = 'T';
		$ret[2][1] = 'Remessa para Conserto ou Troca';
		
		return $ret;		
	}
	
	function getMensagens($dados){
		$msg = [];
		//$msg['titulo'] = utf8_decode('Gauchafarma - Devolução '.$dados['codSac']);
		$msg['titulo'] = 'Gauchafarma - Devolução '.$dados['codSac'];
		
		$msg['mensagem'] = '';
		$msg['mensagem'] .= '<br>Prezado Cliente,';
		$msg['mensagem'] .= '<p>Segue as orienta&ccedil;&otilde;es para o procedimento de devolu&ccedil;&otilde;es junto &agrave; Gauchafarma.</p>';
		$msg['mensagem'] .= '<p>Emiss&atilde;o da Nota Fiscal de Devolu&ccedil;&atilde;o:</p>';
		$msg['mensagem'] .= '<ul>';
		$msg['mensagem'] .= '<li>Em anexo exemplo para emiss&atilde;o da Nota Fiscal de DEVOLU&Ccedil;&Atilde;O quando os produtos tiverem ST</li>';
		$msg['mensagem'] .= '</ul>';
		$msg['mensagem'] .= '<p>Embalagem:</p>';
		$msg['mensagem'] .= '<ul>';
		$msg['mensagem'] .= '<li>Mercadorias dever&atilde;o estar acondicionadas em caixas de papel&atilde;o.</li>';
		$msg['mensagem'] .= '<li>N&atilde;o ser&atilde;o aceitas devolu&ccedil;&otilde;es em sacos pl&aacute;sticos ou papel.</li>';
		$msg['mensagem'] .= '<li>Os volumes dever&atilde;o estar devidamente lacrados e Identificados.</li>';
		$msg['mensagem'] .= '</ul>';
		$msg['mensagem'] .= '<p><strong>Transportadora:</strong></p>';
		$msg['mensagem'] .= '<ul>';
		$msg['mensagem'] .= '<li>A coleta pode levar de 5 a 15 dias dependendo da frequ&ecirc;ncia de entrega na regi&atilde;o da coleta.</li>';
		$msg['mensagem'] .= '<li>A transportadora far&aacute; duas tentativas de coleta, no caso de coleta frustrada o protocolo ser&aacute; encerrado.</li>';
		$msg['mensagem'] .= '</ul>';
		$msg['mensagem'] .= '<p>Por gentileza imprimir em duas vias a autoriza&ccedil;&atilde;o de coleta, uma via &eacute; para farm&aacute;cia e a outra via entregar &agrave; Transportadora devidamente carimbada e assinada.</p>';
		$msg['mensagem'] .= '<p><strong><font color="#FF0000" size="4">OBS: Se a mercadoria n&atilde;o for enviada ap&oacute;s 15 dias da abertura da solicita&ccedil;&atilde;o, o SAC ser&aacute; encerrado automaticamente. E a devolu&ccedil;&atilde;o n&atilde;o ser&aacute; mais aceita.</font></strong></p>';
		$msg['mensagem'] .= '<p>Atenciosamente,</p>';
		$msg['mensagem'] .= "<img alt='Gauchafarma' src='cid:embedded1'>";
		return $msg;
	}
	
	function enviaEmail($dados, $dadosForm){
		global $config;
		$anexo = [];
		
		$anexo[] = $this->_arquivo;
        
		/*
		 * modificado em 21/10/22 por Emanuel a pedido de Gustavo Lipert
		 * foi solicitado que os anexos comentados fossem removidos
		if($dados['uf'] == 'RS'){
			$anexo[] = $config['modulos'].'cliente000002/gfsac/arquivos/DEVOLUCAO_DE_CLIENTES_RS_COM_ST.pdf';
		}
		if($dados['uf'] == 'SC'){
			$anexo[] = $config['modulos'].'cliente000002/gfsac/arquivos/DEVOLUCAO_DE_CLIENTES_SC_COM_ST.pdf';
		}
        */
		$msg = $this->getMensagens($dados);
		
		$emailCli = explode(';', $dadosForm['cliente']);
		$emailTra = explode(';', $dadosForm['trasp']);
		$emailSac = explode(';', $dadosForm['sacEmail']);
		
		$email = array_merge($emailCli, $emailTra, $emailSac);
		$emails = implode(';', $email);
		
		$emailsender = [];
		$emailsender[0] = 'sac@gauchafarma.com';
		$emailsender[1] = 'Gauchafarma - SAC';
		
		//enviaEmail($email, $titulo, $corpo,[], [],array(array('caminho' => $config["baseFW"].'imagens/','nome'=>'assinatura.png')),$emailsender);
		
		
		$caminho = $config['modulos'].'cliente000002/gfsac/arquivos/';
		$nome = 'assinatura.png';
		$assinatura = array(array('caminho' => $caminho,'nome' => $nome));
		
		$param = [];
		$param['mensagem'] 		= $msg['mensagem'];
		$param['assunto']		= $msg['titulo'];
		$param['anexos'] 		= $anexo;
		$param['emailsender'] 	= $emailsender;
		$param['embeddedImage'] = $assinatura;
		//$param['responderPara'] = '';
		$param['destinatario']  = $emails;
		
		if($this->_teste){
			$param['destinatario']  = 'suporte@thielws.com.br';
		}else{
//			$param['destinatario']  = $emails;
			$param['bcc'] 			= 'suporte@thielws.com.br';
		}
		
		if(!empty($emails)){
			enviaEmail($param);
			log::gravaLog('SAC_devolucao', 'SAC '.$dados['codSac'].' Email enviado: '.$param['destinatario']);
		}else{
			log::gravaLog('SAC_sem_email', 'email cliente - '.$msg['mensagem']);
			addPortalMensagem( 'Email não enviado pois não foi indicado nenhum, favor conferir!','erro');
			return false;
		}
		
		return true;
	}
	
	function layout($datas, $email = false){
		global $config, $nl;
		
		$tipo = '';
		$tiposDefinidos = $this->getTiposDevolucao();
		switch ($datas['tipo_dev']) {
			case 'P':
				$tipo = $tiposDefinidos[0][1];
				break;
			case 'I':
				$tipo = $tiposDefinidos[1][1];
				break;
			case 'T':
				$tipo = $tiposDefinidos[2][1];
				break;
		}
		$ret = '';
//		$ret =  "
//			<div class='noPrint'>
//				<div class='box-body' align='right'>
//					<a role='button' onclick='jsToPdf()' href='index.php?menu=gfsac.devolucao.index&export=pdf&sac=".$datas['codSac']."' style='background-color:#f2f2f2;'><i class='fa fa-file-pdf-o' style='color: red;'></i></a>
//				</div>
//			</div>
//		";
		$ret .= '
		<table width="100%">
			<tr>
				<td width="80%" colspan="4">
					<img src="'.$config['imagens'].'logo.png">
				</td>
				<td rowspan="3" width="20%" align="center" style="font-size:10">
					<div class="borda"><b>Caro tranportador, <br>favor coletar <br>devolução no cliente <br>abaixo</b></div>
				</td>
			</tr>
			<tr>
				<td align="center">
					<b>Protocolo</b>
				</td>
				<td colspan="3">
					<div align="center"><strong><font size="4">Autoriza&ccedil;&atilde;o de Coleta</font></strong></div></td>
				</td>
			</tr>
			<tr> 
				<td align="center" style="font-size:9">
					<b>'.$datas['codSac'].'</b>
				</td>
				<td colspan="3">
				</td>
			</tr>
			<tr>
				<th>Cod. Ciente</th>
				<th>Cliente</th>
				<th>Rua / Nº</th>
				<th>Bairro</th>
				<th>Município</th>
			</tr>
			<tr>
				<td align="center">'.$datas['codCli'].'</td>
				<td>'.$datas['cliente'].'</td>
				<td>'.$datas['endereco'].'/'.$datas['num'].'</td>
				<td align="center">'.$datas['bairro'].'</td>
				<td align="center">'.$datas['mun'].'</td>
			</tr>
			<tr>
				<th>Nota fiscal de origem</th>
				<th align="left" style="text-align:left">Tipo de devolução</th>
				<th colspan="2"></th>
				<th>Nota de Devolução</th>
			</tr>
			<tr>
				<td align="center">'.$datas['numNota'].'</td>
				<td>'.$nl;
		if($email){
			$ret .= $tipo.$nl;
		}
		//$ret .= '<td><input type="text" class="noBorder centralize" id="tipo_dev" name="tipo_dev" value="'.$datas['tipo_dev'].'" placeholder="'.$datas['tipo_dev'].'" readonly="true"></td>'.$nl;
		$ret .= '</td>'.$nl;
		$ret .= '
				<td colspan="3"></td>
			</tr>
		</table>
		<br>';
		if(isset($datas['prods']) && count($datas['prods']) > 0){
			$ret .= '	
			<table width="100%">
				<tr>
					<th>Cod.</th>
					<th>Devolução</th>
					<th>Quantidade</th>
				</tr>';
			foreach($datas['prods'] as $prod){
				$ret.='
				<tr>
					<td align="center">'.$prod['cod'].'</td>
					<td>'.$prod['prod'].'</td>
					<td align="center">'.$prod['qnt'].'</td>
				</tr>';
			}
			$ret.='</table>';
		}
		$ret.='
		<br>
		<table width="100%">
			<tr>
				<th>MOTIVO DA DEVOLUCÃO</th>
			</tr>
			<tr width="100%">
				<td width="100%" align="center">'.$nl;
		if($email){
			$ret .= $datas['motivo'].$nl;
		}
		//$ret .= '<input type="text"  class="width-dynamic proba dva noBorder centralize" name="motivo" id="motivo" value="'.$datas['motivo'].'" width="100%" onblur="jsSetSac()">'.$nl;
		$ret .= '
				</td>
			</tr>
		</table>
		<br>
		<table width="100%">
			<tr>
				<th>TRANSPORTADORA</th>
			</tr>
			<tr width="100%">
				<td width="100%" align="center">
					<input type="text" class="width-dynamic proba dva noBorder centralize" name="tranportadora" id="tranportadora" value="'.$datas['transp'].'"> 
				</td>
			</tr>
		</table>
		<br>
		<table width="100%">
			<tr>
				<td colspan="2" align="center">
					Favor imprimir duas vias e entregar uma delas á Transportadora devidamente assinada e carimbada.
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<br>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					Serão aceitas devoluções somente com esta autorização em anexo á <b><u>NF de devolução</u></b> e com os volumes devidamente identificados, <b><u>mercadorias deverão estar em caixas</u></b>.
				</td>
			</tr>
			<tr>
				<td align="center">
					<br><br><br>
					<hr class="linha"  width="50%">
					Carimbo\CPF e assinatura - Cliente
				</td>
				<td align="center">
					<br><br><br>
					<hr class="linha" width="50%">
					Carimbo\CPF e assinatura - Motorista
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<br>
				</td>
			</tr>
			<tr>
				<td class="obs" colspan="2">
					Dados adicionais Gauchafarma:
				</td>
			</tr
			<tr>
				<td class="obs1" colspan="2">
					Obs.:As devoluções serão aceitas pela Gauchafarma, somente mediante apresentação da cópia desta Autorização.
				</td>
			</tr>
		</table>
		';
		return $ret;
	}
	
	function gravaLogSac($dados, $dadosForm){
		$emails = $dadosForm['cliente'].';'.$dadosForm['trasp'].';'.$dadosForm['sacEmail'];
		$motivo = str_replace("'", '´', $dadosForm['motivo']);
		$tipoDevolucao = $dadosForm['tipo'];
		$produtos = '';
		if(isset($dados['prods']) && is_array($dados['prods']) && count($dados['prods']) > 0){
			foreach ($dados['prods'] as $prod){
				$p[] = $prod['cod'];
			}
			$produtos = implode(',', $p);
		}
		$sql = "
			INSERT INTO gf_sac(operador, date, cod_sac, cod_cli, nf_origem, produtos, tipo_dev, motivo, emails) 
				VALUES (
				'".getUsuario()."',
				CURRENT_TIMESTAMP,
				'".$dados['codSac']."',
				'".$dados['codCli']."',
				'".$dados['numNota']."',
				'$produtos',
				'$tipoDevolucao',
				'$motivo',
				'$emails'
			)
		";
//echo $sql;
		query($sql);
	}
	
	function exportPdf($filename, $pdf = ''){
		$pdf .= '
			<head>
				<style>
				'.$this->_style.'
				</style>
			</head>
		';
		$pdf .= $this->_pdf;
		$options = new Options();
		//$options->set('defaultFont', 'Courier');
		$options->set('isRemoteEnabled', TRUE);
		$options->set('debugKeepTemp', TRUE);
		$options->set('isHtml5ParserEnabled', true);
		
		//$dompdf = new Dompdf(array('enable_remote' => true));
		$dompdf = new Dompdf($options);
		global $config;
		$dompdf->set_base_path($config['temp'].'sac/');
		//$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->loadHtml($pdf);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$this->_arquivo = $config['temp'].'sac/Gauchafarma_sac_'.$filename.'.pdf';
		file_put_contents($this->_arquivo, $dompdf->output()); 
		//$dompdf->stream("intranet.pdf");
		//$dompdf->stream("intranet.pdf",array("Attachment"=>0));

	}
	
	
	function style(){
		$this->_style = "
			table {
				font-family: 'Times New Roman', Times, serif;
				font-size: 9;
				border: 1px solid black;
				background-color: white;
				-moz-border-radius:5px;
				-webkit-border-radius:5px;
				border-radius:5px
			}
			tr {
				height: 35px;
			}
			th {
				text-align: center;
				background-color: #d9d9d9;
			}
			hr { 
				display: block;
				margin-top: 0.5em;
				margin-bottom: 0.5em;
				margin-left: auto;
				margin-right: auto;
				border-style: inset;
				border-width: 1px;
			} 
			input {
				min-width: 50%;
				max-width: 80%;
			}
			.centralize{
				text-align: center;
			}
			.noBorder {
				background-color: transparent;
				border: 0px solid;
			}
			.borda {
				border: 1px solid black;
				width: 90%;
			}
			.obs {
				font-size: 8;
			}
			.obs1 {
				border: 1px solid black;
				font-size: 7;
			}
		";
		addStyleLinhas($this->_style);
	}
}