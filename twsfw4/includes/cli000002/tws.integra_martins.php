<?php
/*
 * Data Criação: 04/02/2022
 * Autor: Emanuel
 *
 * Descrição: Coleção de funções para integrar com o Martins
 *
 * Alteracoes:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class integra_martins{
    var $funcoes_publicas = array(
        'index' => true,
    );
    
    // Nome do Programa
    private $_regioes = array();
    private $_rest;
    
    
    function __construct(){
        global $config;
        $this->_rest = new rest_martins($config['martins']['url_catalogo'], $config['martins']['url_pedidos'], $config['martins']['token']);
    }
    
    function migrarTudo(){
        $this->migracaoProdutosCompleta();
        $this->migracaoPrecosCompleta();
        $this->migracaoEstoqueCompleta();
        $this->migracaoFotosCompleta();
    }
    
    function migracaoProdutosCompleta(){
        $dados = $this->getProdutos();
        foreach ($dados as $row){
            $json = json_encode($row['dados']);
            $json = str_replace('null', '""', $json);
            $retorno_rest = $this->_rest->cadastrarProduto($json);
            $this->autalizarBD($row['id'], 'martins_produtos', $retorno_rest === false);
        }
    }
    
    function getProdutos(){
        $ret = array();
        $sql = "select * from martins_produtos where cadastro > sincro or (erro >= cadastro and erro >= sincro)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['product_code'] = $row['codigo_produto'];
                $temp['seller_id'] = '722';
                $temp['name'] = $row['nome_produto'];
                $temp['description'] = $row['descricao'];
                $temp['model'] = $row['modelo'];
                $temp['marketplace_category_code'] = intval($row['categoria']);
                $temp['manufacturer'] = $this->montarManifaturante($row);
                $temp['unit_measure'] = $this->montarUnidadeMedida($row);
                $temp['dimensions'] = $this->montarDimensoes($row);
                $temp['minimum_quantity'] = intval($row['quantidade_minima']);
                $temp['multiple_quantity'] = intval($row['quantidade_multipla']);
                $temp['multiple'] = $row['venda_multipla'] == 0 ? 0 : 1;
                $temp['package_quantity'] = intval($row['quantidade_pacote']);
                $temp['manufacturer_package_quantity'] = intval($row['quantidade_manufaturante']);
                $temp['cst_a'] = $row['cst_a'];
                $temp['cst_b'] = $row['cst_b'];
                $temp['cst_c'] = $row['cst_c'];
                $temp['ncm'] = $row['ncm'];
                $temp['additional_delivery_time'] = intval($row['tempo_entrega_adicional']);
                $temp['months_warranty'] = intval($row['garantia_meses']);
                $temp['active'] = $row['ativo'] == 'S' ? true : false;
                $temp['list_price'] = $this->montarPrecos($row['codigo_produto']);
                $temp['stock_attributes_distribution_center'] = $this->montarEstoque($row);
                $temp['attributes'] = $this->montarAtributos();
                
                $ret[] = array(
                    'id' => $row['id'],
                    'dados' => $temp,
                );
            }
        }
        return $ret;
    }
    
    function autalizarBD($id, $tabela, $erro){
        $data = date('Y-m-d H:i:s');
        if($erro === false){
            //se não ocorreu um erro
            $sql = "update $tabela set sincro = '$data' where id = '$id'";
        }
        else{
            $sql = "update $tabela set erro = '$data' where id = '$id'";
        }
        query($sql);
    }
    
    function montarManifaturante($dados){
        $ret = array();
        $ret['code'] = $dados['manifaturante_codigo'];
        $ret['name'] = $dados['manifaturante_nome'];
        return $ret;
    }
    
    function montarUnidadeMedida($dados){
        $ret = array();
        $ret['initials'] = $dados['unidade_de_medida_sigla'];
        $ret['description'] = $dados['unidade_de_medida'];
        return $ret;
    }
    
    function montarDimensoes($dados){
        $ret = array();
        $ret['height'] = floatval($dados['altura']);
        $ret['width'] = floatval($dados['largura']);
        $ret['length'] = floatval($dados['comprimento']);
        $ret['weight'] = floatval($dados['peso']);
        return $ret;
    }
    
    function montarEstoque($dados){
        $ret = array();
        $temp = array(
            'dc_code' => '722_UNICO',
            'variation_option_id' => 0,
            'quantity' => $this->getEstoqueProduto($dados['codigo_produto']),
            'sku_seller_id' => $dados['codigo_produto'],
            'bar_code' => $dados['cod_barra'],
            'dimensions' => $this->montarDimensoes($dados),
            'reference_code' => "varchar(30)",
        );
        $ret[] = $temp;
        return $ret;
    }
    
    function getEstoqueProduto($produto){
        $ret = 0;
        $sql = "select * from martins_estoque where produto = '$produto' and centro = '722_UNICO'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = intval($rows[0]['estoque']);
        }
        return $ret;
    }
    
    function montarPrecos($produto){
        $ret = array();
        $dados = $this->getPrecoProduto($produto);
        
        $temp = array(
            'current_price' => floatval($dados['preco_venda']),
            'list_price' => floatval($dados['preco_listado']),
            'st_value' => floatval($dados['st']),
            'ipi_value' => floatval($dados['ipi']),
            'variation_option_id' => 0,
            "marketplace_scope_code" => "LST_RS_0",
            'erp_code' => $dados['produto'],
        );        
        $ret[] = $temp;
        return $ret;
    }
    
    private function getPrecoProduto($produto){
        $ret = array(
            'preco_venda' => 1000,
            'preco_listado' => 0,
            'st' => 0,
            'ipi' => 0,
            'produto' => $produto,
        );
        $sql = "select * from martins_precos where produto = '$produto' and estado = 'RS'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = array(
                'preco_venda' => $rows[0]['preco_venda'],
                'preco_listado' => $rows[0]['preco_listado'],
                'st' => $rows[0]['st'],
                'ipi' => $rows[0]['ipi'],
                'produto' => $produto,
            );
        }
        
        return $ret;
    }
    
    function montarAtributos(){
        $ret = array();
        $atributos = array(
            'attribute_id' => 0,
            'attribute_option_id' => 0,
        );
        $ret[] = $atributos;
        return $ret;
    }
    
    function getEstoque(){
        $ret = array();
        $sql = "select * from martins_estoque where cadastro > sincro or (erro >= cadastro and erro >= sincro)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['sku_seller_id'] = $row['produto'];
                $temp['variation_option_id'] = 0;
                $temp['dc_code'] = $row['centro'];
                $temp['quantity'] = $row['estoque'];
                
                $ret[] = array(
                    'id' => $row['id'],
                    'dados' => $temp,
                );
            }
        }
        return $ret;
    }
    
    function migracaoEstoqueCompleta(){
        $dados = $this->getEstoque();
        foreach ($dados as $row){
            $json = json_encode(array($row['dados']));
            $retorno_rest = $this->_rest->cadastrarEstoque($json);
            $this->autalizarBD($row['id'], 'martins_estoque', $retorno_rest === false);
            sleep(1);
        }
    }
    
    function getPrecos(){
        $ret = array();
        $sql = "select * from martins_precos where cadastro > sincro or (erro >= cadastro and erro >= sincro)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if($this->getRegiao($row['estado']) != ''){
                    $temp1 = array();
                    $temp1['erp_code'] = $row['produto'];
                    $temp1['variation_option_id'] = 0;
                    $temp1['sale_price'] = floatval($row['preco_venda']);
                    $temp1['list_price'] = floatval($row['preco_listado']);
                    $temp1['st_value'] = floatval($row['st']);
                    $temp1['ipi_value'] = floatval($row['ipi']);
                    $temp2 = array(
                        'marketplace_scope_code' => $this->getRegiao($row['estado']),
                        'items' => array($temp1)
                    );
                    $ret[] = array(
                        'id' => $row['id'],
                        'dados' => $temp2,
                    );
                }
                
            }
        }
        return $ret;
    }
    
    function migracaoPrecosCompleta(){
        $dados = $this->getPrecos();
        foreach ($dados as $row){
            $json = json_encode(array($row['dados']));
            $retorno_rest = $this->_rest->cadastrarPrecos($json);
            $this->autalizarBD($row['id'], 'martins_precos', $retorno_rest === false);
        }
    }
    
    function getRegiao($regiao){
        if(count($this->_regioes) == 0){
            $sql = "select * from martins_regiao";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $this->_regioes[$row['estado']] = $row['codigo'];
                }
            }
        }
        return isset($this->_regioes[$regiao]) ? $this->_regioes[$regiao] : '';
    }
    
    function migracaoFotosCompleta(){
        $dados = $this->getFotos();
        foreach ($dados as $row){
            $json = json_encode($row['dados']);
            $retorno_rest = $this->_rest->incluirFotoProduto($json);
//echo "$json <br>\n";
            $this->autalizarBD($row['id'], 'martins_fotos', $retorno_rest === false);
        }
    }
    
    private function getFotos(){
        $ret = array();
        $sql = "select * from martins_fotos where cadastro > sincro or (erro >= cadastro and erro >= sincro)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp['code'] = $row['produto'];
                $temp['name'] = $row['produto'].'_1';
                $temp['variation_option_id'] = 0;
                $temp['link'] = $row['link'];
                $ret[] = array(
                    'id' => $row['id'],
                    'dados' => array(
                        'product_code' => $row['produto'],
                        'photos' => array(
                            $temp,
                        ),
                    ),
                );
            }
        }
        return $ret;
    }
    
    public function getCategorias(){
        $ret = $this->_rest->getCategorias();
        return $ret;
    }
}