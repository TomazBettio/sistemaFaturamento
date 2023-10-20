<?php
/*
 * Data Criação: 02/08/2023
 * Autor: BCS
 *
 * Novo Centralizador de Ações
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class centralizador_acoes {
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'        => true,
        'salvar'        => true,
        'excluir'       => true,
        
        'ajax'          => true
    );
    
    //MINHA TABELA: adm_acoes
    
    private $_campos;
    
    public function __construct()
    {
        $this->_campos = [
            'data','departamento','responsavel','acao','status','prazo',
            'finalizado','obs','cliente','privado','percen_exec',
        ];
        $programa = get_class($this);
       /* $param = [
            'programa' => $programa,
            'titulo' => 'Minhas Ações',
        ];
        $this->_relatorio = new relatorio01($param);
        */
        sys004::inclui(array('programa' => $programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Responsável'	, 'variavel' => 'responsavel' ,'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'centralizador_acoes::getListaUsuarios()'                    , 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        sys004::inclui(array('programa' => $programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Departamento', 'variavel' => 'departamento','tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'tabela("DEPTO", "")' , 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        sys004::inclui(array('programa' => $programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Status'	    , 'variavel' => 'status'      ,'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'tabela("STATUS", "")', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        sys004::inclui(array('programa' => $programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Prazo INI'   , 'variavel' => 'prazo_min'   ,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''                    , 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        sys004::inclui(array('programa' => $programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Prazo FIM'	, 'variavel' => 'prazo_max'   ,'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''                    , 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        
        
        //JAVASCRIPT
        $script = '
                function marcarTodos(modulo, marcado){
                				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
                			}
                ';
        addPortaljavaScript($script);
    }
    
    public function index()
    {
        $ret = '';
        
        $tab = new tabela01(['titulo' => '', 'scroll' => true]);
        
        //Colunas
        $param = ['campo' => 'data','etiqueta' => 'DATA','tipo' => 'D','width' =>  160,'posicao' => 'E'];
        $tab->addColuna($param);
        
        $param = ['campo' => 'departamento','etiqueta' => 'Depto','tipo' => 'T','width' =>  160,'posicao' => 'E'];
        $tab->addColuna($param);
        
        $param = ['campo' => 'acao','etiqueta' => 'Ação','tipo' => 'T','width' =>  160,'posicao' => 'E'];
        $tab->addColuna($param);
        
        $param = ['campo' => 'prazo','etiqueta' => 'Prazo','tipo' => 'D','width' =>  160,'posicao' => 'E'];
        $tab->addColuna($param);
        
        $param = ['campo' => 'finalizado','etiqueta' => 'Finalizada Em','tipo' => 'D','width' =>  160,'posicao' => 'E'];
        $tab->addColuna($param);
        
        $param = ['campo' => 'status','etiqueta' => 'Status','tipo' => 'T','width' =>  160,'posicao' => 'E'];
        $tab->addColuna($param);
        
        $param = ['campo' => 'responsavel','etiqueta' => 'Responsável','tipo' => 'T','width' =>  160,'posicao' => 'E'];
        $tab->addColuna($param);
        
        //Botões Tabela
        $param = array(
            'texto' => 'Editar',
            'link' => getLink() . 'editar&id=',
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'success',
        );
        $tab->addAcao($param);
        
        $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir essa ação?"');
        
        $param = array(
            'texto' => 'Excluir',
            'link' => "javascript:confirmaExclusao('" . getLink()."excluir&id=','{ID}')",
            'coluna' => 'id',
            'width' => 10,
            'flag' => '',
            //'tamanho' => 'pequeno',
            'cor' => 'danger',
        );
        $tab->addAcao($param);
        
        
        //Botões Título
        $param = [];
        
        $p = [
            'onclick' => "setLocation('" . getLink() . "editar&id=0')",
            'texto' => 'Incluir',
            'cor' => 'success'
        ];
       // $param['botoesTitulo'][] = $p;
        
        //Rotina01 pra filtro
        $param = [
            'filtro' => true,
            'titulo' => 'Ações',
            'programa' => get_class($this),
            'botoesTitulo' => [$p]
            ];
        $roti = new rotina01($param);
        
        $filtro = $roti->getFiltro();
        
        $dados = $this->getAcoes($filtro);
        if(count($dados) > 0){
            $roti->escondeFiltro();
        }
        $tab->setDados($dados);
        
        $roti->setConteudo($tab);
        
        $ret .= $roti;
        return $ret;
    }
    
    private function getAcoes($filtro)
    {
        $ret = [];
        //var_dump($filtro);die();
        
        $acoes = $this->acoesEnvolvido(getUsuario());
        
        $sql = "SELECT * FROM adm_acoes WHERE ativo = 'S'";// AND responsavel = '".getUsuario()."'";
        
        if(isset($filtro['responsavel']) && $filtro['responsavel'] != ''){
            $sql .= " AND responsavel='{$filtro['responsavel']}' ";
        }
        if(isset($filtro['departamento']) && $filtro['departamento'] != ''){
            $sql .= " AND departamento='{$filtro['departamento']}' ";
        }
        if(isset($filtro['status']) && $filtro['status'] != ''){
            $sql .= " AND status='{$filtro['status']}' ";
        }
        if(isset($filtro['prazo_min']) && $filtro['prazo_min'] != ''){
            $sql .= " AND data>='{$filtro['prazo_min']}' ";
        }
        if(isset($filtro['prazo_max']) && $filtro['prazo_max'] != ''){
            $sql .= " AND data <='{$filtro['prazo_max']}' ";
        }
        
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach($rows as $row)
            {
                if(in_array($row['id'], $acoes))
                {
                    foreach($this->_campos as $campo){
                        $temp[$campo] = $row[$campo];
                        
                        if($campo == 'departamento'){
                            $temp[$campo] = $this->nomeDepartamento($temp[$campo]);
                        }
                        if($campo == 'status'){
                            $temp[$campo] = $this->nomeStatus($temp[$campo]);
                        }
                    }
                    $temp['id'] = $row['id'];
                    $ret[]=$temp;
                }
            }
        }
        return $ret;
    }
    
    private function acoesEnvolvido ($envolvido)
    {
        $ret = [];
        $sql = "SELECT id_acao FROM adm_responsavel WHERE resp = '$envolvido'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $ret[] = $row['id_acao'];
            }
        }
        return $ret;
    }
    
    public function editar()
    {
        $ret = '';
        $id = getParam($_GET, 'id');
        $dados = $this->getDadosAcao($id);
        
        $form = new form01();
        $form->addCampo(array('id' => '', 'campo' => "formAcao[acao]"        , 'etiqueta' => 'Ação'         , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['acao']	        , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[departamento]", 'etiqueta' => 'Departamento' , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['departamento']	, 'pasta'	=> 0, 'lista' => $this->valoresSYS5('DEPTO')	     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[responsavel]" , 'etiqueta' => 'Responsável'  , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['responsavel'] 	, 'pasta'	=> 0, 'lista' => $this->getListaUsuarios()	         , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[status]"      , 'etiqueta' => 'Status'       , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['status']	    , 'pasta'	=> 0, 'lista' => $this->valoresSYS5('STATUS')	     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[prazo]"       , 'etiqueta' => 'Prazo'        , 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['prazo'] 	    , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[finalizado]"  , 'etiqueta' => 'Finalizado em', 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['finalizado'] 	, 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false, ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[cliente]"     , 'etiqueta' => 'Cliente'      , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['cliente']       , 'pasta'	=> 0, 'lista' => funcoes_cad::getListaClientes()	 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[privado]"     , 'etiqueta' => 'Privado?'     , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['privado']       , 'pasta'	=> 0, 'lista' => $this->valoresSYS5('000003')	     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[obs]"         , 'etiqueta' => 'Obs'          , 'tipo' => 'TA' 	, 'tamanho' => '215', 'linhas' => '', 'valor' => $dados['obs']	        , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false ,));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[percen_exec]" , 'etiqueta' => '% Executado'  , 'tipo' => 'N' 	, 'tamanho' => '215', 'linhas' => '', 'valor' => $dados['percen_exec']	, 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false ,));
        
        //$form->setEnvio(getLink() . "salvar&id=$id", 'formAcao', 'formAcao');
        
        $envolvidos = $this->montaFormResponsaveis($dados['lista']);
        
        //$ret .= addCard(['conteudo'=>$form . $envolvidos,'titulo'=>'Editar Ação']);
        
        $ret .= $form . '<br>' . $envolvidos;
        
        $param = [
            'URLcancelar' => getLink().'index',
            'IDform' => 'formAcao',
        ];
        formbase01::formSendFooter($param);
        
        $param = [
            'id' => 'formAcao',
            'acao' => getLink()."salvar&id=$id",
        ];
        $ret = formbase01::form($param, $ret);
        
        $param = [
            'conteudo' => $ret,
            'titulo' => 'Informações da Ação',
        ];
        $ret = addCard($param);
        
        return $ret;
    }
    
    private function montaFormResponsaveis($lista, $readonly = false)
    {
        $ret = '';
        
        $type = 'TODOS';
        $descricao = "Marcar Todos";
        $checkbox = [];
        $lista_usuarios = [];
        
        $sql = "SELECT user, nome FROM sys001 WHERE ativo = 'S' ORDER BY nome";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach ($rows as $row)
            {
                $temp['user'] = $row['user'];
                $temp['nome'] = $row['nome'];
                $temp['tipo'] = $type;
                $lista_usuarios[] = $temp;
            }
        }
        
        foreach($lista_usuarios as $useless=>$user)
        {
            $temp = [];
            $temp["nome"] = "formAcao[users][{$user['user']}]";
            $temp['etiqueta'] = $user['nome'];
            $temp["modulo"] 	= $user['tipo'];
            $temp["classeadd"] 	= $user['tipo'];
            $temp["checked"]    = in_array($user['user'], $lista) ? true : false;
            $temp['ativo'] = !$readonly;
            $checkbox[$user['tipo']][] = $temp;
        }
        
        $impressao = $readonly ? 'disabled="disabled"' : '';
        
        if(isset($checkbox[$type])){
            $param = [];
            $param['colunas'] 	= 3;
            $param['combos']	= $checkbox[$type];
            $formCombo = formbase01::formGrupoCheckBox($param);
            $param = [];
            $param['titulo'] = "<label><input type='checkbox'  onclick='marcarTodos(\"$type\",this.checked);' $impressao name='[$type]' id='".$descricao."_id'  >&nbsp;&nbsp;$descricao</label>";
            $param['conteudo'] = $formCombo;
            
            //$ret .= $param['titulo'] . $param['conteudo'];
            $ret .= addCard($param);
           // $ret = addCard(['conteudo' => $ret, 'titulo' => 'Permissão de Visualização']);
        }
        
        return $ret;
    }
    
    private function getDadosAcao($id)
    {
        $ret = [];
        $sql = "SELECT * FROM adm_acoes WHERE id = $id";
        $rows = query($sql);
        foreach($this->_campos as $campo){
            if(is_array($rows) && count($rows)==1){
                $ret[$campo] = $rows[0][$campo];
            } else {
                $ret[$campo] = '';
            }
        }
        
        $ret['lista'] = $this->getEnvolvidosAcao($id);
        return $ret;
    }
    
    private function getEnvolvidosAcao($id_acao)
    {
        $ret = [];
        $sql = "SELECT resp FROM adm_responsavel WHERE id_acao = $id_acao";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $ret[] = $row['resp'];
            }
            //$ret[] = $rows;
        }
      //  print_r($ret);
        return $ret;
    }
    
    private function salvarEnvolvidos($usuarios, $id_acao)
    {
        //Nomes que estão nessa atualização
        $novos = [];
        foreach($usuarios as $user=>$useless){
            $novos[] = $user;
        }
        
        //Nomes que estavam na última atualização
        $sql = "SELECT resp FROM adm_responsavel WHERE id_acao = $id_acao";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $antigos = [];
            foreach($rows as $row){
                $antigos[] = $row['resp'];
            }
            //Usuários que estão no antigo mas não nos novos serão apagados
            $apagados = array_diff($antigos, $novos);
            //Usuários que estão nos novos mas não nos antigos serão inseridos
            $inseridos = array_diff($novos,$antigos);
        }
        
        foreach($apagados as $apaga){
            $sql = "DELETE FROM adm_responsavel WHERE id_acao = $id_acao AND resp = '$apaga'";
            query($sql);
        }
        
        foreach($inseridos as $insere){
            $sql = "INSERT INTO adm_responsavel (id_acao, resp) VALUES ($id_acao, '$insere')";
            query($sql);
        }

        /*
        foreach($nomes as $nom)
        {
            //TESTA SE O NOME JÁ ESTÁ NA TABELA, CASO SIM NÃO INSERE
            $sql = "SELECT * FROM adm_responsavel WHERE id_acao = $id_acao AND resp = '$nom'";
            $rows = query($sql);
            if(!(is_array($rows) && count($rows)>0))
            {
                $sql = "INSERT INTO adm_responsavel (id_acao, resp) VALUES ($id_acao,'$nom')";
                query($sql);
            }
        }
        */
    }
    
    public function salvar()
    {
        //var_dump($_POST);die();
        
        $id = getParam($_GET, 'id');
        $dados = getParam($_POST, 'formAcao');
        $usuarios = [];
        
        $dados['prazo'] = datas::dataD2S($dados['prazo']);
        if(isset($dados['finalizado'])){
            $dados['finalizado'] = datas::dataD2S($dados['finalizado']);
        }
        if(isset($dados['users'])){
            $usuarios = $dados['users'];
            unset($dados['users']);
        }
        if($id==0)
        {
            //Nova ação
            $dados_acao['data'] = date('Ymd');
            $sql = montaSQL($dados_acao, 'adm_acoes');
            $id_acao = query($sql);
            if(!empty($usuarios)){
                $this->salvarEnvolvidos($usuarios, $id_acao);
            }
        } else {
            //atualiza ação
            $sql = montaSQL($dados, 'adm_acoes', 'update', "id = $id");
            query($sql);
            if(!empty($usuarios)){
                $this->salvarEnvolvidos($usuarios, $id);
            }
        }
        redireciona(getLink()."index");
    }
    
    public function excluir()
    {
        $id = getParam($_GET, 'id');
        $sql = "UPDATE adm_acoes SET ativo = 'N' WHERE id = $id";
        query($sql);
        redireciona(getLink()."index");
    }
    
    private function valoresSYS5($tabela)
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT chave, descricao from sys005 where tabela = '$tabela' and ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['chave'];
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    static function getListaUsuarios()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("select user, nome from sys001 where ativo = 'S' ORDER BY user");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['user'];
                $temp[1] = $row['nome'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function nomeDepartamento($dept)
    {
        $ret = $dept;
        switch ($ret){
            case 'BI':
                $ret = "Business Intel";
                break;
            case 'DEV':
                $ret = "Desenvolvimento";
                break;
            case 'ERP':
                $ret = "Planejamento";
                break;
            case 'RPA':
                $ret = "Automacao de Processos";
                break;
            default:
                break;
        }
        return $ret;
    }
    
    private function nomeStatus($sta)
    {
        $ret = $sta;
        switch ($ret){
            case 'CONC':
                $ret = "Concluído";
                break;
            case 'PRAZO':
                $ret = "No Prazo";
                break;
            case 'CANCEL':
                $ret = "Cancelado";
                break;
            case 'ATRASA':
                $ret = "Atrasado";
                break;
            case 'AGUARD':
                $ret = "Aguardando";
                break;
            default:
                break;
        }
        return $ret;
    }
    
    private function jsConfirmaExclusao($titulo){
        $ret = "
            function confirmaExclusao(link,id){
            	if (confirm('$titulo')){
            		setLocation(link+id);
            	}
            }
        ";
        addPortaljavaScript($ret);
    }
    
}