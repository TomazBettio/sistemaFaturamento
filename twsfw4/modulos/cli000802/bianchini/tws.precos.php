<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class precos{
    var $funcoes_publicas = array(
        'index'     => true,
        'salvarPrecos'  => true,
    );
    
    
    public function __construct(){
    }
    
    public function index(){
        $relatorio = new relatorio01(['titulo' => 'Definir Preços']);
        $relatorio->setParamTabela([
            'ordenacao' => false,
            'info' => false,
            'filtro' => false,
        ]);
        $cidades = [
            'canoas' => 'Canoas', 'riogrande' => 'Rio Grande', 'carazinho' => 'Carazinho', 'vacaria' => 'Vacaria', 'algusto' => 'Santo Algusto'
        ];
        $relatorio->addColuna(array('campo' => 'etiqueta' , 'etiqueta' => 'Produtos'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        foreach ($cidades as $codigo => $nome){
            $relatorio->addColuna(array('campo' => $codigo , 'etiqueta' => $nome         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        }
        $dados = $this->getDados();
        $relatorio->setDados($dados);
        $relatorio->setFormTabela([
            'acao' => getLink() . 'salvarPrecos',
            'nome' => 'formPrecos',
            'id' => 'formPrecos',
            'sendFooter' => true,
        ]);
        return $relatorio . '';
    }
    
    private function getDados(){
        $ret = [];
        $cidades = [
            'canoas', 'riogrande', 'carazinho', 'vacaria', 'algusto'
        ];
        $valores = [
            'limpo' => [
                'canoas' => 151,
                'riogrande' => 1,
                'carazinho' => -1,
                'vacaria' => -0.5,
                'algusto' => 2,
            ],
            'lavoura' => [
                'canoas' => 148,
                'riogrande' => 1,
                'carazinho' => -1,
                'vacaria' => -0.5,
                'algusto' => 2,
            ],
        ];
        
        foreach ($valores as $chave => $lista){
            $temp = ['etiqueta' => ucfirst($chave)];
            foreach ($cidades as $c){
                $temp[$c] = formbase01::formTexto(['campo' => "formPrecos[$c][$chave]", 'tamanho' => 100, 'mascara' => 'V', 'valor' => $lista[$c], 'negativo' => true]);
            }
            $ret[] = $temp;
        }
        return $ret;
    }
    
    private function toFloat($string){
        return floatval(str_replace(['.', ','], ['', '.'], $string));
    }
    
    public function salvarPrecos(){
        $dados = $_POST['formPrecos'];
        $canoas = $this->toFloat($dados['canoas']['limpo']);
        $riogrande = $this->toFloat($dados['riogrande']['limpo']) + $canoas;
        $carazinho = $this->toFloat($dados['carazinho']['limpo']) + $canoas;
        $vacaria = $this->toFloat($dados['vacaria']['limpo']) + $canoas;
        $algusto = $this->toFloat($dados['algusto']['limpo']) + $canoas;
        $hora = date('H');
        $minuto = date('i');
        $titulo = "$hora:$minuto" . "h - R$";
        $sql = "insert into kl_cards values (null, '" . $titulo . formataReais($canoas) . "', 51, '', 'primary', 'BIANCHINI', '', 0.00, CURRENT_TIMESTAMP())";
        query($sql);
        $sql = "insert into kl_cards values (null, '" . $titulo . formataReais($riogrande) . "', 52, '', 'primary', 'BIANCHINI', '', 0.00, CURRENT_TIMESTAMP())";
        query($sql);
        $sql = "insert into kl_cards values (null, '" . $titulo . formataReais($carazinho) . "', 53, '', 'primary', 'BIANCHINI', '', 0.00, CURRENT_TIMESTAMP())";
        query($sql);
        $sql = "insert into kl_cards values (null, '" . $titulo . formataReais($vacaria). "', 54, '', 'primary', 'BIANCHINI', '', 0.00, CURRENT_TIMESTAMP())";
        query($sql);
        $sql = "insert into kl_cards values (null, '" . $titulo . formataReais($algusto) . "', 55, '', 'primary', 'BIANCHINI', '', 0.00, CURRENT_TIMESTAMP())";
        query($sql);
        redireciona('index.php?menu=bianchini.compras.index');
    }
}