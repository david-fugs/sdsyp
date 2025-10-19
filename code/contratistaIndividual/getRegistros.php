<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupos.php');

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

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
if ($tipo_usuario != 1 && $id_grupo_session && $tipo_usuario != 3) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

// Aplicar filtro adicional para usuarios técnicos (tipos 4 y 5)
$where .= $where_grupos_filtro;

// Consulta SQL para obtener los datos
$query = " SELECT ri.id_registro_individual,c.id_condicion,p.cedula_persona,p.nombres_persona,p.apellidos_persona,c.descripcion_condicion, 
           ri.fecha_registro,ri.observacion_registro,ri.id_centro_vida_traslado,g.descripcion_grupo as centro_vida_traslado,
           ri.id_meta, ri.id_actividad, ri.id_accion, ri.departamento_procedencia, ri.id_politica_publica,
           m.descripcion_meta, a.descripcion_actividad, ac.descripcion_accion, u.nombre as nombre_usuario, u.id as id_usuario, a.descripcion_actividad, ac.descripcion_accion, pp.descripcion_politica
           FROM personas as p
        JOIN registro_individual as ri ON p.cedula_persona = ri.cedula_persona
        JOIN condiciones_componente as c ON ri.id_condicion = c.id_condicion
        LEFT JOIN grupos g ON ri.id_centro_vida_traslado = g.id_grupo
        LEFT JOIN metas m ON ri.id_meta = m.id_meta
        LEFT JOIN actividades a ON ri.id_actividad = a.id_actividad
        LEFT JOIN acciones ac ON ri.id_accion = ac.id_accion
        LEFT JOIN usuarios u ON ri.id_usuario = u.id
        LEFT JOIN politicas_publicas pp ON ri.id_politica_publica = pp.id_politica

        $where
        ORDER BY ri.fecha_registro DESC
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
        echo "<td>" . ($row['descripcion_actividad'] ? $row['descripcion_actividad'] : 'N/A') . "</td>";
        echo "<td>" . ($row['descripcion_accion'] ? $row['descripcion_accion'] : 'N/A') . "</td>";
        echo "<td>" . ($row['descripcion_politica'] ? $row['descripcion_politica'] : 'N/A') . "</td>";
        echo "<td>" . ($row['departamento_procedencia'] ? $row['departamento_procedencia'] : 'N/A') . "</td>";
        echo "<td>" . ($row['centro_vida_traslado'] ? $row['centro_vida_traslado'] : 'N/A') . "</td>";
        echo "<td>" . $row['fecha_registro'] . "</td>";
        echo "<td>" . $row['observacion_registro'] . "</td>";
        echo "<td>" . $row['nombre_usuario'] . "</td>";
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar registro"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-cedula="' . $row['cedula_persona'] . '"
                        data-nombre="' . $row['nombres_persona'] . '"
                        data-apellidos="' . $row['apellidos_persona'] . '"
                        data-descripcion_condicion="' . $row['descripcion_condicion'] . '"
                        data-fecha_movimiento="' . $row['fecha_registro'] . '"
                        data-observacion_movimiento="' . $row['observacion_registro'] . '"
                        data-condicion="' . $row['id_condicion'] . '"
                        data-centro_vida_traslado="' . $row['id_centro_vida_traslado'] . '"
                        data-id_registro_individual="' . $row['id_registro_individual'] . '"
                        data-meta="' . ($row['id_meta'] ?? '') . '"
                        data-actividad="' . ($row['id_actividad'] ?? '') . '"
                        data-id_usuario="' . ($row['id_usuario'] ?? '') . '"
                        data-accion="' . ($row['id_accion'] ?? '') . '"
                        data-id_politica_publica="' . ($row['id_politica_publica'] ?? '') . '"
                        data-departamento_procedencia="' . ($row['departamento_procedencia'] ?? '') . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <a href="?delete=' . $row['cedula_persona'] . '" 
                       class="btn-action btn-delete" 
                       title="Eliminar registro"
                       onclick="return confirm(\'¿Estás seguro de que deseas eliminar este registro?\')">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
        // El encabezado de la tabla tiene 14 columnas, mantener colspan consistente para DataTables
        echo "<tr><td colspan='14' class='text-center text-muted'>
                        <i class='bi bi-search'></i><br>
                        No se encontraron registros que coincidan con los filtros aplicados.
                    </td></tr>";
}


$mysqli->close();
