<?php
// Consolidado Mensual Centro Vida — solo tipo_usuario 11 y 13 (2 hojas: Mañana y Tarde)
ob_start();
session_start();
require_once '../../conexion.php';
$mysqli->set_charset('utf8mb4');
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Acceso solo para tipo_usuario 11 y 13
$tipo_usuario_ses = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
if ($tipo_usuario_ses !== 11 && $tipo_usuario_ses !== 13) {
    http_response_code(403);
    die('Acceso no autorizado');
}

$id_grupo_ses = isset($_SESSION['id_grupo']) ? (int)$_SESSION['id_grupo'] : 0;
if (!$id_grupo_ses) {
    die('No tiene centro de vida asociado en su perfil.');
}

// Parámetros
$mes       = isset($_GET['mes'])          ? (int)$_GET['mes']                           : 0;
$anio      = isset($_GET['anio'])         ? (int)$_GET['anio']                          : 0;
$ids_act_cv_raw = isset($_GET['id_actividad_cv']) ? (array)$_GET['id_actividad_cv'] : [];
$ids_act_cv = array_values(array_filter(array_map('intval', $ids_act_cv_raw)));
$funcionario = isset($_GET['funcionario']) ? (int)$_GET['funcionario']                  : 0;
$jornada   = isset($_GET['jornada'])      ? trim($_GET['jornada'])                      : ''; // '' = Ambas, 'Mañana', 'Tarde'

if (!$mes || !$anio || $mes < 1 || $mes > 12) {
    die('Mes y año son requeridos.');
}

// Días del mes
$dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

// Nombre del centro de vida
$cv_res = $mysqli->query("SELECT descripcion_grupo FROM grupos WHERE id_grupo = $id_grupo_ses LIMIT 1");
$cv_nombre = $cv_res ? ($cv_res->fetch_assoc()['descripcion_grupo'] ?? "CV $id_grupo_ses") : "CV $id_grupo_ses";

$meses_nombres = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                  7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
$mes_nombre = $meses_nombres[$mes] ?? $mes;

// ─── 1. Personas del centro de vida ─────────────────────────────────────────
$sql_personas = "SELECT p.cedula_persona,
                        p.nombres_persona,
                        p.apellidos_persona,
                        p.genero_persona,
                        p.jornada
                 FROM personas p
                 WHERE p.id_grupo = $id_grupo_ses
                 ORDER BY p.apellidos_persona ASC";
$res_personas = $mysqli->query($sql_personas);
$personas_all = $res_personas ? $res_personas->fetch_all(MYSQLI_ASSOC) : [];

// ── Colores
$COLOR_HEADER_BG  = '1565C0';
$COLOR_HEADER_FG  = 'FFFFFF';
$COLOR_DAY_BG     = 'E3F2FD';
$COLOR_DOMINGO_BG = 'BDBDBD';
$COLOR_ASISTIO_BG = 'A5D6A7';
$COLOR_TOT_M_BG   = 'BBDEFB';
$COLOR_TOT_F_BG   = 'F8BBD9';

/**
 * Construye y llena una hoja con el consolidado de una jornada específica.
 */
