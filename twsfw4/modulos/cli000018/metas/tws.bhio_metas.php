<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class bhio_metas{
    var $funcoes_publicas = array(
        'index' 		        => true,
        'editarLinha'           => true,
        'salvarMetaLinha'       => true,
        'editarVendedor'        => true,
        'salvarMetaVendedor'    => true,
        'editarGrupo'           => true,
        'salvarMetaGrupo'       => true,
    );
    
    private $_linhas;
    private $_programa;
    private $_filtro;
    private $_docs;
    private $_metas;
    
    public function __construct(){
        $this->_programa = get_class($this);
        
        
        sys004::inclui(['programa' => $this->_programa, 'ordem' => '1', 'pergunta' => 'Código'		, 'variavel' => 'COD', 'tipo' => 'T', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        
        $this->_linhas = $this->montarListaLinhas();
        
        if(getAppVar('listaVendedoresBhio') === null){
            $lista_temporaria = array();
            
            $vendedores_ativos = $this->gerarListaVendedoresAtivos();
            
            $sql = "select * from bs_vendedores order by ativo";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $codigo = $row['codigo'];
                    $nome = in_array($codigo, $vendedores_ativos) ? '' : 'BLOQUEADO - ';
                    $lista_temporaria[] = array(
                        'id' => $row['codigo'],
                        'nome' => $nome . $row['nome'] . ' - ' . $row['codigo'],
                    );
                }
            }
            putAppVar('listaVendedoresBhio', $lista_temporaria);
        }
    }
    
    private function montarListaLinhas(){
        $ret = array();
        
        $sql = "select * from bs_linhas";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['codigo']] = $row['nome'];
            }
        }
        
        return $ret;
    }
    
    public function index(){
        $ret = '';
        $this->_filtro = new formfiltro01($this->_programa, array());
        $aba_ativa = $this->escolherAbaAtiva();
        $tabs = array(
            array('titulo' => 'Linhas', 'conteudo' => $this->montarAbaLinhas()),
            array('titulo' => 'Vendedores', 'conteudo' => $this->montarAbaVendedores()),
            array('titulo' => 'Grupos', 'conteudo' => $this->montarAbaGrupos())
        );
        $ret = formbase01::tabs(array('id' => 'formTabs', 'tabs' => $tabs, 'ativo' => $aba_ativa));
        $ret = addCard(array('titulo' => 'Metas', 'conteudo' => $ret));
        return $ret;
    }
    
    private function montarAbaGrupos(){
        $ret = '';
        
        $grupos = montarDicionarioSys005('BSGRLI');
        $temp = array();
        /*
         * //se for fazer o esquema de 3 botoes por linha
        while (count($grupos) > 0) {
            $temp_linha = array();
            while(count($temp_linha) < 3 && count($grupos) > 0){
                $codigo_temp = array_key_first($grupos);
                $param['texto'] 	= $grupos[$codigo_temp];
                $param['width'] 	= 30;
                $param['flag'] 		= '';
                $param['onclick'] 		= "setLocation('" . getLink() . "editarGrupo&id=$codigo_temp')";
                $param['cor'] 		= 'success';
                $param['bloco'] 		= true;
                $temp_linha[] = formbase01::formBotao($param);
                unset($grupos[$codigo_temp]);
            }
            while(count($temp_linha) < 3){
                $temp_linha[] = '';
            }
            
            $param = array();
            $param['tamanhos'] = array(4, 4, 4);
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
        }
        */
        while (count($grupos) > 0) {
            $temp_linha = array();
            $temp_linha[] = '';
            $codigo_temp = array_key_first($grupos);
            $param['texto'] 	= $grupos[$codigo_temp];
            $param['width'] 	= 30;
            $param['flag'] 		= '';
            $param['onclick'] 		= "setLocation('" . getLink() . "editarGrupo&id=$codigo_temp')";
            $param['cor'] 		= 'success';
            $param['bloco'] 		= true;
            $temp_linha[] = formbase01::formBotao($param);
            $temp_linha[] = '';
            unset($grupos[$codigo_temp]);
            $param = array();
            $param['tamanhos'] = array(3, 6, 3);
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
        }
        
        $ret = implode('<br>', $temp);
        return $ret;
    }
    
    private function escolherAbaAtiva(){
        $ret = 0;
        if(!$this->_filtro->getPrimeira()){
            $ret = 1;
        }
        return $ret;
    }
    
    private function montarAbaLinhas(){
        $ret = '';
        $temp = array();
        $linhas_local = array();
        /*
        while (count($this->_linhas) > 0) {
            $temp_linha = array();
            while(count($temp_linha) < 3 && count($this->_linhas) > 0){
                $codigo_temp = array_key_first($this->_linhas);
                $param['texto'] 	= $this->_linhas[$codigo_temp];
                $param['width'] 	= 30;
                $param['flag'] 		= '';
                $param['onclick'] 		= "setLocation('" . getLink() . "editarLinha&id=$codigo_temp')";
                $param['cor'] 		= 'success';
                $param['bloco'] 		= true;
                $temp_linha[] = formbase01::formBotao($param);
                unset($this->_linhas[$codigo_temp]);
            }
            while(count($temp_linha) < 3){
                $temp_linha[] = '';
            }
            
            $param = array();
            $param['tamanhos'] = array(4, 4, 4);
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
        }
        */
        $relacoes = $this->getListaRelacoes();
        $dicionario_grupo = montarDicionarioSys005('BSGRLI');
        $conteudos_primeira_linha = [];
        $tamanhos = array(3 ,3 ,3, 3);
        foreach ($this->_linhas as $codigo => $nome){
            $grupo = $relacoes[$codigo] ?? 'sem';
            $linhas_local[$grupo][$codigo] = $nome;
        }
        if(isset($linhas_local['sem'])){
            $tamanhos = array(2, 2, 2, 2, 2, 2);
            $dicionario_grupo[''] = '';
            $dicionario_grupo['sem'] = 'Sem Grupo';
        }
        foreach ($dicionario_grupo as $nome){
            $conteudos_primeira_linha[] = $nome;
        }
        //$conteudos_primeira_linha[] = '';
        //$conteudos_primeira_linha[] = 'Sem Grupo';
        $temp[] = addLinha(array('tamanhos' => $tamanhos, 'conteudos' => $conteudos_primeira_linha));
        
        
        
        $dicionario_grupo = array_keys($dicionario_grupo);
        while(count($linhas_local) > 0){
            $temp_linha = array();
            foreach ($dicionario_grupo as $grupo){
                if(!empty($grupo) && isset($linhas_local[$grupo])){
                    $codigo_temp = array_key_first($linhas_local[$grupo]);
                    $param['texto'] 	= $this->_linhas[$codigo_temp];
                    $param['width'] 	= 30;
                    $param['flag'] 		= '';
                    $param['onclick'] 		= "setLocation('" . getLink() . "editarLinha&id=$codigo_temp')";
                    $param['cor'] 		= 'success';
                    $param['bloco'] 		= true;
                    $temp_linha[] = formbase01::formBotao($param);
                    unset($linhas_local[$grupo][$codigo_temp]);
                    if(count($linhas_local[$grupo]) == 0){
                        unset($linhas_local[$grupo]);
                    }
                }
                else{
                    $temp_linha[] = '';
                }
            }
            
            $param = array();
            $param['tamanhos'] = $tamanhos;
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
        }

        
        $ret = implode('<br>', $temp);
        return $ret;
    }
    
    private function getListaRelacoes(){
        $ret = array();
        $sql = "select * from bs_linha_grupo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['linha']] = $row['grupo'];
            }
        }
        return $ret;
    }
    
    private function montarAbaVendedores(){
        $ret = '';
        if(!$this->_filtro->getPrimeira()){
            $dados_filtro = $this->_filtro->getFiltro();
            $dados = $this->getDadosAbaVendedores($dados_filtro['COD']);
        }
        else{
            $dados = array();
        }
        $ret .= $this->_filtro;
        $temp = array();
        $vendedores_ativos = $this->gerarListaVendedoresAtivos();
        while (count($dados) > 0) {
            $temp_linha = array();
            while(count($temp_linha) < 3 && count($dados) > 0){
                $codigo_temp = array_key_first($dados);
                $param['texto'] 	= $dados[$codigo_temp]['nome'];
                $param['width'] 	= 30;
                $param['flag'] 		= '';
                $param['onclick'] 		= "setLocation('" . getLink() . "editarVendedor&id={$dados[$codigo_temp]['id']}')";
                $param['cor'] 		= in_array($dados[$codigo_temp]['id'], $vendedores_ativos) ? 'success' : 'danger';
                $param['bloco'] 		= true;
                $temp_linha[] = formbase01::formBotao($param);
                unset($dados[$codigo_temp]);
            }
            while(count($temp_linha) < 3){
                $temp_linha[] = '';
            }
            
            $param = array();
            $param['tamanhos'] = array(4, 4, 4);
            $param['conteudos'] = $temp_linha;
            $temp[] =  addLinha($param);
        }
        if(is_array($temp) && count($temp) > 0){
            $ret .= '<br><br>';
            $ret .= implode('<br>', $temp);
        }
        
        return $ret;
    }
    
    private function getDadosAbaVendedores($codigo = ''){
        $ret = array();
        if(empty($codigo)){
            $ret = getAppVar('listaVendedoresBhio');
        }
        else{
            $lista_temporaria = getAppVar('listaVendedoresBhio');
            foreach ($lista_temporaria as $vendedor){
                if(strpos($vendedor['nome'], $codigo) !== false || strpos($vendedor['id'], $codigo) !== false){
                    $ret[] = $vendedor;
                }
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
    
    public function editarLinha(){
        $ret = '';
        addPortaljavaScript('
function atualizarTotal(anomes){
    var collection = document.getElementsByClassName("campo" + anomes);
    var total = 0;
    for (let i = 0; i < collection.length; i++) {
        total = total + parseFloat(collection[i].value.replace(/\./, \'\').replace(/,/, \'.\'));
    }
    const formatter = new Intl.NumberFormat(\'pt-br\', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    total = formatter.format(total);
    document.getElementById(\'total\' + anomes).innerHTML = total;
}
');
        
        $vendedores = $this->getDadosAbaVendedores();
        array_unshift($vendedores, array('id' => 'GERAL_SOMA', 'nome' => 'Geral Soma'));
        array_unshift($vendedores, array('id' => 'GERAL', 'nome' => 'Geral Linha'));
        $linha = trim($_GET['id']);
        
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'INI', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'FIM', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        
        
        $filtro = new formfiltro01('bhioMetasLinha', array());
        
        $anoMesAtual = date('Ym');
        
        $dataIni = '';
        $dataFim = '';
        
        if(!$filtro->getPrimeira()){
            $dados_filtro = $filtro->getFiltro();
            $dataIni = $dados_filtro['INI'] ?? '';
            $dataFim = $dados_filtro['FIM'] ?? '';
            
            if(empty($dataIni) && empty($dataFim)){
                $dataIni = date('Y') . '0101';
                $dataFim = date('Y') . '1231';
            }
            elseif(!empty($dataIni) && empty($dataFim)){
                $dataFim = date('Ymd', strtotime('+1 years', strtotime($dataIni)));
            }
            elseif(empty($dataIni) && !empty($dataFim)){
                $dataIni = date('Ymd', strtotime('-1 years', strtotime($dataFim)));
            }
        }
        else{
            $dataIni = date('Y') . '0101';
            $dataFim = date('Y') . '1231';

        }
        
        $meses_prontos = datas::getMeses($dataIni, $dataFim);
        foreach ($meses_prontos as $m){
            $anoMes[$m['anomes']] = $m['mes'] . ' ' . $m['ano'];
        }
        $metas_existentes = $this->getMetas($linha, '', $dataIni, $dataFim);
        
        $tabela = new tabela01(array('ordenacao' => false));
        $tabela->addColuna(array('campo' => 'nome'	, 'etiqueta' => 'Vendedor'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        foreach ($anoMes as $chave => $valor){
            $tabela->addColuna(array('campo' => $chave	, 'etiqueta' => $valor		, 'tipo' => 'T', 'width' =>  119, 'posicao' => 'E'));
        }
        
        $dados = array();
        foreach ($vendedores as $v){
            $temp = array(
                'nome' => $v['nome'],
            );
            foreach ($anoMes as $chave => $valor){
                $valor = $metas_existentes[$linha][$v['id']][$chave] ?? 0;
                //if($chave < $anoMesAtual && false){
                if($v['id'] == 'GERAL_SOMA'){
                    $temp[$chave] = "<div id=\"total$chave\">" . formataReais($valor) . '</div>';
                }
                else{
                    $campo = array('nome' => 'camposMetas[' . $chave . $v['id'] . ']', 'mascara' => 'V', 'valor' => $valor);
                    if($v['id'] != 'GERAL'){
                        $campo['onchange'] = "atualizarTotal('$chave')";
                        $campo['classeadd'] = "campo$chave";
                    }
                    $temp[$chave] = formbase01::formTexto($campo);
                }
            }
            
            $dados[] = $temp;
        }
        
        $tabela->setDados($dados);
        
        
        $datas_hidden = formbase01::formHidden(array('nome' => 'datas[ini]', 'valor' => $dataIni)) . formbase01::formHidden(array('nome' => 'datas[fim]', 'valor' => $dataFim));
        
        $ret .= $filtro;
        $ret .= '<form id="formMetas" method="post" action="' . getLink() . 'salvarMetaLinha&id=' . $linha . '">' . $datas_hidden . $tabela . '</form>';
        
        $bt_cancelar = array('texto' => 'cancelar', 'cor' => 'danger', 'onclick' => "setLocation('" . getLink() . "index')");
        $bt = array('cor' => 'success','texto' => 'Salvar Metas', 'onclick' => 'document.getElementById(\'formMetas\').submit();');
        $ret = addCard(array('titulo' => 'Metas ' . $this->_linhas[$_GET['id']], 'conteudo' => $ret, 'botoesTitulo' => array($bt, $bt_cancelar)));
        return $ret;
    }
    
    public function salvarMetaLinha(){
        $dados = $_POST['camposMetas'];
        $datas = $_POST['datas'];
        $linha = $_GET['id'];
        if(is_array($dados) && count($dados) > 0){
            $this->_metas = $this->getMetas($linha, '', $datas['ini'], $datas['fim']);
            foreach ($dados as $chave => $valor){
                $ano = substr($chave, 0, 4);
                $mes = substr($chave, 4, 2);
                $vendedor = substr($chave, 6);
                
                $this->integrarMeta($linha, $vendedor, $mes, $ano, $valor);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    private function integrarMeta($linha, $vendedor, $mes, $ano, $valor, $teste = false){
        $valor_formatado = str_replace(array('.', ','), array('', '.'), $valor);
        if(!isset($this->_metas[$linha][$vendedor][$ano . $mes]) && !empty(str_replace(array('0', ',', '.'), '', $valor))){
            $sql = "insert into bs_metas values (null, '$linha', '$vendedor', '" . $ano . $mes . "', $valor_formatado)";
            query($sql);
        }
        elseif(isset($this->_metas[$linha][$vendedor][$ano . $mes]) && strval($this->_metas[$linha][$vendedor][$ano . $mes]) !== $valor_formatado){
			$sql = "update bs_metas set valor = $valor_formatado where linha = '$linha' and vendedor = '$vendedor' and ano_mes = '" . $ano . $mes . "'";
			query($sql);
        }
        //$this->_metas[$linha][$vendedor][$ano . $mes] = $valor_formatado;
    }
    
    private function getMetas($linha_filtro = '', $vendedor_filtro = '',  $dataIni = '', $dataFim = ''){
        $ret = array();
        $sql = "select * from bs_metas";
        $sql_sufixo = '';
        $where = [];
        if(!empty($dataIni)){
            $where[] = "ano_mes >= " . substr($dataIni, 0, 6);
        }
        if(!empty($dataFim)){
            $where[] = "ano_mes <= " . substr($dataFim, 0, 6);
        }
        if(!empty($vendedor_filtro)){
            $where[] = "vendedor = '$vendedor_filtro'";
        }
        if(!empty($linha_filtro)){
            $where[] = "linha = '$linha_filtro'";
            $sql_sufixo = " union select null as id, '$linha_filtro' as linha, 'GERAL_SOMA' as vendedor, ano_mes, sum(valor) as valor from bs_metas where " . implode(' and ', $where) . " and vendedor != 'GERAL' group by ano_mes";
        }
        
        if(count($where) > 0){
            $sql .= " where " . implode(' and ', $where);
        }
        if(!empty($sql_sufixo)){
            $sql .= " $sql_sufixo";
        }
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['linha']][$row['vendedor']][$row['ano_mes']] = $row['valor'];
            }
        }
        return $ret;
    }
    
    public function editarVendedor(){
        $ret = '';
        $vendedor = $_GET['id'];
        
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'INI', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'FIM', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        
        
        $filtro = new formfiltro01('bhioMetasLinha', array());
        
        $anoMesAtual = date('Ym');
        
        $dataIni = '';
        $dataFim = '';
        
        if(!$filtro->getPrimeira()){
            $dados_filtro = $filtro->getFiltro();
            $dataIni = $dados_filtro['INI'] ?? '';
            $dataFim = $dados_filtro['FIM'] ?? '';
            
            if(empty($dataIni) && empty($dataFim)){
                $dataIni = date('Y') . '0101';
                $dataFim = date('Y') . '1231';
            }
            elseif(!empty($dataIni) && empty($dataFim)){
                $dataFim = date('Ymd', strtotime('+1 years', strtotime($dataIni)));
            }
            elseif(empty($dataIni) && !empty($dataFim)){
                $dataIni = date('Ymd', strtotime('-1 years', strtotime($dataFim)));
            }
        }
        else{
            $dataIni = date('Y') . '0101';
            $dataFim = date('Y') . '1231';
            
        }
        
        $meses_prontos = datas::getMeses($dataIni, $dataFim);
        foreach ($meses_prontos as $m){
            $anoMes[$m['anomes']] = $m['mes'] . ' ' . $m['ano'];
        }
        $metas_existentes = $this->getMetas('', $vendedor, $dataIni, $dataFim);
        
        $tabela = new tabela01(array('ordenacao' => false));
        $tabela->addColuna(array('campo' => 'nome'	, 'etiqueta' => 'Linha'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        foreach ($anoMes as $chave => $valor){
            $tabela->addColuna(array('campo' => $chave	, 'etiqueta' => $valor		, 'tipo' => 'T', 'width' =>  117, 'posicao' => 'E'));
        }
        
        $dados = array();
        
        //$this->_linhas = array_merge(array('GERAL' => 'Geral'), $this->_linhas);
        $this->_linhas = $this->redefinirLinhas();
        foreach ($this->_linhas as $cod_linha => $linha){
            $temp = array(
                'nome' => $linha,
            );
            foreach ($anoMes as $chave => $valor){
                $valor = $metas_existentes[$cod_linha][$vendedor][$chave] ?? 0;
                if(($chave < $anoMesAtual && false) || $cod_linha == 'GOUTROS'){
                    $temp[$chave] = '';
                }
                else{
                    $temp[$chave] = formbase01::formTexto(array('nome' => 'camposMetas[' . $chave . $cod_linha . ']', 'mascara' => 'V', 'valor' => $valor));
                }
            }
            
            $dados[] = $temp;
        }
        
        $tabela->setDados($dados);
        
        
        $datas_hidden = formbase01::formHidden(array('nome' => 'datas[ini]', 'valor' => $dataIni)) . formbase01::formHidden(array('nome' => 'datas[fim]', 'valor' => $dataFim));
        
        $ret .= $filtro;
        $ret .= '<form id="formMetas" method="post" action="' . getLink() . 'salvarMetaVendedor&id=' . $vendedor . '">' . $datas_hidden . $tabela . '</form>';
        
        $bt_cancelar = array('texto' => 'cancelar', 'cor' => 'danger', 'onclick' => "setLocation('" . getLink() . "index')");
        $bt = array('cor' => 'success', 'texto' => 'Salvar Metas', 'onclick' => 'document.getElementById(\'formMetas\').submit();');
        $ret = addCard(array('titulo' => 'Metas ' . $this->getNomeVendedor($vendedor), 'conteudo' => $ret, 'botoesTitulo' => array($bt, $bt_cancelar)));
        return $ret;
    }
    
    private function redefinirLinhas(){
        //ao editar o vendedor precisa aparece os grupos das linhas, criei essa função para não ter q editar o resto do programa
        $ret = array();
        $sql = "select bs_linhas.codigo as linha, bs_linhas.nome as linha_nome, COALESCE(bs_linha_grupo.grupo, 'GOUTROS') as grupo from bs_linhas left join bs_linha_grupo on bs_linhas.codigo = bs_linha_grupo.linha";
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
    
    private function getNomeVendedor($codigo){
        $ret = '';
        $sql = "select nome from bs_vendedores where codigo = '$codigo'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['nome'];
        }
        return $ret;
    }
    
    public function salvarMetaVendedor(){
        $dados = $_POST['camposMetas'];
        $datas = $_POST['datas'];
        $vendedor = $_GET['id'];
        if(is_array($dados) && count($dados) > 0){
            $this->_metas = $this->getMetas('', $vendedor, $datas['ini'], $datas['fim']);
            foreach ($dados as $chave => $valor){
                $ano = substr($chave, 0, 4);
                $mes = substr($chave, 4, 2);
                $linha = substr($chave, 6);
                $this->integrarMeta($linha, $vendedor, $mes, $ano, $valor);
            }
        }
        redireciona(getLink() . 'index');
    }
    
    public function editarGrupo(){
        $ret = '';
        $grupo = $_GET['id'];
        
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '1', 'pergunta' => 'De'		, 'variavel' => 'INI', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        sys004::inclui(['programa' => 'bhioMetasLinha', 'ordem' => '2', 'pergunta' => 'Até'		, 'variavel' => 'FIM', 'tipo' => 'D', 'tamanho' => '50', 'casadec' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => ''	, 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => '']);
        
        
        $filtro = new formfiltro01('bhioMetasLinha', array());
        
        $anoMesAtual = date('Ym');
        
        $dataIni = '';
        $dataFim = '';
        
        if(!$filtro->getPrimeira()){
            $dados_filtro = $filtro->getFiltro();
            $dataIni = $dados_filtro['INI'] ?? '';
            $dataFim = $dados_filtro['FIM'] ?? '';
            
            if(empty($dataIni) && empty($dataFim)){
                $dataIni = date('Y') . '0101';
                $dataFim = date('Y') . '1231';
            }
            elseif(!empty($dataIni) && empty($dataFim)){
                $dataFim = date('Ymd', strtotime('+1 years', strtotime($dataIni)));
            }
            elseif(empty($dataIni) && !empty($dataFim)){
                $dataIni = date('Ymd', strtotime('-1 years', strtotime($dataFim)));
            }
        }
        else{
            $dataIni = date('Y') . '0101';
            $dataFim = date('Y') . '1231';
            
        }
        
        $meses_prontos = datas::getMeses($dataIni, $dataFim);
        foreach ($meses_prontos as $m){
            $anoMes[$m['anomes']] = $m['mes'] . ' ' . $m['ano'];
        }
        $metas_existentes = $this->getMetasGrupo($grupo);
        
        $tabela = new tabela01(array('ordenacao' => false));
        $tabela->addColuna(array('campo' => 'nome'	, 'etiqueta' => 'Linha'		, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        foreach ($anoMes as $chave => $valor){
            $tabela->addColuna(array('campo' => $chave	, 'etiqueta' => $valor		, 'tipo' => 'T', 'width' =>  117, 'posicao' => 'E'));
        }
        
        $dados = array();
        
        $this->_linhas = array_merge(array('GERAL' => 'Geral'), $this->_linhas);
        
        $linhas_alt = ['GERAL' => 'Geral'];
        
        foreach ($linhas_alt as $cod_linha => $linha){
            $temp = array(
                'nome' => $linha,
            );
            foreach ($anoMes as $chave => $valor){
                $valor = $metas_existentes[$grupo][$cod_linha][$chave] ?? 0;
                if($chave < $anoMesAtual && false){
                    $temp[$chave] = formataReais($valor);
                }
                else{
                    $temp[$chave] = formbase01::formTexto(array('nome' => 'camposMetas[' . $chave . $cod_linha . ']', 'mascara' => 'V', 'valor' => $valor));
                }
            }
            
            $dados[] = $temp;
        }
        
        $tabela->setDados($dados);
        
        
        $datas_hidden = formbase01::formHidden(array('nome' => 'datas[ini]', 'valor' => $dataIni)) . formbase01::formHidden(array('nome' => 'datas[fim]', 'valor' => $dataFim));
        
        $ret .= $filtro;
        $ret .= '<form id="formMetas" method="post" action="' . getLink() . 'salvarMetaGrupo&id=' . $grupo . '">' . $datas_hidden . $tabela . '</form>';
        
        $bt_cancelar = array('texto' => 'cancelar', 'cor' => 'danger', 'onclick' => "setLocation('" . getLink() . "index')");
        $bt = array('cor' => 'success','texto' => 'Salvar Metas', 'onclick' => 'document.getElementById(\'formMetas\').submit();');
        $ret = addCard(array('titulo' => 'Metas ' . $this->getNomeGrupo($grupo), 'conteudo' => $ret, 'botoesTitulo' => array($bt, $bt_cancelar)));
        return $ret;
    }
    
    private function getNomeGrupo($grupo){
        $ret = getTabelaDesc('BSGRLI', $grupo);
        return $ret;
    }
        
    private function getMetasGrupo($grupo){
        $ret = array();
        $sql = "select * from bs_metas_grupos where grupo = '$grupo'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$grupo][$row['linha']][$row['ano_mes']] = $row['valor'];
            }
        }
        return $ret;
    }
    
    public function salvarMetaGrupo(){
        $grupo = $_GET['id'];
        $dados = $_POST['camposMetas'];
        $datas = $_POST['datas'];
        if(is_array($dados) && count($dados) > 0){
            $metas_existentes = $this->getMetasGrupo($grupo);
            foreach ($dados as $chave => $valor){
                $ano = substr($chave, 0, 4);
                $mes = substr($chave, 4, 2);
                $linha = substr($chave, 6);
                $valor_formatado = str_replace(array('.', ','), array('', '.'), $valor);
                if(!isset($metas_existentes[$grupo][$linha][$ano . $mes]) && !empty(str_replace(array('0', ',', '.'), '', $valor))){
                    $sql = "insert into bs_metas_grupos values ('$grupo', '$linha', '" . $ano . $mes . "', $valor_formatado)";
                    query($sql);
                }
                elseif(isset($metas_existentes[$grupo][$linha][$ano . $mes]) && strval($metas_existentes[$grupo][$linha][$ano . $mes]) !== $valor_formatado){
                    $sql = "update bs_metas_grupos set valor = $valor_formatado where grupo = '$grupo' and linha = '$linha' and ano_mes = '" . $ano . $mes . "'";
                    query($sql);
                }
            }
        }
        redireciona(getLink() . 'index');
    }
}