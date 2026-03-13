<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit;
}

// Solo tipo_usuario 1 (admin) y 11 (ingeniero centro vida) pueden acceder
$tipo_usuario_session = $_SESSION['tipo_usuario'];
if ($tipo_usuario_session != 1 && $tipo_usuario_session != 11) {
    header("Location: ../../access.php");
    exit;
}

if (ob_get_length()) {
    ob_end_clean();
}

require_once '../../conexion.php';
if (isset($mysqli)) {
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES 'utf8mb4'");
}

require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Restringir por grupo si es tipo 11
$where_tipo_11 = '';
if ($tipo_usuario_session == 11 && isset($_SESSION['id_grupo']) && $_SESSION['id_grupo']) {
    $id_grupo_session = intval($_SESSION['id_grupo']);
    $where_tipo_11 = " AND u.id_grupo = $id_grupo_session";
}

// Mapeo de tipo_usuario a texto
function getTipoUsuarioTexto($tipo) {
    switch ($tipo) {
        case 1:  return 'ADMINISTRADOR';
        case 2:  return 'CPSAM O CENTRO VIDA';
        case 3:  return 'CONTRATISTA CPSAM';
        case 4:  return 'TÉCNICO CPSAM';
        case 5:  return 'TÉCNICO CENTRO VIDA';
        case 7:  return 'SIN ACCESO';
        case 8:  return 'TÉCNICO COLOMBIA MAYOR';
        case 9:  return 'CONTRATISTA COLOMBIA MAYOR';
        case 10: return 'CONTRATISTA CENTRO VIDA';
        case 11: return 'INGENIERO CENTRO VIDA';
        case 12: return 'CONTRATISTA CENTRO VIDA ALCALDIA';
        default: return 'DESCONOCIDO';
    }
}

// Consulta principal: usuarios con conteo de registros por tabla
// registro_centro_vida usa funcionario_registro (nombre del usuario, no id)
// masiva_centro_vida y registro_actividades usan id_usuario
$query = "
SELECT
    u.id,
    u.usuario,
    u.nombre,
    COALESCE(g.descripcion_grupo, '') AS centro_asociado,
    u.tipo_usuario,
    (SELECT COUNT(*) FROM personas p WHERE p.id_usuario = u.id) AS personas_registradas,
    (SELECT COUNT(*) FROM registro_actividades ra WHERE ra.id_usuario = u.id) AS masivas_cpsam,
    (SELECT COUNT(*) FROM registro_individual ri WHERE ri.id_usuario = u.id) AS individuales_cpsam,
    (SELECT COUNT(*) FROM masiva_centro_vida mcv WHERE mcv.id_usuario = u.id) AS masivas_cv,
    (SELECT COUNT(*) FROM registro_centro_vida rcv WHERE rcv.funcionario_registro = u.nombre) AS individuales_cv,
    (SELECT COUNT(*) FROM movimiento_persona mp WHERE mp.id_usuario = u.id) AS movimientos_personas
FROM usuarios u
LEFT JOIN grupos g ON u.id_grupo = g.id_grupo
WHERE u.estado_usu = 1
$where_tipo_11
ORDER BY u.nombre ASC
";

$result = $mysqli->query($query);
if (!$result) {
    die('Error en la consulta: ' . $mysqli->error);
}

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Usuarios');

// Cabeceras
$headers = [
    'No.',
    'Usuario',
    'Nombre',
    'Centro Asociado',
    'Tipo Usuario',
    'Personas Registradas',
    'Act. Masivas CPSAM',
    'Act. Individuales CPSAM',
    'Act. Masivas Centro Vida',
    'Act. Individuales Centro Vida',
    'Movimientos Personas',
    'Total Registros'
];

$col_idx = 1;
foreach ($headers as $header) {
    $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx);
    $sheet->setCellValue($col_letter . '1', $header);
    $col_idx++;
}

$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
$headerRange = 'A1:' . $lastCol . '1';

// Estilo cabecera
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 11,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2C3E50']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['rgb' => 'AAAAAA']
        ]
    ]
]);
$sheet->getRowDimension(1)->setRowHeight(45);

// Colores de fondo según tipo de usuario
$tipo_colores = [
    1  => 'D5E8D4', // Admin - verde claro
    2  => 'DAE8FC', // CPSAM o Centro Vida - azul claro
    3  => 'DAE8FC', // Contratista CPSAM - azul claro
    4  => 'FFF2CC', // Técnico CPSAM - amarillo
    5  => 'FFF2CC', // Técnico Centro Vida - amarillo
    7  => 'F8CECC', // Sin acceso - rojo claro
    8  => 'E1D5E7', // Técnico Colombia Mayor - morado claro
    9  => 'E1D5E7', // Contratista Colombia Mayor - morado claro
    10 => 'DAE8FC', // Contratista Centro Vida - azul claro
    11 => 'D5E8D4', // Ingeniero Centro Vida - verde claro
    12 => 'DAE8FC', // Contratista Centro Vida Alcaldía - azul claro
];

