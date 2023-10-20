<?php
use Smalot\PdfParser\XObject\Form;

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class supervisor{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'salvar'        => true,
    );
    
    public function index(){
        $ret = '';
        $linhas = $this->getLinhas();
        
        $tabela = new tabela01(/*array('ordenacao' => false)*/);
        $tabela->addColuna(array('campo' => 'nome'	, 'etiqueta' => 'Vendedor', 'tipo' => 'T', 'width' =>  117, 'posicao' => 'E'));
        foreach ($linhas as $codigo => $nome){
            $tabela->addColuna(array('campo' => $codigo	, 'etiqueta' => $nome, 'tipo' => 'T', 'width' =>  150, 'posicao' => 'E'));
        }
        
        $dados = $this->getDados();
        $tabela->setDados($dados);
        $ret .= $tabela;
        
        $ret = '<form id="formLinhasTeste" action="' . getLink() . 'salvar" method="post">' . $ret . '</form>';
        
        $param = [];
        //$param['texto'] 	= traducoes::traduzirTextoDireto('Incluir');
        $param['texto'] 	= 'Salvar';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 		= "document.getElementById('formLinhasTeste').submit();";
        $param['cor'] 		= 'success';
        
        $ret = addCard(array('titulo' => 'Configurar Supervisores', 'conteudo' => $ret, 'botoesTitulo' => array($param)));
        
        return $ret;
    }
    
    private function getDados(){
        $ret = array();
        $linhas = $this->getLinhas();
        $vendedores = $this->getVendedores();
        $relacoes = $this->getRelacoes();
        
        foreach ($vendedores as $codigo => $nome){
            $temp = array();
            $temp = array('nome' => $nome);
            foreach ($linhas as $codigo_linha => $nome_linha){
                $form = new form01();
                $form->addCampo(array('valor' => ($relacoes[$codigo][$codigo_linha] ?? ''), 'campo' => 'formSup[' . $codigo . '_' . $codigo_linha . ']', 'tipo' => 'A', 'lista' => $this->getSupervisores()));
                $temp[$codigo_linha] = $form . '';
            }
            $ret[] = $temp;
        }
        
        return $ret;
    }
    
    public function salvar(){
        $dados = $_POST['formSup'];
        $relacoes = $this->getRelacoes();
        foreach ($dados as $chave => $valor){
            $temp = explode('_', $chave);
            $vendedor = $temp[0];
            $linha = $temp[1];
            $sql = '';
            if(isset($relacoes[$vendedor][$linha]) && empty($valor)){
                //deleta
                $sql = "delete from bs_supervisor where vendedor = '$vendedor' and linha = '$linha'";
                query($sql);
            }
            elseif(isset($relacoes[$vendedor][$linha]) && !empty($valor) && $relacoes[$vendedor][$linha] != $valor){
                //altera
                $sql = "update bs_supervisor set supervisor = '$valor' where vendedor = '$vendedor' and linha = '$linha'";
            }
            elseif(!isset($relacoes[$vendedor][$linha]) && !empty($valor)){
                //incluir
                $sql = "insert into bs_supervisor values ('$vendedor', '$linha', '$valor')";
            }
            if(!empty($sql)){
                query($sql);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    private function getRelacoes(){
        $ret = array();
        $sql = "select * from bs_supervisor";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['vendedor']][$row['linha']] = $row['supervisor'];
            }
        }
        return $ret;
    }
    
    private function getLinhas(){
        $ret = array();
        $sql = "select * from bs_linhas";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $ret[$row['codigo']] = $row['nome'];
            }
        }
        return $ret;
    }
    
    private function getVendedores(){
        $ret = array();
        $sql = "select * from bs_vendedores where codigo not like 'SUP%'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $ret[$row['codigo']] = $row['nome'] . ' - ' . $row['codigo'];
            }
        }
        return $ret;
    }
    
    private function getSupervisores(){
        $ret = array();
        $sql = "select * from bs_vendedores where codigo like 'SUP%'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret[] = array('', '');
            foreach($rows as $row){
                //$ret[$row['codigo']] = $row['nome'];
                $ret[] = array($row['codigo'], $row['nome']);
            }
        }
        return $ret;
    }
}