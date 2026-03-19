<?php
// Exportar actividades masivas centro vida desde página de reportes
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
$filtro_fecha_inicio = isset($_GET['filtro_fecha_inicio']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['filtro_fecha_inicio']) ? $_GET['filtro_fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['filtro_fecha_fin']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['filtro_fecha_fin']) ? $_GET['filtro_fecha_fin'] : '';

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

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

$where = '';
if ($filtro_fecha_inicio && $filtro_fecha_fin) {
    $where .= " AND mcv.fecha_atencion BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin' ";
} else {
    if ($filtro_anio) {
        $where .= " AND YEAR(mcv.fecha_atencion) = $filtro_anio ";
    }
    // Aplicar filtro de mes si se seleccionó
    if ($filtro_mes) {
        $where .= " AND MONTH(mcv.fecha_atencion) = " . intval($filtro_mes) . " ";
    }
}

// Si se seleccionó un grupo específico, agregar ese filtro
if ($filtro_grupo && !$filtro_todos_grupos) {
    $filtro_grupo_int = intval($filtro_grupo);
    $where .= " AND g.id_grupo = $filtro_grupo_int ";
} elseif ($filtro_todos_grupos && !empty($grupos_a_exportar)) {
    // Si es "Todos CPSAM" o "Todos CV", filtrar por todos esos grupos
    $ids_grupos = array_map(function($g) { return intval($g['id_grupo']); }, $grupos_a_exportar);
    $where .= " AND g.id_grupo IN (" . implode(',', $ids_grupos) . ") ";
}

// Aplicar filtro de usuario si se seleccionó
if ($filtro_usuario) {
    $where .= " AND mcv.id_usuario = $filtro_usuario ";
}

// Si es usuario tipo 2 (INGENIERO), filtrar solo actividades de su grupo y usuarios de su grupo
if ($tipo_usuario == 2 && isset($_SESSION['id_grupo'])) {
    $id_grupo_session = intval($_SESSION['id_grupo']);
    $where .= " AND g.id_grupo = $id_grupo_session ";
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
    $where .= " AND mcv.id_usuario = $id_usuario ";
}
// Si es usuario tipo 5 o 10, filtrar por su grupo
elseif (($tipo_usuario == 5 || $tipo_usuario == 10) && isset($_SESSION['id_grupo'])) {
    $id_grupo_session = intval($_SESSION['id_grupo']);
    $where .= " AND g.id_grupo = $id_grupo_session ";
}

$sql = "SELECT 
 mcv.id_masiva_centro_vida AS id_registro,
 m.descripcion_meta,
 a.descripcion_actividad,
 ac.descripcion_accion,
 acv.descripcion_actividad AS actividad_centro_vida,
 pp.descripcion_politica,
 g.descripcion_grupo AS centro_vida,
 mcv.fecha_atencion,
 mcv.nombre_lider,
 mcv.telefono_contacto,
 c.nombre_com AS nombre_comuna,
 mcv.medio_verificacion,
 mcv.cantidad_masculino,
 mcv.cantidad_femenino,
 mcv.observacion_actividad,
 mcv.tipo_actividad,
 u1.nombre AS digitado_por,
 u2.nombre AS funcionario_responsable_nombre
FROM masiva_centro_vida mcv
LEFT JOIN metas m ON mcv.id_meta=m.id_meta
LEFT JOIN actividades a ON mcv.id_actividad=a.id_actividad
LEFT JOIN acciones ac ON mcv.id_accion=ac.id_accion
LEFT JOIN actividad_centro_vida acv ON mcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
LEFT JOIN politicas_publicas pp ON mcv.politica_publica = pp.id_politica
LEFT JOIN grupos g ON mcv.id_centro_vida=g.id_grupo
LEFT JOIN comunas c ON mcv.id_comuna=c.id_com
LEFT JOIN usuarios u1 ON mcv.id_usuario=u1.id
LEFT JOIN usuarios u2 ON mcv.funcionario_responsable=u2.id
WHERE 1 $where $where_grupos_filtro
ORDER BY " . ($filtro_todos_grupos ? "g.descripcion_grupo ASC, mcv.fecha_atencion DESC" : "mcv.fecha_atencion DESC");

$result = $mysqli->query($sql);

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Masivo Centro Vida');

// Cabeceras
$headers = [
    'ID', 'Meta', 'Actividad Plan', 'Acción', 'Actividad Centro Vida', 'Política Pública', 'Centro Vida', 
    'Fecha Atención', 'Nombre Líder', 'Teléfono', 'Comuna', 'Medio Verificación', 
    'Masculino', 'Femenino', 'Total', 'Tipo Actividad', 'Observación', 'Digitado por', 'Funcionario Responsable'
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
        'startColor' => ['rgb' => 'C8E6C9']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'A5D6A7']
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
            $data['actividad_centro_vida'],
            $data['descripcion_politica'],
            $data['centro_vida'],
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
                    'color' => ['rgb' => 'E8F5E9']
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

$anio_texto = ($filtro_fecha_inicio && $filtro_fecha_fin) ? $filtro_fecha_inicio . '_' . $filtro_fecha_fin : ($filtro_anio ? $filtro_anio : 'Todos');
$mes_texto = '';
if (!$filtro_fecha_inicio && $filtro_mes) {
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $mes_texto = '_' . $meses[intval($filtro_mes)];
}
$filename = 'Actividades_CentroVida_Masivo_' . $anio_texto . $mes_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
