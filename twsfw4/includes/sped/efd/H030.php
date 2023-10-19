<?php
/**
 * REGISTRO H030: Informações complementares do inventário das mercadorias sujeitas ao
 *0 regime de substituição tributária
 *
 */
class H030 extends Elementos
{
	private $_bloco = 'H030';
	private $_nivel = 4;
    private $_pai = 'H010';

    public function __construct()
    {
        parent::__construct();
        
        $this->_parametros = [
        		'VL_ICMS_OP' => [
        				'type'     => 'numeric',
        				'regex'     => '',
        				'required' => true,
        				'info'     => 'Valor médio unitário do ICMS OP',
        				'format'   => '15v6'
        		],
        		'VL_BC_ICMS_ST' => [
        				'type'     => 'numeric',
        				'regex'    => '^\d+(\.\d*)?|\.\d+$',
        				'required' => true,
        				'info'     => 'Valor médio unitário da base de cálculo do ICMS ST',
        				'format'   => '15v6'
        		],
        		'VL_ICMS_ST' => [
        				'type'     => 'numeric',
        				'regex'    => '^\d+(\.\d*)?|\.\d+$',
        				'required' => true,
        				'info'     => 'Valor médio unitário do ICMS ST',
        				'format'   => '15v2'
        		],
        		'VL_FCP' => [
        				'type'     => 'numeric',
        				'regex'    => '^\d+(\.\d*)?|\.\d+$',
        				'required' => true,
        				'info'     => 'Valor médio unitário do FCP',
        				'format'   => '15v6'
        		],
        ];
    }
}
