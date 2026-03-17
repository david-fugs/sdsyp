<?php
// Exportar actividades masivas e individuales de contratista combinadas en un solo archivo Excel con 2 hojas
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
$filtro_grupo = isset($_GET['filtro_grupo']) ? $_GET['filtro_grupo'] : '';
$filtro_mes = isset($_GET['filtro_mes']) && !empty($_GET['filtro_mes']) ? $_GET['filtro_mes'] : '';
$filtro_usuario = isset($_GET['filtro_usuario']) ? intval($_GET['filtro_usuario']) : '';

// Detectar si se seleccionó "Todos CPSAM" o "Todos Centros Vida"
$filtro_todos_grupos = false;
$prefijo_grupo = '';
$grupos_a_exportar = [];

if ($filtro_grupo === 'TODOS_CPSAM') {
    $filtro_todos_grupos = true;
    $prefijo_grupo = 'CPSAM';
    // Obtener todos los grupos que empiezan con CPSAM
    $query_grupos = "SELECT id_grupo, descripcion_grupo FROM grupos WHERE descripcion_grupo LIKE 'CPSAM%' ORDER BY descripcion_grupo ASC";
    $result_grupos_query = $mysqli->query($query_grupos);
    if ($result_grupos_query) {
        while ($grupo_row = $result_grupos_query->fetch_assoc()) {
            $grupos_a_exportar[] = $grupo_row;
        }
    }
} elseif ($filtro_grupo === 'TODOS_CV') {
    $filtro_todos_grupos = true;
    $prefijo_grupo = 'CV';
    // Obtener todos los grupos que empiezan con CV
    $query_grupos = "SELECT id_grupo, descripcion_grupo FROM grupos WHERE descripcion_grupo LIKE 'CV%' ORDER BY descripcion_grupo ASC";
    $result_grupos_query = $mysqli->query($query_grupos);
    if ($result_grupos_query) {
        while ($grupo_row = $result_grupos_query->fetch_assoc()) {
            $grupos_a_exportar[] = $grupo_row;
        }
    }
}

// Aplicar filtro de grupos según tipo de usuario
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');

// ============== HOJA 1: ACTIVIDADES MASIVAS ==============
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Actividades Masivas');

// Filtros para actividades masivas
$where_grupos_filtro_masivas = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
$where_masivas = '';
if ($filtro_anio) {
    $where_masivas .= " AND YEAR(ra.fecha_atencion) = $filtro_anio ";
}
if ($filtro_mes) {
    $where_masivas .= " AND MONTH(ra.fecha_atencion) = " . intval($filtro_mes) . " ";
}
if ($filtro_grupo && !$filtro_todos_grupos) {
    $filtro_grupo_int = intval($filtro_grupo);
    $where_masivas .= " AND g.id_grupo = $filtro_grupo_int ";
} elseif ($filtro_todos_grupos && !empty($grupos_a_exportar)) {
    // Si es "Todos CPSAM" o "Todos CV", filtrar por todos esos grupos
    $ids_grupos = array_map(function($g) { return intval($g['id_grupo']); }, $grupos_a_exportar);
    $where_masivas .= " AND g.id_grupo IN (" . implode(',', $ids_grupos) . ") ";
}
if ($filtro_usuario) {
    $where_masivas .= " AND ra.id_usuario = $filtro_usuario ";
}
// Si es usuario tipo 2 (INGENIERO), filtrar solo actividades de su grupo y usuarios de su grupo
if ($tipo_usuario == 2 && isset($_SESSION['id_grupo'])) {
    $id_grupo_session = intval($_SESSION['id_grupo']);
    $where_masivas .= " AND g.id_grupo = $id_grupo_session ";
    // Si se especificó usuario, verificar que sea del mismo grupo
    if ($filtro_usuario) {
        $query_check = "SELECT id FROM usuarios WHERE id = $filtro_usuario AND id_grupo = $id_grupo_session";
        $result_check = $mysqli->query($query_check);
        if (!$result_check || $result_check->num_rows == 0) {
            die("Acceso denegado: No puede exportar datos de usuarios de otros grupos.");
        }
    }
}
// Si es usuario tipo 3 (CONTRATISTA CPSAM), filtrar solo sus actividades
elseif ($tipo_usuario == 3 && isset($_SESSION['id'])) {
    $id_usuario = intval($_SESSION['id']);
    $where_masivas .= " AND ra.id_usuario = $id_usuario ";
}

