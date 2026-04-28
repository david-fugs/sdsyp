<?php
// Iniciar sesión para obtener tipo_usuario
session_start();

// Eliminar cualquier salida previa
if (ob_get_length()) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Hay salida previa al header. El archivo Excel se corromperá.\n";
    echo "Verifica que no haya espacios, saltos de línea o echo/print antes de los headers.\n";
    exit;
}

// Forzar codificación UTF-8 en la conexión MySQL
require_once '../filtros_grupos.php';
require_once '../../conexion.php';
if (isset($mysqli)) {
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES 'utf8mb4'");
    $mysqli->query("SET CHARACTER SET 'utf8mb4'");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Verificar que se proporcione el año
if (!isset($_GET['year']) || empty($_GET['year'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error: Año no proporcionado';
    exit;
}

$year = intval($_GET['year']);

// Obtener filtros
$filtro_grupo = isset($_GET['filtro_grupo']) && !empty($_GET['filtro_grupo']) ? intval($_GET['filtro_grupo']) : null;
$filtro_mes = isset($_GET['filtro_mes']) && !empty($_GET['filtro_mes']) ? $_GET['filtro_mes'] : null;
$filtro_usuario = isset($_GET['filtro_usuario']) && !empty($_GET['filtro_usuario']) ? intval($_GET['filtro_usuario']) : null;
$filtro_fecha_inicio = isset($_GET['filtro_fecha_inicio']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['filtro_fecha_inicio']) ? $_GET['filtro_fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['filtro_fecha_fin']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['filtro_fecha_fin']) ? $_GET['filtro_fecha_fin'] : '';

try {

    // Crear nuevo objeto Spreadsheet
    $spreadsheet = new Spreadsheet();
    // Forzar codificación UTF-8 en Spreadsheet
    \PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Informe SDSYP ' . $year);

    // Configurar metadatos
    $spreadsheet->getProperties()
        ->setCreator('Sistema SDSYP')
        ->setTitle('Informe Completo SDSYP ' . $year)
        ->setSubject('Datos completos de personas y movimientos')
        ->setDescription('Informe generado el ' . date('Y-m-d H:i:s'));

    // Definir las cabeceras reales según la estructura de la tabla personas y los datos solicitados
    $headers = [
        'CÉDULA',
        'TIPO IDENTIFICACIÓN',
        'NOMBRES',
        'APELLIDOS',
        'FECHA NACIMIENTO',
        'TELÉFONO',
        'TELÉFONO REFERENCIA',
        'REFERENCIA',
        'GÉNERO',
        'GRUPO SISBEN',
        'PERSONA DISCAPACIDAD',
        'CUÁL DISCAPACIDAD',
        'VÍCTIMA',
        'CABEZA HOGAR',
        'LÍDER COMUNIDAD',
        'SE RECONOCE COMO',
        'ORIENTACIÓN SEXUAL',
        'EXPERIENCIA MIGRATORIA',
        'GRUPO ÉTNICO',
        'TIPO SALUD',
        'NIVEL EDUCATIVO',
        'GRUPO',
        'POLÍTICA PÚBLICA',
        'RESPONSABLE',
        'ZONA PERSONA',
        'DIRECCIÓN',
        'CORREO',
        'BARRIO',
        'COMUNA',
        'EPS',
        'PESO',
        'TALLA',
        'PATOLOGÍAS',
        'FACTORES RIESGO',
        'FACTORES PREVENTIVOS',
        'INGRESOS ECONÓMICOS',
        'CONVIVENCIA ACTUAL',
        'RESULTADO ACTIVIDAD',
        'REMISIÓN',
        'TELÉFONO REFERENCIA PERSONA',
        'CONDICIÓN OCUPACIÓN',
        'CONDICIÓN COMPONENTE',
        // Datos de movimiento y cálculos
        'CENTRO DE VIDA', 'POLÍTICA PÚBLICA',
        'ESTADO ACTUAL', 'FECHA ÚLTIMO ESTADO',
        'ÚLTIMA META', 'ÚLTIMA ACTIVIDAD', 'ÚLTIMA ACCIÓN', 'ÚLTIMO DPTO PROCEDENCIA',
        'MOVIMIENTOS AÑO', 'TRASLADOS AÑO', 'ÚLTIMO CENTRO TRASLADO',
        'ACTIVO DESDE', 'ACTIVO HASTA', 'DÍAS ACTIVOS',
        'DÍAS ACTIVOS PRESENTE AÑO',
        'FECHA ÚLTIMO CONTRATO GRUPO', 'DÍAS ACTIVO DESDE CONTRATO'
    ];

    // Escribir cabeceras en la fila 1
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    // Aplicar estilos a las cabeceras
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = 'A1:' . $lastCol . '1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 11,
            'color' => ['rgb' => '2D3436']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFF3A0'] // Amarillo muy suave
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ]);

    // Configurar altura de la fila de cabeceras
    $sheet->getRowDimension(1)->setRowHeight(40);

    // Obtener tipo de usuario y aplicar filtro de grupos
    $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
    $where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');
    
    // Aplicar filtro adicional por grupo específico si se seleccionó uno
    if ($filtro_grupo !== null) {
        $where_grupos_filtro .= " AND p.id_grupo = " . intval($filtro_grupo);
    }
    
    // Aplicar filtro por usuario específico si se seleccionó uno
    if ($filtro_usuario !== null) {
        $where_grupos_filtro .= " AND p.id_usuario = " . intval($filtro_usuario);
    }
    
    // Aplicar filtro de fecha/mes en movimientos si se seleccionó
    if ($filtro_fecha_inicio && $filtro_fecha_fin) {
        $where_grupos_filtro .= " AND EXISTS (
            SELECT 1 FROM movimiento_persona mp 
            WHERE mp.cedula_persona = p.cedula_persona 
            AND mp.fecha_movimiento BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin'
        )";
    } elseif ($filtro_mes !== null) {
        $where_grupos_filtro .= " AND EXISTS (
            SELECT 1 FROM movimiento_persona mp 
            WHERE mp.cedula_persona = p.cedula_persona 
            AND YEAR(mp.fecha_movimiento) = " . intval($year) . "
            AND MONTH(mp.fecha_movimiento) = " . intval($filtro_mes) . "
        )";
    }
    
    // Filtro para usuarios tipo 3 (CONTRATISTA): solo exportar personas que haya registrado
    if ($tipo_usuario == 3 && isset($_SESSION['id'])) {
        $id_usuario_session = intval($_SESSION['id']);
        $where_grupos_filtro .= " AND p.id_usuario = $id_usuario_session ";
    }

    // Consulta completa para obtener todos los datos
    $query = "
        SELECT 
            p.*,
            g.descripcion_grupo as centro_vida,
            b.nombre_bar as barrio_nombre,
            c.nombre_com as comuna_nombre,
            u.nombre as nombre_usuario,

            -- Fecha más reciente de contratación del grupo
            (SELECT hfc.fecha_contratacion
             FROM historial_fechas_contratacion hfc
             WHERE hfc.id_grupo = p.id_grupo
             ORDER BY hfc.fecha_contratacion DESC
             LIMIT 1) AS fecha_ultimo_contrato_grupo,

            -- Descripción de la política pública del último movimiento
            (SELECT pol.descripcion_politica
             FROM movimiento_persona mp
             LEFT JOIN politicas_publicas pol ON mp.id_politica_publica = pol.id_politica
             WHERE mp.cedula_persona = p.cedula_persona
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS descripcion_politica,
            -- Último estado/condición del movimiento
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
             
            -- Última meta, actividad y acción
            (SELECT m.descripcion_meta 
             FROM movimiento_persona mp 
             LEFT JOIN metas m ON mp.id_meta = m.id_meta
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_meta,
            (SELECT mp.id_meta 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_meta_id,
             
            (SELECT a.descripcion_actividad 
             FROM movimiento_persona mp 
             LEFT JOIN actividades a ON mp.id_actividad = a.id_actividad
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_actividad,
            (SELECT mp.id_actividad 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_actividad_id,
             
            (SELECT ac.descripcion_accion 
             FROM movimiento_persona mp 
             LEFT JOIN acciones ac ON mp.id_accion = ac.id_accion
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_accion,
            (SELECT mp.id_accion 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_accion_id,
             
            (SELECT mp.departamento_procedencia 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_departamento_procedencia,

            -- Centro de vida anterior y actual del último movimiento
            (SELECT g_ant.descripcion_grupo
             FROM movimiento_persona mp
             LEFT JOIN grupos g_ant ON mp.id_centro_vida_traslado_anterior = g_ant.id_grupo
             WHERE mp.cedula_persona = p.cedula_persona
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_centro_traslado_anterior,
            (SELECT g_act.descripcion_grupo
             FROM movimiento_persona mp
             LEFT JOIN grupos g_act ON mp.id_centro_vida_traslado = g_act.id_grupo
             WHERE mp.cedula_persona = p.cedula_persona
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_centro_traslado_actual,

            -- Estadísticas de movimientos en el período
            (SELECT COUNT(*)
             FROM movimiento_persona mp2
             WHERE mp2.cedula_persona = p.cedula_persona
             " . ($filtro_fecha_inicio && $filtro_fecha_fin ? "AND mp2.fecha_movimiento BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin'" : ("AND YEAR(mp2.fecha_movimiento) = ?" . ($filtro_mes !== null ? " AND MONTH(mp2.fecha_movimiento) = " . intval($filtro_mes) : ""))) . ") AS movimientos_en_year,
             
            -- Traslados en el período
            (SELECT COUNT(*)
             FROM movimiento_persona mp3
             JOIN condiciones_componente cc3 ON mp3.id_condicion = cc3.id_condicion
             WHERE mp3.cedula_persona = p.cedula_persona
             AND cc3.descripcion_condicion LIKE '%TRASLADADO%'
             " . ($filtro_fecha_inicio && $filtro_fecha_fin ? "AND mp3.fecha_movimiento BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin'" : ("AND YEAR(mp3.fecha_movimiento) = ?" . ($filtro_mes !== null ? " AND MONTH(mp3.fecha_movimiento) = " . intval($filtro_mes) : ""))) . ") AS traslados_en_year,

            -- Último centro de traslado (para compatibilidad)
            (SELECT g2.descripcion_grupo
             FROM movimiento_persona mp4
             JOIN condiciones_componente cc4 ON mp4.id_condicion = cc4.id_condicion
             LEFT JOIN grupos g2 ON mp4.id_centro_vida_traslado = g2.id_grupo
             WHERE mp4.cedula_persona = p.cedula_persona
             AND cc4.descripcion_condicion LIKE '%TRASLADADO%'
             ORDER BY mp4.fecha_movimiento DESC
             LIMIT 1) AS ultimo_centro_traslado,
             
            -- Cálculo de edad actual
            CASE 
                WHEN p.fecha_nacimiento IS NOT NULL AND p.fecha_nacimiento != '0000-00-00' 
                THEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE())
                ELSE NULL 
            END AS edad_actual,
            
            -- Cálculo de días activos
            CASE 
                WHEN p.activo_desde IS NOT NULL AND p.activo_desde != '0000-00-00' THEN
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
                                     LIMIT 1), p.activo_desde)
                        ELSE DATEDIFF(CURDATE(), p.activo_desde)
                    END
                ELSE NULL
            END AS dias_activos
            
        FROM personas p
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
        LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
        LEFT JOIN usuarios u ON p.id_usuario = u.id
        WHERE p.estado_persona = 1 $where_grupos_filtro
        ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC
    ";

    $stmt = $mysqli->prepare($query);
    if (!($filtro_fecha_inicio && $filtro_fecha_fin)) {
        $stmt->bind_param("ii", $year, $year);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Prefetch all persons into array to allow bulk prefetch of movimientos
    $persons = $result->fetch_all(MYSQLI_ASSOC);

    // Map grupos id => descripcion for lookups
    $gruposMap = [];
    $resG = $mysqli->query("SELECT id_grupo, descripcion_grupo FROM grupos");
    if ($resG) {
        while ($g = $resG->fetch_assoc()) {
            $gruposMap[$g['id_grupo']] = $g['descripcion_grupo'];
        }
    }

    // Prefetch metas, actividades y acciones para fallback desde campos en la tabla personas
    $metasMap = [];
    $resM = $mysqli->query("SELECT id_meta, descripcion_meta FROM metas");
    if ($resM) {
        while ($m = $resM->fetch_assoc()) {
            $metasMap[$m['id_meta']] = $m['descripcion_meta'];
        }
    }

    $actividadesMap = [];
    $resA = $mysqli->query("SELECT id_actividad, descripcion_actividad FROM actividades");
    if ($resA) {
        while ($a = $resA->fetch_assoc()) {
            $actividadesMap[$a['id_actividad']] = $a['descripcion_actividad'];
        }
    }

    $accionesMap = [];
    $resAc = $mysqli->query("SELECT id_accion, descripcion_accion FROM acciones");
    if ($resAc) {
        while ($ac = $resAc->fetch_assoc()) {
            $accionesMap[$ac['id_accion']] = $ac['descripcion_accion'];
        }
    }

    // Prefetch all movimientos for these persons in one query
    $movimientosByCedula = [];
    if (count($persons) > 0) {
        $cedulas = array_map(function ($p) {
            return $p['cedula_persona'];
        }, $persons);
        $escaped = array_map(function ($v) use ($mysqli) {
            return "'" . $mysqli->real_escape_string($v) . "'";
        }, $cedulas);
        $in = implode(',', $escaped);
        $movQ = "SELECT * FROM movimiento_persona WHERE cedula_persona IN ($in) ORDER BY cedula_persona, fecha_movimiento ASC, id_movimiento_persona ASC";
        $resMov = $mysqli->query($movQ);
        if ($resMov) {
            while ($m = $resMov->fetch_assoc()) {
                $movimientosByCedula[$m['cedula_persona']][] = $m;
            }
        }
    }

    // OPTIONAL: if you create a precomputed table 'persona_traslados' we will prefer it.
    // This table should include: id_persona_traslado, cedula_persona, id_movimiento_persona,
    // fecha_movimiento, id_grupo_anterior, id_grupo_nuevo, periodo_inicio (opt), periodo_fin (opt)
    $trasladosByCedula = [];
    $tableCheckTras = $mysqli->query("SHOW TABLES LIKE 'persona_traslados'");
    if ($tableCheckTras && $tableCheckTras->num_rows > 0 && count($persons) > 0) {
        $trasQ = "SELECT * FROM persona_traslados WHERE cedula_persona IN ($in) ORDER BY cedula_persona, fecha_movimiento ASC, id_persona_traslado ASC";
        $resTras = $mysqli->query($trasQ);
        if ($resTras) {
            while ($t = $resTras->fetch_assoc()) {
                $trasladosByCedula[$t['cedula_persona']][] = $t;
            }
        }
    }

    $row_num = 2; // Empezar en la fila 2 (después de las cabeceras)
    $total_registros = 0;

    foreach ($persons as $row) {
        $total_registros++;

        // Determinar el estado actual basado en el último movimiento o condicion_componente
        $estado_actual = 'ACTIVO';
        if (isset($row['condicion_componente']) && trim(mb_strtolower($row['condicion_componente'])) === 'usuario interesado') {
            $estado_actual = 'Usuario Interesado';
        } elseif (!empty($row['ultimo_estado_movimiento'])) {
            $ultimo_estado = strtoupper($row['ultimo_estado_movimiento']);
            if (strpos($ultimo_estado, 'EVADIDO') !== false || strpos($ultimo_estado, 'EVASION') !== false) {
                $estado_actual = 'EVADIDO';
            } elseif (strpos($ultimo_estado, 'FALLECIDO') !== false || strpos($ultimo_estado, 'MUERTE') !== false) {
                $estado_actual = 'FALLECIDO';
            } elseif (strpos($ultimo_estado, 'RETIRADO') !== false || strpos($ultimo_estado, 'RETIRO') !== false) {
                $estado_actual = 'RETIRADO VOLUNTARIO';
            } elseif (strpos($ultimo_estado, 'TRASLADADO') !== false || strpos($ultimo_estado, 'TRASLADO') !== false) {
                $estado_actual = 'ACTIVO (TRASLADADO)';
            } elseif (strpos($ultimo_estado, 'SUSPENDIDO') !== false || strpos($ultimo_estado, 'SUSPENSION') !== false) {
                $estado_actual = 'SUSPENDIDO';
            } elseif (strpos($ultimo_estado, 'ACTIVO') !== false || strpos($ultimo_estado, 'INGRESO') !== false) {
                $estado_actual = 'ACTIVO';
            } else {
                $estado_actual = $ultimo_estado;
            }
        }

        // Formatear fechas
        $fecha_nacimiento = '';
        if (!empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] != '0000-00-00') {
            $fecha_nacimiento = date('d/m/Y', strtotime($row['fecha_nacimiento']));
        }

        $activo_desde_raw = (!empty($row['activo_desde']) && $row['activo_desde'] != '0000-00-00') ? $row['activo_desde'] : null;
        $fecha_ultimo_estado = '';
        if (!empty($row['fecha_ultimo_movimiento'])) {
            $fecha_ultimo_estado = date('d/m/Y', strtotime($row['fecha_ultimo_movimiento']));
        }

        // Obtener programas
        $programas = 'Sin programa';
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
            $programas = $row['centro_vida'] ?: 'Sin programa';
        }

        // Movimientos de la persona (prefetched)
        // Preferimos la tabla persona_traslados si existe; de lo contrario usamos movimiento_persona
        $movs = [];
        if (!empty($trasladosByCedula[$row['cedula_persona']])) {
            // Map persona_traslados a la misma forma esperada (fecha_movimiento, id_centro_vida_traslado_anterior, id_centro_vida_traslado)
            foreach ($trasladosByCedula[$row['cedula_persona']] as $t) {
                $movs[] = [
                    'fecha_movimiento' => $t['fecha_movimiento'],
                    'id_centro_vida_traslado_anterior' => $t['id_grupo_anterior'] ?? null,
                    'id_centro_vida_traslado' => $t['id_grupo_nuevo'] ?? null,
                    'id_movimiento_persona' => $t['id_movimiento_persona'] ?? null
                ];
            }
        } else {
            $movs = $movimientosByCedula[$row['cedula_persona']] ?? [];
        }

        // Detectar si hay traslados entre los movimientos
        $hasTraslados = false;
        foreach ($movs as $m) {
            if (!empty($m['id_centro_vida_traslado'])) {
                $hasTraslados = true;
                break;
            }
        }

        // Si hay traslados, generamos segmentos por cada periodo en un centro
        $segments = [];
        // Si la persona tiene condicion_componente = 'Visita psicosocial fallida', entonces
        // no generamos segmentos ni calculamos 'Activo Desde'/'Activo Hasta'/'DÍAS ACTIVOS'.
        // Solo escribiremos una fila con los datos disponibles y el estado 'VISITA FALLIDA'.
        $isVisitaFallida = (isset($row['condicion_componente']) && trim(mb_strtolower($row['condicion_componente'])) === 'visita psicosocial fallida');
        if ($isVisitaFallida) {
            $estado_actual = 'VISITA FALLIDA';
            // single empty segment to write one row with blanks for fechas/días
            $segments[] = [
                'start' => '',
                'end' => '',
                'centro_desc' => $row['centro_vida'] ?? ''
            ];
        } else {
            if ($hasTraslados) {
                // start from activo_desde if exists, otherwise from first movement date or today
                if ($activo_desde_raw) {
                    $current_start = $activo_desde_raw;
                } elseif (count($movs) > 0) {
                    $current_start = $movs[0]['fecha_movimiento'];
                } else {
                    $current_start = date('Y-m-d');
                }
                // initial group id and description:
                // prefer the earliest traslado's 'id_centro_vida_traslado_anterior' if present (this represents the group before the first traslado)
                $current_group_id = null;
                if (count($movs) > 0) {
                    foreach ($movs as $mv) {
                        if (!empty($mv['id_centro_vida_traslado_anterior'])) {
                            $current_group_id = $mv['id_centro_vida_traslado_anterior'];
                            break; // use the earliest traslado anterior found
                        }
                    }
                }
                // fallback to persona's current group id if we couldn't deduce a previous group
                if (empty($current_group_id) && !empty($row['id_grupo'])) {
                    $current_group_id = $row['id_grupo'];
                }
                $current_group_desc = isset($gruposMap[$current_group_id]) ? $gruposMap[$current_group_id] : ($row['centro_vida'] ?? '');

                foreach ($movs as $mov) {
                    // treat as traslado when id_centro_vida_traslado is set
                    if (!empty($mov['id_centro_vida_traslado'])) {
                        $segment_end = $mov['fecha_movimiento'];
                        // Prefer explicit anterior id from movimiento/traslado when available
                        $pre_centro_id = !empty($mov['id_centro_vida_traslado_anterior']) ? $mov['id_centro_vida_traslado_anterior'] : $current_group_id;
                        $segments[] = [
                            'start' => $current_start,
                            'end' => $segment_end,
                            'centro_id' => $pre_centro_id
                        ];
                        // next segment starts the day after the movimiento
                        $current_start = date('Y-m-d', strtotime($mov['fecha_movimiento'] . ' +1 day'));
                        // update current_group_id to the target group for next segment
                        $current_group_id = $mov['id_centro_vida_traslado'];
                    }
                }
                // push final segment until today
                $segments[] = [
                    'start' => $current_start,
                    'end' => date('Y-m-d'),
                    'centro_id' => $current_group_id
                ];
            } else {
                // no traslados -> single segment using existing activo_desde/activo_hasta logic
                $start = $activo_desde_raw ?: ($row['fecha_ultimo_movimiento'] ?: date('Y-m-d'));
                // compute activo_hasta similarly to existing logic
                $activo_hasta_calc = '';
                if ($estado_actual == 'FALLECIDO' || $estado_actual == 'EVADIDO') {
                    $activo_hasta_calc = $fecha_ultimo_estado ?: 'No registrada';
                } else {
                    $activo_hasta_calc = 'N/A';
                }
                $segments[] = [
                    'start' => $start,
                    'end' => ($activo_hasta_calc === 'N/A' ? date('Y-m-d') : $row['fecha_ultimo_movimiento']),
                    'centro_desc' => $row['centro_vida'] ?? ''
                ];
            }
        }

        // For each segment, write a row in the spreadsheet
            $segCount = count($segments);
            $iSeg = 0;
            foreach ($segments as $seg) {
                $iSeg++;
            // calculate inclusive days in segment
            $dias_seg = '';
                try {
                    // When start or end are empty (e.g., visita fallida case), DateTime will throw; handle below
                    if (!empty($seg['start']) && !empty($seg['end'])) {
                        $ds = new DateTime($seg['start']);
                        $de = new DateTime($seg['end']);
                        $interval = $ds->diff($de);
                        $days = isset($interval->days) ? $interval->days : 0;
                        $dias_seg = $days + 1;
                    } else {
                        $dias_seg = '';
                    }
                } catch (Exception $e) {
                    $dias_seg = '';
                }

            // Días activos por segmento: para el segmento final contar hasta hoy, para segmentos previos usar duración del segmento
            if (isset($isVisitaFallida) && $isVisitaFallida) {
                // Requerimiento: cuando condicion_componente == 'Visita psicosocial fallida', DÍAS ACTIVOS debe ser 0
                $dias_activos_val = 0;
            } elseif (mb_strtolower($estado_actual) === 'usuario interesado') {
                $dias_activos_val = 'N/A';
            } else {
                // If this is the last segment (current), count days from segment start to today; else use segment length
                $isLast = ($iSeg === $segCount);
                if ($isLast) {
                    try {
                        $ds2 = new DateTime($seg['start']);
                        $de2 = new DateTime(); // today
                        $interval2 = $ds2->diff($de2);
                        $dias_activos_val = (isset($interval2->days) ? $interval2->days : 0) + 1;
                    } catch (Exception $e) {
                        $dias_activos_val = '';
                    }
                } else {
                    $dias_activos_val = $dias_seg;
                }
            }

            // Calcular DÍAS ACTIVOS PRESENTE AÑO
            $dias_activos_presente_year = '';
            
            if (!$isVisitaFallida && mb_strtolower($estado_actual) !== 'usuario interesado') {
                try {
                    $year_inicio = new DateTime($year . '-01-01');
                    $year_fin = new DateTime($year . '-12-31');
                    $hoy = new DateTime();
                    
                    // Determinar fecha de inicio para el cálculo
                    $fecha_inicio_calculo = null;
                    if (!empty($seg['start'])) {
                        $fecha_activo_desde = new DateTime($seg['start']);
                        
                        // Si activo_desde es menor al año actual, usar 1/1 del año
                        if ($fecha_activo_desde < $year_inicio) {
                            $fecha_inicio_calculo = $year_inicio;
                        } else {
                            $fecha_inicio_calculo = $fecha_activo_desde;
                        }
                    }
                    
                    // Determinar fecha de fin para el cálculo
                    $fecha_fin_calculo = null;
                    if (!empty($seg['end'])) {
                        $fecha_activo_hasta = new DateTime($seg['end']);
                        
                        // Si es el último segmento (activo), usar la fecha menor entre hoy y fin de año
                        $isLast = ($iSeg === $segCount);
                        if ($isLast) {
                            $fecha_fin_calculo = ($hoy < $year_fin) ? $hoy : $year_fin;
                        } else {
                            // Para segmentos pasados, usar la fecha de fin del segmento
                            $fecha_fin_calculo = ($fecha_activo_hasta < $year_fin) ? $fecha_activo_hasta : $year_fin;
                        }
                    }
                    
                    // Calcular diferencia si ambas fechas están definidas
                    if ($fecha_inicio_calculo && $fecha_fin_calculo) {
                        // Solo calcular si el periodo intersecta con el año actual
                        if ($fecha_inicio_calculo <= $year_fin && $fecha_fin_calculo >= $year_inicio) {
                            $interval_year = $fecha_inicio_calculo->diff($fecha_fin_calculo);
                            $dias_activos_presente_year = (isset($interval_year->days) ? $interval_year->days : 0) + 1;
                        } else {
                            $dias_activos_presente_year = 0;
                        }
                    }
                } catch (Exception $e) {
                    $dias_activos_presente_year = '';
                }
            } elseif ($isVisitaFallida) {
                $dias_activos_presente_year = 0;
            } else {
                $dias_activos_presente_year = 'N/A';
            }

            // Calcular días activos desde el contrato
            $dias_activo_desde_contrato = '';
            $fecha_ultimo_contrato_mostrar = '';
            
            if (!empty($row['fecha_ultimo_contrato_grupo'])) {
                $fecha_ultimo_contrato_mostrar = date('d/m/Y', strtotime($row['fecha_ultimo_contrato_grupo']));

                // Si activo_desde es POSTERIOR a fecha_ultimo_contrato, usar el mismo valor que DÍAS ACTIVOS
                try {
                    $usar_dias_activos = false;
                    
                    if (!empty($row['activo_desde']) && $row['activo_desde'] != '0000-00-00') {
                        try {
                            $fecha_activo_desde = new DateTime($row['activo_desde']);
                            $fecha_contrato = new DateTime($row['fecha_ultimo_contrato_grupo']);
                            
                            // Si activo_desde es POSTERIOR al contrato, usar DÍAS ACTIVOS
                            if ($fecha_activo_desde > $fecha_contrato) {
                                $usar_dias_activos = true;
                            }
                        } catch (Exception $innerEx) {
                            // Error al parsear fechas, usar cálculo normal
                        }
                    }
                    
                    if ($usar_dias_activos) {
                        // Usar el mismo valor que DÍAS ACTIVOS
                        $dias_activo_desde_contrato = $dias_activos_val;
                    } else {
                        // Calcular desde fecha_ultimo_contrato_grupo
                        $fecha_inicio_calculo = new DateTime($row['fecha_ultimo_contrato_grupo']);
                        
                        // Determinar fecha final para el cálculo
                        $fecha_final = null;
                        if ($estado_actual == 'FALLECIDO' || $estado_actual == 'EVADIDO' || $estado_actual == 'RETIRADO VOLUNTARIO') {
                            // Si hay fecha de último movimiento, usar esa
                            if (!empty($row['fecha_ultimo_movimiento'])) {
                                $fecha_final = new DateTime($row['fecha_ultimo_movimiento']);
                            }
                        }
                        
                        // Si no hay fecha final específica, usar hoy
                        if ($fecha_final === null) {
                            $fecha_final = new DateTime();
                        }
                        
                        // Calcular diferencia (sumando +1 para hacer el cálculo inclusivo, igual que DÍAS ACTIVOS)
                        $interval_contrato = $fecha_inicio_calculo->diff($fecha_final);
                        $dias_activo_desde_contrato = (isset($interval_contrato->days) ? $interval_contrato->days : 0) + 1;
                    }

                } catch (Exception $e) {
                    $dias_activo_desde_contrato = '';
                }
            }

            // Calcular descripciones finales para última meta/actividad/acción (prioridad: movimiento.descripcion -> movimiento.id -> persona.id -> 'No registrada')
            $ultima_meta_desc = 'No registrada';
            if (!empty($row['ultima_meta'])) {
                $ultima_meta_desc = $row['ultima_meta'];
            } elseif (!empty($row['ultima_meta_id']) && isset($metasMap[$row['ultima_meta_id']])) {
                $ultima_meta_desc = $metasMap[$row['ultima_meta_id']];
            } elseif (!empty($row['id_meta']) && isset($metasMap[$row['id_meta']])) {
                $ultima_meta_desc = $metasMap[$row['id_meta']];
            }

            $ultima_actividad_desc = 'No registrada';
            if (!empty($row['ultima_actividad'])) {
                $ultima_actividad_desc = $row['ultima_actividad'];
            } elseif (!empty($row['ultima_actividad_id']) && isset($actividadesMap[$row['ultima_actividad_id']])) {
                $ultima_actividad_desc = $actividadesMap[$row['ultima_actividad_id']];
            } elseif (!empty($row['id_actividad']) && isset($actividadesMap[$row['id_actividad']])) {
                $ultima_actividad_desc = $actividadesMap[$row['id_actividad']];
            }

            $ultima_accion_desc = 'No registrada';
            if (!empty($row['ultima_accion'])) {
                $ultima_accion_desc = $row['ultima_accion'];
            } elseif (!empty($row['ultima_accion_id']) && isset($accionesMap[$row['ultima_accion_id']])) {
                $ultima_accion_desc = $accionesMap[$row['ultima_accion_id']];
            } elseif (!empty($row['id_accion']) && isset($accionesMap[$row['id_accion']])) {
                $ultima_accion_desc = $accionesMap[$row['id_accion']];
            }

            $data = [
                $row['cedula_persona'] ?? '',
                $row['tipo_identificacion'] ?? '',
                $row['nombres_persona'] ?? '',
                $row['apellidos_persona'] ?? '',
                $fecha_nacimiento,
                $row['telefono_persona'] ?? '',
                $row['telefono_referencia_persona'] ?? '',
                $row['referencia_persona'] ?? '',
                $row['genero_persona'] ?? '',
                $row['grupo_sisben'] ?? '',
                $row['persona_discapacidad'] ?? '',
                $row['cual_discapacidad'] ?? '',
                $row['victima'] ?? '',
                $row['cabeza_hogar'] ?? '',
                $row['lider_comunidad'] ?? '',
                $row['se_reconoce_como'] ?? '',
                $row['orientacion_sexual'] ?? '',
                $row['experiencia_migratoria'] ?? '',
                $row['grupo_etnico'] ?? '',
                $row['tipo_salud'] ?? '',
                $row['nivel_educativo'] ?? '',
                // Resolve centro description from centro_id if present
                (isset($seg['centro_id']) ? (isset($gruposMap[$seg['centro_id']]) ? $gruposMap[$seg['centro_id']] : ($row['centro_vida'] ?? 'No asignado')) : ($seg['centro_desc'] ?? ($row['centro_vida'] ?? 'No asignado'))),
                $row['descripcion_politica'] ?? 'No asignada',
                $row['nombre_usuario'] ?? '',
                $row['zona_persona'] ?? '',
                $row['direccion_persona'] ?? '',
                $row['correo_persona'] ?? '',
                $row['barrio_nombre'] ?? '',
                $row['comuna_nombre'] ?? '',
                $row['eps'] ?? '',
                $row['peso'] ?? '',
                $row['talla'] ?? '',
                $row['patologias'] ?? '',
                $row['factores_riesgo'] ?? '',
                $row['factores_preventivos'] ?? '',
                $row['ingresos_economicos'] ?? '',
                $row['convivencia_actual'] ?? '',
                $row['resultado_actividad'] ?? '',
                $row['remision'] ?? '',
                $row['telefono_referencia_persona'] ?? '',
                $row['condicion_ocupacion'] ?? '',
                $row['condicion_componente'] ?? '',
                // Datos de movimiento y cálculos
                (isset($seg['centro_id']) ? (isset($gruposMap[$seg['centro_id']]) ? $gruposMap[$seg['centro_id']] : ($row['centro_vida'] ?? 'No asignado')) : ($seg['centro_desc'] ?? ($row['centro_vida'] ?? 'No asignado'))),
                $row['descripcion_politica'] ?? 'No asignada',
                $estado_actual,
                $fecha_ultimo_estado,
                $ultima_meta_desc,
                $ultima_actividad_desc,
                $ultima_accion_desc,
                $row['ultimo_departamento_procedencia'] ?? 'No registrado',
                $row['movimientos_en_year'] ?? 0,
                $row['traslados_en_year'] ?? 0,
                $row['ultimo_centro_traslado'] ?? 'N/A',
                $seg['start'] ? date('d/m/Y', strtotime($seg['start'])) : '',
                $seg['end'] ? date('d/m/Y', strtotime($seg['end'])) : '',
                $dias_activos_val,
                $dias_activos_presente_year,
                $fecha_ultimo_contrato_mostrar,
                $dias_activo_desde_contrato
            ];

            // Escribir datos en la fila
            $col = 'A';
            foreach ($data as $value) {
                $sheet->setCellValue($col . $row_num, $value);
                $col++;
            }

            // Aplicar estilos a las filas de datos
            $dataRange = 'A' . $row_num . ':' . $lastCol . $row_num;
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E0E0E0']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ]);

            $row_num++;
        }
    }

    // Configurar anchos de columna automáticamente
    // Configurar ancho fijo de todas las columnas a 30
    for ($colIdx = 1; $colIdx <= count($headers); $colIdx++) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $sheet->getColumnDimension($col)->setWidth(30);
    }

    // Configurar altura mínima de filas
    for ($i = 2; $i < $row_num; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(25);
    }



    // Descargar el archivo directamente sin guardarlo
    $fileName = 'SDSYP_Informe_Completo_' . $year . '_' . date('Y-m-d_H-i-s') . '.xlsx';
    // Limpiar el buffer de salida antes de enviar headers
    if (ob_get_length()) {
        ob_end_clean();
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al generar Excel: ' . $e->getMessage();
}

$mysqli->close();
// No cierre PHP para evitar salida accidental
