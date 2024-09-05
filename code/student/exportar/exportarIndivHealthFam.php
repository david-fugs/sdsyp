<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include("../../../conexion.php");
date_default_timezone_set("America/Bogota");
$mysqli->set_charset('utf8');
if (!isset($_GET['id_salud_familiaSalud'])) {
    header('Location: ../../../index.php');
}
$id_familiaSalud = $_GET['id_salud_familiaSalud'];
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


// Consultar la tabla familiasalud con el id
$sql = "SELECT * FROM familiasalud WHERE id_salud_familiaSalud = $id_familiaSalud";
// Ejecutar la consulta
$res = mysqli_query($mysqli, $sql);

// Verificar si la consulta se ejecutó correctamente
if ($res === false) {
    // Mostrar un mensaje de error si la consulta falla
    echo "Error en la consulta: " . mysqli_error($mysqli);
    exit;
}
$nombreEstudiante = "";
// Procesar los resultados
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    $nombreEstudiante =  $row['nom_ape_est'];
    $sheet->setTitle($row['num_doc_est'] . "Familia");

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
    $sheet->setCellValue('Q1', 'SISTEMA AFILIADO');
    $sheet->setCellValue('R1', 'PRESENTA DIAGNOSTICO');
    $sheet->setCellValue('S1', 'CUAL DIAGNOSTICO');
    $sheet->setCellValue('T1', 'ASISTE A TERAPIAS');
    $sheet->setCellValue('U1', 'FRECUENCIA DE TERAPIAS');
    $sheet->setCellValue('V1', 'ESTA SIENDO ATENDIDO POR ALGUNA CONDICION?');
    $sheet->setCellValue('W1', 'FRECUENCIA QUE ES ATENDIDO');
    $sheet->setCellValue('X1', 'PRESENTA ALERGIA');
    $sheet->setCellValue('Y1', 'PRESENTA ALERGIA A QUE');
    $sheet->setCellValue('Z1', 'ESQUEMA DE VACUNACION COMPLETO');
    $sheet->setCellValue('AA1', 'TIPO DE SANGRE');
    $sheet->setCellValue('AB1', 'FECHA DE APLICACION');
    $sheet->setCellValue('AC1', 'FECHA DE MODIFICACION');

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
$sheet->getDefaultRowDimension()->setRowHeight(25);


$columnIndex = 0;
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['nom_ape_est']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['num_doc_est']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['mun_dig_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['nombre_encuestador_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['relacion_madre_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['relacion_padre_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['relacion_hermanos_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['relacion_abuelos_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['relacion_tios_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['relacion_otros_familiaSalud']);
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['discapacidad_est_familiaSalud']));
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['afecta_aprendizaje_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['beneficiario_pae_familiaSalud']));
$sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['comida_dia_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['eps_estudiante_familiaSalud']));
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['nombre_eps_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['afiliado_eps_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['presenta_diagnostico_familiaSalud']));
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['diagnostico_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['terapia_familiaSalud']));
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['frecuencia_terapia_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['condicion_particular_familiaSalud']));
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['frecuencia_atencion_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['alergia_familiaSalud']));
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['tipo_alergia_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', Si1No2($row['vacunacion_familiaSalud']));
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['sangre_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['fechacreacion_familiaSalud']);
 $sheet->setCellValue(getColumnLetter($columnIndex++) . '2', $row['fechaedicion_familiaSalud']);

}


// Crear un objeto Writer para escribir el archivo Excel
$writer = new Xlsx($spreadsheet);

// Configurar las cabeceras para la descarga del archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="FamiliaSalud ' . $nombreEstudiante . '.xlsx"');
header('Cache-Control: max-age=0');

// Enviar el archivo al navegador
$writer->save('php://output');
exit;
