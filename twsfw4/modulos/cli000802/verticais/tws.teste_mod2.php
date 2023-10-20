<?php
/*
 * Data Criação: 04/05/2023
 * Autor: BCS
 *
 *Controle de reuniões
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class teste_mod2{
    
    var $funcoes_publicas = array(
        'index' 		=> true,
        'salvar'        => true,
        'excluir'       => true,
    );
    
   
    public function __construct(){
        
    }
    
   
    
    public function index(){
        $ret = '';
        
        $param = array(
            'opcao'     => 0,
            'form'      => [],
            'nome'      => 'formTeste',
            'titulo'    => 'TESTE',
            'urlExcluir' => getLink().'excluir',
        );
        $rel = new form_modelo2($param);
        $rel->addCampoForm(['id' => '', 'campo' => 'obs', 'etiqueta' => 'Obs', 'tipo' => 'TA' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => ''	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true]);
        $rel->addCampoForm(['id' => '', 'campo' => 'texto'        , 'etiqueta' => 'campo1'         , 'tipo' => 'T' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => ''	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true]);
        
        //Todos os addColunaTab já viram campos de input automaticamente
        $rel->addColunaTab(['campo' => 'prazo'      , 'etiqueta' => 'PRazo'       , 'tipo' => 'D', 'width' =>  160, 'posicao' => 'E']);
        $rel->addColunaTab(['campo' => 'cliente'      , 'etiqueta' => 'Cliente'       , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E']);
        $rel->addColunaTab(['campo' => 'total'      , 'etiqueta' => 'tot'       , 'tipo' => 'N', 'width' =>  160, 'posicao' => 'E']);
        
        //campos hidden no formulário
        $rel->addCampoHidden('oculto', true);
        
        //campo de coluna que vai ter opção de array
        $param = [
            'campo' => 'opcao'      , 
            'etiqueta' => 'Várias Opções'       , 
            'tipo' => 'A', //Tipo é Array
            'width' =>  160, 
            'posicao' => 'E',
            //Precisa da lista de opções
            'lista' => [
                ['',''],
                ['OP1','Primeira Opção'],
                ['OP2','Segunda Opção']
            ]
        ];
        $rel->addColunaTab($param);
        
        //Campos extras para o modal
        $param = [
            'id' => '', 
            'campo' => 'extra', 
            'etiqueta' => 'Extra', 
            'tipo' => 'T' 	, 
            'tamanho' => '15', 
            'linhas' => '', 
            'valor' => ''	, 
            'pasta'	=> 0, 
            'lista' => ''	, 
            'validacao' => '', 
            'largura' => 4, 
            'obrigatorio' => true];
        $rel->addCampoModal($param);
        
        $param = [
            'id' => '',
            'campo' => 'novo',
            'etiqueta' => 'Novo',
            'tipo' => 'T' 	,
            'tamanho' => '15',
            'linhas' => '',
            'valor' => ''	,
            'pasta'	=> 0,
            'lista' => ''	,
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false];
        $rel->addCampoModal($param);
        
        $rel->setEnvio(getLink() . 'salvar');
        
        $dados = [
            [
                'cliente'=>'teste1',
                'prazo'=>20220404,
                'total'=>11,
                'id'=>0,
                ]
        ];
        
        $rel->setDadosTab($dados);
        
        $ret .= $rel;
        return $ret;
    }
    
    public function salvar(){
        var_dump($_POST);die();
    }
    
    public function excluir(){
        echo "Tentei excluir a linha da tabela"; die();
    }
    
    
}