<?php
include("../../conexion.php");

if (isset($_POST['id_meta'])) {
    $id_meta = $_POST['id_meta'];
    
    $query = "SELECT id_actividad, descripcion_actividad 
              FROM actividades 
              WHERE id_meta = '$id_meta' 
              ORDER BY descripcion_actividad ASC";
    
    $result = mysqli_query($mysqli, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['id_actividad'] . '">' . htmlspecialchars($row['descripcion_actividad']) . '</option>';
        }
    }
}

$mysqli->close();
?>
