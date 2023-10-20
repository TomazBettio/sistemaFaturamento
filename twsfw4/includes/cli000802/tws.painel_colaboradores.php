<?php
/*
 * Data Criacao 15/12/2022
 * Autor: TWS - Rafael Postal
 *
 * Descricao:
 *
 * Altera��es:
 *
 */
if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class painel_colaboradores extends elemento_painel {

    function __construct($tabela, $id, $param = []) {
        parent::__construct($tabela, $id, 'rh_atualizacoes', $param);

        // $this->insereJS();
    }

    public function __toString() {
        
        
        $html = '<div class="content">
                <!-- Content Header (Page header) -->
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">'.$this->_titulo.'</h1>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.container-fluid -->
                </div>
                ';
	    if(isset($this->_dados['nome'])) {
	        $html .= $this->cabecalhoHtml('primary');
	    }

	    $html .=    $this->navegacao('primary').
                        '<div class="div-main" id = "adwsadasdsda">
                            <div class="col-lg-12 resizable-summary-view">
                                <!-- TO DO List -->
                                            
                                '.$this->cardGeral().'

                            </div>
                        </div>
                </div>
            </div>';

//style="display: none;

	    return $html;
    }

    public function cardGeral() {
        $html = '<div class="row">
                    <!-- /.card-header -->
                    <div>
                                                    
                        '.$this->cardDocumentos('rh_arquivos', 'primary').'
                    </div>
                    <div class="col-sm">
                        <div class="row">
                            <div class="col-sm-5">';

        $html .=                $this->cardAtividades('rh_eventos', 'primary').'

                            </div>
                            <div class="col-sm-7">
                                '.$this->cardContrato('rh_contrato_expectativa').'
                            </div>
                        </div>';

        $html .=        $this->cardComentarios('rh_comentarios', 'primary').'

                    </div>
                </div>';

        return $html;
    }

    private function getEventos($detalhado = '') {
        $html = '';
    
        $sql = "SELECT * FROM rh_eventos WHERE entidade_tipo = '{$this->_tabela}' AND entidade_id = {$this->_id} AND ativo = 'S'";
        $dados = query($sql);
    
        if(is_array($dados) && count($dados) > 0) {
            if($detalhado == '') {
                $html = '<tr>
                            <td>Nome</td>
                            <td>Data</td>
                            <td>Tipo</td>
                            <td>Onde</td>
                        </tr>';
                        
                foreach($dados as $dado) {
                    $html .= '<tr>
                                <td>'.
                                    $dado['nome']
                                .'</td>
                                <td>'.
                                    $dado['data']
                                .'</td>
                                <td>'.
                                    $dado['tipo']
                                .'</td>
                                <td>'.
                                    $dado['onde']
                                .'</td>
                            </tr>';
                }
            } else {
                $cad = new cad01('rh_eventos');
                $sys003 = $cad->getSys003();

                $html .= '<table>
                            <tr>
                                <td>';
                $temp =            '<table>
                                        <thead>
                                            <tr>';
                foreach($sys003 as $s) {
                    $temp .=                    '<th>'.$s['etiqueta'].'</th>';
                }
                $temp .=                    '</tr>
                                        </thead>
                                        <tbody>';
                foreach($dados as $dado) {
                    $temp .=                '<tr>';
                    foreach($sys003 as $s) {
                        $temp .=                '<td>'.$dado[$s['campo']].'</td>';
                    }
                    $temp .=                '</tr>';
                }
                $temp .=                '</tbody>
                                    </table>';
                $html .=            addCard(['conteudo' => $temp, 'titulo' => 'evento '.$dado['nome']]);
                $html .=        '</td>
                            </tr>';
                $html .= '</table>';
            }
        }
        
        return $html;
    }

    public function salvarAlteracoes() {
        $crudTemp = new cad01($this->_tabela);
        $crudTemp->salvar($this->_id_codifcado, $_POST, 'E');

        $param = [];
        $param['descricao'] = 'Alterações no banco '.$this->_tabela;
        $param['operacao'] = 'alteração';
        $this->gravarAtualizacoes($param);
    }

    public function salvarAlteracoesConexao() {
        $sql = montaSQL($_POST, 'rh_conexao_colaborador', 'UPDATE', "id_colaborador = {$this->_id}");
        query($sql);
        
        $param = [];
        $param['descricao'] = 'Alterações na conexão com colaborador';
        $param['operacao'] = 'alteração';
        $this->gravarAtualizacoes($param);
    }

    public function salvarAlteracoesContrato() {
        $sql = montaSQL($_POST, 'rh_contrato_expectativa', 'UPDATE', "id_colaborador = {$this->_id}");
        query($sql);
        
        $param = [];
        $param['descricao'] = 'Alterações no contrato de expectativa com colaborador';
        $param['operacao'] = 'alteração';

        $this->gravarAtualizacoes($param);
    }

    public function salvarAlteracoesEndereco($id = '') {
        if(!empty($id)) {
            $where = "id = $id";
            $tipo = 'UPDATE';
            $operacao = 'alteração';
        } else {
            $where = '';
            $tipo = 'INSERT';
            $operacao = 'implementação';
        }
        
        $sql = montaSQL($_POST, 'rh_enderecos', $tipo, $where);
        query($sql);

        $param = [];
        $param['descricao'] = 'Alterado um endereço';
        $param['operacao'] = $operacao;

        $this->gravarAtualizacoes($param);
    }

    public function salvarComentario($mensagem, $id_comentario = '') {
        // $id = getParam($_GET, 'id', '');
        $param = [];
        $param['usuario'] = getUsuario();
        $param['data'] = date('YmdHis');
        $param['comentario'] = $mensagem;
        $param['entidade'] = $this->_tabela;
        $param['id_entidade'] = $this->_id;
        $param['id_pai'] = $id_comentario;

        $sql = montaSQL($param, 'rh_comentarios');
        query($sql);

        // --- SALVANDO A MODIFICAÇÃO EM rh_atualizacoes ------
        $param = [];
        $param['descricao'] = 'Adicionado um novo comentário';
        $param['operacao'] = 'inclusão';

        $this->gravarAtualizacoes($param);

        // redireciona(getLink() . "perfil&tabela=$this->_tabela&id=$this->_id_cod");
    }

    /////////////////// FUNÇÃO PARA GRAVAR AS MODIFICAÇÕES FEITAS NO SITE //////////////////////
    public function gravarAtualizacoes($param) {
        $temp = [];
        $temp['descricao'] = $param['descricao'];
        $temp['operacao'] = $param['operacao'];
        $temp['entidade'] = $this->_tabela;
        $temp['id_entidade'] = $this->_id;
        $temp['usuario'] = getUsuario();
        $temp['data'] = date('YmdHis');

        $sql = montaSQL($temp, 'rh_atualizacoes');
        query($sql);
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