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
    $where .= " AND pag.usuario_registro = '$usuario_id'";
}

// Consulta
$sql = "SELECT det.id_detalle_pago_cm, p.cedula_persona_cm, CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) as persona_nombre,
        pag.mes_pago, pag.anio_pago, det.monto, det.estado_cobro, 
        det.fecha_cobro, det.observaciones,
        u.nombre as registrado_por
        FROM detalle_pagos_cm det
        INNER JOIN personas_colombia_mayor p ON det.cedula_persona_cm = p.cedula_persona_cm
        INNER JOIN pagos_colombia_mayor pag ON det.id_pago_cm = pag.id_pago_cm
        LEFT JOIN usuarios u ON pag.usuario_registro = u.id
        WHERE $where
        ORDER BY pag.anio_pago DESC, pag.mes_pago DESC, p.apellidos_persona_cm, p.nombres_persona_cm";

$result = $mysqli->query($sql);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Pagos CM');

// Encabezados
$headers = ['Cédula', 'Persona', 'Mes', 'Año', 'Período', 'Monto', 'Estado Cobro', 'Fecha Cobro', 'Observaciones', 'Registrado por'];
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

// Nombres de meses
$meses = [
    '01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', 
    '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto',
    '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'
];

// Datos
$fila = 2;
while($row = $result->fetch_assoc()) {
    $periodo = $meses[$row['mes_pago']] . ' ' . $row['anio_pago'];
    
    $sheet->setCellValue('A'.$fila, $row['cedula']);
    $sheet->setCellValue('B'.$fila, $row['persona_nombre']);
    $sheet->setCellValue('C'.$fila, $row['mes_pago']);
    $sheet->setCellValue('D'.$fila, $row['anio_pago']);
    $sheet->setCellValue('E'.$fila, $periodo);
    $sheet->setCellValue('F'.$fila, number_format($row['monto'], 2));
    $sheet->setCellValue('G'.$fila, $row['estado_cobro']);
    $sheet->setCellValue('H'.$fila, $row['fecha_cobro'] ?? '-');
    $sheet->setCellValue('I'.$fila, $row['observaciones'] ?? '');
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
    'B' => 30,  // Persona
    'C' => 10,  // Mes
    'D' => 10,  // Año
    'E' => 18,  // Período
    'F' => 15,  // Monto
    'G' => 18,  // Estado Cobro
    'H' => 15,  // Fecha Cobro
    'I' => 40,  // Observaciones
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
$filename = 'PagosCM_'.date('Y-m-d_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
