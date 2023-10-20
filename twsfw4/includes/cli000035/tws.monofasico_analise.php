<?php
if (!defined('TWSiNet') || !TWSiNet)
	die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_analise
{

	// Classe relatorio
	private $_relatorio;

	// Nome do programa
	private $_programa;

	// Dados
	private $_dados;

	// Titulo
	private $_titulo;

	// ////Blocos///////

	// Cliente Marpa
	private $_0140 = [];

	// Fornecedor
	private $_0150 = [];

	// Produto
	private $_0200 = [];

	// Nota Fiscal
	private $_C100 = [];

	// Item da Nota Fiscal
	private $_C170 = [];

	// /////////////////
	// cnpj
	private $_cnpj;

	// path
	private $_path;

	// NCMs
	private $_ncm = [];

	// CFOP
	private $_cfop = [];

	// Colunas dos itens selecionados
	private $_colunas;

	// contrato do cliente
	private $_contrato;

	//id
	private $_id;
	
	//Cadastro do contrato
	private $_contrato_dados;

	public function __construct($cnpj, $contrato, $id)
	{
		global $config;

		conectaERP();
		//conectaMF();
		// select ncm, aliq_pis, aliq_cofins from mgt_ncm

		$this->getNCM();
		$this->getCFOP();

		$this->_programa = get_class($this);

		$this->_cnpj = $cnpj;
		$this->_contrato = $contrato;
		$this->_id = $id;
		$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;
		$this->_teste = false;

		$this->getInfoContrato();

		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$this->_relatorio = new relatorio01($param);

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
	}

	public function index()
	{
		$ret = '';

		$arquivos = $this->existeArquivos();

		if (!$arquivos) {
			addPortalMensagem('Não existem arquivos processados neste cliente','error');
			redireciona();
		}

		if (is_file($this->_path . 'arquivos/resultado.vert')) {
			$dados = $this->recuperaArquivo();
		} else {
			$this->setDados();
			$dados = $this->getDados();
			$dados = $dados['dados_item'] ?? [];
		}

		$this->montaColunas();
		$this->_relatorio->setDados($dados);

		$botaoCancela = [];
		$botaoCancela["onclick"] = "setLocation('" . getLink() . "index')";
		$botaoCancela["texto"] = "Retornar";
		$botaoCancela['cor'] = 'warning';
		$this->_relatorio->addBotao($botaoCancela);

		$botao = [];
		// $botao['cor'] = 'sucess';
		$botao['texto'] = 'Excluir Itens Selecionados';
		$botao['cor'] = 'danger';
		$botao["onclick"] = "$('#formItens').submit();";
		$this->_relatorio->addBotao($botao);

		$botao_processa = [];
		$botao_processa['texto'] = 'Processar Novamente';
		$botao_processa["onclick"] = "setLocation('" . getLink() . "analise.refazerAnalise&cnpj=" . $this->_cnpj . '|' . $this->_contrato . '|' . $this->_id  . "')";
		// $botao_processa['link'] = getLink() . 'analise.refazerAnalise&cnpj=' . $this->_cnpj;
		$this->_relatorio->addBotao($botao_processa);

		// $botao = [];
		// $botao['texto'] = 'Gerar planilha';
		// $botao["onclick"] = "setLocation('" . getLink() . "analise.gerarPlanilha&cnpj=" . $this->_cnpj . "')";
		// $this->_relatorio->addBotao($botao);

		// $botao = [];
		// $botao['texto'] = 'Gerar PDF';
		// $botao['onclick'] = "setLocation('" . getLink() . "analise.geraPDF&cnpj=" . $this->_cnpj . "')";
		// $this->_relatorio->addBotao($botao);

		$ret .= $this->_relatorio;

		$param = [];
		$param['acao'] = getLink() . "analise.salvarItens&cnpj=" . $this->_cnpj . '|' . $this->_contrato . '|' . $this->_id;
		$param['id'] = 'formItens';
		$param['nome'] = 'formItens';

		$ret = formbase01::form($param, $ret);

		return $ret;
	}

	private function getInfoContrato()
	{
		$sql = "SELECT * FROM mgt_monofasico WHERE cnpj = '$this->_cnpj' AND contrato = '$this->_contrato'";
		$rows = query($sql);

		if (isset($rows[0]['razao'])) {
			$this->_titulo = $rows[0]['razao'];
			$this->_contrato_dados['id'] 		= $rows[0]['id'];
			$this->_contrato_dados['razao'] 	= $rows[0]['razao'];
			$this->_contrato_dados['cnpj'] 		= $rows[0]['cnpj'];
			$this->_contrato_dados['contrato'] 	= $rows[0]['contrato'];
			$this->_contrato_dados['integrado'] = $rows[0]['integrado'];
			$this->_contrato_dados['apura_ini'] = $rows[0]['apura_ini'];
			$this->_contrato_dados['apura_fim'] = $rows[0]['apura_fim'];
		} else {
			$this->_titulo = '';
			addPortalMensagem('Contrato não encontrado!','error');
		}
		return;
	}

	private function existeArquivos()
	{
		$ret = false;
		if (file_exists($this->_path)) {
			$temp = [];

			$diretorio = dir($this->_path . 'arquivos');

			while ($arquivo = $diretorio->read()) {
				$ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
				$ext = strtolower($ext);
				if ($arquivo != '.' && $arquivo != '..' && $ext == 'vert') {
					$temp[] = $arquivo;
				}
			}

			if (count($temp) > 1) {
				$ret = true;
			}
		}

		return $ret;
	}

	public function refazerAnalise()
	{
		// se os arquivos resultado.vert e resultadoCompliance.vert existirem, delete eles e salvenovamente os resultados
		if (is_file($this->_path . 'arquivos/resultado.vert')) {
			unlink($this->_path . 'arquivos/resultado.vert');
		}
		if (is_file($this->_path . 'arquivos/resumoCompliance.vert')) {
			unlink($this->_path . 'arquivos/resumoCompliance.vert');
		}
		if (is_file($this->_path . 'arquivos/resultado_orig.vert')) {
			unlink($this->_path . 'arquivos/resultado_orig.vert');
		}
		if (is_file($this->_path . 'arquivos/resumoCompliance_orig.vert')) {
			unlink($this->_path . 'arquivos/resumoCompliance_orig.vert');
		}
		if (is_file($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado' . $this->_contrato . '.csv')) {
			unlink($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'resultado' . $this->_contrato . '.csv');
		}
	}

	public function salvarItens()
	{
		$itens = $_POST['item'] ?? [];

		if (count($itens) > 0) {
			// Recupera todo o arquivo e marca todos os itens como "N" utilizados
			$dados = [];
			$resultado = [];
			$arquivo = $this->_path . 'arquivos/resultado.vert';
			if (is_file($arquivo)) {
				$arquivo = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				foreach ($arquivo as $linha) {
					$linha = explode('|', $linha);
					$temp = [];
					foreach ($this->_colunas as $key => $coluna) {
						$temp[$coluna] = str_replace(';', '', $linha[$key]);
					}
					$dados[] = $temp;
				}

				// Varre os dados e seta como SELECIONADO "S" os que estiverem no $_POST
				foreach ($dados as $key => $dado) {
					$id = $dado['chv_nf'] . '-' . $dado['num_item'];
					if (isset($itens[$id])) {
						$dados[$key]['selecionado'] = 'N';
					} else if ($dado['selecionado'] == 'S') {
						$anoMes = substr($dado['data_emi'], 0, 6);
						$tipo = $this->_cfop[$dado['cfop']]['tipo'];
						$resultado = $this->calcularValoresMensais($resultado, $anoMes, $tipo, $dado);
						$resultado[$anoMes]['bruto'] += $dado['vl_base'];
					}
				}
				// Salva novamente o arquivo
				$this->salvarResultado($dados);
				$this->getComplianceMensal($resultado);
			}
		}
	}

	private function recuperaArquivo()
	{
		$dados = [];
		$resultado = [];
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

			$checked = '';
			$temp['sel'] = '<input name="item[' . $temp['chv_nf'] . '-' . $temp['num_item'] . ']" type="checkbox" value="" ' . $checked . ' id="' . $temp['chv_nf'] . '-' . $temp['num_item'] . '">';

			if ($temp['selecionado'] == 'S') {
				$dados[] = $temp;
				$anoMes = substr($temp['data_emi'], 0, 6);
				$tipo = $this->_cfop[$temp['cfop']]['tipo'];
				$resultado = $this->calcularValoresMensais($resultado, $anoMes, $tipo, $temp);
				// var_dump($temp);
			}
		}

		return $dados;
	}

	private function montaColunas()
	{
		$this->_relatorio->addColuna(array('campo' => 'sel'					,'etiqueta' => '<span class="text-danger">Excluir<span>', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C' ));
		$this->_relatorio->addColuna(array('campo' => 'chv_nf'				,'etiqueta' => 'Chave de Acesso'	,'tipo' => 'T','width' =>  80,'posicao' => 'E' ));
		$this->_relatorio->addColuna(array('campo' => 'num_doc'				,'etiqueta' => 'Nota Fiscal'		,'tipo' => 'T','width' =>  80,'posicao' => 'E' ));
		$this->_relatorio->addColuna(array('campo' => 'data_emi'			,'etiqueta' => 'Data Nota'			,'tipo' => 'D','width' =>  80,'posicao' => 'C'));
		$this->_relatorio->addColuna(array('campo' => 'fornecedor'			,'etiqueta' => 'Fornecedor'			,'tipo' => 'T','width' => 100,'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'descr_item'			,'etiqueta' => 'Nome Produto'		,'tipo' => 'T','width' => 100,'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ncm'					,'etiqueta' => 'NCM'				,'tipo' => 'T','width' =>  80,'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cfop'				,'etiqueta' => 'CFOP'				,'tipo' => 'T','width' => 250,'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'ind_oper'			,'etiqueta' => 'Operação'			,'tipo' => 'T','width' => 250,'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'num_item'			,'etiqueta' => 'Nº Item'			,'tipo' => 'T','width' =>  80,'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'itens_nota'			,'etiqueta' => 'itens_nota'			,'tipo' => 'T','width' => 250,'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vl_item'				,'etiqueta' => 'Valor item'			,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vl_desc'				,'etiqueta' => 'Desconto'			,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vl_base'				,'etiqueta' => 'Valor Base'			,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'aliq_pis'			,'etiqueta' => 'Aliq_pis'			,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'aliq_cofins'			,'etiqueta' => 'Aliq_cofins'		,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vl_final_pis'		,'etiqueta' => 'Valor Final PIS'	,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vl_final_cofins'		,'etiqueta' => 'Valor Final COFINS'	,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vl_calc_final_pis'	,'etiqueta' => 'PIS a recuperar'	,'tipo' => 'V','width' => 250,'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'vl_calc_final_cofins','etiqueta' => 'COFINS a recuperar'	,'tipo' => 'V','width' => 250,'posicao' => 'D'));
	}

	private function getComplianceMensal($resultado)
	{
		$arquivo = fopen($this->_path . 'arquivos/resumoCompliance.vert', 'w');
		$colunas = [
			'bruto',
			'vl_final_I_pis',
			'vl_final_I_cofins',
			'vl_calc_final_I_pis',
			'vl_calc_final_I_cofins',
			'vl_final_C_pis',
			'vl_final_C_cofins',
			'vl_calc_final_C_pis',
			'vl_calc_final_C_cofins',
		];

		foreach ($resultado as $anoMes => $resumo) {
			$temp = [];
			$temp[] = $anoMes;
			foreach ($colunas as $coluna) {
				$temp[] = str_replace(',', '', number_format($resumo[$coluna], 2));
			}
			$linha = implode('|', $temp);
			fwrite($arquivo, $linha . "\n");
		}
		fclose($arquivo);
	}

	private function getDados()
	{
		$ret = [];
		$resultado = [];

		foreach ($this->_C100 as $nota) {
			$chave = substr($nota['chv_nfe'], 0, 43);
			$temp_notas = [];
			$anoMes = str_replace('-', '', substr($nota['data_emi'], 0, 6));
			if (isset($this->_C170[$chave])) {
				foreach ($this->_C170[$chave] as $item) {
					$codigo_ncm = $this->_0200[$item['cod_item']]['ncm'] . '';
					if (isset($this->_ncm[$codigo_ncm]) && isset($this->_cfop[$item['cfop']])) {
						$checked = '';
						$temp = [];
						$temp['sel'] 						= '<input name="item[' . $item['chv_nfe'] . '-' . $item['num_item'] . ']" type="checkbox" value="" ' . $checked . ' id="' . $item['chv_nfe'] . '-' . $item['num_item'] . '">';
						$temp['chv_nf'] 				= $item['chv_nfe'];
						$temp['num_doc'] 				= $nota['num_doc'];
						$temp['data_emi'] 			= str_replace('-', '', $nota['data_emi']); // 2020-04-06
						$temp['fornecedor'] 		= isset($this->_0150[$nota['cod_part']]['razao']) ? $this->_0150[$nota['cod_part']]['razao'] : $this->_0140[$nota['cod_part']]['razao'];
						$temp['descr_item'] 		= $this->_0200[$item['cod_item']]['descr_item'];
						$temp['ncm'] 						= $this->_0200[$item['cod_item']]['ncm'];
						$temp['cfop'] 					= $item['cfop'];
						$temp['ind_oper'] 			= $nota['ind_oper'] == '0' ? 'Entrada' : 'Saída';
						$temp['num_item'] 			= $item['num_item'];
						// $temp['itens_nota'] = count($this->_C170[$chave]);
						$temp['vl_item'] 				= empty($item['vl_item']) ? 0 : $item['vl_item'];
						$temp['vl_desc'] 				= empty($item['vl_desc']) ? 0 : $item['vl_desc'];
						$temp['vl_base'] 				= $temp['vl_item'] - $temp['vl_desc'];
						$resultado 							= $this->calcularValoresMensais($resultado, $anoMes, 'C', ['vl_final_pis' => 0, 'vl_final_cofins' => 0, 'vl_calc_final_pis' => 0, 'vl_calc_final_cofins' => 0]);
						$resultado[$anoMes]['bruto'] += $temp['vl_base'];
						//log::gravaLog('emanuel', $anoMes . '-' . $temp['vl_base']);
						$temp['aliq_pis'] 			= $this->_ncm[$this->_0200[$item['cod_item']]['ncm']]['aliq_pis'];
						$temp['aliq_cofins'] 		= $this->_ncm[$this->_0200[$item['cod_item']]['ncm']]['aliq_cofins'];
						$temp['vl_final_pis'] 	= $temp['vl_base'] * $temp['aliq_pis'] / 100;
						$temp['vl_final_cofins'] 			= $temp['vl_base'] * $temp['aliq_cofins'] / 100;
						$temp['vl_calc_final_pis'] 		= $this->calculaValorFinal($temp['vl_base'], $temp['aliq_pis'], $this->_cfop[$item['cfop']]['tipo']);
						$temp['vl_calc_final_cofins'] = $this->calculaValorFinal($temp['vl_base'], $temp['aliq_cofins'], $this->_cfop[$item['cfop']]['tipo']);
						$temp['selecionado'] 		= 'S';
						$temp['qtd'] 						= $item['qtd'];
						$temp['cod_item']				= $item['cod_item'];
						$temp['filial'] 				= $nota['filial'];
						$temp['cnpj_forn']			= isset($this->_0150[$nota['cod_part']]['cnpj']) ? $this->_0150[$nota['cod_part']]['cnpj'] : $this->_0140[$nota['cod_part']]['cnpj'];
						// print_r($this->_0140);
						// die();x

						//Calcula valores brutos e separa industria de comercio

						$tipo = $this->_cfop[$item['cfop']]['tipo'];
						$resultado = $this->calcularValoresMensais($resultado, $anoMes, $tipo, $temp);
						// print_r($temp);

						$temp_notas[] = $temp;
					}
					//se esse $resultado não estiver com os valores criados não vai gerar os anoMes nem os valores separados
				}
				//conta quantos items entraram no ncm
				// print_r($temp_notas);
				if (count($temp_notas) > 0) {
					foreach ($temp_notas as $tn) {
						$tn['itens_nota'] = count($temp_notas) . '/' . count($this->_C170[$chave]);
						$ret['dados_item'][] = $tn;
					}
				}
			}
		}
		$ret['resultado'] = $resultado;
		if(isset($ret['dados_item'])){
			$this->salvarResultado($ret['dados_item']);
		}
		$this->getComplianceMensal($resultado); // resumo
		
		$this->realiza_copia_auditoria();
		
		return $ret;
	}

	public function resumoCompliance($arquivo)
	{
		$ret = ''; //abrir arquivo em php com a função file
		$dados = [];
		$resultado = [];
		$this->_colunas;

		$handle = fopen($arquivo, 'r');
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
					$temp = [];
					$temp['data_emi']             = $sep[3];
					$temp['cfop']                 = $sep[6];
					$temp['vl_base']              = $sep[12];
					$temp['vl_final_pis']         = $sep[15];
					$temp['vl_final_cofins']      = $sep[16];
					$temp['vl_calc_final_pis']    = $sep[17];
					$temp['vl_calc_final_cofins'] = $sep[18];

					$dados[] = $temp;
				}
			}
		}
		fclose($handle);

		foreach ($dados as $dado) {
			$temp = [];
			$anoMes = substr($dado['data_emi'], 0, 6);
			$tipo = $this->_cfop[$dado['cfop']]['tipo'];
			$resultado = $this->calcularValoresMensais($resultado, $anoMes, 'C', ['vl_final_pis' => 0, 'vl_final_cofins' => 0, 'vl_calc_final_pis' => 0, 'vl_calc_final_cofins' => 0]);
			$temp['vl_base'] = $dado['vl_base'];
			$resultado[$anoMes]['bruto'] += $dado['vl_base'];
			$temp['vl_final_pis'] = $dado['vl_final_pis'];
			$temp['vl_final_cofins'] = $dado['vl_final_cofins'];
			$temp['vl_calc_final_pis'] = $dado['vl_calc_final_pis'];
			$temp['vl_calc_final_cofins'] = $dado['vl_calc_final_cofins'];

			$resultado = $this->calcularValoresMensais($resultado, $anoMes, $tipo, $temp);
		}

		$this->getComplianceMensal($resultado);
		return $ret;
	}

	public function calcularValoresMensais($resultado, $anoMes, $tipo, $linha)
	{
		// var_dump($anoMes);
		if (!isset($resultado[$anoMes])) {
			$resultado[$anoMes]['vl_final_C_pis'] = 0;
			$resultado[$anoMes]['vl_final_C_cofins'] = 0;
			$resultado[$anoMes]['vl_calc_final_C_pis'] = 0;
			$resultado[$anoMes]['vl_calc_final_C_cofins'] = 0;
			$resultado[$anoMes]['vl_final_I_pis'] = 0;
			$resultado[$anoMes]['vl_final_I_cofins'] = 0;
			$resultado[$anoMes]['vl_calc_final_I_pis'] = 0;
			$resultado[$anoMes]['vl_calc_final_I_cofins'] = 0;
			$resultado[$anoMes]['bruto'] = 0;

			// var_dump($resultado[$anoMes]);
		}
		// echo $resultado[$anoMes]['vl_final_' . $tipo . '_pis'] 					. ' ' . $linha['vl_final_pis'] . '<br><br>' . "\n";
		$resultado[$anoMes]['vl_final_' . $tipo . '_pis'] 					+= round($linha['vl_final_pis'], 2);
		$resultado[$anoMes]['vl_final_' . $tipo . '_cofins'] 				+= round($linha['vl_final_cofins'], 2);
		$resultado[$anoMes]['vl_calc_final_' . $tipo . '_pis'] 			+= $linha['vl_calc_final_pis'];
		$resultado[$anoMes]['vl_calc_final_' . $tipo . '_cofins'] 	+= $linha['vl_calc_final_cofins'];
		// echo $anoMes;
		return $resultado;
	}

	private function calculaValorFinal($valor, $aliquota, $tipo)
	{
		if ($tipo == 'I') {
			return round((($valor * ($aliquota / 100) / 3) * 2), 2);
		} else if ($tipo == 'C') {
			return round(($valor * ($aliquota / 100) / 3), 2);
		} else {
			return 0;
		}
	}

	private function salvarResultado($dados)
	{
		$arquivo = fopen($this->_path . 'arquivos/resultado.vert', 'w');
		foreach ($dados as $dado) {
			$temp = [];
			foreach ($this->_colunas as $coluna) {
				$temp[] = $dado[$coluna];
			}
			$linha = implode('|', $temp);
			fwrite($arquivo, $linha . "\n");
		}
	}

	private function setDados0140($arquivo)
	{
		$handle = fopen($arquivo, "r");

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
					if (count($sep) > 0) {
						$temp = [];
						$temp['cod_part'] = $sep[1];
						$temp['razao'] = $sep[2];
						$temp['cnpj'] = $sep[3];
						$this->_0140[$temp['cod_part']] = $temp;
					}
				}
			}
			fclose($handle);
		} else {
			addPortalMensagem('Erro ao abrir o arquivo 0140', 'error');
		}
		return;
	}

	private function setDados0150($arquivo)
	{
		$handle = fopen($arquivo, "r");

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
					if (count($sep) > 0) {
						$temp = [];
						$temp['cod_part'] = $sep[1] . '';
						$temp['razao'] = $sep[2];
						$temp['cnpj'] = $sep[3];
						$this->_0150[$temp['cod_part']] = $temp;
					}
				}
			}
			fclose($handle);
		} else {
			addPortalMensagem('Erro ao abrir o arquivo 0150', 'error');
		}
		return;
	}

	private function setDados0200($arquivo)
	{
		$handle = fopen($arquivo, "r");

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
					if (count($sep) > 0) {
						$temp = [];
						$temp['cod_item'] = $sep[1] . '';
						$temp['descr_item'] = $sep[2];
						$temp['ncm'] = $sep[3];
						$this->_0200[$temp['cod_item']] = $temp;
					}
				}
			}
			fclose($handle);
		} else {
			addPortalMensagem('Erro ao abrir o arquivo 0200', 'error');
		}
		return;
	}

	private function setDadosC100($arquivo)
	{
		$handle = fopen($arquivo, "r");
		
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
					if (count($sep) > 0 && $sep[0] == 'C100') {
						$temp = [];
						$temp['ind_oper'] = $sep[1];
						$temp['cod_part'] = $sep[2];
						$temp['num_doc'] = $sep[3];
						$temp['chv_nfe'] = $sep[4];
						$temp['data_emi'] = $sep[5];
						$temp['total_bnf'] = $sep[6];
						$temp['filial'] = $sep[7];
						
						//Verifica se a nota está dentro do periodo de analise
						$processa = true;
						if(!empty($this->_contrato_dados['apura_ini']) && $this->_contrato_dados['apura_ini'] > $temp['data_emi']){
							$processa = false;
						}
						if(!empty($this->_contrato_dados['apura_fim']) && $this->_contrato_dados['apura_fim'] < $temp['data_emi']){
							$processa = false;
						}
						
						if($processa){
							$this->_C100[$temp['chv_nfe']] = $temp;
						}
					}
				}
			}
			fclose($handle);
		} else {
			addPortalMensagem('Erro ao abrir o arquivo C100', 'error');
		}
		return;
	}
	
	private function setDadosC170($arquivo)
	{
		$handle = fopen($arquivo, "r");
		
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
					if (count($sep) > 0 && $sep[0] == 'C170') {
						// echo $linha . '<br>' . "\n";
						$temp = [];
						$temp['num_item'] 	= $sep[1];
						$temp['cod_item'] 	= $sep[2];
						$temp['qtd'] 		= $sep[3];
						$temp['vl_item'] 	= $sep[4];
						$temp['vl_desc'] 	= $sep[5];
						$temp['cfop'] 		= $sep[6];
						$temp['cst'] 		= $sep[7];
						$temp['aliq_pis'] 	= $sep[8];
						$temp['aliq_cofins']= $sep[9];
						$temp['chv_nfe'] 	= $sep[10];
						
						//Carrega somente os itens das notas dentro do período de análise
						if(isset($this->_C100[$temp['chv_nfe']])){
							$this->_C170[substr($temp['chv_nfe'], 0, 43)][] = $temp;
						}
					}
				}
			}
			fclose($handle);
		} else {
			addPortalMensagem('Erro ao abrir o arquivo C170', 'error');
		}
		return;
	}
	private function setDados()
	{
		$arquivo = $this->_path . 'arquivos' . DIRECTORY_SEPARATOR;

		if (is_file($arquivo . '0140.vert')) {
			$this->setDados0140($arquivo . '0140.vert');
		}
		if (is_file($arquivo . '0150.vert')) {
			$this->setDados0150($arquivo . '0150.vert');
		}
		if (is_file($arquivo . '0200.vert')) {
			$this->setDados0200($arquivo . '0200.vert');
		}
		if (is_file($arquivo . 'C100.vert')) {
			$this->setDadosC100($arquivo . 'C100.vert');
			$this->setDadosC170($arquivo . 'C100.vert');
		}

		// print_r($this->_0140);
		// print_r($this->_0150);
		// print_r($this->_0200);
		// print_r($this->_C100);
		// print_r($this->_C170);
	}
	
	private function realiza_copia_auditoria(){
		$arquivos = [];
		$arquivos[] = $this->_path . 'arquivos/resultado.vert';
		$arquivos[] = $this->_path . 'arquivos/resumoCompliance.vert';

		$novos = [];
		$novos[] = $this->_path . 'arquivos/resultado_orig.vert';
		$novos[] = $this->_path . 'arquivos/resumoCompliance_orig.vert';
		
		foreach ($arquivos as$key => $arquivo) {
			if(is_file($arquivo) && !is_file($novos[$key])){
				copy($arquivo, $novos[$key]);
			}
		}
		
	}
	// ------------------------------------------------------------------------------ UTEIS ------------------
	private function getNCM()
	{
		$sql = "SELECT ncm, aliq_pis, aliq_cofins FROM mgt_ncm WHERE ativo = 'S'";
		$rows = query($sql);
		foreach ($rows as $row) {
			$temp = [];
			$temp['ncm'] = $row['ncm'];
			$temp['aliq_pis'] = $row['aliq_pis'];
			$temp['aliq_cofins'] = $row['aliq_cofins'];
			$this->_ncm[$temp['ncm']] = $temp;
		}
	}

	private function getCFOP()
	{
		$sql = "SELECT cfop, tipo FROM mgt_cfop WHERE ativo = 'S' and tipo in('I', 'C')";
		$rows = query($sql);
		foreach ($rows as $row) {
			$temp = [];
			$temp['cfop'] = $row['cfop'];
			$temp['tipo'] = $row['tipo'];
			$this->_cfop[$temp['cfop']] = $temp;
		}
	}

	public function gerarPlanilha()
	{
		$dados = $this->getDados();

		// print_r($dados);
	}
}
