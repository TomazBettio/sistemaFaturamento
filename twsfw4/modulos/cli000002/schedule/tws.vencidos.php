<?php
/*
 * Data Criação: 24/10/2014 - 14:54:01
 * Autor: Thiel
 *
 * Descrição: Relatório automático que envia titulos vencidos a mais de x dias (parametro)
 *
 * Alterações:
 * 16/07/18 - Neto/Márcio - Motificar para selecionar todos os códigos de cobrança
 * 16/11/18 - Emanuel     - Migração para intranet2
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);


class vencidos{
    var $_relatorio;
    var $funcoes_publicas = array(
        'index' 		=> true,
    );
    
    // Nome do Programa
    var $_programa = '';
    
    var $_teste;
    
    function __construct(){
        set_time_limit(0);
        $this->_teste = false;
        
        $this->_programa = '000002.pora_vencidos15';
        
        $param = [];
        $param['programa']	= $this->_programa;
        $this->_relatorio = new relatorio01($param);
        
        $this->_relatorio->addColuna(array('campo' => 'coord'	, 'etiqueta' => 'Regiao'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vend'	, 'etiqueta' => 'ERC'			, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'CODIGO'		, 'tipo' => 'T', 'width' =>  80,  'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'CLIENTE'		, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'ativ'	, 'etiqueta' => 'ATIVIDADE'		, 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'titulo'	, 'etiqueta' => 'DUPLICATA'		, 'tipo' => 'T', 'width' =>  80,  'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'parcela'	, 'etiqueta' => 'PARCELA'		, 'tipo' => 'T', 'width' =>  80,  'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'valor'	, 'etiqueta' => 'VALOR'			, 'tipo' => 'V', 'width' =>  100, 'posicao' => 'D'));
        $this->_relatorio->addColuna(array('campo' => 'emissao'	, 'etiqueta' => 'EMISSAO'		, 'tipo' => 'T', 'width' =>  80,  'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'venc'	, 'etiqueta' => 'VENCIMENTO'	, 'tipo' => 'T', 'width' =>  80,  'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'dias'	, 'etiqueta' => 'DIAS ATRASO'	, 'tipo' => 'N', 'width' =>  80,  'posicao' => 'centro'));
        $this->_relatorio->addColuna(array('campo' => 'cidade'	, 'etiqueta' => 'CIDADE'		, 'tipo' => 'T', 'width' => 150,  'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'uf'		, 'etiqueta' => 'UF'			, 'tipo' => 'T', 'width' => 80,   'posicao' => 'E'));
        
        $this->_relatorio->addColuna(array('campo' => 'dtprotesto'	, 'etiqueta' => 'DT PROTESTO'	, 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
        $this->_relatorio->addColuna(array('campo' => 'vlcustas'	, 'etiqueta' => 'VLR CUSTAS'	, 'tipo' => 'V', 'width' => 80, 'posicao' => 'E'));
        
        //ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        //ExecMethod('config.sys004.inclui',array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
    }
    
    function index(){
    }
    
    function schedule($param){
    	
        $this->_relatorio->setAuto(true);
        
        $parametros = explode("|", $param);
        $dias = $parametros[0];
        $emailGerencia = $parametros[1];
        
        $dados = $this->getDados($dias);
        
        
        $gerencia = array();
//print_r($dados);
        foreach ($dados as $super => $dado){
        	$supervisor = array();
            foreach ($dado as $rca => $titulos){
                $tit = array();
//print_r($titulos);
                foreach ($titulos as $titulo){
//print_r($titulo);
                	$temp = array();
                	$temp['vend'] 		= $titulo['vend'];
                	$temp['coord'] 		= $titulo['coord'];
                    $temp['cod'] 		= $titulo['cod'];
                    $temp['cliente']	= $titulo['cliente'];
                    $temp['ativ']		= $titulo['ativ'];
                    $temp['titulo'] 	= $titulo['titulo'];
                    $temp['parcela']	= $titulo['parcela'];
                    $temp['valor'] 		= $titulo['valor'];
                    $temp['emissao']	= $titulo['emissao'];
                    $temp['venc'] 		= $titulo['venc'];
                    $temp['dias'] 		= $titulo['dias'];
                    $temp['cidade']		= $titulo['cidade'];
                    $temp['uf']			= $titulo['uf'];
                    $temp['dtprotesto'] = $titulo['dtprotesto'];
                    $temp['vlcustas'] 	= $titulo['vlcustas'];
                    
                    $tit[] = $temp;
                    $supervisor[] = $temp;
                    $gerencia[] = $temp;
                }
                if(count($tit) > 0){
                	$titulo = "Titulos vencidos a mais de $dias dias - ".$tit[0]['vend'];
                    $email = $this->getEmailRCA($rca);
                    $this->_relatorio->setTitulo($titulo);
                    $this->_relatorio->setDados($tit);
                    $this->_relatorio->setToExcel(true, "vencidos_".$dias."_dias");
                    if(!$this->_teste){
                        $this->_relatorio->enviaEmail($email,$titulo);
                        log::gravaLog("vencidos", $dias." dias - Enviado email: ".$email." - ".$rca);
                    }
                    else{
                       // $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
                        echo $titulo." <br>\n";
                    }
                    
                }
            }
            if(count($supervisor) > 0){
            	$titulo = "Titulos vencidos a mais de $dias dias - ".$titulos[0]['coord'];
                $email = $this->getEmailCoord($super);
                $this->_relatorio->setTitulo($titulo);
                $this->_relatorio->setDados($supervisor);
                $this->_relatorio->setToExcel(true, "vencidos_".$dias."_dias");
                if(!$this->_teste){
                    $this->_relatorio->enviaEmail($email,$titulo);
                    log::gravaLog("vencidos", $dias." dias - Enviado email: ".$email." - ".$super);
                }
                else{
                    $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
                    echo "------------------------------------------------------------------------> SUPER -------------------->>>>".$titulo." <br>\n";
                }
            }
        }
        
        $email = $emailGerencia;
        
        $titulo = "Titulos vencidos a mais de $dias dias";
        $this->_relatorio->setTitulo($titulo);
        $this->_relatorio->setDados($gerencia);
        $this->_relatorio->setToExcel(true, "vencidos_".$dias."_dias");
        //$this->_relatorio->enviaEmail('thiel@thielws.com.br',$titulo);
        if(!$this->_teste){
            $this->_relatorio->enviaEmail($email,$titulo);
            log::gravaLog("vencidos", $dias." dias - Enviado email: ".$email);
        }
        else{
            $this->_relatorio->enviaEmail('suporte@thielws.com.br',$titulo);
        }
        
        return;
        
    }
    
    function getEmailCoord($super){
        $ret = '';
        $sql = "SELECT pcusuari.email FROM pcusuari WHERE pcusuari.codusur = (SELECT COD_CADRCA FROM pcsuperv WHERE CODSUPERVISOR = $super)";
        $rows = query4($sql);
        if(count($rows) > 0){
            $ret = $rows[0][0];
        }
        return $ret;
    }
    
    function getEmailRCA($rca){
        $ret = '';
        $sql = "SELECT pcusuari.email FROM pcusuari WHERE pcusuari.codusur = $rca";
        $rows = query4($sql);
        if(count($rows) > 0){
            $ret = $rows[0][0];
        }
        return $ret;
    }
    
    function getDados($dias){
        $ret = array();
        $sql = "
				SELECT  pcusuari.codsupervisor,
				        pcclient.codusur1,
				        pcsuperv.nome,
				        pcusuari.nome,
				        pcprest.dtvenc,
				        (select ((TRUNC(pcprest.dtvenc) - TRUNC(SYSDATE)) * -1) FROM DUAL where pcprest.dtvenc < SYSDATE)DIAS_ATRASO,
				        pcprest.duplic,
				        pcprest.prest,
				        pcprest.dtemissao,
				        pcprest.codcli,
				        pcclient.cliente,
				        pcsuperv.cod_cadrca,
				        pcprest.valor,
				        pcclient.municent,
				        pcclient.estent,
				        pcativi.ramo,
				        (SELECT pclogcobmag.data FROM pclogcobmag WHERE pcprest.numtransvenda = pclogcobmag.numtransvenda and pcprest.prest = pclogcobmag.prest and pclogcobmag.codocorrencia = '40' AND ROWNUM = 1 ) DTPROTESTO,
				        (SELECT SUM(pclogcobmag.vlcustas) FROM pclogcobmag WHERE pcprest.numtransvenda = pclogcobmag.numtransvenda and pcprest.prest = pclogcobmag.prest and pclogcobmag.codocorrencia = '40') vlcustas
				        
				FROM pcprest,
				     pcclient,
				     pcusuari,
				     pcsuperv,
				     pcativi
				WHERE pcprest.dtpag IS NULL
				    --AND pcprest.codcob IN ('C','001','041','DEP','BK')
				    AND pcprest.codcli = PCCLIENT.codcli (+)
				    --and pcprest.codusur = pcusuari.codusur (+)
				    and pcclient.codusur1 = pcusuari.codusur (+)
				    and pcclient.codatv1 = pcativi.codativ (+)
				    and pcusuari.codsupervisor = pcsuperv.codsupervisor (+)
				    and TRUNC(pcprest.dtvenc) <= TRUNC(SYSDATE) - $dias
				    and pcclient.codatv1 not in (7,4,15,5)
				ORDER BY pcprest.valor desc,pcusuari.codsupervisor,pcprest.codusur,pcprest.dtvenc
						";
        $rows = query4($sql);
        
        if(count($rows) > 0){
            foreach($rows as $row){
            	$super = $row[0];
            	$erc = $row[1];
            	
				$temp = array();               
                $temp['coord'] 		= $row[2];
                $temp['vend'] 		= $row[3];
                $temp['venc'] 		= datas::dataMS2D($row[4]);
                $temp['dias'] 		= $row[5];
                $temp['valor']		= $row[12];
                $temp['titulo'] 	= $row[6];
                $temp['parcela'] 	= $row[7];
                $temp['emissao'] 	= datas::dataMS2D($row[8]);
                $temp['cod'] 		= $row[9];
                $temp['cliente'] 	= $row[10];
                $temp['rca'] 		= $row[11];
                $temp['cidade']		= $row[13];
                $temp['uf'] 		= $row[14];
                $temp['ativ'] 		= $row[15];
                $temp['dtprotesto']	= datas::dataMS2D($row[16]);
                $temp['vlcustas']	= $row[17];
                
                $ret[$super][$erc][] = $temp;
            }
        }

        return $ret;
    }
}


