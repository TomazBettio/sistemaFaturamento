<?php
class desarquivar{
    var $funcoes_publicas = array(
        'index' 			=> true,
        'desarquivar'	    => true,
    	'auditoria'			=> true,
    );
    
    private $_titulo;
    private $_programa;
    
    //Path dos arquivos
    private $_path;
    
    //Colunas do arquivo de resultados
    private $_colunas;
    
    // NCMs
    private $_ncm = [];
    
    // CFOP
    private $_cfop = [];
    
    // Cliente Marpa
    private $_0140 = [];
    
    // Fornecedor
    private $_0150 = [];
    
    // Produto
    private $_0200 = [];
    
    // Nota Fiscal
    private $_C100 = [];
    
    // Item da Nota Fiscal
    private $_C170 = [];
    
    //Dados Atuais
    private $_dadosAtuais = [];
    
    //Dados Originais
    private $_dadosOriginais = [];
    
    public function __construct(){
    	global $config;
        $this->_titulo = 'Modelo Monofásico';
        $this->_programa = get_class($this);
        
        $this->_path = $config['pathUpdMonofasico'];
        
        $this->_colunas = [
        	'chv_nf',
        	'fornecedor',
        	'num_doc',
        	'data_emi',
        	'descr_item',
        	'ncm',
        	'cfop',
        	'ind_oper',
        	'num_item',
        	'itens_nota',
        	'vl_item',
        	'vl_desc',
        	'vl_base',
        	'aliq_pis',
        	'aliq_cofins',
        	'vl_final_pis',
        	'vl_final_cofins',
        	'vl_calc_final_pis',
        	'vl_calc_final_cofins',
        	'selecionado',
        	'qtd',
        	'cod_item',
        	'filial',
        	'cnpj_forn'
        ];
    }
    
