<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "SELECT * FROM actividades
          JOIN metas ON actividades.id_meta = metas.id_meta
          ORDER BY actividades.descripcion_actividad ASC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . $row['id_actividad'] . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_meta']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_actividad']) . "</td>";
        
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar actividad"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_actividad="' . $row['id_actividad'] . '"
                        data-id_meta="' . $row['id_meta'] . '"
                        data-descripcion_actividad="' . htmlspecialchars($row['descripcion_actividad']) . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete" 
                        title="Eliminar actividad"
                        onclick="confirmarEliminacion(' . $row['id_actividad'] . ', \'' . htmlspecialchars($row['descripcion_actividad'], ENT_QUOTES) . '\')">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No se encontraron registros.</td></tr>";
}

$mysqli->close();
?>
