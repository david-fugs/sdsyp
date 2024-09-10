<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../../index.php");
}

header("Content-Type: text/html;charset=utf-8");
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

    include("../../../conexion.php");
    date_default_timezone_set("America/Bogota");
    $mysqli->set_charset('utf8');
    if (isset($_POST)) {

        // Captura cada valor en una variable con el mismo nombre
        $fecha_dig_preescolar                        = isset($_POST['fecha_dig_preescolar']) ? $_POST['fecha_dig_preescolar'] : '';
        $mun_dig_preescolar                         = isset($_POST['mun_dig_preescolar']) ? $_POST['mun_dig_preescolar'] : '';
        $nombre_encuestador_preescolar               = isset($_POST['nombre_encuestador_preescolar']) ? $_POST['nombre_encuestador_preescolar'] : '';
        $rol_encuestador_preescolar                  = isset($_POST['rol_encuestador_preescolar']) ? $_POST['rol_encuestador_preescolar'] : '';
        $num_doc_est                                = isset($_POST['num_doc_est']) ? $_POST['num_doc_est'] : '';
        $nom_ape_est                                = isset($_POST['nom_ape_est']) ? $_POST['nom_ape_est'] : '';
        $p1_eleccion_preescolar                     = isset($_POST['p1_eleccion_preescolar']) ? $_POST['p1_eleccion_preescolar'] : '';
        $p1_consecuen_preescolar                    = isset($_POST['p1_consecuen_preescolar']) ? $_POST['p1_consecuen_preescolar'] : '';
        $p1_independencia_preescolar                = isset($_POST['p1_independencia_preescolar']) ? $_POST['p1_independencia_preescolar'] : '';
        $p1_artistico_preescolar                      = isset($_POST['p1_artistico_preescolar']) ? $_POST['p1_artistico_preescolar'] : '';
        $p1_lugarvive_preescolar                     = isset($_POST['p1_lugarvive_preescolar']) ? $_POST['p1_lugarvive_preescolar'] : '';
        $p1_rolfamilia_preescolar                   = isset($_POST['p1_rolfamilia_preescolar']) ? $_POST['p1_rolfamilia_preescolar'] : '';
        $p1_escucha_preescolar                        = isset($_POST['p1_escucha_preescolar']) ? $_POST['p1_escucha_preescolar'] : '';
        $p2_juegos_preescolar                          = isset($_POST['p2_juegos_preescolar']) ? $_POST['p2_juegos_preescolar'] : '';
        $p2_palabras_preescolar                         = isset($_POST['p2_palabras_preescolar']) ? $_POST['p2_palabras_preescolar'] : '';
        $p2_crea_preescolar                             = isset($_POST['p2_crea_preescolar']) ? $_POST['p2_crea_preescolar'] : '';
        $p2_leer_preescolar                             = isset($_POST['p2_leer_preescolar']) ? $_POST['p2_leer_preescolar'] : '';
        $p2_escribir_preescolar                         = isset($_POST['p2_escribir_preescolar']) ? $_POST['p2_escribir_preescolar'] : '';
        $p3_concentracion_preescolar                   = isset($_POST['p3_concentracion_preescolar']) ? $_POST['p3_concentracion_preescolar'] : '';
        $p3_explica_preescolar                       = isset($_POST['p3_explica_preescolar']) ? $_POST['p3_explica_preescolar'] : '';
        $p3_participa_preescolar                      = isset($_POST['p3_participa_preescolar']) ? $_POST['p3_participa_preescolar'] : '';
        $p3_identifica_preescolar                     = isset($_POST['p3_identifica_preescolar']) ? $_POST['p3_identifica_preescolar'] : '';
        $p3_acontecimientos_preescolar                  = isset($_POST['p3_acontecimientos_preescolar']) ? $_POST['p3_acontecimientos_preescolar'] : '';
        $p3_cuerpo_preescolar                           = isset($_POST['p3_cuerpo_preescolar']) ? $_POST['p3_cuerpo_preescolar'] : '';
        $p3_secuencia_preescolar                          = isset($_POST['p3_secuencia_preescolar']) ? $_POST['p3_secuencia_preescolar'] : '';
        $p3_atributo_preescolar                          = isset($_POST['p3_atributo_preescolar']) ? $_POST['p3_atributo_preescolar'] : '';
        $p3_enumeracion_preescolar                         = isset($_POST['p3_enumeracion_preescolar']) ? $_POST['p3_enumeracion_preescolar'] : '';
        $fechaedicion_preescolar                      =   date('Y-m-d H:i:s');
        $id_registro                                 = isset($_POST['id_registro']) ? $_POST['id_registro'] : '';


        $sql = "UPDATE preescolar SET 
    fecha_dig_preescolar = '$fecha_dig_preescolar',
    mun_dig_preescolar = '$mun_dig_preescolar',
    nombre_encuestador_preescolar = '$nombre_encuestador_preescolar',
    rol_encuestador_preescolar = '$rol_encuestador_preescolar',
    num_doc_est = '$num_doc_est',
    nom_ape_est = '$nom_ape_est',
    p1_eleccion_preescolar = '$p1_eleccion_preescolar',
    p1_consecuen_preescolar = '$p1_consecuen_preescolar',
    p1_independencia_preescolar = '$p1_independencia_preescolar',
    p1_artistico_preescolar = '$p1_artistico_preescolar',
    p1_lugarvive_preescolar = '$p1_lugarvive_preescolar',
    p1_rolfamilia_preescolar = '$p1_rolfamilia_preescolar',
    p1_escucha_preescolar = '$p1_escucha_preescolar',
    p2_juegos_preescolar = '$p2_juegos_preescolar',
    p2_palabras_preescolar = '$p2_palabras_preescolar',
    p2_crea_preescolar = '$p2_crea_preescolar',
    p2_leer_preescolar = '$p2_leer_preescolar',
    p2_escribir_preescolar = '$p2_escribir_preescolar',
    p3_concentracion_preescolar = '$p3_concentracion_preescolar',
    p3_explica_preescolar = '$p3_explica_preescolar',
    p3_participa_preescolar = '$p3_participa_preescolar',
    p3_identifica_preescolar = '$p3_identifica_preescolar',
    p3_acontecimientos_preescolar = '$p3_acontecimientos_preescolar',
    p3_cuerpo_preescolar = '$p3_cuerpo_preescolar',
    p3_secuencia_preescolar = '$p3_secuencia_preescolar',
    p3_atributo_preescolar = '$p3_atributo_preescolar',
    p3_enumeracion_preescolar = '$p3_enumeracion_preescolar',
    fechaedicion_preescolar = '$fechaedicion_preescolar'
    WHERE id_preescolar = '$id_registro'";


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