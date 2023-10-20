<?php
/*
* Data Criaï¿½ï¿½o: 27/08/2014 - 10:57:14
* Autor: Thiel
*
* Exportação da tabela de preço do cliente Angeloni
* Pode ser executado no portal ou via schedule (que está rodando todo o dia pela manhã e gravando os arquivos no FTP)
* 
* Alteração:
* 			11/03/19 - Inclusão de restrição aos fornecedores 18069, 15858 e 16165 (Gustavo/Jeniffer)
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class tabela24_pr{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	//Classe de exportacao do arquivo
	var $_integra;
	
	// Caminho dos arquivos gerados
	var $_path;
	
	// Nome dos arquivos
	var $_arq;

	//Log detalhado
	var $_log;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_log = true;
	}			
	
	function index(){
	}
	
	function schedule($param){
		sleep(30);
		
		$data = date('Ymd');
		//$data = '20160425';
		$dir 	= "/mnt/pedtemp/neogridangeloni/bkp/";
		$dirTemp = "";
		
		$this->_integra = new integratxt01('', 1);
		$this->setEstruturaInc();
		
		//$servidor = "edi.gauchafarma.com";
		$servidor = "10.0.0.107";
		$diretorio = "";
		$usuario = "neogridangeloni";
		$senha = "nwdM43jO";
		
		$conn_id = ftp_connect($servidor);
		
		if(!$conn_id){
			log::gravaLog("precosAngeloni", "Nao foi possivel conectar ao servidor FTP PR".$servidor);
			die("Nao foi possivel conectar a $servidor");
		}
		$login_result = ftp_login($conn_id, $usuario, $senha);
		$this->limpaFTP($conn_id);
		
		$dados = array();
		$dados = $this->getDados();
		$link = $this->geraArquivo($dados, $data, $data, 'F','PR');
		$fpPR = fopen($this->_path.$this->_arq, 'r');
		
		if (ftp_fput($conn_id, $this->_arq, $fpPR, FTP_ASCII)) {
			log::gravaLog("precosAngeloni", "Enviado PR com sucesso arquivo para o FTP: ".$this->_arq);
		} else {
			log::gravaLog("precosAngeloni", "Erro PR ao enviar arquivo para o FTP: ".$this->_arq);
		}
		
		fclose($fpPR);
		copy($this->_path.$this->_arq, $dir.$this->_arq.'_'.$data.'.txt');
		//copy($this->_path.$this->_arq, $config['temp'].$this->_arq.'_'.$data.'.txt');
		
		ftp_close($conn_id);
	}
	
	
	function geraArquivo($dados, $dtDe, $dtAte, $tipo,$uf){
		global $config;
		if($this->_log){
			log::gravaLog("precosAngeloni", "Executando geraArquivo PR: $dtDe, $dtAte, $tipo,$uf");
		}
		$dtDe = datas::dataS2D(str_replace('/', '', $dtDe),4,'');
		$dtAte = datas::dataS2D(str_replace('/', '', $dtAte),4,'');
//print_r($dados);
		
		$cnpj = '89735070000100';
		$hora = date('His');
		$arquivo = 'ImpTbPre'.$cnpj.$hora.'.txt';
		log::gravaLog("precosAngeloni", 'Seta diretorio saida PR');

		$this->_integra->setDiretorios(array('saida' => $config['temp']));
		
		$this->_path = $config['temp'];
		$this->_arq = $arquivo;
		
		log::gravaLog("precosAngeloni", 'Gera arquivo tipo 0 PR');
		$this->_integra->gravaArquivoTipo3(0, $arquivo, array(array('cnpj'=>$cnpj,'data'=>date('dmY'),'inivigencia'=>$dtDe,'fimvigencia'=>$dtAte	,'tipo'=>$tipo	,'uf'=>$uf)));
		if($this->_log){
			log::gravaLog("precosAngeloni", "Gerando tipo 0 PR $arquivo");
		}
		
		log::gravaLog("precosAngeloni", 'Prepara dados PR. Quant: '.count($dados));
		//Ajusta os precos (multiplica por 100 para ficar
		$i = 0;
		$ret = array();
		foreach($dados as $dado){
			$preco = getPrecoFabrica($dado['cod'],'PR');
			if($dado['cod'] == 5278) echo "Preco: $preco <br>\n";
			$ret[$i]['ean'] 		= $dado['ean'];
			$ret[$i]['descricao'] 	= $dado['descricao'];
			if($preco == 0 || $dado['precoTabela'] >  $dado['preco']){
				$ret[$i]['preco'] 		= (int)($dado['preco'] * 100);
				$ret[$i]['desconto'] 	= (int)($dado['desconto'] * 100);
			}else{
				//				$ret[$i]['preco'] 		= (int)($dado['preco'] * 100);
				//				$desconto = round(100-((100*$dado['precoTabela'])/$dado['preco']),2);
				//				$ret[$i]['desconto'] 	= (int)($desconto * 100);
				
				$ret[$i]['preco'] 		= (int)($preco * 100);
				$desconto = round(100-((100*$dado['precoTabela'])/$preco),2);
				$ret[$i]['desconto'] 	= (int)($desconto * 100);
			}
			$ret[$i]['icms'] 		= (int)($dado['icms'] * 100);
			$ret[$i]['mva'] 		= (int)($dado['mva'] * 100);
			$ret[$i]['pauta'] 		= 0;
			$ret[$i]['ipi'] 		= 0;
			$ret[$i]['valipi'] 		= 0;
			$ret[$i]['pis'] 		= (int)($dado['pis'] * 100);
			$ret[$i]['cofins'] 		= (int)($dado['cofins'] * 100);
			if($ret[$i]['desconto'] < 0){
				$ret[$i]['desconto'] = 0;
				$ret[$i]['preco'] = (int)($dado['precoTabela'] * 100);
			}
			$ret[$i]['estoque'] 	= $dado['estoque'];
			$i++;
		}
		$quant = count($ret);
		log::gravaLog("precosAngeloni", 'Dados Processados PR : '.$quant);
		log::gravaLog("precosAngeloni", 'Gera tipo 2 PR');
		$this->_integra->gravaArquivoTipo3(2, $arquivo, $ret);
		if($this->_log){
			log::gravaLog("precosAngeloni", "Gerando tipo 2 PR $arquivo");
		}
		
		log::gravaLog("precosAngeloni", 'Gera tipo 3 PR');
		$this->_integra->gravaArquivoTipo3(3, $arquivo, array(array('quant'=>$quant)));
		if($this->_log){
			log::gravaLog("precosAngeloni", "Gerando tipo 3 PR $arquivo");
		}

	}

	function setEstruturaInc(){
		$param = array();
		$param['var'] = array('cnpj'	,'data'	,'inivigencia'	,'fimvigencia'	,'tipo'	,'uf');
		$param['pos'] = array(1			,15		,23				,31				,39		,40);
		$param['tam'] = array(14		,8		,8				,8				,1		,2);
		$param['fixo']= array(0);
		
		$this->_integra->setEstrutRet($param);
//print_r($param);		
		$param = array();
		$param['var'] = array("ean"	,"preco"	,"desconto"	,"icms"	,"mva"	,"pauta"	,"ipi"	,"valipi"	,"pis"	,"cofins", 'estoque');
		$param['pos'] = array(1		,15			,22			,26		,30		,35			,42		,47			,54		,58      , 62);
		$param['tam'] = array(14	,7			,4			,4		,5		,7			,5		,7			,4		,4       , 6);
		$param['preencher'] = array('0','0','0','0','0','0','0','0','0','0','0');
		$param['alin'] = array('E','E','E','E','E','E','E','E','E','E','E');
		$param['fixo']= array(2);
		
		$this->_integra->setEstrutRet($param);
//print_r($param);		
		$param = array();
		$param['var'] = array("quant");
		$param['pos'] = array(1);
		$param['tam'] = array(6);
		$param['fixo']= array(3);
		$param['alin'] = array('E');
		$param['preencher'] = array('0');
//print_r($param);	
		$this->_integra->setEstrutRet($param);
	}

	
	function limpaFTP($conn_id){
		$arquivos = ftp_nlist($conn_id, ".");
		foreach ($arquivos as $arq){
			if($arq != '.' && $arq != '..' && strpos($arq, '_OLD')){
				ftp_delete($conn_id, $arq);
			}
		}
	}
	
	function getDados(){
	    $ret = array();
	    $tabela = 24;
	    $sql = "
SELECT
    pcprodut.codauxiliar,
    pcprodut.descricao,
    ROUND(pctabpr.pvenda1,2) PRECO,
    case
        WHEN PCPRODUT.utilizaprecomaxconsumidor = 'S' THEN (round(100 - ((pctabpr.pvenda1 * 100)/pcprodut.custorep),2))
        WHEN PCPRODUT.utilizaprecomaxconsumidor = 'N' THEN 0
    end DESCONTO,
    PCTABPR.CODTRIBPISCOFINS PIS,
    pctribut.codicm,
    pctribut.ivafonte,
    pctabpr.codprod,
    PCPRODUT.CUSTOREP,
    (NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0)) QUANT
FROM
    pctabpr,
    pcprodut,
    pctribut,
    pcest
WHERE pctabpr.numregiao = $tabela
    AND pctabpr.codprod = pcprodut.codprod (+)
    and pctabpr.codst = pctribut.codst (+)
    AND pctabpr.codprod = pcest.codprod (+)
    and pcest.codfilial = 1
    AND pcprodut.dtexclusao is null
    and pcprodut.obs2 <> 'FL'
    and pcprodut.codfornec NOT IN (18069, 15858, 16165, 949)
	AND pcprodut.codprod IN (select pcest.codprod from pcest where (PCEST.QTESTGER - PCEST.QTINDENIZ - PCEST.QTBLOQUEADA) > 0 AND PCEST.CODFILIAL = 1)
ORDER BY pcprodut.codprod
		";
	    //echo "$sql <br>\n";
	    $rows = query4($sql);
	    //print_r($rows);
	    if(count($rows) > 0){
	        $i = 0;
	        foreach($rows as $row){
	            if($row[2] > 0){
	                $ret[$i]['ean'] 		= $row[0];
	                $ret[$i]['descricao'] 	= $row[1];
	                if($row[3] == 0){
	                    $ret[$i]['preco'] 		= $row[2];
	                }else{
	                    $ret[$i]['preco'] 		= $row[8];
	                }
	                $ret[$i]['desconto'] 	= $row[3];
	                $ret[$i]['icms'] 		= $row[5];
	                $ret[$i]['mva'] 		= $row[6];
	                $ret[$i]['pauta'] 		= 0;
	                $ret[$i]['ipi'] 		= 0;
	                $ret[$i]['valipi'] 		= 0;
	                if($row[4] <> 3){
	                    $ret[$i]['pis'] 	= 0;
	                    $ret[$i]['cofins'] 	= 0;
	                }else{
	                    $ret[$i]['pis'] 	= 1.65;
	                    $ret[$i]['cofins'] 	= 7.60;
	                }
	                $ret[$i]['cod']			= $row[7];
	                $ret[$i]['precoTabela']	= $row[2];
	                $ret[$i]['estoque']	    = $row[9] > 200 ? 200 : $row[9];
	                $i++;
	            }
	        }
	    }
	    //print_r($ret);
	    return $ret;	
	}

	function getPreco($cod,$uf){
		$ret = 0;
		if($uf == 'PR'){
			$sql = "SELECT pf12 FROM gf_pf_17_12 WHERE cod = $cod";
		}else{
			$sql = "SELECT pf17 FROM gf_pf_17_12 WHERE cod = $cod";
		}
		$rows = query($sql);
		if(count($rows) > 0){
			$ret = $rows[0][0];
		}
	
		return $ret;
	}
}