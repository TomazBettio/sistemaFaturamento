    <?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class comparar_arquivos{
    var $funcoes_publicas = array(
        'schedule' 	=> true,
        'index'     => true,
    );
    
    private $_listaNegraGenerica;
    private $_linkComparar;
    private $_contato;
    private $_arquivosIgnorarMd5;
    private $_listaNegraEspecifica;
    private $_ignorarGit;
    
    public function __construct(){
        global $config;
        
        $this->_listaNegraGenerica = array(
            '.',
            '..',
            's3',
            'twsfw4',
            '.git',
            'logs',
            'logs_fw',
            '.settings',
            '.buildpath',
            '.gitignore',
            '.project',
            'temp',
            'config.php',
            '.htaccess',
            'tws.modelo_programa.php',
            'tws.modelo_relatorio.php',
        );
        
        $this->_listaNegraEspecifica = array(
            $this->limparBarras($config['verificar']['S3Imagens'] ?? ($config['baseS3'] . 'imagens')),
        );
        
        $this->_arquivosIgnorarMd5 = array(
            'ajax.php',
            'arquivos.php',
            'download.php',
            'index.php',
            'schedule.php',
            'login.php',
        );
        
        $this->_linkComparar = 'https://thiel.twslabs.com.br/temp/comparacao.txt';
        
        $this->_contato = 'emanuel.thiel@verticais.com.br';
        
        $this->_ignorarGit = array();
        
        if(isset($config['configGit'])){
            $this->mesclarConfig($config['configGit']);
        }
    }
    
    private function mesclarConfig($caminhoConfigGit){
        include $caminhoConfigGit;
        if(isset($configGit['listaNegraGenerica'])){
            foreach ($configGit['listaNegraGenerica'] as $caminho){
                $this->_listaNegraGenerica[] = $this->limparBarras($caminho);
            }
        }
        if(isset($configGit['listaNegraEspecifica'])){
            foreach ($configGit['listaNegraEspecifica'] as $caminho){
                $this->_listaNegraEspecifica[] = $this->limparBarras($caminho);
            }
        }
        if(isset($configGit['ignorarMd5'])){
            foreach ($configGit['ignorarMd5'] as $caminho){
                $this->_arquivosIgnorarMd5[] = $this->limparBarras($caminho);
            }
        }
        if(isset($configGit['ignorarGit'])){
            foreach ($configGit['ignorarGit'] as $caminho){
                $this->_ignorarGit[] = $this->limparBarras($caminho);
            }
        }
    }
    
    private function limparBarras($caminho){
        return str_replace(array('\\', '/'), array('||', '||'), $caminho);
    }
    
    public function index(){
        $this->alimentarBanco('');
        //$this->criarArquivo();
        $this->comparar();
    }
    
    public function schedule($param){
        if(!empty($param)){
            $temp = explode('|', $param);
            $operacao = $temp[0];
            if(in_array($operacao, array('arquivo', 'comparar'))){
                $this->_contato = $temp[1] ?? $this->_contato;
                $this->alimentarBanco($param);
                if ($operacao === 'arquivo'){
                    $this->criarArquivo();    
                }
                elseif ($operacao === 'comparar'){
                    $this->comparar();
                }
            }
        }
    }
    
    private function comparar(){
        $dados_exterior = $this->getDadosExterior();
        $dados_local = $this->getDadosLocal();
        $arquivos_faltando_local = array();
        $arquivos_extras_local = array();
        $arquivos_diferentes = array();
        foreach($dados_local as $md5 => $arquivo){
            if(in_array($arquivo, $dados_exterior)){
                if((!isset($dados_exterior[$md5]) || $dados_exterior[$md5] !== $arquivo) && !in_array($arquivo, $this->_arquivosIgnorarMd5)){
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
        
        $this->enviarEmail($arquivos_diferentes, $arquivos_extras_local, $arquivos_faltando_local);
    }
    
    private function enviarEmail($arquivos_diferentes, $arquivos_extras_local, $arquivos_faltando_local){
        if(count($arquivos_diferentes) > 0 || count($arquivos_extras_local) > 0 || count($arquivos_faltando_local) > 0){
            global $config;
            if(is_file($config['debugPath'].'arquivos_com_problemas.log')){
                unlink($config['debugPath'].'arquivos_com_problemas.log');
            }

            $titulo = "comparação dos arquivos cliente " . $config['cliente'];
            $mensagem = "Foram encontrados discrepâncias nos arquivos do cliente " . $config['cliente'];
            
            $dados = array();
            if(count($arquivos_diferentes) > 0){
                foreach ($arquivos_diferentes as $arq){
                    $dados[] = array(
                        'arquivo' => str_replace('||', '\\', $arq),
                        'motivo' => 'MD5 Diferente',
                    );
                    log::gravaLog('arquivos_com_problemas', $arq . ' - MD5 Diferente');
                }
            }
            if(count($arquivos_extras_local) > 0){
                foreach ($arquivos_extras_local as $arq){
                    $dados[] = array(
                        'arquivo' => str_replace('||', '\\', $arq),
                        'motivo' => 'Arquivo Extra',
                    );
                    log::gravaLog('arquivos_com_problemas', $arq . ' - Arquivo Extra');
                }
            }
            if(count($arquivos_faltando_local) > 0){
                foreach ($arquivos_faltando_local as $arq){
                    $dados[] = array(
                        'arquivo' => str_replace('||', '\\', $arq),
                        'motivo' => 'Arquivo Faltando',
                    );
                    log::gravaLog('arquivos_com_problemas', $arq . ' - Arquivo Faltando');
                }
            }
            
            if(is_array($dados) && count($dados) > 0){
                $relatorio = new relatorio01();
                $relatorio->addColuna(array('campo' => 'arquivo' , 'etiqueta' => 'Arquivo'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
                $relatorio->addColuna(array('campo' => 'motivo'  , 'etiqueta' => 'Situação'			, 'tipo' => 'T', 'width' =>  80, 'posicao' => 'E'));
                $relatorio->setDados($dados);
                $relatorio->enviaEmail($this->_contato, $titulo, array('msgIni' => $mensagem));
            }
        }
    }
    
    private function getDadosExterior(){
        $ret = array();
        global $config;
        $dados = file_get_contents($this->_linkComparar);
        if($dados === false){
            die('Erro ao recuperar os dados do servidor principal');
        }
        else{
            $dados = json_decode($dados, true);
            foreach ($dados as $d){
                if(strpos($d['arquivo'], 'modulos||cli') !== false || strpos($d['arquivo'], 'includes||cli') !== false || strpos($d['arquivo'], 'includes||tws.cli') !== false){
                    if(strpos($d['arquivo'], 'cli' . $config['cliente']) !== false){
                        $ret[$d['md5']] = $d['arquivo'];
                    }
                }
                elseif(isset($config['appNome']) && strpos($d['arquivo'], 'twsfw4||modulos||apps||') !== false){
                    if(strpos($d['arquivo'], 'twsfw4||modulos||apps||' . $config['appNome']) !== false){
                        $ret[$d['md5']] = $d['arquivo'];
                    }
                }
                elseif(!in_array($d['arquivo'], $this->_ignorarGit)){
                    if(strpos($d['arquivo'], 'config') !== false){
                        echo '<br> ' . $d['arquivo'] . ' não esta na lista';
                    }
                    $ret[$d['md5']] = $d['arquivo'];
                }
            }
        }
        return $ret;
    }
    
    private function getDadosLocal(){
        $ret = array();
        $sql = "select arquivo, md5 from sys900";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['md5']] = $row['arquivo'];
            }
        }
        return $ret;
    }
    
    private function criarArquivo(){
        global $config;
        $arquivo = ($config['tempPach'] ?? $config['tempUPD']) . "comparacao.txt";
        if(is_file($arquivo)){
            unlink($arquivo);
        }
        $sql = "select arquivo, md5 from sys900";
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
            file_put_contents($arquivo, json_encode($dados));
        }
    }
    
    private function alimentarBanco($param){
        global $config;
        
        $caminho = $param === 'arquivo' ? $config['verificar']['baseFW'] : $config['baseFW'];
        $arquivos_fw = $this->getAllArquivosFromPasta($caminho, 'twsfw4');
        //var_dump($arquivos_fw);
        
        $caminho = $param === 'arquivo' ? $config['verificar']['base'] : ($config['baseHTML'] ?? $config['base']);
        $arquivos_index = $this->getAllArquivosFromPasta($caminho, '', $caminho);
        //var_dump($arquivos_index);
        
        $caminho = $param === 'arquivo' ? $config['verificar']['baseS3'] : $config['baseS3'];
        $arquivos_s3 = $this->getAllArquivosFromPasta($caminho, 's3');
        //var_dump($arquivos_s3);
        
        $sql = "truncate sys900";
        query($sql);
        
        $dados = array($arquivos_fw, $arquivos_index, $arquivos_s3);
        $dados_incluir = array();
        $data_atual = date('Ymd');
        foreach ($dados as $pasta){
            foreach ($pasta as $arquivo){
                $temp = array(
                    "'" . str_replace(array('\\', '/'), array('||', '||'), $arquivo['arquivo']) . "'",
                    "'{$arquivo['md5']}'",
                    "'$data_atual'"
                );
                
                $dados_incluir[] = '(' . implode(', ', $temp) . ')';
            }
        }
        
        if(is_array($dados_incluir) && count($dados_incluir) > 0){
            $sql = "insert into sys900 values " . implode(', ', $dados_incluir);
            query($sql);
        }
    }
    
    private function getAllArquivosFromPasta($pasta, $resumir = '', $apagar = ''){
        $ret = array();
        if(!empty($resumir) || !empty($apagar)){
            $arquivos = scandir($pasta);
            $arquivos = $this->limparListaNegra($arquivos, $pasta);
            foreach ($arquivos as $arq){
                if(is_dir($pasta.$arq)){
                    $ret = array_merge($ret, $this->getAllArquivosFromPasta($pasta.$arq . DIRECTORY_SEPARATOR, $resumir, $apagar));
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
                        //'md5' => md5_file($pasta.$arq)
                        'md5' => $this->calcularMd5Manual($pasta.$arq),
                    );
                }
            }
        }
        return $ret;
    }
    
    private function calcularMd5Manual($caminho){
        $ret = '';
        $data = file_get_contents($caminho);
        $data = str_replace("\r", '', $data);
        $ret = md5($data);
        return $ret;
    }
    
    private function limparListaNegra($arquivos, $pasta){
        foreach ($this->_listaNegraGenerica as $apagar){
            unset($arquivos[array_search($apagar, $arquivos, true)]);
        }
        
        $caminho = $this->limparBarras($pasta);
        $apagar = array();
        foreach ($arquivos as $arq){
            if(in_array($caminho . $arq, $this->_listaNegraEspecifica)){
                $apagar[] = $arq;
            }
        }
        if(is_array($apagar) && count($apagar) > 0){
            foreach ($apagar as $ap){
                unset($arquivos[array_search($ap, $arquivos, true)]);
            }
        }
        
        return $arquivos;
    }
}