<?php
class amarrarVendedores{
    var $funcoes_publicas = array(
        'index'     => true,
        'ajax'      => true,
    );
    
    public function index(){
        $ret = '';
        $bloco_agendor = '';
        
        $this->addJs();
        
        $tabela_usuarios_agendor = new tabela01();
        $tabela_usuarios_agendor->addColuna(array('campo' => 'nome', 'etiqueta' => 'Usuário', 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
        $param = [];
        $param['texto'] 	= 'Detalhes';
        $param['onclick'] 	= "detalhes('{COLUNA:id}');";
        $param['coluna'] 	= 'id';
        $param['pos'] = 'F';
        $param['flag'] 	= '';
        $tabela_usuarios_agendor->addAcao($param);
        
        $dados = $this->getDadosAgendor();
        $tabela_usuarios_agendor->setDados($dados);
        
        $bloco_agendor = addCard(['conteudo' => $tabela_usuarios_agendor . '', 'titulo' => 'Usuários Agendor']);
        
        $bloco_protheus = addCard(['conteudo' => '', 'titulo' => 'Vendedores Protheus Vinculados']);
        
        $ret = addLinha(['tamanhos' => [6, 6], 'conteudos' => [$bloco_agendor, '<div id="blocoProtheus">' . $bloco_protheus . '</div>']]);
        return $ret;
    }
    
    private function addJs(){
        $ret = '
function detalhes(codigo){
    var link = "' . getLinkAjax('bloco') . '&codigo=" + codigo;
    $.get(link, function(retorno){
        document.getElementById("blocoProtheus").innerHTML = retorno;
    });
}

function amarrar(id){
    var codigo = document.getElementById("codigoProtheus").value;
    var link = "' . getLinkAjax('amarrar') . '&agendor=" + id + "&protheus=" + codigo;
    $.get(link, function(retorno){
        document.getElementById("blocoProtheus").innerHTML = retorno;
    });
}

function excluir(id, codigo){
    var link = "' . getLinkAjax('excluir') . '&agendor=" + id + "&protheus=" + codigo;
    $.get(link, function(retorno){
        document.getElementById("blocoProtheus").innerHTML = retorno;
    });
}
';
        addPortaljavaScript($ret);
    }
    
    private function getDadosAgendor(){
        $ret = [];
        $sql = "select * from bs_agendor_usuarios";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['id', 'nome'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function montarOpcoesProtheus(){
        $ret = [];
        $sql = "select codigo, nome from bs_vendedores where codigo not in (select cod_protheus from bs_agendor_protheus_vendedores)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret[] = ['', 'Selecione um vendedor'];
            foreach ($rows as $row){
                $ret[] = [$row['codigo'], $row['nome']];
            }
        }
        return $ret;
    }
    
    private function desenharTabelaProtheus($codigo){
        $ret = '';
        
        $tabela = new tabela01();
        $tabela->addColuna(array('campo' => 'codigo', 'etiqueta' => 'Código', 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'nome'  , 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
        
        $param = [];
        $param['texto'] 	= 'Excluir';
        $param['onclick'] 	= "excluir('$codigo', '{COLUNA:codigo}');";
        $param['coluna'] 	= 'codigo';
        $param['pos'] = 'F';
        $param['cor'] 	= 'danger';
        $tabela->addAcao($param);
        
        $dados = $this->getDadosProtheus($codigo);
        $tabela->setDados($dados);
        
        $ret .= $tabela;
        
        $select = formbase01::formSelect(['id' => 'codigoProtheus', 'campo' => 'aaa', 'lista' => $this->montarOpcoesProtheus()]);
        $bt = formbase01::formBotao([
            'bloco' => true,
            'cor' => 'success',
            'onclick' => "amarrar('$codigo');",
            'texto' => 'Amarrar <br>vendedor X usuário',
        ]);
        $linha = addLinha(['tamanhos' => [8, 4], 'conteudos' => [$select, $bt]]) . '<br>';
        $ret = $linha . $ret;
        return $ret;
    }
    
    private function getDadosProtheus($codigo){
        $ret = [];
        $sql = "select bs_agendor_protheus_vendedores.cod_protheus as codigo, bs_vendedores.nome as nome from bs_agendor_protheus_vendedores join bs_vendedores on (bs_agendor_protheus_vendedores.cod_protheus = bs_vendedores.codigo) where bs_agendor_protheus_vendedores.id_agendor = $codigo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows;
        }
        return $ret;
    }
    
    private function getNomeAgendor($codigo){
        $ret = '';
        $sql = "select nome from bs_agendor_usuarios where id = $codigo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['nome'];
        }
        return $ret;
    }
    
    private function amarrar($id, $codigo){
        $sql = "insert into bs_agendor_protheus_vendedores values ($id, '$codigo')";
        query($sql);
    }
    
    private function excluir($id, $codigo){
        $sql = "delete from bs_agendor_protheus_vendedores where id_agendor = $id and cod_protheus = '$codigo'";
        query($sql);
    }
    
    public function ajax(){
        $ret = '';
        $op = getOperacao();
        if($op == 'bloco'){
            $codigo = $_GET['codigo'];
            $tabela = $this->desenharTabelaProtheus($codigo);
            
            $ret = addCard(['conteudo' => '<div id="tabelaProtheus">' . $tabela . '</div>', 'titulo' => 'Vendedores Protheus Vinculados - ' . $this->getNomeAgendor($codigo)]);
        }
        if($op == 'amarrar'){
            $id_agendor = $_GET['agendor'] ?? '';
            $cod_protheus = $_GET['protheus'] ?? '';
            if(!empty($id_agendor) && !empty($cod_protheus)){
                $this->amarrar($id_agendor, $cod_protheus);
            }
            $tabela = $this->desenharTabelaProtheus($id_agendor);
            
            $ret = addCard(['conteudo' => '<div id="tabelaProtheus">' . $tabela . '</div>', 'titulo' => 'Vendedores Protheus Vinculados - ' . $this->getNomeAgendor($id_agendor)]);
        }
        if($op == 'excluir'){
            $id_agendor = $_GET['agendor'] ?? '';
            $cod_protheus = $_GET['protheus'] ?? '';
            if(!empty($id_agendor) && !empty($cod_protheus)){
                $this->excluir($id_agendor, $cod_protheus);
            }
            $tabela = $this->desenharTabelaProtheus($id_agendor);
            
            $ret = addCard(['conteudo' => '<div id="tabelaProtheus">' . $tabela . '</div>', 'titulo' => 'Vendedores Protheus Vinculados - ' . $this->getNomeAgendor($id_agendor)]);
        }
        return $ret;
    }
}