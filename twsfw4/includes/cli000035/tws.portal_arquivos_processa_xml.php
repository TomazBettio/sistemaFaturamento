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

class portal_arquivos_processa_xml
{
	//Path
	private $_path;

	//Dados
	private $_dados = [];

	//debug
	private $_trace;

	//Existe arquivo
	private $_file_exists;

	//cnpj
	private $_cnpj;



	public function __construct($cnpj, $contrato, $trace = false)
	{
		global $config;
		$this->_path = $config['pathPortalArquivo'] . $cnpj . DIRECTORY_SEPARATOR . $contrato . DIRECTORY_SEPARATOR;
		$this->_trace = $trace;

		$this->_cnpj = $cnpj;

		$this->_file_exists = $this->existeArquivos('xml');
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
		// if(!file_exists($this->_path . $cnpj)) {
		if ($this->_trace) {
			// print_r($arquivos);
		} else {
			// die("Não constam arquivo de " . $arquivos . " no diretorio, favor verificar!");
			// echo 'Variável trace está retornando false';
		}

		$arquivos = $this->getArquivos($this->_path);

		foreach ($arquivos as $arquivo) {
			$this->leituraArquivo($cnpj, $arquivo);
			if(file_exists($this->_path . $arquivo)) {
				rename($this->_path . $arquivo, $this->_path . 'processados_xml' . DIRECTORY_SEPARATOR . $arquivo);
			}
			// $this->excluiNota($arquivo, $cnpj);
		}
	}

