<?php
include '../conexion.php';

if (isset($_POST['id_grupo'])) {
    $id_grupo = $_POST['id_grupo'];

    $stmt = $mysqli->prepare("SELECT id_centro_vida, descripcion_centro_vida FROM centro_vida WHERE id_grupo = ?");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificamos si hay resultados
    if ($result->num_rows > 0) {
        while ($centro = $result->fetch_assoc()) {
            echo "<option value='{$centro['id_centro_vida']}'>{$centro['descripcion_centro_vida']}</option>";
        }
    } else {
        echo "<option value=''>No hay centros disponibles</option>";
    }

    $stmt->close();
}
?>
