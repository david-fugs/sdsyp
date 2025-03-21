<?php 
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

require '../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['error' => 'Error al subir el archivo']));
    }

    $tempFile = $_FILES['excelFile']['tmp_name'];

    include("../../conexion.php");

    $reader = ReaderEntityFactory::createXLSXReader();
    $reader->open($tempFile);

    $loteDatos = [];
    $contadorRegistros = 0;
    $loteTamano = 1000;

    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $data = $row->toArray();

            if ($contadorRegistros == 0) {
                $contadorRegistros++;
                continue;
            }

            list($num_doc_est, $tip_doc_est, $fecha_dig_est, $mun_dig_est, $nom_ape_est, $fec_nac_est) = $data;

            $loteDatos[] = "('$num_doc_est', '$tip_doc_est', '$fecha_dig_est', '$mun_dig_est', '$nom_ape_est', '$fec_nac_est')";

            $contadorRegistros++;

            if (count($loteDatos) >= $loteTamano) {
                procesarLote($loteDatos, $mysqli);
                $loteDatos = [];
            }
        }
    }

    if (!empty($loteDatos)) {
        procesarLote($loteDatos, $mysqli);
    }

    $reader->close();
    echo json_encode(["finalizado" => "Carga completada"]);
}

function procesarLote($loteDatos, $mysqli) {
    $sql = "INSERT INTO estudiantes (num_doc_est, tip_doc_est, fecha_dig_est, mun_dig_est, nom_ape_est, fec_nac_est) 
            VALUES " . implode(", ", $loteDatos) . "
            ON DUPLICATE KEY UPDATE tip_doc_est = VALUES(tip_doc_est), fecha_dig_est = VALUES(fecha_dig_est)";

    $mysqli->query($sql);
}
