<?php
class formfiltro02 extends formfiltro01{
    public function __toString(){
        global $nl;
        formbase01::setLayout('basico');
        $campos = $this->imprimeParametros();
        
        $ret = '<form id="formFiltro" name="form1" method="post" action="'.$this->_link.'">'.$nl;
        $token = geraStringAleatoria(30);
        putAppVar('token_form_filtro', $token);
        $ret .= '<input name="fwFiltro" type="hidden" value="'.$token.'" />'.$nl;
        $ret .= $campos;
        
        $param = [];
        $param['bloco'] 	= true;
        $param['texto'] 	= $this->_botaoEnviarTexto;
        $param['type'] 		= 'submit';
        $param['cor'] 		= $this->_botaoEnviarCor;
        //$param['tamanho'] 	= 'pequeno';
        $bt_enviar = formbase01::formBotao($param);
        
        $param = [];
        $param['bloco'] 	= true;
        $param['texto'] 	= 'Minimizar';
        $param['cor'] 		= 'danger';
        $param["data-widget"]= "control-sidebar";
        //$param['tamanho'] 	= 'pequeno';
        $bt_minimizar = formbase01::formBotao($param);
        
        $ret .= addLinha(['tamanhos' => [6, 6], 'conteudos' => [$bt_enviar, $bt_minimizar]]);
        
        $ret .= '</form>'.$nl;
        
        //$ret = '<div style="overflow-y:auto;">' . $ret . '</div>' . $nl;
        
        putAppVar('conteudoControlSidebar', $ret);
        return '';
    }
    
    function imprimeParametros(){
        global $nl;
        $ret = '';
        
        $quant = count($this->_perguntas);
        if($quant == 0){
            $this->getPerguntas();
            $quant = count($this->_perguntas);
        }
        for($i=0;$i<$quant;$i++){
            $ret .= $nl.'<div class="row">'.$nl;
                $ret .= $this->imprimeColuna(0, $i);
            $ret .= '</div>'.$nl;
        }
        return $ret;
    }
    
    protected function imprimeColuna($i,$pos){
        global $nl;
        $param = [];
        $ret = '';
        $formLayoutAtual = getAppVar('formBase_layout');
        formBase01::setLayout($this->_layout);
        if(count($this->_perguntas) > ($i * $this->_colunas + $pos)){
            $pergunta = $this->_perguntas[$i * $this->_colunas + $pos];
            
            $selecionado = isset($this->_retorno[$pergunta['variavel']]) ? $this->_retorno[$pergunta['variavel']] : '';
            
            if($selecionado == "" && $pergunta['inicializador'] != ""){
                $selecionado = $pergunta['inicializador'];
            }elseif($selecionado == "" && $pergunta['inicFunc'] != ""){
                //@todo: implementar função para inicializar
            }
            if($pergunta['tabela'] != ''){
                $form = $this->montaSelect($pergunta['tabela'], $pergunta['variavel'],$selecionado, $pergunta['tipo']);
            }elseif($pergunta['funcaodados'] != ''){
                $form = $this->montaSelect2($pergunta['funcaodados'], $pergunta['variavel'], $pergunta['tipo'],$selecionado);
            }elseif($pergunta['tipo'] == "D"){
                $form = $this->montaData($pergunta['variavel'],$selecionado);
            }elseif($pergunta['opcoes'] != ""){
                $form = $this->montaSelect2($pergunta['opcoes'], $pergunta['variavel'], $pergunta['tipo'],$selecionado,2);
            }elseif($pergunta['tipo'] == "TA"){
                $param['nome']	= self::getNomeCampo($pergunta['variavel']);
                $param['valor']	= $selecionado;
                $param['id']	= self::getNomeCampo($pergunta['variavel']);
                $param['linhas'] = 5;
                $form = formBase01::formTextArea($param);
            }else{
                $param['nome']	= $this->getNomeCampo($pergunta['variavel']);
                $param['valor']	= $selecionado;
                $param['id']	= $this->getNomeCampo($pergunta['variavel']);
                
                $form = formBase01::formTexto($param);
            }
        }
        $tam = 12;
        $ret .= '	<div class="col-md-'.$tam.'">';
        if(isset($pergunta)){
            if(!isset($param['id']) || $param['id'] == ''){
                $param['id'] = $this->getID($pergunta['variavel']);
            }
            $ret .= Formbase01::formLinha($form,$pergunta['pergunta'],$param['id'],$pergunta['help']);
        }else{
            $ret .= '&nbsp;';
        }
        $ret .= '</div>'.$nl;
        
        putAppVar('formBase_layout', $formLayoutAtual);
        
        return $ret;
    }
}