function llenarHojaConsolidado($sheet, $personas, $jornada_hoja, $id_grupo_ses, $mes, $anio, $dias_mes, $mes_nombre, $cv_nombre, $ids_act_cv, $funcionario, $mysqli, $colors) {
    extract($colors);
    $last_col_idx = 3 + $dias_mes;
    $last_col_ltr = Coordinate::stringFromColumnIndex($last_col_idx);

    // Filtrar personas por jornada
    $personas_jornada = [];
    foreach ($personas as $per) {
        $per_jornada = trim($per['jornada'] ?? '');
        $coincide = false;
        
        if ($jornada_hoja === 'Sin Jornada') {
            $coincide = empty($per_jornada);
        } elseif ($jornada_hoja === 'Mañana') {
            $coincide = ($per_jornada === 'Mañana');
        } elseif ($jornada_hoja === 'Tarde') {
            $coincide = ($per_jornada === 'Tarde');
        }
        
        if ($coincide) {
            $personas_jornada[] = $per;
        }
    }

    // Fila 1: Título
    $sheet->mergeCells('A1:' . $last_col_ltr . '1');
    $titulo = "$cv_nombre — Consolidado $mes_nombre $anio | Jornada: $jornada_hoja";
    if (!empty($ids_act_cv)) {
        $act_names = [];
        foreach ($ids_act_cv as $act_id) {
            $act_r = $mysqli->query("SELECT descripcion_actividad FROM actividad_centro_vida WHERE id_actividad_centro_vida = $act_id LIMIT 1");
            if ($act_r) $act_names[] = $act_r->fetch_assoc()['descripcion_actividad'] ?? '';
        }
        $act_names = array_filter($act_names);
        if (!empty($act_names)) $titulo .= " | Actividad: " . implode(', ', $act_names);
    }
    $sheet->setCellValue('A1', $titulo);
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $COLOR_HEADER_FG]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_HEADER_BG]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(28);

    // Fila 2: Encabezados
    $sheet->setCellValue('A2', 'CÉDULA');
    $sheet->setCellValue('B2', 'NOMBRE');
    $sheet->setCellValue('C2', 'GÉNERO');
    for ($d = 1; $d <= $dias_mes; $d++) {
        $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
        $sheet->setCellValue($col_ltr . '2', $d);
        $es_dom = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
        $sheet->getStyle($col_ltr . '2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $es_dom ? $COLOR_DOMINGO_BG : $COLOR_DAY_BG]],
        ]);
    }
    $sheet->getStyle('A2:C2')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => $COLOR_HEADER_FG]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_HEADER_BG]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
    ]);
    $sheet->getStyle('D2:' . $last_col_ltr . '2')->applyFromArray([
        'font'      => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
    ]);
    $sheet->getRowDimension(2)->setRowHeight(22);

    // ── Registros del mes con filtro de jornada
    $jornada_esc = $mysqli->real_escape_string($jornada_hoja);
    if ($jornada_hoja === 'Sin Jornada') {
        $cond_jornada = "(rcv.jornada IS NULL OR rcv.jornada = '')";
    } else {
        $cond_jornada = "rcv.jornada = '$jornada_esc'";
    }
    $where_rec = "YEAR(rcvf.fecha_atencion) = $anio AND MONTH(rcvf.fecha_atencion) = $mes AND p.id_grupo = $id_grupo_ses AND $cond_jornada";
    if (!empty($ids_act_cv)) $where_rec .= " AND rcv.id_actividad_centro_vida IN (" . implode(',', $ids_act_cv) . ")";
    if ($funcionario) $where_rec .= " AND rcv.funcionario_registro = $funcionario";

    $sql_reg = "SELECT DISTINCT rcv.cedula_persona, DAY(rcvf.fecha_atencion) AS dia
                FROM registro_centro_vida rcv
                INNER JOIN registro_centro_vida_fechas rcvf ON rcvf.id_registro_centro_vida = rcv.id_registro_centro_vida
                INNER JOIN personas p ON p.cedula_persona = rcv.cedula_persona
                WHERE $where_rec
                ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC";
    $res_reg = $mysqli->query($sql_reg);
    $matriz = [];
    if ($res_reg) {
        while ($r = $res_reg->fetch_assoc()) {
            $matriz[$r['cedula_persona']][(int)$r['dia']] = 1;
        }
    }

    // ── Filas de personas (solo de esta jornada)
    $fila = 3;
    $totales_m = array_fill(1, $dias_mes, 0);
    $totales_f = array_fill(1, $dias_mes, 0);

    foreach ($personas_jornada as $per) {
        $ced    = $per['cedula_persona'];
        $nombre = $per['apellidos_persona'] . ' ' . $per['nombres_persona'];
        $genero = $per['genero_persona'] ?? '';

        $sheet->setCellValue('A' . $fila, $ced);
        $sheet->setCellValue('B' . $fila, $nombre);
        $sheet->setCellValue('C' . $fila, strtoupper($genero));
        $sheet->getStyle('A' . $fila . ':C' . $fila)->applyFromArray([
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ECEFF1']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        for ($d = 1; $d <= $dias_mes; $d++) {
            $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
            $es_dom  = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
            $celda   = $col_ltr . $fila;
            if ($es_dom) {
                $sheet->getStyle($celda)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DOMINGO_BG]], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]]]);
            } elseif (isset($matriz[$ced][$d])) {
                $sheet->setCellValue($celda, 1);
                $sheet->getStyle($celda)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_ASISTIO_BG]], 'font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]]]);
                $gen_upper = strtoupper(trim($genero));
                if ($gen_upper === 'M' || $gen_upper === 'MASCULINO') $totales_m[$d]++;
                elseif ($gen_upper === 'F' || $gen_upper === 'FEMENINO') $totales_f[$d]++;
            } else {
                $sheet->getStyle($celda)->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ECEFF1']]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            }
        }
        $sheet->getRowDimension($fila)->setRowHeight(20);
        $fila++;
    }

    // ── Fila totales Masculino
    $sheet->setCellValue('A' . $fila, 'TOTAL MASCULINO');
    $sheet->setCellValue('C' . $fila, 'M');
    $sheet->getStyle('A' . $fila . ':C' . $fila)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_M_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]]]);
    for ($d = 1; $d <= $dias_mes; $d++) {
        $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
        $es_dom  = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
        $celda   = $col_ltr . $fila;
        if ($es_dom) { $sheet->getStyle($celda)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DOMINGO_BG]]]); }
        else { $val = $totales_m[$d]; $sheet->setCellValue($celda, $val > 0 ? $val : ''); $sheet->getStyle($celda)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_M_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]]]); }
    }
    $sheet->getRowDimension($fila)->setRowHeight(22);
    $fila_tot_f = $fila + 1;

    // ── Fila totales Femenino
    $sheet->setCellValue('A' . $fila_tot_f, 'TOTAL FEMENINO');
    $sheet->setCellValue('C' . $fila_tot_f, 'F');
    $sheet->getStyle('A' . $fila_tot_f . ':C' . $fila_tot_f)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_F_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]]]);
    for ($d = 1; $d <= $dias_mes; $d++) {
        $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
        $es_dom  = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
        $celda   = $col_ltr . $fila_tot_f;
        if ($es_dom) { $sheet->getStyle($celda)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DOMINGO_BG]]]); }
        else { $val = $totales_f[$d]; $sheet->setCellValue($celda, $val > 0 ? $val : ''); $sheet->getStyle($celda)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_F_BG]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]]]); }
    }
    $sheet->getRowDimension($fila_tot_f)->setRowHeight(22);

    // ── Anchos y freeze
    $sheet->getColumnDimension('A')->setWidth(16);
    $sheet->getColumnDimension('B')->setWidth(32);
    $sheet->getColumnDimension('C')->setWidth(10);
    for ($d = 1; $d <= $dias_mes; $d++) {
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(3 + $d))->setWidth(6);
    }
    $sheet->freezePane('D3');
}

