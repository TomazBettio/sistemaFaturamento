<?php
/*
 * Data Criacao 17/07/2023
 * Autor: Alex Cesar
 *
 * Descrição: Formulário para setar os parâmetros de cada transportadora
 *
 */

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class parametro_transportadora
{
    //Tabela
    private $_tabela = 'gf_param_transportadora';
    private $_meses = [
        '01' => 'janeiro',
        '02' => 'fevereiro',
        '03' => 'março',
        '04' => 'abril',
        '05' => 'maio',
        '06' => 'junho',
        '07' => 'julho',
        '08' => 'agosto',
        '09' => 'setembro',
        '10' => 'outubro',
        '11' => 'novembro',
        '12' => 'dezembro',
    ];
    
    var $funcoes_publicas = array(
        'index' 		=> true,
        'editar'        => true,
        'salvar'        => true,
        'excluir'       => true,
        'incluir'       => true,
        'historico'     => true,
    );
    
    public function __construct()
    {
        
    }
    
    public function index()
    {
        $ret = '';
        
        $tab = new tabela01(['titulo' => 'Transportadoras']);
        $tab->addColuna(array('campo' => 'transportadora' , 'etiqueta' => 'Transportadora'         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'atual'          , 'etiqueta' => 'Atualizado esse mês'     , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        
        $tab->addAcao([
            'texto' =>  'Atualizar',
            'link' 	=> getLink()."editar&id=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'success',
        ]);
        
        $tab->addAcao([
            'texto' =>  'Histórico',
            'link' 	=> getLink()."historico&id=",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'primary',
        ]);
        
        $this->jsConfirmaExclusao('"AVISO: a exclusão apagará TODOS os dados dessa transportadora. Deseja continuar?"');
        $tab->addAcao([
            'texto' =>  'Excluir',
            'link' 	=> "javascript:confirmaExclusao('" . getLink()."excluir&id=','{ID}')",
            'coluna'=> 'id',
            'flag' 	=> '',
            'cor'   => 'danger',
        ]);
        
        $tab->addBotaoTitulo([
            'id' => 'incluir',
            'onclick' => "setLocation('".getlink()."incluir')",
            'texto' => 'Incluir Transportadora'
        ]);
        
        $dados = $this->getTransportadorasComStatus();
        $tab->setDados($dados);
        
        $ret.=$tab;
        return $ret;
    }
    
    private function getTransportadorasComStatus()
    {
        $ret = [];
        $sql = "SELECT id, transportadora FROM $this->_tabela WHERE excluido = 'N'  GROUP BY transportadora";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0)
        {
            $temp = [];
            foreach($rows as $row)
            {
                $temp['id'] = $row['id'];
                $temp['transportadora'] = $row['transportadora'];
                $hoje = date('Ymd');
                $ultimo = $this->getUltimaAtualizacao($row['transportadora']).'01';
                if(datas::calculaDifMesesS($hoje, $ultimo)==0){
                    $temp['atual'] = 'Sim';
                } else {
                    $temp['atual'] = 'Não';
                }
                $ret[]=$temp;
            }
        }
        return $ret;
    }
    
    private function getUltimaAtualizacao($trans)
    {
        $ret = '';
        $sql = "SELECT data FROM $this->_tabela WHERE transportadora = '$trans'  AND excluido = 'N' ORDER BY data DESC";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0]['data'];
        }
        return $ret;
    }
    
    public function historico()
    {
        $ret = '';
        
        $id = getParam($_GET, 'id',0);
        
        $tab = new tabela01(['titulo' => 'Transportadoras', 'ordenacao'=>false]);
        $tab->addColuna(array('campo' => 'data'             , 'etiqueta' => 'Mês/Ano'                         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'transportadora'   , 'etiqueta' => 'Transportadora'                  , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'codigos'          , 'etiqueta' => 'Códigos'                         , 'tipo' => 'T', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'percentual'       , 'etiqueta' => 'Percentual'                      , 'tipo' => 'P', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'frete_min'        , 'etiqueta' => 'Frete Mínimo'                    , 'tipo' => 'V', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'frete_martins'    , 'etiqueta' => 'Frete Mínimo - Martins'          , 'tipo' => 'V', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'notas_5'          , 'etiqueta' => 'Notas Acima 5mil'                , 'tipo' => 'P', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'notas_10'         , 'etiqueta' => 'Notas Acima 10mil'               , 'tipo' => 'P', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'notas_15'         , 'etiqueta' => 'Notas Acima 15mil'               , 'tipo' => 'P', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'notas_50'         , 'etiqueta' => 'Notas Acima 50mil'               , 'tipo' => 'P', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'devolucoes'       , 'etiqueta' => 'Devoluções'                      , 'tipo' => 'V', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'clamed'           , 'etiqueta' => 'Rede Clamed'                     , 'tipo' => 'P', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'frete_peso'       , 'etiqueta' => 'Frete Peso (exced. 50kg) por kg' , 'tipo' => 'V', 'width' =>  160, 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'icms'             , 'etiqueta' => 'ICMS'                            , 'tipo' => 'P', 'width' =>  160, 'posicao' => 'E'));
        
        $tab->addBotaoTitulo([
            'id' => 'id',
            'onclick' => "setLocation('".getlink()."index')",
            'texto' => 'Voltar',
            'cor' => 'warning'
        ]);
        
        $tab->addBotaoTitulo([
            'id' => 'id',
            'onclick' => "setLocation('".getlink()."editar&id=$id')",
            'texto' => 'Atualizar',
            'cor' => 'success'
        ]);
        
        $dados = $this->getHistoricoTrans($id);
        $tab->setDados($dados);
        
        $ret.=$tab;
        return $ret;
    }
    
    private function getHistoricoTrans($id)
    {
        $ret = [];
        $trans = $this->getNomeTransportadora($id);
        $sql = "SELECT * FROM $this->_tabela WHERE transportadora = '$trans' AND excluido = 'N' ORDER BY data ASC";
        //echo $sql;
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $temp = [];
            foreach($rows as $row)
            {
                $temp = $row;
                $mes = substr($temp['data'],4);
                $ano = substr($temp['data'],0,4);
                $temp['data']= $this->_meses[$mes]."/$ano";
                $ret[] = $temp;
            }
        }
        return $ret;
    }
    
    private function getNomeTransportadora($id)
    {
        $ret = '';
        $sql = "SELECT transportadora FROM $this->_tabela WHERE id = $id group by transportadora";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0]['transportadora'];
        }
        return $ret;
    }
    
    public function incluir()
    {
        $ret = '';
        $form = $this->formEdit();
        $ret .= addCard(['titulo' => 'Inclusão de Nova Transportadora - '.datas::dataS2D(date('Ymd')), 'conteudo' => $form]);
        return $ret;
    }
    
    private function formEdit($id=0,$dados=[])
    {
        $form = new form01([]);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[transportadora]',
            'etiqueta' => 'Transportadora',
            'tipo' => 'T',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['transportadora'])? $dados['transportadora'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => true,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[codigos]',
            'etiqueta' => "Códigos (separe por ';')",
            'tipo' => 'TA',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['codigos'])? $dados['codigos'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[percentual]',
            'etiqueta' => 'Percentual',
            'tipo' => 'P',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['percentual'])? $dados['percentual'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[frete_min]',
            'etiqueta' => 'Frete Mínimo',
            'tipo' => 'V',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['frete_min'])? $dados['frete_min'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[frete_martins]',
            'etiqueta' => 'Frete Mínimo - Martins (R$)',
            'tipo' => 'V',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['frete_martins'])? $dados['frete_martins'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[mercados]',
            'etiqueta' => 'Mercados (R$)',
            'tipo' => 'V',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['mercados'])? $dados['mercados'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[notas_5]',
            'etiqueta' => 'Notas Acima 5mil (%)',
            'tipo' => 'P',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['notas_5'])? $dados['notas_5'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[notas_10]',
            'etiqueta' => 'Notas Acima 10mil (%)',
            'tipo' => 'P',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['notas_10'])? $dados['notas_10'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[notas_15]',
            'etiqueta' => 'Notas Acima 15mil (%)',
            'tipo' => 'P',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['notas_15'])? $dados['notas_15'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[notas_50]',
            'etiqueta' => 'Notas Acima 50mil (%)',
            'tipo' => 'P',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['notas_50'])? $dados['notas_50'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[devolucoes]',
            'etiqueta' => 'Devoluções (R$)',
            'tipo' => 'V',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['devolucoes'])? $dados['devolucoes'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[clamed]',
            'etiqueta' => 'Rede Clamed (%)',
            'tipo' => 'P',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['clamed'])? $dados['clamed'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = (array(
            'id' => '',
            'campo' => 'formPrograma[frete_peso]',
            'etiqueta' => 'Frete de Peso (exced. 50kg) por kg (R$)',
            'tipo' => 'V',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['frete_peso'])? $dados['frete_peso'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,));
        $form->addCampo($param);
        $param = array(
            'id' => '',
            'campo' => 'formPrograma[icms]',
            'etiqueta' => 'ICMS (%)',
            'tipo' => 'P',
            'tamanho' => '15',
            'linhas' => '',
            'valor' => isset($dados['icms']) ? $dados['icms'] : '',
            'pasta'	=> 0,
            'lista' => '',
            'validacao' => '',
            'largura' => 4,
            'obrigatorio' => false,);
        $form->addCampo($param);
        
        $envio = isset($dados['data']) ? "salvar&id=$id&ultimo=".$dados['data'] : "salvar&id=0";
        $form->setEnvio(getLink() . $envio, 'formPrograma', 'formPrograma');
        
        return $form;
    }
    
    public function editar()
    {
        $ret = '';
        $id = getParam($_GET, 'id',0);
        if($id!=0)
        {
            $dados = $this->getDadosTransportadora($id);
            $form = $this->formEdit($id,$dados);
            $ret .= addCard(['titulo' => 'Atualização '.datas::dataS2D(date('Ymd')).' - Transportadora '.$dados['transportadora'], 'conteudo' => $form]);
            
        }
        return $ret;
    }
    
    
    private function getDadosTransportadora($id)
    {
        $ret = [];
        $sql = "SELECT * FROM $this->_tabela WHERE id = $id";
        $rows = query($sql);
        if(is_array($rows) && count($rows)>0){
            $ret = $rows[0];
        }
        return $ret;
    }
    
    public function salvar()
    {
        $id = getParam($_GET, 'id');
        $dados = getParam($_POST, 'formPrograma');
        $ultimo = getParam($_GET,'ultimo',false).'01';
        $hoje = date('Yms');
        
        $sql = '';
        if($id == 0)
        {
            $dados['data']=substr($hoje,0,6);
            $sql .= montaSQL($dados, $this->_tabela);
        } else {
            //atualizar ou incluir
            if(datas::calculaDifMesesS($hoje, $ultimo)==0){
                $sql .= montaSQL($dados, $this->_tabela,'UPDATE',"id=$id");
            } else {
                $dados['data']=substr($hoje,0,6);
                $sql .= montaSQL($dados, $this->_tabela);
            }
        }
        query($sql);
        redireciona(getLink().'index');
    }
    
    public function excluir()
    {
        $id = getParam($_GET, 'id',0);
        if($id != 0)
        {
            $trans = $this->getNomeTransportadora($id);
            $sql = "UPDATE $this->_tabela SET excluido='S' WHERE transportadora = '$trans'";
            query($sql);
        }
        redireciona(getLink().'index');
    }
    
    
    
    private function jsConfirmaExclusao($titulo){
        $ret = "
                function confirmaExclusao(link,id){
                    if (confirm($titulo)){
                        setLocation(link+id);
                    }
                }";
        addPortaljavaScript($ret);
    }
}
