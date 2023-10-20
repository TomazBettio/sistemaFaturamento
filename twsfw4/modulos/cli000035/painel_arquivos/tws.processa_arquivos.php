<?php
/*
 * Data Criacao: 10/10/2023
 *
 * Autor: Verticais - Thiel
 *
 * Descricao: Processa os arquivos recebidos por upload e movimenta para a pasta correta
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class processa_arquivos
{
	var $funcoes_publicas = array(
		'index'             => true,
	);

	// cnpj
	private $_cnpj;

	// contrato
	private $_contrato;
	
	// contrato Original
	private $_contrato_original;

	// diretório
	private $_path;
	
	//nome arquivo log
	private $_arquivo_log;
	
	//Local do arquivo de log
	private $_path_log;
	
	//Diretorio de Upload
	private $_upload_path;

	function __construct()
	{
		global $config;
		set_time_limit(0);
		
		$this->_upload_path = $config['uploadPortalArquivo'];
		$this->_arquivo_log = 'unzip';
	}

	public function index()
	{
	}

	public function schedule()
	{
		$diretorios = [];
		$dir = dir($this->_upload_path);
		$processados = 0;
		$maximo = 100;
		while($arquivo = $dir->read()){
			if($arquivo != '.' && $arquivo != '..' && $processados < $maximo){
				$temp = $this->_upload_path.$arquivo;
				if(is_dir($temp)){
					log::gravaLog('upload_diretorios', 'Processando diretorio: '. $temp);
					$diretorios[$arquivo] = $temp.DIRECTORY_SEPARATOR;
					$processados++;
				}
			}
		}
		
		$this->descompactaArquivos($diretorios);
		$this->movimentaArquivosUpload($diretorios);
		
echo "<br>\nFinalizado<br>\n";
//print_r($diretorios);
	}
	
	private function descompactaArquivos($diretorios){
		if(count($diretorios) > 0){
			foreach ($diretorios as $dir){
				if(is_dir($dir)){
					log::gravaLog('upload_diretorios', 'Descompactando: '. $dir);
					$this->extrai_zip($dir);
				}
			}
		}
	}
	
	private function movimentaArquivosUpload($diretorios){
		if(count($diretorios) > 0){
			foreach ($diretorios as $cnpj => $dir){
				if(is_dir($dir)){
					$contrato = $this->getContrato($cnpj);
					
					
					log::gravaLog('upload_diretorios', 'Movendo os arquivos para o destino: '. $dir);
					$quant = $this->movimentaArquivos($cnpj, $dir, $contrato);
					$this->contaArquivos($quant);
					
					//Separa "outros" arquivos
					$this->moveOutrosArquivos();
					
					$resultados = $this->getArquivos($this->_path . 'recebidos', true);
					if (count($resultados) > 0) {
						$this->geraZIP($resultados, 'recebidos');
					}
					$outros = $this->getArquivos($this->_path . 'outros', true);
					if(count($outros) > 0){
						$this->geraZIP($outros, 'outros');
					}
				}
			}
		}
	}
	
	private function contaArquivos($quant){
		$sql = "UPDATE painel_arquivos SET arq_quant = arq_quant + $quant  WHERE cnpj = '".$this->_cnpj."' AND contrato = '".$this->_contrato_original."'";
		log::gravaLog('upload_diretorios', 'Ajustando quantidade - '.$sql);
		query($sql);
	}
	
	private function movimentaArquivos($cnpj, $dir, $contrato){
		$recebidos = $this->_path.'recebidos'.DIRECTORY_SEPARATOR;
		log::gravaLog('upload_diretorios', 'Movendo os arquivos de: '.$dir.' para: '.$recebidos);
		$quant = $this->moveArqivosParaPastaRecebidos($dir, $recebidos);
echo "<br>$cnpj - $contrato - Quant: $quant <br>\n";
		log::gravaLog('upload_diretorios', 'Arquivos movidos: '.$quant);
		if($quant > 0){
			$this->abreContrato();
		}
		
		return $quant;
	}
	
	private function abreContrato(){
		$sql = "UPDATE painel_arquivos SET finalizado = 'N'  WHERE cnpj = '".$this->_cnpj."' AND contrato = '".$this->_contrato_original."'";
		log::gravaLog('upload_diretorios', 'Abrindo contrato - '.$sql);
		query($sql);
	}
	
	private function getContrato($cnpj){
		global $config;
		$ret = '';
		
		$sql = "SELECT * FROM painel_arquivos WHERE cnpj = '$cnpj' ORDER BY dt_inc DESC";
echo "SQL: $sql <br>\n";
		$dados = query($sql);
		 
		if(is_array($dados) && count($dados) > 0){
			$this->_cnpj = $cnpj;
			$this->_contrato = str_replace('/', '-', $dados[0]['contrato']);
			$this->_contrato_original = $dados[0]['contrato'];
			$this->_status = $dados[0]['status'];
			$this->criaPasta($config['pathPortalArquivo']);
			$this->_path = $config['pathPortalArquivo'] . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . DIRECTORY_SEPARATOR;
			$ret = $this->_contrato;
		 }
		 
		 return $ret;
	}
	
	private function extrai_zip($path)
	{
		$dir = glob($path . '*', GLOB_ONLYDIR);
		$zip = glob($path . '*.zip');
		$rar = glob($path . '*.rar');
		
		while (count($dir) > 0 || count($zip) > 0 || count($rar) > 0) {
			if(count($dir) > 0 ){
				//Se existe subdiretorios move todos os arquivos para o diretorio /recebidos/
				$this->moveArqivosParaPastaRecebidos($path, $path);
			}
			$zip = glob($path . '*.zip');
			$rar = glob($path . '*.rar');
			
			foreach ($rar as $folder) {
				Unzipper::extractRarArchive($folder, $path);
				log::gravaLog($this->_arquivo_log, 'RAR descompactado - nome: ' . $folder.' - path: ' . $path);
				
				unlink($folder);
			}
			foreach ($zip as $folder) {
				$zip = new ZipArchive;
				$res = $zip->open($folder);
				if ($res === TRUE) {
					$zip->extractTo($path);
					log::gravaLog($this->_arquivo_log, 'ZIP extraido - Arquivo: ' . $folder . ' extraído com sucesso!');
					$zip->close();
					unlink($folder);
				}
			}
			
			$zip = glob($path . '*.zip');
			$rar = glob($path . '*.rar');
			$dir = glob($path . '*', GLOB_ONLYDIR);
		}
	}
	
	function moveArqivosParaPastaRecebidos($pasta, $recebidos) {
		$ret = 0;
		if (!is_dir($pasta)) {
			return $ret;
		}
		
		log::gravaLog($this->_arquivo_log,"Analisando pasta: $pasta");
		$files = [];
		$diretorio = dir($pasta);
		while($arquivo = $diretorio->read()){
			if($arquivo != '.' && $arquivo != '..'){
				$files[] = $pasta.DIRECTORY_SEPARATOR.$arquivo;
			}
		}
		$diretorio->close();
		
		if($pasta == $recebidos){
			foreach ($files as $key => $file){
				if(!is_dir($file)){
					unset($files[$key]);
				}
			}
		}
		
		if(count($files) > 0){
			foreach ($files as $file) {
				if (is_file($file)) {
					$nomeArq = basename($file);
					if(substr($nomeArq, 0, 1) == '.'){
						$nomeArq = substr($nomeArq, 1);
					}
					if(is_file($recebidos.DIRECTORY_SEPARATOR.$nomeArq)){
						//Se já existir arquivo com o mesmo nome exclui
						unlink($file);
						log::gravaLog($this->_arquivo_log,"Arquivo excluido: $file");
					}else{
						$newPath = $recebidos.DIRECTORY_SEPARATOR.$nomeArq;
						rename($file, $newPath);
						$ret++;
					}
				} else {
					$this->moveArqivosParaPastaRecebidos($file, $recebidos);
				}
			}
			if ($pasta != $recebidos) {
				if(is_dir($pasta)){
					if(!rmdir($pasta)){
						//Se por algum motivo não conseguiu excluir a pasta, força o processo
						$this->apagaDiretorio($pasta);
					}
				}
			}
		}else{
			if($pasta != $recebidos && is_dir($pasta)){
				if(!rmdir($pasta)){
					//Se por algum motivo não conseguiu excluir a pasta, força o processo
					$this->apagaDiretorio($pasta);
				}
			}
		}
		
		return $ret;
	}
	
	private function apagaDiretorio($dir){
		if(is_dir($dir)){
			$iterator     = new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS);
			$rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
			
			foreach($rec_iterator as $file){
				if($file->getFilename() != 'memoria_calculo'){
					$file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
				}
			}
			
			rmdir($dir);
		}
	}
	
	private function criaPasta($path)
	{
		global $config;
		if (!file_exists($path . $this->_cnpj)) {
			mkdir($path . $this->_cnpj, 0777, true);
			chMod($path . $this->_cnpj, 0777);
		}
		if (!file_exists($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato)) {
			mkdir($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato, 0777, true);
			chMod($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato, 0777);
		}
		if (!file_exists($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/recebidos')) {
			mkdir($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/recebidos', 0777, true);
			chMod($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/recebidos', 0777);
		}
		if (!file_exists($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/outros')) {
			mkdir($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/outros', 0777, true);
			chMod($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/outros', 0777);
		}
		if (!file_exists($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/zip')) {
			mkdir($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/zip', 0777, true);
			chMod($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/zip', 0777);
		}
		if (!file_exists($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/log')) {
			mkdir($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/log', 0777, true);
			chMod($path . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . '/log', 0777);
		}
		
		//Cria pastas para upload
		if (!file_exists($config['uploadPortalArquivo'] . $this->_cnpj)) {
			mkdir($config['uploadPortalArquivo'] . $this->_cnpj, 0777, true);
			chMod($config['uploadPortalArquivo'] . $this->_cnpj, 0777);
		}
		
	}
	
	private function getArquivos($dir, $detalhe = false)
	{
		$ret = [];
		log::gravaLog($this->_arquivo_log, "getArquivos: $dir");
		if (is_dir($dir)) {
			$diretorio = dir($dir);
			while ($arquivo = $diretorio->read()) {
				if ($arquivo != '.' && $arquivo != '..') {
					if ($detalhe) {
						$temp = [];
						$temp[0] = $dir . DIRECTORY_SEPARATOR . $arquivo;
						$temp[1] = $arquivo;
						$ret[] = $temp;
					} else {
						$ret[] = $arquivo;
					}
				}
			}
		}
		
		return $ret;
	}
	
	private function geraZIP($arquivos, $nome)
	{
		$arquivo = $this->_path . 'zip' . DIRECTORY_SEPARATOR . $nome . '.zip';
		log::gravaLog($this->_arquivo_log, "gerando: $arquivo");
		
		$zip = new ZipArchive();
		$zip->open($arquivo, ZipArchive::CREATE);
		foreach ($arquivos as $arquivo) {
			$zip->addFile($arquivo[0], $arquivo[1]);
//			log::gravaLog($this->_arquivo_log, "     adicionando: ".$arquivo[0]);
		}
		$zip->close();
		
		foreach ($arquivos as $arquivo) {
			unlink($arquivo[0]);
		}
	}
	
	private function moveOutrosArquivos(){
		$path_recebidos = $this->_path.'recebidos';
		$path_outros = $this->_path.'outros'.DIRECTORY_SEPARATOR;
		
		$files = glob($path_recebidos . '/*');
		foreach ($files as $file) {
			if (is_file($file)) {
				$fileExt = pathinfo($file, PATHINFO_EXTENSION);
				if (!in_array(strtolower($fileExt), ['txt','xml'])) {
					$newPath = $path_outros . basename($file);
					rename($file, $newPath);
				}
			}
		}
		
	}
	//------------------------------------------------------------------------------------------------------------------------------------
/*/




	

	private function moveOutrosArquivos(){
		$path_recebidos = $this->_path.'recebidos';
		$path_outros = $this->_path.'outros'.DIRECTORY_SEPARATOR;
		
		$files = glob($path_recebidos . '/*');
		foreach ($files as $file) {
			if (is_file($file)) {
				$fileExt = pathinfo($file, PATHINFO_EXTENSION);
				if (!in_array(strtolower($fileExt), ['txt','xml'])) {
					$newPath = $path_outros . basename($file);
					rename($file, $newPath);
				}
			}
		}
		
	}
	
	private function verifica_quant_arquivos($dir){
		$ret = 0;
		if(is_dir($dir)){
			$files = scandir($dir);
			$count = 0;
			foreach ($files as $file) {
				if (is_file($dir . $file)) {
					$count++;
				}
			}
			if($count > 0){
				$sql = "UPDATE painel_arquivos SET arq_quant = $count WHERE usuario = '".$this->_cnpj."' AND contrato = '".$this->_contrato_original."'";
				query($sql);
				$ret = $count;
			}else{
				$sql = "SELECT arq_quant FROM painel_arquivos  WHERE usuario = '".$this->_cnpj."' AND contrato = '".$this->_contrato_original."'";
				$rows = query($sql);
				if(isset($rows[0]['arq_quant'])){
					$ret = $rows[0]['arq_quant'];
				}
				
			}
		}
		
		return $ret;
	}

	private function gravaLog($arquivo, $texto, $quebraLinha = false){
		if(is_array($texto)){
			$texto = print_r($texto, true);
		}
		$data = date('ymd - H:i:s ').getUsuario().' ';
		$fileName = $this->_path_log.$arquivo.'.log';
		if(!$quebraLinha){
			$texto = str_replace("\n"," ",$texto);
			$texto = str_replace("\r"," ",$texto);
		}
		
		$file = fopen($fileName, "a");
		fwrite($file, $data.$texto."\n");
		fclose($file);
	}
	/*/
}
