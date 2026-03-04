<?php
session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    exit('Acceso denegado');
}

// Limpiar cualquier salida previa
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once '../../conexion.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

// Filtro por usuario
$where = "1=1";
if($tipo_usuario == 9) {
    $where .= " AND m.usuario_registro = '$usuario_id'";
}

// Consulta (corregida con nombres reales de columnas y tabla condiciones_componente)
$sql = "SELECT m.id_movimiento_cm, 
        m.cedula_persona_cm as cedula, 
        CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) as persona_nombre,
        c.descripcion_condicion as condicion, 
        m.fecha_movimiento_cm as fecha_movimiento, 
        m.observaciones_cm as observaciones,
        u.nombre as registrado_por
        FROM movimientos_colombia_mayor m
        INNER JOIN personas_colombia_mayor p ON m.cedula_persona_cm = p.cedula_persona_cm
        LEFT JOIN condiciones_componente c ON m.id_condicion_cm = c.id_condicion
        LEFT JOIN usuarios u ON m.usuario_registro = u.id
        WHERE $where
        ORDER BY m.fecha_movimiento_cm DESC";

$result = $mysqli->query($sql);

if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Movimientos CM');

// Encabezados
$headers = ['Cédula', 'Persona', 'Condición', 'Fecha Movimiento', 'Observaciones', 'Registrado por'];
$col = 'A';
foreach($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Estilo encabezados - Bonito y amplio
$sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:F1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('2E75B6');
$sheet->getStyle('A1:F1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:F1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze pane (congelar encabezado)
$sheet->freezePane('A2');

// Datos
$fila = 2;
while($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A'.$fila, $row['cedula']);
    $sheet->setCellValue('B'.$fila, $row['persona_nombre']);
    $sheet->setCellValue('C'.$fila, $row['condicion']);
    $sheet->setCellValue('D'.$fila, $row['fecha_movimiento']);
    $sheet->setCellValue('E'.$fila, $row['observaciones']);
    $sheet->setCellValue('F'.$fila, $row['registrado_por'] ?? 'N/A');
    
    // Filas alternadas con color
    if ($fila % 2 == 0) {
        $sheet->getStyle('A'.$fila.':F'.$fila)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7F0FF');
    }
    
    // Altura de fila más amplia
    $sheet->getRowDimension($fila)->setRowHeight(18);
    $fila++;
}

// Ajustar columnas más amplias
$columnWidths = [
    'A' => 15,  // Cédula
    'B' => 30,  // Persona
    'C' => 25,  // Condición
    'D' => 18,  // Fecha Movimiento
    'E' => 40,  // Observaciones
    'F' => 20   // Registrado por
];
foreach($columnWidths as $col => $width) {
    $sheet->getColumnDimension($col)->setWidth($width);
}

// Bordes más prominentes
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ],
        'outline' => [
            'borderStyle' => Border::BORDER_MEDIUM,
            'color' => ['rgb' => '000000']
        ]
    ],
];
$sheet->getStyle('A1:F'.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:F'.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

$mysqli->close();

// Limpiar el buffer de salida antes de enviar los headers
ob_end_clean();

// Descargar
$filename = 'MovimientosCM_'.date('Y-m-d_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

