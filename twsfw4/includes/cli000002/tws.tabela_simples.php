<?php
/*
 * Data Criacao 11/09/18
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao: Tabela simples
 */
 
class tabela_simples{
	
	//Colunas
	private $_colunas = array();
	
	//dados
	private $_dados;
	
	//Titulo
	private $_titulo;
	
	//bordered
	private $_bordered;
	
	//striped
	private $_striped;
	
	//hover
	private $_hover;
	
	//condensed
	private $_condensed;
	
	//responsive
	private $_responsive;
	
	function __construct($param = array()){
		$parametrosPadroes = array(
				'bordered'	=> true,
				'striped' 	=> false,
				'hover'		=> true,
				'responsive'=> false,
				'condensed'	=> true,
				'titulo'	=> '',
		);
		$param = mesclaParametros($parametrosPadroes, $param);
		
		$this->_bordered 	= $param['bordered'];
		$this->_striped 	= $param['striped'];
		$this->_hover		= $param['hover'];
		$this->_responsive 	= $param['responsive'];
		$this->_condensed	= $param['condensed'];
		$this->_titulo		= $param['titulo'];
		//table-striped
		//table-hover
	}
	
	function __toString(){
		global $nl;
		$ret = '';
		$classe = '';
		if($this->_bordered){
			$classe .= ' table-bordered';
		}
		if($this->_hover){
			$classe .= ' table-hover';
		}
		if($this->_striped){
			$classe .= ' table-striped';
		}
		if($this->_condensed){
			$classe .= ' table-condensed';
		}
		
		$ret .= '<table class="table'.$classe.'">'.$nl;
		if(count($this->_colunas) > 0){
			$ret .= $this->impCabecalho();
		}
		$ret .= $this->impDados();
		$ret .= '</table>'.$nl;
		
		if($this->_responsive){
			$temp = '<div class="box-body table-responsive no-padding">'.$nl;
			$temp .= $ret;
			$temp .= '</div>'.$nl;
			
			$ret = $temp;
		}
		
		if(!empty($this->_titulo)){
			$param = array();
			$ret = addBoxInfo($this->_titulo, $ret, $param);
		}
		
		return $ret;
	}
	
	function addColuna($param){
		$campo = '';
		if(!isset($param['campo']) || $param['campo'] == ''){
			$param['campo'] = count($this->_cab);
		}
		
		$this->_colunas[$param['campo']] = $param;
	}
	
	function addDados(&$dados){
		$this->_dados = $dados;
	}	
	
	private function impCabecalho(){
		global $nl;
		$ret = '';
		
		$ret .= '<thead>'.$nl;
		$ret .= '	<tr>'.$nl;
		if(count($this->_colunas) > 0){
			foreach ($this->_colunas as $chave => $coluna){
				
				$w = '';
				if(isset($coluna['width']) && $coluna['width'] > 0){
					$w = ' style="width: '.$coluna['width'].'px"';
				}
				$ret .= '		<th'.$w.'>'.$coluna['etiqueta'].'</th>'.$nl;
			}
		}
		$ret .= '	</tr>'.$nl;
		$ret .= '</thead>'.$nl;
		
		return $ret;
	}
	private function impDados(){
		global $nl;
		$ret = '';
		
		if(count($this->_dados) > 0){
			foreach ($this->_dados as $i => $dado){
				$ret .= '	<tr>'.$nl;
				foreach ($this->_colunas as $chave => $coluna){
					$tipo = $coluna['tipo'];
					$valorCampo = isset($dado[$coluna['campo']]) ? $dado[$coluna['campo']] : '';
					
					switch ($tipo){
						case "V":
							//Valor (duas casas decimais)
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 2, ',', '.');
							}
							break;
						case "V4":
							//Valor (quatro casas decimais)
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 4, ',', '.');
							}
							break;
						case "N":
							//Valor inteiro
							if($valorCampo == "" || !$valorCampo) $valorCampo = 0;
							if(!preg_match("/([a-zA-Z])/", $valorCampo ) ){
								$valorCampo = number_format($valorCampo, 0, ',', '.');
							}
							break;
						case "T":
							//Texto
							$valorCampo = ajustaCaractHTML($valorCampo);
							break;
						case "D":
							//Data
							if($valorCampo != '' && $valorCampo != 0){
								$valorCampo = datas::dataS2D($valorCampo);
							}else{
								$valorCampo = '';
							}
							break;
					}
					switch ($coluna['posicao']) {
						case "D":
							$pos = 'right';
							break;
						case "C":
							$pos = 'center';
							break;
						case "J":
							$pos = 'justify';
							break;
						default:
							$pos = 'left';
							break;
					}

					$ret .= '		<td align="'.$pos.'">'.$valorCampo.'</td>'.$nl;
				}
				$ret .= '	</tr>'.$nl;
			}
		}

		return $ret;
	}
}
 