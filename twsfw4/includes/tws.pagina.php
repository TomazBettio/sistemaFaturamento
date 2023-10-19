<?php
/*
 * Data Criacao 04/01/2022
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 *
 * Altera��es:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class pagina{
	
	//Estilos
	private  $_style = [];
	
	//JS
	private  $_js = [];
	
	// Mensagens a serem mostradas
	private $_mensagens = [];
	
	//TAGs META
	private $_meta = [];
	
	public $_modulo;
	public $_classe;
	public $_metodo;
	public $_operacao;
	
	//Conteudo proncipal
	private $_principalString;

	// Variavel com JS (a ser declarado dentro de <script language="JavaScript"></script>)
	private  $_javaScript = [];
	
	// Variavel a ser adicinada dentro da funcao inicial do jquery
	private  $_jquery = [];
	
	//Conteudo do Footer
	private $_conteudoFooter;
	
	//Classes adicionais ao footer
	private $_footerClass = [];
	
	//Classes adicionais ao body
	private $_bodyClass = [];
	
	function __construct(){
		global $config;
		
		//$_REQUEST	= stripslashes_deep($_REQUEST);
		//$_POST	= stripslashes_deep($_POST);
		//$_GET		= stripslashes_deep($_GET);
		
		$this->_conteudoFooter = $config['footerPadrao'];
	}
	
	public function __toString(){
		global $tws_pag;
		$ret = '';
		
		
		$this->preparaPagina();
		
		$this->consumirFilaMensagens();
		
		$ret .= $this->geraHTML();
		
		return $ret;
	}
	
	/*
	 * Verifica se foi solicitado logout
	 */
	function verificaLogout(){
		if (isset($_GET['menu']) && $_GET['menu']=='logout'){
			session_unset();
			session_destroy();
			//exit;
		}
	}
	
	/**
	 * Verifica se o usu�rio est� logado
	 * 
	 * @param string $pagina - Arquivo a ser carregado caso n�o esteje logado
	 */
	function login($pagina = 'login.php'){
		global $app;
		
		if(!$app->logado()){
			$login = new login($pagina);
			$ret = $login->loginPagina($pagina);
			if($ret === false){
				include_once $pagina;
				session_unset();
				session_destroy();
				exit;
				
			}
		}
	}
	
	//------------------------------------------------------------------ Adiciona Recursos
	public function addMensagem($mensagem, $cor){
		$temp = [];
		$temp['msg'] 	= $mensagem;
		$temp['cor'] 	= $cor;
		
		$this->_mensagens[] = $temp;
	}
	
	/**
	 * Inclui links de estilo a página
	 * 
	 * @param string $tipo (local de onde vai buscar: 'link' direto, pasta 'plugin', '' pasta S3/css
	 * @param string $link
	 * @param string $local (local onde vai imprimir - Inicio/Final do HTML
	 * @param string $indice
	 */
	public function addStyle($tipo, $link, $local = 'I', $indice = ''){
		$temp = [];
		$temp['tipo'] = $tipo;
		$temp['link'] = $link;
		$local = $local == 'I' ? 'I' : 'F';
		
		if(!empty($indice)){
			$this->_style[$local][$indice] = $temp;
		}else{
			$this->_style[$local][] = $temp;
		}
	}
	
	/**
	 * Inclui links JS a página
	 *
	 * @param string $tipo (local de onde vai buscar: 'link' direto, pasta 'plugin', '' pasta S3/js
	 * @param string $link
	 * @param string $local (local onde vai imprimir - Inicio/Final do HTML
	 * @param string $indice
	 */
	public function addScript($tipo, $link, $local = 'I', $indice = ''){
		$temp = [];
		$temp['tipo'] = $tipo;
		$temp['link'] = $link;
		$local = $local == 'I' ? 'I' : 'F';
		
		if(!empty($indice)){
			$this->_js[$local][$indice] = $temp;
		}else{
			$this->_js[$local][] = $temp;
		}
	}
	
	public function addMeta($conteudo){
		if(!empty($conteudo)){
			$this->_meta[] = $conteudo;
		}
	}
	
	//------------------------------------------------------------------ Uteis
	
	private function preparaPagina(){
		
		
		if(isset($_GET['menu']) && (preg_match('/^[A-Za-z0-9_]+\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+$/',$_GET['menu']) || preg_match('/^[A-Za-z0-9_]+\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+$/',$_GET['menu'])	)){
			@list($this->_modulo,$this->_classe,$this->_metodo,$this->_operacao) = explode('.',$_GET['menu']);;
			if(empty($this->_modulo)  || empty($this->_classe)){
				$this->setMenuPadrao(false);
			}
			if(empty($this->_metodo)){
				$this->_metodo = 'index';
			}
		}else{
			$programaInicial = getUsuario('inicial');
			
			if(!empty($programaInicial)){
				@list($this->_modulo,$this->_classe,$this->_metodo,$this->_operacao) = explode('.',$programaInicial);
			}else{
				$this->setMenuPadrao();
			}
		}
		$programaLink = $this->_modulo.'.'.$this->_classe;
		
//echo "{$this->_modulo},{$this->_classe},{$this->_metodo},{$this->_operacao} <br>\n";
		if($this->verificaPermissaoUsuario($programaLink)){
			$obj =&CreateObject($this->_modulo.'.'.$this->_classe );
			if(is_array($obj->funcoes_publicas) && isset($obj->funcoes_publicas[$this->_metodo])){
				$metodo = $this->_metodo;
				
				$ret = $obj->$metodo();
				
				if(is_array($ret)){
					foreach ($ret as $r){
						$temp = array();
						$temp['elemento'] 	= isset($r['elemento']) ? $r['elemento'] : 'section ';
						$temp['class']		= isset($r['class']) ? $r['class'] : 'content';
						$temp['conteudo']	= isset($r['conteudo']) ? $r['conteudo'] : '';
						
						$this->_principalString[] = $temp;
					}
				}else{
					$this->_principalString = $ret;
				}
			}else{
				log::logAcesso('Programa: '.$programaLink, 4);
				$this->_principalString = "Tentativa de acesso a funcao nao publica ou inexistente<br>";
			}
			
		}else{
			log::logAcesso('Programa: '.$programaLink, 4);
			$this->_principalString = "Tentativa de acesso a funcao nao publica ou inexistente<br>";
		}
	}
	
	private function verificaPermissaoUsuario($programa){
		$ret = false;
		
		$sql = "SELECT programa, perm FROM sys115 WHERE user = '".getUsuario()."' AND programa LIKE '".$programa."%' AND perm = 'S'";
		$rows = query($sql);
		if(isset($rows[0][0])){
			$ret = true;
		}
		
		//Verifica se não é um programa liberado
		if(!$ret){
			$sql = "SELECT programa, perm FROM sys116 WHERE programa LIKE '".$programa."%' AND perm = 'S'";
			$rows = query($sql);
			if(isset($rows[0][0])){
				$pos = strrpos($rows[0][0], '.');
				if(substr($rows[0][0], 0,$pos) == $menuLink && $rows[0][1] == 'S'){
					$ret = true;
				}
			}
			
		}
		
		return $ret;
	}
	
	/**
	 * 
	 * @param boolean $metodo - Infica se deve sertar o método também
	 */
	private function setMenuPadrao($metodo = true){
		global $config;
		
		$this->_modulo = $config["appPrincipal"];
		$this->_classe = $config["classePrincipal"];
		if($metodo){
			$this->_metodo = $config["metodoPrincipal"];
		}
	}
	//------------------------------------------------------------------ Gera HTML
	
	private function geraHTML(){
		global $nl, $config;
		$ret = '';
		
		$ret .= '<!DOCTYPE html>'.$nl;
		$ret .= '<html lang="pt-BR">'.$nl;
		$ret .= '<head>'.$nl;
		$ret .= '	<base href="'.$config["raiz"].'"/>'.$nl;
		if(count($this->_meta) > 0){
			foreach ($this->_meta as $meta){
				$ret .= '	<meta '.$meta.'>'.$nl;
			}
		}
		$ret .= '	<title>'.$config['titulo'].'</title>'.$nl;
		
		$ret .= $this->getCSS('I');
		$ret .= $this->getJS('I');
		$ret .= $this->getJquery('I');
		$ret .= $this->getJavaScript('I');
		
		$ret .= '</head>'.$nl;
		$bodyClass = implode(' ', $this->_bodyClass);
		$ret .= '<body class="sidebar-mini layout-fixed text-sm '.$bodyClass.'">'.$nl; //layout-fixed
		$ret .= '	<div class="wrapper">'.$nl;
		
		if(!empty($config['preloader'])){
			//$ret .= '		<div class="preloader">'.$nl;
			$ret .= '		<div class="preloader flex-column justify-content-center align-items-center">'.$nl;
			$ret .= '			<img class="animation__shake" src="'.$config['imagens'].$config['preloader'].'" alt="'.$config['preloaderAlt'].'" height="'.$config['preloaderH'].'" width="'.$config['preloaderW'].'">'.$nl;
			$ret .= '		</div>'.$nl;
			
			//$preloader.children().hide();
		}
		
		$ret .= $this->geraNavbar();
		if($config['tws_pag']['menu']){
			$ret .= $this->geraSidebar();
		}
		$ret .= $this->geraContent($this->_principalString);
		$ret .= $this->geraFooter();
		$ret .= $this->geraControlSidebar();
		
		$ret .= '	</div>'.$nl;

		$ret .= $this->getCSS('F');
		$ret .= $this->getJS('F');
		$ret .= $this->getJquery('F');
		$ret .= $this->getJavaScript('F');
		
		
		if(isset($config['gtag']) && $config['gtag'] != ''){
			$this->geraAnalytics($config['gtag']);
		}
		
		$ret .= '</body>'.$nl;
		$ret .= '</html>'.$nl;

		return $ret;
	}
	
	private function geraNavbar(){
		global $nl, $config;
		$ret = '';
		
		$ret .= '<nav class="main-header navbar navbar-expand navbar-white navbar-light">'.$nl;
		$ret .= '	<ul class="navbar-nav">'.$nl;
		$ret .= '		<li class="nav-item">'.$nl;
		$ret .= '			<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>'.$nl;
		$ret .= '		</li>'.$nl;
//		$ret .= '		<li class="nav-item d-none d-sm-inline-block">'.$nl;
//		$ret .= '			<a href="index3.html" class="nav-link">Home</a>'.$nl;
//		$ret .= '		</li>'.$nl;
//		$ret .= '		<li class="nav-item d-none d-sm-inline-block">'.$nl;
//		$ret .= '			<a href="#" class="nav-link">Contact</a>'.$nl;
//		$ret .= '		</li>'.$nl;
		$ret .= '	</ul>'.$nl;
		$ret .= '	<!-- Right navbar links -->'.$nl;
		$ret .= '	<ul class="navbar-nav ml-auto">'.$nl;
		
		if($config['NavbarSearch']){
			$ret .= $this->geraNavbarSearch();
		}
		if($config['mensagens']){
			$ret .= $this->geraMensagens();
		}
		if($config['notificacoes']){
			$ret .= $this->geraNotificacoes();
		}
		if($config['fullScreen']){
			$ret .= $this->geraFullScreen();
		}
		if($config['botaoLogout']){
			$ret .= $this->geraLogout();
		}
		$ret .= '	</ul>'.$nl;
		$ret .= '</nav>'.$nl;
		
		return $ret;
	}
	
	private function geraSidebar(){
		global $nl, $config, $app;
		$ret = '';
		
		$ret .= '<aside class="main-sidebar sidebar-dark-primary elevation-4">'.$nl;
		
		$ret .= $this->geraTituloSidebar();
		$ret .= '	<div class="sidebar">'.$nl;
		
		if($config['perfil']){
			$ret .= $this->geraPerfilSidebar();
		}
		
		if($config['menuProcura']){
			$ret .= $this->geraSearchSidebar();
		}
		
		$ret .= $app->getMenu();
		
		$ret .= '	</div>'.$nl;
		$ret .= '</aside>'.$nl;
		
		return $ret;
	}
	
	private function geraMenuSidebar(){
		global $nl;
		$ret = '';
		
		$ret .= '	<nav class="mt-2">'.$nl;
		$ret .= '	</nav>'.$nl;
		
		return $ret;
	}
	
	private function geraSearchSidebar(){
		global $nl;
		$ret = '';
		
		$ret .= '	<div class="form-inline">'.$nl;
		$ret .= '		<div class="input-group" data-widget="sidebar-search">'.$nl;
		$ret .= '			<input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">'.$nl;
		$ret .= '			<div class="input-group-append">'.$nl;
		$ret .= '				<button class="btn btn-sidebar">'.$nl;
		$ret .= '					<i class="fa fa-search fa-fw"></i>'.$nl;
		$ret .= '				</button>'.$nl;
		$ret .= '			</div>'.$nl;
		$ret .= '		</div>'.$nl;
		$ret .= '	</div>'.$nl;
		
		return $ret;
	}
	
	private function geraTituloSidebar(){
		global $config, $nl;
		$ret = '';
		/*/
    <a href="index3.html" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">'.$config['titulo'].'</span>
    </a>
		/*/
		$ret .= '<a class="brand-link logo-switch">'.$nl;
		if(isset($config['tipoTitulo']) && $config['tipoTitulo'] == 'imagem'){
			$ret .= '	<img src="'.$config['imagens'].$config['logoArquivoMini'].'" alt="'.$config['tituloCurto'].'" class="brand-image-xl logo-xs">'.$nl;
			$ret .= '	<img src="'.$config['imagens'].$config['logoArquivo'].'" alt="'.$config['titulo'].'" class="brand-image-xs logo-xl" style="left: 12px">'.$nl;
		}else{
			$ret .= '	<span class="brand-image-xl logo-xs">'.$config['tituloCurto'].'</span>'.$nl;
			$ret .= '	<span class="brand-image-xs logo-xl">'.$config['titulo'].'</span>'.$nl;
		}
		$ret .= '</a>'.$nl;
		
		return $ret;
	}
	
	private function geraPerfilSidebar(){
		global $config, $nl;
		$ret = '';
		
      	$ret .= '	<div class="user-panel mt-3 pb-3 mb-3 d-flex">'.$nl;
        $ret .= '		<div class="image">'.$nl;
        $ret .= '  			<img src="'.getUsuario('avatar').'" class="img-circle elevation-2" alt="User Imagem">'.$nl;
        $ret .= '		</div>'.$nl;
        $ret .= '		<div class="info">'.$nl;
        $ret .= '  			<a href="#" class="d-block">'.getUsuario('nome').'</a>'.$nl;
        $ret .= '		</div>'.$nl;
      	$ret .= '	</div>'.$nl;

		return $ret;
	}
	
	private function geraContent($conteudo){
		global $nl;
		$ret = '';
		
		$ret .= '<div class="content-wrapper">'.$nl;
		
		$ret .= '	<div class="content-header">'.$nl;
		$ret .= '		'.$nl;
		$ret .= '	</div>'.$nl;
		
		//$ret .= '	<div class="content-header">'.$nl;
		//$ret .= '		<div class="container-fluid">'.$nl;
		//$ret .= '			<div class="row mb-2">'.$nl;
		//$ret .= '				<div class="col-sm-6">'.$nl;
		//$ret .= '					<h1 class="m-0">Dashboard</h1>'.$nl;
		//$ret .= '				</div>'.$nl;
		//$ret .= '				<div class="col-sm-6">'.$nl;
		//$ret .= '					<ol class="breadcrumb float-sm-right">'.$nl;
		//$ret .= '						<li class="breadcrumb-item"><a href="#">Home</a></li>'.$nl;
		//$ret .= '						<li class="breadcrumb-item active">Dashboard v1</li>'.$nl;
		//$ret .= '					</ol>'.$nl;
		//$ret .= '				</div>'.$nl;
		//$ret .= '			</div>'.$nl;
		//$ret .= '		</div>'.$nl;
		//$ret .= '	</div>'.$nl;
		
		$ret .= '	<section class="content">'.$nl;
		$ret .= '		<div class="container-fluid">'.$nl;
		$ret .= '			'.$conteudo;
		$ret .= '		</div>'.$nl;
		$ret .= '	</section>'.$nl;
		$ret .= '</div>'.$nl;
		
		return $ret;
	}
	
	private function geraFooter(){
		global $nl;
		$ret = '';
		
		if(!empty($this->_conteudoFooter)){
			$class = '';
			if(count($this->_footerClass) > 0){
				$class = implode(' ', $this->_footerClass);
			}
			$ret .= '	<footer class="main-footer '.$class.'">'.$nl;
			$ret .= '		'.$this->_conteudoFooter.$nl;
			$ret .= '	</footer>'.$nl;
		}
		
		return $ret;
	}
	
	private function geraControlSidebar(){
		global $nl;
		$ret = '';
		
		$ret .= '	<aside class="control-sidebar control-sidebar-dark">'.$nl;
		$ret .= '	<!-- Control sidebar content goes here -->'.$nl;
		$ret .= '	</aside>'.$nl;
		
		return $ret;
	}
	
	private function geraNavbarSearch(){
		global $nl;
		$ret = '';
		
		$ret .= '				<!-- Navbar Search -->'.$nl;
		$ret .= '				<li class="nav-item">'.$nl;
		$ret .= '					<a class="nav-link" data-widget="navbar-search" href="#" role="button">'.$nl;
		$ret .= '						<i class="fa fa-search"></i>'.$nl;
		$ret .= '					</a>'.$nl;
		$ret .= '					<div class="navbar-search-block">'.$nl;
		$ret .= '						<form class="form-inline">'.$nl;
		$ret .= '							<div class="input-group input-group-sm">'.$nl;
		$ret .= '								<input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">'.$nl;
		$ret .= '								<div class="input-group-append">'.$nl;
		$ret .= '									<button class="btn btn-navbar" type="submit">'.$nl;
		$ret .= '										<i class="fa fa-search"></i>'.$nl;
		$ret .= '									</button>'.$nl;
		$ret .= '									<button class="btn btn-navbar" type="button" data-widget="navbar-search">'.$nl;
		$ret .= '										<i class="fa fa-times"></i>'.$nl;
		$ret .= '									</button>'.$nl;
		$ret .= '								</div>'.$nl;
		$ret .= '							</div>'.$nl;
		$ret .= '						</form>'.$nl;
		$ret .= '					</div>'.$nl;
		$ret .= '				</li>'.$nl;
		
		return $ret;
	}
	
	private function geraMensagens(){
		global $nl;
		$ret = '';

		$ret .= '		<li class="nav-item dropdown">'.$nl;
		$ret .= '			<a class="nav-link" data-toggle="dropdown" href="#">'.$nl;
		$ret .= '				<i class="far fa-comments"></i>'.$nl;
		$ret .= '				<span class="badge badge-danger navbar-badge">3</span>'.$nl;
		$ret .= '			</a>'.$nl;
		$ret .= '			<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">'.$nl;
		$ret .= '				<a href="#" class="dropdown-item">'.$nl;
		$ret .= '					<!-- Message Start -->'.$nl;
		$ret .= '					<div class="media">'.$nl;
		$ret .= '						<img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">'.$nl;
		$ret .= '						<div class="media-body">'.$nl;
		$ret .= '							<h3 class="dropdown-item-title">'.$nl;
		$ret .= '								Brad Diesel'.$nl;
		$ret .= '								<span class="float-right text-sm text-danger"><i class="fa fa-star"></i></span>'.$nl;
		$ret .= '							</h3>'.$nl;
		$ret .= '							<p class="text-sm">Call me whenever you can...</p>'.$nl;
		$ret .= '							<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>'.$nl;
		$ret .= '						</div>'.$nl;
		$ret .= '					</div>'.$nl;
		$ret .= '				<!-- Message End -->'.$nl;
		$ret .= '				</a>'.$nl;
		$ret .= '				<div class="dropdown-divider"></div>'.$nl;
		$ret .= '				<a href="#" class="dropdown-item">'.$nl;
		$ret .= '					<!-- Message Start -->'.$nl;
		$ret .= '					<div class="media">'.$nl;
		$ret .= '					<img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">'.$nl;
		$ret .= '					<div class="media-body">'.$nl;
		$ret .= '					<h3 class="dropdown-item-title">'.$nl;
		$ret .= '					John Pierce'.$nl;
		$ret .= '					<span class="float-right text-sm text-muted"><i class="fa fa-star"></i></span>'.$nl;
		$ret .= '					</h3>'.$nl;
		$ret .= '					<p class="text-sm">I got your message bro</p>'.$nl;
		$ret .= '					<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>'.$nl;
		$ret .= '					</div>'.$nl;
		$ret .= '					</div>'.$nl;
		$ret .= '					<!-- Message End -->'.$nl;
		$ret .= '				</a>'.$nl;
		$ret .= '				<div class="dropdown-divider"></div>'.$nl;
		$ret .= '				<a href="#" class="dropdown-item">'.$nl;
		$ret .= '					<!-- Message Start -->'.$nl;
		$ret .= '					<div class="media">'.$nl;
		$ret .= '					<img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">'.$nl;
		$ret .= '					<div class="media-body">'.$nl;
		$ret .= '					<h3 class="dropdown-item-title">'.$nl;
		$ret .= '					Nora Silvester'.$nl;
		$ret .= '					<span class="float-right text-sm text-warning"><i class="fa fa-star"></i></span>'.$nl;
		$ret .= '					</h3>'.$nl;
		$ret .= '					<p class="text-sm">The subject goes here</p>'.$nl;
		$ret .= '					<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>'.$nl;
		$ret .= '					</div>'.$nl;
		$ret .= '					</div>'.$nl;
		$ret .= '					<!-- Message End -->'.$nl;
		$ret .= '				</a>'.$nl;
		$ret .= '				<div class="dropdown-divider"></div>'.$nl;
		$ret .= '				<a href="#" class="dropdown-item dropdown-footer">See All Messages</a>'.$nl;
		$ret .= '			</div>'.$nl;
		$ret .= '		</li>'.$nl;
		
		return $ret;
	}
	
	private function geraNotificacoes(){
		global $nl;
		$ret = '';
		
		$ret .= '		<li class="nav-item dropdown">'.$nl;
		$ret .= '			<a class="nav-link" data-toggle="dropdown" href="#">'.$nl;
		$ret .= '				<i class="far fa-bell"></i>'.$nl;
		$ret .= '				<span class="badge badge-warning navbar-badge">15</span>'.$nl;
		$ret .= '			</a>'.$nl;
		$ret .= '			<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">'.$nl;
		$ret .= '				<span class="dropdown-item dropdown-header">15 Notifications</span>'.$nl;
		$ret .= '				<div class="dropdown-divider"></div>'.$nl;
		$ret .= '				<a href="#" class="dropdown-item">'.$nl;
		$ret .= '					<i class="fa fa-envelope mr-2"></i> 4 new messages'.$nl;
		$ret .= '					<span class="float-right text-muted text-sm">3 mins</span>'.$nl;
		$ret .= '				</a>'.$nl;
		$ret .= '				<div class="dropdown-divider"></div>'.$nl;
		$ret .= '				<a href="#" class="dropdown-item">'.$nl;
		$ret .= '					<i class="fa fa-users mr-2"></i> 8 friend requests'.$nl;
		$ret .= '					<span class="float-right text-muted text-sm">12 hours</span>'.$nl;
		$ret .= '				</a>'.$nl;
		$ret .= '				<div class="dropdown-divider"></div>'.$nl;
		$ret .= '				<a href="#" class="dropdown-item">'.$nl;
		$ret .= '					<i class="fa fa-file mr-2"></i> 3 new reports'.$nl;
		$ret .= '					<span class="float-right text-muted text-sm">2 days</span>'.$nl;
		$ret .= '				</a>'.$nl;
		$ret .= '				<div class="dropdown-divider"></div>'.$nl;
		$ret .= '				<a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>'.$nl;
		$ret .= '			</div>'.$nl;
		$ret .= '		</li>'.$nl;

		return $ret;
	}
	
	private function geraFullScreen(){
		global $nl;
		$ret = '';
		
		$ret .= '	<li class="nav-item">'.$nl;
		$ret .= '		<a class="nav-link" data-widget="fullscreen" href="#" role="button">'.$nl;
		$ret .= '		<i class="fa fa-arrows-alt"></i>'.$nl;
		$ret .= '		</a>'.$nl;
		$ret .= '	</li>'.$nl;
		
		return $ret;
	}
	
	private function geraLogout(){
		global $nl;
		$ret = '';
		
		$ret .= '	<li class="nav-item">'.$nl;
		$ret .= '		<a class="nav-link" data-widget="" href="index.php?menu=logout" role="button">'.$nl;
		$ret .= '		<i class="fa fa-power-off "></i>'.$nl;
		$ret .= '		</a>'.$nl;
		$ret .= '	</li>'.$nl;
		
		return $ret;
	}
	
	private function geraAnalytics($gtag){
		$ret = '
				<script async src="https://www.googletagmanager.com/gtag/js?id='.$gtag.'"></script>
				<script>
					  window.dataLayer = window.dataLayer || [];
					  function gtag(){dataLayer.push(arguments);}
					  gtag("js", new Date());
					  gtag("config", "'.$gtag.'");
				</script>
				';
		
		return $ret;
	}
	
	private function getCSS($pos = 'I'){
		global $nl, $config;
		$ret = '';

		if(isset($this->_style[$pos]) && count($this->_style[$pos]) > 0){
			foreach ($this->_style[$pos] as $style){
				$link = '';
				switch ($style['tipo']) {
					case 'link':
						$link = $style['link'];
						break;
					case 'plugin':
						$link = $config['plugins'].$style['link'];
						break;
					default:
						$link = $config['css'].$style['link'];
						break;
				}
				$ret .= '	<link rel="stylesheet" href="'.$link.'">'.$nl; 
			}
		}
		return $ret;
	}
	
	private function getJS($pos = 'I'){
		global $nl, $config;
		$ret = '';
		
		if(isset($this->_js[$pos]) && count($this->_js[$pos]) > 0){
			foreach ($this->_js[$pos] as $js){
				$link = '';
				switch ($js['tipo']) {
					case 'link':
						$link = $js['link'];
						break;
					case 'plugin':
						$link = $config['plugins'].$js['link'];
						break;
					default:
						$link = $config['js'].$js['link'];
						break;
				}
				$ret .= '	<script src="'.$link.'"></script>'.$nl;
			}
		}
		return $ret;
	}
	
	private function getJquery($pos = 'I'){
		global $nl;
		$ret = '';
		
		if(isset($this->_jquery[$pos]) && count($this->_jquery[$pos]) > 0){
			$ret .= '	<script>'.$nl;
			$ret .= '		$(function() {'.$nl;
			foreach ($this->_jquery[$pos] as $item){
				$ret .= '		'.$item.$nl;
			}
			$ret .= '		});'.$nl;
			$ret .= '	</script>'.$nl;
		}
		
		return $ret;
	}
	
	private function getJavaScript($pos = 'I'){
		global $nl;
		$ret = '';
		
		if(isset($this->_javaScript[$pos]) && count($this->_javaScript[$pos]) > 0){
			$ret .= '	<script>'.$nl;
			foreach ($this->_javaScript[$pos] as $item){
				$ret .= '		'.$item.$nl;
			}
			$ret .= '	</script>'.$nl;
		}
		
		return $ret;
	}
	
	
	/**
	 * Imprime alerta - erro-vermelho, info-azul, atencao-amarelo, qualquer outro - verde
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 *
	 * @version 0.01
	 */
	private function mensagemPrint($msg){
		global $nl;
		
		$ret = '';
		switch ($msg['tipo']) {
			case 'erro':
				$class = 'alert-danger';
				break;
			case 'info':
				$class = 'alert-info';
				break;
			case 'atencao':
				$class = 'alert-warning';
				break;
			case 'primario':
				$class = 'alert-primary';
				break;
			default:
				$class = 'alert-success';
				break;
		}
		
		$ret .= '<div class="alert '.$class.' alert-dismissible" role="alert">'.$nl;
		$ret .= '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'.$nl;
		$ret .= '<strong>'.$msg['titulo'].'</strong>&nbsp;'.$msg['msg'].$nl;
		$ret .= '</div>'.$nl;
		
		return $ret;
	}
	
	/**
	 *  Consome a fila de mensagens criada pela função funcoes_html::addPortalMensagem
	 * */
	private function consumirFilaMensagens(){
	    $fila_mensagens = getAppVar('fila_mensagens');
	    if(is_array($fila_mensagens) && count($fila_mensagens) > 0){
	        addPortalCSS('plugin', 'toastr/toastr.min.css', 'I', 'toastr');
	        addPortalJS('plugin', 'toastr/toastr.min.js','I', 'toastr');
	        foreach ($fila_mensagens as $mensagem_atual){
	            $cor = $mensagem_atual['cor'];
	            $mensagem = $mensagem_atual['mensagem'];
	            $cor = !empty($cor) ? $cor : 'success';
	            addPortalJquery('toastr.'.$cor.'("'.$mensagem.'");');
	        }
	    }
	    putAppVar('fila_mensagens', null);
	}
	
	//---------------------------------------------------------------------------------- SET ------------------
	
	public function setConteudoFooter($conteudo = ''){
		$this->_conteudoFooter = $conteudo ?? '';
	}
	
	//---------------------------------------------------------------------------------- ADD ------------------
	public function addBodyClass($class){
		if(!empty($class)){
			$this->_bodyClass[] = $class;
		}
	}
	
	public function addFooterClass($class){
		if(!empty($class)){
			$this->_footerClass[] = $class;
		}
	}
	
	public function addJavascript($string, $posicao = 'I'){
		$posicao = strtoupper($posicao);
		$posicao = $posicao != 'F' ? 'I' : 'F'; 
		$this->_javaScript[$posicao][] = $string;
	}
	
	public 	function addJquery($linha, $posicao = 'I'){
		$this->_jquery[$posicao][] = $linha;
	}
}