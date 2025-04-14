<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    $descripcion_actividad = $_POST['descripcion_actividad'];
    $id_meta = $_POST['id_meta'];

    $sql_insert_actividad = "INSERT INTO actividades (descripcion_actividad, id_meta) VALUES ('$descripcion_actividad', '$id_meta')";
    if ($mysqli->query($sql_insert_actividad)) {
        echo "<script>
            alert('Insert successful');
            window.location.href = 'seeActivity.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeActivity.php';
          </script>";
    }
  
}