<?php
/*
 * Data Criacao: 26/01/2023
 * Autor: Verticais - Rafael Postal
 *
 * Descricao: Portal usado para que os funcionários da MGT possam acessar os arquivos dos clientes
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class portal {
    var $funcoes_publicas = array(
        'index'             => true,
        'getArquivos'       => true,
        'download'          => true,
    );

    // Pasta raiz
    private $_path;

    // Classe tabela01
    private $_tabela;

    function __construct() {
        global $config;

        $this->_path = $config['pathPortalArquivo'];

        $param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Portal de Arquivos';
		$this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        $this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

        $param = array(
			'texto' => 'Relatório', //Texto no botão
			'link' => getLink() . 'getArquivos&get=', //Link da página para onde o botão manda
			'coluna' => ['cnpj', 'contrato'], //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

        $ret .= $this->_tabela;

        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'nome'		, 'etiqueta' => 'Nome'		, 'tipo' => 'T', 'width' => 300, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cnpj'		, 'etiqueta' => 'CNPJ'		, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'contrato'	, 'etiqueta' => 'Contrato'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'status'		, 'etiqueta' => 'Status'	, 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT sys.nome, painel.cnpj, painel.contrato, painel.status 
                    FROM sys001 as sys INNER JOIN painel_arquivos as painel ON sys.user = painel.usuario";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['nome'] = $row['nome'];
                $temp['cnpj'] = $row['cnpj'];
                $temp['contrato'] = $row['contrato'];
                $temp['status'] = ($row['status'] == 0) ? 'Em andamento' : 'Finalizado';

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function getArquivos() {
        $get = explode('|', $_GET['get']);
        $cnpj = $get[0];
        $contrato = $get[1];

		$tipo = 'outros';

		$lista = new portal_arquivos_lista($cnpj, $contrato);
		$ret = $lista->getListaProcessados($tipo);

		return $ret;
    }

    public function download() {
        $cnpj = $_GET['cnpj'];
        $contrato = $_GET['contrato'];
        $arquivo = $_GET['arquivo'] ?? '';

        $lista = new portal_arquivos_lista($cnpj, $contrato);
        
        $operacao = getOperacao();
        if($operacao == 'selecionados') {
            $lista->selecionados();
        } else {
            $lista->download($arquivo);
        }

    }
}