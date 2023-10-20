<?php
/*
 * Data Criacao 16 de ago de 2016
 * Autor: TWS - Alexandre Thiel
 *
 * Arquivo: class.ora_caixaPadrao.inc.php
 * 
 * Descricao: compara as mudan�as realizadas no cadastro de produtos
 * 			  campos: MULTIPLOCOMPRAS e QTUNITCX
 * 
 * 
 * Alterções:
 *            19/11/2018 - Emanuel - Migração para intranet2
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class caixaPadrao{
	var $_relatorio;
	
	var $funcoes_publicas = array(
		'index' 		=> true,
	);
	
	// Nome do Programa
	var $_programa = '';
	
	// Dados
	var $_dados;
	
	// Usuarios que altraram os produtos
	var $_usuarios;
	
	//Indica se é teste
	var $_teste;
	
	function __construct(){
		set_time_limit(0);
		
		$this->_teste = false;
		
		$this->_programa = '000002.caixapadrao';
		
		$param = [];
		$param['programa']	= $this->_programa;
		$this->_relatorio = new relatorio01($param);
		$this->_relatorio->addColuna(array('campo' => 'cod'			, 'etiqueta' => 'Cod'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'produto'		, 'etiqueta' => 'Produto'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'ca'			, 'etiqueta' => 'Caixa Atual'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'co'			, 'etiqueta' => 'Caixa Antiga'		, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'ma'			, 'etiqueta' => 'Multiplo Atual'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'mo'			, 'etiqueta' => 'Multiplo Antigo'	, 'tipo' => 'N', 'width' =>  80, 'posicao' => 'centro'));
		$this->_relatorio->addColuna(array('campo' => 'user'		, 'etiqueta' => 'Usuario'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'data'		, 'etiqueta' => 'Data'				, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$this->_relatorio->addColuna(array('campo' => 'dif'			, 'etiqueta' => ''					, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
		//ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data At�'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	}			
	
	function index(){
	}

	function schedule($param){
		$emails = str_replace(',', ';', $param);

		$titulo = 'Alteracao Caixa Padrao. Data: '.date('d/m/Y');
		
		$this->_relatorio->setTitulo($titulo);
		$this->getUsuario();
		$this->atualizaTabela();
		$this->atualizaDados();
		$this->getDados();
		$this->ajustaUsuarios();
		$this->atualizaOracle();
		
		$this->_relatorio->setDados($this->_dados);
		$this->_relatorio->setAuto(true);
		$this->_relatorio->setToExcel(true,'caixaPadrao'.date('d.m.Y'));
		
		$semana = date("N");
		if(!$this->_teste){
		    if($semana > 0 && $semana < 6){
			    $this->_relatorio->enviaEmail($emails,$titulo);
		    }
		}
		else{
		    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
		}
	}
	
	function ajustaUsuarios(){
		$nomes = array();
		$sql = "select codprod, NOME_GUERRA from pcprodut, pcempr where pcprodut.codfuncultalter = pcempr.MATRICULA (+)";
		$rows = query4($sql);
		foreach ($rows as $row){
			$nomes[$row[0]] = $row[1];
		}	
		foreach ($this->_dados as $i => $dado){
			if($dado['dif'] == '***' && $dado['user'] == ''){
				$this->_dados[$i]['user'] = $nomes[$dado['cod']];
			}
		}
		
	}
	
	function getUsuario(){
		$sql = "select
				    VALOROLD,
				    NOME_GUERRA,
				    CODPROD
				from 
				    pclogcadastro,
				    pcempr,
				    pcprodut
				where pclogcadastro.MATRICULAUSUARIO = pcempr.MATRICULA (+)
				    AND pclogcadastro.ROWIDCAMPO = pcprodut.ROWID 
				    AND nomeobjeto = 'PCPRODUT'
				    AND TRUNC(DATALOG) =  TRUNC(SYSDATE -1)
				    --AND (VALOROLD LIKE '%QTUNITCX%' OR VALOROLD LIKE '%MULTIPLOCOMPRAS%')
				order by datalog desc";
		
		$rows = query4($sql);
		foreach ($rows as $row){
			$this->_usuarios[$row[2]] = $row[1];
		}
	}
	
	function atualizaTabela(){
		$sql = "UPDATE gf_caixa_padrao SET caixa_old = caixa, multiplo_old = multiplo";
		query($sql);
		
		$sql = "UPDATE gf_caixa_padrao SET caixa = 0, multiplo = 0";
		query($sql);
		
	}

	function atualizaDados(){
		$sql = "SELECT CODPROD, NVL(QTUNITCX,0) CAIXA, NVL(MULTIPLOCOMPRAS,0) MULTIPLO, DESCRICAO FROM PCPRODUT ORDER BY CODPROD ";
		$rows = query4($sql);
//echo "$sql \n";
		
		if(count($rows) > 0){
			foreach($rows as $row){
				$this->updatetabela($row[0],$row[2],$row[1],$row[3]);
			}
		}
	}

	
	function updateTabela($cod, $multiplo, $caixa,$desc){
		$sql = "SELECT * FROM gf_caixa_padrao WHERE cod = $cod";
		$rows = query($sql);
		if(count($rows) > 0){
			$sql = "UPDATE gf_caixa_padrao SET caixa = $caixa, multiplo = $multiplo WHERE cod = $cod";
			query($sql);
		}else{
			$desc = str_replace("'", "�", $desc);
			$sql = "INSERT INTO gf_caixa_padrao (cod,caixa,multiplo,produto) VALUES ($cod, $caixa, $multiplo,'$desc')";
//echo "$sql <br>\n";
			query($sql);
		}
	}
	
	function getDados(){
		// Seleciona os que tem altera��o
		$sql = "SELECT * FROM gf_caixa_padrao WHERE caixa <> caixa_old OR multiplo <> multiplo_old ORDER BY cod";
		$rows = query($sql);
		
		if(count($rows) > 0){
			$dia = datas::dataS2D(datas::getDataDias(-1));
			foreach ($rows as $row){
				$temp = array();
				$temp['cod'] 	 = $row['cod'];
				$temp['produto'] = $row['produto'];
				$temp['ca'] 	 = $row['caixa'];
				$temp['co'] 	 = $row['caixa_old'];
				$temp['ma'] 	 = $row['multiplo'];
				$temp['mo'] 	 = $row['multiplo_old'];
				$temp['user']	 = isset($this->_usuarios[$row['cod']]) ? $this->_usuarios[$row['cod']] : '';
				$temp['data']	 = $dia;
				$temp['dif'] 	 = '***';
				
				$this->_dados[] = $temp;
			}
		}
		
		// Seleciona os que n�o tem altera��o
		$sql = "SELECT * FROM gf_caixa_padrao WHERE caixa = caixa_old OR multiplo = multiplo_old ORDER BY cod";
		$rows = query($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = array();
				$temp['cod'] 	 = $row['cod'];
				$temp['produto'] = $row['produto'];
				$temp['ca'] 	 = $row['caixa'];
				$temp['co'] 	 = $row['caixa_old'];
				$temp['ma'] 	 = $row['multiplo'];
				$temp['mo'] 	 = $row['multiplo_old'];
				$temp['user']	 = '';
				$temp['data']	 = '';
				$temp['dif'] 	 = '';
				
				$this->_dados[] = $temp;
			}
		}
	}
	
	/*
	 * Atualiza na tabela do oracle TWS_CAIXAPADRAO os alterados
	 */
	
	function atualizaOracle(){
		if(count($this->_dados) > 0 ){
			foreach ($this->_dados as $dado){
				if($dado['user'] != ''){
					$sql = "INSERT INTO TWS_CAIXAPADRAO (CODPROD,DTALT,CAIXA,CAIXA_OLD,MULTIPLO,MULTIPLO_OLD,USUARIO) VALUES (".$dado['cod'].",SYSDATE -1,".$dado['ca'].",".$dado['co'].",".$dado['ma'].",".$dado['mo'].",'".substr($dado['user'],0,30)."')";
					query4($sql);
				}
			}
		}
	}
}
/*/
create table tws_caixapadrao(
codprod INTEGER,
dtalt date,
caixa NUMBER(16,3),
caixa_old NUMBER(16,3),
multiplo NUMBER(16,3),
multiplo_old NUMBER(16,3)
)
/*/ 
