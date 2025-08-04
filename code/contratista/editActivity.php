<?php
include("../../conexion.php");
session_start();
print_r($_POST);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario
    $descripcion_actividad = $_POST['descripcion_actividad'];
    $id_actividad = $_POST['id_actividad'];
    // Actualizar la actividad
    $sql_update_actividad = "UPDATE actividad_contratista SET descripcion_actividad='$descripcion_actividad' WHERE id_actividad_contratista='$id_actividad'";

    if ($mysqli->query($sql_update_actividad)) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeActivities.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . addslashes($mysqli->error) . "');
            window.location.href = 'seeActivities.php';
          </script>";
    }

}