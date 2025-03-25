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
        if (isset($_POST['btn-update'])) {
            $num_doc_est                        =   $_POST['num_doc_est'];
            $fecha_dig_prePostnatales           =   date('Y-m-d h:i:s');
            $mun_dig_prePostnatales             =   $_POST['mun_dig_prePostnatales'];
            $nombre_encuestador_prePostnatales  =   mb_strtoupper($_POST['nombre_encuestador_prePostnatales']);
            $rol_encuestador_prePostnatales     =   mb_strtoupper($_POST['rol_encuestador_prePostnatales']);
            $edad_madre_prePostnatales          =   $_POST['edad_madre_prePostnatales'];
            $gestacion_meses_prePostnatales     =   $_POST['gestacion_meses_prePostnatales'];
            $embarazo_mama_prePostnatales       =   $_POST['embarazo_mama_prePostnatales'];
            $lactancia_mama_prePostnatales      =   $_POST['lactancia_mama_prePostnatales'];
            $gateo_prePostnatales               =   $_POST['gateo_prePostnatales'];
            $camino_prePostnatales              =   $_POST['camino_prePostnatales'];
            $fecha_alta_prePostnatales          =   date('Y-m-d h:i:s');
            $fecha_edit_prePostnatales          =   ('0000-00-00 00:00:00');
            $id_usu                             =   $_SESSION['id'];
            // Validar conexión
            if (!$mysqli) {
                die("Error de conexión: " . mysqli_connect_error());
            }

            $sql = "INSERT INTO prePostnatales (num_doc_est, fecha_dig_prePostnatales, mun_dig_prePostnatales, nombre_encuestador_prePostnatales, rol_encuestador_prePostnatales, edad_madre_prePostnatales, gestacion_meses_prePostnatales, embarazo_mama_prePostnatales, lactancia_mama_prePostnatales, gateo_prePostnatales, camino_prePostnatales, fecha_alta_prePostnatales, fecha_edit_prePostnatales, id_usu) values ('$num_doc_est','$fecha_dig_prePostnatales', '$mun_dig_prePostnatales', '$nombre_encuestador_prePostnatales', '$rol_encuestador_prePostnatales', '$edad_madre_prePostnatales','$gestacion_meses_prePostnatales','$embarazo_mama_prePostnatales','$lactancia_mama_prePostnatales', '$gateo_prePostnatales', '$camino_prePostnatales', '$fecha_alta_prePostnatales', '$fecha_edit_prePostnatales','$id_usu')";
            // Ejecutar la consulta
            if ($mysqli->query($sql) === TRUE) {
                echo "Registro insertado correctamente.";
            } else {
                echo "Error al insertar: " . $mysqli->error . "<br>Consulta: " . $sql;
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
                                <h3><b><i class='fas fa-users'></i> SE ACTUALIZÓ DE FORMA EXITOSA EL REGISTRO</b></h3><br />
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