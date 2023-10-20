<?php

/*
 * Data Criacao: 06/10/2023
 * 
 * Autor: Gilson Britto
 *
 * Descricao: Telas para os pagamentos das campanhas
 *
 *
 *Alteraçoes:
 *      - Acréscimo de filtro e mudança nas tabelas | Alex Cesar
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


ini_set('display_errors', 1);

ini_set('display_startup_erros', 1);

error_reporting(E_ALL);



class road_pagamentos{

    var $funcoes_publicas = array(
        'index' => true,
        'pagamento' => true,
        'avaliar' => true,
        'salvar_avaliacao' => true,
        'salvar_pagamento' => true,
    );

    private $_programa = 'rooad_pagamentos';

    public function index(){
        $ret = '';

        $tabela = new tabela01();
        $tabela->addColuna(array('campo' => 'participante'          , 'etiqueta' => 'Usuário'                 , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'campanha'              , 'etiqueta' => 'Campanha'                     , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'dt_inscricao'           , 'etiqueta' => 'Data de Inscrição'            , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'dt_inicio'             , 'etiqueta' => 'Data de início'               , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'periodo_pgto'            , 'etiqueta' => 'Dias Entre Pagamentos'                   , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'totais'            , 'etiqueta' => 'Pagamentos Totais'                   , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
        
        $param = array(
            'texto' => 'Histórico Pgtos',
            'link' => getLink() . 'pagamento&id=',
            'coluna' =>  'id',
            'width' => 150,
            'flag' => '',
            'cor' => 'default'
        );
        $tabela->addAcao($param);

        
        $dados = $this->getDados();
        $tabela->setDados($dados);

        $ret = addCard(['conteudo' => $tabela.'', 'titulo' => 'Pagamentos']);
        return $ret;
    }
    
    public function avaliar()
    {
        $ret = "";
        $id = getParam($_GET, 'id', '');
        $id_anexo = $this->getUserAnexoId($id);
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
        $form->setEnvio(getLink().'salvar_avaliacao', 'avaliacao', 'avaliacao');
        
        
        $imageData = base64_encode(file_get_contents($file));
        $conteudo = '<img src="data:image/jpeg;base64,'.$imageData.'">';
        
        $ret = addCard(['conteudo' => $form.$conteudo.'', 'titulo' => 'Aprovação']);
        return $ret;
    }
    
    public function salvar_avaliacao()
    {
        $dados = $_POST;
        $dados['validador'] = getUsuario();
        $dados['data_validacao'] = date('Ymd');
        $sql = montaSQL($dados, 'road_fotos', 'UPDATE', "id = '". $dados['id']."'");
        query($sql);
        redireciona();
    }

    public function pagamento(){
        $ret = '';
        $id = getParam($_GET, 'id');
       
        $tabela = new tabela01();
        //$tabela->addColuna(array('campo' => 'usuario'          , 'etiqueta' => 'Usuário'            , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        //$tabela->addColuna(array('campo' => 'campanha'         , 'etiqueta' => 'Campanha'           , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'anexo'            , 'etiqueta' => 'Anexo'              , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'data'             , 'etiqueta' => 'Data da foto'       , 'tipo' => 'D', 'width' => '200'  , 'posicao' => 'C'));
        //$tabela->addColuna(array('campo' => 'data_inc'         , 'etiqueta' => 'Data de inclusão'   , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'validado'         , 'etiqueta' => 'Status'           , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'validador'        , 'etiqueta' => 'Validador'          , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
   //     $tabela->addColuna(array('campo' => 'data_validacao'   , 'etiqueta' => 'Data da validação'  , 'tipo' => 'D', 'width' => '200'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'motivo'           , 'etiqueta' => 'Motivo'             , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
  //      $tabela->addColuna(array('campo' => 'local'            , 'etiqueta' => 'Local'              , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
        $dados = $this->getDadosFotos($id);
        $tabela->setDados($dados);
        
       
       


        $param = [];
        $param['icone'] = '';
        $param['titulo'] = 'Status Dias';
        $itens_pendentes = false;
        $itens_reprovados = false;

        foreach ($dados as $dado) {
            $itens_pendentes = $itens_pendentes || $dado['validado'] == 'Aguardando';
            $itens_reprovados = $itens_reprovados || $dado['validado'] == 'Reprovado';


        }
        if(!$itens_pendentes && !$itens_reprovados){
            //criar botão de pagar
            $botao = array();
            $botao['texto'] = 'Fazer Pagamento';
            $botao['cor'] = 'success';
            $botao['tipo'] = "link";
            $botao['url'] = getLink()."salvar_pagamento&id=$id";
            $param['conteudo'] = $tabela . formbase01::formBotao($botao);
        }else if($itens_reprovados){

            $param['conteudo'] = $tabela. '';

        }else if($itens_pendentes){
            $param = array(
                'texto' => 'Avaliar',
                'link' => getLink()."avaliar&id=" ,
                'coluna' => 'id',
                'width' => 10,
                'flag' => '',
              //  'tamanho' => 'pequeno',
                'cor' => 'success'
            );
            $tabela->addAcao($param);
            $param['conteudo'] = $tabela.'';

        }

       
        $param['botoesTitulo'] = array( 

            array('texto' => 'Voltar',
            'link' => getLink() . 'index',
            'width' => 30,
            'flag' => '',
            'cor' => '',
            'onclick'=> "setLocation('" . getLink() . "index')",

            ),
       
        );
       
        $ret .= addCard($param);

        return $ret;
    }

    public function avaliar_foto(){

    }
    public function salvar_pagamento(){
        $id_campanha_usuario = getParam($_GET, 'id','');
        if($id_campanha_usuario != '')
        {
            $val = $this->getValorCampanhaUsuario($id_campanha_usuario);
            $hoje = date('Ymd');
            $sql = "INSERT INTO road_pagamentos (id_campanha_usuario,valor,dt_pagamento) VALUES ($id_campanha_usuario, '$val', '$hoje')";
            query($sql);
        }
        redireciona();
    }
    
    
    private function getEmailUsuario($id)
    {
        $ret = '';
        $sql = "SELECT email FROM road_usuarios WHERE id=$id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = $rows[0]['email'];
        }
        return $ret;
    }
    
    
    private function getUserAnexoId($id)
    {
        $ret = ['anexo'=>'','email'=>''];
        $sql = "SELECT anexo,id_usuario_campanha FROM road_fotos WHERE id=$id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1)
        {
            $ret['anexo'] = $rows[0]['anexo'];
            $ret['id'] = $rows[0]['id_usuario_campanha'];
        }
        return $ret;
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

    private function getDados(){
        $ret = array();
        $sql = "SELECT 
                road_campanha_usuario.id AS id,
                    road_usuarios.nome AS participante, 
                    road_campanhas.nome_campanha AS campanha, 
                    road_campanha_usuario.dt_inscricao AS dt_inscricao, 
                    road_campanha_usuario.dt_inicio AS dt_inicio,
                    road_campanhas.periodo_pgto AS periodo_pgto
                FROM 
                    road_campanha_usuario JOIN road_campanhas ON road_campanha_usuario.campanha = road_campanhas.id
                    JOIN road_usuarios ON road_campanha_usuario.participante = road_usuarios.id
                WHERE road_campanha_usuario.ativo = 'S' AND road_campanhas.ativo = 'S' AND road_usuarios.ativo = 'S'";
        $rows = query($sql);
        $temp = array();
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $sql = "SELECT * FROM road_pagamentos WHERE id_campanha_usuario ={$row['id']}";
                $pagamento = query($sql);
                if(is_array($pagamento)){
                    $temp['totais'] = count($pagamento);
                } else {
                    $temp['totais'] = 0;
                }
                $campos = array(
                    'id',
                    'participante',
                    'campanha',
                    'dt_inscricao',
                    'dt_inicio',
                    'periodo_pgto',
                );
                foreach($campos as $c){
                    $temp[$c] = $row[$c];
                }
               $ret[] = $temp; 
            }
        }
        return $ret;
    }

    /*
    private function getDadosPagamento($id){
        $ret = array();
        $sql = "SELECT * FROM road_pagamentos WHERE id_campanha_usuario = '$id'";
        $rows = query($sql);
        $temp = array();
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $campos = array(
                    'id',
                    'id_campanha_usuario',
                    'valor',
                    'dt_pagamento',
                );
                foreach($campos as $c){
                    $temp[$c] = $row[$c];
                }
               $ret[] = $temp; 
            }
        }
        return  $ret;

    }
    */

    private function getDadosFotos($id){
        $ret = array();
        $sql = "SELECT campanha FROM road_campanha_usuario WHERE id = $id"; 
        $row = query($sql);
        if(is_array($row) && count($row) > 0){
            $campanha = $row[0]['campanha'];
            $sql = "SELECT periodo_pgto FROM road_campanhas WHERE id = '$campanha'"; 
            $row = query($sql);
            if(is_array($row) && count($row) > 0){
                $periodo = $row[0]['periodo_pgto'];
            

                $data_periodo = date('Ymd') - $periodo;
                $data_atual = date('Ymd');
        
                $sql = "SELECT * FROM road_fotos WHERE id_usuario_campanha = '$id' AND (data BETWEEN '$data_periodo' AND '$data_atual')";
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
                        $temp['validado'] = $temp['validado'] == 'N' ? 'Reprovado' : ($temp['validado'] == 'S' ? 'Aprovado' : 'Aguardando' );
                        
                        $ret[] = $temp; 
                    }
                
                }
            }
        }
     
        return $ret;
    }

}
