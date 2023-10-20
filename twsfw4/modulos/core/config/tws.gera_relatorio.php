<?php
/*
 * Data Criacao: 05/06/2023
 * Autor: Alex
 *
 * Descricao: Gerador de Relatórios
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

///salvar filtros direto pela tabela


class gera_relatorio {
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'        => true,
        'salvar'        => true,
        'excluir'       => true,
        'incluir'       => true,
  //      'visualizar'    => true,
        'executa'       => true,
    );
    
    //Tabela a guardar as informações de um relatório
	private $_tabela = 'formbase';
	private $_programa = "config.gera_relatorio.executa";
	//private $_cliente = 'admin';
	
	public function __construct()
	{
	    //
	}
	
	public function index()
	{
	    //redireciona(getLink().'visualizar');
	    $ret='';
	    
	    $tabela = new tabela01(['titulo' => 'Modelos de Relatórios']);
	    
	    //Editar
	    $tabela->addAcao([
	        'texto' =>  'Editar',
	        'link' 	=> getLink()."editar&id=",
	        'coluna'=> 'id',
	        'flag' 	=> '',
	        'cor'   => 'success',
	    ]);
	    //PDF
	    $tabela->addAcao([
	        'texto' =>  'Permite PDF',
	        'link' 	=> getLink()."salvar&pdf=1&id=",
	        'coluna'=> 'id',
	        'flag' 	=> '',
	        'cor'   => '',
	    ]);
	    //EXCEL
	    $tabela->addAcao([
	        'texto' =>  'Permite Excel',
	        'link' 	=> getLink()."salvar&excel=1&id=",
	        'coluna'=> 'id',
	        'flag' 	=> '',
	        'cor'   => '',
	    ]);
	    //Excluir
	    $tabela->addAcao([
	        'texto' =>  'Excluir',
	        'link' 	=> getLink()."excluir&id=",
	        'coluna'=> 'id',
	        'flag' 	=> '',
	        'cor'   => 'danger',
	    ]);
	    
	    
	    $tabela->addColuna(array('campo' => 'id'	   , 'etiqueta' => 'ID'		  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' =>  120, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'my_query' , 'etiqueta' => 'SQL'	  , 'tipo' => 'T', 'width' =>  300, 'posicao' => 'C'));
	    // $tabela->addColuna(array('campo' => 'filtro' , 'etiqueta' => 'Filtro'	  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'conexao'  , 'etiqueta' => 'Tipo de Query'	  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'geraPDF'  , 'etiqueta' => 'Permite PDF'	  , 'tipo' => 'T', 'width' =>  120, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'geraExcel', 'etiqueta' => 'Permite Excel'	  , 'tipo' => 'T', 'width' =>  120, 'posicao' => 'C'));
	    
	    //INCLUIR
	    $tabela->addBotaoTitulo([
	        'onclick'=>"setLocation('".getLink()."incluir')",
	        'cor'=>'info',
	        'texto'=>'Criar Novo Relatório',
	    ]);
	    
	    $tabela->setDados($this->getDadosAll());
	    $ret.=$tabela;
	    
	    return $ret;
	}
	
	private function getDadosAll()
	{
	    $ret = [];
	    $rows = query("SELECT * FROM formbase WHERE usuario = '".getUsuario()."'");
	    if(is_array($rows) && count($rows)>0){
	        foreach ($rows as $row){
	            $temp = [];
	            foreach($row as $chave=>$valor){
    	            if(!is_int($chave)){
    	                $temp[$chave] = $valor;
    	                if($chave == 'conexao'){
        	                switch ($valor){
        	                    case 4:
        	                        $temp[$chave] = 'WinThor';
        	                        break;
        	                    default:
        	                        $temp[$chave] = 'MySQL';
        	                }
        	            } 
    	            }
    	        }
    	        $ret[] = $temp;
	        }
	    }	     
	    return $ret;
	}
	
	public function incluir()
	{
	    $ret = '';
	    
	    $form = new form01([]);
	    $form->setBotaoCancela();
	    $form->setPastas(array('Geral', 'Convidados'));
	    
	    //$opcoes = '1=query;2=query2;3=query3;4=query4;5=query5';
	    $opcoes = '1=MySQL;4=WinThor';
	    
	   $form->addCampo(array('id' => '', 'campo' => 'valoresForm[titulo]'    , 'etiqueta' => 'Nome do Relatório (opcional)'         , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	   $form->addCampo(array('id' => '', 'campo' => 'valoresForm[descricao]' , 'etiqueta' => 'Descrição do Relatório'    , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '' 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
	   $form->addCampo(array('id' => '', 'campo' => 'valoresForm[my_query]'  , 'etiqueta' => 'Sua Query'	                   , 'tipo' => 'TA' 	, 'tamanho' => '95', 'linhas' => '', 'valor' => '' 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
	   $form->addCampo(array('id' => '', 'campo' => 'valoresForm[conexao]'   , 'etiqueta' => 'Tipo de Query'	                   , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => '1' 	, 'pasta'	=> 0, 'lista' => '', 'opcoes' => $opcoes	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	   
	   $form->setEnvio(getLink() . 'salvar&id=0', 'valoresForm', 'valoresForm');
	        
	   $ret .= addCard([ 'titulo' => "Novo Relatório", 'conteudo' => $form]);
	    
	   return $ret;
	}
	
	public function editar()
	{
	    $ret = '';
	    
	    $id = getParam($_GET, 'id', 0);	    
	    $ret .= $this->montaFormEdicao($id);
	    return $ret . '';
	}
	
	private function montaFormEdicao($id)
	{
	    $ret = '';
	    
	    $dados = $this->getDadosSingle($id);
	    //FORM DE EDIÇÃO
	    $form = new form01([]);
	    $form->setBotaoCancela();
	    
	    $opcoes = '1=MySQL;4=WinThor';
	    
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[titulo]'    , 'etiqueta' => 'Nome do Relatório (opcional)'                        , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['titulo'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[descricao]' , 'etiqueta' => 'Descrição do Relatório'                              , 'tipo' => 'T' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['descricao'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[my_query]'  , 'etiqueta' => 'SQL'	                                        , 'tipo' => 'TA' 	, 'tamanho' => '95', 'linhas' => '', 'valor' => $dados['my_query'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 8, 'obrigatorio' => true));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[conexao]'   , 'etiqueta' => 'Query'	                                    , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['conexao'] 	, 'pasta'	=> 0, 'lista' => '', 'opcoes' => $opcoes	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[geraPDF]'  , 'etiqueta' => 'Gerar PDF'	                                        , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['geraPDF'] 	, 'pasta'	=> 0, 'lista' => ''	, 'opcoes' => 'S=Sim;N=Não', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	    $form->addCampo(array('id' => '', 'campo' => 'valoresForm[geraExcel]', 'etiqueta' => 'Gerar Excel'	                                    , 'tipo' => 'A' 	, 'tamanho' => '35', 'linhas' => '', 'valor' => $dados['geraExcel'] 	, 'pasta'	=> 0, 'lista' => ''	, 'opcoes' => 'S=Sim;N=Não', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false));
	    
	 //   $form->setEnvio(getLink() . "salvar&id=$id", 'valoresForm', 'valoresForm');
	    
	    //$ret .= addCard([ 'titulo' => "Atualizar Relatório", 'conteudo' => $form]);
	    $colunas = $this->montaTabelaColunas($id);
	    
	    $ret = formbase01::form([
	        'acao' => getLink()."salvar&id=$id",
	        'id' => "valoresForm",
	    ],$form . $colunas);
	    
	    $param = [];
	    $param['URLcancelar'] = '';
	    $param['IDform'] = 'valoresForm';
	    formbase01::formSendFooter($param);
	    
	    return $ret;
	}
	
	public function executa()
	{
	    $ret = '';

	    $id = getParam($_GET, 'id');
	    $dados = $this->getDadosSingle($id);
	    
	    $rows = $this->executaQuery($dados, $dados['conexao']);
	    $ret .= $this->montaTabelaRelatorio($id,$rows, $dados);
	    return $ret;
	}
	
	private function montaTabelaRelatorio($id, $rows, $dados)
	{
	    $ret = '';
	    
        $colunas = $this->recuperaColunasSelecionadas($dados);
        if($dados['tipocolunas'] != '') {
            $colunas = $this->recuperaValorColunas($colunas,$dados['tipocolunas']);
	    }
	    
	    $param = [];
	    $param['programa'] = $this->_programa."&id=$id";
	    
	    if($dados['filtro'] == 'S'){
	        $param['filtro'] = true;
	    }
	    
	    $tabela = new relatorio01($param);
	    foreach($colunas as $coluna){
	        $tabela->addColuna(array('campo' => $coluna['nome'] , 'etiqueta' => $coluna['etiqueta'], 'tipo' => $coluna['tipo'], 'width' =>  160, 'posicao' => 'C'));
	    }
	    
	    if($dados['filtro'] != 'S'){
	        $rows = $this->getDadosFiltrados([], $dados['my_query'], $dados['conexao']);
	        $tabela->setDados($rows);
	    } else if(!$tabela->getPrimeira()){
	        $filtro = $tabela->getFiltro();
	        $rows = $this->getDadosFiltrados($filtro, $dados['my_query'], $dados['conexao']);
	        $tabela->setDados($rows);
	    }
	    
	    $ret .= $tabela;
	    return $ret;
	}
	
	private function getDadosFiltrados($filtro,$query, $tipo_q)
	{
	    $ret = [];
	    $my_query = strtolower($query);
	    $my_query = str_replace("#","'",$my_query);
	    //echo $q;
	    //filtros que realmente existem
	    
	 //   var_dump($filtro);die();
	    
	    $fils = [];
	    $temp = [];
	    foreach($filtro as $fi=>$va){
	        if($fi != "fwFiltro" && $va != ''){
	            $temp["coluna"] = $fi;
	            $temp["valor"] = $va;
	            $fils[] = $temp;
	        }
	    }
	    
	    if(!empty($temp))
	    {
    	    $q = explode("where",$my_query);
    	    if(count($q)==1)
    	    {
    	        //Não tinha where
    	        $where = "WHERE ";
    	    } else {
    	        //tinha where
    	        $where = " AND ";
    	    }
    	    
    	    
    	    
    	   // var_dump($fils);die();
    	    
    	    //construção where
    	    $i = count($fils);
    	    foreach($fils as $fi){
                    $where .= $fi['coluna'] . " = '".$fi['valor']."'";
                    $i--;
                    if($i != 0){
                        $where .= " AND ";
                    }
    	    }
    	    
    	  //  echo $my_query . $where; die();
    	    
    	    //query
    	    switch($tipo_q){
    	        case 4:
    	            $ret = query4($my_query.$where);
    	            break;
    	        case 1:
    	        default:
    	            $ret = query($my_query.$where);
    	            break;
    	    }
	    } else {
	        $ret = query($my_query);
	    }
	    return $ret;
	}
	
	private function montaTabelaColunas($id)
	{
	    $ret = '';
	    
	    $tabela = new tabela01(['titulo' => 'Colunas', 'filtro' => false]);
	    
	    $tabela->addColuna(array('campo' => 'nome' , 'etiqueta' => 'Coluna', 'tipo' => 'T', 'width' =>  160, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'etiqueta' , 'etiqueta' => 'Etiqueta', 'tipo' => 'T', 'width' =>  160, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'tipo' , 'etiqueta' => 'Tipo', 'tipo' => 'T', 'width' =>  160, 'posicao' => 'C'));
	    $tabela->addColuna(array('campo' => 'filtro' , 'etiqueta' => 'É filtro?', 'tipo' => 'T', 'width' =>  160, 'posicao' => 'C'));
	    
	    $tabela->setDados($this->getInfoColunas($id));
	    
	    $param = [];
	    $param['onclick'] = "document.getElementById('valoresForm').submit()";
	    $param['texto'] = "Atualizar Colunas";
	   // $tabela->addBotaoTitulo($param);
	    
	   /*$param = [
	        'id' => 'valoresForm',
	        'nome' => 'valoresForm',
	        'acao' => getLink() . "salvar&colunas=true&id=$id",
	    ];
	 //   $ret = formbase01::form($param,$ret);
	    */
	    
	    $ret .= $tabela;
	    return $ret;
	}
	
	
	private function recuperaValorColunas($colunas,$valorcolunas)
	{
	    $ret = [];
	    
	    //$colunas = $this->recuperaColunasSelecionadas($dados);
	    if($valorcolunas != ''){
	        $valcol = explode(';',$valorcolunas);
	        //valcol = array([val 1a col][val 2a col])
	        
	        foreach($colunas as $coluna)
	        {
	            foreach($valcol as $col)
	            {
	                if($col!='')
	                {
	                    $temp = [];
	                    $col = explode(':',$col);
	                    if($col[0] == $coluna['nome'])
	                    {
	                        $temp['nome'] = $col[0];
	                        $temp['etiqueta'] = $col[1];
	                        $temp['tipo'] = $col[2];
	                        $temp['filtro'] = $col[3];
	                         
	                        $ret[] = $temp;
	                    }
	                }
	            }
	           
	        }
	    }
	    return $ret;
	}
	
	private function recuperaColunasSelecionadas($dados)
	{
	 $ret = [];
	 
	 $query = $dados['my_query'];
	 
	 $subq = explode('where', strtolower($query));
	 $subq = explode('from', $subq[0]);
	 $subq = str_replace('select','', $subq[0]);
	 $colunas = str_replace(' ', '', $subq);
	 
	 if($colunas == '*'){
	     $colunas = [];
	     $query = $dados['my_query'];
	     
	     $subq = explode('where', strtolower($query));
	     $subq = explode('order by',$subq[0]);
	     $subq = explode('limit',$subq[0]);
	     $subq = explode('from', $subq[0]);
	     $all_rows = query("SELECT * FROM ". $subq[1] . " LIMIT 1");
	     if(is_array($all_rows) && count($all_rows) > 0) {
	         foreach($all_rows[0] as $chave=>$valor)
	         {
	             if(!is_int($chave)){
	                 $colunas[] = $chave;
	             }
	         }
	     }
	 } else {
	     $colunas = explode(',',$colunas);
	     
	 }
	 
	 foreach($colunas as $id => $coluna){
	     $ret[$coluna]['nome'] = $coluna;
	     $ret[$coluna]['etiqueta'] = $coluna;
	     $ret[$coluna]['tipo'] = '';
	     $ret[$coluna]['filtro'] = 'N';
	 }
	 return $ret;
	}
	
	private function getInfoColunas($id)
	{
	    $ret = [];	    
	    $dados = $this->getDadosSingle($id);
	    
	    $lista = [
	        ['T','Texto'],
	        ['N','Número S Vírgula'],
	        ['V','Número C Vírgula'],
	        ['D','Data']
	    ];
	    
	    $yesno = [
	        ['N','Não'],
	        ['S','Sim']
	    ];
 
	    
        $colunas = $this->recuperaColunasSelecionadas($dados);
	    if($dados['tipocolunas'] != ''){
	        //var_dump($colunas);die();
	        $colunas = $this->recuperaValorColunas($colunas,$dados['tipocolunas'] );
	    }
	    
	    foreach($colunas as $coluna)
	        {
	            $temp = [];
	            
	            $temp['nome'] = $coluna['nome'];
	            
	            $param = [];
	            $param['id'] = "valoresForm[coluna][".$temp['nome']."][etiqueta]";
	            $param['nome'] = "valoresForm[coluna][".$temp['nome']."][etiqueta]";
	            $param['valor'] = $coluna['etiqueta'];
	            //$param['lista'] = $coluna['tipo'];
	            $param['obrigatorio']	= true;
	            $temp['etiqueta'] = formbase01::formTexto($param);
	            
	            $param = [];
	            $param['id'] = "valoresForm[coluna][".$temp['nome']."][tipo]";
	            $param['nome'] = "valoresForm[coluna][".$temp['nome']."][tipo]";
	            $param['valor'] = $coluna['tipo'];
	            $param['lista'] = $lista;
	            $param['obrigatorio']	= true;
	            $temp['tipo'] = formbase01::formSelect($param);
	            
	            $param = [];
	            $param['id'] = "valoresForm[coluna][".$temp['nome']."][filtro]";
	            $param['nome'] = "valoresForm[coluna][".$temp['nome']."][filtro]";
	            $param['valor'] = $coluna['filtro'];
	            $param['lista'] = $yesno;
	            $param['obrigatorio']	= true;
	            $temp['filtro'] = formbase01::formSelect($param);
	            
	            $ret[] = $temp;
	            
	        }
	    return $ret;
	    
	}
	
	
	private function getDadosSingle($id)
	{
	    $ret = [];
	    $rows = query("SELECT * FROM formbase WHERE id = $id");
	    if(is_array($rows) && count($rows)==1){
	        $ret = $rows[0];
	    }
	    return $ret;
	}
	
	private function atualizaPermissoes($excel, $pdf, $id)
	{
	    if($pdf != 0)
	    {
	        $rows = query("SELECT geraPDF FROM $this->_tabela WHERE id = $id");
	        $pdf = $rows[0]['geraPDF'];
	        if($pdf == 'N'){
	            query("UPDATE $this->_tabela SET geraPDF = 'S' WHERE id = $id");
	        } else {
	            query("UPDATE $this->_tabela SET geraPDF = 'N' WHERE id = $id");
	        }
	    }
	    if ($excel != 0)
	    {
	        $rows = query("SELECT geraExcel FROM $this->_tabela WHERE id = $id");
	        $excel = $rows[0]['geraExcel'];
	        if($excel == 'N'){
	            query("UPDATE $this->_tabela SET geraExcel = 'S' WHERE id = $id");
	        } else {
	            query("UPDATE $this->_tabela SET geraExcel = 'N' WHERE id = $id");
	        }
	    }
	}
	
	public function salvar()
	{
	    $id = getParam($_GET, 'id', 0);
	    
	    $excel = getParam($_GET, 'excel', 0);
	    $pdf = getParam($_GET, 'pdf', 0);
	    
	    $colunas = getParam($_GET, 'colunas', false);

	    $dados = getParam($_POST, 'valoresForm', []);
	    
	    $this->atualizaPermissoes($excel, $pdf, $id);
	    
	   // var_dump($dados);die();
	    
	    if(!empty($dados))
	    {
	        $dados['my_query'] = str_replace("'", "#", $dados['my_query']);
	        $dados['usuario'] = getUsuario();
	        
	        if($id==0)
	        {
	            //incluir
	            $sql = montaSQL($dados, $this->_tabela);
	            
	            if($this->getQuery($sql,$id,$dados['conexao'])!==false)
	            {
	                $sql = "SELECT id FROM formbase WHERE usuario = '".$dados['usuario']."'
                                AND descricao = '".$dados['descricao']."' AND my_query = '".$dados['my_query']."'
                                AND conexao = '".$dados['conexao']."' AND titulo = '".$dados['titulo']."'
                                 AND usuario = '".$dados['usuario']."'";
	                $rows = query($sql);
	                // var_dump($rows); die();
	                $id = $rows[0]['id'];
	                $this->atualizaPrograma($id, $dados['titulo']);
	            }
	        } else {
	            //editar
	            sys004::deletaTodos($this->_programa . "&id=$id");
	            $tipocolunas = '';
	            
	            if(is_array($dados['coluna']) && count($dados['coluna']) > 0)
	            {
	                $i = 1;
	                foreach($dados['coluna'] as $nome=>$conteudo){
	                    $tipo = $conteudo['tipo'];
	                    $etiqueta = $conteudo['etiqueta']=='' ? $nome : $conteudo['etiqueta'];
	                    $filtro = $conteudo['filtro'];
	                    $tipocolunas .= "$nome:$etiqueta:$tipo:$filtro;";
	                    if($filtro == 'S')
	                    {
	                        $fil = (array('programa' => $this->_programa . "&id=$id", 'emp' => '', 'fil' => '', 'ordem' => $i, 'pergunta' => $etiqueta		, 'variavel' => $nome	,'tipo' => $tipo, 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	                        
	                        sys004::inclui($fil);
	                        $dados['filtro'] = 'S';
	                        $i++;
	                    }
	                }
	            }
	            
	            if($tipocolunas != '')
	            {
	                $sql = "UPDATE $this->_tabela SET tipocolunas = '$tipocolunas' WHERE id = $id";
	                $this->getQuery($sql, $id);
	            }
	            
	            $sql = montaSQL($dados, $this->_tabela, 'UPDATE',"id = $id");
	            $this->getQuery($sql, $id);
	        }
	        
	    }
	    /*
	    if(!empty($dados)) 
	    {
	        if($colunas == 'true')
	        {
	            //Atualiza colunas
	            $tipocolunas = '';
	            sys004::deletaTodos($this->_programa . "&id=$id");
	            if(is_array($dados['coluna']) && count($dados['coluna']) > 0){
	                $i = 1;
	                foreach($dados['coluna'] as $nome=>$conteudo){
	                    $tipo = $conteudo['tipo'];
	                    $etiqueta = $conteudo['etiqueta']=='' ? $nome : $conteudo['etiqueta'];
	                    $filtro = $conteudo['filtro'];
	                    $tipocolunas .= "$nome:$etiqueta:$tipo:$filtro;";
	                    if($filtro == 'S')
	                    {
	                        $fil = (array('programa' => $this->_programa . "&id=$id", 'emp' => '', 'fil' => '', 'ordem' => $i, 'pergunta' => $etiqueta		, 'variavel' => $nome	,'tipo' => $tipo, 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
	                        
	                        sys004::inclui($fil);
	                        $temp['filtro'] = 'S';
	                        $i++;
	                    }
	                }
	            }
	            if($tipocolunas != ''){
	                $sql = "UPDATE $this->_tabela SET tipocolunas = '$tipocolunas' WHERE id = $id";
	                $this->getQuery($sql, $id);
	            }
	        } else {
    	        $temp = [];
    	        $temp['usuario'] = getUsuario();
        	    foreach($dados as $chave=>$valor){
        	        if($chave != 'coluna' && $chave != 'filtro'){
        	            $temp[$chave] = $valor;
        	        }
        	    }
        	    $temp['my_query'] = str_replace("'", "#", $temp['my_query']);
        	    
        	    if ($id == 0){
        	        //INSERT
        	        //var_dump($temp);die();
        	        $sql = montaSQL($temp, $this->_tabela);
        	        if($this->getQuery($sql,$id,$temp['conexao'])!==false){
        	            $sql = "SELECT id FROM formbase WHERE usuario = '".$temp['usuario']."' 
                                AND descricao = '".$temp['descricao']."' AND my_query = '".$temp['my_query']."'
                                AND conexao = '".$temp['conexao']."' AND titulo = '".$temp['titulo']."'";
        	            $rows = query($sql);
        	           // var_dump($rows); die();
        	           $id = $rows[0]['id'];
        	            $this->atualizaPrograma($id, $temp['titulo']);
        	        }
        	    } else {
        	            //Atualiza outros dados da query
        	            $sql = montaSQL($temp, $this->_tabela, 'UPDATE',"id = $id");
        	            $this->getQuery($sql, $id);
        	    }
    	    }
	    }
	    */
	    if($id!=0){
	        redireciona(getLink()."editar&id=$id");
	    }
	}
	
	private function getQuery($sql, $id, $conexao = 0)
	{
	    $ret = true;
	    if($conexao == 0)
	    {
	        $rows = query("SELECT conexao FROM $this->_tabela WHERE id = $id");
	        if(is_array($rows) && count($rows)>0){
	            $conexao = $rows[0]['conexao'];
	        }
	    }
	    switch ($conexao)
	    {
	        case 4:
	            $ret = query4($sql);
	            break;
	        case 1:
	        default:
	            $ret = query($sql);
	    }
	    return $ret;
	}

	private function atualizaPrograma($id, $nomeRel)
	{
	    //função para criar o programa na app002 e adicionar permissão do usuário (se não existe) 
	    $campos = [];
	    $campos['modulo'] 	= 'relatorio';
	    $campos['etiqueta'] = ($nomeRel == '') ? "Relatório $id" : "Relatório $nomeRel";
	    $campos['descricao']= 'Programa visualizador de relatórios do usuário atual';
	    $campos['ativo'] 	= 'S';
        $campos['seq'] 		=  1;
        $campos['programa'] = $this->_programa . "&id=$id";
        
        $rows = query("SELECT id FROM app001 WHERE nome = '".$campos['modulo']."'");
        if(empty($rows))
        {
            query("INSERT INTO app001 (nome, etiqueta, icone, descricao, nivel, ordem, programa, ativo) 
    VALUES ('relatorio','Meus Relatórios','','Aba dos relatórios do usuário',1,'5','','S')");
        }
	    
	    $rows = query("SELECT id FROM app002 WHERE programa = '$this->_programa'");
	    if(empty($rows))
	    {
	        $sql = montaSQL($campos, 'app002');
            query($sql);
	    }
	    $rows = query("SELECT id FROM sys115 WHERE programa = $this->_programa AND user = '".getUsuario()."'");
	    if(empty($rows))
	    {
	        $temp = array(
	            'user' => getUsuario(),
	            'programa' => $campos['programa'],
	            'perm' => 'S',
	        );
	        $sql = montaSQL($temp, 'sys115');
	        query($sql);
	    }
	}
	
	private function executaQuery($dados, $query = 1)
	{
	    $sql = $dados['my_query'];
	    
	    $sql = str_replace("#","'",$sql);
	    switch ($query)
	    {
	        case '4':
	            $rows = query4($sql);
	            break;
	        case '1':
	        default:
	            $rows = query($sql);
	    }
	    return $rows;
	}
	
	public function excluir()
	{
	    $id = getParam($_GET, 'id');
	    //Deletar dos programas (sys115)
	    query("DELETE FROM sys115 WHERE user = '".getUsuario()."' AND programa = '$this->_programa"."&id=$id"."'");
	    //Deletar da app002
	    query("DELETE FROM app002 WHERE modulo = 'relatorio' AND programa = '$this->_programa"."&id=$id"."'");
	    //Deletar da tabela
	    query("DELETE FROM $this->_tabela WHERE id = $id");
	    return $this->index();
	}
	
}