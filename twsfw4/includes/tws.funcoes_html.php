<?php

/**
 * Adiciona a string passada nas tags <script></script> no início ou final da página
 *
 * @param string $string - Código a ser adicionado
 * @param string \$posicao - Inicio ou Final da Página
 */
function addPortaljavaScript($string, $posicao = 'I'){
	global $pagina;
	$pagina->addJavascript($string, $posicao);
}

/**
 * Adiciona um CSS  no início ou final da página
 */
function addPortalCSS($pasta, $arquivo, $posicao = 'I', $indice = ''){
	global $pagina;
	$pagina->addStyle($pasta, $arquivo, $posicao, $indice);
}

/**
 * Adiciona um JS  no início ou final da página
 */
function addPortalJS($pasta, $arquivo, $posicao = 'I', $indice = ''){
	global $pagina;
	$pagina->addScript($pasta, $arquivo, $posicao, $indice);
}

/**
 * Inclui o parâmetro dentro da $(function) JQuery
 *
 * @param string $linha
 */
function addPortalJquery($linha, $posicao = 'I'){
	global $pagina;
	$posicao = $posicao == 'F' ? 'F' : 'I';
	if(isset($pagina)){
		$pagina->addJquery($linha, $posicao);
	}
}

/**
 * Adiciona uma mensagem a ser exibida no portal
 *
 * @param	string	$mensagem	Mensagem
 * @param	string	$cor		Cor - success | info | error | warning
 * @return	void
 */
function addPortalMensagem($mensagem,$cor = 'success'){
    
	if(!empty($mensagem)){
	    /*
		addPortalCSS('plugin', 'toastr/toastr.min.css', 'I', 'toastr');
		addPortalJS('plugin', 'toastr/toastr.min.js','I', 'toastr');
		$cor = !empty($cor) ? $cor : 'success';
		addPortalJquery('toastr.'.$cor.'("'.$mensagem.'");');
		*/
	    $fila_mensagens = getAppVar('fila_mensagens');
	    if($fila_mensagens === null){
	        $fila_mensagens = array();
	    }
	    $mensagem_nova = array(
	        'mensagem' => $mensagem,
	        'cor' => $cor,
	    );
	    $fila_mensagens[] = $mensagem_nova;
	    putAppVar('fila_mensagens', $fila_mensagens);
	}
}

/**
 * 
 * Cores:
  		'' 			-> Branco
		primary 	-> Azul
		secondary 	-> Cinza
		success 	-> Verde
		info" 		-> Azul Claro
		warning 	-> Amarelo/Laranja
		danger 		-> Vermelho
		dark" 		-> Preto
 * @param array $parametros
 * @return string
 */
