<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include("../../../conexion.php");
date_default_timezone_set("America/Bogota");
$mysqli->set_charset('utf8');
if (!isset($_GET['id_desempeno'])) {
    header('Location: ../../../index.php');
}
$id_desempeno = $_GET['id_desempeno'];

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


$sheet->setCellValue('G1', 'MATERIAS ASIGNADAS (COMPRENSION,PARTICIPACION,APLICACION,CONSISTENCIA)');

$sheet->setCellValue('A2', 'NOMBRE ESTUDIANTE');
$sheet->setCellValue('B2', 'DOCUMENTO ESTUDIANTE');
$sheet->setCellValue('C2', 'MUNICIPIO DILIGENCIA');
$sheet->setCellValue('D2', 'NOMBRE ENCUESTADOR');
$sheet->setCellValue('E2', 'DESEMPEÑO CIENCIA');
$sheet->setCellValue('F2', 'DESEMPEÑO SOCIALES');
$sheet->setCellValue('G2', 'DESEMPEÑO EDUCACION FISICA');
$sheet->setCellValue('H2', 'DESEMPEÑO ETICA');
$sheet->setCellValue('I2', 'DESEMPEÑO RELIGION');
$sheet->setCellValue('J2', 'DESEMPEÑO ARTISTICA');
$sheet->setCellValue('K2', 'DESEMPEÑO HUMANIDADES');
$sheet->setCellValue('L2', 'DESEMPEÑO MATEMATICAS');
$sheet->setCellValue('M2', 'DESEMPEÑO FISICA');
$sheet->setCellValue('N2', 'DESEMPEÑO ALGEBRA');
$sheet->setCellValue('O2', 'DESEMPEÑO CALCULO');
$sheet->setCellValue('P2', 'DESEMPEÑO INGLES');
$sheet->setCellValue('Q2', 'DESEMPEÑO TECNOLOGIA E INFORMATICA');
$sheet->setCellValue('R2', 'DESEMPEÑO EMPRENDIMIENTO');
$sheet->setCellValue('S2', 'DESEMPEÑO AREAS TECNICAS');
$sheet->setCellValue('T2', 'DESEMPEÑO FILOSOFIA');
$sheet->setCellValue('U2', 'DESEMPEÑO CIENCIAS ECONOMICAS');
$sheet->setCellValue('V2', 'VINCULADO PROGRAMA DOBLE TITULACION');
$sheet->setCellValue('W2', 'CUAL PROGRAMA');
 $sheet->setCellValue('X2', 'FECHA CREACION');
 $sheet->setCellValue('Y2', 'FECHA EDICION');

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
$sheet->getDefaultRowDimension()->setRowHeight(25);

// // Fusiona las celdas desde H1 hasta AI1
$sheet->mergeCells('G1:X1');

// Aplica el formato deseado al título
$sheet->getStyle('F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('F1')->getFont()->setBold(true)->setSize(14);
// Aplicar color de fondo a las celdas A1 a AL1
$sheet->getStyle('A1:Y1')->applyFromArray([
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'ffd880', // Cambia 'CCE5FF' al color deseado en formato RGB
        ],
    ],
]);
$sheet->getStyle('A2:Y2')->applyFromArray([
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'ffd880', // Cambia 'CCE5FF' al color deseado en formato RGB
        ],
    ],
]);

