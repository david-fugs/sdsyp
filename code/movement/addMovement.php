<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    $descripcion_movimiento = $_POST['descripcion_movimiento'];

    $sql_insert_movimiento = "INSERT INTO movimientos (descripcion_movimiento) VALUES ('$descripcion_movimiento')";
    if ($mysqli->query($sql_insert_movimiento)) {
        echo "<script>
            alert('Insert successful');
            window.location.href = 'seeMovement.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeMovement.php';
          </script>";
    }
  
}