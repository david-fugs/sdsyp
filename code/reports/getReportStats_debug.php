<?php
// No incluir access.php para evitar problemas de sesión
include("../../conexion.php");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar que se haya proporcionado el año
if (!isset($_GET['year']) || empty($_GET['year'])) {
    echo json_encode(['error' => 'Año no proporcionado']);
    exit;
}

$year = intval($_GET['year']);

// Verificar conexión a la base de datos
if ($mysqli->connect_error) {
    echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $mysqli->connect_error]);
    exit;
}

try {
    // Estadísticas básicas
    $stats = [];
    
    // 1. Total de personas activas
    $query_personas_activas = "SELECT COUNT(*) as total FROM personas WHERE estado_persona = 1";
    $result = $mysqli->query($query_personas_activas);
    if (!$result) {
        throw new Exception("Error en consulta personas activas: " . $mysqli->error);
    }
    $stats['personas_activas'] = $result->fetch_assoc()['total'];
    
    // 2. Total de movimientos en el año
    $query_movimientos = "SELECT COUNT(*) as total FROM movimiento_persona WHERE YEAR(fecha_movimiento) = ?";
    $stmt = $mysqli->prepare($query_movimientos);
    if (!$stmt) {
        throw new Exception("Error preparando consulta movimientos: " . $mysqli->error);
    }
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_movimientos'] = $result->fetch_assoc()['total'];
    
    // 3. Personas con movimientos en el año
    $query_personas_nuevas = "SELECT COUNT(DISTINCT cedula_persona) as total 
                              FROM movimiento_persona 
                              WHERE YEAR(fecha_movimiento) = ?";
    $stmt = $mysqli->prepare($query_personas_nuevas);
    if (!$stmt) {
        throw new Exception("Error preparando consulta personas nuevas: " . $mysqli->error);
    }
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['personas_nuevas'] = $result->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'year' => $year,
        'stats' => $stats,
        'debug' => [
            'mysql_version' => $mysqli->server_info,
            'php_version' => phpversion()
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al obtener estadísticas: ' . $e->getMessage(),
        'debug' => [
            'mysql_error' => $mysqli->error,
            'mysql_errno' => $mysqli->errno
        ]
    ]);
}

$mysqli->close();
?>
