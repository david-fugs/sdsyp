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
                , // Columna A
                , // Columna B
                $mun_dig_est, // Columna C
                , // Columna D
                , // Columna E
                , // Columna F
                , // Columna G
                , // Columna H
                , // Columna I
                , // Columna J
                $tip_doc_est, // Columna K
                $num_doc_est, // Columna L
                , // Columna M y posteriores
            ) = $data;

            // Añadir la fecha actual
            $fecha_dig_est = date('Y-m-d');

            // Añadir los datos actuales al lote
            $loteDatos[] = [
                'mun_dig_est' => $mun_dig_est,
                'tip_doc_est' => $tip_doc_est,
                'num_doc_est' => $num_doc_est,
                'fecha_dig_est' => $fecha_dig_est,
                // Otros campos que desees agregar
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
?>
