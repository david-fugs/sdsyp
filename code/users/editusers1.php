<?php
    
    session_start();
    
    if(!isset($_SESSION['id'])){
        header("Location: ../../index.php");
    }
    
    header("Content-Type: text/html;charset=utf-8");
    $nombre         = $_SESSION['nombre'];
    $tipo_usuario   = $_SESSION['tipo_usuario'];
    $cod_dane_ie    = $_SESSION['cod_dane_ie'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FICHA</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <style>
        .responsive {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

   	<?php
        include("../../conexion.php");
        date_default_timezone_set("America/Bogota");
        $mysqli->set_charset('utf8');
	    if(isset($_POST['btn-update']))
        {
            $id                 =   $_POST['id'];
            $usuario            =   $_POST['usuario'];
            $nombre             =   $_POST['nombre'];
            $tipo_usuario       =   $_POST['tipo_usuario'];
            $cod_dane_ie        =   $_POST['cod_dane_ie'];
           
            $update = "UPDATE usuarios SET usuario='".$usuario."', nombre='".$nombre."', tipo_usuario='".$tipo_usuario."', cod_dane_ie='".$cod_dane_ie."' WHERE id='".$id."'";

            $up = mysqli_query($mysqli, $update);


            echo "
                <!DOCTYPE html>
                    <html lang='es'>
                        <head>
                            <meta charset='utf-8' />
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <meta http-equiv='X-UA-Compatible' content='ie=edge'>
                            <link href='https://fonts.googleapis.com/css?family=Lobster' rel='stylesheet'>
                            <link href='https://fonts.googleapis.com/css?family=Orbitron' rel='stylesheet'>
                            <link rel='stylesheet' href='../../css/bootstrap.min.css'>
                            <link href='../../fontawesome/css/all.css' rel='stylesheet'>
                            <title>FICHA</title>
                            <style>
                                .responsive {
                                    max-width: 100%;
                                    height: auto;
                                }
                            </style>
                        </head>
                        <body>
                            <center>
                               <img src='../../img/logo_educacion.png' width=600 height=121 class='responsive'>
                            <div class='container'>
                                <br />
                                <h3><b><i class='fa-solid fa-user-pen'></i> SE ACTUALIZÓ DE FORMA EXITOSA EL REGISTRO</b></h3><br />
                                <p align='center'><a href='showusers.php'><img src='../../img/atras.png' width=96 height=96></a></p>
                            </div>
                            </center>
                        </body>
                    </html>
        ";
        }
    ?>

</body>
</html>