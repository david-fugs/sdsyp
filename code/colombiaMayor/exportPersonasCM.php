<?php
session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    exit('Acceso denegado');
}

// Eliminar cualquier salida previa
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
    $where .= " AND p.usuario_registro = '$usuario_id'";
}

// Consulta (corregida con nombres reales de columnas)
$sql = "SELECT p.cedula_persona_cm as cedula, 
        p.nombres_persona_cm as nombre, 
        p.apellidos_persona_cm as apellido, 
        p.fecha_nacimiento_cm as fecha_nacimiento, 
        TIMESTAMPDIFF(YEAR, p.fecha_nacimiento_cm, CURDATE()) as edad,
        p.direccion_cm as direccion, 
        p.telefono_persona_cm as telefono, 
        p.estado_cm, 
        p.fecha_registro,
        u.nombre as registrado_por
        FROM personas_colombia_mayor p
        LEFT JOIN usuarios u ON p.usuario_registro = u.id
        WHERE $where
        ORDER BY p.apellidos_persona_cm, p.nombres_persona_cm";

$result = $mysqli->query($sql);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Personas CM');

// Encabezados
$headers = ['Cédula', 'Nombre', 'Apellido', 'Fecha Nacimiento', 'Edad', 'Dirección', 'Teléfono', 'Estado', 'Fecha Registro', 'Registrado por'];
$col = 'A';
foreach($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Estilo encabezados - Bonito y amplio
$sheet->getStyle('A1:J1')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:J1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('2E75B6');
$sheet->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:J1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze pane (congelar encabezado)
$sheet->freezePane('A2');

// Datos
$fila = 2;
while($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A'.$fila, $row['cedula']);
    $sheet->setCellValue('B'.$fila, $row['nombre']);
    $sheet->setCellValue('C'.$fila, $row['apellido']);
    $sheet->setCellValue('D'.$fila, $row['fecha_nacimiento']);
    $sheet->setCellValue('E'.$fila, $row['edad']);
    $sheet->setCellValue('F'.$fila, $row['direccion']);
    $sheet->setCellValue('G'.$fila, $row['telefono']);
    $sheet->setCellValue('H'.$fila, $row['estado_cm']);
    $sheet->setCellValue('I'.$fila, $row['fecha_registro']);
    $sheet->setCellValue('J'.$fila, $row['registrado_por'] ?? 'N/A');
    
    // Filas alternadas con color
    if ($fila % 2 == 0) {
        $sheet->getStyle('A'.$fila.':J'.$fila)->getFill()
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
    'B' => 20,  // Nombre
    'C' => 20,  // Apellido
    'D' => 18,  // Fecha Nacimiento
    'E' => 10,  // Edad
    'F' => 30,  // Dirección
    'G' => 15,  // Teléfono
    'H' => 20,  // Estado
    'I' => 18,  // Fecha Registro
    'J' => 20   // Registrado por
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
$sheet->getStyle('A1:J'.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:J'.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

$mysqli->close();

// Descargar
$filename = 'PersonasCM_'.date('Y-m-d_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
