<?php

/*
 * Data Criacao: 24/05/2023
 * 
 * Autora:  Alessandra Nunes 
 *
 * Descrição: importar planilha do excel para dentro do banco.
 *
 * Alterações:
 *      21/06/2023 devido a mudanças no kanban_lite - Alex Cesar
 *      09/08/2023 ordenação por tags - Alex Cesar
 */


error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_erros',1);

include 'PHPExcel/IOFactory.php';
use PhpOffice\PhpSpreadsheet\Calculation\Information\Value;


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

include_once ($config['include'].'phpExcel/PHPExcel.php');
class mrp_excel {
    var $funcoes_publicas = array(
		'importar' 		=> true,
		'index' => true,
		'ajax'      => true,
        'upload'=>true,
        'visualizar' => true,

	);

	//NOSSA BOARD DO KANBAN-LITE
	private $_board = 2;

	//NOSSA TABELA DO EXCEL 'kb_excel_mrp';


    public function index(){
		unsetAppVar('cliente_teste');
		
		$ret = '';
		$ret .= formbase01::formBotao(array(
		'texto' => 'Incluir chamada',
		'url' => 'https://forms.office.com/r/qK35D51RSY',
		'cor' => '',
		'tipo' => 'link',
		));
		
		$usuario = getUsuario();
		//echo $usuario; die();
		if($usuario == 'alvaro.lamb@verticais.com.br'){
	//	if($usuario == 'emanuel.thiel@verticais.com.br'){
		    
		    $param = [];
		    $param['nome'] 	= 'excelUpload';
		    $form = formbase01::formFile($param);
		    $param = formbase01::formSendParametros();
		    $param['texto'] = 'Enviar Excel';
		    $form .= formbase01::formBotao($param);
		    
		    $param = array();
		    $param['acao'] = getLink()."upload";
		    $param['nome'] = 'formExcel';
		    $param['id']   = 'formExcel';
		    $param['enctype'] = true;
		    $form = formbase01::form($param, $form);
		    
		    $ret = addCard(array('titulo' => $this->_titulo, 'conteudo' => $form,));
		    
		    
    		if($this->jaImportado()){
    		    $this->setaNomeSetorDaTabela();
    		    $this->setaCardDaTabela();
    		    $ret.= $this->visualizar();
    		}
		}
		else {
		    $ret.= $this->visualizar();
		}
       

		/*
		

		$kan = new kanban_lite(2, array('editar' => false, 'add' => false));
		putAppVar('bt_etapa',false);
		$ret .= $kan;
		*/
		return addCard(['conteudo' =>$ret, 'titulo' => 'Kanban Flow' ]);
	}
	
	private function jaImportado()
	{
	    $ret = false;
	    $rows = query("Select * FROM kb_excel_mrp");
	    if(is_array($rows) && count($rows)>0){
	        $ret = true;
	    }
	    return $ret;
	}
	
	
	
	public function visualizar()
	{
	    $ret = '';
	    
	    unsetAppVar('cliente_teste');
	    $kan = new kanban_lite(2, array('editar' => false, 'add' => false, 'configurar' => false));
	    putAppVar('bt_etapa',false);
	    
	    $ret .= $kan;
	    return($ret);
	}

	public function ajax(){
        $ret = kanban_lite::ajax();
        return $ret;
    }
    
    
    public function upload()
    {
        //$produto = getAppVar('excelUpload');
        
        if(count($_FILES) > 0){
            echo "TEM FILES - ";
            if (is_uploaded_file($_FILES['excelUpload']['tmp_name'])) 
            {
                echo"tem arquivo - ";
            //    $nome = $_FILES['excelUpload']['name'];
                $this->importar($_FILES['excelUpload']['tmp_name']);
            }else{
                addPortalMensagem('Ocorreu um erro, favor tentar novamente!','error');
            }
        } else {
            echo "Nao fez upload de arquivo nenhum";
        }
        redireciona(getLink().'index');
    }
    