$row_num = 2;
$num = 1;
while ($row = $result->fetch_assoc()) {
    $tipo = (int)$row['tipo_usuario'];
    $tipo_texto = getTipoUsuarioTexto($tipo);
    $personas_reg     = (int)$row['personas_registradas'];
    $masivas_cpsam    = (int)$row['masivas_cpsam'];
    $individuales_cpsam = (int)$row['individuales_cpsam'];
    $masivas_cv       = (int)$row['masivas_cv'];
    $individuales_cv  = (int)$row['individuales_cv'];
    $movimientos      = (int)$row['movimientos_personas'];
    $total = $personas_reg + $masivas_cpsam + $individuales_cpsam + $masivas_cv + $individuales_cv + $movimientos;

    $data = [
        $num,
        $row['usuario'],
        $row['nombre'],
        $row['centro_asociado'],
        $tipo_texto,
        $personas_reg,
        $masivas_cpsam,
        $individuales_cpsam,
        $masivas_cv,
        $individuales_cv,
        $movimientos,
        $total
    ];

    $col_idx = 1;
    foreach ($data as $value) {
        $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx);
        $sheet->setCellValue($col_letter . $row_num, $value);
        $col_idx++;
    }

    // Estilo por fila
    $bgColor = isset($tipo_colores[$tipo]) ? $tipo_colores[$tipo] : 'FFFFFF';
    $dataRange = 'A' . $row_num . ':' . $lastCol . $row_num;
    $sheet->getStyle($dataRange)->applyFromArray([
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $bgColor]
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['rgb' => 'CCCCCC']
            ]
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);

    // Centrar columnas numéricas
    for ($c = 6; $c <= count($headers); $c++) {
        $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $sheet->getStyle($cl . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    $sheet->getRowDimension($row_num)->setRowHeight(22);
    $row_num++;
    $num++;
}

// Anchos de columna
$col_widths = [5, 18, 35, 35, 30, 22, 20, 22, 22, 24, 20, 16];
foreach ($col_widths as $idx => $width) {
    $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
    $sheet->getColumnDimension($cl)->setWidth($width);
}

// Leyenda de tipos de usuario (hoja 2)
$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(1);
$sheetLeyenda = $spreadsheet->getActiveSheet();
$sheetLeyenda->setTitle('Leyenda Tipos');

$leyenda_headers = ['Tipo', 'Descripción', 'Act. Masivas CPSAM', 'Act. Individuales CPSAM', 'Act. Masivas CV', 'Act. Individuales CV', 'Mov. Personas'];
$col_idx = 1;
foreach ($leyenda_headers as $h) {
    $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx);
    $sheetLeyenda->setCellValue($cl . '1', $h);
    $col_idx++;
}
$lastColL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($leyenda_headers));
$sheetLeyenda->getStyle('A1:' . $lastColL . '1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

$leyenda_data = [
    [1,  'ADMINISTRADOR',                    'SI', 'SI', 'SI', 'SI', 'SI'],
    [2,  'CPSAM O CENTRO VIDA',              'SI', 'SI', 'NO', 'NO', 'SI'],
    [3,  'CONTRATISTA CPSAM',                'SI', 'SI', 'NO', 'NO', 'NO'],
    [4,  'TÉCNICO CPSAM',                    'SI', 'SI', 'NO', 'NO', 'SI'],
    [5,  'TÉCNICO CENTRO VIDA',              'NO', 'NO', 'SI', 'SI', 'SI'],
    [7,  'SIN ACCESO',                       'NO', 'NO', 'NO', 'NO', 'NO'],
    [8,  'TÉCNICO COLOMBIA MAYOR',           'NO', 'NO', 'NO', 'NO', 'NO'],
    [9,  'CONTRATISTA COLOMBIA MAYOR',       'NO', 'NO', 'NO', 'NO', 'NO'],
    [10, 'CONTRATISTA CENTRO VIDA',          'NO', 'NO', 'SI', 'SI', 'SI'],
    [11, 'INGENIERO CENTRO VIDA',            'NO', 'NO', 'SI', 'SI', 'SI'],
    [12, 'CONTRATISTA CENTRO VIDA ALCALDIA', 'NO', 'NO', 'SI', 'SI', 'NO'],
];

$l_row = 2;
foreach ($leyenda_data as $ld) {
    $col_idx = 1;
    foreach ($ld as $val) {
        $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx);
        $sheetLeyenda->setCellValue($cl . $l_row, $val);
        $col_idx++;
    }
    $sheetLeyenda->getStyle('A' . $l_row . ':' . $lastColL . $l_row)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);
    $l_row++;
}
foreach ([1 => 8, 2 => 35, 3 => 22, 4 => 24, 5 => 20, 6 => 22, 7 => 18] as $cidx => $w) {
    $sheetLeyenda->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cidx))->setWidth($w);
}

// Volver a hoja principal
$spreadsheet->setActiveSheetIndex(0);

$fileName = 'Informe_Usuarios_' . date('Y-m-d_H-i-s') . '.xlsx';
if (ob_get_length()) {
    ob_end_clean();
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
