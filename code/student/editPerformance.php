<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
}

header("Content-Type: text/html;charset=utf-8");

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
    <link href="../../fontawesome/css/all.css" rel="stylesheet">
    <script type="text/javascript" src="../../js/jquery.min.js"></script>
    <!-- Using Select2 from a CDN-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        
function Si1No2($value)
{
    if ($value == 1) {
        return "SI";
    } else {
        return "NO";
    }
}
    </script>

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
    $time = time();
    $num_doc_est  = $_GET['num_doc_est'];
    $id_registro        = $_GET['id_desempeno'];
    if (isset($_GET['num_doc_est'])) {
        $sql = mysqli_query($mysqli, "SELECT * FROM estudiantes INNER JOIN desempeno ON estudiantes.num_doc_est=desempeno.num_doc_est WHERE estudiantes.num_doc_est = '$num_doc_est'");
        $row = mysqli_fetch_array($sql);
        //$row = $result->fetch_assoc();
        $fec_nac_est = $row['fec_nac_est'];

        // Calcula la edad
        $fecha_actual = new DateTime();
        $fec_nac_est = new DateTime($fec_nac_est);
        $edad = $fecha_actual->diff($fec_nac_est)->y;


        //formulario de actualizacion
        $sql_formulario =  mysqli_query($mysqli, "SELECT * FROM desempeno WHERE id_desempeno = '$id_registro' ");
        $res_formulario =  mysqli_fetch_array($sql_formulario);
        print_r($res_formulario);
    }
    ?>

    <div class="container">
        <center>
            <img src='../../img/logo_educacion.png' width=600 height=121 class='responsive'>
        </center>

        <h1><b><img src="../../img/baby.png" width=35 height=35> ACTUALIZAR INFORMACIÓN DESEMPEÑO DEL ESTUDIANTE <img src="../../img/baby.png" width=35 height=35></b></h1>
        <p><i><b>
                    <font size=3 color=#c68615>* Datos obligatorios</i></b></font>
        </p>

        <form action='editEducation1.php' method="POST">

            <hr style="border: 2px solid #16087B; border-radius: 2px;">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="fecha_dig_educacion">FECHA DILIGENC.</label>
                        <input type='text' name='fecha_dig_educacion' id="fecha_dig_educacion" class='form-control' value='<?php echo date("d-m-Y h:i", $time); ?>' readonly />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="mun_dig_educacion">* MUNICIPIO DILIGENCIAMIENTO:</label>
                        <select name='mun_dig_educacion' class='form-control' id='selectMunicipio' required />
                        <option value=''></option>
                        <?php
                        header('Content-Type: text/html;charset=utf-8');
                        $consulta = 'SELECT * FROM municipios';
                        $res = mysqli_query($mysqli, $consulta);
                        $num_reg = mysqli_num_rows($res);
                        while ($row1 = $res->fetch_array()) {
                        ?>
                            <option value='<?php echo $row1['nombre_mun']; ?>' <?php if ($row['mun_dig_educacion'] == $row1['nombre_mun']) {
                                                                                    echo 'selected';
                                                                                } ?>>
                                <?php echo $row1['nombre_mun']; ?>
                            </option>
                        <?php
                        }
                        ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="nombre_encuestador_educacion">NOMBRE DEL ENCUESTADOR:</label>
                        <input type='text' name='nombre_encuestador_educacion' class='form-control' id="nombre_encuestador_educacion" value='<?php echo $nombre; ?>' readonly />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="rol_encuestador_educacion">TIPO DE ACCESO:</label>
                        <select class="form-control" name="rol_encuestador_educacion" readonly />
                        <option value="">SELECCIONE:</option>
                        <option value="RECTOR" <?php if ($tipo_usuario == 1) {
                                                    echo 'selected';
                                                } ?>>RECTOR</option>
                        <option value="SIMAT" <?php if ($tipo_usuario == 2) {
                                                    echo 'selected';
                                                } ?>>SIMAT</option>
                        <option value="DOCENTE" <?php if ($tipo_usuario == 3) {
                                                    echo 'selected';
                                                } ?>>DOCENTE</option>
                        <option value="DOCENTE DIRECTIVO" <?php if ($tipo_usuario == 4) {
                                                                echo 'selected';
                                                            } ?>>DOCENTE DIRECTIVO</option>
                        <option value="DOCENTE ORIENTADOR" <?php if ($tipo_usuario == 5) {
                                                                echo 'selected';
                                                            } ?>>DOCENTE ORIENTADOR</option>
                        <option value="ADMINISTRATIVO" <?php if ($tipo_usuario == 6) {
                                                            echo 'selected';
                                                        } ?>>ADMINISTRATIVO</option>
                        <option value="SIN ACCESO" <?php if ($tipo_usuario == 7) {
                                                        echo 'selected';
                                                    } ?>>SIN ACCESO</option>
                        </select>
                    </div>
                </div>
            </div>
            <hr style="border: 2px solid #16087B; border-radius: 2px;">

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="num_doc_est">No. DOCUMENTO ESTUDIANTE:</label>
                        <input type='number' name='num_doc_est' class='form-control' id="num_doc_est" value='<?php echo $row['num_doc_est']; ?>' readonly />
                    </div>


                    <div class="col-12 col-sm-9">
                        <label for="nom_ape_est">NOMBRES Y APELLIDOS COMPLETOS DEL ESTUDIANTE:</label>
                        <input type='text' name='nom_ape_est' id="nom_ape_est" class='form-control' value='<?php echo utf8_encode($res_formulario['nom_ape_est']); ?>' readonly />
                    </div>
                </div>
            </div>


            <div class="form-group mt-4">
                <div class="row">
                    <div class="col-12">
                        <label for="relaciones">* CALIFIQUE AL ESTUDIANTE ENN CUATRO DIMENSIONES (Comprensión, aplicación, Participación y Consistencia) CONFORME AL DESEMPEÑO EN EL AREA </label>
                        <table class="table table-bordered table-info mt-4">
                            <thead>
                                <tr>
                                    <th>Asignatura</th>
                                    <th>Comprensión</th>
                                    <th>Participación</th>
                                    <th>Aplicación</th>
                                    <th>Consistencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ciencias Naturales</td>
                                    <td>
                                        <select  class="form-control" name="comprension_ciencia_desempeno">
                                            <option   value="bajo" <?php 
                                            if ($res_formulario['comprension_ciencia_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_ciencia_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_ciencia_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_ciencia_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>

                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="participacion_ciencia_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['participacion_ciencia_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['participacion_ciencia_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['participacion_ciencia_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                                                                            <option  value="superior" <?php
                                            if ($res_formulario['participacion_ciencia_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>


                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="aplicacion_ciencia_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['aplicacion_ciencia_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['aplicacion_ciencia_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['aplicacion_ciencia_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['aplicacion_ciencia_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>

                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="consistencia_ciencia_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['consistencia_ciencia_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['consistencia_ciencia_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['consistencia_ciencia_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['consistencia_ciencia_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Ciencias Sociales</td>
                                    <td>
                                        <select  class="form-control" name="comprension_sociales_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_sociales_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_sociales_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_sociales_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_sociales_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Educacion Fisica</td>
                                    <td>
                                        <select  class="form-control" name="comprension_edufisica_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_edufisica_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_edufisica_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_edufisica_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_edufisica_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_edufisica_participacion">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_edufisica_participacion'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_edufisica_participacion'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_edufisica_participacion'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_edufisica_participacion'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_edufisica_aplicacion">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_edufisica_aplicacion'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_edufisica_aplicacion'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_edufisica_aplicacion'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_edufisica_aplicacion'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_edufisica_consistencia">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_edufisica_consistencia'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_edufisica_consistencia'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_edufisica_consistencia'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_edufisica_consistencia'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Etica y Valores</td>
                                    <td>
                                        <select  class="form-control" name="comprension_etica_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_etica_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_etica_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select  class="form-control" name="comprension_etica_desempeno">
                                            <option  value="bajo" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'bajo') {
                                                echo 'selected';  }?>>Desempeño Bajo</option>
                                            <option  value="basico" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'basico') {
                                                echo 'selected';  }?>>Desempeño Básico</option>
                                            <option  value="alto" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'alto') {
                                                echo 'selected';  }?>>Desempeño Alto</option>
                                            <option  value="superior" <?php
                                            if ($res_formulario['comprension_etica_desempeno'] == 'superior') {
                                                echo 'selected';  }?>>Desempeño Superior</option>
                                        </select>
                                    </td>
                                </tr>
                                
                                    <tr>
                                        <td>Religion</td>
                                        <td>
                                            <select  class="form-control" name="comprension_religion_desempeno">
                                                <option  value="bajo" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'bajo') {
                                                    echo 'selected';  }?>>Desempeño Bajo</option>
                                                <option  value="basico" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'basico') {
                                                    echo 'selected';  }?>>Desempeño Básico</option>
                                                <option  value="alto" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'alto') {
                                                    echo 'selected';  }?>>Desempeño Alto</option>
                                                <option  value="superior" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'superior') {
                                                    echo 'selected';  }?>>Desempeño Superior</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select  class="form-control" name="comprension_religion_desempeno">
                                                <option  value="bajo" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'bajo') {
                                                    echo 'selected';  }?>>Desempeño Bajo</option>
                                                <option  value="basico" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'basico') {
                                                    echo 'selected';  }?>>Desempeño Básico</option>
                                                <option  value="alto" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'alto') {
                                                    echo 'selected';  }?>>Desempeño Alto</option>
                                                <option  value="superior" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'superior') {
                                                    echo 'selected';  }?>>Desempeño Superior</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select  class="form-control" name="comprension_religion_desempeno">
                                                <option  value="bajo" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'bajo') {
                                                    echo 'selected';  }?>>Desempeño Bajo</option>
                                                <option  value="basico" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'basico') {
                                                    echo 'selected';  }?>>Desempeño Básico</option>
                                                <option  value="alto" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'alto') {
                                                    echo 'selected';  }?>>Desempeño Alto</option>
                                                <option  value="superior" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'superior') {
                                                    echo 'selected';  }?>>Desempeño Superior</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select  class="form-control" name="comprension_religion_desempeno">
                                                <option  value="bajo" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'bajo') {
                                                    echo 'selected';  }?>>Desempeño Bajo</option>
                                                <option  value="basico" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'basico') {
                                                    echo 'selected';  }?>>Desempeño Básico</option>
                                                <option  value="alto" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'alto') {
                                                    echo 'selected';  }?>>Desempeño Alto</option>
                                                <option  value="superior" <?php
                                                if ($res_formulario['comprension_religion_desempeno'] == 'superior') {
                                                    echo 'selected';  }?>>Desempeño Superior</option>
                                            </select>
                                        </td>
                               </tr>
                               
                                 <tr>
                                        <td>Artistica</td>
                                        <td>
                                         <select  class="form-control" name="comprension_artistica_desempeno">
                                                <option  value="bajo" <?php
                                                if ($res_formulario['comprension_artistica_desempeno'] == 'bajo') {
                                                    echo 'selected';  }?>>Desempeño Bajo</option>
                                                <option  value="basico" <?php
                                                if ($res_formulario['comprension_artistica_desempeno'] == 'basico') {
                                                    echo 'selected';  }?>>Desempeño Básico</option>
                                                <option  value="alto" <?php
                                                if ($res_formulario['comprension_artistica_desempeno'] == 'alto') {
                                                    echo 'selected';  }?>>Desempeño Alto</option>
                                                <option  value="superior" <?php
                                                if ($res_formulario['comprension_artistica_desempeno'] == 'superior') {
                                                    echo 'selected';  }?>>Desempeño Superior</option>
                                         </select>
                                        </td>
                                        <td>
                                         <select  class="form-control" name="comprension_artistica_desempeno">
                                              <option  value="bajo">Desempeño Bajo</option>
                                              <option  value="basico">Desempeño Básico</option>
                                              <option  value="alto">Desempeño Alto</option>
                                              <option  value="superior">Desempeño Superior</option>
                                         </select>
                                        </td>
                                        <td>
                                         <select  class="form-control" name="comprension_artistica_desempeno">
                                              <option  value="bajo">Desempeño Bajo</option>
                                              <option  value="basico">Desempeño Básico</option>
                                              <option  value="alto">Desempeño Alto</option>
                                              <option  value="superior">Desempeño Superior</option>
                                         </select>
                                        </td>
                                        <td>
                                         <select  class="form-control" name="comprension_artistica_desempeno">
                                              <option  value="bajo">Desempeño Bajo</option>
                                              <option  value="basico">Desempeño Básico</option>
                                              <option  value="alto">Desempeño Alto</option>
                                              <option  value="superior">Desempeño Superior</option>
                                         </select>
                                        </td>
                                  </tr>
                                  
                                    <tr>
                                            <td>Humanidades</td>
                                            <td>
                                             <select  class="form-control" name="comprension_humanidades_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_humanidades_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_humanidades_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_humanidades_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Matematicas</td>
                                            <td>
                                             <select  class="form-control" name="comprension_matematicas_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_matematicas_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_matematicas_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_matematicas_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                       
                                        <tr>
                                            <td>Fisica</td>
                                            <td>
                                             <select  class="form-control" name="comprension_fisica_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_fisica_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_fisica_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_fisica_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Algebra</td>
                                            <td>
                                             <select  class="form-control" name="comprension_algebra_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_algebra_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_algebra_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_algebra_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Calculo</td>
                                            <td>
                                             <select  class="form-control" name="comprension_calculo_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_calculo_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_calculo_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_calculo_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Ingles</td>
                                            <td>
                                             <select  class="form-control" name="comprension_ingles_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_ingles_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_ingles_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_ingles_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                      
                                        <tr>
                                            <td>Tecnologia e Informatica</td>
                                            <td>
                                             <select  class="form-control" name="comprension_tecno_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_tecno_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_tecno_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_tecno_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                        <!-- emprendimiento -->
                                        <tr>
                                            <td>Emprendimiento</td>
                                            <td>
                                             <select  class="form-control" name="comprension_emprendimiento_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_emprendimiento_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_emprendimiento_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_emprendimiento_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                      
                                        <tr>
                                            <td>Areas Tecnicas</td>
                                            <td>
                                             <select  class="form-control" name="comprension_areastec_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_areastec_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_areastec_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_areastec_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Filosofia</td>
                                            <td>
                                             <select  class="form-control" name="comprension_filosofia_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_filosofia_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_filosofia_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_filosofia_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                                        <!-- ciencias economicas -->
                                        <tr>
                                            <td>Ciencias Economicas</td>
                                            <td>
                                             <select  class="form-control" name="comprension_cienciaseco_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_cienciaseco_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_cienciaseco_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                            <td>
                                             <select  class="form-control" name="comprension_cienciaseco_desempeno">
                                                <option  value="bajo">Desempeño Bajo</option>
                                                <option  value="basico">Desempeño Básico</option>
                                                <option  value="alto">Desempeño Alto</option>
                                                <option  value="superior">Desempeño Superior</option>
                                             </select>
                                            </td>
                                        </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-6 col-sm-6 mt-4">
                        <label for="doble_titu_desempeno">* ESTUDIANTE ESTA VINCULADO A PROGRAMAS DE DOBLE TITULACION: </label>
                        <select class="form-control" name="doble_titu_desempeno" id="doble_titu_desempeno">
                            <option value=""></option>
                            <option value="1">SI</option>
                            <option value="2">NO</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-6 mt-4">
                        <label for="nom_dobletitu_desempeno">* SI LA RESPUESTA FUE SI, ESPECIFIQUE CUAL: </label>
                        <input class="form-control" type="text" name="nom_dobletitu_desempeno">
                    </div>

                </div>
            </div>
            
            <button type="submit" class="btn btn-primary mt-5" name="btn-update">
                <span class="spinner-border spinner-border-sm"></span>
                ACTUALIZAR INFORMACIÓN PRE POSTNATAL
            </button>
            <button type="reset" class="btn btn-outline-dark mt-5" role='link' onclick="history.back();">
                <img src='../../img/atras.png' width=27 height=27> REGRESAR
            </button>
        </form>
    </div>
</body>


</html>