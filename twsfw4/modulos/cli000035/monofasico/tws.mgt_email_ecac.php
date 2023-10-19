<?php
/*
 * Data Criacao: 02/2023
 * 
 * Autor:  Tomaz Bettio
 *
 * Descricao: log robo monofasico diario
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
$emailConsultor = '';

//ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);

class mgt_email_ecac
{
	var $funcoes_publicas = array(
		'index' 		=> true,
        'schedule' 		=> true,
	);
    
	//Classe relatorio
	private $_relatorio;

	//Nome do programa
	private $_programa;

	//Titulo do relatorio
	private $_titulo;

	//Indica que se é teste (não envia email se for)
	private $_teste;

	//Dados
	private $_dados;

	private $_texto;
    

	public function __construct()
	{
		$this->_programa = get_class($this);
		$this->_titulo = '';
		
		$this->_teste = true;


		conectaMF();
		conectaERP();

		$param = [];
		$param['programa'] = $this->_programa;

		if (false) {
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De', 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até', 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '3', 'pergunta' => 'Cliente', 'variavel' => 'CLIENTE', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
			sys004::inclui(['programa' => $this->_programa, 'ordem' => '4', 'pergunta' => 'Analista', 'variavel' => 'RECURSO', 'tipo' => 'A', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		}
	}

	public function index()
	{
		$ret = '';
		$filtro = $this->_relatorio->getFiltro();

		$dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		$dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';


		// if (!$this->_relatorio->getPrimeira()) {
		// $this->getDados();
		// $this->montaColunas();

		$this->_relatorio->setDados($this->getDados());



		$ret .= $this->_relatorio;

		return $ret;
	}

	public function schedule($param = ''){

        ini_set('display_errors',0);
		ini_set('display_startup_erros',0);
		error_reporting(E_ALL);
        $dados = $this->getDados();

        // print_r($dados);

        if (count($dados) > 0){
    
            foreach($dados as $cnpj => $titulos){

                $meses = $this->getMeses($titulos['valor']);
                $ajustaDados = $this->ajustaDados($titulos['valor']);
                $totais = $this->geraTotais($titulos['valor']);

                // print_r($titulos);

                $param = [];
                $this->_relatorio = new relatorio01($param);

                $this->_relatorio->addColuna(array('campo' => 'cod_imposto'	, 'etiqueta' => 'Código Imposto'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'), 0);

                $this->_relatorio->addColuna(array('campo' => 'denominacao'	, 'etiqueta' => 'Descrição do código'		, 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'), 0);

               
                foreach ($meses as $mes){
                    // print_r($mes);
                    $this->_relatorio->addColuna(array('campo' => 'valor'.$mes		, 'etiqueta' => 'Mês '.$mes			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'), 0);

                    $this->_relatorio->addColuna(array('campo' => 'valor'.$mes		, 'etiqueta' => 'Total Mes '.$mes			, 'tipo' => 'V', 'width' => 100, 'posicao' => 'D'), 1);
                    
                    }
                
                // print_r($ajustaDados);

                $this->_relatorio->setMensagemInicioEmail('Em anexo impostos pagos pelo cliente nos últimos 6 meses. Caso não tenha valor apontando em algum período o imposto foi compensado ou não pago.' . '<br>' . '<br>');
                $this->_relatorio->setDados($ajustaDados);
                $this->_relatorio->setDados($totais, 1);
                $this->_relatorio->setToExcel(true, 'Impostos_Pagos_Tabela');
                $this->_relatorio->setToPDF(true, 'Impostos_Pagos_Tabela');
                $this->_relatorio->setTitulo("Impostos Pagos ECAC - Nº Contrato: " . $titulos['info']['id_credito_apurado_ecac'] . " Razão Social: " . $titulos['info']['razao_social']);
                
                $sql = "UPDATE mgt_credito_apurado_ecac SET email_enviado = 'S' WHERE cnpj = '" . $cnpj . "'";
                $sql = queryMF($sql);

                
                $this->_relatorio->enviaEmail('tomaz.bettio@verticais.com.br;'.'fiscal@grupomarpa.com.br;');
                
            }
        
        }

    
		
		echo "Email enviado";
	}

    private function getMeses($titulos){
        $ret = [];
        
        foreach ($titulos as $denominacao => $codigos){
            // print_r($codigos);
            foreach($codigos as $codigo => $valores){
                // print_r($valores);
                foreach($valores as $mes => $valor){
                    // print_r($mes);

                    if (!isset($ret[$mes])){
                    $ret[$mes] = $mes;
                    // print_r($mes);
                    }
                }
            }
        }
        // print_r($mes);
        return $ret;
    }


    private function ajustaDados($titulos){
        $ret = [];
        // print_r($titulos);
        foreach ($titulos as $denominacao => $codigos){
            // print_r($denominacao);
            $temp = [];
            foreach($codigos as $codigo => $valores){
                // print_r($codigo);
                foreach($valores as $valor => $v){
                    // print_r($valor);
                    $temp['valor'.$valor] = $v['valor'];
                    // print_r($v['valor']);
                }
                $temp['cod_imposto'] = $codigo;
                $temp['denominacao'] = $denominacao;
                $ret[] = $temp;
            }
          
        }

        // print_r($ret);
        return $ret;
     
    }

    private function geraTotais($titulos){
        $ret = [];

        if (count($titulos) >  0 ){
            $temp = [];

            foreach ($titulos as $denominacao => $codigos){
                // print_r($denominacao);

                foreach($codigos as $codigo => $valores){
                    // print_r($valores);
                    foreach($valores as $mes => $v){
                        // print_r($valor);
                        if(!isset($temp['valor' . $mes])){
                            $temp['valor'.$mes] = 0;
                        }
                        $temp['valor'.$mes] += $v['valor'];
                        }
                }

            }
            $ret[] = $temp;
        }
        return $ret;
    }

	private function getDados()
	{
		$ret = [];

		$sql = "SELECT 
                    id_credito_apurado_ecac, num_doc, cnpj, razao_social, valor, cod_imposto, DATE_FORMAT(STR_TO_DATE(data, '%Y-%m-%d'), '%d/%m/%Y') as data_padrao, email_enviado
					FROM 
						mgt_credito_apurado_ecac
                    where 
                        email_enviado = 'N'
					order by data ASC
		";
	
		$rows = queryMF($sql);

		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
                $temp = [];
                $cnpj = $row['cnpj'];
                $cod_imposto = $row['cod_imposto'];
                $dataMes = substr($row['data_padrao'], 3, 2);

				$temp['id_credito_apurado_ecac']    = $row['id_credito_apurado_ecac'];
				$temp['num_doc'] 				    = $row['num_doc'];
				$temp['cnpj'] 			            = $cnpj;
				$temp['razao_social'] 			    = $row['razao_social'];
				$temp['valor'] 		                = $row['valor'];
				$temp['data_padrao'] 		        = $row['data_padrao'];
				$temp['email_enviado']              = $row['email_enviado'];
                $temp['valor' . $dataMes]           = $row['valor'];
                $temp['cod_imposto']                = $cod_imposto;

                $sqldois = "SELECT * FROM mgt_codigos_ecac WHERE Codigo_Receita = $cod_imposto AND LOWER(Denominacao) NOT REGEXP 'multa' AND LOWER(Denominacao) NOT REGEXP 'parcelamento'";
                $rowsDois = queryMF($sqldois);

                foreach($rowsDois as $r){
                    $temp['denominacao'] = $r['Denominacao'];
                }

                $denominacao = $temp['denominacao'];

                if( !isset($ret[$cnpj][$cod_imposto][$dataMes]['valor'])) {
                    $ret[$cnpj][$denominacao][$cod_imposto][$dataMes] = []; 
                    $ret[$cnpj][$denominacao][$cod_imposto][$dataMes]['valor'] = 0;
                }
                

                $ret[$cnpj]['valor'][$denominacao][$cod_imposto][$dataMes]['valor'] += $temp['valor'];
                $ret[$cnpj]['info']['razao_social'] = $temp['razao_social']; 
                $ret[$cnpj]['info']['id_credito_apurado_ecac'] = $temp['id_credito_apurado_ecac'];                 
                
                }

                
			}
            return $ret;
        }

		
    }



