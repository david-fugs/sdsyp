<?php
// Eliminar cualquier salida previa
if (ob_get_length()) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Hay salida previa al header. El archivo Excel se corromperá.\n";
    exit;
}

require_once '../../conexion.php';
if (isset($mysqli)) {
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES 'utf8mb4'");
    $mysqli->query("SET CHARACTER SET 'utf8mb4'");
}

require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Obtener filtros
// Filtros año, mes y funcionario responsable
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_mes = isset($_GET['filtro_mes']) ? intval($_GET['filtro_mes']) : '';
$filtro_funcionario = isset($_GET['filtro_funcionario']) ? intval($_GET['filtro_funcionario']) : '';
$where = '';
if ($filtro_anio) {
    $where .= " AND YEAR(ra.fecha_atencion) = $filtro_anio ";
}
if ($filtro_mes) {
    $where .= " AND MONTH(ra.fecha_atencion) = $filtro_mes ";
}
if ($filtro_funcionario) {
    $where .= " AND ra.id_usuario = $filtro_funcionario ";
}

$query = "SELECT ra.id_registro, m.descripcion_meta, a.descripcion_actividad, ac.descripcion_accion, pp.descripcion_politica,
       g.descripcion_grupo AS centro_vida, ra.fecha_atencion, ra.nombre_lider, ra.telefono_contacto, c.nombre_com AS nombre_comuna,
       ra.medio_verificacion, ra.cantidad_masculino, ra.cantidad_femenino, ra.tipo_actividad, ra.observacion_actividad,
       ra.id_usuario, u.nombre AS funcionario_responsable
FROM registro_actividades AS ra
LEFT JOIN metas m ON ra.id_meta = m.id_meta
LEFT JOIN actividades a ON ra.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON ra.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON ra.politica_publica = pp.id_politica
LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON ra.id_comuna = c.id_com
LEFT JOIN usuarios u ON ra.id_usuario = u.id
WHERE 1 $where
ORDER BY ra.fecha_atencion DESC
";

$result = $mysqli->query($query);

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Actividades');

// Cabeceras
$headers = [
    'ID', 'Meta', 'Actividad', 'Acción', 'Política Pública', 'Centro Vida', 'Fecha Atención',
    'Nombre Líder', 'Teléfono Contacto', 'Comuna/Corregimiento', 'Medio de Verificación',
    'Cant. Masculino', 'Cant. Femenino', 'Tipo Actividad', 'Observación Actividad', 'Funcionario Responsable'
];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Estilos cabecera
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
$headerRange = 'A1:' . $lastCol . '1';
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => [ 'bold' => true, 'size' => 11, 'color' => ['rgb' => '2D3436'] ],
    'fill' => [ 'fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3A0'] ],
    'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true ],
    'borders' => [ 'allBorders' => [ 'borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC'] ] ]
]);
$sheet->getRowDimension(1)->setRowHeight(40);

// Datos
$row_num = 2;
while ($row = $result->fetch_assoc()) {
    $data = [
        $row['id_registro'],
        $row['descripcion_meta'],
        $row['descripcion_actividad'],
        $row['descripcion_accion'],
        $row['descripcion_politica'],
        $row['centro_vida'],
        $row['fecha_atencion'],
        $row['nombre_lider'],
        $row['telefono_contacto'],
        $row['nombre_comuna'],
        $row['medio_verificacion'],
        $row['cantidad_masculino'],
        $row['cantidad_femenino'],
        $row['tipo_actividad'],
        $row['observacion_actividad'],
        $row['funcionario_responsable']
    ];
    $col = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($col . $row_num, $value);
        $col++;
    }
    $dataRange = 'A' . $row_num . ':' . $lastCol . $row_num;
    $sheet->getStyle($dataRange)->applyFromArray([
        'borders' => [ 'allBorders' => [ 'borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0'] ] ],
        'alignment' => [ 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true ]
    ]);
    $row_num++;
}

// Ancho columnas
for ($colIdx = 1; $colIdx <= count($headers); $colIdx++) {
    $colL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
    $sheet->getColumnDimension($colL)->setWidth(30);
}
for ($i = 2; $i < $row_num; $i++) {
    $sheet->getRowDimension($i)->setRowHeight(25);
}

$fileName = 'Actividades_' . ($filtro_anio ?: 'todos') . '_' . ($filtro_mes ?: 'todos') . '_' . date('Y-m-d_H-i-s') . '.xlsx';
if (ob_get_length()) { ob_end_clean(); }
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
