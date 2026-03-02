<?php
session_start();
include("../../conexion.php");

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json');

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id'];

// Estados a excluir según requerimiento
$estados_excluidos = [
    'C.M SUSPENDIDO POR RENTA',
    'C.M SUSPENDIDO POR PENSION',
    'C.M. EN LISTA DE ESPERA',
    'C.M. FALLECIDO',
    'C.M. FALLECIDO SIN CERTIFICADO',
    'C.M. RETIRO DEFINITIVO',
    'C.M. RETIRO VOLUNTARIO',
    'C.M. SUSPENDIDO',
    'C.M. SUSPENDIDO POR TRASLADO MUNICIPIO'
];

// Escapar estados para el query
$estados_excluidos_escaped = array_map(function($estado) use ($mysqli) {
    return "'" . $mysqli->real_escape_string($estado) . "'";
}, $estados_excluidos);

$estados_excluidos_str = implode(', ', $estados_excluidos_escaped);

// Construir query para traer todas las personas EXCEPTO las que tienen estados excluidos
// Permite NULL o vacío en estado_cm
$where = "WHERE (estado_cm IS NULL OR estado_cm = '' OR estado_cm NOT IN ($estados_excluidos_str))";

// Si es contratista (tipo 9), solo ver sus propios registros
if ($tipo_usuario == 9) {
    $where .= " AND usuario_registro = '$id_usuario'";
}

// Consulta SQL
$query = "
    SELECT 
        cedula_persona_cm as cedula,
        CONCAT(nombres_persona_cm, ' ', apellidos_persona_cm) as nombre_completo,
        genero_persona_cm as genero,
        estado_cm as estado
    FROM personas_colombia_mayor
    $where
    ORDER BY apellidos_persona_cm ASC, nombres_persona_cm ASC
";

$result = $mysqli->query($query);

$personas = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $personas[] = [
            'cedula' => $row['cedula'],
            'nombre_completo' => $row['nombre_completo'],
            'genero' => $row['genero'] ?? 'N/A',
            'estado' => $row['estado']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'personas' => $personas,
        'total' => count($personas)
    ]);
} else {
    echo json_encode([
        'success' => true,
        'personas' => [],
        'total' => 0,
        'message' => 'No hay personas disponibles para seleccionar (se excluyen estados: suspendidos, fallecidos, retirados, en lista de espera)'
    ]);
}

$mysqli->close();
?>
