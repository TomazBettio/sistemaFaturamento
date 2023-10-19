<?php
/*
 * Data Criação: 22/03/2022
 * Autor: Alexandre
 *
 * Descrição: Rotinas ajax utilizadas no SDM
 *
 * Alterações: 
 *
 */
if (! defined('TWSiNet') || ! TWSiNet)
	die('Esta nao e uma pagina de entrada valida!');

class sdm_ajax{
	var $funcoes_publicas = array(
			'ajax'          => true,
	);
	public function ajax(){
		$ret = [];
		$operacao = getOperacao();
		
		if($operacao == 'getProjetosCliente'){
			$ret[] = array('valor' => '', 'etiqueta' => '');
			$cliente = getParam($_GET, 'cliente', '');
			
			if($cliente != ''){
				$sql = "select * from sdm_projetos where cliente = '$cliente'";
				$rows = query($sql);
				if(is_array($rows) && count($rows) > 0){
					foreach ($rows as $row){
						$temp = array(
								'valor' => $row['id'],
								'etiqueta' => $row['titulo'],
						);
						$ret[] = $temp;
					}
				}
			}
		}
		
		return json_encode($ret);
	}
}