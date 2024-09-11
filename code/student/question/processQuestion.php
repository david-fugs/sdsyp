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

        $fecha_dig_preguntas                  = $_POST['fecha_dig_preguntas'];
        $mun_dig_preguntas                   = $_POST['mun_dig_preguntas'];
        $nombre_encuestador_preguntas         = $_POST['nombre_encuestador_preguntas'];
        $rol_encuestador_preguntas           = $_POST['rol_encuestador_preguntas'];
        $num_doc_est                         = $_POST['num_doc_est'];
        $nom_ape_est                        = $_POST['nom_ape_est'];
        $bueno_preguntas                   = $_POST['bueno_preguntas'];
        $grande_preguntas                      = $_POST['grande_preguntas'];
        $gustar_preguntas                = $_POST['gustar_preguntas'];  
        $recomendacion_preguntas           = $_POST['recomendacion_preguntas'];
        $consentimiento_preguntas           = $_POST['consentimiento_preguntas'];

        // Construir la consulta SQL para insertar los datos en la tabla preguntas
$sql = "INSERT INTO preguntas (
    fecha_dig_preguntas,
    mun_dig_preguntas,
    nombre_encuestador_preguntas,
    rol_encuestador_preguntas,
    num_doc_est,
    nom_ape_est,
    bueno_preguntas,
    grande_preguntas,
    gustar_preguntas,
    consentimiento_preguntas,
    recomendacion_preguntas,
    estado_preguntas
) VALUES (
    '$fecha_dig_preguntas',
    '$mun_dig_preguntas',
    '$nombre_encuestador_preguntas',
    '$rol_encuestador_preguntas',
    '$num_doc_est',
    '$nom_ape_est',
    '$bueno_preguntas',
    '$grande_preguntas',
    '$gustar_preguntas',
    '$consentimiento_preguntas',
    '$recomendacion_preguntas',
    '1'
)";
     $resultado = $mysqli->query($sql);

     if ($resultado) {
         echo "Datos insertados correctamente.";
     } else {
         echo "Error: " . $mysqli->error;
     }
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
                     <br />";
     echo "  <h3><b><i class='fas fa-users'></i> SE CREO DE FORMA EXITOSA EL REGISTRO</b></h3><br />";


     echo "    
                 <p align='center'><a href='../../../access.php'><img src='../../../img/atras.png' width=96 height=96></a></p>
                 </div>
                 </center>
             </body>
         </html>
 ";
 }

 ?>

</body>

</html>
       
