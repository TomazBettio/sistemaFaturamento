<?php
/*
* Data Criação: 04/09/2023
* Autor: BCS
*
* Relatório de Horas Apontadas por Recurso
*/

if(!defined('TWSiNet'))define('TWSiNet', true);
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);


class verificacao_recursos{
    
    //Último id acessado das OSs
    private $_idOs = 0;
    //Último id acessado das Tarefas
    private $_idTarefa = 0;
    //Lista de remetentes para o email
    private $_lista_email = // ['alex.cesar@verticais.com.br']; 
    ['alexandre.thiel@verticais.com.br'];
    //Lista de recursos abaixo do percentual semanal
    private $_lista_recursos = [];
    
    function __construct(){
        set_time_limit(0);
    }
    
    function index(){
        
    }
    
    function schedule($parametro)
    {
        //TESTE: alex.cesar@verticais.com.br;
        //      alexandre.thiel@verticais.com.br;rodrigo.ximenes@verticais.com.br;ary.andrade@verticais.com.br;marcos.ludwig@verticais.com.br;rayan.andrade@verticais.com.br
        
        //criar grupos usuários(equipes), separar tabela por equipes
        
        $this->_lista_email = $parametro;
        
        //1o: Atualiza recursos-hora com os valores de OS/Tarefas
        $this->getLastIds();
        
        $lista_recursos = $this->getRecursosAll();
        
        $equipe = $lista_recursos[0]['equipe'];
        $total_equipe = [
            'mes' => [
                'total'        => '0:00',
                'apontada'     => '0:00',
            ],
            'semana' => [
                'total'        => '0:00',
                'apontada'     => '0:00',
            ],
            'dia' => [
                'total'        => '0:00',
                'apontada'     => '0:00',
            ]
        ];
        $total = [
            'mes' => [
                'total'        => '0:00',
                'apontada'     => '0:00',
            ],
            'semana' => [
                'total'        => '0:00',
                'apontada'     => '0:00',
            ],
            'dia' => [
                'total'        => '0:00',
                'apontada'     => '0:00',
            ]
        ];
        
        $this->atualizaRecursosHora($lista_recursos);
        
        $this->setLastIds();
        
        //2o: Observa %hora/apontada de cada recurso
        foreach($lista_recursos as $recurso)
        {
            if($equipe != $recurso['equipe'])
            {
                //início de uma nova equipe, cria linha com o total da anterior
                $temp = [
                    'nome'      => "TOTAL $equipe",
                    'usuario'   => $equipe,
                    'h_tmes'    => $total_equipe['mes']['total'],
                    'h_ames'    => $total_equipe['mes']['apontada'],
                    'per_m'     => $this->percentualHoras($total_equipe['mes']),
                    'h_tsem'    => $total_equipe['semana']['total'],
                    'h_asem'    => $total_equipe['semana']['apontada'],
                    'per_s'     => $this->percentualHoras($total_equipe['semana']),
                    'h_tdia'    => $total_equipe['dia']['total'],
                    'h_adia'    => $total_equipe['dia']['apontada'],
                    'per_d'     => $this->percentualHoras($total_equipe['dia']),
                ];
                $this->_lista_recursos[] = $temp;
                
                //Soma horas
                $total['mes']['total']      = $this->somaHora($total['mes']['total']        , $total_equipe['mes']['total']      );
                $total['mes']['apontada']   = $this->somaHora($total['mes']['apontada']     , $total_equipe['mes']['apontada']   );
                $total['semana']['total']   = $this->somaHora($total['semana']['total']     , $total_equipe['semana']['total']   );
                $total['semana']['apontada']= $this->somaHora($total['semana']['apontada']  , $total_equipe['semana']['apontada']);
                $total['dia']['total']      = $this->somaHora($total['dia']['total']        , $total_equipe['dia']['total']      );
                $total['dia']['apontada']   = $this->somaHora($total['dia']['apontada']     , $total_equipe['dia']['apontada']   );
                
                
                //Zera para próxima equipe
                $equipe = $recurso['equipe'];
                $total_equipe = [
                    'mes' => [
                        'total'        => '0:00',
                        'apontada'     => '0:00',
                    ],
                    'semana' => [
                        'total'        => '0:00',
                        'apontada'     => '0:00',
                    ],
                    'dia' => [
                        'total'        => '0:00',
                        'apontada'     => '0:00',
                    ]
                ];
            }
            //Pega horas totais das tarefas do usuário, soma e verifica com o horário de trabalho (percentual)
            //3 percentuais: dia, semana e mês
            $tempo = ['mes'=>[],'semana'=>[],'dia'=>[]];
            $id_recurso = $this->getIdRecurso($recurso['usuario']);
            
            $tempo['mes'] = $this->getHoras($id_recurso,30);
            $tempo['semana'] = $this->getHoras($id_recurso,7);
            $tempo['dia'] = $this->getHoras($id_recurso,1);
            
            //$perc_mes = $this->percentualHoras($tempo['mes']);
            //$perc_sem = $this->percentualHoras($tempo['semana']);
            //$perc_dia = $this->percentualHoras($tempo['dia']);
            
            //2.5: Manda UM email com as informações numa tabela
            $temp = [
                'nome'      => getUsuario('nome', $recurso['usuario']),
                'usuario'   => $recurso['usuario'],
                'h_tmes'    => $tempo['mes']['total'],
                'h_ames'    => $tempo['mes']['apontada'],
                'per_m'     => $this->percentualHoras($tempo['mes']),
                'h_tsem'    => $tempo['semana']['total'],
                'h_asem'    => $tempo['semana']['apontada'],
                'per_s'     => $this->percentualHoras($tempo['semana']),
                'h_tdia'    => $tempo['dia']['total'],
                'h_adia'    => $tempo['dia']['apontada'],
                'per_d'     => $this->percentualHoras($tempo['dia']),
            ];
            $this->_lista_recursos[] = $temp;
            
            //Soma horas
            $total_equipe['mes']['total']      = $this->somaHora($total_equipe['mes']['total']        , $tempo['mes']['total']      );
            $total_equipe['mes']['apontada']   = $this->somaHora($total_equipe['mes']['apontada']     , $tempo['mes']['apontada']   );
            $total_equipe['semana']['total']   = $this->somaHora($total_equipe['semana']['total']     , $tempo['semana']['total']   );
            $total_equipe['semana']['apontada']= $this->somaHora($total_equipe['semana']['apontada']  , $tempo['semana']['apontada']);
            $total_equipe['dia']['total']      = $this->somaHora($total_equipe['dia']['total']        , $tempo['dia']['total']      );
            $total_equipe['dia']['apontada']   = $this->somaHora($total_equipe['dia']['apontada']     , $tempo['dia']['apontada']   );
        }
        
        //fim do loop, total para a última equipe
        $temp = [
            'nome'      => "TOTAL $equipe",
            'usuario'   => $equipe,
            'h_tmes'    => $total_equipe['mes']['total'],
            'h_ames'    => $total_equipe['mes']['apontada'],
            'per_m'     => $this->percentualHoras($total_equipe['mes']),
            'h_tsem'    => $total_equipe['semana']['total'],
            'h_asem'    => $total_equipe['semana']['apontada'],
            'per_s'     => $this->percentualHoras($total_equipe['semana']),
            'h_tdia'    => $total_equipe['dia']['total'],
            'h_adia'    => $total_equipe['dia']['apontada'],
            'per_d'     => $this->percentualHoras($total_equipe['dia']),
        ];
        $this->_lista_recursos[] = $temp;
        
        //Soma horas
        $total['mes']['total']      = $this->somaHora($total['mes']['total']        , $total_equipe['mes']['total']      );
        $total['mes']['apontada']   = $this->somaHora($total['mes']['apontada']     , $total_equipe['mes']['apontada']   );
        $total['semana']['total']   = $this->somaHora($total['semana']['total']     , $total_equipe['semana']['total']   );
        $total['semana']['apontada']= $this->somaHora($total['semana']['apontada']  , $total_equipe['semana']['apontada']);
        $total['dia']['total']      = $this->somaHora($total['dia']['total']        , $total_equipe['dia']['total']      );
        $total['dia']['apontada']   = $this->somaHora($total['dia']['apontada']     , $total_equipe['dia']['apontada']   );
        
        //Total da empresa
        $temp = [
            'nome'      => "TOTAL Verticais",
            'usuario'   => "Verticais",
            'h_tmes'    => $total['mes']['total'],
            'h_ames'    => $total['mes']['apontada'],
            'per_m'     => $this->percentualHoras($total['mes']),
            'h_tsem'    => $total['semana']['total'],
            'h_asem'    => $total['semana']['apontada'],
            'per_s'     => $this->percentualHoras($total['semana']),
            'h_tdia'    => $total['dia']['total'],
            'h_adia'    => $total['dia']['apontada'],
            'per_d'     => $this->percentualHoras($total['dia']),
        ];
        $this->_lista_recursos[] = $temp;
        
        echo "- Enviei email - ";
        $this->enviaEmailPercentual();
    }
    
