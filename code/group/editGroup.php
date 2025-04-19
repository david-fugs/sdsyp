<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario

    $descripcion_grupo = $_POST['descripcion_grupo'];
    $id_grupo = $_POST['id_grupo'];
    // Actualizar la grupo
    $sql_update_grupo = "UPDATE grupos SET descripcion_grupo='$descripcion_grupo' WHERE id_grupo='$id_grupo'";
    //ejecutar consulta
    if ($mysqli->query($sql_update_grupo)) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeGro up.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeGroup.php';
          </script>";
    }

    

}