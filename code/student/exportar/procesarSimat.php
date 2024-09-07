<?php
ini_set('memory_limit', '1G');  // Opcional: Incrementar límite de memoria si es necesario

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES['archivo']['tmp_name'];

        require '../../vendor/autoload.php';

        // Crear el lector en modo de streaming
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tempFile);
        $reader->setReadDataOnly(true); // Lee solo los datos (sin estilos, para mayor eficiencia)

        // Crear el lector con modo iterativo
        $spreadsheet = $reader->load($tempFile);
        $sheet = $spreadsheet->getActiveSheet();

        $loteTamano = 1000; // Número de filas por lote
        $maxIterations = PHP_INT_MAX; // Sin límite de iteraciones
        $iterationCount = 0; // Contador de iteraciones
        $loteDatos = []; // Inicializar el array para los datos del lote
        $filaInicial = 2; // Fila inicial de los datos en la hoja de cálculo
        
        foreach ($sheet->getRowIterator($filaInicial) as $row) {
            // Incrementar el contador de iteraciones
            $iterationCount++;
            
            // Salir si se alcanza el límite de iteraciones
            // En este caso, PHP_INT_MAX permite procesar todas las filas
            // Puedes establecer un límite mayor si deseas restringir la cantidad de filas procesadas
            
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);
        
            $data = [];
            foreach ($cells as $cell) {
                $data[] = $cell->getValue();
            }
        
            // Descomponer los datos en las variables correspondientes
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
        
            // Preparar los datos del lote
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
                // Imprimir el lote de datos actual
                echo json_encode([
                    'estado' => 'procesado',
                    'mensaje' => 'Lote de datos procesado.',
                    'loteDatos' => $loteDatos
                ]);
        
                // Limpiar los datos del lote para continuar con el siguiente grupo
                $loteDatos = [];
            }
        }
        
        // Si hay datos restantes en el lote después de salir del bucle, imprimirlos
        if (!empty($loteDatos)) {
            echo json_encode([
                'estado' => 'procesado',
                'mensaje' => 'Lote final procesado.',
                'loteDatos' => $loteDatos
            ]);
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
