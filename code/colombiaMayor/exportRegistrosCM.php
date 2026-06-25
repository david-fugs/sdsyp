<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
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
$usuario_id = intval($_SESSION['id']);

// Obtener filtros de GET
$filtro_fecha_inicio  = isset($_GET['filtro_fecha_inicio'])  && !empty($_GET['filtro_fecha_inicio'])  ? $_GET['filtro_fecha_inicio']  : null;
$filtro_fecha_fin     = isset($_GET['filtro_fecha_fin'])     && !empty($_GET['filtro_fecha_fin'])     ? $_GET['filtro_fecha_fin']     : null;
$filtro_usuario       = isset($_GET['filtro_usuario'])       && is_numeric($_GET['filtro_usuario'])   ? intval($_GET['filtro_usuario']) : null;
$filtro_tipo_usuario  = isset($_GET['filtro_tipo_usuario'])  && is_numeric($_GET['filtro_tipo_usuario']) ? intval($_GET['filtro_tipo_usuario']) : null;

// Filtro por usuario según permisos
$where = "1=1";

// Tipo 9 solo ve sus propios registros
if ($tipo_usuario == 9) {
    $where .= " AND r.usuario_registro = " . $usuario_id;
} elseif ($filtro_usuario) {
    $where .= " AND r.usuario_registro = " . $filtro_usuario;
} elseif ($filtro_tipo_usuario) {
    $where .= " AND u.tipo_usuario = " . $filtro_tipo_usuario;
}

// Aplicar filtros de fecha
if ($filtro_fecha_inicio) {
    $where .= " AND r.fecha_registro_actividad >= '" . $mysqli->real_escape_string($filtro_fecha_inicio) . "'";
}
if ($filtro_fecha_fin) {
    $where .= " AND r.fecha_registro_actividad <= '" . $mysqli->real_escape_string($filtro_fecha_fin) . " 23:59:59'";
}

// Consulta con toda la caracterización
$sql = "SELECT
        r.fecha_registro_actividad,
        p.cedula_persona_cm,
        p.tipo_identificacion_cm,
        p.nombres_persona_cm,
        p.apellidos_persona_cm,
        p.genero_persona_cm,
        p.fecha_nacimiento_cm,
        p.telefono_persona_cm,
        p.telefono_referencia_cm,
        p.referencia_cm,
        p.correo_cm,
        p.direccion_cm,
        p.barrio_cm,
        p.comuna_cm,
        p.zona_cm,
        p.departamento_cm,
        p.municipio_cm,
        p.estado_cm,
        p.condicion_componente,
        c.descripcion_condicion AS condicion,
        m.descripcion_meta AS meta,
        act.descripcion_actividad AS actividad,
        acc.descripcion_accion AS accion,
        pp.descripcion_politica AS politica_publica,
        r.observaciones,
        p.grupo_sisben,
        p.eps,
        p.peso,
        p.talla,
        p.patologias,
        p.factores_riesgo,
        p.factores_preventivos,
        p.ingresos_economicos,
        p.convivencia_actual,
        p.resultado_actividad,
        p.remision,
        p.persona_discapacidad,
        p.cual_discapacidad,
        p.cabeza_hogar,
        p.lider_comunidad,
        p.se_reconoce_como,
        p.orientacion_sexual,
        p.experiencia_migratoria,
        p.grupo_etnico,
        p.tipo_salud,
        p.nivel_educativo,
        p.condicion_ocupacion,
        u.nombre AS registrado_por
        FROM registros_individuales_cm r
        INNER JOIN personas_colombia_mayor p ON r.cedula_persona_cm = p.cedula_persona_cm
        LEFT JOIN condiciones_componente c ON r.id_condicion = c.id_condicion
        LEFT JOIN metas m ON r.id_meta = m.id_meta
        LEFT JOIN actividades act ON r.id_actividad = act.id_actividad
        LEFT JOIN acciones acc ON r.id_accion = acc.id_accion
        LEFT JOIN politicas_publicas pp ON r.id_politica_publica = pp.id_politica
        LEFT JOIN usuarios u ON r.usuario_registro = u.id
        WHERE $where
        ORDER BY r.fecha_registro_actividad DESC, r.id_registro_individual_cm DESC";

$result = $mysqli->query($sql);

if (!$result) {
    die('Error en consulta: ' . $mysqli->error);
}

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Registros CM');

