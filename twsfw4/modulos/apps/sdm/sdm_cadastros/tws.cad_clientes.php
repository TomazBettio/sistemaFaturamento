<?php
/*
 * Data Criação: 12/08/2020
 * Autor: bcs
 */
if (! defined('TWSiNet') || ! TWSiNet)
	die('Esta nao e uma pagina de entrada valida!');
	ini_set('display_errors', 1);
	ini_set('display_startup_erros', 1);
	error_reporting(E_ALL);
	
	
	class cad_clientes{
		var $funcoes_publicas = array(
				'index'			=> true,
				'editar'        => true,
				'salvar'        => true,
				'excluir'       => true,
				
		);
		
		function index(){
			$ret = '';
			
			$bw = new tabela01(array('paginacao' => false));
			
			$bw->addColuna(array('campo' => 'id' 		, 'etiqueta' => 'ID'			, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
			$bw->addColuna(array('campo' => 'nome' 		, 'etiqueta' => 'Nome'			, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'nreduz' 	, 'etiqueta' => 'Nome Reduzido'	, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'cnpj' 		, 'etiqueta' => 'CNPJ'			, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'endereco' 	, 'etiqueta' => 'Endereço'		, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'bairro' 	, 'etiqueta' => 'Bairro'		, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'cidade' 	, 'etiqueta' => 'Cidade'		, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'uf' 		, 'etiqueta' => 'UF'			, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'C'));
			$bw->addColuna(array('campo' => 'cep' 		, 'etiqueta' => 'CEP'			, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'contato' 	, 'etiqueta' => 'Contato'		, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'fone' 		, 'etiqueta' => 'Telefone'		, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			$bw->addColuna(array('campo' => 'hpage' 	, 'etiqueta' => 'Homepage'		, 'tipo' => 'T', 'width' =>  10, 'posicao' => 'E'));
			
			// Botão Editar
			$param = array(
					'texto' => 'Editar',
					'link' => getLink() . 'editar&id=',
					'coluna' => 'id',
					'width' => 10,
					'flag' => '',
					//'tamanho' => 'pequeno',
					'cor' => 'success'
			);
			$bw->addAcao($param);
			
			// Botão Excluir
			$param2 = array(
					'texto' => 'Excluir',
					'link' => getLink() . 'excluir&id=',
					'coluna' => 'id',
					'width' => 10,
					'flag' => '',
					//'tamanho' => 'pequeno',
					'cor' => 'danger'
			);
			$bw->addAcao($param2);
			
			$dados = $this->getDados();
			$bw->setDados($dados);
			
			$param = [];
			$param['titulo'] = 'Cadastro de Clientes';
			$param['conteudo'] = $bw;
			$p = array('onclick' => "setLocation('" . getLink() . "editar&id=0')",'texto' => 'Incluir', 'cor' => COR_PADRAO_BOTOES);
			$param['botoesTitulo'][] = $p;
			$ret = addCard($param);
			
			return $ret;
			
		}
		
		//Pega os dados da tabela
		private function getDados(){
			$ret = array();
			$col = array('id','nome','nreduz','ie','resp','cnpj','cod','endereco','bairro','cidade','uf','cep','contato','fone','hpage','diafat');
			
			$rows = query("SELECT * FROM cad_clientes WHERE 1=1");
			if(is_array($rows) && count($rows)>0){
				foreach ($rows as $r) {
					foreach ($col as $c)
						$temp[$c] = $r[$c];
						$ret[] = $temp;
				}
			}
			// print_r($rows);
			return $ret;
		}
		
		private function getDados2($id){
			$ret = array();
			$col = array('id','nome','nreduz','ie','resp','cnpj','cod','endereco','bairro','cidade','uf','cep','contato','fone','hpage','diafat');
			foreach($col as $coluna){
				$ret[$coluna] = '';
			}
			$ret['cod'] = 'AUTOMÁTICO';
			
			if($id != 0){
				$rows = query("SELECT * FROM cad_clientes WHERE id = $id ");
				if(is_array($rows) && count($rows)>0){
					foreach ($rows as $r) {
						foreach ($col as $c){
							$ret[$c] = $r[$c];
						}
					}
				}
			}
			return $ret;
		}
		
		public function editar(){
			$ret = '';
			$id = getParam($_GET, 'id', 0);
			
			$param = [];
			$form = new form01($param);
			//editar
			$dados = $this->getDados2($id);
			
			$form->addCampo(array('id' => '','campo' => 'formPrograma[nome]',      'etiqueta' => 'Nome',               'tipo' => 'T','tamanho' => '60','linhas' => '','valor' => $dados['nome'],       'pasta' => 0, 'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => true));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[nreduz]',    'etiqueta' => 'Nome Reduzido',      'tipo' => 'T','tamanho' => '20','linhas' => '','valor' => $dados['nreduz'],     'pasta' => 0, 'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => true));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[resp]',      'etiqueta' => 'Responsável',        'tipo' => 'T','tamanho' => '50','linhas' => '','valor' => $dados['resp'],       'pasta' => 0, 'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[ie]',        'etiqueta' => 'Inscrição Estadual', 'tipo' => 'N','tamanho' => '15','linhas' => '','valor' => $dados['ie'],         'pasta' => 0, 'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[cnpj]',      'etiqueta' => 'CNPJ',               'tipo' => 'N','tamanho' => '20','linhas' => '','valor' => $dados['cnpj'],       'pasta' => 0, 'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false,'mascara'=>'cnpj'));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[cod]',       'etiqueta' => 'Código',             'tipo' => 'I','tamanho' => '6', 'linhas' => '','valor' => $dados['cod'],        'pasta' => 0, 'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[endereco]',  'etiqueta' => 'Endereço',           'tipo' => 'T','tamanho' => '50','linhas' => '','valor' => $dados['endereco'],   'pasta' => 01,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[bairro]',    'etiqueta' => 'Bairro',             'tipo' => 'T','tamanho' => '20','linhas' => '','valor' => $dados['bairro'],     'pasta' => 01,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[cidade]',    'etiqueta' => 'Cidade',             'tipo' => 'T','tamanho' => '15','linhas' => '','valor' => $dados['cidade'],     'pasta' => 01,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[uf]',        'etiqueta' => 'UF',                 'tipo' => 'T','tamanho' => '2', 'linhas' => '','valor' => $dados['uf'],         'pasta' => 01,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[cep]',       'etiqueta' => 'CEP',                'tipo' => 'N','tamanho' => '20','linhas' => '','valor' => $dados['ie'],         'pasta' => 01,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false,'mascara'=>'cep'));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[contato]',   'etiqueta' => 'Contato',            'tipo' => 'T','tamanho' => '15','linhas' => '','valor' => $dados['cep'],        'pasta' => 02,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[fone]',      'etiqueta' => 'Telefone',           'tipo' => 'N','tamanho' => '15','linhas' => '','valor' => $dados['fone'],       'pasta' => 02,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[hpage]',     'etiqueta' => 'Homepage',           'tipo' => 'T','tamanho' => '30','linhas' => '','valor' => $dados['hpage'],      'pasta' => 02,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->addCampo(array('id' => '','campo' => 'formPrograma[diafat]',    'etiqueta' => 'Dia da Fatura',      'tipo' => 'N','tamanho' => '2', 'linhas' => '','valor' => $dados['diafat'],     'pasta' => 02,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false));
			$form->setPastas(array('Geral', 'Endereço', 'Outros'));
			
			$form->setEnvio(getLink() . 'salvar&id=' . $id, 'formPrograma', 'formPrograma');
			
			$titulo = 0 == $id ? 'NOVO Cliente' : 'EDIÇÃO Cliente';
			
			$param = [];
			$param['titulo'] = $titulo;
			$param['conteudo'] = $form;
			$ret = addCard($param);
			
			return $ret;
			//  return $this->index();
		}
		
		
		public function salvar(){
			$id = getParam($_GET, 'id', 0);
			if(isset($_POST['formPrograma'])&&count($_POST['formPrograma'])>0){
				$dados = $_POST['formPrograma'];
				if(!(isset($dados['diafat'])&&!empty($dados['diafat'])))
				{
					$dados['diafat']=1;
				}
				
				if($id==0){
					//$dados['id']=$this->getId();
					$dados['cod'] = $this->getNovoCliente();
					$dados['ativo'] = 'S';
					$dados['cliente'] = getCliente();
					$dados['emp'] = '';
					$dados['fil'] = '01';
					$sql = montaSQL($dados, 'cad_clientes');
					query($sql);
				}
				else {
					$sql = montaSQL($dados, 'cad_clientes', 'UPDATE', "id = $id ");
					query($sql);
				}
			}
			return $this->index();
		}
		
		
		public function excluir()
		{
			$id = getParam($_GET, 'id', 0);
			query("UPDATE cad_clientes SET ativo = 'N' WHERE id = $id ");
			return $this->index();
		}
		
		private function getNovoCliente(){
			$ret = '000001';
			$sql = "select max(cod) as cod from cad_clientes where cod < '900000'";
			$rows = query($sql);
			if(is_array($rows) && count($rows) > 0){
				$temp = $rows[0]['cod'];
				$ret = intval($temp) + 1;
				while(strlen($ret) < 6){
					$ret = '0' . $ret;
				}
			}
			return $ret;
		}
		
	}