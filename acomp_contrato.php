<?php
include"contratos_funcoes.php";
$id_contrato = $_GET['idc'];
?>
<SCRIPT TYPE="text/javascript">
function apaga_medicao(id_medicao) {
    document.getElementById("medicao_id_apaga").value = id_medicao;
    document.getElementById("apaga_medicao_botao").click();
}
</SCRIPT>
<?php
//LAN�ANDO UMA NOVA MEDI��O
if(isset($_POST['cria_nova_medicao']) && $_POST['cria_nova_medicao'] == 'Salvar'){
	$mes = $_POST['periodo_ref'];
	$contratoID = $id_contrato;
	$descricao = $_POST['MEDI_descricao'];
	$data_pagto = $_POST['MEDI_data'];
	if($data_pagto <> ''){
		$data_pagto = trata_data($data_pagto);
	}
	$valor = $_POST['MEDI_valor'];
	$valor = str_replace('.','',$valor);
	$valor = str_replace(',','.',$valor);
	$dia = lediamedi($contratoID);
	$diafim = $dia;
	$dia++;
	$mesFINAL = $mes;
	$sql = 'INSERT INTO tab_cf_medicoes (id_contrato, mes_ref, valor, descricao, data_pagto) VALUES (:id_contrato, :mes_ref, :valor, :descricao, :data_pagto)';
	try{
        $query_select = $conecta->prepare($sql);
    		$query_select->bindValue(':id_contrato',$contratoID,PDO::PARAM_STR);
    		$query_select->bindValue(':mes_ref',$mesFINAL,PDO::PARAM_STR);
    		$query_select->bindValue(':valor',$valor,PDO::PARAM_STR);
    		$query_select->bindValue(':descricao',$descricao,PDO::PARAM_STR);
    		$query_select->bindValue(':data_pagto',$data_pagto,PDO::PARAM_STR);
        $query_select->execute();
	}catch(PDOexception $error_sqlselect){
        echo 'Erro ao lan�ar medi��o - '.$error_sqlselect->getMessage();
    }
}
if(isset($_POST['cria_nova_medicao']) && $_POST['cria_nova_medicao'] == 'Atualizar'){
	$contratoID = $id_contrato;
	$mes = $_POST['periodo_ref'];
	$medicao_edita_id = $_POST['medicao_edita_id'];
	$descricao = $_POST['MEDI_descricao'];
	$data_pagto = $_POST['MEDI_data'];
	if($data_pagto <> ''){
		$data_pagto = trata_data($data_pagto);
	}
	$valor = $_POST['MEDI_valor'];
	$valor = str_replace('.','',$valor);
	$valor = str_replace(',','.',$valor);
	$dia = lediamedi($contratoID);
	$diafim = $dia;
	$dia++;
	$mesFINAL = $mes;
	$sql = 'UPDATE tab_cf_medicoes SET mes_ref = :mes_ref, valor = :valor, descricao = :descricao, data_pagto = :data_pagto WHERE id_medicao = :id_medicao';
	try{
        $query_select = $conecta->prepare($sql);
    		$query_select->bindValue(':mes_ref',$mesFINAL,PDO::PARAM_STR);
    		$query_select->bindValue(':valor',$valor,PDO::PARAM_STR);
    		$query_select->bindValue(':descricao',$descricao,PDO::PARAM_STR);
    		$query_select->bindValue(':data_pagto',$data_pagto,PDO::PARAM_STR);
    		$query_select->bindValue(':id_medicao',$medicao_edita_id,PDO::PARAM_STR);
    		$query_select->execute();
	}catch(PDOexception $error_sqlselect){
        echo 'Erro ao lan�ar medi��o - '.$error_sqlselect->getMessage();
    }
}
if(isset($_POST['apaga_medicao_botao']) && $_POST['apaga_medicao_botao'] == 'Apaga'){
    $id_medicao_apaga = $_POST['medicao_id_apaga'];
    $sql = 'DELETE FROM tab_cf_medicoes WHERE id_medicao = :id_medicao';
    try{
    	$query_select = $conecta->prepare($sql);
    	$query_select->bindValue(':id_medicao',$id_medicao_apaga,PDO::PARAM_STR);
    	$query_select->execute();
    }catch(PDOexception $error_sqlselect){
    	echo 'Erro ao apagar medi��o - '.$error_sqlselect->getMessage();
    }
}
//SELECIONA OS DADOS DO CONTRATO
$sql_dados = "SELECT c.*,cl.nome as cliente FROM tab_contratos as c INNER JOIN tab_empresas as cl ON cl.id_empresa = c.id_cliente WHERE c.id_contrato = :id_contrato";
$i=0;
try{
    $query_select = $conecta->prepare($sql_dados);
    $query_select->bindValue(':id_contrato',$id_contrato,PDO::PARAM_STR);
    $query_select->execute();
    $resultado_select = $query_select->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOexception $error_sqlselect){
    echo 'Erro ao selecionar os dados do contrato - '.$error_sqlselect->getMessage();
}
foreach($resultado_select as $res_dados){
    $contrato_nome = $res_dados['nome'];
    $contrato_obra = $res_dados['descricao'];
    $contrato_cliente = $res_dados['cliente'];
    $contrato_pai = $res_dados['id_contratopai'];
    $contrato_mes_inicio = $res_dados['mes_inicio'];
    $contrato_dia_medi = $res_dados['dia_medi'];
    $contrato_prev_dura = $res_dados['prev_dura'];
    //TRATA NOME DO CONTRATO PAI
    if($contrato_pai == 0){
	      $contrato_pai = '-';
    }else{
      	//BUSCA O NOME DO CONTRATO PAI
      	$contrato_pai_nome = nome_contrato($contrato_pai);
    }
}
?>
<div class="row">
    
    
    <h3 style="color: #56888B"><?php echo $contrato_nome;?></h3>
    <ol class="breadcrumb">
	<!-- BREADCRUMB -->
	<li>
	    <a href="index.php">Inicio</a>
	</li>
	<li>
	    <a href="index.php?exe=comercial/contratos">Contratos</a>
	</li>
	<li class="active"><?php echo $contrato_nome;?></li>
    </ol>
    
    <div class="row">
    	<div class="col-md-12">
    	    <div class="col-md-10"><h4 style="color: #56888b; margin-bottom: 0px;">Dados</h4></div>
      	    <div class="col-md-2">
    		        <a href="index.php?exe=comercial/acomp_contrato&idc=<?php echo $id_contrato;?>"><button class="btn btn-primary" style="margin-bottom: 10px;">Editar contrato</button></a>
      	    </div>
    	    <br>
    	    <div class="col-md-6">
          		<h3 class="valor_dados"><font class="valor_titulo">Nome</font><br><?php echo $contrato_nome;?></h3>
          		<h3 class="valor_dados"><font class="valor_titulo">Obra</font><br><?php echo $contrato_obra;?></h3>
          		<h3 class="valor_dados"><font class="valor_titulo">M&ecirc;s de in&iacute;cio</font><br><?php echo $contrato_mes_inicio;?></h3>
          		<h3 class="valor_dados"><font class="valor_titulo">Dia de fechamento da medi&ccedil;&atilde;o</font><br><?php echo $contrato_dia_medi;?></h3>
    	    </div>
    	    <div class="col-md-6">
          		<h3 class="valor_dados"><font class="valor_titulo">Cliente</font><br><?php echo $contrato_cliente;?></h3>
          		<?php
          		if($contrato_pai == '-'){
          		    ?><h3 class="valor_dados"><font class="valor_titulo">Contrato pai</font><br><?php echo $contrato_pai;?></h3><?php
          		}else{
          		    ?>
          		    <h3 class="valor_dados"><font class="valor_titulo">Contrato pai</font><br>
          		    <a href="index.php?exe=comercial/acomp_contrato&idc=<?php echo $contrato_pai;?>"><?php echo $contrato_pai_nome;?></a></h3>
          		   <?php
          		}
          		?>
          		
          		<h3 class="valor_dados"><font class="valor_titulo">Previs&atilde;o de dura&ccedil;&atilde;o (em meses)</font><br><?php echo "$contrato_prev_dura meses";?></h3>
    	    </div>
    	    
    	    
    	</div>
    </div>
    
    <br>
    