// Encabezados - Fecha Registro primero, Registrado Por último
$headers = [
    'Fecha Registro',
    'Cédula',
    'Tipo Identificación',
    'Nombres',
    'Apellidos',
    'Género',
    'Fecha Nacimiento',
    'Edad',
    'Teléfono',
    'Teléfono Referencia',
    'Referencia',
    'Correo',
    'Dirección',
    'Barrio',
    'Comuna',
    'Zona',
    'Departamento',
    'Municipio',
    'Estado CM',
    'Condición Componente',
    'Condición',
    'Meta',
    'Actividad',
    'Acción',
    'Política Pública',
    'Observaciones',
    'Grupo Sisbén',
    '¿Discapacidad?',
    'Categoría Discapacidad',
    'Se Reconoce Como',
    'Registrado Por',
];

$totalCols = count($headers); // 49
$lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

$col = 1;
foreach ($headers as $header) {
    $sheet->setCellValueByColumnAndRow($col, 1, $header);
    $col++;
}

// Estilo encabezados
$headerRange = 'A1:' . $lastColLetter . '1';
$sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11);
$sheet->getStyle($headerRange)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('667eea');
$sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle($headerRange)->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze pane
$sheet->freezePane('A2');

// Datos
$fila = 2;
while ($row = $result->fetch_assoc()) {
    // Calcular edad
    $edad = '';
    if (!empty($row['fecha_nacimiento_cm'])) {
        try {
            $nacimiento = new DateTime($row['fecha_nacimiento_cm']);
            $hoy = new DateTime();
            $edad = $nacimiento->diff($hoy)->y;
        } catch (Exception $e) {
            $edad = '';
        }
    }

    $valores = [
        !empty($row['fecha_registro_actividad']) ? date('d/m/Y', strtotime($row['fecha_registro_actividad'])) : '',
        $row['cedula_persona_cm']       ?? '',
        $row['tipo_identificacion_cm']  ?? '',
        $row['nombres_persona_cm']      ?? '',
        $row['apellidos_persona_cm']    ?? '',
        $row['genero_persona_cm']       ?? '',
        !empty($row['fecha_nacimiento_cm']) ? date('d/m/Y', strtotime($row['fecha_nacimiento_cm'])) : '',
        $edad,
        $row['telefono_persona_cm']     ?? '',
        $row['telefono_referencia_cm']  ?? '',
        $row['referencia_cm']           ?? '',
        $row['correo_cm']               ?? '',
        $row['direccion_cm']            ?? '',
        $row['barrio_cm']               ?? '',
        $row['comuna_cm']               ?? '',
        $row['zona_cm']                 ?? '',
        $row['departamento_cm']         ?? '',
        $row['municipio_cm']            ?? '',
        $row['estado_cm']               ?? '',
        $row['condicion_componente']    ?? '',
        $row['condicion']               ?? 'N/A',
        $row['meta']                    ?? 'N/A',
        $row['actividad']               ?? 'N/A',
        $row['accion']                  ?? 'N/A',
        $row['politica_publica']        ?? 'N/A',
        $row['observaciones']           ?? '',
        $row['grupo_sisben']            ?? '',
        $row['persona_discapacidad']    ?? '',
        $row['cual_discapacidad']       ?? '',
        $row['se_reconoce_como']        ?? '',
        $row['registrado_por']          ?? 'N/A',
    ];

    foreach ($valores as $idx => $val) {
        $sheet->setCellValueByColumnAndRow($idx + 1, $fila, $val);
    }

    // Filas alternadas
    if ($fila % 2 == 0) {
        $sheet->getStyle('A' . $fila . ':' . $lastColLetter . $fila)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EEF0FF');
    }

    $sheet->getRowDimension($fila)->setRowHeight(18);
    $fila++;
}

// Anchos de columna
$colWidths = [
    18, 15, 18, 22, 22, 10, 16, 6,
    14, 18, 18, 25, 25, 15, 10, 8,
    15, 15, 14, 22,
    25, 35, 35, 35, 35,
    30, 14, 14, 22, 20, 22
];
for ($i = 0; $i < $totalCols; $i++) {
    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    $sheet->getColumnDimension($letter)->setWidth($colWidths[$i] ?? 18);
}

// Bordes
$dataRange = 'A1:' . $lastColLetter . ($fila - 1);
$sheet->getStyle($dataRange)->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['rgb' => 'CCCCCC']
        ],
        'outline' => [
            'borderStyle' => Border::BORDER_MEDIUM,
            'color'       => ['rgb' => '667eea']
        ]
    ],
]);

// Alineación vertical
$sheet->getStyle('A2:' . $lastColLetter . ($fila - 1))->getAlignment()
    ->setVertical(Alignment::VERTICAL_CENTER);

$mysqli->close();

ob_end_clean();

// Descargar
$filename = 'RegistrosIndividualesCM_' . date('Y-m-d_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
