<?php
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "SELECT * FROM condiciones_componente ORDER BY id_condicion ASC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . htmlspecialchars($row['id_condicion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_condicion']) . "</td>";
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary btn-edit btn-sm" 
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_condicion="' . htmlspecialchars($row['id_condicion']) . '"
                        data-descripcion_condicion="' . htmlspecialchars($row['descripcion_condicion']) . '"
                        data-bs-toggle="tooltip" title="Editar condición">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ms-1" 
                        onclick="eliminarCondicion(' . htmlspecialchars($row['id_condicion']) . ')"
                        data-bs-toggle="tooltip" title="Eliminar condición">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3' class='text-center'>No se encontraron registros.</td></tr>";
}

$mysqli->close();
?>
