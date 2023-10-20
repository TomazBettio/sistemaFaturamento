<?php
/*
 * Data Criação: 13/09/2023
 * Autor: BCS
 *
 * Descricao: 	tela para visualizar requisições abertas
 * 
 * 
 * 
todas as requisicoes do usuario 
botao de editar só habilita para as abertas 'A' e se tiver o que editar (preenchíveis pelo usuário) OK
itens só podem ser alterados se são do usuário E não foram validados ?
no BD alterar de reprovado para aberto caso o usuário edite o item reprovado OK
só pode editar se status A e itens A(bertos) ou R(eprovados) OK
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class lista_requisicao{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'        => true,
        'salvar'        => true,
        'reabrir'       => true,
        'detalhes'      => true,
        
        'ajax'          => true,
    );
    
    private $_programa = 'lista_requisicao';
    private $_dir = '/var/www/pmp/anexos/';
    //FORMATO ANEXO: base/id_requisicao/id_documento/arquivo
    
    public function __construct()
    {
        //filtro: status, cadeia e papel
        if(false){
            //função dados: static
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Cadeia', 'variavel' => 'cadeia', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'lista_requisicao::getCadeiasForm()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Papel' , 'variavel' => 'papel' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'lista_requisicao::getPapeisForm()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Status', 'variavel' => 'status' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'lista_requisicao::getStatusForm()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
        }
    }
    
    public function index()
    {
        $ret = '';
        global $config;
        
        $param = [
            'programa' => $this->_programa,
            'titulo' => 'Minhas Requisições',
            'mostraFiltro' => true,
            'filtroTipo' => 1
        ];
        $tab = new tabela02($param);
        $tab->addColuna(array('campo' => 'botao'    , 'etiqueta' => ''              , 'tipo' => 'T', 'width' =>  350, 'posicao' => 'centro'));
        $tab->addColuna(array('campo' => 'cadeia'   , 'etiqueta' => 'Cadeia'        , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        $tab->addColuna(array('campo' => 'papel'    , 'etiqueta' => 'Papel'         , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        $tab->addColuna(array('campo' => 'data_ini' , 'etiqueta' => 'Data Abertura' , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
        $tab->addColuna(array('campo' => 'status'   , 'etiqueta' => 'Status'        , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        
        $p = [];
        $p['onclick'] = "setLocation('{$config['raiz']}index.php?menu=".getModulo().".requisicao.index')";
        $p['texto'] = 'Nova Requisição';
        $p['cor'] = 'success';
        $tab->addBotaoTitulo($p);
        
        $botao = [
            'texto' => 'Detalhes',
            'link' => getLink() . 'detalhes&id=',
            'coluna' => 'id',
            'width' => 50,
            'flag' => '',
            'cor' => 'default',
        ];
        $tab->addAcao($botao);
        
        
        $filtro = $tab->getFiltro();
       // if(!$tab->getPrimeira()){
            $dados = $this->getDadosTab($filtro);
            $tab->setDados($dados);
        //}
        
        $ret .= $tab;
        
        return $ret;
    }
    
    public function ajax()
    {
        $op = getOperacao();
        switch ($op)
        {
            case 'mostrarAnexo':
                $var = getParam($_GET, 'var','');
                if(!empty($var))
                {
                    $var = explode("|", base64_decode($var));
                    $req = $var[0];
                    $doc = $var[1];
                    $id = $var[2];
                    
                    $arquivo = $this->getArquivo($req, $doc,$id);
                    if(!empty($arquivo))
                    {
                        $download = getParam($_GET, 'download', '');
                        $download = '';
                        $file = $this->_dir . "$req/$doc/$arquivo";
                        return $this->mostrarArquivo($file, $arquivo, $download);
                    }
                }
            default :
                break;
        }
    }
    
    private function getArquivo($req,$doc,$id)
    {
        $ret = '';
        $sql = "SELECT valor FROM pmp_item_requisicao WHERE id = $id AND requisicao = $req AND documento = $doc AND ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = $rows[0]['valor'];
        }
        return $ret;
    }
    
    
    private function getDadosTab($filtro)
    {
        $ret = [];
        
        $status = '';
        $cadeia = '';
        $papel = '';
        
        //FILTRO:
        if(isset($filtro['status']) && $filtro['status'] != ''){
            $status = " AND status = '{$filtro['status']}' ";
        }
        if(isset($filtro['cadeia']) && $filtro['cadeia'] != ''){
            $cadeia = base64_decode($filtro['cadeia']);
            $cadeia = " AND cadeia = $cadeia ";
        }
        if(isset($filtro['papel']) && $filtro['papel'] != ''){
            $papel = base64_decode($filtro['papel']);
            $papel = " AND papel = $papel ";
        }
        
        
        $id_usuario = $this->getIdUsuarioColab();
        $sql_aux = "SELECT pmp_item_requisicao.requisicao 
                    FROM pmp_item_requisicao JOIN pmp_param_certificacao 
                    ON pmp_item_requisicao.documento = pmp_param_certificacao.id
                    WHERE pmp_param_certificacao.responsavel = 'S'";
        
        $sql = "SELECT id, cadeia, papel, status, data_ini, 1 AS editar 
                FROM pmp_requisicao 
                WHERE   ativo = 'S' 
                    AND colaborador = $id_usuario
                    $status
                    $cadeia
                    $papel
                    AND id IN ($sql_aux)

                UNION
                SELECT id, cadeia, papel, status, data_ini, 0 AS editar 
                FROM pmp_requisicao 
                WHERE   ativo = 'S' 
                    AND colaborador = $id_usuario
                    $status
                    $cadeia
                    $papel
                    AND id NOT IN ($sql_aux)
                ";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            $campos = ['id','data_ini','cadeia','papel','editar'];
            foreach($rows as $row){
                foreach($campos as $campo){
                    $temp[$campo] = $row[$campo];
                }
                $temp['cadeia'] = $this->getDescFromId($temp['cadeia'], 'pmp_cadeia');
                $temp['papel'] = $this->getDescFromId($temp['papel'], 'pmp_papel');
                //$temp['editar'] = $row['editar'] == 1;
                //$temp['reabrir'] = false;
                
                $id64 = base64_encode($temp['id']);
                
                switch($row['status'])
                {
                    case 'F':
                        $temp['status'] = 'Finalizada';
                        //$temp['editar'] = false;
                        $temp['botao'] = '';
                        break;
                    case 'R':
                        $temp['status'] = 'Rejeitada';
                        //Botão reabrir requisição
                        $param = [];
                        $param['texto'] 	= 'Reabrir Requisição';
                        $param['width'] 	= 350;
                        $param['flag'] 		= '';
                        $param['onclick'] 	= "setLocation('" . getLink()."reabrir&id=$id64 ')";
                        $param['cor'] 		= 'default';
                        $param['bloco'] 	= true;
                        $temp['botao'] = formbase01::formBotao($param);
                        //$temp['editar'] = false;
                        //$temp['reabrir'] = true;
                        break;
                    case 'A':
                        $temp['status'] = 'Em Aberto';
                        //Botão editar
                      //  if($temp['editar'] == '1')
                       // {
                            $param = [];
                            $param['texto'] 	= 'Editar';
                            $param['width'] 	= 350;
                            $param['flag'] 		= '';
                            $param['onclick'] 	= "setLocation('" . getLink()."editar&id=$id64 ')";
                            $param['cor'] 		= 'success';
                            $param['bloco'] 	= true;
                            $temp['botao'] = formbase01::formBotao($param);
                  //      } else {
                  //          $temp['botao'] = '';
                   //     }
                    default:
                        break;
                }
                
                $temp['id'] = $id64;
                $ret[] = $temp;
            }
        }

        return $ret;
    }
    
    private function getDescFromId($id, $tabela)
    {
        $ret = '';
        $sql = "SELECT descricao FROM $tabela WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0]['descricao'];
        }
        return $ret;
    }
    
    private function getIdUsuarioColab()
    {
        $ret = '';
        $user = getUsuario();
        $sql = "SELECT id FROM pmp_colaborador WHERE user = '$user' AND ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['id'];
        }
        return $ret;
    }
    
    public function detalhes()
    {
        //tabela que mostra status dos itens da requisição
        $ret = '';
        $id_req = getParam($_GET, 'id','');
        if($id_req != '')
        {
            $id_req = base64_decode($id_req);
            
            $tab = new tabela01(['titulo'=>'Minha Requisição - Detalhes']);
            $tab->addColuna(array('campo' => 'titulo'   , 'etiqueta' => 'Campo'     , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tab->addColuna(array('campo' => 'valor'    , 'etiqueta' => 'Valor'     , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tab->addColuna(array('campo' => 'status'   , 'etiqueta' => 'Status'    , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tab->addColuna(array('campo' => 'obrigatorio'   , 'etiqueta' => 'Obrigatório'    , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tab->addColuna(array('campo' => 'mensagem' , 'etiqueta' => 'Mensagem'  , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            
            $dados = $this->meusItensReq($id_req);
            $tab->setDados($dados);
            
            $botao = [];
            $botao['onclick'] = "setLocation('".getLink()."index')";
            $botao['texto'] = 'Voltar';
            $botao['cor'] = 'default';
            $tab->addBotaoTitulo($botao);
            
            $ret .= $tab;
        }
        return $ret;
    }
    
    private function meusItensReq($id)
    {
        $ret = [];
        $sql = "SELECT 
                    pmp_param_certificacao.titulo AS titulo,
                    pmp_item_requisicao.id AS id,
                    pmp_item_requisicao.status AS status,
                    pmp_item_requisicao.mensagem AS mensagem,
                    pmp_item_requisicao.valor AS valor,
                    pmp_item_requisicao.documento AS doc,
                    pmp_item_requisicao.requisicao AS requi,
                    pmp_param_certificacao.tipo AS tipo,
                    pmp_param_certificacao.obrigatorio AS obrigatorio,
                    pmp_param_certificacao.aux AS aux 
                FROM pmp_item_requisicao JOIN pmp_param_certificacao 
                        ON pmp_param_certificacao.id = pmp_item_requisicao.documento 
                WHERE pmp_param_certificacao.ativo = 'S' AND pmp_item_requisicao.ativo = 'S' 
                    AND pmp_item_requisicao.requisicao = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            $campos = ['id','titulo','valor','status','mensagem','obrigatorio'];
            foreach($rows as $row){
                foreach($campos as $camp){
                    $temp[$camp] = $row[$camp];
                }
                
                $temp['obrigatorio'] = $temp['obrigatorio'] == 'S' ? 'Sim' : 'Não';
                $temp['status'] = $temp['status'] == 'R' ? 'Rejeitado' : ($temp['status'] == 'F' ? 'Aprovado' : 'Pendente');
                
                $temp['valor'] = $this->validaValor($row['valor'], $row['tipo'], $row['aux']);
                if($row['tipo'] == 'DO' && $temp['valor'] != '')
                {
                    //Tipo Anexo: permite fazer download
                    
                    $var = base64_encode($row['requi']."|".$row['doc']."|".$row['id']);
                    $link = getLinkAjax('mostrarAnexo') . "&var=$var";
                    
                    //Extensão do arquivo
                    $n = explode('.',$temp['valor']);
                    $extensao = empty($n)? '' : $n[count($n)-1];
                    if(in_array($extensao, ['png', 'jpg', 'jpeg'])){
                        $html = '<a class="btn btn-tool" onclick="window.open(\'' . $link . '\', \'_blank\').focus();">' . $temp['valor'] . '</a>';
                    } else {
                        $link .= "&download=1";
                        $html = '<a class="btn btn-tool" href="' . $link . '" download>' . $temp['valor'] . '</a>';
                    }
                    $temp['valor'] = $html;
                }
                
                if($row['tipo'] == 'A')
                {
                    //Tipo lista precisa traduzir o valor
                    $temp['valor'] = $this->traduzValorLista($row['aux'], $row['valor']);
                    
                }
                $temp['id'] = base64_encode($temp['id']);
                
                $temp['mensagem'] = nl2br($temp['mensagem']);
                
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function traduzValorLista($lista, $valor)
    {
        $ret = '';
        
        $valores = [];
        
        $tab = explode('|', $lista);
        if(count($tab) > 2){
            $tabela = $tab[0];
            $id = $tab[1];
            $desc = $tab[2];
            $ordem = isset($tab[3]) && !empty($tab[3])? ' ORDER BY '.$tab[3] : '';
            $where = isset($tab[4]) && !empty($tab[4]) ? ' WHERE '.$tab[4] : '';
            $sql = "SELECT $id,$desc FROM $tabela $where $ordem";
            //echo "$sql <br>\n\n";
            $rows = query($sql);
            if(isset($rows[0][$desc])){
                foreach ($rows as $row){
                    if($row[0] == $valor){
                        $ret = $row[1];
                        break;
                    }
                }
            }
        } else {
            $valores = tabela($lista);
            foreach($valores as $val){
                if($val[0] == $valor){
                    $ret = $val[1];
                    break;
                }
            }
        }
        
        return $ret;
    }
    
    private function validaValor($valor,$tipo,$aux)
    {
        $ret = '';
        switch($tipo)
        {
            case 'CB':
                $ret = $valor == 'on' ? 'Sim' : 'Não';
                break;
            case 'A':
                $chave_val = explode(';',$aux);
                foreach($chave_val as $chaval){
                    $temp = explode('=',$chaval);
                    if(isset($temp[1]) && $valor == $temp[0]){
                        $ret = $temp[1];
                    }
                }
                break;
            default:
                $ret = $valor;
                break;
        }
        return $ret;
    }
    
    
    
    public function editar()
    {
        $ret = '';
        $id_requisicao = getParam($_GET,'id','');
        $readonly = getParam($_GET,'readonly','0');
        
        if($id_requisicao != '')
        {
            $req64 = $id_requisicao;
            $id_requisicao = base64_decode($id_requisicao);
            
            $botao_anexo = '';
            $param_certificacao = $this->getParamCert($id_requisicao);
            $form = new form01(['geraScriptValidacaoObrigatorios' => true]);
            foreach($param_certificacao as $par)
            {
                $doc64 = base64_encode($par['doc']);
                $item64 = base64_encode($par['id_item']);
                
                $obrigatorio = $par['obrigatorio'] == 'S' ? true : false;
                $param = [
                    'id' => '', 
                    'campo' => "formReq[{$par['id_item']}]", 
                    'etiqueta' => $par['titulo'], 
                    'tipo' => $par['tipo'], 
                    'tamanho' => '15', 
                    'linhas' => '', 
                    'valor' => $par['valor'], 
                    'pasta'	=> 0, 
                    'lista' => '', 
                    'opcoes' => '',
                    'validacao' => '', 
                    'largura' => 4, 
                    'obrigatorio' => $obrigatorio
                ];
                //aux: switch baseado no tipo
                //Tipo A: opcoes
                //Tipo T: tamanho
                //Outros: --
                switch($par['tipo'])
                {
                    case 'A':
                        $param['tabela_itens'] = $par['aux'];
                        break;
                    case 'T':
                        $param['tamanho'] = $par['aux'];
                        break;
                    case 'DO':
                        //tipo Anexo: DO
                        $param['tipo'] = 'F';
                        $param['texto'] = "Anexe aqui: {$par['titulo']}";
                        $param['estilo'] = 'opacity:0';
                        
                        //BOTÃO de baixar anexo anterior
                        if($par['valor'] != '')
                        {
                            $var = base64_encode($id_requisicao."|".$par['doc']."|".$par['id_item']);
                            $link = getLinkAjax('mostrarAnexo') . "&var=$var";
                            
                            //$link = getLinkAjax('mostrarAnexo') . "&req=$req64&doc=$doc64&item=$item64";
                            $param_botao = [
                                'onclick'   => "window.open('$link', '_blank').focus();",
                                'texto'     => "Baixar Anexo {$par['titulo']}",
                                'cor'       => 'default',
                                ];
                            $botao_anexo .= formbase01::formBotao($param_botao);
                        }
                        break;
                    case 'V':
                    default:
                        break;
                }
                
                if($readonly == 1 || $par['status'] == 'F'){
                    $param['tipo'] = 'I';
                    $param['valor'] = $param['opcoes'] != '' ? $this->traduzItem($par['aux'], $par['valor']) : $param['valor'];
                    $param['opcoes'] = '';
                }
                    
                $form->addCampo($param);
               // $form .= $botao_anexo;
            }
            $form->setEnvio(getLink() . "salvar&id=$req64", 'formReq', 'formReq');
            
            $ret .= addCard(['titulo'=>'Minha Requisição', 'conteudo'=>''.$form . $botao_anexo]);
        }
        
        return $ret;
    }
    
    private function traduzItem($aux, $valor)
    {
        $ret = '';
        $lista = explode(';', $aux);
        foreach($lista as $lis){
            $temp = explode('=',$lis);
            if($temp[0] == $valor){
                $ret = $temp[1];
            }
        }
        return $ret;
    }
    
    private function getParamCert($requisicao)
    {
        $ret = [];
        $sql = "SELECT 
                        pmp_param_certificacao.id AS id,
                        pmp_param_certificacao.id AS doc,
                        pmp_param_certificacao.tipo AS tipo,
                        pmp_param_certificacao.titulo AS titulo,
                        pmp_param_certificacao.aux AS aux,
                        pmp_param_certificacao.responsavel AS responsavel,
                        pmp_param_certificacao.papel_aprovador AS papel_aprovador,
                        pmp_param_certificacao.obrigatorio AS obrigatorio,
                        pmp_item_requisicao.valor AS valor,
                        pmp_item_requisicao.status AS status,
                        pmp_item_requisicao.id AS id_item
                FROM pmp_param_certificacao JOIN pmp_item_requisicao 
                ON pmp_param_certificacao.id = pmp_item_requisicao.documento 
                WHERE pmp_item_requisicao.requisicao = $requisicao 
                AND pmp_param_certificacao.responsavel = 'S'
                AND pmp_param_certificacao.ativo = 'S' AND pmp_item_requisicao.ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            $campos = ['id','doc','id_item','tipo','titulo','aux','responsavel','papel_aprovador','obrigatorio', 'valor','status'];
            foreach($rows as $row){
                foreach($campos as $campo){
                    $temp[$campo] = $row[$campo];
                }
                $ret[] = $temp;
            }
        }
        return $ret;
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
            header('Content-Disposition: inline; filename='.basename($file));
            
            echo file_get_contents($file);
        }
        die();
    }
    
    private function getDadosItensSalvar($requisicao)
    {
        $ret = [];
        //$campos = ['tipo', 'doc', 'item'];
        $sql = "SELECT  pmp_param_certificacao.tipo AS tipo,
                        pmp_item_requisicao.documento AS doc,
                        pmp_item_requisicao.id AS item
                FROM pmp_item_requisicao JOIN pmp_param_certificacao 
                ON pmp_item_requisicao.documento = pmp_param_certificacao.id 
                WHERE pmp_item_requisicao.ativo = 'S' AND pmp_param_certificacao.ativo = 'S' 
                    AND pmp_item_requisicao.requisicao = $requisicao";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $ret[$row['item']] = [
                    'tipo' => $row['tipo'],
                    'doc'  => $row['doc']
                ];
            }
        }

        return $ret;
    }
    
    public function salvar()
    {
        //salvar: pmp_item_requisição atualizar valor em id_requisicao e id_documento
        $id_req = getParam($_GET, 'id','');
        $dados = getParam($_POST, 'formReq');
        $error = [];
        if($id_req != '')
        {
            $id_req = base64_decode($id_req);
            if($this->requisicaoAberta($id_req))
            {
                $itens_salvar = $this->getDadosItensSalvar($id_req);
                
                foreach($dados as $id_item=>$valor)
                {
                    $id_doc = $itens_salvar[$id_item]['doc'];
                    $tipo_item = $itens_salvar[$id_item]['tipo'];
                    if($tipo_item == 'V'){
                        $valor = str_replace(['.',','], ['','.'], $valor);
                    }
                    
                    $sql = "UPDATE pmp_item_requisicao SET valor = '$valor', status='A' WHERE id = $id_item AND requisicao = $id_req AND documento = $id_doc AND ativo = 'S'";
                    if(query($sql) !== false){
                        gravarAtualizacao('pmp_item_requisicao', $id_item, 'E');
                    } else {
                        $error[] = $id_doc;
                    }
                }
                
                if(isset($_FILES["formReq"]) && isset($_FILES["formReq"]['tmp_name']))
                {
                    addPortalMensagem("Salvando anexos",'success');
                    
                    foreach($_FILES["formReq"]['tmp_name'] as $id_item=>$valor)
                    {
                        //FORMATO ANEXO: base/id_requisicao/id_documento/arquivo
                        $id_doc = $itens_salvar[$id_item]['doc'];
                        //$id_doc = trim($val_id,"val");
                        $dir = $this->_dir . "$id_req";
                        if(!is_dir($dir)){
                            mkdir($dir);
                        }
                        $dir = $dir . "/$id_doc";
                        if(!is_dir($dir)){
                            mkdir($dir);
                        }
                        
                        $origem = $valor;
                        $arquivo_novo = $_FILES['formReq']['name'][$id_item];
                        $destino = $dir . "/$arquivo_novo";
                        
                        if(file_exists($destino))
                        {
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
                        if(move_uploaded_file($origem, $destino))
                        {
                            $sql = "UPDATE pmp_item_requisicao SET valor = '$arquivo_novo' WHERE requisicao = $id_req AND documento = $id_doc AND ativo = 'S'";
                            //echo $sql;die();
                            if(query($sql) !== false){
                                gravarAtualizacao('pmp_item_requisicao', $id_doc, 'E');
                            } else {
                                $error[] = $id_doc;
                            }
                        }
                    }
                }
                
                
                if($error == []){
                    addPortalMensagem("Informações salvas com sucesso",'success');
                } else if(count($error) == count($dados)){
                    addPortalMensagem("Erro ao salvar os valores",'error');
                } else if(count($error) < count($dados)){
                    $titulos = '';
                    foreach($error as $err)
                    {
                        $sql = "SELECT titulo FROM pmp_param_certificacao WHERE id = $err";
                        $rows = query($sql);
                        if(is_array($rows) && count($rows)>0){
                            $titulos .= " {$rows[0]['titulo']},";
                        }
                    }
                    addPortalMensagem("Alguns valores ($titulos) não foram salvos corretamente",'warning');
                }
            } else {
                addPortalMensagem("Requisição fechada! Não foi possível atualizar os valores",'error');
            }
        }
        redireciona();
    }
    
    private function requisicaoAberta($id_item)
    {
        $ret = false;
        $sql = "SELECT status FROM pmp_requisicao WHERE id = $id_item";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = $rows[0]['status'] == 'A' ? true : false;
        }
        return $ret;
    }
    
    public function reabrir()
    {
        //reabrir requisição
        //abre uma nova requisição com aquele papel/cadeia
        $id = getParam($_GET, 'id', '');
        if($id != '')
        {
            $id = base64_decode($id);
            $usuario = getUsuario();
            $sql = "UPDATE pmp_requisicao SET ativo = 'N', user_atualiza = '$usuario', data_atualiza = CURRENT_TIMESTAMP()
                WHERE id = $id";
            query($sql);
            
            $sql = "UPDATE pmp_item_requisicao SET ativo = 'N', user_atualiza = '$usuario', data_atualiza = CURRENT_TIMESTAMP()
                WHERE requisicao = $id";
            query($sql);
            
            $dados_req = $this->dadosReq($id);
            $cadeia = $dados_req['cadeia'];
            $papel = $dados_req['papel'];
            $usuario = $dados_req['colaborador'];
            
            $parametros = $this->getParamCertReabertura($cadeia,$papel);
            if($parametros != [])
            {
                $data = date('Ymd');
                $sql = "INSERT INTO pmp_requisicao (colaborador,cadeia,papel,data_ini,status)
                VALUES ($usuario,$cadeia,$papel,'$data','A')";
                $requisicao = query($sql);
                if($requisicao !== false){
                    gravarAtualizacao('pmp_requisicao', $requisicao, 'I');
                    foreach($parametros as $param){
                        $documento = $param['id'];
                        $sql = "INSERT INTO pmp_item_requisicao (requisicao,documento,valor,status,mensagem)
                                        VALUES ($requisicao,$documento,'','A','')";
                        $id_param = query($sql);
                        if($id_param !== false){
                            gravarAtualizacao('pmp_item_requisicao', $id_param, 'I');
                        }
                    }
                    addPortalMensagem('Requisição aberta com sucesso','success');
                } else {
                    addPortalMensagem('Erro ao salvar a requisição','error');
                }
            }
        }
        
        
        
        /*
        //pegar todos os itens, salvar com os mesmos valores para os A, salvar limpo para os R
        $dados_req = $this->dadosReq($id);
        $itens_req = $this->itensReq($id);
        
        $data = date('Ymd');
        
        $sql = "INSERT INTO pmp_requisicao (colaborador,cadeia,papel,data_ini,status)
                VALUES ({$dados_req['colaborador']},{$dados_req['cadeia']},{$dados_req['papel']},'$data','A')";
        $id_novo = query($sql);
        if($id_novo !== false){
            gravarAtualizacao('pmp_requisicao', $id_novo, 'I');
            
            foreach($itens_req as $item)
            {
                $valor = $item['status'] == 'R' ? '' : $item['valor'];
                $sql = "INSERT INTO pmp_item_requisicao (requisicao,documento,valor,status,mensagem)
                                    VALUES ($id_novo,{$item['documento']},'$valor','A','{$item['mensagem']}')";
                $id_param = query($sql);
                if($id_param !== false){
                    //echo " - item $id_param criado sem problema";
                    
                    gravarAtualizacao('pmp_item_requisicao', $id_param, 'I');
                    if($item['tipo'] == 'DO')
                    {
                        //Anexo: pegar do caminho e passar para o novo
                        //FORMATO ANEXO: base/id_requisicao/id_documento/arquivo
                        
                        $origem = $this->_dir . $id . "/{$item['documento']}/$valor";
                        $destino = $this->_dir . $id_novo ;
                        mkdir($destino);
                        $destino .= "/{$item['documento']}";
                        mkdir($destino);
                        
                        $destino .= "/$valor";
                        
                        copy($origem,$destino);
                    }
                }
            }
            //die();
            
            $sql = "UPDATE pmp_requisicao SET ativo = 'N' WHERE id = $id";
            query($sql);
            
            $sql = "UPDATE pmp_item_requisicao SET ativo = 'N' WHERE requisicao = $id";
            query($sql);
        } else {
            addPortalMensagem("Erro ao Reabrir a Requisição. Por Favor Tente Novamente",'error');
        }
        */
        redireciona();
    }
    
    private function dadosReq($id)
    {
        $ret = [];
        
        $sql = "SELECT colaborador, cadeia, papel FROM pmp_requisicao WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $temp = [
                'colaborador' => $rows[0]['colaborador'],
                'cadeia' => $rows[0]['cadeia'],
                'papel' => $rows[0]['papel'],
                
            ];
            $ret = $temp;
        }
        return $ret;
    }
    
    private function getParamCertReabertura($cadeia,$papel)
    {
        $ret = [];
        $sql = "SELECT id FROM pmp_param_certificacao
                WHERE cadeia = $cadeia AND papel = $papel AND ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach($rows as $row){
                $temp['id'] = $row['id'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function itensReq($id)
    {
        $ret = [];
        $sql = "SELECT  t1.id AS id, 
                        t1.documento AS documento,  
                        t1.valor AS valor,  
                        t1.dt_modificado AS dt_modificado,  
                        t1.status AS status,  
                        t1.validador AS validador,  
                        t1.mensagem AS mensagem,  
                        t2.tipo AS tipo
                FROM pmp_item_requisicao AS t1 JOIN pmp_param_certificacao AS t2 ON t1.documento = t2.id 
                WHERE requisicao =  $id AND t1.ativo = 'S' AND t2.ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            $campos = ['id','documento', 'valor', 'dt_modificado', 'status', 'validador', 'mensagem', 'tipo'];
            foreach($rows as $row){
                foreach($campos as $cam){
                    $temp[$cam] = $row[$cam];
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    static function getCadeiasForm()
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
    
    static function getPapeisForm()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, descricao FROM pmp_papel WHERE ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = base64_encode($row['id']);
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    static function getStatusForm()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $temp = [];
        $temp[0] = 'A';
        $temp[1] = 'Em Aberto';
        $ret[]=$temp;
        
        $temp = [];
        $temp[0] = 'F';
        $temp[1] = 'Finalizada';
        $ret[]=$temp;
        
        $temp = [];
        $temp[0] = 'R';
        $temp[1] = 'Rejeitada';
        $ret[]=$temp;
        
        return $ret;
    }
    
}