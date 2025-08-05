<?php
include("../../conexion.php");

if (isset($_POST['id_actividad'])) {
    $id_actividad = $_POST['id_actividad'];
    
    $query = "SELECT id_accion, descripcion_accion 
              FROM acciones 
              WHERE id_actividad = '$id_actividad' 
              ORDER BY descripcion_accion ASC";
    
    $result = mysqli_query($mysqli, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['id_accion'] . '">' . htmlspecialchars($row['descripcion_accion']) . '</option>';
        }
    }
}

$mysqli->close();
?>