<?php
//BUSCA OS TOTAIS POR CATEGORIA
$resultado_categorias = categorias($id_contrato);
$totalPROP = 0;
$totalCLI = 0;
$totalGERAL = 0;
$totalPRAT_total = 0;
$totalPRAT_PROP = 0;
$totalPRAT_CLI = 0;
?>
    
    <div class="row">
    	<div class="col-md-12">
    	    <div class="col-md-10"><h4 style="color: #56888b; margin-bottom: 10px;">Despesas por categoria</h4></div>
    	    <button id="btn_nova_despesa" type="button" class="btn btn-primary" style="margin-bottom: 10px;" data-toggle="modal" data-target="#myModal" onclick='nova_despesa()'>
    		       Nova despesa
    	    </button>
    	    <div>
        		<?php
        		include"nova_despesa.php";
        		?>
    	    </div>
    	    <br>
    	    <table class="count_tabela table table-striped">
        		<thead>
        		    <tr>
        			<th>Categoria</th>
        			<th>Valor previsto</th>
        			<th>Valor praticado</th>
        			<th>Saldo</th>
        			<th>Op&ccedil;&otilde;es</th>
        		    </tr>
        		</thead>
        		<tbody>
        		    <?php
            		foreach($resultado_categorias as $res_categorias){
            			$categoriaID = $res_categorias['id_categoria'];
            			$categoriaNOME = $res_categorias['nome'];
            			$categoriaACESSOID = $res_categorias['acesso'];
            			$categoriaACESSO = nomeacesso($categoriaACESSOID);
            			$categoriaIDPAI = $res_categorias['id_pai'];
            			$resultado_itens = itens($categoriaID);
            			$totalCAT = 0;
            			$totalPRAT = 0;
            			foreach($resultado_itens as $res_itens){
            			    $itemID = $res_itens['id_item'];
            			    $itemNOME = $res_itens['nome'];
            			    $itemVALOR_UNIT = $res_itens['valor'];
            			    $itemQTD = $res_itens['qtd'];
            			    $itemVALOR = $itemVALOR_UNIT * $itemQTD;
            			    $totalCAT = $totalCAT + $itemVALOR;
            			    $totalPROP = $totalPROP + $itemVALOR;
            			}
            				$valorPRAT = valor_praticado($categoriaID);
            				$totalPRAT_PROP = $totalPRAT_PROP + $valorPRAT;
              			$totalPRAT = $totalPRAT + $valorPRAT;
              			$totalPRAT_total = $totalPRAT_total + $totalPRAT;
              			//L� O NOME DOS PAIS DA CATEGORIA
              			$catNOMESPAIS = ' ('.nomespais($categoriaIDPAI).')';
              			if(($totalCAT-$totalPRAT)>0){
              			    $cor_saldo = '';
              			}else{
              			    $cor_saldo = "color='#FF0000'";
              			}
              			$nome_completo = busca_nome_completo($itemID);
            		    ?>
            		    <tr>
                			<td><a href="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $categoriaID;?>"><?php echo $nome_completo;?></a></td>
                			<td><?php echo 'R$ '.number_format($totalCAT,2,",",".");?></td>
                			<td><?php echo 'R$ '.number_format($totalPRAT,2,",",".");?></td>
                			<td><font <?php echo $cor_saldo;?>><?php echo 'R$ '.number_format($totalCAT-$totalPRAT,2,",",".");?></font></td>
                			<td><span class="glyph-table glyphicon glyphicon-pencil" aria-hidden="true" align="left"></span>
                			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                			    <span class="glyph-table glyphicon glyphicon-trash" aria-hidden="true" align="right"></span>
                			</td>
            		    </tr>
            		    <?php
            			$totalGERAL = $totalGERAL + $totalCAT;
        		    }
        		    if(($totalGERAL-$totalPRAT_total)>0){
        			       $cor_saldo = '';
        		    }else{
        			       $cor_saldo = "color='#FF0000'";
        		    }
        		    ?>
        		    <tr>
            			<th>TOTAL</th>
            			<th><?php echo 'R$ '.number_format($totalGERAL,2,",",".");?></th>
            			<th><?php echo 'R$ '.number_format($totalPRAT_total,2,",",".");?></th>
            			<th><font <?php echo $cor_saldo;?>><?php echo 'R$ '.number_format($totalGERAL-$totalPRAT_total,2,",",".");?></font></th>
            			<th>-</th>
        		    </tr>
        		</tbody>
    	    </table>
    	    
    	    
    	</div>
    </div>

