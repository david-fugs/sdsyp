<?php
// Exportar actividades individuales de contratista desde página de reportes
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
$filtro_mes = isset($_GET['filtro_mes']) && !empty($_GET['filtro_mes']) ? $_GET['filtro_mes'] : '';
$filtro_usuario = isset($_GET['filtro_usuario']) ? intval($_GET['filtro_usuario']) : '';

// Aplicar filtro de grupos según tipo de usuario
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

$where = 'WHERE p.estado_persona = 1';

// Filtro por año (basado en fecha_registro)
if ($filtro_anio) {
    $where .= " AND YEAR(ri.fecha_registro) = $filtro_anio ";
}

// Aplicar filtro de mes si se seleccionó
if ($filtro_mes) {
    $where .= " AND MONTH(ri.fecha_registro) = " . intval($filtro_mes) . " ";
}

// Si se seleccionó un grupo específico, agregar ese filtro
if ($filtro_grupo) {
    $where .= " AND p.id_grupo = $filtro_grupo ";
}

// Aplicar filtro de usuario si se seleccionó
if ($filtro_usuario) {
    $where .= " AND ri.id_usuario = $filtro_usuario ";
}

// Si es usuario tipo 3 (CONTRATISTA CPSAM), filtrar solo sus actividades
if ($tipo_usuario == 3 && isset($_SESSION['id'])) {
    $id_usuario = intval($_SESSION['id']);
    $where .= " AND ri.id_usuario = $id_usuario ";
}

// Aplicar filtro adicional para usuarios técnicos (tipos 4 y 5)
$where .= $where_grupos_filtro;

$query = "SELECT 
    ri.id_registro_individual,
    p.cedula_persona,
    p.nombres_persona,
    p.apellidos_persona,
    c.descripcion_condicion,
    ri.fecha_registro,
    ri.observacion_registro,
    g.descripcion_grupo as centro_vida_traslado,
    m.descripcion_meta,
    a.descripcion_actividad,
    ac.descripcion_accion,
    pp.descripcion_politica,
    ri.departamento_procedencia,
    u.nombre as nombre_usuario,
    p_grupo.descripcion_grupo as grupo_persona
FROM registro_individual as ri
JOIN personas as p ON ri.cedula_persona = p.cedula_persona
JOIN condiciones_componente as c ON ri.id_condicion = c.id_condicion
LEFT JOIN grupos g ON ri.id_centro_vida_traslado = g.id_grupo
LEFT JOIN grupos p_grupo ON p.id_grupo = p_grupo.id_grupo
LEFT JOIN metas m ON ri.id_meta = m.id_meta
LEFT JOIN actividades a ON ri.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON ri.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON ri.id_politica_publica = pp.id_politica
LEFT JOIN usuarios u ON ri.id_usuario = u.id
$where
ORDER BY ri.fecha_registro DESC
";

$result = $mysqli->query($query);

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Actividades Individuales');

// Cabeceras
$headers = [
    'ID',
    'Cédula',
    'Nombres',
    'Apellidos',
    'Grupo/Centro Vida',
    'Condición',
    'Fecha Registro',
    'Meta',
    'Actividad',
    'Acción',
    'Política Pública',
    'Centro Traslado',
    'Dpto. Procedencia',
    'Observaciones',
    'Usuario Registro'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Aplicar estilos a encabezados
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
$sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'FFA726']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

$sheet->getRowDimension(1)->setRowHeight(32);

// Llenar datos
$row = 2;
if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $rowData = [
            $data['id_registro_individual'],
            $data['cedula_persona'],
            $data['nombres_persona'],
            $data['apellidos_persona'],
            $data['grupo_persona'] ?? 'N/A',
            $data['descripcion_condicion'],
            $data['fecha_registro'],
            $data['descripcion_meta'] ?? '',
            $data['descripcion_actividad'] ?? '',
            $data['descripcion_accion'] ?? '',
            $data['descripcion_politica'] ?? '',
            $data['centro_vida_traslado'] ?? '',
            $data['departamento_procedencia'] ?? '',
            $data['observacion_registro'] ?? '',
            $data['nombre_usuario'] ?? ''
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
$columnWidths = [
    'A' => 10,  // ID
    'B' => 15,  // Cédula
    'C' => 20,  // Nombres
    'D' => 20,  // Apellidos
    'E' => 25,  // Grupo
    'F' => 25,  // Condición
    'G' => 15,  // Fecha
    'H' => 25,  // Meta
    'I' => 25,  // Actividad
    'J' => 25,  // Acción
    'K' => 20,  // Política
    'L' => 25,  // Centro
    'M' => 20,  // Dpto
    'N' => 35,  // Observaciones
    'O' => 20   // Usuario
];

foreach ($columnWidths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

// Limpiar buffer y generar archivo
if (ob_get_length()) {
    ob_end_clean();
}

$anio_texto = $filtro_anio ? $filtro_anio : 'Todos';
$mes_texto = '';
if ($filtro_mes) {
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $mes_texto = '_' . $meses[intval($filtro_mes)];
}
$filename = 'Actividades_Individuales_' . $anio_texto . $mes_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
