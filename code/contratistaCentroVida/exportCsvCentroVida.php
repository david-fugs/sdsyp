<?php
// Exportación CSV ultrarrápida para Centro Vida
session_start();
require_once '../../conexion.php';
require_once '../filtros_grupo_usuario.php';
require_once '../filtros_grupos.php';

if (isset($mysqli)) {
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES 'utf8mb4'");
}

try {
    $log_file = __DIR__ . '/csv_debug.log';
    file_put_contents($log_file, "\n" . str_repeat("=", 80) . "\n", FILE_APPEND);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - [INICIO CSV]\n", FILE_APPEND);
    file_put_contents($log_file, "GET params: " . json_encode($_GET) . "\n", FILE_APPEND);

    $tipo_usuario_export = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
    file_put_contents($log_file, "Tipo usuario: $tipo_usuario_export\n", FILE_APPEND);

    // Query simple y rápida
    $query = "
        SELECT
            rcv.id_registro_centro_vida,
            p.cedula_persona,
            p.tipo_identificacion,
            p.nombres_persona,
            p.apellidos_persona,
            p.fecha_nacimiento,
            p.telefono_persona,
            p.genero_persona,
            p.grupo_sisben,
            p.persona_discapacidad,
            p.cual_discapacidad,
            p.cabeza_hogar,
            p.lider_comunidad,
            p.se_reconoce_como,
            p.orientacion_sexual,
            p.experiencia_migratoria,
            p.grupo_etnico,
            p.tipo_salud,
            p.nivel_educativo,
            p.zona_persona,
            p.referencia_persona,
            p.eps,
            p.peso,
            p.talla,
            p.patologias,
            p.factores_riesgo,
            p.factores_preventivos,
            p.ingresos_economicos,
            p.convivencia_actual,
            p.resultado_actividad,
            p.remision,
            p.correo_persona,
            p.telefono_referencia_persona,
            p.direccion_persona,
            p.condicion_ocupacion,
            p.condicion_componente,
            p.activo_desde,
            COALESCE(acv.descripcion_actividad, '') as actividad_centro_vida,
            COALESCE(pol.descripcion_politica, '') as descripcion_politica_registro,
            rcv.departamento_procedencia,
            rcv.condicion_otra,
            rcv.profesion,
            rcv.jornada,
            rcv.observacion,
            COALESCE(u.nombre, '') AS nombre_funcionario,
            rcv.fecha_registro,
            COALESCE(g.descripcion_grupo, '') as descripcion_grupo,
            COALESCE(b.nombre_bar, '') as nombre_barrio,
            COALESCE(c.nombre_com, '') as nombre_comuna,
            COALESCE(m.descripcion_meta, '') as descripcion_meta,
            COALESCE(a.descripcion_actividad, '') as descripcion_actividad_persona,
            COALESCE(ac.descripcion_accion, '') as descripcion_accion_persona,
            GROUP_CONCAT(DISTINCT rcvf.fecha_atencion ORDER BY rcvf.fecha_atencion ASC SEPARATOR ', ') as fechas_actividad
        FROM registro_centro_vida rcv
        INNER JOIN personas p ON rcv.cedula_persona = p.cedula_persona
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN actividad_centro_vida acv ON rcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
        LEFT JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida
        LEFT JOIN politicas_publicas pol ON rcv.politica_publica = pol.id_politica
        LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
        LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
        LEFT JOIN metas m ON rcv.id_meta = m.id_meta
        LEFT JOIN actividades a ON rcv.id_actividad = a.id_actividad
        LEFT JOIN acciones ac ON rcv.id_accion = ac.id_accion
        LEFT JOIN usuarios u ON u.id = rcv.funcionario_registro
    ";

    // Aplicar filtros
    $where = [];
    $params = [];
    $types = '';

    if (isset($_GET['cedula_persona']) && $_GET['cedula_persona'] !== '') {
        $where[] = "p.cedula_persona = ?";
        $params[] = $_GET['cedula_persona'];
        $types .= 's';
    }
    if (isset($_GET['nombre']) && $_GET['nombre'] !== '') {
        $where[] = "(p.nombres_persona LIKE ? OR p.apellidos_persona LIKE ?)";
        $params[] = "%" . $_GET['nombre'] . "%";
        $params[] = "%" . $_GET['nombre'] . "%";
        $types .= 'ss';
    }
    if (isset($_GET['actividad']) && $_GET['actividad'] !== '') {
        $where[] = "rcv.id_actividad_centro_vida = ?";
        $params[] = $_GET['actividad'];
        $types .= 'i';
    }
    if (isset($_GET['funcionario']) && $_GET['funcionario'] !== '') {
        $where[] = "rcv.funcionario_registro = ?";
        $params[] = intval($_GET['funcionario']);
        $types .= 'i';
    }
    if (isset($_GET['filtro_tipo_usuario']) && $_GET['filtro_tipo_usuario'] !== '' && is_numeric($_GET['filtro_tipo_usuario'])) {
        $where[] = "u.tipo_usuario = ?";
        $params[] = intval($_GET['filtro_tipo_usuario']);
        $types .= 'i';
    }
    if (isset($_GET['id_grupo_cv']) && $_GET['id_grupo_cv'] !== '') {
        $where[] = "p.id_grupo = ?";
        $params[] = intval($_GET['id_grupo_cv']);
        $types .= 'i';
    }

    // Filtro mes/año - igual que en Excel
    $mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? intval($_GET['mes']) : 0;
    $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : date('Y'); // Si no hay año, usar el actual
    if ($mes >= 1 && $mes <= 12) {
        $where[] = "YEAR(rcvf.fecha_atencion) = ? AND MONTH(rcvf.fecha_atencion) = ?";
        $params[] = $anio;
        $params[] = $mes;
        $types .= 'ii';
    }

    if (!empty($where)) {
        $query .= ' WHERE ' . implode(' AND ', $where);
    }

    // Filtro por grupo de usuario
    $where_grupo_usuario = obtenerCondicionFiltroGrupo('p');
    if (!empty($where_grupo_usuario)) {
        if (empty($where)) {
            $query .= ' WHERE 1=1 ';
        }
        $query .= $where_grupo_usuario;
    }

    // Tipo 10, 12 y 13: solo sus propios registros
    if (in_array($tipo_usuario_export, [10, 12, 13])) {
        $id_usuario_export = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
        if ($id_usuario_export === 0) {
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= ' AND 1=0';
        } else {
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= " AND rcv.funcionario_registro = $id_usuario_export";
        }
    } else {
        $grupos_cv_export = getGruposPermitidos($mysqli, $tipo_usuario_export);
        if (!empty($grupos_cv_export)) {
            $ids_cv_export = implode(',', array_map('intval', $grupos_cv_export));
            if (stripos($query, 'WHERE') === false) { $query .= ' WHERE 1=1'; }
            $query .= " AND p.id_grupo IN ($ids_cv_export)";
        }
    }

    $query .= ' GROUP BY rcv.id_registro_centro_vida ORDER BY rcv.fecha_registro DESC';

    // Ejecutar query
    $result = null;
    if (!empty($params) && $types) {
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        }
    } else {
        $result = $mysqli->query($query);
    }

    if (!$result) {
        file_put_contents($log_file, "[ERROR] " . $mysqli->error . "\n", FILE_APPEND);
        throw new Exception("Error en query: " . $mysqli->error);
    }

    file_put_contents($log_file, "[QUERY OK] Filas encontradas: " . $result->num_rows . "\n", FILE_APPEND);

    // Encabezados CSV
    $headers = [
        'FECHAS ACTIVIDAD',
        'CÉDULA', 'TIPO ID', 'NOMBRES', 'APELLIDOS', 'FECHA NAC', 'TELÉFONO', 'GÉNERO', 'SISBEN', 'DISCAPACIDAD', 'CUAL DISC.',
        'CABEZA HOGAR', 'LÍDER COM', 'RECONOCE COMO', 'ORIENT. SEXUAL', 'MIGRACIÓN', 'ETNIA', 'TIPO SALUD', 'NIVEL EDU', 'ZONA', 'REFERENCIA',
        'EPS', 'PESO', 'TALLA', 'PATOLOGÍAS', 'FACTORES RIESGO', 'FACT. PREV', 'INGRESOS', 'CONVIVENCIA', 'RESULTADO', 'REMISIÓN', 'CORREO', 'TEL REF',
        'DIRECCIÓN', 'COND. OCUP', 'COND. COMP', 'ACTIVO DESDE', 'GRUPO', 'BARRIO', 'COMUNA', 'META', 'ACTIVIDAD', 'ACCIÓN',
        'ACTIVIDAD CV', 'POLÍTICA PÚB', 'DEPARTAMENTO', 'CONDICIÓN OTRA', 'PROFESIÓN', 'JORNADA', 'OBSERVACIÓN', 'FUNCIONARIO', 'FECHA REGISTRO'
    ];

    // Enviar headers HTTP
    ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8-sig');
    header('Content-Disposition: attachment; filename="CentroVida_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    // Abrir output como archivo
    $output = fopen('php://output', 'w');

    // Escribir BOM para UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Escribir cabeceras
    fputcsv($output, $headers, ';', '"');

    // Procesar cada fila
    while ($row = $result->fetch_assoc()) {
        $fecha_nac = '';
        if (!empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] != '0000-00-00') {
            $fecha_nac = date('d/m/Y', strtotime($row['fecha_nacimiento']));
        }

        $fecha_registro = '';
        if (!empty($row['fecha_registro'])) {
            $fecha_registro = date('d/m/Y H:i', strtotime($row['fecha_registro']));
        }

        $activo_desde = '';
        if (!empty($row['activo_desde']) && $row['activo_desde'] != '0000-00-00') {
            $activo_desde = date('d/m/Y', strtotime($row['activo_desde']));
        }

        $fechas_actividad = '';
        if (!empty($row['fechas_actividad'])) {
            $formatted_dates = [];
            foreach (explode(', ', $row['fechas_actividad']) as $f) {
                if (!empty($f) && $f != '0000-00-00') {
                    $formatted_dates[] = date('d/m/Y', strtotime($f));
                }
            }
            $fechas_actividad = implode(', ', $formatted_dates);
        }

        $data = [
            $fechas_actividad,
            $row['cedula_persona'] ?? '',
            $row['tipo_identificacion'] ?? '',
            $row['nombres_persona'] ?? '',
            $row['apellidos_persona'] ?? '',
            $fecha_nac,
            $row['telefono_persona'] ?? '',
            $row['genero_persona'] ?? '',
            $row['grupo_sisben'] ?? '',
            $row['persona_discapacidad'] ?? '',
            $row['cual_discapacidad'] ?? '',
            $row['cabeza_hogar'] ?? '',
            $row['lider_comunidad'] ?? '',
            $row['se_reconoce_como'] ?? '',
            $row['orientacion_sexual'] ?? '',
            $row['experiencia_migratoria'] ?? '',
            $row['grupo_etnico'] ?? '',
            $row['tipo_salud'] ?? '',
            $row['nivel_educativo'] ?? '',
            $row['zona_persona'] ?? '',
            $row['referencia_persona'] ?? '',
            $row['eps'] ?? '',
            $row['peso'] ?? '',
            $row['talla'] ?? '',
            $row['patologias'] ?? '',
            $row['factores_riesgo'] ?? '',
            $row['factores_preventivos'] ?? '',
            $row['ingresos_economicos'] ?? '',
            $row['convivencia_actual'] ?? '',
            $row['resultado_actividad'] ?? '',
            $row['remision'] ?? '',
            $row['correo_persona'] ?? '',
            $row['telefono_referencia_persona'] ?? '',
            $row['direccion_persona'] ?? '',
            $row['condicion_ocupacion'] ?? '',
            $row['condicion_componente'] ?? '',
            $activo_desde,
            $row['descripcion_grupo'] ?? '',
            $row['nombre_barrio'] ?? '',
            $row['nombre_comuna'] ?? '',
            $row['descripcion_meta'] ?? '',
            $row['descripcion_actividad_persona'] ?? '',
            $row['descripcion_accion_persona'] ?? '',
            $row['actividad_centro_vida'] ?? '',
            $row['descripcion_politica_registro'] ?? '',
            $row['departamento_procedencia'] ?? '',
            $row['condicion_otra'] ?? '',
            $row['profesion'] ?? '',
            $row['jornada'] ?? '',
            $row['observacion'] ?? '',
            $row['nombre_funcionario'] ?? '',
            $fecha_registro,
        ];

        fputcsv($output, $data, ';', '"');
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al generar CSV: ' . $e->getMessage();
}

if (isset($mysqli)) {
    $mysqli->close();
}
?>