$sql = "SELECT * FROM desempeno WHERE id_desempeno = $id_desempeno";
// Ejecutar la consulta
$res = mysqli_query($mysqli, $sql);
// Verificar si la consulta se ejecutó correctamente
if ($res === false) {
    // Mostrar un mensaje de error si la consulta falla
    echo "Error en la consulta: " . mysqli_error($mysqli);
    exit;
}
$nombreEst ='';
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    $nombreEst = $row['nom_ape_est'];

    $sheet->setCellValue('A3', $row['nom_ape_est']);
    $sheet->setCellValue('B3', $row['num_doc_est']);
    $sheet->setCellValue('C3', $row['mun_dig_desempeno']);
    $sheet->setCellValue('D3', $row['nombre_encuestador_desempeno']);
    $sheet->setCellValue('E3', "(" . $row['comprension_ciencia_desempeno'] . "," . $row['participacion_ciencia_desempeno'] . "," . $row['aplicacion_ciencia_desempeno'] . "," . $row['consistencia_ciencia_desempeno'] . ")");
    $sheet->setCellValue('F3', "(" . $row['comprension_sociales_desempeno'] . "," . $row['participacion_sociales_desempeno'] . "," . $row['aplicacion_sociales_desempeno'] . "," . $row['consistencia_sociales_desempeno'] . ")");
    $sheet->setCellValue('G3', "(" . $row['comprension_edufisica_desempeno'] . "," . $row['participacion_edufisica_desempeno'] . "," . $row['aplicacion_edufisica_desempeno'] . "," . $row['consistencia_edufisica_desempeno'] . ")");
    $sheet->setCellValue('H3', "(" . $row['comprension_etica_desempeno'] . "," . $row['participacion_etica_desempeno'] . "," . $row['aplicacion_etica_desempeno'] . "," . $row['consistencia_etica_desempeno'] . ")");
    $sheet->setCellValue('I3', "(" . $row['comprension_religion_desempeno'] . "," . $row['participacion_religion_desempeno'] . "," . $row['aplicacion_religion_desempeno'] . "," . $row['consistencia_religion_desempeno'] . ")");
    $sheet->setCellValue('J3', "(" . $row['comprension_artistica_desempeno'] . "," . $row['participacion_artistica_desempeno'] . "," . $row['aplicacion_artistica_desempeno'] . "," . $row['consistencia_artistica_desempeno'] . ")");
    $sheet->setCellValue('K3', "(" . $row['comprension_humanidades_desempeno'] . "," . $row['participacion_humanidades_desempeno'] . "," . $row['aplicacion_humanidades_desempeno'] . "," . $row['consistencia_humanidades_desempeno'] . ")");
     $sheet->setCellValue('L3', "(" . $row['comprension_matematicas_desempeno'] . "," . $row['participacion_matematicas_desempeno'] . "," . $row['aplicacion_matematicas_desempeno'] . "," . $row['consistencia_matematicas_desempeno'] . ")");
     $sheet->setCellValue('M3', "(" . $row['comprension_fisica_desempeno'] . "," . $row['participacion_fisica_desempeno'] . "," . $row['aplicacion_fisica_desempeno'] . "," . $row['consistencia_fisica_desempeno'] . ")");
    $sheet->setCellValue('N3', "(" . $row['comprension_algebra_desempeno'] . "," . $row['participacion_algebra_desempeno'] . "," . $row['aplicacion_algebra_desempeno'] . "," . $row['consistencia_algebra_desempeno'] . ")");
     $sheet->setCellValue('O3', "(" . $row['comprension_calculo_desempeno'] . "," . $row['participacion_calculo_desempeno'] . "," . $row['aplicacion_calculo_desempeno'] . "," . $row['consistencia_calculo_desempeno'] . ")");
     $sheet->setCellValue('P3', "(" . $row['comprension_ingles_desempeno'] . "," . $row['participacion_ingles_desempeno'] . "," . $row['aplicacion_ingles_desempeno'] . "," . $row['consistencia_ingles_desempeno'] . ")");
     $sheet->setCellValue('Q3', "(" . $row['comprension_tecno_desempeno'] . "," . $row['participacion_tecno_desempeno'] . "," . $row['aplicacion_tecno_desempeno'] . "," . $row['consistencia_tecno_desempeno'] . ")");
     $sheet->setCellValue('R3', "(" . $row['comprension_emprendimiento_desempeno'] . "," . $row['participacion_emprendimiento_desempeno'] . "," . $row['aplicacion_emprendimiento_desempeno'] . "," . $row['consistencia_emprendimiento_desempeno'] . ")");
     $sheet->setCellValue('S3', "(" . $row['comprension_areastec_desempeno'] . "," . $row['participacion_areastec_desempeno'] . "," . $row['aplicacion_areastec_desempeno'] . "," . $row['consistencia_areastec_desempeno'] . ")");
     $sheet->setCellValue('T3', "(" . $row['comprension_filosofia_desempeno'] . "," . $row['participacion_filosofia_desempeno'] . "," . $row['aplicacion_filosofia_desempeno'] . "," . $row['consistencia_filosofia_desempeno'] . ")");
     $sheet->setCellValue('U3', "(" . $row['comprension_cienciaseco_desempeno'] . "," . $row['participacion_cienciaseco_desempeno'] . "," . $row['aplicacion_cienciaseco_desempeno'] . "," . $row['consistencia_cienciaseco_desempeno'] . ")");
     $sheet->setCellValue('V3', Si1No2($row['doble_titu_desempeno']));
     $sheet->setCellValue('W3', $row['nom_dobletitu_desempeno']);
     $sheet->setCellValue('X3', $row['fechacreacion_desempeno']);
     $sheet->setCellValue('Y3', $row['fechaedicion_desempeno']);



}
// // Set the filename and file format
$filename = 'desempeno '.$nombreEst.'.xlsx';
$writer = new Xlsx($spreadsheet);

// Set the headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the generated Excel file to the browser
$writer->save('php://output');
exit;
