<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "SELECT * FROM acciones
          JOIN actividades ON acciones.id_actividad = actividades.id_actividad
          JOIN metas ON actividades.id_meta = metas.id_meta
          ORDER BY acciones.descripcion_accion ASC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . $row['id_accion'] . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_actividad']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_accion']) . "</td>";
        
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar acción"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_accion="' . $row['id_accion'] . '"
                        data-id_actividad="' . $row['id_actividad'] . '"
                        data-descripcion_accion="' . htmlspecialchars($row['descripcion_accion']) . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete" 
                        title="Eliminar acción"
                        onclick="confirmarEliminacion(' . $row['id_accion'] . ', \'' . htmlspecialchars($row['descripcion_accion'], ENT_QUOTES) . '\')">
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
