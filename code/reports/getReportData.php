<?php
session_start();
require_once('../filtros_grupos.php');
include("../../conexion.php");

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Aplicar filtro de grupos según tipo de usuario (tipos 3, 4, 5 y 10)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

header('Content-Type: application/json');


// Verificar que se haya proporcionado el año
if (!isset($_GET['year']) || empty($_GET['year'])) {
    echo json_encode(['error' => 'Año no proporcionado']);
    exit;
}

$year = intval($_GET['year']);

// Obtener filtros
$filtro_grupo = isset($_GET['filtro_grupo']) && !empty($_GET['filtro_grupo']) ? intval($_GET['filtro_grupo']) : null;
$filtro_mes = isset($_GET['filtro_mes']) && !empty($_GET['filtro_mes']) ? $_GET['filtro_mes'] : null;
$filtro_usuario = isset($_GET['filtro_usuario']) && !empty($_GET['filtro_usuario']) ? intval($_GET['filtro_usuario']) : null;

// Verificar conexión a la base de datos
if ($mysqli->connect_error) {
    echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $mysqli->connect_error]);
    exit;
}

try {
    // Consulta mejorada para obtener todos los campos de personas y movimientos (consulta plana)
$where = "WHERE p.estado_persona = 1";
if ($tipo_usuario != 1 && $id_grupo_session && $tipo_usuario != 3 && $tipo_usuario != 10) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

// Si es usuario tipo 3 (CONTRATISTA CPSAM), filtrar solo las personas que ha registrado
if ($tipo_usuario == 3 && isset($_SESSION['id'])) {
    $id_usuario_session = intval($_SESSION['id']);
    $where .= " AND p.id_usuario = $id_usuario_session ";
}

// Aplicar filtro adicional para usuarios técnicos y contratistas (tipos 3, 4, 5 y 10)
$where .= $where_grupos_filtro;

// Aplicar filtro por grupo específico si se seleccionó uno
if ($filtro_grupo !== null) {
    $where .= " AND p.id_grupo = " . intval($filtro_grupo);
}

// Aplicar filtro por usuario específico si se seleccionó uno
if ($filtro_usuario !== null) {
    $where .= " AND p.id_usuario = " . intval($filtro_usuario);
}

// Aplicar filtro de mes en movimientos si se seleccionó uno
// Filtrar personas que tengan al menos un movimiento en el año y mes especificado
if ($filtro_mes !== null) {
    $where .= " AND EXISTS (
        SELECT 1 FROM movimiento_persona mp 
        WHERE mp.cedula_persona = p.cedula_persona 
        AND YEAR(mp.fecha_movimiento) = " . intval($year) . "
        AND MONTH(mp.fecha_movimiento) = " . intval($filtro_mes) . "
    )";
}

