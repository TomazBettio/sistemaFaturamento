<?php
/*
 * Data Criacao 22/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class funcoesusuario{
	
	static function getClienteUsuario(){
		global $app;
		
		return $app->_usuario['cliente'];
	}
	
	static function getInfoUsuario($campo = 'nome',$usuario = ''){
		global $app;
		$ret = '';
		if($usuario == ''){
			$ret = isset($app->_usuario[$campo]) ? $app->_usuario[$campo] : '';
		}else{
			$sql = "SELECT * FROM sys001 WHERE user = '$usuario'";
			$rows = query($sql);
			
			if(isset($rows[0][$campo])){
				$ret = $rows[0][$campo];
			}
		}
		
		return $ret;
	}
	
	static function getUsuario($tipo = 'usuario'){
		global $app;
		if($tipo == 'id'){
			return $app->_userID;
		}else{
			return $app->_user;
		}
	}
	
	static function listaRecursos($ativo = true,$primeiroBranco = true){
		$usuarios = array();
		if ($ativo === true){
			$where = "ativo = 'S' ";
		}else{
			$where = "1=1 ";
		}
		
		$sql = "SELECT user, apelido FROM sys001 WHERE cliente = '".getCliente()."' AND ".$where." ORDER BY apelido";
		$rows = query($sql);
		
		if($primeiroBranco){
			$usuarios[0][0] = "";
			$usuarios[0][1] = "";
		}
		
		$i = count($usuarios);
		foreach ($rows as $row) {
			$usuarios[$i][0] = $row[0];
			$usuarios[$i][1] = utf8_encode($row[1]);
			$i++;
		}
		return $usuarios;
	}
}