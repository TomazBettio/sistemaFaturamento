<?php
/*
 * Data Criação: 13/09/2023
 * Autor: BCS
 *
 * Descricao: 	tela para abrir requisições
 *              redireciona para lista_requisicao
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class requisicao{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'salvar'        => true,
    );
    
    
    public function __construct(){
    }
    
    public function index()
    {
        $ret = '';
        global $config;
        
        $cancelar = $config['raiz'].'index.php?menu='.getModulo().'.lista_requisicao.index';
        $form = new form01(['geraScriptValidacaoObrigatorios' => true,'cancelar' => $cancelar]);
        $form->addCampo(array('id' => '', 'campo' => "formIndex[cadeia]"      , 'etiqueta' => 'Cadeia'        , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => '', 'pasta'	=> 0, 'lista' => $this->getCadeias(), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true ));
        $form->addCampo(array('id' => '', 'campo' => "formIndex[papel]"       , 'etiqueta' => 'Papel'         , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => '', 'pasta'	=> 0, 'lista' => $this->getPapeis(), 'validacao' => '', 'largura' => 4, 'obrigatorio' => true ));
        
        $form->setEnvio(getLink() . "salvar", 'formIndex', 'formIndex');
        
        $ret .= addCard(['titulo'=>'Criar Requisição', 'conteudo'=>$form]);
        return $ret;
    }
    
    private function getCadeias()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '0';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, descricao FROM pmp_cadeia WHERE ativo = 'S' ORDER BY descricao");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = base64_encode($row['id']);
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function getPapeis()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '0';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = query("SELECT id, descricao FROM pmp_papel WHERE ativo = 'S' ORDER BY descricao");
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = base64_encode($row['id']);
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    
    
    public function salvar()
    {
        global $config;
        $redireciona = true;
        $dados = getParam($_POST, 'formIndex',[]);
        if(isset($dados['cadeia']) && isset($dados['papel']))
        {
            $cadeia = base64_decode($dados['cadeia']);
            $papel = base64_decode($dados['papel']);
            $parametros = $this->getParamCert($cadeia,$papel);
            $usuario = $this->getIdUsuarioColab(getUsuario());
            
            if(!$this->temPapelAprovador($cadeia,$papel,$usuario))
            {
                if(!$this->temRequisicao($cadeia,$papel,$usuario))
                {
                    if($parametros != [])
                    {
                        /**
                         * //depois de selecionar cadeia-papel
                         * salva uma requisicao pmp_requisicao
                         * 
                         * salva na tabela item_requisicao
                         * uma linha por id de param_certificação que houver para o par cadeia/papel
                         * dt_modificado, validador, mensagem e valor VAZIOS
                         * status para ambos requisição e item 'A' Aberto
                         */
                        $requisicao = $this->salvaRequisicao($usuario, $cadeia, $papel);
                        if($requisicao != 0){
                            foreach($parametros as $param){
                                $documento = $param['id'];
                                $sql = "INSERT INTO pmp_item_requisicao (requisicao,documento,valor,status,mensagem) 
                                        VALUES ($requisicao,$documento,'','A','')";
                                $id_param = query($sql);
                                if($id_param !== false){
                                    gravarAtualizacao('pmp_item_requisicao', $id_param, 'I');
                                }
                            }
                            addPortalMensagem('Requisição aberta com sucesso','success');
                        } else {
                            addPortalMensagem('Erro ao salvar a requisição','error');
                            $redireciona = false;
                        }
                    } else {
                        addPortalMensagem('Não existem parâmetros para esse par cadeia/papel','error');
                        $redireciona = false;
                    }
                } else {
                    addPortalMensagem('Você já possui uma requisição semelhante em aberto','error');
                    $redireciona = false;
                }
            } else {
                addPortalMensagem('Você já está registrado para esse papel/cadeia','error');
                $redireciona = false;
            }
        } else {
            addPortalMensagem('Cadeia/Papel inválidos','error');
            $redireciona = false;
        }
        
        if(!$redireciona){
            redireciona();
        } else {
            redireciona($config['raiz'].'index.php?menu='.getModulo().'.lista_requisicao.index');
        }
    }
    
    private function salvaRequisicao($usuario,$cadeia,$papel)
    {
        $ret = 0;
        
        $data = date('Ymd');
        
        $sql = "INSERT INTO pmp_requisicao (colaborador,cadeia,papel,data_ini,status) 
                VALUES ($usuario,$cadeia,$papel,'$data','A')";
        //echo $sql;die();
        $id = query($sql);
        if($id !== false){
            $ret = $id;
            gravarAtualizacao('pmp_requisicao', $ret, 'I');
        }
        return $ret;
    }
    
    private function temRequisicao($cadeia,$papel,$usuario)
    {
        $sql = "SELECT * FROM pmp_requisicao
                WHERE papel = $papel AND cadeia = $cadeia AND colaborador = $usuario AND ativo = 'S' AND status = 'A'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            return true;
        }
        return false;
    }
    
    private function temPapelAprovador($cadeia,$papel,$usuario)
    {
        $sql = "SELECT * FROM pmp_colab_papel
                WHERE papel = $papel AND cadeia = $cadeia AND colaborador = $usuario AND ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            return true;
        }
        return false;
    }
    
    private function getIdUsuarioColab($user)
    {
        $ret = '';
        $sql = "SELECT id FROM pmp_colaborador WHERE user = '$user' AND ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['id'];
        }
        return $ret;
    }
    
    private function getParamCert($cadeia,$papel)
    {
        $ret = [];
        $sql = "SELECT id FROM pmp_param_certificacao 
                WHERE cadeia = $cadeia AND papel = $papel AND ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach($rows as $row){
                $temp['id'] = $row['id'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
}