<?php
$resultado_medicoes = lemedicoes($id_contrato);
$totalmedi = 0;
include"nova_medicao.php";
?>
<br>   
    <div class="row">
    	<div class="col-md-12">
    	    <div class="col-md-10"><h4 style="color: #56888b; margin-bottom: 0px;">Medi&ccedil;&otilde;es</h4></div>
    	    <div class="col-md-2">
    		      <button class="btn btn-primary" style="margin-bottom: 10px;" data-toggle="modal" data-target="#myModal_medi" onclick='nova_medicao()'>Nova medi&ccedil;&atilde;o</button>
    	    </div>
    	    <br>
    	    <table class="count_tabela table table-striped">
        		<thead>
        		    <tr>
            			<th>Data</th>
            			<th>Descri&ccedil;&atilde;o</th>
            			<th>Valor</th>
            			<th>Per&iacute;odo de refer&ecirc;ncia</th>
            			<th>Op&ccedil;&otilde;es</th>
        		    </tr>
        		</thead>
        		<tbody>
        		    <?php
              	foreach($resultado_medicoes as $res_med){
              			$medicaoID = $res_med['id_medicao'];
              			$medicaoMES = $res_med['mes_ref'];
              			$medicaoVALOR = $res_med['valor'];
              			$medicaoDESC = $res_med['descricao'];
              			$medicaoDATA = (string)$res_med['data_pagto'];
              			if($medicaoDATA <> ''){
              			    $medicaoDATA = trata_data2($medicaoDATA);
              			}
              			if($medicaoVALOR == ''){
              			    $medicaoVALOR = 0;
              			}
              			$periodoREF = $medicaoMES;
              			$totalmedi = $totalmedi + $medicaoVALOR;
              			
              			if($medicaoMES == $periodoREF){
              			    ?>
              			    <tr>
                  				<td><?php echo $medicaoDATA;?></td>
                  				<td><?php echo strtoupper($medicaoDESC);?></td>
                  				<td><?php echo 'R$ '.number_format($medicaoVALOR,2,",",".");?></td>
                  				<td><?php echo $periodoREF;?></td>
                  				<td>
                  				    <form class="form-inline" name="busca" id="busca" action="index.php?exe=comercial/acomp_contrato&idc=<?php echo $id_contrato;?>" enctype="multipart/form-data" method="post">
                        					<a href="#" data-toggle="modal" data-target="#myModal_medi"
                        					    onclick="edita_medicao(<?php echo "'$medicaoID','$medicaoDESC','$medicaoDATA','$medicaoMES','$medicaoVALOR'";?>)">
                        					    <span class="glyph-table glyphicon glyphicon-pencil" aria-hidden="true" align="left"></span>
                        					 </a>
                        					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        					<a href="#" onclick="apaga_medicao(<?php echo $medicaoID;?>);">
                        					<span class="glyph-table glyphicon glyphicon-trash" aria-hidden="true" align="right"></span></a>
                        					<input type="hidden" name="medicao_id_apaga" id="medicao_id_apaga" value="">
                        					<input type="submit" name="apaga_medicao_botao" id="apaga_medicao_botao" value="Apaga" style="visibility: hidden;">
                  				    </form>
                  				</td>
              			    </tr>
              			    <?php
              			}
        		    }
        		    ?>
        		    <tr>
            			<th>TOTAL</th>
            			<th><?php echo 'R$ '.number_format($totalmedi,2,",",".");?></th>
            			<th>-</th>
            			<th>-</th>
            			<th>-</th>
        		    </tr>
        		</tbody>
    	    </table>
    	</div>
    </div>
    
    
    </div>
    
