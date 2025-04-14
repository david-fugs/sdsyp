<?php
session_start();
include("../../conexion.php");

// Consulta SQL para obtener los datos
$query = "
SELECT p.*, GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas
FROM personas p
JOIN persona_programa pp ON p.cedula_persona = pp.cedula_persona
JOIN programas pr ON pp.id_programa = pr.id_programa
WHERE p.estado_persona = 1
GROUP BY p.cedula_persona
ORDER BY p.apellidos_persona ASC
";
$result = $mysqli->query($query);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['cedula_persona'] . "</td>";
        echo "<td>" . $row['nombres_persona'] . "</td>";
        echo "<td>" . $row['apellidos_persona'] . "</td>";
        echo "<td>" . $row['telefono_persona'] . "</td>";
        echo "<td>" . $row['referencia_persona'] . "</td>";
        echo "<td>" . $row['programas'] . "</td>";
        //edit
        echo '
            <td data-label="Editar">
                <button type="button" class="btn-edit" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-cedula="' . $row['cedula_persona'] . '"
                    data-nombre="' . $row['nombres_persona'] . '"
                    data-apellidos="' . $row['apellidos_persona'] . '"
                    data-telefono="' . $row['telefono_persona'] . '"
                    data-referencia="' . $row['referencia_persona'] . '"
                    data-programas="' .  $row['programas']  . '"
                    style="background-color:transparent; border:none;">
                    <img src="../../img/editar.png" width="28" height="28">
                </button>     
            </td> ';
        //delete
        echo '
        <td>
                <a href="?delete=' . $row['cedula_persona'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta persona?\')">
                    <img src="../../img/delete1.png" width="20" height="20" alt="Eliminar">
                </a>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No se encontraron registros.</td></tr>";
}


$mysqli->close();
