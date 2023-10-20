<?php
if (!defined('TWSiNet') || !TWSiNet)
	die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class rh_contrato_pdf {
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

	// Dados
	private $_dados;

	// path
	private $_path;

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
        // $this->montaColunas();

        if(is_array($this->_dados) && count($this->_dados) > 0) {
            // $this->_relatorio->setDados($dados);
            $arquivo = $this->_path . 'contrato_expectativa.pdf';
            $temp = [];
            $temp[0] = $arquivo;
            $temp[1] = 'contrato_expectativa.pdf';
            $arquivos[] = $temp;

            // $this->_relatorio->setModoConversaoPdf('HTML2PDF');
            // $this->_relatorio->gerarPdf($arquivo, 'Cotrato de Expectativa');

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
        $sql = "SELECT * FROM rh_contrato_expectativa WHERE id_colaborador = {$this->_id}";
        $rows = query($sql);

        $sql = "SELECT nome FROM rh_colaboradores WHERE id = {$this->_id}";
        $colaborador = query($sql);

        $contrato = [];
        $contrato['id']             = $rows[0]['id'];
        $contrato['colaborador']    = $colaborador[0]['nome'];
        $contrato['gestor']         = $rows[0]['gestor'];
        $contrato['inicio']         = $rows[0]['inicio'];
        $contrato['fim']            = $rows[0]['fim'];
        $contrato['desempenho']     = $rows[0]['desempenho'];
        $contrato['comportamento']  = $rows[0]['comportamento'];
        $contrato['data']           = $rows[0]['data'];
        $contrato['data_proximo']   = $rows[0]['data_proximo'];

        return $contrato;
    }

    private function montaColunas() {
        $this->_relatorio->addColuna(array('campo' => 'colaborador', 'etiqueta' => 'Nome colaborador: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'gestor', 'etiqueta' => 'Gestor: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'inicio', 'etiqueta' => 'Inicio: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'fim', 'etiqueta' => 'Fim: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'desempenho', 'etiqueta' => 'Desempenho: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'comportamento', 'etiqueta' => 'Comportamento: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'data', 'etiqueta' => 'Data: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
        $this->_relatorio->addColuna(array('campo' => 'data_proximo', 'etiqueta' => 'Data da próxima negociação: ', 'tipo' => 'T', 'width' => 80, 'posicao' => 'C'));
    }

    private function criaPasta($config) {
        if(!file_exists($config)) {
            mkdir($config, 0777, true);
			chMod($config, 0777);
        }
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
                        * {
                            width: 100px;
                            margin: auto;
                        }
                        table {
                            width: 300px;
                            border-collapse: collapse;
                        }
                        .titulo-principal {
                            border: solid;
                            text-align: center;
                            font-size: 15px;
                            padding-bottom: -10px;
                            margin: auto;
                        }
                        .titulo {
                            background-color: grey;
                            padding: 5px;
                        }
                        .centro {
                            text-align: center;
                        }
                        .esquerda {
                            text-align: left;
                        }
                        .sem-borda-lados {
                            border-right: none;
                            border-left: none;
                        }
                        .tabela2 {
                            position: absolute;
                            bottom: 150px;
                        }
                        .tabela2 th, .tabela2 td {
                            border: none;
                        }
                    </style>';
        $htmlPDF = '<table cellspacing=0 cellpadding=0 border=0>
                        <thead>
                            <tr>
                                <td class="titulo-principal" colspan="5">
                                    Contrato de Expectativa
                                </td>
                            </tr>
                            <tr>
                                <td style="height: 10px; border-left:none;" class="sem-borda-lados" colspan="5"></td>
                            </tr>
                            <tr class="centro">
                                <th class="borda">Colaborador:</th>
                                <td style="width: 200px;" class="borda">'.$this->_dados['colaborador'].'</td>
                                <th style="width: 50px; margin-align: center;" class="borda" rowspan="2">Período</th>
                                <th class="borda">Inicio:</th>
                                <td class="borda">'.$this->_dados['inicio'].'</td>
                            </tr>
                            <tr class="centro">
                                <th class="borda">Gestor:</th>
                                <td style="width: 200px" class="borda">'.$this->_dados['gestor'].'</td>
                                <th class="borda">Fim:</th>
                                <td class="borda">'.$this->_dados['fim'].'</td>
                            </tr>
                            <tr>
                                <td style="height: 5px; border: none;" class="sem-borda-lados" colspan="5"></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: none; padding-rigth: 10px;" class="titulo" colspan="2"><b>Desempenho</b></td>
                                <td style="border: none; padding-left: 10px;" class="titulo" colspan="3"><b>Comportamento</b></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="border: none; padding-rigth: 10px;" class="sem-borda-lados">'.nl2br($this->_dados['desempenho']).'</td>
                                <td colspan="3" style="border: none; padding-left: 10px;" class="sem-borda-lados">'.nl2br($this->_dados['comportamento']).'</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="tabela2">
                        <tbody>
                            <tr>
                                <td height="100px" style="border: 1.5px solid" class="borda" colspan="9">
                                    <div height="100px" style="width: 550px; text-align: center; margin: auto;">
                                        Espero que as expectativas acima alinhadas, possam ser cumpridas com responsabilidade a fim de mantermos a qualidade em nossos serviços prestados, e para que possamos buscar constantemente o desenvolvimento alcançando novos desafios e novas metas.
                                    </div>        
                                </td>
                            </tr>
                            <tr>
                                <td style="height: 10px; border: none;" class="sem-borda-lados" colspan="9"></td>
                            </tr>
                            <tr>
                                <th style="width: 25px; text-align: right;">Data:</th>
                                <td class="esquerda">'.$this->_dados['data'].'</td>
                                <th style="width: 50px;"></th>
                                <th style="width: 135px; text-align: right;" colspan="3">Data do próximo alinhamento:</th>
                                <td class="esquerda">'.$this->_dados['data_proximo'].'</td>
                            </tr>
                            <tr>
                                <td style="height: 50px; border: none;" class="sem-borda-lados" colspan="9"></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; border-top: 1px solid;" colspan="4">
                                    ________________________________________<br>
                                    <b>Colaborador</b><br>
                                    '.$this->_dados['colaborador'].'
                                </td>
                                <td style="text-align: center; border-top: 1px solid;" colspan="4">
                                    ________________________________________<br>
                                    <b>Gestor</b><br>
                                    '.$this->_dados['gestor'].'
                                </td>
                            </tr>
                        </tbody>
                    </table>';

        return $estilo . $htmlPDF;
    }
}