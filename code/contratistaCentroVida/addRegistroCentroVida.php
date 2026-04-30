<?php
ob_start();
session_start();
ini_set('display_errors', 0);
include("../../conexion.php");

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Debug: Mostrar todos los datos recibidos
error_log("POST data recibido: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Capturar datos del formulario
        $cedula_persona = $_POST['cedula_persona'] ?? null;
        $id_condicion = $_POST['id_condicion'] ?? null;
        $condicion_otra = (isset($_POST['id_condicion']) && $_POST['id_condicion'] === 'otra') ? (trim($_POST['condicion_otra'] ?? '')) : null;
        if ($id_condicion === 'otra') $id_condicion = null; // guardar NULL en id_condicion cuando es "Otra"
        $id_meta = $_POST['id_meta'] ?? null;
        $id_actividad = $_POST['id_actividad'] ?? null;
        $id_accion = $_POST['id_accion'] ?? null;
        $id_actividad_centro_vida = $_POST['id_actividad_centro_vida'] ?? null;
        $politica_publica = $_POST['politica_publica'] ?? '';
        $departamento_procedencia = $_POST['departamento_procedencia'] ?? '';
        $observacion = $_POST['observacion'] ?? '';
        $profesion = $_POST['profesion'] ?? null;
        $jornada = $_POST['jornada'] ?? null;
        $grupos_externos_post = array_values(array_filter(array_map('intval', $_POST['grupos_externos'] ?? []), function($v){ return $v > 0; }));
        // Guardar el ID numérico del usuario para filtrado (el nombre se resuelve por JOIN)
        $funcionario_registro = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;

        // Obtener las fechas del nuevo parámetro
        $fechas_atencion_json = $_POST['fechas_atencion'] ?? '[]';
        
        // Debug: Mostrar datos capturados
        error_log("Datos capturados - Cedula: $cedula_persona, Fechas JSON: $fechas_atencion_json");

        // Validar campos requeridos
        if (empty($cedula_persona)) {
            throw new Exception("La cédula es requerida.");
        }
        if (empty($id_condicion)) {
            throw new Exception("La condición es requerida.");
        }

        // Decodificar fechas JSON
        $fechas_array = json_decode($fechas_atencion_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($fechas_array)) {
            throw new Exception("Debe seleccionar al menos una fecha de atención.");
        }

        // Debug: Mostrar fechas decodificadas
        error_log("Fechas decodificadas: " . print_r($fechas_array, true));

        // Iniciar transacción
        $mysqli->autocommit(FALSE);

        // Preparar sentencias: 1 registro por cada fecha seleccionada
        $sql_insert_registro = "INSERT INTO registro_centro_vida 
            (cedula_persona, id_condicion, condicion_otra, id_meta, id_actividad, id_accion, id_actividad_centro_vida, 
             politica_publica, departamento_procedencia, observacion, profesion, jornada, funcionario_registro, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $mysqli->prepare($sql_insert_registro);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta principal: " . $mysqli->error);
        }

        $sql_insert_fecha = "INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES (?, ?)";
        $stmt_fecha = $mysqli->prepare($sql_insert_fecha);
        if (!$stmt_fecha) {
            throw new Exception("Error al preparar consulta de fechas: " . $mysqli->error);
        }

        $stmt_ge = null;
        if (!empty($grupos_externos_post)) {
            $stmt_ge = $mysqli->prepare("INSERT IGNORE INTO registro_centro_vida_grupo_externo (id_registro_centro_vida, id_grupo_externo) VALUES (?, ?)");
            if (!$stmt_ge) {
                throw new Exception("Error al preparar consulta de grupos externos: " . $mysqli->error);
            }
        }

        $registros_insertados = 0;
        $primer_id = null;

        // Insertar 1 registro por cada fecha seleccionada
        foreach ($fechas_array as $fecha) {
            if (empty($fecha)) continue;

            $stmt->bind_param("sisiiiisssssi", $cedula_persona, $id_condicion, $condicion_otra, $id_meta, $id_actividad, $id_accion,
                     $id_actividad_centro_vida, $politica_publica,
                     $departamento_procedencia, $observacion, $profesion, $jornada, $funcionario_registro);

            if (!$stmt->execute()) {
                throw new Exception("Error al insertar registro para fecha '$fecha': " . $stmt->error);
            }

            $id_registro_centro_vida = $mysqli->insert_id;
            if ($primer_id === null) $primer_id = $id_registro_centro_vida;

            $stmt_fecha->bind_param("is", $id_registro_centro_vida, $fecha);
            if (!$stmt_fecha->execute()) {
                throw new Exception("Error al insertar fecha '$fecha': " . $stmt_fecha->error);
            }

            // Insertar grupos externos para este registro
            if ($stmt_ge) {
                foreach ($grupos_externos_post as $id_ge) {
                    $stmt_ge->bind_param("ii", $id_registro_centro_vida, $id_ge);
                    $stmt_ge->execute();
                }
            }

            $registros_insertados++;
        }

        $stmt->close();
        $stmt_fecha->close();
        if ($stmt_ge) $stmt_ge->close();

        // Confirmar transacción
        $mysqli->commit();
        $mysqli->autocommit(TRUE);

        // Debug: Confirmar inserción exitosa
        error_log("Registros insertados: $registros_insertados (1 por fecha) - Primer ID: $primer_id");

        // Devolver respuesta JSON exitosa
        $msg = $registros_insertados === 1
            ? "Registro de centro vida agregado correctamente. ID: $primer_id"
            : "Se agregaron $registros_insertados registros (uno por cada fecha seleccionada).";

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => $msg,
            'id_registro' => $primer_id,
            'registros_insertados' => $registros_insertados
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $mysqli->rollback();
        $mysqli->autocommit(TRUE);
        
        // Debug: Mostrar error
        error_log("Error en addRegistroCentroVida.php: " . $e->getMessage());
        
        // Devolver respuesta JSON de error
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Devolver respuesta JSON para método no permitido
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
