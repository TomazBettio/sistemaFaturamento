<?php
       /*
 * Data Criacao 20/11/17
 * Autor: TWS - Alexandre Thiel
 *
 * Descricao:
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class geraMapa{
	var $funcoes_publicas = array(
			'index' 		=> true
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
	
	public function __construct(){
		
		conectaCONSULT();
		$this->_programa = get_class($this);
		$this->_titulo = '';
		
		$this->_teste = false;
		
		$param = [];
		$param['filtro'] = false;
		$param['programa'] = $this->_programa;
		$this->_relatorio = new relatorio01($param);
		


		// $this-> _relatorio->setParamTabela($param);
		// if(true){
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'DATAINI', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// 	sys004::inclui(['programa' => $this->_programa, 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'DATAFIM', 'tipo' => 'D', 'tamanho' => '8', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
		// }
	}
	
	public function index(){
		$ret = '';
		// $filtro = $this->_relatorio->getFiltro();
		
		// $dtDe 	= isset($filtro['DATAINI']) ? $filtro['DATAINI'] : '';
		// $dtAte 	= isset($filtro['DATAFIM']) ? $filtro['DATAFIM'] : '';
		
		$this->_relatorio->setTitulo("Vendedores");
		$this->montaColunas();
		//if(!$this->_relatorio->getPrimeira()){
		
		$sessao = 0;
			
		$vendedores = $this->getDados();
		foreach($vendedores as $vendedor){
			$dados = $this->geraComissaoLista($vendedor['codigovendedor']);
			$this->_relatorio->setDados($dados,$sessao);
			$this->_relatorio->setTituloSecao($sessao, $vendedor['vendedor']);
			$sessao++;
		}

		
		// $vendedoresNovos = $this->getDados1();

		
		// $this->_relatorio->setDados($dados, 2);
		// $this->_relatorio->setTituloSecao(2,"");

		// $dados = $this->geraComissaoListaNovos();
		// $this->_relatorio->setDados($dados, 3);
		// $this->_relatorio->setTituloSecao(3,"");


		//To Do: Verificar quebra de linha nas colunas
		$this->_relatorio->setNowrap(false,2);
		$ret .= $this->_relatorio;
		
		return $ret;
	}
	
	//------------------------------- SCHEDULE
	// public function schedule($param = ''){
	// 	ini_set('display_errors',0);
	// 	ini_set('display_startup_erros',0);
	// 	error_reporting(E_ALL);
	// $this->_relatorio->setTitulo("Relatório Diário de Cobrança");
	// $this->montaColunas();
	// //if(!$this->_relatorio->getPrimeira()){
		
		
	// $dados = $this->getDadosTot();
	// $this->_relatorio->setDados($dados, 0);
	// $this->_relatorio->setTituloSecao(0,"");
	
	// $dados = $this->getDadosSig();
	// $this->_relatorio->setDados($dados, 1);
	// $this->_relatorio->setTituloSecao(1,"");


	// //To Do: Verificar quebra de linha nas colunas
	// $this->_relatorio->setNowrap(false,1);
	// $ret .= $this->_relatorio;
		

	// 	$this->_relatorio->enviaEmail('vitor.valadas@verticais.com.br');
	// 	//log::gravaLog('agenda_sem_os', 'Email teste enviado');
	// 	echo "Email enviado";
				

	//}
		
	
	private function montaColunas(){
		

		$this->_relatorio->addColuna(array('campo' => '$r[empresa]'			, 'etiqueta' => 'Cliente'	, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$r[descrtipolan]'	, 'etiqueta' => 'CA/AB/CTR'	, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$r[nome]'			, 'etiqueta' => 'Trabalho'	, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$indicacao'			, 'etiqueta' => 'Indicação'	, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$total'				, 'etiqueta' => 'Taxa'		, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$taxa'				, 'etiqueta' => 'Data'		, 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$honorarios'			, 'etiqueta' => 'Parcela'	, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$comissao'			, 'etiqueta' => 'Total'		, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$r[pagamento]'		, 'etiqueta' => 'Honorários', 'tipo' => 'T', 'width' =>  300, 'posicao' => 'E'));
		$this->_relatorio->addColuna(array('campo' => '$r[recibo]'			, 'etiqueta' => 'Comissão'	, 'tipo' => 'T', 'width' =>  200, 'posicao' => 'E'));

	}
	
	private function getDados(){
		$ret = [];
		
		$sql = 
		"SELECT
			codigovendedor,
			vendedor, 
			email
		FROM
			marpavendedor
		WHERE
			codigovendedor NOT IN(1, 20, 365, 380, 400, 221, 375, 361) 
		AND
			(codigovendedor < 380 OR codigovendedor = 1080) 
		AND    
			codstatus = 1";
			$rows = query2($sql);
			//echo "$sql <br> ";
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				//$temp['yusuario'] = $row['yusuario'];
				$temp['codigovendedor'] = $row['codigovendedor'];
				$temp['vendedor'] = $row['vendedor'];
				$temp['email'] = $row['email'];
		
				$ret[] = $temp;
			}
		}
		return $ret;
	}

	private function getDados1(){
		$ret = [];
		
		$sql = 
		"SELECT
			codigovendedor,
			vendedor,
			email
		FROM
			marpavendedor
		WHERE
			codigovendedor IN(1075) ";
		$rows = query2($sql);
		//echo "$sql <br> ";
		
		if(is_array($rows) && count($rows) > 0){
			foreach ($rows as $row){
				$temp = [];
				$temp['codigovendedor'] = $row['codigovendedor'];
				$temp['vendedor'] = mb_convert_encoding($row['vendedor'], "UTF-8", "CP1252");
				$temp['email'] = $row['email'];

				$ret[] = $temp;
			}
		}
	
		return $ret;
	}

//função	
	public function geraComissaoLista($codigovendedor) {
  		
  		$sql = "SELECT distinct on 
			(parcela_id) sigla, empresa, num_ca, descrtipolan, vlparc, mp.seq, mp.numlan, mp.tipolan,
		  me.nome, mtc.ca, mtc.contrato, mtc.aporte, mtc.honorarios_tramitacao, mtc.procedimento_com_taxa,
		  vendedor, TO_CHAR(mp.dtpag, 'dd/mm/YYYY') as pagamento, 
		  	CASE WHEN 
				me=1 
			THEN 
				taxa_me 
			ELSE 
				taxa 
			END as 
				taxac,
		  	mf.valor, descrtipomoedarz, tipomoeda, marpatipoprocedimento_id, valdeducoes, me, parcela_id, mp.dtpag,
		  	CASE WHEN
		  		(nrnota>0) 
			THEN 
				nrnota 
			ELSE 
				numrec 
			END 
				as recibo
			FROM 
				marpafinpc mp 
			LEFT JOIN marpafin mf USING
				(tipolan, numlan) 
			LEFT JOIN 
				marpacliente mc USING(sigla) 
			LEFT JOIN 
				marpatipolan ml USING (tipolan)
			LEFT JOIN 
				marpatipoprocedimento me USING(marpatipoprocedimento_id) 
			LEFT JOIN 
				marpavendedor mv 
			ON 
				(mc.codigovendedor = mv.codigovendedor)
			LEFT JOIN
				marpatipomoeda USING(tipomoeda) 
			LEFT JOIN 
				marpafincomis mfc 
			ON
				(mfc.tipolan = mp.tipolan AND mfc.numlan = mp.numlan AND mfc.seq = mp.seq AND mfc.n = mp.n)
			LEFT JOIN
				 marpatipocomissao mtc USING(tipo_comissao)
			WHERE 
				mp.dtpag BETWEEN '2021-02-03' AND '2022-02-03' 
			AND 
				mc.codigovendedor = '87'
			AND 
				mp.codigotipostatus IN(3,9) 
			AND 
				mfc.codigovendedor IS NULL AND mf.tipolan NOT IN(13, 14)
			AND 
				marpatipoprocedimento_id NOT IN (45, 106) 
			AND 
				mc.comissao = 1 
			AND 
				num_ca > 0
			AND 
				((pendencias = 0) or (pendencias is null)) 
			AND 
				mp.comissao = 1";
 	//	echo $sql . "<hr>";
		$res = query2($sql);
	//WHERE mp.dtpag >= '".$de."' and mp.dtpag <= '".$ate."' and mc.codigovendedor = '".$codigovendedor."'

  		// $html.="<br /><br /><h2 style='text-align: center'>Mapa de comissões - ".$vendedor." Período de ".$_POST['de']." até ".$_POST['ate'].".</h2>";
  		// $html.="<table width=\"95%\" style=\"font: 10px verdana,arial,helvetica; margin-left: 15px\">";
  		// $html.="<tr> <th>Cliente</th> <th>CA / AB / CTR</th> <th>Trabalho</th> <th>Indicação</th> <th>Total</th> <th>Taxas</th> <th>Honorários</th> <th>Comissão</th>";
  		// $html.="<th>Data</th> <th>Parcela</th> <th>Baixar</th> </tr>";
  		$total_valor = 0;
  		$total_taxa = 0;
  		$total_honorarios = 0;
  		$total_comissao = 0;
		//$html.="<form action='?on=consultores&in=comissoes&ac=baixar' method='POST'>";
  		foreach($res as $r) {
			$r = [];
			$sql = "SELECT COUNT(*) as t FROM marpaindicacao WHERE sigla = $r[sigla]";
			$indicacao = query2($sql);
			$indicacao = $indicacao[0]['t'];
			if($indicacao>0) $indicacao = 'Sim'; 
			else $indicacao = 'Não';

			if($r['tipolan']==16 && $r['marpatipoprocedimento_id']==1 && $r['seq']==1) // contrato premium
			{
			  if($r['me']==1)
				$r['taxac'] = 140;
			  else
				$r['taxac'] = 355;
			}
			if($r['tipolan']==16 && $r['marpatipoprocedimento_id']==1 && $r['seq']==2)
			{
			  if($r['me']==1)
				$r['taxac'] = 300;
			  else
				$r['taxac'] = 745;
			}
			if ($r['seq'] > 1) // todos os procedimentos conta a taxa so na primeira parcela, depois apenas 15.
			{
				if ($r['taxac'] > 25)
				{
				$r['taxac'] = 15;
				}
			}

  			$total = number_format($r['vlparc'],2,',','.');
  			if($r['num_ca']>0) $numero = " - ".$r['num_ca'];
  			else $numero = '';
  			$honorarios = $r['vlparc']-$r['taxac'];
  			if($r['taxac']<0) $r['taxac'] = 0;
  			$taxa = number_format($r['taxac'],2,',','.');
			if($honorarios<0) $honorarios = 0;
			
			////Ação Judicial - Aditamento para processo - Anuidade de acompanhamento - Recadastramento e Atualização de Documentos
			if($r['marpatipoprocedimento_id']==107) {	 
				$comissao = ($honorarios * 10)/100;		
			}elseif($r['marpatipoprocedimento_id']==24 || $r['marpatipoprocedimento_id']==47)
				  $comissao = $honorarios * $r['contrato']/100;
			//Comissão Tramitação (PR)
			elseif($r['honorarios_tramitacao']>0 && $r['marpatipoprocedimento_id']==97)
				  $comissao = $honorarios * $r['honorarios_tramitacao']/100;
			//Comissão Procedimentos com taxa (PR)
				elseif($r['procedimento_com_taxa']>0 && $r['taxac']>0)
				  $comissao = $honorarios * $r['procedimento_com_taxa']/100;
			//Comissão CA	
			else
				$comissao = $honorarios * $r['ca']/100;
			
			
			//Comissão Aporte
  			if($r['marpatipoprocedimento_id']==42 || $r['marpatipoprocedimento_id']==45){
				if ($r['tipolan']==16) {  // Contrato Premium
					if (($codigovendedor == 87) || ($codigovendedor == 33))
						$comissao = ($honorarios * 10)/100;
					else 
						$comissao = ($honorarios * 30)/100;
				}else { // outros contratos buscas da tabela marpatipocomissao
					$comissao = $honorarios * $r['contrato']/100;
				}	
			//Comissão Contrato
			}elseif(
			($r['marpatipoprocedimento_id']==138) || ($r['marpatipoprocedimento_id']==137) || ($r['marpatipoprocedimento_id']==6)
				|| ($r['marpatipoprocedimento_id']==147) || ($r['marpatipoprocedimento_id']==100) 
				|| ($r['marpatipoprocedimento_id']==21) || ($r['marpatipoprocedimento_id']==20) || ($r['marpatipoprocedimento_id']==157)
			) {	
				//Apresentação de Procuração - Aditamento para processo - Anuidade de acompanhamento - Recadastramento e Atualização de Documentos
				// Prova de Uso - Decênio - Vigilância de acompanhamento - Vigilância Global - Vigilância Técnica
				$comissao = ($honorarios * 5)/100;		
			}elseif($r['marpatipoprocedimento_id']==107) {	
				//Ação Judicial - Aditamento para processo - Anuidade de acompanhamento - Recadastramento e Atualização de Documentos
				// Prova de Uso - 
				$comissao = ($honorarios * 10)/100;		
			}elseif($r['marpatipoprocedimento_id']==104) {	//Decênio
				//Decênio
				$comissao = ($honorarios * 20)/100;		
			} 
			/*else {
				
			  if ($indicacao == 'Sim')
				$comissao = ($honorarios * 5)/100;
			  else
				$comissao = $honorarios * $r['ca']/100;
			}*/
		  
		  
			//nova função conforme codigo buscar valor
			$resultDados = $this->valorComissaoMapa($r['marpatipoprocedimento_id']);
			foreach ($resultDados as $result1) {
				if (($codigovendedor == 87) || ($codigovendedor == 33))
					$vCValorComissao = $result1['PROREPRESENTANTEPR'];
				else $vCValorComissao = $result1['PROREPRESENTANTERS'];
						
				if ($r['tipolan']==17) {  // Contrato Premium
					$vCValorComissao = 30.00;
				}else if (($r['tipolan']==16) && ($r['seq']==1)) {  // Contrato Premium
					if (($codigovendedor == 87) || ($codigovendedor == 33))
						$vCValorComissao = 30.00;
					else 
						$vCValorComissao = 30.00;
				} else {
					if (($r['tipolan']==16)) {  // Contrato Premium
						if (($codigovendedor == 87) || ($codigovendedor == 33))
							$vCValorComissao = 10.00;
						else 
							$vCValorComissao = 10.00;
					}	
				}		
				
		    }	
			$comissao = ($honorarios * $vCValorComissao)/100;	
			
  			$total_valor = $total_valor + $r['vlparc'];
  			$total_taxa = $total_taxa + $r['taxac'];
  			$total_honorarios = $total_honorarios + $honorarios;
  			$total_comissao = $total_comissao + $comissao;
  			$honorarios = number_format($honorarios,2,',','.');
  			$comissao = number_format($comissao,2,',','.');
  			// $html.="<tr> <td>$r[empresa]</td> <td>$r[descrtipolan] $numero</td> <td>$r[nome]</td> <td>$indicacao</td>";
  			// $html.="<td style=\"text-align: right\">$total</td> <td style=\"text-align: right\">$taxa</td> ";
  			// $html.="<td style=\"text-align: right\">$honorarios</td> <td style=\"text-align: right\">$comissao</td> ";
  			// $html.="<td style=\"text-align: right\">$r[pagamento]</td> <td style=\"text-align: right\">$r[recibo] - $r[seq]</td>";
			// $html.="<td style='text-align: center'><input type='checkbox' name='parcela_id[]' value='".$r['parcela_id']."|$comissao' value='Baixar' /></td> </tr>";
  		}
  		$sql =
		 	"SELECT empresa as cliente, ma.numero, valor, TO_CHAR(ma.dtpag, 'dd/mm/YYYY') as pagamento, busca
            FROM 
				marpaab ma 
			LEFT JOIN 
				marpacliente mc USING(sigla) 
			LEFT JOIN 
				marpafincomis mf 
			ON 
				(mf.tipolan = 5 
			and 
				mf.numlan = ma.numero)
            LEFT JOIN 
				marpavendedor mv 
			ON
				(mv.codigovendedor = mc.codigovendedor) 
			LEFT JOIN 
				marpatipocomissao mtc USING(tipo_comissao)
			WHERE 
				ma.dtcomis is null 
			and 
				mc.codigovendedor ='87'
			and 
				mf.numlan is null";
  		$res = query2($sql);
  		foreach($res as $r) {
  			$total = number_format($r['valor'],2,',','.');
  			//$comissao = $total * $r['busca']/100;
			$comissao = 25.00; 
  			$total_valor = $total_valor + $r['valor'];
  			$total_honorarios = $total_honorarios + $r['valor'];
  			$total_comissao = $total_comissao + $comissao;
  			$comissao = number_format($comissao,2,',','.');
  			// $html.="<tr> <td>$r[cliente]</td> <td>AB - $r[numero]</td> <td>Autorização de Busca</td> <td>Não</td>";
  			// $html.="<td style=\"text-align: right\">$total</td> <td style=\"text-align: right\">0.00</td> ";
  			// $html.="<td style=\"text-align: right\">$total</td> <td style=\"text-align: right\">$comissao</td> ";
  			// $html.="<td style=\"text-align: right\">$r[pagamento]</td> <td style=\"text-align: right\">1</td> </tr>";
  		}
  		$total_valor = number_format($total_valor,2,',','.');
  		$total_taxa = number_format($total_taxa,2,',','.');
  		$total_honorarios = number_format($total_honorarios,2,',','.');
  		$total_comissao = number_format($total_comissao,2,',','.');
  		// $html.="<tr> <td colspan=\"4\"> <b>Total</b> </td> <td style=\"text-align: right\"> $total_valor</td> ";
  		// $html.="<td style=\"text-align: right\"> $total_taxa</td> <td style=\"text-align: right\"> $total_honorarios</td> ";
  		// $html.="<td style=\"text-align: right\"> <b>$total_comissao</b></td> <td colspan=\"2\"></td>";
		// $html.="<td><input type='submit' value='Baixar' /></td> </tr>";
  		// $html.="</table>";
 
        //$this->envia($email, $html,'Mapa de Comissões | '.date('d/m/Y',  strtotime($_POST['de'])).' - '.date('d/m/Y',  strtotime($_POST['ate'])).' ');		

      
		
  	}

