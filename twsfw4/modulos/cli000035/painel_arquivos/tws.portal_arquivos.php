<?php
/*
 * Data Criacao: 01/01/2023
 *
 * Autor: Verticais
 *
 * Descricao: Recepção dos arquivos enviados pelos clientes
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class portal_arquivos
{
	var $funcoes_publicas = array(
		'index'             => true,
		'arquivos'          => true,
		'editarStatus'		=> true,
		'listaArquivos'		=> true,
		'download'			=> true,
		'limparArquivos'		=> true,
	);

	// Painel
	private $_painel;

	// cnpj
	private $_cnpj;

	// contrato
	private $_contrato;
	
	// contrato Original
	private $_contrato_original;

	//status cliente
	private $_status;

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
		
		$config['tws_pag']['menu'] = false;

		$usuario = getUsuario();

		$sql = "SELECT * FROM painel_arquivos WHERE cnpj = '$usuario' ORDER BY dt_inc DESC";
		$dados = query($sql);

		$this->_cnpj = $dados[0]['cnpj'];
		$this->_contrato = str_replace('/', '-', $dados[0]['contrato']);
		$this->_contrato_original = $dados[0]['contrato'];
		$this->_status = $dados[0]['status'];

		$this->_path = $config['pathPortalArquivo'] . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato . DIRECTORY_SEPARATOR;
		$this->_upload_path = $config['uploadPortalArquivo'] . $this->_cnpj.DIRECTORY_SEPARATOR; 
		
		$this->_arquivo_log = 'contrato';
		$this->_path_log = $this->_path.'log'.DIRECTORY_SEPARATOR;
		
		$this->incluiJS();
	}

	public function index()
	{
		global $nl;
		$ret = '';
		$html = '';

		$html .= '<div class="row">'.$nl;
		$html .= '<div class="col-lg-6">'.$nl;
		$param = [];
		$param['titulo'] = 'Envie arquivos <span class="text-info">ZIP</span> e/ou <span class="text-info">RAR</span>';
		$param['conteudo'] = $this->getCard1();
		$html .= addCard($param);
		$html .= '</div>'.$nl;

		$html .= '<div class="col-lg-6">'.$nl;
		$param = [];
		$param['titulo'] = 'Enviar arquivos';
		$param['conteudo'] = $this->getCard2();
		$html .= addCard($param);
		$html .= '</div>'.$nl;
		$html .= '</div>'.$nl;
		
		$param = [];
		$param['titulo'] = "Contrato: ".$this->_contrato.' - '.getUsuario('nome');
		$param['conteudo'] = $html;
		$ret = addCard($param);
		
		return $ret;
	}

	public function arquivos()
	{
		$ret = '';

		if (count($_FILES) > 0) {
			if (is_uploaded_file($_FILES['files']['tmp_name'][0])) {
				//get ext of uploaded file
				// $ext = pathinfo($_FILES['files']['name'][0], PATHINFO_EXTENSION);
				//check if file is zip or rar
				$ret = $this->leArquivo(
					$_FILES['files']['tmp_name'][0],
					$_FILES['files']['name'][0],
					$_FILES['files']['error'][0]
				);
			} else {
				echo "Sem arquivos \n";
			}
			$this->salvaArquivos();
			$ret = ['files' => $ret];

			return $ret;
		}
	}

	public function leArquivo($nomeTemp, $nome, $erro)
	{
		global $config;
		$ret = [];

		//Se o diretório não existir, cria
		$this->criaPasta($config['pathPortalArquivo']);

		//ext do nome do arquivo
		$ext = pathinfo($nome, PATHINFO_EXTENSION);
		if (strtolower($ext) == 'zip' || strtolower($ext) == 'rar') {
			move_uploaded_file($nomeTemp, $this->_upload_path.$nome);
			$ret[]['name'] = '<b class="text-success">Arquivo: ' . $nome . ' </b>';
		} else {
			$ret[]['name'] = '<b class="text-danger">Erro: ' . $nome . ' não é um arquivo ZIP ou RAR.</b>';
		}

//		$this->extrai_zip();
//		$this->moveOutrosArquivos();

		return $ret;
	}

	public function download()
	{
		$cnpj = $_GET['cnpj'];
		$contrato = $_GET['contrato'];
		$arquivo = $_GET['arquivo'];
		
		$lista = new portal_arquivos_lista($cnpj, $contrato);
		
		$operacao = getOperacao();
		if ($operacao == 'selecionados') {
			$lista->selecionados();
		} else {
			$lista->download($arquivo);
		}
	}
	
	public function editarStatus()
	{
		$resultados = $this->getArquivos($this->_path . 'recebidos', true);
		if (count($resultados) > 0) {
			$this->geraZIP($resultados, 'recebidos');
		}
		$outros = $this->getArquivos($this->_path . 'outros', true);
		if(count($outros) > 0){
			$this->geraZIP($outros, 'outros');
		}
		
		$sql = "UPDATE painel_arquivos SET status = 1 WHERE cnpj = '$this->_cnpj' AND contrato = '$this->_contrato_original'";
		query($sql);
		
		redireciona(getLink() . "index");
	}
	
	public function limparArquivos()
	{
		$this->gravaLog('contrato', "Limpeza de arquivos");
		
		$this->limparDiretorio('zip');
		$this->limparDiretorio('recebidos');
		$this->limparDiretorio('outros');
		
		$sql = "UPDATE painel_arquivos SET arq_quant = 0 WHERE cnpj = '$this->_cnpj' AND contrato = '$this->_contrato_original'";
		query($sql);
		
		addPortalMensagem('Arquivos excluídos com sucesso!');
		
		redireciona(getLink() . "index");
	}
	
	public function listaArquivos()
	{
		$cnpj = base64_decode($_GET['cnpj']);
		$contrato = base64_decode($_GET['contrato']);
		$tipo = $_GET['tipo'] ?? '';
		
		addPortaljavaScript('function ');
		
		$lista = new portal_arquivos_lista($cnpj, $contrato);
		$ret = $lista->getListaProcessados($tipo);
		
		return $ret;
	}
	
	private function limparDiretorio($dir){
		$dir = $this->_path.$dir;
		if(is_dir($dir)){
			$iterator     = new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS);
			$rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
			
			foreach($rec_iterator as $file){
				$file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
			}
		}
	}
	
	private function getCard1(){
		global $nl;
		$ret = '';
		$card_1 = '';
		
		$processados = $this->verifica_quant_arquivos($this->_path.'recebidos'.DIRECTORY_SEPARATOR);
		
		$param = [];
		$param['cor'] = 'bg-blue';
		$param['valor'] = $processados;
		$param['medida'] = '';
		$param['texto'] = 'Arquivos enviados';
		$param['icone'] = 'fa fa-file-text';

		if ($this->_status == 0) {
			$disabled = '';
			$card_1 .= '<input ' . $disabled . ' onclick="alerta()" type="button" class="btn btn-warning btn-lg btn-block" value="Encerrar Envios">';
			$card_1 .= '<br><br><input ' . $disabled . ' onclick="alerta2()" type="button" class="btn btn-warning btn-lg btn-block" value="Limpar Arquivos Enviados">';
		} else {
			$card_1 .= '<div class="row justify-content-md-center">'.$nl;
			$card_1 .= '	<div class="card text-white bg-info mb-3 col-sm-7" style="max-width: 250rem;">'.$nl;
			$card_1 .= '		<div class="card-body row justify-content-md-center">'.$nl;
			$card_1 .= '			<p class="card-text" style="font-size: 20px; text-align: center;"><strong>Você finalizou os envios <br> Obrigado!</strong></p>'.$nl;
			$card_1 .= '		</div>'.$nl;
			$card_1 .= '	</div>'.$nl;
			$card_1 .= '</div>'.$nl;
		}
		
		
		$ret .= '<div class="row">'.$nl;
		$ret .= '	<div class="col-lg-6">'.$nl;
		$ret .= boxPequeno($param);
		$ret .= '	</div>'.$nl;
		$ret .= '	<div class="col-lg-6">'.$nl;
		$ret .= $card_1;
		$ret .= '	</div>'.$nl;
		$ret .= '</div>'.$nl;
		
		return $ret;
	}
	
	private function getCard2(){
		global $nl;
		$card_2 = '';
		
		$card_2 .= '<div class="row justify-content-md-center">'.$nl;
		$param = [];
		$param['get'] = 'cnpj=' . $this->_cnpj . '&contrato=' . $this->_contrato_original . "&status=$this->_status";
		if($this->_status <> 0){
			$param['ativo'] = false;
		}
		$card_2 .= formbase01::formUploadFile($param);
		$card_2 .= '</div>'.$nl;
		
		return $card_2;
	}
	
	private function salvaArquivos()
	{
		$arquivos['recebidos'] = $this->getArquivos($this->_path . 'recebidos');
		$arquivos['outros'] = $this->getArquivos($this->_path . 'outros');
		$log = $this->_path.'log'.DIRECTORY_SEPARATOR.'arquivos_recebidos.log';
		
		$file = fopen($log, "a");

		foreach ($arquivos as $tipo => $pasta) {
			if (count($pasta) > 0) {
				foreach ($pasta as $arquivo) {
					$texto = date('ymd - H:i:s ')." - $tipo/$arquivo";
					fwrite($file, $texto."\n");
				}
			}
		}
		fclose($file);
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
		$this->gravaLog($this->_arquivo_log, "getArquivos: $dir");
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

	private function extrai_zip()
	{
		$path_recebidos = $this->_path.'recebidos'.DIRECTORY_SEPARATOR;

		$dir = glob($path_recebidos . '*', GLOB_ONLYDIR);
		$zip = glob($path_recebidos . '*.zip');
		$rar = glob($path_recebidos . '*.rar');

		while (count($dir) > 0 || count($zip) > 0 || count($rar) > 0) {
			if(count($dir) > 0 ){
				//Se existe subdiretorios move todos os arquivos para o diretorio /recebidos/
				$this->moveArqivosParaPastaRecebidos($path_recebidos, $path_recebidos);
			}
			$zip = glob($path_recebidos . '*.zip');
			$rar = glob($path_recebidos . '*.rar');

			foreach ($rar as $folder) {
				Unzipper::extractRarArchive($folder, $path_recebidos);
				$this->gravaLog($this->_arquivo_log, 'RAR descompactado - nome: ' . $folder.' - path: ' . $path_recebidos);
				
				unlink($folder);
			}
			foreach ($zip as $folder) {
				$zip = new ZipArchive;
				$res = $zip->open($folder);
				if ($res === TRUE) {
					$zip->extractTo($path_recebidos);
					$this->gravaLog($this->_arquivo_log, 'ZIP extraido - Arquivo: ' . $folder . ' extraído com sucesso!');
					$zip->close();
					unlink($folder);
				}
			}

			$zip = glob($path_recebidos . '*.zip');
			$rar = glob($path_recebidos . '*.rar');
			$dir = glob($path_recebidos . '*', GLOB_ONLYDIR);
		}
	}
	
	function moveArqivosParaPastaRecebidos($pasta, $recebidos) {
		if (!is_dir($pasta)) {
			return;
		}
		
		$this->gravaLog($this->_arquivo_log,"Analisando pasta: $pasta");
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
						$this->gravaLog($this->_arquivo_log,"Arquivo excluido: $file");
					}else{
						$newPath = $recebidos.DIRECTORY_SEPARATOR.$nomeArq;
						rename($file, $newPath);
					}
				} else {
					$this->moveArqivosParaPastaRecebidos($file, $recebidos);
				}
			}
			if ($pasta != $recebidos) {
				if(is_dir($pasta)){
					rmdir($pasta);
				}
			}
		}else{
			if($pasta != $recebidos && is_dir($pasta)){
				rmdir($pasta);
			}
		}
	}

	private function geraZIP($arquivos, $nome)
	{
		$arquivo = $this->_path . 'zip' . DIRECTORY_SEPARATOR . $nome . '.zip';
		$this->gravaLog($this->_arquivo_log, "gerando: $arquivo");

		$zip = new ZipArchive();
		$zip->open($arquivo, ZipArchive::CREATE);
		foreach ($arquivos as $arquivo) {
			$zip->addFile($arquivo[0], $arquivo[1]);
			$this->gravaLog($this->_arquivo_log, "     adicionando: ".$arquivo[0]);
		}
		$zip->close();

		foreach ($arquivos as $arquivo) {
			unlink($arquivo[0]);
		}
	}

	private function getListaArquivos()
	{
		$html = '';

		$sql = "SELECT arquivo, data FROM arquivos WHERE cnpj = $this->_cnpj AND contrato = '$this->_contrato_original' ORDER BY data DESC LIMIT 6";
		$rows = query($sql);

		if (is_array($rows) && count($rows) > 0) {
			$html = '<table class="table">
						<tr>
							<th>Arquivo</th>
							<th>Data</th>
						</tr>';
			foreach ($rows as $row) {
				$html .= '<tr>
							<td>' . $row['arquivo'] . '</td>
							<td>' . Datas::dataS2D($row['data']) . '</td>
						</tr>';
			}
			$html .= '</table>';
		}

		return $html;
	}

	private function incluiJS()
	{
		addPortaljavaScript('function alerta() {
			var r = confirm("Se você continuar, dará inicio ao processo fiscal e não poderá mais enviar arquivos.\n\nDeseja finalizar os envios?");

			if(r) {
				window.location.href = "' . getLink() . "editarStatus&cnpj=" . base64_encode($this->_cnpj) . "&contrato=" . base64_encode($this->_contrato_original) . '";
			}
		}');
		addPortaljavaScript('function alerta2() {
			var r = confirm("Se você continuar todos os arquivos já enviados serão excluídos.\n\nDeseja eliminar os arquivos?");
			
			if(r) {
				window.location.href = "'.getLink()."limparArquivos&cnpj=".base64_encode($this->_cnpj)."&contrato=".base64_encode($this->_contrato_original) . '";
			}
		}');
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
}
