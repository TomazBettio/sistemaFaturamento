<?php
/*
 * Data Criacao: 10/01/2023
 * Autor: Verticais - Rafael Postal
 *
 * Descricao: Implementando ou alterando no banco o valor bruto da nota e o total de arquivos processados p/mês
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class monofasico_set_arquivos {
    // Pasta raiz
    private $_path;

    // CNPj cliente
    private $_cnpj;

    // Contrato cliente
    private $_contrato;

    // ID do cliente
    private $_id;

    // Quantidade de arquivos processados
    private $_processados = [];

    // Quantidade de arquivos com erro
    private $_erros = [];
    
    //Arquivo fe log do contrato
    private $_logContrato;

    public function __construct($cnpj, $contrato, $id){
        global $config;
        $this->_path = $config['pathUpdMonofasico'] . $cnpj . '_' . $contrato . DIRECTORY_SEPARATOR;

        $this->_cnpj = $cnpj;
        $this->_contrato = $contrato;
        $this->_id = $id;
        
        $this->_logContrato = 'monofasico_processa'.DIRECTORY_SEPARATOR.$cnpj . '_' . $contrato;
    }

    public function setDados() {
        $arquivos_xml = $this->getArquivos($this->_path . 'processados_xml', 'xml');
        $arquivos_sped = $this->getArquivos($this->_path . 'processados_sped', 'txt');
        
        if(count($arquivos_xml) > 0) {
            foreach($arquivos_xml as $arquivo) {
                $this->getQuantidadeXmlP($arquivo);
            }
        }
        if(count($arquivos_sped) > 0) {
            foreach($arquivos_sped as $arquivo) {
                $mes_ano = $this->getQuantidadeSped($this->_path . 'processados_sped' . DIRECTORY_SEPARATOR . $arquivo);

                if(!isset($this->_processados[$mes_ano])) {
                    $this->_processados[$mes_ano] = 0;
                }
                $this->_processados[$mes_ano]++;

                // if(!isset($this->_erros[$mes_ano])) {
                //     $this->_erros[$mes_ano] = 0;
                // }
            }
        }

        $erros_xml = $this->getArquivos($this->_path . 'erro', 'xml');
        $erros_sped = $this->getArquivos($this->_path . 'erro', 'txt');

        if(count($erros_xml) > 0) {
            foreach($erros_xml as $arquivo) {
                $this->getQuantidadeXmlE($arquivo);
            }
        }
        if(count($erros_sped) > 0) {
            foreach($erros_sped as $arquivo) {
                $mes_ano = $this->getQuantidadeSped($this->_path . 'erro' . DIRECTORY_SEPARATOR . $arquivo);

                if(!isset($this->_erros[$mes_ano])) {
                    $this->_erros[$mes_ano] = 0;
                }
                $this->_erros[$mes_ano]++;
            }
        }

// print_r($dados);
// print_r($this->_processados);
// print_r($this->_erros);

        $dados = $this->getDados();

        if(count($dados) > 0) {
            foreach($dados as $dado) {
                $sql = "SELECT * from mgt_monofasico_arquivos WHERE id_monofasico = $this->_id AND data = '".$dado['data']."'";
                $row = queryMF($sql);
                
                if(!is_array($row) || count($row) == 0) {
                    $sql = montaSQL($dado, 'mgt_monofasico_arquivos');
                    queryMF($sql);

                } else if($row[0]['alterar'] == 'S') { // $row[0]['valor_bruto'] != $dado['valor_bruto'] || $row[0]['arq_autorizados'] != $dado['arq_autorizados'] || $row[0]['arq_negados'] != $dado['arq_negados']
                    $sql = montaSQL($dado, 'mgt_monofasico_arquivos', 'UPDATE', "id_monofasico = $this->_id AND data = '".$dado['data']."'");
                    queryMF($sql);
                }
            }
        }
    }

    private function getDados() {
        $ret = [];

        $arquivo = $this->_path . 'arquivos' . DIRECTORY_SEPARATOR . 'resumoCompliance.vert';

        if(file_exists($arquivo)) {
            $arquivo = file($arquivo);
            foreach($arquivo as $linha) {
                $sep = explode('|', $linha);

                $temp = [];
                $temp['id_monofasico'] = $this->_id;
                $temp['data'] = $sep[0];
                $temp['valor_bruto'] = $sep[1];
                $temp['arq_autorizados'] = $this->_processados[$sep[0]] ?? 0;
                $temp['arq_negados'] = $this->_erros[$sep[0]] ?? 0;
                $temp['arq_total'] = $temp['arq_autorizados'] + $temp['arq_negados'];
                $temp['alterar'] = 'N';
                $ret[] = $temp;
            }
        }

        return $ret;
    }

    private function getQuantidadeXmlP($arquivo) {
        $files = glob($this->_path . 'processados_xml' . DIRECTORY_SEPARATOR . $arquivo);

        if(count($files) > 0) {
			foreach ($files as $file) {
				$xml = carregaXMLnota($file, $this->_logContrato);
				
				if($xml !== false){
//print_r($xml);die();
	                $xml = $xml->procNFe ?? $xml;
	
	                $dhEmi = $xml->NFe->infNFe->ide->dhEmi ?? $xml->infCFe->ide->dEmi;
	                $mes_ano = str_replace('-', '', substr($dhEmi, 0, 10));
	                $mes_ano = substr($mes_ano, 0, 6);
	                // $mes_ano = substr($dhEmi, 4, 2) .'/'.substr($dhEmi, 0, 4);
	
	                if(!isset($this->_processados[$mes_ano])) {
	                    $this->_processados[$mes_ano] = 0;
	                }
	                $this->_processados[$mes_ano]++;

				}else{
					$erro = getAppVar('erro_imp_xml_monofasico');
//echo "\n<br> $erro <br>\n";
				}
			}
        }
    }

    private function getQuantidadeXmlE($arquivo) {
        $files = glob($this->_path . 'erro' . DIRECTORY_SEPARATOR . $arquivo);

        if(count($files) > 0) {
			foreach ($files as $file) {
				$xml = carregaXMLnota($file, $this->_logContrato);

                $xml = $xml->procNFe ?? $xml;

                $dhEmi = $xml->NFe->infNFe->ide->dhEmi ?? ''; 
                if($dhEmi != '') {
                    $dhEmi = str_replace('-', '', substr($dhEmi, 0, 10));
                    $mes_ano = substr($dhEmi, 0, 6);
                    // $mes_ano = substr($dhEmi, 4, 2) .'/'.substr($dhEmi, 0, 4);
    
                    if(!isset($this->_erros[$mes_ano])) {
                        $this->_erros[$mes_ano] = 0;
                    }
                    $this->_erros[$mes_ano]++;
                } else {
                    if(!isset($this->_erros['sem_data'])) {
                        $this->_erros['sem_data'] = 0;
                    }
                    $this->_erros['sem_data']++;
                }
            }
        }
    }

    private function getQuantidadeSped($arquivo) {
        set_time_limit(200);

        $files = glob($arquivo);

        if (count($files) > 0) {
            foreach ($files as $file) {
                $handle = fopen($file, "r");
                if ($handle) {
                    while (!feof($handle)) {
                        $linha = fgets($handle);
                        if (!empty($linha)) {
                            $sep = explode('|', $linha);
                            if (count($sep) > 1) {
                                if ($sep[1] == 'C100') {
                                    $mes_ano = datas::dataD2S($sep[11], '', '');
                                    $mes_ano = substr($mes_ano, 0, 6);
                                    // $mes_ano = substr($data, 4, 2) .'/'.substr($data, 0, 4);

                                    if(!empty($mes_ano)) {
                                        return $mes_ano;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function getArquivos($dir, $tipo) {
        $ret = [];
        $diretorio = dir($dir);
        
        if(!is_null($diretorio) && $diretorio !== false){
	        while ($arquivo = $diretorio->read()) {
	            $ext = ltrim(substr($arquivo, strrpos($arquivo, '.')), '.');
	            $ext = strtolower($ext);
	            if ($arquivo != '.' && $arquivo != '..' && $ext == $tipo) {
	                $ret[] = $arquivo;
	            }
	        }
        }else{
        	log::logApache("Problemas ao abir o diretorio $dir");
        }
        
        return $ret;
    }

}