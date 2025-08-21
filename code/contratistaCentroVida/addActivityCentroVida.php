<?php
include("../../conexion.php");
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $descripcion_actividad = $_POST['descripcion_actividad'];
    $sql_insert_actividad = "INSERT INTO actividad_centro_vida (descripcion_actividad) VALUES ('$descripcion_actividad')";
    if ($mysqli->query($sql_insert_actividad)) {
        echo "<script>
            alert('Actividad centro vida agregada correctamente');
            window.location.href = 'seeActivitiesCentroVida.php';
          </script>";
    } else {
        echo "<script>
            alert('Error: " . $mysqli->error . "');
            window.location.href = 'seeActivitiesCentroVida.php';
          </script>";
    }
}
?>
