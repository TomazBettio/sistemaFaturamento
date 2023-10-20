<?php

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);


class calendario02 {
    private $_tabela;
    private $_id;
    private $_id_codificado;

    // Link para retorno
    private $_link_retorno;
    
    //array de tarefas 
    private $_tarefas=[];
    
    private $_sql;
    
    protected $_tabela_evento;
    //Tabelas: calendario_eventos contém os eventos
    //calendario_eventos_externos contém os eventos arrastáveis

    var $funcoes_publicas = array(
        'index'             => true,
    );

    
    
    function __construct() {/*
        $this->_tarefas = array(
            array(
                'titulo' => 'TAREFA TESTE 1',
                'data'   => '20230509'
            ),
            array(
                'titulo' => 'TAREFA TESTE 2',
                'data'   => '20230513'
            ),
            array(
                'titulo' => 'TAREFA TESTE 3',
                'data'   => '20230523'
            ),
        );
        /*
        $this->_tabela = $tabela;
        $this->_id = $id;
        $this->_id_codificado = base64_encode($id);

        $this->_link_retorno = $param['link_retorno'] ?? getLink() . "perfil&id=$this->_id_codificado";

        $this->insereJS();*/
        $this->_tabela_evento = 'calendario_eventos';
        $this->_sql = "SELECT titulo,data FROM ". $this->_tabela_evento;
    }
    
    private function printTarefas()
    {
        $ret='';
        /*{
            //       title          : 'All Day Event',
            //       start          : new Date(y, m, 1),
            //       backgroundColor: '#f56954', //red
            //       borderColor    : '#f56954', //red
            //       allDay         : true
            //     },*/
        $arrayTarefa=[];
        foreach ($this->_tarefas as $tarefa){
            $temp = "{
                        title : '{$tarefa['titulo']}',
                        start : '{$tarefa['data']}',
                        backgroundColor: '#f56954', 
                        borderColor    : '#f56954',
                        allDay         : true
                    }";
            $arrayTarefa[] = $temp;
        }
        $ret = implode(', ',$arrayTarefa);
        return $ret;
    }
    
    public function setQueryBD($sql)
    {
        
        $this->_sql = $sql;
    }
    
    public function addTarefa($tarefa)
    {
        //Função que acrescenta uma tarefa a $_tarefas;
        if(is_array($tarefa)){
            $this->_tarefas[] = $tarefa;
        }
    }
    
    public function addConjuntoTarefas($arrayTarefas)
    {
        foreach($arrayTarefas as $tarefa){
            $this->addTarefa($tarefa);
        }
    }
    
    private function getTarefasFromDB()
    {
        $rows = query($this->_sql);
        if(is_array($rows) && count($rows)>0)
        {
            foreach($rows as $row)
            {
                $temp=[];
                $temp['titulo'] = $row['titulo'];
                $temp['data'] = $row['data'];
                $this->_tarefas[] = $temp;
            }
        }
    }

    public function __toString() {
        if(count($this->_tarefas)==0){
            $this->getTarefasFromDB();
        }        
        $this->insereJS();
        
        $html = '<div class="">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div id="modal">
      </div>
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Calendário</h1>
          </div>
          <div class="col-sm-6">
            <a href="'.$this->_link_retorno.'" type="button" class="btn btn-outline-danger float-right btn-sm">Voltar</a>
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
                  <h4 class="card-title">Eventos arrastáveis</h4>
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
                  <h3 class="card-title">Criar Evento</h3>
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
             // events: '" . getLinkAjax("eventos&tabela=$this->_tabela&id=$this->_id") . "',
               events: [
                     ".$this->printTarefas()."
               ],
              editable  : true,
              droppable : true, // this allows things to be dropped onto the calendar !!!
              eventDrop : function(info) {
                var link = '".getLinkAjax('mover'). '&id=' ."';
                $.get(link + info.event.id + '&data=' + info.event.start, function(retorno){
                  document.getElementById('adwsadasdsda').innerHTML = retorno;
                });
              },
              eventClick: function(info) {
                // alert('Event: ' + info.event.title);
                // alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
                // alert('View: ' + info.view.type);

                var link = '".getLinkAjax('editar'). '&id=' ."';
                $.get(link + info.event.id + '&data=' + info.event.start, function(retorno){
                    document.getElementById('modal').innerHTML = retorno;
                    // alert(retorno);
                });
            
                // change the border color just for fun
                // info.el.style.borderColor = 'red';
              },
              eventReceive : function(info) {
                
                var link = '".getLinkAjax('incluir') . "&tabela=$this->_tabela&id=$this->_id" ."';
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