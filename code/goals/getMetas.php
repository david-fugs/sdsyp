<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . $row['id_meta'] . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_meta']) . "</td>";
        
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar meta"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_meta="' . $row['id_meta'] . '"
                        data-descripcion_meta="' . htmlspecialchars($row['descripcion_meta']) . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete" 
                        title="Eliminar meta"
                        onclick="confirmarEliminacion(' . $row['id_meta'] . ', \'' . htmlspecialchars($row['descripcion_meta'], ENT_QUOTES) . '\')">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No se encontraron registros.</td></tr>";
}

$mysqli->close();
?>
