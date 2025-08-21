<?php
include("../../conexion.php");
session_start();

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Debug: Mostrar todos los datos recibidos
error_log("POST data recibido: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Capturar datos del formulario
        $cedula_persona = $_POST['cedula_persona'] ?? null;
        $id_condicion = $_POST['id_condicion'] ?? null;
        $id_meta = $_POST['id_meta'] ?? null;
        $id_actividad = $_POST['id_actividad'] ?? null;
        $id_accion = $_POST['id_accion'] ?? null;
    $id_actividad_centro_vida = $_POST['id_actividad_centro_vida'] ?? null;
        $politica_publica = $_POST['politica_publica'] ?? '';
        $departamento_procedencia = $_POST['departamento_procedencia'] ?? '';
        $observacion = $_POST['observacion'] ?? '';
        $funcionario_registro = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Sistema';

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

        // Insertar el registro principal
        $sql_insert_registro = "INSERT INTO registro_centro_vida 
            (cedula_persona, id_condicion, id_meta, id_actividad, id_accion, id_actividad_centro_vida, 
             politica_publica, departamento_procedencia, observacion, funcionario_registro, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $mysqli->prepare($sql_insert_registro);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta principal: " . $mysqli->error);
        }

    $stmt->bind_param("iiiiiiisss", $cedula_persona, $id_condicion, $id_meta, $id_actividad, $id_accion, 
             $id_actividad_centro_vida, $politica_publica, 
             $departamento_procedencia, $observacion, $funcionario_registro);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar el registro principal: " . $stmt->error);
        }

        $id_registro_centro_vida = $mysqli->insert_id;
        $stmt->close();

        // Insertar las fechas seleccionadas
        $sql_insert_fecha = "INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES (?, ?)";
        $stmt_fecha = $mysqli->prepare($sql_insert_fecha);
        
        if (!$stmt_fecha) {
            throw new Exception("Error al preparar consulta de fechas: " . $mysqli->error);
        }

        $fechas_insertadas = 0;
        foreach ($fechas_array as $fecha) {
            if (!empty($fecha)) {
                $stmt_fecha->bind_param("is", $id_registro_centro_vida, $fecha);
                if (!$stmt_fecha->execute()) {
                    throw new Exception("Error al insertar fecha '$fecha': " . $stmt_fecha->error);
                }
                $fechas_insertadas++;
            }
        }
        
        $stmt_fecha->close();

        // Confirmar transacción
        $mysqli->commit();
        $mysqli->autocommit(TRUE);

        // Debug: Confirmar inserción exitosa
        error_log("Registro insertado correctamente - ID: $id_registro_centro_vida, Fechas: $fechas_insertadas");

        // Devolver respuesta JSON exitosa
        echo json_encode([
            'success' => true,
            'message' => "Registro de centro vida agregado correctamente. ID: $id_registro_centro_vida",
            'id_registro' => $id_registro_centro_vida,
            'fechas_insertadas' => $fechas_insertadas
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $mysqli->rollback();
        $mysqli->autocommit(TRUE);
        
        // Debug: Mostrar error
        error_log("Error en addRegistroCentroVida.php: " . $e->getMessage());
        
        // Devolver respuesta JSON de error
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Devolver respuesta JSON para método no permitido
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
