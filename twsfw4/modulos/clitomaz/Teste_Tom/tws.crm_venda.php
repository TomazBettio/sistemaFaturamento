<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class crm_venda {

    var $funcoes_publicas = array(
        'index'             => true,
		'incluirNovo'		=> true,
        'salvar'            => true,
		'ajax'				=> true,
		'avisos'			=> true,
		'excluir'			=> true,
    );

    // Titulo relatório
    // private $_titulo;

	// Programa
	// private $_programa;

	// tabela
	private $_tabela;

	// Produtos cadastrados no banco
	private $_produtos;
    
    function __construct() {
		// $this->_titulo = 'Modelo CRM';
		// $this->_programa = get_class($this);

		$param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'CRM Pedidos';
		$this->_tabela = new tabela01($param);

		$this->addJS_ListaPedidos();
    }

	public function avisos() {
		$tipo = $_GET['tipo'] ?? '';
		if ($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->index();
	}

	public function index() {
		$ret = '';

		// MONTA E APRESENTA AS INFORMAÇÕES DA TABELA
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

		// Botão para incluir um novo pedido
		$param = array(
			'texto' => 'Incluir',
			'onclick' => "setLocation('" . getLink() . "incluirNovo')",
		);
		$this->_tabela->addBotaoTitulo($param);

		$param = array(
	        'texto' => 'Editar', //Texto no botão
	        'link' => getLink().'incluirNovo&id=', //Link da página para onde o botão manda
	        'coluna' => 'id', //Coluna impressa no final do link
	        'width' => 10, //Tamanho do botão
	        'flag' => '',
	        'tamanho' => 'pequeno', //Nenhum fez diferença?
	        'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
	    );
	    $this->_tabela->addAcao($param);

		$param = array(
	        'texto' => 'Excluir', //Texto no botão
	        'link' => getLink().'excluir&id=', //Link da página para onde o botão manda
	        'coluna' => 'id', //Coluna impressa no final do link
	        'width' => 10, //Tamanho do botão
	        'flag' => '',
	        'tamanho' => 'pequeno', //Nenhum fez diferença?
	        'cor' => 'danger', //padrão: azul; danger: vermelho; success: verde
	    );
	    $this->_tabela->addAcao($param);

		$ret .= $this->_tabela;

		return $ret;
	}

	private function montaColunas() {
		// $this->_tabela->addColuna(array('campo' => 'id', 'etiqueta' => 'ID', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Entidade', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'id_cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' => 280, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'assunto', 'etiqueta' => 'Assunto', 'tipo' => 'T', 'width' => 180, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'contatos', 'etiqueta' => 'Contato', 'tipo' => 'T', 'width' => 80, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'vendedores', 'etiqueta' => 'Responsável', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
		$this->_tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' => 40, 'posicao' => 'E'));
	}
	
	private function gerarSqlTeste(){
	    $ret = '';
	    $entidades = array('crm_lead', 'crm_contatos', 'crm_marcas', 'crm_organizacoes', 'crm_vendedores');
	    $sqls = array();
	    foreach ($entidades as $e){
	        $sqls[] = "select id, nome, '$e' as entidade from $e";
	    }
	    $ret = implode(' union ', $sqls);
	    $ret = "($ret) as temp_entidades";
	    return $ret;
	}

	private function getDados() {
		$ret = [];

		// $sql = "SELECT * FROM crm_pedido_cab";
		// $rows = query($sql);

		// $sql = "SELECT id, nome FROM crm_vendedores";
		// $vendedores = query($sql);

		// $sql = "SELECT id, nome FROM crm_contatos";
		// $contatos = query($sql);

		$sql = "SELECT p.id, p.cliente, p.id_cliente, p.assunto, p.descricao, crm_vendedores.nome as vendedor, crm_contatos.nome as contato, temp_entidades.nome as entidade_nome
				FROM crm_pedido_cab as p join crm_vendedores on (p.vendedores = crm_vendedores.id) 
				join crm_contatos on (p.contatos = crm_contatos.id) ";
		$sql .= " left join " . $this->gerarSqlTeste() . ' on (p.cliente = temp_entidades.entidade and p.id_cliente = temp_entidades.id)';
		$sql .= " where p.ativo = 'S'";
		$rows = query($sql);
		// $entidades = [];

		if(is_array($rows) && count($rows) > 0) {
			foreach($rows as $row) {
				$temp = [];
				$temp['id'] = base64_encode($row['id']);
				$temp['cliente'] = ucfirst(str_replace('_', ' ', str_replace('crm_', '', $row['cliente'])));
				$temp['id_cliente'] = $row['entidade_nome'];
				$temp['assunto'] = $row['assunto'];
				$temp['contatos'] = $row['contato'];
				$temp['vendedores'] = $row['vendedor'];
				$temp['descricao'] = $row['descricao'];
				
                /*
				if(array_key_exists($row['cliente'], $entidades) && count($entidades[$row['cliente']]) > 0) {
					foreach($entidades[$row['cliente']] as $entidade) {
						if($row['id_cliente'] == $entidade['id']) {
							$temp['id_cliente'] = $entidade['nome'];
						}
					}
				} else {
					$sql = "SELECT id, nome FROM ".$row['cliente'];
					$entidades[$row['cliente']] = query($sql);

					if(is_array($entidades[$row['cliente']]) && count($entidades[$row['cliente']]) > 0) {
						foreach($entidades[$row['cliente']] as $entidade) {
							if($row['id_cliente'] == $entidade['id']) {
								$temp['id_cliente'] = $entidade['nome'];
							}
						}
					}
				}
*/
				// if(is_array($vendedores) && count($vendedores) > 0) {
				// 	foreach($vendedores as $vendedor) {
				// 		if($row['vendedores'] == $vendedor['id']) {
				// 			$temp['vendedores'] = $vendedor['nome'];
				// 		}
				// 	}
				// }
				// if(is_array($contatos) && count($contatos) > 0) {
				// 	foreach($contatos as $contato) {
				// 		if($row['contatos'] == $contato['id']) {
				// 			$temp['contatos'] = $contato['nome'];
				// 		}
				// 	}
				// }

				$ret[] = $temp;
			}
		}

		return $ret;
	}

	public function ajax() {
	    $ret = array();
	    
	    $ret[] = array('valor' => '', 'etiqueta' => '');
	    $entidade = getParam($_GET, 'entidade', '');
		$entidade = base64_decode($entidade);
	    
	    if($entidade != ''){
	        $sql = "SELECT id, nome FROM $entidade where ativo = 'S'";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            foreach ($rows as $row){
	                $temp = array(
	                    'valor' => base64_encode($row['id']),
	                    'etiqueta' => $row['nome'],
	                );
	                $ret[] = $temp;
	            }
	        }
	    }
	    return json_encode($ret);
	}

    public function incluirNovo() {
		$this->addJqueryAjax();
		$id = $_GET['id'] ?? '';

        $ret = '';

		if(!empty($id)) {
			$id = base64_decode($id);

			$sql = "SELECT * FROM crm_pedido_cab WHERE id = $id";
			$pedido = query($sql);

			$sql = "SELECT * FROM crm_enderecos WHERE entidade = 'crm_pedido_cab' AND cod = ".$pedido[0]['id'];
			$endereco = query($sql);

			$sql = "SELECT * FROM crm_pedido_itens WHERE id_pedido = ".$pedido[0]['id'];
			$itens = query($sql);

			// $sql = "SELECT * FROM crm_pedido_cab pedido, crm_pedido_itens itens, crm_enderecos enderecos WHERE pedido.id = $id AND pedido.id = itens.id_pedido AND pedido.id = enderecos.cod";
			// $rows = query($sql);
			// print_r($rows);
		}
		// $id = base64_encode(time());

		$form = new form01();
		$form->addHidden('id', $id);

        // ================= PEDIDO CABEÇALHO =================
        $param = [];
		$param['id'] = 'inputOrigem';
		$param['campo'] = 'cliente';
		$param['etiqueta'] = 'Entidade';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['onchange'] = "callAjax();";
		$param['lista'] = [['', 'Selecione uma opção'], [base64_encode('crm_lead'), 'Lead'], [base64_encode('crm_contatos'), 'Contato'], [base64_encode('crm_marcas'), 'Marca'], [base64_encode('crm_organizacoes'), 'Organização'], [base64_encode('crm_vendedores'), 'Vendedor']];
		$param['valor'] = isset($pedido[0]['cliente']) ? base64_encode($pedido[0]['cliente']) : '';
		$form->addCampo($param);

        $param = [];
		$param['id'] = 'inputDestino';
		$param['campo'] = 'id_cliente';
		$param['etiqueta'] = 'ID Cliente';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = isset($pedido[0]['id_cliente']) ? base64_encode($pedido[0]['id_cliente']) : '0';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cond_pagamento';
		$param['etiqueta'] = 'Condição de pagamento';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['cond_pagamento'] ?? '';
		$form->addCampo($param);

		$param = [];
		$param['campo'] = 'assunto';
		$param['etiqueta'] = 'Assunto';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['assunto'] ?? '';
		$form->addCampo($param);


		$param = [];
		$param['campo'] = 'oportunidades';
		$param['etiqueta'] = 'Oportunidades';
		$param['largura'] = '4';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'crm_oportunidades|id|nome||ativo="S"';
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['oportunidade'] ?? '';
		$form->addCampo($param);

		$param = [];
		$param['campo'] = 'estagio_pedido';
		$param['etiqueta'] = 'Estágio Pedido';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['estagio_pedido'] ?? '';
		$form->addCampo($param);

		$param = [];
		$param['campo'] = 'validade';
		$param['etiqueta'] = 'Validade';
		$param['largura'] = '2';
		$param['tipo'] = 'D';
        $PARAM['mascara'] = 'D';
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['validade'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'contatos';
		$param['etiqueta'] = 'Contato';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'crm_contatos|id|nome||ativo="S"';
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['contatos'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'entrega';
		$param['etiqueta'] = 'Entrega';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['entrega'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'organizacoes';
		$param['etiqueta'] = 'Organização';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'crm_organizacoes|id|nome||ativo="S"';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['organizacoes'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'vendedores';
		$param['etiqueta'] = 'Responsável';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'crm_vendedores|id|nome||ativo="S"';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['vendedores'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'prazo_condicao';
		$param['etiqueta'] = 'Prazo e Condição';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['prazo_condicao'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'descricao';
		$param['etiqueta'] = 'Descrição';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['descricao'] ?? '';
		$form->addCampo($param);


        // ================ TABELA PARA ENDEREÇO ==================
        $param = [];
		$param['campo'] = 'seq';
		$param['etiqueta'] = 'Sequência';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['seq'] ?? '0';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'apelido';
		$param['etiqueta'] = 'Apelido';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['apelido'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'pais';
		$param['etiqueta'] = 'País';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['pais'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'tipo_log';
		$param['etiqueta'] = 'Tipo Log.';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['tipo_log'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'logradouro';
		$param['etiqueta'] = 'Logradouro';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['logradouro'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'nr';
		$param['etiqueta'] = 'N°';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['nr'] ?? '0';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'complemento';
		$param['etiqueta'] = 'Complemento';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['complemento'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'bairro';
		$param['etiqueta'] = 'Bairro';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['bairro'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cidade';
		$param['etiqueta'] = 'Cidade';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['cidade'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'estado';
		$param['etiqueta'] = 'Estado';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['estado'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cep';
		$param['etiqueta'] = 'CEP';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['cep'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'caixa_postal';
		$param['etiqueta'] = 'Caixa Postal';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['caixa_postal'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'obs';
		$param['etiqueta'] = 'Observações';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['pasta'] = 2;
		$param['valor'] = $endereco[0]['obs'] ?? '';
		$form->addCampo($param);

        // ================== TABELA PEDIDO ITENS ====================
		$form->addConteudoPastas(3, $this->getTabelaTarefas($itens ?? ''));

		$id = base64_encode($id);
		$form->setEnvio(getLink() . "salvar&id=$id", 'formIncluir_pedido');
        $form->setPastas([1 => 'Pedidos', 2 => 'Endereço', 3 => 'Itens']);

		$ret .= $form;

		$param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Incluir Nova Venda';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
    }

    public function salvar() {
		if(empty($_GET['id'])) {
			$id_codificado = base64_encode(time());
			$id = base64_decode($id_codificado);

			$tipo = 'INSERT';
			$where_pedido = "";
			$where_endereco = "";
		} else {
			$id = base64_decode($_GET['id']);

			$tipo = 'UPDATE';
			$where_pedido = "id = $id";
			$where_endereco = "entidade = 'crm_pedido_cab' AND cod = $id";
		}

        // ================ SALVANDO PEDIDOS CABEÇALHO =====================
        $param = [];
        $param['id'] = $id;
        $param['cliente'] = $_POST['cliente'];
        $param['id_cliente'] = base64_decode($_POST['id_cliente']);
        $param['cond_pagamento'] = $_POST['cond_pagamento'];
        $param['assunto'] = $_POST['assunto'];
        $param['oportunidades'] = ($_POST['oportunidades'] != '') ? $_POST['oportunidades'] : 0;
        $param['estagio_pedido'] = $_POST['estagio_pedido'];
        $param['validade'] = $_POST['validade'];
        $param['contatos'] = ($_POST['contatos'] != '') ? $_POST['contatos'] : 0;
        $param['entrega'] = $_POST['entrega'];
        $param['organizacoes'] = $_POST['organizacoes'];
        $param['vendedores'] = $_POST['vendedores'];
        $param['prazo_condicao'] = $_POST['prazo_condicao'];
        $param['descricao'] = $_POST['descricao'];
        $param['ativo'] = 'S';
        $sql = montaSQL($param, 'crm_pedido_cab', $tipo, $where_pedido);
        query($sql);

        // ================= SALVANDO ENDEREÇOS =======================
        $param = [];
        $param['entidade'] = 'crm_pedido_cab';
        $param['cod'] = $id;
        $param['seq'] = $_POST['seq'];
        $param['apelido'] = $_POST['apelido'];
        $param['pais'] = $_POST['pais'];
        $param['tipo_log'] = $_POST['tipo_log'];
        $param['logradouro'] = $_POST['logradouro'];
        $param['nr'] = $_POST['nr'];
        $param['complemento'] = $_POST['complemento'];
        $param['bairro'] = $_POST['bairro'];
        $param['cidade'] = $_POST['cidade'];
        $param['estado'] = $_POST['estado'];
        $param['cep'] = $_POST['cep'];
        $param['caixa_postal'] = $_POST['caixa_postal'];
        $param['obs'] = $_POST['obs'];
        $param['ativo'] = 'S';
        $sql = montaSQL($param, 'crm_enderecos', $tipo, $where_endereco);
        query($sql);

        // ============== SALVANDO PEDIDOS ITENS ======================
		if(isset($_POST['formOS'])) {
			$seq = 0;
			foreach($_POST['formOS'] as $campos) {
				if(isset($campos['id_produto'])) {
					$param = [];
					$param['id_produto'] = $campos['id_produto'];
					$param['quantidade'] = $campos['quantidade'];
					$param['seq'] = $seq++;
					$param['id_pedido'] = $id;
					$param['desconto_porcentagem'] = $campos['desconto_porcentagem'];
					$param['desconto_valor'] = $campos['desconto_valor'];
					$where_itens = isset($campos['id_item']) ? 'id = '.$campos['id_item'] : '';
					$sql = montaSQL($param, 'crm_pedido_itens', $tipo, $where_itens);
					query($sql);
				} else {
					$sql = "DELETE FROM crm_pedido_itens WHERE id = ".$campos['id_item'];
					query($sql);
				}
				
			}
		}

        redireciona(getLink() . 'avisos&mensagem=Dados salvos com sucesso!');
    }

	private function getTabelaTarefas($itens = []){
	    $ret = '';
	    
		$num_tarefas = !empty($itens) ? count($itens) : 0;

	    $param = [];
	    $param['texto'] = 'Incluir Tarefa';
	    $param['onclick'] = "incluiRat($num_tarefas);";
	    $param['id'] = 'myInput';
	    $ret .= formbase01::formBotao($param);
	    
	    $param = [];
	    $param['paginacao'] = false;
	    $param['scroll'] 	= false;
	    $param['scrollX'] 	= false;
	    $param['scrollY'] 	= false;
	    $param['ordenacao'] = false;
	    $param['filtro']	= false;
	    $param['info']		= false;
	    $param['id']		= 'tabRatID';
	    $param['width']		= '100%';
	    $tab = new tabela01($param);
	    
	    $tab->addColuna(array('campo' => 'id_produto'			, 'etiqueta' => 'Nome Produto'		    , 'tipo' => 'V', 'width' => '5'  , 'posicao' => 'C'));
	    $tab->addColuna(array('campo' => 'quantidade'			, 'etiqueta' => 'Quantidade'			, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'desconto_porcentagem'	, 'etiqueta' => 'Desconto Porcentagem'	, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'desconto_valor'		, 'etiqueta' => 'desconto_valor'		, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
	    $tab->addColuna(array('campo' => 'bt'					, 'etiqueta' => ''						, 'tipo' => 'V', 'width' => ' 50', 'posicao' => 'D'));

		// $campos = ['id_produto', 'quantidade', 'desconto_porcentagem', 'desconto_valor'];

		if(!empty($itens) && count($itens) > 0){
			$dados = [];
			$num = 0;
			foreach($itens as $item){
				$temp = array();
				//$temp['id_item'] = "<input type='text' name='formOS[id_item][]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";
				$ret .= "<input type='text' name='formOS[$num][id_item]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";

				$temp['id_produto'] = "<select name='formOS[$num][id_produto]' style='width:100%;text-align: right;' id='" . ($num+1) . "campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect($item['id_produto'])."</select>";
				$temp['quantidade'] = "<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][quantidade]' value='{$item['quantidade']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoquantidade' class='form-control  form-control-sm'          >";
				$temp['desconto_porcentagem'] = "<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][desconto_porcentagem]' value='{$item['desconto_porcentagem']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoporcentagem' class='form-control  form-control-sm'          >";
				$temp['desconto_valor'] = "<input type='number' name='formOS[$num][desconto_valor]' value='{$item['desconto_valor']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campovalor' class='form-control  form-control-sm'          >";
				$temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>";
				
				$dados[] = $temp;
				$num++;
			}
			$tab->setDados($dados);
		}

		// foreach($dados as $d) {
		// 	$ret .= $d['id_item'];
		// }

	    $ret .= $tab;
	    
	    return $ret;
	}

	public function excluir() {
		$id = base64_decode($_GET['id']);

		$sql = "UPDATE crm_pedido_cab SET ativo = 'N' WHERE id = $id;
				UPDATE crm_pedido_itens SET ativo = 'N' WHERE id_pedido = $id;
				UPDATE crm_enderecos SET ativo = 'N' WHERE entidade = 'crm_pedido_cab' AND cod = $id;";
		query($sql);

		redireciona(getLink() . "avisos&mensagem=Pedido excluido");
	}

	private function criarCampoSelect($id_produto = ''){
		if(empty($this->_produtos)) {
			$sql = "SELECT id, nome FROM crm_produtos WHERE ativo = 'S'";
			$this->_produtos = query($sql);
		}

		$html = "<option value=''>Escolha uma opção</option>";
		if(is_array($this->_produtos) && count($this->_produtos) > 0) {
			foreach($this->_produtos as $row) {
				if($id_produto == $row['id']) {
					$selecionado = 'selected';
				} else {
					$selecionado = '';
				}
				$html .= "<option value='{$row['id']}' $selecionado>{$row['nome']}</option>";
			}
		}

		return $html;
	}

	private function addJS_ListaPedidos(){
		$ret = '';
		
		$ret .= "
		
		function excluirRat(e){
						var t = $('#tabRatID').DataTable();
						t.row( $(e).parents('tr') ).remove().draw();
			}
		function incluiRat(valor){
						var t = $('#tabRatID').DataTable();
				
						var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";
				
						var id_produto = \"<select name='formOS[\"+valor+\"][id_produto]' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect()."</select>\";
						// var id_produto = \"<input  type='text' name='formOS[\"+valor+\"][id_produto]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'          >\";
						var quantidade = \"<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][quantidade]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoquantidade' class='form-control  form-control-sm'          >\";
						var desconto_porcentagem = \"<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][desconto_porcentagem]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoporcentagem' class='form-control  form-control-sm'          >\";
						var desconto_valor = \"<input  type='number' name='formOS[\"+valor+\"][desconto_valor]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campovalor' class='form-control  form-control-sm'          >\";

						// var texto = \"<input  type='text' name='formOS[descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
				
							t.row.add( [id_produto, quantidade, desconto_porcentagem, desconto_valor, bt] ).draw( false );
							$('#'+valor+'tabelacampohora');
				
							valor = valor + 1;
							$('#myInput').attr('onclick', 'incluiRat('+valor+');' );
		}
	
			";
		
		addPortaljavaScript($ret);
		
		return $ret;
	}

	private function addJqueryAjax(){
		addPortaljavaScript("function callAjax(){");
			
			addPortaljavaScript("  var entidade = document.getElementById('inputOrigem').value;");
			addPortaljavaScript("  var option = '';
			$.getJSON('" . getLinkAjax('ajax') . "&entidade=' + entidade, function (dados){
				if (dados.length > 0){
					$.each(dados, function(i, obj){
					option += '<option value=\"'+obj.valor+'\">'+obj.etiqueta+'</option>';
						$('#inputDestino').html(option).show();
					});
				}
			})
		}");
	}
}