$query_masivas = "SELECT ra.id_registro, m.descripcion_meta, a.descripcion_actividad, ac.descripcion_accion, pp.descripcion_politica,
       g.descripcion_grupo AS centro_vida, ra.otro_lugar, ra.fecha_atencion, ra.nombre_lider, ra.telefono_contacto, c.nombre_com AS nombre_comuna,
       b.nombre_bar AS nombre_barrio,
       ra.medio_verificacion, ra.cantidad_masculino, ra.cantidad_femenino, ra.tipo_actividad, ra.observacion_actividad,
       ra.id_usuario, u1.nombre AS digitado_por, ra.funcionario_responsable, u2.nombre AS funcionario_responsable_nombre
FROM registro_actividades AS ra
LEFT JOIN metas m ON ra.id_meta = m.id_meta
LEFT JOIN actividades a ON ra.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON ra.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON ra.politica_publica = pp.id_politica
LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON ra.id_comuna = c.id_com
LEFT JOIN barrios b ON ra.id_barrio = b.id_bar
LEFT JOIN usuarios u1 ON ra.id_usuario = u1.id
LEFT JOIN usuarios u2 ON CAST(ra.funcionario_responsable AS UNSIGNED) = u2.id AND ra.funcionario_responsable REGEXP '^[0-9]+$'
WHERE 1 $where_masivas $where_grupos_filtro_masivas
ORDER BY " . ($filtro_todos_grupos ? "g.descripcion_grupo ASC, ra.fecha_atencion DESC" : "ra.fecha_atencion DESC") . "
";

$result_masivas = $mysqli->query($query_masivas);

// Cabeceras hoja 1
$headers_masivas = [
    'ID', 'Meta', 'Actividad', 'Acción', 'Política Pública', 'Lugar del Evento', 'Otro Lugar', 'Fecha Atención',
    'Nombre Líder', 'Teléfono Contacto', 'Barrio', 'Comuna/Corregimiento', 'Medio de Verificación',
    'Cant. Masculino', 'Cant. Femenino', 'Total personas', 'Tipo Actividad', 'Observación Actividad', 'Digitado por', 'Funcionario Responsable'
];

$col = 'A';
foreach ($headers_masivas as $header) {
    $sheet1->setCellValue($col . '1', $header);
    $col++;
}

