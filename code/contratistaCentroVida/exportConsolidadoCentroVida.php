<?php
// Consolidado Mensual Centro Vida — solo tipo_usuario 11
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

// Acceso solo para tipo_usuario 11
$tipo_usuario_ses = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
if ($tipo_usuario_ses !== 11) {
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
$id_act_cv = isset($_GET['id_actividad_cv']) ? (int)$_GET['id_actividad_cv']            : 0;
$jornada   = isset($_GET['jornada'])      ? $mysqli->real_escape_string(trim($_GET['jornada'])) : '';
$funcionario = isset($_GET['funcionario']) ? (int)$_GET['funcionario']                  : 0;

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
                        p.genero_persona
                 FROM personas p
                 WHERE p.id_grupo = $id_grupo_ses
                 ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC";
$res_personas = $mysqli->query($sql_personas);
$personas = $res_personas ? $res_personas->fetch_all(MYSQLI_ASSOC) : [];

// ─── 2. Registros del mes (cedula → días) ────────────────────────────────────
$where_rec = "YEAR(rcvf.fecha_atencion) = $anio
              AND MONTH(rcvf.fecha_atencion) = $mes
              AND p.id_grupo = $id_grupo_ses";
if ($id_act_cv)   $where_rec .= " AND rcv.id_actividad_centro_vida = $id_act_cv";
if ($jornada)     $where_rec .= " AND rcv.jornada = '$jornada'";
if ($funcionario) $where_rec .= " AND rcv.funcionario_registro = $funcionario";

$sql_reg = "SELECT DISTINCT rcv.cedula_persona, DAY(rcvf.fecha_atencion) AS dia
            FROM registro_centro_vida rcv
            INNER JOIN registro_centro_vida_fechas rcvf
                ON rcvf.id_registro_centro_vida = rcv.id_registro_centro_vida
            INNER JOIN personas p ON p.cedula_persona = rcv.cedula_persona
            WHERE $where_rec";
$res_reg = $mysqli->query($sql_reg);
$matriz = []; // [cedula][dia] = 1
if ($res_reg) {
    while ($r = $res_reg->fetch_assoc()) {
        $matriz[$r['cedula_persona']][(int)$r['dia']] = 1;
    }
}

// ─── 3. Construir Excel ──────────────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Consolidado');

// ── Colores
$COLOR_HEADER_BG  = '1565C0'; // azul oscuro
$COLOR_HEADER_FG  = 'FFFFFF';
$COLOR_DAY_BG     = 'E3F2FD'; // azul muy claro
$COLOR_DOMINGO_BG = 'BDBDBD'; // gris
$COLOR_ASISTIO_BG = 'A5D6A7'; // verde claro
$COLOR_TOT_M_BG   = 'BBDEFB'; // azul pastel
$COLOR_TOT_F_BG   = 'F8BBD9'; // rosa pastel

// ── Fila 1: Título
$last_col_idx = 3 + $dias_mes; // A=1 B=2 C=3 días=4...(3+dias_mes)
$last_col_ltr = Coordinate::stringFromColumnIndex($last_col_idx);
$sheet->mergeCells('A1:' . $last_col_ltr . '1');
$titulo = "$cv_nombre — Consolidado $mes_nombre $anio";
if ($jornada)     $titulo .= " | Jornada: $jornada";
if ($id_act_cv) {
    $act_r = $mysqli->query("SELECT descripcion_actividad FROM actividad_centro_vida WHERE id_actividad_centro_vida = $id_act_cv LIMIT 1");
    if ($act_r) $titulo .= " | Actividad: " . ($act_r->fetch_assoc()['descripcion_actividad'] ?? '');
}
$sheet->setCellValue('A1', $titulo);
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_HEADER_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension(1)->setRowHeight(28);

// ── Fila 2: Encabezados
$sheet->setCellValue('A2', 'CÉDULA');
$sheet->setCellValue('B2', 'NOMBRE');
$sheet->setCellValue('C2', 'GÉNERO');

for ($d = 1; $d <= $dias_mes; $d++) {
    $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
    $sheet->setCellValue($col_ltr . '2', $d);
    // Marcar domingos en encabezado
    $es_dom = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
    if ($es_dom) {
        $sheet->getStyle($col_ltr . '2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DOMINGO_BG]],
        ]);
    } else {
        $sheet->getStyle($col_ltr . '2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DAY_BG]],
        ]);
    }
}

