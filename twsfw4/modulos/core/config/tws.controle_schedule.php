<?php
//if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class controle_schedule{
    var $_dados;
    
    var $_tabela;
    
    var $_campos;
    
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar' 		=> true,
        'excluir' 		=> true,
        'gravar'        => true,
        'cadear'        => true,
    );
    
    var $_log;
    
    function __construct(){
        $this->_tabela = 'schedule';
        $this->_campos = array('nome','descricao','periodo','prioridade','programa','parametros','ano','mes','dia','hora','minuto','semana','ativo','del','fil','emp');
        $this->_log = 'controle_schedule';
    }
    
    function index(){
        return $this->browser1();
    }
    
    function browser1(){
        $ret = '';
        $this->getDados();
        $param = [];
        $param['paginacao'] = false;
        //$param['width'] = 'AUTO';
        $param['titulo'] = 'Controle Schedule';
        $browser = new tabela01($param);
        $browser->setDados($this->_dados);
        
        $browser->addColuna(array('campo' => 'id'			, 'etiqueta' => 'ID'		, 'width' =>  80, 'posicao' => 'C'));
        $browser->addColuna(array('campo' => 'nome'			, 'etiqueta' => 'Nome'		, 'width' => 100, 'posicao' => 'E'));
        $browser->addColuna(array('campo' => 'descricao'	, 'etiqueta' => 'Descrição'	, 'width' => 100, 'posicao' => 'E'));
        $browser->addColuna(array('campo' => 'periodo'		, 'etiqueta' => 'Periodo'	, 'width' =>  80, 'posicao' => 'C'));
        $browser->addColuna(array('campo' => 'parametros'	, 'etiqueta' => 'Parametros', 'width' => 200, 'posicao' => 'esquerda'));
        $browser->addColuna(array('campo' => 'ativo'		, 'etiqueta' => 'Ativo'		, 'width' =>  80, 'posicao' => 'centro'));
        
        
        $botaoedi = [];
        $botaoedi['texto'] = 'Editar'; 
        $botaoedi['link'] 	= getLink().'editar&id=';
        $botaoedi['coluna']= 'id';
        $botaoedi['flag'] 	= '';
        $botaoedi['width'] = 80;
        
        $this->jsConfirmaExclusao('"Confirma a exclusão do schedule? \nDescrição: "+desc');
        $botaoexcluir = [];
        $botaoexcluir['texto'] = 'Excluir';
        //$botaoexcluir['link'] 	= getLink().'excluir&id=';
        $botaoexcluir['link'] 	= "javascript:confirmaExclusao('" . getLink() .'excluir&id=' . "','{ID}',{COLUNA:descricao})";
        $botaoexcluir['coluna']= 'id';
        $botaoexcluir["cor"] 	= 'danger';
        $botaoexcluir['flag'] 	= '';
        $botaoexcluir['width'] = 80;
        
        
        $this->jsConfirmaExecucao('"Confirma a execução do schedule? \nDescrição: "+desc');
        // Adiciona acao excluir
        $botaoexecutar = [];
        $botaoexecutar["texto"] = "Executar";
        $botaoexecutar["link"] 	= "javascript:confirmaExecucao('schedule.php?schedule=','{ID}',{COLUNA:descricao})";
        $botaoexecutar["coluna"]= 'nome';
        $botaoexecutar["flag"] 	= '';
        $botaoexecutar["cor"] 	= 'warning';
        $botaoexecutar["width"] = 80;
        
        
        $botaoinc = [];
        $botaoinc['texto'] = 'Incluir';
        //$botaoinc['link'] 	= getLink().'editar';
        $botaoinc['onclick']	= "setLocation('".getLink()."editar')";
        $botaoinc['flag'] 	= '';
        //$botaoinc['width'] = 100;
        
        $browser->addAcao($botaoedi);
        $browser->addAcao($botaoexcluir);
        $browser->addAcao($botaoexecutar);
        $browser->addBotaoTitulo($botaoinc);
        $ret .= $browser;
        return $ret;
    }
    
    function  getDados(){
    	$this->_dados = [];
    	
        $sql = "SELECT * FROM $this->_tabela where del <> '*'";
        $rows = query($sql);
        foreach ($rows as $row){
            $temp = [];
            $temp['id'			] = $row['id'];
            $temp['nome'		] = $row['nome'];
            $temp['descricao'	] = $row['descricao'];
            $temp['periodo'		] = $row['periodo'];
            $temp['parametros'	] = '';
            $parametro = $row['parametros'];
            $caracteres = 100;
            $tam = strlen($parametro);
            $linhas = round($tam/$caracteres,0) + 1;
            for($i=0;$i<$linhas;$i++){
            	$ini = $i * $caracteres;
            	if($i > 0){
            		$temp['parametros'	] .= '<br>';
            	}
            	$temp['parametros'	] .= substr($parametro, $ini, $caracteres);
            }
            $temp['parametros'	] = substr($row['parametros'], 0, 150);
            $temp['ativo'		] = $row['ativo'];
            
            $this->_dados[] = $temp;
        }
    }
    
    function editar($dados_erro = [], $msg = ''){
    	$ret = '';
    	
    	$id = $_GET['id'] ?? '';
    	
        $titulo = '';
        $form = new form01();
        $dado = [];
        foreach($this->_campos as $campo){
            $dado[$campo] = '';
        }
        if (!empty($id)){
            $titulo = 'editar';
            $url = getLink().'gravar&id='.$id;
            $sql = "SELECT * FROM $this->_tabela WHERE id = $id";
            $dadob = query($sql);
            $dadob = $dadob[0];
            foreach($this->_campos as $campo){
                if(isset($dadob[$campo])){
                    $dado[$campo] = $dadob[$campo];
                }
            }
            
        }elseif(count($dados_erro) > 0){
            foreach($this->_campos as $campo){
                if(isset($dados_erro[$campo])){
                    $dado[$campo] = $dados_erro[$campo];
                }
            }
            if(isset($dados_erro['id'])){
                $titulo = 'editar';
                $url = getLink().'gravar&id='.$dados_erro['id'];
            }
            else{
                $titulo = 'incluir';
                $url = getLink().'gravar';
            }
            addPortalMensagem('Atenção', $msg, 'erro');
        }else{
            $titulo = 'Incluir Programa';
            $url = getLink().'gravar';
        }
        $hora = '0=0; 1=1; 2=2; 3=3; 4=4; 5=5; 6=6; 7=7; 8=8; 9=9; 10=10; 11=11; 12=12; 13=13; 14=14; 15=15; 16=16; 17=17; 18=18; 19=19; 20=20; 21=21; 22=22; 23=23';
        $ativo = 'S=sim;N=não';
        $periodo = '5=De 5 em 5 minutos;N=De 15 em 15 minutos;D=Diario;M=Mensal;H=Hora';
        $minuto = '0=00;1=15;2=30;3=45';
        $prioridade = '1=1;2=2;3=3;4=4;5=5;6=6;7=7;8=8;9=9;10=10';
        
        $form->addCampo(array('id' => '', 'campo' => 'formt[nome]'		, 'etiqueta' => 'Nome'			,'tipo' => 'T'	, 'largura' =>  6,'tamanho' => '11', 'linhas' => '' , 'valor' => $dado['nome']		, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[descricao]'	, 'etiqueta' => 'Descrição'		,'tipo' => 'T'	, 'largura' =>  6,'tamanho' => '11', 'linhas' => '' , 'valor' => $dado['descricao']	, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[programa]'	, 'etiqueta' => 'Programa'		,'tipo' => 'T'	, 'largura' =>  4,'tamanho' => '11', 'linhas' => '' , 'valor' => $dado['programa']	, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[periodo]'	, 'etiqueta' => 'Periodo'		,'tipo' => 'T'	, 'largura' =>  4,'tamanho' => '11', 'linhas' => '' , 'valor' => $dado['periodo']	, 'opcoes' => $periodo, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[prioridade]', 'etiqueta' => 'Prioridade'	,'tipo' => 'T'	, 'largura' =>  4,'tamanho' => '4' , 'linhas' => '' , 'valor' => $dado['prioridade'], 'opcoes' => $prioridade		,'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[dia]'		, 'etiqueta' => 'Dia'			,'tipo' => 'N'	, 'largura' =>  4,'tamanho' => '11', 'linhas' => '' , 'valor' => $dado['dia']		, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[hora]'		, 'etiqueta' => 'Hora'			,'tipo' => 'T'	, 'largura' =>  4,'tamanho' => '4' , 'linhas' => '' , 'valor' => $dado['hora']		, 'opcoes' => $hora		,'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[minuto]'	, 'etiqueta' => 'Minuto'		,'tipo' => 'T'	, 'largura' =>  4,'tamanho' => '4' , 'linhas' => '' , 'valor' => $dado['minuto']	, 'opcoes' => $minuto		,'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[semana]'	, 'etiqueta' => 'Dias da Semana','tipo' => 'DS'	, 'largura' =>  8,'tamanho' => '11', 'linhas' => '' , 'valor' => $dado['semana']	, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[ativo]'		, 'etiqueta' => 'Ativo'			,'tipo' => 'T'	, 'largura' =>  4,'tamanho' => '4' , 'linhas' => '' , 'valor' => $dado['ativo']		, 'opcoes' => $ativo, 'validacao' => '', 'obrigatorio' => true));
        $form->addCampo(array('id' => '', 'campo' => 'formt[parametros]', 'etiqueta' => 'Parametros'	,'tipo' => 'TA'	, 'largura' => 12,'tamanho' => '11', 'linhas' => '6', 'valor' => $dado['parametros'], 'validacao' => '', 'obrigatorio' => true));
        
        $form->setEnvio($url, 'tform', 'tform');
        
        $param = [];
        $param['conteudo'] = $form;
        $param['titulo'] = $titulo;
        $ret = addCard($param);
        
        return $ret;
    }
    
    function excluir(){
        $sql = "SELECT * FROM $this->_tabela where id = " . $_GET['id'];
        $dado_temp = query($sql);
        $dado_temp = $dado_temp[0];
        $sql = "UPDATE $this->_tabela set del = '*', ativo = 'N' where id = ".$_GET['id'];
        query($sql);
        log::gravaLog($this->_log, 'o usuário ' . getUsuario() . ' excluiu o seguinte schedule: ' . $dado_temp['nome']);
        return $this->index();
    }
    
    function gravar(){
        $post = $_POST['formt'];
        global $config;
        $semana = '';
        $erro = '';
        $where = '';
        $cadeado = array(
            'semana' => array('periodo' => array('D', 'N'))
          //campo1 => array(campo2 => array(valores)) = se o campo 2 for igual a um dos valores, o campo1 deve estar preenchido
        ); //
        if(isset($_POST['diasSemana'])){
            $diasb = $_POST['diasSemana'];
            foreach($diasb as $key => $value){
                $semana .= $key;
            }
        }
        
        if(isset($_GET['id'])){
            $where = 'id = '.$_GET['id'];
        }
        
        $dados = [];
        foreach($this->_campos as $campo){
            if(isset($post[$campo])){
                $dados[$campo] = $post[$campo];
            }
            else{
                $dados[$campo] = '';
            }
        }
        
        
        $dados['del'] = ' ';
        $dados['cliente'] = $config['cliente'];
        $dados['semana'] = $semana;
        
        if(!isset($dados['ano'])){
            $dados['mes'] = date('m');
            $dados['ano'] = date('Y');
        }
        
        else{
            if($dados['ano'] == ''){
                $dados['mes'] = date('m');
                $dados['ano'] = date('Y');
            }
        }
        
        if($dados['periodo'] != 'M' && !isset($dados['dia'])){
            $dados['dia'] = date('d');
        }
        
        
        $erro = $this->cadear($dados, $cadeado);
        
        if($erro == ''){
            if($where == ''){
                $sql = montaSQL($dados, $this->_tabela, 'INSERT');
                log::gravaLog($this->_log,  'o usuário ' . getUsuario() . ' incluiu o seguinte schedule: ' . $dados['nome'] . ' os dados foram: ' . json_encode($dados));
            }else{
                $sql = montaSQL($dados, $this->_tabela, 'UPDATE', $where);
                log::gravaLog($this->_log,  'o usuário ' . getUsuario() . ' editou o seguinte schedule: ' . $dados['nome'] . ' os novos dados são: ' . json_encode($dados));
            }
            query($sql);
            return $this->index();
        }
        else{
            return $this->editar($dados, $erro);
        }
    }
    
    function cadear($dados, $cond){
        $ret = '';
        $campos = [];
        foreach($cond as $campo1 => $temp){
            if(count($temp) > 0){
                foreach($temp as $campo2 => $valores){
                    foreach($valores as $val){
                        if($dados[$campo2] == $val){
                            if(!isset($dados[$campo1])){
                                $campos[] = ucfirst($campo1);
                            }
                            else{
                                if($dados[$campo1] == ''){
                                    $campos[] = ucfirst($campo1);
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach($this->_campos as $campos_p){
            if($campos_p != 'del' && $campos_p != 'fil' && $campos_p != 'emp'){
                if(isset($dados[$campos_p])){
                    if($dados[$campos_p] == ''){
                        if(!in_array($campos_p, $campos)){
                            $campos[] = $campos_p;
                        }
                    }
                }
                else{
                    $campos[] = $campos_p;
                }
            }
        }
        if(count($campos) == 1){
            $ret = 'O seguinte campo não foi preenchido: ' . $campos[0];
        }
        else if(count($campos) > 1){
            $ret = implode(', ', $campos);
            $ret = 'Os seguintes campos não foram preenchidos: ' . $ret;
            return $ret;
        }
        return $ret;
    }
    
    function jsConfirmaExecucao($titulo){
        addPortaljavaScript('function confirmaExecucao(link,id,desc){');
        addPortaljavaScript('	if (confirm('.$titulo.')){');
        addPortaljavaScript('		op2(link+id);');
        addPortaljavaScript('	}');
        addPortaljavaScript('}');
    }
    
    function jsConfirmaExclusao($titulo){
        addPortaljavaScript('function confirmaExclusao(link,id,desc){');
        addPortaljavaScript('	if (confirm('.$titulo.')){');
        addPortaljavaScript('		setLocation(link+id);');
        addPortaljavaScript('	}');
        addPortaljavaScript('}');
    }
}