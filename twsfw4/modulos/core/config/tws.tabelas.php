<?php
/*
 * Data Criacao: 07/07/2018
 * Autor: Alexandre Thiel
 *
 * Descricao: Configuração de tabelas
 * 
 * Atualizado maio 2023
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class tabelas{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'		=> true,
        'excluir'		=> true,
        'salvar'        => true,
    );
	private $_tabela = 'sys005';
	private $_chave_vazia = '00000000';
	private $_tabela_base = '000000';
	
	public function __construct()
	{
	    //
	}
	
	public function index()
	{
	    $ret='';
	    $tabela = new relatorio01([
	        'titulo'=>'Tabelas sys005',
	        'programa'=>'core_'.get_class($this)
	    ]);
	    
	    $paramTabela['acoes'] = [];
	    
	    $param = [
	    'texto' =>  'Editar',
	    'link' 	=> getLink()."editar&tabela=$this->_tabela_base&chave=",
	    'coluna'=> 'chave',
	    'flag' 	=> '',
	    'width' => 30,
	    'cor'   => 'success',
	    ];
	    $paramTabela['acoes'][] = $param;
	  
	    $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir a tabela "+tab+" ("+desc+") e TODAS as suas subtabelas?"');
	    
	    $param = [
	        'texto' =>  'Excluir',	        
	    //   'link' 	=> getLink()."excluir&tabela=$this->_tabela_base&chave=",
	         'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&tabela=$this->_tabela_base&chave=','{ID}', {COLUNA:chave}, {COLUNA:descricao})",
	        'coluna'=> 'chave',
	        'flag' 	=> '',
	        'cor'   => 'danger',
	    ];
	    $paramTabela['acoes'][] = $param;
	    
	    $tabela->setParamTabela( $paramTabela);
	    
	    $tabela->addColuna(array('campo' => 'chave'			    , 'etiqueta' => 'Tabela'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'descricao'			, 'etiqueta' => 'Descrição'			, 'tipo' => 'T', 'width' => 110, 'posicao' => 'E'));
	    $tabela->addColuna(array('campo' => 'grupo'			    , 'etiqueta' => 'Grupo'		        , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'ativo'		        , 'etiqueta' => 'Ativo'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
	    
	    $p = [];
	    $p['onclick'] = "setLocation('".getLink()."editar&tabela=$this->_tabela_base&chave=$this->_chave_vazia')";
	    $p['cor'] = 'info';
	    $p['texto'] = 'Incluir Tabela';
	    $tabela->addBotao($p);
	    
	    $dados = $this->getDados();
	    $tabela->setDados($dados);
	    
	    $ret .= $tabela;
	    
	    return $ret;
	}
	
	private function getDados( $chave = '')
	{
	    //função que pega os dados (se chave setado, lista de opções, senão lista de entradas na sys005)
	    $ret=[];
	    $sql = "SELECT chave, descricao, grupo, ativo FROM $this->_tabela WHERE tabela = ";
	    if (!empty($chave)){
	        $sql.=" '$chave' ";
	    } else {
	        $sql.=" '$this->_tabela_base' ";
	    }
	    $rows = query($sql);
	    if(is_array($rows) && count($rows)>0)
	    {
	        $campos = ['chave','descricao','grupo','ativo'];
	        foreach($rows as $row)
	        {
	            $temp=[];
	            foreach($campos as $c){
	                $temp[$c] = $row[$c];
	            }
	            
	            $ret[]=$temp;
	        }
	    }
	    return $ret;
	}
	
	private function getDadosFormBase($chave)
	{
	    $ret=[];
	    $campos = ['chave','descricao','grupo','ativo'];
	    foreach($campos as $c){
	        $ret[$c] = '';
	    }
	    $sql="SELECT chave, descricao, grupo, ativo FROM $this->_tabela WHERE tabela = '$this->_tabela_base' AND chave = '$chave'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows)==1){
	        foreach($rows as $row){
	            foreach($campos as $c){
	                $ret[$c] = $row[$c];
	            }
	        }
	    }

	    return $ret;
	}
	private function getDadosForm($chave,$tabela)
	{
	    $ret=[];
	    $campos = ['chave','descricao','grupo','ativo'];
	    foreach($campos as $c){
	        $ret[$c] = '';
	    }
	    $sql="SELECT chave, descricao, grupo, ativo FROM $this->_tabela WHERE tabela = '$tabela' AND chave = '$chave'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows)==1){
	        foreach($rows as $row){
	            foreach($campos as $c){
	                $ret[$c] = $row[$c];
	            }
	        }
	    }
	    
	    return $ret;
	}
	
	public function editar()
	{
	    $ret='';	
	    $chave = getParam($_GET, 'chave', '');    	   
	    $tabela = getParam($_GET, 'tabela', $this->_tabela_base); 
	 
	  //  if($tabela == '000000'){
	 //       $ret.=$this->montaForm($chave, $tabela);
	   /* } else */
	    if($chave==$this->_chave_vazia || $tabela != $this->_tabela_base){
	        $ret.=$this->montaForm($chave, $tabela);
	    }
	    else{
	        $ret.=$this->montaTabela($chave);
	        $ret.=$this->montaForm($chave, $tabela);
	    }
	    
	    return $ret;
	}
	
	private function montaTabela($chave)
	{
	    $ret='';
	    
	    //Lista de dados atrelados àquela chave
	       $lista_tabela = $this->getDados($chave);
	    
	    $tabela = new tabela01(['titulo'=>"Tabelas $chave"]);
	    
	    $param = [
	        'texto' =>  'Editar',
	        'link' 	=> getLink()."editar&tabela=$chave&chave=",
	        'coluna'=> 'chave',
	        'flag' 	=> '',
	        'cor'   => 'success',
	    ];
	    $tabela->addAcao( $param);
	    
	    //Botão EXCLUIR
	    $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir a linha? \nLinha: "+tab+" Descrição: "+desc');
	    
	    $param = [
	        'texto' =>  'Excluir',
	//        'link' 	=> getLink()."excluir&tabela=$chave&chave=",
	        'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&tabela=$chave&chave=','{ID}', {COLUNA:chave}, {COLUNA:descricao})",	        
	        'coluna'=> 'chave',
	        'flag' 	=> '',
	        'cor'   => 'danger',
	    ];
	    $tabela->addAcao( $param);
	    
	    $tabela->addColuna(array('campo' => 'chave'			    , 'etiqueta' => 'Subtabela'			    , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'descricao'			, 'etiqueta' => 'Descrição'			, 'tipo' => 'T', 'width' => 110, 'posicao' => 'E'));
	    $tabela->addColuna(array('campo' => 'grupo'			    , 'etiqueta' => 'Grupo'		        , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'ativo'		        , 'etiqueta' => 'Ativa'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
	    
	    $p = [];
	    $p['onclick'] = "setLocation('".getLink()."editar&chave=$this->_chave_vazia&tabela=$chave')";
	    //$p['tamanho'] = 'pequeno';
	    $p['cor'] = 'info';
	    $p['texto'] = 'Incluir Nova Entrada';
	    $tabela->addBotaoTitulo($p);
	    
	    $tabela->setDados($lista_tabela);
	    
	    $ret .= $tabela;
	    return $ret;
	}
	
	private function montaForm($chave, $tabela)
	{
	    $ret='';
	    //Dados da linha
	    if($tabela=='000000')
	    {
	        $dados = $this->getDadosFormBase($chave);
	    } else{
	        $dados = $this->getDadosForm($chave,$tabela);
	    }
	    
	    $form = new form01([]);
	    $form->setBotaoCancela();
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[chave]'		, 'etiqueta' => 'Nome'	            , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['chave']    , 'pasta'	=> 0, 'lista' => ''	             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[descricao]'	, 'etiqueta' => 'Descrição'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['descricao'], 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[grupo]'	    , 'etiqueta' => 'Grupo'	            , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['grupo']	   , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 8, 'obrigatorio' => false));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[ativo]'		, 'etiqueta' => 'Ativa'		        , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo']	   , 'pasta'	=> 0, 'lista' => tabela('000003'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
	    
	    
	    $form->setEnvio(getLink() . "salvar&tabela=$tabela&chave=$chave", 'valoresForm', 'valoresForm');
	    
	    $param = [];
	    $param['icone'] = 'fa-edit';
	    $param['titulo'] = $chave == '' ? 'Incluir' : 'Editar';
	    $param['conteudo'] = $form;
	    
	    $ret .= addCard($param);
	    return $ret;
	}
	
	public function excluir()
	{
	    $tabela = getParam($_GET, 'tabela', $this->_tabela_base);
	    $chave = getParam($_GET, 'chave', '') == $this->_chave_vazia ? '' : getParam($_GET, 'chave', '');
	    if($tabela==$this->_tabela_base){//exclusão direto do index
	        //Deleta subtabelas
	        $sql = "DELETE FROM $this->_tabela WHERE tabela = '$chave'";
	        query($sql);
	        //Deleta entrada
	        $sql = "DELETE FROM $this->_tabela WHERE tabela = '$this->_tabela_base' AND chave = '$chave'";
	    } else{ //exclusão de subtabela
	        $sql = "DELETE FROM $this->_tabela WHERE tabela = '$tabela' AND chave = '$chave'";
	    }
	    query($sql);
	    return $this->index();
	}
	
	public function salvar()
	{
	    $tabela = getParam($_GET, 'tabela', $this->_tabela_base);
	    $chave = getParam($_GET, 'chave', '') == $this->_chave_vazia ? '' : getParam($_GET, 'chave', '');
	    $dados = getParam($_POST, 'valoresForm', []);
	    
	    if($tabela==$this->_tabela_base){ //inserção de nova label de lista na sys005	        
	        $sql = "INSERT INTO $this->_tabela (tabela, chave, descricao, grupo, ativo)
                    VALUES ('$tabela','". $dados['chave']."', '".$dados['descricao']."', '".$dados['grupo']."', '".$dados['ativo']."')";
	       // echo ($sql);
	    } 
	    else if(empty($chave)){ //inserção de novo item no array da label "chave"
	        $sql = "INSERT INTO $this->_tabela (tabela, chave, descricao, grupo, ativo)
                    VALUES ('$tabela','". $dados['chave']."', '".$dados['descricao']."', '".$dados['grupo']."', '".$dados['ativo']."')";
	       // echo ($sql);
	    } 
	    else{ //atualização de item $chave
	        $sql = "UPDATE $this->_tabela 
                    SET chave = '".$dados['chave']."', descricao = '".$dados['descricao']."', grupo = '".$dados['grupo']."', ativo = '".$dados['ativo']."'
                    WHERE tabela='$tabela' AND chave = '$chave'";
	       // echo ($sql);
	    }
	    query($sql);
	    
	    return $this->index();
	}
	
	function jsConfirmaExclusao($titulo){
	    addPortaljavaScript('function confirmaExclusao(link,id,tab,desc){');
	    addPortaljavaScript('	if (confirm('.$titulo.')){');
	    addPortaljavaScript('		setLocation(link+id);');
	    addPortaljavaScript('	}');
	    addPortaljavaScript('}');
	}
}