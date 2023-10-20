<?php 
/*
* Data Criação: 02/07/2014 - 15:58:49
* Autor: Thiel
*
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class campanhacli{
	var $_relatorio;
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	private $_programa = '';
	
	
	function __construct(){
		set_time_limit(0);
		
		$this->_programa = 'campanhaCli';
		$this->_relatorio = new relatorio01(array('programa' => $this->_programa));
		$this->_relatorio->addColuna(array('campo' => 'cli'			, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'cliente'		, 'etiqueta' => 'Cliente'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'sup'			, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'supnome'		, 'etiqueta' => 'Regiao'		, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vend'		, 'etiqueta' => 'Cod'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vendnome'	, 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));

		$this->_relatorio->addColuna(array('campo' => 'vendCli'		, 'etiqueta' => 'Cod ERC Cli'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => 'vendnomeCli'	, 'etiqueta' => 'ERC Cliente'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
		
		$this->_relatorio->addColuna(array('campo' => 'mesano'	, 'etiqueta' => 'Mes/Ano'			, 'tipo' => 'T', 'width' => 200, 'posicao' => 'C'));
		
		$this->_relatorio->addColuna(array('campo' => 'v0'	, 'etiqueta' => 'Venda Total'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p0'	, 'etiqueta' => 'Pedidos Total'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'v1'	, 'etiqueta' => 'Venda PDA'				, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p1'	, 'etiqueta' => 'Pedidos PDA'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'v2'	, 'etiqueta' => 'Venda TMKT'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p2'	, 'etiqueta' => 'Pedidos TMKT'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'v3'	, 'etiqueta' => 'Venda Ped.Elet.'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p3'	, 'etiqueta' => 'Pedidos Ped.Elet.'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'v4'	, 'etiqueta' => 'Venda Op.Logist.'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p4'	, 'etiqueta' => 'Pedidos Op.Logist.'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'v5'	, 'etiqueta' => 'Venda Balcao'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p5'	, 'etiqueta' => 'Pedidos Balcao'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'v6'	, 'etiqueta' => 'Venda CallCenter'		, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p6'	, 'etiqueta' => 'Pedidos CallCenter'	, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		$this->_relatorio->addColuna(array('campo' => 'v7'	, 'etiqueta' => 'Venda WEB'				, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		$this->_relatorio->addColuna(array('campo' => 'p7'	, 'etiqueta' => 'Pedidos WEB'			, 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){
		$filtro = $this->_relatorio->getFiltro();
		
		$dtDe = $filtro['DATAINI'];
		$dtAte = $filtro['DATAFIM'];
		
		$titulo = 'Campanha de Vendas';

		if(!empty($dtDe) && !empty($dtAte)) {
			$titulo .= ". Período: ".datas::dataS2D($dtDe)." a ".datas::dataS2D($dtAte);
		}

		$this->_relatorio->setTitulo($titulo);
		if(!$this->_relatorio->getPrimeira() ){
			if(!empty($dtDe) && !empty($dtAte)) {
				$dados = $this->getDados($dtDe, $dtAte);
			} else {
				$dados = [];
			}
	
			$this->_relatorio->setDados($dados);
			$this->_relatorio->setToExcel(true);
		}
		return $this->_relatorio . '';
	
	}

	function schedule($param){
	
	}

	function getDados($dtDe, $dtAte){
		$ret = array();
		$dados = array();

		$dataini = datas::dataS2D($dtDe);
		$datafim = datas::dataS2D($dtAte);
		$mesano = substr($dataini, 3, 7);
		
		$sql = " 
select  
        pcsuperv.codsupervisor, -- 0
        pcsuperv.nome, -- 1
        pcusuari.codusur, -- 2
        pcusuari.nome, -- 3
        CASE 
            WHEN pcpedc.origemped = 'B' THEN '5'
            WHEN pcpedc.origemped = 'C' THEN '6'
            WHEN pcpedc.origemped = 'T' THEN '2'
            WHEN pcpedc.origemped = 'F' AND pcpedc.tipofv IS NULL THEN '1'
            WHEN pcpedc.origemped = 'F' AND pcpedc.tipofv = 'OL' THEN '4'
            WHEN pcpedc.origemped = 'F' AND pcpedc.tipofv = 'PE' THEN '3'
			WHEN PCPEDC.origemped = 'W' THEN '7'
        END ORIGEM, -- 4
        SUM((pcmov.punit - pcmov.st)* pcmov.qt) VLVENDA, -- 5
        COUNT(DISTINCT pcpedc.numped) PEDIDOS, -- 6
        pcnfsaid.codcli, -- 7
        pcclient.cliente -- 8
from pcnfsaid, pcmov, pcpedc, pcclient, pcsuperv, pcusuari
where pcnfsaid.numtransvenda = pcmov.numtransvenda
    and pcnfsaid.dtcancel is NULL
    and pcmov.dtcancel is null
    and pcnfsaid.numped = pcpedc.numped
    and pcpedc.dtcancel is null
    --and pcnfsaid.DTSAIDA >=  TO_DATE('01/06/2015', 'DD/MM/YYYY') and pcnfsaid.DTSAIDA <=  TO_DATE('30/06/2015', 'DD/MM/YYYY')
    and pcnfsaid.DTSAIDA >=  TO_DATE('$dataini', 'DD/MM/YYYY') and pcnfsaid.DTSAIDA <=  TO_DATE('$datafim', 'DD/MM/YYYY')
	--and pcnfsaid.codcli = 6959
    AND pcnfsaid.CODFISCAL NOT IN (522, 622, 722, 532, 632, 732)
    AND pcnfsaid.CONDVENDA NOT IN (4, 8, 10, 13, 20, 98, 99)
    and pcpedc.CONDVENDA NOT IN (4, 8, 10, 13, 20, 98, 99)
    and pcpedc.DTCANCEL IS NULL  
    and pcpedc.POSICAO = 'F'
    and pcnfsaid.codcli = pcclient.codcli (+)
    and pcclient.codusur1 = pcusuari.codusur (+)
    and pcusuari.codsupervisor = pcsuperv.codsupervisor (+)
GROUP by pcnfsaid.codcli,
         pcpedc.origemped,
         pcpedc.tipofv,
         pcsuperv.codsupervisor,
         pcsuperv.nome,
         pcusuari.codusur,
         pcusuari.nome,
         pcnfsaid.codcli,
         pcclient.cliente
				
		";
		$rows = query4($sql);
//echo "$sql \n";
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$chave = $row[7];
				$dados[$chave]['cli'] = $row[7];
				$dados[$chave]['cliente'] = $row[8];
				$dados[$chave]['Vcod'] = $row[2];
				$dados[$chave]['Vnome'] = $row[3];
				$dados[$chave]['Scod'] = $row[0];
				$dados[$chave]['Snome'] = $row[1];
				$dados[$chave]['venda']['valor'][$row[4]] = $row[5];
				$dados[$chave]['venda']['ped'][$row[4]] = $row[6];
			}
		}
//print_r($ret);
		
		foreach ($dados as $codcli => $vend){
			$ercCad = $this->getERCcliente($codcli);
//			$ercCodCad = '';
//			$ercNomeCad = '';
//			if($vend['Vcod'] != $ercCad['cod']){
				$ercCodCad = $ercCad['cod'];
				$ercNomeCad = $ercCad['nome'];
//			}
			$temp = array( 'cli' => $vend['cli'],
								'cliente' => $vend['cliente'],
								'sup' => $vend['Scod'],
								'supnome' => $vend['Snome'],
								'vend' => $vend['Vcod'],
								'vendnome' => $vend['Vnome'],
								'vendCli' => $ercCodCad,
								'vendnomeCli' => $ercNomeCad,
								'v0' => 0,
								'p0' => 0,
								'v1' => 0,
								'p1' => 0,
								'v2' => 0,
								'p2' => 0,
								'v3' => 0,
								'p3' => 0,
								'v4' => 0,
								'p4' => 0,
								'v5' => 0,
								'p5' => 0,
								'v6' => 0,
								'p6' => 0,
								'v7' => 0,
								'p7' => 0,
			);
			foreach ($vend['venda'] as $venda){
				//print_r($venda);
				$v = 0;
				$p = 0;
				foreach ($venda as $tipo => $valor){
					//echo $tipo.' '.$valor."\n";
//print_r($vend);
					$temp['v'.$tipo] = $vend['venda']['valor'][$tipo];
					$temp['p'.$tipo] = $vend['venda']['ped'][$tipo];
					$v += $vend['venda']['valor'][$tipo];
					$p += $vend['venda']['ped'][$tipo];
				}
				$temp['v0'] = $v;
				$temp['p0'] = $p;
				$temp['mesano'] = $mesano;
			}
			$ret[] = $temp;
		}
//print_r($dados);
		return $ret;	
	}
	
	private function getERCcliente($codcli){
		$ret = array('cod'=>'', 'nome'=>'');
		
		$sql = "SELECT CODUSUR1, PCUSUARI.NOME FROM PCCLIENT ,PCUSUARI WHERE CODCLI = $codcli AND PCCLIENT.CODUSUR1 = PCUSUARI.CODUSUR";
		$rows = query4($sql);
		if(isset($rows[0]['CODUSUR1'])){
			$ret['cod']  = $rows[0]['CODUSUR1'];
			$ret['nome'] = $rows[0]['NOME'];
		}
		return $ret;
	}
}