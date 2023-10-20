<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
set_time_limit(0);

class gerenciar_kanbanflow{
    var $funcoes_publicas = array(
        'index'     => true,
        'schedule'  => true,
        'tokens'    => true,
        'tarefas'   => true,
        'colunas'   => true,
        'eventos'   => true,
        'labels'    => true,
        'dicionario' => true,
        'comentarios' => true,
        'usuarios' => true,
        'tituloBoards' => true,
    );
    
    private $_nomesProibidos = array('Marpa');
    private $_listaUsuarios;
    private $_labelsInt;
    private $_schedule;
    
    public function __construct(){
        $this->_labelsInt = $this->montarListaLabelsInt();
        $this->_schedule = false;
    }
    
    public function index(){
        $ret = '';
        
        $bt_tokens = array(
            'texto' => 'Alterar Tokens',
            'url' => getLink() . 'tokens',
            'tipo' => 'link',
        );
        
        $ret .= formbase01::formBotao($bt_tokens);
        
        $bt_colunas = array(
            'texto' => 'Renovar Dados Colunas',
            'url' => getLink() . 'colunas',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_colunas);
        
        $bt_tarefas = array(
            'texto' => 'Renovar Dados Tarefas',
            'url' => getLink() . 'tarefas',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_tarefas);
        
        $bt_eventos = array(
            'texto' => 'Renovar Dados Eventos',
            'url' => getLink() . 'eventos',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_eventos);
        
        $bt_labels = array(
            'texto' => 'Renovar Labels',
            'url' => getLink() . 'labels',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_labels);
        
        $bt_comentarios = array(
            'texto' => 'Renovar Comentarios',
            'url' => getLink() . 'comentarios',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_comentarios);
        
        $bt_usuarios = array(
            'texto' => 'Renovar Usuários',
            'url' => getLink() . 'usuarios',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_usuarios);
        
        $bt_dicionario = array(
            'texto' => 'Dicionário de Labels',
            'url' => getLink() . 'dicionario',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_dicionario);
        
        $bt_titulos = array(
            'texto' => 'Títulos Boards',
            'url' => getLink() . 'tituloBoards',
            'tipo' => 'link',
        );
        
        $ret .= '<br><br>' . formbase01::formBotao($bt_titulos);
        
        
        
        $ret = addCard(array('titulo' => 'Gerenciar Kanbanflow', 'conteudo' => $ret));
        
        return $ret;
    }
    
    public function schedule(){
        $this->_schedule = true;
        $this->colunas();
        $this->usuarios();
        $this->tarefas();
        $this->eventos();
    }
    
