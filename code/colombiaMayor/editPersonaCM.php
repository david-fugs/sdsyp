<?php
session_start();
include("../../conexion.php");

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    header("Location: ../../access.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_SESSION['id'];
    
    // Asegurar que autocommit esté activado
    $mysqli->autocommit(TRUE);
    error_log("Autocommit activado: " . ($mysqli->autocommit(TRUE) ? 'SI' : 'NO'));
    
    // DEBUG: Log de todos los datos POST
    error_log("=== INICIO EDIT PERSONA ===");
    error_log("POST cedula_original: [" . ($_POST['cedula_original'] ?? 'NO ENVIADO') . "]");
    error_log("POST cedula_persona_cm: [" . ($_POST['cedula_persona_cm'] ?? 'NO ENVIADO') . "]");
    error_log("POST estado_cm: [" . ($_POST['estado_cm'] ?? 'NO ENVIADO') . "]");
    
    // Capturar datos básicos del formulario
    $cedula_original = $mysqli->real_escape_string($_POST['cedula_original']);
    $cedula_persona_cm = $mysqli->real_escape_string($_POST['cedula_persona_cm']);
    $tipo_identificacion_cm = $mysqli->real_escape_string($_POST['tipo_identificacion_cm']);
    $nombres_persona_cm = $mysqli->real_escape_string($_POST['nombres_persona_cm']);
    $apellidos_persona_cm = $mysqli->real_escape_string($_POST['apellidos_persona_cm'] ?? '');
    $genero_persona_cm = $mysqli->real_escape_string($_POST['genero_persona_cm']);
    $telefono_persona_cm = $mysqli->real_escape_string($_POST['telefono_persona_cm'] ?? '');
    $telefono_referencia_cm = $mysqli->real_escape_string($_POST['telefono_referencia_cm'] ?? '');
    $referencia_cm = $mysqli->real_escape_string($_POST['referencia_cm'] ?? '');
    $correo_cm = $mysqli->real_escape_string($_POST['correo_cm'] ?? '');
    $fecha_nacimiento_cm = $_POST['fecha_nacimiento_cm'] ?? null;
    $edad_cm = $_POST['edad_cm'] ?? null;
    $grupo_sisben = $mysqli->real_escape_string($_POST['grupo_sisben'] ?? '');
    $direccion_cm = $mysqli->real_escape_string($_POST['direccion_cm'] ?? '');
    $barrio_cm = $mysqli->real_escape_string($_POST['barrio_cm'] ?? '');
    $comuna_cm = $mysqli->real_escape_string($_POST['comuna_cm'] ?? '');
    $zona_cm = $mysqli->real_escape_string($_POST['zona_cm'] ?? '');
    $departamento_cm = $mysqli->real_escape_string($_POST['departamento_cm'] ?? 'Risaralda');
    $municipio_cm = $mysqli->real_escape_string($_POST['municipio_cm'] ?? 'Pereira');
    $estado_cm = $mysqli->real_escape_string($_POST['estado_cm'] ?? 'ACTIVO');
    $fecha_ingreso_cm = $_POST['fecha_ingreso_cm'] ?? null;
    $observaciones_cm = $mysqli->real_escape_string($_POST['observaciones_cm'] ?? '');
    
    // Capturar campos de salud
    $eps = $mysqli->real_escape_string($_POST['eps'] ?? '');
    $peso = $_POST['peso'] ?? null;
    $talla = $_POST['talla'] ?? null;
    $patologias = $mysqli->real_escape_string($_POST['patologias'] ?? '');
    $factores_riesgo = $mysqli->real_escape_string($_POST['factores_riesgo'] ?? '');
    $factores_preventivos = $mysqli->real_escape_string($_POST['factores_preventivos'] ?? '');
    $ingresos_economicos = $mysqli->real_escape_string($_POST['ingresos_economicos'] ?? '');
    $convivencia_actual = $mysqli->real_escape_string($_POST['convivencia_actual'] ?? '');
    $resultado_actividad = $mysqli->real_escape_string($_POST['resultado_actividad'] ?? '');
    $remision = $mysqli->real_escape_string($_POST['remision'] ?? '');
    
    // Capturar campos de caracterización
    $persona_discapacidad = $mysqli->real_escape_string($_POST['persona_discapacidad'] ?? '');
    $cual_discapacidad = ($persona_discapacidad === 'Si') ? $mysqli->real_escape_string($_POST['cual_discapacidad'] ?? '') : '';
    $cabeza_hogar = $mysqli->real_escape_string($_POST['cabeza_hogar'] ?? '');
    $lider_comunidad = $mysqli->real_escape_string($_POST['lider_comunidad'] ?? '');
    $se_reconoce_como = $mysqli->real_escape_string($_POST['se_reconoce_como'] ?? '');
    $orientacion_sexual = $mysqli->real_escape_string($_POST['orientacion_sexual'] ?? '');
    $experiencia_migratoria = $mysqli->real_escape_string($_POST['experiencia_migratoria'] ?? '');
    $grupo_etnico = $mysqli->real_escape_string($_POST['grupo_etnico'] ?? '');
    $tipo_salud = $mysqli->real_escape_string($_POST['tipo_salud'] ?? '');
    $nivel_educativo = $mysqli->real_escape_string($_POST['nivel_educativo'] ?? '');
    $condicion_ocupacion = $mysqli->real_escape_string($_POST['condicion_ocupacion'] ?? '');
    $condicion_componente = $mysqli->real_escape_string($_POST['condicion_componente'] ?? '');
    
    // Capturar campos de Meta, Actividad, Acción y Política Pública
    $id_meta = !empty($_POST['id_meta']) ? intval($_POST['id_meta']) : null;
    $id_actividad = !empty($_POST['id_actividad']) ? intval($_POST['id_actividad']) : null;
    $id_accion = !empty($_POST['id_accion']) ? intval($_POST['id_accion']) : null;
    $id_politica_publica = !empty($_POST['id_politica_publica']) ? intval($_POST['id_politica_publica']) : null;

    // DEBUG: Crear archivo temporal con el UPDATE completo
    $debug_file = "../../debug_last_update.txt";
    $debug_content = "=== UPDATE EJECUTADO " . date('Y-m-d H:i:s') . " ===\n";
    $debug_content .= "Usuario: $id_usuario\n";
    $debug_content .= "Cedula: $cedula_original\n";
    $debug_content .= "Estado POST: [" . ($_POST['estado_cm'] ?? 'NO ENVIADO') . "]\n";
    $debug_content .= "Estado escapado: [$estado_cm]\n\n";

    // Actualizar persona con todos los campos
    $sql_update = "UPDATE personas_colombia_mayor SET
        cedula_persona_cm = '$cedula_persona_cm',
        tipo_identificacion_cm = '$tipo_identificacion_cm',
        nombres_persona_cm = '$nombres_persona_cm',
        apellidos_persona_cm = '$apellidos_persona_cm',
        genero_persona_cm = '$genero_persona_cm',
        telefono_persona_cm = '$telefono_persona_cm',
        telefono_referencia_cm = '$telefono_referencia_cm',
        referencia_cm = '$referencia_cm',
        correo_cm = '$correo_cm',
        fecha_nacimiento_cm = " . ($fecha_nacimiento_cm ? "'$fecha_nacimiento_cm'" : 'NULL') . ",
        edad_cm = " . ($edad_cm ? "'$edad_cm'" : 'NULL') . ",
        grupo_sisben = '$grupo_sisben',
        direccion_cm = '$direccion_cm',
        barrio_cm = '$barrio_cm',
        comuna_cm = '$comuna_cm',
        zona_cm = '$zona_cm',
        departamento_cm = '$departamento_cm',
        municipio_cm = '$municipio_cm',
        estado_cm = '$estado_cm',
        fecha_ingreso_cm = " . ($fecha_ingreso_cm ? "'$fecha_ingreso_cm'" : 'NULL') . ",
        eps = '$eps',
        peso = " . ($peso ? "'$peso'" : 'NULL') . ",
        talla = " . ($talla ? "'$talla'" : 'NULL') . ",
        patologias = '$patologias',
        factores_riesgo = '$factores_riesgo',
        factores_preventivos = '$factores_preventivos',
        ingresos_economicos = '$ingresos_economicos',
        convivencia_actual = '$convivencia_actual',
        resultado_actividad = '$resultado_actividad',
        remision = '$remision',
        persona_discapacidad = '$persona_discapacidad',
        cual_discapacidad = '$cual_discapacidad',
        cabeza_hogar = '$cabeza_hogar',
        lider_comunidad = '$lider_comunidad',
        se_reconoce_como = '$se_reconoce_como',
        orientacion_sexual = '$orientacion_sexual',
        experiencia_migratoria = '$experiencia_migratoria',
        grupo_etnico = '$grupo_etnico',
        tipo_salud = '$tipo_salud',
        nivel_educativo = '$nivel_educativo',
        condicion_ocupacion = '$condicion_ocupacion',
        condicion_componente = '$condicion_componente',
        id_meta = " . ($id_meta ? $id_meta : 'NULL') . ",
        id_actividad = " . ($id_actividad ? $id_actividad : 'NULL') . ",
        id_accion = " . ($id_accion ? $id_accion : 'NULL') . ",
        id_politica_publica = " . ($id_politica_publica ? $id_politica_publica : 'NULL') . ",
        observaciones_cm = '$observaciones_cm',
        usuario_modificacion = '$id_usuario',
        fecha_modificacion = NOW()
    WHERE cedula_persona_cm = '$cedula_original'";

    // Guardar query completo en archivo debug
    $debug_content .= "QUERY COMPLETO:\n";
    $debug_content .= $sql_update . "\n\n";
    file_put_contents($debug_file, $debug_content);

    // Debug: Registrar valores importantes
    error_log("=== DEBUG EDIT PERSONA CM ===");
    error_log("Cedula Original: [$cedula_original]");
    error_log("Estado CM RAW POST: [" . ($_POST['estado_cm'] ?? 'NO ENVIADO') . "]");
    error_log("Estado CM después de escape: [$estado_cm]");
    
    // Mostrar parte del query UPDATE para debugging
    $query_preview = "UPDATE personas_colombia_mayor SET ... estado_cm = '$estado_cm' ... WHERE cedula_persona_cm = '$cedula_original'";
    error_log("Query resumen: $query_preview");
    
    if ($mysqli->query($sql_update)) {
        $filas_afectadas = $mysqli->affected_rows;
        error_log("Query ejecutado OK. Filas afectadas: $filas_afectadas");
        
        // VERIFICAR QUÉ VALOR QUEDÓ GUARDADO - ESPERAR UN MOMENTO
        usleep(100000); // 100ms de espera
        
        $sql_verify = "SELECT estado_cm FROM personas_colombia_mayor WHERE cedula_persona_cm = '$cedula_original'";
        $result_verify = $mysqli->query($sql_verify);
        $valor_guardado = 'ERROR';
        if ($result_verify && $row_verify = $result_verify->fetch_assoc()) {
            $valor_guardado = $row_verify['estado_cm'] ?? 'NULL';
            error_log("Valor verificado en DB después del UPDATE: [$valor_guardado]");
        } else {
            error_log("ERROR: No se pudo verificar el valor guardado");
        }
        
        // Agregar resultado al archivo debug
        $debug_result = "\nRESULTADO:\n";
        $debug_result .= "Filas afectadas: $filas_afectadas\n";
        $debug_result .= "Valor en BD después: [$valor_guardado]\n";
        $debug_result .= "¿Coincide? " . ($valor_guardado === $estado_cm ? "SÍ" : "NO") . "\n";
        file_put_contents($debug_file, $debug_result, FILE_APPEND);
        
        if ($filas_afectadas > 0) {
            // Mostrar información detallada
            $debug_info = "Estado enviado: " . addslashes($estado_cm) . "\\n";
            $debug_info .= "Filas afectadas: $filas_afectadas\\n";
            $debug_info .= "Valor en BD: " . addslashes($valor_guardado) . "\\n";
            
            if ($valor_guardado !== $estado_cm && $valor_guardado !== 'ERROR') {
                $debug_info .= "\\n⚠ ADVERTENCIA: El valor guardado NO coincide con el enviado!";
            }
            
            echo "<script>
                console.log('Estado enviado: " . addslashes($estado_cm) . "');
                console.log('Filas afectadas: $filas_afectadas');
                console.log('Valor verificado en DB: " . addslashes($valor_guardado) . "');
                alert('Persona actualizada correctamente\\n\\n$debug_info');
                window.location.href = 'seePersonaCM.php';
              </script>";
        } else {
            // No se actualizó ninguna fila
            echo "<script>
                console.error('WARNING: El UPDATE no afectó ninguna fila');
                console.log('Cedula original buscada: " . addslashes($cedula_original) . "');
                console.log('Estado enviado: " . addslashes($estado_cm) . "');
                alert('ADVERTENCIA: La actualización se ejecutó pero no afectó ninguna fila.\\nEs posible que no existan cambios o la cédula no coincida.\\nCédula buscada: " . addslashes($cedula_original) . "');
                window.location.href = 'seePersonaCM.php';
              </script>";
        }
    } else {
        error_log("Error en UPDATE: " . $mysqli->error);
        echo "<script>
            alert('Error al actualizar persona: " . addslashes($mysqli->error) . "');
            window.location.href = 'seePersonaCM.php';
          </script>";
    }
} else {
    echo "<script>
            alert('Método no válido');
            window.location.href = 'seePersonaCM.php';
          </script>";
}
?>
