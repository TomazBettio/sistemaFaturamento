<?php


/*
 * Data Criacao: 05/10/2023
 * 
 * Autor: Gilson Britto
 *
 * Descricao: Tela para avaliar fotos enviadas
 *
 *
 *Alterações:
 *      - Acréscimo de Filtro | Alex Cesar
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


ini_set('display_errors', 1);

ini_set('display_startup_erros', 1);

error_reporting(E_ALL);



class avaliar_foto{

    var $funcoes_publicas = array(
        'index'         => true,
        'avaliar'       => true,
        'salvar'        => true,
        'ajax'          => true,
    );
    
    private $_dir = "/var/www/app/fotos/";
    private $_programa = 'avaliar_foto';
    
    public function __construct()
    {
        if(true)
        {
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Usuário' , 'variavel' => 'usuario', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'avaliar_foto::getUsers()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Campanha', 'variavel' => 'campanha' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'avaliar_foto::getCampanhas()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Data da Foto'   , 'variavel' => 'data' , 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Já Avaliada'    , 'variavel' => 'validado' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '=;A=Não Avaliada;S=Aprovada;N=Reprovada' ]);
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
        $tabela->addColuna(array('campo' => 'botao'          , 'etiqueta' => ''            , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'usuario'          , 'etiqueta' => 'Usuário'            , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'campanha'         , 'etiqueta' => 'Campanha'           , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'anexo'            , 'etiqueta' => 'Anexo'              , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'data'             , 'etiqueta' => 'Data da foto'       , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'local'            , 'etiqueta' => 'Local'              , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        //$tabela->addColuna(array('campo' => 'data_inc'         , 'etiqueta' => 'Data de inclusão'   , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'validado'         , 'etiqueta' => 'Validado'           , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'validador'        , 'etiqueta' => 'Validador'          , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'data_validacao'   , 'etiqueta' => 'Data da validação'  , 'tipo' => 'D', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'motivo'           , 'etiqueta' => 'Motivo'             , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        
        $filtro = $tabela->getFiltro();
        
        $dados = array();
        $dados = $this->getDadosFotos($filtro);
        $tabela->setDados($dados);

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

    public function avaliar(){
        $ret = "";
        $id = getParam($_GET, 'id', '');
        $id_anexo = $this->getAnexo($id);
        $file = $this->_dir . "{$id_anexo['id']}/{$id_anexo['anexo']}";
        $form = new form01();

        $sim_nao = array();
        $sim_nao[0][0] = 'N';
        $sim_nao[0][1] = 'Não';
        $sim_nao[1][0] = 'S';
        $sim_nao[1][1] = 'Sim';

        $form->addCampo(array('campo' => 'validado', 'tipo' => 'A', 'valor' => '', 'etiqueta' => 'Foto aprovada?', 'largura' => 6, 'lista' => $sim_nao));
        $form->addCampo(array('campo' => 'motivo', 'tipo' => 'TA', 'valor' => '', 'etiqueta' => 'Motivo da decisão', 'largura' => 6, 'linhasTA' => 6));
        $form->addHidden('id', $id, 'id');
        $form->setEnvio(getLink().'salvar', 'avaliacao', 'avaliacao');


        $imageData = base64_encode(file_get_contents($file));
        $conteudo = '<img src="data:image/jpeg;base64,'.$imageData.'">';

        $ret = addCard(['conteudo' => $form.$conteudo.'', 'titulo' => 'Avaliação']);
        return $ret;
    }

   public function salvar(){
    $dados = $_POST;
    $dados['validador'] = getUsuario();
    $dados['data_validacao'] = date('Ymd');
    $sql = montaSQL($dados, 'road_fotos', 'UPDATE', "id = '". $dados['id']."'");
    query($sql);
    redireciona(getLink().'index');
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

   private function getDadosFotos($filtro){
        $ret = array();
        $sql = "SELECT 
                    road_fotos.id as id,
                    road_usuarios.nome as usuario,
                    road_campanhas.nome_campanha as campanha,
                    road_fotos.data as data,
                    road_fotos.data_inc as data_inc,
                    road_fotos.local as local,
                    road_fotos.validado as validado,
                    road_fotos.validador as validador,
                    road_fotos.data_validacao as data_validacao,
                    road_fotos.motivo as motivo,
                    road_fotos.anexo as anexo
                FROM road_fotos join road_campanhas on road_fotos.campanha = road_campanhas.id join road_usuarios on road_fotos.usuario = road_usuarios.id
                WHERE 1=1 
        ";
        if(isset($filtro['usuario']) && $filtro['usuario'] != ''){
            $sql .= " AND road_fotos.usuario = {$filtro['usuario']}";
        }
        if(isset($filtro['campanha']) && $filtro['campanha'] != ''){
            $sql .= " AND road_fotos.campanha = {$filtro['campanha']}";
        }
        if(isset($filtro['validado']) && $filtro['validado'] != ''){
            $sql .= " AND road_fotos.validado = '{$filtro['validado']}'";
        }
        if(isset($filtro['data']) && $filtro['data'] != ''){
            $sql .= " AND road_fotos.data = '{$filtro['data']}'";
        }
        $rows = query($sql);
        $temp = array();
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row) {
                $campos = array(
                    'id',
                    'usuario',
                    'campanha',
                    'data',
                    'data_inc',
                    'validado',
                    'validador',
                    'data_validacao',
                    'motivo',
                    'local',
                    'anexo'
                );
                foreach($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $temp['validado'] = $temp['validado'] == 'N' ? 'Rejeitado' : ($temp['validado'] == 'S' ? 'Aprovado' : 'Não Validado');
                if($row['validado'] == 'A'){
                    //Faz botão
                    //Botão reabrir requisição
                    $param = [];
                    $param['texto'] 	= 'Avaliar';
                    $param['width'] 	= 10;
                    $param['flag'] 		= '';
                    $param['onclick'] 	= "setLocation('" . getLink()."avaliar&id={$row['id']} ')";
                    $param['cor'] 		= 'success';
                    $param['bloco'] 	= true;
                    $temp['botao'] = formbase01::formBotao($param);
                } else {
                    $temp['botao'] = '';
                }
                $link = getLinkAjax('mostrarAnexo') . "&id={$row['id']}";
                //$html = '<a class="btn btn-tool" href="' . $link . '" download>' . $temp['anexo'] . '</a>';
                $html = '<a class="btn btn-tool" onclick="window.open(\'' . $link . '\', \'_blank\').focus();">' . $temp['anexo'] . '</a>';
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
}