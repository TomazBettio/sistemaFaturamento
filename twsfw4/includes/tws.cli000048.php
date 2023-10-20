<?php
function gravarAtualizacao($tabela, $id, $acao){
    $usuario = getUsuario();
    if($acao == 'I'){
        $sql = "update $tabela set user_inclui = '$usuario', data_inclui = CURRENT_TIMESTAMP() where id = $id";
        query($sql);
    }
    if($acao == 'E'){
        $sql = "update $tabela set user_atualiza = '$usuario', data_atualiza = CURRENT_TIMESTAMP() where id = $id";
        query($sql);
    }
}