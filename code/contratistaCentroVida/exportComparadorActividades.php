<?php
// Exportar comparador de actividades a Excel con 2 hojas
session_start();
if (ob_get_length()) { 
    header('Content-Type: text/plain; charset=utf-8'); 
    echo 'Salida previa'; 
    exit; 
}

require_once '../../conexion.php';
require_once '../filtros_grupo_usuario.php';
$mysqli->set_charset('utf8mb4');
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Filtros
$filtro_dia = isset($_GET['filtro_dia']) ? intval($_GET['filtro_dia']) : '';
$filtro_fecha = isset($_GET['filtro_fecha']) ? $mysqli->real_escape_string($_GET['filtro_fecha']) : '';
$filtro_mes = isset($_GET['filtro_mes']) ? intval($_GET['filtro_mes']) : '';
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_funcionario = isset($_GET['filtro_funcionario']) ? intval($_GET['filtro_funcionario']) : '';

// Construir WHERE para masivas
$where_masivas = '';
if ($filtro_dia) {
    $where_masivas .= " AND DAY(mcv.fecha_atencion) = $filtro_dia";
}
if ($filtro_fecha) {
    $where_masivas .= " AND mcv.fecha_atencion = '$filtro_fecha'";
}
if ($filtro_mes) {
    $where_masivas .= " AND MONTH(mcv.fecha_atencion) = $filtro_mes";
}
if ($filtro_anio) {
    $where_masivas .= " AND YEAR(mcv.fecha_atencion) = $filtro_anio";
}
if ($filtro_funcionario) {
    $where_masivas .= " AND mcv.id_usuario = $filtro_funcionario";
}

// Construir WHERE para individuales
$where_individuales = '';
if ($filtro_fecha) {
    // Fecha específica tiene prioridad
    $where_individuales .= " AND rcvf.fecha_atencion = '$filtro_fecha'";
} else {
    // Aplicar filtros de día, mes, año individualmente
    if ($filtro_dia) {
        $where_individuales .= " AND DAY(rcvf.fecha_atencion) = $filtro_dia";
    }
    if ($filtro_mes) {
        $where_individuales .= " AND MONTH(rcvf.fecha_atencion) = $filtro_mes";
    }
    if ($filtro_anio) {
        $where_individuales .= " AND YEAR(rcvf.fecha_atencion) = $filtro_anio";
    }
}

// Aplicar filtros por grupo de usuario (tipo 11: INGENIERO CENTRO VIDA)
$where_grupo_usuario_masivas = '';
$where_grupo_usuario_individuales = '';
if (debeAplicarFiltroGrupo($_SESSION['tipo_usuario'] ?? null) && isset($_SESSION['id_grupo'])) {
    $id_grupo = intval($_SESSION['id_grupo']);
    $where_grupo_usuario_masivas = " AND mcv.id_centro_vida = $id_grupo";
    $where_grupo_usuario_individuales = " AND p.id_grupo = $id_grupo";
}

// ========================
// HOJA 1: ACTIVIDADES MASIVAS
// ========================
$sql_masivas = "SELECT 
    mcv.id_masiva_centro_vida AS id_registro,
    m.descripcion_meta,
    a.descripcion_actividad,
    ac.descripcion_accion,
    acv.descripcion_actividad AS actividad_centro_vida,
    pp.descripcion_politica,
    g.descripcion_grupo AS centro_vida,
    mcv.fecha_atencion,
    mcv.nombre_lider,
    mcv.telefono_contacto,
    c.nombre_com AS nombre_comuna,
    mcv.medio_verificacion,
    mcv.cantidad_masculino,
    mcv.cantidad_femenino,
    mcv.observacion_actividad,
    mcv.tipo_actividad,
    u1.nombre AS digitado_por,
    u2.nombre AS funcionario_responsable_nombre
