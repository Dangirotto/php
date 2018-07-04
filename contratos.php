<SCRIPT TYPE="text/javascript">
<!--
function submitenter(myfield,e) {
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return true;
    if (keycode == 13) {
        document.getElementById("botao_busca").click();
        return false;
    } else return true;
}
function desativar_contrato(id_contrato) {
    var confirma = confirm('Tem certeza que deseja desativar este contrato?');
    if(confirma){
    	document.getElementById("id_desativa").value = id_contrato;
    	document.getElementById("botao_desativa").click();
    }
}
function reativar_contrato(id_contrato) {
    var confirma = confirm('Tem certeza que deseja reativar este contrato?');
    if(confirma){
    	document.getElementById("id_desativa").value = id_contrato;
    	document.getElementById("botao_reativa").click();
    }
}
//-->
</SCRIPT>
<?php
include"contratos_funcoes.php";
$busca_nome = '';
$tipo = 'Contrato';
//TRATA CEN�RIO
if(isset($_SESSION['MM_CenarioID'])){
    $cenarioID = $_SESSION['MM_CenarioID'];
}else{
    $cenarioID = busca_cenario_id('ATIVO');
}
//CRIANDO UM CONTRATO
if(isset($_POST['novo_contrato']) && $_POST['novo_contrato'] == 'Criar novo contrato'){
    $tipo = 'Contrato';
    $nome_contrato = strtoupper($_POST['nome_contrato']);
    if(isset($_POST['cliente_novo_cria'])){
        $novo_cliente_nome = strtoupper($_POST['cliente_novo_cria']);
        $nome_cliente = cria_cliente($novo_cliente_nome);
    }else{
        $nome_cliente = $_POST['nome_cliente'];
    }
    
    $nome_obra =      $_POST['nome_obra'];
    $descricao =      $_POST['descricao'];
    $contrato_pai =   $_POST['contrato_pai'];
    $dia_fecha =      $_POST['dia_fecha'];
    $mes_inicio =     $_POST['mes_inicio'];
    $prev_dura =      $_POST['prev_dura'];
    $tipo = 'contrato';
    $buscado = 0;
    $tipo_contrato = 'fixo';
    //CRIA CENTRO DE CUSTO DO CONTRATO
    $centrocustoID = 0;
    $sql_cria_centrocusto = 'INSERT INTO tab_centrocusto (nome, id_pai) VALUES (:nome, :id_pai)';
    try{
      	$query_cria_centrocusto = $conecta->prepare($sql_cria_centrocusto);
      	$query_cria_centrocusto->bindValue(':nome',$nome_contrato,PDO::PARAM_STR);
      	$query_cria_centrocusto->bindValue(':id_pai',$cenarioID,PDO::PARAM_STR);
      	$query_cria_centrocusto->execute();
      	$centrocustoID = $conecta->lastInsertId();
    }catch(PDOexception $error_criaccusto){
	       echo 'Erro ao criar centro de custo - '.$error_criaccusto->getMessage();
    }
    //CRIA O CONTRATO
    $sql_cadcontrato = 'INSERT INTO tab_contratos (nome, id_cliente, obra, descricao, id_contratopai, dia_medi, mes_inicio, prev_dura,
			id_centrocusto, id_cenario, tipo, buscado, tipo_contrato) 
			VALUES (:nome, :id_cliente, :obra, :descricao, :id_contratopai, :dia_medi, :mes_inicio, :prev_dura,
			:id_centrocusto, :id_cenario, :tipo, :buscado, :tipo_contrato)';
    try{
      	$query_cadcontrato = $conecta->prepare($sql_cadcontrato);
      	$query_cadcontrato->bindValue(':nome',$nome_contrato,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':id_cliente',$nome_cliente,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':obra',$nome_obra,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':descricao',$descricao,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':id_contratopai',$contrato_pai,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':dia_medi',$dia_fecha,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':mes_inicio',$mes_inicio,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':prev_dura',$prev_dura,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':id_centrocusto',$centrocustoID,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':id_cenario',$cenarioID,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':tipo',$tipo,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':buscado',$buscado,PDO::PARAM_STR);
      	$query_cadcontrato->bindValue(':tipo_contrato',$tipo_contrato,PDO::PARAM_STR);
      	$query_cadcontrato->execute();
      	$contrato_novo_ID = $conecta->lastInsertId();
    }catch(PDOexception $error_cadcontrato){
        echo 'Erro ao criar o contrato - '.$error_cadcontrato->getMessage();
    }
    
    //CRIA AS CATEGORIAS
    $num_itens = $_POST['num_itens'];
    for($i = 1; $i <= $num_itens; $i++){
      	if(isset($_POST["cont_item_$i"])){
      	    if($_POST["cont_item_$i"] <> ''){
          		$cont_item_nome = $_POST["cont_item_$i"];
          		$cont_item_exp = explode(' - ',$cont_item_nome);
          		$cont_item = $cont_item_exp[count($cont_item_exp)-1];
          		$cont_quant = $_POST["cont_quant_$i"];
          		$cont_valor = $_POST["cont_valor_$i"];
          		$cont_valor = str_replace('.','',$cont_valor);
          		$cont_valor = str_replace(',','.',$cont_valor);
          		$fornecimento = 'proprio';
          		$acesso = 26;
          		$sql_cat = 'INSERT INTO tab_cf_categorias (id_contrato, id_contacontabil, acesso, fornecimento)
        			             VALUES (:id_contrato, :id_contacontabil, :acesso, :fornecimento)';
          		try{
          		    $query_cat = $conecta->prepare($sql_cat);
          		    $query_cat->bindValue(':id_contrato',$contrato_novo_ID,PDO::PARAM_STR);
          		    $query_cat->bindValue(':id_contacontabil',$cont_item,PDO::PARAM_STR);
          		    $query_cat->bindValue(':acesso',$acesso,PDO::PARAM_STR);
          		    $query_cat->bindValue(':fornecimento',$fornecimento,PDO::PARAM_STR);
          		    $query_cat->execute();
          		    $categoriaID = $conecta->lastInsertId();
          		    //CRIA O ITEM
          		    novo_item($categoriaID, $cont_valor, $cont_quant, $cont_item);
          		}catch(PDOexception $error_cat){
          		    echo 'Erro ao criar a categoria - '.$error_cat->getMessage();
          		}
      	    }
      	}
    }
    ?>
    <div class="panel panel-success">
      	<div class="panel-heading">
      	    <h3 class="panel-title">Contrato criado com sucesso!</h3> 
      	</div>
    </div>
    <?php
}
//DESATIVANDO UM CONTRATO
if(isset($_POST['botao_desativa']) && $_POST['botao_desativa'] == 'Desativa'){
    $id_desativa = $_POST['id_desativa'];
    $tipo = 'inativo';
    $sql = 'UPDATE tab_contratos SET tipo = :tipo WHERE id_contrato = :id_contrato';
    try{
      	$query = $conecta->prepare($sql);
      	$query->bindValue(':tipo',$tipo,PDO::PARAM_STR);
      	$query->bindValue(':id_contrato',$id_desativa,PDO::PARAM_STR);
      	$query->execute();
    }catch(PDOexception $error_sqlselect){
        echo 'Erro ao desativar o contrato - '.$error_sqlselect->getMessage();
    }
}
//REATIVANDO UM CONTRATO
if(isset($_POST['botao_reativa']) && $_POST['botao_reativa'] == 'Reativa'){
    $id_reativa = $_POST['id_desativa'];
    $tipo = 'Contrato';
    $sql = 'UPDATE tab_contratos SET tipo = :tipo WHERE id_contrato = :id_contrato';
    try{
      	$query = $conecta->prepare($sql);
      	$query->bindValue(':tipo',$tipo,PDO::PARAM_STR);
      	$query->bindValue(':id_contrato',$id_desativa,PDO::PARAM_STR);
      	$query->execute();
    }catch(PDOexception $error_sqlselect){
        echo 'Erro ao reativar o contrato - '.$error_sqlselect->getMessage();
    }
    echo '<meta http-equiv="refresh" content="0; URL = index.php?exe=comercial/contratos" />';
}
if(isset($_POST['novo_contrato']) && $_POST['novo_contrato'] == 'Criar novo contrato'){
    $tipo = 'Contrato';
}
//N�MERO DE P�GINAS PRA PAGINA��O
$id_contratopai = 0;
if(isset($_GET['tipo'])){
    if($_GET['tipo'] == 'inativos'){
	    $tipo = 'inativo';
    }
}
if($tipo == 'Contrato'){
    $num_contratos = num_contratos($cenarioID, $tipo, $id_contratopai);
}else{
    $num_contratos = num_contratos_inativos($cenarioID, $tipo, $id_contratopai);
}
$num_paginas1 = $num_contratos / 10;
$num_paginas = (int)$num_paginas1;
if($num_paginas <> $num_paginas1){
    $num_paginas++;
}
//BUSCA A P�GINA SELECIONADA
if(isset($_GET['pag'])){
    $pagina = $_GET['pag'];
}else{
    $pagina = 1;
}
$limit_inf = ($pagina - 1) * 10;
$limit_sup = $pagina * 10;
//SELECIONA OS CONTRATOS CADASTRADOS NO BD
//BUSCANDO CONTRATOS POR NOME
if(isset($_POST['botao_busca']) && $_POST['botao_busca'] == 'Ir'){
    $num_paginas = 1;
    $busca_nome = $_POST['busca_nome'];
    $sql = "SELECT * FROM tab_contratos WHERE nome LIKE '%$busca_nome%' AND tipo = :tipo";
    try{
      	$query = $conecta->prepare($sql);
      	$query->bindValue(':tipo',$tipo,PDO::PARAM_STR);
      	$query->execute();
      	$resultado_select = $query->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOexception $error_sqlselect){
        echo 'Erro ao pesquisar contrato por nome - '.$error_sqlselect->getMessage();
    }
}else{
    $sql_select = 'SELECT * FROM tab_contratos WHERE id_cenario = :id_cenario AND tipo = :tipo AND id_contratopai = :id_contratopai
		    ORDER BY nome LIMIT 10 OFFSET '.$limit_inf;
    $i=0;
    try{
      	$query_select = $conecta->prepare($sql_select);
      	$query_select->bindValue(':id_cenario',$cenarioID,PDO::PARAM_STR);
      	$query_select->bindValue(':tipo',$tipo,PDO::PARAM_STR);
      	$query_select->bindValue(':id_contratopai',$id_contratopai,PDO::PARAM_STR);
      	$query_select->execute();
      	$resultado_select = $query_select->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOexception $error_sqlselect){
	       echo 'Erro ao selecionar os contratos - '.$error_sqlselect->getMessage();
    }
}
?>

