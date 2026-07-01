<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Nuevos campos funcionales
    $eps = $_POST['eps'] ?? '';
    $peso = $_POST['peso'] ?? '';
    $talla = $_POST['talla'] ?? '';
    $patologias = is_array($_POST['patologias'] ?? '') ? implode(',', $_POST['patologias']) : ($_POST['patologias'] ?? '');
    $factores_riesgo = $_POST['factores_riesgo'] ?? '';
    $factores_preventivos = $_POST['factores_preventivos'] ?? '';
    $ingresos_economicos = $_POST['ingresos_economicos'] ?? '';
    $convivencia_actual = $_POST['convivencia_actual'] ?? '';
    $resultado_actividad = $_POST['resultado_actividad'] ?? '';
    $remision = $_POST['remision'] ?? '';
    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
    $tipo_identificacion = $_POST['tipo_identificacion'] ?? '';
    $nombres_persona = $_POST['nombres_persona'];
    $apellidos_persona = $_POST['apellidos_persona'];
    $telefono_persona = $_POST['telefono_persona'];
    $telefono_referencia_persona = $_POST['telefono_referencia_persona'] ?? '';
    $referencia_persona = $_POST['referencia_persona'];
    $correo_persona = $_POST['correo_persona'] ?? '';
    $direccion_persona = $_POST['direccion_persona'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $genero_persona = $_POST['genero_persona'];
    $grupo_sisben = $_POST['grupo_sisben'] ?? '';
    $persona_discapacidad = $_POST['persona_discapacidad'] ?? '';
    $cual_discapacidad = $_POST['cual_discapacidad'] ?? '';
    $victima = $_POST['victima'] ?? '';
    $cabeza_hogar = $_POST['cabeza_hogar'] ?? '';
    $lider_comunidad = $_POST['lider_comunidad'] ?? '';
    $se_reconoce_como = $_POST['se_reconoce_como'] ?? '';
    $orientacion_sexual = $_POST['orientacion_sexual'] ?? '';
    $experiencia_migratoria = $_POST['experiencia_migratoria'] ?? '';
    $grupo_etnico = $_POST['grupo_etnico'] ?? '';
    $tipo_salud = $_POST['tipo_salud'] ?? '';
    $nivel_educativo = $_POST['nivel_educativo'] ?? '';
    $condicion_ocupacion = $_POST['condicion_ocupacion'] ?? '';
    $condicion_componente = $_POST['condicion_componente'] ?? '';
    $activo_desde = $_POST['activo_desde'] ?? null;
    $programa = $_POST['programa'];
    $cedula_original = $_POST['cedula_original'];
    $id_grupo = $_POST['id_grupo'] ?? '';
    $id_politica_publica = $_POST['id_politica_publica'] ?? '';
    // Nuevos campos de barrio, comuna y zona (usar solo IDs y zona como texto)
    $id_barrio_persona = $_POST['id_barrio_persona'] ?? null;
    $id_comuna_persona = $_POST['id_comuna_persona'] ?? null;
    $zona_persona = $_POST['zona_persona'] ?? '';

    // Nuevos campos: Meta, Actividad, Acción y Política Pública
    $id_meta = $_POST['id_meta'] ?? null;
    $id_actividad = $_POST['id_actividad'] ?? null;
    $id_accion = $_POST['id_accion'] ?? null;
    $id_politica_publica = $_POST['id_politica_publica'] ?? null;

    // Nuevo campo: Sin convenio
    $sin_convenio = isset($_POST['sin_convenio']) ? 1 : 0;

    // Nuevos campos: Jornada y Grupos Externos
    $jornada = $_POST['jornada'] ?? null;
    if ($jornada === '') $jornada = null;
    $grupos_externos_post = isset($_POST['grupos_externos']) && is_array($_POST['grupos_externos'])
        ? array_filter(array_map('intval', $_POST['grupos_externos']))
        : [];

    // PRIMERO: Actualizar persona
    $sql_update_persona = "UPDATE personas SET
        cedula_persona='$cedula_persona',
        tipo_identificacion='$tipo_identificacion',
        nombres_persona='$nombres_persona',
        apellidos_persona='$apellidos_persona',
        telefono_persona='$telefono_persona',
        telefono_referencia_persona='$telefono_referencia_persona',
        referencia_persona='$referencia_persona',
        correo_persona='$correo_persona',
        direccion_persona='$direccion_persona',
        fecha_nacimiento='$fecha_nacimiento',
        genero_persona='$genero_persona',
        grupo_sisben='$grupo_sisben',
        persona_discapacidad='$persona_discapacidad',
        cual_discapacidad='$cual_discapacidad',
        victima='$victima',
        cabeza_hogar='$cabeza_hogar',
        lider_comunidad='$lider_comunidad',
        se_reconoce_como='$se_reconoce_como',
        orientacion_sexual='$orientacion_sexual',
        experiencia_migratoria='$experiencia_migratoria',
        grupo_etnico='$grupo_etnico',
        tipo_salud='$tipo_salud',
        nivel_educativo='$nivel_educativo',
        condicion_ocupacion='$condicion_ocupacion',
        condicion_componente='$condicion_componente',
        activo_desde='$activo_desde',
        id_barrio_persona=" . ($id_barrio_persona !== null ? "'$id_barrio_persona'" : 'NULL') . ",
        id_comuna_persona=" . ($id_comuna_persona !== null ? "'$id_comuna_persona'" : 'NULL') . ",
        zona_persona='$zona_persona',
        id_grupo='$id_grupo',
        id_meta=" . ($id_meta !== null ? "'$id_meta'" : 'NULL') . ",
        id_actividad=" . ($id_actividad !== null ? "'$id_actividad'" : 'NULL') . ",
        id_accion=" . ($id_accion !== null ? "'$id_accion'" : 'NULL') . ",
        id_politica_publica=" . ($id_politica_publica !== null ? "'$id_politica_publica'" : 'NULL') . ",
        eps='$eps',
        peso='$peso',
        talla='$talla',
        patologias='$patologias',
        factores_riesgo='$factores_riesgo',
        factores_preventivos='$factores_preventivos',
        ingresos_economicos='$ingresos_economicos',
        convivencia_actual='$convivencia_actual',
        resultado_actividad='$resultado_actividad',
        remision='$remision',
        sin_convenio='$sin_convenio',
        jornada=" . ($jornada !== null ? "'" . $mysqli->real_escape_string($jornada) . "'" : 'NULL') . "
        WHERE cedula_persona='$cedula_original'";
    
    // Ejecutar consulta de actualización
    if (!$mysqli->query($sql_update_persona)) {
        echo "<script>
            alert('Error al actualizar persona: " . $mysqli->error . "');
            window.location.href = 'seePerson.php';
          </script>";
        exit();
    }

    // SEGUNDO: Actualizar/eliminar referencias en tablas relacionadas según cambie la cédula
    if ($cedula_original !== $cedula_persona) {
        // Si la cédula cambió, ELIMINAR los registros antiguos con cédula original
        // (porque no se puede tener registros huérfanos)
        $mysqli->query("DELETE FROM persona_programa WHERE cedula_persona='$cedula_original'");
        $mysqli->query("DELETE FROM persona_grupo_externo WHERE cedula_persona='$cedula_original'");
    }

    // TERCERO: Insertar nuevos programas (sin eliminar los existentes)
        foreach ($programa as $id_programa) {
            $sql_insert_persona_programa = "INSERT IGNORE INTO persona_programa (cedula_persona, id_programa) VALUES ('$cedula_persona', '$id_programa')";
            $mysqli->query($sql_insert_persona_programa);
        }
        
        // CUARTO: Insertar grupos externos sin eliminar los existentes
        foreach ($grupos_externos_post as $id_ge) {
            if ((int)$id_ge > 0) {
                $mysqli->query("INSERT IGNORE INTO persona_grupo_externo (cedula_persona, id_grupo_externo) VALUES ('$cedula_persona', " . (int)$id_ge . ")");
            }
        }
        
        echo "<script>
            alert('Actualizado correctamente');
            window.location.href = 'seePerson.php';
          </script>";

}

