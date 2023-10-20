<?php

$sql = "SELECT * from rh_eventos";
$rows = query($sql);

$eventos = [];
foreach($rows as $row) {
    
    // $eventos .= '{
    //           title          : \''.$row['nome'].'\',
    //           start          : '.$row['data'].',
    //           backgroundColor: \'#f56954\', //red
    //           borderColor    : \'#f56954\', //red
    //           allDay         : true
    //         },';

    $temp = [];
    // $temp['id'] = $row['id'];
    $temp['title'] = $row['nome'];
    $temp['start'] = $row['data'];
    // $temp['tipo'] = $row['tipo'];
    // $temp['onde'] = $row['onde'];
    // $temp['entidade_tipo'] = $row['entidade_tipo'];
    // $temp['entidade_id'] = $row['entidade_id'];
    // $temp['detalhes'] = $row['detalhes'];
    // $temp['notas'] = $row['notas'];
    $temp['backgroundColor'] = '#f56954';
    $temp['borderColor'] = 'f56954';
    $temp['allDay'] = true;

    $eventos[] = $temp;
}
// $eventos .= ']';

echo json_encode($eventos);