<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class gerar_excel
{

	private $_path;

	private $_excel;

	private $_excelArquivo;

	//Indices de ws excel
	private $_indicesWS = [];

	public function __construct($cnpj, $contrato)
	{
		global $config;

		$this->_cnpj = $cnpj;

		$this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;

		$this->_excelArquivo = $this->_path . 'arquivos' . DIRECTORY_SEPARATOR . $cnpj . '_' . $contrato . '.xlsx';
	}

	public function setPlanilha()
	{
		$ret = [];

		$meses = array(
			'01' => 'Jan',
			'02' => 'Fev',
			'03' => 'Mar',
			'04' => 'Abr',
			'05' => 'Mai',
			'06' => 'Jun',
			'07' => 'Jul',
			'08' => 'Ago',
			'09' => 'Set',
			'10' => 'Out',
			'11' => 'Nov',
			'12' => 'Dez',
		);

		$files = glob($this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'resumoCompliance.vert');

		if (count($files) > 0) {
			$total_pis = 0;
			$total_cofins = 0;
			$total = 0;
			foreach ($files as $file) {
				$handle = fopen($file, "r");
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
							$mes = substr($sep[0], 4, 2);
							$ano = substr($sep[0], 0, 4);

							$pis = $sep[4] + $sep[8];
							$cofins = $sep[5] + $sep[9];
							$pis_cofins = $pis + $cofins;

							$param = [];
							$param['competencia'] = $mes . '/' . $ano;
							$param['pis'] = number_format($pis, 2, ',', '.');
							$param['cofins'] = number_format($cofins, 2, ',', '.');
							$param['credito'] = number_format($pis_cofins, 2, ',', '.');
							$ret[$ano . $mes] = $param;

							$total_pis += $pis;
							$total_cofins += $cofins;
							$total += $pis_cofins;
						}
					}
				}
			}
		}

		ksort($ret);

		$param = [];
		$param['competencia'] = 'TOTAL';
		$param['pis'] = number_format($total_pis, 2, ',', '.');
		$param['cofins'] = number_format($total_cofins, 2, ',', '.');
		$param['credito'] = number_format($total, 2, ',', '.');
		$ret[] = $param;

		// print_r($ret);
		$this->geraExcel($ret);
	}

	private function geraExcel($dados)
	{
		$this->_excel = new excel_marpa($this->_excelArquivo);

		$cab = [];
		$campos = [];
		$tipos = [];

		$cab[] = 'Competência';
		$campos[] = 'competencia';
		$tipos[] = 'T';

		$cab[] = 'PIS';
		$campos[] = 'pis';
		$tipos[] = 'T';

		$cab[] = 'COFINS';
		$campos[] = 'cofins';
		$tipos[] = 'T';

		$cab[] = 'Crédito à restituir';
		$campos[] = 'credito';
		$tipos[] = 'T';

		// $dados = array('0140' => 'rafa', '0150' => 'luis', 'C100' => 'igor', '0200' => 'thiel', 'C170' => 'emanoel');

		// $dados2 = array($dados);

		$this->_excel->setDados($cab, $dados, $campos, $tipos);

		$this->_excel->grava();
	}
}
