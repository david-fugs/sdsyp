<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
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

// Obtener filtros de GET
$filtro_fecha_inicio = isset($_GET['filtro_fecha_inicio']) && !empty($_GET['filtro_fecha_inicio']) ? $_GET['filtro_fecha_inicio'] : null;
$filtro_fecha_fin = isset($_GET['filtro_fecha_fin']) && !empty($_GET['filtro_fecha_fin']) ? $_GET['filtro_fecha_fin'] : null;
$filtro_usuario = isset($_GET['filtro_usuario']) && !empty($_GET['filtro_usuario']) ? intval($_GET['filtro_usuario']) : null;

// Filtro por usuario según permisos
$where = "1=1";

// Tipo 9 solo ve sus propios registros (sin importar el filtro)
if($tipo_usuario == 9) {
    $where .= " AND r.usuario_registro = '$usuario_id'";
}
// Tipo 8 puede filtrar por usuarios 8 y 9 si se pasa el filtro
elseif($tipo_usuario == 8 && $filtro_usuario) {
    $where .= " AND r.usuario_registro = " . $filtro_usuario;
}
// Tipo 1 (Admin) puede filtrar por usuario si se pasa
elseif($tipo_usuario == 1 && $filtro_usuario) {
    $where .= " AND r.usuario_registro = " . $filtro_usuario;
}

// Aplicar filtros de fecha
if ($filtro_fecha_inicio) {
    $where .= " AND r.fecha_registro >= '" . $mysqli->real_escape_string($filtro_fecha_inicio) . "'";
}
if ($filtro_fecha_fin) {
    $where .= " AND r.fecha_registro <= '" . $mysqli->real_escape_string($filtro_fecha_fin) . "'";
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
        u.nombre as registrado_por
        FROM registros_masivos_cm r
        LEFT JOIN metas m ON r.id_meta = m.id_meta
        LEFT JOIN actividades a ON r.id_actividad = a.id_actividad
        LEFT JOIN acciones ac ON r.id_accion = ac.id_accion
        LEFT JOIN politicas_publicas pp ON r.id_politica_publica = pp.id_politica
        LEFT JOIN usuarios u ON r.usuario_registro = u.id
        WHERE $where
        ORDER BY r.fecha_registro DESC, r.id_registro_masivo_cm DESC";

$result = $mysqli->query($sql);

if (!$result) {
    die('Error en consulta: ' . $mysqli->error);
}

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Registros Masivos CM');

// Encabezados
$headers = ['ID', 'Fecha Registro', 'Meta', 'Actividad', 'Acción', 'Política Pública', 'Masculino', 'Femenino', 'Total', 'Observaciones', 'Registrado por'];
$col = 'A';
foreach($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Estilo encabezados - Bonito y amplio
$sheet->getStyle('A1:K1')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:K1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('2E75B6');
$sheet->getStyle('A1:K1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:K1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze pane (congelar encabezado)
$sheet->freezePane('A2');

// Datos
$fila = 2;
while($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A'.$fila, $row['id_registro_masivo_cm'] ?? '');
    $sheet->setCellValue('B'.$fila, !empty($row['fecha_registro']) ? date('d/m/Y', strtotime($row['fecha_registro'])) : '');
    $sheet->setCellValue('C'.$fila, $row['descripcion_meta'] ?? 'N/A');
    $sheet->setCellValue('D'.$fila, $row['descripcion_actividad'] ?? 'N/A');
    $sheet->setCellValue('E'.$fila, $row['descripcion_accion'] ?? 'N/A');
    $sheet->setCellValue('F'.$fila, $row['descripcion_politica'] ?? 'N/A');
    $sheet->setCellValue('G'.$fila, $row['cantidad_masculino'] ?? 0);
    $sheet->setCellValue('H'.$fila, $row['cantidad_femenino'] ?? 0);
    $sheet->setCellValue('I'.$fila, $row['total_personas'] ?? 0);
    $sheet->setCellValue('J'.$fila, $row['observaciones'] ?? '');
    $sheet->setCellValue('K'.$fila, $row['registrado_por'] ?? 'N/A');
    
    // Filas alternadas con color
    if ($fila % 2 == 0) {
        $sheet->getStyle('A'.$fila.':K'.$fila)->getFill()
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
    'K' => 20   // Registrado por
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
$sheet->getStyle('A1:K'.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:K'.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Alineación centrada para columnas numéricas
$sheet->getStyle('G2:I'.($fila-1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$mysqli->close();

ob_end_clean();

// Descargar
$filename = 'RegistrosMasivosCM_'.date('Y-m-d_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
