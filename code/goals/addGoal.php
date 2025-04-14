<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $descripcion_meta = $_POST['descripcion_meta'];
    $sql_insert_meta = "INSERT INTO metas (descripcion_meta) VALUES ('$descripcion_meta')";
    if ($mysqli->query($sql_insert_meta)) {
        echo "<script>
            alert('Insert successful');
            window.location.href = 'seeGoals.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeGoals.php';
          </script>";
    }
}