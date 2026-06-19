<?php
// Exportar registros Centro Vida a Excel
ob_start();

session_start();
// Leer variables de sesión ANTES del try-catch para garantizar disponibilidad
$tipo_usuario_export  = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
$nombre_usuario_export = isset($_SESSION['nombre'])      ? trim($_SESSION['nombre'])       : '';

// Crear archivo de log para debugging
$log_file = __DIR__ . '/export_debug.log';

try {
    // Aumentar límites para exportación
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 120); // 2 minutos (debe ser suficiente con query optimizada)

    require_once '../../conexion.php';
    require_once '../filtros_grupo_usuario.php';
    require_once '../filtros_grupos.php';

    if (isset($mysqli)) {
        $mysqli->set_charset('utf8mb4');
        $mysqli->query("SET NAMES 'utf8mb4'");
    }

    require_once '../../vendor/autoload.php';
} catch (Exception $e) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error en includes: " . $e->getMessage() . "\n", FILE_APPEND);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al cargar archivos requeridos: ' . $e->getMessage();
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {
    $spreadsheet = new Spreadsheet();
    \PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
    $spreadsheet->getProperties()
        ->setCreator('SDSYP')
        ->setTitle('Export Registros Centro Vida')
        ->setDescription('Export generado ' . date('Y-m-d H:i:s'));

    // Encabezados: incluir todas las columnas de la tabla `personas` (excepto id_usuario)
    $headers = [
    // Fechas de actividad primero
    'FECHAS ACTIVIDAD',
    // Campos de la tabla personas (caracterización)
    'CÉDULA', 'TIPO IDENTIFICACIÓN', 'NOMBRES', 'APELLIDOS', 'FECHA NACIMIENTO', 'EDAD', 'TELÉFONO', 'GÉNERO', 'GRUPO SISBEN', 'PERSONA DISCAPACIDAD', 'CUÁL DISCAPACIDAD',
    'CABEZA HOGAR', 'LÍDER COMUNIDAD', 'SE RECONOCE COMO', 'ORIENTACIÓN SEXUAL', 'EXPERIENCIA MIGRATORIA', 'GRUPO ÉTNICO', 'TIPO SALUD', 'NIVEL EDUCATIVO', 'GRUPO',
    'ZONA PERSONA', 'BARRIO', 'COMUNA', 'REFERENCIA PERSONA', 'EPS', 'PESO', 'TALLA', 'PATOLOGÍAS', 'FACTORES RIESGO', 'FACTORES PREVENTIVOS', 'INGRESOS ECONÓMICOS', 'CONVIVENCIA ACTUAL',
        'RESULTADO ACTIVIDAD', 'REMISIÓN', 'CORREO PERSONA', 'TELÉFONO REFERENCIA PERSONA', 'DIRECCIÓN PERSONA', 'CONDICIÓN OCUPACIÓN', 'CONDICIÓN COMPONENTE', 'ACTIVO DESDE', 'META', 'ACTIVIDAD', 'ACCIÓN',
        // Campos del registro Centro Vida
        'ACTIVIDAD CENTRO VIDA', 'POLÍTICA PÚBLICA', 'DEPARTAMENTO PROCEDENCIA', 'CONDICIÓN OTRA', 'PROFESIÓN', 'JORNADA', 'GRUPOS EXTERNOS', 'NOMBRE ACTIVIDAD EVENTO/ASUNTO', 'FUNCIONARIO', 'FECHA REGISTRO',
    ];

    // Estilo cabeceras
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = 'A1:' . $lastCol . '1';

    // Consulta OPTIMIZADA: sin GROUP_CONCAT, solo el registro principal
    $query = "
        SELECT
            rcv.id_registro_centro_vida,
            p.cedula_persona,
            p.tipo_identificacion,
            p.nombres_persona,
            p.apellidos_persona,
            p.fecha_nacimiento,
            p.telefono_persona,
            p.genero_persona,
            p.grupo_sisben,
            p.persona_discapacidad,
            p.cual_discapacidad,
            p.cabeza_hogar,
            p.lider_comunidad,
            p.se_reconoce_como,
            p.orientacion_sexual,
            p.experiencia_migratoria,
            p.grupo_etnico,
            p.tipo_salud,
            p.nivel_educativo,
            p.zona_persona,
            p.referencia_persona,
            p.eps,
            p.peso,
            p.talla,
            p.patologias,
            p.factores_riesgo,
            p.factores_preventivos,
            p.ingresos_economicos,
            p.convivencia_actual,
            p.resultado_actividad,
            p.remision,
            p.correo_persona,
            p.telefono_referencia_persona,
            p.direccion_persona,
            p.condicion_ocupacion,
            p.condicion_componente,
            p.activo_desde,
            acv.descripcion_actividad as actividad_centro_vida,
            COALESCE(pol.descripcion_politica, '') as descripcion_politica_registro,
            rcv.departamento_procedencia,
            rcv.condicion_otra,
            rcv.profesion,
            rcv.jornada,
            rcv.observacion,
            COALESCE(u.nombre, '') AS nombre_funcionario,
            rcv.fecha_registro,
            COALESCE(g.descripcion_grupo, '') as descripcion_grupo,
            COALESCE(b.nombre_bar, '') as nombre_barrio,
            COALESCE(c.nombre_com, '') as nombre_comuna,
            COALESCE(m.descripcion_meta, '') as descripcion_meta,
            COALESCE(a.descripcion_actividad, '') as descripcion_actividad_persona,
            COALESCE(ac.descripcion_accion, '') as descripcion_accion_persona
        FROM registro_centro_vida rcv
        INNER JOIN personas p ON rcv.cedula_persona = p.cedula_persona
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN actividad_centro_vida acv ON rcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
        LEFT JOIN politicas_publicas pol ON rcv.politica_publica = pol.id_politica
        LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
        LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
        LEFT JOIN metas m ON rcv.id_meta = m.id_meta
        LEFT JOIN actividades a ON rcv.id_actividad = a.id_actividad
        LEFT JOIN acciones ac ON rcv.id_accion = ac.id_accion
        LEFT JOIN usuarios u ON u.id = rcv.funcionario_registro
    ";

    // Aplicar filtros (mismos que la UI)
    $where = [];
    $params = [];
    $types = '';
    $has_fecha_filter = false;

    if (isset($_GET['cedula_persona']) && $_GET['cedula_persona'] !== '') {
        $where[] = "p.cedula_persona = ?";
        $params[] = $_GET['cedula_persona'];
        $types .= 's';
    }
    if (isset($_GET['nombre']) && $_GET['nombre'] !== '') {
        $where[] = "(p.nombres_persona LIKE ? OR p.apellidos_persona LIKE ?)";
        $params[] = "%" . $_GET['nombre'] . "%";
        $params[] = "%" . $_GET['nombre'] . "%";
        $types .= 'ss';
    }
    if (isset($_GET['actividad']) && $_GET['actividad'] !== '') {
        $where[] = "rcv.id_actividad_centro_vida = ?";
        $params[] = $_GET['actividad'];
        $types .= 'i';
    }
    if (isset($_GET['funcionario']) && $_GET['funcionario'] !== '') {
        $where[] = "rcv.funcionario_registro = ?";
        $params[] = intval($_GET['funcionario']);
        $types .= 'i';
    }
    if (isset($_GET['filtro_tipo_usuario']) && $_GET['filtro_tipo_usuario'] !== '' && is_numeric($_GET['filtro_tipo_usuario'])) {
        $where[] = "u.tipo_usuario = ?";
        $params[] = intval($_GET['filtro_tipo_usuario']);
        $types .= 'i';
    }
    if (isset($_GET['id_grupo_cv']) && $_GET['id_grupo_cv'] !== '') {
        $where[] = "p.id_grupo = ?";
        $params[] = intval($_GET['id_grupo_cv']);
        $types .= 'i';
    }

    // Soporte filtro por mes y año
    $mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? intval($_GET['mes']) : 0;
    $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : 0;
    if ($mes >= 1 && $mes <= 12 && $anio > 0) {
        $where[] = "YEAR(rcvf.fecha_atencion) = ? AND MONTH(rcvf.fecha_atencion) = ?";
        $params[] = $anio;
        $params[] = $mes;
        $types .= 'ii';
        $has_fecha_filter = true;
    }

    if (!empty($where)) {
        $query .= ' WHERE ' . implode(' AND ', $where);
    }

    // Aplicar filtro por grupo de usuario (tipo 11: INGENIERO CENTRO VIDA)
    $where_grupo_usuario = obtenerCondicionFiltroGrupo('p');
    if (!empty($where_grupo_usuario)) {
        if (empty($where)) {
            $query .= ' WHERE 1=1 ';
        }
        $query .= $where_grupo_usuario;
    }

    // Tipo 10, 12 y 13: solo sus propios registros (filtro por ID numérico)
    if (in_array($tipo_usuario_export, [10, 12, 13])) {
        $id_usuario_export = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
        if ($id_usuario_export === 0) {
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= ' AND 1=0';
        } else {
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= " AND rcv.funcionario_registro = $id_usuario_export";
        }
    } else {
        $grupos_cv_export = getGruposPermitidos($mysqli, $tipo_usuario_export);
        if (!empty($grupos_cv_export)) {
            $ids_cv_export = implode(',', array_map('intval', $grupos_cv_export));
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= " AND p.id_grupo IN ($ids_cv_export)";
        }
    }

    // Guardar query base (sin jornada ni GROUP BY) para reusar en cada hoja
    $query_base = $query;

    // ── Closure: ejecutar query para una jornada (null = sin jornada)
    $ejecutarJornada = function($jornada_val) use ($mysqli, $query_base, $types, $params, $log_file) {
        $time_start = microtime(true);

        if ($jornada_val === null) {
            $cond = "(rcv.jornada IS NULL OR rcv.jornada = '')";
        } else {
            $jornada_esc = $mysqli->real_escape_string($jornada_val);
            $cond = "rcv.jornada = '$jornada_esc'";
        }
        if (stripos($query_base, 'WHERE') !== false) {
            $q = $query_base . " AND $cond";
        } else {
            $q = $query_base . " WHERE $cond";
        }
        $q .= ' GROUP BY rcv.id_registro_centro_vida ORDER BY rcv.fecha_registro DESC';

        file_put_contents($log_file, "\n" . str_repeat("=", 80) . "\n", FILE_APPEND);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - [INICIO] Jornada: " . ($jornada_val ?? 'NULL') . "\n", FILE_APPEND);
        file_put_contents($log_file, "Params: " . json_encode($params) . " | Types: $types\n", FILE_APPEND);

        if (!empty($params)) {
            file_put_contents($log_file, "[PREPARE] Preparando query...\n", FILE_APPEND);
            $stmt2 = $mysqli->prepare($q);
            if (!$stmt2) {
                file_put_contents($log_file, "[ERROR PREPARE] " . $mysqli->error . "\n", FILE_APPEND);
                return false;
            }
            file_put_contents($log_file, "[BIND_PARAM] Vinculando parámetros...\n", FILE_APPEND);
            if ($types !== '') {
                $stmt2->bind_param($types, ...$params);
            }
            file_put_contents($log_file, "[EXECUTE] Ejecutando query...\n", FILE_APPEND);
            $stmt2->execute();
            $exec_time = microtime(true) - $time_start;
            if ($stmt2->errno) {
                file_put_contents($log_file, "[ERROR EXECUTE] " . $stmt2->error . " (en " . number_format($exec_time, 2) . "s)\n", FILE_APPEND);
                return false;
            }
            file_put_contents($log_file, "[RESULT] Query completada en " . number_format($exec_time, 2) . "s\n", FILE_APPEND);
            $result = $stmt2->get_result();
            file_put_contents($log_file, "[ROWS] Filas encontradas: " . $result->num_rows . "\n", FILE_APPEND);
            return $result;
        }

        file_put_contents($log_file, "[EXECUTE DIRECTO] Ejecutando query sin prepared statement...\n", FILE_APPEND);
        $result = $mysqli->query($q);
        $exec_time = microtime(true) - $time_start;
        if (!$result) {
            file_put_contents($log_file, "[ERROR QUERY] " . $mysqli->error . " (en " . number_format($exec_time, 2) . "s)\n", FILE_APPEND);
            return false;
        }
        file_put_contents($log_file, "[RESULT] Query completada en " . number_format($exec_time, 2) . "s | Filas: " . $result->num_rows . "\n", FILE_APPEND);
        return $result;
    };

    // ── Closure: escribir cabeceras en una hoja
    $escribirCabeceras = function($sheet) use ($headers, $lastCol, $headerRange) {
        $col = 'A';
        foreach ($headers as $h) { $sheet->setCellValue($col . '1', $h); $col++; }
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEECE1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);
    };

    // ── Función rápida para obtener fechas
    $getFechas = function($id_registro) use ($mysqli) {
        $q = "SELECT GROUP_CONCAT(DISTINCT fecha_atencion ORDER BY fecha_atencion ASC SEPARATOR ', ') as f FROM registro_centro_vida_fechas WHERE id_registro_centro_vida = ?";
        $s = $mysqli->prepare($q);
        $s->bind_param('i', $id_registro);
        $s->execute();
        $res = $s->get_result();
        $row = $res->fetch_assoc();
        $s->close();
        return $row['f'] ?? '';
    };

    // ── Función rápida para obtener grupos externos
    $getGruposExternos = function($id_registro) use ($mysqli) {
        $q = "SELECT GROUP_CONCAT(DISTINCT ge.nombre_grupo_externo ORDER BY ge.nombre_grupo_externo ASC SEPARATOR ', ') as g
              FROM registro_centro_vida_grupo_externo rcvge
              LEFT JOIN grupos_externos ge ON rcvge.id_grupo_externo = ge.id_grupo_externo
              WHERE rcvge.id_registro_centro_vida = ?";
        $s = $mysqli->prepare($q);
        $s->bind_param('i', $id_registro);
        $s->execute();
        $res = $s->get_result();
        $row = $res->fetch_assoc();
        $s->close();
        return $row['g'] ?? '';
    };

    // ── Closure: llenar hoja con datos de un resultado
    $llenarHoja = function($sheet, $result) use ($headers, $getFechas, $getGruposExternos, $log_file) {
        $rowNum = 2;
        $totalCols = count($headers);

        if (!$result || $result->num_rows === 0) {
            file_put_contents($log_file, "[LLENAR HOJA] Sin datos para procesar\n", FILE_APPEND);
            return;
        }

        file_put_contents($log_file, "[LLENAR HOJA] Iniciando... Total filas: " . $result->num_rows . "\n", FILE_APPEND);
        $time_inicio_hoja = microtime(true);
        $contador_filas = 0;

        while ($r = $result->fetch_assoc()) {
            $contador_filas++;
            $id_reg = $r['id_registro_centro_vida'];

            // Formatear fechas
            $fecha_nac = '';
            if (!empty($r['fecha_nacimiento']) && $r['fecha_nacimiento'] != '0000-00-00') {
                $fecha_nac = date('d/m/Y', strtotime($r['fecha_nacimiento']));
            }

            $fecha_registro = '';
            if (!empty($r['fecha_registro'])) {
                $fecha_registro = date('d/m/Y H:i', strtotime($r['fecha_registro']));
            }

            $edad = '';
            if (!empty($r['fecha_nacimiento']) && $r['fecha_nacimiento'] != '0000-00-00') {
                $edad = (int)(new DateTime($r['fecha_nacimiento']))->diff(new DateTime())->y;
            }

            $activo_desde = '';
            if (!empty($r['activo_desde']) && $r['activo_desde'] != '0000-00-00') {
                $activo_desde = date('d/m/Y', strtotime($r['activo_desde']));
            }

            // Obtener fechas y grupos (consultas separadas rápidas)
            $fechas_display = '';
            $fechas_raw = $getFechas($id_reg);
            if (!empty($fechas_raw)) {
                $parts = explode(', ', $fechas_raw);
                $formatted_dates = [];
                foreach ($parts as $f) {
                    if (!empty($f) && $f != '0000-00-00') {
                        $formatted_dates[] = date('d/m/Y', strtotime($f));
                    }
                }
                $fechas_display = implode(', ', $formatted_dates);
            }

            $nombres_grupos_externos = $getGruposExternos($id_reg);

            $data = [
                $fechas_display,
                $r['cedula_persona'] ?? '', $r['tipo_identificacion'] ?? '',
                $r['nombres_persona'] ?? '', $r['apellidos_persona'] ?? '',
                $fecha_nac, $edad, $r['telefono_persona'] ?? '', $r['genero_persona'] ?? '',
                $r['grupo_sisben'] ?? '', $r['persona_discapacidad'] ?? '', $r['cual_discapacidad'] ?? '',
                $r['cabeza_hogar'] ?? '', $r['lider_comunidad'] ?? '', $r['se_reconoce_como'] ?? '',
                $r['orientacion_sexual'] ?? '', $r['experiencia_migratoria'] ?? '', $r['grupo_etnico'] ?? '',
                $r['tipo_salud'] ?? '', $r['nivel_educativo'] ?? '', $r['descripcion_grupo'] ?? '',
                $r['zona_persona'] ?? '', $r['nombre_barrio'] ?? '', $r['nombre_comuna'] ?? '',
                $r['referencia_persona'] ?? '', $r['eps'] ?? '', $r['peso'] ?? '', $r['talla'] ?? '',
                $r['patologias'] ?? '', $r['factores_riesgo'] ?? '', $r['factores_preventivos'] ?? '',
                $r['ingresos_economicos'] ?? '', $r['convivencia_actual'] ?? '',
                $r['resultado_actividad'] ?? '', $r['remision'] ?? '', $r['correo_persona'] ?? '',
                $r['telefono_referencia_persona'] ?? '', $r['direccion_persona'] ?? '',
                $r['condicion_ocupacion'] ?? '', $r['condicion_componente'] ?? '', $activo_desde,
                $r['descripcion_meta'] ?? '', $r['descripcion_actividad_persona'] ?? '',
                $r['descripcion_accion_persona'] ?? '',
                $r['actividad_centro_vida'] ?? '', $r['descripcion_politica_registro'] ?? '',
                $r['departamento_procedencia'] ?? '', $r['condicion_otra'] ?? '',
                $r['profesion'] ?? '', $r['jornada'] ?? '', $nombres_grupos_externos,
                $r['observacion'] ?? '', $r['nombre_funcionario'] ?? '', $fecha_registro,
            ];

            $col = 'A';
            foreach ($data as $val) {
                $sheet->setCellValue($col . $rowNum, $val);
                $col++;
            }

            // SIN APLICAR ESTILOS A CADA FILA (es lo que ralentiza)
            $rowNum++;

            // Log cada 2000 filas
            if ($contador_filas % 2000 === 0) {
                $tiempo_transcurrido = microtime(true) - $time_inicio_hoja;
                file_put_contents($log_file, "[LLENAR HOJA] Procesadas $contador_filas filas en " . number_format($tiempo_transcurrido, 2) . "s\n", FILE_APPEND);
            }
        }

        $tiempo_fill = microtime(true) - $time_inicio_hoja;
        file_put_contents($log_file, "[LLENAR HOJA] Llenado completado en " . number_format($tiempo_fill, 2) . "s para $contador_filas filas\n", FILE_APPEND);

        // Aplicar estilos SOLO a cabeceras (las datos no necesitan)
        file_put_contents($log_file, "[FORMATO] Aplicando anchos a columnas...\n", FILE_APPEND);
        $time_formato = microtime(true);

        for ($i = 1; $i <= $totalCols; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setWidth($i === 1 ? 60 : 18);
        }

        $tiempo_formato_total = microtime(true) - $time_formato;
        file_put_contents($log_file, "[FORMATO] Anchos aplicados en " . number_format($tiempo_formato_total, 2) . "s\n", FILE_APPEND);
    };

    // ── Hoja Mañana
    file_put_contents($log_file, "\n[HOJA] Creando hoja MAÑANA...\n", FILE_APPEND);
    $sheetM = $spreadsheet->getActiveSheet();
    $sheetM->setTitle('Mañana');
    $escribirCabeceras($sheetM);
    file_put_contents($log_file, "[HOJA] Cabeceras escritas. Llenando datos...\n", FILE_APPEND);
    $llenarHoja($sheetM, $ejecutarJornada('Mañana'));
    file_put_contents($log_file, "[HOJA] Hoja MAÑANA completada\n", FILE_APPEND);

    // ── Hoja Tarde
    file_put_contents($log_file, "\n[HOJA] Creando hoja TARDE...\n", FILE_APPEND);
    $sheetT = $spreadsheet->createSheet();
    $sheetT->setTitle('Tarde');
    $escribirCabeceras($sheetT);
    file_put_contents($log_file, "[HOJA] Cabeceras escritas. Llenando datos...\n", FILE_APPEND);
    $llenarHoja($sheetT, $ejecutarJornada('Tarde'));
    file_put_contents($log_file, "[HOJA] Hoja TARDE completada\n", FILE_APPEND);

    // ── Hoja Sin Jornada
    file_put_contents($log_file, "\n[HOJA] Creando hoja SIN JORNADA...\n", FILE_APPEND);
    $sheetS = $spreadsheet->createSheet();
    $sheetS->setTitle('Sin Jornada');
    $escribirCabeceras($sheetS);
    file_put_contents($log_file, "[HOJA] Cabeceras escritas. Llenando datos...\n", FILE_APPEND);
    $llenarHoja($sheetS, $ejecutarJornada(null));
    file_put_contents($log_file, "[HOJA] Hoja SIN JORNADA completada\n", FILE_APPEND);

    file_put_contents($log_file, "\n[FINALIZACION] Preparando descarga...\n", FILE_APPEND);
    $spreadsheet->setActiveSheetIndex(0);

    // Enviar archivo
    file_put_contents($log_file, "[ARCHIVO] Preparando archivo XLSX para descarga...\n", FILE_APPEND);
    $time_save = microtime(true);

    ob_end_clean();
    $fileName = 'CentroVida_Export_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    file_put_contents($log_file, "[ARCHIVO] Creando writer XLSX...\n", FILE_APPEND);
    $writer = new Xlsx($spreadsheet);

    file_put_contents($log_file, "[ARCHIVO] Guardando archivo en output...\n", FILE_APPEND);
    $writer->save('php://output');

    $tiempo_save = microtime(true) - $time_save;
    file_put_contents($log_file, "[ARCHIVO] Archivo guardado en " . number_format($tiempo_save, 2) . "s\n", FILE_APPEND);
    exit;

} catch (Exception $e) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Stack: " . $e->getTraceAsString() . "\n", FILE_APPEND);

    ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al generar Excel: ' . $e->getMessage();
} catch (Throwable $t) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Throwable: " . $t->getMessage() . "\n", FILE_APPEND);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Stack: " . $t->getTraceAsString() . "\n", FILE_APPEND);

    ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al generar Excel: ' . $t->getMessage();
}

if (isset($mysqli)) {
    $mysqli->close();
}

