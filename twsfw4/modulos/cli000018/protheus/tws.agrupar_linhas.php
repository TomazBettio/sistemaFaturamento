<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class agrupar_linhas{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'salvar'        => true,
    );
    
    private $_rest;
    
    public function __construct(){
        $this->_rest = new rest_protheus('http://192.168.1.125:8888/rest', 'admin', 'q1w2e3');
    }
    
    public function index(){
        $ret = '';
        $linhas = $this->getListaLinhas();
        $relacoes = $this->getListaRelacoes();
        foreach ($linhas as $codigo => $nome){
            $form = new form01();
            $form->addCampo(array('tipo' => 'A', 'campo' => "formLinhas[$codigo]", 'tabela_itens' => 'BSGRLI', 'valor' => $relacoes[$codigo] ?? ''));
            $ret .= addLinha(array('tamanhos' => array(8, 4), 'conteudos' => array($nome, $form . '')));
        }
        $ret = '<form id="formLinhasTeste" action="' . getLink() . 'salvar" method="post">' . $ret . '</form>';
        $param = [];
        //$param['texto'] 	= traducoes::traduzirTextoDireto('Incluir');
        $param['texto'] 	= 'Salvar';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 		= "document.getElementById('formLinhasTeste').submit();";
        $param['cor'] 		= 'success';
        
        $ret = addCard(array('titulo' => 'Agrupar Linhas', 'conteudo' => $ret, 'botoesTitulo' => array($param)));
        $ret = '<div class="row"><div class="col-lg-3"></div><div class="col-lg-6">' . $ret . '</div><div class="col-lg-3"></div></div>';
        return $ret;
    }
    
    private function getListaLinhas(){
        $ret = array();
        $sql = "select * from bs_linhas";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['codigo']] = $row['nome'];
            }
        }
        return $ret;
    }
    
    private function getListaRelacoes(){
        $ret = array();
        $sql = "select * from bs_linha_grupo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['linha']] = $row['grupo'];
            }
        }
        return $ret;
    }
    
    public function salvar(){
        $dados = $_POST['formLinhas'];
        if(is_array($dados) && count($dados) > 0){
            $sql = "truncate bs_linha_grupo";
            query($sql);
            $sqls = array();
            foreach ($dados as $linha => $grupo){
                if(!empty($grupo)){
                    $sqls[] = "('$linha', '$grupo')";
                }
            }
            if(count($sqls) > 0){
                $sql = "insert into bs_linha_grupo values " . implode(', ', $sqls);
                query($sql);
            }
        }
        redireciona(getLink() . 'index');
    }
}