<?php
/*
 * Data Criacao: 28/12/2021
 * Autor: Verticais - Thiel
 *
 * Descricao: Agenda
 *
 * Alteracoes: 01/09/2023 - Integração API agenda microsoft - Gilson
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class sdm_agenda{
    var $funcoes_publicas = array(
        'index' 	=> true,
        'agenda' 	=> true,
        'marcar' 	=> true,
        'excluir' 	=> true,
        'ajax'      => true,
    );
    
    //Programa
    private $_programa = '';
    
    //Filtro
    private $_filtro;
    
    //Recursos
    private $_recursos = [];
    
    //Agendas Marcadas
    private $_agendas = [];
    
    //Nome clientes
    private $_clientes = [];
    
    //Token de acesso
    private $_token = 
    //'eyJ0eXAiOiJKV1QiLCJub25jZSI6IkNGMUV0WFFpRGFCWTgxN0xZdGlTMlk4aVI3U0U0dG9KMzRjaDBTd1lDc1UiLCJhbGciOiJSUzI1NiIsIng1dCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyIsImtpZCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC9lYzVlYjE0Mi03MTQzLTRiMTktYjE1Ny03NGNmY2Q4OGViMzkvIiwiaWF0IjoxNjkzNTcyMzEyLCJuYmYiOjE2OTM1NzIzMTIsImV4cCI6MTY5MzY1OTAxMiwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFUUUF5LzhVQUFBQVkrZHlSZ0ZhSFlaVDVBMWJWVHZQTzNPTHhVWVdPb3JkZ2wyNmtpUGtHQ1djck1DK1l4YU5LSkZEa3NuOTlpM1giLCJhbXIiOlsicHdkIl0sImFwcF9kaXNwbGF5bmFtZSI6IkdyYXBoIEV4cGxvcmVyIiwiYXBwaWQiOiJkZThiYzhiNS1kOWY5LTQ4YjEtYThhZC1iNzQ4ZGE3MjUwNjQiLCJhcHBpZGFjciI6IjAiLCJmYW1pbHlfbmFtZSI6IkNlc2FyIiwiZ2l2ZW5fbmFtZSI6IkFsZXgiLCJpZHR5cCI6InVzZXIiLCJpcGFkZHIiOiIxNjcuMjUwLjI4Ljg2IiwibmFtZSI6IkFsZXggQ2VzYXIiLCJvaWQiOiJiZDFkODE0OS0yM2QyLTRiYzMtODllYS05MjgwMDMyMTE1ZDMiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDI5RkVGQzMyOSIsInJoIjoiMC5BVkFBUXJGZTdFTnhHVXV4VjNUUHpZanJPUU1BQUFBQUFBQUF3QUFBQUFBQUFBQlFBRE0uIiwic2NwIjoiQ2FsZW5kYXJzLlJlYWRXcml0ZSBDaGF0LlJlYWQgQ2hhdC5SZWFkQmFzaWMgQ29udGFjdHMuUmVhZFdyaXRlIERldmljZU1hbmFnZW1lbnRSQkFDLlJlYWQuQWxsIERldmljZU1hbmFnZW1lbnRTZXJ2aWNlQ29uZmlnLlJlYWQuQWxsIEZpbGVzLlJlYWRXcml0ZS5BbGwgR3JvdXAuUmVhZFdyaXRlLkFsbCBJZGVudGl0eVJpc2tFdmVudC5SZWFkLkFsbCBNYWlsLlJlYWQgTWFpbC5SZWFkV3JpdGUgTWFpbGJveFNldHRpbmdzLlJlYWRXcml0ZSBOb3Rlcy5SZWFkV3JpdGUuQWxsIG9wZW5pZCBQZW9wbGUuUmVhZCBQbGFjZS5SZWFkIFByZXNlbmNlLlJlYWQgUHJlc2VuY2UuUmVhZC5BbGwgUHJpbnRlclNoYXJlLlJlYWRCYXNpYy5BbGwgUHJpbnRKb2IuQ3JlYXRlIFByaW50Sm9iLlJlYWRCYXNpYyBwcm9maWxlIFJlcG9ydHMuUmVhZC5BbGwgU2l0ZXMuUmVhZFdyaXRlLkFsbCBUYXNrcy5SZWFkV3JpdGUgVXNlci5SZWFkIFVzZXIuUmVhZEJhc2ljLkFsbCBVc2VyLlJlYWRXcml0ZSBVc2VyLlJlYWRXcml0ZS5BbGwgZW1haWwiLCJzaWduaW5fc3RhdGUiOlsia21zaSJdLCJzdWIiOiJjOERUOFNmUjNQSU51RXJWdFlvUDdUMUxTbVpFYzQ5aDNja0VIcFl5WWFZIiwidGVuYW50X3JlZ2lvbl9zY29wZSI6IlNBIiwidGlkIjoiZWM1ZWIxNDItNzE0My00YjE5LWIxNTctNzRjZmNkODhlYjM5IiwidW5pcXVlX25hbWUiOiJhbGV4LmNlc2FyQHZlcnRpY2Fpcy5jb20uYnIiLCJ1cG4iOiJhbGV4LmNlc2FyQHZlcnRpY2Fpcy5jb20uYnIiLCJ1dGkiOiJLNEYyRUdMTU9VbXFPaUJjV3J3VUFBIiwidmVyIjoiMS4wIiwid2lkcyI6WyJiNzlmYmY0ZC0zZWY5LTQ2ODktODE0My03NmIxOTRlODU1MDkiXSwieG1zX2NjIjpbIkNQMSJdLCJ4bXNfc3NtIjoiMSIsInhtc19zdCI6eyJzdWIiOiJUT2NISzg1OUs2VXpzNDBmVnhVWXZITTNrNGhsVWVQU00xZjc3UWU4TlV3In0sInhtc190Y2R0IjoxNjI3NjU5NDQ5fQ.oklqMX2Q2yL2ih0wN3ZV6KV-Iz5VKMObsbtnPRPfRp2uK33y-N2wXgd0FwkIVjrZ_bgLWLCGNKBWhbqzxoewKF7UxxgHOXL_NE0lsDmZ8YRxZyMvbOlpzHIwPpEZSzannV2uh5cB5PK7KwMa1kHv1DMITgAMUZDbxvnF_q9V4xyB9up7gtqhkpmN9AZgFPtR1PptwOBmGvNw80857sdmZTblajmMnkb5kTnIaWdbUhdd4IcQ1V2HM_b7rv_Gt3XEbkYyYTJAHCXqHM7gGLsRGEhSTsEupYKhvTXNoRLtvZgt2YrM9FrdFykEhhWU2H7NJgvOKnj3qvCd1QtUqOsvww'
    'eyJ0eXAiOiJKV1QiLCJub25jZSI6Inc5Y2huQ0xTajlsVzVfT2dCSFlCUFU3NTNVY0MySFQzVmt4THY3UmhCcjAiLCJhbGciOiJSUzI1NiIsIng1dCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyIsImtpZCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC9lYzVlYjE0Mi03MTQzLTRiMTktYjE1Ny03NGNmY2Q4OGViMzkvIiwiaWF0IjoxNjkzNTgzODM4LCJuYmYiOjE2OTM1ODM4MzgsImV4cCI6MTY5MzY3MDUzOCwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFUUUF5LzhVQUFBQTlXMzF1eUJGbXZEOHFqaWJFSVl2K0g2b3RHUFNjN25OT1lFNFpqQzNNYjZnblNQK29Cb09hazYydXBpQXRZN1UiLCJhbXIiOlsicHdkIl0sImFwcF9kaXNwbGF5bmFtZSI6IkdyYXBoIEV4cGxvcmVyIiwiYXBwaWQiOiJkZThiYzhiNS1kOWY5LTQ4YjEtYThhZC1iNzQ4ZGE3MjUwNjQiLCJhcHBpZGFjciI6IjAiLCJmYW1pbHlfbmFtZSI6IkFuZHJhZGUiLCJnaXZlbl9uYW1lIjoiUmF5YW4iLCJpZHR5cCI6InVzZXIiLCJpcGFkZHIiOiIyODA0OjJjMjQ6MjEyMDpkOTAwOmQ0NzpkYzA1OjM0YzoyMDliIiwibmFtZSI6IlJheWFuIEFuZHJhZGUiLCJvaWQiOiI0YTRlOTVkNi1iZGViLTQ4YjItYmQzOC0wODk2YmUyODU4MDUiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDE2NzM3NTJEOSIsInJoIjoiMC5BVkFBUXJGZTdFTnhHVXV4VjNUUHpZanJPUU1BQUFBQUFBQUF3QUFBQUFBQUFBQlFBRHMuIiwic2NwIjoiQ2FsZW5kYXJzLlJlYWRXcml0ZSBDaGF0LlJlYWQgQ2hhdC5SZWFkQmFzaWMgQ29udGFjdHMuUmVhZFdyaXRlIERldmljZU1hbmFnZW1lbnRSQkFDLlJlYWQuQWxsIERldmljZU1hbmFnZW1lbnRTZXJ2aWNlQ29uZmlnLlJlYWQuQWxsIEZpbGVzLlJlYWRXcml0ZS5BbGwgR3JvdXAuUmVhZFdyaXRlLkFsbCBJZGVudGl0eVJpc2tFdmVudC5SZWFkLkFsbCBNYWlsLlJlYWQgTWFpbC5SZWFkV3JpdGUgTWFpbGJveFNldHRpbmdzLlJlYWRXcml0ZSBOb3Rlcy5SZWFkV3JpdGUuQWxsIG9wZW5pZCBQZW9wbGUuUmVhZCBQbGFjZS5SZWFkIFByZXNlbmNlLlJlYWQgUHJlc2VuY2UuUmVhZC5BbGwgUHJpbnRlclNoYXJlLlJlYWRCYXNpYy5BbGwgUHJpbnRKb2IuQ3JlYXRlIFByaW50Sm9iLlJlYWRCYXNpYyBwcm9maWxlIFJlcG9ydHMuUmVhZC5BbGwgU2l0ZXMuUmVhZFdyaXRlLkFsbCBUYXNrcy5SZWFkV3JpdGUgVXNlci5SZWFkIFVzZXIuUmVhZEJhc2ljLkFsbCBVc2VyLlJlYWRXcml0ZSBVc2VyLlJlYWRXcml0ZS5BbGwgZW1haWwiLCJzaWduaW5fc3RhdGUiOlsia21zaSJdLCJzdWIiOiJ6czd4X0dtd0szb2VKSEsyRUVMUmp5dV9LdUl1Ylhsa2laT09iUk1sTDRVIiwidGVuYW50X3JlZ2lvbl9zY29wZSI6IlNBIiwidGlkIjoiZWM1ZWIxNDItNzE0My00YjE5LWIxNTctNzRjZmNkODhlYjM5IiwidW5pcXVlX25hbWUiOiJyYXlhbi5hbmRyYWRlQHZlcnRpY2Fpcy5jb20uYnIiLCJ1cG4iOiJyYXlhbi5hbmRyYWRlQHZlcnRpY2Fpcy5jb20uYnIiLCJ1dGkiOiJkQlNGU3dnSFlrcUQta0ZFalBsS0FRIiwidmVyIjoiMS4wIiwid2lkcyI6WyI2MmU5MDM5NC02OWY1LTQyMzctOTE5MC0wMTIxNzcxNDVlMTAiLCJiNzlmYmY0ZC0zZWY5LTQ2ODktODE0My03NmIxOTRlODU1MDkiXSwieG1zX2NjIjpbIkNQMSJdLCJ4bXNfc3NtIjoiMSIsInhtc19zdCI6eyJzdWIiOiJ3dWRrVmRHQlFzSVR6RnNOWWhtWXJfT3ROTWRsWUo3blV1NXg3amtPNkU0In0sInhtc190Y2R0IjoxNjI3NjU5NDQ5fQ.MMhheaMUSlqhnl_BOBPB89g_oIwlJyi9cWh_9AOgm3H3lTkwAynQeOA4X_zhhrOo0yaubbal5FnoK4ne9Mh93FJUS7fioEnE46LADiraydKFtffLggxIvTHKz6q1oHO_v5863c8ZMzDT67ycYPJcx6IiT58iVs7-B0Ta1HvSnY8AuHrQT6vSQxZE7Aai8RY3sAhi0B6jy0gcDcoaBKrzURbtG3XQqdvdw6gjCAGQM-hSdWpJT0YYdWzdwMrfwiGQrjUp1EwKICprVrz7Rn1pw6SsbgxX5V9y7rasDqrr3M0KrSmhtHWq-5-mlYcsfZqWjJq7EcgdW5iVG0nJrkGKeg'
    ;
    
    function __construct(){
        $this->getRecursos();
        $this->getClientesNome();
        $this->_programa = get_class($this).'_geral';
        
        $paramFiltro = array();
        $paramFiltro['tamanho'] = 12;
        $this->_filtro = new formFiltro01($this->_programa, $paramFiltro);
        
        if(false){
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''						, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente'	, 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sdm_agenda_clientes()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Analista', 'variavel' => 'RECURSO', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'sdm_agenda_recursos()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        }
    }
    
    function index(){
        $ret = '';
        
        
        
        
        $this->scriptAgenda();
        $filtro  = $this->_filtro->getFiltro();
        addPortalJquery("$('#formFiltro').hide();");
        
        $diaIni = !empty($filtro['DATAINI']) ? $filtro['DATAINI'] : datas::getDataDias();
        $diaFim = !empty($filtro['DATAFIM']) ? $filtro['DATAFIM'] : datas::getDataDias(15, $diaIni);
        $recurso = !empty($filtro['RECURSO']) ? $filtro['RECURSO'] : '';
        $cliente = !empty($filtro['CLIENTE']) ? $filtro['CLIENTE'] : '';
        
        //Botoes semana e mês
        $operacao = getOperacao();
        if(!empty($operacao)){
            $datas = $this->getDatas($operacao);
            $diaIni = $datas['ini'];
            $diaFim = $datas['fim'];
            
            $param['DATAINI'] = $diaIni;
            $param['DATAFIM'] = $diaFim;
            $this->_filtro->setRespostas($param);
            $this->_filtro->setLink(getLink().'index');
        }
        
        if($diaIni > $diaFim){
            $diaTemp = $diaIni;
            $diaIni = $diaFim;
            $diaFim = $diaTemp;
            
            $param['DATAINI'] = $diaIni;
            $param['DATAFIM'] = $diaFim;
            $this->_filtro->setRespostas($param);
            $this->_filtro->setLink(getLink().'index');
        }
        
        $dias = datas::calendario($diaIni, $diaFim);
        
        $this->getAgendas($dias, $recurso, $cliente);
        
        $ret .= $this->_filtro;
        $ret .= $this->montaTabela($dias);
        
        $param = [];
        
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index.semana');";
        $botao['texto']		= 'Esta Semana';
        $botao['id'] 		= 'bt_semana';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        $botao = [];
        $botao['onclick']	= "setLocation('" . getLink() . "index.mes');";
        $botao['texto']		= 'Este Mês';
        $botao['id'] 		= 'bt_mes';
        $botao['cor']		= 'info';
        $param['botoesTitulo'][] = $botao;
        
        $botao = [];
        $botao['onclick']	= "$('#formFiltro').toggle();";
        $botao['texto']		= FORMFILTRO_TITULO;
        $botao['id'] 		= 'bt_form';
        $param['botoesTitulo'][] = $botao;
        
        $param['titulo'] 	= 'Agendas';
        $param['conteudo'] 	= $ret;
        $param['cor']		= 'success';
        $ret = addCard($param);
        
        return $ret;
    }
    
    function agenda(){
        $recurso = base64_decode(getParam($_GET, 'recurso'));
        $dia = base64_decode(getParam($_GET, 'dia'));
        if(!empty($dia) && !empty($recurso)){
            $ret = $this->getMarcacao($recurso, $dia);
        }else{
            $ret = $this->index();
        }
        return $ret;
    }
    
    function marcar(){
        $dia = getAppVar('vert_agenda_dia');
        $recurso = getAppVar('vert_agenda_recurso');
        
        if(!empty($dia) && !empty($recurso)){
            $dados = getParam($_POST, 'formAgenda');
            $temp = preg_split('/[.]/', $recurso);
            $nome = $temp[0];
            
            $campos = [];
            
            $campos['cliente'] 			= getCliente();
            $campos['emp'] 				= '';
            $campos['fil'] 				= '01';
            $campos['recurso'] 			= $recurso;
            $campos['data'] 			= $dia;
            $campos['turno'] 			= $dados['turno'];
            $campos['local'] 			= $dados['local'];
            $campos['contato'] 			= $dados['contato'];
            $campos['cliente_agenda'] 	= $dados['cliente'];
            $campos['tipo'] 			= $dados['tipo'];
            $campos['tarefa'] 			= $dados['tarefa'];
            $campos['status'] 			= 'F';
            $campos['os'] 				= '';
            $campos['ticket'] 			= '';
            $campos['marcado_por'] 		= getUsuario();
            $campos['projeto']			= $dados['projeto'] ?? '';
            
            $dadosPost = array();
            $dadosPost['dia'] = $dia;
            $dadosPost['email'] = $recurso;
            $dadosPost['nome'] = $nome;
            $dadosPost['turno'] = $dados['turno'];
            $dadosPost['local'] = $dados['local'];
            $dadosPost['titulo'] = $dados['titulo'];
            $dadosPost['tarefa'] = $dados['tarefa'];
            
            
            $calendario = new rest_calendario('https://graph.microsoft.com/v1.0', $dadosPost);    //, $this->_token);
            
            $ret = $calendario->agendarEvento($dadosPost['email']);
            
            if($ret !== false){
                if(isset($ret['id'])){
                    $campos['id_api'] = $ret['id'];
                } else {
                    $campos['id_api'] = '';
                }
                $sql = montaSQL($campos, 'sdm_agenda');
                $res = query($sql);
                if($res == true){
                    app_sdm::enviaEmailAgendaRecurso($campos);
                    addPortalMensagem('Agenda marcada!');
                }else{
                    addPortalMensagem('Algo sinistro aconteceu, por favor tente novamente!', 'erro');
                }
            }else{
                addPortalMensagem('Algo sinistro aconteceu, por favor tente novamente!', 'erro');
            }
        }else{
            addPortalMensagem('Algo sinistro aconteceu, por favor tente novamente!', 'erro');
        }
        
        putAppVar('vert_agenda_recurso', '');
        putAppVar('vert_agenda_dia', '');
        
        redireciona();
    }
    
    function excluir(){
        $erro = false;
        $agenda = base64_decode(getParam($_GET, 'agenda'));
        
        if(!empty($agenda)){
            $dados = explode('|', $agenda);
            if(count($dados) == 4){
                $sql = "UPDATE sdm_agenda SET status = 'E', del = '*', del_por = '".getUsuario()."', del_em = '".date("Y-m-d H:i:s")."' WHERE recurso = '".$dados[0]."' AND data = '".$dados[1]."' AND cliente_agenda = '".$dados[3]."' AND turno = '".$dados[2]."'";
                //echo "$sql <br>\n";
                query($sql);
                $id = $this->getIdApi($dados);
                
                $calendario = new rest_calendario('https://graph.microsoft.com/v1.0', '');//, $this->_token);
                $calendario->deletarEvento($id, $dados[0]);
                
                app_sdm::enviaEmailExclusaoAgendaRecurso($dados);
                addPortalMensagem('Agenda excluída!');
                
                $ret = $this->getMarcacao($dados[0], $dados[1]);
            }else{
                $erro = true;
            }
        }else{
            $erro = true;
        }
        
        if($erro){
            addPortalMensagem('Algo sinistro aconteceu, por favor tente novamente!', 'erro');
        }
        redireciona();
    }
    
    //----------------------------------------------------------------- UI   ------------
    
    private function getTabelaMarcadas($agendas, $recurso, $dia){
        $ret = '';
        $this->geraScriptConfirmacao();
        
        $dados = [];
        foreach ($agendas as $turno => $cliente){
            $temp = [];
            $temp['cod'] = $cliente;
            $temp['nome'] = $this->_clientes[$cliente];
            $temp['turno'] = $turno;
            $temp['chave'] = base64_encode($recurso.'|'.$dia.'|'.$turno.'|'.$cliente);
            $temp['descricao'] = $recurso.' | '.datas::dataS2D($dia,2).' | '.$turno;
            
            $dados[] = $temp;
        }
        
        $param = [];
        $param['width'] = 'AUTO';
        $param['paginacao'] = false;
        $param['ordenacao'] = false;
        $param['filtro'] = false;
        $param['scroll'] = false;
        $param['info'] = false;
        $tab = new tabela01($param );
        
        $tab->addColuna(array('campo' => 'cod'		, 'etiqueta' => 'Cod.'		,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Cliente'	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'turno'	, 'etiqueta' => 'Turno'		,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        
        // Botão Excluir
      //  if(getUsuario('tipo') == 'S' || getUsuario('tipo') == 'A'){
  //      if($this->verificarAcaoSys016(getUsuario(), 'arquivar')){
            $param = [];
            $param['texto'] = 'Excluir';
            $param['link'] 	= "javascript:confirmaExclusao('".getLink()."excluir&agenda=','{ID}',{COLUNA:descricao})";
            $param['coluna']= 'chave';
            $param['cor'] 	= 'danger';
            $param['flag'] 	= '';
            $param['width'] = 80;
            $param['pos'] = 'I';
            $tab->addAcao($param);
    //    }
        
        $tab->setDados($dados);
        
        $ret .= $tab;
        
        return $ret;
    }
    
    private function getMarcacao($recurso, $dia){
        global $nl;
        $ret = '';
        $marcadas = [];
        
        putAppVar('vert_agenda_recurso', $recurso);
        putAppVar('vert_agenda_dia', $dia);
        
        $analista = $this->_recursos[$recurso];
        $this->getAgendas($dia, $recurso);
        $agendas = $this->_agendas[$recurso][$dia] ?? [];
        if(count($agendas) > 0){
            $marcadas = $this->getTabelaMarcadas($agendas, $recurso, $dia);
        }else{
            $marcadas = 'Não existe agenda marcarda nesta data!';
        }
        
        
        $turnos = $this->getTurnosLivres($agendas);
        //$turnos = tabela('000022');
        $local = tabela('000023');
        $tipo = tabela('000024');
        $clientes = $this->getClientes();
        
        if(true){
            $turnosVal = '';
            $localVal = 'C';
            $tipoVal = 'E';
        }
        
        $this->addAjaxProjetos();
        //padrão cliente Verticais
        //form setar obrigatorios
        
        $form = new form01(['geraScriptValidacaoObrigatorios'=>true]);
        $form->setBotaoCancela();
        $form->addCampo(array('id' => 'titulo'	, 'campo' => 'formAgenda[titulo]'	, 'etiqueta' => 'Titulo da Agenda'	, 'tipo' => 'T'	, 'tamanho' => '20', 'linha' => 1, 'largura' => 12	, 'linhasTA' => ''	, 'valor' => ''		, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => 'cliente'	, 'campo' => 'formAgenda[cliente]'	, 'etiqueta' => 'Cliente'	, 'tipo' => 'A'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => '000001'	, 'lista' => $clientes	, 'validacao' => '', 'obrigatorio' => true, 'onchange' => 'callAjax();'));
        $form->addCampo(array('id' => 'projeto'	, 'campo' => 'formAgenda[projeto]'	, 'etiqueta' => 'Projeto'	, 'tipo' => 'A'		, 'tamanho' => '80', 'linha' => 1, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => array(array('', ''))	, 'validacao' => '', 'obrigatorio' => false));
        $form->addCampo(array('id' => 'turno'	, 'campo' => 'formAgenda[turno]'	, 'etiqueta' => 'Turno'		, 'tipo' => 'A'		, 'tamanho' => '20', 'linha' => 2, 'largura' => 4	, 'linhasTA' => ''	, 'valor' => $turnosVal , 'lista' => $turnos	, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => 'local'	, 'campo' => 'formAgenda[local]'	, 'etiqueta' => 'Local'		, 'tipo' => 'A'		, 'tamanho' => '10', 'linha' => 2, 'largura' => 8	, 'linhasTA' => ''	, 'valor' => $localVal	, 'lista' => $local		, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => 'contato'	, 'campo' => 'formAgenda[contato]'	, 'etiqueta' => 'Contato'	, 'tipo' => 'T'		, 'tamanho' => '10', 'linha' => 3, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => 'tipo'	, 'campo' => 'formAgenda[tipo]'		, 'etiqueta' => 'Tipo'		, 'tipo' => 'A'		, 'tamanho' => '20', 'linha' => 3, 'largura' => 6	, 'linhasTA' => ''	, 'valor' => $tipoVal	, 'lista' => $tipo		, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => 'tarefa'	, 'campo' => 'formAgenda[tarefa]'	, 'etiqueta' => 'Tarefa'	, 'tipo' => 'TA'	, 'tamanho' => '20', 'linha' => 4, 'largura' => 12	, 'linhasTA' => ''	, 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
        
        $form->setEnvio(getLink().'marcar', 'formAgenda', 'formAgenda');
        
        $param = [];
        $param['titulo'] = 'Recurso: '.$analista['apelido'];
        $param['conteudo'] = $form;
        $formAgenda = addCard($param);
        
        $param = [];
        $param['titulo'] = 'Marcadas '.datas::dataS2D($dia);
        $param['conteudo'] = $marcadas;
        $agendasMarcadas = addCard($param);
        
        $ret .= '<div class="row">'.$nl;
        $ret .= '	<div  class="col-md-6">'.$formAgenda.'</div>'.$nl;
        //$ret .= '	<div  class="col-md-2"></div>'.$nl;
        $ret .= '	<div  class="col-md-6">'.$agendasMarcadas.'</div>'.$nl;
        $ret .= '</div>'.$nl;
        
        $param = [];
        $param['titulo'] = '	Agenda';
        $param['conteudo'] = $ret;
        $ret = addCard($param);
        
        return $ret;
    }
    
    private function montaTabela($dias){
        global $nl;
        $ret = '';
        //print_r($this->_agendas);
        $ret .= '<div class="table-responsive">'.$nl;
        $ret .= '<table class="table table-striped table-bordered table-hover" id="agenda-tabela">'.$nl;
        $ret .= '	<thead class="thead-light">'.$nl;
        $ret .= '		<tr>'.$nl;
        $ret .= '			<th scope="col">Analista</th>'.$nl;
        foreach ($dias[0] as $key => $dia){
            $ret .= '			<th scope="col" align="center">'.datas::dataS2D($dia,2).'<br>'.$dias[1][$key].'</th>'.$nl;
        }
        $ret .= '		</tr>'.$nl;
        $ret .= '	</thead>'.$nl;
        $ret .= '	<tbody>'.$nl;
        foreach ($this->_recursos as $recurso){
            $ret .= '		<tr>'.$nl;
            $ret .= '		<th scope="row">'.$recurso['apelido'].'</th>'.$nl;
            foreach ($dias[0] as $key => $dia){
                $cor = 'table-danger';
                $conteudo = '';
                if(isset($this->_agendas[$recurso['recurso']][$dia])){
                    $cor = 'table-success';
                    foreach ($this->_agendas[$recurso['recurso']][$dia] as $turno => $cliente){
                        $conteudo .= $turno.'-'.$this->_clientes[$cliente]."<br>\n";
                    }
                }else{
                    //parcial
                    //$cor = 'table-warning';
                }
                $ret .= '			<td class="'.$cor.'" align="center" onclick="agendaClick(\''.base64_encode($recurso['recurso']).'\',\''.base64_encode($dia).'\');">'.$conteudo.'</td>'.$nl;
            }
            $ret .= '		</tr>'.$nl;
        }
        $ret .= '	</tbody>'.$nl;
        $ret .= '</table>'.$nl;
        $ret .= '</div>'.$nl;
        return $ret;
    }
    
    //----------------------------------------------------------------- GETs ------------
    
    private function getTurnosLivres($agendas){
        $ret = [];
        $livre = ['M'=>true,'T'=>true,'N'=>true,'I'=>true];
        
        foreach ($agendas as $t => $cli){
            if($t == 'I'){
                unset($livre['M']);
                unset($livre['T']);
            }
            if($t == 'M' || $t == 'T'){
                unset($livre['I']);
            }
            unset($livre[$t]);
        }
        
        $turnos = tabela('000022');
        foreach ($turnos as $t){
            if(isset($livre[$t[0]])){
                $ret[] = $t;
            }
        }
        
        //print_r($ret);
        
        return $ret;
    }
    
    private function getRecursos($recurso = ''){
        $where = '';
        if(!empty($recurso)){
            $where = " AND recurso = '$recurso'";
        }
        $sql = "SELECT * FROM sdm_recursos WHERE agenda = 'S' AND ativo = 'S' AND tipo = 'A' $where ORDER BY apelido";
        //echo "$sql <br>\n";
        $rows = query($sql);
        
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = [];
                $temp['nome'] 	= $row['nome'];
                $temp['apelido']= $row['apelido'];
                $temp['tipo'] 	= $row['tipo'];
                $temp['recurso']= $row['usuario'];
                
                $this->_recursos[$row['usuario']] = $temp;
            }
        }
        //print_r($this->_recursos);
    }
    
    private function getAgendas($dias, $recurso = '', $cliente = ''){
        $this->_agendas = [];
        
        $dia = '';
        if(is_array($dias)){
            $datas = [];
            foreach ($dias[0] as $dia){
                $datas[] = $dia;
            }
            $dia = "'".implode("','", $datas)."'";
        }else{
            $dia = "'".$dias."'";
        }
        
        $recursoWhere = '';
        if(!empty($recurso)){
            $recursoWhere .= " AND recurso = '$recurso'";
        }
        if(!empty($cliente)){
            $recursoWhere .= " AND cliente_agenda = '$cliente'";
        }
        
        $sql = "SELECT * FROM sdm_agenda WHERE data IN ($dia) $recursoWhere AND cliente_agenda <> '' AND IFNULL(del,'') = '' ORDER BY recurso, data";
        $rows = query($sql);
        
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $usuario = $row['recurso'];
                $dia 	 = $row['data'];
                $turno	 = $row['turno'];
                
                $this->_agendas[$usuario][$dia][$turno] = $row['cliente_agenda'];
            }
        }
    }
    
    private function getClientesNome(){
        $sql = "SELECT cod, nreduz FROM cad_organizacoes ";
        $rows = query($sql);
        
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row) {
                $this->_clientes[$row['cod']] = $row['nreduz'];
            }
        }
    }
    
    private function getClientes(){
        $ret = array();
        
        //$ret[0][0] = "";
        //$ret[0][1] = "&nbsp;";
        
        $sql = "SELECT cod, nreduz FROM cad_organizacoes WHERE ativo = 'S' ORDER BY nreduz";
        $rows = query($sql);
        
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row) {
                $temp[0] = $row['cod'];
                $temp[1] = $row['nreduz'];
                
                $ret[] = $temp;
            }
        }
        //print_r($tabela);
        return $ret;
    }
    
    private function getIdApi($dados){
        $ret = '';
        $sql = "SELECT id_api FROM sdm_agenda WHERE recurso = '".$dados[0]."' AND data = '".$dados[1]."' AND cliente_agenda = '".$dados[3]."' AND turno = '".$dados[2]."'";
        
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = $rows[0]['id_api'];
        }
        return $ret;
    }
    //----------------------------------------------------------------------------- UTEIS ------------------------------------------------------------
    private function scriptAgenda(){
        addPortaljavaScript('function agendaClick(recurso, dia){');
        addPortaljavaScript("	setLocation('".getLink()."agenda&recurso='+recurso+'&dia='+dia);");
        addPortaljavaScript("}");
    }
    
    private function geraScriptConfirmacao(){
        addPortaljavaScript('function confirmaExclusao(link,id,desc){');
        addPortaljavaScript('	if (confirm("Confirma a EXCLUSAO da agenda "+desc+"?")){');
        addPortaljavaScript('		setLocation(link+id);');
        addPortaljavaScript('	}');
        addPortaljavaScript('}');
        
    }
    
    private function verificarAcaoSys016($usuario, $acao)
    {
        $ret = false;
        if($acao == strtolower('arquivar'))
        {
            //testa  usuário - permissão
            $sql = "SELECT * FROM sys016 WHERE usuario = '$usuario'";
            $rows = query($sql);
            if(is_array($rows) && count($rows)>0){
            //    foreach($rows as $row){
             //       if($row['item'] == 'coordenador'){
                        $ret = true;
             //       }
             //   }
            }
        } /*else if($acao == strtolower(''))
        {
            //
        }*/
        return $ret;
    }
    
    private function getDatas($operacao){
        $ret = [];
        
        if($operacao == 'semana'){
            $sem = date('w');
            $ret['ini'] = datas::getDataDias($sem * -1);
            $ret['fim'] = datas::getDataDias(6 - $sem);
        }else{
            $ret['ini'] = date('Ym').'01';
            $ret['fim'] = date('Ymt');
        }
        
        return $ret;
    }
    
    public function ajax(){
        $ret = array();
        
        
        $ret[] = array('valor' => '', 'etiqueta' => '');
        $cliente = getParam($_GET, 'cliente', '');
        
        if($cliente != ''){
            $sql = "select * from sdm_projetos where cliente = '$cliente'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $temp = array(
                        'valor' => $row['id'],
                        'etiqueta' => $row['titulo'],
                    );
                    $ret[] = $temp;
                }
            }
        }
        return json_encode($ret);
    }
    
    private function addAjaxProjetos(){
        $ret = "function callAjax(){
	        var cliente = document.getElementById('cliente').value;
	        var option = '';
	        $.getJSON('" . getLinkAjax('projeto') . "&cliente=' + cliente, function (dados){
	            if (dados.length > 0){
	                $.each(dados, function(i, obj){
	                    option += '<option value=" . '"' . "'+obj.valor+'" . '"' . ">'+obj.etiqueta+'</option>';
	                    $('#projeto').html(option).show();
	                });
	            }
	        })
	    }";
        addPortaljavaScript($ret);
    }
    
}

function sdm_agenda_clientes(){
    $ret = array();
    
    $ret[0][0] = "";
    $ret[0][1] = "&nbsp;";
    
    $sql = "SELECT cod, nreduz FROM cad_organizacoes WHERE ativo = 'S' ORDER BY nreduz";
    $rows = query($sql);
    
    if(is_array($rows) && count($rows) > 0){
        foreach ($rows as $row) {
            $temp[0] = $row['cod'];
            $temp[1] = $row['nreduz'];
            
            $ret[] = $temp;
        }
    }
    //print_r($tabela);
    return $ret;
}

function sdm_agenda_recursos(){
    $ret = array();
    
    $ret[0][0] = "";
    $ret[0][1] = "&nbsp;";
    
    $sql = "SELECT usuario, apelido FROM sdm_recursos WHERE agenda = 'S'  ORDER BY apelido";
    $rows = query($sql);
    
    if(is_array($rows) && count($rows) > 0){
        foreach ($rows as $row) {
            $temp[0] = $row['usuario'];
            $temp[1] = $row['apelido'];
            
            $ret[] = $temp;
        }
    }
    //print_r($tabela);
    return $ret;
}