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
$usuario_id = $_SESSION['id'];

// Filtro por usuario
$where = "1=1";
if($tipo_usuario == 9) {
    $where .= " AND p.usuario_registro = '$usuario_id'"; 
}

// Consulta con todos los campos del formulario
$sql = "SELECT 
        p.tipo_identificacion_cm,
        p.cedula_persona_cm, 
        p.genero_persona_cm,
        p.nombres_persona_cm, 
        p.apellidos_persona_cm, 
        p.telefono_persona_cm,
        p.telefono_referencia_cm,
        p.referencia_cm,
        p.fecha_nacimiento_cm, 
        TIMESTAMPDIFF(YEAR, p.fecha_nacimiento_cm, CURDATE()) as edad_calculada,
        p.edad_cm,
        p.grupo_sisben,
        p.direccion_cm,
        p.barrio_cm,
        p.comuna_cm, 
        p.zona_cm,
        p.departamento_cm,
        p.municipio_cm,
        p.convivencia_actual,
        p.persona_discapacidad,
        p.cual_discapacidad,
        p.cabeza_hogar,
        p.se_reconoce_como,
        p.orientacion_sexual,
        p.grupo_etnico,
        p.tipo_salud,
        p.condicion_ocupacion,
        p.condicion_componente,
        p.fecha_ingreso_cm,
        p.estado_cm,
        p.fecha_registro,
        u.nombre as registrado_por
        FROM personas_colombia_mayor p
        LEFT JOIN usuarios u ON p.usuario_registro = u.id
        WHERE $where
        ORDER BY p.apellidos_persona_cm, p.nombres_persona_cm";

$result = $mysqli->query($sql);

if (!$result) {
    die('Error en consulta: ' . $mysqli->error);
}

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Personas CM');

