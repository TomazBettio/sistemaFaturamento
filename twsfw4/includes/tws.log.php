<?php
/*
* Data Criação: 13/03/2013 - 10:08:43
* Autor: Thiel
*
* Descricao:
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class log{
	
	/**
	* Grava arquivo log
	*
	* @author	Alexandre Thiel
	* @access	public
	* @param	string		$arquivo		Nome do arquivo de log
	* @param	string		$texto			Texto a ser gravado
	* @param	int			$criticidade	Indica a criticidade do log, acima de 8 envia email
	* @param	boolean		$quebraLinha	False indica que deve limpar a quebra de linha
	* @return	void
	*
	* @version 0.01
	*/
	static function gravaLog($arquivo,$texto, $criticidade = 0, $quebraLinha = false){
		global $config;
		if($criticidade > 8){
			//envia email TODO: fazer isto direito
		}
		if(is_array($texto)){
			$texto = print_r($texto, true);
		}
		$data = date('ymd - H:i:s ');
		$fileName = $config['debugPath'].$arquivo.'.log';
		if(!$quebraLinha){
			$texto = str_replace("\n"," ",$texto);
			$texto = str_replace("\r"," ",$texto);
		}
	
		$file = fopen($fileName, "a");
		fwrite($file, $data.$texto."\n");
		fclose($file);
	}
	
	/**
	 * Grava na tabela de log de acesso
	 *
	 * @author	Alexandre Thiel
	 * @access	public
	 * @param	string	$msg	Mensagem a ser incluida (não é necessário data)
	 * @param	string	$idLog 	ID do tipo do log (tabela syslogID)
	 * @return	null
	 *
	 * @version 0.01
	 */
	static function logAcesso($mensagem, $idLog = 0){
		$param = explode("?",$_SERVER['REQUEST_URI']);

		$campos = [];
		$campos['usuario'] 		= getUsuario();
		$campos['ip'] 			= $_SERVER["REMOTE_ADDR"];
		$campos['id_log'] 		= $idLog;
		$campos['mensagem'] 	= $mensagem;
		$campos['dia'] 			= date('Ymd');
		$campos['hora'] 		= date('H:i:s');
		$campos['parametros'] 	= isset($param[1]) ? $param[1] : '';
		
		$sql = montaSQL($campos, 'syslog');
		query($sql);

		return true;
	}
	
}