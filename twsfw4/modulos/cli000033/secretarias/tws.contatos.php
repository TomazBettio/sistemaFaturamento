<?php

/*
 * Data Criacao: 06/10/2023
 * Autor: TWS - Rafael Postal
 *
 * Descricao: Interface para cadastro de contatos
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class contatos {
    var $funcoes_publicas = array(
        'avisos'            => true,
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    // Classe formFiltro01
    private $_filtro;

    // Título da tabela
    private $_titulo;

    // O programa irá gerar relatório?
    private $_gerar_relatorio;

    // Deve desabilitar o filtro?
    private $_desabilitar_filtro;

    function __construct() {
        conectaCONSULT();

        $this->_gerar_relatorio = $_GET['gerar_relatorio'] ?? false;

        $param = [];
        // $param['titulo'] = 'Contatos';
        $param['paginacao'] = true;
        $param['ordenacao'] = false;
        $param['auto'] = $this->_gerar_relatorio;
        $this->_tabela = new tabela01($param);

        $param = [];
        $param['link'] = getLink().'index';
        $this->_filtro = new formFiltro01('secretarias_aniversario', $param);
    }

    public function avisos() {
		$tipo = $_GET['tipo'] ?? '';
        $redireciona = $_GET['redireciona'] ?? 'index';

		if ($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->{$redireciona}();
	}

    public function index() {
        $ret = '';

        $filtrar = $_GET['filtrar'] ?? false;
        $this->_desabilitar_filtro = $_GET['desabilitar'] ?? false;

        $filtro = $this->_filtro->getFiltro();

        $de = (isset($filtro['DATAINI']) && !empty($filtro['DATAINI'])) ? $filtro['DATAINI'] : date('Ymd');
        $ate = (isset($filtro['DATAFIM']) && !empty($filtro['DATAFIM'])) ? $filtro['DATAFIM'] : date('Ymd');
        $tipo = $filtro['TIPO'] ?? 'E';

        if($filtrar && !$this->_desabilitar_filtro) {
            $ret .= $this->_filtro;
        }

        // =============== MONTA A TABELA ===============
        $this->montaColunas($tipo);
        $dados = $this->getDados($de, $ate, $tipo);
        $this->_tabela->setDados($dados);
        $this->_tabela->setTitulo($this->_titulo);

        // =============== CRIA OS BOTÕES NO TÍTULO ===============
        $param = array(
			'texto' => 'Novo Contato',
			'onclick' => "setLocation('" . getLink() . "incluir')",
            'cor' => 'success',
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
			'texto' => 'Filtrar',
			'onclick' => "setLocation('" . getLink() . "index&filtrar=1')",
            'cor' => 'info',
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
			'texto' => 'Desabilitar filtro',
			'onclick' => "setLocation('" . getLink() . "index&desabilitar=1')",
            'cor' => 'secondary',
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
            'texto' => 'Gerar Relatório',
            'onclick' => ($this->_desabilitar_filtro ? '' : "setLocation('" . getLink() . "index&gerar_relatorio=1')"),
            'cor' => 'warning',
        );
        $this->_tabela->addBotaoTitulo($param);
        
        // =============== CRIA OS BOTÕES INDIVIDUAIS ===============
        $param = array(
            'texto' => 'Visualizar', //Texto no botão
            'link' => getLink() . 'incluir&visualizacao=1&id=', //Link da página para onde o botão manda
            'coluna' => 'id', //Coluna impressa no final do link
            'width' => 100, //Tamanho do botão
            'flag' => '',
            'tamanho' => 'pequeno', //Nenhum fez diferença?
            //'cor' => 'success', //padrão: azul; danger: vermelho; success: verde
            'pos' => 'F',
        );
        $this->_tabela->addAcao($param);
        
        $param = array(
			'texto' => 'Editar', //Texto no botão
			'link' => getLink() . 'incluir&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'success', //padrão: azul; danger: vermelho; success: verde
            'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

        $ret .= $this->_tabela;

        if($this->_gerar_relatorio) {
            $param = [];
            $param['destinatario'] = 'assistente@marpa.com.br';
            $param['mensagem'] = $ret;
            $param['assunto'] = 'Relatório de aniversários';
            enviaEmail($param);

            redireciona(getLink() . "avisos&mensagem=Relatório gerado com sucesso");
        }

        return $ret;
    }

    private function montaColunas($tipo) {
        if($tipo == 'E' || $this->_desabilitar_filtro) {
            $this->_tabela->addColuna(array('campo' => 'nome_empresa', 'etiqueta' => 'Nome Empresa', 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
        }
        $this->_tabela->addColuna(array('campo' => 'nome_contato', 'etiqueta' => 'Nome Contato', 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'email', 'etiqueta' => 'E-mail', 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'telefone', 'etiqueta' => 'Telefone', 'tipo' => 'T', 'width' =>  50, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'aniversario', 'etiqueta' => 'Aniversário', 'tipo' => 'D', 'width' =>  50, 'posicao' => 'C'));
    }

    private function getDados($de, $ate, $tipo) {
        $ret = [];

        $de_dia = date("d", strtotime($de));
        $de_mes = date("m", strtotime($de));

        $ate_dia = date("d", strtotime($ate));
        $ate_mes = date("m", strtotime($ate));

        if($this->_desabilitar_filtro) {
            $this->_titulo = 'Empresas - Sem filtro de aniversário';

            $sql = "SELECT contato_id, nome AS nome_empresa, dtanivemp AS aniversario, contato AS nome_contato, email, fones
                    FROM marpaorgpess
                    ORDER BY DATE_PART('month', dtanivemp), DATE_PART('day', dtanivemp), DATE_PART('year', dtanivemp)";
        } else if($tipo == 'E') {
            $this->_titulo = "Empresas - <b>$de_dia/$de_mes - $ate_dia/$ate_mes</b>";

            $sql = "SELECT contato_id, nome AS nome_empresa, dtanivemp AS aniversario, contato AS nome_contato, email, fones
                    FROM marpaorgpess
                    WHERE (date_part('month', dtanivemp) BETWEEN '$de_mes' AND '$ate_mes')
                        AND (date_part('day', dtanivemp) BETWEEN '$de_dia' AND '$ate_dia')
                        ORDER BY DATE_PART('month', dtanivemp), DATE_PART('day', dtanivemp), DATE_PART('year', dtanivemp)";
        } else {
            $this->_titulo = "Contatos - <b>$de_dia/$de_mes - $ate_dia/$ate_mes</b>";

            /*
                Antigamente os contatos eram salvos na mesma tabela das empresas (um registro por empresa),
                nesses casos, é preciso buscar os contatos das duas tabelas
            */
            $sql = "SELECT * FROM 
                        (
                            SELECT empresa.contato_id, contato.contato AS nome_contato, contato.aniversario, contato.email, contato.fones
                            FROM marpaorgpess_contatos AS contato
                            LEFT JOIN marpaorgpess AS empresa ON (empresa.marpaorgpess_contatos_id_1 = contato.marpaorgpess_contatos_id
                                                                    OR empresa.marpaorgpess_contatos_id_2 = contato.marpaorgpess_contatos_id
                                                                    OR empresa.marpaorgpess_contatos_id_3 = contato.marpaorgpess_contatos_id
                                                                    OR empresa.marpaorgpess_contatos_id_4 = contato.marpaorgpess_contatos_id)
                            WHERE (date_part('month', contato.aniversario) BETWEEN '$de_mes' AND '$ate_mes')
                                AND (date_part('day', contato.aniversario) BETWEEN '$de_dia' AND '$ate_dia')
                                
                            UNION
                            
                            SELECT contato_id, contato, dtaniver AS aniversario, email, fones
                            FROM marpaorgpess
                            WHERE (date_part('month', dtaniver) BETWEEN '$de_mes' AND '$ate_mes')
                                AND (date_part('day', dtaniver) BETWEEN '$de_dia' AND '$ate_dia')
                        ) AS retorno
                        
                    ORDER BY DATE_PART('month', aniversario), DATE_PART('day', aniversario), DATE_PART('year', aniversario)";
        }
        $rows = query2($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['contato_id'];
                $temp['nome_empresa'] = isset($row['nome_empresa']) ? mb_convert_encoding($row['nome_empresa'], 'UTF-8', 'ASCII') : '';
                $temp['nome_contato'] = mb_convert_encoding($row['nome_contato'], 'UTF-8', 'ASCII');
                $temp['email'] = $row['email'];
                $temp['telefone'] = $row['fones'];
                $temp['aniversario'] = str_replace('-', '', $row['aniversario']);

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';
        $visualizacao = $_GET['visualizacao'] ?? false;

        if(!empty($id)) {
            $sql = "SELECT * FROM marpaorgpess WHERE contato_id = $id";
            $row = query2($sql);
            $row = $row[0];
        }

        $form = new form01();
        $form->setDescricao('Novo Contato');

        // ==================== PASTA 1 ====================
        $param = [];
		$param['campo'] = 'nome';
		$param['etiqueta'] = 'Nome';
		$param['largura'] = '6';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 90;
        $param['valor'] = $row['nome'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'empresa';
		$param['etiqueta'] = 'Empresa';
		$param['largura'] = '3';
		$param['tipo'] = 'A';
        $param['opcoes'] = ['' => 'Selecione', 'S' => 'Sim', 'N' => 'Não'];
		$param['obrigatorio'] = true;
		$param['tamanho'] = 90;
        $param['valor'] = $row['empresa'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'dtanivemp';
		$param['etiqueta'] = 'Data Anivesário';
		$param['largura'] = '3';
		$param['tipo'] = 'D';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 90;
        $param['valor'] = isset($row['dtanivemp']) ? Datas::dataMS2D($row['dtanivemp']) : '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'obs';
		$param['etiqueta'] = 'Observações';
		$param['largura'] = '12';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 1000;
        $param['valor'] = $row['obs'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
        $param['campo'] = "contato";
        $param['etiqueta'] = 'Nome Contato';
        $param['largura'] = '6';
        $param['tipo'] = 'C';
        // $param['obrigatorio'] = true;
        $param['tamanho'] = 90;
        $param['valor'] = $row['contato'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
        $form->addCampo($param);

        $param = [];
		$param['campo'] = 'dtaniver';
		$param['etiqueta'] = 'Anivesário Contato';
		$param['largura'] = '2';
		$param['tipo'] = 'D';
		// $param['obrigatorio'] = true;
		$param['tamanho'] = 90;
        $param['valor'] = isset($row['dtaniver']) ? Datas::dataMS2D($row['dtaniver']) : '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
        $param['campo'] = "email";
        $param['etiqueta'] = 'E-mail';
        $param['largura'] = '4';
        $param['tipo'] = 'C';
        // $param['obrigatorio'] = true;
        $param['tamanho'] = 150;
        $param['valor'] = $row['email'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = "fones";
        $param['etiqueta'] = 'Fones';
        $param['largura'] = '4';
        $param['tipo'] = 'C';
        // $param['obrigatorio'] = true;
        $param['tamanho'] = 40;
        $param['valor'] = $row['fones'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = "cels";
        $param['etiqueta'] = 'Celulares';
        $param['largura'] = '4';
        $param['tipo'] = 'C';
        // $param['obrigatorio'] = true;
        $param['tamanho'] = 40;
        $param['valor'] = $row['cels'] ?? '';
        $param['pasta'] = 1;
        $param['readonly'] = $visualizacao;
        $form->addCampo($param);

        // ==================== PASTA 2 ====================
        $param = [];
		$param['campo'] = 'endereco';
		$param['etiqueta'] = 'Endereço';
		$param['largura'] = '8';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 90;
        $param['valor'] = $row['endereco'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cep';
		$param['etiqueta'] = 'CEP';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 9;
        $param['valor'] = $row['cep'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'uf';
		$param['etiqueta'] = 'UF';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 2;
        $param['valor'] = $row['uf'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cidade';
		$param['etiqueta'] = 'Cidade';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 100;
        $param['valor'] = $row['cidade'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'bairro';
		$param['etiqueta'] = 'Bairro';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['tamanho'] = 100;
        $param['valor'] = $row['bairro'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        $sql = "SELECT codpais, nomepais FROM marpapais ORDER BY nomepais";
        $rows_paises = query2($sql);

        $paises = [];
        if(is_array($rows_paises) && count($rows_paises) > 0) {
            foreach($rows_paises as $pais) {
                $paises[$pais['codpais']] = mb_convert_encoding($pais['nomepais'], 'UTF-8', 'ASCII');
            }
        }

        $param = [];
		$param['campo'] = 'codpais';
		$param['etiqueta'] = 'País';
		$param['largura'] = '4';
		$param['tipo'] = 'A';
        $param['opcoes'] = $paises;
		$param['obrigatorio'] = true;
		$param['tamanho'] = 2;
        $param['valor'] = $row['codpais'] ?? '';
        $param['pasta'] = 2;
        $param['readonly'] = $visualizacao;
		$form->addCampo($param);

        // ==================== PASTA 3 4 5 e 6 ====================
        $pastas = [
            1 => 3,
            2 => 4,
            3 => 5,
            4 => 6
        ];
        for($i = 1; $i <= 4; $i++) {
            $contato = '';
            if(isset($row['marpaorgpess_contatos_id_'.$i]) && !empty($row['marpaorgpess_contatos_id_'.$i])) {
                $sql = "SELECT * FROM marpaorgpess_contatos WHERE marpaorgpess_contatos_id = ".$row['marpaorgpess_contatos_id_'.$i];
                $contato = query2($sql);
                $contato = $contato[0];
            }

            $param = [];
            $param['campo'] = "contatos[$i][contato]";
            $param['etiqueta'] = 'Nome';
            $param['largura'] = '6';
            $param['tipo'] = 'C';
            // $param['obrigatorio'] = true;
            $param['tamanho'] = 90;
            $param['valor'] = $contato['contato'] ?? '';
            $param['pasta'] = $pastas[$i];
            $param['readonly'] = $visualizacao;
            $form->addCampo($param);

            $param = [];
            $param['campo'] = "contatos[$i][cargo]";
            $param['etiqueta'] = 'Cargo';
            $param['largura'] = '4';
            $param['tipo'] = 'C';
            // $param['obrigatorio'] = true;
            $param['tamanho'] = 90;
            $param['valor'] = $contato['cargo'] ?? '';
            $param['pasta'] = $pastas[$i];
            $param['readonly'] = $visualizacao;
            $form->addCampo($param);
            
            $param = [];
            $param['campo'] = "contatos[$i][aniversario]";
            $param['etiqueta'] = 'Aniversário';
            $param['largura'] = '2';
            $param['tipo'] = 'D';
            // $param['obrigatorio'] = true;
            $param['tamanho'] = 90;
            $param['valor'] = isset($contato['aniversario']) ? Datas::dataMS2D($contato['aniversario']) : '';
            $param['pasta'] = $pastas[$i];
            $param['readonly'] = $visualizacao;
            $form->addCampo($param);
    
            $param = [];
            $param['campo'] = "contatos[$i][email]";
            $param['etiqueta'] = 'E-mail';
            $param['largura'] = '6';
            $param['tipo'] = 'C';
            // $param['obrigatorio'] = true;
            $param['tamanho'] = 150;
            $param['valor'] = $contato['email'] ?? '';
            $param['pasta'] = $pastas[$i];
            $param['readonly'] = $visualizacao;
            $form->addCampo($param);
    
            $param = [];
            $param['campo'] = "contatos[$i][fones]";
            $param['etiqueta'] = 'Fone';
            $param['largura'] = '4';
            $param['tipo'] = 'C';
            // $param['obrigatorio'] = true;
            $param['tamanho'] = 40;
            $param['valor'] = $contato['fones'] ?? '';
            $param['pasta'] = $pastas[$i];
            $param['readonly'] = $visualizacao;
            $form->addCampo($param);
    
            $param = [];
            $param['campo'] = "contatos[$i][cels]";
            $param['etiqueta'] = 'Celular';
            $param['largura'] = '4';
            $param['tipo'] = 'C';
            // $param['obrigatorio'] = true;
            $param['tamanho'] = 40;
            $param['valor'] = $contato['cels'] ?? '';
            $param['pasta'] = $pastas[$i];
            $param['readonly'] = $visualizacao;
            $form->addCampo($param);
        }

        $form->setPastas([1 => 'Empresa', 2 => 'Endereço', 3 => 'Contato 01', 4 => 'Contato 02', 5 => 'Contato 03', 6 => 'Contato 04']);
        $form->setEnvio(getLink() . "salvar&id=$id", 'formEditarContatos');

        $ret .= $form;
        return $ret;
    }

    public function salvar() {
        if(!empty($_POST)) {
            $id = $_GET['id'] ?? '';

            if(empty($id)) {
                $sql = "SELECT MAX(marpaorgpess_contatos_id) AS max FROM marpaorgpess_contatos";
                $id_contato = query2($sql);
                $id_contato = $id_contato[0]['max'];

                $ids_contatos = [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0
                ];
            } else {
                $sql = "SELECT marpaorgpess_contatos_id_1,
                                marpaorgpess_contatos_id_2,
                                marpaorgpess_contatos_id_3,
                                marpaorgpess_contatos_id_4
                        FROM marpaorgpess WHERE contato_id = $id";
                $ids_banco = query2($sql);

                $ids_contatos = [
                    1 => $ids_banco[0]['marpaorgpess_contatos_id_1'],
                    2 => $ids_banco[0]['marpaorgpess_contatos_id_2'],
                    3 => $ids_banco[0]['marpaorgpess_contatos_id_3'],
                    4 => $ids_banco[0]['marpaorgpess_contatos_id_4']
                ];
            }

            foreach($_POST['contatos'] as $k => $contato) {
                if($ids_contatos[$k] == 0 || empty($ids_contatos[$k])) {
                    $tipo = 'INSERT';
                    $where = '';

                    $id_contato++;
                    $ids_contatos[$k] = $id_contato;
                } else {
                    $id_contato = $ids_contatos[$k];
                    
                    $tipo = 'UPDATE';
                    $where = "marpaorgpess_contatos_id = $id_contato";
                }
                
                // Se for um INSERT, deve ter pelo menos um campo preenchido
                if(!($tipo == 'INSERT' && empty($contato['contato']) && empty($contato['cargo']) && empty($contato['email']) && empty($contato['fones']) && empty($contato['cels']))) {
                    $aniver = array_reverse(explode('/', $contato['aniversario']));
                    $aniver = implode('-', $aniver);
                    
                    $temp = [];
                    $temp['marpaorgpess_contatos_id'] = $id_contato;
                    $temp['contato']    = str_replace(['"', "'"], '', $contato['contato']);
                    $temp['cargo']      = str_replace(['"', "'"], '', $contato['cargo']);
                    $temp['email']      = str_replace(['"', "'"], '', $contato['email']);
                    $temp['fones']      = str_replace(['"', "'"], '', $contato['fones']);
                    $temp['cels']       = str_replace(['"', "'"], '', $contato['cels']);
                    $temp['aniversario'] = !empty($aniver) ? $aniver : 'NULL';

                    $sql = montaSQL($temp, 'marpaorgpess_contatos', $tipo, $where);
                    query2($sql);
                }
            }

            if(empty($id)) {
                $sql = "SELECT MAX(contato_id) AS max FROM marpaorgpess";
                $id = query2($sql);
                $id = $id[0]['max'];
                $id++;

                $tipo = 'INSERT';
                $where = '';
            } else {
                $tipo = 'UPDATE';
                $where = "contato_id = $id";
            }

            $aniversario = array_reverse(explode('/', $_POST['dtanivemp']));
            $aniversario = implode('-', $aniversario);

            $aniversario_contato = array_reverse(explode('/', $_POST['dtaniver']));
            $aniversario_contato = implode('-', $aniversario_contato);

            $temp = [];
            $temp['contato_id'] = $id;
            $temp['usuario']    = getUsuario();
            $temp['nome']       = str_replace(['"', "'"], '', $_POST['nome']);
            $temp['dtanivemp']  = !empty($aniversario) ? $aniversario : 'NULL';
            $temp['obs']        = str_replace(['"', "'"], '', $_POST['obs']);
            $temp['endereco']   = str_replace(['"', "'"], '', $_POST['endereco']);
            $temp['cep']        = str_replace(['"', "'"], '', $_POST['cep']);
            $temp['uf']         = $_POST['uf'];
            $temp['cidade']     = str_replace(['"', "'"], '', $_POST['cidade']);
            $temp['bairro']     = str_replace(['"', "'"], '', $_POST['bairro']);
            $temp['codpais']    = $_POST['codpais'];
            $temp['contato']    = str_replace(['"', "'"], '', $_POST['contato']);
            $temp['dtaniver']   = !empty($aniversario_contato) ? $aniversario_contato : 'NULL';
            $temp['email']      = str_replace(['"', "'"], '', $_POST['email']);
            $temp['fones']      = str_replace(['"', "'"], '', $_POST['fones']);
            $temp['cels']       = str_replace(['"', "'"], '', $_POST['cels']);
            $temp['marpaorgpess_contatos_id_1'] = $ids_contatos[1];
            $temp['marpaorgpess_contatos_id_2'] = $ids_contatos[2];
            $temp['marpaorgpess_contatos_id_3'] = $ids_contatos[3];
            $temp['marpaorgpess_contatos_id_4'] = $ids_contatos[4];

            $sql = montaSQL($temp, 'marpaorgpess', $tipo, $where);
            query2($sql);

            $mensagem = "Dados salvos com sucesso!";
            $tipo_msgm = '';
        } else {
            $mensagem = "Erro ao enviar o formulário";
            $tipo_msgm = "error";
        }

        redireciona(getLink() . "avisos&mensagem=$mensagem&tipo=$tipo_msgm&redireciona=incluir&id=$id&visualizacao=1");
    }
}