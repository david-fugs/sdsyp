<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
    $movimiento = $_POST['movimiento'];
    $fecha_movimiento = $_POST['fecha_movimiento'];
    $observacion_movimiento = $_POST['observacion_movimiento'];

    $sql_insert_movimiento = "INSERT INTO movimiento_persona (cedula_persona, id_movimiento, fecha_movimiento, observacion_movimiento)
    VALUES ('$cedula_persona', '$movimiento', '$fecha_movimiento', '$observacion_movimiento')";

    // Ejecutar consulta
    if ($mysqli->query($sql_insert_movimiento)) {
        echo "<script>
            alert('Insert successful');
            window.location.href = 'seePersonMovement.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seePersonMovement.php';
          </script>";
    }
} else {
    echo "<script>
            alert('Method not valid');
            window.location.href = 'seePersonMovement.php';
          </script>";
}
