<?php
/*
 * Data Criacao: 28/12/2022
 * Autor: Verticais - Rafael Postal e Vitor Valadas
 *
 * Descricao: Processa o sped e joga para um arquivo intermediário
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class portal_arquivos_lista {
    //cnpj
    private $_cnpj;

    //contrato
    private $_contrato;

    //pasta raiz documentos
    private $_path;

    //tabela
    private $_tabela;

    function __construct($cnpj, $contrato) {
        global $config;

        $this->_cnpj = $cnpj;
        $this->_contrato = $contrato;
        $this->_path = $config['pathPortalArquivo'].$this->_cnpj.DIRECTORY_SEPARATOR.$this->_contrato.DIRECTORY_SEPARATOR;
    }

    public function getListaProcessados($tipo = '') {
    	$ret = '';
    	$dados = [];
    	
    	$path = $this->_path.'zip'.DIRECTORY_SEPARATOR;

    	if(is_dir($path)){
    		$files = glob($path.'*.zip');
    		if(count($files) > 0){
    			foreach ($files as $file) {
    				$arq_nome = basename($file);
    				$key = basename($file,'.zip');
    				
    				$checked = '';
    				$temp = [];
    				$temp['sel']        = '<input name="arquivos['.$key.']" type="checkbox" value="'.$key.'" '.$checked.' id="'.$key.'">';
    				$temp['arquivo']    = $arq_nome;
    				$temp['data']       = date('d/m/Y',filemtime($file));
    				$dados[] = $temp;
//echo "Arquivo: $file <br>\n";
//echo "data: ".date('d/m/Y',filemtime($file))." <br>\n";
    			}
    		}else{
    			addPortalMensagem('Envio de arquivos ainda não foi finalizado!','error');
    		}
    	}else{
    		addPortalMensagem('Envio de arquivos ainda não foi finalizado!','error');
    	}
    	
   	
    	$param = [];
    	$param['info'] = false;
    	$param['filter'] = false;
    	$param['ordenacao'] = false;
    	$param['titulo'] = 'Arquivos recebidos';
    	$this->_tabela = new tabela01($param);
    	
    	$this->montaColunas();
    	$this->_tabela->setDados($dados);
    	
    	$param = array(
    		'texto' => 'Voltar',
    		'onclick' => "setLocation('" . getLink() . "index')",
    	);
    	$this->_tabela->addBotaoTitulo($param);
    	
    	$botao = [];
    	$botao['texto'] = 'Baixar arquivos selecionados';
    	$botao['cor'] = 'Success';
    	$botao["onclick"] = "$('#formItens').submit();";
    	$this->_tabela->addBotaoTitulo($botao);
    	
    	$param = array(
    		'texto' => 'Download', //Texto no botão
    		'link' => getLink() . "download&cnpj=$this->_cnpj&contrato=$this->_contrato&arquivo=", //Link da página para onde o botão manda
    		'coluna' => 'arquivo', //Coluna impressa no final do link
    		'width' => 100, //Tamanho do botão
    		'flag' => '',
    		'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
    		'pos' => 'F',
    	);
    	$this->_tabela->addAcao($param);
    	
    	
    	$param = [];
    	$param['acao'] = getLink() . "download.selecionados&cnpj=$this->_cnpj&contrato=$this->_contrato";
    	$param['id'] = 'formItens';
    	$param['nome'] = 'formItens';
    	
    	$ret = formbase01::form($param, $ret);
    	
    	$ret .= $this->_tabela;
    	
    	return $ret;
    }

    private function montaColunas() {
        //$this->_tabela->addColuna(array( 'campo' => 'sel', 'etiqueta' => '<span class="text-success">Selecionar<span>', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'arquivo', 'etiqueta' => 'Arquivo', 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    public function download($arquivo) {
        $caminho = $this->_path.'zip'.DIRECTORY_SEPARATOR.$arquivo;

        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$arquivo");
        header("Content-Length: " . filesize($caminho));

        $fp = fopen($caminho, 'rb');
        fpassthru($fp);
        fclose($fp);
    }

    public function selecionados() {
        $arquivos_get = $_POST['arquivos'] ?? [];

        if(count($arquivos_get) > 0) {
            $ids = implode(', ', $arquivos_get);
            $sql = "SELECT arquivo, tipo FROM arquivos WHERE id IN ($ids)";
            $rows = query($sql);

            if(is_array($rows) && count($rows) > 0) {
                $arquivos = [];
                foreach($rows as $row) {
                    $temp = [];
                    $temp[0] = $this->_path . $row['tipo'] . DIRECTORY_SEPARATOR . $row['arquivo'];
                    $temp[1] = $row['arquivo'];
                    $arquivos[] = $temp;
                }
                $this->geraZIP($arquivos, 'arquivos');

                $caminho = $this->_path . 'outros' . DIRECTORY_SEPARATOR . 'arquivos.zip';

                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=arquivos.zip");
                header("Content-Length: " . filesize($caminho));

                $fp = fopen($caminho, 'rb');
                fpassthru($fp);
                fclose($fp);

                unlink($caminho);
            }
        }
    }

    private function geraZIP($arquivos, $nome) {
		$arquivo = $this->_path . 'outros' . DIRECTORY_SEPARATOR . $nome . '.zip';
		$zip = new ZipArchive();
		$zip->open($arquivo, ZipArchive::CREATE);
		foreach ($arquivos as $arquivo) {
			$zip->addFile($arquivo[0], $arquivo[1]);
			echo "veio <br>\n";
		}
		$zip->close();

		// foreach ($arquivos as $arquivo) {
		// 	unlink($arquivo[0]);
		// }
	}

    private function getArquivos($dir) {
		$ret = [];

        if(is_dir($dir)) {
            $diretorio = dir($dir);

            // $diretorio = scandir($dir . $this->_cnpj . DIRECTORY_SEPARATOR . $this->_contrato);
            $naoLer = ['erro', 'processados', 'arquivos'];

            while ($arquivo = $diretorio->read()) {
                $ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
                $ext = strtolower($ext);
                if ($arquivo != '.' && $arquivo != '..' && !in_array($arquivo, $naoLer)) {
                    $ret[]['arquivo'] = $arquivo;
                }
            }
        }

		return $ret;
	}
}