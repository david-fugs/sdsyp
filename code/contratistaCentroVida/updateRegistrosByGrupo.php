<?php
session_start();
include("../../conexion.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$numero_grupo = isset($_POST['numero_grupo']) ? intval($_POST['numero_grupo']) : 0;
if ($numero_grupo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Número de grupo inválido.']);
    exit;
}

$accion = $_POST['accion'] ?? 'update'; // 'update' o 'delete'

if ($accion === 'delete') {
    // Eliminar todos los registros del grupo
    // Primero eliminar fechas
    $ids_res = $mysqli->query("SELECT id_registro_centro_vida FROM registro_centro_vida WHERE numero_grupo = " . intval($numero_grupo));
    if ($ids_res) {
        $ids = [];
        while ($r = $ids_res->fetch_assoc()) $ids[] = $r['id_registro_centro_vida'];
        if (!empty($ids)) {
            $ids_in = implode(',', $ids);
            $mysqli->query("DELETE FROM registro_centro_vida_fechas WHERE id_registro_centro_vida IN ($ids_in)");
            $mysqli->query("DELETE FROM registro_centro_vida_grupo_externo WHERE id_registro_centro_vida IN ($ids_in)");
        }
    }
    $del = $mysqli->query("DELETE FROM registro_centro_vida WHERE numero_grupo = " . intval($numero_grupo));
    if ($del) {
        echo json_encode(['success' => true, 'message' => 'Todos los registros del grupo ' . $numero_grupo . ' fueron eliminados.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $mysqli->error]);
    }
    exit;
}

if ($accion === 'delete_persona') {
    $id_reg = isset($_POST['id_registro_centro_vida']) ? intval($_POST['id_registro_centro_vida']) : 0;
    if ($id_reg <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de registro inválido.']);
        exit;
    }
    $mysqli->query("DELETE FROM registro_centro_vida_fechas WHERE id_registro_centro_vida = " . $id_reg);
    $mysqli->query("DELETE FROM registro_centro_vida_grupo_externo WHERE id_registro_centro_vida = " . $id_reg);
    $del = $mysqli->query("DELETE FROM registro_centro_vida WHERE id_registro_centro_vida = " . $id_reg);
    if ($del) {
        echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $mysqli->error]);
    }
    exit;
}

if ($accion === 'add_persona') {
    $cedula_persona = trim($_POST['cedula_persona'] ?? '');
    if (empty($cedula_persona)) {
        echo json_encode(['success' => false, 'message' => 'La cédula es requerida.']);
        exit;
    }
    // Verify person exists
    $chk = $mysqli->prepare("SELECT cedula_persona FROM personas WHERE cedula_persona = ?");
    if (!$chk) { echo json_encode(['success' => false, 'message' => 'Error BD.']); exit; }
    $chk->bind_param('s', $cedula_persona);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows === 0) {
        $chk->close();
        echo json_encode(['success' => false, 'message' => 'No se encontró una persona con esa cédula.']);
        exit;
    }
    $chk->close();

    $id_meta                  = intval($_POST['id_meta'] ?? 0);
    $id_actividad             = intval($_POST['id_actividad'] ?? 0);
    $id_accion                = intval($_POST['id_accion'] ?? 0);
    $id_actividad_centro_vida = intval($_POST['id_actividad_centro_vida'] ?? 0);
    $politica_publica         = $_POST['politica_publica'] ?? '';
    $departamento_procedencia = $_POST['departamento_procedencia'] ?? 'Risaralda';
    $observacion              = $_POST['observacion'] ?? '';
    $profesion                = $_POST['profesion'] ?? '';
    $jornada                  = trim($_POST['jornada'] ?? '');
    $fechas_json              = $_POST['fechas_seleccionadas'] ?? '[]';
    $funcionario_registro     = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
    // Get id_condicion from group template
    $cond_res = $mysqli->query("SELECT id_condicion, condicion_otra FROM registro_centro_vida WHERE numero_grupo = " . intval($numero_grupo) . " LIMIT 1");
    $id_condicion = 5; $condicion_otra = null;
    if ($cond_res && $row_c = $cond_res->fetch_assoc()) {
        $id_condicion = $row_c['id_condicion'];
        $condicion_otra = $row_c['condicion_otra'];
    }

    $fechas = json_decode($fechas_json, true);
    if (!is_array($fechas) || empty($fechas)) {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos una fecha.']);
        exit;
    }

    $mysqli->autocommit(FALSE);
    try {
        $sql_ins = "INSERT INTO registro_centro_vida
            (cedula_persona, id_condicion, condicion_otra, id_meta, id_actividad, id_accion,
             id_actividad_centro_vida, politica_publica, departamento_procedencia, observacion,
             profesion, jornada, funcionario_registro, numero_grupo, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_ins = $mysqli->prepare($sql_ins);
        if (!$stmt_ins) throw new Exception('Error preparando INSERT: ' . $mysqli->error);
        $stmt_ins->bind_param('ssiiiiiissssii',
            $cedula_persona, $id_condicion, $condicion_otra,
            $id_meta, $id_actividad, $id_accion, $id_actividad_centro_vida,
            $politica_publica, $departamento_procedencia, $observacion,
            $profesion, $jornada, $funcionario_registro, $numero_grupo);
        if (!$stmt_ins->execute()) throw new Exception('Error al insertar: ' . $stmt_ins->error);
        $new_id = $mysqli->insert_id;
        $stmt_ins->close();

        $stmt_f = $mysqli->prepare("INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES (?, ?)");
        if (!$stmt_f) throw new Exception('Error preparando fechas: ' . $mysqli->error);
        foreach ($fechas as $f) {
            if (empty(trim($f))) continue;
            $stmt_f->bind_param('is', $new_id, $f);
            $stmt_f->execute();
        }
        $stmt_f->close();

        $mysqli->commit();
        $mysqli->autocommit(TRUE);
        echo json_encode(['success' => true, 'message' => 'Persona agregada al grupo correctamente.', 'id_registro' => $new_id]);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->autocommit(TRUE);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Acción update
$id_meta                 = intval($_POST['id_meta'] ?? 0);
$id_actividad            = intval($_POST['id_actividad'] ?? 0);
$id_accion               = intval($_POST['id_accion'] ?? 0);
$id_actividad_centro_vida = intval($_POST['id_actividad_centro_vida'] ?? 0);
$politica_publica        = $_POST['politica_publica'] ?? '';
$departamento_procedencia = $_POST['departamento_procedencia'] ?? '';
$observacion             = $_POST['observacion'] ?? '';
$profesion               = $_POST['profesion'] ?? '';
$jornada                 = trim($_POST['jornada'] ?? '');
$fechas_json             = $_POST['fechas_seleccionadas'] ?? ($_POST['fechas_atencion'] ?? '[]');

$fechas = json_decode($fechas_json, true);
if (!is_array($fechas)) {
    $fechas = array_filter(array_map('trim', explode(',', $fechas_json)));
}
if (!is_array($fechas) || count($fechas) === 0) {
    echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos una fecha de atención.']);
    exit;
}

$mysqli->autocommit(FALSE);
try {
    // Obtener IDs de registros del grupo
    $ids_res = $mysqli->query("SELECT id_registro_centro_vida FROM registro_centro_vida WHERE numero_grupo = " . intval($numero_grupo));
    if (!$ids_res) throw new Exception('Error al obtener registros: ' . $mysqli->error);
    $ids = [];
    while ($r = $ids_res->fetch_assoc()) $ids[] = (int)$r['id_registro_centro_vida'];

    if (empty($ids)) throw new Exception('No hay registros para el grupo ' . $numero_grupo . '.');

    // Actualizar campos comunes en todos los registros del grupo
    $stmt_upd2 = $mysqli->prepare(
        "UPDATE registro_centro_vida
         SET id_meta=?, id_actividad=?, id_accion=?, id_actividad_centro_vida=?,
             politica_publica=?, departamento_procedencia=?, observacion=?,
             profesion=?, jornada=?
         WHERE numero_grupo=?"
    );
    if (!$stmt_upd2) throw new Exception('Error preparando UPDATE2: ' . $mysqli->error);
    $stmt_upd2->bind_param('iiiiissssi', $id_meta, $id_actividad, $id_accion, $id_actividad_centro_vida,
        $politica_publica, $departamento_procedencia, $observacion, $profesion, $jornada, $numero_grupo);
    if (!$stmt_upd2->execute()) throw new Exception('Error al actualizar: ' . $stmt_upd2->error);
    $stmt_upd2->close();

    // Actualizar fechas: borrar las existentes e insertar las nuevas
    $ids_in = implode(',', $ids);
    $mysqli->query("DELETE FROM registro_centro_vida_fechas WHERE id_registro_centro_vida IN ($ids_in)");

    $stmt_fecha = $mysqli->prepare("INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES (?, ?)");
    if (!$stmt_fecha) throw new Exception('Error preparando fechas: ' . $mysqli->error);
    foreach ($ids as $id_reg) {
        foreach ($fechas as $f) {
            if (empty(trim($f))) continue;
            $stmt_fecha->bind_param('is', $id_reg, $f);
            $stmt_fecha->execute();
        }
    }
    $stmt_fecha->close();

    $mysqli->commit();
    $mysqli->autocommit(TRUE);
    echo json_encode(['success' => true, 'message' => count($ids) . ' registros del grupo ' . $numero_grupo . ' actualizados correctamente.', 'numero_grupo' => $numero_grupo]);
} catch (Exception $e) {
    $mysqli->rollback();
    $mysqli->autocommit(TRUE);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
