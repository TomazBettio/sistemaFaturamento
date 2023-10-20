<?php
class regiao_vend{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'salvar'        => true,
        'excluir'       => true,
    );
    
    public function index(){
        $ret = '';
        $form = new form01();
        $form->addCampo(array('campo' => 'formRegiao[vendedor]', 'etiqueta' => 'Vendedor', 'valor' => '', 'tipo' => 'A', 'lista' => $this->getVendedores(), 'largura' => 6));
        $form->addCampo(array('campo' => 'formRegiao[regiao]', 'etiqueta' => 'Região', 'valor' => '', 'tipo' => 'A', 'lista' => $this->getRegioes(), 'largura' => 6));
        $ret = '<form id="formLinhasTeste" action="' . getLink() . 'salvar" method="post">' . $form . '</form>';
        $ret .= $this->criarTabela();
        
        $param = [];
        //$param['texto'] 	= traducoes::traduzirTextoDireto('Incluir');
        $param['texto'] 	= 'Incluir Relação';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 		= "document.getElementById('formLinhasTeste').submit();";
        $param['cor'] 		= 'success';
        
        $ret = addCard(array('titulo' => 'Configurar Regiões', 'conteudo' => $ret, 'botoesTitulo' => array($param)));
        
        return $ret;
    }
    
    public function excluir(){
        $temp = $_GET['codigo'];
        $temp = explode('_', $temp);
        $vendedor = $temp[0];
        $regiao = $temp[1];
        $sql = "delete from bs_regiao_vendedor where vendedor = '$vendedor' and regiao = '$regiao'";
        query($sql);
        redireciona(getLink() . 'index');
    }
    
    private function criarTabela(){
        $ret = '';
        $tabela = new tabela01();
        
        $param = array();
        $param["texto"] = "Excluir";
        $param["link"] 	= getLink() . 'excluir&codigo={ID}';
        $param["coluna"]= 'codigo';
        $param['cor'] = 'danger';
        $param["flag"] 	= "";
        $param["width"] = 30;
        $tabela->addAcao($param);
        
        $tabela->addColuna(array('campo' => 'nome'	    , 'etiqueta' => 'Vendedor'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'regiao'	, 'etiqueta' => 'Região'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        
        $dados = $this->getDados();
        $tabela->setDados($dados);
        
        $ret .= $tabela;
        
        return $ret;
    }
    
    private function getDados(){
        $ret = array();
        
        $sql = "select vend.nome as nome, relacao.vendedor as cod_vendedor, relacao.regiao as regiao from bs_regiao_vendedor as relacao left join bs_vendedores as vend on relacao.vendedor = vend.codigo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $regioes = $this->getRegioes(false);
            foreach ($rows as $row){
                $temp = array();
                $temp['nome'] = $row['nome'] . ' - ' . $row['cod_vendedor'];
                $temp['regiao'] = $regioes[$row['regiao']];
                $temp['codigo'] = $row['cod_vendedor'] . '_' . $row['regiao'];
                $ret[] = $temp;
            }
        }
        
        return $ret;
    }
    
    public function salvar(){
        $vendedor = $_POST['formRegiao']['vendedor'];
        $regiao = $_POST['formRegiao']['regiao'];
        $sql = "select * from bs_regiao_vendedor where vendedor = '$vendedor' and regiao = '$regiao'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            addPortalMensagem('O vendedor selecionado já faz parte dessa região', 'erro');
        }
        else{
            $sql = "insert into bs_regiao_vendedor values ('$vendedor', '$regiao')";
            query($sql);
            addPortalMensagem('Região adicionada com sucesso');
        }
        redireciona(getLink() . 'index');
    }
    
    private function getRegioes($lista = true){
        if($lista){
            $ret = tabela('000019', array('branco' => false));
            $ret[] = array('BR', 'Brasil');
        }
        else{
            $ret = montarDicionarioSys005('000019');
            $ret['BR'] = 'Brasil';
        }
        return $ret;
    }
    
    private function getVendedores(){
        $ret = array();
        $sql = "select * from bs_vendedores where codigo not like 'SUP%'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $ret[] = array($row['codigo'], ($row['codigo'] . ' - ' . $row['nome']));
            }
        }
        return $ret;
    }
}