<?php
session_start();
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = " SELECT * FROM metas";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_meta'] . "</td>";
        echo "<td>" . $row['descripcion_meta'] . "</td>";
        echo '
            <td data-label="Editar">
                <button type="button" class="btn-edit" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-id_meta="' . $row['id_meta'] . '"
                    data-descripcion_meta="' . $row['descripcion_meta'] . '"
                    style="background-color:transparent; border:none;">
                    <img src="../../img/editar.png" width="28" height="28">
                </button>     
            </td> ';
        //delete
        echo '
        <td>
                <a href="?delete=' . $row['id_meta'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta persona?\')">
                    <img src="../../img/delete1.png" width="20" height="20" alt="Eliminar">
                </a>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No se encontraron registros.</td></tr>";
}


$mysqli->close();
