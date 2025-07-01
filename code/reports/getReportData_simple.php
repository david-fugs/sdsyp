<?php
include("../../conexion.php");

header('Content-Type: application/json');

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
            pol.descripcion_politica,
            CURRENT_DATE as fecha_registro
        FROM personas p
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
        WHERE p.estado_persona = 1 
        ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC
        LIMIT 10
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $persona = [
            'cedula_persona' => $row['cedula_persona'],
            'nombres_persona' => $row['nombres_persona'],
            'apellidos_persona' => $row['apellidos_persona'],
            'genero_persona' => $row['genero_persona'],
            'fecha_nacimiento' => $row['fecha_nacimiento'],
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
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener datos: ' . $e->getMessage()]);
}

$mysqli->close();
?>
