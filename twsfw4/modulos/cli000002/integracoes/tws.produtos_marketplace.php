<?php

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class produtos_marketplace{
    var $funcoes_publicas = array(
        'index' 	    => true,
        'salvar' => true,
        'recuperarProdutosWinthor' => true,
    );
    
    private $_integracoes;
    private $_etiquetas_integracoes;
    private $_dados_brutos;
    private $_nomes_produtos;
    
    //Nome programa
    private $_programa;
    
    //Titulo
    private $_titulo;
    
    function __construct(){
        $this->_integracoes = array('MA', 'CR');
        $this->_etiquetas_integracoes = array(
            'MA' => 'Martins',
            'CR' => 'Consulta Remédios',
        );
        $this->_dados_brutos = array();
        $this->_nomes_produtos = array();
        
        $this->_programa = get_class($this);
        $this->_titulo = 'Gerenciar Produtos Marketplace';
        
        if(false){
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Status Produto', 'variavel' => 'STATUS'         ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'T=Todos;FL=Fora de Linha;E=Excluídos;FLE=Fora de Linha/Excluídos']);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Fornecedor'		, 'variavel' => 'FORNECEDOR'    ,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Departamento'	, 'variavel' => 'DEPTO'         ,'tipo' => 'T', 'tamanho' => '6', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '=Todos;1=Medicamentos;12=Não Medicamentos']);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '4', 'pergunta' => 'Psicotrópicos'	, 'variavel' => 'PSICO'      	,'tipo' => 'T', 'tamanho' => '6', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '=Todos;S=Psicotropicos;N=Não Psicotropicos']);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '5', 'pergunta' => 'Retnoicos'		, 'variavel' => 'RETINOICO'     ,'tipo' => 'T', 'tamanho' => '6', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '=Todos;S=Retnoicos;N=Não Retinoicos']);
        	sys004::inclui(['programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '6', 'pergunta' => 'Marcas'			, 'variavel' => 'MARCAS'    	,'tipo' => 'T', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        }
        
    }
    
    public function index(){
    	$ret = '';
    	
    	$param = array();
    	$param['paginacao'] = true;
    	$param['scrollX'] = true;
    	$param['scrollY'] = true;
    	$param['scroll'] = true;
    	$param['programa'] = $this->_programa;
    	$param['titulo'] = $this->_titulo;
    	$tabela = new relatorio01($param);
    	//$tabela->setLinkFiltro(getLink().'index');
    	$tabela->setToExcel(true);
    	
        $filtro = $tabela->getFiltro();
        
        $tabela->addColuna(array('campo' => 'cod_prod'	, 'etiqueta' => 'Codprod' 			,'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'nome' 		, 'etiqueta' => 'Nome eCommerce' 	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'nomemkp' 	, 'etiqueta' => 'Nome MktPlace' 	,'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        foreach ($this->_integracoes as $int){
            $tabela->addColuna(array('campo' => $int , 'etiqueta' => $this->_etiquetas_integracoes[$int] ,'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
        }
        
        $tabela->addColuna(array('campo' => 'detalhe' 		, 'etiqueta' => 'FL/Exluido' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'ean' 			, 'etiqueta' => 'EAN' 			,'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'dtcadastro' 	, 'etiqueta' => 'DT Cadastro' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'codfornec' 	, 'etiqueta' => 'Cod.Fornec' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'fornecedor' 	, 'etiqueta' => 'Fornecedor' 	,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'depto' 		, 'etiqueta' => 'Depto' 		,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'marca' 		, 'etiqueta' => 'Marca' 		,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'psico' 		, 'etiqueta' => 'Psicotropico' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'retinoico' 	, 'etiqueta' => 'Retinoico' 	,'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'desc2' 		, 'etiqueta' => 'Descrição 2' 	,'tipo' => 'T', 'width' => 150, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'estoque' 		, 'etiqueta' => 'Estoque' 		,'tipo' => 'N', 'width' =>  80, 'posicao' => 'D'));
        	
        if(!$tabela->getPrimeira()){
	        
	        $dados = $this->getDados($filtro);
	        $tabela->setDados($dados);
	        
	        $param = array(
	            'acao' =>  getLink().'salvar',
	            'nome' => 'formIntegracao',
	            'id' => 'formIntegracao',
	        );
	        $tabela->setFormTabela($param);
	
	        $p = array();
	        $p['onclick'] = "setLocation('".getLink()."recuperarProdutosWinthor')";
	        $p['cor'] = 'sucess';
	        $p['texto'] = 'Recuperar Produtos Winthor';
	        $tabela->addBotao($p);
	        
	        $p = array();
	        $p['onclick'] = "$('#formIntegracao').submit();";
	        $p['cor'] = 'danger';
	        $p['texto'] = 'Gravar';
	        $tabela->addBotao($p);
	    }
        
        $ret .= $tabela;
        
        return $ret;
    }
    
    private function getDados($filtro){
        $ret = array();
        
        $sql = "select produtos.cod_prod, ";
        $campos_select = array();
        foreach ($this->_integracoes as $int){
            $campos_select[] = " case when $int" . "temp.cod_prod is null then 0 else 1 end as $int";
        }
        $sql .= implode(', ', $campos_select);
        
        $sql .= " from (select distinct cod_prod from gf_produtos_marketplace) produtos ";
        
        $campos_join = array();
        
        foreach ($this->_integracoes as $int){
            $nome_tabela = $int . "temp";
            $campos_join[] = " left join (select * from gf_produtos_marketplace where integracao = '$int') as $nome_tabela on produtos.cod_prod = $nome_tabela.cod_prod ";
        }
        
        $sql .= implode('', $campos_join);
        
        $sql .= ' order by produtos.cod_prod';
        
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
            	$codProd = $row['cod_prod'];
                $temp = array();
                $temp['cod_prod'] = $row['cod_prod'];
                //$temp['nome'] = $this->getNomeProduto($row['cod_prod']);
                foreach ($this->_integracoes as $int){
                    $param = array(
                        'nome' => 'formIntegracao[' . $row['cod_prod'] . "][$int]",
                    );
                    if($row[$int] == 1){
                        $param['checked'] = true;
                    }
                    
                    $temp[$int] = formbase01::formCheck($param);
                    //$temp[$int] = $row[$int];
                }
                $ret[$codProd] = $temp;
            }
        }
        
        $produtos = $this->getDadosProdutos($filtro);
        $retorno = [];
        
        foreach ($produtos as $codprod => $produto){
        	if(isset($ret[$codprod])){
        		$temp = $produto;
        		foreach ($this->_integracoes as $int){
        			$temp[$int] = $ret[$codprod][$int];
        		}
        		
        		$retorno[] = $temp;
        	}
        }
        
        return $retorno;
    }
    
    private function getDadosProdutos($filtro){
    	$ret = [];
    	
    	$ativo 		= isset($filtro['STATUS']) && !empty($filtro['STATUS']) ? $filtro['STATUS'] : '';
    	$fornec 	= isset($filtro['FORNECEDOR']) && !empty($filtro['FORNECEDOR']) ? $filtro['FORNECEDOR'] : '';
    	$depto 		= isset($filtro['DEPTO']) && !empty($filtro['DEPTO']) ? $filtro['DEPTO'] : '';
    	$psico 		= isset($filtro['PSICO']) && !empty($filtro['PSICO']) ? $filtro['PSICO'] : '';
    	$retinoico 	= isset($filtro['RETINOICO']) && !empty($filtro['RETINOICO']) ? $filtro['RETINOICO'] : '';
    	$marcas 	= isset($filtro['MARCAS']) && !empty($filtro['MARCAS']) ? $filtro['MARCAS'] : '';
    	
    	$where = '';
    	if(!empty($ativo) && $ativo <> 'T'){
    		if($ativo == 'FL'){
    			$where .= " AND OBS2 <> 'FL'";
    		}
    		if($ativo == 'E'){
    			$where .= " AND DTEXCLUSAO IS NULL ";
    		}
    		if($ativo == 'FLE'){
    			$where .= " AND DTEXCLUSAO IS NULL AND OBS2 <> 'FL' ";
    		}
    	}
    	
    	if(!empty($fornec)){
    		$where .= " AND CODFORNEC IN ($fornec) ";
    	}
    	
    	if(!empty($marcas)){
    		$where .= " AND CODMARCA IN ($marcas) ";
    	}
    	
    	if(!empty($depto)){
    		$where .= " AND CODEPTO IN ($depto) ";
    	}
    	
    	if(!empty($retinoico)){
    		$where .= " AND RETINOICO = '$retinoico' ";
    	}
    	
    	if(!empty($psico)){
    		$where .= " AND PSICOTROPICO = '$psico' ";
    	}
    	
    	$sql = "
				SELECT 
					CODPROD,
					DESCRICAO,
					NOMEECOMMERCE,
					NOMEECOMMERCE_MARKETPLACE,
					CODAUXILIAR,
					DTCADASTRO,
					CODFORNEC,
					DTEXCLUSAO,
					(SELECT PCMARCA.MARCA FROM PCMARCA WHERE PCMARCA.CODMARCA = PCPRODUT.CODMARCA) MARCA,
					(SELECT FORNECEDOR FROM PCFORNEC WHERE CODFORNEC = PCPRODUT.CODFORNEC) AS FORNEC,
					(SELECT DESCRICAO FROM PCDEPTO WHERE CODEPTO = PCPRODUT.CODEPTO) AS DEPARTAMENTO,
					PCPRODUT.OBS2 AS FORA_LINHA,
					PSICOTROPICO,
					RETINOICO,
					DESCRICAO2,
					OBS2,
					(SELECT SUM((NVL(PCEST.QTESTGER,0) - NVL(PCEST.QTRESERV,0) - NVL(PCEST.QTINDENIZ,0))) FROM PCEST WHERE CODPROD = PCPRODUT.CODPROD) AS ESTOQUE
				FROM 
					PCPRODUT
				WHERE 
					CODEPTO <> 4
					$where";

		$rows = query4($sql);
		if( getUsuario() == 'thiel'){
			echo "$sql <br>\n";
		}
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$hiden = formbase01::formHidden(['nome' => 'produtoListado[]', 'valor' => $row['CODPROD']]);
				$temp['cod_prod'	] = $row['CODPROD'].$hiden;
				//$temp['descricao' 	] = $row['DESCRICAO'];
				$temp['nome' 		] = $row['NOMEECOMMERCE'];
				$temp['nomemkp' 	] = $row['NOMEECOMMERCE_MARKETPLACE'];
				$temp['detalhe' 	] = !empty($row['DTEXCLUSAO']) ? $row['DTEXCLUSAO'] : (!empty($row['OBS2']) ? $row['OBS2'] : '');
				$temp['ean' ] 		  = $row['CODAUXILIAR'];
				$temp['dtcadastro' 	] = $row['DTCADASTRO'];
				$temp['codfornec' 	] = $row['CODFORNEC'];
				$temp['fornecedor' 	] = $row['FORNEC'];
				$temp['depto' 		] = $row['DEPARTAMENTO'];
				$temp['psico' 		] = $row['PSICOTROPICO'];
				$temp['retinoico' 	] = $row['RETINOICO'];
				$temp['desc2' 		] = $row['DESCRICAO2'];
				$temp['estoque' 	] = $row['ESTOQUE'];
				$temp['marca' 		] = $row['MARCA'];
		
				$ret[$row['CODPROD']] = $temp;
			}
		}
		
		return $ret;
    }
    
    public function salvar(){
        $form = verificaParametro($_POST, 'formIntegracao', array());
        $produtosListados = getParam($_POST, 'produtoListado');
        if(is_array($form)){
            $this->getDadosBrutos();
            $produtos_usados = array();
            foreach ($form as $cod_prod => $integracoes){
                $produtos_usados[] = $cod_prod;
                $integracoes_usadas = array_keys($integracoes);
                $integracoes_ignoradas = array();
                foreach ($this->_integracoes as $int){
                    if(!in_array($int, $integracoes_usadas)){
                        $integracoes_ignoradas[] = "'$int'";
                    }
                }
                if(count($integracoes_ignoradas) > 0){
                    $this->excluirIntegracao($cod_prod, $integracoes_ignoradas);
                }
                if(count($integracoes_usadas) > 0){
                    $this->salvarIntegracao($cod_prod, $integracoes_usadas);
                }
            }
            foreach ($produtosListados as $pt){
                if(!in_array($pt, $produtos_usados)){
                    $this->deletarTodasIntegracoes($pt);
                }
            }
        }
        return $this->index();
    }
    
    private function excluirIntegracao($cod_prod, $integracoes){
        $sql = "delete from gf_produtos_marketplace where cod_prod = '$cod_prod' and integracao in (" . implode(', ', $integracoes) . ")";
        query($sql);
    }
    
    private function salvarIntegracao($cod_prod, $integracoes){
        $integracos_temp = array();
        foreach ($integracoes as $int){
            if(!isset($this->_dados_brutos[$cod_prod][$int])){
                $integracos_temp[] = $int;
            }
        }
        $values = array();
        foreach ($integracos_temp as $int){
            $values[] = "(null, '$cod_prod', '$int')";
        }
        if(count($values) > 0){
            $sql = "insert into gf_produtos_marketplace values " . implode(', ', $values);
            query($sql);
        }
    }
    
    private function getProdutos(){
        $ret = array();
        $sql = "select distinct cod_prod from gf_produtos_marketplace";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['cod_prod'];
            }
        }
        return $ret;
    }
    
    private function deletarTodasIntegracoes($cod_prod){
        $sql = "delete from gf_produtos_marketplace where cod_prod = '$cod_prod' and integracao != 'P'";
        query($sql);
    }
    
    private function getDadosBrutos(){
        if(count($this->_dados_brutos) == 0){
            $ret = array();
            $sql = "select * from gf_produtos_marketplace";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[$row['cod_prod']][$row['integracao']] = true;
                }
            }
            $this->_dados_brutos = $ret;
        }
    }
    
    public function recuperarProdutosWinthor(){
        $this->getDadosBrutos();
        $sql = "select CODPROD from pcprodut where dtexclusao is null and revenda = 'S'";
        $rows = query4($sql);
        if(is_array($rows) && count($rows)){
            foreach ($rows as $row){
                if(!isset($this->_dados_brutos[$row['CODPROD']])){
                    $this->incluirProdutoWinthor($row['CODPROD']);
                }
            }
        }
        return $this->index();
    }
    
    private function incluirProdutoWinthor($cod_prod){
        $sql = "insert into gf_produtos_marketplace values (null, '$cod_prod', 'P')";
        query($sql);
    }
    
    private function getNomeProduto($cod_prod){
        $ret = 'sem nome';
        if(count($this->_nomes_produtos) == 0){
            $sql = "select CODPROD, DESCRICAO from pcprodut where dtexclusao is null and revenda = 'S'";
            $rows = query4($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $this->_nomes_produtos[$row['CODPROD']] = $row['DESCRICAO'];
                }
            }
        }
        if(isset($this->_nomes_produtos[$cod_prod])){
            $ret = $this->_nomes_produtos[$cod_prod];
        }
        return $ret;
    }
    
    private function incluirTodasIntegracoes(){
        //função para marcar todas as box
        $produtos = $this->getProdutos();
        foreach ($produtos as $prod){
            $this->deletarTodasIntegracoes($prod);
            $this->salvarIntegracao($prod, $this->_integracoes);
        }
    }
}