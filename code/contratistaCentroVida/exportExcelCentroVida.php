<?php
// Exportar registros Centro Vida a Excel
ob_start();

session_start();
// Leer variables de sesión ANTES del try-catch para garantizar disponibilidad
$tipo_usuario_export  = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
$nombre_usuario_export = isset($_SESSION['nombre'])      ? trim($_SESSION['nombre'])       : '';
require_once '../../conexion.php';
require_once '../filtros_grupo_usuario.php';
require_once '../filtros_grupos.php';

if (isset($mysqli)) {
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES 'utf8mb4'");
}

ini_set('display_errors', 0);
error_reporting(0);
require_once '../../vendor/autoload.php';

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

    // Construir consulta similar a getRegistrosCentroVida.php pero trayendo campos de personas
    $query = "
        SELECT DISTINCT
            rcv.id_registro_centro_vida,
            p.*, -- todos los campos de la tabla personas
            acv.descripcion_actividad as actividad_centro_vida,
            pol.descripcion_politica as descripcion_politica_registro,
            rcv.departamento_procedencia,
            rcv.condicion_otra,
            rcv.profesion,
            rcv.jornada,
            rcv.observacion,
            rcv.funcionario_registro,
            IFNULL(u.nombre, '') AS nombre_funcionario,
            rcv.fecha_registro,
            GROUP_CONCAT(DISTINCT rcvf.fecha_atencion ORDER BY rcvf.fecha_atencion ASC SEPARATOR ', ') as fechas_programadas,
            GROUP_CONCAT(DISTINCT ge.nombre_grupo_externo ORDER BY ge.nombre_grupo_externo ASC SEPARATOR ', ') as nombres_grupos_externos,
            g.descripcion_grupo as descripcion_grupo,
            b.nombre_bar as nombre_barrio,
            c.nombre_com as nombre_comuna,
            m.descripcion_meta as descripcion_meta,
            a.descripcion_actividad as descripcion_actividad_persona,
            ac.descripcion_accion as descripcion_accion_persona
        FROM registro_centro_vida rcv
        INNER JOIN personas p ON rcv.cedula_persona = p.cedula_persona
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
    LEFT JOIN actividad_centro_vida acv ON rcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
        LEFT JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida
    LEFT JOIN politicas_publicas pol ON rcv.politica_publica = pol.id_politica
    LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
    LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
    LEFT JOIN metas m ON rcv.id_meta = m.id_meta
    LEFT JOIN actividades a ON rcv.id_actividad = a.id_actividad
    LEFT JOIN acciones ac ON rcv.id_accion = ac.id_accion
    LEFT JOIN usuarios u ON u.id = rcv.funcionario_registro
    LEFT JOIN registro_centro_vida_grupo_externo rcvge ON rcv.id_registro_centro_vida = rcvge.id_registro_centro_vida
    LEFT JOIN grupos_externos ge ON rcvge.id_grupo_externo = ge.id_grupo_externo
    ";

    // Aplicar filtros (mismos que la UI)
    $where = [];
    $params = [];
    $types = '';
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

    // Soporte filtro por mes y año (se esperan GET 'mes' = 1-12 y 'anio' = YYYY)
    $mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? intval($_GET['mes']) : 0;
    $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : 0;
    if ($mes >= 1 && $mes <= 12 && $anio > 0) {
        // insertar INNER JOIN para forzar existencia de alguna fecha en ese mes/año
        $query = str_replace("LEFT JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida",
                              "LEFT JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida\n    INNER JOIN registro_centro_vida_fechas rcvf_filter ON rcv.id_registro_centro_vida = rcvf_filter.id_registro_centro_vida AND YEAR(rcvf_filter.fecha_atencion) = ? AND MONTH(rcvf_filter.fecha_atencion) = ?",
                              $query);
        // agregar params al inicio para que el bind mantenga el orden
        array_unshift($params, $mes);
        array_unshift($params, $anio);
        $types = 'ii' . $types;
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
    $ejecutarJornada = function($jornada_val) use ($mysqli, $query_base, $types, $params) {
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
        if (!empty($params)) {
            $stmt2 = $mysqli->prepare($q);
            if ($stmt2 && $types !== '') {
                $stmt2->bind_param($types, ...$params);
            }
            if ($stmt2) { $stmt2->execute(); return $stmt2->get_result(); }
            return false;
        }
        return $mysqli->query($q);
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

    // ── Closure: llenar hoja con datos de un resultado
    $llenarHoja = function($sheet, $result) use ($headers, $lastCol) {
        $rowNum = 2;
        if ($result && $result->num_rows > 0) {
            while ($r = $result->fetch_assoc()) {
                $fecha_nac = (!empty($r['fecha_nacimiento']) && $r['fecha_nacimiento'] != '0000-00-00') ? date('d/m/Y', strtotime($r['fecha_nacimiento'])) : '';
                $fecha_registro = !empty($r['fecha_registro']) ? date('d/m/Y H:i', strtotime($r['fecha_registro'])) : '';
                $fechas_display = '';
                if (!empty($r['fechas_programadas'])) {
                    $parts = explode(', ', $r['fechas_programadas']);
                    $map = array_map(function($f) { return ($f && $f != '0000-00-00') ? date('d/m/Y', strtotime($f)) : ''; }, $parts);
                    $fechas_display = implode(', ', array_filter($map));
                }
                $edad = '';
                if (!empty($r['fecha_nacimiento']) && $r['fecha_nacimiento'] != '0000-00-00') {
                    $edad = (int)(new DateTime($r['fecha_nacimiento']))->diff(new DateTime())->y;
                }
                $activo_desde = (!empty($r['activo_desde']) && $r['activo_desde'] != '0000-00-00') ? date('d/m/Y', strtotime($r['activo_desde'])) : '';

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
                    $r['profesion'] ?? '', $r['jornada'] ?? '', $r['nombres_grupos_externos'] ?? '',
                    $r['observacion'] ?? '', $r['nombre_funcionario'] ?? '', $fecha_registro,
                ];

                $col = 'A';
                foreach ($data as $val) { $sheet->setCellValue($col . $rowNum, $val); $col++; }
                $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
                ]);
                $rowNum++;
            }
        }
        // Anchos
        $totalCols = count($headers);
        for ($i = 1; $i <= $totalCols; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setWidth($i === 1 ? 60 : 20);
        }
        for ($rr = 2; $rr < $rowNum; $rr++) { $sheet->getRowDimension($rr)->setRowHeight(22); }
    };

    // ── Hoja Mañana
    $sheetM = $spreadsheet->getActiveSheet();
    $sheetM->setTitle('Mañana');
    $escribirCabeceras($sheetM);
    $llenarHoja($sheetM, $ejecutarJornada('Mañana'));

    // ── Hoja Tarde
    $sheetT = $spreadsheet->createSheet();
    $sheetT->setTitle('Tarde');
    $escribirCabeceras($sheetT);
    $llenarHoja($sheetT, $ejecutarJornada('Tarde'));

    // ── Hoja Sin Jornada
    $sheetS = $spreadsheet->createSheet();
    $sheetS->setTitle('Sin Jornada');
    $escribirCabeceras($sheetS);
    $llenarHoja($sheetS, $ejecutarJornada(null));

    $spreadsheet->setActiveSheetIndex(0);

    // Enviar archivo
    ob_end_clean();
    $fileName = 'CentroVida_Export_' . date('Y-m-d_H-i-s') . '.xlsx';
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