    //Pega último id salvo para OSs e tarefas
    private function getLastIds()
    {
        $sql = "SELECT valor FROM sys006 WHERE parametro = 'recursos_id_OS'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $this->_idOs = $rows[0]['valor'];
        }
        
        $sql = "SELECT valor FROM sys006 WHERE parametro = 'recursos_id_tarefa'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $this->_idTarefa = $rows[0]['valor'];
        }
    }
    
    //Salva último id das OSs e Tarefas na sys006
    private function setLastIds()
    {
        $sql = "SELECT id FROM sdm_os ORDER BY id DESC LIMIT 1";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $id = $rows[0]['id'];
            gravarParametroSistema('recursos_id_OS', $id);
            
            $this->_idOs = $id;
        }
        
        $sql = "SELECT id FROM sdm_tarefas ORDER BY id DESC LIMIT 1";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $id = $rows[0]['id'];
            gravarParametroSistema('recursos_id_tarefa', $id);
            
            $this->_idTarefa = $id;
        }
    }
    
    
    //ATUALIZAÇÃO sdm_horas_apontadas
    private function atualizaRecursosHora($lista_recursos)
    {
        foreach($lista_recursos as $recurso)
        {
            //$hora_dia = $recurso['hora_dia'];
            $id = $this->getIdRecurso($recurso['usuario']);
            $tarefas = $this->getTarefasOS($recurso['usuario']);
            foreach($tarefas as $tar)
            {
                //$hora_apontada = $tar['hora_apontada'];
                //$data = $tar['data'];
                $val = $this->getDataTabela($tar['data'], $id);
                $h_aponta = $this->somaHora($tar['hora_apontada'], $val['hora_apontada']);
                
                if($val['id'] == 0){
                    //insere em sdm_horas_apontadas
                    $sql = "INSERT INTO sdm_horas_apontadas (recurso,data,hora_dia,hora_apontada)                         
                            VALUES ($id, '{$tar['data']}', '{$recurso['hora_dia']}', '$h_aponta')";
                } else {
                    //atualiza sdm_horas_apontadas
                    $sql = "UPDATE sdm_horas_apontadas SET hora_apontada = '$h_aponta' 
                            WHERE recurso = $id AND data = '{$tar['data']}'";
                }
                query($sql);
            }
        }
    }
    
    //retorna se existe um registro de recurso alocado para aquela data
    private function getDataTabela($data, $id_recurso)
    {
        $ret = ['id'=>0,'hora_apontada'=>'0:00'];
        $sql = "SELECT id, hora_apontada FROM sdm_horas_apontadas WHERE recurso=$id_recurso AND data='$data'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)==1){
            $ret['id'] = $rows[0]['id'];
            $ret['hora_apontada'] = $rows[0]['hora_apontada'];
        }
        return $ret;
    }
    
    //Lista de todos os recursos (usuario+hora/dia)
    private function getRecursosAll()
    {
        $ret = [];
        
        $sql = "SELECT usuario, hora_dia, equipe FROM sdm_recursos WHERE ativo='S' ORDER BY equipe, nome";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach($rows as $row){
                $temp['usuario'] = $row['usuario'];
                $temp['hora_dia'] = $row['hora_dia'];
                $temp['equipe'] = $row['equipe'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    //Lista todas as tarefas de um recurso (usuario)
    private function getTarefasOS($usuario)
    {
        $ret = [];
        
        //Tarefas
        $sql = "SELECT data, tempo FROM sdm_tarefas WHERE usuario = '$usuario' 
                AND id > ".$this->_idTarefa." ORDER BY data";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach($rows as $row){
                $temp['data'] = $row['data'];
                $temp['hora_apontada'] = $row['tempo'];
                $ret[] = $temp;
            }
        }
        
        //OSs
        $sql = "SELECT data, hora_total FROM sdm_os WHERE user = '$usuario' 
                AND id > ".$this->_idOs." ORDER BY data";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach($rows as $row){
                $temp['data'] = $row['data'];
                $temp['hora_apontada'] = $row['hora_total'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
      
    private function getIdRecurso($usuario)
    {
        $ret = 0;
        $sql = "SELECT id FROM sdm_recursos WHERE usuario = '$usuario'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0]['id'];
        }
        return $ret;
    }
    
    private function getHoraDiaRecurso($id_recurso)
    {
        $ret = '0:00';
        $sql = "SELECT hora_dia FROM sdm_recursos WHERE id = '$id_recurso'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0]['hora_dia'];
        }
        return $ret;
    }
    
    private function getDiaSemanaRecurso($id_recurso)
    {
        $ret = '';
        $sql = "SELECT semana FROM sdm_recursos WHERE id = '$id_recurso'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0]['semana'];
        }
        return $ret;
    }
    
    
    //HORAS APONTADAS
  
    private function getHoras($id_recurso,$range = 0)
    {
        $total = $this->getHorasTotais($id_recurso,$range);
        $ret = ['total' => $total, 'apontada'=>'00:00'];
        
        $dia = date('Ymd');
        if($range == 1){
            $anterior = $dia;
        } else {
            $anterior = datas::getDataDias(-$range,$dia);
        }
        
        $sql = "SELECT hora_apontada FROM sdm_horas_apontadas
             WHERE recurso = $id_recurso AND data between '$anterior' and '$dia'";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            //soma todas as horas apontadas
            $apontado = '0:00';
            foreach($rows as $row){
                $apontado = $this->somaHora($apontado, $row['hora_apontada']);
            }
            $ret['apontada'] = $apontado;
        }
        return $ret;
    }
    
    private function getHorasTotais($id_recurso, $range)
    {
        $ret = '00:00';
        
        //do id_recurso recupera os dias trabalhados
        $dias_semana = $this->getDiaSemanaRecurso($id_recurso);
        $num_dias_semana = strlen($dias_semana);
        //do id_recurso pega as horas por dia
        $hora_dia = $this->getHoraDiaRecurso($id_recurso);
        
        if($range == 1){
            $ini = date('N');
            if(strpos($dias_semana,"$ini") !== false){
                $ret = $hora_dia;
            }
        } else {
            //do range observa o 1o dia e compara com os dias trabalhados
            //pegar timestamp unix: strtotime
            $data_ini = datas::getDataDias(-$range,date('Ymd'));
            //Descobrir  o dia da semana
            $ini = date('N', strtotime($data_ini));
            
            if($num_dias_semana!=0){
                $semanas_no_periodo = intdiv($range,7);
                $total_dias = $num_dias_semana * $semanas_no_periodo;
                $dias_sub_semana = $range % 7;
                $i = 0;
                while($i < $dias_sub_semana){
                    $dia_atual = $ini+$i;
                    if(strpos($dias_semana,"$dia_atual") !== false){
                        $total_dias += 1;
                    }
                    $i++;
                }
                for($i=0;$i<$total_dias;$i++){
                    $ret = $this->somaHora($ret, $hora_dia);
                }
            }
        }
        return $ret;
    }
    
    //Função que soma duas horas em formato string "XX:XX"
    private function somaHora($h1,$h2)
    {
        $ret = "00:00";
        
        $hora1 = explode(':',$h1);
        $hora2 = explode(':',$h2);
        if(isset($hora1[1]))
        {
            if(isset($hora2[1])){ //Ambos no formato certo, faz a soma:
                $h = $hora1[0];
                $min = $hora1[1];
                
                $h += $hora2[0];
                $min += $hora2[1];
                
                if($min>59){
                    $horas = intdiv($min,60);
                    $min = $min - 60*$horas;
                    $h += $horas;
                }
                if($min == 0){
                    $min = '00';
                }
                if($h < 10){
                    $h = "0$h";
                }
                
                $ret = "$h:$min";
            } else { //Retorna a hora no formato certo, trata outra como zero
                $ret = $h1;
            }
        } else if(isset($hora2[1])){ //Retorna a hora no formato certo, trata outra como zero
            $ret = $h2;
        }
        
        return $ret;
    }
    
    //Função que calcula o percentual de horas apontadas/totais, hora no formato string "XX:XX"
    private function percentualHoras($param_horas)
    {
        $ret = 0;
        $total = $param_horas['total'];
        $apontada = $param_horas['apontada'];
        
        $hora = explode(':',$total);
       // $h_tot = $hora[0];
       // $min_tot = $hora[1];
        $num_hora_total = $hora[0] + $hora[1]/60;
        
        $hora = explode(':',$apontada);
       // $h_apo = $hora[0];
       // $min_apo = $hora[1];
        $num_hora_apo = $hora[0] + $hora[1]/60;
        
        if($num_hora_total!=0){
            $val = ($num_hora_apo/$num_hora_total)*100;
            $ret = round($val,2);
        }
        return $ret;
    }
    
    //EMAIL
    
    private function enviaEmailPercentual()
    {
        //cria tabela corpo do email e envia
        $param = [];
        $param['programa'] = 'envio_agenda_email';
        $param['imprimeCabecalho'] = true;
        $param['auto'] = true;
        $mensagem = "Boa noite,<br>\nSegue abaixo as horas apontadas da equipe verticais.";
        $param['mensagem_inicio_email'] = $mensagem;
        $email = new relatorio01($param);
        //formato tabela:
        //separa por equipe
        //total equipe
        //total verticais
        $email->addColuna(['campo' => 'nome'    , 'etiqueta' => 'Nome'                      , 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'usuario' , 'etiqueta' => 'Usuário'                   , 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'h_tmes'  , 'etiqueta' => 'Horas do Mês'	            , 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'h_ames' 	, 'etiqueta' => 'Horas Apontadas do Mês'    , 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'per_m'   , 'etiqueta' => '% Cumprido (Mês)'          , 'tipo' => 'N', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'h_tsem' 	, 'etiqueta' => 'Horas da Semana'	        , 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'h_asem'  , 'etiqueta' => 'Horas Apontadas da Semana'	, 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'per_s' 	, 'etiqueta' => '% Cumprido (Semana)'       , 'tipo' => 'N', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'h_tdia'  , 'etiqueta' => 'Horas do Dia'              , 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'h_adia' 	, 'etiqueta' => 'Horas Apontadas do Dia'	, 'tipo' => 'T', 'width' => 20, 'posicao' => 'E']);
        $email->addColuna(['campo' => 'per_d'   , 'etiqueta' => '% Cumprido (Dia)'          , 'tipo' => 'N', 'width' => 20, 'posicao' => 'E']);
        
        //$destinatario = implode(';', $this->_lista_email);
        $email->setDados($this->_lista_recursos);
        $email->setToExcel(true,'Relatório de Horas Apontadas');
        $email->enviaEmail($this->_lista_email,'Relatório de Horas Apontadas');
        
    }
    
}
