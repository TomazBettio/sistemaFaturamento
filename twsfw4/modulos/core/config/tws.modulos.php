<?php
/*
* Data Criação: 08/06/2020
* Autor: bcs
*
* Arquivo: class.modulos.inc.php
* Descrição: Baseado no programas.inc.php
*/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
class modulos{
    var $funcoes_publicas = array(
        'index'			=> true,
        'editar'        => true,
        'salvar'        => true,
        'excluir'       => true,
    );
    
    var $_programa = 'modulos';
    var $_titulo = 'Módulos';
	
    //Função base index
	function index(){
	    //Tabela
	    $bw = new tabela01(array('paginacao' => false));
	    $bw->addColuna(array('campo' => 'id' , 'etiqueta' => 'ID'	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
	    $bw->addColuna(array('campo' => 'nome', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));	    
	    $bw->addColuna(array('campo' => 'etiqueta', 'etiqueta' => 'Etiqueta', 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
	    $bw->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
	    $bw->addColuna(array('campo' => 'programa', 'etiqueta' => 'Programa', 'tipo' => 'T', 'width' =>  10, 'posicao' => 'centro'));
	    	    
	    $dados = $this->getDados();
	    $bw->setDados($dados);
	    
	    //Botão EDITAR
	    $param = array(
	        'texto' => 'Editar', //Texto no botão
	        'link' => getLink().'editar&id=', //Link da página para onde o botão manda
	        'coluna' => 'id', //Coluna impressa no final do link
	        'width' => 10, //Tamanho do botão
	        'flag' => '',
	        'tamanho' => 'pequeno', //Nenhum fez diferença?
	        'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
	    );
	    $bw->addAcao($param);
	    
	    //Botão INCLUIR
	    $param = array();
	    $p = array(
	    'onclick' => "setLocation('".getLink()."editar&id=0')",
	    'tamanho' => 'pequeno',
	    'texto' => 'Incluir',
	    'cor'=>'success');
	    $param['botoesTitulo'][] = $p;
	    
	    //Botão EXCLUIR
	    $param2 = array(
	        'texto' => 'Excluir', //Texto no botão
	        'link' => getLink().'excluir&id=', //Link da página para onde o botão manda
	        'coluna' => 'id', //Coluna impressa no final do link
	        'width' => 10, //Tamanho do botão
	        'flag' => '',
	        'tamanho' => 'pequeno', //Nenhum fez diferença?
	        'cor' => 'danger', //padrão: azul; danger: vermelho; success: verde
	    );
	    $bw->addAcao($param2);
	    
	    $param['conteudo'] = $bw . '';
	    $param['titulo'] = 'Módulos';
	    return addCard($param);
	}
	
	//Pega os dados da tabela app001
	private function getDados(){
	    $ret = array();
	    $sql = "select app001.id as id, app001.nome as nome, app001.etiqueta as etiqueta, app001.descricao as descricao, app001.programa as programa  from app001";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $campos = array('id',  'nome', 'etiqueta', 'descricao', 'programa');
	        foreach ($rows as $row){
	            $temp = array();
	            foreach ($campos as $campo){
	                $temp[$campo] = $row[$campo];
	            }
	            $ret[] = $temp;
	        }
	    }
	    return $ret;
	}
	
	public function editar()
	{
	   // echo "Em construção";
	    
	    $id = getParam($_GET, 'id', 0);	 
	    
	    $form = new form01();    
	    if(0==$id)
	    {
	        $form->addCampo(array('id' => '', 'campo' => 'formPrograma[nome]', 'etiqueta' => 'Nome', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '', 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
	        $form->addCampo(array('id' => '', 'campo' => 'formPrograma[etiqueta]', 'etiqueta' => 'Etiqueta', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
	        $form->addCampo(array('id' => '', 'campo' => 'formPrograma[desc]', 'etiqueta' => 'Descrição', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '', 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 8, 'obrigatorio' => false));
	        $form->addCampo(array('id' => '', 'campo' => 'formPrograma[programa]', 'etiqueta' => 'Programa', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	        $form->addCampo(array('id' => '', 'campo' => 'formPrograma[ativo]', 'etiqueta' => 'Ativo', 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '', 'pasta'	=> 0	, 'lista' => tabela('000003', 'desc', false),'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
	        
	    }
	    else{
	        $dados = $this->getDadosApp001($id);
	        
    	    $form->addCampo(array('id' => '', 'campo' => 'formPrograma[nome]', 'etiqueta' => 'Nome', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['nome'], 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
    	    $form->addCampo(array('id' => '', 'campo' => 'formPrograma[etiqueta]', 'etiqueta' => 'Etiqueta', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['etiqueta'] , 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
    	    $form->addCampo(array('id' => '', 'campo' => 'formPrograma[desc]', 'etiqueta' => 'Descrição', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['descricao'], 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 8, 'obrigatorio' => false));
    	    $form->addCampo(array('id' => '', 'campo' => 'formPrograma[programa]', 'etiqueta' => 'Programa', 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['programa'] , 'pasta'	=> 0	, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
    	    $form->addCampo(array('id' => '', 'campo' => 'formPrograma[ativo]', 'etiqueta' => 'Ativo', 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo'], 'pasta'	=> 0	, 'lista' => tabela('000003', 'desc', false),'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
	    }
	    $form->setEnvio(getLink() . 'salvar&id=' . $id, 'formPrograma', 'formPrograma');
	    	    
	    $param = [];
	    $p = array(
	    'onclick' => "setLocation('".getLink()."index')",
	    'tamanho' => 'pequeno',
	    'cor' => 'danger',
	    'texto' => 'Cancelar');
	    $param['botoesTitulo'][] = $p;
	    
	    $titulo = 0==$id ? 'Incluir Módulo' : 'Editar Módulo';
	    
	    $param['conteudo'] =  $form . '';
	    $param['titulo'] = $titulo;
	    
	    $ret = addCard($param);
	    
	    return $ret;	    
	}
	
	private function getDadosApp001($id){
	    $ret = array();
	    $campos = array('nome', 'etiqueta', 'descricao', 'programa','ativo');
	    foreach ($campos as $campo){
	        $ret[$campo] = '';
	    }
	    if($id != 0){
	        $sql = "select app001.nome as nome, app001.etiqueta as etiqueta, app001.descricao as descricao, app001.programa as programa, app001.ativo as ativo from app001 where id = $id";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            foreach ($campos as $campo){
	                $ret[$campo] = $rows[0][$campo];
	            }
	        }
	    }
	    return $ret;
	}
	
	public function salvar()
	{
	    $ret = '';
	    $id = getParam($_GET, 'id', 0);
	    $campos = $_POST['formPrograma'];
	    
	    if($id!=0) //Se tem id faz update
	    {
	        $sql = "select * from app001 where id = $id and nome = '" . $campos['nome'] . "'";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) == 0){
	            $sql = "update app002 set modulo = '" . $campos['nome'] . "' where modulo in (select nome from app001 where id = $id)";
	            query($sql);
	        }
	        query("UPDATE app001
                    SET app001.nome = '".$campos['nome']."' ,
                    app001.etiqueta = '".$campos['etiqueta']."' ,
                    app001.descricao = '".$campos['desc']."' ,
                    app001.programa = '".$campos['programa']."' ,
                    app001.ativo = '".$campos['ativo']."'
                    WHERE app001.id = $id");
	        addPortalMensagem("SUCESSO!", "O módulo foi alterado!");
	    }	
	    else {
	        $campos['nivel']=1;
	        $campos['ordem']=5;
	        query("INSERT INTO app001 (app001.nome, app001.etiqueta,app001.descricao,app001.programa,app001.nivel,app001.ordem,app001.ativo)
                    VALUES ('".$campos['nome']."' , '".$campos['etiqueta']."' , '".$campos['desc'].
	            "' , '".$campos['programa']."' , '".$campos['nivel']. "', '" . $campos['ordem'] . "', '".$campos['ativo']."')");
	        addPortalMensagem("SUCESSO!", "O módulo foi inserido!");
	        
	    }
	    
	    $ret = $this->index();	    
	    return $ret;
	}
	
	public function excluir()
	{
	    $id = getParam($_GET, 'id', 0);
	    $sql =  "SELECT app002.programa FROM (app001 JOIN app002 on (app002.modulo = app001.nome)) where app001.id = $id";
	   $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        addPortalMensagem("ERRO!", "O Módulo está atrelado a um programa!", 'erro');
	    }
	     else{
	        query("UPDATE app001 SET ativo = 'N' WHERE app001.id = $id");
	        addPortalMensagem("SUCESSO!", "O módulo foi excluído!");
	        
	    }
	    return $this->index();
	}
}