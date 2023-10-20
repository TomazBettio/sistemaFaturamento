<?php
/*
 * Data Criacao: 19/05/2020
 * Autor: Emanuel & bcs
 *
 * Descricao: Faz Backup do banco de dados
 */
if (! defined('TWSiNet') || ! TWSiNet)
    die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

include_once '/var/www/twsfw4/includes/aws/aws.phar';

class backup
{
    protected $_log = 'log_backup_bd.txt';
    protected $_tabela = "Log de Backup do Banco de Dados";

    var $funcoes_publicas = array(
        'index' => true,
        'schedule' => true
    );

    var $_nome_arquivo;

    function __construct()
    {        
        
    }

    function index()
    {
        $param = array(
            'width'     => 'AUTO',            
            'info'      => false,
            'filter'    => false,
            'ordenacao' => true,
            'titulo'    => $this->_tabela
        );
        
        $tabela = new tabela01($param);
        $tabela->addColuna(array('campo' => 'mensagem',       'etiqueta' => 'Tipo de Backup',  'tipo' => 'T',  'width' => 100,  'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'dia',            'etiqueta' => 'Data do Backup',  'tipo' => 'D',  'width' => 100,  'posicao' => 'E'));
        $tabela->addColuna(array('campo' => 'hora',           'etiqueta' => 'Hora do Backup',  'tipo' => 'T',  'width' => 100,  'posicao' => 'E'));
        /*
        $botao = array(
        'cor' 	=> 'sucesse',
        'texto' 	=> 'Fazer Backup',
        'id'		=> 'backup',
        "onclick"	=> "setLocation('" . getLink() . "schedule&index=true')");
        
        $tabela->addBotaoTitulo($botao);*/
        
       // 
       $dados = [];
       $dados = $this->getDados();
     //  var_dump($dados);
     //  die();
        $tabela->setDados($dados);
        
        $index = getParam($_GET, 'index', '');
        
        
        if(''!=$index){
           $this->schedule();
        }
        return $tabela;
    }

    public function schedule()
    {
        global $config;
       
        $index = getParam($_GET, 'index', '');

        if ($this->criarBKP()) { //O DEFAULT É POR DIRETÓRIO
            if (! empty($config['backup']['s3']['flag']) && false) {
                // Salva em s3
                $this->enviaS3();
                log::gravaLog($this->_log, "Backup por S3");
            } else if (! empty($config['backup']['ftp']) && false) {
                // Salva em ftp
                $this->fazFTP();
                log::gravaLog($this->_log, "Backup por FTP");
            } else if (! empty($config['backup']['dir']) && true) {
                // Salva em diretório
                log::gravaLog($this->_log, "Backup por diretório");
               // logAcesso("BACKUP POR DIRETORIO", 9);
            }
        } else{
            echo("ERRO AO FAZER BACKUP");
        }

        if ('' != $index) {
            // Retorna à index
            return $this->index();
        }    
    }

    // ***********************************
    // FUNÇÕES PARA SALVAR NO S3
    // ***********************************
    private function enviaS3()
    {
        global $config;
        // Create an S3 client
        $client = new \Aws\S3\S3Client([
            'region' => 'us-east-1',
            'version' => '2006-03-01',
            'credentials' => [
                'key' => $config['backup']['s3']['k'],
                'secret' => $config['backup']['s3']['s']
            ]
        ]);

        // Where the files will be source from
       // $source = '/var/www/twsfw/backup/cliente000011';
        $source = $config['temp'] .'/backup/';

        // Where the files will be transferred to
        $dest = 's3://tws-intranet';

        // Create a transfer object
        $manager = new \Aws\S3\Transfer($client, $source, $dest);

        echo "Chegamos até aqui<br>";
        // Perform the transfer synchronously
        $manager->transfer();
        echo "E fizemos uma transferência<br>";
    }

    // ***********************************
    // FUNÇÕES PARA SALVAR NO DIRETÓRIO
    // ***********************************
    // Linha de comando para salvar no diretório. Retorna TRUE se o arquivo foi criado, FALSE caso contrário
    private function criarBKP()
    {
        $os = php_uname();
        strpos(php_uname(), "Windows");
        $comando = '';
        echo($os);
        if (stripos($os, "Windows",0) !== FALSE) {
            $comando = $this->getComandoWin();
         //   echo("backup por windows");
        } elseif (stripos($os, "Linux",0) !== FALSE) {
            $comando = $this->getComandoLinux();
            
         //   echo("backup por linux");
        }

        if (trim($comando) != '') {
            shell_exec($comando);
            return true;
        } else {
            return false;
        }
    }

    private function getComandoLinux()
    {
        global $config;
        $ret='';
        // cria o comando para criar o bkp no linux
        $ret .= 'mysqldump -u ' . $config['db_usuario'] . ' -p';
        if (! empty($config['db_senha'])) {
            $ret .= $config['db_senha'] . ' ';
        }
        
        $pasta = $config['backup']['dir'];///$config['temp'] . '/backup/';
        //Se a pasta de backup não existe, é necessário criá-la:
        clearstatcache();
        if(!file_exists($pasta))
        {
            //echo('TENHO Q FAZER A PASTA!!!!' . $pasta);
            mkdir($pasta. DIRECTORY_SEPARATOR . 'arquivos', 0777, true);
            chMod($pasta . DIRECTORY_SEPARATOR . 'arquivos', 0777);
        }
        $ret .= $config['db_database'] . ' > ' . $config['backup']['dir'] . $this->_nome_arquivo . '.sql';
        return $ret;
    }

    private function getComandoWin()
    {
        // cria o comando para criar o bkp no windows
        global $config;

        $ret .= 'cd C:\xampp\mysql\backup ' . "\n" . 'C:\xampp\mysql\bin\mysqldump ' . $config['db_database'] . ' -u ';
        $ret .= $config['db_usuario'];
        if (! empty($config['db_senha'])) {
            $ret .= '-p ';
            $ret .= $config['db_senha'];
        }
        //No Windows, a pasta é criada automaticamente se ela não existir
        $ret .= ' > ' . $config['backup']['dir']  . $this->_nome_arquivo . '.sql' . "\n" . 'pause';
        return $ret;
    }


    // ***********************************
    // FUNÇÕES PARA SALVAR EM FTP
    // ***********************************
    // $file é o arquivo (com caminho) a ser enviado por ftp, $remote_file é o arquivo a ser recebido no servidor (Exemplo:/temp/arquivo.sql)
    private function fazFTP()
    {
        global $config;
        // server, usuário e senha
        $ftp_server = ""; // "ftp.gauchafarma.com.br";
        $ftp_user = ""; // 'gauchafarma1';
        $ftp_pass = "";

        // set up a connection or die
        $conn_id = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server");

        // try to login
        if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            echo "Conectou em $ftp_user@$ftp_server<br>";

            // o arquivo para transferência:
            $file = $config['temp'] .'/backup/' . $this->_nome_arquivo . '.sql';
            $remote_file = $config['backup']['dir'] . $this->_nome_arquivo . '.sql';

            // upload
            if (ftp_put($conn_id, $remote_file, $file, FTP_BINARY)) {
                echo "successfully uploaded $file<br>";
            } else {
                echo "There was a problem while uploading $file<br>";
            }
        } else {
            echo "ERRO DE CONEXÃO EM $ftp_user<br>";
        }
        // close the connection
        ftp_close($conn_id);
    }

    // ******************************
    // CRIAÇÃO DO LOG
    // ******************************
    
   
    
    private function getDados()
    {
        global $config;
        $ret = array();
        //$campos = array ('mensagem','dia','hora');
        $path = $config['debugPath'].$this->_log.'.log';
        $file = fopen($path,'r');
        do {
            $line = fgets($file);
            
            $temp = array();
            $elemento_log = explode(' - ', $line);
            if (is_array($elemento_log) && count($elemento_log) > 1) {
            // 1o elemento é a data
            // 2o elemento é hora + mensagem (separado por espaço)
                $temp['dia'] = $elemento_log[0];
                // Caso haja outro(s) ' - ' na mensagem
                if (count($elemento_log) != 2) {
                    $i = 2;
                    while (isset($elemento_log[$i])) {
                        $elemento_log[1] .= ' - '.$elemento_log[$i];
                        $i ++;
                    }
                }
                $aux = explode(' ', $elemento_log[1]);
                $temp['hora'] = $aux[0];

                $i = 2;
                while (isset($aux[$i])) {
                    $aux[1] .= ' '.$aux[$i];
                    $i ++;
                }

                $temp['mensagem'] = $aux[1];
                $ret[] = $temp;
            }
                
        } while($line != null);
        
        fclose($file);
        
      /*  $sql = "select * from syslog where id_log = 9 order by dia,hora";
        $rows = query($sql);
        if (is_array($rows) && count($rows) > 0) {
            $campos = array(
                'usuario',
                'mensagem',
                'dia',
                'hora'
            );
            foreach ($rows as $row) {
                $temp = array();
                foreach ($campos as $campo) {
                    $temp[$campo] = $row[$campo];
                }
                $ret[] = $temp;
            }
        }*/
        return $ret;
    }    
}