<?php
session_start();
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = " SELECT * FROM actividades
 JOIN metas ON actividades.id_meta = metas.id_meta
 ORDER BY metas.id_meta ASC
";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo "<tr>";
        echo "<td>" . $row['id_actividad'] . "</td>";
        echo "<td>" . $row['descripcion_meta'] . "</td>";
        echo "<td>" . $row['descripcion_actividad'] . "</td>";
        echo '
            <td data-label="Editar">
                <button type="button" class="btn-edit" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-id_actividad="' . $row['id_actividad'] . '"
                    data-id_meta="' . $row['id_meta'] . '"
                    data-descripcion_actividad="' . $row['descripcion_actividad'] . '"
                    style="background-color:transparent; border:none;">
                    <img src="../../img/editar.png" width="80px" height="80px">
                </button>     
            </td> ';
        //delete
        echo '
        <td>
                <a href="?delete=' . $row['id_actividad'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta persona?\')">
                    <img src="../../img/delete1.png" width="80px" height="80px" alt="Eliminar">
                </a>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No se encontraron registros.</td></tr>";
}


$mysqli->close();
