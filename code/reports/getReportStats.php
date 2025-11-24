<?php
session_start();
require_once('../filtros_grupos.php');
include("../../conexion.php");

header('Content-Type: application/json');

// Verificar que se haya proporcionado el año
if (!isset($_GET['year']) || empty($_GET['year'])) {
    echo json_encode(['error' => 'Año no proporcionado']);
    exit;
}

$year = intval($_GET['year']);

// Obtener filtro de grupo si está presente
$filtro_grupo = isset($_GET['filtro_grupo']) && !empty($_GET['filtro_grupo']) ? intval($_GET['filtro_grupo']) : null;

try {
    // Obtener tipo de usuario y aplicar filtro de grupos
    $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
    $where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');
    
    // Aplicar filtro por grupo específico si se seleccionó uno
    if ($filtro_grupo !== null) {
        $where_grupos_filtro .= " AND p.id_grupo = " . intval($filtro_grupo);
    }
    
    // Estadísticas generales del año
    $stats = [];

    // 1. Personas con movimientos en el año seleccionado
    $query_personas_nuevas = "SELECT COUNT(DISTINCT mp.cedula_persona) as total 
                              FROM movimiento_persona mp 
                              JOIN personas p ON mp.cedula_persona = p.cedula_persona
                              WHERE YEAR(mp.fecha_movimiento) = ? 
                              AND p.estado_persona = 1 $where_grupos_filtro";
    $stmt = $mysqli->prepare($query_personas_nuevas);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['personas_nuevas'] = $result->fetch_assoc()['total'];

    // 2. Total de personas activas (sin movimientos de salida)
    $query_personas_activas = "SELECT COUNT(*) as total FROM personas p 
                              WHERE p.estado_persona = 1 $where_grupos_filtro";
    $stmt = $mysqli->prepare($query_personas_activas);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['personas_activas'] = $result->fetch_assoc()['total'];

    // 3. Total de movimientos en el año
    $query_movimientos = "SELECT COUNT(*) as total FROM movimiento_persona WHERE YEAR(fecha_movimiento) = ?";
    $stmt = $mysqli->prepare($query_movimientos);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_movimientos'] = $result->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'year' => $year,
        'stats' => $stats
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener estadisticas: ' . $e->getMessage()]);
}

$mysqli->close();