<div class="row">
    
    
    <h3 style="color: #56888B">Contratos</h3>
    <ol class="breadcrumb">
    	<!-- BREADCRUMB -->
    	<li>
    	    <a href="index.php">Inicio</a>
    	</li>
    	<li class="active">Contratos</li>
    </ol>

    <div class="row">

      <ul class="nav nav-tabs">
        <?php
        if($tipo == 'Contrato'){
          	?>
          	<li class="active"><a href="index.php?exe=comercial/contratos" style="color: #56888B">Ativos</a></li>
          	<li><a href="index.php?exe=comercial/contratos&tipo=inativos" style="color: #56888B"> Inativos</a></li>
          	<?php
          }else{
          	?>
          	<li><a href="index.php?exe=comercial/contratos" style="color: #56888B">Ativos</a></li>
          	<li class="active"><a href="index.php?exe=comercial/contratos&tipo=inativos" style="color: #56888B"> Inativos</a></li>
          	<?php
          }
          ?>
      </ul>
      <br>
      <form name="desativa_contrato" id="desativa_contrato" action="index.php?exe=comercial/contratos&tipo=inativos" enctype="multipart/form-data" method="post" style="visibility: hidden; height: 0;">
          <input type="text" name="id_desativa" id="id_desativa" value="">
          <input type="submit" name="botao_desativa" id="botao_desativa" value="Desativa">
          <input type="submit" name="botao_reativa" id="botao_reativa" value="Reativa">
      </form>
      	<div class="">
        	   <a href="index.php?exe=comercial/novo_contrato">
        		     <button class="btn btn-primary" style="margin-bottom: 10px; float:left;" >Novo contrato</button>
        	   </a>
        	    <form class="form-inline" name="busca" id="busca" action="index.php?exe=comercial/contratos" enctype="multipart/form-data" method="post" style="float:right; width: 1080px;">
            		<div class="input-group" style="margin-left:20px; margin-bottom:10px;">
            		    <input type="text" id="busca_nome" name="busca_nome" class="form-control" placeholder="Buscar..." onKeyPress="return submitenter(this,event)" value="<?php echo $busca_nome;?>">
            		    <span class="input-group-btn">
                			<input type="submit" name="botao_busca" id="botao_busca" class="btn btn-default" value="Ir">
                			<!-- CANCELAR PESQUISA -->
                			<?php
                			if(isset($_POST['botao_busca']) && $_POST['botao_busca'] == 'Ir'){
                			    ?>
                			    <input type="submit" name="botao_cancela_busca" id="botao_cancela_busca" class="btn btn-danger" value="X">
                			    <?php
                			}
                			?>
                			<!-- **************** -->
            		    </span>
            		</div>
        	    </form>
      	</div>
      	    <div class="col-md-8"></div>
      	    <div>
        		    <div class="btn-group" aria-hidden="true" align="right">
            			<button type="button" class="btn btn-default btn-md" style="margin-bottom: 10px">
            			    <span class="glyphicon glyphicon-pencil"></span> Editar
            			</button>
            			<button type="button" class="btn btn-default btn-md" style="margin-bottom: 10px">
            			    <span class="glyphicon glyphicon-trash"></span> Excluir 
            			</button>
        		    </div>
      	    </div>
	</div>
	<div class="row">
      <div class="col-md-12">
      	<table class="count_tabela table table-striped">
      	    <thead>
          		<tr>
          		    <th>#</th>
          		    <th>Descri&ccedil;&atilde;o</th>
          		    <th>Cliente</th>
          		    <th>Status</th>
          		    <th>Op&ccedil;&otilde;es</th>
          		</tr>
      	    </thead>
      	    <tbody>
            		<?php
            		$num = $limit_inf;
            		foreach($resultado_select as $res_select){
            		    $contratoID = $res_select['id_contrato'];
            		    //VERIFICA SE O USU�RIO TEM ACESSO A ESTE CONTRATO
            		    $aplicacao = 1;
            		    if($aplicacao > 0 || $usuario_nivel == 'admin'){
                  			$num++;
                  			$contratoNOME = strtoupper($res_select['nome']);
                  			$contratoCLIENTE = $res_select['id_cliente'];
                  			$contratoCLIENTENOME = strtoupper(nomecliente($contratoCLIENTE));
                  			?>
                  			<tr>
                  			    <td><?php echo $num;?></td>
                  			    <td><a href="index.php?exe=comercial/acomp_contrato&idc=<?php echo $contratoID;?>"><?php echo $contratoNOME;?></a></td>
                  			    <td><?php echo $contratoCLIENTENOME;?></td>
                  			    <td>Em andamento</td>
                  			    <td>
                        				<?php
                        				if($tipo == 'Contrato'){
                        				    ?>
                        				    <a href="index.php?exe=comercial/edita_contrato&idc=<?php echo $contratoID; ?>">
                        				    <span class="glyph-table glyphicon glyphicon-pencil" aria-hidden="true" align="left" title="Editar dados do contrato"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        				    <a href="#" onclick="desativar_contrato(<?php echo $contratoID;?>)">
                        				    <span class="glyph-table glyphicon glyphicon-trash" aria-hidden="true" align="right" title="Desativar contrato"></span></a>
                        				    <?php
                        				}else{
                        				    ?>
                        				    <a href="#" onclick="reativar_contrato(<?php echo $contratoID;?>)">
                        				    <span class="glyph-table glyphicon glyphicon-share-alt" aria-hidden="true" align="right" title="Reativar contrato"></span></a>
                        				    <?php
                        				}
                        				?>
                  			    </td>
                  			</tr>
                  			<?php
            		    }
            		}
            		?>
      	    </tbody>
      	</table>
      	<div class="row">
        	    <div class="col-md-10" style="padding-right: 0px; padding-left: 0px;">
            		<ul class="pagination pagination-sm" style="margin: 0px">
              		    <li>
                    			<a href="index.php?exe=comercial/contratos" aria-label="Previous" title="Primeira p&aacute;gina">
                    			    <span aria-hidden="true">&laquo;</span>
                    			</a>
              		    </li>
              		    <?php
              		    for($i=1;$i<=$num_paginas;$i++){
                    			if($pagina == $i){
                    			    ?>
                    			    <li class="active">
              				              <a href="#"><?php echo $i;?></a>
                    			    </li>
                    			    <?php
                    			}else{
                    			    ?>
                    			    <li>
                				            <a href="index.php?exe=comercial/contratos&pag=<?php echo $i;?>"><?php echo $i;?></a>
                    			    </li>
                    			    <?php
                    			}
              		    }
              		    ?>
              		    <li>
          			       <a href="index.php?exe=comercial/contratos&pag=<?php echo $num_paginas;?>" aria-label="Next" title="&Uacute;ltima p&aacute;gina (<?php echo $num_paginas;?>)">
              			        <span aria-hidden="true">&raquo;</span>
            			     </a>
              		    </li>
            		</ul>
        	    </div>
      	</div>
      </div>
      
      
    </div>
    
