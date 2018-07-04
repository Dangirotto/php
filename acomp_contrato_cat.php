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
</SCRIPT>

<?php
include"contratos_funcoes.php";
//SELECIONA OS DADOS DO CONTRATO
$id_contrato = $_GET['idc'];
$id_categoria = $_GET['categoria'];
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
      	$contrato_pai = nome_contrato($contrato_pai);
    }
}
//APAGAR IMAGEM
if(isset($_POST['btn_del_img']) && $_POST['btn_del_img'] == 'Excluir imagem'){
    $despesa_DELID = $_POST['despesa_del_id'];
    $despesa_DELIMG = $_POST['despesa_del_imagem'];
    $pastaDel = '';
    
    $ftp_server = '***********************';
    $ftp_user_name = '***********************';
    $ftp_user_pass = '***********************';
    $conn_id = ftp_connect($ftp_server);
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    if (ftp_delete($conn_id, "public_html/novo/img_desp/".$despesa_DELIMG)) {
	    echo 'Arquivo '.$despesa_DELIMG.' excluido!';
    } else {
	    echo 'Problema ao excluir! Entre em contato com o suporte!';
    }
    ftp_close($conn_id);
    atualiza_delimg_banco($despesa_DELID);
}

//UPLOAD DE IMAGEM
if(isset($_POST['btn_upload']) && $_POST['btn_upload'] == 'Upload'){
    $despesa_UPID = $_POST['despesa_upload_id'];

    $foto_temp = $_FILES["arquivo"]["tmp_name"]; 
    $foto_name = $_FILES["arquivo"]["name"];
    $foto_size = $_FILES["arquivo"]["size"];
    $foto_type = $_FILES["arquivo"]["type"];
    
    if($foto_type == 'image/jpeg'){
    	$caminho1 = explode("\\",$foto_temp);
    	$n1 = count($caminho1);
    	$n2 = strlen($caminho1[$n1-1]);
    	$n3 = strlen($foto_temp);
    	$caminho2 = left($foto_temp, $n3 - $n2);
    	$nome1 = explode(".",$foto_name);
    	$novo_nome = $nome1[0].' redim.'.$nome1[1];
    	$img_origem = ImageCreateFromJpeg($foto_temp);
    	$largura = imagesx($img_origem);
    	$altura = imagesy($img_origem);
    	$nova_largura = 800;
    	$nova_altura = $altura*$nova_largura/$largura;
    	$img_destino = imagecreatetruecolor($nova_largura, $nova_altura);
    	imagecopyresampled($img_destino,$img_origem,0,0,0,0,$nova_largura,$nova_altura,$largura,$altura);
    	imageJPEG($img_destino,$caminho2.$novo_nome,85);
    	$caminho_nome_local = $caminho2.$novo_nome;
    }else{
      $caminho_nome_local = $foto_temp;
    }
    
    //CAMINHO E NOME DA FOTO NO SERVIDOR
    $foto_name_EXP = explode('.',$foto_name);
    $n = count($foto_name_EXP);
    $extensao = $foto_name_EXP[$n - 1];
    $nome_arquivo = "$despesa_UPID.$extensao";
    $caminho = "public_html/novo/img_desp/".$nome_arquivo;
    
    //Login no FTP
    $ftp_server = '****************************';
    $ftp_user_name = '****************************';
    $ftp_user_pass = '****************************';
    $conn_id = ftp_connect($ftp_server);
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    // turn passive mode on
    ftp_pasv($conn_id, true);
    //ENVIA O ARQUIVO PARA A PASTA
    $upload = ftp_put($conn_id, "$caminho", "$caminho_nome_local", FTP_BINARY); // upload do arquivo
    if (!$upload) {  // checa download do arquivo 
      echo "Aconteceu algum erro !!!"; 
    } else { 
	     atualiza_upload_banco($despesa_UPID, $nome_arquivo);
    	?>
    	<div class="panel panel-success">
    	    <div class="panel-heading">
    		      <h3 class="panel-title">Upload realizado com sucesso!</h3> 
    	    </div>
    	</div>
    	<?php
    }
    ftp_close($conn_id);
}

//BUSCA O NOME DA CATEGORIA
$sql_cat = "SELECT cat.*,cc.nome FROM tab_cf_categorias as cat
	    INNER JOIN tab_contacontabil as cc ON cc.id_contacontabil = cat.id_contacontabil
	    WHERE cat.id_categoria = :id_categoria";
