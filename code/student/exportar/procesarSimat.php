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

        $loteTamano = 500; // Número de filas por lote
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

            //NECESARIO PARA INGRESAR
            // L2,K2,texnow,C2,S2,AE2,Q2,V2,AB2,AC2,BO2,W2,estudiante@est.edu.co,SOLTERO(A),AK2," ", " ", AD2, , F2 , " ", " ", AQ2 , AS2 , CF2 , AU2 , AM2, AY2 , BA2 , BC2, BD2 , BE2, 1 , " ", " ", FECHAACTUAL,  1 , 1900-01-01 00:00:00 , 1

            // Descomponer los datos en variables según su posición
            list( , // Columna A
                , // Columna B
                $mun_dig_est, //C
                , //D
                , //E
                $cod_dane_ieSede, //F
                , //G
                , //H
                , //I
                , //J
                $tip_doc_est, //K
               $num_doc_est , //L
                , //M
                , // Columna N
                , // Columna O
                , // Columna P
                $ciu_nac_est, // Columna Q
                , // Columna R
               $nom_ape_est , // Columna S
                , // Columna T
                , // Columna U
                $dir_est, // Columna V
                $tel1_est, // Columna W
                , // Columna X
                , // Columna Y
                , // Columna Z
                , // Columna AA
               $mun_res_est, // Columna AB
                $estrato_est, // Columna AC
                $sisben_est, // Columna AD
               $fec_nac_est , // Columna AE
                , // Columna AF
                , // Columna AG
                , // Columna AH
                , // Columna AI
                , // Columna AJ
                $gen_est, // Columna AK
                , // Columna AL
                $victima_est, // Columna AM
                , // Columna AN
                , // Columna AO
                , // Columna AP
                $discapacidad_est, // Columna AQ
                , // Columna AR
                $capacidad_est, // Columna AS
                , // Columna AT
                $etnia_est, // Columna AU
                , // Columna AV
                , // Columna AW
                , // Columna AX
                $jornada_est, // Columna AY
                , // Columna AZ
                $caracter_media_est, // Columna BA
                , // Columna BB
               $especialidad_caracter_est , // Columna BC
                $grado_est, // Columna BD
                $nom_grado_est, // Columna BE
                , // Columna BF
                , // Columna BG
                , // Columna BH
                , // Columna BI
                , // Columna BJ
                , // Columna BK
                , // Columna BL
                , // Columna BM
                , // Columna BN
                $zona_est, // Columna BO
                , // Columna BP
                , // Columna BQ
                , // Columna BR
                , // Columna BS
                , // Columna BT
                , // Columna BU
                , // Columna BV
                , // Columna BW
                , // Columna BX
                , // Columna BY
                , // Columna BZ
                , // Columna CA
                , // Columna CB
                , // Columna CC
                , // Columna CD
                , // Columna CE
                $trastorno_est, // Columna CF
                , // Columna CG
                , // Columna CH
            ) = $data;

            // Añadir la fecha actual
            $fecha_dig_est = date('Y-m-d');
            $email_est = 'estudiante@est.edu.co';
            $est_civ_est = "SOLTERO (A)";
            $eps_est = " ";
            $med_trans_est = " ";
            $obs_est = " ";
            $poblacion_vulnerable_est = " ";
            $estado_est = 1;
            $nombre_encuestador_est = " ";
            $rol_encuestador_est = " ";
            $fecha_alta_est = date('Y-m-d H:i:s');
            $id_usu_alta  = 1;
            $fecha_edit_est = '1900-01-01 00:00:00';
            $id_usu = 1;


            // Añadir los datos actuales al lote
            $loteDatos[] = [
                'num_doc_est'  => $num_doc_est,
                'tip_doc_est'  => $tip_doc_est,
                'fecha_dig_est' =>  $fecha_dig_est,
                'mun_dig_est' => $mun_dig_est,
                'cod_dane_ieSede' => $cod_dane_ieSede,
                'ciu_nac_est' => $ciu_nac_est,
                'nom_ape_est' => $nom_ape_est,
                'dir_est' => $dir_est,
                'tel1_est' => $tel1_est,
                'mun_res_est' => $mun_res_est,
                'estrato_est' => $estrato_est,
                'sisben_est' => $sisben_est,
                'fec_nac_est' => $fec_nac_est,
                'gen_est' => $gen_est,
                'victima_est' => $victima_est,
                'discapacidad_est' => $discapacidad_est,
                'capacidad_est' => $capacidad_est,
                'etnia_est' => $etnia_est,
                'jornada_est' => $jornada_est,
                'caracter_media_est' => $caracter_media_est,
                'especialidad_caracter_est' => $especialidad_caracter_est,
                'grado_est' => $grado_est,
                'nom_grado_est' => $nom_grado_est,
                'zona_est' => $zona_est,
                'trastorno_est' => $trastorno_est,
                'email_est' => $email_est,
                'est_civ_est' => $est_civ_est,
                'eps_est' => $eps_est,
                'med_trans_est' => $med_trans_est,
                'obs_est' => $obs_est,
                'poblacion_vulnerable_est' => $poblacion_vulnerable_est,
                'estado_est' => $estado_est,
                'nombre_encuestador_est' => $nombre_encuestador_est,
                'rol_encuestador_est' => $rol_encuestador_est,
                'fecha_alta_est' => $fecha_alta_est,
                'id_usu_alta' => $id_usu_alta,
                'fecha_edit_est' => $fecha_edit_est,
                'id_usu' => $id_usu

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
function procesarLote($loteDatos)
{
    include("../../../conexion.php");
 
    // Iniciar transacción
    $mysqli->begin_transaction();

    try {
        // Preparar la consulta de inserción
        $sql = "INSERT INTO estudiantes (
            num_doc_est, tip_doc_est, fecha_dig_est, mun_dig_est, nom_ape_est, fec_nac_est, ciu_nac_est, dir_est, mun_res_est, estrato_est, zona_est, tel1_est, tel2_est, email_est, est_civ_est, gen_est, eps_est, med_trans_est, sisben_est, cod_dane_ieSede, obs_est, poblacion_vulnerable_est, discapacidad_est, capacidad_est, trastorno_est, etnia_est, victima_est, jornada_est, caracter_media_est, especialidad_caracter_est, grado_est, nom_grado_est, estado_est, nombre_encuestador_est, rol_encuestador_est, fecha_alta_est, id_usu_alta, fecha_edit_est, id_usu
        ) VALUES ";

        // Acumular los valores para la consulta
        $values = [];
        foreach ($loteDatos as $datos) {
            $values[] = "(
                '" . $mysqli->real_escape_string($datos['num_doc_est']) . "',
                '" . $mysqli->real_escape_string($datos['tip_doc_est']) . "',
                '" . $mysqli->real_escape_string($datos['fecha_dig_est']) . "',
                '" . $mysqli->real_escape_string($datos['mun_dig_est']) . "',
                '" . $mysqli->real_escape_string($datos['nom_ape_est']) . "',
                '" . $mysqli->real_escape_string($datos['fec_nac_est']) . "',
                '" . $mysqli->real_escape_string($datos['ciu_nac_est']) . "',
                '" . $mysqli->real_escape_string($datos['dir_est']) . "',
                '" . $mysqli->real_escape_string($datos['mun_res_est']) . "',
                '" . $mysqli->real_escape_string($datos['estrato_est']) . "',
                '" . $mysqli->real_escape_string($datos['zona_est']) . "',
                '" . $mysqli->real_escape_string($datos['tel1_est']) . "',
                '" . '' . "',
                '" . $mysqli->real_escape_string($datos['email_est']) . "',
                '" . $mysqli->real_escape_string($datos['est_civ_est']) . "',
                '" . $mysqli->real_escape_string($datos['gen_est']) . "',
                '" . $mysqli->real_escape_string($datos['eps_est']) . "',
                '" . $mysqli->real_escape_string($datos['med_trans_est']) . "',
                '" . $mysqli->real_escape_string($datos['sisben_est']) . "',
                '" . $mysqli->real_escape_string($datos['cod_dane_ieSede']) . "',
                '" . $mysqli->real_escape_string($datos['obs_est']) . "',
                '" . $mysqli->real_escape_string($datos['poblacion_vulnerable_est']) . "',
                '" . $mysqli->real_escape_string($datos['discapacidad_est']) . "',
                '" . $mysqli->real_escape_string($datos['capacidad_est']) . "',
                '" . $mysqli->real_escape_string($datos['trastorno_est']) . "',
                '" . $mysqli->real_escape_string($datos['etnia_est']) . "',
                '" . $mysqli->real_escape_string($datos['victima_est']) . "',
                '" . $mysqli->real_escape_string($datos['jornada_est']) . "',
                '" . $mysqli->real_escape_string($datos['caracter_media_est']) . "',
                '" . $mysqli->real_escape_string($datos['especialidad_caracter_est']) . "',
                '" . $mysqli->real_escape_string($datos['grado_est']) . "',
                '" . $mysqli->real_escape_string($datos['nom_grado_est']) . "',
                '" . $mysqli->real_escape_string($datos['estado_est']) . "',
                '" . $mysqli->real_escape_string($datos['nombre_encuestador_est']) . "',
                '" . $mysqli->real_escape_string($datos['rol_encuestador_est']) . "',
                '" . $mysqli->real_escape_string($datos['fecha_alta_est']) . "',
                '" . $mysqli->real_escape_string($datos['id_usu_alta']) . "',
                '" . $mysqli->real_escape_string($datos['fecha_edit_est']) . "',
                '" . $mysqli->real_escape_string($datos['id_usu']) . "'
            )";
        }

        // Unir todos los valores en una sola consulta
        $sql .= implode(", ", $values);

        // Ejecutar la consulta
        if ($mysqli->query($sql) === TRUE) {
            // Confirmar la transacción
            $mysqli->commit();
            echo json_encode([
                'estado' => 'procesado',
                'mensaje' => 'Lote de datos procesado e insertado correctamente.',
                'loteDatos' => $loteDatos
            ]);
        } else {
            throw new Exception("Error al insertar datos: " . $mysqli->error);
        }
    } catch (Exception $e) {
        // Deshacer la transacción en caso de error
        $mysqli->rollback();
        echo json_encode([
            'estado' => 'error',
            'mensaje' => $e->getMessage()
        ]);
    }
}