FROM masiva_centro_vida mcv
LEFT JOIN metas m ON mcv.id_meta = m.id_meta
LEFT JOIN actividades a ON mcv.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON mcv.id_accion = ac.id_accion
LEFT JOIN actividad_centro_vida acv ON mcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
LEFT JOIN politicas_publicas pp ON mcv.politica_publica = pp.id_politica
LEFT JOIN grupos g ON mcv.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON mcv.id_comuna = c.id_com
LEFT JOIN usuarios u1 ON mcv.id_usuario = u1.id
LEFT JOIN usuarios u2 ON mcv.funcionario_responsable = u2.id
WHERE 1 $where_masivas $where_grupo_usuario_masivas
ORDER BY mcv.fecha_atencion DESC";

$res_masivas = $mysqli->query($sql_masivas);

// ========================
// HOJA 2: ACTIVIDADES INDIVIDUALES
// ========================
// Agrupar por registro pero contar fechas filtradas
$sql_individuales = "SELECT DISTINCT
    rcv.id_registro_centro_vida,
    p.cedula_persona,
    p.nombres_persona,
    p.apellidos_persona,
    p.fecha_nacimiento,
    p.genero_persona,
    p.telefono_persona,
    g.descripcion_grupo as descripcion_grupo,
    b.nombre_bar as nombre_barrio,
    c.nombre_com as nombre_comuna,
    acv.descripcion_actividad as actividad_centro_vida,
    pol.descripcion_politica as descripcion_politica,
    rcv.departamento_procedencia,
    rcv.observacion,
    rcv.funcionario_registro,
    rcv.fecha_registro,
    (SELECT GROUP_CONCAT(rcvf2.fecha_atencion ORDER BY rcvf2.fecha_atencion ASC SEPARATOR ', ')
     FROM registro_centro_vida_fechas rcvf2
     WHERE rcvf2.id_registro_centro_vida = rcv.id_registro_centro_vida
     $where_individuales
    ) as fechas_filtradas,
    (SELECT COUNT(*)
     FROM registro_centro_vida_fechas rcvf3
     WHERE rcvf3.id_registro_centro_vida = rcv.id_registro_centro_vida
     $where_individuales
    ) as total_fechas_filtradas,
    m.descripcion_meta,
    a.descripcion_actividad as descripcion_actividad_plan,
    ac.descripcion_accion
FROM registro_centro_vida rcv
INNER JOIN personas p ON rcv.cedula_persona = p.cedula_persona
INNER JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida
LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
LEFT JOIN actividad_centro_vida acv ON rcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
LEFT JOIN politicas_publicas pol ON rcv.politica_publica = pol.id_politica
LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
LEFT JOIN metas m ON rcv.id_meta = m.id_meta
LEFT JOIN actividades a ON rcv.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON rcv.id_accion = ac.id_accion
WHERE 1 $where_individuales $where_grupo_usuario_individuales
GROUP BY rcv.id_registro_centro_vida 
ORDER BY rcv.fecha_registro DESC";

$res_individuales = $mysqli->query($sql_individuales);

// Crear Spreadsheet
$spreadsheet = new Spreadsheet();

// ========================
// HOJA 1: MASIVAS
// ========================
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Masivas');

$headers_masivas = [
    'ID', 'Meta', 'Actividad Plan', 'Acción', 'Actividad Centro Vida', 'Política Pública', 
    'Centro Vida', 'Fecha Atención', 'Nombre Líder', 'Teléfono', 'Comuna', 'Medio Verificación', 
    'Masculino', 'Femenino', 'Total', 'Tipo Actividad', 'Observación', 'Digitado por', 
    'Funcionario Responsable'
];

$col = 'A';
foreach ($headers_masivas as $h) {
    $sheet1->setCellValue($col . '1', $h);
    $col++;
}

$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers_masivas));
$sheet1->getStyle('A1:' . $lastCol . '1')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F093FB']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']]]
]);
$sheet1->getRowDimension(1)->setRowHeight(32);

$r = 2;
$total_masculino_suma = 0;
$total_femenino_suma = 0;