$colors = compact('COLOR_HEADER_BG','COLOR_HEADER_FG','COLOR_DAY_BG','COLOR_DOMINGO_BG','COLOR_ASISTIO_BG','COLOR_TOT_M_BG','COLOR_TOT_F_BG');

$spreadsheet = new Spreadsheet();

// ── Crear hojas dinámicamente según la jornada seleccionada
if (empty($jornada)) {
    // Ambas: crear hojas Mañana y Tarde
    $sheetM = $spreadsheet->getActiveSheet();
    $sheetM->setTitle('Mañana');
    llenarHojaConsolidado($sheetM, $personas_all, 'Mañana', $id_grupo_ses, $mes, $anio, $dias_mes, $mes_nombre, $cv_nombre, $ids_act_cv, $funcionario, $mysqli, $colors);

    $sheetT = $spreadsheet->createSheet();
    $sheetT->setTitle('Tarde');
    llenarHojaConsolidado($sheetT, $personas_all, 'Tarde', $id_grupo_ses, $mes, $anio, $dias_mes, $mes_nombre, $cv_nombre, $ids_act_cv, $funcionario, $mysqli, $colors);
} elseif ($jornada === 'Mañana') {
    // Solo Mañana
    $sheetM = $spreadsheet->getActiveSheet();
    $sheetM->setTitle('Mañana');
    llenarHojaConsolidado($sheetM, $personas_all, 'Mañana', $id_grupo_ses, $mes, $anio, $dias_mes, $mes_nombre, $cv_nombre, $ids_act_cv, $funcionario, $mysqli, $colors);
} elseif ($jornada === 'Tarde') {
    // Solo Tarde
    $sheetT = $spreadsheet->getActiveSheet();
    $sheetT->setTitle('Tarde');
    llenarHojaConsolidado($sheetT, $personas_all, 'Tarde', $id_grupo_ses, $mes, $anio, $dias_mes, $mes_nombre, $cv_nombre, $ids_act_cv, $funcionario, $mysqli, $colors);
}

$spreadsheet->setActiveSheetIndex(0);

// ── Enviar al navegador
ob_end_clean();
$filename = 'Consolidado_' . $cv_nombre . '_' . $mes_nombre . '_' . $anio;
$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename) . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

