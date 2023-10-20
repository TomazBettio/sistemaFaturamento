<?php
/*
 * Data Criacao: 30/08/18
 * Autor: Thiel
 *
 * Descricao: CRUD na tabela sys020 - Parãmetros de programas
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class sys020{
	
	//Campos do SYS020
	private $_camposSYS020;
	
	//Campos do SYS021
	private $_camposSYS021;
	
	function __construct(){
		$this->_camposSYS020 = ['id','programa','parametro','seq','tipo','config','descricao','linhas','opcoes','help','ativo'];
		
		$this->_camposSYS021 = ['id','programa','parametro','versao','valor'];
	}
	
	//---------------------------------------------------------------------------------- GET -------------------------------------------------
	function getParametros($programa, $parametro = ''){
		$ret = [];
		$whereParametro = '';
		if(!empty($parametro)){
			$whereParametro = " AND parametro = '$parametro'";
		}
		
		$sql = "SELECT
					sys020.*,
					sys021.*	
				FROM 
					sys020 
						JOIN sys021 using (id,programa,parametro) 
				WHERE
					sys020.programa = '$programa' 
					AND sys021.ativo = 'S' 
					$whereParametro 
				ORDER BY 
					sys020.seq, 
					sys021.versao";
		$rows = query($sql);
		
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				foreach ($this->_camposSYS020 as $campo){
					$temp[$campo] = $row[$campo];
				}
				foreach ($this->_camposSYS021 as $campo){
					$temp[$campo] = $row[$campo];
				}
				
				$ret[] = $temp;
			}
		}
		
		return $ret;
	}
	
	public function inclui($dados, $debug = false){
	    if(!empty($dados['programa']) && !empty($dados['parametro'])){
	        if(!$this->existeParametro($dados['programa'], $dados['parametro'])){
	            $this->criarRegistroSys020($dados, $debug);
	            $valor  = isset($dados['valor']) ? escapeQuery($dados['valor']) : '';
	            $this->atualiza($dados['programa'], $dados['parametro'], $valor);
	        }
	        else{
	            if($debug){
	                echo "Parâmetros já existe no cadastro SYS020<br>\n";
	            }
	        }
	    }
	    else{
	        if($debug){
	            echo "Parâmetros incorretos no cadastro SYS020<br>\n";
	            print_r($dados);
	        }
	    }
	}
	
	private function existeParametro($programa, $parametro){
	    $ret = false;
	    $sql = "SELECT * FROM sys020 WHERE programa = '$programa' AND parametro = '$parametro' AND ativo = 'S'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = true;
	    }
	    return $ret;
	}
	
	private function criarRegistroSys020($dados, $debug = false){
	    $help		= isset($dados['help']) ? escapeQuery($dados['help']) : '';
	    $descricao 	= escapeQuery($dados['descricao']);
	    
	    $seq = isset($dados['seq']) ? $dados['seq'] : $this->getProximoSeq($dados['programa']);
	    
	    $campos = [];
	    $campos['id'] 			= geraID('sys020');
	    $campos['programa'] 	= $dados['programa'];
	    $campos['parametro'] 	= $dados['parametro'];
	    $campos['seq'] 			= $seq;
	    $campos['tipo'] 		= $dados['tipo'];
	    $campos['config'] 		= $dados['config'];
	    $campos['descricao'] 	= $descricao;
	    $campos['linhas'] 		= $dados['linhas'] ?? 0;
	    $campos['opcoes'] 		= $dados['opcoes'] ?? 0;
	    $campos['help'] 		= $help;
	    $campos['ativo'] 		= 'S';
	    
	    $sql = montaSQL($campos, 'sys020');
	    if($debug){
            echo "$sql <br>\n";
	    }
	    query($sql);
	}
	
	private function getProximoSeq($programa){
	    $ret = 1;
	    $sql = "SELECT coalesce(MAX(seq), 0) as seq FROM sys020 WHERE programa = '".$programa."' ";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret += $rows[0]['seq'];
	    }
	    return $ret;
	}
	
	private function getNovaVersao($programa, $parametro){
	    return 1 + $this->getVersaoAtual($programa, $parametro);
	}
	
	private function getVersaoAtual($programa, $parametro){
	    $ret = 0;
	    $sql = "SELECT coalesce(MAX(versao), 0) as versao FROM sys021 WHERE programa = '".$programa."' AND parametro = '".$parametro."'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = $rows[0]['versao'];
	    }
	    return $ret;
	}
	
	private function getParametroByVersao($programa, $parametro, $versao){
	    $ret = '';
	    $sql = "SELECT valor FROM sys021 WHERE programa = '".$programa."' AND parametro = '".$parametro."' and versao = $versao";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = $rows[0]['valor'];
	    }
	    return $ret;
	}
	
	public function getParametroValor($programa, $parametro){
		$ret = '';
		$sql = "SELECT valor FROM sys021 WHERE programa = '".$programa."' AND parametro = '".$parametro."' ORDER BY  versao DESC LIMIT 1";
		$rows = query($sql);
		if(is_array($rows) && count($rows) > 0){
			$ret = $rows[0]['valor'];
		}
		return $ret;
	}
	
	function getParametroAtualizado($programa, $parametro){
	    $ret = '';
	    $versao_atual = $this->getVersaoAtual($programa, $parametro);
	    if($versao_atual > 0){
	        $ret = $this->getParametroByVersao($programa, $parametro, $this->getVersaoAtual($programa, $parametro));
	    }
	    return $ret;
	}
	
	private function getIdSys020($programa, $parametro){
	    $ret = '';
	    $sql = "select id from sys020 where programa = '$programa' and parametro = '$parametro'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = $rows[0]['id'];
	    }
	    return $ret;
	}
	
	function atualiza($programa, $parametro, $valor){
	    $sql = "update sys021 set ativo = 'N' where programa = '$programa' and parametro = '$parametro'";
	    query($sql);
	    $id = $this->getIdSys020($programa, $parametro);
	    if(!empty($id)){
	        $campos = array(
	            'id' => $id,
	            'programa' => $programa,
	            'parametro' => $parametro,
	            'versao' => $this->getNovaVersao($programa, $parametro),
	            'valor' => $valor,
	            'usuario' => getUsuario(),
	            'ativo' => 'S',
	        );
	        $sql = montaSQL($campos, 'sys021');
	        query($sql);
	    }
	}
	
	public function formulario($programa, $titulo = '', $link = ''){
	    $ret = '';
	    $parametros = $this->getParametros($programa);
//print_r($parametros);
	    if(count($parametros) > 0){
	        if(!empty($titulo)){
	            $titulo .= ' - ';
	        }
	        $param = array();
	        $param['tipoForm'] = 5;
	        $form = new form01($param);
	        $form->setDescricao($titulo.'Parâmetros');
	        formbase01::setLayout('horizontal-reduzido');
	        formbase01::setLayout('basico');
	        if(empty($link)){
	            $link = getLink().'index.sysParametrosGravar';
	        }
	        $form->setEnvio($link, 'parametrosForm', 'parametrosForm');
	        
	        foreach ($parametros as $parametro){
	            if($parametro['ativo'] == 'S'){
	                $param = array();
	                $param['campo']			= $parametro['parametro'];
	                $param['etiqueta']		= $parametro['descricao'];
	                $param['tipo']			= $parametro['tipo'];
	                $param['tamanho']		= $parametro['tamanho'] ?? 30;
	                $param['linhas']		= $parametro['linhas'];
	                //					$param['pasta']			= '';
	                $param['valor']			= $parametro['valor'];
	                //					$param['lista']			= '';
	                //					$param['validacao']		= '';
	                //					$param['obrigatorio']	= '';
	                $param['opcoes']			= $parametro['opcoes'];
	                //					$param['help']			= '';
	                //					$param['mascara']		= '';
	                //					$param['readonly']		= '';
	                
	                $form->addCampo($param);
	            }
	        }
	        
	        $ret .= $form;
	    }
	    
	    return $ret;
	}
	
	function gravaFormulario($programa){
	    $parametros = $this->getParametros($programa);
	    if(count($parametros) > 0){
	        foreach ($parametros as $parametro){
	            $campo = $parametro['parametro'];
	            $valor = isset($_POST[$campo]) ? $_POST[$campo] : '';
	            if($parametro['tipo'] == 'S' && $valor == '***************'){
	                
	            }
	            else{
	                $this->atualiza($programa, $campo, $valor);
	            }
	        }
	    }
	}
}