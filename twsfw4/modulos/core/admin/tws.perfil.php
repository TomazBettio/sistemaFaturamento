<?php
/*
 * Data Criacao 03/07/2023
 * Autor: Alex Cesar
 *
 * Descricao: Página de perfil do usuário
 *
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class perfil{
    
    private $_anexopath = '/var/www/avatar/';
    
    var $funcoes_publicas = array(
        'index' 		=> true,
        'salvar'        => true,
        'upload'        => true,
    );
    
    public function __construct(){
        
    }
    
    public function index(){
        global $nl;
        $ret = '';
        
        $dados = $this->getDadosUsuario();
        $iniciais = $this->getListaProgramasIniciais();
        $linguas = $this->getLinguas();
        $form = new form01([]);
        
        $form->setBotaoCancela();
        $form->setPastas(array('Geral', 'Contato', 'Senha', 'Foto'));
        
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[nome]', 'etiqueta' => 'Nome', 'tipo' => 'T', 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['nome'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[apelido]', 'etiqueta' => 'Apelido', 'tipo' => 'T', 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['apelido']  	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[inicial]', 'etiqueta' => 'Programa Inicial', 'tipo' => 'A', 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['inicial']  	, 'pasta'	=> 0, 'lista' => $iniciais	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[lingua]', 'etiqueta' => 'Língua', 'tipo' => 'A', 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['lingua'] 	, 'pasta'	=> 0, 'lista' => $linguas	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        
        //CONTATO
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[email]', 'etiqueta' => 'Email', 'tipo' => 'T', 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['email'] 	, 'pasta'	=> 1, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[fone1]', 'etiqueta' => 'Telefone', 'tipo' => 'T', 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['fone1'] 	, 'pasta'	=> 1, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[fone2]', 'etiqueta' => 'Telefone 2 (Opcional)', 'tipo' => 'T', 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['fone2']  	, 'pasta'	=> 1, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        
        //SENHA
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[senha]', 'etiqueta' => 'Nova Senha', 'tipo' => 'S', 'tamanho' => '15', 'linhas' => '', 'valor' => '' 	, 'pasta'	=> 2, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        $form->addCampo(array('id' => '', 'campo' => 'formPerfil[senha2]', 'etiqueta' => 'Confirma Senha', 'tipo' => 'S', 'tamanho' => '15', 'linhas' => '', 'valor' => ''  	, 'pasta'	=> 2, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        
        //AVATAR
        $form->addCampo(array('id' => '', 'campo' => 'imageUpload', 'etiqueta' => 'Nova Imagem', 'tipo' => 'F', 'tamanho' => '15', 'linhas' => '', 'valor' => ''  	, 'pasta'	=> 3, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        
        $form->setEnvio(getLink() . 'salvar', 'formPerfil');
        $ret .= addCard([ 'titulo' => "Editar Perfil", 'conteudo' => $form]);
        
        return $ret;
    }
    
    
    private function upload()
    {
        //var_dump($_POST);var_dump($_FILES);die();
        
        if(count($_FILES) > 0)
        {
            if (is_uploaded_file($_FILES['imageUpload']['tmp_name']))
            {
                global $config;
                $arquivo = $_FILES['imageUpload']['name'];
                echo $arquivo;
                $partes = explode('.',$arquivo);
                $tipo = end($partes);
                if($tipo == 'png' || $tipo == 'jpg' || $tipo == 'gif' )
                {
                    $dir = $config['baseS3'].'imagens/avatares/';
                    $this->salvaImagem($dir, $tipo);
                    
                } else {
                    echo 'Não é arquivo válido, o tipo é '.$tipo;
                }
            } else {
                echo'Não fez upload';
            }
        }
       // redireciona(getLink().'index');
    }
    
    private function salvaImagem($dir, $tipo)
    {
        $id = getUsuario('id', getUsuario());
        
        if(!is_dir($dir)){
            mkdir($dir);
        }
        $png = $dir . $id . '.png';
        $jpg = $dir . $id . '.jpg';
        $gif = $dir . $id . '.gif';
        
        //deleta avatar anterior (se houver)
        if(file_exists($png)){
            unlink($png);
        } else {echo "nao tinha png ";}
        if(file_exists($jpg)){
            unlink($jpg);
        } else {echo "nao tinha jpg ";}
        if(file_exists($gif)){
            unlink($gif);
        }else {echo "nao tinha gif ";}
        
        $origem = $_FILES['imageUpload']['tmp_name'];
        switch($tipo){
            case 'png':
                $destino = $png;
                break;
            case 'jpg':
                $destino = $jpg;
                break;
            case 'gif':
                $destino = $gif;
                break;
            default:
                $destino = '';
                break;
        }
        if($destino != ''){
            move_uploaded_file($origem, $destino);
            echo "Fiz upload para id = $id";
            
        } else {echo "opa, destino vazio";}
    }
    
    private function getDadosUsuario()
    {
        $ret = [];
        $user = getUsuario();
        $campos = array("nome", "apelido", "senha", "fone1", "fone2", "email", "inicial", "lingua", );
        $sql = "select * from sys001 where user = '$user'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) == 1){
            foreach($campos as $campo){
                if(isset($rows[0][$campo])){
                    $ret[$campo] = $rows[0][$campo];
                } else {
                    $ret[$campo] = '';
                }
            }
        }
        $ret['senha2'] = '';
        return $ret;
    }
    
    private function getListaProgramasIniciais(){
        $ret = array(array('', ''));
        $sql = "select app002_new.programa as valor, concat(app001.etiqueta, ' - ', app002_new.etiqueta) as etiqueta from app001 join 
(select app002.programa, app002.etiqueta, app002.modulo, app002.ativo FROM app002 join sys115 on (sys115.programa = app002.programa) WHERE sys115.user = '".getUsuario()."') as app002_new
on (app001.nome = app002_new.modulo) where app002_new.ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    0 => $row['valor'],
                    1 => $row['etiqueta'],
                );
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getLinguas(){
        $ret = array(array('', ''));
        $sql = "select chave, descricao from sys005 where tabela = '000020' and ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    0 => $row['chave'],
                    1 => $row['descricao'],
                );
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function salvar()
    {
        $usuario = getUsuario();
        
        $dados = getParam($_POST, 'formPerfil', []);
        if(!empty($_FILES)){
            $this->upload();
        }
        if($this->verificarSenhas())
        {
            $temp = [];
            foreach ($dados as $campo=>$valor){
                if(!empty($valor)){
                    $temp[$campo] = $valor;
                }
            }
            unset($temp['senha2']);
            $temp['senha'] = login::criptografarSenha($temp['senha']);
            $sql = montaSQL($temp, 'sys001', 'update', "user = '$usuario'");
            log::gravaLog('senha', $sql);
            query($sql);
            addPortalMensagem("Dados alterados com sucesso");
        } else {
            addPortalMensagem("As senhas informadas são diferentes", 'error');
        }
        redireciona(getLink().'index');
    }
    
    private function verificarSenhas(){
        $ret = false;
        if(!isset($_POST["formPerfil"]['senha']) || empty($_POST["formPerfil"]['senha'])){
            $ret = true;
        }
        else{
            if(isset($_POST["formPerfil"]['senha2']) && !empty($_POST["formPerfil"]['senha2']) && $_POST["formPerfil"]['senha'] == $_POST["formPerfil"]['senha2']){
                $ret = true;
            }
        }
        return $ret;
    }
    
}