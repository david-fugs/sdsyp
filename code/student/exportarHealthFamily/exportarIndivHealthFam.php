<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
include("../../../conexion.php");
    date_default_timezone_set("America/Bogota");
    $mysqli->set_charset('utf8');

// Crear una nueva instancia de Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

if(!isset($_GET['id_salud_familiaSalud'])) {
    header('Location: ../../../index.php');
}
$id_familiaSalud = $_GET['id_salud_familiaSalud'];
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

// Procesar los resultados
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    // Imprimir el resultado
    print_r($row);
}





// // Configurar la hoja de cálculo
// $sheet->setCellValue('A1', 'Hello World !');
// $sheet->setCellValue('A2', 'This is a test.');

// // Crear un objeto Writer para escribir el archivo Excel
// $writer = new Xlsx($spreadsheet);

// // Configurar las cabeceras para la descarga del archivo
// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// header('Content-Disposition: attachment;filename="example.xlsx"');
// header('Cache-Control: max-age=0');

// // Enviar el archivo al navegador
// $writer->save('php://output');
// exit;