function addCard($parametros){
	global $nl;
	$ret = '';
	$styleCard = [];
	
	$outline 	= $parametros['outline'] ?? CARD_OUTLINE;
	$chat 		= $parametros['chat'] ?? false;
	
	$cor 		= $parametros['cor'] ?? 'primary';
	$possiveis = ['','primary','secondary','success','info','warning','danger','default'];
	if(array_search($cor,$possiveis) !== false){
		$styleCard[]= 'card-'.$cor;
	}
	
	$titulo 	= $parametros['titulo'] ?? '';
	$conteudo 	= $parametros['conteudo'] ?? '';
	$footer 	= $parametros['footer'] ?? '';
	
	//Determina se é um card de chat
	if($chat){
		$styleCard[] = ' direct-chat direct-chat-success';
	}

	if($outline){
		$styleCard[] = 'card-outline';
	}
	
	//Botoes
	$botoesTool = [];
	if(isset($parametros['collapse']) && $parametros['collapse']){
		$botoesTool[] = ['collapse' => 'fa fa-minus'];
	}
	if(isset($parametros['remove']) && $parametros['remove']){
		$botoesTool[] = ['remove' => 'fa fa-times'];
	}
	
	//Botoes título
	$botoesTitulo = [];
	if(isset($parametros['botoesTitulo']) && is_array($parametros['botoesTitulo'])){
		$botoesTitulo = $parametros['botoesTitulo'];
	}
	
	//Botão cancelar (voltar)
	if(isset($parametros['botaoCancelar']) && $parametros['botaoCancelar'] === true){
		$temp = [];
		$temp_link 			= $parametros['botaoCancelarLink'] ?? getLink().'index';
		$temp['onclick'] 	= "setLocation('".$temp_link."')";
		//$temp['tamanho'] 	= 'pequeno';
		$temp['cor'] 		= $parametros['botaoCancelarCor'] ?? COR_PADRAO_BOTAO_CANCELAR;
		$temp['texto'] 		= $parametros['botaoCancelarTexto'] ?? 'Cancelar';
		
		$botoesTitulo[] = $temp;
	}
	
	$ret .= '<div class="card '.implode(' ', $styleCard).'">'.$nl;
	
	$header_class = ''; 
	if(isset($parametros['header-class'])){
		$header_class = is_array($parametros['header-class']) ? implode(' ', $parametros['header-class']) : $parametros['header-class'];
	}
	$header_style = '';
	if(isset($parametros['header-style'])){
		$header_class = is_array($parametros['header-style']) ? implode('; ', $parametros['header-style']) : $parametros['header-style'];
		$header_style = 'style="'.$header_class.'"';
	}
	$ret .= '	<div class="card-header '.$header_class.'" '.$header_style.'>'.$nl;
	$icone = isset($parametros['icone']) ? addIcone($parametros['icone']) : '';
	$ret .= '		<h3 class="card-title">'.$icone.$titulo.'</h3>'.$nl;
	if(count($botoesTool) > 0 || count($botoesTitulo) > 0){
		$ret .= '		<div class="card-tools">'.$nl;
		//$ret .= '			<span title="3 New Messages" class="badge bg-success">3</span>'.$nl;
		
		if(count($botoesTitulo) > 0){
			foreach ($botoesTitulo as $botao){
				$ret .= '&nbsp;'.formbase01::formBotao($botao);
			}
		}
		
		
		if(count($botoesTool) > 0){
			foreach ($botoesTool as $bot){
				foreach ($bot as $acao => $icone){
					$ret .= '			<button type="button" class="btn btn-tool" data-card-widget="'.$acao.'">'.$nl;
					$ret .= '				<i class="'.$icone.'"></i>'.$nl;
					$ret .= '			</button>'.$nl;
				}
			}
		}
		//$ret .= '			<button type="button" class="btn btn-tool" title="Contacts" data-widget="chat-pane-toggle">'.$nl;
		//$ret .= '				<i class="fas fa-comments"></i>'.$nl;
		//$ret .= '			</button>'.$nl;
		$ret .= '	</div>'.$nl;
	}
	$ret .= '	</div>'.$nl;

	$ret .= '	<div class="card-body">'.$nl;
	$ret .= '		'.$conteudo;	
	$ret .= '	</div>'.$nl;
	
	if(!empty($footer)){
		$ret .= '	<div class="card-footer">'.$nl;
		$ret .= '		<form action="#" method="post">'.$nl;
		$ret .= '			<div class="input-group">'.$nl;
		$ret .= '				<input type="text" name="message" placeholder="Type Message ..." class="form-control">'.$nl;
		$ret .= '				<span class="input-group-append">'.$nl;
		$ret .= '					<button type="submit" class="btn btn-success">Send</button>'.$nl;
		$ret .= '				</span>'.$nl;
		$ret .= '			</div>'.$nl;
		$ret .= '		</form>'.$nl;
		$ret .= '	</div>'.$nl;
	}
	$ret .= '	</div>'.$nl;
	return $ret;
	
}

function ajustaCaractHTML($texto){
	$de = array('á','Á','ã','Ã','â','Â','à','À','é','É','ê','Ê','í','Í','ó','Ó','õ','Õ','ô','Ô','ú','Ú','ç','Ç');
	$por = array('&aacute;','&Aacute;','&atilde;','&Atilde;','&acirc;' ,'&Acirc;' ,'agrave;' ,'&Agrave;','&eacute;','&Eacute;','&ecirc;' ,'&Ecirc;' ,'&iacute;','&Iacute;','&oacute;','&Oacute;','&otilde;','&Otilde;','&ocirc;' ,'&Ocirc;' ,'&uacute;','&Uacute;','&ccedil;','&Ccedil;');
	$ret = str_replace($de, $por, $texto);
	return  $ret;
}

