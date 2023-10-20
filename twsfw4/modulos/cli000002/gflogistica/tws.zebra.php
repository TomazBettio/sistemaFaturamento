<?php
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class zebra{
    var $funcoes_publicas = array(
        'index'             => true,
        'ajax'              => true,
        'imprimir'          => true,
    );
    
    private $_programa;
    
    public function __construct(){
        $this->_programa = get_class($this);
        
        sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '1', 'pergunta' => 'Data Ini'		, 'variavel' => 'dtIni'  , 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '2', 'pergunta' => 'Data Fim'		, 'variavel' => 'dtFim'  , 'tipo' => 'D', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => ''));
        sys004::inclui(array('programa' => $this->_programa, 'emp' => '', 'fil' => '', 'ordem' => '3', 'pergunta' => 'Status'		, 'variavel' => 'status' , 'tipo' => 'A', 'tamanho' => '8', 'decimal' => 0, 'validador' => '', 'tabela' => '', 'funcaodados' => '', 'tipo_2' => '', 'help' => '', 'inicializador' => '', 'inicfunc' => '', 'opcoes' => 'I=Impresso;N=Não Impresso;A=Ambos'));
        
        //pcnfsaid.dtsaida é a data
        //pcnfsaid.numnota numero da nota
        //pcnfsaid.vltotal é o valor
        //pcnfsaid.numped pra juntar com os pedidos de e-commerce
        //pcnfsaid.chavenfe
        //pcnfsaid.protocolonfe no lugar da data do protoco de autorização
        //endereco-> endereco bairro cidade
    }
    
    public function imprimir(){
        $dados = $_POST['formNotas'] ?? array();
        $imprimir = intval(getAppVar('num_exec_zebra')) === intval($_GET['num']);
        if(count($dados) > 0 && $imprimir){
            $notas = array_keys($dados);
            $link = getLinkAjax('multi') . "&notas=" . implode(',', $notas);
            addPortaljavaScript("op2('" . $link . "')", 'F');
        }
        return $this->index(true);
    }
    
    public function index($forcar = false){
        $ret = '';
        $vl_exec = getAppVar('num_exec_zebra');
        if($vl_exec === null){
            $vl_exec = 1;
        }
        else{
            $vl_exec++;
        }
        putAppVar('num_exec_zebra', $vl_exec);
        $tabela = new relatorio01(array('programa' => $this->_programa, 'link' => getLink() . 'index'));
        $tabela->addColuna(array('campo' => 'bt'	, 'etiqueta' => 'Imprimir'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'pedido'	, 'etiqueta' => 'Pedido'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        
        $tabela->addColuna(array('campo' => 'nota'	, 'etiqueta' => 'Nota'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'cliente'	, 'etiqueta' => 'Cliente'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        //$tabela->addColuna(array('campo' => 'nota_num'	, 'etiqueta' => 'Pedido'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'valor'	, 'etiqueta' => 'Valor'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        
        //nota
        //numero da nota
        //cliente //pcnfsaid.codcli
        //valor
        $tabela->addColuna(array('campo' => 'data'	, 'etiqueta' => 'Data'  , 'tipo' => 'D', 'width' =>  80, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'impresso'	, 'etiqueta' => 'Impresso'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'C'));
        $tabela->addColuna(array('campo' => 'impressor'	, 'etiqueta' => 'Impresso Por'  , 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
        if(!$tabela->getPrimeira() || $forcar){
            $filtro = $tabela->getFiltro();
            $dados = $this->getDados($filtro['dtIni'] ?? '', $filtro['dtFim'] ?? '', $filtro['status'] ?? 'A');
            $tabela->setDados($dados);
            $param = array(
                'texto' => 'Imprimir Notas Selecionadas',
                'type' => 'submit',
                'form' => 'formNotas',
            );
            $tabela->addBotao($param);
            
            $tabela->setFormTabela(array('acao' => getLink() . 'imprimir&num=' . $vl_exec, 'id' => 'formNotas', 'nome' => 'formNotas'));
        }
        $ret .= $tabela;
        return $ret;
    }
    
    private function montarListaNotasEmpressas(){
        $ret = array();
        $sql = "select gf_impressoes_zebra.nota, gf_impressoes_zebra.user from (select nota, max(id) as id from gf_impressoes_zebra group by nota) as tmp1 join gf_impressoes_zebra on (tmp1.id = gf_impressoes_zebra.id)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['nota']] = $row['user'];
            }
        }
        return $ret;
    }
    
    private function getDados($dtIni, $dtFim, $status){
        $ret = array();
        
        $notas_impressas = $this->montarListaNotasEmpressas();
        
        $botao = [];
        $botao['texto'] 	= 'Imprimir';
        //$botao['url'] 	= getLinkAjax('imprimir');
        
        //$botao['tipo'] = 'link';
        //$botao['cor'] = 'danger';
        
        
        /*
        $ret[] = array(
            'bt' => $bt,
            'pedido' => 1,
            'data' => '20220606',
            'impresso' => 'X',
            'impressor' => 'fulano'
        );*/
        
        $where = array();
        
        $where[] = "numped in (select NUMPED from pcpedc where codusur in (872, 838, 841, 791, 849, 839))";
        $where[] = "pcnfsaid.dtcancel is null";
        $where[] = "pcnfsaid.tipoemissao != 2";
        
        if(!empty($dtIni)){
            $where[] = "dtsaida >= TO_DATE('$dtIni', 'YYYYMMDD')";
        }
        
        if(!empty($dtFim)){
            $where[] = "dtsaida <= TO_DATE('$dtFim', 'YYYYMMDD')";
        }
        
        $sql = "SELECT * from pcnfsaid where " . implode(' and ', $where);
        $rows = query4($sql);
        
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                if($status == 'A' || ($status == 'I' && isset($notas_impressas[$row['NUMNOTA']])) || ($status == 'N' && !isset($notas_impressas[$row['NUMNOTA']]))){
                    $temp = array();
                    /*
                    $botao['onclick'] = "op2('" . getLinkAjax('imprimir') . "&nota={$row['NUMNOTA']}')";
                    $bt = formbase01::formBotao($botao);
                    $temp['bt'] = $bt;
                    */
                    $temp['bt'] = formbase01::formCheck(array('nome' => "formNotas[{$row['NUMNOTA']}]"));
                    $temp['pedido'] = $row['NUMPED'];
                    $temp['nota'] = $row['NUMNOTA'];
                    $temp['cliente'] = $row['CODCLI'];
                    $temp['valor'] = formataReais($row['VLTOTAL']);
                    $temp['data'] = datas::dataMS2S($row['DTSAIDA']);
                    $temp['impresso'] = isset($notas_impressas[$row['NUMNOTA']]) ? 'X' : '';
                    $temp['impressor'] = $notas_impressas[$row['NUMNOTA']] ?? '';
                    
                    $ret[] = $temp;
                }
            }
        }
        
        return $ret;
    }
    
    private function getDadosEtiqueta($nota){
        $ret = array();
        $sql = "
select 
    n.PROTOCOLONFE as PROTOCOLO
    ,n.VLTOTAL AS VALOR
    ,n.CLIENTE AS CLI_NOME
    ,n.ENDERECO || ' , ' || n.BAIRRO || ' , ' || n.MUNICIPIO as CLI_ENDERECO
    ,n.UFCODIGO AS CLI_UF
    ,n.CGC as CLI_CPF
    ,n.IE as CLI_IE
    ,n.SERIE AS SERIE
    ,n.NUMNOTA as NUMERO
    ,n.DTSAIDA AS DT_EMISSAO
    ,f.razaosocial as EMI_NOME
    ,f.endereco || ' , ' || f.bairro || ' , ' || f.cidade as EMI_ENDERECO
    ,f.IE AS EMI_IE
    ,f.cgc AS EMI_CPF
    ,f.uf as EMI_UF
    ,n.chavenfe as CHAVE
    ,case when n.tipoemissao = 2 then 1 else 0 end as EMISSAO
from pcnfsaid n left join pcfilial f on (f.codigo = n.codfilial) where n.NUMNOTA = '$nota'";
        //left join pcclient c on (n.codcli = c.CODCLI)
        //echo '<br>' . $sql . '<br>';
        $rows = query4($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados_brutos = $rows[0];
        }
        //4314 0589 7350 7000 0100 5500 1001 5998 8611 1101 2051
        $ret['PROTOCOLO'] = $dados_brutos['PROTOCOLO'] ?? '';
        $ret['VALOR'] = formataReais($dados_brutos['VALOR'] ?? 0);
        $ret['CLI_NOME'] = ($dados_brutos['CLI_NOME'] ?? '');
        if(strlen($ret['CLI_NOME']) > 47){
            $ret['2CLI_NOME'] = $this->quebrarString($ret['CLI_NOME'], 2, 47, 693);
            $ret['CLI_NOME'] = $this->quebrarString($ret['CLI_NOME'], 1, 47, 693);
        }
        else{
            $ret['2CLI_NOME'] = '';
        }
        
        $ret['CLI_ENDERECO'] = ($dados_brutos['CLI_ENDERECO'] ?? '');
        $ret['CLI_UF'] = $dados_brutos['CLI_UF'] ?? '';
        $ret['CLI_CPF'] = $dados_brutos['CLI_CPF'] ?? '';
        $ret['CLI_IE'] = $dados_brutos['CLI_IE'] ?? '';
        $ret['SERIE'] = $dados_brutos['SERIE'] ?? '';
        $ret['NUMERO'] = $dados_brutos['NUMERO'] ?? '';
        $ret['DT_EMISSAO'] = datas::dataMS2D($dados_brutos['DT_EMISSAO']) ?? '';
        
        $ret['EMI_NOME'] = ($dados_brutos['EMI_NOME'] ?? '');
        if(strlen($ret['EMI_NOME']) > 47){
            $ret['2EMI_NOME'] = $this->quebrarString($ret['EMI_NOME'], 2, 47, 358);
            $ret['EMI_NOME'] = $this->quebrarString($ret['EMI_NOME'], 1, 47, 358);
            //$ret['2EMI_NOME'] = "^FO20,358^FDcom nome2^FS";
            //$ret['2EMI_NOME'] = "com nome2";
        }
        else{
            //$ret['2EMI_NOME'] = "^FO20,358^FDsem nome2^FS";
            $ret['2EMI_NOME'] = '';
        }
        
        $ret['EMI_ENDERECO'] = $dados_brutos['EMI_ENDERECO'] ?? '';
        $ret['EMI_UF'] = $dados_brutos['EMI_UF'] ?? '';
        $ret['EMI_CPF'] = $dados_brutos['EMI_CPF'] ?? '';
        $ret['EMI_IE'] = $dados_brutos['EMI_IE'] ?? '';
        
        $ret['BARRRAS'] = $dados_brutos['CHAVE'] ?? '';
        $ret['CHAVE'] = $this->formatarChave($dados_brutos['CHAVE']);
        
        
        
        $ret['EMISSAO'] = $dados_brutos['EMISSAO'] ?? '';
        
        return $ret;
    }
    
    private function quebrarString($string, $parte, $tamanho, $altura){
        $ret = '';
        
        $explode = explode(' ', $string);
        $parte_um = '';
        $continuar = true;
        $parte_dois = '';
        foreach ($explode as $palavra){
            //echo "<br>$palavra";
            if($continuar){
                if(strlen($parte_um . ' ' . $palavra) < $tamanho && $continuar){
                    $parte_um .= ' ' . $palavra;
                }
                else{
                    $parte_dois .= ' ' . $palavra;
                    $continuar = false;
                }
            }
            else{
                $parte_dois .= ' ' . $palavra;
            }
        }
        
        //$string = str_replace($parte_um, '', $string);
        
        
        
        if($parte == 1){
            $ret = $parte_um;
        }
        elseif ($parte == 2){
            $ret = "^FO20,$altura^FD$parte_dois^FS";
        }
        return $ret;
        
    }
    
    private function formatarChave($chave){
        $ret = '';
        if(!empty($chave)){
            $temp = str_split($chave, 4);
            $ret = implode(' ', $temp);
        }
        return $ret;
    }
    
    public function ajax(){
        $op = getOperacao();
        
        if($op === 'imprimir' || $op === 'multi'){
            $temp = "
^XA

^FX Pimeira Parte
^CF0,50
^FO0,40^FB800,100,1,C^FDDANFE SIMPLIFICADO\&^FS
^FO50,30^GB700,60,3^FS

^FX Segunda Parte
^CFA,30
^BY2,3,50 
^FO120,110 
^BC,50,N,,N,A
^FD@@BARRRAS^FS

^CFA,20
^FO0,180^FB800,100,1,C^FDCHAVE DE ACESSO\&^FS
^FO0,200^FB800,100,5,C^FD@@CHAVE\&^FS

^CFA,20
^FO0,240^FB800,100,1,C^FDPROTOCOLO DE AUTORIZACAO\&^FS
^FO0,260^FB800,100,5,C^FD@@PROTOCOLO\&^FS

^FO15,285^GB770,3,3^FS
 
^A0N,30,30^FO298,300^FDDADOS DO EMITENTE^FS
^A0N,30,30^FO299,300^FDDADOS DO EMITENTE^FS

^FO20,335^FDNOME /^FS
^FO21,335^FDNOME /^FS 
^FO22,335^FDNOME /^FS

^FO99,335^FDRAZAO^FS
^FO100,335^FDRAZAO^FS
^FO101,335^FDRAZAO^FS

^FO165,335^FDSOCIAL:^FS
^FO166,335^FDSOCIAL:^FS
^FO167,335^FDSOCIAL:^FS


^FO265,335^FD@@EMI_NOME^FS 
@@2EMI_NOME

^FO20,385^FB760,200,5,L^FDENDERECO: @@EMI_ENDERECO^FS
^FO21,385^FDENDERECO:^FS
^FO22,385^FDENDERECO:^FS

^FO20,435^FDUF: @@EMI_UF^FS
^FO21,435^FDUF:^FS
^FO22,435^FDUF:^FS

^FO20,460^FDCPF/CNPJ: @@EMI_CPF^FS
^FO21,460^FDCPF/CNPJ:^FS
^FO22,460^FDCPF/CNPJ:^FS

^FO400,460^FDIE: @@EMI_IE^FS
^FO401,460^FDIE:^FS
^FO402,460^FDIE:^FS

^FO15,485^GB770,3,3^FS

^A0N,30,30^FO280,500^FDDADOS^FS
^A0N,30,30^FO281,500^FDDADOS^FS 

^A0N,30,30^FO375,500^FDGERAIS^FS
^A0N,30,30^FO376,500^FDGERAIS^FS

^A0N,30,30^FO473,500^FDDA^FS
^A0N,30,30^FO474,500^FDDA^FS

^A0N,30,30^FO517,500^FDNF-E^FS
^A0N,30,30^FO518,500^FDNF-E^FS

^FO20,535^FDTIPO DE^FS 
^FO21,535^FDTIPO DE^FS
^FO22,535^FDTIPO DE^FS 

^FO115,535^FDOPERACAO^FS 
^FO116,535^FDOPERACAO^FS
^FO117,535^FDOPERACAO^FS

^FO220,535^FD(0 - ENTRADA / 1 - SAIDA): 1^FS 

^FO20,560^FDNUMERO: @@NUMERO^FS
^FO21,560^FDNUMERO:^FS
^FO22,560^FDNUMERO:^FS

^FO400,560^FDSERIE: @@SERIE^FS
^FO401,560^FDSERIE:^FS
^FO402,560^FDSERIE:^FS

^FO20,585^FDEMISSAO: @@DT_EMISSAO^FS
^FO21,585^FDEMISSAO:^FS
^FO22,585^FDEMISSAO:^FS

^FO15,610^GB770,3,3^FS


^A0N,30,30^FO273,635^FDDADOS DO ^FS 
^A0N,30,30^FO274,635^FDDADOS DO ^FS
^A0N,30,30^FO412,635^FDDESTINATARIO^FS 
^A0N,30,30^FO413,635^FDDESTINATARIO^FS 


^FO20,670^FDNOME / ^FS
^FO21,670^FDNOME / ^FS
^FO22,670^FDNOME / ^FS

^FO99,670^FDRAZAO^FS
^FO100,670^FDRAZAO^FS
^FO101,670^FDRAZAO^FS

^FO165,670^FDSOCIAL:^FS
^FO166,670^FDSOCIAL:^FS
^FO167,670^FDSOCIAL:^FS

^FO265,670^FD@@CLI_NOME^FS 
@@2CLI_NOME

^FO20,720^FB760,200,5,L^FDENDERECO: @@CLI_ENDERECO^FS
^FO21,720^FDENDERECO:^FS
^FO22,720^FDENDERECO:^FS


^FO20,770^FDUF: @@CLI_UF^FS
^FO21,770^FDUF:^FS
^FO22,770^FDUF:^FS

^FO20,795^FDCPF/CNPJ: @@CLI_CPF^FS
^FO21,795^FDCPF/CNPJ:^FS
^FO22,795^FDCPF/CNPJ:^FS

^FO400,795^FDIE: @@CLI_IE^FS
^FO401,795^FDIE:^FS
^FO402,795^FDIE:^FS

^FO15,820^GB770,3,3^FS

^FO20,855^FDVALOR TOTAL DA NF - R$: @@VALOR^FS
^FO20,880^FDEMISSAO EM CONTINGENCIA (0 - NAO / 1 - SIM): @@EMISSAO^FS


^XZ

";
            /*
            //^FO0,450^FB800,100,1,C^FDDADOS GERAIS DA NF-E\&^FS tirei para tentar gerar negrito
            //^FO0,300^FB800,100,1,C^FDDADOS DO EMITENTE\&^FS
            //^FO0,585^FB800,100,1,C^FDDADOS DO DESTINATARIO\&^FS
^FO280,450^FDDADOS GERAIS DA NF-E^FS
^FO281,450^FDDADOS GERAIS DA NF-E^FS
^FO282,450^FDDADOS GERAIS DA NF-E^FS

^FO273,585^FDDADOS DO DESTINATARIO^FS
^FO274,585^FDDADOS DO DESTINATARIO^FS 
^FO275,585^FDDADOS DO DESTINATARIO^FS

^FO298,300^FDDADOS DO EMITENTE^FS
^FO299,300^FDDADOS DO EMITENTE^FS 
^FO300,300^FDDADOS DO EMITENTE^FS 
             */
            if($op === 'imprimir'){
                $nota = $_GET['nota'];
                $dados = $this->getDadosEtiqueta($nota);
                foreach ($dados as $campo => $valor){
                    $temp = str_replace("@@$campo", $valor, $temp);
                }
                echo $temp;
                $this->printarJsImprimir($nota);
            }
            else{
                $notas = $_GET['notas'];
                $notas = explode(',', $notas);
                $nota_multi = '';
                foreach ($notas as $nota){
                    $dados = $this->getDadosEtiqueta($nota);
                    $temp_atual = $temp;
                    foreach ($dados as $campo => $valor){
                        $temp_atual = str_replace("@@$campo", $valor, $temp_atual);
                    }
                    $nota_multi .= $temp_atual;
                }
                echo $nota_multi;
                $this->printarJsImprimir('', $notas);
            }
        }
        
        elseif($op == 'marcar'){
            $nota = $_GET['nota'];
            $user = $_GET['user'];
            $data = date('Ymd');
            
            $sql = "insert into gf_impressoes_zebra values (null, '$nota', '$data', '$user')";
            query($sql);
        }
        
        elseif($op == 'marcar_multi'){
            $notas = $_GET['notas'];
            $user = $_GET['user'];
            $data = date('Ymd');
            
            
            $notas = explode(',', $notas);
            foreach ($notas as $nota){
                $sql = "insert into gf_impressoes_zebra values (null, '$nota', '$data', '$user')";
                log::gravaLog('230503.txt', $sql);
                query($sql);
            }
        }
        
        return '';
    }
    
    private function printarJsImprimir($nota = '', $notas = array()){
        global $config;
        $link_jquery = $config['plugins'].'jquery/jquery.min.js';
        echo '	<script  src="'.$link_jquery.'"></script>';
        if(!empty($nota)){
            $url_ajax = getLinkAjax('marcar') . "&user=" . getUsuario() . "&nota=$nota";
        }
        elseif(count($notas) > 0){
            $url_ajax = getLinkAjax('marcar_multi') . "&user=" . getUsuario() . "&notas=" . implode(',', $notas);
        }
        echo "
<script>
(function() {
    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (mql.matches) {
            } else {
                if(confirm('Etiqueta(s) impressa(s) com Sucesso? Se sim, confirmar')){
                    $.ajax(
						{
							url: '".$url_ajax."',
							success: function(result){
                                window.close();
							}
						}
			         );
                }
                else{
                    window.close();
                }
                
            }
        });
    }
}());
window.onload = function() {
    window.print();
};
</script>
";
    }
}
