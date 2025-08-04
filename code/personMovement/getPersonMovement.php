<?php
include("../../conexion.php");
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

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

// Filtro por condición
if (!empty($_GET['condicion'])) {
    $condicion = $mysqli->real_escape_string($_GET['condicion']);
    $where .= " AND c.id_condicion = '$condicion'";
}
if ($tipo_usuario != 1 && $id_grupo_session && $tipo_usuario != 2) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}
// Consulta SQL para obtener los datos
$query = " SELECT mp.id_movimiento_persona,c.id_condicion,p.cedula_persona,p.nombres_persona,p.apellidos_persona,c.descripcion_condicion, 
           mp.fecha_movimiento,mp.observacion_movimiento,mp.id_centro_vida_traslado,g.descripcion_grupo as centro_vida_traslado,
           mp.id_meta, mp.id_actividad, mp.id_accion, mp.departamento_procedencia, mp.id_politica_publica,
           m.descripcion_meta, a.descripcion_actividad, ac.descripcion_accion
           FROM personas as p
        JOIN movimiento_persona as mp ON p.cedula_persona = mp.cedula_persona
        JOIN condiciones_componente as c ON mp.id_condicion = c.id_condicion
        LEFT JOIN grupos g ON mp.id_centro_vida_traslado = g.id_grupo
        LEFT JOIN metas m ON mp.id_meta = m.id_meta
        LEFT JOIN actividades a ON mp.id_actividad = a.id_actividad
        LEFT JOIN acciones ac ON mp.id_accion = ac.id_accion
        $where
        ORDER BY mp.fecha_movimiento DESC
";
$result = $mysqli->query($query);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . $row['cedula_persona'] . "</td>";
        echo "<td>" . $row['nombres_persona'] . "</td>";
        echo "<td>" . $row['apellidos_persona'] . "</td>";
        echo "<td>" . $row['descripcion_condicion'] . "</td>";
        echo "<td>" . ($row['descripcion_meta'] ? $row['descripcion_meta'] : 'N/A') . "</td>";
        echo "<td>" . ($row['departamento_procedencia'] ? $row['departamento_procedencia'] : 'N/A') . "</td>";
        echo "<td>" . ($row['centro_vida_traslado'] ? $row['centro_vida_traslado'] : 'N/A') . "</td>";
        echo "<td>" . $row['fecha_movimiento'] . "</td>";
        echo "<td>" . $row['observacion_movimiento'] . "</td>";
        
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar movimiento"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-cedula="' . $row['cedula_persona'] . '"
                        data-nombre="' . $row['nombres_persona'] . '"
                        data-apellidos="' . $row['apellidos_persona'] . '"
                        data-descripcion_condicion="' . $row['descripcion_condicion'] . '"
                        data-fecha_movimiento="' . $row['fecha_movimiento'] . '"
                        data-observacion_movimiento="' . $row['observacion_movimiento'] . '"
                        data-condicion="' . $row['id_condicion'] . '"
                        data-centro_vida_traslado="' . $row['id_centro_vida_traslado'] . '"
                        data-id_movimiento_persona="' . $row['id_movimiento_persona'] . '"
                        data-meta="' . ($row['id_meta'] ?? '') . '"
                        data-actividad="' . ($row['id_actividad'] ?? '') . '"
                        data-accion="' . ($row['id_accion'] ?? '') . '"
                        data-id_politica_publica="' . ($row['id_politica_publica'] ?? '') . '"
                        data-departamento_procedencia="' . ($row['departamento_procedencia'] ?? '') . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <a href="?delete=' . $row['cedula_persona'] . '" 
                       class="btn-action btn-delete" 
                       title="Eliminar movimiento"
                       onclick="return confirm(\'¿Estás seguro de que deseas eliminar este movimiento?\')">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10' class='text-center text-muted'>
            <i class='bi bi-search'></i><br>
            No se encontraron registros que coincidan con los filtros aplicados.
          </td></tr>";
}


$mysqli->close();
