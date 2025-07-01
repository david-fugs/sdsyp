<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "SELECT * FROM grupos ORDER BY id_grupo ASC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . htmlspecialchars($row['id_grupo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_grupo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['limite_personas']) . "</td>";
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary btn-edit btn-sm" 
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_grupo="' . htmlspecialchars($row['id_grupo']) . '"
                        data-descripcion_grupo="' . htmlspecialchars($row['descripcion_grupo']) . '"
                        data-limite_personas="' . htmlspecialchars($row['limite_personas']) . '"
                        data-bs-toggle="tooltip" title="Editar grupo">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ms-1" 
                        onclick="eliminarGrupo(' . htmlspecialchars($row['id_grupo']) . ')"
                        data-bs-toggle="tooltip" title="Eliminar grupo">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' class='text-center'>No se encontraron registros.</td></tr>";
}

$mysqli->close();
?>
