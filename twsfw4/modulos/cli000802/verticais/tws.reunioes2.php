<?php
/*
 * Data Criação: 09/08/2023
 * Autor: BCS
 *
 * Controle de reuniões com consultas no BD pelo FastAPI
 *
 **/

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class reunioes2{
    
    //MINHAS TABELAS
    //Tabela de Reuniões 'adm_reunioes';
    //Tabela de pautas 'adm_pauta';
    //Tabela de participantes 'adm_participantes';
    //Tabela de ações (mesma do centralizador de ações) 'adm_acoes';
    //tabela de comentários 'adm_comentarios';
    //tabela de anexos 'adm_anexo';
    
    
    //caminho para salvar os anexos
    private $_anexopath = '/var/www/crm/anexosReunioes/';
    
    private $_rest;
    
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'        => true,
        'editarPauta'   => true,
        'salvar'        => true,
        'excluir'       => true,

        'ajax'          => true
    );
    
   
    public function __construct(){
        $this->_rest = new rest_fast('http://api02.twslabs.com.br/rest','admin','q1w2e3');
        $temp = ' '." 

            function salvarCardEditado(id,reuniao,pauta){
                if(verificaObrigatoriosModal(id)){
                    var elementos = document.getElementsByClassName('campoEditarCard');
                    var link = '" . getLinkAjax('salvaEditar') . "' + '&id=' + id + '&reuniao=' + reuniao + '&pauta=' + pauta;
                    var objeto = {};
                    for(let elemento of elementos){
                        objeto[elemento.id] = elemento.value;
                    }
                    $.post(link, objeto, function(retorno){
                        location.reload();
                    });
                }
            }
            ".'
			function marcarTodos(modulo, marcado){
				$("." + modulo).each(function(){$(this).prop("checked", marcado);});
			}'."

            function excluirRat(e){
				var t = $('#tabRatID').DataTable();
				t.row( $(e).parents('tr') ).remove().draw();
            }

            function incluiRat(valor){
				var t = $('#tabRatID').DataTable();
        
				var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";
        
				var titulo = \"<input  type='text' name='formOS[pauta][titulo][]' value='' style='width:100%;text-align: right;' id='\"+valor+\"tabelacampotitulo' class='form-control  form-control-sm'          >\";
                var texto = \"<input  type='text' name='formOS[pauta][descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
        
					t.row.add( [titulo, texto, bt] ).draw( false );
        
                    valor = valor + 1;
                    $('#myInput').attr('onclick', 'incluiRat('+valor+');' );
            }

            function adicionarComentario(id){
                var comentario = {};
                comentario['texto'] = document.getElementById('novoComentario').value;
                document.getElementById('novoComentario').value = '';
                var link = '" . getLinkAjax('addComentario') ."' + '&id=' + id;
                $.post(link, comentario, function(retorno){
                    var linha_tempo = document.getElementById('linhaTempo');
                    linha_tempo.innerHTML = retorno;
                })
            }
        
            function btReply(id_pai, tabela, id_tabela){
                var comentario = {};
                comentario['texto'] = document.getElementById('novoComentarioReply').value;
                document.getElementById('novoComentarioReply').value = '';
                var link = '" . getLinkAjax('btReply') ."' +'&id_pai=' + id_pai + '&id=' + id_tabela + '&tabela=' + tabela;
                $.post(link, comentario, function(retorno){
                    var linha_tempo = document.getElementById('linhaTempo');
                    linha_tempo.innerHTML = retorno;
                })
            }
            
            function enviarArquivoAnexo(id){
                var arquivo = document.getElementById('id_campo_anexo').files[0];
                var formData = new FormData();
                formData.append('arquivo', arquivo);
                var xhr = new XMLHttpRequest();
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Requisição concluída com sucesso
                        document.getElementById('idBlocoAnexo').innerHTML = xhr.response;
                    }
                else {
                        // Ocorreu um erro durante a requisição
                        console.error('Ocorreu um erro durante a requisição.');
                    }
                };
                var link_servidor = '" . getLinkAjax('salvarAnexo') . "' + '&id=' + id;
                xhr.open('POST', link_servidor, true);
                xhr.send(formData);
            }


			";
        addPortaljavaScript($temp);
    }
    
    
    public function index(){
        $ret = '';
        
        //print_r($this->_rest->dadosIndex());
        //print_r($this->_rest->mandaReq(1));
        
        $rel = new tabela01(['titulo' => 'Reuniões']);
        $rel->addColuna(array('campo' => 'data'             , 'etiqueta' => 'DATA'         , 'tipo' => 'D', 'width' =>  160, 'posicao' => 'E'));
        $rel->addColuna(array('campo' => 'titulo'           , 'etiqueta' => 'Assunto'       , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $rel->addColuna(array('campo' => 'status'           , 'etiqueta' => 'Status'    , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $rel->addColuna(array('campo' => 'nro_acoes'        , 'etiqueta' => 'Ações Criadas'    , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $rel->addColuna(array('campo' => 'nro_acoes_realiza', 'etiqueta' => 'Ações Realizadas'    , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        
        $param=([
            'id' => 'incluir',
            'onclick' => "setLocation('".getlink()."editar&reuniao=0')",
            'texto' => 'Criar Nova Reunião'
        ]);
        $rel->addBotaoTitulo($param);
        
        
        $param=([
            'texto' =>  'Atualizar',
            'link' 	=> getLink()."editar&reuniao=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'success',
        ]);
        $rel->addAcao($param);
        
        $this->jsConfirmaExclusao('"Você REALMENTE deseja excluir essa reunião?"');
        $param=([
            'texto' =>  'Excluir',
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&reuniao=','{ID}')",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'danger',
        ]);
        $rel->addAcao($param);
        
        $dados = $this->getDadosIndex();
        $rel->setDados($dados);
        
        $ret .= $rel;
        return $ret;
    }
    
    private function jsConfirmaExclusao($titulo){
        $ret = "
            function confirmaExclusao(link,id){
            	if (confirm('$titulo')){
            		setLocation(link+id);
            	}
            }
        ";
        addPortaljavaScript($ret);
    }
    
    public function ajax()
    {
        $op = getOperacao();
        $id = getParam($_GET, 'id', 0);
        if(true){//$id != 0 && $tabela != ''){
            switch ($op){
                case 'salvaEditar': //TODO: Salvar inclusão/edição de ação do modal
                    log::gravaLog('post.txt', json_encode($_POST));
                    //var_dump($_POST);die();
                    $dadosAcao = $_POST;
                    $reuniao = getParam($_GET, 'reuniao');
                    $pauta = getParam($_GET, 'pauta');
                    return $this->salvarAcao($id,$dadosAcao,$reuniao,$pauta);
                    break;
                case 'addComentario':
                    return $this->salvarComentario($id);
                    break;
                case 'salvarAnexo':
                    return $this->salvarAnexo($id);
                    break;
                case 'mostrarAnexo':
                    $anexo = $_GET['arquivo'] ?? '';
                    $download = $_GET['download'] ?? '';
                    return $this->mostrarAnexo($id, $anexo, $download);
                    break;
                default :
                    return $this->index();
            }
        } else {
            return redireciona(getLink() . 'index');
        }
    }
    
    private function getDadosIndex()
    {
        $ret = [];
        
        $dados = $this->_rest->dadosIndex();
        $dados = $dados['dados'];
        if(is_array($dados) && count($dados)>0)
        {
            foreach($dados as $dado)
            {
                $temp = $dado;
                $temp['convidados'] = $this->getParticipantesReuniao($temp['id'], 'C');
                $temp['participantes'] = $this->getParticipantesReuniao($temp['id'], 'P');
                $temp['status'] = $this->getStatusReuniao($dado['status']);
                $nroAcoes = $this->getNroAcoes($dado['id']);
                $temp['nro_acoes'] = $nroAcoes['total'];
                $temp['nro_acoes_realiza'] = $nroAcoes['realizadas'];
                $ret[] = $temp;
            }
        }
            
        return $ret;
    }
    
    
    private function getNroAcoes($id)
    {
        $ret = [
            'total'=>0,
            'realizadas'=>0
        ];
        
        $minhasPautas = $this->getPautasReuniao($id);
        foreach($minhasPautas as $pauta)
        {
            $acoes = $this->getAcoes($pauta['id'], $id);
            $ret['total'] += count($acoes);
            foreach($acoes as $acao){
                if($acao['status'] == 'CONC'){
                    $ret['realizadas'] += 1;
                }
            }
        }
        return $ret;
    }
    
    private function getStatusReuniao($status)
    {
        $ret = '';
        switch($status)
        {
            case 'A':
                $ret .= 'Agendada';
                break;
            case 'E':
                $ret .= 'Em Progresso';
                break;
            case 'F':
            default:
                $ret .= 'Finalizada';
                break;
        }
        return $ret;
    }
    
    private function getParticipantesPauta($id_reuniao)
    {
        $ret = [];
        
        $dados = $this->_rest->getParticipantesPauta($id_reuniao);
        //print_r($dados);
        $dados = $dados['participantes'];
        if(is_array($dados) && count($dados)>0){
            foreach($dados as $dado){
                $ret[] = $dado['user'];
            }
        }
        
        return $ret;
    }
    
    private function getParticipantesReuniao($id_reuniao, $tipo)
    {
        $ret = '';
        
        $dados = $this->_rest->getParticipantesReuniao($id_reuniao, $tipo);
        //print_r($dados);
        //var_dump($dados);
        $dados = $dados['dados'];
        if(is_array($dados) && count($dados)>0){
            foreach($dados as $dado){
                $ret .= $dado['nome'].";";
            }
        }
        
        return $ret;
    }
    
    
    
    private function editPautaGeral($dados_reuniao,$dados_pauta)
    {
        $ret = '';
        $form = new form01();
        
        $form->setBotaoCancela();
        
        //campos reunião (readonly para a pauta)
        $form->addCampo(['id' => '','campo' => 'formOutro[data]  ','etiqueta' => 'Data da Reunião'  ,'tipo' => 'D','tamanho' => '15' ,'linhas' => '','valor' => $dados_reuniao['data']  ,'pasta'	=> 0,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false,'readonly' => true,]);
        //Campos pauta
        $form->addCampo(['id' => '','campo' => 'formOutro[titulo]','etiqueta' => 'Assunto'          ,'tipo' => 'T','tamanho' => '150','linhas' => '','valor' => $dados_reuniao['titulo'],'pasta'	=> 0,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => true, 'readonly' => true,]);
        $form->addCampo(['id' => '','campo' => 'formPauta[titulo]','etiqueta' => 'Pauta'            ,'tipo' => 'T','tamanho' => '150','linhas' => '','valor' => $dados_pauta['titulo']  ,'pasta'	=> 0,'lista' => '','validacao' => '','largura' => 4,'obrigatorio' => false,]);
        
        
        $ret .= addCard(['titulo'=>'Dados Gerais', 'conteudo'=>$form]);
        return $ret;
    }
    
    private function getComentariosPauta($id)
    {
        $ret = '';
        
        $novo_com = $this->montarBlocoNovoComentario($id, 'adm_pauta');
        
        $historico = '<div id="linhaTempo">' . $this->gerarHistoricoComentarios($id) . '</div>'. '<br>' . $novo_com . '<br>';

        $ret = addCard(['titulo' => 'Comentários','conteudo' => $historico]);
        return $ret;
    }
    
    private function getDetalhesPauta($texto)
    {
        $ret = '';
        
        $form = new form01();
        $form->addCampo(['id' => '','campo' => 'formPauta[descricao]','etiqueta' => '','tipo' => 'TA','tamanho' => '55','linhas' => '','valor' => $texto,'pasta'	=> 0,'lista' => '','validacao' => '','largura' => 12,'obrigatorio' => false,]);
        
        $ret = addCard(['titulo' => 'Detalhes','conteudo' => $form]);
        return $ret;
    }
    
    private function getAcoesPauta($pauta,$reuniao)
    {
        $ret = '';
        $ret .= $this->addHtmlModal($pauta,$reuniao);
        $this->geraScriptValidacaoModal();
        $conteudo = $this->conteudoCardAcoes($pauta, $reuniao);
        
        $param = [
            'titulo' => 'Ações',
            'conteudo' => $conteudo,
            'botoesTitulo' => [
                [
                    'onclick' => "$('#myModalIncluirEditar0').modal();",
                    'id' => 'myInputOpenModal',
                    'texto' => 'Incluir Ação',
                    'cor' => 'success',
                ]
            ]
        ];
            
        $ret .= addCard($param);
        return $ret;
    }
    
    private function conteudoCardAcoes($pauta,$reuniao)
    {
        $ret = '';
        $acoes = $this->getAcoes($pauta, $reuniao);
        foreach($acoes as $acao){
            $param = [
                'titulo'=>$acao['acao'],
                'conteudo'=>'',
                'botoesTitulo' => [
                    [
                        'onclick' => "$('#myModalIncluirEditar{$acao['id']}').modal();",
                        'id' => 'myInputOpenModal',
                        'texto' => 'Editar',
                        'cor' => 'success',
                    ],
                    [
                        'onclick' => "setLocation('".getLink()."excluir&pauta=$pauta&reuniao=$reuniao&acao=".$acao['id']."')",
                        'id' => '',
                        'texto' => 'Excluir',
                        'cor' => 'danger',
                    ]
                ]
            ];
            $ret .= addCard($param);
            $ret .= $this->addHtmlModal( $pauta, $reuniao, $acao['id']);
        }
        return $ret;
    }
    
    //MODAL AÇÕES
    
    private function addHtmlModal($id_pauta,$id_reuniao,$id=0)
    {
        $conteudo = '';
        $conteudo = $this->formModalIncluirEditar($id_pauta,$id_reuniao,$id);
        $ret = '
            <div class="modal fade" id="myModalIncluirEditar'.$id.'" data-backdrop="static">
                <div class="modal-dialog modal-xl" id="divTamanho">
                    <div class="modal-content">
            
                        <!-- Cabeçalho do modal -->
                        <div class="modal-header">
                            <h4 class="modal-title" id="titulo-modal">Ação</h4>
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
    
    private function formModalIncluirEditar($id_pauta,$id_reuniao,$id_acao=0)
    {
        $ret = '';
        
        $dados = $this->getDadosAcao($id_acao);
        
        $form = new form01([]);
        $form->addCampo(array('id' => '', 'campo' => "formAcao[acao]$id_acao"        , 'etiqueta' => 'Ação'         , 'tipo' => 'T' 	, 'tamanho' => '115', 'linhas' => '', 'valor' => $dados['acao']	        , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[departamento]$id_acao", 'etiqueta' => 'Departamento' , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['departamento']	, 'pasta'	=> 0, 'lista' => $this->valoresSYS5('DEPTO')	     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[responsavel]$id_acao" , 'etiqueta' => 'Responsável'  , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['responsavel'] 	, 'pasta'	=> 0, 'lista' => $this->getListaUsuarios()	         , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[status]$id_acao"      , 'etiqueta' => 'Status'       , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['status']	    , 'pasta'	=> 0, 'lista' => $this->valoresSYS5('STATUS')	     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[prazo]$id_acao"       , 'etiqueta' => 'Prazo'        , 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['prazo'] 	    , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[finalizado]$id_acao"  , 'etiqueta' => 'Finalizado em', 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['finalizado'] 	, 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false, 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[cliente]$id_acao"     , 'etiqueta' => 'Cliente'      , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['cliente']       , 'pasta'	=> 0, 'lista' => funcoes_cad::getListaClientes()	 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[privado]$id_acao"     , 'etiqueta' => 'Privado?'     , 'tipo' => 'A' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['privado']       , 'pasta'	=> 0, 'lista' => $this->valoresSYS5('000003')	     , 'validacao' => '', 'largura' => 4, 'obrigatorio' => true , 'classeadd' => 'campoEditarCard'));
        $form->addCampo(array('id' => '', 'campo' => "formAcao[obs]$id_acao"         , 'etiqueta' => 'Obs'          , 'tipo' => 'TA' 	, 'tamanho' => '215', 'linhas' => '', 'valor' => $dados['obs']	        , 'pasta'	=> 0, 'lista' => ''	                                 , 'validacao' => '', 'largura' => 4, 'obrigatorio' => false , 'classeadd' => 'campoEditarCard'));
        
        $ret .= $form;
        
        
        $param = [];
        $param['texto'] = 'Salvar';
        $param['onclick'] = "salvarCardEditado($id_acao,$id_reuniao,$id_pauta);";
        $param['id'] = "myModalIncluirEditar$id_acao";
        $param['cor'] = 'success';
        
        $ret .= formbase01::formBotao($param);
        
        return $ret;
    }
    
    private function geraScriptValidacaoModal(){
        $camposModal = [
            ['campo' => 'formAcao[acao]'        , 'etiqueta' => 'Ação'          ,'obrigatorio' => true],
            ['campo' => 'formAcao[departamento]', 'etiqueta' => 'Departamento'  ,'obrigatorio' => true],
            ['campo' => 'formAcao[responsavel]' , 'etiqueta' => 'Responsável'   ,'obrigatorio' => true],
            ['campo' => 'formAcao[status]'      , 'etiqueta' => 'Status'        ,'obrigatorio' => true],
            ['campo' => 'formAcao[prazo]'       , 'etiqueta' => 'Prazo'         ,'obrigatorio' => true],
            ['campo' => 'formAcao[finalizado]'  , 'etiqueta' => 'Finalizado em' ,'obrigatorio' => false],
            ['campo' => 'formAcao[cliente]'     , 'etiqueta' => 'Cliente'       ,'obrigatorio' => true],
            ['campo' => 'formAcao[privado]'     , 'etiqueta' => 'Privado?'      ,'obrigatorio' => true],
            ['campo' => 'formAcao[obs]'         , 'etiqueta' => 'Obs'           ,'obrigatorio' => false],
        ];
        
        $ret = "
            function verificaObrigatoriosModal(id)
            {
                	msg = '';
                    var conteudo = '';
";
        foreach ($camposModal as $c)
        {
            if($c['obrigatorio'] == true)
            {
                $id = $c['campo'];
                $id = str_replace("[","",$id);
                $id = str_replace("]","",$id);
                if(isset($c['select']) && $c['select'] === true){
                    $ret .= "
                        conteudo = $('#$id' + id + ' option:selected').val();
                    ";
                }else{
                    $ret .= "
                        conteudo = $('#$id' + id).val();
                    ";
                }
                $ret .= "
                    if(conteudo  === undefined || conteudo.trim() == '' ) {
                        msg += 'O campo ".$c['etiqueta']." deve ser preenchido!\\n'
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

    
  
    //fim modal ações
    public function editarPauta()
    {
        $ret = '';
        
        $pauta = getParam($_GET, 'pauta');
        $reuniao = getParam($_GET, 'reuniao');
        
        //dados reunião + dados pauta
        $dados_reuniao = $this->getInfoReuniao($reuniao);
        $dados_pauta = $this->getInfoPauta($pauta);
        
        $geral = $this->editPautaGeral($dados_reuniao,$dados_pauta);
        
        //linha: comentários, detalhes (texto), ações (edição em modal)
        $detalhes = $this->getDetalhesPauta($dados_pauta['descricao']);
        if($pauta == 0){
            $linha2 = $detalhes;
        } else {
            $comentario = $this->getComentariosPauta($pauta);
            $acoes = $this->getAcoesPauta($pauta,$reuniao);
            
            $linha2 = addLinha(['tamanhos' => [4,4,4],'conteudos' => [$comentario,$detalhes,$acoes]]);
        }
        
        
        $ret .= $geral . "<br>" . $linha2;
        //footer de envio do form
        $param = [
            'URLcancelar' => getLink()."editar&reuniao=$reuniao",
            'IDform' => 'formPauta',
        ];
        formbase01::formSendFooter($param);
        
        $param = [
            'id' => 'formPauta',
            'acao' => getLink()."salvar&reuniao=$reuniao&pauta=$pauta",
        ];
        $ret = formbase01::form($param, $ret);
        
        $param = [
            'conteudo' => $ret,
            'titulo' => 'Pauta',
        ];
        $ret = addCard($param);
        return $ret;
        return $ret;
    }
    
    private function getPautasReuniao($id_reuniao)
    {
        $ret = [];
        $dados = $this->_rest->dadosPauta($id_reuniao);
        $dados = $dados['dados'];
        if(is_array($dados) && count($dados)>0){
            $ret = $dados;
        }
        return $ret;
    }
    
    
    private function getListaUsuarios()
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = $this->_rest->getListaUsuarios();
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['user'];
                $temp[1] = $row['nome'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function valoresSYS5($tabela)
    {
        $ret = [];
        
        $temp = [];
        $temp[0] = '';
        $temp[1] = '';
        $ret[]=$temp;
        
        $rows = $this->_rest->getValoresSys5($tabela);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $temp[0] = $row['chave'];
                $temp[1] = $row['descricao'];
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function getDadosAcao($id)
    {
        $ret = [];
        $campos = ['acao','departamento','responsavel','status','prazo','finalizado','cliente','privado','obs'];
        $rows = $this->_rest->getDadosAcao($id);
        $rows = $rows['dados'];
        foreach($campos as $campo){
            if(is_array($rows) && count($rows)==1){
                $ret[$campo] = $rows[0][$campo];
            } else {
                $ret[$campo] = '';
            }
        }
        return $ret;
    }
    
    private function getAcoes($pauta, $reuniao)
    {
        $ret = [];
        $rows = $this->_rest->getAcoes($reuniao, $pauta);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows)>0){
            foreach($rows as $row){
                $temp = $row;
                $temp['cliente'] = $this->getClienteNreduz($temp['cliente']);
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function getClienteNreduz($cliente)
    {
        $ret = '';
        $rows = $this->_rest->getClienteNreduz($cliente);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) == 1){
            $ret = $rows[0]['nreduz'];
        }
        return $ret;
    }
    
    private function montaBlocoAnexo2($id, $readonly = false)
    {
        $ret = '';
        
        $tabela_anexos = new tabela01(['filtro' => false, 'info' => false, 'ordenacao' => false]);
        $tabela_anexos->addColuna(array('campo' => 'nome', 'etiqueta' => 'Arquivos'));

        $tipos_exibir = ['png', 'jpg'];

        $dados = array();
        $rows = $this->_rest->getAnexosReuniao($id);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $extensao = substr(strtolower($row['arquivo']), -3);
                $link = getLinkAjax('mostrarAnexo') . "&id=$id&arquivo=".$row['id'];
                if(in_array($extensao, $tipos_exibir)){
                    $html = '<a class="btn btn-tool" onclick="window.open(\'' . $link . '\', \'_blank\').focus();">' . $row['arquivo'] . '</a>';
                }
                else{               
                    $link .= "&download=1";
                    $html = '<a class="btn btn-tool" href="' . $link . '" download>' . $row['arquivo'] . '</a>';
                }
                
                
                $temp = array(
                    'nome' => $html
                );
                $dados[] = $temp;
            }
        }
        $tabela_anexos->setDados($dados);
        
        $form = new form01();
        $form->addCampo(array('tipo' => 'F', 'nome' => 'id_campo_anexo', 'id' => 'id_campo_anexo', 'campo' => 'id_campo_anexo', 'estilo' => 'opacity:0'));
        if($readonly){
            $ret = '<div id="idBlocoAnexo">' . $tabela_anexos . '<br></div>';
        } else {
            $bt_enviar_arquivo = formbase01::formBotao(array('texto' => 'Enviar Arquivo', 'onclick' => "enviarArquivoAnexo('$id')"));
            $ret = '<div id="idBlocoAnexo">' . $tabela_anexos . '<br>' . $form . '<br>' . $bt_enviar_arquivo . '</div>';
        }
        
        return $ret;
    }
    
    protected function salvarAnexo($id)
    {
        if(isset($_FILES['arquivo']) && isset($_FILES['arquivo']['tmp_name'])){
            global $config;
            $dir = ($config['anexosticket'] ?? '/var/www/crm/anexosReunioes/') . $id;
            if(!is_dir($dir)){
                mkdir($dir);
            }
            $origem = $_FILES['arquivo']['tmp_name'];
            $destino = $dir . '/' . $_FILES['arquivo']['name'];
            $arquivo_novo = pathinfo($destino, PATHINFO_BASENAME);
            
            if(file_exists($destino)){
                $i = 1;
                $nome_original = pathinfo($destino, PATHINFO_FILENAME);
                while(file_exists($destino)){
                    $partes = pathinfo($destino);
                    $arquivo_novo = $nome_original . "($i)." . $partes['extension'];
                    $destino = $partes['dirname'] . '/' .  $arquivo_novo;
                    $i++;
                }
            }
            
            //echo $destino;
            if(move_uploaded_file($origem, $destino)){
                $this->_rest->salvarAnexo($id, $arquivo_novo); //TODO: testar anexo
            }
        }
        
        return $this->montaBlocoAnexo2($id);
    }
  
    
    private function salvarComentario($id, $id_pai=0)
    {
        $ret = '';
        $texto = $_POST['texto'];
        $dados = [
            'id' => $id,
            'id_pai' => $id_pai,
            'usuario'=>getUsuario(),
            'texto'=>$texto,
        ];
        $dados = json_encode($dados);
        $this->_rest->salvarComentario($id, $dados);
        $ret = $this->gerarHistoricoComentarios($id);
        return $ret;
    }
    
    private function mostrarAnexo($id_tab, $id, $download){
        $rows = $this->_rest->getMeuAnexo($id);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) > 0){
            $file = $this->_anexopath . $id_tab  . "/" . $rows[0]['arquivo'];
            $this->mostrarArquivo($file, $download);
        }
    }
    
    function mostrarArquivo($file, $download){
        if(!empty($download)){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            header('Connection: close');
            ob_clean();
            flush();
            readfile($file);
        }
        else{
            header('Content-Type: image');
            header('Content-Length: ' . filesize($file));
            echo file_get_contents($file);
        }
        die();
    }
    
    private function montarBlocoNovoComentario($id, $tabela){
        $ret = '';
        $form = new form01();
        $form->addCampo(array('id' => 'novoComentario', 'campo' => 'formCard[novoComentario]'		, 'etiqueta' => ''			, 'linha' => 1, 'largura' =>12, 'tipo' => 'TA'	, 'tamanho' => '60', 'linhas' => '', 'valor' => ''		, 'lista' => '', 'funcao_lista' => "", 'validacao' => '', 'obrigatorio' => false, 'maxtamanho' => 100, 'classeadd' => 'campoComentario', 'linhasTA' => 6));
        $ret .= $form . '<br>';
        $ret .= formbase01::formBotao(array('texto' => 'Adicionar Comentário', 'onclick' => "adicionarComentario($id,'$tabela')"));
        return $ret;
    }
    
    private function gerarHistoricoComentarios($id){
        $ret = '';
        $rows=$this->_rest->getHistoricoComentarios($id);
        $rows=$rows['dados'];
        if(is_array($rows) && count($rows) > 0){

            $param = [
                'pai'=>[]
            ];
            
            $temp = [];
            
            $data = '';
            
            foreach($rows as $row)
            {
                
                $botao = [];
                $hora = datas::dataMS2H($row['dt_criado']);
                $nome = $this->getNomeUsuario($row['usuario']);
                
                if($data != datas::dataMS2D($row['dt_criado'])){
                    if(count($temp)!=0){
                        $param['pai'][] = $temp;
                    }
                    
                    $data = datas::dataMS2D($row['dt_criado']);
                    $temp = [
                        'titulo' => $data,
                        'cor' => 'bg-green',
                        'filho' => [],
                    ];
                    $filho = [
                        'titulo' => $hora . ' - ' . $nome,
                        'conteudo' => nl2br($row['conteudo']),
                        'icone' => 'fa-user',
                        'iconeCor' => 'bg-aqua',
                    ];
                } else {
                    $filho = [
                        'titulo' => $hora . ' - ' . $nome,
                        'conteudo' => nl2br($row['conteudo']),
                        'icone' => 'fa-user',
                        'iconeCor' => 'bg-aqua',
                        'botoes' => [$botao],
                    ];
                }
                $temp['filho'][] = $filho;
            }
            if(count($temp)!=0){
                $param['pai'][] = $temp;
            }
            
            $ret = addTimeline($param);
            
        }
        return $ret;
    }
    
    private function editFormGeral($reuniao,$dados, $readonly)
    {
        $ret = '';
        
        if($reuniao != 0){
            $dados['data'] = datas::dataS2D($dados['data']);
        }
        
        $form = new form01([]);
        $form->setBotaoCancela();
        
        $form->addCampo(array('id' => '', 'campo' => 'formReuniao[data]'      , 'etiqueta' => 'Data da Reunião'         , 'tipo' => 'D' 	, 'tamanho' => '15', 'linhas' => '', 'valor' => $dados['data'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true, 'readonly' => $readonly,));
        $form->addCampo(array('id' => '', 'campo' => 'formReuniao[titulo]'    , 'etiqueta' => 'Assunto'         , 'tipo' => 'T' 	, 'tamanho' => '125', 'linhas' => '', 'valor' => $dados['titulo'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => true, 'readonly' => $readonly,));
        $form->addCampo(array('id' => '', 'campo' => 'formReuniao[descricao]' , 'etiqueta' => 'Observação'         , 'tipo' => 'TA' 	, 'tamanho' => '45', 'linhas' => '', 'valor' => $dados['descricao'] 	, 'pasta'	=> 0, 'lista' => ''	, 'validacao' => '', 'largura' => 4, 'obrigatorio' => false, 'readonly' => $readonly,));
        
        $ret .= addCard(['conteudo' => $form,'titulo' => 'Informações Gerais']);
        
        return $ret;
    }
    
    
    private function editFormPautas($reuniao, $readonly = false)
    {
        $ret = '';
        
        $tab = new tabela01(['titulo'=>'Pautas', 'filtro'=>false]);
        $tab->addColuna(['campo' => 'titulo', 'etiqueta' => 'Pauta','tipo' => 'T','width' => '5','posicao' => 'C',]);
        
        //Botões
        if(!$readonly)
        {
            $param = [
                'texto' =>  'Editar',
                'link' 	=> getLink()."editarPauta&reuniao=$reuniao&pauta=",
                'coluna'=> 'id',
                'flag' 	=> '',
                'cor'   => 'success',
                'pos' => 'F',
            ];
            $tab->addAcao($param);
            $param = [
                'texto' =>  'Excluir',
                'link' 	=> getLink()."excluir&reuniao=$reuniao&pauta=",
                'coluna'=> 'id',
                'flag' 	=> '',
                'cor'   => 'danger',
                'pos' => 'F',
            ];
            $tab->addAcao($param);
            
            $botao = [
                'id' => 'incluir',
                'onclick' => "setLocation('".getLink()."editarPauta&reuniao=$reuniao&pauta=0')",
                'texto' => 'Nova Pauta',
                'cor' => 'success',
            ];
            $tab->addBotaoTitulo($botao);
        }
        
        $dados = $this->getPautasReuniao($reuniao);
        $tab->setDados($dados);
        
        $ret .= $tab;
        return $ret;
    }
    
    public function editar($id = 0)
    {
        $ret = '';

        $reuniao = $id != 0 ? $id : getParam($_GET, 'reuniao',0);
        
        $dados = $this->getInfoReuniao($reuniao);
        $readonly = false;
        
        $geral = $this->editFormGeral($reuniao,$dados,$readonly);
        
        if($reuniao == 0){
            $convidados = $this->montaFormUsuariosNivel();
            $convidados = addCard(['titulo' => 'Convidados', 'conteudo'=>$convidados]);
            
            $linha2 = $convidados;
        } else {
            $convidados = $this->montaFormConvidados($dados['participantes'], 'P', $readonly, $reuniao);
            $convidados = addCard(['titulo' => 'Participantes', 'conteudo'=>$convidados]);
            
            $anexo = $this->montaBlocoAnexo2($reuniao, $readonly);
            $anexo = addCard(['titulo'=>'Anexos','conteudo'=>$anexo]);
            
            
            $pautas = $this->editFormPautas($reuniao, $readonly);
            
            $convidados = $convidados . "<br>" . $anexo;
            $linha2 = addLinha(['tamanhos' => [7,5],'conteudos' => [$pautas,$convidados]]);
        }
        
        $ret .= $geral . '<br>' . $linha2;
        //footer de envio do form
        $param = [
            'URLcancelar' => getLink().'index',
            'IDform' => 'formReuniao',
        ];
        formbase01::formSendFooter($param);
        
        $param = [
            'id' => 'formReuniao',
            'acao' => getLink()."salvar&reuniao=$reuniao",
        ];
        $ret = formbase01::form($param, $ret);
        
        $param = [
            'conteudo' => $ret,
            'titulo' => 'Reunião',
        ];
        $ret = addCard($param);
       
        return $ret;
    }

    private function montaFormConvidados($lista_convidados, $tipo, $readonly = false, $id = 0)
    {
        $ret = '';
        
        $type = 'Todos';
        $descricao = 'Marcar Todos';
        $checkbox = [];
        $lista_usuarios = [];
        
        $convidados = [];
        $convidados = explode(';',$lista_convidados);
        
        $rows = $this->_rest->getFormConvidados($id);
        $rows = $rows['dados'];
        
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $temp = [];
                $temp['user'] = $row['user'];
                $temp['nome'] = $row['nome'];
                $temp['tipo'] = $type;
                //$lista_usuarios[$row['id']] = $temp;
                $lista_usuarios[] = $temp;
            }
        }
        
        foreach ($lista_usuarios as $user => $info){
            $temp = [];
            $temp["nome"] 		= $tipo === 'C' ? "formConvidados[{$info['user']}]" : "formParticipantes[{$info['user']}]";
            $temp["etiqueta"] 	= $info['nome'];
            $temp["modulo"] 	= $info['tipo'];
            $temp["classeadd"] 	= $info['tipo'];
            $temp["checked"]    = in_array($info['nome'], $convidados) ? true : false;
            $temp['ativo'] = !$readonly;
            $checkbox[$info['tipo']][] = $temp;
        }
        
        $impressao = $readonly ? 'disabled="disabled"' : '';
        $name = '';
        
        if(isset($checkbox[$type])){
            $param = [];
            $param['colunas'] 	= 3;
            $param['combos']	= $checkbox[$type];
            $formCombo = formbase01::formGrupoCheckBox($param);
            $param = [];
            $param['titulo'] = '<label><input type="checkbox"  onclick="marcarTodos(\''.$type.'\',this.checked);" '.$impressao.' name="'.$name.'['.$type.']" id="' . $descricao . '_id"  />&nbsp;&nbsp;'.$descricao.'</label>';
            $param['conteudo'] = $formCombo;
            
            $ret .= $param['titulo'] . $param['conteudo'];
            
            //$ret .= addCard($param).'<br><br>';
        }
        return $ret;
    }
    
    //USUÁRIOS COM SEPARAÇÃO POR NÍVEL
    
    private function montaFormUsuariosNivel(){
        $ret = '';
        
        $lista_tipos_usuarios = $this->getListaTiposUsuarios();
        $lista_usuarios = $this->getAllUsuariosCB();
        $CB = [];
        
        foreach ($lista_usuarios as $user => $info){
            $temp = [];
            $temp["nome"] 		= "formConvidados[$user]";
            $temp["etiqueta"] 	= $info['nome'];
            $temp["checked"] 	= $info['permissao'];
            $temp["modulo"] 	= $info['tipo'];
            $temp["classeadd"] 	= $info['tipo'];
            $CB[$info['tipo']][] = $temp;
        }
        
        foreach ($lista_tipos_usuarios as $tipo => $descricao){
            if(isset($CB[$tipo])){
                $param = [];
                $param['colunas'] 	= 3;
                $param['combos']	= $CB[$tipo];
                $formCombo = formbase01::formGrupoCheckBox($param);
                $param = [];
                $param['titulo'] = '<label><input type="checkbox"  onclick="marcarTodos(\''.$tipo.'\',this.checked);"  name="['.$tipo.']" id="' . $descricao . '_id" />&nbsp;&nbsp;'.$descricao.'</label>';
                $param['conteudo'] = $formCombo;
                $ret .= addCard($param);
            }
        }
        return $ret;
    }
    
    private function getListaTiposUsuarios(){
        $ret = [];
        $rows = $this->_rest->getTiposUsuarios();
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) > 0){
            foreach ($rows as $row){
                $ret[$row['chave']] = $row['descricao'];
            }
        }
        return $ret;
    }
    
    private function getAllUsuariosCB(){
        $ret = [];
        $rows = $this->_rest->getUsuariosCB();
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $temp = [];
                $temp['nome'] = $row['nome'];
                $temp['tipo'] = $row['tipo'];
                $temp['permissao'] = false;
                $ret[$row['user']] = $temp;
            }
        }
        return $ret;
    }
    
 
    private function getInfoReuniao($id)
    {
        //$ret = [];
        $ret = [
            'data' => '',
            'titulo' => '',
            'descricao' => '',
            'status' => '',
            'convidados' => '',
            'participantes' => '',
        ];
        
        $dados = $this->_rest->infoReuniao($id);
        //var_dump($dados);die();
        $dados = $dados['dados'];
        
        if(is_array($dados) && count($dados)==1)
        {
            $ret = $dados[0];
            $ret['convidados'] = $this->getParticipantesReuniao($id, 'C');
            $ret['participantes'] = $this->getParticipantesReuniao($id, 'P');
        }
        
        return $ret;
    }
    
    private function getInfoPauta($id)
    {
        $ret = [];
        
        $rows = $this->_rest->getInfoPauta($id);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows) == 1) {
            $ret = $rows[0];
        } else {
            $ret = [
                'titulo' => '',
                'descricao' => '',
            ];
        }
        return $ret;
    }
    
    public function excluir()
    {
        //$id = getParam($_GET, 'id', 0);
        $pauta = getParam($_GET, 'pauta', 0);
        $reuniao = getParam($_GET, 'reuniao', 0);
        $acao = getParam($_GET, 'acao', 0);
        
        if($acao != 0)
        {
            //exclui ação
            $this->_rest->deleteAcao($acao);
            redireciona(getLink()."editarPauta&reuniao=$reuniao&pauta=$pauta");
        } else if ($reuniao != 0 || $pauta != 0){
            if($pauta != 0)
            {
                //exclui pauta
                //Só se Não tiver ações!
                $minhas_acoes = $this->getAcoes($pauta, $reuniao);
                if(empty($minhas_acoes)){
                    $this->_rest->deletePauta($pauta);
                } else {
                    addPortalMensagem('A pauta possui ações pendentes e NÃO pode ser excluída','error');
                    return $this->editar($reuniao);
                }
                redireciona(getLink()."editar&reuniao=$reuniao");
            } else {
                //exclui reuniao
                //somente se não tem acoes!!
                $nroAcoes = $this->getNroAcoes($reuniao);
                if($nroAcoes['total'] != 0){
                    addPortalMensagem("A reunião possui ações pendentes e NÃO pode ser excluída",'error');
                    return $this->index();
                }
                //exclui pautas
                $minhas_pautas = $this->getPautasReuniao($reuniao);
                foreach ($minhas_pautas as $pauta){
                    $minhas_acoes = $this->getAcoes($pauta['id'], $reuniao);
                    foreach($minhas_acoes as $acao){
                        $this->_rest->deleteAcao($acao['id']);
                    }
                    $this->_rest->deletePauta($pauta['id']);
                }
                //exclui participantes
                //exclui reuniao
                $this->_rest->deleteReuniao($reuniao);
                redireciona(getLink().'index');
            }
        }

     
    }
 
    private function getNomeUsuario($user)
    {
        $ret = '';
        $rows = $this->_rest->getNomeUsuario($user);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows)==1){
            $ret = $rows[0]['nome'];
        }
        return $ret;
    }
    
  
    private function salvarParticipantes($id_reuniao,$nome_participantes, $tipo = 'C')
    {
        foreach($nome_participantes as $nome){
            //testa se existe
            $row=$this->_rest->getParticipantesNomeSalvar($id_reuniao, $nome);
            $row=$row['dados'];
            if(is_array($row) && count($row)==1){
                $dados = [
                    'tipo'=>$tipo,
                    'user'=>$nome,
                ];
                $dados = json_encode($dados);
                $this->_rest->salvarParticipante($id_reuniao,$dados);
            } else {
                $dados = [
                    'tipo'=>$tipo,
                    'user'=>$nome,
                    'nome'=>$this->getNomeUsuario($nome),
                ];
                $dados = json_encode($dados);
                $this->_rest->salvarNovoParticipante($id_reuniao,$dados);
            }
        }
        
        $rows=$this->_rest->getParticipantesIdSalvar($id_reuniao, $tipo);
        $rows=$row['dados'];
        if(is_array($rows) && count($rows)>1){
            $deletar = [];
            foreach($rows as $row){
                if(!in_array($row['user'], $nome_participantes)){
                    $deletar[] = $row['id'];
                }
            }
            if(!empty($deletar)){
                foreach($deletar as $del_id){
                    $this->_rest->deleteParticipante($del_id);
                }
            }
        }
        
    }

    private function salvarPauta($reuniao, $dados, $pauta = 0)
    {
        $ret = $pauta;
        //$virgula = false;
        
        if($pauta == 0)
        {
            //pauta nova
            $dados_json = [
                'reuniao'=>$reuniao,
                'titulo'=>$dados['titulo'],
                'descricao'=>$dados['descricao'],
                'usuario'=>getUsuario(),
            ];
            $dados_json=json_encode($dados_json);
            $ret = $this->_rest->salvarPauta($pauta, $dados_json);
            $ret =  $ret['id']["LAST_INSERT_ID()"];
        } else {
            //atualiza pauta
            $dados_json = [
                'titulo'=>$dados['titulo'],
                'descricao'=>$dados['descricao']
            ];
            $dados_json = json_encode($dados_json);
            $this->_rest->salvarPauta($pauta, $dados_json);
        }
        return $ret;
    }
    
    private function salvarReuniao($dados_reuniao,$dados_convidados, $reuniao = 0)
    {
        $ret = $reuniao;
        
        //Reunião
        $dados_reuniao['data'] = datas::dataD2S($dados_reuniao['data']);
        $dados = [
            'data' => $dados_reuniao['data'],
            'titulo' => $dados_reuniao['titulo'],
            'descricao' => $dados_reuniao['descricao'],
        ];
        $dados = json_encode($dados);
        
        if($reuniao == '0'){
            
            
            $ret = $this->_rest->salvarReuniao($dados);
            //var_dump($ret);die();
            $ret = $ret['id']["LAST_INSERT_ID()"];
            if(!empty($dados_convidados)){
                $convidados = [];
                foreach($dados_convidados as $nome=>$useless){
                    $convidados[] = $nome;
                }
                $this->salvarParticipantes($ret, $convidados);
            }
        } else {
            $this->_rest->salvarReuniao($dados, $reuniao);
            
            if(!empty($dados_convidados) && $dados_reuniao['data'] >= date('Ymd')){
                $convidados = [];
                foreach($dados_convidados as $nome=>$useless){
                    $convidados[] = $nome;
                }
                $this->salvarParticipantes($reuniao, $convidados, 'C');
            } else if(!empty($dados_convidados)){
                $convidados = [];
                foreach($dados_convidados as $nome=>$useless){
                    $convidados[] = $nome;
                }
                $this->salvarParticipantes($reuniao, $convidados, 'P');
            }
        }
        
        return $ret;
    }
    
    private function salvarAcao($id,$dados,$reuniao,$pauta)
    {
        $campos = [
            "formAcaoacao$id",
            "formAcaodepartamento$id",
            "formAcaoresponsavel$id",
            "formAcaostatus$id",
            "formAcaoprazo$id",
            "formAcaofinalizado$id",
            "formAcaocliente$id",
            "formAcaoprivado$id",
            "formAcaoobs$id",
        ];
        $dados_acao = [];
        foreach ($campos as $campo){
            $nomeCampo = str_replace($id, '', $campo);
            $nomeCampo = str_replace('formAcao', '', $nomeCampo);
            $dados_acao[$nomeCampo] = $dados[$campo];
        }
        
        $dados_acao['prazo'] = datas::dataD2S($dados_acao['prazo']);
        //log::gravaLog('salvarAcao', json_encode($dados_acao) . "\n\n");
        if(isset($dados_acao['finalizado'])){
            $dados_acao['finalizado'] = datas::dataD2S($dados_acao['finalizado']);
        }
        $dados_acao['data'] = date('Ymd');
        
        if($id == 0)
        {
            //incluir
            $dados_acao['id_pauta'] = $pauta;
            $dados_acao['id_reuniao'] = $reuniao;
            
            $dados = json_encode($dados_acao);
            
            $id_acao = $this->_rest->salvarAcao($id, $dados);
            
            log::gravaLog('salvarAcao', json_encode($id_acao) . "\n\n");
            $id_acao = $id_acao['id']["LAST_INSERT_ID()"];
        } else {
            //editar
            $dados = json_encode($dados_acao);
            $this->_rest->salvarAcao($id, $dados);
            $id_acao = $id;
        }
        
        $usuarios = $this->getParticipantesPauta($reuniao);
        $usuarios[] = $dados_acao['responsavel'];
       // log::gravaLog('envolvidosReuniao', json_encode($usuarios));
        $this->salvarEnvolvidos($usuarios, $id_acao);
    }
    
    //Envolvidos na Ação (não necessariamente participam de reunião)
    private function salvarEnvolvidos($usuarios, $id_acao)
    {
        //Nomes que estavam na última atualização
        $rows = $this->_rest->getRespAcao($id_acao);
        $rows = $rows['dados'];
        if(is_array($rows) && count($rows)>0){
            $antigos = [];
            foreach($rows as $row){
                $antigos[] = $row['resp'];
            }
            //Usuários que estão no antigo mas não nos novos serão apagados
            $apagados = array_diff($antigos, $usuarios);
            
            foreach($apagados as $apaga){
                $this->_rest->deleteRespAcao($id_acao, $apaga);
            }
            
            //Usuários que estão nos novos mas não nos antigos serão inseridos
            $usuarios = array_diff($usuarios, $antigos);
        }
        
        foreach($usuarios as $insere){
            $this->_rest->salvarRespAcao($id_acao, $insere);
        }
        
    }
    
    
    public function salvar()
    {
        var_dump($_POST);var_dump($_GET); 
        
        $dados_reuniao = getParam($_POST, 'formReuniao',[]);
        $dados_convidados = getParam($_POST, 'formConvidados', []);
        if(empty($dados_convidados)){
            $dados_convidados = getParam($_POST, 'formParticipantes', []);
        }
        $reuniao = getParam($_GET, 'reuniao',0);
        
        $dados_pauta = getParam($_POST, 'formPauta',[]);
        $pauta = getParam($_GET, 'pauta',0);
        
        if (!empty($dados_pauta) && $reuniao != 0){
            //PAUTA
            echo '                                            pauta';
            $id = $this->salvarPauta($reuniao, $dados_pauta, $pauta);
            redireciona(getLink()."editarPauta&reuniao=$reuniao&pauta=$id");
        } else if (!empty($dados_reuniao) ) { //&& !empty($dados_convidados) ) {
            //REUNIÃO
            echo '                                            reuniao';
            $id = $this->salvarReuniao($dados_reuniao, $dados_convidados, $reuniao);
            redireciona(getLink()."editar&reuniao=$id");
        } else {
            echo '                                            else';
            redireciona(getLink().'index');
        }
  
    }
    
}