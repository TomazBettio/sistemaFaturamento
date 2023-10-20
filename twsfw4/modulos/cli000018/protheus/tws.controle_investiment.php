<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class controle_investiment{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'incluirInvestimento' => true,
        'salvarInvestimento' => true,
        'listarInvestimentos' => true,
        'excluirInvestimento' => true,
        'listarProdutos' => true,
        'criarProduto' => true,
        'salvarProduto' => true,
        'recuperarProdutosProtheus' => true,
    );
    
    public function index(){
        $relatorio = new relatorio01(['titulo' => 'Controle de Investimentos', 'programa' => get_class(),]);
        $relatorio->addColuna(array('campo' => 'bt' , 'etiqueta' => '', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $relatorio->addColuna(array('campo' => 'cod' , 'etiqueta' => 'Cod. Anvisa', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $relatorio->addColuna(array('campo' => 'descricao'  , 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
        $relatorio->addColuna(array('campo' => 'total'      , 'etiqueta' => 'Total Investido', 'tipo' => 'V', 'width' => 80, 'posicao' => 'D'));
        $dados = $this->getDados();
        $relatorio->setDados($dados);
        $relatorio->setToExcel(true, 'investimentos');
        
        $botao = [];
        $botao['texto'] = 'Incluir Produto Manualmente';
        $botao['cor'] = 'success';
        $botao['tipo'] = 'link';
        $botao['url'] = getLink() . "criarProduto";
        $relatorio->addBotao($botao);
        
        $botao = [];
        $botao['texto'] = 'Recuperar Produtos do Protheus';
        $botao['cor'] = 'success';
        $botao['tipo'] = 'link';
        $botao['url'] = getLink() . "recuperarProdutosProtheus";
        $relatorio->addBotao($botao);
        
        return $relatorio . '';
    }
    
    public function criarProduto(){
        $ret = '';
        $form = new form01();
        $form->addCampo(array('id' => '', 'campo' => 'formProd[cod]'	 		, 'etiqueta' => 'Código'			, 'largura' => 3, 'tipo' => 'T' , 'tamanho' => '20'	, 'linhas' => '', 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formProd[desc]'	 		, 'etiqueta' => 'Descrição'			, 'largura' => 3, 'tipo' => 'T' , 'tamanho' => '100'	, 'linhas' => '', 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
        $form->setEnvio(getLink() . 'salvarProduto', 'formProd', 'formProd');
        $ret .= $form;
        $ret = addCard(['titulo' => 'Criar Produto', 'conteudo' => $ret]);
        return $ret;
    }
    
    public function salvarProduto(){
        $dados = $_POST['formProd'] ?? [];
        if(isset($dados['cod']) && isset($dados['desc'])){
            $codigo = $dados['cod'];
            $descricao = $dados['desc'];
            if(!empty($codigo) && !empty($descricao)){
                $sql = "insert into bs_produtos_investimentos values ('$codigo', '$descricao')";
                query($sql);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    private function getDados(){
        $ret = array();
        $sql = "select produtos.cod_anvisa, produtos.descricao, coalesce(valores.total, 0) as total from bs_produtos_investimentos as produtos left join (select sum(valor) as total, produto from bs_produtos_investimentos_valores where del is null group by produto) as valores on (produtos.cod_anvisa = valores.produto)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $param = array(
                    'titulo' => 'Ações',
                    'opcoes' => [
                        [
                            'texto' => 'Incluir Investimento',
                            'url' => getLink() . 'incluirInvestimento&id=' . $row['cod_anvisa'],
                        ],
                        [
                            'texto' => 'Listar Investimentos',
                            'url' => getLink() . 'listarInvestimentos&id=' . $row['cod_anvisa'],
                        ],
                        [
                            'texto' => 'Listar Produtos',
                            'url' => getLink() . 'listarProdutos&id=' . $row['cod_anvisa'],
                        ],
                    ],
                );
                
                $temp = array(
                    'bt' => formbase01::formBotaoDropdown($param),
                    'cod' => $row['cod_anvisa'],
                    'descricao' => $row['descricao'],
                    'total' => $row['total'],
                );
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function recuperarProdutosProtheus(){
        $sql = "SELECT * FROM (select RTRIM(LTRIM(B1_DESC)) as DESCRICAO, COALESCE(RTRIM(LTRIM(B1_REGANVI)), RTRIM(LTRIM(B1_REGANV2)), RTRIM(LTRIM(B1_REGANV3)), RTRIM(LTRIM(B1_REGANV4)), RTRIM(LTRIM(B1_REGANV5))) AS COD_ANVISA from SB1040 WHERE B1_TIPO = 'PA') AS TMP1 WHERE COD_ANVISA IS NOT NULL AND COD_ANVISA NOT LIKE ''";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados = [];
            $codigos_anvisa_usados = $this->getCodigosAnvisaExistentes();
            foreach ($rows as $row){
                if(!isset($dados[$row['COD_ANVISA']]) && !in_array($row['COD_ANVISA'], $codigos_anvisa_usados)){
                    $dados[$row['COD_ANVISA']] = $row['DESCRICAO'];
                };
            }
            
            if(is_array($dados) && count($dados) > 0){
                $sql = "insert into bs_produtos_investimentos values ";
                $dados_insert = array();
                foreach ($dados as $codigo_anvisa => $descricao){
                    $dados_insert[] = "('$codigo_anvisa', '$descricao')";
                }
                if(is_array($dados_insert) && count($dados_insert) > 0){
                    addPortalMensagem('Foram incluido ' . count($dados_insert) . ' produtos');
                    $sql .= implode(', ', $dados_insert);
                    query($sql);
                }
            }
            else{
                addPortalMensagem('Não foram encontrados produtos novos', 'error');
            }
        }
        
        redireciona(getLink() . 'index');
    }
    
    private function getCodigosAnvisaExistentes(){
        $ret = array();
        $sql = "select cod_anvisa from bs_produtos_investimentos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['cod_anvisa'];
            }
        }
        return $ret;
    }
    
    public function incluirInvestimento(){
        $ret = '';
        $id = $_GET['id'] ?? '';
        if(!empty($id)){
            $form = new form01();
            //valor, descricao
            $form->addCampo(array('id' => '', 'campo' => 'formInv[desc]'	 		, 'etiqueta' => 'Descrição'			, 'largura' => 3, 'tipo' => 'T' , 'tamanho' => '100'	, 'linhas' => '', 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
            $form->addCampo(array('id' => '', 'campo' => 'formInv[valor]'	 		, 'etiqueta' => 'Valor'				, 'largura' => 3, 'tipo' => 'V' , 'tamanho' => '100'	, 'linhas' => '', 'valor' => ''			, 'lista' => ''			, 'validacao' => '', 'obrigatorio' => true));
            $form->setEnvio(getLink() . 'salvarInvestimento&id=' . $id, 'formInv', 'formInv');
            $ret .= $form;
            $ret = addCard(['titulo' => 'Adicionar Investimento', 'conteudo' => $ret]);
        }
        else{
            $ret = 'nenhum produto foi informado';
        }
        return $ret;
    }
    
    public function salvarInvestimento(){
        $id = $_GET['id'] ?? '';
        if(!empty($id)){
            $dados = $_POST['formInv'];
            if(isset($dados['valor']) && isset($dados['desc'])){
                $desc = $dados['desc'];
                $valor = $dados['valor'];
                $valor = str_replace(['.', ','], ['', '.'], $valor);
                $usuario= getUsuario();
                $sql = "insert into bs_produtos_investimentos_valores values (null, '$id', '$desc', null, $valor, '$usuario')";
                query($sql);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    private function getNomeProduto($cod){
        $ret = '';
        $sql = "select descricao from bs_produtos_investimentos where cod_anvisa = '$cod'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['descricao'];
        }
        return $ret;
    }
    
    public function listarInvestimentos(){
        $ret = '';
        $id = $_GET['id'] ?? '';
        if(!empty($id)){
            //Controle de Investimentos
            $relatorio = new relatorio01(['titulo' => 'Controle de Investimentos - ' . $id . ' - ' . $this->getNomeProduto($id)]);
            $relatorio->addColuna(array('campo' => 'bt'         , 'etiqueta' => ''          , 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
            $relatorio->addColuna(array('campo' => 'descricao'  , 'etiqueta' => 'Descrição' , 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
            $relatorio->addColuna(array('campo' => 'valor'      , 'etiqueta' => 'Valor'     , 'tipo' => 'V', 'width' => 80, 'posicao' => 'D'));
            $dados = $this->getDadosInvestimentos($id);
            $relatorio->setDados($dados);
            
            $botao = [];
            $botao['texto'] = 'Incluir Investimento';
            $botao['cor'] = 'success';
            $botao['tipo'] = 'link';
            $botao['url'] = getLink() . "incluirInvestimento&id=$id";
            $relatorio->addBotao($botao);
            
            $botao = [];
            $botao['texto'] = 'Voltar';
            $botao['cor'] = 'danger';
            $botao['tipo'] = 'link';
            $botao['url'] = getLink() . "index";
            $relatorio->addBotao($botao);
            
            $relatorio->setToExcel(true);
            $relatorio->setToPDF(true);
            
            $relatorio->setBotaoDropDownTitulo(true);
            
            $ret .= $relatorio;
        }
        else{
            $ret = 'nenhum produto foi informado';
        }
        return $ret;
    }
    
    private function getDadosInvestimentos($id){
        $ret = [];
        $sql = "select * from bs_produtos_investimentos_valores where produto = '$id' and del is null";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $bt = [
                    'texto' => 'Excluir',
                    'tipo' => 'link',
                    'cor' => 'danger',
                    'url' => getLink() . 'excluirInvestimento&produto=' . $id . '&id=' . $row['id'],
                ];
                $bt = formbase01::formBotao($bt);
                $ret[] = [
                    'bt' => $bt,
                    'descricao' => $row['descricao'],
                    'valor' => $row['valor'],
                ];
            }
        }
        return $ret;
    }
    
    public function excluirInvestimento(){
        $id = $_GET['id'] ?? '';
        $produto = $_GET['produto'] ?? '';
        if(!empty($id) && !empty($produto)){
            $sql = "update bs_produtos_investimentos_valores set del = '" . getUsuario() . "' where id = $id";
            query($sql);
            $link = getLink() . 'listarInvestimentos&id=' . $produto;
        }
        else{
            $link = getLink() . 'index';
        }
        redireciona($link);
    }
    
    public function listarProdutos(){
        $ret = '';
        $id = $_GET['id'] ?? '';
        if(!empty($id)){
            $relatorio = new tabela01(['titulo' => 'Lista de Produtos - ' . $id . ' - ' . $this->getNomeProduto($id)]);
            $relatorio->addColuna(array('campo' => 'codigo'     , 'etiqueta' => 'Código'   , 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
            $relatorio->addColuna(array('campo' => 'descricao'  , 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
            $dados = $this->getListaProdutos($id);
            $relatorio->setDados($dados);
            
            $botao = [];
            $botao['texto'] = 'Voltar';
            $botao['cor'] = 'danger';
            $botao['tipo'] = 'link';
            $botao['url'] = getLink() . "index";
            $relatorio->addBotaoTitulo($botao);
            
            $ret .= $relatorio;
        }
        else{
            $ret = 'nenhum produto foi informado';
        }
        return $ret;
    }
    
    public function getListaProdutos($cod_anvisa){
        $ret = [];
        $sql = "SELECT * FROM (select B1_COD AS CODIGO, RTRIM(LTRIM(B1_DESC)) as DESCRICAO, COALESCE(RTRIM(LTRIM(B1_REGANVI)), RTRIM(LTRIM(B1_REGANV2)), RTRIM(LTRIM(B1_REGANV3)), RTRIM(LTRIM(B1_REGANV4)), RTRIM(LTRIM(B1_REGANV5))) AS COD_ANVISA from SB1040 WHERE B1_TIPO = 'PA') AS TMP1 WHERE COD_ANVISA like '$cod_anvisa'";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = [
                    'descricao' => $row['DESCRICAO'],
                    'codigo' => $row['CODIGO'],
                ];
            }
        }
        return $ret;
    }
}