function addRow($texto){
	global $nl;
	
	$ret = '<div class="row">'.$nl;
	$ret .= $texto.$nl;
	$ret .= '</div>'.$nl;
	
	return $ret;
}

function addDivColuna($tam, $texto){
	global $nl;
	
	$ret = '<div class="col-md-'.$tam.'">'.$nl;
	$ret .= $texto.$nl;
	$ret .= '</div>'.$nl;
	
	return $ret;
}

function addLinha($param){
	global $nl;
	$ret = '';
	
	$tamanhos = $param['tamanhos'] ?? [12];
	$conteudos  = $param['conteudos'] ?? ['Sem Conteudo'];
	
	
	$ret .= "<div class='row'>".$nl;
	foreach ($tamanhos as $k => $tamanho){
		$cont = $conteudos[$k] ?? '-';
		$ret .= "	<div class='col-lg-$tamanho'>".$nl;
		$ret .= $cont.$nl;
		$ret .= "	</div>".$nl;
	}
	$ret .= "</div>".$nl;
	
	return $ret;
}

function addIcone($icone, $cor = ''){
	global $nl;
	$ret = '';
	
	if(!empty($icone)){
		$ext = '';
		if(strpos($icone, 'fa') !== false){
			$ext = 'fa';
		}elseif(strpos($icone, 'glyphicon') !== false){
			$ext = 'glyphicon';
		}elseif(strpos($icone, 'ion') !== false){
			$ext = 'ion';
		}

		if(!empty($ext)){
			$ret = '<i class="'.$ext.' '.$icone.' '.$cor.'"></i>'.$nl;
		}
	}
	
	return $ret;
}


function addCardsMoveis($param){
	global $nl;
	$ret = '';
	
	addPortalJquery("$('.connectedSortable').sortable({placeholder:'sort-highlight',connectWith:'.connectedSortable',handle:'.card-header, .nav-tabs',forcePlaceholderSize:true,zIndex:999999})");
	
	$colunas = $param['colunas'] ?? 2;
	$largura = intdiv(12, $colunas);
	$larguras = [];
	
	for($i=0;$i<$colunas;$i++){
		$larguras[$i] = $largura;
		$ultimo = $i;
	}
	
	if($colunas * $largura <> 12){
		$larguras[$ultimo] = $largura + (12 - ($colunas * $largura));
	}
	
	$ret .= '<div class="row">'.$nl;
	foreach ($larguras as $coluna => $largura){
		$ret .= '	<section class="col-lg-'.$largura.' connectedSortable ui-sortable">'.$nl;
		$ret .= $param['cards'][$coluna];
		$ret .= '	</section>'.$nl;
	}
	$ret .= '</div>'.$nl;
	
	return $ret;
}


/**
 * Retorna uma Timeline
 *
 * $param['pai'][0]
 * 				   ['cor']
 * 				   ['titulo']
 * 				   ['filhos']
 * 							 [0]['icone']
 * 							 [0]['iconeCor']
 * 							 [0]['hora']
 * 							 [0]['titulo']
 * 							 [0]['titSub']
 * 							 [0]['titLink']
 * 							 [0]['conteudo']
 * 							 [0]['botoes']
 * 							 [0]['footer']
 *
 * @param array $param
 * @return string
 */
