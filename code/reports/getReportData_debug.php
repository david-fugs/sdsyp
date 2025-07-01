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
    // Consulta simplificada para pruebas
    $query = "
        SELECT 
            p.cedula_persona,
            p.nombres_persona,
            p.apellidos_persona,
            p.genero_persona,
            p.fecha_nacimiento,
            g.descripcion_grupo as centro_vida,
            pol.descripcion_politica
        FROM personas p
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
        WHERE p.estado_persona = 1 
        ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC
        LIMIT 10
    ";
    
    $result = $mysqli->query($query);
    if (!$result) {
        throw new Exception("Error en consulta principal: " . $mysqli->error);
    }
    
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        // Formatear fecha de nacimiento
        $fecha_nacimiento_formatted = '';
        if ($row['fecha_nacimiento'] && $row['fecha_nacimiento'] != '0000-00-00') {
            $fecha_nacimiento_formatted = date('d/m/Y', strtotime($row['fecha_nacimiento']));
        }
        
        $persona = [
            'cedula_persona' => $row['cedula_persona'],
            'nombres_persona' => $row['nombres_persona'],
            'apellidos_persona' => $row['apellidos_persona'],
            'genero_persona' => $row['genero_persona'],
            'fecha_nacimiento' => $fecha_nacimiento_formatted,
            'centro_vida' => $row['centro_vida'] ?: 'No asignado',
            'descripcion_politica' => $row['descripcion_politica'] ?: 'No asignada',
            'fecha_registro' => date('d/m/Y')
        ];
        
        $data[] = $persona;
    }
    
    echo json_encode([
        'success' => true,
        'year' => $year,
        'total_registros' => count($data),
        'data' => $data,
        'debug' => [
            'mysql_version' => $mysqli->server_info,
            'php_version' => phpversion(),
            'query_executed' => true
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al obtener datos: ' . $e->getMessage(),
        'debug' => [
            'mysql_error' => $mysqli->error,
            'mysql_errno' => $mysqli->errno,
            'line' => __LINE__
        ]
    ]);
}

$mysqli->close();
?>
