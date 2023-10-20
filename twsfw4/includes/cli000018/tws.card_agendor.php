<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class card_agendor{
    private $_id;
    private $_dados;
    private $_rest;
    
    public function __construct($id){
        $this->_id = $id;
        $this->_dados = false;
        $this->_rest = criarRestAgendor();
    }
    
    private function getDados(){
        if($this->_dados === false){
            $temp = $this->_rest->getNegocio($this->_id);
            if(is_array($temp) && count($temp) > 0 && isset($temp['data'])){
                $this->_dados= $temp['data'];
            }
        }
        return $this->_dados;
    }
    
    public function getDadosOrcamento(){
        //retorna dados como nome agendor, nome protheus, quantidade, valor_orcamento, valor_lista, ipi, porcentagem, valor total
        //vem do agendor: nome agendor, quantidade, valor_orcamento, valor total
        //vem do protheus: nome protheus, valor_lista, ipi
        //calculado: porcentagem
        $this->getDados();
        $ret = array();
        $entidades = $this->_dados['products_entities'];
        $produtos = [];
        if(is_array($entidades) && count($entidades) > 0){
            foreach ($entidades as $entidade){
                $codigo = $entidade['code'];
                $quantidade = $entidade['quantity'] > 0 ? $entidade['quantity'] : 1;
                $preco_orcamento = $entidade['unitValue'];
                $total = $entidade['totalValue'];
                $nome_agendor = $entidade['name'];
                $temp = array(
                    'codigo' => $codigo,
                    'qtde' => $quantidade,
                    'valor_orcamento' => $preco_orcamento,
                    'total' => $total,
                    'nome_agendor' => $nome_agendor,
                );
                $produtos[] = $codigo;
                $ret[$codigo] = $temp;
            }
        }
        $dados_goflow = $this->getDadosProdutosGoFlow($produtos);
        foreach ($dados_goflow as $codigo => $dados){
            $ret[$codigo]['nome_protheus'] = $dados['etiqueta'];
            $ret[$codigo]['ipi'] = $dados['ipi'];
            $ret[$codigo]['valor_protheus'] = $dados['valor'];
            $ret[$codigo]['valor_orcamento_ipi'] = round((1 + ($ret[$codigo]['ipi'] / 100)) * $ret[$codigo]['valor_orcamento'], 2);
            
            $ret[$codigo]['total_ipi'] = $ret[$codigo]['total'] * (1 + ($ret[$codigo]['ipi'] > 0 ? ($ret[$codigo]['ipi'] / 100) : 0));
        }
        
        
        
        foreach ($produtos as $prod){
            $porcentagem_base = $ret[$prod]['valor_protheus'] > 0 ? ($ret[$prod]['valor_orcamento']/$ret[$prod]['valor_protheus']) : 0;
            $porcentagem_final = 0;
            if($porcentagem_base != 0 && $porcentagem_base != 1){
                if($porcentagem_base > 1){
                    $porcentagem_final = '(+)' . formataReais(($porcentagem_base - 1) * 100);
                }
                else{
                    $porcentagem_final = '(-)' . formataReais((1 - $porcentagem_base) * 100);
                }
            }
            $ret[$prod]['porcentagem'] = $porcentagem_final . '%'; 
        }
        
        $temp = $ret;
        $ret = [];
        foreach ($temp as $t){
            $ret[] = $t;
        }
        
        return $ret;
    }
    
    private function getDadosProdutosGoFlow($produtos){
        //busca dados dos produtos no banco local
        $ret = [];
        $temp = [];
        foreach ($produtos as $prod){
            $temp[] = "'$prod'";
        }
        $sql = "select * from bs_produtos where cod in (" . implode(', ', $temp) . ")";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['etiqueta', 'ipi', 'valor'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $ret[$row['cod']] = $temp;
            }
        }
        return $ret;
    }
    
    public function gerarOrcamento($condicao){
        $ret = '';
        $this->getDados();
        
        //verificar se já existe orçamento para esse card
        //se não existe:
        
        
        $codigo_unico = card_agendor::gerarCodigoUnico();
        
        $id_empresa = $this->_dados['organization']['id'];
        $cod_vendedor = $this->_dados['owner']['id'] ?? '';
        //$cnpj = $negocio['organization']['cnpj'];
        $dados_post = array();
        $dados_post['appEmail'] = $this->getEmailVendedor($cod_vendedor);
        $dados_post['client'] = $this->getDadosEmpresa($id_empresa);
        $dados_post['delivery'] = $dados_post['client']['delivery'];
        $dados_post['file'] = '';
        $dados_post['obs'] = '';
        $dados_post['paymentCondition'] = $this->recuperarCondPagamentoRest($condicao);
        $dados_post['products'] = $this->getProdutosCriarOrcamento();
        $dados_post['saleDate'] = datas::data_hoje();
        $dados_post['sellerCode'] = $this->getCodigoVendedor($dados_post['client']['state']);
        $dados_post['type'] = 'O';
        $dados_post['uid'] = $codigo_unico;
        
        if($this->validarDados($dados_post)){
            $json = $this->montarJsonFinal($dados_post);
            $rest_protheus = criarRestProtheus();
            $contador = 0;
            do{
                $resposta = $rest_protheus->criarOrcamento($json);
                $contador++;
            }
            while($contador < 10 && !(is_array($resposta) && isset($resposta['errorCode']) && $resposta['errorCode'] == 0));
            //while para tentar mais de uma vez caso seja um erro simples
            if(is_array($resposta) && isset($resposta['errorCode']) && $resposta['errorCode'] == 0){
                $ret = $codigo_unico;
            }
            
        }       
        return $ret;
    }
    
    static function gerarCodigoUnico(){
        //gera um código único e vefica o banco para ter certeza que o código não é repetido
        $ret = '';
        do{
            $ret = card_agendor::gerarBlocoCodigoUnico(8) . '-' . card_agendor::gerarBlocoCodigoUnico(4) . '-' . card_agendor::gerarBlocoCodigoUnico(4) . '-' . card_agendor::gerarBlocoCodigoUnico(4) . '-' . card_agendor::gerarBlocoCodigoUnico(12);
            $sql = "select codigo_unico from bs_orcamentos where codigo_unico = '$ret'";
            $rows = query($sql);
        }
        while(!(is_array($rows) && count($rows) === 0));
        return $ret;
    }
    
    static function gerarBlocoCodigoUnico($tam){
        $ret = '';
        $string = "0123456789abcdef";
        $quant= strlen($string);
        for($i=0;$i<$tam;$i++){
            $ret .= $string[rand(0, $quant-1)];
        }
        return $ret;
    }
    
    private function validarDados($dados){
        $ret = true;
        foreach ($dados as $valor){
            $ret = (!is_null($valor)) && $ret;
        }
        foreach ($dados['client'] as $valor){
            $ret = (!is_null($valor)) && $ret;
        }
        $ret = $ret && (count($dados['products']) > 0);
        return $ret;
    }
    
    private function montarJsonFinal($dados){
        $ret = json_encode($dados);
        $ret = str_replace('\\' . '/', '/', $ret);
        return $ret;
    }
    
    private function getEmailVendedor($cod_vendedor){
        $ret = null;
        if(!empty($cod_vendedor)){
            $dados_vendedor = $this->_rest->getUser($cod_vendedor);
            if(is_array($dados_vendedor)){
                $ret = $dados_vendedor['data'][0]['contact']['email'];
            }
        }
        return $ret;
    }
    
    private function getDadosEmpresa($id_empresa){
        $ret = array();
        $temp = $this->_rest->getEmpresa($id_empresa);
        if(is_array($temp) && count($temp) > 0){
            $dados_empresa = $temp['data'];
            $rua = $dados_empresa['address']['streetName'] . ' ' . $dados_empresa['address']['streetNumber'];
            $telefone = !empty($dados_empresa['contact']['work']) ? $dados_empresa['contact']['work'] : $dados_empresa['contact']['mobile'];
            $telefone = str_replace(array('(', ')', '-', '/', '\\', ' '), '', $telefone);
            $ret = array(
                'cep' => $dados_empresa['address']['postalCode'] ?? '',
                'city' => $dados_empresa['address']['city'] ?? '',
                'delivery' => $rua,
                'document' => $dados_empresa['cnpj'] ?? '',
                'email' => $dados_empresa['email'] ?? '',
                'name' => $dados_empresa['legalName'] ?? '',
                'neighborhood' => $dados_empresa['address']['district'] ?? '',
                'phone' => $telefone,
                'state' => $dados_empresa['address']['state'] ?? '',
                'stateInscription' => '',
                'street' => $rua,
            );
        }
        return $ret;
    }
    
    private function getProdutosCriarOrcamento(){
        $ret = [];
        $produtos_raw = $this->getDadosOrcamento();
        foreach ($produtos_raw as $produto){
            $temp = [
                'code' => $produto['codigo'],
                'ipi' => $produto['ipi'],
                'obs'   => '',
                'operation' => '01',
                'price' => $produto['valor_orcamento'],
                'quantity' => $produto['qtde'],
            ];
            $ret[] = $temp;
        }
        return $ret;
    }
    
    private function getCodigoVendedor($estado){
        $ret = null;
        $vendedor = $this->_dados['owner']['id'];
        $sql = "select id_agendor, cod_protheus, coalesce(regiao, 'BASE') as regiao from bs_agendor_protheus_vendedores left join bs_regiao_vendedor on (bs_agendor_protheus_vendedores.cod_protheus = bs_regiao_vendedor.vendedor) where id_agendor = '$vendedor'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $temp = [];
            foreach($rows as $row){
                $temp[$row['regiao']] = $row['cod_protheus'];
            }
            $ret = $temp[$estado] ?? $temp['BASE'] ?? null;
        }
        return $ret;
    }
    
    private function recuperarCondPagamentoRest($condicao){
        //recupera o condição de pagamento para usar no rest de criar orçamentos
        $ret = '';
        $sql = "select E4_DESCRI from SE4040 where E4_CODIGO = '$condicao' and D_E_L_E_T_ != '*'";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = "$condicao - " . $rows[0]['E4_DESCRI'];
        }
        return $ret;
    }
    
    public function existeOrcamento(){
        $ret = false;
        $sql = "select id from bs_orcamentos where id_agendor = '$this->_id'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) == 1){
            $ret = true;
        }
        return $ret;
    }
}