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
		if(is_array($texto) || is_object($texto)){
			$texto = print_r($texto, true);
		}
		$data = date('ymd - H:i:s ').getUsuario().' ';
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
	 * Grava arquivo de log para exceção SQL do ADOdb
	 *
	 * @author	Alex Cesar
	 * @access	public
	 * @param	string		$arquivo		Nome do arquivo de log
	 * @param	string		$texto			Texto a ser gravado
	 * @return	void
	 *
	 * @version 0.01
	 */
	static function gravaLogErroDB($arquivo, $texto = '')
	{
	    global $config;
	    if(is_array($texto)){
	        $texto = print_r($texto, true);
	    }
	    $data = date('ymd - H:i:s ').getUsuario().' ';
	    $fileName = $config['debugPath'].$arquivo.'.log';
	    
	    if(!defined('ADODB_ERROR_LOG_TYPE')){
	        define('ADODB_ERROR_LOG_TYPE',3);
	    }
	    
	    if(!defined('ADODB_ERROR_LOG_DEST')){
	        define('ADODB_ERROR_LOG_DEST',$fileName); 
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
	
	/**
	 * Inclui a mensagem enviada no log do Apache - Iniciando a mesma com 'Verticais - ' para facilitar a localização e terminando com o programa que chamou a função
	 * 
	 * @param string $mensagem
	 */
	static function logApache($mensagem){
		$trace = debug_backtrace();
		$programa = explode('\\', $trace[0]['file']);
		$total = count($programa);
		error_log('Verticais - '.$mensagem.' - '.$programa[$total-1].'/'.$programa[$total-2].'/'.$programa[$total-3], 0);
	}
}