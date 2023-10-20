<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class calcValTransportadora{
    var $funcoes_publicas = array(
        'index'     => true,
    );
    
    public function index(){
        $dados = [
            'transportadora' => 'COMAR',
            'codigos' => ['19550', '19549', '19182'],
            'minimo' => 0,
            'base' => 2.5,
            'niveis' => [
                ['minimo' => 15000, 'maximo' => 50000, 'porcentagem' => 2.25],
                ['minimo' => 50000,  'porcentagem' => 1.8],
            ]
        ];
        
        $sql = "
SELECT TRASPORTADORA
	,GREATEST(LIQUIDO * P, vl_minimo)
FROM (
	SELECT TRASPORTADORA
		,5 AS vl_minimo
		,LIQUIDO
		,";
        if(isset($dados['niveis']) && is_array($dados['niveis']) && count($dados['niveis']) > 0){
            $sql .= "CASE 
                ";
            foreach ($dados['niveis'] as $nivel){
                if(isset($nivel['maximo'])){
                    
                }
            }
            
            $sql .= "ELSE " . ($dados['base'] / 100) . "
			END";
        }
        else{
            $sql .= ($dados['base'] / 100);
        }
            
	$sql .= " AS P	
	 FROM (
		SELECT 'SOFTLOG' AS TRASPORTADORA
			,ROUND((PCNFSAID.VLTOTAL - PCNFSAID.ICMSRETIDO - NVL(PCNFSAID.VLOUTRASDESP, 0)), 2) AS LIQUIDO
		FROM Pcnfsaid
		WHERE DTSAIDA BETWEEN TO_DATE('20230601', 'YYYYMMDD')
				AND TO_DATE('20230630', 'YYYYMMDD')
			AND CODFORNECFRETE = 19514
		)
	)
";
    }
}