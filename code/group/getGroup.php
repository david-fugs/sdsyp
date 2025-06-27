<?php
session_start();
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = " SELECT * FROM grupos
 ORDER BY id_grupo ASC
";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo "<tr>";
        echo "<td>" . $row['id_grupo'] . "</td>";
        echo "<td>" . $row['descripcion_grupo'] . "</td>";
        echo "<td>" . $row['limite_personas'] . "</td>";
        echo '
            <td data-label="Editar">
                <button type="button" class="btn-edit" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-id_grupo="' . $row['id_grupo'] . '"
                    data-descripcion_grupo="' . $row['descripcion_grupo'] . '"
                    data-limite_personas="' . $row['limite_personas'] . '"
                    style="background-color:transparent; border:none;">
                    <img src="../../img/editar.png" width="80px" height="80px">
                </button>     
            </td> ';
        //delete
        echo '
        <td>
                <a href="?delete=' . $row['id_grupo'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta grupo?\')">
                    <img src="../../img/delete1.png" width="80px" height="80px" alt="Eliminar">
                </a>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No se encontraron registros.</td></tr>";
}


$mysqli->close();
