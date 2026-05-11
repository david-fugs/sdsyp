<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupos.php');

header('Content-Type: application/json');

$tipo_usuario    = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// DataTables server-side params
$draw   = isset($_GET['draw'])   ? intval($_GET['draw'])   : 1;
$start  = isset($_GET['start'])  ? intval($_GET['start'])  : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 15;

// Columnas ordenables (índice DataTables => columna SQL)
$columnas_ordenables = [
    0  => 'p.cedula_persona',
    1  => 'p.nombres_persona',
    2  => 'p.apellidos_persona',
    3  => 'c.descripcion_condicion',
    4  => 'm.descripcion_meta',
    5  => 'a.descripcion_actividad',
    6  => 'ac.descripcion_accion',
    7  => 'pp.descripcion_politica',
    8  => 'ri.departamento_procedencia',
    9  => 'g.descripcion_grupo',
    10 => 'ri.fecha_registro',
    11 => 'ri.observacion_registro',
    12 => 'u.nombre',
];

$order_col_idx = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 10;
$order_dir     = isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
$order_col_sql = isset($columnas_ordenables[$order_col_idx]) ? $columnas_ordenables[$order_col_idx] : 'ri.fecha_registro';

// Aplicar filtro de grupos según tipo de usuario
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

$where = "WHERE p.estado_persona = 1";

// Filtros personalizados
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona = '$cedula'";
}
if (!empty($_GET['nombre'])) {
    $nombre = $mysqli->real_escape_string($_GET['nombre']);
    $where .= " AND (p.nombres_persona LIKE '%$nombre%' OR p.apellidos_persona LIKE '%$nombre%')";
}
if (!empty($_GET['condicion'])) {
    $condicion = $mysqli->real_escape_string($_GET['condicion']);
    $where .= " AND c.id_condicion = '$condicion'";
}

if ($tipo_usuario != 1 && $id_grupo_session && !in_array($tipo_usuario, [2, 3, 4, 5, 10])) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

// Contratistas (tipo 2 y 3) solo ven sus propios registros
if (in_array($tipo_usuario, [2, 3]) && isset($_SESSION['id'])) {
    $id_usuario_session = intval($_SESSION['id']);
    $where .= " AND ri.id_usuario = $id_usuario_session";
}

$where .= $where_grupos_filtro;

$from_join = "FROM personas AS p
    JOIN registro_individual AS ri ON p.cedula_persona = ri.cedula_persona
    JOIN condiciones_componente AS c ON ri.id_condicion = c.id_condicion
    LEFT JOIN grupos g ON ri.id_centro_vida_traslado = g.id_grupo
    LEFT JOIN metas m ON ri.id_meta = m.id_meta
    LEFT JOIN actividades a ON ri.id_actividad = a.id_actividad
    LEFT JOIN acciones ac ON ri.id_accion = ac.id_accion
    LEFT JOIN usuarios u ON ri.id_usuario = u.id
    LEFT JOIN politicas_publicas pp ON ri.id_politica_publica = pp.id_politica
    LEFT JOIN barrios b ON ri.id_barrio = b.id_bar
    LEFT JOIN comunas com ON ri.id_comuna = com.id_com
    $where";

// Total de registros filtrados
$result_count = $mysqli->query("SELECT COUNT(*) AS total $from_join");
$total_records = 0;
if ($result_count) {
    $row_count = $result_count->fetch_assoc();
    $total_records = intval($row_count['total']);
}

// Consulta paginada
$query = "SELECT
    ri.id_registro_individual,
    c.id_condicion,
    p.cedula_persona,
    p.nombres_persona,
    p.apellidos_persona,
    c.descripcion_condicion,
    ri.fecha_registro,
    ri.observacion_registro,
    ri.id_centro_vida_traslado,
    g.descripcion_grupo AS centro_vida_traslado,
    ri.id_meta,
    ri.id_actividad,
    ri.id_accion,
    ri.departamento_procedencia,
    ri.id_politica_publica,
    ri.id_barrio,
    ri.id_comuna,
    b.nombre_bar AS nombre_barrio,
    com.nombre_com AS nombre_com,
    m.descripcion_meta,
    a.descripcion_actividad,
    ac.descripcion_accion,
    u.nombre AS nombre_usuario,
    u.id AS id_usuario,
    pp.descripcion_politica
    $from_join
    ORDER BY $order_col_sql $order_dir
    LIMIT $length OFFSET $start";

