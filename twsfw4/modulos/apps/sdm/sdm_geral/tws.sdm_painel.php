<?php
/*
 * Data Criacao: 12/01/2022
 * Autor: Verticais - Thiel
 *
 * Descricao: Painel 
 *
 * Alteracoes;
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class sdm_painel{
	var $funcoes_publicas = array(
			'index' 		=> true,
	);
	
	//Titulo
	private $_titulo;
	
	//Programa
	private $_programa;
	
	function __construct(){
		$this->_titulo ='Painel';
		$this->_programa = get_class($this);

	}
	
	function index(){
		global $pagina;
		$ret = '';
		
		//$pagina->setTitulo('Painel SDN', '');
		

		
		$param1 = [];
		$param1['titulo'] = 'Tickets';
		$param1['conteudo'] = '';
		
		$param2 = [];
		$param2['titulo'] = 'OSs';
		$param2['conteudo'] = '';
		
		$param3 = [];
		$param3['titulo'] = 'OSs';
		$param3['conteudo'] = '';
		
		$param4 = [];
		$param4['titulo'] = 'Agendas';
		$param4['conteudo'] = '';
		
		
		$param = [];
		$param['tamanhos'] = [3,3,3,3];
		$param['conteudos'] = [addCard($param1), addCard($param2), addCard($param3), addCard($param4)];
		$ret = addLinha($param);
		
		$ret .= $this->montaNumeros();
		$ret .= $this->montaLinha2();
		
		return $ret;
	}
	
	
	//------------------------------------------------------------------------- UI ----------------------------
	
	private function montaLinha2(){
		$ret = '';
		
		$param = array();
		$p = [];
		$p['onclick'] = "setLocation('".$this->_linkCancela."')";
		//$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Cancelar';
		$param['botoesTitulo'][] = $p;
		
		$p = [];
		//		$p['icone'] 	= 'fa-arrow-right';
		$p['cor'] 		= 'primary';
		$p['texto'] 	= 'Salvar';
		//$p['tamanho'] = 'pequeno';
		$p['id'] 		= 'bt_salvar';
		$p["onclick"]	= "$('#formEditor').submit()";
		$param['botoesTitulo'][] = $p;
		
		$param['class'] = 'card card-danger card-outline';
		
		$col2 = addCard('Agendas Marcadas', $ret, $param);
		
		$col1 = cardNew();
		
		$param = [];
		$param['tamanhos'] = [6,6];
		$param['conteudos'] = [$col1, $col2];
		$ret .= addLinha($param);
		return $ret;
	}
	
	private function montaNumeros(){
		global $nl;
		$ret = '';
		$quantidades = $this->getQuantidadesChamados();
		
		$ret .= '<div class="row">'.$nl;
		
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = 'bg-red';
		$param['valor'] = $quantidades['abertos'];
		$param['medida'] = '';
		$param['texto'] = 'Chamados Abertos';
		$param['icone'] = 'fa-plus-square';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'detalhes.000001';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = 'bg-orange';
		$param['valor'] = $quantidades['terceiros'];
		$param['medida'] = '';
		$param['texto'] = 'Aguarda Terceiros';
		$param['icone'] = 'fa-wrench';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'detalhes.000001';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = 'bg-orange';
		$param['valor'] = $quantidades['meus'];
		$param['medida'] = '';
		$param['texto'] = 'Aguarda Terceiros';
		$param['icone'] = 'fa-wrench';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'detalhes.000001';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		$ret .= '<div class="col-lg-3 col-6">'.$nl;
		$param = [];
		$param['cor'] = 'bg-orange';
		$param['valor'] = $quantidades['atrasados'];
		$param['medida'] = '';
		$param['texto'] = 'Aguarda Terceiros';
		$param['icone'] = 'fa-wrench';
		$param['footer'] = 'Detalhes';
		$param['link'] = getLink().'detalhes.000001';
		$box = boxPequeno($param);
		$ret .= $box;
		$ret .= '</div>'.$nl;
		
		
		$param = [];
		$param['titulo'] = 'Tickets';
		$param['conteudo'] = $ret;
		
		$ret = addCard($param);
		return $ret;
	}
	
	private function getQuantidadesChamados(){
		$ret = [];
		
		//Chamados abertos
		$ret['abertos'] = 5;
		
		//Chamados aguardando terceiros
		$ret['terceiros'] = 3;
		
		//Chamados sob responsabilidade do usuário atual
		$ret['terceiros'] = 3;
		
		//Chamados aguardando terceiros
		$ret['atrasados'] = 3;
		
		return $ret;
	}
}

function cardNew(){
	$ret = '';
	
	$ret = '
	<div class="card">
	<div class="card-header border-0">
	<h3 class="card-title">Products</h3>
	<div class="card-tools">
	<a href="#" class="btn btn-tool btn-sm">
	<i class="fas fa-download"></i>
	</a>
	<a href="#" class="btn btn-tool btn-sm">
	<i class="fas fa-bars"></i>
	</a>
	</div>
	</div>
	<div class="card-body table-responsive p-0">
	<table class="table table-striped table-valign-middle">
	<thead>
	<tr>
	<th>Product</th>
	<th>Price</th>
	<th>Sales</th>
	<th>More</th>
	</tr>
	</thead>
	<tbody>
	<tr>
	<td>
	<img src="dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
	Some Product
	</td>
	<td>$13 USD</td>
	<td>
	<small class="text-success mr-1">
	<i class="fas fa-arrow-up"></i>
	12%
	</small>
	12,000 Sold
	</td>
	<td>
	<a href="#" class="text-muted">
	<i class="fas fa-search"></i>
	</a>
	</td>
	</tr>
	<tr>
	<td>
	<img src="dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
	Another Product
	</td>
	<td>$29 USD</td>
	<td>
	<small class="text-warning mr-1">
	<i class="fas fa-arrow-down"></i>
	0.5%
	</small>
	123,234 Sold
	</td>
	<td>
	<a href="#" class="text-muted">
	<i class="fas fa-search"></i>
	</a>
	</td>
	</tr>
	<tr>
	<td>
	<img src="dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
	Amazing Product
	</td>
	<td>$1,230 USD</td>
	<td>
	<small class="text-danger mr-1">
	<i class="fas fa-arrow-down"></i>
	3%
	</small>
	198 Sold
	</td>
	<td>
	<a href="#" class="text-muted">
	<i class="fas fa-search"></i>
	</a>
	</td>
	</tr>
	<tr>
	<td>
	<img src="dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
	Perfect Item
	<span class="badge bg-danger">NEW</span>
	</td>
	<td>$199 USD</td>
	<td>
	<small class="text-success mr-1">
	<i class="fas fa-arrow-up"></i>
	63%
	</small>
	87 Sold
	</td>
	<td>
	<a href="#" class="text-muted">
	<i class="fas fa-search"></i>
	</a>
	</td>
	</tr>
	</tbody>
	</table>
	</div>
	</div>';
	
	return $ret;
}