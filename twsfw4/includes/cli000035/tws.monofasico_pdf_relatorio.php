<?php
if (!defined('TWSiNet') || !TWSiNet)
	die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_pdf_relatorio
{

	// programa
	private $_programa;

	// titulo
	private $_titulo;

	// Classe relatorio
	private $_relatorio;

	private $_relatorioPisCofins01;

	private $_relatorioPisCofins02;

	private $_relatorioPisCofins03;

	private $_relatorioPisCofins04;

	// Dados
	private $_dados;

	// cnpj
	private $_cnpj;

	// path
	private $_path;

	// Colunas dos itens selecionados
	private $_colunas;

	// nome do cliente
	private $_razao;

	public function __construct($cnpj, $contrato)
	{
		global $config;

		$this->_programa = get_class($this);
		$this->_titulo = 'PDF - Relatório';

		$this->_cnpj = $cnpj;
		$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
		$this->_teste = false;

		$this->getRazao();

		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$this->_relatorio = new tabela_pdf01($param);
		$this->_relatorio->setStripe(true);

		$this->_relatorio->setModoConversaoPdf('WKHTMLTOPDF');

		$param = [];
		$this->_relatorioPisCofins01 = new tabela_pdf01($param);
		$this->_relatorioPisCofins01->setStripe(true);
		$this->montaColunasPisCofins($this->_relatorioPisCofins01);
		/*/
		$param = [];
		$this->_relatorioPisCofins02 = new tabela_pdf01($param);
		$this->montaColunasPisCofins($this->_relatorioPisCofins02);

		$param = [];
		$this->_relatorioPisCofins03 = new tabela_pdf01($param);
		$this->montaColunasPisCofins($this->_relatorioPisCofins03);

		$param = [];
		$this->_relatorioPisCofins04 = new tabela_pdf01($param);
		$this->montaColunasPisCofins($this->_relatorioPisCofins04);
/*/
		$this->_colunas = [
			'chv_nf',
			'fornecedor',
			'num_doc',
			'data_emi',
			'descr_item',
			'ncm',
			'cfop',
			'ind_oper',
			'num_item',
			'itens_nota',
			'vl_item',
			'vl_desc',
			'vl_base',
			'aliq_pis',
			'aliq_cofins',
			'vl_final_pis',
			'vl_final_cofins',
			'vl_calc_final_pis',
			'vl_calc_final_cofins',
			'selecionado',
			'qtd',
			'cod_item',
			'filial',
			'cnpj_forn'
		];
		$this->log('Instanciada a classe');
	}

	public function index($titulo = 'Relat&oacute;rio de Apura&ccedil;&atilde;o de cr&eacute;dito')
	{
		if (file_exists($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . $this->_cnpj . '.zip')) {
			unlink($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . $this->_cnpj . '.zip');
		}

		set_time_limit(0);

		$dados = [];
		if (is_file($this->_path . 'arquivos/resultado.vert')) {
			$dados = $this->getDados();
		} else {
			addPortalMensagem('Não foi possível encontrar o arquivo de resultado. Rode a Analise para gerar o resultado.', 'erro');
			$this->log('Não existe o arquivo resultado.vert');
			return '';
		}
		$this->montaColunas();

		$arquivos = [];

		if (count($dados) > 0) {
			// print_r($dados);die();
			foreach ($dados as $mes => $dadosMes) {
				$mesano = substr($mes, 4, 2) . '/' . substr($mes, 0, 4);
				$this->_relatorio->setDados($dadosMes);
				$arquivo = $this->_path . 'arquivos/' . $this->_cnpj . '_' . $mes . '.pdf';
				$temp = [];
				$temp[0] = $arquivo;
				$temp[1] = $this->_cnpj . '_' . $mes . '.pdf';
				$arquivos[] = $temp;

				$dadosPisCofins = [];
				$temp = $this->getDadosPisCofins('geral', $mes);
				$temp[0]['titulo'] = 'Produtos Adquiridos';
				$dadosPisCofins[] = $temp[0];
				$dadosPisCofins[] = $temp[1];
				//$this->_relatorioPisCofins01->setDados($dadosPisCofins);
				//$this->_relatorioPisCofins01->setTitulo('<span style="text-align:center">Resumo Geral de Produtos Adquiridos</span>');
				$this->_relatorioPisCofins01->setTitulo('<span style="text-align:center">Resumo Geral</span>');
				//$tabelaPisCofins = $this->_relatorioPisCofins01 . '<br><br><br>';

				$temp = $this->getDadosPisCofins('fabricante', $mes);
				$temp[0]['titulo'] = 'Cr&eacute;dito - Fabricante';
				$dadosPisCofins[] = $temp[0];
				//$this->_relatorioPisCofins02->setDados($dadosPisCofins);
				//$this->_relatorioPisCofins02->setTitulo('<span>Resumo Proporcional de Cr&eacute;dito - Fabricante</span>');
				//$tabelaPisCofins .= $this->_relatorioPisCofins02 . '<br><br><br>';

				$temp = $this->getDadosPisCofins('distribuidor', $mes);
				$temp[0]['titulo'] = 'Cr&eacute;dito - Distribuidor';
				$dadosPisCofins[] = $temp[0];
				//$this->_relatorioPisCofins03->setDados($dadosPisCofins);
				//$this->_relatorioPisCofins03->setTitulo('<span>Resumo Proporcional de Cr&eacute;dito - Distribuidor</span>');
				//$tabelaPisCofins .= $this->_relatorioPisCofins03 . '<br><br><br>';

				$temp = $this->getDadosPisCofins('compensar', $mes);
				$temp[0]['titulo'] = '<strong>Cr&eacute;dito a compensar</strong>';
				// foreach($dados[0] as $k => $dado) {
				// 	$temp[$k] = '<b>' . $dado[$k] . '</b>';
				// }
				$dadosPisCofins[] = $temp[0];
				//$this->_relatorioPisCofins04->setDados($dadosPisCofins);
				//$this->_relatorioPisCofins04->setTitulo('<span>Resumo de Cr&eacute;dito a compensar</span>');
				//$tabelaPisCofins .= $this->_relatorioPisCofins04 . '<br><br><br>';

				// $dadosFormatados = [];
				// foreach($dadosPisCofins as $temp) {
				// 	$param = [];
				// 	foreach($temp as $k => $dado) {
				// 		if($k == 'titulo' || $k == 'data_emi') {
				// 			$param[$k] = $temp[$k];
				// 		} else {
				// 			$param[$k] = number_format($temp[$k], 2, ',', '.');
				// 		}
				// 	}
				// 	$dadosFormatados[] = $param;
				// }

				$this->_relatorioPisCofins01->setDados($dadosPisCofins);
				$tabelaPisCofins = $this->_relatorioPisCofins01 . '<br><br><br>';

				$this->_relatorio->setFooter($tabelaPisCofins);

				$this->_relatorio->gerarPdf($arquivo, $this->getCabecalho($titulo, $mesano) . " " . $mesano);
			}
		} else {
			addPortalMensagem('Não foram encontrados dados para gerar os relat&oacute;rios!', 'error');
		}

		if (count($arquivos) > 0) {
			$this->geraZIP($arquivos);
		} else {
			addPortalMensagem('Não foi gerado nenhum PDF!', 'error');
		}

		$this->log('fanalizou');
		$this->_relatorio . '';
		return '';
	}

	private function getRazao()
	{
		$file = glob($this->_path . 'arquivos/0000.vert');

		if (count($file) > 0) {
			$handle = fopen($file[0], "r");
			if ($handle) {
				while (!feof($handle)) {
					$linha = fgets($handle);
					$linha = str_replace(["\r\n", "\n", "\r"], '', $linha);
					if (!empty($linha)) {
						$sep = explode('|', $linha);
						$this->_razao = $sep[2];
						return true;
					}
				}
			}
		}
	}

	private function geraZIP($arquivos)
	{
		$arquivo = $this->_path . 'arquivos/' . $this->_cnpj . '.zip';
		$zip = new ZipArchive();
		$zip->open($arquivo, ZipArchive::CREATE);
		foreach ($arquivos as $arquivo) {
			$zip->addFile($arquivo[0], $arquivo[1]);
		}
		$zip->close();

		foreach ($arquivos as $arquivo) {
			unlink($arquivo[0]);
		}
	}

	private function getDadosPisCofins($tipo, $mes)
	{
		$ret = [];

		$file = glob($this->_path . 'arquivos/resumoCompliance.vert');

		if (count($file) > 0) {
			$handle = fopen($file[0], "r");
			if ($handle) {
				while (!feof($handle)) {
					$linha = fgets($handle);
					$linha = str_replace([
						"\r\n",
						"\n",
						"\r"
					], '', $linha);
					if (!empty($linha)) {
						$sep = explode('|', $linha);
						if (count($sep) > 1) {
							// print_r($sep);
							if ($mes == $sep[0]) {
								$mes = substr($sep[0], 4, 2);
								$ano = substr($sep[0], 0, 4);
								$data_emi = $mes . '/' . $ano;
								if ($tipo == 'geral') {
									$param = [];
									$param['data_emi'] = $data_emi;
									$param['bruto'] = number_format($sep[1], 2, ',', '.');
									$param['total_pis'] = number_format($sep[2] + $sep[6], 2, ',', '.');
									$param['total_cofins'] = number_format($sep[3] + $sep[7], 2, ',', '.');
									$param['liquido'] = number_format($sep[2] + $sep[6] + $sep[3] + $sep[7], 2, ',', '.');
									$ret[] = $param;

									$param = [];
									$param['titulo'] = ' ';
									$param['data_emi'] = ' ';
									$param['bruto'] = ' ';
									$param['total_pis'] = ' ';
									$param['total_cofins'] = ' ';
									$param['liquido'] = ' ';
									$ret[] = $param;
								} else if ($tipo == 'fabricante') {
									$param = [];
									$param['data_emi'] = $data_emi;
									$param['bruto'] = number_format($sep[1], 2, ',', '.');
									$param['total_pis'] = number_format($sep[4], 2, ',', '.');
									$param['total_cofins'] = number_format($sep[5], 2, ',', '.');
									$param['liquido'] = number_format($sep[4] + $sep[5], 2, ',', '.');
									$ret[] = $param;
								} else if ($tipo == 'distribuidor') {
									$param = [];
									$param['data_emi'] = $data_emi;
									$param['bruto'] = number_format($sep[1], 2, ',', '.');
									$param['total_pis'] = number_format($sep[8], 2, ',', '.');
									$param['total_cofins'] = number_format($sep[9], 2, ',', '.');
									$param['liquido'] = number_format($sep[8] + $sep[9], 2, ',', '.');
									$ret[] = $param;
								} else if ($tipo == 'compensar') {
									$param = [];
									$param['data_emi'] = '<b>' . $data_emi . '</b>';
									$param['bruto'] = '<b>' . number_format($sep[1], 2, ',', '.') . '</b>';
									$param['total_pis'] = '<b>' . number_format($sep[4] + $sep[8], 2, ',', '.') . '</b>';
									$param['total_cofins'] = '<b>' . number_format($sep[5] + $sep[9], 2, ',', '.') . '</b>';
									$param['liquido'] = '<b>' . number_format($sep[4] + $sep[8] + $sep[5] + $sep[9], 2, ',', '.') . '</b>';
									$ret[] = $param;
								}
							}
						}
					}
				}
			}
		}
		// print_r($ret);
		return $ret;
	}

	private function montaColunasPisCofins($relatorio)
	{
		$relatorio->addColuna(array(
			'campo' => 'titulo',
			'etiqueta' => 'Referencia',
			'tipo' => 'T',
			'width' => 200,
			'posicao' => 'E'
		));
		$relatorio->addColuna(array(
			'campo' => 'data_emi',
			'etiqueta' => 'Per&iacute;odo',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'C'
		));
		$relatorio->addColuna(array(
			'campo' => 'bruto',
			'etiqueta' => 'Valor bruto',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'D'
		));
		$relatorio->addColuna(array(
			'campo' => 'total_pis',
			'etiqueta' => 'Valor total PIS',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'D'
		));
		$relatorio->addColuna(array(
			'campo' => 'total_cofins',
			'etiqueta' => 'Valor total COFINS',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'D'
		));
		$relatorio->addColuna(array(
			'campo' => 'liquido',
			'etiqueta' => 'Valor l&iacute;quido',
			'tipo' => 'T',
			'width' => 100,
			'posicao' => 'D'
		));
	}

	private function montaColunas()
	{
		$this->_relatorio->addColuna(array(
			'campo' => 'num_doc',
			'etiqueta' => 'Nota Fiscal',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'C'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'cod_item',
			'etiqueta' => 'Codigo',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'C'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'descr_item',
			'etiqueta' => 'Nome Produto',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'fornecedor',
			'etiqueta' => 'Fornecedor',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'E'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'ncm',
			'etiqueta' => 'Ncm',
			'tipo' => 'T',
			'width' => 80,
			'posicao' => 'C'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'qtd',
			'etiqueta' => 'Qtde',
			'tipo' => 'T',
			'width' => 20,
			'posicao' => 'C'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'vl_base',
			'etiqueta' => 'Valor',
			'tipo' => 'V',
			'width' => 40,
			'posicao' => 'D'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'cfop',
			'etiqueta' => 'CFOP',
			'tipo' => 'T',
			'width' => 40,
			'posicao' => 'C'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'aliq_pis',
			'etiqueta' => 'PIS',
			'tipo' => 'T',
			'width' => 40,
			'posicao' => 'D'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'vl_final_pis',
			'etiqueta' => 'Valor PIS',
			'tipo' => 'V',
			'width' => 40,
			'posicao' => 'D'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'aliq_cofins',
			'etiqueta' => 'cofins',
			'tipo' => 'T',
			'width' => 40,
			'posicao' => 'D'
		));
		$this->_relatorio->addColuna(array(
			'campo' => 'vl_final_cofins',
			'etiqueta' => 'Valor cofins',
			'tipo' => 'V',
			'width' => 80,
			'posicao' => 'D'
		));
	}

	private function recuperaArquivo()
	{
		$dados = [];
		$arquivo = $this->_path . 'arquivos/resultado.vert';
		$arquivo = file($arquivo);

		foreach ($arquivo as $linha) {
			$linha = str_replace([
				"\r\n",
				"\n",
				"\r"
			], '', $linha);
			$linha = explode('|', $linha);
			$temp = [];
			foreach ($this->_colunas as $key => $coluna) {
				$temp[$coluna] = isset($linha[$key]) ? $linha[$key] : '';
			}

			// $checked = '';
			// $temp['sel'] = '<input name="item[' . $temp['chv_nf'] . '-' . $temp['num_item'] . ']" type="checkbox" value="" ' . $checked . ' id="' . $temp['chv_nf'] . '-' . $temp['num_item'] . '">';
			// print_r($temp);
			if ($temp['selecionado'] == 'S') {
				$dados[] = $temp;
			}
		}
		return $dados;
	}

	private function getDados()
	{
		$ret = [];
		$sort = []; // array de ordenação
		$dados = $this->recuperaArquivo();
 //print_r($dados);die();
		// $arquivo = $this->_path . $this->_cnpj . DIRECTORY_SEPARATOR . 'arquivos/resultado.vert';
		// $arquivo = file($arquivo);
		foreach ($dados as $dado) {
			$filial = $dado['filial'];
			$anoMes = str_replace('-', '', substr($dado['data_emi'], 0, 6));
			$temp = [];
			$temp['num_doc'] = $dado['num_doc'];
			$temp['cod_item'] = substr($dado['cod_item'], 6);
			$temp['descr_item'] = $dado['descr_item'];
			$temp['fornecedor'] = $dado['fornecedor'];
			$temp['ncm'] = $dado['ncm'];
			$temp['qtd'] = $dado['qtd'];
			$temp['vl_base'] = $dado['vl_base'];
			$temp['cfop'] = $dado['cfop'];
			$temp['aliq_pis'] = $dado['aliq_pis'];
			$temp['vl_final_pis'] = $dado['vl_final_pis']; // sem calculo de industria/comercio
			$temp['vl_calc_final_pis'] = $dado['vl_calc_final_pis']; // COM calculo de industria/comercio
			$temp['aliq_cofins'] = $dado['aliq_cofins'];
			$temp['vl_final_cofins'] = $dado['vl_final_cofins']; // sem calculo de industria/comercio
			$temp['vl_calc_final_cofins'] = $dado['vl_calc_final_cofins']; // COM calculo de industria/comercio

			$sort[$anoMes][$filial][$dado['ncm']][] = $temp;
		}
//print_r($sort);
		$mesesAmostrar = array_keys($sort);
		sort($mesesAmostrar);
		// print_r($mesesAmostrar);
		foreach ($mesesAmostrar as $mes) {
			$total_mes_pis = 0;
			$total_mes_cofins = 0;
			$filiais = array_keys($sort[$mes]);
			sort($filiais);
			foreach ($filiais as $filial) {
				$total_filial_pis = 0;
				$total_filial_cofins = 0;

				$ncms = array_keys($sort[$mes][$filial]);
				sort($ncms);
				foreach ($ncms as $ncm) {
					$total_ncm_pis = 0;
					$total_ncm_cofins = 0;

					foreach ($sort[$mes][$filial][$ncm] as $item) {
						$ret[$mes][] = $item;

						$total_mes_pis += round($item['vl_final_pis'], 2);
						$total_filial_pis += round($item['vl_final_pis'], 2);
						$total_ncm_pis += round($item['vl_final_pis'], 2);

						$total_mes_cofins += round($item['vl_final_cofins'], 2);
						$total_filial_cofins += round($item['vl_final_cofins'], 2);
						$total_ncm_cofins += round($item['vl_final_cofins'], 2);
					}

					$ret[$mes][] = $this->getMatrizTotal('<b>Total NCM</b>', '<b>' . round($total_ncm_pis, 2) . '</b>', '<b>' . $total_ncm_cofins . '</b>');
				}
				$ret[$mes][] = $this->getMatrizTotal('<b>Total Companhia</b>', '<b>' . round($total_filial_pis, 2) . '</b>', '<b>' . $total_filial_cofins . '</b>');
			}
			$ret[$mes][] = $this->getMatrizTotal('<b>Total Mês</b>', '<b>' . round($total_mes_pis, 2) . '</b>', '<b>' . round($total_mes_cofins, 2) . '</b>');
		}

		return $ret;
	}

	private function getMatrizTotal($titulo, $total_pis, $total_cofins)
	{
		$ret = [];

		$ret['num_doc'] = '';
		$ret['cod_item'] = '';
		$ret['descr_item'] = '';
		$ret['fornecedor'] = $titulo;
		$ret['ncm'] = '';
		$ret['qtd'] = '';
		$ret['vl_item'] = '';
		$ret['cfop'] = '';
		$ret['aliq_pis'] = '';
		$ret['vl_final_pis'] = $total_pis;
		$ret['aliq_cofins'] = '';
		$ret['vl_final_cofins'] = $total_cofins;

		return $ret;
	}

	private function getCabecalho($titulo, $mesano)
	{
		$ret = '';
		global $config;
		$empresa = "M. T. Marpa Gest&atilde;o Tribut&aacute;ria";
		$cnpj = "20.102.230/0002-50";
		$telefone = "(51) 3025-7977";
		$site = "www.marpagestaotributaria.com.br";
		$dimensao_logo = "style='height: 70px; width: 250px'";
		$caminho_logo = $config['baseS3'] . 'imagens/logo_pdf.png';

		$ret = "
		<table style='width=100%;' width='100%'>
			<tr>
				<td align='left' style='width=50%; border:none;'>
					<img src='$caminho_logo' $dimensao_logo />
				</td>
				<td align='right' style='font-size:14px; width=50%; border:none;'>
					" . $empresa . "<br/>
					" . $cnpj . "<br/>
					" . $telefone . "<br/>
					" . $site . "
				</td>
			</tr>
            <tr>
						<td colspan='2' align='center' style='font-size:18px;width=100%; border:none;'>
						" . $titulo . '<br><br>' . $this->_razao . ' - ' . $this->_cnpj . '<br><br>Per&iacute;odo: ' . $mesano . "
					</td>
				
			</tr>
		</table><br>";
		return $ret;
	}

	private function log($mensagem)
	{
		if (!empty($mensagem)) {
			log::gravaLog('monofasico_processa'.DIRECTORY_SEPARATOR.'monofasico_pdf_' . $this->_cnpj, $mensagem);
		}
	}
}
