<?php
ini_set('memory_limit', '2G');  // Incrementar el límite de memoria si es necesario

require '../../vendor/autoload.php';  // Cargar la librería de PhpSpreadsheet

// Utilizar el lector de PhpSpreadsheet en modo de streaming
use PhpOffice\PhpSpreadsheet\Reader\Xlsx; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES['archivo']['tmp_name'];

        // Crear el lector y configurar para que lea solo datos (sin estilos)
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);

        // Cargar el archivo (modo de lectura eficiente)
        $spreadsheet = $reader->load($tempFile);
        $sheet = $spreadsheet->getActiveSheet();

        $loteTamano = 1000; // Número de filas por lote
        $loteDatos = []; // Inicializar el array para los datos del lote
        $filaInicial = 2; // Fila inicial de los datos (si la primera fila es de encabezados)

        foreach ($sheet->getRowIterator($filaInicial) as $rowIndex => $row) {
            // Obtener celdas de la fila actual
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false); // Asegurar que se lean todas las celdas, incluso vacías

            $data = [];
            foreach ($cells as $cell) {
                $data[] = $cell->getValue();
            }

            // Descomponer los datos en variables según su posición
            list(
                $mun_dig_est,
                $cod_dane_ieSede,
                $nom_ie_est,
                $cod_dane_ieSede_2,
                $nom_sede_est,
                $grado_est,
                $tip_doc_est,
                $num_doc_est,
                $nom_ape_est,
                $nom_ape2_est,
                $dir_est,
                $tel1_est,
                $mun_res_est,
                $estrato_est,
                $zona_est,
                $fec_nac_est,
                $gen_est,
                $etnia_est,
                $victima_est,
                $caracter_media_est,
                $especialidad_caracter_est
            ) = $data;

            // Añadir los datos actuales al lote
            $loteDatos[] = [
                'mun_dig_est' => $mun_dig_est,
                'cod_dane_ieSede' => $cod_dane_ieSede,
                'nom_ie_est' => $nom_ie_est,
                'cod_dane_ieSede_2' => $cod_dane_ieSede_2,
                'nom_sede_est' => $nom_sede_est,
                'grado_est' => $grado_est,
                'tip_doc_est' => $tip_doc_est,
                'num_doc_est' => $num_doc_est,
                'nom_ape_est' => $nom_ape_est,
                'nom_ape2_est' => $nom_ape2_est,
                'dir_est' => $dir_est,
                'tel1_est' => $tel1_est,
                'mun_res_est' => $mun_res_est,
                'estrato_est' => $estrato_est,
                'zona_est' => $zona_est,
                'fec_nac_est' => $fec_nac_est,
                'gen_est' => $gen_est,
                'etnia_est' => $etnia_est,
                'victima_est' => $victima_est,
                'caracter_media_est' => $caracter_media_est,
                'especialidad_caracter_est' => $especialidad_caracter_est
            ];

            // Verificar si el lote alcanzó el tamaño definido
            if (count($loteDatos) >= $loteTamano) {
                procesarLote($loteDatos); // Procesar el lote
                $loteDatos = []; // Vaciar el array para el siguiente lote
                gc_collect_cycles(); // Forzar la recolección de basura
            }
        }

        // Procesar el lote restante (si hay)
        if (!empty($loteDatos)) {
            procesarLote($loteDatos);
        }

    } else {
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'Error al subir el archivo.'
        ]);
    }
} else {
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Método de solicitud no permitido.'
    ]);
}

// Función para procesar un lote de datos
function procesarLote($loteDatos) {
    // Aquí puedes procesar el lote o guardarlo en la base de datos
    echo json_encode([
        'estado' => 'procesado',
        'mensaje' => 'Lote de datos procesado.',
        'loteDatos' => $loteDatos
    ]);
}
