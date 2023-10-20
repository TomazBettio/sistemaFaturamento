<?php
/*
 * Data Criação: 07/11/2014 - 10:15:03
 * Autor: Thiel
 *
 * Arquivo: tws.canhoto.inc.php
 *
 * Programa para realizar o apontamento do retorno do canhoto das NFs
 * 
 *  Alterações:
 *             16/11/2018 - Emanuel - Migração para intranet2
 *             01/02/2023 - Rafael Postal - Migração para intranet4
 *             24/07/2023 - Alex Cesar - Atualização Protocolo
 */

/*
 * TODO:
 * Excluir protocolo (deve verificar se o mesmo é vazio)
 * Editar protocolo: permite que passe as notas de um protocolo para outro manualmente
 * incluir: atualizar caso seja incluido notas de outro protocolo - ok
 * incluir: atualizar tabela de protocolos com última inclusão e por quem
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

#[\AllowDynamicProperties]
class canhoto_novo{
    
    var $funcoes_publicas = array(
        'index' 		=> true,
        'excluir'        => true,
        'ajax'		    => true,
        'canhoto'       => true,
        'novoProtocolo' => true,
        'lisNotasProtocolo'  => true,
        'editar'        => true,
        'salvar'        => true,
        'validaProtocolo'=>true,
    );
    
    //MINHAS TABELAS
    /*
    private $_tabIdProtocolo = 'gf_canhoto_protocolo_id';
    private $_tabProtocolo = 'gf_canhoto_protocolo_nota';
    private $_tabCanhoto = 'gf_canhotos';
    */
    
    //Relatório
    private $_relatorio;
    //Nome do programa
    private $_programa;
    //Titulo do relatorio
    private $_titulo;
    
    var $_linhas;
    var $_colunas;
    
    var $_userWT;
    
    public function __construct(){
        $this->_linhas = 10;
        $this->_colunas = 4;
        
        $this->_userWT = '';
        $this->addScript();
        
        $this->_programa = get_class($this);
        $this->_titulo = 'Visualização dos Canhotos';
        
        $param = [];
        $param['programa'] = $this->_programa;
        $param['titulo'] = $this->_titulo;
        $param['cancela'] = true;
        $this->_relatorio = new relatorio01($param);
     
        
        if(false){
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Protocolo'        , 'variavel' => 'IDPROTOCOLO', 'tipo' => 'N', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Nota'	         , 'variavel' => 'NOTA', 'tipo' => 'N', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Data da Nota (Início)'     , 'variavel' => 'DATANOTAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
            sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Data da Nota (Fim)'     , 'variavel' => 'DATANOTAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
            
        }
        
    }
    
    private function jsConfirmaExclusao($titulo){
        $ret = "
            function confirmaExclusao(link,id){
            	if (confirm('$titulo')){
            		setLocation(link+id);
            	}
            }
            ";
        addPortaljavaScript($ret);
        
    }
    
    public function index()
    {
        $ret = '';
        
        $tab = new tabela01(['titulo' => 'Protocolos Registrados']);
        $tab->addColuna(array('campo' => 'id'	        , 'etiqueta' => 'Protocolo'		        , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'data_criacao'	, 'etiqueta' => 'Criado em'		, 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'data_atualiza', 'etiqueta' => 'Última Atualização'	, 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'ultima_nota'	, 'etiqueta' => 'Última Nota Inserida'	, 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        
        $param = [
            'texto' =>  'Incluir Notas',
            'link' 	=> getLink()."canhoto&protocolo=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'success',
        ];
        $tab->addAcao($param);
        $param = [
            'texto' =>  'Transferir Notas',
            'link' 	=> getLink()."editar&protocolo=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'primary',
        ];
        $tab->addAcao($param);
        
        $this->jsConfirmaExclusao("Você REALMENTE deseja excluir esse protocolo?");
        $param = [
            'texto' =>  'Excluir Protocolo',
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&id=','{ID}')",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'danger',
        ];
        $tab->addAcao($param);
        
        $dados = $this->getProtocolos();
        $tab->setDados($dados);
        
        $botao = [
            'id' => 'incluir',
            'onclick' => "setLocation('".getlink()."novoProtocolo')",
            'texto' => 'Criar Novo Protocolo',
            'cor' => 'success',
        ];
        $tab->addBotaoTitulo($botao);
        
        $botao = [
            'id' => 'visualizar',
            'onclick' => "setLocation('".getlink()."lisNotasProtocolo')",
            'texto' => 'Consulta'
        ];
        $tab->addBotaoTitulo($botao);
        
        //$ret = addCard(['conteudo' => $tab, 'titulo' => 'Protocolos Registrados']);
        $ret .= $tab;
        return $ret;
    }
    
    private function getProtocolos()
    {
        $ret = [];
        
        $sql = "SELECT * FROM gf_canhoto_protocolo_id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $temp = [];
                
                $temp['id'] = $row['id'];
                
              //  $data = explode(' ',$row['data_criacao']);
              //  $temp['data_criacao'] = datas::dataMS2D($row['data_criacao']) . ' - ' . $data[1];
                $temp['data_criacao'] = datas::dataMS2D($row['data_criacao']);
              //  $data = explode(' ',$row['data_atualiza']);
              //  $temp['data_atualiza'] = datas::dataMS2D($row['data_atualiza']) . ' - ' . $data[1];
                $temp['data_atualiza'] = datas::dataMS2D($row['data_atualiza']);
                $temp['ultima_nota'] = $this->getUltimaNotaProtocolo($temp['id']);
                
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function getUltimaNotaProtocolo($id)
    {
        $ret = ' --- ';
        $sql = "SELECT nota FROM gf_canhoto_protocolo_nota where protocolo=$id order by data DESC";
        $rows = query($sql);
        if(!empty($rows) && count($rows)>0){
            $ret = $rows[0]['nota'];
        }
        return $ret;
    }

    public function canhoto()
    {
        $ret = '';
        $protocolo = getParam($_GET, 'protocolo',0);
        $form = $this->printForm2($protocolo);
        $ret = $form;
        return $ret;
    }
    
    public function novoProtocolo()
    {
        $sql = "INSERT INTO gf_canhoto_protocolo_id (aberto_por,atualizado_por) VALUES ('".getUsuario()."','".getUsuario()."')";
        $protocolo = query($sql);
        //echo $sql;die();
        addPortalMensagem("SUCESSO! Criado protocolo de Nro: $protocolo");
        redireciona(getLink() . "canhoto&protocolo=$protocolo" );
    }
    
    public function lisNotasProtocolo()
    {
        $ret = '';
        
        $this->_relatorio->addColuna(array('campo' => 'protocolo', 'etiqueta' => 'Protocolo'	           , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'NUMNOTA', 'etiqueta' => 'Nota'	         	       , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'DTSAIDA', 'etiqueta' => 'Data de Inserção'	       , 'tipo' => 'D', 'width' => '5'  , 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'CODCLI', 'etiqueta' => 'Código Cliente'             , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'CLIENTE', 'etiqueta' => 'Cliente'	               , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'CODFORNECFRETE', 'etiqueta' => 'Código Fornecedor'  , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'FORNECEDOR', 'etiqueta' => 'Fornecedor'	           , 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'VLTOTAL', 'etiqueta' => 'Valor Total'	           , 'tipo' => 'N', 'width' => '5'  , 'posicao' => 'C'));
        
        
        $filtro = $this->_relatorio->getFiltro();
        
        if(!$this->_relatorio->getPrimeira()){
            $dados = $this->getDadosListagemProtocolo($filtro);
            $this->_relatorio->setDados($dados);
        }
        
        $ret .= $this->_relatorio;
        return $ret;
    }
    
    //Função apenas para query
    private function queryProtocolo($nota = 0, $dataini = 0, $datafim = 0, $protocolo = 0)
    {
        $ret = [];
        $campos = ['NUMNOTA', 'DTSAIDA','CODCLI', 'CLIENTE', 'CODFORNECFRETE','FORNECEDOR', 'VLTOTAL'];
        $protocolos = $this->protocolosMinhasNotas();
        
        if(!empty($nota) || (!empty($dataini) && !empty($datafim)))
        {
            $where = 'WHERE 1 = 1 ';
            
            if(!empty($nota)){
                if(is_array($nota)){
                    $nota = implode(',', $nota);
                }
                $where .= "AND PCNFSAID.NUMNOTA IN ($nota) ";
            }
            if(!empty($dataini) && !empty($datafim)){
                $dataini = datas::dataS2D($dataini);
                $datafim = datas::dataS2D($datafim);
                $where .= " AND PCNFSAID.DTSAIDA BETWEEN to_date('$dataini','DD/MM/YYYY') AND to_date('$datafim','DD/MM/YYYY') ";
            }
            $sql = "SELECT
                     PCNFSAID.NUMNOTA
                    ,PCNFSAID.DTSAIDA
                    ,PCNFSAID.CODCLI
                    ,PCCLIENT.CLIENTE
                    ,PCNFSAID.VLTOTAL
                    ,PCNFSAID.DTCANHOTO
                    ,PCNFSAID.CODFUNCLANC
                    ,PCEMPR.NOME
                    ,PCNFSAID.CODFORNECFRETE
                    ,PCFORNEC.FORNECEDOR
                FROM
                    PCNFSAID
                    LEFT JOIN PCCLIENT ON (PCCLIENT.CODCLI    = PCNFSAID.CODCLI)
                    LEFT JOIN PCEMPR   ON (PCEMPR.MATRICULA   = PCNFSAID.CODFUNCLANC)
                    LEFT JOIN PCFORNEC ON (PCFORNEC.CODFORNEC = PCNFSAID.CODFORNECFRETE)
                $where
                ORDER BY
                    PCNFSAID.DTSAIDA
                ";
            $rows = query4($sql);
            if(is_array($rows) && count($rows)>0)
            {
                foreach($rows as $row)
                {
                    $temp = [];
                    foreach($campos as $camp){
                        $temp[$camp] = $row[$camp];
                    }
                    $temp['DTSAIDA'] = str_replace('-', '', $temp['DTSAIDA']);
                    $temp['protocolo'] = ($protocolo!=0) ? $protocolo : ($protocolos[$temp['NUMNOTA']] ?? '');
                    
                    $ret[] = $temp;
                }
            }
        }
        return $ret;
    }
    
    //Função que retorna uma lista de todas as notas num dado protocolo
    private function notasMeuProtocolo($protocolo,$nota=0)
    {
        $ret = [];
        $sql = "SELECT nota FROM gf_canhoto_protocolo_nota WHERE protocolo = $protocolo";
        if($nota != 0){
            $sql .= " AND nota = $nota";
        }
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $ret[] = $row['nota'];
            }
        }
        return $ret;
    }
    
    //Função que retorna array com todas as notas que tem protocolos (nota=>protocolo)
    private function protocolosMinhasNotas()
    {
        $ret = [];
        $protocolos = [];
        $sql = "SELECT nota, protocolo FROM gf_canhoto_protocolo_nota";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row)
            {
                $protocolos[$row['nota']] = $row['protocolo'];
            }
        }
        $ret = $protocolos;
        return $ret;
    }
    
    private function getDadosListagemProtocolo($filtro)
    {
        $ret = [];
        
        $nota 	= $filtro['NOTA'] ?? 0;
        $data_ini 	= $filtro['DATANOTAINI'] ?? '';
        $data_fim 	= $filtro['DATANOTAFIM'] ?? '';
        $protocolo = $filtro['IDPROTOCOLO'] ?? 0;
        if(!empty($protocolo))
        {
            //pega a lista de notas do protocolo, testa de 10 em 10 notas a função de query
            $notas = $this->notasMeuProtocolo($protocolo,$nota);
            if(count($notas) > 0)
            {
                if(count($notas)<=10){
                    $ret = $this->queryProtocolo($notas, $data_ini,$data_fim,$protocolo);
                } else {
                    while(count($notas)>0)
                    {
                        //pegar notas de 10 em 10
                        $dez_notas = [];
                        $temp = [];
                        $itera = 10;
                        while($itera>0 && count($notas)>0)
                        {
                            $dez_notas[] = array_pop($notas);
                            $itera--;
                        }
                        $temp = $this->queryProtocolo($dez_notas,$data_ini,$data_fim,$protocolo);
                        $ret = array_merge($temp,$ret);
                    }
                }
            }
            
        } else {
            //passa outros filtros para a função
            $ret = $this->queryProtocolo($nota,$data_ini,$data_fim);
        }
        
        return $ret;
    }
    
    private function printForm2($protocolo)
    {
        $ret = '';
        $card1 = '';
        $card2 = '';
        $formCanhoto = new form01();
        $formCanhoto->addHidden('protocolo', $protocolo);
        $formCanhoto->addCampo(array('id' => 'protocolo'		,'campo' => 'protocolo'	,'etiqueta' => 'Protocolo'	    ,'tipo' => 'N'	,'linha' => 1, 'largura' => 3	,'tamanho' => '10','linhas' => '','valor' => $protocolo	,'pasta' => 0 ,'lista' => ''		,'validacao' => '','obrigatorio' => true, 'readonly' => true));
        $formCanhoto->addCampo(array('id' => 'form_data'		,'campo' => 'form_data' /*'formCanhoto[data]'		*/	,'etiqueta' => 'Data'			,'tipo' => 'D'	,'linha' => 1, 'largura' => 3	,'tamanho' => '10','linhas' => '','valor' => date('d/m/Y')	,'pasta' => 0 ,'lista' => ''		,'validacao' => '','obrigatorio' => true));
        $formCanhoto->addCampo(array('id' => 'canhoto'		,'campo' => 'canhoto' /*'formCanhoto[canhoto]'	*/		,'etiqueta' => 'Canhoto'			,'tipo' => 'N'	,'linha' => 2, 'largura' => 10	,'tamanho' => '30','linhas' => '','valor' => ''	,'pasta' => 0 ,'lista' => ''		,'validacao' => '','obrigatorio' => true, 'classeadd' => ' pulaCampo'));
        $card1 .= addCard(['conteudo' => $formCanhoto, 'titulo' => 'Lançamento de Canhoto NF']);
        $card2 .= addCard(['conteudo' => '<div id="lancadas"></div>', 'titulo' => 'NF Lançadas']);
        $linha = addLinha(['tamanhos' => [9,3],'conteudos' => [$card1,$card2]]);
        
        $param = [
            'conteudo' => $linha . $this->minhasNFs($protocolo),
            'titulo' => "Protocolo $protocolo - NFs",
            'botoesTitulo' => [
                [
                    'texto' => "Salvar & Voltar",
                    'onclick' => "setLocation('".getLink()."index')",
                    'cor' => 'success',
                ],
    /*            [
                    'texto' => "Cancelar", //TODO:apagar os novos
                    'onclick' => "setLocation('".getLink()."index')",
                    'cor' => 'danger',
                ],*/
            ]
        ];
        
        $ret .= addCard($param);
        return $ret;
    }
    
    public function editar()
    {
        $ret = '';
        $protocolo = getParam($_GET, 'protocolo',0);
        //Como editar? Passar todas as notas para outro protocolo
        if($this->temNotas($protocolo))
        {
            $form = new form01();
            $param = [
                'id' => 'protocolo_old',
                'campo' => 'protocolo_old',
                'etiqueta' => 'Protocolo Atual',
                'tipo' => 'N'	,'linha' => 1,
                'largura' => 3	,'tamanho' => '10',
                'linhas' => '',
                'valor' => $protocolo,
                'pasta' => 0 ,
                'lista' => '',
                'validacao' => '',
                'obrigatorio' => true,
                'readonly' => true
            ];
            $form->addCampo($param);
            $param = [
                'id' => 'protocolo',
                'campo' => 'protocolo',
                'etiqueta' => 'Protocolo Novo',
                'tipo' => 'N'	,'linha' => 1,
                'largura' => 3	,'tamanho' => '10',
                'linhas' => '',
                'valor' => '',
                'pasta' => 0 ,
                'lista' => '',
                'validacao' => '',
                'obrigatorio' => true,
            ];
            $form->addCampo($param);
            
            $form->setEnvio(getLink()."salvar", 'protocolo');
            
            $ret .= addCard(['conteudo'=>$form . $this->minhasNFs($protocolo,'Canhotos a Transferir'),'titulo'=>'Transferir Canhotos']);
            return $ret;
        } else {
            addPortalMensagem("Protocolo NÃO POSSUI notas fiscais", 'error');
            return $this->index();
        }
    }
    
    public function salvar()
    {
        $antigo = getParam($_POST, 'protocolo_old',0);
        $protocolo = getParam($_POST, 'protocolo',0);
        
        if($antigo==$protocolo){
            addPortalMensagem("Protocolo novo e antigo IGUAIS", 'error');
        } else if($this->protocoloExiste($protocolo))
        {
            $sql = "UPDATE gf_canhoto_protocolo_nota SET protocolo = $protocolo WHERE protocolo = $antigo";
            query($sql);
    
            $sql = "UPDATE gf_canhoto_protocolo_id SET atualizado_por = '".getUsuario()."', data_atualiza = CURRENT_TIMESTAMP()  WHERE id = $antigo OR id = $protocolo";
            query($sql);
        } else {
            addPortalMensagem("Protocolo informado NÃO EXISTE!", 'error');
        }
        
        
        redireciona(getLink().'index');
    }
    
    private function temNotas($protocolo)
    {
        $ret = false;
        $sql = "SELECT * FROM gf_canhoto_protocolo_nota WHERE protocolo = $protocolo";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = true;
        }
        return $ret;
    }
    
    private function minhasNFs($protocolo, $titulo = '')
    {
        $ret = '';
        $tab = new tabela01(['titulo'=>$titulo]);
        $tab->addColuna(array('campo' => 'nota'	, 'etiqueta' => 'NF'		, 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        $tab->addColuna(array('campo' => 'data'	, 'etiqueta' => 'Data de Inclusão'		, 'tipo' => 'T', 'width' => '5'  , 'posicao' => 'C'));
        
        $dados = $this->getNFs($protocolo);
        $tab->setDados($dados);
        $ret .= $tab;
        return $ret;
    }
    
    private function getNFs($protocolo)
    {
        $ret = [];
        $sql = "SELECT * FROM gf_canhoto_protocolo_nota WHERE protocolo = $protocolo";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $temp = [];
                
                $temp['id'] = $row['id'];
                $temp['nota'] = $row['nota'];
                
                $data = explode(' ',$row['data']);
                $temp['data'] = datas::dataMS2D($row['data']) . ' - ' . $data[1];
                
                $ret[]=$temp;
            }
        }
            
        return $ret;
    }
    
    public function excluir()
    {
        $protocolo = getParam($_GET, 'id');
        $sql = "SELECT * FROM gf_canhoto_protocolo_id WHERE id=$protocolo";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $sql = "SELECT * FROM gf_canhoto_protocolo_nota WHERE protocolo=$protocolo";
            $rows = query($sql);
            if(!(is_array($rows) && count($rows)>0)){
                $sql = "DELETE FROM gf_canhoto_protocolo_id WHERE id=$protocolo";
                query($sql);
            } else {
                addPortalMensagem("O protocolo POSSUI NFs e não pôde ser excluído",'error');
            }
            //return $this->index();
        }
        redireciona(getLink().'index');
    }
    
    private function atualiza($nota,$data,$protocolo)
    {
        $dataT = explode('/', $data);
        
        if($this->_userWT == ''){
            $sql = "SELECT matricula FROM pcempr WHERE NOME_GUERRA = '".strtoupper(getUsuario())."'";
            echo '1 ' . $sql . " - ";
            $rows = query4($sql);
            
            if(count($rows) > 0){
                $this->_userWT = $rows[0][0];
            }else{
                $sql = "SELECT matricula FROM pcempr WHERE matricula = '".getUsuario()."'";
                echo '2 ' . $sql . " - ";
                $rows = query4($sql);
                if(is_array($rows) && count($rows) > 0){
                    $this->_userWT = $rows[0][0];
                }
            }
        }
        
        $usuario = $this->_userWT;
        if(checkdate( $dataT[1], $dataT[0], $dataT[2]) === false){
            return '<div style="color: red;">'.$nota.' - '.$data.' - Inválida</div>';
            
        }
        //Verifica se a nota já não foi informada
        $sql = "SELECT CODFUNCLANC, DTCANHOTO, OBSNFCARREG FROM PCNFSAID WHERE NUMNOTA = $nota";
        echo '3 ' . $sql . " - ";
        $rows = query4($sql);
        if(!is_array($rows) || count($rows) == 0){
            return '<div style="color: red;">'.$nota.' - '.ajustaCaractHTML('Não existe!').'</div>';
        }else{
            if($rows[0][0] != 0){
                $sql = "update PCNFSAID set DTCANHOTO2 = to_date('".$data."','DD/MM/YYYY') where NUMNOTA = $nota";
                query4($sql);
                $sql = "INSERT INTO gf_canhotos (nota) VALUES ($nota);";
                query($sql);
                //Testa se a nota já foi informada, se sim atualiza protocolo, senão insere
                if($this->notaExiste($nota)){
                    $sql = "UPDATE gf_canhoto_protocolo_nota SET protocolo = $protocolo, data = CURRENT_TIMESTAMP()  WHERE nota = $nota";
                } else {
                    $sql = "INSERT INTO gf_canhoto_protocolo_nota (protocolo, nota) VALUES ($protocolo,$nota)";
                }
                query($sql);
                //atualiza protocolo
                $sql = "UPDATE gf_canhoto_protocolo_id SET atualizado_por = '".getUsuario()."', data_atualiza = CURRENT_TIMESTAMP() WHERE id = $protocolo";
                query($sql);
                return '<div style="color: red;">'.$nota.' - '.ajustaCaractHTML('Nota já informada - Adicionada data 2!').'</div>';
            }else{
                $sql = "update PCNFSAID set CODFUNCLANC = $usuario, DTCANHOTO = to_date('".$data."','DD/MM/YYYY'), OBSNFCARREG = '' where NUMNOTA = $nota";
                query4($sql);
                $sql = "INSERT INTO gf_canhotos (nota) VALUES ($nota);";
                query($sql);
                $sql = "INSERT INTO gf_canhoto_protocolo_nota (protocolo, nota) VALUES ($protocolo,$nota)";
                query($sql);
                //atualiza protocolo
                $sql = "UPDATE gf_canhoto_protocolo_id SET atualizado_por = '".getUsuario()."', data_atualiza = CURRENT_TIMESTAMP()  WHERE id = $protocolo";
                query($sql);
                return '<strong>'.$nota.' - '.$data.' - '.ajustaCaractHTML('Incluída!').'</strong>';
            }
        }
        return $nota.' - '.$data;
        
        
    }
    
    private function protocoloExiste($protocolo)
    {
        $ret = false;
        $sql = "SELECT * FROM gf_canhoto_protocolo_id WHERE id = $protocolo";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = true;
        }
        return $ret;
    }
    
    private function notaExiste($nota)
    {
        $ret = false;
        $sql = "SELECT * FROM gf_canhoto_protocolo_nota WHERE nota = $nota";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = true;
        }
        return $ret;
    }
    
    public function validaProtocolo()
    {
        $ret = false;
        $protocolo = $_GET['protocolo'];
        $sql = "SELECT * FROM gf_canhoto_protocolo_id WHERE id = $protocolo";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = true;
        }
        echo $ret;
    }
    
    function ajax(){
        $op = getOperacao();
        
        switch ($op)
        {
            case 'novoProtocolo':
                $this->novoProtocolo();
                return $this->index();
                break;
            case 'atualiza':
                $nota = $_GET['nota'];
                $data = $_GET['data'];
                $protocolo = $_GET['protocolo'];
                return $this->atualiza($nota, $data,$protocolo);
                break;
            default:
                break;
        }
        
    }
    
    function addScript(){
        $ret = "
        $('#canhoto').focus(); 
        $('.pulaCampo').keypress(function(e){
        	var tecla = (e.keyCode?e.keyCode:e.which);
            if(tecla == 13)
            {
        	   dataApont = $('#form_data').val();
        	   if (dataApont == '')
               {  
        		  alert ('Preencha o campo com a data!');     
        		  $('#form_data').focus();  
        		  return false;  
        	   }
               idProt = $('#protocolo').val();
        	   if (idProt == '')
               {  
        		  alert ('Preencha o campo do protocolo!');     
        		  $('#protocolo').focus();  
        		  return false;  
        	   }
        	   nota = $('#canhoto').val();
        	   $.ajax({
        		  url: '".getLinkAjax('atualiza')."&nota=' + nota + '&data=' + dataApont + '&protocolo=' + idProt,
        		  success: function(data) {
        		    $('<p style=\"font-size: 12px;\"/>').html(data).prependTo('#lancadas');
        			$('#canhoto').val('');
        		  }
        		});
        	   campo = $('.pulaCampo');
        	   indice = campo.index(this);
        	   if(campo[indice+1] != null)
               {
        		  proximo = campo[indice + 1];
        		  proximo.focus(); 
        	   } 
            }else{ return true;}
        e.preventDefault(e); 
        return false; 
        })
";
        addPortalJquery($ret);
    }
}