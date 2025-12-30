<?php
// Exportar actividades de contratista desde página de reportes
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
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_grupo = isset($_GET['filtro_grupo']) ? intval($_GET['filtro_grupo']) : '';

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

$where = '';
if ($filtro_anio) {
    $where .= " AND YEAR(ra.fecha_atencion) = $filtro_anio ";
}

// Si se seleccionó un grupo específico, agregar ese filtro
if ($filtro_grupo) {
    $where .= " AND g.id_grupo = $filtro_grupo ";
}

// Si es usuario tipo 3 (CONTRATISTA CPSAM), filtrar solo sus actividades
if ($tipo_usuario == 3 && isset($_SESSION['id'])) {
    $id_usuario = intval($_SESSION['id']);
    $where .= " AND ra.id_usuario = $id_usuario ";
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
$sheet->setTitle('Actividades Contratista');

// Cabeceras
$headers = [
    'ID', 'Meta', 'Actividad', 'Acción', 'Política Pública', 'Lugar del Evento', 'Otro Lugar', 'Fecha Atención',
    'Nombre Líder', 'Teléfono Contacto', 'Comuna/Corregimiento', 'Medio de Verificación',
    'Cant. Masculino', 'Cant. Femenino', 'Total personas', 'Tipo Actividad', 'Observación Actividad', 'Digitado por', 'Funcionario Responsable'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Aplicar estilos a encabezados
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
$sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E0F7FA']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'B0BEC5']
        ]
    ]
]);

$sheet->getRowDimension(1)->setRowHeight(32);

// Llenar datos
$row = 2;
if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $totalPersonas = $data['cantidad_masculino'] + $data['cantidad_femenino'];
        
        $rowData = [
            $data['id_registro'],
            $data['descripcion_meta'],
            $data['descripcion_actividad'],
            $data['descripcion_accion'],
            $data['descripcion_politica'],
            $data['centro_vida'],
            $data['otro_lugar'],
            $data['fecha_atencion'],
            $data['nombre_lider'],
            $data['telefono_contacto'],
            $data['nombre_comuna'],
            $data['medio_verificacion'],
            $data['cantidad_masculino'],
            $data['cantidad_femenino'],
            $totalPersonas,
            $data['tipo_actividad'],
            $data['observacion_actividad'],
            $data['digitado_por'],
            $data['funcionario_responsable_nombre']
        ];
        
        $col = 'A';
        foreach ($rowData as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        
        // Aplicar estilos a la fila
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'ECEFF1']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);
        
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;
    }
}

// Ajustar anchos de columna
for ($i = 1; $i <= count($headers); $i++) {
    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(25);
}

// Limpiar buffer y generar archivo
if (ob_get_length()) {
    ob_end_clean();
}

$anio_texto = $filtro_anio ? $filtro_anio : 'Todos';
$filename = 'Actividades_Contratista_' . $anio_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
