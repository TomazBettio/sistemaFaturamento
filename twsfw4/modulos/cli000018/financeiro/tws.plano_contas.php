<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

class plano_contas{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'agrupa2'       => true,
        'contas'        => true,
        'novo'          => true,
        'atualizar'     => true,
        'salvar'        => true,
        'excluir'       => true,
        'editarConta'   => true,
        'salvarConta'   => true,
        'salvarNovoGrupo' => true,
    );
    
    private function jsConfirmaExclusao($titulo){
        addPortaljavaScript('function confirmaExclusao(link){');
        addPortaljavaScript('	if (confirm('.$titulo.')){');
        addPortaljavaScript('		setLocation(link);');
        addPortaljavaScript('	}');
        addPortaljavaScript('}');
    }
    
    public function index(){
        $ret = '';
        
        $tab = new tabela01(['titulo' => 'BHIO PLANO CONTAS - AGRUPADOR 1']);
        
        $param = [
            'campo'     => 'etiqueta',
            'etiqueta'  => 'Agrupador 1',
            'tipo'      => 'T',
            'width'     => '50',
            'posicao'   => 'E',
        ];
        $tab->addColuna($param);
        
        $param = [
            'texto' =>  'Editar',
            'link' 	=> getLink() . "atualizar&id=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'success',
            'pos'   => 'F',
        ];
        $tab->addAcao($param);
        
        $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir esse agrupamento?"');
        $param = [
            'texto' =>  'Excluir',
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&agrupa1={ID}')",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'danger',
            'pos'   => 'F',
        ];
        $tab->addAcao($param);
        
        $param = [
            'texto' =>  'Detalhes',
            'link' 	=> getLink() . 'agrupa2&id=',
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'primary',
            'pos'   => 'F',
        ];
        $tab->addAcao($param);
        
        $botao = [
            'cor' => 'success',
            'onclick' => "setLocation('".getlink()."novo')",
            'texto' => 'Novo Agrupador 1'
        ];
        $tab->addBotaoTitulo($botao);
        
        $dados = $this->getDadosAgrupador1();
        $tab->setDados($dados);
        
        $ret .= $tab;
        
        return $ret;
    }
    
    public function agrupa2(){
        $ret = '';
        
        $id_nivel1 = getParam($_GET, 'id', '');
        
        if(!empty($id_nivel1)){
            $dados = $this->getDadosAgrupa2($id_nivel1);
            $tab = new tabela01(['titulo' => "BHIO PLANO CONTAS - '" . $this->getEtiquetaById($id_nivel1) . "' - AGRUPADOR 2"]);
            
            $param = [
                'campo'     => 'etiqueta',
                'etiqueta'  => 'Agrupador 2',
                'tipo'      => 'T',
                'width'     => '50',
                'posicao'   => 'E',
            ];
            $tab->addColuna($param);
            
            //$agrupa1 = str_replace(' ', '@@', $agrupa1);
            $param = [
                'texto' =>  'Editar',
                'link' 	=> getLink() . "atualizar&id=",
                'coluna'=> 'id',
                'flag' 	=> '',
                'cor'   => 'success',
                'pos'   => 'F',
            ];
            $tab->addAcao($param);
            
            $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir esse agrupamento?"');
            $param = [
                'texto' =>  'Excluir',
                'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&agrupa1=$id_nivel1&agrupa2={ID}')",
                'coluna'=> 'id',
                'flag' 	=> '',
                'cor'   => 'danger',
                'pos'   => 'F',
            ];
            $tab->addAcao($param);
            
             $param = [
                'texto' =>  'Contas',
                'link' 	=> getLink() . "contas&agrupa1=$id_nivel1&agrupa2=",
                'coluna'=> 'id',
                'flag' 	=> '',
                'cor'   => 'primary',
                'pos'   => 'F',
            ];
            $tab->addAcao($param);
            
            $botao = [
                'id' => 'novo',
                'cor' => 'success',
                'onclick' => "setLocation('".getlink()."novo&g=2&agrupa1=$id_nivel1')",
                'texto' => 'Novo Agrupador'
            ];
            $tab->addBotaoTitulo($botao);
            
            $tab->setDados($dados);
            
            $tabela_agrupamento2 = $tab . '';
            
            $tab = new tabela01(['titulo' => "Contas sem Agrupamento 2"]);
            $tab->addColuna(['campo' => 'conta', 'etiqueta' => 'Conta']);
            $tab->addColuna(['campo' => 'rateio', 'etiqueta' => 'Rateio', 'tipo' => 'V']);
            
            $param = [
                'texto' =>  'Editar',
                'link' 	=> getLink() . "editarConta&agrupa1=$id_nivel1&conta=",
                'coluna'=> 'conta',
                'flag' 	=> '',
                'cor'   => 'success',
                'pos'   => 'F',
            ];
            $tab->addAcao($param);
            
            $param = [
                'texto' =>  'Excluir',
                'link' 	=> getLink() . "excluir&agrupa1=$id_nivel1&conta=",
                'coluna'=> 'conta',
                'flag' 	=> '',
                'cor'   => 'danger',
                'pos'   => 'F',
            ];
            $tab->addAcao($param);
            
            $botao = [
                'id' => 'novo',
                'cor' => 'success',
                'onclick' => "setLocation('".getlink()."editarConta&agrupa1=$id_nivel1')",
                'texto' => 'Nova conta sem agrupador 2'
            ];
            $tab->addBotaoTitulo($botao);
            
            $botao = [
                'id' => 'voltar',
                'cor' => 'danger',
                'onclick' => "setLocation('".getlink()."index')",
                'texto' => 'Voltar'
            ];
            $tab->addBotaoTitulo($botao);
            
            $dados = $this->getContasSemGrupo2($id_nivel1);
            $tab->setDados($dados);
            
            $tabela_contas_sem_agrupamento2 = $tab . '';
            
            $ret = addLinha(['tamanhos' => [6, 6], 'conteudos' => [$tabela_agrupamento2, $tabela_contas_sem_agrupamento2]]);
        }
        return $ret;
    }
    
    private function getContasSemGrupo2($id_agrupamento1){
        $ret = [];
        $sql = "select * from bs_plano_contas where AGRUPADOR1 = $id_agrupamento1 and AGRUPADOR2 is null";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = [
                    'conta' => $row['CONTAS'],
                    'rateio' => $row['RATEIO'],
                ];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    public function contas(){
        $ret = '';
        $agrupa1 = getParam($_GET, 'agrupa1', '');
        $agrupa2 = getParam($_GET, 'agrupa2', '');
        
        if(!empty($agrupa1) && !empty($agrupa2)){
            $tab = new tabela01(['titulo' => "BHIO PLANO CONTAS - " . $this->getEtiquetaById($agrupa1) . " - " . $this->getEtiquetaById($agrupa2) . " - CONTAS"]);
            
            $param = [
                'campo'     => 'conta',
                'etiqueta'  => 'Conta',
                'tipo'      => 'T',
                'width'     => '50',
                'posicao'   => 'E',
            ];
            $tab->addColuna($param);
            
            $param = [
                'campo'     => 'rateio',
                'etiqueta'  => 'Rateio',
                'tipo'      => 'V',
                'width'     => '50',
                'posicao'   => 'E',
            ];
            $tab->addColuna($param);
            
            $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir essa conta?"');
            
            $param = [
                'texto' =>  'Editar',
                'link' 	=> getLink() . "editarConta&agrupa1=$agrupa1&agrupa2=$agrupa2&conta=",
                'coluna'=> 'conta',
                'flag' 	=> '',
                'cor'   => 'success',
                'pos'   => 'F',
            ];
            $tab->addAcao($param);
            
            $param = [
                'texto' =>  'Excluir',
                'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&agrupa1=" . str_replace(' ', '@@', $agrupa1) . "&agrupa2=" . str_replace(' ', '@@', $agrupa2) . "&conta={ID}')",
                'coluna'=> 'conta',
                'flag' 	=> '',
                'cor'   => 'danger',
                'pos'   => 'F',
            ];
            $tab->addAcao($param);
            
            $botao = [
                'id' => 'voltar',
                'cor' => 'danger',
                'onclick' => "setLocation('".getlink()."agrupa2&id=$agrupa1')",
                'texto' => 'Voltar'
            ];
            $tab->addBotaoTitulo($botao);
            
            $botao = [
                'id' => 'novo',
                'cor' => 'success',
                'onclick' => "setLocation('".getlink()."editarConta&agrupa1=$agrupa1&agrupa2=$agrupa2')",
                'texto' => 'Acrescentar Conta'
            ];
            $tab->addBotaoTitulo($botao);
            
            $dados = $this->getDadosContas($agrupa1, $agrupa2);
            $tab->setDados($dados);
            
            $ret .= $tab;
        }
        
        return $ret;
    }
    
    public function editarConta(){
        $ret = '';
        $titulo = "Criar Conta Nova";
        
        $conta = $_GET['conta'] ?? '';
        $agrupa1 = $_GET['agrupa1'] ?? '';
        $agrupa2 = $_GET['agrupa2'] ?? '';
        
        $form = new form01();
        
        if(!empty($conta)){
            $titulo = 'Editar Conta ' . $conta;
            $form->addCampo([
                'campo'         => 'formConta[conta]',
                'etiqueta'      => 'Nº da conta',
                'tipo'          => 'N',
                'tamanho'       => '50',
                'readonly'      => true,
                'valor'         => $conta,
                'largura'       => 4,
                'obrigatorio'   => true
            ]);
            
            $form->addCampo([
                'campo'         => 'formConta[rateio]',
                'etiqueta'      => 'Rateio',
                'tipo'          => 'V',
                'tamanho'       => '50',
                'valor'         => $this->getRateio($agrupa1, $agrupa2, $conta),
                'largura'       => 4,
                'obrigatorio'   => true
            ]);
            
        }
        else{
            $form->addCampo([
                'campo'         => 'formConta[conta]',
                'etiqueta'      => 'Nº da conta',
                'tipo'          => 'N',
                'tamanho'       => '50',
                'valor'         => '',
                'largura'       => 4,
                'obrigatorio'   => true
            ]);
            
            $form->addCampo([
                'campo'         => 'formConta[rateio]',
                'etiqueta'      => 'Rateio',
                'tipo'          => 'V',
                'tamanho'       => '50',
                'valor'         => 0,
                'largura'       => 4,
                'obrigatorio'   => true
            ]);
        }
        
        $url_salvar = getLink() . 'salvarConta';
        if(!empty($agrupa1)){
            $url_salvar .= "&agrupa1=$agrupa1";
        }
        if(!empty($agrupa2)){
            $url_salvar .= "&agrupa2=$agrupa2";
        }
        if(!empty($conta)){
            $url_salvar .= "&conta=$conta";
        }
        
        $url_cancelar = getLink();
        if(!empty($agrupa2)){
            $url_cancelar .= "contas&agrupa1=$agrupa1&agrupa2=$agrupa2";
        }
        else{
            $url_cancelar .= "agrupa2&id=$agrupa1";
        }
        
        $form->setBotaoCancela($url_cancelar);
        $form->setEnvio($url_salvar, 'formConta', 'formConta');
        
        $ret .= $form;
        
        $ret = addCard(['titulo' => $titulo, 'conteudo' => $ret]);
        return $ret;
    }
    
    private function getRateio($agrupa1, $agrupa2, $conta){
        $ret = 0;
        if(!empty($conta)){
            $sql = "select RATEIO from bs_plano_contas where ";
            $where = ["CONTAS = '$conta'"];
            if(!empty($agrupa1)){
                $where[] = "AGRUPADOR1 = $agrupa1";
            }
            if(!empty($agrupa2)){
                $where[] = "AGRUPADOR2 = $agrupa2";
            }
            $sql .= implode(' and ', $where);
            $rows = query($sql);
            if(is_array($rows) && count($rows) > 0){
                $ret = $rows[0]['RATEIO'];
            }
        }
        return $ret;
    }
    
    public function salvarConta(){
        $conta = $_GET['conta'] ?? '';
        
        $agrupa1 = $_GET['agrupa1'] ?? '';
        if(!empty($agrupa1)){
            $agrupa1 = "'$agrupa1'";
        }
        else{
            $agrupa1 = 'null';
        }
        
        $agrupa2 = $_GET['agrupa2'] ?? '';
        if(!empty($agrupa2)){
            $agrupa2 = "'$agrupa2'";
        }
        else{
            $agrupa2 = 'null';
        }
        
        $rateio = $_POST['formConta']['rateio'] ?? '0';
        $rateio = str_replace(['.', ','], ['', '.'], $rateio);
        if(!empty($conta)){
            $sql = "update bs_plano_contas set RATEIO = $rateio where AGRUPADOR1 = $agrupa1 and CONTAS = '$conta'";
            if($agrupa2 == 'null'){
                $sql .= ' and AGRUPADOR2 is null';
            }
            else{
                $sql .= " and AGRUPADOR2 = $agrupa2";
            }
            query($sql);
        }
        else{
            //recuperar cont
            $conta = $_POST['formConta']['conta'] ?? '';
            if(!empty($conta)){
                $sql = "insert into bs_plano_contas values ($agrupa1, $agrupa2, '$conta', $rateio)";
                query($sql);
            }
        }
        $agrupa1 = $_GET['agrupa1'] ?? '';
        $agrupa2 = $_GET['agrupa2'] ?? '';
        $url = getLink();
        if(!empty($agrupa2)){
            $url .= "contas&agrupa1=$agrupa1&agrupa2=$agrupa2";
        }
        else{
            $url .= "agrupa2&id=$agrupa1";
        }
        redireciona($url);
    }
    
    public function novo(){
        $ret = '';
        $agrupa1 = $_GET['agrupa1'] ?? '';
        $form = new form01();
        $param = [
            'id'            => '',
            'campo'         => 'formGrupo[etiqueta]',
            'etiqueta'      => 'Nome do Agrupador',
            'tipo'          => 'T',
            'tamanho'       => '50',
            'linhas'        => '',
            'valor'         => '',
            'pasta'         => 0,
            'lista'         => '',
            'validacao'     => '',
            'largura'       => 4,
            'obrigatorio'   => true
        ];
        $form->addCampo($param);
        
        $link = getLink() . 'salvarNovoGrupo';
        if(!empty($agrupa1)){
            $link .= "&agrupa1=$agrupa1";
        }
        
        $form->setEnvio($link, 'formGrupo','formGrupo' );
        
        $ret = addCard(['conteudo'=>$form, 'titulo'=>'Novo Agrupador']);
        
        return $ret;
    }
    
    public function salvarNovoGrupo(){
        $agrupa1 = $_GET['agrupa1'] ?? '';
        $etiqueta = $_POST['formGrupo']['etiqueta'];
        if(!empty($agrupa1)){
            //novo grupo2
            $sql = "insert into bs_agrupadores_contas values (null, '$etiqueta', 2)";
            $id_novo = query($sql);
            $sql = "insert into bs_agrupadores_relacao values (null, $agrupa1, $id_novo)";
            query($sql);
        }
        else{
            $sql = "insert into bs_agrupadores_contas values (null, '$etiqueta', 1)";
            query($sql);
        }
        
        $url = getLink();
        if(!empty($agrupa1)){
            $url .= "agrupa2&id=$agrupa1";
        }
        else{
            $url .= "index";
        }
        redireciona($url);
    }
    
    public function atualizar(){
        $ret = '';
        
        $form = new form01();

        $id = getParam($_GET, 'id');
        //Atualizar do agrupamento 1
        $param = [
            'id'            => '',
            'campo'         => 'formUpdate[etiqueta]',
            'etiqueta'      => 'Nome do Agrupador',
            'tipo'          => 'T',
            'tamanho'       => '50',
            'linhas'        => '',
            'valor'         => $this->getEtiquetaById($id),
            'largura'       => 4,
            'obrigatorio'   => true
        ];
        $form->addCampo($param);
        
        $form->setEnvio(getLink()."salvar&id=$id", 'formUpdate','formUpdate' );
        if($this->getCampoById($id, 'nivel') == 2){
            $url = getLink() . 'agrupa2&id=' . $this->getRelacaoPai($id);
            $form->setBotaoCancela($url);
        }
        
        $ret .= addCard(['titulo' => 'Atualização Nome Agrupador', 'conteudo' => $form]);
        
        return $ret;
    }
    
    private function getRelacaoPai($id_filho){
        $ret = '';
        $sql = "select agrupador1 from bs_agrupadores_relacao where agrupador2 = $id_filho";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0]['agrupador1'];
        }
        return $ret;
    }
    
    private function getEtiquetaById($id){
        return $this->getCampoById($id, 'etiqueta');
    }
    
    private function getCampoById($id, $campo){
        $ret = '';
        $sql = "select $campo from bs_agrupadores_contas where id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $ret = $rows[0][$campo];
        }
        return $ret;
    }
    
    public function salvar(){
        //var_dump($_POST); die();
        $id = $_GET['id'];
        $etiqueta = $_POST['formUpdate']['etiqueta'];
        $sql = "update bs_agrupadores_contas set etiqueta = '$etiqueta' where id = $id";
        query($sql);
        if($this->getCampoById($id, 'nivel') == 2){
            $url = getLink() . 'agrupa2&id=' . $this->getRelacaoPai($id);
        }
        else{
            $url = getLink() . 'index';
        }
        redireciona($url);
        
        $agrupa1 = getParam($_GET, 'agrupa1');
        if(isset($_POST['formUpdate']))
        {
            $novo_nome = $_POST['formUpdate']['nome'];
            //Atualização de nome
            if (isset($_GET['agrupa2']))
            {
                //Atualização de nome para agrupa2
                $agrupa2 = getParam($_GET, 'agrupa2');
                $agrupa1 = str_replace('@@', ' ', $agrupa1);
                $sql = "UPDATE bs_plano_contas SET AGRUPADOR2 = '$novo_nome'
                     WHERE AGRUPADOR1 = '$agrupa1' AND AGRUPADOR2 = '$agrupa2'";
                query($sql);
                redireciona(getLink()."agrupa2&agrupa1=$agrupa1");
            } else {
                //Atualização de nome para agrupa1
                $sql = "UPDATE bs_plano_contas SET AGRUPADOR1 = '$novo_nome' 
                    WHERE AGRUPADOR1 = '$agrupa1'";
                query($sql);
                redireciona(getLink().'index');
            }
            //query($sql);
        } else if(isset($_POST['formGrupo']))
        {
            //Novo nome
            $agrupa1 = str_replace('@@', ' ', $agrupa1);
            $novo_grupo = $_POST['formGrupo']['nome'];
            $sql = "INSERT INTO $this->tabela (AGRUPADOR1, AGRUPADOR2) 
            VALUES ('$agrupa1','$novo_grupo')";
            query($sql);
            $agrupa1 = str_replace(' ', '@@', $agrupa1);
            $agrupa2 = str_replace(' ', '@@', $novo_grupo);
            redireciona(getLink()."novo&g=3&agrupa1=$agrupa1&agrupa2=$agrupa2");
            
        } else if(isset($_POST['formContas']))
        {
            //Novas contas para aqueles agrupa1 e agrupa2
            $agrupa1 = str_replace('@@', ' ' , $agrupa1);
            $agrupa2 = getParam($_GET, 'agrupa2');
            $agrupa2 = str_replace('@@', ' ', $agrupa2);
            
            $contas = getParam($_POST, 'formContas');
            $de = $contas['conta_de'];
            
            if(!empty($contas['conta_ate']))
            {
                $ate = $contas['conta_ate'];
                for ($i=$de; $i<=$ate; $i++)
                {
                    $sql = "INSERT INTO bs_plano_contas (AGRUPADOR1, AGRUPADOR2, CONTAS, RATEIO) 
            VALUES ('$agrupa1','$agrupa2','$i', NULL)";
                    query($sql);
                }
            } else {
                $sql = "INSERT INTO bs_plano_contas (AGRUPADOR1, AGRUPADOR2, CONTAS, RATEIO)
            VALUES ('$agrupa1','$agrupa2','$de', NULL)";
                query($sql);
            }
           // echo $sql;die();
            redireciona(getLink() . "contas&agrupa1=" . str_replace(' ' , '@@', $agrupa1) . "&agrupa2=$agrupa2");
        } else {
            redireciona(getLink().'index');
        }
    }
    
    public function excluir(){
        $sql = 'DELETE FROM bs_plano_contas WHERE ';
        $where = array();
        
        $agrupa1 = $_GET['agrupa1'] ?? '';
        $agrupa2 = $_GET['agrupa2'] ?? '';
        $conta= $_GET['conta'] ?? '';
        
        if(!empty($conta)){
            $where[] = "CONTAS = '$conta'";
        } 
        if(!empty($agrupa2)){
            $where[] = "AGRUPADOR2 = $agrupa2";
        }
        if(!empty($agrupa1)){
            $where[] = "AGRUPADOR1 = $agrupa1";
        }
        
        if(count($where) > 0){
            $sql .= implode(' and ', $where);
            log::gravaLog('contas_exclusao', 'o usuário ' . getUsuario() . ' rodou a seguinte query: ' . $sql);
            query($sql);
        }
        
        if(!empty($agrupa1) && !empty($agrupa2) && empty($conta)){
            //é para apagar o agrupador de nível 2
            $sql = "delete from bs_agrupadores_contas where id = $agrupa2 and nivel = 2";
            query($sql);
            $sql = "delete from bs_agrupadores_relacao where agrupador2 = $agrupa2";
            query($sql);
        }
        if(!empty($agrupa1) && empty($agrupa2) && empty($conta)){
            //é para apagar o agrupador de nível 1
            $sql = "delete from bs_agrupadores_contas where id = $agrupa1 and nivel = 1";
            query($sql);
            $sql = "delete from bs_agrupadores_contas where nivel = 2 and id in (select agrupador2 from bs_agrupadores_relacao where agrupador1 = $agrupa1)";
            query($sql);
            $sql = "delete from bs_agrupadores_relacao where agrupador1 = $agrupa2";
            query($sql);
        }
        
        $url = getLink();
        if(!empty($agrupa1)){
            if(!empty($agrupa2) && !empty($conta)){
                $url .= "contas&agrupa1=$agrupa1&agrupa2=$agrupa2";
            }
            elseif(!empty($agrupa2) && empty($conta)){
                $url .= "agrupa2&id=$agrupa1";
            }
            elseif(empty($agrupa2) && !empty($conta)){
                $url .= "agrupa2&id=$agrupa1";
            }
            else{
                $url .= 'index';
            }
        }
        else{
            $url .= 'index';
        }
        redireciona($url);
    }
    
    private function formNovoGrupo2($agrupa1){
        $ret = '';
        
        $form = new form01();
        $param = [
            'id'            => '',
            'campo'         => 'formGrupo[nome]',
            'etiqueta'      => 'Nome do Agrupador',
            'tipo'          => 'T',
            'tamanho'       => '50',
            'linhas'        => '',
            'valor'         => '',
            'pasta'         => 0,
            'lista'         => '',
            'validacao'     => '',
            'largura'       => 4,
            'obrigatorio'   => true
        ];
        $form->addCampo($param);
        
        $form->setEnvio(getLink()."salvar&agrupa1=$agrupa1", 'formGrupo','formGrupo' );
        
        $ret = addCard(['conteudo'=>$form, 'titulo'=>'Novo Agrupador']);
        
        return $ret;
    }
    
    private function formContas($agrupa1, $agrupa2){
        $ret = '';
        $form = new form01();
        $param = [
            'id'            => '',
            'campo'         => 'formContas[conta_de]',
            'etiqueta'      => 'De',
            'tipo'          => 'T',
            'tamanho'       => '50',
            'linhas'        => '',
            'valor'         => '',
            'pasta'         => 0,
            'lista'         => '',
            'validacao'     => '',
            'largura'       => 4,
            'obrigatorio'   => true
        ];
        $form->addCampo($param);
        $param = [
            'id'            => '',
            'campo'         => 'formContas[conta_ate]',
            'etiqueta'      => 'Até',
            'tipo'          => 'T',
            'tamanho'       => '50',
            'linhas'        => '',
            'valor'         => '',
            'pasta'         => 0,
            'lista'         => '',
            'validacao'     => '',
            'largura'       => 4,
            'obrigatorio'   => false
        ];
        $form->addCampo($param);
        
        $form->setEnvio(getLink()."salvar&agrupa1=$agrupa1&agrupa2=$agrupa2", 'formContas','formContas' );
        
        $ret .= addCard(['titulo' => 'Contas do Agrupamento: ' . str_replace('@@',' ',$agrupa2), 'conteudo'=>$form]);
        return $ret;
    }
    
    private function getDadosAgrupador1(){
        $ret = [];
        $sql = "select * from bs_agrupadores_contas where nivel = 1";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['etiqueta', 'id', 'nivel'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getDadosAgrupa2($agrupa1){
        $ret = [];
        $sql = "SELECT * FROM bs_agrupadores_contas WHERE nivel = 2 and id in (select agrupador2 from bs_agrupadores_relacao where agrupador1 = $agrupa1)";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            $campos = ['etiqueta', 'id', 'nivel'];
            foreach ($rows as $row){
                $temp = [];
                foreach ($campos as $c){
                    $temp[$c] = $row[$c];
                }
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getDadosContas($agrupa1, $agrupa2){
        $ret = [];
        $sql = "SELECT CONTAS, RATEIO FROM bs_plano_contas WHERE AGRUPADOR1 = $agrupa1 and AGRUPADOR2 = $agrupa2";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp = [
                    'conta' => $row['CONTAS'],
                    'rateio' => $row['RATEIO'],
                ];
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
}