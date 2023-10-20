<?php
/*
 * Data Criacao: 24/03/2021
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 *
 * Alterções:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
date_default_timezone_set('America/Sao_Paulo');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class brasil_bitcoin{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	function __construct(){
		//set_time_limit(0);
	}
	
	function index(){
		
	}
	
	function schedule($param = ''){

		$moedas = ['REAU', 'BTC', 'USDT', 'ETH', 'XRP', 'SOL', 'DOGE', 'MATIC', 'LTC', 'FTM', 'SHIB', 'ADA', 'LINK', 'GALA', 'UNI', 'CHZ', 'MANA', 'BCH', 'SAND', 'LRC', 'CRV',  'APE', 'AXS', 'AAVE', 'STORJ', 'SNX', 'COMP', 'GRT', 'ENS', 'GAL', 'YFI', 'ANKR', 'ZRX', 'BAND', 'ENJ', 'BAT', 'MKR', 'QNT', 'CVX', 'PAXG', 'SXP', 'AMP'];
		$naoMaisNegociadas = ['LUNA',];
		
		
		foreach ($moedas as $moeda){
			$url = 'https://brasilbitcoin.com.br/API/prices/'.$moeda;
			$jsondata = file_get_contents($url);
			$obj = json_decode($jsondata,true);
		
			$compra = $obj['buy'] ?? 0;
			$venda  = $obj['sell'] ?? 0;
		
			if($compra == 0){
				$relacao = 0;
				log::gravaLog("Brasil_BC_zerado", "$moeda - Compra: $compra - Venda: $venda - Relação: $relacao");
			}else{
				$relacao = $venda/$compra;
			}
			
			log::gravaLog("Brasil_BC", "$moeda - Compra: $compra - Venda: $venda - Relação: $relacao");
			
			$this->gravaDados($moeda, $compra, $venda, $relacao);
		
			if($relacao > 1.1){
				$param = [];
				$param['destinatario'] 	= 'alexandre.thiel@gmail.com';
				$param['mensagem'] 		= 'Oportunidade de compra: '.$moeda;
				$param['assunto'] 		= "$moeda - Compra: $compra - Venda: $venda - Relação: $relacao";
				enviaEmail($param);
				log::gravaLog('COMPRAR', "$moeda - Compra: $compra - Venda: $venda - Relação: $relacao");
			}
		}

	}
	
	private function gravaDados($moeda, $compra, $venda, $relacao){
		$campos = [];
		
		$campos['moeda'] = $moeda;
		$campos['compra'] = $compra;
		$campos['venda'] = $venda;
		$campos['relacao'] = $relacao;
		
		$sql = montaSQL($campos, 'crypto_variacoes');
		query($sql);
	}
	
	private function grava($obj, $tabela){
		$campos = [];
		$campos['preco'] = $obj["last"];
		$campos['max'] = $obj["max"];
		$campos['min'] = $obj["min"];
		$campos['compra'] = $obj["buy"];
		$campos['venda'] = $obj["sell"];
		$campos['abertura'] = $obj["open"];
		$campos['volume'] = $obj["vol"];
		$campos['trade'] = $obj["trade"];
		$campos['trades'] = $obj["trades"];
		$campos['vwap'] = $obj["vwap"];
		$campos['money'] = $obj["money"];
		$campos['hora'] = date('Y-m-d H:i:s');
		
		$sql = montaSQL($campos, $tabela);
		query($sql);
	}
	
	private function getDaily($trace = false){
		$json_string = 'https://brasilbitcoin.com.br/api/dailySummary/REAU/'.date('d').'/'.date('n').'/'.date('Y');
		$jsondata = file_get_contents($json_string);
		$obj = json_decode($jsondata,true);

		if($trace){
			print_r($obj);
		}
		
		$campos = [];
		$campos['volume_cripto']= $obj['volume_cripto'];
		$campos['volume_fiat']	= $obj['volume_fiat'];
		$campos['high']			= $obj['high'];
		$campos['low']			= $obj['low'];
		$campos['open']			= $obj['open'];
		$campos['close']		= $obj['close'];
		$campos['trades']		= $obj['trades'];
		$campos['avg_price']	= $obj['avg_price'];
		$campos['data'] 		= date('Y-m-d H:i:s');
		$campos['vol_por_trader'] = round(($campos['volume_fiat']/$campos['trades'] ),2);
		
		$sql = montaSQL($campos, 'brasil_btc');
		query($sql);
		
		/**
		 * curl --location --request GET 'https://brasilbitcoin.com.br/api/dailySummary/BTC/17/9/2019'
		 */
	}
	
	function getMinhasOrdens(){
		$json_string = 'https://brasilbitcoin.com.br/api/my_orders --header \'Authentication: "nlVEHHhL3PbkaCylLbkOZfsu69yM8btq"\'';
		//curl --location --request GET 'https://brasilbitcoin.com.br/api/my_orders' --header 'Authentication: "API_Key"'
		echo "$json_string <br>\n";
		$jsondata = file_get_contents($json_string);
		$obj = json_decode($jsondata,true);
		
		print_r($obj);	
	}
}

