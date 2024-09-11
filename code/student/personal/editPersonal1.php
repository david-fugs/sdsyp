<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../../index.php");
}

header("Content-Type: text/html;charset=utf-8");
$usuario      = $_SESSION['usuario'];
$nombre       = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$cod_dane_ie  = $_SESSION['cod_dane_ie'];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FICHA</title>
    <link href="../../../css/bootstrap.min.css" rel="stylesheet">
    <style>
        .responsive {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <?php
    include("../../../conexion.php");
    date_default_timezone_set("America/Bogota");
    $mysqli->set_charset('utf8');
    if (isset($_POST)) {
        $fecha_dig_personal                  = $_POST['fecha_dig_personal'];
        $mun_dig_personal                   = $_POST['mun_dig_personal'];
        $nombre_encuestador_personal         = $_POST['nombre_encuestador_personal'];
        $rol_encuestador_personal           = $_POST['rol_encuestador_personal'];
        $num_doc_est                         = $_POST['num_doc_est'];
        $nom_ape_est                        = $_POST['nom_ape_est'];
        $normas_personal                    = $_POST['normas_personal'];
        $acata_personal                      = $_POST['acata_personal'];
        $interactua_personal                = $_POST['interactua_personal'];
        $cuidado_personal                   = $_POST['cuidado_personal'];
        $observacion_personal               = $_POST['observacion_personal'];
        $id_personal                       = $_POST['id_registro'];
        $fechaedicion_personal               = date("Y-m-d H:i:s");

     
// Construir la consulta SQL de UPDATE
$sql = "UPDATE personal SET
fecha_dig_personal = '$fecha_dig_personal',
mun_dig_personal = '$mun_dig_personal',
nombre_encuestador_personal = '$nombre_encuestador_personal',
rol_encuestador_personal = '$rol_encuestador_personal',
num_doc_est = '$num_doc_est',
nom_ape_est = '$nom_ape_est',
normas_personal = '$normas_personal',
acata_personal = '$acata_personal',
interactua_personal = '$interactua_personal',
cuidado_personal = '$cuidado_personal',
observacion_personal = '$observacion_personal',
fechaedicion_personal = '$fechaedicion_personal'
WHERE id_personal = '$id_personal'";

      
if ($mysqli->query($sql) === TRUE) {
    // Si la consulta se ejecuta correctamente
    echo "
<!DOCTYPE html>
    <html lang='es'>
        <head>
            <meta charset='utf-8' />
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <meta http-equiv='X-UA-Compatible' content='ie=edge'>
            <link href='https://fonts.googleapis.com/css?family=Lobster' rel='stylesheet'>
            <link href='https://fonts.googleapis.com/css?family=Orbitron' rel='stylesheet'>
            <link rel='stylesheet' href='../../../css/bootstrap.min.css'>
            <link href='../../../fontawesome/css/all.css' rel='stylesheet'>
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
               <img src='../../../img/logo_educacion.png' width=600 height=121 class='responsive'>
            <div class='container'>
                <br />
           ";
    echo "  <h3><b><i class='fas fa-users'></i> SE ACTUALIZO DE FORMA EXITOSA EL REGISTRO</b></h3><br />";
    echo "    
            <p align='center'><a href='../../../access.php'><img src='../../../img/atras.png' width=96 height=96></a></p>
            </div>
            </center>
        </body>
    </html>
";
} else {
    //  error 
    echo "<div class='alert alert-danger' role='alert'>";
    echo "Error al actualizar el registro: " . $mysqli->error . "<br>";
    echo "Consulta ejecutada: " . $sql . "<br>";  // Muestra la consulta SQL
    echo "</div>";
}
}

?>
</body>

</html>