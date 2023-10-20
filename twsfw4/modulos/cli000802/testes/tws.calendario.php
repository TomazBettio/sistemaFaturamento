<?php


class calendario {
    private $_tabela;
    private $_id;

    var $funcoes_publicas = array(
        'index'             => true,
        'ajax'              => true,
    );

    public function ajax(){
      $op = getOperacao();

      $meses = array(
        'Jan' => '01',
        'Feb' => '02',
        'Mar' => '03',
        'Apr' => '04',
        'May' => '05',
        'Jun' => '06',
        'Jul' => '07',
        'Aug' => '08',
        'Sep' => '09',
        'Oct' => '10',
        'Nov' => '11',
        'Dec' => '12'
      );

      if($op == 'eventos') {
        $sql = "SELECT * from rh_eventos WHERE ativo = 'S'";
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
            $temp['id'] = $row['id'];
            $temp['title'] = $row['nome'];
            $temp['start'] = $row['data'];
            // $temp['tipo'] = $row['tipo'];
            // $temp['onde'] = $row['onde'];
            // $temp['entidade_tipo'] = $row['entidade_tipo'];
            // $temp['entidade_id'] = $row['entidade_id'];
            // $temp['detalhes'] = $row['detalhes'];
            // $temp['notas'] = $row['notas'];
            $temp['backgroundColor'] = $row['cor_fundo'] ??  '#f56954';
            $temp['borderColor'] = $row['cor_borda'] ?? 'f56954';
            $temp['allDay'] = true;

            $eventos[] = $temp;
        }
        // $eventos .= ']';

        return json_encode($eventos);
      } else if($op == 'mover') {
        var_dump($_GET['data']);

        $datas = explode(' ', $_GET['data']);
        $id = $_GET['id'];
        $mes = $meses[$datas[1]];
        $dia = $datas[2];
        $ano = $datas[3];
        $data = $ano . '-' . $mes . '-' . $dia . ' 00:00:00';
        
        $sql = "UPDATE rh_eventos SET data = '$data' WHERE id = $id";
        query($sql);

      } 
      else if($op == 'incluir') {
        $permissao = false;

        if($permissao) {

          $titulo = $_GET['titulo'];
          $datas = explode(' ', $_GET['data']);
          $mes = $meses[$datas[1]];
          $dia = $datas[2];
          $ano = $datas[3];
          $data = $ano . '-' . $mes . '-' . $dia . ' 00:00:00';

          $param = [];
          $param['data'] = $data;
          $param['nome'] = $titulo;
          $param['cor_fundo'] = $_GET['cor_fundo'];
          $param['cor_borda'] = $_GET['cor_borda'];
          $param['ativo'] = 'S';
          
          $sql = montaSQL($param, 'rh_eventos');
          query($sql);

          return 'true';
        }
        else {
          return 'false';
        }
      }
    }

    public function index() {
        $this->insereJS();

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

    private function insereJS() {
        addPortaljavaScript("$(function () {

            /* initialize the external events
             -----------------------------------------------------------------*/
            function ini_events(ele) {
              ele.each(function () {
        
                // create an Event Object (https://fullcalendar.io/docs/event-object)
                // it doesn't need to have a start or end
                var eventObject = {
                  title: $.trim($(this).text()) // use the element's text as the event title
                }
        
                // store the Event Object in the DOM element so we can get to it later
                $(this).data('eventObject', eventObject)
        
                // make the event draggable using jQuery UI
                $(this).draggable({
                  zIndex        : 1070,
                  revert        : true, // will cause the event to go back to its
                  revertDuration: 0  //  original position after the drag
                })
        
              })
            }
        
            ini_events($('#external-events div.external-event'))
        
            /* initialize the calendar
             -----------------------------------------------------------------*/
            //Date for the calendar events (dummy data)
            var date = new Date()
            var d    = date.getDate(),
                m    = date.getMonth(),
                y    = date.getFullYear()
        
            var Calendar = FullCalendar.Calendar;
            var Draggable = FullCalendar.Draggable;
        
            var containerEl = document.getElementById('external-events');
            var checkbox = document.getElementById('drop-remove');
            var calendarEl = document.getElementById('calendar');
        
            // initialize the external events
            // -----------------------------------------------------------------
        
            new Draggable(containerEl, {
              itemSelector: '.external-event',
              eventData: function(eventEl) {
                return {
                  title: eventEl.innerText,
                  backgroundColor: window.getComputedStyle( eventEl ,null).getPropertyValue('background-color'),
                  borderColor: window.getComputedStyle( eventEl ,null).getPropertyValue('background-color'),
                  textColor: window.getComputedStyle( eventEl ,null).getPropertyValue('color'),
                };
              }
            });
        
            var calendar = new Calendar(calendarEl, {
                locale: 'pt-br',
              headerToolbar: {
                left  : 'prev,next today',
                center: 'title',
                right : 'dayGridMonth,timeGridWeek,timeGridDay'
              },
              themeSystem: 'bootstrap',
              //Random default events
              // events: '/var/www/twsfw4_dev/includes/tws.get_eventos_fullCalendar.php',
              events: '" . getLinkAjax('eventos') . "',
            //   events: [
            //     {
            //       title          : 'All Day Event',
            //       start          : new Date(y, m, 1),
            //       backgroundColor: '#f56954', //red
            //       borderColor    : '#f56954', //red
            //       allDay         : true
            //     },
            //     {
            //       title          : 'Long Event',
            //       start          : new Date(y, m, d - 5),
            //       end            : new Date(y, m, d - 2),
            //       backgroundColor: '#f39c12', //yellow
            //       borderColor    : '#f39c12' //yellow
            //     },
            //     {
            //       title          : 'Meeting',
            //       start          : new Date(y, m, d, 10, 30),
            //       allDay         : false,
            //       backgroundColor: '#0073b7', //Blue
            //       borderColor    : '#0073b7' //Blue
            //     },
            //     {
            //       title          : 'Lunch',
            //       start          : new Date(y, m, d, 12, 0),
            //       end            : new Date(y, m, d, 14, 0),
            //       allDay         : false,
            //       backgroundColor: '#00c0ef', //Info (aqua)
            //       borderColor    : '#00c0ef' //Info (aqua)
            //     },
            //     {
            //       title          : 'Birthday Party',
            //       start          : new Date(y, m, d + 1, 19, 0),
            //       end            : new Date(y, m, d + 1, 22, 30),
            //       allDay         : false,
            //       backgroundColor: '#00a65a', //Success (green)
            //       borderColor    : '#00a65a' //Success (green)
            //     },
            //     {
            //       title          : 'Click for Google',
            //       start          : new Date(y, m, 28),
            //       end            : new Date(y, m, 29),
            //       url            : 'https://www.google.com/',
            //       backgroundColor: '#3c8dbc', //Primary (light-blue)
            //       borderColor    : '#3c8dbc' //Primary (light-blue)
            //     }
            //   ],
              editable  : true,
              droppable : true, // this allows things to be dropped onto the calendar !!!
              eventDrop : function(info) {
                var link = '".getLinkAjax('mover'). '&id=' ."';
                $.get(link + info.event.id + '&data=' + info.event.start, function(retorno){
                  document.getElementById('adwsadasdsda').innerHTML = retorno;
                });
              },
              eventReceive : function(info) {
                
                var link = '".getLinkAjax('incluir') ."';
                $.get(link + '&data=' + info.event.start + '&titulo=' + info.event.title + '&cor_fundo=' + info.event.backgroundColor + '&cor_borda=' + info.event.borderColor, function(retorno){
                  if(retorno == 'false') {
                    info.event.remove();
                  }
                });
              }
            });
        
            calendar.render();
            // $('#calendar').fullCalendar()
        
            /* ADDING EVENTS */
            var currColor = '#3c8dbc' //Red by default
            // Color chooser button
            $('#color-chooser > li > a').click(function (e) {
              e.preventDefault()
              // Save color
              currColor = $(this).css('color')
              // Add color effect to button
              $('#add-new-event').css({
                'background-color': currColor,
                'border-color'    : currColor
              })
            })
            $('#add-new-event').click(function (e) {
              e.preventDefault()
              // Get value and make sure it is not null
              var val = $('#new-event').val()
              if (val.length == 0) {
                return
              }
        
              // Create events
              var event = $('<div />')
              event.css({
                'background-color': currColor,
                'border-color'    : currColor,
                'color'           : '#fff'
              }).addClass('external-event')
              event.text(val)
              $('#external-events').prepend(event)
        
              // Add draggable funtionality
              ini_events(event)
        
              // Remove event from text input
              $('#new-event').val('')
            })
          })");
    }
}