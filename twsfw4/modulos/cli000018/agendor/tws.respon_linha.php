<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class respon_linha{
    var $funcoes_publicas = array(
        'index' 		        => true,
        'salvar' 		        => true,
    );
    
    public function index(){
        $ret = '';
        
        $relatorio = new tabela01(['paginacao' => false, 'info' => false, 'ordenacao' => false, 'filtro' => false]);
        //$relatorio->setParamTabela(['paginacao' => false, 'info' => false, 'ordenacao' => false, 'filtro' => false]);
        $relatorio->addColuna(array('campo' => 'grupo'      , 'etiqueta' => 'Grupo'       , 'width' =>  205, 'posicao' => 'C', 'tipo' => 'T'));
        $relatorio->addColuna(array('campo' => 'emails'     , 'etiqueta' => 'Emails (separados por ;)'      , 'width' =>  300, 'posicao' => 'C', 'tipo' => 'T'));
        $dados = $this->getDados();
        $relatorio->setDados($dados);
        
        $ret .= $relatorio;
        
        
        $bt_enviar = [
            'texto' => 'Salvar Alterações',
            'type' => 'submit',
            'cor'   => 'primary',
        ];
        $ret = addCard(['titulo' => 'Emails Responsáveis', 'conteudo' => $ret, 'botoesTitulo' => [$bt_enviar]]);
        $ret = formbase01::form(['nome' => 'formEmail', 'id' => 'formEmail', 'sendFooter' => false, 'acao' => getLink() . 'salvar'], $ret);
        
        return $ret;
    }
    
    public function salvar(){
        $dados = $_POST['formEmail'];
        $dicionario_grupo = montarDicionarioSys005('BSGRLI');
        $dicionario_grupo['SEM'] = 'Outros';
        
        $grupos = array_keys($dicionario_grupo);
        foreach ($grupos as $grupo){
            $emails = $dados[$grupo];
            $sql_excluir = "delete from bs_agendor_resp_linha where grupo = '$grupo'";
            $sql_incluir = "insert into bs_agendor_resp_linha values ('$grupo', '$emails')";
            
            query($sql_excluir);
            query($sql_incluir);
        }
        redireciona();
    }
    
    private function getDados(){
        $ret = [];
        $dicionario_grupo = montarDicionarioSys005('BSGRLI');
        if(is_array($dicionario_grupo) && count($dicionario_grupo) > 0){
            $dicionario_grupo['SEM'] = 'Outros';
            $emails = [];
            $sql = "select * from bs_agendor_resp_linha";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $emails[$row['grupo']] = $row['emails'];
                }
            }
            foreach ($dicionario_grupo as $cod => $etiqueta){
                $param = [
                    'nome' => "formEmail[$cod]",
                    'valor' => $emails[$cod] ?? '',
                    'estilo' => "width: 300px",
                ];
                $temp = [
                    'grupo' => $etiqueta,
                    'emails' => formbase01::formTexto($param),
                ];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
}