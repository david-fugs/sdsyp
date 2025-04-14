<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $descripcion_accion = $_POST['descripcion_accion'];
    $id_meta = $_POST['id_meta'];
    $id_accion = $_POST['id_accion'];
    // Actualizar  accion
    $sql_update_accion = "UPDATE acciones SET descripcion_accion='$descripcion_accion', id_meta='$id_meta' WHERE id_accion='$id_accion'";
    //ejecutar consulta
    if ($mysqli->query($sql_update_accion)) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeActions.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeActions.php';
          </script>";
    }

    

}