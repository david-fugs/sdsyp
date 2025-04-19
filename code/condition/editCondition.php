<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario

    $descripcion_condicion = $_POST['descripcion_condicion'];
    $id_meta = $_POST['id_meta'];
    $id_condicion = $_POST['id_condicion'];
    // Actualizar la condicion
    $sql_update_condicion = "UPDATE condiciones_componente SET descripcion_condicion='$descripcion_condicion' WHERE id_condicion='$id_condicion'";
    //ejecutar consulta
    if ($mysqli->query($sql_update_condicion)) {
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seeCondition.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seeCondition.php';
          </script>";
    }

    

}