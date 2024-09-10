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
// Aplicar color de fondo a las celdas A1 a AL1
$sheet->getStyle('A1:Z1')->applyFromArray([
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
$sheet->getStyle('A2:Z2')->applyFromArray(['font' => $boldFontStyle]);

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
$sheet->getStyle('A1:Z1')->applyFromArray(['font' => $styleHeader, 'fill' => $styleHeader, 'alignment' => $styleHeader]);

$sheet->setCellValue('A1', 'NOMBRE ESTUDIANTE');
$sheet->setCellValue('B1', 'DOCUMENTO ESTUDIANTE');
$sheet->setCellValue('C1', 'MUNICIPIO DILIGENCIA');
$sheet->setCellValue('D1', 'NOMBRE ENCUESTADOR');

$sheet->setCellValue('E1', 'EXPLICA ELECCION');
$sheet->setCellValue('F1', 'ANTICIPA CONSECUENCIA');
$sheet->setCellValue('G1', 'INDEPENDENCIA');
$sheet->setCellValue('H1', 'REPRESENTACION ARTISTICA');
$sheet->setCellValue('I1', 'IDENTIFICA LUGARES');
$sheet->setCellValue('J1', 'DESCRIBE ROLES FAMILIA');
$sheet->setCellValue('K1', 'ESCUCHA PUNTOS DE VISTA');

$sheet->setCellValue('L1', 'PARTICIPA EN CANCIONES');
$sheet->setCellValue('M1', 'IDENTIFICA PALABRAS');
$sheet->setCellValue('N1', 'SIGUE JUEGO DE PALABRAS');
$sheet->setCellValue('O1', 'LEE IMAGENES');
$sheet->setCellValue('P1', 'EXPLORA TIPOS DE TEXTO');
$sheet->setCellValue('Q1', 'INTERES EN ESCRITURA');

$sheet->setCellValue('R1', 'CONCENTRACION');
$sheet->setCellValue('S1', 'EXPLICA RAZONES');
$sheet->setCellValue('T1', 'PARTICIPA EN PRACTICAS');
$sheet->setCellValue('U1', 'IDENTIFICA CARACTERISTICAS');
$sheet->setCellValue('V1', 'REPRESENTA CUERPO');
$sheet->setCellValue('W1', 'ACONTECIMIENTOS');
$sheet->setCellValue('X1', 'SECUENCIAS DE ACCIONES');
$sheet->setCellValue('Y1', 'ATRIBUTOS');
$sheet->setCellValue('Z1', 'ENUMERA OBJETOS');



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
$sheet->getColumnDimension('R')->setWidth(25);
$sheet->getColumnDimension('S')->setWidth(25);
$sheet->getColumnDimension('T')->setWidth(25);
$sheet->getColumnDimension('U')->setWidth(25);
$sheet->getColumnDimension('V')->setWidth(25);
$sheet->getColumnDimension('W')->setWidth(25);
$sheet->getColumnDimension('X')->setWidth(25);
$sheet->getColumnDimension('Y')->setWidth(25);
$sheet->getColumnDimension('Z')->setWidth(25);
$sheet->getDefaultRowDimension()->setRowHeight(25);




$sql = "SELECT * FROM preescolar WHERE num_doc_est = $num_doc_est";
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
    $sheet->setCellValue('A'.$rowIndex , $row['nom_ape_est']);
    $sheet->setCellValue('B'.$rowIndex, $row['num_doc_est']);
    $sheet->setCellValue('C'.$rowIndex, $row['mun_dig_preescolar']);
    $sheet->setCellValue('D'.$rowIndex, $row['nombre_encuestador_preescolar']);


    $sheet->setCellValue('E'.$rowIndex, $row['p1_eleccion_preescolar']);
    $sheet->setCellValue('F'.$rowIndex, $row['p1_consecuen_preescolar']);
    $sheet->setCellValue('G'.$rowIndex, $row['p1_independencia_preescolar']);
    $sheet->setCellValue('H'.$rowIndex, $row['p1_artistico_preescolar']);
    $sheet->setCellValue('I'.$rowIndex, $row['p1_lugarvive_preescolar']);
    $sheet->setCellValue('J'.$rowIndex, $row['p1_rolfamilia_preescolar']);
    $sheet->setCellValue('K'.$rowIndex, $row['p1_escucha_preescolar']);

    $sheet->setCellValue('L'.$rowIndex, $row['p2_juegos_preescolar']);
    $sheet->setCellValue('M'.$rowIndex, $row['p2_palabras_preescolar']);
    $sheet->setCellValue('N'.$rowIndex, $row['p2_segmenoral_preescolar']);
    $sheet->setCellValue('O'.$rowIndex, $row['p2_crea_preescolar']);
    $sheet->setCellValue('P'.$rowIndex, $row['p2_leer_preescolar']);
    $sheet->setCellValue('Q'.$rowIndex, $row['p2_escribir_preescolar']);

    $sheet->setCellValue('R'.$rowIndex, $row['p3_concentracion_preescolar']);
    $sheet->setCellValue('S'.$rowIndex, $row['p3_explica_preescolar']);
    $sheet->setCellValue('T'.$rowIndex, $row['p3_participa_preescolar']);
    $sheet->setCellValue('U'.$rowIndex, $row['p3_identifica_preescolar']);
    $sheet->setCellValue('V'.$rowIndex, $row['p3_acontecimientos_preescolar']);
    $sheet->setCellValue('W'.$rowIndex, $row['p3_cuerpo_preescolar']);
    $sheet->setCellValue('X'.$rowIndex, $row['p3_secuencia_preescolar']);
    $sheet->setCellValue('Y'.$rowIndex, $row['p3_atributo_preescolar']);
    $sheet->setCellValue('Z'.$rowIndex, $row['p3_enumeracion_preescolar']);
    $sheet->getStyle('A' .$rowIndex. ':Z'.$rowIndex.'')->applyFromArray(['font' => $boldFontStyle]);


    $rowIndex++;

}


$filename = 'Preescolar'. $nombreEst.'.xlsx';
$writer = new Xlsx($spreadsheet);

// Set the headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the generated Excel file to the browser
$writer->save('php://output');
exit;
