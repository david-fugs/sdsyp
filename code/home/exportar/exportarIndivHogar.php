<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include("../../../conexion.php");

date_default_timezone_set("America/Bogota");
$mysqli->set_charset('utf8');
if (!isset($_GET['id_hog'])) {
    header('Location: ../../../index.php');
}
$id_hog = $_GET['id_hog'];

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
    } 
    if ($value == 0) {
        return "NO";
    } 
    if($value == 2) {
        return "No Aplica";
    }
}
// Crear una nueva instancia de Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sql = "SELECT entornohogar.*, estudiantes.nom_ape_est 
FROM entornohogar 
JOIN estudiantes ON entornohogar.num_doc_est = estudiantes.num_doc_est
WHERE entornohogar.id_hog = $id_hog
 ORDER BY num_doc_est ASC";

// Ejecutar la consulta
$res = mysqli_query($mysqli, $sql);
// Verificar si la consulta se ejecutó correctamente
if ($res === false) {
    // Mostrar un mensaje de error si la consulta falla
    echo "Error en la consulta: " . mysqli_error($mysqli);
    exit;
}
// Aplicar color de fondo a las celdas A1 a AL1
$sheet->getStyle('A1:AY1')->applyFromArray([
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
$sheet->getStyle('A2:AY2')->applyFromArray(['font' => $boldFontStyle]);

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
$sheet->getStyle('A1:AY1')->applyFromArray(['font' => $styleHeader, 'fill' => $styleHeader, 'alignment' => $styleHeader]);


// // Definir los encabezados de columna

$sheet->setCellValue('A1', 'NOMBRE ESTUDIANTE');
$sheet->setCellValue('B1', 'DOCUMENTO ESTUDIANTE');
$sheet->setCellValue('C1', 'MUNICIPIO DILIGENCIA');
$sheet->setCellValue('D1', 'NOMBRE ENCUESTADOR');
$sheet->setCellValue('E1', 'ROL ENCUESTADOR');

$sheet->setCellValue('F1', 'NOMBRE MADRE');
$sheet->setCellValue('G1', 'AUN VIVE');
$sheet->setCellValue('H1', 'OCUPACION MADRE ');
$sheet->setCellValue('I1', 'NIVEL EDUCATIVO MADRE');
$sheet->setCellValue('J1', 'NOMBRE PADRE');
$sheet->setCellValue('K1', 'AUN VIVE');
$sheet->setCellValue('L1', 'OCUPACION PADRE ');
$sheet->setCellValue('M1', 'NIVEL EDUCATIVO PADRE');
$sheet->setCellValue('N1', 'ESTUDIANTE VIVE CON');
$sheet->setCellValue('O1', 'CON QUIEN VIVE');
$sheet->setCellValue('P1', 'NOMBRE CUIDADOR');
$sheet->setCellValue('Q1', 'PARENTESCO CUIDADOR');
$sheet->setCellValue('R1', 'ESPEFIQUE OTRO');
$sheet->setCellValue('S1', 'NIVEL EDUCATIVO CUIDADOR');
$sheet->setCellValue('T1', 'OCUPACION CUIDADOR');
$sheet->setCellValue('U1', 'CUAL OCUPACION');
$sheet->setCellValue('V1', 'CONTACTO CUIDADOR');
$sheet->setCellValue('W1', 'EMAIL CUIDADOR');
$sheet->setCellValue('X1', 'NUMERO HERMANOS');
$sheet->setCellValue('Y1', 'LUGAR QUE OCUPA DE HERMANOS');
$sheet->setCellValue('Z1', 'NUMERO DE HERMANOS ESTUDIANDO');
$sheet->setCellValue('AA1', 'NIVEL EDUCATIVO HERMANOS');
$sheet->setCellValue('AB1', 'QUIENES APOYAN PROCESO CRIANZA');
$sheet->setCellValue('AC1', 'QUIEN APOYA PROCESO');
$sheet->setCellValue('AD1', 'CUAL ES LA PRACTICA COMUNICATIVA MAS FRECUENTE');
$sheet->setCellValue('AE1', 'CATEGORIA FAMILIA');
$sheet->setCellValue('AF1', 'FAMILIA RECIBE SUBSIDIO');
$sheet->setCellValue('AG1', 'NOMBRE SUBSIDIO');
$sheet->setCellValue('AH1', 'MECANISMOS SOLUCION CONFLICTOS');
$sheet->setCellValue('AI1', 'OTROS TIPOS DE MECANISMOS DE SOLUCION CONFLICTOS');
$sheet->setCellValue('AJ1', 'QUIENES SOLUCIONAN LOS INCONVENIENTES');
$sheet->setCellValue('AK1', 'MENCIONE LOS QUE SOLUCIONAN INCONVENIENTES');
$sheet->setCellValue('AL1', 'COMO SOLUCIONA LOS INCONVENIENTES');
$sheet->setCellValue('AM1', 'QUE OTRA FORMA DE SOLUCIONAR INCONVENIENTES');
$sheet->setCellValue('AN1', 'RESPONSABILIDADES DEL ESTUDIANTE EN EL HOGAR');
$sheet->setCellValue('AO1', 'CUALES RESPONSABILIDADES');
$sheet->setCellValue('AP1', 'COMO EXPRESAN EL AFECTO ENTRE LA FAMILIA');
$sheet->setCellValue('AQ1', 'DE QUE MANERA EXPRESAN AFECTO');
$sheet->setCellValue('AR1', 'TIPO VIVIENDA');
$sheet->setCellValue('AS1', 'TENENCIA DE VIVIENDA');
$sheet->setCellValue('AT1', 'OTRO TIPO TENENCIA');
$sheet->setCellValue('AU1', 'MATERIAL DE CONSTRUCCION');
$sheet->setCellValue('AV1', 'SERVICIOS DEL HOGAR');
$sheet->setCellValue('AW1', 'CUANTAS PERSONAS VIVEN EN VIVIENDA');
$sheet->setCellValue('AX1', 'FECHA DE APLICACIÓN');
$sheet->setCellValue('AY1', 'FECHA DE MODIFICACIÓN');



$sheet->getDefaultRowDimension()->setRowHeight(25);
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
$sheet->getColumnDimension('R')->setWidth(35);
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
$sheet->getColumnDimension('AD')->setWidth(18);
$sheet->getColumnDimension('AE')->setWidth(25);
$sheet->getColumnDimension('AF')->setWidth(25);
$sheet->getColumnDimension('AG')->setWidth(25);
$sheet->getColumnDimension('AH')->setWidth(25);
$sheet->getColumnDimension('AI')->setWidth(25);
$sheet->getColumnDimension('AJ')->setWidth(25);
$sheet->getColumnDimension('AK')->setWidth(25);
$sheet->getColumnDimension('AL')->setWidth(18);
$sheet->getColumnDimension('AM')->setWidth(25);
$sheet->getColumnDimension('AN')->setWidth(25);
$sheet->getColumnDimension('AO')->setWidth(25);
$sheet->getColumnDimension('AP')->setWidth(25);
$sheet->getColumnDimension('AQ')->setWidth(25);
$sheet->getColumnDimension('AR')->setWidth(25);
$sheet->getColumnDimension('AS')->setWidth(25);
$sheet->getColumnDimension('AT')->setWidth(18);
$sheet->getColumnDimension('AU')->setWidth(25);
$sheet->getColumnDimension('AV')->setWidth(25);
$sheet->getColumnDimension('AW')->setWidth(25);
$sheet->getColumnDimension('AX')->setWidth(25);
$sheet->getColumnDimension('AY')->setWidth(25);
$sheet->getColumnDimension('AZ')->setWidth(25);
$nombreEst = '';
$rowIndex = 2;
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    $nombreEst= $row['nom_ape_est'];
    $sheet->setCellValue('A'. $rowIndex, $row['nom_ape_est']);
    $sheet->setCellValue('B'. $rowIndex, $row['num_doc_est']);
    $sheet->setCellValue('C'. $rowIndex, $row['mun_dig_hog']);
    $sheet->setCellValue('D'. $rowIndex, $row['nombre_encuestador_hog']);
    $sheet->setCellValue('E'. $rowIndex, $row['rol_encuestador_hog']);
    
    $sheet->setCellValue('F'. $rowIndex, $row['nombre_madre_hog']);
    $sheet->setCellValue('G'. $rowIndex, Si1No2($row['vive_madre_hog']));
    $sheet->setCellValue('H'. $rowIndex, $row['ocupacion_madre_hog']);
    $sheet->setCellValue('I'. $rowIndex, $row['educacion_madre_hog']);
    $sheet->setCellValue('J'. $rowIndex, $row['nombre_padre_hog']);
    $sheet->setCellValue('K'. $rowIndex, Si1No2($row['vive_padre_hog']));
    $sheet->setCellValue('L'. $rowIndex, $row['ocupacion_padre_hog']);
    $sheet->setCellValue('M'. $rowIndex, $row['educacion_padre_hog']);
    $sheet->setCellValue('N'. $rowIndex, $row['vive_estu_hog']);
    $sheet->setCellValue('O'. $rowIndex, $row['nom_vive_estu_hog']);
    $sheet->setCellValue('P'. $rowIndex, $row['cuidador_estu_hog']);
    $sheet->setCellValue('Q'. $rowIndex, $row['parentesco_cuid_estu_hog']);
    $sheet->setCellValue('R'. $rowIndex, $row['nom_parentesco_cuid_estu_hog']);
    $sheet->setCellValue('S'. $rowIndex, $row['educacion_cuid_estu_hog']);
    $sheet->setCellValue('T'. $rowIndex, $row['ocupacion_cuid_estu_hog']);
    $sheet->setCellValue('U'. $rowIndex, $row['nom_ocupacion_cuid_estu_hog']);
    $sheet->setCellValue('V'. $rowIndex, $row['tel_cuid_estu_hog']);
    $sheet->setCellValue('W'. $rowIndex, $row['email_cuid_estu_hog']);
    $sheet->setCellValue('X'. $rowIndex, $row['num_herm_estu_hog']);
    $sheet->setCellValue('Y'. $rowIndex, $row['lugar_ocupa_estu_hog']);
    $sheet->setCellValue('Z'. $rowIndex, Si1No2($row['tiene_herm_ie_estu_hog']));
    $sheet->setCellValue('AA'. $rowIndex, $row['niveles_educativos_herm_ie_estu_hog']);
    $sheet->setCellValue('AB'. $rowIndex, $row['crianza_estu_hog']);
    $sheet->setCellValue('AC'. $rowIndex, $row['nom_crianza_estu_hog']);
    $sheet->setCellValue('AD'. $rowIndex, $row['prac_comu_estu_hog']);
    $sheet->setCellValue('AE'. $rowIndex, $row['fam_categ_estu_hog']);
    $sheet->setCellValue('AF'. $rowIndex, Si1No2($row['fam_subsidio_hog']));
    $sheet->setCellValue('AG'. $rowIndex, $row['tipo_subsidio_hog']);
    $sheet->setCellValue('AH'. $rowIndex, $row['mecanismos_conflictos_estu_hog']);
    $sheet->setCellValue('AI'. $rowIndex, $row['nom_mecanismos_conflictos_estu_hog']);
    $sheet->setCellValue('AJ'. $rowIndex, $row['inconvenientes_quien_hog']);
    $sheet->setCellValue('AK'. $rowIndex, $row['nom_quien_inconvenientes_hog']);
    $sheet->setCellValue('AL'. $rowIndex, $row['inconvenientes_como_hog']);
    $sheet->setCellValue('AM'. $rowIndex, $row['nom_como_inconvenientes_hog']);
    $sheet->setCellValue('AN'. $rowIndex, $row['responsabilidades_est_hog']);
    $sheet->setCellValue('AO'. $rowIndex, $row['nom_responsabilidades_est_hog']);
    $sheet->setCellValue('AP'. $rowIndex, $row['afecto_est_hog']);
    $sheet->setCellValue('AQ'. $rowIndex, $row['nom_afecto_est_hog']);
    $sheet->setCellValue('AR'. $rowIndex, $row['tipo_vivienda_hog']);
    $sheet->setCellValue('AS'. $rowIndex, $row['tenencia_vivienda_hog']);
    $sheet->setCellValue('AT'. $rowIndex, $row['nom_tenencia_vivienda_hog']);
    $sheet->setCellValue('AU'. $rowIndex, $row['material_vivienda_hog']);
    $sheet->setCellValue('AV'. $rowIndex, $row['servicios_vivienda_hog']);
    $sheet->setCellValue('AW'. $rowIndex, $row['num_personas_vivienda_hog']);
    $sheet->setCellValue('AX'. $rowIndex, $row['fecha_alta_hog']);
    $sheet->setCellValue('AY'. $rowIndex, $row['fecha_edit_hog']);
    $sheet->getStyle('A' .$rowIndex. ':AC'.$rowIndex.'')->applyFromArray(['font' => $boldFontStyle]);
    $rowIndex++;

}

$filename = 'EntornoHogar'. $nombreEst.'.xlsx';
$writer = new Xlsx($spreadsheet);

// Set the headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the generated Excel file to the browser
$writer->save('php://output');
exit;









