<?php
/*
* Data Criação: 12/11/2014 - 09:59:48
* Autor: Thiel
*
* Arquivo: class.pedidos5000.inc.php
* 
* 
* Alterções:
*            04/01/2019 - Emanuel - Migração para intranet2  
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class pedidos5000{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';

	//Valor do corte de pedido
	var $_valor;
	
	//Indica se é teste
	private $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_valor = 10000;
	}			
	
	function index(){
	}
	
	function schedule($param){
		log::gravaLog('Pedidos5000', 'Executado');
		$dados = $this->getDados();
		if(count($dados) > 0){
			foreach ($dados as $dado){
				$tab = new tabela_gmail01();
				$tab->abreTabela(800);
				$tab->addTitulo('Pedido Bloqueado com valor superior a R$ '.number_format($this->_valor, 2, ',', '.'));
		
				$tab->abreTR();
					$tab->abreTD('<strong>Pedido:</strong>',6,'direita');
					$tab->abreTD($dado['ped'],6);
				$tab->fechaTR();
				
				$tab->abreTR();
					$tab->abreTD('<strong>Data:</strong>',6,'direita');
					$tab->abreTD($dado['data'],6);
				$tab->fechaTR();

				$tab->abreTR();
					$tab->abreTD('<strong>Cliente</strong>',6,'direita');
					$tab->abreTD($dado['cod'].' - '.$dado['cliente'],6);
				$tab->fechaTR();

				$tab->abreTR();
					$tab->abreTD('<strong>Valor:</strong>',6,'direita');
					$tab->abreTD($dado['valor'],6);
				$tab->fechaTR();

				$tab->abreTR();
					$tab->abreTD('<strong>OBS:</strong>',6,'direita');
					$tab->abreTD($dado['obs'],6);
				$tab->fechaTR();

				$tab->abreTR();
					$tab->abreTD('<strong>Quant. Volumes</strong>',6,'direita');
					$tab->abreTD($dado['volumes'],6);
				$tab->fechaTR();

				$tab->abreTR();
					$tab->abreTD('<strong>Peso</strong>',6,'direita');
					$tab->abreTD($dado['peso'],6);
				$tab->fechaTR();

				$tab->abreTR();
					$tab->abreTD('<strong>Volume</strong>',6,'direita');
					$tab->abreTD($dado['volume'],6);
				$tab->fechaTR();

				$tab->abreTR();
					$tab->abreTD('<strong>Motivo:</strong>',6,'direita');
					$tab->abreTD($dado['motivo'],6);
				$tab->fechaTR();
				
				$tab->abreTR();
					$tab->abreTD('<strong>Praca:</strong>',6,'direita');
					$tab->abreTD($dado['codpraca'].' - '.$dado['praca'],6);
				$tab->fechaTR();
				
				$tab->abreTR();
					$tab->abreTD('<strong>Transportadora:</strong>',6,'direita');
					$tab->abreTD($dado['codtransp'].' - '.$dado['transp'],6);
				$tab->fechaTR();
				
				$tab->fechaTabela();
				$tab->addBR();
				$tab->termos();
				
				if(!$this->_teste){
					enviaEmail($param, 'Pedido Bloqueado com valor superior a R$ '.number_format($this->_valor, 2, ',', '.'),$tab);
					log::gravaLog('Pedidos5000', 'Pedido:'.$dado['ped'].' - Enviado email para: '.$param);
					enviaEmail('suporte@thielws.com.br', 'Pedido Bloqueado com valor superior a R$ '.number_format($this->_valor, 2, ',', '.'),$tab);
				}else{
					enviaEmail('suporte@thielws.com.br', 'Pedido Bloqueado com valor superior a R$ '.number_format($this->_valor, 2, ',', '.'),$tab);
					log::gravaLog('Pedidos5000', 'Pedido TESTE:'.$dado['ped'].' - Enviado email para: suporte@thielws.com.br');
				}
				
			}
		}
		
	}

	function getDados(){
		$ret = array();
		$dia = date('Ymd');
		$sql = " 
select  pcpedc.numped,
        pcpedc.data,
        pcpedc.codcli,
        pcclient.cliente,
        pcpedc.vltotal,
        pcpedc.obs,
        pcpedc.obs1,
        pcpedc.obs2,
        pcpedc.numvolume,
        pcpedc.motivoposicao,
        pcclient.codpraca,
        pcpraca.praca,
        pcclient.codfornecfrete,
        pcfornec.fornecedor,
        pcpedc.totpeso,
        pcpedc.totvolume
from    pcpedc,
        pcclient,
        pcpraca,
        pcfornec
where posicao = 'B'
    AND PCPEDC.vltotal >= ".$this->_valor."
    AND PCPEDC.dtcancel IS NULL
    and pcpedc.codcli = pcclient.codcli (+)
    and pcclient.codpraca = pcpraca.codpraca (+)
    and pcclient.codfornecfrete = pcfornec.codfornec (+)
   		";
		$rows = query4($sql);
//print_r($rows);
		
		if(count($rows) > 0){
			$i = 0;
			foreach($rows as $row){
				$sql = "SELECT pedido FROM gf_pedidos5000 WHERE dia = $dia AND pedido = ".$row[0];
//echo "$sql <br>\n";
				$verifica = query($sql);
				
				if(is_array($verifica) && count($verifica) == 0){
					$ret[$i]['ped'] 	= $row[0];
					$ret[$i]['data'] 	= datas::dataMS2D($row[1]);
					$ret[$i]['cod'] 	= $row[2];
					$ret[$i]['cliente'] = $row[3];
					$ret[$i]['valor'] 	= number_format($row[4], 2, ',', '.');
					$ret[$i]['obs'] 	= $row[5].' '.$row[6].' '.$row[7];
					$ret[$i]['volumes'] 	= $row[8];
					$ret[$i]['motivo'] 	= $row[9];
					$ret[$i]['codpraca']= $row[10];
					$ret[$i]['praca'] 	= $row[11];
					$ret[$i]['codtransp']= $row[12];
					$ret[$i]['transp'] 	= $row[13];
					$ret[$i]['peso'] 	= $row[14];
					$ret[$i]['volume'] 	= $row[15];
					
					$sql = "INSERT INTO gf_pedidos5000 (pedido, dia) VALUES (".$row[0].", $dia)";
					query($sql);
					
					$i++;
				}
			}
		}
		
		return $ret;	
	}
}