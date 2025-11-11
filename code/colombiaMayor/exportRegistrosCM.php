<?php
session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    exit('Acceso denegado');
}

if (ob_get_length()) {
    ob_clean();
}

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
    $where .= " AND r.usuario_registro = '$usuario_id'";
}

// Consulta (corregida con nombres correctos de tablas)
$sql = "SELECT r.id_registro_individual_cm, 
        p.cedula_persona_cm, 
        CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) as persona_nombre,
        c.descripcion_condicion_cm as condicion, 
        m.descripcion_meta_cm as meta,
        act.descripcion_actividad_cm as actividad, 
        acc.descripcion_accion_cm as accion,
        r.fecha_registro, 
        r.observaciones,
        u.nombre as registrado_por
        FROM registros_individuales_cm r
        INNER JOIN personas_colombia_mayor p ON r.cedula_persona_cm = p.cedula_persona_cm
        LEFT JOIN condiciones_colombia_mayor c ON r.id_condicion_cm = c.id_condicion_cm
        LEFT JOIN metas_colombia_mayor m ON r.id_meta_cm = m.id_meta_cm
        LEFT JOIN actividades_colombia_mayor act ON r.id_actividad_cm = act.id_actividad_cm
        LEFT JOIN acciones_colombia_mayor acc ON r.id_accion_cm = acc.id_accion_cm
        LEFT JOIN usuarios u ON r.usuario_registro = u.id
        WHERE $where
        ORDER BY r.fecha_registro DESC";

$result = $mysqli->query($sql);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Registros CM');

// Encabezados
$headers = ['Cédula', 'Persona', 'Condición', 'Meta', 'Actividad', 'Acción', 'Fecha Registro', 'Observaciones', 'Registrado por'];
$col = 'A';
foreach($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Estilo encabezados - Bonito y amplio
$sheet->getStyle('A1:I1')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:I1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('2E75B6');
$sheet->getStyle('A1:I1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:I1')->getAlignment()
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
    $sheet->setCellValue('D'.$fila, $row['meta']);
    $sheet->setCellValue('E'.$fila, $row['actividad']);
    $sheet->setCellValue('F'.$fila, $row['accion']);
    $sheet->setCellValue('G'.$fila, $row['fecha_registro']);
    $sheet->setCellValue('H'.$fila, $row['observaciones']);
    $sheet->setCellValue('I'.$fila, $row['registrado_por'] ?? 'N/A');
    
    // Filas alternadas con color
    if ($fila % 2 == 0) {
        $sheet->getStyle('A'.$fila.':I'.$fila)->getFill()
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
    'D' => 25,  // Meta
    'E' => 30,  // Actividad
    'F' => 30,  // Acción
    'G' => 18,  // Fecha Registro
    'H' => 40,  // Observaciones
    'I' => 20   // Registrado por
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
$sheet->getStyle('A1:I'.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:I'.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

$mysqli->close();

// Descargar
$filename = 'RegistrosCM_'.date('Y-m-d_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
