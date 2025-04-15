<?php
session_start();
include("../../conexion.php");
$where = "WHERE p.estado_persona = 1";

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona = '$cedula'";
}

// Filtro por nombre
if (!empty($_GET['nombre'])) {
    $nombre = $mysqli->real_escape_string($_GET['nombre']);
    $where .= " AND (p.nombres_persona LIKE '%$nombre%' OR p.apellidos_persona LIKE '%$nombre%')";
}

// Filtro por programa
if (!empty($_GET['programa'])) {
    $programa = $mysqli->real_escape_string($_GET['programa']);
    $where .= " AND pp.id_programa = '$programa'";
}

// Consulta SQL para obtener los datos
$query = "
SELECT p.*, 
       GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas,
       GROUP_CONCAT(pr.id_programa ORDER BY pr.nombre_programa ASC) AS ids_programas
FROM personas p
LEFT JOIN persona_programa pp ON p.cedula_persona = pp.cedula_persona
LEFT JOIN programas pr ON pp.id_programa = pr.id_programa
$where
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
        echo "<td>" . $row['genero_persona'] . "</td>";
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
                    data-genero="' . $row['genero_persona'] . '"
                     data-ids-programas="' .  $row['ids_programas']  . '"
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
