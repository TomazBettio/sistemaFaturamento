<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


class sincronizar{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'sincronizarLinhas' => true,
        'sincronizarVendedores' => true,
        'sincronizarIpi' =>true,
        'sincronizarListaDePrecos' => true,
    );
    
    private $_rest;
    
    public function __construct(){
        global $config;
        $this->_rest = new rest_protheus($config['protheus']['link'], $config['protheus']['user'] ?? '', $config['protheus']['senha'] ?? '');
    }
    
    public function index(){
        $ret = '';
        
        /*
        $vendedores = $this->_rest->getAllVendedores();
        var_dump($vendedores);
        die();
        */
        
        $param['texto'] 	= 'Sincronizar Linhas';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick']   = "setLocation('" . getLink() . "sincronizarLinhas')";
        $param['cor'] 		= 'success';
        $param['bloco'] 		= true;
        $bt_linhas = formbase01::formBotao($param);
        
        $param['texto'] 	= 'Sincronizar Vendedores';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 	= "setLocation('" . getLink() . "sincronizarVendedores')";
        $param['cor'] 		= 'success';
        $param['bloco'] 		= true;
        $bt_vendedores = formbase01::formBotao($param);
        
        $param['texto'] 	= 'Sincronizar Preços';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 	= "setLocation('" . getLink() . "sincronizarListaDePrecos')";
        $param['cor'] 		= 'success';
        $param['bloco'] 		= true;
        $bt_precos = formbase01::formBotao($param);
        
        $param['texto'] 	= 'Sincronizar IPI';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 	= "setLocation('" . getLink() . "sincronizarIpi')";
        $param['cor'] 		= 'success';
        $param['bloco'] 		= true;
        $bt_ipi = formbase01::formBotao($param);
        
        $ret = addLinha(array('tamanhos' => [6, 6], 'conteudos' => [$bt_linhas, $bt_vendedores]));
        $ret .= '<br><br>' . addLinha(array('tamanhos' => [6, 6], 'conteudos' => [$bt_precos, $bt_ipi]));
        $ret = addCard(array('titulo' => 'Sincronizar Dados', 'conteudo' => $ret));
        
        return $ret;
    }
    
    public function sincronizarIpi(){
        $produtos = $this->_rest->getAllProdutos();
        if(is_array($produtos) && count($produtos) > 0){
            $sql_insert = [];
            foreach ($produtos as $prod){
                $sql_insert[] = "('{$prod['Code']}', {$prod['IpiTaxRate']})";
            }
            if(is_array($sql_insert) && count($sql_insert) > 0){
                $sql = "truncate bs_ipi";
                query($sql);
                $sql = "insert into bs_ipi values " . implode(', ', $sql_insert);
                query($sql);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    public function sincronizarListaDePrecos(){
        $lista = "001";
        $temp = $this->_rest->getListaPrecos($lista);
        $sql_insert = [];
        if(is_array($temp) && count($temp) > 0 && isset($temp['itensTablePrices']) && count($temp['itensTablePrices']) > 0){
            foreach ($temp['itensTablePrices'] as $produto){
                $sql_insert[] = "(null, '$lista', '{$produto['itemCode']}', {$produto['minimumSalesPrice']})";
            }
        }
        if(is_array($sql_insert) && count($sql_insert) > 0){
            $sql = "delete from bs_lista_precos where lista = '$lista'";
            query($sql);
            $sql = "insert into bs_lista_precos values " . implode(', ', $sql_insert);
            query($sql);
        }
        redireciona(getLink() . 'index');
    }
    
    public function sincronizarLinhas(){
        //$linhas = $this->_rest->getAllLinhas();
        $sql = "select * from SZ3040 where D_E_L_E_T_ != '*'";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $linhas = [];
            foreach ($rows as $row){
                $linhas[] = ['Code' => $row['Z3_GRUPO'], 'Name' => $row['Z3_DESC']];
            }
        //if(is_array($linhas) && count($linhas) > 0){
            $sql = "truncate bs_linhas";
            query($sql);
            $sqls = array();
            /*
            $originais = [
                '0M' => 'TROCATER DESCARTAVEIS',
                'BP' => 'BHIOPTA',
                'BQ' => 'BHIOQAP',
                'CL' => 'CLIP',
                'DR' => 'DRENOS E RESERVATORIOS',
                'KT' => 'VEDANTES - BHIOPTA / BALAO',
                'ME' => 'MEDICINA DO ESPORTE',
                '0S' => 'PINCA DE ENERGIA',
                '0W' => 'EQUIPAMENTOS BHIO SUPPLY',
                'EM' => 'GERADORES',
                'LC' => 'LAPARO DE COLUNA',
                'PC' => 'PRODUTOS CONFIANCE',
                'PP' => 'PRACTICAL',
                '02' => 'SENSORES E CABOS ECG',
                '0I' => 'OXIGENOTERAPIA',
                'PN' => 'PNI',
                '0A' => 'VIDEOLAPAROSCOPIA',
                '0B' => 'CIRURGIA ABERTA',
                '0D' => 'KIT CARDIO',
                '0L' => 'OTICAS RIGIDAS',
                '0U' => 'UROLOGIA / GINECOLOGIA',
                '0Y' => 'ARTROSCOPIA',
                'B0' => 'BHIOPP (ENGATE RAPIDO)',
                'BD' => 'LINHA DE TREINAMENTO',
                'SV' => 'SERVICOS ASSISTENCIA TECNICA',
                'VT' => 'VATS / TBC',
                'C0' => 'COMPONENTES USINADOS',
                'IM' => 'IMOBILIZADO',
                'LA' => 'LOCACAO DE INSTRUMENTAIS',
                'MP' => 'MATERIA PRIMA',
                'UC' => 'MATERIAL DE CONSUMO',
                'ZZ' => 'DIVERSOS / GENERICO',
            ];
            
            $codigos_linhas_rest_temp = array_column($linhas, 'Code');
            $codigos_linhas_rest = [];
            foreach ($codigos_linhas_rest_temp as $linha){
                $codigos_linhas_rest[] = trim($linha);
            }
            foreach($originais as $cod => $nome){
                if(!in_array($cod, $codigos_linhas_rest)){
                    $linhas[] = ['Code' => $cod, 'Name' => $nome];
                }
            }
            */
            foreach ($linhas as $linha){
                $sqls[] = "('" . trim($linha['Code']) . "', '" . $linha['Name'] . "')";
            }
            
            if(count($sqls) > 0){
                $sql = "insert into bs_linhas values " . implode(', ', $sqls);
                query($sql);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    public function sincronizarVendedores(){
        $sql = "
SELECT A3_COD, A3_NREDUZ, A3_NOME, A3_EMAIL, A3_MSBLQL FROM SA3040 where A3_COD IN (SELECT A3_COD FROM SA3040 GROUP BY A3_COD HAVING COUNT(*) = 1)

UNION

SELECT A3_COD, A3_NREDUZ, A3_NOME, A3_EMAIL, A3_MSBLQL FROM SA3040 where D_E_L_E_T_ != '*' AND A3_COD IN (SELECT A3_COD FROM SA3040 GROUP BY A3_COD HAVING COUNT(*) > 1)

";
        $rows = query2($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados_insert = array();
            foreach ($rows as $row){
                $nome = $row['A3_NREDUZ']; //ou name
                if(!empty(trim($nome))){
                    $nome = $row['A3_NOME']; //ou name
                }
                if(!empty(trim($nome))){
                    $id = $row['A3_COD'];
                    $email = strtolower($row['A3_EMAIL']);
                    $ativo = strval(trim($row['A3_MSBLQL'])) == '1' ? '*' : '';
                    $dados_insert[] = "('$id', '$nome', '$email', '$ativo')";
                }
            }
            if(is_array($dados_insert) && count($dados_insert) > 0){
                $sql_excluir = "truncate bs_vendedores";
                $sql = "insert into bs_vendedores values " . implode(', ', $dados_insert);
                query($sql_excluir);
                query($sql);
                addPortalMensagem('Vendedores recuperados com sucesso');
            }
            else{
                addPortalMensagem('Nenhum vendedor foi encontrado', 'error');
            }
        }
        else{
            addPortalMensagem('Não foi possível recuperar dados do Protheus', 'error');
        }
        redireciona(getLink() . 'index');
    }
}