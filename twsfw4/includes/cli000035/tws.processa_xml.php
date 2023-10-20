<?php

/*
 * Data Criacao:
 * Autor:
 *
 * Descricao:
 *
 * Altera��es:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class processa_xml
{
	//Path dos arquivos XMLs
	private $_path;
	
	//Path raiz do cliente-contrato
	private $_path_raiz;

	//Dados
	private $_dados = [];

	//debug
	private $_trace;

	//Existe arquivo
	private $_file_exists;

	//cnpj
	private $_cnpj;

	// ID cliente
	private $_id;

	//Arquivo fe log do contrato
	private $_logContrato;
	
	//Contrato
	private $_contrato;
	
	//Indica se está rodando por schedule
	private $_schedule;
	
	//Dados do contrato
	private $_dadosContrato;
	
	//Data do incio da apuracao
	private $_periodo_ini;
	
	//Data do fim da apuracao
	private $_periodo_fim;
	
	//Notas já processadas
	private $_notas_processadas = [];
	
	public function __construct($cnpj, $contrato, $id, $arquivos_importados, $dados_contrato = [], $schedule = false, $trace = false)
	{
		global $config;
		
		$this->_dadosContrato = $dados_contrato;
		$this->_periodo_ini = $dados_contrato['apura_ini'] ?? '';
		$this->_periodo_fim = $dados_contrato['apura_fim'] ?? '';
		
		if($arquivos_importados == 'N'){
			$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_'.$contrato.DIRECTORY_SEPARATOR.'recebidos'.DIRECTORY_SEPARATOR;
		}else{
			$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR.'zip'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR;
		}
		$this->_path_raiz = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
		$this->_trace = $trace;

		$this->_cnpj = $cnpj;
		$this->_contrato = $contrato;
		$this->_id = $id;

		$this->_file_exists = $this->existeArquivos('xml');
		
		$this->_logContrato = 'monofasico_processa'.DIRECTORY_SEPARATOR.$cnpj . '_' . $contrato;
		log::gravaLog($this->_logContrato, 'Processa XML');
		log::gravaLog($this->_logContrato, $this->_path);
		
		$this->_schedule = $schedule;
	}

	public function getExisteArquivo()
	{
		return $this->_file_exists;
	}

	public function existeArquivos($tipo)
	{
		$ret = false;

		$files = glob($this->_path . '*.' . strtoupper($tipo));

		if (count($files) > 0) {
			//rename extension to lower
			foreach ($files as $file) {
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				$ext = strtolower($ext);
				rename($file, $this->_path . pathinfo($file, PATHINFO_FILENAME) . '.' . $ext);
			}
		}

		$files = glob($this->_path . "*." . $tipo);

		if (count($files) > 0) {
			$ret = true;
		}
		return $ret;
	}

	public function getDados()
	{
		return $this->_dados;
	}

	public function getInformacoes($cnpj)
	{
		$arquivos = $this->getArquivos($this->_path);

		foreach ($arquivos as $arquivo) {
			$this->leituraArquivo($cnpj, $arquivo);
			if (file_exists($this->_path . $arquivo)) {
				rename($this->_path . $arquivo, $this->_path_raiz . 'processados_xml' . DIRECTORY_SEPARATOR . $arquivo);
			}
		}
	}

	private function leituraArquivo($cnpj, $arquivo)
	{
		$files = glob($this->_path . $arquivo);
		$erros = 0;
		$item_seq = 0;
		if (count($files) > 0) {
			foreach ($files as $file) {
				$filename = pathinfo($file, PATHINFO_BASENAME);
				$log_destinatario = '';
				$log_emitente = '';
				$log_operacao = '';
				$processa = true;
				$motivo = '';
				$xml = carregaXMLnota($file, $this->_logContrato);
//print_r($xml);die();
				
				if($xml === false){
					$processa = false;
					$motivo = 'Erro na estrutura XML do arquivo.';
				}
				
				if($processa){
					$xml = $xml->procNFe ?? $xml;
//echo $file."<br>\n";
//print_r($xml);
					$dhEmi = '';
					if(isset($xml->NFe->infNFe->ide) && isset($xml->NFe->infNFe->ide->dhEmi)){
						$dhEmi = $xml->NFe->infNFe->ide->dhEmi;
					}
					$dhEmi = str_replace('-', '', substr($dhEmi, 0, 10));
					
					if(!empty($this->_periodo_ini) && $dhEmi < $this->_periodo_ini){
						$processa = false;
						$motivo = 'Nota fora do período de análise - '.datas::dataS2D($dhEmi);
					}
					
					if(!empty($this->_periodo_fim) && $dhEmi > $this->_periodo_fim){
						$processa = false;
						$motivo = 'Nota fora do período de análise - '.datas::dataS2D($dhEmi);
					}
				}
				
				if($processa){
					$aut = $xml->protNFe->infProt->cStat ?? 0; //$xml->procNFe->protNFe->infProt->cStat;
					$log_emitente		= $xml->NFe->infNFe->emit->CNPJ ?? $xml->NFe->infNFe->emit->CPF;
					$log_destinatario	= $xml->NFe->infNFe->dest->CNPJ ?? $xml->NFe->infNFe->dest->CPF;
					$log_operacao 		= $xml->NFe->infNFe->ide->natOp;
					
					if ($aut <> 100) {
						//Rquivo não foi assinado/autorizado
						$processa = false;
						$motivo = 'Arquivo não autorizado: '.$filename;
					}
				}
				
				if($processa){
					if (substr($log_destinatario, 0, 7) != substr($cnpj, 0, 7)) {
						//Nota de saida ou nota que não é do cliente
						$processa = false;
						if (substr($log_emitente, 0, 7) != substr($cnpj, 0, 7)) {
							//Não é do cliente
							$motivo = 'Erro: CNPJ do cliente não encontrado no arquivo.';
						}else{
							//Nota de saida
							$motivo = 'Erro: Nota fiscal de saída.';
						}
					}
				}
					
				if ($processa) {
					$num_doc = $xml->NFe->infNFe->ide->cNF;
					$nome = str_replace("'", "", $xml->NFe->infNFe->dest->xNome);
					
					if (!$this->verificador($num_doc, $dhEmi)) {
						//Nota já foi processada
						$processa = false;
						$motivo = 'Nota já processada.';
					}
				}
					
				if ($processa) {
					$this->controlador($num_doc, $dhEmi, $nome);

					$cnpj_nf = '';
					$tipo_nf = 0; // Entrada

					$param = [];
					$temp = [];
					$temp['bloco'] 			= '0140';
					$temp['cod_part']		= $num_doc;
					$temp['nome_cliente'] 	= str_replace("'", "", $xml->NFe->infNFe->dest->xNome);
					$temp['cnpj'] 			= $xml->NFe->infNFe->dest->CNPJ; // $cnpj_dest
					$cnpj_nf = $temp['cnpj']; // $cnpj_emit
					$param[] = $temp;
					$this->gravaNotaBloco($param, $cnpj);

					$param = [];
					$temp = [];
					$temp['bloco'] 			= '0150';
					$temp['cod_part']		= $num_doc;
					$temp['nome_cliente'] 	= str_replace("'", "", $xml->NFe->infNFe->emit->xNome);
					$temp['cnpj'] 			= $xml->NFe->infNFe->emit->CNPJ; // $cnpj_emit
					$cnpj_nfor = $temp['cnpj']; // $cnpj_emit
					$param[] = $temp;
					$this->gravaNotaBloco($param, $cnpj); // ok

					$param = [];
					$temp = [];
					$temp['bloco']			= 'C100'; // cep 01
					$temp['tipo_nf']		= $tipo_nf; // sep 02
					$temp['cod_part']		= $num_doc;
					$temp['num_doc']		= $num_doc; // sep 03
					$temp['chave_nfe'] 		= substr($xml->NFe->infNFe['Id'], 3); // sep 09
					$temp['data_emissao'] 	= $dhEmi; // 2022-01-21T08:39:00-03:00   sep 10
					$temp['total_bruto']	= $xml->NFe->infNFe->total->ICMSTot->vNF; // sep 12
					$temp['filial'] = $cnpj_nf;

					$chave_nfe = $temp['chave_nfe'];
					$param[] = $temp;
					$this->gravaNotaBloco($param, $cnpj);
					$this->setPermissoesBanco(substr($temp['data_emissao'], 0, 6));

					$det = $xml->NFe->infNFe->det;
					$cont = 0;
					foreach ($det as $item) {
						$ncm = $item->prod->NCM;
						$cont++;

						$param = [];
						$temp = [];
						$temp['bloco']			= '0200'; // sep 01
						$temp['cod_item'] 		= $item->prod->cProd; // sep 02
						$temp['nome_produto'] 	= str_replace(";", '', $item->prod->xProd); // sep 04
						$temp['cod_ncm'] 		= $ncm; //$item->prod->NCM;   sep 08
						$param[] = $temp;
						$this->gravaNotaBloco($param, $cnpj); // ok

						$aliq_pis = 0;
						$aliq_cofins = 0;
						if (isset($item->PIS->PISOutr->pPIS)) {
							$aliq_pis = !empty($item->PIS->PISOutr->pPIS) ?? 0;
							$aliq_cofins = !empty($item->COFINS->COFINSOutr->pCOFINS) ?? 0;
						} elseif (isset($item->PIS->PISAliq->pPIS)) {
							$aliq_pis = !empty($item->PIS->PISAliq->pPIS) ?? 0;
							$aliq_cofins = !empty($item->COFINS->COFINSAliq->pCOFINS) ?? 0;
						}

						$cst = $item->imposto->PIS->PISNT->CST ?? 0;

						if ($cst == 0) {
							$cst = $item->imposto->PIS->PISQtde->CST ?? 0;
						}

						if($cst == 0) {
							$cst = $item->imposto->PIS->PISAliq->CST ?? 0;
						}

						// if (isset($item->imposto->PIS->PISNT->CST) == 04 || isset($item->imposto->PIS->PISAliq->CST) == 02 || isset($item->imposto->PIS->PISNT->CST) == 06 || isset($item->imposto->PIS->PISQtde->CST) == 03) {
						if ($cst == 04 || $cst == 02 || $cst == 06 || $cst == 03) {
							$param = [];
							$temp = [];
							$temp['bloco']			= 'C170'; // sep 01
							$temp['num_item'] 		= ++$item_seq; // sep 02
							$temp['cod_item'] 		= $item->prod->cProd; // sep 03
							$temp['qtd'] 			= $item->prod->qCom; // sep 05
							$temp['vlr_total'] 		= $item->prod->vProd; // sep 07
							$temp['vlr_desc'] 		= !empty($item->prod->vDesc) ? $item->prod->vDesc : 0.0; // sep 08
							$temp['cfop'] 			= $item->prod->CFOP; // sep 11
							$temp['cst'] 			= $item->imposto->PIS->PISNT->CST ?? $item->imposto->PIS->PISAliq->CST; // sep 25
							$temp['aliq_pis']		= $aliq_pis; // sep 27
							$temp['aliq_cofins']	= $aliq_cofins; // sep 33
							$temp['chv_nfe']			= $chave_nfe;
							$param[] = $temp;
							$this->gravaNotaBloco($param, $cnpj);
						}
					}
					gravaLogLeituraXMLmonofasico($this->_cnpj , $this->_contrato, $arquivo, 'Arquivo PROCESSADO', $log_destinatario, $log_emitente, $log_operacao);
				}
				
				if(!$processa){
					//ERRO
					gravaLogLeituraXMLmonofasico($this->_cnpj , $this->_contrato, $arquivo, $motivo, $log_destinatario, $log_emitente, $log_operacao,'S');
					log::gravaLog($this->_logContrato, $motivo);
					$erros++;
					if(!$this->_schedule && $erros < 100){
						addPortalMensagem($motivo.' - '.$filename, 'error');
					}
					$this->moveArquivoErro($file, $filename, $log_destinatario, $log_emitente, $log_operacao);
					
				}
			}
		}else {
			addPortalMensagem('Nenhum arquivo encontrado no diretório: ' . $this->_path, 'error');
			log::gravaLog($this->_logContrato, 'Nenhum arquivo encontrado no diretório: ' . $this->_path);
		}
		return;
	}

	private function setPermissoesBanco($data)
	{
		$sql = "SELECT * FROM mgt_monofasico_arquivos WHERE id_monofasico = $this->_id AND data = $data";
		$row = queryMF($sql);

		if (is_array($row) && count($row) > 0 && $row[0]['alterar'] == 'N') {
			$sql = "UPDATE mgt_monofasico_arquivos SET alterar = 'S' WHERE id_monofasico = $this->_id AND data = $data";
			queryMF($sql);
		}
	}

	private function moveArquivoErro($path, $filename, $log_destinatario, $log_emitente, $log_operacao)
	{
		if(is_file($path)){
			rename($path, $this->_path_raiz . 'erro' . DIRECTORY_SEPARATOR . $filename);
//			gravaLogLeituraXMLmonofasico($this->_cnpj , $this->_contrato, $filename, 'Arquivo movido para pasta erro', $log_destinatario, $log_emitente, $log_operacao,'S');
		}
	}

	private function gravaNotaBloco($dados, $cnpj)
	{
		if (empty($dados)) {
			return;
		}

		if ($dados[0]['bloco'] == 'C170') {
			$arquivo = 'C100';
		} else {
			$arquivo = $dados[0]['bloco'];
		}

		$file = fopen($this->_path_raiz . 'arquivos' . DIRECTORY_SEPARATOR . $arquivo . '.vert', "a");

		foreach ($dados as $dado) {
			fwrite($file, implode('|', $dado) . "\n");
		}

		fclose($file);
	}

	private function controlador($num_doc, $data, $nome)
	{
		$file = fopen($this->_path_raiz . 'arquivos' . DIRECTORY_SEPARATOR . '0000.vert', "a");
		$linha = $num_doc.'|'.$data.'|'.$nome."\n";
		fwrite($file, $linha);
		fclose($file);
		
		$chave = $num_doc.'*#*'.$data;
		$this->_notas_processadas[$chave] = '*';
	}

	private function verificador($num_doc, $data)
	{
		$ret = true;
		$arquivo = $this->_path_raiz . 'arquivos' .  DIRECTORY_SEPARATOR . '0000.vert';
		
		//Verifica se já foram carregdas as notas processadas
		if(count($this->_notas_processadas) == 0 && is_file($arquivo)){
			$files = glob($arquivo);

			if (count($files) > 0) {
				$handle = fopen($files[0], "r");
				if ($handle) {
					while (!feof($handle)) {
						$linha = fgets($handle);
						if (!empty($linha)) {
							$sep = explode('|', $linha);
							// $num_doc + data
							$chave = $sep[0].'*#*'.$sep[1];
							$this->_notas_processadas[$chave] = '*';
							
						}
					}
				}
			}
		}
		
		if (isset($this->_notas_processadas[$num_doc.'*#*'.$data])) {
			$ret = false;
		}
		
		return $ret;
	}

	private function getArquivos($dir)
	{
		$ret = [];
		$diretorio = dir($dir);

		if(!is_null($diretorio) && $diretorio !== false){
			while ($arquivo = $diretorio->read()) {
				$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
				$ext = strtolower($ext);
				if ($arquivo != '.' && $arquivo != '..' && $ext == 'xml') {
					$ret[] = $arquivo;
				}
			}
		}
		return $ret;
	}
}

