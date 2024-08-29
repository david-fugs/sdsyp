<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
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

    if (isset($_POST)) {
        $fecha_dig_educacion               = $_POST['fecha_dig_educacion'];
        $mun_dig_educacion                 = $_POST['mun_dig_educacion'];
        $nombre_encuestador_educacion      = $_POST['nombre_encuestador_educacion'];
        $rol_encuestador_educacion         = $_POST['rol_encuestador_educacion'];
        $num_doc_est                       = $_POST['num_doc_est'];
        $nom_ape_est                       = $_POST['nom_ape_est'];
        $vinculacion_inst_educacion        = $_POST['vinculacion_inst_educacion'];
        $nom_inst_educacion                = $_POST['nom_inst_educacion'];
        $modalidad_inst_educacion          = $_POST['modalidad_inst_educacion'];
        $complementario_educacion          = $_POST['complementario_educacion'];
        $program_complement_educacion      = $_POST['program_complement_educacion'];
        $repetir_year_educacion            = $_POST['repetir_year_educacion'];
        $anios_repet_educacion             = $_POST['anios_repet_educacion'];
        $talento_educacion                 = $_POST['talento_educacion'];
        $talento_descrip_educacion          = $_POST['talento_descrip_educacion'];
        $vinculacion_club_educacion        = $_POST['vinculacion_club_educacion'];
        $club_descrip_educacion            = $_POST['club_descrip_educacion'];
        $id_usu                             =   $_SESSION['id'];
        //convertir array a string
        $anios_repet_educacion = implode(',', $_POST['anios_repet_educacion']);


        //lo que trae $$anios_repet_educacion     es un array ya que vienen varios valores y loq ue necesito es que en la insercion se metan todos digamos si viene [1,2,3] ingrese en la base de datos 1,2,3
        $sql = "INSERT INTO educacion 
    (num_doc_est, fecha_dig_educacion, mun_dig_educacion, nombre_encuestador_educacion, rol_encuestador_educacion, nom_ape_est, vinculacion_inst_educacion, nom_inst_educacion, modalidad_inst_educacion, complementario_educacion, program_complement_educacion, repetir_year_educacion, anios_repet_educacion, talento_educacion, talento_descrip_educacion, vinculacion_club_educacion, club_descrip_educacion,id_usu)
  
    values ('$num_doc_est','$fecha_dig_educacion', '$mun_dig_educacion', '$nombre_encuestador_educacion', '$rol_encuestador_educacion', '$nom_ape_est', '$vinculacion_inst_educacion', '$nom_inst_educacion', '$modalidad_inst_educacion', '$complementario_educacion', '$program_complement_educacion', '$repetir_year_educacion', '$anios_repet_educacion', '$talento_educacion', '$talento_descrip_educacion', '$vinculacion_club_educacion', '$club_descrip_educacion','$id_usu')";
        $resultado = $mysqli->query($sql);

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
                                ";
        if ($mysqli->query($sql) === TRUE) {
            echo "  <h3><b><i class='fas fa-users'></i> SE CREO DE FORMA EXITOSA EL REGISTRO</b></h3><br />";
        } else {
            echo "Error al insertar el registro: ";
        }
        echo "
                                <p align='center'><a href='../../access.php'><img src='../../img/atras.png' width=96 height=96></a></p>
                            </div>
                            </center>
                        </body>
                    </html>
    ";
    }
    ?>

</body>

</html>