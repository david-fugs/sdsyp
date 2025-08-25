<?php
include("../../conexion.php");
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Debug incoming payload
    error_log('addRegistroCentroVidaMasivo POST: ' . print_r($_POST, true));

    $cedulas_json = $_POST['cedulas'] ?? '[]';
    $cedulas = json_decode($cedulas_json, true);
    if (!is_array($cedulas)) {
        // intentar parsear si viene como string simple
        $cedulas = array_filter(array_map('trim', explode(',', trim($cedulas_json, '[]"'))));
    }
    if (!is_array($cedulas) || count($cedulas) === 0) {
        throw new Exception('No se enviaron cédulas.');
    }

    // Campos comunes
    $id_condicion = $_POST['id_condicion'] ?? null;
    $id_meta = $_POST['id_meta'] ?? null;
    $id_actividad = $_POST['id_actividad'] ?? null;
    $id_accion = $_POST['id_accion'] ?? null;
    $id_actividad_centro_vida = $_POST['id_actividad_centro_vida'] ?? null;
    $politica_publica = isset($_POST['politica_publica']) ? intval($_POST['politica_publica']) : 0;
    $departamento_procedencia = $_POST['departamento_procedencia'] ?? '';
    $observacion = $_POST['observacion'] ?? '';
    $funcionario_registro = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Sistema';

    $fechas_json = $_POST['fechas_seleccionadas'] ?? ($_POST['fechas_atencion'] ?? '[]');
    // Intentar parsear JSON; si no es JSON, aceptar formato coma-separado
    $fechas = json_decode($fechas_json, true);
    if (!is_array($fechas)) {
        // Si viene como string con comas
        $fechas = array_filter(array_map('trim', explode(',', $fechas_json)));
    }
    error_log('Fechas procesadas: ' . print_r($fechas, true));
    if (!is_array($fechas) || count($fechas) === 0) {
        throw new Exception('Debe seleccionar al menos una fecha de atención.');
    }

    // Iniciar transacción
    $mysqli->autocommit(FALSE);

    $sql_insert = "INSERT INTO registro_centro_vida (cedula_persona, id_condicion, id_meta, id_actividad, id_accion, id_actividad_centro_vida, politica_publica, departamento_procedencia, observacion, funcionario_registro, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql_insert);
    if (!$stmt) throw new Exception('Error al preparar consulta principal: ' . $mysqli->error);

    $sql_fecha = "INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES (?, ?)";
    $stmt_fecha = $mysqli->prepare($sql_fecha);
    if (!$stmt_fecha) throw new Exception('Error al preparar consulta de fechas: ' . $mysqli->error);

    $inserted = 0;
    $failures = [];

    foreach ($cedulas as $ced) {
        $ced = trim($ced);
        if ($ced === '') continue;

        // Bind params: cedula as string
        $stmt->bind_param('siiiiiisss', $ced, $id_condicion, $id_meta, $id_actividad, $id_accion, $id_actividad_centro_vida, $politica_publica, $departamento_procedencia, $observacion, $funcionario_registro);
        if (!$stmt->execute()) {
            $failures[] = ['cedula' => $ced, 'error' => $stmt->error];
            continue;
        }

        $id_reg = $mysqli->insert_id;
        foreach ($fechas as $f) {
            if (empty($f)) continue;
            $stmt_fecha->bind_param('is', $id_reg, $f);
            if (!$stmt_fecha->execute()) {
                $failures[] = ['cedula' => $ced, 'error' => $stmt_fecha->error];
            }
        }

        $inserted++;
    }

    $stmt->close();
    $stmt_fecha->close();

    if (count($failures) > 0) {
        $mysqli->rollback();
        $mysqli->autocommit(TRUE);
        echo json_encode(['success' => false, 'message' => 'Ocurrieron errores en algunos registros.', 'failures' => $failures]);
        exit;
    }

    $mysqli->commit();
    $mysqli->autocommit(TRUE);

    echo json_encode(['success' => true, 'message' => "Registros agregados: $inserted"]);
    exit;

} catch (Exception $e) {
    if ($mysqli->errno) {
        $mysqli->rollback();
        $mysqli->autocommit(TRUE);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

?>