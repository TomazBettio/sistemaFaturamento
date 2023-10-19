<?php
/*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class menu {
	//Modulos do usuario
	private $_modulos = [];
	
	//Programas do usuario
	private $_programas = [];
	
	public function __construct() {
		$this->getModulos();
		$this->getProgramas();
	}
	
	public function __toString(){
		global $nl;
		$ret = '';
		
		if (count($this->_modulos) == 0|| count($this->_programas) == 0) {
			return $ret;
		}
		
		$ret .= '<nav class="mt-2">'.$nl;
		$ret .= '	<ul class="nav nav-pills nav-sidebar flex-column text-sm nav-flat nav-compact" data-widget="treeview" role="menu" data-accordion="false">'.$nl;
		
		foreach ($this->_modulos as $modulo){
			$mod = $modulo['nome'];
			if(isset($this->_programas[$mod]) && count($this->_programas[$mod]) > 0){
				$sub = '';

				$icone 	= (isset($modulo['icone']) && !empty($modulo['icone'])) ? $modulo['icone']: 'fa-link';
				$titulo = (isset($modulo['etiqueta'])) ? (string) $modulo['etiqueta']: '';
				
				foreach ($this->_programas[$mod] as $programa){
					$sub .= $this->programa($programa);
					//TODO: verificar como fazer para o programa ativo estar marcado como ativo
					//$selecionado = $this->_menu == $programa['programa'] ? 'class="active"' : $selecionado;
				}
				$ret .= '		<li class="nav-item">'.$nl;
				$ret .= '			<a href="#" class="nav-link">'.$nl;
				$ret .= '				<i class="nav-icon fa '.$icone.'"></i><p>'.$titulo.'<i class="right fa fa-angle-left"></i></p>'.$nl;
				$ret .= '   		</a>'.$nl;
				$ret .= '			<ul class="nav nav-treeview" style="display: none;">'.$nl;
				$ret .= $sub;
				$ret .= '			</ul>'.$nl;
				$ret .= '		</li>'.$nl;
			}
		}
		$ret .= '	</ul>'.$nl;
		$ret .= '</nav>'.$nl;
		
		
		return $ret;
	}
	
	private function programa($programa) {
		global  $config, $nl;
		$ret = '';
		
		$url 	= (isset($programa['programa']) && !empty($programa['programa'])) 	? 'index.php?menu='.$programa['programa']: $config['url_error404'];
		$icone 	= (isset($programa['icone']) && !empty($programa['icone'])) 		? (string) $programa['icone']: 'fa-circle-o';
		$titulo = (isset($programa['etiqueta'])) 									? (string) $programa['etiqueta']: '';
		
		
		$ret .= '				<li class="nav-item">'.$nl;
		$ret .= '					<a href="'.$url.'" class="nav-link">'.$nl;
		$ret .= '						<i class="fa '.$icone.' nav-icon"></i>'.$nl;
		$ret .= '						<p>'.$titulo.'</p>'.$nl;
		$ret .= '					</a>'.$nl;
		$ret .= '				</li>'.$nl;
		
		return $ret;
	}
	
	private function getModulos(){
		$nivel = getUsuario('nivel');
		$tipo = getUsuario('tipo');
		
		$sql = "SELECT * FROM app001 WHERE ativo = 'S' ";
		if($tipo != "S"){
			$sql .= " AND nivel <= $nivel";
		}
		$sql .= " ORDER BY ordem";
		//echo "$sql <br>\n";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				
				$temp['nome'] 		= $row['nome'];
				$temp['etiqueta'] 	= $row['etiqueta'] ;
				$temp['programa']	= $row['programa'];
				$temp['icone'] 		= $row['icone'];
				$temp['ativo'] 		= $row['ativo'] == 'S' ? true : false;
				
				$this->_modulos[] = $temp;
			}
			$this->_modulos = traducoes::traduzirApp001($this->_modulos);
		}
	}
	
	private function getProgramas(){
		$tipoUser = getUsuario('tipo');
		$nivel = getUsuario('nivel');
		
		$sql  = "SELECT * FROM app002, sys115 ";
		$sql .= "WHERE app002.ativo = 'S' ";
		if($tipoUser != "S"){
			$sql .= " AND nivel < 900 ";
		}
		$sql .= "	AND sys115.programa = app002.programa ";
		$sql .= "	AND sys115.user = '".getUsuario()."' ";
		$sql .= "	AND sys115.perm = 'S' ";
		$sql .= "	AND app002.nivel <= $nivel ";
		$sql .= "ORDER BY app002.modulo, app002.etiqueta";
//echo "$sql <br>\n";
		$rows = query($sql);
		if(count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp["etiqueta"] 	= $row["etiqueta"];
				$temp["programa"] 	= $row["programa"];
				$temp["icone"] 		= $row["icone"];
				
				$this->_programas[$row["modulo"]][] = $temp;
			}
			$this->_programas = traducoes::traduzirApp002($this->_programas);
		}
	}
}

