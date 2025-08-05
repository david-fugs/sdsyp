<?php
include("../../conexion.php");

// Recibir id_meta por POST
$id_meta = isset($_POST['id_meta']) ? intval($_POST['id_meta']) : 0;

// Consulta SQL para obtener actividades relacionadas con la meta

if ($id_meta > 0) {
    $query = "SELECT id_actividad, descripcion_actividad FROM actividades WHERE id_meta = $id_meta ORDER BY descripcion_actividad ASC";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id_actividad'] . '">' . htmlspecialchars($row['descripcion_actividad']) . '</option>';
        }
    } else {
        echo '<option value="">No hay actividades</option>';
    }
} else {
    echo '<option value="">Seleccione Meta primero</option>';
}

$mysqli->close();
?>
