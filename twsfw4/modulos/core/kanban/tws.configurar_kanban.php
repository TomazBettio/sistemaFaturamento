<?php
class configurar_kanban{
    var $funcoes_publicas = array(
        'index'			=> true,
        'editarBoard'   => true,
        'editarPermissoes' => true,
        'salvarPermissoes' => true,
        'criarColuna'       => true,
        'gerenciarColunas' => true,
        'salvarNovaColuna' => true,
        'parametros' => true,
        'salvarParametros' => true,
        'ajax' => true,
    );
    
    public function index(){
        $ret = $this->montarBotoesBoards();
        return $ret;
    }
    
    public function ajax(){
        $ret = kanban_lite::ajax();
        return $ret;
    }
    
    public function editarBoard(){
        $id = $_GET['id'] ?? '';
        $ret = '';
        if(!empty($id)){
            $ret = $this->montarBotoesEdicaoBoard($id);
        }
        else{
            $ret = 'Nenhuma board informada';
        }
        return $ret;
    }
    
    public function editarPermissoes(){
        addPortaljavaScript("
function habilitarByClasse(id_select, classe, numero_tab){
    var collection = document.getElementsByClassName(classe);
    var valor = document.getElementById(id_select).value;
    var desabilitar = (valor != 'caso');
    var id_tab = '" . formbase01::gerarIdTab('') . "' + numero_tab + '-tab';
    if(collection.length > 0){
        for (let i = 0; i < collection.length; i++){
            collection[i].disabled = desabilitar;
        }
        var elemento_tab = document.getElementById(id_tab);
        if(desabilitar){
            elemento_tab.classList.add('disabled');
        }
        else{
            elemento_tab.classList.remove('disabled');
        }
    }
}
");
        $ret = '';
        $id = $_GET['id'];
        $permissoes = [
            ['codigo' => 'editarCards', 'etiqueta' => 'Editar Cards'],
            ['codigo' => 'configurarCards', 'etiqueta' => 'Configurar Cards'],
            ['codigo' => 'moverCards', 'etiqueta' => 'Mover Cards'], //falta  usar
            ['codigo' => 'detalhesCards', 'etiqueta' => 'Ver Detalhes do Cards'], //falta  usar
            ['codigo' => 'criarCards', 'etiqueta' => 'Criar Cards'],
            
            
            ['codigo' => 'configurarColuna', 'etiqueta' => 'Configurar Colunas'],
        ];
        $dados = $this->getPermissoesGerais($id);
        $opcoes_tipos = array(
            ['livre', 'Livre'],
            ['proibido', 'Proibido'],
            ['caso', 'Caso a Caso'],
        );
        
        $i = 0;
        foreach ($permissoes as $permissao){
            //$ret .= formbase01::formCheck(['etiqueta' => $permissao['etiqueta'], 'id' => 'cb' . $permissao['codigo'], 'nome' => "formPermissao[{$permissao['codigo']}][base]"]);
            $ret .= formbase01::formSelect(['onchange' => "habilitarByClasse(this.id, '{$permissao['codigo']}', $i)", 'valor' => $dados[$permissao['codigo']] ?? 'livre', 'lista' => $opcoes_tipos,'etiqueta' => $permissao['etiqueta'], 'id' => 'tipoPermissao' . $permissao['codigo'], 'nome' => "formPermissao[tipo][{$permissao['codigo']}]"]);
            $i++;
        }
        
        $tabs = [];
        $tabs_desativadas = [];
        $i = 0;
        foreach ($permissoes as $permissao){
            $habilitar = ($dados[$permissao['codigo']] ?? 'livre') === 'caso';
            $nova_tab = ['titulo' => $permissao['etiqueta'], 'conteudo' => $this->montarCbPermissao($permissao['codigo'], $habilitar)];
            $tabs[] = $nova_tab;
            if(!$habilitar){
                $tabs_desativadas[] = $i;
            }
            $i++;
        }
        $ret .= formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tabs, 'desativado' => $tabs_desativadas));
        
        $ret = formbase01::form(['id' => 'idFormulario', 'acao' => getLink() . 'salvarPermissoes&id=' . $id], $ret);
        
        $param = [];
        $param['URLcancelar'] = getLink() . 'editarBoard&id=' . $id;
        $param['IDform'] = 'idFormulario';
        formbase01::formSendFooter($param);
                