// Estilo encabezados fijos A2:C2
$sheet->getStyle('A2:C2')->applyFromArray([
    'font'      => ['bold' => true, 'color' => ['rgb' => $COLOR_HEADER_FG]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_HEADER_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
]);
// Estilo encabezados de días
$sheet->getStyle('D2:' . $last_col_ltr . '2')->applyFromArray([
    'font'      => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
]);
$sheet->getRowDimension(2)->setRowHeight(22);

// ── Filas de personas
$fila = 3;
$totales_m = array_fill(1, $dias_mes, 0); // [dia] => count masculino
$totales_f = array_fill(1, $dias_mes, 0); // [dia] => count femenino

foreach ($personas as $per) {
    $ced     = $per['cedula_persona'];
    $nombre  = $per['nombres_persona'] . ' ' . $per['apellidos_persona'];
    $genero  = $per['genero_persona'] ?? '';

    $sheet->setCellValue('A' . $fila, $ced);
    $sheet->setCellValue('B' . $fila, $nombre);
    $sheet->setCellValue('C' . $fila, strtoupper($genero));

    // Estilo base fila
    $sheet->getStyle('A' . $fila . ':C' . $fila)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ECEFF1']]],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ]);

    for ($d = 1; $d <= $dias_mes; $d++) {
        $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
        $es_dom  = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
        $celda   = $col_ltr . $fila;

        if ($es_dom) {
            // Domingo: gris, vacío
            $sheet->getStyle($celda)->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DOMINGO_BG]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
            ]);
        } elseif (isset($matriz[$ced][$d])) {
            // Tiene registro: 1 + color verde
            $sheet->setCellValue($celda, 1);
            $sheet->getStyle($celda)->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_ASISTIO_BG]],
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
            ]);
            // Acumular totales por género
            $gen_upper = strtoupper(trim($genero));
            if ($gen_upper === 'M' || $gen_upper === 'MASCULINO') {
                $totales_m[$d]++;
            } elseif ($gen_upper === 'F' || $gen_upper === 'FEMENINO') {
                $totales_f[$d]++;
            }
        } else {
            // Sin registro (no domingo): vacío
            $sheet->getStyle($celda)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ECEFF1']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
    }

    $sheet->getRowDimension($fila)->setRowHeight(20);
    $fila++;
}

// ── Fila totales Masculino
$sheet->setCellValue('A' . $fila, 'TOTAL MASCULINO');
$sheet->setCellValue('B' . $fila, '');
$sheet->setCellValue('C' . $fila, 'M');
$sheet->getStyle('A' . $fila . ':C' . $fila)->applyFromArray([
    'font'  => ['bold' => true],
    'fill'  => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_M_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
]);
for ($d = 1; $d <= $dias_mes; $d++) {
    $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
    $es_dom  = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
    $celda   = $col_ltr . $fila;
    if ($es_dom) {
        $sheet->getStyle($celda)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DOMINGO_BG]]]);
    } else {
        $val = $totales_m[$d];
        $sheet->setCellValue($celda, $val > 0 ? $val : '');
        $sheet->getStyle($celda)->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_M_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
        ]);
    }
}
$sheet->getRowDimension($fila)->setRowHeight(22);
$fila_tot_f = $fila + 1;

// ── Fila totales Femenino
$sheet->setCellValue('A' . $fila_tot_f, 'TOTAL FEMENINO');
$sheet->setCellValue('B' . $fila_tot_f, '');
$sheet->setCellValue('C' . $fila_tot_f, 'F');
$sheet->getStyle('A' . $fila_tot_f . ':C' . $fila_tot_f)->applyFromArray([
    'font'  => ['bold' => true],
    'fill'  => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_F_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
]);
for ($d = 1; $d <= $dias_mes; $d++) {
    $col_ltr = Coordinate::stringFromColumnIndex(3 + $d);
    $es_dom  = (date('w', mktime(0, 0, 0, $mes, $d, $anio)) == 0);
    $celda   = $col_ltr . $fila_tot_f;
    if ($es_dom) {
        $sheet->getStyle($celda)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_DOMINGO_BG]]]);
    } else {
        $val = $totales_f[$d];
        $sheet->setCellValue($celda, $val > 0 ? $val : '');
        $sheet->getStyle($celda)->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $COLOR_TOT_F_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '90A4AE']]],
        ]);
    }
}
$sheet->getRowDimension($fila_tot_f)->setRowHeight(22);

// ── Anchos de columna
$sheet->getColumnDimension('A')->setWidth(16);
$sheet->getColumnDimension('B')->setWidth(32);
$sheet->getColumnDimension('C')->setWidth(10);
for ($d = 1; $d <= $dias_mes; $d++) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(3 + $d))->setWidth(6);
}

// ── Freeze panes (fijar las 3 primeras columnas y las 2 primeras filas)
$sheet->freezePane('D3');

// ── Enviar al navegador
if (ob_get_length()) ob_end_clean();
$filename = 'Consolidado_' . $cv_nombre . '_' . $mes_nombre . '_' . $anio;
$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename) . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