    public function tituloBoards(){
        $sql = "SELECT * FROM kanbanflow_token";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $sql = "truncate kanbanflow_boards";
            query($sql);
            
            $sql_insert = array();
            foreach ($rows as $row){
                $id = $row['id'];
                $chave = $row['token'];
                
                $api = new integra_kanbanflow($chave);
                $dados_board = $api->getDadosBoard();
                
                if(is_array($dados_board) && count($dados_board) > 0){
                    $nome = $dados_board['name'];
                    $sql_insert[] = "($id, '$nome')";
                }
            }
            
            if(is_array($sql_insert) && count($sql_insert) > 0){
                $sql = "insert into kanbanflow_boards values " . implode(', ', $sql_insert);
                query($sql);
            }
        }
        return $this->index();
    }
    
    public function dicionario(){
        $ret = '';
        
        $sql = "select distinct chave from kanbanflow_labels_desc";
        $rows = query($sql);
        
    }
    
    public function tarefas(){
        $this->usuarios();
        $this->_listaUsuarios = $this->montarDicionarioUsuarios();
        
        $tabelas_limpar = array('kanbanflow_tasks');
        $this->limparTabelas($tabelas_limpar);
        
        $dados_incluir = array();
        
        $dados = $this->recuperarDadosComId();
        if(is_array($dados) && count($dados) > 0){
            foreach ($dados as $chave => $tarefas){
                foreach ($tarefas as $tarefa){
                    $descricao = str_replace("'", "''", $tarefa['description']);
                    $nome = str_replace("'", "''", $tarefa['name']);
                    $temp = array(
                        "'{$tarefa['id']}'",
                        "'{$tarefa['_id']}'",
                        "'$chave'",
                        "'{$tarefa['coluna']}'",
                        "'$descricao'",
                        "'$nome'",
                        isset($tarefa['collaborators']) ? $this->montaColaboradoresInsert($chave, $tarefa['collaborators']) : 'null',
                    );
                    $dados_incluir[] = '(' . implode(', ', $temp) . ')';
                }
            }
        }
        
        if(is_array($dados_incluir) && count($dados_incluir) > 0){
            $sql = "insert into kanbanflow_tasks values " . implode(', ', $dados_incluir);
            query($sql);
        }
        
        if(!$this->_schedule){
            addPortalMensagem('Tarefas renovadas com sucesso');
        }
        $this->labels($dados);
        $this->comentarios();
        return $this->index();
    }
    
    public function labels($dados = array()){
        $tabelas = array('kanbanflow_labels');
        $this->limparTabelas($tabelas);
        
        if(count($dados) === 0){
            $dados = $this->recuperarDadosComId(false);
        }
        
        
        $dados_inserir_labels = array();
        foreach ($dados as $tarefas){
            foreach ($tarefas as $tarefa){
                if(isset($tarefa['labels']) && is_array($tarefa['labels']) && count($tarefa['labels']) > 0){
                    $id_tarefa = $tarefa['id'];
                    $tags = $this->recuperarLabelsFromTask($tarefa['labels']);
                    foreach ($tags as $tag){
                        $dados_inserir_labels[] = "('$id_tarefa', '{$tag['chave']}', '{$tag['valor']}')";
                    }
                }
            }
        }
        if(count($dados_inserir_labels) > 0){
            while(count($dados_inserir_labels) > 0){
                $insert_atual = [];
                while(count($insert_atual) < 500 && count($dados_inserir_labels) > 0){
                    $insert_atual[] = array_shift($dados_inserir_labels);
                }
                $sql = "insert into kanbanflow_labels values " . implode(', ', $insert_atual);
                query($sql);
            }
        }
        
        $this->ajustarLabelPl();
        $this->ajustarLabelDinheiro();
        if(!$this->_schedule){
            addPortalMensagem('Labels renovados com sucesso');
        }
        return $this->index();
    }
    
    public function colunas(){
        $tabelas_limpar = array('kanbanflow_columns');
        $this->limparTabelas($tabelas_limpar);
        $chaves = $this->getChaves();
        $dados_insert = array();
        foreach ($chaves as $chave){
            $api = new integra_kanbanflow($chave);
            $dados = $api->getDadosBoard();
            if(isset($dados['columns']) && is_array($dados['columns'])){
                foreach ($dados['columns'] as $index => $coluna){
                    $temp = array(
                        "'{$coluna['uniqueId']}'",
                        "'$chave'",
                        $index,
                    );
                    $dados_insert[] = "(" . implode(', ', $temp) . ")";
                }
            }
        }
        
        if(is_array($dados_insert) && count($dados_insert) > 0){
            $sql = "insert into kanbanflow_columns values " . implode(', ', $dados_insert);
            query($sql);
        }
        
        if(!$this->_schedule){
            addPortalMensagem('Colunas renovadas com sucesso');
        }
        return $this->index();
    }
    
    public function eventos($dados = array()){
        if(count($dados) === 0){
            $dados = $this->recuperarDadosComId();
        }
        
        $tabelas_limpar = array('kanbanflow_events');
        $this->limparTabelas($tabelas_limpar);
        
        $dados_incluir = array();
        foreach ($dados as $chave => $tarefas){
            foreach ($tarefas as $tarefa){
                $eventos = $this->getAllEventosTarefa($tarefa['_id'], $chave);
                $eventos = $this->filtrarEventos($eventos, $tarefa['_id'], $chave);
                foreach($eventos as $evento){
                    $dados_incluir[] = '(' . implode(', ', $evento) . ')';
                }
            }
        }
        
        if(is_array($dados_incluir) && count($dados_incluir) > 0){
            $sql = "insert into kanbanflow_events values " . implode(', ', $dados_incluir);
            query($sql);
        }
        
        if(!$this->_schedule){
            addPortalMensagem('Eventos renovados com sucesso');
        }
        return $this->index();
    }
    
    public function comentarios(){
        $tabelas_limpar = array('kanbanflow_comments');
        $this->limparTabelas($tabelas_limpar);
        
        $sql = "select id, id_kanbanflow as task, token from kanbanflow_tasks order by token";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados_incluir = array();
            $token_antior = '';
            foreach ($rows as $row){
                $token = $row['token'];
                if($token != $token_antior){
                    $api = new integra_kanbanflow($token);
                }
                $comentarios_raw = $api->getComentariosTask($row['task']);
                if(is_array($comentarios_raw) && count($comentarios_raw) > 0){
                    foreach ($comentarios_raw as $comentario){
                        $temp = array(
                            "'{$comentario['_id']}'",
                            "'{$row['id']}'",
                            "'" . str_replace("'", "''", $comentario['text']) . "'",
                            "'" . $this->formatarData($comentario['createdTimestamp']) . "'",
                            "'{$comentario['authorUserId']}'",
                        );
                        $dados_incluir[] = "(" . implode(', ', $temp) . ")";
                    }
                }
                $token_antior = $token;
            }
            
            if(is_array($dados_incluir) && count($dados_incluir) > 0){
                $sql = "insert into kanbanflow_comments values " . implode(', ', $dados_incluir);
                query($sql);
            }
        }
        $this->index();
    }
    
    public function usuarios(){
        $tabelas_limpar = array('kanbanflow_users');
        $this->limparTabelas($tabelas_limpar);
        
        $chaves = $this->getChaves();
        if(is_array($chaves) && count($chaves) > 0){
            $dados_insert = array();
            foreach ($chaves as $chave){
                $api = new integra_kanbanflow($chave);
                $dados_raw = $api->getUsuariosBoard();
                if(is_array($dados_raw) && count($dados_raw) > 0){
                    foreach ($dados_raw as $dado){
                        if(!in_array($dado['fullName'], $this->_nomesProibidos)){
                            $temp = array(
                                "'{$dado['_id']}'",
                                "'$chave'",
                                "'{$dado['fullName']}'",
                            );
                            $dados_insert[] = "(" . implode(', ', $temp) . ")";
                        }
                    }
                }
            }
            if(is_array($dados_insert) && count($dados_insert) > 0){
                $sql = "insert into kanbanflow_users values " . implode(', ', $dados_insert);
                query($sql);
            }
        }
        return $this->index();
    }
    
    private function montarDicionarioUsuarios(){
        $ret = array();
        $sql = "select * from kanbanflow_users";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['token']][$row['id']] = $row['nome'];
            }
        }
        return $ret;
    }
    
    private function getAllEventosTarefa($tarefa, $chave){
        $ret = array();
        $api = new integra_kanbanflow($chave);
        $dados = $api->getEventosTarefa($tarefa, '', time() * 1000);
        $ret = $dados['events'];
        if($dados['eventsLimited']){
            //tem mais de 100 eventos
            $lista_ids = array();
            foreach ($ret as $evento){
                $lista_ids[] = $evento['_id'];
            }
            while($dados['eventsLimited']){
                $evento_pivo = new evento_kanbanflow($dados['events'][99]);
                $dados = $api->getEventosTarefa($tarefa, '', $evento_pivo->getTimeStamp());
                $eventos_novos = array();
                foreach($dados['events'] as $evento){
                    if(!in_array($evento['_id'], $lista_ids)){
                        $lista_ids[] = $evento['_id'];
                        $eventos_novos[] = $evento;
                    }
                }
                $ret = array_merge($ret, $eventos_novos);
            }
        }
        return $ret;
    }
    
    private function filtrarEventos($eventos, $id_tarefa, $chave){
        $ret = array();
        $id_usados = array();
        foreach ($eventos as $evento){
            if(!in_array($evento['_id'], $id_usados)){
                $id_usados[] = $evento['_id'];
                
                $tipo_evento = $evento['detailedEvents'][0]['eventType'];
                if($tipo_evento === 'taskChanged'){
                    $propriedades_alteradas = $evento['detailedEvents'][0]['changedProperties'] ?? array();
                    if(count($propriedades_alteradas) > 0){
                        foreach ($propriedades_alteradas as $campo_alterado){
                            if($campo_alterado['property'] === 'columnId'){
                                $temp = array(
                                    "'{$evento['_id']}'",
                                    "'$id_tarefa'",
                                    "'$chave'",
                                    "'taskChanged'",
                                    "'" . $campo_alterado['property'] . "'",
                                    "'" . $campo_alterado['oldValue'] . "'",
                                    "'" . $campo_alterado['newValue'] . "'",
                                    "'" . $this->formatarData($evento['timestamp']) . "'",
                                );
                                
                                $ret[] = $temp;
                            }
                            elseif($campo_alterado['property'] === 'labels'){
                                $temp = array(
                                    "'{$evento['_id']}'",
                                    "'$id_tarefa'",
                                    "'$chave'",
                                    "'taskChanged'",
                                    "'" . $campo_alterado['property'] . "'",
                                    "'" . $this->formatarLabelsEventos($campo_alterado['oldValue']) . "'",
                                    "'" . $this->formatarLabelsEventos($campo_alterado['newValue']) . "'",
                                    "'" . $this->formatarData($evento['timestamp']) . "'",
                                );
                                
                                $ret[] = $temp;
                            }
                        }
                    }
                }
                
                elseif($tipo_evento === 'taskCreated'){
                    $temp = array(
                        "'{$evento['_id']}'",
                        "'$id_tarefa'",
                        "'$chave'",
                        "'taskCreated'",
                        "null",
                        "null",
                        "null",
                        "'" . $this->formatarData($evento['timestamp']) . "'",
                    );
                    $ret[] = $temp;
                }
            }
        }
        return $ret;
    }
    
    private function formatarLabelsEventos($labels){
        $ret = '';
        if(is_array($labels) && count($labels) > 0){
            $lista_raw = array_column($labels, 'name');
            if(is_array($lista_raw) && count($lista_raw) > 0){
                $lista_temp = $this->recuperarLabelsFromTask($lista_raw);
                $lista = array();
                foreach ($lista_temp as $t){
                    $lista[] = $t['chave'] . ':' . $t['valor'];
                }
                $ret = implode(';', $lista);
            }
        }
        return $ret;
    }
    
    private function formatarData($data_original){
        $temp = new DateTime($data_original, new DateTimeZone('America/Sao_Paulo'));
        return $temp->format('Y-m-d H:i:s');
    }
    
    private function recuperarDadosComId($insert = true){
        $ret = array();
        $chaves = $this->getChaves();
        $contador = 1;
        $dicionario = $this->montarDicionarioTarefaId();
        foreach ($chaves as $chave){
            $api = new integra_kanbanflow($chave);
            $colunas = $api->getTodasTarefas();
            if(is_array($colunas) && count($colunas) > 0){
                unset($colunas[0]); //tira a coluna de legenda
                $ret[$chave] = array();
                foreach ($colunas as $coluna){
                    if(isset($coluna['tasks']) && is_array($coluna['tasks']) && count($coluna['tasks'])){
                        $id_coluna = $coluna['columnId'];
                        foreach ($coluna['tasks'] as $tarefa){
                            $temp = $tarefa;
                            $temp['id'] = $insert ? $contador : ($dicionario[$tarefa['_id']][$chave] ?? '');
                            $temp['coluna'] = $id_coluna;
                            if(!empty($temp['id'])){
                                $ret[$chave][] = $temp;
                                $contador++;
                            }
                        }
                    }
                }
            }
            else{
                if(!$this->_schedule){
                    addPortalMensagem('Problemas com os dados do token ' . $chave, 'error');
                }
                log::gravaLog('gerenciar_kanbanflow_erros', 'erros ao recuperar tarefas do token ' . $chave);
            }
        }
        return $ret;
    }
    
    private function montarDicionarioTarefaId(){
        $ret = array();
        $sql = "select id, id_kanbanflow, token from kanbanflow_tasks";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['id_kanbanflow']][$row['token']] = $row['id'];
            }
        }
        return $ret;
    }
    
    private function recuperarLabelsFromTask($labels){
        $ret = array();
        if(is_array($labels) && count($labels) > 0){
            foreach ($labels as $tag){
                if(is_array($tag)){
                    $temp = explode(':', str_replace(' ', '', $tag['name']));
                }
                else{
                    $temp = explode(':', str_replace(' ', '', $tag));
                }
                if(count($temp) === 2){
                    $chave = strtoupper($temp[0]);
                    $valor = $temp[1];
                    if(in_array($chave, $this->_labelsInt)){
                        $valor = intval(preg_replace('/[^0-9]/', '', $valor));
                    }
                    $ret[] = array('chave' => $chave, 'valor' => $valor);
                }
            }
        }
        return $ret;
    }
    
    private function montaColaboradoresInsert($chave, $colaboradores){
        $ret = '';
        if(is_array($colaboradores) && count($colaboradores) > 0){
            foreach ($colaboradores as $colaborador){
                if(empty($ret) && isset($this->_listaUsuarios[$chave][ $colaborador['userId']])){
                    $ret = "'" . $this->_listaUsuarios[$chave][ $colaborador['userId']] . "'";
                }
            }
        }
        if(empty($ret)){
            $ret = 'null';
        }
        return $ret;
    }
    
    private function limparTabelas($tabelas){
        foreach ($tabelas as $tabela){
            $sql = "TRUNCATE TABLE $tabela";
            query($sql);
        }
    }
    
    private function getChaves(){
        $ret = array();
        $sql = "select token from kanbanflow_token";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['token'];
            }
        }
        return $ret;
    }
    
    private function montarListaLabelsInt(){
        $ret = array();
        $sql = "select chave, valor from kanbanflow_labels_desc";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados_organizados = array();
            foreach ($rows as $row){
                $dados_organizados[$row['chave']][] = $row['valor'];
            }
            foreach ($dados_organizados as $chave => $valores){
                $int = true;
                foreach ($valores as $valor){
                    if($int){
                        $copia = preg_replace('/[^0-9]/', '', $valor);
                        if($copia !== $valor){
                            $int = false;
                        }
                        if(strpos($valor, '0') === 0){
                            $int = false;
                        }
                    }
                }
                if($int){
                    $ret[] = $chave;
                }
            }
        }
        return $ret;
    }
    
    private function ajustarLabelPl(){
        $sql = "select * from kanbanflow_labels where chave = 'PL'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $valor = $row['valor'];
                if(strpos($valor, '0') === 0){
                    while(strpos($valor, '0') === 0){
                        $valor = substr($valor, 1);
                    }
                    $sql = "update kanbanflow_labels set valor = '$valor' where task = {$row['task']} and chave = '{$row['chave']}'";
                    query($sql);
                }
            }
        }
    }
    
    private function ajustarLabelDinheiro(){
        $sql = "select * from kanbanflow_labels where chave in ('$', 'CA$')";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $valor_refinado = preg_replace('/[^0-9]/', '', $row['valor']);
                if($valor_refinado !== $row['valor']){
                    $sql = "update kanbanflow_labels set valor = '$valor_refinado' where task = {$row['task']} and chave = '{$row['chave']}'";
                    query($sql);
                }
            }
        }
    }
}