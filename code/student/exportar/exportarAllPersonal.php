<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
session_start();
include("../../../conexion.php");
date_default_timezone_set("America/Bogota");
$mysqli->set_charset('utf8');
$cod_dane_ie = $_SESSION['cod_dane_ie'];
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
// Aplicar color de fondo a las celdas A1 a AL1
$sheet->getStyle('A1:I1')->applyFromArray([
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
$sheet->getStyle('A2:I2')->applyFromArray(['font' => $boldFontStyle]);

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
$sheet->getStyle('A1:I1')->applyFromArray(['font' => $styleHeader, 'fill' => $styleHeader, 'alignment' => $styleHeader]);

$sheet->setCellValue('A1', 'NOMBRE ESTUDIANTE');
$sheet->setCellValue('B1', 'DOCUMENTO ESTUDIANTE');
$sheet->setCellValue('C1', 'MUNICIPIO DILIGENCIA');
$sheet->setCellValue('D1', 'NOMBRE ENCUESTADOR');

$sheet->setCellValue('E1', 'SABE NORMAS ESCOLARES');
$sheet->setCellValue('F1', 'ACATA NORMAS Y REGLAS');
$sheet->setCellValue('G1', 'INTERACTUA CON ADULTOS CORRECTAMENTE');
$sheet->setCellValue('H1', 'SE OCUPA DE SU CUIDADO PERSONAL');
$sheet->setCellValue('I1', 'OBSERVACIONES');



// Ajustar el ancho de las columna
$sheet->getColumnDimension('A')->setWidth(35);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(25);
$sheet->getColumnDimension('E')->setWidth(25);
$sheet->getColumnDimension('F')->setWidth(25);
$sheet->getColumnDimension('G')->setWidth(25);
$sheet->getColumnDimension('H')->setWidth(25);
$sheet->getColumnDimension('I')->setWidth(25);
$sheet->getDefaultRowDimension()->setRowHeight(25);




$sql = "SELECT * FROM personal
        INNER JOIN estudiantes ON personal.num_doc_est = estudiantes.num_doc_est 
        INNER JOIN ieSede ON estudiantes.cod_dane_ieSede = ieSede.cod_dane_ieSede 
        INNER JOIN ie ON ieSede.cod_dane_ie = ie.cod_dane_ie 
        WHERE ie.cod_dane_ie = $cod_dane_ie 
         ORDER BY estudiantes.num_doc_est ASC 

 ";
// Ejecutar la consulta
$res = mysqli_query($mysqli, $sql);

// Verificar si la consulta se ejecutó correctamente
if ($res === false) {
    // Mostrar un mensaje de error si la consulta falla
    echo "Error en la consulta: " . mysqli_error($mysqli);
    exit;
}
$rowIndex = 2;
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {

    $nombreEst = $row['nom_ape_est'];
     $sheet->setCellValue('A'. $rowIndex  , $row['nom_ape_est']);
     $sheet->setCellValue('B'. $rowIndex , $row['num_doc_est']);
     $sheet->setCellValue('C'. $rowIndex, $row['mun_dig_personal']);
     $sheet->setCellValue('D' . $rowIndex, $row['nombre_encuestador_personal']);


    $sheet->setCellValue('E'.$rowIndex, $row['normas_personal']);
    $sheet->setCellValue('F'.$rowIndex, $row['acata_personal']);
    $sheet->setCellValue('G'.$rowIndex, $row['interactua_personal']);
    $sheet->setCellValue('H'.$rowIndex, $row['cuidado_personal']);
    $sheet->setCellValue('I'.$rowIndex, $row['observacion_personal']);
    $sheet->getStyle('A' .$rowIndex. ':I'.$rowIndex.'')->applyFromArray(['font' => $boldFontStyle]);
    $rowIndex++;
}


$filename = 'Personal'. $nombreEst.'.xlsx';
$writer = new Xlsx($spreadsheet);

// Set the headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the generated Excel file to the browser
$writer->save('php://output');
exit;
