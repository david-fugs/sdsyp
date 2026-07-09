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
    $cedulas_json = $_POST['cedulas_json'] ?? '';

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

    // Sincronizar personas del grupo (solo aplica a Registro Actividad)
    $numero_grupo = null;
    $res_ng = $mysqli->query("SELECT numero_grupo FROM masiva_centro_vida WHERE id_masiva_centro_vida = $id");
    if ($res_ng && $row_ng = $res_ng->fetch_assoc()) {
        $numero_grupo = $row_ng['numero_grupo'] !== null ? (int)$row_ng['numero_grupo'] : null;
    }

    if ($tipo_registro === 'Registro Actividad') {
        $cedulas_nuevas = [];
        $decoded = json_decode($cedulas_json, true);
        if (is_array($decoded)) {
            foreach ($decoded as $c) {
                $c = trim($c);
                if ($c !== '') $cedulas_nuevas[] = $c;
            }
        }

        if ($numero_grupo === null && !empty($cedulas_nuevas)) {
            $ng_res = $mysqli->query("SELECT COALESCE(MAX(numero_grupo), 0) + 1 AS next_ng FROM registro_centro_vida");
            $numero_grupo = $ng_res ? (int)$ng_res->fetch_assoc()['next_ng'] : 1;
        }

        if ($numero_grupo !== null) {
            // Cédulas actualmente en el grupo
            $cedulas_actuales = [];
            $res_act = $mysqli->query("SELECT id_registro_centro_vida, cedula_persona FROM registro_centro_vida WHERE numero_grupo = $numero_grupo");
            if ($res_act) {
                while ($r = $res_act->fetch_assoc()) {
                    $cedulas_actuales[$r['cedula_persona']] = (int)$r['id_registro_centro_vida'];
                }
            }

            // Eliminar las que el usuario quitó de la lista
            foreach ($cedulas_actuales as $ced => $id_reg) {
                if (!in_array($ced, $cedulas_nuevas, true)) {
                    $mysqli->query("DELETE FROM registro_centro_vida_fechas WHERE id_registro_centro_vida = $id_reg");
                    $mysqli->query("DELETE FROM registro_centro_vida_grupo_externo WHERE id_registro_centro_vida = $id_reg");
                    $mysqli->query("DELETE FROM registro_centro_vida WHERE id_registro_centro_vida = $id_reg");
                }
            }

            // Insertar las que el usuario agregó
            $stmt_ins = $mysqli->prepare(
                "INSERT INTO registro_centro_vida (cedula_persona, id_meta, id_actividad, id_accion, id_actividad_centro_vida, politica_publica, departamento_procedencia, observacion, profesion, jornada, funcionario_registro, numero_grupo, fecha_registro)
                 VALUES (?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?, ?, NOW())"
            );
            $stmt_ge = $mysqli->prepare(
                "INSERT IGNORE INTO registro_centro_vida_grupo_externo (id_registro_centro_vida, id_grupo_externo)
                 SELECT ?, id_grupo_externo FROM persona_grupo_externo WHERE cedula_persona = ?"
            );
            $id_usuario_sesion = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
            if ($stmt_ins) {
                foreach ($cedulas_nuevas as $ced) {
                    if (isset($cedulas_actuales[$ced])) continue;
                    $stmt_ins->bind_param(
                        'siiiissssii',
                        $ced,
                        $id_meta,
                        $id_actividad,
                        $id_accion,
                        $id_actividad_centro_vida,
                        $politica_publica,
                        $observacion_actividad,
                        $profesion,
                        $jornada,
                        $id_usuario_sesion,
                        $numero_grupo
                    );
                    if ($stmt_ins->execute()) {
                        $id_reg_new = $mysqli->insert_id;
                        if ($id_reg_new > 0) {
                            if (!empty($fecha_atencion)) {
                                $mysqli->query("INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES ($id_reg_new, '$fecha_atencion')");
                            }
                            if ($stmt_ge) {
                                $stmt_ge->bind_param('is', $id_reg_new, $ced);
                                $stmt_ge->execute();
                            }
                        }
                    }
                }
                $stmt_ins->close();
            }
            if ($stmt_ge) $stmt_ge->close();
        }
    }

    $sql = "UPDATE masiva_centro_vida SET
     id_meta=?, id_actividad=?, id_accion=?, politica_publica=?, id_centro_vida=?, fecha_atencion=?, nombre_lider=?, telefono_contacto=?, id_comuna=?, medio_verificacion=?, cantidad_masculino=?, cantidad_femenino=?, tipo_actividad=?, tipo_registro=?, jornada=?, profesion=?, observacion_actividad=?, funcionario_responsable=?, id_actividad_centro_vida=?, numero_grupo=?
     WHERE id_masiva_centro_vida=?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparando actualización: ' . $mysqli->error);
    }
    $stmt->bind_param(
        'iiisisssisiisssssiiii',
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
        $numero_grupo,
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