$result = $mysqli->query($query);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $esc = function($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); };

        $acciones = '<div class="action-buttons">'
            . '<button type="button" class="btn-action btn-edit"'
            . ' title="Editar registro"'
            . ' data-bs-toggle="modal" data-bs-target="#modalEdicion"'
            . ' data-cedula="'                   . $esc($row['cedula_persona'])          . '"'
            . ' data-nombre="'                   . $esc($row['nombres_persona'])         . '"'
            . ' data-apellidos="'                . $esc($row['apellidos_persona'])       . '"'
            . ' data-descripcion_condicion="'    . $esc($row['descripcion_condicion'])   . '"'
            . ' data-fecha_movimiento="'         . $esc($row['fecha_registro'])          . '"'
            . ' data-observacion_movimiento="'   . $esc($row['observacion_registro'])    . '"'
            . ' data-condicion="'                . $esc($row['id_condicion'])            . '"'
            . ' data-centro_vida_traslado="'     . $esc($row['id_centro_vida_traslado']) . '"'
            . ' data-id_registro_individual="'   . $esc($row['id_registro_individual'])  . '"'
            . ' data-meta="'                     . $esc($row['id_meta'] ?? '')           . '"'
            . ' data-actividad="'                . $esc($row['id_actividad'] ?? '')      . '"'
            . ' data-id_usuario="'               . $esc($row['id_usuario'] ?? '')        . '"'
            . ' data-accion="'                   . $esc($row['id_accion'] ?? '')         . '"'
            . ' data-id_politica_publica="'      . $esc($row['id_politica_publica'] ?? '') . '"'
            . ' data-departamento_procedencia="' . $esc($row['departamento_procedencia'] ?? '') . '"'
            . ' data-barrio="'                   . $esc($row['id_barrio'] ?? '')         . '"'
            . ' data-nombre_barrio="'            . $esc($row['nombre_barrio'] ?? '')     . '"'
            . ' data-id_comuna="'                . $esc($row['id_comuna'] ?? '')         . '"'
            . ' data-nombre_com="'               . $esc($row['nombre_com'] ?? '')        . '">'
            . '<i class="bi bi-pencil-fill"></i>'
            . '</button>'
            . '<a href="?delete=' . urlencode($row['cedula_persona']) . '"'
            . ' class="btn-action btn-delete"'
            . ' title="Eliminar registro"'
            . ' onclick="return confirm(\'¿Estás seguro de que deseas eliminar este registro?\')">'
            . '<i class="bi bi-trash-fill"></i>'
            . '</a>'
            . '</div>';

        $data[] = [
            $esc($row['cedula_persona']),
            $esc($row['nombres_persona']),
            $esc($row['apellidos_persona']),
            $esc($row['descripcion_condicion']),
            $row['descripcion_meta']       ? $esc($row['descripcion_meta'])       : 'N/A',
            $row['descripcion_actividad']  ? $esc($row['descripcion_actividad'])  : 'N/A',
            $row['descripcion_accion']     ? $esc($row['descripcion_accion'])     : 'N/A',
            $row['descripcion_politica']   ? $esc($row['descripcion_politica'])   : 'N/A',
            $row['departamento_procedencia'] ? $esc($row['departamento_procedencia']) : 'N/A',
            $row['centro_vida_traslado']   ? $esc($row['centro_vida_traslado'])   : 'N/A',
            $esc($row['fecha_registro']),
            $esc($row['observacion_registro']),
            $esc($row['nombre_usuario']),
            $acciones,
        ];
    }
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total_records,
    'recordsFiltered' => $total_records,
    'data'            => $data,
]);
