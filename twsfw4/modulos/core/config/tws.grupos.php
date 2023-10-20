<?php
/*
 * Data Criacao: 04/07/2018
 * Autor: Alexandre Thiel
 * Data Atualização: 26/06/2023
 *
 * Descricao: Cadastro de Grupos
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class grupos
{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'        => true,
        'salvar'        => true,
        'excluir'       => true,
        'incluir'       => true,
    );
    
    private $_tabela_grupos = 'sys010';
    private $_tabela_usuarios = 'sys011';
    
    function __construct(){
        $temp = '
			function marcarTodos(modulo, marcado){
				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
			}';
        addPortaljavaScript($temp);
    }
    
    function jsConfirmaExclusao($titulo){
        addPortaljavaScript('function confirmaExclusao(link,id){');
        addPortaljavaScript('	if (confirm('.$titulo.')){');
        addPortaljavaScript('		setLocation(link+id);');
        addPortaljavaScript('	}');
        addPortaljavaScript('}');
    }
  
    public function index()
    {
        $ret = '';
        $tabela = new tabela01(['titulo' => 'Grupos']);
        
        $tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        
        $tabela->addBotaoTitulo([
            'id' => 'incluir',
            'onclick' => "setLocation('".getlink()."incluir')",
            'texto' => 'Novo Grupo'
        ]);
        
        $tabela->addAcao([
            'texto' =>  'Editar',
            'link' 	=> getLink()."editar&id=",
            'coluna'=> 'seq',
            'flag' 	=> '',
            'cor'   => 'success',
        ]);
        
        $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir esse grupo?"');
        
        $tabela->addAcao([
            'texto' =>  'Excluir',
           // 'link' 	=> getLink()."excluir&id=",
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&id=','{ID}')",
            'coluna'=> 'seq',
            'flag' 	=> '',
            'cor'   => 'danger',
        ]);
        
        $dados = $this->getDadosAllGrupos();
        $tabela->setDados($dados);
        
        $ret .= $tabela;
        
        return $ret;
    }
    
    private function getDadosAllGrupos()
    {
        $ret = [];
        $sql = "SELECT * FROM $this->_tabela_grupos WHERE ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows;
        }
        return $ret;
    }
    
    public function incluir()
    {
        $ret = '';
        
        $form = new form01([]);
        
        $form->setBotaoCancela();
        $form->setPastas(array('Geral', 'Participantes'));
        
        $form->addCampo(array('id' => '', 'campo' => 'formGrupo[descricao]' , 'etiqueta' => 'Nome do Grupo'         , 'tipo' => 'T' 	, 'tamanho' => '45', 'linhas' => '', 'valor' => '' 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        
        $temp = $this->montaFormUsuarios();
        $form->addConteudoPastas(1, $temp);
        
        $form->setEnvio(getLink() . 'salvar&id=0', 'formGrupo', 'formGrupo');
        
        $ret .= addCard([ 'titulo' => "Novo Grupo", 'conteudo' => $form ]);
        
        
        return $ret;
    }
   
    /*
    private function montaFormConvidados($lista_convidados=''){
        $ret = '';
        
        $type = 'Todos';
        $descricao = '';
        $checkbox = [];
        $lista_usuarios = [];
        
        $convidados = [];
        $convidados = explode(';',$lista_convidados);
        
        $sql = "SELECT user, nome, tipo FROM sys001 WHERE ativo = 'S'";
        $rows = query($sql);
        
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $temp = [];
                $temp['user'] = $row['user'];
                $temp['nome'] = $row['nome'];
                $temp['tipo'] = $row['tipo'];
                $lista_usuarios[] = $temp;
            }
        }
        
        foreach ($lista_usuarios as $user => $info){
            $temp = [];
            $temp["nome"] 		= "formUsers[".$info['user']."]";
            $temp["etiqueta"] 	= $info['nome'];
            $temp["modulo"] 	= $info['tipo'];
            $temp["classeadd"] 	= $info['tipo'];
            $temp["checked"]    = in_array($info['user'], $convidados) ? true : false;
            $checkbox[$info['tipo']][] = $temp;
        }
        
        foreach ($lista_tipos_usuarios as $tipo => $descricao){
            if(isset($checkbox[$type])){
                $param = [];
                $param['colunas'] 	= 3;
                $param['combos']	= $checkbox[$type];
                $formCombo = formbase01::formGrupoCheckBox($param);
                $param = [];
                $param['titulo'] = '<label><input type="checkbox"  onclick="marcarTodos(\''.$tipo.'\',this.checked);" ['.$tipo.']" id="' . $descricao . '_id"  />&nbsp;&nbsp;'.$descricao.'</label>';
                $param['conteudo'] = $formCombo;
                $ret .= addCard($param).'<br><br>';
            }
        }
        return $ret;
    }
    */
    
    public function editar()
    {
        $ret = '';
        $seq = getParam($_GET, 'id',0);
        
        if($seq != 0)
        {
            $dados = $this->getDadosGrupo($seq);
            $usuariosGrupo = $this->getUsersGrupo($dados['id']);
            $form = new form01([]);
        
            $form->setBotaoCancela();
            $form->setPastas(array('Geral', 'Participantes'));
            
            $form->addCampo(array('id' => '', 'campo' => 'formGrupo[descricao]' , 'etiqueta' => 'Nome do Grupo'         , 'tipo' => 'T' 	, 'tamanho' => '45', 'linhas' => '', 'valor' => $dados['descricao'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
            
            $temp = $this->montaFormUsuarios($usuariosGrupo);
   //         die();
            $form->addConteudoPastas(1, $temp);
            
            $form->setEnvio(getLink() . "salvar&id=$seq", 'formGrupo', 'formGrupo');
            
            $ret .= addCard([ 'titulo' => "Editar Grupo", 'conteudo' => $form ]);
        }
        return $ret;
    }
    
    private function getDadosGrupo($seq)
    {
        $ret = [];
        $sql = "SELECT * FROM $this->_tabela_grupos WHERE seq = $seq";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret = $rows[0];
        }
        return $ret;
    }
    
    private function getUsersGrupo($ID)
    {
        $ret = '';
        $sql = "SELECT usuario FROM $this->_tabela_usuarios WHERE id = '$ID'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $ret .= $row['usuario'] . ';';
            }
        }
        return $ret;
    }
    
    public function excluir()
    {
        $id = getParam($_GET, 'id');
        query("UPDATE $this->_tabela_grupos SET ativo = 'N' WHERE seq = $id");
        redireciona(getLink().'index');
    }
    
    public function salvar()
    {
        $seq = getParam($_GET, 'id',0);
        $dadosGrupo = getParam($_POST, 'formGrupo');
        $dadosUser = getParam($_POST, 'formUsers');
        
    //    var_dump($_POST);die();
        
        if($seq == 0)
        {
            //incluir novo
            $ID = base64_encode(random_bytes(8));
            $sql = "INSERT INTO $this->_tabela_grupos (id,descricao) VALUES ('$ID','".$dadosGrupo['descricao']."')";
            query($sql);
            foreach($dadosUser as $usuario=>$useless)
            {
                $sql = "INSERT INTO $this->_tabela_usuarios (id,usuario,tipo) VALUES ('$ID','".$usuario."','E')";
                query($sql);
            }
        } else {
            //atualizar grupo
            $sql = "UPDATE $this->_tabela_grupos SET descricao = '".$dadosGrupo['descricao']."' WHERE seq = $seq";
            query($sql);
            $this->atualizaUsuarios($seq,$dadosUser);
        }
        
       redireciona(getLink().'index');
    }
    
    private function atualizaUsuarios($seq,$dadosUser)
    {
        $rows = query("select id from $this->_tabela_grupos WHERE seq = $seq");
        if(is_array($rows) && count($rows)==1){
            $ID = $rows[0]['id'];
            query("DELETE FROM $this->_tabela_usuarios WHERE id = '$ID'");
            foreach($dadosUser as $usuario=>$useless)
            {
                $sql = "INSERT INTO $this->_tabela_usuarios (id,usuario,tipo) VALUES ('$ID','".$usuario."','E')";
                query($sql);
            }
        }
    }
    
    private function montaFormUsuarios($lista_convidados=''){
        //separar por tipo de usuário => usar tabela 000015
        //aquele monte de CB com os nome
        $ret = '';
        $lista_tipos_usuarios = $this->getListaTiposUsuarios();
        //print_r($lista_tipos_usuarios);
        $CB = $this->montaArraysCB('formUsers',$lista_convidados);
        foreach ($lista_tipos_usuarios as $tipo => $descricao){
            if(isset($CB[$tipo])){
                $param = [];
                $param['colunas'] 	= 3;
                $param['combos']	= $CB[$tipo];
                $formCombo = formbase01::formGrupoCheckBox($param);
                //print_r($CB);
                $param = [];
                $param['titulo'] = '<label><input type="checkbox"  onclick="marcarTodos(\''.$tipo.'\',this.checked);"  name="formTipo['.$tipo.']" id="' . $descricao . '_id" />&nbsp;&nbsp;'.$descricao.'</label>';
                $param['conteudo'] = $formCombo;
                $ret .= addCard($param).'<br><br>';
            } 
        }
        return $ret;
    }
    
    
    private function getListaTiposUsuarios(){
        $ret = [];
        $sql = "select chave, descricao from sys005 where tabela = '000015' and ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['chave']] = $row['descricao'];
            }
        }
        return $ret;
    }
    
    
    
    private function montaArraysCB($nomeCampo,$lista_convidados){
        $ret = [];
        $lista_usuarios = $this->getInfoUsuariosCB($lista_convidados);
        foreach ($lista_usuarios as $user => $info){
            $temp = [];
            $temp["nome"] 		= $nomeCampo.'['.$user.']';
            $temp["etiqueta"] 	= $info['nome'];
            $temp["checked"] 	= $info['permissao'];
            $temp["modulo"] 	= $info['tipo'];
            $temp["classeadd"] 	= $info['tipo'];
            $ret[$info['tipo']][] = $temp;
        }
        return $ret;
    }
    
    private function getInfoUsuariosCB($lista_convidados){
        $ret = [];
        
        $convidados = [];
        $convidados = explode(';',$lista_convidados);
        
        $sql = "select user, nome, tipo from sys001 where ativo = 'S' ORDER BY nome";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $temp = [];
                $temp['nome'] = $row['nome'];
                $temp['tipo'] = $row['tipo'];
                $temp['permissao'] = in_array($row['user'], $convidados) ? true : false;
                $ret[$row['user']] = $temp;
            }
        }
        return $ret;
    }
}