// Encabezados
$headers = [
    'Tipo Identificación',
    'Cédula',
    'Género',
    'Nombres',
    'Apellidos',
    'Teléfono',
    'Teléfono Referencia',
    'Referencia',
    'Fecha Nacimiento',
    'Edad',
    'Grupo Sisbén',
    'Dirección',
    'Barrio',
    'Comuna',
    'Zona',
    'Departamento',
    'Municipio',
    'Convivencia Actual',
    'Persona con Discapacidad',
    'Categoría Discapacidad',
    'Cabeza de Hogar',
    'Se Reconoce Como',
    'Orientación Sexual',
    'Grupo Étnico',
    'Tipo de Salud',
    'Condición Ocupación',
    'Condición Componente',
    'Fecha Atención',
    'Estado',
    'Fecha Registro',
    'Registrado por'
];
$col = 'A';
foreach($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Estilo encabezados - Bonito y amplio
$lastCol = 'AE'; // Columna 31
$sheet->getStyle('A1:'.$lastCol.'1')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:'.$lastCol.'1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('2E75B6');
$sheet->getStyle('A1:'.$lastCol.'1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:'.$lastCol.'1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze pane (congelar encabezado)
$sheet->freezePane('A2');

// Datos
$fila = 2;
while($row = $result->fetch_assoc()) {
    $col = 'A';
    
    // Tipo Identificación
    $sheet->setCellValue($col++.$fila, $row['tipo_identificacion_cm'] ?? '');
    // Cédula
    $sheet->setCellValue($col++.$fila, $row['cedula_persona_cm'] ?? '');
    // Género
    $sheet->setCellValue($col++.$fila, $row['genero_persona_cm'] ?? '');
    // Nombres
    $sheet->setCellValue($col++.$fila, $row['nombres_persona_cm'] ?? '');
    // Apellidos
    $sheet->setCellValue($col++.$fila, $row['apellidos_persona_cm'] ?? '');
    // Teléfono
    $sheet->setCellValue($col++.$fila, $row['telefono_persona_cm'] ?? '');
    // Teléfono Referencia
    $sheet->setCellValue($col++.$fila, $row['telefono_referencia_cm'] ?? '');
    // Referencia
    $sheet->setCellValue($col++.$fila, $row['referencia_cm'] ?? '');
    // Fecha Nacimiento
    $sheet->setCellValue($col++.$fila, !empty($row['fecha_nacimiento_cm']) ? date('d/m/Y', strtotime($row['fecha_nacimiento_cm'])) : '');
    // Edad
    $sheet->setCellValue($col++.$fila, $row['edad_calculada'] ?? $row['edad_cm'] ?? '');
    // Grupo Sisbén
    $sheet->setCellValue($col++.$fila, $row['grupo_sisben'] ?? '');
    // Dirección
    $sheet->setCellValue($col++.$fila, $row['direccion_cm'] ?? '');
    // Barrio
    $sheet->setCellValue($col++.$fila, $row['barrio_cm'] ?? '');
    // Comuna
    $sheet->setCellValue($col++.$fila, $row['comuna_cm'] ?? '');
    // Zona
    $sheet->setCellValue($col++.$fila, $row['zona_cm'] ?? '');
    // Departamento
    $sheet->setCellValue($col++.$fila, $row['departamento_cm'] ?? '');
    // Municipio
    $sheet->setCellValue($col++.$fila, $row['municipio_cm'] ?? '');
    // Convivencia Actual
    $sheet->setCellValue($col++.$fila, $row['convivencia_actual'] ?? '');
    // Persona con Discapacidad
    $sheet->setCellValue($col++.$fila, $row['persona_discapacidad'] ?? '');
    // Categoría Discapacidad
    $sheet->setCellValue($col++.$fila, $row['cual_discapacidad'] ?? '');
    // Cabeza de Hogar
    $sheet->setCellValue($col++.$fila, $row['cabeza_hogar'] ?? '');
    // Se Reconoce Como
    $sheet->setCellValue($col++.$fila, $row['se_reconoce_como'] ?? '');
    // Orientación Sexual
    $sheet->setCellValue($col++.$fila, $row['orientacion_sexual'] ?? '');
    // Grupo Étnico
    $sheet->setCellValue($col++.$fila, $row['grupo_etnico'] ?? '');
    // Tipo de Salud
    $sheet->setCellValue($col++.$fila, $row['tipo_salud'] ?? '');
    // Condición Ocupación
    $sheet->setCellValue($col++.$fila, $row['condicion_ocupacion'] ?? '');
    // Condición Componente
    $sheet->setCellValue($col++.$fila, $row['condicion_componente'] ?? '');
    // Fecha Atención
    $sheet->setCellValue($col++.$fila, !empty($row['fecha_ingreso_cm']) ? date('d/m/Y', strtotime($row['fecha_ingreso_cm'])) : '');
    // Estado
    $sheet->setCellValue($col++.$fila, $row['estado_cm'] ?? '');
    // Fecha Registro
    $sheet->setCellValue($col++.$fila, !empty($row['fecha_registro']) ? date('d/m/Y H:i', strtotime($row['fecha_registro'])) : '');
    // Registrado por
    $sheet->setCellValue($col++.$fila, $row['registrado_por'] ?? 'N/A');
    
    // Filas alternadas con color
    if ($fila % 2 == 0) {
        $sheet->getStyle('A'.$fila.':AE'.$fila)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7F0FF');
    }
    
    // Altura de fila más amplia
    $sheet->getRowDimension($fila)->setRowHeight(18);
    $fila++;
}

// Ajustar columnas más amplias
$columnWidths = [
    'A' => 20,  // Tipo Identificación
    'B' => 15,  // Cédula
    'C' => 12,  // Género
    'D' => 25,  // Nombres
    'E' => 25,  // Apellidos
    'F' => 15,  // Teléfono
    'G' => 18,  // Teléfono Referencia
    'H' => 20,  // Referencia
    'I' => 18,  // Fecha Nacimiento
    'J' => 8,   // Edad
    'K' => 12,  // Grupo Sisbén
    'L' => 35,  // Dirección
    'M' => 25,  // Barrio
    'N' => 10,  // Comuna
    'O' => 10,  // Zona
    'P' => 15,  // Departamento
    'Q' => 15,  // Municipio
    'R' => 18,  // Convivencia Actual
    'S' => 20,  // Persona con Discapacidad
    'T' => 20,  // Categoría Discapacidad
    'U' => 18,  // Cabeza de Hogar
    'V' => 18,  // Se Reconoce Como
    'W' => 18,  // Orientación Sexual
    'X' => 18,  // Grupo Étnico
    'Y' => 20,  // Tipo de Salud
    'Z' => 20,  // Condición Ocupación
    'AA' => 30, // Condición Componente
    'AB' => 18, // Fecha Atención
    'AC' => 30, // Estado
    'AD' => 18, // Fecha Registro
    'AE' => 20  // Registrado por
];
foreach($columnWidths as $col => $width) {
    $sheet->getColumnDimension($col)->setWidth($width);
}

// Bordes más prominentes
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ],
        'outline' => [
            'borderStyle' => Border::BORDER_MEDIUM,
            'color' => ['rgb' => '000000']
        ]
    ],
];
$sheet->getStyle('A1:AE'.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:AE'.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

$mysqli->close();

ob_end_clean();

// Descargar
$filename = 'PersonasCM_'.date('Y-m-d_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
