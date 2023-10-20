<?php
/*
 * Data Criacao: 17/05/2023
 * Autor: Alex
 *
 * Descricao: Configuração de parâmetros sys006
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class parametros{
    var $funcoes_publicas = array(
        'index'=> true,
        'salvar'=>true,
        'excluir'=>true,
        'editar'=>true,
        'index'=>true
    );
    
    public function __construct(){
        
    }
    
    public function index(){
        $ret='';
        
        $tabela=new tabela01(['titulo'=>"Parâmetros"]);
        //EDITAR
        $tabela->addAcao([
            'texto' =>  'Editar',
            'link' 	=> getLink()."editar&id=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'success',
        ]);
        //EXCLUIR
        $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir o parâmetro? \nParâmetro: "+tab+" \nDescrição: "+desc');
        $tabela->addAcao([
            'texto' =>  'Excluir',
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&id=','{ID}', {COLUNA:parametro}, {COLUNA:descricao})",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'danger',
        ]);
        
        $tabela->addColuna(array('campo' => 'id'	   , 'etiqueta' => 'ID'		  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'parametro', 'etiqueta' => 'Parâmetro', 'tipo' => 'T', 'width' => 110, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'grupo'	   , 'etiqueta' => 'Grupo'	  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'valor'	   , 'etiqueta' => 'Valor'    , 'tipo' => 'T', 'width' => 110, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'tipo'	   , 'etiqueta' => 'Tipo'	  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'tamanho'  , 'etiqueta' => 'Tamanho'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'opcoes'   , 'etiqueta' => 'Opções'	  , 'tipo' => 'T', 'width' => 110, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'tabela'   , 'etiqueta' => 'Tabela'	  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'ativo'	   , 'etiqueta' => 'Ativo'	  , 'tipo' => 'T', 'width' => 150, 'posicao' => 'C'));
        
        //INCLUIR
        $tabela->addBotaoTitulo([
            'onclick'=>"setLocation('".getLink()."editar&id=0')",
            'cor'=>'info',
            'texto'=>'Incluir Novo Parâmetro',
        ]);
        
        $tabela->setDados($this->getDados());
        
        $ret.=$tabela;  
        return $ret;
    }
    
    private function getDados()
    {
        $ret=[];
        $sql = "SELECT * FROM sys006 WHERE ativo = 'S' ORDER BY grupo";
        $rows=query($sql);
        if(is_array($rows)&&count($rows)>0)
        {
            foreach($rows as $row)
            {
                $ret[]=$row;
            }
        }
        return $ret;
    }
    
    public function editar()
    {
        $id=getParam($_GET, 'id');
        $dados=[
            'parametro'=>'',
            'grupo'=>''	,
            'descricao'=>''	, 
            'valor'=>''	, 
            'tipo'=>''	, 
            'tamanho'=>'',	
            'opcoes'=>'', 
            'tabela'=>'', 
            'ativo'=>''
            ];
        
        $form=new form01([]);
        $form->setBotaoCancela();
        if($id!=0)
        {
            $dados=$this->getDadoEdit($id);
            $form->addCampo(array('readonly'=>true,'id' => '', 'campo' => 'valoresForm[parametro]'	    , 'etiqueta' => 'Parâmetro'	            , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['parametro']	   , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
        }
        else{
                    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[parametro]'	    , 'etiqueta' => 'Parâmetro'	            , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['parametro']	   , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
        }
                
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[grupo]'	, 'etiqueta' => 'Grupo'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['parametro'] , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[descricao]'	, 'etiqueta' => 'Descrição'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['descricao'] , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[valor]'	, 'etiqueta' => 'Valor'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['valor'] , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[tipo]'	, 'etiqueta' => 'Tipo'	        , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['tipo'] , 'pasta'	=> 0, 'opcoes'=>'T=Texto;N=Numero;A=Array','lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[tamanho]'	, 'etiqueta' => 'Tamanho'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['tamanho'] , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[opcoes]'	, 'etiqueta' => 'Opções'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['opcoes'] , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[tabela]'	, 'etiqueta' => 'Tabela'	        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['tabela'] , 'pasta'	=> 0, 'lista' => ''				 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));        
        $form->addCampo(array('id' => '', 'campo' => 'valoresForm[ativo]'		, 'etiqueta' => 'Ativo'		        , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['ativo']	   , 'pasta'	=> 0, 'lista' => tabela('000003'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
        
        
        $form->setEnvio(getLink() . "salvar&id=$id", 'valoresForm', 'valoresForm');
        
        $param = [];
        $param['icone'] = 'fa-edit';
        $param['titulo'] = 'Edição de Parâmetro';
        $param['conteudo'] = $form;
        
        return addCard($param);
    }
    
    private function getDadoEdit($id)
    {
        $ret=[];
        $sql="SELECT * FROM sys006 WHERE id = $id and ativo = 'S'";
        $rows=query($sql);
        if(is_array($rows)&&count($rows)>0)
        {
            $ret=$rows[0];
        }
        return $ret;
    }
    
    public function salvar ()
    {
        $dados = getParam($_POST,'valoresForm',[]);
        $id = getParam($_GET,'id');
        $campos=[
            'parametro'=>$dados['parametro'],
            'grupo'=>$dados['grupo'],
            'descricao'=>$dados['descricao'],
            'valor'=>$dados['valor'],
            'tipo'=>$dados['tipo'],
            'tamanho'=>$dados['tamanho'],
            'opcoes'=>$dados['opcoes'],
            'tabela'=>$dados['tabela'],
            'ativo'=>$dados['ativo'],
        ];
        if($id==0){
            query(montaSQL($campos, 'sys006'));
        } else{
            query(montaSQL($campos, 'sys006','UPDATE',"id = $id"));
        }
        return $this->index();
    }
    
    public function excluir()
    {
        $id = getParam($_GET,'id');
        query(montaSQL(['ativo'=>'N'], 'sys006','UPDATE',"id = $id"));
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