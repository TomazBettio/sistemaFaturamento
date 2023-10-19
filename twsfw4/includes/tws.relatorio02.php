<?php
if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

//ini_set('display_errors', 1);
//ini_set('display_startup_erros', 1);
//error_reporting(E_ALL);

class relatorio02 extends relatorio01{
    protected $_modoConversaoPdf = '';
    
    public function setModoConversaoPdf($modo){
        $modo = strtoupper($modo);
        if(in_array($modo, ['HTML2PDF','WKHTMLTOPDF'])){
            $this->_modoConversaoPdf = $modo;
        }
        else{
        }
    }
    
    public function __toString(){
        $ret = '';
        
        $operacao = getOperacao();
        $filtro = '';
        $quantDados = 0;
        if($operacao == 'sysParametros'){
            $ret .= $this->_sys020->formulario($this->_programa, $this->_titulo);
        }elseif($operacao == 'sysParametrosGravar'){
            $ret .= $this->_sys020->gravaFormulario($this->_programa);
            addPortalMensagem('', 'Configurações alteradas com sucesso!');
            $ret = '';
        }
        
        if(empty($ret)){
            // Mostra o relatório (não é alterar parametros)
            if(count($this->_quantDados) > 0){
                foreach ($this->_quantDados as $quant){
                    $quantDados += $quant;
                }
            }
            
            // Se existir dados a serem mostrados esconde o filtro
            if($quantDados > 0){
                addPortalJquery("$('#formFiltro').hide();");
            }
            
            if($this->_mostraFiltro){
                $filtro .= $this->_filtro;
            }
            
            if($this->_toPDF && !$this->_auto && $quantDados > 0){
                $botao = [];
                $botao['onclick']= 'window.open(\''.$this->_linkPDF.'\')';
                $botao['texto']	= 'PDF';
                $botao['id'] = 'bt_pdf';
                $this->_botaoTitulo[] = $botao;
            }
            //print_r($this->_campos);
            if($this->_toExcel && !$this->_auto && $quantDados > 0){
                $excel = new excel02($this->_arqExcel);
                foreach ($this->_browser as $secao => $tabela){
                    if(isset($this->_tituloSecao[$secao]['worksheet'])){
                        $excel->addWorksheet($secao, $this->_tituloSecao[$secao]['worksheet']);
                    }else{
                        $excel->addWorksheet($secao, 'Planilha '.$secao);
                    }
                    $dadosExcel = $tabela->getDados();
                    //Adiciona o total a tabela excel
                    if(isset($this->_dadosTfoot[$secao])){
                        $dadosExcel[] = $this->_dadosTfoot[$secao];
                    }
                    $excel->setDados($this->_cab[$secao], $dadosExcel, $this->_campo[$secao],$this->_tipo[$secao]);
                }
                //$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
                //$excel->grava();
                $excel->grava();
                unset($excel);
                
                if(!$this->_btExcel){
                    $botao = [];
                    $botao['onclick']= 'window.open(\''.$this->_linkExcel.'\')';
                    $botao['texto']	= $this->_textoExportaPlanilha;
                    $botao['id'] = 'bt_excel';
                    $this->_botaoTitulo[] = $botao;
                    $this->_btExcel = true;
                }
            }
            
            if($this->_filtro->getQuantPerguntas() > 0 && !$this->_auto && $this->_mostraFiltro){
                $botao = [];
                $botao["onclick"]= "$('#formFiltro').toggle();";
                $botao["texto"]	= "Par&acirc;metros";
                $botao["id"] = "bt_form";
                $this->_botaoTitulo[] = $botao;
                $ret .= $filtro;
            }
            
            if($this->_botaoConfigura){
                $botao = [];
                $botao['onclick']= 'setLocation(\''.getLink().'index.sysParametros\')';
                $botao['texto']	= 'Configurações';
                $botao['id'] = 'btConfigurar';
                $botao['icone'] = 'fa-cog';
                $this->_botaoTitulo[] = $botao;
            }
            
            if($this->_btCancela){
                $botao = [];
                $botao['onclick'] = "setLocation('".$this->_linkCarcela."')";
                //$botao['tamanho'] = 'pequeno';
                $botao['cor'] = 'danger';
                $botao['texto'] = 'Cancelar';
                $botao['id'] = 'bt_cancela';
                $this->_botaoTitulo[] = $botao;
            }
            
            //print_r($this->_tituloSecao);
            if(count($this->_browser ) > 0){
                foreach ($this->_browser as $secao => $tabela){
                    if(isset($this->_tituloSecao[$secao]['titulo'])){
                        $ret .= '<h2>'.$this->_tituloSecao[$secao]['titulo'];
                        if(isset($this->_tituloSecao[$secao]['sub'])){
                            $ret .= '<small>'.$this->_tituloSecao[$secao]['sub'].'</small>';
                        }
                        $ret .= '</h2>'."\n";
                    }
                    $ret .= $tabela;
                }
            }
            
            if($this->_toPDF && !$this->_auto && $quantDados > 0){
                $htmlPDF = '';
                $paramTabPdf = [];
                
                foreach ($this->_browser as $secao => $tabela){
                    $tabPdf = new tabela_pdf($paramTabPdf);
                    
                    if($this->_stripePDF['stripe'] === true){
                        $tabPdf->setStripe(true);
                        if(isset($this->_stripePDF['cor1']) && !empty($this->_stripePDF['cor1']) && isset($this->_stripePDF['cor2']) && !empty($this->_stripePDF['cor2'])){
                            $tabPdf->setCorStripe($this->_stripePDF['cor1'], $this->_stripePDF['cor2']);
                        }
                    }
                    
                    $titulo =  isset($this->_tituloSecaoPDF[$secao]['titulo']) ? $this->_tituloSecaoPDF[$secao]['titulo'] : '';
                    $sub =  isset($this->_tituloSecaoPDF[$secao]['sub']) ? $this->_tituloSecaoPDF[$secao]['sub'] : '';
                    $tabPdf->setTitulo($titulo, $sub);
                    
                    
                    $dadosTabela = $tabela->getDados();
                    //Adiciona o total a tabela
                    if(isset($this->_dadosTfoot[$secao])){
                        $tabPdf->setDadosTotais($this->_dadosTfoot[$secao]);
                    }
                    $tabPdf->setTabela($this->_campo[$secao], $this->_cab[$secao], $this->_width[$secao], $this->_posicao[$secao], $this->_tipo[$secao]);
                    $tabPdf->setDados($dadosTabela);
                    $tabPdf->setFooter($this->_footer);
                    
                    $htmlPDF .= $tabPdf;
                }
                //echo "<br><br><br><br>$htmlPDF<br><br><br><br>";
                
                $paramPDF = [];
                $paramPDF['orientacao'] = 'L';
                $PDF = new pdf_exporta02($paramPDF);
                $PDF->setModoConversao($this->_modoConversaoPdf);
                if($this->_cabecalhoPDF != ''){
                    $htmlPDF = $this->_cabecalhoPDF . $htmlPDF;
                }
                $PDF->setHTML($htmlPDF);
                if(count($this->_cabPDF) > 0){
                    $PDF->setHeader($this->getHeaderPDF(), $this->_cabPDF['altura']);
                }elseif(!empty($this->_headerPDF)){
                    $PDF->setHeader($this->_headerPDF, $this->_headerAltPDF);
                }
                $PDF->grava( $this->_arqPDF);
                unset($PDF);
                
            }
            
            if($quantDados <= 0){
                if(!empty($this->_textoSemDados)){
                    $ret .= $this->_textoSemDados;
                }else{
                    $ret .= "Nao existem dados!";
                }
            }
            
            $param = [];
            if(count($this->_botaoTitulo) > 0){
                foreach ($this->_botaoTitulo as $botao){
                    $param['botoesTitulo'][] = $botao;
                }
            }
            
            $param['titulo'] = $this->_titulo;
            $param['conteudo'] = $ret;
            $ret = addCard($param);
        }
        
        return $ret;
    }
    