    public function index(){
        $param = [];
        $param['titulo'] = $this->_titulo;
        $param['programa'] = $this->_programa;
        $tabela = new tabela01($param);
        
        $tabela->addColuna(array('campo' => 'id', 'etiqueta' => 'ID#', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'razao', 'etiqueta' => 'Razão', 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'cnpj', 'etiqueta' => 'CNPJ', 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'datactr', 'etiqueta' => 'Data CTR', 'tipo' => 'D', 'width' => 80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'contrato', 'etiqueta' => 'Contrato', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'status', 'etiqueta' => 'Status', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'usuario', 'etiqueta' => 'Usuario', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
        
        $get = [];
        $get['cnpj'] = 'cnpj';
        $get['contrato'] = 'contrato';
        $get['id'] = 'id';
        
        $param = array(
            'texto' => 'Desarquivar', //Texto no botão
            'link' => getLink() . 'desarquivar&cnpj=', //Link da página para onde o botão manda
            'coluna' => $get, //Coluna impressa no final do link
            // 'link' => 'razao',
            // 'coluna' => 'CLINOMEFANTASIA',
            'width' => 100, //Tamanho do botão
            'flag' => '',
            'tamanho' => 'pequeno', //Nenhum fez diferença?
            'cor' => 'warning', //padrão: azul; danger: vermelho; success: verde
            'pos' => 'F',
        );
        $tabela->addAcao($param);
        
        $param = array(
        	'texto' => 'Auditoria',
        	'link' => getLink() . 'auditoria&cnpj=',
        	'coluna' => $get,
        	'width' => 100,
        	'flag' => '',
        	'tamanho' => 'pequeno',
        	'cor' => 'info',
        	'pos' => 'F',
        );
        $tabela->addAcao($param);
        
        $dados = $this->getDados();
        $tabela->setDados($dados);
        
        return $tabela . '';
    }
    
    public function desarquivar(){
        if(isset($_GET['cnpj'])){
            $temp = explode('|', $_GET['cnpj']);
            $cnpj = $temp[0];
            $contrato = $temp[1];
            $id = $temp[2];
            
            $param = [];
            $param['status'] = 'em andamento';
            $param['data_alt'] = date('Y-m-d H:i:s');
            $sql = montaSQL($param, 'mgt_monofasico', 'UPDATE', "id = $id");
            query($sql);
            
            addPortalMensagem('Contrato desarquivado com sucesso');
        }
        else{
            addPortalMensagem('Não foi possível desarquivar o contrato', 'error');
        }
        
        redireciona(getLink() . 'index');
    }
    
    public function auditoria(){
    	$temp = explode('|', $_GET['cnpj']);
    	$cnpj = $temp[0];
    	$contrato = $temp[1];
    	$id = $temp[2];
    	
    	$path = $this->_path . $cnpj . '_' . $contrato.DIRECTORY_SEPARATOR.'arquivos'.DIRECTORY_SEPARATOR;
    	
    	if(isset($_GET['cnpj']) && is_file($path.'resultado_orig.vert')){
    		$dados = $this->realizarAuditoria($path);
    		
    		return $this->geraRelatorioAuditoria($dados);
    	}
    	else{
    		addPortalMensagem('Não foi possível identificar o contrato, favor tentar novamente!', 'error');
    	}
    	
    	redireciona(getLink() . 'index');
    }
    
    private function geraRelatorioAuditoria($dados){
    	$ret = '';
    	
    	$param = [];
    	$param['programa']	= 'Auditoria';
    	$relatorio = new relatorio01($param);
    	$relatorio->addColuna(array('campo' => 'chv_nf'		, 'etiqueta' => 'Chave NFE'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'num_item'	, 'etiqueta' => 'Item'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'num_doc'	, 'etiqueta' => 'DOC'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'data_emi'	, 'etiqueta' => 'Emissão'		, 'tipo' => 'D', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'fornecedor'	, 'etiqueta' => 'Fornecedor'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'descr_item'	, 'etiqueta' => 'Produto'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'ncm'		, 'etiqueta' => 'NCM'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'cfop'		, 'etiqueta' => 'CFOP'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'cod_item'	, 'etiqueta' => 'Cod.Prod.'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	$relatorio->addColuna(array('campo' => 'consta'		, 'etiqueta' => 'Atual/Orig.'	, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
    	
    	$relatorio->setDados($dados);
    	
    	$ret .= $relatorio;
    	
    	return $ret;
    }
    
    private function getDados()
    {
    	$ret = [];
    	
    	$sql = "SELECT * FROM mgt_monofasico where status = 'arquivado'";
    	$rows = query($sql);
    	
    	if (is_array($rows) && count($rows) > 0) {
    		foreach ($rows as $row) {
    			$temp = [];
    			$temp['id'] 			= $row['id'];
    			$temp['razao'] 		= $row['razao'];
    			$temp['cnpj'] 		= $row['cnpj'];
    			$temp['contrato'] 	= str_replace('/', '-',$row['contrato']);
    			$temp['datactr'] 	= $row['datactr'];
    			$temp['status'] 	= $row['status'];
    			$temp['usuario'] 	= $row['usuario'];
    			
    			$ret[] = $temp;
    		}
    	}
    	
    	return $ret;
    }

    private function realizarAuditoria($path){
    	$this->getNCM();
    	$this->getCFOP();
    	
    	$this->recuperarArquivos($path);
    	
		//Gera resultados atuais
    	$this->geraNovoArquivo();
    	
    	//Recupera arquivo de resultados
    	$this->recuperaResultado($path.'resultado_orig.vert');
    	
    	$dados = $this->comparaAtual();
    	
    	return $dados;
    }
    
    private function geraNovoArquivo(){
    	foreach ($this->_C100 as $nota) {
    		$chave = substr($nota['chv_nfe'], 0, 43);
    		$temp_notas = [];
    		if (isset($this->_C170[$chave])) {
    			foreach ($this->_C170[$chave] as $item) {
    				$codigo_ncm = $this->_0200[$item['cod_item']]['ncm'] . '';
    				if (isset($this->_ncm[$codigo_ncm]) && isset($this->_cfop[$item['cfop']])) {
    					// $checked = $row['status'] == 'S' ? 'checked' : '';
    					$temp = [];
    					$temp['chv_nf'] 			= $item['chv_nfe'];
    					$temp['num_doc'] 			= $nota['num_doc'];
    					$temp['data_emi'] 			= str_replace('-', '', $nota['data_emi']); // 2020-04-06
    					$temp['fornecedor'] 		= isset($this->_0150[$nota['cod_part']]['razao']) ? $this->_0150[$nota['cod_part']]['razao'] : $this->_0140[$nota['cod_part']]['razao'];
    					$temp['descr_item'] 		= $this->_0200[$item['cod_item']]['descr_item'];
    					$temp['ncm'] 				= $this->_0200[$item['cod_item']]['ncm'];
    					$temp['cfop'] 				= $item['cfop'];
    					$temp['ind_oper'] 			= $nota['ind_oper'] == '0' ? 'Entrada' : 'Saída';
    					$temp['num_item'] 			= $item['num_item'];
    					$temp['vl_item'] 			= empty($item['vl_item']) ? 0 : $item['vl_item'];
    					$temp['vl_desc'] 			= empty($item['vl_desc']) ? 0 : $item['vl_desc'];
    					$temp['vl_base'] 			= $temp['vl_item'] - $temp['vl_desc'];
    					$temp['aliq_pis'] 			= $this->_ncm[$this->_0200[$item['cod_item']]['ncm']]['aliq_pis'];
    					$temp['aliq_cofins'] 		= $this->_ncm[$this->_0200[$item['cod_item']]['ncm']]['aliq_cofins'];
    					$temp['vl_final_pis'] 		= $temp['vl_base'] * $temp['aliq_pis'] / 100;
    					$temp['vl_final_cofins'] 	= $temp['vl_base'] * $temp['aliq_cofins'] / 100;
    					$temp['vl_calc_final_pis'] 	= 0;//$this->calculaValorFinal($temp['vl_base'], $temp['aliq_pis'], $this->_cfop[$item['cfop']]['tipo']);
    					$temp['vl_calc_final_cofins'] = 0;//$this->calculaValorFinal($temp['vl_base'], $temp['aliq_cofins'], $this->_cfop[$item['cfop']]['tipo']);
    					$temp['selecionado'] 		= 'S';
    					$temp['qtd'] 				= $item['qtd'];
    					$temp['cod_item']			= $item['cod_item'];
    					$temp['filial'] 			= $nota['filial'];
    					$temp['cnpj_forn']			= isset($this->_0150[$nota['cod_part']]['cnpj']) ? $this->_0150[$nota['cod_part']]['cnpj'] : $this->_0140[$nota['cod_part']]['cnpj'];
    					
    					$temp_notas[] = $temp;
    				}
    			}
    			//conta quantos items entraram no ncm
    			if (count($temp_notas) > 0) {
    				foreach ($temp_notas as $tn) {
    					$tn['itens_nota'] = count($temp_notas) . '/' . count($this->_C170[$chave]);
    					$this->_dadosAtuais[$tn['chv_nf']][$tn['num_item']] = $tn;
    				}
    			}
    		}
    	}
    }
    
    private function recuperarArquivos($path)
    {
    	// 0140
    	$handle = fopen($path.'0140.vert', "r");
    	
    	if ($handle) {
    		while (!feof($handle)) {
    			$linha = fgets($handle);
    			$linha = str_replace(["\r\n","\n","\r"], '', $linha);
    			if (!empty($linha)) {
    				$sep = explode('|', $linha);
    				if (count($sep) > 0) {
    					$temp = [];
    					$temp['cod_part'] = $sep[1];
    					$temp['razao'] = $sep[2];
    					$temp['cnpj'] = $sep[3];
    					$this->_0140[$temp['cod_part']] = $temp;
    				}
    			}
    		}
    		fclose($handle);
    	} else {
    		addPortalMensagem('Erro ao abrir o arquivo 0140', 'error');
    	}
    	
    	
    	//0150
    	$handle = fopen($path.'0150.vert', "r");
    	
    	if ($handle) {
    		while (!feof($handle)) {
    			$linha = fgets($handle);
    			$linha = str_replace(["\r\n","\n","\r"], '', $linha);
    			if (!empty($linha)) {
    				$sep = explode('|', $linha);
    				if (count($sep) > 0) {
    					$temp = [];
    					$temp['cod_part'] = $sep[1] . '';
    					$temp['razao'] = $sep[2];
    					$temp['cnpj'] = $sep[3];
    					$this->_0150[$temp['cod_part']] = $temp;
    				}
    			}
    		}
    		fclose($handle);
    	} else {
    		addPortalMensagem('Erro ao abrir o arquivo 0150', 'error');
    	}
    	
    	
    	//0200
    	$handle = fopen($path.'0200.vert', "r");
    	
    	if ($handle) {
    		while (!feof($handle)) {
    			$linha = fgets($handle);
    			$linha = str_replace(["\r\n","\n","\r"], '', $linha);
    			if (!empty($linha)) {
    				$sep = explode('|', $linha);
    				if (count($sep) > 0) {
    					$temp = [];
    					$temp['cod_item'] = $sep[1] . '';
    					$temp['descr_item'] = $sep[2];
    					$temp['ncm'] = $sep[3];
    					$this->_0200[$temp['cod_item']] = $temp;
    				}
    			}
    		}
    		fclose($handle);
    	} else {
    		addPortalMensagem('Erro ao abrir o arquivo 0200', 'error');
    	}
    	
    	//C100
    	$handle = fopen($path.'C100.vert', "r");
    	
    	if ($handle) {
    		while (!feof($handle)) {
    			$linha = fgets($handle);
    			$linha = str_replace(["\r\n","\n","\r"], '', $linha);
    			if (!empty($linha)) {
    				$sep = explode('|', $linha);
    				if (count($sep) > 0 && $sep[0] == 'C100') {
    					$temp = [];
    					$temp['ind_oper'] = $sep[1];
    					$temp['cod_part'] = $sep[2];
    					$temp['num_doc'] = $sep[3];
    					$temp['chv_nfe'] = $sep[4];
    					$temp['data_emi'] = $sep[5];
    					$temp['total_bnf'] = $sep[6];
    					$temp['filial'] = $sep[7];
    					$this->_C100[] = $temp;
    				}
    			}
    		}
    		fclose($handle);
    	} else {
    		addPortalMensagem('Erro ao abrir o arquivo C100', 'error');
    	}
    	
    	//C170
    	$handle = fopen($path.'C100.vert', "r");
    	
    	if ($handle) {
    		while (!feof($handle)) {
    			$linha = fgets($handle);
    			$linha = str_replace(["\r\n","\n","\r"], '', $linha);
    			if (!empty($linha)) {
    				$sep = explode('|', $linha);
    				if (count($sep) > 0 && $sep[0] == 'C170') {
    					// echo $linha . '<br>' . "\n";
    					$temp = [];
    					$temp['num_item'] = $sep[1];
    					$temp['cod_item'] = $sep[2];
    					$temp['qtd'] = $sep[3];
    					$temp['vl_item'] = $sep[4];
    					$temp['vl_desc'] = $sep[5];
    					$temp['cfop'] = $sep[6];
    					$temp['cst'] = $sep[7];
    					$temp['aliq_pis'] = $sep[8];
    					$temp['aliq_cofins'] = $sep[9];
    					$temp['chv_nfe'] = $sep[10];
    					$this->_C170[substr($temp['chv_nfe'], 0, 43)][] = $temp;
    				}
    			}
    		}
    		fclose($handle);
    	} else {
    		addPortalMensagem('Erro ao abrir o arquivo C170', 'error');
    	}
    }

    
    private function recuperaResultado($arquivo)
    {
    	$linhas = file($arquivo);
    	
    	foreach ($linhas as $linha) {
    		$linha = str_replace(["\r\n","\n","\r"], '', $linha);
    		$linha = explode('|', $linha);
    		$temp = [];
    		foreach ($this->_colunas as $key => $coluna) {
    			$temp[$coluna] = isset($linha[$key]) ? $linha[$key] : '';
    		}
    		
    		$this->_dadosOriginais[$temp['chv_nf']][$temp['num_item']] = $temp;
    	}
    }
    
    /**
     * Realiza a comparação do original com os dados se o mesmo fosse executado hoje
     * 
     */
    private function comparaAtual(){
    	$ret = [];
    	
    	foreach ($this->_dadosAtuais as $nf => $notas){
    		foreach ($notas as $item => $dados){
    			if(isset($this->_dadosOriginais[$nf][$item])){
    				unset($this->_dadosAtuais[$nf][$item]);
    				unset($this->_dadosOriginais[$nf][$item]);
    			}
    		}
    		if(count($this->_dadosAtuais[$nf]) == 0){
    			unset($this->_dadosAtuais[$nf]);
    		}
    		if(count($this->_dadosOriginais[$nf]) == 0){
    			unset($this->_dadosOriginais[$nf]);
    		}
    	}

    	if(count($this->_dadosAtuais) > 0){
    		foreach ($this->_dadosAtuais as $nf => $notas){
    			foreach ($notas as $item => $dados){
    				$dados['consta'] = 'Atual';
    				$ret[] = $dados;
    			}
    		}
    	}
    	
    	if(count($this->_dadosOriginais) > 0){
    		foreach ($this->_dadosOriginais as $nf => $notas){
    			foreach ($notas as $item => $dados){
    				$dados['consta'] = 'Original';
    				$ret[] = $dados;
    			}
    		}
    	}
    	return $ret;
    }
    
    // ------------------------------------------------------------------------------ UTEIS ------------------
    private function getNCM()
    {
    	$sql = "SELECT ncm, aliq_pis, aliq_cofins FROM mgt_ncm WHERE ativo = 'S'";
    	$rows = query($sql);
    	foreach ($rows as $row) {
    		$temp = [];
    		$temp['ncm'] = $row['ncm'];
    		$temp['aliq_pis'] = $row['aliq_pis'];
    		$temp['aliq_cofins'] = $row['aliq_cofins'];
    		$this->_ncm[$temp['ncm']] = $temp;
    	}
    }
    
    private function getCFOP()
    {
    	$sql = "SELECT cfop, tipo FROM mgt_cfop WHERE ativo = 'S' and tipo in('I', 'C')";
    	$rows = query($sql);
    	foreach ($rows as $row) {
    		$temp = [];
    		$temp['cfop'] = $row['cfop'];
    		$temp['tipo'] = $row['tipo'];
    		$this->_cfop[$temp['cfop']] = $temp;
    	}
    }
}