if ($res_masivas && $res_masivas->num_rows > 0) {
    while ($row = $res_masivas->fetch_assoc()) {
        $masculino = intval($row['cantidad_masculino']);
        $femenino = intval($row['cantidad_femenino']);
        $total = $masculino + $femenino;
        
        $total_masculino_suma += $masculino;
        $total_femenino_suma += $femenino;

        $data = [
            $row['id_registro'], 
            $row['descripcion_meta'], 
            $row['descripcion_actividad'], 
            $row['descripcion_accion'], 
            $row['actividad_centro_vida'], 
            $row['descripcion_politica'],
            $row['centro_vida'], 
            $row['fecha_atencion'], 
            $row['nombre_lider'], 
            $row['telefono_contacto'], 
            $row['nombre_comuna'], 
            $row['medio_verificacion'],
            $masculino, 
            $femenino, 
            $total, 
            $row['tipo_actividad'], 
            $row['observacion_actividad'], 
            $row['digitado_por'], 
            $row['funcionario_responsable_nombre']
        ];

        $col = 'A';
        foreach ($data as $val) {
            $sheet1->setCellValue($col . $r, $val);
            $col++;
        }

        $sheet1->getStyle('A' . $r . ':' . $lastCol . $r)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ECEFF1']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $r++;
    }
}

// Fila de totales para masivas
$sheet1->setCellValue('A' . $r, 'TOTALES');
$sheet1->setCellValue('M' . $r, $total_masculino_suma);
$sheet1->setCellValue('N' . $r, $total_femenino_suma);
$sheet1->setCellValue('O' . $r, $total_masculino_suma + $total_femenino_suma);
$sheet1->getStyle('A' . $r . ':' . $lastCol . $r)->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEB3B']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']]]
]);

// Ajustar anchos
for ($i = 1; $i <= count($headers_masivas); $i++) {
    $sheet1->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(20);
}

// ========================
// HOJA 2: INDIVIDUALES
// ========================
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Individuales');

$headers_individuales = [
    'ID', 'Cédula', 'Nombres', 'Apellidos', 'Fecha Nacimiento', 'Género', 'Teléfono', 
    'Grupo', 'Barrio', 'Comuna', 'Actividad Centro Vida', 'Política Pública', 
    'Departamento Procedencia', 'Observación', 'Funcionario', 'Fecha Registro', 
    'Fechas Atención', 'Cantidad Fechas', 'Meta', 'Actividad Plan', 'Acción'
];

$col = 'A';
foreach ($headers_individuales as $h) {
    $sheet2->setCellValue($col . '1', $h);
    $col++;
}

$lastCol2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers_individuales));
$sheet2->getStyle('A1:' . $lastCol2 . '1')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4FACFE']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']]]
]);
$sheet2->getRowDimension(1)->setRowHeight(32);

$r = 2;
$total_individuales = 0;

if ($res_individuales && $res_individuales->num_rows > 0) {
    while ($row = $res_individuales->fetch_assoc()) {
        // Contar las fechas que cumplen el filtro
        $cantidad_fechas = intval($row['total_fechas_filtradas']);
        $total_individuales += $cantidad_fechas;
        
        // Formatear fechas
        $fecha_nac = '';
        if (!empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] != '0000-00-00') {
            $fecha_nac = date('d/m/Y', strtotime($row['fecha_nacimiento']));
        }
        
        $fecha_registro = '';
        if (!empty($row['fecha_registro'])) {
            $fecha_registro = date('d/m/Y H:i', strtotime($row['fecha_registro']));
        }
        
        // Formatear fechas filtradas
        $fechas_display = '';
        if (!empty($row['fechas_filtradas'])) {
            $parts = explode(', ', $row['fechas_filtradas']);
            $map = array_map(function($f) { 
                return ($f && $f != '0000-00-00') ? date('d/m/Y', strtotime($f)) : ''; 
            }, $parts);
            $fechas_display = implode(', ', array_filter($map));
        }

        $data = [
            $row['id_registro_centro_vida'],
            $row['cedula_persona'],
            $row['nombres_persona'],
            $row['apellidos_persona'],
            $fecha_nac,
            $row['genero_persona'],
            $row['telefono_persona'],
            $row['descripcion_grupo'],
            $row['nombre_barrio'],
            $row['nombre_comuna'],
            $row['actividad_centro_vida'],
            $row['descripcion_politica'],
            $row['departamento_procedencia'],
            $row['observacion'],
            $row['funcionario_registro'],
            $fecha_registro,
            $fechas_display,
            $cantidad_fechas,
            $row['descripcion_meta'],
            $row['descripcion_actividad_plan'],
            $row['descripcion_accion']
        ];

        $col = 'A';
        foreach ($data as $val) {
            $sheet2->setCellValue($col . $r, $val);
            $col++;
        }

        $sheet2->getStyle('A' . $r . ':' . $lastCol2 . $r)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'ECEFF1']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $r++;
    }
}