    private function importar($inputFileName)
    {
        echo 'e1';
        try{
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        } catch (Exception $e){
            var_dump($e); echo($e);
        }
        echo'e2';
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        echo'e3';
        $objPHPExcel = $objReader->load($inputFileName);
        echo'e4';
        
     /* die('p1');  
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            //die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
            echo "DEU ERRO"; die();
            
        }*/
        
        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        $sql = "TRUNCATE TABLE kb_excel_mrp";
        query($sql);
        $rows = query("SELECT id FROM kl_etapas WHERE board = 2");
        if(is_array($rows) && count($rows) > 0){
            foreach($rows as $row){
                $sql = "DELETE FROM kl_cards WHERE id = " . $row["id"];
            }
            
        }
        $sql = "DELETE FROM kl_etapas WHERE board = 2";
        query($sql);
        
        //  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);
            //  Insert row data array into your database of choice here
            if($row > 1){
                //var_dump($rowData); die();
                $campos = array();
                $campos['id_tarefa'] = $rowData[0][0];
                $campos['nome_tarefa'] = str_replace("'", "`",  $rowData[0][1]);
                $campos['nome_bucket'] =  str_replace("'", "`", $rowData[0][2]);
                $campos['progresso'] = $rowData[0][3];
                $campos['prioridade'] = $rowData[0][4];
                $campos['atribuido_a'] = $rowData[0][5];
                $campos['criado_por'] = $rowData[0][6];
                $campos['data_criacao'] = datas::dataD2S($rowData[0][7]);
                $campos['data_inicio'] = datas::dataD2S($rowData[0][8]);
                $campos['data_fim'] = datas::dataD2S($rowData[0][9]);
                $campos['data_conclui']= datas::dataD2S($rowData[0][12]);
                
              //  $campos['tag'] = $rowData[0][16]; //informação de rótulo 
                //Rótulos: observar se há campo numérico para colocar prioridade
                $tags = $rowData[0][16];
                $tags = explode(';', $tags);
                if($tags[0] == ''){
                    $campos['tag'] = '';
                } else {
                    $campos_tag = '';
                    foreach($tags as $tag){
                        //if(preg_replace('/[0-9]/i', '', $tag)!=''){
                        if(intval($tag) != 0){
                            $campos['tag_priori'] = $tag;
                            ($campos_tag == '') ? ($campos_tag = $tag) : ($campos_tag = $tag.";$campos_tag");
                        } else{
                            ($campos_tag == '') ? ($campos_tag .= $tag) : ($campos_tag .= ';'.$tag);
                        }
                    }
                    $campos['tag'] = $campos_tag;
                }
                //log::gravaLog('tags_kl', json_encode($tags));
                
                $campos['descricao'] =  str_replace("'", "`",$rowData[0][17]);
                //print_r($campos);
                $sql = montaSQL($campos, 'kb_excel_mrp');
                query($sql);
                
            //    echo $sql." -tarefa $row incluida <br>\n";
                
            }
        }
    }

