<?php

/*
* Data Criação: 12/01/2015 - 17:18:33
* Autor: Thiel
*
* Arquivo: class.avarias.inc.php
* 
* Apontamento/contagem de avarias
* 
*/



if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class avarias{
	var $funcoes_publicas = array(
		'index' 		=> true,
		'atualiza'		=> true,
		'finaliza'		=> true,
	    'ajax'          => true,
	);
	
	function __construct(){
		
	}
	
	function ajax(){
	    $ret = '';
	    $op = getOperacao();
	    if($op == 'finalizar'){
	        $ret = $this->finaliza();
	    }
	    elseif ($op == 'atualizar'){
	        $ret = $this->atualiza();
	    }
	    return $ret;
	}
	
	function finaliza(){
		global $app;
		global $nl;
		/*
		$GLOBALS['bf_pag'] = array(
				'flags' => array(
						'header'   => false,
						'menu'   => false,
						'footer'   => false,
						'html'		=> false,
						'onLoad'	=> ''
				)
		);
		*/
		
		$GLOBALS['tws_pag'] = array(
		    'header'   	=> false, //Imprime o cabeçalho (no caso de ajax = false)
		    'html'		=> false, //Imprime todo html (padão) ou só o processamento principal?
		    'menu'   	=> false,
		    'content' 	=> false,
		    'footer'   	=> false,
		    'onLoad'	=> '',
		);
		
		if (!isset( $_SESSION['produtos'] )) {
			$_SESSION['produtos'] = array();
		}
		$produtos =& $_SESSION['produtos'];
//print_r($produtos);
		
		$sql = "SELECT MAX( leitura ) FROM gf_avarias";
		$rows = query($sql);
		
		$leitura = $rows[0][0] + 1;
		
		$ret = '';
		$ret .= '<div id="grid" class="grid">'.$nl;
		$ret .= '<table border="0" style="border: 1px solid #a5b5ee;" cellpadding="2" cellspacing="2" width="100%">'.$nl;
		$ret .= '<tr>'.$nl;
		$ret .= '<td height="40" align="center" width="10%" valign="middle" class="tab_td">Cod</td>'.$nl;
		$ret .= '<td height="40" align="center" width="20%" valign="middle" class="tab_td">EAN</td>'.$nl;
		$ret .= '<td height="40" align="center" width="50%" valign="middle" class="tab_td">Produto</td>'.$nl;
		$ret .= '<td height="40" align="center" width="50%" valign="middle" class="tab_td">Endereco</td>'.$nl;
		$ret .= '<td height="40" align="center" width="20%" valign="middle" class="tab_td">Quantidade</td>'.$nl;
		$ret .= '</tr>'.$nl;
		
		foreach ($produtos as $prod => $produto){
			$sql = "SELECT PCPRODUT.codprod, 
					       PCPRODUT.descricao,
					       pcendereco.rua || '.' || pcendereco.predio || '.' || pcendereco.nivel || '.' || pcendereco.apto ENDERECO
					FROM PCPRODUT,
					     pcestendereco,
					     pcendereco
					WHERE PCPRODUT.codauxiliar = $prod
					    and pcprodut.codprod = pcestendereco.codprod
					    and pcestendereco.codendereco = pcendereco.codendereco
					    and pcendereco.rua in (1,2)";
			$rows = query4($sql);
			$cod = $rows[0][0];
			$desc = $rows[0][1];
			$end = $rows[0][2];
			
			$sql = "INSERT INTO gf_avarias (data,leitura,user,produto,quant,endereco) VALUES ('".date('Ymd')."',$leitura,'".getUsuario()."',$cod,$produto,'$end')";
			query($sql);
			
			$ret .= '<tr>'.$nl;
			$ret .= '<td height="40" align="center" width="10%" valign="middle" class="tab_td">'.$cod.'</td>'.$nl;
			$ret .= '<td height="40" align="center" width="20%" valign="middle" class="tab_td">'.$prod.'</td>'.$nl;
			$ret .= '<td height="40" align="center" width="50%" valign="middle" class="tab_td">'.$desc.'</td>'.$nl;
			$ret .= '<td height="40" align="center" width="50%" valign="middle" class="tab_td">'.$end.'</td>'.$nl;
			$ret .= '<td height="40" align="center" width="20%" valign="middle" class="tab_td">'.$produto.'</td>'.$nl;
			$ret .= '</tr>'.$nl;
		}
		$ret .= '</table>'.$nl;
		$ret .= '</div>'.$nl;
		//$ret = tabela3("Contagem - Leitura: $leitura. Usuario: ".$app->user->user, $ret, "100%");
		$ret = addCard(['conteudo' => $ret, 'titulo' => 'Contagem - Leitura: ' . $leitura . ' Usuario: '.getUsuario()]);

		unset($_SESSION['produtos']);
		
		return $ret;
	}
	
	function atualiza(){
		global $app;
		
		/*
		$GLOBALS['bf_pag'] = array(
				'flags' => array(
						'header'   => false,
						'menu'   => false,
						'footer'   => false,
						'html'		=> false,
						'onLoad'	=> ''
				)
		);
		*/
		
		/*
		$GLOBALS['tws_pag'] = array(
		    'header'   	=> false, //Imprime o cabeçalho (no caso de ajax = false)
		    'html'		=> false, //Imprime todo html (padão) ou só o processamento principal?
		    'menu'   	=> false,
		    'content' 	=> false,
		    'footer'   	=> false,
		    'onLoad'	=> '',
		);*/
		
		$produto = $_GET['produto'];
		$usuario = getUsuario();

		if (!isset( $_SESSION['produtos'] )) {
			$_SESSION['produtos'] = array();
		}
		$produtos =& $_SESSION['produtos'];
		
		//$sql = "SELECT codprod FROM PCPRODUT WHERE codauxiliar = $produto";
		$sql = "SELECT PCPRODUT.codprod, 
				       PCPRODUT.descricao,
				       pcendereco.rua || '.' || pcendereco.predio || '.' || pcendereco.nivel || '.' || pcendereco.apto ENDERECO
				FROM PCPRODUT,
				     pcestendereco,
				     pcendereco
				WHERE PCPRODUT.codauxiliar = $produto
				    and pcprodut.codprod = pcestendereco.codprod
				    and pcestendereco.codendereco = pcendereco.codendereco
				    and pcendereco.rua in (1,2)
				";
		$rows = query4($sql);
		
		if(!is_array($rows) || count($rows) == 0){
			return '<div style="color: red;">'.$produto.' - Produto Inválido!</div>';
		}
		
		if(isset($produtos[$produto])){
			$produtos[$produto]++;
		}else{
			$produtos[$produto] = 1;
		}
		
		//$desc = $this->getProdDesc($produto);
		
		return $produto.' - '.$rows[0][1].' - '.$rows[0][2].' - '.ajustaCaractHTML('Incluído!');
		//return $produto.' - '.$desc.' - '.ajustaCaractHTML('Incluído!');
	}
	
	function getProdDesc($prod){
	    $ret = '';
	    
		$sql = "SELECT descricao FROM PCPRODUT WHERE codauxiliar = $prod";
		$rows = query4($sql);
		
		if(is_array($rows) && count($rows) > 0) {
		  $ret = $rows[0][0];
		}
		
		return $ret;
	}
	
	function index(){
		//global $portal_js, $portal_css, $config;
		//$portal_css['Browse'] = $config["themepath"]."browse.css";
		$this->addScript();
		return $this->printForm();
	}
	
	function printForm(){
		global $nl;
		$ret = '';
		$botao = formbase01::formBotao(array('tipo' => 'botao', 'textoAlt'=>'Encerrar Contagem', 'id'=>'BTfecha','onclick'=>'fecharAvarias();', 'texto' => 'Encerrar Contagem'));

		$ret .= '<table border="0" cellpadding="2" cellspacing="2" width="100%">'.$nl;
		$ret .= '<tr>'.$nl;
		$ret .= '<td height="40" align="center" width="60%" valign="top">'.$nl;
		
			$temp= '<table border="0" cellpadding="0" cellspacing="0" width="100%">'.$nl;
			$param = array();
			$param['valor'] = "";
			$param['classeadd'] = ' pulaCampo';
			$param['nome'] = 'produto';
			$param['id'] = 'produto';
			$temp .= '<tr>'.$nl;
			$temp .= '<td height="40" align="center">Produto: '.formbase01::formTexto($param).'</td>'.$nl;
			$temp .= '</tr>'.$nl;
			$temp .= '<tr>'.$nl;
			$temp .= '<td height="40" align="center">'.$botao.'</td>'.$nl;
			$temp .= '</tr>'.$nl;
			$temp .= '</table>'.$nl;
		
		//$ret .= tabela3("Lancamento Avarias", $temp, "100%");
		$ret .= addCard(['conteudo' => $temp, 'titulo' => 'Lancamento Avarias']);
			
		$ret .= '</td>'.$nl;
		//$ret .= '<td height="40" align="center" width="40%" valign="top">'.tabela3("Produtos Lançados:", '<div id="lancadas"></div>',"100%").'</td>'.$nl;
		$ret .= '<td height="40" align="center" width="40%" valign="top">'.addCard(['conteudo' => '<div id="lancadas"></div>', 'titulo' => 'Produtos Lançados:']).'</td>'.$nl;
		$ret .= '</tr>'.$nl;
		$ret .= '</table>'.$nl;
		$ret .= '<div id="resultado"></div>';
				
		//$ret = formbase::formForm($ret, 'index.php?menu=gflogistica.canhoto.atualiza', 'canhotos');
		
		
		
		return $ret;
	}	

	function addScript(){
	    global $nl;
		addPortalJquery("$('#produto').focus();  ");
		addPortalJquery("$('.pulaCampo').keypress(function(e){"); //* * verifica se o evento é Keycode (para IE e outros browsers) * se não for pega o evento Which (Firefox) */ 
		addPortalJquery("	var tecla = (e.keyCode?e.keyCode:e.which);"); //* verifica se a tecla pressionada foi o ENTER */ 
		addPortalJquery("if(tecla == 13){");
		addPortalJquery("	produto = $('#produto').val();");
		addPortalJquery("	$.ajax({");
		addPortalJquery("		  url: '" . getLinkAjax('atualizar') . "&produto=' + produto,");
		addPortalJquery("		  success: function(data) {");
	  //addPortalJquery("		    alert('1'+data);");;
		addPortalJquery("		    $('<p style=\"font-size: 12px;\"/>').html(data).prependTo('#lancadas');").$nl;
	  //addPortalJquery("		    alert('2'+data);");
		addPortalJquery("			$('#produto').val('');");
		addPortalJquery("		  }");
		addPortalJquery("		});");

		addPortalJquery("	campo = $('.pulaCampo');");
		addPortalJquery("	indice = campo.index(this);");
		addPortalJquery("	if(campo[indice+1] != null){");
		addPortalJquery("		proximo = campo[indice + 1];");
		addPortalJquery("		proximo.focus(); ");
		addPortalJquery("	} ");
		addPortalJquery("}else{ return true;}");
		addPortalJquery("e.preventDefault(e); ");
		addPortalJquery("return false; ");
		addPortalJquery("})");
		
		addPortaljavaScript("function fecharAvarias(){");
		addPortaljavaScript("	$.ajax({");
		addPortaljavaScript("		  url: '" . getLinkAjax('finalizar') . "',");
		addPortaljavaScript("		  success: function(data) {");
		addPortaljavaScript("		    $('#resultado').html(data);").$nl;
		addPortaljavaScript("			$('#lancadas').html('');");
		addPortaljavaScript("		  }");
		addPortaljavaScript("		});");
		addPortaljavaScript("}");
	}
	function motivos(){
		$ret = array(	1	=> array(0=>31	,1=>'AJUSTE                                '),
						2	=> array(0=>20	,1=>'ATRASO NA ENTREGA                     '),
						3	=> array(0=>72	,1=>'AVARIA                                '),
						4	=> array(0=>35	,1=>'AVARIA - DEVOLUÇÃO DE CLIENTE         '),
						5	=> array(0=>43	,1=>'AVARIA - USO INTERNO                  '),
						6	=> array(0=>45	,1=>'AVARIA - VENC. CURTO/VENCIDO          '),
						7	=> array(0=>64	,1=>'AVARIA DE TRANSPORTE                  '),
						8	=> array(0=>46	,1=>'AVARIA DEF.- AMASSADO CX PDR          '),
						9	=> array(0=>47	,1=>'AVARIA DEF.- CPM TRINCADO             '),
						10	=> array(0=>48	,1=>'AVARIA DEF.- EMB. MANCHADA            '),
						11	=> array(0=>49	,1=>'AVARIA DEF.- EMB. SEM RÓTULO          '),
						12	=> array(0=>50	,1=>'AVARIA DEF.- EMBALAGEM VAZIA          '),
						13	=> array(0=>59	,1=>'AVARIA DEF.- ENFERRUJADO              '),
						14	=> array(0=>51	,1=>'AVARIA DEF.- FALTA NA CX PDR          '),
						15	=> array(0=>58	,1=>'AVARIA DEF.- NÃO FUNCIONA             '),
						16	=> array(0=>52	,1=>'AVARIA DEF.- SEM TAMPA                '),
						17	=> array(0=>54	,1=>'AVARIA DEF.- VAZANDO                  '),
						18	=> array(0=>53	,1=>'AVARIA DEF.-DISPLAY INCOMPLETO        '),
						19	=> array(0=>55	,1=>'AVARIA DEF.-EMB. COLADA CX PDR        '),
						20	=> array(0=>57	,1=>'AVARIA DEF.-RASGADA NA CX PDR         '),
						21	=> array(0=>56	,1=>'AVARIA DEF.-SEM LOTE E VENC.          '),
						22	=> array(0=>71	,1=>'AVARIA DEF-DESVIO DE QUALIDADE        '),
						23	=> array(0=>39	,1=>'AVARIA GERAL - CONFERÊNCIA            '),
						24	=> array(0=>104	,1=>'AVARIA GERAL - CONTROLADO             '),
						25	=> array(0=>40	,1=>'AVARIA GERAL - EMBALAGEM              '),
						26	=> array(0=>37	,1=>'AVARIA GERAL - ESTOQUE                '),
						27	=> array(0=>41	,1=>'AVARIA GERAL - EXPEDIÇÃO              '),
						28	=> array(0=>36	,1=>'AVARIA GERAL - RECEBIMENTO            '),
						29	=> array(0=>38	,1=>'AVARIA GERAL - SEPARAÇÃO              '),
						30	=> array(0=>34	,1=>'AVARIA NO TRANSPORTE                  '),
						31	=> array(0=>33	,1=>'AVARIA RECUPERADA                     '),
						32	=> array(0=>91	,1=>'AVARIA/EXTRAV DEVOLUME -TRANSP        '),
						33	=> array(0=>96	,1=>'avaria-refaturamento                  '),
						34	=> array(0=>74	,1=>'BLOQUEIO DE ESTOQUE                   '),
						35	=> array(0=>90	,1=>'CADASTRO NA TABELA ERRADA             '),
						36	=> array(0=>70	,1=>'CLIENTE DESISTIU DA COMPRA            '),
						37	=> array(0=>97	,1=>'CLIENTE ERRADO                        '),
						38	=> array(0=>15	,1=>'CLIENTE NAO ACEITOU PG. IMPOST        '),
						39	=> array(0=>82	,1=>'CLIENTE SOLICITOU PROD ERRADO         '),
						40	=> array(0=>24	,1=>'COND. PAGTO. DIF. DO NEGOCIADO        '),
						41	=> array(0=>32	,1=>'CORTE LOGISTICO                       '),
						42	=> array(0=>106	,1=>'CORTE POR VALIDADE                    '),
						43	=> array(0=>99	,1=>'DEFEITO DE FABRICA                    '),
						44	=> array(0=>65	,1=>'DESACORDO COMERCIAL                   '),
						45	=> array(0=>84	,1=>'DESACORDO COMERCIAL                   '),
						46	=> array(0=>63	,1=>'DESBLOQUEIO DE MERC PARA VENDA        '),
						47	=> array(0=>9	,1=>'DESISTIU DA COMPRA                    '),
						48	=> array(0=>89	,1=>'DEVOL FORNEC DEFEITO E VENCIDO        '),
						49	=> array(0=>12	,1=>'ENDERECO DIVERGENTE                   '),
						50	=> array(0=>60	,1=>'Entrada (Acerto de Lote)              '),
						51	=> array(0=>19	,1=>'FALTA DE MERCADORIA                   '),
						52	=> array(0=>30	,1=>'FALTA DE MERCADORIA                   '),
						53	=> array(0=>81	,1=>'FALTA TRANSP - EXTRAVIO DE VOL        '),
						54	=> array(0=>102	,1=>'FALTA-CAPILÉ                          '),
						55	=> array(0=>83	,1=>'FARMACIA FECHOU                       '),
						56	=> array(0=>80	,1=>'LANÇAMENTO INDEVIDO                   '),
						57	=> array(0=>66	,1=>'MERC. ENVIADA PARA CONSERTO           '),
						58	=> array(0=>77	,1=>'MERCADORIA LOCALIZADA                 '),
						59	=> array(0=>88	,1=>'NAO SOLICITADO                        '),
						60	=> array(0=>76	,1=>'NF EMITIDA INCORRETAMENTE             '),
						61	=> array(0=>68	,1=>'OUTRO                                 '),
						62	=> array(0=>103	,1=>'PEDIDO CANCELADO                      '),
						63	=> array(0=>6	,1=>'PEDIDO DUPLICADO                      '),
						64	=> array(0=>85	,1=>'PEDIDO NAO SOLICITADO                 '),
						65	=> array(0=>4	,1=>'PRECO DIFERENTE DO NEGOCIADO          '),
						66	=> array(0=>105	,1=>'PROD NAO CADAS NA FCIA POPULA         '),
						67	=> array(0=>73	,1=>'PRODUTO DIFERENTE SOLICITADO          '),
						68	=> array(0=>7	,1=>'PRODUTO NAO SOLICITADO                '),
						69	=> array(0=>79	,1=>'QNTD MAIOR QUE SOLICITADO             '),
						70	=> array(0=>42	,1=>'REAPROVEITAMENTO DE AVARIA            '),
						71	=> array(0=>100	,1=>'RECOLHIMENTO                          '),
						72	=> array(0=>62	,1=>'RECOLHIMENTO VENCIMENTO CURTO         '),
						73	=> array(0=>94	,1=>'REFATURAMENTO                         '),
						74	=> array(0=>92	,1=>'REFATURAMENTO - DESACORDO COME        '),
						75	=> array(0=>93	,1=>'ROTULO TROCADO -ERRO LOGISTICO        '),
						76	=> array(0=>61	,1=>'Saída (Acerto de Lote)                '),
						77	=> array(0=>44	,1=>'SOLIC. RECOLHIMENTO COMPRAS           '),
						78	=> array(0=>107	,1=>'TRANSPORTADORA ENCONTROU VOL          '),
						79	=> array(0=>101	,1=>'Troca Carga                           '),
						80	=> array(0=>95	,1=>'TROCA DE VOLUME                       '),
						81	=> array(0=>98	,1=>'VENCIDOS                              '),
						82	=> array(0=>67	,1=>'VENCIMENTO CURTO                      '),
										
					);
		return $ret;
	}
}