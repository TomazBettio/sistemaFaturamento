<?php
if (!defined('TWSiNet') || !TWSiNet)
	die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class rh_conexao_pdf {
    // ID entidade
    private $_id;

    // Tabela entidade
    private $_tabela;

    // programa
	private $_programa;

	// titulo
	private $_titulo;

	// Classe relatorio
	private $_relatorio;

	private $_relatorioPisCofins01;

	private $_relatorioPisCofins02;

	private $_relatorioPisCofins03;

	private $_relatorioPisCofins04;

	// Dados
	private $_dados;

	// cnpj
	private $_cnpj;

	// path
	private $_path;

	// Colunas dos itens selecionados
	private $_colunas;

	// nome do cliente
	private $_razao;

    public function __construct($tabela, $id) {
		global $config;

        $this->_tabela = $tabela;
        $this->_id = $id;
		$this->_programa = get_class($this);
		$this->_titulo = 'PDF - Contrato de Expectativa';

        $this->criaPasta($config['arquivosDir']);
		$this->_path = $config['arquivosDir'] . $tabela . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
		$this->_teste = false;

		$param = [];
		$param['programa'] = $this->_programa;
		$param['titulo'] = $this->_titulo;
		$this->_relatorio = new tabela_pdf01($param);
		$this->_relatorio->setStripe(true);

		$this->_relatorio->setModoConversaoPdf('WKHTMLTOPDF');

		$this->_colunas = [
			'Colaborador',
            'Gestor',
            'Inicio',
            'Fim',
            'Desempenho',
            'Comportamento',
            'Data',
            'Data do próximo alinhamento'
		];
	}

    public function index() {
        $this->_dados = $this->getDados();

        if(is_array($this->_dados) && count($this->_dados) > 0) {
            $arquivo = $this->_path . 'conexao.pdf';
            $temp = [];
            $temp[0] = $arquivo;
            $temp[1] = 'conexao.pdf';
            $arquivos[] = $temp;

            $htmlPDF = $this->geraHtml();

            $paramPDF = [];
	        $paramPDF['orientacao'] = 'L';
            $PDF = new pdf_exporta02($paramPDF);
	        $PDF->setModoConversao('HTML2PDF');
            $PDF->setHTML($htmlPDF);
	        $PDF->grava($arquivo);
	        unset($PDF);
        } else {
            addPortalMensagem('Não foram encontrados dados para gerar os relat&oacute;rios!', 'error');
        }
    }

    private function getDados() {
        $sql = "SELECT * FROM rh_conexao_colaborador WHERE id_colaborador = {$this->_id}";
        $rows = query($sql);

        $sql = "SELECT nome FROM rh_colaboradores WHERE id = {$this->_id}";
        $colaborador = query($sql);

        $conexao = [];
        $conexao['id']                         = $rows[0]['id'];
        $conexao['nome']                       = $colaborador[0]['nome'];
        $conexao['aniversario']                = $rows[0]['aniversario'];
        $conexao['anseios_pessoais']           = $rows[0]['anseios_pessoais'];
        $conexao['hobbies']                    = $rows[0]['hobbies'];
        $conexao['habilidades_pessoais']       = $rows[0]['habilidades_pessoais'];
        $conexao['comida_preferida']           = $rows[0]['comida_preferida'];
        $conexao['nao_gosta']                  = $rows[0]['nao_gosta'];
        $conexao['outros_insights']            = $rows[0]['outros_insights'];
        $conexao['necessidades']               = $rows[0]['necessidades'];
        $conexao['perfil_comportamental']      = $rows[0]['perfil_comportamental'];
        $conexao['comunicacao']                = $rows[0]['comunicacao'];
        $conexao['perfil_lideranca']           = $rows[0]['perfil_lideranca'];
        $conexao['anseios_profissionais']      = $rows[0]['anseios_profissionais'];
        $conexao['valores']                    = $rows[0]['valores'];
        $conexao['habilidades_profissionais']  = $rows[0]['habilidades_profissionais'];

        return $conexao;
    }

    private function criaPasta($config) {
        if(!file_exists($config . DIRECTORY_SEPARATOR . $this->_tabela)) {
            mkdir($config . DIRECTORY_SEPARATOR . $this->_tabela, 0777, true);
			chMod($config . DIRECTORY_SEPARATOR . $this->_tabela, 0777);
        }
        if(!file_exists($config . DIRECTORY_SEPARATOR . $this->_tabela . DIRECTORY_SEPARATOR . $this->_id)) {
            mkdir($config . DIRECTORY_SEPARATOR . $this->_tabela . DIRECTORY_SEPARATOR . $this->_id, 0777, true);
			chMod($config . DIRECTORY_SEPARATOR . $this->_tabela . DIRECTORY_SEPARATOR . $this->_id, 0777);
        }
    }

    private function geraHtml() {
        $estilo = '<style>
                        table {
                            border-collapse: separate;
                            display: inline-table;
                            margin: 10px;
                            font-size: 10px;
                        }
                        .titulo {
                            text-align: center;
                            background-color: grey;
                            color: white;
                            padding: 5px;
                            font-size: 20px;
                        }
                        table td {
                            border-spacing: 10px;
                            width: 200px;
                            vertical-align: top;
                            background-color: #E8E8E8;
                        }
                    </style>';

        $htmlPDF = '<table>
                        <thead>
                            <tr>
                                <th class="titulo" colspan="3">
                                    Conexão com o Colaborador
                                </th>
                            </tr>
                            <tr>
                                <td style="height: 10px; border: none; background-color: white;" colspan="3"></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2"><b>Nome:</b> '.$this->_dados['nome'].'</td>
                                <td><b>Aniversário:</b> '.$this->_dados['aniversario'].'</td>
                            </tr>
                            <tr colspan="3">
                                <td><b>Anseios pessoais:</b><br> '.$this->_dados['anseios_pessoais'].'</td>
                                <td><b>Hobbies:</b><br> '.$this->_dados['hobies'].'</td>
                                <td><b>Talentos, dons pessoais:</b><br> '.$this->_dados['habilidades_pessoais'].'</td>
                            </tr>
                            <tr>
                                <td>
                                    <b>O que mais gosta de comer:</b><br>
                                    '.$this->_dados['comida_preferida'].'
                                </td>
                                <td>
                                    <b>O que não gostam que façam com ele:</b><br>
                                    '.$this->_dados['nao_gosta'].'
                                </td>
                                <td>
                                    <b>Outros insights ou sacadas:</b><br>
                                    '.$this->_dados['outros_insights'].'
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <b>Necessidades de desenvolvimento profissional/objetivos dentro da empresa:<br>
                                    Qual futuro você sonha em ter dentro da empresa:</b><br>
                                    '.$this->_dados['necessidades'].'
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Perfil Comportamental:</b><br>
                                    '.$this->_dados['perfil_comportamental'].'
                                </td>
                                <td>
                                    <b>Estilo de comunicação:</b><br>
                                    '.$this->_dados['comunicacao'].'
                                </td>
                                <td>
                                    <b>Perfil de Liderança que precisa:</b><br>
                                    '.$this->_dados['perfil_lideranca'].'
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Anseios profissionais:</b><br>
                                    '.$this->_dados['anseios_profissionais'].'
                                </td>
                                <td>
                                    <b>Valores:</b><br>
                                    '.$this->_dados['valores'].'
                                </td>
                                <td>
                                    <b>Habilidades Profissionais:</b><br>
                                    '.$this->_dados['habilidades_profissionais'].'
                                </td>
                            </tr>
                        </tbody>
                    </table>';

        return $estilo . $htmlPDF;
    }
}