	private function leituraArquivo($cnpj, $arquivo)
	{
		//ler xml
		// $xml = simplexml_load_file($arquivo);
		$ret = [];
		//ta autorizado && diretorio cnpj é igual ao cnpj do xml?

		// $files = glob($this->_path . $cnpj . DIRECTORY_SEPARATOR . $arquivo);
		// //transform all xml extensions into lower case

		// foreach ($files as $file) {
		// 	$ext = pathinfo($file, PATHINFO_EXTENSION);

		// 	if ($ext == 'XML') {
		// 		$newfile = str_replace('.XML', '.xml', $file);
		// 		rename($file, $newfile);
		// 	}
		// }
		$files = glob($this->_path . $arquivo);
		//load all xml files

		$item_seq = 0;
		if (count($files) > 0) {
			foreach ($files as $file) {
				$xml = simplexml_load_file($file);

				$xml = $xml->procNFe ?? $xml;

				$aut = $xml->protNFe->infProt->cStat ?? 0; //$xml->procNFe->protNFe->infProt->cStat;

				// verifica se o item é aprovado
				if ($aut == 100) {
					///////////////////// DADOS DA NOTA */////////////////////
					$cnpj_dest = substr($xml->NFe->infNFe->dest->CNPJ, 0, 7);
					$num_doc = $xml->NFe->infNFe->ide->cNF;
					$dhEmi = $xml->NFe->infNFe->ide->dhEmi;
					$dhEmi = str_replace('-', '', substr($dhEmi, 0, 10));
					$nome = str_replace("'", "", $xml->NFe->infNFe->dest->xNome);

					if ($this->verificador($num_doc, $dhEmi)) {
						$this->controlador($num_doc, $dhEmi, $nome);

						$cnpj_nf = '';
						$continua = true;
						// if ($cnpj_emit == substr($cnpj, 0, 7)) {
						// 	$tipo_nf = 1; // Saida

						// 	// XML será sempre saída, portanto, puxar sempre 0140(destinatário) e 0150(emitente)
						// 	// E os dois devem entrar
						// 	$param = [];
						// 	$temp = [];
						// 	$temp['bloco'] 			= '0140';
						// 	$temp['cod_part']		= $num_doc;
						// 	$temp['nome_cliente'] 	= str_replace("'", "", $xml->NFe->infNFe->dest->xNome);
						// 	$temp['cnpj'] 			= $xml->NFe->infNFe->dest->CNPJ; // $cnpj_dest
						// 	$cnpj_nf = $temp['cnpj']; // $cnpj_emit
						// 	$param[] = $temp;
						// 	$this->gravaNotaBloco($param, $cnpj);

						// 	$param = [];
						// 	$temp = [];
						// 	$temp['bloco'] 			= '0150';
						// 	$temp['cod_part']		= $num_doc;
						// 	$temp['nome_cliente'] 	= str_replace("'", "", $xml->NFe->infNFe->emit->xNome);
						// 	$temp['cnpj'] 			= $xml->NFe->infNFe->emit->CNPJ; // $cnpj_emit
						// 	$cnpj_nf = $temp['cnpj']; // $cnpj_emit
						// 	$param[] = $temp;
						// 	$this->gravaNotaBloco($param, $cnpj); // ok
						if ($cnpj_dest == substr($cnpj, 0, 7)) {
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
						} else {
							addPortalMensagem('Erro: CNPJ do cliente não encontrado no arquivo', 'error');

							$filename = pathinfo($file, PATHINFO_BASENAME);
							$this->moveArquivoErro($file, $filename);

							// echo $file . "<br> \n";

							// echo "arquivo caiu no erro por ter cnpj diferente da nota, cnpj da nota: $cnpj_dest, cnpj do cliente: $cnpj <br> \n";

							$continua = false;
						}

						if ($continua) {
							// $dhEmi = $xml->NFe->infNFe->ide->dhEmi;
							// $dhEmi = substr($dhEmi, 0, 10);
							// $dhEmi = str_replace('-', '', $dhEmi);

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

							//$dados_nota['data_inc'] 				= date('Y-m-d H:i:s', time());
							// $dados_nota['itens_total'] 			= $detCount;

							// echo 'Notas incluidas no banco <br>';

							///////////////////// DADOS DOS ITENS */////////////////////
							//passar item por item e guardar os dados no array
							$det = $xml->NFe->infNFe->det;
							$cont = 0;
							foreach ($det as $item) {

								$ncm = $item->prod->NCM;

								// $sql = "SELECT ncm FROM mgt_ncm WHERE ncm = $ncm";
								// $row_ncm = queryMF($sql);

								// $sql = "SELECT * FROM itens_entrada WHERE ncm = $ncm";
								// $row_item = queryMF($sql);

								// if(count($row_ncm) > 0 && count($row_item) == 0) {
								$cont++;

								$param = [];
								$temp = [];
								$temp['bloco']			= '0200'; // sep 01
								$temp['cod_item'] 		= $item->prod->cProd; // sep 02
								$temp['nome_produto'] 	= str_replace(";", '', $item->prod->xProd); // sep 04
								$temp['cod_ncm'] 		= $ncm; //$item->prod->NCM;   sep 08
								$param[] = $temp;
								$this->gravaNotaBloco($param, $cnpj); // ok

								// 

								$aliq_pis = 0;
								$aliq_cofins = 0;
								if (isset($item->PIS->PISOutr->pPIS)) {
									$aliq_pis = !empty($item->PIS->PISOutr->pPIS) ?? 0;
									$aliq_cofins = !empty($item->COFINS->COFINSOutr->pCOFINS) ?? 0;
								} else if (isset($item->PIS->PISAliq->pPIS)) {
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
						}
					} else {
						unlink($file);
					}

					// return $ret;


					// $cnpj_nota = str_replace(['/', '.', '-'], '', $dados_nota['cnpj']);

					// if ($cnpj_nota == $cnpj) {

					// 	// $this->gravaNota($this->_dados, $cnpj);
					// 	echo '<br>Json feito <br>';
					// } else {
					// 	echo 'Não foi criado o Json  ';
					// }
				} else {

					$filename = pathinfo($file, PATHINFO_BASENAME);
					$this->moveArquivoErro($file, $filename);

					// echo "arquivo movido para o erro por não estar autorizado, caminho seguido: xml->protNFe->infProt->cStat = $aut <br> \n";

				}
				// $ret .= '===================================================================== <br>';
				// $dhEmi = $xml->NFe->infNFe->ide->dhEmi;
			}
		} else {
			// $ret = 'Nenhum arquivo encontrado no diretório: ' . $this->_path;
		}

		return $ret;
	}

	private function moveArquivoErro($path, $filename)
	{
		rename($path, $this->_path . 'erro' . DIRECTORY_SEPARATOR . $filename);
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

		$file = fopen($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . $arquivo . '.vert', "a");

		foreach ($dados as $dado) {
			// fwrite($file, implode('|', $dado) . "\n");
			//write a json file
			// fwrite($file, json_encode($dado) . "\n");
			fwrite($file, implode('|', $dado) . "\n");
			// print_r($dado);
			// echo "<br>";
		}

		fclose($file);
	}

	private function controlador($num_doc, $data, $nome)
	{
		$file = fopen($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . '0000.vert', "a");

		$dados = [];
		$dados['num_doc'] = $num_doc;
		$dados['data'] = $data;
		$dados['nome'] = $nome;

		fwrite($file, implode('|', $dados) . "\n");

		fclose($file);
	}

	private function verificador($num_doc, $data)
	{
		$ret = true;

		$files = glob($this->_path . 'arquivos' .  DIRECTORY_SEPARATOR . '0000.vert');

		if (count($files) > 0) {
			$handle = fopen($files[0], "r");
			if ($handle) {
				while (!feof($handle)) {
					$linha = fgets($handle);
					if (!empty($linha)) {
						$sep = explode('|', $linha);
						if ($sep[0] == $num_doc && $sep[1] == $data) {
							$ret = false;
						}
					}
				}
			}
		}
		return $ret;
	}

	private function gravaNota($dados, $cnpj)
	{
		if (empty($dados)) {
			return;
		}

		$file = fopen($this->_path . 'arquivo_xml' . DIRECTORY_SEPARATOR . 'xml.txt', "a");

		foreach ($dados as $dado) {
			// fwrite($file, implode('|', $dado) . "\n");
			//write a json file
			// fwrite($file, json_encode($dado) . "\n");
			fwrite($file, implode('|', $dado) . "\n");
			// print_r($dado . "\n");
		}

		fclose($file);
	}

	private function excluiNota($arquivo, $cnpj)
	{
		$file = $this->_path . $arquivo;

		if (file_exists($file)) {
			unlink($file);
		}
	}

	private function getArquivos($dir)
	{
		$ret = [];
		$diretorio = dir($dir);

		while ($arquivo = $diretorio->read()) {
			$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
			$ext = strtolower($ext);
			if ($arquivo != '.' && $arquivo != '..' && $ext == 'xml') {
				$ret[] = $arquivo;
			}
		}
		return $ret;
	}
}



// $dados_nota['itens_aprovados'] 	= $cont;

	// $ret .= '<hr> ITEM DA NOTA <br>';
	// $ret .= 'cProd: ' roduto: ' . $dados_item['nome_produto'] . '<br>';
	// $ret .= 'NCM: ' . . $dados_item['cod_prod'] . '<br>';
	// $ret .= 'CFOP: ' . $dados_item['cfop'] . '<br>';
	// $ret .= 'Nome do p$dados_item['ncm'] . '<br>';
	// $ret .= 'Valor: ' . $dados_item['valor'] . '<br>';
	// $ret .= 'Desconto: ' . $dados_item['desconto'] . '<br>';
	// $ret .= 'Valor com desconto: ' . $dados_item['valor_com_desconto'] . '<br>';
	// $ret .= 'NCM PIS: ' . $ncm[0]['aliquota_pis'] . '%<br>';
	// $ret .= 'NCM COFINS: ' . $ncm[0]['aliquota_cofins'] . '%<br>';
	// $total_ncm = $ncm[0]['aliquota_pis'] + $ncm[0]['aliquota_cofins'];
	// $ret .= 'Total de NCM: ' . $total_ncm . '%<br>';
	// $desconto_ncm = ($dados_item['valor_com_desconto'] * $total_ncm) / 100;
	// $valor_final = $dados_item['valor_com_desconto'] - $desconto_ncm;
	// $ret .= 'Valor com aliquota: ' . $valor_final . '<br>';
	// $ret .= '<hr>';




	// $ret .= '<hr> NOTA FISCAL <br>';
	// $ret .= 'Nome: ' . $dados_nota['nome'] . '<br>';
	// $ret .= 'cNF: ' . $dados_nota['cod_nf'] . '<br>';
	// $ret .= 'data_emi: ' . $dados_nota['data_emi'] . '<br>';
	// $ret .= 'data_inc: ' . $dados_nota['data_inc'] . '<br>';
	// $ret .= 'CNPJ: ' . $dados_nota['cnpj'] . '<br>';
	// $ret .= 'ID Nota: ' . $dados_nota['id_nota'] . '<br>';
	// $ret .= 'Total de itens: ' . $dados_nota['itens_total'] . '<br>';
	// $ret .= 'Itens aprovados: ' . $dados_nota['itens_aprovados'] . '<br><br>';
	// $ret .= '<hr>';
