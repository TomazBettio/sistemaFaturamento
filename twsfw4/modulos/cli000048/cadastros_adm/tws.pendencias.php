<?php
/*
 * Data Criação: 18/09/2023
 * Autor: BCS
 *
 * Descricao: 	Tela de pendências dos itens das certificações
 * 
 * tela inicial 
 * coloca botoes de aprovar/rejeitar/detalhes
 * tira status
 * coloca coluna valor: traduzir valor (se era select, se era arquivo linka)
 * usar botoes dropdown
 * 
 */
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class pendencias{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'        => true,
        'aprovar'       => true,
        'reprovar'      => true,
        'salvar'        => true,
        
        'ajax'          => true,
    );
    
    private $_dir = '/var/www/pmp/anexos/';
    //FORMATO ANEXO: base/id_requisicao/id_documento/arquivo
    
	public function __construct(){
	    $temp = "";
	    addPortaljavaScript($temp);
	}
	
	public function index()
	{
	    $ret = '';
	    $ret .= addLinha(['tamanhos' => [6,6],'conteudos' => [$this->montaBoxPendencias('E'),$this->montaBoxPendencias('S')]]);
	    return $ret;
	}

	private function montaBoxPendencias($tipo = 'E')
	{
	    $ret = '';
	    
	    $titulo = $tipo == 'S' ? 'A Aprovar' :   'Preencher e Aprovar';
	    
	    $tab = new tabela01(['titulo' => $titulo]);
	    
	    $tab->addColuna(['campo' => 'data_ini' , 'etiqueta' => 'Data da Requisição', 'tipo' => 'D', 'width' =>  20, 'posicao' => 'centro']);
	    $tab->addColuna(['campo' => 'titulo'   , 'etiqueta' => 'Título'            , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro']);
	    //$tab->addColuna(['campo' => 'status'   , 'etiqueta' => 'Status'            , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro']);
	    if($tipo == 'S'){
	        $tab->addColuna(['campo' => 'valor'   , 'etiqueta' => 'Valor'            , 'tipo' => 'T', 'width' =>  20, 'posicao' => 'centro']);
	    }
	    
	    if($tipo == 'E')
	    {
	        $param = [];
	        $param['texto'] = 'Editar';
	        $param['link'] 	= getLink()."aprovar&tipo=$tipo&id=";
	        $param['coluna']= 'id';
	        $param['width'] = 10;
	        $param['cor'] 	= 'success';
	        $tab->addAcao($param);
	    } else {
    	    // Botões dropdown
    	    $param = [];
    	    $param['titulo'] = 'Ações';
    	    $param['width'] = 100;
    	    
    	    $i = 0;
    	    $this->jsConfirmaAprova('APROVAR esse valor?');
    	    
    	    $param['opcoes'][$i]['texto'] 	= 'Aprovar';
    	    $param['opcoes'][$i]['link'] 	= "javascript:confirmaAprova('" . getLink() . "salvar&aprova=S&id=','{ID}')";
    	    //getLink() . 'salvar&aprova=S&id=';
    	    $param['opcoes'][$i]['coluna'] 	= 'id';
    	    
    	    $i++;
    	    $this->jsConfirmaRejeita('REJEITAR esse valor?');
    	    
    	    $param['opcoes'][$i]['texto'] 	= 'Rejeitar';
    	    $param['opcoes'][$i]['link'] 	= "javascript:confirmaRejeita('" . getLink() . "reprovar&id=','{ID}')";
    	    //getLink() . 'salvar&aprova=N&id=';
    	    $param['opcoes'][$i]['coluna'] 	= 'id';
    	    
    	    $i++;
    	    $param['opcoes'][$i]['texto']   = 'Detalhes';
    	    $param['opcoes'][$i]['link'] 	= getLink()."aprovar&tipo=$tipo&id=";
    	    $param['opcoes'][$i]['coluna']  = 'id';
    	    
    	    $tab->addAcaoDropdown($param);
	    
	    }
	    
	    $dados = $this->getItens($tipo);
	    $tab->setDados($dados);
	    
	    $ret .= $tab;
	    return $ret;
	}
	
	public function ajax()
	{
	    $op = getOperacao();
	    switch ($op)
	    {
	        case 'mostrarAnexo':
	            $var = getParam($_GET, 'var','');
	            if(!empty($var))
	            {
	                $var = explode("|", base64_decode($var));
	                $req = $var[0];
	                $doc = $var[1];
	                $id = $var[2];
	                
	                $arquivo = $this->getArquivo($id, $req, $doc);
	                if(!empty($arquivo))
	                {
	                    $download = getParam($_GET, 'download', '');
	                    $download = '';
	                    $file = $this->_dir . "$req/$doc/$arquivo";
	                    return $this->mostrarArquivo($file, $arquivo, $download);
	                }
	            }
	        default :
	            break;
	    }
	}
	
	private function getArquivo($id, $req, $doc)
	{
	    $ret = '';
	    $sql = "SELECT valor FROM pmp_item_requisicao WHERE id = $id AND requisicao = $req AND documento = $doc AND ativo = 'S' AND documento IN (SELECT id FROM pmp_param_certificacao WHERE tipo = 'DO' AND ativo = 'S')";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows)==1){
	        $ret = $rows[0]['valor'];
	    }
	    return $ret;
	}
	
	
	function mostrarArquivo($file, $arquivo, $download){
	    if(!empty($download)){
	        header('Content-Description: File Transfer');
	        header('Content-Type: application/octet-stream');
	        header("Content-Disposition: attachment; filename=\"$arquivo\"");
	        header('Content-Transfer-Encoding: binary');
	        header('Expires: 0');
	        header('Cache-Control: must-revalidate');
	        header('Pragma: public');
	        header('Content-Length: ' . filesize($file));
	        header('Connection: close');
	        ob_clean();
	        flush();
	        readfile($file);
	    }
	    else{
	        header('Content-Type: image');
	        header('Content-Length: ' . filesize($file));
	        header("Content-Disposition: inline; filename=\"$arquivo\"");
	        echo file_get_contents($file);
	    }
	    die();
	}
	
	private function getIdUsuarioColab()
	{
	    $ret = '';
	    $user = getUsuario();
	    $sql = "SELECT id FROM pmp_colaborador WHERE user = '$user' AND ativo = 'S'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = $rows[0]['id'];
	    }
	    return $ret;
	}
	
	private function getNomeUsuarioColab($id)
	{
	    $ret = '';
	    $sql = "SELECT user FROM pmp_colaborador WHERE id = $id AND ativo = 'S'";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows) > 0){
	        $ret = $rows[0]['user'];
	        $ret = getUsuario('nome',$ret);
	    }
	    return $ret;
	}
	//só aprovar: responsavel S, preencher e aprovar: E
	private function getItens($tipo)
	{
	    //Itens tipo E onde o usuário deve preencher e aprovar
	    //Itens tipo S apenas para serem aprovados pelo usuário

	    $ret = [];
	    $id_usuario = $this->getIdUsuarioColab();
	    
	    $sql = "SELECT 
                    pmp_param_certificacao.titulo AS titulo,
                    pmp_item_requisicao.id AS id,
                    pmp_requisicao.data_ini AS data_ini,
                    pmp_item_requisicao.status AS status,
                    pmp_item_requisicao.valor AS valor,
                    pmp_item_requisicao.documento AS doc,
                    pmp_item_requisicao.requisicao AS requi,

                    pmp_param_certificacao.tipo AS tipo,
                    pmp_param_certificacao.aux AS aux

                FROM pmp_param_certificacao JOIN pmp_item_requisicao ON pmp_param_certificacao.id = pmp_item_requisicao.documento
                JOIN pmp_requisicao ON pmp_item_requisicao.requisicao = pmp_requisicao.id
                WHERE pmp_requisicao.ativo = 'S' AND pmp_item_requisicao.ativo = 'S' AND pmp_param_certificacao.ativo = 'S'
                AND pmp_item_requisicao.status IN ('A')
                AND pmp_requisicao.status = 'A'
                AND pmp_item_requisicao.validador = $id_usuario 
                AND pmp_param_certificacao.responsavel = '$tipo' 
        ";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows)>0){
	        $temp = [];
	        $campos = ['titulo','id','data_ini'];
	        foreach($rows as $row){
	            foreach($campos as $ca){
	                $temp[$ca] = $row[$ca];
	            }
	            $temp['valor'] = $this->validaValor($row['valor'], $row['tipo'], $row['aux']);
	            if($row['tipo'] == 'DO' && $temp['valor'] != '')
	            {
	                //Tipo Anexo: permite fazer download
	                
	                $var = base64_encode($row['requi']."|".$row['doc']."|".$row['id']);
	                $link = getLinkAjax('mostrarAnexo') . "&var=$var";
	                //Extensão do arquivo
	                $n = explode('.',$temp['valor']);
	                $extensao = empty($n)? '' : $n[count($n)-1];
	                if(in_array($extensao, ['png', 'jpg', 'jpeg'])){
	                    $html = '<a class="btn btn-tool" onclick="window.open(\'' . $link . '\', \'_blank\').focus();">' . $temp['valor'] . '</a>';
	                } else {
	                    $link .= "&download=1";
	                    $html = '<a class="btn btn-tool" href="' . $link . '" download>' . $temp['valor'] . '</a>';
	                }
	                $temp['valor'] = $html;
	            }
	            
	            if($row['tipo'] == 'A')
	            {
	                //Tipo lista precisa traduzir o valor
	                $temp['valor'] = $this->traduzValorLista($row['aux'], $row['valor'], $row['tipo']);
	                
	            }
	            $temp['id'] = base64_encode($temp['id']);
	            $ret[] = $temp;
	        }
	    }
	    return $ret;
	}
	
	private function traduzValorLista($lista, $valor, $tipo)
	{
	    $ret = $valor;
	    if($tipo == 'A')
	    {
	        $valores = [];
	        
	        $tab = explode('|', $lista);
	        if(count($tab) > 2){
	            $tabela = $tab[0];
	            $id = $tab[1];
	            $desc = $tab[2];
	            $ordem = isset($tab[3]) && !empty($tab[3])? ' ORDER BY '.$tab[3] : '';
	            $where = isset($tab[4]) && !empty($tab[4]) ? ' WHERE '.$tab[4] : '';
	            $sql = "SELECT $id,$desc FROM $tabela $where $ordem";
	            //echo "$sql <br>\n\n";
	            $rows = query($sql);
	            if(isset($rows[0][$desc])){
	                foreach ($rows as $row){
	                    if($row[0] == $valor){
	                        $ret = $row[1];
	                        break;
	                    }
	                }
	            }
	        } else {
	            $valores = tabela($lista);
	            foreach($valores as $val){
	                if($val[0] == $valor){
	                    $ret = $val[1];
	                    break;
	                }
	            }
	        }
	    }    
	    
	    return $ret;
	}
	
	private function validaValor($valor,$tipo,$aux)
	{
	    $ret = '';
	    switch($tipo)
	    {
	        case 'CB':
	            $ret = $valor == 'on' ? 'Sim' : 'Não'; 
	            break;
	        case 'A':
	            $chave_val = explode(';',$aux);
	            foreach($chave_val as $chaval){
	                $temp = explode('=',$chaval);
	                if(isset($temp[1]) && $valor == $temp[0]){
	                    $ret = $temp[1];
	                }
	            }
	            break;
	        default:
	            $ret = $valor;
	            break;
	    }
	    return $ret;
	}
	
	public function reprovar()
	{
	    $ret = '';
	    $id = getParam($_GET, 'id','');
	    if(!empty($id))
	    {
	        $form = new form01(['geraScriptValidacaoObrigatorios' => true,]);
	        $form->addCampo(array('id' => '', 'campo' => "formMensagem[texto]",  'etiqueta' => 'Justificativa Reprovação', 'tipo' => 'TA', 'tamanho' => '35', 'linha' => '2', 'valor' => '', 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => true, ));
	        
	        $link = getLink() . "salvar&aprova=N&id=$id";
	        $form->setEnvio($link, 'formMensagem', 'formMensagem');
	        
	        $ret .= addCard(['titulo'=>'Reprovação Pendência', 'conteudo'=>''.$form]);
	        
	    }
	    return $ret;
	}
	
	public function aprovar()
	{
	    $ret = '';
	    $id = getParam($_GET, 'id');
	    $tipo = getParam($_GET, 'tipo');
	    
	    $par = $this->getDadosItem(base64_decode($id));
	    $form = new form01(['geraScriptValidacaoObrigatorios' => true,]);
	    $form->addCampo(array('id' => '', 'campo' => "colab", 'etiqueta' => 'Solicitante'       , 'tipo' => 'I', 'tamanho' => '15', 'linha' => '1', 'valor' => $this->getNomeUsuarioColab($par['solicitante']), 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false, ));
	    $form->addCampo(array('id' => '', 'campo' => "data",  'etiqueta' => 'Data da Requisição', 'tipo' => 'I', 'tamanho' => '15', 'linha' => '1', 'valor' => datas::dataS2D($par['data_ini']), 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false, ));
	    
	    $anexo = '';
	    
        $obrigatorio = $par['obrigatorio'] == 'S' ? true : false;
        $param = ['id' => '',   'campo' => "formReq[valor]",  'etiqueta' => $par['titulo'],   'tipo' => $par['tipo'], 'tamanho' => '15','linha' => '0', 'valor' => $par['valor'],    'pasta'	=> 0,'lista' => '','opcoes' => '','validacao' => '','largura' => 4, 'obrigatorio' => $obrigatorio ];
        if($tipo == 'E'){
            switch ($par['tipo'])
            {
                case 'A':
                    $param['tabela_itens'] = $par['aux'];
                    break;
                case 'T':
                    $param['tamanho'] = $par['aux'];
                    break;
                case 'DO':
                    //Tipo Anexo:
                    $param['tipo'] = 'F';
                    $param['texto'] = "Anexe aqui: {$par['titulo']}";
                    $param['estilo'] = 'opacity:0';
                    break;
                default:
                    break;
            }
            
            $form->addCampo($param);
            
            //Campo de aprova/reprova
            $form->addCampo(array('id' => '', 'campo' => "formReq[aprova]"      , 'etiqueta' => 'Resultado da Avaliação'        , 'tipo' => 'A' 	, 'tamanho' => '15', 'linha' => '0', 'valor' => '', 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => true, 'opcoes' => 'S=Aprovado;R=Rejeitado' ));
            $form->addCampo(array('id' => '', 'campo' => "formReq[mensagem]",  'etiqueta' => 'Justificativa Reprovação', 'tipo' => 'TA', 'tamanho' => '35', 'linha' => '2', 'valor' => '', 'pasta'	=> 0, 'lista' => '', 'validacao' => '', 'largura' => 4, 'obrigatorio' => false, ));
            
            
            $form->setEnvio(getLink() . "salvar&id=$id&tipoItem={$par['tipo']}", 'formReq', 'formReq');
            $botoes = [];
        } else {
            
            if($par['tipo'] == 'DO' && $param['valor'] != '')
            {
                //Tipo Anexo: permite fazer download
                //$req64 = base64_encode($par['requi']);
                //$doc64 = base64_encode($par['doc']);
                $var = base64_encode($par['requi']."|".$par['doc']."|".base64_decode($id));
                $link = getLinkAjax('mostrarAnexo') . "&var=$var";
                /*
                $var = base64_encode($row['requi']."|".$row['doc']."|".$row['id']);
                $link = getLinkAjax('mostrarAnexo') . "&var=$var";
                //Extensão do arquivo
                $n = explode('.',$temp['valor']);
                $extensao = empty($n)? '' : $n[count($n)-1];
                if(in_array($extensao, ['png', 'jpg', 'jpeg'])){
                    $html = '<a class="btn btn-tool" onclick="window.open(\'' . $link . '\', \'_blank\').focus();">' . $temp['valor'] . '</a>';
                } else {
                    $link .= "&download=1";
                    $html = '<a class="btn btn-tool" href="' . $link . '" download>' . $temp['valor'] . '</a>';
                }
                $temp['valor'] = $html;
                */
                //$link = getLinkAjax('mostrarAnexo') . "&req=$req64&doc=$doc64&arquivo={$par['valor']}";
                $param_botao = [
                    'onclick'   => "window.open('$link', '_blank').focus();",
                    'texto'     => "Visualizar Anexo",
                    'cor'       => 'default',
                    ];
                $anexo .= formbase01::formBotao($param_botao);
            } else {
                $param['tipo'] = $par['tipo'] == 'CB' ? 'CI' : 'I';
                $param['valor'] = $this->traduzValorLista($par['aux'], $par['valor'], $par['tipo']);
            }
            
            $form->addCampo($param);
            
            $botoes = [[
                'onclick' => "setLocation('".getlink()."index')",
                'id' => 'myInputOpenModal',
                'texto' => 'Voltar',
                'cor' => 'warning',
            ],
            ];
            
        }
        
        $ret .= addCard(['titulo'=>'Aprovar Pendência', 'conteudo'=>''.$form.$anexo, 'botoesTitulo' => $botoes]);
	    return $ret;
	}
	
	private function getDadosItem($id)
	{
	    $ret = [];
	    
	    $sql = "SELECT
                        pmp_param_certificacao.id AS id,
                        pmp_param_certificacao.tipo AS tipo,
                        pmp_param_certificacao.titulo AS titulo,
                        pmp_param_certificacao.aux AS aux,
                        pmp_param_certificacao.responsavel AS responsavel,
                        pmp_param_certificacao.papel_aprovador AS papel_aprovador,
                        pmp_param_certificacao.obrigatorio AS obrigatorio,
                        pmp_item_requisicao.valor AS valor,
                        pmp_item_requisicao.status AS status,
                        pmp_requisicao.colaborador AS solicitante,
                        pmp_requisicao.data_ini AS data_ini,
                        pmp_requisicao.id AS requi,
                        pmp_item_requisicao.documento AS doc

                FROM (pmp_param_certificacao JOIN pmp_item_requisicao
                ON pmp_param_certificacao.id = pmp_item_requisicao.documento) JOIN pmp_requisicao ON pmp_requisicao.id = pmp_item_requisicao.requisicao
                WHERE pmp_item_requisicao.id = $id
                ";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows)>0){
	        $campos = ['id','tipo','titulo','aux','responsavel','papel_aprovador','obrigatorio', 'valor','status','data_ini','solicitante','requi','doc'];
	            foreach($campos as $campo){
	                $ret[$campo] = $rows[0][$campo];
	            }
	    }
	    return $ret;
	}
	
	public function salvar()
	{
	    //Se veio do editar, salva e redireciona para o aprovar
	    //Se veio do aprovar, redireciona pro index
	    $id = getParam($_GET, 'id', '');
	    $aprova = getParam($_GET, 'aprova','');
	    if(!empty($id))
	    {
	        $id = base64_decode($id);
	        if(empty($aprova) && !empty($id))
	        {
	            //Salva o valor preenchido
	            $dados = getParam($_POST, 'formReq',[]);
	            if(isset($dados['aprova']))
	            {
	                $status = $dados['aprova'] == 'S' ? 'F' : 'R';
	                
	                if(isset($dados['valor'])) //campo comum
	                {
	                    $tipo_item = getParam($_GET, 'tipoItem','');
	                    if($tipo_item == 'V'){
	                        $dados['valor'] = str_replace(['.',','], ['','.'], $dados['valor']);
	                    }
	                    $sql = "UPDATE pmp_item_requisicao SET valor = '{$dados['valor']}' , status='$status' WHERE id = $id";
	                    query($sql);
	                    gravarAtualizacao('pmp_item_requisicao', $id, 'E');
	                } else if (isset($_FILES["formReq"]) && isset($_FILES["formReq"]['tmp_name'])) //campo anexo
	                {
	                    foreach($_FILES["formReq"]['tmp_name'] as $val_id=>$valor)
	                    {
	                        //FORMATO ANEXO: base/id_requisicao/id_documento/arquivo
	                        //$id é o id do item! Precisa do id da requisição e o id do documento
	                        $sql = "SELECT documento, requisicao FROM pmp_item_requisicao WHERE id = $id AND ativo = 'S'";
	                        $rows = query($sql);
	                        if(is_array($rows) && count($rows) == 1)
	                        {
	                            $id_doc = $rows[0]['documento'];
	                            $id_req = $rows[0]['requisicao'];
	                            
	                            $dir = $this->_dir . "$id_req";
	                            if(!is_dir($dir)){
	                                mkdir($dir);
	                            }
	                            $dir = $dir . "/$id_doc";
	                            if(!is_dir($dir)){
	                                mkdir($dir);
	                            }
	                            
	                            $origem = $valor;
	                            $arquivo_novo = $_FILES['formReq']['name'][$val_id];
	                            $destino = $dir . "/$arquivo_novo";
	                            
	                            if(file_exists($destino))
	                            {
	                                $i = 1;
	                                $nome_original = pathinfo($destino, PATHINFO_FILENAME);
	                                while(file_exists($destino)){
	                                    $partes = pathinfo($destino);
	                                    $arquivo_novo = $nome_original . "($i)." . $partes['extension'];
	                                    $destino = $partes['dirname'] . '/' .  $arquivo_novo;
	                                    $i++;
	                                }
	                            }
	                            
	                            if(move_uploaded_file($origem, $destino))
	                            {
	                                $sql = "UPDATE pmp_item_requisicao SET valor = '$arquivo_novo', status = '$status' WHERE id = $id AND ativo = 'S'";
	                                if(query($sql) !== false){
	                                    gravarAtualizacao('pmp_item_requisicao', $id_doc, 'E');
	                                } else {
	                                    addPortalMensagem("Erro ao gravar no banco de dados",'error');
	                                }
	                            }
	                        } else {
	                            addPortalMensagem("Erro ao salvar o anexo",'error');
	                        }
	                    }
	                }
	            }else {
	                addPortalMensagem("Erro no Status (Aprovado, Rejeitado)",'error');
	            }
	        } else if ($aprova == 'N' && $this->requisicaoAberta($id)) {
	            //Salva como rejeitado
	            $mensagem = getParam($_POST, 'formMensagem',[]);
	            if(!empty($mensagem) && isset($mensagem['texto']))
	            {
	                $mensagem = $mensagem['texto'];
	                $sql = "UPDATE pmp_item_requisicao SET status='R', mensagem='$mensagem' WHERE id = $id";
	                query($sql);
	                gravarAtualizacao('pmp_item_requisicao', $id, 'E');
	            }
	        } else if ($aprova == 'S' && $this->requisicaoAberta($id)) {
	            //Salva como aprovado (Finalizado)
	            $sql = "UPDATE pmp_item_requisicao SET status='F' WHERE id = $id";
	            query($sql);
	            gravarAtualizacao('pmp_item_requisicao', $id, 'E');
	        }
	    }
	    
        redireciona();
	}
	
	private function requisicaoAberta($id_item)
	{
	    $ret = false;
	    $sql = "SELECT pmp_requisicao.status AS status FROM pmp_item_requisicao JOIN pmp_requisicao ON pmp_requisicao.id = pmp_item_requisicao.requisicao WHERE pmp_item_requisicao.id = $id_item";
	    $rows = query($sql);
	    if(is_array($rows) && count($rows)==1){
	        $ret = $rows[0]['status'] == 'A' ? true : false;
	    }
	    return $ret;
	}
	
	private function jsConfirmaAprova($titulo){
	    $ret = "
            function confirmaAprova(link,id){
            	if (confirm('$titulo')){
            		setLocation(link+id);
            	}
            }
        ";
	    addPortaljavaScript($ret);
	}
	
	private function jsConfirmaRejeita($titulo){
	    $ret = "
            function confirmaRejeita(link,id){
            	if (confirm('$titulo')){
            		setLocation(link+id);
            	}
            }
        ";
	    addPortaljavaScript($ret);
	}
	
}