    public function enviaEmail($para, $titulo = '', $param = []){
        $de 			= $param['de'] ?? [];
        $bcc			= $param['copiaOculta'] ?? '';
        $emailsender	= $param['emailsender'] ?? [];
        $embeddedImage	= $param['embeddedImage'] ?? [];
        $responderPara	= $param['responderPara'] ?? [];
        $agendado		= $param['agendado'] ?? false;
        $dia			= $param['dia'] ?? '';
        $hora			= $param['hora'] ?? '08:00';
        
        $msgIni			= $param['msgIni'] ?? '';
        $msgFim			= $param['msgFim'] ?? '';
        $mensagem 		=  $param['mensagem'] ?? '';
        
        $anexos = [];
        $msg = '';
        
        if(!empty($msgIni)){
            $msg = $msgIni;
        }
        
        //Passa as tabelas como AUTO
        if(count($this->_browser) > 0){
            foreach ($this->_browser as $secao => $b){
                $this->_browser[$secao]->setAuto(true);
            }
        }
        
        if($this->_toExcel && $this->_quantDados[0] > 0){
            
            //			$excel = new excel01($this->_arqExcel);
            //			$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
            //			$excel->grava();
            //			$anexos[] = $this->_arqExcel;
            //			unset($excel);
            
            $excel = new excel02($this->_arqExcel);
            foreach ($this->_browser as $secao => $tabela){
                if(isset($this->_tituloSecao[$secao]['worksheet'])){
                    $excel->addWorksheet($secao, $this->_tituloSecao[$secao]['worksheet']);
                }else{
                    $excel->addWorksheet($secao, 'Planilha '.$secao);
                }
                $dadosExcel = $tabela->getDados();
                //Adiciona o total a tabela excel
                if(isset($this->_dadosTfoot[$secao])){
                    $dadosExcel[] = $this->_dadosTfoot[$secao];
                }
                $excel->setDados($this->_cab[$secao], $dadosExcel, $this->_campo[$secao],$this->_tipo[$secao]);
            }
            //$excel->setDados($this->_cab, $this->_browser->getDados(),$this->_campo,$this->_tipo);
            //$excel->grava();
            $excel->grava();
            unset($excel);
            
            $anexos[] = $this->_arqExcel;
        }
        
        
        if($this->_toPDF && $this->_quantDados[0] > 0){
            $htmlPDF = '';
            $paramTabPdf = array();
            
            foreach ($this->_browser as $secao => $tabela){
                $tabPdf = new tabela_pdf($paramTabPdf);
                
                $titulo =  isset($this->_tituloSecaoPDF[$secao]['titulo']) ? $this->_tituloSecaoPDF[$secao]['titulo'] : '';
                $sub =  isset($this->_tituloSecaoPDF[$secao]['sub']) ? $this->_tituloSecaoPDF[$secao]['sub'] : '';
                $tabPdf->setTitulo($titulo, $sub);
                
                
                $dadosTabela = $tabela->getDados();
                //Adiciona o total a tabela
                if(isset($this->_dadosTfoot[$secao])){
                    $tabPdf->setDadosTotais($this->_dadosTfoot[$secao]);
                }
                $tabPdf->setTabela($this->_campo[$secao], $this->_cab[$secao], $this->_width[$secao], $this->_posicao[$secao], $this->_tipo[$secao]);
                $tabPdf->setDados($dadosTabela);
                $tabPdf->setFooter($this->_footer);
                
                $htmlPDF .= $tabPdf;
            }
            //echo "<br><br><br><br>$htmlPDF<br><br><br><br>";
            
            $paramPDF = array();
            $paramPDF['orientacao'] = 'L';
            $PDF = new pdf_exporta02($paramPDF);
            $PDF->setModoConversao($this->_modoConversaoPdf);
            if($this->_cabecalhoPDF != ''){
                $htmlPDF = $this->_cabecalhoPDF . $htmlPDF;
            }
            $PDF->setHTML($htmlPDF);
            if(count($this->_cabPDF) > 0){
                $PDF->setHeader($this->getHeaderPDF(), $this->_cabPDF['altura']);
            }elseif(!empty($this->_headerPDF)){
                $PDF->setHeader($this->_headerPDF, $this->_headerAltPDF);
            }
            $PDF->grava( $this->_arqPDF);
            $anexos[] = $this->_arqPDF;
            unset($PDF);
            
        }
        
        if($titulo == ""){
            $titulo = $this->_titulo;
        }
        
        if(!empty($this->_mensagem_inicio_email)){
            $msg .= $this->_mensagem_inicio_email;
        }
        
        if(count($this->_browser ) > 0){
            foreach ($this->_browser as $secao => $tabela){
                if(isset($this->_tituloSecao[$secao]['titulo'])){
                    $msg .= '<h2>'.$this->_tituloSecao[$secao]['titulo'];
                    if(isset($this->_tituloSecao[$secao]['sub'])){
                        $msg .= '<small>'.$this->_tituloSecao[$secao]['sub'].'</small>';
                    }
                    $msg .= '</h2>'."\n";
                }
                $msg .= $tabela;
            }
        }
        
        //Se o email ficar muito grande ou não envia tabela no corpo do email
        //TODO: mesmo que a mensagem seja grande, mas não for para enviar planilha ou pdf deve ir no corpo da mensagem
        if(strlen($msg) > 2000000 || count($this->_campo) > 56 || $this->_enviaTabelaCorpoEmail == false){
            $msg = "Segue anexo o relatório ".$titulo.".";
        }
        
        if(empty($msg)){
            if(!empty($this->_textoSemDados)){
                $msg = $this->_textoSemDados;
            }else{
                $msg = "Não existem dados!";
            }
        }
        
        if(!empty($msgFim)){
            $msg .= $msgFim;
        }
        
        if(!empty($mensagem)){
            $msg = $mensagem;
        }
        
        
        $param = [];
        $param['emailsender'] 	= $de;
        $param['destinatario'] 	= $para;
        $param['mensagem'] 		= $msg;
        $param['assunto'] 		= $titulo;
        $param['anexos'] 		= $anexos;
        $param['embeddedImage'] = $embeddedImage;
        $param['responderPara'] = $responderPara;
        $param['bcc'] 			= $bcc;
        enviaEmail($param);
    }
}