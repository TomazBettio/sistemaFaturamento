<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class sincronizar_arquivos{
    var $funcoes_publicas = array(
        'schedule' 	=> true,
        'index'     => true,
    );
    
    private $_linkComparar;
    private $_pastaInsumos;
    private $_pastaMono;
    private $_listaNegraGenerica;
    
    public function __construct(){
        //$this->_linkComparar = "http://192.168.1.164/temp/sincronizar.txt";
        $this->_linkComparar = "http://192.168.1.164/sincronizarDocumentos.php";
        //$this->_linkComparar = "http://192.168.1.216/temp/arquivos.txt";
        
        global $config;
        $this->_pastaInsumos = $config['verificar']['insumos'];
        $this->_pastaMono = $config['verificar']['mono'];
        $this->_listaNegraGenerica = array(
            '.',
            '..',
        );
    }
    
    public function index(){
        set_time_limit(0);
        $this->alimentarBanco('');
        $this->comparar();
    }
    
    public function schedule($param){
        $temp = explode('|', $param);
        $op = $temp[0];
        $this->alimentarBanco($op);
        if($op == 'arquivo'){
            $this->criarArquivo();
        }
        elseif ($op == 'comparar'){
            $this->comparar();
        }
        elseif($op == 'echo'){
            $this->criarArquivo(true);
        }
    }
    
    private function getContratosUsados(&$dados_externos){
        $ret = array();
        $temp = array();
        foreach ($dados_externos as $arq){
            $temp[$this->getContrato($arq)] = '';
        }
        $ret = array_keys($temp);
        return $ret;
    }
    
    private function getContrato($arq){
        $ret = '';
        $pos_um = strpos($arq, '||');
        $pos_dois = strpos($arq, '||', $pos_um + 2);
        $ret = substr($arq, $pos_um + 2, $pos_dois - $pos_um - 2);
        return $ret;
    }
    
    private function comparar(){
        $dados_exterior = $this->getDadosExterior();
        $contratos = $this->getContratosUsados($dados_exterior);
        $dados_local = $this->getDadosLocal($contratos);
        $arquivos_faltando_local = array();
        $arquivos_extras_local = array();
        $arquivos_diferentes = array();
        foreach($dados_local as $md5 => $arquivo){
            if(in_array($arquivo, $dados_exterior)){
                if(!isset($dados_exterior[$md5]) || $dados_exterior[$md5] !== $arquivo){
                    $arquivos_diferentes[] = $arquivo;
                }
            }
            else{
                $arquivos_extras_local[] = $arquivo;
            }
        }
        
        foreach ($dados_exterior as $arquivo){
            if(!in_array($arquivo, $dados_local)){
                $arquivos_faltando_local[] = $arquivo;
            }
        }
        

        /*
        $temp = array(
            'md5' => $arquivos_diferentes,
            'extras' => $arquivos_extras_local,
            'faltando' => $arquivos_faltando_local,
        );
        
        var_dump($temp);
        */
        $data = date('Ymd');
        $horario = date('Gi');
        foreach ($arquivos_diferentes as $arq){
            //arquivos que foram alterados 
            
            //copia o atual para outra pasta
            $caminho_local_origem = '/var/www/intranet4/html/' . str_replace('||', '/', $arq);
            $caminho_local_destino = "/var/www/intranet4/html/alterados/$data/$horario/" . str_replace('||', '/', $arq);
            $this->criarPastas($caminho_local_destino);
            rename($caminho_local_origem, $caminho_local_destino);
            
            //baixa a versão nova
            $url = "http://192.168.1.164/" . str_replace('||', '/', $arq);
            $caminho_local = '/var/www/intranet4/html/' . str_replace('||', '/', $arq);
            copy($url, $caminho_local);
        }
        
        foreach ($arquivos_extras_local as $arq){
            //arquivos que foram excluidos ou trocaram de nome
            echo '<br>-------------------------';
            $caminho_local_origem = '/var/www/intranet4/html/' . str_replace('||', '/', $arq);
            $caminho_local_destino = "/var/www/intranet4/html/excluidos/$data/$horario/" . str_replace('||', '/', $arq);
            echo "<br>arq: $arq *<br>origem: $caminho_local_origem<br>destino: $caminho_local_destino";
            $this->criarPastas($caminho_local_destino);
            rename($caminho_local_origem, $caminho_local_destino);
        }
        
        foreach ($arquivos_faltando_local as $arq){
            //arquivos novos
            $url = "http://192.168.1.164/" . str_replace('||', '/', $arq);
            $caminho_local = '/var/www/intranet4/html/' . str_replace('||', '/', $arq);
            $this->criarPastas($caminho_local);
            copy($url, $caminho_local);
        }
        //$this->enviarEmail($arquivos_diferentes, $arquivos_extras_local, $arquivos_faltando_local);
    }
    
    private function criarNomeVencido($arq, $data){
        $ret = '';
        $temp = explode('||', $arq);
        $ret = array_shift($temp) . '||' . $data . '||';
        $ret .= implode('||', $temp);
        return $ret;
    }
    
    private function criarPastas($caminho){
        $temp = explode('/', $caminho);
        unset($temp[count($temp) - 1]);
        unset($temp[0]);
        $caminho_total = '/';
        foreach ($temp as $pasta){
            $caminho_total .= $pasta;
            if(!is_dir($caminho_total) && !is_file($caminho_total)){
                mkdir($caminho_total);
            }
            $caminho_total .= '/';
        }
    }
    
    private function getDadosExterior(){
        $ret = array();
        $dados = file_get_contents($this->_linkComparar);
        if($dados === false){
            die('Erro ao recuperar os dados do servidor principal');
        }
        else{
            $dados = json_decode($dados, true);
            foreach ($dados as $d){
                $ret[$d['md5']] = $d['arquivo'];
            }
        }
        return $ret;
    }
    
    private function getDadosLocal($contratos = array()){
        $ret = array();
        $sql = "select arquivo, md5 from mgt_sincronizar_arquivos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            if(is_array($contratos) && count($contratos) > 0){
                foreach ($rows as $row){
                    $ret[$row['md5']] = $row['arquivo'];
                }
            }
            else{
                foreach ($rows as $row){
                    $contrato = $this->getContrato($row['arquivo']);
                    if(in_array($contrato, $contratos)){
                        $ret[$row['md5']] = $row['arquivo'];
                    }
                }
            }
        }
        return $ret;
    }
    
    private function gerarListaNegraInsumos(){
        $ret = array();
        $sql = "select REPLACE(REPLACE(REPLACE(cnpj, '/', ''), '.', ''), '-', '') as cnpj, REPLACE(contrato, '/', '-') as contrato from mgt_monofasico where status = 'arquivado'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[] = $row['cnpj'] . '_' . $row['contrato'];
            }
        }
        return $ret;
    }
    
    private function alimentarBanco($op){
        $horario = intval(date('H'));
        if(($op == 'arquivo' || $op == 'echo') && $horario < 20){
            $lista_negra = $this->gerarListaNegraInsumos(); 
            $arquivos_insumos = $this->getAllArquivosFromPasta($this->_pastaInsumos, 'insumos', '', $lista_negra);
            $arquivos_mono = $this->getAllArquivosFromPasta($this->_pastaMono, 'monofasico');
        }
        else{
            $arquivos_insumos = $this->getAllArquivosFromPasta($this->_pastaInsumos, 'insumos');
            $arquivos_mono = $this->getAllArquivosFromPasta($this->_pastaMono, 'monofasico');
        }
        $dados = array_merge($arquivos_insumos, $arquivos_mono);
        $sql = "truncate mgt_sincronizar_arquivos";
        query($sql);
        $dados_incluir = array();
        $data_atual = date('Ymd');
        foreach ($dados as $arquivo){
            $temp = array(
                "'" . str_replace(array('\\', '/'), array('||', '||'), $arquivo['arquivo']) . "'",
                "'{$arquivo['md5']}'",
                "'$data_atual'"
            );
            
            $dados_incluir[] = '(' . implode(', ', $temp) . ')';
        }
        
        if(is_array($dados_incluir) && count($dados_incluir) > 0){
            $sql = "insert into mgt_sincronizar_arquivos values " . implode(', ', $dados_incluir);
            query($sql);
        }
    }
    
    private function getAllArquivosFromPasta($pasta, $resumir = '', $apagar = '', $linta_negra_temp = array()){
        $ret = array();
        if(!empty($resumir) || !empty($apagar)){
            $arquivos = scandir($pasta);
            $arquivos = $this->limparListaNegra($arquivos, $pasta, $linta_negra_temp);
            foreach ($arquivos as $arq){
                if(is_dir($pasta.$arq)){
                    $ret = array_merge($ret, $this->getAllArquivosFromPasta($pasta.$arq . DIRECTORY_SEPARATOR, $resumir, $apagar, ''));
                }
                elseif(is_file($pasta.$arq)){
                    $caminho_resumido = $pasta.$arq;
                    if(!empty($resumir)){
                        $caminho_resumido = substr($caminho_resumido, strpos($caminho_resumido, $resumir));
                    }
                    if(!empty($apagar)){
                        $caminho_resumido = str_replace($apagar, '', $caminho_resumido);
                    }
                    $ret[] = array(
                        'camiho_completo' => $pasta.$arq,
                        'arquivo' => $caminho_resumido,
                        'md5' => md5_file($pasta.$arq)
                        //'md5' => $this->calcularMd5Manual($pasta.$arq),
                    );
                }
            }
        }
        return $ret;
    }
    
    private function limparListaNegra($arquivos, $pasta, $lista_negra_temp = array()){
        foreach ($this->_listaNegraGenerica as $apagar){
            unset($arquivos[array_search($apagar, $arquivos, true)]);
        }
        if(is_array($lista_negra_temp) && count($lista_negra_temp) > 0){
            foreach ($lista_negra_temp as $apagar){
                unset($arquivos[array_search($apagar, $arquivos, true)]);
            }
        }
        return $arquivos;
    }
    
    private function criarArquivo($echo = false){
        global $config;
        $arquivo = ($config['tempPach'] ?? $config['tempUPD']) . "sincronizar.txt";
        if(is_file($arquivo)){
            unlink($arquivo);
        }
        $sql = "select arquivo, md5 from mgt_sincronizar_arquivos";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $dados = array();
            foreach ($rows as $row){
                $temp = array(
                    'arquivo' => $row['arquivo'],
                    'md5' => $row['md5'],
                );
                $dados[] = $temp;
            }
            if($echo){
                echo json_encode($dados);
            }
            else{
                file_put_contents($arquivo, json_encode($dados));
            }
        }
    }
}