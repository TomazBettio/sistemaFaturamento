<?php
class usuarios extends cad01{
    function __construct(){
        $param = array();
        parent::__construct('sys001', $param);
        
        $temp = '
			function marcarTodos(modulo, marcado){
				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
			}
			';
        addPortaljavaScript($temp);
    }
    
    public function salvar($id = 0, $dados = array(), $acao = '', $redireciona = true){
        $ret = '';
        if($this->verificarSenhas()){
            if(count($dados) == 0){
                $dados = $_POST["formCRUD"] ?? [];
            }
            if(count($dados) == 0){
                die('Erro no formulário');
            }
            $dados['senha'] = login::criptografarSenha($dados['senha']);
            $ret = parent::salvar($id, $dados, $acao, false);
            $acao = $acao != '' ? $acao : getParam($_GET, 'acao', 'D');
            if($acao == 'E'){
                $id = $_GET['id'];
                $entrada = $this->getEntrada($id);
                $this->salvarAcessos($entrada['user']);
                $this->salvarAcoes($entrada['user']);
            }
            redireciona();
        }
        else{
            addPortalMensagem("As senhas informadas são diferentes", 'error');
            $acao = !empty($acao) ? $acao : $_GET['acao'];
            if($acao == 'E'){
                $id = $_GET['id'];
                $ret = $this->editar($id);
            }
            elseif ($acao == 'I'){
                $dados = $_POST['formCRUD'];
                $ret = $this->incluir($dados);
            }
        }
        return $ret;
    }
    
    private function salvarAcessos($user){
        $sql = "delete from sys115 where user = '$user'";
        query($sql);
        if(isset($_POST['formUserAcessos']) && count($_POST['formUserAcessos']) > 0){
            $chaves = array_keys($_POST['formUserAcessos']);
            $dados_incluir = array();
            foreach ($chaves as $programa){
                $temp = array(
                    'null',
                    "'$user'",
                    "'" . str_replace("__", '.', $programa) . "'",
                    "'S'",
                );
                
                //'formUserAcessos['.str_replace(".","__",$row["programa"]).']';
                
                $dados_incluir[] = '(' . implode(', ', $temp) . ')';
            }
            
            if(is_array($dados_incluir) && count($dados_incluir) > 0){
                $sql = "insert into sys115 values " . implode(', ', $dados_incluir);
                query($sql);
            }
        }
    }
    
