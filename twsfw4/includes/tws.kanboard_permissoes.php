<?php
class kanboard_permissoes{
    static function verificarPermissaoProjeto($projeto, $usuario){
        $ret = true;
        if(kanboard_permissoes::checarExistenciaPermissao('projeto', 'visualizar', $projeto)){
            $sql = "select temp1.id_entidade, temp2.user from (select * from kanboard_permissoes where entidade = 'projeto' and tipo = 'visualizar' and id_entidade = $projeto) as temp1
                            join
                        kanboard_grupos on (temp1.grupo = kanboard_grupos.id) left join kanboard_membros on (kanboard_grupos.id = kanboard_membros.grupo)
                        join (select * from sys001 where user = '$usuario') as temp2 on (kanboard_membros.usuario = temp2.id)";
            $rows = query($sql);
            if(is_array($rows) && count($rows) == 0){
                $ret = false;
            }
        }
        
        return $ret;
    }
    
    static function getColunasPermitidas($projeto, $usuario){
        $ret = array();
        /*
        $sql = "select temp1.id_entidade, temp3.user from (select * from kanboard_permissoes where entidade = 'coluna' and tipo = 'visualizar' and id_entidade in (select id from kanboard_colunas where projeto = $projeto)) as temp1
            left join 
        kanboard_grupos on (temp1.grupo = kanboard_grupos.id) LEFT JOIN (
            	SELECT kanboard_membros.grupo
            		,temp2.*
            	FROM kanboard_membros
            	JOIN (
            		SELECT *
            		FROM sys001
            		WHERE user = '$usuario'
            		) AS temp2 ON (kanboard_membros.usuario = temp2.id)
            	) temp3 ON (kanboard_grupos.id = temp3.grupo)
        ";
        $rows = query($sql);
        if(is_array($rows)){
            if(count($rows) > 0){
                //se pelo menos uma das colunas tem permissões cadastradas
                foreach ($rows as $row){
                    if($row['user'] == $usuario){
                        $ret[] = $row['id_entidade'];
                    }
                }
            }
            else{
                //cc
                $sql = "select id from kanboard_colunas where projeto = $projeto";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    foreach ($rows as $row){
                        $ret[] = $row['id'];
                    }
                }
            }
        }*/
        /*
        $sql = "select id from kanboard_colunas where projeto = $projeto";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            if(kanboard_permissoes::checarStatusPermissao('visualizar', 'coluna', $projeto, $usuario) == 'livre'){
                foreach ($rows as $row){
                    $ret[] = $row['id'];
                }
            }
            else{
                foreach ($rows as $row){
                    if(kanboard_permissoes::checarPermissaoEspecifica('visualizar', 'coluna', $row['id'], $usuario)){
                        $ret[] = $row['id'];
                    }
                }
            }
        }
        */
        $sql = "select status from kanboard_permissoes_status where entidade = 'coluna' and id_entidade = $projeto and tipo = 'visualizar' and grupo in (select grupo from kanboard_membros where usuario in (select id from sys001 where user = '$usuario'))";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $status = array();
            foreach ($rows as $row){
                $status[] = $row['status'];
            }
            if(in_array('livre', $status)){
                $sql = "select id from kanboard_colunas where projeto = $projeto";
            }
            else{
                $sql = "select id_entidade as id from kanboard_permissoes where entidade = 'coluna' and id_entidade in (select id from kanboard_colunas where projeto = $projeto) and tipo = 'visualizar' and grupo in (select grupo from kanboard_membros where usuario in (select id from sys001 where user = '$usuario'))";
            }
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $ret[] = $row['id'];
                }
            }
        }
        return $ret;
    }
    
    static function getRaisPermitidas($projeto, $usuario){
        $ret = array();
        $sql = "select temp1.id_entidade, temp3.user from (select * from kanboard_permissoes where entidade = 'raia' and tipo = 'visualizar' and id_entidade in (select id from kanboard_raia where projeto = $projeto)) as temp1
            left join
        kanboard_grupos on (temp1.grupo = kanboard_grupos.id) LEFT JOIN (
            	SELECT kanboard_membros.grupo
            		,temp2.*
            	FROM kanboard_membros
            	JOIN (
            		SELECT *
            		FROM sys001
            		WHERE user = '$usuario'
            		) AS temp2 ON (kanboard_membros.usuario = temp2.id)
            	) temp3 ON (kanboard_grupos.id = temp3.grupo)
        ";
        $rows = query($sql);
        if(is_array($rows)){
            if(count($rows) > 0){
                //se pelo menos uma das colunas tem permissões cadastradas
                foreach ($rows as $row){
                    if($row['user'] == $usuario){
                        $ret[] = $row['id_entidade'];
                    }
                }
            }
            else{
                //cc
                $sql = "select id from kanboard_raia where projeto = $projeto";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    foreach ($rows as $row){
                        $ret[] = $row['id'];
                    }
                }
            }
        }
        return $ret;
    }
    
    static function getListaPossiveisUsuarios($tarefa, $projeto = '', $coluna = '', $raia = ''){
        //verificar se existem permissoes para o projeto
        //verificar se existem permissoes para a coluna
        //verificar se existem permissoes para a raia
        $ret = array();
        
        if(empty($coluna)){
            $coluna = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'coluna');
        }
        if(empty($raia)){
            $raia = kanboard_tarefa_unitaria::getCampoTarefa($tarefa, 'raia');
        }
        if(empty($projeto)){
            $projeto = kanboard_cabecalho_coluna::getCampoColuna($coluna, 'projeto');
        }
        
        $permissoes_projeto = kanboard_permissoes::getListaPossiveisUsuariosByProjeto($projeto);
        $permissoes_coluna  = kanboard_permissoes::getListaPossiveisUsuariosByColuna($projeto, $coluna);
        $permissoes_raia    = kanboard_permissoes::getListaPossiveisUsuariosByRaia($projeto, $raia);
        
        $ret = array_intersect($permissoes_projeto, $permissoes_coluna, $permissoes_raia);
        
        return $ret;
    }
    
    static function getListaPossiveisUsuariosByProjeto($projeto){
        $ret = array();
        $sql = "select distinct grupo from kanboard_permissoes where entidade = 'projeto' and tipo = 'visualizar' and id_entidade = $projeto";
        $rows = query($sql);
        if(is_array($rows)){
            if(count($rows) > 0){
                $grupos = array();
                foreach ($rows as $row){
                    $grupos[] = $row['grupo'];
                }
                $ret = kanboard_permissoes::getIdMembrosGrupos($grupos);
            }
            else{
                $sql = "select id from sys001";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    foreach ($rows as $row){
                        $ret[] = $row['id'];
                    }
                }
            }
        }
        return $ret;
    }
    
    static function getListaPossiveisUsuariosByColuna($projeto, $coluna){
        $ret = array();
        $sql = "select grupo from kanboard_permissoes_status where entidade = 'coluna' and id_entidade = $projeto and status = 'livre' and tipo = 'visualizar' 
union select grupo from kanboard_permissoes where entidade = 'coluna' and tipo = 'visualizar' and id_entidade = $coluna";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $grupos = array();
            foreach ($rows as $row){
                $grupos[] = $row['grupo'];
            }
            $ret = kanboard_permissoes::getIdMembrosGrupos($grupos);
        }
        return $ret;
    }
    
    static function getListaPossiveisUsuariosByRaia($projeto, $raia){
        $ret = array();
        $sql = "select grupo from kanboard_permissoes_status where entidade = 'raia' and id_entidade = $projeto and status = 'livre' and tipo = 'visualizar'
union select grupo from kanboard_permissoes where entidade = 'raia' and tipo = 'visualizar' and id_entidade = $raia";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $grupos = array();
            foreach ($rows as $row){
                $grupos[] = $row['grupo'];
            }
            $ret = kanboard_permissoes::getIdMembrosGrupos($grupos);
        }
        return $ret;
    }
    
    static function getIdMembrosGrupos($grupos){
        $ret = array();
        if(is_array($grupos) && count($grupos) > 0){
            $sql = "select distinct usuario from kanboard_membros where grupo in (" . implode(', ', $grupos) . ')';
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0 ){
                foreach ($rows as $row){
                    $ret[] = $row['usuario'];
                }
            }
        }
        return $ret;
    }
    
    static function checarPermissao($tipo, $projeto, $coluna, $raia, $usuario){
        $ret = true;
        if($projeto != '' && kanboard_permissoes::checarExistenciaPermissao('projeto', $tipo, $projeto)){
            $sql = "select temp1.id_entidade, temp2.user from (select * from kanboard_permissoes where entidade = 'projeto' and tipo = '$tipo' and id_entidade = $projeto) as temp1
                        join
                    kanboard_grupos on (temp1.grupo = kanboard_grupos.id) left join kanboard_membros on (kanboard_grupos.id = kanboard_membros.grupo)
                    join (select * from sys001 where user = '$usuario') as temp2 on (kanboard_membros.usuario = temp2.id)";
            $rows = query($sql);
            if(is_array($rows) && count($rows) == 0){
                $ret = false;
            }
        }
        if($ret && $coluna != ''){
            $projeto = $projeto != '' ? $projeto : kanboard_cabecalho_coluna::getCampoColuna($coluna, 'projeto');
            $status = kanboard_permissoes::checarStatusPermissao($tipo, 'coluna', $projeto, $usuario);
            if($status != 'livre'){
                $ret = kanboard_permissoes::checarPermissaoEspecifica($tipo, 'coluna', $coluna, $usuario);
            }
        }
        if($ret && $raia != ''){
            $projeto = $projeto != '' ? $projeto : kanboard_raia::getCampoRaia($raia, 'projeto');
            $status = kanboard_permissoes::checarStatusPermissao($tipo, 'raia', $projeto, $usuario);
            if($status != 'livre'){
                $ret = kanboard_permissoes::checarPermissaoEspecifica($tipo, 'raia', $raia, $usuario);
            }
        }
        return $ret;
    }
    
    static function checarStatusPermissao($tipo, $entidade, $id_entidade, $usuario){
        $ret = '';
        $sql = "select ps.status from kanboard_permissoes_status as ps join kanboard_membros as km on (ps.grupo = km.grupo) join sys001 on (km.usuario = sys001.id) where user = '$usuario' and ps.tipo = '$tipo'
and ps.id_entidade = $id_entidade and ps.entidade = '$entidade'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['status'];
        }
        return $ret;
    }
    
    static function checarPermissaoEspecifica($tipo, $entidade, $id_entidade, $usuario){
        $ret = false;
        $sql = "select * from kanboard_permissoes where grupo in (select grupo from kanboard_membros join sys001 on (kanboard_membros.usuario = sys001.id) where user = '$usuario') 
and entidade = '$entidade' and tipo = '$tipo' and id_entidade = $id_entidade";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = true;
        }
        return $ret;
    }
    
    static function checarPermissaoMovimentacao($origem, $destino, $tipo, $usuario){
        $ret = false;
        $status_origem = kanboard_permissoes::verificarStatusEntidade($origem, $tipo);
        $status_destino = kanboard_permissoes::verificarStatusEntidade($destino, $tipo);
        
        if($status_origem == 'livre' && $status_destino == 'livre'){
            //as duas colunas são livres
            $ret = true;
        }
        elseif ($status_origem == 'saida' && $status_destino == 'entrada'){
            //a coluna origem é de saida e a destino é entrada
            $ret = true;
        }
        elseif ($status_origem == 'saida' && $status_destino == 'livre'){
            //a coluna origem é de saida e a destino é livre
            $ret = true;
        }
        elseif ($status_origem == 'livre' && $status_destino == 'entrada'){
            //a coluna origem é de livre e a destino é entrada
            $ret = true;
        }
        //saida > livre > entrada
        //ex: saida = 3, livre = 2, entrada = 1, especifico = 0
        //if origem > destino && origem != 0 && destino != 0
        elseif ($status_origem == 'especifico' || $status_destino == 'especifico'){
            $ret = kanboard_permissoes::verificarMovimentacaoEspecifica($origem, $destino, $tipo, $usuario);
        }
        return $ret;
    }
    
    static function verificarStatusEntidade($id_entidade, $tipo){
        $ret = 'livre';
        $sql = "select status from kanboard_permissoes_movimentacao_status where entidade = '$tipo' and id_entidade = $id_entidade";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['status'];
        }
        return $ret;
    }
    
    static function verificarMovimentacaoEspecifica($origem, $destino, $tipo, $usuario){
        $ret = false;
        $sql = "select * from kanboard_permissoes_movimentacao where tipo = '$tipo' and origem = $origem and destino = $destino and grupo in 
        (select grupo from kanboard_membros join sys001 on (kanboard_membros.usuario = sys001.id) where sys001.user = '$usuario')";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = true;
        }
        return $ret;
    }
    
    static function getCampoPermissao($id, $campo){
        $ret = '';
        $sql = "select $campo from kanboard_permissoes where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0][$campo];
        }
        return $ret;
    }
    
    static function checarExistenciaPermissao($entidade, $tipo, $id_entidade){
        $ret = false;
        $sql = "SELECT *
            	FROM kanboard_permissoes
            	WHERE entidade = '$entidade'
            		AND tipo = '$tipo'
            		AND id_entidade = $id_entidade";
        $rows = query($sql);
        $ret = (is_array($rows) && count($rows) > 0);
        return $ret;
    }
    
    static function checarPermissaoSentidoMovimentacao($coluna_origem, $coluna_destino){
        $ret = true;
        $sql = "select sentido_movimentacao from kanboard_projetos where id in (select projeto from kanboard_colunas where id = $coluna_origem)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $sentido = $rows[0]['sentido_movimentacao'];
            if($sentido !== ''){
                $pos_coluna_origem  = kanboard_cabecalho_coluna::getCampoColuna($coluna_origem , 'posicao');
                $pos_coluna_destino = kanboard_cabecalho_coluna::getCampoColuna($coluna_destino, 'posicao');
                if($sentido == 'E'){
                    $ret = ($pos_coluna_origem >= $pos_coluna_destino);
                }
                elseif ($sentido == 'D'){
                    $ret = ($pos_coluna_origem <= $pos_coluna_destino);
                }
            }
        }
        return $ret;
    }
}