<?php
if(!defined('TWSiNet'))define('TWSiNet', true);
include("/var/www/admin/config/config.php");

$tipo = getParam($_GET, 'tipo', '');
$id = getParam($_GET,'id','');
$nome = getParam($_GET, 'nome','');
if ($tipo != '' && $id != '' && $nome != '')
{
    $tipo = base64_decode($tipo);
    $id = base64_decode($id);
    $nome = base64_decode($nome);
    switch($tipo)
    {
        case 'C':
            $dir = "/var/www/app/fotos/campanhas/$id/$nome";
            if(is_file($dir))
            {
                //mostra imagem no browser
                header('Content-Type: image');
                header('Content-Length: ' . filesize($dir));
                header('Content-Disposition: inline; filename='.basename($dir));
                echo file_get_contents($dir);
            }
            break;
        case 'F':
            $dir = "/var/www/app/fotos/$id/$nome";
            if(is_file($dir))
            {
                //mostra imagem no browser
                header('Content-Type: image');
                header('Content-Length: ' . filesize($dir));
                header('Content-Disposition: inline; filename='.basename($dir));
                echo file_get_contents($dir);
            }
            break;
        default:
            break;
    }
}

