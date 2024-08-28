<?php
    session_start();
     
    if(!isset($_SESSION['id'])){
        header("Location: ../../index.php");
    }
    
    $usuario      = $_SESSION['usuario'];
    $nombre       = $_SESSION['nombre'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    $cod_dane_ie  = $_SESSION['cod_dane_ie'];
?>

<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>FICHA</title>
    <link rel="stylesheet" href="../student/css/styles.css">
    <link rel="stylesheet" type="text/css" href="../student/css/estilos2024.css">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm">
    <script src="https://kit.fontawesome.com/fed2435e21.js" crossorigin="anonymous"></script>

    <style>
        .responsive {
            max-width: 100%;
            height: auto;
        }

        .selector-for-some-widget {
            box-sizing: content-box;
        }
    </style>
    <script>
        function confirmarEliminacion(id) {
            if (confirm('¿Está seguro que desea ELIMINAR el estudiante de la lista? Por favor verifique antes de aceptar el procedimiento')) {
                window.location.href = 'deleteusers.php?id=' + id;
            }
        }
    </script>
</head>
<body>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"></script>

<center>
    <img src='../../img/logo_educacion.png' width=600 height=121 class='responsive'>
</center>

<section class="principal">

    <div style="border-radius: 9px 9px 9px 9px; -moz-border-radius: 9px 9px 9px 9px; -webkit-border-radius: 9px 9px 9px 9px; border: 4px solid #FFFFFF;" align="center">

        <div align="center">
            <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em"><b><i class="fa-solid fa-user-pen"></i> GESTIÓN Y ADMINISTRACIÓN DE USUARIOS</b></h1>
        </div>

    </div>

    <div class="flex">
        <div class="box">
            <form action="showusers.php" method="get" class="form">
                <input name="usuario" type="text" placeholder="Ingrese el usuario">
                <input name="nombre" type="text" placeholder="Nombre(s) y/o apellido(s)">
 
                <input value="Realizar Busqueda" type="submit">
            </form>
        </div>
    </div>
    <br/><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="Regresar" /></a>

<?php

date_default_timezone_set("America/Bogota");
include("../../conexion.php");
require_once("../../zebra.php");
	@$usuario = ($_GET['usuario']);
	@$nombre = ($_GET['nombre']);

	$query = "SELECT * FROM `usuarios` INNER JOIN `ie` ON usuarios.cod_dane_ie=ie.cod_dane_ie WHERE (usuario LIKE '%".$usuario."%') AND (nombre LIKE '%".$nombre."%') AND ie.cod_dane_ie=$cod_dane_ie AND usuarios.estado_usu = 1 ORDER BY usuarios.id ASC";
	$res = $mysqli->query($query);
	$num_registros = mysqli_num_rows($res);
	$resul_x_pagina = 50;

	if ($res) {

    $paginacion = new Zebra_Pagination();
    $paginacion->records($num_registros);
    $paginacion->records_per_page($resul_x_pagina);

    $consulta = "SELECT * FROM `usuarios` INNER JOIN `ie` ON usuarios.cod_dane_ie=ie.cod_dane_ie WHERE (usuario LIKE '%".$usuario."%') AND (nombre LIKE '%".$nombre."%') AND ie.cod_dane_ie=$cod_dane_ie AND usuarios.estado_usu = 1 ORDER BY usuarios.id ASC LIMIT " .(($paginacion->get_page() - 1) * $resul_x_pagina). "," .$resul_x_pagina;
	$result = $mysqli->query($consulta);

	if ($result) {
        echo '<br>';
        $paginacion->render();
        
		echo "<section class='content'>
				<div class='card-body'>
	        		<div class='table-responsive'>
			        	<table>
			            	<thead>
			                	<tr>
									<th>No.</th>
									<th>USUARIO</th>
									<th>NOMBRE</th>
									<th>TIPO USUARIO</th>
					        		<th>EDITAR</th>
					        		<th>ELIMINAR</th>
					    		</tr>
					  		</thead>
	            			<tbody>";

	$i = 1;
	while($row = mysqli_fetch_array($result))
	{

		echo '
				<tr>
					<td data-label="No." >'.($i + (($paginacion->get_page() - 1) * $resul_x_pagina)).'</td>
					<td data-label="USUARIO">'.$row['usuario'].'</td>
					<td data-label="NOMBRE">'.utf8_encode($row['nombre']).'</td>
					<td data-label="TIPO USUARIO">
						<select class="form-control" name="tipo_usuario" disabled >
                        	<option value="">SELECCIONE:</option>   
                            <option value=1 '; if($row['tipo_usuario']==1){echo 'selected';} echo '>RECTOR</option>
                            <option value=2 '; if($row['tipo_usuario']==2){echo 'selected';} echo '>SIMAT</option>
                            <option value=3 '; if($row['tipo_usuario']==3){echo 'selected';} echo '>DOCENTE</option>
                            <option value=4 '; if($row['tipo_usuario']==4){echo 'selected';} echo '>DOCENTE DIRECTIVO</option>
                            <option value=5 '; if($row['tipo_usuario']==5){echo 'selected';} echo '>DOCENTE ORIENTADOR</option>
                            <option value=6 '; if($row['tipo_usuario']==6){echo 'selected';} echo '>ADMINISTRATIVO</option>
                            <option value=7 '; if($row['tipo_usuario']==7){echo 'selected';} echo '>SIN ACCESO</option>
                        </select>
					</td>
					<td data-label="EDITAR"><a href="editusers.php?id='.$row['id'].'"><img src="../../img/editar.png" width=28 height=28></a></td>
					<td data-label="ELIMINAR"><a href="#" onclick="confirmarEliminacion('.$row['id'].')"><img src="../../img/delete1.png" width=28 height=28></a></td>
				</tr>';
            $i++;
        }

        echo '</tbody></table></div></div></section>';

    } else {
        echo "Error en la consulta: " . $mysqli->error;
    }
} else {
    echo "Error en la consulta: " . $mysqli->error;
}
?>

			<div class="share-container">
	            <!-- Go to www.addthis.com/dashboard to customize your tools -->
	            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4ecc1a47193e29e4" async="async"></script>
	            <!-- Go to www.addthis.com/dashboard to customize your tools -->
	            <div class="addthis_sharing_toolbox"></div>
	        </div>
			<center>
			<br/><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="Regresar" /></a>
			</center>

			</div>
		</div>
		</section>
		<script src="../student/js/app.js"></script>
		<script src="https://www.jose-aguilar.com/scripts/fontawesome/js/all.min.js" data-auto-replace-svg="nest"></script>

	</body>
</html>