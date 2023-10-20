<?php
/*
 * Data Criacao: 15/05/23
 *
 * Autor: Verticais - Thiel
 *
 * Descricao: Realiza a inclusão de mensagem na OBS do pedido. A query é indicada nos parãmetros do schedule - mensagem | where
 *
 * Alterações:
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class altera_obs_pedidos{
    var $_relatorio;
    var $funcoes_publicas = array(
        'index' 		=> true,
    );
    
    // Nome do Programa
    var $_programa = '';
    
    function __construct(){
        set_time_limit(0);
    }
    
    function index(){
    }
    
    function schedule($parametro){
    	$param = explode('|', $parametro);
        
    	if(count($param) == 2){
			$obs = str_replace("'", "´", $param[0]);
			$where = $param[1];
	        //--- Pedidos da Feira
	        $sql = "UPDATE PCPEDC SET OBS2 = SUBSTR('$obs' || OBS2,1,25)  WHERE $where";
	        query4($sql);
	        
	        log::gravaLog('altera_obs_pedidos', 'Executado: '.$sql);
    	}
    }
    
}