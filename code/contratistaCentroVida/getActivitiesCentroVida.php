<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "SELECT * FROM actividad_centro_vida ORDER BY descripcion_actividad ASC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . $row['id_actividad_centro_vida'] . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_actividad']) . "</td>";
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar actividad"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_actividad="' . $row['id_actividad_centro_vida'] . '"
                        data-descripcion_actividad="' . htmlspecialchars($row['descripcion_actividad']) . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete" 
                        title="Eliminar actividad"
                        onclick="confirmarEliminacion(' . $row['id_actividad_centro_vida'] . ', \'' . htmlspecialchars($row['descripcion_actividad'], ENT_QUOTES) . '\')">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr>";
    echo "<td colspan='3' class='text-center text-muted py-4'>";
    echo "<i class='bi bi-inbox fs-1 d-block mb-2' style='color: #e5e7eb;'></i>";
    echo "No hay actividades centro vida registradas";
    echo "</td>";
    echo "</tr>";
}

$mysqli->close();
?>