$query = "
    SELECT 
        p.*,
        g.descripcion_grupo as centro_vida,
        pol.descripcion_politica,
        b.nombre_bar as barrio_nombre,
        c.nombre_com as comuna_nombre,
        (SELECT cc.descripcion_condicion 
         FROM movimiento_persona mp 
         JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
         WHERE mp.cedula_persona = p.cedula_persona 
         ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
         LIMIT 1) AS ultimo_estado_movimiento,
        (SELECT mp.fecha_movimiento 
         FROM movimiento_persona mp 
         WHERE mp.cedula_persona = p.cedula_persona 
         ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
         LIMIT 1) AS fecha_ultimo_movimiento,
        (SELECT m.descripcion_meta 
         FROM movimiento_persona mp 
         LEFT JOIN metas m ON mp.id_meta = m.id_meta
         WHERE mp.cedula_persona = p.cedula_persona 
         ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
         LIMIT 1) AS ultima_meta,
        (SELECT a.descripcion_actividad 
         FROM movimiento_persona mp 
         LEFT JOIN actividades a ON mp.id_actividad = a.id_actividad
         WHERE mp.cedula_persona = p.cedula_persona 
         ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
         LIMIT 1) AS ultima_actividad,
        (SELECT ac.descripcion_accion 
         FROM movimiento_persona mp 
         LEFT JOIN acciones ac ON mp.id_accion = ac.id_accion
         WHERE mp.cedula_persona = p.cedula_persona 
         ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
         LIMIT 1) AS ultima_accion,
        (SELECT mp.departamento_procedencia 
         FROM movimiento_persona mp 
         WHERE mp.cedula_persona = p.cedula_persona 
         ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
         LIMIT 1) AS ultimo_departamento_procedencia,
        CASE 
            WHEN p.fecha_alta_persona IS NOT NULL AND p.fecha_alta_persona != '0000-00-00' THEN
                CASE
                    WHEN (SELECT cc.descripcion_condicion 
                          FROM movimiento_persona mp 
                          JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                          WHERE mp.cedula_persona = p.cedula_persona 
                          ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                          LIMIT 1) IS NOT NULL 
                         AND (UPPER((SELECT cc.descripcion_condicion 
                                    FROM movimiento_persona mp 
                                    JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                                    WHERE mp.cedula_persona = p.cedula_persona 
                                    ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                    LIMIT 1)) LIKE '%FALLECIDO%' 
                              OR UPPER((SELECT cc.descripcion_condicion 
                                       FROM movimiento_persona mp 
                                       JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                                       WHERE mp.cedula_persona = p.cedula_persona 
                                       ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                       LIMIT 1)) LIKE '%EVADIDO%'
                              OR UPPER((SELECT cc.descripcion_condicion 
                                       FROM movimiento_persona mp 
                                       JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                                       WHERE mp.cedula_persona = p.cedula_persona 
                                       ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                       LIMIT 1)) LIKE '%RETIRADO%') THEN
                        DATEDIFF((SELECT mp.fecha_movimiento 
                                 FROM movimiento_persona mp 
                                 WHERE mp.cedula_persona = p.cedula_persona 
                                 ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                 LIMIT 1), p.fecha_alta_persona)
                    ELSE DATEDIFF(CURDATE(), p.fecha_alta_persona)
                END
            ELSE NULL
        END AS dias_activos,
        (SELECT COUNT(*)
         FROM movimiento_persona mp2
         JOIN condiciones_componente cc2 ON mp2.id_condicion = cc2.id_condicion
         WHERE mp2.cedula_persona = p.cedula_persona
         AND cc2.descripcion_condicion LIKE '%TRASLADADO%'
         AND YEAR(mp2.fecha_movimiento) = " . intval($year) . "
         " . ($filtro_mes !== null ? "AND MONTH(mp2.fecha_movimiento) = " . intval($filtro_mes) : "") . ") AS traslados_en_year,
        (SELECT g2.descripcion_grupo
         FROM movimiento_persona mp3
         JOIN condiciones_componente cc3 ON mp3.id_condicion = cc3.id_condicion
         LEFT JOIN grupos g2 ON mp3.id_centro_vida_traslado = g2.id_grupo
         WHERE mp3.cedula_persona = p.cedula_persona
         AND cc3.descripcion_condicion LIKE '%TRASLADADO%'
         ORDER BY mp3.fecha_movimiento DESC
         LIMIT 1) AS ultimo_centro_traslado,
        (SELECT COUNT(*)
         FROM movimiento_persona mp4
         WHERE mp4.cedula_persona = p.cedula_persona
         AND YEAR(mp4.fecha_movimiento) = " . intval($year) . "
         " . ($filtro_mes !== null ? "AND MONTH(mp4.fecha_movimiento) = " . intval($filtro_mes) : "") . ") AS movimientos_en_year,
        CASE 
            WHEN p.fecha_nacimiento IS NOT NULL AND p.fecha_nacimiento != '0000-00-00' 
            THEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE())
            ELSE NULL 
        END AS edad_actual
    FROM personas p
    LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
    LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
    LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
    LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
    $where
    ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC
