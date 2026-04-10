<?php
// Exportar registros Centro Vida a Excel
// Basado en code/reports/generateExcel.php

if (ob_get_length()) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Hay salida previa al header. El archivo Excel se corromperá.\n";
    exit;
}

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
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Export CentroVida');

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
        'ACTIVIDAD CENTRO VIDA', 'POLÍTICA PÚBLICA', 'DEPARTAMENTO PROCEDENCIA', 'NOMBRE ACTIVIDAD EVENTO/ASUNTO', 'FUNCIONARIO', 'FECHA REGISTRO',
    ];

    // Escribir cabeceras
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $col++;
    }

    // Estilo cabeceras
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = 'A1:' . $lastCol . '1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => ['bold' => true, 'size' => 11],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEECE1']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
    ]);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // Construir consulta similar a getRegistrosCentroVida.php pero trayendo campos de personas
    $query = "
        SELECT DISTINCT
            rcv.id_registro_centro_vida,
            p.*, -- todos los campos de la tabla personas
            acv.descripcion_actividad as actividad_centro_vida,
            pol.descripcion_politica as descripcion_politica_registro,
            rcv.departamento_procedencia,
            rcv.observacion,
            rcv.funcionario_registro,
            COALESCE(u.nombre, rcv.funcionario_registro) AS nombre_funcionario,
            rcv.fecha_registro,
            GROUP_CONCAT(rcvf.fecha_atencion ORDER BY rcvf.fecha_atencion ASC SEPARATOR ', ') as fechas_programadas,
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

    // Tipo 10 y 12: solo sus propios registros (filtro por ID numérico)
    if (in_array($tipo_usuario_export, [10, 12])) {
        $id_usuario_export = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
        if ($id_usuario_export === 0) {
            // Sin ID en sesión → bloquear todo
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= ' AND 1=0';
        } else {
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= " AND rcv.funcionario_registro = $id_usuario_export";
        }
    } else {
        // Otros tipos con restricción de grupos CV (ej. tipo 5)
        $grupos_cv_export = getGruposPermitidos($mysqli, $tipo_usuario_export);
        if (!empty($grupos_cv_export)) {
            $ids_cv_export = implode(',', array_map('intval', $grupos_cv_export));
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= " AND p.id_grupo IN ($ids_cv_export)";
        }
    }

    $query .= ' GROUP BY rcv.id_registro_centro_vida ORDER BY rcv.fecha_registro DESC';

    if (!empty($params)) {
        $stmt = $mysqli->prepare($query);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $mysqli->query($query);
    }

    $rowNum = 2;
    if ($result && $result->num_rows > 0) {
        while ($r = $result->fetch_assoc()) {
            // Formatear fechas
            $fecha_nac = '';
            if (!empty($r['fecha_nacimiento']) && $r['fecha_nacimiento'] != '0000-00-00') {
                $fecha_nac = date('d/m/Y', strtotime($r['fecha_nacimiento']));
            }
            $fecha_registro = '';
            if (!empty($r['fecha_registro'])) {
                $fecha_registro = date('d/m/Y H:i', strtotime($r['fecha_registro']));
            }

            // Formatear fechas programadas (a d/m/Y, coma-separated)
            $fechas_display = '';
            if (!empty($r['fechas_programadas'])) {
                $parts = explode(', ', $r['fechas_programadas']);
                $map = array_map(function($f) { return ($f && $f != '0000-00-00') ? date('d/m/Y', strtotime($f)) : ''; }, $parts);
                $fechas_display = implode(', ', array_filter($map));
            }

            // Calcular edad
            $edad = '';
            if (!empty($r['fecha_nacimiento']) && $r['fecha_nacimiento'] != '0000-00-00') {
                $edad = (int)(new DateTime($r['fecha_nacimiento']))->diff(new DateTime())->y;
            }

            // Formatear otras fechas
            $fecha_alta = (!empty($r['fecha_alta_persona']) && $r['fecha_alta_persona'] != '0000-00-00') ? date('d/m/Y', strtotime($r['fecha_alta_persona'])) : '';
            $activo_desde = (!empty($r['activo_desde']) && $r['activo_desde'] != '0000-00-00') ? date('d/m/Y', strtotime($r['activo_desde'])) : '';

            // Construir fila
            $data = [
                $fechas_display,
                $r['cedula_persona'] ?? '',
                $r['tipo_identificacion'] ?? '',
                $r['nombres_persona'] ?? '',
                $r['apellidos_persona'] ?? '',
                $fecha_nac,
                $edad,
                $r['telefono_persona'] ?? '',
                $r['genero_persona'] ?? '',
                $r['grupo_sisben'] ?? '',
                $r['persona_discapacidad'] ?? '',
                $r['cual_discapacidad'] ?? '',
                $r['cabeza_hogar'] ?? '',
                $r['lider_comunidad'] ?? '',
                $r['se_reconoce_como'] ?? '',
                $r['orientacion_sexual'] ?? '',
                $r['experiencia_migratoria'] ?? '',
                $r['grupo_etnico'] ?? '',
                $r['tipo_salud'] ?? '',
                $r['nivel_educativo'] ?? '',
                $r['descripcion_grupo'] ?? '',
                $r['zona_persona'] ?? '',
                $r['nombre_barrio'] ?? '',
                $r['nombre_comuna'] ?? '',
                $r['referencia_persona'] ?? '',
                $r['eps'] ?? '',
                $r['peso'] ?? '',
                $r['talla'] ?? '',
                $r['patologias'] ?? '',
                $r['factores_riesgo'] ?? '',
                $r['factores_preventivos'] ?? '',
                $r['ingresos_economicos'] ?? '',
                $r['convivencia_actual'] ?? '',
                $r['resultado_actividad'] ?? '',
                $r['remision'] ?? '',
                $r['correo_persona'] ?? '',
                $r['telefono_referencia_persona'] ?? '',
                $r['direccion_persona'] ?? '',
                $r['condicion_ocupacion'] ?? '',
                $r['condicion_componente'] ?? '',
                $activo_desde,
                $r['descripcion_meta'] ?? '',
                $r['descripcion_actividad_persona'] ?? '',
                $r['descripcion_accion_persona'] ?? '',
                // Datos del registro Centro Vida
                $r['actividad_centro_vida'] ?? '',
                $r['descripcion_politica_registro'] ?? '',
                $r['departamento_procedencia'] ?? '',
                $r['observacion'] ?? '',
                $r['nombre_funcionario'] ?? '',
                $fecha_registro,
            ];

            $col = 'A';
            foreach ($data as $val) {
                $sheet->setCellValue($col . $rowNum, $val);
                $col++;
            }

            // Estilos fila
            $dataRange = 'A' . $rowNum . ':' . $lastCol . $rowNum;
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
            ]);

            $rowNum++;
        }
    }

    // Ajustar anchos: primera columna (fechas actividad) más ancha
    $totalCols = count($headers);
    for ($i = 1; $i <= $totalCols; $i++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        // Primera columna más ancha (fechas actividad)
        if ($i === 1) {
            $sheet->getColumnDimension($colLetter)->setWidth(60);
        } else {
            $sheet->getColumnDimension($colLetter)->setWidth(20);
        }
    }

    // Altura filas
    for ($r = 2; $r < $rowNum; $r++) {
        $sheet->getRowDimension($r)->setRowHeight(22);
    }

    // Enviar archivo
    if (ob_get_length()) ob_end_clean();
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

