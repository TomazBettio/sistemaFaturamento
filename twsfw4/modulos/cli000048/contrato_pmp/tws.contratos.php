<?php

/*
 * Data Criacao: 11/09/2023
 * 
 * Autor: Gilson Britto
 *
 * Descricao: Sistema de contratos do PMP
 *
 * Alterações:
 *      - Alterações query, Criação da tela de visualização dos contratos + baixar arquivos - Alex Cesar
 *
 *TODO: atualização do contrato
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


ini_set('display_errors', 1);

ini_set('display_startup_erros', 1);

error_reporting(E_ALL);



class contratos{
    var $funcoes_publicas = array(
        'index'         => true,
        'contratos'     => true,
        'inserir'       => true,
        'salvar'        => true,
        'ajax'          => true,
    );
    var $_dir = '/var/www/pmp/contratos/';
    var $_programa = 'contratos';

    public function __construct(){
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Tipo de Requisição'  , 'variavel' => 'TIPO'  , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getTiposReq();' , 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Colaborador'         , 'variavel' => 'COLAB' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getColab();'    , 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cadeia'              , 'variavel' => 'CADEIA', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getCadeia();'   , 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Papel'               , 'variavel' => 'PAPEL' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getPapel();'    , 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
    }

    public function index(){
        $ret = '';
        $tabela = new tabela02(['programa' => $this->_programa, 'mostraFiltro' => true, 'filtroTipo' => 2, 'titulo'=> 'Requisições', 'botaoTitulo' => array()]);
        $tabela->addColuna(array('campo' => 'anexo'         , 'etiqueta' => 'Último Contrato'       , 'tipo' => 'T', 'width' => '120'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'colaborador'   , 'etiqueta' => 'Colaborador'           , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'cadeia'        , 'etiqueta' => 'Cadeia'                , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'papel'         , 'etiqueta' => 'Papel'                 , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'data_ini'      , 'etiqueta' => 'Data de Início'        , 'tipo' => 'D', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'data_fecha'    , 'etiqueta' => 'Data do Fechamento'    , 'tipo' => 'D', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'status'        , 'etiqueta' => 'Status'                , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));

        $filtro = $tabela->getFiltro();
        $dados = $this->getDados($filtro);
        $tabela->setDados($dados);

        $botao = array(

          'texto' => 'Novo Contrato',
            'link' => getLink() . 'inserir&id=',
            'coluna' => 'id',
            'width' => 150,
            'flag' => '',
            'cor' => 'success',
        );
        $tabela->addAcao($botao);
        //$tabela->addBotaoTitulo($botao);
        
        $param = [
            'texto' => 'Histórico',
            'link' => getLink() . 'contratos&id=',
            'coluna' => 'id',
            'width' => 150,
            'flag' => '',
            'cor' => 'warning',
        ];
        $tabela->addAcao($param);
        
        $ret .= $tabela;

        return $ret;
    }
    
    public function ajax()
    {
        $op = getOperacao();
        switch ($op)
        {
            case 'mostrarAnexo':
                $id = getParam($_GET, 'id', '');
                if($id != '')
                {
                    $id = base64_decode($id);
                    $download = getParam($_GET, 'download', '');
                    $download = '';
                    
                    $arquivo = $this->meuAnexo($id);
                    $file = $this->_dir . "$id/$arquivo";
                    return $this->mostrarArquivo($file, $download);
                }
            default :
                break;
        }
    }
    
    public function contratos()
    {
        $ret = '';
        $requisicao = getParam($_GET, 'id','');
        if($requisicao != '')
        {
            $requisicao = base64_decode($requisicao);
            $dados = $this->getContratos($requisicao);
            
            $tab = new tabela01(['titulo'=>'Histórico de Contratos', 'ordenacao' => false]);
            $tab->addColuna(['campo' => 'dt_assinado'   , 'etiqueta' => 'Assinado em'   , 'tipo' => 'D', 'width' => '30'  , 'posicao' => 'D']);
            $tab->addColuna(['campo' => 'valido_ini'    , 'etiqueta' => 'Início'        , 'tipo' => 'D', 'width' => '30'  , 'posicao' => 'D']);
            $tab->addColuna(['campo' => 'valido_fim'    , 'etiqueta' => 'Validade'      , 'tipo' => 'D', 'width' => '30'  , 'posicao' => 'D']);
            $tab->addColuna(['campo' => 'arquivo'       , 'etiqueta' => 'Arquivo'       , 'tipo' => 'T', 'width' => '50'  , 'posicao' => 'C']);
            $tab->addColuna(['campo' => 'anexo'         , 'etiqueta' => 'Anexo'         , 'tipo' => 'T', 'width' => '50'  , 'posicao' => 'D']);
            
            $tab->setDados($dados);
            
            $botao = [
                'id' => 'retorno',
                'onclick' => "setLocation('".getLink()."index')",
                'texto' => 'Voltar',
                'cor' => 'warning',
            ];
            $tab->addBotaoTitulo($botao);
            
            $ret .= $tab;   
        }
             
        return $ret;
    }

    public function inserir(){
        $ret = '';
        
        $requisicao = getParam($_GET, 'id','');
        if($requisicao != '')
        {
            $requisicao = base64_decode($requisicao);
            $form = new form01(['geraScriptValidacaoObrigatorios' => true]);
            
            //$form->addCampo(['id' => '', 'campo' => 'requisicao', 'etiqueta' => 'Requisição do Contrato', 'tipo' => 'I' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $requisicao	, 'pasta'	=> 0, 'largura' => 6, 'obrigatorio' => true]);
            $form->addHidden('formIncluir[requisicao]', $requisicao);
            $form->addCampo(['id' => '', 'campo' => 'formIncluir[dt_assinado]'  , 'etiqueta' => 'Data de Assinatura'    , 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => ''	, 'pasta'	=> 0, 'largura' => 3, 'obrigatorio' => true]);
            $form->addCampo(['id' => '', 'campo' => 'formIncluir[valido_ini]'   , 'etiqueta' => 'Início do Contrato'    , 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => ''	, 'pasta'	=> 0, 'largura' => 3, 'obrigatorio' => true]);
            $form->addCampo(['id' => '', 'campo' => 'formIncluir[valido_fim]'   , 'etiqueta' => 'Contrato Válido Até'  , 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => ''	, 'pasta'	=> 0, 'largura' => 3, 'obrigatorio' => true]);
            $form->addCampo(['id' => '', 'campo' => 'formIncluir[arquivo]'      , 'etiqueta' => 'Anexo'                 , 'tipo' => 'F' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => ''	, 'pasta'	=> 0, 'largura' => 4, 'obrigatorio' => true, 'estilo' => 'opacity:0', 'texto' => 'Anexe aqui o contrato']);
            
            $form->setEnvio(getLink().'salvar', 'formIncluir', 'formIncluir');
            
            $param = [];
            $param['icone'] = 'fa-edit';
            $param['titulo'] = 'Incluir Contrato ';
            $param['conteudo'] = $form. '';
            
            $ret = addCard($param);
            return $ret;
        }
    }
    
    private function getContratos($id_req)
    {
        $ret = [];
        $sql = "SELECT * FROM pmp_contrato WHERE ativo = 'S' AND requisicao = $id_req ORDER BY valido_fim DESC";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            $campos = ['id','dt_assinado','valido_ini','valido_fim','arquivo'];
            foreach($rows as $row){
                foreach($campos as $campo){
                    $temp[$campo] = $row[$campo];
                }
                $temp['id'] = base64_encode($temp['id']);
                //botão para anexo
                if( $temp['arquivo'] != '')
                {
                    $link = getLinkAjax('mostrarAnexo') . "&id={$temp['id']}";
                    $param = [
                        'onclick'   => "window.open('$link', '_blank').focus();",
                        'texto'     => "Baixar Anexo",
                        'cor'       => 'default',
                    ];
                    
                    $botao_anexo = formbase01::formBotao($param);
                    $temp['anexo'] = $botao_anexo;
                } else {
                    $temp['anexo'] = '';
                }
                
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function meuAnexo($id)
    {
        $arquivo = '';
        
        $sql = "SELECT arquivo FROM pmp_contrato WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $arquivo = $rows[0]['arquivo'];
        }
        return $arquivo;
    }
    
   
    function mostrarArquivo($file, $download){
        if(!empty($download)){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            header('Connection: close');
            ob_clean();
            flush();
            readfile($file);
        }
        else{
            header('Content-Type: image');
            header('Content-Length: ' . filesize($file));
            echo file_get_contents($file);
        }
        die();
    }

    private function getDados($filtro){
        $ret = [];
        
        //Filtros
        $tipo = $filtro['TIPO'];
        $colab = $filtro['COLAB'];
        $cadeia = $filtro['CADEIA'];
        $papel = $filtro['PAPEL'];
        
        $data_hoje = datas::data_hoje('AMD', '');
        $vencida = "SELECT DISTINCT requisicao 
                    FROM pmp_contrato 
                    JOIN (SELECT MAX(id) as id, requisicao FROM pmp_contrato WHERE ativo = 'S' GROUP BY requisicao) 
                    AS aux USING (id, requisicao)
                    WHERE ativo = 'S' AND valido_fim < '$data_hoje'";
        $contratos = "SELECT requisicao FROM pmp_contrato WHERE ativo = 'S'";
        $status = "
        CASE 
            WHEN
                pmp_requisicao.id IN ($vencida)
            THEN 'Vencida'
            WHEN
                pmp_requisicao.id NOT IN ($contratos)
            THEN 'Sem Contrato'
            WHEN
                pmp_requisicao.id IN ($contratos)
            THEN 'Com Contrato'
        ELSE 'Finalizado' END
        ";
        
        $sql = "SELECT  pmp_requisicao.id AS id,
                        pmp_colaborador.user AS colaborador,
                        pmp_cadeia.descricao AS cadeia,
                        pmp_papel.descricao AS papel,
                        pmp_requisicao.data_ini AS data_ini,
                        pmp_requisicao.data_fecha AS data_fecha,
                        $status AS status
                FROM (((pmp_requisicao JOIN pmp_cadeia ON pmp_cadeia.id = pmp_requisicao.cadeia) 
                        JOIN pmp_papel ON pmp_requisicao.papel = pmp_papel.id) JOIN pmp_colaborador ON pmp_colaborador.id = pmp_requisicao.colaborador)
                WHERE pmp_requisicao.ativo = 'S' AND pmp_requisicao.status = 'F' ";
        
                        
        switch($tipo)
        {
            case 'FS':
                $sql .= " AND pmp_requisicao.id NOT IN ($contratos) ";
                break;
            case 'FC':
                $sql .= " AND pmp_requisicao.id IN ($contratos) ";
                break;
            case 'V':
                $sql .= " AND pmp_requisicao.id IN ($vencida) ";
                break;
            case 'F':
            default:
                break;
        }
        
        if($cadeia != ''){
            $sql .= " AND pmp_cadeia.id = $cadeia ";
        }
        
        if($papel != ''){
            $sql .= " AND pmp_papel.id = $papel ";
        }
        
        if($colab != ''){
            $sql .= " AND pmp_colaborador.id = $colab ";
        }
        
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $temp = array(
                    'id'            => base64_encode($row['id']),
                    'colaborador'   => getUsuario('nome',$row['colaborador']),
                    'cadeia'        => $row['cadeia'],
                    'papel'         => $row['papel'],
                    'data_ini'      => $row['data_ini'],
                    'data_fecha'    => $row['data_fecha'],
                    'status'        => $row['status'],
                );
                
                $temp['anexo'] = '';
                
                $sql = "SELECT id,arquivo FROM pmp_contrato WHERE ativo = 'S' AND requisicao = {$row['id']} ORDER BY valido_fim DESC";
                $aux_rows = query($sql);
                if(is_array($aux_rows) && count($aux_rows)>0)
                {
                    //botão para anexo
                    $i = 0;
                    while($i < count($aux_rows) && $temp['anexo'] == '')
                    {
                        if( $aux_rows[$i]['arquivo'] != '')
                        {
                            $id_anexo = base64_encode($aux_rows[$i]['id']);
                            $link = getLinkAjax('mostrarAnexo') . "&id=$id_anexo";
                            $param = [
                                'onclick'   => "window.open('$link', '_blank').focus();",
                                'texto'     => "Baixar Anexo",
                                'cor'       => 'default',
                            ];
                            
                            $botao_anexo = formbase01::formBotao($param);
                            $temp['anexo'] = $botao_anexo;
                        }
                        $i++;
                    }
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function salvar(){
        //$dados = $_POST;
        $dados = getParam($_POST, 'formIncluir',[]);
        $dados['dt_assinado'] = datas::dataD2S($dados['dt_assinado']);
        $dados['valido_ini'] = datas::dataD2S($dados['valido_ini']);
        $dados['valido_fim'] = datas::dataD2S($dados['valido_fim']);
        $sql = montaSQL($dados, 'pmp_contrato');
        $id = query($sql);

        //$sql = "update pmp_contrato set user_inclui = '$usuario', data_inclui = CURRENT_TIMESTAMP() where id = '$id'"; 
       
        //query($sql);
        if($id !== false){
            $this->salvarAnexo($id);
            gravarAtualizacao('pmp_contrato', $id, 'I');
            $this->atribuirCadeiaPapel($id);
        }
       redireciona(getLink(). 'index');
    }
    
    private function salvarAnexo($id)
    {
        if(isset($_FILES["formIncluir"]) && isset($_FILES["formIncluir"]['tmp_name']))
        {
            $dir = $this->_dir . $id;
            if(!is_dir($dir)){
                mkdir($dir);
            }
            $origem = $_FILES['formIncluir']['tmp_name']["arquivo"];
            $destino = $dir . '/' . $_FILES['formIncluir']['name']["arquivo"];
            $arquivo_novo = pathinfo($destino, PATHINFO_BASENAME);
            
            if(file_exists($destino)){
                $i = 1;
                $nome_original = pathinfo($destino, PATHINFO_FILENAME);
                while(file_exists($destino)){
                    $partes = pathinfo($destino);
                    $arquivo_novo = $nome_original . "($i)." . $partes['extension'];
                    $destino = $partes['dirname'] . '/' .  $arquivo_novo;
                    $i++;
                }
            }
            
            //echo $destino;
            if(move_uploaded_file($origem, $destino)){
                $sql = "UPDATE pmp_contrato SET arquivo = '$arquivo_novo' WHERE id = $id";
                query($sql);
            }
        } else {
            addPortalMensagem("Erro ao salvar anexo", 'error');
        }
    }

    private function atribuirCadeiaPapel($id){
        $sql = "SELECT COUNT(*) FROM pmp_contrato WHERE requisicao in (SELECT requisicao FROM pmp_contrato WHERE id = '$id')";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $contador = $rows[0][0];
            if($contador == 1){
                $sql = "SELECT colaborador, cadeia, papel FROM pmp_requisicao WHERE id IN (SELECT requisicao FROM pmp_contrato WHERE id = '$id')";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    $param['colaborador'] = $rows[0]['colaborador'];
                    $param['cadeia'] = $rows[0]['cadeia'];
                    $param['papel'] = $rows[0]['papel'];
    
                    $sql = montaSQL($param, 'pmp_colab_papel');
                    //var_dump($sql);
                    $id_novo = query($sql);
                    if($id_novo !== false){
                        gravarAtualizacao('pmp_colab_papel', $id_novo, 'I');
                    }
                }
            }
        }
    }

   
}

function getTiposReq(){
    $ret = [
        ['F', 'Finalizadas'],
        ['FC', 'Finalizadas c/ Contrato'],
        ['FS', 'Finalizadas s/ Contrato'],
        ['V', 'Contrato Vencido'],
    ];


    return $ret;
}

function getColab(){
    $ret = [
        ['', ''],
    ];
    
    $sql = "SELECT id,user FROM pmp_colaborador WHERE ativo = 'S' ORDER BY user";
    $rows = query($sql);
    if(is_array($rows) && count($rows)>0){
        foreach($rows as $row){
            $ret[] = [$row['id'], getUsuario('nome',$row['user'])];
        }
    }
    return $ret;
}

function getCadeia(){
    $ret = [
        ['', ''],
    ];
    
    $sql = "SELECT id,descricao FROM pmp_cadeia WHERE ativo = 'S' ORDER BY descricao";
    $rows = query($sql);
    if(is_array($rows) && count($rows)>0){
        foreach($rows as $row){
            $ret[] = [$row['id'], $row['descricao']];
        }
    }
    return $ret;
}

function getPapel(){
    $ret = [
        ['', ''],
    ];
    
    $sql = "SELECT id,descricao FROM pmp_papel WHERE ativo = 'S' ORDER BY descricao";
    $rows = query($sql);
    if(is_array($rows) && count($rows)>0){
        foreach($rows as $row){
            $ret[] = [$row['id'], $row['descricao']];
        }
    }
    return $ret;
}