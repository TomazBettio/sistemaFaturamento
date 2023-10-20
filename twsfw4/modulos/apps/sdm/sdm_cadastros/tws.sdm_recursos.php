<?php
/*
 * Data Criacao: 06/09/2023
 * Autor: BCS
 *
 * Descricao: Novo Recursos (não estende cad01)
 *
 * Alteracoes;
 *
 */
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class sdm_recursos{
    
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar' 		=> true,
        'excluir' 		=> true,
        'salvar'        => true,
    );
    
    function __construct(){
    }
    
    function index(){
        $ret = '';
        
        $tab = new tabela01(['titulo' => 'Recursos']);
        $tab->addColuna(array('campo' => 'nome'     , 'etiqueta' => 'Nome'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'usuario'  , 'etiqueta' => 'Usuário'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'tipo'     , 'etiqueta' => 'Tipo'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'equipe'   , 'etiqueta' => 'Equipe'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'agenda'   , 'etiqueta' => 'Agenda'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'ativo'    , 'etiqueta' => 'Ativo'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        //$tab->addColuna(array('campo' => 'del'      , 'etiqueta' => 'Del'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        
        $param=([
            'id' => 'incluir',
            'onclick' => "setLocation('".getlink()."editar&id=0')",
            'texto' => 'Novo'
        ]);
        $tab->addBotaoTitulo($param);
        
        
        $param=([
            'texto' =>  'Editar',
            'link' 	=> getLink()."editar&id=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'success',
        ]);
        $tab->addAcao($param);
        
        $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir esse recurso?"');
        $param=([
            'texto' =>  'Excluir',
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&id=','{ID}')",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'danger',
        ]);
        $tab->addAcao($param);
        
        $dados = $this->getRecursos();
        $tab->setDados($dados);
        
        $ret .= $tab;
        return $ret;
    }
    
    private function getRecursos()
    {
        $ret = [];
        $campos = ['id','nome','apelido','tipo','usuario','agenda','equipe','hora_dia','semana','ativo','del'];
        $sql = "SELECT * FROM sdm_recursos WHERE del = ''";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            foreach($rows as $row){
                foreach($campos as $campo){
                    $temp[$campo] = $row[$campo];
                }
                $temp['ativo'] = $temp['ativo'] == 'S' ? 'Sim' : 'Não';
                $temp['agenda'] = $temp['agenda'] == 'S' ? 'Sim' : 'Não';
                $temp['tipo'] = $temp['tipo'] == 'A' ? 'Analista' : ($temp['tipo'] == 'R' ? 'Recurso' : '');
                
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    function editar(){
        $ret = '';
        $id = getParam($_GET, 'id');
        $dados = [
            'nome' => '',
            'apelido' => '',
            'tipo' => '',
            'usuario' => '',
            'agenda' => '',
            'hora_dia' => '',
            'semana' => '',
            'equipe' => ''
        ];
        if($id != 0){
            $dados = $this->getDadosRecurso($id);
        }
        
        $opcoes = 'S=Sim;N=Não';
        $tipo = "A=Analista;R=Recurso";
        
        $form = new form01([]);
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[nome]"    , 'etiqueta' => 'Nome'          , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['nome']	    , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[apelido]" , 'etiqueta' => 'Apelido'       , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['apelido']	, 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[tipo]"    , 'etiqueta' => 'Tipo'          , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['tipo']	    , 'pasta'	=> 0, 'lista' => '', 'opcoes' => $tipo               , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[usuario]" , 'etiqueta' => 'Usuário'       , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['usuario']	, 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[agenda]"  , 'etiqueta' => 'Agenda'        , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['agenda']   , 'pasta'	=> 0, 'lista' => '', 'opcoes' => $opcoes             , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        //grupo-equipe = tabela DEPTO sys005
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[equipe]", 'etiqueta' => 'Equipe' , 'tipo' => 'A' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['equipe'] , 'pasta'	=> 0, 'lista' => $this->listaEquipes()	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false , ));
        
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[hora_dia]", 'etiqueta' => 'Horas por Dia' , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['hora_dia'] , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false , ));
        $form->addCampo(array('id' => '', 'campo' => "formRecurso[semana]"  , 'etiqueta' => 'Dias da Semana', 'tipo' => 'DS' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['semana']   , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , ));
        
        $form->setEnvio(getLink()."salvar&id=$id", 'formRecurso', 'formRecurso');
        
        $param = [];
        $param['conteudo'] = $form;
        $param['titulo'] = $id == 0 ? "Novo Recurso" : "Atualizar Recurso";
        $ret = addCard($param);
        return $ret;
    }
    
    private function listaEquipes()
    {
        $ret = [];
        $sql = "SELECT chave, descricao FROM sys005 WHERE tabela = 'DEPTO' AND ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            foreach($rows as $row){
                $temp[0] = $row['chave'];
                $temp[1] = $row['descricao'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getDadosRecurso($id)
    {
        $ret = [];
        $campos = ['id','nome','apelido','tipo','usuario','agenda','equipe','hora_dia','semana','ativo','del'];
        $sql = "SELECT * FROM sdm_recursos WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            foreach($campos as $campo){
                $temp[$campo] = $rows[0][$campo];
            }
            $ret = $temp;
        }
        return $ret;
    }
    
    function salvar(){
        $id = getParam($_GET, 'id');
        $dados = getParam($_POST, 'formRecurso');
        //var_dump($dados);die();
        $voltar = false;
        
        //obrigatorios: nome,apelido,tipo,usuario,agenda,
        $obrigatorios = [ 'nome','apelido','tipo','usuario','agenda',];
        foreach($obrigatorios as $obrig){
            if(!(isset($dados[$obrig]) && $dados[$obrig] != '')){
                addPortalMensagem("Preencha o campo $obrig",'error');
                $voltar = true;
            }
        }
        if(!$voltar){
            $diasSemana = getParam($_POST, 'diasSemana');
            
            $dados['semana'] = '';
            foreach($diasSemana as $dia=>$useless){
                $dados['semana'].=$dia;
            }
            
            if(isset($dados['hora_dia']) && $dados['hora_dia'] != ''){
                $temp = explode(':',$dados['hora_dia']);
                if(!isset($temp[1]) || $temp[1] == ''){
                    $dados['hora_dia'] = $temp[0] . ":00";
                }
            } else {
                $dados['hora_dia'] = '6:00';
            }
            
            if($id == 0){ //salva novo
                $sql = montaSQL($dados, 'sdm_recursos');
            } else { //atualiza
                $sql = montaSQL($dados, 'sdm_recursos','UPDATE',"id=$id");
            }
            query($sql);
           redireciona();
        } else {
            redireciona(getLink()."editar&id=$id");
        }
        
    }
    
    function excluir(){
        $id = getParam($_GET, 'id');
        $sql = "UPDATE sdm_recursos SET ativo = 'N', del = '*' WHERE id = $id";
        query($sql);
        redireciona();
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