<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class configurar_traducoes{
    var $funcoes_publicas = array(
        'index'			=> true,
        'selectCad'		=> true,
        'editarSys003'     => true,
        'salvarSys003'  => true,
        'selectSys005'  => true,
        'editarSys005'  => true,
        'salvarSys005'  => true,
        'editarSys002'     => true,
        'salvarSys002'  => true,
        'EditarModulos' => true,
        'salvarModulos' => true,
        'EditarProgramas' => true,
        'salvarProgramas' => true,
    );
    
    public function index(){
        $ret = '';
        $param = [];
        $param['texto'] 	= 'Editar CADs';
        $param['width'] 	= 30;
        $param['onclick'] 	= "setLocation('".getLink()."selectCad')";
        $param['cor'] 		= 'success';
        $bt_cad = formbase01::formBotao($param);
        
        $ret = '';
        $param = [];
        $param['texto'] 	= 'Editar SYS005';
        $param['width'] 	= 30;
        $param['onclick'] 	= "setLocation('".getLink()."selectSys005')";
        $param['cor'] 		= 'success';
        $bt_sys005 = formbase01::formBotao($param);
        
        $ret = '';
        $param = [];
        $param['texto'] 	= 'Editar Módulos';
        $param['width'] 	= 30;
        $param['onclick'] 	= "setLocation('".getLink()."EditarModulos')";
        $param['cor'] 		= 'success';
        $bt_app001 = formbase01::formBotao($param);
        
        $ret = '';
        $param = [];
        $param['texto'] 	= 'Editar Programas';
        $param['width'] 	= 30;
        $param['onclick'] 	= "setLocation('".getLink()."EditarProgramas')";
        $param['cor'] 		= 'success';
        $bt_app002 = formbase01::formBotao($param);
        
        $param  = array(
            'tamanhos' => array(3, 3, 3, 3),
            'conteudos' => array($bt_cad, $bt_sys005, $bt_app001, $bt_app002),
        );
        $ret = addLinha($param);
        $param = [];
        $param['titulo'] = 'Editar Traduções';
        $param['conteudo'] = $ret;
        $ret = addCard($param);
        return $ret;
    }
    
    public function selectCad(){
        $ret = '';
        $param = array('titulo' => 'Escolha um CAD para configurar');
        $tabela = new tabela01($param);
        $tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T'));
        
        $param = [];
        $param['texto'] 	= 'Configurar Campos';
        $param['link'] 		= getLink().'editarSys003&tabela=';
        $param['coluna'] 	= 'tabela';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['cor'] 		= 'success';
        $tabela->addAcao($param);
        
        $param = [];
        $param['texto'] 	= 'Configurar Outros';
        $param['link'] 		= getLink().'editarSys002&tabela=';
        $param['coluna'] 	= 'tabela';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['cor'] 		= 'success';
        $tabela->addAcao($param);
        
        $param = [];
        $param['texto'] 	= 'Voltar';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 		= "setLocation('".getLink()."index')";
        $param['cor'] 		= 'danger';
        $tabela->addBotaoTitulo($param);
        
        $dados = $this->getDadosCad();
        $tabela->setDados($dados);
        
        $ret = $tabela . '';
        
        return $ret;
    }
    
    private function getDadosCad(){
        $ret = array();
        $sql = "select tabela, descricao from sys002 where tabela in (select distinct tabela from sys003)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array();
                $temp['tabela'] = $row['tabela'];
                $temp['descricao'] = $row['descricao'];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getLinguagens($etiqueta = true){
        $ret = array();
        $linguas = array('EN' => 'Inglês', 'SP' => 'Espanhol');
        if(!$etiqueta){
            $ret = array_keys($linguas);
        }
        else{
            $ret = $linguas;
        }
        return $ret;
    }
    
    private function getCampoSys003(){
        return array('etiqueta');
    }
    
    public function editarSys003(){
        $ret = '';
        $tabela = verificaParametro($_GET, 'tabela');
        $form = new form01();
        $pastas = array();
        foreach($this->getLinguagens() as $index => $etiqueta){
            $pastas[$index] = $etiqueta;
        }
        $form->setPastas($pastas);
        $pastas = $this->getLinguagens(false);
        $campos_sys003 = $this->getCampoSys003();
        $sql = "select * from sys003 where tabela = '$tabela'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $param = array(
                'tipo' => 'T',
                'largura' => 3,
            );
            $dados = $this->getTodasAsTraducoesSys003($tabela);
            $form->addHidden('formTraducao[tabela]', $tabela);
            foreach ($rows as $row){
                foreach ($campos_sys003 as $campo){
                    foreach ($pastas as $lingua){
                        $param['campo'] = "formTraducao[" . $row['campo'] . "][$campo][$lingua]";
                        $param['pasta'] = $lingua;
                        $param['etiqueta'] = $row[$campo];
                        $param['valor'] = isset($dados[$lingua][$row['campo']][$campo]) ? $dados[$lingua][$row['campo']][$campo] : '';
                        $form->addCampo($param);
                    }
                }
            }
            $form->setEnvio(getLink() . 'salvarSys003', 'formTraducao', 'formTraducao');
            $ret = $form . '';
            $param  = array(
                'titulo' => 'Traduzir SYS003 da tabela '. $tabela,
                'conteudo' => $ret,
            );
            $ret = addCard($param);
        }
        return $ret;
    }
    
    private function getTodasAsTraducoesSys003($tabela){
        $ret = array();
        $linguas = $this->getLinguagens(false);
        foreach ($linguas as $l){
            $ret[$l] = traducoes::getTodasAsTraducoesSys003($tabela, $l);
        }
        return $ret;
    }
    
    public function salvarSys003(){
        $retornoForm = verificaParametro($_POST, 'formTraducao', array());
        if(count($retornoForm) > 2){
            $tabela = $retornoForm['tabela'];
            unset($retornoForm['tabela']);
            foreach ($retornoForm as $campo => $temp1){
                foreach ($temp1 as $coluna => $temp2){
                    foreach ($temp2 as $lingua => $texto){
                        if($this->verificarValoresSys003($tabela, $campo, $coluna, $lingua) && !empty(trim($texto))){
                            $this->salvarEntradaSys003($tabela, $campo, $coluna, $lingua, $texto);
                        }
                    }
                }
            }
        }
        return $this->index();
    }
    
    private function verificarValoresSys003($tabela, $campo, $coluna, $lingua){
        $ret = false;
        if(in_array($coluna, $this->getCampoSys003())){
            if(in_array($lingua, $this->getLinguagens(false))){
                $sql = "select * from sys003 where tabela = '$tabela' and campo = '$campo'";
                $rows = query($sql);
                if(is_array($rows) && count($rows) > 0){
                    $ret = true;
                }
            }
        }
        return $ret;
    }
    
    private function salvarEntradaSys003($tabela, $campo, $coluna, $lingua, $texto){
        $codigo = traducoes::montarCodigoSys003($tabela, $campo, $coluna);
        $texto_traduzido = traducoes::traduzir($codigo, $lingua);
        if($texto_traduzido === ''){
            traducoes::salvarTraducao($codigo, $lingua, $texto);
        }
        else{
            traducoes::atualizarTraducao($codigo, $lingua, $texto);
        }
    }
    
    public function selectSys005(){
        $ret = '';
        $param = array('titulo' => 'Escolha uma tabela da SYS005 para traduzir');
        $tabela = new tabela01($param);
        $tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T'));
        
        $param = [];
        $param['texto'] 	= 'Configurar';
        $param['link'] 		= getLink().'editarSys005&tabela=';
        $param['coluna'] 	= 'tabela';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['cor'] 		= 'success';
        $tabela->addAcao($param);
        
        $param = [];
        $param['texto'] 	= 'Voltar';
        $param['width'] 	= 30;
        $param['flag'] 		= '';
        $param['onclick'] 		= "setLocation('".getLink()."index')";
        $param['cor'] 		= 'danger';
        $tabela->addBotaoTitulo($param);
        
        $dados = $this->getDadosSys005();
        $tabela->setDados($dados);
        
        $ret = $tabela . '';
        
        return $ret;
    }
    
    private function getDadosSys005(){
        $ret = array();
        $sql = "SELECT * FROM sys005 where tabela = '000000'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = array(
                    'descricao' => $row['descricao'],
                    'tabela' =>  $row['chave'],
                );
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function editarSys005(){
        $ret = '';
        $tabela = verificaParametro($_GET, 'tabela');
        $form = new form01();
        $pastas = array();
        foreach($this->getLinguagens() as $index => $etiqueta){
            $pastas[$index] = $etiqueta;
        }
        $form->setPastas($pastas);
        $pastas = $this->getLinguagens(false);
        $sql = "select * from sys005 where tabela = '$tabela'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $param = array(
                'tipo' => 'T',
                'largura' => 3,
            );
            $dados = $this->getTodasAsTraducoesSys005($tabela);
            $form->addHidden('formTraducao[tabela]', $tabela);
            foreach ($rows as $row){
                foreach ($pastas as $lingua){
                    $param['campo'] = "formTraducao[" . $row['chave'] . "][$lingua]";
                    $param['pasta'] = $lingua;
                    $param['etiqueta'] = $row['descricao'];
                    $param['valor'] = isset($dados[$lingua][$row['chave']]) ? $dados[$lingua][$row['chave']] : '';
                    $form->addCampo($param);
                }
            }
            $form->setEnvio(getLink() . 'salvarSys005', 'formTraducao', 'formTraducao');
            $form->setBotaoCancela(getLink()."selectSys005");
            $ret = $form . '';
            $param  = array(
                'titulo' => 'Traduzir SYS005',
                'conteudo' => $ret,
            );
            $ret = addCard($param);
        }
        return $ret;
    }
    
    private function getTodasAsTraducoesSys005($tabela){
        $ret = array();
        $linguas = $this->getLinguagens(false);
        foreach ($linguas as $l){
            $ret[$l] = traducoes::getTodasAsTraducoesSys005($tabela, $l);
        }
        return $ret;
    }
    
    public function salvarSys005(){
        $retornoForm = verificaParametro($_POST, 'formTraducao', array());
        if(count($retornoForm) > 2){
            $tabela = $retornoForm['tabela'];
            unset($retornoForm['tabela']);
            foreach ($retornoForm as $chave => $temp){
                foreach ($temp as $lingua => $texto){
                    if($this->verificarValoresSys005($tabela, $chave, $lingua) && !empty(trim($texto))){
                        $this->salvarEntradaSys005($tabela, $chave, $lingua, $texto);
                    }
                }
            }
        }
        return $this->index();
    }
    
    private function verificarValoresSys005($tabela, $chave, $lingua){
        $ret = false;
        if(in_array($lingua, $this->getLinguagens(false))){
            $sql = "select * from sys005 where tabela = '$tabela' and chave = '$chave'";
            $rows = query($sql);
            $ret = (is_array($rows) && count($rows) > 0);
        }
        return $ret;
    }
    
    private function salvarEntradaSys005($tabela, $chave, $lingua, $texto){
        $codigo = traducoes::montarCodigoSys005($tabela, $chave);
        $texto_traduzido = traducoes::traduzir($codigo, $lingua);
        if($texto_traduzido === ''){
            traducoes::salvarTraducao($codigo, $lingua, $texto);
        }
        else{
            traducoes::atualizarTraducao($codigo, $lingua, $texto);
        }
    }
    
    public function editarSys002(){
        $ret = '';
        $tabela = verificaParametro($_GET, 'tabela');
        $form = new form01();
        $pastas = array();
        foreach($this->getLinguagens() as $index => $etiqueta){
            $pastas[$index] = $etiqueta;
        }
        $form->setPastas($pastas);
        $pastas = $this->getLinguagens(false);
        $campos_sys002 = $this->getCamposSys002();
        $sql = "select * from sys002 where tabela = '$tabela'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $row = $rows[0];
            $param = array(
                'tipo' => 'T',
                'largura' => 3,
            );
            $dados = $this->getTodasAsTraducoesSys002($tabela);
            $form->addHidden('formTraducao[tabela]', $tabela);
            foreach ($campos_sys002 as $campo){
                foreach ($pastas as $lingua){
                    $param['campo'] = "formTraducao[$campo][$lingua]";
                    $param['pasta'] = $lingua;
                    $param['etiqueta'] = $row[$campo];
                    $param['valor'] = isset($dados[$lingua][$campo]) ? $dados[$lingua][$campo] : '';
                    $form->addCampo($param);
                }
            }
            $form->setEnvio(getLink() . 'salvarSys002', 'formTraducao', 'formTraducao');
            $ret = $form . '';
            $param  = array(
                'titulo' => 'Traduzir SYS002 da tabela '. $tabela,
                'conteudo' => $ret,
            );
            $ret = addCard($param);
        }
        return $ret;
    }
    
    private function getCamposSys002(){
        return array('descricao', 'unidade', 'etiqueta');
    }
    
    private function getTodasAsTraducoesSys002($tabela){
        $ret = array();
        $linguas = $this->getLinguagens(false);
        foreach ($linguas as $l){
            $ret[$l] = traducoes::getTodasAsTraducoesSys002($tabela, $l);
        }
        return $ret;
    }
    
    public function salvarSys002(){
        $retornoForm = verificaParametro($_POST, 'formTraducao', array());
        if(count($retornoForm) > 2){
            $tabela = $retornoForm['tabela'];
            unset($retornoForm['tabela']);
            foreach ($retornoForm as $campo => $temp){
                foreach ($temp as $lingua => $texto){
                    if($this->verificarValoresSys002($tabela, $campo, $lingua) && !empty(trim($texto))){
                        $this->salvarEntradaSys002($tabela, $campo, $lingua, $texto);
                    }
                }
            }
        }
        return $this->index();
    }
    
    private function verificarValoresSys002($tabela, $campo, $lingua){
        $ret = false;
        if(in_array($lingua, $this->getLinguagens(false))){
            $sql = "select $campo from sys002 where tabela = '$tabela'";
            $rows = query($sql);
            $ret = (is_array($rows) && count($rows) > 0);
        }
        return $ret;
    }

    private function salvarEntradaSys002($tabela, $campo, $lingua, $texto){
        $codigo = traducoes::montarCodigoSys002($tabela, $campo);
        $texto_traduzido = traducoes::traduzir($codigo, $lingua);
        if($texto_traduzido === ''){
            traducoes::salvarTraducao($codigo, $lingua, $texto);
        }
        else{
            traducoes::atualizarTraducao($codigo, $lingua, $texto);
        }
    }
    
    public function EditarModulos(){
        $ret = '';
        $form = new form01();
        $pastas = array();
        foreach($this->getLinguagens() as $index => $etiqueta){
            $pastas[$index] = $etiqueta;
        }
        $form->setPastas($pastas);
        $pastas = $this->getLinguagens(false);
        $sql = "select * from app001 where ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $param = array(
                'tipo' => 'T',
                'largura' => 3,
            );
            $dados = $this->getTodasAsTraducoesModulos();
            foreach ($rows as $row){
                foreach ($pastas as $lingua){
                    $campo = $row['nome'];
                    $param['campo'] = "formTraducao[$campo][$lingua]";
                    $param['pasta'] = $lingua;
                    $param['etiqueta'] = $row['etiqueta'];
                    $param['valor'] = isset($dados[$lingua][$campo]) ? $dados[$lingua][$campo] : '';
                    $form->addCampo($param);
                }
            }
            $form->setEnvio(getLink() . 'salvarModulos', 'formTraducao', 'formTraducao');
            $ret = $form . '';
            $param  = array(
                'titulo' => 'Traduzir App001',
                'conteudo' => $ret,
            );
            $ret = addCard($param);
        }
        return $ret;
    }
    
    private function getTodasAsTraducoesModulos(){
        $ret = array();
        $linguas = $this->getLinguagens(false);
        foreach ($linguas as $l){
            $ret[$l] = traducoes::getTodasAsTraducoesApp001($l);
        }
        return $ret;
    }
    
    public function salvarModulos(){
        $retornoForm = verificaParametro($_POST, 'formTraducao', array());
        if(count($retornoForm) > 0){
            foreach ($retornoForm as $modulo => $temp){
                foreach ($temp as $lingua => $texto){
                    if($this->verificarValoresModulos($modulo, $lingua) && !empty(trim($texto))){
                        $this->salvarEntradaModulos($modulo, $lingua, $texto);
                    }
                }
            }
        }
        return $this->index();
    }
    
    private function verificarValoresModulos($modulo, $lingua){
        $ret = false;
        if(in_array($lingua, $this->getLinguagens(false))){
            $sql = "select * from app001 where nome = '$modulo'";
            $rows = query($sql);
            $ret = (is_array($rows) && count($rows) > 0);
        }
        return $ret;
    }
    
    private function salvarEntradaModulos($modulo, $lingua, $texto){
        $codigo = traducoes::montarCodigoApp001($modulo);
        $texto_traduzido = traducoes::traduzir($codigo, $lingua);
        if($texto_traduzido === ''){
            traducoes::salvarTraducao($codigo, $lingua, $texto);
        }
        else{
            traducoes::atualizarTraducao($codigo, $lingua, $texto);
        }
    }
    
    public function EditarProgramas(){
        $ret = '';
        $form = new form01();
        $pastas = array();
        foreach($this->getLinguagens() as $index => $etiqueta){
            $pastas[$index] = $etiqueta;
        }
        $form->setPastas($pastas);
        $pastas = $this->getLinguagens(false);
        $sql = "select programa, etiqueta from app002 where ativo = 'S'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $param = array(
                'tipo' => 'T',
                'largura' => 3,
            );
            $dados = $this->getTodasAsTraducoesProgramas();
            foreach ($rows as $row){
                foreach ($pastas as $lingua){
                    $campo = $row['programa'];
                    $param['campo'] = "formTraducao[$campo][$lingua]";
                    $param['pasta'] = $lingua;
                    $param['etiqueta'] = $row['etiqueta'];
                    $param['valor'] = isset($dados[$lingua][$campo]) ? $dados[$lingua][$campo] : '';
                    $form->addCampo($param);
                }
            }
            $form->setEnvio(getLink() . 'salvarProgramas', 'formTraducao', 'formTraducao');
            $ret = $form . '';
            $param  = array(
                'titulo' => 'Traduzir App002',
                'conteudo' => $ret,
            );
            $ret = addCard($param);
        }
        return $ret;
    }
    
    private function getTodasAsTraducoesProgramas(){
        $ret = array();
        $linguas = $this->getLinguagens(false);
        foreach ($linguas as $l){
            $ret[$l] = traducoes::getTodasAsTraducoesApp002($l);
        }
        return $ret;
    }
    
    public function salvarProgramas(){
        $retornoForm = verificaParametro($_POST, 'formTraducao', array());
        if(count($retornoForm) > 0){
            foreach ($retornoForm as $programa => $temp){
                foreach ($temp as $lingua => $texto){
                    if($this->verificarValoresProgramas($programa, $lingua) && !empty(trim($texto))){
                        $this->salvarEntradaProgramas($programa, $lingua, $texto);
                    }
                }
            }
        }
        return $this->index();
    }
    
    private function verificarValoresProgramas($programa, $lingua){
        $ret = false;
        if(in_array($lingua, $this->getLinguagens(false))){
            $sql = "select * from app002 where programa = '$programa'";
            $rows = query($sql);
            $ret = (is_array($rows) && count($rows) > 0);
        }
        return $ret;
    }
    
    private function salvarEntradaProgramas($programa, $lingua, $texto){
        $codigo = traducoes::montarCodigoApp002($programa);
        $texto_traduzido = traducoes::traduzir($codigo, $lingua);
        if($texto_traduzido === ''){
            traducoes::salvarTraducao($codigo, $lingua, $texto);
        }
        else{
            traducoes::atualizarTraducao($codigo, $lingua, $texto);
        }
    }
}