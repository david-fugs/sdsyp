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
    $estado_cm = $_POST['estado_cm'] ?? 'ACTIVO';
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

    if ($mysqli->query($sql_update)) {
        echo "<script>
            alert('Persona actualizada correctamente');
            window.location.href = 'seePersonaCM.php';
          </script>";
    } else {
        echo "<script>
            alert('Error al actualizar persona: " . $mysqli->error . "');
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
