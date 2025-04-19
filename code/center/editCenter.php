<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario

    $descripcion_actividad = $_POST['descripcion_actividad'];
    $id_meta = $_POST['id_meta'];
    $id_actividad = $_POST['id_actividad'];
    // Actualizar la actividad
    $sql_update_actividad = "UPDATE actividades SET descripcion_actividad='$descripcion_actividad', id_meta='$id_meta' WHERE id_actividad='$id_actividad'";
    //ejecutar consulta
    if ($mysqli->query($sql_update_actividad)) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeActivity.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeActivity.php';
          </script>";
    }

    

}