function addTimeline($param){
	global $nl;
	$ret = '';
	$param['iconeFinal'] = verificaParametro($param, 'iconeFinal', true);
	
	if(isset($param['pai']) && count($param['pai']) > 0){
		$ret .= '<div class="row">'.$nl;
		$ret .= '<div class="col-md-12">'.$nl;
		$ret .= '<div class="timeline">'.$nl;
		
		foreach ($param['pai'] as $pai){
			$pai['cor'] = verificaParametro($pai, 'cor','bg-green');
			$ret .= '	<div class="time-label">'.$nl;
			$ret .= '		<span class="'.$pai['cor'].'">'.$pai['titulo'].'</span>'.$nl;
			$ret .= '	</div>'.$nl;
			
			if(isset($pai['filho']) && count($pai['filho']) > 0){
				foreach($pai['filho'] as $filho){
					$filho['icone'] 	= verificaParametro($filho, 'icone','fa-comments');
					$filho['iconeCor'] 	= verificaParametro($filho, 'iconeCor','bg-aqua');
					$filho['conteudo'] 	= verificaParametro($filho, 'conteudo','');
					$filho['botoes'] 	= verificaParametro($filho, 'botoes',[]);
					$filho['footer'] 	= verificaParametro($filho, 'footer','');
					$ret .= '		<div>'.$nl;
					$ret .= addIcone($filho['icone'], $filho['iconeCor']);

					$ret .= '			<div class="timeline-item">'.$nl;
					if(isset($filho['hora']) && !empty($filho['hora'])){
						$ret .= '				<span class="time"><i class="fa fa-clock-o"></i> '.$filho['hora'].'</span>'.$nl;
					}
					$titulo = isset($filho['titulo']) ? $filho['titulo'] : '';
					$subTit = isset($filho['titSub']) ? $filho['titSub'] : '';
					if(!empty($titulo)){
						$link = '#';
						if(isset($filho['titLink']) && !empty($filho['titLink'])){
							$link = $filho['titLink'];
						}
						$titulo = '<a href="'.$link.'">'.$titulo.'</a>'.$subTit;
						$ret .= '				<h3 class="timeline-header">'.$titulo.'</h3>'.$nl;
					}
					//------------------------------------------------------------------------------- Corpo
					if(!empty($filho['conteudo'])){
						$ret .= '				<div class="timeline-body">'.$nl;
						$ret .= $filho['conteudo'];
						$ret .= '				</div>'.$nl;
					}
					
					//------------------------------------------------------------------------------- Footer
					$botoes = $filho['botoes'];
					if(count($botoes) > 0 || !empty($filho['footer'])){
						$ret .= '				<div class="timeline-footer">'.$nl;
						$ret .= $filho['footer'];
						if(count($botoes) > 0){
							foreach ($botoes as $botao){
								$ret .= formbase01::formBotao($botao);
							}
						}
						$ret .= '				</div>'.$nl;
					}
					$ret .= '			</div>'.$nl;
					$ret .= '		</div>'.$nl;
				}
			}
		}
		if($param['iconeFinal']){
			$ret .= '<div>'.$nl;
			$ret .= '<i class="fa fa-clock-o bg-gray"></i>'.$nl;
			$ret .= '</div>'.$nl;
		}
		$ret .= '</div>'.$nl;
		$ret .= '</div>'.$nl;
		$ret .= '</div>'.$nl;
	}
	return $ret;
}

