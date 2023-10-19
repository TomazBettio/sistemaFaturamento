<?php
if(!defined('TWSiNet'))define('TWSiNet', true);

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0
header('Content-type: text/html; charset=utf-8');

include("./config/config.php");

if($config['error_reporting']){
	ini_set('display_errors',1);
	ini_set('display_startup_erros',1);
	error_reporting(E_ALL);
}

$pagina = new pagina();

//Verifica se foi realizado logout
$pagina->verificaLogout();

//Verifica se est� logado
$pagina->login('login.php');


//Adiciona TAGs META
$pagina->addMeta('charset="'.$config['charset'].'"');
$pagina->addMeta('name="viewport" content="width=device-width, initial-scale=1"');
$pagina->addMeta('charset="Content-Type" content="text/html; charset='.$config['charset'].'"');
$pagina->addMeta('http-equiv="X-UA-Compatible" content="IE=edge"');
$pagina->addMeta('http-equiv="X-Frame-Options" content="DENY"');

//Inicio da pagina
$pagina->addStyle('link', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback', 'I');
$pagina->addStyle('link', 'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css', 'I');
$pagina->addStyle('plugin', 'font-awesome/css/font-awesome.min.css', 'I');
//$pagina->addStyle('plugin', 'fontawesome-free/css/all.min.css', 'I');
//$pagina->addStyle('plugin', 'tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css', 'I');
//$pagina->addStyle('plugin', 'icheck-bootstrap/icheck-bootstrap.min.css', 'I');
//$pagina->addStyle('plugin', 'jqvmap/jqvmap.min.css', 'I');
//$pagina->addStyle('plugin', 'overlayScrollbars/css/OverlayScrollbars.min.css', 'I');
//$pagina->addStyle('plugin', 'daterangepicker/daterangepicker.css', 'I');
//$pagina->addStyle('plugin', 'summernote/summernote-bs4.min.css', 'I'); //WYSIHTML5
$pagina->addStyle('', 'adminlte.min.css', 'I');

//Final da pagina
$pagina->addScript('plugin', 'jquery/jquery.min.js', 'I', 'jquery');
$pagina->addScript('plugin', 'jquery-ui/jquery-ui.min.js', 'I', 'jquery-ui');
$pagina->addScript('plugin', 'bootstrap/js/bootstrap.bundle.min.js', 'I');
$pagina->addScript('plugin', 'mask/jquery.mask.min.js', 'I','mask');
$pagina->addScript('plugin', 'maskmoney/jquery.maskMoney.min.js', 'I','maskmoney');

//$pagina->addScript('plugin', 'chart.js/Chart.min.js', 'F');
//$pagina->addScript('plugin', 'sparklines/sparkline.js', 'F');
//$pagina->addScript('plugin', 'jqvmap/jquery.vmap.min.js', 'F');
//$pagina->addScript('plugin', 'jqvmap/maps/jquery.vmap.usa.js', 'F');
//$pagina->addScript('plugin', 'jquery-knob/jquery.knob.min.js', 'F');
//$pagina->addScript('plugin', 'moment/moment.min.js', 'F');
//$pagina->addScript('plugin', 'daterangepicker/daterangepicker.js', 'F');
//$pagina->addScript('plugin', 'tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js', 'F');
//$pagina->addScript('plugin', 'summernote/summernote-bs4.min.js', 'F'); //WYSIHTML5
//$pagina->addScript('plugin', 'overlayScrollbars/js/jquery.overlayScrollbars.min.js', 'F');
$pagina->addScript('', 'adminlte.js', 'F');
$pagina->addScript('', 'scripts.js', 'F');

echo $pagina;
die();