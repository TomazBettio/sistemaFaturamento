<?php
/*
 * Data Criação: 27/09/2023
 * Autor: Gilson Britto
 *
 * Descricao: 	Inclusão de novas regiões
 * 
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class regioes{

    var $funcoes_publicas = array(
        'index'          => true,
        'incluir'        => true,
        'salvar'         => true,
        'excluir'        => true,
        'editar'         => true,
        'salvar_edicao'  => true,
    );

    public function index(){
        $ret = '';
        $sql = "SELECT * FROM sys005 WHERE tabela = 'RG_RS'";
        $rows = query($sql);
        $dados = array();
          
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $t = array(

                    'chave' => $row['chave'],
                    'descricao' => $row['descricao'],
                    'ativo' => $row['ativo']
                );
    
                $dados[] = $t;	
            }
          
        }

        $tabela = new tabela01();
        $tabela->addColuna(array('campo' => 'chave'         , 'etiqueta' => 'Chave'       , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'descricao'         , 'etiqueta' => 'Descrição'       , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'ativo'         , 'etiqueta' => 'Ativo'       , 'tipo' => 'T', 'width' => '80'  , 'posicao' => 'C'));
        $tabela->setDados($dados);

        $botao = array(

            'texto' => 'Excluir',
              'link' => getLink() . 'excluir&chave=',
              'coluna' => 'chave',
              'width' => 70,
              'flag' => '',
              'cor' => 'danger',
          );
          $tabela->addAcao($botao);
          $botao = array(

            'texto' => 'Editar',
              'link' => getLink() . 'editar&chave=',
              'coluna' => 'chave',
              'width' => 70,
              'flag' => '',
              'cor' => 'success',
          );
          $tabela->addAcao($botao);

        $param = [];
        $param['icone'] = 'fa-edit';
        $param['titulo'] = 'Regiões ';
        $param['conteudo'] = $tabela . '';
        $param['botoesTitulo'] = array(
    
            array('texto' => 'incluir',
            'link' => getLink() . 'incluir',
            'coluna' => 'id64',
            'width' => 30,
            'flag' => '',
            'cor' => '',
            'onclick'=> "setLocation('" . getLink() . "incluir')",
    
            )
    
        );
    
        $ret .= addCard($param);
        return $ret;
        
    }

    public function incluir(){
        $ret = '';
        $form = new form01();

        $form->addCampo(array('campo' => 'chave',     'tipo' => 'T', 'valor' => '', 'tamanho' => '6', 'etiqueta' => 'Chave', 'largura' => 6));
        $form->addCampo(array('campo' => 'descricao', 'tipo' => 'T', 'valor' => '', 'tamanho' => '50', 'etiqueta' => 'Descrição', 'largura' => 6));
       
    
        $form->setEnvio(getLink().'salvar', 'regiao', 'regiao');
        
        $ret = addCard(['conteudo' => $form . '', 'titulo'=> 'Incluir nova região']);
        return $ret;
    
    }

    public function salvar(){
        $dados = $_POST;
        $dados['tabela'] = 'RG_RS';
        $sql = montaSQL($dados, 'sys005');
        query($sql);
        redireciona(getLink().'index');
    }

    public function excluir(){
        $chave = getparam($_GET, 'chave');
        $param['ativo'] = 'N';
        $sql = montaSQL($param, 'sys005', 'UPDATE', "chave = '$chave'");
        query($sql);
        redireciona(getLink().'index');
    }
    public function editar(){
        $ret = '';
        $chave = getparam($_GET, 'chave');
        $sql = "SELECT * FROM sys005 WHERE tabela = 'RG_RS' AND chave = '$chave'";
        
        $dados = query($sql);
        if(is_array($dados) && count($dados)> 0) {
      
            $form = new form01();

            $form->addCampo(array('campo' => 'descricao', 'tipo' => 'T', 'valor' => $dados[0]['descricao'], 'tamanho' => '50', 'etiqueta' => 'Descrição', 'largura' => 6));
            $form->addHidden('chave', $dados[0]['chave'], 'chave');
    
            $form->setEnvio(getLink().'salvar_edicao', 'regiao', 'regiao');
        
            $ret = addCard(['conteudo' => $form . '', 'titulo'=> 'Incluir nova região']);
        }
        return $ret;
    }
    public function salvar_edicao(){
        $dados = $_POST;
        
        $param['descricao'] = $dados['descricao'];
        $sql = montaSQL($param, 'sys005', 'UPDATE', "chave = '".$dados['chave']."' AND tabela = 'RG_RS'");
        
        query($sql);
        redireciona(getLink().'index');

    }
}