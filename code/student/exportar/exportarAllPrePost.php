<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include("../../../conexion.php");
date_default_timezone_set("America/Bogota");
$mysqli->set_charset('utf8');
function getColumnLetter($index)
{
    $letter = '';
    while ($index >= 0) {
        $letter = chr($index % 26 + 65) . $letter;
        $index = floor($index / 26) - 1;
    }
    return $letter;
}
function Si1No2($value)
{
    if ($value == 1) {
        return "SI";
    } else {
        return "NO";
    }
}
// Crear una nueva instancia de Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sql = "SELECT  prepostnatales.*, estudiantes.nom_ape_est 
        FROM prepostnatales 
        JOIN estudiantes ON prepostnatales.num_doc_est = estudiantes.num_doc_est
        ORDER BY prepostnatales.num_doc_est ASC ";
// Ejecutar la consulta
$res = mysqli_query($mysqli, $sql);
// Verificar si la consulta se ejecutó correctamente
if ($res === false) {
    // Mostrar un mensaje de error si la consulta falla
    echo "Error en la consulta: " . mysqli_error($mysqli);
    exit;
}
// Aplicar color de fondo a las celdas A1 a AL1
$sheet->getStyle('A1:L1')->applyFromArray([
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'ffd880', // Cambia 'CCE5FF' al color deseado en formato RGB
        ],
    ],
]);

// Aplicar formato en negrita a las celdas con títulos
$boldFontStyle = [
    'bold' => true,
];
$sheet->getStyle('A1:L1')->applyFromArray(['font' => $boldFontStyle]);
// Establecer estilos para los encabezados
$styleHeader = [
    'font' => [
        'bold' => true,
        'size' => 20,
        'color' => ['rgb' => '333333'], // Color de texto (negro)
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F2F2F2'], // Color de fondo (gris claro)
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
];

// Aplicar el estilo a las celdas de encabezado
$sheet->getStyle('A1:L1')->applyFromArray(['font' => $styleHeader, 'fill' => $styleHeader, 'alignment' => $styleHeader]);

// // Definir los encabezados de columna

$sheet->setCellValue('A1', 'NOMBRE ESTUDIANTE');
$sheet->setCellValue('B1', 'DOCUMENTO ESTUDIANTE');
$sheet->setCellValue('C1', 'MUNICIPIO DILIGENCIA');
$sheet->setCellValue('D1', 'NOMBRE ENCUESTADOR');

$sheet->setCellValue('E1', 'EDAD MADRE');
$sheet->setCellValue('F1', 'GESTACION EN MESES');
$sheet->setCellValue('G1', 'EL EMBARAZO PRESENTO');
$sheet->setCellValue('H1', 'LACTANCIA EN MESES');
$sheet->setCellValue('I1', 'EL ESTUDIANTE GATEO');
$sheet->setCellValue('J1', 'EL ESTUDIANTE CAMINO');
$sheet->setCellValue('K1', 'FECHA CREACION');
$sheet->setCellValue('L1', 'FECHA EDICION');


// Ajustar el ancho de las columna
$sheet->getColumnDimension('A')->setWidth(35);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(25);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(25);
$sheet->getColumnDimension('G')->setWidth(25);
$sheet->getColumnDimension('H')->setWidth(25);
$sheet->getColumnDimension('I')->setWidth(25);
$sheet->getColumnDimension('J')->setWidth(25);
$sheet->getColumnDimension('K')->setWidth(25);
$sheet->getColumnDimension('L')->setWidth(25);

$sheet->getDefaultRowDimension()->setRowHeight(25);

$nombreEst = '';
$rowIndex = 2;
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {

    $nombreEst = $row['nom_ape_est'];
    $sheet->setCellValue('A' . $rowIndex, $row['nom_ape_est']);
    $sheet->setCellValue('B' . $rowIndex, $row['num_doc_est']);
    $sheet->setCellValue('C' . $rowIndex, $row['mun_dig_prePostnatales']);
    $sheet->setCellValue('D' . $rowIndex, $row['nombre_encuestador_prePostnatales']);
    $sheet->setCellValue('E' . $rowIndex, $row['edad_madre_prePostnatales']);
    $sheet->setCellValue('F' . $rowIndex, $row['gestacion_meses_prePostnatales']);
    $sheet->setCellValue('G' . $rowIndex, $row['embarazo_mama_prePostnatales']);
    $sheet->setCellValue('H' . $rowIndex, $row['lactancia_mama_prePostnatales']);
    $sheet->setCellValue('I' . $rowIndex, $row['gateo_prePostnatales']);
    $sheet->setCellValue('J' . $rowIndex, $row['camino_prePostnatales']);
    $sheet->setCellValue('K' . $rowIndex, $row['fecha_alta_prePostnatales']);
    $sheet->setCellValue('L' . $rowIndex, $row['fecha_edit_prePostnatales']);


    $sheet->getStyle('A' . $rowIndex . ':J' . $rowIndex . '')->applyFromArray(['font' => $boldFontStyle]);
    $rowIndex++;
}


$filename = 'PrePostNatales.xlsx';
$writer = new Xlsx($spreadsheet);

// Set the headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the generated Excel file to the browser
$writer->save('php://output');
exit;