    private function salvarAcoes($user){
        if(isset($_POST['formAcoes'])){
            $dados = $_POST['formAcoes'];
            $sql = "select item, tipo from sys015";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $sql = "delete from sys016 where usuario = '$user'";
                query($sql);
                $dados_inserir = array();
                foreach ($rows as $row){
                    $item = $row['item'];
                    $tipo = $row['tipo'];
                    if(isset($dados[$item])){
                        if($tipo == 'CB'){
                            $dados_inserir[] = "(null, '$item', '$user', 'S', null)";
                        }
                        else{
                            $dados_inserir[] = "(null, '$item', '$user', '{$dados[$item]}', null)";
                        }
                    }
                }
                if(is_array($dados_inserir) && count($dados_inserir) > 0){
                    $sql = "insert into sys016 values " . implode(', ', $dados_inserir);
                    query($sql);
                }
            }
        }
    }
    
    private function verificarSenhas(){
        $ret = false;
        if(!isset($_POST["formCRUD"]['senha'])){
            $ret = true;
        }
        else{
            if(isset($_POST["formCRUD"]['senha']) && isset($_POST["formCRUD"]['user']) && !empty($_POST["formCRUD"]['senha']) && !empty($_POST["formCRUD"]['user'])){
                if($_POST["formCRUD"]['senha'] == $this->recuperarSenhaUsuario($_POST["formCRUD"]['user'])){
                    $ret = true;
                }
                elseif(isset($_POST["formCRUD"]['senha2']) && !empty($_POST["formCRUD"]['senha2']) && $_POST["formCRUD"]['senha'] == $_POST["formCRUD"]['senha2']){
                    $ret = true;
                }
            }
        }
        return $ret;
    }
    
    private function recuperarSenhaUsuario($user){
        $ret = '';
        $sql = "select senha from sys001 where user = '$user'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['senha'];
        }
        return $ret;
    }
    
    public function montarFormulario($dados = [], $acao = 'I', $indice_extra = ''){
        $form = parent::montarFormulario($dados, $acao, $indice_extra);
        //die('p3');
        if($acao == 'E'){
            $entrada = $this->getEntrada($_GET['id']);
            $form->addConteudoPastas(4, $this->montaFormPermissoes($entrada['user']));
            $form->addConteudoPastas(6, $this->montarFormAcoes($entrada['user'], $entrada['tipo']));
        }
        return $form;
    }
    
    private function montarFormAcoes($user, $tipo){
        $ret = '';
        $dados = $this->getDadosFormAcoes($user, $tipo);
        foreach ($dados as $sys014){
            $form = new form01();
            foreach ($sys014['itens'] as $sys015){
                $form->addCampo($sys015);
            }
            $param = array(
                'titulo' => $sys014['etiqueta'],
                'conteudo' => $form . '',
            );
            $ret .= addCard($param);
        }
        return $ret;
    }
    
    private function getDadosFormAcoes($user, $tipo){
        $ret = array();
        $sql = "select distinct sys014.grupo, sys014.etiqueta from sys014 join sys015 on sys014.grupo = sys015.grupo where sys015.tipo_usuario = '' or sys015.tipo_usuario is null or sys015.tipo_usuario like '%$tipo%'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $itens = $this->getItensAcoes($row['grupo'], $user, $tipo);
                if(is_array($itens) && count($itens) > 0){
                    $temp = array(
                        'etiqueta' => $row['etiqueta'],
                        'grupo' => $row['grupo'],
                        'itens' => $itens,
                    );
                    $ret[] = $temp;
                }
            }
        }
        return $ret;
    }
    
    private function getItensAcoes($grupo, $user, $tipo){
        $ret = array();
        $sql = "select sys015.*, sys016.valor from sys015 left join sys016 on sys015.item = sys016.item and sys016.usuario = '$user' where (sys015.tipo_usuario = '' or sys015.tipo_usuario is null or sys015.tipo_usuario like '%$tipo%') and sys015.grupo = '$grupo'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'campo' => "formAcoes" . '[' . $row['item'] . ']',
                    'etiqueta' => $row['etiqueta'],
                    'mascara' => $row['mascara'],
                    'tipo' => $row['tipo'],
                    'tamanho' => $row['tamanho'],
                    'valor' => $row['valor'],
                    'funcao_lista' => $row['funcao_lista'],
                    'opcoes' => $row['opcoes'],
                    'validacao' => $row['validacao'],
                    'obrigatorio' => $row['obrigatorio'] === 'S',
                    'help' => $row['help'],
                    'largura' => $row['largura'],
                    'tabela_itens' => $row['tabela_itens'],
                    'estilo' => $row['estilo_form'],
                    'classeadd' => $row['class_form'],
                );

                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function montaFormPermissoes($usuario){
        $ret = '';
        $dados = $this->getDadosFormPermissoes($usuario);
        if(is_array($dados) && count($dados) > 0){
            foreach ($dados as $dado){
                $temp = $this->montaComboBoxProgramas($usuario, $dado['modulo']);
                if($temp != ''){
                    $param = [];
                    $checked = $dado['marcado'] == 'S' ? 'checked="checked"' : '';
                    $param['titulo'] 	= '<label><input type="checkbox"  onclick="marcarTodos(\''.$dado["modulo"].'\',this.checked);"  name="formUserModulos['.$dado["modulo"].']" id="" '.$checked.'/>&nbsp;'.$dado["etiqueta"].'</label>';
                    $param['conteudo']	= $temp;
                    $ret .= addCard($param);
                }
            }
        }
        return $ret;
    }
    
    private function getDadosFormPermissoes($usuario){
        $ret = array();
        $sql = "select app001.nome, app001.etiqueta, case when totalizador_u is not null and totalizador_u = totalizador then 'S' else 'N' end as marcado from app001 left join 
(select app001.nome, count(*) as totalizador from app001 join app002 on (app001.nome = app002.modulo and app002.ativo = 'S') group by app001.nome) 
as total_programas on (app001.nome = total_programas.nome)
left join (select app001.nome, count(*) as totalizador_u from app001 join app002 on (app001.nome = app002.modulo and app002.ativo = 'S') join sys115 on (app002.programa = sys115.programa and sys115.perm = 'S' and sys115.user = '$usuario') group by app001.nome) as total_usuario on (app001.nome = total_usuario.nome)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'modulo' => $row['nome'],
                    'etiqueta' => $row['etiqueta'],
                    'marcado' => $row['marcado'],
                    'programas' => $this->getProgramasUsuarios($usuario, $row['nome'])
                );
                
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function montaComboBoxProgramas($usuario, $modulo){
        $ret = '';
        
        $dados	= $this->getProgramasUsuarios($usuario, $modulo);
        if(is_array($dados) && count($dados) > 0){
            $param = [];
            $param['colunas'] = 3;
            $param['combos']  = $dados;
            $ret = formbase01::formGrupoCheckBox($param);
        }
        
        return $ret;
    }
    
    function getProgramasUsuarios($usuario, $modulo){
        $ret = [];
        if(!empty($modulo)){
            $sql  = "SELECT app002.programa, app002.etiqueta, sys115.perm, app002.modulo FROM app002 ";
            $sql .= "LEFT JOIN sys115 ON (app002.programa = sys115.programa AND sys115.user = '$usuario' and sys115.perm = 'S') ";
            $sql .= "WHERE app002.ativo = 'S'";
            $sql .= " AND app002.modulo = '$modulo' ";
            
            $rows = query($sql);
            
            if(count($rows) > 0){
                foreach ($rows as $row){
                    $temp = [];
                    
                    $temp["nome"] 		= 'formUserAcessos['.str_replace(".","__",$row["programa"]).']';
                    $temp["etiqueta"] 	= $row["etiqueta"];
                    $temp["checked"] 	= $row["perm"] == "S" ? true : false;
                    $temp["classeadd"]	= $row["modulo"];
                    
                    $ret[] = $temp;
                }
            }
        }
        return $ret;
    }
}

function getListaProgramasIniciais(){
    $ret = array(array('', ''));
    $sql = "select app002.programa as valor, concat(app001.etiqueta, ' - ', app002.etiqueta) as etiqueta from app001 join app002 on 
(app001.nome = app002.modulo) where app002.ativo = 'S'";
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

function gerarCampoConfirmarSenha(){
    $ret = '';
    $form = new form01();
    $param = array(
        'campo' => "formCRUD" . '[senha2]',
        'etiqueta' => "Confirmar Senha",
        'pasta' => 3,
        'tipo' => 'S',
        'tamanho' => 20,
        'linhas' => '',
        'obrigatorio' => false,
        'largura' => 3,
    );
    $form->addCampo($param);
    $ret .= $form;
    //$ret = formbase01::formTexto($param);
    //$ret = '<div class="row"><div class="col-md-3">'.$ret.'</div></div>';
    //return $ret;
    return $ret;
}