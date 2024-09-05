<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include("../../../conexion.php");
date_default_timezone_set("America/Bogota");
$mysqli->set_charset('utf8');
if (!isset($_GET['num_doc_est'])) {
    header('Location: ../../../index.php');
}
$num_doc_est = $_GET['num_doc_est'];
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

$sql = "SELECT * FROM educacion WHERE num_doc_est = $num_doc_est";
// Ejecutar la consulta
$res = mysqli_query($mysqli, $sql);
// Verificar si la consulta se ejecutó correctamente
if ($res === false) {
    // Mostrar un mensaje de error si la consulta falla
    echo "Error en la consulta: " . mysqli_error($mysqli);
    exit;
}
// Aplicar color de fondo a las celdas A1 a AL1
$sheet->getStyle('A1:Q1')->applyFromArray([
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
$sheet->getStyle('A2:Q2')->applyFromArray(['font' => $boldFontStyle]);

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
$sheet->getStyle('A1:Q1')->applyFromArray(['font' => $styleHeader, 'fill' => $styleHeader, 'alignment' => $styleHeader]);

// // Definir los encabezados de columna

$sheet->setCellValue('A1', 'NOMBRE ESTUDIANTE');
$sheet->setCellValue('B1', 'DOCUMENTO ESTUDIANTE');
$sheet->setCellValue('C1', 'MUNICIPIO DILIGENCIA');
$sheet->setCellValue('D1', 'NOMBRE ENCUESTADOR');

$sheet->setCellValue('E1', 'VINCULADO A OTRA INSTITUCION');
$sheet->setCellValue('F1', 'CUAL INSTITUCION');
$sheet->setCellValue('G1', 'MODALIDAD DE 10º u 11º EN LA ANTERIOR INSTITUCION');
$sheet->setCellValue('H1', 'ASISTE PROGRAMAS COMPLEMENTARIOS');
$sheet->setCellValue('I1', 'CUAL PROGRAMA COMPLEMENTARIO');
$sheet->setCellValue('J1', 'HA REPTIDO AÑOS');
$sheet->setCellValue('K1', 'CUALES AÑOS HA REPETIDO');
$sheet->setCellValue('L1', 'RECONOCIO ALGUN TALENTO');
$sheet->setCellValue('M1', 'CUAL TALENTO');
$sheet->setCellValue('N1', 'VINCULADO A CLUB O LIGA');
$sheet->setCellValue('O1', 'CUAL CLUB O LIGA');
$sheet->setCellValue('P1', 'FECHA DE CREACION');
$sheet->setCellValue('Q1', 'FECHA DE EDICION');

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
$sheet->getColumnDimension('J')->setWidth(25);
$sheet->getColumnDimension('K')->setWidth(25);
$sheet->getColumnDimension('L')->setWidth(25);
$sheet->getColumnDimension('M')->setWidth(18);
$sheet->getColumnDimension('N')->setWidth(25);
$sheet->getColumnDimension('O')->setWidth(25);
$sheet->getColumnDimension('P')->setWidth(25);
$sheet->getColumnDimension('Q')->setWidth(25);

$sheet->getDefaultRowDimension()->setRowHeight(25);
$nombreEst = '';
$rowIndex = 2;
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    $nombreEst = $row['nom_ape_est'];

    $nombreEst= $row['nom_ape_est'];
    $sheet->setCellValue('A'. $rowIndex, $row['nom_ape_est']);
    $sheet->setCellValue('B'. $rowIndex, $row['num_doc_est']);
    $sheet->setCellValue('C'. $rowIndex, $row['mun_dig_educacion']);
    $sheet->setCellValue('D'. $rowIndex, $row['nombre_encuestador_educacion']);
    $sheet->setCellValue('E'. $rowIndex, Si1No2($row['vinculacion_inst_educacion']));
    $sheet->setCellValue('F'. $rowIndex, $row['nom_inst_educacion']);
    $sheet->setCellValue('G'. $rowIndex, $row['modalidad_inst_educacion']);
    $sheet->setCellValue('H'. $rowIndex, Si1No2($row['complementario_educacion']));
    $sheet->setCellValue('I'. $rowIndex, $row['program_complement_educacion']);
    $sheet->setCellValue('J'. $rowIndex, Si1No2($row['repetir_year_educacion']));
    $sheet->setCellValue('K'. $rowIndex, $row['anios_repet_educacion']);
    $sheet->setCellValue('L'. $rowIndex, Si1No2($row['talento_educacion']));
    $sheet->setCellValue('M'. $rowIndex, $row['talento_descrip_educacion']);
    $sheet->setCellValue('N'. $rowIndex, Si1No2($row['vinculacion_club_educacion']));
    $sheet->setCellValue('O'. $rowIndex, $row['club_descrip_educacion']);
    $sheet->setCellValue('P'. $rowIndex, $row['fechacreacion_educacion']);
    $sheet->setCellValue('Q'. $rowIndex, $row['fechaedicion_educacion']);
    

     $sheet->getStyle('A' .$rowIndex. ':AC'.$rowIndex.'')->applyFromArray(['font' => $boldFontStyle]);
     $rowIndex++;
}


$filename = 'Educacion'. $nombreEst.'.xlsx';
$writer = new Xlsx($spreadsheet);

// Set the headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the generated Excel file to the browser
$writer->save('php://output');
exit;
