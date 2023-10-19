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

	
	function formulario($programa, $titulo = ''){
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
			$form->setEnvio(getLink().'index.sysParametrosGravar', 'parametrosForm', 'parametrosForm');
			
			$form->addBotaoTitulo(formbase01::formSendParametros());
			
			$p = array();
			$p['onclick'] = "setLocation('".getLink()."index')";
			$p['tamanho'] = 'pequeno';
			$p['cor'] = 'warning';
			$p['texto'] = 'Cancelar';
			$form->addBotaoTitulo($p);
			
			foreach ($parametros as $parametro){
				if($parametro['ativo'] == 'S'){
					$param = array();
					$param['campo']			= $parametro['parametro'];
					$param['etiqueta']		= $parametro['descricao'];
					$param['tipo']			= $parametro['tipo'];
//					$param['tamanho']		= '';
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
	
	static function inclui($dados, $debug = false){
		if(isset($dados['programa']) && isset($dados['parametro']) && !empty($dados['programa']) && !empty($dados['parametro'])){
			$sql = "SELECT * FROM sys020 WHERE empresa = '".getEmp()."' AND cliente = '".getCliente()."' AND programa = '".$dados['programa']."' AND parametro = '".$dados['parametro']."' AND ativo = 'S'";
			$rows = query($sql);
//echo "$sql <br>\n";
			if(count($rows) == 0){
				$dados['linhas'] = isset($dados['linhas']) && !empty($dados['linhas']) ? $dados['linhas'] : '0';
				$dados['opcoes'] = isset($dados['opcoes']) && !empty($dados['opcoes']) ? $dados['opcoes'] : '0';
				$dados['usuario'] = funcoesusuario::getUsuario();
				$dados['versao'] = self::getNovaVersao($dados['programa'], $dados['parametro']);
				$id = geraID('sys020');
				
				$valor  	= escapeQuery($dados['valor']);
				$help		= isset($dados['help']) ? escapeQuery($dados['help']) : '';
				$descricao 	= escapeQuery($dados['descricao']);
				
				$seq = 0;
				if(isset($dados['seq'])){
					$seq = $dados['seq'];
				}else{
					$sql = "SELECT MAX(seq) FROM sys020 WHERE empresa = '".getEmp()."' AND cliente = '".getCliente()."' AND programa = '".$dados['programa']."' ";
					$rows = query($sql);
					$seq = $rows[0][0] + 1;
				}
				
				$campos = [];
				$campos['id'] 			= $id;
				$campos['cliente'] 		= getCliente();
				$campos['empresa'] 		= getEmp();
				$campos['programa'] 	= $dados['programa'];
				$campos['parametro'] 	= $dados['parametro'];
				$campos['versao'] 		= $dados['versao'];
				$campos['seq'] 			= $seq;
				$campos['tipo'] 		= $dados['tipo'];
				$campos['config'] 		= $dados['config'];
				$campos['descricao'] 	= $descricao;
				$campos['valor'] 		= $valor;
				$campos['linhas'] 		= $dados['linhas'];
				$campos['opcoes'] 		= $dados['opcoes'];
				$campos['usuario'] 		= $dados['usuario'];
				$campos['help'] 		= $help;
				$campos['ativo'] 		= 'S';
				
				$sql = montaSQL($campos, 'sys020');
//echo "$sql <br>\n";
				query($sql);
			}else{
				if($debug){
					echo "Parâmetros já existe no cadastro SYS020<br>\n";
				}
			}
		}else{
			if($debug){
				echo "Parâmetros incorretos no cadastro SYS020<br>\n";
				print_r($dados);
			}
		}
		return '';
	}
	
	function atualiza($programa, $campo, $valor){
		if(!empty(trim($programa)) && !empty(trim($campo))){
			$valoresAntigos = $this->getParametros($programa, $campo);
			$key = count($valoresAntigos) -1;
			if($key < 0){
				$key = 0;
			}
			$dados = array();
			foreach ($this->_campos as $c){
				$dados[$c] = isset($valoresAntigos[$key][$c]) ? $valoresAntigos[$key][$c] : '';
			}
			
			//Só modifica se o valor for diferente
			if($valor != $dados['valor']){
				$dados['valor'] = $valor;
				
				$sql = "UPDATE sys020 SET ativo = 'N' WHERE empresa = '".getEmp()."' AND cliente = '".getCliente()."' AND programa = '$programa' AND parametro = '$campo'";
				query($sql);
//echo "$sql <br>\n";	
				self::inclui($dados);
			}
		}
	}
	
	function gravaFormulario($programa){
		$param = $_POST;
		if(count($parametros) > 0){
			foreach ($parametros as $parametro){
				$campo = $parametro['parametro'];
				$valor = isset($param[$campo]) ? $param[$campo] : '';
				
				if($parametro['tipo'] == 'S' && $valor == '***************'){
					
				}else{
					$this->atualiza($programa, $campo, $valor);
				}
			}
		}
	}
	

	static private function getNovaVersao($programa, $parametro){
		$ret = 1;
		$sql = "SELECT MAX(versao) FROM sys020 WHERE empresa = '".getEmp()."' AND cliente = '".getCliente()."' AND programa = '".$programa."' AND parametro = '".$parametro."'";
		$rows = query($sql);
		$ret += $rows[0][0];
		return $ret;
	}
}