/*

	public function importar(){
	    include $config;
		//var_dump($_POST); die();
		
	    
		

			//  Include PHPExcel_IOFactory
		//include 'PHPExcel/IOFactory.php';
		
	    
		$caminho = $_FILES ["files"] ["tmp_name"][0]; //ARRUMAR UPLOAD DEPOIS
		//$caminho = "C:\Users\Alessandra\Downloads\KANBANMRP.xlsx";
		$inputFileName = $caminho;
	
		var_dump($caminho); die();
		
		//  Read your Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) {
			//die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			echo "DEU ERRO"; die();

		}

		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
		$highestColumn = $sheet->getHighestColumn();

		//  Loop through each row of the worksheet in turn
		$sql = "TRUNCATE TABLE kb_excel_mrp";
		query($sql);
		$rows = query("SELECT id FROM kl_etapas WHERE board = 2");
		if(is_array($rows) && count($rows) > 0){
			foreach($rows as $row){
				$sql = "DELETE FROM kl_cards WHERE id = " . $row["id"];
			}
			
		}

		
		$sql = "DELETE FROM kl_etapas WHERE board = 2";
		query($sql);
		
			for ($row = 1; $row <= $highestRow; $row++){ 
				$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
											NULL,
											TRUE,
											FALSE);
			//  Insert row data array into your database of choice here
				
				if($row > 1){
					//var_dump($rowData); die();
					$campos = array();
					$campos['id_tarefa'] = $rowData[0][0];
					$campos['nome_tarefa'] = str_replace("'", "`",  $rowData[0][1]);
					$campos['nome_bucket'] =  str_replace("'", "`", $rowData[0][2]);
					$campos['progresso'] = $rowData[0][3];
					$campos['prioridade'] = $rowData[0][4];
					$campos['atribuido_a'] = $rowData[0][5];
					$campos['criado_por'] = $rowData[0][6];
					$campos['data_criacao'] = datas::dataD2S($rowData[0][7]);
					$campos['data_inicio'] = datas::dataD2S($rowData[0][8]);
					$campos['data_fim'] = datas::dataD2S($rowData[0][9]);
					$campos['data_conclui']= datas::dataD2S($rowData[0][12]);
					$campos['empresa'] = 'MRP';
					$campos['descricao'] =  str_replace("'", "`",$rowData[0][17]);
					//print_r($campos);
					$sql = montaSQL($campos, 'kb_excel_mrp');
					query($sql);

					echo "tarefa $row incluida <br>\n";
					
				}
			}

		return $this->index();			
	}
*/
	private function getIdEtapa($etiqueta)
	{
			$id = 0;
			$sql = "select id from  kl_etapas where etiqueta = '$etiqueta'	and board = '2'";
			$lista = query($sql);
			if(is_array($lista) && count($lista) > 0){
				$id = $lista[0]['id'];
            }
			return $id;
	}

	private function setNomeSetor($nome_bucket)
	{
		//Pegar o nome bucket e transformar numa linha em kl_etapas SE não existe
		//SE não existe
		$rows = query("SELECT * FROM kl_etapas WHERE etiqueta = '$nome_bucket' AND board = '2'");

		if(!(is_array($rows) && count($rows) > 0))
		{
			//INSERIR EM kl_etapas NOVA ETAPA com board = 2 etiqueta $nome_bucket, ordem 0
			$sql = "INSERT INTO kl_etapas (board, etiqueta, ordem, cor) VALUES ('2', '$nome_bucket', '0','primary')";
			//echo $sql; die();
			query($sql);
		}		

	}

	private function setaNomeSetorDaTabela()
	{
		$sql = "SELECT DISTINCT nome_bucket FROM kb_excel_mrp";
		$lista = query($sql);
		//var_dump($lista);die();
		if(is_array($lista) && count($lista) > 0)
		{
			foreach($lista as $elemento)
			{
				$this->setNomeSetor($elemento['nome_bucket']);
			}
		}
	}


	private function setCard($etiqueta,$etapa,$resumo,$id_tarefa, $tag)
	{
		//Pega os dados da tabela excel e transforma em dados para a tabela do kanban lite (kl_cards)
		//etiqueta = nome tarefa
		//etapa = id da etapa ->buscar
		//resumo = descricao
		//tag = valor da coluna tag
		//SE não existe
		$rows = query("SELECT * FROM kl_cards WHERE etiqueta = '$etiqueta' AND etapa = '$etapa'");

		if(!(is_array($rows) && count($rows) > 0))
		{
			//insere em cards
			$sql = "INSERT INTO kl_cards (etiqueta,etapa,resumo,cor,tipo,tags) VALUES ('$etiqueta',$etapa,'$resumo','primary','EXCEL_MRP','$tag')";
			$inserido = query($sql);
			//atualiza no excel
			if($inserido !== false)
			{
				//pega id do card
				$rows = query("SELECT id FROM kl_cards WHERE etiqueta = '$etiqueta' AND etapa = '$etapa'");
				//passa id para kb_excel_mrp
				$sql = "update kb_excel_mrp set id_card = ".$rows[0]['id']." where id_tarefa = '$id_tarefa'";
				query($sql);
			}

		}
	}
/*
 * no momento, pega de kb_excel_mrp ordenando pela data, para inserir ordenado
 * agora, a tag é a prioridade de ordenação, então os que tem número vêm necessariamente na frente
 * talvez o melhor jeito seja pegar todos os valores, colocar num array, fazer o tratamento e daí inserir ordenado no kanban
 * então precisamos de uma variável global que recebe os valores de kb_excel_mrp
 * e uma função para fazer o tratamento e depois setar o card na tabela
 * */
	private function setaCardDaTabela()
	{
		$sql = "SELECT * FROM kb_excel_mrp ORDER BY tag_priori, data_inicio desc, data_conclui";
		$lista = query($sql);
		//var_dump($lista);die();
		if(is_array($lista) && count($lista) > 0)
		{
			foreach($lista as $elemento)
			{
			    $etiqueta = $elemento['nome_tarefa'];
			    $resumo = $elemento['descricao'];
			    $id_tarefa = $elemento['id_tarefa'];
			    $tags = $elemento['tag'];
			    $etapa = $this->getIdEtapa($elemento['nome_bucket']);
			    $this->setCard($etiqueta, $etapa,$resumo,$id_tarefa,$tags);
			}
		}
	}

	

}



			
			
