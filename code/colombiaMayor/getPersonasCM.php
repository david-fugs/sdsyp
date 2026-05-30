<?php
// Endpoint DataTables server-side para personas Colombia Mayor
session_start();
include("../../conexion.php");

if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json');

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario   = $_SESSION['id'];

// DataTables params
$draw   = intval($_POST['draw'] ?? 1);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 25);

// Custom filter params
$cedula = trim($_POST['cedula'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$estado = trim($_POST['estado'] ?? '');

// Column map: DataTables column index → DB column for ORDER BY
$column_map = [
    0 => 'p.cedula_persona_cm',
    1 => 'p.apellidos_persona_cm',
    2 => 'p.genero_persona_cm',
    3 => 'p.fecha_nacimiento_cm',
    4 => 'p.telefono_persona_cm',
    5 => 'p.fecha_ingreso_cm',
    6 => 'p.estado_cm',
];
$order_col_idx = intval($_POST['order'][0]['column'] ?? 1);
$order_dir     = (($_POST['order'][0]['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';
$order_col     = $column_map[$order_col_idx] ?? 'p.apellidos_persona_cm';

// Build WHERE
$where = "WHERE 1=1";

if ($tipo_usuario == 9) {
    $where .= " AND p.usuario_registro = '" . $mysqli->real_escape_string($id_usuario) . "'";
}
if (!empty($cedula)) {
    $where .= " AND p.cedula_persona_cm LIKE '%" . $mysqli->real_escape_string($cedula) . "%'";
}
if (!empty($nombre)) {
    $n = $mysqli->real_escape_string($nombre);
    $where .= " AND (p.nombres_persona_cm LIKE '%$n%' OR p.apellidos_persona_cm LIKE '%$n%')";
}
if (!empty($estado)) {
    $where .= " AND p.estado_cm LIKE '%" . $mysqli->real_escape_string($estado) . "%'";
}

// Total unfiltered
$res_total     = $mysqli->query("SELECT COUNT(*) AS cnt FROM personas_colombia_mayor p");
$total_records = (int)$res_total->fetch_assoc()['cnt'];

// Total filtered
$res_filtered     = $mysqli->query("SELECT COUNT(*) AS cnt FROM personas_colombia_mayor p $where");
$filtered_records = (int)$res_filtered->fetch_assoc()['cnt'];

// Data query
$limit_clause = ($length >= 0)
    ? "LIMIT " . intval($start) . ", " . intval($length)
    : "";

$query = "
    SELECT p.*, u.nombre AS nombre_contratista
    FROM personas_colombia_mayor p
    LEFT JOIN usuarios u ON p.usuario_registro = u.id
    $where
    ORDER BY $order_col $order_dir, p.nombres_persona_cm $order_dir
    $limit_clause
";

$result = $mysqli->query($query);
if (!$result) {
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
        'error'           => $mysqli->error,
    ]);
    exit();
}

$data = [];
while ($row = $result->fetch_assoc()) {

    // Compute age
    $edad_texto = 'N/A';
    if (!empty($row['fecha_nacimiento_cm']) && $row['fecha_nacimiento_cm'] !== '0000-00-00') {
        $hoy = new DateTime();
        $nac = new DateTime($row['fecha_nacimiento_cm']);
        $edad_texto = $hoy->diff($nac)->y . ' años';
    } elseif (!empty($row['edad_cm'])) {
        $edad_texto = $row['edad_cm'] . ' años';
    }

    // Format fecha ingreso
    $fecha_ingreso_fmt = 'N/A';
    if (!empty($row['fecha_ingreso_cm']) && $row['fecha_ingreso_cm'] !== '0000-00-00') {
        $fecha_ingreso_fmt = date('d/m/Y', strtotime($row['fecha_ingreso_cm']));
    }

    // Estado badge
    $badge_class  = 'status-badge status-secondary';
    $estado_icon  = 'bi-circle-fill';
    $estado_upper = strtoupper($row['estado_cm'] ?? '');

    if ($estado_upper === 'ACTIVO' || stripos($estado_upper, 'ACTIVO') !== false) {
        $badge_class = 'status-badge status-active';
        $estado_icon = 'bi-check-circle-fill';
    } elseif (stripos($estado_upper, 'FALLECIDO') !== false || stripos($estado_upper, 'FALLECIDA') !== false) {
        $badge_class = 'status-badge status-secondary';
        $estado_icon = 'bi-x-circle-fill';
    } elseif (stripos($estado_upper, 'RETIRO') !== false || stripos($estado_upper, 'RETIRADO') !== false) {
        $badge_class = 'status-badge status-info';
        $estado_icon = 'bi-arrow-left-circle-fill';
    } elseif (stripos($estado_upper, 'SUSPENDIDO') !== false || stripos($estado_upper, 'SUSPENDIDA') !== false) {
        $badge_class = 'status-badge status-warning';
        $estado_icon = 'bi-pause-circle-fill';
    } elseif (stripos($estado_upper, 'POTENCIAL') !== false || stripos($estado_upper, 'BENEFICIARIO') !== false) {
        $badge_class = 'status-badge status-info';
        $estado_icon = 'bi-person-fill-add';
    } elseif ($estado_upper === 'INSCRITO') {
        $badge_class = 'status-badge status-primary';
        $estado_icon = 'bi-person-check-fill';
    } elseif (stripos($estado_upper, 'ESPERA') !== false || stripos($estado_upper, 'LISTA') !== false) {
        $badge_class = 'status-badge status-warning';
        $estado_icon = 'bi-clock-fill';
    } elseif (
        stripos($estado_upper, 'BDUA')       !== false ||
        stripos($estado_upper, 'BLOQUEO')    !== false ||
        stripos($estado_upper, 'DUPLICIDAD') !== false
    ) {
        $badge_class = 'status-badge status-danger';
        $estado_icon = 'bi-exclamation-triangle-fill';
    }

    $data[] = [
        // Display columns
        'cedula'               => htmlspecialchars($row['cedula_persona_cm'] ?? ''),
        'nombre_completo'      => htmlspecialchars(trim(($row['nombres_persona_cm'] ?? '') . ' ' . ($row['apellidos_persona_cm'] ?? ''))),
        'genero'               => htmlspecialchars($row['genero_persona_cm'] ?? 'N/A'),
        'edad'                 => $edad_texto,
        'telefono'             => htmlspecialchars($row['telefono_persona_cm'] ?? 'N/A'),
        'fecha_ingreso'        => $fecha_ingreso_fmt,
        'estado_display'       => str_replace('_', ' ', $row['estado_cm'] ?? ''),
        'badge_class'          => $badge_class,
        'estado_icon'          => $estado_icon,
        // Edit modal data
        'tipo_identificacion'  => htmlspecialchars($row['tipo_identificacion_cm'] ?? ''),
        'nombres'              => htmlspecialchars($row['nombres_persona_cm'] ?? ''),
        'apellidos'            => htmlspecialchars($row['apellidos_persona_cm'] ?? ''),
        'telefono_referencia'  => htmlspecialchars($row['telefono_referencia_cm'] ?? ''),
        'referencia'           => htmlspecialchars($row['referencia_cm'] ?? ''),
        'fecha_nacimiento'     => htmlspecialchars($row['fecha_nacimiento_cm'] ?? ''),
        'edad_num'             => htmlspecialchars($row['edad_cm'] ?? ''),
        'grupo_sisben'         => htmlspecialchars($row['grupo_sisben'] ?? ''),
        'direccion'            => htmlspecialchars($row['direccion_cm'] ?? ''),
        'barrio'               => htmlspecialchars($row['barrio_cm'] ?? ''),
        'comuna'               => htmlspecialchars($row['comuna_cm'] ?? ''),
        'zona'                 => htmlspecialchars($row['zona_cm'] ?? ''),
        'departamento'         => htmlspecialchars($row['departamento_cm'] ?? ''),
        'municipio'            => htmlspecialchars($row['municipio_cm'] ?? ''),
        'convivencia_actual'   => htmlspecialchars($row['convivencia_actual'] ?? ''),
        'persona_discapacidad' => htmlspecialchars($row['persona_discapacidad'] ?? ''),
        'cual_discapacidad'    => htmlspecialchars($row['cual_discapacidad'] ?? ''),
        'cabeza_hogar'         => htmlspecialchars($row['cabeza_hogar'] ?? ''),
        'se_reconoce_como'     => htmlspecialchars($row['se_reconoce_como'] ?? ''),
        'orientacion_sexual'   => htmlspecialchars($row['orientacion_sexual'] ?? ''),
        'grupo_etnico'         => htmlspecialchars($row['grupo_etnico'] ?? ''),
        'tipo_salud'           => htmlspecialchars($row['tipo_salud'] ?? ''),
        'condicion_ocupacion'  => htmlspecialchars($row['condicion_ocupacion'] ?? ''),
        'condicion_componente' => htmlspecialchars($row['condicion_componente'] ?? ''),
        'fecha_ingreso_raw'    => htmlspecialchars($row['fecha_ingreso_cm'] ?? ''),
        'estado_raw'           => htmlspecialchars($row['estado_cm'] ?? ''),
        'id_meta'              => htmlspecialchars($row['id_meta'] ?? ''),
        'id_actividad'         => htmlspecialchars($row['id_actividad'] ?? ''),
        'id_accion'            => htmlspecialchars($row['id_accion'] ?? ''),
        'id_politica_publica'  => htmlspecialchars($row['id_politica_publica'] ?? ''),
        'observaciones'        => htmlspecialchars($row['observaciones_cm'] ?? ''),
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total_records,
    'recordsFiltered' => $filtered_records,
    'data'            => $data,
]);
