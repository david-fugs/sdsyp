<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
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
        p.correo_cm,
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
    'Correo',
    'Fecha Nacimiento',
    'Edad',
    'Grupo Sisbén',
    'Dirección',
    'Barrio',
    'Comuna',
    'Zona',
    'Departamento',
    'Municipio',
    'EPS',
    'Peso (kg)',
    'Talla (cm)',
    'Patologías',
    'Factores de Riesgo',
    'Factores Preventivos',
    'Ingresos Económicos',
    'Convivencia Actual',
    'Resultado Actividad',
    'Remisión',
    'Persona con Discapacidad',
    'Categoría Discapacidad',
    'Cabeza de Hogar',
    'Líder Comunidad',
    'Se Reconoce Como',
    'Orientación Sexual',
    'Experiencia Migratoria',
    'Grupo Étnico',
    'Tipo de Salud',
    'Nivel Educativo',
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
$lastCol = 'AR'; // Columna 44
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
    // Correo
    $sheet->setCellValue($col++.$fila, $row['correo_cm'] ?? '');
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
    // EPS
    $sheet->setCellValue($col++.$fila, $row['eps'] ?? '');
    // Peso
    $sheet->setCellValue($col++.$fila, $row['peso'] ?? '');
    // Talla
    $sheet->setCellValue($col++.$fila, $row['talla'] ?? '');
    // Patologías
    $sheet->setCellValue($col++.$fila, $row['patologias'] ?? '');
    // Factores de Riesgo
    $sheet->setCellValue($col++.$fila, $row['factores_riesgo'] ?? '');
    // Factores Preventivos
    $sheet->setCellValue($col++.$fila, $row['factores_preventivos'] ?? '');
    // Ingresos Económicos
    $sheet->setCellValue($col++.$fila, $row['ingresos_economicos'] ?? '');
    // Convivencia Actual
    $sheet->setCellValue($col++.$fila, $row['convivencia_actual'] ?? '');
    // Resultado Actividad
    $sheet->setCellValue($col++.$fila, $row['resultado_actividad'] ?? '');
    // Remisión
    $sheet->setCellValue($col++.$fila, $row['remision'] ?? '');
    // Persona con Discapacidad
    $sheet->setCellValue($col++.$fila, $row['persona_discapacidad'] ?? '');
    // Categoría Discapacidad
    $sheet->setCellValue($col++.$fila, $row['cual_discapacidad'] ?? '');
    // Cabeza de Hogar
    $sheet->setCellValue($col++.$fila, $row['cabeza_hogar'] ?? '');
    // Líder Comunidad
    $sheet->setCellValue($col++.$fila, $row['lider_comunidad'] ?? '');
    // Se Reconoce Como
    $sheet->setCellValue($col++.$fila, $row['se_reconoce_como'] ?? '');
    // Orientación Sexual
    $sheet->setCellValue($col++.$fila, $row['orientacion_sexual'] ?? '');
    // Experiencia Migratoria
    $sheet->setCellValue($col++.$fila, $row['experiencia_migratoria'] ?? '');
    // Grupo Étnico
    $sheet->setCellValue($col++.$fila, $row['grupo_etnico'] ?? '');
    // Tipo de Salud
    $sheet->setCellValue($col++.$fila, $row['tipo_salud'] ?? '');
    // Nivel Educativo
    $sheet->setCellValue($col++.$fila, $row['nivel_educativo'] ?? '');
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
        $sheet->getStyle('A'.$fila.':AR'.$fila)->getFill()
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
    'I' => 30,  // Correo
    'J' => 18,  // Fecha Nacimiento
    'K' => 8,   // Edad
    'L' => 12,  // Grupo Sisbén
    'M' => 35,  // Dirección
    'N' => 25,  // Barrio
    'O' => 10,  // Comuna
    'P' => 10,  // Zona
    'Q' => 15,  // Departamento
    'R' => 15,  // Municipio
    'S' => 20,  // EPS
    'T' => 10,  // Peso
    'U' => 10,  // Talla
    'V' => 25,  // Patologías
    'W' => 20,  // Factores de Riesgo
    'X' => 20,  // Factores Preventivos
    'Y' => 20,  // Ingresos Económicos
    'Z' => 18,  // Convivencia Actual
    'AA' => 30, // Resultado Actividad
    'AB' => 20, // Remisión
    'AC' => 20, // Persona con Discapacidad
    'AD' => 20, // Categoría Discapacidad
    'AE' => 18, // Cabeza de Hogar
    'AF' => 18, // Líder Comunidad
    'AG' => 18, // Se Reconoce Como
    'AH' => 18, // Orientación Sexual
    'AI' => 20, // Experiencia Migratoria
    'AJ' => 18, // Grupo Étnico
    'AK' => 20, // Tipo de Salud
    'AL' => 20, // Nivel Educativo
    'AM' => 20, // Condición Ocupación
    'AN' => 30, // Condición Componente
    'AO' => 18, // Fecha Atención
    'AP' => 30, // Estado
    'AQ' => 18, // Fecha Registro
    'AR' => 20  // Registrado por
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
$sheet->getStyle('A1:AR'.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:AR'.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

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
