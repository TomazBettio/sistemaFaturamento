<?php 
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

$nl = "\n";
$config = array();

date_default_timezone_set('America/Sao_Paulo');

//Define se mostra erros do PHP
$config['error_reporting']  = true;
$config['debug']			= true;

/*
 * Configuracoes gerais da pagina
 */
$config['charset'] 			= 'utf-8';
$config['titulo'] 			= 'Intranet 4';
$config['tituloCurto'] 		= 'I4';
$config['tipoTitulo']		= 'texto'; // texto/imagem
$config['logoArquivoMini']	= '';
$config['logoLarguraMini']	= '';
$config['logoAlturaMini']	= '';
$config['logoArquivo']		= '';
$config['logoLargura']		= '';
$config['logoAltura']		= '';
$config['footerPadrao']		= '<strong>Copyright &copy; 2023-'.date('Y').' <a href="#">Tomaz Culau |Labs|</a>.</strong>Todos os direitos reservados<div class="float-right d-none d-sm-inline-block"><b>Versão</b> 4.0-rc</div>';


$config['preloader']	= 'AdminLTELogo.png'; //Indica a imagem que deve ser mostrada ao carregar a página (vazio nao mostra)
$config['preloaderAlt']	= 'Carregando...';
$config['preloaderW']	= '60';
$config['preloaderH']	= '60';

$config['perfil']		= true;
$config['NavbarSearch'] = true;
$config['mensagens']	= false;
$config['notificacoes']	= false;
$config['fullScreen']	= true;
$config['menuProcura']	= true;
$config['botaoLogout']	= true;

/*
 * PRograma inicial caso não seja setado nenhum
 */
$config["appPrincipal"]		= '';
$config["classePrincipal"]	= '';
$config["metodoPrincipal"]	= '';

/*
 * Opções do menu
 */
$config['sidebar_collapse'] = false; //Indica se o menu deve estar recolhido
$config['sidebar_cache'] 	= false; //Indica se deve fazer cache do menu ou desenhar a cada carregamento 

/*
 * Configurações de caminhos
 */
$config['base'] 	= 'C:\xampp\htdocs\sistemaFaturamento/';
$config['baseFW'] 	= 'C:\xampp\htdocs\sistemaFaturamento/twsfw4/';
$config['baseS3'] 	= 'C:\xampp\htdocs\sistemaFAturamento/s3/';

$config['include'] 	= $config['baseFW'].'includes/';
$config['modulos'] 	= $config['baseFW'].'modulos/';

$protocolo = 'http://';
$config['raiz'] 	= $protocolo.$_SERVER['HTTP_HOST'].'/sistemaFaturamento/';
$config['raizS3'] 	= $protocolo.$_SERVER['HTTP_HOST'].'/sistemaFaturamento/s3/';
$config["raizTemp"]  = $protocolo.$_SERVER['HTTP_HOST'].'/sistemaFaturamento/temp/';
$config["tempURL"] = $config['base'] . 'temp/';

$config['imagens'] 	= $config['raizS3'].'imagens/';
$config['css'] 		= $config['raizS3'].'css/';
$config['js'] 		= $config['raizS3'].'js/';
$config['plugins']	= $config['raizS3'].'plugins/';

//Temp fora do www para ser utilizado para upload ou downloads que necessitam de permissão
$config['tempUPD'] 	= $config['base'].'temp/';
$config['debugPath']= $config['base'].'logs/';
$config['tempEmailDir'] = $config['base'].'emails/';


//------------------------------------------------------------
// Define as caracteristicas gerais da pagina
//------------------------------------------------------------
$config['tws_pag'] = [];
$config['tws_pag']['menu'] = true;

/*
 * Forma de login
 */
$config['site']['login'] = 'I';
//$config['site']['loginAlt'] = 'I';

/*
 * Indica qual cliente ou sistema (sera utilizado para buscar as personalizacoes)
 */
