<?php
/*
 * Data Criação: 11/09/2023
 * Autor: BCS
 *
 * Descricao: 	Parâmetros de certificação (usados na requisição)
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class param_certificacoes{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'papeisCadeia'  => true,
        'parametros'    => true,
        'editar'        => true,
        'salvar'        => true,
        'excluir'       => true,
    );
    
    private $_programa = 'param_certificacoes';
    
    
    public function __construct(){
        if(true){
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Tipo'	        , 'variavel' => 'tipo'		  , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => "montarDicionarioSys005('TIPOP')"	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Solicitante'	, 'variavel' => 'solicitante' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ' = ;S=Solicitante;E=Externo' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Obrigatório'	, 'variavel' => 'obrigatorio' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ' = ;S=Sim;N=Não' ]);
        }
        
        $javascript = "
            function comparaPapel(id_campo)
            {
                var formPapel = document.getElementById('formParampapel').value;
                var formAprova = document.getElementById('formParampapel_aprovador').value;
                if (formPapel == formAprova){
                    document.getElementById(id_campo).value = '';
                    alert('O papel do parâmetro não pode ser igual ao papel do seu aprovador');
                }
            } ";
        addPortaljavaScript($javascript);
    }
    
    public function index()
    {
        //A index inicia com uma lista (botões) das cadeias COM PARÂMETROS
        $ret = '';
        $param = [
            'titulo' => 'Cadeias',
            'conteudo' => $this->montaGrid("cadeias"),
            'botoesTitulo' => [
                [
                    'onclick' => "setLocation('".getLink()."editar')",
                    'id' => 'incluirParamVazio',
                    'texto' => 'Novo Parâmetro',
                    'cor' => 'success',
                ]
            ]
        ];
        $ret = addCard($param);
        return $ret;
    }
    
    private function montaGrid($dado, $cadeia = 0)
    {
        $ret = '';
        
        $dados = [];
        if($dado == strtolower("cadeias")){
            $dados = $this->getCadeias();
        } else if ($dado == strtolower("papeis")){
            $dados = $this->getPapeis($cadeia);
        }
        $temp = [];
        while(count($dados) > 0)
        {
            $temp_linha = [];
            while(count($temp_linha) < 3 && count($dados) > 0)
            {
                //enquanto cabe na linha (3 itens por linha), insere botão
                $codigo_temp = array_key_first($dados);
                $param['texto'] 	= $dados[$codigo_temp]['descricao'];
                $param['width'] 	= 30;
                $param['flag'] 		= '';
                $link = '';
                if($dado == strtolower("cadeias")){
                    $link .= "papeisCadeia&cadeia={$dados[$codigo_temp]['id']}'";
                } else if ($dado == strtolower("papeis")){
                    $link .= "parametros&cadeia=$cadeia&papel={$dados[$codigo_temp]['id']}'";
                }
                $param['onclick'] 	= "setLocation('" . getLink() . "$link)";
                $param['cor'] 		= 'success';
                $param['bloco'] 	= true;
                $temp_linha[] = formbase01::formBotao($param);
                unset($dados[$codigo_temp]);
            }
            
            while(count($temp_linha) < 3){
                $temp_linha[] = '';
            }
            
            $param = array();
            $param['tamanhos'] = array(4, 4, 4);
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
            
        }
        
        if(is_array($temp) && count($temp) > 0){
            $ret .= '<br><br>';
            $ret .= implode('<br>', $temp);
        }
        
        return $ret;
    }
    
    private function getCadeias()
    {
        $ret = [];
        $sql = "SELECT pmp_cadeia.id, pmp_cadeia.descricao FROM pmp_cadeia 
                JOIN pmp_param_certificacao ON pmp_cadeia.id = pmp_param_certificacao.cadeia 
                WHERE pmp_cadeia.ativo = 'S' AND pmp_param_certificacao.ativo = 'S' GROUP BY pmp_cadeia.id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            foreach($rows as $row){
                $temp['id'] = base64_encode($row['id']);
                $temp['descricao'] = $row['descricao'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function papeisCadeia()
    {
        //Para uma dada cadeia, lista (botões) dos papéis COM PARÂMETROS
        $ret = '';
        $cadeia = getParam($_GET, 'cadeia','');
        if($cadeia!='')
        {
            $param = [
                'titulo' => 'Papéis Registrados',
                'conteudo' => $this->montaGrid("papeis",$cadeia),
                'botoesTitulo' => [
                    [
                        'onclick' => "setLocation('".getLink()."editar&cadeia=$cadeia')",
                        'id' => 'incluirParamCadeia',
                        'texto' => 'Novo Parâmetro',
                        'cor' => 'success',
                    ],
                    [
                        'onclick' => "setLocation('".getLink()."index')",
                        'id' => 'returnCadeia',
                        'texto' => 'Voltar a Cadeias',
                        'cor' => 'warning',
                    ]
                ]
            ];
            $ret = addCard($param);
        }
        return $ret;
    }
    
    private function getPapeis($cadeia64)
    {
        $ret = [];
        $cadeia = base64_decode($cadeia64);
        $sql = "SELECT pmp_papel.id, pmp_papel.descricao FROM pmp_papel
                JOIN pmp_param_certificacao 
                ON pmp_papel.id = pmp_param_certificacao.papel AND pmp_param_certificacao.cadeia = $cadeia 
                WHERE pmp_papel.ativo = 'S' AND pmp_param_certificacao.ativo = 'S' GROUP BY pmp_papel.id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            foreach($rows as $row){
                $temp['id'] = base64_encode($row['id']);
                $temp['descricao'] = $row['descricao'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function parametros()
    {
        //Lista de parâmetros (tabela) para uma cadeia/papel -> USAR FILTRO
        $ret = '';
        $cadeia = getParam($_GET, 'cadeia','');
        $papel = getParam($_GET, 'papel','');
        if($cadeia != '' && $papel != '')
        {
            $param = [
                'programa' => $this->_programa, 
                'titulo' => 'Parâmetros Registrados', 
                'mostraFiltro' => true, 
                'filtroTipo' => 1
            ];
            $tab = new tabela02($param);
            $tab->addColuna(array('campo' => 'titulo'       , 'etiqueta' => 'Título'	       	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'esquerda'));
            $tab->addColuna(array('campo' => 'tipo'         , 'etiqueta' => 'Tipo'  	        , 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
            $tab->addColuna(array('campo' => 'obrigatorio'  , 'etiqueta' => 'Obrigatório'   	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
            $tab->addColuna(array('campo' => 'responsavel'  , 'etiqueta' => 'Responsável'	   	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
            $tab->addColuna(array('campo' => 'aux'          , 'etiqueta' => 'Dados Auxiliares'  , 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
            
            // Botão Editar
            $param = [];
            $param['texto'] = 'Editar';
            $param['link'] 	= getLink()."editar&id=";
            $param['coluna']= 'id';
            $param['width'] = 10;
            $param['cor'] 	= 'success';
            $tab->addAcao($param);
            
            // Botão Excluir
            $this->jsConfirmaExclusao('"Confirme a exclusão do parâmetro"');
            $param = [];
            $param['texto'] = 'Excluir';
            $param['link'] 	= "javascript:confirmaExclusao('" . getLink() ."excluir&cadeia=$cadeia&papel=$papel&id=" . "','{ID}')";
            $param['coluna']= 'id';
            $param['width'] = 10;
            $param['cor'] 	= 'danger';
            $tab->addAcao($param);
            
            $p = [];
            $p['onclick'] = "setLocation('".getLink()."editar&cadeia=$cadeia&papel=$papel')";
            $p['texto'] = 'Incluir';
            $p['cor'] = 'success';
            $tab->addBotaoTitulo($p);
            
            $p = [];
            $p['onclick'] = "setLocation('".getLink()."papeisCadeia&cadeia=$cadeia')";
            $p['texto'] = 'Voltar a Papéis';
            $p['cor'] = 'warning';
            $tab->addBotaoTitulo($p);
            
            $filtro = $tab->getFiltro();
            //if(!$tab->getPrimeira()){
                $dados = $this->getParam($cadeia, $papel, $filtro);
                $tab->setDados($dados);
            //}
            
            $ret .= $tab;
        }
        return $ret;
    }
    
    private function getParam($cadeia64,$papel64, $filtro)
    {
        $ret = [];
        //var_dump($filtro);
        
        $cadeia = base64_decode($cadeia64);
        $papel = base64_decode($papel64);
        
        $sql = "SELECT * FROM pmp_param_certificacao WHERE cadeia = $cadeia AND papel = $papel AND ativo = 'S' ";
        if(isset($filtro['tipo']) && $filtro['tipo'] != ''){
            $sql .= " AND tipo = '{$filtro['tipo']}'";
        }
        if(isset($filtro['solicitante']) && trim($filtro['solicitante']) != ''){
            $sql .= " AND solicitante = '{$filtro['solicitante']}'";
        }
        if(isset($filtro['obrigatorio']) && trim($filtro['obrigatorio']) != ''){
            $sql .= " AND obrigatorio = '{$filtro['obrigatorio']}'";
        }
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            $campos = ['id','cadeia','papel','tipo','titulo','aux','responsavel','papel_aprovador','obrigatorio',];
            $dicionario = montarDicionarioSys005('TIPOP');
            foreach($rows as $row){
                foreach($campos as $campo){
                    $temp[$campo] = $row[$campo];
                }
                $temp['id'] = base64_encode($temp['id']);
                $temp['tipo'] = $dicionario[$temp['tipo']];
                $temp['responsavel'] = $temp['responsavel'] == 'S' ? 'Solicitante' : 'Externo';
                $temp['obrigatorio'] = $temp['obrigatorio'] == 'S' ? 'Sim' : 'Não';
                $ret[] = $temp;
            }
        }
        return $ret;
    }

    public function editar()
    {
        //Verificar casos: sem cadeia/papel, com cadeia sem papel, com cadeia/papel
        $ret = '';
        
        $cadeia64 = getParam($_GET, 'cadeia','');
        $papel64 = getParam($_GET, 'papel','');
        $id64 = getParam($_GET, 'id','');
        
        $tipo_cadeia = 'A';
        $tipo_papel = 'A';
        $lista_cadeia = $this->selectCadeia();
        $lista_papel = $this->selectPapel();
        $cancelar = getLink();
        $envio = getLink()."salvar";
        $papel_param = '';
        
        $dados = $id64 == '' ? $this->getDadosEditar(0) : $this->getDadosEditar(base64_decode($id64));
        
        //testes: 
        //novo vazio
        //novo só cadeia
        //novo cadeia e papel
        //editar cadeia e papel
        
        if($cadeia64 != '')
        {
            $tipo_cadeia = 'I';
            $dados['cadeia'] = $this->traduzDescricao('pmp_cadeia',base64_decode($cadeia64));
            $lista_cadeia = '';
            
            if($papel64 != '')
            {
                //novo cadeia e papel
                $tipo_papel = "I";
                $papel_param = base64_decode($papel64);
                $dados['papel'] = $this->traduzDescricao('pmp_papel',$papel_param);
                $lista_papel = "";
                
                $cancelar .= "parametros&cadeia=$cadeia64&papel=$papel64";
                $envio .= "&cadeia=$cadeia64&papel=$papel64";
            } else {
                //novo só cadeia
                $cancelar .= "papeisCadeia&cadeia=$cadeia64";
                $envio .= "&cadeia=$cadeia64";
            }
            
            
        } else if ($id64 != '') {
            //editar
            $papel_cadeia = $this->getPapelCadeiaID(base64_decode($id64));
            
            $cad64 = base64_encode($papel_cadeia['cadeia']);
            $pap64 = base64_encode($papel_cadeia['papel']);

            $dados['papel'] = $this->traduzDescricao('pmp_papel',$papel_cadeia['papel']);
            $dados['cadeia'] = $this->traduzDescricao('pmp_cadeia',$papel_cadeia['cadeia']);
            
            $tipo_papel = "I";
            $lista_papel = "";
            $tipo_cadeia = 'I';
            $lista_cadeia = '';
            
            $cancelar .= "parametros&cadeia=$cad64&papel=$pap64";
            $envio .= "&id=$id64&cadeia=$cad64&papel=$pap64";
        } else {
            //novo
            $cancelar .= 'index';
        }
        
        $form = new form01(['cancelar' => $cancelar, 'geraScriptValidacaoObrigatorios' => true]);     
        $form->addCampo(array('id' => '', 'campo' => "formParam[cadeia]"         , 'etiqueta' => 'Cadeia'                , 'tipo' => $tipo_cadeia 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['cadeia'] 	   , 'pasta'	=> 0, 'lista' => $lista_cadeia	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true ));
        $form->addCampo(array('id' => '', 'campo' => "formParam[papel]"          , 'etiqueta' => 'Papel'                 , 'tipo' => $tipo_papel 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['papel']        , 'pasta'	=> 0, 'lista' => $lista_papel	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true, 'onchange' => "comparaPapel('formParampapel');" ));
        $form->addCampo(array('id' => '', 'campo' => "formParam[tipo]"           , 'etiqueta' => 'Tipo'                  , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['tipo']             , 'pasta'	=> 0, 'lista' => $this->valoresSYS5('TIPOP'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true ,));
        $form->addCampo(array('id' => '', 'campo' => "formParam[titulo]"         , 'etiqueta' => 'Título'                , 'tipo' => 'T' 	, 'tamanho' => '100', 'linhas' => '', 'valor' => $dados['titulo']          , 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true ));
        $form->addCampo(array('id' => '', 'campo' => "formParam[responsavel]"    , 'etiqueta' => 'Responsável'           , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['responsavel'] 	   , 'pasta'	=> 0, 'lista' => ''	, 'opcoes'=>'S=Solicitante;E=Externo', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false,));
        $form->addCampo(array('id' => '', 'campo' => "formParam[papel_aprovador]", 'etiqueta' => 'Papel do Aprovador'    , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['papel_aprovador']  , 'pasta'	=> 0, 'lista' => $this->selectPapel($papel_param)	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'onchange' => "comparaPapel('formParampapel_aprovador');"));
        $form->addCampo(array('id' => '', 'campo' => "formParam[obrigatorio]"    , 'etiqueta' => 'Obrigatório?'          , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['obrigatorio']      , 'pasta'	=> 0, 'lista' => $this->valoresSYS5('000003')	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true ));
        $form->addCampo(array('id' => '', 'campo' => "formParam[aux]"            , 'etiqueta' => 'Dado Auxiliar'         , 'tipo' => 'T' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['aux']              , 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false ));
        
        $form->setEnvio($envio, 'formParam', 'formParam');
        
        $ret .= addCard(['titulo'=>'Novo Parâmetro', 'conteudo'=>$form]);
        return $ret;
    }
    
    private function getPapelCadeiaID($id)
    {
        $ret = ['papel'=>'','cadeia'=>''];
        $sql = "SELECT papel, cadeia FROM pmp_param_certificacao WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret['papel'] = $rows[0]['papel'];
            $ret['cadeia'] = $rows[0]['cadeia'];
        }
        return $ret;
    }
    
    private function traduzDescricao($tabela, $id)
    {
        $ret = '';
        $sql = "SELECT descricao FROM $tabela WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0]['descricao'];
        }
        return $ret;
    }
    
    private function getDadosEditar($id)
    {
        $ret = [];
        $campos = ['cadeia','papel','tipo','titulo','aux','responsavel','papel_aprovador','obrigatorio',];
        
        $sql = "SELECT * FROM pmp_param_certificacao WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = $rows[0];
            $temp['cadeia'] = base64_encode($temp['cadeia']);
            $temp['papel'] = base64_encode($temp['papel']);
            $temp['papel_aprovador'] = base64_encode($temp['papel_aprovador']);
            foreach($campos as $campo){
                $ret[$campo] = $temp[$campo];
            }
        } else {
            foreach($campos as $campo){
                $ret[$campo] = '';
            }
        }
        return $ret;
    }
    
    public function salvar()
    {
        $dados = getParam($_POST, 'formParam', []);
        if($dados != [])
        {
            $id = getParam($_GET, 'id','');
            $cadeia = getParam($_GET, 'cadeia','');
            $papel = getParam($_GET, 'papel','');
            if($id == '')
            {
                if($papel != '')
                {
                    //tem cadeia & papel
                    $dados['cadeia'] = base64_decode($cadeia);
                    $dados['papel'] = base64_decode($papel);
                } else if ($cadeia != '')
                {
                    //tem só cadeia
                    $dados['cadeia'] = base64_decode($cadeia);
                    
                    $papel = $dados['papel'];
                    $dados['papel'] = base64_decode($dados['papel']);
                } else {
                    //cadeia & papel vazio
                    $cadeia = $dados['cadeia'];
                    $papel = $dados['papel'];
                    
                    $dados['cadeia'] = base64_decode($dados['cadeia']);
                    $dados['papel'] = base64_decode($dados['papel']);
                }
                $dados['papel_aprovador'] = base64_decode($dados['papel_aprovador']);
                
                $sql = montaSQL($dados, 'pmp_param_certificacao');
                $ultimo_id_inserido = query($sql);
                if($ultimo_id_inserido !== false){
                    addPortalMensagem("Parâmetro salvo",'success');
                    gravarAtualizacao('pmp_param_certificacao', $ultimo_id_inserido, 'I');
                } else {
                    addPortalMensagem("Erro ao inserir o parâmetro",'error');
                }
            } else {
                //editar
                $id = base64_decode($id);
                $dados['papel_aprovador'] = base64_decode($dados['papel_aprovador']);
                
                $sql = montaSQL($dados, 'pmp_param_certificacao','UPDATE',"id = $id");
                query($sql);
                gravarAtualizacao('pmp_param_certificacao', $id, 'E');
                addPortalMensagem("Parâmetro atualizado",'success');
            }
            
            redireciona(getLink()."parametros&cadeia=$cadeia&papel=$papel");
        } else {
            redireciona();
        }
    }
    
    
    public function excluir()
    {
        $id = getParam($_GET, 'id','');
        if($id!='')
        {
            $id = base64_decode($id);
            $usuario = getUsuario();
            $sql = "UPDATE pmp_param_certificacao 
                    SET ativo = 'N', user_atualiza = '$usuario', data_atualiza = CURRENT_TIMESTAMP() 
                    WHERE id = $id";
            query($sql);
        }
        $cadeia = getParam($_GET, 'cadeia',0);
        $papel = getParam($_GET, 'papel',0);
        
        redireciona(getLink()."parametros&cadeia=$cadeia&papel=$papel");
    }
    
    //LISTAS 
    private function selectCadeia()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, descricao FROM pmp_cadeia WHERE ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = base64_encode($row['id']);
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function selectPapel($blacklisted = '')
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $sql = "SELECT id, descricao FROM pmp_papel WHERE ativo = 'S'";
        if($blacklisted != ''){
            $sql .= " AND id != $blacklisted ";
        }
        
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = base64_encode($row['id']);
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function valoresSYS5($tabela)
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT chave, descricao from sys005 where tabela = '$tabela' and ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['chave'];
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    static function selectTipo()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT chave, descricao from sys005 where tabela = 'TIPOP' and ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['chave'];
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    static function selectSolicitante()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT chave, descricao from sys005 where tabela = 'TIPOP' and ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['chave'];
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    //JavaScript
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
    
    
}