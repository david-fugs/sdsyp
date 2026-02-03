<?php
// Iniciar sesión para obtener tipo_usuario
session_start();

// Eliminar cualquier salida previa
if (ob_get_length()) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Hay salida previa al header. El archivo Excel se corromperá.\n";
    exit;
}

require_once '../filtros_grupos.php';
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

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_usuario_session = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

$where = '';

// Filtro para usuarios tipo 3 (CONTRATISTA): solo exportar sus propias actividades
if ($tipo_usuario == 3 && $id_usuario_session) {
    $where .= " AND ra.id_usuario = " . $id_usuario_session;
}

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
       g.descripcion_grupo AS centro_vida, ra.otro_lugar, ra.fecha_atencion, ra.nombre_lider, ra.telefono_contacto, c.nombre_com AS nombre_comuna,
       ra.medio_verificacion, ra.cantidad_masculino, ra.cantidad_femenino, ra.tipo_actividad, ra.observacion_actividad,
       ra.id_usuario, u1.nombre AS digitado_por, ra.funcionario_responsable, u2.nombre AS funcionario_responsable_nombre
FROM registro_actividades AS ra
LEFT JOIN metas m ON ra.id_meta = m.id_meta
LEFT JOIN actividades a ON ra.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON ra.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON ra.politica_publica = pp.id_politica
LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON ra.id_comuna = c.id_com
LEFT JOIN usuarios u1 ON ra.id_usuario = u1.id
LEFT JOIN usuarios u2 ON CAST(ra.funcionario_responsable AS UNSIGNED) = u2.id AND ra.funcionario_responsable REGEXP '^[0-9]+$'
WHERE 1 $where $where_grupos_filtro
ORDER BY ra.fecha_atencion DESC
";

$result = $mysqli->query($query);

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Actividades');

// Cabeceras
$headers = [
    'Meta', 'Actividad', 'Acción', 'Política Pública', 'Lugar del Evento', 'Otro Lugar', 'Fecha Atención',
    'Nombre Líder', 'Teléfono Contacto', 'Comuna/Corregimiento', 'Medio de Verificación',
    'Cant. Masculino', 'Cant. Femenino', 'Total personas', 'Tipo Actividad', 'Observación Actividad', 'Digitado por', 'Funcionario Responsable'
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
    // Determinar funcionario responsable
    $funcionarioResp = $row['funcionario_responsable_nombre'] ? $row['funcionario_responsable_nombre'] : ($row['funcionario_responsable'] ?: 'N/A');
    
    $data = [
        $row['descripcion_meta'],
        $row['descripcion_actividad'],
        $row['descripcion_accion'],
        $row['descripcion_politica'],
        $row['centro_vida'] ?: '',  // Columna E: Lugar del Evento (Centro Vida)
        $row['otro_lugar'] ?: '',    // Columna F: Otro Lugar
        $row['fecha_atencion'],
        $row['nombre_lider'],
        $row['telefono_contacto'],
        $row['nombre_comuna'],
        $row['medio_verificacion'],
        $row['cantidad_masculino'],
        $row['cantidad_femenino'],
        $row['cantidad_masculino'] + $row['cantidad_femenino'],
        $row['tipo_actividad'],
        $row['observacion_actividad'],
        $row['digitado_por'],
        $funcionarioResp
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
