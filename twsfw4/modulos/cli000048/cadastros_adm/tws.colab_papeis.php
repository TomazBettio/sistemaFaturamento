<?php
/*
 * Data Criação: 11/09/2023
 * Autor: BCS, Emanuel
 *
 * Descricao: 	Papéis de Colaborador (amarra cadeias, papéis e colaboradores)
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
//incluir 3 selects: papel cadeia e colaborador
//usar tabela02 - filtro papel, cadeia e colaborador
class colab_papeis{
    //tabela: pmp_colab_papel
    var $funcoes_publicas = array(
        'ajax'          => true,
        'index' 		=> true,
        'incluir'       => true,
        'salvar'        => true,
        'excluir'       => true,
    );
    
    private $_programa = 'pmp_colab_papel';
    
    function __construct(){
        if(false){
            //função dados: static
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Cadeia'       , 'variavel' => 'cadeia', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'colab_papeis::getCadeiasForm()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Papel'        , 'variavel' => 'papel' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'colab_papeis::getPapeisForm()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
            sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Colaborador'  , 'variavel' => 'colab' , 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => 'colab_papeis::getColabsForm()'	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '' ]);
        }
        $this->jsAtualizaLista();
    }
    
    public function index()
    {
        $ret = '';
        
        $param = [
            'programa' => $this->_programa,
            'titulo' => 'Papéis Colaboradores',
            'mostraFiltro' => true,
            'filtroTipo' => 1
        ];
        $tab = new tabela02($param);
        $tab->addColuna(array('campo' => 'cadeia'      , 'etiqueta' => 'Cadeia'        , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        $tab->addColuna(array('campo' => 'papel'       , 'etiqueta' => 'Papel'         , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        $tab->addColuna(array('campo' => 'colaborador' , 'etiqueta' => 'Colaborador'   , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro'));
        
        // Botão Excluir
        $this->jsConfirmaExclusao('"Confirme a exclusão do parâmetro"');
        $param = [];
        $param['texto'] = 'Excluir';
        $param['link'] 	= "javascript:confirmaExclusao('" . getLink() ."excluir&id=" . "','{ID}')";
        $param['coluna']= 'id';
        $param['width'] = 10;
        $param['cor'] 	= 'danger';
        $tab->addAcao($param);
        
        $p = [];
        $p['onclick'] = "setLocation('".getLink()."incluir')";
        $p['texto'] = 'Incluir';
        $p['cor'] = 'success';
        $tab->addBotaoTitulo($p);
        
        
        
        $filtro = $tab->getFiltro();
        if(!$tab->getPrimeira()){
            $dados = $this->getDadosTab($filtro);
            $tab->setDados($dados);
        }
        
        $ret .= $tab;
        return $ret;
    }
    
    public function ajax()
    {
        //javascript para se selecionar quaisquer 2,
        //atualiza a lista do 3o p/ só os disponíveis
        //(ou seja, aqueles q ainda não tem combinação única)
        $cadeia = getParam($_GET, 'cadeia', '');
        $cadeia = $cadeia == ''? '' : base64_decode($cadeia);
        $papel = getParam($_GET, 'papel', '');
        $papel = $papel == ''? '' : base64_decode($papel);
        $colab = getParam($_GET, 'colab', '');
        $colab = $colab == ''? '' : base64_decode($colab);
        
        
        $ret = '';
        
        $op = getOperacao();
        if(in_array($op, ['colab', 'cadeia', 'papel'])){
            $ret = json_encode($this->getListaOpcoes($op, $cadeia, $papel, $colab, true));
        }
        return $ret;
    }
    
    private function jsAtualizaLista()
    {
        
        /*
         -ao selecionar 2 campos, a lista de opções do ultimo campo seja renovada para respeitar as opção selecionadas no outros 2
         -caso 3 campos estejam selecionados e um desses campos seja passado para vazio: os outros 2 campos devem ter a sua lista resetada para a lista base e a lista do campo em questão criada novamente baseada nos outros 2 campos
         -caso 2 campos estejam selecionados e um desses campos seja passado para vazio: resetar a lista do terceiro campo
         */
        $ret = "
            
    function changePapel(){
        var valor = document.getElementById('formColabPapelpapel').value;
        var option = '';
        papel = document.getElementById('formColabPapelpapel').value;
        cadeia = document.getElementById('formColabPapelcadeia').value;
        colab = document.getElementById('formColabPapelcolaborador').value;
        $.getJSON('" . getLinkAjax('papel') . "&colab=' + colab + '&cadeia=' + cadeia , function (dados){
                	            if (dados.length > 0){
                	                $.each(dados, function(i, obj){
                                        if(obj.valor == valor){
                                            option += '<option value=" . '"' . "'+obj.valor+'" . '"' . " selected>'+obj.etiqueta+'</option>';
                                        }
                                        else{
                                            option += '<option value=" . '"' . "'+obj.valor+'" . '"' . ">'+obj.etiqueta+'</option>';
                                        }
                	                    $('#formColabPapelpapel').html(option).show();
                	                });
                	            }
                	        });
        //document.getElementById('formColabPapelpapel').value = valor;
    }
                                                
    function changeCadeia(){
        var valor = document.getElementById('formColabPapelcadeia').value;
        var option = '';
        papel = document.getElementById('formColabPapelpapel').value;
        cadeia = document.getElementById('formColabPapelcadeia').value;
        colab = document.getElementById('formColabPapelcolaborador').value;
        $.getJSON('" . getLinkAjax('cadeia') . "&papel=' + papel + '&colab=' + colab , function (dados){
                	            if (dados.length > 0){
                	                $.each(dados, function(i, obj){
                	                    if(obj.valor == valor){
                                            option += '<option value=" . '"' . "'+obj.valor+'" . '"' . " selected>'+obj.etiqueta+'</option>';
                                        }
                                        else{
                                            option += '<option value=" . '"' . "'+obj.valor+'" . '"' . ">'+obj.etiqueta+'</option>';
                                        }
                	                    $('#formColabPapelcadeia').html(option).show();
                	                });
                	            }
                	        });
        //document.getElementById('formColabPapelcadeia').value = valor;
                                                
                                                
    }
                                                
                                                
    function changeColab(){
        var valor = document.getElementById('formColabPapelcolaborador').value;
        var option = '';
        papel = document.getElementById('formColabPapelpapel').value;
        cadeia = document.getElementById('formColabPapelcadeia').value;
        colab = document.getElementById('formColabPapelcolaborador').value;
        $.getJSON('" . getLinkAjax('colab') . "&papel=' + papel + '&cadeia=' + cadeia , function (dados){
                	            if (dados.length > 0){
                	                $.each(dados, function(i, obj){
                	                    if(obj.valor == valor){
                                            option += '<option value=" . '"' . "'+obj.valor+'" . '"' . " selected>'+obj.etiqueta+'</option>';
                                        }
                                        else{
                                            option += '<option value=" . '"' . "'+obj.valor+'" . '"' . ">'+obj.etiqueta+'</option>';
                                        }
                	                    $('#formColabPapelcolaborador').html(option).show();
                	                });
                	            }
                	        });
        //document.getElementById('formColabPapelcolaborador').value = valor;
    }
                                                
    function atualizarListas(){
        changePapel();
        changeCadeia();
        changeColab();
}
                                                
                                                
                                                
    ";
        addPortaljavaScript($ret);
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
    
    private function getDadosTab($filtro)
    {
        $ret = [];
        $where = "";
        if(isset($filtro['cadeia']) && $filtro['cadeia'] != 0){
            $where .= " AND pmp_cadeia.id = {$filtro['cadeia']} ";
        }
        if(isset($filtro['colaborador']) && $filtro['colaborador'] != 0){
            $where .= " AND pmp_colaborador.id = {$filtro['colaborador']} ";
        }
        if(isset($filtro['papel']) && $filtro['papel'] != 0){
            $where .= " AND pmp_papel.id = {$filtro['papel']} ";
        }
        $sql = "
                SELECT
                        t1.id AS id,
                        pmp_cadeia.descricao AS cadeia,
                        pmp_papel.descricao AS papel,
                        pmp_colaborador.user AS colaborador
                FROM (  (   pmp_colab_papel AS t1
                            JOIN pmp_cadeia ON t1.cadeia = pmp_cadeia.id    )
                        JOIN pmp_papel ON t1.papel = pmp_papel.id   )
                    JOIN pmp_colaborador ON t1.colaborador = pmp_colaborador.id
                    
                WHERE
                    t1.ativo = 'S'
                    AND pmp_cadeia.ativo = 'S'
                    AND pmp_colaborador.ativo = 'S'
                    AND pmp_papel.ativo='S'
                    $where
                ";
                    $rows = query($sql);
                    if(is_array($rows) && count($rows)>0)
                    {
                        $temp = [];
                        $campos = ['id','colaborador','papel','cadeia'];
                        foreach($rows as $row){
                            foreach($campos as $cam){
                                $temp[$cam] = $row[$cam];
                            }
                            $temp['id'] = base64_encode($temp['id']);
                            $temp['colaborador'] = getUsuario('nome',$temp['colaborador']);
                            $ret[] = $temp;
                        }
                    }
                    return $ret;
    }
    
    public function incluir()
    {
        $ret = '';
        
        $form = new form01();
        $form->addCampo(array('id' => '', 'campo' => "formColabPapel[cadeia]"      , 'etiqueta' => 'Cadeia'        , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => '', 'pasta'	=> 0, 'lista' => $this->getListaOpcoes('cadeia'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'onchange' => 'atualizarListas();'));
        $form->addCampo(array('id' => '', 'campo' => "formColabPapel[papel]"       , 'etiqueta' => 'Papel'         , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => '', 'pasta'	=> 0, 'lista' => $this->getListaOpcoes('papel'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'onchange' => 'atualizarListas();'));
        $form->addCampo(array('id' => '', 'campo' => "formColabPapel[colaborador]" , 'etiqueta' => 'Colaborador'   , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => '', 'pasta'	=> 0, 'lista' => $this->getListaOpcoes('colab'), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'onchange' => 'atualizarListas();'));
        
        $form->setEnvio(getLink() . "salvar", 'formColabPapel', 'formColabPapel');
        
        $ret .= addCard(['titulo'=>'Novo Papel Colaborador', 'conteudo'=>$form]);
        return $ret;
    }
    
    static function getCadeiasForm()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '0';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, descricao FROM pmp_cadeia WHERE ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['id'];
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    static function getPapeisForm()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '0';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, descricao FROM pmp_papel WHERE ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['id'];
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    static function getColabsForm()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '0';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, user FROM pmp_colaborador WHERE ativo = 'S'");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['id'];
                $temp[1] = getUsuario('nome',$row['user']);
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function getListaOpcoes($lista, $cadeia = '', $papel = '', $colab = '', $ajax = false){
        $ret = [];
        $sql = "";
        switch ($lista){
            case 'cadeia':
                $sql .= "SELECT id as valor, descricao as etiqueta FROM pmp_cadeia WHERE ativo = 'S'";
                break;
            case 'papel':
                $sql .= "SELECT id as valor, descricao as etiqueta FROM pmp_papel WHERE ativo = 'S'";
                break;
            case 'colab':
                $sql .= "SELECT pmp_colaborador.id as valor, nome as etiqueta FROM pmp_colaborador JOIN sys001 USING (user) WHERE pmp_colaborador.ativo = 'S'";
                break;
            default :
                break;
        }
        $where = [];
        if($lista == 'colab' && !empty($cadeia) && !empty($papel)){
            $where[] = "id not in (select colaborador from pmp_colab_papel where papel = $papel and cadeia = $cadeia)";
        }
        elseif($lista == 'cadeia' && !empty($colab) && !empty($papel)){
            $where[] = "id not in (select cadeia from pmp_colab_papel where papel = $papel and colaborador = $colab)";
        }
        elseif($lista == 'papel' && !empty($cadeia) && !empty($colab)){
            $where[] = "id not in (select papel from pmp_colab_papel where colaborador = $colab and cadeia = $cadeia)";
        }
        if(count($where) > 0){
            $sql .= " and " . implode(' and ', $where);
        }
        LOG::gravaLog('SQL_LISTAS', $sql);
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            if($ajax){
                $ret[] = ['valor' => '', 'etiqueta' => ''];
                foreach ($rows as $row){
                    $ret[] = [
                        'valor' => base64_encode($row['valor']),
                        'etiqueta' => $row['etiqueta'],
                    ];
                }
            }
            else{
                $ret[] = ['', ''];
                foreach ($rows as $row){
                    $ret[] = [
                        0 => base64_encode($row['valor']),
                        1 => $row['etiqueta'],
                    ];
                }
            }
        }
        return $ret;
    }
    
    
    public function salvar()
    {
        $dados = getParam($_POST, 'formColabPapel', []);
        
        $dados['colaborador'] = base64_decode($dados['colaborador']);
        $dados['papel'] = base64_decode($dados['papel']);
        $dados['cadeia'] = base64_decode($dados['cadeia']);
        
        if($this->existePapelColab($dados)){
            addPortalMensagem("Cadastro já existe",'error');
        } else {
            //incluir
            $sql = montaSQL($dados, 'pmp_colab_papel');
            $ultimo_id = query($sql);
            if($ultimo_id !== false){
                gravarAtualizacao('pmp_colab_papel', $ultimo_id, 'I');
                //deletar todos os itens relacionados àquele usuário
                $usuario = $dados['colaborador'];
                $sql = "UPDATE pmp_requisicao SET ativo = 'N', user_atualiza = '".getUsuario()."', data_atualiza = CURRENT_TIMESTAMP() WHERE colaborador = $usuario";
                query($sql);
            } else {
                addPortalMensagem("Erro ao salvar no banco de dados",'error');
            }
        }
        
        redireciona();
    }
    
    private function existePapelColab($dados)
    {
        $ret = false;
        
        $sql = "SELECT * FROM pmp_colab_papel
                WHERE colaborador = {$dados['colaborador']}
                AND papel = {$dados['papel']}
                AND cadeia = {$dados['cadeia']}";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = true;
        }
        return $ret;
    }
    
    public function excluir()
    {
        $id = getParam($_GET, 'id', 0);
        $id = base64_decode($id);
        $dados = ['ativo'=>'N'];
        $sql = montaSQL($dados, 'pmp_colab_papel', 'UPDATE', "id = $id");
        $ultimo_id = query($sql);
        if($ultimo_id !== false){
            gravarAtualizacao('pmp_colab_papel', $id, 'E');
        } else {
            addPortalMensagem("Erro ao salvar no banco de dados",'error');
        }
        redireciona();
    }
}