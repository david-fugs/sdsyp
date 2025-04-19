<?php
session_start();
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = " SELECT * FROM condiciones_componente
 ORDER BY id_condicion ASC
";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo "<tr>";
        echo "<td>" . $row['id_condicion'] . "</td>";
        echo "<td>" . $row['descripcion_condicion'] . "</td>";
        echo '
            <td data-label="Editar">
                <button type="button" class="btn-edit" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-id_condicion="' . $row['id_condicion'] . '"
                    data-descripcion_condicion="' . $row['descripcion_condicion'] . '"
                    style="background-color:transparent; border:none;">
                    <img src="../../img/editar.png" width="80px" height="80px">
                </button>     
            </td> ';
        //delete
        echo '
        <td>
                <a href="?delete=' . $row['id_condicion'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta condicion?\')">
                    <img src="../../img/delete1.png" width="80px" height="80px" alt="Eliminar">
                </a>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No se encontraron registros.</td></tr>";
}


$mysqli->close();
