<?php

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class teste_pdf {
    var $funcoes_publicas = array(
        'index'             => true,
    );
    
    //Array de palavras a buscar no pdf
    private $_buscados =[];

    function __construct() {
        $this->_buscados = array("a", "e", "PDF", "linha");
    }

    public function index() {
        $html = '<form method="POST" action="" enctype="multipart/form-data">
                    <input type="file" name="arquivo" id="arquivo">

                    <input type="submit" value="Enviar"><br><br>
                </form>';

        if(!empty($_FILES)) {
            $arquivo = $_FILES['arquivo'];
    
            //print_r($arquivo);
    
            $local = '/var/www/twsfw4_dev/includes/pdfparser/arquivos/';
            if(move_uploaded_file($arquivo['tmp_name'], $local.$arquivo['name']) ) {
                $meu_pdf = file_get_contents($local.$arquivo['name']);
                $meu_pdf = $this->lerArquivo($meu_pdf);
                
                $param = array(
                    'width'     => 'AUTO',
                    'info'      => false,
                    'filter'    => false,
                    'ordenacao' => false,
                    'titulo'    => 'Exemplo'
                );
               
                $tabela = new tabela01($param);
                $tabela->addColuna(array('campo' => 'palavra',       'etiqueta' => 'Palavra',  'tipo' => 'T',  'width' => 100,  'posicao' => 'E'));
                $tabela->addColuna(array('campo' => 'total',       'etiqueta' => 'Nro de Vezes que Aparece no Texto',  'tipo' => 'T',  'width' => 100,  'posicao' => 'E'));
                $tabela->addColuna(array('campo' => 'local',       'etiqueta' => 'Índices',  'tipo' => 'T',  'width' => 100,  'posicao' => 'E'));
                $dados_f=[];
                $dados=$this->getConteudoTexto($meu_pdf);
               // var_dump($dados);die();
                //Tratamento dos dados
                foreach($dados as $palavra=>$array)
                {
                    $temp['palavra'] = $palavra;
                    $temp['total'] = count($array);
                    if($temp['total']>1){
                        $temp['local'] = implode(', ', $array);
                    } else{
                        $temp['local'] = $array[0];
                    }
                    
                    $dados_f[]=$temp;
                }
                $tabela->setDados($dados_f);
                $html.=$tabela;
                
               // echo($meu_pdf);
                
                
                 $html .= nl2br($meu_pdf);
            } else {
                echo 'não enviou';
            }
    
        }

    
        return $html;
    }

    private function lerArquivo($arquivo) {
        $ret='';
        // Carregar composer
        require_once '/var/www/twsfw4_dev/includes/pdfparser/vendor/autoload.php';

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseContent($arquivo);
        $ret = $pdf->getText();
        
        //var_dump($pdf->getText());
        //O retorno de getText() é uma string com o conteúdo do PDF

    //    $text = nl2br($pdf->getText());
        return $ret;
    }
    
    private function getConteudoTexto($texto)
    {
        //Função busca no texto instâncias de $_buscados,
        //Retorna um array de arrays, onde cada palavra é um array seus índices no texto
        $ret=[];
        if(is_string($texto) && strlen($texto)>0 && count($this->_buscados)>0){
            foreach($this->_buscados as $ind)
            {
                $ret[$ind] = $this->buscaTexto($ind, $texto);
            }
        }
        return $ret;
    }
    
    private function buscaTexto($substring,$texto)
    {
        //A função busca uma substring no texto dado, 
        //retornando um array com as posições de início dessa substring no texto para referência
        $ret = [];
        if(is_string($texto) && strlen($texto)>0){
            $posi = 0;
            while(($posi = strpos($texto,$substring,$posi))!==false){
                $ret[] = $posi;
                $posi++;
            }
        }
        return $ret;
    }
}