try{
    $query_cat = $conecta->prepare($sql_cat);
    $query_cat->bindValue(':id_categoria',$id_categoria,PDO::PARAM_STR);
    $query_cat->execute();
    $resultado_cat = $query_cat->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOexception $error_sqlselect){
    echo 'Erro ao selecionar os dados da categoria - '.$error_sqlselect->getMessage();
}
foreach($resultado_cat as $res_cat){
    $id_contacontabil = $res_cat['id_contacontabil'];
    $nome_categoria = $res_cat['nome'];
}
?>
<div id="paginatoda" class="row">
    
    <h3 style="color: #56888B"><?php echo "$nome_categoria - $contrato_nome";?></h3>
    <ol class="breadcrumb">
    	<!-- BREADCRUMB -->
    	<li>
    	    <a href="index.php">Inicio</a>
    	</li>
    	<li>
    	    <a href="index.php?exe=comercial/contratos">Contratos</a>
    	</li>
    	<li>
    	    <a href="index.php?exe=comercial/acomp_contrato&idc=<?php echo $id_contrato;?>"><?php echo $contrato_nome;?></a>
    	</li>
    	<li class="active"><?php echo $nome_categoria;?></li>
    </ol>
    
    <br>
    
<?php
//BUSCA AS DESPESAS DA CATEGORIA
$limitado = false;
$busca_nome = '';
if(isset($_POST['botao_busca']) && $_POST['botao_busca'] == 'Ir'){
	$busca_nome = $_POST['busca_nome'];
	$resultado_despsas = despesas_pesquisa($id_categoria, $busca_nome);
}elseif(isset($_POST['executar']) && $_POST['executar'] == 'Filtra por Data'){
	$mesFiltro = $_POST['mes'];
	$resultado_despsas = despesas_cat_data($id_categoria, $mesFiltro);
}elseif(isset($_POST['executar']) && $_POST['executar'] == 'Filtra por Vencimento'){
	$mesFiltro = $_POST['mes'];
	$resultado_despsas = despesas_cat_venc($id_categoria, $mesFiltro);
}elseif(isset($_POST['executar']) && $_POST['executar'] == 'Pesquisar'){
	$pesquisa = $_POST['pesquisa'];
	$resultado_despsas = despesas_pesquisa($id_categoria, $pesquisa);
}elseif(isset($_POST['executar']) && $_POST['executar'] == 'Filtra por Dia'){
	$diaFiltro = $_POST['data_dia'];
	$resultado_despsas = despesas_cat_dia($id_categoria, $diaFiltro);
}else{
	if(isset($_POST['executar']) && $_POST['executar'] == 'Mostrar todas'){
		$resultado_despsas = despesas_cat($id_categoria);
	}else{
		//PAGINA��O
		//N�MERO DE P�GINAS
		$num_despesas = num_despesas($id_categoria);
		$num_paginas1 = $num_despesas / 10;
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
		//BUSCA NO BANCO
		$resultado_despsas = despesas_cat_limite_pag($id_categoria, '20',$limit_inf);
		$limitado = true;
	}
}
$totalDESP = 0;
$resultado_categorias = categorias($id_contrato);
$fixo_mensal = 'fixo';
?>
    
    <div class="row">
      	<div class="col-md-12">
          	    <div class="col-md-10"><h4 style="color: #56888b; margin-bottom: 0px;">Despesas da categoria</h4></div>
          	    <!-- BUSCA POR NOME -->
          	    <form class="form-inline" name="busca" id="busca" action="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $id_categoria;?>" enctype="multipart/form-data" method="post">
              		<div class="input-group" style="margin-left:20px; margin-bottom:10px;">
              		    <input type="text" name="busca_nome" class="form-control" placeholder="Buscar..." onKeyPress="return submitenter(this,event)" value="<?php echo $busca_nome;?>">
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
          	    <!-- **************** -->
          	    <button id="btn_nova_despesa" type="button" class="btn btn-primary" style="margin-bottom: 10px;" data-toggle="modal" data-target="#myModal" onclick='nova_despesa()'>
          		      Nova despesa
          	    </button>
          	    <br>
          	    <form class="form-inline" name="busca" id="busca" action="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $id_categoria;?>" enctype="multipart/form-data" method="post">
              		<!--  PAGAR DESPESA -->
              		<input type="hidden" name="despesa_id_pagamento" id="despesa_id_pagamento" value="">
              		<input type="submit" name="pagamento_despesa" id="pagamento_despesa" value="Pagamento" style="visibility: hidden; width: 0px;">
              		<!--  DESPAGAR DESPESA -->
              		<input type="hidden" name="despesa_id_despagar" id="despesa_id_despagar" value="">
              		<input type="submit" name="despagar_despesa" id="despagar_despesa" value="Despagar" style="visibility: hidden; width: 0px;">
              		<!--  APAGAR DESPESA -->
              		<input type="hidden" name="despesa_id_apaga" id="despesa_id_apaga" value="">
              		<input type="submit" name="apaga_despesa" id="apaga_despesa" value="Apaga" style="visibility: hidden;">
          	    </form>
          	    <table class="count_tabela table table-striped">
          		<thead>
          		    <tr>
              			<th>Documento</th>
              			<th>Valor (% pago)</th>
              			<!--<th>Data da despesa</th>-->
              			<th>Data da despesa</th>
              			<th>Descri&ccedil;&atilde;o</th>
          			<th>Op&ccedil;&otilde;es</th>
          		    </tr>
          		</thead>
          		<tbody>
          		    <?php
          		    foreach($resultado_despsas as $res_despsas){
                			$cfdespID = $res_despsas['id_cf_despesa'];
                			$itemID = $res_despsas['id_item'];
                			$despesaID = $res_despsas['id_despesa'];
                			$resultado_dados_despesa = dados_despesa($despesaID);
                			foreach($resultado_dados_despesa as $res_dados){
                			    $despesaDOC		 = $res_dados['documento'];
                			    $despesaVALOR	 = $res_dados['valor'];
                			    $despesaIDCEN	 = $res_dados['id_centrocusto'];
                			    $despesaDATA	 = $res_dados['data_despesa'];
                			    $despesaDESCRICAO = $res_dados['descricao'];
                			    $despesaPARCELA   = $res_dados['parcelamento'];
                			    $despesaVENCIMENTO = $res_dados['data_despesa_venc'];
                			    $despesaVENCANO = $res_dados['venc_ano'];
                			    $despesaFORNECEDOR = $res_dados['id_empresa'];
                			    $despesaPAGTO = $res_dados['pagto'];
                			    $despesaIMAGEM = $res_dados['imagem'];
                			    if($despesaIMAGEM == null){
                				        $despesaIMAGEM = '';
                			    }
                			    //SE FOR MENSAL, VERIFICA SE A DESPESA EST� DENTRO DO PER�ODO SELECIONADO
                			    if($fixo_mensal == 'mensal'){
                				    $dataP = explode(' - ',$periodoREF);
                				    $dentro = verificadatalimites($dataP[0], $dataP[1], $despesaDATA);
                			    }else{
                				    $dentro = true;
                			    }
                			    //BUSCA O CONTRATO E CATEGORIA DA DESPESA
                			    $id_categoria_contrato = busca_categoria_contrato($despesaID);
                			    foreach($id_categoria_contrato as $res_ids){
                    				$despesa_id_categoria = $res_ids['id_categoria'];
                    				$despesa_id_contrato = $res_ids['id_contrato'];
                			    }
                			    if($dentro == true){
                				    $totalDESP = $totalDESP + $despesaVALOR;
                				    if(!empty($despesaVENCIMENTO)){
                					    $despeVEC = 'Vencimento:';
                				    }else{
                					    $despeVEC = '';
                				    }
                				    $valorpago = verificapagamento($despesaID);
                				    $despesaCCUSTO = ccustodespesa($despesaID);
                				    $i++;
                				    if($i % 2 == 0){
                					    $cor =' style="background:#E6FFF2;"';
                				    }else{
                					    $cor = 'style="background:#f4f4f4;"';
                				    }
                				    $data = date('d/m/Y');
                				    $valorPAGTO = 0;
                				    if($despesaPAGTO <> ''){
        					               $valorPAGTO = 100;
                				    }
                				    if(strpos($despesaVALOR,".") == ''){
                					         $despesaVALOR = $despesaVALOR.'.00';
                				    }
                				    $temp1 = right($despesaVALOR,2);
                				    $temp2 = left($temp1,1);
                				    if($temp2 == "."){
                					         $despesaVALOR = $despesaVALOR.'0';
                				    }
                				    ?>
                				    <tr>
                    					<td><?php echo strtoupper($despesaDOC);//.'-'.$despesaID;?></td>
                    					<td><?php echo number_format($despesaVALOR,2,",",".")." ($valorpago)";?></td>
                    					<!--<td><?php echo $despesaDATA;?></td>-->
                    					<td><?php echo $despesaVENCIMENTO;?></td>
                    					<td><?php echo $despesaDESCRICAO;?></td>
                    					<td>
                    					    <form class="form-inline" name="busca" id="busca" action="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $id_categoria;?>" enctype="multipart/form-data" method="post">
                        					    <?php
                        					    if($valorPAGTO == 100){
                              						?>
                              						<span class="glyph-table glyphicon glyphicon-usd" aria-hidden="true" align="right" title="Despesa paga"></span>
                              						<?php
                          					  }else{?>
                            						<a href="#" onclick="pagar_despesa(<?php echo $despesaID;?>);">
                            						<span class="glyph-table glyphicon glyphicon-usd" aria-hidden="true" align="right" title="Lan&ccedil;ar pagamento"></span></a>
                    						      <?php } ?>
                        					    &nbsp;&nbsp;
                        					    <a href="#" data-toggle="modal" data-target="#myModal" onclick="edita_despesa(<?php echo "'$despesaID','$despesaDOC','$despesaDESCRICAO','$despesaVALOR','$despesaFORNECEDOR','$despesaDATA'";?>)">
                        						          <span class="glyph-table glyphicon glyphicon-pencil" aria-hidden="true" align="left" title="Editar despesa"></span>
                        					    </a>
                        					    &nbsp;&nbsp;
                        					    <a href="#" data-toggle="modal" data-target="#ModalMove" onclick="mover_despesa(<?php echo "'$despesaID','$despesa_id_categoria','$despesa_id_contrato'";?>);">
                        						          <span class="glyph-table glyphicon glyphicon-share-alt" aria-hidden="true" align="right" title="Mover despesa"></span></a>
                        					    </a>
                        					    &nbsp;&nbsp;
                        					    <?php
                        					    //FAZER UPLOAD / VER IMAGEM
                        					    if($despesaIMAGEM <> ''){
                              						?>
                              						<a href="#" data-toggle="modal" data-target="#ModalVerImg" 
                              						    onclick="ver_imagem(<?php echo "'$despesaIMAGEM','$despesaID'";?>);">
                              						    <span class="glyph-table glyphicon glyphicon-picture" aria-hidden="true" align="right" title="Ver imagem"></span></a>
                              						</a>
                              						&nbsp;&nbsp;
                              						<?php
                        					    }else{
                              						?>
                              						<a href="#" data-toggle="modal" data-target="#ModalUpload" 
                              						    onclick="upload_imagem_id(<?php echo "'$despesaID'";?>);">
                              						    <span class="glyph-table glyphicon glyphicon-upload" aria-hidden="true" align="right" title="Carregar imagem"></span></a>
                              						</a>
                              						&nbsp;&nbsp;
                              						<?php
                        					    }
                        					    ?>
                        					    <a href="#" onclick="manda_apagar(<?php echo $despesaID;?>);">
                        					    <span class="glyph-table glyphicon glyphicon-trash" aria-hidden="true" align="right" title="Excluir despesa"></span></a>
                    					    </form>
                    					</td>
                				    </tr>
                				    <?php
                			    }
                			}
          		    }
          		    ?>
          		</tbody>
          	</table>
          	    <?php
          	    if($limitado == true){ ?>
              		<div class="row">
              		    <div class="col-md-10" style="padding-right: 0px; padding-left: 0px;">
                    			<ul class="pagination pagination-sm" style="margin: 0px">
                    			    <li>
                        			    <?php
                        			    if(isset($_POST['botao_busca']) && $_POST['botao_busca'] == 'Ir'){
                            				?>
                            				<li>
                            				    <a href="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $id_categoria;?>">Exibir todos</a>
                            				</li>
                            				<?php
                        			    }else{
                              			    ?>
                              			    <a href="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $id_categoria;?>" aria-label="Previous" title="Primeira p&aacute;gina">
                              				        <span aria-hidden="true">&laquo;</span>
                              			    </a>
                          			    </li>
                          			    <?php
                          			    $pagina_inicial = $pagina - 5;
                          			    if($pagina_inicial < 1){
                          				        $pagina_inicial = 1;
                          			    }
                          			    $pagina_final = $pagina_inicial + 10;
                          			    if($pagina_final > $num_paginas){
                          				        $pagina_final = $num_paginas;
                          			    }
                          			    for($i=$pagina_inicial;$i<=$pagina_final;$i++){
                                				if($pagina == $i){
                                				    ?>
                                				    <li class="active">
                                					         <a href="#"><?php echo $i;?></a>
                                				    </li>
                                				    <?php
                                				}else{
                                				    ?>
                                				    <li>
                                					         <a href="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $id_categoria;?>&pag=<?php echo $i;?>"><?php echo $i;?></a>
                                				    </li>
                                				    <?php
                                				}
                          			    }
                          			    ?>
                          			    <li>
                          			    <a href="index.php?exe=comercial/acomp_contrato_cat&idc=<?php echo $id_contrato;?>&categoria=<?php echo $id_categoria;?>&pag=<?php echo $num_paginas;?>" aria-label="Next" title="&Uacute;ltima p&aacute;gina (<?php echo $num_paginas;?>)">
                          				        <span aria-hidden="true">&raquo;</span>
                          			    </a>
                          			    <?php
                    			    }
                    			    ?>
                    			    </li>
                    			</ul>
              		    </div>
              		</div>
              	    <?php
          	    }
          	    ?>
      	</div>
    </div>
    
    </div>
    