";
    $result = $mysqli->query($query);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        // Determinar el estado actual: priorizar la columna condicion_componente en personas
        $estado_actual = 'ACTIVO'; // Estado por defecto

        if (isset($row['condicion_componente']) && trim(mb_strtolower($row['condicion_componente'])) === 'visita psicosocial fallida') {
            // Si la persona ya tiene esa condición en su registro, respetarla
            $estado_actual = 'VISITA FALLIDA';
        } else {
            // Si no, basar el estado en el último movimiento cuando exista
            if ($row['ultimo_estado_movimiento']) {
                $ultimo_estado = strtoupper($row['ultimo_estado_movimiento']);

                // Mapear estados según condiciones - mejorado para capturar más casos
                if (strpos($ultimo_estado, 'VISITA PSICOSOCIAL FALLIDA') !== false) {
                    $estado_actual = 'VISITA FALLIDA';
                } elseif (
                    strpos($ultimo_estado, 'EVADIDO') !== false ||
                    strpos($ultimo_estado, 'EVASION') !== false ||
                    strpos($ultimo_estado, 'FUGA') !== false
                ) {
                    $estado_actual = 'EVADIDO';
                } elseif (
                    strpos($ultimo_estado, 'FALLECIDO') !== false ||
                    strpos($ultimo_estado, 'MUERTE') !== false ||
                    strpos($ultimo_estado, 'DEFUNCION') !== false
                ) {
                    $estado_actual = 'FALLECIDO';
                } elseif (
                    strpos($ultimo_estado, 'RETIRADO') !== false ||
                    strpos($ultimo_estado, 'RETIRO') !== false ||
                    strpos($ultimo_estado, 'SALIDA') !== false
                ) {
                    $estado_actual = 'RETIRADO VOLUNTARIO';
                } elseif (
                    strpos($ultimo_estado, 'TRASLADADO') !== false ||
                    strpos($ultimo_estado, 'TRASLADO') !== false
                ) {
                    $estado_actual = 'TRASLADADO';
                } elseif (
                    strpos($ultimo_estado, 'SUSPENDIDO') !== false ||
                    strpos($ultimo_estado, 'SUSPENSION') !== false
                ) {
                    $estado_actual = 'SUSPENDIDO';
                } elseif (
                    strpos($ultimo_estado, 'ACTIVO') !== false ||
                    strpos($ultimo_estado, 'INGRESO') !== false ||
                    strpos($ultimo_estado, 'ACTIVACION') !== false
                ) {
                    $estado_actual = 'ACTIVO';
                } else {
                    // Si no coincide con ningún patrón conocido, usar el estado original
                    $estado_actual = $ultimo_estado;
                }
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
            // Campos básicos de identificación
            'cedula_persona' => $row['cedula_persona'] ?? '',
            'nombres_persona' => $row['nombres_persona'] ?? '',
            'apellidos_persona' => $row['apellidos_persona'] ?? '',
            'genero_persona' => $row['genero_persona'] ?? '',
            'fecha_nacimiento' => $fecha_nacimiento_formatted,
            'edad_actual' => $row['edad_actual'] ?? '',

            // Información de contacto
            'telefono_persona' => $row['telefono_persona'] ?? '',
            'telefono_referencia_persona' => $row['telefono_referencia_persona'] ?? '',
            'correo_persona' => $row['correo_persona'] ?? '',
            'referencia_persona' => $row['referencia_persona'] ?? '',

            // Información demográfica
            'estado_civil_persona' => $row['estado_civil_persona'] ?? '',
            'nacionalidad_persona' => $row['nacionalidad_persona'] ?? '',
            'pais_procedencia_persona' => $row['pais_procedencia_persona'] ?? '',
            'departamento_procedencia_persona' => $row['departamento_procedencia_persona'] ?? '',
            'municipio_procedencia_persona' => $row['municipio_procedencia_persona'] ?? '',

            // Ubicación actual
            'direccion_persona' => $row['direccion_persona'] ?? '',
            'barrio_nombre' => $row['barrio_nombre'] ?? 'No asignado',
            'comuna_nombre' => $row['comuna_nombre'] ?? 'No asignada',

            // Información educativa y laboral
            'nivel_educativo_persona' => $row['nivel_educativo_persona'] ?? '',
            'profesion_persona' => $row['profesion_persona'] ?? '',
            'ocupacion_persona' => $row['ocupacion_persona'] ?? '',

            // Información familiar
            'nombre_padre' => $row['nombre_padre'] ?? '',
            'nombre_madre' => $row['nombre_madre'] ?? '',
            'nombre_conyuge' => $row['nombre_conyuge'] ?? '',
            'numero_hijos' => $row['numero_hijos'] ?? '',

            // Información médica y social
            'eps_persona' => $row['eps_persona'] ?? '',
            'regimen_salud_persona' => $row['regimen_salud_persona'] ?? '',
            'discapacidad_persona' => $row['discapacidad_persona'] ?? '',
            'tipo_discapacidad_persona' => $row['tipo_discapacidad_persona'] ?? '',
            'grupo_poblacional_persona' => $row['grupo_poblacional_persona'] ?? '',
            'situacion_juridica_persona' => $row['situacion_juridica_persona'] ?? '',

            // Fechas del sistema
            'fecha_registro' => $activo_desde_formatted, // "Activo Desde"
            'dias_activos' => $row['dias_activos'] ?? 'N/A',

            // Información institucional
            'centro_vida' => $row['centro_vida'] ?? 'No asignado',
            'descripcion_politica' => $row['descripcion_politica'] ?? 'No asignada',
            'programas' => $programas,

            // Estado y movimientos
            'estado_actual' => $estado_actual, // Estado real basado en movimientos
            'fecha_ultimo_estado' => $fecha_ultimo_estado_formatted,
            'activo_hasta' => $activo_hasta, // Nueva columna "ACTIVO HASTA"

            // Datos de movimientos recientes
            'ultima_meta' => $row['ultima_meta'] ?? 'No registrada',
            'ultima_actividad' => $row['ultima_actividad'] ?? 'No registrada',
            'ultima_accion' => $row['ultima_accion'] ?? 'No registrada',
            'ultimo_departamento_procedencia' => $row['ultimo_departamento_procedencia'] ?? 'No registrado',

            // Estadísticas del año
            'traslados_en_year' => $row['traslados_en_year'] ?? 0,
            'ultimo_centro_traslado' => $row['ultimo_centro_traslado'] ?? 'N/A',
            'movimientos_en_year' => $row['movimientos_en_year'] ?? 0,

            // Información adicional si existe
            'observaciones_persona' => $row['observaciones_persona'] ?? '',
            'documentos_persona' => $row['documentos_persona'] ?? '',
            'ingresos_economicos_persona' => $row['ingresos_economicos_persona'] ?? '',
            'sisben_persona' => $row['sisben_persona'] ?? ''
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
    $errorMsg = $e->getMessage();
    // Si hay error de MySQL, mostrarlo también
    if (isset($mysqli) && $mysqli->error) {
        $errorMsg .= ' | MySQL: ' . $mysqli->error;
    }
    echo json_encode(['error' => 'Error al obtener datos: ' . $errorMsg]);
}

$mysqli->close();
