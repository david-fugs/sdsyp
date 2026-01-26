<?php
// Suprimir warnings para evitar que interfieran con respuestas JSON
error_reporting(E_ERROR | E_PARSE);
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Nuevos campos funcionales
    $eps = $_POST['eps'] ?? '';
    $peso = $_POST['peso'] ?? '';
    $talla = $_POST['talla'] ?? '';
    $patologias = $_POST['patologias'] ?? '';
    $factores_riesgo = $_POST['factores_riesgo'] ?? '';
    $factores_preventivos = $_POST['factores_preventivos'] ?? '';
    $ingresos_economicos = $_POST['ingresos_economicos'] ?? '';
    $convivencia_actual = $_POST['convivencia_actual'] ?? '';
    $resultado_actividad = $_POST['resultado_actividad'] ?? '';
    $remision = $_POST['remision'] ?? '';

    // Nuevos campos solicitados
    $correo_persona = $_POST['correo_persona'] ?? '';
    $telefono_referencia_persona = $_POST['telefono_referencia_persona'] ?? '';
    $direccion_persona = $_POST['direccion_persona'] ?? '';
    $condicion_ocupacion = $_POST['condicion_ocupacion'] ?? '';
    $condicion_componente = $_POST['condicion_componente'] ?? '';

    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
    $tipo_identificacion = $_POST['tipo_identificacion'] ?? '';
    $nombres_persona = $_POST['nombres_persona'];
    $apellidos_persona = $_POST['apellidos_persona'];
    $telefono_persona = $_POST['telefono_persona'];
    $referencia_persona = $_POST['referencia_persona'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $genero_persona = $_POST['genero_persona'];
    $grupo_sisben = $_POST['grupo_sisben'] ?? '';
    $persona_discapacidad = $_POST['persona_discapacidad'] ?? '';
    $cual_discapacidad = $_POST['cual_discapacidad'] ?? '';
    $cabeza_hogar = $_POST['cabeza_hogar'] ?? '';
    $lider_comunidad = $_POST['lider_comunidad'] ?? '';
    $se_reconoce_como = $_POST['se_reconoce_como'] ?? '';
    $orientacion_sexual = $_POST['orientacion_sexual'] ?? '';
    $experiencia_migratoria = $_POST['experiencia_migratoria'] ?? '';
    $grupo_etnico = $_POST['grupo_etnico'] ?? '';
    $tipo_salud = $_POST['tipo_salud'] ?? '';
    $nivel_educativo = $_POST['nivel_educativo'] ?? '';
    $programa = $_POST['programa'] ?? [];
    $id_grupo = $_POST['id_grupo'] ?? '';

    // Nuevos campos de barrio, comuna y zona (usar solo IDs y zona como texto)
    $id_barrio_persona = $_POST['id_barrio_persona'] ?? null;
    $id_comuna_persona = $_POST['id_comuna_persona'] ?? null;
    $zona_persona = $_POST['zona_persona'] ?? '';
    $activo_desde = $_POST['activo_desde'] ?? null;

    // Nuevos campos: Meta, Actividad, Acción y Política Pública
    $id_meta = $_POST['id_meta'] ?? null;
    $id_actividad = $_POST['id_actividad'] ?? null;
    $id_accion = $_POST['id_accion'] ?? null;
    $id_politica_publica = $_POST['id_politica_publica'] ?? null;

    // Normalizar cadenas vacías a NULL para columnas FK y opcionales
    if ($id_meta === '' ) $id_meta = null;
    if ($id_actividad === '' ) $id_actividad = null;
    if ($id_accion === '' ) $id_accion = null;
    if ($id_politica_publica === '' ) $id_politica_publica = null;
    if ($id_barrio_persona === '' ) $id_barrio_persona = null;
    if ($id_comuna_persona === '' ) $id_comuna_persona = null;
    if ($id_grupo === '' ) $id_grupo = null;

    // Obtener fecha actual para fecha_alta_persona
    $fecha_alta_persona = date('Y-m-d H:i:s');

    //consulta
    $sql_insert_persona = "INSERT INTO personas (
        cedula_persona,
        tipo_identificacion,
        nombres_persona,
        apellidos_persona,
        telefono_persona,
        referencia_persona,
        fecha_nacimiento,
        genero_persona,
        grupo_sisben,
        persona_discapacidad,
        cual_discapacidad,
        cabeza_hogar,
        lider_comunidad,
        se_reconoce_como,
        orientacion_sexual,
        experiencia_migratoria,
        grupo_etnico,
        tipo_salud,
        nivel_educativo,
        id_barrio_persona,
        id_comuna_persona,
        zona_persona,
        id_grupo,
        id_grupo_inicial,
        correo_persona,
        telefono_referencia_persona,
        direccion_persona,
        condicion_ocupacion,
        condicion_componente,
        eps,
        peso,
        talla,
        patologias,
        factores_riesgo,
        factores_preventivos,
        ingresos_economicos,
        convivencia_actual,
        resultado_actividad,
        remision,
        id_usuario,
        fecha_alta_persona,
        activo_desde,
        id_meta,
        id_actividad,
        id_accion,
        id_politica_publica
    ) VALUES (
        '$cedula_persona',
        '$tipo_identificacion',
        '$nombres_persona',
        '$apellidos_persona',
        '$telefono_persona',
        '$referencia_persona',
        '$fecha_nacimiento',
        '$genero_persona',
        '$grupo_sisben',
        '$persona_discapacidad',
        '$cual_discapacidad',
        '$cabeza_hogar',
        '$lider_comunidad',
        '$se_reconoce_como',
        '$orientacion_sexual',
        '$experiencia_migratoria',
        '$grupo_etnico',
        '$tipo_salud',
        '$nivel_educativo',
        " . ($id_barrio_persona !== null ? "'$id_barrio_persona'" : 'NULL') . ",
        " . ($id_comuna_persona !== null ? "'$id_comuna_persona'" : 'NULL') . ",
        '$zona_persona',
        '$id_grupo',
        '$id_grupo',
        '$correo_persona',
        '$telefono_referencia_persona',
        '$direccion_persona',
        '$condicion_ocupacion',
        '$condicion_componente',
        '$eps',
        '$peso',
        '$talla',
        '$patologias',
        '$factores_riesgo',
        '$factores_preventivos',
        '$ingresos_economicos',
        '$convivencia_actual',
        '$resultado_actividad',
        '$remision',
        '$id_usuario',
        '$fecha_alta_persona',
        '$activo_desde',
        " . ($id_meta !== null ? "'$id_meta'" : 'NULL') . ",
        " . ($id_actividad !== null ? "'$id_actividad'" : 'NULL') . ",
        " . ($id_accion !== null ? "'$id_accion'" : 'NULL') . ",
        " . ($id_politica_publica !== null ? "'$id_politica_publica'" : 'NULL') . "
    )";


    // Ejecutar consulta con manejo de excepciones
    try {
        if ($mysqli->query($sql_insert_persona)) {
            //insert en persona_programa (solo si hay programas seleccionados)
            if (is_array($programa) && count($programa) > 0) {
                foreach ($programa as $id_programa) {
                    $sql_insert_persona_programa = "INSERT INTO persona_programa (cedula_persona, id_programa) VALUES ('$cedula_persona', '$id_programa')";

                    if ($mysqli->query($sql_insert_persona_programa)) {
                        echo "✅ Programa ID $id_programa insertado correctamente.<br>";
                    } else {
                        echo "❌ Error al insertar programa ID $id_programa: " . $mysqli->error . "<br>";
                    }
                }
            }
            echo "<script>
                alert('Insert successful');
                window.location.href = 'seePerson.php';
              </script>";
        }
    } catch (mysqli_sql_exception $e) {
        // Capturar excepción de mysqli
        // Verificar si es un error de cédula duplicada (código 1062)
        if ($e->getCode() == 1062 && strpos($e->getMessage(), 'cedula_persona') !== false) {
            // Consultar información de la persona existente
            $query_persona = "SELECT p.*, g.descripcion_grupo,
                (SELECT cc.descripcion_condicion 
                 FROM movimiento_persona mp 
                 JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                 WHERE mp.cedula_persona = p.cedula_persona 
                 AND cc.descripcion_condicion IN ('CPSAM EVADIDO', 'CPSAM FALLECIDO', 'CPSAM RETIRADO VOLUNTARIO', 'CPSAM TRASLADADO')
                 ORDER BY mp.fecha_movimiento DESC 
                 LIMIT 1) AS estado_movimiento
                FROM personas p
                LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
                WHERE p.cedula_persona = '$cedula_persona'";
            
            $result_persona = $mysqli->query($query_persona);
            
            if ($result_persona && $row_persona = $result_persona->fetch_assoc()) {
                $nombre_completo = $row_persona['nombres_persona'] . ' ' . $row_persona['apellidos_persona'];
                $grupo = $row_persona['descripcion_grupo'] ? $row_persona['descripcion_grupo'] : 'No asignado';
                
                // Determinar el estado
                $estado_persona = $row_persona['estado_movimiento'] ? $row_persona['estado_movimiento'] : 'CPSAM ACTIVO';
                
                // Verificar si es "Usuario Interesado" según condicion_componente
                if (isset($row_persona['condicion_componente']) && 
                    mb_strtolower(trim($row_persona['condicion_componente'])) === 'usuario interesado') {
                    $estado_persona = 'USUARIO INTERESADO';
                }
                
                // Verificar si es "Visita psicosocial fallida"
                if (isset($row_persona['condicion_componente']) && 
                    mb_strtolower(trim($row_persona['condicion_componente'])) === 'visita psicosocial fallida') {
                    $estado_persona = 'VISITA PSICOSOCIAL FALLIDA';
                }
                
                // Limpiar el prefijo CPSAM para mostrar
                $estado_mostrar = str_ireplace('CPSAM ', '', $estado_persona);
                
                // Enviar respuesta JSON
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => true,
                    'tipo' => 'duplicado',
                    'cedula' => $cedula_persona,
                    'nombre' => $nombre_completo,
                    'grupo' => $grupo,
                    'estado' => $estado_mostrar
                ]);
                exit;
            }
        }
        
        // Error genérico para otras excepciones
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'tipo' => 'general',
            'mensaje' => $e->getMessage()
        ]);
        exit;
    }
} else {
    echo "<script>
            alert('Method not valid');
            window.location.href = 'seePerson.php';
          </script>";
}
