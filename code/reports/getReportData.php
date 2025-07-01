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
    // Consulta simplificada para obtener datos de personas
    $query = "
        SELECT 
            p.cedula_persona,
            p.nombres_persona,
            p.apellidos_persona,
            p.genero_persona,
            p.fecha_nacimiento,
            p.telefono_persona,
            p.referencia_persona,
            g.descripcion_grupo as centro_vida,
            pol.descripcion_politica,
            CASE 
                WHEN p.fecha_nacimiento IS NOT NULL AND p.fecha_nacimiento != '0000-00-00' 
                THEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE())
                ELSE NULL 
            END AS edad_actual
        FROM personas p
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
        WHERE p.estado_persona = 1 
        ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC
    ";
    
    $result = $mysqli->query($query);
    
    if (!$result) {
        throw new Exception("Error en consulta: " . $mysqli->error);
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
            'edad_actual' => $row['edad_actual'],
            'telefono_persona' => $row['telefono_persona'],
            'referencia_persona' => $row['referencia_persona'],
            'fecha_registro' => date('d/m/Y'), // Fecha actual como registro
            'centro_vida' => $row['centro_vida'] ?: 'No asignado',
            'descripcion_politica' => $row['descripcion_politica'] ?: 'No asignada',
            'programas' => 'Sin programa', // Simplificado
            'estado_actual' => 'ACTIVO', // Simplificado
            'fecha_ultimo_estado' => '',
            'traslados_en_year' => 0,
            'ultimo_centro_traslado' => 'N/A',
            'movimientos_en_year' => 0
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
