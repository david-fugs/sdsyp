<?php
session_start();
ob_start();
include("../../conexion.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $id = intval($_POST['id_masiva_centro_vida'] ?? 0);
    if ($id <= 0) {
        throw new Exception('ID inválido');
    }

    $id_meta = intval($_POST['id_meta'] ?? 0);
    $id_actividad = intval($_POST['id_actividad'] ?? 0);
    $id_accion = intval($_POST['id_accion'] ?? 0);
    $id_actividad_centro_vida = intval($_POST['id_actividad_centro_vida'] ?? 0);
    $politica_publica = $mysqli->real_escape_string($_POST['politica_publica'] ?? '');
    $id_centro_vida = intval($_POST['id_centro_vida'] ?? 0);
    $nombre_lider = $mysqli->real_escape_string($_POST['nombre_lider'] ?? '');
    $telefono_contacto = $mysqli->real_escape_string($_POST['telefono_contacto'] ?? '');
    $id_comuna = intval($_POST['id_comuna'] ?? 0);
    $medio_verificacion = $mysqli->real_escape_string($_POST['medio_verificacion'] ?? '');
    $cantidad_masculino = intval($_POST['cantidad_masculino'] ?? 0);
    $cantidad_femenino = intval($_POST['cantidad_femenino'] ?? 0);
    $observacion_actividad = $mysqli->real_escape_string($_POST['observacion_actividad'] ?? '');
    $funcionario_responsable = intval($_POST['funcionario_responsable'] ?? 0);
    $tipo_actividad = 'Masiva';
    $tipo_registro = $mysqli->real_escape_string($_POST['tipo_registro'] ?? '');
    $jornada = $mysqli->real_escape_string(trim($_POST['jornada'] ?? ''));
    $profesion = $mysqli->real_escape_string(trim($_POST['profesion'] ?? ''));

    // La fila de masiva_centro_vida guarda una única fecha; tomamos la primera
    // seleccionada en el calendario (el hidden "fecha_atencion" sirve de respaldo)
    $fechas_atencion_json = $_POST['fechas_atencion'] ?? '[]';
    $fechas_array = json_decode($fechas_atencion_json, true);
    if (json_last_error() === JSON_ERROR_NONE && !empty($fechas_array)) {
        $fecha_atencion = $fechas_array[0];
    } else {
        $fecha_atencion = $_POST['fecha_atencion'] ?? '';
    }
    if (empty($fecha_atencion)) {
        throw new Exception('Debe seleccionar la fecha de atención.');
    }
    $fecha_atencion = $mysqli->real_escape_string($fecha_atencion);

    $sql = "UPDATE masiva_centro_vida SET
     id_meta=?, id_actividad=?, id_accion=?, politica_publica=?, id_centro_vida=?, fecha_atencion=?, nombre_lider=?, telefono_contacto=?, id_comuna=?, medio_verificacion=?, cantidad_masculino=?, cantidad_femenino=?, tipo_actividad=?, tipo_registro=?, jornada=?, profesion=?, observacion_actividad=?, funcionario_responsable=?, id_actividad_centro_vida=?
     WHERE id_masiva_centro_vida=?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparando actualización: ' . $mysqli->error);
    }
    $stmt->bind_param(
        'iiisisssisiisssssiii',
        $id_meta,
        $id_actividad,
        $id_accion,
        $politica_publica,
        $id_centro_vida,
        $fecha_atencion,
        $nombre_lider,
        $telefono_contacto,
        $id_comuna,
        $medio_verificacion,
        $cantidad_masculino,
        $cantidad_femenino,
        $tipo_actividad,
        $tipo_registro,
        $jornada,
        $profesion,
        $observacion_actividad,
        $funcionario_responsable,
        $id_actividad_centro_vida,
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar: ' . $stmt->error);
    }
    $stmt->close();

    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente']);
} catch (Exception $e) {
    error_log('Error en editRegistroMasivoCentroVida.php: ' . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$mysqli->close();
