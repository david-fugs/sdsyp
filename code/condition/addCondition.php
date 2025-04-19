<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    $descripcion_condicion = $_POST['descripcion_condicion'];
    $sql_insert_condicion = "INSERT INTO condiciones_componente (descripcion_condicion) VALUES ('$descripcion_condicion')";
    if ($mysqli->query($sql_insert_condicion)) {
        echo "<script>
            alert('Insert successful');
            window.location.href = 'seeCondition.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeCondition.php';
          </script>";
    }
  
}