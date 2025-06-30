<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "
SELECT pp.*, acc.descripcion_accion 
FROM politicas_publicas pp
LEFT JOIN acciones acc ON pp.id_accion = acc.id_accion
ORDER BY pp.id_politica ASC
";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . htmlspecialchars($row['id_politica']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_politica']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_accion'] ? $row['descripcion_accion'] : 'No asignada') . "</td>";
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary btn-edit btn-sm" 
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_politica="' . htmlspecialchars($row['id_politica']) . '"
                        data-descripcion_politica="' . htmlspecialchars($row['descripcion_politica']) . '"
                        data-id_accion="' . htmlspecialchars($row['id_accion']) . '"
                        data-bs-toggle="tooltip" title="Editar política pública">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ms-1" 
                        onclick="eliminarPolitica(' . htmlspecialchars($row['id_politica']) . ')"
                        data-bs-toggle="tooltip" title="Eliminar política pública">
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
