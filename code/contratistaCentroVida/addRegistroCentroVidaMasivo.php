<?php
ob_start();
session_start();
ini_set('display_errors', 0);
include("../../conexion.php");

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
    $id_condicion_raw = $_POST['id_condicion'] ?? null;
    $condicion_otra = ($id_condicion_raw === 'otra') ? trim($_POST['condicion_otra'] ?? '') : null;
    $id_condicion = ($id_condicion_raw === 'otra') ? null : $id_condicion_raw;
    $id_meta = $_POST['id_meta'] ?? null;
    $id_actividad = $_POST['id_actividad'] ?? null;
    $id_accion = $_POST['id_accion'] ?? null;
    $id_actividad_centro_vida = $_POST['id_actividad_centro_vida'] ?? null;
    $politica_publica = isset($_POST['politica_publica']) ? intval($_POST['politica_publica']) : 0;
    $departamento_procedencia = $_POST['departamento_procedencia'] ?? '';
    $observacion = $_POST['observacion'] ?? '';
    $profesion = $_POST['profesion'] ?? null;
    $jornada = $_POST['jornada'] ?? null;
    // Guardar el ID numérico del usuario para filtrado (el nombre se resuelve por JOIN)
    $funcionario_registro = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
    if ($funcionario_registro <= 0) {
        throw new Exception('Sesión no válida. Por favor, recargue la página e inicie sesión nuevamente.');
    }

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

    // Pre-cargar jornada de cada persona desde la tabla personas
    $jornadas_map = [];
    if (!empty($cedulas)) {
        $cedulas_esc_pre = array_map(fn($c) => "'" . $mysqli->real_escape_string(trim($c)) . "'", $cedulas);
        $in_pre = implode(',', $cedulas_esc_pre);
        $jorn_res = $mysqli->query("SELECT cedula_persona, jornada FROM personas WHERE cedula_persona IN ($in_pre)");
        if ($jorn_res) {
            while ($jr = $jorn_res->fetch_assoc()) {
                $jornadas_map[$jr['cedula_persona']] = $jr['jornada'];
            }
        }
    }

    // Iniciar transacción
    $mysqli->autocommit(FALSE);

    // INSERT: 1 registro por persona por fecha
    $sql_insert = "INSERT INTO registro_centro_vida (cedula_persona, id_condicion, condicion_otra, id_meta, id_actividad, id_accion, id_actividad_centro_vida, politica_publica, departamento_procedencia, observacion, profesion, jornada, funcionario_registro, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql_insert);
    if (!$stmt) throw new Exception('Error al preparar consulta principal: ' . $mysqli->error);

    $sql_fecha = "INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES (?, ?)";
    $stmt_fecha = $mysqli->prepare($sql_fecha);
    if (!$stmt_fecha) throw new Exception('Error al preparar consulta de fechas: ' . $mysqli->error);

    // Grupos externos: auto-poblar desde persona_grupo_externo
    $sql_ge = "INSERT IGNORE INTO registro_centro_vida_grupo_externo (id_registro_centro_vida, id_grupo_externo)
               SELECT ?, id_grupo_externo FROM persona_grupo_externo WHERE cedula_persona = ?";
    $stmt_ge = $mysqli->prepare($sql_ge);
    // No falla si la tabla no tiene datos; simplemente no inserta nada

    $inserted = 0;
    $failures = [];

    foreach ($cedulas as $ced) {
        $ced = trim($ced);
        if ($ced === '') continue;

        // Jornada tomada de la tabla personas para esta cédula
        $jornada_persona = $jornadas_map[$ced] ?? $jornada;

        foreach ($fechas as $f) {
            if (empty($f)) continue;

            // 1 registro por persona por fecha
            $stmt->bind_param('sisiiiisssssi', $ced, $id_condicion, $condicion_otra, $id_meta, $id_actividad, $id_accion, $id_actividad_centro_vida, $politica_publica, $departamento_procedencia, $observacion, $profesion, $jornada_persona, $funcionario_registro);
            if (!$stmt->execute()) {
                $failures[] = ['cedula' => $ced, 'fecha' => $f, 'error' => $stmt->error];
                continue;
            }

            $id_reg = $mysqli->insert_id;
            $stmt_fecha->bind_param('is', $id_reg, $f);
            if (!$stmt_fecha->execute()) {
                $failures[] = ['cedula' => $ced, 'fecha' => $f, 'error' => $stmt_fecha->error];
                continue;
            }

            // Auto-poblar grupos externos de la persona
            if ($stmt_ge && $id_reg > 0) {
                $stmt_ge->bind_param('is', $id_reg, $ced);
                $stmt_ge->execute();
            }

            $inserted++;
        }
    }

    $stmt->close();
    $stmt_fecha->close();
    if ($stmt_ge) $stmt_ge->close();

    if (count($failures) > 0) {
        $mysqli->rollback();
        $mysqli->autocommit(TRUE);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Ocurrieron errores en algunos registros.', 'failures' => $failures]);
        exit;
    }

    $mysqli->commit();
    $mysqli->autocommit(TRUE);

    // ── Crear registro resumen en masiva_centro_vida ────────────────────────
    if ($inserted > 0 && !empty($cedulas)) {
        $cedulas_esc = array_map(function($c) use ($mysqli) {
            return "'" . $mysqli->real_escape_string(trim($c)) . "'";
        }, $cedulas);
        $cedulas_in = implode(',', $cedulas_esc);

        // Contar géneros desde personas
        $cnt_masc = 0; $cnt_fem = 0;
        $cnt_res = $mysqli->query("SELECT genero_persona, COUNT(*) as cnt FROM personas WHERE cedula_persona IN ($cedulas_in) GROUP BY genero_persona");
        if ($cnt_res) {
            while ($cr = $cnt_res->fetch_assoc()) {
                $g_lower = strtolower(trim($cr['genero_persona'] ?? ''));
                if ($g_lower === 'masculino') $cnt_masc = (int)$cr['cnt'];
                elseif ($g_lower === 'femenino') $cnt_fem = (int)$cr['cnt'];
            }
        }

        // Obtener id_grupo de la primera persona con grupo CV
        $id_centro_vida_masivo = 0;
        $cv_res = $mysqli->query("SELECT p.id_grupo FROM personas p INNER JOIN grupos g ON p.id_grupo = g.id_grupo WHERE p.cedula_persona IN ($cedulas_in) AND g.descripcion_grupo LIKE 'CV%' LIMIT 1");
        if ($cv_res) {
            $cv_row = $cv_res->fetch_assoc();
            $id_centro_vida_masivo = $cv_row ? (int)$cv_row['id_grupo'] : 0;
        }

        // Usar primera fecha disponible
        $primera_fecha_masivo = !empty($fechas) ? $mysqli->real_escape_string($fechas[0]) : date('Y-m-d');
        $obs_esc   = $mysqli->real_escape_string($observacion ?? '');
        $prof_esc  = $mysqli->real_escape_string($profesion ?? '');
        $jorn_esc  = $mysqli->real_escape_string($jornada ?? '');
        $pol_int   = intval($politica_publica ?? 0);
        $meta_int  = intval($id_meta ?? 0);
        $act_int   = intval($id_actividad ?? 0);
        $acc_int   = intval($id_accion ?? 0);
        $acv_int   = intval($id_actividad_centro_vida ?? 0);

        $sql_masivo = "INSERT INTO masiva_centro_vida
            (id_meta, id_actividad, id_accion, politica_publica, id_centro_vida,
             fecha_atencion, nombre_lider, telefono_contacto, id_comuna, medio_verificacion,
             cantidad_masculino, cantidad_femenino, tipo_actividad, observacion_actividad,
             id_usuario, funcionario_responsable, id_actividad_centro_vida, tipo_registro, jornada, profesion)
            VALUES
            ($meta_int, $act_int, $acc_int, $pol_int, $id_centro_vida_masivo,
             '$primera_fecha_masivo', '', '', 0, '',
             $cnt_masc, $cnt_fem, 'Masiva', '$obs_esc',
             $funcionario_registro, $funcionario_registro, $acv_int, 'Registro Actividad', '$jorn_esc', '$prof_esc')";
        $mysqli->query($sql_masivo); // Error no crítico; no interrumpir el flujo
    }
    // ────────────────────────────────────────────────────────────────────────

    ob_clean();
    echo json_encode(['success' => true, 'message' => "Registros agregados: $inserted"]);
    exit;

} catch (Exception $e) {
    if ($mysqli->errno) {
        $mysqli->rollback();
        $mysqli->autocommit(TRUE);
    }
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

?>