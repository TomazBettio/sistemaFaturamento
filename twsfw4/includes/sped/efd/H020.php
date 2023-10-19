<?php
/**
 * REGISTRO H020: INFORMAÇÃO COMPLEMENTAR DO INVENTÁRIO
 * Este registro deve ser preenchido para complementar as informações do
 * inventário, quando o campo MOT_INV do registro H005 for de “02” a “05”.
 * Não informar se o campo 03 (VL_INV) do registro H005 for igual a “0” (zero).
 * No caso de mudança da forma de tributação do ICMS da mercadoria
 * (MOT_INV=2 do H005), somente deverá ser gerado esse registro para os itens
 * que sofreram alteração da tributação do ICMS.
 */
class H020 extends Elementos
{
	private $_bloco = 'H020';
	private $_nivel = 4;
	private $_pai = 'H010';
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_parametros = [
        		'CST_ICMS' => [
        				'type'     => 'string',
        				'regex'    => '^[0-9]{3}$',
        				'required' => true,
        				'info'     => 'Código da Situação Tributária referente ao ICMS, '
        				. 'conforme a Tabela indicada no item 4.3.1',
        				'format'   => ''
        		],
        		'BC_ICMS' => [
        				'type'     => 'numeric',
        				'regex'    => '^\d+(\.\d*)?|\.\d+$',
        				'required' => true,
        				'info'     => 'Informe a base de cálculo do ICMS',
        				'format'   => '15v2'
        		],
        		'VL_ICMS' => [
        				'type'     => 'numeric',
        				'regex'    => '^\d+(\.\d*)?|\.\d+$',
        				'required' => true,
        				'info'     => 'Informe o valor do ICMS a ser debitado ou creditado',
        				'format'   => '15v2'
        		]
        ];
    }
}
