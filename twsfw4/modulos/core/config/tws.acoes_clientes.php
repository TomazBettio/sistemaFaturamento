<?php
/*
 * Data Criacao: 17/05/2023
 * Autor: Alex
 *
 * Descricao: Configuração de ações de clientes
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class acoes_clientes{
    var $funcoes_publicas = array(
        'index' 		=> true,
        
        'incluir_g'       => true,
        'incluir_i'       => true,
        
        'editar_g'		=> true,
        'editar_i'		=> true,
        
        'excluir_g'		=> true,
        'excluir_i'		=> true,
        
        'salvar_g'        => true,
        'salvar_i'        => true,
        
    );
    
    //Tabelas
    private $_grupos    = 'sys014';
    private $_itens     = 'sys015';
    private $_usuarios  = 'sys016';
    
    public function __construct()
    {
        //
    }
    
    public function index()
    {
        $ret='';
        
        $tabela = new tabela01(['titulo'=>"Grupos"]);
        //EDITAR
        $tabela->addAcao([
            'texto' =>  'Editar',
            'link' 	=> getLink()."editar_g&id=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'success',
        ]);        
        //EXCLUIR
        $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir o grupo? \nGrupo: "+tab+" \nDescrição: "+desc');
        $tabela->addAcao([
            'texto' =>  'Excluir',
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir_g&id=','{ID}', {COLUNA:grupo}, {COLUNA:etiqueta})",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'danger',
        ]);
        
        $tabela->addColuna(array('campo' => 'id'			    , 'etiqueta' => 'ID'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'grupo'			    , 'etiqueta' => 'Grupo'			, 'tipo' => 'T', 'width' => 110, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'etiqueta'			, 'etiqueta' => 'Etiqueta'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'ativo'		        , 'etiqueta' => 'Ativo'			, 'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
        
        //INCLUIR
        $tabela->addBotaoTitulo([
            'onclick'=>"setLocation('".getLink()."incluir_g&id=0')",
            'cor'=>'info',
            'texto'=>'Incluir Novo Grupo',
        ]);
        
        $tabela->setDados($this->getGrupos());
        
        $ret.=$tabela;        
        return $ret;
    }
    
    public function editar_g()
    {
        $ret='';
        $id = getParam($_GET,'id', false);
        if($id!==false)
        {
            //Formulário de edição
            $dados=$this->getDadosGrupo($id);
       
            
            $form = new form01([]);
            $form->setBotaoCancela();
            $form->addCampo(array('readonly'=>true,'id' => '', 'campo' => 'valoresForm[grupo]'	    , 'etiqueta' => 'Grupo'	            , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['grupo']	   , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 8, 'obrigatorio' => false));
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[etiqueta]'	, 'etiqueta' => 'Etiqueta'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['etiqueta'] , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[ativo]'		, 'etiqueta' => 'Ativo'		        , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo']	   , 'pasta'	=> 0, 'lista' => tabela('000003'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
            
            
            $form->setEnvio(getLink() . "salvar_g&id=$id", 'valoresForm', 'valoresForm');
            
            $param = [];
            $param['icone'] = 'fa-edit';
            $param['titulo'] = 'Editar Grupo';
            $param['conteudo'] = $form;
            
            $ret .= addCard($param);
            //Tabela de itens do grupo
            
            $tabela = new tabela01(['titulo'=>"Itens"]);
            //EDITAR
            $tabela->addAcao([
                'texto' =>  'Editar',
                'link' 	=> getLink()."editar_i&grupo=".$dados['grupo']."&item=",
                'coluna'=> 'item',
                'flag' 	=> '',
                'cor'   => 'success',
            ]);
            //EXCLUIR
            $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir o item? \nItem: "+tab+" \nDescrição: "+desc');
            $tabela->addAcao([
                'texto' =>  'Excluir',
                'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir_i&grupo=".$dados['grupo']."&item=','{ID}', {COLUNA:etiqueta}, {COLUNA:descricao})",
                'coluna'=> 'item',
                'flag' 	=> '',
                'cor'   => 'danger',
            ]);
            
            $tabela->addColuna(array('campo' => 'item'			    , 'etiqueta' => 'Item'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
            $tabela->addColuna(array('campo' => 'etiqueta'		    , 'etiqueta' => 'Etiqueta'			, 'tipo' => 'T', 'width' => 110, 'posicao' => 'C'));
            $tabela->addColuna(array('campo' => 'descricao'			, 'etiqueta' => 'Descrição'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
            
            //INCLUIR
            $tabela->addBotaoTitulo([
                'onclick'=>"setLocation('".getLink()."incluir_i&grupo=".$dados['grupo']."')",
                'cor'=>'info',
                'texto'=>'Incluir Novo Item',
            ]);
            
            $tabela->setDados($this->getItens($dados['grupo']));
            
            $ret.=$tabela;        
            
        }
        return $ret;
    }
    
    public function editar_i()
    {
        $ret='';
        $grupo = getParam($_GET,'grupo', false);
        $item = getParam($_GET,'item', false);
        
        if($grupo!==false && $item!==false)
        {
            $dados = $this->getDadosItem($item, $grupo);
            
            $form = new form01([]);
            $form->setBotaoCancela();
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[item]'	 , 'etiqueta' => 'Item'	    , 'tipo' => 'T', 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['item']     , 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 8, 'obrigatorio' => true,'readonly'=>true));
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[etiqueta]' , 'etiqueta' => 'Etiqueta'	, 'tipo' => 'T', 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['etiqueta'] , 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[descricao]', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['descricao'], 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
            
            
            $form->setEnvio(getLink() . "salvar_i&grupo=$grupo", 'valoresForm', 'valoresForm');
            
            $param = [];
            $param['icone'] = 'fa-edit';
            $param['titulo'] = 'Editar Item';
            $param['conteudo'] = $form;
            
            $ret .= addCard($param);
        }
        return $ret;
    }
    
    public function incluir_i()
    {
        $ret='';
        $grupo = getParam($_GET,'grupo', false);
        
        if($grupo!==false)
        {
            $form = new form01([]);
            $form->setBotaoCancela();
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[item]'	    , 'etiqueta' => 'Item'	            , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[etiqueta]'	, 'etiqueta' => 'Etiqueta'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[descricao]'		, 'etiqueta' => 'Descrição'		        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
            $form->addCampo(array('id' => '', 'campo' => 'valoresForm[tipo]'		, 'etiqueta' => 'Tipo'		        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false, 'opcoes'=>'T=Texto;CB=Check Box'));
            
            
            $form->setEnvio(getLink() . "salvar_i&id=1&grupo=$grupo", 'valoresForm', 'valoresForm');
            
            $param = [];
            $param['icone'] = 'fa-edit';
            $param['titulo'] = 'Novo Item';
            $param['conteudo'] = $form;
            
            $ret .= addCard($param);
        }
        return $ret;
    }
    
    public function incluir_g()
    {
        $ret='';
        $form = new form01([]);
        $form->setBotaoCancela();        
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[grupo]'	    , 'etiqueta' => 'Grupo'	            , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[etiqueta]'	, 'etiqueta' => 'Etiqueta'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[ativo]'		, 'etiqueta' => 'Ativo'		        , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' , 'pasta'	=> 0, 'lista' => tabela('000003'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        
        
        $form->setEnvio(getLink() . "salvar_g&id=0", 'valoresForm', 'valoresForm');
        
        $param = [];
        $param['icone'] = 'fa-edit';
        $param['titulo'] = 'Novo Grupo';
        $param['conteudo'] = $form;
        
        $ret .= addCard($param);
        return $ret;
    }
    public function salvar_i()
    {
        $grupo = getParam($_GET,'grupo', false);
        $id = getParam($_GET,'id', false);
        $dados = getParam($_POST, 'valoresForm', []);
        if($id==1)
            {
                $campos=[
                    'grupo' => $grupo,
                    'item' => $dados['item'],
                    'descricao'=>$dados['descricao'],
                    'etiqueta'=>$dados['etiqueta'],
                    'tipo'=>$dados['tipo'],
                ];
                $sql = montaSQL($campos, $this->_itens);
         } else {
             $campos=[
                 'descricao'=>$dados['descricao'],
                 'etiqueta'=>$dados['etiqueta'],
             ];
             $sql = montaSQL($campos, $this->_itens,'UPDATE', "grupo='$grupo' AND item='".$dados['item']."'");
            }
            query($sql);
        return $this->index();
        
    }
    public function salvar_g()
    {
        $dados = getParam($_POST, 'valoresForm', []);
        $id = getParam($_GET,'id', false);
        if($id!==false)
        {
            if($id==0)
            {
                $campos = [
                    'grupo'=>$dados['grupo'],
                    'etiqueta'=>$dados['etiqueta'],
                    'ativo'=>$dados['ativo']
                ];
                $sql=montaSQL($campos, $this->_grupos);
            } else {
                $campos = [
                    'grupo'=>$dados['grupo'],
                    'etiqueta'=>$dados['etiqueta'],
                    'ativo'=>$dados['ativo']
                ];
                $sql = montaSQL($campos, $this->_grupos,'UPDATE',"id=$id");
            }
            query($sql);
        }
        return $this->index();        
    }
    
    public function excluir_i($grupo='',$item='')
    {
        $grupo = getParam($_GET,'grupo', $grupo);
        $item = getParam($_GET,'item', $item);
        query("DELETE FROM $this->_usuarios WHERE item='$item'");
        $sql = "DELETE FROM $this->_itens WHERE grupo='$grupo' AND item='$item'";
        query($sql);
        if($grupo!='')
        {
                    return $this->index();
        }
    }
    
    public function excluir_g()
    {
        $id = getParam($_GET, 'id');
        $grupo = $this->getGrupoFromId($id);
        $itens = $this->getItens($grupo);
        foreach($itens as $it){
            $this->excluir_i($grupo,$it['item']);
        }        
        
        query("DELETE FROM $this->_itens WHERE grupo='$grupo'");
        $sql = "UPDATE $this->_grupos SET ativo = 'N' WHERE id = $id";
        query($sql);
        return $this->index();
    }
    
    private function getGrupoFromId($id)
    {
        $ret = query("SELECT grupo FROM $this->_grupos WHERE id = $id");
        return $ret[0]['grupo'];
        
    }
    
    private function getDadosGrupo($id)
    {
        $ret=[];
        $sql = "SELECT grupo,etiqueta,ativo FROM $this->_grupos WHERE id = $id";
        $rows=query($sql);
        if(is_array($rows) && count($rows)==1)
        {
            $campos = ['ativo','etiqueta','grupo'];
            $temp=[];
            foreach($campos as $c){
                $temp[$c] = $rows[0][$c];
            }
            $ret=$temp;
        }
        return $ret;
    }
    
    private function getDadosItem($item,$grupo)
    {
        $ret=[];
        $sql = "SELECT item,etiqueta,descricao FROM $this->_itens WHERE item = '$item' AND grupo = '$grupo'";
        $rows=query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $campos = ['item','etiqueta','descricao'];
            $temp=[];
            foreach($campos as $c){
                $temp[$c] = $rows[0][$c];
            }
            $ret=$temp;
        }
        return $ret;
    }
    
    private function getGrupos()
    {
        $ret=[];
        $sql = "SELECT * FROM $this->_grupos";
        $rows=query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $campos = ['id','etiqueta','grupo','ativo'];
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
    
    private function getItens($grupo)
    {
        $ret=[];
        $sql = "SELECT * FROM $this->_itens WHERE grupo = '$grupo'";
        $rows=query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $campos = ['grupo','item','tipo_usuario','ordem','descricao','etiqueta','tipo','tamanho','largura','linha',
                'linhasTA','mascara','funcao_lista','opcoes','tabela_itens','validacao','obrigatorio','real','inicializador',
                'funcao_layout','funcao_salvar','estilo_form','class_form','help'
            ];
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
    
    private function getUsuarios()
    {
        $ret=[];
        $sql = "SELECT * FROM $this->_usuarios";
        $rows=query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $campos = ['id','valor','extra','item','usuario'];
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
    
    function jsConfirmaExclusao($titulo){
        addPortaljavaScript('function confirmaExclusao(link,id,tab,desc){');
        addPortaljavaScript('	if (confirm('.$titulo.')){');
        addPortaljavaScript('		setLocation(link+id);');
        addPortaljavaScript('	}');
        addPortaljavaScript('}');
    }
}
