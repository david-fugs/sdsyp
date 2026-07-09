<?php
session_start();
ob_start();
include("../../conexion.php");
header('Content-Type: application/json');

// Validar método
if($_SERVER['REQUEST_METHOD']!=='POST'){
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
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
    $id_usuario = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
    $funcionario_responsable = intval($_POST['funcionario_responsable'] ?? 0);
    $tipo_registro = $mysqli->real_escape_string($_POST['tipo_registro'] ?? '');
    // Jornada: radio (valor único)
    $jornada = $mysqli->real_escape_string(trim($_POST['jornada'] ?? ''));
    // Profesión
    $profesion = $mysqli->real_escape_string(trim($_POST['profesion'] ?? ''));
    // Cédulas JSON para tipo Registro Actividad
    $cedulas_json = $_POST['cedulas_json'] ?? '';
    
    // Obtener las fechas del nuevo parámetro
    $fechas_atencion_json = $_POST['fechas_atencion'] ?? '[]';
    
    // Debug: Mostrar datos capturados
    error_log("Datos capturados - Fechas JSON: $fechas_atencion_json");

    // Decodificar fechas JSON
    $fechas_array = json_decode($fechas_atencion_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($fechas_array)) {
        throw new Exception("Debe seleccionar al menos una fecha de atención.");
    }

    // Debug: Mostrar fechas decodificadas
    error_log("Fechas decodificadas: " . print_r($fechas_array, true));

    // Insertar (sin tipo_actividad: siempre 'Masiva')
    $tipo_actividad = 'Masiva';

    $numero_grupo_ind = null;
    $registros_insertados = 0;

    // Iniciar transacción
    $mysqli->autocommit(FALSE);

    // Si es Registro Actividad y hay cédulas, calcular el numero_grupo antes de insertar
    // las filas de masiva_centro_vida, para poder guardarlo en cada una y así poder
    // recuperar luego las personas asociadas al editar.
    if ($tipo_registro === 'Registro Actividad' && !empty($cedulas_json)) {
        $cedulas_check = json_decode($cedulas_json, true);
        if (is_array($cedulas_check) && count($cedulas_check) > 0) {
            $ng_res = $mysqli->query("SELECT COALESCE(MAX(numero_grupo), 0) + 1 AS next_ng FROM registro_centro_vida");
            $numero_grupo_ind = $ng_res ? (int)$ng_res->fetch_assoc()['next_ng'] : 1;
        }
    }

    // Por cada fecha, insertar un registro en masiva_centro_vida
    foreach ($fechas_array as $fecha) {
        if (empty($fecha)) continue;

        // Construir consulta SQL plana (escapando valores de texto y casteando enteros)
        $cols = "id_meta,id_actividad,id_accion,politica_publica,id_centro_vida,fecha_atencion,nombre_lider,telefono_contacto,id_comuna,medio_verificacion,cantidad_masculino,cantidad_femenino,tipo_actividad,observacion_actividad,id_usuario,funcionario_responsable,id_actividad_centro_vida,tipo_registro,jornada,profesion,numero_grupo";

        // Escapar y formatear valores
        $vals = [
            intval($id_meta),
            intval($id_actividad),
            intval($id_accion),
            "'" . $mysqli->real_escape_string($politica_publica) . "'",
            intval($id_centro_vida),
            "'" . $mysqli->real_escape_string($fecha) . "'",
            "'" . $mysqli->real_escape_string($nombre_lider) . "'",
            "'" . $mysqli->real_escape_string($telefono_contacto) . "'",
            intval($id_comuna),
            "'" . $mysqli->real_escape_string($medio_verificacion) . "'",
            intval($cantidad_masculino),
            intval($cantidad_femenino),
            "'" . $mysqli->real_escape_string($tipo_actividad) . "'",
            "'" . $mysqli->real_escape_string($observacion_actividad) . "'",
            intval($id_usuario),
            intval($funcionario_responsable),
            intval($id_actividad_centro_vida),
            "'" . $mysqli->real_escape_string($tipo_registro) . "'",
            "'" . $mysqli->real_escape_string($jornada) . "'",
            "'" . $mysqli->real_escape_string($profesion) . "'",
            $numero_grupo_ind === null ? "NULL" : intval($numero_grupo_ind)
        ];

        $sql = "INSERT INTO masiva_centro_vida (" . $cols . ") VALUES (" . implode(',', $vals) . ")";

        if (!$mysqli->query($sql)) {
            throw new Exception("Error al insertar registro para fecha '$fecha': " . $mysqli->error);
        }

        $id_masiva = $mysqli->insert_id;

        // Si tipo_registro = Registro Actividad, generar registros individuales en registro_centro_vida
        if ($tipo_registro === 'Registro Actividad' && !empty($cedulas_json)) {
            $cedulas = json_decode($cedulas_json, true);
            if (is_array($cedulas) && count($cedulas) > 0) {

                $stmt_ind = $mysqli->prepare(
                    "INSERT INTO registro_centro_vida (cedula_persona, id_meta, id_actividad, id_accion, id_actividad_centro_vida, politica_publica, departamento_procedencia, observacion, profesion, jornada, funcionario_registro, numero_grupo, fecha_registro)
                     VALUES (?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?, ?, NOW())"
                );
                $stmt_ge_ind = $mysqli->prepare(
                    "INSERT IGNORE INTO registro_centro_vida_grupo_externo (id_registro_centro_vida, id_grupo_externo)
                     SELECT ?, id_grupo_externo FROM persona_grupo_externo WHERE cedula_persona = ?"
                );
                if ($stmt_ind) {
                    foreach ($cedulas as $ced) {
                        $ced = trim($ced);
                        if (empty($ced)) continue;
                        $stmt_ind->bind_param('siiiissssii',
                            $ced,
                            $id_meta,
                            $id_actividad,
                            $id_accion,
                            $id_actividad_centro_vida,
                            $politica_publica,
                            $observacion_actividad,
                            $profesion,
                            $jornada,
                            $id_usuario,
                            $numero_grupo_ind
                        );
                        if (!$stmt_ind->execute()) {
                            throw new Exception("Error al insertar registro individual: " . $stmt_ind->error);
                        }
                        $id_reg_ind = $mysqli->insert_id;
                        if ($id_reg_ind > 0) {
                            // Insertar fecha
                            if (!empty($fecha)) {
                                $mysqli->query("INSERT INTO registro_centro_vida_fechas (id_registro_centro_vida, fecha_atencion) VALUES ($id_reg_ind, '" . $mysqli->real_escape_string($fecha) . "')");
                            }
                            // Auto-poblar grupos externos de persona_grupo_externo
                            if ($stmt_ge_ind) {
                                $stmt_ge_ind->bind_param('is', $id_reg_ind, $ced);
                                $stmt_ge_ind->execute();
                            }
                        }
                    }
                    $stmt_ind->close();
                    if ($stmt_ge_ind) $stmt_ge_ind->close();
                }
            }
        }

        $registros_insertados++;
    }

    // Confirmar transacción
    $mysqli->commit();
    $mysqli->autocommit(TRUE);

    // Debug: Confirmar inserción exitosa
    error_log("Registros insertados: $registros_insertados");

    ob_clean();
    $msg = $registros_insertados === 1
        ? "Actividad masiva agregada correctamente (1 registro)"
        : "Se agregaron $registros_insertados registros (uno por cada fecha seleccionada).";
    
    if ($numero_grupo_ind !== null) {
        $msg .= '. Número de Grupo: ' . $numero_grupo_ind;
    }

    echo json_encode(['success' => true, 'message' => $msg, 'numero_grupo' => $numero_grupo_ind, 'registros_insertados' => $registros_insertados]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $mysqli->rollback();
    $mysqli->autocommit(TRUE);
    
    // Debug: Mostrar error
    error_log("Error en addRegistroMasivoCentroVida.php: " . $e->getMessage());
    
    // Devolver respuesta JSON de error
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$mysqli->close();
?>
