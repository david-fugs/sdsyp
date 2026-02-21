<?php
// Exportar movimientos de personas desde página de reportes
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
$filtro_anio = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$filtro_grupo = isset($_GET['filtro_grupo']) && !empty($_GET['filtro_grupo']) ? intval($_GET['filtro_grupo']) : '';
$filtro_mes = isset($_GET['filtro_mes']) && !empty($_GET['filtro_mes']) ? $_GET['filtro_mes'] : '';
$filtro_usuario = isset($_GET['filtro_usuario']) && !empty($_GET['filtro_usuario']) ? intval($_GET['filtro_usuario']) : '';

// Aplicar filtro de grupos según tipo de usuario
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

$where = 'WHERE p.estado_persona = 1';

// Filtro por año (basado en fecha_movimiento)
if ($filtro_anio) {
    $where .= " AND YEAR(mp.fecha_movimiento) = $filtro_anio ";
}

// Filtro por mes
if ($filtro_mes) {
    $where .= " AND MONTH(mp.fecha_movimiento) = " . intval($filtro_mes) . " ";
}

// Si se seleccionó un grupo específico
if ($filtro_grupo) {
    $where .= " AND p.id_grupo = $filtro_grupo ";
}

// Filtro por usuario
if ($filtro_usuario) {
    $where .= " AND p.id_usuario = $filtro_usuario ";
}

// Si es usuario tipo 2 (INGENIERO), filtrar solo personas de su grupo y usuarios de su grupo
if ($tipo_usuario == 2 && $id_grupo_session) {
    $where .= " AND p.id_grupo = " . intval($id_grupo_session) . " ";
    // Si se especificó usuario, verificar que sea del mismo grupo
    if ($filtro_usuario) {
        $query_check = "SELECT id FROM usuarios WHERE id = $filtro_usuario AND id_grupo = " . intval($id_grupo_session);
        $result_check = $mysqli->query($query_check);
        if (!$result_check || $result_check->num_rows == 0) {
            die("Acceso denegado: No puede exportar datos de usuarios de otros grupos.");
        }
    }
}
// Si es usuario tipo 3 (CONTRATISTA CPSAM), filtrar solo sus personas
elseif ($tipo_usuario == 3 && isset($_SESSION['id'])) {
    $id_usuario = intval($_SESSION['id']);
    $where .= " AND p.id_usuario = $id_usuario ";
}

// Aplicar filtro de grupos
$where .= $where_grupos_filtro;

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
    u.nombre as usuario_registro
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
            $data['usuario_registro'] ?? ''
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
    'P' => 20  // Usuario
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
$filename = 'Movimientos_Personas_' . $anio_texto . $mes_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