function addTimelineOld($param){
	global $nl;
	$ret = '';
	$param['iconeFinal'] = verificaParametro($param, 'iconeFinal', true);
	
	if(isset($param['pai']) && count($param['pai']) > 0){
		$ret .= '<ul class="timeline">'.$nl;
		foreach ($param['pai'] as $pai){
			$pai['cor'] = verificaParametro($pai, 'cor','bg-green');
			$ret .= '	<li class="time-label"><span class="'.$pai['cor'].'">'.$pai['titulo'].'</span></li>'.$nl;
			if(isset($pai['filho']) && count($pai['filho']) > 0){
				foreach($pai['filho'] as $filho){
					$filho['icone'] 	= verificaParametro($filho, 'icone','fa-comments');
					$filho['iconeCor'] 	= verificaParametro($filho, 'iconeCor','bg-aqua');
					$filho['conteudo'] 	= verificaParametro($filho, 'conteudo','');
					$filho['botoes'] 	= verificaParametro($filho, 'botoes',[]);
					$filho['footer'] 	= verificaParametro($filho, 'footer','');
					$ret .= '		<li>'.$nl;
					if(strpos($filho['icone'], 'fa') !== false){
						$ext = 'fa';
					}elseif(strpos($filho['icone'], 'glyphicon') !== false){
						$ext = 'glyphicon';
					}elseif(strpos($filho['icone'], 'ion') !== false){
						$ext = 'ion';
					}
					$ret .= '			<i class="'.$ext.' '.$filho['icone'].' '.$filho['iconeCor'].'"></i>'.$nl;
					$ret .= '			<div class="timeline-item">'.$nl;
					if(isset($filho['hora']) && !empty($filho['hora'])){
						$ret .= '				<span class="time"><i class="fa fa-clock-o"></i> '.$filho['hora'].'</span>'.$nl;
					}
					$titulo = isset($filho['titulo']) ? $filho['titulo'] : '';
					$subTit = isset($filho['titSub']) ? $filho['titSub'] : '';
					if(isset($filho['titLink']) && !empty($filho['titLink'])){
						$titulo = '<a href="'.$filho['titLink'].'">'.$titulo.'</a>'.$subTit;
					}else{
						$titulo = '<a href="#">'.$titulo.'</a>'.$subTit;
					}
					$ret .= '				<h3 class="timeline-header">'.$titulo.'</h3>'.$nl;
					//------------------------------------------------------------------------------- Corpo
					if(!empty($filho['conteudo'])){
						$ret .= '				<div class="timeline-body">'.$nl;
						$ret .= $filho['conteudo'];
						$ret .= '				</div>'.$nl;
					}
					
					//------------------------------------------------------------------------------- Footer
					$botoes = $filho['botoes'];
					if(count($botoes) > 0 || !empty($filho['footer'])){
						$ret .= '				<div class="timeline-footer">'.$nl;
						$ret .= $filho['footer'];
						if(count($botoes) > 0){
							foreach ($botoes as $botao){
								$ret .= formbase01::formBotao($botao);
							}
						}
						$ret .= '				</div>'.$nl;
					}
					$ret .= '			</div>'.$nl;
					$ret .= '		</li>'.$nl;
				}
			}
		}
		if($param['iconeFinal']){
			$ret .= '<li>'.$nl;
			$ret .= '<i class="fa fa-clock-o bg-gray"></i>'.$nl;
			$ret .= '</li>'.$nl;
		}
		$ret .= '</ul>'.$nl;
	}
	
	return $ret;
}

/**
 * Smal Box
 *
 * Cores: bg-green, bg-yellow, bg-red, bg-aqua, bg-blue, bg-lime, bg-light-blue, bg-navy,
 * 		  bg-teal, bg-olive, bg-orange, bg-fuchsia, bg-purple, bg-maroon, bg-black
 * @return string
 */
function boxPequeno($param){
	global $nl;
	$ret = '';
	
	$cor = verificaParametro($param, 'cor', 'bg-green');
	$valor = verificaParametro($param, 'valor', 0);
	$compl = verificaParametro($param, 'medida', '');
	$texto = verificaParametro($param, 'texto', '');
	$icone = verificaParametro($param, 'icone', 'ion-stats-bars');
	$footer = verificaParametro($param, 'footer', '');
	$link = verificaParametro($param, 'link', '#');
	$iconeFooter = verificaParametro($param, 'iconeFooter', 'fa-arrow-circle-right');
	
	$ret .= '<div class="small-box '.$cor.'">'.$nl;
	$ret .= '	<div class="inner">'.$nl;
	$extra = '';
	if(!empty($compl)){
		$extra = '<sup style="font-size: 20px">'.$compl.'</sup>';
	}
	$ret .= '		<h3>'.$valor.$extra.'</h3>'.$nl;
	$ret .= '		<p>'.$texto.'</p>'.$nl;
	$ret .= '	</div>'.$nl;
	$ret .= '	<div class="icon">'.$nl;
	$ret .= addIcone($icone);
	$ret .= '	</div>'.$nl;
	$ret .= '	<a href="'.$link.'" class="small-box-footer">'.$footer.' <i class="fa '.$iconeFooter.'"></i></a>'.$nl;
	$ret .= '</div>'.$nl;
	
	return $ret;
}