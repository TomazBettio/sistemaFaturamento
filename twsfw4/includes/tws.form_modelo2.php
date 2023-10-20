<?php
use PhpParser\Node\Expr\BinaryOp\Identical;

/*
 * Data Criação: 05/07/2023
 * Autor: Alex Cesar
 *
 * Descricao: Gera formulários no estilo OS (form + tabela)
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class form_modelo2{
    
    //Tabela do formulário
    public $_itens;
    
    //url do envio do formulário
    private $_envio;
    
    //tabela de input
    private $_rat;
    
    //Form cabeçalho
    public $_cab;
    
    //Dados da tabela
    private $_dadosItens = [];
    
    //Campos da tabela
    private $_colunas = [];
    
    //Nomes dos campos ÚNICOS AO MODAL
    private $_nomeCamposModal = [];
    
    //Campos do form do modal
    private $_camposModal = [];
    
    //Campos tipo data para tratamento
    private $_colData = [];
    
    //Nome do formulário
    private $_nomeForm;
    
    //Título do card
    private $_titulo = '';
    
    //id da tabela
    private $_idTab = '';
    
    //Opção de cadastro 0-Linha 1-Modal
    private $_opCadastro;
    
    //URL do botão de cancelar
    private $_urlCancela = '';
    
    //Campos do form para verificar obrigatórios
    private $_campos;
    
    //Indica se os botões "excluir" e "copiar" aparecem na tabela
    private $_apenasExibicao;
    
    //Indica a url do botão de exclusão, se a tabela não é apenas exibição
    private $_btExcluir='';
    
    
    /**
     * Form_modelo2 : Formulário + Tabela com possibilidade de acréscimo de dados
     * @param array $param Parâmetros para a criação do formulário
     *                     form : array com dados do formulário (igual form01)
     *                     opcao : 0 para cadastro na 1a linha (default), 1 para modal
     *                     apenasExibicao : true se a tabela for apenas de visualização
     *                     urlExcluir : url do botão de exclusão
     *                     nome : nome do form de envio
     *                     titulo : título do programa no card
     */
    public function __construct($param = [])
    {
        $tab = [];
        $tab['paginacao'] = false;
        $tab['scroll'] 	  = false;
        $tab['scrollX']   = false;
        $tab['scrollY']   = false;
        $tab['ordenacao'] = false;
        $tab['filtro']	  = false;
        $tab['info']	  = false;
        $tab['width']	  = '100%';
        $tab['id']		  = 'tabelaItens';
        $this->_itens = new tabela01($tab);
        
        $this->_opCadastro = isset($param['opcao']) ? $param['opcao'] : 0;
        
        if($this->_opCadastro == 1){
            $this->_rat = new form01();
        } else {
            $tab['id']		  = $param['nome'].'tabela';
            $this->_rat = new tabela01($tab);
        }
        
        $this->_idTab =  $param['nome'].'tabela';
        
        if(!isset($param['form'])){
            $param['form'] = [];
        }
        $this->_cab = new form01($param['form']);
        
        $this->_nomeForm = $param['nome'];
        $this->_titulo = isset($param['titulo']) ? $param['titulo'] : '';
        $this->_urlCancela  = verificaParametro($param, 'cancelar',  getLink().'index');
        $this->_campos = [];
        $this->_apenasExibicao = isset($param['apenasExibicao']) ? $param['apenasExibicao'] : false;
        
        if(!$this->_apenasExibicao){
            $this->_btExcluir = $param['urlExcluir'];
        }
    }
    
    //----------PRINT-----------
    public function __toString()
    {
        $this->formModelo2JS();
        
        //form cabeçalho
        $ret = '';
        $ret .= $this->_cab . '';
        
        //incluir linha da tabela
        if(!empty($this->_dadosItens)){
            $numItem = count($this->_dadosItens);
        }
        //tabela rateio
        if($this->_opCadastro == 1)
        {
            $param = [];
            $param['texto'] = 'Incluir';
            $param['onclick'] = "$('#myModal').modal();";
            $param['id'] = 'myInputOpenModal';
            $param['cor'] = 'success';
            $ret .= formbase01::formBotao($param);
        } else {
            $this->setDados();
            $ret .= $this->_rat . '';
        }
        
        
        //tabela itens
        $param = ['campo' => 'btExcluir'      , 'etiqueta' => ''       , 'tipo' => 'B', 'width' =>  160, 'posicao' => 'E'];
        $this->_itens->addColuna($param);
        $ret .= $this->_itens . '';
        
        $param = array(
            'acao' => $this->_envio ,
            'id'   => $this->_nomeForm,
            'nome' => $this->_nomeForm,
            'onsubmit' => 'verificaObrigatorios',
            
            'IDform'   => $this->_nomeForm,
            'URLcancelar' => $this->_urlCancela,
            'sendFooter' => true,
        );
        $ret = formbase01::form($param, $ret);
        
        if($this->_opCadastro == 1)
        {
            //modal de cadastro
            $ret .= $this->addHtmlModal();
        }
        
        $this->geraScriptValidacao();
        if($this->_opCadastro == 1) {
            $this->geraScriptValidacaoModal();
        } else {
            $this->geraScriptValidacaoTabela();
        }
        
        
        return addCard(['titulo' => $this->_titulo, 'conteudo' => $ret]);
    }
    
    //----------MODAL----------
    /**
     * addCampoModal: Inclui um campo (que NÃO está na tabela) no formulário do Modal
     *
     * @param array() $param Parametros para criação do campo:
     * 							tipo ('T' Caracter, 'N' Numero (inteiro), 'V' Valor (2 casas decimais), 'V4' Valor (4 casas decimais)
     * 							id
     * 							campo (nome do [CAMPO] do envio)
     * 							etiqueta (label)
     * 							tamanho
     * 							linhas (textbox)
     * 							valor
     * 							lista -> array(array(valor1, etiqueta1),array(valor2,etiqueta2)....)
     * 							validacao
     * 							obrigatorio
     * 							opcao -> String "valor=etiqueta;valor2=etiqueta2;...."
     * 							help
     * @return	void
     */
    public function addCampoModal($param)
    {
        if($this->_opCadastro == 1){
            $this->_nomeCamposModal[] = $param['campo'];
            $temp = $param;
            $temp['campo'] = 'inputVal['.$temp['campo'].']';
            $this->_rat->addCampo($temp);
            $this->_camposModal[] = $temp;
        }
    }
    
    //corpo
    private function addHtmlModal(){
        $conteudo = '';
        $conteudo = $this->formModal();
        $ret = '
            <div class="modal fade" id="myModal" data-backdrop="static">
                <div class="modal-dialog modal-xl" id="divTamanho">
                    <div class="modal-content">
                        
                        <!-- Cabeçalho do modal -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="titulo-modal">Incluir</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                                
                        <!-- Corpo do modal -->
                        <div class="modal-body" id="corpo-modal">
                            <p>'.$conteudo.'</p>
                        </div>
                                                               
                    </div>
                </div>
            </div>
            ';
        return $ret;
    }
    
    //conteúdo
    private function formModal()
    {
        $ret = '';
        
        $ret .= $this->_rat;
        
        $param = [];
        $param['texto'] = 'Incluir';
        $param['onclick'] = "incluiRat(0);";
        $param['id'] = 'myInputIncluiModal';
        $param['cor'] = 'success';
        
        $ret .= formbase01::formBotao($param);
        
        $param = [];
        $param['texto'] = 'Limpar';
        $param['onclick'] = "limpaCampos();";
        $param['id'] = 'myInputLimpaModal';
        $param['cor'] = 'default';
        $ret .= '                '. formbase01::formBotao($param);
        
        return $ret;
    }
    
    private function geraScriptValidacaoModal(){
        $ret = "
            function verificaObrigatoriosModal()
            {
                	msg = '';";
        foreach ($this->_camposModal as $c)
        {
            if($c['obrigatorio'] == true)
            {
                $id = $c['campo'];
                $id = str_replace("[","",$id);
                $id = str_replace("]","",$id);
                if(isset($c['select']) && $c['select'] === true){
                    $ret .= "
                    conteudo = $('#$id option:selected').val();
                    ";
                }else{
                    $ret .= "
                    conteudo = $('#$id').val();
                    ";
                }
                $ret .= "
                    if(conteudo.trim() == '' || conteudo == 'undefined') {
                        msg += 'O campo ".$c['etiqueta']." deve ser preenchido\\n'
                    }";
            }
        }
        $ret .= "
                    if(msg == '') {
                        return true;
                	} else {
                        alert(msg);
                		return false;
                	}
            }";
        addPortaljavaScript($ret);
    }
    
    //-----------FORM--------------
    /**
     * addCampoForm: Inclui um campo no formulário cabeçalho
     *
     * @param array() $param Parametros para criação do campo:
     * 							tipo ('T' Caracter, 'N' Numero (inteiro), 'V' Valor (2 casas decimais), 'V4' Valor (4 casas decimais)
     * 							id
     * 							campo (nome do [CAMPO] do envio)
     * 							etiqueta (label)
     * 							tamanho
     * 							linhas (textbox)
     * 							valor
     * 							lista -> array(array(valor1, etiqueta1),array(valor2,etiqueta2)....)
     * 							validacao
     * 							obrigatorio
     * 							opcao -> String "valor=etiqueta;valor2=etiqueta2;...."
     * 							help
     * @return	void
     */
    public function addCampoForm($param){
        $param['campo'] = $this->_nomeForm . "[{$param['campo']}]";
        $this->_cab->addCampo($param);
        $this->_campos[] = $param;
    }
    
    public function addCampoHidden($nome,$valor){
        $campo = $this->_nomeForm . "[$nome]";
        $this->_cab->addHidden($campo, $valor);
    }
    
    /**
     * setEnvio: Seta o envio do formulário
     * @param string $url URL para envio do formulário
     */
    public function setEnvio($url){
       $this->_envio = $url;
    }
    
    public function setBotaoCancela($url = ''){
        $url = empty(trim($url)) ? getLink().'index' : $url;
        $this->_URLcancelar = $url;
    }
    
    private function geraScriptValidacao(){
        $ret = "
            function verificaObrigatorios()
            { 
                	msg = '';";
        foreach ($this->_campos as $c)
        {
            if($c['obrigatorio'] == true)
            {
                $id = $c['campo'];
                $id = str_replace("[","",$id);
                $id = str_replace("]","",$id);
                if(isset($c['select']) && $c['select'] === true){
                    $ret .= "	
                    conteudo = $('#$id option:selected').val();
                    ";
                }else{
                    $ret .= "	
                    conteudo = $('#$id').val();
                    ";
                }
                $ret .= "
                    if(conteudo.trim() == '' || conteudo == 'undefined') {
                        msg += 'O campo ".$c['etiqueta']." deve ser preenchido\\n'
                    }";
            }
        }
        $ret .= "
                    if(msg == '') {
                        return true;
                	} else {
                        alert(msg);
                		return false;            
                	}
            }";
        addPortaljavaScript($ret);
    }
    
    //-----------TABELA-------------
    
    /**
     * addColunaTab: Inclui uma coluna na tabela
     * @param array $param Parâmetros para a coluna (idem a tabela01):
     * 							campo (nome do campo)
     * 							etiqueta (label)
     *     						tipo ('T' Caracter, 'N' Numero (inteiro), 'V' Valor (2 casas decimais), 'V4' Valor (4 casas decimais)
     * 							width (tamanho da coluna)
     * 							posicao ('E' esquerda, 'D' direita, 'C' centralizado)
     * 							class
     */
    public function addColunaTab($param){
        if($this->_opCadastro == 1){
            $temp = $param;
            $temp['campo'] = 'inputVal['.$temp['campo'].']';
            $temp['id'] = '';
            $temp['tamanho'] = '7';
            $temp['obrigatorio'] = true;
            $this->_rat->addCampo($temp);
        } else {
            $this->_rat->addColuna($param);
        }
        $this->_colunas[] = $param;
        if($param['tipo']=='D'){
            $this->_colData[] = $param['campo'];
            $param['tipo']='T';
        } 
        $this->_itens->addColuna($param);
    }
    
    /**
     * setDadosTab: Constrói as linhas na tabela
     * @param array $dados Dados da linha da tabela
     */
    public function setDadosTab($dados){
        if(!empty($this->_colData)){
            $temp = [];
            foreach($dados as $dado){
                foreach($this->_colData as $colunaData){
                    $dado[$colunaData] = datas::dataS2D($dado[$colunaData].'');
                }
                if(!$this->_apenasExibicao){
                    $dado['btExcluir'] = "<button type='button' class='btn btn-xs btn-warning'  onclick='copiarRat(this);'>Copiar</button>          <button type='button' class='btn btn-xs btn-danger'  onclick=\"op2('$this->_btExcluir');excluirRat(this);\">Excluir</button>";
                }
                $temp[] = $dado;
            }
            $dados = $temp;
        }
        $this->_dadosItens = $dados;
        $this->_itens->setDados($dados);
    }
    
    /**
     * addAcaoTab: Adiciona uma ação à tabela
     * @param array $param parâmetros da ação
     */
    public function addAcaoTab($param){
        $this->_itens->addAcao($param);
    }
    
    private function geraScriptValidacaoTabela()
    {
        
        
        $string = "
            function verificaObrigatoriosLinha()
            {
                msg = '';
";
        
        foreach($this->_colunas as $col)
        {
            $id = $col['campo'];
            $id = str_replace("[","",$id);
            $id = str_replace("]","",$id);
            $string .= "
                    conteudo = $('#inputVal$id').val();
                    if(conteudo.trim() == '' || conteudo == 'undefined') {
                            msg += 'O campo ".$col['etiqueta']." deve ser preenchido\\n'
                    }
                    ";
        }
        
        $string .= "
                    if(msg == '') {
                        return true;
                	} else {
                        alert(msg);
                		return false;
                	}
            }";
        
        addPortaljavaScript($string);
    }
    
    //Constrói os dados da tabela como campos editáveis
    private function setDados()
    {
        //número da próxima entrada da tabela - inicia zero
        $num = 0;
        $dados = [];
        $temp = [];
        
        $this->_rat->addColuna(['campo' => 'btIncluir'      , 'etiqueta' => ''       , 'tipo' => 'B', 'width' =>  160, 'posicao' => 'E']);
        
        foreach($this->_colunas as $col){
            switch ($col['tipo']){
                case 'D':
                    $tipo = 'date';
                    $temp[$col['campo']] = "<input type='$tipo' name='inputVal[tabela][{$col['campo']}]' value='' style='width:100%;text-align: right;' id='inputVal{$col['campo']}' class='form-control  form-control-sm' required=''      >";
                    break;
                case 'N':
                    $tipo = 'number';
                    $temp[$col['campo']] = "<input type='$tipo' name='inputVal[tabela][{$col['campo']}]' value='' style='width:100%;text-align: right;' id='inputVal{$col['campo']}' class='form-control  form-control-sm' required=''      >";
                    break;
                case 'A':
                    $options = '';
                    foreach($col['lista'] as $lis){
                        $options .= "<option value='{$lis[0]}' selected=''>{$lis[1]}</option>";
                    }
                    $temp[$col['campo']] = "<select id='inputVal{$col['campo']}' name='inputVal[tabela][{$col['campo']}]' value='' style='width:100%;text-align: right;' id='inputVal{$col['campo']}' class='form-control  form-control-sm' required=''>$options</select>";
                    break;
                default:
                    $tipo = 'text';
                    $temp[$col['campo']] = "<input type='$tipo' name='inputVal[tabela][{$col['campo']}]' value='' style='width:100%;text-align: right;' id='inputVal{$col['campo']}' class='form-control  form-control-sm' required=''      >";
                    break;
            }
        }
        
         $param = [];
         $param['texto'] = 'Incluir';
         $param['onclick'] = "incluiRat($num);";
         $param['id'] = 'myInputInclui';
         $param['cor'] = 'success';
         $temp['btIncluir']= formbase01::formBotao($param);
         
         $param = [];
         $param['texto'] = 'Limpar';
         $param['onclick'] = "limpaCampos();";
         $param['id'] = 'myInputLimpa';
         $param['cor'] = 'default';
         $temp['btIncluir'].= '                '. formbase01::formBotao($param);
         
        $dados[] = $temp;
        
        $this->_rat->setDados($dados);
    }
    
    //--------JAVASCRIPT--------
    public function formModelo2JS()
    {
        $rat_add = "";
        $rat_Texto = "";
        $limpa_texto = "";
        $elementos = "";
        
        $i = count($this->_colunas);
        
        foreach($this->_nomeCamposModal as $hidden)
        {
            //incluirRat
            $rat_Texto .= "
                    valCampo = document.getElementById('inputVal$hidden').value;
                    hidden += \"<input type='hidden' name='$this->_nomeForm[tabela][$hidden][]' value='\"+valCampo+\"' id='\"+valor+\"tabelacampo$hidden'>\";
                " ;
            //LimpaCampos
            $limpa_texto .= "
                    var element$hidden = document.getElementById('inputVal$hidden');
                    element$hidden.value = '';
                ";
        }
        
        foreach($this->_colunas as $col)
        {
            $tipo = $col['tipo'];
            $i--;
            
            //IncluirRat
            $rat_Texto .= "
                    valCampo = document.getElementById('inputVal" . $col['campo'] . "').value;
                  //  alert(valCampo);
                ";
            if($tipo == 'D' && $this->_opCadastro != 1)
            {
                    $rat_Texto .= "
                    valCampo = valCampo.replace('-','');
                    valCampo = valCampo.replace('-','');
                    ano = valCampo.slice(0,4);
                    mes = String(valCampo.slice(4,6));
                    dia = String(valCampo.slice(6,8));
                    valCampo = dia + '/' + mes + '/' + ano;
                ";
            }
            
            $rat_Texto .= "
                    var " . $col['campo'] . " = valCampo+\"<input type='hidden' name='$this->_nomeForm[tabela][" . $col['campo'] . "][]' value='\"+valCampo+\"' id='\"+valor+\"tabelacampo" . $col['campo'] . "'>\";
                " ;
            
            if($i==0){
                $rat_add .= $col['campo'];
            } else {
                $rat_add .= $col['campo'] . ",";
            }
            
            //LimpaCampos
            $limpa_texto .= "
                    var element" . $col['campo'] . " = document.getElementById('inputVal" . $col['campo'] . "');  
                    element" . $col['campo'] . ".value = '';
                ";
            
            //CopiarRat
            $elementos .=  "
                    var element" . $col['campo'] . " = document.getElementById('inputVal" . $col['campo'] . "');
                    valor = rowData[" . (count($this->_colunas) - $i - 1) . "];
                    if(valor.indexOf('<') != -1) {
                        valor = valor.slice(0,valor.indexOf('<'));
                    }";
            if($tipo == 'D' && $this->_opCadastro != 1)
            {
                $elementos .= "
                    //alert(valor);
                    valor = valor.replace('/','');
                    valor = valor.replace('/','');
                    ano = valor.slice(4,8);
                    mes = String(valor.slice(2,4));
                    dia = String(valor.slice(0,2));
                    valor = ano + '-' + mes + '-' + dia;
                    ";
            }
            $elementos .= "
                        element" . $col['campo'] . ".value = valor;
                ";
        }
        
        
        
        $ret = "
                function excluirRat(e)
                {
    				var table = $('#tabelaItens').DataTable();
    				table.row( $(e).parents('tr') ).remove().draw();
                }
                function limpaCampos()
                {
                    $limpa_texto      
                }
                function copiarRat(e)
                {
                    var table = $('#tabelaItens').DataTable();
    				var rowData = table.row( $(e).parents('tr') ).data();
                    var valor;
                    //alert(Object.getOwnPropertyNames(rowData));
                    //alert(rowData);
                    $elementos";
        if($this->_opCadastro == 1){
        $ret .=   "return $('#myModal').modal();
                ";
        }
        $ret .= "
                }
                function incluiRat(valor)
                {";
       if($this->_opCadastro == 1) {
            $ret .= "if(verificaObrigatoriosModal())" ;
        } else {
           $ret .= "if(verificaObrigatoriosLinha())" ;
            // $ret .= "if(true)" ;
        }
        $ret .= " {
        				var table = $('#tabelaItens').DataTable();
                        var hidden = '';
                        var valCampo;
                        var btExcluir = \"<button type='button' class='btn btn-xs btn-warning'  onclick='copiarRat(this);'>Copiar</button>          <button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";
                        $rat_Texto
                        $limpa_texto 
                        btExcluir += hidden;
                        table.row.add( [$rat_add,btExcluir] ).draw( );
                        valor = valor + 1;
                        $('#myInput').attr('onclick', 'incluiRat('+valor+');' );
                    }
                }
            ";
        
        addPortaljavaScript($ret);
    }
    
}