?>
            $sheet->setCellValue('E' . $row, $edad);
            $sheet->setCellValue('F' . $row, $data['sexo_persona']);
            $sheet->setCellValue('G' . $row, $data['telefono_persona']);
            $sheet->setCellValue('H' . $row, $data['direccion_persona']);
            $sheet->setCellValue('I' . $row, $data['barrio_persona']);
            $sheet->setCellValue('J' . $row, $data['comuna_persona']);
            $sheet->setCellValue('K' . $row, $data['estrato_persona']);
            $sheet->setCellValue('L' . $row, $data['actividad_centro_vida']);
            $sheet->setCellValue('M' . $row, date('d/m/Y', strtotime($data['fecha_atencion'])));
            $sheet->setCellValue('N' . $row, $data['politica_publica']);
            $sheet->setCellValue('O' . $row, $data['departamento_procedencia']);
            $sheet->setCellValue('P' . $row, $data['observacion']);
            $sheet->setCellValue('Q' . $row, $data['funcionario_registro']);
            $sheet->setCellValue('R' . $row, date('d/m/Y H:i', strtotime($data['fecha_registro'])));

            $row++;
        }

        // Aplicar estilos a los datos
        if ($row > 2) {
            $sheet->getStyle('A2:R' . ($row - 1))->applyFromArray($dataStyle);
        }
    } else {
        // No hay datos - agregar depuración a log
        error_log('exportExcelCentroVida: No rows returned for query.');
        // Log query and params for debugging
        try {
            error_log('exportExcelCentroVida QUERY: ' . $query);
            error_log('exportExcelCentroVida TYPES: ' . $types);
            error_log('exportExcelCentroVida PARAMS: ' . var_export($params, true));
        } catch (Exception $e) {
            error_log('exportExcelCentroVida: error logging debug info - ' . $e->getMessage());
        }

        // If the statement exists, log possible error
        if (isset($stmt) && $stmt === false) {
            error_log('exportExcelCentroVida: prepare failed: ' . $mysqli->error);
        }

        // No hay datos
        $sheet->setCellValue('A2', 'No se encontraron registros con los filtros aplicados');
        $sheet->mergeCells('A2:S2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Añadir información de depuración en filas siguientes (visibles en el Excel)
        $sheet->setCellValue('A4', 'DEBUG QUERY:');
        $sheet->setCellValue('B4', $query);
        $sheet->setCellValue('A5', 'DEBUG TYPES:');
        $sheet->setCellValue('B5', $types);
        $sheet->setCellValue('A6', 'DEBUG PARAMS:');
        $sheet->setCellValue('B6', is_array($params) ? json_encode($params, JSON_UNESCAPED_UNICODE) : strval($params));
    }

    // Ajustar ancho de columnas
    foreach (range('A', 'R') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Establecer altura de filas para mejor visualización
    for ($i = 1; $i < $row; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(25);
    }

    // Limpiar cualquier buffer de salida que pueda corromper el archivo
    if (ob_get_level()) {
        // Descarta todo el contenido del buffer de salida
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    // Configurar headers para descarga
    $filename = 'Registros_Centro_Vida_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    // Crear el writer y enviar el archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // Limpiar memoria
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);

    if (isset($stmt)) {
        $stmt->close();
    }

    // Cerrar conexión y terminar script
    $mysqli->close();
    exit;

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al generar el archivo Excel: ' . $e->getMessage();
    error_log('Error en exportExcelCentroVida.php: ' . $e->getMessage());
}
?>