// Aplicar estilos a encabezados de hoja 1
$lastCol_masivas = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers_masivas));
$sheet1->getStyle('A1:' . $lastCol_masivas . '1')->applyFromArray([
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

$sheet1->getRowDimension(1)->setRowHeight(32);

// Llenar datos de actividades masivas
$row = 2;
$grupo_anterior = '';
if ($result_masivas && $result_masivas->num_rows > 0) {
    while ($data = $result_masivas->fetch_assoc()) {
        // Si es "Todos CPSAM" o "Todos CV", agregar fila separadora entre grupos
        if ($filtro_todos_grupos && $data['centro_vida'] != $grupo_anterior && $grupo_anterior != '') {
            // Agregar fila de separación
            $sheet1->mergeCells('A' . $row . ':' . $lastCol_masivas . $row);
            $sheet1->setCellValue('A' . $row, '--- ' . strtoupper($data['centro_vida']) . ' ---');
            $sheet1->getStyle('A' . $row . ':' . $lastCol_masivas . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'B0E0E6']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            $sheet1->getRowDimension($row)->setRowHeight(30);
            $row++;
        }
        $grupo_anterior = $data['centro_vida'];

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
            $data['nombre_barrio'],
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
            $sheet1->setCellValue($col . $row, $value);
            $col++;
        }

        // Aplicar estilos a la fila
        $sheet1->getStyle('A' . $row . ':' . $lastCol_masivas . $row)->applyFromArray([
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

        $sheet1->getRowDimension($row)->setRowHeight(24);
        $row++;
    }
}

// Ajustar anchos de columna hoja 1
for ($i = 1; $i <= count($headers_masivas); $i++) {
    $sheet1->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(25);
}

// ============== HOJA 2: ACTIVIDADES INDIVIDUALES ==============
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Actividades Individuales');

// Filtros para actividades individuales
$where_grupos_filtro_individuales = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');
$where_individuales = 'WHERE p.estado_persona = 1';

if ($filtro_anio) {
    $where_individuales .= " AND YEAR(ri.fecha_registro) = $filtro_anio ";
}
if ($filtro_mes) {
    $where_individuales .= " AND MONTH(ri.fecha_registro) = " . intval($filtro_mes) . " ";
}
if ($filtro_grupo && !$filtro_todos_grupos) {
    $filtro_grupo_int = intval($filtro_grupo);
    $where_individuales .= " AND p.id_grupo = $filtro_grupo_int ";
} elseif ($filtro_todos_grupos && !empty($grupos_a_exportar)) {
    // Si es "Todos CPSAM" o "Todos CV", filtrar por todos esos grupos
    $ids_grupos = array_map(function($g) { return intval($g['id_grupo']); }, $grupos_a_exportar);
    $where_individuales .= " AND p.id_grupo IN (" . implode(',', $ids_grupos) . ") ";
}
if ($filtro_usuario) {
    $where_individuales .= " AND ri.id_usuario = $filtro_usuario ";
}
// Si es usuario tipo 2 (INGENIERO), filtrar solo actividades de su grupo y usuarios de su grupo
if ($tipo_usuario == 2 && isset($_SESSION['id_grupo'])) {
    $id_grupo_session = intval($_SESSION['id_grupo']);
    $where_individuales .= " AND p.id_grupo = $id_grupo_session ";
    // Si se especificó usuario, verificar que sea del mismo grupo
    if ($filtro_usuario) {
        $query_check = "SELECT id FROM usuarios WHERE id = $filtro_usuario AND id_grupo = $id_grupo_session";
        $result_check = $mysqli->query($query_check);
        if (!$result_check || $result_check->num_rows == 0) {
            die("Acceso denegado: No puede exportar datos de usuarios de otros grupos.");
        }
    }
}
// Si es usuario tipo 3 (CONTRATISTA CPSAM), filtrar solo sus actividades
elseif ($tipo_usuario == 3 && isset($_SESSION['id'])) {
    $id_usuario = intval($_SESSION['id']);
    $where_individuales .= " AND ri.id_usuario = $id_usuario ";
}
$where_individuales .= $where_grupos_filtro_individuales;

$query_individuales = "SELECT 
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
    b.nombre_bar AS nombre_barrio,
    com.nombre_com AS nombre_com,
    u.nombre as nombre_usuario,
    p_grupo.descripcion_grupo as grupo_persona,
    p.sin_convenio
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
LEFT JOIN barrios b ON ri.id_barrio = b.id_bar
LEFT JOIN comunas com ON ri.id_comuna = com.id_com
$where_individuales
ORDER BY " . ($filtro_todos_grupos ? "p_grupo.descripcion_grupo ASC, ri.fecha_registro DESC" : "ri.fecha_registro DESC") . "
";

$result_individuales = $mysqli->query($query_individuales);

// Cabeceras hoja 2
$headers_individuales = [
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
    'Barrio',
    'Comuna/Corregimiento',
    'Observaciones',
    'Usuario Registro',
    'Con Convenio'
];

$col = 'A';
foreach ($headers_individuales as $header) {
    $sheet2->setCellValue($col . '1', $header);
    $col++;
}

// Aplicar estilos a encabezados de hoja 2
$lastCol_individuales = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers_individuales));
$sheet2->getStyle('A1:' . $lastCol_individuales . '1')->applyFromArray([
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

$sheet2->getRowDimension(1)->setRowHeight(32);

// Llenar datos de actividades individuales
$row = 2;
$grupo_anterior = '';
if ($result_individuales && $result_individuales->num_rows > 0) {
    while ($data = $result_individuales->fetch_assoc()) {
        // Si es "Todos CPSAM" o "Todos CV", agregar fila separadora entre grupos
        if ($filtro_todos_grupos && $data['grupo_persona'] != $grupo_anterior && $grupo_anterior != '') {
            // Agregar fila de separación
            $sheet2->mergeCells('A' . $row . ':' . $lastCol_individuales . $row);
            $sheet2->setCellValue('A' . $row, '--- ' . strtoupper($data['grupo_persona']) . ' ---');
            $sheet2->getStyle('A' . $row . ':' . $lastCol_individuales . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE0B2']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            $sheet2->getRowDimension($row)->setRowHeight(30);
            $row++;
        }
        $grupo_anterior = $data['grupo_persona'];

        $con_convenio = (isset($data['sin_convenio']) && $data['sin_convenio'] == 1) ? 'NO' : 'SÍ';

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
            $data['nombre_barrio'] ?? '',
            $data['nombre_com'] ?? '',
            $data['observacion_registro'] ?? '',
            $data['nombre_usuario'] ?? '',
            $con_convenio
        ];

        $col = 'A';
        foreach ($rowData as $value) {
            $sheet2->setCellValue($col . $row, $value);
            $col++;
        }

        // Aplicar estilos a la fila
        $sheet2->getStyle('A' . $row . ':' . $lastCol_individuales . $row)->applyFromArray([
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

        $sheet2->getRowDimension($row)->setRowHeight(24);
        $row++;
    }
}

// Ajustar anchos de columna hoja 2
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
    $sheet2->getColumnDimension($column)->setWidth($width);
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
$filename = 'Actividades_Contratista_Combinadas_' . $anio_texto . $mes_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