//função	
	public function geraComissaoListaNovos() {
			//global $db,$formata,$TPLV;
		// if($_GET['ac']!='imprimir') $TPLV->newBlock('ComissoesForm');
		// if($_SESSION['s_codigovendedor']) {
		// 	$codigovendedor = $_SESSION['s_codigovendedor'];
		// } else {
		// $sql = 
		// 	"SELECT 
		// 		codigovendedor,vendedor, email 
		// 	FROM 
		// 		marpavendedor 
		// 	WHERE 
		// 		codstatus = 1 $where ORDER BY vendedor ASC";
		// 	$res = query2($sql);
			//$select = "<span class=\"verdana_9_FFFFFF\" style='margin-left: 10px'> <b>Consultor:</b> </span> <select name='codigovendedor' class='select'>";
			// foreach($res as $r) {
			// 	$select="<option value='$r[codigovendedor]'".($_POST['codigovendedor']==$r['codigovendedor']?'selected':'').">$r[vendedor]</option>";
			// }
			// $select.="</select>";
			// $TPLV->assignGlobal('select_consultores',$select);
			// $TPLV->assignGlobal('campo_pesquisa_consultores',$psq_con	);
			// $codigovendedor = ($_POST['codigovendedor']>0?$_POST['codigovendedor']:1);
		

		// $sql = 
		// 	"SELECT 
		// 		vendedor, email 
		// 	FROM 
		// 		marpavendedor 
		// 	WHERE 
		// 		codigovendedor = $codigovendedor";
		// $res = query2($sql);
		// $vendedor = $res[0]['vendedor'];
		// $email = $res[0]['email'];
		// $vSTem = 'N';
		// if(strlen($_POST['de'])==10 && strlen($_POST['ate'])==10) {
		// 	$de = $formata->formataData($_POST['de'],'sql');
		// 	$ate = $formata->formataData($_POST['ate'],'sql');
			
			$sql = 
				"SELECT distinct on 
					(parcela_id) sigla, empresa, num_ca, descrtipolan, vlparc, mp.seq, mp.numlan, mp.tipolan,
					me.nome, mtc.ca, mtc.contrato, mtc.aporte, mtc.honorarios_tramitacao, mtc.procedimento_com_taxa,
					vendedor, TO_CHAR(mp.dtpag, 'dd/mm/YYYY') as pagamento, CASE WHEN me=1 THEN taxa_me ELSE taxa END as taxac,
					mf.valor, descrtipomoedarz, tipomoeda, marpatipoprocedimento_id, valdeducoes, me, parcela_id, mp.dtpag,
				CASE WHEN
					(nrnota>0) 
				THEN 
					nrnota 
				ELSE 
					numrec 
				END as 
					recibo
				FROM 
					marpafinpc mp 
				LEFT JOIN 
					marpafin mf USING(tipolan, numlan) 
				LEFT JOIN 
					marpacliente mc USING(sigla) 
				LEFT JOIN 
					marpatipolan ml USING (tipolan)
				LEFT JOIN 
					marpatipoprocedimento me USING(marpatipoprocedimento_id) 
				LEFT JOIN 
					marpavendedor mv ON (mc.codigovendedor = mv.codigovendedor)
				LEFT JOIN 
					marpatipomoeda USING(tipomoeda) LEFT JOIN marpafincomis mfc ON(mfc.tipolan = mp.tipolan AND mfc.numlan = mp.numlan AND mfc.seq = mp.seq AND mfc.n = mp.n)
				LEFT JOIN 
					marpatipocomissao mtc USING(tipo_comissao)
				WHERE 
					mp.dtpag BETWEEN '2021-02-03' AND '2022-02-03' 
				AND 
					mc.codigovendedorinicial = '87'
				AND 
					mp.codigotipostatus IN(3,9) 
				AND 
					mfc.codigovendedor IS NULL AND mf.tipolan NOT IN(13, 14)
				AND 
					marpatipoprocedimento_id NOT IN (45, 106) 
				AND 
					mc.comissao = 1 
				AND 
					num_ca > 0
				AND 
					((pendencias = 0) or (pendencias is null)) 
				AND 
					mp.comissao = 1";
				//echo $sql . "<hr>";
				$res = query2($sql);
				//mp.dtpag BETWEEN '2021-02-03' AND '2022-02-03 -------------------PARA TESTE
				//mp.dtpag >= '".$de."' and mp.dtpag <= '".$ate."' -----------------REAL

			// $html.="<br /><br /><h2 style='text-align: center'>Mapa de comissões - ".$vendedor." Período de ".$_POST['de']." até ".$_POST['ate'].".</h2>";
			// $html.="<table width=\"95%\" style=\"font: 10px verdana,arial,helvetica; margin-left: 15px\">";
			// $html.="<tr> <th>Cliente</th> <th>CA / AB / CTR</th> <th>Trabalho</th> <th>Indicação</th> <th>Total</th> <th>Taxas</th> <th>Honorários</th> <th>Comissão</th>";
			// $html.="<th>Data</th> <th>Parcela</th> <th>Baixar</th> </tr>";
			$total_valor = 0;
			$total_taxa = 0;
			$total_honorarios = 0;
			$total_comissao = 0;
			//$html.="<form action='?on=consultores&in=comissoes&ac=baixar' method='POST'>";
			foreach($res as $r) {
				if($r['num_ca']>0) $numero = " - ".$r['num_ca'];
				else $numero = '';
				$total = number_format($r['vlparc'],2,',','.');
				//if ($r['seq']==1){			
					$honorarios = $r['vlparc']-$r['taxac'];
					if($r['taxac']<0) $r['taxac'] = 0;
					$taxa = number_format($r['taxac'],2,',','.');
					if($honorarios<0) $honorarios = 0;
					$comissao = $honorarios * 30/100;
					$total_valor = $total_valor + $r['vlparc'];
					$total_taxa = $total_taxa + $r['taxac'];
					$total_honorarios = $total_honorarios + $honorarios;
					$total_comissao = $total_comissao + $comissao;
					$honorarios = number_format($honorarios,2,',','.');
					$comissao = number_format($comissao,2,',','.');
					// $html.="<tr> <td>$r[empresa]</td> <td>$r[descrtipolan] $numero</td> <td>$r[nome]</td> <td>$indicacao</td>";
					// $html.="<td style=\"text-align: right\">$total</td> <td style=\"text-align: right\">$taxa</td> ";
					// $html.="<td style=\"text-align: right\">$honorarios</td> <td style=\"text-align: right\">$comissao</td> ";
					// $html.="<td style=\"text-align: right\">$r[pagamento]</td> <td style=\"text-align: right\">$r[recibo] - $r[seq]</td>";
					// $html.="<td style='text-align: center'><input type='checkbox' name='parcela_id[]' value='".$r['parcela_id']."|$comissao' value='Baixar' /></td> </tr>";
					$vSTem = 'S';
				//}	
			}
			$sql = 
				"SELECT 
					empresa as cliente, ma.numero, valor, TO_CHAR(ma.dtpag, 'dd/mm/YYYY') as pagamento, busca
				FROM 
					marpaab ma 
				LEFT JOIN 
					marpacliente mc USING(sigla) 
				LEFT JOIN 
					marpafincomis mf 
				ON 
					(mf.tipolan = 5 and mf.numlan = ma.numero)
				LEFT JOIN 
					marpavendedor mv 
				ON
					(mv.codigovendedor = mc.codigovendedor) 
				LEFT JOIN 
					marpatipocomissao mtc USING(tipo_comissao)
				WHERE 
					ma.dtpag BETWEEN '2021-02-03' AND '2022-02-03' 
				AND 
					mc.codigovendedorinicial = '87' and mf.numlan is null";
			
			$res = query2($sql);
			//mp.dtpag BETWEEN '2021-02-03' AND '2022-02-03'  ------------- TESTE
			//ma.dtpag >= '".$de."' and ma.dtpag <= '".$ate."' ------------ REAL
			foreach($res as $r) {
				$vSTem = 'S';
				$total = number_format($r['valor'],2,',','.');
				$comissao = 25.00; //($total * 25)/100;
				$total_valor = $total_valor + $r['valor'];
				$total_honorarios = $total_honorarios + $r['valor'];
				$total_comissao = $total_comissao + $comissao;
				$comissao = number_format($comissao,2,',','.');
				// $html.="<tr> <td>$r[cliente]</td> <td>AB - $r[numero]</td> <td>Autorização de Busca</td> <td>Não</td>";
				// $html.="<td style=\"text-align: right\">$total</td> <td style=\"text-align: right\">0.00</td> ";
				// $html.="<td style=\"text-align: right\">$total</td> <td style=\"text-align: right\">$comissao</td> ";
				// $html.="<td style=\"text-align: right\">$r[pagamento]</td> <td style=\"text-align: right\">1</td> </tr>";
				}
				$total_valor = number_format($total_valor,2,',','.');
				$total_taxa = number_format($total_taxa,2,',','.');
				$total_honorarios = number_format($total_honorarios,2,',','.');
				$total_comissao = number_format($total_comissao,2,',','.');
				// $html.="<tr> <td colspan=\"4\"> <b>Total</b> </td> <td style=\"text-align: right\"> $total_valor</td> ";
				// $html.="<td style=\"text-align: right\"> $total_taxa</td> <td style=\"text-align: right\"> $total_honorarios</td> ";
				// $html.="<td style=\"text-align: right\"> <b>$total_comissao</b></td> <td colspan=\"2\"></td>";
				// $html.="<td><input type='submit' value='Baixar' /></td> </tr>";
				// $html.="</table>";
				// $TPLV->newBlock('lista');
				// $TPLV->assignGlobal('lista',$html);
				// if(trim($_SESSION['s_login'])=='weba') $TPLV->newBlock('BtnComissoesForm');
				// $TPLV->assignGlobal('codigovendedor',$codigovendedor);
				// $TPLV->assignGlobal('de',$_POST['de']);
				// $TPLV->assignGlobal('ate',$_POST['ate']);
		}	
	
	
	public function valorComissaoMapa($vIPROIDLEGADO) {
		$sql = "SELECT
				  PROTAXA, PROTAXAME, PROREPRESENTANTEPR, PROREPRESENTANTERS, PROASSISTENTES, PROCRM
			  FROM
				  PROCEDIMENTOS
			  WHERE
				  PROIDLEGADO = $vIPROIDLEGADO";				
		$result = queryCONSULT($sql); 
		return $result;
	}
  
	
 }
