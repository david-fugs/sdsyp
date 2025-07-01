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
    // Consulta mejorada para obtener estados reales de las personas
    $query = "
        SELECT 
            p.cedula_persona,
            p.nombres_persona,
            p.apellidos_persona,
            p.genero_persona,
            p.fecha_nacimiento,
            p.telefono_persona,
            p.referencia_persona,
            p.fecha_alta_persona,
            g.descripcion_grupo as centro_vida,
            pol.descripcion_politica,
             
            -- Estado actual basado en el último movimiento
            (SELECT cc.descripcion_condicion 
             FROM movimiento_persona mp 
             JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_estado_movimiento,
             
            -- Fecha del último movimiento
            (SELECT mp.fecha_movimiento 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS fecha_ultimo_movimiento,
             
            -- Traslados en el año
            (SELECT COUNT(*)
             FROM movimiento_persona mp2
             JOIN condiciones_componente cc2 ON mp2.id_condicion = cc2.id_condicion
             WHERE mp2.cedula_persona = p.cedula_persona
             AND cc2.descripcion_condicion LIKE '%TRASLADADO%'
             AND YEAR(mp2.fecha_movimiento) = ?) AS traslados_en_year,
             
            -- Último centro de traslado
            (SELECT g2.descripcion_grupo
             FROM movimiento_persona mp3
             JOIN condiciones_componente cc3 ON mp3.id_condicion = cc3.id_condicion
             LEFT JOIN grupos g2 ON mp3.id_centro_vida_traslado = g2.id_grupo
             WHERE mp3.cedula_persona = p.cedula_persona
             AND cc3.descripcion_condicion LIKE '%TRASLADADO%'
             ORDER BY mp3.fecha_movimiento DESC
             LIMIT 1) AS ultimo_centro_traslado,
             
            -- Total de movimientos en el año
            (SELECT COUNT(*)
             FROM movimiento_persona mp4
             WHERE mp4.cedula_persona = p.cedula_persona
             AND YEAR(mp4.fecha_movimiento) = ?) AS movimientos_en_year,
             
            -- Edad calculada
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
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $year, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        // Determinar el estado actual basado en el último movimiento
        $estado_actual = 'ACTIVO'; // Estado por defecto
        
        if ($row['ultimo_estado_movimiento']) {
            $ultimo_estado = strtoupper($row['ultimo_estado_movimiento']);
            
            // Mapear estados según condiciones - mejorado para capturar más casos
            if (strpos($ultimo_estado, 'EVADIDO') !== false || 
                strpos($ultimo_estado, 'EVASION') !== false ||
                strpos($ultimo_estado, 'FUGA') !== false) {
                $estado_actual = 'EVADIDO';
            } elseif (strpos($ultimo_estado, 'FALLECIDO') !== false || 
                      strpos($ultimo_estado, 'MUERTE') !== false ||
                      strpos($ultimo_estado, 'DEFUNCION') !== false) {
                $estado_actual = 'FALLECIDO';
            } elseif (strpos($ultimo_estado, 'RETIRADO') !== false || 
                      strpos($ultimo_estado, 'RETIRO') !== false ||
                      strpos($ultimo_estado, 'SALIDA') !== false) {
                $estado_actual = 'RETIRADO VOLUNTARIO';
            } elseif (strpos($ultimo_estado, 'TRASLADADO') !== false || 
                      strpos($ultimo_estado, 'TRASLADO') !== false) {
                $estado_actual = 'TRASLADADO';
            } elseif (strpos($ultimo_estado, 'SUSPENDIDO') !== false || 
                      strpos($ultimo_estado, 'SUSPENSION') !== false) {
                $estado_actual = 'SUSPENDIDO';
            } elseif (strpos($ultimo_estado, 'ACTIVO') !== false || 
                      strpos($ultimo_estado, 'INGRESO') !== false ||
                      strpos($ultimo_estado, 'ACTIVACION') !== false) {
                $estado_actual = 'ACTIVO';
            } else {
                // Si no coincide con ningún patrón conocido, usar el estado original
                $estado_actual = $ultimo_estado;
            }
        }
        
        // Formatear fecha de nacimiento
        $fecha_nacimiento_formatted = '';
        if ($row['fecha_nacimiento'] && $row['fecha_nacimiento'] != '0000-00-00') {
            $fecha_nacimiento_formatted = date('d/m/Y', strtotime($row['fecha_nacimiento']));
        }
        
        // Formatear fecha "Activo Desde" (usar fecha_alta_persona)
        $activo_desde_formatted = 'No registrada';
        if ($row['fecha_alta_persona'] && $row['fecha_alta_persona'] != '0000-00-00') {
            $activo_desde_formatted = date('d/m/Y', strtotime($row['fecha_alta_persona']));
        }
        
        // Formatear fecha del último estado
        $fecha_ultimo_estado_formatted = '';
        if ($row['fecha_ultimo_movimiento']) {
            $fecha_ultimo_estado_formatted = date('d/m/Y', strtotime($row['fecha_ultimo_movimiento']));
        }

        // Determinar "ACTIVO HASTA" según el estado
        $activo_hasta = '';
        if ($estado_actual == 'FALLECIDO' || $estado_actual == 'EVADIDO') {
            // Para fallecidos y evadidos, mostrar la fecha del último movimiento
            $activo_hasta = $fecha_ultimo_estado_formatted ?: 'No registrada';
        } elseif ($estado_actual == 'TRASLADADO') {
            // Para trasladados, mostrar el centro de destino
            $activo_hasta = $row['ultimo_centro_traslado'] ? 'Trasladado a: ' . $row['ultimo_centro_traslado'] : 'Traslado sin destino registrado';
        } elseif ($estado_actual == 'RETIRADO VOLUNTARIO') {
            // Para retirados, mostrar la fecha de retiro
            $activo_hasta = $fecha_ultimo_estado_formatted ?: 'No registrada';
        } else {
            // Para activos y otros estados, mostrar "N/A"
            $activo_hasta = 'N/A';
        }

        // Obtener programas de la persona - verificar si la tabla existe
        $programas = 'Sin programa';
        
        // Verificar si la tabla persona_programa existe
        $table_check = $mysqli->query("SHOW TABLES LIKE 'persona_programa'");
        if ($table_check && $table_check->num_rows > 0) {
            $programas_query = "SELECT GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas
                               FROM persona_programa pp
                               JOIN programas pr ON pp.id_programa = pr.id_programa
                               WHERE pp.cedula_persona = ?";
            $stmt_prog = $mysqli->prepare($programas_query);
            $stmt_prog->bind_param("s", $row['cedula_persona']);
            $stmt_prog->execute();
            $result_prog = $stmt_prog->get_result();
            $programas_row = $result_prog->fetch_assoc();
            $programas = $programas_row['programas'] ?: 'Sin programa';
        } else {
            // Si no existe la tabla, usar el centro de vida como programa
            $programas = $row['centro_vida'] ?: 'Sin programa';
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
            'fecha_registro' => $activo_desde_formatted, // "Activo Desde"
            'centro_vida' => $row['centro_vida'] ?: 'No asignado',
            'descripcion_politica' => $row['descripcion_politica'] ?: 'No asignada',
            'programas' => $programas,
            'estado_actual' => $estado_actual, // Estado real basado en movimientos
            'fecha_ultimo_estado' => $fecha_ultimo_estado_formatted,
            'activo_hasta' => $activo_hasta, // Nueva columna "ACTIVO HASTA"
            'traslados_en_year' => $row['traslados_en_year'],
            'ultimo_centro_traslado' => $row['ultimo_centro_traslado'] ?: 'N/A',
            'movimientos_en_year' => $row['movimientos_en_year']
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
