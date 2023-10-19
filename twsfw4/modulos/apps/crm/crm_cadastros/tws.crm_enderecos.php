<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class crm_enderecos extends cad01{
    function __construct(){
        $param = array();
        parent::__construct('crm_enderecos', $param);
    }
    
    public function CriarCampoVirtualEnderecos($id_pai, $tabela_pai, $entidade){
        $ret = '';
        
        $this->_mostrarMensagens = false;
        
        $dados_extras = array(
            'seq' => 1,
            'entidade' => $entidade,
        );
        $form1 = $this->criarCampoVirtual($id_pai, $tabela_pai, 'Endereço 1', $dados_extras, 'endereco1');
        
        $dados_extras = array(
            'seq' => 2,
            'entidade' => $entidade,
        );
        $form2 = $this->criarCampoVirtual($id_pai, $tabela_pai, 'Endereço 2', $dados_extras, 'endereco2');
        
        $ret = $form1 . $form2;
        return $ret;
    }
    
    public function SalvarCampoVirtualEnderecos($form, $tabela_pai){
        $this->_mostrarMensagens = false;
        $dados_diretos = array(
            'seq' => 1, 
        );
        $this->salvarCampoVirtual($form, 'endereco1', $tabela_pai, $dados_diretos);
        $dados_diretos = array(
            'seq' => 2,
        );
        $this->salvarCampoVirtual($form, 'endereco2', $tabela_pai, $dados_diretos);
    }
}
    /*
campo virtual =>    'crm_cadastros.crm_enderecos.criarCampoVirtualUnico',@@codigo, $this->_tabela, 'Endereço 1',array('seq' => 1, 'entidade' => 'cat'), 'endereco1'
salvar campo virtuak => 'crm_cadastros.crm_enderecos.salvarCampoVirtual',$formCrud,'endereco1', 'crm_categorias', array('seq' => 1, 'entidade' => 'cat')
sys023 => 3
crm_categorias
crm_enderecos
NULL
seq
NULL
NULL
seq
*/


