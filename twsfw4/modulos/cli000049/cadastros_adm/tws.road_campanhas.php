<?php

/*
 * Data Criacao: 11/09/2023
 * 
 * Autor: Gilson Britto
 *
 * Descricao: cad das campanhas do Road.APP
 *
 *
 *TODO:
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


ini_set('display_errors', 1);

ini_set('display_startup_erros', 1);

error_reporting(E_ALL);



class road_campanhas extends cad01{


    var $_dir = '/var/www/app/fotos/campanhas/';

    public function __construct()
    {
        
        $this->funcoes_publicas['usuarios'] = true;
        $this->funcoes_publicas['finalizadas'] = true;
        $param = array();
		$param["botoesExtras"] = array(
    				array(
    				'texto'  => 'Participantes',
    				'link'   => getLink() . 'usuarios&id_campanha=',
    				'coluna' => 'id64',
          			'width'  => 30,
          			'flag'   => '',
          			'cor'    => '',
    				'posicao' => 'inicio',
    				)
				);
		$param["botoesCard"] = array(
		    array(
		        'texto'  => 'Campanhas Finalizadas',
		        'link'   => getLink() . 'finalizadas',
		        'width'  => 30,
		        'flag'   => '',
		        'cor'    => '',
		        'onclick'=> "setLocation('" . getLink() . "finalizadas')"
		        
		    )
		);
        parent::__construct('road_campanhas', $param);
    }

    public function editar($id = ''){
       $edit = parent::editar($id); 
       $id = base64_decode(getParam($_GET, 'id', 0));
       $dados = parent::getEntrada($id, false);
    
       $dir = $this->_dir.$id. '/' . $dados['img_descricao'];

       $imageData = base64_encode(file_get_contents($dir));
       $conteudo = '<img src="data:image/jpeg;base64,'.$imageData.'">
                    <h4>Imagem atual da campanha</h4>';

       //$ret = addCard(['conteudo' => .'', 'titulo' => 'editar Campanha']);
        $ret =  $edit.$conteudo. '';
       return $ret;
    }

    public function usuarios(){
            $ret = '';
            $id_campanha = base64_decode(getParam($_GET, 'id_campanha')) ;
     
            $dados = $this->getUsuariosCampanha($id_campanha);
            $tabela = new tabela01();
            $tabela->addColuna(array('campo' => 'nome'      , 'etiqueta' => 'Participante'      , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'E'));
            $tabela->addColuna(array('campo' => 'email'     , 'etiqueta' => 'Email'             , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'E'));
            $tabela->addColuna(array('campo' => 'dt_inicio' , 'etiqueta' => 'Data de inscrição' , 'tipo' => 'D', 'width' => '200'  , 'posicao' => 'E'));
            $tabela->addColuna(array('campo' => 'dt_fim'    , 'etiqueta' => 'Data de término'   , 'tipo' => 'D', 'width' => '200'  , 'posicao' => 'E'));
            //$tabela->addColuna(array('campo' => 'bloqueado'          , 'etiqueta' => 'Bloqueado'            , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'C'));
            $tabela->addColuna(array('campo' => 'ativo'     , 'etiqueta' => 'Ativo'             , 'tipo' => 'T', 'width' => '200'  , 'posicao' => 'E'));
            $tabela->setDados($dados);
            
            $param = [];
            $param['icone'] = 'fa-edit';
            $param['titulo'] = 'Participantes';
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

  public function salvar($id = 0, $dados = array(), $acao = '', $redireciona = true){
    $id = $id !== 0 ? $id : getParam($_GET, 'id', 0);
    $acao = $acao != '' ? $acao : getParam($_GET, 'acao', 'D');
    parent::salvar($id, $dados, $acao, false);


    //salva o arquivo da campanha no diretorio escolhido
    if($acao == 'I'){
        if(isset($_FILES["formCRUD"]) && isset($_FILES["formCRUD"]['tmp_name']))
        {
            $id = $this->_ultimoIdIncluido;
            $dir = $this->_dir . $id;
            if(!is_dir($dir)){
                mkdir($dir);
            }
            $origem = $_FILES['formCRUD']['tmp_name']["img_descricao"];
            $destino = $dir . '/' . $_FILES['formCRUD']['name']["img_descricao"];
            $arquivo_novo = pathinfo($destino, PATHINFO_BASENAME);
            $teste_imagem = array('JPG',' PNG',' JPEG');
            $ext = pathinfo($destino, PATHINFO_EXTENSION);
            if(in_array($ext, $teste_imagem) ){

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
                if(move_uploaded_file($origem, $destino)){
                    $sql = "UPDATE road_campanhas SET img_descricao = '$arquivo_novo' WHERE id = $id";
                    query($sql);
                }
                

            } else {
                addPortalMensagem('Anexo não salvo pois não era uma imagem', 'error');
            }

          
        }
    }else if($acao == 'E'){
        if(isset($_FILES["formCRUD"]) && isset($_FILES["formCRUD"]['tmp_name'])){
            $id =  base64_decode($id);
            $dir = $this->_dir .$id;
            
            $origem = $_FILES['formCRUD']['tmp_name']["img_descricao"];
            $destino = $dir . '/' . $_FILES['formCRUD']['name']["img_descricao"];
            $arquivo_novo = pathinfo($destino, PATHINFO_BASENAME);
            $teste_imagem = array('jpg','png','jpeg');
            $ext = pathinfo($destino, PATHINFO_EXTENSION);
            if(in_array($ext, $teste_imagem) ){
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
                if(move_uploaded_file($origem, $destino)){
                    $sql = "UPDATE road_campanhas SET img_descricao = '$arquivo_novo' WHERE id = $id";
                    query($sql);
                }
            }else{
                addPortalMensagem('Anexo não editado pois não era uma imagem', 'error');
            }
           
        }
    }

    redireciona(getLink().'index');
  }
  
  public function finalizadas()
  {
      $ret = '';
      $tabela = new tabela01(['titulo' => 'Campanhas Finalizadas']);
      
      $tabela->addColuna(array('campo' => 'nome_campanha'   , 'etiqueta' => 'Campanha'                  , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'descricao'       , 'etiqueta' => 'Descrição'                 , 'tipo' => 'T', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'img_descricao'   , 'etiqueta' => 'Logo'                      , 'tipo' => 'A', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'inicio'          , 'etiqueta' => 'Data de Início'            , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'fim'             , 'etiqueta' => 'Data de Fim'               , 'tipo' => 'D', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'valor_pagar'     , 'etiqueta' => 'Valor'                     , 'tipo' => 'V', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'maximo'          , 'etiqueta' => 'Lotação'                   , 'tipo' => 'N', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'nro_ocupados'    , 'etiqueta' => 'Número de Participantes'   , 'tipo' => 'N', 'width' => '100'  , 'posicao' => 'C'));
      $tabela->addColuna(array('campo' => 'periodo_pgto'    , 'etiqueta' => 'Período para Pagamento'    , 'tipo' => 'N', 'width' => '100'  , 'posicao' => 'C'));
      
      
      return $ret;
  }
    

    /**
     * @param $id_campanha ID da campanha em que estão os usuarios
     * @return $ret Usuarios da campanha selecionada
     */
    private function getUsuariosCampanha($id_campanha){
        $ret = array();
        $sql = "SELECT participante,dt_fim, dt_inicio, ativo FROM road_campanha_usuario WHERE campanha = '$id_campanha'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            foreach($rows as $row){
                $id_usuario = $row['participante'];
                $sql = "SELECT * FROM road_usuarios WHERE id = '$id_usuario'";
                $campos = query($sql);
                if(is_array($campos) && count($campos)>0){
                    $campos[0]['dt_inicio'] = $row['dt_inicio'];
                    $campos[0]['dt_fim'] = $row['dt_fim'];
                    $campos[0]['ativo'] = $row['ativo'];
                    $ret[] = $campos[0];
                }
            }
        }
        return $ret;

    }
   

}