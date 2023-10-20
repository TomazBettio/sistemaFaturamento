<?php
/*
 * Data Criação: 07/11/2014 - 10:15:03
 * Autor: Thiel
 *
 * Arquivo: tws.canhoto.inc.php
 *
 * Programa para realizar o apontamento do retorno do canhoto das NFs
 * 
 *  Alterações:
 *             16/11/2018 - Emanuel - Migração para intranet2
 *             01/02/2023 - Rafael Postal - Migração para intranet4
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');


class canhoto{
    var $funcoes_publicas = array(
        'index' 		=> true,
        'ajax'		=> true,
    );
    
    var $_linhas;
    var $_colunas;
    
    var $_userWT;
    
    function __construct(){
        $this->_linhas = 10;
        $this->_colunas = 4;
        
        $this->_userWT = '';
    }
    
    function index(){
        $form = $this->printForm();
        $this->addScript();
        return $form;
    }
    
    function ajax(){
        $nota = $_GET['nota'];
        $data = $_GET['data'];
        $dataT = explode('/', $data);
        
        if($this->_userWT == ''){
            //$sql = "SELECT matricula FROM pcempr WHERE NOME_GUERRA = '".strtoupper($app->user->user)."'";
            $sql = "SELECT matricula FROM pcempr WHERE NOME_GUERRA = '".strtoupper(getUsuario())."'";
            $rows = query4($sql);

            if(count($rows) > 0){
                $this->_userWT = $rows[0][0];
            }else{
                //$sql = "SELECT matricula FROM pcempr WHERE matricula = '".$app->user->user."'";
                $sql = "SELECT matricula FROM pcempr WHERE matricula = '".getUsuario()."'";
                $rows = query4($sql);
                if(is_array($rows) && count($rows) > 0){
                    $this->_userWT = $rows[0][0];
                }
            }
            //putAppVar('canhoto_userWT', $this->_userWT); //novo
        }
        
        $usuario = $this->_userWT;
        if(checkdate( $dataT[1], $dataT[0], $dataT[2]) === false){
            return '<div style="color: red;">'.$nota.' - '.$data.' - Invalida</div>';
            
        }
        //Verifica se a nota já não foi informada
        $sql = "SELECT CODFUNCLANC, DTCANHOTO, OBSNFCARREG FROM PCNFSAID WHERE NUMNOTA = $nota";
        $rows = query4($sql);
        if(!is_array($rows) || count($rows) == 0){
            return '<div style="color: red;">'.$nota.' - '.ajustaCaractHTML('Não existe!').'</div>';
        }else{
            if($rows[0][0] != 0){
                $sql = "update PCNFSAID set DTCANHOTO2 = to_date('".$data."','DD/MM/YYYY') where NUMNOTA = $nota";
                query4($sql);
                $sql = "INSERT INTO gf_canhotos (nota) VALUES ($nota);";
                query($sql);
                return '<div style="color: red;">'.$nota.' - '.ajustaCaractHTML('Nota já informada - Adicionada data 2!').'</div>';
            }else{
                $sql = "update PCNFSAID set CODFUNCLANC = $usuario, DTCANHOTO = to_date('".$data."','DD/MM/YYYY'), OBSNFCARREG = '' where NUMNOTA = $nota";
                query4($sql);
                $sql = "INSERT INTO gf_canhotos (nota) VALUES ($nota);";
                query($sql);
                return '<strong>'.$nota.' - '.$data.' - '.ajustaCaractHTML('Incluída!').'</strong>';
            }
        }
        return $nota.' - '.$data;
    }
    
    function printForm(){
        global $nl;
        $ret = '';
        
        $ret .= '<table border="0" cellpadding="2" cellspacing="2" width="100%">'.$nl;
        $ret .= '<tr>'.$nl;
        $ret .= '<td height="40" align="center" width="60%" valign="top">'.$nl;
        
        $temp= '<table border="0" cellpadding="0" cellspacing="0" width="100%">'.$nl;
        $param = array();
        $param['nome']		= "form_data";
        $param['valor']		= date('d/m/Y');
        $param['label']		= 'Data:'; //novo
        //$param['classadd']	= "";
        //$param['class']		= "";
        //$param['onkeypress']= "";
        //$param['onchange']	= "";
        
        $temp .= '<tr>'.$nl;
        $temp .= '<td height="40" align="center">Data: '.formbase01::formData($param).'</td>'.$nl;
        $temp .= '</tr>'.$nl;
        
        $param = array();
        $param['valor'] = "";
        $param['classeadd'] = ' pulaCampo';
        $param['nome'] = 'canhoto';
        $param['id'] = 'canhoto';
        $temp .= '<tr>'.$nl;
        $temp .= '<td height="40" align="center">Canhoto: '.formbase01::formTexto($param).'</td>'.$nl;
        $temp .= '</tr>'.$nl;
        $temp .= '</table>'.$nl;
        
        //$ret .= tabela3("Lancamento de Canhoto NF", $temp, "100%");
        // $ret .= addBoxInfo("Lancamento de Canhoto NF", $temp); // função não existe mais no intranet4
        $ret .= addCard(['conteudo' => $temp, 'titulo' => 'Lancamento de Canhoto NF']);
        
        $ret .= '</td>'.$nl;
        //$ret .= '<td height="40" align="center" width="40%" valign="top">'.tabela3("NF Lancadas", '<div id="lancadas"></div>',"100%").'</td>'.$nl;
        $ret .= '<td height="40" align="center" width="40%" valign="top">'.addCard(['conteudo' => '<div id="lancadas"></div>', 'titulo' => 'NF Lancadas']).'</td>'.$nl;
        $ret .= '</tr>'.$nl;
        $ret .= '</table>'.$nl;
        
        //$ret = formbase::formForm($ret, 'index.php?menu=gflogistica.canhoto.atualiza', 'canhotos');
        
        
        
        return $ret;
    }
    
    function addScript(){
        global $nl;
        addPortalJquery("$('#canhoto').focus();  ");
        addPortalJquery("$('.pulaCampo').keypress(function(e){"); //* * verifica se o evento é Keycode (para IE e outros browsers) * se não for pega o evento Which (Firefox) */
        addPortalJquery("	var tecla = (e.keyCode?e.keyCode:e.which);"); //* verifica se a tecla pressionada foi o ENTER */
        addPortalJquery("if(tecla == 13){");
        addPortalJquery("	dataApont = $('#form_data').val();");
        addPortalJquery("	if (dataApont == ''){  ");
        addPortalJquery("		alert ('Preencha o campo com a data!');     ");
        addPortalJquery("		$('#form_data').focus();  ");
        addPortalJquery("		return false;  ");
        addPortalJquery("	}");
        addPortalJquery("	nota = $('#canhoto').val();");
        addPortalJquery("	$.ajax({");
        addPortalJquery("		  url: '".getLinkAjax('atualiza')."&nota=' + nota + '&data=' + dataApont,");
        addPortalJquery("		  success: function(data) {");
        //addPortalJquery("		    alert('1'+data);");;
        addPortalJquery("		    $('<p style=\"font-size: 12px;\"/>').html(data).prependTo('#lancadas');").$nl;
        //addPortalJquery("		    alert('2'+data);");
        addPortalJquery("			$('#canhoto').val('');");
        addPortalJquery("		  }");
        addPortalJquery("		});");
        
        addPortalJquery("	campo = $('.pulaCampo');");
        addPortalJquery("	indice = campo.index(this);");
        addPortalJquery("	if(campo[indice+1] != null){");
        addPortalJquery("		proximo = campo[indice + 1];");
        addPortalJquery("		proximo.focus(); ");
        addPortalJquery("	} ");
        addPortalJquery("}else{ return true;}");
        addPortalJquery("e.preventDefault(e); ");
        addPortalJquery("return false; ");
        addPortalJquery("})");
    }
}