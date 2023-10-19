<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class traducoes{
    public static function traduzirSys003($tabela, $sys003){
        $lingua = getLingua();
        if($lingua !== ''){
            $traducoes = traducoes::getTodasAsTraducoesSys003($tabela, $lingua);
            foreach ($traducoes as $campo => $temp){
                foreach ($temp as $coluna => $texto){
                    $sys003[$campo][$coluna] = $texto;
                }
            }
        }
        return $sys003;
    }
    
    public static function getTodasAsTraducoesSys003($tabela, $lingua){
        $ret = array();
        $codigo = "'sys003-$tabela%'";
        $sql = "select * from traducoes where lingua = '$lingua' and codigo like $codigo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = explode('-', $row['codigo']);
                $campo = $temp[2];
                $coluna = $temp[3];
                $ret[$campo][$coluna] = $row['texto'];
            }
        }
        return $ret;
    }
    
    public static function traduzirTextoDireto($texto){
        $ret = $texto;
        $traducao = traducoes::traduzir($texto);
        if(!empty($traducao)){
            $ret = $traducao;
        }
        return $ret;
    }
    
    public static function traduzir($codigo, $lingua = ''){
        $ret = '';
        $lingua = ($lingua === '' ? getLingua() : $lingua);
        if($lingua !== ''){
            $sql = "select texto from traducoes where lingua = '$lingua'";
            if(is_array($codigo)){
                foreach ($codigo as $codigo_parcial){
                    $sql .= " and codigo like '$codigo_parcial'";
                }
            }
            else{
                $sql .= " and codigo = '$codigo'";
            }
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0]['texto'];
            }
        }
        return $ret;
    }
    
    public static function montarCodigoSys003($tabela, $campo, $coluna){
        return "sys003-$tabela-$campo-$coluna";
    }

    public static function salvarTraducao($codigo, $lingua, $texto){
        $sql = "insert into traducoes values (null, '$codigo', '$lingua', '$texto')";
        query($sql);
    }
    
    public static function atualizarTraducao($codigo, $lingua, $texto){
        $sql = "update traducoes set texto = '$texto' where codigo = '$codigo' and lingua = '$lingua'";
        query($sql);
    }
    
    public static function getTodasAsTraducoesSys005($tabela, $lingua){
        $ret = array();
        $codigo = "'sys005-$tabela%'";
        $sql = "select * from traducoes where lingua = '$lingua' and codigo like $codigo";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = explode('-', $row['codigo']);
                $chave = $temp[2];
                $ret[$chave] = $row['texto'];
            }
        }
        return $ret;
    }
    
    public static function traduzirSys005($tabela, $dados){
        $ret = array();
        $lingua = getLingua();
        if($lingua !== ''){
            $traducoes = traducoes::getTodasAsTraducoesSys005($tabela, $lingua);
            foreach ($dados as $d){
                if(isset($traducoes[$d['chave']])){
                    $temp = $d;
                    $temp[1] = $traducoes[$d['chave']];
                    $temp['descricao'] = $traducoes[$d['chave']];
                    $ret[] = $temp;
                }
                else{
                    $ret[] = $d;
                }
            }
        }
        else{
            $ret = $dados;
        }
        return $ret;
    }
    
    public static function montarCodigoSys005($tabela, $chave){
        return "sys005-$tabela-$chave";
    }
    
    public static function getTodasAsTraducoesSys002($tabela, $lingua){
        $ret = array();
        if($lingua !== ''){
            $codigo = "sys002-$tabela-";
            $sql = "select * from traducoes where codigo like '$codigo%' and lingua = '$lingua'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $temp = explode('-', $row['codigo']);
                    $ret[$temp[2]] = $row['texto'];
                }
            }
        }
        return $ret;
    }
    
    public static function traduzirSys002($tabela, $dados){
        $ret = array();
        $lingua = getLingua();
        if($lingua !== ''){
            $traducoes = traducoes::getTodasAsTraducoesSys002($tabela, $lingua);
            foreach ($dados as $campo => $valor){
                if(isset($traducoes[$campo]) && !empty($traducoes[$campo])){
                    $ret[$campo] = $traducoes[$campo];
                }
                else{
                    $ret[$campo] = $valor;
                }
            }
        }
        else{
            $ret = $dados;
        }
        return $ret;
    }
    
    public static function montarCodigoSys002($tabela, $campo){
        return "sys002-$tabela-$campo";
    }

    public static function getTodasAsTraducoesApp001($lingua){
        $ret = array();
        if($lingua !== ''){
            $sql = "select * from traducoes where codigo like 'app001-%' and lingua = '$lingua'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $temp = explode('-', $row['codigo']);
                    $ret[$temp[1]] = $row['texto'];
                }
            }
        }
        return $ret;
    }
    
    public static function traduzirApp001($dados){
        $ret = array();
        $lingua = getLingua();
        if($lingua !== ''){
            $traducoes = traducoes::getTodasAsTraducoesApp001($lingua);
            foreach ($dados as $d){
                $temp = $d;
                if(isset($traducoes[$d['nome']])){
                    $temp['etiqueta'] = $traducoes[$d['nome']];
                }
                $ret[] = $temp;
            }
        }
        else{
            $ret = $dados;
        }
        return $ret;
    }
    
    public static function montarCodigoApp001($modulo){
        return "app001-$modulo";
    }
    
    public static function getTodasAsTraducoesApp002($lingua){
        $ret = array();
        if($lingua !== ''){
            $sql = "select * from traducoes where codigo like 'app002-%' and lingua = '$lingua'";
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                foreach ($rows as $row){
                    $temp = explode('-', $row['codigo']);
                    $ret[$temp[1]] = $row['texto'];
                }
            }
        }
        return $ret;
    }
    
    public static function traduzirApp002($dados){
        $ret = array();
        $lingua = getLingua();
        if($lingua !== ''){
            $traducoes = traducoes::getTodasAsTraducoesApp002($lingua);
            foreach ($dados as $modulo => $d){
                foreach ($d as $dd){
                    if(isset($traducoes[$dd['programa']])){
                        $temp = $dd;
                        $temp['etiqueta'] = $traducoes[$dd['programa']];
                        $ret[$modulo][] = $temp;
                    }
                    else{
                        $ret[$modulo][] = $dd;
                    }
                }
            }
        }
        else{
            $ret = $dados;
        }
        return $ret;
    }
    
    public static function montarCodigoApp002($programa){
        return "app002-$programa";
    }
}