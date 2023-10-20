<?php
/*
 * Data Criacao: 05/04/2023
 * Autor: Alexandre Thiel
 *
 * Descricao: Realiza um de/para entre Operadores (televendedores) e seu respectivo cadastro de ERC
 * 
 * 
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class operador_erc{
	var $funcoes_publicas = array(
		'index' 		=> true,
	    'excluir'       => true,
	    'incluir'       => true,
	    'salvar'        => true,
	);
	
	//Titulo
	private $_titulo;
	
	//Programa
	private $_programa;
	
	private $_operadores;
	private $_erc;
	
	function __construct(){
		$this->_titulo ='Operador X ERC';
		$this->_programa = get_class($this);
		
		
		$this->carregarERC();
		$this->carregarOperadores();
	}
	
	public function index(){
		$ret = '';

		/*
		var_dump($this->_operadores);
		
		echo '<br>------------------------<br>';
		
		var_dump($this->_erc);
		*/
		
		$param = array(
		    'programa' => $this->_programa,
		    'titulo' => $this->_titulo,
		);
		$tabela = new tabela01($param);
		$tabela->addColuna(array('campo' => 'operador'	, 'etiqueta' => 'Operador'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		$tabela->addColuna(array('campo' => 'erc'	    , 'etiqueta' => 'ERC'       , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'esquerda'));
		
		$acao = [];
		$acao['texto'] 	= 'Excluir';
		$acao['coluna'] = 'id';
		$acao['link'] 	= getLink() . 'excluir&id=';
		$acao['cor'] = 'danger';
		$tabela->addAcao($acao);
		
		$botao = [];
		$botao['texto'] 	= 'Incluir';
		$botao['url'] 	= getLink() . 'incluir';
		$botao['tipo'] = 'link';
		//$botao['cor'] = 'danger';
		$tabela->addBotaoTitulo($botao);
		
		$dados = $this->getDados();
		$tabela->setDados($dados);
		
		$ret .= $tabela;
		
		return $ret;
	}
	
	private function getDados(){
	    $ret = array();
	    $sql = "select id, operador, erc from gf_operador_erc where del != '*'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        foreach ($rows as $row){
	            $temp = array(
	                'id' => base64_encode($row['id']),
	                'operador' => $this->_operadores[$row['operador']] ?? 'Sem Nome',
	                'erc' => $this->_erc[$row['erc']] ?? 'Sem Nome',
	            );
	            
	            $ret[] = $temp;
	        }
	    }
	    return $ret;
	}
	
	private function carregarOperadores($inativos = false){
	    $sql = "
			SELECT
			    MATRICULA,
			    NOME
			FROM
			    PCEMPR
			WHERE
			    CODPERFILTELEVMED IS NOT NULL
			";
	    
	    if(!$inativos){
	        $sql .= "AND SITUACAO = 'A' AND DTDEMISSAO IS NULL";
	    }
	    //echo "SQL: $sql<br>\n";
	    $rows = query4($sql);
	    
	    if(is_array($rows) && count($rows) > 0){
	        foreach ($rows as $row){
	        	$this->_operadores[$row['MATRICULA']] = $row['MATRICULA'].' - '.$row['NOME'];
	        }
	    }
	}
	
	private function carregarERC($inativos = false){
	    $lista = getListaEmailGF('rca',$inativos);
	    if(is_array($lista) && count($lista) > 0){
	        foreach ($lista as $l){
	        	$this->_erc[$l['rca']] = $l['rca'].' - '.$l['nome'];
	        }
	    }
	}
	
	
	
	public function incluir(){
	    $ret = '';
	    
	    //$this->limparOpcoes();
	    
	    $form = new form01();
	    
	    $form->addCampo(array('id' => '', 'campo' => 'formInc[operador]' , 'etiqueta' => 'Operador' , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'lista' => $this->criarOpcoesOperador()		, 'validacao' => '', 'obrigatorio' => true, 'maxtamanho' => 60));
	    $form->addCampo(array('id' => '', 'campo' => 'formInc[erc]'      , 'etiqueta' => 'ERC'      , 'linha' => 1, 'largura' =>4, 'tipo' => 'A'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'lista' => $this->criarOpcoesErc()			, 'validacao' => '', 'obrigatorio' => true, 'maxtamanho' => 60));
	    
	    $form->setEnvio(getLink() . 'salvar', 'formInc', 'formInc');
	    
	    $ret .= $form;
	    
	    $ret = addCard(array('titulo' => 'Incluir Registro', 'conteudo' => $ret));
	    
	    return $ret;
	}
	
	private function limparOpcoes(){
	    $sql = "select operador, erc from gf_operador_erc where del != '*'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        foreach ($rows as $row){
	            unset($this->_operadores[$row['operador']]);
	            unset($this->_erc[$row['erc']]);
	        }
	    }
	}
	
	private function criarOpcoesOperador(){
	    $ret = array(array('', ''));
	    if(is_array($this->_operadores) && count($this->_operadores) > 0){
	        foreach ($this->_operadores as $codigo => $nome){
	            $ret[] = array($codigo, $nome);
	        }
	    }
	    return $ret;
	}
	
	private function criarOpcoesErc(){
	    $ret = array(array('', ''));
	    if(is_array($this->_erc) && count($this->_erc) > 0){
	        foreach ($this->_erc as $codigo => $nome){
	            $ret[] = array($codigo, $nome);
	        }
	    }
	    return $ret;
	}
	
	public function excluir(){
	    $id = $_GET['id'] ?? '';
	    if(!empty($id)){
	        $id = base64_decode($id);
	        $campos = array(
	            'del' => '*',
	            'user_del' => getUsuario(),
	        );
	        $sql = montaSQL($campos, 'gf_operador_erc', 'UPDATE', "id = $id");
	        query($sql);
	        $this->gerarLog('excluir', '', '', $id);
	        addPortalMensagem('Registro excluido com sucesso');
	    }
	    else{
	        log::gravaLog('operadorXerc', "o usuario " . getUsuario() . " tentou excluir um registro mas o id veio vazio");
	        addPortalMensagem('Não foi possivel excluir o Registro', 'error');
	    }
	    redireciona(getLink() . 'index');
	}
	
	private function gerarLog($operacao, $operador, $erc, $id = ''){
	    $texto = '';
	    switch ($operacao){
	        case 'excluir':
	            $sql = "select * from gf_operador_erc where id = $id";
	            log::gravaLog('operadorXerc', $sql);
	            $rows = query($sql);
	            if(is_array($rows) && count($rows) > 0){
	                $texto = "o usuario " . getUsuario() . " excluiu a entrada id: $id operador: ({$rows[0]['operador']}) " . ($this->_operadores[$rows[0]['operador']] ?? 'Sem Nome') . " ERC: ({$rows[0]['erc']}) " . ($this->_erc[$rows[0]['erc']] ?? 'Sem Nome');
	            }
	            else{
	                $texto = "ERRO o usuario " . getUsuario() . " tentou excluir a entrada de id $id mas as mesma não existe";
	            }
	            break;
	        case 'incluir':
	            $sql = "select * from gf_operador_erc where operador = $operador and erc = $erc";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    $texto = "o usuario " . getUsuario() . " incluiu a entrada id: " . $rows[0]['id'] . " operador: ($operador) " . ($this->_operadores[$operador] ?? 'Sem Nome') . " ERC: ($erc) " . ($this->_erc[$erc] ?? 'Sem Nome');
                }
                else{
                    $texto = "ERRO o usuario " . getUsuario() . " tentou incluir a entrada operador: ($operador) " . ($this->_operadores[$operador] ?? 'Sem Nome') . " ERC: ($erc) " . ($this->_erc[$erc] ?? 'Sem Nome') . " mas acorreu algum erro";
                }
	            break;
	        case 'alterar':
	            $sql = "select * from gf_operador_erc where operador = $operador and erc = $erc";
	            $rows = query($sql);
	            if(is_array($rows) && count($rows) > 0){
	                $texto = "o usuario " . getUsuario() . " alterou a entrada id: " . $rows[0]['id'] . " operador: ($operador) " . ($this->_operadores[$operador] ?? 'Sem Nome') . " ERC: ($erc) " . ($this->_erc[$erc] ?? 'Sem Nome');
	            }
	            else{
	                $texto = "ERRO o usuario " . getUsuario() . " tentou alterar a entrada operador: ($operador) " . ($this->_operadores[$operador] ?? 'Sem Nome') . " ERC: ($erc) " . ($this->_erc[$erc] ?? 'Sem Nome') . " mas acorreu algum erro";
	            }
	            break;
            default:
                break;
	    }
	    if(!empty($texto)){
	        log::gravaLog('operadorXerc', $texto);
	    }
	}
	
	public function salvar(){
	    $dados = $_POST['formInc'] ?? '';
	    $erro = true;
	    if(!empty($dados)){
	        $operador = $dados['operador'] ?? '';
	        $erc = $dados['erc'] ?? '';
	        if(!empty($operador) && !empty($erc)){
	            $sql = "select * from gf_operador_erc where operador = $operador and erc = $erc";
	            $rows = query($sql);
	            if(is_array($rows)){
	                $erro = false;
	                if(count($rows) > 0){
	                    //já existe
	                    $campos = array(
	                        'del' => '',
	                        'user_alt' => getUsuario(),
	                    );
	                    $sql = montaSQL($campos, 'gf_operador_erc', 'UPDATE', "operador = $operador and erc = $erc");
	                    query($sql);
	                    
	                    $this->gerarLog('alterar', $operador, $erc);
	                }
	                else{
	                    //não existe
	                    $campos = array(
	                        'operador' => $operador,
	                        'erc'      => $erc,
	                        'user_inc' => getUsuario(),
	                        'user_del' => '',
	                        'user_alt' => '',
	                    );
	                    $sql = montaSQL($campos, 'gf_operador_erc');
	                    query($sql);
	                    
	                    $this->gerarLog('incluir', $operador, $erc);
	                }
	            }
	        }
	        else{
	            log::gravaLog('operadorXerc', "ERRO o usuario " . getUsuario() . " tentou incluir mas um ou mais campos vieram vazios");
	        }
	    }
	    else{
	        log::gravaLog('operadorXerc', "ERRO o usuario " . getUsuario() . " tentou incluir mas o POST veio vazio");
	    }
	    
	    if($erro === false){
	        addPortalMensagem('Registro incluido com sucesso');
	        redireciona(getLink() . 'index');
	    }
	    else{
	        addPortalMensagem('Não foi possivel incluir o Registro', 'error');
	        redireciona(getLink() . 'incluir');
	    }
	}
}