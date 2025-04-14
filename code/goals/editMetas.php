<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario
    $descripcion_meta = $_POST['descripcion_meta'];
    $id_meta = $_POST['id_meta'];
    // Actualizar la meta
    $sql_update_meta = "UPDATE metas SET descripcion_meta='$descripcion_meta' WHERE id_meta='$id_meta'";
    
    if ($mysqli->query($sql_update_meta)) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeGoals.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeGoals.php';
          </script>";
    }

}