$config['appNome']	= 'sdm';
$config['app'] 		= 'tomaz';
$config['cliente'] 	= 'tomaz';

/*
 * Includes Fixos
 */
include_once($config['include'].'adodb5/adodb.inc.php');
include_once($config['include'].'tws.funcoes.php');
include_once($config['include'].'tws.funcoes_html.php');

include_once($config["include"].'tws.cli'.$config['cliente'].'.php');

/*
 * Configuracoes do banco de dados - Intranet
 */
$config['db_banco'] 	= 'pdo';
$config['db_server'] 	= 'host=localhost';
$config['db_database'] 	= 'intranet4';
$config['db_usuario'] 	= 'root';
$config['db_senha'] 	= '';

$db = ADOnewConnection($config['db_banco']);

$dsnString= $config['db_server'].';dbname='.$config['db_database'].';charset=utf8';
$db->connect('mysql:' . $dsnString,$config['db_usuario'],$config['db_senha']);
	// $db->debug = true;

//------------------------------------------------------------
// Email
//------------------------------------------------------------
//Tipo de envio de email: SES = Amazon, MAIL = fun��o MAIL do PHP, SMTP = SMTP
$config['email'] = 'SMTP';
/*/
$config['smtp']['servidor'] = 'email-smtp.us-east-1.amazonaws.com';
$config['smtp']['porta'] = 587;
$config['smtp']['SMTPAuth'] = true;
$config['smtp']['usuario'] = 'AKIAYR3G53PF23RUPWIY'; //tws-suporte
$config['smtp']['senha'] = 'BB1qEUIVz4Vn9ZYSeO9kiM7MqrA2/9/BdfahPXWLTMiX';
$config['smtp']['secure'] = 'tls';
/*/
$config['smtp']['servidor'] = 'correio.marpa.com.br';
$config['smtp']['porta'] = 587;
$config['smtp']['SMTPAuth'] = true;
$config['smtp']['usuario'] = 'sistema@marpa.com.br'; //tws-suporte
// $config['smtp']['senha'] = 'Mar2$!sis@2022#'; // Caso precise ser usado, descomentar esta linha
$config['smtp']['emissorPadrao'] = 'sistema@marpa.com.br';
$config['smtp']['nomeEmissorPadrao'] = 'Sistema Marpa';
// $config['smtp']['secure'] = 'tls';


//$config['smtp']['nomeEmissorPadrao'] = 'Suporte TWS';
//$config['smtp']['emissorPadrao'] = 'suporte@thielws.com.br';

session_start();
if (!isset( $_SESSION['app'] ) || isset($_GET['logout'])) {
	$_SESSION['app'] 	= new app();
}

$app 	=& $_SESSION['app'];
$collapse = getAppVar('sidebar_collapse');
if(!is_null($collapse)){
	$config['sidebar_collapse'] = $collapse;
}

define('COR_PADRAO_BOTOES', 'primary');
define('COR_PADRAO_BOTAO_SALVAR', 'primary');
define('COR_PADRAO_BOTAO_SELECIONAR', 'success');
define('COR_PADRAO_BOTAO_CANCELAR', 'warning');
define('COR_PADRAO_BOTAO_EXCLUIR', 'danger');

//formFiltro
define('FORMFILTRO_COR', 'primary');
define('FORMFILTRO_BT_ENVIAR', 'Filtrar');
define('FORMFILTRO_BT_COR', 'primary');
define('FORMFILTRO_BT_POS', 'L');
define('FORMFILTRO_TITULO', 'Filtro');

//Cards
define('CARD_OUTLINE', true);	// Indica se o titulo dos cards deve ser pintado ou somente a linha superior


//----------------------------------- Especifico Cliente ----------------------------------------------------
// $config['pathUpdInsumos'] = 'C:\xampp\htdocs\testes\sped_mgt/';
// $config['linkInsumos'] = 'http://localhost/intranet4/temp/sped_mgt/';
// As duas linhas estão comentadas pois não sei para o que servem
