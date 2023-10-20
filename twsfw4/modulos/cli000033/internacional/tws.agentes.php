<?php
/*
 * Data Criacao: 15/09/2023
 * Autor: Verticais - Rafael
 *
 * Descricao: Interface de cadastro de Agentes
 *
 * Alteracoes;
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class agentes {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
        'ajax'              => true,
    );

    // Classe relatorio01
    private $_tabela;

    function __construct() {
        conectaCONSULT();

        $this->_tabela = new tabela01(['titulo' => 'Agentes']);
    }

    public function index() {
        $ret = '';

        $this->montaColunas();
        $dados = $this->getDados();
        $this->_tabela->setDados($dados);

        $param = array(
			'texto' => 'Novo Agente',
			'onclick' => "setLocation('" . getLink() . "incluir')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
			'texto' => 'Editar', //Texto no botão
			'link' => getLink() . 'incluir&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'success', //padrão: azul; danger: vermelho; success: verde
		);
		$this->_tabela->addAcao($param);

        $param = array(
			'texto' => 'Visualizar', //Texto no botão
			'link' => getLink() . 'incluir&visualizar=1&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			//'cor' => 'success', //padrão: azul; danger: vermelho; success: verde
		);
		$this->_tabela->addAcao($param);

        $ret .= $this->_tabela;
        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'pais', 'etiqueta' => 'País', 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'agente', 'etiqueta' => 'Agente', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'contato', 'etiqueta' => 'Contato', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'email', 'etiqueta' => 'E-mail', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' =>  100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT ma.*, mp.nomepais
                FROM marpaagente AS ma
                LEFT JOIN marpapais AS mp ON mp.codpais = ma.paisagente";
        $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['codagente'];
                $temp['pais'] = mb_convert_encoding($row['nomepais'], 'UTF-8', 'ASCII');
                $temp['agente'] = mb_convert_encoding($row['nomeagente'], 'UTF-8', 'ASCII');
                $temp['contato'] = mb_convert_encoding($row['contato'], 'UTF-8', 'ASCII');
                $temp['email'] = $row['email'];
                $temp['ativo'] = ($row['ativo'] == 'S') ? 'Sim' : 'Não';

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir($id = '', $visualizacao = 0) {
        $ret = '';
        $id = (isset($_GET['id']) && !empty($_GET['id'])) ? $_GET['id'] : $id;
        $visualizar = $_GET['visualizar'] ?? $visualizacao;

        $agentes = new internacional_agentes();
        $ret .= $agentes->incluir($id, $visualizar);

        return $ret;
    }

    public function salvar() {
        $ret = '';
        $id = $_GET['id'] ?? '';

        $agentes = new internacional_agentes();
        $id_salvo = $agentes->salvar($id);

        if($id_salvo > 0) {
            addPortalMensagem("Informações salvas com sucesso!");
            $ret = $this->incluir($id_salvo, 1);
        } else {
            addPortalMensagem("Erro ao gravar as informações", 'error');
            $ret = $this->incluir($id);
        }

        return $ret;
    }

    public function ajax() {
        $id = $_GET['id'];
        $op = getOperacao();
        
        if($op == 'incluir_comissao') {
            if(empty($_GET['id_comissao'])) {
               $tipo = 'INSERT';
               $where = ''; 
            } else {
                $tipo = 'UPDATE';
                $where = "marpacomissoes_agentes_id = ".$_GET['id_comissao'];
            }

            $temp = [];
            $temp['codagente'] = $id;
            $temp['data'] = $_GET['data'];
            $temp['valor'] = str_replace(',', '.', $_GET['valor']);
            $temp['descricao'] = str_replace(['"', "'"], '', $_GET['descricao']);

            $sql_insert = montaSQL($temp, 'marpacomissoes_agentes', $tipo, $where);
            query2($sql_insert);
        }
        else if($op == 'excluir_comissao') {
            $id_comissao = $_GET['id_comissao'];

            $sql_delete = "DELETE FROM marpacomissoes_agentes WHERE marpacomissoes_agentes_id = $id_comissao";
            query2($sql_delete);
        }

        $this->_tabela->setTitulo('Comissões');
    
        $this->montaColunasComissoes();
        $dados = $this->getDadosComissoes($id);
        $this->_tabela->setDados($dados);

        echo '' . $this->_tabela;

        die();
    }

    private function montaColunasComissoes() {
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' =>  200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'valor', 'etiqueta' => 'Valor', 'tipo' => 'V', 'width' =>  200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'botoes', 'etiqueta' => '', 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
    }

    private function getDadosComissoes($id) {
        $ret = [];

        $sql_select = "SELECT * FROM marpacomissoes_agentes WHERE codagente = $id ORDER BY marpacomissoes_agentes_id";
        $rows = query2($sql_select);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $id_comissao = $row['marpacomissoes_agentes_id'];

                $temp = [];
                $temp['id_comissao'] = $id_comissao;
                $temp['data'] = str_replace('-', '', $row['data']);
                $temp['valor'] = $row['valor'];
                $temp['descricao'] = $row['descricao'];

                $valor = str_replace('.', ',', $row['valor']);
                $temp['botoes'] = "<input type='button' value='Editar' onclick='editarComissao($id_comissao, \"{$row['data']}\", \"$valor\", \"{$row['descricao']}\")' class='btn btn-success'>
                                    <input type='button' value='Excluir' onclick='excluirComissao($id, $id_comissao)' class='btn btn-danger'>";

                $ret[] = $temp;
            }
        }

        return $ret;
    }
}