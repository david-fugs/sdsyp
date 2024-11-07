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

$sql = "SELECT * FROM familiasalud WHERE num_doc_est = $num_doc_est";
// Ejecutar la consulta
$res = mysqli_query($mysqli, $sql);
// Verificar si la consulta se ejecutó correctamente
if ($res === false) {
    // Mostrar un mensaje de error si la consulta falla
    echo "Error en la consulta: " . mysqli_error($mysqli);
    exit;
}
// Aplicar color de fondo a las celdas A1 a AL1
$sheet->getStyle('A1:AC1')->applyFromArray([
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
$sheet->getStyle('A2:AC2')->applyFromArray(['font' => $boldFontStyle]);

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
$sheet->getStyle('A1:AC1')->applyFromArray(['font' => $styleHeader, 'fill' => $styleHeader, 'alignment' => $styleHeader]);

// // Definir los encabezados de columna
$sheet->setCellValue('A1', 'NOMBRE ESTUDIANTE');
$sheet->setCellValue('B1', 'DOCUMENTO ESTUDIANTE');
$sheet->setCellValue('C1', 'MUNICIPIO DILIGENCIA');
$sheet->setCellValue('D1', 'NOMBRE ENCUESTADOR');
$sheet->setCellValue('E1', 'RELACION MADRE');
$sheet->setCellValue('F1', 'RELACION PADRE');
$sheet->setCellValue('G1', 'RELACION HERMANOS');
$sheet->setCellValue('H1', 'RELACION ABUELOS');
$sheet->setCellValue('I1', 'RELACION TIOS');
$sheet->setCellValue('J1', 'RELACION OTROS FAMILIARES');
$sheet->setCellValue('K1', 'PRESENTA DISCAPACIDAD');
$sheet->setCellValue('L1', 'SITUACIONES QUE AFECTAN APRENDIZAJE DEL ESTUDIANTE');
$sheet->setCellValue('M1', 'BENEFICIARIO PAE');
$sheet->setCellValue('N1', 'MOMENTOS DE COMIDA AL DIA');
$sheet->setCellValue('O1', 'AFILIADO EPS');
$sheet->setCellValue('P1', 'NOMBRE EPS');
$sheet->setCellValue('Q1', 'CUAL EPS');
$sheet->setCellValue('R1', 'SISTEMA AFILIADO');
$sheet->setCellValue('S1', 'PRESENTA DIAGNOSTICO');
$sheet->setCellValue('T1', 'CUAL DIAGNOSTICO');
$sheet->setCellValue('U1', 'ASISTE A TERAPIAS');
$sheet->setCellValue('V1', 'FRECUENCIA DE TERAPIAS');
$sheet->setCellValue('W1', 'ESTA SIENDO ATENDIDO POR ALGUNA CONDICION?');
$sheet->setCellValue('X1', 'FRECUENCIA QUE ES ATENDIDO');
$sheet->setCellValue('Y1', 'PRESENTA ALERGIA');
$sheet->setCellValue('Z1', 'PRESENTA ALERGIA A QUE');
$sheet->setCellValue('AA1', 'ESQUEMA DE VACUNACION COMPLETO');
$sheet->setCellValue('AB1', 'TIPO DE SANGRE');
$sheet->setCellValue('AC1', 'FECHA DE APLICACION');
$sheet->setCellValue('AD1', 'FECHA DE MODIFICACION');

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
$sheet->getColumnDimension('AA')->setWidth(25);
$sheet->getColumnDimension('AB')->setWidth(25);
$sheet->getColumnDimension('AC')->setWidth(25);
$sheet->getColumnDimension('AD')->setWidth(25);
$sheet->getDefaultRowDimension()->setRowHeight(25);
$nombreEst = '';
$rowIndex = 2;
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    $nombreEst = $row['nom_ape_est'];

    $sheet->setCellValue('A' . $rowIndex , $row['nom_ape_est']);
    $sheet->setCellValue('B' . $rowIndex , $row['num_doc_est']);
    $sheet->setCellValue('C' . $rowIndex , $row['mun_dig_familiaSalud']);
    $sheet->setCellValue('D' . $rowIndex , $row['nombre_encuestador_familiaSalud']);
    $sheet->setCellValue('E' . $rowIndex , $row['relacion_madre_familiaSalud']);
    $sheet->setCellValue('F' . $rowIndex , $row['relacion_padre_familiaSalud']);
    $sheet->setCellValue('G' . $rowIndex , $row['relacion_hermanos_familiaSalud']);
    $sheet->setCellValue('H' . $rowIndex , $row['relacion_abuelos_familiaSalud']);
    $sheet->setCellValue('I' . $rowIndex , $row['relacion_tios_familiaSalud']);
    $sheet->setCellValue('J' . $rowIndex , $row['relacion_otros_familiaSalud']);
    $sheet->setCellValue('K' . $rowIndex , Si1No2($row['discapacidad_est_familiaSalud']));
    $sheet->setCellValue('L' . $rowIndex , $row['afecta_aprendizaje_familiaSalud']);
    $sheet->setCellValue('M' . $rowIndex , Si1No2($row['beneficiario_pae_familiaSalud']));
    $sheet->setCellValue('N' . $rowIndex , $row['comida_dia_familiaSalud']);
    $sheet->setCellValue('O' . $rowIndex , Si1No2($row['eps_estudiante_familiaSalud']));
    $sheet->setCellValue('P' . $rowIndex , $row['nombre_eps_familiaSalud']);
    $sheet->setCellValue('Q' . $rowIndex , $row['cual_eps_familiaSalud']);
    $sheet->setCellValue('R' . $rowIndex , $row['afiliado_eps_familiaSalud']);
    $sheet->setCellValue('S' . $rowIndex , Si1No2($row['presenta_diagnostico_familiaSalud']));
    $sheet->setCellValue('T' . $rowIndex , $row['diagnostico_familiaSalud']);
    $sheet->setCellValue('U' . $rowIndex , Si1No2($row['terapia_familiaSalud']));
    $sheet->setCellValue('V' . $rowIndex , $row['frecuencia_terapia_familiaSalud']);
    $sheet->setCellValue('W' . $rowIndex , Si1No2($row['condicion_particular_familiaSalud']));
    $sheet->setCellValue('X' . $rowIndex , $row['frecuencia_atencion_familiaSalud']);
    $sheet->setCellValue('Y' . $rowIndex , Si1No2($row['alergia_familiaSalud']));
    $sheet->setCellValue('Z' . $rowIndex , $row['tipo_alergia_familiaSalud']);
    $sheet->setCellValue('AA' . $rowIndex , Si1No2($row['vacunacion_familiaSalud']));
    $sheet->setCellValue('AB' . $rowIndex , $row['sangre_familiaSalud']);
    $sheet->setCellValue('AC' . $rowIndex , $row['fechacreacion_familiaSalud']);
    $sheet->setCellValue('AD' . $rowIndex , $row['fechaedicion_familiaSalud']);

    $sheet->getStyle('A' .$rowIndex. ':AD'.$rowIndex.'')->applyFromArray(['font' => $boldFontStyle]);
    $rowIndex++;
}

// Set the filename and file format
$filename = 'FamiliaSalud ' . $nombreEst . '.xlsx';
$writer = new Xlsx($spreadsheet);
// Set the headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the generated Excel file to the browser
$writer->save('php://output');
exit;
