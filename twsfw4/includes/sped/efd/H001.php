<?php
/**
 * REGISTRO H001: ABERTURA DO BLOCO H
 * Este  registro  deve  ser  gerado  para  abertura  do  bloco  H,  indicando
 * se  há  registros  de  informações  no  bloco.
 * Obrigatoriamente deverá ser informado “0” no campo IND_MOV no período de
 * referência fevereiro de cada ano.
 * Contribuinte que apresente inventário com periodicidade anual ou trimestral,
 * caso apresente o inventário de 31/12 na EFD ICMS IPI de dezembro ou janeiro,
 * deve repetir a informação na escrituração de fevereiro.
 */
class H001 extends Elementos
{
    private $_bloco = 'H001';
    private $_nivel = 1;
    private $_pai = '';
    
    public function __construct()
    {
    	parent::__construct();
    	
    	$this->_parametros = [
    			'IND_MOV' => [
    					'type'     => 'numeric',
    					'regex'    => '^[0-1]{1}$',
    					'required' => true,
    					'info'     => 'Indicador de movimento: '
    					. '0- Bloco com dados informados; '
    					. '1- Bloco sem dados informados',
    					'format'   => ''
    			]
    	];
    }
}
