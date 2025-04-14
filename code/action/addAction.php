<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    $descripcion_accion = $_POST['descripcion_accion'];
    $id_meta = $_POST['id_meta'];
    //insertar en acciones
    $sql_insert_accion = "INSERT INTO acciones (descripcion_accion, id_meta) VALUES ('$descripcion_accion', '$id_meta')";
    if ($mysqli->query($sql_insert_accion)) {
        echo "<script>
            alert('Insertado correctamente');
            window.location.href = 'seeActions.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeActions.php';
          </script>";
    }


  
}