// Fila de totales para individuales
$sheet2->setCellValue('A' . $r, 'TOTAL REGISTROS');
$sheet2->setCellValue('B' . $r, $total_individuales);
$sheet2->getStyle('A' . $r . ':' . $lastCol2 . $r)->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEB3B']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']]]
]);

// Ajustar anchos
for ($i = 1; $i <= count($headers_individuales); $i++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
    // La columna de fechas de atención más ancha
    if ($i === 17) { // Fechas Atención
        $sheet2->getColumnDimension($colLetter)->setWidth(50);
    } else {
        $sheet2->getColumnDimension($colLetter)->setWidth(20);
    }
}

// ========================
// HOJA 3: RESUMEN (OPCIONAL)
// ========================
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Resumen');

$sheet3->setCellValue('A1', 'RESUMEN DE COMPARACIÓN');
$sheet3->mergeCells('A1:C1');
$sheet3->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

$sheet3->setCellValue('A3', 'Concepto');
$sheet3->setCellValue('B3', 'Cantidad');
$sheet3->setCellValue('C3', 'Porcentaje');
$sheet3->getStyle('A3:C3')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

$total_general = ($total_masculino_suma + $total_femenino_suma) + $total_individuales;

$sheet3->setCellValue('A4', 'Actividades Masivas (Total)');
$sheet3->setCellValue('B4', $total_masculino_suma + $total_femenino_suma);
$sheet3->setCellValue('C4', $total_general > 0 ? round((($total_masculino_suma + $total_femenino_suma) / $total_general) * 100, 2) . '%' : '0%');

$sheet3->setCellValue('A5', '  - Masculino');
$sheet3->setCellValue('B5', $total_masculino_suma);
$sheet3->setCellValue('C5', '');

$sheet3->setCellValue('A6', '  - Femenino');
$sheet3->setCellValue('B6', $total_femenino_suma);
$sheet3->setCellValue('C6', '');

$sheet3->setCellValue('A7', 'Actividades Individuales');
$sheet3->setCellValue('B7', $total_individuales);
$sheet3->setCellValue('C7', $total_general > 0 ? round(($total_individuales / $total_general) * 100, 2) . '%' : '0%');

$sheet3->setCellValue('A8', 'TOTAL GENERAL');
$sheet3->setCellValue('B8', $total_general);
$sheet3->setCellValue('C8', '100%');
$sheet3->getStyle('A8:C8')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '43E97B']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Información de filtros
$sheet3->setCellValue('A10', 'FILTROS APLICADOS:');
$sheet3->getStyle('A10')->getFont()->setBold(true);

$filtros_texto = [];
if ($filtro_dia) $filtros_texto[] = "Día: $filtro_dia";
if ($filtro_fecha) $filtros_texto[] = "Fecha: $filtro_fecha";
if ($filtro_mes) {
    $meses_nombres = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                      7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $filtros_texto[] = "Mes: " . $meses_nombres[$filtro_mes];
}
if ($filtro_anio) $filtros_texto[] = "Año: $filtro_anio";

if (!empty($filtros_texto)) {
    $row_filtro = 11;
    foreach ($filtros_texto as $filtro) {
        $sheet3->setCellValue('A' . $row_filtro, $filtro);
        $row_filtro++;
    }
} else {
    $sheet3->setCellValue('A11', 'Sin filtros (todos los datos)');
}

// Ajustar anchos
$sheet3->getColumnDimension('A')->setWidth(35);
$sheet3->getColumnDimension('B')->setWidth(20);
$sheet3->getColumnDimension('C')->setWidth(20);

// Exportar
if (ob_get_length()) ob_end_clean();
$filename = 'Comparador_Actividades_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
