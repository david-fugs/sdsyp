<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario

    $descripcion_movimiento = $_POST['descripcion_movimiento'];
    $id_movimiento = $_POST['id_movimiento'];
    // Actualizar la movimiento
    $sql_update_movimiento = "UPDATE movimientos SET descripcion_movimiento='$descripcion_movimiento' WHERE id_movimiento='$id_movimiento'";
    //ejecutar consulta
    if ($mysqli->query($sql_update_movimiento)) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeMovement.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeMovement.php';
          </script>";
    }

    

}