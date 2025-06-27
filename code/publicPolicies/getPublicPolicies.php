<?php
session_start();
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
        echo "<tr>";
        echo "<td>" . $row['id_politica'] . "</td>";
        echo "<td>" . $row['descripcion_politica'] . "</td>";
        echo "<td>" . ($row['descripcion_accion'] ? $row['descripcion_accion'] : 'No asignada') . "</td>";
        echo '
            <td data-label="Editar">
                <button type="button" class="btn-edit" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-id_politica="' . $row['id_politica'] . '"
                    data-descripcion_politica="' . $row['descripcion_politica'] . '"
                    data-id_accion="' . $row['id_accion'] . '"
                    style="background-color:transparent; border:none;">
                    <img src="../../img/editar.png" width="40px" height="40px">
                </button>     
            </td> ';
        //delete
        echo '
        <td>
                <a href="?delete=' . $row['id_politica'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta política pública?\')">
                    <img src="../../img/delete1.png" width="40px" height="40px" alt="Eliminar">
                </a>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No se encontraron registros.</td></tr>";
}

$mysqli->close();
?>
