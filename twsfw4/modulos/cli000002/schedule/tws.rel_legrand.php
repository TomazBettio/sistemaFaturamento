<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class rel_legrand{
    var $funcoes_publicas = array(
        'schedule' 		=> true,
    );
    
    public function schedule($param){
        $explode = explode('|', $param);
        $fornecedor = $explode[0];
        $emails = $explode[1];
        if(!empty($fornecedor)){
            $dados = $this->getDados($fornecedor);
            if(is_array($dados) && count($dados) > 0){
                $relatorio = $this->montarRelatorio();
                $relatorio->setDados($dados);
                $nome_arquivo = $this->montarNomeArquivo($fornecedor);
                $relatorio->setToExcel(true, $nome_arquivo);
                $envia = $relatorio->enviaEmail($emails, 'Relatório Legrand ' . $fornecedor);
                if($envia){
                	echo "Email enviado com sucesso!<br>\n";
                }else{
                	echo "Falha ao enviar o email!<br>\n";
                }
            }else{
            	echo "Não existem dados";
            }
        }
    }
    
    private function montarNomeArquivo($fornecedor){
        $data_ini =  '01' . date('-m-Y');
        $data_fim = date('d-m-Y');
        $ret = "relatorio_legrand_$fornecedor" . '_de_' . $data_ini . '_a_' .$data_fim;
        return $ret;
    }
    
    private function montarRelatorio(){
        $ret = new relatorio01();
        $ret->addColuna(array('campo' => 'numnota'          , 'etiqueta' => 'NUMNOTA'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'dtsaida'          , 'etiqueta' => 'DTSAIDA'	,'tipo' => 'D', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'codcli'           , 'etiqueta' => 'CODCLI'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'codcliprinc'	    , 'etiqueta' => 'CODCLIPRINC'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'cliente'          , 'etiqueta' => 'CLIENTE'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'cgcent'           , 'etiqueta' => 'CGCENT'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'codsupervisor'	, 'etiqueta' => 'CODSUPERVISOR'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'codusur'          , 'etiqueta' => 'CODUSUR'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'codprod'          , 'etiqueta' => 'CODPROD'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'descricao'        , 'etiqueta' => 'PRODDESC'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'ean'              , 'etiqueta' => 'EAN'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'codmarca'         , 'etiqueta' => 'CODMARCA'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'marca'            , 'etiqueta' => 'MARCA'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'qt'               , 'etiqueta' => 'QTVENDIDA'	,'tipo' => 'V', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'preco_fabrica'	, 'etiqueta' => 'PREÇO FÁBRICA'	,'tipo' => 'V', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'vlvenda'	        , 'etiqueta' => 'VLVENDA'	,'tipo' => 'V', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'vlvenda_semst'	, 'etiqueta' => 'VLVENDA_SEMST'	,'tipo' => 'V', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'vlr_st'	        , 'etiqueta' => 'VLR_ST'	,'tipo' => 'V', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'origem_ped'	    , 'etiqueta' => 'ORIGEM_PED'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'codfornec'	    , 'etiqueta' => 'CODFORNEC'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'fornecedor'	    , 'etiqueta' => 'FORNECEDOR'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        $ret->addColuna(array('campo' => 'estcob'	        , 'etiqueta' => 'ESTCOB'	,'tipo' => 'T', 'width' => 250,	'posicao' => 'E'));
        return $ret;
    }
    
    private function getDados($fornecedor){
        $ret = [];
        $data_ini = date('Ym') . '01';
        $data_fim = date('Ymd');
            $sql = "
SELECT pcmov.numnota
	,dtsaida
	,pcnfsaid.codcli
	,codcliprinc
	,pcclient.cliente
	,cgcent
	,pcnfsaid.codsupervisor
	,pcnfsaid.codusur
	,codprod
	,pcmov.descricao
	,pcmov.codauxiliar AS EAN
	,codmarca
	,pcmarca.marca
	,qt
	,pcprodut.custorep AS preco_fabrica
	,pcmov.punit * qt AS vlvenda
	,(pcmov.punit - st) * qt AS vlvenda_semst
	,st * qt AS vlr_st
	,CASE
		WHEN pcpedc.origemped = 'F'
			AND tipofv IS NULL
			THEN 'ERC'
		WHEN pcpedc.origemped = 'F'
			AND tipofv = 'PE'
			THEN 'Pedido Eletronico'
		WHEN pcpedc.origemped = 'F'
			AND tipofv = 'OL'
			THEN 'OL'
		WHEN pcpedc.origemped = 'T'
			AND tipofv IS NULL
			THEN 'TELEVENDAS'
		ELSE 'ECOMERCE'
		END AS origem_ped
	,codfornec
	,fornecedor
	,pcnfsaid.uf as estcob
FROM pcmov
JOIN pcnfsaid ON (pcmov.numtransvenda = pcnfsaid.numtransvenda)
JOIN pcclient ON (pcnfsaid.codcli = pcclient.codcli)
JOIN pcprodut USING (codprod)
JOIN pcmarca using (codmarca)
JOIN pcpedc ON (pcnfsaid.numped = pcpedc.numped)
JOIN pcfornec using (codfornec)
WHERE codoper LIKE 'S%'
	AND DTSAIDA BETWEEN TO_DATE('$data_ini', 'YYYYMMDD')
		AND TO_DATE('$data_fim', 'YYYYMMDD')
	" . $this->montarWhereStatus($fornecedor) . "
	AND codfornec IN ($fornecedor)
";
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['numnota', 'codcli', 'codcliprinc', 'cliente', 'cgcent', 'codsupervisor', 'codusur', 'codprod', 'descricao', 'ean', 'codmarca', 'marca', 'qt', 'preco_fabrica', 'vlvenda', 'vlvenda_semst', 'vlr_st', 'origem_ped', 'codfornec', 'fornecedor', 'estcob'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[strtoupper($c)];
                }
                $temp['dtsaida'] = datas::dataMS2S($row['DTSAIDA']);
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function montarWhereStatus($codigo){
        $ret = ' AND ';
        if($codigo == '19891'){
            $ret .= "pcnfsaid.uf in ('RS', 'SC')";
        }
        else{
            $ret .= "pcnfsaid.uf = 'RS'";
        }
        return $ret;
    }
}