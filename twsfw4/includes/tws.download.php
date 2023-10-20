<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class download{
    public $_modulo;
    public $_classe;
    public $_metodo;
    public $_operacao;
    
    //Conteudo proncipal
    private $_retorno;
    
    //TAGs META
    private $_meta = [];
    
    //funcao que retorna o nome do arquivo a ser baixado
    private $_funcao_arquivo = 'baixarArquivo';
    
    function __construct(){
        set_time_limit(0);
        //$_REQUEST	= stripslashes_deep($_REQUEST);
        //$_POST	= stripslashes_deep($_POST);
        //$_GET		= stripslashes_deep($_GET);
    }
    
    public function __toString(){
        $ret = '';
        
        if(isset($_GET['menu']) && (preg_match('/^[A-Za-z0-9_]+\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+$/',$_GET['menu']) || preg_match('/^[A-Za-z0-9_]+\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+$/',$_GET['menu'])	)){
            @list($this->_modulo,$this->_classe,$this->_metodo,$this->_operacao) = explode('.',$_GET['menu']);
            $this->_metodo = 'arquivos';
            
            if(empty($this->_modulo)  || empty($this->_classe)){
                return $ret;
            }
        }else{
            return $ret;
        }
        $programaLink = $this->_modulo.'.'.$this->_classe;
        //echo "{$this->_modulo},{$this->_classe},{$this->_metodo},{$this->_operacao} <br>\n";
        if($this->verificaPermissaoUsuario($programaLink)){
            $obj =&CreateObject($this->_modulo.'.'.$this->_classe );
            print_r($obj->funcoes_publicas);
            if(is_array($obj->funcoes_publicas) && isset($obj->funcoes_publicas[$this->_funcao_arquivo])){
                $metodo = $this->_funcao_arquivo;
                $arquivo = $obj->$metodo();
                if($arquivo === false){
                    //o usuário não tem acesso ao arquivo
                    $ret = 'Você não tem permissão para ler esse arquivo';
                }
                else{
                    //o usuário tem acesso ao arquivo
                    header("Content-Type: application/octet-stream");
                    header("Content-Disposition: attachment; filename={$arquivo['nome']}");
                    header("Content-Length: " . filesize($arquivo['caminho']));
                    
                    $fp = fopen($arquivo['caminho'], 'rb');
                    fpassthru($fp);
                }
            }
            else{
                log::logAcesso('Programa DOWNLOAD: '.$programaLink, 4);
                $ret = "Tentativa de acesso a funcao nao publica ou inexistente - DOWNLOAD<br>";
            }
            
        }else{
            log::logAcesso('Programa DOWNLOAD - Sem Permissao: '.$programaLink, 4);
            $ret = "Tentativa de acesso a funcao sem permissão - DOWNLOAD<br>";
        }
        if($ret != ''){
            header('Content-type: text/html; charset=utf-8');
        }
        
        return $ret;
    }
    
    //------------------------------------------------------------------ Uteis
    
    private function verificaPermissaoUsuario($programa){
        $ret = false;
        
        $sql = "SELECT programa, perm FROM sys115 WHERE user = '".getUsuario()."' AND programa LIKE '".$programa."%' AND perm = 'S'";
        $rows = query($sql);
        if(isset($rows[0][0])){
            $ret = true;
        }
        
        //Verifica se não é um programa liberado
        if(!$ret){
            $sql = "SELECT programa, perm FROM sys116 WHERE programa LIKE '".$programa."%' AND perm = 'S'";
            $rows = query($sql);
            if(isset($rows[0][0])){
                $pos = strrpos($rows[0][0], '.');
                if(substr($rows[0][0], 0,$pos) == $programa && $rows[0][1] == 'S'){
                    $ret = true;
                }
            }
            
        }
        
        return $ret;
    }
    
    /**
     * Verifica se o usuario est� logado
     */
    function logado(){
        global $app;
        $ret = false;
        
        if($app->logado()){
            $ret = true;
        }
        
        return $ret;
    }
    
    //------------------------------------------------------------------ Adiciona Recursos
    
    public function addMeta($conteudo){
        if(!empty($conteudo)){
            $this->_meta[] = $conteudo;
        }
    }
    
    // Funções que devem existir para não dar problema ao gerar o __construct das classes
    
    public function addJavascript($string, $posicao = 'I'){}
    
    public function setConteudoFooter($conteudo = ''){}
    
    public function addBodyClass($class){}
    
    public function addFooterClass($class){}
    
    public 	function addJquery($linha, $posicao = 'I'){}
    
    public function addScript($tipo, $link, $local = 'I', $indice = ''){}
    
    public function addStyle($tipo, $link, $local = 'I', $indice = ''){}
    
}