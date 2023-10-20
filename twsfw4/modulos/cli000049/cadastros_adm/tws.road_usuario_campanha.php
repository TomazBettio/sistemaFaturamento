<?php

/*
 * Data Criacao: 02/10/2023
 * 
 * Autor: Gilson Britto
 *
 * Descricao: Junção dos usuarios e suas respectivas campanhas
 *
 *
 *Alterações: 
 *      - Mudança do formato das telas | Alex Cesar
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


ini_set('display_errors', 1);

ini_set('display_startup_erros', 1);

error_reporting(E_ALL);



class road_usuario_campanha{
    
    var $funcoes_publicas = array(
        'index'     => true,
        'campanhas' => true,
        'fotos'     => true,
        'pagamentos'=> true,
        'salvarPgtos' => true,
        
        'ajax'      => true,
    );
    
    private $_dir = '/var/www/app/fotos/';
    private $_programa = 'rooad_usuario_campanha';
    
    public function __construct()
    {
       if(true)
       {
           sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Usuário' , 'variavel' => 'usuario', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'road_usuario_campanha::getUsers()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
           sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Campanha', 'variavel' => 'campanha' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'road_usuario_campanha::getCampanhas()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
           sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Campanha Ativa'   , 'variavel' => 'ativo_c' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '=;S=Sim;N=Não' ]);
           sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Usuário Ativo'   , 'variavel' => 'ativo_u' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '=;S=Sim;N=Não' ]);
           sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Data'    , 'variavel' => 'data' , 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
           
       }
    }

    public function index(){
        $ret = '';
        
        $tabela = new tabela02([
            'programa' => $this->_programa,
            'titulo' => 'Contratos',
            'mostraFiltro' => true,
            'filtroTipo' => 1
        ]);
        
        $filtro = $tabela->getFiltro();
        //if(!$tabela->getPrimeira()){
        if(isset($filtro['usuario']) && $filtro['usuario'] != '')
        {
            //lista de campanhas
            $tabela->addColuna(array('campo' => 'campanha'      , 'etiqueta' => 'Campanha'          , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_inscricao'  , 'etiqueta' => 'Data Inscrição'     , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_inicio'     , 'etiqueta' => 'Data Iniciou'      , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_fim'        , 'etiqueta' => 'Data Saiu'         , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'ativo'         , 'etiqueta' => 'Ativo na Campanha' , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        } else if(isset($filtro['campanha']) && $filtro['campanha'] != '')
        {
            //lista de usuários da campanha
            $tabela->addColuna(array('campo' => 'participante'  , 'etiqueta' => 'Usuário'          , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_inscricao'  , 'etiqueta' => 'Data Inscrição'     , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_inicio'     , 'etiqueta' => 'Data Iniciou'      , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_fim'        , 'etiqueta' => 'Data Saiu'         , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'ativo'         , 'etiqueta' => 'Ativo na Campanha' , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        }
        else {
            $tabela->addColuna(array('campo' => 'campanha'      , 'etiqueta' => 'Campanha'          , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'participante'  , 'etiqueta' => 'Usuário'          , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_inscricao'  , 'etiqueta' => 'Data Inscrição'     , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_inicio'     , 'etiqueta' => 'Data Iniciou'      , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'dt_fim'        , 'etiqueta' => 'Data Saiu'         , 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro'));
            $tabela->addColuna(array('campo' => 'ativo'         , 'etiqueta' => 'Ativo na Campanha' , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        }
        $param = array(
            'texto' => 'Registros',
            'link' => getLink() . 'fotos&id=',
            'coluna' =>  'id',
            'width' => 200,
            'flag' => '',
            'cor' => 'default'
        );
        $tabela->addAcao($param);
        
        $param = array(
            'texto' => 'Pagamentos',
            'link' => getLink() . 'pagamentos&id=',
            'coluna' =>  'id',
            'width' => 200,
            'flag' => '',
            'cor' => 'success'
        );
        $tabela->addAcao($param);
        
        
        $dados = $this->getDadosTab($filtro);
        $tabela->setDados($dados);
       // }
        $ret .= $tabela;

        return $ret;
    }
    
    public function ajax()
    {
        $op = getOperacao();
        switch ($op)
        {
            case 'mostrarAnexo':
                $id_fotos = getParam($_GET, 'id', '');
                $anexo = $this->getAnexo($id_fotos);
                if($anexo['id'] != '' && $anexo['anexo'] != '')
                {
                    $file = $this->_dir . "{$anexo['id']}/{$anexo['anexo']}";
                    return $this->mostrarArquivo($file);
                }
            default :
                break;
        }
    }
    
    public function fotos(){
        $id_user_campanha = getParam($_GET,'id', 0);
        $ret = '';
        
        $tabela = new tabela01();
        $tabela->addColuna(array('campo' => 'anexo'            , 'etiqueta' => 'Foto'           , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'data'             , 'etiqueta' => 'Data da Foto'   , 'tipo' => 'D', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'local'            , 'etiqueta' => 'Local da Foto'               , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'validado'         , 'etiqueta' => 'Resultado'      , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'validador'        , 'etiqueta' => 'Validador'           , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'data_validacao'   , 'etiqueta' => 'Data da Validação'   , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'motivo'           , 'etiqueta' => 'Motivo da decisão'   , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        
        $dados = $this->getDadosFotos($id_user_campanha);
        $tabela->setDados($dados);
        
        $param = [];
        $param['titulo'] = 'Registros da Campanha';
        $param['conteudo'] = $tabela . '';
        $param['botoesTitulo'] = array(
            array('texto' => 'Voltar',
                'width' => 30,
                'cor' => 'default',
                'onclick'=> "setLocation('" . getLink() . "index')",
            )
        );
        $ret .= addCard($param);
        return $ret;
    }
    
    public function pagamentos()
    {
        $ret = '';
        $id_user_campanha = getParam($_GET, 'id','');
        if($id_user_campanha!=''){
            $tabela = new tabela01(['titulo' => 'Pagamentos']);
            $tabela->addColuna(array('campo' => 'valor'         , 'etiqueta' => 'Valor Pago'             , 'tipo' => 'V', 'width' => '100'  , 'posicao' => 'C'));
            $tabela->addColuna(array('campo' => 'dt_pagamento'  , 'etiqueta' => 'Data de Pagamento'     , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
            //$tabela->addColuna(array('campo' => 'dt_inicio'     , 'etiqueta' => 'Início da Campanha'    , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
            //$tabela->addColuna(array('campo' => 'dt_fim'        , 'etiqueta' => 'Fim da Campanha'       , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
            //$tabela->addColuna(array('campo' => 'periodo_pgto'  , 'etiqueta' => 'Dias Entre Pagamentos' , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
            
            $dados = $this->getDadosPagamentos($id_user_campanha);
            $tabela->setDados($dados);
            
            $card1 = ''.$tabela;
            
            $dados_texto = $this->dadosTexto($id_user_campanha);
            $texto = $this->boxText($dados_texto, $id_user_campanha);
            
            $card2 = addCard(['conteudo' => $texto, 'titulo' => '']);
            $ret = addLinha(['tamanhos' => [8,4],'conteudos' => [$card1,$card2]]);
        }
        return $ret;
    }
    
    public function salvarPgtos(){
        $id_campanha_usuario = getParam($_GET, 'id','');
        if($id_campanha_usuario != '')
        {
            $val = $this->getValorCampanhaUsuario($id_campanha_usuario);
            $hoje = date('Ymd');
            $sql = "INSERT INTO road_pagamentos (id_campanha_usuario,valor,dt_pagamento) VALUES ($id_campanha_usuario, '$val', '$hoje')";
            query($sql);
            redireciona(getLink()."pagamentos&id=$id_campanha_usuario");
        }
        redireciona();
    }
    
    private function getValorCampanhaUsuario($id_campanha_usuario)
    {
        $ret = 0;
        $sql = "SELECT campanha FROM road_campanha_usuario WHERE id = $id_campanha_usuario";
        $rows = query($sql);
        if(is_array($rows) && count($rows) == 1){
            $id_campanha = $rows[0]['campanha'];
            $sql = "SELECT valor_pagar FROM road_campanhas WHERE id = $id_campanha";
            $rows = query($sql);
            if(is_array($rows) && count($rows) == 1){
                $ret = $rows[0]['valor_pagar'];
            }
        }
        return $ret;
    }
    
    private function boxText($dados, $id)
    {
        $ret = '  
            <span style="font-size: 24px; margin: 21px 0; display: block;  text-align: center;">  
            ';
        
        $ret .= "<p>Período da Campanha: 
                    <br>de {$dados['inicio']} a {$dados['fim']},
                    <br> <b>{$dados['participante']}</b>
                        <br> Inscrito em: {$dados['dt_inscricao']}
                        <br> Participou ";
        if($dados['ativo'] == 'N'){
            $ret .= "de {$dados['dt_inicio']} a {$dados['dt_fim']} (Total de {$dados['dias']} dias)<br>";
        } else {
            $ret .= "desde {$dados['dt_inicio']}<br>";
        }
        $ret .= "</p></span>";        
        //botao
        $param=[];
        $param['texto'] 	= 'Fazer Pagamento';
        $param['width'] 	= 10;
        $param['flag'] 		= '';
        $param['onclick'] 	= "setLocation('" . getLink()."salvarPgtos&id=$id ')";
        $param['cor'] 		= 'success';
        $param['bloco'] 	= true;
        $param['tamanho']   = 'grande';
        $param['ativo'] = $this->habilitaPgto($id);
        $ret .= "<br>".formbase01::formBotao($param);
        //texto de habilita pgto
        if(!($this->habilitaPgto($id))){
            $ret .= "<br> As fotos do usuário não foram aprovadas. Não é possível realizar o pagamento.";
        }
        
        
        return $ret;
    }
    
    private function dadosTexto($id_user_campanha)
    {
        $ret = [];
        $campos = ['dt_inscricao', 'dt_inicio','dt_fim', 'ativo', 'inicio', 'fim', 'participante'];
        $campos_dt = ['dt_inscricao', 'dt_inicio','dt_fim', 'inicio', 'fim'];
        
        $sql = "SELECT
                    road_campanha_usuario.ativo AS ativo,
                    road_campanha_usuario.dt_inicio AS dt_inicio,
                    road_campanha_usuario.dt_fim AS dt_fim,
                    road_campanha_usuario.dt_inscricao AS dt_inscricao,
                    road_campanhas.inicio AS inicio,
                    road_campanhas.fim AS fim,
                    road_usuarios.nome AS participante
                FROM road_campanha_usuario JOIN road_campanhas on road_campanha_usuario.campanha = road_campanhas.id
                    JOIN road_usuarios on road_campanha_usuario.participante = road_usuarios.id
                WHERE road_campanha_usuario.id = $id_user_campanha
                ";
        $row = query($sql);
        if(is_array($row) && count($row)==1){
            foreach($campos as $cam){
                if(in_array($cam, $campos_dt)){
                    $ret[$cam] = datas::dataS2D($row[0][$cam]);
                } else {
                    $ret[$cam] = $row[0][$cam];
                }
                $ret['dias'] = road_usuario_campanha::getDiffDias($row[0]['dt_inicio'],$row[0]['dt_fim']);
            }
        }
        return $ret;
    }
    
    private function habilitaPgto($id_user_campanha)
    {
        $ret = false;
        $sql = "SELECT validado FROM road_fotos WHERE id_usuario_campanha = $id_user_campanha";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                if($row['validado'] != 'S'){
                    return $ret;
                }
            }
            $ret = true;
        }
        return $ret;
    }
    
    private function getDadosPagamentos($id)
    {
        $ret = [];
        $sql = "SELECT 
                    road_pagamentos.valor AS valor,
                    road_pagamentos.dt_pagamento AS dt_pagamento,
                    road_campanha_usuario.dt_inicio AS dt_inicio,
                    road_campanha_usuario.dt_fim AS dt_fim,
                    road_campanhas.periodo_pgto AS periodo_pgto
                FROM road_pagamentos 
                    JOIN road_campanha_usuario ON road_pagamentos.id_campanha_usuario = road_campanha_usuario.id 
                    JOIN road_campanhas ON road_campanha_usuario.campanha = road_campanhas.id
                WHERE road_campanha_usuario.id = $id
        ";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows;
        }
        return $ret;
    }
    
    private function getDadosTab($filtro)
    {
        $ret = [];
        $sem_filtro = 0;
        
        $sql = "SELECT 
                    road_campanha_usuario.id as id,
                    road_usuarios.nome as participante,
                    road_campanhas.nome_campanha as campanha,
                    road_campanha_usuario.dt_fim as dt_fim,
                    road_campanha_usuario.dt_inscricao as dt_inscricao,
                    road_campanha_usuario.dt_inicio as dt_inicio,
                    road_campanha_usuario.ativo as ativo
            
                FROM road_campanha_usuario join road_campanhas on road_campanha_usuario.campanha = road_campanhas.id join road_usuarios on road_campanha_usuario.participante = road_usuarios.id
                WHERE 1=1 ";
        if(isset($filtro['usuario']) && $filtro['usuario'] != ''){
            $sql .= " AND road_campanha_usuario.participante = {$filtro['usuario']}";
            $sem_filtro++;
        }
        if(isset($filtro['campanha']) && $filtro['campanha'] != ''){
            $sql .= " AND road_campanha_usuario.campanha = {$filtro['campanha']}";
            $sem_filtro++;
        }
        if(isset($filtro['ativo_u']) && $filtro['ativo_u'] != ''){
            $sql .= " AND road_campanha_usuario.ativo = '{$filtro['ativo_u']}'";
            $sem_filtro++;
        }
        if(isset($filtro['ativo_c']) && $filtro['ativo_c'] != ''){
            $sql .= " AND road_campanhas.ativo = '{$filtro['ativo_c']}'";
            $sem_filtro++;
        }
        if(isset($filtro['data']) && $filtro['data'] != ''){
            $sql .= " AND '{$filtro['data']}' >= road_campanha_usuario.dt_inscricao";
            $sem_filtro++;
        }
        if($sem_filtro == 0){
            //Sem filtro nenhum, pega só as campanhas atuais, com todos os usuários delas
            $sql .= " AND road_campanhas.ativo = 'S'";
        }
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $campos = ['id','participante','campanha','dt_inscricao','dt_inicio','dt_fim','ativo'];
            $temp = [];
            foreach($rows as $row){
                foreach($campos as $cam){
                    $temp[$cam] = $row[$cam];
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    
   
    private function getAnexo($id){
        $ret =['anexo' => '', 'id' => ''];   
        $sql = "SELECT anexo, id_usuario_campanha FROM road_fotos WHERE id = '$id'";
        $row = query($sql);
        if(is_array($row) && count($row) > 0){
            $ret['anexo'] = $row[0]['anexo'];
            $ret['id'] = $row[0]['id_usuario_campanha'];
        }
        return $ret;
    }

   private function mostrarArquivo($file){
       header('Content-Type: image');
       header('Content-Length: ' . filesize($file));
       header('Content-Disposition: inline; filename='.basename($file));
       
       echo file_get_contents($file);
    }

    private function getDadosFotos($id_user_campanha){
        $ret = array();
        $sql = "SELECT * FROM road_fotos WHERE id_usuario_campanha = $id_user_campanha";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $temp = array();
            $campos = array(
                'id',
                'usuario',
                'data',
                'data_inc',
                'validado',
                'validador',
                'data_validacao',
                'motivo',
                'local',
                'anexo'
            );
            
            foreach($rows as $row){
                foreach($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $temp['validado'] = $temp['validado'] == 'N' ? 'Rejeitado' : ($temp['validado'] == 'S' ? 'Aprovado' : 'Não Validado');
                
                $link = getLinkAjax('mostrarAnexo') . "&id={$row['id']}";
                $html = '<a class="btn btn-tool" onclick="window.open(\'' . $link . '\', \'_blank\').focus();">' . $temp['anexo'] . '</a>';
                //$html = '<a class="btn btn-tool" href="' . $link . '" download>' . $temp['anexo'] . '</a>';
                $temp['anexo'] = $html;
                
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    static function getUsers()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, nome FROM road_usuarios WHERE ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['id'];
                $temp[1] = $row['nome'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    
    static function getCampanhas()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, nome_campanha FROM road_campanhas WHERE ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['id'];
                $temp[1] = $row['nome_campanha'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    
    static function getDiffDias($data1, $data2 = ''){
        if($data2 == ''){
            $data2 = date('Ymd');
        }
        $date1 = strtotime($data1);
        $date2 = strtotime($data2);
        $subTime = $date1 - $date2;
        
        //$y = ($subTime/(60*60*24*365));
        $d = (int)floor( $subTime / (60 * 60 * 24));
        
        if($d < 1){
            $d *= -1;
        }
        
        return $d;
    }
}