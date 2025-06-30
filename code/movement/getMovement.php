<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "SELECT * FROM movimientos ORDER BY descripcion_movimiento ASC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . $row['id_movimiento'] . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_movimiento']) . "</td>";
        
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar movimiento"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_movimiento="' . $row['id_movimiento'] . '"
                        data-descripcion_movimiento="' . htmlspecialchars($row['descripcion_movimiento']) . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete" 
                        title="Eliminar movimiento"
                        onclick="confirmarEliminacion(' . $row['id_movimiento'] . ', \'' . htmlspecialchars($row['descripcion_movimiento'], ENT_QUOTES) . '\')">
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