        $ret = addCard(['titulo' => 'Configurar Permissões - ' . $this->getCampoBoard($id, 'etiqueta'), 'conteudo' => $ret]);
        return $ret;
    }
    
    public function salvarPermissoes(){
        $id = $_GET['id'] ?? '';
        if(!empty($id)){
            $dados = $_POST['formPermissao'];
            $permissoes_gerais = $dados['tipo'];
            $permissoes_caso = $dados['caso'];
            $this->salvarPermissoesGerais($id, $permissoes_gerais);
            $this->salvarPermissoesEspecificas($id, $permissoes_caso, $permissoes_gerais);
            redireciona(getLink() . 'editarBoard&id=' . $id);
        }
        redireciona(getLink() . 'index');
    }
    
    public function gerenciarColunas(){
        $ret = '';
        $id = $_GET['id'];
        $kanban = new kanban_lite($id, ['add' => false, 'imprimirTarefas' => false, 'contador' => false, 'totalizador' => false]);
        $ret .= $kanban;
        $bt = [
            'texto' => 'Voltar',
            'cor' => 'danger',
            'tipo' => 'link',
            'url' => getLink() . 'editarBoard&id=' . $id,
        ];
        $ret = addCard(['titulo' => 'Configurar Colunas - ' . $this->getCampoBoard($id, 'etiqueta'), 'conteudo' => $ret, 'botoesTitulo' => [$bt]]);
        return $ret;
    }
    
    public function criarColuna(){
        $ret = '';
        $id = $_GET['id'];
        $form = new form01();
        $form->addCampo(array('id' => '', 'campo' => 'formNovaColuna[etiqueta]'		, 'etiqueta' => 'Etiqueta'			, 'linha' => 1, 'largura' =>4, 'tipo' => 'T'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'lista' => '', 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->setBotaoCancela(getLink() . 'editarBoard&id=' . $id);
        $form->setEnvio(getLink() . 'salvarNovaColuna&id=' . $id, 'formNovaColuna', 'formNovaColuna');
        $ret .= $form;
        $ret = addCard(['conteudo' => $ret, 'titulo' => 'Criar Coluna - ' . $this->getCampoBoard($id, 'etiqueta')]);
        return $ret;
    }
    
    public function salvarNovaColuna(){
        $id = $_GET['id'];
        $etiqueta = $_POST['formNovaColuna']['etiqueta'];
        $ordem = $this->getOrdemNovaEtapa($id);
        $sql = "insert into kl_etapas values (null, $id, '$etiqueta', '$ordem', '')";
        query($sql);
        redireciona(getLink() . 'editarBoard&id=' . $id);
    }
    
    public function parametros(){
        $ret = '';
        $id = $_GET['id'];
        $dados = $this->getDadosBoard($id);
        $form = new form01();
        
        $cores = [
            ['primary' , 'Azul'],
            ['secondary' , 'Cinza'],
            ['success' , 'Verde'],
            ['danger' , 'Vermelho'],
            ['warning' , 'Amarelo'],
            ['info' , 'Turquesa'],
            ['light' , 'Branco'],
            ['dark' , 'Preto'],
        ];
        
        $form->setPastas(['Geral', 'Indicadores']);
        
        $form->addCampo(array('id' => '', 'campo' => 'formParam[etiqueta]'		, 'etiqueta' => 'Etiqueta'			                   , 'linha' => 1, 'largura' =>4, 'tipo' => 'T'	, 'tamanho' => '60', 'pasta' => 0, 'linhas' => '', 'valor' => $dados['etiqueta']		, 'lista' => '', 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => '', 'campo' => 'formParam[totalizador]'	, 'etiqueta' => 'Exibir o score total das tarefas'	   , 'linha' => 1, 'largura' =>6, 'tipo' => 'A'	, 'tamanho' => '60', 'pasta' => 1, 'linhas' => '', 'valor' => $dados['totalizador']		, 'lista' => [['N', 'Não'],['S', 'Sim']], 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => '', 'campo' => 'formParam[totalizadorCor]', 'etiqueta' => 'Cor do Score Total'	               , 'linha' => 1, 'largura' =>6, 'tipo' => 'A'	, 'tamanho' => '60', 'pasta' => 1, 'linhas' => '', 'valor' => $dados['totalizadorCor']		, 'lista' => $cores, 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => '', 'campo' => 'formParam[contador]'		, 'etiqueta' => 'Exibir o número de tarefas'		   , 'linha' => 1, 'largura' =>6, 'tipo' => 'A'	, 'tamanho' => '60', 'pasta' => 1, 'linhas' => '', 'valor' => $dados['contador']		, 'lista' => [['N', 'Não'],['S', 'Sim']], 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => '', 'campo' => 'formParam[contadorCor]'	, 'etiqueta' => 'Cor do número de tarefas'	           , 'linha' => 1, 'largura' =>6, 'tipo' => 'A'	, 'tamanho' => '60', 'pasta' => 1, 'linhas' => '', 'valor' => $dados['contadorCor']		, 'lista' => $cores, 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => '', 'campo' => 'formParam[score]'	        , 'etiqueta' => 'Exibir o score de cada card'	       , 'linha' => 1, 'largura' =>6, 'tipo' => 'A'	, 'tamanho' => '60', 'pasta' => 1, 'linhas' => '', 'valor' => $dados['score']		, 'lista' => [['N', 'Não'],['S', 'Sim']], 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        $form->addCampo(array('id' => '', 'campo' => 'formParam[scoreCor]'	    , 'etiqueta' => 'Cor do score de cada card'	           , 'linha' => 1, 'largura' =>6, 'tipo' => 'A'	, 'tamanho' => '60', 'pasta' => 1, 'linhas' => '', 'valor' => $dados['scoreCor']		, 'lista' => $cores, 'funcao_lista' => ""       , 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100));
        
        $form->setBotaoCancela(getLink() . 'editarBoard&id=' . $id);
        $form->setEnvio(getLink() . 'salvarParametros&id=' . $id, 'formParam', 'formParam');
        $ret .= $form;
        $ret = addCard(['conteudo' => $ret, 'titulo' => 'Parâmetros da Board - ' . $this->getCampoBoard($id, 'etiqueta')]);
        return $ret;
    }
    
    public function salvarParametros(){
        $id = $_GET['id'];
        $dados = $_POST['formParam'];
        $sql = montaSQL($dados, 'kl_boards', 'UPDATE', "id = $id");
        query($sql);
        redireciona(getLink() . 'editarBoard&id=' . $id);
    }
    
    private function getDadosBoard($id){
        $ret = [];
        $sql = "select * from kl_boards where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['etiqueta', 'totalizador', 'totalizadorCor', 'contador', 'contadorCor', 'score', 'scoreCor'];
            foreach ($campos as $c){
                $ret[$c] = $rows[0][$c];
            }
        }
        if(empty($ret['totalizadorCor'])){
            $ret['totalizadorCor'] = 'warning';
        }
        if(empty($ret['contadorCor'])){
            $ret['contadorCor'] = 'success';
        }
        if(empty($ret['scoreCor'])){
            $ret['scoreCor'] = 'warning';
        }
        return $ret;
    }
    
    private function getOrdemNovaEtapa($id){
        $ret = 1;
        $sql = "select coalesce(max(ordem), 0) as total from kl_etapas where board = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret += $rows[0]['total'];
        }
        return $ret;
    }
    
    private function salvarPermissoesGerais($id, $dados){
        $sql = "delete from kl_permissoes_geral where board = $id";
        query($sql);
        $dados_inserir = [];
        foreach ($dados as $chave => $valor){
            $dados_inserir[] = "($id, '$chave', '$valor')";
        }
        if(count($dados_inserir) > 0){
            $sql = "insert into kl_permissoes_geral values " . implode(', ', $dados_inserir);
            query($sql);
        }
    }
    
    private function salvarPermissoesEspecificas($id, $dados, $geral){
        $dados_inserir = [];
        $sql = "delete from kl_permissoes_especificas where board = $id";
        query($sql);
        foreach ($dados as $codigo => $usuarios_raw){
            if($geral[$codigo] == 'caso'){
                $usuarios = array_keys($usuarios_raw);
                foreach ($usuarios as $usuario){
                    $dados_inserir[] = "($id, '$codigo', '$usuario')";
                }
            }
        }
        if(count($dados_inserir) > 0){
            $sql = "insert into kl_permissoes_especificas values " . implode(', ', $dados_inserir);
            query($sql);
        }
    }
    
    private function getPermissoesGerais($id){
        $ret = [];
        $sql = "select * from kl_permissoes_geral where board = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['operacao']] = $row['valor'];
            }
        }
        return $ret;
    }
    
    private function montarCbPermissao($codigo, $habilitar){
        $ret = '';
        $dados	= $this->getPermissoesByCodigo($codigo, $habilitar);
        if(is_array($dados) && count($dados) > 0){
            $param = [];
            $param['colunas'] = 3;
            $param['combos']  = $dados;
            $ret = formbase01::formGrupoCheckBox($param);
        }
        return $ret;
    }
    
    private function getPermissoesByCodigo($codigo, $habilitar){
        $ret = [];
        $sql = "select sys001.apelido, sys001.user, case when board is not null then 'S' else 'N' end as marcado  from sys001 left join kl_permissoes_especificas on (sys001.user = kl_permissoes_especificas.usuario and operacao = '$codigo') order by sys001.id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = [];
                $temp["nome"] 		= "formPermissao[caso][$codigo][{$row['user']}]";
                $temp["etiqueta"] 	= $row['apelido'];
                $temp["checked"] 	= $row["marcado"] == "S" ? true : false;
                $temp["classeadd"]	= $codigo;
                $temp['ativo'] = $habilitar;
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getCampoBoard($id, $campo){
        $ret = '';
        $sql = "select $campo from kl_boards where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0][$campo];
        }
        return $ret;
    }
    
    private function montarBotoesEdicaoBoard($id){
        $ret = '';
        $opcoes = [
            ['etiqueta' => 'Parâmetros da Board', 'programa' => 'parametros'],
            ['etiqueta' => 'Permissões'  , 'programa' => 'editarPermissoes'],
            ['etiqueta' => 'Criar Nova Coluna', 'programa' => 'criarColuna'],
            ['etiqueta' => 'Gerenciar Colunas', 'programa' => 'gerenciarColunas'],
            
        ];
        while(count($opcoes) > 0){
            $conteudo = [];
            while(count($conteudo) < 2){
                if(count($opcoes) > 0){
                    $dado = array_shift($opcoes);
                    $bt = [
                        'texto' => $dado['etiqueta'],
                        'tipo' => 'link',
                        'url' => getLink() . $dado['programa'] . "&id=$id",
                        'bloco' => true,
                    ];
                    $conteudo[] = formbase01::formBotao($bt);
                }
                else{
                    $conteudo[] = '';
                }
            }
            $ret .= addLinha(['tamanhos' => [6, 6], 'conteudos' => $conteudo]);
            $ret .= '<br>';
        }
        $bt_voltar = [
            'texto' => 'Voltar',
            'cor' => 'danger',
            'tipo' => 'link',
            'url' => getLink() . 'index',
        ];
        $ret = addCard(['titulo' => 'Editar Board - ' . $this->getCampoBoard($id, 'etiqueta'), 'conteudo' => $ret, 'botoesTitulo' => [$bt_voltar]]);
        return $ret;
    }
    
    private function getBoards(){
        $ret = [];
        $sql = "select * from kl_boards";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = ['etiqueta' => $row['etiqueta'], 'id' => $row['id']];
            }
        }
        return $ret;
    }
    
    private function montarBotoesBoards(){
        $ret = '';
        $boards = $this->getBoards();
        if(count($boards) > 0){
            while(count($boards) > 0){
                $conteudo = [];
                while(count($conteudo) < 3){
                    if(count($boards) > 0){
                        $dado = array_shift($boards);
                        $bt = [
                            'texto' => $dado['etiqueta'],
                            'tipo' => 'link',
                            'url' => getLink() . 'editarBoard&id=' . $dado['id'],
                            'bloco' => true,
                        ];
                        $conteudo[] = formbase01::formBotao($bt);
                    }
                    else{
                        $conteudo[] = '';
                    }
                }
                $ret .= addLinha(['tamanhos' => [4, 4, 4], 'conteudos' => $conteudo]);
            }
            $ret = addCard(['titulo' => 'Editar Boards', 'conteudo' => $ret]);
        }
        else{
            $ret = 'Nenhuma board encontrada';
        }
        return $ret;
    }
}