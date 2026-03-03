<?php
// Exportar movimientos de personas para usuario tipo 3 (CONTRATISTA CPSAM)
session_start();

// Validar que el usuario sea tipo 3
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 3) {
    die("Acceso denegado: Esta función es exclusiva para usuarios tipo 3 (CONTRATISTA CPSAM).");
}

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
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : date('Y');
$filtro_mes = isset($_GET['filtro_mes']) && !empty($_GET['filtro_mes']) ? $_GET['filtro_mes'] : '';

// Obtener el ID del usuario de sesión (tipo 3)
$id_usuario_session = intval($_SESSION['id']);

$where = 'WHERE p.estado_persona = 1';

// Filtro obligatorio: solo movimientos creados por este usuario
$where .= " AND mp.id_usuario = $id_usuario_session ";

// Filtro por año (basado en fecha_movimiento)
if ($filtro_anio) {
    $where .= " AND YEAR(mp.fecha_movimiento) = $filtro_anio ";
}

// Filtro por mes
if ($filtro_mes) {
    $where .= " AND MONTH(mp.fecha_movimiento) = " . intval($filtro_mes) . " ";
}

// Consulta SQL para obtener los movimientos
$query = "SELECT 
    mp.id_movimiento_persona,
    p.cedula_persona,
    p.nombres_persona,
    p.apellidos_persona,
    c.descripcion_condicion,
    mp.fecha_movimiento,
    mp.observacion_movimiento,
    g.descripcion_grupo as centro_vida_traslado,
    g_ant.descripcion_grupo as centro_vida_traslado_anterior,
    m.descripcion_meta,
    a.descripcion_actividad,
    ac.descripcion_accion,
    pp.descripcion_politica,
    mp.departamento_procedencia,
    p_grupo.descripcion_grupo as grupo_persona,
    u.nombre as usuario_registro,
    p.sin_convenio
FROM movimiento_persona as mp
JOIN personas as p ON mp.cedula_persona = p.cedula_persona
JOIN condiciones_componente as c ON mp.id_condicion = c.id_condicion
LEFT JOIN grupos g ON mp.id_centro_vida_traslado = g.id_grupo
LEFT JOIN grupos g_ant ON mp.id_centro_vida_traslado_anterior = g_ant.id_grupo
LEFT JOIN grupos p_grupo ON p.id_grupo = p_grupo.id_grupo
LEFT JOIN metas m ON mp.id_meta = m.id_meta
LEFT JOIN actividades a ON mp.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON mp.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON mp.id_politica_publica = pp.id_politica
LEFT JOIN usuarios u ON p.id_usuario = u.id
$where
ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC";

$result = $mysqli->query($query);

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Movimientos');

// Cabeceras
$headers = [
    'ID Movimiento',
    'Cédula',
    'Nombres',
    'Apellidos',
    'Grupo/Centro Vida',
    'Condición/Estado',
    'Fecha Movimiento',
    'Meta',
    'Actividad',
    'Acción',
    'Política Pública',
    'Centro Traslado Anterior',
    'Centro Traslado Nuevo',
    'Dpto. Procedencia',
    'Observaciones',
    'Usuario Registro',
    'Con Convenio'
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
        'startColor' => ['rgb' => '667eea']
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

$sheet->getRowDimension(1)->setRowHeight(35);

// Llenar datos
$row = 2;
if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        // Determinar si es un traslado
        $traslado_info = '';
        if ($data['centro_vida_traslado'] && $data['centro_vida_traslado_anterior']) {
            $traslado_info = 'Trasladado de ' . $data['centro_vida_traslado_anterior'] . ' a ' . $data['centro_vida_traslado'];
        } elseif ($data['centro_vida_traslado']) {
            $traslado_info = $data['centro_vida_traslado'];
        }
        
        $con_convenio = (isset($data['sin_convenio']) && $data['sin_convenio'] == 1) ? 'NO' : 'SÍ';
        
        $rowData = [
            $data['id_movimiento_persona'],
            $data['cedula_persona'],
            $data['nombres_persona'],
            $data['apellidos_persona'],
            $data['grupo_persona'] ?? 'N/A',
            $data['descripcion_condicion'],
            $data['fecha_movimiento'],
            $data['descripcion_meta'] ?? '',
            $data['descripcion_actividad'] ?? '',
            $data['descripcion_accion'] ?? '',
            $data['descripcion_politica'] ?? '',
            $data['centro_vida_traslado_anterior'] ?? '',
            $data['centro_vida_traslado'] ?? '',
            $data['departamento_procedencia'] ?? '',
            $data['observacion_movimiento'] ?? '',
            $data['usuario_registro'] ?? '',
            $con_convenio
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
                    'color' => ['rgb' => 'DDDDDD']
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
    'A' => 12, // ID
    'B' => 15, // Cédula
    'C' => 20, // Nombres
    'D' => 20, // Apellidos
    'E' => 25, // Grupo
    'F' => 25, // Condición
    'G' => 15, // Fecha
    'H' => 25, // Meta
    'I' => 25, // Actividad
    'J' => 25, // Acción
    'K' => 20, // Política
    'L' => 25, // Centro Anterior
    'M' => 25, // Centro Nuevo
    'N' => 20, // Dpto
    'O' => 35, // Observaciones
    'P' => 20, // Usuario
    'Q' => 15  // Con Convenio
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
$filename = 'Movimientos_Tipo3_' . $anio_texto . $mes_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
