<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {    $descripcion_accion = $_POST['descripcion_accion'];
    $id_actividad = $_POST['id_actividad'];
    $id_accion = $_POST['id_accion'];
    // Actualizar  accion
    $sql_update_accion = "UPDATE acciones SET descripcion_accion=?, id_actividad=? WHERE id_accion=?";
    $stmt = $mysqli->prepare($sql_update_accion);
    $stmt->bind_param("sii", $descripcion_accion, $id_actividad, $id_accion);
    
    //ejecutar consulta
    if ($stmt->execute()) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeActions.php';
          </script>";
    } else {
        echo "<script>
            alert('Error: " . $mysqli->error . "');
            window.location.href = 'seeActions.php';
          </script>";
    }
    
    $stmt->close();

    

}