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

// Consulta
$sql = "SELECT r.id_registro_masivo_cm,
        r.fecha_registro,
        m.descripcion_meta,
        a.descripcion_actividad,
        ac.descripcion_accion,
        pp.descripcion_politica,
        r.cantidad_masculino,
        r.cantidad_femenino,
        r.total_personas,
        r.observaciones,
        u.nombre as registrado_por,
        r.fecha_creacion
        FROM registros_masivos_cm r
        LEFT JOIN metas m ON r.id_meta = m.id_meta
        LEFT JOIN actividades a ON r.id_actividad = a.id_actividad
        LEFT JOIN acciones ac ON r.id_accion = ac.id_accion
        LEFT JOIN politicas_publicas pp ON r.id_politica_publica = pp.id_politica_publica
        LEFT JOIN usuarios u ON r.usuario_registro = u.id
        WHERE $where
        ORDER BY r.fecha_registro DESC, r.fecha_creacion DESC";

$result = $mysqli->query($sql);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Registros Masivos CM');

// Encabezados
$headers = ['ID', 'Fecha Registro', 'Meta', 'Actividad', 'Acción', 'Política Pública', 'Masculino', 'Femenino', 'Total', 'Observaciones', 'Registrado por', 'Fecha Creación'];
$col = 'A';
foreach($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Estilo encabezados - Bonito y amplio
$sheet->getStyle('A1:L1')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:L1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('2E75B6');
$sheet->getStyle('A1:L1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:L1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze pane (congelar encabezado)
$sheet->freezePane('A2');

// Datos
$fila = 2;
while($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A'.$fila, $row['id_registro_masivo_cm']);
    $sheet->setCellValue('B'.$fila, date('d/m/Y', strtotime($row['fecha_registro'])));
    $sheet->setCellValue('C'.$fila, $row['descripcion_meta'] ?? 'N/A');
    $sheet->setCellValue('D'.$fila, $row['descripcion_actividad'] ?? 'N/A');
    $sheet->setCellValue('E'.$fila, $row['descripcion_accion'] ?? 'N/A');
    $sheet->setCellValue('F'.$fila, $row['descripcion_politica'] ?? 'N/A');
    $sheet->setCellValue('G'.$fila, $row['cantidad_masculino']);
    $sheet->setCellValue('H'.$fila, $row['cantidad_femenino']);
    $sheet->setCellValue('I'.$fila, $row['total_personas']);
    $sheet->setCellValue('J'.$fila, $row['observaciones'] ?? '');
    $sheet->setCellValue('K'.$fila, $row['registrado_por'] ?? 'N/A');
    $sheet->setCellValue('L'.$fila, date('d/m/Y H:i', strtotime($row['fecha_creacion'])));
    
    // Filas alternadas con color
    if ($fila % 2 == 0) {
        $sheet->getStyle('A'.$fila.':L'.$fila)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7F0FF');
    }
    
    // Altura de fila más amplia
    $sheet->getRowDimension($fila)->setRowHeight(18);
    $fila++;
}

// Ajustar columnas más amplias
$columnWidths = [
    'A' => 10,  // ID
    'B' => 15,  // Fecha Registro
    'C' => 30,  // Meta
    'D' => 35,  // Actividad
    'E' => 35,  // Acción
    'F' => 35,  // Política Pública
    'G' => 12,  // Masculino
    'H' => 12,  // Femenino
    'I' => 10,  // Total
    'J' => 40,  // Observaciones
    'K' => 20,  // Registrado por
    'L' => 18   // Fecha Creación
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
$sheet->getStyle('A1:L'.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:L'.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Alineación centrada para columnas numéricas
$sheet->getStyle('G2:I'.($fila-1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$mysqli->close();

// Descargar
$filename = 'RegistrosMasivosCM_'.date('Y-m-d_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
