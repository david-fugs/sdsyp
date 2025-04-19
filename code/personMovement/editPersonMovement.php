<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
    $cedula_original = $_POST['cedula_original'];
    $fecha_movimiento = $_POST['fecha_movimiento'];
    $observacion_movimiento = $_POST['observacion_movimiento'];
    $id_movimiento = $_POST['id_movimiento'];
    $id_movimiento_persona = $_POST['id_movimiento_persona'];

    //actualizar movimiento_persona
    $sql_update_movimiento = "UPDATE movimiento_persona SET cedula_persona = '$cedula_persona', fecha_movimiento = '$fecha_movimiento', observacion_movimiento = '$observacion_movimiento' , id_movimiento = '$id_movimiento'  WHERE id_movimiento_persona = '$id_movimiento_persona'";
    //ejecutar consulta
    if ($mysqli->query($sql_update_movimiento)) {

        echo "<script>
                alert('Update successful');
                window.location.href = 'seePersonMovement.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seePersonMovement.php';
          </script>";
}
