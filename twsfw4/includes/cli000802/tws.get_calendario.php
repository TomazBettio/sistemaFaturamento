<?php


class get_calendario {
    var $funcoes_publicas = array(
        'index'             => true,
        'ajax'              => true,
    );

    private $_tabela;
    private $_id;

    // public function ajax(){
    //   $op = getOperacao();

    //   $meses = array(
    //     'Jan' => '01',
    //     'Feb' => '02',
    //     'Mar' => '03',
    //     'Apr' => '04',
    //     'May' => '05',
    //     'Jun' => '06',
    //     'Jul' => '07',
    //     'Aug' => '08',
    //     'Sep' => '09',
    //     'Oct' => '10',
    //     'Nov' => '11',
    //     'Dec' => '12'
    //   );

    //   if($op == 'eventos') {
    //     $sql = "SELECT * from rh_eventos WHERE ativo = 'S'";
    //     $rows = query($sql);

    //     $eventos = [];
    //     foreach($rows as $row) {
            
    //         // $eventos .= '{
    //         //           title          : \''.$row['nome'].'\',
    //         //           start          : '.$row['data'].',
    //         //           backgroundColor: \'#f56954\', //red
    //         //           borderColor    : \'#f56954\', //red
    //         //           allDay         : true
    //         //         },';

    //         $temp = [];
    //         $temp['id'] = $row['id'];
    //         $temp['title'] = $row['nome'];
    //         $temp['start'] = $row['data'];
    //         // $temp['tipo'] = $row['tipo'];
    //         // $temp['onde'] = $row['onde'];
    //         // $temp['entidade_tipo'] = $row['entidade_tipo'];
    //         // $temp['entidade_id'] = $row['entidade_id'];
    //         // $temp['detalhes'] = $row['detalhes'];
    //         // $temp['notas'] = $row['notas'];
    //         $temp['backgroundColor'] = $row['cor_fundo'] ??  '#f56954';
    //         $temp['borderColor'] = $row['cor_borda'] ?? 'f56954';
    //         $temp['allDay'] = true;

    //         $eventos[] = $temp;
    //     }
    //     // $eventos .= ']';

    //     return json_encode($eventos);
    //   } else if($op == 'mover') {
    //     var_dump($_GET['data']);

    //     $datas = explode(' ', $_GET['data']);
    //     $id = $_GET['id'];
    //     $mes = $meses[$datas[1]];
    //     $dia = $datas[2];
    //     $ano = $datas[3];
    //     $data = $ano . '-' . $mes . '-' . $dia . ' 00:00:00';
        
    //     $sql = "UPDATE rh_eventos SET data = '$data' WHERE id = $id";
    //     query($sql);

    //   } 
    //   else if($op == 'incluir') {
    //     $permissao = false;

    //     if($permissao) {

    //       $titulo = $_GET['titulo'];
    //       $datas = explode(' ', $_GET['data']);
    //       $mes = $meses[$datas[1]];
    //       $dia = $datas[2];
    //       $ano = $datas[3];
    //       $data = $ano . '-' . $mes . '-' . $dia . ' 00:00:00';

    //       $param = [];
    //       $param['data'] = $data;
    //       $param['nome'] = $titulo;
    //       $param['cor_fundo'] = $_GET['cor_fundo'];
    //       $param['cor_borda'] = $_GET['cor_borda'];
    //       $param['ativo'] = 'S';
          
    //       $sql = montaSQL($param, 'rh_eventos');
    //       query($sql);

    //       return 'true';
    //     }
    //     else {
    //       return 'false';
    //     }
    //   }
    // }

    public function index() {
        $html = '<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Calendar</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Calendar</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <div class="sticky-top mb-3">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Draggable Events</h4>
                </div>
                <div class="card-body">
                  <!-- the events -->
                  <div id="external-events">
                    <div class="external-event bg-success">Lunch</div>
                    <div class="external-event bg-warning">Go home</div>
                    <div class="external-event bg-info">Do homework</div>
                    <div class="external-event bg-primary">Work on UI design</div>
                    <div class="external-event bg-danger">Sleep tight</div>
                    <div class="checkbox">
                      <label for="drop-remove">
                        <input type="checkbox" id="drop-remove">
                        remove after drop
                      </label>
                    </div>
                  </div>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Create Event</h3>
                </div>
                <div class="card-body">
                  <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                    <ul class="fc-color-picker" id="color-chooser">
                      <li><a class="text-primary" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-warning" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-success" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-danger" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-muted" href="#"><i class="fas fa-square"></i></a></li>
                    </ul>
                  </div>
                  <!-- /btn-group -->
                  <div class="input-group">
                    <input id="new-event" type="text" class="form-control" placeholder="Event Title">

                    <div class="input-group-append">
                      <button id="add-new-event" type="button" class="btn btn-primary">Add</button>
                    </div>
                    <!-- /btn-group -->
                  </div>
                  <!-- /input-group -->
                </div>
              </div>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card card-primary">
              <div class="card-body p-0">
                <!-- THE CALENDAR -->
                <div id="calendar"></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>';

        return $html;
    }

    
}