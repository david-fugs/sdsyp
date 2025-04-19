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
if (!empty($_GET['movimiento'])) {
    $movimiento = $mysqli->real_escape_string($_GET['movimiento']);
    $where .= " AND m.id_movimiento = '$movimiento'";
}

// Consulta SQL para obtener los datos
$query = " SELECT mp.id_movimiento_persona,m.id_movimiento,p.cedula_persona,p.nombres_persona,p.apellidos_persona,m.descripcion_movimiento, mp.fecha_movimiento,mp.observacion_movimiento FROM personas as p
        JOIN movimiento_persona as mp ON p.cedula_persona = mp.cedula_persona
        JOIN movimientos as m ON mp.id_movimiento = m.id_movimiento
        $where
        ORDER BY mp.fecha_movimiento DESC
";
$result = $mysqli->query($query);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['cedula_persona'] . "</td>";
        echo "<td>" . $row['nombres_persona'] . "</td>";
        echo "<td>" . $row['apellidos_persona'] . "</td>";
        echo "<td>" . $row['descripcion_movimiento'] . "</td>";
        echo "<td>" . $row['fecha_movimiento'] . "</td>";
        echo "<td>" . $row['observacion_movimiento'] . "</td>";
        //edit
        echo '
            <td data-label="Editar">
                <button type="button" class="btn-edit" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-cedula="' . $row['cedula_persona'] . '"
                    data-nombre="' . $row['nombres_persona'] . '"
                    data-apellidos="' . $row['apellidos_persona'] . '"
                    data-descripcion_movimiento="' . $row['descripcion_movimiento'] . '"
                    data-fecha_movimiento="' . $row['fecha_movimiento'] . '"
                    data-observacion_movimiento="' . $row['observacion_movimiento'] . '"
                    data-movimiento="' . $row['id_movimiento'] . '"
                    data-id_movimiento_persona="' . $row['id_movimiento_persona'] . '"
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
