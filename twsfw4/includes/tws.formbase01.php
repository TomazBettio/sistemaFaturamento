<?php
/*
 * Data Criacao 14/02/2022
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Elemntos de formularios
 *
 * Altera��es:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class formbase01{
	
	static function construct(){
		if(!issetAppVar('formBase_layout')){
			// Tipo de formulario: basico, em linha, horizontal
			putAppVar('formBase_layout', 'basico');
		}
		if(!issetAppVar('formBase_ID')){
			// Controle de IDs (quando nao for indicada uma ID)
			putAppVar('formBase_ID'				, 0);
		}
		if(!issetAppVar('formBase_tamanhoForm')){
			// Tamanho dos forms
			putAppVar('formBase_tamanhoForm'	, ' form-control-sm');
		}
		if(!issetAppVar('formBase_tamanhoEtiqueta')){
			//Tamanho das etiquetas
			putAppVar('formBase_tamanhoEtiqueta', ' col-form-label-sm');
		}
		if(!issetAppVar('formBase_tamanhoSelect')){
			//Tamanho select
			putAppVar('formBase_tamanhoSelect'	, ' form-select-sm');
		}
		if(!issetAppVar('formBase_formGroup')){
			//Se deve imprimir o 'form-group'
			putAppVar('formBase_formGroup', true);
		}
	}
	
	//---------------------------------------------------------------------------------------------------- SETS ------------------------------
	
	static function setTamanho($tamanho){
		switch ($tamanho) {
			case 'grande':
				putAppVar('formBase_tamanhoForm'	,' form-control-lg');
				putAppVar('formBase_tamanhoEtiqueta',' col-form-label-lg');
				putAppVar('formBase_tamanhoSelect'	,' custom-select-lg');
				break;
			case 'medio':
			case 'padrao':
			case 'normal':
				putAppVar('formBase_tamanhoForm'	,'');	//form-control
				putAppVar('formBase_tamanhoEtiqueta','');	//col-form-label
				putAppVar('formBase_tamanhoSelect'	,'');	//custom-select
				break;
			case 'pequeno':
			default:
				putAppVar('formBase_tamanhoForm'	,' form-control-sm');
				putAppVar('formBase_tamanhoEtiqueta',' col-form-label-sm');
				putAppVar('formBase_tamanhoSelect'	,' form-select-sm');
				break;
		}
	}
	
	static function setLayout($layout = 'basico'){
		switch ($layout) {
			case 'basico':
				putAppVar('formBase_layout', 'basico');
				break;
			case 'basico-reduzido':
				putAppVar('formBase_layout','minimo');
				break;
			case 'horizontal-reduzido':
				putAppVar('formBase_layout','minimoHorizontal');
				break;
			default:
				putAppVar('formBase_layout','horizontal');
				break;
		}
	}
	
	static function setFormGroup($param = true){
		$formGroup = $param === false ? false : true;
		putAppVar('formBase_formGroup', $formGroup);
	}
	//---------------------------------------------------------------------------------------------------- FORMULARIOS -----------------------
	
	static function formHidden($param){
		global $nl;
		self::construct();
		$nome		= verificaParametro($param, 'nome', '');
		$valor		= verificaParametro($param, 'valor', '');
		$id			= verificaParametro($param, 'id', ajustaID($nome));
		
		$ret =  '<input type="hidden" name="'.$nome.'" value="'.$valor.'" id="'.$id.'">'.$nl;
		return $ret;
	}
	
	/**
	 * Retorna codigo de botão
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	array	$param		Indica as caracteristicas do botao/link
	 * 					[url]		URL para onde vai direcionar a acao
	 * 					[tipo]		(link, botao - padrao, input)
	 * 					[tamanho]	tamanho do botao (default, grande, pequeno, minimo - padrao -)
	 * 					[cor] 		cor do botao(default - padrao -, primary - azul, success - verde, info - azulfraco, warning - laranja, vermelho, branco)
	 * 					[bloco] 	tamanho normal - padrao - ou toda largura dispon�vel - true -
	 * 					[ativo]		indica se o bot�o est� ativo - padrao - ou desabilitado - false -
	 * 					[icone]		indica imagem (icone) a utilizar (do padr�o bootstrap)
	 * 					[posicone]	posicao da imagem A - antes (default) D - depois do texto
	 * 					[texto] 	string com o texto a ser impresso na tabela
	 * 					[type]		em inputs: type = "type"
	 * 					[onclick]
	 * 					[class]
	 * 					[id]
	 * @param	string	$name	Nome do parametro a procurar
	 * @param	string	$def	Valor que deve retornar se o parametro n�o existir
	 * @return	mixed	Valor do parametro
	 * @version 0.02
	 */
	static function formBotao($param){
		$ret = '';
		$classe =[];
		
		$tipo 				= $param['tipo']	?? 'botao';
		$param['posicone'] 	= $param['posicone']?? '';
		$bloco			 	= $param['bloco']	?? false;
		$tamanho		 	= $param['tamanho']	?? '';
		$param['url'] 		= $param['url']		?? '';
		$cor		 		= $param['cor']		?? COR_PADRAO_BOTOES;
		$param['texto']		= $param['texto']	?? '';
		
		$param['data-toggle'] 	= $param['data-toggle']		?? '';
		$param['data-target'] 	= $param['data-target']		?? '';
		$param['data-placement']= $param['data-placement']	?? '';
		$param['data-html']		= $param['data-html']		?? '';
		
		$help 	= $param['help'] ?? '';
		$help	= str_replace('"',	"'", $help);
		
		if(!empty($help)){
			$param['data-toggle'] 	= 'tooltip';
			$param['data-placement']= empty($param['data-placement']) ? 'top' : $param['data-placement'];
			$param['data-html']		= 'true';
		}
		
		switch ($tamanho) {
			case 'grande':
				$classe['tam'] = 'btn-lg';
				break;
			case 'pequeno':
				$classe['tam'] = 'btn-sm';
				break;
			case 'padrao':
				$classe['tam'] = '';
				break;
			default:
				$classe['tam'] = 'btn-xs';
				break;
		}
		
		switch ($cor) {
			case 'primary':
				$classe['cor'] = 'btn-primary';
				break;
			case 'secondary':
				$classe['cor'] = 'btn-secondary';
				break;
			case 'success':
				$classe['cor'] = 'btn-success';
				break;
			case 'info':
				$classe['cor'] = 'btn-info';
				break;
			case 'warning':
				$classe['cor'] = 'btn-warning';
				break;
			case 'danger':
				$classe['cor'] = 'btn-danger';
				break;
			case 'light':
				$classe['cor'] = 'btn-light';
				break;
			case 'dark':
				$classe['cor'] = 'btn-dark';
				break;
			case 'link':
				$classe['cor'] = 'btn-link';
				break;
			default:
				//$cor = COR_PADRAO_BOTOES;
				$classe['cor'] = 'btn-'.COR_PADRAO_BOTOES;
				break;
		}
		
		// Expande o botão para todo o espaço
		if ($bloco){
			$classe['bloco'] = 'btn-block';
		}
		
		$texto = $param['texto'];
		if(isset($param['icone']) && !empty($param['icone'])){
			if(strpos($param['icone'], 'glyphicon') !== false){
				$i = 'glyphicon';
			}else{
				$i = 'fa';
			}
			$icone = '<span class="'.$i.' '.$param['icone'].'"></span>';
			if($param['posicone'] == 'D'){
				$texto .= $icone;
			}else{
				$texto = $icone.' '.$texto;
			}
		}
		
		$class = implode(" ", $classe);
		$class = 'btn '.$class;
		
		if(isset($param['classe']) && $param['classe'] != ''){
			$class .= ' '.$param['classe'];
		}
		
		$textoAlt = '';
		if(isset($param['textoAlt']) && $param['textoAlt'] != ''){
			$textoAlt = ' title="'.$param['textoAlt'].'"';
		}
		
		$type = 'type="button"';
		if(isset($param['type']) && $param['type'] != ''){
			$type = ' type="'.$param['type'].'"';
		}
		
		$onclick = '';
		if(isset($param['onclick']) && $param['onclick'] !=''){
			$onclick = ' onclick="'.$param['onclick'].'"';
		}
		
		$id = $param['id'] ?? ajustaID($param['texto']);
		if(isset($param['id']) && $param['id'] !=''){
			$id = ' id="'.$param['id'].'"';
		}else{
			$id = ' id="'.$id.'"';
		}
		
		$data_toggle = '';
		if(!empty($param['data-toggle'])){
			$data_toggle = ' data-toggle="'.$param['data-toggle'].'"';
		}
		$data_target = '';
		if(!empty($param['data-target'])){
			$data_target = ' data-target="'.$param['data-target'].'"';
		}
		if(!empty($help)){
			$help = ' data-original-title="'.$help.'"';
		}
		
		$diversos = $textoAlt.$onclick.$id.$data_toggle.$data_target.$help;
		if($tipo == 'botao') {
			$ret = '<button '.$type.' class="'.$class.'" '.$diversos.'>'.$texto.'</button>';
		}elseif($tipo == 'link') {
			$ret = '<a href="'.$param['url'].'" class="'.$class.'" role="button"'.$diversos.'>'.$texto.'</a>';
		}elseif($tipo == 'input'){
			$ret .= '<input title="'.$texto.'"  class="'.$class.'"'.$diversos.'>';
		}
		return $ret;
	}
	
	
	static function formTexto($param, $enable = true){
		self::construct();
		$style = '';
		
		$nome 		= verificaParametro($param, 'nome', '');
		$etiqueta 	= verificaParametro($param, 'etiqueta', '');
		if(empty($nome) && isset($param['campo']) && !empty($param['campo'])){
			$nome = $param['campo'];
		}
		$valor 	= verificaParametro($param, 'valor', '');
		$id 	= ajustaID(verificaParametro($param, 'id', ajustaID($nome)));
		$estilo	= verificaParametro($param, 'estilo');
		
		$classeAdd 	= verificaParametro($param, 'classeadd');
		$tamanhoForm = getAppVar('formBase_tamanhoForm');
		$classeAdd 	= 'form-control '.$classeAdd.$tamanhoForm;
		$classe		= verificaParametro($param, 'classe');
		if(empty($classe)){
			$classe = $classeAdd;
		}
		$classe 	= 'class="'.$classe.'"';
		
		$tamanho 	= isset($param['tamanho']) && !empty($param['tamanho'])	? 'size="'.$param['tamanho'].'"' : "";
		
		$maxTamanho = isset($param['maxtamanho']) && $param['maxtamanho'] 	? 'maxLength="'.$param['maxtamanho'].'"' : "";
		$onkeypress	= isset($param['onkeypress']) && $param['onkeypress'] 	? 'onKeyPress="'.$param['onkeypress'].'"' : "";
		$onkeyup	= isset($param['onkeyup']) && $param['onkeyup'] 		? 'onkeyup="'.$param['onkeyup'].'"' : "";
		$onchange	= isset($param['onchange']) && $param['onchange'] 		? 'onchange="'.$param['onchange'].'"' : "";
		$onblur		= isset($param['onblur']) && $param['onblur'] 			? 'onblur="'.$param['onblur'].'"' : "";
		$alt		= isset($param['alt']) && $param['alt']					? 'alt="'.$param['alt'].'"' : "";
		$idGroup	= verificaParametro($param, 'idGroup', '');
		
		$help 		= verificaParametro($param, 'help');
		$layout 	= verificaParametro($param, 'layout');
		
		$readonly 	= isset($param['readonly']) && $param['readonly'] == true ? 'readonly' : '';
		$ativo 		= $enable ? '' : ' disabled ';
		$obrigatorio= verificaParametro($param, 'obrigatorio', false) == true ? 'required' : '';
		
		//Macara
		$mascara 	= verificaParametro($param, 'mascara');
		if(!empty(trim($mascara))){
			$reverse = false;
			$maskmoney = false;
			$decimais = 0;
			switch ($mascara) {
				case 'cpf':
					$mascara = '000.000.000-00';
					$reverse = true;
					break;
				case 'cnpj':
					$mascara = '00.000.000/0000-00';
					$reverse = true;
					break;
				case 'telefone':
					//Fixo é 8 números, e celular é 9.... então fica tudo com 9 sem separação
					$mascara = '(00) 000000000';
					break;
				case 'cep':
					$mascara = '00000-000';
					break;
				case 'I':
					$valor = empty($valor) ? 0 : $valor;
					$valor = round($valor,0);
					$maskmoney = (isset($param['negativo']) && $param['negativo'] === true) ? true : false;
					$mascara = '##############0';
					$decimais = 0;
					$estilo = empty($estilo) ? "text-align: right" : '';
					break;
				case 'N':
					$valor = empty($valor) ? 0 : $valor;
					$valor = round($valor,0);
					$maskmoney = (isset($param['negativo']) && $param['negativo'] === true) ? true : false;
					$mascara = '###.###.###.###.##0';
					$decimais = 0;
					$estilo = empty($estilo) ? "text-align: right" : '';
					break;
				case 'V':
					//$valor = round($valor,2);
					$valor = empty($valor) ? 0 : $valor;
					$valor = number_format($valor, 2);
					//$maskmoney = true;
					$maskmoney = (isset($param['negativo']) && $param['negativo'] === true) ? true : false;
					$mascara = '###.###.###.###.##0,00';
					$decimais = 2;
					$reverse = true;
					$estilo = empty($estilo) ? "text-align: right" : '';
					break;
				case 'V4':
					//$valor = round($valor,4);
					$valor = empty($valor) ? 0 : $valor;
					$valor = number_format($valor, 4);
					$maskmoney = (isset($param['negativo']) && $param['negativo'] === true) ? true : false;
					$decimais = 4;
					$estilo = empty($estilo) ? "text-align: right" : '';
					break;
				case 'hora':
				case 'H':
					$mascara = '99:99';
					$estilo = empty($estilo) ? "text-align: right" : '';
					$reverse = true;
					break;
				default:
					$mascara = trim($mascara);
					break;
			}
			if($maskmoney){
				$precision = '';
				if($decimais <> 2){
					$precision = ', precision: '.$decimais;
				}
				addPortalJquery("$('#".$id."').maskMoney({allowNegative: true, thousands:'.', selectAllOnFocus: true, decimal:','".$precision."});");
			}else{
				$mascara = "'".$mascara."'";
				$add = [];
				if($reverse){
					$add[] = 'reverse: true';
				}
				$parametro = '';
				if(count($add) > 0){
					$parametro = ',{'.implode(',', $add).'}';
				}
				
				addPortalJquery('$( "#'.$id.'" ).mask('.$mascara.$parametro.');');
			}
		}
		if(!empty($estilo)){
			$style	= 'style = "'.$estilo.'"';
		}
		
		$ret = '<input '.$ativo.' type="text" name="'.$nome.'" value="'.$valor.'" id="'.$id.'" '.$classe.' '.$style.' '.$tamanho.' '.$maxTamanho.' '.$onkeypress.' '.$onkeyup.' '.$onblur.' '.$onchange.' '.$alt.' '.$readonly.' '.$obrigatorio.'>';
		
		if(!empty($etiqueta)){
			$ret = self::formLinha($ret,$etiqueta,$id, $help, $idGroup);
		}
		
		return $ret;
	}
	
	static function formEditor($param, $enable = true){
		$param['id'] = verificaParametro($param, 'id', ajustaID($param['nome']));
		$ret = formbase01::formTextArea($param, $enable);
		
		addPortalJquery("CKEDITOR.replace('".$param['id']."');");
		
		return $ret;
	}
	
	static function formTextArea($param, $enable = true){
		global $nl;
		self::construct();
		
		$nome		= verificaParametro($param, 'nome', '');
		$valor		= verificaParametro($param, 'valor', '');
		$id			= verificaParametro($param, 'id', ajustaID($nome));
		$etiqueta 	= verificaParametro($param, 'etiqueta', '');
		
		$estilo	= verificaParametro($param, 'estilo');
		if(isset($param['width']) && $param['width'] != ''){
			$estilo .= ' width: '.$param['tamanho'].'mm';
		}
		$style		= $estilo != '' ? 'style = "'.$estilo.'"' : "";
		
		$linhas		= verificaParametro($param, 'linhas', 5);
		$linhas		= verificaParametro($param, 'linhasTA', $linhas);
		$classeAdd 	= verificaParametro($param, 'classeadd');
		$tamanhoForm = getAppVar('formBase_tamanhoForm');
		$classeAdd 	= 'form-control '.$classeAdd.$tamanhoForm;
		$classe		= verificaParametro($param, 'classe');
		if(empty($classe)){
			$classe = $classeAdd;
		}
		$classe 	= 'class="'.$classe.'"';
		
		$onkeypress	= isset($param['onkeypress']) && $param['onkeypress'] != '' 	? 'onKeyPress="'.$param['onkeypress'].'"' 	: "";
		$onkeyup	= isset($param['onkeyup']) && $param['onkeyup'] != '' 			? 'onkeyup="'.$param['onkeyup'].'"' 		: "";
		$onchange	= isset($param['onchange']) && $param['onchange'] != '' 		? 'onchange="'.$param['onchange'].'"' 		: "";
		$onblur		= isset($param['onblur']) && $param['onblur'] != '' 			? 'onblur="'.$param['onblur'].'"' 			: "";
		$alt		= isset($param['alt']) && $param['alt'] != ''					? 'alt="'.$param['alt'].'"' 				: "";
		
		$help 		= verificaParametro($param, 'help');
		$layout 	= verificaParametro($param, 'layout');
		
		$ativo = '';
		if(!$enable){$ativo = 'disabled ';}
		$readonly 	= isset($param['readonly']) && $param['readonly'] == true ? 'readonly' : '';
		$obrigatorio= verificaParametro($param, 'obrigatorio', false) == true ? 'required' : '';
		
		$ret = '<textarea '.$ativo.' name="'.$nome.'" rows="'.$linhas.'" id="'.$id.'" '.$classe.' '.$style.' '.$onkeypress.' '.$onkeyup.' '.$onblur.' '.$onchange.' '.$alt.' '.$readonly.' '.$obrigatorio.'>'.$valor.'</textarea>';
		if(!empty($etiqueta)){
			$ret = self::formLinha($ret, $etiqueta, $id, $help);
		}
		
		return $ret;
	}
	
	static function formSenha($param, $enable = true){
		global $nl;
		self::construct();
		$ativo 		= '';
		$nome		= verificaParametro($param, 'nome', '');
		$valor		= verificaParametro($param, 'valor', '');
		//Para não expor a senha o valor fica 15 *
		$valor		= '';
		$id			= verificaParametro($param, 'id', ajustaID($nome));
		$etiqueta 	= verificaParametro($param, 'etiqueta', '');
		$help 		= verificaParametro($param, 'help');
		
		$estilo	= verificaParametro($param, 'estilo', '');
		$style		= $estilo != '' ? 'style = "'.$estilo.'"' : "";
		
		$classeAdd 	= verificaParametro($param, 'classeadd');
		$tamanhoForm = getAppVar('formBase_tamanhoForm');
		$classeAdd 	= 'form-control '.$classeAdd.$tamanhoForm;
		$classe		= verificaParametro($param, 'classe');
		if(empty($classe)){
			$classe = $classeAdd;
		}
		$classe 	= 'class="'.$classe.'"';
		$tamanho 	= isset($param['tamanho']) 		? 'size="'.$param['tamanho'].'"' : "";
		$maxTamanho = isset($param['maxtamanho']) 	? 'maxLength="'.$param['maxtamanho'].'"' : "";
		$onkeypress	= isset($param['onkeypress']) 	? 'onKeyPress="'.$param['onkeypress'].'"' : "";
		$onkeyup	= isset($param['onkeyup']) 		? 'onkeyup="'.$param['onkeyup'].'"' : "";
		$onchange	= isset($param['onchange']) 	? 'onchange="'.$param['onchange'].'"' : "";
		$onblur		= isset($param['onblur']) 		? 'onblur="'.$param['onblur'].'"' : "";
		$alt		= isset($param['alt'])			? 'alt="'.$param['alt'].'"' : "";
		
		if(!$enable){$ativo = 'disabled ';}
		$obrigatorio = verificaParametro($param, 'obrigatorio', false) == true ? 'required' : '';
		
		$ret = '<input '.$ativo.' type="password" name="'.$nome.'" value="'.$valor.'" id="'.$id.'" '.$classe.' '.$style.' '.$tamanho.' '.$maxTamanho.' '.$onkeypress.' '.$onkeyup.' '.$onblur.' '.$onchange.' '.$alt.' '.$obrigatorio.'>';
		if(!empty($etiqueta)){
			$ret = self::formLinha($ret, $etiqueta, $id, $help);
		}
		
		return $ret;
	}
	
	static function formData($param, $enable = true){
		self::construct();
		
		$nome		= verificaParametro($param, 'nome', '');
		$valor		= verificaParametro($param, 'valor', '');
		$id			= verificaParametro($param, 'id', ajustaID($nome));
		$etiqueta 	= verificaParametro($param, 'etiqueta', '');
		$estilo	= verificaParametro($param, 'estilo', 'width: 100px;');
		$style		= $estilo != '' ? 'style = "'.$estilo.'"' : "";
		
		$classeAdd 	= verificaParametro($param, 'classeadd');
		$tamanhoForm = getAppVar('formBase_tamanhoForm');
		$classeAdd 	= 'form-control '.$classeAdd.$tamanhoForm;
		$classe		= verificaParametro($param, 'classe');
		if(empty($classe)){
			$classe = $classeAdd;
		}
		$classe 	= 'class="'.$classe.'"';
		
		$tamanho 	= 'size="10"';
		$maxTamanho = 'maxLength="10"';
		$onkeypress	= isset($param['onkeypress']) && $param['onkeypress'] 	? 'onKeyPress="'.$param['onkeypress'].'"' : "";
		$onkeyup	= isset($param['onkeyup']) && $param['onkeyup'] 		? 'onkeyup="'.$param['onkeyup'].'"' : "";
		$onchange	= isset($param['onchange']) && $param['onchange'] 		? 'onchange="'.$param['onchange'].'"' : "";
		$onblur		= isset($param['onblur']) && $param['onblur'] 			? 'onblur="'.$param['onblur'].'"' : "";
		$alt		= isset($param['alt']) && $param['alt']					? 'alt="'.$param['alt'].'"' : "";
		
		$help 		= verificaParametro($param, 'help');
		$layout 	= verificaParametro($param, 'layout');
		$readonly 	= isset($param['readonly']) && $param['readonly'] == true ? 'readonly' : '';
		$ativo		= $enable ? '' : 'disabled ';
		$obrigatorio= verificaParametro($param, 'obrigatorio', false) == true ? 'required' : '';
		
		addPortalCSS('plugin', 'bootstrap-datepicker/css/bootstrap-datepicker3.min.css', 'I','datepicker');
		addPortalJS('plugin', 'bootstrap-datepicker/js/bootstrap-datepicker.min.js', 'F','datepicker');
		addPortalJS('plugin', 'bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js', 'F','datepicker-BR');
		addPortalJquery('$( "#'.$id.'" ).datepicker({
											    todayBtn: "linked",
											    language: "pt-BR",
											    todayHighlight: true
											});');
		
		$ret = '<input type="text" name="'.$nome.'" value="'.$valor.'" id="'.$id.'" '.$classe.' '.$style.' '.$tamanho.' '.$maxTamanho.' '.$onkeypress.' '.$onchange.' '.$readonly.' '.$ativo.' '.$obrigatorio.'>';//<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
		if(!empty($etiqueta)){
			$ret = self::formLinha($ret,$etiqueta,$id, $help);
		}
		return $ret;
	}
	
	static function formGrupoCheckBox($param){
		$ret = '';
		
		$colunas = $param['colunas'] ?? 1;
		$colunas = $colunas > 12 ? 12 : $colunas;
		$colunas = $colunas < 1  ? 1  : $colunas;
		
		$largura = intdiv(12, $colunas);
//echo "$colunas - largura: $largura <br>\n";
		$larguras = [];
		
		for($i=0;$i<$colunas;$i++){
			$larguras[$i] = $largura;
			$ultimo = $i;
		}
		
		if($colunas * $largura <> 12){
			$larguras[$ultimo] = $largura + (12 - ($colunas * $largura));
		}
//print_r($larguras);
		$combos = $param['combos'] ?? [];
		$quantCombos = count($combos);
		while (($quantCombos % $colunas) <> 0) {
			$combos[] = '';
			$quantCombos = count($combos);
		}
		
		if($quantCombos > 0){
			$coluna = 0;
			$temp = '';
			foreach ($combos as $combo){
//print_r($combo);
				if(is_array($combo)){
					$param = [];
					$param['nome']		= $combo['nome'];
					$param['etiqueta']	= $combo['etiqueta'];
					$param['checked']	= $combo['checked'] ?? false;
					$param['inline']	= true;
					$param['classeadd'] = $combo['classeadd'] ?? '';
					$cb = '&nbsp;&nbsp;&nbsp;'.self::formCheck($param);
//cho $cb."<br>\n";
					$temp .= addDivColuna($larguras[$coluna], $cb);
				}else{
					$temp .= addDivColuna($larguras[$coluna], '');
				}
//echo "$coluna - $colunas <br>\n";
				if(($coluna + 1) == $colunas){
					$ret .= addRow($temp);
					$temp = '';
					$coluna = 0;
				}else{
					$coluna++;
				}
			}
		}
		
		return $ret;
	}
	
	static function formCheck($param, $enable = true){
		global $nl;
		self::construct();
		
		$nome		= verificaParametro($param, 'nome', '');
		$valor		= verificaParametro($param, 'valor', '');
		$id			= verificaParametro($param, 'id', $nome);
		$id 		= ajustaID($id);
		$etiqueta 	= verificaParametro($param, 'etiqueta', '');
		$checked	= verificaParametro($param, 'checked', false);
		$inline     = true;//isset($param['inline']) ? $param['inline'] : true;
		
		$estilo	= verificaParametro($param, 'estilo', '');
		$style		= $estilo != '' ? 'style = "'.$estilo.'"' : "";
		
		$classeAdd 	= verificaParametro($param, 'classeadd');
		//$tamanhoForm = getAppVar('formBase_tamanhoForm');
		//$classeAdd 	= 'form-control '.$classeAdd.$tamanhoForm;
		$classe		= verificaParametro($param, 'classe','form-check-input');
		$inline 	= verificaParametro($param, 'inline');
		//if(!empty($inline)){
		//	$classe .= ' form-check-inline';
		//}
		
		$classe = empty($classeAdd) && empty($classe) ? '' : 'class="'.$classe.' '.$classeAdd.'"';
		
		$onkeypress	= isset($param['onkeypress']) 	? 'onKeyPress="'.$param['onkeypress'].'"' : "";
		$onkeyup	= isset($param['onkeyup']) 		? 'onkeyup="'.$param['onkeyup'].'"' : "";
		$onchange	= isset($param['onchange']) 	? 'onchange="'.$param['onchange'].'"' : "";
		$onblur		= isset($param['onblur']) 		? 'onblur="'.$param['onblur'].'"' : "";
		
		$check = $checked == true ? ' checked="checked"' : "";
		$ativo		= $enable ? '' : 'disabled ';
		
		$value = '';
		if(!empty($valor)){
			$value = 'value="'.$valor.'"';
		}
		
		if($inline){
			$ret = '	<input type="checkbox" id = "'.$id.'" '.$classe.' name="'.$nome.'" '.$check.' '.$value.' '.$ativo.'>'.$nl;
			$ret .= '	<label class="form-check-label" for="'.$id.'">'.$etiqueta.'</label>'.$nl;
		}else{
			$ret  = '<div class="form-check">'.$nl;                                 //
			$ret .= '	<input type="checkbox" id = "'.$id.'" '.$classe.' name="'.$nome.'" '.$check.' '.$value.' '.$ativo.'>'.$nl;
			$ret .= '	<label class="form-check-label" for="'.$id.'">'.$etiqueta.'</label>'.$nl;
			$ret .= '</div>'.$nl;
		}
		
		return $ret;
	}
	
	static function form($param, $conteudo = ''){
		global $nl;
		self::construct();
		
		$ret = '';
		$acao 		= $param['acao'];
		$metodo		= verificaParametro($param, 'metodo','post');
		$nome		= verificaParametro($param, 'nome','formulario');
		$id			= verificaParametro($param, 'id', ajustaID($nome));
		$class		= (isset($param['inline']) && $param['inline'] !== false)? ' class="form-inline"' : '';
		$class		= (isset($param['horizontal']) && $param['horizontal'] !== false) 	? ' class="form-horizontal"' : '';
		$onSubmit	= (isset($param['onsubmit']) && $param['onsubmit'] != '') ? ' onSubmit="return '.$param['onsubmit'].'();"' : '';
		//$acao	=	(isset($param['']) && $param[''] != '') 					? $param[''] : '';
		
		$enctype = (isset($param['enctype']) && $param['enctype'] !== false) 	?  'enctype="multipart/form-data"' : '';
		
		$ret .= '<form '.$class.' id="'.$id.'" action="'.$acao.'" method="'.$metodo.'" name="'.$nome.'" '.$onSubmit.'  role="form" '.$enctype.'>'.$nl;
		if($conteudo != ''){
			$ret .= $conteudo.$nl;
			$ret .= '</form>'.$nl;
		}
		
		$sendFooter = $param['sendFooter'] ?? false;
		
		if($sendFooter){
			$param = [];
			$param['URLcancelar'] = $param['URLcancelar'] ?? getLink().'index';
			$param['IDform'] = $id;
			formbase01::formSendFooter($param);
		}
		
		return $ret;
	}
	
	static function formUploadFile($param){
		global $pagina, $nl;
		self::construct();
		
		$pagina->addScript('plugin', 'jQueryUI/jquery.ui.widget.js','jqueryUI2');
		$pagina->addStyle('plugin','fileupload/fileupload.css','fileupload');
		$pagina->addScript('plugin', 'fileupload/jquery.fileupload.js');
		$pagina->addScript('plugin', 'fileupload/jquery.iframe-transport.js');
		
		$ret = '';
		
		$botaoFim = $param['botaoFim'] ?? '';
		
		$textoSelecione = isset($param['textoSelecione']) && $param['textoSelecione'] != '' ? $param['textoSelecione'] : 'Selecione Arquivos...';
		$textoProcessados = isset($param['textoProcessado']) && $param['textoProcessado'] != '' ? $param['textoProcessado'] : 'Arquivos Processados';
		if(isset($param['url']) && !empty($param['url'])){
			$url = $param['url'];
		}else{
			$operacao = $param['operacao'] ?? 'upload';
			$url = getLinkArquivos($operacao);
		}
		
		if(isset($param['get']) && $param['get'] != '' ){
			if(substr($param['get'], 0, 1) != '&'){
				$url .= '&';
			}
			$url .= $param['get'];
		}
		
		$temp1 = '';
		$temp1 .= '    <span class="btn btn-primary btn-xs fileinput-button">'.$nl;
		$temp1 .= '        <i class="glyphicon glyphicon-plus"></i>'.$nl;
		$temp1 .= '        <span>'.$textoSelecione.'</span>'.$nl;
		$temp1 .= '        <input id="fileupload" type="file" name="files[]" multiple>'.$nl;
		$temp1 .= '    </span>'.$nl;
		$temp1 .= '    <br>'.$nl;
		$temp1 .= '    <br>'.$nl;
		$temp1 .= '    <div id="progress" class="progress">'.$nl;
		$temp1 .= '        <div class="progress-bar progress-bar-primary"></div>'.$nl;
		$temp1 .= '    </div>'.$nl;
		
		$temp1 = addDivColuna(4, $temp1);
		
		$temp2 = '    <h4>'.$textoProcessados.'</h4>'.$nl;
		$temp2 .= '    <div id="arquivosUpload" class="files"></div>'.$nl;
		$temp2 = addDivColuna(8, $temp2);
		
		$ret .= addRow($temp1.$temp2);
		
		
		
		$ret .= "<script>".$nl;
		$ret .= "/*jslint unparam: true */".$nl;
		$ret .= "/*global window, $ */".$nl;
		$ret .= "$(function () {".$nl;
		$ret .= "    'use strict';".$nl;
		//$ret .= "    // Change this to the location of your server-side upload handler:".$nl;
		$ret .= "    var url = '".$url."'".$nl;
		$ret .= "    $('#fileupload').fileupload({".$nl;
		$ret .= "        url: url,".$nl;
		$ret .= "        dataType: 'json',".$nl;
		$ret .= "        done: function (e, data) {".$nl;
		$ret .= "            $.each(data.result.files, function (index, file) {".$nl;
		$ret .= "                $('<p style=\"font-size: 12px;\"/>').html(file.name + '<i class=\"fa fa-check\"></i>').appendTo('#arquivosUpload');".$nl;
		$ret .= "            });".$nl;
		$ret .= "        },".$nl;
		
		//Funciona, mas para cada arquivo enviado gera um alert
		//$ret .= "        success: function (data) {".$nl;
		//$ret .= "        	alert('Enviado!');".$nl;
		//$ret .= "        },".$nl;
		
		$ret .= "        progressall: function (e, data) {".$nl;
		$ret .= "            var progress = parseInt(data.loaded / data.total * 100, 10);".$nl;
		if(!empty($botaoFim)){
			$ret .= "            if(data.loaded == data.total){".$nl;
			$ret .= "            	$('$botaoFim').appendTo('#arquivosUpload');".$nl;
			$ret .= "            }".$nl;
		}
		$ret .= "            $('#progress .progress-bar').css(".$nl;
		$ret .= "                'width',".$nl;
		$ret .= "                progress + '%'".$nl;
		$ret .= "            );".$nl;
		$ret .= "        }".$nl;
		$ret .= "    }).prop('disabled', !$.support.fileInput)".$nl;
		$ret .= "        .parent().addClass($.support.fileInput ? undefined : 'disabled');".$nl;
		$ret .= "});".$nl;
		$ret .= "</script>".$nl;
		
		return $ret;
	}
	
	static function formSend($param = []){
		global $nl;
		self::construct();
		$ret = '';
		$envia['texto']		= verificaParametro($param, 'texto',traducao('S.Ações', 'enviar', getLingua(), 'Enviar'));
		$envia['class'] 	= verificaParametro($param, 'class','');
		$envia['onclick']	= verificaParametro($param, 'onclick','');
		$envia['id ']		= verificaParametro($param, 'id', ajustaID($envia['texto']));
		$envia['type']		= 'submit';
		$envia['cor']		= verificaParametro($param, 'cor','primary');
		$envia['tamanho']	= verificaParametro($param, 'tamanho','pequeno');
		$envia['posicao']	= verificaParametro($param, 'posicao','E');
		
		$botao = formbase01::formBotao($envia).$nl;
		
		$alin = '';
		switch (strtoupper($envia['posicao'])) {
			case 'D':
				$alin = 'align="right"';
				break;
			case 'C':
				$alin = 'align="center"';
				break;
			default:
				$alin = 'align="left"';
				break;
		}
		
		$ret .= '<div class="control-group" '.$alin.'><label class="control-label" for="'.$envia['id '].'">&nbsp;</label>'.$nl;
		$ret .= '<div class="controls">'.$botao.'</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		return $ret;
	}
	
	static function formSendParametros($param = []){
		self::construct();
		$envia = [];
		$envia['texto']		= verificaParametro($param, 'texto','Enviar');
		$envia['class'] 	= verificaParametro($param, 'class','');
		$envia['onclick']	= verificaParametro($param, 'onclick','');
		$envia['id ']		= verificaParametro($param, 'id', ajustaID($envia['texto']));
		$envia['type']		= verificaParametro($param, 'type','submit');
		$envia['cor']		= verificaParametro($param, 'cor',COR_PADRAO_BOTAO_SALVAR);
		$envia['tamanho']	= verificaParametro($param, 'tamanho','');
		
		return $envia;
	}

	static function formFile($param){
		global $nl;
		self::construct();
		$ret = '';
		
		$nome		= verificaParametro($param, 'nome', '');
		$id			= verificaParametro($param, 'id', ajustaID($nome));
		$multi 		= verificaParametro($param, 'multi', false);
		$etiqueta 	= verificaParametro($param, 'etiqueta');
		$texto		= verificaParametro($param, 'texto','Selecionar Arquivo');
		$help 		= verificaParametro($param, 'help');
		
		$multiSel = $multi ? ' multiple ': '';
		
		//$ret = '<input type="file" class="custom-file-input" id="'.$id.'" '.$multiSel.' value="'.$texto.'" name="'.$nome.'">'.$nl;
		$ret = '<input type="file" class="custom-file-input" id="'.$id.'" '.$multiSel.' name="'.$nome.'">'.$nl;
		$ret .= '<label class="custom-file-label" for="'.$id.'">'.$texto.'</label>';
		if(!empty($etiqueta)){
			$ret = self::formLinha($ret,$etiqueta,$id, $help);
		}
		return $ret;
	}
	
	

	
	//------------------------------------------------------------------------------------------------- REVISAR --------------------------------------------
	
	static function formHora($param, $enable = true){
		global $nl;
		self::construct();
		
		$new_param['nome'] 		= isset($param['nome']) 		? $param['nome'] : "";
		$proximo 				= isset($param['proximo']) 		? $param['proximo'] : "";
		$new_param['id']		= isset($param['id']) 			? $param['id'] : self::ajustaID($param['nome']);
		$new_param['valor']		= isset($param['valor']) 		? $param['valor'] : "";
		$new_param['maxtamanho'] 	= 5;
		$new_param['tamanho'] 		= 5;
		$new_param['alt'] 			= "Informe a hora, formato HH:MM hora de 00 a 24 e minuto de 00 a 60.";
		//		$new_param['onkeypress']	= "return(FormataHora('".$new_param['id']."', event));";
		//		$new_param['onkeyup']		= "SaltaCampo('".$new_param['id']."','".self::ajustaID($proximo)."', 5, event);";
		//		$new_param['onblur']		= "calculaHora();";
		
		$portal_jquery[] = '$("#'.$new_param['id'].'").focusout(function(){if(!testaHora($("'.$new_param['id'].'").val())){$("'.$new_param['id'].'").focus();}});';
		
		$new_param['mascara']	= 'hora';
		
		$ret = self::formTexto($new_param, $enable);
		
		return $ret;
	}
	
	/**
	 * Retorna as tags referentes aos dias da semana a serem marcados.
	 *
	 * @param	string	$selecionados	Dias da semana que devem ser já marcados (Ex: "13" -> segunda e terça)
	 * @return	string	Tags do form
	 */
	static function checkSemana($nome, $selecionados = ''){
		$ret = '';
		$etq = [];
		$param = [];
		$etq[] = 'Segunda';
		$etq[] = 'Terça';
		$etq[] = 'Quarta';
		$etq[] = 'Quinta';
		$etq[] = 'Sexta';
		$etq[] = 'Sábado';
		$etq[] = 'Domingo';
		for($i=1;$i<=7;$i++){
			$param['nome'] 		= 'diasSemana['.$i.']';
			$param['etiqueta'] 	= $etq[$i-1];
			$param['id']		= 'formSemana_'.$i;
			$param['checked']	= strpos($selecionados, "$i") === false ? false : true;
			$param['inline'] = true;
			$ret .= self::formCheck($param).'&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		$ret ='<div class="form-check form-check-inline">' . $ret;
		$ret .= '</div>';
		$ret = self::formLinha($ret, $nome);
		return $ret;
	}
	
	//---------------------------------------------------------------------------------------------------- UTEIS ----------------------------
	
	static function formLinha($input, $label='', $id='', $help='', $idGroup = ''){
		global $nl;
		$ret = '';
		$layout = getAppVar('formBase_layout');
		
		$classAdd = '';
		$tamanhoEtiqueta = getAppVar('formBase_tamanhoEtiqueta');
		
		switch ($layout) {
			case 'basico':
				if($label != ''){
					$ret .= '	<label for="'.$id.'" class="col-form-label'.$tamanhoEtiqueta.'">'.$label.'</label>'.$nl;
				}
				$ret .= self::formHelp($help);
				$ret .= '	'.$input.$nl;
				//$ret .= self::formHelp($help);
				break;
			case 'minimo':
				$classAdd = 'row';
				$ret .= '<div class="col-sm-2"></div>'.$nl;
				$ret .= '<div class="col-sm-8">'.$nl;
				if($label != ''){
					$ret .= '	<label class="col-form-label'.$tamanhoEtiqueta.'" for="'.$id.'">'.$label.'</label>'.$nl;
				}
				$ret .= self::formHelp($help);
				$ret .= '	'.$input.$nl;
				$ret .= '</div>'.$nl;
				$ret .= '<div class="col-sm-2"></div>'.$nl;
				break;
			case 'minimoHorizontal':
				$classAdd = 'row';
				$ret .= '<div class="col-sm-2"></div>'.$nl;
				if($label != ''){
					$ret .= '	<label class="col-sm-2 col-form-label'.$tamanhoEtiqueta.'" for="'.$id.'">'.$label.'</label>'.$nl;
				}
				$ret .= '<div class="col-sm-6">'.$nl;
				$ret .= self::formHelp($help);
				$ret .= '	'.$input.$nl;
				$ret .= '</div>'.$nl;
				$ret .= '<div class="col-sm-2"></div>'.$nl;
				break;
			default:
				$classAdd = 'row';
				if($label != ''){
					$ret .= '	<label class="col-sm-2 col-form-label'.$tamanhoEtiqueta.'" for="'.$id.'">'.$label.'</label>'.$nl;
				}
				$ret .= '<div class="col-sm-9">'.$nl;
				$ret .= self::formHelp($help);
				$ret .= '	'.$input.$nl;
				$ret .= '</div>'.$nl;
				$ret .= '<div class="col-sm-1"></div>'.$nl;
				break;
		}
		
		
		
		if(getAppVar('formBase_formGroup')){
			$ret = self::formGroup($ret, $classAdd, $idGroup);
		}
		
		return $ret;
	}
	
	static function formHelp($help){
		global $nl;
		$ret = '';
		
		if(!empty($help)){
			//$ret .= '		<p class="help-block">'.$help.'</p>'.$nl;
			//$ret .= '<div class="input-group-prepend"><div class="input-group-text">@</div></div>'.$nl;
			$ret .= '<i class="fa fa-fw fa-info-circle"  data-toggle="tooltip" data-placement="top" title="'.$help.'"></i>'.$nl;
		}
		
		return $ret;
	}
	
	static function formGroup($conteudo, $classAdd = '', $id = ''){
		global $nl;
		$ret = '';
		$idLinha = '';
		
		switch ($classAdd) {
			case 'check':
				$classForm = ' form-check';
				break;
			case 'row':
				$classForm = ' row';
				break;
			default:
				$classForm = '';
				break;
		}
		
		if(!empty(trim($id))){
			$idLinha = ' id="'.$id.'"';
		}
		
		
		$ret .= '<div class="form-group'.$classForm.'"'.$idLinha.'>'.$nl;
		$ret .= $conteudo;
		$ret .= '</div>'.$nl;
		
		return $ret;
	}
	
	
	//------------------------------------------------------------------------------------------------------------------ Já ajustado para INTRANET 4 -------------------
	static function tabs($param){
		global $nl;
		$ret = '';
		
		$tabs 	= $param['tabs'] ?? [];
		$id		= $param['id'] ?? '';
		
		if(empty($id) || count($tabs) == 0){
			addPortalMensagem('É necessário informar um ID (incormado: '.$id.') e ao menos um conteudo (quant tabs:'.count($tabs).')');
			return '';
		}
		
		$id_tabs = [];
		$ret .= '<ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">'.$nl;
		$primeiro = true;
		foreach ($tabs as $i => $tab){
			$ativo = $primeiro ? 'active' : '';
			$selecionado = $primeiro ? 'true' : 'false';
			$primeiro = false;
			$id_tabs[$i] = 'custom-content-'.$i;
			$ret .= '	<li class="nav-item">'.$nl;
			$ret .= '		<a class="nav-link '.$ativo.'" id="'.$id_tabs[$i].'-tab" data-toggle="pill" href="#'.$id_tabs[$i].'" role="tab" aria-controls="'.$id_tabs[$i].'" aria-selected="'.$selecionado.'">'.$tab['titulo'].'</a>'.$nl;
			$ret .= '	</li>'.$nl;
		}
		$ret .= '</ul>'.$nl;
		
		$ret .= '<div class="tab-content" id="'.$id.'-tabContent">'.$nl;
		$primeiro = true;
		foreach ($tabs as $i => $tab){
			$ativo = $primeiro ? 'show active' : '';
			$primeiro = false;
			$ret .= '	<div class="tab-pane fade '.$ativo.'" id="'.$id_tabs[$i].'" role="tabpanel" aria-labelledby="'.$id_tabs[$i].'-tab">'.$nl;
			$ret .= '		<br>'.$tab['conteudo'].$nl;
			$ret .= '	</div>'.$nl;
		}
		$ret .= '</div>'.$nl;
		
		return $ret;
	}

	
	/**
	 * Monta botões de envio (e cancelamento) dentro do footer, mantendo eles asempre a mostra
	 *
	 * @param array $param
	 */
	static function formSendFooter($parametros){
		global $pagina;
		$ret = '';
		
		$cancela = isset($parametros['URLcancelar']) && !empty($parametros['URLcancelar']) ? true : false;
		$IDform = $parametros['IDform'];
		
		$ret .= addDivColuna(10, '');
		
		$param = [];
		$param['type'] = '';
		$param['onclick'] = "$('#".$IDform."').submit();";
		$param = self::formSendParametros($param);
		unset($param['type']);
		
		$botaoSend = self::formBotao($param);
		
		$botaoCancela = '';
		if($cancela){
			$param = [];
			$param['onclick'] = "setLocation('".$parametros['URLcancelar']."')";
			//$param['tamanho'] = 'pequeno';
			$param['cor'] = COR_PADRAO_BOTAO_CANCELAR;
			$param['texto'] = 'Cancelar';
			$botaoCancela = self::formBotao($param);
		}
		
		$ret .= addDivColuna(1, $botaoSend);
		$ret .= addDivColuna(1, $botaoCancela);
		
		$ret = addRow($ret);
		
		$pagina->addBodyClass('layout-footer-fixed');
		$pagina->setConteudoFooter($ret);
		
	}
	
	static function formSelect($param){
		global $nl;
		self::construct();
		
		$nome		= verificaParametro($param, 'nome', '');
		$valor		= verificaParametro($param, 'valor', '');
		$id			= verificaParametro($param, 'id', ajustaID($nome));
		$etiqueta 	= verificaParametro($param, 'etiqueta', '');
		$title 		= verificaParametro($param, 'title', '');
		
		$estilo	= verificaParametro($param, 'estilo');
		if(isset($param['width']) && $param['width'] != ''){
			$estilo .= ' width: '.$param['width'].'mm';
		}
		$style		= $estilo != '' ? 'style = "'.$estilo.'"' : "";
		
		$classes = [];
		$classes[] = 'form-control';
		$classes[] = getAppVar('formBase_tamanhoSelect');
		$classeAdd 	= $param['classeadd'] ?? [];
		if(is_array($classeAdd)){
			if(count($classeAdd) > 0){
				$classes = array_merge($classes, $classeAdd);
			}
		}elseif(!empty($classeAdd)){
			$classes = array_merge($classes, explode(' ', $classeAdd));
		}
		
		
		$classe		= $param['classe'] ?? '';
		if(!empty($classe)){
			$classeAdd = explode(' ', $classe);
		}
		
		$lista		= $param['lista'] ?? [];
		
		//Procura
		$procura = $param['procura'] ?? 0;
		if(is_array($lista) && count($lista) > 15 && $procura !== false){
			$procura = true;
		}
		$complementoProcura = '';
		if($procura){
			$classes[] = 'selectpicker';
			$complementoProcura = 'liveSearchNormalize="true" data-show-subtext="true" data-live-search="true"';
		}
		
		$classe 	= 'class="'.implode(' ', $classes).'"';
		
		$onkeypress	= (isset($param['onkeypress']) && $param['onkeypress'] != '') 	? ' onkeypress="'.$param['onkeypress'].'".' : "";
		$onchange	= (isset($param['onchange'])   && $param['onchange'] != '') 	? '  onchange="'.$param['onchange'].'"' : "";
		
		$multi		= $param['multi'] ?? 0;
		if($multi <> 0){
			$nome .= '[]';
			$multiple = ' multiple ';
			$size = ' size="'.$multi.'" ';
		}else{
			$multiple = '';
			$size = '';
		}
		
		$help 		= verificaParametro($param, 'help');
		$layout 	= verificaParametro($param, 'layout');
		$obrigatorio= verificaParametro($param, 'obrigatorio', false) == true ? 'required' : '';
		$readonly 	= isset($param['readonly']) && $param['readonly'] == true ? 'disabled="disabled"' : '';
		
		$ret = '<select title="'.$title.'" name="'.$nome.'"  id="'.$id.'" '.$classe.' '.$style.' '.$onkeypress.$onchange.$multiple.$size.' '.$obrigatorio.' '.$readonly.' '.$complementoProcura.'>'.$nl;
		if(is_array($lista) && count($lista) > 0){
			foreach ($lista as $ind => $campo){
				$ret .= '<option value="'.$campo[0].'"';
				if(is_array($valor)){
					if (in_array(trim($campo[0]),$valor))
						$ret .= ' selected';
				}else{
					if (trim($valor) == trim($campo[0]))
						$ret .= ' selected';
				}
				$ret .= ">".$campo[1]."</option>".$nl;
			}
		}
		$ret .= "</select>";
		if(!empty($etiqueta)){
			$ret = self::formLinha($ret, $etiqueta, $id, $help);
		}
		return $ret;
	}
}