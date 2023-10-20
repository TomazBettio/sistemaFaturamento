<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class exportar_metas{
    var $funcoes_publicas = array(
        'index' 		        => true,
        'ajax'                  => true,
    );
    
    public function index(){
        $ret = '';
        /*
        $ret = $this->gerarExcel('2023');
        return $ret;
        die();
        */
        addPortaljavaScript("
function exportarMetas(status){
    var ano = document.getElementById('idSelectAno').value;
    var link = '" . getLinkAjax('exportar') . "' + '&ano=' + ano + '&status=' + status;
    //op2(link);
    window.open(link, 'Download');
}
");
        
        $select = formbase01::formSelect(array('lista' => $this->getAnosSelect(), 'nome' => 'formteste[ano]', 'id' => 'idSelectAno'));
        
        $param = array(
            'texto' => 'Exportar somente vendedores ATIVOS',
            'onclick' => "exportarMetas('ATIVOS')",
        );
        $bt = formbase01::formBotao($param);
        
        $ret = $select . $bt;
        
        $param = array(
            'texto' => 'Exportar somente vendedodes BLOQUEADOS',
            'onclick' => "exportarMetas('BLOQUEADOS')",
        );
        $bt = formbase01::formBotao($param);
        
        $ret .=  ' ' . $bt;
        
        $param = array(
            'texto' => 'Exportar TODOS os vendedores',
            'onclick' => "exportarMetas('TODOS')",
        );
        $bt = formbase01::formBotao($param);
        
        $ret .= ' ' . $bt;
        /*
        $link = getLinkAjax('multi') . "&notas=" . implode(',', $notas);
        addPortaljavaScript("op2('" . $link . "')", 'F');
        */
        $ret = addCard(array('titulo' => 'Exportar Metas', 'conteudo' => $ret));
        
        return $ret;
    }
    
    private function getAnosSelect(){
        $ret = array();
        //$ret[] = ['2023', '2023'];
        $ano_atual = date('Y');
        $sql = "select * from (select distinct substring(ano_mes, 1, 4) as ano from bs_metas where ano_mes not like '{$ano_atual}__') as temp1 order by ano";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = [$row['ano'], $row['ano']];
            }
        }
        else{
            $ret[] = [$ano_atual, $ano_atual];
        }
        
        return $ret;
    }
    
    private function gerarExcel($ano, $status){
        $relatorio = new relatorio01();
        $meses = datas::getMeses($ano . '0101', $ano . '1231');
        $metas = $this->getMetas($ano);
        $vendedores = $this->getVendedores($status);
        $linhas = $this->getLinhas();
        $i = 0;
        $dados_secao = array();
        foreach ($vendedores as $cod_vendedor => $nome_vendedor){
            $this->addColunasRelatorio($relatorio, $i, $meses);
            $dados_secao[$cod_vendedor] = $this->montarDadosSecao($metas, $cod_vendedor, $linhas);
            $relatorio->setDados($dados_secao[$cod_vendedor], $i);
            $relatorio->setTituloSecaoPlanilha($i, $nome_vendedor);
            $i++;
        }
        $relatorio->setToExcel(true, getUsuario() . '_metas');
        $relatorio . '';
    }
    
    private function montarDadosSecao(&$dados, $cod_vendedor, &$linhas){
        $ret = array();
        foreach($linhas as $cod_linha => $nome_linha){
            $temp = array('nome_linha' => $nome_linha, 'codigo' => $cod_vendedor . '_' . $cod_linha);
            /*
            $chaves = array_keys($dados[$cod_vendedor][$cod_linha]);
            foreach ($chaves as $c){
                $temp[$c] = $dados[$cod_vendedor][$cod_linha][$c];
            }
            */
            $temp = array_merge($temp, $dados[$cod_vendedor][$cod_linha]);
            //var_dump($valores);
            //var_dump($temp);
            $ret[] = $temp;
        }
        return $ret;
    }
        
    private function getLinhas(){
        $ret = array();
        $sql = "
select 
    bs_linhas.codigo as linha, 
    bs_linhas.nome as linha_nome, 
    COALESCE(bs_linha_grupo.grupo, 'GOUTROS') as grupo 
from 
    bs_linhas 
    left join bs_linha_grupo 
        on bs_linhas.codigo = bs_linha_grupo.linha";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dicionario_grupos = montarDicionarioSys005('BSGRLI');
            $dicionario_grupos['GOUTROS'] = 'Outros';
            $dados_temp = array();
            foreach ($rows as $row){
                $dados_temp[$row['grupo']][$row['linha']] = $row['linha_nome'];
            }
            $ret['GERAL'] = 'Geral';
            foreach ($dicionario_grupos as $cod_grupo => $nome_grupo){
                $linhas_atuais = $dados_temp[$cod_grupo];
                $ret[$cod_grupo] = '<b>' . $nome_grupo . '</b>';
                foreach ($linhas_atuais as $cod_linha => $nome_linha){
                    $ret[$cod_linha] = $nome_linha;
                }
            }
        }
        return $ret;
    }
    
    private function getVendedores($status){
        $ret = [];
        
        //$sql = "select codigo, nome from bs_vendedores where codigo not like 'SUP%' order by ativo";
        $sql = "select codigo, nome from bs_vendedores where ativo != '*'";
        if($status == 'TODOS'){
            $sql = "select codigo, nome from bs_vendedores order by ativo";
        }
        elseif ($status == 'BLOQUEADOS'){
            $sql = "select codigo, nome from bs_vendedores where ativo = '*'";
        }
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $vendedores_ativos = $this->gerarListaVendedoresAtivos();
            foreach ($rows as $row){
                $nome = '';
                if(!in_array($row['codigo'], $vendedores_ativos)){
                    $nome .= 'BLQ-';
                }
                $nome .= str_replace(array('*', ':', '/', '\\', '?', '[', ']'), '', $row['codigo'] . '-' . $row['nome']);
                if(strlen($nome) > 31){
                    $nome = substr($nome, 0, 31);
                }
                $ret[$row['codigo']] = $nome;
            }
        }
        return $ret;
    }
    
    private function gerarListaVendedoresAtivos(){
        $ret = [];
        $sql = "select * from bs_vendedores where ativo != '*'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['codigo'];
            }
        }
        return $ret;
    }
    
    private function getMetas($ano){
        //$sql = "select bs_metas.linha as cod_linha, bs_metas.vendedor as cod_vendedor, bs_metas.ano_mes as ano_mes, bs_metas.valor as valor, bs_vendedores.nome as nome_vendedor, COALESCE(bs_linhas.nome, dicionario_grupos.descricao) as nome_linha from bs_metas left join bs_vendedores on (bs_metas.vendedor = bs_vendedores.codigo) left join bs_linhas on (bs_metas.linha = bs_linhas.codigo) left join (select chave, descricao from sys005 where tabela = 'BSGRLI' union select 'GOUTROS' as chave, 'Outros' as descricao) as dicionario_grupos on (bs_metas.linha = dicionario_grupos.chave)";
        $ret = array();
        $sql = "
SELECT vendedor_linha.*
	,ano_mes.ano_mes AS ano_mes
	,COALESCE(bs_metas.valor, 0) AS valor
FROM (
	SELECT bs_vendedores.codigo AS cod_vendedor
		,linhas.codigo AS cod_linha
	FROM bs_vendedores
	CROSS JOIN (
		SELECT bs_linhas.codigo
		FROM bs_linhas
		
		UNION
		
		SELECT chave
		FROM sys005
		WHERE tabela = 'BSGRLI'
		
		UNION
		
		SELECT 'GOUTROS' AS chave
		
		UNION
		
		SELECT 'GERAL' AS chave
		) AS linhas
	) AS vendedor_linha
CROSS JOIN (
	SELECT '{$ano}01' AS ano_mes
	
	UNION
	
	SELECT '{$ano}02'
	
	UNION
	
	SELECT '{$ano}03'
	
	UNION
	
	SELECT '{$ano}04'
	
	UNION
	
	SELECT '{$ano}05'
	
	UNION
	
	SELECT '{$ano}06'
	
	UNION
	
	SELECT '{$ano}07'
	
	UNION
	
	SELECT '{$ano}08'
	
	UNION
	
	SELECT '{$ano}09'
	
	UNION
	
	SELECT '{$ano}10'
	
	UNION
	
	SELECT '{$ano}11'
	
	UNION
	
	SELECT '{$ano}12'
	) AS ano_mes
LEFT JOIN bs_metas ON (
		vendedor_linha.cod_vendedor = bs_metas.vendedor
		AND vendedor_linha.cod_linha = bs_metas.linha
		AND bs_metas.ano_mes LIKE '{$ano}__'
		AND bs_metas.ano_mes = ano_mes.ano_mes
		)
";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['cod_vendedor']][$row['cod_linha']]['D' . $row['ano_mes']] = floatval($row['valor']);
            }
        }
        return $ret;
    }
    
    private function addColunasRelatorio(&$relatorio, $index, &$meses){
        $relatorio->addColuna(array('campo' => 'nome_linha'	, 'etiqueta' => 'Linha'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'), $index);
        foreach ($meses as $mes){
            $relatorio->addColuna(array('campo' => 'D' . $mes['anomes']	, 'etiqueta' => $mes['diafim'] . '/' . $mes['mesano']  , 'tipo' => 'V', 'width' =>  80, 'posicao' => 'D'), $index);
        }
        $relatorio->addColuna(array('campo' => 'codigo'	, 'etiqueta' => 'Código'  , 'tipo' => 'HD', 'width' =>  80, 'posicao' => 'E'), $index);
    }
    
    
    
    public function ajax(){
        /*
        $ano = $_GET['ano'];
        echo $ano;
        */
        // Define file name and path
        set_time_limit(0);
        $fileName = getUsuario() . '_metas.xlsx';
        global $config;
        $filePath = $config['tempPach'] . $fileName;
        $ano = $_GET['ano'];
        $status = $_GET['status'];
        
        $this->gerarExcel($ano, $status);
        
        if(!empty($fileName) && file_exists($filePath)){
            // Define headers
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=metas_$ano.xlsx");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            
            // Read the file
            readfile($filePath);
            exit;
        }else{
            echo 'The file does not exist.';
        }
        die();
    }
}