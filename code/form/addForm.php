<?php
    
    session_start();
    
    if(!isset($_SESSION['id'])){
        header("Location: index.php");
    }

    $usuario      = $_SESSION['usuario'];
    $nombre       = $_SESSION['nombre'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    header("Content-Type: text/html;charset=utf-8");

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>sdsyp</title>
        <script type="text/javascript" src="../../js/jquery.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
        <link href="../../fontawesome/css/all.css" rel="stylesheet">
       	<script src="https://kit.fontawesome.com/fed2435e21.js" crossorigin="anonymous"></script>
		<style>
        	.responsive {
           		max-width: 100%;
            	height: auto;
        	}
    	</style>
    	<!--SCRIPT PARA VALIDAR SI EL REGISTRO YA ESTÁ EN LA BD-->
   </head>
    <body>
    
		<center>
	    	<img src='../../img/logo.png' width=150 height=154 class="responsive">
		</center>

<?php

	date_default_timezone_set("America/Bogota");
	include("../../conexion.php");
	require_once("../../zebra.php");

    $sql_personas = "SELECT id_persona, nombres_persona,apellidos_persona,cedula_persona FROM personas ORDER BY apellidos_persona ASC";
    $result_personas = $mysqli->query($sql_personas);

    $sql_proyectos = "SELECT id_proyecto, descripcion_proyecto FROM proyectos ORDER BY descripcion_proyecto ASC";
    $result_proyectos = $mysqli->query($sql_proyectos);

?>

		<div class="container pt-2">
			<h1><b><i class="fa-solid fa-dolly"></i> Formulario</b></h1>
	        
	        <div class="row">
	        	<div class="col-md-12">
                    <form id="form_contacto" action='processForm.php' method="POST">
                    	<div class="row">
                    		<div class="col">
                    			<div id="result-upc_sku_item"></div>
                    		</div>  
                    	</div>
	                	<div class="form-group">
                			<div class="row">
                    			<div class="col-12 col-sm-3">
	                   	        	<label for="date_item">* FECHA:</label>
									<input type='date' name='fecha_form' class='form-control' id="fecha_form" required autofocus />
	                        	</div>
                                <div class="col-12 col-sm-3">
	                   	        	<label for="date_item">* FECHA ATENCION:</label>
									<input type='date' name='atencion_form' class='form-control' id="atencion_form" required autofocus />
	                        	</div>
	                        	<div class="col-12 col-sm-5">
		                            <label for="upc_sku_item">* PERSONA:</label>
                                    <select name="cedula_persona" class="form-control" id="cedula_persona" required>
                                        <option value="">Seleccione...</option>
                                        <?php while ($row_personas = $result_personas->fetch_assoc()) { ?>
                                            <option value="<?= $row_personas['cedula_persona']; ?>"><?= $row_personas['apellidos_persona'] . " " . $row_personas['nombres_persona']; ?></option>
                                        <?php } ?>
                                    </select>
	                        	</div>
	                        	
	                    	</div>
	                    </div>

	                    <div class="form-group">
                			<div class="row">
                            <div class="col-12 col-sm-3 mt-3">
		                            <label for="proyecto_form">* PROYECTO:</label>
                                    <select name="proyecto_form" class="form-control" id="proyecto_form">
                                        <option value="">Seleccione...</option>
                                        <?php while ($row_proyectos = $result_proyectos->fetch_assoc()) { ?>
                                            <option value="<?= $row_proyectos['id_proyecto']; ?>"><?= $row_proyectos['descripcion_proyecto']; ?></option>
                                        <?php } ?>
                                    </select>
	                        	</div>

	                        	
	                    	</div>
	                    </div>

	                    <div class="form-group">
                			<div class="row">
	                        	<div class="col-12 col-sm-3">
		                            <label for="weight_item">* WEIGHT:</label>
									<input type='text' name='weight_item' class='form-control' style="text-transform:uppercase;" id="weight_item" required />
	                        	</div>
                				<div class="col-12 col-sm-3">
		                            <label for="inventory_item">* INVENTORY:</label>
									<input type='text' name='inventory_item' class='form-control' style="text-transform:uppercase;" id="inventory_item" required />
	                        	</div>
	                    	</div>
	                    </div>
	                 
                        <button type="submit" class="btn btn-primary">
							<span class="spinner-border spinner-border-sm"></span>
							ADD ITEM
						</button>
						<button type="reset" class="btn btn-outline-dark" role='link' onclick="history.back();" type='reset'><img src='../../img/atras.png' width=27 height=27> BACK
						</button>
	                </form>
	            </div>
        	</div>
   		</div>

    	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
   
	</body>
</html>