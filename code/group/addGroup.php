<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    $descripcion_grupo = $_POST['descripcion_grupo'];
    $sql_insert_grupo = "INSERT INTO grupos (descripcion_grupo) VALUES ('$descripcion_grupo')";
    if ($mysqli->query($sql_insert_grupo)) {
        echo "<script>
            alert('Insert successful');
            window.location.href = 'seeGroup.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeGroup.php';
          </script>";
    }
  
}