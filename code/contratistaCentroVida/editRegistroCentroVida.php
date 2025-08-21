<?php
include("../../conexion.php");
session_start();
header('Content-Type: application/json; charset=utf-8');

// Debug
error_log("EDIT POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_registro = isset($_POST['id_registro_centro_vida']) ? intval($_POST['id_registro_centro_vida']) : 0;
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

$fechas_atencion_json = $_POST['fechas_atencion'] ?? '[]';

try {
    if ($id_registro <= 0) throw new Exception('ID de registro inválido');
    if (empty($cedula_persona)) throw new Exception('La cédula es requerida');

    $fechas_array = json_decode($fechas_atencion_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Formato de fechas inválido');
    }

    // Iniciar transacción
    $mysqli->autocommit(FALSE);

    // Actualizar registro principal
    $sql_update = "UPDATE registro_centro_vida SET
        cedula_persona = ?,
        id_condicion = ?,
        id_meta = ?,
        id_actividad = ?,
        id_accion = ?,
    id_actividad_centro_vida = ?,
        politica_publica = ?,
        departamento_procedencia = ?,
        observacion = ?,
        funcionario_registro = ?,
        fecha_registro = NOW()
        WHERE id_registro_centro_vida = ?";

    $stmt = $mysqli->prepare($sql_update);
    if (!$stmt) throw new Exception('Error al preparar consulta de actualización: ' . $mysqli->error);

    // Bind parameters - types: i for integers, s for strings
    $stmt->bind_param('iiiiiiisssi', $cedula_persona, $id_condicion, $id_meta, $id_actividad, $id_accion, $id_actividad_centro_vida, $politica_publica, $departamento_procedencia, $observacion, $funcionario_registro, $id_registro);

    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar registro: ' . $stmt->error);
    }
    $stmt->close();

    // Eliminar fechas anteriores
    $sql_delete = "DELETE FROM registro_centro_vida_fechas WHERE id_registro_centro_vida = ?";
    $stmt_del = $mysqli->prepare($sql_delete);
    if (!$stmt_del) throw new Exception('Error al preparar eliminación de fechas: ' . $mysqli->error);
    $stmt_del->bind_param('i', $id_registro);
    if (!$stmt_del->execute()) {
        throw new Exception('Error al eliminar fechas anteriores: ' . $stmt_del->error);
    }
    $stmt_del->close();

    // Insertar nuevas fechas
    if (!empty($fechas_array) && is_array($fechas_array)) {
        $sql_insert_fecha = "INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES (?, ?)";
        $stmt_fecha = $mysqli->prepare($sql_insert_fecha);
        if (!$stmt_fecha) throw new Exception('Error al preparar inserción de fechas: ' . $mysqli->error);

        foreach ($fechas_array as $fecha) {
            if (!empty($fecha)) {
                $stmt_fecha->bind_param('is', $id_registro, $fecha);
                if (!$stmt_fecha->execute()) {
                    throw new Exception('Error al insertar fecha: ' . $stmt_fecha->error);
                }
            }
        }
        $stmt_fecha->close();
    }

    $mysqli->commit();
    $mysqli->autocommit(TRUE);

    echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente', 'id_registro' => $id_registro]);

} catch (Exception $e) {
    $mysqli->rollback();
    $mysqli->autocommit(TRUE);
    error_log('Error en editRegistroCentroVida: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
