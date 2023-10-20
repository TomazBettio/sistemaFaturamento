<?php
/*
 * Data Criacao: 11/09/2023
 * 
 * Autor: Gilson Britto
 *
 * Descricao: Sistema de contratos do PMP
 *
 * Alterações:
 *
 */

 if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


 ini_set('display_errors', 1);
 
 ini_set('display_startup_erros', 1);
 
 error_reporting(E_ALL);


 class validador_pendente {

    var $funcoes_publicas = array(
        'index'         => true,
        'detalhes'         => true,
        'incluir'         => true,
        'salvar'         => true,
    );
    var $_programa = 'validador_pendente';

    public function __construct(){
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Colaborador'         , 'variavel' => 'COLAB' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getColab();'    , 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cadeia'              , 'variavel' => 'CADEIA', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getCadeia();'   , 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Papel'               , 'variavel' => 'PAPEL' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'getPapel();'    , 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
    }

    public function index(){
        $ret = '';
        $tabela = new tabela02(['programa' => $this->_programa, 'mostraFiltro' => true, 'filtroTipo' => 2, 'titulo'=> 'Requisições', 'botaoTitulo' => array()]);
        //$tabela->addColuna(array('campo' => 'id'         , 'etiqueta' => 'id'       , 'tipo' => 'T', 'width' => '120'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'colaborador'         , 'etiqueta' => 'Colaborador'       , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'cadeia'   , 'etiqueta' => 'Cadeia'           , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'papel'        , 'etiqueta' => 'Papel'                , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $filtro = $tabela->getFiltro();
        $dados = $this->getDados($filtro);
        $tabela->setDados($dados);

        $botao = array(

          'texto' => 'Detalhes',
            'link' => getLink() . 'detalhes&id=',
            'coluna' => 'id',
            'width' => 50,
            'flag' => '',
            'cor' => 'success',
        );
        $tabela->addAcao($botao);

        $ret .= $tabela;

        return $ret;

    }

    public function detalhes(){
        $id = getParam($_GET, 'id','');
        $ret = '';
        if(!empty($id))
        {
            $param['titulo'] = 'Incluir Validador';
            $tabela = new relatorio01($param);
            $tabela->addColuna(array('campo' => 'titulo'    , 'etiqueta' => 'Campo'     , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
            $tabela->addColuna(array('campo' => 'validador' , 'etiqueta' => 'Validador' , 'tipo' => 'T', 'width' => '180'  , 'posicao' => 'C'));
            
            $p = array();
            $p['id'] = 'formValidador';
            $p['acao'] = getLink(). 'salvar&id=' . $id;
            $p['sendFooter'] = true;
            $tabela->setFormTabela($p);
            $dados = $this->getDadosItem(base64_decode($id));
            $tabela->setDados($dados);
    
    
            $ret .= $tabela;
        }
        return $ret;
    }

    public function salvar(){
       $dados = $_POST['formValidador'] ?? [];
       $id_requisicao = getParam($_GET, 'id', '');
       $id_requisicao = base64_decode($id_requisicao);
       
       if(!empty($id_requisicao) && is_array($dados) && count($dados) > 0){
            foreach($dados as $id => $validador){
            
                $sql = "UPDATE pmp_item_requisicao SET validador = '$validador' WHERE id = '$id' AND requisicao = '$id_requisicao'";
                query($sql);
                gravarAtualizacao('pmp_item_requisicao', $id, 'E');
            }
       }
       redireciona(getLink(). 'index');
    }





    private function getDados($filtro){
        $ret = array();
        //$sql = "SELECT * FROM pmp_item_requisicao WHERE validador IS NULL";
        $sql = "SELECT * FROM pmp_requisicao WHERE id IN (SELECT DISTINCT requisicao FROM pmp_item_requisicao WHERE validador IS NULL AND ativo = 'S') AND ativo = 'S'";
        $colab = $filtro['COLAB'];
        $cadeia_filtro = $filtro['CADEIA'];
        $papel_filtro = $filtro['PAPEL'];

        if($cadeia_filtro != ''){
            $sql.= " AND cadeia = '$cadeia_filtro'";

        }
        if($colab!=''){
            $sql .= "AND colaborador = '$colab'";
        }
        if($papel_filtro != ''){
            $sql .= " AND papel = '$papel_filtro'";
        }
        $rows = query($sql);
       

        if(is_array($rows) && count($rows) > 0){
            $campos = array(
                'id',
                'colaborador',
                'cadeia',
                'papel',
            );
            foreach($rows as $row){
                $temp = array();
                foreach($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $papel = $this->traduzirPapel($row['papel']);
                $cadeia = $this->traduzirCadeia($row['cadeia']);
                $colaborador = $this->traduzirColaborador($row['colaborador']);

                $temp['papel'] = $papel;
                $temp['cadeia'] = $cadeia;
                $temp['colaborador'] = $colaborador;
                $temp['id'] = base64_encode($temp['id']);
                
              
                $ret[] = $temp;
            }
        }
        return $ret;
    }

    private function getDadosItem($id){
        $ret = array();
        
        $sql = "SELECT colaborador FROM pmp_requisicao WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0)
        {
            $criador_requisicao = $rows[0]['colaborador'];
            
            $sql = "SELECT * FROM pmp_item_requisicao WHERE requisicao = '$id' AND validador IS NULL AND ativo = 'S'";
            $rows = query($sql);
            
            if(is_array($rows) && count($rows) > 0){
                $campos = array(
                    'documento',
                );
                
                foreach($rows as $row){
                    $temp = array();
                    foreach($campos as $c){
                        $temp[$c] = $row[$c];
                    }
                    
                    
                    $sql = "SELECT cadeia, papel_aprovador, titulo FROM pmp_param_certificacao WHERE id = '" . $row['documento'] . "'";
                    $dados_param = query($sql);
                    if(is_array($dados_param) && count($dados_param) > 0){
                        foreach($dados_param as $dado){
                            $cadeia = $dado['cadeia'];
                            $papel = $dado['papel_aprovador'];
                            $titulo = $dado['titulo'];
                        }
                        $sql = "SELECT pmp_colaborador.id, sys001.nome FROM pmp_colaborador JOIN sys001 USING (user) WHERE pmp_colaborador.id != $criador_requisicao AND pmp_colaborador.id IN (SELECT colaborador FROM pmp_colab_papel WHERE papel = '$papel' AND cadeia = '$cadeia' AND ativo = 'S')";
                        $dados = query($sql);
                        if(is_array($dados_param) && count($dados_param) > 0){
                            
                            $lista =  $this->getListaColab($dados);
                            
                        }
                        
                    }
                    $temp['titulo'] = $titulo;
                    $temp['validador'] = formbase01::formSelect(['nome' => 'formValidador[' . $row['id'].']', 'lista' => $lista]);
                    $ret[] = $temp;
                }
            }
        }
        
        return $ret;
    }
    

    private function getListaColab($dados){
        $ret = array();
      
        foreach($dados as $dado){
            $temp = array(
                    
                    $dado['id'],
                    $dado['nome']
            );
          $ret[] = $temp;  
        }

        

        return $ret;

    }

    private function traduzirPapel($id_papel){
        $ret = array();
        $sql = "SELECT descricao FROM pmp_papel WHERE id = '$id_papel'";
        $papel = query($sql);
        if(is_array($papel) && count($papel) > 0){
            $ret = $papel[0]['descricao'];
        }
        return $ret;
    }

    private function traduzirCadeia($id_cadeia){
        $ret = array();
        $sql = "SELECT descricao FROM pmp_cadeia WHERE id = '$id_cadeia'";
        $cadeia = query($sql);
        if(is_array($cadeia) && count($cadeia) > 0){
            $ret = $cadeia[0]['descricao'];
        }
        return $ret;
    }


    private function traduzirColaborador($id_colab){
        $ret = array();
        $sql = "SELECT sys001.nome FROM pmp_colaborador JOIN sys001 USING (user) WHERE pmp_colaborador.id = '$id_colab'";
        $colaborador = query($sql);
        if(is_array($colaborador) && count($colaborador) > 0){
            $ret = $colaborador[0]['nome'];
        }
        return $ret;
    }


  
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
 
 
 